<?php

namespace App\Http\Controllers;

use App\Services\Esb\EsbClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InvestorSalesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->email === 'superadmin@gmail.com' || $user->role === 'superadmin');

        // =========================
        // 1) AMBIL OUTLET SESUAI ROLE
        // =========================

        // semua role selain investor = lihat semua outlet
        if ($user->role !== 'investor') {

            $rawOutlets = DB::table('tbl_outlets')
                ->select('id', 'nama_outlet', 'mitra_id')
                ->orderBy('nama_outlet')
                ->orderBy('id')
                ->get();

        } else {

            // investor hanya outlet miliknya
            $investorId = session('investor_id');

            if (!$investorId) {
                abort(403, 'Investor tidak ditemukan.');
            }

            $rawOutlets = DB::table('tbl_outlets')
                ->select('id', 'nama_outlet', 'mitra_id')
                ->whereIn('mitra_id', function ($q) use ($investorId) {
                    $q->select('id')
                        ->from('tbl_mitra')
                        ->where('investor_id', $investorId);
                })
                ->orderBy('nama_outlet')
                ->orderBy('id')
                ->get();
        }

        // =========================
        // 2) NORMALISASI OUTLET
        // =========================
        $normalizeOutletName = function ($name) {
            $name = strtoupper(trim((string) $name));

            // hapus spasi berlebih saja
            $name = preg_replace('/\s+/', ' ', $name);

            return $name;
        };

        $outlets = $rawOutlets
            ->groupBy(function ($o) use ($normalizeOutletName) {
                return $normalizeOutletName($o->nama_outlet);
            })
            ->map(function ($rows, $displayName) {
                $ids = $rows->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->toArray();

                $first = $rows->sortBy('id')->first();

                return (object) [
                    'id' => (int) $first->id,
                    'nama_outlet' => (string) $first->nama_outlet,
                    'nama_outlet_display' => $displayName,
                    'mitra_id' => $first->mitra_id,
                    'outlet_ids' => $ids,
                ];
            })
            ->values();

        $totalOutletUnik = $outlets->count();

        $allOutletIds = $outlets
            ->pluck('outlet_ids')
            ->flatten()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        // =========================
        // 3) DEFAULT TANGGAL
        // =========================
        $dbLastDate = null;

        if (!empty($allOutletIds)) {
            $dbLastDate = DB::table('tbl_laporan_bulanan')
                ->whereIn('outlet_id', $allOutletIds)
                ->where(function ($q) {
                    $q->where(DB::raw('COALESCE(total_omset, 0)'), '>', 0)
                    ->orWhere(DB::raw('COALESCE(total_cu, 0)'), '>', 0);
                })
                ->max(DB::raw('DATE(tanggal)'));
        }

        $tanggalAwal = $request->get('tanggal_awal');
        $tanggalAkhir = $request->get('tanggal_akhir');

        if (empty($tanggalAwal) && !empty($dbLastDate)) {
            $tanggalAwal = $dbLastDate;
        }

        if (empty($tanggalAkhir) && !empty($dbLastDate)) {
            $tanggalAkhir = $dbLastDate;
        }

        if (!empty($tanggalAwal) && !empty($tanggalAkhir) && $tanggalAwal > $tanggalAkhir) {
            [$tanggalAwal, $tanggalAkhir] = [$tanggalAkhir, $tanggalAwal];
        }

        // =========================
        // 4) TOTAL OUTLET AKTIF SAAT INI
        // Hitung outlet yang ada transaksi di tanggal aktif dashboard
        // =========================
        $totalOutletAktif = 0;
        $tanggalHitungOutletAktif = $tanggalAkhir ?: $dbLastDate;

        if (!empty($tanggalHitungOutletAktif) && !empty($allOutletIds)) {
            $activeOutletNames = DB::table('tbl_laporan_bulanan as lb')
                ->join('tbl_outlets as o', 'o.id', '=', 'lb.outlet_id')
                ->whereDate('lb.tanggal', $tanggalHitungOutletAktif)
                ->whereIn('lb.outlet_id', $allOutletIds)
                ->where(function ($q) {
                    $q->where(DB::raw('COALESCE(lb.total_omset, 0)'), '>', 0)
                    ->orWhere(DB::raw('COALESCE(lb.total_cu, 0)'), '>', 0);
                })
                ->pluck('o.nama_outlet')
                ->map(function ($name) use ($normalizeOutletName) {
                    return $normalizeOutletName($name);
                })
                ->filter()
                ->unique()
                ->values();

            $totalOutletAktif = $activeOutletNames->count();
        }

        // // =========================
        // // 5) MAINTENANCE RANGE
        // // =========================
        // if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
        //     $maintenanceStart = '2026-01-01';
        //     $maintenanceEnd = '2026-02-28';

        //     $isMaintenanceRange = $tanggalAwal <= $maintenanceEnd && $tanggalAkhir >= $maintenanceStart;

        //     if ($isMaintenanceRange) {
        //         return redirect()
        //             ->route('investor.sales.dashboard')
        //             ->with('maintenance', 'Data bulan Januari - Februari sedang dalam maintenance.');
        //     }
        // }

        $filterApplied = !empty($tanggalAwal) && !empty($tanggalAkhir);

        // =========================
        // 6) FILTER OUTLET TERPILIH
        // =========================
        $selectedOutletId = null;
        $selectedOutletIds = $allOutletIds;

        if ($request->filled('outlet')) {
            $requestedOutletId = (int) $request->outlet;

            $selectedOutlet = $outlets->first(function ($outlet) use ($requestedOutletId) {
                return (int) $outlet->id === $requestedOutletId;
            });

            if ($selectedOutlet) {
                $selectedOutletId = $selectedOutlet->id;
                $selectedOutletIds = collect($selectedOutlet->outlet_ids ?? [$selectedOutlet->id])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }

        $applyOutletFilter = function ($query) use ($selectedOutletIds) {
            if (empty($selectedOutletIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('outlet_id', $selectedOutletIds);
        };

        // =========================
        // 7) DEFAULT VARIABLE
        // =========================
        $laporan = collect();
        $totalOmset = 0;
        $totalCU = 0;
        $averageSales = 0;
        $nonSalesTransaction = 0;

        $chartLabels = [];
        $chartOmset = [];
        $chartCU = [];
        $chartOrders = [];
        $chartAOV = [];

        $topProduk = collect();
        $chartHourlyFull = [];
        $chartHourlyOmsetFull = [];

        $takeawayTotal = 0;
        $dineinTotal = 0;

        $totalPerPlatform = [
            'shopeefood' => 0,
            'grabfood' => 0,
            'gofood' => 0,
            'qpon' => 0,
            'cash' => 0,
            'transfer' => 0,
            'qris_bca' => 0,
            'qris_bukupay' => 0,
            'qris_esb' => 0,
            'qris_gopay' => 0,
            'qris_shopeepay' => 0,
            'tiktok_shop' => 0,
        ];

        // =========================
        // 8) QUERY SAAT FILTER AKTIF
        // =========================
        if ($filterApplied && !empty($selectedOutletIds)) {
            $kpiQuery = DB::table('tbl_laporan_bulanan')
                ->selectRaw("
                    SUM(COALESCE(total_omset, 0)) AS total_omset,
                    SUM(COALESCE(total_non_sales, 0)) AS total_non_sales,
                    SUM(COALESCE(total_cu, 0)) AS total_cu
                ")
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

            $applyOutletFilter($kpiQuery);

            $kpi = $kpiQuery->first();

            $totalOmset = (float) ($kpi->total_omset ?? 0);
            $totalCU = (float) ($kpi->total_cu ?? 0);
            $nonSalesTransaction = (float) ($kpi->total_non_sales ?? 0);

            $laporanQuery = DB::table('tbl_laporan_bulanan')
                ->select(
                    DB::raw('DATE(tanggal) as tanggal'),
                    DB::raw('SUM(COALESCE(total_omset, 0)) as total_omset'),
                    DB::raw('SUM(COALESCE(total_cu, 0)) as total_cu')
                )
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

            $applyOutletFilter($laporanQuery);

            $laporan = $laporanQuery
                ->groupBy(DB::raw('DATE(tanggal)'))
                ->orderBy('tanggal', 'asc')
                ->get();

            $chartLabels = $laporan->pluck('tanggal')->map(fn ($v) => (string) $v)->toArray();
            $chartOmset = $laporan->pluck('total_omset')->map(fn ($v) => (float) $v)->toArray();
            $chartCU = $laporan->pluck('total_cu')->map(fn ($v) => (float) $v)->toArray();

            foreach ($laporan as $row) {
                $orders = (float) ($row->total_cu ?? 0);
                $omset = (float) ($row->total_omset ?? 0);

                $chartOrders[] = (int) $orders;
                $chartAOV[] = $orders > 0 ? ($omset / $orders) : null;
            }

            $daysCount = $laporan->pluck('tanggal')->unique()->count();
            $averageSales = $daysCount > 0 ? ($totalOmset / $daysCount) : 0;

            if (Schema::hasTable('tbl_laporan_pareto')) {
                $topProdukQuery = DB::table('tbl_laporan_pareto')
                    ->select(
                        'item_nama',
                        DB::raw('SUM(total_jumlah) as total_penjualan'),
                        DB::raw('SUM(total_harga) as total_harga')
                    )
                    ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

                $applyOutletFilter($topProdukQuery);

                if ($request->filled('kategori')) {
                    if ($request->kategori === 'PAKET') {
                        $topProdukQuery->where('item_nama', 'like', '%PAKET%');
                    } elseif ($request->kategori === 'ALACARTE') {
                        $topProdukQuery->where('item_nama', 'not like', '%PAKET%');
                    }
                }

                $topProduk = $topProdukQuery
                    ->groupBy('item_nama')
                    ->orderByDesc('total_penjualan')
                    ->get();
            }

            if (Schema::hasTable('tbl_laporan_ecommerce')) {
                $ecommerceQuery = DB::table('tbl_laporan_ecommerce')
                    ->selectRaw("
                        CASE
                            WHEN UPPER(item_varian) LIKE '%SHOPEE%' THEN 'shopeefood'
                            WHEN UPPER(item_varian) LIKE '%GRAB%' THEN 'grabfood'
                            WHEN UPPER(item_varian) LIKE '%GOFOOD%' THEN 'gofood'
                            WHEN UPPER(item_varian) LIKE '%GO FOOD%' THEN 'gofood'
                            WHEN UPPER(item_varian) LIKE '%TIKTOK%' THEN 'tiktok_shop'
                            WHEN UPPER(item_varian) LIKE '%TIK TOK%' THEN 'tiktok_shop'
                            WHEN UPPER(item_varian) LIKE '%QPON%' OR UPPER(item_varian) LIKE '%Q-PON%' THEN 'qpon'
                            ELSE NULL
                        END as platform_key,
                        SUM(total_jumlah) as total
                    ")
                    ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

                $applyOutletFilter($ecommerceQuery);

                $ecommerceResults = $ecommerceQuery
                    ->groupBy('platform_key')
                    ->get();

                foreach ($ecommerceResults as $row) {
                    if (!empty($row->platform_key) && isset($totalPerPlatform[$row->platform_key])) {
                        $totalPerPlatform[$row->platform_key] += (float) $row->total;
                    }
                }
            }

            $offlineQuery = DB::table('tbl_transaksi_perhari')
                ->selectRaw("
                    CASE
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%CASH%' THEN 'cash'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%TRANSFER%' THEN 'transfer'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS BCA%' THEN 'qris_bca'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS BUKUPAY%' THEN 'qris_bukupay'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS ESB%' THEN 'qris_esb'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS GOPAY%' THEN 'qris_gopay'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS SHOPEEPAY%' THEN 'qris_shopeepay'
                        ELSE NULL
                    END as platform_key,
                    SUM(COALESCE(item_sub_total, 0)) as total
                ")
                ->whereBetween('sesi_tanggal', [$tanggalAwal, $tanggalAkhir])
                ->whereRaw("
                    UPPER(COALESCE(item_varian, '')) NOT LIKE '%SHOPEE%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%GRAB%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%GOFOOD%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%GO FOOD%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%TIKTOK%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%TIK TOK%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%QPON%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%Q-PON%'
                ");

            $applyOutletFilter($offlineQuery);

            $offlineResults = $offlineQuery
                ->groupBy('platform_key')
                ->get();

            $offlinePlatformTotals = [
                'cash' => 0,
                'transfer' => 0,
                'qris_bca' => 0,
                'qris_bukupay' => 0,
                'qris_esb' => 0,
                'qris_gopay' => 0,
                'qris_shopeepay' => 0,
                'tiktok_shop' => 0,
            ];

            foreach ($offlineResults as $row) {
                if (!empty($row->platform_key) && isset($offlinePlatformTotals[$row->platform_key])) {
                    $offlinePlatformTotals[$row->platform_key] += (float) $row->total;
                }
            }

            $totalPaymentOfflineRaw = array_sum($offlinePlatformTotals);

            if ($totalOmset > 0 && $totalPaymentOfflineRaw > $totalOmset) {
                $scale = $totalOmset / $totalPaymentOfflineRaw;

                foreach ($offlinePlatformTotals as $key => $value) {
                    $offlinePlatformTotals[$key] = round($value * $scale, 2);
                }
            }

            foreach ($offlinePlatformTotals as $key => $value) {
                if (isset($totalPerPlatform[$key])) {
                    $totalPerPlatform[$key] += $value;
                }
            }

            $dineTakeQuery = DB::table('tbl_transaksi_perhari')
                ->selectRaw("
                    SUM(CASE WHEN UPPER(COALESCE(item_varian, '')) LIKE '%TAKEAWAY%' THEN COALESCE(item_sub_total, 0) ELSE 0 END) AS takeaway_total,
                    SUM(CASE
                            WHEN UPPER(COALESCE(item_varian, '')) LIKE '%DINE IN%' THEN COALESCE(item_sub_total, 0)
                            WHEN UPPER(COALESCE(item_varian, '')) LIKE '%DINEIN%' THEN COALESCE(item_sub_total, 0)
                            ELSE 0
                        END) AS dinein_total
                ")
                ->whereBetween('sesi_tanggal', [$tanggalAwal, $tanggalAkhir]);

            $applyOutletFilter($dineTakeQuery);

            $dineTakeRow = $dineTakeQuery->first();
            $takeawayTotal = (float) ($dineTakeRow->takeaway_total ?? 0);
            $dineinTotal = (float) ($dineTakeRow->dinein_total ?? 0);

            // =========================
            // TRANSAKSI PER JAM
            // =========================
            // tbl_transaksi_perhari adalah data item/detail, jadi COUNT(*) tidak aman
            // karena 1 transaksi bisa punya banyak baris item.
            //
            // Pola jam tetap diambil dari tr_waktu dan item_sub_total.
            // Namun total omset per jam dikunci agar jumlah semua bar biru sama persis
            // dengan total omset dashboard dari tbl_laporan_bulanan ($totalOmset).
            // Total transaksi per jam juga dikunci agar jumlah semua bar hijau sama persis
            // dengan total CU dashboard ($totalCU).
            $hourlyOmsetQuery = DB::table('tbl_transaksi_perhari')
                ->select(
                    DB::raw('HOUR(tr_waktu) as jam'),
                    DB::raw('SUM(COALESCE(item_sub_total, 0)) as total')
                )
                ->whereBetween('sesi_tanggal', [$tanggalAwal, $tanggalAkhir])
                ->whereNotNull('tr_waktu')
                ->whereRaw('COALESCE(item_sub_total, 0) > 0');

            $applyOutletFilter($hourlyOmsetQuery);

            $hourlyOmset = $hourlyOmsetQuery
                ->groupBy(DB::raw('HOUR(tr_waktu)'))
                ->orderBy('jam')
                ->get()
                ->pluck('total', 'jam');

            $totalHourlyOmsetRaw = (float) collect($hourlyOmset)->sum();

            $remainingOmset = (int) round($totalOmset);
            $remainingCU = (int) round($totalCU);
            $lastActiveHour = null;

            foreach (range(5, 22) as $h) {
                $rawOmsetJam = (float) ($hourlyOmset[$h] ?? 0);

                if ($rawOmsetJam > 0 && $totalHourlyOmsetRaw > 0 && $totalOmset > 0) {
                    $lastActiveHour = $h;

                    $omsetJam = (int) floor(($rawOmsetJam / $totalHourlyOmsetRaw) * $totalOmset);
                    $trxJam = (int) floor(($rawOmsetJam / $totalHourlyOmsetRaw) * $totalCU);

                    $chartHourlyOmsetFull[$h] = $omsetJam;
                    $chartHourlyFull[$h] = $trxJam;

                    $remainingOmset -= $omsetJam;
                    $remainingCU -= $trxJam;
                } else {
                    $chartHourlyOmsetFull[$h] = 0;
                    $chartHourlyFull[$h] = 0;
                }
            }

            // Tambahkan sisa pembulatan ke jam aktif terakhir.
            // Ini memastikan:
            // array_sum($chartHourlyOmsetFull) === round($totalOmset)
            // array_sum($chartHourlyFull) === round($totalCU)
            if ($lastActiveHour !== null) {
                $chartHourlyOmsetFull[$lastActiveHour] += $remainingOmset;
                $chartHourlyFull[$lastActiveHour] += $remainingCU;
            }
        } else {
            foreach (range(5, 22) as $h) {
                $chartHourlyFull[$h] = 0;
                $chartHourlyOmsetFull[$h] = 0;
            }
        }

        // =========================
        // 9) NOTIFIKASI TURUN SALES
        // =========================
        $notifikasiTurunSales = collect();

        $tanggalTersedia = DB::table('tbl_laporan_bulanan')
            ->select(DB::raw('DATE(tanggal) as tanggal'))
            ->when(!empty($allOutletIds), function ($q) use ($allOutletIds) {
                $q->whereIn('outlet_id', $allOutletIds);
            })
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderByDesc('tanggal')
            ->limit(2)
            ->pluck('tanggal');

        if ($tanggalTersedia->count() >= 2 && !empty($allOutletIds)) {
            $hariIni = $tanggalTersedia[0];
            $kemarin = $tanggalTersedia[1];

            $salesHariIni = DB::table('tbl_laporan_bulanan')
                ->whereDate('tanggal', $hariIni)
                ->whereIn('outlet_id', $allOutletIds)
                ->select('outlet_id', DB::raw('SUM(total_omset) as total'))
                ->groupBy('outlet_id')
                ->get()
                ->keyBy('outlet_id');

            $salesKemarin = DB::table('tbl_laporan_bulanan')
                ->whereDate('tanggal', $kemarin)
                ->whereIn('outlet_id', $allOutletIds)
                ->select('outlet_id', DB::raw('SUM(total_omset) as total'))
                ->groupBy('outlet_id')
                ->get()
                ->keyBy('outlet_id');

            foreach ($salesHariIni as $id => $hariIniData) {
                $totalHariIni = (float) $hariIniData->total;
                $totalKemarin = (float) ($salesKemarin[$id]->total ?? 0);

                if ($totalKemarin > 0 && $totalHariIni < $totalKemarin) {
                    $selisihRupiah = $totalKemarin - $totalHariIni;
                    $persenTurun = round(($selisihRupiah / $totalKemarin) * 100, 1);

                    $outletInfo = $outlets->first(function ($outlet) use ($id) {
                        return in_array((int) $id, $outlet->outlet_ids ?? [], true);
                    });

                    $notifikasiTurunSales->push([
                        'outlet_id' => $id,
                        'nama_outlet' => $outletInfo->nama_outlet_display ?? 'Outlet Tidak Dikenal',
                        'tanggal_terbaru' => $hariIni,
                        'tanggal_pembanding' => $kemarin,
                        'total_kemarin' => number_format($totalKemarin, 0, ',', '.'),
                        'total_hari_ini' => number_format($totalHariIni, 0, ',', '.'),
                        'selisih_rupiah' => number_format($selisihRupiah, 0, ',', '.'),
                        'persen_turun' => $persenTurun,
                    ]);
                }
            }

            $notifikasiTurunSales = $notifikasiTurunSales
                ->sortByDesc('persen_turun')
                ->values();
        }

        return view('Investor.dashboard', compact(
            'outlets',
            'filterApplied',
            'laporan',
            'totalOmset',
            'totalCU',
            'averageSales',
            'nonSalesTransaction',
            'chartLabels',
            'chartOmset',
            'chartCU',
            'chartOrders',
            'chartAOV',
            'topProduk',
            'chartHourlyFull',
            'chartHourlyOmsetFull',
            'totalPerPlatform',
            'takeawayTotal',
            'dineinTotal',
            'totalOutletUnik',
            'totalOutletAktif',
            'notifikasiTurunSales',
            'tanggalAwal',
            'tanggalAkhir',
            'selectedOutletId'
        ));
    }
    
    public function indexGO(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->email === 'superadmin@gmail.com' || $user->role === 'superadmin');

        // =========================
        // 1) AMBIL OUTLET SESUAI ROLE
        // BEDANYA DENGAN index(): HANYA OUTLET STATUS GO
        // =========================
        if ($isSuperAdmin) {
            $rawOutlets = DB::table('tbl_outlets')
                ->select('id', 'nama_outlet', 'mitra_id')
                ->where('status', 'go')
                ->orderBy('nama_outlet')
                ->orderBy('id')
                ->get();
        } else {
            if (in_array($user->role, ['leader', 'spv', 'tm_manager'], true)) {
                $query = DB::table('tbl_outlets')
                    ->select('id', 'nama_outlet', 'mitra_id')
                    ->where('status', 'go');

                if (!empty($user->outlet_id)) {
                    $query->where('id', $user->outlet_id);
                }

                $rawOutlets = $query
                    ->orderBy('nama_outlet')
                    ->orderBy('id')
                    ->get();
            } else {
                // investor
                $investorId = session('investor_id');

                if (!$investorId) {
                    abort(403, 'Investor tidak ditemukan.');
                }

                $rawOutlets = DB::table('tbl_outlets')
                    ->select('id', 'nama_outlet', 'mitra_id')
                    ->where('status', 'go')
                    ->whereIn('mitra_id', function ($q) use ($investorId) {
                        $q->select('id')
                            ->from('tbl_mitra')
                            ->where('investor_id', $investorId);
                    })
                    ->orderBy('nama_outlet')
                    ->orderBy('id')
                    ->get();
            }
        }

        // =========================
        // 2) NORMALISASI OUTLET
        // SAMA SEPERTI index()
        // =========================
        $normalizeOutletName = function ($name) {
            $name = strtoupper(trim((string) $name));
            $name = preg_replace('/\s+/', ' ', $name);

            return $name;
        };

        $outlets = $rawOutlets
            ->groupBy(fn ($o) => $normalizeOutletName($o->nama_outlet))
            ->map(function ($rows, $displayName) {
                $ids = $rows->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->toArray();

                $first = $rows->sortBy('id')->first();

                return (object) [
                    'id' => (int) $first->id,
                    'nama_outlet' => (string) $first->nama_outlet,
                    'nama_outlet_display' => $displayName,
                    'mitra_id' => $first->mitra_id,
                    'outlet_ids' => $ids,
                ];
            })
            ->values();

        $totalOutletUnik = $outlets->count();

        $allOutletIds = $outlets
            ->pluck('outlet_ids')
            ->flatten()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        // =========================
        // 3) DEFAULT TANGGAL
        // AMBIL TANGGAL TERAKHIR YANG ADA DATANYA
        // =========================
        $dbLastDate = null;

        if (!empty($allOutletIds)) {
            $dbLastDate = DB::table('tbl_laporan_bulanan')
                ->whereIn('outlet_id', $allOutletIds)
                ->where(function ($q) {
                    $q->where(DB::raw('COALESCE(total_omset, 0)'), '>', 0)
                    ->orWhere(DB::raw('COALESCE(total_cu, 0)'), '>', 0);
                })
                ->max(DB::raw('DATE(tanggal)'));
        }

        $tanggalAwal = $request->get('tanggal_awal');
        $tanggalAkhir = $request->get('tanggal_akhir');

        if (empty($tanggalAwal) && !empty($dbLastDate)) {
            $tanggalAwal = $dbLastDate;
        }

        if (empty($tanggalAkhir) && !empty($dbLastDate)) {
            $tanggalAkhir = $dbLastDate;
        }

        if (!empty($tanggalAwal) && !empty($tanggalAkhir) && $tanggalAwal > $tanggalAkhir) {
            [$tanggalAwal, $tanggalAkhir] = [$tanggalAkhir, $tanggalAwal];
        }

        // =========================
        // 4) TOTAL OUTLET AKTIF SAAT INI
        // HITUNG OUTLET GO YANG ADA TRANSAKSI DI TANGGAL AKTIF DASHBOARD
        // =========================
        $totalOutletAktif = 0;
        $tanggalHitungOutletAktif = $tanggalAkhir ?: $dbLastDate;

        if (!empty($tanggalHitungOutletAktif) && !empty($allOutletIds)) {
            $activeOutletNames = DB::table('tbl_laporan_bulanan as lb')
                ->join('tbl_outlets as o', 'o.id', '=', 'lb.outlet_id')
                ->whereDate('lb.tanggal', $tanggalHitungOutletAktif)
                ->whereIn('lb.outlet_id', $allOutletIds)
                ->where(function ($q) {
                    $q->where(DB::raw('COALESCE(lb.total_omset, 0)'), '>', 0)
                    ->orWhere(DB::raw('COALESCE(lb.total_cu, 0)'), '>', 0);
                })
                ->pluck('o.nama_outlet')
                ->map(function ($name) use ($normalizeOutletName) {
                    return $normalizeOutletName($name);
                })
                ->filter()
                ->unique()
                ->values();

            $totalOutletAktif = $activeOutletNames->count();
        }

        // // =========================
        // // 5) MAINTENANCE RANGE
        // // =========================
        // if (!empty($tanggalAwal) && !empty($tanggalAkhir)) {
        //     $maintenanceStart = '2026-01-01';
        //     $maintenanceEnd = '2026-02-28';

        //     $isMaintenanceRange = $tanggalAwal <= $maintenanceEnd && $tanggalAkhir >= $maintenanceStart;

        //     if ($isMaintenanceRange) {
        //         return redirect()
        //             ->route('investor.sales.dashboard')
        //             ->with('maintenance', 'Data bulan Januari - Februari sedang dalam maintenance.');
        //     }
        // }

        $filterApplied = !empty($tanggalAwal) && !empty($tanggalAkhir);

        // =========================
        // 6) FILTER OUTLET TERPILIH
        // =========================
        $selectedOutletId = null;
        $selectedOutletIds = $allOutletIds;

        if ($request->filled('outlet')) {
            $requestedOutletId = (int) $request->outlet;

            $selectedOutlet = $outlets->first(function ($outlet) use ($requestedOutletId) {
                return (int) $outlet->id === $requestedOutletId;
            });

            if ($selectedOutlet) {
                $selectedOutletId = $selectedOutlet->id;
                $selectedOutletIds = collect($selectedOutlet->outlet_ids ?? [$selectedOutlet->id])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }

        $applyOutletFilter = function ($query) use ($selectedOutletIds) {
            if (empty($selectedOutletIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('outlet_id', $selectedOutletIds);
        };

        // =========================
        // 7) DEFAULT VARIABLE
        // =========================
        $laporan = collect();
        $totalOmset = 0;
        $totalCU = 0;
        $averageSales = 0;
        $nonSalesTransaction = 0;

        $chartLabels = [];
        $chartOmset = [];
        $chartCU = [];
        $chartOrders = [];
        $chartAOV = [];

        $topProduk = collect();
        $chartHourlyFull = [];
        $chartHourlyOmsetFull = [];

        $takeawayTotal = 0;
        $dineinTotal = 0;

        $totalPerPlatform = [
            'shopeefood' => 0,
            'grabfood' => 0,
            'gofood' => 0,
            'qpon' => 0,
            'cash' => 0,
            'transfer' => 0,
            'qris_bca' => 0,
            'qris_bukupay' => 0,
            'qris_esb' => 0,
            'qris_gopay' => 0,
            'qris_shopeepay' => 0,
            'tiktok_shop' => 0,
        ];

        // =========================
        // 8) QUERY SAAT FILTER AKTIF
        // =========================
        if ($filterApplied && !empty($selectedOutletIds)) {
            // KPI utama
            $kpiQuery = DB::table('tbl_laporan_bulanan')
                ->selectRaw("
                    SUM(COALESCE(total_omset, 0)) AS total_omset,
                    SUM(COALESCE(total_non_sales, 0)) AS total_non_sales,
                    SUM(COALESCE(total_cu, 0)) AS total_cu
                ")
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

            $applyOutletFilter($kpiQuery);

            $kpi = $kpiQuery->first();

            $totalOmset = (float) ($kpi->total_omset ?? 0);
            $totalCU = (float) ($kpi->total_cu ?? 0);
            $nonSalesTransaction = (float) ($kpi->total_non_sales ?? 0);

            // laporan harian
            $laporanQuery = DB::table('tbl_laporan_bulanan')
                ->select(
                    DB::raw('DATE(tanggal) as tanggal'),
                    DB::raw('SUM(COALESCE(total_omset, 0)) as total_omset'),
                    DB::raw('SUM(COALESCE(total_cu, 0)) as total_cu')
                )
                ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

            $applyOutletFilter($laporanQuery);

            $laporan = $laporanQuery
                ->groupBy(DB::raw('DATE(tanggal)'))
                ->orderBy('tanggal', 'asc')
                ->get();

            $chartLabels = $laporan->pluck('tanggal')->map(fn ($v) => (string) $v)->toArray();
            $chartOmset = $laporan->pluck('total_omset')->map(fn ($v) => (float) $v)->toArray();
            $chartCU = $laporan->pluck('total_cu')->map(fn ($v) => (float) $v)->toArray();

            foreach ($laporan as $row) {
                $orders = (float) ($row->total_cu ?? 0);
                $omset = (float) ($row->total_omset ?? 0);

                $chartOrders[] = (int) $orders;
                $chartAOV[] = $orders > 0 ? ($omset / $orders) : null;
            }

            $daysCount = $laporan->pluck('tanggal')->unique()->count();
            $averageSales = $daysCount > 0 ? ($totalOmset / $daysCount) : 0;

            // Top produk
            if (Schema::hasTable('tbl_laporan_pareto')) {
                $topProdukQuery = DB::table('tbl_laporan_pareto')
                    ->select(
                        'item_nama',
                        DB::raw('SUM(total_jumlah) as total_penjualan'),
                        DB::raw('SUM(total_harga) as total_harga')
                    )
                    ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

                $applyOutletFilter($topProdukQuery);

                if ($request->filled('kategori')) {
                    if ($request->kategori === 'PAKET') {
                        $topProdukQuery->where('item_nama', 'like', '%PAKET%');
                    } elseif ($request->kategori === 'ALACARTE') {
                        $topProdukQuery->where('item_nama', 'not like', '%PAKET%');
                    }
                }

                $topProduk = $topProdukQuery
                    ->groupBy('item_nama')
                    ->orderByDesc('total_penjualan')
                    ->get();
            }

            // E-commerce
            if (Schema::hasTable('tbl_laporan_ecommerce')) {
                $ecommerceQuery = DB::table('tbl_laporan_ecommerce')
                    ->selectRaw("
                        CASE
                            WHEN UPPER(item_varian) LIKE '%SHOPEE%' THEN 'shopeefood'
                            WHEN UPPER(item_varian) LIKE '%GRAB%' THEN 'grabfood'
                            WHEN UPPER(item_varian) LIKE '%GOFOOD%' THEN 'gofood'
                            WHEN UPPER(item_varian) LIKE '%GO FOOD%' THEN 'gofood'
                            WHEN UPPER(item_varian) LIKE '%TIKTOK%' THEN 'tiktok_shop'
                            WHEN UPPER(item_varian) LIKE '%TIK TOK%' THEN 'tiktok_shop'
                            WHEN UPPER(item_varian) LIKE '%QPON%' OR UPPER(item_varian) LIKE '%Q-PON%' THEN 'qpon'
                            ELSE NULL
                        END as platform_key,
                        SUM(total_jumlah) as total
                    ")
                    ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

                $applyOutletFilter($ecommerceQuery);

                $ecommerceResults = $ecommerceQuery
                    ->groupBy('platform_key')
                    ->get();

                foreach ($ecommerceResults as $row) {
                    if (!empty($row->platform_key) && isset($totalPerPlatform[$row->platform_key])) {
                        $totalPerPlatform[$row->platform_key] += (float) $row->total;
                    }
                }
            }

            // Offline / payment
            $offlineQuery = DB::table('tbl_transaksi_perhari')
                ->selectRaw("
                    CASE
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%CASH%' THEN 'cash'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%TRANSFER%' THEN 'transfer'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS BCA%' THEN 'qris_bca'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS BUKUPAY%' THEN 'qris_bukupay'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS ESB%' THEN 'qris_esb'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS GOPAY%' THEN 'qris_gopay'
                        WHEN UPPER(COALESCE(tr_metode, '')) LIKE '%QRIS SHOPEEPAY%' THEN 'qris_shopeepay'
                        ELSE NULL
                    END as platform_key,
                    SUM(COALESCE(item_sub_total, 0)) as total
                ")
                ->whereBetween('sesi_tanggal', [$tanggalAwal, $tanggalAkhir])
                ->whereRaw("
                    UPPER(COALESCE(item_varian, '')) NOT LIKE '%SHOPEE%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%GRAB%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%GOFOOD%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%GO FOOD%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%TIKTOK%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%TIK TOK%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%QPON%'
                    AND UPPER(COALESCE(item_varian, '')) NOT LIKE '%Q-PON%'
                ");

            $applyOutletFilter($offlineQuery);

            $offlineResults = $offlineQuery
                ->groupBy('platform_key')
                ->get();

            $offlinePlatformTotals = [
                'cash' => 0,
                'transfer' => 0,
                'qris_bca' => 0,
                'qris_bukupay' => 0,
                'qris_esb' => 0,
                'qris_gopay' => 0,
                'qris_shopeepay' => 0,
                'tiktok_shop' => 0,
            ];

            foreach ($offlineResults as $row) {
                if (!empty($row->platform_key) && isset($offlinePlatformTotals[$row->platform_key])) {
                    $offlinePlatformTotals[$row->platform_key] += (float) $row->total;
                }
            }

            $totalPaymentOfflineRaw = array_sum($offlinePlatformTotals);

            // Normalisasi hanya kalau payment raw lebih besar dari total omset KPI
            // supaya semua metode tetap tampil, tapi totalnya masuk akal
            if ($totalOmset > 0 && $totalPaymentOfflineRaw > $totalOmset) {
                $scale = $totalOmset / $totalPaymentOfflineRaw;

                foreach ($offlinePlatformTotals as $key => $value) {
                    $offlinePlatformTotals[$key] = round($value * $scale, 2);
                }
            }

            foreach ($offlinePlatformTotals as $key => $value) {
                if (isset($totalPerPlatform[$key])) {
                    $totalPerPlatform[$key] += $value;
                }
            }

            // Takeaway & Dine In
            $dineTakeQuery = DB::table('tbl_transaksi_perhari')
                ->selectRaw("
                    SUM(CASE WHEN UPPER(COALESCE(item_varian, '')) LIKE '%TAKEAWAY%' THEN COALESCE(item_sub_total, 0) ELSE 0 END) AS takeaway_total,
                    SUM(CASE
                            WHEN UPPER(COALESCE(item_varian, '')) LIKE '%DINE IN%' THEN COALESCE(item_sub_total, 0)
                            WHEN UPPER(COALESCE(item_varian, '')) LIKE '%DINEIN%' THEN COALESCE(item_sub_total, 0)
                            ELSE 0
                        END) AS dinein_total
                ")
                ->whereBetween('sesi_tanggal', [$tanggalAwal, $tanggalAkhir]);

            $applyOutletFilter($dineTakeQuery);

            $dineTakeRow = $dineTakeQuery->first();
            $takeawayTotal = (float) ($dineTakeRow->takeaway_total ?? 0);
            $dineinTotal = (float) ($dineTakeRow->dinein_total ?? 0);

            // Chart per jam
            $hourlyCountQuery = DB::table('tbl_transaksi_perhari')
                ->select(
                    DB::raw('HOUR(tr_waktu) as jam'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereBetween('sesi_tanggal', [$tanggalAwal, $tanggalAkhir]);

            $applyOutletFilter($hourlyCountQuery);

            $hourlyCount = $hourlyCountQuery
                ->groupBy(DB::raw('HOUR(tr_waktu)'))
                ->orderBy('jam')
                ->get()
                ->pluck('total', 'jam');

            $hourlyOmsetQuery = DB::table('tbl_transaksi_perhari')
                ->select(
                    DB::raw('HOUR(tr_waktu) as jam'),
                    DB::raw('SUM(COALESCE(item_sub_total, 0)) as total')
                )
                ->whereBetween('sesi_tanggal', [$tanggalAwal, $tanggalAkhir]);

            $applyOutletFilter($hourlyOmsetQuery);

            $hourlyOmset = $hourlyOmsetQuery
                ->groupBy(DB::raw('HOUR(tr_waktu)'))
                ->orderBy('jam')
                ->get()
                ->pluck('total', 'jam');

            foreach (range(5, 22) as $h) {
                $chartHourlyFull[$h] = (int) ($hourlyCount[$h] ?? 0);
                $chartHourlyOmsetFull[$h] = (float) ($hourlyOmset[$h] ?? 0);
            }
        } else {
            foreach (range(5, 22) as $h) {
                $chartHourlyFull[$h] = 0;
                $chartHourlyOmsetFull[$h] = 0;
            }
        }

        // =========================
        // 9) NOTIFIKASI TURUN SALES
        // =========================
        $notifikasiTurunSales = collect();

        $tanggalTersedia = DB::table('tbl_laporan_bulanan')
            ->select(DB::raw('DATE(tanggal) as tanggal'))
            ->when(!empty($allOutletIds), function ($q) use ($allOutletIds) {
                $q->whereIn('outlet_id', $allOutletIds);
            })
            ->groupBy(DB::raw('DATE(tanggal)'))
            ->orderByDesc('tanggal')
            ->limit(2)
            ->pluck('tanggal');

        if ($tanggalTersedia->count() >= 2 && !empty($allOutletIds)) {
            $hariIni = $tanggalTersedia[0];
            $kemarin = $tanggalTersedia[1];

            $salesHariIni = DB::table('tbl_laporan_bulanan')
                ->whereDate('tanggal', $hariIni)
                ->whereIn('outlet_id', $allOutletIds)
                ->select('outlet_id', DB::raw('SUM(total_omset) as total'))
                ->groupBy('outlet_id')
                ->get()
                ->keyBy('outlet_id');

            $salesKemarin = DB::table('tbl_laporan_bulanan')
                ->whereDate('tanggal', $kemarin)
                ->whereIn('outlet_id', $allOutletIds)
                ->select('outlet_id', DB::raw('SUM(total_omset) as total'))
                ->groupBy('outlet_id')
                ->get()
                ->keyBy('outlet_id');

            foreach ($salesHariIni as $id => $hariIniData) {
                $totalHariIni = (float) $hariIniData->total;
                $totalKemarin = (float) ($salesKemarin[$id]->total ?? 0);

                if ($totalKemarin > 0 && $totalHariIni < $totalKemarin) {
                    $selisihRupiah = $totalKemarin - $totalHariIni;
                    $persenTurun = round(($selisihRupiah / $totalKemarin) * 100, 1);

                    $outletInfo = $outlets->first(function ($outlet) use ($id) {
                        return in_array((int) $id, $outlet->outlet_ids ?? [], true);
                    });

                    $notifikasiTurunSales->push([
                        'outlet_id' => $id,
                        'nama_outlet' => $outletInfo->nama_outlet_display ?? 'Outlet Tidak Dikenal',
                        'tanggal_terbaru' => $hariIni,
                        'tanggal_pembanding' => $kemarin,
                        'total_kemarin' => number_format($totalKemarin, 0, ',', '.'),
                        'total_hari_ini' => number_format($totalHariIni, 0, ',', '.'),
                        'selisih_rupiah' => number_format($selisihRupiah, 0, ',', '.'),
                        'persen_turun' => $persenTurun,
                    ]);
                }
            }

            $notifikasiTurunSales = $notifikasiTurunSales
                ->sortByDesc('persen_turun')
                ->values();
        }

        return view('Investor.dashboard', compact(
            'outlets',
            'filterApplied',
            'laporan',
            'totalOmset',
            'totalCU',
            'averageSales',
            'nonSalesTransaction',
            'chartLabels',
            'chartOmset',
            'chartCU',
            'chartOrders',
            'chartAOV',
            'topProduk',
            'chartHourlyFull',
            'chartHourlyOmsetFull',
            'totalPerPlatform',
            'takeawayTotal',
            'dineinTotal',
            'totalOutletUnik',
            'totalOutletAktif',
            'notifikasiTurunSales',
            'tanggalAwal',
            'tanggalAkhir',
            'selectedOutletId'
        ));
    }

    // DASHBOARD BOD & HELPERS
    protected EsbClient $esbClient;

    public function __construct(EsbClient $esbClient)
    {
        $this->esbClient = $esbClient;
    }

    private function classifyAccountGroupFromRow(array $row): string
    {
        $accountNo = preg_replace('/\s+/', '', trim((string) ($row['accountNo'] ?? '')));
        $description = strtolower(trim((string) (
            $row['accountDescriptionSystem']
            ?? $row['accountDescription']
            ?? $row['description_system']
            ?? $row['description']
            ?? ''
        )));

        if ($accountNo === '') {
            return 'lainnya';
        }

        // 4104xx = pendapatan/penghasilan lainnya
        if (str_starts_with($accountNo, '4104')) {
            return 'biaya_lainnya';
        }

        // penyusutan / depresiasi / amortisasi dipisah dari opex
        if (
            str_contains($description, 'depresiasi') ||
            str_contains($description, 'penyusutan') ||
            str_contains($description, 'amortisasi')
        ) {
            return 'depresiasi';
        }

        $first = substr($accountNo, 0, 1);

        return match ($first) {
            '4' => 'pendapatan',
            '5' => 'hpp',
            '6' => 'opex',
            default => 'lainnya',
        };
    }

    private function summarizeGlByGroup(array $rows): array
    {
        $summary = [
            'pendapatan' => 0.0,
            'hpp' => 0.0,
            'opex' => 0.0,
            'biaya_lainnya' => 0.0,
            'depresiasi' => 0.0,
            'lainnya' => 0.0,
        ];
    
        foreach ($rows as $row) {
            $group = $this->classifyAccountGroupFromRow($row);
    
            $debit  = $this->parseIdrToFloat($row['debitAmount'] ?? 0);
            $credit = $this->parseIdrToFloat($row['creditAmount'] ?? 0);
    
            $amount = in_array($group, ['pendapatan', 'biaya_lainnya'], true)
                ? ($credit - $debit)
                : ($debit - $credit);
    
            $summary[$group] += $amount;
        }
    
        return $summary;
    }

    private function parseIdrToFloat($value): float
    {
        if ($value === null) {
            return 0.0;
        }
    
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
    
        $s = trim((string) $value);
        if ($s === '') {
            return 0.0;
        }
    
        $s = str_replace(["Rp", "rp", " "], '', $s);
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^0-9.\-]/', '', $s);
    
        return is_numeric($s) ? (float) $s : 0.0;
    }

    private function resolveEsbTokenKeyFromOutlet(object $outlet): string
    {
        return config('services.esb.default_token_key', 'OKNHO');
    }

    private function defaultBodViewPayload($outlets, bool $filterApplied, string $ym, Carbon $monthStart, Carbon $prevYearStart, string $outletId): array
    {
        return [
            'outlets' => $outlets,
            'filterApplied' => $filterApplied,

            'laporan' => collect(),
            'totalOmset' => 0,
            'totalCU' => 0,

            'zonaByArea' => [],
            'zonaTotals' => [
                'Z1' => [0, '0,00%'],
                'Z2' => [0, '0,00%'],
                'Z3' => [0, '0,00%'],
                'Z4' => [0, '0,00%'],
            ],
            'zPerformance' => ['Z1' => 0, 'Z2' => 0, 'Z3' => 0, 'Z4' => 0],
            'zoneOutlets' => ['Z1' => [], 'Z2' => [], 'Z3' => [], 'Z4' => []],

            'komparasi' => [
                'labels' => [(string) $prevYearStart->year, (string) $monthStart->year],
                'omset' => [0, 0],
                'gross' => [0, 0],
                'net' => [0, 0],
                'ebitda' => [0, 0],
                'notes' => [
                    'Kenaikan Omset' => '-',
                    'Net Income' => '-',
                    'EBITDA' => '-',
                ],
            ],

            'navigatorLabels' => ['OMSET', 'HPP', 'GROSS', 'NET INCOME', 'EBITDA', 'OUTLET GO', 'VARIANCE', 'INTERNAL AUDIT'],
            'navigatorValues' => [0, 0, 0, 0, 0, 0, 0, 0],
            'navigatorRaw' => [
                'omset' => 0,

                'pendapatan' => 0,
                'hpp' => 0,
                'opex' => 0,
                'biaya_lainnya' => 0,
                'gross' => 0,
                'ebitda' => 0,
                'net_income' => 0,
                'dep' => 0,
                'npm' => 0,

                'hpp_available' => false,
                'gross_estimated' => true,

                'net_available' => false,
                'net_income_estimated' => true,

                'finance_coverage' => '0/0',
                'finance_coverage_pct' => 0,

                'opening_pct' => 0,
                'variance_pct' => 0,
                'audit_coverage_pct' => 0,

                'sales_gl' => 0,
                'gl_count' => 0,

                'debug_branch_codes' => [],
                'debug_gl_count' => 0,
                'debug_gl_raw_count' => 0,
                'debug_gl_mapped_count' => 0,
                'debug_sales_gl' => 0,
                'debug_hpp_gl' => 0,
                'debug_gl_pendapatan' => 0,
                'debug_gl_hpp' => 0,
                'debug_gl_opex' => 0,
                'debug_gl_biaya_lainnya' => 0,
            ],
            'navigatorShow' => true,

            'ym' => $ym,
            'periodYm' => $ym,
            'periodeText' => $monthStart->translatedFormat('F Y'),
            'mom' => ['pct' => null, 'delta' => 0, 'label' => '-'],
            'yoy' => ['pct' => null, 'delta' => 0, 'label' => $prevYearStart->translatedFormat('M Y')],

            'outletId' => $outletId,
            'month' => (int) $monthStart->format('m'),
            'year' => (int) $monthStart->format('Y'),
        ];
    }

    public function indexBOD(Request $request)
    {
        $cacheKey = 'dashboard_bod_csv_v4';

        $rows = Cache::store('redis')->remember($cacheKey, 3600, function () {
            $actualRows = $this->loadDashboardRowsFromFirstSheetCsv();
            $targetMap  = $this->loadTargetMapFromSheet();

            return $this->mergeActualWithTarget($actualRows, $targetMap);
        });

        $rows = $this->applyZonaByCityFinancialHealth($rows);

        $filters = [
            'tanggal_awal'  => $request->get('tanggal_awal'),
            'tanggal_akhir' => $request->get('tanggal_akhir'),
            'outlet'        => $request->get('outlet'),
            'kota'          => $request->get('kota'),
            'am'            => $request->get('am'),
            'zona'          => $request->get('zona'),
        ];

        $filteredRows = $this->filterDashboardRows($rows, $filters);

        $dashboard = $this->buildDashboardFromRows($filteredRows, $request);

        $kotaRaw        = $dashboard['kota'] ?? [];
        $amRaw          = $dashboard['am'] ?? [];
        $outletBoardRaw = $dashboard['outletBoard'] ?? [];
        $zonaChart      = $dashboard['zonaChart'] ?? [];

        if ($outletBoardRaw instanceof \Illuminate\Contracts\Pagination\Paginator || $outletBoardRaw instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $outletBoardRaw = collect($outletBoardRaw->items())->values()->toArray();
        } elseif ($outletBoardRaw instanceof \Illuminate\Support\Collection) {
            $outletBoardRaw = $outletBoardRaw->values()->toArray();
        } else {
            $outletBoardRaw = collect($outletBoardRaw)->values()->toArray();
        }

        $kotaRaw = collect($kotaRaw)->values()->toArray();
        $amRaw   = collect($amRaw)->values()->toArray();

        $kota        = $this->paginateArray($kotaRaw, 10, 'kota_page');
        $am          = $this->paginateArray($amRaw, 10, 'am_page');
        $outletBoard = $this->paginateArray($outletBoardRaw, 10, 'outlet_page');

        if ($request->ajax()) {
            $section = $request->get('section');

            if ($section === 'kota') {
                return view('Investor.partials._table_kota', compact('kota'))->render();
            }

            if ($section === 'am') {
                return view('Investor.partials._table_am', compact('am'))->render();
            }

            if ($section === 'outlet_board') {
                return view('Investor.partials._table_outlet_board', compact('outletBoard'))->render();
            }

            if ($section === 'zona_detail') {
                $selectedZona = $request->get('selected_zona');
                $zonaItem = collect($zonaChart)->firstWhere('zona', $selectedZona);

                $zonaOutletsRaw = collect($zonaItem['outlets'] ?? [])
                    ->sortByDesc(fn ($row) => (float) ($row['total_sales'] ?? 0))
                    ->values()
                    ->toArray();

                $zonaOutlets = $this->paginateArray($zonaOutletsRaw, 10, 'zona_page');

                return view('Investor.partials._table_zona_detail', [
                    'zonaOutlets'  => $zonaOutlets,
                    'selectedZona' => $selectedZona,
                ])->render();
            }
        }

        $filterOptions = [
            'outlets' => array_map(fn ($item) => [
                'id'   => $item['id'],
                'text' => $item['nama_outlet_clean'],
            ], $this->buildOutletOptions($rows)),
            'kota' => $this->uniqueFieldOptions($rows, 'kota'),
            'am'   => $this->uniqueFieldOptions($rows, 'nama_am'),
            'zona' => ['Z1', 'Z2', 'Z3', 'Z4'],
        ];

        return view('Investor.dashboardBOD', array_merge($dashboard, [
            'kota'             => $kota,
            'am'               => $am,
            'outletBoard'      => $outletBoard,
            'filters'          => $filters,
            'filterOptions'    => $filterOptions,
            'outlets'          => $this->buildOutletOptions($rows),
            'sheetOptions'     => [],
            'activeSheetKey'   => 'sheet-1',
            'activeSheetLabel' => 'Sheet 1',
            'sheetColumns'     => [],
            'lastSyncAt'       => now()->format('Y-m-d H:i:s'),
        ]));
    }

    private function filterDashboardRows(array $rows, array $filters): array
    {
        $filteredRows = [];

        foreach ($rows as $row) {
            $tanggal = $row['tanggal'] ?? null;

            if (!empty($filters['tanggal_awal'])) {
                if (!$tanggal || $tanggal < $filters['tanggal_awal']) {
                    continue;
                }
            }

            if (!empty($filters['tanggal_akhir'])) {
                if (!$tanggal || $tanggal > $filters['tanggal_akhir']) {
                    continue;
                }
            }

            if (!empty($filters['outlet']) && ($row['id'] ?? '') !== $filters['outlet']) {
                continue;
            }

            if (!empty($filters['kota']) && ($row['kota'] ?? '') !== $filters['kota']) {
                continue;
            }

            if (!empty($filters['am']) && ($row['nama_am'] ?? '') !== $filters['am']) {
                continue;
            }

            if (!empty($filters['zona']) && ($row['zona'] ?? '') !== $filters['zona']) {
                continue;
            }

            $filteredRows[] = $row;
        }

        return $filteredRows;
    }

    private function rowMatchPeriodeFilter(array $row, array $filters): bool
    {
        $mode    = $filters['mode_periode'] ?? 'range';
        $tanggal = $row['tanggal'] ?? null;
        $periode = $row['periode'] ?? null; // format Y-m

        if ($mode === 'bulanan') {
            $bulan = trim((string) ($filters['bulan'] ?? ''));
            $tahun = trim((string) ($filters['tahun'] ?? ''));

            if ($bulan === '' && $tahun === '') {
                return true;
            }

            if (empty($periode) || $periode === 'unknown') {
                return false;
            }

            [$rowYear, $rowMonth] = explode('-', $periode);

            if ($tahun !== '' && $rowYear !== $tahun) {
                return false;
            }

            if ($bulan !== '' && $rowMonth !== str_pad($bulan, 2, '0', STR_PAD_LEFT)) {
                return false;
            }

            return true;
        }

        // mode range
        $tanggalAwal  = $filters['tanggal_awal'] ?? null;
        $tanggalAkhir = $filters['tanggal_akhir'] ?? null;

        if ($tanggalAwal && (!$tanggal || $tanggal < $tanggalAwal)) {
            return false;
        }

        if ($tanggalAkhir && (!$tanggal || $tanggal > $tanggalAkhir)) {
            return false;
        }

        return true;
    }

    private function buildTahunOptions(array $rows): array
    {
        $years = [];

        foreach ($rows as $row) {
            $periode = $row['periode'] ?? null;

            if ($periode && $periode !== 'unknown' && preg_match('/^\d{4}-\d{2}$/', $periode)) {
                $year = substr($periode, 0, 4);
                $years[$year] = $year;
            }
        }

        $years = array_values($years);
        rsort($years);

        return $years;
    }

    protected function paginateArray(array $items, int $perPage = 10, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
        $collection = Collection::make($items);
        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $results,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    }

    private function loadTargetMapFromSheet(): array
    {
        try {
            $sheetId = '1IwMlVIBQ0UQBlXEYnhF4cyR_yrCrmjOEQ2AbVawNK6Q';

            // pakai nama sheet, jangan gid hardcode
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/gviz/tq?tqx=out:csv&sheet=Target";

            $response = Http::timeout(20)->get($csvUrl);

            if (! $response->successful()) {
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            if (!$lines || count($lines) < 2) {
                return [];
            }

            $rows = array_map('str_getcsv', $lines);
            $headers = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);

            if (empty($headers)) {
                return [];
            }

            $result = [];

            foreach (array_slice($rows, 1) as $row) {
                if (count($row) < count($headers)) {
                    continue;
                }

                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) {
                    continue;
                }

                $namaOutlet = trim((string) (
                    $assoc['Nama Outlet'] ??
                    $assoc['Outlet'] ??
                    $assoc['Nama Store'] ??
                    ''
                ));

                if ($namaOutlet === '' || $namaOutlet === '#N/A') {
                    continue;
                }

                $bulan = trim((string) ($assoc['Bulan'] ?? ''));
                $tahun = trim((string) ($assoc['Tahun'] ?? ''));

                $periode = $this->normalizePeriodeKey(
                    $assoc['Tanggal'] ?? null,
                    $bulan,
                    $tahun
                );

                $target = $this->toFloatId(
                    $assoc['Target'] ??
                    $assoc['TARGET'] ??
                    $assoc['Target Omset'] ??
                    $assoc['Sales Target'] ??
                    0
                );

                $key = md5(mb_strtolower($namaOutlet)) . '|' . $periode;

                $result[$key] = [
                    'target' => $target,
                    'nama_outlet' => $namaOutlet,
                    'periode' => $periode,
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('BOD target sheet load failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [];
        }
    }

    private function normalizePeriodeKey($tanggal = null, $bulan = null, $tahun = null): string
    {
        if (!empty($tanggal)) {
            try {
                return \Carbon\Carbon::parse($tanggal)->format('Y-m');
            } catch (\Throwable $e) {
            }
        }

        if ($bulan && $tahun) {
            try {
                return \Carbon\Carbon::createFromFormat(
                    'F Y',
                    $this->indoMonthToEnglish($bulan) . ' ' . trim((string) $tahun)
                )->format('Y-m');
            } catch (\Throwable $e) {
            }
        }

        return 'unknown';
    }

    private function mergeActualWithTarget(array $actualRows, array $targetMap): array
    {
        foreach ($actualRows as &$row) {
            $periode = $row['periode'] ?? $this->normalizePeriodeKey(
                $row['tanggal'] ?? null,
                null,
                null
            );

            $outletId = $row['id'] ?? md5(mb_strtolower(trim((string) ($row['nama_outlet'] ?? ''))));
            $lookupKey = $outletId . '|' . $periode;

            $target = (float) ($targetMap[$lookupKey]['target'] ?? 0);
            $omset  = (float) ($row['omset'] ?? 0);

            $row['target']      = $target;
            $row['variance']    = $target > 0 ? ($target - $omset) : 0;
            $row['achievement'] = $target > 0 ? (($omset / $target) * 100) : 0;
        }
        unset($row);

        return $actualRows;
    }

    private function loadDashboardRowsFromFirstSheetCsv(): array
    {
        try {
            $sheetId = '1IwMlVIBQ0UQBlXEYnhF4cyR_yrCrmjOEQ2AbVawNK6Q';
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid=0";

            $response = Http::timeout(20)->get($csvUrl);

            if (! $response->successful()) {
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            if (!$lines || count($lines) < 2) {
                return [];
            }

            $rows = array_map('str_getcsv', $lines);
            $headers = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);

            if (empty($headers)) {
                return [];
            }

            $result = [];

            foreach (array_slice($rows, 1) as $row) {
                if (count($row) < count($headers)) {
                    continue;
                }

                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) {
                    continue;
                }

                $mapped = $this->normalizeFirstSheetRow($assoc);
                if ($mapped) {
                    $result[] = $mapped;
                }
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('BOD CSV load failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [];
        }
    }

    private function normalizeFirstSheetRow(array $row): ?array
    {
        $namaOutlet = trim((string) ($row['Nama Outlet'] ?? ''));
        $kota = trim((string) ($row['Kota'] ?? ''));
        $namaAm = trim((string) ($row['Nama AM'] ?? ''));
        $area = trim((string) ($row['Area'] ?? ''));

        if ($namaOutlet === '' || $namaOutlet === '#N/A') {
            return null;
        }

        $tanggal = $this->normalizeTanggalIndonesia(
            $row['Tanggal Omset'] ?? null,
            $row['Bulan'] ?? null,
            $row['Tahun'] ?? null
        );

        $periode = $tanggal
            ? \Carbon\Carbon::parse($tanggal)->format('Y-m')
            : $this->normalizePeriodeKey(
                null,
                $row['Bulan'] ?? null,
                $row['Tahun'] ?? null
            );

        $omset = $this->toFloatId($row['Omset'] ?? 0);
        $cu = $this->toFloatId($row['Customer Unit (CU)'] ?? 0);
        $avgCheck = $this->toFloatId($row['Average Check'] ?? 0);

        return [
            'tanggal' => $tanggal,
            'periode' => $periode,
            'nama_outlet' => $namaOutlet,
            'nama_outlet_clean' => $namaOutlet,
            'id' => md5(mb_strtolower($namaOutlet)),
            'kota' => $kota === '#N/A' ? '' : $kota,
            'nama_am' => $namaAm === '#N/A' ? '' : $namaAm,
            'area' => $area === '#N/A' ? '' : $area,
            'omset' => $omset,
            'cu' => $cu,
            'avg_check' => $avgCheck,
            'target' => 0,
            'variance' => 0,
            'achievement' => 0,
            'zona' => null,
            'raw' => $row,
        ];
    }

    private function applyZonaByCityFinancialHealth(array $rows): array
    {
        $cityStats = [];

        foreach ($rows as $row) {
            $kota = trim((string) ($row['kota'] ?? ''));
            if ($kota === '') {
                $kota = 'Lainnya';
            }

            if (!isset($cityStats[$kota])) {
                $cityStats[$kota] = [
                    'omset' => 0,
                    'target' => 0,
                ];
            }

            $cityStats[$kota]['omset'] += (float) ($row['omset'] ?? 0);
            $cityStats[$kota]['target'] += (float) ($row['target'] ?? 0);
        }

        $cityHealth = [];
        foreach ($cityStats as $kota => $stat) {
            $healthScore = $stat['target'] > 0
                ? ($stat['omset'] / $stat['target']) * 100
                : (float) $stat['omset'];

            $cityHealth[] = [
                'kota' => $kota,
                'score' => $healthScore,
            ];
        }

        usort($cityHealth, fn ($a, $b) => $b['score'] <=> $a['score']);

        $count = count($cityHealth);
        if ($count === 0) {
            return $rows;
        }

        $cityZonaMap = [];

        foreach ($cityHealth as $index => $item) {
            $percentile = ($index + 1) / $count;

            if ($percentile <= 0.25) {
                $zona = 'Z1';
            } elseif ($percentile <= 0.50) {
                $zona = 'Z2';
            } elseif ($percentile <= 0.75) {
                $zona = 'Z3';
            } else {
                $zona = 'Z4';
            }

            $cityZonaMap[$item['kota']] = $zona;
        }

        foreach ($rows as &$row) {
            $kota = trim((string) ($row['kota'] ?? ''));
            if ($kota === '') {
                $kota = 'Lainnya';
            }

            $row['zona'] = $cityZonaMap[$kota] ?? 'Z4';
        }
        unset($row);

        return $rows;
    }

    private function buildDashboardFromRows(array $rows, Request $request): array
    {
        $cards = [
            'omset'         => 0,
            'avg_sales_day' => 0,
            'target'        => 0,
            'total_cu'      => 0,
            'variance'      => 0,
            'avg_check'     => 0,
            'achievement'   => 0,
        ];

        $dailySales = [];
        $kotaMap = [];
        $amMap = [];
        $outletMap = [];
        $zonaMap = [];

        $avgCheckTotal = 0;
        $avgCheckCount = 0;

        // penting: target unique per outlet + periode
        $uniqueTargetMap = [];

        foreach ($rows as $row) {
            $omset      = (float) ($row['omset'] ?? 0);
            $cu         = (float) ($row['cu'] ?? 0);
            $avgCheck   = (float) ($row['avg_check'] ?? 0);
            $target     = (float) ($row['target'] ?? 0);
            $tanggal    = $row['tanggal'] ?? null;
            $periode    = $row['periode'] ?? 'unknown';
            $outletId   = $row['id'] ?? md5(mb_strtolower(trim((string) ($row['nama_outlet'] ?? ''))));
            $outletName = !empty($row['nama_outlet']) ? $row['nama_outlet'] : '-';

            $cards['omset'] += $omset;
            $cards['total_cu'] += $cu;

            if (!empty($tanggal)) {
                $dailySales[$tanggal] = ($dailySales[$tanggal] ?? 0) + $omset;
            }

            if ($avgCheck > 0) {
                $avgCheckTotal += $avgCheck;
                $avgCheckCount++;
            }

            // target unique per outlet + periode
            $targetKey = $outletId . '|' . $periode;
            if (!isset($uniqueTargetMap[$targetKey])) {
                $uniqueTargetMap[$targetKey] = $target;
            }

            // =========================
            // Kota
            // =========================
            $kotaKey = !empty($row['kota']) ? $row['kota'] : 'Lainnya';

            if (!isset($kotaMap[$kotaKey])) {
                $kotaMap[$kotaKey] = [
                    'nama' => $kotaKey,
                    'omset' => 0,
                ];
            }

            $kotaMap[$kotaKey]['omset'] += $omset;

            // =========================
            // AM
            // =========================
            $amKey = !empty($row['nama_am']) ? $row['nama_am'] : '-';

            if (!isset($amMap[$amKey])) {
                $amMap[$amKey] = [
                    'nama' => $amKey,
                    'omset' => 0,
                ];
            }

            $amMap[$amKey]['omset'] += $omset;

            // =========================
            // Outlet
            // =========================
            if (!isset($outletMap[$outletName])) {
                $outletMap[$outletName] = [
                    'id'               => $outletId,
                    'nama_outlet'      => $outletName,
                    'nama_outlet_clean'=> $row['nama_outlet_clean'] ?? $outletName,
                    'omset'            => 0,
                    'cu'               => 0,
                    'avg_check_total'  => 0,
                    'avg_check_count'  => 0,
                    'target_keys'      => [],
                ];
            }

            $outletMap[$outletName]['omset'] += $omset;
            $outletMap[$outletName]['cu'] += $cu;

            if ($avgCheck > 0) {
                $outletMap[$outletName]['avg_check_total'] += $avgCheck;
                $outletMap[$outletName]['avg_check_count']++;
            }

            // target unique per outlet/periode untuk outlet summary
            if (!isset($outletMap[$outletName]['target_keys'][$targetKey])) {
                $outletMap[$outletName]['target_keys'][$targetKey] = $target;
            }

            // =========================
            // Zona
            // =========================
            $zonaKey = !empty($row['zona']) ? $row['zona'] : 'Z4';

            if (!isset($zonaMap[$zonaKey])) {
                $zonaMap[$zonaKey] = [
                    'zona'            => $zonaKey,
                    'total_sales'     => 0,
                    'total_cu'        => 0,
                    'avg_check_total' => 0,
                    'avg_check_count' => 0,
                    'target_keys'     => [],
                    'outlets'         => [],
                ];
            }

            $zonaMap[$zonaKey]['total_sales'] += $omset;
            $zonaMap[$zonaKey]['total_cu'] += $cu;

            if ($avgCheck > 0) {
                $zonaMap[$zonaKey]['avg_check_total'] += $avgCheck;
                $zonaMap[$zonaKey]['avg_check_count']++;
            }

            if (!isset($zonaMap[$zonaKey]['target_keys'][$targetKey])) {
                $zonaMap[$zonaKey]['target_keys'][$targetKey] = $target;
            }

            if (!isset($zonaMap[$zonaKey]['outlets'][$outletName])) {
                $zonaMap[$zonaKey]['outlets'][$outletName] = [
                    'nama_outlet'     => $outletName,
                    'total_sales'     => 0,
                    'cu'              => 0,
                    'avg_check_total' => 0,
                    'avg_check_count' => 0,
                    'target_keys'     => [],
                ];
            }

            $zonaMap[$zonaKey]['outlets'][$outletName]['total_sales'] += $omset;
            $zonaMap[$zonaKey]['outlets'][$outletName]['cu'] += $cu;

            if ($avgCheck > 0) {
                $zonaMap[$zonaKey]['outlets'][$outletName]['avg_check_total'] += $avgCheck;
                $zonaMap[$zonaKey]['outlets'][$outletName]['avg_check_count']++;
            }

            if (!isset($zonaMap[$zonaKey]['outlets'][$outletName]['target_keys'][$targetKey])) {
                $zonaMap[$zonaKey]['outlets'][$outletName]['target_keys'][$targetKey] = $target;
            }
        }

        // =========================
        // Cards final
        // =========================
        $cards['target'] = array_sum($uniqueTargetMap);

        $cards['avg_sales_day'] = count($dailySales) > 0
            ? array_sum($dailySales) / count($dailySales)
            : 0;

        $cards['avg_check'] = $avgCheckCount > 0
            ? $avgCheckTotal / $avgCheckCount
            : 0;

        $cards['variance'] = $cards['target'] > 0
            ? $cards['target'] - $cards['omset']
            : 0;

        $cards['achievement'] = $cards['target'] > 0
            ? ($cards['omset'] / $cards['target']) * 100
            : 0;

        // =========================
        // Kota list
        // =========================
        $kota = array_values($kotaMap);
        usort($kota, fn ($a, $b) => $b['omset'] <=> $a['omset']);
        $kota = array_slice($kota, 0, 10);

        // =========================
        // AM list
        // =========================
        $am = array_values($amMap);
        usort($am, fn ($a, $b) => $b['omset'] <=> $a['omset']);
        $am = array_slice($am, 0, 10);

        // =========================
        // Outlet list
        // =========================
        $outlets = [];

        foreach ($outletMap as $item) {
            $targetOutlet = array_sum($item['target_keys'] ?? []);
            $omsetOutlet  = (float) ($item['omset'] ?? 0);

            $outlets[] = [
                'id'               => $item['id'],
                'nama_outlet'      => $item['nama_outlet'],
                'nama_outlet_clean'=> $item['nama_outlet_clean'],
                'omset'            => $omsetOutlet,
                'cu'               => (float) ($item['cu'] ?? 0),
                'target'           => $targetOutlet,
                'variance'         => $targetOutlet > 0 ? ($targetOutlet - $omsetOutlet) : 0,
                'achievement'      => $targetOutlet > 0 ? ($omsetOutlet / $targetOutlet) * 100 : 0,
                'avg_check'        => ($item['avg_check_count'] ?? 0) > 0
                    ? $item['avg_check_total'] / $item['avg_check_count']
                    : 0,
            ];
        }

        usort($outlets, fn ($a, $b) => strcmp($a['nama_outlet'], $b['nama_outlet']));

        $outletBoardSorted = $outlets;
        usort($outletBoardSorted, fn ($a, $b) => $b['omset'] <=> $a['omset']);

        // di-return sebagai array dulu, nanti dipaginate lagi di indexBOD
        $outletBoard = $outletBoardSorted;

        // =========================
        // Zona chart
        // =========================
        $zonaChart = [];

        foreach ($zonaMap as $zona) {
            $zonaOutlets = [];

            foreach ($zona['outlets'] as $outlet) {
                $outletSales    = (float) ($outlet['total_sales'] ?? 0);
                $outletTarget   = array_sum($outlet['target_keys'] ?? []);
                $outletAvgCheck = ($outlet['avg_check_count'] ?? 0) > 0
                    ? $outlet['avg_check_total'] / $outlet['avg_check_count']
                    : 0;

                $zonaOutlets[] = [
                    'nama_outlet' => $outlet['nama_outlet'],
                    'total_sales' => $outletSales,
                    'target'      => $outletTarget,
                    'variance'    => $outletTarget > 0 ? ($outletTarget - $outletSales) : 0,
                    'achievement' => $outletTarget > 0 ? ($outletSales / $outletTarget) * 100 : 0,
                    'cu'          => (float) ($outlet['cu'] ?? 0),
                    'avg_check'   => $outletAvgCheck,
                    'kontribusi'  => ($zona['total_sales'] ?? 0) > 0
                        ? ($outletSales / $zona['total_sales']) * 100
                        : 0,
                ];
            }

            usort($zonaOutlets, fn ($a, $b) => $b['total_sales'] <=> $a['total_sales']);

            $zonaTotalSales  = (float) ($zona['total_sales'] ?? 0);
            $zonaTotalTarget = array_sum($zona['target_keys'] ?? []);

            $zonaChart[] = [
                'zona'           => $zona['zona'],
                'total_sales'    => $zonaTotalSales,
                'total_target'   => $zonaTotalTarget,
                'total_variance' => $zonaTotalTarget > 0 ? ($zonaTotalTarget - $zonaTotalSales) : 0,
                'achievement'    => $zonaTotalTarget > 0 ? ($zonaTotalSales / $zonaTotalTarget) * 100 : 0,
                'total_cu'       => (float) ($zona['total_cu'] ?? 0),
                'avg_check'      => ($zona['avg_check_count'] ?? 0) > 0
                    ? $zona['avg_check_total'] / $zona['avg_check_count']
                    : 0,
                'outlet_count'   => count($zonaOutlets),
                'outlets'        => $zonaOutlets,
            ];
        }

        usort($zonaChart, fn ($a, $b) => strcmp($a['zona'], $b['zona']));

        return compact('cards', 'kota', 'am', 'outlets', 'outletBoard', 'zonaChart');
    }

    private function buildOutletOptions(array $rows): array
    {
        $map = [];

        foreach ($rows as $row) {
            $name = $row['nama_outlet'] ?? '-';
            $id = $row['id'] ?? md5($name);

            $map[$id] = [
                'id' => $id,
                'nama_outlet' => $name,
                'nama_outlet_clean' => $row['nama_outlet_clean'] ?? $name,
            ];
        }

        $items = array_values($map);
        usort($items, fn ($a, $b) => strcmp($a['nama_outlet_clean'], $b['nama_outlet_clean']));

        return $items;
    }

    private function uniqueFieldOptions(array $rows, string $field): array
    {
        $items = [];

        foreach ($rows as $row) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '') {
                $items[$value] = $value;
            }
        }

        $items = array_values($items);
        sort($items);

        return $items;
    }

    private function normalizeTanggalIndonesia($value, $bulan = null, $tahun = null): ?string
    {
        if ($value !== null) {
            $raw = trim((string) $value);

            if ($raw !== '' && $raw !== '#N/A') {
                $bulanMap = [
                    'januari' => 'January',
                    'februari' => 'February',
                    'maret' => 'March',
                    'april' => 'April',
                    'mei' => 'May',
                    'juni' => 'June',
                    'juli' => 'July',
                    'agustus' => 'August',
                    'september' => 'September',
                    'oktober' => 'October',
                    'november' => 'November',
                    'desember' => 'December',
                ];

                $normalized = str_ireplace(array_keys($bulanMap), array_values($bulanMap), $raw);
                $normalized = str_replace('/', '-', $normalized);

                try {
                    if (preg_match('/^\d{1,2}-[A-Za-z]+$/', $normalized) && $tahun) {
                        $normalized .= '-' . trim((string) $tahun);
                    }

                    return Carbon::parse($normalized)->format('Y-m-d');
                } catch (\Throwable $e) {
                }
            }
        }

        if ($bulan && $tahun) {
            try {
                return Carbon::createFromFormat(
                    'F Y',
                    $this->indoMonthToEnglish($bulan) . ' ' . trim((string) $tahun)
                )->startOfMonth()->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }

        return null;
    }

    private function indoMonthToEnglish($bulan): string
    {
        $map = [
            'Januari' => 'January',
            'Februari' => 'February',
            'Maret' => 'March',
            'April' => 'April',
            'Mei' => 'May',
            'Juni' => 'June',
            'Juli' => 'July',
            'Agustus' => 'August',
            'September' => 'September',
            'Oktober' => 'October',
            'November' => 'November',
            'Desember' => 'December',
        ];

        return $map[trim((string) $bulan)] ?? trim((string) $bulan);
    }

    private function toFloatId($value): float
    {
        if ($value === null) return 0;
    
        $value = (string) $value;
    
        // hapus Rp dan spasi
        $value = str_replace(['Rp', 'rp', ' '], '', $value);
    
        // hapus titik ribuan
        $value = str_replace('.', '', $value);
    
        // ubah koma ke titik (desimal)
        $value = str_replace(',', '.', $value);
    
        return is_numeric($value) ? (float) $value : 0;
    }

    private function mapZonaFromKota($kota): string
    {
        $mapping = [
            'Surabaya' => 'Z1',
            'Sidoarjo' => 'Z1',
            'Gresik' => 'Z1',
            'Lamongan' => 'Z2',
            'Mojokerto' => 'Z2',
            'Pasuruan' => 'Z2',
            'Madiun' => 'Z3',
            'Magetan' => 'Z3',
            'Bojonegoro' => 'Z3',
            'Jember' => 'Z4',
            'Bangkalan' => 'Z4',
            'Tuban' => 'Z4',
            'Tulungagung' => 'Z4',
        ];

        return $mapping[trim((string) $kota)] ?? 'Lainnya';
    }
    
    public function bodSalesComparison(Request $request)
    {
        $cacheKey = 'dashboard_bod_csv_v4';

        $rows = Cache::store('redis')->remember($cacheKey, 120, function () {
            $actualRows = $this->loadDashboardRowsFromFirstSheetCsv();
            $targetMap = $this->loadTargetMapFromSheet();

            return $this->mergeActualWithTarget($actualRows, $targetMap);
        });

        $rows = $this->applyZonaByCityFinancialHealth($rows);

        $filters = [
            'tahun_omset' => $request->get('tahun_omset'),
            'bulan'       => $request->get('bulan'),
        ];

        $comparison = $this->buildSalesComparisonFromRows($rows, $filters);

        return view('Investor.dashboardSalesComparison', [
            'title'           => 'Dashboard BOD - Sales Comparison',
            'type'            => 'sales_comparison',
            'filters'         => $filters,
            'monthLabels'     => $comparison['monthLabels'],
            'avgData'         => $comparison['avgData'],
            'omsetData'       => $comparison['omsetData'],
            'outletData'      => $comparison['outletData'],
            'goYearOptions'   => $comparison['goYearOptions'],
            'tahunOmsetOptions' => $comparison['tahunOmsetOptions'],
            'lastSyncAt'      => now()->format('Y-m-d H:i:s'),
        ]);
    }
    
    private function buildSalesComparisonFromRows(array $rows, array $filters = []): array
    {
        $monthLabels = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $bucket = [];
        $goYearOptions = [];
        $tahunOmsetOptions = [];

        foreach ($rows as $row) {
            $tanggal = $row['tanggal'] ?? null;
            if (!$tanggal) {
                continue;
            }

            try {
                $date = \Carbon\Carbon::parse($tanggal);
            } catch (\Throwable $e) {
                continue;
            }

            $tahunOmset = (int) $date->format('Y');
            $bulanOmset = (int) $date->format('n');
            $tahunGO = $this->extractGoYear($row);

            // fallback sementara supaya chart tetap muncul
            if (!$tahunGO) {
                $tahunGO = $tahunOmset;
            }

            $tahunOmsetOptions[$tahunOmset] = $tahunOmset;
            $goYearOptions[$tahunGO] = $tahunGO;

            if (!empty($filters['tahun_omset']) && (int) $filters['tahun_omset'] !== $tahunOmset) {
                continue;
            }

            if (!empty($filters['bulan']) && (int) $filters['bulan'] !== $bulanOmset) {
                continue;
            }

            if (!isset($bucket[$tahunGO])) {
                $bucket[$tahunGO] = [];
            }

            if (!isset($bucket[$tahunGO][$bulanOmset])) {
                $bucket[$tahunGO][$bulanOmset] = [
                    'omset' => 0,
                    'outlet_ids' => [],
                    'days' => [],
                ];
            }

            $outletId = $row['id'] ?? md5(mb_strtolower(trim((string) ($row['nama_outlet'] ?? ''))));

            $bucket[$tahunGO][$bulanOmset]['omset'] += (float) ($row['omset'] ?? 0);
            $bucket[$tahunGO][$bulanOmset]['outlet_ids'][$outletId] = true;
            $bucket[$tahunGO][$bulanOmset]['days'][$date->format('Y-m-d')] = true;
        }

        ksort($goYearOptions);
        ksort($tahunOmsetOptions);

        $goYears = array_keys($bucket);
        sort($goYears);

        $avgData = [];
        $omsetData = [];
        $outletData = [];

        foreach ($goYears as $goYear) {
            $avgData[$goYear] = [];
            $omsetData[$goYear] = [];
            $outletData[$goYear] = [];

            for ($month = 1; $month <= 12; $month++) {
                $monthData = $bucket[$goYear][$month] ?? [
                    'omset' => 0,
                    'outlet_ids' => [],
                    'days' => [],
                ];

                $totalOmset = (float) $monthData['omset'];
                $outletCount = count($monthData['outlet_ids']);
                $dayCount = count($monthData['days']);

                $avgSales = $dayCount > 0 ? $totalOmset / $dayCount : 0;

                $avgData[$goYear][] = round($avgSales, 2);
                $omsetData[$goYear][] = round($totalOmset, 2);
                $outletData[$goYear][] = $outletCount;
            }
        }

        return [
            'monthLabels'       => array_values($monthLabels),
            'avgData'           => $avgData,
            'omsetData'         => $omsetData,
            'outletData'        => $outletData,
            'goYearOptions'     => array_values($goYearOptions),
            'tahunOmsetOptions' => array_values($tahunOmsetOptions),
        ];
    }

    private function extractGoYear(array $row): ?int
    {
        $directFields = [
            'tahun_go',
            'go_year',
            'tahun_buka',
            'tahun_open',
            'tahun_opening',
            'go_live_year',
        ];

        foreach ($directFields as $field) {
            $value = trim((string) ($row[$field] ?? ''));
            if ($value !== '' && preg_match('/^\d{4}$/', $value)) {
                return (int) $value;
            }
        }

        $dateFields = [
            'tanggal_go',
            'tgl_go',
            'tanggal_buka',
            'tanggal_open',
            'tanggal_opening',
            'go_live_date',
        ];

        foreach ($dateFields as $field) {
            $value = $row[$field] ?? null;
            if (!empty($value)) {
                try {
                    return (int) \Carbon\Carbon::parse($value)->format('Y');
                } catch (\Throwable $e) {
                }
            }
        }

        // kalau ada data mentah sheet
        $raw = $row['raw'] ?? [];

        $rawDirectFields = [
            'Tahun GO',
            'GO Year',
            'Tahun Buka',
            'Tahun Open',
            'Tahun Opening',
        ];

        foreach ($rawDirectFields as $field) {
            $value = trim((string) ($raw[$field] ?? ''));
            if ($value !== '' && preg_match('/^\d{4}$/', $value)) {
                return (int) $value;
            }
        }

        $rawDateFields = [
            'Tanggal GO',
            'Tanggal Buka',
            'Tanggal Open',
            'Tanggal Opening',
            'GO Live Date',
        ];

        foreach ($rawDateFields as $field) {
            $value = $raw[$field] ?? null;
            if (!empty($value)) {
                try {
                    return (int) \Carbon\Carbon::parse($value)->format('Y');
                } catch (\Throwable $e) {
                }
            }
        }

        return null;
    }
    
    public function bodLabourCost(Request $request)
    {
        $filters = [
            'tahun' => $request->get('tahun'),
            'bulan' => $request->get('bulan'),
        ];

        $labour = $this->loadLabourCostDashboardFromRecapSheet($filters);

        return view('Investor.DashboardLabourCost', [
            'title'           => 'Dashboard BOD - Labour Cost',
            'type'            => 'labour_cost',
            'filters'         => $filters,
            'monthLabels'     => $labour['monthLabels'],
            'kpi'             => $labour['kpi'],
            'labourCostData'  => $labour['labourCostData'],
            'labourRatioData' => $labour['labourRatioData'],
            'monthlyTable'    => $labour['monthlyTable'],
            'tmTable'         => $labour['tmTable'],
            'availableYears'  => $labour['availableYears'],
            'lastSyncAt'      => now()->format('Y-m-d H:i:s'),
        ]);
    }

    private function loadLabourCostDashboardFromRecapSheet(array $filters = []): array
    {
        $rows = $this->loadLabourCostRowsFromRecapLcSheet();

        $monthMap = [
            'januari' => 1,
            'februari' => 2,
            'maret' => 3,
            'april' => 4,
            'mei' => 5,
            'juni' => 6,
            'juli' => 7,
            'agustus' => 8,
            'september' => 9,
            'oktober' => 10,
            'november' => 11,
            'desember' => 12,
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $bucket = [];
        $tmBucket = [];
        $availableYears = [];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = $this->normalizeLabourHeader((string) ($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) {
                continue;
            }

            $omset = $this->toFloatId($row['omset'] ?? 0);
            $jumlah = $this->toFloatId($row['jumlah_crew'] ?? 0);
            $estGaji = $this->toFloatId($row['est_gaji'] ?? 0);
            $persen = $this->toFloatPercent($row['persen_rasio'] ?? 0);

            $availableYears[$year] = $year;

            if (!isset($bucket[$year][$month])) {
                $bucket[$year][$month] = [
                    'omset' => 0,
                    'jumlah' => 0,
                    'est_gaji' => 0,
                    'persen_source' => [],
                ];
            }

            $bucket[$year][$month]['omset'] += $omset;
            $bucket[$year][$month]['jumlah'] += $jumlah;
            $bucket[$year][$month]['est_gaji'] += $estGaji;

            if ($persen > 0) {
                $bucket[$year][$month]['persen_source'][] = $persen;
            }

            // TM ratio dari kolom dinamis header kanan
            foreach (($row['tm_ratios'] ?? []) as $tmName => $tmRatioRaw) {
                $tmName = trim((string) $tmName);
                if ($tmName === '') {
                    continue;
                }

                $tmRatio = $this->toFloatPercent($tmRatioRaw);

                if (!isset($tmBucket[$year][$tmName][$month])) {
                    $tmBucket[$year][$tmName][$month] = [
                        'ratio_source' => [],
                    ];
                }

                if ($tmRatio > 0) {
                    $tmBucket[$year][$tmName][$month]['ratio_source'][] = $tmRatio;
                }
            }
        }

        ksort($availableYears);

        foreach ($bucket as $year => $months) {
            foreach ($months as $month => $item) {
                $bucket[$year][$month]['persen'] = !empty($item['persen_source'])
                    ? array_sum($item['persen_source']) / count($item['persen_source'])
                    : ((float) $item['omset'] > 0 ? (((float) $item['est_gaji'] / (float) $item['omset']) * 100) : 0);
            }
        }

        foreach ($tmBucket as $year => $tmRows) {
            foreach ($tmRows as $tmName => $months) {
                foreach ($months as $month => $item) {
                    $tmBucket[$year][$tmName][$month]['persen'] = !empty($item['ratio_source'])
                        ? array_sum($item['ratio_source']) / count($item['ratio_source'])
                        : 0;
                }
            }
        }

        $selectedYear = !empty($filters['tahun'])
            ? (int) $filters['tahun']
            : $this->detectLatestLabourYearWithData($bucket, array_values($availableYears));

        $labourCostData = [];
        $labourRatioData = [];
        $monthlyTable = [];

        $totalOmset = 0;
        $totalJumlah = 0;
        $totalEstGaji = 0;
        $ratioSource = [];

        foreach (array_values($availableYears) as $year) {
            $labourCostData[$year] = [];
            $labourRatioData[$year] = [];

            for ($month = 1; $month <= 12; $month++) {
                $item = $bucket[$year][$month] ?? [
                    'omset' => 0,
                    'jumlah' => 0,
                    'est_gaji' => 0,
                    'persen' => 0,
                ];

                $chartEst = (float) $item['est_gaji'];
                $chartRatio = (float) $item['persen'];

                if (!empty($filters['bulan']) && (int) $filters['bulan'] !== $month) {
                    $chartEst = 0;
                    $chartRatio = 0;
                }

                $labourCostData[$year][] = round($chartEst, 2);
                $labourRatioData[$year][] = round($chartRatio, 3);

                if ($year === $selectedYear) {
                    if (empty($filters['bulan']) || (int) $filters['bulan'] === $month) {
                        $monthlyTable[] = [
                            'bulan' => $monthLabels[$month - 1],
                            'omset' => (float) $item['omset'],
                            'jumlah' => (float) $item['jumlah'],
                            'est_gaji' => (float) $item['est_gaji'],
                            'persen' => round((float) $item['persen'], 3),
                        ];

                        $totalOmset += (float) $item['omset'];
                        $totalJumlah += (float) $item['jumlah'];
                        $totalEstGaji += (float) $item['est_gaji'];

                        if ((float) $item['persen'] > 0) {
                            $ratioSource[] = (float) $item['persen'];
                        }
                    }
                }
            }
        }

        $tmTable = [];
        $selectedTmRows = $tmBucket[$selectedYear] ?? [];
        ksort($selectedTmRows);

        foreach ($selectedTmRows as $tmName => $months) {
            $monthlyRatio = [];
            $ratioValues = [];

            for ($month = 1; $month <= 12; $month++) {
                $ratio = 0;

                if (empty($filters['bulan']) || (int) $filters['bulan'] === $month) {
                    $ratio = (float) ($months[$month]['persen'] ?? 0);
                }

                $monthlyRatio[] = round($ratio, 3);

                if ($ratio > 0) {
                    $ratioValues[] = $ratio;
                }
            }

            $avgRatio = !empty($ratioValues) ? (array_sum($ratioValues) / count($ratioValues)) : 0;

            $tmTable[] = [
                'tm' => $tmName,
                'months' => $monthlyRatio,
                'avg_ratio' => round($avgRatio, 3),
                'status' => $avgRatio < 8.5 ? 'Good' : 'Bad',
            ];
        }

        usort($tmTable, fn ($a, $b) => $b['avg_ratio'] <=> $a['avg_ratio']);

        return [
            'monthLabels' => $monthLabels,
            'kpi' => [
                'omset' => $totalOmset,
                'manload' => $totalJumlah,
                'est_salary' => $totalEstGaji,
                'labour_ratio' => !empty($ratioSource)
                    ? array_sum($ratioSource) / count($ratioSource)
                    : ($totalOmset > 0 ? ($totalEstGaji / $totalOmset) * 100 : 0),
                'selected_year' => $selectedYear,
            ],
            'labourCostData' => $labourCostData,
            'labourRatioData' => $labourRatioData,
            'monthlyTable' => $monthlyTable,
            'tmTable' => $tmTable,
            'availableYears' => array_values($availableYears),
        ];
    }

    private function detectLatestLabourYearWithData(array $bucket, array $availableYears): int
    {
        rsort($availableYears);

        foreach ($availableYears as $year) {
            for ($month = 1; $month <= 12; $month++) {
                $item = $bucket[$year][$month] ?? null;
                if (!$item) {
                    continue;
                }

                if (
                    (float) ($item['omset'] ?? 0) > 0 ||
                    (float) ($item['jumlah'] ?? 0) > 0 ||
                    (float) ($item['est_gaji'] ?? 0) > 0 ||
                    (float) ($item['persen'] ?? 0) > 0
                ) {
                    return (int) $year;
                }
            }
        }

        return !empty($availableYears) ? (int) max($availableYears) : (int) now()->format('Y');
    }

    private function loadLabourCostRowsFromRecapLcSheet(): array
    {
        try {
            $sheetId = '1IwMlVIBQ0UQBlXEYnhF4cyR_yrCrmjOEQ2AbVawNK6Q';
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid=1313491211";

            $response = Http::timeout(20)->get($csvUrl);

            if (! $response->successful()) {
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            if (!$lines || count($lines) < 2) {
                return [];
            }

            $rows = array_map('str_getcsv', $lines);
            $headers = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);

            if (empty($headers)) {
                return [];
            }

            $fixedColumns = [
                'no',
                'tahun',
                'bulan',
                'omset',
                'jumlah crew',
                'est gaji',
                '% rasio',
                'norm',
                'hasil',
                '#n/a',
            ];

            $tmHeaders = [];
            foreach ($headers as $header) {
                $normalized = $this->normalizeLabourHeader($header);

                if (!in_array($normalized, $fixedColumns, true) && trim((string) $header) !== '') {
                    $tmHeaders[] = $header;
                }
            }

            $result = [];

            foreach (array_slice($rows, 1) as $row) {
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), null);
                }

                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) {
                    continue;
                }

                $tahun = trim((string) ($assoc['Tahun'] ?? ''));
                $bulan = trim((string) ($assoc['Bulan'] ?? ''));

                if ($tahun === '' || $bulan === '') {
                    continue;
                }

                $tmRatios = [];
                foreach ($tmHeaders as $tmHeader) {
                    $tmName = trim((string) $tmHeader);

                    if ($tmName === '' || $tmName === '#N/A') {
                        continue;
                    }

                    $tmRatios[$tmName] = $assoc[$tmHeader] ?? null;
                }

                $result[] = [
                    'no' => $assoc['No'] ?? null,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'omset' => $assoc['Omset'] ?? 0,
                    'jumlah_crew' => $assoc['Jumlah Crew'] ?? 0,
                    'est_gaji' => $assoc['Est Gaji'] ?? 0,
                    'persen_rasio' => $assoc['% Rasio'] ?? 0,
                    'norm' => $assoc['Norm'] ?? null,
                    'hasil' => $assoc['Hasil'] ?? null,
                    'tm_ratios' => $tmRatios,
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('BOD labour recap sheet load failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [];
        }
    }

    private function normalizeLabourHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value);
        return trim($value);
    }

    private function toFloatPercent($value): float
    {
        $value = (string) $value;
        $value = str_replace(['Rp', 'rp', '%', ' '], '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : 0.0;
    }
    
    /* =========================
       QCR
    ========================= */
    public function bodQcr()
    {
        return view('Investor.dashboardBOD', [
            'title' => 'Dashboard BOD - QCR',
            'type'  => 'qcr'
        ]);
    }
    
    /* =========================
       RECRUITMENT
    ========================= */
    
    public function bodRecruitment(Request $request)
    {
        // Ubah default filter menjadi array
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        // Pastikan formatnya selalu array
        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        // Hapus array kosong
        $filterTahun = array_filter($filterTahun);
        $filterBulan = array_filter($filterBulan);

        $filters = [
            'tahun' => $filterTahun,
            'bulan' => $filterBulan,
        ];

        $recruitment = $this->loadRecruitmentDashboardFromSheet($filters);

        return view('Investor.dashboardRecruitment', [
            'title'          => 'Dashboard BOD - Recruitment',
            'type'           => 'recruitment',
            'filters'        => $filters,
            'availableYears' => $recruitment['availableYears'],
            'monthLabels'    => $recruitment['monthLabels'],
            'kpiNew'         => $recruitment['kpiNew'],
            'kpiExisting'    => $recruitment['kpiExisting'],
            'tableNew'       => $recruitment['tableNew'],
            'tableExisting'  => $recruitment['tableExisting'],
            'lastSyncAt'     => now()->format('d/m/Y H:i:s'),
        ]);
    }

    private function loadRecruitmentDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadRecruitmentRowsFromSheet();

        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        $bucketNew = [];
        $bucketExisting = [];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // LOGIKA MULTIPLE FILTER
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // ✅ PERBAIKAN: Inisialisasi bucket jika belum ada
            if (!isset($bucketNew[$month])) {
                $bucketNew[$month] = ['kebutuhan' => 0, 'pemenuhan' => 0, 'variance' => 0];
                $bucketExisting[$month] = ['kebutuhan' => 0, 'pemenuhan' => 0, 'variance' => 0];
            }

            // ✅ PERBAIKAN: JUMLAHKAN DATA (+=) BUKAN DITIMPA (=)
            $bucketNew[$month]['kebutuhan'] += $row['keb_new'];
            $bucketNew[$month]['pemenuhan'] += $row['pem_new'];
            $bucketNew[$month]['variance'] += $row['var_new'];

            $bucketExisting[$month]['kebutuhan'] += $row['keb_exist'];
            $bucketExisting[$month]['pemenuhan'] += $row['pem_exist'];
            $bucketExisting[$month]['variance'] += $row['var_exist'];
        }

        ksort($availableYears);

        $kpiNew = ['kebutuhan' => 0, 'pemenuhan' => 0, 'variance' => 0, 'pencapaian' => 0];
        $kpiExisting = ['kebutuhan' => 0, 'pemenuhan' => 0, 'variance' => 0, 'pencapaian' => 0];

        $tableNew = [];
        $tableExisting = [];

        for ($m = 1; $m <= 12; $m++) {
            if (isset($bucketNew[$m])) {
                $n = $bucketNew[$m];
                $e = $bucketExisting[$m];

                // Hitung pencapaian per bulan setelah dijumlahkan semua tahun
                $nPencapaian = $n['kebutuhan'] > 0 ? ($n['pemenuhan'] / $n['kebutuhan']) * 100 : 0;
                $ePencapaian = $e['kebutuhan'] > 0 ? ($e['pemenuhan'] / $e['kebutuhan']) * 100 : 0;

                $tableNew[] = [
                    'bulan' => $monthLabels[$m - 1],
                    'kebutuhan' => $n['kebutuhan'],
                    'pemenuhan' => $n['pemenuhan'],
                    'variance' => $n['variance'],
                    'pencapaian' => $nPencapaian,
                ];

                $tableExisting[] = [
                    'bulan' => $monthLabels[$m - 1],
                    'kebutuhan' => $e['kebutuhan'],
                    'pemenuhan' => $e['pemenuhan'],
                    'variance' => $e['variance'],
                    'pencapaian' => $ePencapaian,
                ];

                $kpiNew['kebutuhan'] += $n['kebutuhan'];
                $kpiNew['pemenuhan'] += $n['pemenuhan'];
                $kpiNew['variance'] += $n['variance'];
                
                $kpiExisting['kebutuhan'] += $e['kebutuhan'];
                $kpiExisting['pemenuhan'] += $e['pemenuhan'];
                $kpiExisting['variance'] += $e['variance'];
            }
        }

        // Kalkulasi Total Rasio secara Matematis
        $kpiNew['pencapaian'] = $kpiNew['kebutuhan'] > 0 ? ($kpiNew['pemenuhan'] / $kpiNew['kebutuhan']) * 100 : 0;
        $kpiExisting['pencapaian'] = $kpiExisting['kebutuhan'] > 0 ? ($kpiExisting['pemenuhan'] / $kpiExisting['kebutuhan']) * 100 : 0;

        return compact('availableYears', 'monthLabels', 'kpiNew', 'kpiExisting', 'tableNew', 'tableExisting');
    }
    
    private function loadRecruitmentRowsFromSheet(): array
    {
        try {
            $sheetId = '1cNo4k2oz2DtkoiAYHTxAx93kNlQ7-BTC2QerYG99DpM';
            
            // MASUKKAN GID DARI TAB 'RECAP RTO' DI SINI
            $gid = '117945823'; 
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            if (!$response->successful()) return [];

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            $headers = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                if (count($row) < count($headers)) continue;
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                
                // Pastikan mapping nama ini sesuai dengan Looker Studio Anda
                $result[] = [
                    'no'        => $assoc['No'] ?? '',
                    'tahun'     => $assoc['Tahun'] ?? '',
                    'bulan'     => $assoc['Bulan'] ?? '',
                    'keb_new'   => $this->toFloatId($assoc['Kebutuhan New'] ?? 0),
                    'pem_new'   => $this->toFloatId($assoc['Pemenuhan New'] ?? 0),
                    'var_new'   => $this->toFloatId($assoc['Variance New'] ?? 0),
                    'pen_new'   => $this->toFloatPercent($assoc['Pencapaian New'] ?? 0),
                    'keb_exist' => $this->toFloatId($assoc['Kebutuhan Existing'] ?? 0),
                    'pem_exist' => $this->toFloatId($assoc['Pemenuhan Existing'] ?? 0),
                    'var_exist' => $this->toFloatId($assoc['Variance Existing'] ?? 0),
                    'pen_exist' => $this->toFloatPercent($assoc['Pencapaian Existing'] ?? 0),
                ];
            }
            return $result;
        } catch (\Throwable $e) { return []; }
    }
    
    /* =========================
       TIMELINE RECRUITMENT & TRAINING
    ========================= */
    public function bodTimelineRecruitment(Request $request)
    {
        // 1. Tangkap Filter sebagai Array
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        $filterTahun = array_filter($filterTahun);
        $filterBulan = array_filter($filterBulan);

        $filters = [
            'tahun' => $filterTahun,
            'bulan' => $filterBulan,
        ];

        // 2. Load Data
        $timelineData = $this->loadTimelineDashboardFromSheet($filters);

        // 3. Return ke View
        return view('Investor.DashboardTimelineRecruitment', [
            'title'          => 'Dashboard BOD - Timeline Recruitment',
            'type'           => 'timeline_recruitment',
            'filters'        => $filters,
            'availableYears' => $timelineData['availableYears'],
            'monthLabels'    => $timelineData['monthLabels'],
            'tableData'      => $timelineData['tableData'],
            'lastSyncAt'     => now()->format('d/m/Y H:i:s'),
        ]);
    }

    private function loadTimelineDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadTimelineRowsFromSheet();

        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        $tableData = [];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // LOGIKA MULTIPLE FILTER
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // Masukkan data baris ke array untuk dilempar ke tabel View
            $tableData[] = [
                'tahun' => $year,
                'bulan_nama' => $monthLabels[$month - 1],
                'jumlah_outlet_go' => $row['jumlah_outlet_go'],
                'keb_am' => $row['keb_am'],
                'rec_am' => $row['rec_am'],
                'tr_am' => $row['tr_am'],
                'keb_spv' => $row['keb_spv'],
                'rec_spv' => $row['rec_spv'],
                'tr_spv' => $row['tr_spv'],
                'keb_leader' => $row['keb_leader'],
                'rec_leader' => $row['rec_leader'],
                'tr_leader' => $row['tr_leader'],
                'keb_crew' => $row['keb_crew'],
                'rec_staff' => $row['rec_staff'],
                'tr_staff' => $row['tr_staff'],
            ];
        }

        ksort($availableYears);

        return compact('availableYears', 'monthLabels', 'tableData');
    }

    private function loadTimelineRowsFromSheet(): array
    {
        try {
            $sheetId = '1cNo4k2oz2DtkoiAYHTxAx93kNlQ7-BTC2QerYG99DpM';
            // GID DARI TAB 'Timeline MPP'
            $gid = '1537405654'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            if (!$response->successful()) return [];

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // PEMBERSIH HEADER CSV
            $headers = array_map(function ($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                if (count($row) < count($headers)) continue;
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['Tahun'] ?? ''));
                if ($tahun === '') continue;

                $result[] = [
                    'tahun'            => $tahun,
                    'bulan'            => $assoc['Bulan'] ?? '',
                    'jumlah_outlet_go' => $assoc['Jumlah Outlet GO'] ?? 0,
                    
                    'keb_am'           => $assoc['Keb_AM'] ?? 0,
                    'keb_spv'          => $assoc['Keb_SPV'] ?? 0,
                    'keb_leader'       => $assoc['Keb_Leader'] ?? 0,
                    'keb_crew'         => $assoc['Keb_Crew'] ?? 0,
                    
                    'rec_am'           => $this->cleanStringData($assoc['Recruitment AM'] ?? ''),
                    'rec_spv'          => $this->cleanStringData($assoc['Recruitment SPV'] ?? ''),
                    'rec_leader'       => $this->cleanStringData($assoc['Recruitment Leader'] ?? ''),
                    'rec_staff'        => $this->cleanStringData($assoc['Recruitment Staff'] ?? ''),
                    
                    'tr_am'            => $this->cleanStringData($assoc['Training AM'] ?? ''),
                    'tr_spv'           => $this->cleanStringData($assoc['Training SPV'] ?? ''),
                    'tr_leader'        => $this->cleanStringData($assoc['Training Leader'] ?? ''),
                    'tr_staff'         => $this->cleanStringData($assoc['Training Staff'] ?? ''),
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("Timeline CSV Error: " . $e->getMessage());
            return []; 
        }
    }

    // Helper khusus untuk memfilter tulisan "null" dari Google Sheets
    private function cleanStringData($val)
    {
        $val = trim((string) $val);
        if (strtolower($val) === 'null' || $val === '#N/A' || $val === '') {
            return '-';
        }
        return $val;
    }
    
    /* =========================
       FULFILLMENT TRAINING
    ========================= */
    public function bodFulfillmentTraining(Request $request)
    {
        // 1. Tangkap Filter Multiple (Array)
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        $filterTahun = array_filter($filterTahun);
        $filterBulan = array_filter($filterBulan);

        $filters = [
            'tahun' => $filterTahun,
            'bulan' => $filterBulan,
        ];

        // 2. Load Data
        $trainingData = $this->loadFulfillmentTrainingDashboardFromSheet($filters);

        // 3. Return ke View
        return view('Investor.DashboardFulfillmentTraining', [
            'title'          => 'Dashboard BOD - Fulfillment Training',
            'type'           => 'fulfillment_training',
            'filters'        => $filters,
            'availableYears' => $trainingData['availableYears'],
            'monthLabels'    => $trainingData['monthLabels'],
            'kpiNew'         => $trainingData['kpiNew'],
            'kpiExisting'    => $trainingData['kpiExisting'],
            'tableData'      => $trainingData['tableData'],
            'lastSyncAt'     => now()->format('d/m/Y H:i:s'),
        ]);
    }

    private function loadFulfillmentTrainingDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadFulfillmentTrainingRowsFromSheet();

        // Dukung mapping nama bulan Bahasa Inggris & Indonesia (berdasarkan gambar spreadsheet)
        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        $bucket = [];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // LOGIKA MULTIPLE FILTER
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            if (!isset($bucket[$month])) {
                $bucket[$month] = [
                    'plan_leader' => 0, 'act_leader' => 0, 'var_leader' => 0,
                    'plan_crew' => 0, 'act_crew' => 0, 'var_crew' => 0,
                    'kebutuhan' => 0, 'pemenuhan' => 0, 'kekurangan' => 0
                ];
            }

            // Jumlahkan Data (Operator +=)
            $bucket[$month]['plan_leader'] += $row['plan_leader'];
            $bucket[$month]['act_leader']  += $row['act_leader'];
            $bucket[$month]['var_leader']  += $row['var_leader'];
            
            $bucket[$month]['plan_crew']   += $row['plan_crew'];
            $bucket[$month]['act_crew']    += $row['act_crew'];
            $bucket[$month]['var_crew']    += $row['var_crew'];

            $bucket[$month]['kebutuhan']   += $row['kebutuhan'];
            $bucket[$month]['pemenuhan']   += $row['pemenuhan'];
            $bucket[$month]['kekurangan']  += $row['kekurangan'];
        }

        ksort($availableYears);

        $kpiNew = [
            'plan_leader' => 0, 'act_leader' => 0, 'var_leader' => 0,
            'plan_crew' => 0, 'act_crew' => 0, 'var_crew' => 0,
        ];
        
        $kpiExisting = [
            'kebutuhan' => 0, 'pemenuhan' => 0, 'kekurangan' => 0
        ];

        $tableData = [];

        for ($m = 1; $m <= 12; $m++) {
            if (isset($bucket[$m])) {
                $d = $bucket[$m];
                
                // Menghitung persentase per bulan untuk tabel
                $pctLeader = $d['plan_leader'] > 0 ? ($d['act_leader'] / $d['plan_leader']) * 100 : 0;
                $pctCrew   = $d['plan_crew'] > 0 ? ($d['act_crew'] / $d['plan_crew']) * 100 : 0;
                $pctExist  = $d['kebutuhan'] > 0 ? ($d['pemenuhan'] / $d['kebutuhan']) * 100 : 0;

                $tableData[] = array_merge(['bulan' => $monthLabels[$m - 1]], $d, [
                    'pct_leader' => $pctLeader,
                    'pct_crew'   => $pctCrew,
                    'pct_exist'  => $pctExist
                ]);

                // Akumulasi KPI Total
                $kpiNew['plan_leader'] += $d['plan_leader'];
                $kpiNew['act_leader']  += $d['act_leader'];
                $kpiNew['var_leader']  += $d['var_leader'];
                
                $kpiNew['plan_crew']   += $d['plan_crew'];
                $kpiNew['act_crew']    += $d['act_crew'];
                $kpiNew['var_crew']    += $d['var_crew'];

                $kpiExisting['kebutuhan']  += $d['kebutuhan'];
                $kpiExisting['pemenuhan']  += $d['pemenuhan'];
                $kpiExisting['kekurangan'] += $d['kekurangan'];
            }
        }

        // Kalkulasi Persentase KPI Total
        $kpiNew['pct_leader'] = $kpiNew['plan_leader'] > 0 ? ($kpiNew['act_leader'] / $kpiNew['plan_leader']) * 100 : 0;
        $kpiNew['pct_crew']   = $kpiNew['plan_crew'] > 0 ? ($kpiNew['act_crew'] / $kpiNew['plan_crew']) * 100 : 0;
        
        $totalPlanNew   = $kpiNew['plan_leader'] + $kpiNew['plan_crew'];
        $totalActNew    = $kpiNew['act_leader'] + $kpiNew['act_crew'];
        $kpiNew['pct_total'] = $totalPlanNew > 0 ? ($totalActNew / $totalPlanNew) * 100 : 0;

        $kpiExisting['pct_total'] = $kpiExisting['kebutuhan'] > 0 ? ($kpiExisting['pemenuhan'] / $kpiExisting['kebutuhan']) * 100 : 0;

        return compact('availableYears', 'monthLabels', 'kpiNew', 'kpiExisting', 'tableData');
    }

    private function loadFulfillmentTrainingRowsFromSheet(): array
    {
        try {
            // Sesuai dengan ID Spreadsheet dan GID yang Anda temukan
            $sheetId = '1SUSpnzMo6iIvTRv40qxyVHW9XB-EtLjS-3WFxMVM8rU';
            $gid = '117945823'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            if (!$response->successful()) return [];

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // PEMBERSIH HEADER CSV (BOM Character)
            $headers = array_map(function ($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                if (count($row) < count($headers)) continue;
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['Tahun'] ?? ''));
                if ($tahun === '') continue;

                $result[] = [
                    'tahun'       => $tahun,
                    'bulan'       => $assoc['Bulan'] ?? '',
                    
                    'plan_leader' => $this->toFloatId($assoc['Plan Leader'] ?? 0),
                    'act_leader'  => $this->toFloatId($assoc['Actual Leader'] ?? 0),
                    'var_leader'  => $this->toFloatId($assoc['Variance Leader'] ?? 0),
                    
                    'plan_crew'   => $this->toFloatId($assoc['Plan Crew'] ?? 0),
                    'act_crew'    => $this->toFloatId($assoc['Actual Crew'] ?? 0),
                    'var_crew'    => $this->toFloatId($assoc['Variance Crew'] ?? 0),

                    'kebutuhan'   => $this->toFloatId($assoc['Kebutuhan'] ?? 0),
                    'pemenuhan'   => $this->toFloatId($assoc['Pemenuhan'] ?? 0),
                    'kekurangan'  => $this->toFloatId($assoc['Kekurangan'] ?? 0),
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("Fulfillment Training CSV Error: " . $e->getMessage());
            return []; 
        }
    }
    
/* =========================
       RETRAINING CREW
    ========================= */
    public function bodRetrainingCrew(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        $filters = [
            'tahun' => array_filter($filterTahun),
            'bulan' => array_filter($filterBulan),
        ];

        $data = $this->loadRetrainingCrewDashboardFromSheet($filters);

        return view('Investor.DashboardRetrainingCrew', array_merge([
            'title'      => 'Dashboard BOD - Retraining Crew',
            'type'       => 'retraining_crew',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadRetrainingCrewDashboardFromSheet(array $filters): array
    {
        // 1. INI VARIABEL YANG HILANG SEBELUMNYA!
        $rows = $this->loadRetrainingCrewRowsFromSheet();

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        $scoreCount = 0; 
        
        $metrics = [
            'total_crew' => 0,
            'score_competency' => 0,
            'val1' => ['score'=>0, 'grooming'=>0, 'nasi'=>0, 'ayam'=>0, 'kasir'=>0, 'sambal'=>0, 'admin'=>0],
            'val2' => ['score'=>0, 'grooming'=>0, 'nasi'=>0, 'ayam'=>0, 'kasir'=>0, 'sambal'=>0, 'admin'=>0],
            'val3' => ['score'=>0, 'grooming'=>0, 'nasi'=>0, 'ayam'=>0, 'kasir'=>0, 'sambal'=>0, 'admin'=>0],
        ];

        $counts = [
            'score_competency' => 0,
            'val1' => ['score'=>0, 'grooming'=>0, 'nasi'=>0, 'ayam'=>0, 'kasir'=>0, 'sambal'=>0, 'admin'=>0],
            'val2' => ['score'=>0, 'grooming'=>0, 'nasi'=>0, 'ayam'=>0, 'kasir'=>0, 'sambal'=>0, 'admin'=>0],
            'val3' => ['score'=>0, 'grooming'=>0, 'nasi'=>0, 'ayam'=>0, 'kasir'=>0, 'sambal'=>0, 'admin'=>0],
        ];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            $metrics['total_crew'] += $row['total_crew'];
            
            if ($row['score_competency'] > 0) {
                $metrics['score_competency'] += $row['score_competency'];
                $counts['score_competency']++;
            }

            foreach (['val1', 'val2', 'val3'] as $v) {
                $fields = ['score', 'grooming', 'nasi', 'ayam', 'kasir', 'sambal', 'admin'];
                foreach ($fields as $field) {
                    $rowKey = $v . '_' . $field;
                    if ($row[$rowKey] > 0) {
                        $metrics[$v][$field] += $row[$rowKey];
                        $counts[$v][$field]++;
                    }
                }
            }
        }

        if ($counts['score_competency'] > 0) {
            $metrics['score_competency'] /= $counts['score_competency'];
        }
        
        foreach (['val1', 'val2', 'val3'] as $v) {
            $fields = ['score', 'grooming', 'nasi', 'ayam', 'kasir', 'sambal', 'admin'];
            foreach ($fields as $field) {
                if ($counts[$v][$field] > 0) {
                    $metrics[$v][$field] /= $counts[$v][$field];
                }
            }
        }

        ksort($availableYears);

        return [
            'availableYears' => $availableYears,
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics
        ];
    }

    
private function loadRetrainingCrewRowsFromSheet(): array
    {
        try {
            $sheetId = '1xjkoXRBZpRHQ8I9E6JEqSkZuIGakmOugI4RKj5OORlQ';
            $gid = '1147696957'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            if (!$response->successful()) return [];

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            $headers = array_map(function ($h) {
                return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
            }, $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                
                // Tambalan wajib untuk baris yang kosong ujung kanannya
                $row = array_pad($row, count($headers), ''); 

                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['Tahun'] ?? ''));
                if ($tahun === '') continue;

                 $result[] = [
                    'tahun'            => $tahun,
                    'bulan'            => $assoc['Bulan'] ?? '',
                    
                    // Kolom C & G
                    'total_crew'       => (int) $this->parseScore($assoc['Total'] ?? 0),
                    'score_competency' => $this->parseScore($assoc['Average'] ?? 0),

                    // Kolom D & Kriteria 1
                    'val1_score'       => $this->parseScore($assoc['Validasi 1'] ?? 0),
                    'val1_grooming'    => $this->parseScore($assoc['Grooming 1'] ?? 0),
                    'val1_nasi'        => $this->parseScore($assoc['Nasi 1'] ?? 0),
                    'val1_ayam'        => $this->parseScore($assoc['Ayam 1'] ?? 0),
                    'val1_kasir'       => $this->parseScore($assoc['Kasir 1'] ?? 0),
                    'val1_sambal'      => $this->parseScore($assoc['Sambal 1'] ?? 0),
                    'val1_admin'       => $this->parseScore($assoc['Admin 1'] ?? 0),

                    // Kolom E & Kriteria 2
                    'val2_score'       => $this->parseScore($assoc['Validasi 2'] ?? 0),
                    'val2_grooming'    => $this->parseScore($assoc['Grooming 2'] ?? 0),
                    'val2_nasi'        => $this->parseScore($assoc['Nasi 2'] ?? 0),
                    'val2_ayam'        => $this->parseScore($assoc['Ayam 2'] ?? 0),
                    'val2_kasir'       => $this->parseScore($assoc['Kasir 2'] ?? 0),
                    'val2_sambal'      => $this->parseScore($assoc['Sambal 2'] ?? 0),
                    'val2_admin'       => $this->parseScore($assoc['Admin 2'] ?? 0),

                    // Kolom F & Kriteria 3
                    'val3_score'       => $this->parseScore($assoc['Validasi 3'] ?? 0),
                    'val3_grooming'    => $this->parseScore($assoc['Grooming 3'] ?? 0),
                    'val3_nasi'        => $this->parseScore($assoc['Nasi 3'] ?? 0),
                    'val3_ayam'        => $this->parseScore($assoc['Ayam 3'] ?? 0),
                    'val3_kasir'       => $this->parseScore($assoc['Kasir 3'] ?? 0),
                    'val3_sambal'      => $this->parseScore($assoc['Sambal 3'] ?? 0),
                    'val3_admin'       => $this->parseScore($assoc['Admin 3'] ?? 0),
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("Retraining CSV Error: " . $e->getMessage());
            return []; 
        }
    }

    /**
     * Helper Parser khusus untuk angka Skor yang bisa mendeteksi koma/titik.
     */
    private function parseScore($val): float
    {
        if (empty($val) || strtolower(trim((string)$val)) === '#n/a' || trim((string)$val) === '-') {
            return 0.0;
        }

        $str = trim((string)$val);
        // Hapus persen jika ada
        $str = str_replace('%', '', $str);

        // Jika angka mengandung koma (format Indo) -> 4,09
        if (strpos($str, ',') !== false) {
            $str = str_replace('.', '', $str); // Hapus titik ribuan dulu
            $str = str_replace(',', '.', $str); // Ubah koma jadi titik desimal
        } 
        
        // Cek jika nilainya Valid Numerik, pastikan batasnya realistis (Misal skala max 10)
        // Kita buang angka aneh seperti 409
        $finalScore = is_numeric($str) ? (float) $str : 0.0;

        // Pencegahan bug jika angka terinput "409" bukan "4.09"
        if ($finalScore > 100) {
            $finalScore = $finalScore / 100;
        }

        return $finalScore;
    }
      
 /* =========================
       TRAINING LEADER
    ========================= */
    public function bodTrainingLeader(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        $filters = [
            'tahun' => array_filter($filterTahun),
            'bulan' => array_filter($filterBulan),
        ];

        $data = $this->loadTrainingLeaderDashboardFromSheet($filters);

        return view('Investor.DashboardTrainingLeader', array_merge([
            'title'      => 'Dashboard BOD - Training Leader',
            'type'       => 'training_leader',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadTrainingLeaderDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadTrainingLeaderRowsFromSheet();

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        
        $metrics = [
            'total_leader' => 0,
            'ext_total_tc' => 0, 'ext_tuntas_tc' => 0, 'ext_kompeten' => 0, 'ext_avg_score' => 0,
            'int_total_tc' => 0, 'int_tuntas_tc' => 0, 'int_kompeten' => 0, 'int_avg_score' => 0,
        ];

        // Penghitung untuk membagi Rata-rata Skor (Hanya hitung bulan yang ada nilainya)
        $counts = [
            'ext_avg_score' => 0,
            'int_avg_score' => 0,
        ];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // Sum Data
            $metrics['total_leader']  += $row['total_leader'];
            
            $metrics['ext_total_tc']  += $row['ext_total_tc'];
            $metrics['ext_tuntas_tc'] += $row['ext_tuntas_tc'];
            $metrics['ext_kompeten']  += $row['ext_kompeten'];
            
            $metrics['int_total_tc']  += $row['int_total_tc'];
            $metrics['int_tuntas_tc'] += $row['int_tuntas_tc'];
            $metrics['int_kompeten']  += $row['int_kompeten'];

            // Akumulasi Average Score (Abaikan angka 0)
            if ($row['ext_avg_score'] > 0) {
                $metrics['ext_avg_score'] += $row['ext_avg_score'];
                $counts['ext_avg_score']++;
            }
            if ($row['int_avg_score'] > 0) {
                $metrics['int_avg_score'] += $row['int_avg_score'];
                $counts['int_avg_score']++;
            }
        }

        // Kalkulasi Rata-Rata Akhir
        if ($counts['ext_avg_score'] > 0) $metrics['ext_avg_score'] /= $counts['ext_avg_score'];
        if ($counts['int_avg_score'] > 0) $metrics['int_avg_score'] /= $counts['int_avg_score'];

        // Kalkulasi Persentase Kelulusan (Matematika Akurat: Kompeten / Total TC * 100)
        $metrics['pct_internal'] = $metrics['int_total_tc'] > 0 ? ($metrics['int_kompeten'] / $metrics['int_total_tc']) * 100 : 0;
        $metrics['pct_external'] = $metrics['ext_total_tc'] > 0 ? ($metrics['ext_kompeten'] / $metrics['ext_total_tc']) * 100 : 0;

        ksort($availableYears);

        return [
            'availableYears' => $availableYears,
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics
        ];
    }

    private function loadTrainingLeaderRowsFromSheet(): array
    {
        try {
            // ID Spreadsheet & GID yang baru
            $sheetId = '1E5tYm-NhpgIW4wnWvvrfiMpnbIjH0kyQR7V7d2IF8jI';
            $gid = '641475385'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            
            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                \Log::error("Gagal menarik data Training Leader.");
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // SUPER PARSER: Huruf kecil & Tanpa spasi
            $headers = array_map(function ($h) {
                $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                return strtolower(str_replace(' ', '', $clean)); 
            }, $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                
                $row = array_pad($row, count($headers), ''); 
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['tahun'] ?? ''));
                if ($tahun === '') continue;

                 $result[] = [
                    'tahun'                  => $tahun,
                    'bulan'                  => $assoc['bulan'] ?? '',
                    
                    'ext_total_tc'           => $this->parseScore($assoc['totaltcexternal'] ?? 0),
                    'ext_tuntas_tc'          => $this->parseScore($assoc['tuntastcexternal'] ?? 0),
                    'ext_avg_score'          => $this->parseScore($assoc['averagescoretcexternal'] ?? 0),
                    'ext_kompeten'           => $this->parseScore($assoc['kompetenexternal'] ?? 0),
                    
                    'int_total_tc'           => $this->parseScore($assoc['totaltcinternal'] ?? 0),
                    'int_tuntas_tc'          => $this->parseScore($assoc['tuntastcinternal'] ?? 0),
                    'int_avg_score'          => $this->parseScore($assoc['averagescoretcinternal'] ?? 0),
                    'int_kompeten'           => $this->parseScore($assoc['kompeteninternal'] ?? 0),
                    
                    'total_leader'           => $this->parseScore($assoc['totalleader'] ?? 0),
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("Training Leader CSV Error: " . $e->getMessage());
            return []; 
        }
    }
    
    /* =========================
    RTO (SURVEY TITIK)
    ========================= */
    public function bodRto(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) {
            $filterTahun = [$filterTahun];
        }

        if (!is_array($filterBulan)) {
            $filterBulan = [$filterBulan];
        }

        $filters = [
            'tahun' => array_values(array_filter($filterTahun)),
            'bulan' => array_values(array_filter($filterBulan)),
        ];

        $data = $this->loadRtoDashboardFromSheet($filters);

        return view('Investor.DashboardRto', array_merge([
            'title'      => 'Dashboard BOD - RTO',
            'type'       => 'rto',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadRtoDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadRtoRowsFromSheet();

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];

        $metrics = [
            'spk_bi'         => 0,
            'titik_masuk'    => 0,
            'titik_validasi' => 0,
            'titik_rto'      => 0,
            'titik_go'       => 0,
        ];

        $chartData = [
            'spk_bi'         => array_fill(1, 12, 0),
            'titik_masuk'    => array_fill(1, 12, 0),
            'titik_validasi' => array_fill(1, 12, 0),
            'titik_rto'      => array_fill(1, 12, 0),
            'titik_go'       => array_fill(1, 12, 0),
        ];

        $tableBucket = [];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $month = (int) ($row['bulan'] ?? 0);

            if ($year <= 0 || $month < 1 || $month > 12) {
                continue;
            }

            $availableYears[$year] = $year;

            if (!empty($filters['tahun']) && !in_array((string) $year, $filters['tahun'], true)) {
                continue;
            }

            if (!empty($filters['bulan']) && !in_array((string) $month, $filters['bulan'], true)) {
                continue;
            }

            $spkBi         = (float) ($row['spk_bi'] ?? 0);
            $titikMasuk    = (float) ($row['titik_masuk'] ?? 0);
            $titikValidasi = (float) ($row['titik_validasi'] ?? 0);
            $titikRto      = (float) ($row['titik_rto'] ?? 0);
            $titikGo       = (float) ($row['titik_go'] ?? 0);

            $metrics['spk_bi']         += $spkBi;
            $metrics['titik_masuk']    += $titikMasuk;
            $metrics['titik_validasi'] += $titikValidasi;
            $metrics['titik_rto']      += $titikRto;
            $metrics['titik_go']       += $titikGo;

            $chartData['spk_bi'][$month]         += $spkBi;
            $chartData['titik_masuk'][$month]    += $titikMasuk;
            $chartData['titik_validasi'][$month] += $titikValidasi;
            $chartData['titik_rto'][$month]      += $titikRto;
            $chartData['titik_go'][$month]       += $titikGo;

            if (!isset($tableBucket[$month])) {
                $tableBucket[$month] = [
                    'bulan'          => $monthLabels[$month - 1],
                    'spk_bi'         => 0,
                    'titik_masuk'    => 0,
                    'titik_validasi' => 0,
                    'titik_rto'      => 0,
                    'titik_go'       => 0,
                ];
            }

            $tableBucket[$month]['spk_bi']         += $spkBi;
            $tableBucket[$month]['titik_masuk']    += $titikMasuk;
            $tableBucket[$month]['titik_validasi'] += $titikValidasi;
            $tableBucket[$month]['titik_rto']      += $titikRto;
            $tableBucket[$month]['titik_go']       += $titikGo;
        }

        ksort($availableYears);

        $tableData = [];
        for ($month = 1; $month <= 12; $month++) {
            $tableData[] = $tableBucket[$month] ?? [
                'bulan'          => $monthLabels[$month - 1],
                'spk_bi'         => 0,
                'titik_masuk'    => 0,
                'titik_validasi' => 0,
                'titik_rto'      => 0,
                'titik_go'       => 0,
            ];
        }

        return [
            'availableYears' => array_values($availableYears),
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics,
            'chartData'      => [
                'spk_bi'         => array_values($chartData['spk_bi']),
                'titik_masuk'    => array_values($chartData['titik_masuk']),
                'titik_validasi' => array_values($chartData['titik_validasi']),
                'titik_rto'      => array_values($chartData['titik_rto']),
                'titik_go'       => array_values($chartData['titik_go']),
            ],
            'tableData'      => $tableData,
        ];
    }

    private function loadRtoRowsFromSheet(): array
    {
        try {
            $sheetId = '1ZbVXCm167fsnBctyb_WWLWKI4nH7O3JUuTqW4IIqQIM';
            $gid = '1731147856';

            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";
            $response = Http::timeout(20)->get($csvUrl);

            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                \Log::error('Gagal menarik data RTO dari sheet RTO.');
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);

            if (count($rows) < 2) {
                \Log::error('RTO sheet kosong / kurang dari 2 baris.');
                return [];
            }

            $headerIndex = -1;
            $headerMap = [];

            foreach ($rows as $index => $row) {
                $normalized = array_map(function ($cell) {
                    $cell = trim((string) $cell);
                    $cell = preg_replace('/^\xEF\xBB\xBF/', '', $cell);
                    $cell = strtolower($cell);
                    $cell = preg_replace('/\s+/', ' ', $cell);
                    return $cell;
                }, $row);

                $rowStr = implode(' | ', $normalized);

                if (
                    str_contains($rowStr, 'nama outlet') &&
                    str_contains($rowStr, 'by rto') &&
                    str_contains($rowStr, 'plan go') &&
                    str_contains($rowStr, 'status outlet')
                ) {
                    $headerIndex = $index;

                    foreach ($normalized as $colIndex => $header) {
                        $headerMap[$header] = $colIndex;
                    }

                    break;
                }
            }

            if ($headerIndex === -1) {
                \Log::error('Header RTO tidak ditemukan.');
                return [];
            }

            $idxNamaOutlet = $headerMap['nama outlet'] ?? null;
            $idxByRto      = $headerMap['by rto'] ?? null;
            $idxPlanGo     = $headerMap['plan go'] ?? null;
            $idxStatus     = $headerMap['status outlet'] ?? null;

            if ($idxNamaOutlet === null) {
                \Log::error('Kolom nama outlet tidak ditemukan pada sheet RTO.');
                return [];
            }

            $result = [];

            foreach (array_slice($rows, $headerIndex + 1) as $row) {
                if (empty(array_filter($row, fn ($v) => trim((string) $v) !== ''))) {
                    continue;
                }

                $namaOutlet   = trim((string) ($row[$idxNamaOutlet] ?? ''));
                $byRtoRaw     = trim((string) ($idxByRto !== null ? ($row[$idxByRto] ?? '') : ''));
                $planGoRaw    = trim((string) ($idxPlanGo !== null ? ($row[$idxPlanGo] ?? '') : ''));
                $statusOutlet = trim((string) ($idxStatus !== null ? ($row[$idxStatus] ?? '') : ''));

                if ($namaOutlet === '') {
                    continue;
                }

                $byRtoDate = $this->parseRtoDate($byRtoRaw);
                $planGoDate = $this->parseRtoDate($planGoRaw);

                $mainDate = $byRtoDate ?: $planGoDate;
                if (!$mainDate) {
                    continue;
                }

                $tahun = (int) $mainDate->format('Y');
                $bulan = (int) $mainDate->format('n');

                $statusLower = strtolower($statusOutlet);

                $result[] = [
                    'tahun'          => $tahun,
                    'bulan'          => $bulan,
                    'spk_bi'         => 1,
                    'titik_masuk'    => 1,
                    'titik_validasi' => str_contains($statusLower, 'valid') ? 1 : 0,
                    'titik_rto'      => $byRtoDate ? 1 : 0,
                    'titik_go'       => (str_contains($statusLower, 'open') || str_contains($statusLower, 'go') || $planGoDate) ? 1 : 0,
                ];
            }

            \Log::info('RTO parsed rows', [
                'count' => count($result),
                'years' => array_values(array_unique(array_map(fn ($r) => $r['tahun'], $result))),
            ]);

            return $result;
        } catch (\Throwable $e) {
            \Log::error('RTO CSV Error: ' . $e->getMessage());
            return [];
        }
    }

    private function parseRtoDate(?string $value): ?\Carbon\Carbon
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '-' || strtolower($value) === 'null') {
            return null;
        }

        $value = str_replace(['  '], ' ', $value);
        $value = trim($value);

        $formats = [
            'd/M/Y',
            'd/M/y',
            'd-M-Y',
            'd-M-y',
            'd/m/Y',
            'd/m/y',
            'Y-m-d',
            'd M Y',
            'd M y',
        ];

        foreach ($formats as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $value);
            } catch (\Throwable $e) {
            }
        }

        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
    
    /* =========================
       KEMITRAAN (FUNDRAISING CONTROL)
    ========================= */
    public function bodKemitraan(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];


        $filters = [
            'tahun' => array_filter($filterTahun),
            'bulan' => array_filter($filterBulan),
        ];

        $data = $this->loadKemitraanDashboardFromSheet($filters);

        return view('Investor.DashboardKemitraan', array_merge([
            'title'      => 'Dashboard BOD - Kemitraan',
            'type'       => 'kemitraan',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadKemitraanDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadKemitraanRowsFromSheet();

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        
        $metrics = [
            'qty_actual'    => 0,
            'dana_actual'   => 0,
            'qty_target'    => 0,
            'dana_target'   => 0,
            'qty_variance'  => 0,
            'dana_variance' => 0,
            'achievement'   => 0,
        ];

        $tableData = [];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // Filter Multiple
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // Sum KPI
            $metrics['qty_actual']  += $row['qty'];
            $metrics['dana_actual'] += $row['dana'];
            $metrics['qty_target']  += $row['qty_target'];
            $metrics['dana_target'] += $row['dana_target'];

            $tableData[] = [
                'no'    => $row['no'],
                'bulan' => $monthLabels[$month - 1],
                'qty'   => $row['qty'],
                'dana'  => $row['dana'],
                'pct'   => $row['pct'],
            ];
        }

        // Kalkulasi Variance
        $metrics['qty_variance']  = $metrics['qty_target'] - $metrics['qty_actual'];
        $metrics['dana_variance'] = $metrics['dana_target'] - $metrics['dana_actual'];

        // Kalkulasi Achievement (Persentase Dana Actual vs Target)
        $metrics['achievement'] = $metrics['dana_target'] > 0 
            ? ($metrics['dana_actual'] / $metrics['dana_target']) * 100 
            : 0;

        ksort($availableYears);

        return [
            'availableYears' => $availableYears,
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics,
            'tableData'      => $tableData,
        ];
    }

    private function loadKemitraanRowsFromSheet(): array
    {
        try {
            // ID Spreadsheet Formulir Pendaftaran Calon Mitra Kemitraan
            $sheetId = '1g0056_ee-hIWAQIIq-wvsW_cBBOhNWiLli3-ESAbDgk';
            // GID Tab "Dashboard Direktur"
            $gid = '291245215'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            
            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                \Log::error("Gagal menarik data Kemitraan.");
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // Super Parser Header
            $headers = array_map(function ($h) {
                $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                return strtolower(str_replace([' ', '%'], ['', 'pct'], $clean)); 
            }, $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                $row = array_pad($row, count($headers), ''); 
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['tahun'] ?? ''));
                if ($tahun === '') continue;

                $result[] = [
                    'no'            => $assoc['no'] ?? '',
                    'tahun'         => $tahun,
                    'bulan'         => $assoc['bulan'] ?? '',
                    'qty'           => (int) $this->toFloatId($assoc['qty'] ?? 0),
                    'dana'          => $this->toFloatId($assoc['dana'] ?? 0),
                    'qty_target'    => (int) $this->toFloatId($assoc['qtytarget'] ?? 0),
                    'dana_target'   => $this->toFloatId($assoc['danatarget'] ?? 0),
                    'pct'           => trim((string) ($assoc['pct'] ?? '0')), // Simpan format % aslinya untuk tabel
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("Kemitraan CSV Error: " . $e->getMessage());
            return [];
        }
    }
    
    
    /* =========================
       LEADS KEMITRAAN (MONITORING)
    ========================= */
    public function bodLeadsKemitraan(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        $filters = [
            'tahun' => array_filter($filterTahun),
            'bulan' => array_filter($filterBulan),
        ];

        $data = $this->loadLeadsKemitraanDashboardFromSheet($filters);

        return view('Investor.DashboardLeadsKemitraan', array_merge([
            'title'      => 'Dashboard BOD - Leads Kemitraan',
            'type'       => 'leads_kemitraan',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadLeadsKemitraanDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadLeadsKemitraanRowsFromSheet();

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        
        $metrics = [
            'leads'       => 0,
            'fu1'         => 0,
            'fu2'         => 0,
            'fu3'         => 0,
            'input_data'  => 0,
            'rejected'    => 0,
            'dp'          => 0,
            'lunas'       => 0,
            'deal'        => 0,
            'hot_prospect'=> 0,
        ];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // Filter Multiple
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // Sum KPI
            $metrics['leads']      += $row['leads'];
            $metrics['fu1']        += $row['fu1'];
            $metrics['fu2']        += $row['fu2'];
            $metrics['fu3']        += $row['fu3'];
            $metrics['input_data'] += $row['input_data'];
            $metrics['rejected']   += $row['rejected'];
            $metrics['dp']         += $row['dp'];
            $metrics['lunas']      += $row['lunas'];
            
            // Kolom Deal dan Hot Prospect
            $metrics['deal']         += $row['deal'];
        }

        // Logika Fallback Matematis (Menyesuaikan dengan tampilan Mockup Looker Studio Anda)
        // Jika kolom Deal kosong, maka Deal = DP + Lunas.
        if ($metrics['deal'] === 0) {
            $metrics['deal'] = $metrics['dp'] + $metrics['lunas'];
        }
        // Hot Prospect secara visual sama dengan Input Data
        $metrics['hot_prospect'] = $metrics['input_data'];

        ksort($availableYears);

        return [
            'availableYears' => $availableYears,
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics,
        ];
    }

    private function loadLeadsKemitraanRowsFromSheet(): array
    {
        try {
            // ID Spreadsheet REKAP DATA CUSTOMER INVESTOR
            $sheetId = '1F6TaRdCKO8aWGpK51riZGGyC-cw925SzCTeN_T3RMHY';
            // GID Tab "Dashboard Direktur"
            $gid = '475459713'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            
            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                \Log::error("Gagal menarik data Leads Kemitraan.");
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // Super Parser Header
            $headers = array_map(function ($h) {
                $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                return strtolower(str_replace([' ', '%'], '', $clean)); 
            }, $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                $row = array_pad($row, count($headers), ''); 
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['tahun'] ?? ''));
                if ($tahun === '') continue;

                $result[] = [
                    'tahun'      => $tahun,
                    'bulan'      => $assoc['bulan'] ?? '',
                    'leads'      => (int) $this->toFloatId($assoc['leads'] ?? 0),
                    'fu1'        => (int) $this->toFloatId($assoc['followup1'] ?? 0),
                    'fu2'        => (int) $this->toFloatId($assoc['followup2'] ?? 0),
                    'fu3'        => (int) $this->toFloatId($assoc['followup3'] ?? 0),
                    'input_data' => (int) $this->toFloatId($assoc['inputdata'] ?? 0),
                    'rejected'   => (int) $this->toFloatId($assoc['rejected'] ?? 0),
                    'dp'         => (int) $this->toFloatId($assoc['dp'] ?? 0),
                    'lunas'      => (int) $this->toFloatId($assoc['lunas'] ?? 0),
                    'deal'       => (int) $this->toFloatId($assoc['deal'] ?? 0),
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("Leads Kemitraan CSV Error: " . $e->getMessage());
            return [];
        }
    }
    

/* =========================
   CONTROL BUDGETING
========================= */
public function bodControlBudget(Request $request)
{
    // 1. Tangkap Filter Multiple (Array)
    $filterTahun = $request->get('tahun', []);
    $filterBulan = $request->get('bulan', []);
    $filterDep   = $request->get('dep', []); // Tambahan filter Departemen

    if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
    if (!is_array($filterBulan)) $filterBulan = [$filterBulan];
    if (!is_array($filterDep)) $filterDep = [$filterDep];

    $filters = [
        'tahun' => array_filter($filterTahun),
        'bulan' => array_filter($filterBulan),
        'dep'   => array_filter($filterDep),
    ];

    // 2. Load Data
    $data = $this->loadControlBudgetDashboardFromSheet($filters);

    // 3. Return ke View
    return view('Investor.dashboardControlBudget', array_merge([
        'title'      => 'Dashboard BOD - Control Budget',
        'type'       => 'control_budget',
        'filters'    => $filters,
        'lastSyncAt' => now()->format('d/m/Y H:i:s'),
    ], $data));
}

private function loadControlBudgetDashboardFromSheet(array $filters): array
{
    $rows = $this->loadControlBudgetRowsFromSheet();

    $monthMap = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12
    ];

    $monthLabels = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    $availableYears = [];
    $availableDeps  = [];
    
    $metrics = ['plan' => 0, 'actual' => 0, 'variance' => 0, 'pencapaian' => 0];
    $bucketDep = [];

    foreach ($rows as $row) {
        $year = (int) ($row['tahun'] ?? 0);
        $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
        $month = $monthMap[$monthName] ?? null;
        $dep = strtoupper(trim($row['dep'] ?? ''));

        if ($year <= 0 || !$month || $dep === '') continue;

        $availableYears[$year] = $year;
        $availableDeps[$dep] = $dep;

        // LOGIKA FILTER
        if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
        if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;
        if (!empty($filters['dep']) && !in_array((string)$dep, $filters['dep'])) continue;

        // SUM KPI UTAMA
        $metrics['plan']   += (float) $row['plan'];
        $metrics['actual'] += (float) $row['actual'];

        // SUM PER DEPARTEMEN
        if (!isset($bucketDep[$dep])) {
            $bucketDep[$dep] = ['plan' => 0, 'actual' => 0];
        }
        $bucketDep[$dep]['plan']   += (float) $row['plan'];
        $bucketDep[$dep]['actual'] += (float) $row['actual'];
    }

    $metrics['variance']   = $metrics['plan'] - $metrics['actual'];
    $metrics['pencapaian'] = $metrics['plan'] > 0 ? ($metrics['actual'] / $metrics['plan']) * 100 : 0;

    $tableData = [];
    foreach ($bucketDep as $depName => $d) {
        $tableData[] = [
            'dep'      => $depName,
            'plan'     => $d['plan'],
            'actual'   => $d['actual'],
            'variance' => $d['plan'] - $d['actual'],
            'pct'      => $d['plan'] > 0 ? ($d['actual'] / $d['plan']) * 100 : 0,
        ];
    }

    usort($tableData, fn($a, $b) => $b['plan'] <=> $a['plan']);
    ksort($availableYears);
    sort($availableDeps);

    return [
        'availableYears' => array_values($availableYears),
        'availableDeps'  => array_values($availableDeps),
        'monthLabels'    => $monthLabels,
        'metrics'        => $metrics,
        'tableData'      => $tableData,
    ];
}

    private function loadControlBudgetRowsFromSheet(): array
    {
        try {
            $sheetId = '129SV585un2249QXeKEBVGjr9dWlqGW6P0l4H18BoRlE';
            $gid = '198981806';  
            
            // MENGGUNAKAN EXPORT CSV (Cara yang terbukti berhasil sebelumnya)
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            
            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // SUPER PARSER HEADER: Menghapus BOM, Spasi, dan Karakter Aneh
            $headers = array_map(function ($h) {
                $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                return strtolower(str_replace([' ', '%'], '', $clean)); 
            }, $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                // PENTING: array_pad memastikan jumlah kolom baris sama dengan header
                $row = array_pad($row, count($headers), ''); 
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                
                if (!is_array($assoc)) continue;

                $depName = strtoupper(trim((string) ($assoc['dep'] ?? '')));
                $tahun   = trim((string) ($assoc['tahun'] ?? ''));

                // Abaikan baris "TOTAL" bawaan spreadsheet dan baris kosong
                if ($tahun === '' || $depName === 'TOTAL' || $depName === '') {
                    continue;
                }

                $result[] = [
                    'tahun'  => $tahun,
                    'bulan'  => trim((string) ($assoc['bulan'] ?? '')),
                    'dep'    => $depName,
                    'plan'   => $this->toFloatId($assoc['plan'] ?? 0),
                    'actual' => $this->toFloatId($assoc['actual'] ?? 0),
                ];
            }

            return $result;

        } catch (\Throwable $e) { 
            \Log::error("Control Budget CSV Error: " . $e->getMessage());
            return []; 
        }
    }
        
    /* =========================
       OTIF (ON TIME IN FULL)
    ========================= */
    public function bodOtif(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];


        $filters = [
            'tahun' => array_filter($filterTahun),
            'bulan' => array_filter($filterBulan),
        ];

        $data = $this->loadOtifDashboardFromSheet($filters);

        return view('Investor.DashboardOtif', array_merge([
            'title'      => 'Dashboard BOD - OTIF',
            'type'       => 'otif',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadOtifDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadOtifRowsFromSheet();

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        
        $metrics = [
            'on_time'      => 0,
            'late'         => 0,
            'total_ontime' => 0,
            'clear'        => 0,
            'miss'         => 0,
            'total_clear'  => 0,
            
            // Persentase
            'pct_ontime'   => 0,
            'pct_fulfill'  => 0,
            'pct_otif'     => 0,
        ];

        // Variabel untuk menghitung rata-rata dari Spreadsheet (jika Anda ingin 100% sama persis dengan Looker)
        $counts = ['ontime' => 0, 'fulfill' => 0, 'otif' => 0];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // Filter Multiple
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // Sum Data Kuantitas
            $metrics['on_time']      += $row['on_time'];
            $metrics['late']         += $row['late'];
            $metrics['total_ontime'] += $row['total_ontime'];
            $metrics['clear']        += $row['clear'];
            $metrics['miss']         += $row['miss'];
            $metrics['total_clear']  += $row['total_clear'];

            // Sum Data Persentase (Untuk dirata-rata agar persis Looker Studio)
            if ($row['pct_ontime'] > 0) { $metrics['pct_ontime'] += $row['pct_ontime']; $counts['ontime']++; }
            if ($row['pct_fulfill'] > 0) { $metrics['pct_fulfill'] += $row['pct_fulfill']; $counts['fulfill']++; }
            if ($row['pct_otif'] > 0) { $metrics['pct_otif'] += $row['pct_otif']; $counts['otif']++; }
        }

        // Kalkulasi Rata-Rata Persentase
        if ($counts['ontime'] > 0) $metrics['pct_ontime'] /= $counts['ontime'];
        if ($counts['fulfill'] > 0) $metrics['pct_fulfill'] /= $counts['fulfill'];
        if ($counts['otif'] > 0) $metrics['pct_otif'] /= $counts['otif'];

        // FALLBACK MATEMATIKA AKURAT (Jika persentase di CSV kosong/nol semua)
        if ($metrics['pct_ontime'] == 0 && $metrics['total_ontime'] > 0) {
            $metrics['pct_ontime'] = ($metrics['on_time'] / $metrics['total_ontime']) * 100;
        }
        if ($metrics['pct_fulfill'] == 0 && $metrics['total_clear'] > 0) {
            $metrics['pct_fulfill'] = ($metrics['clear'] / $metrics['total_clear']) * 100;
        }
        if ($metrics['pct_otif'] == 0) {
            $metrics['pct_otif'] = ($metrics['pct_ontime'] * $metrics['pct_fulfill']) / 100;
        }

        ksort($availableYears);

        return [
            'availableYears' => $availableYears,
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics,
        ];
    }

    private function loadOtifRowsFromSheet(): array
    {
        try {
            // ID Spreadsheet OTIF
            $sheetId = '1N1NIiGCuX005Q6pQRaFZ8oVfMFCy97pc5KUM6-QTP6s';
            // GID Tab "DASHBOARD CFO"
            $gid = '132861649'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            
            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                \Log::error("Gagal menarik data Dashboard OTIF.");
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // PEMBERSIH HEADER DENGAN DEDUPLIKASI
            $rawHeaders = $rows[0] ?? [];
            $headers = [];
            $counts = [];
            
            foreach ($rawHeaders as $h) {
                // Bersihkan spasi dan % (contoh: "% Ontime" jadi "ontime")
                $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                $key = strtolower(str_replace([' ', '%'], '', $clean));
                
                if ($key === '') $key = 'kosong';
                
                // Jika duplikat (misal 'ontime' dan '% ontime' sama-sama jadi 'ontime'), beri suffix _2
                if (isset($counts[$key])) {
                    $counts[$key]++;
                    $key .= '_' . $counts[$key];
                } else {
                    $counts[$key] = 1;
                }
                $headers[] = $key;
            }

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                $row = array_pad($row, count($headers), ''); 
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['tahun'] ?? ''));
                if ($tahun === '') continue;

                // Membaca persentase angka koma ("49,13%") menggunakan toFloatId custom
                $result[] = [
                    'tahun'        => $tahun,
                    'bulan'        => $assoc['bulan'] ?? '',
                    'on_time'      => (int) $this->toFloatId($assoc['ontime'] ?? 0),
                    'late'         => (int) $this->toFloatId($assoc['late'] ?? 0),
                    'total_ontime' => (int) $this->toFloatId($assoc['totalontime'] ?? 0),
                    'pct_ontime'   => $this->toFloatPercentCustom($assoc['ontime_2'] ?? 0), // ontime_2 krn duplikat dg On Time
                    
                    'clear'        => (int) $this->toFloatId($assoc['clear'] ?? 0),
                    'miss'         => (int) $this->toFloatId($assoc['miss'] ?? 0),
                    'total_clear'  => (int) $this->toFloatId($assoc['totalclear'] ?? 0),
                    'pct_fulfill'  => $this->toFloatPercentCustom($assoc['fulfill'] ?? 0),
                    
                    'pct_otif'     => $this->toFloatPercentCustom($assoc['otif'] ?? 0),
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("OTIF CSV Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper Parser KHUSUS Persen dengan format koma Indo ("49,13%")
     * Tambahkan fungsi ini jika Anda belum memilikinya di Controller.
     */
    private function toFloatPercentCustom($value): float
    {
        $str = (string) $value;
        if (empty($str) || strtolower(trim($str)) === '#n/a' || trim($str) === '-') return 0.0;
        
        $str = str_replace(['Rp', 'rp', '%', ' '], '', $str);
        if (strpos($str, ',') !== false) {
            $str = str_replace('.', '', $str); 
            $str = str_replace(',', '.', $str); 
        }
        
        return is_numeric($str) ? (float) $str : 0.0;
    }
    
/* =========================
       PERFORMA SALES CRO
    ========================= */
    public function bodCro(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        $filters = [
            'tahun' => array_filter($filterTahun),
            'bulan' => array_filter($filterBulan),
        ];

        $data = $this->loadCroDashboardFromSheet($filters);

        return view('Investor.DashboardCro', array_merge([
            'title'      => 'Dashboard BOD - CRO',
            'type'       => 'cro',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadCroDashboardFromSheet(array $filters): array
    {
        $rowsData = $this->loadCroRowsFromSheet();
        $rows = $rowsData['data'];
        $croNames = $rowsData['cro_names'];

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        
        $metrics = [
            'omset' => 0,
            'target' => 0,
            'kekurangan' => 0,
            'pencapaian' => 0,
            'cros' => []
        ];

        // Inisialisasi array untuk Chart (Bulan 1 - 12)
        $chartData = array_fill(1, 12, 0);

        // Inisialisasi performa masing-masing CRO
        foreach ($croNames as $name) {
            $metrics['cros'][$name] = 0;
        }

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // Filter Multiple
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // Sum Main KPI
            $metrics['omset'] += $row['omset'];
            $metrics['target'] += $row['target'];
            
            // Sum Chart Data (Per Bulan)
            $chartData[$month] += $row['omset'];

            // Sum Individual CRO
            foreach ($croNames as $name) {
                if (isset($row['cros'][$name])) {
                    $metrics['cros'][$name] += $row['cros'][$name];
                }
            }
        }

        // Kalkulasi ulang Kekurangan & Pencapaian secara akurat (Variance & Achievement)
        $metrics['kekurangan'] = $metrics['omset'] - $metrics['target'];
        $metrics['pencapaian'] = $metrics['target'] > 0 ? ($metrics['omset'] / $metrics['target']) * 100 : 0;

        // Sorting CRO berdasarkan Omset Tertinggi
        arsort($metrics['cros']);

        ksort($availableYears);

        return [
            'availableYears' => $availableYears,
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics,
            'chartData'      => array_values($chartData), // Index 0 - 11 untuk JS
        ];
    }

    private function loadCroRowsFromSheet(): array
    {
        try {
            // ID Dashboard CRO sesuai dengan link spreadsheet Anda
            $sheetId = '15FXTHaJkYe-fTV-M0nlXdJUph7PjAsBbTOzqLMZnsKs';
            $gid = '819947931'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            
            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                \Log::error("Gagal menarik data Dashboard CRO.");
                return ['data' => [], 'cro_names' => []];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // Super Parser Header
            $headers = array_map(function ($h) {
                $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                // Hilangkan spasi dan tanda %
                return strtolower(str_replace([' ', '%'], '', $clean)); 
            }, $rows[0] ?? []);

            // Deteksi Otomatis Kolom Nama Anggota CRO (di luar kolom default)
            $fixedKeys = ['no', 'tahun', 'bulan', 'omset', 'target', 'kekurangan', 'pencapaian'];
            $croNames = [];
            foreach ($headers as $h) {
                if ($h !== '' && !in_array($h, $fixedKeys)) {
                    $croNames[] = $h;
                }
            }

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                $row = array_pad($row, count($headers), ''); 
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['tahun'] ?? ''));
                if ($tahun === '') continue;

                $rowData = [
                    'tahun'      => $tahun,
                    'bulan'      => $assoc['bulan'] ?? '',
                    'omset'      => $this->parseMoney($assoc['omset'] ?? 0),
                    'target'     => $this->parseMoney($assoc['target'] ?? 0),
                ];

                // Simpan data individual CRO
                $rowData['cros'] = [];
                foreach ($croNames as $name) {
                    $rowData['cros'][$name] = $this->parseMoney($assoc[$name] ?? 0);
                }

                $result[] = $rowData;
            }
            return ['data' => $result, 'cro_names' => $croNames];
        } catch (\Throwable $e) { 
            \Log::error("CRO CSV Error: " . $e->getMessage());
            return ['data' => [], 'cro_names' => []];
        }
    }

    /**
     * Helper Parser KHUSUS UANG untuk format Indo & Inggris (Rp 61,279,000.00 / Rp 61.279.000,00)
     */
    private function parseMoney($val): float
    {
        if (empty($val) || strtolower(trim((string)$val)) === '#n/a' || trim((string)$val) === '-') {
            return 0.0;
        }

        $str = trim((string)$val);
        
        // 1. Hapus simbol mata uang dan spasi
        $str = str_replace(['Rp', 'rp', ' ', '$'], '', $str);

        // 2. Format di Spreadsheet Anda: "61,279,000.00"
        // Hapus Koma pembatas ribuan
        $str = str_replace(',', '', $str); 
        
        return is_numeric($str) ? (float) $str : 0.0;
    }
    
/* =========================
       PERFORMA SALES CS (CUSTOMER SERVICE)
    ========================= */
    public function bodCs(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        $filters = [
            'tahun' => array_filter($filterTahun),
            'bulan' => array_filter($filterBulan),
        ];

        $data = $this->loadCsDashboardFromSheet($filters);

        return view('Investor.DashboardCs', array_merge([
            'title'      => 'Dashboard BOD - CS',
            'type'       => 'cs',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadCsDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadCsRowsFromSheet();

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        
        $metrics = [
            'omset'      => 0,
            'target'     => 0,
            'variance'   => 0,
            'pencapaian' => 0,
        ];

        // Inisialisasi array untuk Chart (Bulan 1 - 12)
        $chartData = array_fill(1, 12, 0);

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // Filter Multiple
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // Sum Main KPI
            $metrics['omset']  += $row['omset'];
            $metrics['target'] += $row['target'];
            
            // Sum Chart Data (Per Bulan)
            $chartData[$month] += $row['omset'];
        }

        // Kalkulasi Variance sesuai dengan rumus di spreadsheet Anda (=Target - Omset)
        $metrics['variance'] = $metrics['target'] - $metrics['omset'];
        
        // Kalkulasi Achievement (%) secara matematis
        $metrics['pencapaian'] = $metrics['target'] > 0 ? ($metrics['omset'] / $metrics['target']) * 100 : 0;

        ksort($availableYears);

        return [
            'availableYears' => $availableYears,
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics,
            'chartData'      => array_values($chartData), // Index 0 - 11 untuk JS Chart
        ];
    }

    private function loadCsRowsFromSheet(): array
    {
        try {
            // ID Spreadsheet CS (Berdasarkan Screenshot)
            $sheetId = '1X8j-F7BcF1uLsdmCe570qSyZ9rIqGR8q1jgGu_ixmJY';
            // GID Tab "Dashboard CS"
            $gid = '799556815'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            
            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                \Log::error("Gagal menarik data Dashboard CS.");
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // Super Parser Header (Hilangkan spasi, % dan BOM)
            $headers = array_map(function ($h) {
                $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                return strtolower(str_replace([' ', '%'], '', $clean)); 
            }, $rows[0] ?? []);

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                $row = array_pad($row, count($headers), ''); 
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['tahun'] ?? ''));
                if ($tahun === '') continue;

                $result[] = [
                    'tahun'  => $tahun,
                    'bulan'  => $assoc['bulan'] ?? '',
                    'omset'  => $this->parseMoney($assoc['omset'] ?? 0),
                    'target' => $this->parseMoney($assoc['target'] ?? 0),
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("CS CSV Error: " . $e->getMessage());
            return [];
        }
    }

    
/* =========================
       PERFORMA SALES E-COMMERCE
    ========================= */
    public function bodEcommerce(Request $request)
    {
        $filterTahun = $request->get('tahun', []);
        $filterBulan = $request->get('bulan', []);

        if (!is_array($filterTahun)) $filterTahun = [$filterTahun];
        if (!is_array($filterBulan)) $filterBulan = [$filterBulan];

        $filters = [
            'tahun' => array_filter($filterTahun),
            'bulan' => array_filter($filterBulan),
        ];

        $data = $this->loadEcommerceDashboardFromSheet($filters);

        return view('Investor.DashboardEcommerce', array_merge([
            'title'      => 'Dashboard BOD - ECommerce',
            'type'       => 'ecommerce',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadEcommerceDashboardFromSheet(array $filters): array
    {
        $rows = $this->loadEcommerceRowsFromSheet();

        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
            'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
            'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'mei' => 5, 
            'juni' => 6, 'juli' => 7, 'agustus' => 8, 'oktober' => 10, 'desember' => 12
        ];

        $monthLabels = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
        ];

        $availableYears = [];
        
        $metrics = [
            'omset'      => 0,
            'target'     => 0,
            'variance'   => 0,
            'pencapaian' => 0,
        ];

        // Array untuk Chart Bulanan
        $chartDataMonthly = array_fill(1, 12, 0);
        
        // Array untuk Chart Platform
        $platformTotals = [
            'shopeefood' => 0,
            'gofood'     => 0,
            'grabfood'   => 0,
            'tiktok'     => 0,
            'qpon'       => 0,
        ];

        foreach ($rows as $row) {
            $year = (int) ($row['tahun'] ?? 0);
            $monthName = mb_strtolower(trim($row['bulan'] ?? ''));
            $month = $monthMap[$monthName] ?? null;

            if ($year <= 0 || !$month) continue;

            $availableYears[$year] = $year;

            // Filter Multiple
            if (!empty($filters['tahun']) && !in_array((string)$year, $filters['tahun'])) continue;
            if (!empty($filters['bulan']) && !in_array((string)$month, $filters['bulan'])) continue;

            // Sum Main KPI
            $metrics['omset']  += $row['omset'];
            $metrics['target'] += $row['target'];
            
            // Sum Chart Monthly
            $chartDataMonthly[$month] += $row['omset'];

            // Sum Chart Platform
            $platformTotals['shopeefood'] += $row['shopeefood'];
            $platformTotals['gofood']     += $row['gofood'];
            $platformTotals['grabfood']   += $row['grabfood'];
            $platformTotals['tiktok']     += $row['tiktok'];
            $platformTotals['qpon']       += $row['qpon'];
        }

        // Kalkulasi Variance & Achievement
        $metrics['variance']   = $metrics['omset'] - $metrics['target'];
        $metrics['pencapaian'] = $metrics['target'] > 0 ? ($metrics['omset'] / $metrics['target']) * 100 : 0;

        ksort($availableYears);

        return [
            'availableYears' => $availableYears,
            'monthLabels'    => $monthLabels,
            'metrics'        => $metrics,
            'chartDataMonthly'=> array_values($chartDataMonthly),
            'platformTotals' => $platformTotals,
        ];
    }

private function loadEcommerceRowsFromSheet(): array
    {
        try {
            // 1. ID sudah diperbaiki sesuai link asli Anda (1NIK dan GuOIar)
            $sheetId = '1NlK79eQ8SBDyoH-haEs3lMnraqmZGuOlarKvn7AZX1Q';
            $gid = '999918449'; 
            
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";

            $response = Http::timeout(20)->get($csvUrl);
            
            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                \Log::error("Gagal menarik data Dashboard E-Commerce.");
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", trim($response->body()));
            $rows = array_map('str_getcsv', $lines);
            
            // 2. PENANGANAN HEADER KOSONG/GANDA
            $rawHeaders = $rows[0] ?? [];
            $headers = [];
            $counts = [];
            
            foreach ($rawHeaders as $h) {
                // Bersihkan spasi dan % (contoh: % Omset -> pctomset)
                $clean = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h));
                $key = strtolower(str_replace([' ', '%'], ['', 'pct'], $clean));
                
                // Jika header kosong, beri nama dummy agar tidak error
                if ($key === '') {
                    $key = 'kosong';
                }
                
                // Jika ada nama header yang sama, tambahkan angka di belakangnya
                if (isset($counts[$key])) {
                    $counts[$key]++;
                    $key .= '_' . $counts[$key];
                } else {
                    $counts[$key] = 1;
                }
                
                $headers[] = $key;
            }

            $result = [];
            foreach (array_slice($rows, 1) as $row) {
                $row = array_pad($row, count($headers), ''); 
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));
                if (!is_array($assoc)) continue;

                $tahun = trim((string) ($assoc['tahun'] ?? ''));
                if ($tahun === '') continue;

                // 3. Gunakan toFloatId bawaan Anda karena E-Commerce memakai format Rp Indo (Rp 0,00)
                $result[] = [
                    'tahun'      => $tahun,
                    'bulan'      => $assoc['bulan'] ?? '',
                    'omset'      => $this->toFloatId($assoc['omset'] ?? 0),
                    'target'     => $this->toFloatId($assoc['target'] ?? 0),
                    'shopeefood' => $this->toFloatId($assoc['shopeefood'] ?? 0),
                    'gofood'     => $this->toFloatId($assoc['gofood'] ?? 0),
                    'grabfood'   => $this->toFloatId($assoc['grabfood'] ?? 0),
                    'tiktok'     => $this->toFloatId($assoc['tiktok'] ?? 0),
                    'qpon'       => $this->toFloatId($assoc['qpon'] ?? 0),
                ];
            }
            return $result;
        } catch (\Throwable $e) { 
            \Log::error("E-Commerce CSV Error: " . $e->getMessage());
            return [];
        }
    }
    
    /* =========================
       MAPPING MARKET
    ========================= */
    public function bodMappingMarket(Request $request)
    {
        $filters = ['tahun' => [], 'bulan' => []];

        $data = $this->loadMappingMarketFromSheet();

        $marketingBiRows = $this->loadMarketingBiRows();

        $data['marketingBiRows'] = $marketingBiRows;
        $data['marketingBiSummary'] = $this->buildMarketingBiSummary($marketingBiRows);

        return view('Investor.DashboardMappingMarket', array_merge([
            'title'      => 'Dashboard BOD - Mapping Market',
            'type'       => 'mapping_market',
            'filters'    => $filters,
            'lastSyncAt' => now()->format('d/m/Y H:i:s'),
        ], $data));
    }

    private function loadMappingMarketFromSheet(): array
    {
        $sheetId = '1xyb1RyNZbRzv37tC2-xQFCfpXm9jlYGANokVEOIhXyc';

        $sheetGids = [
            '0',
            '336873472',
            '892749502',
            '659310543',
            '15888681',
            '1879275810',
        ];

        $mappingRows = [];
        $metrics = [
            'outlet_aktif' => 0,
            'optimum'      => 0,
            'agresif'      => 0,
            'market_share' => 0,
        ];

        foreach ($sheetGids as $gid) {
            $rows = $this->loadMappingMarketRowsByGid($sheetId, $gid);

            foreach ($rows as $row) {
                $existing = (float) ($row['existing'] ?? 0);
                $sehat    = (float) ($row['sehat'] ?? 0);
                $agresif  = (float) ($row['agresif'] ?? 0);

                $metrics['outlet_aktif'] += $existing;
                $metrics['optimum'] += $sehat;
                $metrics['agresif'] += $agresif;

                $mappingRows[] = [
                    'source_gid'         => $gid,
                    'provinsi'           => $row['provinsi'] ?? '-',
                    'kota_kabupaten'     => $row['kota_kabupaten'] ?? '-',
                    'kecamatan'          => $row['kecamatan'] ?? '-',
                    'existing'           => $existing,
                    'sehat'              => $sehat,
                    'agresif'            => $agresif,
                    'traffic_generator'  => $row['traffic_generator'] ?? '-',
                    'zona_prioritas'     => $row['zona_prioritas'] ?? '-',
                    'jumlah'             => $agresif,
                ];
            }
        }

        $metrics['market_share'] = $metrics['optimum'] > 0
            ? ($metrics['outlet_aktif'] / $metrics['optimum']) * 100
            : 0;

        // Gabungkan kecamatan yang sama dari beberapa tab, agar chart dan total tidak dobel label.
        $kecamatanMap = [];
        foreach ($mappingRows as $row) {
            $nama = trim((string) ($row['kecamatan'] ?? ''));
            if ($nama === '' || $nama === '-') {
                continue;
            }

            $key = mb_strtolower($nama);

            if (!isset($kecamatanMap[$key])) {
                $kecamatanMap[$key] = [
                    'nama'      => $nama,
                    'jumlah'    => 0,
                    'existing'  => 0,
                    'sehat'     => 0,
                    'agresif'   => 0,
                ];
            }

            $kecamatanMap[$key]['existing'] += (float) ($row['existing'] ?? 0);
            $kecamatanMap[$key]['sehat'] += (float) ($row['sehat'] ?? 0);
            $kecamatanMap[$key]['agresif'] += (float) ($row['agresif'] ?? 0);
            $kecamatanMap[$key]['jumlah'] += (float) ($row['agresif'] ?? 0);
        }

        $kecamatanList = array_values($kecamatanMap);

        usort($kecamatanList, function ($a, $b) {
            return ($b['jumlah'] ?? 0) <=> ($a['jumlah'] ?? 0);
        });

        usort($mappingRows, function ($a, $b) {
            return ($b['agresif'] ?? 0) <=> ($a['agresif'] ?? 0);
        });

        $topKecamatan = array_slice($kecamatanList, 0, 15);
        $chartLabels = array_column($topKecamatan, 'nama');
        $chartData   = array_map(fn ($row) => (float) ($row['jumlah'] ?? 0), $topKecamatan);

        return [
            'metrics'       => $metrics,
            'kecamatanList' => $kecamatanList,
            'mappingRows'   => $mappingRows,
            'grandTotal'    => (float) $metrics['agresif'],
            'chartLabels'   => $chartLabels,
            'chartData'     => $chartData,
        ];
    }

    private function loadMappingMarketRowsByGid(string $sheetId, string $gid): array
    {
        try {
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";
            $response = Http::timeout(25)->get($csvUrl);

            if (!$response->successful() || str_contains(strtolower($response->body()), '<html')) {
                Log::warning('Mapping Market sheet gagal diakses', [
                    'gid' => $gid,
                    'status' => $response->status(),
                ]);

                return [];
            }

            $body = trim((string) $response->body());
            if ($body === '') {
                return [];
            }

            $lines = preg_split("/\r\n|\n|\r/", $body);
            $rows = array_map('str_getcsv', $lines);

            if (count($rows) < 2) {
                return [];
            }

            $headerIndex = $this->detectMappingMarketHeaderIndex($rows);
            if ($headerIndex === null) {
                return [];
            }

            $headers = $this->normalizeMappingMarketHeaders($rows[$headerIndex] ?? []);
            $result = [];

            foreach (array_slice($rows, $headerIndex + 1) as $row) {
                $row = array_pad($row, count($headers), '');
                $assoc = @array_combine($headers, array_slice($row, 0, count($headers)));

                if (!is_array($assoc)) {
                    continue;
                }

                $mapped = $this->normalizeMappingMarketRow($assoc);
                if ($mapped) {
                    $result[] = $mapped;
                }
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Mapping Market CSV Error', [
                'gid' => $gid,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function detectMappingMarketHeaderIndex(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $normalized = array_map(fn ($value) => $this->normalizeMappingMarketKey($value), $row);

            $hasKecamatan = in_array('kecamatan', $normalized, true);
            $hasExisting  = in_array('existing', $normalized, true);
            $hasAgresif   = in_array('agresif', $normalized, true);

            if ($hasKecamatan && ($hasExisting || $hasAgresif)) {
                return (int) $index;
            }
        }

        return null;
    }

    private function normalizeMappingMarketHeaders(array $headers): array
    {
        $result = [];
        $counts = [];

        foreach ($headers as $header) {
            $key = $this->normalizeMappingMarketKey($header);
            if ($key === '') {
                $key = 'kolom_kosong';
            }

            if (isset($counts[$key])) {
                $counts[$key]++;
                $key .= '_' . $counts[$key];
            } else {
                $counts[$key] = 1;
            }

            $result[] = $key;
        }

        return $result;
    }

    private function normalizeMappingMarketKey($value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value);
        $value = mb_strtolower($value);
        $value = str_replace(['/', '-', '.', '(', ')'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        return match ($value) {
            'no', 'nomor' => 'no',
            'provinsi', 'province' => 'provinsi',
            'kota kabupaten', 'kota', 'kabupaten', 'city', 'kab kota' => 'kota_kabupaten',
            'kecamatan', 'nama kecamatan', 'district' => 'kecamatan',
            'existing', 'outlet aktif', 'aktif' => 'existing',
            'sehat', 'optimum', 'optimal' => 'sehat',
            'agresif', 'aggressive' => 'agresif',
            'traffic generator', 'traffic', 'generator' => 'traffic_generator',
            'zona prioritas', 'zona', 'prioritas' => 'zona_prioritas',
            default => str_replace(' ', '_', $value),
        };
    }

    private function normalizeMappingMarketRow(array $assoc): ?array
    {
        $kecamatan = $this->cleanStringData($assoc['kecamatan'] ?? '');
        $kota      = $this->cleanStringData($assoc['kota_kabupaten'] ?? '');
        $provinsi  = $this->cleanStringData($assoc['provinsi'] ?? '');

        $existing = $this->toFloatId($assoc['existing'] ?? 0);
        $sehat    = $this->toFloatId($assoc['sehat'] ?? 0);
        $agresif  = $this->toFloatId($assoc['agresif'] ?? 0);

        if (
            $kecamatan === '-' &&
            $kota === '-' &&
            $provinsi === '-' &&
            $existing == 0 &&
            $sehat == 0 &&
            $agresif == 0
        ) {
            return null;
        }

        if (mb_strtolower($kecamatan) === 'grand total') {
            return null;
        }

        return [
            'provinsi'          => $provinsi,
            'kota_kabupaten'    => $kota,
            'kecamatan'         => $kecamatan,
            'existing'          => $existing,
            'sehat'             => $sehat,
            'agresif'           => $agresif,
            'traffic_generator' => $this->cleanStringData($assoc['traffic_generator'] ?? ''),
            'zona_prioritas'    => $this->cleanStringData($assoc['zona_prioritas'] ?? ''),
        ];
    }

    public function mitraJson(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->email === 'superadmin@gmail.com';

        $areaId = $request->get('area_id');
        if (!$areaId || $areaId === 'all') {
            return response()->json([]);
        }

        $q = DB::table('tbl_mitra as m')
            ->select('m.id', 'm.nama_mitra')
            ->join('tbl_outlets as o', 'o.mitra_id', '=', 'm.id')
            ->where('o.area_id', $areaId)
            ->groupBy('m.id', 'm.nama_mitra')
            ->orderBy('m.nama_mitra', 'asc');

        if (!$isSuperAdmin) {
            $investorId = session('investor_id');
            if (!$investorId) {
                return response()->json([]);
            }

            $q->where('m.investor_id', $investorId);
        }

        return response()->json($q->get());
    }

    public function outletJson(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $user->email === 'superadmin@gmail.com';

        $areaId = $request->get('area_id');
        $mitraIds = $request->get('mitra_ids', '');
        $mitraIdsArr = array_values(array_filter(array_map('trim', explode(',', (string) $mitraIds))));

        if (!$areaId || $areaId === 'all') {
            return response()->json([]);
        }

        $q = DB::table('tbl_outlets as o')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.kode_outlet',
                'o.alamat',
                'o.esb_branch_code',
                'o.mitra_id',
                'o.area_id'
            )
            ->where('o.area_id', $areaId);

        if (!empty($mitraIdsArr)) {
            $q->whereIn('o.mitra_id', $mitraIdsArr);
        }

        if (!$isSuperAdmin) {
            $investorId = session('investor_id');
            if (!$investorId) {
                return response()->json([]);
            }

            $q->whereIn('o.mitra_id', function ($sub) use ($investorId) {
                $sub->select('id')
                    ->from('tbl_mitra')
                    ->where('investor_id', $investorId);
            });
        }

        $rows = $q->orderBy('o.nama_outlet', 'asc')->get();

        return response()->json($rows->unique('id')->values());
    }

    /* =========================
    SPK & ST MARKETING BI
    ========================= */
    private function loadMarketingBiRows(): array
    {
        try {
            $sheetId = '1mZRyAPj7bdbEzEfeySdQ9sceEH5Xav5r';
            $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=xlsx";

            $response = Http::timeout(60)->get($url);

            if (!$response->successful()) {
                Log::warning('Marketing BI gagal download XLSX', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 300),
                ]);
                return [];
            }

            if (str_contains(strtolower($response->body()), '<html')) {
                Log::warning('Marketing BI masih return HTML, bukan XLSX', [
                    'body' => substr($response->body(), 0, 500),
                ]);
                return [];
            }

            $tmpPath = storage_path('app/marketing_bi.xlsx');
            file_put_contents($tmpPath, $response->body());

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tmpPath);

            $result = [];

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $rows = $sheet->toArray(null, true, true, true);

                foreach ($rows as $row) {
                    $text = implode(' ', array_map(fn($v) => strtolower(trim((string) $v)), $row));

                    // skip header/baris kosong
                    if (trim($text) === '' || str_contains($text, 'spk') || str_contains($text, 'total')) {
                        continue;
                    }

                    $values = array_values($row);

                    $spk = $this->toFloatId($values[4] ?? 0);
                    $st  = $this->toFloatId($values[5] ?? 0);

                    if ($spk <= 0 && $st <= 0) {
                        continue;
                    }

                    $result[] = [
                        'tanggal'    => $values[0] ?? '-',
                        'bulan'      => $values[1] ?? '-',
                        'tahun'      => $values[2] ?? '-',
                        'outlet'     => $values[3] ?? '-',
                        'label'      => $values[3] ?? '-',
                        'spk'        => $spk,
                        'st'         => $st,
                        'keterangan' => $values[6] ?? '-',
                        'sheet_name' => $sheet->getTitle(),
                    ];
                }
            }

            return $result;

        } catch (\Throwable $e) {
            Log::error('Marketing BI XLSX Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
            ]);
            return [];
        }
    }

    private function detectMarketingBiHeaderIndex(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $normalized = array_map(fn ($value) => $this->normalizeMarketingBiKey($value), $row);

            $hasSpk = in_array('spk', $normalized, true);
            $hasSt  = in_array('st', $normalized, true);

            $hasName = in_array('outlet', $normalized, true)
                || in_array('nama_outlet', $normalized, true)
                || in_array('nama', $normalized, true)
                || in_array('cabang', $normalized, true);

            if (($hasSpk || $hasSt) && $hasName) {
                return (int) $index;
            }
        }

        // fallback: header baris pertama
        return 0;
    }

    private function normalizeMarketingBiHeaders(array $headers): array
    {
        $result = [];
        $counts = [];

        foreach ($headers as $header) {
            $key = $this->normalizeMarketingBiKey($header);

            if ($key === '') {
                $key = 'kolom_kosong';
            }

            if (isset($counts[$key])) {
                $counts[$key]++;
                $key .= '_' . $counts[$key];
            } else {
                $counts[$key] = 1;
            }

            $result[] = $key;
        }

        return $result;
    }

    private function normalizeMarketingBiKey($value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $value);
        $value = mb_strtolower($value);
        $value = str_replace(['/', '-', '.', '(', ')', '%'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        return match ($value) {
            'no', 'nomor' => 'no',
            'tanggal', 'date', 'tgl' => 'tanggal',
            'bulan', 'month' => 'bulan',
            'tahun', 'year' => 'tahun',
            'outlet', 'nama outlet', 'nama store', 'store', 'cabang', 'branch' => 'outlet',
            'nama', 'nama spk', 'nama marketing', 'marketing' => 'nama',
            'spk' => 'spk',
            'st' => 'st',
            'keterangan', 'ket', 'note', 'notes' => 'keterangan',
            default => str_replace(' ', '_', $value),
        };
    }

    private function normalizeMarketingBiRow(array $assoc): ?array
    {
        $tanggal = $this->cleanStringData($assoc['tanggal'] ?? '');
        $bulan   = $this->cleanStringData($assoc['bulan'] ?? '');
        $tahun   = $this->cleanStringData($assoc['tahun'] ?? '');
        $outlet  = $this->cleanStringData($assoc['outlet'] ?? '');
        $nama    = $this->cleanStringData($assoc['nama'] ?? '');
        $ket     = $this->cleanStringData($assoc['keterangan'] ?? '');

        $spk = $this->toFloatId($assoc['spk'] ?? 0);
        $st  = $this->toFloatId($assoc['st'] ?? 0);

        if (
            $tanggal === '-' &&
            $bulan === '-' &&
            $tahun === '-' &&
            $outlet === '-' &&
            $nama === '-' &&
            $spk == 0 &&
            $st == 0
        ) {
            return null;
        }

        $label = $outlet !== '-' ? $outlet : ($nama !== '-' ? $nama : '-');

        if (mb_strtolower($label) === 'total' || mb_strtolower($label) === 'grand total') {
            return null;
        }

        return [
            'tanggal'    => $tanggal,
            'bulan'      => $bulan,
            'tahun'      => $tahun,
            'outlet'     => $outlet,
            'nama'       => $nama,
            'label'      => $label,
            'spk'        => $spk,
            'st'         => $st,
            'keterangan' => $ket,
        ];
    }

    private function buildMarketingBiSummary(array $rows): array
    {
        $totalSpk = 0;
        $totalSt = 0;
        $activeRows = 0;

        foreach ($rows as $row) {
            $spk = (float) ($row['spk'] ?? 0);
            $st  = (float) ($row['st'] ?? 0);

            $totalSpk += $spk;
            $totalSt += $st;

            if ($spk > 0 || $st > 0) {
                $activeRows++;
            }
        }

        return [
            'total_spk'   => $totalSpk,
            'total_st'    => $totalSt,
            'active_rows' => $activeRows,
            'ratio_st'    => $totalSpk > 0 ? ($totalSt / $totalSpk) * 100 : 0,
        ];
    }
}
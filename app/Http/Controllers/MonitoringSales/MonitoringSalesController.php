<?php

namespace App\Http\Controllers\MonitoringSales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\Esb\EsbClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MonitoringSalesController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && ($user->email === 'superadmin@gmail.com' || $user->role === 'superadmin');

        // =========================
        // 1) AMBIL OUTLET SESUAI ROLE
        // =========================
        if ($isSuperAdmin) {
            $rawOutlets = DB::table('tbl_outlets')
                ->select('id', 'nama_outlet', 'mitra_id')
                ->orderBy('nama_outlet')
                ->orderBy('id')
                ->get();
        } else {
            if (in_array($user->role, ['leader', 'spv', 'tm_manager'], true)) {
                $query = DB::table('tbl_outlets')
                    ->select('id', 'nama_outlet', 'mitra_id');

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
        // 2) TAMPILKAN SEMUA OUTLET
        // JANGAN DIGABUNG BERDASARKAN NAMA DISPLAY
        // KARENA BEDA ID = BISA PUNYA TRANSAKSI SENDIRI
        // =========================
        $normalizeOutletName = function ($name) {
            $name = strtoupper(trim((string) $name));
            return preg_replace('/\s+/', ' ', $name);
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
                    'nama_outlet_display' => $displayName . ' [ID: ' . implode(',', $ids) . ']',
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
                ->max('tanggal');
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

        $filterApplied = !empty($tanggalAwal) && !empty($tanggalAkhir);

        // =========================
        // 4) FILTER OUTLET TERPILIH
        // PAKAI ID ASLI YANG DIPILIH
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
        // 5) DEFAULT VARIABLE
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
        // 6) QUERY SAAT FILTER AKTIF
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
        // 7) NOTIFIKASI TURUN SALES
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
                        return (int) $outlet->id === (int) $id;
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

        return view('MonitoringSales.monitoring-sales', compact(
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
            'notifikasiTurunSales',
            'tanggalAwal',
            'tanggalAkhir',
            'selectedOutletId'
        ));
    }
}
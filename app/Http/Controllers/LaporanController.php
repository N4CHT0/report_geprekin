<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\UndianExport;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
class LaporanController extends Controller
{
    public function laporanPerBulan(Request $request)
    {
        $bulanTahun = $request->input('bulan_tahun'); // format YYYY-MM
        $filterApplied = !empty($bulanTahun);

        // Ambil data outlet
        $outlets = DB::table('tbl_outlets')->pluck('nama_outlet', 'id');

        $laporan = [];

        if ($filterApplied) {
            [$year, $month] = explode('-', $bulanTahun);

            $laporanRaw = DB::table('tbl_laporan_bulanan')
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->whereIn('outlet_id', $outlets->keys())
                ->get();

            // Mapping per outlet
            foreach ($outlets as $id => $nama) {
                $laporan[$id] = [
                    'nama_outlet' => $nama,
                    'kode_outlet' => '', // isi jika ada
                    'hari' => []
                ];
                for ($d = 1; $d <= 31; $d++) {
                    $laporan[$id]['hari'][$d] = [
                        'sales' => 0,
                        'cu' => 0,
                        'ac' => 0
                    ];
                }
            }

            foreach ($laporanRaw as $row) {
                $d = (int)date('d', strtotime($row->tanggal));
                $laporan[$row->outlet_id]['hari'][$d] = [
                    'sales' => $row->total_omset,
                    'cu' => $row->total_cu,
                    'ac' => ($row->total_cu > 0) ? round($row->total_omset / $row->total_cu) : 0
                ];
            }
        }

        return view('Laporan.laporanPerBulan', compact('laporan', 'filterApplied', 'bulanTahun'));
    }

    public function laporanPerTahun(Request $request)
    {
        $tahun = $request->input('tahun', date('Y'));

        // Ambil semua outlet existing
        $outlets = DB::table('tbl_outlets')
            ->where('status', 'existing')
            ->pluck('nama_outlet', 'id'); // [id => nama_outlet]

        // Query data bulanan dari tbl_laporan_bulanan
        $laporanRaw = DB::table('tbl_laporan_bulanan as lb')
            ->join('tbl_outlets as o', 'lb.outlet_id', '=', 'o.id')
            ->select(
                'lb.outlet_id',
                DB::raw('MONTH(lb.tanggal) as bulan'),
                DB::raw('SUM(lb.total_omset) as total_omset'),
                DB::raw('SUM(lb.total_cu) as total_cu')
            )
            ->whereYear('lb.tanggal', $tahun)
            ->where('o.status', 'existing')
            ->groupBy('lb.outlet_id', DB::raw('MONTH(lb.tanggal)'))
            ->get();

        // Inisialisasi data outlet dan grand total
        $laporan = [];
        $grandTotal = [
            'sales' => array_fill(1, 12, 0),
            'cu'    => array_fill(1, 12, 0),
            'ac'    => array_fill(1, 12, 0),
            'totalSales' => 0,
            'totalCU'    => 0,
            'totalAC'    => 0,
        ];

        foreach ($outlets as $id => $nama) {
            $laporan[$id] = [
                'nama_outlet' => $nama,
                'kode_outlet' => '',
                'bulan' => []
            ];
            for ($m = 1; $m <= 12; $m++) {
                $laporan[$id]['bulan'][$m] = [
                    'sales' => 0,
                    'cu'    => 0,
                    'ac'    => 0
                ];
            }
        }

        // Isi data hasil query
        foreach ($laporanRaw as $row) {
            $ac = ($row->total_cu > 0) ? round($row->total_omset / $row->total_cu) : 0;
            $laporan[$row->outlet_id]['bulan'][$row->bulan] = [
                'sales' => $row->total_omset,
                'cu'    => $row->total_cu,
                'ac'    => $ac
            ];

            // Update grand total per bulan
            $grandTotal['sales'][$row->bulan] += $row->total_omset;
            $grandTotal['cu'][$row->bulan]    += $row->total_cu;
            $grandTotal['ac'][$row->bulan]    = ($grandTotal['cu'][$row->bulan] > 0) ? round($grandTotal['sales'][$row->bulan] / $grandTotal['cu'][$row->bulan]) : 0;
        }

        // Total tahunan untuk grand total
        $grandTotal['totalSales'] = array_sum($grandTotal['sales']);
        $grandTotal['totalCU']    = array_sum($grandTotal['cu']);
        $grandTotal['totalAC']    = ($grandTotal['totalCU'] > 0) ? round($grandTotal['totalSales'] / $grandTotal['totalCU']) : 0;

        return view('Laporan.laporanPerTahun', compact('laporan', 'tahun', 'grandTotal'));
    }

    public function laporanQCR(Request $request)
    {
        return view('Laporan.laporanQCR');
    }

    public function laporanDSC(Request $request)
{
    $user = auth()->user();

    [$start, $end] = $this->dscResolveDateRange($request);

    $outlets = $this->dscOutletBaseQuery($user)
        ->select('o.id', 'o.nama_outlet', 'o.kota', 'o.status')
        ->orderBy('o.nama_outlet', 'asc')
        ->get();

    return view('Investor.Laporan.laporanDailyStockControl', [
        'outlets' => $outlets,
        'start' => $start,
        'end' => $end,
        'startDate' => $start->toDateString(),
        'endDate' => $end->toDateString(),
        'selectedOutlet' => $request->input('outlet_id', ''),
    ]);
}

public function laporanDSCData(Request $request)
{
    try {
        $user = auth()->user();

        [$start, $end] = $this->dscResolveDateRange($request);

        $page = max((int) $request->input('page', 1), 1);
        $perPage = (int) $request->input('per_page', 25);
        $perPage = min(max($perPage, 10), 100);
        $offset = ($page - 1) * $perPage;

        $search = trim((string) $request->input('search', ''));
        $selectedOutlet = $request->input('outlet_id', '');

        $cacheKey = 'dsc_sf:' . md5(json_encode([
            'user_id' => $user->id ?? 0,
            'role' => $user->role ?? '',
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'outlet_id' => $selectedOutlet,
        ]));

        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $start, $end, $page, $perPage, $offset, $search, $selectedOutlet) {
        $baseOutlet = $this->dscOutletBaseQuery($user)
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.kota',
                'o.status',
                'o.mitra_id',
                'a.nama_area'
            );

        if ($selectedOutlet !== '' && $selectedOutlet !== null) {
            $baseOutlet->where('o.id', $selectedOutlet);
        }

        if ($search !== '') {
            $baseOutlet->where(function ($q) use ($search) {
                $q->where('o.nama_outlet', 'like', '%' . $search . '%')
                    ->orWhere('o.kota', 'like', '%' . $search . '%')
                    ->orWhere('o.status', 'like', '%' . $search . '%')
                    ->orWhere('a.nama_area', 'like', '%' . $search . '%');
            });
        }

        $totalRows = (clone $baseOutlet)->count();

        $outlets = $baseOutlet
            ->orderBy('o.nama_outlet', 'asc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        $outletIds = $outlets->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $dates = $this->dscBuildDates($start, $end);

        $rowsMap = $this->dscFetchOmsetMap($outletIds, $start, $end);
        $stockMap = $this->dscFetchStockSummaryMap($outletIds, $start, $end);

        $rows = [];
        $grand = $this->dscEmptyGrand($dates);

        foreach ($outlets as $outlet) {
            $row = [
                'outlet_id' => (int) $outlet->id,
                'nama_outlet' => $outlet->nama_outlet,
                'area' => $outlet->nama_area ?? '-',
                'kota' => $outlet->kota ?? '-',
                'status' => $outlet->status ?? '-',
                'hari' => [],
                'sub_total' => $this->dscEmptyTotal(),
            ];

            foreach ($dates as $date) {
                $d = $rowsMap[$outlet->id][$date] ?? null;
                $stock = $stockMap[$outlet->id][$date] ?? ['item_count' => 0, 'used_qty' => 0, 'ending_stock' => 0];
                $calc = $this->dscCalculateDay($d, $stock);

                $row['hari'][$date] = $calc;

                $this->dscAddTotal($row['sub_total'], $calc);
                $this->dscAddGrandDay($grand, $date, $calc);
            }

            $this->dscFinalizeTotal($row['sub_total']);
            $rows[] = $row;
        }

        $this->dscFinalizeGrand($grand);

        return [
            'rows' => $rows,
            'grand' => $grand,
            'dates' => $dates,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_rows' => $totalRows,
                'total_pages' => (int) ceil($totalRows / max($perPage, 1)),
            ],
        ];
    });

        return response()->json($payload);
    } catch (\Throwable $e) {
        Log::error('laporanDSCData error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'ok' => false,
            'message' => 'Gagal mengambil data DSC.',
            'error' => config('app.debug') ? $e->getMessage() : 'Server error. Cek storage/logs/laravel.log',
        ], 500);
    }
}

public function laporanDSCExport(Request $request): StreamedResponse
{
    $user = auth()->user();
    [$start, $end] = $this->dscResolveDateRange($request);

    $search = trim((string) $request->input('search', ''));
    $selectedOutlet = $request->input('outlet_id', '');
    $dates = $this->dscBuildDates($start, $end);

    $fileName = 'laporan_dsc_' . $start->format('Ymd') . '_' . $end->format('Ymd') . '.csv';

    return response()->streamDownload(function () use ($user, $start, $end, $search, $selectedOutlet, $dates) {
        $handle = fopen('php://output', 'w');

        $header = ['No', 'Outlet ID', 'Nama Outlet', 'Area', 'Kota', 'Status'];
        foreach ($dates as $date) {
            $label = Carbon::parse($date)->format('d/m/Y');
            $header[] = $label . ' Total Transaction';
            $header[] = $label . ' Uang Fisik';
            $header[] = $label . ' Uang Minus';
            $header[] = $label . ' Harus Disetor';
            $header[] = $label . ' Total Disetor';
            $header[] = $label . ' Selisih';
            $header[] = $label . ' Stock Item';
        }
        $header[] = 'Sub Total Transaction';
        $header[] = 'Sub Uang Fisik';
        $header[] = 'Sub Uang Minus';
        $header[] = 'Sub Harus Disetor';
        $header[] = 'Sub Total Disetor';
        $header[] = 'Sub Selisih';

        fputcsv($handle, $header);

        $query = $this->dscOutletBaseQuery($user)
            ->select('o.id', 'o.nama_outlet', 'o.kota', 'o.status', 'a.nama_area')
            ->orderBy('o.nama_outlet', 'asc');

        if ($selectedOutlet !== '' && $selectedOutlet !== null) {
            $query->where('o.id', $selectedOutlet);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('o.nama_outlet', 'like', '%' . $search . '%')
                    ->orWhere('o.kota', 'like', '%' . $search . '%')
                    ->orWhere('o.status', 'like', '%' . $search . '%')
                    ->orWhere('a.nama_area', 'like', '%' . $search . '%');
            });
        }

        $no = 1;

        $query->chunk(100, function ($outlets) use (&$no, $handle, $start, $end, $dates) {
            $outletIds = $outlets->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $rowsMap = $this->dscFetchOmsetMap($outletIds, $start, $end);
            $stockMap = $this->dscFetchStockSummaryMap($outletIds, $start, $end);

            foreach ($outlets as $outlet) {
                $line = [
                    $no++,
                    $outlet->id,
                    $outlet->nama_outlet,
                    $outlet->nama_area ?? '-',
                    $outlet->kota ?? '-',
                    $outlet->status ?? '-',
                ];

                $sub = $this->dscEmptyTotal();

                foreach ($dates as $date) {
                    $d = $rowsMap[$outlet->id][$date] ?? null;
                    $stock = $stockMap[$outlet->id][$date] ?? ['item_count' => 0, 'used_qty' => 0, 'ending_stock' => 0];
                    $calc = $this->dscCalculateDay($d, $stock);
                    $this->dscAddTotal($sub, $calc);

                    $line[] = $calc['total_transaction'];
                    $line[] = $calc['uang_fisik'];
                    $line[] = $calc['uang_minus'];
                    $line[] = $calc['harus_disetor'];
                    $line[] = $calc['total_disetor'];
                    $line[] = $calc['selisih'];
                    $line[] = $calc['stock_item_count'];
                }

                $this->dscFinalizeTotal($sub);
                $line[] = $sub['total_transaction'];
                $line[] = $sub['uang_fisik'];
                $line[] = $sub['uang_minus'];
                $line[] = $sub['harus_disetor'];
                $line[] = $sub['total_disetor'];
                $line[] = $sub['selisih'];

                fputcsv($handle, $line);
            }
        });

        fclose($handle);
    }, $fileName, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}

private function dscResolveDateRange(Request $request): array
{
    $startInput = $request->input('start_date', now()->startOfMonth()->toDateString());
    $endInput = $request->input('end_date', now()->endOfMonth()->toDateString());

    try {
        $start = Carbon::parse($startInput)->startOfDay();
        $end = Carbon::parse($endInput)->endOfDay();
    } catch (\Throwable $e) {
        $start = now()->startOfMonth()->startOfDay();
        $end = now()->endOfMonth()->endOfDay();
    }

    if ($end->lt($start)) {
        [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
    }

    // Batas aman UI. Export tetap masih bisa berat kalau periode terlalu panjang.
    if ($start->diffInDays($end) > 92) {
        $end = $start->copy()->addDays(92)->endOfDay();
    }

    return [$start, $end];
}

private function dscOutletBaseQuery($user)
{
    $query = DB::table('tbl_outlets as o')
        ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id');

    if ($user && ($user->role ?? null) !== 'superadmin') {
        $investorId = DB::table('tbl_investor')
            ->where('user_id', $user->id)
            ->value('id');

        if (! $investorId) {
            abort(403, 'Investor tidak ditemukan.');
        }

        $mitraIds = DB::table('tbl_mitra')
            ->where('investor_id', $investorId)
            ->pluck('id')
            ->toArray();

        if (empty($mitraIds)) {
            $query->whereRaw('1 = 0');
        } else {
            $query->whereIn('o.mitra_id', $mitraIds);
        }
    }

    return $query;
}

private function dscBuildDates(Carbon $start, Carbon $end): array
{
    $dates = [];
    $cursor = $start->copy()->startOfDay();

    while ($cursor->lte($end)) {
        $dates[] = $cursor->toDateString();
        $cursor->addDay();
    }

    return $dates;
}

private function dscFetchOmsetMap(array $outletIds, Carbon $start, Carbon $end): array
{
    if (empty($outletIds)) {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | SAFE QUERY
    |--------------------------------------------------------------------------
    | Versi ini tidak akan 500 kalau ada kolom yang belum ada di database.
    | Kolom yang tidak ditemukan otomatis diganti 0 / NULL.
    */
    $table = 'tbl_dsc_omset_setoran';

    $num = function (string $column) use ($table): string {
        return Schema::hasColumn($table, $column)
            ? "SUM(COALESCE($column, 0)) as $column"
            : "0 as $column";
    };

    $dateSetor = Schema::hasColumn($table, 'tanggal_setor')
        ? 'MAX(tanggal_setor) as tanggal_setor'
        : 'NULL as tanggal_setor';

    $selects = [
        'outlet_id',
        'tanggal',
        $num('s1_total_transaction'),
        $num('s2_total_transaction'),
        $num('s1_diskon'),
        $num('s2_diskon'),
        $num('s1_non_tunai'),
        $num('s2_non_tunai'),
        $num('s1_expense'),
        $num('s2_expense'),
        $num('s1_uang_fisik'),
        $num('s2_uang_fisik'),
        $num('s1_sudah_disetor'),
        $num('s2_sudah_disetor'),
        $num('s1_admin_pot_sales'),
        $num('s2_admin_pot_sales'),
        $num('s1_adjustment'),
        $num('s2_adjustment'),
        $dateSetor,
    ];

    $rows = DB::table($table)
        ->selectRaw(implode(",\n", $selects))
        ->whereIn('outlet_id', $outletIds)
        ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
        ->groupBy('outlet_id', 'tanggal')
        ->get();

    $map = [];
    foreach ($rows as $row) {
        $date = Carbon::parse($row->tanggal)->toDateString();
        $map[(int) $row->outlet_id][$date] = $row;
    }

    return $map;
}

private function dscFetchStockSummaryMap(array $outletIds, Carbon $start, Carbon $end): array
{
    if (empty($outletIds)) {
        return [];
    }

    $table = 'tbl_stock';

    // Kalau tbl_stock belum ada / beda nama, jangan buat report 500.
    if (! Schema::hasTable($table)) {
        return [];
    }

    $num = function (string $column) use ($table): string {
        return Schema::hasColumn($table, $column)
            ? "SUM(COALESCE($column, 0)) as $column"
            : "0 as $column";
    };

    $selects = [
        'outlet_id',
        'tanggal',
        'COUNT(*) as item_count',
        $num('used_qty'),
        $num('ending_stock'),
    ];

    $rows = DB::table($table)
        ->selectRaw(implode(",\n", $selects))
        ->whereIn('outlet_id', $outletIds)
        ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
        ->groupBy('outlet_id', 'tanggal')
        ->get();

    $map = [];
    foreach ($rows as $row) {
        $date = Carbon::parse($row->tanggal)->toDateString();
        $map[(int) $row->outlet_id][$date] = [
            'item_count' => (int) ($row->item_count ?? 0),
            'used_qty' => (float) ($row->used_qty ?? 0),
            'ending_stock' => (float) ($row->ending_stock ?? 0),
        ];
    }

    return $map;
}

private function dscCalculateDay($r, array $stock): array
{
    $s1Transaction = (float) ($r->s1_total_transaction ?? 0);
    $s2Transaction = (float) ($r->s2_total_transaction ?? 0);
    $s1Diskon = (float) ($r->s1_diskon ?? 0);
    $s2Diskon = (float) ($r->s2_diskon ?? 0);
    $s1NonTunai = (float) ($r->s1_non_tunai ?? 0);
    $s2NonTunai = (float) ($r->s2_non_tunai ?? 0);
    $s1Expense = (float) ($r->s1_expense ?? 0);
    $s2Expense = (float) ($r->s2_expense ?? 0);
    $s1UangFisik = (float) ($r->s1_uang_fisik ?? 0);
    $s2UangFisik = (float) ($r->s2_uang_fisik ?? 0);

    $s1CashNet = $s1Transaction - $s1Diskon - $s1NonTunai - $s1Expense;
    $s2CashNet = $s2Transaction - $s2Diskon - $s2NonTunai - $s2Expense;

    // Auto pergantian uang minus:
    // Jika cash net lebih besar dari uang fisik, kekurangan itu otomatis jadi uang minus yang harus disetor.
    $s1UangMinus = max($s1CashNet - $s1UangFisik, 0);
    $s2UangMinus = max($s2CashNet - $s2UangFisik, 0);

    $s1HarusDisetor = $s1UangFisik + $s1UangMinus;
    $s2HarusDisetor = $s2UangFisik + $s2UangMinus;

    $s1TotalDisetor = (float) ($r->s1_sudah_disetor ?? 0)
        + (float) ($r->s1_admin_pot_sales ?? 0)
        + (float) ($r->s1_adjustment ?? 0);

    $s2TotalDisetor = (float) ($r->s2_sudah_disetor ?? 0)
        + (float) ($r->s2_admin_pot_sales ?? 0)
        + (float) ($r->s2_adjustment ?? 0);

    $totalTransaction = $s1Transaction + $s2Transaction;
    $cashNet = $s1CashNet + $s2CashNet;
    $uangFisik = $s1UangFisik + $s2UangFisik;
    $uangMinus = $s1UangMinus + $s2UangMinus;
    $harusDisetor = $s1HarusDisetor + $s2HarusDisetor;
    $totalDisetor = $s1TotalDisetor + $s2TotalDisetor;
    $selisih = $totalDisetor - $harusDisetor;

    return [
        'total_transaction' => $totalTransaction,
        'diskon' => $s1Diskon + $s2Diskon,
        'non_tunai' => $s1NonTunai + $s2NonTunai,
        'expense' => $s1Expense + $s2Expense,
        'cash_net' => $cashNet,
        'uang_fisik' => $uangFisik,
        'uang_minus' => $uangMinus,
        'harus_disetor' => $harusDisetor,
        'total_disetor' => $totalDisetor,
        'selisih' => $selisih,
        'stock_item_count' => (int) ($stock['item_count'] ?? 0),
        'stock_used_qty' => (float) ($stock['used_qty'] ?? 0),
        'stock_ending' => (float) ($stock['ending_stock'] ?? 0),
        'tanggal_setor' => $r->tanggal_setor ?? null,
    ];
}

private function dscEmptyTotal(): array
{
    return [
        'total_transaction' => 0,
        'diskon' => 0,
        'non_tunai' => 0,
        'expense' => 0,
        'cash_net' => 0,
        'uang_fisik' => 0,
        'uang_minus' => 0,
        'harus_disetor' => 0,
        'total_disetor' => 0,
        'selisih' => 0,
        'stock_item_count' => 0,
        'stock_used_qty' => 0,
        'stock_ending' => 0,
    ];
}

private function dscAddTotal(array &$total, array $day): void
{
    foreach ($total as $key => $value) {
        $total[$key] = (float) $value + (float) ($day[$key] ?? 0);
    }
}

private function dscFinalizeTotal(array &$total): void
{
    $total['stock_item_count'] = (int) $total['stock_item_count'];
}

private function dscEmptyGrand(array $dates): array
{
    $grand = [
        'hari' => [],
        'sub_total' => $this->dscEmptyTotal(),
    ];

    foreach ($dates as $date) {
        $grand['hari'][$date] = $this->dscEmptyTotal();
    }

    return $grand;
}

private function dscAddGrandDay(array &$grand, string $date, array $day): void
{
    if (! isset($grand['hari'][$date])) {
        $grand['hari'][$date] = $this->dscEmptyTotal();
    }

    $this->dscAddTotal($grand['hari'][$date], $day);
    $this->dscAddTotal($grand['sub_total'], $day);
}

private function dscFinalizeGrand(array &$grand): void
{
    foreach ($grand['hari'] as &$day) {
        $this->dscFinalizeTotal($day);
    }
    unset($day);

    $this->dscFinalizeTotal($grand['sub_total']);
}

    public function undianReport(Request $request)
    {
        $tanggalMulai   = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $outletId       = $request->input('outlet_id'); // filter outlet

        // ambil list outlet untuk dropdown
        $outlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet', 'asc')
            ->get();

        $query = DB::table('tbl_undian_struk as us')
            ->leftJoin('tbl_outlets as o', 'o.id', '=', 'us.outlet_id')
            ->select([
                'us.id',
                'us.outlet_id',
                'o.nama_outlet as outlet',
                'us.nama_lengkap',
                'us.no_telp',
                'us.nomor_struk',
                'us.total_belanja',
                'us.nomor_undian',
                'us.tanggal_struk',
                'us.periode',
            ])
            ->orderBy('us.tanggal_struk', 'desc');

        if ($tanggalMulai) {
            $query->whereDate('us.tanggal_struk', '>=', $tanggalMulai);
        }

        if ($tanggalSelesai) {
            $query->whereDate('us.tanggal_struk', '<=', $tanggalSelesai);
        }

        if ($outletId) {
            $query->where('us.outlet_id', $outletId);
        }

        $dataUndian = $query->get();

        return view('Investor.Laporan.laporanUndianBerhadiah', compact(
            'dataUndian',
            'tanggalMulai',
            'tanggalSelesai',
            'outlets',
            'outletId'
        ));
    }

    public function undianExportExcel(Request $request)
    {
        $tanggalMulai   = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $outletId       = $request->input('outlet_id');

        $filename = 'laporan_undian_struk_' .
            ($tanggalMulai ?: 'all') . '_' .
            ($tanggalSelesai ?: 'all') . '_' .
            ($outletId ?: 'alloutlet') . '.xlsx';

        return Excel::download(
            new UndianExport($tanggalMulai, $tanggalSelesai, $outletId),
            $filename
        );
    }

    public function undianDestroy(Request $request)
    {
        $ids = $request->input('ids'); // array id dari checkbox

        if (!$ids || !is_array($ids) || count($ids) == 0) {
            return redirect()->back()->with('error', 'Pilih minimal 1 data untuk dihapus.');
        }

        $deleted = DB::table('tbl_undian_struk')
            ->whereIn('id', $ids)
            ->delete();

        if ($deleted) {
            return redirect()->back()->with('success', "Berhasil menghapus {$deleted} data.");
        }

        return redirect()->back()->with('error', 'Gagal menghapus data.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Jobs\ImportSalesJob;
use Illuminate\Http\Request;
use App\Models\M_Sales;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function dashboard(Request $request)
    {
        $filterApplied = $request->filled('tanggal_awal') && $request->filled('tanggal_akhir');

        // --- List outlet (untuk filter di frontend)
        $outlets = DB::table('tbl_transaksi_perhari as t')
            ->join('tbl_outlets as o', 't.outlet_id', '=', 'o.id')
            ->where('o.status', 'existing')
            ->select('o.id', 'o.nama_outlet')
            ->distinct()
            ->orderBy('o.nama_outlet')
            ->pluck('o.nama_outlet', 'o.id');

        // Default value
        $totalOmset = 0;
        $totalCU = 0;
        $omsetData = [];
        $cuData = [];
        $transaksiJam = [];
        $paretoData = [];
        $targetNextMonth = 0;
        $averageOmset = 0;

        if ($filterApplied) {
            $start = \Carbon\Carbon::parse($request->tanggal_awal);
            $end   = \Carbon\Carbon::parse($request->tanggal_akhir);

            // --- Query omset & CU dari tbl_omset_harian ---
            $baseQuery = DB::table('tbl_omset_harian as oh')
                ->join('tbl_outlets as o', 'oh.outlet_id', '=', 'o.id')
                ->where('o.status', 'existing')
                ->whereBetween('oh.tanggal', [$start, $end]);

            if ($request->filled('outlet')) {
                $baseQuery->where('o.nama_outlet', $request->outlet);
            }

            $omsetRaw = (clone $baseQuery)
                ->select('oh.tanggal', DB::raw('SUM(oh.total_omset) as total_omset'))
                ->groupBy('oh.tanggal')
                ->pluck('total_omset', 'oh.tanggal')
                ->toArray();

            $cuRaw = (clone $baseQuery)
                ->select('oh.tanggal', DB::raw('SUM(oh.total_cu) as total_cu'))
                ->groupBy('oh.tanggal')
                ->pluck('total_cu', 'oh.tanggal')
                ->toArray();

            // --- Buat range tanggal lengkap ---
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $d = $date->format('Y-m-d');
                $omsetData[] = ['tanggal' => $d, 'total_omset' => $omsetRaw[$d] ?? 0];
                $cuData[]    = ['tanggal' => $d, 'total_cu' => $cuRaw[$d] ?? 0];
            }

            // --- Hitung total ---
            $totalOmset = array_sum(array_column($omsetData, 'total_omset'));
            $totalCU    = array_sum(array_column($cuData, 'total_cu'));

            // --- Hitung Target bulan depan (contoh: naik 10% dari total bulan ini) ---
            $targetNextMonth = $totalOmset * 1.10;

            // --- Hitung rata-rata harian (average) ---
            $daysCount = $start->diffInDays($end) + 1;
            $averageOmset = $daysCount > 0 ? $totalOmset / $daysCount : 0;

            // --- Transaksi per jam ---
            $transaksiRaw = DB::table('tbl_transaksi_perhari as t')
                ->join('tbl_outlets as o', 't.outlet_id', '=', 'o.id')
                ->where('o.status', 'existing')
                ->where('t.item_status', 1)
                ->whereBetween('t.sesi_tanggal', [$start, $end]);

            if ($request->filled('outlet')) {
                $transaksiRaw->where('o.nama_outlet', $request->outlet);
            }

            $transaksiRaw = $transaksiRaw
                ->select(DB::raw('HOUR(t.tr_waktu) as jam'), DB::raw('COUNT(*) as total'))
                ->groupBy(DB::raw('HOUR(t.tr_waktu)'))
                ->pluck('total', 'jam')
                ->toArray();

            for ($h = 5; $h <= 23; $h++) {
                $jamStr = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                $transaksiJam[] = ['jam' => $jamStr, 'total' => $transaksiRaw[$h] ?? 0];
            }

            // --- Pareto top 10 menu ---
            $paretoQuery = DB::table('tbl_transaksi_perhari as t')
                ->join('tbl_outlets as o', 't.outlet_id', '=', 'o.id')
                ->where('o.status', 'existing')
                ->where('t.item_status', 1)
                ->whereBetween('t.sesi_tanggal', [$start, $end]);

            if ($request->filled('outlet')) {
                $paretoQuery->where('o.nama_outlet', $request->outlet);
            }

            $paretoData = $paretoQuery
                ->select('t.item_nama', DB::raw('SUM(t.item_jumlah) as total'))
                ->groupBy('t.item_nama')
                ->orderByDesc('total')
                ->take(10)
                ->get()
                ->map(fn($row) => ['produk' => $row->item_nama, 'total' => $row->total])
                ->toArray();
        }

        return view('dashboard', compact(
            'outlets',
            'filterApplied',
            'totalOmset',
            'totalCU',
            'omsetData',
            'cuData',
            'transaksiJam',
            'paretoData',
            'targetNextMonth',
            'averageOmset'
        ));
    }

    public function grandopening(Request $request)
    {
        $filterApplied = $request->filled('tanggal_awal') && $request->filled('tanggal_akhir');

        // --- List outlet (untuk filter di frontend)
        $outlets = DB::table('tbl_transaksi_perhari as t')
            ->join('tbl_outlets as o', 't.outlet_id', '=', 'o.id')
            ->where('o.status', 'go')
            ->select('o.id', 'o.nama_outlet')
            ->distinct()
            ->orderBy('o.nama_outlet')
            ->pluck('o.nama_outlet', 'o.id');

        // Default value
        $totalOmset = 0;
        $totalCU = 0;
        $omsetData = [];
        $cuData = [];
        $transaksiJam = [];
        $paretoData = [];
        $targetNextMonth = 0;
        $averageOmset = 0;

        if ($filterApplied) {
            $start = \Carbon\Carbon::parse($request->tanggal_awal);
            $end   = \Carbon\Carbon::parse($request->tanggal_akhir);

            // --- Query omset & CU dari tbl_omset_harian ---
            $baseQuery = DB::table('tbl_omset_harian as oh')
                ->join('tbl_outlets as o', 'oh.outlet_id', '=', 'o.id')
                ->where('o.status', 'go')
                ->whereBetween('oh.tanggal', [$start, $end]);

            if ($request->filled('outlet')) {
                $baseQuery->where('o.nama_outlet', $request->outlet);
            }

            $omsetRaw = (clone $baseQuery)
                ->select('oh.tanggal', DB::raw('SUM(oh.total_omset) as total_omset'))
                ->groupBy('oh.tanggal')
                ->pluck('total_omset', 'oh.tanggal')
                ->toArray();

            $cuRaw = (clone $baseQuery)
                ->select('oh.tanggal', DB::raw('SUM(oh.total_cu) as total_cu'))
                ->groupBy('oh.tanggal')
                ->pluck('total_cu', 'oh.tanggal')
                ->toArray();

            // --- Buat range tanggal lengkap ---
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $d = $date->format('Y-m-d');
                $omsetData[] = ['tanggal' => $d, 'total_omset' => $omsetRaw[$d] ?? 0];
                $cuData[]    = ['tanggal' => $d, 'total_cu' => $cuRaw[$d] ?? 0];
            }

            // --- Hitung total ---
            $totalOmset = array_sum(array_column($omsetData, 'total_omset'));
            $totalCU    = array_sum(array_column($cuData, 'total_cu'));

            // --- Hitung Target bulan depan (contoh: naik 10% dari total bulan ini) ---
            $targetNextMonth = $totalOmset * 1.10;

            // --- Hitung rata-rata harian (average) ---
            $daysCount = $start->diffInDays($end) + 1;
            $averageOmset = $daysCount > 0 ? $totalOmset / $daysCount : 0;

            // --- Transaksi per jam ---
            $transaksiRaw = DB::table('tbl_transaksi_perhari as t')
                ->join('tbl_outlets as o', 't.outlet_id', '=', 'o.id')
                ->where('o.status', 'go')
                ->where('t.item_status', 1)
                ->whereBetween('t.sesi_tanggal', [$start, $end]);

            if ($request->filled('outlet')) {
                $transaksiRaw->where('o.nama_outlet', $request->outlet);
            }

            $transaksiRaw = $transaksiRaw
                ->select(DB::raw('HOUR(t.tr_waktu) as jam'), DB::raw('COUNT(*) as total'))
                ->groupBy(DB::raw('HOUR(t.tr_waktu)'))
                ->pluck('total', 'jam')
                ->toArray();

            for ($h = 5; $h <= 23; $h++) {
                $jamStr = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                $transaksiJam[] = ['jam' => $jamStr, 'total' => $transaksiRaw[$h] ?? 0];
            }

            // --- Pareto top 10 menu ---
            $paretoQuery = DB::table('tbl_transaksi_perhari as t')
                ->join('tbl_outlets as o', 't.outlet_id', '=', 'o.id')
                ->where('o.status', 'go')
                ->where('t.item_status', 1)
                ->whereBetween('t.sesi_tanggal', [$start, $end]);

            if ($request->filled('outlet')) {
                $paretoQuery->where('o.nama_outlet', $request->outlet);
            }

            $paretoData = $paretoQuery
                ->select('t.item_nama', DB::raw('SUM(t.item_jumlah) as total'))
                ->groupBy('t.item_nama')
                ->orderByDesc('total')
                ->take(10)
                ->get()
                ->map(fn($row) => ['produk' => $row->item_nama, 'total' => $row->total])
                ->toArray();
        }

        return view('dashboard', compact(
            'outlets',
            'filterApplied',
            'totalOmset',
            'totalCU',
            'omsetData',
            'cuData',
            'transaksiJam',
            'paretoData',
            'targetNextMonth',
            'averageOmset'
        ));
    }

    public function ecommerce(Request $request)
    {
        $filterApplied = $request->filled('tanggal_awal') && $request->filled('tanggal_akhir');

        $platforms = ['shopeefood', 'grabfood', 'gofood'];

        // Ambil list outlet unik
        $outlets = DB::table('tbl_outlets')
            ->orderBy('nama_outlet')
            ->pluck('nama_outlet', 'id');

        // Inisialisasi array total per platform
        $totalOmsetPerPlat = array_fill_keys($platforms, 0);
        $totalCUPerPlat    = array_fill_keys($platforms, 0);
        $omsetDataPerPlat  = [];
        $cuDataPerPlat     = [];
        $transaksiJamPerPlat = [];
        $paretoDataPerPlat   = [];

        if ($filterApplied) {
            $start = \Carbon\Carbon::parse($request->tanggal_awal);
            $end   = \Carbon\Carbon::parse($request->tanggal_akhir);

            foreach ($platforms as $platform) {
                $query = DB::table('tbl_laporan_bulanan')
                    ->where('platform', $platform)
                    ->whereBetween('tanggal', [$start, $end]);

                if ($request->filled('outlet')) {
                    $outletId = array_search($request->outlet, $outlets->toArray());
                    if ($outletId) $query->where('outlet_id', $outletId);
                }

                $rows = $query->orderBy('tanggal')->get();

                $omsetData = $rows->map(fn($r) => ['tanggal' => $r->tanggal, 'total_omset' => $r->total_omset])->toArray();
                $cuData    = $rows->map(fn($r) => ['tanggal' => $r->tanggal, 'total_cu' => $r->total_cu])->toArray();

                $omsetDataPerPlat[$platform] = $omsetData;
                $cuDataPerPlat[$platform]    = $cuData;

                $totalOmsetPerPlat[$platform] = array_sum(array_column($omsetData, 'total_omset'));
                $totalCUPerPlat[$platform]    = array_sum(array_column($cuData, 'total_cu'));

                // Transaksi per jam (jika ingin grafik per jam)
                $jamRaw = DB::table('tbl_laporan_bulanan')
                    ->where('platform', $platform)
                    ->whereBetween('tanggal', [$start, $end])
                    ->when($request->filled('outlet') && $outletId, fn($q) => $q->where('outlet_id', $outletId))
                    ->select(DB::raw('HOUR(created_at) as jam'), DB::raw('SUM(total_cu) as total'))
                    ->groupBy(DB::raw('HOUR(created_at)'))
                    ->pluck('total', 'jam')
                    ->toArray();

                $jamData = [];
                for ($h = 5; $h <= 23; $h++) {
                    $jamStr = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                    $jamData[] = ['jam' => $jamStr, 'total' => $jamRaw[$h] ?? 0];
                }
                $transaksiJamPerPlat[$platform] = $jamData;

                // Pareto Top 10 per platform (dari transaksi perhari)
                $paretoDataPerPlat[$platform] = DB::table('tbl_transaksi_perhari as t')
                    ->join('tbl_outlets as o', 't.outlet_id', '=', 'o.id')
                    ->where('t.item_status', 1)
                    ->whereRaw('LOWER(t.item_varian) LIKE ?', ["%{$platform}%"])
                    ->whereBetween('t.sesi_tanggal', [$start, $end])
                    ->when($request->filled('outlet'), fn($q) => $q->where('o.nama_outlet', $request->outlet))
                    ->select('t.item_nama', DB::raw('SUM(t.item_jumlah) as total'))
                    ->groupBy('t.item_nama')
                    ->orderByDesc('total')
                    ->take(10)
                    ->get()
                    ->map(fn($r) => ['produk' => $r->item_nama, 'total' => $r->total])
                    ->toArray();
            }
        }

        // Total keseluruhan (semua platform)
        $totalOmset = array_sum($totalOmsetPerPlat);
        $totalCU    = array_sum($totalCUPerPlat);

        // Hitung target & average per platform
        $targets = [];
        $averages = [];
        foreach ($platforms as $platform) {
            $targets[$platform] = $totalOmsetPerPlat[$platform] * 1.1;
            $averages[$platform] = count($omsetDataPerPlat[$platform] ?? []) > 0
                ? $totalOmsetPerPlat[$platform] / count($omsetDataPerPlat[$platform])
                : 0;
        }

        return view('dashboardEcom', compact(
            'outlets',
            'filterApplied',
            'totalOmset',
            'totalCU',
            'totalOmsetPerPlat',
            'totalCUPerPlat',
            'omsetDataPerPlat',
            'cuDataPerPlat',
            'transaksiJamPerPlat',
            'paretoDataPerPlat',
            'targets',
            'averages'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Imports\StockImport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use App\Exports\QcrExport;
use App\Jobs\GenerateQcrExportJob;

class QCRController extends Controller
{
    /*
     |--------------------------------------------------------------------------
     | MASTER FIX QCR USAGE DSC SHIFT 1 PLUS SHIFT 2
     |--------------------------------------------------------------------------
     | Summary QCR:
     | Stock Available = OPEN Shift 1 / ending H-1
     | TOTAL           = Stock Available + Purchase In + Mutasi In - Mutasi Out
     | Usage DSC       = TOTAL - Ending
     |
     | Dengan ini Usage DSC Summary tidak lagi mengambil Shift 2 saja.
     */

    /*
     |--------------------------------------------------------------------------
     | MASTER FIX ADJUSTMENT AS ENDING CORRECTION
     |--------------------------------------------------------------------------
     | Final rumus:
     | TOTAL  = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
     | ENDING = ENDING BASE + ADJ
     | USED   = TOTAL - ENDING
     */

    /*
     |--------------------------------------------------------------------------
     | MASTER FIX TOTAL USED FORMULA DSC QCR
     |--------------------------------------------------------------------------
     | Rumus stok diseragamkan:
     |
     | TOTAL  = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
     | ENDING = ENDING BASE + ADJ
     | USED   = TOTAL - ENDING
     |
     | Adjustment tidak masuk ke TOTAL.
     | Adjustment dipakai sebagai koreksi ENDING, lalu USED dihitung ulang.
     */

    // public function dailyStockControl(Request $request)
    // {
    //     $todayRaw     = (string) $request->get('tanggal', date('Y-m-d'));
    //     $outletIdRaw  = $request->get('outlet_id', '');
    //     $shiftFilter  = (string) $request->get('shift_filter', 'all');
    //     $today        = $this->normalizeDateToYmd($todayRaw);
    
    //     $startDateRaw = (string) $request->get('start_date', '');
    //     $endDateRaw   = (string) $request->get('end_date', '');
    
    //     $startDate = $startDateRaw ? $this->normalizeDateToYmd($startDateRaw) : null;
    //     $endDate   = $endDateRaw ? $this->normalizeDateToYmd($endDateRaw) : null;
    
    //     if ($startDate && $endDate && $startDate > $endDate) {
    //         [$startDate, $endDate] = [$endDate, $startDate];
    //     }
    
    //     $shiftFilter = in_array($shiftFilter, ['all', '1', '2'], true) ? $shiftFilter : 'all';
    
    //     $outlets = collect();
    
    //     $selectedOutlet = null;
    //     $selectedOutletIds = [];
    //     $selectedOutletLabel = null;
    
    //     if ($outletIdRaw !== '' && $outletIdRaw !== null) {
    //         $selectedOutlet = DB::table('tbl_outlets')
    //             ->select('id', 'nama_outlet', 'status')
    //             ->where('id', (int) $outletIdRaw)
    //             ->first();
    
    //         if ($selectedOutlet) {
    //             $sameOutletRows = DB::table('tbl_outlets')
    //                 ->select('id', 'nama_outlet', 'status')
    //                 ->whereRaw('LOWER(TRIM(nama_outlet)) = ?', [mb_strtolower(trim($selectedOutlet->nama_outlet))])
    //                 ->orderBy('id')
    //                 ->get();
    
    //             $selectedOutletIds = $sameOutletRows->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
    //             $selectedOutletLabel = $selectedOutlet->nama_outlet;
    
    //             // override selected outlet agar UI tahu ini outlet gabungan
    //             $selectedOutlet = (object) [
    //                 'id' => (int) $outletIdRaw,
    //                 'nama_outlet' => $selectedOutlet->nama_outlet,
    //                 'status' => $selectedOutlet->status,
    //                 'merged_ids' => $selectedOutletIds,
    //                 'is_merged' => count($selectedOutletIds) > 1,
    //             ];
    //         }
    //     }
    
    //     $isSuperadmin   = auth()->user()?->role === 'superadmin';
    //     $missingOutlets = collect();
    
    //     if ($isSuperadmin && $startDate && $endDate) {
    //         $missingOutlets = DB::table('tbl_outlets as o')
    //             ->select(
    //                 DB::raw('MIN(o.id) as id'),
    //                 'o.nama_outlet',
    //                 DB::raw('GROUP_CONCAT(o.id ORDER BY o.id) as merged_ids'),
    //                 DB::raw('(
    //                     SELECT MAX(DATE(s2.tanggal))
    //                     FROM tbl_stock s2
    //                     WHERE s2.outlet_id IN (
    //                         SELECT o2.id
    //                         FROM tbl_outlets o2
    //                         WHERE LOWER(TRIM(o2.nama_outlet)) = LOWER(TRIM(o.nama_outlet))
    //                     )
    //                 ) as last_input_date')
    //             )
    //             ->groupBy('o.nama_outlet')
    //             ->havingRaw('SUM(
    //                 CASE WHEN EXISTS (
    //                     SELECT 1
    //                     FROM tbl_stock s
    //                     WHERE s.outlet_id = o.id
    //                     AND DATE(s.tanggal) BETWEEN ? AND ?
    //                 ) THEN 1 ELSE 0 END
    //             ) = 0', [$startDate, $endDate])
    //             ->orderBy('o.nama_outlet')
    //             ->get();
    //     }
    
    //     if ($outletIdRaw === '' || $outletIdRaw === null || !$selectedOutlet) {
    //         return view('Investor.Inventory.dailyStockControl', [
    //             'today'               => $today,
    //             'outletId'            => $outletIdRaw,
    //             'shiftFilter'         => $shiftFilter,
    //             'outlets'             => $outlets,
    //             'selectedOutlet'      => $selectedOutlet,
    //             'selectedOutletIds'   => $selectedOutletIds,
    //             'selectedOutletLabel' => $selectedOutletLabel,
    
    //             'sales_shift_1'       => 0,
    //             'sales_shift_2'       => 0,
    
    //             'rekapRows'           => [],
    //             'shiftRows'           => [],
    
    //             'omsetActive'         => $this->defaultOmsetActive($today),
    
    //             'startDate'           => $startDateRaw,
    //             'endDate'             => $endDateRaw,
    //             'missingOutlets'      => $missingOutlets,
    //             'isSuperadmin'        => $isSuperadmin,
    //         ]);
    //     }
    
    //     // fallback kalau tidak ada outlet kembar
    //     if (empty($selectedOutletIds)) {
    //         $selectedOutletIds = [(int) $outletIdRaw];
    //     }
    
    //     // =========================
    //     // 1) OMSET GABUNGAN SEMUA ID OUTLET SAMA
    //     // =========================
    //     $omsetRows = DB::table('tbl_dsc_omset_setoran')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->whereDate('tanggal', $today)
    //         ->get();
    
    //     $omsetRow = (object) [
    //         's1_total_transaction' => (float) $omsetRows->sum('s1_total_transaction'),
    //         's2_total_transaction' => (float) $omsetRows->sum('s2_total_transaction'),
    //     ];
    
    //     $mode = $omsetRows->isNotEmpty() ? 'EXACT' : 'NO_DATA';
    
    //     $omsetLastDate = DB::table('tbl_dsc_omset_setoran')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->max('tanggal');
    
    //     $omsetActive = $this->buildOmsetActive(
    //         $omsetRows->isNotEmpty() ? $omsetRow : null,
    //         $shiftFilter,
    //         $today,
    //         $omsetLastDate,
    //         $mode
    //     );
    
    //     $salesShift1 = (float) ($omsetRow->s1_total_transaction ?? 0);
    //     $salesShift2 = (float) ($omsetRow->s2_total_transaction ?? 0);
    
    //     // =========================
    //     // 2) MASTER BAHAN
    //     // =========================
    //     $bahanList = DB::table('tbl_bahan_dsc')
    //         ->select('id', 'nama_bahan', 'satuan')
    //         ->where('is_active', 1)
    //         ->orderBy('id')
    //         ->get();
    
    //     // =========================
    //     // 3) STOCK HARI INI GABUNGAN SEMUA OUTLET ID
    //     // ambil terbaru per bahan + shift dari seluruh outlet kembar
    //     // =========================
    //     $stockRows = DB::table('tbl_stock')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->whereDate('tanggal', $today)
    //         ->orderByDesc('tanggal')
    //         ->orderByDesc('id')
    //         ->get();
    
    //     $stockMap = [];
    //     foreach ($stockRows as $sr) {
    //         $bid = (int) $sr->bahan_id;
    //         $sh  = (int) $sr->shift;
    
    //         if (!isset($stockMap[$bid][$sh])) {
    //             $stockMap[$bid][$sh] = $sr;
    //         }
    //     }
    
    //     $getPrevClosing = function (array $outletIds, int $bahanId, string $todayYmd): float {
    //         $row = DB::table('tbl_stock')
    //             ->whereIn('outlet_id', $outletIds)
    //             ->where('bahan_id', $bahanId)
    //             ->whereDate('tanggal', '<', $todayYmd)
    //             ->orderBy('tanggal', 'desc')
    //             ->orderBy('shift', 'desc')
    //             ->orderBy('id', 'desc')
    //             ->first();
    
    //         return (float) ($row->ending_stock ?? 0);
    //     };
    
    //     $getOpenForShift = function (array $outletIds, int $bahanId, string $todayYmd, int $shift, $r1Today, $getPrevClosing): float {
    //         if ($shift === 1) {
    //             return (float) $getPrevClosing($outletIds, $bahanId, $todayYmd);
    //         }
    
    //         if ($r1Today) {
    //             return (float) ($r1Today->ending_stock ?? 0);
    //         }
    
    //         return (float) $getPrevClosing($outletIds, $bahanId, $todayYmd);
    //     };
    
    //     $getTodayClosing = function ($r1Today, $r2Today): float {
    //         if ($r2Today) {
    //             return (float) ($r2Today->ending_stock ?? 0);
    //         }
    //         if ($r1Today) {
    //             return (float) ($r1Today->ending_stock ?? 0);
    //         }
    //         return 0.0;
    //     };
    
    //     $calcShiftValues = function (float $open, $row): array {
    //         $pin    = $row ? (float) ($row->purchase_in ?? 0) : 0.0;
    //         $mi     = $row ? (float) ($row->mutasi_in ?? 0) : 0.0;
    //         $mo     = $row ? (float) ($row->mutasi_out ?? 0) : 0.0;
    //         $adj    = $row ? (float) ($row->adjustment_qty ?? 0) : 0.0;
    //         $ending = $row ? (float) ($row->ending_stock ?? 0) : 0.0;
    
    //         $total  = $open + $pin + $mi - $mo;
    //         $used   = $row && $row->used_qty !== null
    //             ? (float) $row->used_qty
    //             : ($total - $ending);
    
    //         $wP      = $row ? (float) ($row->waste_product ?? 0) : 0.0;
    //         $wB      = $row ? (float) ($row->waste_bahan ?? 0) : 0.0;
    //         $wTepung = $row ? (float) ($row->waste_tepung ?? 0) : 0.0;
    //         $uang    = $row ? (float) ($row->uang_plus ?? 0) : 0.0;
    //         $ket     = $row ? (string) ($row->keterangan ?? '') : '';
            
    //         return [
    //             'open'         => $open,
    //             'pin'          => $pin,
    //             'mi'           => $mi,
    //             'mo'           => $mo,
    //             'adj'          => $adj,
    //             'total'        => $total,
    //             'ending_stock' => $ending,
    //             'used'         => $used,
    //             'wP'           => $wP,
    //             'wB'           => $wB,
    //             'wT'           => $wP + $wB + $wTepung,
    //             'uang'         => $uang,
    //             'ket'          => $ket,
    //         ];
    //     };
    
    //     // =========================
    //     // 4) REKAP
    //     // =========================
    //     $rekapRows = [];
    //     $no = 1;
    
    //     foreach ($bahanList as $b) {
    //         $bahanId = (int) $b->id;
    
    //         $r1 = $stockMap[$bahanId][1] ?? null;
    //         $r2 = $stockMap[$bahanId][2] ?? null;
    
    //         $openPrev = (float) $getPrevClosing($selectedOutletIds, $bahanId, $today);
    //         $openS1   = (float) $getOpenForShift($selectedOutletIds, $bahanId, $today, 1, $r1, $getPrevClosing);
    //         $openS2   = (float) $getOpenForShift($selectedOutletIds, $bahanId, $today, 2, $r1, $getPrevClosing);
    
    //         $s1 = $calcShiftValues($openS1, $r1);
    //         $s2 = $calcShiftValues($openS2, $r2);
    
    //         if ($shiftFilter === '1') {
    //             $openRekap = $s1['open'];
    //             $pin       = $s1['pin'];
    //             $mi        = $s1['mi'];
    //             $mo        = $s1['mo'];
    //             $adj       = $s1['adj'];
    //             $total     = $s1['total'];
    //             $ending    = $s1['ending_stock'];
    //             $used      = $s1['used'];
    //             $wP        = $s1['wP'];
    //             $wB        = $s1['wB'];
    //             $wT        = $s1['wT'];
    //             $uang      = $s1['uang'];
    //             $ketParts  = !empty($s1['ket']) ? ['S1: '.$s1['ket']] : [];
    //         } elseif ($shiftFilter === '2') {
    //             $openRekap = $s2['open'];
    //             $pin       = $s2['pin'];
    //             $mi        = $s2['mi'];
    //             $mo        = $s2['mo'];
    //             $adj       = $s2['adj'];
    //             $total     = $s2['total'];
    //             $ending    = $s2['ending_stock'];
    //             $used      = $s2['used'];
    //             $wP        = $s2['wP'];
    //             $wB        = $s2['wB'];
    //             $wT        = $s2['wT'];
    //             $uang      = $s2['uang'];
    //             $ketParts  = !empty($s2['ket']) ? ['S2: '.$s2['ket']] : [];
    //         } else {
    //             $openRekap = $openPrev;
    //             $pin       = $s1['pin'] + $s2['pin'];
    //             $mi        = $s1['mi'] + $s2['mi'];
    //             $mo        = $s1['mo'] + $s2['mo'];
    //             $adj       = $s1['adj'] + $s2['adj'];
    //             $ending    = (float) $getTodayClosing($r1, $r2);
    //             $total     = $openRekap + $pin + $mi - $mo;
    //             $used      = $total - $ending;
    //             $wP        = $s1['wP'] + $s2['wP'];
    //             $wB        = $s1['wB'] + $s2['wB'];
    //             $wT        = $wP + $wB;
    //             $uang      = $s1['uang'] + $s2['uang'];
    
    //             $ketParts = [];
    //             if (!empty($s1['ket'])) $ketParts[] = 'S1: '.$s1['ket'];
    //             if (!empty($s2['ket'])) $ketParts[] = 'S2: '.$s2['ket'];
    //         }
    
    //         $namaNorm = strtolower(trim((string) $b->nama_bahan));
    //         $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wT) : 0.0;
    //         $closingToday = (float) $getTodayClosing($r1, $r2);
    
    //         $rekapRows[] = [
    //             'no'               => $no++,
    //             'nama'             => (string) $b->nama_bahan,
    //             'sat'              => (string) $b->satuan,
    //             'open'             => $openRekap,
    //             'pin'              => $pin,
    //             'mi'               => $mi,
    //             'mo'               => $mo,
    //             'adj'              => $adj,
    //             'total'            => $total,
    //             'ending_stock'     => $ending,
    //             'used'             => $used,
    //             'wP'               => $wP,
    //             'wB'               => $wB,
    //             'wT'               => $wT,
    //             'actualTepung'     => $actualTepung,
    //             'shift1'           => $s1['used'],
    //             'shift2'           => $s2['used'],
    //             'uang'             => $uang,
    //             'ket'              => implode(' | ', $ketParts),
    //             'open_stock_right' => $closingToday,
    //         ];
    //     }
    
    //     // =========================
    //     // 5) DETAIL SHIFT
    //     // =========================
    //     $shiftRows = [];
    //     $no2 = 1;
    
    //     $shifts = match ($shiftFilter) {
    //         '1' => [1],
    //         '2' => [2],
    //         default => [1, 2],
    //     };
    
    //     foreach ($bahanList as $b) {
    //         $bahanId = (int) $b->id;
    //         $r1Today = $stockMap[$bahanId][1] ?? null;
    
    //         foreach ($shifts as $sh) {
    //             $sr = $stockMap[$bahanId][$sh] ?? null;
    //             $open = (float) $getOpenForShift($selectedOutletIds, $bahanId, $today, (int) $sh, $r1Today, $getPrevClosing);
    //             $v = $calcShiftValues($open, $sr);
    
    //             $shiftRows[] = [
    //                 'no'           => $no2++,
    //                 'nama'         => (string) $b->nama_bahan,
    //                 'sat'          => (string) $b->satuan,
    //                 'shift'        => (int) $sh,
    //                 'bahan_id'     => $bahanId,
    //                 'open'         => $v['open'],
    //                 'pin'          => $v['pin'],
    //                 'mi'           => $v['mi'],
    //                 'mo'           => $v['mo'],
    //                 'adj'          => $v['adj'],
    //                 'total'        => $v['total'],
    //                 'ending_stock' => $v['ending_stock'],
    //                 'used'         => $v['used'],
    //                 'wP'           => $v['wP'],
    //                 'wB'           => $v['wB'],
    //                 'wT'           => $v['wT'],
    //                 'ket'          => $v['ket'],
    //             ];
    //         }
    //     }
    
    //     return view('Investor.Inventory.dailyStockControl', [
    //         'today'               => $today,
    //         'outletId'            => (int) $outletIdRaw,
    //         'shiftFilter'         => $shiftFilter,
    //         'outlets'             => $outlets,
    //         'selectedOutlet'      => $selectedOutlet,
    //         'selectedOutletIds'   => $selectedOutletIds,
    //         'selectedOutletLabel' => $selectedOutletLabel,
    
    //         'sales_shift_1'       => $salesShift1,
    //         'sales_shift_2'       => $salesShift2,
    
    //         'rekapRows'           => $rekapRows,
    //         'shiftRows'           => $shiftRows,
    
    //         'omsetActive'         => $omsetActive,
    
    //         'startDate'           => $startDateRaw,
    //         'endDate'             => $endDateRaw,
    //         'missingOutlets'      => $missingOutlets,
    //         'isSuperadmin'        => $isSuperadmin,
    //     ]);
    // }

    // public function dailyStockControl(Request $request)
    // {
    //     $todayRaw     = (string) $request->get('tanggal', date('Y-m-d'));
    //     $outletIdRaw  = $request->get('outlet_id', '');
    //     $shiftFilter  = (string) $request->get('shift_filter', 'all');
    //     $today        = $this->normalizeDateToYmd($todayRaw);

    //     $startDateRaw = (string) $request->get('start_date', '');
    //     $endDateRaw   = (string) $request->get('end_date', '');

    //     $startDate = $startDateRaw ? $this->normalizeDateToYmd($startDateRaw) : null;
    //     $endDate   = $endDateRaw ? $this->normalizeDateToYmd($endDateRaw) : null;

    //     if ($startDate && $endDate && $startDate > $endDate) {
    //         [$startDate, $endDate] = [$endDate, $startDate];
    //     }

    //     $shiftFilter = in_array($shiftFilter, ['all', '1', '2'], true) ? $shiftFilter : 'all';

    //     $outlets = collect();

    //     $selectedOutlet = null;
    //     $selectedOutletIds = [];
    //     $selectedOutletLabel = null;

    //     if ($outletIdRaw !== '' && $outletIdRaw !== null) {
    //         $selectedOutlet = DB::table('tbl_outlets')
    //             ->select('id', 'nama_outlet', 'status')
    //             ->where('id', (int) $outletIdRaw)
    //             ->first();

    //         if ($selectedOutlet) {
    //             $sameOutletRows = DB::table('tbl_outlets')
    //                 ->select('id', 'nama_outlet', 'status')
    //                 ->whereRaw('LOWER(TRIM(nama_outlet)) = ?', [mb_strtolower(trim($selectedOutlet->nama_outlet))])
    //                 ->orderBy('id')
    //                 ->get();

    //             $selectedOutletIds = $sameOutletRows->pluck('id')->map(fn ($v) => (int) $v)->values()->all();
    //             $selectedOutletLabel = $selectedOutlet->nama_outlet;

    //             $selectedOutlet = (object) [
    //                 'id' => (int) $outletIdRaw,
    //                 'nama_outlet' => $selectedOutlet->nama_outlet,
    //                 'status' => $selectedOutlet->status,
    //                 'merged_ids' => $selectedOutletIds,
    //                 'is_merged' => count($selectedOutletIds) > 1,
    //             ];
    //         }
    //     }

    //     $isSuperadmin   = auth()->user()?->role === 'superadmin';
    //     $missingOutlets = collect();

    //     if ($isSuperadmin && $startDate && $endDate) {
    //         $missingOutlets = DB::table('tbl_outlets as o')
    //             ->select(
    //                 DB::raw('MIN(o.id) as id'),
    //                 'o.nama_outlet',
    //                 DB::raw('GROUP_CONCAT(o.id ORDER BY o.id) as merged_ids'),
    //                 DB::raw('(
    //                     SELECT MAX(DATE(s2.tanggal))
    //                     FROM tbl_stock s2
    //                     WHERE s2.outlet_id IN (
    //                         SELECT o2.id
    //                         FROM tbl_outlets o2
    //                         WHERE LOWER(TRIM(o2.nama_outlet)) = LOWER(TRIM(o.nama_outlet))
    //                     )
    //                 ) as last_input_date')
    //             )
    //             ->groupBy('o.nama_outlet')
    //             ->havingRaw('SUM(
    //                 CASE WHEN EXISTS (
    //                     SELECT 1
    //                     FROM tbl_stock s
    //                     WHERE s.outlet_id = o.id
    //                     AND DATE(s.tanggal) BETWEEN ? AND ?
    //                 ) THEN 1 ELSE 0 END
    //             ) = 0', [$startDate, $endDate])
    //             ->orderBy('o.nama_outlet')
    //             ->get();
    //     }

    //     if ($outletIdRaw === '' || $outletIdRaw === null || !$selectedOutlet) {
    //         return view('Investor.Inventory.dailyStockControl', [
    //             'today'               => $today,
    //             'outletId'            => $outletIdRaw,
    //             'shiftFilter'         => $shiftFilter,
    //             'outlets'             => $outlets,
    //             'selectedOutlet'      => $selectedOutlet,
    //             'selectedOutletIds'   => $selectedOutletIds,
    //             'selectedOutletLabel' => $selectedOutletLabel,

    //             'sales_shift_1'       => 0,
    //             'sales_shift_2'       => 0,

    //             'rekapRows'           => [],
    //             'shiftRows'           => [],

    //             'omsetActive'         => $this->defaultOmsetActive($today),

    //             'startDate'           => $startDateRaw,
    //             'endDate'             => $endDateRaw,
    //             'missingOutlets'      => $missingOutlets,
    //             'isSuperadmin'        => $isSuperadmin,
    //         ]);
    //     }

    //     if (empty($selectedOutletIds)) {
    //         $selectedOutletIds = [(int) $outletIdRaw];
    //     }

    //     // =========================
    //     // DISPLAY OUTLET ID
    //     // Samakan dengan dscLoad: opening pakai display outlet id
    //     // =========================
    //     $displayOutletIds = $selectedOutletIds;

    //     // =========================
    //     // 1) OMSET GABUNGAN SEMUA ID OUTLET SAMA
    //     // =========================
    //     $omsetRows = DB::table('tbl_dsc_omset_setoran')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->whereDate('tanggal', $today)
    //         ->get();

    //     $omsetRow = (object) [
    //         's1_total_transaction' => (float) $omsetRows->sum('s1_total_transaction'),
    //         's2_total_transaction' => (float) $omsetRows->sum('s2_total_transaction'),
    //     ];

    //     $mode = $omsetRows->isNotEmpty() ? 'EXACT' : 'NO_DATA';

    //     $omsetLastDate = DB::table('tbl_dsc_omset_setoran')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->max('tanggal');

    //     $omsetActive = $this->buildOmsetActive(
    //         $omsetRows->isNotEmpty() ? $omsetRow : null,
    //         $shiftFilter,
    //         $today,
    //         $omsetLastDate,
    //         $mode
    //     );

    //     $salesShift1 = (float) ($omsetRow->s1_total_transaction ?? 0);
    //     $salesShift2 = (float) ($omsetRow->s2_total_transaction ?? 0);

    //     // =========================
    //     // 2) MASTER BAHAN
    //     // =========================
    //     $bahanList = DB::table('tbl_bahan_dsc')
    //         ->select('id', 'nama_bahan', 'satuan')
    //         ->where('is_active', 1)
    //         ->orderBy('id')
    //         ->get();

    //     // =========================
    //     // 3) DATA SHIFT HARI INI
    //     // Samakan dengan dscLoad:
    //     // per shift => draft terbaru > final terbaru
    //     // =========================
    //     $draftShift1Rows = DB::table('tbl_stock_draft')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->whereDate('tanggal', $today)
    //         ->where('shift', 1)
    //         ->where('is_draft', 1)
    //         ->orderByDesc('updated_at')
    //         ->orderByDesc('id')
    //         ->get()
    //         ->unique('bahan_id')
    //         ->keyBy('bahan_id');

    //     $finalShift1Rows = DB::table('tbl_stock')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->whereDate('tanggal', $today)
    //         ->where('shift', '1')
    //         ->orderByDesc('updated_at')
    //         ->orderByDesc('id')
    //         ->get()
    //         ->unique('bahan_id')
    //         ->keyBy('bahan_id');

    //     $draftShift2Rows = DB::table('tbl_stock_draft')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->whereDate('tanggal', $today)
    //         ->where('shift', 2)
    //         ->where('is_draft', 1)
    //         ->orderByDesc('updated_at')
    //         ->orderByDesc('id')
    //         ->get()
    //         ->unique('bahan_id')
    //         ->keyBy('bahan_id');

    //     $finalShift2Rows = DB::table('tbl_stock')
    //         ->whereIn('outlet_id', $selectedOutletIds)
    //         ->whereDate('tanggal', $today)
    //         ->where('shift', '2')
    //         ->orderByDesc('updated_at')
    //         ->orderByDesc('id')
    //         ->get()
    //         ->unique('bahan_id')
    //         ->keyBy('bahan_id');

    //     // kasih marker source biar badge tetap jalan
    //     foreach ($draftShift1Rows as $row) $row->row_source = 'draft';
    //     foreach ($finalShift1Rows as $row) $row->row_source = 'final';
    //     foreach ($draftShift2Rows as $row) $row->row_source = 'draft';
    //     foreach ($finalShift2Rows as $row) $row->row_source = 'final';

    //     // helper hitung nilai row
    //     $calcShiftValues = function (float $open, $row): array {
    //         $pin    = $row ? (float) ($row->purchase_in ?? 0) : 0.0;
    //         $mi     = $row ? (float) ($row->mutasi_in ?? 0) : 0.0;
    //         $mo     = $row ? (float) ($row->mutasi_out ?? 0) : 0.0;
    //         $adj    = $row ? (float) ($row->adjustment_qty ?? 0) : 0.0;
    //         $ending = $row && $row->ending_stock !== null ? (float) $row->ending_stock : 0.0;

    //         $total  = $open + $pin + $mi - $mo + $adj;
    //         $used   = $total - $ending;

    //         $wP = $row ? (float) ($row->waste_product ?? 0) : 0.0;
    //         $wB = $row ? (float) ($row->waste_bahan ?? 0) : 0.0;

    //         $wTDb = $row ? (float) ($row->waste_tepung ?? 0) : 0.0;
    //         $wT = $wTDb > 0 ? $wTDb : ($wP + $wB);

    //         $uang   = $row ? (float) ($row->uang_plus ?? 0) : 0.0;
    //         $ket    = $row ? (string) ($row->keterangan ?? '') : '';
    //         $source = $row->row_source ?? null;

    //         return [
    //             'open'         => $open,
    //             'pin'          => $pin,
    //             'mi'           => $mi,
    //             'mo'           => $mo,
    //             'adj'          => $adj,
    //             'total'        => $total,
    //             'ending_stock' => $ending,
    //             'used'         => $used,
    //             'wP'           => $wP,
    //             'wB'           => $wB,
    //             'wT'           => $wT,
    //             'uang'         => $uang,
    //             'ket'          => $ket,
    //             'source'       => $source,
    //         ];
    //     };

    //     // =========================
    //     // 4) REKAP
    //     // PERSIS IKUT POLA dscLoad
    //     // =========================
    //     $rekapRows = [];
    //     $no = 1;

    //     foreach ($bahanList as $b) {
    //         $bahanId = (int) $b->id;

    //         // shift aktif: draft > final
    //         $r1 = $draftShift1Rows[$bahanId] ?? $finalShift1Rows[$bahanId] ?? null;
    //         $r2 = $draftShift2Rows[$bahanId] ?? $finalShift2Rows[$bahanId] ?? null;

    //         // OPEN SHIFT 1 = persis dscLoad
    //         $openS1 = (float) $this->getOpeningStockUi($displayOutletId, $bahanId, $today, 1);

    //         // OPEN SHIFT 2 = ending S1 hari sama (draft > final), fallback getOpeningStockUi(...,2)
    //         if ($r1 && $r1->ending_stock !== null) {
    //             $openS2 = (float) $r1->ending_stock;
    //         } else {
    //             $openS2 = (float) $this->getOpeningStockUi($displayOutletId, $bahanId, $today, 2);
    //         }

    //         $s1 = $calcShiftValues($openS1, $r1);
    //         $s2 = $calcShiftValues($openS2, $r2);

    //         if ($shiftFilter === '1') {
    //             $openRekap = $s1['open'];
    //             $pin       = $s1['pin'];
    //             $mi        = $s1['mi'];
    //             $mo        = $s1['mo'];
    //             $adj       = $s1['adj'];
    //             $total     = $s1['total'];
    //             $ending    = $s1['ending_stock'];
    //             $used      = $s1['used'];
    //             $wP        = $s1['wP'];
    //             $wB        = $s1['wB'];
    //             $wT        = $s1['wT'];
    //             $uang      = $s1['uang'];
    //             $ketParts  = !empty($s1['ket']) ? ['S1: ' . $s1['ket']] : [];
    //             $rowSource = $s1['source'] ?? null;
    //         } elseif ($shiftFilter === '2') {
    //             $openRekap = $s2['open'];
    //             $pin       = $s2['pin'];
    //             $mi        = $s2['mi'];
    //             $mo        = $s2['mo'];
    //             $adj       = $s2['adj'];
    //             $total     = $s2['total'];
    //             $ending    = $s2['ending_stock'];
    //             $used      = $s2['used'];
    //             $wP        = $s2['wP'];
    //             $wB        = $s2['wB'];
    //             $wT        = $s2['wT'];
    //             $uang      = $s2['uang'];
    //             $ketParts  = !empty($s2['ket']) ? ['S2: ' . $s2['ket']] : [];
    //             $rowSource = $s2['source'] ?? null;
    //         } else {
    //             // ALL = open pakai open shift 1 yang sama seperti dscLoad
    //             $openRekap = $openS1;
    //             $pin       = $s1['pin'] + $s2['pin'];
    //             $mi        = $s1['mi'] + $s2['mi'];
    //             $mo        = $s1['mo'] + $s2['mo'];
    //             $adj       = $s1['adj'] + $s2['adj'];

    //             $ending = 0.0;
    //             if ($r2 && $r2->ending_stock !== null) {
    //                 $ending = (float) $r2->ending_stock;
    //             } elseif ($r1 && $r1->ending_stock !== null) {
    //                 $ending = (float) $r1->ending_stock;
    //             }

    //             $total     = $openRekap + $pin + $mi - $mo + $adj;
    //             $used      = $total - $ending;
    //             $wP        = $s1['wP'] + $s2['wP'];
    //             $wB        = $s1['wB'] + $s2['wB'];
    //             $wT        = $s1['wT'] + $s2['wT'];
    //             $uang      = $s1['uang'] + $s2['uang'];

    //             $ketParts = [];
    //             if (!empty($s1['ket'])) $ketParts[] = 'S1: ' . $s1['ket'];
    //             if (!empty($s2['ket'])) $ketParts[] = 'S2: ' . $s2['ket'];

    //             $source1 = $s1['source'] ?? null;
    //             $source2 = $s2['source'] ?? null;

    //             $rowSource = ($source1 === 'draft' || $source2 === 'draft')
    //                 ? 'draft'
    //                 : (($source1 === 'final' || $source2 === 'final') ? 'final' : null);
    //         }

    //         $namaNorm = strtolower(trim((string) $b->nama_bahan));
    //         $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wT) : 0.0;

    //         $closingToday = 0.0;
    //         if ($r2 && $r2->ending_stock !== null) {
    //             $closingToday = (float) $r2->ending_stock;
    //         } elseif ($r1 && $r1->ending_stock !== null) {
    //             $closingToday = (float) $r1->ending_stock;
    //         }

    //         $rekapRows[] = [
    //             'no'               => $no++,
    //             'nama'             => (string) $b->nama_bahan,
    //             'sat'              => (string) $b->satuan,
    //             'source'           => $rowSource,
    //             'open'             => $openRekap,
    //             'pin'              => $pin,
    //             'mi'               => $mi,
    //             'mo'               => $mo,
    //             'adj'              => $adj,
    //             'total'            => $total,
    //             'ending_stock'     => $ending,
    //             'used'             => $used,
    //             'wP'               => $wP,
    //             'wB'               => $wB,
    //             'wT'               => $wT,
    //             'actualTepung'     => $actualTepung,
    //             'shift1'           => $s1['used'],
    //             'shift2'           => $s2['used'],
    //             'uang'             => $uang,
    //             'ket'              => implode(' | ', $ketParts),
    //             'open_stock_right' => $closingToday,
    //         ];
    //     }

    //     // =========================
    //     // 5) DETAIL SHIFT
    //     // PERSIS IKUT POLA dscLoad
    //     // =========================
    //     $shiftRows = [];
    //     $no2 = 1;

    //     $shifts = match ($shiftFilter) {
    //         '1' => [1],
    //         '2' => [2],
    //         default => [1, 2],
    //     };

    //     foreach ($bahanList as $b) {
    //         $bahanId = (int) $b->id;

    //         $r1 = $draftShift1Rows[$bahanId] ?? $finalShift1Rows[$bahanId] ?? null;
    //         $r2 = $draftShift2Rows[$bahanId] ?? $finalShift2Rows[$bahanId] ?? null;

    //         foreach ($shifts as $sh) {
    //             if ((int) $sh === 1) {
    //                 $sr = $r1;
    //                 $open = (float) $this->getOpeningStockUi($displayOutletId, $bahanId, $today, 1);
    //             } else {
    //                 $sr = $r2;

    //                 if ($r1 && $r1->ending_stock !== null) {
    //                     $open = (float) $r1->ending_stock;
    //                 } else {
    //                     $open = (float) $this->getOpeningStockUi($displayOutletId, $bahanId, $today, 2);
    //                 }
    //             }

    //             $v = $calcShiftValues($open, $sr);

    //             $shiftRows[] = [
    //                 'no'           => $no2++,
    //                 'nama'         => (string) $b->nama_bahan,
    //                 'sat'          => (string) $b->satuan,
    //                 'shift'        => (int) $sh,
    //                 'bahan_id'     => $bahanId,
    //                 'source'       => $v['source'],
    //                 'open'         => $v['open'],
    //                 'pin'          => $v['pin'],
    //                 'mi'           => $v['mi'],
    //                 'mo'           => $v['mo'],
    //                 'adj'          => $v['adj'],
    //                 'total'        => $v['total'],
    //                 'ending_stock' => $v['ending_stock'],
    //                 'used'         => $v['used'],
    //                 'wP'           => $v['wP'],
    //                 'wB'           => $v['wB'],
    //                 'wT'           => $v['wT'],
    //                 'ket'          => $v['ket'],
    //             ];
    //         }
    //     }

    //     return view('Investor.Inventory.dailyStockControl', [
    //         'today'               => $today,
    //         'outletId'            => (int) $outletIdRaw,
    //         'shiftFilter'         => $shiftFilter,
    //         'outlets'             => $outlets,
    //         'selectedOutlet'      => $selectedOutlet,
    //         'selectedOutletIds'   => $selectedOutletIds,
    //         'selectedOutletLabel' => $selectedOutletLabel,

    //         'sales_shift_1'       => $salesShift1,
    //         'sales_shift_2'       => $salesShift2,

    //         'rekapRows'           => $rekapRows,
    //         'shiftRows'           => $shiftRows,

    //         'omsetActive'         => $omsetActive,

    //         'startDate'           => $startDateRaw,
    //         'endDate'             => $endDateRaw,
    //         'missingOutlets'      => $missingOutlets,
    //         'isSuperadmin'        => $isSuperadmin,
    //     ]);
    // }

    public function dailyStockControl(Request $request)
    {
        return view(
            'Investor.Inventory.dailyStockControl',
            $this->buildDailyStockControlData($request)
        );
    }

    private function buildDailyStockControlData(Request $request): array
    {
        $todayRaw     = (string) $request->get('tanggal', date('Y-m-d'));
        $outletId     = $request->get('outlet_id', 'all');
        $shiftFilter  = (string) $request->get('shift_filter', 'all');
        $today        = $this->normalizeDateToYmd($todayRaw);

        $startDateRaw = (string) $request->get('start_date', '');
        $endDateRaw   = (string) $request->get('end_date', '');

        $startDate = $startDateRaw ? $this->normalizeDateToYmd($startDateRaw) : null;
        $endDate   = $endDateRaw ? $this->normalizeDateToYmd($endDateRaw) : null;

        if ($startDate && $endDate && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $shiftFilter = in_array($shiftFilter, ['all', '1', '2'], true)
            ? $shiftFilter
            : 'all';

        $normalizeOutletName = function ($name) {
            $name = strtoupper(trim((string) $name));
            return preg_replace('/\s+/', ' ', $name);
        };

        $allOutletsRaw = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet', 'status')
            ->orderBy('nama_outlet')
            ->orderBy('id')
            ->get();

        $outletGroups = [];

        foreach ($allOutletsRaw->groupBy(fn ($o) => $normalizeOutletName($o->nama_outlet)) as $name => $rows) {
            $ids = $rows->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $groupKey = 'group_' . md5($name);

            $outletGroups[$groupKey] = [
                'label' => $name . ' [ID: ' . implode(',', $ids) . ']',
                'nama_outlet' => $name,
                'ids' => $ids,
            ];
        }

        $outlets = collect($outletGroups)->map(function ($group, $key) {
            return (object) [
                'id' => $key,
                'nama_outlet' => $group['label'],
                'merged_ids' => $group['ids'],
                'is_merged' => count($group['ids']) > 1,
            ];
        })->values();

        $selectedOutlet = null;
        $selectedOutletIds = [];
        $selectedOutletLabel = null;

        if ($outletId === 'all') {
            $selectedOutletIds = $allOutletsRaw->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $selectedOutletLabel = 'ALL OUTLET';
        } elseif (isset($outletGroups[$outletId])) {
            $selectedOutletIds = $outletGroups[$outletId]['ids'];
            $selectedOutletLabel = $outletGroups[$outletId]['label'];

            $selectedOutlet = (object) [
                'id' => $outletId,
                'nama_outlet' => $outletGroups[$outletId]['label'],
                'merged_ids' => $selectedOutletIds,
                'is_merged' => count($selectedOutletIds) > 1,
            ];
        } else {
            $singleOutlet = $allOutletsRaw->firstWhere('id', (int) $outletId);

            if ($singleOutlet) {
                $nameNorm = $normalizeOutletName($singleOutlet->nama_outlet);

                $sameOutletRows = $allOutletsRaw->filter(function ($o) use ($normalizeOutletName, $nameNorm) {
                    return $normalizeOutletName($o->nama_outlet) === $nameNorm;
                });

                $selectedOutletIds = $sameOutletRows->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                $selectedOutletLabel = $nameNorm . ' [ID: ' . implode(',', $selectedOutletIds) . ']';

                $selectedOutlet = (object) [
                    'id' => (int) $outletId,
                    'nama_outlet' => $selectedOutletLabel,
                    'status' => $singleOutlet->status ?? null,
                    'merged_ids' => $selectedOutletIds,
                    'is_merged' => count($selectedOutletIds) > 1,
                ];
            }
        }

        $isAllOutlet = $outletId === 'all';
        $isSuperadmin = auth()->user()?->role === 'superadmin';

        /*
         * Notifikasi lonceng outlet belum isi DSC:
         * default H-1 dari tanggal filter. Contoh tanggal aktif 2026-05-29,
         * yang dicek otomatis 2026-05-28. Ini dibuat ringan karena tidak lagi
         * membuka tab besar, hanya data ringkas untuk dropdown/modal lonceng.
         */
        $missingCheckStartDate = \Carbon\Carbon::parse($today)->subDay()->format('Y-m-d');
        $missingCheckEndDate   = $missingCheckStartDate;
        $missingOutlets = collect();

        if ($isSuperadmin) {
            /*
             * OPTIMIZE: versi lama memakai correlated subquery + DATE(s.tanggal)
             * untuk setiap outlet. Saat outlet banyak, ini ikut menambah beban halaman DSC.
             * Versi ini ambil data stock H-1 dan last input dalam 2 query ringan,
             * lalu grouping outlet dilakukan di Collection yang sudah ada di memori.
             */
            $submittedOutletIds = DB::table('tbl_stock')
                ->where('tanggal', $missingCheckStartDate)
                ->distinct()
                ->pluck('outlet_id')
                ->map(fn ($id) => (int) $id)
                ->flip();

            $lastInputRows = DB::table('tbl_stock')
                ->select('outlet_id', DB::raw('MAX(tanggal) as last_input_date'))
                ->groupBy('outlet_id')
                ->get()
                ->keyBy(fn ($row) => (int) $row->outlet_id);

            $missingOutlets = collect($outletGroups)
                ->map(function ($group) use ($submittedOutletIds, $lastInputRows) {
                    $ids = array_values(array_map('intval', $group['ids'] ?? []));
                    $hasInput = false;
                    $lastInputDate = null;

                    foreach ($ids as $id) {
                        if (isset($submittedOutletIds[$id])) {
                            $hasInput = true;
                        }

                        $row = $lastInputRows->get($id);
                        if ($row && $row->last_input_date) {
                            $date = $this->normalizeDateToYmd((string) $row->last_input_date);
                            if ($lastInputDate === null || $date > $lastInputDate) {
                                $lastInputDate = $date;
                            }
                        }
                    }

                    if ($hasInput) {
                        return null;
                    }

                    return (object) [
                        'id' => (int) ($ids[0] ?? 0),
                        'nama_outlet' => (string) ($group['nama_outlet'] ?? ''),
                        'merged_ids' => implode(',', $ids),
                        'last_input_date' => $lastInputDate,
                    ];
                })
                ->filter()
                ->sortBy('nama_outlet')
                ->values();
        }

        if (empty($selectedOutletIds)) {
            return [
                'today'               => $today,
                'outletId'            => $outletId,
                'shiftFilter'         => $shiftFilter,
                'outlets'             => $outlets,
                'outletGroups'        => $outletGroups,
                'selectedOutlet'      => $selectedOutlet,
                'selectedOutletIds'   => [],
                'selectedOutletLabel' => $selectedOutletLabel,

                'sales_shift_1'       => 0,
                'sales_shift_2'       => 0,

                'rekapRows'           => [],
                'shiftRows'           => [],

                'omsetActive'         => $this->defaultOmsetActive($today),

                'startDate'           => $startDateRaw,
                'endDate'             => $endDateRaw,
                'missingOutlets'      => $missingOutlets,
                'missingCheckStartDate' => $missingCheckStartDate ?? null,
                'missingCheckEndDate'   => $missingCheckEndDate ?? null,
                'isSuperadmin'        => $isSuperadmin,
            ];
        }

        $displayOutletId = (int) $selectedOutletIds[0];

        $omsetRows = DB::table('tbl_dsc_omset_setoran')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereDate('tanggal', $today)
            ->get();

        $omsetRow = (object) [
            'tanggal' => $today,
            'pic' => $omsetRows->pluck('pic')->filter()->first(),

            'akumulasi_selisih' => (float) $omsetRows->sum('akumulasi_selisih'),
            'kekurangan_bulan_lalu' => (float) $omsetRows->sum('kekurangan_bulan_lalu'),

            's1_total_transaction' => (float) $omsetRows->sum('s1_total_transaction'),
            's1_diskon' => (float) $omsetRows->sum('s1_diskon'),
            's1_non_tunai' => (float) $omsetRows->sum('s1_non_tunai'),
            's1_expense' => (float) $omsetRows->sum('s1_expense'),
            's1_uang_fisik' => (float) $omsetRows->sum('s1_uang_fisik'),
            's1_admin_pot_sales' => (float) $omsetRows->sum('s1_admin_pot_sales'),
            's1_adjustment' => (float) $omsetRows->sum('s1_adjustment'),
            's1_hanya_selisih_minus' => (float) $omsetRows->sum('s1_hanya_selisih_minus'),
            's1_tanggal_setor' => $omsetRows->pluck('s1_tanggal_setor')->filter()->first(),
            's1_sudah_disetor' => (float) $omsetRows->sum('s1_sudah_disetor'),
            'bukti_foto_s1' => $omsetRows->pluck('bukti_foto_s1')->filter()->first(),

            's2_total_transaction' => (float) $omsetRows->sum('s2_total_transaction'),
            's2_diskon' => (float) $omsetRows->sum('s2_diskon'),
            's2_non_tunai' => (float) $omsetRows->sum('s2_non_tunai'),
            's2_expense' => (float) $omsetRows->sum('s2_expense'),
            's2_uang_fisik' => (float) $omsetRows->sum('s2_uang_fisik'),
            's2_admin_pot_sales' => (float) $omsetRows->sum('s2_admin_pot_sales'),
            's2_adjustment' => (float) $omsetRows->sum('s2_adjustment'),
            's2_hanya_selisih_minus' => (float) $omsetRows->sum('s2_hanya_selisih_minus'),
            's2_tanggal_setor' => $omsetRows->pluck('s2_tanggal_setor')->filter()->first(),
            's2_sudah_disetor' => (float) $omsetRows->sum('s2_sudah_disetor'),
            'bukti_foto_s2' => $omsetRows->pluck('bukti_foto_s2')->filter()->first(),
        ];

        $mode = $omsetRows->isNotEmpty() ? 'EXACT' : 'NO_DATA';

        $omsetLastDate = DB::table('tbl_dsc_omset_setoran')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->max('tanggal');

        $omsetActive = $this->buildOmsetActive(
            $omsetRows->isNotEmpty() ? $omsetRow : null,
            $shiftFilter,
            $today,
            $omsetLastDate,
            $mode
        );

        $salesShift1 = (float) ($omsetRow->s1_total_transaction ?? 0);
        $salesShift2 = (float) ($omsetRow->s2_total_transaction ?? 0);

        $bahanList = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        $bahanIds = $bahanList->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        // PERFORMANCE FIX BESAR:
        // Opening stock sebelumnya dihitung lewat getOpeningFromAliasIds() di dalam loop bahan.
        // Jika outlet = ALL, query itu membawa ribuan outlet_id dan dipanggil berkali-kali.
        // Sekarang semua opening untuk semua bahan diambil bulk sekali per request.
        $openingBulkMap = $this->getOpeningBulkFromAliasIds($selectedOutletIds, $displayOutletId, $bahanIds, $today);

        $getOpeningMerged = function (int $bahanId, string $tanggal, int $shift) use ($openingBulkMap, $selectedOutletIds, $displayOutletId) {
            // Untuk tanggal aktif yang sedang dibuka, pakai hasil bulk tanpa query tambahan.
            if (isset($openingBulkMap[$bahanId][$shift])) {
                return (float) $openingBulkMap[$bahanId][$shift];
            }

            // Fallback aman untuk tanggal berbeda, seharusnya jarang/tidak terpanggil dari halaman DSC ini.
            return (float) $this->getOpeningFromAliasIds($selectedOutletIds, $displayOutletId, $bahanId, $tanggal, $shift);
        };

        $draftShift1Rows = DB::table('tbl_stock_draft')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereDate('tanggal', $today)
            ->where('shift', 1)
            ->where('is_draft', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('bahan_id');

        $finalShift1Rows = DB::table('tbl_stock')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereDate('tanggal', $today)
            ->where('shift', '1')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('bahan_id');

        $draftShift2Rows = DB::table('tbl_stock_draft')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereDate('tanggal', $today)
            ->where('shift', 2)
            ->where('is_draft', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('bahan_id');

        $finalShift2Rows = DB::table('tbl_stock')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereDate('tanggal', $today)
            ->where('shift', '2')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('bahan_id');

        $mergeRows = function ($rows, string $source) {
            if (!$rows || $rows->isEmpty()) {
                return null;
            }

            $first = $rows->sortByDesc('updated_at')->sortByDesc('id')->first();

            $row = clone $first;
            $row->row_source = $source;

            $row->purchase_in = (float) $rows->sum('purchase_in');
            $row->mutasi_in = (float) $rows->sum('mutasi_in');
            $row->mutasi_out = (float) $rows->sum('mutasi_out');
            $row->adjustment_qty = (float) $rows->sum('adjustment_qty');
            // ACTUAL USED harus ikut dijumlah saat outlet tergabung / alias outlet.
            // Jika tidak, Usage DSC bisa ambil used dari satu row terbaru saja atau hitung ulang dari open yang tidak sepadan.
            $row->used_qty = (float) $rows->sum('used_qty');

            $latestEnding = $rows
                ->filter(fn ($r) => $r->ending_stock !== null)
                ->sortByDesc('updated_at')
                ->sortByDesc('id')
                ->first();

            $row->ending_stock = $latestEnding ? (float) $latestEnding->ending_stock : 0;

            $row->waste_product = (float) $rows->sum('waste_product');
            $row->waste_bahan = (float) $rows->sum('waste_bahan');
            $row->waste_tepung = (float) $rows->sum('waste_tepung');
            $row->uang_plus = (float) $rows->sum('uang_plus');

            $kets = $rows->pluck('keterangan')->filter()->values()->all();
            $row->keterangan = implode(' | ', $kets);

            return $row;
        };

        // OPTIMIZE: merge draft/final per bahan cukup sekali.
        // Sebelumnya mergeRows() dipanggil ulang di loop rekap dan loop detail shift.
        $mergedShift1Rows = [];
        $mergedShift2Rows = [];

        foreach ($bahanIds as $bahanId) {
            $mergedShift1Rows[$bahanId] = isset($draftShift1Rows[$bahanId])
                ? $mergeRows($draftShift1Rows[$bahanId], 'draft')
                : (isset($finalShift1Rows[$bahanId]) ? $mergeRows($finalShift1Rows[$bahanId], 'final') : null);

            $mergedShift2Rows[$bahanId] = isset($draftShift2Rows[$bahanId])
                ? $mergeRows($draftShift2Rows[$bahanId], 'draft')
                : (isset($finalShift2Rows[$bahanId]) ? $mergeRows($finalShift2Rows[$bahanId], 'final') : null);
        }

        $calcShiftValues = function (float $open, $row): array {
            $pin    = $row ? (float) ($row->purchase_in ?? 0) : 0.0;
            $mi     = $row ? (float) ($row->mutasi_in ?? 0) : 0.0;
            $mo     = $row ? (float) ($row->mutasi_out ?? 0) : 0.0;
            $adj    = $row ? (float) ($row->adjustment_qty ?? 0) : 0.0;

            // ENDING BASE adalah ending fisik sebelum adjustment.
            $endingBase = $row && $row->ending_stock !== null ? (float) $row->ending_stock : 0.0;

            // TOTAL tidak memasukkan ADJ.
            // TOTAL = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
            $total = $open + $pin + $mi - $mo;

            // ADJ dipakai untuk koreksi ending.
            // ENDING = ENDING BASE + ADJ
            $ending = $endingBase + $adj;

            // USED = TOTAL - ENDING
            $used = $total - $ending;

            $wP = $row ? (float) ($row->waste_product ?? 0) : 0.0;
            $wB = $row ? (float) ($row->waste_bahan ?? 0) : 0.0;

            $wTDb = $row ? (float) ($row->waste_tepung ?? 0) : 0.0;
            // WASTE T di Rekap = Waste P + Waste B + Waste T input.
            // Jangan pakai fallback/override, karena nilai negatif/desimal harus tetap ikut terakumulasi.
            $wT = $wP + $wB + $wTDb;

            return [
                'open'         => $open,
                'pin'          => $pin,
                'mi'           => $mi,
                'mo'           => $mo,
                'adj'          => $adj,
                'total'        => $total,
                'ending_stock' => $ending,
                'used'         => $used,
                'wP'           => $wP,
                'wB'           => $wB,
                'wT'           => $wT,
                'wT_input'     => $wTDb,
                'uang'         => $row ? (float) ($row->uang_plus ?? 0) : 0.0,
                'ket'          => $row ? (string) ($row->keterangan ?? '') : '',
                'source'       => $row->row_source ?? null,
            ];
        };

        $rekapRows = [];
        $no = 1;

        foreach ($bahanList as $b) {
            $bahanId = (int) $b->id;

            $r1 = $mergedShift1Rows[$bahanId] ?? null;
            $r2 = $mergedShift2Rows[$bahanId] ?? null;

            $openS1 = (float) $getOpeningMerged($bahanId, $today, 1);

            if ($r1 && $r1->ending_stock !== null) {
                $openS2 = (float) $r1->ending_stock;
            } else {
                $openS2 = (float) $getOpeningMerged($bahanId, $today, 2);
            }

            $s1 = $calcShiftValues($openS1, $r1);
            $s2 = $calcShiftValues($openS2, $r2);

            $sourceS1 = $s1['source'] ?? null;
            $sourceS2 = $s2['source'] ?? null;

            if ($shiftFilter === '1') {
                $openRekap = $s1['open'];
                $pin       = $s1['pin'];
                $mi        = $s1['mi'];
                $mo        = $s1['mo'];
                $adj       = $s1['adj'];
                $total     = $s1['total'];
                $ending    = $s1['ending_stock'];
                $used      = $s1['used'];
                $wP        = $s1['wP'];
                $wB        = $s1['wB'];
                $wT        = $s1['wT'];
                $uang      = $s1['uang'];

                $ketParts  = !empty($s1['ket']) ? ['S1: ' . $s1['ket']] : [];
                $rowSource = $sourceS1;

            } elseif ($shiftFilter === '2') {
                $openRekap = $s2['open'];
                $pin       = $s2['pin'];
                $mi        = $s2['mi'];
                $mo        = $s2['mo'];
                $adj       = $s2['adj'];
                $total     = $s2['total'];
                $ending    = $s2['ending_stock'];
                $used      = $s2['used'];
                $wP        = $s2['wP'];
                $wB        = $s2['wB'];
                $wT        = $s2['wT'];
                $uang      = $s2['uang'];

                $ketParts  = !empty($s2['ket']) ? ['S2: ' . $s2['ket']] : [];
                $rowSource = $sourceS2;

            } else {
                // SHIFT SEMUA
                $openRekap = $openS1;

                $pin = $s1['pin'] + $s2['pin'];
                $mi  = $s1['mi'] + $s2['mi'];
                $mo  = $s1['mo'] + $s2['mo'];
                $adj = $s1['adj'] + $s2['adj'];

                /*
                * Ending mode SEMUA:
                * prioritas ending S2 kalau > 0,
                * kalau S2 kosong/0 fallback ke S1.
                */
                // Pakai ending hasil kalkulasi shift, karena sudah termasuk koreksi ADJ.
                $endingS1 = ($r1 && $r1->ending_stock !== null) ? (float) $s1['ending_stock'] : null;
                $endingS2 = ($r2 && $r2->ending_stock !== null) ? (float) $s2['ending_stock'] : null;

                if ($endingS2 !== null && $endingS2 > 0) {
                    $ending = $endingS2;
                } elseif ($endingS1 !== null && $endingS1 > 0) {
                    $ending = $endingS1;
                } elseif ($endingS2 !== null) {
                    $ending = $endingS2;
                } elseif ($endingS1 !== null) {
                    $ending = $endingS1;
                } else {
                    $ending = 0.0;
                }

                /*
                * Total mode SEMUA:
                * stok tersedia sebelum pemakaian.
                *
                * Rumus:
                * TOTAL = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
                *
                * ADJ tidak masuk ke TOTAL.
                * ADJ sudah menjadi koreksi ENDING pada perhitungan shift.
                */
                $total = $openRekap + $pin + $mi - $mo;

                /*
                * Used mode SEMUA:
                * USED = TOTAL - ENDING
                */
                $used = $total - $ending;

                $wP   = $s1['wP'] + $s2['wP'];
                $wB   = $s1['wB'] + $s2['wB'];
                $wT   = $s1['wT'] + $s2['wT'];
                $uang = $s1['uang'] + $s2['uang'];

                $ketParts = [];
                if (!empty($s1['ket'])) {
                    $ketParts[] = 'S1: ' . $s1['ket'];
                }
                if (!empty($s2['ket'])) {
                    $ketParts[] = 'S2: ' . $s2['ket'];
                }

                $rowSource = ($sourceS1 === 'draft' || $sourceS2 === 'draft')
                    ? 'draft'
                    : (($sourceS1 === 'final' || $sourceS2 === 'final') ? 'final' : null);
            }

            $namaNorm = strtolower(trim((string) $b->nama_bahan));
            $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wT) : 0.0;

            /*
            * Open next / closing today:
            * sama seperti ending SEMUA, jangan langsung ambil S2 kalau nilainya 0.
            */
            // Open next mengikuti ending yang sudah dikoreksi ADJ.
            $closingS1 = ($r1 && $r1->ending_stock !== null) ? (float) $s1['ending_stock'] : null;
            $closingS2 = ($r2 && $r2->ending_stock !== null) ? (float) $s2['ending_stock'] : null;

            if ($closingS2 !== null && $closingS2 > 0) {
                $closingToday = $closingS2;
            } elseif ($closingS1 !== null && $closingS1 > 0) {
                $closingToday = $closingS1;
            } elseif ($closingS2 !== null) {
                $closingToday = $closingS2;
            } elseif ($closingS1 !== null) {
                $closingToday = $closingS1;
            } else {
                $closingToday = 0.0;
            }

            $rekapRows[] = [
                'no'               => $no++,
                'nama'             => (string) $b->nama_bahan,
                'sat'              => (string) $b->satuan,

                'source'           => $rowSource,
                'source_s1'        => $sourceS1,
                'source_s2'        => $sourceS2,

                'open'             => $openRekap,
                'pin'              => $pin,
                'mi'               => $mi,
                'mo'               => $mo,
                'adj'              => $adj,
                'total'            => $total,
                'ending_stock'     => $ending,
                'used'             => $used,

                'wP'               => $wP,
                'wB'               => $wB,
                'wT'               => $wT,
                'actualTepung'     => $actualTepung,

                'shift1'           => $s1['used'],
                'shift2'           => $s2['used'],

                'uang'             => $uang,
                'ket'              => implode(' | ', $ketParts),
                'open_stock_right' => $closingToday,
            ];
        }
        
        $shiftRows = [];
        $no2 = 1;

        $shifts = match ($shiftFilter) {
            '1' => [1],
            '2' => [2],
            default => [1, 2],
        };

        foreach ($bahanList as $b) {
            $bahanId = (int) $b->id;

            $r1 = $mergedShift1Rows[$bahanId] ?? null;
            $r2 = $mergedShift2Rows[$bahanId] ?? null;

            foreach ($shifts as $sh) {
                if ((int) $sh === 1) {
                    $sr = $r1;
                    $open = (float) $getOpeningMerged($bahanId, $today, 1);
                } else {
                    $sr = $r2;

                    if ($r1 && $r1->ending_stock !== null) {
                        $open = (float) $r1->ending_stock;
                    } else {
                        $open = (float) $getOpeningMerged($bahanId, $today, 2);
                    }
                }

                $v = $calcShiftValues($open, $sr);

                $shiftRows[] = [
                    'no'           => $no2++,
                    'nama'         => (string) $b->nama_bahan,
                    'sat'          => (string) $b->satuan,
                    'shift'        => (int) $sh,
                    'bahan_id'     => $bahanId,
                    'source'       => $v['source'],
                    'open'         => $v['open'],
                    'pin'          => $v['pin'],
                    'mi'           => $v['mi'],
                    'mo'           => $v['mo'],
                    'adj'          => $v['adj'],
                    'total'        => $v['total'],
                    'ending_stock' => $v['ending_stock'],
                    'used'         => $v['used'],
                    'wP'           => $v['wP'],
                    'wB'           => $v['wB'],
                    'wT'           => $v['wT'],
                    'wT_input'     => $v['wT_input'] ?? 0,
                    'ket'          => $v['ket'],
                ];
            }
        }

        return [
            'today'               => $today,
            'outletId'            => $outletId,
            'shiftFilter'         => $shiftFilter,
            'outlets'             => $outlets,
            'outletGroups'        => $outletGroups,
            'selectedOutlet'      => $selectedOutlet,
            'selectedOutletIds'   => $selectedOutletIds,
            'selectedOutletLabel' => $selectedOutletLabel,

            'sales_shift_1'       => $salesShift1,
            'sales_shift_2'       => $salesShift2,

            'rekapRows'           => $rekapRows,
            'shiftRows'           => $shiftRows,

            'omsetActive'         => $omsetActive,

            'startDate'           => $startDateRaw,
            'endDate'             => $endDateRaw,
            'missingOutlets'      => $missingOutlets,
            'missingCheckStartDate' => $missingCheckStartDate ?? null,
            'missingCheckEndDate'   => $missingCheckEndDate ?? null,
            'isSuperadmin'        => $isSuperadmin,
        ];
    }

    private function getOpeningBulkFromAliasIds(array $aliasIds, int $displayOutletId, array $bahanIds, string $tanggal): array
    {
        $aliasIds = array_values(array_unique(array_map('intval', $aliasIds)));
        $aliasIds = array_values(array_filter($aliasIds, fn ($id) => $id > 0));

        if (empty($aliasIds) && $displayOutletId > 0) {
            $aliasIds = [(int) $displayOutletId];
        }

        $bahanIds = array_values(array_unique(array_map('intval', $bahanIds)));
        $bahanIds = array_values(array_filter($bahanIds, fn ($id) => $id > 0));

        $result = [];
        foreach ($bahanIds as $bahanId) {
            $result[$bahanId] = [1 => 0.0, 2 => 0.0];
        }

        if (empty($aliasIds) || empty($bahanIds)) {
            return $result;
        }

        $pickLatestByBahan = function ($rows) {
            return $rows
                ->sort(function ($a, $b) {
                    $priorityCmp = ((int) ($b->source_priority ?? 0)) <=> ((int) ($a->source_priority ?? 0));
                    if ($priorityCmp !== 0) {
                        return $priorityCmp;
                    }

                    $timeCmp = strcmp((string) ($b->updated_at ?? ''), (string) ($a->updated_at ?? ''));
                    if ($timeCmp !== 0) {
                        return $timeCmp;
                    }

                    return ((int) ($b->id ?? 0)) <=> ((int) ($a->id ?? 0));
                })
                ->groupBy('bahan_id');
        };

        // 1) Opening shift 2 = ending shift 1 di tanggal yang sama.
        // Final tetap menang dari draft untuk tanggal+shift yang sama.
        $todayFinalRows = DB::table('tbl_stock')
            ->select('bahan_id', 'ending_stock', 'updated_at', 'id', DB::raw('1 as source_priority'))
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $bahanIds)
            ->where('tanggal', $tanggal)
            ->where('shift', 1)
            ->whereNotNull('ending_stock')
            ->get();

        $todayDraftRows = DB::table('tbl_stock_draft')
            ->select('bahan_id', 'ending_stock', 'updated_at', 'id', DB::raw('0 as source_priority'))
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $bahanIds)
            ->where('tanggal', $tanggal)
            ->where('shift', 1)
            ->where('is_draft', 1)
            ->whereNotNull('ending_stock')
            ->get();

        $sameDayShift1 = $pickLatestByBahan($todayFinalRows->concat($todayDraftRows));

        foreach ($sameDayShift1 as $bahanId => $rows) {
            $first = $rows->first();
            if ($first && $first->ending_stock !== null) {
                $result[(int) $bahanId][2] = (float) $first->ending_stock;
            }
        }

        /*
         * 2) Cari closing terakhir sebelum tanggal aktif.
         *
         * Versi lama mengambil SEMUA histori sebelum tanggal aktif lalu sorting di PHP:
         *   ->orderBy(...)->get()
         * Ini yang muncul di slowlog line getOpeningBulkFromAliasIds().
         *
         * Versi ini hanya mengambil 1 key terakhir per bahan dari DB:
         *   MAX(CONCAT(tanggal,'|',LPAD(shift,2,'0')))
         * sehingga jumlah row yang kembali maksimal sebanyak jumlah bahan.
         */
        $lastFinal = DB::table('tbl_stock')
            ->select(
                'bahan_id',
                DB::raw("MAX(CONCAT(tanggal, '|', LPAD(shift, 2, '0'))) as last_key")
            )
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $bahanIds)
            ->where('tanggal', '<', $tanggal)
            ->whereNotNull('ending_stock')
            ->groupBy('bahan_id');

        $lastDraft = DB::table('tbl_stock_draft')
            ->select(
                'bahan_id',
                DB::raw("MAX(CONCAT(tanggal, '|', LPAD(shift, 2, '0'))) as last_key")
            )
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $bahanIds)
            ->where('tanggal', '<', $tanggal)
            ->where('is_draft', 1)
            ->whereNotNull('ending_stock')
            ->groupBy('bahan_id');

        $lastRows = DB::query()
            ->fromSub($lastFinal->unionAll($lastDraft), 'x')
            ->select('bahan_id', DB::raw('MAX(last_key) as last_key'))
            ->groupBy('bahan_id')
            ->get();

        $lastByBahan = [];
        foreach ($lastRows as $row) {
            $bahanId = (int) $row->bahan_id;
            $lastKey = (string) ($row->last_key ?? '');

            if ($lastKey === '' || ! str_contains($lastKey, '|')) {
                continue;
            }

            [$lastDate, $lastShift] = explode('|', $lastKey, 2);

            $lastByBahan[$bahanId] = [
                'tanggal' => $this->normalizeDateToYmd((string) $lastDate),
                'shift' => (int) $lastShift,
            ];
        }

        if (empty($lastByBahan)) {
            return $result;
        }

        $lastDates = array_values(array_unique(array_map(fn ($row) => $row['tanggal'], $lastByBahan)));
        $lastBahanIds = array_keys($lastByBahan);

        // 3) Ambil row closing untuk kombinasi bahan+tanggal+shift yang sudah ditemukan.
        $closingFinalRows = DB::table('tbl_stock')
            ->select('bahan_id', 'tanggal', 'shift', 'ending_stock', 'updated_at', 'id', DB::raw('1 as source_priority'))
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $lastBahanIds)
            ->whereIn('tanggal', $lastDates)
            ->whereNotNull('ending_stock')
            ->get();

        $closingDraftRows = DB::table('tbl_stock_draft')
            ->select('bahan_id', 'tanggal', 'shift', 'ending_stock', 'updated_at', 'id', DB::raw('0 as source_priority'))
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $lastBahanIds)
            ->whereIn('tanggal', $lastDates)
            ->where('is_draft', 1)
            ->whereNotNull('ending_stock')
            ->get();

        $closingGroups = $pickLatestByBahan(
            $closingFinalRows
                ->concat($closingDraftRows)
                ->filter(function ($row) use ($lastByBahan) {
                    $bahanId = (int) $row->bahan_id;
                    if (! isset($lastByBahan[$bahanId])) {
                        return false;
                    }

                    return $this->normalizeDateToYmd((string) $row->tanggal) === $lastByBahan[$bahanId]['tanggal']
                        && (int) $row->shift === (int) $lastByBahan[$bahanId]['shift'];
                })
        );

        foreach ($closingGroups as $bahanId => $rows) {
            $first = $rows->first();
            if ($first && $first->ending_stock !== null) {
                $closing = (float) $first->ending_stock;
                $result[(int) $bahanId][1] = $closing;

                // Shift 2 fallback sama dengan opening shift 1 kalau belum ada ending shift 1 hari ini.
                if (! isset($sameDayShift1[$bahanId])) {
                    $result[(int) $bahanId][2] = $closing;
                }
            }
        }

        return $result;
    }

    private function getPrevClosing(int $outletId, int $bahanId, string $todayYmd): float
    {
        // Ambil ending Shift 2 terakhir sebelum tanggal aktif.
        // Draft diprioritaskan supaya ending Shift 2 yang belum final tetap menjadi opening besok.
        $draft = DB::table('tbl_stock_draft')
            ->where('outlet_id', $outletId)
            ->where('bahan_id', $bahanId)
            ->whereDate('tanggal', '<', $todayYmd)
            ->where('shift', 2)
            ->where('is_draft', 1)
            ->orderByDesc('tanggal')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($draft && $draft->ending_stock !== null) {
            return (float) $draft->ending_stock;
        }

        $final = DB::table('tbl_stock')
            ->where('outlet_id', $outletId)
            ->where('bahan_id', $bahanId)
            ->whereDate('tanggal', '<', $todayYmd)
            ->where('shift', 2)
            ->orderByDesc('tanggal')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        return (float) ($final->ending_stock ?? 0);
    }

    private function defaultOmsetActive(string $today): array
    {
        return [
            'exists' => false,
    
            'pic' => '',
            'akumulasi_selisih' => 0,
            'kekurangan_bulan_lalu' => 0,
    
            's1' => [
                'total_transaction' => 0,
                'diskon' => 0,
                'non_tunai' => 0,
                'expense' => 0,
                'uang_fisik' => 0,
                'admin_pot_sales' => 0,
                'adjustment' => 0,
                'selisih_minus' => 0,
                'tanggal_setor' => null,
                'sudah_disetor' => 0,
                'foto_url' => null,
            ],
    
            's2' => [
                'total_transaction' => 0,
                'diskon' => 0,
                'non_tunai' => 0,
                'expense' => 0,
                'uang_fisik' => 0,
                'admin_pot_sales' => 0,
                'adjustment' => 0,
                'selisih_minus' => 0,
                'tanggal_setor' => null,
                'sudah_disetor' => 0,
                'foto_url' => null,
            ],
    
            'total' => [
                'total_transaction' => 0,
                'diskon' => 0,
                'non_tunai' => 0,
                'expense' => 0,
                'uang_fisik' => 0,
                'admin_pot_sales' => 0,
                'adjustment' => 0,
                'selisih_minus' => 0,
            ],
    
            'meta' => [
                'filter_tanggal' => $today,
                'actual_tanggal' => null,
                'last_available_tanggal' => null,
                'mode' => 'NO_DATA',
            ],
        ];
    }

    private function buildOmsetActive($omsetRow, string $shiftFilter, string $today, $omsetLastDate, string $mode): array
    {
        $base = $this->defaultOmsetActive($today);
        $base['meta']['last_available_tanggal'] = $omsetLastDate;
    
        if (! $omsetRow) {
            $base['meta']['mode'] = $mode;
            return $base;
        }
    
        $fotoUrlS1 = $this->publicOmsetPhotoUrl($omsetRow->bukti_foto_s1 ?? null);
        $fotoUrlS2 = $this->publicOmsetPhotoUrl($omsetRow->bukti_foto_s2 ?? null);
    
        $isPaid = function ($v): int {
            return ((float) ($v ?? 0)) > 0 ? 1 : 0;
        };
    
        $s1 = [
            'total_transaction' => (float) ($omsetRow->s1_total_transaction ?? 0),
            'diskon' => (float) ($omsetRow->s1_diskon ?? 0),
            'non_tunai' => (float) ($omsetRow->s1_non_tunai ?? 0),
            'expense' => (float) ($omsetRow->s1_expense ?? 0),
            'uang_fisik' => (float) ($omsetRow->s1_uang_fisik ?? 0),
            'admin_pot_sales' => (float) ($omsetRow->s1_admin_pot_sales ?? 0),
            'adjustment' => (float) ($omsetRow->s1_adjustment ?? 0),
            'selisih_minus' => (float) ($omsetRow->s1_hanya_selisih_minus ?? 0),
            'tanggal_setor' => $omsetRow->s1_tanggal_setor ?? null,
            'sudah_disetor' => $isPaid($omsetRow->s1_sudah_disetor ?? 0),
            'foto_url' => $fotoUrlS1,
        ];
    
        $s2 = [
            'total_transaction' => (float) ($omsetRow->s2_total_transaction ?? 0),
            'diskon' => (float) ($omsetRow->s2_diskon ?? 0),
            'non_tunai' => (float) ($omsetRow->s2_non_tunai ?? 0),
            'expense' => (float) ($omsetRow->s2_expense ?? 0),
            'uang_fisik' => (float) ($omsetRow->s2_uang_fisik ?? 0),
            'admin_pot_sales' => (float) ($omsetRow->s2_admin_pot_sales ?? 0),
            'adjustment' => (float) ($omsetRow->s2_adjustment ?? 0),
            'selisih_minus' => (float) ($omsetRow->s2_hanya_selisih_minus ?? 0),
            'tanggal_setor' => $omsetRow->s2_tanggal_setor ?? null,
            'sudah_disetor' => $isPaid($omsetRow->s2_sudah_disetor ?? 0),
            'foto_url' => $fotoUrlS2,
        ];
    
        $total = [
            'total_transaction' => $s1['total_transaction'] + $s2['total_transaction'],
            'diskon' => $s1['diskon'] + $s2['diskon'],
            'non_tunai' => $s1['non_tunai'] + $s2['non_tunai'],
            'expense' => $s1['expense'] + $s2['expense'],
            'uang_fisik' => $s1['uang_fisik'] + $s2['uang_fisik'],
            'admin_pot_sales' => $s1['admin_pot_sales'] + $s2['admin_pot_sales'],
            'adjustment' => $s1['adjustment'] + $s2['adjustment'],
            'selisih_minus' => $s1['selisih_minus'] + $s2['selisih_minus'],
        ];
    
        return [
            'exists' => true,
            'pic' => (string) ($omsetRow->pic ?? ''),
            'akumulasi_selisih' => (float) ($omsetRow->akumulasi_selisih ?? 0),
            'kekurangan_bulan_lalu' => (float) ($omsetRow->kekurangan_bulan_lalu ?? 0),
            's1' => $s1,
            's2' => $s2,
            'total' => $total,
            'meta' => [
                'filter_tanggal' => $today,
                'actual_tanggal' => $omsetRow->tanggal ?? null,
                'last_available_tanggal' => $omsetLastDate,
                'mode' => $mode,
                'shift_filter' => $shiftFilter,
            ],
        ];
    }

    public function dscOutlets(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $normalize = function ($name) {
            $name = strtoupper(trim((string) $name));
            return preg_replace('/\s+/', ' ', $name);
        };

        $rows = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('nama_outlet', 'like', "%{$q}%")
                    ->orWhere('id', $q);
            })
            ->get();

        $groups = [];

        foreach ($rows->groupBy(fn ($o) => $normalize($o->nama_outlet)) as $name => $items) {

            $ids = $items->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

            $groups[] = [
                'id' => 'group_' . md5($name), // tetap dipakai backend
                'text' => $name,               // 👈 bersih TANPA ID
                'ids' => $ids,                // 👈 penting untuk backend
            ];
        }

        return response()->json([
            'results' => $groups,
        ]);
    }

    public function dscAdjustmentImportPreview(Request $r)
    {
        $r->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:1,2',
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $outletId = (int) $r->outlet_id;
        $tanggal = (string) $r->tanggal;
        $shift = (int) $r->shift;

        // lock check (biar preview pun kasih warning)
        $close = $this->getCloseStatus($outletId, $tanggal);

        $file = $r->file('file');
        $path = $file->getRealPath();

        $rows = [];
        if (($fh = fopen($path, 'r')) !== false) {
            // ambil header
            $header = fgetcsv($fh);
            if (! $header) {
                return response()->json(['ok' => false, 'message' => 'File kosong / header tidak terbaca'], 422);
            }

            $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

            // mapping kolom
            $idxBahanId = array_search('bahan_id', $header, true);
            $idxNama = array_search('nama_bahan', $header, true);
            if ($idxNama === false) {
                $idxNama = array_search('nama', $header, true);
            }
            $idxAdj = array_search('adjustment_qty', $header, true);
            if ($idxAdj === false) {
                $idxAdj = array_search('adj', $header, true);
            }

            $idxWasteProduct = array_search('waste_product', $header, true);
            if ($idxWasteProduct === false) {
                $idxWasteProduct = array_search('waste_p', $header, true);
            }

            $idxWasteBahan = array_search('waste_bahan', $header, true);
            if ($idxWasteBahan === false) {
                $idxWasteBahan = array_search('waste_b', $header, true);
            }

            $idxWasteTepung = array_search('waste_tepung', $header, true);
            if ($idxWasteTepung === false) {
                $idxWasteTepung = array_search('waste_t', $header, true);
            }

            if ($idxAdj === false) {
                return response()->json(['ok' => false, 'message' => 'Kolom adjustment_qty/adj tidak ditemukan di header CSV'], 422);
            }
            if ($idxBahanId === false && $idxNama === false) {
                return response()->json(['ok' => false, 'message' => 'Kolom bahan_id atau nama_bahan/nama wajib ada'], 422);
            }

            $line = 1; // header = 1
            while (($data = fgetcsv($fh)) !== false) {
                $line++;

                $bahanIdRaw = ($idxBahanId !== false) ? ($data[$idxBahanId] ?? '') : '';
                $namaRaw = ($idxNama !== false) ? ($data[$idxNama] ?? '') : '';
                $adjRaw = $data[$idxAdj] ?? '';
                $wasteProductRaw = ($idxWasteProduct !== false) ? ($data[$idxWasteProduct] ?? '') : '';
                $wasteBahanRaw = ($idxWasteBahan !== false) ? ($data[$idxWasteBahan] ?? '') : '';
                $wasteTepungRaw = ($idxWasteTepung !== false) ? ($data[$idxWasteTepung] ?? '') : '';

                $rows[] = [
                    'row' => $line,
                    'bahan_id_raw' => trim((string) $bahanIdRaw),
                    'nama_raw' => trim((string) $namaRaw),
                    'adj_raw' => trim((string) $adjRaw),
                    'waste_product_raw' => trim((string) $wasteProductRaw),
                    'waste_bahan_raw' => trim((string) $wasteBahanRaw),
                    'waste_tepung_raw' => trim((string) $wasteTepungRaw),
                ];
            }
            fclose($fh);
        }

        // siapkan lookup nama -> id (kalau bahan_id tidak ada)
        $namaToId = [];
        $bahanAll = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->where('is_active', 1)
            ->get();

        foreach ($bahanAll as $b) {
            $namaToId[strtolower(trim((string) $b->nama_bahan))] = (int) $b->id;
        }

        $items = [];
        $errors = [];
        $warnings = [];

        foreach ($rows as $rr) {
            $rowNo = (int) $rr['row'];

            $bahanId = 0;
            if ($rr['bahan_id_raw'] !== '') {
                if (! ctype_digit($rr['bahan_id_raw'])) {
                    $errors[] = ['row' => $rowNo, 'nama' => $rr['nama_raw'] ?: '-', 'error' => 'bahan_id harus angka'];

                    continue;
                }
                $bahanId = (int) $rr['bahan_id_raw'];
            } else {
                $namaKey = strtolower(trim($rr['nama_raw']));
                if ($namaKey === '' || ! isset($namaToId[$namaKey])) {
                    $errors[] = ['row' => $rowNo, 'nama' => $rr['nama_raw'] ?: '-', 'error' => 'nama_bahan tidak dikenali (atau kosong)'];

                    continue;
                }
                $bahanId = (int) $namaToId[$namaKey];
            }

            // parse angka Indonesia/standar. Adjustment wajib, waste opsional boleh kosong.
            $parseNumber = function ($raw, bool $required = false) {
                $raw = trim((string) $raw);
                if ($raw === '') {
                    return $required ? null : 0.0;
                }

                $num = str_replace(['.', ' '], ['', ''], $raw);
                $num = str_replace(',', '.', $num);

                return is_numeric($num) ? (float) $num : null;
            };

            $adj = $parseNumber($rr['adj_raw'], true);
            if ($adj === null) {
                $errors[] = ['row' => $rowNo, 'nama' => $rr['nama_raw'] ?: (string) $bahanId, 'error' => 'adjustment_qty harus angka'];

                continue;
            }

            $wasteProduct = $parseNumber($rr['waste_product_raw'] ?? '', false);
            $wasteBahan = $parseNumber($rr['waste_bahan_raw'] ?? '', false);
            $wasteTepung = $parseNumber($rr['waste_tepung_raw'] ?? '', false);

            if ($wasteProduct === null || $wasteBahan === null || $wasteTepung === null) {
                $errors[] = ['row' => $rowNo, 'nama' => $rr['nama_raw'] ?: (string) $bahanId, 'error' => 'Kolom waste_product/waste_bahan/waste_tepung harus angka jika diisi'];

                continue;
            }

            // ambil info bahan
            $bahan = $bahanAll->firstWhere('id', $bahanId);
            if (! $bahan) {
                $errors[] = ['row' => $rowNo, 'nama' => $rr['nama_raw'] ?: (string) $bahanId, 'error' => 'bahan_id tidak ditemukan / tidak aktif'];

                continue;
            }

            $items[] = [
                'row' => $rowNo,
                'id' => (int) $bahanId,
                'nama_bahan' => (string) $bahan->nama_bahan,
                'satuan' => (string) $bahan->satuan,
                'adjustment_qty' => $adj,
                'waste_product' => $wasteProduct,
                'waste_bahan' => $wasteBahan,
                'waste_tepung' => $wasteTepung,
            ];
        }

        if ($close['is_closed']) {
            $warnings[] = ['row' => 0, 'nama' => '-', 'warning' => 'Kasir sudah ditutup. Apply akan ditolak (locked).'];
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'meta' => [
                    'outlet_id' => $outletId,
                    'tanggal' => $tanggal,
                    'shift' => $shift,
                    'count_items' => count($items),
                    'count_errors' => count($errors),
                    'count_warnings' => count($warnings),
                ],
                'items' => $items,
                'errors' => $errors,
                'warnings' => $warnings,
            ],
        ]);
    }
    public function dscAdjustmentApply(Request $r)
    {
        $r->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'nama_petugas' => 'required|string|max:120',
            'items' => 'required|array|min:1',
            'items.*.outlet_id' => 'nullable|integer',
            'items.*.bahan_id' => 'required|integer',
            'items.*.shift' => 'required|in:1,2',
            'items.*.adjustment_qty' => 'nullable|numeric',
            'items.*.waste_product' => 'nullable|numeric',
            'items.*.waste_bahan' => 'nullable|numeric',
            'items.*.waste_tepung' => 'nullable|numeric',
            'note' => 'nullable|string|max:255',
        ]);

        $outletIdDefault = (int) $r->outlet_id;
        $tanggal = $this->normalizeDateToYmd((string) $r->tanggal);
        $pic = trim((string) $r->nama_petugas);
        $note = trim((string) ($r->note ?: 'Adjustment'));
        $note = $note !== '' ? $note : 'Adjustment';

        $role = auth()->user()->role ?? null;
        $canBypassClose = in_array($role, ['superadmin', 'tm_manager', 'spv', 'superadmin_audit'], true);

        $close = $this->getCloseStatus($outletIdDefault, $tanggal);
        if (($close['is_closed'] ?? false) && ! $canBypassClose) {
            return response()->json([
                'ok' => false,
                'message' => 'Kasir sudah ditutup. Data terkunci.',
            ], 423);
        }

        $items = collect($r->items)
            ->map(function ($it) use ($outletIdDefault) {
                return [
                    'outlet_id' => (int) ($it['outlet_id'] ?? $outletIdDefault),
                    'bahan_id' => (int) ($it['bahan_id'] ?? 0),
                    'shift' => (int) ($it['shift'] ?? 0),
                    'adjustment_qty' => (float) ($it['adjustment_qty'] ?? 0),
                    'waste_product' => (float) ($it['waste_product'] ?? 0),
                    'waste_bahan' => (float) ($it['waste_bahan'] ?? 0),
                    'waste_tepung' => (float) ($it['waste_tepung'] ?? 0),
                ];
            })
            ->filter(fn ($it) => $it['outlet_id'] > 0 && $it['bahan_id'] > 0 && in_array($it['shift'], [1, 2], true))
            ->groupBy(fn ($it) => $it['outlet_id'] . '|' . $it['bahan_id'] . '|' . $it['shift'])
            ->map(fn ($rows) => $rows->last())
            ->values();

        if ($items->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Tidak ada item adjustment valid.',
            ], 422);
        }

        $bahanIds = $items->pluck('bahan_id')->unique()->values()->all();

        $bahanMap = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->whereIn('id', $bahanIds)
            ->where('is_active', 1)
            ->get()
            ->keyBy('id');

        $buildSafeKet = function (string $msg) use ($pic) {
            return $this->buildKeteranganSafe($msg, $pic);
        };

        $savedRows = [];

        DB::beginTransaction();

        try {
            foreach ($items as $it) {
                $rowOutletId = (int) $it['outlet_id'];
                $bahanId = (int) $it['bahan_id'];
                $shiftInt = (int) $it['shift'];
                $shift = (string) $shiftInt;

                $bahan = $bahanMap->get($bahanId);
                if (! $bahan) {
                    continue;
                }

                $adjNew = (float) $it['adjustment_qty'];
                $wasteProductNew = (float) $it['waste_product'];
                $wasteBahanNew = (float) $it['waste_bahan'];
                $wasteTepungInputNew = (float) $it['waste_tepung'];
                $wasteTepungTotalNew = $wasteProductNew + $wasteBahanNew + $wasteTepungInputNew;

                $existingDraft = DB::table('tbl_stock_draft')
                    ->where('outlet_id', $rowOutletId)
                    ->where('bahan_id', $bahanId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', $shift)
                    ->where('is_draft', 1)
                    ->lockForUpdate()
                    ->first();

                $existingFinal = DB::table('tbl_stock')
                    ->where('outlet_id', $rowOutletId)
                    ->where('bahan_id', $bahanId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', $shift)
                    ->lockForUpdate()
                    ->first();

                $updateRow = function (string $table, $row) use ($bahan, $adjNew, $wasteProductNew, $wasteBahanNew, $wasteTepungInputNew, $wasteTepungTotalNew, $buildSafeKet, $note, $pic) {
                    $opening = (float) ($row->opening_stock ?? 0);
                    $pin = (float) ($row->purchase_in ?? 0);
                    $mi = (float) ($row->mutasi_in ?? 0);
                    $mo = (float) ($row->mutasi_out ?? 0);
                    $ending = (float) ($row->ending_stock ?? 0);

                    // ADJ tidak masuk ke TOTAL.
                    // ADJ menjadi koreksi ENDING.
                    $total = $opening + $pin + $mi - $mo;
                    $endingAdjusted = $ending + $adjNew;
                    $used = $total - $endingAdjusted;
                    $actualTepung = strtolower(trim((string) ($bahan->nama_bahan ?? ''))) === 'tepung breader'
                        ? ($used - $wasteTepungTotalNew)
                        : 0.0;

                    $payload = [
                        'adjustment_qty' => $adjNew,
                        'used_qty' => $used,
                        'waste_product' => $wasteProductNew,
                        'waste_bahan' => $wasteBahanNew,
                        // Kolom ini menyimpan input Waste T saja. Rekap menampilkan akumulasi WP+WB+WT.
                        'waste_tepung' => $wasteTepungInputNew,
                        'actual_tepung' => $actualTepung,
                        'keterangan' => $buildSafeKet($note),
                        'updated_at' => now(),
                    ];

                    // tbl_stock_draft memakai kolom pic, sedangkan tbl_stock memakai nama_petugas.
                    // Kalau dipaksa update nama_petugas di tbl_stock_draft, MySQL error dan transaksi rollback,
                    // sehingga autosave terlihat tidak masuk sama sekali.
                    if ($table === 'tbl_stock_draft') {
                        $payload['pic'] = $pic;
                    } else {
                        $payload['nama_petugas'] = $pic;
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update($payload);

                    return [$total, $used, $actualTepung];
                };

                $targetRow = $existingDraft ?: $existingFinal;

                if ($targetRow) {
                    [$total, $used, $actualTepung] = $updateRow($existingDraft ? 'tbl_stock_draft' : 'tbl_stock', $targetRow);
                    $ending = (float) ($targetRow->ending_stock ?? 0);
                } else {
                    $opening = (float) $this->getOpeningStockUi($rowOutletId, $bahanId, $tanggal, $shiftInt);
                    $pin = 0.0;
                    $mi = 0.0;
                    $mo = 0.0;
                    $ending = $opening;
                    // ADJ tidak masuk ke TOTAL.
                    // ADJ menjadi koreksi ENDING.
                    $total = $opening + $pin + $mi - $mo;
                    $endingAdjusted = $ending + $adjNew;
                    $used = $total - $endingAdjusted;
                    $actualTepung = strtolower(trim((string) ($bahan->nama_bahan ?? ''))) === 'tepung breader'
                        ? ($used - $wasteTepungTotalNew)
                        : 0.0;

                    DB::table('tbl_stock_draft')->insert([
                        'outlet_id' => $rowOutletId,
                        'tanggal' => $tanggal,
                        'shift' => $shiftInt,
                        'bahan_id' => $bahanId,
                        'purchase_in' => 0,
                        'mutasi_in' => 0,
                        'mutasi_out' => 0,
                        'adjustment_qty' => $adjNew,
                        'used_qty' => $used,
                        'ending_stock' => $ending,
                        'actual_tepung' => $actualTepung,
                        'waste_product' => $wasteProductNew,
                        'waste_bahan' => $wasteBahanNew,
                        'waste_tepung' => $wasteTepungInputNew,
                        'uang_plus' => 0,
                        'customer_unit' => 0,
                        'shift_1' => 0,
                        'shift_2' => 0,
                        'keterangan' => $buildSafeKet($note),
                        'is_draft' => 1,
                        'satuan' => (string) ($bahan->satuan ?? null),
                        'opening_stock' => $opening,
                        'pic' => $pic,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $savedRows[] = [
                    'outlet_id' => $rowOutletId,
                    'bahan_id' => $bahanId,
                    'shift' => $shiftInt,
                    'adjustment_qty' => $adjNew,
                    'waste_product' => $wasteProductNew,
                    'waste_bahan' => $wasteBahanNew,
                    'waste_tepung' => $wasteTepungInputNew,
                    'waste_tepung_total' => $wasteTepungTotalNew,
                    'ending_stock' => $ending,
                    'ending_stock_adjusted' => ($ending ?? 0) + ($adjNew ?? 0),
                    'used_qty' => $used,
                    'actual_tepung' => $actualTepung,
                ];
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Adjustment berhasil autosave dan stock dihitung ulang.',
                'data' => [
                    'count' => count($savedRows),
                    'rows' => $savedRows,
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Gagal menerapkan adjustment: ' . $e->getMessage(),
            ], 500);
        }
    }

    // public function applyAdjustment(Request $request)
    // {
    //     $v = Validator::make($request->all(), [
    //         'outlet_id' => 'required|integer',
    //         'tanggal' => 'required|date',
    //         'nama_petugas' => 'required|string|max:100',
    //         'items' => 'required|array|min:1',
    //         'items.*.bahan_id' => 'required|integer',
    //         'items.*.shift' => 'required|integer|in:1,2',
    //         'items.*.adjustment_qty' => 'nullable|numeric', // delta ending
    //     ]);

    //     if ($v->fails()) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Validasi gagal',
    //             'errors' => $v->errors(),
    //         ], 422);
    //     }

    //     $outletId = (int) $request->outlet_id;
    //     $tanggal = (string) $request->tanggal;
    //     $pic = trim((string) $request->nama_petugas);

    //     DB::beginTransaction();
    //     try {
    //         foreach ($request->items as $it) {
    //             $bahanId = (int) $it['bahan_id'];
    //             $shift = (int) $it['shift'];
    //             $deltaEnding = (float) ($it['adjustment_qty'] ?? 0);

    //             $row = DB::table('tbl_stock')
    //                 ->where('outlet_id', $outletId)
    //                 ->where('tanggal', $tanggal)
    //                 ->where('shift', $shift)
    //                 ->where('bahan_id', $bahanId)
    //                 ->first();

    //             if (! $row) {
    //                 $opening = (float) $this->getOpeningStock($outletId, $bahanId, $tanggal, $shift);
    //                 $endingNew = $opening + $deltaEnding;
    //                 $totalNoAdj = $opening;
    //                 $used = $totalNoAdj - $endingNew;

    //                 DB::table('tbl_stock')->insert([
    //                     'outlet_id' => $outletId,
    //                     'tanggal' => $tanggal,
    //                     'shift' => $shift,
    //                     'bahan_id' => $bahanId,

    //                     'opening_stock' => $opening,
    //                     'purchase_in' => 0,
    //                     'mutasi_in' => 0,
    //                     'mutasi_out' => 0,

    //                     'adjustment_qty' => $deltaEnding,
    //                     'ending_stock' => $endingNew,
    //                     'used_qty' => $used,

    //                     'waste_product' => 0,
    //                     'waste_bahan' => 0,
    //                     'uang_plus' => 0,

    //                     'keterangan' => "Adj ending by {$pic}",
    //                     'nama_petugas' => $pic,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ]);
    //             } else {
    //                 $endingOld = (float) ($row->ending_stock ?? 0);
    //                 $endingNew = $endingOld + $deltaEnding;

    //                 $opening = (float) ($row->opening_stock ?? 0);
    //                 $pin = (float) ($row->purchase_in ?? 0);
    //                 $mi = (float) ($row->mutasi_in ?? 0);
    //                 $mo = (float) ($row->mutasi_out ?? 0);
    //                 $totalNoAdj = $opening + $pin + $mi - $mo;

    //                 $used = $totalNoAdj - $endingNew;

    //                 DB::table('tbl_stock')
    //                     ->where('id', $row->id)
    //                     ->update([
    //                         'adjustment_qty' => $deltaEnding,
    //                         'ending_stock' => $endingNew,
    //                         'nama_petugas' => $pic,   // ✅ tambahkan
    //                         'used_qty' => $used,
    //                         'nama_petugas' => $pic,
    //                         'updated_at' => now(),
    //                     ]);
    //             }
    //         }

    //         DB::commit();

    //         return response()->json(['ok' => true, 'message' => 'Adjustment tersimpan (ending mode)']);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         return response()->json(['ok' => false, 'message' => 'Gagal simpan', 'error' => $e->getMessage()], 500);
    //     }
    // }

    private function normalizeDateToYmd(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return date('Y-m-d');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $raw)) {
                return \Carbon\Carbon::createFromFormat('d/m/Y', $raw)->format('Y-m-d');
            }
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $raw)) {
                return \Carbon\Carbon::createFromFormat('d-m-Y', $raw)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            return date('Y-m-d');
        }
    }

    private function resolveOpen($outletId, $bahanId, $tanggal, $shift, $stockMap)
    {
        $sr = $stockMap[$bahanId][$shift] ?? null;

        // prioritas: opening_stock dari DB (hasil import / input)
        if ($sr && isset($sr->opening_stock) && (float) $sr->opening_stock != 0.0) {
            return (float) $sr->opening_stock;
        }

        // fallback: histori
        return (float) $this->getOpeningStock($outletId, $bahanId, $tanggal, $shift);
    }

    public function dscFormulirOmset(Request $request)
    {
        $todayRaw = (string) $request->get('tanggal', date('Y-m-d'));
        $outletRaw = (int) $request->get('outlet_id', 0);
        $shiftRaw = (string) $request->get('shift', '1');
        $today = $this->normalizeDateToYmd($todayRaw);
    
        $shift = in_array($shiftRaw, ['1', '2'], true) ? $shiftRaw : '1';
    
        $userId = auth()->id();
        $role = DB::table('users')->where('id', $userId)->value('role');
    
        $outlets = $this->getGroupedOutletsForUser($userId);
    
        $selectedOutlet = null;
        if ($outletRaw > 0) {
            $displayId = $this->getOutletDisplayIdFromSelected($outletRaw, $outlets);
            $selectedOutlet = $outlets->firstWhere('id', $displayId);
        }
    
        if (! $selectedOutlet && $outlets->count()) {
            $selectedOutlet = $outlets->first();
        }
    
        $outletId = $selectedOutlet ? (int) $selectedOutlet->id : 0;
    
        return view('Investor.Inventory.dscFormulirOmset', [
            'today' => $today,
            'outletId' => $outletId ?: '',
            'shift' => $shift,
            'outlets' => $outlets,
            'selectedOutlet' => $selectedOutlet,
        ]);
    }
    
    private function publicOmsetPhotoUrl(?string $dbPath): ?string
    {
        $p = trim((string) $dbPath);
    
        if ($p === '') {
            return null;
        }
    
        $p = preg_replace('#^(public/|storage/)#', '', $p);
        $p = ltrim($p, '/');
    
        $newAbs = public_path('storage/' . $p);
        if (is_file($newAbs)) {
            return url('/storage/' . $p);
        }
    
        $oldAbs = storage_path('app/public/' . $p);
        if (is_file($oldAbs)) {
            $targetDir = dirname($newAbs);
    
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }
    
            @copy($oldAbs, $newAbs);
    
            if (is_file($newAbs)) {
                return url('/storage/' . $p);
            }
        }
    
        return null;
    }


    private function hasOmsetShiftData($row, int $shift): bool
    {
        // Dipakai sebagai status LOCK/FINAL, bukan sekadar pernah auto-save.
        // Selama shift belum lengkap, data masih boleh diedit dan boleh di-save ulang.
        if (!$row) {
            return false;
        }

        $p = $shift === 1 ? 's1_' : 's2_';

        $hasNumber = function (string $col) use ($row): bool {
            return isset($row->{$col}) && (float) $row->{$col} > 0;
        };

        $hasText = function (string $col) use ($row): bool {
            return isset($row->{$col}) && trim((string) $row->{$col}) !== '';
        };

        // Field yang benar-benar menandakan shift sudah FULL.
        // Field seperti diskon, non tunai, expense, admin, adjustment, dan hanya selisih
        // boleh bernilai 0, jadi tidak dijadikan syarat lock.
        $hasMainOmset = $hasNumber($p . 'total_transaction');
        $hasCash      = $hasNumber($p . 'uang_fisik');
        $hasTanggal   = $hasText($p . 'tanggal_setor');
        $hasSetoran   = $hasNumber($p . 'sudah_disetor');

        $photoColumns = [];
        if ($shift === 1) {
            $photoColumns[] = 'bukti_foto_s1';
        } else {
            $photoColumns[] = 'bukti_foto_s2';
        }

        // Fallback untuk schema lama 1 kolom foto.
        $photoColumns[] = 'bukti_foto';

        $hasPhoto = false;
        foreach ($photoColumns as $col) {
            if ($hasText($col)) {
                $hasPhoto = true;
                break;
            }
        }

        return $hasMainOmset && $hasCash && $hasTanggal && $hasSetoran && $hasPhoto;
    }

    private function omsetShiftLocks($row): array
    {
        return [
            's1' => $this->hasOmsetShiftData($row, 1),
            's2' => $this->hasOmsetShiftData($row, 2),
        ];
    }

    public function dscOmsetLoad(Request $r)
    {
        $r->validate([
            'outlet_id' => 'required|integer|min:1',
            'tanggal'   => 'required',
        ]);
    
        $selectedOutletId = (int) $r->input('outlet_id');
        $today = $this->normalizeDateToYmd((string) $r->input('tanggal'));
    
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $displayOutletId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);
        $aliasIds = $this->getOutletAliasIdsFromSelected($selectedOutletId, $groupedOutlets);
    
        $num = function ($v): float {
            if ($v === null) return 0.0;
            if (is_numeric($v)) return (float) $v;
    
            $s = trim((string) $v);
            if ($s === '') return 0.0;
    
            $s = str_replace([' ', '.'], '', $s);
            $s = str_replace(',', '.', $s);
    
            return is_numeric($s) ? (float) $s : 0.0;
        };
    
        $row = DB::table('tbl_dsc_omset_setoran')
            ->whereIn('outlet_id', $aliasIds)
            ->whereDate('tanggal', $today)
            ->orderByDesc('id')
            ->first();
    
        $mode = $row ? 'EXACT' : 'NO_DATA';
    
        if (!$row) {
            return response()->json([
                'ok' => true,
                'meta' => [
                    'mode' => $mode,
                    'row_tanggal' => null,
                    'requested_tanggal' => $today,
                    'outlet_aliases' => $aliasIds,
                    'locks' => ['s1' => false, 's2' => false],
                ],
                'message' => 'Anda belum mengisi setoran pada tanggal ini.',
                'data' => [
                    'outlet_id' => $displayOutletId,
                    'tanggal' => $today,
                    'locks' => ['s1' => false, 's2' => false],
                    'pic' => '',
                    'extra' => [
                        'akumulasi_selisih' => 0,
                        'kekurangan_bulan_lalu' => 0
                    ],
                    'omset' => [
                        's1' => [
                            'total_transaction' => 0,
                            'diskon' => 0,
                            'non_tunai' => 0,
                            'expense' => 0,
                            'uang_fisik' => 0,
                        ],
                        's2' => [
                            'total_transaction' => 0,
                            'diskon' => 0,
                            'non_tunai' => 0,
                            'expense' => 0,
                            'uang_fisik' => 0,
                        ],
                    ],
                    'setoran' => [
                        's1' => [
                            'hanya_selisih' => 0,
                            'tanggal_setor' => null,
                            'sudah_setor' => 0,
                            'admin' => 0,
                            'adjustment' => 0,
                            'bukti_foto' => '',
                            'bukti_url' => null,
                        ],
                        's2' => [
                            'hanya_selisih' => 0,
                            'tanggal_setor' => null,
                            'sudah_setor' => 0,
                            'admin' => 0,
                            'adjustment' => 0,
                            'bukti_foto' => '',
                            'bukti_url' => null,
                        ],
                    ],
                ],
            ]);
        }
    
        $hasS1 = Schema::hasColumn('tbl_dsc_omset_setoran', 'bukti_foto_s1');
        $hasS2 = Schema::hasColumn('tbl_dsc_omset_setoran', 'bukti_foto_s2');
    
        if ($hasS1 || $hasS2) {
            $pathS1 = $hasS1 ? ($row->bukti_foto_s1 ?? null) : null;
            $pathS2 = $hasS2 ? ($row->bukti_foto_s2 ?? null) : null;
        } else {
            $pathS1 = $row->bukti_foto ?? null;
            $pathS2 = $row->bukti_foto ?? null;
        }
    
        $urlS1 = $this->publicOmsetPhotoUrl($pathS1);
        $urlS2 = $this->publicOmsetPhotoUrl($pathS2);
        $locks = $this->omsetShiftLocks($row);
    
        $data = [
            'outlet_id' => $displayOutletId,
            'outlet_aliases' => $aliasIds,
            'tanggal' => $today,
            'locks' => $locks,
            'pic' => (string) ($row->pic ?? ''),
            'extra' => [
                'akumulasi_selisih' => $num($row->akumulasi_selisih ?? 0),
                'kekurangan_bulan_lalu' => $num($row->kekurangan_bulan_lalu ?? 0),
            ],
            'omset' => [
                's1' => [
                    'total_transaction' => $num($row->s1_total_transaction ?? 0),
                    'diskon' => $num($row->s1_diskon ?? 0),
                    'non_tunai' => $num($row->s1_non_tunai ?? 0),
                    'expense' => $num($row->s1_expense ?? 0),
                    'uang_fisik' => $num($row->s1_uang_fisik ?? 0),
                ],
                's2' => [
                    'total_transaction' => $num($row->s2_total_transaction ?? 0),
                    'diskon' => $num($row->s2_diskon ?? 0),
                    'non_tunai' => $num($row->s2_non_tunai ?? 0),
                    'expense' => $num($row->s2_expense ?? 0),
                    'uang_fisik' => $num($row->s2_uang_fisik ?? 0),
                ],
            ],
            'setoran' => [
                's1' => [
                    'hanya_selisih' => $num($row->s1_hanya_selisih_minus ?? 0),
                    'tanggal_setor' => $row->s1_tanggal_setor ?? null,
                    'sudah_setor' => $num($row->s1_sudah_disetor ?? 0),
                    'admin' => $num($row->s1_admin_pot_sales ?? 0),
                    'adjustment' => $num($row->s1_adjustment ?? 0),
                    'bukti_foto' => (string) ($pathS1 ?? ''),
                    'bukti_url' => $urlS1,
                ],
                's2' => [
                    'hanya_selisih' => $num($row->s2_hanya_selisih_minus ?? 0),
                    'tanggal_setor' => $row->s2_tanggal_setor ?? null,
                    'sudah_setor' => $num($row->s2_sudah_disetor ?? 0),
                    'admin' => $num($row->s2_admin_pot_sales ?? 0),
                    'adjustment' => $num($row->s2_adjustment ?? 0),
                    'bukti_foto' => (string) ($pathS2 ?? ''),
                    'bukti_url' => $urlS2,
                ],
            ],
        ];
    
        return response()->json([
            'ok' => true,
            'meta' => [
                'mode' => $mode,
                'row_tanggal' => $row?->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') : null,
                'requested_tanggal' => $today,
                'outlet_aliases' => $aliasIds,
                'locks' => $locks,
            ],
            'data' => $data,
        ]);
    }

    private function publicHtmlPath(string $rel = ''): string
    {
        // asumsi project kamu ada di /home/user/laravel
        // dan docroot main domain ada di /home/user/public_html
        return base_path('../public_html/' . ltrim($rel, '/'));
    }
        
    private function storeOmsetPhotoToPublic($file, $outletId, $tanggal)
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $name = uniqid('', true) . '.' . $ext;
    
        $file->storeAs('dsc_bukti_uang_omset', $name, 'public');
    
        return "dsc_bukti_uang_omset/{$name}";
    }
    
    private function deletePublicOmsetPhotoIfExists(?string $dbPath): void
    {
        if (!$dbPath) return;
    
        $p = trim($dbPath);
        $p = preg_replace('#^(public/|storage/)#', '', $p);
        $p = ltrim($p, '/');
    
        $abs = $this->publicHtmlPath('storage/'.$p);
    
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
    
    private function omsetTesseractBinary(): ?string
    {
        // Shared hosting sering mematikan exec(). Jangan panggil exec/shell command
        // supaya proses simpan omset tidak gagal dengan error:
        // Call to undefined function App\Http\Controllers\exec().
        return null;
    }

    private function runOmsetPhotoOcr(string $absolutePath): array
    {
        if (!is_file($absolutePath)) {
            return [
                'ok' => false,
                'text' => '',
                'error' => 'File bukti tidak ditemukan untuk OCR.',
            ];
        }

        return [
            'ok' => false,
            'text' => '',
            'error' => 'OCR dilewati karena fungsi exec/shell command tidak tersedia di server.',
        ];
    }

    private function normalizeOcrMoney(string $raw): ?float
    {
        $s = trim($raw);

        if ($s === '') {
            return null;
        }

        $s = preg_replace('/[^\d,.]/', '', $s);

        if ($s === '') {
            return null;
        }

        // Format Indonesia: 1.250.000 atau 1.250.000,00
        if (str_contains($s, '.')) {
            $s = str_replace('.', '', $s);
        }

        if (str_contains($s, ',')) {
            $s = str_replace(',', '.', $s);
        }

        if (!is_numeric($s)) {
            return null;
        }

        return (float) $s;
    }

    private function extractOmsetOcrNominal(string $text): ?float
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        $lines = preg_split('/\R+/', $text);
        $candidates = [];

        foreach ($lines as $line) {
            $lineRaw = trim($line);
            $lineLower = strtolower($lineRaw);

            // Prioritaskan baris yang mengandung keyword pembayaran.
            $weight = 0;
            if (preg_match('/(rp|jumlah|total|nominal|transfer|bayar|setor|disetor|transaksi)/i', $lineRaw)) {
                $weight += 10;
            }

            if (preg_match_all('/(?:rp\s*)?(\d{1,3}(?:[.,]\d{3})+(?:,\d{2})?|\d{5,})/i', $lineRaw, $matches)) {
                foreach ($matches[1] as $m) {
                    $amount = $this->normalizeOcrMoney($m);

                    if ($amount !== null && $amount > 0) {
                        // Hindari nomor rekening/ref yang sangat panjang.
                        $digits = preg_replace('/\D/', '', $m);
                        if (strlen($digits) > 12) {
                            continue;
                        }

                        $candidates[] = [
                            'amount' => $amount,
                            'score' => $weight + min(strlen($digits), 10),
                            'line' => $lineRaw,
                        ];
                    }
                }
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) {
            if ($a['score'] === $b['score']) {
                return $b['amount'] <=> $a['amount'];
            }

            return $b['score'] <=> $a['score'];
        });

        return (float) $candidates[0]['amount'];
    }

    private function extractOmsetOcrDate(string $text): ?string
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        $patterns = [
            // 11/05/2026 or 11-05-2026
            '/\b(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2,4})\b/',
            // 2026-05-11
            '/\b(\d{4})[\/\-.](\d{1,2})[\/\-.](\d{1,2})\b/',
        ];

        foreach ($patterns as $idx => $pattern) {
            if (preg_match($pattern, $text, $m)) {
                try {
                    if ($idx === 0) {
                        $d = (int) $m[1];
                        $mo = (int) $m[2];
                        $y = (int) $m[3];
                        if ($y < 100) {
                            $y += 2000;
                        }
                    } else {
                        $y = (int) $m[1];
                        $mo = (int) $m[2];
                        $d = (int) $m[3];
                    }

                    return \Carbon\Carbon::createFromDate($y, $mo, $d)->format('Y-m-d');
                } catch (\Throwable $e) {
                    continue;
                }
            }
        }

        // Bulan Indonesia sederhana
        $bulan = [
            'jan' => 1, 'januari' => 1,
            'feb' => 2, 'februari' => 2,
            'mar' => 3, 'maret' => 3,
            'apr' => 4, 'april' => 4,
            'mei' => 5,
            'jun' => 6, 'juni' => 6,
            'jul' => 7, 'juli' => 7,
            'agu' => 8, 'agustus' => 8,
            'sep' => 9, 'september' => 9,
            'okt' => 10, 'oktober' => 10,
            'nov' => 11, 'november' => 11,
            'des' => 12, 'desember' => 12,
        ];

        if (preg_match('/\b(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})\b/i', $text, $m)) {
            $d = (int) $m[1];
            $monName = strtolower($m[2]);
            $y = (int) $m[3];

            if (isset($bulan[$monName])) {
                try {
                    return \Carbon\Carbon::createFromDate($y, $bulan[$monName], $d)->format('Y-m-d');
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        return null;
    }

    private function extractOmsetOcrReference(string $text): ?string
    {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        $patterns = [
            '/(?:no\.?\s*ref(?:erensi)?|ref(?:erensi)?|id\s*transaksi|no\.?\s*transaksi|transaction\s*id)\s*[:\-]?\s*([A-Z0-9\-\/]{6,40})/i',
            '/\b([A-Z]{2,6}\d{8,30})\b/i',
            '/\b(\d{10,30})\b/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                return strtoupper(trim($m[1]));
            }
        }

        return null;
    }

    private function publicOmsetPhotoAbsolutePath(?string $dbPath): ?string
    {
        $p = trim((string) $dbPath);

        if ($p === '') {
            return null;
        }

        $p = preg_replace('#^(public/|storage/)#', '', $p);
        $p = ltrim($p, '/');

        $candidates = [
            public_path('storage/' . $p),
            storage_path('app/public/' . $p),
            base_path('../public_html/storage/' . $p),
        ];

        foreach ($candidates as $abs) {
            if (is_file($abs)) {
                return $abs;
            }
        }

        return null;
    }

    private function analyzeOmsetProofFraud(array $payload): array
    {
        $source = strtolower((string) ($payload['source'] ?? 'upload'));
        $hash = (string) ($payload['hash'] ?? '');
        $ocrOk = (bool) ($payload['ocr_ok'] ?? false);
        $ocrError = (string) ($payload['ocr_error'] ?? '');
        $ocrNominal = $payload['ocr_nominal'] ?? null;
        $ocrTanggal = $payload['ocr_tanggal'] ?? null;
        $ocrReference = $payload['ocr_reference'] ?? null;
        $formNominal = (float) ($payload['form_nominal'] ?? 0);
        $formTanggal = (string) ($payload['form_tanggal'] ?? '');
        $outletId = (int) ($payload['outlet_id'] ?? 0);
        $shift = (int) ($payload['shift'] ?? 0);

        $score = 0;
        $flags = [];

        if ($source === 'camera') {
            $flags[] = 'SOURCE_CAMERA_REALTIME';
        } else {
            $score += 20;
            $flags[] = 'SOURCE_UPLOAD_NEED_REVIEW';
        }

        if ($hash !== '') {
            $hashColumn = $shift === 1 ? 's1_bukti_hash' : 's2_bukti_hash';

            $duplicate = DB::table('tbl_dsc_omset_setoran')
                ->where($hashColumn, $hash)
                ->where(function ($q) use ($outletId, $formTanggal) {
                    $q->where('outlet_id', '!=', $outletId)
                    ->orWhere('tanggal', '!=', $formTanggal);
                })
                ->exists();

            if ($duplicate) {
                $score += 80;
                $flags[] = 'DUPLICATE_PHOTO_HASH';
            }
        }

        if (!$ocrOk) {
            $score += 15;
            $flags[] = 'OCR_NOT_AVAILABLE_OR_FAILED';
            if ($ocrError !== '') {
                $flags[] = 'OCR_ERROR: ' . mb_substr($ocrError, 0, 120);
            }
        } else {
            if ($ocrNominal === null) {
                $score += 25;
                $flags[] = 'OCR_NOMINAL_NOT_FOUND';
            } else {
                $diff = abs((float) $ocrNominal - $formNominal);

                // toleransi 100 rupiah untuk hasil OCR minor.
                if ($formNominal > 0 && $diff > 100) {
                    $score += 60;
                    $flags[] = 'OCR_NOMINAL_MISMATCH_FORM';
                } else {
                    $flags[] = 'OCR_NOMINAL_MATCH';
                }
            }

            if ($ocrTanggal === null) {
                $score += 25;
                $flags[] = 'OCR_DATE_NOT_FOUND';
            } elseif ($formTanggal !== '' && $ocrTanggal !== $formTanggal) {
                $score += 45;
                $flags[] = 'OCR_DATE_MISMATCH_FORM';
            } else {
                $flags[] = 'OCR_DATE_MATCH';
            }

            if (!$ocrReference) {
                $score += 10;
                $flags[] = 'OCR_REFERENCE_NOT_FOUND';
            } else {
                $flags[] = 'OCR_REFERENCE_FOUND';
            }
        }

        if ($score >= 70) {
            $status = 'suspicious';
        } elseif ($score >= 25) {
            $status = 'need_review';
        } else {
            $status = 'trusted';
        }

        return [
            'status' => $status,
            'score' => $score,
            'reason' => implode(' | ', $flags),
            'flags' => $flags,
        ];
    }

    public function dscOmsetSave(Request $r)
    {
        $v = Validator::make($r->all(), [
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:1,2',
            'pic' => 'required|string|max:120',

            'total_transaction' => 'nullable|numeric',
            'diskon' => 'nullable|numeric',
            'non_tunai' => 'nullable|numeric',
            'expense' => 'nullable|numeric',
            'uang_fisik' => 'nullable|numeric',

            'hanya_selisih_minus' => 'nullable|numeric',
            'tanggal_setor' => 'nullable|date_format:Y-m-d',
            'sudah_disetor' => 'nullable|numeric',
            'admin_pot_sales' => 'nullable|numeric',
            'adjustment' => 'nullable|numeric',

            'akumulasi_selisih' => 'nullable|numeric',
            'kekurangan_bulan_lalu' => 'nullable|numeric',

            'bukti_source' => 'nullable|in:camera,upload',
            'bukti_foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'hapus_bukti_foto' => 'nullable|in:0,1',
        ]);

        if ($v->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal',
                'errors' => $v->errors()
            ], 422);
        }

        $selectedOutletId = (int) $r->outlet_id;
        $tanggal = (string) $r->tanggal;
        $shift = (int) $r->shift;
        $pic = trim((string) $r->pic);

        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $displayOutletId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);

        $existing = DB::table('tbl_dsc_omset_setoran')
            ->where('outlet_id', $displayOutletId)
            ->where('tanggal', $tanggal)
            ->first();

        $p = $shift === 1 ? 's1_' : 's2_';

        // SEMENTARA: jangan lock shift dulu.
        // Data boleh disimpan ulang berkali-kali untuk koreksi.
        if (false && $existing && $this->hasOmsetShiftData($existing, $shift)) {
            return response()->json([
                'ok' => false,
                'message' => 'Shift ' . $shift . ' sudah pernah disimpan. Data tetap bisa dilihat, tapi tidak bisa diedit lagi.',
            ], 409);
        }

        $hasS1PhotoColumn = Schema::hasColumn('tbl_dsc_omset_setoran', 'bukti_foto_s1');
        $hasS2PhotoColumn = Schema::hasColumn('tbl_dsc_omset_setoran', 'bukti_foto_s2');

        if ($shift === 1 && $hasS1PhotoColumn) {
            $photoColumn = 'bukti_foto_s1';
        } elseif ($shift === 2 && $hasS2PhotoColumn) {
            $photoColumn = 'bukti_foto_s2';
        } else {
            $photoColumn = 'bukti_foto';
        }

        $sourceColumn = "{$p}bukti_source";
        $hashColumn = "{$p}bukti_hash";
        $reviewStatusColumn = "{$p}review_status";
        $reviewReasonColumn = "{$p}review_reason";
        $uploadedAtColumn = "{$p}bukti_uploaded_at";

        $ocrTextColumn = "{$p}ocr_text";
        $ocrNominalColumn = "{$p}ocr_nominal";
        $ocrTanggalColumn = "{$p}ocr_tanggal";
        $ocrReferenceColumn = "{$p}ocr_reference";
        $fraudScoreColumn = "{$p}fraud_score";
        $fraudFlagsColumn = "{$p}fraud_flags";

        $oldPhotoPath = $existing->{$photoColumn} ?? null;
        $newPhotoPath = $oldPhotoPath;

        $source = (string) ($existing->{$sourceColumn} ?? '');
        $hash = (string) ($existing->{$hashColumn} ?? '');
        $reviewStatus = (string) ($existing->{$reviewStatusColumn} ?? '');
        $reviewReason = (string) ($existing->{$reviewReasonColumn} ?? '');

        $ocrText = (string) ($existing->{$ocrTextColumn} ?? '');
        $ocrNominal = $existing->{$ocrNominalColumn} ?? null;
        $ocrTanggal = $existing->{$ocrTanggalColumn} ?? null;
        $ocrReference = $existing->{$ocrReferenceColumn} ?? null;
        $fraudScore = (int) ($existing->{$fraudScoreColumn} ?? 0);
        $fraudFlags = (string) ($existing->{$fraudFlagsColumn} ?? '');

        if ($r->hasFile('bukti_foto')) {
            $source = (string) ($r->bukti_source ?: 'upload');
            $hash = hash_file('sha256', $r->file('bukti_foto')->getRealPath());

            $newPhotoPath = $this->storeOmsetPhotoToPublic(
                $r->file('bukti_foto'),
                $displayOutletId,
                $tanggal
            );

            if ($oldPhotoPath && $oldPhotoPath !== $newPhotoPath) {
                $this->deletePublicOmsetPhotoIfExists($oldPhotoPath);
            }

            $abs = $this->publicOmsetPhotoAbsolutePath($newPhotoPath);
            $ocr = $abs ? $this->runOmsetPhotoOcr($abs) : [
                'ok' => false,
                'text' => '',
                'error' => 'Path file bukti tidak ditemukan.',
            ];

            $ocrText = (string) ($ocr['text'] ?? '');
            $ocrNominal = $ocr['ok'] ? $this->extractOmsetOcrNominal($ocrText) : null;
            $ocrTanggal = $ocr['ok'] ? $this->extractOmsetOcrDate($ocrText) : null;
            $ocrReference = $ocr['ok'] ? $this->extractOmsetOcrReference($ocrText) : null;

            $analysis = $this->analyzeOmsetProofFraud([
                'source' => $source,
                'hash' => $hash,
                'ocr_ok' => (bool) ($ocr['ok'] ?? false),
                'ocr_error' => (string) ($ocr['error'] ?? ''),
                'ocr_nominal' => $ocrNominal,
                'ocr_tanggal' => $ocrTanggal,
                'ocr_reference' => $ocrReference,
                'form_nominal' => (float) ($r->sudah_disetor ?? 0),
                'form_tanggal' => $tanggal,
                'outlet_id' => $displayOutletId,
                'shift' => $shift,
            ]);

            $reviewStatus = $analysis['status'];
            $reviewReason = $analysis['reason'];
            $fraudScore = $analysis['score'];
            $fraudFlags = implode("\n", $analysis['flags']);
        }

        $n = fn ($value) => (float) ($value ?? 0);

        $update = [
            "{$p}total_transaction" => $n($r->total_transaction),
            "{$p}diskon" => $n($r->diskon),
            "{$p}non_tunai" => $n($r->non_tunai),
            "{$p}expense" => $n($r->expense),
            "{$p}uang_fisik" => $n($r->uang_fisik),

            "{$p}hanya_selisih_minus" => $n($r->hanya_selisih_minus),
            "{$p}tanggal_setor" => $r->tanggal_setor ?: null,
            "{$p}sudah_disetor" => $n($r->sudah_disetor),
            "{$p}admin_pot_sales" => $n($r->admin_pot_sales),
            "{$p}adjustment" => $n($r->adjustment),

            'akumulasi_selisih' => $n($r->akumulasi_selisih),
            'kekurangan_bulan_lalu' => $n($r->kekurangan_bulan_lalu),

            $sourceColumn => $source,
            $hashColumn => $hash,
            $reviewStatusColumn => $reviewStatus,
            $reviewReasonColumn => $reviewReason,

            $ocrTextColumn => $ocrText,
            $ocrNominalColumn => $ocrNominal,
            $ocrTanggalColumn => $ocrTanggal,
            $ocrReferenceColumn => $ocrReference,
            $fraudScoreColumn => $fraudScore,
            $fraudFlagsColumn => $fraudFlags,

            'pic' => $pic,
            'updated_at' => now(),
            'created_at' => DB::raw('COALESCE(created_at, NOW())'),
        ];

        if ($r->hasFile('bukti_foto')) {
            $update[$uploadedAtColumn] = now();
        }

        if ((string) $r->input('hapus_bukti_foto', '0') === '1') {
            if ($oldPhotoPath) {
                $this->deletePublicOmsetPhotoIfExists($oldPhotoPath);
            }

            $newPhotoPath = null;
            $source = '';
            $hash = '';
            $reviewStatus = '';
            $reviewReason = '';

            $ocrText = '';
            $ocrNominal = null;
            $ocrTanggal = null;
            $ocrReference = null;
            $fraudScore = 0;
            $fraudFlags = '';

            $update[$photoColumn] = null;
            $update[$sourceColumn] = '';
            $update[$hashColumn] = '';
            $update[$reviewStatusColumn] = '';
            $update[$reviewReasonColumn] = '';

            $update[$ocrTextColumn] = '';
            $update[$ocrNominalColumn] = null;
            $update[$ocrTanggalColumn] = null;
            $update[$ocrReferenceColumn] = null;
            $update[$fraudScoreColumn] = 0;
            $update[$fraudFlagsColumn] = '';
        }

        if ($photoColumn) {
            $update[$photoColumn] = $newPhotoPath;
        }

        DB::table('tbl_dsc_omset_setoran')->updateOrInsert(
            [
                'outlet_id' => $displayOutletId,
                'tanggal' => $tanggal,
            ],
            $update
        );

        return response()->json([
            'ok' => true,
            'message' => 'Omset/Setoran Shift ' . $shift . ' tersimpan',
            'data' => [
                'shift' => $shift,
                'bukti_foto' => $newPhotoPath,
                'bukti_url' => $this->publicOmsetPhotoUrl($newPhotoPath),

                'bukti_source' => $source,
                'bukti_hash' => $hash,
                'review_status' => $reviewStatus,
                'review_reason' => $reviewReason,

                'ocr_nominal' => $ocrNominal,
                'ocr_tanggal' => $ocrTanggal,
                'ocr_reference' => $ocrReference,
                'fraud_score' => $fraudScore,
                'fraud_flags' => $fraudFlags,
            ],
        ]);
    }

    public function dscOmsetSaveFinal(Request $r)
    {
        $v = Validator::make($r->all(), [
            'outlet_id' => 'required|integer',
            'tanggal'   => 'required|date_format:Y-m-d',
            'pic'       => 'required|string|max:120',
    
            // BATAS UKURAN: 2MB lebih aman untuk HP (2048 = 2MB)
            'bukti_foto_s1' => 'required|image|mimes:jpg,jpeg,png,webp|max:10048',
            'bukti_foto_s2' => 'required|image|mimes:jpg,jpeg,png,webp|max:10048',
        ]);
    
        if ($v->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal',
                'errors' => $v->errors()
            ], 422);
        }
    
        $outletId = (int) $r->outlet_id;
        $tanggal  = (string) $r->tanggal;
        $pic      = trim((string) $r->pic);
    
        $existing = DB::table('tbl_dsc_omset_setoran')
            ->where('outlet_id', $outletId)
            ->where('tanggal', $tanggal)
            ->first();
    
        // ambil path lama (support 1 kolom / 2 kolom)
        $oldS1 = $existing->bukti_foto_s1 ?? null;
        $oldS2 = $existing->bukti_foto_s2 ?? null;
    
        if ($oldS1 === null && $oldS2 === null) {
            $oldS1 = $existing->bukti_foto ?? null;
            $oldS2 = $existing->bukti_foto ?? null;
        }
    
        $pathS1 = $oldS1;
        $pathS2 = $oldS2;
    
        // ✅ SIMPAN FILE CUMA SEKALI PER SHIFT (PAKAI 1 METODE)
        if ($r->hasFile('bukti_foto_s1')) {
            $this->deletePublicOmsetPhotoIfExists($pathS1);
            $pathS1 = $this->storeOmsetPhotoToPublic($r->file('bukti_foto_s1'), $outletId, $tanggal);
        }
    
        if ($r->hasFile('bukti_foto_s2')) {
            $this->deletePublicOmsetPhotoIfExists($pathS2);
            $pathS2 = $this->storeOmsetPhotoToPublic($r->file('bukti_foto_s2'), $outletId, $tanggal);
        }
    
        $n = fn ($v) => (float) ($v ?? 0);
    
        $update = [
            's1_total_transaction' => $n(data_get($r->all(), 'omset.s1.total_transaction')),
            's1_diskon'            => $n(data_get($r->all(), 'omset.s1.diskon')),
            's1_non_tunai'         => $n(data_get($r->all(), 'omset.s1.non_tunai')),
            's1_expense'           => $n(data_get($r->all(), 'omset.s1.expense')),
            's1_uang_fisik'        => $n(data_get($r->all(), 'omset.s1.uang_fisik')),
    
            's2_total_transaction' => $n(data_get($r->all(), 'omset.s2.total_transaction')),
            's2_diskon'            => $n(data_get($r->all(), 'omset.s2.diskon')),
            's2_non_tunai'         => $n(data_get($r->all(), 'omset.s2.non_tunai')),
            's2_expense'           => $n(data_get($r->all(), 'omset.s2.expense')),
            's2_uang_fisik'        => $n(data_get($r->all(), 'omset.s2.uang_fisik')),
    
            's1_hanya_selisih_minus' => $n(data_get($r->all(), 'setoran.s1.hanya_selisih')),
            's1_tanggal_setor'       => data_get($r->all(), 'setoran.s1.tanggal_setor') ?: null,
            's1_sudah_disetor'       => (int) $n(data_get($r->all(), 'setoran.s1.sudah_setor')),
            's1_admin_pot_sales'     => $n(data_get($r->all(), 'setoran.s1.admin')),
            's1_adjustment'          => $n(data_get($r->all(), 'setoran.s1.adjustment')),
    
            's2_hanya_selisih_minus' => $n(data_get($r->all(), 'setoran.s2.hanya_selisih')),
            's2_tanggal_setor'       => data_get($r->all(), 'setoran.s2.tanggal_setor') ?: null,
            's2_sudah_disetor'       => (int) $n(data_get($r->all(), 'setoran.s2.sudah_setor')),
            's2_admin_pot_sales'     => $n(data_get($r->all(), 'setoran.s2.admin')),
            's2_adjustment'          => $n(data_get($r->all(), 'setoran.s2.adjustment')),
    
            'akumulasi_selisih'      => $n(data_get($r->all(), 'extra.akumulasi_selisih')),
            'kekurangan_bulan_lalu'  => $n(data_get($r->all(), 'extra.kekurangan_bulan_lalu')),
    
            'pic'       => $pic,
            'updated_at'=> now(),
            'created_at'=> DB::raw('COALESCE(created_at, NOW())'),
        ];
    
        if (Schema::hasColumn('tbl_dsc_omset_setoran', 'bukti_foto_s1')) {
            $update['bukti_foto_s1'] = $pathS1;
        }
        if (Schema::hasColumn('tbl_dsc_omset_setoran', 'bukti_foto_s2')) {
            $update['bukti_foto_s2'] = $pathS2;
        }
    
        if (!Schema::hasColumn('tbl_dsc_omset_setoran', 'bukti_foto_s1')
            && !Schema::hasColumn('tbl_dsc_omset_setoran', 'bukti_foto_s2')) {
            $update['bukti_foto'] = $pathS2 ?: $pathS1;
        }
    
        DB::table('tbl_dsc_omset_setoran')->updateOrInsert(
            ['outlet_id' => $outletId, 'tanggal' => $tanggal],
            $update
        );
    
        $urlS1 = $this->publicOmsetPhotoUrl($pathS1);
        $urlS2 = $this->publicOmsetPhotoUrl($pathS2);
    
        return response()->json([
            'ok' => true,
            'message' => 'Omset/Setoran tersimpan',
            'data' => [
                'setoran' => [
                    's1' => ['bukti_foto' => $pathS1, 'bukti_url' => $urlS1],
                    's2' => ['bukti_foto' => $pathS2, 'bukti_url' => $urlS2],
                ],
            ],
        ]);
    }

    private function getLastEndingBeforeDate(int $outletId, int $bahanId, string $todayYmd): float
    {
        // ambil ending terakhir < today
        // prefer shift 2 dulu kalau di hari yg sama ada beberapa shift
        $row = DB::table('tbl_stock')
            ->where('outlet_id', $outletId)
            ->where('bahan_id', $bahanId)
            ->whereDate('tanggal', '<', $todayYmd)
            ->orderBy('tanggal', 'desc')
            ->orderBy('shift', 'desc')   // prefer shift 2
            ->orderBy('id', 'desc')      // latest row
            ->first();

        return (float) ($row->ending_stock ?? 0);
    }

    private function getShift1EndingSameDay(int $outletId, int $bahanId, string $todayYmd): ?float
    {
        $row = DB::table('tbl_stock')
            ->where('outlet_id', $outletId)
            ->where('bahan_id', $bahanId)
            ->whereDate('tanggal', $todayYmd)
            ->where('shift', 1)
            ->orderBy('id', 'desc')
            ->first();

        if (! $row) {
            return null;
        }

        return (float) ($row->ending_stock ?? 0);
    }

    private function resolveOpenProper(int $outletId, int $bahanId, string $todayYmd, int $shift): float
    {
        if ($shift === 2) {
            // shift 2 open = ending shift1 di hari yg sama (kalau ada)
            $s1Ending = $this->getShift1EndingSameDay($outletId, $bahanId, $todayYmd);
            if ($s1Ending !== null) {
                return $s1Ending;
            }

            // fallback = ending terakhir sebelum today
            return $this->getLastEndingBeforeDate($outletId, $bahanId, $todayYmd);
        }

        // shift 1 open = ending terakhir sebelum today
        return $this->getLastEndingBeforeDate($outletId, $bahanId, $todayYmd);
    }

    private function normTanggal($raw): string
    {
        $s = trim((string) $raw);
        if ($s === '') return date('Y-m-d');
    
        // sudah YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
    
        // dd/mm/yyyy
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $s)) {
            [$d,$m,$y] = explode('/', $s);
            return sprintf('%04d-%02d-%02d', (int)$y, (int)$m, (int)$d);
        }
    
        // fallback parse umum
        try {
            return \Carbon\Carbon::parse($s)->format('Y-m-d');
        } catch (\Throwable $e) {
            return date('Y-m-d');
        }
    }
    
    private function normalizeOutletName(?string $name): string
    {
        $name = strtoupper(trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name); // rapikan spasi ganda
        return $name ?? '';
    }
    
    /**
     * Ambil semua outlet, lalu group berdasarkan nama outlet yang sudah dinormalisasi.
     * Tujuannya:
     * - dropdown cuma tampil 1x per outlet kembar
     * - tetap simpan semua alias id untuk query data
     */
    private function getGroupedOutletsForUser(?int $userId = null): Collection
    {
        // PERFORMANCE FIX:
        // Method ini dipanggil dari banyak endpoint dan sebelumnya juga terpanggil
        // berkali-kali dari getOpeningStockUi(). Cache statis per request supaya query
        // tbl_outlets + proses group/sort tidak berulang ratusan kali.
        static $requestCache = [];

        $cacheKey = (string) ($userId ?? 'all');
        if (isset($requestCache[$cacheKey])) {
            return $requestCache[$cacheKey];
        }

        // Kalau nanti mau balikin role-based lagi, tinggal ganti query base di sini.
        $rows = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet', 'status')
            ->orderBy('nama_outlet')
            ->get();
    
        $grouped = $rows
            ->groupBy(function ($row) {
                return $this->normalizeOutletName($row->nama_outlet);
            })
            ->map(function ($group, $normalizedName) {
                // pilih 1 outlet utama untuk ditampilkan di dropdown
                // strategi: ambil ID terkecil
                $main = $group->sortBy('id')->first();
    
                return (object) [
                    'id' => (int) $main->id, // ID utama untuk dropdown
                    'nama_outlet' => (string) $main->nama_outlet,
                    'status' => (string) ($main->status ?? ''),
                    'normalized_name' => $normalizedName,
                    'alias_ids' => $group->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                    'all_rows' => $group->values(),
                ];
            })
            ->sortBy('nama_outlet', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $requestCache[$cacheKey] = $grouped;

        return $grouped;
    }
    
    /**
     * Cari semua alias outlet_id berdasarkan outlet yang dipilih.
     * Kalau outlet_id yang dikirim termasuk outlet duplicate, semua id kembar akan dikembalikan.
     */
    private function getOutletAliasIdsFromSelected(int $selectedOutletId, ?Collection $groupedOutlets = null): array
    {
        $groupedOutlets = $groupedOutlets ?: $this->getGroupedOutletsForUser(auth()->id());
    
        foreach ($groupedOutlets as $outlet) {
            if (in_array($selectedOutletId, $outlet->alias_ids, true)) {
                return $outlet->alias_ids;
            }
        }
    
        return [$selectedOutletId];
    }
    
    /**
     * Ambil ID utama/dropdown ID dari outlet terpilih.
     * Kalau user kirim outlet_id alias lama, kita map balik ke ID utama group.
     */
    private function getOutletDisplayIdFromSelected(int $selectedOutletId, ?Collection $groupedOutlets = null): int
    {
        $groupedOutlets = $groupedOutlets ?: $this->getGroupedOutletsForUser(auth()->id());
    
        foreach ($groupedOutlets as $outlet) {
            if (in_array($selectedOutletId, $outlet->alias_ids, true)) {
                return (int) $outlet->id;
            }
        }
    
        return $selectedOutletId;
    }
    
    public function dscFormulir(Request $request)
    {
        $today = $request->get('tanggal', date('Y-m-d'));
        $outletIdRaw = (int) $request->get('outlet_id', 0);
        $shift = (string) $request->get('shift', '1');

        $userId = auth()->id();

        $user = DB::table('users')
            ->select('id', 'role', 'outlet_id', 'name')
            ->where('id', $userId)
            ->first();

        $role = (string) ($user->role ?? '');

        /*
        |--------------------------------------------------------------------------
        | Ambil outlet grouped
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | getGroupedOutletsForUser() HARUS sudah memakai grouping nama outlet
        | normalisasi supaya outlet duplicate / alias tetap jadi 1 group.
        |
        | Contoh:
        | 160 PASINAN BAURENO BJN
        | 201 PASINAN BAURENO BJN
        | 348 PASINAN BAURENO BJN
        |
        | => dianggap 1 outlet group
        |--------------------------------------------------------------------------
        */
        $outlets = $this->getGroupedOutletsForUser($userId);

        /*
        |--------------------------------------------------------------------------
        | Restrict khusus role crew
        |--------------------------------------------------------------------------
        | Crew hanya boleh akses outlet sesuai outlet_id miliknya.
        |
        | TAPI:
        | karena outlet bisa duplicate/alias,
        | maka kita cocokkan berdasarkan nama outlet normalisasi,
        | BUKAN hanya berdasarkan outlet_id tunggal.
        |--------------------------------------------------------------------------
        */
        if ($role === 'crew') {

            $crewOutletId = (int) ($user->outlet_id ?? 0);

            if ($crewOutletId <= 0) {
                abort(403, 'Akun crew belum disetting outlet.');
            }

            // outlet utama crew
            $assignedOutlet = DB::table('tbl_outlets')
                ->select('id', 'nama_outlet')
                ->where('id', $crewOutletId)
                ->first();

            if (!$assignedOutlet) {
                abort(403, 'Outlet crew tidak ditemukan.');
            }

            // normalisasi nama outlet
            $assignedOutletNameNorm = $this->normalizeOutletName(
                $assignedOutlet->nama_outlet
            );

            /*
            |--------------------------------------------------------------------------
            | Filter hanya outlet group yang sama
            |--------------------------------------------------------------------------
            | Jadi kalau outlet duplicate:
            |
            | 160 PASINAN BAURENO BJN
            | 201 PASINAN BAURENO BJN
            | 348 PASINAN BAURENO BJN
            |
            | maka crew tetap bisa akses semua alias outlet tersebut.
            |--------------------------------------------------------------------------
            */
            $outlets = $outlets
                ->filter(function ($outlet) use ($assignedOutletNameNorm) {

                    return $this->normalizeOutletName(
                        $outlet->nama_outlet ?? ''
                    ) === $assignedOutletNameNorm;

                })
                ->values();

            if ($outlets->isEmpty()) {
                abort(403, 'Outlet crew tidak cocok dengan master outlet.');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Default outlet
        |--------------------------------------------------------------------------
        */
        if ($outletIdRaw <= 0) {

            $outletId = $outlets->count()
                ? (int) $outlets->first()->id
                : 0;

        } else {

            /*
            |--------------------------------------------------------------------------
            | Mapping selected outlet ke display outlet utama
            |--------------------------------------------------------------------------
            | Aman untuk outlet duplicate / alias.
            |--------------------------------------------------------------------------
            */
            $outletId = $this->getOutletDisplayIdFromSelected(
                $outletIdRaw,
                $outlets
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi keamanan
        |--------------------------------------------------------------------------
        */
        $validIds = $outlets
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($outletId > 0 && !in_array($outletId, $validIds, true)) {

            $outletId = $outlets->count()
                ? (int) $outlets->first()->id
                : 0;
        }

        return view('Investor.Inventory.dscFormulir', [
            'today' => $today,
            'outletId' => $outletId ?: '',
            'shift' => $shift,
            'outlets' => $outlets,
        ]);
    }

    private function getCloseStatus(int $outletId, string $tanggal): array
    {
        $row = DB::table('tbl_dsc_kasir_close')
            ->where('outlet_id', $outletId)
            ->where('tanggal', $tanggal)
            ->first();

        return [
            'is_closed' => $row ? ((int) $row->is_closed === 1) : false,
            'closed_shift' => $row ? (int) ($row->closed_shift ?? 0) : 0,
            'closed_by' => $row ? (string) ($row->closed_by ?? '') : '',
            'closed_at' => $row ? (string) ($row->closed_at ?? '') : '',
            'note' => $row ? (string) ($row->note ?? '') : '',
        ];
    }

    private function setKasirClosed(int $outletId, string $tanggal, int $shift, string $pic, string $note = ''): void
    {
        DB::table('tbl_dsc_kasir_close')->updateOrInsert(
            ['outlet_id' => $outletId, 'tanggal' => $tanggal],
            [
                'is_closed' => 1,
                'closed_shift' => $shift,
                'closed_by' => $pic,
                'closed_at' => now(),
                'note' => $note,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function closeStatus(Request $r)
    {
        $outletId = (int) $r->query('outlet_id');
        $tanggal = (string) $r->query('tanggal');

        if (! $outletId || ! $tanggal) {
            return response()->json([
                'ok' => false,
                'message' => 'OUTLET_ID / TANGGAL WAJIB'
            ], 422);
        }

        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $displayOutletId = $this->getOutletDisplayIdFromSelected($outletId, $groupedOutlets);

        $st = $this->getCloseStatus($displayOutletId, $tanggal);

        return response()->json([
            'ok' => true,
            'data' => $st
        ]);
    }

    public function closeKasir(Request $r)
    {
        $r->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:2',
            'nama_petugas' => 'required|string|max:120',
            'note' => 'nullable|string|max:255',
        ]);

        $outletId = (int) $r->outlet_id;
        $tanggal = (string) $r->tanggal;
        $shift = (int) $r->shift;
        $pic = strtoupper(trim((string) $r->nama_petugas));
        $note = strtoupper((string) ($r->note ?? ''));

        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $displayOutletId = $this->getOutletDisplayIdFromSelected($outletId, $groupedOutlets);
        $aliasIds = $this->getOutletAliasIdsFromSelected($outletId, $groupedOutlets);
        if (empty($aliasIds)) {
            $aliasIds = [$displayOutletId];
        }

        $close = $this->getCloseStatus($displayOutletId, $tanggal);

        if ($close['is_closed']) {
            return response()->json([
                'ok' => true,
                'message' => 'KASIR SUDAH DITUTUP',
                'data' => ['lock' => $close]
            ]);
        }

        $this->setKasirClosed($displayOutletId, $tanggal, $shift, $pic, $note);

        return response()->json([
            'ok' => true,
            'message' => 'KASIR DITUTUP. DATA TERKUNCI.',
            'data' => ['lock' => $this->getCloseStatus($displayOutletId, $tanggal)],
        ]);
    }

    public function dscLoad(Request $r)
    {
        $r->validate([
            'outlet_id' => 'required|integer',
            'tanggal'   => 'required',
            'shift'     => 'required|in:1,2',
        ]);

        $selectedOutletId = (int) $r->query('outlet_id');
        $todayRaw = (string) $r->query('tanggal');
        $today = $this->normTanggal($todayRaw);
        $shift = (int) $r->query('shift');

        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $displayOutletId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);
        $aliasIds = $this->getOutletAliasIdsFromSelected($selectedOutletId, $groupedOutlets);

        $bahanList = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        // data untuk shift aktif yang sedang dibuka form-nya
        $finalRows = DB::table('tbl_stock')
            ->whereIn('outlet_id', $aliasIds)
            ->whereDate('tanggal', $today)
            ->where('shift', (string) $shift)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->unique('bahan_id')
            ->keyBy('bahan_id');

        $draftRows = DB::table('tbl_stock_draft')
            ->whereIn('outlet_id', $aliasIds)
            ->whereDate('tanggal', $today)
            ->where('shift', $shift)
            ->where('is_draft', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->unique('bahan_id')
            ->keyBy('bahan_id');

        // sumber opening shift 2 = ending shift 1
        // PRIORITAS: DRAFT TERBARU -> FINAL TERBARU
        $draftShift1Rows = DB::table('tbl_stock_draft')
            ->whereIn('outlet_id', $aliasIds)
            ->whereDate('tanggal', $today)
            ->where('shift', 1)
            ->where('is_draft', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->unique('bahan_id')
            ->keyBy('bahan_id');

        $finalShift1Rows = DB::table('tbl_stock')
            ->whereIn('outlet_id', $aliasIds)
            ->whereDate('tanggal', $today)
            ->where('shift', '1')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->unique('bahan_id')
            ->keyBy('bahan_id');

        $hasDraftS1 = $draftShift1Rows->isNotEmpty();
        $hasFinalS1 = $finalShift1Rows->isNotEmpty();

        $lock = $this->getCloseStatus($displayOutletId, $today);

        $items = [];

        foreach ($bahanList as $b) {
            // isi form shift aktif: draft menang atas final
            $sr = $draftRows[$b->id] ?? $finalRows[$b->id] ?? null;

            if ($shift === 2) {
                $s1Draft = $draftShift1Rows[$b->id] ?? null;
                $s1Final = $finalShift1Rows[$b->id] ?? null;

                if ($s1Draft && $s1Draft->ending_stock !== null) {
                    $open = (float) $s1Draft->ending_stock;
                } elseif ($s1Final && $s1Final->ending_stock !== null) {
                    $open = (float) $s1Final->ending_stock;
                } else {
                    $open = (float) $this->getOpeningStockUi($displayOutletId, (int) $b->id, $today, $shift);
                }
            } else {
                $open = (float) $this->getOpeningStockUi($displayOutletId, (int) $b->id, $today, $shift);
            }

            $items[] = [
                'id'            => (int) $b->id,
                'nama_bahan'    => (string) $b->nama_bahan,
                'satuan'        => (string) $b->satuan,
                'open'          => (float) $open,

                'purchase_in'   => (float) ($sr->purchase_in ?? 0),
                'mutasi_in'     => (float) ($sr->mutasi_in ?? 0),
                'mutasi_out'    => (float) ($sr->mutasi_out ?? 0),
                'adjustment_qty'=> (float) ($sr->adjustment_qty ?? 0),
                'ending_stock'  => $sr && $sr->ending_stock !== null ? (float) $sr->ending_stock : null,
                'waste_product' => (float) ($sr->waste_product ?? 0),
                'waste_bahan'   => (float) ($sr->waste_bahan ?? 0),
                'uang_plus'     => (float) ($sr->uang_plus ?? 0),
                'keterangan'    => (string) ($sr->keterangan ?? ''),
            ];
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'items' => $items,
                'lock'  => $lock,
                'meta'  => [
                    'tanggal_raw'    => $todayRaw,
                    'tanggal_norm'   => $today,
                    'outlet_id'      => $displayOutletId,
                    'outlet_aliases' => $aliasIds,
                    'shift'          => $shift,
                    'draft_count'    => $draftRows->count(),
                    'final_count'    => $finalRows->count(),
                    'has_draft_s1'   => $hasDraftS1 ? 1 : 0,
                    'has_final_s1'   => $hasFinalS1 ? 1 : 0,
                    'has_shift_1'    => ($hasDraftS1 || $hasFinalS1) ? 1 : 0,
                ],
            ],
        ]);
    }


    private function isDscCrewFilledLockActive(): bool
    {
        // LOCK CREW SEMENTARA DIMATIKAN.
        // Semua role, termasuk crew, boleh mengubah nilai DSC selama kasir belum close.
        // Kalau nanti lock ingin diaktifkan lagi, ubah return false menjadi:
        // return strtolower((string) (auth()->user()->role ?? '')) === 'crew';
        return false;
    }

    private function canEditFilledDscNominal(): bool
    {
        $role = strtolower((string) (auth()->user()->role ?? ''));

        return in_array($role, [
            'spv',
            'tm_manager',
            'superadmin',
            'superadmin_audit',
        ], true);
    }

    private function dscCrewLockFields(): array
    {
        return [
            'purchase_in' => 'Purchase In',
            'mutasi_in' => 'Mutasi In',
            'mutasi_out' => 'Mutasi Out',
            'adjustment_qty' => 'Adjustment',
            'ending_stock' => 'Ending Stock',
            'waste_product' => 'Waste Product',
            'waste_bahan' => 'Waste Bahan',
            'waste_tepung' => 'Waste Tepung',
            'uang_plus' => 'Uang Plus',
        ];
    }

    private function protectCrewFilledDscNominalValues($existingRow, array $inputRow, int $bahanId, bool $preserveInsteadOfReject = true): array
    {
        // LOCK CREW SEMENTARA DIMATIKAN.
        // Sebelumnya: crew tidak bisa mengubah kolom nominal yang sudah > 0.
        // Efeknya Shift 2 yang sudah pernah diisi, misal Purchase In 400, tidak bisa diubah jadi 450.
        // Sekarang: data yang dikirim dari frontend langsung dipakai agar Save Draft dan Final bisa update normal.
        return $inputRow;
    }

    private function rejectIfCrewChangesFilledDscNominal($existingRow, array $inputRow, int $bahanId): void
    {
        // LOCK CREW SEMENTARA DIMATIKAN.
        // Tidak melakukan reject apa pun.
        return;
    }

    public function dscSaveSO(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required',
            'shift' => 'required|in:1,2',
            'nama_petugas' => 'required|string|max:255',
            'rows' => 'required|array|min:1',
        ]);

        $outletId = (int) $request->outlet_id;
        $tanggal = $this->normTanggal($request->tanggal);
        $shift = (string) $request->shift;
        $pic = strtoupper(trim((string) $request->nama_petugas));

        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $displayOutletId = $this->getOutletDisplayIdFromSelected($outletId, $groupedOutlets);
        $aliasIds = $this->getOutletAliasIdsFromSelected($outletId, $groupedOutlets);
        if (empty($aliasIds)) {
            $aliasIds = [$displayOutletId];
        }

        $close = $this->getCloseStatus($displayOutletId, $tanggal);

        if (! empty($close['is_closed'])) {
            return response()->json([
                'ok' => false,
                'message' => 'KASIR SUDAH DITUTUP. DATA TERKUNCI.'
            ], 423);
        }

        if ((int) $shift === 2) {
            $hasShift1Draft = DB::table('tbl_stock_draft')
                ->whereIn('outlet_id', $aliasIds)
                ->whereDate('tanggal', $tanggal)
                ->where('shift', 1)
                ->where('is_draft', 1)
                ->exists();

            $hasShift1Final = DB::table('tbl_stock')
                ->whereIn('outlet_id', $aliasIds)
                ->whereDate('tanggal', $tanggal)
                ->where('shift', '1')
                ->exists();

            if (! $hasShift1Draft && ! $hasShift1Final) {
                return response()->json([
                    'ok' => false,
                    'message' => 'FINAL SHIFT 2 DITOLAK. SHIFT 1 BELUM ADA / BELUM DISIMPAN.'
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
        // Simpan final shift aktif saja
        $this->upsertFinalStockRows(
            $displayOutletId,
            $tanggal,
            $shift,
            $pic,
            $request->rows,
            0,
            0,
            0
        );

        // Hapus draft hanya untuk shift yang difinalkan
        $bahanIds = collect($request->rows)
            ->pluck('bahan_id')
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        DB::table('tbl_stock_draft')
            ->where('outlet_id', $displayOutletId)
            ->whereDate('tanggal', $tanggal)
            ->where('shift', (int) $shift)
            ->whereIn('bahan_id', $bahanIds)
            ->delete();

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'SO TERSIMPAN'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'GAGAL SIMPAN SO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function upsertFinalStockRows(
        int $outletId,
        string $tanggal,
        string $shift,
        string $pic,
        array $rows,
        float $salesS1,
        float $salesS2,
        ?float $cuForShift
    ): void {
        $bahanIds = collect($rows)
            ->pluck('bahan_id')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();

        $bahanMap = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->whereIn('id', $bahanIds)
            ->get()
            ->keyBy('id');

        foreach ($rows as $r) {
            $bahanId = (int) ($r['bahan_id'] ?? 0);
            if (! $bahanId) {
                continue;
            }

            $bahan = $bahanMap->get($bahanId);
            if (! $bahan) {
                continue;
            }

            $purchaseIn = (float) ($r['purchase_in'] ?? 0);
            $mutasiIn = (float) ($r['mutasi_in'] ?? 0);
            $mutasiOut = (float) ($r['mutasi_out'] ?? 0);
            $adjQty = (float) ($r['adjustment_qty'] ?? 0);

            $endingRaw = $r['ending_stock'] ?? null;
            $ending = ($endingRaw === null || $endingRaw === '') ? null : (float) $endingRaw;

            $wProd = (float) ($r['waste_product'] ?? 0);
            $wBahan = (float) ($r['waste_bahan'] ?? 0);
            $uangPlus = (float) ($r['uang_plus'] ?? 0);
            $ket = (string) ($r['keterangan'] ?? '');

            $existing = DB::table('tbl_stock')
                ->where('outlet_id', $outletId)
                ->where('tanggal', $tanggal)
                ->where('shift', $shift)
                ->where('bahan_id', $bahanId)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            $lockBase = $existing ?: DB::table('tbl_stock_draft')
                ->where('outlet_id', $outletId)
                ->whereDate('tanggal', $tanggal)
                ->where('shift', (int) $shift)
                ->where('bahan_id', $bahanId)
                ->where('is_draft', 1)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();

            $r = $this->protectCrewFilledDscNominalValues($lockBase, $r, $bahanId, true);

            $purchaseIn = (float) ($r['purchase_in'] ?? 0);
            $mutasiIn = (float) ($r['mutasi_in'] ?? 0);
            $mutasiOut = (float) ($r['mutasi_out'] ?? 0);
            $adjQty = (float) ($r['adjustment_qty'] ?? 0);

            $endingRaw = $r['ending_stock'] ?? null;
            $ending = ($endingRaw === null || $endingRaw === '') ? null : (float) $endingRaw;

            $wProd = (float) ($r['waste_product'] ?? 0);
            $wBahan = (float) ($r['waste_bahan'] ?? 0);
            $wTepungInput = (float) ($r['waste_tepung'] ?? 0);
            $uangPlus = (float) ($r['uang_plus'] ?? 0);
            $ket = (string) ($r['keterangan'] ?? '');

            $opening = $existing
                ? (float) ($existing->opening_stock ?? 0)
                : (float) $this->getOpeningStock($outletId, $bahanId, $tanggal, (int) $shift);

            if (! $existing && $ending === null) {
                $ending = $opening;
            }

            $wTepung = $wTepungInput;
            $wasteTotal = $wProd + $wBahan + $wTepung;
            $total = $opening + $purchaseIn + $mutasiIn - $mutasiOut + $adjQty;
            $used = $total - (float) ($ending ?? 0);

            $namaNorm = strtolower(trim((string) $bahan->nama_bahan));
            $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wasteTotal) : 0.0;

            $payload = [
                'satuan' => (string) $bahan->satuan,
                'opening_stock' => $opening,

                'purchase_in' => $purchaseIn,
                'mutasi_in' => $mutasiIn,
                'mutasi_out' => $mutasiOut,
                'adjustment_qty' => $adjQty,

                'used_qty' => $used,

                'waste_product' => $wProd,
                'waste_bahan' => $wBahan,
                'waste_tepung' => $wTepung,

                'ending_stock' => (float) ($ending ?? 0),
                'actual_tepung' => $actualTepung,

                'uang_plus' => $uangPlus,
                'keterangan' => $ket,
                'nama_petugas' => $pic,

                'customer_unit' => (float) ($cuForShift ?? 0),
                'shift_1' => $salesS1,
                'shift_2' => $salesS2,

                'updated_at' => now(),
            ];

            $this->auditDscStockChanges(
                'tbl_stock',
                $existing ? 'FINAL_UPDATE' : 'FINAL_CREATE',
                $outletId,
                $tanggal,
                (int) $shift,
                $bahanId,
                $existing,
                $payload,
                $pic,
                'final'
            );

            if ($existing) {
                DB::table('tbl_stock')
                    ->where('id', $existing->id)
                    ->update($payload);

                DB::table('tbl_stock')
                    ->where('outlet_id', $outletId)
                    ->where('tanggal', $tanggal)
                    ->where('shift', $shift)
                    ->where('bahan_id', $bahanId)
                    ->where('id', '!=', $existing->id)
                    ->delete();
            } else {
                DB::table('tbl_stock')->insert(array_merge([
                    'outlet_id' => $outletId,
                    'tanggal' => $tanggal,
                    'shift' => $shift,
                    'bahan_id' => $bahanId,
                    'created_at' => now(),
                ], $payload));
            }
        }
    }

    private function numOrZero($val): float
    {
        if ($val === null || $val === '') {
            return 0.0;
        }

        if (is_string($val) && strtolower(trim($val)) === 'null') {
            return 0.0;
        }

        if (! is_numeric($val)) {
            return 0.0;
        }

        return (float) $val;
    }

    public function dscSaveDraft(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required',
            'shift' => 'required|in:1,2',
            'nama_petugas' => 'required|string|max:120',
            'rows' => 'required|array',
        ]);

        $selectedOutletId = (int) $request->outlet_id;
        $tanggal = $this->normTanggal($request->tanggal);
        $shift = (int) $request->shift;
        $pic = strtoupper(trim((string) $request->nama_petugas));

        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $displayOutletId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);

        $close = $this->getCloseStatus($displayOutletId, $tanggal);

        if ($close['is_closed']) {
            return response()->json([
                'ok' => false,
                'message' => 'KASIR SUDAH DITUTUP. DATA TERKUNCI.'
            ], 423);
        }

        DB::beginTransaction();

        try {
            $bahanIds = collect($request->rows)
                ->pluck('bahan_id')
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            $bahanMap = DB::table('tbl_bahan_dsc')
                ->select('id', 'satuan')
                ->whereIn('id', $bahanIds)
                ->get()
                ->keyBy('id');

            foreach ($request->rows as $r) {
                $bahanId = (int) ($r['bahan_id'] ?? 0);

                if ($bahanId <= 0) {
                    continue;
                }

                $existing = DB::table('tbl_stock_draft')
                    ->where('outlet_id', $displayOutletId)
                    ->where('tanggal', $tanggal)
                    ->where('shift', $shift)
                    ->where('bahan_id', $bahanId)
                    ->where('is_draft', 1)
                    ->lockForUpdate()
                    ->first();

                $lockBase = $existing ?: DB::table('tbl_stock')
                    ->where('outlet_id', $displayOutletId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', (string) $shift)
                    ->where('bahan_id', $bahanId)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->first();

                $r = $this->protectCrewFilledDscNominalValues($lockBase, $r, $bahanId, true);

                $opening = $existing && $existing->opening_stock !== null
                    ? (float) $existing->opening_stock
                    : (float) $this->getOpeningStockUi($displayOutletId, $bahanId, $tanggal, $shift);

                $purchaseIn = (float) ($r['purchase_in'] ?? 0);
                $mutasiIn = (float) ($r['mutasi_in'] ?? 0);
                $mutasiOut = (float) ($r['mutasi_out'] ?? 0);
                $adjQty = (float) ($r['adjustment_qty'] ?? 0);
                $ending = (float) ($r['ending_stock'] ?? 0);
                $wProd = (float) ($r['waste_product'] ?? 0);
                $wBahan = (float) ($r['waste_bahan'] ?? 0);
                $wTepung = (float) ($r['waste_tepung'] ?? 0);
                $uangPlus = (float) ($r['uang_plus'] ?? 0);
                $ket = strtoupper((string) ($r['keterangan'] ?? ''));

                $total = $opening + $purchaseIn + $mutasiIn - $mutasiOut + $adjQty;
                $usedQty = $total - $ending;

                $payload = [
                    'purchase_in' => $purchaseIn,
                    'mutasi_in' => $mutasiIn,
                    'mutasi_out' => $mutasiOut,
                    'adjustment_qty' => $adjQty,
                    'used_qty' => $usedQty,
                    'ending_stock' => $ending,
                    'actual_tepung' => (float) ($r['actual_tepung'] ?? 0),
                    'waste_product' => $wProd,
                    'waste_bahan' => $wBahan,
                    'waste_tepung' => $wTepung,
                    'uang_plus' => $uangPlus,
                    'customer_unit' => (float) ($r['customer_unit'] ?? 0),
                    'shift_1' => (float) ($r['shift_1'] ?? 0),
                    'shift_2' => (float) ($r['shift_2'] ?? 0),
                    'keterangan' => $ket,
                    'pic' => $pic,
                    'is_draft' => 1,
                    'satuan' => (string) ($bahanMap[$bahanId]->satuan ?? ($r['satuan'] ?? '')),
                    'opening_stock' => $opening,
                    'updated_at' => now(),
                ];

                $this->auditDscStockChanges(
                    'tbl_stock_draft',
                    $existing ? 'DRAFT_UPDATE' : 'DRAFT_CREATE',
                    $displayOutletId,
                    $tanggal,
                    $shift,
                    $bahanId,
                    $existing,
                    $payload,
                    $pic,
                    'draft'
                );

                if ($existing) {
                    DB::table('tbl_stock_draft')
                        ->where('id', $existing->id)
                        ->update($payload);
                } else {
                    DB::table('tbl_stock_draft')->insert(array_merge([
                        'outlet_id' => $displayOutletId,
                        'tanggal' => $tanggal,
                        'shift' => $shift,
                        'bahan_id' => $bahanId,
                        'created_at' => now(),
                    ], $payload));
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'DRAFT TERSIMPAN.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'DRAFT GAGAL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint JSON untuk modal History DSC.
     * Dipanggil oleh dailyStockControl.blade.php via AJAX.
     *
     * FIX:
     * - Support date_from/date_to dari modal.
     * - Filter outlet mengikuti alias/group outlet yang sama dengan Save Draft.
     * - Return created_at dan jam sekaligus, supaya kolom Jam tidak kosong.
     */
    public function dscHistory(Request $request)
    {
        $request->validate([
            'outlet_id' => 'nullable',
            'tanggal' => 'nullable|string',
            'date_from' => 'nullable|string',
            'date_to' => 'nullable|string',
            'shift' => 'nullable|in:1,2',
            'bahan_id' => 'nullable|integer',
            'q' => 'nullable|string|max:120',
            'source' => 'nullable|string|max:30',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        $tanggal = $request->filled('tanggal')
            ? $this->normTanggal((string) $request->tanggal)
            : null;

        $dateFrom = $request->filled('date_from')
            ? $this->normTanggal((string) $request->date_from)
            : null;

        $dateTo = $request->filled('date_to')
            ? $this->normTanggal((string) $request->date_to)
            : null;

        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        $limit = (int) ($request->limit ?: 500);
        $search = trim((string) $request->get('q', ''));

        $outletIds = [];
        $outletRaw = (string) $request->get('outlet_id', '');

        if ($outletRaw !== '' && $outletRaw !== 'all') {
            if (is_numeric($outletRaw)) {
                $selectedOutletId = (int) $outletRaw;
                $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
                $aliasIds = $this->getOutletAliasIdsFromSelected($selectedOutletId, $groupedOutlets);
                $outletIds = collect($aliasIds)->map(fn ($id) => (int) $id)->filter()->values()->all();

                if (empty($outletIds)) {
                    $displayId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);
                    $outletIds = $displayId ? [(int) $displayId] : [$selectedOutletId];
                }
            } elseif (str_starts_with($outletRaw, 'group_')) {
                $hash = substr($outletRaw, 6);
                $normalize = function ($name) {
                    $name = strtoupper(trim((string) $name));
                    return preg_replace('/\s+/', ' ', $name);
                };

                $allOutlets = DB::table('tbl_outlets')->select('id', 'nama_outlet')->get();
                foreach ($allOutlets->groupBy(fn ($o) => $normalize($o->nama_outlet)) as $name => $rows) {
                    if (md5($name) === $hash) {
                        $outletIds = $rows->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                        break;
                    }
                }
            }
        }

        $query = DB::table('tbl_dsc_stock_history as h')
            ->leftJoin('tbl_bahan_dsc as b', 'b.id', '=', 'h.bahan_id')
            ->leftJoin('tbl_outlets as o', 'o.id', '=', 'h.outlet_id')
            ->select(
                'h.id', 'h.outlet_id', 'o.nama_outlet', 'h.tanggal', 'h.shift',
                'h.bahan_id', 'b.nama_bahan', 'b.satuan', 'h.table_name', 'h.action',
                'h.field_name', 'h.old_value', 'h.new_value', 'h.pic', 'h.user_id',
                'h.user_name', 'h.source', 'h.ip_address', 'h.user_agent', 'h.created_at'
            )
            ->when(! empty($outletIds), fn ($q) => $q->whereIn('h.outlet_id', $outletIds))
            ->when($dateFrom || $dateTo, function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom && $dateTo) {
                    $q->whereBetween(DB::raw('DATE(h.tanggal)'), [$dateFrom, $dateTo]);
                } elseif ($dateFrom) {
                    $q->whereDate('h.tanggal', '>=', $dateFrom);
                } elseif ($dateTo) {
                    $q->whereDate('h.tanggal', '<=', $dateTo);
                }
            })
            ->when(! $dateFrom && ! $dateTo && $tanggal, fn ($q) => $q->whereDate('h.tanggal', $tanggal))
            ->when($request->filled('shift'), fn ($q) => $q->where('h.shift', (int) $request->shift))
            ->when($request->filled('bahan_id'), fn ($q) => $q->where('h.bahan_id', (int) $request->bahan_id))
            ->when($request->filled('source') && $request->source !== 'all', fn ($q) => $q->where('h.source', $request->source))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where(function ($w) use ($like) {
                    $w->where('b.nama_bahan', 'like', $like)
                        ->orWhere('o.nama_outlet', 'like', $like)
                        ->orWhere('h.field_name', 'like', $like)
                        ->orWhere('h.old_value', 'like', $like)
                        ->orWhere('h.new_value', 'like', $like)
                        ->orWhere('h.pic', 'like', $like)
                        ->orWhere('h.user_name', 'like', $like)
                        ->orWhere('h.ip_address', 'like', $like)
                        ->orWhere('h.user_agent', 'like', $like);
                });
            })
            ->orderByDesc('h.created_at')
            ->orderByDesc('h.id')
            ->limit($limit);

        $rows = $query->get()->map(function ($row) {
            $createdAt = $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : null;

            return [
                'id' => (int) $row->id,
                'jam' => $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i:s') : '-',
                'created_at' => $createdAt,
                'tanggal' => $row->tanggal,
                'shift' => (int) $row->shift,
                'outlet_id' => (int) $row->outlet_id,
                'nama_outlet' => $row->nama_outlet ?: ('Outlet #' . $row->outlet_id),
                'bahan_id' => (int) $row->bahan_id,
                'nama_bahan' => $row->nama_bahan ?: ('Bahan #' . $row->bahan_id),
                'satuan' => $row->satuan,
                'table_name' => $row->table_name,
                'action' => $row->action,
                'source' => $row->source,
                'field_name' => $row->field_name,
                'old_value' => $row->old_value,
                'new_value' => $row->new_value,
                'pic' => $row->pic,
                'user_id' => $row->user_id,
                'user_name' => $row->user_name,
                'ip_address' => $row->ip_address,
                'user_agent' => $row->user_agent,
                'device' => $this->detectDeviceLabel((string) $row->user_agent),
            ];
        });

        return response()->json([
            'ok' => true,
            'data' => $rows,
            'meta' => [
                'count' => $rows->count(),
                'limit' => $limit,
                'filter' => [
                    'outlet_id' => $request->outlet_id,
                    'outlet_ids' => $outletIds,
                    'tanggal' => $tanggal,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'shift' => $request->shift,
                    'bahan_id' => $request->bahan_id,
                    'source' => $request->source,
                    'q' => $search,
                ],
            ],
        ]);
    }

    /**
     * Audit semua perubahan stock draft/final.
     * Isi kolom IP, device, akun login, dan petugas supaya investigasi bisa jelas.
     */
    private function auditDscStockChanges(
        string $tableName,
        string $action,
        int $outletId,
        string $tanggal,
        int $shift,
        int $bahanId,
        $oldRow,
        array $newPayload,
        string $pic,
        string $source
    ): void {
        $fieldLabels = [
            'opening_stock' => 'Opening Stock',
            'purchase_in' => 'Purchase In',
            'mutasi_in' => 'Mutasi In',
            'mutasi_out' => 'Mutasi Out',
            'adjustment_qty' => 'Adjustment',
            'used_qty' => 'Used Qty',
            'waste_product' => 'Waste Product',
            'waste_bahan' => 'Waste Bahan',
            'waste_tepung' => 'Waste Tepung',
            'ending_stock' => 'Ending Stock / AB',
            'actual_tepung' => 'Actual Tepung',
            'uang_plus' => 'Uang Plus',
            'customer_unit' => 'Customer Unit',
            'shift_1' => 'Sales Shift 1',
            'shift_2' => 'Sales Shift 2',
            'keterangan' => 'Keterangan',
            'nama_petugas' => 'Nama Petugas',
            'pic' => 'PIC / Petugas',
            'is_draft' => 'Status Draft',
            'satuan' => 'Satuan',
        ];

        $ignore = ['created_at', 'updated_at', 'used_qty', 'actual_tepung'];
        $now = now();
        $rows = [];

        foreach ($newPayload as $field => $newValue) {
            if (in_array($field, $ignore, true) || ! array_key_exists($field, $fieldLabels)) {
                continue;
            }

            $oldValue = $oldRow ? ($oldRow->{$field} ?? null) : null;

            if ($this->normalizeHistoryValue($oldValue) === $this->normalizeHistoryValue($newValue)) {
                continue;
            }

            $rows[] = [
                'outlet_id' => $outletId,
                'tanggal' => $tanggal,
                'shift' => $shift,
                'bahan_id' => $bahanId,
                'table_name' => $tableName,
                'action' => $action,
                'field_name' => $fieldLabels[$field],
                'old_value' => $oldRow ? $this->historyValueToString($oldValue) : '',
                'new_value' => $this->historyValueToString($newValue),
                'pic' => $pic,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? auth()->user()->email ?? null,
                'source' => $source,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 1000),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($rows)) {
            DB::table('tbl_dsc_stock_history')->insert($rows);
        }
    }

    private function normalizeHistoryValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_numeric($value)) {
            return rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.');
        }

        return trim((string) $value);
    }

    private function historyValueToString($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_numeric($value)) {
            return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
        }

        return (string) $value;
    }

    private function detectDeviceLabel(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        if ($ua === '') {
            return 'Unknown Device';
        }

        $os = 'Device';
        if (str_contains($ua, 'android')) {
            $os = 'Android';
        } elseif (str_contains($ua, 'iphone')) {
            $os = 'iPhone';
        } elseif (str_contains($ua, 'ipad')) {
            $os = 'iPad';
        } elseif (str_contains($ua, 'windows')) {
            $os = 'Windows';
        } elseif (str_contains($ua, 'macintosh') || str_contains($ua, 'mac os')) {
            $os = 'Mac';
        } elseif (str_contains($ua, 'linux')) {
            $os = 'Linux';
        }

        $browser = 'Browser';
        if (str_contains($ua, 'edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) {
            $browser = 'Opera';
        } elseif (str_contains($ua, 'chrome/') && ! str_contains($ua, 'edg/')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome/')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'firefox/')) {
            $browser = 'Firefox';
        }

        return trim($os . ' ' . $browser);
    }

    public function dscOmsetLoadHarian(Request $r)
    {
        $r->validate([
            'outlet_id' => 'required|integer',
            'tanggal'   => 'required|date_format:Y-m-d',
        ]);
    
        $selectedOutletId = (int) $r->query('outlet_id');
        $tanggal = (string) $r->query('tanggal');
    
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $displayOutletId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);
        $aliasIds = $this->getOutletAliasIdsFromSelected($selectedOutletId, $groupedOutlets);
    
        $row = DB::table('tbl_dsc_omset_setoran')
            ->whereIn('outlet_id', $aliasIds)
            ->whereDate('tanggal', $tanggal)
            ->orderByDesc('id')
            ->first();
    
        $buktiUrl = null;
        if ($row && !empty($row->bukti_foto)) {
            $buktiUrl = $this->publicOmsetPhotoUrl($row->bukti_foto);
        }
    
        $n = fn ($v) => (float) ($v ?? 0);
    
        return response()->json([
            'ok' => true,
            'data' => [
                'outlet_id' => $displayOutletId,
                'outlet_aliases' => $aliasIds,
                'tanggal' => $tanggal,
                'pic' => (string) ($row->pic ?? ''),
                'bukti_foto' => (string) ($row->bukti_foto ?? ''),
                'bukti_url' => $buktiUrl,
                'omset' => [
                    's1' => [
                        'total_transaction' => $n($row->s1_total_transaction ?? 0),
                        'diskon' => $n($row->s1_diskon ?? 0),
                        'non_tunai' => $n($row->s1_non_tunai ?? 0),
                        'expense' => $n($row->s1_expense ?? 0),
                        'uang_fisik' => $n($row->s1_uang_fisik ?? 0),
                    ],
                    's2' => [
                        'total_transaction' => $n($row->s2_total_transaction ?? 0),
                        'diskon' => $n($row->s2_diskon ?? 0),
                        'non_tunai' => $n($row->s2_non_tunai ?? 0),
                        'expense' => $n($row->s2_expense ?? 0),
                        'uang_fisik' => $n($row->s2_uang_fisik ?? 0),
                    ],
                ],
                'setoran' => [
                    's1' => [
                        'hanya_selisih' => $n($row->s1_hanya_selisih_minus ?? 0),
                        'tanggal_setor' => $row->s1_tanggal_setor ?? null,
                        'sudah_setor' => (int) ($row->s1_sudah_disetor ?? 0),
                        'admin' => $n($row->s1_admin_pot_sales ?? 0),
                        'adjustment' => $n($row->s1_adjustment ?? 0),
                        'bukti_foto' => (string) ($row->bukti_foto ?? ''),
                        'bukti_url' => $buktiUrl,
                    ],
                    's2' => [
                        'hanya_selisih' => $n($row->s2_hanya_selisih_minus ?? 0),
                        'tanggal_setor' => $row->s2_tanggal_setor ?? null,
                        'sudah_setor' => (int) ($row->s2_sudah_disetor ?? 0),
                        'admin' => $n($row->s2_admin_pot_sales ?? 0),
                        'adjustment' => $n($row->s2_adjustment ?? 0),
                        'bukti_foto' => (string) ($row->bukti_foto ?? ''),
                        'bukti_url' => $buktiUrl,
                    ],
                ],
                'extra' => [
                    'akumulasi_selisih' => $n($row->akumulasi_selisih ?? 0),
                    'kekurangan_bulan_lalu' => $n($row->kekurangan_bulan_lalu ?? 0),
                ],
            ],
        ]);
    }

    private function numOrNull($val): ?float
    {
        if ($val === null || $val === '') {
            return null;
        }
        if (is_string($val) && strtolower(trim($val)) === 'null') {
            return null;
        }
        if (! is_numeric($val)) {
            return null;
        }

        return (float) $val;
    }

    private function getOpeningStockUi(int $outletId, int $bahanId, string $tanggal, int $shift): float
    {
        // PERFORMANCE FIX:
        // Satu request DSC/QCR bisa memanggil opening stock bahan yang sama beberapa kali
        // dari rekap + detail shift. Cache statis ini mencegah query DB yang identik.
        static $openingRequestCache = [];

        $cacheKey = $outletId . '|' . $bahanId . '|' . $tanggal . '|' . $shift;
        if (array_key_exists($cacheKey, $openingRequestCache)) {
            return (float) $openingRequestCache[$cacheKey];
        }

        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $aliasIds = $this->getOutletAliasIdsFromSelected($outletId, $groupedOutlets);

        $openingRequestCache[$cacheKey] = (float) $this->getOpeningFromAliasIds($aliasIds, $outletId, $bahanId, $tanggal, $shift);

        return (float) $openingRequestCache[$cacheKey];
    }

    private function getOpeningFromAliases(array $aliasIds, int $bahanId, string $tanggal, int $shift): float
    {
        return $this->getOpeningFromAliasIds($aliasIds, (int) ($aliasIds[0] ?? 0), $bahanId, $tanggal, $shift);
    }

    private function pickClosingFromAliasIds(array $aliasIds, int $bahanId, string $tanggal, ?int $shift = null): ?float
    {
        $aliasIds = array_values(array_unique(array_map('intval', $aliasIds)));
        $aliasIds = array_values(array_filter($aliasIds, fn ($id) => $id > 0));

        if (empty($aliasIds)) {
            return null;
        }

        $baseFinal = DB::table('tbl_stock')
            ->whereIn('outlet_id', $aliasIds)
            ->where('bahan_id', $bahanId)
            ->whereDate('tanggal', $tanggal);

        $baseDraft = DB::table('tbl_stock_draft')
            ->whereIn('outlet_id', $aliasIds)
            ->where('bahan_id', $bahanId)
            ->whereDate('tanggal', $tanggal)
            ->where('is_draft', 1);

        if ($shift !== null) {
            $baseFinal->where('shift', $shift);
            $baseDraft->where('shift', $shift);
        }

        /*
         * FIX OPENING STOCK:
         * Jika final sudah ada untuk tanggal + shift yang sama, final harus menang.
         * Draft hanya dipakai kalau data final belum ada. Ini mencegah draft lama
         * menimpa ending final dan membuat opening besok beda dari ending kemarin.
         */
        $final = $baseFinal
            ->whereNotNull('ending_stock')
            ->orderByDesc('shift')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($final && $final->ending_stock !== null) {
            return (float) $final->ending_stock;
        }

        $draft = $baseDraft
            ->whereNotNull('ending_stock')
            ->orderByDesc('shift')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($draft && $draft->ending_stock !== null) {
            return (float) $draft->ending_stock;
        }

        return null;
    }

    private function getOpeningFromAliasIds(array $aliasIds, int $displayOutletId, int $bahanId, string $tanggal, int $shift): float
    {
        $aliasIds = array_values(array_unique(array_map('intval', $aliasIds)));
        $aliasIds = array_values(array_filter($aliasIds, fn ($id) => $id > 0));

        if (empty($aliasIds) && $displayOutletId > 0) {
            $aliasIds = [(int) $displayOutletId];
        }

        if (empty($aliasIds)) {
            return 0.0;
        }

        if ($shift === 2) {
            $sameDayShift1 = $this->pickClosingFromAliasIds($aliasIds, $bahanId, $tanggal, 1);

            if ($sameDayShift1 !== null) {
                return (float) $sameDayShift1;
            }
        }

        /*
         * Opening Shift 1 tanggal aktif = ending fisik terakhir sebelum tanggal aktif.
         * Urutan benar:
         * 1. Cari tanggal terakhir yang punya final/draft.
         * 2. Pada tanggal itu pilih shift terbesar dulu (Shift 2 > Shift 1).
         * 3. Untuk tanggal + shift yang sama, FINAL menang dari DRAFT.
         *
         * Contoh kasus komplain:
         * 27 Mei ending Shift 2 final AYAM BESAR = 455.
         * Walaupun masih ada draft lama 559, opening 28 Mei tetap wajib 455.
         */
        $lastFinal = DB::table('tbl_stock')
            ->select('tanggal', 'shift')
            ->whereIn('outlet_id', $aliasIds)
            ->where('bahan_id', $bahanId)
            ->whereDate('tanggal', '<', $tanggal)
            ->whereNotNull('ending_stock');

        $lastDraft = DB::table('tbl_stock_draft')
            ->select('tanggal', 'shift')
            ->whereIn('outlet_id', $aliasIds)
            ->where('bahan_id', $bahanId)
            ->whereDate('tanggal', '<', $tanggal)
            ->where('is_draft', 1)
            ->whereNotNull('ending_stock');

        $lastClosing = DB::query()
            ->fromSub($lastFinal->unionAll($lastDraft), 'x')
            ->orderByDesc('tanggal')
            ->orderByDesc('shift')
            ->first();

        if (! $lastClosing) {
            return 0.0;
        }

        $lastDate = $this->normalizeDateToYmd((string) $lastClosing->tanggal);
        $lastShift = (int) $lastClosing->shift;

        $closing = $this->pickClosingFromAliasIds($aliasIds, $bahanId, $lastDate, $lastShift);

        return $closing !== null ? (float) $closing : 0.0;
    }

    private function getOpeningStock(int $outletId, int $bahanId, string $tanggal, int $shift): float
    {
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $aliasIds = $this->getOutletAliasIdsFromSelected($outletId, $groupedOutlets);

        return $this->getOpeningFromAliasIds($aliasIds, $outletId, $bahanId, $tanggal, $shift);
    }

    private function buildKeteranganSafe(string $ketRaw, string $pic): string
    {
        $ketRaw = trim($ketRaw);
        $picTag = 'PIC: '.trim($pic);

        // hindari dobel PIC kalau user sudah nulis
        $base = $ketRaw !== '' ? $ketRaw : '';
        if ($base === '') {
            $full = $picTag;
        } else {
            $full = $base.' | '.$picTag;
        }

        // pastikan max 255 (sesuai validasi & kolom)
        if (mb_strlen($full) > 255) {
            $full = mb_substr($full, 0, 255);
        }

        return $full;
    }

    private function upsertSalesShift(int $outletId, string $tanggal, int $shift, float $amount, string $pic): void
    {
        $existing = DB::table('tbl_sales_shift')
            ->where('outlet_id', $outletId)
            ->where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->first();

        $payload = [
            'sales_amount' => $amount,
            'source' => 'manual',
            'keterangan' => trim('PIC: '.$pic),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('tbl_sales_shift')->where('id', $existing->id)->update($payload);
        } else {
            DB::table('tbl_sales_shift')->insert(array_merge($payload, [
                'outlet_id' => $outletId,
                'tanggal' => $tanggal,
                'shift' => $shift,
                'created_at' => now(),
            ]));
        }
    }

    public function dscImportPreview(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:1,2',
            'mode' => 'required|in:merge,replace',
            // 'file'      => 'required|file|max:5120',
            'file' => 'required|file|max:5120|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        $bahanMaster = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->where('is_active', 1)
            ->get();

        $masterMap = [];
        foreach ($bahanMaster as $b) {
            $k = $this->normName($b->nama_bahan);
            $masterMap[$k] = [
                'id' => (int) $b->id,
                'nama_bahan' => (string) $b->nama_bahan,
                'satuan' => (string) $b->satuan,
            ];
        }

        try {
            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $rows = $this->readExcelRows($file->getPathname());
            } elseif ($ext === 'csv') {
                $rows = $this->readCsvRows($file->getPathname());
            } else {
                return response()->json([
                    'ok' => false,
                    'message' => 'Format file harus .xlsx / .xls / .csv',
                ], 422);
            }
        } catch (\Throwable $e) {
            \Log::error('DSC IMPORT PREVIEW ERROR', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'File Excel tidak bisa dibaca. Biasanya karena: merge cell, formula error, atau file corrupt. Silakan Save As ulang atau export ke CSV.',
            ], 422);
        }
        // cari header (scan max 20 baris)
        $headerRowIndex = null;
        $headerNorm = [];

        for ($ri = 0; $ri < min(50, count($rows)); $ri++) {
            $cand = $rows[$ri] ?? [];
            $candNorm = array_map(fn ($h) => $this->normHeader((string) $h), $cand);

            $hasNama = in_array('NAMA BARANG', $candNorm, true)
                    || in_array('NAMA', $candNorm, true)
                    || in_array('ITEM', $candNorm, true)
                    || in_array('ITEM NAME', $candNorm, true);

            if ($hasNama) {
                $headerRowIndex = $ri;
                $headerNorm = $candNorm;
                break;
            }
        }

        if ($headerRowIndex === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Header tidak ketemu. Pastikan ada kolom "NAMA BARANG" di salah satu dari 20 baris pertama.',
            ], 422);
        }

        $col = $this->mapColumnsFlexible($headerNorm);

        if ($col['nama_barang'] === null) {
            return response()->json([
                'ok' => false,
                'message' => 'Kolom NAMA BARANG tidak terdeteksi. Header: '.implode(' | ', $headerNorm),
            ], 422);
        }

        $startDataIndex = $headerRowIndex + 1;

        $items = [];
        $errors = [];
        $warnings = [];
        $seen = [];

        for ($i = $startDataIndex; $i < count($rows); $i++) {
            $r = $rows[$i];
            if ($this->isRowEmpty($r)) {
                continue;
            }

            $namaRaw = (string) $this->getCell($r, $col['nama_barang']);
            $namaNorm = $this->normName($namaRaw);
            if ($namaNorm === '') {
                continue;
            }

            if (! isset($masterMap[$namaNorm])) {
                $errors[] = [
                    'row' => $i + 1,
                    'nama' => $namaRaw,
                    'error' => 'Nama barang tidak ditemukan di master tbl_bahan_dsc',
                ];

                continue;
            }

            $m = $masterMap[$namaNorm];

            $satExcel = (string) $this->getCell($r, $col['sat']);
            if ($satExcel !== '' && $this->normHeader($satExcel) !== $this->normHeader($m['satuan'])) {
                $warnings[] = [
                    'row' => $i + 1,
                    'nama' => $m['nama_bahan'],
                    'warning' => "Satuan excel ($satExcel) beda dengan master ({$m['satuan']}). Dipakai master.",
                ];
            }

            $open = $this->num($this->getCell($r, $col['open_stock']));
            $pin = $this->num($this->getCell($r, $col['purchase_in']));
            $mi = $this->num($this->getCell($r, $col['mutasi_in']));
            $mo = $this->num($this->getCell($r, $col['mutasi_out']));
            $ending = $this->num($this->getCell($r, $col['ending_stock']));
            $wProd = $this->num($this->getCell($r, $col['waste_product']));
            $wBahan = $this->num($this->getCell($r, $col['waste_bahan']));
            $uang = $this->num($this->getCell($r, $col['uang_plus']));
            $ket = (string) $this->getCell($r, $col['keterangan']);

            $id = $m['id'];

            if (! isset($seen[$id])) {
                $items[$id] = [
                    'id' => $id,
                    'nama_bahan' => $m['nama_bahan'],
                    'satuan' => $m['satuan'],
                    'open' => $open,
                    'purchase_in' => $pin,
                    'mutasi_in' => $mi,
                    'mutasi_out' => $mo,
                    'ending_stock' => $ending,
                    'waste_product' => $wProd,
                    'waste_bahan' => $wBahan,
                    'uang_plus' => $uang,
                    'keterangan' => $ket,
                ];
                $seen[$id] = true;
            } else {
                $items[$id]['open'] += $open;
                $items[$id]['purchase_in'] += $pin;
                $items[$id]['mutasi_in'] += $mi;
                $items[$id]['mutasi_out'] += $mo;
                $items[$id]['ending_stock'] += $ending;
                $items[$id]['waste_product'] += $wProd;
                $items[$id]['waste_bahan'] += $wBahan;
                $items[$id]['uang_plus'] += $uang;

                if (trim($ket) !== '') {
                    $items[$id]['keterangan'] = trim($items[$id]['keterangan'].' | '.$ket);
                }
            }
        }

        return response()->json([
            'ok' => true,
            'data' => [
                'meta' => [
                    'outlet_id' => (int) $request->outlet_id,
                    'tanggal' => (string) $request->tanggal,
                    'shift' => (int) $request->shift,
                    'mode' => (string) $request->mode,
                    'rows_total' => max(0, count($rows) - 1),
                    'rows_valid' => count($items),
                ],
                'items' => array_values($items),
                'errors' => $errors,
                'warnings' => $warnings,
            ],
        ]);
    }

    /* =========================================================
     *  Helpers - Sales
     * ========================================================= */
    private function shiftWindow(string $shift): array
    {
        return ((string) $shift === '2') ? ['15:00:00', '23:59:59'] : ['00:00:00', '14:59:59'];
    }

    private function salesTotalByShift(int $outletId, string $tanggal): array
    {
        [$s1Start, $s1End] = $this->shiftWindow('1');
        [$s2Start, $s2End] = $this->shiftWindow('2');

        $base = DB::table('tbl_transaksi_perhari')
            ->where('outlet_id', $outletId)
            ->where('sesi_tanggal', $tanggal);

        $sales1 = (float) (clone $base)->whereBetween('tr_waktu', [$s1Start, $s1End])->sum('item_sub_total');
        $sales2 = (float) (clone $base)->whereBetween('tr_waktu', [$s2Start, $s2End])->sum('item_sub_total');

        return [$sales1, $sales2];
    }

    private function getClosingEffective(?object $row): float
    {
        if (! $row) {
            return 0.0;
        }

        return (float) ($row->ending_stock ?? 0) + (float) ($row->adjustment_qty ?? 0);
    }

    /* =========================================================
     *  Helpers - Import parsing
     * ========================================================= */
    private function mapColumnsFlexible(array $header): array
    {
        $findLike = function (array $h, array $aliases) {
            foreach ($h as $i => $name) {
                $n = strtoupper(trim((string) $name));
                foreach ($aliases as $a) {
                    if ($n === $a) {
                        return $i;
                    }
                    if (str_contains($n, $a)) {
                        return $i;
                    }
                }
            }

            return null;
        };

        return [
            'nama_barang' => $findLike($header, ['NAMA BARANG', 'NAMA', 'ITEM NAME', 'ITEM']),
            'sat' => $findLike($header, ['SAT', 'SATUAN']),
            'open_stock' => $findLike($header, ['OPEN STOCK', 'OPEN']),
            'purchase_in' => $findLike($header, ['PURCHASE IN', 'PURCHASE']),
            'mutasi_in' => $findLike($header, ['MUTASI IN', 'MUTASI_IN', 'MUTASI MASUK']),
            'mutasi_out' => $findLike($header, ['MUTASI OUT', 'MUTASI_OUT', 'MUTASI KELUAR']),
            'ending_stock' => $findLike($header, ['ENDING STOCK', 'ENDING']),
            'waste_product' => $findLike($header, ['WASTE PRODUCT', 'WASTE PROD']),
            'waste_bahan' => $findLike($header, ['WASTE BAHAN']),
            'uang_plus' => $findLike($header, ['UANG PLUS', 'UANG PLUS (RP)']),
            'keterangan' => $findLike($header, ['KETERANGAN', 'KET']),
        ];
    }

    private function readExcelRows(string $path): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true); // 🔑 penting
        $spreadsheet = $reader->load($path);

        $sheet = $spreadsheet->getActiveSheet();
        if (! $sheet) {
            throw new \RuntimeException('Sheet aktif tidak ditemukan.');
        }

        $arr = $sheet->toArray(null, true, true, false);
        if (! is_array($arr) || count($arr) === 0) {
            throw new \RuntimeException('Sheet kosong atau tidak bisa dibaca.');
        }

        return array_map(
            fn ($row) => array_map(fn ($c) => is_string($c) ? trim($c) : $c, $row),
            $arr
        );
    }

    private function readCsvRows(string $path): array
    {
        $rows = [];
        $fh = fopen($path, 'r');
        if (! $fh) {
            return [];
        }

        $first = fgets($fh);
        if ($first === false) {
            fclose($fh);

            return [];
        }

        $delim = (substr_count($first, ';') > substr_count($first, ',')) ? ';' : ',';
        rewind($fh);

        while (($data = fgetcsv($fh, 0, $delim)) !== false) {
            $rows[] = array_map(fn ($c) => is_string($c) ? trim($c) : $c, $data);
        }
        fclose($fh);

        return $rows;
    }

    private function isRowEmpty(array $r): bool
    {
        foreach ($r as $v) {
            if ($v === null) {
                continue;
            }
            if (is_numeric($v)) {
                return false;
            }
            if (trim((string) $v) !== '') {
                return false;
            }
        }

        return true;
    }

    private function getCell(array $row, ?int $idx)
    {
        if ($idx === null) {
            return '';
        }
        $v = $row[$idx] ?? '';

        return is_string($v) ? trim($v) : $v;
    }

    private function num($v): float
    {
        if ($v === null) {
            return 0.0;
        }

        // kalau PhpSpreadsheet sudah kasih numeric, jangan diapa-apain
        if (is_int($v) || is_float($v)) {
            return (float) $v;
        }

        $s = trim((string) $v);
        if ($s === '') {
            return 0.0;
        }

        // buang Rp, spasi, dll
        $s = str_ireplace(['rp', 'idr', ' '], '', $s);

        // kalau format ID: 1.234,56 => 1234.56
        // kalau cuma 50.000 => 50000
        // kalau cuma 123,45 => 123.45
        $hasDot = str_contains($s, '.');
        $hasComma = str_contains($s, ',');

        if ($hasDot && $hasComma) {
            // anggap '.' ribuan, ',' desimal
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);
        } elseif ($hasComma && ! $hasDot) {
            // anggap ',' desimal
            $s = str_replace(',', '.', $s);
        } else {
            // anggap '.' ribuan (atau tidak ada pemisah)
            $s = str_replace('.', '', $s);
        }

        // buang karakter selain angka dan titik minus
        $s = preg_replace('/[^0-9\.\-]/', '', $s);

        return is_numeric($s) ? (float) $s : 0.0;
    }

    private function normName($s): string
    {
        $s = strtolower(trim((string) $s));
        $s = preg_replace('/\s+/', ' ', $s);

        return $s;
    }

    private function normHeader(string $s): string
    {
        $s = trim($s);
        $s = preg_replace('/\s+/', ' ', $s);

        return strtoupper($s);
    }

    private function mapColumns(array $header): array
    {
        // map template columns, toleransi variasi
        $find = function (array $h, array $aliases) {
            foreach ($h as $i => $name) {
                foreach ($aliases as $a) {
                    if ($name === $a) {
                        return $i;
                    }
                }
            }

            return null;
        };

        return [
            'nama_barang' => $find($header, ['NAMA BARANG', 'NAMA']),
            'sat' => $find($header, ['SAT', 'SATUAN']),
            'open_stock' => $find($header, ['OPEN STOCK', 'OPEN']),
            'purchase_in' => $find($header, ['PURCHASE IN', 'PURCHASE']),
            'mutasi_in' => $find($header, ['MUTASI IN']),
            'mutasi_out' => $find($header, ['MUTASI OUT']),
            'ending_stock' => $find($header, ['ENDING STOCK', 'ENDING']),
            'waste_product' => $find($header, ['WASTE PRODUCT', 'WASTE PROD']),
            'waste_bahan' => $find($header, ['WASTE BAHAN']),
            'uang_plus' => $find($header, ['UANG PLUS', 'UANG PLUS (RP)']),
            'keterangan' => $find($header, ['KETERANGAN', 'KET']),
        ];
    }

    private function getSalesForDate(int $outletId, string $tanggal): array
    {
        $rows = DB::table('tbl_sales_shift')
            ->select('shift', 'sales_amount', 'source')
            ->where('outlet_id', $outletId)
            ->where('tanggal', $tanggal)
            ->whereIn('shift', [1, 2])
            ->get()
            ->keyBy('shift');

        $row1 = $rows->get(1);
        $row2 = $rows->get(2);

        $s1 = $row1 ? (float) ($row1->sales_amount ?? 0) : 0.0;
        $s2 = $row2 ? (float) ($row2->sales_amount ?? 0) : 0.0;

        return [
            'shift_1' => $s1,
            'shift_2' => $s2,
            'meta' => [
                'shift_1_source' => $row1 ? (string) ($row1->source ?? 'manual') : 'empty',
                'shift_2_source' => $row2 ? (string) ($row2->source ?? 'manual') : 'empty',
            ],
        ];
    }

    public function dscSalesUpsert(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:1,2',
            'sales_amount' => 'required|numeric',
            'nama_petugas' => 'required|string|max:120',
        ]);

        $outletId = (int) $request->outlet_id;
        // $tanggal = (string) $request->tanggal;
        $tanggal = $this->normTanggal($request->tanggal);
        $shift = (int) $request->shift;
        $amount = (float) $request->sales_amount;
        $pic = trim((string) $request->nama_petugas);

        $this->upsertSalesShift($outletId, $tanggal, $shift, $amount, $pic);

        return response()->json([
            'ok' => true,
            'message' => 'Sales tersimpan.',
            'data' => [
                'outlet_id' => $outletId,
                'tanggal' => $tanggal,
                'shift' => $shift,
                'sales_amount' => $amount,
            ],
        ]);
    }

    public function dscStockUpsertRow(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:1,2',
            'nama_petugas' => 'required|string|max:120',

            'bahan_id' => 'required|integer|exists:tbl_bahan,id',
            'ending_stock' => 'required|numeric',

            'purchase_in' => 'nullable|numeric',
            'mutasi_in' => 'nullable|numeric',
            'mutasi_out' => 'nullable|numeric',
            'waste_product' => 'nullable|numeric',
            'waste_bahan' => 'nullable|numeric',
            'uang_plus' => 'nullable|numeric',
            'keterangan' => 'nullable|string|max:255',

            'adjustment_qty' => 'nullable|numeric', // ✅ FIX
        ]);

        $outletId = (int) $request->outlet_id;
        // $tanggal = (string) $request->tanggal;
        $tanggal = $this->normTanggal($request->tanggal);
        $shift = (int) $request->shift;
        $pic = trim((string) $request->nama_petugas);

        $bahanId = (int) $request->bahan_id;

        $ending = (float) $request->ending_stock;
        $purchase = (float) ($request->purchase_in ?? 0);
        $mutIn = (float) ($request->mutasi_in ?? 0);
        $mutOut = (float) ($request->mutasi_out ?? 0);

        $adj = (float) ($request->adjustment_qty ?? 0); // ✅ FIX

        $wProd = (float) ($request->waste_product ?? 0);
        $wBahan = (float) ($request->waste_bahan ?? 0);
        $wTepung = $wProd + $wBahan;

        $uangPlus = (float) ($request->uang_plus ?? 0);
        $ketRaw = (string) ($request->keterangan ?? '');

        $bahan = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->where('id', $bahanId)
            ->first();

        if (! $bahan) {
            return response()->json(['ok' => false, 'message' => 'Bahan tidak ditemukan'], 404);
        }

        // ✅ OPENING selalu pakai helper class (JANGAN define private function di sini)
        $opening = (float) $this->getOpeningStock($outletId, $bahanId, $tanggal, $shift);

        // ✅ total/used harus include adj
        $total = $opening + $purchase + $mutIn - $mutOut + $adj;
        $used = $total - $ending;

        $namaNorm = strtolower(trim((string) $bahan->nama_bahan));
        $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wTepung) : 0.0;

        $existing = DB::table('tbl_stock')
            ->where('outlet_id', $outletId)
            ->where('bahan_id', $bahanId)
            ->where('tanggal', $tanggal)
            ->where('shift', $shift)
            ->first();

        $payload = [
            'satuan' => (string) $bahan->satuan,

            'opening_stock' => $opening,
            'purchase_in' => $purchase,
            'mutasi_in' => $mutIn,
            'mutasi_out' => $mutOut,

            'adjustment_qty' => $adj, // ✅ simpan adj

            'used_qty' => $used,

            'waste_product' => $wProd,
            'waste_bahan' => $wBahan,
            'waste_tepung' => $wTepung,

            'ending_stock' => $ending,
            'actual_tepung' => $actualTepung,

            'uang_plus' => $uangPlus,
            'keterangan' => $this->buildKeteranganSafe($ketRaw, $pic),
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('tbl_stock')->where('id', $existing->id)->update($payload);
        } else {
            DB::table('tbl_stock')->insert(array_merge($payload, [
                'outlet_id' => $outletId,
                'bahan_id' => $bahanId,
                'tanggal' => $tanggal,
                'shift' => $shift,
                'created_at' => now(),
            ]));
        }

        return response()->json([
            'ok' => true,
            'message' => 'Row stock tersimpan.',
            'data' => [
                'outlet_id' => $outletId, 'tanggal' => $tanggal, 'shift' => $shift, 'bahan_id' => $bahanId,
                'opening' => $opening, 'total' => $total, 'used' => $used, 'actual_tepung' => $actualTepung,
            ],
        ]);
    }

    public function dscStockDeleteRow(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:1,2',
            'bahan_id' => 'required|integer|exists:tbl_bahan,id',
        ]);

        $deleted = DB::table('tbl_stock')
            ->where('outlet_id', (int) $request->outlet_id)
            ->where('tanggal', (string) $request->tanggal)
            ->where('shift', (int) $request->shift)
            ->where('bahan_id', (int) $request->bahan_id)
            ->delete();

        return response()->json([
            'ok' => true,
            'message' => $deleted ? 'Row dihapus.' : 'Row tidak ditemukan.',
            'deleted' => (int) $deleted,
        ]);
    }

    public function dscImport(Request $request)
    {
        // Sesuaikan model/tabel outlet kamu
        $outlets = DB::table('tbl_outlets')->select('id', 'nama_outlet')->orderBy('nama_outlet')->get();

        return view('Investor.Inventory.dscImport', [
            'outlets' => $outlets,
        ]);
    }

    public function dscImportPreviewBulk(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => ['required'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'shift' => ['required', 'in:1,2,all'],
            'sales_shift_1' => ['nullable', 'numeric', 'min:0'],
            'sales_shift_2' => ['nullable', 'numeric', 'min:0'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $outletId = (int) $validated['outlet_id'];
        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->startOfDay();
        $shiftMode = (string) $validated['shift'];

        $salesS1 = (float) ($validated['sales_shift_1'] ?? 0);
        $salesS2 = (float) ($validated['sales_shift_2'] ?? 0);

        $master = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->get()
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'nama' => (string) $r->nama_bahan,
                'nama_norm' => $this->normName($r->nama_bahan),
                'satuan' => (string) $r->satuan,
            ])->values();

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheetNames = $spreadsheet->getSheetNames();

        $items = [];
        $errors = [];
        $warnings = [];
        $daysProcessed = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $day = (int) $cursor->format('d');
            $daysProcessed[] = $day;

            if (! in_array((string) $day, $sheetNames, true)) {
                $cursor->addDay();

                continue;
            }
            $ws = $spreadsheet->getSheetByName((string) $day);
            if (! $ws) {
                $cursor->addDay();

                continue;
            }

            $parsed = $this->parseDscGreenTemplateSheet($ws);
            if (! $parsed) {
                $cursor->addDay();

                continue;
            }

            foreach ($parsed as $rowIndex => $row) {
                if ($this->isTrulyEmptyRow($row)) {
                    continue;
                }

                $excelName = (string) ($row['nama_bahan'] ?? '');
                $sat = (string) ($row['satuan'] ?? '');
                $shiftTag = (string) ($row['shift_tag'] ?? '');

                if (trim($excelName) === '') {
                    $errors[] = ['day' => $day, 'row' => $rowIndex, 'nama' => '(kosong)', 'error' => 'Nama barang kosong.'];

                    continue;
                }

                $match = $this->matchBahan($excelName, $master);
                if (! $match) {
                    $errors[] = ['day' => $day, 'row' => $rowIndex, 'nama' => $excelName, 'error' => 'Tidak ditemukan di master tbl_bahan_dsc.'];

                    continue;
                }

                if (($match['score'] ?? 0) < 0.75) {
                    $warnings[] = ['day' => $day, 'row' => $rowIndex, 'nama' => $excelName, 'warning' => 'Fuzzy match score rendah', 'suggest' => $match['nama'] ?? null];
                }

                $rowShifts = $this->resolveRowShifts($shiftMode, $shiftTag);

                foreach ($rowShifts as $sh) {
                    $existing = DB::table('tbl_stock')
                        ->where('outlet_id', $outletId)
                        ->whereDate('tanggal', $cursor->toDateString())
                        ->where('bahan_id', (int) $match['id'])
                        ->where('shift', (int) $sh)
                        ->first();

                    $open = (float) ($row['open'] ?? 0);
                    $purchaseIn = (float) ($row['purchase_in'] ?? 0);
                    $mutIn = (float) ($row['mutasi_in'] ?? 0);
                    $mutOut = (float) ($row['mutasi_out'] ?? 0);
                    $ending = (float) ($row['ending_stock'] ?? 0);

                    $totalStock = $open + $purchaseIn + $mutIn - $mutOut;
                    $actualUsed = $totalStock - $ending;

                    $wProd = (float) ($row['waste_product'] ?? 0);
                    $wBhn = (float) ($row['waste_bahan'] ?? 0);
                    $wTep = (float) ($row['waste_tepung'] ?? 0);

                    $items[] = [
                        'id' => (int) $match['id'],             // ✅ FE butuh ini
                        'day' => $day,
                        'shift' => (int) $sh,
                        'shift_tag' => $shiftTag,
                        'nama_bahan' => $excelName,
                        'satuan' => $sat ?: ($match['satuan'] ?? ''),

                        'adjustment' => $existing ? (float) ($existing->adjustment ?? 0) : 0.0,

                        'open' => $open,
                        'purchase_in' => $purchaseIn,
                        'mutasi_in' => $mutIn,
                        'mutasi_out' => $mutOut,
                        'ending_stock' => $ending,
                        'waste_product' => $wProd,
                        'waste_bahan' => $wBhn,
                        'waste_tepung' => $wTep,
                        'waste_tepung_total' => $wProd + $wBhn + $wTep,
                        'uang_plus' => (float) ($row['uang_plus'] ?? 0),
                        'keterangan' => (string) ($row['keterangan'] ?? ''),

                        'total_stock' => $totalStock,
                        'actual_used' => $actualUsed,
                        'actual_tepung' => null,
                    ];
                }
            }

            $cursor->addDay();
        }

        return response()->json([
            'ok' => true,
            'message' => 'Preview OK',
            'data' => [
                'meta' => [
                    'outlet_id' => $outletId,
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'shift' => $shiftMode,
                    'days' => count(array_unique($daysProcessed)),
                    'rows' => count($items),
                    'sales' => ['shift_1' => $salesS1, 'shift_2' => $salesS2],
                ],
                'items' => $items,
                'errors' => $errors,
                'warnings' => $warnings,
            ],
        ]);
    }

    public function dscImportApplyBulk(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => ['required'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'shift' => ['required', 'in:1,2,all'],
            'sales_shift_1' => ['nullable', 'numeric', 'min:0'],
            'sales_shift_2' => ['nullable', 'numeric', 'min:0'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'nama_petugas' => ['required', 'string', 'max:120'],
        ]);

        $outletId = (int) $validated['outlet_id'];
        $start = Carbon::parse($validated['start_date'])->startOfDay();
        $end = Carbon::parse($validated['end_date'])->startOfDay();
        $shiftMode = (string) $validated['shift'];
        $salesS1 = (float) ($validated['sales_shift_1'] ?? 0);
        $salesS2 = (float) ($validated['sales_shift_2'] ?? 0);
        $pic = trim((string) $validated['nama_petugas']);

        $master = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->get()
            ->map(fn ($r) => [
                'id' => (int) $r->id,
                'nama' => (string) $r->nama_bahan,
                'nama_norm' => $this->normName($r->nama_bahan),
                'satuan' => (string) $r->satuan,
            ])->values();

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheetNames = $spreadsheet->getSheetNames();

        $inserted = 0;
        $updated = 0;
        $skippedSheets = 0;
        $skippedRowsNoMatch = 0;

        DB::beginTransaction();
        try {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $day = (int) $cursor->format('d');
                $tanggal = $cursor->toDateString();

                if (! in_array((string) $day, $sheetNames, true)) {
                    $cursor->addDay();

                    continue;
                }

                $ws = $spreadsheet->getSheetByName((string) $day);
                if (! $ws) {
                    $cursor->addDay();

                    continue;
                }

                $parsed = $this->parseDscGreenTemplateSheet($ws);
                if (! $parsed) {
                    $skippedSheets++;
                    $cursor->addDay();

                    continue;
                }

                // sales shift per hari
                $this->upsertSalesShift($outletId, $tanggal, 1, $salesS1, $pic);
                $this->upsertSalesShift($outletId, $tanggal, 2, $salesS2, $pic);

                foreach ($parsed as $row) {
                    if ($this->isTrulyEmptyRow($row)) {
                        continue;
                    }

                    $excelName = (string) ($row['nama_bahan'] ?? '');
                    if (trim($excelName) === '') {
                        continue;
                    }

                    $match = $this->matchBahan($excelName, $master);
                    if (! $match) {
                        $skippedRowsNoMatch++;

                        continue;
                    }

                    $bahanId = (int) $match['id'];
                    $sat = (string) ($row['satuan'] ?? '');
                    $shiftTag = (string) ($row['shift_tag'] ?? '');

                    // angka excel
                    $open = (float) ($row['open'] ?? 0);
                    $purchase = (float) ($row['purchase_in'] ?? 0);
                    $mutIn = (float) ($row['mutasi_in'] ?? 0);
                    $mutOut = (float) ($row['mutasi_out'] ?? 0);
                    $ending = (float) ($row['ending_stock'] ?? 0);
                    $wProd = (float) ($row['waste_product'] ?? 0);
                    $wBahan = (float) ($row['waste_bahan'] ?? 0);
                    $wTepung = (float) ($row['waste_tepung'] ?? 0);
                    $uangPlus = (float) ($row['uang_plus'] ?? 0);
                    $ket = (string) ($row['keterangan'] ?? '');

                    // Simpan Waste TEPUNG mentah dari kolom M.
                    // Akumulasi untuk Rekap dihitung sebagai Waste P + Waste B + Waste TEPUNG.

                    // ✅ FIX UTAMA: shift ditentukan per baris, bukan dobel untuk all
                    $rowShifts = $this->resolveRowShifts($shiftMode, $shiftTag);

                    foreach ($rowShifts as $sh) {
                        $payload = [
                            'outlet_id' => $outletId,
                            'tanggal' => $tanggal,
                            'shift' => (int) $sh,
                            'bahan_id' => $bahanId,

                            'satuan' => $sat !== '' ? $sat : (string) ($match['satuan'] ?? ''),

                            // ⛳️ jika kamu TIDAK mau overwrite OPEN sistem, comment baris ini:
                            'opening_stock' => $open,

                            'purchase_in' => $purchase,
                            'mutasi_in' => $mutIn,
                            'mutasi_out' => $mutOut,
                            'ending_stock' => $ending,

                            'waste_product' => $wProd,
                            'waste_bahan' => $wBahan,
                            'waste_tepung' => $wTepung,

                            'uang_plus' => $uangPlus,
                            'keterangan' => $ket,

                            'updated_at' => now(),
                        ];

                        $exists = DB::table('tbl_stock')
                            ->where('outlet_id', $outletId)
                            ->whereDate('tanggal', $tanggal)
                            ->where('shift', (int) $sh)
                            ->where('bahan_id', $bahanId)
                            ->first();

                        if ($exists) {
                            DB::table('tbl_stock')->where('id', $exists->id)->update($payload);
                            $updated++;
                        } else {
                            $payload['created_at'] = now();
                            DB::table('tbl_stock')->insert($payload);
                            $inserted++;
                        }
                    }
                }

                $cursor->addDay();
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Import applied (no double shift; shift based on Excel SHIFT column)',
                'data' => [
                    'inserted' => $inserted,
                    'updated' => $updated,
                    'skipped_sheets' => $skippedSheets,
                    'skipped_rows_no_match' => $skippedRowsNoMatch,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function parseDscGreenTemplateSheet(Worksheet $ws): array
    {
        // =========================
        // Kolom template Hijau (DSC)
        // =========================
        $COL_NO = 1;  // A
        $COL_NAMA = 2;  // B
        $COL_SAT = 3;  // C
        $COL_OPEN = 4;  // D
        $COL_PIN = 5;  // E
        $COL_MI = 6;  // F
        $COL_MO = 7;  // G
        $COL_END = 9;  // I

        $COL_WPROD = 11; // K = WASTE PRODUCT / WASTE P
        $COL_WBAH = 12; // L = WASTE BAHAN / WASTE B
        $COL_WTEP = 13; // M = WASTE TEPUNG / WASTE T input

        // ✅ FIX: Bagian kanan (ini yang bikin uang_plus masuk keterangan kalau salah)
        // N (14) = ACTUAL TEPUNG (bukan shift)
        $COL_SHIFT = 15; // O = UANG PLUS (SHIFT) -> S1/S2/SHO/TOT
        $COL_UANG = 16; // P = UANG PLUS (RP)
        $COL_KET = 17; // Q = KETERANGAN (biasanya merge Q-R, cukup ambil Q)

        $highestRow = min(500, (int) $ws->getHighestRow());

        // Cari baris data pertama: kolom NO numerik dan NAMA tidak kosong
        $startRow = null;
        for ($r = 1; $r <= $highestRow; $r++) {
            $no = $this->cellString($ws, $COL_NO, $r);
            $nm = $this->cellString($ws, $COL_NAMA, $r);

            if ($this->isNumericLike($no) && trim($nm) !== '') {
                $startRow = $r;
                break;
            }
        }
        if ($startRow === null) {
            return [];
        }

        $rows = [];
        for ($r = $startRow; $r <= $highestRow; $r++) {

            $noRaw = $this->cellString($ws, $COL_NO, $r);
            if (! $this->isNumericLike($noRaw)) {
                break;
            } // stop kalau NO sudah bukan angka

            $namaRaw = $this->cellString($ws, $COL_NAMA, $r);
            $namaRaw = $this->collapseSpacedLetters($namaRaw);

            $namaNorm = $this->normName($namaRaw);
            if (in_array($namaNorm, ['nama', 'barang', 'nama barang', 'no'], true)) {
                continue;
            }

            $row = [
                'no' => (int) $noRaw,
                'nama_bahan' => $namaRaw,
                'satuan' => $this->cellString($ws, $COL_SAT, $r),

                'open' => $this->cellNumID($ws, $COL_OPEN, $r),
                'purchase_in' => $this->cellNumID($ws, $COL_PIN, $r),
                'mutasi_in' => $this->cellNumID($ws, $COL_MI, $r),
                'mutasi_out' => $this->cellNumID($ws, $COL_MO, $r),
                'ending_stock' => $this->cellNumID($ws, $COL_END, $r),

                'waste_product' => $this->cellNumID($ws, $COL_WPROD, $r),
                'waste_bahan' => $this->cellNumID($ws, $COL_WBAH, $r),
                'waste_tepung' => $this->cellNumID($ws, $COL_WTEP, $r),

                // ✅ FIX: shift_tag dari kolom O, uang_plus dari kolom P, keterangan dari kolom Q
                'shift_tag' => strtoupper(trim($this->cellString($ws, $COL_SHIFT, $r))),
                'uang_plus' => $this->cellNumID($ws, $COL_UANG, $r),
                'keterangan' => $this->cellString($ws, $COL_KET, $r),
            ];

            if ($this->isTrulyEmptyRow($row)) {
                continue;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function cellNumID(Worksheet $ws, int $col, int $row): float
    {
        $v = $ws->getCellByColumnAndRow($col, $row)->getCalculatedValue();

        return $this->numID($v);
    }

    private function numID($v): float
    {
        if ($v === null) {
            return 0.0;
        }
        if (is_int($v) || is_float($v)) {
            return (float) $v;
        }

        $s = trim((string) $v);
        if ($s === '') {
            return 0.0;
        }

        // buang spasi, "Rp", dll yang umum
        $s = str_replace(['Rp', 'rp', ' '], '', $s);

        // kalau ada koma dan titik, anggap titik ribuan, koma desimal
        if (str_contains($s, ',') && str_contains($s, '.')) {
            $s = str_replace('.', '', $s);
            $s = str_replace(',', '.', $s);

            return is_numeric($s) ? (float) $s : 0.0;
        }

        // kalau hanya koma -> desimal
        if (str_contains($s, ',')) {
            $s = str_replace('.', '', $s); // jaga-jaga kalau ada ribuan pakai titik
            $s = str_replace(',', '.', $s);

            return is_numeric($s) ? (float) $s : 0.0;
        }

        // kalau hanya titik, cek apakah titik ribuan (xxx.xxx) -> remove dots
        // heuristik: kalau group terakhir 3 digit -> ribuan
        if (preg_match('/\.\d{3}(\.\d{3})*$/', $s)) {
            $s = str_replace('.', '', $s);

            return is_numeric($s) ? (float) $s : 0.0;
        }

        return is_numeric($s) ? (float) $s : 0.0;
    }

    private function resolveRowShifts(string $shiftMode, string $shiftTag): array
    {
        // shiftMode: 1/2/all (dari request)
        // shiftTag : S1/S2/SHO/TOT/'' (dari excel)

        $tag = strtoupper(trim($shiftTag));

        // Kalau user pilih shift spesifik, pakai itu saja (abaikan tag excel)
        if ($shiftMode === '1') {
            return [1];
        }
        if ($shiftMode === '2') {
            return [2];
        }

        // shiftMode === 'all' -> baca tag excel
        if ($tag === 'S1') {
            return [1];
        }
        if ($tag === 'S2') {
            return [2];
        }

        // SHO/TOT/blank: ini biasanya "total harian / shared"
        // ✅ PENTING: JANGAN diisi ke dua shift, nanti dobel.
        // Pilih salah satu kebijakan:
        // A) masuk shift 1 saja (AMAN, tidak dobel)
        return [1];

        // Kalau kamu mau kebijakan lain:
        // B) masuk shift 2 saja -> return [2];
        // C) bagi 50/50 -> butuh logic split (tidak saya sarankan tanpa rule jelas)
    }

    private function matchBahan(string $excelName, $master): ?array
    {
        $excelNorm = $this->normName($excelName);
        if ($excelNorm === '') {
            return null;
        }

        // master bisa Collection atau array
        $masterArr = is_array($master) ? $master : $master->toArray();

        // 1) exact by normalized
        foreach ($masterArr as $m) {
            if (($m['nama_norm'] ?? '') === $excelNorm) {
                return [
                    'id' => (int) $m['id'],
                    'nama' => (string) $m['nama'],
                    'satuan' => (string) $m['satuan'],
                    'score' => 1.0,
                ];
            }
        }

        // 2) fuzzy: similar_text (tanpa ekstensi php tambahan)
        $best = null;
        $bestScore = 0.0;

        foreach ($masterArr as $m) {
            $mNorm = (string) ($m['nama_norm'] ?? '');
            if ($mNorm === '') {
                continue;
            }

            // similar_text menghasilkan percent (0..100)
            $percent = 0.0;
            similar_text($excelNorm, $mNorm, $percent);
            $score = $percent / 100.0;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $m;
            }
        }

        // threshold minimal supaya gak ngawur
        if (! $best || $bestScore < 0.60) {
            return null;
        }

        return [
            'id' => (int) $best['id'],
            'nama' => (string) $best['nama'],
            'satuan' => (string) $best['satuan'],
            'score' => (float) $bestScore,
        ];
    }

    private function cellString(Worksheet $ws, int $col, int $row): string
    {
        $v = $ws->getCellByColumnAndRow($col, $row)->getCalculatedValue();
        if ($v === null) {
            return '';
        }

        return trim((string) $v);
    }

    private function cellNum(Worksheet $ws, int $col, int $row): float
    {
        $v = $ws->getCellByColumnAndRow($col, $row)->getCalculatedValue();

        return $this->num($v);
    }

    private function isNumericLike($v): bool
    {
        $s = trim((string) $v);
        if ($s === '') {
            return false;
        }

        // "1", "2", "3" ...
        return preg_match('/^\d+$/', $s) === 1;
    }

    /**
     * Convert "N A M A" => "NAMA" (header template suka begini)
     */
    private function collapseSpacedLetters(string $s): string
    {
        $t = trim($s);
        // kalau banyak huruf tunggal dipisah spasi, gabungkan
        if (preg_match('/^([A-Za-z]\s+){2,}[A-Za-z]$/', $t)) {
            return str_replace(' ', '', $t);
        }

        return $s;
    }

    /**
     * Row dianggap kosong kalau nama kosong dan semua angka 0
     */
    private function isTrulyEmptyRow(array $row): bool
    {
        $nama = trim((string) ($row['nama_bahan'] ?? ''));
        $nums = [
            $row['open'] ?? 0,
            $row['purchase_in'] ?? 0,
            $row['mutasi_in'] ?? 0,
            $row['mutasi_out'] ?? 0,
            $row['ending_stock'] ?? 0,
            $row['waste_product'] ?? 0,
            $row['waste_bahan'] ?? 0,
            $row['uang_plus'] ?? 0,
        ];

        if ($nama !== '') {
            // kalau ada nama, anggap tidak kosong
            return false;
        }

        foreach ($nums as $n) {
            if (abs((float) $n) > 0.0000001) {
                return false;
            }
        }

        return true;
    }


    private function normalizeOutletNameDsc($name): string
    {
        $name = strtoupper(trim((string) $name));
        return preg_replace('/\s+/', ' ', $name) ?: '-';
    }

    private function getDscMissingOutletRows(string $startDate, string $endDate, ?string $keyword = null): array
    {
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $keyword = trim((string) ($keyword ?? ''));

        $outletsRaw = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet', 'status')
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where('nama_outlet', 'like', '%' . $keyword . '%')
                  ->orWhere('id', $keyword);
            })
            ->orderBy('nama_outlet')
            ->orderBy('id')
            ->get();

        $groups = [];
        foreach ($outletsRaw->groupBy(fn ($o) => $this->normalizeOutletNameDsc($o->nama_outlet)) as $name => $items) {
            $ids = $items->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $groups[] = [
                'id' => min($ids),
                'nama_outlet' => $name,
                'ids' => $ids,
                'merged_ids' => implode(',', $ids),
            ];
        }

        $allOutletIds = collect($groups)->flatMap(fn ($g) => $g['ids'])->unique()->values()->all();
        if (empty($allOutletIds)) {
            return [];
        }

        $filledRows = DB::table('tbl_stock')
            ->select(DB::raw('DATE(tanggal) as tanggal'), 'outlet_id')
            ->whereIn('outlet_id', $allOutletIds)
            ->whereDate('tanggal', '>=', $startDate)
            ->whereDate('tanggal', '<=', $endDate)
            ->groupBy(DB::raw('DATE(tanggal)'), 'outlet_id')
            ->get();

        $filledSet = [];
        foreach ($filledRows as $row) {
            $filledSet[$row->tanggal . '|' . (int) $row->outlet_id] = true;
        }

        $lastInputRows = DB::table('tbl_stock')
            ->select('outlet_id', DB::raw('MAX(DATE(tanggal)) as last_input_date'))
            ->whereIn('outlet_id', $allOutletIds)
            ->groupBy('outlet_id')
            ->get()
            ->keyBy('outlet_id');

        $rows = [];
        $no = 1;

        foreach (\Carbon\CarbonPeriod::create($startDate, $endDate) as $date) {
            $dateYmd = $date->format('Y-m-d');

            foreach ($groups as $group) {
                $hasInput = false;
                foreach ($group['ids'] as $id) {
                    if (isset($filledSet[$dateYmd . '|' . (int) $id])) {
                        $hasInput = true;
                        break;
                    }
                }

                if ($hasInput) {
                    continue;
                }

                $lastInputDate = null;
                foreach ($group['ids'] as $id) {
                    $candidate = $lastInputRows[(int) $id]->last_input_date ?? null;
                    if ($candidate && (!$lastInputDate || $candidate > $lastInputDate)) {
                        $lastInputDate = $candidate;
                    }
                }

                $rows[] = [
                    'no' => $no++,
                    'tanggal' => $dateYmd,
                    'outlet_id' => $group['id'],
                    'nama_outlet' => $group['nama_outlet'],
                    'merged_ids' => $group['merged_ids'],
                    'last_input_date' => $lastInputDate,
                    'status' => 'BELUM MENGISI DSC',
                ];
            }
        }

        return $rows;
    }

    public function dscMissingOutlet(Request $request)
    {
        $today = $this->normalizeDateToYmd((string) $request->get('tanggal', date('Y-m-d')));
        $defaultDate = \Carbon\Carbon::parse($today)->subDay()->format('Y-m-d');

        $startDate = $this->normalizeDateToYmd((string) $request->get('start_date', $defaultDate));
        $endDate = $this->normalizeDateToYmd((string) $request->get('end_date', $startDate));
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $keyword = trim((string) $request->get('q', ''));
        $rows = $this->getDscMissingOutletRows($startDate, $endDate, $keyword);

        return view('Investor.Inventory.dscMissingSOOutlet', [
            'today' => $today,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'keyword' => $keyword,
            'rows' => $rows,
            'totalMissing' => count($rows),
        ]);
    }

    public function exportDscMissingOutlet(Request $request)
    {
        $today = $this->normalizeDateToYmd((string) $request->get('tanggal', date('Y-m-d')));
        $defaultDate = \Carbon\Carbon::parse($today)->subDay()->format('Y-m-d');

        $startDate = $this->normalizeDateToYmd((string) $request->get('start_date', $defaultDate));
        $endDate = $this->normalizeDateToYmd((string) $request->get('end_date', $startDate));
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $keyword = trim((string) $request->get('q', ''));
        $rows = $this->getDscMissingOutletRows($startDate, $endDate, $keyword);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('OUTLET BELUM DSC');

        $headers = ['NO', 'TANGGAL', 'OUTLET ID', 'NAMA OUTLET', 'MERGED IDS', 'LAST INPUT DATE', 'STATUS'];
        $title = 'MONITORING OUTLET BELUM MENGISI DSC - PERIODE ' . $startDate . ' s/d ' . $endDate;
        $this->xlsxRenderTemplateLike($sheet, $title, $headers);

        $rowNo = 4;
        foreach ($rows as $row) {
            $sheet->fromArray([
                (int) $row['no'],
                (string) $row['tanggal'],
                (int) $row['outlet_id'],
                (string) $row['nama_outlet'],
                (string) $row['merged_ids'],
                (string) ($row['last_input_date'] ?? '-'),
                (string) $row['status'],
            ], null, 'A' . $rowNo);
            $rowNo++;
        }

        $lastRow = max(4, $rowNo - 1);
        $this->xlsxApplyBordersAndFormat($sheet, count($headers), $lastRow, [
            'textCols' => [2, 4, 5, 6, 7],
            'numFrom' => 1,
            'numTo' => 3,
            'nameCol' => 4,
        ]);
        $this->xlsxSetWidths($sheet, [
            1 => 6, 2 => 14, 3 => 12, 4 => 38, 5 => 22, 6 => 16, 7 => 24,
        ], count($headers), 14);

        $filename = 'MONITORING_OUTLET_BELUM_DSC_' . $startDate . '_sd_' . $endDate . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportDsc(Request $request)
    {
        try {
            $tanggalRaw = (string) $request->get('tanggal', date('Y-m-d'));
            $startRaw = (string) $request->get('start_date', $tanggalRaw);
            $endRaw = (string) $request->get('end_date', $startRaw ?: $tanggalRaw);
            $outletIdRaw = $request->get('outlet_id', '');
            $shiftFilter = (string) $request->get('shift_filter', 'all');

            $startDate = $this->normalizeDateToYmd($startRaw ?: $tanggalRaw);
            $endDate = $this->normalizeDateToYmd($endRaw ?: $startDate);

            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            if ($outletIdRaw === '' || $outletIdRaw === null || $outletIdRaw === 'all') {
                return response()->json(['ok' => false, 'message' => 'outlet_id wajib dipilih sebelum export'], 400);
            }

            $outletId = (int) $outletIdRaw;
            $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
            if ($days > 31) {
                return response()->json(['ok' => false, 'message' => 'Range export maksimal 31 hari.'], 422);
            }

            $spreadsheet = new Spreadsheet;

            // ======================
            // SHEET 1: REKAP RANGE
            // ======================
            $s1 = $spreadsheet->getActiveSheet();
            $s1->setTitle('REKAP');

            $headersRekap = [
                'TANGGAL', 'NO', 'NAMA BARANG', 'SAT', 'OPEN', 'PURCHASE IN', 'MUTASI IN', 'MUTASI OUT', 'ADJ',
                'TOTAL', 'ENDING', 'USED', 'WASTE P', 'WASTE B', 'WASTE T', 'WASTE TEPUNG INPUT',
                'ACTUAL TEPUNG', 'USED S1', 'USED S2', 'UANG PLUS', 'KETERANGAN', 'OPEN NEXT (R)',
            ];

            $title1 = "DAILY STOCK CONTROL - REKAP (OUTLET: {$outletId}, PERIODE: {$startDate} s/d {$endDate}, SHIFT: {$shiftFilter})";
            $this->xlsxRenderTemplateLike($s1, $title1, $headersRekap);

            $row = 4;
            foreach (\Carbon\CarbonPeriod::create($startDate, $endDate) as $date) {
                $dateYmd = $date->format('Y-m-d');
                $data = $this->buildDscDataFor($dateYmd, $outletId, $shiftFilter);
                foreach (($data['rekapRows'] ?? []) as $r) {
                    $s1->fromArray([
                        $dateYmd,
                        (int) ($r['no'] ?? 0),
                        (string) ($r['nama'] ?? ''),
                        (string) ($r['sat'] ?? ''),
                        (float) ($r['open'] ?? 0),
                        (float) ($r['pin'] ?? 0),
                        (float) ($r['mi'] ?? 0),
                        (float) ($r['mo'] ?? 0),
                        (float) ($r['adj'] ?? 0),
                        (float) ($r['total'] ?? 0),
                        (float) ($r['ending_stock'] ?? 0),
                        (float) ($r['used'] ?? 0),
                        (float) ($r['wP'] ?? 0),
                        (float) ($r['wB'] ?? 0),
                        (float) ($r['wT'] ?? 0),
                        (float) ($r['wT_input'] ?? 0),
                        (float) ($r['actualTepung'] ?? 0),
                        (float) ($r['shift1'] ?? 0),
                        (float) ($r['shift2'] ?? 0),
                        (float) ($r['uang'] ?? 0),
                        (string) ($r['ket'] ?? ''),
                        (float) ($r['open_stock_right'] ?? 0),
                    ], null, "A{$row}");
                    $row++;
                }
            }

            $lastRow1 = max(4, $row - 1);
            $this->xlsxApplyBordersAndFormat($s1, count($headersRekap), $lastRow1, [
                'textCols' => [1, 3, 4, 21],
                'numFrom' => 5,
                'numTo' => 22,
                'nameCol' => 3,
            ]);
            $this->xlsxSetWidths($s1, [
                1 => 13, 2 => 5, 3 => 34, 4 => 8, 21 => 42,
            ], 22, 14);

            // ======================
            // SHEET 2: DETAIL SHIFT RANGE
            // ======================
            $s2 = $spreadsheet->createSheet();
            $s2->setTitle('DETAIL SHIFT');

            $headersShift = [
                'TANGGAL', 'NO', 'NAMA BARANG', 'SAT', 'SHIFT', 'OPEN', 'PIN', 'MI', 'MO',
                'ADJ', 'TOTAL', 'ENDING', 'USED', 'WP', 'WB', 'WT', 'WASTE TEPUNG INPUT', 'KET',
            ];

            $title2 = "DAILY STOCK CONTROL - DETAIL SHIFT (OUTLET: {$outletId}, PERIODE: {$startDate} s/d {$endDate}, SHIFT: {$shiftFilter})";
            $this->xlsxRenderTemplateLike($s2, $title2, $headersShift);

            $row = 4;
            foreach (\Carbon\CarbonPeriod::create($startDate, $endDate) as $date) {
                $dateYmd = $date->format('Y-m-d');
                $data = $this->buildDscDataFor($dateYmd, $outletId, $shiftFilter);
                foreach (($data['shiftRows'] ?? []) as $r) {
                    $s2->fromArray([
                        $dateYmd,
                        (int) ($r['no'] ?? 0),
                        (string) ($r['nama'] ?? ''),
                        (string) ($r['sat'] ?? ''),
                        (int) ($r['shift'] ?? 0),
                        (float) ($r['open'] ?? 0),
                        (float) ($r['pin'] ?? 0),
                        (float) ($r['mi'] ?? 0),
                        (float) ($r['mo'] ?? 0),
                        (float) ($r['adj'] ?? 0),
                        (float) ($r['total'] ?? 0),
                        (float) ($r['ending_stock'] ?? 0),
                        (float) ($r['used'] ?? 0),
                        (float) ($r['wP'] ?? 0),
                        (float) ($r['wB'] ?? 0),
                        (float) ($r['wT'] ?? 0),
                        (float) ($r['wT_input'] ?? 0),
                        (string) ($r['ket'] ?? ''),
                    ], null, "A{$row}");
                    $row++;
                }
            }

            $lastRow2 = max(4, $row - 1);
            $this->xlsxApplyBordersAndFormat($s2, count($headersShift), $lastRow2, [
                'textCols' => [1, 3, 4, 18],
                'numFrom' => 6,
                'numTo' => 17,
                'nameCol' => 3,
            ]);
            $this->xlsxSetWidths($s2, [
                1 => 13, 2 => 5, 3 => 34, 4 => 8, 5 => 8, 18 => 42,
            ], 18, 14);

            $filename = "DSC_EXPORT_outlet{$outletId}_{$startDate}_sd_{$endDate}_shift{$shiftFilter}.xlsx";

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Export gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function xlsxRenderTemplateLike($sheet, string $title, array $headers): void
    {
        $colCount = count($headers);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        // Row 1: Title (merge + center + bold)
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 2: kosong (biarin kosong)

        // Row 3: Header
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Freeze pane sama kayak template (header row stay)
        $sheet->freezePane('A4');
    }

    private function xlsxApplyBordersAndFormat($sheet, int $colCount, int $lastRow, array $opt = []): void
    {
        $lastColLetter = Coordinate::stringFromColumnIndex($colCount);

        // Border untuk semua area data (mulai row 3 header sampai lastRow)
        $sheet->getStyle("A3:{$lastColLetter}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Default alignment center untuk semua
        $sheet->getStyle("A3:{$lastColLetter}{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Kolom nama: left
        $nameCol = (int) ($opt['nameCol'] ?? 2);
        $nameLetter = Coordinate::stringFromColumnIndex($nameCol);
        $sheet->getStyle("{$nameLetter}4:{$nameLetter}{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Kolom text lain: left
        $textCols = $opt['textCols'] ?? [];
        foreach ($textCols as $c) {
            $letter = Coordinate::stringFromColumnIndex((int) $c);
            $sheet->getStyle("{$letter}4:{$letter}{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // Kolom numeric: right + number format
        $numFrom = (int) ($opt['numFrom'] ?? 0);
        $numTo = (int) ($opt['numTo'] ?? 0);

        if ($numFrom > 0 && $numTo >= $numFrom && $lastRow >= 4) {
            $fromL = Coordinate::stringFromColumnIndex($numFrom);
            $toL = Coordinate::stringFromColumnIndex($numTo);

            $sheet->getStyle("{$fromL}4:{$toL}{$lastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("{$fromL}4:{$toL}{$lastRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0');
        }
    }

    private function xlsxSetWidths($sheet, array $customWidths, int $colCount, float $default = 13): void
    {
        for ($c = 1; $c <= $colCount; $c++) {
            $letter = Coordinate::stringFromColumnIndex($c);
            $w = $customWidths[$c] ?? $default;
            $sheet->getColumnDimension($letter)->setWidth($w);
        }
    }

    private function buildDscDataFor(string $today, int $outletId, string $shiftFilter): array
    {
        // =========================
        // 1) OMSET (EXACT dulu, fallback)
        // =========================
        $omsetExact = DB::table('tbl_dsc_omset_setoran')
            ->where('outlet_id', $outletId)
            ->whereDate('tanggal', $today)
            ->first();

        $omsetRow = $omsetExact;
        $mode = 'EXACT';

        if (! $omsetRow) {
            $omsetRow = DB::table('tbl_dsc_omset_setoran')
                ->where('outlet_id', $outletId)
                ->whereDate('tanggal', '<=', $today)
                ->orderBy('tanggal', 'desc')
                ->first();

            $mode = $omsetRow ? 'FALLBACK_LTE' : 'NO_DATA';
        }

        $omsetLastDate = DB::table('tbl_dsc_omset_setoran')
            ->where('outlet_id', $outletId)
            ->max('tanggal');

        $omsetActive = $this->buildOmsetActive($omsetRow, $shiftFilter, $today, $omsetLastDate, $mode);

        $salesShift1 = $omsetRow ? (float) ($omsetRow->s1_total_transaction ?? 0) : 0.0;
        $salesShift2 = $omsetRow ? (float) ($omsetRow->s2_total_transaction ?? 0) : 0.0;

        // =========================
        // 2) master bahan aktif
        // =========================
        $bahanList = DB::table('tbl_bahan_dsc')
            ->select('id', 'nama_bahan', 'satuan')
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        // =========================
        // 3) STOCK hari itu (FINAL id terbesar per bahan+shift)
        // =========================
        $stockRows = DB::table('tbl_stock')
            ->where('outlet_id', $outletId)
            ->whereDate('tanggal', $today)
            ->orderByDesc('id')
            ->get();

        $stockMap = [];
        foreach ($stockRows as $sr) {
            $bid = (int) $sr->bahan_id;
            $sh = (int) $sr->shift;
            if (isset($stockMap[$bid][$sh])) {
                continue;
            } // keep newest
            $stockMap[$bid][$sh] = $sr;
        }

        $useS1 = ($shiftFilter === 'all' || $shiftFilter === '1');
        $useS2 = ($shiftFilter === 'all' || $shiftFilter === '2');

        // =========================
        // 4) BUILD REKAP (tetap seperti punyamu)
        // =========================
        $rekapRows = [];
        $no = 1;

        foreach ($bahanList as $b) {
            $bahanId = (int) $b->id;

            $r1 = $stockMap[$bahanId][1] ?? null;
            $r2 = $stockMap[$bahanId][2] ?? null;

            $openRekap = (float) $this->getOpeningStock($outletId, $bahanId, $today, 1);

            $pin = 0.0;
            $mi = 0.0;
            $mo = 0.0;
            $adj = 0.0;
            $wP = 0.0;
            $wB = 0.0;
            $uang = 0.0;
            $ketParts = [];

            if ($useS1 && $r1) {
                $pin += (float) ($r1->purchase_in ?? 0);
                $mi += (float) ($r1->mutasi_in ?? 0);
                $mo += (float) ($r1->mutasi_out ?? 0);
                $adj += (float) ($r1->adjustment_qty ?? 0);
                $wP += (float) ($r1->waste_product ?? 0);
                $wB += (float) ($r1->waste_bahan ?? 0);
                $uang += (float) ($r1->uang_plus ?? 0);
                if (! empty($r1->keterangan)) {
                    $ketParts[] = 'S1: '.$r1->keterangan;
                }
            }

            if ($useS2 && $r2) {
                $pin += (float) ($r2->purchase_in ?? 0);
                $mi += (float) ($r2->mutasi_in ?? 0);
                $mo += (float) ($r2->mutasi_out ?? 0);
                $adj += (float) ($r2->adjustment_qty ?? 0);
                $wP += (float) ($r2->waste_product ?? 0);
                $wB += (float) ($r2->waste_bahan ?? 0);
                $uang += (float) ($r2->uang_plus ?? 0);
                if (! empty($r2->keterangan)) {
                    $ketParts[] = 'S2: '.$r2->keterangan;
                }
            }

            $ending = 0.0;
            if ($shiftFilter === '1') {
                $ending = (float) ($r1->ending_stock ?? 0);
            } elseif ($shiftFilter === '2') {
                $ending = (float) ($r2->ending_stock ?? 0);
            } else {
                if ($r2) {
                    $ending = (float) ($r2->ending_stock ?? 0);
                } elseif ($r1) {
                    $ending = (float) ($r1->ending_stock ?? 0);
                }
            }

            $total = $openRekap + $pin + $mi - $mo + $adj;
            $used = $total - $ending;

            $wT = $wP + $wB;

            $namaNorm = strtolower(trim((string) $b->nama_bahan));
            $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wT) : 0.0;

            $openS1 = (float) $this->getOpeningStock($outletId, $bahanId, $today, 1);
            $openS2 = (float) $this->getOpeningStock($outletId, $bahanId, $today, 2);

            $usedS1 = 0.0;
            if ($r1) {
                $t1 = $openS1 + (float) ($r1->purchase_in ?? 0) + (float) ($r1->mutasi_in ?? 0) - (float) ($r1->mutasi_out ?? 0) + (float) ($r1->adjustment_qty ?? 0);
                $usedS1 = (float) ($r1->used_qty ?? ($t1 - (float) ($r1->ending_stock ?? 0)));
            }

            $usedS2 = 0.0;
            if ($r2) {
                $t2 = $openS2 + (float) ($r2->purchase_in ?? 0) + (float) ($r2->mutasi_in ?? 0) - (float) ($r2->mutasi_out ?? 0) + (float) ($r2->adjustment_qty ?? 0);
                $usedS2 = (float) ($r2->used_qty ?? ($t2 - (float) ($r2->ending_stock ?? 0)));
            }

            $rekapRows[] = [
                'no' => $no++,
                'nama' => (string) $b->nama_bahan,
                'sat' => (string) $b->satuan,

                'open' => $openRekap,
                'pin' => $pin,
                'mi' => $mi,
                'mo' => $mo,
                'adj' => $adj,

                'total' => $total,
                'ending_stock' => $ending,
                'used' => $used,

                'wP' => $wP,
                'wB' => $wB,
                'wT' => $wT,

                'actualTepung' => $actualTepung,

                'shift1' => $usedS1,
                'shift2' => $usedS2,

                'uang' => $uang,
                'ket' => implode(' | ', $ketParts),

                'open_stock_right' => $ending,
            ];
        }

        // =========================
        // 5) BUILD SHIFT ROWS (tambahkan uang_plus!)
        // =========================
        $shiftRows = [];
        $no2 = 1;

        $shifts = [];
        if ($shiftFilter === 'all') {
            $shifts = [1, 2];
        }
        if ($shiftFilter === '1') {
            $shifts = [1];
        }
        if ($shiftFilter === '2') {
            $shifts = [2];
        }

        foreach ($bahanList as $b) {
            $bahanId = (int) $b->id;

            foreach ($shifts as $sh) {
                $sr = $stockMap[$bahanId][$sh] ?? null;

                $open = (float) $this->getOpeningStock($outletId, $bahanId, $today, (int) $sh);

                $pin = $sr ? (float) ($sr->purchase_in ?? 0) : 0.0;
                $mi = $sr ? (float) ($sr->mutasi_in ?? 0) : 0.0;
                $mo = $sr ? (float) ($sr->mutasi_out ?? 0) : 0.0;
                $adj = $sr ? (float) ($sr->adjustment_qty ?? 0) : 0.0;
                $ending = $sr ? (float) ($sr->ending_stock ?? 0) : 0.0;

                $total = $open + $pin + $mi - $mo;

            // ADJ dipakai untuk koreksi ending.
            $ending = $ending + $adj;

            $used = $total - $ending;

                $wP = $sr ? (float) ($sr->waste_product ?? 0) : 0.0;
                $wB = $sr ? (float) ($sr->waste_bahan ?? 0) : 0.0;
                $wT = $wP + $wB;

                $shiftRows[] = [
                    'no' => $no2++,
                    'nama' => (string) $b->nama_bahan,
                    'sat' => (string) $b->satuan,
                    'shift' => (int) $sh,
                    'bahan_id' => $bahanId,

                    'open' => $open,
                    'pin' => $pin,
                    'mi' => $mi,
                    'mo' => $mo,
                    'adj' => $adj,

                    'total' => $total,
                    'ending_stock' => $ending,
                    'used' => $used,

                    'wP' => $wP,
                    'wB' => $wB,
                    'wT' => $wT,

                    'uang' => $sr ? (float) ($sr->uang_plus ?? 0) : 0.0,
                    'ket' => $sr ? (string) ($sr->keterangan ?? '') : '',
                ];
            }
        }

        return [
            'rekapRows' => $rekapRows,
            'shiftRows' => $shiftRows,
            'sales_shift_1' => $salesShift1,
            'sales_shift_2' => $salesShift2,
            'omsetActive' => $omsetActive,
        ];
    }

    private function writeHeaderRow($sheet, array $headers): void
    {
        $sheet->fromArray($headers, null, 'A1');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $range = "A1:{$lastCol}1";

        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DDEBF7'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);
    }

    /**
     * ✅ Helper: auto width + freeze header + number format
     */
    private function beautifySheet($sheet, int $colCount, int $lastRow): void
    {
        // freeze header
        $sheet->freezePane('A2');

        // autosize columns
        for ($c = 1; $c <= $colCount; $c++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // border + alignment basic
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
        $rangeAll = "A1:{$lastCol}{$lastRow}";
        $sheet->getStyle($rangeAll)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // format angka: kolom angka biasanya dari kolom 4 sampai sebelum kolom "KETERANGAN"
        // (rekap: kolom 4..18 angka; shift: kolom 4 SHIFT angka juga ok)
        // aman: set general numeric format untuk kolom 4..(colCount-2)
        if ($colCount >= 6) {
            $from = 4;
            $to = $colCount - 2;
            if ($to >= $from) {
                $fromCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($from);
                $toCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($to);
                $sheet->getStyle("{$fromCol}2:{$toCol}{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');
                $sheet->getStyle("{$fromCol}2:{$toCol}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }
        }
    }

    public function index(Request $request)
    {
        return view('Investor.Inventory.qualityCostReport', $this->buildQcrData($request));
    }
    
    private function isTepungBreader($nama): bool
    {
        $n = strtolower(trim((string) $nama));
        $n = preg_replace('/\s+/', ' ', $n);

        return str_contains($n, 'tepung breader')
            || str_contains($n, 'breader');
    }

    private function getQcrHiddenMap(string $outletKey, string $startDate, string $endDate, string $jenis): array
    {
        return DB::table('tbl_qcr_hidden_items')
            ->where('outlet_key', $outletKey)
            ->where('start_date', $startDate)
            ->where('end_date', $endDate)
            ->where('jenis', $jenis)
            ->get()
            ->keyBy('reference_key')
            ->all();
    }

    public function saveHiddenItems(Request $request)
    {
        $request->validate([
            'outlet_key' => 'required|string|max:100',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'jenis' => 'required|in:plus,minus',
            'items' => 'required|array',
            'items.*.reference_key' => 'required|string|max:64',
            'items.*.reference_name' => 'required|string|max:255',
            'items.*.qty_abs' => 'nullable|numeric|min:0',
            'items.*.qty_hidden' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $qtyAbs = (float) ($item['qty_abs'] ?? 0);
                $hiddenQty = (float) ($item['qty_hidden'] ?? 0);

                if ($hiddenQty < 0) {
                    $hiddenQty = 0;
                }

                if ($hiddenQty > $qtyAbs) {
                    $hiddenQty = $qtyAbs;
                }

                DB::table('tbl_qcr_hidden_items')->updateOrInsert(
                    [
                        'outlet_key'    => (string) $request->outlet_key,
                        'start_date'    => (string) $request->start_date,
                        'end_date'      => (string) $request->end_date,
                        'jenis'         => (string) $request->jenis,
                        'reference_key' => (string) $item['reference_key'],
                    ],
                    [
                        'reference_name' => (string) $item['reference_name'],
                        'hidden_qty'     => $hiddenQty,
                        'hidden_all'     => ($qtyAbs > 0 && $hiddenQty >= $qtyAbs) ? 1 : 0,
                        'created_by'     => auth()->id(),
                        'updated_at'     => now(),
                        'created_at'     => now(),
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Hidden items berhasil disimpan.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'Gagal simpan hidden items.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function saveUangPlus(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|string|max:120',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'uang_plus' => 'nullable|numeric|min:0',
            'idempotency_key' => 'nullable|string|max:120',
            'items' => 'required|array|min:1',
            'items.*.nama_menu' => 'required|string|max:255',
            'items.*.tipe' => 'nullable|string|max:100',
            'items.*.harga' => 'required|numeric|min:0',
            'items.*.qty' => 'required|numeric|min:1',
        ]);

        $outletIdRaw = (string) $request->outlet_id;
        $startDate = $this->normalizeDateToYmd((string) $request->start_date);
        $endDate = $this->normalizeDateToYmd((string) $request->end_date);
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if ($outletIdRaw === '' || $outletIdRaw === 'all') {
            return response()->json([
                'ok' => false,
                'message' => 'Penukaran uang plus wajib pilih satu outlet / satu grup outlet. Tidak boleh mode Semua Outlet.',
            ], 422);
        }

        $outletIds = $this->resolveQcrOutletIdsFromRequest($outletIdRaw);
        if (empty($outletIds)) {
            return response()->json([
                'ok' => false,
                'message' => 'Outlet tidak valid.',
            ], 422);
        }

        $items = collect($request->items)
            ->map(function ($item) {
                $namaMenu = trim((string) ($item['nama_menu'] ?? ''));
                $tipe = trim((string) ($item['tipe'] ?? 'Regular'));
                $harga = (float) ($item['harga'] ?? 0);
                $qty = (float) ($item['qty'] ?? 0);

                return [
                    'nama_menu' => $namaMenu,
                    'tipe' => $tipe !== '' ? $tipe : 'Regular',
                    'harga' => $harga,
                    'qty' => $qty,
                    'subtotal' => $harga * $qty,
                ];
            })
            ->filter(fn ($item) => $item['nama_menu'] !== '' && $item['qty'] > 0 && $item['harga'] >= 0)
            ->values();

        if ($items->isEmpty()) {
            return response()->json([
                'ok' => false,
                'message' => 'Tidak ada item yang dibelanjakan.',
            ], 422);
        }

        $totalBelanja = (float) $items->sum('subtotal');
        if ($totalBelanja <= 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Total belanja harus lebih dari 0.',
            ], 422);
        }

        $idempotencyKey = trim((string) $request->get('idempotency_key', ''));
        if ($idempotencyKey === '') {
            $idempotencyKey = 'UPLUS-' . now()->format('YmdHis') . '-' . substr(md5(json_encode($items->all()) . auth()->id()), 0, 10);
        }

        DB::beginTransaction();
        try {
            $stockRows = DB::table('tbl_stock')
                ->whereIn('outlet_id', $outletIds)
                ->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
                ->where('uang_plus', '>', 0)
                ->orderByDesc('tanggal')
                ->orderByDesc('shift')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get(['id', 'outlet_id', 'tanggal', 'shift', 'uang_plus']);

            $saldoServer = (float) $stockRows->sum('uang_plus');
            if ($saldoServer <= 0) {
                DB::rollBack();
                return response()->json([
                    'ok' => false,
                    'message' => 'Saldo uang plus di server sudah kosong. Refresh QCR lalu cek ulang.',
                ], 422);
            }

            if ($totalBelanja > $saldoServer) {
                DB::rollBack();
                return response()->json([
                    'ok' => false,
                    'message' => 'Uang plus tidak mencukupi. Saldo server: Rp ' . number_format($saldoServer, 0, ',', '.') . ', kebutuhan: Rp ' . number_format($totalBelanja, 0, ',', '.') . '.',
                ], 422);
            }

            $remaining = $totalBelanja;
            $deductions = [];
            foreach ($stockRows as $row) {
                if ($remaining <= 0) {
                    break;
                }

                $available = (float) ($row->uang_plus ?? 0);
                if ($available <= 0) {
                    continue;
                }

                $take = min($available, $remaining);
                $newValue = $available - $take;

                DB::table('tbl_stock')
                    ->where('id', (int) $row->id)
                    ->update([
                        'uang_plus' => $newValue,
                        'updated_at' => now(),
                    ]);

                $deductions[] = [
                    'stock_id' => (int) $row->id,
                    'outlet_id' => (int) $row->outlet_id,
                    'tanggal' => (string) $row->tanggal,
                    'shift' => (int) $row->shift,
                    'old_value' => $available,
                    'deducted' => $take,
                    'new_value' => $newValue,
                ];

                $remaining -= $take;
            }

            if ($remaining > 0.0001) {
                throw new \RuntimeException('Saldo uang plus gagal dikurangi seluruhnya.');
            }

            $trxOutletId = (int) ($deductions[0]['outlet_id'] ?? $outletIds[0]);
            $trxDate = $endDate;
            $trxTime = now()->format('H:i:s');

            foreach ($items as $idx => $item) {
                $this->insertQcrUangPlusTransaction([
                    'nomor' => $idempotencyKey . '-' . ($idx + 1),
                    'outlet_id' => $trxOutletId,
                    'sesi_tanggal' => $trxDate,
                    'tr_waktu' => $trxTime,
                    'item_nama' => $item['nama_menu'],
                    'item_varian' => $item['tipe'],
                    'item_jumlah' => $item['qty'],
                    'item_harga' => $item['harga'],
                    'item_sub_total' => $item['subtotal'],
                    'item_status' => 8,
                ]);
            }

            $this->storeQcrUangPlusAuditIfTableExists([
                'idempotency_key' => $idempotencyKey,
                'outlet_id' => $trxOutletId,
                'outlet_ids' => json_encode($outletIds),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'saldo_awal' => $saldoServer,
                'total_belanja' => $totalBelanja,
                'saldo_sisa' => $saldoServer - $totalBelanja,
                'items' => $items->all(),
                'deductions' => $deductions,
            ]);

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Uang plus berhasil ditukar. Saldo uang plus berkurang dan transaksi QCR otomatis ditambahkan.',
                'data' => [
                    'saldo_awal' => $saldoServer,
                    'total_belanja' => $totalBelanja,
                    'sisa' => $saldoServer - $totalBelanja,
                    'idempotency_key' => $idempotencyKey,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan belanja uang plus.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function resolveQcrOutletIdsFromRequest(string $outletIdRaw): array
    {
        $normalize = function ($name) {
            $name = strtoupper(trim((string) $name));
            return preg_replace('/\s+/', ' ', $name);
        };

        $rows = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('id')
            ->get();

        if (str_starts_with($outletIdRaw, 'group_')) {
            foreach ($rows->groupBy(fn ($o) => $normalize($o->nama_outlet)) as $name => $groupRows) {
                if ('group_' . md5($name) === $outletIdRaw) {
                    return $groupRows->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                }
            }
            return [];
        }

        $single = $rows->firstWhere('id', (int) $outletIdRaw);
        if (! $single) {
            return [];
        }

        $nameNorm = $normalize($single->nama_outlet);
        return $rows
            ->filter(fn ($o) => $normalize($o->nama_outlet) === $nameNorm)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function insertQcrUangPlusTransaction(array $data): void
    {
        if (! Schema::hasTable('tbl_transaksi_perhari')) {
            throw new \RuntimeException('Tabel tbl_transaksi_perhari tidak ditemukan.');
        }

        $insert = [];
        foreach ($data as $column => $value) {
            if (Schema::hasColumn('tbl_transaksi_perhari', $column)) {
                $insert[$column] = $value;
            }
        }

        if (Schema::hasColumn('tbl_transaksi_perhari', 'created_at')) {
            $insert['created_at'] = now();
        }
        if (Schema::hasColumn('tbl_transaksi_perhari', 'updated_at')) {
            $insert['updated_at'] = now();
        }

        if (! isset($insert['item_nama']) || ! isset($insert['item_sub_total'])) {
            throw new \RuntimeException('Struktur tbl_transaksi_perhari tidak sesuai untuk transaksi uang plus.');
        }

        DB::table('tbl_transaksi_perhari')->insert($insert);
    }

    private function storeQcrUangPlusAuditIfTableExists(array $data): void
    {
        if (! Schema::hasTable('tbl_qcr_uang_plus')) {
            return;
        }

        $header = [
            'idempotency_key' => $data['idempotency_key'],
            'outlet_id' => $data['outlet_id'],
            'outlet_ids' => $data['outlet_ids'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'saldo_awal' => $data['saldo_awal'],
            'total_belanja' => $data['total_belanja'],
            'saldo_sisa' => $data['saldo_sisa'],
            'deductions_json' => json_encode($data['deductions']),
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $safeHeader = [];
        foreach ($header as $column => $value) {
            if (Schema::hasColumn('tbl_qcr_uang_plus', $column)) {
                $safeHeader[$column] = $value;
            }
        }

        $headerId = DB::table('tbl_qcr_uang_plus')->insertGetId($safeHeader);

        if (! Schema::hasTable('tbl_qcr_uang_plus_items')) {
            return;
        }

        foreach ($data['items'] as $item) {
            $detail = [
                'uang_plus_id' => $headerId,
                'nama_menu' => $item['nama_menu'],
                'tipe' => $item['tipe'],
                'harga' => $item['harga'],
                'qty' => $item['qty'],
                'subtotal' => $item['subtotal'],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $safeDetail = [];
            foreach ($detail as $column => $value) {
                if (Schema::hasColumn('tbl_qcr_uang_plus_items', $column)) {
                    $safeDetail[$column] = $value;
                }
            }

            DB::table('tbl_qcr_uang_plus_items')->insert($safeDetail);
        }
    }

    private function applyHiddenToDifference(float $rawQty, float $hiddenQty = 0, bool $hiddenAll = false): array
    {
        $rawAbs = abs($rawQty);
        $sign = $rawQty < 0 ? -1 : 1;

        if ($hiddenAll) {
            return [
                'raw_qty' => $rawQty,
                'raw_abs' => $rawAbs,
                'hidden_qty' => $rawAbs,
                'visible_qty' => 0.0,
                'visible_signed_qty' => 0.0,
            ];
        }

        $hiddenQty = max(0, $hiddenQty);
        $hiddenQty = min($hiddenQty, $rawAbs);

        $visibleAbs = max(0, $rawAbs - $hiddenQty);
        $visibleSigned = $visibleAbs * $sign;

        return [
            'raw_qty' => $rawQty,
            'raw_abs' => $rawAbs,
            'hidden_qty' => $hiddenQty,
            'visible_qty' => $visibleAbs,
            'visible_signed_qty' => $visibleSigned,
        ];
    }

    private function getQcrMergedStockRows(array $outletIds, string $startDate, string $endDate, bool $isAllOutlet)
    {
        $finalRows = DB::table('tbl_stock as s')
            ->join('tbl_bahan_dsc as bd', 'bd.id', '=', 's.bahan_id')
            ->select(
                's.id',
                's.outlet_id',
                's.bahan_id',
                's.tanggal',
                's.shift',
                's.opening_stock',
                's.purchase_in',
                's.mutasi_in',
                's.mutasi_out',
                's.adjustment_qty',
                's.used_qty',
                's.ending_stock',
                's.waste_product',
                's.waste_bahan',
                's.waste_tepung',
                's.uang_plus',
                'bd.nama_bahan as bahan_nama',
                'bd.satuan as bahan_satuan',
                DB::raw("'final' as row_source")
            )
            ->when(!$isAllOutlet, fn ($q) => $q->whereIn('s.outlet_id', $outletIds))
            ->whereBetween(DB::raw('DATE(s.tanggal)'), [$startDate, $endDate])
            ->get();

        $draftRows = DB::table('tbl_stock_draft as s')
            ->join('tbl_bahan_dsc as bd', 'bd.id', '=', 's.bahan_id')
            ->select(
                's.id',
                's.outlet_id',
                's.bahan_id',
                's.tanggal',
                's.shift',
                's.opening_stock',
                's.purchase_in',
                's.mutasi_in',
                's.mutasi_out',
                's.adjustment_qty',
                's.used_qty',
                's.ending_stock',
                's.waste_product',
                's.waste_bahan',
                's.waste_tepung',
                's.uang_plus',
                'bd.nama_bahan as bahan_nama',
                'bd.satuan as bahan_satuan',
                DB::raw("'draft' as row_source")
            )
            ->when(!$isAllOutlet, fn ($q) => $q->whereIn('s.outlet_id', $outletIds))
            ->whereBetween(DB::raw('DATE(s.tanggal)'), [$startDate, $endDate])
            ->where('s.is_draft', 1)
            ->get();

        $merged = collect();

        foreach ($finalRows as $row) {
            $key = implode('|', [
                (int) $row->outlet_id,
                (string) \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d'),
                (int) $row->shift,
                (int) $row->bahan_id,
            ]);

            $merged->put($key, $row);
        }

        foreach ($draftRows as $row) {
            $key = implode('|', [
                (int) $row->outlet_id,
                (string) \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d'),
                (int) $row->shift,
                (int) $row->bahan_id,
            ]);

            $existingFinal = $merged->get($key);

            // Kalau draft opening kosong, ambil opening dari final yang sama
            if (
                $existingFinal &&
                ((float) ($row->opening_stock ?? 0) == 0) &&
                ((float) ($existingFinal->opening_stock ?? 0) != 0)
            ) {
                $row->opening_stock = (float) $existingFinal->opening_stock;
            }

            // Draft overwrite Final
            $merged->put($key, $row);
        }

        return $merged
            ->values()
            ->sortBy([
                fn ($a, $b) => strcmp((string) $a->tanggal, (string) $b->tanggal),
                fn ($a, $b) => ((int) $a->shift <=> (int) $b->shift),
                fn ($a, $b) => ((int) $a->id <=> (int) $b->id),
            ])
            ->values();
    }

    private function buildQcrStockAggByBahanId($stockRows)
    {
        return $stockRows
            ->groupBy(fn ($r) => (int) $r->bahan_id)
            ->map(function ($grp) {
                $sorted = $grp->sortBy([
                    fn ($a, $b) => strcmp((string) $a->tanggal, (string) $b->tanggal),
                    fn ($a, $b) => ((int) $a->shift <=> (int) $b->shift),
                    fn ($a, $b) => ((int) $a->id <=> (int) $b->id),
                ])->values();

                $first = $sorted->first();

                $bahanId = (int) ($first->bahan_id ?? 0);
                $firstDate = \Carbon\Carbon::parse($first->tanggal)->format('Y-m-d');

                /*
                 |--------------------------------------------------------------------------
                 | FIX QCR SUMMARY USAGE DSC
                 |--------------------------------------------------------------------------
                 | Summary QCR harus mengikuti rumus Rekap Daily Stock Control mode SEMUA.
                 |
                 | Stock Available = OPEN shift 1 / ending H-1
                 | TOTAL           = Stock Available + Purchase In + Mutasi In - Mutasi Out
                 | Usage DSC       = TOTAL - Ending
                 |
                 | Jadi Usage DSC bukan hanya used shift terakhir.
                 | Contoh:
                 | Shift 1 used = 110
                 | Shift 2 used = 130
                 | Usage DSC Summary = 240
                 */

                // Stock Available QCR = OPEN Shift 1 / ending H-1.
                $available = (float) $this->getOpeningStockUi(
                    (int) $first->outlet_id,
                    $bahanId,
                    $firstDate,
                    1
                );

                $opening = $available;

                $purchase = (float) $sorted->sum(fn ($r) => (float) ($r->purchase_in ?? 0));
                $mutIn    = (float) $sorted->sum(fn ($r) => (float) ($r->mutasi_in ?? 0));
                $mutOut   = (float) $sorted->sum(fn ($r) => (float) ($r->mutasi_out ?? 0));
                $adj      = (float) $sorted->sum(fn ($r) => (float) ($r->adjustment_qty ?? 0));

                $endingRow = $sorted
                    ->filter(fn ($r) => $r->ending_stock !== null)
                    ->last();

                $endingBase = $endingRow
                    ? (float) ($endingRow->ending_stock ?? 0)
                    : 0.0;

                // ADJ sebagai koreksi ending, bukan masuk total.
                $ending = $endingBase + $adj;

                $wasteProduct = (float) $sorted->sum(fn ($r) => (float) ($r->waste_product ?? 0));
                $wasteBahan   = (float) $sorted->sum(fn ($r) => (float) ($r->waste_bahan ?? 0));
                $wasteTepung  = (float) $sorted->sum(fn ($r) => (float) ($r->waste_tepung ?? 0));

                $wasteQty = $wasteProduct;

                // TOTAL mengikuti rumus final:
                // TOTAL = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
                // ADJ tidak masuk TOTAL.
                $totalAvailable = $available + $purchase + $mutIn - $mutOut;

                // Usage DSC Summary mengikuti kalkulasi gabungan shift:
                // USED = TOTAL - ENDING
                $usage = $totalAvailable - $ending;

                return (object) [
                    'bahan_id'     => $bahanId,
                    'bahan_nama'   => (string) ($first->bahan_nama ?? ''),
                    'bahan_satuan' => (string) ($first->bahan_satuan ?? ''),

                    'opening'      => $opening,
                    'ending'       => $ending,
                    'purchase'     => $purchase,
                    'mut_in'       => $mutIn,
                    'mut_out'      => $mutOut,
                    'adjustment'   => $adj,

                    'waste_product_qty' => $wasteProduct,
                    'waste_bahan_qty'   => $wasteBahan,
                    'waste_tepung_qty'  => $wasteTepung,

                    'waste_qty'    => $wasteQty,

                    'available'       => $available,
                    'stock_available' => $available,

                    'total_available' => $totalAvailable,

                    // Ini yang tampil sebagai Usage DSC di Summary QCR.
                    'usage_stock'     => $usage,
                ];
            });
    }

    public function buildQcrData(Request $request): array
    {
        $outletId    = $request->get('outlet_id', '');
        $start_date  = $request->get('start_date', '');
        $end_date    = $request->get('end_date', '');

        $filterApplied = $request->filled('outlet_id')
            && $request->filled('start_date')
            && $request->filled('end_date');

        if (!$filterApplied) {
            $outletId = '';
            $start_date = '';
            $end_date = '';
        }

        $allOutletsRaw = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->orderBy('id')
            ->get();

        $normalizeOutletName = function ($name) {
            $name = strtoupper(trim((string) $name));
            $name = preg_replace('/\s+/', ' ', $name);
            return $name;
        };

        $outletGroups = [];

        foreach ($allOutletsRaw->groupBy(fn ($o) => $normalizeOutletName($o->nama_outlet)) as $name => $rows) {
            $ids = $rows->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $groupKey = 'group_' . md5($name);

            $outletGroups[$groupKey] = [
                'label' => $name,
                'ids'   => $ids,
            ];
        }

        $outlets = collect();

        if ($outletId === 'all') {
            $outletIds = $allOutletsRaw->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $selectedOutletLabel = 'SEMUA OUTLET';
        } elseif (isset($outletGroups[$outletId])) {
            $outletIds = $outletGroups[$outletId]['ids'];
            $selectedOutletLabel = $outletGroups[$outletId]['label'];
        } else {
            $outletIds = [(int) $outletId];
            $selectedOutletLabel = optional($allOutletsRaw->firstWhere('id', (int) $outletId))->nama_outlet;
            $selectedOutletLabel = $selectedOutletLabel
                ? strtoupper(trim((string) $selectedOutletLabel))
                : 'OUTLET';
        }

        $isAllOutlet = $outletId === 'all';

        $rangeDays    = 1;
        $forecastDays = 1;
        $forecastFrom = now();
        $forecastTo   = now();

        if ($filterApplied) {
            $startCarbon  = \Carbon\Carbon::parse($start_date);
            $endCarbon    = \Carbon\Carbon::parse($end_date);
            $rangeDays    = $startCarbon->diffInDays($endCarbon) + 1;
            $forecastDays = $rangeDays;
            $forecastFrom = $endCarbon->copy()->addDay();
            $forecastTo   = $endCarbon->copy()->addDays($forecastDays);
        }

        $allOutlets = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->orderBy('nama_outlet')
            ->orderBy('id')
            ->get()
            ->map(function ($outlet) {
                return (object) [
                    'id' => (int) $outlet->id,
                    'nama_outlet' => (string) $outlet->nama_outlet,
                    'nama_outlet_display' => strtoupper(trim((string) $outlet->nama_outlet)) . ' [ID: ' . (int) $outlet->id . ']',
                ];
            });

        $outlets = $allOutlets;

        // TOTAL SALES FINAL: pakai tbl_laporan_bulanan
        $totalSalesBulanan = (float) DB::table('tbl_transaksi_perhari')
        ->when(!$isAllOutlet, fn ($q) => $q->whereIn('outlet_id', $outletIds))
        ->whereBetween(DB::raw('DATE(sesi_tanggal)'), [$start_date, $end_date])
        ->where('item_nama', '__TRANSACTION__')
        ->where('item_status', 8)
        ->sum('item_sub_total');

        // Harga bahan
        if (!$isAllOutlet && count($outletIds) === 1) {
            $selectedOutletId = (int) $outletIds[0];

            $bahanPrice = DB::table('tbl_bahan as b')
                ->leftJoin('tbl_bahan_harga_outlet as bho', function ($join) use ($selectedOutletId) {
                    $join->on('bho.bahan_id', '=', 'b.id')
                        ->where('bho.outlet_id', '=', $selectedOutletId);
                })
                ->select(
                    'b.id',
                    'b.nama_bahan',
                    DB::raw('COALESCE(bho.harga_bahan, b.harga_bahan) as harga_bahan'),
                    'b.satuan',
                    'b.konversi',
                    'b.isi_per_unit'
                )
                ->orderBy('b.nama_bahan')
                ->get();
        } else {
            $bahanPrice = DB::table('tbl_bahan')
                ->select('id', 'nama_bahan', 'harga_bahan', 'satuan', 'konversi', 'isi_per_unit')
                ->orderBy('nama_bahan')
                ->get();
        }

        $priceMap = [];
        foreach ($bahanPrice as $b) {
            $priceMap[$this->normName($b->nama_bahan)] = $b;
        }

        $menuData = [];
        $bahanSummary = [];
        $visibleBahanNames = [];

        $summary = [
            'sales' => 0,
            'hpp' => 0,
            'hpp_percent' => 0,
            'profit' => 0,
            'profit_percent' => 0,
            'waste' => 0,
            'waste_percent' => 0,
            'selisih_net' => 0,
            'selisih_loss' => 0,
            'selisih_gain' => 0,
            'selisih_percent' => 0,
            'quality_cost' => 0,
            'quality_cost_percent' => 0,
        ];

        // Summary pembanding:
        // - $summaryNormal = data asli sebelum data PLUS/MINUS di-hidden.
        // - $summarySetelahHapus = data setelah qty hidden PLUS/MINUS diterapkan.
        // Default disamakan dulu agar view tetap aman saat filter belum dipakai / data kosong.
        $summaryNormal = $summary;
        $summarySetelahHapus = $summary;

        // Data QCR Bahan Non-BOM / Operasional.
        // Default harus selalu ada supaya Blade aman walaupun filter belum dipakai / data kosong.
        $bahanNonBomRows = [];
        $totalNonBomLoss = 0.0;
        $nonBomMinusCount = 0;
        $nonBomPlusCount = 0;
        $nonBomOpsionalCount = 0;

        if (!$filterApplied) {
            return compact(
                'menuData',
                'bahanPrice',
                'bahanSummary',
                'outlets',
                'outletGroups',
                'outletId',
                'start_date',
                'end_date',
                'summary',
                'summaryNormal',
                'summarySetelahHapus',
                'visibleBahanNames',
                'rangeDays',
                'forecastDays',
                'forecastFrom',
                'forecastTo',
                'bahanNonBomRows',
                'totalNonBomLoss',
                'nonBomMinusCount',
                'nonBomPlusCount',
                'nonBomOpsionalCount'
            ) + [
                'qcrPlusItems' => [],
                'qcrMinusItems' => [],
                'totalUangPlus' => 0,
                'filterApplied' => false,
            ];
        }

        $menuMapping = [
            'Extra Saos Korea' => 'EXTRA SAOS GANGNAM',
            'PAKET GANGNAM HEMAT' => 'Paket Gangnam Hemat',
            'PAKET GANGNAM JUMBO' => 'Paket Gangnam Jumbo',
            'STRAWBERRY' => 'Strawbery',
        ];

        /*
        |--------------------------------------------------------------------------
        | SOURCE MENU QCR: tbl_transaksi_perhari
        |--------------------------------------------------------------------------
        | FIX:
        | - Hapus unique() lama, karena bisa membuang transaksi valid yang kebetulan
        |   punya menu, tipe, harga, qty, subtotal, dan jam yang sama.
        | - Ambil nomor agar row tetap traceable.
        */
        $transaksi = DB::table('tbl_transaksi_perhari')
            ->when(!$isAllOutlet, fn ($q) => $q->whereIn('outlet_id', $outletIds))
            ->whereBetween(DB::raw('DATE(sesi_tanggal)'), [$start_date, $end_date])
            ->whereIn('item_status', [8])
            ->whereNotNull('item_nama')
            ->whereRaw("TRIM(item_nama) <> ''")
            ->where('item_nama', '!=', '__TRANSACTION__')
            ->get([
                'nomor',
                'outlet_id',
                'sesi_tanggal',
                'tr_waktu',
                'item_nama',
                'item_jumlah',
                'item_harga',
                'item_sub_total',
                'item_varian',
                'item_status',
            ]);

        if ($transaksi->isEmpty()) {
            return compact(
                'menuData',
                'bahanPrice',
                'bahanSummary',
                'outlets',
                'outletGroups',
                'outletId',
                'start_date',
                'end_date',
                'summary',
                'summaryNormal',
                'summarySetelahHapus',
                'visibleBahanNames',
                'rangeDays',
                'forecastDays',
                'forecastFrom',
                'forecastTo',
                'bahanNonBomRows',
                'totalNonBomLoss',
                'nonBomMinusCount',
                'nonBomPlusCount',
                'nonBomOpsionalCount'
            ) + [
                'qcrPlusItems' => [],
                'qcrMinusItems' => [],
                'totalUangPlus' => 0,
                'filterApplied' => $filterApplied,
            ];
        }

        $normalized = $transaksi->map(function ($t) use ($menuMapping) {
            $raw  = trim((string) ($t->item_nama ?? ''));
            $nama = $menuMapping[$raw] ?? $raw;

            $tipeRaw = trim((string) ($t->item_varian ?? ''));
            $tipe = $tipeRaw !== '' ? $tipeRaw : 'Regular';

            return (object) [
                'nomor'          => (string) ($t->nomor ?? ''),
                'item_nama'      => $nama,
                'item_nama_norm' => $this->normName($nama),
                'tipe'           => $tipe,
                'tipe_norm'      => $this->normName($tipe),
                'qty'            => is_numeric($t->item_jumlah) ? (float) $t->item_jumlah : 0,
                'harga'          => is_numeric($t->item_harga) ? (float) $t->item_harga : 0,
                'sub_total'      => is_numeric($t->item_sub_total) ? (float) $t->item_sub_total : 0,
            ];
        })->filter(function ($t) {
            return $t->item_nama !== ''
                && $t->item_nama !== '__TRANSACTION__'
                && !preg_match('/^__.*__$/', $t->item_nama);
        })->values();

        /*
        |--------------------------------------------------------------------------
        | REKAP MENU
        |--------------------------------------------------------------------------
        | Grouping:
        | - item_nama
        | - item_varian / tipe
        | - item_harga
        |
        | Total per menu:
        | - Pakai qty x harga agar sama dengan export ESB Qty x Unit Price.
        */
        $rekapTransaksi = $normalized
            ->groupBy(function ($row) {
                return implode('||', [
                    $row->item_nama_norm,
                    $row->tipe_norm,
                    number_format((float) $row->harga, 2, '.', ''),
                ]);
            })
            ->map(function ($rows) {
                $first = $rows->first();

                $totalQty = (float) $rows->sum('qty');

                $totalSales = (float) $rows->sum(function ($r) {
                    return ((float) $r->qty) * ((float) $r->harga);
                });

                return (object) [
                    'item_nama'      => $first->item_nama,
                    'item_nama_norm' => $first->item_nama_norm,
                    'tipe'           => $first->tipe,
                    'tipe_norm'      => $first->tipe_norm,
                    'total_qty'      => $totalQty,
                    'total_sales'    => $totalSales,
                    'harga'          => (float) $first->harga,
                    'total_baris'    => $rows->count(),
                ];
            })
            ->values();

        $totalSalesTransaksi = (float) $rekapTransaksi->sum('total_sales');

        $menuMasterByNorm = DB::table('tbl_menu')
            ->select('id', 'item_produk')
            ->get()
            ->map(function ($m) {
                return (object) [
                    'id' => $m->id,
                    'item_produk' => $m->item_produk,
                    'item_produk_norm' => $this->normName($m->item_produk),
                ];
            })
            ->groupBy('item_produk_norm')
            ->map(function ($rows) {
                return $rows->sortBy('id')->first();
            });

        $menuIdByNorm = [];
        foreach ($rekapTransaksi as $t) {
            if (isset($menuMasterByNorm[$t->item_nama_norm])) {
                $menuIdByNorm[$t->item_nama_norm] = $menuMasterByNorm[$t->item_nama_norm]->id;
            }
        }

        $menuIds = collect($menuIdByNorm)->values()->unique()->values();

        $menuBahanRows = collect();
        if ($menuIds->isNotEmpty()) {
            $menuBahanRows = DB::table('tbl_menu_bahan as mb')
                ->join('tbl_bahan as b', 'b.id', '=', 'mb.bahan_id')
                ->select(
                    'mb.menu_id',
                    'b.nama_bahan',
                    'b.harga_bahan',
                    'b.satuan',
                    'b.konversi',
                    'b.isi_per_unit',
                    DB::raw('MAX(COALESCE(mb.qty, 0)) as qty')
                )
                ->whereIn('mb.menu_id', $menuIds)
                ->groupBy(
                    'mb.menu_id',
                    'b.id',
                    'b.nama_bahan',
                    'b.harga_bahan',
                    'b.satuan',
                    'b.konversi',
                    'b.isi_per_unit'
                )
                ->get()
                ->groupBy('menu_id');
        }

        $toBase = function ($qty, $satuan, $konversi) {
            return match (strtolower($satuan ?? '')) {
                'kg'    => $qty * 1000,
                'ton'   => $qty * 1000000,
                'liter' => $qty * 1000,
                default => $qty * ($konversi ?: 1),
            };
        };

        foreach ($rekapTransaksi as $t) {
            $itemNama   = $t->item_nama;
            $itemNorm   = $t->item_nama_norm;
            $itemTipe   = $t->tipe;
            $jumlah     = (float) $t->total_qty;
            $itemHarga  = (float) $t->harga;
            $itemSales  = (float) $t->total_sales;

            $rowKey = implode('||', [
                $itemNorm,
                $this->normName($itemTipe),
                number_format($itemHarga, 2, '.', ''),
            ]);

            if (!isset($menuData[$rowKey])) {
                $menuData[$rowKey] = [
                    'nama_menu'   => $itemNama,
                    'tipe'        => $itemTipe,
                    'unit_sold'   => 0,
                    'harga'       => $itemHarga,
                    'total_sales' => 0,
                    'bahan'       => [],
                ];
            }

            $menuData[$rowKey]['unit_sold'] += $jumlah;
            $menuData[$rowKey]['total_sales'] += $itemSales;

            $menuId = $menuIdByNorm[$itemNorm] ?? null;
            if (!$menuId) {
                continue;
            }

            $rows = $menuBahanRows[$menuId] ?? collect();

            foreach ($rows as $bhn) {
                $namaBahan = (string) $bhn->nama_bahan;

                if ($this->isTepungBreader($namaBahan)) {
                    continue;
                }

                $pakai     = ((float) ($bhn->qty ?? 0)) * $jumlah;
                $pakaiBase = $toBase($pakai, $bhn->satuan, $bhn->konversi);

                $menuData[$rowKey]['bahan'][$namaBahan] =
                    ($menuData[$rowKey]['bahan'][$namaBahan] ?? 0) + $pakaiBase;

                if (!in_array($namaBahan, $visibleBahanNames, true)) {
                    $visibleBahanNames[] = $namaBahan;
                }
            }
        }

        if (!empty($menuData)) {
            uasort($menuData, function ($a, $b) {
                $cmpMenu = strcmp(
                    strtolower((string) ($a['nama_menu'] ?? '')),
                    strtolower((string) ($b['nama_menu'] ?? ''))
                );

                if ($cmpMenu !== 0) {
                    return $cmpMenu;
                }

                $cmpTipe = strcmp(
                    strtolower((string) ($a['tipe'] ?? '')),
                    strtolower((string) ($b['tipe'] ?? ''))
                );

                if ($cmpTipe !== 0) {
                    return $cmpTipe;
                }

                return ((float) ($a['harga'] ?? 0)) <=> ((float) ($b['harga'] ?? 0));
            });
        }

        $stockRows = $this->getQcrMergedStockRows(
            $outletIds,
            $start_date,
            $end_date,
            $isAllOutlet
        );

        $totalUangPlus = (float) DB::table('tbl_stock')
            ->when(!$isAllOutlet, fn ($q) => $q->whereIn('outlet_id', $outletIds))
            ->whereBetween(DB::raw('DATE(tanggal)'), [$start_date, $end_date])
            ->sum('uang_plus');

        $bahanIdsInRange = $stockRows->pluck('bahan_id')->unique()->filter()->values();

        $prevRows = collect();
        if ($bahanIdsInRange->isNotEmpty()) {
            $prevRows = DB::table('tbl_stock')
                ->select('bahan_id', 'ending_stock', 'tanggal', 'shift', 'id')
                ->when(!$isAllOutlet, fn ($q) => $q->whereIn('outlet_id', $outletIds))
                ->whereIn('bahan_id', $bahanIdsInRange)
                ->where(DB::raw('DATE(tanggal)'), '<', $start_date)
                ->orderBy('tanggal', 'desc')
                ->orderBy('shift', 'desc')
                ->orderBy('id', 'desc')
                ->get();
        }

        $prevClosingByBahanId = [];
        foreach ($prevRows as $r) {
            $bid = (int) ($r->bahan_id ?? 0);
            if ($bid && !isset($prevClosingByBahanId[$bid])) {
                $prevClosingByBahanId[$bid] = (float) ($r->ending_stock ?? 0);
            }
        }

        $stockAggByBahanId = $this->buildQcrStockAggByBahanId($stockRows)
            ->reject(fn ($agg) => $this->isTepungBreader($agg->bahan_nama ?? ''));

        $stockAggByNamaNorm = collect();
        foreach ($stockAggByBahanId as $agg) {
            $stockAggByNamaNorm[$this->normName($agg->bahan_nama)] = $agg;
        }

        foreach ($visibleBahanNames as $namaBahan) {
            $k   = $this->normName($namaBahan);
            $agg = $stockAggByNamaNorm->get($k);

            $priceRow = $priceMap[$k] ?? null;
            $hargaPerBase = 0.0;

            if ($priceRow) {
                $isi = max((float) ($priceRow->isi_per_unit ?? 1), 1);
                $kon = max((float) ($priceRow->konversi ?? 1), 1);
                $hargaPerBase = ((float) $priceRow->harga_bahan / $isi) / $kon;
            }

            $available  = (float) ($agg->available ?? 0);
            $usageStock = (float) ($agg->usage_stock ?? 0);

            $wasteProduct = (float) ($agg->waste_product_qty ?? 0);
            $wasteBahan   = (float) ($agg->waste_bahan_qty ?? 0);
            $wasteTepung  = (float) ($agg->waste_tepung_qty ?? 0);

            $wasteQty = $wasteProduct + $wasteBahan;

            $bahanSummary[$namaBahan] = [
                'qty_resep'       => 0,
                'hpp'             => 0,
                'harga'           => $hargaPerBase,
                'satuan'          => $agg->bahan_satuan ?? ($priceRow->satuan ?? null),
                // Stock (Available) = ending stock tanggal sebelumnya, bukan used Shift 2 / ending tanggal aktif.
                'stock'           => (float) ($agg->stock_available ?? $agg->ending ?? $available),
                'stock_available' => (float) ($agg->stock_available ?? $agg->ending ?? $available),
                'qty_stock'       => $usageStock,
                'opening_stock'   => (float) ($agg->opening ?? 0),
                'ending_stock'    => (float) ($agg->ending ?? 0),
                'total_available' => (float) ($agg->total_available ?? 0),
                'waste_product_qty' => $wasteProduct,
                'waste_bahan_qty'   => $wasteBahan,
                'waste_tepung_qty'  => $wasteTepung,
                'waste_qty'         => $wasteQty,
                'waste_rp'          => $wasteQty * $hargaPerBase,
                'waste_tepung_rp'   => $wasteTepung * $hargaPerBase,
                'avg_per_day'     => 0,
                'forecast_days'   => $forecastDays,
                'forecast_qty'    => 0,
                'forecast_stock'  => 0,
                'forecast_diff'   => 0,
                'forecast_hpp'    => 0,
                'diff_raw_qty'    => 0,
                'diff_hidden_qty' => 0,
                'diff_visible_qty'=> 0,
                'diff_nominal_visible' => 0,
                'diff_status'     => 'visible',
            ];
        }

        foreach ($menuData as $menu) {
            foreach (($menu['bahan'] ?? []) as $namaBahan => $qtyBase) {
                if (!isset($bahanSummary[$namaBahan])) {
                    $k = $this->normName($namaBahan);
                    $priceRow = $priceMap[$k] ?? null;

                    $hargaPerBase = 0.0;
                    if ($priceRow) {
                        $isi = max((float) ($priceRow->isi_per_unit ?? 1), 1);
                        $kon = max((float) ($priceRow->konversi ?? 1), 1);
                        $hargaPerBase = ((float) $priceRow->harga_bahan / $isi) / $kon;
                    }

                    $bahanSummary[$namaBahan] = [
                        'qty_resep'       => 0,
                        'hpp'             => 0,
                        'harga'           => $hargaPerBase,
                        'satuan'          => $priceRow->satuan ?? null,
                        'stock'           => 0,
                        'qty_stock'       => 0,
                        'opening_stock'   => 0,
                        'ending_stock'    => 0,
                        'waste_product_qty' => 0,
                        'waste_bahan_qty'   => 0,
                        'waste_tepung_qty'  => 0,
                        'waste_qty'         => 0,
                        'waste_rp'          => 0,
                        'waste_tepung_rp'   => 0,
                        'avg_per_day'     => 0,
                        'forecast_days'   => $forecastDays,
                        'forecast_qty'    => 0,
                        'forecast_stock'  => 0,
                        'forecast_diff'   => 0,
                        'forecast_hpp'    => 0,
                        'diff_raw_qty'    => 0,
                        'diff_hidden_qty' => 0,
                        'diff_visible_qty'=> 0,
                        'diff_nominal_visible' => 0,
                        'diff_status'     => 'visible',
                    ];
                }

                $hargaPerBase = (float) ($bahanSummary[$namaBahan]['harga'] ?? 0);
                $hpp = $hargaPerBase * (float) $qtyBase;

                $bahanSummary[$namaBahan]['qty_resep'] += (float) $qtyBase;
                $bahanSummary[$namaBahan]['hpp'] += (float) $hpp;
            }
        }

        foreach ($bahanSummary as $namaBahan => &$row) {
            $qtyPos = (float) ($row['qty_resep'] ?? 0);
            $stock  = (float) ($row['stock'] ?? 0);
            $harga  = (float) ($row['harga'] ?? 0);

            $avgPerDay   = $rangeDays > 0 ? ($qtyPos / $rangeDays) : 0.0;
            $forecastQty = $avgPerDay * $forecastDays;

            $row['avg_per_day'] = $avgPerDay;
            $row['forecast_qty'] = $forecastQty;
            $row['forecast_stock'] = $stock - $forecastQty;
            $row['forecast_diff'] = $stock - $forecastQty;
            $row['forecast_hpp'] = $forecastQty * $harga;
        }
        unset($row);

        $bahanSummary = collect($bahanSummary)
            ->reject(fn ($row, $namaBahan) => $this->isTepungBreader($namaBahan))
            ->toArray();

        $visibleBahanNames = array_values(array_filter(
            $visibleBahanNames,
            fn ($namaBahan) => !$this->isTepungBreader($namaBahan)
        ));

        $totalHpp = array_sum(array_map(fn ($r) => (float) ($r['hpp'] ?? 0), $bahanSummary));

        $totalSales = $totalSalesBulanan;
        $profit = $totalSales - $totalHpp;

        $totalWasteMoney  = 0.0;
        $totalSelisihNet  = 0.0;
        $totalSelisihLoss = 0.0;
        $totalSelisihGain = 0.0;

        $usagePosByNorm = [];
        foreach ($bahanSummary as $namaBahan => $row) {
            $usagePosByNorm[$this->normName($namaBahan)] = (float) ($row['qty_resep'] ?? 0);
        }

        foreach ($stockAggByBahanId as $bahanId => $agg) {
            $k = $this->normName($agg->bahan_nama ?? '');
            $priceRow = $priceMap[$k] ?? null;

            $hargaPerBase = 0.0;
            if ($priceRow) {
                $isi = max((float) ($priceRow->isi_per_unit ?? 1), 1);
                $kon = max((float) ($priceRow->konversi ?? 1), 1);
                $hargaPerBase = ((float) $priceRow->harga_bahan / $isi) / $kon;
            }

            $wasteQty = (float) ($agg->waste_qty ?? 0);
            $wasteRp  = $wasteQty * $hargaPerBase;

            $totalWasteMoney += $wasteRp;

            foreach ($bahanSummary as $namaBahan => &$row) {
                if ($this->normName($namaBahan) === $k) {
                    $row['waste_qty'] = $wasteQty;
                    $row['waste_rp']  = $wasteRp;
                    break;
                }
            }
            unset($row);

            $usageDsc = (float) ($agg->usage_stock ?? 0);
            $usagePos = (float) ($usagePosByNorm[$k] ?? 0.0);
            $usageDscNet = $usageDsc - $wasteQty;

            $diffQty = $usageDscNet - $usagePos;
            $diffMoney = $diffQty * $hargaPerBase;

            $totalSelisihNet += $diffMoney;

            if ($diffQty > 0) {
                $totalSelisihLoss += $diffMoney;
            } elseif ($diffQty < 0) {
                $totalSelisihGain += abs($diffMoney);
            }
        }

        $totalSelisihAbsoluteNormal = $totalSelisihLoss + $totalSelisihGain;

        $qualityCostNormal = $totalWasteMoney + $totalSelisihAbsoluteNormal;

        $summaryNormal = [
            'sales' => $totalSales,
            'sales_transaksi' => $totalSalesTransaksi,
            'sales_laporan_bulanan' => $totalSalesBulanan,
            'hpp' => $totalHpp,
            'hpp_percent' => $totalSales != 0 ? round(($totalHpp / $totalSales) * 100, 1) : 0,
            'profit' => $profit,
            'profit_percent' => $totalSales != 0 ? round(($profit / $totalSales) * 100, 1) : 0,
            'waste' => $totalWasteMoney,
            'waste_percent' => $totalSales != 0 ? round(($totalWasteMoney / $totalSales) * 100, 1) : 0,
            'selisih_net' => $totalSelisihNet,
            'selisih_loss' => $totalSelisihAbsoluteNormal,
            'selisih_loss_minus_only' => $totalSelisihLoss,
            'selisih_gain_plus_only' => $totalSelisihGain,
            'selisih_gain' => $totalSelisihGain,
            'selisih_percent' => $totalSales != 0 ? round(($totalSelisihAbsoluteNormal / $totalSales) * 100, 1) : 0,
            'quality_cost' => $qualityCostNormal,
            'quality_cost_percent' => $totalSales != 0 ? round(($qualityCostNormal / $totalSales) * 100, 1) : 0,
        ];

        $outletKey = (string) $outletId;
        $hiddenPlusMap = $this->getQcrHiddenMap($outletKey, $start_date, $end_date, 'plus');
        $hiddenMinusMap = $this->getQcrHiddenMap($outletKey, $start_date, $end_date, 'minus');

        $qcrPlusItems = [];
        $qcrMinusItems = [];

        foreach ($bahanSummary as $namaBahan => &$row) {
            $harga = (float) ($row['harga'] ?? 0);
            $qtyStock = (float) ($row['qty_stock'] ?? 0);
            $qtyResep = (float) ($row['qty_resep'] ?? 0);
            $wasteQty = (float) ($row['waste_qty'] ?? 0);
            $satuan = (string) ($row['satuan'] ?? '-');

            $diffQty = ($qtyStock - $wasteQty) - $qtyResep;
            $referenceKey = md5($namaBahan . '|' . $start_date . '|' . $end_date);

            $jenis = null;
            if ($diffQty < 0) {
                $jenis = 'plus';
            } elseif ($diffQty > 0) {
                $jenis = 'minus';
            }

            $hiddenRow = null;
            if ($jenis === 'plus') {
                $hiddenRow = $hiddenPlusMap[$referenceKey] ?? null;
            } elseif ($jenis === 'minus') {
                $hiddenRow = $hiddenMinusMap[$referenceKey] ?? null;
            }

            $hiddenQty = (float) ($hiddenRow->hidden_qty ?? 0);
            $hiddenAll = (int) ($hiddenRow->hidden_all ?? 0) === 1;

            $calc = $this->applyHiddenToDifference($diffQty, $hiddenQty, $hiddenAll);

            $visibleSignedQty = $calc['visible_signed_qty'] ?? (
                $diffQty < 0 ? -1 * ($calc['visible_qty'] ?? 0) : ($calc['visible_qty'] ?? 0)
            );

            $row['diff_raw_qty'] = $diffQty;
            $row['diff_hidden_qty'] = $calc['hidden_qty'];
            $row['diff_visible_qty'] = $calc['visible_qty'];
            $row['diff_visible_signed_qty'] = $visibleSignedQty;
            $row['diff_nominal_visible'] = $calc['visible_qty'] * $harga;
            $row['diff_status'] = $calc['visible_qty'] > 0 ? 'visible' : 'hidden';

            $item = [
                'reference_key'   => $referenceKey,
                'reference_name'  => $namaBahan,
                'qty_raw'         => $diffQty,
                'qty_abs'         => $calc['raw_abs'],
                'qty_hidden'      => $calc['hidden_qty'],
                'qty_visible'     => $visibleSignedQty,
                'qty_visible_abs' => $calc['visible_qty'],
                'nominal'         => $calc['visible_qty'] * $harga,
                'nominal_raw'     => $calc['raw_abs'] * $harga,
                'satuan'          => $satuan,
                'status'          => $calc['visible_qty'] > 0 ? 'visible' : 'hidden',
            ];

            if ($jenis === 'plus' && $item['qty_abs'] > 0) {
                $qcrPlusItems[] = $item;
            } elseif ($jenis === 'minus' && $item['qty_abs'] > 0) {
                $qcrMinusItems[] = $item;
            }
        }
        unset($row);

        /*
        |--------------------------------------------------------------------------
        | RECALCULATE SELISIH AFTER HIDDEN +/- APPLIED
        |--------------------------------------------------------------------------
        | Jangan pakai totalSelisihLoss/Gain raw yang dihitung sebelum hidden.
        | Card Selisih Persediaan (Loss), persen selisih, dan Quality Cost
        | harus ikut angka visible setelah item + / - di-hide pada filter aktif.
        */
        $totalSelisihNetVisible = 0.0;
        $totalSelisihLossVisible = 0.0;
        $totalSelisihGainVisible = 0.0;

        foreach ($bahanSummary as $namaBahan => $row) {
            $harga = (float) ($row['harga'] ?? 0);
            $visibleSignedQty = (float) ($row['diff_visible_signed_qty'] ?? 0);

            $nominalVisible = $visibleSignedQty * $harga;

            $totalSelisihNetVisible += $nominalVisible;

            if ($visibleSignedQty > 0) {
                // DSC > POS = Loss
                $totalSelisihLossVisible += abs($nominalVisible);
            } elseif ($visibleSignedQty < 0) {
                // DSC < POS = Gain / Plus
                $totalSelisihGainVisible += abs($nominalVisible);
            }
        }

        $totalSelisihNet = $totalSelisihNetVisible;
        $totalSelisihLoss = $totalSelisihLossVisible;
        $totalSelisihGain = $totalSelisihGainVisible;
        $totalSelisihAbsoluteVisible = $totalSelisihLossVisible + $totalSelisihGainVisible;

        $qualityCost = $totalWasteMoney + $totalSelisihAbsoluteVisible;

        $summarySetelahHapus = [
            'sales' => $totalSales,
            'sales_transaksi' => $totalSalesTransaksi,
            'sales_laporan_bulanan' => $totalSalesBulanan,
            'hpp' => $totalHpp,
            'hpp_percent' => $totalSales != 0 ? round(($totalHpp / $totalSales) * 100, 1) : 0,
            'profit' => $profit,
            'profit_percent' => $totalSales != 0 ? round(($profit / $totalSales) * 100, 1) : 0,
            'waste' => $totalWasteMoney,
            'waste_percent' => $totalSales != 0 ? round(($totalWasteMoney / $totalSales) * 100, 1) : 0,
            'selisih_net' => $totalSelisihNet,
            'selisih_loss' => $totalSelisihAbsoluteVisible,
            'selisih_loss_minus_only' => $totalSelisihLoss,
            'selisih_gain_plus_only' => $totalSelisihGain,
            'selisih_gain' => $totalSelisihGain,
            'selisih_percent' => $totalSales != 0 ? round(($totalSelisihAbsoluteVisible / $totalSales) * 100, 1) : 0,
            'quality_cost' => $qualityCost,
            'quality_cost_percent' => $totalSales != 0 ? round(($qualityCost / $totalSales) * 100, 1) : 0,
        ];

        /*
        |--------------------------------------------------------------------------
        | QCR BAHAN NON-BOM / OPERASIONAL
        |--------------------------------------------------------------------------
        | Bahan yang tidak masuk resep menu/BOM tetap ditampilkan di section bawah.
        | Error DataTables dicegah di Blade dengan memastikan tbody selalu punya
        | jumlah kolom yang sama dengan thead.
        */
        $visibleBahanNorms = collect($visibleBahanNames)
            ->map(fn ($nama) => $this->normName($nama))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $bahanNonBomRows = [];
        $totalNonBomLoss = 0.0;

        foreach ($stockAggByBahanId as $bahanId => $agg) {
            $namaBahan = (string) ($agg->bahan_nama ?? '');
            $namaNorm = $this->normName($namaBahan);

            if ($namaBahan === '' || $this->isTepungBreader($namaBahan)) {
                continue;
            }

            // Kalau sudah dipakai BOM/menu, jangan masuk Non-BOM.
            if (in_array($namaNorm, $visibleBahanNorms, true)) {
                continue;
            }

            $priceRow = $priceMap[$namaNorm] ?? null;

            $hargaPerBase = 0.0;
            if ($priceRow) {
                $isi = max((float) ($priceRow->isi_per_unit ?? 1), 1);
                $kon = max((float) ($priceRow->konversi ?? 1), 1);
                $hargaPerBase = ((float) ($priceRow->harga_bahan ?? 0) / $isi) / $kon;
            }

            // Stock Non-BOM juga harus memakai ending stock aktual.
            $stock = (float) ($agg->stock_available ?? $agg->ending ?? $agg->available ?? 0);
            $usageDsc = (float) ($agg->usage_stock ?? 0);

            $wasteProduct = (float) ($agg->waste_product_qty ?? 0);
            $wasteBahan = (float) ($agg->waste_bahan_qty ?? 0);
            $wasteQty = $wasteProduct + $wasteBahan;

            // Karena Non-BOM tidak punya Usage POS/BOM, selisih dinilai dari usage DSC net.
            $usageDscNet = $usageDsc - $wasteQty;
            $selisihQty = $usageDscNet;
            $selisihRp = abs($selisihQty) * $hargaPerBase;

            if ($selisihQty > 0) {
                $status = 'Minus';
                $totalNonBomLoss += $selisihRp;
            } elseif ($selisihQty < 0) {
                $status = 'Plus';
            } elseif ($stock > 0 || $usageDsc == 0) {
                $status = 'Opsional';
            } else {
                $status = 'Normal';
            }

            $bahanNonBomRows[] = [
                'bahan_id' => (int) $bahanId,
                'nama_bahan' => $namaBahan,
                'satuan' => (string) ($agg->bahan_satuan ?? ($priceRow->satuan ?? '-')),
                'stock' => $stock,
                'usage_dsc' => $usageDsc,
                'waste_qty' => $wasteQty,
                'selisih_qty' => $selisihQty,
                'harga' => $hargaPerBase,
                'selisih_rp' => $selisihRp,
                'status' => $status,
            ];
        }

        usort($bahanNonBomRows, function ($a, $b) {
            $rank = ['Minus' => 1, 'Plus' => 2, 'Opsional' => 3, 'Normal' => 4];
            $ra = $rank[$a['status'] ?? 'Normal'] ?? 9;
            $rb = $rank[$b['status'] ?? 'Normal'] ?? 9;

            if ($ra !== $rb) {
                return $ra <=> $rb;
            }

            return strcmp((string) ($a['nama_bahan'] ?? ''), (string) ($b['nama_bahan'] ?? ''));
        });

        $nonBomMinusCount = collect($bahanNonBomRows)->where('status', 'Minus')->count();
        $nonBomPlusCount = collect($bahanNonBomRows)->where('status', 'Plus')->count();
        $nonBomOpsionalCount = collect($bahanNonBomRows)->where('status', 'Opsional')->count();

        /*
        |--------------------------------------------------------------------------
        | NOTIF BAHAN MINUS
        |--------------------------------------------------------------------------
        | Lonceng notifikasi tidak menampilkan qty sebagai nilai utama.
        | Nilai warning yang ditampilkan adalah harga/nominal:
        | qty minus yang masih visible x harga bahan per satuan QCR.
        */
        $qcrMinusNotifItems = collect($qcrMinusItems)
            ->filter(fn ($item) => (float) ($item['qty_visible_abs'] ?? 0) > 0)
            ->map(function ($item) use ($selectedOutletLabel) {
                $qtyVisibleAbs = (float) ($item['qty_visible_abs'] ?? 0);
                $nominalVisible = (float) ($item['nominal'] ?? 0);
                $hargaSatuan = $qtyVisibleAbs > 0 ? ($nominalVisible / $qtyVisibleAbs) : 0;

                $item['outlet_label'] = $selectedOutletLabel;
                $item['harga_satuan'] = $hargaSatuan;
                $item['nominal_warning'] = $nominalVisible;

                return $item;
            })
            ->values()
            ->all();

        $qcrMinusNotifCount = count($qcrMinusNotifItems);
        $qcrMinusNotifTotal = array_sum(array_map(
            fn ($item) => (float) ($item['nominal_warning'] ?? $item['nominal'] ?? 0),
            $qcrMinusNotifItems
        ));

        // Backward compatibility: $summary tetap berisi angka setelah data PLUS/MINUS di-hidden,
        // supaya bagian lama yang masih membaca $summary tidak rusak.
        $summary = $summarySetelahHapus;

        return compact(
            'menuData',
            'bahanPrice',
            'bahanSummary',
            'outlets',
            'outletGroups',
            'outletId',
            'start_date',
            'end_date',
            'summary',
            'summaryNormal',
            'summarySetelahHapus',
            'visibleBahanNames',
            'rangeDays',
            'forecastDays',
            'forecastFrom',
            'forecastTo',
            'bahanNonBomRows',
            'totalNonBomLoss',
            'nonBomMinusCount',
            'nonBomPlusCount',
            'nonBomOpsionalCount',
            'qcrPlusItems',
            'qcrMinusItems',
            'qcrMinusNotifItems',
            'qcrMinusNotifCount',
            'qcrMinusNotifTotal',
            'selectedOutletLabel',
            'totalUangPlus',
            'filterApplied'
        );
    }


    public function exportQcr(Request $request)
    {
        $data = $this->buildQcrData($request);

        $filename = 'QCR_' . ($data['start_date'] ?? date('Y-m-d')) . '_to_' . ($data['end_date'] ?? date('Y-m-d')) . '.xlsx';

        return Excel::download(new QcrExport($data), $filename);
    }

    // REDIS EXPORT QCR
    public function buildQcrExportPayload(Request $request): array
    {
        return $this->buildQcrData($request);
    }

    public function generateQcrExport(Request $request)
    {
        $request->validate([
            'outlet_id' => 'required|string|max:100',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->startOfDay();

        if ($start->gt($end)) {
            return response()->json([
                'ok' => false,
                'message' => 'Tanggal start tidak boleh lebih besar dari tanggal end.',
            ], 422);
        }

        if (($start->diffInDays($end) + 1) > 31) {
            return response()->json([
                'ok' => false,
                'message' => 'Range export maksimal 31 hari.',
            ], 422);
        }

        $exportId = DB::table('qcr_export_jobs')->insertGetId([
            'outlet_id' => (string) $request->outlet_id,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'status' => 'pending',
            'total_outlet' => 0,
            'processed_outlet' => 0,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        GenerateQcrExportJob::dispatch($exportId)->onQueue('qcr-export');

        return response()->json([
            'ok' => true,
            'message' => 'Export QCR sedang diproses.',
            'export_id' => $exportId,
        ]);
    }

    public function qcrExportStatus($id)
    {
        $row = DB::table('qcr_export_jobs')
            ->where('id', (int) $id)
            ->when(auth()->id(), fn ($q) => $q->where('created_by', auth()->id()))
            ->first();

        if (! $row) {
            return response()->json([
                'ok' => false,
                'message' => 'Data export tidak ditemukan.',
            ], 404);
        }

        $total = (int) ($row->total_outlet ?? 0);
        $processed = (int) ($row->processed_outlet ?? 0);
        $progress = $total > 0 ? round(($processed / $total) * 100, 1) : 0;

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => (int) $row->id,
                'status' => (string) $row->status,
                'total_outlet' => $total,
                'processed_outlet' => $processed,
                'progress' => $progress,
                'error_message' => $row->error_message,
                'download_url' => $row->status === 'done'
                    ? route('master.qcr.export.download', $row->id)
                    : null,
            ],
        ]);
    }

    public function downloadQcrExport($id)
    {
        $row = DB::table('qcr_export_jobs')
            ->where('id', (int) $id)
            ->when(auth()->id(), fn ($q) => $q->where('created_by', auth()->id()))
            ->first();

        if (! $row || $row->status !== 'done' || ! $row->file_path) {
            abort(404, 'File export belum tersedia.');
        }

        if (! Storage::disk('local')->exists($row->file_path)) {
            abort(404, 'File export tidak ditemukan di storage.');
        }

        return Storage::disk('local')->download($row->file_path, basename($row->file_path));
    }
    // END REDIS EXPORT QCR
        
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            $errors = [];
            Excel::import(new StockImport($errors), $request->file('file'));

            if (count($errors)) {
                return response()->json(['success' => false, 'errors' => $errors]);
            }

            return response()->json(['success' => true, 'message' => 'Data stock berhasil diimport.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'errors' => [$e->getMessage()]]);
        }
    }

private function isHargaOutletOnlyRole(): bool
{
    $role = auth()->check() ? auth()->user()->role : null;

    return in_array($role, [
        'spv',
        'tm_manager',
        'terotorial_manager',
        'territorial_manager',
    ], true);
}

private function abortIfHargaOutletOnlyRole(): void
{
    if ($this->isHargaOutletOnlyRole()) {
        abort(403, 'Role ini hanya boleh mengakses Harga Bahan Outlet.');
    }
}

private function parseOutletIds($value): array
{
    return collect(explode(',', (string) $value))
        ->map(fn ($id) => (int) trim($id))
        ->filter(fn ($id) => $id > 0)
        ->unique()
        ->values()
        ->toArray();
}

public function dataqcr(Request $request)
{
    $role = auth()->check() ? auth()->user()->role : null;
    $hargaOutletOnly = $this->isHargaOutletOnlyRole();

    $menu = DB::table('tbl_menu')
        ->select('id', 'item_produk', 'kategori', 'harga')
        ->orderBy('item_produk')
        ->get();

    $bahan = DB::table('tbl_bahan')
        ->select('id', 'nama_bahan', 'qty', 'satuan', 'konversi', 'harga_bahan', 'isi_per_unit')
        ->orderBy('nama_bahan')
        ->get();

    $bahanDsc = DB::table('tbl_bahan_dsc')
        ->select('id', 'nama_bahan', 'satuan', 'is_tepung', 'is_active', 'created_at', 'updated_at')
        ->orderBy('id')
        ->get();

    $bahanHargaOutlet = DB::table('tbl_bahan')
        ->select('id', 'nama_bahan', 'qty', 'satuan', 'konversi', 'harga_bahan', 'isi_per_unit')
        ->orderBy('nama_bahan')
        ->get();

    // Nama outlet duplikat digabung jadi 1 option.
    // Value option = "1,2,3" supaya ketika update, semua ID outlet dengan nama sama ikut ter-update.
    $outlets = DB::table('tbl_outlets')
        ->select(
            DB::raw('MIN(id) as id'),
            'nama_outlet',
            DB::raw('GROUP_CONCAT(id ORDER BY id SEPARATOR ",") as outlet_ids')
        )
        ->whereNotNull('nama_outlet')
        ->where('nama_outlet', '!=', '')
        ->groupBy('nama_outlet')
        ->orderBy('nama_outlet')
        ->get();

    $stock = collect();

    return view('Investor.Master.dataQcr', compact(
        'menu',
        'bahan',
        'bahanDsc',
        'bahanHargaOutlet',
        'outlets',
        'stock',
        'role',
        'hargaOutletOnly'
    ));
}

public function bahanHargaOutletList(Request $request)
{
    $request->validate([
        'outlet_id' => 'required',
    ]);

    $outletIds = $this->parseOutletIds($request->outlet_id);

    if (empty($outletIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Outlet tidak valid.',
            'data' => [],
        ], 422);
    }

    $data = DB::table('tbl_bahan as b')
        ->leftJoin('tbl_bahan_harga_outlet as bho', function ($join) use ($outletIds) {
            $join->on('bho.bahan_id', '=', 'b.id')
                ->whereIn('bho.outlet_id', $outletIds);
        })
        ->select(
            'b.id',
            'b.nama_bahan',
            DB::raw('COALESCE(b.harga_bahan, 0) as harga_master'),
            DB::raw('COALESCE(MAX(bho.harga_bahan), b.harga_bahan, 0) as harga_bahan')
        )
        ->groupBy(
            'b.id',
            'b.nama_bahan',
            'b.harga_bahan'
        )
        ->orderBy('b.nama_bahan')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $data,
    ]);
}

public function storeOrUpdateBahanHargaOutlet(Request $request)
{
    $request->validate([
        'outlet_id'   => 'required',
        'bahan_id'    => 'required|integer|exists:tbl_bahan,id',
        'harga_bahan' => 'required|numeric|min:0',
    ]);

    $outletIds = $this->parseOutletIds($request->outlet_id);

    if (empty($outletIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Outlet tidak valid.',
        ], 422);
    }

    DB::transaction(function () use ($request, $outletIds) {
        foreach ($outletIds as $outletId) {
            DB::table('tbl_bahan_harga_outlet')->updateOrInsert(
                [
                    'outlet_id' => $outletId,
                    'bahan_id'  => (int) $request->bahan_id,
                ],
                [
                    'harga_bahan' => $request->harga_bahan,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Harga bahan outlet berhasil disimpan.',
    ]);
}

public function bulkUpdateBahanHargaOutlet(Request $request)
{
    $request->validate([
        'outlet_id' => 'required',
        'harga'     => 'required|array',
    ]);

    $outletIds = $this->parseOutletIds($request->outlet_id);

    if (empty($outletIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Outlet tidak valid.',
        ], 422);
    }

    $allowedBahanIds = DB::table('tbl_bahan')
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->toArray();

    DB::transaction(function () use ($request, $outletIds, $allowedBahanIds) {
        foreach ($outletIds as $outletId) {
            foreach ($request->harga as $bahanId => $harga) {
                $bahanId = (int) $bahanId;

                if (!in_array($bahanId, $allowedBahanIds, true)) {
                    continue;
                }

                if ($harga === null || $harga === '') {
                    continue;
                }

                DB::table('tbl_bahan_harga_outlet')->updateOrInsert(
                    [
                        'outlet_id' => $outletId,
                        'bahan_id'  => $bahanId,
                    ],
                    [
                        'harga_bahan' => $harga,
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ]
                );
            }
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Harga bahan outlet berhasil disimpan.',
    ]);
}

public function deleteBahanHargaOutlet(Request $request)
{
    $request->validate([
        'outlet_id' => 'required',
        'bahan_id'  => 'required|integer|exists:tbl_bahan,id',
    ]);

    $outletIds = $this->parseOutletIds($request->outlet_id);

    if (empty($outletIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Outlet tidak valid.',
        ], 422);
    }

    DB::table('tbl_bahan_harga_outlet')
        ->whereIn('outlet_id', $outletIds)
        ->where('bahan_id', (int) $request->bahan_id)
        ->delete();

    return response()->json([
        'success' => true,
        'message' => 'Harga outlet direset ke harga default master.',
    ]);
}

// ---------------- MENU ----------------
public function storeMenu(Request $request)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'item_produk' => 'required|string|max:100',
        'kategori'    => 'nullable|string|max:50',
        'harga'       => 'required|numeric',
    ]);

    DB::table('tbl_menu')->insert([
        'item_produk' => $request->item_produk,
        'kategori'    => $request->kategori,
        'harga'       => $request->harga,
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return back()->with('success', 'Menu berhasil ditambahkan.');
}

public function updateMenu(Request $request, $id)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'item_produk' => 'required|string|max:100',
        'kategori'    => 'nullable|string|max:50',
        'harga'       => 'required|numeric',
    ]);

    DB::table('tbl_menu')
        ->where('id', $id)
        ->update([
            'item_produk' => $request->item_produk,
            'kategori'    => $request->kategori,
            'harga'       => $request->harga,
            'updated_at'  => now(),
        ]);

    return back()->with('success', 'Menu berhasil diubah.');
}

public function destroyMenu($id)
{
    $this->abortIfHargaOutletOnlyRole();

    DB::transaction(function () use ($id) {
        DB::table('tbl_menu_bahan')->where('menu_id', $id)->delete();
        DB::table('tbl_menu')->where('id', $id)->delete();
    });

    return back()->with('success', 'Menu berhasil dihapus.');
}

// ---------------- BAHAN ----------------
public function storeBahan(Request $request)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'nama_bahan'   => 'required|string|max:255',
        'qty'          => 'required|numeric|min:0',
        'satuan'       => 'required|string|max:50',
        'konversi'     => 'nullable|numeric|min:0',
        'harga_bahan'  => 'nullable|numeric|min:0',
        'isi_per_unit' => 'nullable|numeric|min:0',
    ]);

    DB::table('tbl_bahan')->insert([
        'nama_bahan'   => $request->nama_bahan,
        'qty'          => $request->qty,
        'satuan'       => $request->satuan,
        'konversi'     => $request->konversi ?? 1,
        'harga_bahan'  => $request->harga_bahan ?? 0,
        'isi_per_unit' => $request->isi_per_unit ?? 1,
        'created_at'   => now(),
        'updated_at'   => now(),
    ]);

    return back()->with('success', 'Bahan berhasil ditambahkan.');
}

public function updateBahan(Request $request, $id)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'nama_bahan'  => 'required|string|max:100',
        'qty'         => 'required|numeric',
        'satuan'      => 'nullable|string|max:20',
        'konversi'    => 'nullable|numeric',
        'harga_bahan' => 'required|numeric',
    ]);

    DB::table('tbl_bahan')
        ->where('id', $id)
        ->update([
            'nama_bahan'  => $request->nama_bahan,
            'qty'         => $request->qty,
            'satuan'      => $request->satuan,
            'konversi'    => $request->konversi ?? 1,
            'harga_bahan' => $request->harga_bahan,
            'updated_at'  => now(),
        ]);

    return back()->with('success', 'Bahan berhasil diubah.');
}

public function destroyBahan($id)
{
    $this->abortIfHargaOutletOnlyRole();

    DB::table('tbl_bahan')->where('id', $id)->delete();

    return back()->with('success', 'Bahan berhasil dihapus.');
}

// ---------------- BAHAN DSC ----------------
public function storeBahanDsc(Request $request)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'nama_bahan' => 'required|string|max:255',
        'satuan'     => 'required|string|max:50',
        'is_tepung'  => 'nullable|in:0,1',
        'is_active'  => 'nullable|in:0,1',
    ]);

    $nama = trim((string) $request->nama_bahan);
    $satuan = strtoupper(trim((string) $request->satuan));

    $exists = DB::table('tbl_bahan_dsc')
        ->whereRaw('UPPER(TRIM(nama_bahan)) COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci', [mb_strtoupper($nama)])
        ->exists();

    if ($exists) {
        return back()->with('error', 'Bahan DSC sudah ada.');
    }

    DB::table('tbl_bahan_dsc')->insert([
        'nama_bahan' => $nama,
        'satuan'     => $satuan,
        'is_tepung'  => $request->filled('is_tepung') ? (int) $request->is_tepung : null,
        'is_active'  => $request->has('is_active') ? (int) $request->is_active : 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Bahan DSC berhasil ditambahkan.');
}

public function updateBahanDsc(Request $request, $id)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'nama_bahan' => 'required|string|max:255',
        'satuan'     => 'required|string|max:50',
        'is_tepung'  => 'nullable|in:0,1',
        'is_active'  => 'nullable|in:0,1',
    ]);

    $nama = trim((string) $request->nama_bahan);
    $satuan = strtoupper(trim((string) $request->satuan));

    $exists = DB::table('tbl_bahan_dsc')
        ->where('id', '<>', (int) $id)
        ->whereRaw('UPPER(TRIM(nama_bahan)) COLLATE utf8mb4_general_ci = ? COLLATE utf8mb4_general_ci', [mb_strtoupper($nama)])
        ->exists();

    if ($exists) {
        return back()->with('error', 'Nama bahan DSC sudah dipakai data lain.');
    }

    DB::table('tbl_bahan_dsc')
        ->where('id', (int) $id)
        ->update([
            'nama_bahan' => $nama,
            'satuan'     => $satuan,
            'is_tepung'  => $request->filled('is_tepung') ? (int) $request->is_tepung : null,
            'is_active'  => $request->has('is_active') ? (int) $request->is_active : 1,
            'updated_at' => now(),
        ]);

    return back()->with('success', 'Bahan DSC berhasil diubah.');
}

public function destroyBahanDsc($id)
{
    $this->abortIfHargaOutletOnlyRole();

    $usedInStock = DB::table('tbl_stock')->where('bahan_id', (int) $id)->exists();
    $usedInDraft = DB::table('tbl_stock_draft')->where('bahan_id', (int) $id)->exists();

    if ($usedInStock || $usedInDraft) {
        DB::table('tbl_bahan_dsc')
            ->where('id', (int) $id)
            ->update(['is_active' => 0, 'updated_at' => now()]);

        return back()->with('success', 'Bahan DSC sudah pernah dipakai, jadi dinonaktifkan agar histori stok tetap aman.');
    }

    DB::table('tbl_bahan_dsc')->where('id', (int) $id)->delete();

    return back()->with('success', 'Bahan DSC berhasil dihapus.');
}

// ---------------- BUM / BOM ----------------
public function storeBum(Request $request)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'menu_id'      => 'required|exists:tbl_menu,id',
        'bahan_id'     => 'required|array|min:1',
        'bahan_id.*'   => 'exists:tbl_bahan,id',
        'qty'          => 'nullable|array',
    ]);

    $rows = [];
    foreach ($request->bahan_id as $bahanId) {
        $rows[] = [
            'menu_id'    => $request->menu_id,
            'bahan_id'   => $bahanId,
            'qty'        => $request->qty[$bahanId] ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    DB::table('tbl_menu_bahan')->insert($rows);

    return back()->with('success', 'Bahan berhasil ditambahkan ke menu.');
}

public function updateBum(Request $request, $menu_id)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'bahan_id'   => 'required|array|min:1',
        'bahan_id.*' => 'exists:tbl_bahan,id',
        'qty'        => 'nullable|array',
    ]);

    DB::transaction(function () use ($request, $menu_id) {
        DB::table('tbl_menu_bahan')
            ->where('menu_id', (int) $menu_id)
            ->delete();

        $rows = [];
        foreach ($request->bahan_id as $bahanId) {
            $rows[] = [
                'menu_id'    => (int) $menu_id,
                'bahan_id'   => $bahanId,
                'qty'        => $request->qty[$bahanId] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('tbl_menu_bahan')->insert($rows);
    });

    return back()->with('success', 'BOM berhasil diperbarui.');
}

public function destroyBum($menu_id)
{
    $this->abortIfHargaOutletOnlyRole();

    DB::table('tbl_menu_bahan')
        ->where('menu_id', (int) $menu_id)
        ->delete();

    return back()->with('success', 'BOM untuk menu berhasil dihapus.');
}

public function getMenuBahan($menu_id)
{
    $this->abortIfHargaOutletOnlyRole();

    try {
        $data = DB::table('tbl_menu_bahan as mb')
            ->join('tbl_bahan as b', 'b.id', '=', 'mb.bahan_id')
            ->select(
                'mb.id',
                'mb.menu_id',
                'mb.bahan_id',
                'mb.qty',
                'b.nama_bahan',
                'b.satuan'
            )
            ->where('mb.menu_id', (int) $menu_id)
            ->orderBy('b.nama_bahan')
            ->get();

        return response()->json($data);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

public function destroyBumByMenu($menu_id)
{
    $this->abortIfHargaOutletOnlyRole();

    DB::table('tbl_menu_bahan')->where('menu_id', $menu_id)->delete();

    return back()->with('success', 'BOM untuk menu berhasil dihapus.');
}

// ---------------- STOCK ----------------
public function storeStock(Request $request)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'bahan_id'       => 'required|integer|exists:tbl_bahan,id',
        'outlet_id'      => 'nullable|integer|exists:tbl_outlets,id',
        'shift'          => 'required|string',
        'opening_stock'  => 'required|numeric',
        'used_qty'       => 'required|numeric',
        'waste_product'  => 'nullable|numeric',
        'waste_bahan'    => 'nullable|numeric',
        'waste_tepung'   => 'nullable|numeric',
        'tanggal'        => 'required|date',
    ]);

    DB::table('tbl_stock')->insert([
        'bahan_id'       => $request->bahan_id,
        'outlet_id'      => $request->outlet_id,
        'shift'          => $request->shift,
        'opening_stock'  => $request->opening_stock,
        'used_qty'       => $request->used_qty,
        'waste_product'  => $request->waste_product ?? 0,
        'waste_bahan'    => $request->waste_bahan ?? 0,
        'waste_tepung'   => $request->waste_tepung ?? 0,
        'ending_stock'   => $request->opening_stock - $request->used_qty - ($request->waste_product ?? 0),
        'tanggal'        => $request->tanggal,
        'created_at'     => now(),
        'updated_at'     => now(),
    ]);

    return back()->with('success', 'Stock berhasil ditambahkan.');
}

public function editStock($id)
{
    $this->abortIfHargaOutletOnlyRole();

    $stock = DB::table('tbl_stock')->where('id', $id)->first();

    $bahan = DB::table('tbl_bahan')
        ->select('id', 'nama_bahan')
        ->orderBy('nama_bahan')
        ->get();

    return response()->json([
        'id'            => $stock->id,
        'bahan_id'      => $stock->bahan_id,
        'shift'         => $stock->shift,
        'opening_stock' => $stock->opening_stock,
        'used_qty'      => $stock->used_qty,
        'waste_product' => $stock->waste_product,
        'waste_bahan'   => $stock->waste_bahan,
        'waste_tepung'  => $stock->waste_tepung,
        'tanggal'       => $stock->tanggal,
        'bahanList'     => $bahan,
    ]);
}

public function updateStock(Request $request, $id)
{
    $this->abortIfHargaOutletOnlyRole();

    $request->validate([
        'bahan_id'       => 'required|integer|exists:tbl_bahan,id',
        'shift'          => 'required|string',
        'opening_stock'  => 'required|numeric',
        'used_qty'       => 'required|numeric',
        'waste_product'  => 'nullable|numeric',
        'waste_bahan'    => 'nullable|numeric',
        'waste_tepung'   => 'nullable|numeric',
        'tanggal'        => 'required|date',
    ]);

    DB::table('tbl_stock')
        ->where('id', $id)
        ->update([
            'bahan_id'       => $request->bahan_id,
            'shift'          => $request->shift,
            'opening_stock'  => $request->opening_stock,
            'used_qty'       => $request->used_qty,
            'waste_product'  => $request->waste_product ?? 0,
            'waste_bahan'    => $request->waste_bahan ?? 0,
            'waste_tepung'   => $request->waste_tepung ?? 0,
            'ending_stock'   => $request->opening_stock - $request->used_qty - ($request->waste_product ?? 0),
            'tanggal'        => $request->tanggal,
            'updated_at'     => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Stock berhasil diperbarui',
    ]);
}

public function destroyStock($id)
{
    $this->abortIfHargaOutletOnlyRole();

    DB::table('tbl_stock')->where('id', $id)->delete();

    return back()->with('success', 'Stock berhasil dihapus.');
}

public function stockList(Request $request)
{
    $this->abortIfHargaOutletOnlyRole();

    $perPage  = max(10, min((int) $request->get('per_page', 25), 100));
    $page     = max((int) $request->get('page', 1), 1);
    $search   = trim((string) $request->get('search', ''));
    $tanggal  = trim((string) $request->get('tanggal', ''));
    $outletId = trim((string) $request->get('outlet_id', ''));
    $bahanId  = trim((string) $request->get('bahan_id', ''));

    $query = DB::table('tbl_stock as s')
        ->join('tbl_bahan as b', 'b.id', '=', 's.bahan_id')
        ->leftJoin('tbl_outlets as o', 'o.id', '=', 's.outlet_id')
        ->select(
            's.id',
            's.bahan_id',
            's.shift',
            's.opening_stock',
            's.purchase_in',
            's.mutasi_in',
            's.mutasi_out',
            's.used_qty',
            's.waste_product',
            's.waste_bahan',
            's.waste_tepung',
            's.ending_stock',
            's.actual_tepung',
            's.uang_plus',
            's.keterangan',
            's.tanggal',
            's.outlet_id',
            'b.nama_bahan',
            'b.satuan',
            'o.nama_outlet'
        );

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('b.nama_bahan', 'like', "%{$search}%")
              ->orWhere('o.nama_outlet', 'like', "%{$search}%")
              ->orWhere('s.shift', 'like', "%{$search}%");
        });
    }

    if ($tanggal !== '') {
        $query->whereDate('s.tanggal', $tanggal);
    }

    if ($outletId !== '') {
        $query->where('s.outlet_id', $outletId);
    }

    if ($bahanId !== '') {
        $query->where('s.bahan_id', $bahanId);
    }

    $stock = $query
        ->orderByDesc('s.tanggal')
        ->orderBy('b.nama_bahan')
        ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'current_page' => $stock->currentPage(),
        'last_page'    => $stock->lastPage(),
        'per_page'     => $stock->perPage(),
        'total'        => $stock->total(),
        'from'         => $stock->firstItem(),
        'to'           => $stock->lastItem(),
        'data'         => $stock->items(),
    ]);
}

public function outletOptions()
{
    $data = DB::table('tbl_outlets')
        ->select('id', 'nama_outlet')
        ->orderBy('nama_outlet')
        ->get();

    return response()->json($data);
}

public function bahanOptions()
{
    $data = DB::table('tbl_bahan')
        ->select('id', 'nama_bahan')
        ->orderBy('nama_bahan')
        ->get();

    return response()->json($data);
}
}
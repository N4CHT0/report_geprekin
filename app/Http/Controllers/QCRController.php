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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use App\Exports\QcrExport;
use App\Services\BcaRekonSqlService;
use App\Jobs\GenerateQcrExportJob;

class QCRController extends Controller
{
    /*
     |--------------------------------------------------------------------------
     | MASTER EXPORT STOCK OPNAME TEMPLATE
     |--------------------------------------------------------------------------
     | Tambahan export format Stock Opname Template tanpa menghapus export lama.
     */

    /*
     |--------------------------------------------------------------------------
     | MASTER FIX TEPUNG BREADER V2 NO BAD FORCE BLOCK
     |--------------------------------------------------------------------------
     | Blok force tepung yang sebelumnya bikin error dihapus dari controller.
     | Controller hanya memastikan Tepung Breader tidak di-reject dari pipeline BOM.
     */

    /*
     |--------------------------------------------------------------------------
     | MASTER FIX TEPUNG BREADER TAMPIL DI TABEL QCR
     |--------------------------------------------------------------------------
     | Tepung Breader tidak hanya tampil di Summary, tapi juga di tabel QCR utama.
     | Semua filter/reject/continue isTepungBreader pada pipeline QCR detail dihapus.
     */

    /*
     |--------------------------------------------------------------------------
     | MASTER FIX TEPUNG BREADER FULL BOM PIPELINE
     |--------------------------------------------------------------------------
     | Tepung Breader adalah bahan BOM normal.
     | Harus ikut Usage POS, Usage DSC, Difference, HPP, dan Prediksi Stock.
     | Tidak boleh di-reject dari stockAggByBahanId, bahanSummary,
     | visibleBahanNames, atau loop pembentukan QCR.
     */

    /*
     |--------------------------------------------------------------------------
     | MASTER FIX RED ERROR UNDEFINED SATUAN QCR
     |--------------------------------------------------------------------------
     | Fix variable $satuan undefined di return array QCR dan normalisasi
     | qty_visible supaya PCS/LBR tidak tampil decimal.
     */

    /*
     |--------------------------------------------------------------------------
     | MASTER FIX QCR INTEGER UNIT DISPLAY
     |--------------------------------------------------------------------------
     | Satuan integer seperti PCS/LBR/CUP/BOTOL/SACHET/PACK tidak boleh tampil
     | decimal. Contoh AYAM KECIL 333,09 PCS akan dibulatkan menjadi 333 PCS.
     */

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
        $outletId     = $request->get('outlet_id', 'all');
        $shiftFilter  = (string) $request->get('shift_filter', 'all');
        $isPeriodLoaded = $request->boolean('load_period') || (string) $request->get('active_tab', '') === 'periode';

        /*
         * PATCH REQUEST TIM - SATU FILTER PERIODE DSC
         * UI tidak lagi memakai 3 tanggal. Filter utama hanya start_date dan end_date.
         * Agar logika/rumus lama tetap aman, $today diperlakukan sebagai tanggal akhir periode.
         * Dengan begitu REKAP, DETAIL SHIFT, OMSET, SALES, dan ADJUSTMENT tetap memakai
         * rumus harian lama pada tanggal akhir, sedangkan tab PERIODE DSC memakai range.
         */
        $startDateRaw = (string) $request->get('start_date', '');
        $endDateRaw   = (string) $request->get('end_date', '');
        $tanggalRaw   = (string) $request->get('tanggal', '');

        $fallbackDate = date('Y-m-d');
        $todayRaw = $endDateRaw !== ''
            ? $endDateRaw
            : ($startDateRaw !== '' ? $startDateRaw : ($tanggalRaw !== '' ? $tanggalRaw : $fallbackDate));

        $today = $this->normalizeDateToYmd($todayRaw);

        $startDate = $startDateRaw ? $this->normalizeDateToYmd($startDateRaw) : $today;
        $endDate   = $endDateRaw ? $this->normalizeDateToYmd($endDateRaw) : $today;

        if ($startDate && $endDate && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $periodStartDate = $startDate ?: $today;
        $periodEndDate   = $endDate ?: $periodStartDate;

        if ($periodStartDate && $periodEndDate && $periodStartDate > $periodEndDate) {
            [$periodStartDate, $periodEndDate] = [$periodEndDate, $periodStartDate];
        }
        
        // Date range asli dipertahankan. Tidak ada hardcoded emergency clamp.

        // Batas ringan agar menu DSC tidak berat ketika range terlalu panjang.
        $periodMaxDays = 20;
        if ($periodStartDate && $periodEndDate) {
            $diffDays = Carbon::parse($periodStartDate)->diffInDays(Carbon::parse($periodEndDate)) + 1;
            if ($diffDays > $periodMaxDays) {
                $periodEndDate = Carbon::parse($periodStartDate)->addDays($periodMaxDays - 1)->format('Y-m-d');
            }
        }

        $shiftFilter = in_array($shiftFilter, ['all', '1', '2'], true)
            ? $shiftFilter
            : 'all';

        $normalizeOutletName = function ($name) {
            $name = strtoupper(trim((string) $name));
            return preg_replace('/\s+/', ' ', $name);
        };

        // CPU SAFE OPTIMIZE:
        // Data outlet dipakai untuk dropdown/grouping dan tidak perlu query ulang
        // setiap refresh DSC. Cache pendek 5 menit tidak mengubah rumus DSC, hanya
        // mengurangi beban CPU/database saat halaman dibuka berkali-kali.
        $allOutletsRaw = Cache::remember('dsc:all_outlets_grouped_source:v1', 300, function () {
            return DB::table('tbl_outlets')
                ->select('id', 'nama_outlet', 'status')
                ->orderBy('nama_outlet')
                ->orderBy('id')
                ->get();
        });

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

        if ($outletId === '' || $outletId === null || $outletId === 'all') {
            // CPU SAFE OPTIMIZE:
            // Blade DSC memang mewajibkan outlet dipilih dulu. Sebelumnya nilai default
            // "all" membuat controller mengambil SEMUA outlet lalu memproses query stok
            // besar walaupun UI hanya menampilkan mode ringan. Ini penyebab CPU bisa 100%.
            // Di sini "all" diperlakukan sebagai belum memilih outlet, sehingga rumus,
            // tampilan, dan data saat outlet sudah dipilih tetap sama seperti sebelumnya.
            $selectedOutletIds = [];
            $selectedOutletLabel = null;
            $selectedOutlet = null;
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

        if (! empty($selectedOutletIds) && $outletId !== 'all') {
            // FINAL FIX: ikutkan alias outlet dari tbl_outlet_alias, misalnya typo KARANGWELAS -> KARANGLEWAS.
            $selectedOutletIds = $this->expandOutletAliasIds($selectedOutletIds);

            if ($selectedOutlet) {
                $selectedOutlet->merged_ids = $selectedOutletIds;
                $selectedOutlet->is_merged = count($selectedOutletIds) > 1;
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
            // CPU SAFE OPTIMIZE:
            // Notifikasi lonceng hanya informasi ringkas H-1. Cache pendek 5 menit
            // menghindari query GROUP/MAX tbl_stock besar pada setiap load halaman,
            // tanpa mengubah rumus DSC atau data utama yang ditampilkan setelah filter.
            $missingOutlets = Cache::remember('dsc:missing_outlets:' . $missingCheckStartDate . ':v2', 300, function () use ($missingCheckStartDate, $outletGroups) {
            /*
             * OPTIMIZE: versi lama memakai correlated subquery + DATE(s.tanggal)
             * untuk setiap outlet. Saat outlet banyak, ini ikut menambah beban halaman DSC.
             * Versi ini ambil data stock H-1 dan last input dalam 2 query ringan,
             * lalu grouping outlet dilakukan di Collection yang sudah ada di memori.
             */
            $submittedFinalOutletIds = DB::table('tbl_stock')
                ->whereDate('tanggal', $missingCheckStartDate)
                ->distinct()
                ->pluck('outlet_id')
                ->map(fn ($id) => (int) $id);

            $submittedDraftOutletIds = DB::table('tbl_stock_draft')
                ->whereDate('tanggal', $missingCheckStartDate)
                ->where('is_draft', 1)
                ->distinct()
                ->pluck('outlet_id')
                ->map(fn ($id) => (int) $id);

            $submittedOutletIds = $submittedFinalOutletIds
                ->concat($submittedDraftOutletIds)
                ->unique()
                ->values()
                ->flip();

            $lastInputRows = DB::table('tbl_stock')
                ->select('outlet_id', DB::raw('MAX(tanggal) as last_input_date'))
                ->where('tanggal', '>=', Carbon::parse($missingCheckStartDate)->subDays(30)->startOfDay()->format('Y-m-d H:i:s'))
                ->where('tanggal', '<=', Carbon::parse($missingCheckStartDate)->endOfDay()->format('Y-m-d H:i:s'))
                ->groupBy('outlet_id')
                ->get()
                ->keyBy(fn ($r) => (int) $r->outlet_id);

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

                return $missingOutlets;
            });
        }

        $missingCount = $missingOutlets instanceof \Illuminate\Support\Collection ? $missingOutlets->count() : (is_countable($missingOutlets) ? count($missingOutlets) : 0);
        $missingOutlets = collect($missingOutlets)->take(50)->values();

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
                'periodRows'          => [],
                'periodSummary'       => [],
                'rapelBahanRows'      => [],

                'omsetActive'         => $this->defaultOmsetActive($today),

                'startDate'           => $periodStartDate,
                'endDate'             => $periodEndDate,
                'periodStartDate'     => $periodStartDate,
                'periodEndDate'       => $periodEndDate,
                'periodMaxDays'       => $periodMaxDays,
                'missingOutlets'      => $missingOutlets,
                'missingCount'        => $missingCount,
                'isPeriodLoaded'      => $isPeriodLoaded,
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

        /*
         * PATCH SALES DSC:
         * Beberapa outlet menyimpan sales dari form/shift ke tbl_sales_shift.
         * Jika omset setoran belum ada / masih 0, tampilkan fallback dari tbl_sales_shift
         * supaya kartu Sales Shift 1, Shift 2, dan Total Sales tidak kosong.
         */
        if ($salesShift1 == 0.0 && $salesShift2 == 0.0 && Schema::hasTable('tbl_sales_shift')) {
            $salesRows = DB::table('tbl_sales_shift')
                ->select('shift', DB::raw('SUM(COALESCE(sales_amount,0)) as total_sales'))
                ->whereIn('outlet_id', $selectedOutletIds)
                ->whereDate('tanggal', $today)
                ->whereIn('shift', [1, 2])
                ->groupBy('shift')
                ->get()
                ->keyBy('shift');

            $salesShift1 = (float) ($salesRows->get(1)->total_sales ?? 0);
            $salesShift2 = (float) ($salesRows->get(2)->total_sales ?? 0);
        }

        // CACHE SAFE: master bahan jarang berubah dan tidak mengubah rumus DSC.
        // Mengurangi query master pada setiap load Daily Stock Control.
        $bahanList = Cache::remember('dsc:bahan_dsc_active:v2', 300, function () {
            return DB::table('tbl_bahan_dsc')
                ->select('id', 'nama_bahan', 'satuan')
                ->where('is_active', 1)
                ->orderBy('id')
                ->get();
        });

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

        $mergeRows = function ($rows, string $source) use ($displayOutletId) {
            if (!$rows || $rows->isEmpty()) {
                return null;
            }

            /*
             * FIX DUPLIKAT PURCHASE IN DSC
             * Untuk 1 outlet + tanggal + shift + bahan, data stok harus dianggap 1 row aktif.
             * Jika ada row ganda akibat save ulang/draft lama, jangan SUM semua row karena
             * Purchase In AYAM BESAR bisa tampil dobel. Ambil row terbaru per outlet_id dulu,
             * baru jumlahkan antar outlet alias/group.
             */
            $rows = collect($rows)
                ->sortByDesc('updated_at')
                ->sortByDesc('id')
                ->groupBy(fn ($r) => (int) ($r->outlet_id ?? 0))
                ->map(fn ($outletRows) => collect($outletRows)->first())
                ->values();

            $first = $rows->sortByDesc('updated_at')->sortByDesc('id')->first();

            $row = clone $first;
            $row->row_source = $source;

            /*
             * FIX FOKUS DUPLIKAT PURCHASE ALIAS OUTLET
             * Form input/load memakai satu outlet kerja (displayOutletId).
             * Report sebelumnya memakai semua selectedOutletIds hasil alias, sehingga
             * Purchase In AYAM BESAR 500 bisa tampil 1000 jika data yang sama ada
             * di outlet alias. Untuk movement input, gunakan row outlet kerja dulu.
             * Jika tidak ada, baru fallback ke rows alias agar data lama tetap terbaca.
             */
            $movementSourceRows = $rows->where('outlet_id', $displayOutletId);
            if ($movementSourceRows->isEmpty()) {
                $latestOutletId = (int) optional($rows->sortByDesc('updated_at')->sortByDesc('id')->first())->outlet_id;
                $movementSourceRows = $latestOutletId > 0 ? $rows->where('outlet_id', $latestOutletId) : $rows;
            }

            $movementRows = $movementSourceRows->values();

            $row->purchase_in = (float) $movementRows->sum(fn ($r) => (float) ($r->purchase_in ?? 0));
            $row->mutasi_in = (float) $movementRows->sum(fn ($r) => (float) ($r->mutasi_in ?? 0));
            $row->mutasi_out = (float) $movementRows->sum(fn ($r) => (float) ($r->mutasi_out ?? 0));
            $row->adjustment_qty = (float) $movementRows->sum(fn ($r) => (float) ($r->adjustment_qty ?? 0));
            $row->used_qty = (float) $movementRows->sum(fn ($r) => (float) ($r->used_qty ?? 0));

            $latestEnding = $rows
                ->filter(fn ($r) => $r->ending_stock !== null)
                ->sortByDesc('updated_at')
                ->sortByDesc('id')
                ->first();

            $row->ending_stock = $latestEnding ? (float) $latestEnding->ending_stock : 0;

            $row->waste_product = (float) $rows->sum(fn ($r) => (float) ($r->waste_product ?? 0));
            $row->waste_bahan = (float) $rows->sum(fn ($r) => (float) ($r->waste_bahan ?? 0));
            $row->waste_tepung = (float) $rows->sum(fn ($r) => (float) ($r->waste_tepung ?? 0));
            $row->uang_plus = (float) $rows->sum(fn ($r) => (float) ($r->uang_plus ?? 0));

            $kets = $rows->pluck('keterangan')->filter()->values()->all();
            $row->keterangan = implode(' | ', array_values(array_unique($kets)));

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
                'tanggal'          => $today,
                'bahan_id'         => $bahanId,
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
                'wT_input'         => ($s1['wT_input'] ?? 0) + ($s2['wT_input'] ?? 0),
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
                    'uang'         => $v['uang'] ?? 0,
                    'ket'          => $v['ket'],
                ];
            }
        }

        [$periodRows, $periodSummary] = [[], []];
        if ($isPeriodLoaded && ! $isAllOutlet && ! empty($selectedOutletIds) && $periodStartDate && $periodEndDate) {
            [$periodRows, $periodSummary] = $this->buildDscPeriodReportRows(
                $selectedOutletIds,
                $displayOutletId,
                $bahanList,
                $periodStartDate,
                $periodEndDate,
                $shiftFilter
            );

            /*
             * PATCH REQUEST TIM - REKAP IKUT PERIODE
             * Sebelumnya REKAP tetap membaca tanggal akhir saja, sehingga Periode Awal
             * 09-06 dan 01-06 menghasilkan angka yang sama selama Periode Akhir sama.
             * Sekarang REKAP utama ikut start_date s/d end_date, tapi rumus lama tetap dipakai:
             * TOTAL = OPEN AWAL PERIODE + SUM(PIN) + SUM(MI) - SUM(MO)
             * ENDING = CLOSING TANGGAL AKHIR YANG SUDAH DIKOREKSI ADJ
             * USED = TOTAL - ENDING
             */
            if ($periodStartDate !== $periodEndDate) {
                // FIX ENDING RANGE:
                // Kirim rekap harian periode akhir yang sudah dihitung sebelumnya.
                // Dengan ini ENDING range wajib sama dengan ENDING saat filter tanggal akhir saja.
                $rekapRows = $this->buildDscPeriodAggregateRows($periodRows, $bahanList, $shiftFilter, $rekapRows);
                $salesShift1 = (float) collect($periodSummary)->sum('sales_s1');
                $salesShift2 = (float) collect($periodSummary)->sum('sales_s2');
            }
        }

        /*
         |--------------------------------------------------------------------------
         | FIX WASTE RANGE DSC
         |--------------------------------------------------------------------------
         | Filter Periode Awal - Akhir harus mengakumulasi Waste Product,
         | Waste Bahan, dan Waste Tepung pada REKAP. Patch ini fokus ke waste saja:
         | tidak mengubah OPEN, PIN, MI, MO, ENDING, USED, Draft, Final, export,
         | atau rumus stok lain yang sudah berjalan.
         |
         | Source priority tetap sama seperti tampilan DSC: Draft aktif menang dari
         | Final untuk tanggal/bahan/shift yang sama, lalu diakumulasi sepanjang range.
         |--------------------------------------------------------------------------
         */
        $shouldApplyWasteRange = ! empty($selectedOutletIds)
            && ! empty($periodStartDate)
            && ! empty($periodEndDate)
            && $periodStartDate !== $periodEndDate;

        if ($shouldApplyWasteRange) {
            $wasteRangeMap = $this->buildDscWasteRangeMap(
                $selectedOutletIds,
                $periodStartDate,
                $periodEndDate,
                $shiftFilter
            );

            if (! empty($wasteRangeMap)) {
                foreach ($rekapRows as &$rekapRow) {
                    $bahanIdForWaste = (int) ($rekapRow['bahan_id'] ?? 0);
                    if ($bahanIdForWaste <= 0 || ! isset($wasteRangeMap[$bahanIdForWaste])) {
                        continue;
                    }

                    $waste = $wasteRangeMap[$bahanIdForWaste];

                    $rekapRow['wP'] = (float) ($waste['wP'] ?? 0);
                    $rekapRow['wB'] = (float) ($waste['wB'] ?? 0);
                    $rekapRow['wT_input'] = (float) ($waste['wT_input'] ?? 0);
                    $rekapRow['wT'] = (float) ($waste['wT'] ?? ($rekapRow['wP'] + $rekapRow['wB'] + $rekapRow['wT_input']));

                    $namaNormWaste = strtolower(trim((string) ($rekapRow['nama'] ?? '')));
                    if ($namaNormWaste === 'tepung breader') {
                        $rekapRow['actualTepung'] = (float) ($rekapRow['used'] ?? 0) - (float) ($rekapRow['wT'] ?? 0);
                    }
                }
                unset($rekapRow);
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
            'periodRows'          => $periodRows,
            'periodSummary'       => $periodSummary,
            'rapelBahanRows'      => $bahanList,

            'omsetActive'         => $omsetActive,

            'startDate'           => $periodStartDate,
            'endDate'             => $periodEndDate,
            'periodStartDate'     => $periodStartDate,
            'periodEndDate'       => $periodEndDate,
            'periodMaxDays'       => $periodMaxDays,
            'missingOutlets'      => $missingOutlets,
            'missingCount'        => $missingCount,
            'isPeriodLoaded'      => $isPeriodLoaded,
            'missingCheckStartDate' => $missingCheckStartDate ?? null,
            'missingCheckEndDate'   => $missingCheckEndDate ?? null,
            'isSuperadmin'        => $isSuperadmin,
        ];
    }


    /*
     |--------------------------------------------------------------------------
     | PATCH REQUEST TIM - DATA PERIODE DSC
     |--------------------------------------------------------------------------
     | Helper ini hanya membangun data tab PERIODE DSC.
     | Rumus sengaja disamakan dengan buildDailyStockControlData():
     | TOTAL  = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
     | ENDING = ENDING BASE + ADJ
     | USED   = TOTAL - ENDING
     |
     | Tidak mengubah data stok, tidak menyimpan apa pun, dan tidak mengganggu
     | REKAP/DETAIL/OMSET/ADJUSTMENT harian yang sudah berjalan.
     */
private function buildDscPeriodReportRows(array $selectedOutletIds, int $displayOutletId, $bahanList, string $startDate, string $endDate, string $shiftFilter): array
{
    $selectedOutletIds = array_values(array_unique(array_map('intval', $selectedOutletIds)));
    $selectedOutletIds = array_values(array_filter($selectedOutletIds, fn ($id) => $id > 0));

    if (empty($selectedOutletIds)) {
        return [[], []];
    }

    $bahanIds = $bahanList->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

    if (empty($bahanIds)) {
        return [[], []];
    }

    $period = Carbon::parse($startDate)->daysUntil(Carbon::parse($endDate)->addDay());

    $dateKeys = [];
    foreach ($period as $dateObj) {
        $dateKeys[] = $dateObj->format('Y-m-d');
    }

    if (empty($dateKeys)) {
        return [[], []];
    }

    $rangeStart = $startDate . ' 00:00:00';
    $rangeEnd   = $endDate . ' 23:59:59';

    $periodRows = [];
    $periodSummary = [];
    $rowNo = 1;

    $mergeRows = function ($rows, string $source) use ($displayOutletId) {
        if (!$rows || $rows->isEmpty()) {
            return null;
        }

        /*
         * FIX DUPLIKAT PURCHASE IN DSC PERIODE
         * Dalam 1 tanggal + shift + bahan + outlet, hanya row terbaru yang aktif.
         * Jangan SUM semua row duplikat karena Purchase In bisa tampil dobel saat
         * filter start-end. Setelah dedupe per outlet_id, baru jumlahkan antar outlet alias/group.
         */
        $rows = collect($rows)
            ->sortByDesc('updated_at')
            ->sortByDesc('id')
            ->groupBy(fn ($r) => (int) ($r->outlet_id ?? 0))
            ->map(fn ($outletRows) => collect($outletRows)->first())
            ->values();

        $latest = null;
        $latestEnding = null;

        $purchaseIn = 0.0;
        $mutasiIn = 0.0;
        $mutasiOut = 0.0;
        $adjustmentQty = 0.0;
        $usedQty = 0.0;
        $wasteProduct = 0.0;
        $wasteBahan = 0.0;
        $wasteTepung = 0.0;
        $uangPlus = 0.0;
        $ketParts = [];

        foreach ($rows as $r) {
            if (
                !$latest ||
                (($r->updated_at ?? '') > ($latest->updated_at ?? '')) ||
                (($r->updated_at ?? '') == ($latest->updated_at ?? '') && (int) $r->id > (int) $latest->id)
            ) {
                $latest = $r;
            }

            if ($r->ending_stock !== null) {
                if (
                    !$latestEnding ||
                    (($r->updated_at ?? '') > ($latestEnding->updated_at ?? '')) ||
                    (($r->updated_at ?? '') == ($latestEnding->updated_at ?? '') && (int) $r->id > (int) $latestEnding->id)
                ) {
                    $latestEnding = $r;
                }
            }

            $purchaseIn += (float) ($r->purchase_in ?? 0);
            $mutasiIn += (float) ($r->mutasi_in ?? 0);
            $mutasiOut += (float) ($r->mutasi_out ?? 0);
            $adjustmentQty += (float) ($r->adjustment_qty ?? 0);
            $usedQty += (float) ($r->used_qty ?? 0);
            $wasteProduct += (float) ($r->waste_product ?? 0);
            $wasteBahan += (float) ($r->waste_bahan ?? 0);
            $wasteTepung += (float) ($r->waste_tepung ?? 0);
            $uangPlus += (float) ($r->uang_plus ?? 0);

            $ket = trim((string) ($r->keterangan ?? ''));
            if ($ket !== '') {
                $ketParts[] = $ket;
            }
        }

        $row = clone $latest;
        $row->row_source = $source;

        /*
         * FIX FOKUS DUPLIKAT PURCHASE ALIAS OUTLET - PERIODE DSC
         * Movement input diprioritaskan dari outlet kerja (displayOutletId),
         * bukan dijumlah dari semua alias outlet.
         */
        $allMovementRows = collect($rows);
        $movementRows = $allMovementRows->where('outlet_id', $displayOutletId);
        if ($movementRows->isEmpty()) {
            $latestOutletId = (int) optional($allMovementRows->sortByDesc('updated_at')->sortByDesc('id')->first())->outlet_id;
            $movementRows = $latestOutletId > 0 ? $allMovementRows->where('outlet_id', $latestOutletId) : $allMovementRows;
        }
        $movementRows = $movementRows->values();

        $row->purchase_in = (float) $movementRows->sum(fn ($r) => (float) ($r->purchase_in ?? 0));
        $row->mutasi_in = (float) $movementRows->sum(fn ($r) => (float) ($r->mutasi_in ?? 0));
        $row->mutasi_out = (float) $movementRows->sum(fn ($r) => (float) ($r->mutasi_out ?? 0));
        $row->adjustment_qty = (float) $movementRows->sum(fn ($r) => (float) ($r->adjustment_qty ?? 0));
        $row->used_qty = (float) $movementRows->sum(fn ($r) => (float) ($r->used_qty ?? 0));
        $row->ending_stock = $latestEnding ? (float) $latestEnding->ending_stock : 0;
        $row->waste_product = $wasteProduct;
        $row->waste_bahan = $wasteBahan;
        $row->waste_tepung = $wasteTepung;
        $row->uang_plus = $uangPlus;
        $row->keterangan = implode(' | ', array_values(array_unique($ketParts)));

        return $row;
    };

    $calcShiftValues = function (float $open, $row): array {
        $pin = $row ? (float) ($row->purchase_in ?? 0) : 0.0;
        $mi = $row ? (float) ($row->mutasi_in ?? 0) : 0.0;
        $mo = $row ? (float) ($row->mutasi_out ?? 0) : 0.0;
        $adj = $row ? (float) ($row->adjustment_qty ?? 0) : 0.0;
        $endingBase = $row && $row->ending_stock !== null ? (float) $row->ending_stock : 0.0;

        $total = $open + $pin + $mi - $mo;
        $ending = $endingBase + $adj;
        $used = $total - $ending;

        $wP = $row ? (float) ($row->waste_product ?? 0) : 0.0;
        $wB = $row ? (float) ($row->waste_bahan ?? 0) : 0.0;
        $wTDb = $row ? (float) ($row->waste_tepung ?? 0) : 0.0;

        return [
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
            'wT' => $wP + $wB + $wTDb,
            'wT_input' => $wTDb,
            'uang' => $row ? (float) ($row->uang_plus ?? 0) : 0.0,
            'ket' => $row ? (string) ($row->keterangan ?? '') : '',
            'source' => $row->row_source ?? null,
        ];
    };

    $omsetByDate = DB::table('tbl_dsc_omset_setoran')
        ->whereIn('outlet_id', $selectedOutletIds)
        ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
        ->select(
            DB::raw('DATE(tanggal) as tanggal_key'),
            DB::raw('SUM(COALESCE(s1_total_transaction,0)) as sales_s1'),
            DB::raw('SUM(COALESCE(s2_total_transaction,0)) as sales_s2')
        )
        ->groupBy(DB::raw('DATE(tanggal)'))
        ->get()
        ->keyBy('tanggal_key');

    $groupRowsByDateAndBahan = function ($rows) {
        return $rows
            ->groupBy(fn ($row) => $this->normalizeDateToYmd((string) ($row->tanggal ?? '')))
            ->map(fn ($dateRows) => $dateRows->groupBy('bahan_id'));
    };

    $draftShift1ByDate = $groupRowsByDateAndBahan(
        DB::table('tbl_stock_draft')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
            ->where('shift', 1)
            ->where('is_draft', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
    );

    $finalShift1ByDate = $groupRowsByDateAndBahan(
        DB::table('tbl_stock')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
            ->where('shift', '1')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
    );

    $draftShift2ByDate = $groupRowsByDateAndBahan(
        DB::table('tbl_stock_draft')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
            ->where('shift', 2)
            ->where('is_draft', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
    );

    $finalShift2ByDate = $groupRowsByDateAndBahan(
        DB::table('tbl_stock')
            ->whereIn('outlet_id', $selectedOutletIds)
            ->whereBetween('tanggal', [$rangeStart, $rangeEnd])
            ->where('shift', '2')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
    );

    $openingBulkByDate = [];
    foreach ($dateKeys as $date) {
        $openingBulkByDate[$date] = $this->getOpeningBulkFromAliasIds(
            $selectedOutletIds,
            $displayOutletId,
            $bahanIds,
            $date
        );
    }

    foreach ($dateKeys as $date) {
        $openingBulkMap = $openingBulkByDate[$date] ?? [];

        $getOpeningMerged = function (int $bahanId, int $shift) use ($openingBulkMap, $selectedOutletIds, $displayOutletId, $date) {
            return isset($openingBulkMap[$bahanId][$shift])
                ? (float) $openingBulkMap[$bahanId][$shift]
                : (float) $this->getOpeningFromAliasIds($selectedOutletIds, $displayOutletId, $bahanId, $date, $shift);
        };

        $draftShift1Rows = $draftShift1ByDate->get($date, collect());
        $finalShift1Rows = $finalShift1ByDate->get($date, collect());
        $draftShift2Rows = $draftShift2ByDate->get($date, collect());
        $finalShift2Rows = $finalShift2ByDate->get($date, collect());

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

        $omsetRow = $omsetByDate->get($date);

        $summary = [
            'tanggal' => $date,
            'sales_s1' => (float) ($omsetRow->sales_s1 ?? 0),
            'sales_s2' => (float) ($omsetRow->sales_s2 ?? 0),
            'sales_total' => (float) (($omsetRow->sales_s1 ?? 0) + ($omsetRow->sales_s2 ?? 0)),
            'uang_plus' => 0.0,
            'used_minus_count' => 0,
            'used_plus_count' => 0,
            'row_count' => 0,
            'has_dsc' => false,
        ];

        foreach ($bahanList as $b) {
            $bahanId = (int) $b->id;

            $r1 = $mergedShift1Rows[$bahanId] ?? null;
            $r2 = $mergedShift2Rows[$bahanId] ?? null;

            $openS1 = (float) $getOpeningMerged($bahanId, 1);
            $openS2 = ($r1 && $r1->ending_stock !== null)
                ? (float) $r1->ending_stock
                : (float) $getOpeningMerged($bahanId, 2);

            $s1 = $calcShiftValues($openS1, $r1);
            $s2 = $calcShiftValues($openS2, $r2);

            $sourceS1 = $s1['source'] ?? null;
            $sourceS2 = $s2['source'] ?? null;

            if ($shiftFilter === '1') {
                $openRekap = $s1['open'];
                $pin = $s1['pin'];
                $mi = $s1['mi'];
                $mo = $s1['mo'];
                $adj = $s1['adj'];
                $total = $s1['total'];
                $ending = $s1['ending_stock'];
                $used = $s1['used'];
                $wP = $s1['wP'];
                $wB = $s1['wB'];
                $wT = $s1['wT'];
                $wTInput = $s1['wT_input'] ?? 0;
                $uang = $s1['uang'];
                $ketParts = !empty($s1['ket']) ? ['S1: ' . $s1['ket']] : [];
                $rowSource = $sourceS1;
            } elseif ($shiftFilter === '2') {
                $openRekap = $s2['open'];
                $pin = $s2['pin'];
                $mi = $s2['mi'];
                $mo = $s2['mo'];
                $adj = $s2['adj'];
                $total = $s2['total'];
                $ending = $s2['ending_stock'];
                $used = $s2['used'];
                $wP = $s2['wP'];
                $wB = $s2['wB'];
                $wT = $s2['wT'];
                $wTInput = $s2['wT_input'] ?? 0;
                $uang = $s2['uang'];
                $ketParts = !empty($s2['ket']) ? ['S2: ' . $s2['ket']] : [];
                $rowSource = $sourceS2;
            } else {
                $openRekap = $openS1;

                $pin = $s1['pin'] + $s2['pin'];
                $mi = $s1['mi'] + $s2['mi'];
                $mo = $s1['mo'] + $s2['mo'];
                $adj = $s1['adj'] + $s2['adj'];

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

                $total = $openRekap + $pin + $mi - $mo;
                $used = $total - $ending;

                $wP = $s1['wP'] + $s2['wP'];
                $wB = $s1['wB'] + $s2['wB'];
                $wT = $s1['wT'] + $s2['wT'];
                $wTInput = ($s1['wT_input'] ?? 0) + ($s2['wT_input'] ?? 0);
                $uang = $s1['uang'] + $s2['uang'];

                $ketParts = [];
                if (!empty($s1['ket'])) $ketParts[] = 'S1: ' . $s1['ket'];
                if (!empty($s2['ket'])) $ketParts[] = 'S2: ' . $s2['ket'];

                $rowSource = ($sourceS1 === 'draft' || $sourceS2 === 'draft')
                    ? 'draft'
                    : (($sourceS1 === 'final' || $sourceS2 === 'final') ? 'final' : null);
            }

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

            $hasSource = $sourceS1 !== null || $sourceS2 !== null;

            $hasValue =
                abs($openRekap) > 0.000001 ||
                abs($pin) > 0.000001 ||
                abs($mi) > 0.000001 ||
                abs($mo) > 0.000001 ||
                abs($adj) > 0.000001 ||
                abs($ending) > 0.000001 ||
                abs($used) > 0.000001 ||
                abs($wP) > 0.000001 ||
                abs($wB) > 0.000001 ||
                abs($wTInput) > 0.000001 ||
                abs($uang) > 0.000001;

            if (!$hasSource && !$hasValue) {
                continue;
            }

            $namaNorm = strtolower(trim((string) $b->nama_bahan));
            $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wT) : 0.0;

            $periodRows[] = [
                'no' => $rowNo++,
                'tanggal' => $date,
                'bahan_id' => $bahanId,
                'nama' => (string) $b->nama_bahan,
                'sat' => (string) $b->satuan,
                'source' => $rowSource,
                'source_s1' => $sourceS1,
                'source_s2' => $sourceS2,
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
                'wT_input' => $wTInput,
                'actualTepung' => $actualTepung,
                'shift1' => $s1['used'],
                'shift2' => $s2['used'],
                'uang' => $uang,
                'ket' => implode(' | ', $ketParts),
                'open_stock_right' => $closingToday,
            ];

            $summary['row_count']++;
            $summary['has_dsc'] = $summary['has_dsc'] || $hasSource;
            $summary['uang_plus'] += $uang;

            if ($used < 0) $summary['used_minus_count']++;
            if ($used > 0) $summary['used_plus_count']++;
        }

        $periodSummary[] = $summary;
    }

    return [$periodRows, $periodSummary];
}

    /**
     * PATCH REQUEST TIM - AGREGASI REKAP BERDASARKAN PERIODE
     *
     * Input helper ini adalah periodRows yang dihitung per tanggal memakai rumus DSC lama.
     * Helper ini hanya menggabungkan untuk tampilan REKAP agar filter Periode Awal
     * benar-benar mempengaruhi hasil.
     *
     * Rumus periode:
     * - OPEN    = open pada tanggal pertama dalam periode untuk bahan tersebut
     * - PIN/MI/MO/WASTE/UANG PLUS = akumulasi selama periode
     * - ENDING  = closing tanggal terakhir dalam periode untuk bahan tersebut
     * - TOTAL   = OPEN + PIN + MI - MO
     * - USED    = TOTAL - ENDING
     *
     * Tidak ada query baru dan tidak ada proses simpan data.
     */
    private function buildDscPeriodAggregateRows(array $periodRows, $bahanList, string $shiftFilter, array $periodEndRekapRows = []): array
    {
        if (empty($periodRows)) {
            return [];
        }

        /*
         |--------------------------------------------------------------------------
         | FIX ENDING RANGE FINAL
         |--------------------------------------------------------------------------
         | Saat filter range, REKAP periode sebelumnya mengambil ENDING dari periodRows.
         | Akibatnya bisa nyasar ke closing lama, contoh 28-05 s/d 10-06 tampil 680,
         | padahal saat tanggal 10 dibuka sendiri ENDING-nya 840.
         |
         | Controller sebelum masuk fungsi ini sebenarnya sudah membangun $rekapRows
         | untuk tanggal akhir periode. Maka ENDING range harus mengambil dari
         | $periodEndRekapRows tersebut, bukan memilih ulang dari periodRows.
         |--------------------------------------------------------------------------
         */
        $endingByBahanFromPeriodEndRekap = collect($periodEndRekapRows)
            ->filter(fn ($row) => isset($row['bahan_id']))
            ->keyBy(fn ($row) => (int) $row['bahan_id'])
            ->map(fn ($row) => (float) ($row['ending_stock'] ?? 0));

        /*
         |--------------------------------------------------------------------------
         | FIX ENDING REKAP RANGE V2
         |--------------------------------------------------------------------------
         | ENDING range wajib sama dengan ENDING harian pada tanggal akhir periode.
         | Supaya tidak nyasar ke closing row lain di periodRows, helper ini ambil
         | ulang closing tanggal akhir langsung dari data periodRows tanggal max.
         | Ini tidak mengubah rumus lain: OPEN/PIN/MI/MO tetap periode, USED tetap
         | akumulasi harian yang sudah dipatch untuk kasus minus.
         |--------------------------------------------------------------------------
         */
        $periodEndDateForEnding = (string) collect($periodRows)->max('tanggal');

        $endingByBahanAtPeriodEnd = collect($periodRows)
            ->filter(fn ($row) => (string) ($row['tanggal'] ?? '') === $periodEndDateForEnding)
            ->groupBy('bahan_id')
            ->map(function ($rows) use ($shiftFilter) {
                $rows = collect($rows)->sortBy('no')->values();

                if ($shiftFilter === '1') {
                    $row = $rows
                        ->filter(fn ($r) => ($r['source_s1'] ?? null) !== null || abs((float) ($r['ending_stock'] ?? 0)) > 0.000001)
                        ->last();

                    return $row ? (float) ($row['ending_stock'] ?? $row['open_stock_right'] ?? 0) : 0.0;
                }

                if ($shiftFilter === '2') {
                    $row = $rows
                        ->filter(fn ($r) => ($r['source_s2'] ?? null) !== null || abs((float) ($r['ending_stock'] ?? 0)) > 0.000001)
                        ->last();

                    return $row ? (float) ($row['ending_stock'] ?? $row['open_stock_right'] ?? 0) : 0.0;
                }

                $row = $rows->last();
                return $row ? (float) ($row['open_stock_right'] ?? $row['ending_stock'] ?? 0) : 0.0;
            });

        $rowsByBahan = collect($periodRows)
            ->sortBy([['tanggal', 'asc'], ['no', 'asc']])
            ->groupBy('bahan_id');

        $result = [];
        $no = 1;

        foreach ($bahanList as $b) {
            $bahanId = (int) $b->id;
            $rows = $rowsByBahan->get($bahanId);

            if (!$rows || $rows->isEmpty()) {
                continue;
            }

            $rowsAsc = $rows->sortBy([['tanggal', 'asc'], ['no', 'asc']])->values();

            /*
             |--------------------------------------------------------------------------
             | FIX ENDING REKAP RANGE
             |--------------------------------------------------------------------------
             | Untuk filter periode/range, ENDING harus mengikuti closing di tanggal
             | akhir periode untuk bahan tersebut. Sebelumnya menggunakan sortBy desc
             | multi kolom lalu first(), pada beberapa kondisi bisa mengambil row
             | bukan tanggal akhir sehingga summary 28-05 s/d 10-06 tampil 680,
             | padahal harian tanggal 10 sudah 840.
             |
             | OPEN tetap dari tanggal awal periode, PIN/MI/MO/ADJ tetap akumulasi
             | periode, dan USED tetap akumulasi used harian sesuai patch minus.
             |--------------------------------------------------------------------------
             */
            $firstRow = $rowsAsc->first();

            $lastTanggal = (string) $rowsAsc->max('tanggal');
            $lastDateRows = $rowsAsc
                ->filter(fn ($row) => (string) ($row['tanggal'] ?? '') === $lastTanggal)
                ->sortByDesc('no')
                ->values();

            $lastRow = $lastDateRows->first() ?: $rowsAsc->last();

            $open = (float) ($firstRow['open'] ?? 0);
            $pin = (float) $rowsAsc->sum('pin');
            $mi = (float) $rowsAsc->sum('mi');
            $mo = (float) $rowsAsc->sum('mo');
            $adj = (float) $rowsAsc->sum('adj');

            // Closing periode wajib dari tanggal akhir yang benar,
            // sama seperti harian pada Periode Akhir.
            $endingFromRows = (float) ($lastRow['open_stock_right'] ?? $lastRow['ending_stock'] ?? 0);
            $endingFromEndDate = $endingByBahanAtPeriodEnd->has($bahanId)
                ? (float) $endingByBahanAtPeriodEnd->get($bahanId)
                : null;

            // Prioritas utama: ending dari REKAP harian periode akhir.
            // Ini membuat range 28-05 s/d 10-06 sinkron dengan filter 10-06 saja.
            $endingFromDailyPeriodEnd = $endingByBahanFromPeriodEndRekap->has($bahanId)
                ? (float) $endingByBahanFromPeriodEndRekap->get($bahanId)
                : null;

            if ($endingFromDailyPeriodEnd !== null) {
                $ending = $endingFromDailyPeriodEnd;
            } elseif ($endingFromEndDate !== null) {
                $ending = $endingFromEndDate;
            } else {
                $ending = $endingFromRows;
            }

            $total = $open + $pin + $mi - $mo;

            /*
             |--------------------------------------------------------------------------
             | FIX FINAL USED DSC RANGE
             |--------------------------------------------------------------------------
             | Untuk REKAP DSC periode, USED harus mengikuti rumus stok final:
             |
             | TOTAL = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
             | USED  = TOTAL - ENDING
             |
             | Contoh TEH PUCUK:
             | OPEN 97 + PURCHASE 96 - MUTASI OUT 48 = TOTAL 145
             | ENDING 47
             | USED = 145 - 47 = 98
             |
             | Jangan pakai akumulasi harian di tampilan REKAP/QCR karena bisa membuat
             | Usage DSC periode tampil hanya 8/55, padahal rumus periode adalah 98.
             */
            $used = $total - $ending;

            $wP = (float) $rowsAsc->sum('wP');
            $wB = (float) $rowsAsc->sum('wB');
            $wT = (float) $rowsAsc->sum('wT');
            $wTInput = (float) $rowsAsc->sum('wT_input');
            $uang = (float) $rowsAsc->sum('uang');
            $shift1 = (float) $rowsAsc->sum('shift1');
            $shift2 = (float) $rowsAsc->sum('shift2');

            $sourceS1Values = $rowsAsc->pluck('source_s1')->filter()->values();
            $sourceS2Values = $rowsAsc->pluck('source_s2')->filter()->values();

            $sourceS1 = $sourceS1Values->contains('draft') ? 'draft' : ($sourceS1Values->contains('final') ? 'final' : null);
            $sourceS2 = $sourceS2Values->contains('draft') ? 'draft' : ($sourceS2Values->contains('final') ? 'final' : null);

            $rowSourceValues = $rowsAsc->pluck('source')->filter()->values();
            $rowSource = $rowSourceValues->contains('draft') ? 'draft' : ($rowSourceValues->contains('final') ? 'final' : null);

            if ($shiftFilter === '1') {
                $sourceS2 = null;
                $rowSource = $sourceS1;
            } elseif ($shiftFilter === '2') {
                $sourceS1 = null;
                $rowSource = $sourceS2;
            }

            $ketParts = [];
            foreach ($rowsAsc as $row) {
                $ket = trim((string) ($row['ket'] ?? ''));
                if ($ket !== '') {
                    $ketParts[] = Carbon::parse((string) $row['tanggal'])->format('d-m-Y') . ': ' . $ket;
                }
            }

            $namaNorm = strtolower(trim((string) $b->nama_bahan));
            $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wT) : 0.0;

            $result[] = [
                'no'               => $no++,
                'nama'             => (string) $b->nama_bahan,
                'sat'              => (string) $b->satuan,
                'source'           => $rowSource,
                'source_s1'        => $sourceS1,
                'source_s2'        => $sourceS2,
                'open'             => $open,
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
                'wT_input'         => $wTInput,
                'actualTepung'     => $actualTepung,
                'shift1'           => $shift1,
                'shift2'           => $shift2,
                'uang'             => $uang,
                'ket'              => implode(' | ', $ketParts),
                'open_stock_right' => $ending,
            ];
        }

        return $result;
    }

    /**
     * FIX WASTE RANGE DSC
     *
     * Ambil akumulasi waste sepanjang filter start_date - end_date untuk REKAP.
     * Fokus hanya field waste agar tidak mengubah rumus stok lain.
     *
     * Priority per tanggal/bahan/shift:
     * - tbl_stock_draft is_draft=1 menang jika ada
     * - jika tidak ada draft, fallback ke tbl_stock final
     */
    private function buildDscWasteRangeMap(array $selectedOutletIds, string $startDate, string $endDate, string $shiftFilter = 'all'): array
    {
        $selectedOutletIds = array_values(array_unique(array_map('intval', $selectedOutletIds)));
        $selectedOutletIds = array_values(array_filter($selectedOutletIds, fn ($id) => $id > 0));

        if (empty($selectedOutletIds) || $startDate === '' || $endDate === '') {
            return [];
        }

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $shiftFilter = in_array((string) $shiftFilter, ['all', '1', '2'], true)
            ? (string) $shiftFilter
            : 'all';

        $rangeStart = $startDate . ' 00:00:00';
        $rangeEnd = $endDate . ' 23:59:59';

        $makeQuery = function (string $table, bool $draft) use ($selectedOutletIds, $rangeStart, $rangeEnd, $shiftFilter) {
            $query = DB::table($table)
                ->select(
                    DB::raw('DATE(tanggal) as tanggal_key'),
                    'bahan_id',
                    'shift',
                    DB::raw('SUM(COALESCE(waste_product,0)) as waste_product'),
                    DB::raw('SUM(COALESCE(waste_bahan,0)) as waste_bahan'),
                    DB::raw('SUM(COALESCE(waste_tepung,0)) as waste_tepung')
                )
                ->whereIn('outlet_id', $selectedOutletIds)
                ->where('tanggal', '>=', $rangeStart)
                ->where('tanggal', '<=', $rangeEnd);

            if ($draft) {
                $query->where('is_draft', 1);
            }

            if ($shiftFilter !== 'all') {
                $query->where('shift', (int) $shiftFilter);
            }

            return $query
                ->groupBy(DB::raw('DATE(tanggal)'), 'bahan_id', 'shift')
                ->get();
        };

        $draftRows = Schema::hasTable('tbl_stock_draft')
            ? $makeQuery('tbl_stock_draft', true)
            : collect();

        $finalRows = Schema::hasTable('tbl_stock')
            ? $makeQuery('tbl_stock', false)
            : collect();

        $chosenByDateBahanShift = [];

        foreach ($finalRows as $row) {
            $key = implode('|', [
                (string) $row->tanggal_key,
                (int) $row->bahan_id,
                (int) $row->shift,
            ]);

            $chosenByDateBahanShift[$key] = $row;
        }

        foreach ($draftRows as $row) {
            $key = implode('|', [
                (string) $row->tanggal_key,
                (int) $row->bahan_id,
                (int) $row->shift,
            ]);

            // Draft aktif menang dari final untuk key yang sama.
            $chosenByDateBahanShift[$key] = $row;
        }

        $result = [];

        foreach ($chosenByDateBahanShift as $row) {
            $bahanId = (int) ($row->bahan_id ?? 0);
            if ($bahanId <= 0) {
                continue;
            }

            if (! isset($result[$bahanId])) {
                $result[$bahanId] = [
                    'wP' => 0.0,
                    'wB' => 0.0,
                    'wT_input' => 0.0,
                    'wT' => 0.0,
                ];
            }

            $wP = (float) ($row->waste_product ?? 0);
            $wB = (float) ($row->waste_bahan ?? 0);
            $wTInput = (float) ($row->waste_tepung ?? 0);

            $result[$bahanId]['wP'] += $wP;
            $result[$bahanId]['wB'] += $wB;
            $result[$bahanId]['wT_input'] += $wTInput;
            $result[$bahanId]['wT'] += ($wP + $wB + $wTInput);
        }

        return $result;
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
            $result[$bahanId] = [
                1 => 0.0,
                2 => 0.0,
            ];
        }

        if (empty($aliasIds) || empty($bahanIds)) {
            return $result;
        }

        /*
        |--------------------------------------------------------------------------
        | Helper pilih row terbaru per bahan/shift
        |--------------------------------------------------------------------------
        | Priority:
        | - draft lebih tinggi dari final, karena tampilan DSC aktif memakai draft
        |   jika ada.
        | - updated_at/id terbaru menang.
        */
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

        /*
        |--------------------------------------------------------------------------
        | 1) Opening shift 2 = ending shift 1 pada tanggal yang sama
        |--------------------------------------------------------------------------
        | Kalau shift 1 hari ini ada ending 0, opening shift 2 tetap 0.
        | Jangan fallback ke data lain hanya karena nilainya 0.
        */
        $todayFinalRows = DB::table('tbl_stock')
            ->select('bahan_id', 'ending_stock', 'updated_at', 'id', DB::raw('1 as source_priority'))
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $bahanIds)
            ->where('tanggal', $tanggal)
            ->where('shift', 1)
            ->whereNotNull('ending_stock')
            ->get();

        $todayDraftRows = DB::table('tbl_stock_draft')
            ->select('bahan_id', 'ending_stock', 'updated_at', 'id', DB::raw('2 as source_priority'))
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
        |--------------------------------------------------------------------------
        | 2) Cari tanggal closing terakhir sebelum tanggal aktif
        |--------------------------------------------------------------------------
        | Opening shift 1 tanggal aktif = closing terakhir sebelum tanggal aktif.
        */
        $lastFinal = DB::table('tbl_stock')
            ->select('bahan_id', DB::raw('MAX(tanggal) as last_date'))
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $bahanIds)
            ->where('tanggal', '<', $tanggal)
            ->whereNotNull('ending_stock')
            ->groupBy('bahan_id');

        $lastDraft = DB::table('tbl_stock_draft')
            ->select('bahan_id', DB::raw('MAX(tanggal) as last_date'))
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $bahanIds)
            ->where('tanggal', '<', $tanggal)
            ->where('is_draft', 1)
            ->whereNotNull('ending_stock')
            ->groupBy('bahan_id');

        $lastRows = DB::query()
            ->fromSub($lastFinal->unionAll($lastDraft), 'x')
            ->select('bahan_id', DB::raw('MAX(last_date) as last_date'))
            ->groupBy('bahan_id')
            ->get();

        $lastByBahan = [];
        foreach ($lastRows as $row) {
            $bahanId = (int) $row->bahan_id;
            $lastDate = $this->normalizeDateToYmd((string) ($row->last_date ?? ''));

            if ($lastDate === '') {
                continue;
            }

            $lastByBahan[$bahanId] = $lastDate;
        }

        if (empty($lastByBahan)) {
            return $result;
        }

        $lastDates = array_values(array_unique(array_values($lastByBahan)));
        $lastBahanIds = array_keys($lastByBahan);

        /*
        |--------------------------------------------------------------------------
        | 3) Ambil closing tanggal terakhir, shift 1 dan shift 2
        |--------------------------------------------------------------------------
        | Patch utama:
        | - Kalau Shift 2 ada, pakai Shift 2 apa pun nilainya, termasuk 0.
        | - Fallback ke Shift 1 hanya kalau Shift 2 benar-benar tidak ada.
        */
        $closingFinalRows = DB::table('tbl_stock')
            ->select(
                'bahan_id',
                'tanggal',
                'shift',
                'ending_stock',
                'adjustment_qty',
                'updated_at',
                'id',
                DB::raw('1 as source_priority')
            )
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $lastBahanIds)
            ->whereIn('tanggal', $lastDates)
            ->whereIn('shift', [1, 2])
            ->whereNotNull('ending_stock')
            ->get();

        $closingDraftRows = DB::table('tbl_stock_draft')
            ->select(
                'bahan_id',
                'tanggal',
                'shift',
                'ending_stock',
                'adjustment_qty',
                'updated_at',
                'id',
                DB::raw('2 as source_priority')
            )
            ->whereIn('outlet_id', $aliasIds)
            ->whereIn('bahan_id', $lastBahanIds)
            ->whereIn('tanggal', $lastDates)
            ->whereIn('shift', [1, 2])
            ->where('is_draft', 1)
            ->whereNotNull('ending_stock')
            ->get();

        $closingRows = $closingFinalRows
            ->concat($closingDraftRows)
            ->filter(function ($row) use ($lastByBahan) {
                $bahanId = (int) $row->bahan_id;

                return isset($lastByBahan[$bahanId])
                    && $this->normalizeDateToYmd((string) $row->tanggal) === $lastByBahan[$bahanId];
            })
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
            ->groupBy(function ($row) {
                return ((int) $row->bahan_id) . '_' . ((int) $row->shift);
            });

        $closingByBahan = [];

        foreach ($closingRows as $key => $rows) {
            $first = $rows->first();

            if (! $first || $first->ending_stock === null) {
                continue;
            }

            $bahanId = (int) $first->bahan_id;
            $shift = (int) $first->shift;

            $closingByBahan[$bahanId][$shift] = (float) $first->ending_stock;
        }

        foreach ($closingByBahan as $bahanId => $closingByShift) {
            $hasShift2 = array_key_exists(2, $closingByShift);
            $hasShift1 = array_key_exists(1, $closingByShift);

            if ($hasShift2) {
                $closing = (float) $closingByShift[2];
            } elseif ($hasShift1) {
                $closing = (float) $closingByShift[1];
            } else {
                $closing = 0.0;
            }

            $result[(int) $bahanId][1] = $closing;

            /*
            * Shift 2 fallback sama dengan opening shift 1 hanya kalau belum ada
            * ending shift 1 hari ini.
            */
            if (! isset($sameDayShift1[$bahanId])) {
                $result[(int) $bahanId][2] = $closing;
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
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

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
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

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


    /*
     |--------------------------------------------------------------------------
     | OMSET OUTLET ACCESS MAPPING
     |--------------------------------------------------------------------------
     | Khusus Form Omset:
     | - superadmin / role non operasional tetap melihat semua outlet.
     | - crew mengikuti users.outlet_id + tbl_users_outlet jika ada.
     | - leader dan spv mengikuti outlet yang dimapping di tbl_users_outlet.
     | - outlet duplicate / alias tetap digabung seperti DSC Formulir.
     |
     | Tidak mengubah rumus, penyimpanan omset, OCR, foto, maupun route.
     */

    /*
     |--------------------------------------------------------------------------
     | PATCH ROLE OUTLET MAPPING DSC + OMSET
     |--------------------------------------------------------------------------
     | Satu sumber mapping outlet untuk halaman DSC Formulir dan DSC Formulir Omset.
     | Crew / Leader / SPV / TM Manager mengikuti users.outlet_id + tbl_users_outlet.
     | Role lain tetap melihat semua outlet.
     */
    private function isOperationalOutletRestrictedRole(?string $role): bool
    {
        $role = strtolower(trim((string) $role));

        if ($role === '') {
            return false;
        }

        $restrictedRoles = [
            'crew',
            'leader',
            'spv',
            'tm_manager',
            'tm manager',
            'tm-manager',
        ];

        if (in_array($role, $restrictedRoles, true)) {
            return true;
        }

        return str_contains($role, 'leader')
            || str_contains($role, 'spv');
    }

    private function getMappedOutletIdsForUser(?int $userId = null): array
    {
        $userId = $userId ?: auth()->id();

        $user = DB::table('users')
            ->select('id', 'role', 'outlet_id', 'name')
            ->where('id', $userId)
            ->first();

        if (!$user) {
            return [];
        }

        $assignedIds = collect();

        if ((int) ($user->outlet_id ?? 0) > 0) {
            $assignedIds->push((int) $user->outlet_id);
        }

        if (Schema::hasTable('tbl_users_outlet')) {
            $mappedIds = DB::table('tbl_users_outlet')
                ->where('user_id', $userId)
                ->pluck('outlet_id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0);

            $assignedIds = $assignedIds->concat($mappedIds);
        }

        $assignedIds = $assignedIds
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($assignedIds)) {
            return [];
        }

        return $this->expandOutletAliasIds($assignedIds);
    }

    private function getGroupedOutletsForOperationalUser(?int $userId = null): Collection
    {
        $userId = $userId ?: auth()->id();

        $user = DB::table('users')
            ->select('id', 'role', 'outlet_id', 'name')
            ->where('id', $userId)
            ->first();

        $role = (string) ($user->role ?? '');

        $outlets = $this->getGroupedOutletsForUser($userId);

        if (!$this->isOperationalOutletRestrictedRole($role)) {
            return $outlets;
        }

        $expandedAssignedIds = $this->getMappedOutletIdsForUser($userId);

        if (empty($expandedAssignedIds)) {
            abort(403, 'Akun belum disetting akses outlet.');
        }

        $assignedOutletRows = DB::table('tbl_outlets')
            ->select('id', 'nama_outlet')
            ->whereIn('id', $expandedAssignedIds)
            ->get();

        $assignedNames = $assignedOutletRows
            ->map(fn ($row) => $this->normalizeOutletName($row->nama_outlet ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $filtered = $outlets
            ->filter(function ($outlet) use ($expandedAssignedIds, $assignedNames) {
                $aliasIds = collect($outlet->alias_ids ?? [])
                    ->push((int) ($outlet->id ?? 0))
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->unique()
                    ->values()
                    ->all();

                if (!empty(array_intersect($aliasIds, $expandedAssignedIds))) {
                    return true;
                }

                $name = $this->normalizeOutletName($outlet->nama_outlet ?? '');
                return in_array($name, $assignedNames, true);
            })
            ->sortBy('nama_outlet', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        if ($filtered->isEmpty()) {
            abort(403, 'Outlet user tidak cocok dengan master outlet.');
        }

        return $filtered;
    }

    /**
     * PATCH SAFE - OUTLET_ID SELECT2 AJAX / GROUP ID
     *
     * Beberapa halaman DSC sekarang memakai Select2 AJAX yang mengirim value seperti:
     * - group_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
     * bukan angka outlet_id biasa. Endpoint lama memvalidasi outlet_id sebagai integer,
     * sehingga request load/save gagal dengan pesan: "The outlet id field must be an integer".
     *
     * Helper ini hanya menormalisasi value request menjadi outlet ID display yang valid,
     * lalu rumus, alias outlet, save draft/final, omset, dan load data tetap memakai
     * helper lama getOutletDisplayIdFromSelected() / getOutletAliasIdsFromSelected().
     */
    private function normalizeDscOutletRequest(Request $request, Collection $groupedOutlets): int
    {
        $raw = $request->input('outlet_id', $request->query('outlet_id', ''));
        $raw = trim((string) $raw);

        $selectedOutletId = 0;

        if ($raw !== '' && is_numeric($raw)) {
            $selectedOutletId = (int) $raw;
        }

        if ($selectedOutletId <= 0 && str_starts_with($raw, 'group_')) {
            foreach ($groupedOutlets as $outlet) {
                $name = $this->normalizeOutletName($outlet->nama_outlet ?? '');
                $groupKey = 'group_' . md5($name);

                if (hash_equals($groupKey, $raw)) {
                    $selectedOutletId = (int) ($outlet->id ?? 0);
                    break;
                }
            }
        }

        if ($selectedOutletId <= 0 && preg_match('/\d+/', $raw, $m)) {
            $selectedOutletId = (int) $m[0];
        }

        if ($selectedOutletId > 0) {
            $request->merge(['outlet_id' => $selectedOutletId]);
        }

        return $selectedOutletId;
    }

    private function getGroupedOutletsForOmsetUser(?int $userId = null): Collection
    {
        return $this->getGroupedOutletsForOperationalUser($userId);
    }

    private function userCanAccessOmsetOutlet(int $selectedOutletId, Collection $groupedOutlets): bool
    {
        if ($selectedOutletId <= 0) {
            return false;
        }

        foreach ($groupedOutlets as $outlet) {
            $ids = collect($outlet->alias_ids ?? [])
                ->push((int) ($outlet->id ?? 0))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            if (in_array($selectedOutletId, $ids, true)) {
                return true;
            }
        }

        return false;
    }


    public function dscFormulirOmset(Request $request)
    {
        $todayRaw = (string) $request->get('tanggal', date('Y-m-d'));
        $outletRaw = (int) $request->get('outlet_id', 0);
        $shiftRaw = (string) $request->get('shift', '1');
        $today = $this->normalizeDateToYmd($todayRaw);
    
        $shift = in_array($shiftRaw, ['1', '2'], true) ? $shiftRaw : '1';
    
        $userId = auth()->id();

        // Form Omset mengikuti mapping outlet operasional seperti DSC Formulir.
        // Crew: users.outlet_id + tbl_users_outlet.
        // Leader/SPV: tbl_users_outlet.
        // Role lain: tetap semua outlet.
        $outlets = $this->getGroupedOutletsForOmsetUser($userId);

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
        $groupedOutlets = $this->getGroupedOutletsForOmsetUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

        $r->validate([
            'outlet_id' => 'required|integer|min:1',
            'tanggal'   => 'required',
        ]);
    
        $selectedOutletId = (int) $r->input('outlet_id');
        $today = $this->normalizeDateToYmd((string) $r->input('tanggal'));
    
        if (!$this->userCanAccessOmsetOutlet($selectedOutletId, $groupedOutlets)) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda tidak memiliki akses ke outlet ini.',
            ], 403);
        }

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
        $groupedOutlets = $this->getGroupedOutletsForOmsetUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

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

        if (!$this->userCanAccessOmsetOutlet($selectedOutletId, $groupedOutlets)) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda tidak memiliki akses ke outlet ini.',
            ], 403);
        }

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

        $oldPhotoPath = $existing ? ($existing->{$photoColumn} ?? null) : null;
        $newPhotoPath = $oldPhotoPath;

        $source = (string) ($existing ? ($existing->{$sourceColumn} ?? '') : '');
        $hash = (string) ($existing ? ($existing->{$hashColumn} ?? '') : '');
        $reviewStatus = (string) ($existing ? ($existing->{$reviewStatusColumn} ?? '') : '');
        $reviewReason = (string) ($existing ? ($existing->{$reviewReasonColumn} ?? '') : '');

        $ocrText = (string) ($existing ? ($existing->{$ocrTextColumn} ?? '') : '');
        $ocrNominal = $existing ? ($existing->{$ocrNominalColumn} ?? null) : null;
        $ocrTanggal = $existing ? ($existing->{$ocrTanggalColumn} ?? null) : null;
        $ocrReference = $existing ? ($existing->{$ocrReferenceColumn} ?? null) : null;
        $fraudScore = (int) ($existing ? ($existing->{$fraudScoreColumn} ?? 0) : 0);
        $fraudFlags = (string) ($existing ? ($existing->{$fraudFlagsColumn} ?? '') : '');

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

        /*
         * FIX DUPLICATE KEY uniq_outlet_tanggal
         *
         * Masalah sebelumnya:
         * updateOrInsert() melakukan SELECT lalu INSERT/UPDATE secara terpisah.
         * Kalau tombol simpan terkirim 2x hampir bersamaan, dua request bisa sama-sama
         * belum menemukan row, lalu sama-sama mencoba INSERT. MySQL kemudian menolak
         * request kedua karena unique key outlet_id + tanggal sudah ada.
         *
         * Perbaikan:
         * Pakai UPSERT native database sehingga INSERT/UPDATE terjadi atomik di level MySQL.
         * Logika data lain tetap sama: field yang disimpan, validasi, OCR, foto, dan response
         * tidak diubah. created_at hanya diisi saat insert, sedangkan update berikutnya tidak
         * mengubah created_at.
         */
        $upsertUpdate = $update;
        unset($upsertUpdate['created_at']);

        $upsertInsert = array_merge(
            [
                'outlet_id' => $displayOutletId,
                'tanggal' => $tanggal,
                'created_at' => now(),
            ],
            $upsertUpdate
        );

        DB::table('tbl_dsc_omset_setoran')->upsert(
            [$upsertInsert],
            ['outlet_id', 'tanggal'],
            array_keys($upsertUpdate)
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
        $groupedOutlets = $this->getGroupedOutletsForOmsetUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

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
    
        $selectedOutletId = (int) $r->outlet_id;
        $tanggal  = (string) $r->tanggal;
        $pic      = trim((string) $r->pic);

        if (!$this->userCanAccessOmsetOutlet($selectedOutletId, $groupedOutlets)) {
            return response()->json([
                'ok' => false,
                'message' => 'Anda tidak memiliki akses ke outlet ini.',
            ], 403);
        }

        $outletId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);

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
     * FINAL FIX OUTLET ALIAS
     * Menggabungkan outlet typo/duplicate yang dicatat di tbl_outlet_alias.
     * Aman: jika tabel belum ada, sistem tetap berjalan memakai logic lama.
     */
    private function expandOutletAliasIds(array $outletIds): array
    {
        $ids = collect($outletIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids) || ! Schema::hasTable('tbl_outlet_alias')) {
            return $ids;
        }

        // Resolve berulang supaya jika alias menunjuk canonical lain tetap ikut terbaca.
        for ($i = 0; $i < 5; $i++) {
            $rows = DB::table('tbl_outlet_alias')
                ->select('canonical_outlet_id', 'alias_outlet_id')
                ->where(function ($q) use ($ids) {
                    $q->whereIn('canonical_outlet_id', $ids)
                      ->orWhereIn('alias_outlet_id', $ids);
                })
                ->get();

            $merged = collect($ids);
            foreach ($rows as $row) {
                $merged->push((int) $row->canonical_outlet_id);
                $merged->push((int) $row->alias_outlet_id);
            }

            $newIds = $merged->filter(fn ($id) => $id > 0)->unique()->values()->all();
            sort($newIds);

            if ($newIds === $ids) {
                break;
            }

            $ids = $newIds;
        }

        return $ids;
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
                return $this->expandOutletAliasIds($outlet->alias_ids);
            }
        }
    
        return $this->expandOutletAliasIds([$selectedOutletId]);
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

        /*
        |--------------------------------------------------------------------------
        | Outlet mengikuti mapping user operasional
        |--------------------------------------------------------------------------
        | Crew / Leader / SPV / TM Manager:
        | - users.outlet_id
        | - tbl_users_outlet
        |
        | Role lain:
        | - semua outlet
        |
        | Outlet duplicate / alias tetap digabung seperti logic lama.
        |--------------------------------------------------------------------------
        */
        $outlets = $this->getGroupedOutletsForOperationalUser($userId);

        if ($outletIdRaw <= 0) {
            $outletId = $outlets->count()
                ? (int) $outlets->first()->id
                : 0;
        } else {
            $outletId = $this->getOutletDisplayIdFromSelected(
                $outletIdRaw,
                $outlets
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi keamanan outlet
        |--------------------------------------------------------------------------
        | Tidak boleh inject outlet_id di URL yang tidak ada di mapping user.
        |--------------------------------------------------------------------------
        */
        $validIds = $outlets
            ->flatMap(function ($outlet) {
                return collect($outlet->alias_ids ?? [])
                    ->push((int) ($outlet->id ?? 0));
            })
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
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
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

        $outletId = (int) $r->input('outlet_id');
        $tanggal = (string) $r->query('tanggal');

        if (! $outletId || ! $tanggal) {
            return response()->json([
                'ok' => false,
                'message' => 'OUTLET_ID / TANGGAL WAJIB'
            ], 422);
        }

        $displayOutletId = $this->getOutletDisplayIdFromSelected($outletId, $groupedOutlets);

        $st = $this->getCloseStatus($displayOutletId, $tanggal);

        return response()->json([
            'ok' => true,
            'data' => $st
        ]);
    }

    public function closeKasir(Request $r)
    {
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

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


    /*
     |--------------------------------------------------------------------------
     | PATCH REQUEST TIM - SHIFT 2 TIDAK MEMBAWA PURCHASE/MUTASI SHIFT 1
     |--------------------------------------------------------------------------
     | Scope sengaja kecil:
     | - Tidak mengubah rumus TOTAL / USED / ENDING.
     | - Hanya membersihkan input bawaan/carry over saat Shift 2 baru dibuat.
     | - Kalau Shift 2 sudah pernah punya draft/final, nilai existing tetap boleh diedit normal.
     */
    private function cleanShift2CarryOverPurchaseMutasi(
        int $outletId,
        string $tanggal,
        array $rows,
        string $targetTable
    ): array {
        if (empty($rows)) {
            return $rows;
        }

        /*
         * FIX SHIFT 2 DOUBLE PURCHASE / MUTASI
         *
         * Inti masalah:
         * Purchase In, Mutasi In, dan Mutasi Out yang sudah dimasukkan di Shift 1
         * tidak boleh ikut lagi di Shift 2. Nilai tersebut sudah membentuk ending Shift 1
         * dan menjadi opening Shift 2, jadi kalau ikut lagi akan double dan varian meledak.
         *
         * Scope sengaja kecil:
         * - Tidak mengubah rumus TOTAL / ENDING / USED.
         * - Tidak mengubah opening stock.
         * - Tidak mengubah waste, adjustment, uang plus, dan keterangan.
         * - Hanya menolkan purchase/mutasi Shift 2 jika nilainya sama persis dengan Shift 1.
         */
        $aliasIds = [];

        try {
            $aliasIds = $this->expandOutletAliasIds([(int) $outletId]);
        } catch (\Throwable $e) {
            $aliasIds = [];
        }

        $aliasIds = array_values(array_unique(array_map('intval', $aliasIds ?: [(int) $outletId])));
        $aliasIds = array_values(array_filter($aliasIds, fn ($id) => $id > 0));

        if (empty($aliasIds)) {
            $aliasIds = [(int) $outletId];
        }

        $draftS1 = DB::table('tbl_stock_draft')
            ->whereIn('outlet_id', $aliasIds)
            ->whereDate('tanggal', $tanggal)
            ->where('shift', 1)
            ->where('is_draft', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->unique('bahan_id')
            ->keyBy('bahan_id');

        $finalS1 = DB::table('tbl_stock')
            ->whereIn('outlet_id', $aliasIds)
            ->whereDate('tanggal', $tanggal)
            ->where('shift', '1')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->unique('bahan_id')
            ->keyBy('bahan_id');

        $sameNumber = function ($a, $b): bool {
            return abs(((float) $a) - ((float) $b)) < 0.00001;
        };

        foreach ($rows as $idx => $row) {
            $bahanId = (int) ($row['bahan_id'] ?? 0);
            if ($bahanId <= 0) {
                continue;
            }

            // Draft Shift 1 tetap prioritas karena flow crew: S1 draft -> lanjut Shift 2.
            $s1 = $draftS1[$bahanId] ?? $finalS1[$bahanId] ?? null;
            if (! $s1) {
                continue;
            }

            $purchaseIn = (float) ($row['purchase_in'] ?? 0);
            $mutasiIn   = (float) ($row['mutasi_in'] ?? 0);
            $mutasiOut  = (float) ($row['mutasi_out'] ?? 0);

            $samePurchase = $purchaseIn != 0.0 && $sameNumber($purchaseIn, $s1->purchase_in ?? 0);
            $sameMutIn    = $mutasiIn != 0.0 && $sameNumber($mutasiIn, $s1->mutasi_in ?? 0);
            $sameMutOut   = $mutasiOut != 0.0 && $sameNumber($mutasiOut, $s1->mutasi_out ?? 0);

            if ($samePurchase) {
                $rows[$idx]['purchase_in'] = 0;
            }
            if ($sameMutIn) {
                $rows[$idx]['mutasi_in'] = 0;
            }
            if ($sameMutOut) {
                $rows[$idx]['mutasi_out'] = 0;
            }
        }

        return $rows;
    }

    public function dscLoad(Request $r)
    {
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

        $r->validate([
            'outlet_id' => 'required|integer',
            'tanggal'   => 'required',
            'shift'     => 'required|in:1,2',
        ]);

        $selectedOutletId = (int) $r->input('outlet_id');
        $todayRaw = (string) $r->query('tanggal');
        $today = $this->normTanggal($todayRaw);
        $shift = (int) $r->query('shift');

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

            $purchaseIn = (float) ($sr->purchase_in ?? 0);
            $mutasiIn = (float) ($sr->mutasi_in ?? 0);
            $mutasiOut = (float) ($sr->mutasi_out ?? 0);
            $carryOverCleaned = false;

            if ($shift === 2) {
                $s1 = $draftShift1Rows[$b->id] ?? $finalShift1Rows[$b->id] ?? null;
                $sameNumber = function ($a, $b): bool {
                    return abs(((float) $a) - ((float) $b)) < 0.00001;
                };

                if ($s1) {
                    if ($purchaseIn != 0.0 && $sameNumber($purchaseIn, $s1->purchase_in ?? 0)) {
                        $purchaseIn = 0.0;
                        $carryOverCleaned = true;
                    }
                    if ($mutasiIn != 0.0 && $sameNumber($mutasiIn, $s1->mutasi_in ?? 0)) {
                        $mutasiIn = 0.0;
                        $carryOverCleaned = true;
                    }
                    if ($mutasiOut != 0.0 && $sameNumber($mutasiOut, $s1->mutasi_out ?? 0)) {
                        $mutasiOut = 0.0;
                        $carryOverCleaned = true;
                    }
                }
            }

            $items[] = [
                'id'            => (int) $b->id,
                'nama_bahan'    => (string) $b->nama_bahan,
                'satuan'        => (string) $b->satuan,
                'open'          => (float) $open,

                'purchase_in'   => $purchaseIn,
                'mutasi_in'     => $mutasiIn,
                'mutasi_out'    => $mutasiOut,
                'adjustment_qty'=> (float) ($sr->adjustment_qty ?? 0),
                'ending_stock'  => $sr && $sr->ending_stock !== null ? (float) $sr->ending_stock : null,
                'waste_product' => (float) ($sr->waste_product ?? 0),
                'waste_bahan'   => (float) ($sr->waste_bahan ?? 0),
                'uang_plus'     => (float) ($sr->uang_plus ?? 0),
                'keterangan'    => (string) ($sr->keterangan ?? ''),
                'carryover_cleaned' => $carryOverCleaned ? 1 : 0,
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
                    'can_spv_adjust'  => $this->canCurrentUserSpvAdjustment() ? 1 : 0,
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


    /*
     |--------------------------------------------------------------------------
     | PATCH SPV ADJUSTMENT AFTER FINAL
     |--------------------------------------------------------------------------
     | SPV boleh koreksi adjustment pada data yang sudah FINAL/LOCK.
     | Status tetap FINAL, tidak membuka lock, tidak menyentuh draft, dan tidak
     | mengubah purchase/mutasi/ending/waste/uang_plus/keterangan.
     */
    private function canCurrentUserSpvAdjustment(): bool
    {
        $role = strtolower(trim((string) (auth()->user()->role ?? '')));

        $allowedRoles = [
            'spv',
            'superadmin',
            'tm_manager',
            'tm manager',
            'tm-manager',
        ];

        return in_array($role, $allowedRoles, true)
            || str_contains($role, 'spv')
            || str_contains($role, 'tm_manager')
            || str_contains($role, 'tm manager')
            || str_contains($role, 'tm-manager');
    }

public function dscSaveSpvAdjustment(Request $request)
{
    if (! $this->canCurrentUserSpvAdjustment()) {
        return response()->json([
            'ok' => false,
            'message' => 'Akses ditolak. Hanya SPV, TM Manager, atau Superadmin yang boleh simpan koreksi setelah final.',
        ], 403);
    }

    $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
    $this->normalizeDscOutletRequest($request, $groupedOutlets);

    $request->validate([
        'outlet_id' => 'required|integer',
        'tanggal' => 'required',
        'shift' => 'required|in:1,2',
        'nama_petugas' => 'nullable|string|max:255',
        'rows' => 'required|array|min:1',
        'rows.*.bahan_id' => 'required|integer|exists:tbl_bahan_dsc,id',
        'rows.*.purchase_in' => 'nullable|numeric',
        'rows.*.mutasi_in' => 'nullable|numeric',
        'rows.*.mutasi_out' => 'nullable|numeric',
        'rows.*.adjustment_qty' => 'nullable|numeric',
        'rows.*.ending_stock' => 'nullable|numeric',
        'rows.*.waste_product' => 'nullable|numeric',
        'rows.*.waste_bahan' => 'nullable|numeric',
        'rows.*.uang_plus' => 'nullable|numeric',
    ]);

    $selectedOutletId = (int) $request->outlet_id;
    $tanggal = $this->normTanggal((string) $request->tanggal);
    $shift = (string) $request->shift;
    $pic = trim((string) ($request->nama_petugas ?: (auth()->user()->name ?? auth()->user()->email ?? 'SPV')));

    $displayOutletId = (int) $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);
    $aliasIds = $this->getOutletAliasIdsFromSelected($selectedOutletId, $groupedOutlets);

    $aliasIds = collect($aliasIds)
        ->push($selectedOutletId)
        ->push($displayOutletId)
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

    // Ini yang penting: draft cleanup harus mencakup semua kemungkinan outlet.
    $cleanupOutletIds = $aliasIds;

    $lock = $this->getCloseStatus($displayOutletId, $tanggal);
    if (empty($lock['is_closed'])) {
        return response()->json([
            'ok' => false,
            'message' => 'Koreksi SPV hanya boleh dilakukan setelah data berstatus FINAL/LOCK.',
        ], 422);
    }

    $allowedFields = [
        'purchase_in',
        'mutasi_in',
        'mutasi_out',
        'adjustment_qty',
        'ending_stock',
        'waste_product',
        'waste_bahan',
        'uang_plus',
    ];

    $rows = collect($request->rows)
        ->map(function ($row) use ($allowedFields) {
            $payload = [
                'bahan_id' => (int) ($row['bahan_id'] ?? 0),
            ];

            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $row)) {
                    $payload[$field] = (float) ($row[$field] ?? 0);
                }
            }

            return $payload;
        })
        ->filter(fn ($row) => (int) ($row['bahan_id'] ?? 0) > 0)
        ->unique('bahan_id')
        ->values();

    if ($rows->isEmpty()) {
        return response()->json([
            'ok' => false,
            'message' => 'Tidak ada data koreksi yang dikirim.',
        ], 422);
    }

    DB::beginTransaction();

    try {
        $updated = 0;
        $created = 0;
        $notFound = [];

        foreach ($rows as $row) {
            $bahanId = (int) $row['bahan_id'];

            $existing = DB::table('tbl_stock')
                ->where('outlet_id', $displayOutletId)
                ->whereDate('tanggal', $tanggal)
                ->where('shift', $shift)
                ->where('bahan_id', $bahanId)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (! $existing) {
                $existing = DB::table('tbl_stock')
                    ->whereIn('outlet_id', $aliasIds)
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', $shift)
                    ->where('bahan_id', $bahanId)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();
            }

            $bahan = DB::table('tbl_bahan_dsc')
                ->select('id', 'nama_bahan', 'satuan')
                ->where('id', $bahanId)
                ->first();

            if (! $bahan) {
                $notFound[] = $bahanId;
                continue;
            }

            if (! $existing) {
                $purchaseIn = array_key_exists('purchase_in', $row) ? (float) $row['purchase_in'] : 0.0;
                $mutasiIn = array_key_exists('mutasi_in', $row) ? (float) $row['mutasi_in'] : 0.0;
                $mutasiOut = array_key_exists('mutasi_out', $row) ? (float) $row['mutasi_out'] : 0.0;
                $adjustmentQty = array_key_exists('adjustment_qty', $row) ? (float) $row['adjustment_qty'] : 0.0;
                $ending = array_key_exists('ending_stock', $row) ? (float) $row['ending_stock'] : 0.0;
                $wProd = array_key_exists('waste_product', $row) ? (float) $row['waste_product'] : 0.0;
                $wBahan = array_key_exists('waste_bahan', $row) ? (float) $row['waste_bahan'] : 0.0;
                $uangPlus = array_key_exists('uang_plus', $row) ? (float) $row['uang_plus'] : 0.0;
                $wTepung = 0.0;

                $opening = (float) $this->getOpeningStock($displayOutletId, $bahanId, $tanggal, (int) $shift);

                // Ikut rumus form existing di function lama.
                $total = $opening + $purchaseIn + $mutasiIn - $mutasiOut + $adjustmentQty;
                $used = $total - $ending;
                $wasteTotal = $wProd + $wBahan + $wTepung;

                $namaNorm = strtolower(trim((string) ($bahan->nama_bahan ?? '')));
                $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wasteTotal) : 0.0;

                $payload = [
                    'outlet_id' => $displayOutletId,
                    'tanggal' => $tanggal,
                    'shift' => $shift,
                    'bahan_id' => $bahanId,
                    'satuan' => (string) ($bahan->satuan ?? ''),
                    'opening_stock' => $opening,
                    'purchase_in' => $purchaseIn,
                    'mutasi_in' => $mutasiIn,
                    'mutasi_out' => $mutasiOut,
                    'adjustment_qty' => $adjustmentQty,
                    'ending_stock' => $ending,
                    'waste_product' => $wProd,
                    'waste_bahan' => $wBahan,
                    'waste_tepung' => $wTepung,
                    'uang_plus' => $uangPlus,
                    'used_qty' => $used,
                    'actual_tepung' => $actualTepung,
                    'nama_petugas' => $pic,
                    'keterangan' => 'KOREKSI FINAL - ROW DIBUAT OTOMATIS',
                    'customer_unit' => 0,
                    'shift_1' => 0,
                    'shift_2' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $this->auditDscStockChanges(
                    'tbl_stock',
                    'SPV_CORRECTION_AFTER_FINAL_CREATE',
                    $displayOutletId,
                    $tanggal,
                    (int) $shift,
                    $bahanId,
                    null,
                    $payload,
                    $pic,
                    'final'
                );

                DB::table('tbl_stock')->insert($payload);

                $created++;
            } else {
                $purchaseIn = array_key_exists('purchase_in', $row) ? (float) $row['purchase_in'] : (float) ($existing->purchase_in ?? 0);
                $mutasiIn = array_key_exists('mutasi_in', $row) ? (float) $row['mutasi_in'] : (float) ($existing->mutasi_in ?? 0);
                $mutasiOut = array_key_exists('mutasi_out', $row) ? (float) $row['mutasi_out'] : (float) ($existing->mutasi_out ?? 0);
                $adjustmentQty = array_key_exists('adjustment_qty', $row) ? (float) $row['adjustment_qty'] : (float) ($existing->adjustment_qty ?? 0);
                $ending = array_key_exists('ending_stock', $row) ? (float) $row['ending_stock'] : (float) ($existing->ending_stock ?? 0);
                $wProd = array_key_exists('waste_product', $row) ? (float) $row['waste_product'] : (float) ($existing->waste_product ?? 0);
                $wBahan = array_key_exists('waste_bahan', $row) ? (float) $row['waste_bahan'] : (float) ($existing->waste_bahan ?? 0);
                $uangPlus = array_key_exists('uang_plus', $row) ? (float) $row['uang_plus'] : (float) ($existing->uang_plus ?? 0);
                $wTepung = (float) ($existing->waste_tepung ?? 0);

                $opening = (float) ($existing->opening_stock ?? 0);
                $total = $opening + $purchaseIn + $mutasiIn - $mutasiOut + $adjustmentQty;
                $used = $total - $ending;
                $wasteTotal = $wProd + $wBahan + $wTepung;

                $namaNorm = strtolower(trim((string) ($bahan->nama_bahan ?? '')));
                $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wasteTotal) : 0.0;

                $payload = [
                    'purchase_in' => $purchaseIn,
                    'mutasi_in' => $mutasiIn,
                    'mutasi_out' => $mutasiOut,
                    'adjustment_qty' => $adjustmentQty,
                    'ending_stock' => $ending,
                    'waste_product' => $wProd,
                    'waste_bahan' => $wBahan,
                    'uang_plus' => $uangPlus,
                    'used_qty' => $used,
                    'actual_tepung' => $actualTepung,
                    'nama_petugas' => $pic,
                    'updated_at' => now(),
                ];

                $hasChange = false;
                foreach ($payload as $field => $newValue) {
                    if ($field === 'updated_at') {
                        continue;
                    }

                    if ($field === 'nama_petugas') {
                        if ((string) ($existing->{$field} ?? '') !== (string) $newValue) {
                            $hasChange = true;
                            break;
                        }
                        continue;
                    }

                    $oldValue = (float) ($existing->{$field} ?? 0);
                    if (abs($oldValue - (float) $newValue) >= 0.00001) {
                        $hasChange = true;
                        break;
                    }
                }

                if ($hasChange) {
                    $this->auditDscStockChanges(
                        'tbl_stock',
                        'SPV_CORRECTION_AFTER_FINAL',
                        (int) $existing->outlet_id,
                        $tanggal,
                        (int) $shift,
                        $bahanId,
                        $existing,
                        $payload,
                        $pic,
                        'final'
                    );

                    DB::table('tbl_stock')
                        ->where('id', $existing->id)
                        ->update($payload);

                    $updated++;
                }
            }

            // Wajib tetap jalan walau tidak ada perubahan angka.
            // Kalau tidak, draft aktif lama tetap menimpa final saat reload.
            DB::table('tbl_stock_draft')
                ->whereIn('outlet_id', $cleanupOutletIds)
                ->whereDate('tanggal', $tanggal)
                ->where('shift', $shift)
                ->where('bahan_id', $bahanId)
                ->where('is_draft', 1)
                ->update([
                    'is_draft' => 0,
                    'updated_at' => now(),
                ]);
        }

        DB::commit();

        return response()->json([
            'ok' => true,
            'message' => 'Koreksi SPV berhasil disimpan. Status tetap FINAL/LOCK.',
            'data' => [
                'updated' => $updated,
                'created' => $created,
                'not_found' => $notFound,
                'cleanup_outlet_ids' => $cleanupOutletIds,
                'lock' => $this->getCloseStatus($displayOutletId, $tanggal),
            ],
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'ok' => false,
            'message' => 'Gagal simpan koreksi SPV.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function dscSaveSO(Request $request)
    {
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($request, $groupedOutlets);

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

        $rowsToSave = $request->rows;

        // PATCH REQUEST TIM:
        // Shift 2 tidak boleh otomatis membawa ulang Purchase/Mutasi dari Shift 1.
        // Hanya berlaku untuk Shift 2 yang belum punya data final sendiri.
        if ((int) $shift === 2) {
            $rowsToSave = $this->cleanShift2CarryOverPurchaseMutasi(
                $displayOutletId,
                $tanggal,
                $rowsToSave,
                'tbl_stock'
            );
        }

        DB::beginTransaction();
        try {
            /*
             * FINAL SHIFT 2:
             * 1) Draft Shift 1 yang sudah dipakai untuk opening Shift 2 ikut dijadikan FINAL.
             * 2) Shift 2 disimpan dari payload terbaru yang sedang user lihat/klik.
             * 3) Semua draft tanggal ini dibersihkan.
             * 4) Tanggal outlet dikunci agar tidak bisa diedit ulang.
             */
            if ((int) $shift === 2) {
                $this->promoteDscDraftShiftToFinalPreserveDraft(
                    $displayOutletId,
                    $tanggal,
                    1,
                    $pic
                );
            }

            // Simpan final shift aktif dari payload terbaru.
            $this->upsertFinalStockRows(
                $displayOutletId,
                $tanggal,
                $shift,
                $pic,
                $rowsToSave,
                0,
                0,
                0
            );

            // Setelah final Shift 2 berhasil, hapus semua draft tanggal ini dan kunci tanggal.
            if ((int) $shift === 2) {
                // SAFE FIX:
                // Jangan hapus semua draft Shift 1 & 2 setelah final Shift 2.
                // Ini menyebabkan koreksi hilang dan modal pulihkan muncul terus karena local/draft tidak sinkron.
                // Cukup nonaktifkan draft shift yang sedang difinalkan saja.
                DB::table('tbl_stock_draft')
                    ->where('outlet_id', $displayOutletId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', (int) $shift)
                    ->where('is_draft', 1)
                    ->update([
                        'is_draft' => 0,
                        'updated_at' => now(),
                    ]);

                $this->setKasirClosed(
                    $displayOutletId,
                    $tanggal,
                    2,
                    $pic,
                    'FINAL DSC SHIFT 2'
                );
            } else {
                $bahanIds = collect($rowsToSave)
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
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => ((int) $shift === 2)
                    ? 'FINAL TERSIMPAN. DRAFT SHIFT 1 DAN SHIFT 2 SUDAH DIJADIKAN FINAL. DATA TANGGAL INI TERKUNCI.'
                    : 'SO TERSIMPAN',
                'data' => [
                    'lock' => ((int) $shift === 2)
                        ? $this->getCloseStatus($displayOutletId, $tanggal)
                        : null,
                ],
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


    /*
     |--------------------------------------------------------------------------
     | PATCH FINAL SHIFT 2 - PROMOTE DRAFT SHIFT 1 KE FINAL
     |--------------------------------------------------------------------------
     | Saat user klik Simpan Final di Shift 2:
     | - Shift 2 yang sedang aktif disimpan final dari payload terbaru.
     | - Draft Shift 1 yang sudah terhitung ikut dipromote ke tbl_stock.
     | - Draft dibersihkan supaya tidak muncul lagi sebagai draft.
     | - Rumus/payload draft dipertahankan, tidak mengubah logic hitung lain.
     */
    private function promoteDscDraftShiftToFinalPreserveDraft(
        int $outletId,
        string $tanggal,
        int $shift,
        string $pic
    ): void {
        $draftRows = DB::table('tbl_stock_draft')
            ->where('outlet_id', $outletId)
            ->whereDate('tanggal', $tanggal)
            ->where('shift', $shift)
            ->where('is_draft', 1)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->unique('bahan_id')
            ->values();

        if ($draftRows->isEmpty()) {
            return;
        }

        foreach ($draftRows as $draft) {
            $bahanId = (int) ($draft->bahan_id ?? 0);
            if ($bahanId <= 0) {
                continue;
            }

            $existing = DB::table('tbl_stock')
                ->where('outlet_id', $outletId)
                ->whereDate('tanggal', $tanggal)
                ->where('shift', (string) $shift)
                ->where('bahan_id', $bahanId)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $payload = [
                'nama_petugas'   => $pic,
                'satuan'         => (string) ($draft->satuan ?? ''),
                'opening_stock'  => (float) ($draft->opening_stock ?? 0),

                'purchase_in'    => (float) ($draft->purchase_in ?? 0),
                'mutasi_in'      => (float) ($draft->mutasi_in ?? 0),
                'mutasi_out'     => (float) ($draft->mutasi_out ?? 0),
                'adjustment_qty' => (float) ($draft->adjustment_qty ?? 0),

                'used_qty'       => (float) ($draft->used_qty ?? 0),
                'waste_product'  => (float) ($draft->waste_product ?? 0),
                'waste_bahan'    => (float) ($draft->waste_bahan ?? 0),
                'waste_tepung'   => (float) ($draft->waste_tepung ?? 0),

                'ending_stock'   => (float) ($draft->ending_stock ?? 0),
                'actual_tepung'  => (float) ($draft->actual_tepung ?? 0),
                'uang_plus'      => (float) ($draft->uang_plus ?? 0),

                'customer_unit'  => (float) ($draft->customer_unit ?? 0),
                'shift_1'        => (float) ($draft->shift_1 ?? 0),
                'shift_2'        => (float) ($draft->shift_2 ?? 0),
                'keterangan'     => (string) ($draft->keterangan ?? ''),
                'updated_at'     => now(),
            ];

            $this->auditDscStockChanges(
                'tbl_stock',
                $existing ? 'FINAL_UPDATE_FROM_DRAFT' : 'FINAL_CREATE_FROM_DRAFT',
                $outletId,
                $tanggal,
                $shift,
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
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', (string) $shift)
                    ->where('bahan_id', $bahanId)
                    ->where('id', '!=', $existing->id)
                    ->delete();
            } else {
                DB::table('tbl_stock')->insert(array_merge([
                    'outlet_id' => $outletId,
                    'tanggal'   => $tanggal,
                    'shift'     => (string) $shift,
                    'bahan_id'  => $bahanId,
                    'created_at'=> now(),
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
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($request, $groupedOutlets);

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

        $displayOutletId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);

        $close = $this->getCloseStatus($displayOutletId, $tanggal);

        if ($close['is_closed']) {
            return response()->json([
                'ok' => false,
                'message' => 'KASIR SUDAH DITUTUP. DATA TERKUNCI.'
            ], 423);
        }

        $rowsToSave = $request->rows;

        // PATCH REQUEST TIM:
        // Saat Save Draft Shift 2, bersihkan nilai Purchase/Mutasi yang terbawa dari Shift 1
        // jika Shift 2 belum punya draft sendiri. Rumus stok tidak diubah.
        if ((int) $shift === 2) {
            $rowsToSave = $this->cleanShift2CarryOverPurchaseMutasi(
                $displayOutletId,
                $tanggal,
                $rowsToSave,
                'tbl_stock_draft'
            );
        }

        DB::beginTransaction();

        try {
            $bahanIds = collect($rowsToSave)
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

            foreach ($rowsToSave as $r) {
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

        if (! Schema::hasTable('tbl_dsc_stock_history')) {
            return response()->json([
                'ok' => true,
                'data' => [],
                'message' => 'History DSC sementara nonaktif karena tabel history belum tersedia.',
                'meta' => [
                    'count' => 0,
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

        if (empty($rows)) {
            return;
        }

        // HISTORY SAFE GUARD:
        // Jangan sampai simpan Draft/Final gagal hanya karena tabel audit history belum tersedia.
        // Fitur history boleh nonaktif sementara, tetapi data utama tbl_stock_draft/tbl_stock harus tetap aman tersimpan.
        try {
            if (! Schema::hasTable('tbl_dsc_stock_history')) {
                return;
            }

            DB::table('tbl_dsc_stock_history')->insert($rows);
        } catch (\Throwable $e) {
            \Log::warning('DSC stock history skipped', [
                'message' => $e->getMessage(),
                'table' => 'tbl_dsc_stock_history',
                'action' => $action,
                'outlet_id' => $outletId,
                'tanggal' => $tanggal,
                'shift' => $shift,
                'bahan_id' => $bahanId,
            ]);
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
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($r, $groupedOutlets);

        $r->validate([
            'outlet_id' => 'required|integer',
            'tanggal'   => 'required|date_format:Y-m-d',
        ]);
    
        $selectedOutletId = (int) $r->input('outlet_id');
        $tanggal = (string) $r->query('tanggal');
    
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
        static $openingRequestCache = [];
    
        $cacheKey = $outletId . '|' . $bahanId . '|' . $tanggal . '|' . $shift;
    
        if (array_key_exists($cacheKey, $openingRequestCache)) {
            return (float) $openingRequestCache[$cacheKey];
        }
    
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $aliasIds = $this->getOutletAliasIdsFromSelected($outletId, $groupedOutlets);
    
        $openingRequestCache[$cacheKey] = (float) $this->getOpeningFromAliasIds(
            $aliasIds,
            $outletId,
            $bahanId,
            $tanggal,
            $shift
        );
    
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
         * PATCH DSC REPORT / LOAD:
         * Tampilan DSC aktif memakai pola draft terbaru > final terbaru.
         * Opening hari berikutnya harus mengikuti data yang tampil di DSC.
         * Jadi jika masih ada draft shift sebelumnya, ending draft itulah
         * yang menjadi open, bukan final lama.
         */
        $draft = $baseDraft
            ->whereNotNull('ending_stock')
            ->orderByDesc('shift')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($draft && $draft->ending_stock !== null) {
            return (float) $draft->ending_stock;
        }

        $final = $baseFinal
            ->whereNotNull('ending_stock')
            ->orderByDesc('shift')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->first();

        if ($final && $final->ending_stock !== null) {
            return (float) $final->ending_stock;
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
            'waste_tepung' => 'nullable|numeric',
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
        $wTepung = (float) ($request->waste_tepung ?? 0);
        $wasteTotal = $wProd + $wBahan + $wTepung;

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
        $actualTepung = ($namaNorm === 'tepung breader') ? ($used - $wasteTotal) : 0.0;

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

private function getDscMissingOutletRows(string $startDate, string $endDate, string $keyword = ''): array
{
    $normalize = function ($value) {
        $value = strtoupper(trim((string) $value));
        return preg_replace('/\s+/', ' ', $value) ?: '-';
    };

    $isExcludedOutlet = function ($namaOutlet, $status) use ($normalize): bool {
        $nama = $normalize($namaOutlet);
        $status = $normalize($status);

        // FINAL FILTER NAMA/STATUS OUTLET:
        // Jangan tampilkan outlet yang memang marker-nya bukan outlet aktif operasional.
        // Dibuat tahan format: NOT ACTIVE, NOT-ACTIVE, NOT_ACTIVE, NOT.ACTIVE,
        // NO ACTIVE, NON ACTIVE, NO AKTIF, ACTIVED, TUTUP, CLOSED, dan DC.
        $toCompactMarker = function ($value): string {
            $value = strtoupper(trim((string) $value));
            return preg_replace('/[^A-Z0-9]+/', '', $value) ?: '';
        };

        $statusCompact = $toCompactMarker($status);
        $namaCompact = $toCompactMarker($nama);

        $blockedCompacts = [
            'DC',
            'INACTIVE',
            'NONAKTIF',
            'NONACTIVE',
            'NOACTIVE',
            'NOAKTIF',
            'NOTACTIVE',
            'NOTACTIVED',
            'ACTIVED',
            'TUTUP',
            'CLOSED',
        ];

        if (in_array($statusCompact, $blockedCompacts, true)) {
            return true;
        }

        foreach ($blockedCompacts as $marker) {
            if ($marker === 'DC') {
                continue;
            }

            if (str_contains($namaCompact, $marker)) {
                return true;
            }
        }

        // DC hanya diblok kalau benar-benar marker DC, bukan sekadar huruf DC di tengah kata.
        if (
            $namaCompact === 'DC' ||
            str_starts_with($namaCompact, 'DC') ||
            str_contains($nama, ' DISTRIBUTION CENTER')
        ) {
            return true;
        }

        return false;
    };

    $startDate = $this->normalizeDateToYmd($startDate);
    $endDate = $this->normalizeDateToYmd($endDate);
    if ($startDate > $endDate) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }

    $outlets = DB::table('tbl_outlets')
        ->select('id', 'nama_outlet', 'status')
        ->when($keyword !== '', function ($q) use ($keyword) {
            $q->where('nama_outlet', 'like', '%' . $keyword . '%');
        })
        ->orderBy('nama_outlet')
        ->orderBy('id')
        ->get()
        ->filter(function ($outlet) use ($isExcludedOutlet) {
            return ! $isExcludedOutlet($outlet->nama_outlet ?? '', $outlet->status ?? '');
        })
        ->values();

    $groups = [];

    foreach ($outlets->groupBy(fn ($o) => $normalize($o->nama_outlet)) as $name => $rows) {
        $baseIds = $rows->pluck('id')->map(fn ($id) => (int) $id)->unique()->values()->all();

        if (empty($baseIds)) {
            continue;
        }

        // FIX MISSING DSC:
        // Outlet yang punya alias/duplikat harus dicek sebagai satu grup.
        // Kalau salah satu ID alias sudah isi DSC pada tanggal yang difilter,
        // outlet tersebut tidak boleh tampil sebagai BELUM MENGISI.
        $ids = $this->expandOutletAliasIds($baseIds);
        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            $ids = $baseIds;
        }

        $groups[] = [
            'id' => min($baseIds),
            'nama_outlet' => $name,
            'ids' => $ids,
            'merged_ids' => implode(',', $ids),
        ];
    }

    $allOutletIds = collect($groups)->flatMap(fn ($g) => $g['ids'])->unique()->values()->all();

    if (empty($allOutletIds)) {
        return [];
    }

    // FINAL FIX FILTER TANGGAL:
    // Row dianggap SUDAH MENGISI kalau ada data pada tanggal itu di tbl_stock
    // ATAU masih tersimpan sebagai draft aktif di tbl_stock_draft.
    // Ini mencegah kasus tanggal 1 sudah diisi tetapi tetap ketarik di list missing.
    $finalFilledRows = DB::table('tbl_stock')
        ->select(DB::raw('DATE(tanggal) as tanggal'), 'outlet_id')
        ->whereIn('outlet_id', $allOutletIds)
        ->whereDate('tanggal', '>=', $startDate)
        ->whereDate('tanggal', '<=', $endDate)
        ->groupBy(DB::raw('DATE(tanggal)'), 'outlet_id')
        ->get();

    $draftFilledRows = DB::table('tbl_stock_draft')
        ->select(DB::raw('DATE(tanggal) as tanggal'), 'outlet_id')
        ->whereIn('outlet_id', $allOutletIds)
        ->whereDate('tanggal', '>=', $startDate)
        ->whereDate('tanggal', '<=', $endDate)
        ->where('is_draft', 1)
        ->groupBy(DB::raw('DATE(tanggal)'), 'outlet_id')
        ->get();

    $filledSet = [];

    foreach ($finalFilledRows->concat($draftFilledRows) as $row) {
        $tanggalIsi = $this->normalizeDateToYmd((string) $row->tanggal);
        if ($tanggalIsi !== '') {
            $filledSet[$tanggalIsi . '|' . (int) $row->outlet_id] = true;
        }
    }

    // Kolom histori/last input sengaja dihapus dari hasil.
    // Halaman ini fokus murni: tampilkan outlet yang BELUM ADA input DSC pada tanggal filter.

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

            // Kalau tanggal yang sedang dicek sudah ada input DSC,
            // outlet tidak boleh muncul di daftar belum mengisi.
            if ($hasInput) {
                continue;
            }

            $rows[] = [
                'no' => $no++,
                'tanggal' => $dateYmd,
                'outlet_id' => $group['id'],
                'nama_outlet' => $group['nama_outlet'],
                'merged_ids' => $group['merged_ids'],
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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('OUTLET BELUM DSC');

        $headers = ['NO', 'TANGGAL BELUM ISI', 'OUTLET ID', 'NAMA OUTLET', 'MERGED IDS', 'STATUS'];
        $title = 'MONITORING OUTLET BELUM MENGISI DSC - PERIODE ' . $startDate . ' s/d ' . $endDate;

        $this->xlsxRenderTemplateLike($sheet, $title, $headers);

        $rowNo = 4;
        foreach ($rows as $row) {
            $sheet->fromArray([
                (int) ($row['no'] ?? 0),
                (string) ($row['tanggal'] ?? $startDate),
                (int) ($row['outlet_id'] ?? 0),
                (string) ($row['nama_outlet'] ?? '-'),
                (string) ($row['merged_ids'] ?? '-'),
                (string) ($row['status'] ?? 'BELUM MENGISI DSC'),
            ], null, 'A' . $rowNo);
            $rowNo++;
        }

        $lastRow = max(4, $rowNo - 1);

        $this->xlsxApplyBordersAndFormat($sheet, count($headers), $lastRow, [
            'textCols' => [2, 4, 5, 6],
            'numFrom' => 1,
            'numTo' => 3,
            'nameCol' => 4,
        ]);

        $this->xlsxSetWidths($sheet, [
            1 => 6,
            2 => 14,
            3 => 12,
            4 => 38,
            5 => 22,
            6 => 24,
        ], count($headers), 14);

        $filename = 'MONITORING_OUTLET_BELUM_DSC_' . $startDate . '_sd_' . $endDate . '.xlsx';

        /*
         * FIX EXCEL CORRUPT:
         * Jangan stream langsung ke php://output karena kalau ada whitespace,
         * warning, debugbar, BOM, atau output buffer lain, file XLSX akan rusak
         * dan Excel menampilkan pesan "file format or file extension is not valid".
         * Solusi: generate dulu ke file temporary yang valid, bersihkan buffer,
         * lalu download file fisik tersebut.
         */
        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $tmpPath = tempnam($tmpDir, 'missing_dsc_');
        $xlsxPath = $tmpPath . '.xlsx';

        if ($tmpPath && file_exists($tmpPath)) {
            @unlink($tmpPath);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($xlsxPath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $writer);

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        return response()
            ->download($xlsxPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'public',
                'Expires' => '0',
            ])
            ->deleteFileAfterSend(true);
    }
    public function exportDsc(Request $request)
    {
        try {
            /*
             |--------------------------------------------------------------------------
             | MASTER EXPORT STOCK OPNAME TEMPLATE
             |--------------------------------------------------------------------------
             | Tidak menghapus export lama. Jika request membawa:
             |   ?format=stock_opname
             | maka export mengikuti template Excel user:
             | No, Product Name, Product Code, Category, Sub Category, Unit,
             | Opname Qty, Opname Value.
             |
             | Opname Qty memakai ENDING shift 1 + shift 2 dari Detail Shift.
             */
            if ((string) $request->get('format', '') === 'stock_opname') {
                return $this->exportDscStockOpnameTemplate($request);
            }
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

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $tmpPath = $tmpDir . '/' . uniqid('dsc_export_', true) . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()->download($tmpPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Export gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

private function exportDscStockOpnameTemplate(Request $request)
{
    $tanggalRaw  = (string) $request->get('tanggal', date('Y-m-d'));
    $startRaw    = (string) $request->get('start_date', $tanggalRaw);
    $endRaw      = (string) $request->get('end_date', $startRaw ?: $tanggalRaw);
    $outletRaw   = $request->get('outlet_id', '');
    $shiftFilter = (string) $request->get('shift_filter', 'all');

    $startDate = $this->normalizeDateToYmd($startRaw ?: $tanggalRaw);
    $endDate   = $this->normalizeDateToYmd($endRaw ?: $startDate);

    if ($startDate > $endDate) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }

    if ($outletRaw === '' || $outletRaw === null || $outletRaw === 'all') {
        return response()->json([
            'ok' => false,
            'message' => 'outlet_id wajib dipilih sebelum export',
        ], 400);
    }

    $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;

    if ($days > 31) {
        return response()->json([
            'ok' => false,
            'message' => 'Range export maksimal 31 hari.',
        ], 422);
    }

    /*
     |--------------------------------------------------------------------------
     | PATCH STOCK OPNAME EXPORT RANGE
     |--------------------------------------------------------------------------
     | - Export 1 tanggal dan start-end tanggal memakai sumber yang sama:
     |   buildDailyStockControlData().
     | - Untuk range, Opname Qty diambil dari rekap periode. Jika karena data periode
     |   kosong, fallback aman ke tanggal akhir supaya export tetap muncul.
     | - Baris export dibuat dari master bahan DSC aktif, jadi list bahan tetap muncul.
     | - Opname Value adalah harga per 1 bahan dari tbl_bahan_harga HO/MITRA,
     |   bukan total qty x harga.
     |--------------------------------------------------------------------------
     */

    $makeExportRequest = function (string $fromDate, string $toDate) use ($request, $outletRaw, $shiftFilter) {
        $exportRequest = Request::create(
            $request->path(),
            'GET',
            array_merge($request->query(), [
                'tanggal' => $toDate,
                'start_date' => $fromDate,
                'end_date' => $toDate,
                'outlet_id' => $outletRaw,
                'shift_filter' => $shiftFilter,
            ])
        );

        $exportRequest->setUserResolver(fn () => $request->user());

        return $exportRequest;
    };

    $data = $this->buildDailyStockControlData($makeExportRequest($startDate, $endDate));
    $rekapRows = collect($data['rekapRows'] ?? []);

    /*
     * Kalau range start-end ternyata tidak membentuk rekapRows, jangan biarkan export kosong.
     * Fallback ke tanggal akhir karena Stock Opname butuh posisi akhir stok.
     */
    if ($rekapRows->isEmpty() && $startDate !== $endDate) {
        $data = $this->buildDailyStockControlData($makeExportRequest($endDate, $endDate));
        $rekapRows = collect($data['rekapRows'] ?? []);
    }

    $selectedOutletIdsForPrice = array_values(array_filter(array_map('intval', $data['selectedOutletIds'] ?? [])));
    $kategoriHargaExport = null;

    if (!empty($selectedOutletIdsForPrice)) {
        $kategoriHargaExport = DB::table('tbl_outlets')
            ->whereIn('id', $selectedOutletIdsForPrice)
            ->whereNotNull('kategori_harga')
            ->orderByRaw("FIELD(kategori_harga, 'HO', 'MITRA')")
            ->value('kategori_harga');
    }

    $normalizeName = function ($name) {
        $name = strtoupper(trim((string) $name));
        $name = preg_replace('/\s+/', ' ', $name);
        return $name;
    };

    $hargaBahanExportByName = DB::table('tbl_bahan as b')
        ->leftJoin('tbl_bahan_harga as bh', function ($join) use ($kategoriHargaExport) {
            $join->on('bh.bahan_id', '=', 'b.id');

            if ($kategoriHargaExport) {
                $join->where('bh.kategori_harga', '=', $kategoriHargaExport);
            } else {
                $join->whereRaw('1 = 0');
            }
        })
        ->select(
            'b.nama_bahan',
            DB::raw('COALESCE(bh.harga, b.harga_bahan, 0) as harga_export')
        )
        ->get()
        ->mapWithKeys(function ($row) use ($normalizeName) {
            return [$normalizeName($row->nama_bahan) => (float) ($row->harga_export ?? 0)];
        });

    $rekapByKey = $rekapRows
        ->mapWithKeys(function ($r) use ($normalizeName) {
            $name = $normalizeName($r['nama'] ?? '');
            $unit = strtoupper(trim((string) ($r['sat'] ?? '')));
            return [$name . '|' . $unit => $r];
        });

    $masterBahanRows = DB::table('tbl_bahan_dsc')
        ->select('id', 'nama_bahan', 'satuan')
        ->where('is_active', 1)
        ->orderBy('id')
        ->get();

    $rows = collect();

    foreach ($masterBahanRows as $index => $b) {
        $name = trim((string) ($b->nama_bahan ?? ''));

        if ($name === '') {
            continue;
        }

        $unit = strtoupper(trim((string) ($b->satuan ?? '')));
        $key = $normalizeName($name) . '|' . $unit;
        $r = $rekapByKey->get($key);

        [$category, $subCategory] = $this->dscStockOpnameCategory($name, $unit);

        $qty = $r
            ? (float) ($r['ending_stock'] ?? $r['open_stock_right'] ?? 0)
            : 0.0;

        $rows->push([
            'name' => $name,
            'unit' => $unit,
            'category' => $category,
            'sub_category' => $subCategory,
            'qty' => $qty,
            'value' => (float) ($hargaBahanExportByName->get($normalizeName($name)) ?? 0),
        ]);
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Worksheet');

    $headers = [
        'No',
        'Product Name',
        'Product Code',
        'Category',
        'Sub Category',
        'Unit',
        'Opname Qty',
        'Opname Value',
    ];

    $sheet->setCellValue('A1', 'Stock Opname Template');
    $sheet->mergeCells('A1:H1');

    $sheet->fromArray($headers, null, 'A3');

    $rowNum = 4;

    foreach ($rows as $index => $r) {
        $unit = strtoupper((string) ($r['unit'] ?? ''));
        $qty = (float) ($r['qty'] ?? 0);

        if (in_array($unit, ['PCS', 'PC', 'PCE', 'LBR', 'LEMBAR', 'CUP', 'BOTOL', 'BTL', 'SACHET', 'PACK', 'PAK', 'BOX', 'DUS'], true)) {
            $qty = round($qty);
        }

        $sheet->fromArray([
            $index + 1,
            (string) ($r['name'] ?? ''),
            '', // Product Code kosong
            (string) ($r['category'] ?? 'BAHAN BAKU'),
            (string) ($r['sub_category'] ?? 'BAHAN BAKU'),
            $unit,
            $qty,
            (float) ($r['value'] ?? 0), // Harga per 1 bahan, bukan total
        ], null, "A{$rowNum}");

        $rowNum++;
    }

    $lastRow = max(4, $rowNum - 1);

    $sheet->getStyle('A1:H1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 14],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    $sheet->getStyle("A3:H{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ]);

    $sheet->getStyle('A3:H3')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F2F2F2'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ],
    ]);

    $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("B4:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("G4:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // Qty tanpa desimal, harga tetap angka per 1 bahan.
    $sheet->getStyle("G4:G{$lastRow}")->getNumberFormat()->setFormatCode('0');
    $sheet->getStyle("H4:H{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');

    foreach ([
        'A' => 7,
        'B' => 32,
        'C' => 18,
        'D' => 18,
        'E' => 20,
        'F' => 16,
        'G' => 16,
        'H' => 18,
    ] as $col => $width) {
        $sheet->getColumnDimension($col)->setWidth($width);
    }

    $sheet->freezePane('A4');
    $sheet->setAutoFilter("A3:H{$lastRow}");

    $safeOutlet = is_string($outletRaw)
        ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $outletRaw)
        : (string) $outletRaw;

    $filename = "STOCK_OPNAME_TEMPLATE_outlet{$safeOutlet}_{$startDate}_sd_{$endDate}_shift{$shiftFilter}.xlsx";

    return response()->streamDownload(function () use ($spreadsheet) {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

    private function dscStockOpnameNormalizeQty(float $qty, string $unit): float
    {
        $unit = strtoupper(trim($unit));
        $integerUnits = ['PCS', 'PC', 'PCE', 'LBR', 'LEMBAR', 'CUP', 'BOTOL', 'BTL', 'SACHET', 'PACK', 'PAK', 'BOX', 'DUS', 'IKAT', 'BUNGKUS'];

        if (in_array($unit, $integerUnits, true)) {
            return (float) round($qty);
        }

        return round($qty, 2);
    }

    private function dscStockOpnameCategory(string $name, string $unit): array
    {
        $n = strtoupper(trim($name));
        $u = strtoupper(trim($unit));

        if (str_contains($n, 'CUP')) {
            return ['CUP', 'CUP'];
        }

        if (str_contains($n, 'MINERAL') || str_contains($n, 'LE MINERALE') || str_contains($n, 'CLEO') || str_contains($n, 'TEH') || str_contains($n, 'FRUITEA')) {
            return ['BEVERAGE', 'BEVERAGE'];
        }

        if (str_contains($n, 'GLOVE')) {
            return ['GLOVES', 'GLOVES'];
        }

        if (str_contains($n, 'KRESEK') || str_contains($n, 'PLASTIK')) {
            return ['PLASTIK', 'PLASTIK'];
        }

        if (str_contains($n, 'KERTAS') || str_contains($n, 'LUNCHBOX') || str_contains($n, 'PAPER') || str_contains($n, 'WRAP')) {
            return ['PERLENGKAPAN', 'PERLENGKAPAN'];
        }

        return ['BAHAN BAKU', 'BAHAN BAKU'];
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
            'items.*.satuan' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                $satuan = (string) ($item['satuan'] ?? '');
                $qtyAbs = (float) ($item['qty_abs'] ?? 0);
                $hiddenQty = (float) ($item['qty_hidden'] ?? 0);

                // FIX QCR: satuan integer-like (PCS/LBR/CUP/dll) tidak boleh menyimpan qty decimal.
                // Ini mencegah AYAM KECIL / AYAM BESAR tampil 31,20 PCS atau 28,50 PCS di modal Hapus +/-.
                if ($this->isIntegerLikeQcrUnit($satuan)) {
                    $qtyAbs = (float) round($qtyAbs);
                    $hiddenQty = (float) round($hiddenQty);
                }

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


    private function normalizeQcrUangPlusRowsForSaldo($rows)
    {
        /*
         * FIX UANG PLUS DISPLAY / SPEND:
         * Uang Plus adalah saldo global per outlet + tanggal + shift, bukan per bahan.
         * Data lama kadang menyimpan nominal yang sama di banyak bahan, sehingga
         * Rp 20.000 bisa tampil Rp 200.000 jika dijumlah mentah.
         *
         * Normalisasi:
         * - group per outlet/tanggal/shift
         * - pakai 1 row kanonik saja sebagai saldo aktif
         * - jika row positif lebih dari 1, saldo group memakai nilai terbesar
         *   (bukan sum) untuk mencegah nominal terduplikasi antar bahan
         * - duplicate_rows disimpan agar bisa dinolkan saat saldo dibelanjakan
         */
        return collect($rows)
            ->filter(fn ($row) => (float) ($row->uang_plus ?? 0) > 0)
            ->groupBy(function ($row) {
                return implode('|', [
                    (int) ($row->outlet_id ?? 0),
                    \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d'),
                    (int) ($row->shift ?? 0),
                ]);
            })
            ->map(function ($groupRows) {
                $positiveRows = collect($groupRows)
                    ->filter(fn ($row) => (float) ($row->uang_plus ?? 0) > 0)
                    ->sortByDesc('updated_at')
                    ->sortByDesc('id')
                    ->values();

                if ($positiveRows->isEmpty()) {
                    return null;
                }

                $canonical = clone $positiveRows->first();

                // Ambil nominal terbesar sebagai saldo aktif group.
                // Ini aman untuk kasus lama: nominal sama tertulis berulang di banyak bahan.
                $canonical->uang_plus = (float) $positiveRows->max(fn ($row) => (float) ($row->uang_plus ?? 0));

                $canonical->duplicate_uang_plus_rows = $positiveRows
                    ->filter(fn ($row) => (int) ($row->id ?? 0) !== (int) ($canonical->id ?? 0) || (string) ($row->row_source ?? '') !== (string) ($canonical->row_source ?? ''))
                    ->values()
                    ->all();

                return $canonical;
            })
            ->filter()
            ->values();
    }

    private function sumQcrUangPlusSaldo($rows): float
    {
        return (float) $this->normalizeQcrUangPlusRowsForSaldo($rows)
            ->sum(fn ($row) => (float) ($row->uang_plus ?? 0));
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
            /*
             * PATCH SALDO UANG PLUS QCR:
             * Ambil saldo dari final + draft aktif, sama seperti data QCR.
             * Jika ada draft untuk outlet/tanggal/shift/bahan yang sama, draft overwrite final.
             */
            $finalUangPlusRows = DB::table('tbl_stock')
                ->whereIn('outlet_id', $outletIds)
                ->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
                ->where('uang_plus', '>', 0)
                ->orderByDesc('tanggal')
                ->orderByDesc('shift')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get(['id', 'outlet_id', 'tanggal', 'shift', 'bahan_id', 'uang_plus'])
                ->map(function ($row) {
                    $row->row_source = 'final';
                    return $row;
                });

            $draftUangPlusRows = DB::table('tbl_stock_draft')
                ->whereIn('outlet_id', $outletIds)
                ->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
                ->where('is_draft', 1)
                ->where('uang_plus', '>', 0)
                ->orderByDesc('tanggal')
                ->orderByDesc('shift')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get(['id', 'outlet_id', 'tanggal', 'shift', 'bahan_id', 'uang_plus'])
                ->map(function ($row) {
                    $row->row_source = 'draft';
                    return $row;
                });

            $stockRowsByKey = collect();

            foreach ($finalUangPlusRows as $row) {
                $key = implode('|', [
                    (int) $row->outlet_id,
                    \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d'),
                    (int) $row->shift,
                    (int) ($row->bahan_id ?? 0),
                ]);

                $stockRowsByKey->put($key, $row);
            }

            foreach ($draftUangPlusRows as $row) {
                $key = implode('|', [
                    (int) $row->outlet_id,
                    \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d'),
                    (int) $row->shift,
                    (int) ($row->bahan_id ?? 0),
                ]);

                // Draft aktif mengikuti tampilan QCR, jadi overwrite final.
                $stockRowsByKey->put($key, $row);
            }

            $stockRows = $stockRowsByKey
                ->values()
                ->sortByDesc('id')
                ->sortByDesc('shift')
                ->sortByDesc('tanggal')
                ->values();

            $stockRows = $this->normalizeQcrUangPlusRowsForSaldo($stockRows);
            $saldoServer = (float) $stockRows->sum(fn ($row) => (float) ($row->uang_plus ?? 0));
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

                $targetTable = (($row->row_source ?? 'final') === 'draft')
                    ? 'tbl_stock_draft'
                    : 'tbl_stock';

                DB::table($targetTable)
                    ->where('id', (int) $row->id)
                    ->update([
                        'uang_plus' => $newValue,
                        'updated_at' => now(),
                    ]);

                // Bersihkan duplikat uang_plus lama dalam group outlet/tanggal/shift yang sama.
                foreach (collect($row->duplicate_uang_plus_rows ?? []) as $dupRow) {
                    $dupTable = (($dupRow->row_source ?? 'final') === 'draft')
                        ? 'tbl_stock_draft'
                        : 'tbl_stock';

                    DB::table($dupTable)
                        ->where('id', (int) $dupRow->id)
                        ->update([
                            'uang_plus' => 0,
                            'updated_at' => now(),
                        ]);
                }

                $deductions[] = [
                    'stock_id' => (int) $row->id,
                    'outlet_id' => (int) $row->outlet_id,
                    'tanggal' => (string) $row->tanggal,
                    'shift' => (int) $row->shift,
                    'old_value' => $available,
                    'deducted' => $take,
                    'new_value' => $newValue,
                    'source' => (string) ($row->row_source ?? 'final'),
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
                $subtotal = (float) $item['subtotal'];

                $this->insertQcrUangPlusTransaction([
                    'nomor' => $idempotencyKey . '-' . ($idx + 1),
                    'outlet_id' => $trxOutletId,
                    'sesi_tanggal' => $trxDate,
                    'tr_waktu' => $trxTime,
                    'tr_metode' => 'UANG_PLUS',

                    'item_nama' => $item['nama_menu'],
                    'item_varian' => $item['tipe'],
                    'item_jumlah' => $item['qty'],
                    'item_harga' => $item['harga'],
                    'item_sub_total' => $subtotal,

                    'grand_total' => $subtotal,
                    'payment_total' => $subtotal,
                    'customer_unit' => 1,
                    'item_status' => 8,

                    'source' => 'UANG_PLUS',
                    'keterangan' => 'PENUKARAN DATA DARI UANG PLUS',
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


    /*
     |--------------------------------------------------------------------------
     | PATCH REQUEST TIM - RAPEL / REVISI UANG PLUS DSC
     |--------------------------------------------------------------------------
     | Dipakai kalau uang plus lupa diinput pada tanggal yang sedang dikerjakan.
     | Perubahan hanya menyentuh kolom uang_plus dan keterangan pada row DSC.
     | Tidak mengubah purchase, mutasi, adjustment, ending, used, maupun rumus stok.
     */
    public function saveDscUangPlusRapel(Request $request)
    {
        $groupedOutlets = $this->getGroupedOutletsForUser(auth()->id());
        $this->normalizeDscOutletRequest($request, $groupedOutlets);

        $request->validate([
            'outlet_id' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'shift' => 'required|in:1,2',
            'apply_all_bahan' => 'nullable|boolean',
            'bahan_id' => 'nullable|integer',
            'uang_plus' => 'required|numeric|min:0',
            'mode' => 'nullable|in:add,set',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $selectedOutletId = (int) $request->outlet_id;
        $tanggal = $this->normalizeDateToYmd((string) $request->tanggal);
        $shift = (int) $request->shift;
        $applyAllBahan = $request->boolean('apply_all_bahan');
        $bahanId = $request->filled('bahan_id') ? (int) $request->bahan_id : null;
        $nominal = (float) $request->uang_plus;
        $mode = (string) $request->get('mode', 'add');
        $note = strtoupper(trim((string) $request->get('keterangan', 'RAPEL UANG PLUS')));

        if (! $applyAllBahan && ! $bahanId) {
            return response()->json([
                'ok' => false,
                'message' => 'Bahan wajib dipilih jika target bukan Semua Bahan.',
            ], 422);
        }

        $displayOutletId = $this->getOutletDisplayIdFromSelected($selectedOutletId, $groupedOutlets);

        $close = $this->getCloseStatus($displayOutletId, $tanggal);
        if (! empty($close['is_closed']) && ! $this->canEditFilledDscNominal()) {
            return response()->json([
                'ok' => false,
                'message' => 'KASIR SUDAH DITUTUP. RAPEL UANG PLUS HANYA BOLEH OLEH ROLE YANG BERWENANG.',
            ], 423);
        }

        DB::beginTransaction();
        try {
            $targets = collect();

            if ($applyAllBahan) {
                // Semua Bahan:
                // Untuk setiap bahan, prioritas update tetap sama seperti tampilan DSC: draft aktif lebih dulu, fallback final.
                // Perubahan tetap hanya menyentuh uang_plus dan keterangan.
                $draftRows = DB::table('tbl_stock_draft')
                    ->where('outlet_id', $displayOutletId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', $shift)
                    ->where('is_draft', 1)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('bahan_id');

                $finalRows = DB::table('tbl_stock')
                    ->where('outlet_id', $displayOutletId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', (string) $shift)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('bahan_id');

                foreach ($draftRows as $row) {
                    $row->target_table = 'tbl_stock_draft';
                    $targets->push($row);
                }

                foreach ($finalRows as $bid => $row) {
                    if ($draftRows->has($bid)) {
                        continue;
                    }
                    $row->target_table = 'tbl_stock';
                    $targets->push($row);
                }
            } else {
                $draft = DB::table('tbl_stock_draft')
                    ->where('outlet_id', $displayOutletId)
                    ->whereDate('tanggal', $tanggal)
                    ->where('shift', $shift)
                    ->where('bahan_id', $bahanId)
                    ->where('is_draft', 1)
                    ->lockForUpdate()
                    ->first();

                $final = null;
                if (! $draft) {
                    $final = DB::table('tbl_stock')
                        ->where('outlet_id', $displayOutletId)
                        ->whereDate('tanggal', $tanggal)
                        ->where('shift', (string) $shift)
                        ->where('bahan_id', $bahanId)
                        ->lockForUpdate()
                        ->first();
                }

                $targetRow = $draft ?: $final;
                if ($targetRow) {
                    $targetRow->target_table = $draft ? 'tbl_stock_draft' : 'tbl_stock';
                    $targets->push($targetRow);
                }
            }

            if ($targets->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'ok' => false,
                    'message' => $applyAllBahan
                        ? 'Data DSC untuk tanggal dan shift tersebut belum ada. Simpan DSC terlebih dahulu, lalu rapel uang plus.'
                        : 'Data DSC untuk tanggal, shift, dan bahan tersebut belum ada. Simpan DSC terlebih dahulu, lalu rapel uang plus.',
                ], 422);
            }

            $updatedRows = 0;
            $oldTotal = 0.0;
            $newTotal = 0.0;

            /*
             |--------------------------------------------------------------------------
             | FIX RAPEL / REVISI UANG PLUS GLOBAL
             |--------------------------------------------------------------------------
             | Bug sebelumnya:
             | Target "Semua Bahan" mengupdate setiap row bahan dengan nominal yang sama.
             | Akibatnya input 20.000 bisa menjadi 20.000 x jumlah bahan aktif
             | (contoh 1.540.000), dan keterangan "REVISI UANG PLUS" ikut berulang
             | sebanyak jumlah bahan.
             |
             | Perbaikan:
             | - Target Semua Bahan diperlakukan sebagai nilai global per outlet/tanggal/shift.
             | - Nilai global hanya disimpan di 1 row kanonik.
             | - Row lain dinolkan uang_plus-nya agar total tidak dobel.
             | - Keterangan rapel/revisi hanya ditulis sekali, tidak diulang-ulang.
             | - Target Per Bahan tetap seperti biasa.
             |--------------------------------------------------------------------------
             */
            $normalizeKetParts = function (string $ket, string $noteToRemove = '') {
                $parts = collect(explode('|', $ket))
                    ->map(fn ($v) => strtoupper(trim((string) $v)))
                    ->filter();

                if ($noteToRemove !== '') {
                    $noteNorm = strtoupper(trim($noteToRemove));
                    $parts = $parts->reject(fn ($v) => $v === $noteNorm);
                }

                return $parts->unique()->values();
            };

            $buildKet = function (string $oldKet, string $noteText, bool $appendNote = true) use ($normalizeKetParts) {
                $parts = $normalizeKetParts($oldKet, $noteText);

                if ($appendNote && trim($noteText) !== '') {
                    $parts->push(strtoupper(trim($noteText)));
                }

                return $parts->unique()->implode(' | ');
            };

            if ($applyAllBahan) {
                $oldTotal = (float) $targets->sum(fn ($row) => (float) ($row->uang_plus ?? 0));
                $globalNewValue = $mode === 'set'
                    ? $nominal
                    : ($oldTotal + $nominal);

                $canonicalRow = $targets
                    ->sortBy(fn ($row) => ((string) $row->target_table === 'tbl_stock_draft' ? 0 : 1) . '-' . str_pad((string) (int) $row->bahan_id, 10, '0', STR_PAD_LEFT))
                    ->first();

                foreach ($targets as $targetRow) {
                    $targetTable = (string) $targetRow->target_table;
                    $targetBahanId = (int) $targetRow->bahan_id;
                    $oldValue = (float) ($targetRow->uang_plus ?? 0);
                    $isCanonical = $canonicalRow
                        && (string) $canonicalRow->target_table === $targetTable
                        && (int) $canonicalRow->id === (int) $targetRow->id;

                    $newValue = $isCanonical ? $globalNewValue : 0.0;

                    $oldKet = trim((string) ($targetRow->keterangan ?? ''));
                    $newKet = $isCanonical
                        ? $buildKet($oldKet, $note, true)
                        : $buildKet($oldKet, $note, false);

                    DB::table($targetTable)
                        ->where('id', (int) $targetRow->id)
                        ->update([
                            'uang_plus' => $newValue,
                            'keterangan' => $newKet,
                            'updated_at' => now(),
                        ]);

                    $this->auditDscStockChanges(
                        $targetTable,
                        'RAPEL_UANG_PLUS_ALL_BAHAN',
                        $displayOutletId,
                        $tanggal,
                        $shift,
                        $targetBahanId,
                        $targetRow,
                        ['uang_plus' => $newValue, 'keterangan' => $newKet],
                        strtoupper((string) (auth()->user()->name ?? auth()->user()->email ?? 'SYSTEM')),
                        'rapel_uang_plus'
                    );

                    $updatedRows++;
                }

                $newTotal = $globalNewValue;
            } else {
                foreach ($targets as $targetRow) {
                    $targetTable = (string) $targetRow->target_table;
                    $targetBahanId = (int) $targetRow->bahan_id;
                    $oldValue = (float) ($targetRow->uang_plus ?? 0);
                    $newValue = $mode === 'set'
                        ? $nominal
                        : ($oldValue + $nominal);

                    $oldKet = trim((string) ($targetRow->keterangan ?? ''));
                    $newKet = $buildKet($oldKet, $note, true);

                    DB::table($targetTable)
                        ->where('id', (int) $targetRow->id)
                        ->update([
                            'uang_plus' => $newValue,
                            'keterangan' => $newKet,
                            'updated_at' => now(),
                        ]);

                    $this->auditDscStockChanges(
                        $targetTable,
                        'RAPEL_UANG_PLUS',
                        $displayOutletId,
                        $tanggal,
                        $shift,
                        $targetBahanId,
                        $targetRow,
                        ['uang_plus' => $newValue, 'keterangan' => $newKet],
                        strtoupper((string) (auth()->user()->name ?? auth()->user()->email ?? 'SYSTEM')),
                        'rapel_uang_plus'
                    );

                    $updatedRows++;
                    $oldTotal += $oldValue;
                    $newTotal += $newValue;
                }
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => $applyAllBahan
                    ? 'Rapel uang plus berhasil disimpan untuk semua bahan tanpa mengubah rumus stok.'
                    : 'Rapel uang plus berhasil disimpan tanpa mengubah rumus stok.',
                'data' => [
                    'old_uang_plus' => $oldTotal,
                    'new_uang_plus' => $newTotal,
                    'updated_rows' => $updatedRows,
                    'apply_all_bahan' => $applyAllBahan,
                    'mode' => $mode,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan rapel uang plus.',
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
                    return $this->expandOutletAliasIds($groupRows->pluck('id')->map(fn ($id) => (int) $id)->values()->all());
                }
            }
            return [];
        }

        $single = $rows->firstWhere('id', (int) $outletIdRaw);
        if (! $single) {
            return [];
        }

        $nameNorm = $normalize($single->nama_outlet);
        return $this->expandOutletAliasIds(
            $rows
                ->filter(fn ($o) => $normalize($o->nama_outlet) === $nameNorm)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all()
        );
    }

    private function insertQcrUangPlusTransaction(array $data): void
    {
        if (!Schema::hasTable('tbl_qcr_uang_plus_transactions')) {
            throw new \RuntimeException('Tabel tbl_qcr_uang_plus_transactions tidak ditemukan.');
        }

        $nomor = $data['nomor']
            ?? ('UPLUS-' . ($data['outlet_id'] ?? '0') . '-' . now()->format('YmdHis') . '-' . mt_rand(1000, 9999));

        DB::table('tbl_qcr_uang_plus_transactions')->updateOrInsert(
            [
                'nomor' => $nomor,
            ],
            [
                'outlet_id'      => (int) $data['outlet_id'],
                'sesi_tanggal'   => $data['sesi_tanggal'],
                'tr_waktu'       => $data['tr_waktu'] ?? now()->format('H:i:s'),
                'tr_metode'      => 'UANG_PLUS',

                'item_nama'      => $data['item_nama'],
                'item_varian'    => $data['item_varian'] ?? null,
                'item_harga'     => (float) ($data['item_harga'] ?? 0),
                'item_jumlah'    => (float) ($data['item_jumlah'] ?? 0),
                'item_sub_total' => (float) ($data['item_sub_total'] ?? 0),

                'grand_total'    => (float) ($data['grand_total'] ?? $data['item_sub_total'] ?? 0),
                'payment_total'  => (float) ($data['payment_total'] ?? $data['item_sub_total'] ?? 0),

                'customer_unit'  => (int) ($data['customer_unit'] ?? 1),
                'item_status'    => 8,

                'keterangan'     => 'PENUKARAN DATA DARI UANG PLUS',
                'source'         => 'UANG_PLUS',

                'updated_at'     => now(),
                'created_at'     => now(),
            ]
        );
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

    private function isIntegerLikeQcrUnit(string $satuan = ''): bool
    {
        $satuan = strtoupper(trim((string) $satuan));

        $integerLikeUnits = [
            'PCS', 'PC', 'PCE',
            'LBR', 'LEMBAR',
            'CUP',
            'BOTOL', 'BTL',
            'SACHET', 'SCT',
            'PACK', 'PAK',
            'BOX', 'DUS',
            'IKAT', 'BUNGKUS',
            'PCS/BOX',
        ];

        return in_array($satuan, $integerLikeUnits, true);
    }

    private function normalizeQcrDisplayQty(float $qty, string $satuan = ''): float
    {
        /*
         |--------------------------------------------------------------------------
         | FIX SATUAN QCR
         |--------------------------------------------------------------------------
         | Satuan seperti PCS/LBR/CUP/BOTOL/SACHET/PACK tidak masuk akal tampil
         | decimal, contoh:
         | - AYAM KECIL 333,09 PCS
         | - AYAM 0,03 PCS
         |
         | Untuk satuan integer-like, qty dibulatkan ke angka utuh.
         | Untuk GRAM dan satuan berat, decimal tetap boleh.
         */
        if ($this->isIntegerLikeQcrUnit($satuan)) {
            return (float) round($qty);
        }

        $abs = abs($qty);

        if ($abs < 0.000001) {
            return 0.0;
        }

        return round($qty, 3);
    }

    private function normalizeQcrDifferenceQty(float $qty, string $satuan = ''): float
    {
        /*
         |--------------------------------------------------------------------------
         | FIX MODAL HAPUS PLUS/MINUS QCR
         |--------------------------------------------------------------------------
         | Selisih QCR mengikuti satuan.
         | - PCS/LBR/CUP/BOTOL/SACHET/PACK: tidak boleh decimal, dibulatkan.
         | - Nilai yang setelah pembulatan menjadi 0 tidak masuk modal.
         | - GRAM tetap boleh decimal.
         */
        if ($this->isIntegerLikeQcrUnit($satuan)) {
            $rounded = (float) round($qty);
            return abs($rounded) < 1 ? 0.0 : $rounded;
        }

        if (abs($qty) < 0.01) {
            return 0.0;
        }

        return round($qty, 3);
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

    private function buildQcrStockAggByBahanId($stockRows, array $outletIds = [], int $displayOutletId = 0, array $openingBulkMap = [], string $startDate = '', string $endDate = '')
    {
        $outletIds = array_values(array_unique(array_map('intval', $outletIds)));
        $outletIds = array_values(array_filter($outletIds, fn ($id) => $id > 0));
        $displayOutletId = $displayOutletId > 0
            ? $displayOutletId
            : (int) ($outletIds[0] ?? 0);

        $datesInRows = $stockRows
            ->map(fn ($r) => \Carbon\Carbon::parse($r->tanggal)->format('Y-m-d'))
            ->unique()
            ->values();

        $bahanIdsInRows = $stockRows
            ->pluck('bahan_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        /*
         |--------------------------------------------------------------------------
         | FIX QCR RANGE USAGE DSC
         |--------------------------------------------------------------------------
         | QCR range tidak boleh menghitung Usage DSC hanya dari:
         | OPEN tanggal awal + total transaksi stok - ENDING tanggal akhir.
         |
         | Untuk kasus bahan yang stoknya muncul/hilang di tengah periode seperti CLEO,
         | cara itu bisa menutup minus harian. Karena itu kita siapkan opening UI/alias
         | per tanggal, lalu usage dihitung dari akumulasi harian yang sama dengan DSC.
         */
        $openingBulkByDate = [];
        foreach ($datesInRows as $dateKey) {
            if ($dateKey === $startDate && !empty($openingBulkMap)) {
                $openingBulkByDate[$dateKey] = $openingBulkMap;
                continue;
            }

            $openingBulkByDate[$dateKey] = $this->getOpeningBulkFromAliasIds(
                !empty($outletIds) ? $outletIds : [],
                $displayOutletId,
                $bahanIdsInRows,
                $dateKey
            );
        }

        $getOpeningForQcrDate = function (int $bahanId, string $dateKey, int $shift) use ($openingBulkByDate, $outletIds, $displayOutletId) {
            if (isset($openingBulkByDate[$dateKey][$bahanId][$shift])) {
                return (float) $openingBulkByDate[$dateKey][$bahanId][$shift];
            }

            return (float) $this->getOpeningFromAliasIds(
                !empty($outletIds) ? $outletIds : [],
                $displayOutletId,
                $bahanId,
                $dateKey,
                $shift
            );
        };

        return $stockRows
            ->groupBy(fn ($r) => (int) $r->bahan_id)
            ->map(function ($grp) use ($outletIds, $displayOutletId, $openingBulkMap, $getOpeningForQcrDate) {
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
                // V2: pakai opening bulk yang sama dengan Daily Stock Control.
                // Penting untuk kasus closing Shift 2 = 0/kosong tetapi Shift 1 masih punya closing valid;
                // helper bulk melakukan fallback ke Shift 1, sedangkan helper single lama bisa jatuh ke 0.
                $available = isset($openingBulkMap[$bahanId][1])
                    ? (float) $openingBulkMap[$bahanId][1]
                    : (float) $this->getOpeningFromAliasIds(
                        !empty($outletIds) ? $outletIds : [(int) ($first->outlet_id ?? 0)],
                        $displayOutletId > 0 ? $displayOutletId : (int) ($first->outlet_id ?? 0),
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
                $actualTepung = (float) $sorted->sum(fn ($r) => (float) ($r->actual_tepung ?? 0));

                $wasteQty = $wasteProduct + $wasteBahan + $wasteTepung;

                // TOTAL mengikuti rumus final:
                // TOTAL = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
                // ADJ tidak masuk TOTAL.
                $totalAvailable = $available + $purchase + $mutIn - $mutOut;

                /*
                 |--------------------------------------------------------------------------
                 | FIX QCR RANGE USAGE DSC - AKUMULASI HARIAN
                 |--------------------------------------------------------------------------
                 | TOTAL/ENDING tetap disimpan sebagai informasi stok periode.
                 | Tetapi Usage DSC untuk QCR harus mengikuti jumlah USED harian yang
                 | opening-nya sudah dikoreksi oleh helper UI/alias. Ini mencegah minus
                 | harian CLEO tanggal 4/9 hilang karena tertutup ending tanggal akhir.
                 */
                $mergeQcrShiftRows = function ($rows) {
                    if (!$rows || $rows->isEmpty()) {
                        return null;
                    }

                    $latest = $rows->sortByDesc('tanggal')->sortByDesc('id')->first();
                    $row = clone $latest;
                    $row->purchase_in = (float) $rows->sum(fn ($x) => (float) ($x->purchase_in ?? 0));
                    $row->mutasi_in = (float) $rows->sum(fn ($x) => (float) ($x->mutasi_in ?? 0));
                    $row->mutasi_out = (float) $rows->sum(fn ($x) => (float) ($x->mutasi_out ?? 0));
                    $row->adjustment_qty = (float) $rows->sum(fn ($x) => (float) ($x->adjustment_qty ?? 0));

                    $latestEnding = $rows
                        ->filter(fn ($x) => $x->ending_stock !== null)
                        ->sortByDesc('tanggal')
                        ->sortByDesc('id')
                        ->first();

                    $row->ending_stock = $latestEnding ? (float) ($latestEnding->ending_stock ?? 0) : 0.0;

                    return $row;
                };

                $usageDaily = 0.0;
                $sortedByDate = $sorted->groupBy(fn ($r) => \Carbon\Carbon::parse($r->tanggal)->format('Y-m-d'));

                foreach ($sortedByDate as $dateKey => $rowsOnDate) {
                    $r1Day = $mergeQcrShiftRows($rowsOnDate->filter(fn ($r) => (int) $r->shift === 1));
                    $r2Day = $mergeQcrShiftRows($rowsOnDate->filter(fn ($r) => (int) $r->shift === 2));

                    $openS1Day = (float) $getOpeningForQcrDate($bahanId, (string) $dateKey, 1);
                    $openS2Day = ($r1Day && $r1Day->ending_stock !== null)
                        ? (float) $r1Day->ending_stock
                        : (float) $getOpeningForQcrDate($bahanId, (string) $dateKey, 2);

                    $pinDay = (float) ($r1Day->purchase_in ?? 0) + (float) ($r2Day->purchase_in ?? 0);
                    $miDay = (float) ($r1Day->mutasi_in ?? 0) + (float) ($r2Day->mutasi_in ?? 0);
                    $moDay = (float) ($r1Day->mutasi_out ?? 0) + (float) ($r2Day->mutasi_out ?? 0);
                    $adjDay = (float) ($r1Day->adjustment_qty ?? 0) + (float) ($r2Day->adjustment_qty ?? 0);

                    $endingS1Day = ($r1Day && $r1Day->ending_stock !== null)
                        ? (float) $r1Day->ending_stock + (float) ($r1Day->adjustment_qty ?? 0)
                        : null;
                    $endingS2Day = ($r2Day && $r2Day->ending_stock !== null)
                        ? (float) $r2Day->ending_stock + (float) ($r2Day->adjustment_qty ?? 0)
                        : null;

                    if ($endingS2Day !== null && $endingS2Day > 0) {
                        $endingDay = $endingS2Day;
                    } elseif ($endingS1Day !== null && $endingS1Day > 0) {
                        $endingDay = $endingS1Day;
                    } elseif ($endingS2Day !== null) {
                        $endingDay = $endingS2Day;
                    } elseif ($endingS1Day !== null) {
                        $endingDay = $endingS1Day;
                    } else {
                        $endingDay = 0.0;
                    }

                    $totalDay = $openS1Day + $pinDay + $miDay - $moDay;
                    $usageDaily += ($totalDay - $endingDay);
                }

                /*
                 |--------------------------------------------------------------------------
                 | FIX FINAL QCR USAGE DSC RANGE
                 |--------------------------------------------------------------------------
                 | Untuk Summary QCR, Usage DSC harus sama dengan rumus REKAP DSC periode:
                 |
                 | TOTAL = OPEN + PURCHASE IN + MUTASI IN - MUTASI OUT
                 | USED  = TOTAL - ENDING
                 |
                 | Jadi contoh TEH PUCUK:
                 | OPEN 97 + PURCHASE 96 - MUTASI OUT 48 = TOTAL 145
                 | ENDING 47
                 | USED / Usage DSC = 145 - 47 = 98
                 |
                 | $usageDaily tetap dihitung di atas sebagai referensi internal,
                 | tapi nilai yang ditampilkan harus mengikuti TOTAL - ENDING.
                 */
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
                    'actual_tepung_qty' => $actualTepung,

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
            && (string) $request->get('outlet_id') !== 'all'
            && $request->filled('start_date')
            && $request->filled('end_date');

        if (!$filterApplied) {
            $outletId = '';
            $start_date = '';
            $end_date = '';
        } else {
            $start_date = $this->normalizeDateToYmd((string) $start_date);
            $end_date = $this->normalizeDateToYmd((string) $end_date);

            if ($start_date > $end_date) {
                [$start_date, $end_date] = [$end_date, $start_date];
            }
        }

        // CACHE SAFE: master outlet dipakai untuk dropdown/grouping.
        // TTL pendek agar perubahan outlet tetap cepat terbaca, tapi tidak query ulang tiap request.
        $allOutletsRaw = Cache::remember('qcr:all_outlets_raw:v2', 300, function () {
            return DB::table('tbl_outlets')
                ->select('id', 'nama_outlet')
                ->orderBy('nama_outlet')
                ->orderBy('id')
                ->get();
        });

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

        // FIX HARGA OUTLET QCR:
        // Simpan ID outlet asli yang dipilih sebelum alias diperluas.
        // Harga harus memprioritaskan outlet yang dipilih di tab Harga Bahan Outlet,
        // bukan mengambil MAX dari semua alias, karena harga outlet bisa lebih kecil dari master/alias lain.
        $pricePrimaryOutletIds = array_values(array_unique(array_map('intval', $outletIds ?? [])));

        if (! empty($outletIds) && $outletId !== 'all') {
            // FINAL FIX: QCR juga mengikuti tbl_outlet_alias supaya outlet typo/duplicate tetap terbaca.
            $outletIds = $this->expandOutletAliasIds($outletIds);
        }

        $priceLookupOutletIds = array_values(array_unique(array_map('intval', array_merge($pricePrimaryOutletIds, $outletIds ?? []))));

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

        // Reuse hasil cache outlet di atas. Hindari query tbl_outlets kedua.
        $allOutlets = $allOutletsRaw->map(function ($outlet) {
            return (object) [
                'id' => (int) $outlet->id,
                'nama_outlet' => (string) $outlet->nama_outlet,
                'nama_outlet_display' => strtoupper(trim((string) $outlet->nama_outlet)) . ' [ID: ' . (int) $outlet->id . ']',
            ];
        });

        $outlets = $allOutlets;

        /*
        |--------------------------------------------------------------------------
        | TOTAL SALES NET UNTUK CARD SUMMARY
        |--------------------------------------------------------------------------
        | Dashboard Penjualan memakai total_omset dari tbl_laporan_bulanan.
        | Nilai ini sudah NET, sehingga Non Sales Transaction tidak ikut masuk.
        |
        | Catatan penting:
        | - Tabel QCR menu/BOM tetap dihitung dari transaksi item menu.
        | - Summary card Total Sales / Gross Profit / persentase memakai nilai NET ini.
        |--------------------------------------------------------------------------
        */
        $totalSalesBulanan = (float) DB::table('tbl_laporan_bulanan')
            ->when(!$isAllOutlet, fn ($q) => $q->whereIn('outlet_id', $outletIds))
            ->whereBetween(DB::raw('DATE(tanggal)'), [$start_date, $end_date])
            ->sum('total_omset');

        // Harga bahan
        /*
        |--------------------------------------------------------------------------
        | PATCH HARGA QCR - OUTLET KHUSUS AYAM/BERAS, SISANYA HO/MITRA
        |--------------------------------------------------------------------------
        | Logika lama tidak dihapus.
        |
        | Aturan baru:
        | 1. AYAM* dan BERAS tetap mengikuti harga bahan outlet lama
        |    dari tbl_bahan_harga_outlet. Jadi ketika user update harga bahan
        |    outlet di master lama, QCR otomatis tetap mengikuti untuk bahan ini.
        | 2. Selain AYAM* dan BERAS memakai master harga kategori baru:
        |    tbl_bahan_harga berdasarkan tbl_outlets.kategori_harga = HO/MITRA.
        | 3. Jika harga kategori belum ada, fallback tetap ke tbl_bahan.harga_bahan.
        |--------------------------------------------------------------------------
        */
        // CACHE SAFE: master bahan/harga dasar jarang berubah.
        // Harga outlet/kategori tetap dihitung dinamis setelah ini, jadi rumus QCR tidak berubah.
        $bahanPrice = Cache::remember('qcr:bahan_price_base:v2', 300, function () {
            return DB::table('tbl_bahan')
                ->select('id', 'nama_bahan', 'harga_bahan', 'satuan', 'konversi', 'isi_per_unit')
                ->orderBy('nama_bahan')
                ->get();
        })->map(function ($row) {
            return clone $row;
        });

        $isHargaOutletKhususAyamBeras = function ($namaBahan): bool {
            $nama = strtoupper(trim((string) $namaBahan));
            $nama = preg_replace('/\s+/', ' ', $nama);

            return str_contains($nama, 'AYAM') || str_contains($nama, 'BERAS');
        };

        $kategoriHargaQcr = null;

        if (!$isAllOutlet && !empty($pricePrimaryOutletIds)) {
            $kategoriHargaQcr = DB::table('tbl_outlets')
                ->whereIn('id', array_map('intval', $pricePrimaryOutletIds))
                ->whereNotNull('kategori_harga')
                ->orderByRaw("FIELD(kategori_harga, 'HO', 'MITRA')")
                ->value('kategori_harga');
        }

        $hargaKategoriByBahanId = collect();

        if (!$isAllOutlet && $kategoriHargaQcr) {
            $hargaKategoriByBahanId = DB::table('tbl_bahan_harga')
                ->select('bahan_id', 'harga')
                ->where('kategori_harga', $kategoriHargaQcr)
                ->get()
                ->keyBy(fn ($row) => (int) $row->bahan_id);
        }

        $outletPriceRows = collect();

        if (!$isAllOutlet && !empty($priceLookupOutletIds)) {
            // FIX HARGA OUTLET QCR:
            // Untuk AYAM* dan BERAS saja, tetap pakai prioritas harga outlet lama:
            // 1) outlet asli yang dipilih di filter,
            // 2) alias outlet sebagai fallback,
            // 3) harga master bahan.
            $primaryLookup = array_flip(array_map('intval', $pricePrimaryOutletIds));

            $outletPriceRows = DB::table('tbl_bahan_harga_outlet')
                ->select('id', 'outlet_id', 'bahan_id', 'harga_bahan', 'updated_at')
                ->whereIn('outlet_id', $priceLookupOutletIds)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get()
                ->sort(function ($a, $b) use ($primaryLookup) {
                    $aPrimary = isset($primaryLookup[(int) $a->outlet_id]) ? 1 : 0;
                    $bPrimary = isset($primaryLookup[(int) $b->outlet_id]) ? 1 : 0;

                    if ($aPrimary !== $bPrimary) {
                        return $bPrimary <=> $aPrimary;
                    }

                    $timeCmp = strcmp((string) ($b->updated_at ?? ''), (string) ($a->updated_at ?? ''));
                    if ($timeCmp !== 0) {
                        return $timeCmp;
                    }

                    return ((int) ($b->id ?? 0)) <=> ((int) ($a->id ?? 0));
                })
                ->groupBy('bahan_id')
                ->map(fn ($rows) => $rows->first());
        }

        foreach ($bahanPrice as $b) {
            $bahanId = (int) $b->id;

            if ($isHargaOutletKhususAyamBeras($b->nama_bahan)) {
                $outletPrice = $outletPriceRows->get($bahanId);

                if ($outletPrice && $outletPrice->harga_bahan !== null) {
                    $b->harga_bahan = (float) $outletPrice->harga_bahan;
                }

                continue;
            }

            $hargaKategori = $hargaKategoriByBahanId->get($bahanId);

            if ($hargaKategori && $hargaKategori->harga !== null) {
                $b->harga_bahan = (float) $hargaKategori->harga;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PATCH EXPORT QCR - HARGA PER BAHAN
        |--------------------------------------------------------------------------
        | Export Excel memakai object $bahanPrice yang sama dengan tampilan QCR.
        | Field harga_bahan_export dibuat eksplisit supaya blade export tidak kosong
        | dan tetap mengambil harga hasil mapping tbl_bahan_harga HO/MITRA.
        | Tidak mengubah perhitungan HPP / stock / QCR lain.
        |--------------------------------------------------------------------------
        */
        foreach ($bahanPrice as $b) {
            $b->harga_bahan_export = (float) ($b->harga_bahan ?? 0);
        }

        $priceMap = [];
        foreach ($bahanPrice as $b) {
            $priceMap[$this->normName($b->nama_bahan)] = $b;
        }

        /*
        |--------------------------------------------------------------------------
        | PATCH URUTAN BAHAN QCR
        |--------------------------------------------------------------------------
        | Urutan kolom Summary Pemakaian Bahan dibuat mengikuti urutan mapping
        | spreadsheet di tbl_bahan_mapping, bukan urutan abjad / urutan transaksi.
        | Ini hanya mengubah urutan tampilan, bukan rumus, bukan HPP, bukan stock,
        | dan bukan logika QCR lain.
        |--------------------------------------------------------------------------
        */
        $qcrBahanOrder = [];
        // CACHE SAFE: urutan mapping BOM tidak perlu query ulang setiap load QCR.
        $qcrBahanOrderRows = Cache::remember('qcr:bahan_order_rows:v2', 300, function () {
            return DB::table('tbl_bahan_mapping as bm')
                ->join('tbl_bahan as b', 'b.id', '=', 'bm.bahan_id')
                ->select(
                    'bm.id as urutan',
                    'bm.nama_sheet',
                    'b.nama_bahan'
                )
                ->orderBy('bm.id')
                ->get();
        });

        foreach ($qcrBahanOrderRows as $orderRow) {
            $urutan = (int) $orderRow->urutan;

            $namaSheetKey = $this->normName((string) $orderRow->nama_sheet);
            if ($namaSheetKey !== '' && !isset($qcrBahanOrder[$namaSheetKey])) {
                $qcrBahanOrder[$namaSheetKey] = $urutan;
            }

            $namaBahanKey = $this->normName((string) $orderRow->nama_bahan);
            if ($namaBahanKey !== '' && !isset($qcrBahanOrder[$namaBahanKey])) {
                $qcrBahanOrder[$namaBahanKey] = $urutan;
            }
        }

        $menuData = [];
        // Opsi menu khusus penukaran uang plus.
        // Dibuat dari rekap transaksi sesuai range tanggal awal-akhir agar semua jenis/tipe menu muncul.
        $qcrUangPlusMenuOptions = [];
        $bahanSummary = [];
        $visibleBahanNames = [];

        $summary = [
            'sales' => 0,
            'sales_transaksi' => 0,
            'sales_laporan_bulanan' => 0,
            'sales_net' => 0,
            'sales_gross_menu' => 0,
            'non_sales_transaction' => 0,
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
                'qcrUangPlusHistory' => collect(),
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
            ->whereBetween('sesi_tanggal', [$start_date, $end_date])
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

        $uangPlusTransaksi = collect();

        if (Schema::hasTable('tbl_qcr_uang_plus_transactions')) {
            $uangPlusTransaksi = DB::table('tbl_qcr_uang_plus_transactions')
                ->when(!$isAllOutlet, fn ($q) => $q->whereIn('outlet_id', $outletIds))
                ->whereBetween('sesi_tanggal', [$start_date, $end_date])
                ->where('source', 'UANG_PLUS')
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
        }

        $transaksi = $transaksi
            ->concat($uangPlusTransaksi)
            ->values();

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
                'qcrUangPlusHistory' => collect(),
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

        /*
        |--------------------------------------------------------------------------
        | OPSI MENU PENUKARAN UANG PLUS - ESB MASTER MENU TEMPLATE
        |--------------------------------------------------------------------------
        | Source utama sekarang tbl_menus_esb hasil sync API ESB.
        |
        | Format kebutuhan modal:
        | Nama Menu | Harga | Jenis Transaksi
        |
        | Mapping:
        | - nama_menu        = tbl_menus_esb.menu_name
        | - harga            = tbl_menus_esb.price
        | - jenis transaksi  = tbl_menus_esb.menu_template_name
        |
        | Satu menu bisa punya banyak harga/template:
        | AIR MINERAL | 4000 | TAKEAWAY
        | AIR MINERAL | 5000 | SHOPEEFOOD
        | AIR MINERAL | 5500 | GRABFOOD
        |--------------------------------------------------------------------------
        */
$qcrUangPlusMenuOptions = collect();

if (Schema::hasTable('tbl_menus_esb')) {
    $qcrUangPlusMenuOptions = DB::table('tbl_menus_esb')
        ->select(
            'menu_id',
            'menu_code',
            'menu_name',
            'menu_template_id',
            'menu_template_name',
            'price',
            'kategori_qcr',
            'flag_active'
        )
        ->whereNotNull('menu_name')
        ->whereRaw("TRIM(menu_name) <> ''")
        ->where(function ($q) {
            $q->where('flag_active', 1)
              ->orWhereNull('flag_active');
        })
        ->orderBy('menu_name')
        ->orderBy('menu_template_name')
        ->orderBy('price')
        ->get()
        ->map(function ($m) {
            $namaMenu = trim((string) ($m->menu_name ?? ''));
            $jenisTransaksi = trim((string) ($m->menu_template_name ?? 'Regular'));
            $hargaMenu = (float) ($m->price ?? 0);
            $kategori = strtoupper(trim((string) ($m->kategori_qcr ?? 'MAKANAN')));

            if ($jenisTransaksi === '') {
                $jenisTransaksi = 'Regular';
            }

            if (!in_array($kategori, ['MAKANAN', 'MINUMAN'], true)) {
                $kategori = 'MAKANAN';
            }

            return [
                'key' => implode('||', [
                    'ESB',
                    (int) ($m->menu_id ?? 0),
                    (int) ($m->menu_template_id ?? 0),
                    number_format($hargaMenu, 2, '.', ''),
                ]),
                'menu_id' => (int) ($m->menu_id ?? 0),
                'menu_code' => (string) ($m->menu_code ?? ''),
                'menu_template_id' => (int) ($m->menu_template_id ?? 0),

                'nama_menu' => $namaMenu,
                'harga' => $hargaMenu,
                'jenis_transaksi' => $jenisTransaksi,

                // tetap disediakan karena JS/blade lama pakai "tipe"
                'tipe' => $jenisTransaksi,

                'kategori' => $kategori,
                'unit_sold' => 0,
                'total_sales' => 0,
                'source' => 'esb_master',
            ];
        })
        ->filter(fn ($row) => trim((string) ($row['nama_menu'] ?? '')) !== '')
        ->values();
}

/*
 * Fallback kalau tbl_menus_esb belum ada data.
 */
if ($qcrUangPlusMenuOptions->isEmpty()) {
    $qcrUangPlusMenuOptions = collect($rekapTransaksi ?? [])
        ->map(function ($t) {
            $namaMenu = (string) ($t->item_nama ?? '');
            $jenisTransaksi = (string) ($t->tipe ?? 'Regular');
            $hargaMenu = (float) ($t->harga ?? 0);
            $unitSold = (float) ($t->total_qty ?? 0);
            $totalSales = (float) ($t->total_sales ?? ($unitSold * $hargaMenu));

            return [
                'key' => implode('||', [
                    'TRX',
                    (string) ($t->item_nama_norm ?? $this->normName($namaMenu)),
                    (string) ($t->tipe_norm ?? $this->normName($jenisTransaksi)),
                    number_format($hargaMenu, 2, '.', ''),
                ]),
                'nama_menu' => $namaMenu,
                'harga' => $hargaMenu,
                'jenis_transaksi' => $jenisTransaksi !== '' ? $jenisTransaksi : 'Regular',
                'tipe' => $jenisTransaksi !== '' ? $jenisTransaksi : 'Regular',
                'kategori' => 'MAKANAN',
                'unit_sold' => $unitSold,
                'total_sales' => $totalSales,
                'source' => 'transaksi_fallback',
            ];
        })
        ->filter(fn ($row) => trim((string) ($row['nama_menu'] ?? '')) !== '')
        ->unique('key')
        ->values();
}

$qcrUangPlusMenuOptions = $qcrUangPlusMenuOptions
    ->sortBy([
        ['nama_menu', 'asc'],
        ['harga', 'asc'],
        ['jenis_transaksi', 'asc'],
    ])
    ->values();

        /*
         * Fallback aman kalau tbl_menus_esb belum terisi.
         * Ini tidak dipakai kalau sync ESB sudah jalan.
         */
        if ($qcrUangPlusMenuOptions->isEmpty()) {
            $qcrUangPlusMenuOptions = collect($rekapTransaksi)
                ->map(function ($t) {
                    $namaMenu = (string) ($t->item_nama ?? '');
                    $jenisTransaksi = (string) ($t->tipe ?? 'Regular');
                    $hargaMenu = (float) ($t->harga ?? 0);

                    return [
                        'key' => implode('||', [
                            'TRX',
                            (string) ($t->item_nama_norm ?? $this->normName($namaMenu)),
                            (string) ($t->tipe_norm ?? $this->normName($jenisTransaksi)),
                            number_format($hargaMenu, 2, '.', ''),
                        ]),
                        'nama_menu' => $namaMenu,
                        'tipe' => $jenisTransaksi !== '' ? $jenisTransaksi : 'Regular',
                        'jenis_transaksi' => $jenisTransaksi !== '' ? $jenisTransaksi : 'Regular',
                        'harga' => $hargaMenu,
                        'kategori' => 'MAKANAN',
                        'unit_sold' => (float) ($t->total_qty ?? 0),
                        'total_sales' => (float) ($t->total_sales ?? 0),
                        'source' => 'transaksi_fallback',
                    ];
                })
                ->filter(fn ($row) => trim((string) ($row['nama_menu'] ?? '')) !== '')
                ->unique('key')
                ->values();
        }

        $qcrUangPlusMenuOptions = $qcrUangPlusMenuOptions
            ->sortBy([
                ['kategori', 'desc'],
                ['nama_menu', 'asc'],
                ['tipe', 'asc'],
                ['harga', 'asc'],
            ])
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | TOTAL SALES GROSS UNTUK TABEL MENU / BOM
        |--------------------------------------------------------------------------
        | Ini adalah total dari item menu POS.
        | Nilai ini sengaja TIDAK disamakan ke tbl_laporan_bulanan karena:
        | - BOM/Usage POS butuh quantity dan item menu asli.
        | - Non Sales Transaction tidak punya menu dan tidak punya resep BOM.
        |--------------------------------------------------------------------------
        */
        $totalSalesTransaksi = (float) $rekapTransaksi->sum('total_sales');

        $totalSalesMenuGross = $totalSalesTransaksi;
        $totalSalesNetDashboard = $totalSalesBulanan > 0
            ? $totalSalesBulanan
            : $totalSalesMenuGross;

        $nonSalesTransaction = max(0, $totalSalesMenuGross - $totalSalesNetDashboard);

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
            // Tepung Breader tidak boleh di-skip karena termasuk BOM.
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

        /*
        |--------------------------------------------------------------------------
        | KOREKSI DISPLAY SALES QCR KE NET DASHBOARD
        |--------------------------------------------------------------------------
        | Detail menu POS memang gross karena berasal dari item menu asli.
        | Dashboard memakai tbl_laporan_bulanan.total_omset yang sudah NET.
        |
        | Agar total kolom penjualan di tabel QCR saat dijumlah manual sama dengan
        | dashboard, selisih Non Sales Transaction ditampilkan sebagai 1 baris
        | koreksi negatif.
        |
        | Penting:
        | - Qty menu tidak diubah.
        | - Harga menu tidak diubah.
        | - BOM / bahan / HPP / Usage POS tidak diubah karena baris ini bahan = [].
        | - Summary tetap memakai $totalSalesNetDashboard.
        |--------------------------------------------------------------------------
        */
        if ($nonSalesTransaction > 0) {
            $menuData['__NON_SALES_TRANSACTION_ADJUSTMENT__'] = [
                'nama_menu' => 'NON SALES TRANSACTION',
                'tipe' => 'ADJUSTMENT',
                'unit_sold' => 0,
                'harga' => 0,
                'total_sales' => -1 * (float) $nonSalesTransaction,
                'total_sales_gross' => 0,
                'is_non_sales_adjustment' => true,
                'bahan' => [],
            ];
        }

        $stockRows = $this->getQcrMergedStockRows(
            $outletIds,
            $start_date,
            $end_date,
            $isAllOutlet
        );

        /*
        |--------------------------------------------------------------------------
        | PATCH SALDO UANG PLUS QCR
        |--------------------------------------------------------------------------
        | Saldo uang plus harus mengikuti data QCR yang sedang tampil.
        | $stockRows sudah hasil merge tbl_stock + tbl_stock_draft dengan prioritas draft,
        | jadi angka modal "Membelanjakan Uang Plus" tidak lagi berbeda dengan data DSC/QCR.
        |--------------------------------------------------------------------------
        */
        $totalUangPlus = $this->sumQcrUangPlusSaldo($stockRows);
        
        $qcrUangPlusHistory = collect();

        /*
         |--------------------------------------------------------------------------
         | FIX HISTORY PEMBELIAN UANG PLUS QCR - DETAIL MENU
         |--------------------------------------------------------------------------
         | Kolom history yang ditampilkan hanya:
         | Waktu, Outlet, Nama Menu, Saldo Awal, Belanja, Sisa.
         | Data diambil dari tbl_qcr_uang_plus_transactions per item/menu.
         |--------------------------------------------------------------------------
         */
        if (Schema::hasTable('tbl_qcr_uang_plus_transactions')) {
            $qcrUangPlusHistory = DB::table('tbl_qcr_uang_plus_transactions as t')
                ->leftJoin('tbl_outlets as o', 'o.id', '=', 't.outlet_id')
                ->when(!$isAllOutlet && !empty($outletIds), function ($q) use ($outletIds) {
                    $q->whereIn('t.outlet_id', $outletIds);
                })
                ->whereBetween('t.sesi_tanggal', [$start_date, $end_date])
                ->where(function ($q) {
                    $q->where('t.source', 'UANG_PLUS')
                      ->orWhere('t.tr_metode', 'UANG_PLUS');
                })
                ->whereNotNull('t.item_nama')
                ->whereRaw("TRIM(t.item_nama) <> ''")
                ->where('t.item_nama', '!=', '__TRANSACTION__')
                ->select(
                    't.id',
                    't.nomor',
                    't.outlet_id',
                    'o.nama_outlet',
                    't.sesi_tanggal',
                    't.tr_waktu',
                    't.item_nama',
                    DB::raw('COALESCE(t.payment_total, t.grand_total, t.item_sub_total, 0) as saldo_awal'),
                    DB::raw('COALESCE(t.item_sub_total, t.grand_total, 0) as total_belanja'),
                    DB::raw('GREATEST(COALESCE(t.payment_total, t.grand_total, t.item_sub_total, 0) - COALESCE(t.item_sub_total, t.grand_total, 0), 0) as saldo_sisa'),
                    DB::raw('COALESCE(t.created_at, CONCAT(t.sesi_tanggal, " ", COALESCE(t.tr_waktu, "00:00:00"))) as created_at')
                )
                ->orderByDesc('t.sesi_tanggal')
                ->orderByDesc('t.tr_waktu')
                ->orderByDesc('t.id')
                ->limit(100)
                ->get();
        }

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

        /*
         |--------------------------------------------------------------------------
         | FIX BOM TEPUNG BREADER
         |--------------------------------------------------------------------------
         | Tepung Breader adalah bahan BOM seperti bahan lain.
         | Jangan dibuang dari stock aggregation, karena harus ikut:
         | - Usage POS
         | - Usage DSC
         | - Difference
         | - Prediksi Stock
         | - HPP
         */
        $displayOutletId = (int) ($pricePrimaryOutletIds[0] ?? $outletIds[0] ?? 0);
        $qcrBahanIdsForOpening = $bahanIdsInRange->map(fn ($id) => (int) $id)->values()->all();

        $openingBulkMapQcr = $this->getOpeningBulkFromAliasIds(
            $outletIds,
            $displayOutletId,
            $qcrBahanIdsForOpening,
            $start_date
        );

        /*
         |--------------------------------------------------------------------------
         | FIX STOCK AVAILABLE QCR IKUT DSC PERIODE AKHIR
         |--------------------------------------------------------------------------
         | Baris Stock (Available) di Summary QCR harus sama konsepnya dengan OPEN DSC:
         | ambil ending stock tanggal sebelumnya dari tanggal aktif/periode akhir.
         |
         | Contoh range 28-05 s/d 15-06:
         | Stock Available = ending 14-06, bukan opening 28-05.
         |
         | Map ini hanya dipakai untuk tampilan Stock Available dan prediksi.
         | Perhitungan Usage DSC range tetap memakai aggregator periode yang sudah ada.
         |--------------------------------------------------------------------------
         */
        $stockAvailableBulkMapQcr = $this->getOpeningBulkFromAliasIds(
            $outletIds,
            $displayOutletId,
            $qcrBahanIdsForOpening,
            $end_date
        );

        $stockAggByBahanId = $this->buildQcrStockAggByBahanId($stockRows, $outletIds, $displayOutletId, $openingBulkMapQcr, $start_date, $end_date);

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

            $stockAvailableForDisplay = 0.0;
            if ($agg) {
                $aggBahanId = (int) ($agg->bahan_id ?? 0);
                $stockAvailableForDisplay = (float) (
                    $stockAvailableBulkMapQcr[$aggBahanId][1]
                    ?? $agg->stock_available
                    ?? $agg->ending
                    ?? $available
                );
            }

            $wasteProduct = (float) ($agg->waste_product_qty ?? 0);
            $wasteBahan   = (float) ($agg->waste_bahan_qty ?? 0);
            $wasteTepung  = (float) ($agg->waste_tepung_qty ?? 0);
            $actualTepung = (float) ($agg->actual_tepung_qty ?? 0);

            $wasteQty = $wasteProduct + $wasteBahan + $wasteTepung;

            $bahanSummary[$namaBahan] = [
                'qty_resep'       => 0,
                'hpp'             => 0,
                'harga'           => $hargaPerBase,
                'satuan'          => $agg->bahan_satuan ?? ($priceRow->satuan ?? null),
                // Stock (Available) = ending stock tanggal sebelumnya dari periode akhir, sama seperti OPEN DSC.
                'stock'           => $stockAvailableForDisplay,
                'stock_available' => $stockAvailableForDisplay,
                'qty_stock'       => $usageStock,
                'opening_stock'   => (float) ($agg->opening ?? 0),
                'ending_stock'    => (float) ($agg->ending ?? 0),
                'total_available' => (float) ($agg->total_available ?? 0),
                'waste_product_qty' => $wasteProduct,
                'waste_bahan_qty'   => $wasteBahan,
                'waste_tepung_qty'  => $wasteTepung,
                'actual_tepung_qty' => $actualTepung,
                'waste_qty'         => $this->normalizeQcrDisplayQty(
                    $wasteQty,
                    (string) ($agg->bahan_satuan ?? ($priceRow->satuan ?? ''))
                ),
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
                        'actual_tepung_qty' => 0,
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
        /*
         |--------------------------------------------------------------------------
         | FIX BOM TEPUNG BREADER
         |--------------------------------------------------------------------------
         | Jangan hapus Tepung Breader dari bahanSummary.
         */
        /*
         * PATCH URUTAN BAHAN QCR:
         * bahanSummary dan visibleBahanNames diurutkan mengikuti tbl_bahan_mapping.
         * Bahan yang tidak ada di mapping tetap ditampilkan di bagian akhir.
         */
        $bahanSummary = collect($bahanSummary)
            ->sortBy(function ($row, $namaBahan) use ($qcrBahanOrder) {
                $namaKey = $this->normName((string) $namaBahan);
                $urutan = $qcrBahanOrder[$namaKey] ?? 999999;

                return str_pad((string) $urutan, 6, '0', STR_PAD_LEFT) . '_' . $namaKey;
            })
            ->toArray();

        /*
         * Tepung Breader tetap masuk visibleBahanNames untuk Summary Pemakaian Bahan.
         * Jika ada tampilan detail menu yang mau hide Tepung, lakukan di Blade,
         * bukan di pipeline data.
         */
        $visibleBahanNames = array_values($visibleBahanNames);

        usort($visibleBahanNames, function ($a, $b) use ($qcrBahanOrder) {
            $aKey = $this->normName((string) $a);
            $bKey = $this->normName((string) $b);

            $aOrder = $qcrBahanOrder[$aKey] ?? 999999;
            $bOrder = $qcrBahanOrder[$bKey] ?? 999999;

            if ($aOrder !== $bOrder) {
                return $aOrder <=> $bOrder;
            }

            return strcmp($aKey, $bKey);
        });

$totalHpp = array_sum(array_map(fn ($r) => (float) ($r['hpp'] ?? 0), $bahanSummary));

        /*
        |--------------------------------------------------------------------------
        | SUMMARY SALES FINAL
        |--------------------------------------------------------------------------
        | Summary utama harus mengikuti Dashboard Penjualan / net sales.
        | Jangan pakai $totalSalesTransaksi untuk summary, karena itu gross item menu.
        |--------------------------------------------------------------------------
        */
        $totalSales = $totalSalesNetDashboard;
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

        /*
         |--------------------------------------------------------------------------
         | FIX WASTE CARD QCR
         |--------------------------------------------------------------------------
         | Card Waste harus sama dengan baris "Waste Rp" di Summary Pemakaian Bahan.
         | Sebelumnya $totalWasteMoney dihitung dari $stockAggByBahanId, sehingga bisa
         | memasukkan bahan yang tidak tampil di tabel QCR. Akibatnya card Waste
         | berbeda dengan total detail yang user lihat.
         |
         | Source final disamakan ke $bahanSummary['waste_rp'], karena field itu juga
         | yang dipakai Blade pada baris Waste Rp.
         |--------------------------------------------------------------------------
         */
        $totalWasteMoney = array_sum(array_map(function ($row) {
            return (float) ($row['waste_rp'] ?? 0);
        }, $bahanSummary));

        $totalSelisihAbsoluteNormal = $totalSelisihLoss + $totalSelisihGain;

        $qualityCostNormal = $totalWasteMoney + $totalSelisihAbsoluteNormal;

        $summaryNormal = [
            'sales' => $totalSales,
            'sales_transaksi' => $totalSalesTransaksi,
            'sales_laporan_bulanan' => $totalSalesBulanan,
            'sales_net' => $totalSalesNetDashboard,
            'sales_gross_menu' => $totalSalesMenuGross,
            'non_sales_transaction' => $nonSalesTransaction,
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

            $diffQtyRaw = ($qtyStock - $wasteQty) - $qtyResep;

            // Sesuaikan qty selisih dengan satuan.
            // PCS/LBR/CUP/BOTOL/SACHET/PACK tidak boleh decimal.
            $diffQty = $this->normalizeQcrDifferenceQty($diffQtyRaw, $satuan);

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

            /*
             * FIX RESTORE HAPUS +/-:
             * Jangan langsung continue saat visible_qty = 0.
             * Item yang sudah di-hide penuh tetap harus dikirim ke modal agar tombol
             * "Tampilkan Semua" atau Reset per item bisa menampilkan data kembali.
             */
            $visibleSignedQtyRaw = $calc['visible_signed_qty'] ?? (
                $diffQty < 0 ? -1 * ($calc['visible_qty'] ?? 0) : ($calc['visible_qty'] ?? 0)
            );

            $qtyRawDisplay = $this->normalizeQcrDisplayQty((float) ($calc['raw_abs'] ?? 0), $satuan);
            $qtyHiddenDisplay = $this->normalizeQcrDisplayQty((float) ($calc['hidden_qty'] ?? 0), $satuan);
            $qtyVisibleAbsDisplay = $this->normalizeQcrDisplayQty((float) ($calc['visible_qty'] ?? 0), $satuan);
            $qtyVisibleSignedDisplay = $this->normalizeQcrDisplayQty((float) $visibleSignedQtyRaw, $satuan);

            // Kalau visible = 0, item tetap masuk modal sebagai hidden agar bisa di-restore.
            $isFullyHiddenForModal = $qtyVisibleAbsDisplay <= 0;

            $row['diff_raw_qty'] = $diffQty;
            $row['diff_hidden_qty'] = $qtyHiddenDisplay;
            $row['diff_visible_qty'] = $qtyVisibleAbsDisplay;
            $row['diff_visible_signed_qty'] = $qtyVisibleSignedDisplay;
            $row['diff_nominal_visible'] = round($qtyVisibleAbsDisplay * $harga);
            $row['diff_status'] = $qtyVisibleAbsDisplay > 0 ? 'visible' : 'hidden';

            $item = [
                'reference_key'   => $referenceKey,
                'reference_name'  => $namaBahan,
                'qty_raw'         => $diffQty,
                'qty_raw_before_normalize' => $diffQtyRaw ?? $diffQty,
                'qty_abs'         => $qtyRawDisplay,
                'qty_hidden'      => $qtyHiddenDisplay,
                'qty_visible'     => $qtyVisibleSignedDisplay,
                'qty_visible_abs' => $qtyVisibleAbsDisplay,
                // Rupiah di modal Hapus +/- tidak boleh membawa sen/desimal.
                'nominal'         => round($qtyVisibleAbsDisplay * $harga),
                'nominal_raw'     => round($qtyRawDisplay * $harga),
                'satuan'          => $satuan,
                'status'          => $isFullyHiddenForModal ? 'hidden' : 'visible',
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

            $nominalVisible = round($visibleSignedQty * $harga);

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
            'sales_net' => $totalSalesNetDashboard,
            'sales_gross_menu' => $totalSalesMenuGross,
            'non_sales_transaction' => $nonSalesTransaction,
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
        |
        | FIX LPG / OPERASIONAL:
        | - Penentuan Non-BOM harus berdasarkan master BOM asli tbl_menu_bahan.
        | - Jangan menganggap bahan masuk BOM hanya karena sudah ada di Summary QCR
        |   / mapping / stock summary.
        | - Contoh LPG: tidak ada resep menu/BOM, tapi ada di stock DSC, maka wajib
        |   tampil di QCR Bahan Non-BOM / Operasional.
        | - Bahan operasional seperti LPG/GAS/ELPIJI tetap ditampilkan walaupun nilai
        |   stock/usage/waste sedang 0, selama bahan tersebut ada di data stock DSC.
        | - Tidak mengubah rumus QCR utama.
        |
        | FIX NON-BOM VS DSC REPORT:
        | - Non-BOM harus mengikuti REKAP DSC tanggal akhir filter.
        | - Contoh Minyak Goreng di DSC Report: TOTAL 57 - ENDING 55 = USED 2.
        | - Maka Usage DSC Non-BOM harus 2, bukan akumulasi range seperti 77,10.
        */
        $nonBomStockRows = $this->getQcrMergedStockRows(
            $outletIds,
            $end_date,
            $end_date,
            $isAllOutlet
        );

        $nonBomBahanIds = $nonBomStockRows->pluck('bahan_id')->unique()->filter()->values();

        $nonBomOpeningBulkMapQcr = [];
        if ($nonBomBahanIds->isNotEmpty()) {
            $nonBomOpeningBulkMapQcr = $this->getOpeningBulkFromAliasIds(
                $outletIds,
                $displayOutletId,
                $nonBomBahanIds->map(fn ($id) => (int) $id)->values()->all(),
                $end_date
            );
        }

        $nonBomStockAggByBahanId = $this->buildQcrStockAggByBahanId(
            $nonBomStockRows,
            $outletIds,
            $displayOutletId,
            $nonBomOpeningBulkMapQcr,
            $end_date,
            $end_date
        );

        $bomBahanNorms = DB::table('tbl_menu_bahan as mb')
            ->join('tbl_bahan as b', 'b.id', '=', 'mb.bahan_id')
            ->select('b.nama_bahan')
            ->distinct()
            ->get()
            ->map(fn ($row) => $this->normName((string) ($row->nama_bahan ?? '')))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $bahanNonBomRows = [];
        $totalNonBomLoss = 0.0;

        foreach ($nonBomStockAggByBahanId as $bahanId => $agg) {
            $namaBahan = (string) ($agg->bahan_nama ?? '');
            $namaNorm = $this->normName($namaBahan);

            // Hanya bahan yang benar-benar masuk master BOM/menu yang tidak tampil di Non-BOM.
            // Jangan pakai $visibleBahanNames / $bahanSummary sebagai dasar skip,
            // karena bahan operasional seperti LPG bisa ikut mapping/summary tapi bukan BOM.
            if (in_array($namaNorm, $bomBahanNorms, true)) {
                continue;
            }

            $isOperationalForced = str_contains($namaNorm, 'lpg')
                || str_contains($namaNorm, 'elpiji')
                || str_contains($namaNorm, 'gas');

            $priceRow = $priceMap[$namaNorm] ?? null;

            $hargaPerBase = 0.0;
            if ($priceRow) {
                $isi = max((float) ($priceRow->isi_per_unit ?? 1), 1);
                $kon = max((float) ($priceRow->konversi ?? 1), 1);
                $hargaPerBase = ((float) ($priceRow->harga_bahan ?? 0) / $isi) / $kon;
            }

            // Stock Non-BOM mengikuti ending aktual tanggal akhir filter.
            // Usage DSC mengikuti USED REKAP DSC tanggal akhir filter.
            $stock = (float) ($agg->ending ?? $agg->stock_available ?? $agg->available ?? 0);
            $usageDsc = (float) ($agg->usage_stock ?? 0);

            $wasteProduct = (float) ($agg->waste_product_qty ?? 0);
            $wasteBahan = (float) ($agg->waste_bahan_qty ?? 0);
            $wasteTepung = (float) ($agg->waste_tepung_qty ?? 0);
            $wasteQty = $wasteProduct + $wasteBahan + $wasteTepung;

            // Karena Non-BOM tidak punya Usage POS/BOM, selisih dinilai dari usage DSC net.
            $usageDscNet = $usageDsc - $wasteQty;
            $selisihQty = $usageDscNet;

            // Bahan non-BOM yang stock, usage, waste, dan selisih semuanya 0 tetap disembunyikan,
            // kecuali bahan operasional penting seperti LPG/GAS/ELPIJI yang memang harus dimonitor.
            if (
                ! $isOperationalForced &&
                abs($stock) < 0.00001 &&
                abs($usageDsc) < 0.00001 &&
                abs($wasteQty) < 0.00001 &&
                abs($selisihQty) < 0.00001
            ) {
                continue;
            }

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

            $satuanNonBom = (string) ($agg->bahan_satuan ?? ($priceRow->satuan ?? '-'));

            $bahanNonBomRows[] = [
                'bahan_id' => (int) $bahanId,
                'nama_bahan' => $namaBahan,
                'satuan' => $satuanNonBom,
                'stock' => $this->normalizeQcrDisplayQty($stock, $satuanNonBom),
                'usage_dsc' => $this->normalizeQcrDisplayQty($usageDsc, $satuanNonBom),
                'waste_qty' => $this->normalizeQcrDisplayQty($wasteQty, $satuanNonBom),
                'selisih_qty' => $this->normalizeQcrDisplayQty($selisihQty, $satuanNonBom),
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

        // FIX QCR: ringkasan utama harus tetap data NORMAL saat halaman pertama dibuka.
        // Angka setelah hide hanya dipakai saat user klik tombol Hapus PLUS/MINUS / melihat card setelah hide.
        // Jadi Selisih Persediaan (Loss) tidak otomatis berubah karena hidden tersimpan.
        $summary = $summaryNormal;

        return compact(
            'menuData',
            'qcrUangPlusMenuOptions',
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
            'qcrUangPlusHistory',
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
    /*
    |--------------------------------------------------------------------------
    | PERMISSION DRIVEN
    |--------------------------------------------------------------------------
    | Tidak ada lagi role yang dipaksa hanya boleh Harga Bahan Outlet.
    | Tab dan tombol tetap mengikuti role_permissions di Blade + middleware route.
    | Method ini dipertahankan agar variable $hargaOutletOnly di dataqcr tetap aman.
    |--------------------------------------------------------------------------
    */
    return false;
}

private function abortIfHargaOutletOnlyRole(): void
{
    /*
    |--------------------------------------------------------------------------
    | LEGACY NO-OP
    |--------------------------------------------------------------------------
    | Dulu method ini abort untuk role spv/tm_manager.
    | Sekarang tidak boleh hardcode role. Akses CRUD dicek dari permission.
    |--------------------------------------------------------------------------
    */
    return;
}

private function currentUserRole(): string
{
    return strtolower(trim((string) (auth()->user()->role ?? '')));
}

private function controllerHasPermission(string $permission): bool
{
    if (! auth()->check()) {
        return false;
    }

    $role = $this->currentUserRole();

    if ($role === 'superadmin') {
        return true;
    }

    if ($role === '') {
        return false;
    }

    $permissions = Cache::remember("role_permissions:{$role}", now()->addMinutes(5), function () use ($role) {
        return DB::table('role_permissions')
            ->whereRaw('LOWER(TRIM(role)) = ?', [$role])
            ->pluck('permission')
            ->map(fn ($permission) => trim((string) $permission))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    });

    return in_array(trim($permission), $permissions, true);
}

private function assertPermission(string $permission): void
{
    if (! $this->controllerHasPermission($permission)) {
        abort(403, "Anda tidak memiliki akses untuk permission: {$permission}");
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
    $this->assertPermission('menu.store');

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
    $this->assertPermission('menu.update');

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
    $this->assertPermission('menu.destroy');

    DB::transaction(function () use ($id) {
        DB::table('tbl_menu_bahan')->where('menu_id', $id)->delete();
        DB::table('tbl_menu')->where('id', $id)->delete();
    });

    return back()->with('success', 'Menu berhasil dihapus.');
}

// ---------------- BAHAN ----------------
public function storeBahan(Request $request)
{
    $this->assertPermission('bahan.store');

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
    $this->assertPermission('bahan.update');

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
    $this->assertPermission('bahan.destroy');

    DB::table('tbl_bahan')->where('id', $id)->delete();

    return back()->with('success', 'Bahan berhasil dihapus.');
}

// ---------------- BAHAN DSC ----------------
public function storeBahanDsc(Request $request)
{
    $this->assertPermission('bahan-dsc.store');

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
    $this->assertPermission('bahan-dsc.update');

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
    $this->assertPermission('bahan-dsc.destroy');

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
    $this->assertPermission('bum.store');

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
    $this->assertPermission('bum.update');

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
    $this->assertPermission('bum.destroy');

    DB::table('tbl_menu_bahan')
        ->where('menu_id', (int) $menu_id)
        ->delete();

    return back()->with('success', 'BOM untuk menu berhasil dihapus.');
}

public function getMenuBahan($menu_id)
{
    // $this->assertPermission('bum.detail'); // matikan dulu

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
    $this->assertPermission('bum.destroy');

    DB::table('tbl_menu_bahan')->where('menu_id', $menu_id)->delete();

    return back()->with('success', 'BOM untuk menu berhasil dihapus.');
}

// ---------------- STOCK ----------------
public function storeStock(Request $request)
{
    $this->assertPermission('stock.store');

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
    $this->assertPermission('stock.edit');

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
    $this->assertPermission('stock.update');

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
    $this->assertPermission('stock.destroy');

    DB::table('tbl_stock')->where('id', $id)->delete();

    return back()->with('success', 'Stock berhasil dihapus.');
}

public function stockList(Request $request)
{
    $this->assertPermission('stock.edit');

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

    // DSC TRIAL DUPLIKAT FORM SETORAN OMSET
    public function normalizeAmount($value): float
    {
        if ($value === null) {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $s = trim((string) $value);
        if ($s === '') {
            return 0.0;
        }

        $s = str_replace(['Rp', 'rp', ' ', '.'], '', $s);
        $s = str_replace(',', '.', $s);

        return is_numeric($s) ? (float) $s : 0.0;
    }

    public function importBcaItems(array $items): array
    {
        $inserted = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $tanggal = Carbon::parse($item['tanggal'])->format('Y-m-d');
            $nominal = $this->normalizeAmount($item['nominal'] ?? 0);
            $tipe = strtoupper((string) ($item['tipe'] ?? 'CR'));
            $referenceNo = $item['reference_no'] ?? null;
            $description = $item['description'] ?? null;

            $exists = DB::selectOne(
                "SELECT id FROM tbl_bca_mutasi
                 WHERE tanggal = ?
                   AND nominal = ?
                   AND COALESCE(reference_no, '') = COALESCE(?, '')
                 LIMIT 1",
                [$tanggal, $nominal, $referenceNo]
            );

            if ($exists) {
                $skipped++;
                continue;
            }

            DB::insert(
                "INSERT INTO tbl_bca_mutasi
                    (tanggal, nominal, tipe, reference_no, description, raw_payload, is_matched, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), NOW())",
                [
                    $tanggal,
                    $nominal,
                    $tipe,
                    $referenceNo,
                    $description,
                    json_encode($item, JSON_UNESCAPED_UNICODE),
                ]
            );

            $inserted++;
        }

        return [
            'inserted' => $inserted,
            'skipped' => $skipped,
        ];
    }

    public function matchSetoran(
        int $outletId,
        string $tanggalSetor,
        float $nominal,
        ?string $kodeRef = null
    ): array {
        $tanggalSetor = Carbon::parse($tanggalSetor)->format('Y-m-d');
        $start = Carbon::parse($tanggalSetor)->subDays(1)->format('Y-m-d');
        $end = Carbon::parse($tanggalSetor)->addDays(2)->format('Y-m-d');
        $kodeRef = trim((string) $kodeRef) ?: null;

        $candidates = DB::select(
            "SELECT *
             FROM tbl_bca_mutasi
             WHERE tipe = 'CR'
               AND is_matched = 0
               AND tanggal BETWEEN ? AND ?
             ORDER BY tanggal ASC, id ASC",
            [$start, $end]
        );

        if (! $candidates) {
            return [
                'status' => 'BELUM_ADA_BCA',
                'bca_mutasi_id' => null,
                'reference_no' => $kodeRef,
                'selisih' => $nominal,
                'score' => 0,
                'note' => 'Tidak ada mutasi BCA masuk pada rentang H-1 sampai H+2.',
            ];
        }

        $best = null;
        $sameNominalCount = 0;

        foreach ($candidates as $bca) {
            $bcaNominal = (float) $bca->nominal;
            $selisih = abs($bcaNominal - $nominal);
            $score = 0;
            $notes = [];

            if ($selisih == 0.0) {
                $score += 60;
                $sameNominalCount++;
                $notes[] = 'Nominal sama';
            } elseif ($selisih <= 1000) {
                $score += 25;
                $notes[] = 'Nominal beda kecil';
            }

            $dateDiff = abs(Carbon::parse($tanggalSetor)->diffInDays(Carbon::parse($bca->tanggal), false));
            if ($dateDiff === 0) {
                $score += 20;
                $notes[] = 'Tanggal sama';
            } elseif ($dateDiff <= 2) {
                $score += 10;
                $notes[] = 'Tanggal dekat';
            }

            if ($kodeRef) {
                $needle = strtolower($kodeRef);
                $ref = strtolower((string) $bca->reference_no);
                $desc = strtolower((string) $bca->description);

                if ($ref === $needle || str_contains($desc, $needle)) {
                    $score += 40;
                    $notes[] = 'Kode reference cocok';
                }
            }

            if (! $best || $score > $best['score']) {
                $best = [
                    'bca' => $bca,
                    'score' => $score,
                    'selisih' => $selisih,
                    'note' => implode(', ', $notes),
                ];
            }
        }

        if (! $best || $best['score'] < 40) {
            return [
                'status' => 'BELUM_ADA_BCA',
                'bca_mutasi_id' => null,
                'reference_no' => $kodeRef,
                'selisih' => $nominal,
                'score' => $best['score'] ?? 0,
                'note' => 'Ada mutasi BCA, tetapi tidak cukup kuat untuk dicocokkan.',
            ];
        }

        if ($sameNominalCount > 1 && ! $kodeRef) {
            $status = 'DUPLIKAT';
            $note = 'Nominal sama ditemukan lebih dari satu. Isi kode reference agar tidak salah match.';
        } elseif ($best['score'] >= 100) {
            $status = 'MATCH_STRONG';
            $note = $best['note'];
        } elseif ($best['selisih'] == 0.0) {
            $status = 'MATCH_NOMINAL';
            $note = $best['note'];
        } else {
            $status = 'SELISIH';
            $note = $best['note'];
        }

        return [
            'status' => $status,
            'bca_mutasi_id' => $best['bca']->id,
            'reference_no' => $best['bca']->reference_no ?: $kodeRef,
            'selisih' => $best['selisih'],
            'score' => $best['score'],
            'note' => $note,
        ];
    }

    public function applyToOmsetRow(int $rowId, int $shift): array
    {
        $row = DB::selectOne(
            "SELECT * FROM tbl_dsc_omset_setoran WHERE id = ? LIMIT 1",
            [$rowId]
        );

        if (! $row) {
            return [
                'ok' => false,
                'message' => 'Data omset/setoran tidak ditemukan.',
            ];
        }

        $prefix = $shift === 1 ? 's1' : 's2';
        $tanggalSetor = $row->{$prefix . '_tanggal_setor'} ?? null;
        $nominal = $this->normalizeAmount($row->{$prefix . '_sudah_disetor'} ?? 0);
        $kodeRef = $row->{$prefix . '_bca_ref'} ?? null;

        if (! $tanggalSetor || $nominal <= 0) {
            DB::update(
                "UPDATE tbl_dsc_omset_setoran
                 SET {$prefix}_rekon_status = 'PENDING',
                     {$prefix}_rekon_selisih = 0,
                     {$prefix}_rekon_note = 'Tanggal setor atau nominal belum lengkap',
                     updated_at = NOW()
                 WHERE id = ?",
                [$rowId]
            );

            return [
                'ok' => false,
                'message' => 'Tanggal setor atau nominal belum lengkap.',
            ];
        }

        $result = $this->matchSetoran(
            (int) $row->outlet_id,
            $tanggalSetor,
            $nominal,
            $kodeRef
        );

        DB::update(
            "UPDATE tbl_dsc_omset_setoran
             SET {$prefix}_bca_ref = ?,
                 {$prefix}_bca_mutasi_id = ?,
                 {$prefix}_rekon_status = ?,
                 {$prefix}_rekon_selisih = ?,
                 {$prefix}_rekon_note = ?,
                 updated_at = NOW()
             WHERE id = ?",
            [
                $result['reference_no'],
                $result['bca_mutasi_id'],
                $result['status'],
                $result['selisih'],
                $result['note'],
                $rowId,
            ]
        );

        if ($result['bca_mutasi_id'] && in_array($result['status'], ['MATCH_STRONG', 'MATCH_NOMINAL'], true)) {
            DB::update(
                "UPDATE tbl_bca_mutasi
                 SET is_matched = 1,
                     matched_table = 'tbl_dsc_omset_setoran',
                     matched_row_id = ?,
                     matched_shift = ?,
                     updated_at = NOW()
                 WHERE id = ?",
                [$rowId, $shift, $result['bca_mutasi_id']]
            );
        }

        return [
            'ok' => true,
            'result' => $result,
        ];
    }

    public function listRekon(?string $tanggal = null, ?int $outletId = null): array
    {
        $tanggal = $tanggal ?: date('Y-m-d');

        $params = [$tanggal];
        $whereOutlet = '';

        if ($outletId) {
            $whereOutlet = ' AND o.outlet_id = ?';
            $params[] = $outletId;
        }

        return DB::select(
            "SELECT
                o.id,
                o.outlet_id,
                o.tanggal,
                o.pic,

                o.s1_sudah_disetor,
                o.s1_tanggal_setor,
                o.s1_bca_ref,
                o.s1_bca_mutasi_id,
                o.s1_rekon_status,
                o.s1_rekon_selisih,
                o.s1_rekon_note,
                b1.tanggal AS s1_bca_tanggal,
                b1.nominal AS s1_bca_nominal,
                b1.reference_no AS s1_bca_reference,
                b1.description AS s1_bca_description,

                o.s2_sudah_disetor,
                o.s2_tanggal_setor,
                o.s2_bca_ref,
                o.s2_bca_mutasi_id,
                o.s2_rekon_status,
                o.s2_rekon_selisih,
                o.s2_rekon_note,
                b2.tanggal AS s2_bca_tanggal,
                b2.nominal AS s2_bca_nominal,
                b2.reference_no AS s2_bca_reference,
                b2.description AS s2_bca_description
             FROM tbl_dsc_omset_setoran o
             LEFT JOIN tbl_bca_mutasi b1 ON b1.id = o.s1_bca_mutasi_id
             LEFT JOIN tbl_bca_mutasi b2 ON b2.id = o.s2_bca_mutasi_id
             WHERE DATE(o.tanggal) = ? {$whereOutlet}
             ORDER BY o.outlet_id ASC, o.id ASC",
            $params
        );
    }
/*
|--------------------------------------------------------------------------
| TEMPEL METHOD INI DI DALAM CLASS QCRController
|--------------------------------------------------------------------------
| Jangan tempel tag <?php jika file QCRController Anda sudah ada.
| Pastikan di atas controller sudah ada:
| use App\Services\BcaRekonSqlService;
*/

public function index2(Request $request, BcaRekonSqlService $service)
{
    $startDate = $this->normalizeDateToYmd((string) $request->get('start_date', $request->get('tanggal', date('Y-m-d'))));
    $endDate = $this->normalizeDateToYmd((string) $request->get('end_date', $startDate));

    if ($startDate > $endDate) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }

    $outletId = $request->get('outlet_id', '');
    $outletIds = $request->get('outlet_ids', '');
    $statusFilter = $request->get('status', '');
    $providerFilter = $request->get('provider', '');

    $hasFilter = $request->hasAny([
        'tanggal',
        'start_date',
        'end_date',
        'outlet_id',
        'outlet_ids',
        'status',
        'provider',
    ]);

    return view('Investor.Inventory.dscFormulirOmset2', [
        'tanggal' => $startDate,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'outletId' => $outletId,
        'outletIds' => $outletIds,
        'statusFilter' => $statusFilter,
        'providerFilter' => $providerFilter,
        'rows' => $hasFilter
            ? $service->listRekon($startDate, $endDate, $outletId, [
                'outlet_ids' => $outletIds,
                'status' => $statusFilter,
                'provider' => $providerFilter,
            ])
            : collect(),
        'outlets' => $service->listOutlets(),
        'providers' => $service->listProviders(),
        'vaMappings' => $service->listVaMappings(),
        'hasFilter' => $hasFilter,
    ]);
}

public function importManual(Request $request, BcaRekonSqlService $service)
{
    $request->validate([
        'tanggal' => 'required|date',
        'nominal' => 'required',
        'tipe' => 'nullable|string|max:20',
        'reference_no' => 'nullable|string|max:191',
        'description' => 'nullable|string',
        'provider' => 'nullable|string|max:50',
        'va_number' => 'nullable|string|max:100',
    ]);

    $result = $service->importManual($request->all());

    return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
}

public function importApi(Request $request, BcaRekonSqlService $service)
{
    // Kosong dulu untuk Xendit/API. Tidak ada proses simpan.
    return response()->json([
        'ok' => true,
        'message' => 'Import API/Xendit belum diaktifkan. Gunakan input payment manual dulu.',
        'inserted' => 0,
        'skipped' => 0,
    ]);
}

public function updateRef(Request $request, BcaRekonSqlService $service)
{
    $request->validate([
        'row_id' => 'required|integer',
        'shift' => 'required|integer|in:1,2',
        'bca_ref' => 'nullable|string|max:191',
    ]);

    $result = $service->updateRef(
        (int) $request->row_id,
        (int) $request->shift,
        $request->bca_ref
    );

    return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
}

public function matchOne(Request $request, BcaRekonSqlService $service)
{
    $request->validate([
        'row_id' => 'required|integer',
        'shift' => 'required|integer|in:1,2',
    ]);

    $result = $service->matchOne((int) $request->row_id, (int) $request->shift);

    return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
}

public function matchAll(Request $request, BcaRekonSqlService $service)
{
    $request->validate([
        'tanggal' => 'nullable|date',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date',
        'outlet_id' => 'nullable',
        'outlet_ids' => 'nullable|string',
    ]);

    $startDate = (string) ($request->start_date ?: $request->tanggal ?: date('Y-m-d'));
    $endDate = (string) ($request->end_date ?: $startDate);

    $result = $service->matchAll($startDate, $request->outlet_id, $endDate, [
        'outlet_ids' => $request->outlet_ids,
    ]);

    return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
}

public function saveVaMapping(Request $request, BcaRekonSqlService $service)
{
    $request->validate([
        'outlet_id' => 'required|integer',  
        'shift' => 'nullable|in:1,2',
        'provider' => 'required|string|max:50',
        'va_number' => 'required|string|max:100',
        'va_name' => 'nullable|string|max:191',
        'is_active' => 'nullable',
    ]);

    $data = $request->all();
    $data['is_active'] = $request->has('is_active') ? 1 : 0;

    $result = $service->saveVaMapping($data);

    return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
}

public function paymentVaWebhook(Request $request, BcaRekonSqlService $service)
{
    // Kosong dulu untuk Xendit. Service hanya return stub, tidak simpan apa pun.
    $result = $service->handleVaWebhook($request->all());

    return response()->json($result, $result['ok'] ? 200 : 422);
}

}
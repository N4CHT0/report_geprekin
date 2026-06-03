<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jimmyjs\ReportGenerator\Facades\ExcelReportFacade as ExcelReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Writer;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use App\Imports\ExpenseImport;
use Illuminate\Http\Client\PendingRequest;

use App\Services\Esb\EsbClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Services\EsbAuthService;
use App\Services\EsbLedgerService;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Maatwebsite\Excel\Concerns\WithEvents;
use App\Jobs\SyncPnlLiveAllBranchesJob;

class InvestorReportController extends Controller
{
    private function cleanOutletName(?string $namaOutlet): string
    {
        $name = trim(str_ireplace('Express', '', (string) ($namaOutlet ?? 'Tidak Diketahui')));
        $name = preg_replace('/\s+/', ' ', $name);

        return $name ?: 'Tidak Diketahui';
    }

    private function outletGroupKey(?string $namaOutlet): string
    {
        return strtoupper($this->cleanOutletName($namaOutlet));
    }

    private function detectProvinsiFromAlamat(?string $alamat): string
    {
        $alamat = strtoupper((string) $alamat);

        $provinsiList = [
            'KEPULAUAN BANGKA BELITUNG',
            'BANGKA BELITUNG',
            'KEPULAUAN RIAU',
            'SUMATERA UTARA',
            'SUMATERA BARAT',
            'SUMATERA SELATAN',
            'JAWA BARAT',
            'JAWA TENGAH',
            'JAWA TIMUR',
            'DKI JAKARTA',
            'JAKARTA',
            'DI YOGYAKARTA',
            'YOGYAKARTA',
            'NUSA TENGGARA BARAT',
            'NUSA TENGGARA TIMUR',
            'KALIMANTAN BARAT',
            'KALIMANTAN TENGAH',
            'KALIMANTAN SELATAN',
            'KALIMANTAN TIMUR',
            'KALIMANTAN UTARA',
            'SULAWESI UTARA',
            'SULAWESI TENGAH',
            'SULAWESI SELATAN',
            'SULAWESI TENGGARA',
            'SULAWESI BARAT',
            'MALUKU UTARA',
            'PAPUA BARAT DAYA',
            'PAPUA BARAT',
            'PAPUA TENGAH',
            'PAPUA PEGUNUNGAN',
            'PAPUA SELATAN',
            'ACEH',
            'RIAU',
            'JAMBI',
            'BENGKULU',
            'LAMPUNG',
            'BANTEN',
            'BALI',
            'NTB',
            'NTT',
            'GORONTALO',
            'MALUKU',
            'PAPUA',
        ];

        foreach ($provinsiList as $provinsi) {
            if (str_contains($alamat, $provinsi)) {
                return match ($provinsi) {
                    'JAKARTA' => 'DKI Jakarta',
                    'YOGYAKARTA' => 'DI Yogyakarta',
                    'NTB' => 'Nusa Tenggara Barat',
                    'NTT' => 'Nusa Tenggara Timur',
                    'BANGKA BELITUNG' => 'Kepulauan Bangka Belitung',
                    default => ucwords(strtolower($provinsi)),
                };
            }
        }

        return '-';
    }

    private function buildOutletGroups($outlets): array
    {
        $groups = [];

        foreach ($outlets as $o) {
            $key = $this->outletGroupKey($o->nama_outlet ?? '');

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'key' => $key,
                    'ids' => [],
                    'nama_outlet' => $this->cleanOutletName($o->nama_outlet ?? ''),
                    'area' => $o->nama_area ?? '-',
                    'kota' => $o->kota ?? '-',
                    'provinsi' => $this->detectProvinsiFromAlamat($o->alamat ?? ''),
                    'is_grand' => false,
                ];
            }

            $groups[$key]['ids'][] = $o->id;

            if (($groups[$key]['area'] ?? '-') === '-' && ! empty($o->nama_area)) {
                $groups[$key]['area'] = $o->nama_area;
            }

            if (($groups[$key]['kota'] ?? '-') === '-' && ! empty($o->kota)) {
                $groups[$key]['kota'] = $o->kota;
            }

            if (($groups[$key]['provinsi'] ?? '-') === '-') {
                $provinsi = $this->detectProvinsiFromAlamat($o->alamat ?? '');
                if ($provinsi !== '-') {
                    $groups[$key]['provinsi'] = $provinsi;
                }
            }

            if (($o->status ?? '') === 'go') {
                $groups[$key]['is_grand'] = true;
            }
        }

        return array_values($groups);
    }

    public function laporanPerbulan(Request $request)
    {
        $user = auth()->user();
        $bulanTahun = (string) $request->input('bulan_tahun', '');
        $selectedOutlet = $request->input('outlet', '');
        $selectedEcommerce = $request->input('ecommerce', []);

        if (! is_array($selectedEcommerce)) {
            $selectedEcommerce = array_filter([(string) $selectedEcommerce]);
        }

        $data = [];

        $baseOutletQuery = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.mitra_id',
                'o.status',
                'o.kota',
                'o.alamat',
                'a.nama_area'
            );

        if ($user->role !== 'superadmin') {
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
                $baseOutletQuery->whereRaw('1 = 0');
            } else {
                $baseOutletQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        $outletListQuery = DB::table('tbl_outlets as o')
            ->select('o.id', 'o.nama_outlet')
            ->orderBy('o.nama_outlet');

        if ($user->role !== 'superadmin') {
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
                $outletListQuery->whereRaw('1 = 0');
            } else {
                $outletListQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        $outletList = $outletListQuery->get();

        /*
        * Jika user pilih 1 outlet, tetap ambil semua outlet duplikat
        * dengan nama bersih yang sama agar data duplikat ikut terkalkulasi.
        */
        if (! empty($selectedOutlet)) {
            $selectedOutletName = DB::table('tbl_outlets')
                ->where('id', $selectedOutlet)
                ->value('nama_outlet');

            $selectedKey = $this->outletGroupKey($selectedOutletName);

            $baseOutletQuery->whereRaw(
                "UPPER(TRIM(REPLACE(o.nama_outlet, 'Express', ''))) = ?",
                [$selectedKey]
            );
        }

        $outlets = $baseOutletQuery->get();

        $ecommerceList = DB::table('tbl_laporan_ecommerce')
            ->select('item_varian')
            ->distinct()
            ->orderBy('item_varian')
            ->pluck('item_varian')
            ->toArray();

        $grandTotal = [
            'hari' => [],
            'sales' => 0,
            'cu' => 0,
            'item' => 0,
            'ac' => 0,
            'basket' => 0,
            'basket_size' => 0,
        ];

        if ($bulanTahun && strpos($bulanTahun, '-') !== false) {
            [$tahun, $bulan] = explode('-', $bulanTahun);
            $tahun = (int) $tahun;
            $bulan = (int) $bulan;

            if ($tahun > 0 && $bulan >= 1 && $bulan <= 12) {
                $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;
                $outletIds = $outlets->pluck('id')->toArray();

                $laporanBulananRows = DB::table('tbl_laporan_bulanan')
                    ->select(
                        'outlet_id',
                        'tanggal',
                        DB::raw('SUM(total_omset) as total_omset'),
                        DB::raw('SUM(total_cu) as total_cu')
                    )
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->when(! empty($outletIds), function ($q) use ($outletIds) {
                        $q->whereIn('outlet_id', $outletIds);
                    }, function ($q) {
                        $q->whereRaw('1 = 0');
                    })
                    ->groupBy('outlet_id', 'tanggal')
                    ->get();

                $laporanBulananMap = [];
                foreach ($laporanBulananRows as $row) {
                    $day = (int) date('d', strtotime($row->tanggal));
                    $laporanBulananMap[$row->outlet_id][$day] = [
                        'sales' => (float) ($row->total_omset ?? 0),
                        'cu' => (int) ($row->total_cu ?? 0),
                    ];
                }

                $laporanEcommerceMap = [];
                if (! empty($selectedEcommerce)) {
                    $laporanEcommerceRows = DB::table('tbl_laporan_ecommerce')
                        ->select(
                            'outlet_id',
                            'tanggal',
                            DB::raw('SUM(total_jumlah) as total_sales')
                        )
                        ->whereYear('tanggal', $tahun)
                        ->whereMonth('tanggal', $bulan)
                        ->whereIn('item_varian', $selectedEcommerce)
                        ->when(! empty($outletIds), function ($q) use ($outletIds) {
                            $q->whereIn('outlet_id', $outletIds);
                        }, function ($q) {
                            $q->whereRaw('1 = 0');
                        })
                        ->groupBy('outlet_id', 'tanggal')
                        ->get();

                    foreach ($laporanEcommerceRows as $row) {
                        $day = (int) date('d', strtotime($row->tanggal));
                        $laporanEcommerceMap[$row->outlet_id][$day] = (float) ($row->total_sales ?? 0);
                    }
                }

                $laporanItemRows = DB::table('tbl_laporan_pareto')
                    ->select(
                        'outlet_id',
                        'tanggal',
                        DB::raw('SUM(total_jumlah) as total_item')
                    )
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->when(! empty($outletIds), function ($q) use ($outletIds) {
                        $q->whereIn('outlet_id', $outletIds);
                    }, function ($q) {
                        $q->whereRaw('1 = 0');
                    })
                    ->groupBy('outlet_id', 'tanggal')
                    ->get();

                $laporanItemMap = [];
                foreach ($laporanItemRows as $row) {
                    $day = (int) date('d', strtotime($row->tanggal));
                    $laporanItemMap[$row->outlet_id][$day] = (float) ($row->total_item ?? 0);
                }

                $outletGroups = $this->buildOutletGroups($outlets);

                foreach ($outletGroups as $group) {
                    $rowKey = $group['key'];

                    $data[$rowKey] = [
                        'nama_outlet' => $group['nama_outlet'],
                        'area'        => $group['area'],
                        'kota'        => $group['kota'],
                        'provinsi'    => $group['provinsi'],
                        'duplicate_count' => count($group['ids']),
                        'outlet_ids'   => $group['ids'],
                        'hari'        => [],
                        'is_grand'    => $group['is_grand'],
                    ];

                    for ($d = 1; $d <= $jumlahHari; $d++) {
                        $salesBulanan = 0;
                        $salesEcommerce = 0;
                        $cu = 0;
                        $totalItem = 0;

                        foreach ($group['ids'] as $outletId) {
                            $salesBulanan += (float) ($laporanBulananMap[$outletId][$d]['sales'] ?? 0);
                            $cu += (int) ($laporanBulananMap[$outletId][$d]['cu'] ?? 0);
                            $totalItem += (float) ($laporanItemMap[$outletId][$d] ?? 0);

                            if (! empty($selectedEcommerce)) {
                                $salesEcommerce += (float) ($laporanEcommerceMap[$outletId][$d] ?? 0);
                            }
                        }

                        $sales = ! empty($selectedEcommerce) ? $salesEcommerce : $salesBulanan;
                        $ac = $cu > 0 ? round($sales / $cu) : 0;
                        $basketSize = $cu > 0 ? round($totalItem / $cu, 2) : 0;

                        $data[$rowKey]['hari'][$d] = [
                            'sales' => $sales,
                            'cu' => $cu,
                            'ac' => $ac,
                            'basket_size' => $basketSize,
                            'total_item' => $totalItem,
                        ];
                    }

                    $subSales = collect($data[$rowKey]['hari'])->sum('sales');
                    $subCU = collect($data[$rowKey]['hari'])->sum('cu');
                    $subItem = collect($data[$rowKey]['hari'])->sum('total_item');

                    $data[$rowKey]['sub_total'] = [
                        'sales' => $subSales,
                        'cu' => $subCU,
                        'ac' => $subCU > 0 ? round($subSales / $subCU) : 0,
                        'basket_size' => $subCU > 0 ? round($subItem / $subCU, 2) : 0,
                        'total_item' => $subItem,
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | HAPUS OUTLET YANG TIDAK ADA TRANSAKSI
                    |--------------------------------------------------------------------------
                    */
                    if (
                        ($subSales ?? 0) <= 0
                        && ($subCU ?? 0) <= 0
                    ) {
                        unset($data[$rowKey]);
                        continue;
                    }
                }

                $data = array_values($data);

                for ($d = 1; $d <= $jumlahHari; $d++) {
                    $gsales = 0;
                    $gcu = 0;
                    $gitem = 0;

                    foreach ($data as $outlet) {
                        $gsales += (float) ($outlet['hari'][$d]['sales'] ?? 0);
                        $gcu += (int) ($outlet['hari'][$d]['cu'] ?? 0);
                        $gitem += (float) ($outlet['hari'][$d]['total_item'] ?? 0);
                    }

                    $grandTotal['hari'][$d] = [
                        'sales' => $gsales,
                        'cu' => $gcu,
                        'total_item' => $gitem,
                        'ac' => $gcu > 0 ? round($gsales / $gcu) : 0,
                        'basket_size' => $gcu > 0 ? round($gitem / $gcu, 2) : 0,
                    ];

                    $grandTotal['sales'] += $gsales;
                    $grandTotal['cu'] += $gcu;
                    $grandTotal['item'] += $gitem;
                }

                $grandTotal['ac'] = $grandTotal['cu'] > 0
                    ? round($grandTotal['sales'] / $grandTotal['cu'])
                    : 0;

                $grandTotal['basket'] = $grandTotal['cu'] > 0
                    ? round($grandTotal['item'] / $grandTotal['cu'], 2)
                    : 0;

                $grandTotal['basket_size'] = $grandTotal['basket'];
            }
        }

        return view('Investor.Laporan.laporanPerbulan', compact(
            'data',
            'bulanTahun',
            'outletList',
            'selectedOutlet',
            'ecommerceList',
            'selectedEcommerce',
            'grandTotal'
        ));
    }

    public function laporanPerbulanExport(Request $request)
    {
        $user = auth()->user();
        $bulanTahun = (string) $request->input('bulan_tahun', '');
        $selectedOutlet = $request->input('outlet', '');
        $selectedEcommerce = $request->input('ecommerce', []);
        $grandOpening = (int) $request->input('grand_opening', 0);

        if (! is_array($selectedEcommerce)) {
            $selectedEcommerce = array_filter([(string) $selectedEcommerce]);
        }

        if ($bulanTahun === '' || strpos($bulanTahun, '-') === false) {
            return back()->with('error', 'Bulan tahun wajib dipilih.');
        }

        [$tahun, $bulan] = explode('-', $bulanTahun);
        $tahun = (int) $tahun;
        $bulan = (int) $bulan;

        if ($tahun <= 0 || $bulan < 1 || $bulan > 12) {
            return back()->with('error', 'Format bulan tahun tidak valid.');
        }

        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;

        $outletQuery = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.kota',
                'o.alamat',
                'o.status',
                'a.nama_area'
            );

        if ($user->role !== 'superadmin') {
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
                $outletQuery->whereRaw('1 = 0');
            } else {
                $outletQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        if (! empty($selectedOutlet)) {
            $selectedOutletName = DB::table('tbl_outlets')
                ->where('id', $selectedOutlet)
                ->value('nama_outlet');

            $selectedKey = $this->outletGroupKey($selectedOutletName);

            $outletQuery->whereRaw(
                "UPPER(TRIM(REPLACE(o.nama_outlet, 'Express', ''))) = ?",
                [$selectedKey]
            );
        }

        if ($grandOpening) {
            $outletQuery->where('o.status', 'go');
        }

        $outlets = $outletQuery->get();
        $outletIds = $outlets->pluck('id')->toArray();
        $outletGroups = $this->buildOutletGroups($outlets);

        $laporanBulananRows = DB::table('tbl_laporan_bulanan')
            ->select(
                'outlet_id',
                'tanggal',
                DB::raw('SUM(total_omset) as total_omset'),
                DB::raw('SUM(total_cu) as total_cu')
            )
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->when(! empty($outletIds), function ($q) use ($outletIds) {
                $q->whereIn('outlet_id', $outletIds);
            }, function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->groupBy('outlet_id', 'tanggal')
            ->get();

        $laporanBulananMap = [];
        foreach ($laporanBulananRows as $row) {
            $day = (int) date('d', strtotime($row->tanggal));
            $laporanBulananMap[$row->outlet_id][$day] = [
                'sales' => (float) ($row->total_omset ?? 0),
                'cu'    => (int) ($row->total_cu ?? 0),
            ];
        }

        $laporanEcommerceMap = [];
        if (! empty($selectedEcommerce)) {
            $laporanEcommerceRows = DB::table('tbl_laporan_ecommerce')
                ->select(
                    'outlet_id',
                    'tanggal',
                    DB::raw('SUM(total_jumlah) as total_sales')
                )
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->whereIn('item_varian', $selectedEcommerce)
                ->when(! empty($outletIds), function ($q) use ($outletIds) {
                    $q->whereIn('outlet_id', $outletIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->groupBy('outlet_id', 'tanggal')
                ->get();

            foreach ($laporanEcommerceRows as $row) {
                $day = (int) date('d', strtotime($row->tanggal));
                $laporanEcommerceMap[$row->outlet_id][$day] = (float) ($row->total_sales ?? 0);
            }
        }

        $laporanItemRows = DB::table('tbl_laporan_pareto')
            ->select(
                'outlet_id',
                'tanggal',
                DB::raw('SUM(total_jumlah) as total_item')
            )
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->when(! empty($outletIds), function ($q) use ($outletIds) {
                $q->whereIn('outlet_id', $outletIds);
            }, function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->groupBy('outlet_id', 'tanggal')
            ->get();

        $laporanItemMap = [];
        foreach ($laporanItemRows as $row) {
            $day = (int) date('d', strtotime($row->tanggal));
            $laporanItemMap[$row->outlet_id][$day] = (float) ($row->total_item ?? 0);
        }

        return Excel::download(
            new class(
                $outletGroups,
                $laporanBulananMap,
                $laporanEcommerceMap,
                $laporanItemMap,
                $jumlahHari,
                $bulanTahun,
                $selectedEcommerce
            ) implements
                \Maatwebsite\Excel\Concerns\FromGenerator,
                \Maatwebsite\Excel\Concerns\WithHeadings,
                \Maatwebsite\Excel\Concerns\WithStyles {

                protected $outletGroups;
                protected $laporanBulananMap;
                protected $laporanEcommerceMap;
                protected $laporanItemMap;
                protected $jumlahHari;
                protected $bulanTahun;
                protected $selectedEcommerce;

                public function __construct(
                    $outletGroups,
                    $laporanBulananMap,
                    $laporanEcommerceMap,
                    $laporanItemMap,
                    $jumlahHari,
                    $bulanTahun,
                    $selectedEcommerce
                ) {
                    $this->outletGroups = $outletGroups;
                    $this->laporanBulananMap = $laporanBulananMap;
                    $this->laporanEcommerceMap = $laporanEcommerceMap;
                    $this->laporanItemMap = $laporanItemMap;
                    $this->jumlahHari = $jumlahHari;
                    $this->bulanTahun = $bulanTahun;
                    $this->selectedEcommerce = $selectedEcommerce;
                }

                public function headings(): array
                {
                    $baris1 = ['No', 'Nama Outlet', 'Area', 'Kota', 'Provinsi', 'Jumlah ID Outlet'];
                    $baris2 = ['', '', '', '', '', ''];

                    for ($d = 1; $d <= $this->jumlahHari; $d++) {
                        $baris1[] = str_pad($d, 2, '0', STR_PAD_LEFT);
                        $baris1[] = '';
                        $baris1[] = '';
                        $baris1[] = '';

                        $baris2[] = 'Sales';
                        $baris2[] = 'CU';
                        $baris2[] = 'AVG';
                        $baris2[] = 'AVG Size';
                    }

                    $baris1[] = 'Sub Total';
                    $baris1[] = '';
                    $baris1[] = '';
                    $baris1[] = '';

                    $baris2[] = 'Sales';
                    $baris2[] = 'CU';
                    $baris2[] = 'AVG';
                    $baris2[] = 'AVG Size';

                    return [$baris1, $baris2];
                }

                public function generator(): \Generator
                {
                    $no = 1;

                    foreach ($this->outletGroups as $group) {
                        $hari = [];
                        $totalSales = 0;
                        $totalCU = 0;
                        $totalItem = 0;

                        for ($d = 1; $d <= $this->jumlahHari; $d++) {
                            $salesBulanan = 0;
                            $salesEcommerce = 0;
                            $cu = 0;
                            $item = 0;

                            foreach ($group['ids'] as $outletId) {
                                $salesBulanan += (float) ($this->laporanBulananMap[$outletId][$d]['sales'] ?? 0);
                                $cu += (int) ($this->laporanBulananMap[$outletId][$d]['cu'] ?? 0);
                                $item += (float) ($this->laporanItemMap[$outletId][$d] ?? 0);

                                if (! empty($this->selectedEcommerce)) {
                                    $salesEcommerce += (float) ($this->laporanEcommerceMap[$outletId][$d] ?? 0);
                                }
                            }

                            $sales = ! empty($this->selectedEcommerce) ? $salesEcommerce : $salesBulanan;
                            $avgHarian = $cu > 0 ? round($sales / $cu) : 0;
                            $avgSizeHarian = $cu > 0 ? round($item / $cu, 2) : 0;

                            $hari[] = [$sales, $cu, $avgHarian, $avgSizeHarian];

                            $totalSales += $sales;
                            $totalCU += $cu;
                            $totalItem += $item;
                        }

                        $avgTotal = $totalCU > 0 ? round($totalSales / $totalCU) : 0;
                        $avgSizeTotal = $totalCU > 0 ? round($totalItem / $totalCU, 2) : 0;

                        $row = [
                            $no,
                            $group['nama_outlet'],
                            $group['area'],
                            $group['kota'],
                            $group['provinsi'],
                            count($group['ids']),
                        ];

                        foreach ($hari as $h) {
                            $row = array_merge($row, $h);
                        }

                        $row = array_merge($row, [$totalSales, $totalCU, $avgTotal, $avgSizeTotal]);

                        yield $row;
                        $no++;
                    }
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    $sheet->insertNewRowBefore(1, 1);
                    $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
                    $sheet->setCellValue('A1', 'LAPORAN PENJUALAN PERBULAN ' . strtoupper($this->bulanTahun));
                    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                    $sheet->getStyle('A2:' . $sheet->getHighestColumn() . '3')->getFont()->setBold(true);

                    $highestColumn = $sheet->getHighestColumn();
                    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                    }
                }
            },
            "laporan_perbulan_{$bulanTahun}.xlsx"
        );
    }

    public function laporanPerbulanPDF(Request $request)
    {
        $user = auth()->user();
        $bulanTahun = $request->input('bulan_tahun');

        if (! $bulanTahun) {
            abort(400, 'Bulan dan Tahun harus diisi.');
        }

        [$tahun, $bulan] = explode('-', $bulanTahun);

        if ($user->role === 'superadmin') {
            $mitraIds = DB::table('tbl_mitra')->pluck('id')->toArray();
        } else {
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
        }

        $outlets = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id')
            ->whereIn('o.mitra_id', $mitraIds)
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.alamat',
                'o.kota',
                'o.status',
                'a.nama_area'
            )
            ->get();

        if ($outlets->isEmpty()) {
            abort(404, 'Outlet tidak ditemukan.');
        }

        $outletGroups = $this->buildOutletGroups($outlets);
        $outletIds = $outlets->pluck('id')->toArray();

        $laporanRows = DB::table('tbl_laporan_bulanan')
            ->select(
                'outlet_id',
                'tanggal',
                DB::raw('SUM(total_omset) as total_omset'),
                DB::raw('SUM(total_cu) as total_cu')
            )
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->whereIn('outlet_id', $outletIds)
            ->groupBy('outlet_id', 'tanggal')
            ->get();

        $laporanMap = [];
        foreach ($laporanRows as $row) {
            $day = date('Y-m-d', strtotime($row->tanggal));
            $laporanMap[$row->outlet_id][$day] = [
                'sales' => (float) ($row->total_omset ?? 0),
                'cu' => (int) ($row->total_cu ?? 0),
            ];
        }

        $outletsData = [];

        foreach ($outletGroups as $group) {
            $rows = [];
            $subSales = 0;
            $subCU = 0;

            for ($day = 1; $day <= Carbon::create((int) $tahun, (int) $bulan, 1)->daysInMonth; $day++) {
                $date = Carbon::create((int) $tahun, (int) $bulan, $day)->format('Y-m-d');

                $totalOmset = 0;
                $totalCU = 0;

                foreach ($group['ids'] as $outletId) {
                    $totalOmset += (float) ($laporanMap[$outletId][$date]['sales'] ?? 0);
                    $totalCU += (int) ($laporanMap[$outletId][$date]['cu'] ?? 0);
                }

                if ($totalOmset <= 0 && $totalCU <= 0) {
                    continue;
                }

                $ac = $totalCU > 0 ? round($totalOmset / $totalCU) : 0;

                $rows[] = [
                    'tanggal'  => Carbon::parse($date)->format('d M Y'),
                    'Price'    => $totalOmset,
                    'Quantity' => $totalCU,
                    'Totals'   => $ac,
                    'Kota'     => $group['kota'] ?? '-',
                    'Provinsi' => $group['provinsi'] ?? '-',
                    'Jumlah ID Outlet' => count($group['ids']),
                ];

                $subSales += $totalOmset;
                $subCU += $totalCU;
            }

            $subAC = $subCU > 0 ? round($subSales / $subCU) : 0;

            $rows[] = [
                'tanggal'  => 'SUBTOTAL',
                'Price'    => $subSales,
                'Quantity' => $subCU,
                'Totals'   => $subAC,
                'Kota'     => $group['kota'] ?? '-',
                'Provinsi' => $group['provinsi'] ?? '-',
                'Jumlah ID Outlet' => count($group['ids']),
            ];

            $outletsData[$group['nama_outlet']] = $rows;
        }

        $grandSales = 0;
        $grandCU = 0;

        foreach ($outletsData as $rows) {
            $subtotal = end($rows);
            $grandSales += $subtotal['Price'];
            $grandCU += $subtotal['Quantity'];
        }

        $grandAC = $grandCU > 0 ? round($grandSales / $grandCU) : 0;

        $outletsData['GRAND TOTAL'] = [[
            'tanggal'  => 'GRAND TOTAL',
            'Price'    => $grandSales,
            'Quantity' => $grandCU,
            'Totals'   => $grandAC,
            'Kota'     => '-',
            'Provinsi' => '-',
            'Jumlah ID Outlet' => '-',
        ]];

        $pdf = Pdf::loadView('investor.laporan.perbulan_pdf', [
            'outletsData' => $outletsData,
            'bulanTahun'  => $bulanTahun,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("laporan_bulanan_$bulanTahun.pdf");
    }

    public function laporanPertahun(Request $request)
    {
        $user = auth()->user();
        $tahun = (int) $request->input('tahun', date('Y'));
        $selectedOutlet = $request->input('outlet', '');
        $selectedEcommerce = $request->input('ecommerce', []);

        if (! is_array($selectedEcommerce)) {
            $selectedEcommerce = array_filter([(string) $selectedEcommerce]);
        }

        if ($tahun <= 0) {
            $tahun = (int) date('Y');
        }

        $data = [];

        $baseOutletQuery = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.mitra_id',
                'o.status',
                'o.kota',
                'o.alamat',
                'a.nama_area'
            );

        if ($user->role !== 'superadmin') {
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
                $baseOutletQuery->whereRaw('1 = 0');
            } else {
                $baseOutletQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        $outletListQuery = DB::table('tbl_outlets as o')
            ->select('o.id', 'o.nama_outlet')
            ->orderBy('o.nama_outlet');

        if ($user->role !== 'superadmin') {
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
                $outletListQuery->whereRaw('1 = 0');
            } else {
                $outletListQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        $outletList = $outletListQuery->get();

        /*
        * Jika user pilih 1 outlet, tetap ambil semua outlet duplikat
        * dengan nama bersih yang sama agar data duplikat ikut terkalkulasi.
        */
        if (! empty($selectedOutlet)) {
            $selectedOutletName = DB::table('tbl_outlets')
                ->where('id', $selectedOutlet)
                ->value('nama_outlet');

            $selectedKey = $this->outletGroupKey($selectedOutletName);

            $baseOutletQuery->whereRaw(
                "UPPER(TRIM(REPLACE(o.nama_outlet, 'Express', ''))) = ?",
                [$selectedKey]
            );
        }

        $outlets = $baseOutletQuery->get();

        $ecommerceList = DB::table('tbl_laporan_ecommerce')
            ->select('item_varian')
            ->distinct()
            ->orderBy('item_varian')
            ->pluck('item_varian')
            ->toArray();

        $grandTotal = [
            'bulan' => [],
            'sales' => 0,
            'cu' => 0,
            'item' => 0,
            'ac' => 0,
            'basket' => 0,
            'basket_size' => 0,
        ];

        if ($tahun > 0) {
            $outletIds = $outlets->pluck('id')->toArray();

            $laporanBulananRows = DB::table('tbl_laporan_bulanan')
                ->select(
                    'outlet_id',
                    DB::raw('MONTH(tanggal) as bulan'),
                    DB::raw('SUM(total_omset) as total_omset'),
                    DB::raw('SUM(total_cu) as total_cu')
                )
                ->whereYear('tanggal', $tahun)
                ->when(! empty($outletIds), function ($q) use ($outletIds) {
                    $q->whereIn('outlet_id', $outletIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->groupBy('outlet_id', DB::raw('MONTH(tanggal)'))
                ->get();

            $laporanBulananMap = [];
            foreach ($laporanBulananRows as $row) {
                $bulan = (int) $row->bulan;
                $laporanBulananMap[$row->outlet_id][$bulan] = [
                    'sales' => (float) ($row->total_omset ?? 0),
                    'cu' => (int) ($row->total_cu ?? 0),
                ];
            }

            $laporanEcommerceMap = [];
            if (! empty($selectedEcommerce)) {
                $laporanEcommerceRows = DB::table('tbl_laporan_ecommerce')
                    ->select(
                        'outlet_id',
                        DB::raw('MONTH(tanggal) as bulan'),
                        DB::raw('SUM(total_jumlah) as total_sales')
                    )
                    ->whereYear('tanggal', $tahun)
                    ->whereIn('item_varian', $selectedEcommerce)
                    ->when(! empty($outletIds), function ($q) use ($outletIds) {
                        $q->whereIn('outlet_id', $outletIds);
                    }, function ($q) {
                        $q->whereRaw('1 = 0');
                    })
                    ->groupBy('outlet_id', DB::raw('MONTH(tanggal)'))
                    ->get();

                foreach ($laporanEcommerceRows as $row) {
                    $bulan = (int) $row->bulan;
                    $laporanEcommerceMap[$row->outlet_id][$bulan] = (float) ($row->total_sales ?? 0);
                }
            }

            $laporanItemRows = DB::table('tbl_laporan_pareto')
                ->select(
                    'outlet_id',
                    DB::raw('MONTH(tanggal) as bulan'),
                    DB::raw('SUM(total_jumlah) as total_item')
                )
                ->whereYear('tanggal', $tahun)
                ->when(! empty($outletIds), function ($q) use ($outletIds) {
                    $q->whereIn('outlet_id', $outletIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->groupBy('outlet_id', DB::raw('MONTH(tanggal)'))
                ->get();

            $laporanItemMap = [];
            foreach ($laporanItemRows as $row) {
                $bulan = (int) $row->bulan;
                $laporanItemMap[$row->outlet_id][$bulan] = (float) ($row->total_item ?? 0);
            }

            $outletGroups = $this->buildOutletGroups($outlets);

            foreach ($outletGroups as $group) {
                $rowKey = $group['key'];

                $data[$rowKey] = [
                    'nama_outlet' => $group['nama_outlet'],
                    'area'        => $group['area'],
                    'kota'        => $group['kota'],
                    'provinsi'    => $group['provinsi'],
                    'duplicate_count' => count($group['ids']),
                    'outlet_ids'   => $group['ids'],
                    'bulan'       => [],
                    'is_grand'    => $group['is_grand'],
                ];

                for ($m = 1; $m <= 12; $m++) {
                    $salesBulanan = 0;
                    $salesEcommerce = 0;
                    $cu = 0;
                    $totalItem = 0;

                    foreach ($group['ids'] as $outletId) {
                        $salesBulanan += (float) ($laporanBulananMap[$outletId][$m]['sales'] ?? 0);
                        $cu += (int) ($laporanBulananMap[$outletId][$m]['cu'] ?? 0);
                        $totalItem += (float) ($laporanItemMap[$outletId][$m] ?? 0);

                        if (! empty($selectedEcommerce)) {
                            $salesEcommerce += (float) ($laporanEcommerceMap[$outletId][$m] ?? 0);
                        }
                    }

                    $sales = ! empty($selectedEcommerce) ? $salesEcommerce : $salesBulanan;
                    $ac = $cu > 0 ? round($sales / $cu) : 0;
                    $basketSize = $cu > 0 ? round($totalItem / $cu, 2) : 0;

                    $data[$rowKey]['bulan'][$m] = [
                        'sales' => $sales,
                        'cu' => $cu,
                        'ac' => $ac,
                        'basket_size' => $basketSize,
                        'total_item' => $totalItem,
                        'item' => $totalItem,
                    ];
                }

                $subSales = collect($data[$rowKey]['bulan'])->sum('sales');
                $subCU = collect($data[$rowKey]['bulan'])->sum('cu');
                $subItem = collect($data[$rowKey]['bulan'])->sum('total_item');

                $data[$rowKey]['sub_total'] = [
                    'sales' => $subSales,
                    'cu' => $subCU,
                    'ac' => $subCU > 0 ? round($subSales / $subCU) : 0,
                    'basket_size' => $subCU > 0 ? round($subItem / $subCU, 2) : 0,
                    'total_item' => $subItem,
                    'item' => $subItem,
                ];

                /*
                |--------------------------------------------------------------------------
                | HAPUS OUTLET YANG TIDAK ADA TRANSAKSI
                |--------------------------------------------------------------------------
                */
                if (
                    ($subSales ?? 0) <= 0
                    && ($subCU ?? 0) <= 0
                ) {
                    unset($data[$rowKey]);
                    continue;
                }
            }

            $data = array_values($data);

            for ($m = 1; $m <= 12; $m++) {
                $gsales = 0;
                $gcu = 0;
                $gitem = 0;

                foreach ($data as $outlet) {
                    $gsales += (float) ($outlet['bulan'][$m]['sales'] ?? 0);
                    $gcu += (int) ($outlet['bulan'][$m]['cu'] ?? 0);
                    $gitem += (float) ($outlet['bulan'][$m]['total_item'] ?? ($outlet['bulan'][$m]['item'] ?? 0));
                }

                $grandTotal['bulan'][$m] = [
                    'sales' => $gsales,
                    'cu' => $gcu,
                    'total_item' => $gitem,
                    'item' => $gitem,
                    'ac' => $gcu > 0 ? round($gsales / $gcu) : 0,
                    'basket_size' => $gcu > 0 ? round($gitem / $gcu, 2) : 0,
                ];

                $grandTotal['sales'] += $gsales;
                $grandTotal['cu'] += $gcu;
                $grandTotal['item'] += $gitem;
            }

            $grandTotal['ac'] = $grandTotal['cu'] > 0
                ? round($grandTotal['sales'] / $grandTotal['cu'])
                : 0;

            $grandTotal['basket'] = $grandTotal['cu'] > 0
                ? round($grandTotal['item'] / $grandTotal['cu'], 2)
                : 0;

            $grandTotal['basket_size'] = $grandTotal['basket'];
        }

        return view('Investor.Laporan.laporanPertahun', compact(
            'data',
            'tahun',
            'outletList',
            'selectedOutlet',
            'ecommerceList',
            'selectedEcommerce',
            'grandTotal'
        ));
    }

    public function laporanPertahunExport(Request $request)
    {
        $user = auth()->user();
        $tahun = (int) $request->input('tahun', date('Y'));
        $selectedOutlet = $request->input('outlet', '');
        $selectedEcommerce = $request->input('ecommerce', []);
        $grandOpening = (int) $request->input('grand_opening', 0);

        if (! is_array($selectedEcommerce)) {
            $selectedEcommerce = array_filter([(string) $selectedEcommerce]);
        }

        if ($tahun <= 0) {
            return back()->with('error', 'Tahun tidak valid.');
        }

        $outletQuery = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.kota',
                'o.alamat',
                'o.status',
                'a.nama_area'
            );

        if ($user->role !== 'superadmin') {
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
                $outletQuery->whereRaw('1 = 0');
            } else {
                $outletQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        if (! empty($selectedOutlet)) {
            $selectedOutletName = DB::table('tbl_outlets')
                ->where('id', $selectedOutlet)
                ->value('nama_outlet');

            $selectedKey = $this->outletGroupKey($selectedOutletName);

            $outletQuery->whereRaw(
                "UPPER(TRIM(REPLACE(o.nama_outlet, 'Express', ''))) = ?",
                [$selectedKey]
            );
        }

        if ($grandOpening) {
            $outletQuery->where('o.status', 'go');
        }

        $outlets = $outletQuery->get();
        $outletIds = $outlets->pluck('id')->toArray();
        $outletGroups = $this->buildOutletGroups($outlets);

        $laporanBulananRows = DB::table('tbl_laporan_bulanan')
            ->select(
                'outlet_id',
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(total_omset) as total_omset'),
                DB::raw('SUM(total_cu) as total_cu')
            )
            ->whereYear('tanggal', $tahun)
            ->when(! empty($outletIds), function ($q) use ($outletIds) {
                $q->whereIn('outlet_id', $outletIds);
            }, function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->groupBy('outlet_id', DB::raw('MONTH(tanggal)'))
            ->get();

        $laporanBulananMap = [];
        foreach ($laporanBulananRows as $row) {
            $bulan = (int) $row->bulan;
            $laporanBulananMap[$row->outlet_id][$bulan] = [
                'sales' => (float) ($row->total_omset ?? 0),
                'cu'    => (int) ($row->total_cu ?? 0),
            ];
        }

        $laporanEcommerceMap = [];
        if (! empty($selectedEcommerce)) {
            $laporanEcommerceRows = DB::table('tbl_laporan_ecommerce')
                ->select(
                    'outlet_id',
                    DB::raw('MONTH(tanggal) as bulan'),
                    DB::raw('SUM(total_jumlah) as total_sales')
                )
                ->whereYear('tanggal', $tahun)
                ->whereIn('item_varian', $selectedEcommerce)
                ->when(! empty($outletIds), function ($q) use ($outletIds) {
                    $q->whereIn('outlet_id', $outletIds);
                }, function ($q) {
                    $q->whereRaw('1 = 0');
                })
                ->groupBy('outlet_id', DB::raw('MONTH(tanggal)'))
                ->get();

            foreach ($laporanEcommerceRows as $row) {
                $bulan = (int) $row->bulan;
                $laporanEcommerceMap[$row->outlet_id][$bulan] = (float) ($row->total_sales ?? 0);
            }
        }

        $laporanItemRows = DB::table('tbl_laporan_pareto')
            ->select(
                'outlet_id',
                DB::raw('MONTH(tanggal) as bulan'),
                DB::raw('SUM(total_jumlah) as total_item')
            )
            ->whereYear('tanggal', $tahun)
            ->when(! empty($outletIds), function ($q) use ($outletIds) {
                $q->whereIn('outlet_id', $outletIds);
            }, function ($q) {
                $q->whereRaw('1 = 0');
            })
            ->groupBy('outlet_id', DB::raw('MONTH(tanggal)'))
            ->get();

        $laporanItemMap = [];
        foreach ($laporanItemRows as $row) {
            $bulan = (int) $row->bulan;
            $laporanItemMap[$row->outlet_id][$bulan] = (float) ($row->total_item ?? 0);
        }

        return Excel::download(
            new class(
                $outletGroups,
                $laporanBulananMap,
                $laporanEcommerceMap,
                $laporanItemMap,
                $tahun,
                $selectedEcommerce
            ) implements
                \Maatwebsite\Excel\Concerns\FromGenerator,
                \Maatwebsite\Excel\Concerns\WithHeadings,
                \Maatwebsite\Excel\Concerns\WithStyles {

                protected $outletGroups;
                protected $laporanBulananMap;
                protected $laporanEcommerceMap;
                protected $laporanItemMap;
                protected $tahun;
                protected $selectedEcommerce;

                public function __construct(
                    $outletGroups,
                    $laporanBulananMap,
                    $laporanEcommerceMap,
                    $laporanItemMap,
                    $tahun,
                    $selectedEcommerce
                ) {
                    $this->outletGroups = $outletGroups;
                    $this->laporanBulananMap = $laporanBulananMap;
                    $this->laporanEcommerceMap = $laporanEcommerceMap;
                    $this->laporanItemMap = $laporanItemMap;
                    $this->tahun = $tahun;
                    $this->selectedEcommerce = $selectedEcommerce;
                }

                public function headings(): array
                {
                    $baris1 = ['No', 'Nama Outlet', 'Area', 'Kota', 'Provinsi', 'Jumlah ID Outlet'];
                    $baris2 = ['', '', '', '', '', ''];

                    $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

                    foreach ($namaBulan as $bulan) {
                        $baris1[] = $bulan;
                        $baris1[] = '';
                        $baris1[] = '';
                        $baris1[] = '';

                        $baris2[] = 'Sales';
                        $baris2[] = 'CU';
                        $baris2[] = 'AVG';
                        $baris2[] = 'AVG Size';
                    }

                    $baris1[] = 'Sub Total';
                    $baris1[] = '';
                    $baris1[] = '';
                    $baris1[] = '';

                    $baris2[] = 'Sales';
                    $baris2[] = 'CU';
                    $baris2[] = 'AVG';
                    $baris2[] = 'AVG Size';

                    return [$baris1, $baris2];
                }

                public function generator(): \Generator
                {
                    $no = 1;

                    foreach ($this->outletGroups as $group) {
                        $bulanData = [];
                        $totalSales = 0;
                        $totalCU = 0;
                        $totalItem = 0;

                        for ($m = 1; $m <= 12; $m++) {
                            $salesBulanan = 0;
                            $salesEcommerce = 0;
                            $cu = 0;
                            $item = 0;

                            foreach ($group['ids'] as $outletId) {
                                $salesBulanan += (float) ($this->laporanBulananMap[$outletId][$m]['sales'] ?? 0);
                                $cu += (int) ($this->laporanBulananMap[$outletId][$m]['cu'] ?? 0);
                                $item += (float) ($this->laporanItemMap[$outletId][$m] ?? 0);

                                if (! empty($this->selectedEcommerce)) {
                                    $salesEcommerce += (float) ($this->laporanEcommerceMap[$outletId][$m] ?? 0);
                                }
                            }

                            $sales = ! empty($this->selectedEcommerce) ? $salesEcommerce : $salesBulanan;
                            $avgBulan = $cu > 0 ? round($sales / $cu) : 0;
                            $avgSizeBulan = $cu > 0 ? round($item / $cu, 2) : 0;

                            $bulanData[] = [$sales, $cu, $avgBulan, $avgSizeBulan];

                            $totalSales += $sales;
                            $totalCU += $cu;
                            $totalItem += $item;
                        }

                        $avgTotal = $totalCU > 0 ? round($totalSales / $totalCU) : 0;
                        $avgSizeTotal = $totalCU > 0 ? round($totalItem / $totalCU, 2) : 0;

                        if ($totalSales <= 0 && $totalCU <= 0) {
                            continue;
                        }

                        $row = [
                            $no,
                            $group['nama_outlet'],
                            $group['area'],
                            $group['kota'],
                            $group['provinsi'],
                            count($group['ids']),
                        ];

                        foreach ($bulanData as $b) {
                            $row = array_merge($row, $b);
                        }

                        $row = array_merge($row, [$totalSales, $totalCU, $avgTotal, $avgSizeTotal]);

                        yield $row;
                        $no++;
                    }
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    $sheet->insertNewRowBefore(1, 1);
                    $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
                    $sheet->setCellValue('A1', 'LAPORAN PENJUALAN TAHUNAN ' . strtoupper((string) $this->tahun));
                    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                    $sheet->getStyle('A2:' . $sheet->getHighestColumn() . '3')->getFont()->setBold(true);

                    $highestColumn = $sheet->getHighestColumn();
                    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                        $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                    }
                }
            },
            "laporan_pertahun_{$tahun}.xlsx"
        );
    }

    public function laporanMenu(Request $request)
    {
        $user = auth()->user();

        $tanggalMulai = $request->get('tanggal_mulai', '');
        $tanggalAkhir = $request->get('tanggal_akhir', '');
        $selectedOutlet = $request->get('outlet', '');
        $selectedEcommerce = $request->get('ecommerce', []);

        if (! is_array($selectedEcommerce)) {
            $selectedEcommerce = array_filter([(string) $selectedEcommerce]);
        }

        $selectedEcommerce = array_values(array_filter($selectedEcommerce));

        $filterApplied = !empty($tanggalMulai) && !empty($tanggalAkhir);

        if ($filterApplied && $tanggalMulai > $tanggalAkhir) {
            [$tanggalMulai, $tanggalAkhir] = [$tanggalAkhir, $tanggalMulai];
        }

        $baseOutletQuery = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.mitra_id',
                'o.status',
                'o.kota',
                'o.alamat',
                'a.nama_area'
            );

        if ($user->role !== 'superadmin') {
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
                $baseOutletQuery->whereRaw('1 = 0');
            } else {
                $baseOutletQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        $outletListQuery = DB::table('tbl_outlets as o')
            ->select('o.id', 'o.nama_outlet')
            ->orderBy('o.nama_outlet');

        if ($user->role !== 'superadmin') {
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
                $outletListQuery->whereRaw('1 = 0');
            } else {
                $outletListQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        $outletList = $outletListQuery->get();

        /*
        * Jika user pilih 1 outlet, tetap ambil semua outlet duplikat
        * dengan nama bersih yang sama agar data duplikat ikut terkalkulasi.
        */
        if (! empty($selectedOutlet)) {
            $selectedOutletName = DB::table('tbl_outlets')
                ->where('id', $selectedOutlet)
                ->value('nama_outlet');

            $selectedKey = $this->outletGroupKey($selectedOutletName);

            $baseOutletQuery->whereRaw(
                "UPPER(TRIM(REPLACE(o.nama_outlet, 'Express', ''))) = ?",
                [$selectedKey]
            );
        }

        $outlets = $baseOutletQuery->get();
        $outletIds = $outlets->pluck('id')->toArray();
        $outletGroups = $this->buildOutletGroups($outlets);

        /*
        |--------------------------------------------------------------------------
        | Ecommerce list
        |--------------------------------------------------------------------------
        | Di data pareto tidak ada pemetaan ecommerce per menu yang stabil.
        | Tetap disiapkan agar view tidak error bila nanti dibutuhkan.
        */
        $ecommerceList = [];

        $menuColumns = [];
        $data = [];
        $grandTotal = [
            'menu' => [],
            'qty' => 0,
            'total_harga' => 0,
            'ecommerce' => [],
        ];

        if ($filterApplied && !empty($outletIds)) {
            $rows = DB::table('tbl_laporan_pareto as l')
                ->join('tbl_outlets as o', 'o.id', '=', 'l.outlet_id')
                ->select(
                    'l.outlet_id',
                    'o.nama_outlet',
                    'l.item_nama',
                    DB::raw('SUM(COALESCE(l.total_jumlah, 0)) as qty'),
                    DB::raw('SUM(COALESCE(l.total_harga, 0)) as total_harga')
                )
                ->whereBetween('l.tanggal', [$tanggalMulai, $tanggalAkhir])
                ->whereIn('l.outlet_id', $outletIds)
                ->groupBy('l.outlet_id', 'o.nama_outlet', 'l.item_nama')
                ->orderBy('l.item_nama')
                ->get();

            $menuColumns = $rows->pluck('item_nama')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $laporanMap = [];
            foreach ($rows as $row) {
                $laporanMap[$row->outlet_id][$row->item_nama] = [
                    'qty' => (int) ($row->qty ?? 0),
                    'total_harga' => (float) ($row->total_harga ?? 0),
                ];
            }

            foreach ($menuColumns as $menu) {
                $grandTotal['menu'][$menu] = [
                    'qty' => 0,
                    'total_harga' => 0,
                ];
            }

            foreach ($outletGroups as $group) {
                $rowKey = $group['key'];

                $data[$rowKey] = [
                    'nama_outlet' => $group['nama_outlet'],
                    'area' => $group['area'],
                    'kota' => $group['kota'],
                    'provinsi' => $group['provinsi'],
                    'duplicate_count' => count($group['ids']),
                    'outlet_ids' => $group['ids'],
                    'menu' => [],
                    'sub_total' => [
                        'qty' => 0,
                        'total_harga' => 0,
                    ],
                    'ecommerce' => [],
                    'is_grand' => $group['is_grand'],
                ];

                foreach ($menuColumns as $menu) {
                    $qty = 0;
                    $totalHarga = 0;

                    foreach ($group['ids'] as $outletId) {
                        $qty += (int) ($laporanMap[$outletId][$menu]['qty'] ?? 0);
                        $totalHarga += (float) ($laporanMap[$outletId][$menu]['total_harga'] ?? 0);
                    }

                    $data[$rowKey]['menu'][$menu] = [
                        'qty' => $qty,
                        'total_harga' => $totalHarga,
                    ];

                    $data[$rowKey]['sub_total']['qty'] += $qty;
                    $data[$rowKey]['sub_total']['total_harga'] += $totalHarga;

                    $grandTotal['menu'][$menu]['qty'] += $qty;
                    $grandTotal['menu'][$menu]['total_harga'] += $totalHarga;
                }

                if (
                    ($data[$rowKey]['sub_total']['qty'] ?? 0) <= 0
                    && ($data[$rowKey]['sub_total']['total_harga'] ?? 0) <= 0
                ) {
                    unset($data[$rowKey]);
                    continue;
                }

                $grandTotal['qty'] += (int) $data[$rowKey]['sub_total']['qty'];
                $grandTotal['total_harga'] += (float) $data[$rowKey]['sub_total']['total_harga'];
            }

            $data = array_values($data);
        }

        return view('Investor.Laporan.laporanMenu', [
            'tanggalMulai' => $tanggalMulai,
            'tanggalAkhir' => $tanggalAkhir,
            'selectedOutlet' => $selectedOutlet,
            'selectedEcommerce' => $selectedEcommerce,
            'ecommerceList' => $ecommerceList,
            'outletList' => $outletList,
            'menuColumns' => $menuColumns,
            'data' => $data,
            'grandTotal' => $grandTotal,
            'filterApplied' => $filterApplied,
        ]);
    }

    public function laporanMenuExport(Request $request)
    {
        $user = auth()->user();
        $tanggalMulai = $request->input('tanggal_mulai', '');
        $tanggalAkhir = $request->input('tanggal_akhir', '');
        $selectedOutlet = $request->input('outlet', '');

        if (empty($tanggalMulai) || empty($tanggalAkhir)) {
            return back()->with('error', 'Tanggal mulai dan tanggal akhir wajib dipilih.');
        }

        if ($tanggalMulai > $tanggalAkhir) {
            [$tanggalMulai, $tanggalAkhir] = [$tanggalAkhir, $tanggalMulai];
        }

        $outletQuery = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_area_outlet as a', 'a.id', '=', 'o.area_id')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.kota',
                'o.alamat',
                'o.status',
                'a.nama_area'
            );

        if ($user->role !== 'superadmin') {
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
                $outletQuery->whereRaw('1 = 0');
            } else {
                $outletQuery->whereIn('o.mitra_id', $mitraIds);
            }
        }

        if (! empty($selectedOutlet)) {
            $selectedOutletName = DB::table('tbl_outlets')
                ->where('id', $selectedOutlet)
                ->value('nama_outlet');

            $selectedKey = $this->outletGroupKey($selectedOutletName);

            $outletQuery->whereRaw(
                "UPPER(TRIM(REPLACE(o.nama_outlet, 'Express', ''))) = ?",
                [$selectedKey]
            );
        }

        $outlets = $outletQuery
            ->orderBy('o.nama_outlet')
            ->get();

        $outletIds = $outlets->pluck('id')->toArray();
        $outletGroups = $this->buildOutletGroups($outlets);

        if (empty($outletIds)) {
            return back()->with('error', 'Data outlet tidak ditemukan.');
        }

        $rows = DB::table('tbl_laporan_pareto as l')
            ->select(
                'l.outlet_id',
                'l.item_nama',
                DB::raw('SUM(COALESCE(l.total_jumlah, 0)) as qty'),
                DB::raw('SUM(COALESCE(l.total_harga, 0)) as total_harga')
            )
            ->whereBetween('l.tanggal', [$tanggalMulai, $tanggalAkhir])
            ->whereIn('l.outlet_id', $outletIds)
            ->groupBy('l.outlet_id', 'l.item_nama')
            ->orderBy('l.item_nama')
            ->get();

        $menuColumns = $rows->pluck('item_nama')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $laporanMap = [];
        foreach ($rows as $row) {
            $laporanMap[$row->outlet_id][$row->item_nama] = [
                'qty' => (int) ($row->qty ?? 0),
                'total_harga' => (float) ($row->total_harga ?? 0),
            ];
        }

        $fileName = 'laporan_menu_' . $tanggalMulai . '_sd_' . $tanggalAkhir . '.xlsx';

        return Excel::download(
            new class($outletGroups, $laporanMap, $menuColumns, $tanggalMulai, $tanggalAkhir) implements
                \Maatwebsite\Excel\Concerns\FromGenerator,
                \Maatwebsite\Excel\Concerns\WithHeadings,
                \Maatwebsite\Excel\Concerns\WithStyles {

                protected $outletGroups;
                protected $laporanMap;
                protected $menuColumns;
                protected $tanggalMulai;
                protected $tanggalAkhir;

                public function __construct($outletGroups, $laporanMap, $menuColumns, $tanggalMulai, $tanggalAkhir)
                {
                    $this->outletGroups = $outletGroups;
                    $this->laporanMap = $laporanMap;
                    $this->menuColumns = $menuColumns;
                    $this->tanggalMulai = $tanggalMulai;
                    $this->tanggalAkhir = $tanggalAkhir;
                }

                public function headings(): array
                {
                    $baris1 = ['No', 'Nama Outlet', 'Area', 'Kota', 'Provinsi', 'Jumlah ID Outlet'];
                    $baris2 = ['', '', '', '', '', ''];

                    foreach ($this->menuColumns as $menu) {
                        $baris1[] = $menu;
                        $baris1[] = '';
                        $baris2[] = 'QTY';
                        $baris2[] = 'Total Harga';
                    }

                    $baris1[] = 'Sub Total';
                    $baris1[] = '';
                    $baris2[] = 'QTY';
                    $baris2[] = 'Total Harga';

                    return [$baris1, $baris2];
                }

                public function generator(): \Generator
                {
                    $no = 1;

                    $grandTotalPerMenu = [];
                    $grandQty = 0;
                    $grandHarga = 0;

                    foreach ($this->menuColumns as $menu) {
                        $grandTotalPerMenu[$menu] = [
                            'qty' => 0,
                            'total_harga' => 0,
                        ];
                    }

                    foreach ($this->outletGroups as $group) {
                        $row = [
                            $no,
                            $group['nama_outlet'] ?? '-',
                            $group['area'] ?? '-',
                            $group['kota'] ?? '-',
                            $group['provinsi'] ?? '-',
                            count($group['ids'] ?? []),
                        ];

                        $subQty = 0;
                        $subHarga = 0;

                        foreach ($this->menuColumns as $menu) {
                            $qty = 0;
                            $totalHarga = 0;

                            foreach ($group['ids'] as $outletId) {
                                $qty += (int) ($this->laporanMap[$outletId][$menu]['qty'] ?? 0);
                                $totalHarga += (float) ($this->laporanMap[$outletId][$menu]['total_harga'] ?? 0);
                            }

                            $row[] = $qty;
                            $row[] = $totalHarga;

                            $subQty += $qty;
                            $subHarga += $totalHarga;

                            $grandTotalPerMenu[$menu]['qty'] += $qty;
                            $grandTotalPerMenu[$menu]['total_harga'] += $totalHarga;
                        }

                        if ($subQty <= 0 && $subHarga <= 0) {
                            continue;
                        }

                        $row[] = $subQty;
                        $row[] = $subHarga;

                        $grandQty += $subQty;
                        $grandHarga += $subHarga;

                        yield $row;
                        $no++;
                    }

                    $grandRow = ['#', 'GRAND TOTAL', '-', '-', '-', '-'];

                    foreach ($this->menuColumns as $menu) {
                        $grandRow[] = $grandTotalPerMenu[$menu]['qty'];
                        $grandRow[] = $grandTotalPerMenu[$menu]['total_harga'];
                    }

                    $grandRow[] = $grandQty;
                    $grandRow[] = $grandHarga;

                    yield $grandRow;
                }

                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
                {
                    $sheet->insertNewRowBefore(1, 1);

                    $highestColumn = $sheet->getHighestColumn();
                    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                    $highestRow = $sheet->getHighestRow();

                    $sheet->mergeCells('A1:' . $highestColumn . '1');
                    $sheet->setCellValue(
                        'A1',
                        'LAPORAN MENU PER OUTLET (' . $this->tanggalMulai . ' s/d ' . $this->tanggalAkhir . ')'
                    );

                    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                    $sheet->getStyle('A2:' . $highestColumn . '3')->getFont()->setBold(true);
                    $sheet->getStyle('A2:' . $highestColumn . '3')->getAlignment()->setHorizontal('center');
                    $sheet->getStyle('A2:' . $highestColumn . '3')->getAlignment()->setVertical('center');

                    $sheet->getStyle('A2:' . $highestColumn . $highestRow)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                    $sheet->getStyle('A' . $highestRow . ':' . $highestColumn . $highestRow)->getFont()->setBold(true);

                    for ($i = 1; $i <= $highestColumnIndex; $i++) {
                        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                        $sheet->getColumnDimension($col)->setAutoSize(true);
                    }

                    $sheet->freezePane('G4');

                    return [];
                }
            },
            $fileName
        );
    }
    
    // ESB CORE
    private function normalizeCoa(?string $accountNo): string
    {
        return preg_replace('/\s+/', '', trim((string) $accountNo));
    }

    private function getCoaReferenceMap()
    {
        return DB::table('coa_reference')
            ->select('account_no', 'account_no_normalized', 'description_system')
            ->get()
            ->keyBy('account_no_normalized');
    }

    private function filterAndMapGlRowsByCoaReference(array $rows, $coaMap): array
    {
        $result = [];

        foreach ($rows as $row) {
            $accountNo  = trim((string) ($row['accountNo'] ?? ''));
            $normalized = $this->normalizeCoa($accountNo);

            $coaRef = $coaMap[$normalized] ?? null;

            if (!$coaRef) {
                continue;
            }

            $row['accountNo']                = $accountNo;
            $row['accountNoNormalized']      = $normalized;
            $row['accountDescriptionApi']    = $row['accountDescription'] ?? null;
            $row['accountDescriptionSystem'] = $coaRef->description_system;
            $row['accountDescription']       = $coaRef->description_system;

            $result[] = $row;
        }

        return $result;
    }

    private function getAccessibleOutlets($user)
    {
        $query = DB::table('tbl_outlets as o')
            ->leftJoin('tbl_mitra as m', 'm.id', '=', 'o.mitra_id')
            ->select(
                'o.id',
                'o.nama_outlet',
                'o.mitra_id',
                'o.status',
                'o.esb_branch_code',
                'o.outlet_key',
                'o.outlet_key_fix',
                'm.kode_mitra',
                'm.nama_mitra'
            )
            ->orderBy('o.nama_outlet');

        if ($user->role === 'superadmin') {
            return $query->get();
        }

        $investorId = DB::table('tbl_investor')
            ->where('user_id', $user->id)
            ->value('id');

        if (!$investorId) {
            abort(403, 'Investor tidak ditemukan.');
        }

        $mitraIds = DB::table('tbl_mitra')
            ->where('investor_id', $investorId)
            ->pluck('id')
            ->toArray();

        return $query
            ->whereIn('o.mitra_id', $mitraIds)
            ->get();
    }

    private function resolveEsbTokenKeyFromOutlet(object $outlet): string
    {
        if (!empty($outlet->kode_mitra)) {
            return strtoupper(trim((string) $outlet->kode_mitra));
        }

        return 'OKNHO';
    }

    private function makePnlDataTemplate(int $jumlahHari): array
    {
        $descriptions = [
            'Penjualan Outlet',
            'Penjualan - Makanan',
            'Penjualan - Minuman',
            'Penjualan - Lainnya',
            'Penjualan - Bahan',
            'Total Sales',
            'HPP - Makanan',
            'HPP - Minuman',
            'HPP - Lainnya',
            'HPP - Bahan Terjual',
            'Total Cost of Goods Sold',
            'Gross Profit',
        ];

        $data = [];

        foreach ($descriptions as $desc) {
            $data[$desc] = [
                'deskripsi' => $desc,
                'hari'      => [],
                'sub_total' => [
                    'sales' => 0,
                    'cu'    => 0,
                    'ac'    => 0,
                ],
            ];

            for ($d = 1; $d <= $jumlahHari; $d++) {
                $data[$desc]['hari'][$d] = [
                    'sales' => 0,
                    'cu'    => 0,
                    'ac'    => 0,
                ];
            }
        }

        return $data;
    }

    private function buildDateList(?string $startDate, ?string $endDate): array
    {
        $jumlahHari = 31;
        $dateList   = [];

        if ($startDate && $endDate) {
            $startTs  = strtotime($startDate);
            $endTs    = strtotime($endDate);
            $diffDays = (int) floor(($endTs - $startTs) / 86400) + 1;

            $jumlahHari = max(1, min($diffDays, 62));

            for ($i = 0; $i < $jumlahHari; $i++) {
                $dateList[$i + 1] = date('Y-m-d', strtotime("+{$i} day", $startTs));
            }
        }

        return [$jumlahHari, $dateList];
    }

    private function getSalesMap(): array
    {
        return [
            '4 1 01 01' => 'Penjualan Outlet',
            '4 1 01 02' => 'Penjualan - Minuman',
            '4 1 01 03' => 'Penjualan - Lainnya',
            '4 1 02 01' => 'Penjualan - Bahan',
        ];
    }

    private function getSalesSignMap(): array
    {
        return [
            '4 1 03 01' => -1,
            '4 1 03 02' => -1,
            '4 1 03 03' => -1,
            '4 1 03 04' => -1,
            '4 1 03 05' => -1,
        ];
    }

    private function getHppMap(): array
    {
        return [
            '5 1 01 01' => 'HPP - Makanan',
            '5 1 01 02' => 'HPP - Minuman',
            '5 1 01 03' => 'HPP - Lainnya',
            '5 1 02 01' => 'HPP - Bahan Terjual',
        ];
    }

    private function processOutletGl(
        \App\Services\Esb\EsbClient $esb,
        object $outlet,
        string $startDate,
        string $endDate,
        array &$data,
        array $dateList,
        int $jumlahHari
    ): array {
        $branchCode = trim((string) ($outlet->esb_branch_code ?? ''));
        $tokenKey   = $this->resolveEsbTokenKeyFromOutlet($outlet);
    
        if ($branchCode === '') {
            return [
                'ok'         => false,
                'outlet_id'  => $outlet->id ?? null,
                'outlet'     => $outlet->nama_outlet ?? null,
                'branchCode' => null,
                'tokenKey'   => $tokenKey,
                'error'      => 'Branch code kosong',
                'rows_count' => 0,
                'rows'       => [],
                'summary'    => null,
            ];
        }
    
        try {
            $gl = $esb->fetchGeneralLedgerAllByTokenKey([
                'startPeriod' => $startDate,
                'endPeriod'   => $endDate,
                'branchCode'  => $branchCode,
                'costCenter'  => 'No',
                'page'        => 1,
            ], $tokenKey);
        } catch (\Throwable $e) {
            return [
                'ok'         => false,
                'outlet_id'  => $outlet->id ?? null,
                'outlet'     => $outlet->nama_outlet ?? null,
                'branchCode' => $branchCode,
                'tokenKey'   => $tokenKey,
                'error'      => $e->getMessage(),
                'rows_count' => 0,
                'rows'       => [],
                'summary'    => null,
            ];
        }
    
        if (!($gl['ok'] ?? false)) {
            return [
                'ok'         => false,
                'outlet_id'  => $outlet->id ?? null,
                'outlet'     => $outlet->nama_outlet ?? null,
                'branchCode' => $branchCode,
                'tokenKey'   => $tokenKey,
                'error'      => $gl['error'] ?? 'Gagal fetch general ledger',
                'rows_count' => 0,
                'rows'       => [],
                'summary'    => null,
                'raw'        => $gl['raw'] ?? null,
            ];
        }
    
        $rows = $esb->mapGlRowsWithCoaReference($gl['rows'] ?? []);
    
        $salesMap  = $this->getSalesMap();
        $salesSign = $this->getSalesSignMap();
        $hppMap    = $this->getHppMap();
    
        if (!empty($rows)) {
            $esb->fillSalesFromGlByCoaMap($data, $rows, $dateList, $salesMap, $jumlahHari, $salesSign);
            $esb->fillHppFromGlDebitCredit($data, $rows, $dateList, $hppMap, $jumlahHari);
        }
    
        return [
            'ok'         => true,
            'outlet_id'  => $outlet->id ?? null,
            'outlet'     => $outlet->nama_outlet ?? null,
            'branchCode' => $branchCode,
            'tokenKey'   => $tokenKey,
            'error'      => null,
            'rows_count' => count($rows),
            'rows'       => $rows,
            'summary'    => $esb->summarizeGlRows($rows),
        ];
    }
    
    public function laporanPNLHo(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $syncKey = $request->get('sync_key');

        $rows = $this->buildDefaultRows(0);
        $units = collect([]);
        $summary = $this->buildDefaultSummary();
        $liveErrors = [];
        $status = null;

        if ($syncKey) {
            $payload = Cache::get("pnl_live_sync:{$syncKey}");

            if ($payload) {
                $status = $payload;

                $units = collect($payload['units'] ?? []);
                $rows = $payload['rows'] ?? $rows;
                $summary = [
                    'grandPendapatan' => $payload['grandPendapatan'] ?? 0,
                    'grandLaba' => $payload['grandLaba'] ?? 0,
                    'grandNpm' => $payload['grandNpm'] ?? 0,
                ];
                $liveErrors = $payload['errors'] ?? [];
            }
        }

        return view('Investor.Laporan.laporanProfitLoss', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'units' => $units,
            'rows' => $rows,
            'grandPendapatan' => $summary['grandPendapatan'],
            'grandLaba' => $summary['grandLaba'],
            'grandNpm' => $summary['grandNpm'],
            'filterApplied' => !empty($startDate) && !empty($endDate),
            'liveErrors' => $liveErrors,
            'syncKey' => $syncKey,
            'syncStatus' => $status,
        ]);
    }

    public function startSyncPnlHo(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->startOfDay();

        if ($start->gt($end)) {
            $message = 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.';
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 422)
                : back()->with('error', $message);
        }

        if ($start->diffInDays($end) > 6) {
            $message = 'Maksimal filter hanya 7 hari.';
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $credential = DB::table('tbl_api_credentials')
            ->where('credential_code', 'OKNHO')
            ->where('is_active', 1)
            ->first();

        if (! $credential) {
            $message = 'Credential OKNHO tidak ditemukan.';
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $units = DB::table('tbl_outlets')
            ->where('credential_id', $credential->id)
            ->whereNotNull('esb_branch_code')
            ->where('esb_branch_code', '!=', '')
            ->select('id', 'nama_outlet', 'credential_id', 'esb_branch_id', 'esb_branch_code')
            ->orderBy('nama_outlet')
            ->get();

        if ($units->isEmpty()) {
            $message = 'Outlet untuk credential OKNHO tidak ditemukan.';
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $lock = Cache::lock('pnl_live_sync_oknho_start_lock', 10);

        if (! $lock->get()) {
            $message = 'Masih ada proses start sync PNL live yang sedang berjalan.';
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 409)
                : back()->with('error', $message);
        }

        try {
            $activeKey = Cache::get('pnl_live_sync_oknho_active_key');

            if ($activeKey) {
                $existing = Cache::get("pnl_live_sync:{$activeKey}");

                if (! $existing) {
                    Cache::forget('pnl_live_sync_oknho_active_key');
                } else {
                    $existingStatus = $existing['status'] ?? null;
                    $updatedAt = isset($existing['updated_at']) ? strtotime($existing['updated_at']) : null;
                    $isStale = $updatedAt ? ((time() - $updatedAt) > 1800) : true;

                    if (in_array($existingStatus, ['done', 'failed'], true) || $isStale) {
                        Cache::forget('pnl_live_sync_oknho_active_key');
                        Cache::forget("pnl_live_sync:{$activeKey}");
                    } else {
                        $message = 'Masih ada sync PNL live yang sedang berjalan.';
                        return $request->expectsJson()
                            ? response()->json(['status' => 'error', 'message' => $message], 409)
                            : back()->with('error', $message);
                    }
                }
            }

            $syncKey = 'pnl-live-oknho-' . Str::uuid()->toString();

            Cache::put('pnl_live_sync_oknho_active_key', $syncKey, now()->addHours(6));

            Cache::put("pnl_live_sync:{$syncKey}", [
                'status' => 'queued',
                'message' => 'Sync PNL live masuk antrian.',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'credential_id' => (int) $credential->id,
                'credential_code' => 'OKNHO',
                'total_branches' => 0,
                'processed_branches' => 0,
                'success_branches' => 0,
                'failed_branches' => 0,
                'progress' => 0,
                'requested_at' => now()->toDateTimeString(),
                'started_at' => null,
                'finished_at' => null,
                'updated_at' => now()->toDateTimeString(),
                'errors' => [],
                'logs' => [],
                'rows' => $this->buildDefaultRows($units->count()),
                'units' => $units->map(fn ($u) => (array) $u)->values()->all(),
                'grandPendapatan' => 0,
                'grandLaba' => 0,
                'grandNpm' => 0,
            ], now()->addHours(6));

            SyncPnlLiveAllBranchesJob::dispatch(
                $syncKey,
                'OKNHO',
                (int) $credential->id,
                $request->start_date,
                $request->end_date
            )->onConnection('redis')->onQueue('esb-pnl');

            $redirectUrl = route('investor.laporan.profitnloss.oknho', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'sync_key' => $syncKey,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'queued',
                    'message' => 'Sync PNL live sedang diproses di background.',
                    'sync_key' => $syncKey,
                    'redirect_url' => $redirectUrl,
                ]);
            }

            return redirect()->to($redirectUrl);
        } catch (\Throwable $e) {
            Log::error('START SYNC PNL LIVE FAILED', [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            $message = 'Gagal memulai sync PNL live: ' . $e->getMessage();

            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => $message], 500)
                : back()->with('error', $message);
        } finally {
            optional($lock)->release();
        }
    }

    public function syncPnlHoStatus(string $key)
    {
        $data = Cache::get("pnl_live_sync:{$key}");

        if (!$data) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Status sync PNL live tidak ditemukan / expired.',
            ], 404);
        }

        return response()->json($data);
    }

    protected function buildDefaultRows(int $unitCount): array
    {
        return [
            ['keterangan' => 'Pendapatan', 'values' => array_fill(0, $unitCount, 0)],
            ['keterangan' => 'HPP', 'values' => array_fill(0, $unitCount, 0)],
            ['keterangan' => 'Opex', 'values' => array_fill(0, $unitCount, 0)],
            ['keterangan' => 'Pendapatan (Biaya Lainnya)', 'values' => array_fill(0, $unitCount, 0)],
            ['keterangan' => 'Laba (rugi) Bersih', 'values' => array_fill(0, $unitCount, 0)],
            ['keterangan' => 'NPM', 'values' => array_fill(0, $unitCount, 0), 'is_percent' => true],
        ];
    }

    protected function buildDefaultSummary(): array
    {
        return [
            'grandPendapatan' => 0,
            'grandLaba' => 0,
            'grandNpm' => 0,
        ];
    }
    
    // CRON API ESB
    public function testLoginEsb(EsbAuthService $esbAuthService)
    {
        try {
            $result = $esbAuthService->loginByCredentialCode('OKNHO');
            dd($result);
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }
    
    public function loginAllEsb(EsbAuthService $service)
    {
        $result = $service->loginAllCredentials();
    
        dd($result);
    }
    
    public function testSyncLedgerAllBranches(EsbLedgerService $service)
    {
        set_time_limit(0);
    
        try {
            $result = $service->syncAllBranchesFromOutlets(
                'OKNHO',
                '2025-08-01',
                now()->format('Y-m-d')
            );
    
            return response()->json([
                'status' => 'ok',
                'credential_code' => $result['credential_code'],
                'start_period' => $result['start_period'],
                'end_period' => $result['end_period'],
                'total_outlets' => $result['total_outlets'],
                'success_count' => $result['success_count'],
                'failed_count' => $result['failed_count'],
                'grand_total_saved_rows' => $result['grand_total_saved_rows'],
                'details_sample' => array_slice($result['details'], 0, 20),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\M_Outlet;
use App\Models\M_MarketingAreaPotensi;
use App\Models\M_MarketingPotensiPin;
class MarketingController extends Controller
{
    public function salesPerKota(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'provinsi' => (array) $request->get('provinsi', ['All']),
            'kota' => (array) $request->get('kota', ['All']),
            'tahun' => (array) $request->get('tahun', [date('Y')]),
            'bulan' => (array) $request->get('bulan', ['All']),
            'quarter' => (array) $request->get('quarter', ['All']),
        ];

        // Ensure models are available
        $query = \App\Models\LaporanBulanan::query()
            ->join('tbl_outlets', 'tbl_laporan_bulanan.outlet_id', '=', 'tbl_outlets.id')
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->select(
                'tbl_laporan_bulanan.tanggal',
                'tbl_laporan_bulanan.total_omset as omzet',
                'tbl_laporan_bulanan.total_cu as cu',
                'tbl_outlets.nama_outlet as outlet',
                'tbl_outlets.status',
                \Illuminate\Support\Facades\DB::raw("COALESCE(master_outlets.kota_kab, 
                    CASE 
                        WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' THEN 'Sleman'
                        WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' THEN 'Bantul'
                        ELSE '-'
                    END
                ) as kota"),
                \Illuminate\Support\Facades\DB::raw("COALESCE(master_outlets.provinsi, 
                    CASE 
                        WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%DIY%' THEN 'DI Yogyakarta'
                        ELSE '-'
                    END
                ) as provinsi"),
                'master_outlets.tanggal_closed'
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('tbl_laporan_bulanan.tanggal', [$filters['start_date'], $filters['end_date']]);
        } else {
            if (!empty($filters['tahun']) && !in_array('All', $filters['tahun'])) {
                $query->whereIn(\DB::raw('YEAR(tbl_laporan_bulanan.tanggal)'), $filters['tahun']);
            }
            if (!empty($filters['bulan']) && !in_array('All', $filters['bulan'])) {
                $months = array_map(function($m) { return date('m', strtotime($m)); }, $filters['bulan']);
                $query->whereIn(\DB::raw('MONTH(tbl_laporan_bulanan.tanggal)'), $months);
            }
            if (!empty($filters['quarter']) && !in_array('All', $filters['quarter'])) {
                $quarters = array_map(function($q) { return str_replace('Q', '', $q); }, $filters['quarter']);
                $query->whereIn(\DB::raw('QUARTER(tbl_laporan_bulanan.tanggal)'), $quarters);
            }
        }

        if (!empty($filters['provinsi']) && !in_array('All', $filters['provinsi'])) {
            $query->where(function($q) use ($filters) {
                $q->whereIn('master_outlets.provinsi', $filters['provinsi']);
                
                // Smart Fallback jika join gagal namun nama mengandung kota di provinsi tersebut
                if (in_array('DI Yogyakarta', $filters['provinsi'])) {
                    $q->orWhere(\Illuminate\Support\Facades\DB::raw('UPPER(tbl_outlets.nama_outlet)'), 'LIKE', '%SLEMAN%')
                      ->orWhere(\Illuminate\Support\Facades\DB::raw('UPPER(tbl_outlets.nama_outlet)'), 'LIKE', '%BANTUL%')
                      ->orWhere(\Illuminate\Support\Facades\DB::raw('UPPER(tbl_outlets.nama_outlet)'), 'LIKE', '%DIY%');
                }
            });
        }
        if (!empty($filters['kota']) && !in_array('All', $filters['kota'])) {
            $query->where(function($q) use ($filters) {
                foreach ($filters['kota'] as $k) {
                    $q->orWhere('master_outlets.kota_kab', 'LIKE', '%' . $k . '%')
                      ->orWhere('tbl_outlets.nama_outlet', 'LIKE', '%' . $k . '%');
                }
            });
        }

        $knownLocations = \Illuminate\Support\Facades\Cache::remember('known_locations_v1', 3600, function() {
            return \App\Models\MasterOutlet::select('kota_kab', 'provinsi')
                ->whereNotNull('kota_kab')->where('kota_kab', '!=', '')
                ->distinct()->get()
                ->map(function($o) {
                    return [
                        'kota' => trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $o->kota_kab)),
                        'provinsi' => $o->provinsi
                    ];
                })->filter(fn($loc) => strlen($loc['kota']) > 3)->values()->all();
        });

        $allData = $query->get()->map(function($item) use ($knownLocations) {
            if ($item->kota && $item->kota !== '-') {
                $item->kota = trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $item->kota));
                if (!$item->provinsi || $item->provinsi === '-') {
                    $item->provinsi = 'Unidentified Area';
                }
            } else {
                $namaOutlet = strtoupper($item->outlet);
                $foundKota = null;
                $foundProvinsi = null;
                
                // 1. Dictionary Match
                foreach ($knownLocations as $loc) {
                    if (str_contains($namaOutlet, strtoupper($loc['kota']))) {
                        $foundKota = $loc['kota'];
                        $foundProvinsi = $loc['provinsi'];
                        break;
                    }
                }
                
                // 2. Hardcoded fallback for DIY
                if (!$foundKota && (str_contains($namaOutlet, 'DIY ') || str_ends_with($namaOutlet, 'DIY') || str_contains($namaOutlet, 'YOGYA'))) {
                    $foundKota = 'Yogyakarta';
                    $foundProvinsi = 'DI Yogyakarta';
                }
                
                // 3. Fallback to parsing after hyphen "-"
                if (!$foundKota && str_contains($namaOutlet, '-')) {
                    $parts = explode('-', $namaOutlet);
                    $potentialKota = trim(end($parts));
                    // Remove terms like "NOT USE"
                    if (strlen($potentialKota) > 2 && !str_contains($potentialKota, 'NOT USE')) {
                        $foundKota = ucwords(strtolower($potentialKota));
                    }
                }
                
                if ($foundKota) {
                    $item->kota = $foundKota;
                    // Only overwrite province if we found one from dictionary, otherwise keep SQL province
                    if ($foundProvinsi) {
                        $item->provinsi = $foundProvinsi;
                    } else if (!$item->provinsi || $item->provinsi === '-') {
                        $item->provinsi = 'Unidentified Area';
                    }
                } else {
                    $item->kota = 'Unidentified Area';
                    // Preserve SQL fallback province if it exists! (e.g. DI Yogyakarta)
                    if (!$item->provinsi || $item->provinsi === '-') {
                        $item->provinsi = 'Unidentified Area';
                    }
                }
            }
            return $item;
        });

        $totalOmzet = $allData->sum('omzet');
        $totalCu = $allData->sum('cu');
        
        $outletQuery = \App\Models\MasterOutlet::where(function($q) {
            $q->whereNull('tanggal_closed')->orWhere('tanggal_closed', '=', '');
        })->whereNotNull('tanggal_open')->where('tanggal_open', '!=', '');

        if (!empty($filters['provinsi']) && !in_array('All', $filters['provinsi'])) {
            $outletQuery->whereIn('provinsi', $filters['provinsi']);
        }
        if (!empty($filters['kota']) && !in_array('All', $filters['kota'])) {
            $outletQuery->where(function($q) use ($filters) {
                foreach ($filters['kota'] as $k) {
                    $q->orWhere('kota_kab', 'LIKE', '%' . $k . '%');
                }
            });
        }
        
        $totalOutlet = $outletQuery->distinct('nama_outlet')->count('nama_outlet');

        $avgBasket = $totalCu > 0 ? round($totalOmzet / $totalCu) : 0;
        $avgOmzet = $totalOutlet > 0 ? round($totalOmzet / $totalOutlet) : 0;
        $avgCu = $totalOutlet > 0 ? round($totalCu / $totalOutlet) : 0;

        $kpi = [
            'total_omzet' => $this->rupiahShort($totalOmzet),
            'total_cu' => number_format($totalCu, 0, ',', '.'),
            'avg_basket' => $this->rupiah($avgBasket),
            'total_outlet' => number_format($totalOutlet, 0, ',', '.'),
            'avg_omzet' => $this->rupiahShort($avgOmzet),
            'avg_cu' => number_format($avgCu, 0, ',', '.'),
            'jumlah_data' => $allData->count(),
            'status_sheet' => 'Connected to Database',
            'message_sheet' => 'Data tahun ' . (in_array('All', $filters['tahun']) ? 'Semua Tahun' : implode(', ', $filters['tahun'])) . ' berhasil ditarik.',
        ];

        // Format dates into Months (Jan, Feb, etc)
        $monthlyRaw = $allData
            ->map(function ($row) {
                $time = strtotime($row->tanggal);
                $row->bulan_nama = strtoupper(date('M', $time));
                $row->bulan_angka = (int) date('m', $time);
                return $row;
            })
            ->groupBy('bulan_nama')
            ->map(fn ($rows, $bulan) => [
                'bulan' => $bulan,
                'bulan_angka' => (int) $rows->first()->bulan_angka,
                'omzet' => $rows->sum('omzet'),
            ])
            ->sortBy('bulan_angka')
            ->values();

        $maxMonthly = max($monthlyRaw->max('omzet') ?? 0, 1);

        $monthlyTrend = $monthlyRaw
            ->map(fn ($row) => [
                'bulan' => $row['bulan'],
                'omzet_label' => $this->rupiahShort($row['omzet']),
                'height' => max(8, round(($row['omzet'] / $maxMonthly) * 100)),
            ])
            ->values();

        $topCities = $allData
            ->groupBy('kota')
            ->map(function ($rows, $kota) {
                $omzet = $rows->sum('omzet');
                return [
                    'kota' => $kota ?: '-',
                    'provinsi' => $rows->pluck('provinsi')->filter()->first() ?? '-',
                    'omzet' => $omzet,
                    'omzet_label' => $this->rupiahShort($omzet),
                ];
            })
            ->sortByDesc('omzet')
            ->take(5)
            ->values();

        // Need unfiltered data for options
        $allUnfiltered = \App\Models\LaporanBulanan::query()
            ->join('tbl_outlets', 'tbl_laporan_bulanan.outlet_id', '=', 'tbl_outlets.id')
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->select('master_outlets.provinsi as provinsi', 'master_outlets.kota_kab as kota');
            
        if ($filters['tahun'] !== 'All') {
            $allUnfiltered->whereYear('tbl_laporan_bulanan.tanggal', $filters['tahun']);
        }
        
        $allUnfilteredData = $allUnfiltered->get();

        $years = \App\Models\LaporanBulanan::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values();

        $kotaByProvinsi = $allUnfilteredData->groupBy('provinsi')->map(function($items) {
            return $items->pluck('kota')->filter()->map(function($kota) {
                return trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $kota));
            })->unique()->sort()->values();
        });

        $options = [
            'tahun' => $years,
            'provinsi' => $allUnfilteredData->pluck('provinsi')->filter()->unique()->sort()->values(),
            'kota' => $allUnfilteredData->pluck('kota')->filter()->map(function($kota) {
                return trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $kota));
            })->unique()->sort()->values(),
            'bulan' => collect(['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']), // Static or from DB
            'quarter' => collect(['Q1','Q2','Q3','Q4']),
        ];

        return view('Marketing.sales-per-kota', compact(
            'filters',
            'kpi',
            'monthlyTrend',
            'topCities',
            'options',
            'kotaByProvinsi'
        ));
    }

    private function getSheetSalesPerkota()
    {
        return Cache::remember('sales_per_kota_real_debug_v1', now()->addMinutes(3), function () {
            $spreadsheetId = '1C6MBTJfUkYvQSxekBfg0ihPeKLR3EsK8';
            $gid = '443033800';
            $sheetName = urlencode('DATA Sales Perkota');

            $urls = [
                "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}",
                "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/gviz/tq?tqx=out:csv&gid={$gid}",
                "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/gviz/tq?tqx=out:csv&sheet={$sheetName}",
            ];

            $lastMessage = 'Tidak ada response dari Google Sheet.';

            foreach ($urls as $url) {
                try {
                    $response = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0',
                    ])->timeout(30)->get($url);
                } catch (\Throwable $e) {
                    $lastMessage = 'Request error: ' . $e->getMessage();
                    continue;
                }

                $status = $response->status();
                $body = trim($response->body());

                if (! $response->successful()) {
                    $lastMessage = "HTTP {$status}. Google Sheet tidak bisa diakses server.";
                    continue;
                }

                if ($body === '') {
                    $lastMessage = 'Response kosong dari Google Sheet.';
                    continue;
                }

                if (str_contains(strtolower($body), '<html') || str_contains(strtolower($body), '<!doctype')) {
                    $lastMessage = 'Google mengembalikan HTML/login page. Sheet belum public atau belum Publish to web.';
                    continue;
                }

                $data = $this->parseSalesCsv($body);

                if ($data->count() > 0) {
                    return [
                        'data' => $data,
                        'status' => 'Connected',
                        'message' => 'Data berhasil terbaca: ' . $data->count() . ' rows.',
                    ];
                }

                $lastMessage = 'CSV terbaca, tapi header/data tidak cocok. Pastikan tab berisi kolom Outlet, Kota, Provinsi, Kategori OT, Tipe OT, Bulan, Quarter, Omzet, CU.';
            }

            return [
                'data' => collect(),
                'status' => 'Belum terkoneksi',
                'message' => $lastMessage,
            ];
        });
    }

    private function parseSalesCsv($body)
    {
        $rows = array_map('str_getcsv', preg_split('/\r\n|\r|\n/', $body));

        $headerIndex = null;

        foreach ($rows as $index => $row) {
            $first = strtolower(trim((string) ($row[0] ?? '')));
            $third = strtolower(trim((string) ($row[2] ?? '')));

            if ($first === 'outlet' && $third === 'provinsi') {
                $headerIndex = $index;
                break;
            }
        }

        if ($headerIndex === null) {
            return collect();
        }

        $dataRows = array_slice($rows, $headerIndex + 1);

        return collect($dataRows)
            ->filter(fn ($row) => is_array($row) && trim((string) ($row[0] ?? '')) !== '')
            ->map(function ($row) {
                $quarterNumber = (int) $this->numberOnly($row[7] ?? 0);

                return [
                    'outlet' => trim((string) ($row[0] ?? '')),
                    'kota' => trim((string) ($row[1] ?? '')),
                    'provinsi' => trim((string) ($row[2] ?? '')),
                    'kategori' => trim((string) ($row[3] ?? '')),
                    'tipe' => trim((string) ($row[4] ?? '')),
                    'bulan_angka' => (int) $this->numberOnly($row[5] ?? 0),
                    'bulan_nama' => strtoupper(trim((string) ($row[6] ?? ''))),
                    'quarter' => $quarterNumber,
                    'quarter_label' => $quarterNumber > 0 ? 'Q' . $quarterNumber : '',
                    'omzet' => $this->numberOnly($row[8] ?? 0),
                    'cu' => $this->numberOnly($row[9] ?? 0),
                ];
            })
            ->filter(fn ($row) =>
                $row['outlet'] !== ''
                && $row['kota'] !== ''
                && $row['provinsi'] !== ''
            )
            ->values();
    }

    public function outletZ(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'tahun' => (array) $request->get('tahun', [date('Y')]),
            'provinsi' => (array) $request->get('provinsi', ['All']),
            'kota' => (array) $request->get('kota', ['All']),
            'bulan' => (array) $request->get('bulan', ['All']),
            'status' => (array) $request->get('status', ['All']),
            'zona' => (array) $request->get('zona', ['All']),
            'outlet' => (array) $request->get('outlet', ['All']),
        ];

        $query = \App\Models\M_Outlet::query()
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->leftJoin('tbl_laporan_bulanan', function($join) use ($filters) {
                $join->on('tbl_outlets.id', '=', 'tbl_laporan_bulanan.outlet_id');
                if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                    $join->whereBetween('tbl_laporan_bulanan.tanggal', [$filters['start_date'], $filters['end_date']]);
                } else {
                    if (!empty($filters['tahun']) && !in_array('All', $filters['tahun'])) {
                        $join->whereIn(\Illuminate\Support\Facades\DB::raw('YEAR(tbl_laporan_bulanan.tanggal)'), $filters['tahun']);
                    }
                    if (!empty($filters['bulan']) && !in_array('All', $filters['bulan'])) {
                        $months = array_map(function($m) { return date('m', strtotime($m)); }, $filters['bulan']);
                        $join->whereIn(\Illuminate\Support\Facades\DB::raw('MONTH(tbl_laporan_bulanan.tanggal)'), $months);
                    }
                }
            })
            ->select(
                // Mengambil nama dari master_outlets jika ada, jika tidak pakai dari tbl_outlets
                \Illuminate\Support\Facades\DB::raw('MAX(master_outlets.id) as master_id'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(master_outlets.nama_outlet, TRIM(tbl_outlets.nama_outlet)) as nama_outlet'),
                \Illuminate\Support\Facades\DB::raw('MAX(tbl_outlets.status) as status'), // 'existing', 'tutup', 'go'
                \Illuminate\Support\Facades\DB::raw("MAX(COALESCE(master_outlets.kota_kab, 
                    CASE 
                        WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' THEN 'Sleman'
                        WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' THEN 'Bantul'
                        ELSE '-'
                    END
                )) as kota"),
                \Illuminate\Support\Facades\DB::raw("MAX(COALESCE(master_outlets.provinsi, 
                    CASE 
                        WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%DIY%' THEN 'DI Yogyakarta'
                        ELSE '-'
                    END
                )) as provinsi"),
                'master_outlets.tanggal_closed',
                'master_outlets.tanggal_open',
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as omset'),
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_cu) as cu')
            )
            ->groupBy(
                \Illuminate\Support\Facades\DB::raw('COALESCE(master_outlets.nama_outlet, TRIM(tbl_outlets.nama_outlet))'),
                'master_outlets.kota_kab',
                'master_outlets.provinsi',
                'master_outlets.tanggal_closed',
                'master_outlets.tanggal_open'
            );

        if (!empty($filters['provinsi']) && !in_array('All', $filters['provinsi'])) {
            $query->where(function($q) use ($filters) {
                $q->whereIn('master_outlets.provinsi', $filters['provinsi']);
                
                // Smart Fallback jika join gagal namun nama mengandung kota di provinsi tersebut
                if (in_array('DI Yogyakarta', $filters['provinsi'])) {
                    $q->orWhere(\Illuminate\Support\Facades\DB::raw('UPPER(tbl_outlets.nama_outlet)'), 'LIKE', '%SLEMAN%')
                      ->orWhere(\Illuminate\Support\Facades\DB::raw('UPPER(tbl_outlets.nama_outlet)'), 'LIKE', '%BANTUL%')
                      ->orWhere(\Illuminate\Support\Facades\DB::raw('UPPER(tbl_outlets.nama_outlet)'), 'LIKE', '%DIY%');
                }
            });
        }
        if (!empty($filters['kota']) && !in_array('All', $filters['kota'])) {
            $query->where(function($q) use ($filters) {
                foreach ($filters['kota'] as $k) {
                    $q->orWhere('master_outlets.kota_kab', 'LIKE', '%' . $k . '%');
                }
            });
        }
        if (!empty($filters['status']) && !in_array('All', $filters['status'])) {
            $dbStatuses = [];
            foreach ($filters['status'] as $s) {
                $dbStatus = strtolower($s);
                if ($dbStatus === 'closed') $dbStatus = 'tutup';
                if ($dbStatus === 'new') $dbStatus = 'go';
                $dbStatuses[] = $dbStatus;
            }
            $query->whereIn('tbl_outlets.status', $dbStatuses);
        }
        if (!empty($filters['outlet']) && !in_array('All', $filters['outlet'])) {
            $query->whereIn('tbl_outlets.nama_outlet', $filters['outlet']);
        }

        $allData = $query->orderByDesc('omset')->orderByDesc('cu')->get();
        
        $allData = $allData->reject(function ($row) {
            $isClosed = ($row->status === 'tutup') || !empty($row->tanggal_closed);
            
            // Buang outlet bayangan (yatim piatu tanpa master) jika omsetnya 0
            if (empty($row->master_id) && (float)$row->omset == 0) {
                return true;
            }

            // Hanya sembunyikan outlet yang sudah tutup JIKA omsetnya benar-benar 0
            return $isClosed && (float)$row->omset == 0;
        });

        $kpi = [
            'total' => 0,
            'existing' => 0,
            'closed' => 0,
            'new' => 0,
            'omset' => 0,
            'cu' => 0,
        ];

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $daysInMonth = max(1, round((strtotime($filters['end_date']) - strtotime($filters['start_date'])) / (60 * 60 * 24)) + 1);
        } else if (!empty($filters['bulan']) && !in_array('All', $filters['bulan'])) {
            $yearStr = (!empty($filters['tahun']) && !in_array('All', $filters['tahun'])) ? $filters['tahun'][0] : date('Y');
            $totalDays = 0;
            foreach ($filters['bulan'] as $m) {
                $totalDays += \Carbon\Carbon::parse("1 " . $m . " " . $yearStr)->daysInMonth;
            }
            $daysInMonth = $totalDays;
        } else {
            $daysInMonth = 365; // Approx for 'All'
        }

        $outlets = [];
        $totalOutlets = $allData->count();
        $q1 = ceil($totalOutlets * 0.25);
        $q2 = ceil($totalOutlets * 0.50);
        $q3 = ceil($totalOutlets * 0.75);

        foreach ($allData->values() as $index => $row) {
            $rank = $index + 1;
            
            $zona = 'Z4';
            $zonaClass = 'red';
            if ($rank <= $q1) { $zona = 'Z1'; $zonaClass = 'green'; }
            else if ($rank <= $q2) { $zona = 'Z2'; $zonaClass = 'blue'; }
            else if ($rank <= $q3) { $zona = 'Z3'; $zonaClass = 'yellow'; }

            if (!empty($filters['zona']) && !in_array('All', $filters['zona']) && !in_array($zona, $filters['zona'])) {
                continue;
            }

            $statusLabel = 'Existing';
            $statusClass = 'existing';
            if ($row->status === 'tutup') { $statusLabel = 'Closed'; $statusClass = 'closed'; }
            if ($row->status === 'go') { $statusLabel = 'New'; $statusClass = 'new'; }

            $kpi['total']++;
            if ($row->status === 'existing') $kpi['existing']++;
            if ($row->status === 'tutup') $kpi['closed']++;
            if ($row->status === 'go') $kpi['new']++;
            $kpi['omset'] += (float) $row->omset;
            $kpi['cu'] += (int) $row->cu;

            $avgOmset = (float) $row->omset / max(1, $daysInMonth);
            $avgCu = (int) $row->cu / max(1, $daysInMonth);

            $cleanedKota = $row->kota ? trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $row->kota)) : '-';

            $outlets[] = [
                'nama' => $row->nama_outlet,
                'kota' => $cleanedKota,
                'provinsi' => $row->provinsi ?: '-',
                'status_label' => $statusLabel,
                'status_class' => $statusClass,
                'zona' => $zona,
                'zona_class' => $zonaClass,
                'omset' => $this->rupiahShort((float) $row->omset),
                'avg_omset' => $this->rupiahShort($avgOmset),
                'cu' => number_format((int) $row->cu, 0, ',', '.'),
                'avg_cu' => number_format($avgCu, 0, ',', '.'),
                'keperluan' => '-',
            ];
        }

        $kpi['avg_omset'] = $kpi['total'] > 0 ? $kpi['omset'] / $kpi['total'] : 0;
        $kpi['formatted_omset'] = $this->rupiahShort($kpi['omset']);
        $kpi['formatted_avg'] = $this->rupiahShort($kpi['avg_omset']);
        $kpi['formatted_cu'] = number_format($kpi['cu'], 0, ',', '.');

        $years = \App\Models\LaporanBulanan::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values();
        
        $areaData = \App\Models\M_Outlet::leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->select('master_outlets.provinsi', 'master_outlets.kota_kab as kota')
            ->get();

        $kotaByProvinsi = $areaData->groupBy('provinsi')->map(function($items) {
            return $items->pluck('kota')->filter()->map(function($kota) {
                return trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $kota));
            })->unique()->sort()->values();
        });

        // Filter options
        $options = [
            'tahun' => $years,
            'bulan' => ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'],
            'status' => ['Existing', 'Closed', 'New'],
            'provinsi' => $areaData->pluck('provinsi')->filter()->unique()->sort()->values(),
            'kota' => $areaData->pluck('kota')->filter()->map(function($kota) {
                return trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $kota));
            })->unique()->sort()->values(),
            'zona' => ['Z1', 'Z2', 'Z3', 'Z4'],
            'outlet' => \App\Models\M_Outlet::pluck('nama_outlet')->filter()->unique()->sort()->values(),
        ];

        if ($request->has('export')) {
            $filename = "export_outlet_z_" . date('Y-m-d_H-i-s') . ".csv";
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];
            $columns = ['Outlet', 'Kab/Kota', 'Provinsi', 'Zona', 'Status', 'Total Omset', 'Avg Omset', 'Total CU', 'Avg CU'];
            
            $callback = function() use($outlets, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                foreach ($outlets as $row) {
                    fputcsv($file, [
                        $row['nama'],
                        $row['kota'],
                        $row['provinsi'],
                        $row['zona'],
                        $row['status_label'],
                        $row['omset'],
                        $row['avg_omset'],
                        $row['cu'],
                        $row['avg_cu']
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        return view('Marketing.outlet-z', compact('filters', 'outlets', 'kpi', 'options', 'kotaByProvinsi'));
    }

    public function apiGetCityInsights(Request $request)
    {
        $kota = $request->get('kota', '');
        if (empty($kota)) {
            return response()->json(['success' => false, 'message' => 'Kota tidak diset']);
        }

        // Bersihkan nama kota dari prefix Google Maps (Kabupaten, Kota, Regency, dll)
        // Agar pencarian LIKE bisa lebih fleksibel (misal: "Malang" bisa cocok dengan "Kabupaten Malang" atau "Kota Malang")
        $cleanKota = str_ireplace(['kabupaten ', 'kab. ', 'kota ', 'kota administrasi ', ' regency', ' city'], '', $kota);
        $cleanKota = trim($cleanKota);

        // 1. Dapatkan semua outlet di kota tersebut beserta total omset
        $outlets = \App\Models\M_Outlet::join('tbl_area_outlet', 'tbl_outlets.area_id', '=', 'tbl_area_outlet.id')
            ->leftJoin('tbl_laporan_bulanan', 'tbl_outlets.id', '=', 'tbl_laporan_bulanan.outlet_id')
            ->where('tbl_area_outlet.area_kota', 'LIKE', '%' . $cleanKota . '%')
            ->select('tbl_outlets.id', 'tbl_outlets.nama_outlet', 'tbl_outlets.status', \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as omset'))
            ->groupBy('tbl_outlets.id', 'tbl_outlets.nama_outlet', 'tbl_outlets.status')
            ->orderByDesc('omset')
            ->get()
            ->unique('nama_outlet');

        $totalOutlet = $outlets->count();
        
        // Menghitung rata-rata omset PER BULAN (diambil dari rata-rata seluruh laporan bulanan di kota tersebut)
        $avgOmsetMonthRaw = \App\Models\M_Outlet::join('tbl_area_outlet', 'tbl_outlets.area_id', '=', 'tbl_area_outlet.id')
            ->join('tbl_laporan_bulanan', 'tbl_outlets.id', '=', 'tbl_laporan_bulanan.outlet_id')
            ->where('tbl_area_outlet.area_kota', 'LIKE', '%' . $cleanKota . '%')
            ->where('tbl_laporan_bulanan.total_omset', '>', 0)
            ->avg('tbl_laporan_bulanan.total_omset');
            
        $avgOmsetMonth = (float) $avgOmsetMonthRaw;
        $avgOmsetDay = $avgOmsetMonth / 30;

        $topPerformer = $outlets->first();

        // 2. Dapatkan Menu Terlaris di kota tersebut (pendekatan sederhana tanpa filter kota karena keterbatasan join LaporanPareto ke Kota)
        // Sebagai gantinya, kita kembalikan Menu Terlaris secara keseluruhan (atau dummy logic jika belum ada relasi Outlet -> Pareto)
        $topMenu = \App\Models\LaporanPareto::select('item_nama', \Illuminate\Support\Facades\DB::raw('SUM(total_jumlah) as qty'))
            ->where('item_nama', '!=', '__TRANSACTION__')
            ->groupBy('item_nama')
            ->orderByDesc('qty')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'kota' => $kota,
                'total_outlet' => $totalOutlet,
                'avg_omset_month_rp' => $this->rupiahShort($avgOmsetMonth),
                'avg_omset_day_rp' => $this->rupiahShort($avgOmsetDay),
                'avg_omset_raw' => $avgOmsetMonth,
                'top_performer' => $topPerformer ? $topPerformer->nama_outlet : 'Belum Ada',
                'top_menu' => $topMenu ? $topMenu->item_nama : 'Belum Ada Data'
            ]
        ]);
    }

    public function apiGetWarehouses()
    {
        $warehouses = \App\Models\M_Warehouse::leftJoin('tbl_outlets', 'tbl_warehouse.branch_id', '=', 'tbl_outlets.id')
            ->select(
                'tbl_warehouse.id',
                'tbl_warehouse.nama_warehouse',
                'tbl_warehouse.alamat',
                'tbl_outlets.latitude',
                'tbl_outlets.longitude'
            )
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $warehouses
        ]);
    }

    public function menuTerlaris(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'tahun' => (array) $request->get('tahun', [date('Y')]),
            'bulan' => (array) $request->get('bulan', ['All']),
            'outlet' => (array) $request->get('outlet', ['All']),
        ];

        $queryPareto = \App\Models\LaporanPareto::query()
            ->select('item_nama', \Illuminate\Support\Facades\DB::raw('SUM(total_jumlah) as qty'), \Illuminate\Support\Facades\DB::raw('SUM(total_harga) as omset'))
            ->where('item_nama', '!=', '__TRANSACTION__');
            
        $queryEcom = \App\Models\LaporanEcommerce::query()
            ->select('item_varian', \Illuminate\Support\Facades\DB::raw('SUM(total_jumlah) as qty'));

        if (!empty($startDate) && !empty($endDate)) {
            $queryPareto->whereBetween('tanggal', [$startDate, $endDate]);
            $queryEcom->whereBetween('tanggal', [$startDate, $endDate]);
        } else {
            if (!empty($filters['tahun']) && !in_array('All', $filters['tahun'])) {
                $queryPareto->whereIn(\DB::raw('YEAR(tanggal)'), $filters['tahun']);
                $queryEcom->whereIn(\DB::raw('YEAR(tanggal)'), $filters['tahun']);
            }

            if (!empty($filters['bulan']) && !in_array('All', $filters['bulan'])) {
                $months = array_map(function($m) { return date('m', strtotime($m)); }, $filters['bulan']);
                $queryPareto->whereIn(\DB::raw('MONTH(tanggal)'), $months);
                $queryEcom->whereIn(\DB::raw('MONTH(tanggal)'), $months);
            }
        }
        
        if (!empty($filters['outlet']) && !in_array('All', $filters['outlet'])) {
            $queryPareto->whereHas('outlet', function($q) use ($filters) {
                $q->whereIn('nama_outlet', $filters['outlet']);
            });
            $queryEcom->whereHas('outlet', function($q) use ($filters) {
                $q->whereIn('nama_outlet', $filters['outlet']);
            });
        }

        $allPareto = $queryPareto->groupBy('item_nama')->orderByDesc('qty')->get();
        $allEcom = $queryEcom->groupBy('item_varian')->orderByDesc('qty')->get();

        $totalQty = $allPareto->sum('qty');
        $totalOmset = $allPareto->sum('omset');
        
        $menus = $allPareto->map(function($item) {
            return [
                'name' => $item->item_nama,
                'qty' => (int) $item->qty,
                'omset' => $this->rupiahShort((float) $item->omset),
                'omset_raw' => (float) $item->omset,
            ];
        });

        $topMenu = $menus->first();

        $totalEcomQty = $allEcom->sum('qty');
        $channels = $allEcom->map(function($item) use ($totalEcomQty) {
            $qty = (int) $item->qty;
            $percent = $totalEcomQty > 0 ? round(($qty / $totalEcomQty) * 100) : 0;
            return [
                'name' => strtoupper($item->item_varian),
                'qty' => $qty,
                'percent' => $percent,
            ];
        });
        $topChannel = $channels->first();

        $kpi = [
            'total_qty' => number_format($totalQty, 0, ',', '.'),
            'total_omset' => $this->rupiahShort($totalOmset),
            'top_menu_name' => $topMenu ? $topMenu['name'] : '-',
            'top_menu_qty' => $topMenu ? number_format($topMenu['qty'], 0, ',', '.') : '0',
            'channel' => $topChannel ? $topChannel['name'] : '-',
        ];

        $years = \App\Models\LaporanPareto::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values();

        $options = [
            'tahun' => $years,
            'bulan' => ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'],
            'outlet' => \App\Models\M_Outlet::pluck('nama_outlet')->filter()->unique()->sort()->values(),
        ];

        return view('Marketing.menu-terlaris', compact('filters', 'menus', 'channels', 'kpi', 'options'));
    }

    public function contentPosting(Request $request)
    {
        $data = $this->prepareContentPostingData($request, false);
        return view('Marketing.content-posting', $data);
    }

    public function contentPostingReport(Request $request)
    {
        $data = $this->prepareContentPostingData($request, true);
        return view('Marketing.content-posting-report', $data);
    }

    private function prepareContentPostingData(Request $request, $isReport = false)
    {
        $filters = [
            'bulan' => $request->get('bulan', 'All'),
            'creator' => $request->get('creator', 'All'),
            'tanggal' => $request->get('tanggal', 'All'),
            'kategori' => $request->get('kategori', 'All'),
        ];

        $apiSettings = $this->contentPostingGetApiSettings();
        $rows = collect($this->contentPostingRows());

        $filteredRows = $rows
            ->when(($filters['bulan'] ?? 'All') !== 'All', fn($q) => $q->where('bulan', $filters['bulan']))
            ->when(($filters['creator'] ?? 'All') !== 'All', fn($q) => $q->where('creator', $filters['creator']))
            ->when(($filters['tanggal'] ?? 'All') !== 'All', fn($q) => $q->where('tgl', (int) $filters['tanggal']))
            ->when($isReport && ($filters['kategori'] ?? 'All') !== 'All', fn($q) => $q->where('kategori_report', $filters['kategori']))
            ->values();

        $daily = $filteredRows->groupBy(fn($item) => ($item['tanggal'] ?? '') ?: (($item['bulan'] ?? '-') . '-' . ($item['tgl'] ?? '-')))->map(function ($items) {
            $first = $items->first();
            $tgl = (int) ($first['tgl'] ?? 0);
            $bulan = strtoupper((string) ($first['bulan'] ?? '-'));
            $isJune = in_array($bulan, ['JUNE','JUNI'], true);
            $kpi = $isJune ? ($tgl >= 2 ? 6 : 5) : (in_array($tgl, [30,31]) ? 5 : 4);
            $jumlah = $items->where('total', '>', 0)->count();
            return [
                'bulan' => $bulan,
                'tgl' => $tgl,
                'tanggal' => $first['tanggal'] ?? null,
                'creator' => $items->pluck('creator')->filter()->unique()->join(', '),
                'ip_marketing' => $items->pluck('ip_marketing')->filter()->unique()->join(', '),
                'jumlah' => $jumlah,
                'kpi' => $kpi,
                'score' => $kpi > 0 ? round(($jumlah / $kpi) * 100, 2) : 0,
                'items' => $items->values(),
            ];
        })->sortByDesc('tanggal')->values();

        $creatorSummary = $filteredRows->groupBy('creator')->map(fn($items, $creator) => [
            'creator'=>$creator ?: '-', 'konten'=>$items->count(), 'views'=>$items->sum('total'),
            'ig'=>$items->sum('ig_views'), 'tiktok'=>$items->sum('tiktok_views'), 'threads'=>$items->sum('threads_views'), 'x'=>$items->sum('x_views'),
            'likes'=>$items->sum('likes'), 'comments'=>$items->sum('comments'),
        ])->sortByDesc('views')->values();

        $totalKonten = $filteredRows->count();
        $totalViews = $filteredRows->sum('total');
        $totalLikes = $filteredRows->sum('likes');
        $totalComments = $filteredRows->sum('comments');
        $platformTotals = [
            'TikTok' => $filteredRows->sum('tiktok_views'),
            'Instagram' => $filteredRows->sum('ig_views'),
            'Threads' => $filteredRows->sum('threads_views'),
            'X' => $filteredRows->sum('x_views'),
        ];
        
        $totalKpi = $daily->sum('kpi');
        $avgAchieve = $totalKpi ? round(($daily->sum('jumlah') / $totalKpi) * 100, 2) : 0;
        
        $topRows = $filteredRows->sortByDesc('total')->take(8)->values();
        $recentRows = $rows->sortByDesc('created_at')->take(8)->values();
        $creators = $rows->pluck('creator')->filter()->unique()->sort()->values();
        $months = $rows->pluck('bulan')->filter()->unique()->values();
        $dates = $rows->pluck('tgl')->filter()->unique()->sort()->values();
        $categories = collect(['IP','MEDIA','UGC'])->merge($rows->pluck('kategori_report')->filter())->unique()->values();
        
        $apiActive = (!empty($apiSettings['api_utama_enabled']) && !empty($apiSettings['api_utama_token'])) || (!empty($apiSettings['apify_enabled']) && !empty($apiSettings['apify_token']));
        $apiActiveLabel = !empty($apiSettings['apify_enabled']) && !empty($apiSettings['apify_token']) ? 'Apify API Aktif' : (!empty($apiSettings['api_utama_enabled']) && !empty($apiSettings['api_utama_token']) ? 'Omkar API Aktif' : 'API belum aktif');
        
        $isFiltered = (($filters['bulan'] ?? 'All') !== 'All') || (($filters['creator'] ?? 'All') !== 'All') || (($filters['tanggal'] ?? 'All') !== 'All') || ($isReport && ($filters['kategori'] ?? 'All') !== 'All');

        $avgViews = $totalKonten ? round($totalViews / $totalKonten) : 0;

        $perPage = 10;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $sortedFiltered = $filteredRows->sortByDesc('tanggal')->values();
        $paginatedRows = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedFiltered->forPage($page, $perPage),
            $sortedFiltered->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => request()->query()]
        );

        return compact(
            'filters', 'apiSettings', 'filteredRows', 'paginatedRows', 'daily', 'creatorSummary',
            'totalKonten', 'totalViews', 'totalLikes', 'totalComments', 'platformTotals',
            'avgAchieve', 'topRows', 'recentRows', 'creators', 'months', 'dates', 'categories',
            'apiActive', 'apiActiveLabel', 'isFiltered', 'avgViews'
        );
    }

    public function contentPostingSaveApiSettings(Request $request)
    {
        $validated = $request->validate([
            'api_utama_enabled' => ['nullable', 'boolean'],
            'api_utama_base_url' => ['nullable', 'url'],
            'api_utama_token' => ['nullable', 'string', 'max:500'],
            'api_utama_notes' => ['nullable', 'string', 'max:1000'],
            'allowed_ips' => ['nullable', 'string', 'max:3000'],
            'tiktok_api_default' => ['nullable', 'string', 'max:100'],
            'tiktok_api_read_views' => ['nullable', 'boolean'],
            'tiktok_api_reply_comment' => ['nullable', 'boolean'],
            'tiktok_api_upload' => ['nullable', 'boolean'],
            'instagram_meta_enabled' => ['nullable', 'boolean'],
            'instagram_user_id' => ['nullable', 'string', 'max:255'],
            'instagram_page_id' => ['nullable', 'string', 'max:255'],
            'instagram_access_token' => ['nullable', 'string', 'max:1000'],
            'apify_enabled' => ['nullable', 'boolean'],
            'apify_token' => ['nullable', 'string', 'max:1000'],
            'apify_instagram_actor' => ['nullable', 'string', 'max:255'],
            'apify_threads_actor' => ['nullable', 'string', 'max:255'],
            'apify_x_actor' => ['nullable', 'string', 'max:255'],
            'threads_enabled' => ['nullable', 'boolean'],
            'threads_user_id' => ['nullable', 'string', 'max:255'],
            'threads_access_token' => ['nullable', 'string', 'max:1000'],
            'x_enabled' => ['nullable', 'boolean'],
            'x_user_id' => ['nullable', 'string', 'max:255'],
            'x_bearer_token' => ['nullable', 'string', 'max:1000'],
        ]);

        $settings = array_merge($this->contentPostingGetApiSettings(), [
            'api_utama_enabled' => (bool) ($validated['api_utama_enabled'] ?? false),
            'api_utama_base_url' => $validated['api_utama_base_url'] ?? '',
            'api_utama_token' => $validated['api_utama_token'] ?? '',
            'api_utama_notes' => $validated['api_utama_notes'] ?? '',
            'allowed_ips' => collect(preg_split('/\r\n|\r|\n|,/', $validated['allowed_ips'] ?? ''))
                ->map(fn ($ip) => trim($ip))
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'tiktok_api_default' => $validated['tiktok_api_default'] ?? '',
            'tiktok_api_read_views' => (bool) ($validated['tiktok_api_read_views'] ?? false),
            'tiktok_api_reply_comment' => (bool) ($validated['tiktok_api_reply_comment'] ?? false),
            'tiktok_api_upload' => (bool) ($validated['tiktok_api_upload'] ?? false),
            'instagram_meta_enabled' => $request->has('instagram_user_id') || $request->has('instagram_meta_enabled') ? (bool) ($validated['instagram_meta_enabled'] ?? false) : (bool) ($this->contentPostingGetApiSettings()['instagram_meta_enabled'] ?? false),
            'instagram_user_id' => $request->has('instagram_user_id') ? trim((string) ($validated['instagram_user_id'] ?? '')) : ($this->contentPostingGetApiSettings()['instagram_user_id'] ?? ''),
            'instagram_page_id' => $request->has('instagram_page_id') ? trim((string) ($validated['instagram_page_id'] ?? '')) : ($this->contentPostingGetApiSettings()['instagram_page_id'] ?? ''),
            'instagram_access_token' => $request->has('instagram_access_token') ? trim((string) ($validated['instagram_access_token'] ?? '')) : ($this->contentPostingGetApiSettings()['instagram_access_token'] ?? ''),
            'apify_enabled' => $request->has('apify_token') || $request->has('apify_enabled') ? (bool) ($validated['apify_enabled'] ?? false) : (bool) ($this->contentPostingGetApiSettings()['apify_enabled'] ?? false),
            'apify_token' => $request->has('apify_token') ? trim((string) ($validated['apify_token'] ?? '')) : ($this->contentPostingGetApiSettings()['apify_token'] ?? ''),
            'apify_instagram_actor' => $request->has('apify_instagram_actor') ? trim((string) ($validated['apify_instagram_actor'] ?? '')) : ($this->contentPostingGetApiSettings()['apify_instagram_actor'] ?? 'apify/instagram-post-scraper'),
            'apify_threads_actor' => $request->has('apify_threads_actor') ? trim((string) ($validated['apify_threads_actor'] ?? '')) : ($this->contentPostingGetApiSettings()['apify_threads_actor'] ?? 'apify/threads-scraper'),
            'apify_x_actor' => $request->has('apify_x_actor') ? trim((string) ($validated['apify_x_actor'] ?? '')) : ($this->contentPostingGetApiSettings()['apify_x_actor'] ?? 'apidojo/tweet-scraper'),
            'threads_enabled' => $request->has('threads_user_id') || $request->has('threads_enabled') ? (bool) ($validated['threads_enabled'] ?? false) : (bool) ($this->contentPostingGetApiSettings()['threads_enabled'] ?? false),
            'threads_user_id' => $request->has('threads_user_id') ? trim((string) ($validated['threads_user_id'] ?? '')) : ($this->contentPostingGetApiSettings()['threads_user_id'] ?? ''),
            'threads_access_token' => $request->has('threads_access_token') ? trim((string) ($validated['threads_access_token'] ?? '')) : ($this->contentPostingGetApiSettings()['threads_access_token'] ?? ''),
            'x_enabled' => $request->has('x_user_id') || $request->has('x_enabled') ? (bool) ($validated['x_enabled'] ?? false) : (bool) ($this->contentPostingGetApiSettings()['x_enabled'] ?? false),
            'x_user_id' => $request->has('x_user_id') ? trim((string) ($validated['x_user_id'] ?? '')) : ($this->contentPostingGetApiSettings()['x_user_id'] ?? ''),
            'x_bearer_token' => $request->has('x_bearer_token') ? trim((string) ($validated['x_bearer_token'] ?? '')) : ($this->contentPostingGetApiSettings()['x_bearer_token'] ?? ''),
            'updated_at' => now()->toDateTimeString(),
        ]);

        $this->saveContentPostingJson('settings', $settings);

        return back()->with('success', 'Setting API Utama dan IP marketing berhasil disimpan.');
    }

    public function contentPostingStore(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'creator' => ['required', 'string', 'max:100'],
            'ip_marketing' => ['nullable', 'string', 'max:100'],
            'kategori_report' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.judul' => ['nullable', 'string', 'max:255'],
            'items.*.tiktok_link' => ['nullable', 'url', 'max:500'],
            'items.*.ig_link' => ['nullable', 'url', 'max:500'],
            'items.*.threads_link' => ['nullable', 'url', 'max:500'],
            'items.*.x_link' => ['nullable', 'url', 'max:500'],
            'items.*.tiktok_api_type' => ['nullable', 'string', 'max:100'],
            'items.*.activity_upload' => ['nullable', 'boolean'],
            'items.*.activity_reply_comment' => ['nullable', 'boolean'],
            'items.*.manual_views' => ['nullable', 'integer', 'min:0'],
            'items.*.manual_likes' => ['nullable', 'integer', 'min:0'],
            'items.*.manual_comments' => ['nullable', 'integer', 'min:0'],
            'items.*.keterangan' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($validated['items'] as $item) {
            $platformMetrics = $this->readSocialMetrics([
                'tiktok' => $item['tiktok_link'] ?? '',
                'instagram' => $item['ig_link'] ?? '',
                'threads' => $item['threads_link'] ?? '',
                'x' => $item['x_link'] ?? '',
            ]);

            $tiktokMetrics = $platformMetrics['platforms']['tiktok'] ?? [];
            $instagramMetrics = $platformMetrics['platforms']['instagram'] ?? [];
            $threadsMetrics = $platformMetrics['platforms']['threads'] ?? [];
            $xMetrics = $platformMetrics['platforms']['x'] ?? [];

            $useMetrics = (bool) ($platformMetrics['ok'] ?? false);
            $views = $useMetrics ? (int) ($platformMetrics['views'] ?? 0) : (int) ($item['manual_views'] ?? 0);
            $likes = $useMetrics ? (int) ($platformMetrics['likes'] ?? 0) : (int) ($item['manual_likes'] ?? 0);
            $comments = $useMetrics ? (int) ($platformMetrics['comments'] ?? 0) : (int) ($item['manual_comments'] ?? 0);

            $newRow = [
                'id' => (string) Str::uuid(),
                'tanggal' => $validated['tanggal'],
                'bulan' => strtoupper(\Carbon\Carbon::parse($validated['tanggal'])->translatedFormat('F')),
                'tgl' => (int) \Carbon\Carbon::parse($validated['tanggal'])->format('d'),
                'creator' => trim($validated['creator']),
                'ip_marketing' => $validated['ip_marketing'] ?? '',
                'kategori_report' => strtoupper(trim((string) ($validated['kategori_report'] ?? 'IP'))),
                'judul' => ($item['judul'] ?? '') ?: ($useMetrics ? ($platformMetrics['title'] ?? '') : ''),
                'ig' => $item['ig_link'] ?? '',
                'instagram' => $item['ig_link'] ?? '',
                'threads' => $item['threads_link'] ?? '',
                'x' => $item['x_link'] ?? '',
                'tiktok' => $item['tiktok_link'] ?? '',
                'tiktok_api_type' => $item['tiktok_api_type'] ?? '',
                'activity_upload' => (bool) ($item['activity_upload'] ?? false),
                'activity_reply_comment' => (bool) ($item['activity_reply_comment'] ?? false),
                'ig_views' => (int) ($instagramMetrics['views'] ?? 0),
                'tiktok_views' => (int) ($tiktokMetrics['views'] ?? 0),
                'threads_views' => (int) ($threadsMetrics['views'] ?? 0),
                'x_views' => (int) ($xMetrics['views'] ?? 0),
                'likes' => $likes,
                'comments' => $comments,
                'total' => $views,
                'keterangan' => $item['keterangan'] ?? '',
                'metrics_source' => $useMetrics ? ($platformMetrics['source'] ?? 'social_sync') : 'manual',
                'platform_metrics' => $platformMetrics['platforms'] ?? [],
                'created_at' => now()->toDateTimeString(),
            ];

            $this->insertContentPostingRow($newRow);
        }

        return back()->with('success', count($validated['items']) . ' checklist harian content berhasil disimpan.');
    }

    public function contentPostingDestroy(Request $request, $id)
    {
        try {
            DB::table('marketing_content_posting_rows')->where('id', $id)->delete();
        } catch (\Throwable $e) {
            // Fallback ke JSON jika DB error/belum ada
            $rows = $this->readContentPostingJson('rows', []);
            $filtered = array_filter($rows, fn ($r) => ($r['id'] ?? '') !== $id);

            if (count($rows) !== count($filtered)) {
                $path = "marketing/content-posting/rows.json";
                $lockPath = Storage::disk('local')->path("marketing/content-posting/rows.lock");
                $fp = @fopen($lockPath, 'w+');
                if ($fp && flock($fp, LOCK_EX)) {
                    Storage::disk('local')->put(
                        $path,
                        json_encode(array_values($filtered), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    );
                    flock($fp, LOCK_UN);
                    fclose($fp);
                } else {
                    Storage::disk('local')->put(
                        $path,
                        json_encode(array_values($filtered), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    );
                }
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
        }
        return back()->with('success', 'Data content posting berhasil dihapus.');
    }

    public function contentPostingSync(Request $request, $id)
    {
        $rows = $this->readContentPostingJson('rows', []);
        $index = array_search($id, array_column($rows, 'id'));
        $dbRow = null;

        try {
            $dbRow = DB::table('marketing_content_posting_rows')->where('id', $id)->first();
        } catch (\Throwable $e) {}

        if (!$dbRow && $index === false) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.']);
            }
            return back()->with('error', 'Data tidak ditemukan.');
        }

        $rowSource = $dbRow ? (array) $dbRow : $rows[$index];
        // Pastikan kita mengambil kolom yang benar dari DB. Kolom di DB bernama tiktok, ig, threads, x.
        $rowSource['tiktok'] = $rowSource['tiktok'] ?? '';
        $rowSource['ig'] = $rowSource['ig'] ?? ($rowSource['instagram'] ?? '');
        $rowSource['threads'] = $rowSource['threads'] ?? '';
        $rowSource['x'] = $rowSource['x'] ?? '';

        $platformMetrics = $this->readSocialMetrics([
            'tiktok' => $rowSource['tiktok'],
            'instagram' => $rowSource['ig'],
            'threads' => $rowSource['threads'],
            'x' => $rowSource['x'],
        ], true);

        if (!($platformMetrics['ok'] ?? false)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Gagal update: ' .
                     ($platformMetrics['message'] ?? 'API tidak merespon.') . ' Detail: ' . ($platformMetrics['source_detail'] ?? '')
                ]);
            }
            return back()->with('error', 'Gagal update: ' . ($platformMetrics['message'] ?? 'API tidak merespon.'));
        }

        $tiktokMetrics = $platformMetrics['platforms']['tiktok'] ?? [];
        $instagramMetrics = $platformMetrics['platforms']['instagram'] ?? [];
        $threadsMetrics = $platformMetrics['platforms']['threads'] ?? [];
        $xMetrics = $platformMetrics['platforms']['x'] ?? [];

        $updateData = [
            'ig_views' => (int) ($instagramMetrics['views'] ?? 0),
            'tiktok_views' => (int) ($tiktokMetrics['views'] ?? 0),
            'threads_views' => (int) ($threadsMetrics['views'] ?? 0),
            'x_views' => (int) ($xMetrics['views'] ?? 0),
            'likes' => (int) ($platformMetrics['likes'] ?? 0),
            'comments' => (int) ($platformMetrics['comments'] ?? 0),
            'total' => (int) ($platformMetrics['views'] ?? 0),
            'metrics_source' => $platformMetrics['source'] ?? 'social_sync',
        ];

        if (!empty($platformMetrics['title'])) {
            $updateData['judul'] = $platformMetrics['title'];
        }

        if ($dbRow) {
            try {
                $dbUpdateData = $updateData;
                $dbUpdateData['platform_metrics'] = json_encode($platformMetrics['platforms'] ?? []);
                $dbUpdateData['updated_at'] = now()->toDateTimeString();
                DB::table('marketing_content_posting_rows')->where('id', $id)->update($dbUpdateData);
            } catch (\Throwable $e) {}
        } else {
            foreach ($updateData as $k => $v) {
                $rows[$index][$k] = $v;
            }
            $rows[$index]['platform_metrics'] = $platformMetrics['platforms'] ?? [];
            
            $path = "marketing/content-posting/rows.json";
            $lockPath = Storage::disk('local')->path("marketing/content-posting/rows.lock");
            $fp = @fopen($lockPath, 'w+');
            if ($fp && flock($fp, LOCK_EX)) {
                Storage::disk('local')->put(
                    $path,
                    json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true, 
                'message' => 'Metrics berhasil disinkronisasi.',
                'data' => [
                    'views' => number_format($updateData['total'], 0, ',', '.'),
                    'likes' => number_format($updateData['likes'], 0, ',', '.'),
                    'comments' => number_format($updateData['comments'], 0, ',', '.'),
                    'source' => $updateData['metrics_source']
                ]
            ]);
        }
        return back()->with('success', 'Metrics berhasil disinkronisasi.');
    }

    public function contentPostingReadMetrics(Request $request)
    {
        try {
            $links = [
                'tiktok' => trim((string) ($request->get('tiktok_link') ?: $request->get('link') ?: $request->get('url'))),
                'instagram' => trim((string) ($request->get('instagram_link') ?: $request->get('ig_link'))),
                'threads' => trim((string) $request->get('threads_link')),
                'x' => trim((string) ($request->get('x_link') ?: $request->get('twitter_link'))),
            ];

            if (implode('', $links) === '') {
                return response()->json([
                    'ok' => false,
                    'source' => 'manual',
                    'message' => 'Belum ada link sosial media. Silakan isi manual.',
                    'views' => 0,
                    'likes' => 0,
                    'comments' => 0,
                    'title' => '',
                    'platforms' => [],
                ], 200);
            }

            $metrics = $this->readSocialMetrics($links);
            $metrics['message'] = $metrics['ok']
                ? 'Data sosial media berhasil dibaca dari integrasi yang aktif.'
                : 'Belum ada API yang aktif atau semua provider gagal membaca link. Silakan isi manual.';

            return response()->json($metrics, 200);
            } catch (\Throwable $e) {
                return response()->json([
                    'ok' => false,
                    'source' => 'debug',
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ], 200);
            }
    }

    public function dataSalesPerkota(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'tahun' => (array) $request->get('tahun', [date('Y')]),
            'outlet' => (array) $request->get('outlet', ['All']),
            'provinsi' => (array) $request->get('provinsi', ['All']),
            'kota' => (array) $request->get('kota', ['All']),
            'bulan' => (array) $request->get('bulan', ['All']),
            'quarter' => (array) $request->get('quarter', ['All']),
            'search' => $request->get('search', ''),
        ];

        $kotaRaw = "COALESCE(master_outlets.kota_kab, 
            CASE 
                WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' THEN 'Sleman'
                WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' THEN 'Bantul'
                ELSE 'Unidentified Area'
            END
        )";
        $provRaw = "COALESCE(master_outlets.provinsi, 
            CASE 
                WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%DIY%' THEN 'DI Yogyakarta'
                ELSE 'Unidentified Area'
            END
        )";

        $query = \App\Models\LaporanBulanan::query()
            ->join('tbl_outlets', 'tbl_laporan_bulanan.outlet_id', '=', 'tbl_outlets.id')
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->select(
                \Illuminate\Support\Facades\DB::raw("$kotaRaw as kota"),
                \Illuminate\Support\Facades\DB::raw("$provRaw as provinsi"),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT tbl_outlets.id) as jumlah_outlet_aktif'),
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as omset'),
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_cu) as cu')
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('tbl_laporan_bulanan.tanggal', [$filters['start_date'], $filters['end_date']]);
        } else {
            if (!empty($filters['tahun']) && !in_array('All', $filters['tahun'])) {
                $query->whereIn(\DB::raw('YEAR(tbl_laporan_bulanan.tanggal)'), $filters['tahun']);
            }
            if (!empty($filters['bulan']) && !in_array('All', $filters['bulan'])) {
                $months = array_map(function($m) { return date('m', strtotime($m)); }, $filters['bulan']);
                $query->whereIn(\DB::raw('MONTH(tbl_laporan_bulanan.tanggal)'), $months);
            }
            if (!empty($filters['quarter']) && !in_array('All', $filters['quarter'])) {
                $quarters = array_map(function($q) { return str_replace('Q', '', $q); }, $filters['quarter']);
                $query->whereIn(\DB::raw('QUARTER(tbl_laporan_bulanan.tanggal)'), $quarters);
            }
        }

        if (!empty($filters['provinsi']) && !in_array('All', $filters['provinsi'])) {
            $placeholders = implode(',', array_fill(0, count($filters['provinsi']), '?'));
            $query->whereRaw("($provRaw) IN ($placeholders)", $filters['provinsi']);
        }
        
        if (!empty($filters['kota']) && !in_array('All', $filters['kota'])) {
            $query->where(function($q) use ($filters, $kotaRaw) {
                foreach ($filters['kota'] as $k) {
                    $q->orWhereRaw("($kotaRaw) LIKE ?", ['%' . $k . '%']);
                }
            });
        }
        
        if (!empty($filters['outlet']) && !in_array('All', $filters['outlet'])) {
            $query->whereIn('tbl_outlets.nama_outlet', $filters['outlet']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters, $kotaRaw, $provRaw) {
                $q->where('tbl_outlets.nama_outlet', 'like', '%' . $filters['search'] . '%')
                  ->orWhereRaw("($kotaRaw) LIKE ?", ['%' . $filters['search'] . '%'])
                  ->orWhereRaw("($provRaw) LIKE ?", ['%' . $filters['search'] . '%']);
            });
        }

        // Clean zeroes
        $query->where('tbl_laporan_bulanan.total_omset', '>', 0);

        // Calculate Totals before grouping
        $baseQuery = clone $query;
        $baseQuery->select(
            \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as total_omzet'), 
            \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_cu) as total_cu')
        );
        $totals = $baseQuery->first();
        
        $totalOmzet = $totals->total_omzet ?? 0;
        $totalCu = $totals->total_cu ?? 0;
        $avgBasket = $totalCu > 0 ? round($totalOmzet / $totalCu) : 0;
        
        // Apply Grouping
        $query->groupBy(
            \Illuminate\Support\Facades\DB::raw($provRaw),
            \Illuminate\Support\Facades\DB::raw($kotaRaw)
        );

        $citiesResult = clone $query;
        $allCities = $citiesResult->get();
        $jumlahData = $allCities->count();
        $maxVal = $allCities->max('omset') ?: 1;

        $paginator = $query->orderByDesc(\Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset)'))->paginate(20)->appends($request->all());

        $paginator->getCollection()->transform(function($row) use ($maxVal) {
            $row->skor = round(($row->omset / $maxVal) * 100);
            if ($row->kota) {
                $row->kota = trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $row->kota));
            }
            $row->avg_basket = $row->cu > 0 ? round($row->omset / $row->cu) : 0;
            return $row;
        });

        $snapshot = [
            'total_omzet' => $this->rupiahShort($totalOmzet),
            'total_cu' => number_format($totalCu, 0, ',', '.'),
            'avg_basket' => $this->rupiah($avgBasket),
            'jumlah_data' => $jumlahData,
            'status_sheet' => 'Connected',
        ];

        $years = \App\Models\LaporanBulanan::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values();
        $areaData = \App\Models\M_Outlet::leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->select(
                'tbl_outlets.nama_outlet as outlet', 
                \Illuminate\Support\Facades\DB::raw("$provRaw as provinsi"), 
                \Illuminate\Support\Facades\DB::raw("$kotaRaw as kota")
            )
            ->get();

        $hierarchy = $areaData->map(function($item) {
            $cleanedKota = $item->kota ? trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $item->kota)) : '';
            return [
                'outlet' => $item->outlet,
                'provinsi' => $item->provinsi,
                'kota' => $cleanedKota
            ];
        });

        $kotaByProvinsi = $areaData->groupBy('provinsi')->map(function($items) {
            return $items->pluck('kota')->filter()->map(function($kota) {
                return trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $kota));
            })->unique()->sort()->values();
        });

        $options = [
            'tahun' => $years,
            'outlet' => \App\Models\M_Outlet::pluck('nama_outlet')->filter()->unique()->sort()->values(),
            'provinsi' => $areaData->pluck('provinsi')->filter()->unique()->sort()->values(),
            'kota' => $areaData->pluck('kota')->filter()->map(function($kota) {
                return trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $kota));
            })->unique()->sort()->values(),
            'bulan' => collect(['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']),
            'quarter' => collect(['Q1','Q2','Q3','Q4']),
        ];

        return view('Marketing.data-sales-perkota', compact('filters', 'paginator', 'snapshot', 'options', 'kotaByProvinsi', 'hierarchy'));
    }

    public function dataSalesProvinsi(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'tahun' => (array) $request->get('tahun', [date('Y')]),
            'provinsi' => (array) $request->get('provinsi', ['All']),
            'bulan' => (array) $request->get('bulan', ['All']),
            'quarter' => (array) $request->get('quarter', ['All']),
            'search' => $request->get('search', ''),
        ];

        $provRaw = "COALESCE(master_outlets.provinsi, 
            CASE 
                WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%DIY%' THEN 'DI Yogyakarta'
                ELSE 'Unidentified Area'
            END
        )";

        $query = \App\Models\LaporanBulanan::query()
            ->join('tbl_outlets', 'tbl_laporan_bulanan.outlet_id', '=', 'tbl_outlets.id')
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->select(
                \Illuminate\Support\Facades\DB::raw("$provRaw as provinsi"),
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT tbl_outlets.id) as jumlah_outlet_aktif'),
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as omset'),
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_cu) as cu')
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('tbl_laporan_bulanan.tanggal', [$filters['start_date'], $filters['end_date']]);
        } else {
            if (!empty($filters['tahun']) && !in_array('All', $filters['tahun'])) {
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('YEAR(tbl_laporan_bulanan.tanggal)'), $filters['tahun']);
            }
            if (!empty($filters['bulan']) && !in_array('All', $filters['bulan'])) {
                $months = array_map(function($m) { return date('m', strtotime($m)); }, $filters['bulan']);
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('MONTH(tbl_laporan_bulanan.tanggal)'), $months);
            }
            if (!empty($filters['quarter']) && !in_array('All', $filters['quarter'])) {
                $quarters = array_map(function($q) { return str_replace('Q', '', $q); }, $filters['quarter']);
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('QUARTER(tbl_laporan_bulanan.tanggal)'), $quarters);
            }
        }

        if (!empty($filters['provinsi']) && !in_array('All', $filters['provinsi'])) {
            $placeholders = implode(',', array_fill(0, count($filters['provinsi']), '?'));
            $query->whereRaw("($provRaw) IN ($placeholders)", $filters['provinsi']);
        }
        
        if (!empty($filters['search'])) {
            $query->whereRaw("($provRaw) LIKE ?", ['%' . $filters['search'] . '%']);
        }

        // Clean zeroes
        $query->where('tbl_laporan_bulanan.total_omset', '>', 0);

        // Calculate Totals before grouping
        $baseQuery = clone $query;
        $baseQuery->select(
            \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as total_omzet'), 
            \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_cu) as total_cu')
        );
        $totals = $baseQuery->first();
        
        $totalOmzet = $totals->total_omzet ?? 0;
        $totalCu = $totals->total_cu ?? 0;
        $avgBasket = $totalCu > 0 ? round($totalOmzet / $totalCu) : 0;
        
        // Apply Grouping
        $query->groupBy(
            \Illuminate\Support\Facades\DB::raw($provRaw)
        );

        $provResult = clone $query;
        $allProv = $provResult->get();
        $jumlahData = $allProv->count();
        $maxVal = $allProv->max('omset') ?: 1;

        $paginator = $query->orderByDesc(\Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset)'))->paginate(20)->appends($request->all());

        $paginator->getCollection()->transform(function($row) use ($maxVal) {
            $row->skor = round(($row->omset / $maxVal) * 100);
            $row->avg_basket = $row->cu > 0 ? round($row->omset / $row->cu) : 0;
            return $row;
        });

        $snapshot = [
            'total_omzet' => $this->rupiahShort($totalOmzet),
            'total_cu' => number_format($totalCu, 0, ',', '.'),
            'avg_basket' => $this->rupiah($avgBasket),
            'jumlah_data' => $jumlahData,
            'status_sheet' => 'Connected',
        ];

        $years = \App\Models\LaporanBulanan::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values();
        $areaData = \App\Models\M_Outlet::leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->select(
                \Illuminate\Support\Facades\DB::raw("$provRaw as provinsi")
            )
            ->get();

        $options = [
            'tahun' => $years,
            'provinsi' => $areaData->pluck('provinsi')->filter()->unique()->sort()->values(),
            'bulan' => collect(['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']),
            'quarter' => collect(['Q1','Q2','Q3','Q4']),
        ];

        return view('Marketing.data-sales-provinsi', compact('filters', 'paginator', 'snapshot', 'options'));
    }

    public function anomaliKota(Request $request)
    {
        $filters = [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'tahun' => (array) $request->get('tahun', [date('Y')]),
            'bulan' => (array) $request->get('bulan', ['All']),
            'quarter' => (array) $request->get('quarter', ['All']),
            'search' => $request->get('search', ''),
        ];

        $provRaw = "COALESCE(master_outlets.provinsi, 
            CASE 
                WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' OR UPPER(tbl_outlets.nama_outlet) LIKE '%DIY%' THEN 'DI Yogyakarta'
                ELSE 'Unidentified Area'
            END
        )";

        $kotaRaw = "COALESCE(master_outlets.kota_kab, 
            CASE 
                WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%SLEMAN%' THEN 'Kabupaten Sleman'
                WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%BANTUL%' THEN 'Kabupaten Bantul'
                WHEN UPPER(tbl_outlets.nama_outlet) LIKE '%DIY%' THEN 'Kota Yogyakarta'
                ELSE 'Unidentified Area'
            END
        )";

        $query = \App\Models\LaporanBulanan::query()
            ->join('tbl_outlets', 'tbl_laporan_bulanan.outlet_id', '=', 'tbl_outlets.id')
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->select(
                'tbl_outlets.nama_outlet',
                \Illuminate\Support\Facades\DB::raw("$provRaw as provinsi"),
                \Illuminate\Support\Facades\DB::raw("$kotaRaw as kota"),
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as omset'),
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_cu) as cu')
            );

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('tbl_laporan_bulanan.tanggal', [$filters['start_date'], $filters['end_date']]);
        } else {
            if (!empty($filters['tahun']) && !in_array('All', $filters['tahun'])) {
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('YEAR(tbl_laporan_bulanan.tanggal)'), $filters['tahun']);
            }
            if (!empty($filters['bulan']) && !in_array('All', $filters['bulan'])) {
                $months = array_map(function($m) { return date('m', strtotime($m)); }, $filters['bulan']);
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('MONTH(tbl_laporan_bulanan.tanggal)'), $months);
            }
            if (!empty($filters['quarter']) && !in_array('All', $filters['quarter'])) {
                $quarters = array_map(function($q) { return str_replace('Q', '', $q); }, $filters['quarter']);
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('QUARTER(tbl_laporan_bulanan.tanggal)'), $quarters);
            }
        }

        if (!empty($filters['search'])) {
            $query->where('tbl_outlets.nama_outlet', 'LIKE', '%' . $filters['search'] . '%');
        }

        // Clean zeroes
        $query->where('tbl_laporan_bulanan.total_omset', '>', 0);
        
        // ONLY ANOMALY
        $query->whereRaw("(($provRaw) = 'Unidentified Area' OR ($kotaRaw) = 'Unidentified Area')");

        // Calculate Totals before grouping
        $baseQuery = clone $query;
        $baseQuery->select(
            \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT tbl_outlets.id) as total_outlet_anomali'),
            \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as total_omzet'), 
            \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_cu) as total_cu')
        );
        $totals = $baseQuery->first();
        
        $totalOmzet = $totals->total_omzet ?? 0;
        $totalCu = $totals->total_cu ?? 0;
        $totalOutlet = $totals->total_outlet_anomali ?? 0;
        $avgBasket = $totalCu > 0 ? round($totalOmzet / $totalCu) : 0;
        
        // Apply Grouping
        $query->groupBy(
            'tbl_outlets.nama_outlet',
            \Illuminate\Support\Facades\DB::raw($provRaw),
            \Illuminate\Support\Facades\DB::raw($kotaRaw)
        );

        $paginator = $query->orderByDesc(\Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset)'))->paginate(50)->appends($request->all());

        $snapshot = [
            'total_omzet' => $this->rupiahShort($totalOmzet),
            'total_cu' => number_format($totalCu, 0, ',', '.'),
            'avg_basket' => $this->rupiah($avgBasket),
            'jumlah_data' => $totalOutlet,
            'status_sheet' => 'Connected',
        ];

        $years = \App\Models\LaporanBulanan::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values();
        
        $options = [
            'tahun' => $years,
            'bulan' => collect(['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC']),
            'quarter' => collect(['Q1','Q2','Q3','Q4']),
        ];

        return view('Marketing.anomali-kota', compact('filters', 'paginator', 'snapshot', 'options'));
    }

    private function isActiveFilter($value)
    {
        return ! in_array(strtolower(trim((string) $value)), ['', 'all'], true);
    }

    private function sameText($left, $right)
    {
        return strtolower(trim((string) $left)) === strtolower(trim((string) $right));
    }

    private function numberOnly($value)
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        if (preg_match('/^-?[0-9]+(\.[0-9]+)?e[+-]?[0-9]+$/i', $value)) {
            return (float) $value;
        }

        $value = str_replace(['Rp', 'rp', ' '], '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '.', $value);
        }

        return (float) preg_replace('/[^0-9.-]/', '', $value);
    }

    private function rupiah($value)
    {
        return 'Rp' . number_format((float) $value, 0, ',', '.');
    }

    private function rupiahShort($value)
    {
        $value = (float) $value;

        if ($value >= 1000000000) {
            return 'Rp' . number_format($value / 1000000000, 2, ',', '.') . ' M';
        }

        if ($value >= 1000000) {
            return 'Rp' . number_format($value / 1000000, 2, ',', '.') . ' Jt';
        }

        return $this->rupiah($value);
    }

    public function contentPostingApiSettings(Request $request)
    {
        $apiSettings = $this->contentPostingGetApiSettings();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'settings' => $apiSettings,
            ], 200);
        }

        return view('Marketing.content-posting-settings', compact('apiSettings'));
    }

    private function contentPostingGetApiSettings()
    {
        $defaults = [
            'api_utama_enabled' => false,
            'api_utama_base_url' => 'https://tiktok-scraper.omkar.cloud/tiktok/videos/details',
            'api_utama_token' => '',
            'api_utama_notes' => '',
            'allowed_ips' => [],
            'tiktok_api_default' => '',
            'tiktok_api_read_views' => true,
            'tiktok_api_reply_comment' => false,
            'tiktok_api_upload' => false,
            'instagram_meta_enabled' => false,
            'instagram_user_id' => '',
            'instagram_page_id' => '',
            'instagram_access_token' => '',
            'apify_enabled' => false,
            'apify_token' => '',
            'apify_instagram_actor' => 'apify/instagram-post-scraper',
            'apify_threads_actor' => 'apify/threads-scraper',
            'apify_x_actor' => 'apidojo/tweet-scraper',
            'threads_enabled' => false,
            'threads_user_id' => '',
            'threads_access_token' => '',
            'x_enabled' => false,
            'x_user_id' => '',
            'x_bearer_token' => '',
            'updated_at' => null,
        ];

        $saved = $this->readContentPostingJson('settings', []);

        if (! is_array($saved)) {
            $saved = [];
        }

        $saved['allowed_ips'] = collect($saved['allowed_ips'] ?? [])
            ->map(fn ($ip) => trim((string) $ip))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Kompatibilitas kalau file settings pernah dibuat manual dengan nama key lama.
        if (isset($saved['omkar_enabled']) && ! isset($saved['api_utama_enabled'])) {
            $saved['api_utama_enabled'] = (bool) $saved['omkar_enabled'];
        }
        if (isset($saved['omkar_api_key']) && empty($saved['api_utama_token'])) {
            $saved['api_utama_token'] = (string) $saved['omkar_api_key'];
        }
        if (isset($saved['instagram_actor']) && empty($saved['apify_instagram_actor'])) {
            $saved['apify_instagram_actor'] = (string) $saved['instagram_actor'];
        }
        if (isset($saved['threads_actor']) && empty($saved['apify_threads_actor'])) {
            $saved['apify_threads_actor'] = (string) $saved['threads_actor'];
        }
        if (isset($saved['x_actor']) && empty($saved['apify_x_actor'])) {
            $saved['apify_x_actor'] = (string) $saved['x_actor'];
        }
        if (isset($saved['ip_list']) && empty($saved['allowed_ips']) && is_array($saved['ip_list'])) {
            $saved['allowed_ips'] = $saved['ip_list'];
        }

        return array_merge($defaults, $saved);
    }

    private function contentPostingRows()
    {
        $rows = $this->readContentPostingJson('rows', []);

        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->map(function ($row) {
                $row['tanggal'] = $row['tanggal'] ?? '';
                $row['bulan'] = strtoupper((string) ($row['bulan'] ?? ''));
                $row['tgl'] = (int) ($row['tgl'] ?? 0);
                $row['creator'] = trim((string) ($row['creator'] ?? ''));
                $row['ip_marketing'] = trim((string) ($row['ip_marketing'] ?? ''));
                $row['kategori_report'] = strtoupper(trim((string) ($row['kategori_report'] ?? 'IP')));
                $row['judul'] = trim((string) ($row['judul'] ?? ''));
                $row['ig'] = trim((string) ($row['ig'] ?? ($row['instagram'] ?? '')));
                $row['instagram'] = trim((string) ($row['instagram'] ?? $row['ig']));
                $row['threads'] = trim((string) ($row['threads'] ?? ''));
                $row['x'] = trim((string) ($row['x'] ?? ''));
                $row['tiktok'] = trim((string) ($row['tiktok'] ?? ''));
                $row['tiktok_api_type'] = trim((string) ($row['tiktok_api_type'] ?? ''));
                $row['ig_views'] = (int) ($row['ig_views'] ?? 0);
                $row['threads_views'] = (int) ($row['threads_views'] ?? 0);
                $row['x_views'] = (int) ($row['x_views'] ?? 0);
                $row['tiktok_views'] = (int) ($row['tiktok_views'] ?? 0);
                $row['likes'] = (int) ($row['likes'] ?? 0);
                $row['comments'] = (int) ($row['comments'] ?? 0);
                $row['total'] = (int) ($row['total'] ?? ($row['ig_views'] + $row['tiktok_views']));
                $row['keterangan'] = trim((string) ($row['keterangan'] ?? ''));
                $row['metrics_source'] = trim((string) ($row['metrics_source'] ?? 'manual'));

                return $row;
            })
            ->values()
            ->all();
    }

    private function readTikTokMetrics($link)
    {
        $link = trim((string) $link);

        $empty = [
            'ok' => false,
            'source' => 'manual',
            'title' => '',
            'views' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
        ];

        if ($link === '' || ! preg_match('/^https?:\/\/(www\.)?tiktok\.com\//i', $link)) {
            return $empty;
        }

        $settings = $this->contentPostingGetApiSettings();

        if (empty($settings['api_utama_enabled']) || empty($settings['api_utama_token'])) {
            return array_merge($empty, ['source' => 'manual_no_api_key']);
        }

        $endpoint = trim((string) ($settings['api_utama_base_url'] ?? ''));
        if ($endpoint === '') {
            $endpoint = 'https://tiktok-scraper.omkar.cloud/tiktok/videos/details';
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'API-Key' => trim((string) $settings['api_utama_token']),
                ])
                ->get($endpoint, [
                    'video_url' => $link,
                ]);

            if (! $response->successful()) {
                return array_merge($empty, [
                    'source' => 'omkar_error_' . $response->status(),
                    'api_status' => $response->status(),
                ]);
            }

            $json = $response->json();

            if (! is_array($json)) {
                return array_merge($empty, ['source' => 'omkar_invalid_json']);
            }

            $data = $json['data'] ?? $json;
            $stats = $data['stats'] ?? [];

            return [
                'ok' => true,
                'source' => 'omkar_tiktok_scraper',
                'title' => (string) ($data['caption'] ?? $data['title'] ?? $data['desc'] ?? ''),
                'views' => (int) $this->numberOnly($stats['views'] ?? $data['views'] ?? $data['play_count'] ?? 0),
                'likes' => (int) $this->numberOnly($stats['likes'] ?? $data['likes'] ?? $data['digg_count'] ?? 0),
                'comments' => (int) $this->numberOnly($stats['comments'] ?? $data['comments'] ?? $data['comment_count'] ?? 0),
                'shares' => (int) $this->numberOnly($stats['shares'] ?? $data['shares'] ?? $data['share_count'] ?? 0),
            ];
        } catch (\Throwable $e) {
            return array_merge($empty, ['source' => 'omkar_exception']);
        }
    }

    private function readSocialMetrics(array $links, $force = false)
    {
        /*
         * Redis cache + lock.
         * Tujuan: satu link yang sama tidak menembak Omkar/Apify berkali-kali saat user spam klik
         * atau beberapa user membaca metrik bersamaan. Kalau Redis belum aktif, helper akan fallback
         * ke cache default Laravel supaya aplikasi tetap jalan.
         */
        $platforms = [];

        $platforms['tiktok'] = $this->readSocialMetricCached('tiktok', $links['tiktok'] ?? '', function () use ($links) {
            return $this->readTikTokMetrics($links['tiktok'] ?? '');
        }, $force);

        $platforms['instagram'] = $this->readSocialMetricCached('instagram', $links['instagram'] ?? '', function () use ($links) {
            return $this->readInstagramMetrics($links['instagram'] ?? '');
        }, $force);

        $platforms['threads'] = $this->readSocialMetricCached('threads', $links['threads'] ?? '', function () use ($links) {
            return $this->readThreadsMetrics($links['threads'] ?? '');
        }, $force);

        $platforms['x'] = $this->readSocialMetricCached('x', $links['x'] ?? '', function () use ($links) {
            return $this->readXMetrics($links['x'] ?? '');
        }, $force);

        $valid = collect($platforms)->filter(fn ($row) => (bool) ($row['ok'] ?? false));

        return [
            'ok' => $valid->count() > 0,
            'source' => $valid->pluck('source')->filter()->implode('+') ?: 'manual',
            'source_detail' => $this->formatSocialPlatformSources($platforms),
            'title' => (string) ($valid->pluck('title')->filter()->first() ?? ''),
            'views' => (int) $valid->sum(fn ($row) => (int) ($row['views'] ?? 0)),
            'likes' => (int) $valid->sum(fn ($row) => (int) ($row['likes'] ?? 0)),
            'comments' => (int) $valid->sum(fn ($row) => (int) ($row['comments'] ?? 0)),
            'shares' => (int) $valid->sum(fn ($row) => (int) ($row['shares'] ?? 0)),
            'platforms' => $platforms,
        ];
    }

    private function formatSocialPlatformSources(array $platforms)
    {
        return collect($platforms)
            ->map(function ($row, $platform) {
                $status = ! empty($row['ok']) ? 'ok' : 'fail';
                $source = trim((string) ($row['source'] ?? 'manual'));
                return $platform . ':' . $status . ':' . ($source !== '' ? $source : 'manual');
            })
            ->values()
            ->implode(' | ');
    }

    private function readSocialMetricCached($platform, $link, \Closure $resolver, $force = false)
    {
        $platform = strtolower(trim((string) $platform));
        $link = trim((string) $link);

        // Link kosong tidak perlu masuk Redis supaya cache tidak penuh oleh request kosong.
        if ($link === '') {
            return $resolver();
        }

        $cacheKey = $this->socialMetricCacheKey($platform, $link);
        $lockKey = $cacheKey . ':lock';
        $cache = $this->socialMetricCacheStore();

        if ($force) {
            try { $cache->forget($cacheKey); } catch (\Throwable $e) {}
        }

        try {
            $cached = $cache->get($cacheKey);
            if (is_array($cached)) {
                $cached['cache_hit'] = true;
                return $cached;
            }
        } catch (\Throwable $e) {
            return $resolver();
        }

        try {
            $lock = $cache->lock($lockKey, 90);

            if (! $lock->get()) {
                // Jangan blok request lama-lama. Tunggu sebentar, ambil hasil request pertama jika sudah ada.
                usleep(500000);
                $cached = $cache->get($cacheKey);
                if (is_array($cached)) {
                    $cached['cache_hit'] = true;
                    $cached['cache_waited'] = true;
                    return $cached;
                }

                return $this->emptySocialMetric($platform . '_busy_try_again');
            }

            try {
                $metric = $resolver();
                if (! is_array($metric)) {
                    $metric = $this->emptySocialMetric($platform . '_invalid_metric');
                }

                $metric['cache_hit'] = false;
                $metric['cached_at'] = now()->toDateTimeString();

                // Sukses lebih lama. Error Apify 404/502 jangan disimpan lama agar setelah ganti actor bisa langsung dicoba ulang.
                $source = strtolower((string) ($metric['source'] ?? ''));
                if (! empty($metric['ok'])) {
                    $ttl = now()->addMinutes(30);
                } elseif (str_contains($source, '_error_404') || str_contains($source, '_error_502')) {
                    $ttl = now()->addSeconds(30);
                } else {
                    $ttl = now()->addMinutes(2);
                }
                $cache->put($cacheKey, $metric, $ttl);

                return $metric;
            } finally {
                optional($lock)->release();
            }
        } catch (\Throwable $e) {
            $metric = $resolver();
            if (is_array($metric)) {
                $metric['cache_error'] = 'redis_unavailable';
                return $metric;
            }

            return $this->emptySocialMetric($platform . '_cache_exception');
        }
    }

    private function socialMetricCacheStore()
    {
        try {
            return Cache::store('redis');
        } catch (\Throwable $e) {
            return Cache::store(config('cache.default'));
        }
    }

    private function socialMetricCacheKey($platform, $link)
    {
        $settings = $this->contentPostingGetApiSettings();
        $fingerprint = hash('sha256', json_encode([
            'settings_updated_at' => $settings['updated_at'] ?? '',
            'api_utama_enabled' => (bool) ($settings['api_utama_enabled'] ?? false),
            'api_utama_base_url' => $settings['api_utama_base_url'] ?? '',
            'api_utama_token_hash' => hash('sha256', (string) ($settings['api_utama_token'] ?? '')),
            'instagram_meta_enabled' => (bool) ($settings['instagram_meta_enabled'] ?? false),
            'instagram_user_id' => $settings['instagram_user_id'] ?? '',
            'instagram_access_token_hash' => hash('sha256', (string) ($settings['instagram_access_token'] ?? '')),
            'apify_enabled' => (bool) ($settings['apify_enabled'] ?? false),
            'apify_token_hash' => hash('sha256', (string) ($settings['apify_token'] ?? '')),
            'apify_instagram_actor' => $settings['apify_instagram_actor'] ?? '',
            'apify_threads_actor' => $settings['apify_threads_actor'] ?? '',
            'apify_x_actor' => $settings['apify_x_actor'] ?? '',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return 'marketing:content_posting:social_metric:' . $platform . ':' . sha1($this->normalizeSocialUrl($link) . '|' . $fingerprint);
    }

    private function emptySocialMetric($source = 'manual')
    {
        return [
            'ok' => false,
            'source' => $source,
            'title' => '',
            'views' => 0,
            'likes' => 0,
            'comments' => 0,
            'shares' => 0,
        ];
    }

    private function readInstagramMetrics($link)
    {
        $link = trim((string) $link);
        if ($link === '' || ! preg_match('/^https?:\/\/(www\.)?instagram\.com\//i', $link)) {
            return $this->emptySocialMetric('instagram_empty');
        }

        $settings = $this->contentPostingGetApiSettings();

        /*
         * Jalur utama Instagram: Apify.
         * Actor apify/instagram-post-scraper pada server ini sudah terbukti berhasil lewat curl terminal
         * dengan payload username berisi URL post/reel. Karena itu controller dipaksa memakai format ini
         * terlebih dahulu, tanpa fallback ke actor lama apify/instagram-scraper yang sering 404.
         */
        if (! empty($settings['apify_enabled']) && ! empty($settings['apify_token'])) {
            $actorUsed = $this->normalizeApifyActorId($settings['apify_instagram_actor'] ?? 'apify/instagram-post-scraper');
            if ($actorUsed === '' || $actorUsed === 'apify/instagram-scraper') {
                $actorUsed = 'apify/instagram-post-scraper';
            }

            $items = $this->runApifyActor($actorUsed, [
                'username' => [$link],
                'resultsLimit' => 1,
                'dataDetailLevel' => 'detailedData',
            ], $settings['apify_token']);

            $item = $this->findApifyItemByUrl($items, $link) ?: (is_array($items[0] ?? null) ? $items[0] : null);

            if (! is_array($item)) {
                return $this->emptySocialMetric('apify_instagram_no_result_' . str_replace('/', '~', $actorUsed));
            }

            if ($this->isApifyErrorItem($item)) {
                return $this->emptySocialMetric('apify_instagram_error_' . $this->apifyErrorCode($item) . '_' . str_replace('/', '~', $actorUsed));
            }

            $title = (string) ($item['caption'] ?? $item['description'] ?? $item['text'] ?? '');
            $views = (int) $this->numberOnly($item['videoPlayCount'] ?? $item['videoViewCount'] ?? $item['viewsCount'] ?? $item['views'] ?? $item['playCount'] ?? $item['viewCount'] ?? 0);
            $likes = (int) $this->numberOnly($item['likesCount'] ?? $item['likeCount'] ?? $item['likes'] ?? 0);
            $comments = (int) $this->numberOnly($item['commentsCount'] ?? $item['commentCount'] ?? $item['comments'] ?? 0);
            $shares = (int) $this->numberOnly($item['sharesCount'] ?? $item['shareCount'] ?? $item['shares'] ?? 0);

            if ($this->looksLikeGatewayError($title)) {
                return $this->emptySocialMetric('apify_instagram_error_502_' . str_replace('/', '~', $actorUsed));
            }

            return [
                'ok' => ($views + $likes + $comments + $shares) > 0 || $title !== '',
                'source' => 'apify_instagram_' . str_replace('/', '~', $actorUsed),
                'title' => $title,
                'views' => $views,
                'likes' => $likes,
                'comments' => $comments,
                'shares' => $shares,
                'raw_url' => (string) ($item['url'] ?? $item['inputUrl'] ?? $item['permalink'] ?? ''),
                'raw_username' => (string) ($item['ownerUsername'] ?? $item['username'] ?? ''),
                'raw_shortcode' => (string) ($item['shortCode'] ?? $item['shortcode'] ?? ''),
            ];
        }

        /*
         * Fallback resmi Meta Graph. Ini hanya bisa membaca media milik akun IG bisnis sendiri
         * yang terhubung dengan token, bukan semua link publik.
         */
        if (empty($settings['instagram_meta_enabled']) || empty($settings['instagram_user_id']) || empty($settings['instagram_access_token'])) {
            return $this->emptySocialMetric('instagram_no_api');
        }

        try {
            $token = trim((string) $settings['instagram_access_token']);
            $igUserId = trim((string) $settings['instagram_user_id']);
            $response = Http::timeout(30)->acceptJson()->get("https://graph.facebook.com/v22.0/{$igUserId}/media", [
                'fields' => 'id,caption,media_type,permalink,timestamp,like_count,comments_count',
                'limit' => 50,
                'access_token' => $token,
            ]);

            if (! $response->successful()) {
                return $this->emptySocialMetric('instagram_error_' . $response->status());
            }

            $target = $this->normalizeSocialUrl($link);
            $media = collect($response->json('data') ?? [])->first(function ($item) use ($target) {
                $permalink = $this->normalizeSocialUrl((string) ($item['permalink'] ?? ''));
                return $permalink !== '' && $permalink === $target;
            });

            if (! is_array($media)) {
                return $this->emptySocialMetric('instagram_not_found');
            }

            $views = 0;
            $insight = Http::timeout(30)->acceptJson()->get('https://graph.facebook.com/v22.0/' . $media['id'] . '/insights', [
                'metric' => 'views,reach,plays,saved,shares',
                'access_token' => $token,
            ]);
            if ($insight->successful()) {
                $metrics = collect($insight->json('data') ?? []);
                $viewsMetric = $metrics->firstWhere('name', 'views') ?: $metrics->firstWhere('name', 'plays') ?: $metrics->firstWhere('name', 'reach');
                $views = (int) ($viewsMetric['values'][0]['value'] ?? 0);
            }

            return [
                'ok' => true,
                'source' => 'instagram_meta_graph',
                'title' => (string) ($media['caption'] ?? ''),
                'views' => $views,
                'likes' => (int) ($media['like_count'] ?? 0),
                'comments' => (int) ($media['comments_count'] ?? 0),
                'shares' => 0,
            ];
        } catch (\Throwable $e) {
            return $this->emptySocialMetric('instagram_exception');
        }
    }

    private function readThreadsMetrics($link)
    {
        $link = trim((string) $link);
        if ($link === '' || ! preg_match('/^https?:\/\/(www\.)?threads\.(net|com)\//i', $link)) {
            return $this->emptySocialMetric('threads_empty');
        }

        $settings = $this->contentPostingGetApiSettings();
        if (empty($settings['apify_enabled']) || empty($settings['apify_token'])) {
            return $this->emptySocialMetric('threads_no_apify');
        }

        $actorId = $settings['apify_threads_actor'] ?: 'apify/threads-scraper';
        $items = $this->runApifyActorVariants($actorId, $this->buildApifyThreadsInputs($link), $settings['apify_token']);

        $item = $this->findApifyItemByUrl($items, $link) ?: (is_array($items[0] ?? null) ? $items[0] : null);
        if (! is_array($item)) {
            return $this->emptySocialMetric('apify_threads_no_result_' . str_replace('/', '~', $actorId));
        }

        if ($this->isApifyErrorItem($item)) {
            return $this->emptySocialMetric('apify_threads_error_' . $this->apifyErrorCode($item));
        }

        $title = (string) ($item['text'] ?? $item['caption'] ?? $item['description'] ?? '');
        $views = (int) $this->numberOnly($item['viewCount'] ?? $item['viewsCount'] ?? $item['views'] ?? 0);
        $likes = (int) $this->numberOnly($item['likeCount'] ?? $item['likesCount'] ?? $item['likes'] ?? 0);
        $comments = (int) $this->numberOnly($item['replyCount'] ?? $item['repliesCount'] ?? $item['commentsCount'] ?? $item['comments'] ?? 0);
        $shares = (int) $this->numberOnly($item['repostCount'] ?? $item['quoteCount'] ?? $item['shareCount'] ?? $item['shares'] ?? 0);

        if ($this->looksLikeGatewayError($title)) {
            return $this->emptySocialMetric('apify_threads_error_502');
        }

        return [
            'ok' => ($views + $likes + $comments + $shares) > 0 || $title !== '',
            'source' => 'apify_threads',
            'title' => $title,
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'raw_url' => (string) ($item['url'] ?? ''),
        ];
    }

    private function readXMetrics($link)
    {
        $link = trim((string) $link);
        if ($link === '' || ! preg_match('/^https?:\/\/(www\.)?(x\.com|twitter\.com)\//i', $link)) {
            return $this->emptySocialMetric('x_empty');
        }

        $settings = $this->contentPostingGetApiSettings();
        if (empty($settings['apify_enabled']) || empty($settings['apify_token'])) {
            return $this->emptySocialMetric('x_no_apify');
        }

        $actorId = $settings['apify_x_actor'] ?: 'apidojo/tweet-scraper';
        $items = $this->runApifyActorVariants($actorId, $this->buildApifyXInputs($link), $settings['apify_token']);

        $item = $this->findApifyItemByUrl($items, $link) ?: (is_array($items[0] ?? null) ? $items[0] : null);
        if (! is_array($item)) {
            return $this->emptySocialMetric('apify_x_no_result_' . str_replace('/', '~', $actorId));
        }

        if ($this->isApifyErrorItem($item)) {
            return $this->emptySocialMetric('apify_x_error_' . $this->apifyErrorCode($item));
        }

        $title = (string) ($item['text'] ?? $item['fullText'] ?? $item['caption'] ?? '');
        $views = (int) $this->numberOnly($item['viewCount'] ?? $item['viewsCount'] ?? $item['views'] ?? $item['bookmarkCount'] ?? 0);
        $likes = (int) $this->numberOnly($item['likeCount'] ?? $item['favoriteCount'] ?? $item['likes'] ?? 0);
        $comments = (int) $this->numberOnly($item['replyCount'] ?? $item['replies'] ?? $item['comments'] ?? 0);
        $shares = (int) (
            $this->numberOnly($item['retweetCount'] ?? 0)
            + $this->numberOnly($item['quoteCount'] ?? 0)
            + $this->numberOnly($item['shares'] ?? 0)
        );

        if ($this->looksLikeGatewayError($title)) {
            return $this->emptySocialMetric('apify_x_error_502');
        }

        return [
            'ok' => ($views + $likes + $comments + $shares) > 0 || $title !== '',
            'source' => 'apify_x',
            'title' => $title,
            'views' => $views,
            'likes' => $likes,
            'comments' => $comments,
            'shares' => $shares,
            'raw_url' => (string) ($item['url'] ?? $item['twitterUrl'] ?? ''),
        ];
    }

    private function isApifyErrorItem(array $item)
    {
        $text = strtolower(trim((string) (
            ($item['error'] ?? '') . ' ' .
            ($item['message'] ?? '') . ' ' .
            ($item['title'] ?? '') . ' ' .
            ($item['caption'] ?? '') . ' ' .
            ($item['description'] ?? '')
        )));

        if ($text === '') {
            return false;
        }

        return isset($item['error'])
            || isset($item['statusCode'])
            || str_contains($text, 'bad gateway')
            || str_contains($text, 'error 502')
            || str_contains($text, 'proxy')
            || str_contains($text, 'rate limit')
            || str_contains($text, 'blocked');
    }

    private function apifyErrorCode(array $item)
    {
        if (! empty($item['statusCode'])) {
            return preg_replace('/[^0-9a-zA-Z_\-]/', '', (string) $item['statusCode']);
        }

        $text = strtolower((string) (($item['error'] ?? '') . ' ' . ($item['message'] ?? '') . ' ' . ($item['caption'] ?? '') . ' ' . ($item['description'] ?? '')));

        if (str_contains($text, '502') || str_contains($text, 'bad gateway')) {
            return '502';
        }
        if (str_contains($text, '429') || str_contains($text, 'rate limit')) {
            return '429';
        }
        if (str_contains($text, '403') || str_contains($text, 'blocked')) {
            return '403';
        }

        return 'unknown';
    }

    private function looksLikeGatewayError($text)
    {
        $text = strtolower(trim((string) $text));

        return $text !== '' && (
            str_contains($text, 'bad gateway')
            || str_contains($text, 'error 502')
            || preg_match('/\b502\b/', $text)
        );
    }

    private function socialActorCandidates($primaryActorId, array $fallbackActorIds)
    {
        $actorIds = array_merge([trim((string) $primaryActorId)], $fallbackActorIds);

        return collect($actorIds)
            ->map(fn ($actorId) => $this->normalizeApifyActorId($actorId))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeApifyActorId($actorId)
    {
        $actorId = trim((string) $actorId);
        if ($actorId === '') {
            return '';
        }

        // User sering mengisi actor path API memakai ~. Di setting UI kita tetap terima,
        // lalu normalisasi balik ke format owner/name supaya endpoint tidak double salah.
        $actorId = str_replace('~', '/', $actorId);
        $actorId = preg_replace('/\s+/', '', $actorId);
        $actorId = trim($actorId, '/');

        return $actorId;
    }

    private function runApifyActorCandidates(array $actorIds, array $inputVariants, $token)
    {
        $lastError = null;
        $lastActorId = '';

        foreach ($actorIds as $actorId) {
            $actorId = $this->normalizeApifyActorId($actorId);
            if ($actorId === '') {
                continue;
            }

            $lastActorId = $actorId;

            foreach ($inputVariants as $input) {
                if (! is_array($input) || empty($input)) {
                    continue;
                }

                $items = $this->runApifyActor($actorId, $input, $token);
                if (! is_array($items) || count($items) === 0) {
                    continue;
                }

                $first = is_array($items[0] ?? null) ? $items[0] : [];
                if ($first && $this->isApifyErrorItem($first)) {
                    $first['actor_id'] = $actorId;
                    $lastError = $first;
                    continue;
                }

                return [
                    'items' => $items,
                    'actor_id' => $actorId,
                    'error' => null,
                ];
            }
        }

        return [
            'items' => [],
            'actor_id' => $lastActorId,
            'error' => $lastError,
        ];
    }

    private function runApifyActorVariants($actorId, array $inputVariants, $token)
    {
        foreach ($inputVariants as $input) {
            if (! is_array($input) || empty($input)) {
                continue;
            }

            $items = $this->runApifyActor($actorId, $input, $token);
            if (is_array($items) && count($items) > 0) {
                return $items;
            }
        }

        return [];
    }

    private function runApifyActor($actorId, array $input, $token)
    {
        $actorId = $this->normalizeApifyActorId($actorId);
        $token = trim((string) $token);

        if ($actorId === '' || $token === '') {
            return [];
        }

        $actorPath = str_replace('/', '~', $actorId);
        $endpoint = "https://api.apify.com/v2/acts/{$actorPath}/run-sync-get-dataset-items?token=" . urlencode($token);
        $payload = json_encode($input, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        /*
         * Di server ini PHP cURL/Laravel HTTP Client pernah mengembalikan nginx 404,
         * sedangkan binary curl terminal berhasil. Karena itu jalur utama memakai binary curl.
         */
        if (function_exists('shell_exec')) {
            $cmd = 'curl -sS -X POST '
                . escapeshellarg($endpoint)
                . ' -H ' . escapeshellarg('Content-Type: application/json')
                . ' -H ' . escapeshellarg('Accept: application/json')
                . ' --data ' . escapeshellarg($payload);

            $body = shell_exec($cmd);

            if (is_string($body) && trim($body) !== '') {
                $json = json_decode($body, true);

                if (is_array($json)) {
                    return $this->normalizeApifyDatasetItems($json, 200);
                }

                return [[
                    'error' => 'apify_shell_invalid_json',
                    'message' => 'Curl shell response bukan JSON.',
                    'statusCode' => 0,
                    'body' => substr($body, 0, 500),
                    'endpoint' => $endpoint,
                    'payload' => $input,
                ]];
            }
        }

        return [[
            'error' => 'apify_shell_empty',
            'message' => 'Curl shell tidak mengembalikan response. Pastikan shell_exec dan binary curl aktif.',
            'statusCode' => 0,
            'endpoint' => $endpoint,
            'payload' => $input,
        ]];
    }

    private function normalizeApifyDatasetItems(array $json, $status = 200)
    {
        // run-sync-get-dataset-items normalnya mengembalikan list item dataset.
        if (array_is_list($json)) {
            return $json;
        }

        // Kalau Apify/actor mengembalikan object error, ubah menjadi satu item error agar bisa dibaca source_detail.
        if (isset($json['error']) || isset($json['message']) || isset($json['statusCode'])) {
            return [[
                'error' => $json['error'] ?? 'apify_error',
                'message' => $json['message'] ?? ('Apify HTTP ' . $status),
                'statusCode' => $json['statusCode'] ?? $status,
            ]];
        }

        return [$json];
    }

private function buildApifyInstagramInputs($link)
{
    $link = trim((string) $link);

    return [
        [
            'username' => [$link],
            'resultsLimit' => 1,
            'dataDetailLevel' => 'detailedData',
        ],
        [
            'directUrls' => [$link],
            'resultsLimit' => 1,
            'dataDetailLevel' => 'detailedData',
        ],
        [
            'startUrls' => [['url' => $link]],
            'resultsLimit' => 1,
            'dataDetailLevel' => 'detailedData',
        ],
    ];
}

    private function buildApifyThreadsInputs($link)
    {
        $link = trim((string) $link);

        return [
            [
                'startUrls' => [['url' => $link]],
                'urls' => [$link],
                'maxItems' => 1,
                'resultsLimit' => 1,
            ],
            [
                'startUrls' => [$link],
                'maxItems' => 1,
                'resultsLimit' => 1,
            ],
            [
                'directUrls' => [$link],
                'maxItems' => 1,
                'resultsLimit' => 1,
            ],
            [
                'url' => $link,
                'maxItems' => 1,
                'resultsLimit' => 1,
            ],
        ];
    }

    private function buildApifyXInputs($link)
    {
        $link = trim((string) $link);
        $username = $this->extractTwitterHandle($link);
        $tweetId = $this->extractTwitterStatusId($link);

        $inputs = [
            [
                'startUrls' => [$link],
                'maxItems' => 10,
                'sort' => 'Latest',
            ],
            [
                'startUrls' => [['url' => $link]],
                'maxItems' => 10,
                'sort' => 'Latest',
            ],
            [
                'urls' => [$link],
                'maxItems' => 10,
                'sort' => 'Latest',
            ],
        ];

        if ($username) {
            $searchTerm = $tweetId ? "from:{$username} {$tweetId}" : "from:{$username}";

            $inputs[] = [
                'searchTerms' => [$searchTerm],
                'twitterHandles' => [$username],
                'maxItems' => 10,
                'sort' => 'Latest',
            ];
        }

        return $inputs;
    }

    private function normalizeSocialUrl($url)
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }
        $url = strtok($url, '?') ?: $url;
        return rtrim($url, '/');
    }

    private function findApifyItemByUrl(array $items, $url)
    {
        $target = $this->normalizeSocialUrl($url);
        if ($target === '') {
            return null;
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            foreach (['url', 'twitterUrl', 'inputUrl', 'permalink'] as $field) {
                $candidate = $this->normalizeSocialUrl((string) ($item[$field] ?? ''));
                if ($candidate !== '' && $candidate === $target) {
                    return $item;
                }
            }
        }

        return null;
    }

    private function extractTwitterHandle($url)
    {
        if (preg_match('/(?:x\.com|twitter\.com)\/([^\/\?]+)\//i', (string) $url, $m)) {
            $handle = trim($m[1]);
            return in_array(strtolower($handle), ['i', 'home', 'search', 'explore'], true) ? '' : $handle;
        }
        return '';
    }

    private function extractTwitterStatusId($url)
    {
        if (preg_match('/\/status\/(\d+)/i', (string) $url, $m)) {
            return $m[1];
        }
        return '';
    }

    private function readContentPostingJson($name, $default = [])
    {
        try {
            if ($name === 'settings') {
                $row = DB::table('marketing_content_posting_settings')->where('id', 1)->first();

                if ($row) {
                    return $this->contentPostingSettingsRowToArray($row);
                }
            }

            if ($name === 'rows') {
                return DB::table('marketing_content_posting_rows')
                    ->orderBy('tanggal')
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn ($row) => $this->contentPostingRowToArray($row))
                    ->values()
                    ->all();
            }
        } catch (\Throwable $e) {
            // Fallback ke file JSON lama jika tabel belum dibuat atau DB sedang bermasalah.
        }

        $path = "marketing/content-posting/{$name}.json";

        try {
            if (! Storage::disk('local')->exists($path)) {
                return $default;
            }

            $json = Storage::disk('local')->get($path);
            $decoded = json_decode($json, true);

            return is_array($decoded) ? $decoded : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    private function insertContentPostingRow($row)
    {
        try {
            DB::table('marketing_content_posting_rows')->insert($this->contentPostingRowArrayToDb($row));
            return;
        } catch (\Throwable $e) {
            // Fallback ke file JSON lama jika tabel belum dibuat atau DB sedang bermasalah.
        }

        $path = "marketing/content-posting/rows.json";
        $fullPathDir = Storage::disk('local')->path("marketing/content-posting");
        if (!is_dir($fullPathDir)) {
            @mkdir($fullPathDir, 0755, true);
        }
        
        $lockPath = Storage::disk('local')->path("marketing/content-posting/rows.lock");
        $fp = @fopen($lockPath, 'w+');
        if ($fp && flock($fp, LOCK_EX)) {
            $rows = $this->readContentPostingJson('rows', []);
            $rows[] = $row;
            Storage::disk('local')->put(
                $path,
                json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
            flock($fp, LOCK_UN);
            fclose($fp);
        } else {
            // Jika gagal lock, jalankan proses default
            $rows = $this->readContentPostingJson('rows', []);
            $rows[] = $row;
            Storage::disk('local')->put(
                $path,
                json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }
    }

    private function saveContentPostingJson($name, $data)
    {
        try {
            if ($name === 'settings') {
                DB::table('marketing_content_posting_settings')->updateOrInsert(
                    ['id' => 1],
                    $this->contentPostingSettingsArrayToDb($data)
                );
                return;
            }
        } catch (\Throwable $e) {
            // Fallback ke file JSON lama jika tabel belum dibuat atau DB sedang bermasalah.
        }

        $path = "marketing/content-posting/{$name}.json";

        Storage::disk('local')->put(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function contentPostingSettingsRowToArray($row)
    {
        return [
            'api_utama_enabled' => (bool) $row->api_utama_enabled,
            'api_utama_base_url' => (string) ($row->api_utama_base_url ?? ''),
            'api_utama_token' => (string) ($row->api_utama_token ?? ''),
            'api_utama_notes' => (string) ($row->api_utama_notes ?? ''),
            'allowed_ips' => $this->jsonArrayValue($row->allowed_ips ?? null),
            'tiktok_api_default' => (string) ($row->tiktok_api_default ?? ''),
            'tiktok_api_read_views' => (bool) $row->tiktok_api_read_views,
            'tiktok_api_reply_comment' => (bool) $row->tiktok_api_reply_comment,
            'tiktok_api_upload' => (bool) $row->tiktok_api_upload,
            'instagram_meta_enabled' => (bool) $row->instagram_meta_enabled,
            'instagram_user_id' => (string) ($row->instagram_user_id ?? ''),
            'instagram_page_id' => (string) ($row->instagram_page_id ?? ''),
            'instagram_access_token' => (string) ($row->instagram_access_token ?? ''),
            'apify_enabled' => (bool) $row->apify_enabled,
            'apify_token' => (string) ($row->apify_token ?? ''),
            'apify_instagram_actor' => (string) ($row->apify_instagram_actor ?? 'apify/instagram-post-scraper'),
            'apify_threads_actor' => (string) ($row->apify_threads_actor ?? 'apify/threads-scraper'),
            'apify_x_actor' => (string) ($row->apify_x_actor ?? 'apidojo/tweet-scraper'),
            'threads_enabled' => (bool) $row->threads_enabled,
            'threads_user_id' => (string) ($row->threads_user_id ?? ''),
            'threads_access_token' => (string) ($row->threads_access_token ?? ''),
            'x_enabled' => (bool) $row->x_enabled,
            'x_user_id' => (string) ($row->x_user_id ?? ''),
            'x_bearer_token' => (string) ($row->x_bearer_token ?? ''),
            'updated_at' => $row->updated_at ? (string) $row->updated_at : null,
        ];
    }

    private function contentPostingSettingsArrayToDb(array $data)
    {
        $now = now()->toDateTimeString();

        return [
            'api_utama_enabled' => (bool) ($data['api_utama_enabled'] ?? false),
            'api_utama_base_url' => $data['api_utama_base_url'] ?? '',
            'api_utama_token' => $data['api_utama_token'] ?? '',
            'api_utama_notes' => $data['api_utama_notes'] ?? '',
            'allowed_ips' => json_encode(array_values($data['allowed_ips'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'tiktok_api_default' => $data['tiktok_api_default'] ?? '',
            'tiktok_api_read_views' => (bool) ($data['tiktok_api_read_views'] ?? false),
            'tiktok_api_reply_comment' => (bool) ($data['tiktok_api_reply_comment'] ?? false),
            'tiktok_api_upload' => (bool) ($data['tiktok_api_upload'] ?? false),
            'instagram_meta_enabled' => (bool) ($data['instagram_meta_enabled'] ?? false),
            'instagram_user_id' => $data['instagram_user_id'] ?? '',
            'instagram_page_id' => $data['instagram_page_id'] ?? '',
            'instagram_access_token' => $data['instagram_access_token'] ?? '',
            'apify_enabled' => (bool) ($data['apify_enabled'] ?? false),
            'apify_token' => $data['apify_token'] ?? '',
            'apify_instagram_actor' => $data['apify_instagram_actor'] ?? 'apify/instagram-post-scraper',
            'apify_threads_actor' => $data['apify_threads_actor'] ?? 'apify/threads-scraper',
            'apify_x_actor' => $data['apify_x_actor'] ?? 'apidojo/tweet-scraper',
            'threads_enabled' => (bool) ($data['threads_enabled'] ?? false),
            'threads_user_id' => $data['threads_user_id'] ?? '',
            'threads_access_token' => $data['threads_access_token'] ?? '',
            'x_enabled' => (bool) ($data['x_enabled'] ?? false),
            'x_user_id' => $data['x_user_id'] ?? '',
            'x_bearer_token' => $data['x_bearer_token'] ?? '',
            'created_at' => $now,
            'updated_at' => $data['updated_at'] ?? $now,
        ];
    }

    private function contentPostingRowToArray($row)
    {
        return [
            'id' => (string) $row->id,
            'tanggal' => (string) $row->tanggal,
            'bulan' => (string) ($row->bulan ?? ''),
            'tgl' => (int) ($row->tgl ?? 0),
            'creator' => (string) ($row->creator ?? ''),
            'ip_marketing' => (string) ($row->ip_marketing ?? ''),
            'kategori_report' => (string) ($row->kategori_report ?? 'IP'),
            'judul' => (string) ($row->judul ?? ''),
            'ig' => (string) ($row->ig ?? ''),
            'instagram' => (string) ($row->instagram ?? ($row->ig ?? '')),
            'threads' => (string) ($row->threads ?? ''),
            'x' => (string) ($row->x ?? ''),
            'tiktok' => (string) ($row->tiktok ?? ''),
            'tiktok_api_type' => (string) ($row->tiktok_api_type ?? ''),
            'activity_upload' => (bool) $row->activity_upload,
            'activity_reply_comment' => (bool) $row->activity_reply_comment,
            'ig_views' => (int) ($row->ig_views ?? 0),
            'tiktok_views' => (int) ($row->tiktok_views ?? 0),
            'threads_views' => (int) ($row->threads_views ?? 0),
            'x_views' => (int) ($row->x_views ?? 0),
            'likes' => (int) ($row->likes ?? 0),
            'comments' => (int) ($row->comments ?? 0),
            'total' => (int) ($row->total ?? 0),
            'keterangan' => (string) ($row->keterangan ?? ''),
            'metrics_source' => (string) ($row->metrics_source ?? 'manual'),
            'platform_metrics' => $this->jsonArrayValue($row->platform_metrics ?? null),
            'created_at' => $row->created_at ? (string) $row->created_at : null,
            'updated_at' => $row->updated_at ? (string) $row->updated_at : null,
        ];
    }

    private function contentPostingRowArrayToDb(array $row)
    {
        $createdAt = $row['created_at'] ?? now()->toDateTimeString();
        $updatedAt = $row['updated_at'] ?? $createdAt;

        return [
            'id' => (string) ($row['id'] ?? Str::uuid()),
            'tanggal' => $row['tanggal'] ?? now()->toDateString(),
            'bulan' => $row['bulan'] ?? '',
            'tgl' => (int) ($row['tgl'] ?? 0),
            'creator' => $row['creator'] ?? '',
            'ip_marketing' => $row['ip_marketing'] ?? '',
            'kategori_report' => $row['kategori_report'] ?? 'IP',
            'judul' => $row['judul'] ?? '',
            'ig' => $row['ig'] ?? ($row['instagram'] ?? ''),
            'instagram' => $row['instagram'] ?? ($row['ig'] ?? ''),
            'threads' => $row['threads'] ?? '',
            'x' => $row['x'] ?? '',
            'tiktok' => $row['tiktok'] ?? '',
            'tiktok_api_type' => $row['tiktok_api_type'] ?? '',
            'activity_upload' => (bool) ($row['activity_upload'] ?? false),
            'activity_reply_comment' => (bool) ($row['activity_reply_comment'] ?? false),
            'ig_views' => (int) ($row['ig_views'] ?? 0),
            'tiktok_views' => (int) ($row['tiktok_views'] ?? 0),
            'threads_views' => (int) ($row['threads_views'] ?? 0),
            'x_views' => (int) ($row['x_views'] ?? 0),
            'likes' => (int) ($row['likes'] ?? 0),
            'comments' => (int) ($row['comments'] ?? 0),
            'total' => (int) ($row['total'] ?? 0),
            'keterangan' => $row['keterangan'] ?? '',
            'metrics_source' => $row['metrics_source'] ?? 'manual',
            'platform_metrics' => json_encode($row['platform_metrics'] ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    private function jsonArrayValue($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function produkBaru(Request $request)
    {
        $options = [
            'tahun' => \App\Models\LaporanPareto::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values(),
            'bulan' => ['JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'],
            'produk' => \App\Models\LaporanPareto::where('item_nama', '!=', '__TRANSACTION__')->distinct()->pluck('item_nama')->sort()->values(),
        ];

        $defaultProduk = $options['produk']->first() ?? 'PAKET GEPREK HEMAT';

        $rawProduk = $request->get('produk');
        if (is_string($rawProduk)) {
            $selectedProduk = [$rawProduk];
        } elseif (is_array($rawProduk)) {
            $selectedProduk = $rawProduk;
        } else {
            $selectedProduk = [$defaultProduk];
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $tahun = $request->get('tahun', date('Y'));
        $bulan = $request->get('bulan', 'All');

        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'produk' => $selectedProduk,
        ];

        $query = \App\Models\LaporanPareto::query()
            ->join('tbl_outlets', 'tbl_laporan_pareto.outlet_id', '=', 'tbl_outlets.id')
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->whereIn('item_nama', $selectedProduk);

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } else {
            if ($tahun !== 'All') {
                $query->whereYear('tanggal', $tahun);
            }
            if ($bulan !== 'All') {
                $query->whereMonth('tanggal', date('m', strtotime($bulan)));
            }
        }

        $allData = $query->select(
            'tbl_laporan_pareto.item_nama',
            'tbl_laporan_pareto.tanggal',
            'tbl_laporan_pareto.total_jumlah',
            'tbl_laporan_pareto.total_harga',
            'tbl_outlets.nama_outlet',
            'master_outlets.kota_kab as area'
        )->get();

        $productStats = [];
        $allDates = collect();

        $groupedByProduct = $allData->groupBy('item_nama');

        foreach ($selectedProduk as $prodName) {
            $rows = $groupedByProduct->get($prodName, collect());
            
            $totalQty = $rows->sum('total_jumlah');
            $totalOmzetRaw = $rows->sum('total_harga');

            $topOutlets = $rows->groupBy('nama_outlet')->map(function ($oRows, $nama) {
                return [
                    'nama' => $nama,
                    'area' => $oRows->first()->area ?? '-',
                    'qty' => $oRows->sum('total_jumlah'),
                    'omzet' => number_format($oRows->sum('total_harga'), 0, ',', '.')
                ];
            })->sortByDesc('qty')->take(5)->values()->toArray();

            $daily = $rows->groupBy('tanggal')->map(function ($dRows, $tanggal) use ($allDates) {
                $allDates->push($tanggal);
                $time = strtotime($tanggal);
                return [
                    'tanggal_raw' => $tanggal,
                    'tgl' => (int)date('d', $time),
                    'bulan' => date('M', $time),
                    'qty' => $dRows->sum('total_jumlah'),
                    'omzet_raw' => $dRows->sum('total_harga'),
                    'omzet' => number_format($dRows->sum('total_harga'), 0, ',', '.')
                ];
            })->sortBy('tanggal_raw')->values()->toArray();

            $dailyKeyed = [];
            foreach ($daily as $d) {
                $dailyKeyed[$d['tanggal_raw']] = $d;
            }

            $daysCount = count($daily);
            $avgQty = $daysCount > 0 ? round($totalQty / $daysCount) : 0;
            $avgOmzet = $daysCount > 0 ? round($totalOmzetRaw / $daysCount) : 0;

            $productStats[] = [
                'name' => $prodName,
                'totalQty' => $totalQty,
                'avgQty' => $avgQty,
                'totalOmzet' => number_format($totalOmzetRaw, 0, ',', '.'),
                'avgOmzet' => number_format($avgOmzet, 0, ',', '.'),
                'topOutlets' => $topOutlets,
                'dailyTrends' => $daily,
                'dailyKeyed' => $dailyKeyed,
                'is_winner' => false
            ];
        }

        if (count($productStats) > 1) {
            $maxQty = -1;
            $winnerIndex = -1;
            foreach ($productStats as $idx => $stat) {
                if ($stat['totalQty'] > $maxQty) {
                    $maxQty = $stat['totalQty'];
                    $winnerIndex = $idx;
                }
            }
            if ($winnerIndex !== -1) {
                $productStats[$winnerIndex]['is_winner'] = true;
            }
        }

        // Prepare Chart Dataset
        $uniqueDates = $allDates->unique()->sort()->values();
        $chartLabels = $uniqueDates->map(function($d) {
            return date('d M', strtotime($d));
        })->toArray();

        $chartDatasets = [];
        $colors = ['#10b981', '#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
        $cIndex = 0;

        foreach ($productStats as $stat) {
            $dataPts = [];
            foreach ($uniqueDates as $ud) {
                $dataPts[] = isset($stat['dailyKeyed'][$ud]) ? $stat['dailyKeyed'][$ud]['qty'] : 0;
            }
            $chartDatasets[] = [
                'label' => $stat['name'],
                'data' => $dataPts,
                'borderColor' => $colors[$cIndex % count($colors)],
                'backgroundColor' => $colors[$cIndex % count($colors)] . '20',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ];
            $cIndex++;
        }

        $chartData = [
            'labels' => $chartLabels,
            'datasets' => $chartDatasets
        ];

        return view('Marketing.produk-baru', compact(
            'filters',
            'options',
            'productStats',
            'chartData'
        ));
    }

    public function outletGo(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        $bulanInfo = date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun));
        if (!empty($startDate) && !empty($endDate)) {
            $bulanInfo = date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
        }

        $filters = [
            'provinsi' => (array) $request->get('provinsi', ['All']),
            'kota' => (array) $request->get('kota', ['All']),
        ];

        $query = \App\Models\LaporanEcommerce::query()
            ->join('tbl_outlets', 'tbl_laporan_ecommerce.outlet_id', '=', 'tbl_outlets.id')
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->where('tbl_outlets.status', 'go');

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
            $periodStart = $startDate;
            $periodEnd = $endDate;
        } else {
            $query->whereMonth('tanggal', $bulan)
                  ->whereYear('tanggal', $tahun);
            $periodStart = date('Y-m-01', strtotime("$tahun-$bulan-01"));
            $periodEnd = date('Y-m-t', strtotime("$tahun-$bulan-01"));
        }

        if (!empty($filters['provinsi']) && !in_array('All', $filters['provinsi'])) {
            $query->whereIn('master_outlets.provinsi', $filters['provinsi']);
        }
        if (!empty($filters['kota']) && !in_array('All', $filters['kota'])) {
            $query->whereIn('master_outlets.kota_kab', $filters['kota']);
        }

        $dateColumns = [];
        $currentDate = strtotime($periodStart);
        $endDateTS = strtotime($periodEnd);
        $dayCount = 0;
        while ($currentDate <= $endDateTS && $dayCount <= 62) {
            $dateColumns[] = date('Y-m-d', $currentDate);
            $currentDate = strtotime('+1 day', $currentDate);
            $dayCount++;
        }

        $dailyData = $query->select(
            'tbl_outlets.nama_outlet',
            'master_outlets.kota_kab as kota',
            'tanggal',
            \Illuminate\Support\Facades\DB::raw('SUM(total_jumlah) as daily_omzet')
        )->groupBy('tbl_outlets.nama_outlet', 'master_outlets.kota_kab', 'tanggal')->get();

        $outlets = [];
        $grouped = $dailyData->groupBy('nama_outlet');

        foreach ($grouped as $namaOutlet => $rows) {
            $dailySales = array_fill_keys($dateColumns, null);
            $totalOmset = 0;
            $daysWithSales = 0;

            foreach ($rows as $row) {
                $tgl = \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d');
                if (array_key_exists($tgl, $dailySales)) {
                    $dailySales[$tgl] = [
                        'label' => $this->rupiahShort($row->daily_omzet),
                        'raw' => $row->daily_omzet
                    ];
                }
                $totalOmset += $row->daily_omzet;
                $daysWithSales++;
            }

            $outlets[] = [
                'outlet' => $namaOutlet,
                'kota' => $rows->first()->kota ?? '-',
                'promo_go' => '-', // Tidak ada data promo di tabel ini
                'kategori' => 'GO',
                'total_omset' => $this->rupiahShort($totalOmset),
                'avg_harian' => $daysWithSales > 0 ? $this->rupiahShort($totalOmset / $daysWithSales) : '-',
                'daily_sales' => $dailySales,
                'total_omset_raw' => $totalOmset
            ];
        }

        usort($outlets, function($a, $b) {
            return $b['total_omset_raw'] <=> $a['total_omset_raw'];
        });

        $areaData = \App\Models\MasterOutlet::select('provinsi', 'kota_kab as kota')->distinct()->get();
        $options = [
            'tahun' => \App\Models\LaporanEcommerce::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values(),
            'bulan' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
            'provinsi' => $areaData->pluck('provinsi')->filter()->unique()->sort()->values(),
            'kota' => $areaData->pluck('kota')->filter()->map(function($kota) {
                return trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $kota));
            })->unique()->sort()->values(),
        ];

        $dailyTotals = array_fill_keys($dateColumns, 0);
        foreach ($dailyData as $row) {
            $tgl = \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d');
            if (isset($dailyTotals[$tgl])) {
                $dailyTotals[$tgl] += $row->daily_omzet;
            }
        }
        
        $maxDaily = max($dailyTotals) ?: 1;
        $trendData = [];
        foreach ($dateColumns as $dateStr) {
            $val = $dailyTotals[$dateStr];
            $trendData[] = [
                'tanggal' => date('d/m', strtotime($dateStr)),
                'omzet' => $val,
                'omzet_label' => $this->rupiahShort($val),
                'height' => max(8, round(($val / $maxDaily) * 100)),
            ];
        }

        return view('Marketing.outlet-go', compact('bulanInfo', 'outlets', 'options', 'bulan', 'tahun', 'startDate', 'endDate', 'dateColumns', 'trendData', 'filters'));
    }

    public function outletExisting(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        $bulanInfo = date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun));
        if (!empty($startDate) && !empty($endDate)) {
            $bulanInfo = date('d M Y', strtotime($startDate)) . ' - ' . date('d M Y', strtotime($endDate));
        }

        $filters = [
            'provinsi' => (array) $request->get('provinsi', ['All']),
            'kota' => (array) $request->get('kota', ['All']),
        ];

        $query = \App\Models\LaporanEcommerce::query()
            ->join('tbl_outlets', 'tbl_laporan_ecommerce.outlet_id', '=', 'tbl_outlets.id')
            ->leftJoin('master_outlets', 'tbl_outlets.nama_outlet', '=', 'master_outlets.nama_outlet')
            ->where('tbl_outlets.status', 'existing');

        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
            $periodStart = $startDate;
            $periodEnd = $endDate;
        } else {
            $query->whereMonth('tanggal', $bulan)
                  ->whereYear('tanggal', $tahun);
            $periodStart = date('Y-m-01', strtotime("$tahun-$bulan-01"));
            $periodEnd = date('Y-m-t', strtotime("$tahun-$bulan-01"));
        }

        if (!empty($filters['provinsi']) && !in_array('All', $filters['provinsi'])) {
            $query->whereIn('master_outlets.provinsi', $filters['provinsi']);
        }
        if (!empty($filters['kota']) && !in_array('All', $filters['kota'])) {
            $query->whereIn('master_outlets.kota_kab', $filters['kota']);
        }

        $dateColumns = [];
        $currentDate = strtotime($periodStart);
        $endDateTS = strtotime($periodEnd);
        // Limit to max 62 days to avoid infinite loop / memory issues if user selects a massive range
        $dayCount = 0;
        while ($currentDate <= $endDateTS && $dayCount <= 62) {
            $dateColumns[] = date('Y-m-d', $currentDate);
            $currentDate = strtotime('+1 day', $currentDate);
            $dayCount++;
        }

        $dailyData = $query->select(
            'tbl_outlets.nama_outlet',
            'master_outlets.kota_kab as kota',
            'tanggal',
            \Illuminate\Support\Facades\DB::raw('SUM(total_jumlah) as daily_omzet')
        )->groupBy('tbl_outlets.nama_outlet', 'master_outlets.kota_kab', 'tanggal')->get();

        $outlets = [];
        $grouped = $dailyData->groupBy('nama_outlet');

        foreach ($grouped as $namaOutlet => $rows) {
            $dailySales = array_fill_keys($dateColumns, null);
            $totalOmset = 0;
            $daysWithSales = 0;

            foreach ($rows as $row) {
                // Ensure $tgl is a string 'Y-m-d' to prevent Carbon offset error
                $tgl = \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d');
                if (array_key_exists($tgl, $dailySales)) {
                    $dailySales[$tgl] = [
                        'label' => $this->rupiahShort($row->daily_omzet),
                        'raw' => $row->daily_omzet
                    ];
                }
                $totalOmset += $row->daily_omzet;
                $daysWithSales++;
            }

            $outlets[] = [
                'outlet' => $namaOutlet,
                'kota' => $rows->first()->kota ?? '-',
                'promo_go' => '-', // Tidak ada promo
                'kategori' => 'EXISTING',
                'total_omset' => $this->rupiahShort($totalOmset),
                'avg_harian' => $daysWithSales > 0 ? $this->rupiahShort($totalOmset / $daysWithSales) : '-',
                'daily_sales' => $dailySales,
                'total_omset_raw' => $totalOmset
            ];
        }

        usort($outlets, function($a, $b) {
            return $b['total_omset_raw'] <=> $a['total_omset_raw'];
        });

        $areaData = \App\Models\MasterOutlet::select('provinsi', 'kota_kab as kota')->distinct()->get();
        $options = [
            'tahun' => \App\Models\LaporanEcommerce::selectRaw('YEAR(tanggal) as tahun')->distinct()->pluck('tahun')->sortDesc()->values(),
            'bulan' => [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ],
            'provinsi' => $areaData->pluck('provinsi')->filter()->unique()->sort()->values(),
            'kota' => $areaData->pluck('kota')->filter()->map(function($kota) {
                return trim(str_ireplace(['kota administrasi ', 'kabupaten ', 'kab. ', 'kota ', ' regency', ' city'], '', $kota));
            })->unique()->sort()->values(),
        ];

        $dailyTotals = array_fill_keys($dateColumns, 0);
        foreach ($dailyData as $row) {
            $tgl = \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d');
            if (isset($dailyTotals[$tgl])) {
                $dailyTotals[$tgl] += $row->daily_omzet;
            }
        }
        
        $maxDaily = max($dailyTotals) ?: 1;
        $trendData = [];
        foreach ($dateColumns as $dateStr) {
            $val = $dailyTotals[$dateStr];
            $trendData[] = [
                'tanggal' => date('d/m', strtotime($dateStr)),
                'omzet' => $val,
                'omzet_label' => $this->rupiahShort($val),
                'height' => max(8, round(($val / $maxDaily) * 100)),
            ];
        }

        return view('Marketing.outlet-existing', compact('bulanInfo', 'outlets', 'options', 'bulan', 'tahun', 'startDate', 'endDate', 'dateColumns', 'trendData', 'filters'));
    }

    public function kompetitor()
    {
        $csvUrl = "https://docs.google.com/spreadsheets/d/1C6MBTJfUkYvQSxekBfg0ihPeKLR3EsK8/export?format=csv&id=1C6MBTJfUkYvQSxekBfg0ihPeKLR3EsK8&gid=542770651";
        
        $csvData = [];
        try {
            $response = Http::timeout(30)->get($csvUrl);
            if ($response->successful()) {
                $lines = explode("\n", $response->body());
                foreach ($lines as $line) {
                    $csvData[] = str_getcsv($line);
                }
            }
        } catch (\Throwable $e) {}

        $kpi = [
            'total_geprekin' => '0',
            'total_kompetitor' => '0',
            'market_share' => '0%'
        ];

        $provinsiData = [];

        if (count($csvData) > 0) {
            // Read KPI from row 7 (index 6)
            if (isset($csvData[6])) {
                $kpi['total_geprekin'] = trim($csvData[6][1] ?? '0');
                $kpi['total_kompetitor'] = trim($csvData[6][3] ?? '0');
                $kpi['market_share'] = trim(str_replace('"', '', $csvData[6][5] ?? '0%'));
            }

            // Read province blocks
            // Province titles are at rows like 11 and 28.
            for ($i = 0; $i < count($csvData); $i++) {
                $row = $csvData[$i];
                
                // Usually row looks like: "", "Jawa Timur", "", "", "", "", "", "Jawa Barat"
                // Check col 1 and col 7
                $col1 = trim($row[1] ?? '');
                $col7 = trim($row[7] ?? '');

                if ($col1 !== '' && (strpos($col1, 'Jawa') !== false || strpos($col1, 'DKI') !== false || strpos($col1, 'Banten') !== false)) {
                    // It's a province row
                    // Next row is header "Brand", "Cabang"
                    // Next 3 rows are the data
                    
                    if (isset($csvData[$i+2])) {
                        $provinsiData[] = [
                            'name' => $col1,
                            'geprekin' => (int) trim($csvData[$i+2][2] ?? '0'),
                            'berbrand' => (int) trim($csvData[$i+3][2] ?? '0'),
                            'lainnya' => (int) trim($csvData[$i+4][2] ?? '0')
                        ];
                    }
                    if ($col7 !== '' && isset($csvData[$i+2])) {
                        // Extract for the right side block
                        $geprekinVal = trim($csvData[$i+2][8] ?? '0');
                        if ($geprekinVal === 'z') $geprekinVal = '2'; // Handling OCR/CSV error from screenshot

                        $provinsiData[] = [
                            'name' => $col7,
                            'geprekin' => (int) $geprekinVal,
                            'berbrand' => (int) trim($csvData[$i+3][8] ?? '0'),
                            'lainnya' => (int) trim($csvData[$i+4][8] ?? '0')
                        ];
                    }
                }
            }
        }

        return view('Marketing.kompetitor', compact('kpi', 'provinsiData'));
    }

    public function marketIntelligence()
    {
        $csvUrl = "https://docs.google.com/spreadsheets/d/1C6MBTJfUkYvQSxekBfg0ihPeKLR3EsK8/export?format=csv&id=1C6MBTJfUkYvQSxekBfg0ihPeKLR3EsK8&gid=1636451480";
        
        $csvData = [];
        try {
            $response = Http::timeout(30)->get($csvUrl);
            if ($response->successful()) {
                $lines = explode("\n", $response->body());
                foreach ($lines as $line) {
                    $csvData[] = str_getcsv($line);
                }
            }
        } catch (\Throwable $e) {}

        $kpi = [
            'total_sales' => 0,
            'avg_basket' => 0,
            'total_brands' => 0
        ];

        $marketData = [];
        $currentProvince = '';

        if (count($csvData) > 1) { // Skip header
            for ($i = 1; $i < count($csvData); $i++) {
                $row = $csvData[$i];
                if (empty(array_filter($row, fn($v) => trim($v) !== ''))) continue;

                $prov = trim($row[0] ?? '');
                if ($prov !== '') {
                    $currentProvince = $prov;
                }

                $brandName = trim($row[1] ?? '');
                if ($brandName === '') continue;

                $cabang = (int) trim($row[2] ?? '0');
                $basketSize = (int) trim($row[3] ?? '0');
                
                // Clean Sales Est Value string (e.g. "12.420.000.000")
                $salesStr = str_replace(['.', ',', 'Rp', ' '], '', trim($row[6] ?? '0'));
                $salesEst = (float) $salesStr;

                $avgSalesStr = str_replace(['.', ',', 'Rp', ' '], '', trim($row[7] ?? '0'));
                $avgSales = (float) $avgSalesStr;

                $marketData[] = [
                    'provinsi' => $currentProvince,
                    'brand' => $brandName,
                    'cabang' => $cabang,
                    'basket_size' => $basketSize,
                    'sales_est' => $salesEst,
                    'avg_sales_outlet' => $avgSales,
                    'market_share' => trim($row[9] ?? '0')
                ];

                $kpi['total_sales'] += $salesEst;
                $kpi['avg_basket'] += $basketSize;
            }
        }

        if (count($marketData) > 0) {
            $kpi['avg_basket'] = $kpi['avg_basket'] / count($marketData);
        }
        $kpi['total_brands'] = count(array_unique(array_column($marketData, 'brand')));

        // Prepare Top 10 by Sales for Chart
        $brandsAggregated = [];
        foreach ($marketData as $data) {
            $b = $data['brand'];
            if (!isset($brandsAggregated[$b])) {
                $brandsAggregated[$b] = [
                    'brand' => $b,
                    'sales' => 0,
                    'cabang' => 0,
                    'basket' => $data['basket_size'] // average it out later or keep last
                ];
            }
            $brandsAggregated[$b]['sales'] += $data['sales_est'];
            $brandsAggregated[$b]['cabang'] += $data['cabang'];
        }

        // Sort by sales descending
        usort($brandsAggregated, function($a, $b) {
            return $b['sales'] <=> $a['sales'];
        });

        $topSales = array_slice($brandsAggregated, 0, 10);

        return view('Marketing.market-intelligence', compact('kpi', 'marketData', 'topSales'));
    }
    public function areaPotensi()
    {
        $tgtSehatNasional = M_MarketingAreaPotensi::sum('sehat_target') ?? 0;
        $tgtAgresifNasional = M_MarketingAreaPotensi::sum('agresif_target') ?? 0;
        $totalPins = M_MarketingPotensiPin::count();

        return view('Marketing.area-potensi', compact('tgtSehatNasional', 'tgtAgresifNasional', 'totalPins'));
    }

    public function apiGetOutlets()
    {
        $outlets = \App\Models\M_Outlet::leftJoin('tbl_laporan_bulanan', 'tbl_outlets.id', '=', 'tbl_laporan_bulanan.outlet_id')
            ->select(
                'tbl_outlets.id', 
                'tbl_outlets.nama_outlet', 
                'tbl_outlets.kota',
                'tbl_outlets.latitude', 
                'tbl_outlets.longitude', 
                'tbl_outlets.status',
                \Illuminate\Support\Facades\DB::raw('SUM(tbl_laporan_bulanan.total_omset) as omset')
            )
            ->groupBy(
                'tbl_outlets.id', 
                'tbl_outlets.nama_outlet', 
                'tbl_outlets.kota',
                'tbl_outlets.latitude', 
                'tbl_outlets.longitude', 
                'tbl_outlets.status'
            )
            ->get();
            
        return response()->json($outlets);
    }

    public function apiUpdateOutletGps(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $outlet = M_Outlet::find($request->id);
        if ($outlet) {
            $outlet->latitude = $request->latitude;
            $outlet->longitude = $request->longitude;
            $outlet->save();
            return response()->json(['success' => true, 'message' => 'Outlet GPS updated']);
        }
        return response()->json(['success' => false, 'message' => 'Outlet not found'], 404);
    }

    public function apiGetAreaTarget(Request $request)
    {
        $kecamatan = $request->get('kecamatan');
        $kota = $request->get('kota');
        $provinsi = $request->get('provinsi');

        $area = M_MarketingAreaPotensi::where('kecamatan', $kecamatan)
            ->where('kota', $kota)
            ->where('provinsi', $provinsi)
            ->first();

        return response()->json($area);
    }

    public function apiSaveAreaTarget(Request $request)
    {
        $request->validate([
            'provinsi' => 'required|string',
            'kota' => 'required|string',
            'kecamatan' => 'required|string',
        ]);

        $area = M_MarketingAreaPotensi::updateOrCreate(
            [
                'provinsi' => $request->provinsi,
                'kota' => $request->kota,
                'kecamatan' => $request->kecamatan,
            ],
            [
                'existing_count' => $request->existing_count ?? 0,
                'sehat_target' => $request->sehat_target ?? 0,
                'agresif_target' => $request->agresif_target ?? 0,
                'traffic_generator' => $request->traffic_generator,
                'zona_prioritas' => $request->zona_prioritas,
            ]
        );

        return response()->json(['success' => true, 'data' => $area]);
    }

    public function apiSavePin(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'address' => 'required|string',
        ]);

        $pin = M_MarketingPotensiPin::create([
            'area_potensi_id' => $request->area_potensi_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'status' => 'LEAD'
        ]);

        return response()->json(['success' => true, 'data' => $pin]);
    }

    public function apiGetQueuePins()
    {
        $pins = M_MarketingPotensiPin::with('area')->whereIn('status', ['LEAD', 'CANCELLED'])->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $pins]);
    }

    public function apiCancelPin($id)
    {
        $pin = M_MarketingPotensiPin::find($id);
        if($pin) {
            $pin->status = 'CANCELLED';
            $pin->save();
        }
        return response()->json(['success' => true]);
    }

    public function apiDeletePin($id)
    {
        $pin = M_MarketingPotensiPin::find($id);
        if($pin) {
            $pin->delete();
        }
        return response()->json(['success' => true]);
    }
    public function apiGetAllAreaTargets()
    {
        try {
            // Auto-patch database for latitude & longitude safely using raw SQL
            try {
                $check = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM marketing_area_potensis LIKE 'latitude'");
                if (empty($check)) {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE marketing_area_potensis ADD COLUMN latitude DOUBLE NULL, ADD COLUMN longitude DOUBLE NULL");
                }
            } catch (\Exception $ex) {}

            $targets = M_MarketingAreaPotensi::all();
            return response()->json($targets);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function apiSaveAreaTargetGps(Request $request)
    {
        $target = M_MarketingAreaPotensi::find($request->id);
        if ($target) {
            $target->latitude = $request->latitude;
            $target->longitude = $request->longitude;
            $target->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function apiImportAreaTarget(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv,txt'
            ]);

            $file = $request->file('file');
            
            // We use Maatwebsite Excel facade without creating a dedicated class
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array) {}
            }, $file);

            if (empty($data) || empty($data[0])) {
                return response()->json(['success' => false, 'message' => 'File kosong atau tidak valid.']);
            }

            $rows = $data[0];
            $headers = array_map('strtolower', $rows[0]);
            
            $idxProvinsi = -1;
            $idxKota = -1;
            $idxKecamatan = -1;
            $idxExisting = -1;
            $idxSehat = -1;
            $idxAgresif = -1;

            foreach($headers as $i => $h) {
                if (strpos($h, 'provinsi') !== false) $idxProvinsi = $i;
                else if (strpos($h, 'kota') !== false || strpos($h, 'kabupaten') !== false) $idxKota = $i;
                else if (strpos($h, 'kecamatan') !== false) $idxKecamatan = $i;
                else if (strpos($h, 'existing') !== false) $idxExisting = $i;
                else if (strpos($h, 'sehat') !== false) $idxSehat = $i;
                else if (strpos($h, 'agresif') !== false) $idxAgresif = $i;
            }

            if ($idxProvinsi === -1 || $idxKota === -1 || $idxKecamatan === -1) {
                return response()->json(['success' => false, 'message' => 'Format kolom Excel tidak sesuai (harus ada Provinsi, Kota, Kecamatan).']);
            }

            $count = 0;
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $provinsi = trim($row[$idxProvinsi] ?? '');
                $kota = trim($row[$idxKota] ?? '');
                $kecamatan = trim($row[$idxKecamatan] ?? '');

                if (empty($provinsi) || empty($kota) || empty($kecamatan)) continue;

                $existing = $idxExisting !== -1 ? (int)($row[$idxExisting] ?? 0) : 0;
                $sehat = $idxSehat !== -1 ? (int)($row[$idxSehat] ?? 0) : 0;
                $agresif = $idxAgresif !== -1 ? (int)($row[$idxAgresif] ?? 0) : 0;

                M_MarketingAreaPotensi::updateOrCreate(
                    [
                        'provinsi' => $provinsi,
                        'kota' => $kota,
                        'kecamatan' => $kecamatan
                    ],
                    [
                        'existing_count' => $existing,
                        'sehat_target' => $sehat,
                        'agresif_target' => $agresif,
                    ]
                );
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$count} target area kecamatan dari Excel."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ]);
        }
    }
}

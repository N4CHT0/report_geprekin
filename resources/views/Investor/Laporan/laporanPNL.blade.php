{{-- resources/views/Investor/Laporan/laporanPNL.blade.php --}}
@include('Temp.Investor.header')

@php
    $startDate       = request('start_date', $startDate ?? '');
    $endDate         = request('end_date', $endDate ?? '');
    $selectedOutlet  = request('outlet_id', $outletId ?? 'all');
    $showZero        = (bool) request('show_zero', $showZero ?? false);

    $data            = $data ?? [];
    $outlets         = $outlets ?? [];
    $dateList        = $dateList ?? [];
    $jumlahHariSafe  = (int) ($jumlahHari ?? 31);
    $rowCount        = count($data);

    $toNumber = function($v): float {
        $s = (string)($v ?? '0');
        $s = str_replace('.', '', $s);
        $s = str_replace(',', '.', $s);
        $s = preg_replace('/[^0-9\.\-]/', '', $s);
        return (float) $s;
    };

    $isZeroish = function($val): bool {
        if ($val === null || $val === '') return true;
        if (is_numeric($val) && (float)$val == 0.0) return true;
        return in_array((string)$val, ['0', '0,00', '0.00'], true);
    };

    $fmtCell = function($val, string $type = 'num') use ($showZero, $isZeroish) {
        $val = $val ?? 0;

        if (!$showZero && $isZeroish($val)) {
            return '<span class="text-muted">-</span>';
        }

        return match ($type) {
            'money'  => number_format((float)$val, 0, ',', '.'),
            'int'    => number_format((int)$val, 0, ',', '.'),
            'float2' => number_format((float)$val, 2, ',', '.'),
            default  => e((string)$val),
        };
    };

    $fmtID = fn($n) => 'Rp ' . number_format((float)$n, 2, ',', '.');

    $outletName = null;
    if ($selectedOutlet !== 'all') {
        foreach ($outlets as $o) {
            if ((string)$o->id === (string)$selectedOutlet) {
                $outletName = $o->nama_outlet;
                break;
            }
        }
    }

    $gl            = $gl ?? null;
    $glRows        = $gl['rows'] ?? [];
    $glTotalDebit  = 0;
    $glTotalCredit = 0;
    $glLastBalance = 0;

    foreach ($glRows as $r) {
        $glTotalDebit  += $toNumber($r['debitAmount'] ?? 0);
        $glTotalCredit += $toNumber($r['creditAmount'] ?? 0);
        $glLastBalance  = $toNumber($r['balance'] ?? $glLastBalance);
    }

    $glNet = $glTotalDebit - $glTotalCredit;
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.0.2/css/buttons.bootstrap5.min.css">

<style>
    :root{
        --c-primary:#2563eb;
        --c-primary-soft:#eff6ff;
        --c-success:#16a34a;
        --c-danger:#dc2626;
        --c-bg:#f5f7fb;
        --c-card:#ffffff;
        --c-border:#e5e7eb;
        --c-text:#0f172a;
        --c-muted:#64748b;
        --c-head:#f8fafc;
        --shadow:0 8px 24px rgba(15,23,42,.06);
        --r-xl:18px;
        --r-lg:14px;
        --r-md:12px;
    }

    body{
        background:var(--c-bg);
        color:var(--c-text);
        font-size:.92rem;
        overflow-x:hidden;
    }

    .x-page{
        display:flex;
        flex-direction:column;
        gap:1rem;
    }

    .x-card{
        background:var(--c-card);
        border:1px solid var(--c-border);
        border-radius:var(--r-xl);
        box-shadow:var(--shadow);
    }

    .x-card .card-body{
        padding:1.15rem 1.15rem;
    }

    .x-header{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:1rem;
        margin-bottom:.35rem;
    }

    .x-title-wrap{
        display:flex;
        flex-direction:column;
        gap:.45rem;
    }

    .x-title-row{
        display:flex;
        align-items:center;
        gap:.6rem;
        flex-wrap:wrap;
    }

    .x-title{
        margin:0;
        font-weight:900;
        letter-spacing:.2px;
        font-size:1.35rem;
    }

    .x-subtitle{
        color:var(--c-muted);
        font-size:.88rem;
    }

    .x-badge{
        display:inline-flex;
        align-items:center;
        gap:.35rem;
        padding:.34rem .72rem;
        border-radius:999px;
        font-size:.78rem;
        font-weight:800;
        border:1px solid #dbeafe;
        background:var(--c-primary-soft);
        color:#1d4ed8;
        white-space:nowrap;
    }

    .x-section-title{
        display:flex;
        align-items:center;
        gap:.6rem;
        margin:0 0 .95rem 0;
        font-size:1rem;
        font-weight:900;
    }

    .x-filter-grid{
        display:grid;
        grid-template-columns: minmax(280px, 1.2fr) minmax(220px, 1fr) minmax(250px, 1fr);
        gap:1rem;
        align-items:end;
    }

    .x-field{
        display:flex;
        flex-direction:column;
        gap:.45rem;
    }

    .x-label{
        font-size:.84rem;
        font-weight:800;
        color:#334155;
        margin:0;
    }

    .x-date-range{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:.65rem;
    }

    .form-control,
    .form-select{
        min-height:44px;
        border-radius:var(--r-md);
        border-color:#dbe1ea;
        font-size:.9rem;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:#93c5fd;
        box-shadow:0 0 0 .18rem rgba(37,99,235,.12);
    }

    .select2-container--default .select2-selection--single{
        min-height:44px;
        border-radius:var(--r-md);
        border:1px solid #dbe1ea;
        display:flex;
        align-items:center;
        padding:0 .35rem;
    }

    .select2-container .select2-selection--single .select2-selection__rendered{
        line-height:42px;
        padding-left:.35rem;
    }

    .select2-container .select2-selection--single .select2-selection__arrow{
        height:42px;
    }

    .x-switch-box{
        min-height:44px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:.75rem;
        padding:.55rem .8rem;
        background:#fff;
        border:1px solid #dbe1ea;
        border-radius:var(--r-md);
    }

    .x-switch-label{
        font-size:.84rem;
        color:var(--c-muted);
        font-weight:700;
    }

    .x-action-stack{
        display:flex;
        flex-direction:column;
        gap:.7rem;
    }

    .x-btn-row{
        display:flex;
        flex-wrap:wrap;
        gap:.65rem;
    }

    .btn-pill{
        min-height:44px;
        border-radius:var(--r-md);
        padding:.62rem 1rem;
        font-weight:850;
    }

    .btn-icon{
        display:inline-flex;
        align-items:center;
        gap:.5rem;
    }

    .x-toolbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:.75rem;
        flex-wrap:wrap;
        margin-bottom:1rem;
    }

    .x-toolbar-left{
        display:flex;
        align-items:center;
        gap:.55rem;
        flex-wrap:wrap;
    }

    .x-toolbar-right{
        display:flex;
        gap:.65rem;
        flex-wrap:wrap;
    }

    .x-summary{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:1rem;
        margin-bottom:1rem;
    }

    .x-summary-card{
        background:#fff;
        border:1px solid var(--c-border);
        border-radius:var(--r-lg);
        padding:1rem 1rem .95rem;
    }

    .x-summary-label{
        color:var(--c-muted);
        font-size:.84rem;
        font-weight:800;
        margin-bottom:.35rem;
    }

    .x-summary-value{
        font-size:1.1rem;
        font-weight:900;
        line-height:1.2;
    }

    .x-table-frame{
        border:1px solid var(--c-border);
        border-radius:var(--r-lg);
        background:#fff;
        overflow:hidden;
    }

    .x-scroll{
        overflow:auto;
        -webkit-overflow-scrolling:touch;
    }

    table.dataTable{
        margin-top:0 !important;
        margin-bottom:0 !important;
        width:100% !important;
    }

    #laporanTable th,
    #laporanTable td,
    #glTable th,
    #glTable td{
        padding:.62rem .7rem;
        font-size:.88rem;
        vertical-align:middle;
    }

    #laporanTable thead th,
    #glTable thead th{
        background:#f8fafc !important;
        border-bottom:1px solid #e2e8f0 !important;
        color:#0f172a;
        font-weight:900;
        white-space:nowrap;
        text-align:center;
    }

    #laporanTable tbody tr:nth-child(odd),
    #glTable tbody tr:nth-child(odd){
        background:#fcfcfd;
    }

    #laporanTable tbody tr:hover,
    #glTable tbody tr:hover{
        background:#f8fbff !important;
    }

    .x-col-no{
        width:58px;
        min-width:58px;
        max-width:58px;
        text-align:center !important;
    }

    .x-desc{
        min-width:250px;
        max-width:420px;
        white-space:normal !important;
        word-break:break-word;
        line-height:1.28;
        text-align:left !important;
        font-weight:800;
    }

    .x-num{
        text-align:right !important;
        font-variant-numeric:tabular-nums;
    }

    .x-gl-notes{
        min-width:240px;
        max-width:420px;
        white-space:normal !important;
        word-break:break-word;
    }

    .x-sticky-1{
        position:sticky;
        left:0;
        background:#fff;
        z-index:4;
    }

    .x-sticky-2{
        position:sticky;
        left:58px;
        background:#fff;
        z-index:4;
    }

    th.x-sticky-1,
    th.x-sticky-2{
        z-index:8;
    }

    .th-title{ font-weight:950; }
    .th-subtotal{ background:#f8fafc !important; }
    .th-metric{
        background:#f9fafb !important;
        font-size:.78rem !important;
        font-weight:900 !important;
    }

    .x-day-end{
        border-right:2px solid #eef2f7 !important;
    }

    .dt-wrap{
        width:100%;
        max-width:100%;
    }

    .dt-bar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:.85rem;
        padding:.8rem .9rem;
        background:#fff;
        border:1px solid var(--c-border);
        border-radius:var(--r-lg);
    }

    .dt-bar .dt-left,
    .dt-bar .dt-right{
        display:flex;
        align-items:center;
        gap:.75rem;
        flex-wrap:wrap;
    }

    .dt-bar .dt-right{
        margin-left:auto;
        justify-content:flex-end;
    }

    .dt-scroll{
        margin-top:.8rem;
        border:1px solid var(--c-border);
        border-radius:var(--r-lg);
        overflow:auto;
        background:#fff;
    }

    .dt-foot{
        margin-top:.8rem;
    }

    .dt-wrap .dataTables_length,
    .dt-wrap .dataTables_filter,
    .dt-wrap .dataTables_info{
        margin:0 !important;
    }

    .dt-wrap .dataTables_filter label,
    .dt-wrap .dataTables_length label{
        display:flex;
        align-items:center;
        gap:.55rem;
        margin:0;
        font-weight:700;
        color:#334155;
    }

    .dt-wrap .dataTables_filter input,
    .dt-wrap .dataTables_length select{
        border-radius:12px !important;
        padding:.45rem .7rem !important;
        border:1px solid #dbe1ea !important;
        min-height:40px;
    }

    .dt-wrap .dataTables_paginate{
        margin-left:auto;
    }

    .dt-wrap .pagination{
        margin:0;
    }

    .x-empty{
        padding:2rem 1rem;
        text-align:center;
        color:var(--c-muted);
        font-weight:700;
    }

    @media (max-width: 1200px){
        .x-filter-grid{
            grid-template-columns:1fr 1fr;
        }
        .x-action-stack{
            grid-column:1 / -1;
        }
    }

    @media (max-width: 992px){
        .x-summary{
            grid-template-columns:1fr 1fr;
        }
        .x-filter-grid{
            grid-template-columns:1fr;
        }
        .x-sticky-1,
        .x-sticky-2{
            position:relative;
            left:auto;
        }
    }

    @media (max-width: 576px){
        .x-header{
            flex-direction:column;
            align-items:flex-start;
        }
        .x-date-range{
            grid-template-columns:1fr;
        }
        .x-summary{
            grid-template-columns:1fr;
        }
        .dt-bar{
            flex-direction:column;
            align-items:stretch;
        }
        .dt-bar .dt-right{
            margin-left:0;
            justify-content:flex-start;
        }
        .x-toolbar{
            flex-direction:column;
            align-items:flex-start;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="x-page">

                {{-- PAGE HEADER --}}
                <div class="x-header">
                    <div class="x-title-wrap">
                        <div class="x-title-row">
                            <h4 class="x-title">PNL (Daily)</h4>
                            <span class="x-badge">{{ $rowCount }} baris</span>
                            @if(!$showZero)
                                <span class="x-badge">Hide Zero</span>
                            @endif
                            @if($outletName)
                                <span class="x-badge">{{ $outletName }}</span>
                            @endif
                        </div>
                        <div class="x-subtitle">
                            Profit & Loss harian outlet berdasarkan periode terpilih · Render {{ now()->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>

                {{-- FILTER CARD --}}
                <div class="x-card">
                    <div class="card-body">
                        <div class="x-section-title">
                            <i class="fas fa-chart-line text-primary"></i>
                            <span>Filter Laporan</span>
                        </div>

                        <form method="GET" action="{{ route('investor.laporan.profitnloss') }}" id="formProfitLossFilter">
                            <div class="x-filter-grid">
                                <div class="x-field">
                                    <label class="x-label">
                                        <i class="fas fa-calendar-alt text-primary me-1"></i> Periode Tanggal
                                    </label>
                                    <div class="x-date-range">
                                        <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                                        <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                                    </div>
                                </div>

                                <div class="x-field">
                                    <label class="x-label">
                                        <i class="fas fa-store text-primary me-1"></i> Outlet
                                    </label>
                                    <select class="form-select" name="outlet_id" id="outletSelect">
                                        <option value="all" {{ $selectedOutlet == 'all' ? 'selected' : '' }}>All Outlet</option>
                                        @foreach ($outlets as $o)
                                            <option value="{{ $o->id }}" {{ (string)$selectedOutlet === (string)$o->id ? 'selected' : '' }}>
                                                {{ $o->nama_outlet }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="x-action-stack">
                                    <label class="x-label">Opsi Tampilan & Aksi</label>

                                    <div class="x-switch-box">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" name="show_zero" value="1" {{ $showZero ? 'checked' : '' }}>
                                        </div>
                                        <span class="x-switch-label">Tampilkan nilai 0</span>
                                    </div>

                                    <div class="x-btn-row">
                                        <button class="btn btn-primary btn-pill" type="submit">
                                            <span class="btn-icon"><i class="fas fa-search"></i> Tampilkan</span>
                                        </button>
                                        <a href="{{ route('investor.laporan.profitnloss') }}" class="btn btn-outline-secondary btn-pill">
                                            <span class="btn-icon"><i class="fas fa-rotate-right"></i> Reset</span>
                                        </a>
                                        <button type="button" class="btn btn-outline-success btn-pill" id="btnExcelPNL">
                                            <span class="btn-icon"><i class="fas fa-file-excel"></i> Export Excel</span>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-pill" id="btnPdfPNL">
                                            <span class="btn-icon"><i class="fas fa-file-pdf"></i> Export PDF</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- PNL TABLE --}}
                <div class="x-card">
                    <div class="card-body">
                        <div class="x-toolbar">
                            <div class="x-toolbar-left">
                                <div class="x-section-title mb-0">
                                    <i class="fas fa-table text-primary"></i>
                                    <span>Tabel Profit & Loss</span>
                                </div>
                            </div>
                        </div>

                        <div class="dt-wrap">
                            <table id="laporanTable" class="table table-striped table-hover align-middle w-100 mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="3" class="x-sticky-1 x-col-no">No</th>
                                        <th rowspan="3" class="x-sticky-2">Deskripsi</th>
                                        <th colspan="{{ $jumlahHariSafe * 3 }}" class="th-title text-center">
                                            Penjualan Outlet (Sales per Hari)
                                        </th>
                                        <th colspan="3" class="th-subtotal text-center">Sub Total</th>
                                    </tr>
                                    <tr>
                                        @for ($d = 1; $d <= $jumlahHariSafe; $d++)
                                            <th colspan="3">
                                                {{ isset($dateList[$d]) ? \Carbon\Carbon::parse($dateList[$d])->format('d/m') : str_pad($d, 2, '0', STR_PAD_LEFT) }}
                                            </th>
                                        @endfor
                                        <th colspan="3" class="th-subtotal"></th>
                                    </tr>
                                    <tr>
                                        @for ($d = 1; $d <= $jumlahHariSafe; $d++)
                                            <th class="th-metric">Sales</th>
                                            <th class="th-metric">CU</th>
                                            <th class="th-metric">AC</th>
                                        @endfor
                                        <th class="th-metric th-subtotal">Sales</th>
                                        <th class="th-metric th-subtotal">CU</th>
                                        <th class="th-metric th-subtotal">AC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $i => $row)
                                        <tr>
                                            <td class="x-sticky-1 x-col-no">{{ $i + 1 }}</td>
                                            <td class="x-sticky-2 x-desc">{{ $row['deskripsi'] ?? '-' }}</td>

                                            @for ($d = 1; $d <= $jumlahHariSafe; $d++)
                                                @php
                                                    $hari  = $row['hari'][$d] ?? [];
                                                    $sales = $hari['sales'] ?? 0;
                                                    $cu    = $hari['cu'] ?? 0;
                                                    $ac    = $hari['ac'] ?? 0;
                                                @endphp

                                                <td class="x-num">{!! $fmtCell($sales, 'money') !!}</td>
                                                <td class="x-num">{!! $fmtCell($cu, 'int') !!}</td>
                                                <td class="x-num x-day-end">{!! $fmtCell($ac, 'float2') !!}</td>
                                            @endfor

                                            <td class="fw-bold bg-light x-num">{!! $fmtCell($row['sub_total']['sales'] ?? 0, 'money') !!}</td>
                                            <td class="fw-bold bg-light x-num">{!! $fmtCell($row['sub_total']['cu'] ?? 0, 'int') !!}</td>
                                            <td class="fw-bold bg-light x-num">{!! $fmtCell($row['sub_total']['ac'] ?? 0, 'float2') !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- GL SECTION --}}
                @if(!empty($gl) && ($gl['ok'] ?? false))
                    <div class="x-card">
                        <div class="card-body">
                            <div class="x-toolbar">
                                <div class="x-toolbar-left">
                                    <h5 class="m-0 fw-bold" style="font-weight:950;">General Ledger (ESB)</h5>
                                    <span class="x-badge">{{ number_format(count($glRows)) }} baris</span>
                                    @if($outletName)
                                        <span class="x-badge">{{ $outletName }}</span>
                                    @endif
                                </div>

                                <div class="x-toolbar-right">
                                    <button type="button" class="btn btn-outline-success btn-pill" id="btnExcelGL">
                                        <span class="btn-icon"><i class="fas fa-file-excel"></i> Export GL Excel</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger btn-pill" id="btnPdfGL">
                                        <span class="btn-icon"><i class="fas fa-file-pdf"></i> Export GL PDF</span>
                                    </button>
                                </div>
                            </div>

                            <div class="x-summary">
                                <div class="x-summary-card">
                                    <div class="x-summary-label">Total Debit</div>
                                    <div class="x-summary-value">{{ $fmtID($glTotalDebit) }}</div>
                                </div>
                                <div class="x-summary-card">
                                    <div class="x-summary-label">Total Credit</div>
                                    <div class="x-summary-value">{{ $fmtID($glTotalCredit) }}</div>
                                </div>
                                <div class="x-summary-card">
                                    <div class="x-summary-label">Net (Debit - Credit)</div>
                                    <div class="x-summary-value">{{ $fmtID($glNet) }}</div>
                                </div>
                                <div class="x-summary-card">
                                    <div class="x-summary-label">Balance (Last Row)</div>
                                    <div class="x-summary-value">{{ $fmtID($glLastBalance) }}</div>
                                </div>
                            </div>

                            <div class="dt-wrap">
                                <table id="glTable" class="table table-sm table-striped w-100 mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Account</th>
                                            <th>Deskripsi COA</th>
                                            <th>Notes</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
                                            <th class="text-end">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($glRows as $r)
                                            <tr>
                                                <td>{{ $r['journalDate'] ?? '-' }}</td>
                                                <td>{{ $r['accountNo'] ?? '-' }}</td>
                                                <td class="x-gl-notes">{{ $r['accountDescription'] ?? '-' }}</td>
                                                <td class="x-gl-notes">{{ $r['notes'] ?? '-' }}</td>
                                                <td class="text-end">{{ $r['debitAmount'] ?? '0,00' }}</td>
                                                <td class="text-end">{{ $r['creditAmount'] ?? '0,00' }}</td>
                                                <td class="text-end">{{ $r['balance'] ?? '0,00' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @if(empty($glRows))
                                    <div class="x-empty">Tidak ada data GL.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @elseif(!empty($gl) && !($gl['ok'] ?? true))
                    <div class="alert alert-warning">
                        GL error: {{ $gl['error'] ?? 'unknown' }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    $('#outletSelect').select2({ width: '100%' });

    const start = @json($startDate ?: 'start');
    const end   = @json($endDate ?: 'end');

    const makeTitle = (prefix) => `${prefix}_${start}_to_${end}`;

    const initDT = (selector, title, opts = {}) => {
        return new DataTable(selector, {
            paging: true,
            pageLength: opts.pageLength || 25,
            lengthMenu: [10, 25, 50, 100, 200],
            searching: true,
            info: true,
            ordering: false,
            autoWidth: false,
            deferRender: true,
            scrollX: false,
            language: {
                emptyTable: opts.emptyTable || 'Tidak ada data.',
                zeroRecords: 'Data tidak ditemukan',
                search: 'Search:',
                lengthMenu: '_MENU_ entries per page',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                paginate: {
                    first: '«',
                    last: '»',
                    next: '›',
                    previous: '‹'
                }
            },
            layout: {
                topStart: null,
                topEnd: null,
                bottomStart: null,
                bottomEnd: null
            },
            dom:
                "<'dt-bar dt-top'<'dt-left'l><'dt-right'f>>" +
                "<'dt-scroll'rt>" +
                "<'dt-bar dt-foot'<'dt-left'i><'dt-right'p>>",
            buttons: [
                { extend: 'excelHtml5', title },
                { extend: 'pdfHtml5', title, orientation: 'landscape', pageSize: 'A4' }
            ]
        });
    };

    const dtPNL = initDT('#laporanTable', makeTitle('ProfitLoss'), {
        emptyTable: 'Pilih tanggal untuk menampilkan data.'
    });

    document.getElementById('btnExcelPNL')?.addEventListener('click', () => {
        dtPNL.button('.buttons-excel').trigger();
    });

    document.getElementById('btnPdfPNL')?.addEventListener('click', () => {
        dtPNL.button('.buttons-pdf').trigger();
    });

    if (document.querySelector('#glTable')) {
        const dtGL = initDT('#glTable', makeTitle('GeneralLedger'), {
            emptyTable: 'Tidak ada data GL.'
        });

        document.getElementById('btnExcelGL')?.addEventListener('click', () => {
            dtGL.button('.buttons-excel').trigger();
        });

        document.getElementById('btnPdfGL')?.addEventListener('click', () => {
            dtGL.button('.buttons-pdf').trigger();
        });
    }
});
</script>

@include('Temp.Investor.footer')
{{-- EXPORT STOCK OPNAME TEMPLATE --}}
@section('title', 'Daily Stock Control')
@section('breadcrumb', 'Inventory / DSC')

@include('Temp.Investor.header')

{{-- Alpine dipertahankan untuk komponen ADJUSTMENT. Tailwind CDN dihapus agar halaman lebih ringan. --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

@php
    $startDate = $startDate ?? request('start_date', '');
    $endDate   = $endDate ?? request('end_date', '');
    $missingOutlets = $missingOutlets ?? [];
    $missingCheckStartDate = $missingCheckStartDate ?? \Carbon\Carbon::parse($today ?? date('Y-m-d'))->subDay()->format('Y-m-d');
    $missingCheckEndDate = $missingCheckEndDate ?? $missingCheckStartDate;
    $missingCount = is_countable($missingOutlets) ? count($missingOutlets) : 0;
@endphp

@php
    $today = $today ?? ($tanggal ?? date('Y-m-d'));
    $outletId = $outletId ?? request('outlet_id', '');
    $shiftFilter = $shiftFilter ?? request('shift_filter', 'all');
    $hasRequiredFilter = !empty($outletId) && $outletId !== 'all';
    $exportStartDate = request('start_date', $startDate ?: $today);
    $exportEndDate = request('end_date', $endDate ?: $today);

    $salesShift1 = $hasRequiredFilter ? ($sales_shift_1 ?? 0) : 0;
    $salesShift2 = $hasRequiredFilter ? ($sales_shift_2 ?? 0) : 0;
    $salesTotal = (float) $salesShift1 + (float) $salesShift2;

    $rekapRows = $rekapRows ?? [];
    $shiftRows = $shiftRows ?? [];

    // MODE RINGAN: sebelum outlet dipilih, jangan render data besar ke tabel.
    // Ini mencegah halaman berat karena semua row DSC/shift/ADJUSTMENT tidak dibangun di Blade.
    if (!$hasRequiredFilter) {
        $rekapRows = [];
        $shiftRows = [];
    }

    $omsetActive = $omsetActive ?? [
        'exists' => false,
        'shift' => null,
        'total_transaction' => 0,
        'diskon' => 0,
        'non_tunai' => 0,
        'expense' => 0,
        'uang_fisik' => 0,
        'admin_pot_sales' => 0,
        'ADJUSTMENT' => 0,
        'selisih_minus' => 0,
        'tanggal_setor' => null,
        'sudah_disetor' => 0,
        'akumulasi_selisih' => 0,
        'kekurangan_bulan_lalu' => 0,
        'pic' => '',
        'foto_url' => null,
    ];

    $role = auth()->user()->role ?? null;
    $canADJUSTMENT = in_array($role, ['superadmin', 'superadmin_audit', 'tm_manager', 'spv'], true);

    // Tampilan outlet dibuat bersih: tanpa array/group id, tanpa kode unik, tanpa daftar ID panjang.
    $cleanOutletName = function ($value) {
        $value = trim((string) $value);
        $value = preg_replace('/\s*\[ID:\s*[^\]]*\]\s*/i', '', $value);
        $value = preg_replace('/\s*\(ID:\s*[^\)]*\)\s*/i', '', $value);
        $value = preg_replace('/^Outlet ID:\s*/i', '', $value);
        return trim(preg_replace('/\s+/', ' ', $value)) ?: '-';
    };

    $selectedOutletDisplay = $hasRequiredFilter
        ? $cleanOutletName($selectedOutletLabel ?? ($selectedOutlet->nama_outlet ?? ($outletId ?: '-')))
        : '-';
@endphp

<style>
    :root{
        --aws-bg:#f7f8fa;
        --aws-card:#ffffff;
        --aws-text:#16191f;
        --aws-muted:#5f6b7a;
        --aws-line:#d5dbdb;
        --aws-line-soft:#e9ebed;
        --aws-blue:#0972d3;
        --aws-blue-dark:#033160;
        --aws-green:#037f0c;
        --aws-red:#d13212;
        --aws-orange:#b7791f;
        --aws-radius:8px;
        --aws-shadow:0 1px 2px rgba(15,23,42,.06);
        --aws-focus:0 0 0 3px rgba(9,114,211,.18);
    }

    .dsc-shell{
        display:flex;
        flex-direction:column;
        gap:16px;
    }

    .dsc-page,
    .dsc-section,
    .dsc-filter,
    .dsc-body,
    .tab-content,
    .tab-pane{
        background:transparent;
        border:0;
        overflow:visible;
    }

    .dsc-header{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
        padding-bottom:14px;
        border-bottom:1px solid var(--aws-line);
        margin-bottom:0;
    }

    .dsc-title{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .dsc-title h4{
        margin:0;
        font-size:1.5rem;
        font-weight:700;
        letter-spacing:-.02em;
        color:var(--aws-text);
    }

    .dsc-title-mark{
        width:36px;
        height:36px;
        display:grid;
        place-items:center;
        border-radius:8px;
        background:#f1f8ff;
        color:var(--aws-blue);
        border:1px solid #b6d7f5;
    }

    .dsc-badge{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:5px 9px;
        border-radius:999px;
        background:#f1f8ff;
        color:var(--aws-blue);
        border:1px solid #b6d7f5;
        font-size:.72rem;
        font-weight:800;
        white-space:nowrap;
    }

    .dsc-toolbar{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        align-items:center;
        justify-content:flex-end;
    }

    .dsc-toolbar .btn,
    .nav-pills .nav-link,
    .filter-item label,
    .dsc-card .dsc-card-head{
        text-transform:uppercase;
        letter-spacing:.02em;
    }

    .dsc-bell-wrap{ position:relative; }
    .dsc-bell-btn{
        position:relative;
        width:38px;
        height:38px;
        display:grid;
        place-items:center;
        border:1px solid var(--aws-line)!important;
        background:#fff!important;
        color:var(--aws-text)!important;
        border-radius:8px!important;
    }
    .dsc-bell-count{
        position:absolute;
        top:-7px;
        right:-7px;
        min-width:20px;
        height:20px;
        padding:0 5px;
        display:grid;
        place-items:center;
        border-radius:999px;
        background:var(--aws-red);
        color:#fff;
        font-size:.68rem;
        font-weight:900;
        border:2px solid #fff;
    }
    .dsc-bell-panel{
        min-width:360px;
        max-width:440px;
        padding:0;
        border:1px solid var(--aws-line);
        border-radius:10px;
        box-shadow:0 18px 48px rgba(15,23,42,.18);
        overflow:hidden;
    }
    .dsc-bell-head{
        padding:12px 14px;
        background:#fbfbfb;
        border-bottom:1px solid var(--aws-line);
        font-weight:900;
        color:var(--aws-text);
    }
    .dsc-bell-body{ max-height:360px; overflow:auto; }
    .dsc-bell-item{
        padding:10px 14px;
        border-bottom:1px solid var(--aws-line-soft);
        font-weight:700;
    }
    .dsc-bell-item small{ color:var(--aws-muted); font-weight:700; }
    .dsc-bell-foot{
        padding:10px 14px;
        background:#fff;
        color:var(--aws-muted);
        font-size:.78rem;
        font-weight:700;
    }


    .btn{
        border-radius:8px!important;
        font-weight:700!important;
        box-shadow:none!important;
        transform:none!important;
    }

    .btn-sm{
        padding:.42rem .72rem;
        font-size:.84rem;
    }

    .btn-primary{
        background:var(--aws-blue)!important;
        border-color:var(--aws-blue)!important;
    }

    .btn-primary:hover,
    .btn-primary:focus{
        background:var(--aws-blue-dark)!important;
        border-color:var(--aws-blue-dark)!important;
    }

    .btn-success{
        background:var(--aws-green)!important;
        border-color:var(--aws-green)!important;
    }

    .btn-ghost{
        background:#fff!important;
        border:1px solid var(--aws-line)!important;
        color:#414d5c!important;
    }

    .btn-ghost:hover{
        background:#f2f3f3!important;
        color:var(--aws-text)!important;
    }

    .dsc-meta-grid{
        display:grid;
        grid-template-columns:1.4fr .7fr;
        gap:14px;
        margin:16px 0 0;
    }

    .dsc-card,
    .kpi,
    .soft-alert,
    .dsc-filter-bar{
        background:#fff;
        border:1px solid var(--aws-line);
        border-radius:var(--aws-radius);
        box-shadow:var(--aws-shadow);
    }

    .dsc-card .dsc-card-head{
        padding:13px 14px;
        border-bottom:1px solid var(--aws-line);
        background:#fbfbfb;
        color:var(--aws-text);
        font-weight:700;
    }

    .dsc-card .dsc-card-body{
        padding:14px;
    }

    .dsc-kv{
        display:grid;
        grid-template-columns:170px 1fr;
        gap:8px 14px;
        font-size:.9rem;
        align-items:center;
    }

    .dsc-kv .k{
        color:var(--aws-muted);
        font-weight:700;
    }

    .dsc-kv .v{
        color:var(--aws-text);
        font-weight:700;
    }

    .dsc-note{
        padding:10px 12px;
        border:1px solid #b6d7f5;
        background:#f1f8ff;
        border-radius:var(--aws-radius);
        color:var(--aws-blue-dark);
        font-weight:600;
        font-size:.84rem;
    }

    .dsc-help{
        color:var(--aws-muted);
        font-size:.82rem;
        font-weight:600;
    }

    .kpi-grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:14px;
        margin:14px 0 0;
    }

    .kpi{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        padding:14px;
    }

    .kpi .label{
        font-size:.78rem;
        color:var(--aws-muted);
        font-weight:700;
        margin-bottom:4px;
    }

    .kpi .value{
        font-size:1.08rem;
        font-weight:800;
        color:var(--aws-text);
        line-height:1.2;
    }

    .kpi .icon{
        width:42px;
        height:42px;
        display:grid;
        place-items:center;
        border-radius:8px;
        background:#f8f9fa;
        border:1px solid var(--aws-line);
        font-size:1rem;
    }

    .kpi.primary .icon{ background:#f1f8ff;color:var(--aws-blue);border-color:#b6d7f5; }
    .kpi.success .icon{ background:#ecfdf3;color:var(--aws-green);border-color:#b7e4bf; }
    .kpi.warning .icon{ background:#fff7ed;color:var(--aws-orange);border-color:#f8d7a0; }

    .dsc-filter-bar{
        padding:14px;
        margin:16px 0;
    }

    .filter-grid{
        display:grid;
        grid-template-columns:1.35fr .65fr .55fr 1fr auto;
        gap:12px;
        align-items:end;
    }

    .filter-item label,
    .form-label{
        display:block;
        color:var(--aws-text);
        font-size:.78rem;
        font-weight:700;
        margin-bottom:5px;
    }

    .form-control,
    .form-select{
        min-height:38px;
        border-radius:8px!important;
        border:1px solid var(--aws-line)!important;
        box-shadow:none!important;
        color:var(--aws-text);
        font-size:.9rem;
        font-weight:600;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:var(--aws-blue)!important;
        box-shadow:var(--aws-focus)!important;
    }

    .nav-pills{
        gap:4px;
        margin-bottom:16px!important;
        border-bottom:1px solid var(--aws-line);
        flex-wrap:wrap;
    }

    .nav-pills .nav-link{
        position:relative;
        border-radius:0!important;
        border:0;
        background:transparent;
        color:var(--aws-muted);
        font-weight:700;
        padding:10px 12px;
    }

    .nav-pills .nav-link:hover{
        background:#f2f8fd;
        color:var(--aws-blue);
    }

    .nav-pills .nav-link.active{
        background:transparent!important;
        color:var(--aws-blue)!important;
    }

    .nav-pills .nav-link.active::after{
        content:"";
        position:absolute;
        left:10px;
        right:10px;
        bottom:-1px;
        height:3px;
        background:var(--aws-blue);
        border-radius:999px 999px 0 0;
    }

    .dsc-scroll,
    .dsc-scroll-y{
        background:#fff;
        border:1px solid var(--aws-line);
        border-radius:var(--aws-radius);
        box-shadow:var(--aws-shadow);
        overflow:auto;
        -webkit-overflow-scrolling:touch;
    }

    .dsc-scroll{ max-height:680px; }
    .dsc-scroll-y{ max-height:680px; overflow-y:auto; overflow-x:hidden; overscroll-behavior:contain; }

    .dsc-scroll-x{
        overflow:auto;
        -webkit-overflow-scrolling:touch;
        touch-action:pan-x pan-y;
    }

    .dsc-scroll-x > table{
        width:max-content;
        min-width:100%;
    }

    .dsc-table{
        width:100%;
        margin:0!important;
        color:var(--aws-text);
        vertical-align:middle;
        font-size:.86rem;
        border-collapse:separate;
        border-spacing:0;
    }

    .dsc-table th,
    .dsc-table td{
        border-bottom:1px solid var(--aws-line-soft)!important;
        padding:10px 11px;
        font-size:.84rem;
        white-space:nowrap;
        vertical-align:middle;
        background:#fff;
        color:var(--aws-text);
    }

    .dsc-table th{
        position:sticky;
        top:0;
        z-index:5;
        background:#f8f9fa!important;
        color:#414d5c;
        font-size:.72rem;
        font-weight:800;
        text-align:center;
        text-transform:uppercase;
        letter-spacing:.04em;
        border-bottom:1px solid var(--aws-line)!important;
    }

    .dsc-table td.num{
        text-align:right;
        font-variant-numeric:tabular-nums;
        font-weight:700;
    }

    .dsc-table td.center{
        text-align:center;
        font-weight:700;
    }

    .dsc-table td.item{
        font-weight:800;
    }

    .dsc-table tbody tr:hover td{
        background:#f2f8fd;
    }

    .sticky-1{
        position:sticky;
        left:0;
        z-index:4;
        background:#fff!important;
    }

    .sticky-2{
        position:sticky;
        left:62px;
        z-index:4;
        background:#fff!important;
        box-shadow:10px 0 0 rgba(15,23,42,.03);
    }

    thead .sticky-1,
    thead .sticky-2{
        background:#f8f9fa!important;
        z-index:6;
    }

    .w-no{ width:62px; min-width:62px; }
    .w-name{ width:300px; min-width:300px; }
    .w-sat{ width:76px; min-width:76px; }
    .w-num{ width:124px; min-width:124px; }
    .w-wide{ width:300px; min-width:300px; }

    .cell-negative{
        background:#fff1f0!important;
        color:var(--aws-red)!important;
        font-weight:800!important;
    }

    .soft-alert{
        padding:13px 14px;
        margin-bottom:14px;
    }

    .soft-alert.primary{ border-color:#b6d7f5; background:#f1f8ff; }
    .soft-alert.warning{ border-color:#f8d7a0; background:#fff7ed; }
    .soft-alert.danger{ border-color:#f3b8ad; background:#fff1f0; }

    .soft-alert .title{
        color:var(--aws-text);
        font-weight:800;
        margin-bottom:4px;
    }

    .soft-alert .desc{
        color:var(--aws-muted);
        font-weight:600;
        font-size:.84rem;
        margin:0;
    }

    .modal .modal-content{
        border-radius:10px;
        border:1px solid var(--aws-line);
        box-shadow:0 24px 64px rgba(15,23,42,.18);
        overflow:hidden;
    }

    .modal .modal-header,
    .modal .modal-footer{
        background:#fbfbfb;
        border-color:var(--aws-line);
    }

    .omset-photo{
        max-height:240px;
        border-radius:8px;
        border:1px solid var(--aws-line);
        box-shadow:var(--aws-shadow);
        width:auto;
        background:#fff;
    }

    .select2-container{ width:100%!important; }

    .select2-container--default .select2-selection--single{
        border:1px solid var(--aws-line)!important;
        border-radius:8px!important;
        min-height:38px;
        display:flex!important;
        align-items:center;
        background:#fff;
    }

    .select2-container .select2-selection--single .select2-selection__rendered{
        line-height:36px!important;
        padding-left:.7rem!important;
        font-weight:700;
    }

    .select2-container .select2-selection--single .select2-selection__arrow{
        height:36px!important;
    }

    #tab-missing-dsc .dsc-scroll{ overflow:visible; }
    #tab-missing-dsc table.dsc-table th{ position:static!important; }
    #tab-missing-dsc .sticky-1,
    #tab-missing-dsc .sticky-2{ position:static!important; left:auto!important; box-shadow:none!important; }
    #tab-missing-dsc table.dsc-table td,
    #tab-missing-dsc table.dsc-table th{ white-space:normal!important; }

    #dscTableADJUSTMENT tr.row-warning td{
        background:#fff7ed!important;
    }

    @media (max-width:1200px){
        .filter-grid{ grid-template-columns:1fr 1fr 1fr; }
        .filter-grid .span-2{ grid-column:span 2; }
    }

    @media (max-width:992px){
        .dsc-meta-grid,
        .kpi-grid{
            grid-template-columns:1fr;
        }
    }

    @media (max-width:768px){
        .dsc-title h4{ font-size:1.15rem; }
        .dsc-toolbar{ width:100%; justify-content:flex-start; }
        .dsc-toolbar .btn{ flex:1 1 calc(50% - 8px); }
        .nav-pills{ overflow-x:auto; flex-wrap:nowrap; padding-bottom:0; }
        .nav-pills .nav-link{ white-space:nowrap; }
        .filter-grid{ grid-template-columns:1fr; }
        .filter-grid .span-2{ grid-column:auto; }
        .dsc-scroll,
        .dsc-scroll-y{
            max-height:66vh;
        }
        .dsc-scroll-x table,
        .dsc-scroll table{
            min-width:1200px;
        }
        .sticky-1{
            left:0;
        }
        .sticky-2{
            left:54px;
        }
        .w-no{ width:54px; min-width:54px; }
        .w-name{ width:180px; min-width:180px; max-width:180px; }
        .dsc-table th,
        .dsc-table td{
            padding:8px 9px;
            font-size:.76rem;
        }
        .dsc-table th{
            font-size:.68rem;
        }
    }

    @media (max-width:576px){
        .dsc-shell{ gap:12px; }
        .dsc-kv{ grid-template-columns:1fr; }
        .dsc-toolbar .btn{ flex:1 1 100%; }
        .dsc-card .dsc-card-body,
        .dsc-filter-bar{
            padding:12px;
        }
        .kpi{ padding:12px; }
        .kpi .value{ font-size:1rem; }
    }
</style>

<div class="dsc-shell">
    <div class="dsc-page">

                {{-- HEADER --}}
                <div class="dsc-header">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div>
                            <div class="dsc-title">
                                <span class="dsc-title-mark"><i class="bi bi-box-seam"></i></span>
                                <h4>DAILY STOCK CONTROL</h4>
                                <span class="dsc-badge"><i class="bi bi-shield-check"></i> REPORT</span>
                            </div>
                        </div>

                        <div class="dsc-toolbar">
                            @if (!empty($isSuperadmin))
                                <div class="dropdown dsc-bell-wrap">
                                    <button class="btn dsc-bell-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Outlet belum mengisi DSC H-1">
                                        <i class="bi bi-bell"></i>
                                        @if($missingCount > 0)
                                            <span class="dsc-bell-count">{{ $missingCount > 99 ? '99+' : $missingCount }}</span>
                                        @endif
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end dsc-bell-panel">
                                        <div class="dsc-bell-head">
                                            <i class="bi bi-bell me-1"></i> OUTLET BELUM MENGISI DSC
                                            <div class="dsc-help mt-1">PERIODE: {{ \Carbon\Carbon::parse($missingCheckStartDate)->format('d-m-Y') }}</div>
                                        </div>
                                        <div class="dsc-bell-body">
                                            @forelse($missingOutlets as $o)
                                                <div class="dsc-bell-item">
                                                    <div>{{ $cleanOutletName($o->nama_outlet ?? '-') }}</div>
                                                    <small>TERAKHIR ISI: {{ !empty($o->last_input_date) ? \Carbon\Carbon::parse($o->last_input_date)->format('d-m-Y') : '-' }}</small>
                                                </div>
                                            @empty
                                                <div class="dsc-bell-item text-success">
                                                    <i class="bi bi-check-circle me-1"></i> SEMUA OUTLET SUDAH MENGISI DSC.
                                                </div>
                                            @endforelse
                                        </div>
                                        <div class="dsc-bell-foot d-flex align-items-center justify-content-between gap-2">
                                            <span>DATA RINGKAS H-1.</span>
                                            <a class="btn btn-sm btn-primary" href="{{ route('master.dsc.missing', ['start_date' => $missingCheckStartDate, 'end_date' => $missingCheckEndDate]) }}">
                                                LIHAT SEMUA
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <button class="btn btn-sm btn-ghost"
                                onclick="window.location='{{ route('master.dsc.index') }}'">
                                <i class="bi bi-arrow-clockwise me-1"></i> REFRESH
                            </button>

                            <a class="btn btn-sm btn-success"
                                href="{{ route('master.dsc.export', [
                                    'outlet_id' => $outletId,
                                    'tanggal' => $today,
                                    'shift_filter' => $shiftFilter,
                                ]) }}"
                                @if (!$hasRequiredFilter) style="pointer-events:none;opacity:.55;" aria-disabled="true" @endif>
                                <i class="bi bi-file-earmark-excel me-1"></i> EXPORT HARI INI
                            </a>

                            <a class="btn btn-sm btn-success"
                                href="{{ route('master.dsc.export', [
                                    'outlet_id' => $outletId,
                                    'tanggal' => $today,
                                    'start_date' => $exportStartDate,
                                    'end_date' => $exportEndDate,
                                    'shift_filter' => $shiftFilter,
                                    'format' => 'stock_opname',
                                ]) }}"
                                @if (!$hasRequiredFilter) style="pointer-events:none;opacity:.55;" aria-disabled="true" @endif>
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i> EXPORT STOCK OPNAME
                            </a>
                        </div>
                    </div>
                </div>

                {{-- META + KPI --}}
                <div class="dsc-section">
                    <div class="dsc-meta-grid">
                        <div class="dsc-card">
                            <div class="dsc-card-body">
                                <div class="dsc-kv">
                                    <div class="k"><i class="bi bi-calendar2-week me-1"></i>Hari / Tanggal</div>
                                    <div class="v">: {{ \Carbon\Carbon::parse($today)->format('d-m-Y') }}</div>
                                    <div class="k"><i class="bi bi-journal-text me-1"></i>Catatan</div>
                                    <div class="v">: Monitoring + Koreksi SPV/Territorial Manager</div>
                                </div>

                                <div class="mt-3 dsc-note">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Gunakan filter Outlet & Tanggal untuk melihat data report.
                                </div>
                            </div>
                        </div>

                        <div class="dsc-card">
                            <div class="dsc-card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="text-muted fw-bold">Tanggal</div>
                                    <div class="fw-bold" style="font-size:1.25rem;">
                                        {{ (int) \Carbon\Carbon::parse($today)->format('d') }}
                                    </div>
                                </div>
                                <div class="dsc-help mt-2">Tanggal mengikuti filter.</div>
                            </div>
                        </div>
                    </div>

                    <div class="kpi-grid">
                        <div class="kpi primary">
                            <div>
                                <div class="label">Sales Shift 1</div>
                                <div class="value">Rp {{ number_format($salesShift1, 0, ',', '.') }}</div>
                            </div>
                            <div class="icon"><i class="bi bi-1-circle"></i></div>
                        </div>

                        <div class="kpi success">
                            <div>
                                <div class="label">Sales Shift 2</div>
                                <div class="value">Rp {{ number_format($salesShift2, 0, ',', '.') }}</div>
                            </div>
                            <div class="icon"><i class="bi bi-2-circle"></i></div>
                        </div>

                        <div class="kpi warning">
                            <div>
                                <div class="label">Total Sales</div>
                                <div class="value">Rp {{ number_format($salesTotal, 0, ',', '.') }}</div>
                            </div>
                            <div class="icon"><i class="bi bi-sigma"></i></div>
                        </div>
                    </div>
                </div>

                {{-- FILTER --}}
                <div class="dsc-filter">
                    <div class="dsc-filter-bar">
                        <form method="GET" action="{{ route('master.dsc.index') }}" id="dscFilterForm">
                            <div class="filter-grid">
                                <div class="filter-item span-2">
                                    <label><i class="bi bi-shop me-1"></i>OUTLET</label>
                                    <select name="outlet_id" id="outlet_id" class="form-select select2" style="width:100%;" required>
                                        <option value="">Wajib pilih outlet dulu</option>

                                        @if (!empty($selectedOutlet))
                                            <option value="{{ $selectedOutlet->id }}" selected>
                                                {{ $cleanOutletName($selectedOutlet->nama_outlet) }}
                                            </option>
                                        @elseif(!empty($outletId))
                                            <option value="{{ $outletId }}" selected>{{ $selectedOutletDisplay }}</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="filter-item">
                                    <label><i class="bi bi-calendar-event me-1"></i>TANGGAL</label>
                                    <input type="date" name="tanggal" class="form-control" value="{{ $today }}">
                                </div>

                                <div class="filter-item">
                                    <label><i class="bi bi-hourglass-split me-1"></i>SHIFT</label>
                                    <select name="shift_filter" class="form-select">
                                        <option value="all" {{ $shiftFilter === 'all' ? 'selected' : '' }}>Semua</option>
                                        <option value="1" {{ $shiftFilter === '1' ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $shiftFilter === '2' ? 'selected' : '' }}>Shift 2</option>
                                    </select>
                                </div>

                                <div class="filter-item span-2">
                                    <label><i class="bi bi-search me-1"></i>CARI BARANG</label>
                                    <input type="text" id="searchBarang" class="form-control" placeholder="Cari nama barang...">
                                </div>

                                <div class="filter-item">
                                    <button class="btn btn-primary w-100" type="submit">
                                        <i class="bi bi-funnel me-1"></i> TERAPKAN
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- BODY / TABS --}}
                <div class="dsc-body">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-rekap" type="button">
                                <i class="bi bi-table me-1"></i > REKAP
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-shift" type="button">
                                <i class="bi bi-list-check me-1"></i > DETAIL PER SHIFT
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-omset" type="button">
                                <i class="bi bi-receipt me-1"></i > OMSET & SETORAN
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-sales" type="button">
                                <i class="bi bi-cash-coin me-1"></i > SALES
                            </button>
                        </li>

                        @if ($canADJUSTMENT)
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ADJUSTMENT" type="button">
                                    <i class="bi bi-sliders me-1"></i > ADJUSTMENT
                                </button>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content">

                        {{-- REKAP --}}
                        <div class="tab-pane fade show active" id="tab-rekap">
                            <div class="dsc-scroll-y">
                              <div class="dsc-scroll-x">
                                <table id="dscTableRekap" class="table dsc-table">
                                    <thead>
                                        <tr>
                                            <th class="w-no sticky-1">NO</th>
                                            <th class="w-name sticky-2">NAMA BARANG</th>
                                            <th class="w-num">STATUS</th>
                                            <th class="w-sat">SAT</th>
                                            <th class="w-num">OPEN</th>
                                            <th class="w-num">PURCHASE IN</th>
                                            <th class="w-num">MUTASI IN</th>
                                            <th class="w-num">MUTASI OUT</th>
                                            <th class="w-num">ADJUSTMENT</th>
                                            <th class="w-num">TOTAL</th>
                                            <th class="w-num">ENDING</th>
                                            <th class="w-num">USED</th>
                                            <th class="w-num">WASTE PRODUK</th>
                                            <th class="w-num">WASTE BAHAN</th>
                                            <th class="w-num">WASTE TEPUNG</th>
                                            <th class="w-num">ACTUAL TEPUNG</th>
                                            <th class="w-num">USED S1</th>
                                            <th class="w-num">USED S2</th>
                                            <th class="w-num">UANG PLUS</th>
                                            <th class="w-wide">KETERANGAN</th>
                                            <th class="w-num">OPEN NEXT (R)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($rekapRows as $r)
                                            <tr>
                                                <td class="center sticky-1">{{ $r['no'] }}</td>
                                                <td class="sticky-2 item">{{ $r['nama'] }}</td>
                                                <td class="center">
                                                    @if ($shiftFilter === 'all')
                                                        <div class="d-flex flex-column gap-1 align-items-center">
                                                            <span class="badge {{ ($r['source_s1'] ?? null) === 'final' ? 'text-bg-success' : (($r['source_s1'] ?? null) === 'draft' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                                                                S1 {{ strtoupper($r['source_s1'] ?? '-') }}
                                                            </span>

                                                            <span class="badge {{ ($r['source_s2'] ?? null) === 'final' ? 'text-bg-success' : (($r['source_s2'] ?? null) === 'draft' ? 'text-bg-warning' : 'text-bg-secondary') }}">
                                                                S2 {{ strtoupper($r['source_s2'] ?? '-') }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        @if (($r['source'] ?? null) === 'final')
                                                            <span class="badge text-bg-success">FINAL</span>
                                                        @elseif(($r['source'] ?? null) === 'draft')
                                                            <span class="badge text-bg-warning">DRAFT</span>
                                                        @else
                                                            <span class="badge text-bg-secondary">-</span>
                                                        @endif
                                                    @endif
                                                </td>
                                                <td class="center">{{ $r['sat'] }}</td>
                                                <td class="num">{{ number_format($r['open'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['pin'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['mi'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['mo'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num fw-bold">{{ number_format($r['total'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['ending_stock'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num {{ ($r['used'] ?? 0) < 0 ? 'cell-negative' : '' }}">
                                                    {{ number_format($r['used'] ?? 0, 0, ',', '.') }}
                                                </td>
                                                <td class="num">{{ number_format($r['wP'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['wB'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['wT_input'] ?? $r['waste_tepung_input'] ?? $r['waste_tepung'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['actualTepung'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['shift1'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['shift2'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['uang'] ?? 0, 0, ',', '.') }}</td>
                                                <td>{{ $r['ket'] ?? '' }}</td>
                                                <td class="num">{{ number_format($r['open_stock_right'] ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="21" class="text-center text-muted py-4">
                                                    Data dikosongkan. Pilih outlet dan tanggal lalu klik <b>TERAPKAN</b>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                              </div>
                            </div>
                        </div>

                        {{-- DETAIL SHIFT --}}
                        <div class="tab-pane fade" id="tab-shift">
                            <div class="dsc-scroll">
                                <table id="dscTableShift" class="table dsc-table" style="min-width:1830px;">
                                    <thead>
                                        <tr>
                                            <th class="w-no sticky-1">NO</th>
                                            <th class="w-name sticky-2">NAMA BARANG</th>
                                            <th class="w-num">STATUS</th>
                                            <th class="w-sat">SAT</th>
                                            <th class="w-num">SHIFT</th>
                                            <th class="w-num">OPEN</th>
                                            <th class="w-num">PIN</th>
                                            <th class="w-num">MI</th>
                                            <th class="w-num">MO</th>
                                            <th class="w-num">ADJUSTMENT</th>
                                            <th class="w-num">TOTAL</th>
                                            <th class="w-num">ENDING</th>
                                            <th class="w-num">USED</th>
                                            <th class="w-num">WP</th>
                                            <th class="w-num">WB</th>
                                            <th class="w-num">WT</th>
                                            <th class="w-wide">KET</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($shiftRows as $r)
                                            <tr>
                                                <td class="center sticky-1">{{ $r['no'] }}</td>
                                                <td class="sticky-2 item">{{ $r['nama'] }}</td>
                                                <td class="center">
@if (($r['source'] ?? null) === 'final')
    <span class="badge text-bg-success">FINAL</span>
@elseif (($r['source'] ?? null) === 'draft')
    <span class="badge text-bg-warning">DRAFT</span>
@else
    <span class="badge text-bg-secondary">-</span>
@endif
                                                </td>
                                                <td class="center">{{ $r['sat'] }}</td>
                                                <td class="center">Shift {{ $r['shift'] }}</td>
                                                <td class="num">{{ number_format($r['open'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['pin'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['mi'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['mo'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num fw-bold">{{ number_format($r['total'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['ending_stock'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num {{ ($r['used'] ?? 0) < 0 ? 'cell-negative' : '' }}">
                                                    {{ number_format($r['used'] ?? 0, 0, ',', '.') }}
                                                </td>
                                                <td class="num">{{ number_format($r['wP'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['wB'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['wT_input'] ?? $r['waste_tepung_input'] ?? $r['waste_tepung'] ?? 0, 0, ',', '.') }}</td>
                                                <td>{{ $r['ket'] ?? '' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="17" class="text-center text-muted py-4">Data dikosongkan. Pilih outlet dan tanggal lalu klik <b>TERAPKAN</b>.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- OMSET --}}
                        <div class="tab-pane fade" id="tab-omset">
                            @if (!$hasRequiredFilter)
                                <div class="soft-alert primary">
                                    <div class="title"><i class="bi bi-info-circle me-1"></i> Info</div>
                                    <p class="desc mb-0">Data omset/setoran belum ditampilkan. Pilih outlet dan tanggal lalu klik TERAPKAN.</p>
                                </div>
                            @else
                                @if (empty($omsetActive['exists']))
                                    <div class="soft-alert warning">
                                        <div class="title"><i class="bi bi-exclamation-circle me-1"></i> Omset/Setoran belum ada</div>
                                        <p class="desc mb-0">
                                            Tidak ditemukan record <b>data omset setoran</b> untuk outlet ini pada tanggal <b>{{ $today }}</b>.
                                        </p>
                                    </div>
                                @else
                                    <div class="dsc-card mb-3">
                                        <div class="dsc-card-head">Informasi Umum</div>
                                        <div class="dsc-card-body">
                                            <div class="dsc-kv">
                                                <div class="k">PIC</div>
                                                <div class="v">: {{ $omsetActive['pic'] ?: '-' }}</div>

                                                <div class="k">Akumulasi Selisih</div>
                                                <div class="v">: {{ number_format($omsetActive['akumulasi_selisih'] ?? 0, 0, ',', '.') }}</div>

                                                <div class="k">Kekurangan Bulan Lalu</div>
                                                <div class="v">: {{ number_format($omsetActive['kekurangan_bulan_lalu'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-lg-6">
                                            <div class="dsc-card h-100">
                                                <div class="dsc-card-head">Shift 1</div>
                                                <div class="dsc-card-body">
                                                    <div class="dsc-kv mb-3">
                                                        <div class="k">Tanggal Setor</div>
                                                        <div class="v">: {{ $omsetActive['s1']['tanggal_setor'] ?: '-' }}</div>

                                                        <div class="k">Sudah Disetor</div>
                                                        <div class="v">:
                                                            @if (!empty($omsetActive['s1']['sudah_disetor']))
                                                                <span class="badge text-bg-success">YA</span>
                                                            @else
                                                                <span class="badge text-bg-secondary">TIDAK</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if (!empty($omsetActive['s1']['foto_url']))
                                                        <div class="mb-3">
                                                            <div class="dsc-help mb-1">Bukti Foto Shift 1</div>
                                                            <a href="{{ $omsetActive['s1']['foto_url'] }}" target="_blank" rel="noopener">
                                                                <img class="omset-photo" src="{{ $omsetActive['s1']['foto_url'] }}" alt="Bukti Foto Shift 1">
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div class="dsc-help mb-3 text-muted">Belum ada foto shift 1.</div>
                                                    @endif

                                                    <table class="table dsc-table mb-0">
                                                        <tbody>
                                                            <tr><td class="item">Total Transaction</td><td class="num">{{ number_format($omsetActive['s1']['total_transaction'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Diskon</td><td class="num">{{ number_format($omsetActive['s1']['diskon'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Non Tunai</td><td class="num">{{ number_format($omsetActive['s1']['non_tunai'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Expense</td><td class="num">{{ number_format($omsetActive['s1']['expense'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Uang Fisik</td><td class="num">{{ number_format($omsetActive['s1']['uang_fisik'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Admin Pot Sales</td><td class="num">{{ number_format($omsetActive['s1']['admin_pot_sales'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">ADJUSTMENT</td><td class="num">{{ number_format($omsetActive['s1']['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Selisih (Minus)</td><td class="num">{{ number_format($omsetActive['s1']['selisih_minus'] ?? 0, 0, ',', '.') }}</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6">
                                            <div class="dsc-card h-100">
                                                <div class="dsc-card-head">Shift 2</div>
                                                <div class="dsc-card-body">
                                                    <div class="dsc-kv mb-3">
                                                        <div class="k">Tanggal Setor</div>
                                                        <div class="v">: {{ $omsetActive['s2']['tanggal_setor'] ?: '-' }}</div>

                                                        <div class="k">Sudah Disetor</div>
                                                        <div class="v">:
                                                            @if (!empty($omsetActive['s2']['sudah_disetor']))
                                                                <span class="badge text-bg-success">YA</span>
                                                            @else
                                                                <span class="badge text-bg-secondary">TIDAK</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if (!empty($omsetActive['s2']['foto_url']))
                                                        <div class="mb-3">
                                                            <div class="dsc-help mb-1">Bukti Foto Shift 2</div>
                                                            <a href="{{ $omsetActive['s2']['foto_url'] }}" target="_blank" rel="noopener">
                                                                <img class="omset-photo" src="{{ $omsetActive['s2']['foto_url'] }}" alt="Bukti Foto Shift 2">
                                                            </a>
                                                        </div>
                                                    @else
                                                        <div class="dsc-help mb-3 text-muted">Belum ada foto shift 2.</div>
                                                    @endif

                                                    <table class="table dsc-table mb-0">
                                                        <tbody>
                                                            <tr><td class="item">Total Transaction</td><td class="num">{{ number_format($omsetActive['s2']['total_transaction'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Diskon</td><td class="num">{{ number_format($omsetActive['s2']['diskon'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Non Tunai</td><td class="num">{{ number_format($omsetActive['s2']['non_tunai'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Expense</td><td class="num">{{ number_format($omsetActive['s2']['expense'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Uang Fisik</td><td class="num">{{ number_format($omsetActive['s2']['uang_fisik'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Admin Pot Sales</td><td class="num">{{ number_format($omsetActive['s2']['admin_pot_sales'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">ADJUSTMENT</td><td class="num">{{ number_format($omsetActive['s2']['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td></tr>
                                                            <tr><td class="item">Selisih (Minus)</td><td class="num">{{ number_format($omsetActive['s2']['selisih_minus'] ?? 0, 0, ',', '.') }}</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="dsc-card">
                                                <div class="dsc-card-head">Total Omset & Setoran</div>
                                                <div class="dsc-card-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-3">
                                                            <div class="kpi primary">
                                                                <div>
                                                                    <div class="label">Total Transaction</div>
                                                                    <div class="value">{{ number_format($omsetActive['total']['total_transaction'] ?? 0, 0, ',', '.') }}</div>
                                                                </div>
                                                                <div class="icon"><i class="bi bi-receipt"></i></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="kpi success">
                                                                <div>
                                                                    <div class="label">Total Uang Fisik</div>
                                                                    <div class="value">{{ number_format($omsetActive['total']['uang_fisik'] ?? 0, 0, ',', '.') }}</div>
                                                                </div>
                                                                <div class="icon"><i class="bi bi-cash-stack"></i></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="kpi warning">
                                                                <div>
                                                                    <div class="label">Total ADJUSTMENT</div>
                                                                    <div class="value">{{ number_format($omsetActive['total']['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</div>
                                                                </div>
                                                                <div class="icon"><i class="bi bi-sliders"></i></div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="kpi primary">
                                                                <div>
                                                                    <div class="label">Total Selisih Minus</div>
                                                                    <div class="value">{{ number_format($omsetActive['total']['selisih_minus'] ?? 0, 0, ',', '.') }}</div>
                                                                </div>
                                                                <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="dsc-scroll mt-3">
                                                        <table class="table dsc-table mb-0" id="dscTableOmset">
                                                            <thead>
                                                                <tr>
                                                                    <th>Komponen</th>
                                                                    <th>Shift 1</th>
                                                                    <th>Shift 2</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="item">Total Transaction</td>
                                                                    <td class="num">{{ number_format($omsetActive['s1']['total_transaction'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['s2']['total_transaction'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['total']['total_transaction'] ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="item">Diskon</td>
                                                                    <td class="num">{{ number_format($omsetActive['s1']['diskon'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['s2']['diskon'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['total']['diskon'] ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="item">Non Tunai</td>
                                                                    <td class="num">{{ number_format($omsetActive['s1']['non_tunai'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['s2']['non_tunai'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['total']['non_tunai'] ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="item">Expense</td>
                                                                    <td class="num">{{ number_format($omsetActive['s1']['expense'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['s2']['expense'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['total']['expense'] ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="item">Uang Fisik</td>
                                                                    <td class="num">{{ number_format($omsetActive['s1']['uang_fisik'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['s2']['uang_fisik'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['total']['uang_fisik'] ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="item">Admin Pot Sales</td>
                                                                    <td class="num">{{ number_format($omsetActive['s1']['admin_pot_sales'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['s2']['admin_pot_sales'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['total']['admin_pot_sales'] ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="item">ADJUSTMENT</td>
                                                                    <td class="num">{{ number_format($omsetActive['s1']['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['s2']['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['total']['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="item">Selisih Minus</td>
                                                                    <td class="num">{{ number_format($omsetActive['s1']['selisih_minus'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['s2']['selisih_minus'] ?? 0, 0, ',', '.') }}</td>
                                                                    <td class="num">{{ number_format($omsetActive['total']['selisih_minus'] ?? 0, 0, ',', '.') }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- SALES --}}
                        <div class="tab-pane fade" id="tab-sales">
                            <div class="kpi-grid">
                                <div class="kpi primary">
                                    <div>
                                        <div class="label">Sales Shift 1</div>
                                        <div class="value">Rp {{ number_format($salesShift1, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="icon"><i class="bi bi-1-circle"></i></div>
                                </div>
                                <div class="kpi success">
                                    <div>
                                        <div class="label">Sales Shift 2</div>
                                        <div class="value">Rp {{ number_format($salesShift2, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="icon"><i class="bi bi-2-circle"></i></div>
                                </div>
                                <div class="kpi warning">
                                    <div>
                                        <div class="label">Total Sales</div>
                                        <div class="value">Rp {{ number_format($salesTotal, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="icon"><i class="bi bi-sigma"></i></div>
                                </div>
                            </div>

                            <div class="soft-alert primary mt-3">
                                <div class="title"><i class="bi bi-info-circle me-1"></i> Info</div>
                                <p class="desc mb-0">Tab Sales hanya menampilkan data dari backend (GET). Tidak ada input / save di halaman ini.</p>
                            </div>
                        </div>

                        {{-- ADJUSTMENT --}}
                        @if ($canADJUSTMENT)
                          <div class="tab-pane fade" id="tab-ADJUSTMENT" x-data="{ compact:false }">
                            {{-- Panel atas ADJUSTMENT dihilangkan sesuai request.
                                 Elemen status dan PIC tetap disediakan hidden supaya autosave/manual JS tidak error. --}}
                            <span class="d-none" id="ADJUSTMENTAutoStatus"><i class="bi bi-circle-fill"></i> Siap</span>
                            <input type="hidden" id="ADJUSTMENT_tab_pic" value="{{ auth()->user()->name ?? auth()->user()->email ?? 'System' }}">

                            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                              <div class="flex flex-col gap-2 border-b border-slate-200 bg-white px-4 py-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                  <div class="font-black text-slate-900">ADJUSTMENT</div>
                                  <div class="text-xs font-semibold text-slate-500">
                                    Ubah ADJUSTMENT / WASTE PRODUKRODUK / WASTE BAHAN / WASTE TEPUNG, lalu autosave seperti spreadsheet. ADJUSTMENT tersimpan ke kolom ADJUSTMENT_qty dan angka TOTAL/USED/ACTUAL ikut dihitung ulang.
                                  </div>
                                </div>
                                <span class="badge-wh"><i class="bi bi-table"></i> Spreadsheet Autosave</span>
                              </div>

                              <div class="dsc-scroll-y">
                                <div class="dsc-scroll-x">
                                  <table id="dscTableADJUSTMENT" class="table dsc-table mb-0" style="min-width:2100px;">
                                    <thead>
                                      <tr>
                                        <th class="w-no sticky-1">NO</th>
                                        <th class="w-name sticky-2">NAMA BARANG</th>
                                        <th class="w-num">STATUS</th>
                                        <th class="w-sat">SAT</th>
                                        <th class="w-num">SHIFT</th>
                                        <th class="w-num" x-show="!compact">OPEN</th>
                                        <th class="w-num" x-show="!compact">PIN</th>
                                        <th class="w-num" x-show="!compact">MI</th>
                                        <th class="w-num" x-show="!compact">MO</th>
                                        <th class="w-num">ADJUSTMENT</th>
                                        <th class="w-num">TOTAL</th>
                                        <th class="w-num">ENDING</th>
                                        <th class="w-num">USED</th>
                                        <th class="w-num">WASTE PRODUK</th>
                                        <th class="w-num">WASTE BAHAN</th>
                                        <th class="w-num">WASTE TEPUNG</th>
                                        <th class="w-num">ACTUAL TEPUNG</th>
                                        <th class="w-wide">KETERANGAN</th>
                                      </tr>
                                    </thead>

                                    <tbody>
                                      @forelse($shiftRows as $r)
                                        @php
                                          $open = (float) ($r['open'] ?? 0);
                                          $pin  = (float) ($r['pin'] ?? 0);
                                          $mi   = (float) ($r['mi'] ?? 0);
                                          $mo   = (float) ($r['mo'] ?? 0);
                                          $ADJUSTMENT  = (float) ($r['ADJUSTMENT'] ?? 0);
                                          $total = (float) ($r['total'] ?? ($open + $pin + $mi - $mo + $ADJUSTMENT));
                                          $ending = (float) ($r['ending_stock'] ?? 0);
                                          $used = (float) ($r['used'] ?? ($total - $ending));

                                          $wP = (float) ($r['wP'] ?? 0);
                                          $wB = (float) ($r['wB'] ?? 0);
                                          // Di tab ADJUSTMENT, input WASTE TEPUNG adalah nilai waste_tepung mentah.
                                          // Kalau shiftRows lama hanya punya wT agregat, fallback tetap aman.
                                          $wT = (float) ($r['wT_input'] ?? $r['waste_tepung_input'] ?? $r['waste_tepung'] ?? $r['wT'] ?? 0);
                                          $namaNormADJUSTMENT = strtolower(trim((string) ($r['nama'] ?? '')));
                                          $effectiveWasteADJUSTMENT = $wP + $wB + $wT;
                                          $actualTepung = $namaNormADJUSTMENT === 'tepung breader' ? ($used - $effectiveWasteADJUSTMENT) : 0;
                                          $sourceADJUSTMENT = $r['source'] ?? null;
                                        @endphp

                                        <tr class="ADJUSTMENT-row"
                                            data-outlet-id="{{ $r['outlet_id'] ?? ($selectedOutletIds[0] ?? $outletId) }}"
                                            data-bahan-id="{{ $r['bahan_id'] }}"
                                            data-shift="{{ $r['shift'] }}"
                                            data-nama="{{ $r['nama'] }}"
                                            data-open="{{ $open }}"
                                            data-pin="{{ $pin }}"
                                            data-mi="{{ $mi }}"
                                            data-mo="{{ $mo }}"
                                            data-ending="{{ $ending }}"
                                            data-used="{{ $used }}"
                                            data-ADJUSTMENT-current="{{ $ADJUSTMENT }}"
                                            data-ADJUSTMENT-original="{{ $ADJUSTMENT }}"
                                            data-wp-original="{{ $wP }}"
                                            data-wb-original="{{ $wB }}"
                                            data-wt-original="{{ $wT }}">
                                            <td class="center sticky-1">{{ $r['no'] }}</td>
                                            <td class="sticky-2 item">{{ $r['nama'] }}</td>
                                            <td class="center">
                                              @if ($sourceADJUSTMENT === 'final')
                                                <span class="badge text-bg-success">FINAL</span>
                                              @elseif($sourceADJUSTMENT === 'draft')
                                                <span class="badge text-bg-warning">DRAFT</span>
                                              @else
                                                <span class="badge text-bg-secondary">-</span>
                                              @endif
                                            </td>
                                            <td class="center">{{ $r['sat'] }}</td>
                                            <td class="center">Shift {{ $r['shift'] }}</td>
                                            <td class="num" x-show="!compact">{{ number_format($open, 0, ',', '.') }}</td>
                                            <td class="num" x-show="!compact">{{ number_format($pin, 0, ',', '.') }}</td>
                                            <td class="num" x-show="!compact">{{ number_format($mi, 0, ',', '.') }}</td>
                                            <td class="num" x-show="!compact">{{ number_format($mo, 0, ',', '.') }}</td>
                                            <td class="num">
                                              <input type="number" step="0.01"
                                                     class="form-control form-control-sm text-end ADJUSTMENT-input ADJUSTMENT-ADJUSTMENT-input"
                                                     value="{{ $ADJUSTMENT }}"
                                                     oninput="scheduleADJUSTMENTAutoSave(this)"
                                                     onchange="scheduleADJUSTMENTAutoSave(this)"
                                                     onblur="scheduleADJUSTMENTAutoSave(this)"
                                                     onclick="scheduleADJUSTMENTAutoSave(this)">
                                            </td>
                                            <td class="num fw-bold ADJUSTMENT-total">{{ number_format($total, 0, ',', '.') }}</td>
                                            <td class="num ADJUSTMENT-ending">{{ number_format($ending, 0, ',', '.') }}</td>
                                            <td class="num ADJUSTMENT-used {{ $used < 0 ? 'cell-negative' : '' }}">{{ number_format($used, 0, ',', '.') }}</td>

                                            <td class="num">
                                              <input type="number" step="0.01"
                                                     class="form-control form-control-sm text-end ADJUSTMENT-input ADJUSTMENT-wp-input"
                                                     value="{{ $wP }}"
                                                     oninput="scheduleADJUSTMENTAutoSave(this)"
                                                     onchange="scheduleADJUSTMENTAutoSave(this)"
                                                     onblur="scheduleADJUSTMENTAutoSave(this)"
                                                     onclick="scheduleADJUSTMENTAutoSave(this)">
                                            </td>
                                            <td class="num">
                                              <input type="number" step="0.01"
                                                     class="form-control form-control-sm text-end ADJUSTMENT-input ADJUSTMENT-wb-input"
                                                     value="{{ $wB }}"
                                                     oninput="scheduleADJUSTMENTAutoSave(this)"
                                                     onchange="scheduleADJUSTMENTAutoSave(this)"
                                                     onblur="scheduleADJUSTMENTAutoSave(this)"
                                                     onclick="scheduleADJUSTMENTAutoSave(this)">
                                            </td>
                                            <td class="num">
                                              <input type="number" step="0.01"
                                                     class="form-control form-control-sm text-end ADJUSTMENT-input ADJUSTMENT-wt-input"
                                                     value="{{ $wT }}"
                                                     oninput="scheduleADJUSTMENTAutoSave(this)"
                                                     onchange="scheduleADJUSTMENTAutoSave(this)"
                                                     onblur="scheduleADJUSTMENTAutoSave(this)"
                                                     onclick="scheduleADJUSTMENTAutoSave(this)">
                                            </td>
                                            <td class="num ADJUSTMENT-actual-tepung">{{ number_format($actualTepung, 0, ',', '.') }}</td>
                                            <td class="fw-bold text-muted">ADJUSTMENT</td>
                                        </tr>
                                      @empty
                                        <tr>
                                          <td colspan="18" class="text-center text-muted py-4">
                                            Data dikosongkan. Pilih outlet dan tanggal lalu klik <b>TERAPKAN</b>.
                                          </td>
                                        </tr>
                                      @endforelse
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>

                            <div class="d-none" id="ADJUSTMENT_tab_meta"></div>
                          </div>
                        @endif
                        {{-- MODAL IMPORT --}}
                        @if ($canADJUSTMENT)
                            <div class="modal fade" id="modalADJUSTMENTImport" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">
                                                <i class="bi bi-file-earmark-arrow-up me-2"></i> Import ADJUSTMENT (Preview)
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="soft-alert warning mb-3">
                                                <div class="title">Import ADJUSTMENT</div>
                                                <p class="desc mb-0">
                                                    Preview: <code>/dsc/ADJUSTMENT/import-preview</code> • Apply:
                                                    <code>/dsc/ADJUSTMENT/apply</code><br>
                                                    Format CSV/TXT: <code>bahan_id</code> atau <code>nama_bahan</code>, lalu kolom <code>ADJUSTMENT_qty</code>/<code>ADJUSTMENT</code>. Opsional: <code>waste_product</code>, <code>waste_bahan</code>, <code>waste_tepung</code>.
                                                </p>
                                            </div>

                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">OUTLET</label>
                                                    <input class="form-control" readonly value="{{ $outletId }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">Tanggal</label>
                                                    <input class="form-control" readonly value="{{ $today }}">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-bold">SHIFT</label>
                                                    <select class="form-select" id="ADJUSTMENT_imp_shift">
                                                        <option value="1">Shift 1</option>
                                                        <option value="2">Shift 2</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-bold">File</label>
                                                    <input class="form-control" type="file" id="ADJUSTMENT_imp_file"
                                                        accept=".csv,.txt">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label fw-bold">PIC <span class="text-danger">*</span></label>
                                                    <input class="form-control" id="ADJUSTMENT_imp_pic" placeholder="Wajib">
                                                </div>

                                                <div class="col-12">
                                                    <div class="d-flex gap-2 flex-wrap align-items-center">
                                                        <button class="btn btn-warning" id="btnADJUSTMENTPreview" type="button">
                                                            <i class="bi bi-search me-1"></i> Preview
                                                        </button>
                                                        <button class="btn btn-primary" id="btnADJUSTMENTApply" type="button" disabled>
                                                            <i class="bi bi-cloud-arrow-up me-1"></i> Apply
                                                        </button>
                                                        <div class="dsc-help" id="ADJUSTMENT_imp_meta">Belum preview.</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="my-3">

                                            <div class="dsc-scroll">
                                                <table class="table dsc-table" id="ADJUSTMENTImpTable" style="min-width:900px;">
                                                    <thead>
                                                        <tr>
                                                            <th class="w-no sticky-1">NO</th>
                                                            <th class="w-name sticky-2">NAMA BARANG</th>
                                                            <th class="w-num">STATUS</th>
                                                            <th class="w-sat">SAT</th>
                                                            <th class="w-num">ADJUSTMENT</th>
                                                            <th class="w-num">WASTE PRODUK</th>
                                                            <th class="w-num">WASTE BAHAN</th>
                                                            <th class="w-num">WASTE TEPUNG</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="8" class="text-center text-muted py-4">
                                                                Preview akan tampil di sini.
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-3" id="ADJUSTMENTImpErrorsWrap" style="display:none;">
                                                <div class="soft-alert danger">
                                                    <div class="title"><i class="bi bi-exclamation-triangle me-1"></i> Error</div>
                                                    <ul class="mb-0" id="ADJUSTMENTImpErrors"></ul>
                                                </div>
                                            </div>

                                            <div class="mt-2" id="ADJUSTMENTImpWarnWrap" style="display:none;">
                                                <div class="soft-alert primary">
                                                    <div class="title"><i class="bi bi-info-circle me-1"></i> Warning</div>
                                                    <ul class="mb-0" id="ADJUSTMENTImpWarn"></ul>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-ghost" data-bs-dismiss="modal">
                                                <i class="bi bi-x-lg me-1"></i> Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

    </div>
</div>




@push('scripts')
<script>
(function () {
    if (typeof Swal === 'undefined') {
        window.Swal = {
            fire: (o) => alert(o.title || o.text || 'Info'),
            showLoading: () => {},
            close: () => {}
        };
    }

    const BASE_URL = `{{ url('') }}`;
    const URL_DSC_ADJUSTMENT_IMPORT_PREVIEW = BASE_URL + '/dsc/ADJUSTMENT/import-preview';
    const URL_DSC_ADJUSTMENT_APPLY = BASE_URL + '/dsc/ADJUSTMENT/apply';

    let ADJUSTMENTAutoTimer = null;
    let ADJUSTMENTAutoRunning = false;
    let ADJUSTMENTAutoQueued = false;

    let ADJUSTMENT_IMP_CACHE = {
        ok: false,
        items: [],
        errors: [],
        warnings: [],
        meta: null
    };

    function apiHeaders() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return {
            'X-CSRF-TOKEN': meta ? meta.content : '',
            'Accept': 'application/json'
        };
    }

    function toNum(v) {
        let s = (v ?? '').toString().trim();
        if (!s) return 0;
        // Input type=number dari browser memakai titik desimal (-5.01), sedangkan tampilan Indonesia memakai koma (-5,01).
        // Hapus pemisah ribuan hanya jika formatnya jelas ribuan; jangan mengubah -5.01 menjadi -501.
        if (s.includes(',') && s.includes('.')) {
            s = s.replace(/\./g, '').replace(',', '.');
        } else if (s.includes(',')) {
            s = s.replace(',', '.');
        } else if (/^-?\d{1,3}(\.\d{3})+(\.\d+)?$/.test(s)) {
            s = s.replace(/\./g, '');
        }
        const n = Number(s);
        return Number.isFinite(n) ? n : 0;
    }

    function fmt(n) {
        const x = Number(n || 0);
        return x.toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function setADJUSTMENTStatus(text, type = 'info') {
        const icon = type === 'ok' ? 'bi-check-circle-fill'
            : type === 'bad' ? 'bi-x-circle-fill'
            : type === 'warn' ? 'bi-exclamation-circle-fill'
            : 'bi-circle-fill';

        const cls = type === 'ok' ? 'text-success'
            : type === 'bad' ? 'text-danger'
            : type === 'warn' ? 'text-warning'
            : 'text-primary';

        $('#ADJUSTMENTAutoStatus').html(`<i class="bi ${icon} ${cls}"></i> ${text}`);
        $('#ADJUSTMENT_tab_meta').html(text);
    }

    function recalcADJUSTMENTTabRow($tr) {
        const open = toNum($tr.data('open'));
        const pin = toNum($tr.data('pin'));
        const mi = toNum($tr.data('mi'));
        const mo = toNum($tr.data('mo'));
        const ending = toNum($tr.data('ending'));
        const nama = (($tr.data('nama') || '') + '').trim().toLowerCase();

        const ADJUSTMENT = toNum($tr.find('.ADJUSTMENT-ADJUSTMENT-input').val());
        const wp = toNum($tr.find('.ADJUSTMENT-wp-input').val());
        const wb = toNum($tr.find('.ADJUSTMENT-wb-input').val());
        const wt = toNum($tr.find('.ADJUSTMENT-wt-input').val());

        const total = open + pin + mi - mo + ADJUSTMENT;
        const used = total - ending;
        const effectiveWasteTepung = wp + wb + wt;
        const actualTepung = nama === 'tepung breader' ? (used - effectiveWasteTepung) : 0;

        $tr.data('used', used);
        $tr.find('.ADJUSTMENT-total').text(fmt(total));
        $tr.find('.ADJUSTMENT-used').text(fmt(used)).toggleClass('cell-negative', used < 0);
        $tr.find('.ADJUSTMENT-actual-tepung').text(fmt(actualTepung));

        const originalADJUSTMENT = toNum($tr.data('ADJUSTMENT-original'));
        const originalWp = toNum($tr.data('wp-original'));
        const originalWb = toNum($tr.data('wb-original'));
        const originalWt = toNum($tr.data('wt-original'));

        const changed = Math.abs(ADJUSTMENT - originalADJUSTMENT) > 0.0000001
            || Math.abs(wp - originalWp) > 0.0000001
            || Math.abs(wb - originalWb) > 0.0000001
            || Math.abs(wt - originalWt) > 0.0000001;

        $tr.toggleClass('row-warning', changed);
    }

    function initADJUSTMENTTabRecalc() {
        $('#dscTableADJUSTMENT tbody tr.ADJUSTMENT-row').each(function () {
            recalcADJUSTMENTTabRow($(this));
        });
    }

    function collectADJUSTMENTTabItems({ changedOnly = false } = {}) {
        const items = [];

        $('#dscTableADJUSTMENT tbody tr.ADJUSTMENT-row').each(function () {
            const $tr = $(this);
            const outletId = Number($tr.data('outlet-id')) || Number(`{{ is_numeric($outletId ?? null) ? $outletId : ($selectedOutletIds[0] ?? 0) }}`);
            const bahanId = Number($tr.data('bahan-id'));
            const shift = Number($tr.data('shift'));

            if (!outletId || !bahanId || !shift) return;

            const ADJUSTMENT = toNum($tr.find('.ADJUSTMENT-ADJUSTMENT-input').val());
            const wp = toNum($tr.find('.ADJUSTMENT-wp-input').val());
            const wb = toNum($tr.find('.ADJUSTMENT-wb-input').val());
            const wt = toNum($tr.find('.ADJUSTMENT-wt-input').val());

            const originalADJUSTMENT = toNum($tr.data('ADJUSTMENT-original'));
            const originalWp = toNum($tr.data('wp-original'));
            const originalWb = toNum($tr.data('wb-original'));
            const originalWt = toNum($tr.data('wt-original'));

            const changed = Math.abs(ADJUSTMENT - originalADJUSTMENT) > 0.0000001
                || Math.abs(wp - originalWp) > 0.0000001
                || Math.abs(wb - originalWb) > 0.0000001
                || Math.abs(wt - originalWt) > 0.0000001;

            if (changedOnly && !changed) {
                return;
            }

            items.push({
                outlet_id: outletId,
                bahan_id: bahanId,
                shift: shift,
                ADJUSTMENT_qty: ADJUSTMENT,
                waste_product: wp,
                waste_bahan: wb,
                waste_tepung: wt
            });
        });

        return items;
    }

    function validateADJUSTMENTHeader({ silent = false } = {}) {
        const outletId = Number(`{{ is_numeric($outletId ?? null) ? $outletId : ($selectedOutletIds[0] ?? 0) }}`);
        const pic = ($('#ADJUSTMENT_tab_pic').val() || '').trim();

        if (!outletId) {
            if (!silent) Swal.fire({ icon: 'warning', title: 'Pilih outlet dulu' });
            return false;
        }

        if (!pic) {
            setADJUSTMENTStatus('PIC wajib diisi agar autosave aktif.', 'warn');
            if (!silent) Swal.fire({ icon: 'warning', title: 'PIC wajib diisi' });
            return false;
        }

        return true;
    }

    async function saveADJUSTMENT({ silent = false, changedOnly = false } = {}) {
        if (ADJUSTMENTAutoRunning) {
            ADJUSTMENTAutoQueued = true;
            return;
        }

        if (!validateADJUSTMENTHeader({ silent })) return;

        const outletId = Number(`{{ is_numeric($outletId ?? null) ? $outletId : ($selectedOutletIds[0] ?? 0) }}`);
        const tanggal = `{{ $today }}`;
        const pic = ($('#ADJUSTMENT_tab_pic').val() || '').trim();
        const items = collectADJUSTMENTTabItems({ changedOnly });

        if (!items.length) {
            if (!silent) Swal.fire({ icon: 'info', title: 'Tidak ada perubahan ADJUSTMENT' });
            setADJUSTMENTStatus('Tidak ada perubahan.', 'info');
            return;
        }

        try {
            ADJUSTMENTAutoRunning = true;
            setADJUSTMENTStatus(silent ? 'Auto saving ADJUSTMENT...' : 'Menyimpan ADJUSTMENT...', 'info');

            if (!silent) {
                Swal.fire({
                    title: 'Menyimpan ADJUSTMENT...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            }

            const res = await fetch(URL_DSC_ADJUSTMENT_APPLY, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...apiHeaders()
                },
                body: JSON.stringify({
                    outlet_id: outletId,
                    tanggal: tanggal,
                    nama_petugas: pic,
                    note: silent ? 'Auto save ADJUSTMENT' : 'Manual save ADJUSTMENT',
                    items: items
                })
            });

            const json = await res.json().catch(() => ({}));

            if (!res.ok || !json.ok) {
                throw new Error(json.message || 'Gagal simpan ADJUSTMENT');
            }

            // Update baseline supaya autosave berikutnya tidak mengirim ulang item yang sama.
            const serverRows = (json.data && Array.isArray(json.data.rows)) ? json.data.rows : items;
            serverRows.forEach(function (item) {
                const rowOutlet = Number(item.outlet_id) || Number(`{{ is_numeric($outletId ?? null) ? $outletId : ($selectedOutletIds[0] ?? 0) }}`);
                let $tr = $(`#dscTableADJUSTMENT tbody tr.ADJUSTMENT-row[data-outlet-id="${rowOutlet}"][data-bahan-id="${item.bahan_id}"][data-shift="${item.shift}"]`);
                if (!$tr.length) {
                    $tr = $(`#dscTableADJUSTMENT tbody tr.ADJUSTMENT-row[data-bahan-id="${item.bahan_id}"][data-shift="${item.shift}"]`).first();
                }
                if (!$tr.length) return;

                $tr.data('ADJUSTMENT-current', item.ADJUSTMENT_qty);
                $tr.data('ADJUSTMENT-original', item.ADJUSTMENT_qty);
                $tr.data('wp-original', item.waste_product);
                $tr.data('wb-original', item.waste_bahan);
                $tr.data('wt-original', item.waste_tepung);

                if (typeof item.ending_stock !== 'undefined') $tr.data('ending', item.ending_stock);
                if (typeof item.used_qty !== 'undefined') $tr.data('used', item.used_qty);

                $tr.find('.ADJUSTMENT-ADJUSTMENT-input').val(item.ADJUSTMENT_qty);
                $tr.find('.ADJUSTMENT-wp-input').val(item.waste_product);
                $tr.find('.ADJUSTMENT-wb-input').val(item.waste_bahan);
                $tr.find('.ADJUSTMENT-wt-input').val(item.waste_tepung);

                recalcADJUSTMENTTabRow($tr);
                $tr.removeClass('row-warning');
            });

            setADJUSTMENTStatus(silent ? 'Auto saved ADJUSTMENT.' : 'ADJUSTMENT WASTE TEPUNGersimpan.', 'ok');

            if (!silent) {
                Swal.close();
                await Swal.fire({
                    icon: 'success',
                    title: 'Tersimpan',
                    timer: 1000,
                    showConfirmButton: false
                });
                location.reload();
            }

        } catch (err) {
            console.error(err);
            setADJUSTMENTStatus('Gagal save: ' + (err.message || 'Error'), 'bad');

            if (!silent) {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.message || 'Error' });
            }
        } finally {
            ADJUSTMENTAutoRunning = false;

            if (ADJUSTMENTAutoQueued) {
                ADJUSTMENTAutoQueued = false;
                window.scheduleADJUSTMENTAutoSave(null);
            }
        }
    }

    window.scheduleADJUSTMENTAutoSave = function (el) {
        if (el) {
            recalcADJUSTMENTTabRow($(el).closest('tr'));
        }

        clearTimeout(ADJUSTMENTAutoTimer);

        ADJUSTMENTAutoTimer = setTimeout(function () {
            saveADJUSTMENT({
                silent: true,
                changedOnly: true
            });
        }, 1200);
    };

    window.scheduleADJUSTMENTAutoSaveFromPic = function () {
        clearTimeout(ADJUSTMENTAutoTimer);

        ADJUSTMENTAutoTimer = setTimeout(function () {
            if (validateADJUSTMENTHeader({ silent: true })) {
                saveADJUSTMENT({
                    silent: true,
                    changedOnly: true
                });
            }
        }, 900);
    };

    window.resetADJUSTMENTChangedOnly = function () {
        $('#dscTableADJUSTMENT tbody tr.ADJUSTMENT-row').each(function () {
            const $tr = $(this);
            $tr.find('.ADJUSTMENT-ADJUSTMENT-input').val(toNum($tr.data('ADJUSTMENT-original')));
            $tr.find('.ADJUSTMENT-wp-input').val(toNum($tr.data('wp-original')));
            $tr.find('.ADJUSTMENT-wb-input').val(toNum($tr.data('wb-original')));
            $tr.find('.ADJUSTMENT-wt-input').val(toNum($tr.data('wt-original')));
            recalcADJUSTMENTTabRow($tr);
            $tr.removeClass('row-warning');
        });

        setADJUSTMENTStatus('Perubahan input dikembalikan.', 'info');
    };

    $(document).off('input change keyup blur click mouseup', '.ADJUSTMENT-input');
    $(document).on('input change keyup blur click mouseup', '.ADJUSTMENT-input', function () {
        window.scheduleADJUSTMENTAutoSave(this);
    });

    $(document).off('keydown.ADJUSTMENTEnter', '.ADJUSTMENT-input');
    $(document).on('keydown.ADJUSTMENTEnter', '.ADJUSTMENT-input', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            window.scheduleADJUSTMENTAutoSave(this);
            saveADJUSTMENT({ silent: true, changedOnly: true });
        }
    });

    $('#btnSaveADJUSTMENTTab').off('click').on('click', function () {
        saveADJUSTMENT({
            silent: false,
            changedOnly: false
        });
    });

    $('#ADJUSTMENTSearchBarang').off('keyup').on('keyup', function () {
        const q = (this.value || '').toLowerCase();

        $('#dscTableADJUSTMENT tbody tr.ADJUSTMENT-row').each(function () {
            const name = ($(this).find('td').eq(1).text() || '').toLowerCase();
            $(this).toggle(name.includes(q));
        });
    });

    $('button[data-bs-target="#tab-ADJUSTMENT"]').off('shown.bs.tab.ADJUSTMENT').on('shown.bs.tab.ADJUSTMENT', function () {
        initADJUSTMENTTabRecalc();
    });

    $(function () {
        if ($('#tab-ADJUSTMENT').hasClass('show') || $('#tab-ADJUSTMENT').hasClass('active')) {
            initADJUSTMENTTabRecalc();
        }
    });

    function resetADJUSTMENTImp(msg) {
        $('#ADJUSTMENTImpTable tbody').html(`<tr><td colspan="8" class="text-center text-muted py-4">${msg}</td></tr>`);
        $('#btnADJUSTMENTApply').prop('disabled', true);
        $('#ADJUSTMENT_imp_meta').text(msg);
        $('#ADJUSTMENTImpErrorsWrap').hide();
        $('#ADJUSTMENTImpWarnWrap').hide();
        $('#ADJUSTMENTImpErrors').html('');
        $('#ADJUSTMENTImpWarn').html('');
        ADJUSTMENT_IMP_CACHE = { ok: false, items: [], errors: [], warnings: [], meta: null };
    }

    resetADJUSTMENTImp('Preview akan tampil di sini.');

    $('#btnADJUSTMENTPreview').off('click').on('click', async function () {
        try {
            const outletId = Number(`{{ is_numeric($outletId ?? null) ? $outletId : ($selectedOutletIds[0] ?? 0) }}`);
            const tanggal = `{{ $today }}`;
            const shift = Number($('#ADJUSTMENT_imp_shift').val() || 1);
            const fileEl = document.getElementById('ADJUSTMENT_imp_file');
            const file = fileEl.files && fileEl.files[0];

            if (!outletId) return Swal.fire({ icon: 'warning', title: 'Pilih outlet dulu' });
            if (!file) return Swal.fire({ icon: 'warning', title: 'Pilih file dulu' });

            const fd = new FormData();
            fd.append('outlet_id', outletId);
            fd.append('tanggal', tanggal);
            fd.append('shift', shift);
            fd.append('file', file);

            Swal.fire({
                title: 'Preview ADJUSTMENT...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch(URL_DSC_ADJUSTMENT_IMPORT_PREVIEW, {
                method: 'POST',
                headers: { ...apiHeaders() },
                body: fd
            });

            const json = await res.json().catch(() => ({}));

            if (!res.ok || !json.ok) {
                throw new Error(json.message || 'Gagal preview ADJUSTMENT');
            }

            Swal.close();

            const data = json.data || {};
            ADJUSTMENT_IMP_CACHE = {
                ok: true,
                meta: data.meta || null,
                items: data.items || [],
                errors: data.errors || [],
                warnings: data.warnings || []
            };

            $('#ADJUSTMENT_imp_meta').text(
                `Preview OK • items=${ADJUSTMENT_IMP_CACHE.items.length} • errors=${ADJUSTMENT_IMP_CACHE.errors.length} • warnings=${ADJUSTMENT_IMP_CACHE.warnings.length}`
            );

            $('#btnADJUSTMENTApply').prop('disabled', ADJUSTMENT_IMP_CACHE.items.length === 0);

            const html = ADJUSTMENT_IMP_CACHE.items.map((it, i) => `
                <tr>
                    <td class="center sticky-1">${i + 1}</td>
                    <td class="sticky-2 item">${it.nama_bahan}</td>
                    <td class="center"><span class="badge text-bg-secondary">-</span></td>
                    <td class="center">${it.satuan}</td>
                    <td class="num ${Number(it.ADJUSTMENT_qty || 0) < 0 ? 'cell-negative' : ''}">${fmt(it.ADJUSTMENT_qty)}</td>
                    <td class="num ${Number(it.waste_product || 0) < 0 ? 'cell-negative' : ''}">${fmt(it.waste_product || 0)}</td>
                    <td class="num ${Number(it.waste_bahan || 0) < 0 ? 'cell-negative' : ''}">${fmt(it.waste_bahan || 0)}</td>
                    <td class="num ${Number(it.waste_tepung || 0) < 0 ? 'cell-negative' : ''}">${fmt(it.waste_tepung || 0)}</td>
                </tr>
            `).join('');

            $('#ADJUSTMENTImpTable tbody').html(html || `<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data.</td></tr>`);

            if (ADJUSTMENT_IMP_CACHE.errors.length) {
                $('#ADJUSTMENTImpErrorsWrap').show();
                $('#ADJUSTMENTImpErrors').html(ADJUSTMENT_IMP_CACHE.errors.map(e =>
                    `<li>Row ${e.row}: <b>${e.nama}</b> — ${e.error}</li>`).join(''));
            } else {
                $('#ADJUSTMENTImpErrorsWrap').hide();
            }

            if (ADJUSTMENT_IMP_CACHE.warnings.length) {
                $('#ADJUSTMENTImpWarnWrap').show();
                $('#ADJUSTMENTImpWarn').html(ADJUSTMENT_IMP_CACHE.warnings.map(w =>
                    `<li>Row ${w.row}: <b>${w.nama}</b> — ${w.warning}</li>`).join(''));
            } else {
                $('#ADJUSTMENTImpWarnWrap').hide();
            }

        } catch (err) {
            Swal.close();
            resetADJUSTMENTImp('Preview gagal.');
            Swal.fire({ icon: 'error', title: 'Gagal', text: err.message });
        }
    });

    $('#btnADJUSTMENTApply').off('click').on('click', async function () {
        try {
            const outletId = Number(`{{ is_numeric($outletId ?? null) ? $outletId : ($selectedOutletIds[0] ?? 0) }}`);
            const tanggal = `{{ $today }}`;
            const shift = Number($('#ADJUSTMENT_imp_shift').val() || 1);
            const pic = ($('#ADJUSTMENT_imp_pic').val() || '').trim();

            if (!outletId) return Swal.fire({ icon: 'warning', title: 'Pilih outlet dulu' });
            if (!pic) return Swal.fire({ icon: 'warning', title: 'PIC wajib diisi' });
            if (!ADJUSTMENT_IMP_CACHE.ok || !ADJUSTMENT_IMP_CACHE.items.length) {
                return Swal.fire({ icon: 'warning', title: 'Lakukan preview dulu' });
            }

            const payload = {
                outlet_id: outletId,
                tanggal: tanggal,
                nama_petugas: pic,
                note: 'Import ADJUSTMENT',
                items: ADJUSTMENT_IMP_CACHE.items.map(x => ({
                    bahan_id: x.bahan_id || x.id,
                    shift: shift,
                    ADJUSTMENT_qty: toNum(x.ADJUSTMENT_qty),
                    waste_product: toNum(x.waste_product || 0),
                    waste_bahan: toNum(x.waste_bahan || 0),
                    waste_tepung: toNum(x.waste_tepung || 0)
                }))
            };

            Swal.fire({
                title: 'Apply ADJUSTMENT...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetch(URL_DSC_ADJUSTMENT_APPLY, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...apiHeaders()
                },
                body: JSON.stringify(payload)
            });

            const json = await res.json().catch(() => ({}));

            if (!res.ok || !json.ok) {
                throw new Error(json.message || 'Gagal apply ADJUSTMENT');
            }

            Swal.close();

            await Swal.fire({
                icon: 'success',
                title: 'Applied',
                timer: 1000,
                showConfirmButton: false
            });

            location.reload();

        } catch (err) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: err.message
            });
        }
    });

})();
</script>


<script>
    if (typeof Swal === 'undefined') {
        window.Swal = {
            fire: (o) => alert(o.title || o.text || 'Info'),
            showLoading: () => {},
            close: () => {}
        };
    }

    $(document).ready(function() {
        if ($.fn.select2) {
            const cleanOutletText = (txt) => (txt || '').toString()
                    .replace(/\s*\[ID:\s*[^\]]*\]\s*/gi, '')
                    .replace(/\s*\(ID:\s*[^\)]*\)\s*/gi, '')
                    .replace(/^Outlet ID:\s*/i, '')
                    .replace(/\s+/g, ' ')
                    .trim();

            $('#outlet_id').select2({
                width: '100%',
                placeholder: 'Cari outlet...',
                allowClear: true,
                templateResult: item => cleanOutletText(item.text),
                templateSelection: item => cleanOutletText(item.text),
                ajax: {
                    url: `{{ route('outlets') }}`,
                    dataType: 'json',
                    delay: 250,
                    data: params => ({ q: params.term || '' }),
                    processResults: data => ({
                        results: (data.results || []).map(item => ({
                            ...item,
                            text: cleanOutletText(item.text)
                        }))
                    }),
                    cache: true
                }
            });
        }

        const $form = $('#dscFilterForm');
        $form.on('submit', function(e) {
            const outlet = ($form.find('[name="outlet_id"]').val() || '').toString().trim();
            if (!outlet || outlet === 'all') {
                e.preventDefault();
                return Swal.fire({ icon: 'warning', title: 'Outlet wajib dipilih', text: 'Data tidak akan ditampilkan sebelum filter outlet diterapkan.' });
            }
        });

        $('#searchBarang').on('keyup', function() {
            const q = (this.value || '').toLowerCase();

            $('#dscTableRekap tbody tr').each(function() {
                const name = ($(this).find('td').eq(1).text() || '').toLowerCase();
                $(this).toggle(name.includes(q));
            });

            $('#dscTableShift tbody tr').each(function() {
                const name = ($(this).find('td').eq(1).text() || '').toLowerCase();
                $(this).toggle(name.includes(q));
            });

            if ($('#dscTableADJUSTMENT').length) {
                $('#dscTableADJUSTMENT tbody tr').each(function() {
                    const name = ($(this).find('td').eq(1).text() || '').toLowerCase();
                    $(this).toggle(name.includes(q));
                });
            }

            if ($('#dscTableOmset').length) {
                $('#dscTableOmset tbody tr').each(function() {
                    const label = ($(this).find('td').eq(0).text() || '').toLowerCase();
                    $(this).toggle(label.includes(q));
                });
            }
        });
    });


    // ADJUSTMENT autosave/import handlers are defined once in the script above.
    // This lower script only initializes Select2/search to avoid duplicate global const/event binding.
</script>

@endpush

@include('Temp.Investor.footer')
{{-- resources/views/Investor/Laporan/laporanDailyStockControl.blade.php --}}
@section('title', 'Laporan Daily Stock Control')
@section('breadcrumb', 'Laporan / Daily Stock Control')

@include('Temp.Investor.header')

@php
    use Carbon\Carbon;

    $startDate = $startDate ?? ($start ?? now()->startOfMonth())->toDateString();
    $endDate = $endDate ?? ($end ?? now()->endOfMonth())->toDateString();
    $selectedOutlet = $selectedOutlet ?? request('outlet_id', '');
@endphp

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    :root{
        --moka-bg:#f5f7fb;
        --moka-card:#ffffff;
        --moka-ink:#111827;
        --moka-muted:#64748b;
        --moka-border:#dbe3ef;
        --moka-border-dark:#b9c7d8;
        --moka-blue:#2457a7;
        --moka-blue-2:#d9e8ff;
        --moka-blue-3:#edf5ff;
        --moka-yellow:#ffd966;
        --moka-yellow-2:#fff3c4;
        --moka-pink:#e8bed4;
        --moka-green:#078b55;
        --moka-red:#dc2626;
        --moka-shadow:0 10px 30px rgba(15,23,42,.07);
        --moka-radius:16px;
        --left-no:56px;
        --left-outlet:280px;
        --left-area:78px;
        --left-kota:104px;
        --left-status:88px;
    }

    body{ background:var(--moka-bg)!important; }

    .moka-page{
        padding:14px;
        color:var(--moka-ink);
        min-height:100vh;
        font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
    }
    .moka-wrap{ max-width:100%; margin:0 auto; display:flex; flex-direction:column; gap:12px; }

    .moka-hero{
        background:linear-gradient(135deg,#ffffff 0%,#f7fbff 55%,#eef6ff 100%);
        border:1px solid var(--moka-border);
        border-radius:var(--moka-radius);
        box-shadow:var(--moka-shadow);
        padding:15px 16px;
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
    }
    .moka-kicker{
        color:var(--moka-muted);
        font-size:11px;
        font-weight:950;
        text-transform:uppercase;
        letter-spacing:.13em;
        margin-bottom:4px;
    }
    .moka-title{
        display:flex;
        align-items:center;
        gap:10px;
        margin:0;
        color:#07111f;
        font-size:22px;
        font-weight:1000;
        letter-spacing:-.04em;
        line-height:1.08;
    }
    .moka-title-icon{
        width:38px;
        height:38px;
        border-radius:12px;
        background:#eaf3ff;
        color:var(--moka-blue);
        display:grid;
        place-items:center;
        border:1px solid #bfd8ff;
        flex:0 0 auto;
    }
    .moka-subtitle{
        color:var(--moka-muted);
        font-size:12px;
        font-weight:750;
        margin-top:7px;
        max-width:780px;
    }
    .moka-pills{ display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end; align-items:center; }
    .moka-pill{
        display:inline-flex;
        align-items:center;
        gap:7px;
        background:#fff;
        border:1px solid var(--moka-border);
        border-radius:999px;
        padding:7px 10px;
        color:#334155;
        font-size:11px;
        font-weight:950;
        white-space:nowrap;
        box-shadow:0 1px 2px rgba(15,23,42,.04);
    }
    .moka-dot{ width:8px; height:8px; border-radius:99px; background:var(--moka-blue); }

    .moka-filter-card,
    .moka-report-card,
    .moka-stat{
        background:var(--moka-card);
        border:1px solid var(--moka-border);
        border-radius:var(--moka-radius);
        box-shadow:var(--moka-shadow);
    }

    .moka-filter-card{ overflow:visible; }
    .moka-filter-head{
        padding:12px 14px;
        border-bottom:1px solid var(--moka-border);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
        background:#fff;
        border-radius:var(--moka-radius) var(--moka-radius) 0 0;
    }
    .moka-filter-title{
        margin:0;
        font-size:13px;
        font-weight:1000;
        text-transform:uppercase;
        letter-spacing:.08em;
        color:#1e293b;
    }
    .moka-filter-desc{ margin-top:2px; color:var(--moka-muted); font-size:12px; font-weight:750; }
    .moka-filter-body{ padding:13px; background:#fbfdff; border-radius:0 0 var(--moka-radius) var(--moka-radius); }
    .moka-grid{ display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:10px; align-items:end; }
    .moka-field{ display:flex; flex-direction:column; gap:6px; min-width:0; }
    .moka-field label{
        margin:0;
        color:#334155;
        font-size:10px;
        font-weight:1000;
        text-transform:uppercase;
        letter-spacing:.08em;
        display:flex;
        align-items:center;
        gap:6px;
    }
    .moka-input,
    .moka-select{
        width:100%;
        height:40px;
        border:1px solid var(--moka-border-dark);
        border-radius:10px;
        background:#fff;
        color:#0f172a;
        font-size:13px;
        font-weight:850;
        outline:none;
        padding:0 11px;
        box-shadow:none;
    }
    .moka-input:focus,
    .moka-select:focus{
        border-color:var(--moka-blue);
        box-shadow:0 0 0 3px rgba(36,87,167,.16);
    }
    .moka-help{ color:var(--moka-muted); font-size:11px; font-weight:750; margin-top:5px; }

    .select2-container{ width:100%!important; max-width:100%!important; }
    .select2-container--default .select2-selection--single{
        height:40px!important;
        border:1px solid var(--moka-border-dark)!important;
        border-radius:10px!important;
        background:#fff!important;
        display:flex!important;
        align-items:center!important;
        outline:none!important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single{
        border-color:var(--moka-blue)!important;
        box-shadow:0 0 0 3px rgba(36,87,167,.16)!important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered{
        line-height:40px!important;
        height:40px!important;
        padding-left:11px!important;
        padding-right:32px!important;
        color:#0f172a!important;
        font-size:13px!important;
        font-weight:850!important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder{ color:#94a3b8!important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow{ height:40px!important; right:8px!important; }
    .select2-dropdown{ border-color:var(--moka-border-dark)!important; border-radius:12px!important; overflow:hidden!important; box-shadow:0 18px 40px rgba(15,23,42,.16)!important; z-index:99999!important; }
    .select2-container--open{ z-index:99999!important; }
    .select2-search--dropdown{ padding:9px!important; background:#f8fafc!important; }
    .select2-search--dropdown .select2-search__field{ height:36px!important; border-radius:9px!important; border:1px solid var(--moka-border-dark)!important; outline:none!important; padding:7px 10px!important; font-size:13px!important; font-weight:750!important; }
    .select2-results__option{ font-size:13px!important; font-weight:750!important; padding:9px 12px!important; }
    .select2-results__option--highlighted{ background:var(--moka-blue)!important; }

    .moka-btn{
        height:40px;
        border-radius:10px;
        border:1px solid transparent;
        padding:0 13px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:7px;
        font-size:12px;
        font-weight:1000;
        text-transform:uppercase;
        letter-spacing:.02em;
        text-decoration:none;
        cursor:pointer;
        user-select:none;
        white-space:nowrap;
        transition:.14s ease;
    }
    .moka-btn:disabled{ opacity:.45; cursor:not-allowed; }
    .moka-btn-primary{ background:var(--moka-blue); color:#fff; box-shadow:0 8px 18px rgba(36,87,167,.18); }
    .moka-btn-primary:hover{ background:#163f82; color:#fff; }
    .moka-btn-light{ background:#fff; color:#334155; border-color:var(--moka-border-dark); }
    .moka-btn-light:hover{ background:#f1f5f9; color:#0f172a; }
    .moka-btn-green{ background:#e9fbf2; color:#047857; border-color:#bbf7d0; }
    .moka-btn-green:hover{ background:#d1fae5; color:#065f46; }

    .moka-stats{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
    .moka-stat{ padding:12px 13px; display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .moka-stat-label{ font-size:10px; color:var(--moka-muted); font-weight:1000; text-transform:uppercase; letter-spacing:.08em; }
    .moka-stat-value{ margin-top:5px; font-size:17px; line-height:1; font-weight:1000; color:#0f172a; }
    .moka-stat-icon{ width:36px; height:36px; border-radius:11px; border:1px solid var(--moka-border); background:#f8fafc; display:grid; place-items:center; color:var(--moka-blue); }

    .moka-report-card{ overflow:hidden; }
    .moka-report-top{
        padding:10px 12px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        flex-wrap:wrap;
        background:#fff;
        border-bottom:1px solid var(--moka-border);
    }
    .moka-report-title{ margin:0; font-size:13px; font-weight:1000; color:#1e293b; text-transform:uppercase; letter-spacing:.08em; }
    .moka-report-note{ color:var(--moka-muted); font-size:11px; font-weight:750; margin-top:2px; }
    .moka-actions,.moka-pagination{ display:flex; gap:7px; flex-wrap:wrap; align-items:center; }

    .moka-table-scroll{
        height:74vh;
        max-height:760px;
        min-height:380px;
        overflow:auto;
        background:#fff;
        position:relative;
        overscroll-behavior:contain;
    }
    .moka-table-scroll::-webkit-scrollbar{ height:11px; width:11px; }
    .moka-table-scroll::-webkit-scrollbar-thumb{ background:#b7c5d8; border-radius:99px; border:2px solid #eef2f7; }
    .moka-table-scroll::-webkit-scrollbar-track{ background:#eef2f7; }

    .moka-table{
        border-collapse:separate;
        border-spacing:0;
        min-width:2320px;
        width:max-content;
        font-size:12px;
        color:#111827;
    }
    .moka-table th,
    .moka-table td{
        border-right:1px solid var(--moka-border);
        border-bottom:1px solid var(--moka-border);
        padding:8px 10px;
        white-space:nowrap;
        vertical-align:middle;
        background:#fff;
    }
    .moka-table thead th{
        position:sticky;
        z-index:30;
        text-align:center;
        font-size:10px;
        font-weight:1000;
        text-transform:uppercase;
        letter-spacing:.05em;
        color:#233044;
        background:#eaf1fb;
        height:36px;
    }
    .moka-table thead tr:first-child th{ top:0; }
    .moka-table thead tr:nth-child(2) th{ top:36px; background:#f4f8ff; }
    .moka-table thead tr:nth-child(3) th{ top:72px; background:#fff7db; color:#6f4700; }

    .moka-date-head{ background:#cfe1f9!important; color:#102f62!important; border-bottom:1px solid #9fbfe8!important; }
    .moka-sub-head{ background:#fff0b9!important; color:#6b4500!important; }
    .moka-left-head{ background:#e8edf4!important; color:#233044!important; }

    .moka-sticky-no,
    .moka-sticky-outlet,
    .moka-sticky-area,
    .moka-sticky-kota,
    .moka-sticky-status{ position:sticky!important; z-index:45!important; }
    .moka-sticky-no{ left:0; min-width:var(--left-no); width:var(--left-no); text-align:center; }
    .moka-sticky-outlet{ left:var(--left-no); min-width:var(--left-outlet); width:var(--left-outlet); text-align:left!important; box-shadow:8px 0 14px rgba(15,23,42,.05); }
    .moka-sticky-area{ left:calc(var(--left-no) + var(--left-outlet)); min-width:var(--left-area); width:var(--left-area); text-align:center; }
    .moka-sticky-kota{ left:calc(var(--left-no) + var(--left-outlet) + var(--left-area)); min-width:var(--left-kota); width:var(--left-kota); text-align:center; }
    .moka-sticky-status{ left:calc(var(--left-no) + var(--left-outlet) + var(--left-area) + var(--left-kota)); min-width:var(--left-status); width:var(--left-status); text-align:center; }
    thead .moka-sticky-no,
    thead .moka-sticky-outlet,
    thead .moka-sticky-area,
    thead .moka-sticky-kota,
    thead .moka-sticky-status{ z-index:60!important; background:#e8edf4!important; }
    tbody .moka-sticky-no,
    tbody .moka-sticky-outlet,
    tbody .moka-sticky-area,
    tbody .moka-sticky-kota,
    tbody .moka-sticky-status{ background:#fff!important; }

    .moka-outlet-name{ display:flex; align-items:center; gap:7px; font-weight:1000; color:#0f172a; white-space:normal; line-height:1.22; }
    .moka-outlet-meta{ margin-top:4px; color:var(--moka-muted); font-size:10px; font-weight:900; white-space:normal; }
    .moka-status-badge{ display:inline-flex; align-items:center; justify-content:center; min-width:52px; padding:4px 7px; border-radius:999px; background:#eef2ff; color:#1e3a8a; font-size:10px; font-weight:1000; text-transform:uppercase; }

    .num{ text-align:right; font-variant-numeric:tabular-nums; font-weight:900; }
    .center{ text-align:center; font-weight:900; }
    .muted{ color:var(--moka-muted); }
    .soft{ background:#f8fafc!important; }
    .money{ min-width:92px; }
    .stock{ min-width:58px; text-align:center; }
    .subtotal-cell{ background:var(--moka-yellow-2)!important; font-weight:1000!important; }
    .minus{ color:var(--moka-red)!important; font-weight:1000!important; }
    .plus{ color:var(--moka-green)!important; font-weight:1000!important; }
    tbody tr:hover td{ background:#eef6ff!important; }
    tbody tr:hover .moka-sticky-no,
    tbody tr:hover .moka-sticky-outlet,
    tbody tr:hover .moka-sticky-area,
    tbody tr:hover .moka-sticky-kota,
    tbody tr:hover .moka-sticky-status{ background:#eef6ff!important; }

    .moka-grand td{ background:var(--moka-blue)!important; color:#fff!important; font-weight:1000!important; }
    .moka-grand .moka-sticky-no,
    .moka-grand .moka-sticky-outlet,
    .moka-grand .moka-sticky-area,
    .moka-grand .moka-sticky-kota,
    .moka-grand .moka-sticky-status{ background:var(--moka-blue)!important; color:#fff!important; }
    .moka-grand .minus,.moka-grand .plus{ color:#fff!important; }

    .moka-loading{
        position:absolute;
        inset:0;
        display:grid;
        place-items:center;
        background:rgba(255,255,255,.78);
        backdrop-filter:blur(2px);
        z-index:100;
    }
    .moka-loading.hide{ display:none; }
    .moka-loader-box{ text-align:center; color:var(--moka-muted); font-size:13px; font-weight:1000; padding:24px; }
    .moka-spinner{ width:28px; height:28px; border-radius:50%; border:3px solid #dbeafe; border-top-color:var(--moka-blue); margin:0 auto 10px; animation:mokaSpin .75s linear infinite; }
    @keyframes mokaSpin{ to{ transform:rotate(360deg); } }
    .hide{ display:none!important; }

    .moka-empty{ padding:32px; text-align:center; color:var(--moka-muted); font-size:13px; font-weight:1000; }

    @media(max-width:1200px){
        :root{ --left-outlet:240px; --left-area:70px; --left-kota:92px; --left-status:78px; }
        .moka-stats{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media(max-width:991px){
        .moka-page{ padding:10px; }
        .moka-title{ font-size:19px; }
        .moka-grid{ grid-template-columns:1fr; }
        .moka-field{ grid-column:span 1!important; }
        .moka-pills{ justify-content:flex-start; }
        .moka-report-top{ align-items:stretch; }
        .moka-actions,.moka-pagination{ width:100%; display:grid; grid-template-columns:1fr 1fr; }
        .moka-btn{ width:100%; }
        .moka-table-scroll{ height:68vh; }
        .moka-table{ min-width:2100px; }
    }
    @media(max-width:640px){
        :root{ --left-no:46px; --left-outlet:190px; --left-area:0px; --left-kota:0px; --left-status:0px; }
        .moka-stats{ grid-template-columns:1fr; }
        .moka-sticky-area,.moka-sticky-kota,.moka-sticky-status{ position:static!important; min-width:80px; width:auto; }
        thead .moka-sticky-area, thead .moka-sticky-kota, thead .moka-sticky-status{ z-index:30!important; }
        .moka-actions,.moka-pagination{ grid-template-columns:1fr; }
        .moka-table th,.moka-table td{ padding:7px 8px; font-size:11px; }
    }
</style>

<div class="moka-page">
    <div class="moka-wrap">
        <div class="moka-hero">
            <div>
                <div class="moka-kicker">Laporan / Daily Stock Control</div>
                <h1 class="moka-title">
                    <span class="moka-title-icon"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                    Report MOKA Style - Daily Stock Control
                </h1>
                <div class="moka-subtitle">
                    Outlet tetap di kiri, tanggal tetap di atas. Data diload via AJAX pagination supaya ringan untuk banyak outlet dan range panjang.
                </div>
            </div>
            <div class="moka-pills">
                <span class="moka-pill"><span class="moka-dot"></span><span id="periodText">{{ $startDate }} s/d {{ $endDate }}</span></span>
                <span class="moka-pill" id="rowsText">Total Outlet: -</span>
                <span class="moka-pill" id="speedText">AJAX Ready</span>
            </div>
        </div>

        <div class="moka-stats">
            <div class="moka-stat">
                <div>
                    <div class="moka-stat-label">Total Outlet</div>
                    <div class="moka-stat-value" id="statOutlet">-</div>
                </div>
                <div class="moka-stat-icon"><i class="bi bi-shop"></i></div>
            </div>
            <div class="moka-stat">
                <div>
                    <div class="moka-stat-label">Total Transaction</div>
                    <div class="moka-stat-value" id="statTrx">Rp 0</div>
                </div>
                <div class="moka-stat-icon"><i class="bi bi-receipt"></i></div>
            </div>
            <div class="moka-stat">
                <div>
                    <div class="moka-stat-label">Total Disetor</div>
                    <div class="moka-stat-value" id="statDisetor">Rp 0</div>
                </div>
                <div class="moka-stat-icon"><i class="bi bi-cash-coin"></i></div>
            </div>
            <div class="moka-stat">
                <div>
                    <div class="moka-stat-label">Total Selisih</div>
                    <div class="moka-stat-value" id="statSelisih">Rp 0</div>
                </div>
                <div class="moka-stat-icon"><i class="bi bi-activity"></i></div>
            </div>
        </div>

        <div class="moka-filter-card">
            <div class="moka-filter-head">
                <div>
                    <h2 class="moka-filter-title">Filter Laporan</h2>
                    <div class="moka-filter-desc">Pilih outlet, tanggal, pencarian, dan jumlah baris per halaman.</div>
                </div>
            </div>
            <div class="moka-filter-body">
                <form id="dscFilterForm">
                    <div class="moka-grid">
                        <div class="moka-field" style="grid-column:span 3">
                            <label><i class="bi bi-shop"></i> Outlet</label>
                            <select class="moka-select js-select2-outlet" id="outlet_id" name="outlet_id" data-placeholder="Semua Outlet">
                                <option value="">Semua Outlet</option>
                                @foreach ($outlets ?? [] as $o)
                                    <option value="{{ $o->id }}" {{ (string) $selectedOutlet === (string) $o->id ? 'selected' : '' }}>
                                        {{ $o->nama_outlet }} — {{ $o->kota ?? '-' }} ({{ $o->status ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="moka-help">Bisa ketik nama outlet / kota.</div>
                        </div>

                        <div class="moka-field" style="grid-column:span 2">
                            <label><i class="bi bi-calendar-event"></i> Start Date</label>
                            <input class="moka-input" type="date" id="start_date" name="start_date" value="{{ $startDate }}">
                        </div>

                        <div class="moka-field" style="grid-column:span 2">
                            <label><i class="bi bi-calendar-check"></i> End Date</label>
                            <input class="moka-input" type="date" id="end_date" name="end_date" value="{{ $endDate }}">
                        </div>

                        <div class="moka-field" style="grid-column:span 2">
                            <label><i class="bi bi-search"></i> Cari Outlet</label>
                            <input class="moka-input" type="text" id="search" name="search" placeholder="Nama / area / kota">
                        </div>

                        <div class="moka-field" style="grid-column:span 1">
                            <label><i class="bi bi-list-ol"></i> Per Page</label>
                            <select class="moka-select" id="per_page" name="per_page">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <div class="moka-field" style="grid-column:span 2">
                            <label>Aksi</label>
                            <div style="display:flex;gap:8px;min-width:0">
                                <button type="submit" class="moka-btn moka-btn-primary" style="flex:1"><i class="bi bi-funnel"></i> Filter</button>
                                <a href="{{ route('laporan.laporanDSC') }}" class="moka-btn moka-btn-light" style="flex:1"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                            </div>
                        </div>
                    </div>
                    <div class="moka-help">Export mengambil semua data sesuai filter, bukan hanya halaman aktif.</div>
                </form>
            </div>
        </div>

        <div class="moka-report-card">
            <div class="moka-report-top">
                <div>
                    <h2 class="moka-report-title">Report Omset & Setoran</h2>
                    <div class="moka-report-note">Format horizontal seperti MOKA: kolom outlet freeze, tanggal melebar ke kanan.</div>
                </div>

                <div class="moka-pagination">
                    <button type="button" class="moka-btn moka-btn-light" id="prevPage"><i class="bi bi-chevron-left"></i> Prev</button>
                    <span class="moka-pill" id="pageInfo">Page 1</span>
                    <button type="button" class="moka-btn moka-btn-light" id="nextPage">Next <i class="bi bi-chevron-right"></i></button>
                </div>

                <div class="moka-actions">
                    <button type="button" class="moka-btn moka-btn-light" id="reloadBtn"><i class="bi bi-arrow-clockwise"></i> Reload</button>
                    <button type="button" class="moka-btn moka-btn-light" id="fitBtn"><i class="bi bi-arrows-collapse"></i> Compact</button>
                    <a href="#" class="moka-btn moka-btn-green" id="exportBtn"><i class="bi bi-file-earmark-excel"></i> Export</a>
                </div>
            </div>

            <div class="moka-table-scroll" id="tableWrap">
                <div class="moka-loading" id="loadingBox">
                    <div class="moka-loader-box"><div class="moka-spinner"></div>Loading data...</div>
                </div>
                <table class="moka-table" id="dscTable">
                    <thead id="dscThead"></thead>
                    <tbody id="dscTbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function(){
    const routes = {
        data: @json(route('laporan.laporanDSC.data')),
        export: @json(route('laporan.laporanDSC.export'))
    };

    const state = {
        page: 1,
        totalPages: 1,
        perPage: 25,
        rows: [],
        dates: [],
        grand: null,
        loading: false,
        compact: false
    };

    const el = (id) => document.getElementById(id);
    const rupiah = (value) => Number(value || 0).toLocaleString('id-ID', {maximumFractionDigits:0});
    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'",'&#039;');

    function shortDate(dateString){
        const d = new Date(dateString + 'T00:00:00');
        if (Number.isNaN(d.getTime())) return escapeHtml(dateString);
        return String(d.getDate()).padStart(2,'0');
    }

    function longDate(dateString){
        const d = new Date(dateString + 'T00:00:00');
        if (Number.isNaN(d.getTime())) return escapeHtml(dateString);
        return d.toLocaleDateString('id-ID', {day:'2-digit', month:'short'});
    }

    function buildParams(){
        const q = new URLSearchParams();
        q.set('start_date', el('start_date').value || '');
        q.set('end_date', el('end_date').value || '');
        q.set('outlet_id', el('outlet_id').value || '');
        q.set('search', el('search').value || '');
        q.set('per_page', el('per_page').value || '25');
        q.set('page', state.page);
        return q;
    }

    function setLoading(value, message){
        state.loading = value;
        el('loadingBox').classList.toggle('hide', !value);
        if (message) {
            el('loadingBox').innerHTML = '<div class="moka-loader-box"><div class="moka-spinner"></div>' + escapeHtml(message) + '</div>';
        }
    }

    async function loadData(){
        if (state.loading) return;
        const t0 = performance.now();
        setLoading(true, 'Loading data...');

        try{
            const response = await fetch(routes.data + '?' + buildParams().toString(), {
                headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
            });

            let json = null;
            try { json = await response.json(); } catch(e) {}

            if (!response.ok) {
                throw new Error(json?.message || json?.error || ('Gagal mengambil data. HTTP ' + response.status));
            }

            state.rows = json.rows || [];
            state.dates = json.dates || [];
            state.grand = json.grand || null;
            state.totalPages = json.pagination?.total_pages || 1;
            state.page = json.pagination?.page || 1;
            state.perPage = json.pagination?.per_page || Number(el('per_page').value || 25);

            renderTable();
            renderInfo(json.pagination || {}, t0);
            setLoading(false);
        }catch(err){
            el('loadingBox').innerHTML = '<div class="moka-loader-box" style="color:#dc2626"><i class="bi bi-exclamation-triangle me-1"></i>' + escapeHtml(err.message) + '</div>';
        }
    }

    function renderInfo(pagination, t0){
        const totalRows = pagination.total_rows || 0;
        el('pageInfo').textContent = 'Page ' + state.page + ' / ' + state.totalPages;
        el('rowsText').textContent = 'Total Outlet: ' + rupiah(totalRows);
        el('periodText').textContent = (el('start_date').value || '-') + ' s/d ' + (el('end_date').value || '-');
        el('prevPage').disabled = state.page <= 1;
        el('nextPage').disabled = state.page >= state.totalPages;
        el('statOutlet').textContent = rupiah(totalRows);

        const sub = state.grand?.sub_total || {};
        el('statTrx').textContent = 'Rp ' + rupiah(sub.total_transaction || 0);
        el('statDisetor').textContent = 'Rp ' + rupiah(sub.total_disetor || 0);
        el('statSelisih').textContent = 'Rp ' + rupiah(sub.selisih || 0);
        el('statSelisih').classList.toggle('minus', Number(sub.selisih || 0) < 0);
        el('statSelisih').classList.toggle('plus', Number(sub.selisih || 0) > 0);

        if (t0) {
            el('speedText').textContent = 'Load ' + Math.max(1, Math.round(performance.now() - t0)) + ' ms';
        }
    }

    function renderTable(){
        renderHeader();
        renderBody();
    }

    function renderHeader(){
        let h1 = '<tr>';
        h1 += '<th rowspan="3" class="moka-left-head moka-sticky-no">No</th>';
        h1 += '<th rowspan="3" class="moka-left-head moka-sticky-outlet">Nama Outlet</th>';
        h1 += '<th rowspan="3" class="moka-left-head moka-sticky-area">Area</th>';
        h1 += '<th rowspan="3" class="moka-left-head moka-sticky-kota">Kota</th>';
        h1 += '<th rowspan="3" class="moka-left-head moka-sticky-status">Status</th>';
        state.dates.forEach(date => {
            h1 += '<th colspan="7" class="moka-date-head">' + shortDate(date) + '</th>';
        });
        h1 += '<th colspan="6" class="moka-sub-head">Sub Total</th>';
        h1 += '</tr>';

        let h2 = '<tr>';
        state.dates.forEach(date => {
            h2 += '<th colspan="7" class="moka-date-head">' + longDate(date) + '</th>';
        });
        h2 += '<th colspan="6" class="moka-sub-head">Range Aktif</th>';
        h2 += '</tr>';

        let h3 = '<tr>';
        state.dates.forEach(() => {
            h3 += '<th>TRX</th><th>FISIK</th><th>MINUS</th><th>HARUS SETOR</th><th>DISETOR</th><th>SELISIH</th><th>STOCK</th>';
        });
        h3 += '<th>TRX</th><th>FISIK</th><th>MINUS</th><th>HARUS SETOR</th><th>DISETOR</th><th>SELISIH</th>';
        h3 += '</tr>';

        el('dscThead').innerHTML = h1 + h2 + h3;
    }

    function renderBody(){
        const colCount = 5 + (state.dates.length * 7) + 6;
        if (!state.rows.length) {
            el('dscTbody').innerHTML = '<tr><td colspan="' + colCount + '" class="moka-empty"><i class="bi bi-info-circle me-1"></i>Data tidak tersedia untuk filter ini.</td></tr>';
            return;
        }

        let html = '';
        state.rows.forEach((row, i) => {
            const no = ((state.page - 1) * state.perPage) + i + 1;
            html += '<tr>';
            html += '<td class="moka-sticky-no center">' + no + '</td>';
            html += '<td class="moka-sticky-outlet">' + outletCell(row) + '</td>';
            html += '<td class="moka-sticky-area center">' + escapeHtml(row.area || '-') + '</td>';
            html += '<td class="moka-sticky-kota center">' + escapeHtml(row.kota || '-') + '</td>';
            html += '<td class="moka-sticky-status center"><span class="moka-status-badge">' + escapeHtml(row.status || '-') + '</span></td>';
            state.dates.forEach(date => html += dayCells(row.hari?.[date] || {}));
            html += subtotalCells(row.sub_total || {});
            html += '</tr>';
        });

        if (state.grand && state.grand.sub_total) {
            html += '<tr class="moka-grand">';
            html += '<td class="moka-sticky-no center">#</td>';
            html += '<td class="moka-sticky-outlet">GRAND TOTAL HALAMAN INI</td>';
            html += '<td class="moka-sticky-area center">-</td>';
            html += '<td class="moka-sticky-kota center">-</td>';
            html += '<td class="moka-sticky-status center">-</td>';
            state.dates.forEach(date => html += dayCells(state.grand.hari?.[date] || {}));
            html += subtotalCells(state.grand.sub_total || {});
            html += '</tr>';
        }

        el('dscTbody').innerHTML = html;
    }

    function outletCell(row){
        const outlet = escapeHtml(row.nama_outlet || '-');
    
        return `
            <div class="moka-outlet-name">
                <i class="bi bi-shop-window"></i>
                <span>${outlet}</span>
            </div>
        `;
    }

    function dayCells(d){
        const selisih = Number(d.selisih || 0);
        const cls = selisih < 0 ? ' minus' : (selisih > 0 ? ' plus' : '');
        return ''
            + '<td class="num money">' + rupiah(d.total_transaction) + '</td>'
            + '<td class="num money soft">' + rupiah(d.uang_fisik) + '</td>'
            + '<td class="num money minus">' + rupiah(d.uang_minus) + '</td>'
            + '<td class="num money">' + rupiah(d.harus_disetor) + '</td>'
            + '<td class="num money">' + rupiah(d.total_disetor) + '</td>'
            + '<td class="num money' + cls + '">' + rupiah(selisih) + '</td>'
            + '<td class="stock soft">' + rupiah(d.stock_item_count) + '</td>';
    }

    function subtotalCells(d){
        const selisih = Number(d.selisih || 0);
        const cls = selisih < 0 ? ' minus' : (selisih > 0 ? ' plus' : '');
        return ''
            + '<td class="num money subtotal-cell">' + rupiah(d.total_transaction) + '</td>'
            + '<td class="num money subtotal-cell">' + rupiah(d.uang_fisik) + '</td>'
            + '<td class="num money subtotal-cell minus">' + rupiah(d.uang_minus) + '</td>'
            + '<td class="num money subtotal-cell">' + rupiah(d.harus_disetor) + '</td>'
            + '<td class="num money subtotal-cell">' + rupiah(d.total_disetor) + '</td>'
            + '<td class="num money subtotal-cell' + cls + '">' + rupiah(selisih) + '</td>';
    }

    function initSelect2(){
        if (window.jQuery && jQuery.fn.select2) {
            const $outlet = jQuery('#outlet_id');
            if ($outlet.data('select2')) $outlet.select2('destroy');
            $outlet.select2({
                width:'100%',
                placeholder:$outlet.data('placeholder') || 'Semua Outlet',
                allowClear:true,
                minimumResultsForSearch:0,
                dropdownParent:jQuery('.moka-filter-body')
            });
            $outlet.on('change', function(){ state.page = 1; loadData(); });
        }
    }

    el('dscFilterForm').addEventListener('submit', function(e){ e.preventDefault(); state.page = 1; loadData(); });

    let searchTimer = null;
    el('search').addEventListener('input', function(){
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { state.page = 1; loadData(); }, 450);
    });
    el('per_page').addEventListener('change', function(){ state.page = 1; loadData(); });
    el('start_date').addEventListener('change', function(){ state.page = 1; });
    el('end_date').addEventListener('change', function(){ state.page = 1; });
    el('prevPage').addEventListener('click', function(){ if (state.page > 1) { state.page--; loadData(); } });
    el('nextPage').addEventListener('click', function(){ if (state.page < state.totalPages) { state.page++; loadData(); } });
    el('reloadBtn').addEventListener('click', loadData);
    el('exportBtn').addEventListener('click', function(e){
        e.preventDefault();
        const q = buildParams();
        q.delete('page');
        q.delete('per_page');
        window.location.href = routes.export + '?' + q.toString();
    });
    el('fitBtn').addEventListener('click', function(){
        state.compact = !state.compact;
        document.documentElement.style.setProperty('--left-outlet', state.compact ? '220px' : '280px');
        document.querySelectorAll('.money').forEach(td => td.style.minWidth = state.compact ? '78px' : '92px');
        this.innerHTML = state.compact ? '<i class="bi bi-arrows-expand"></i> Normal' : '<i class="bi bi-arrows-collapse"></i> Compact';
    });

    initSelect2();
    loadData();
})();
</script>

@include('Temp.Investor.footer')

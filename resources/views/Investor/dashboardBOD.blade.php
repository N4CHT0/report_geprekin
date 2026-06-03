{{-- resources/views/investor/dashboardBOD.blade.php --}}
@include('Temp.internal.header_internal')

@php
    function formatRupiahShort($angka) {
        $angka = (float) $angka;
    
        if ($angka >= 1000000000) {
            return number_format($angka / 1000000000, 2, ',', '.') . ' M';
        } elseif ($angka >= 1000000) {
            return number_format($angka / 1000000, 2, ',', '.') . ' JT';
        } elseif ($angka >= 1000) {
            return number_format($angka / 1000, 2, ',', '.') . ' RB';
        }
    
        return number_format($angka, 0, ',', '.');
    }
@endphp

<style>
    :root{
        --bod-bg:#07111f;
        --bod-bg-2:#0b1830;
        --bod-card:#0c1729;
        --bod-card-2:#0a1324;
        --bod-line:rgba(148,163,184,.14);
        --bod-line-soft:rgba(148,163,184,.08);
        --bod-text:#eaf2ff;
        --bod-muted:#8ea1bd;
        --bod-green:#22c55e;
        --bod-red:#ef4444;
        --bod-blue:#3b82f6;
        --bod-cyan:#06b6d4;
        --bod-amber:#f59e0b;
        --bod-white:#ffffff;
        --bod-shadow:0 18px 40px rgba(0,0,0,.35);
        --bod-radius:22px;
    }

    .bod-dashboard{
        min-height:100vh;
        padding:22px;
        background:
            radial-gradient(circle at top left, rgba(59,130,246,.16), transparent 20%),
            radial-gradient(circle at top right, rgba(6,182,212,.11), transparent 20%),
            linear-gradient(180deg, #07101d 0%, #0a1526 100%);
        color:var(--bod-text);
    }

    .bod-dashboard *{ box-sizing:border-box; }
    .bod-shell{ width:100%; }

    .bod-topbar{
        position:relative;
        overflow:hidden;
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:18px;
        flex-wrap:wrap;
        margin-bottom:18px;
        padding:22px;
        border-radius:26px;
        border:1px solid var(--bod-line);
        background:
            radial-gradient(circle at 0% 0%, rgba(59,130,246,.20), transparent 30%),
            radial-gradient(circle at 100% 0%, rgba(6,182,212,.14), transparent 24%),
            linear-gradient(135deg, rgba(9,18,34,.98), rgba(6,11,21,.98));
        box-shadow:var(--bod-shadow);
    }

    .bod-title{
        margin:0;
        font-size:clamp(30px, 4vw, 46px);
        line-height:1;
        font-weight:900;
        letter-spacing:-.05em;
        color:#f8fbff;
    }

    .bod-subtitle{
        margin-top:10px;
        max-width:860px;
        font-size:14px;
        line-height:1.6;
        color:var(--bod-muted);
    }

    .bod-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        justify-content:flex-end;
    }

    .bod-btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        border:none;
        border-radius:999px;
        padding:10px 15px;
        font-size:12px;
        font-weight:800;
        text-decoration:none;
        cursor:pointer;
        transition:.2s ease;
        white-space:nowrap;
        min-height:42px;
    }

    .bod-btn-outline{
        background:rgba(255,255,255,.04);
        color:#cfe0ff;
        border:1px solid var(--bod-line);
    }

    .bod-filter-card,
    .bod-panel,
    .bod-section,
    .bod-kpi{
        background:linear-gradient(180deg, rgba(12,23,41,.96), rgba(7,13,25,.98));
        border:1px solid var(--bod-line);
        box-shadow:var(--bod-shadow);
    }

    .bod-filter-card{
        border-radius:24px;
        overflow:hidden;
        margin-bottom:18px;
    }

    .bod-filter-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        padding:16px 20px;
        background:linear-gradient(90deg, rgba(13,110,253,.18), rgba(13,110,253,.06));
        border-bottom:1px solid var(--bod-line);
    }

    .bod-filter-title{
        margin:0;
        font-size:16px;
        font-weight:900;
        color:#f6faff;
    }

    .bod-filter-sub{
        font-size:12px;
        color:#bdd0ef;
        margin-top:4px;
    }

    .bod-filter-body{ padding:18px; }

    .bod-filter-grid{
        display:grid;
        grid-template-columns:repeat(6, minmax(0, 1fr));
        gap:14px;
        align-items:end;
    }

    .bod-filter-col.span-2{ grid-column:span 2; }
    .bod-filter-col.full{ grid-column:1 / -1; }

    .bod-form-label{
        display:block;
        margin-bottom:6px;
        font-size:12px;
        font-weight:700;
        color:#adc0db;
    }

    .bod-input,
    .bod-select{
        width:100%;
        height:46px;
        padding:0 14px;
        border-radius:14px;
        border:1px solid rgba(148,163,184,.18);
        background:rgba(255,255,255,.04);
        color:#eef4ff;
        outline:none;
        transition:.2s ease;
    }

    .bod-input::placeholder{ color:#7d92b1; }

    .bod-input:focus,
    .bod-select:focus{
        border-color:rgba(59,130,246,.5);
        box-shadow:0 0 0 3px rgba(59,130,246,.12);
    }

    .bod-select option{ color:#111827; }

    .bod-submit{
        min-width:210px;
        height:46px;
        border:none;
        border-radius:14px;
        background:linear-gradient(180deg, #2563eb, #1d4ed8);
        color:#fff;
        font-size:13px;
        font-weight:800;
        cursor:pointer;
        transition:.2s ease;
        box-shadow:0 10px 20px rgba(37,99,235,.24);
    }

    .bod-submit:hover,
    .bod-btn:hover{ transform:translateY(-1px); }

    .bod-kpi-grid{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:14px;
        margin-bottom:18px;
    }

    .bod-kpi{
        position:relative;
        overflow:hidden;
        min-height:126px;
        border-radius:20px;
        padding:15px 16px;
    }

    .bod-kpi::before{
        content:"";
        position:absolute;
        top:-34px;
        right:-34px;
        width:88px;
        height:88px;
        border-radius:50%;
        background:radial-gradient(circle, rgba(255,255,255,.09), transparent 70%);
        pointer-events:none;
    }

    .bod-kpi.green{
        box-shadow:0 14px 34px rgba(0,0,0,.35), 0 0 0 1px rgba(34,197,94,.12), 0 0 24px rgba(34,197,94,.08);
    }

    .bod-kpi.blue{
        box-shadow:0 14px 34px rgba(0,0,0,.35), 0 0 0 1px rgba(59,130,246,.12), 0 0 24px rgba(59,130,246,.08);
    }

    .bod-kpi.red{
        box-shadow:0 14px 34px rgba(0,0,0,.35), 0 0 0 1px rgba(239,68,68,.12), 0 0 24px rgba(239,68,68,.08);
    }

    .bod-kpi.gray{
        box-shadow:0 14px 34px rgba(0,0,0,.35);
    }

    .bod-kpi.black{
        background:linear-gradient(180deg, rgba(8,11,18,.98), rgba(4,7,14,.99));
        box-shadow:0 14px 34px rgba(0,0,0,.4), 0 0 0 1px rgba(255,255,255,.05);
    }

    .bod-kpi-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:10px;
        margin-bottom:12px;
    }

    .bod-kpi-label{
        font-size:11px;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.08em;
        color:#97abc7;
    }

    .bod-kpi-dot{
        width:10px;
        height:10px;
        border-radius:50%;
        flex:0 0 10px;
    }

    .bod-kpi.green .bod-kpi-dot{ background:#22c55e; box-shadow:0 0 14px rgba(34,197,94,.7); }
    .bod-kpi.blue .bod-kpi-dot{ background:#3b82f6; box-shadow:0 0 14px rgba(59,130,246,.7); }
    .bod-kpi.red .bod-kpi-dot{ background:#ef4444; box-shadow:0 0 14px rgba(239,68,68,.7); }
    .bod-kpi.gray .bod-kpi-dot{ background:#94a3b8; }
    .bod-kpi.black .bod-kpi-dot{ background:#ffffff; }

    .bod-kpi-value{
        margin-bottom:10px;
        color:#ffffff;
        font-size:clamp(22px, 2.2vw, 28px);
        line-height:1.05;
        font-weight:800;
        letter-spacing:-.03em;
        word-break:break-word;
    }

    .bod-kpi-value.small{ font-size:clamp(20px, 2vw, 24px); }

    .bod-kpi-foot{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
        font-size:11px;
        color:#9ab0cb;
    }

    .bod-badge-trend{
        display:inline-flex;
        align-items:center;
        gap:5px;
        padding:5px 9px;
        border-radius:999px;
        font-size:11px;
        font-weight:800;
        border:1px solid rgba(148,163,184,.12);
        background:rgba(255,255,255,.04);
        white-space:nowrap;
    }

    .bod-badge-trend.up{
        color:#9ff0b9;
        background:rgba(34,197,94,.08);
        border-color:rgba(34,197,94,.18);
    }

    .bod-badge-trend.down{
        color:#ff9d9d;
        background:rgba(239,68,68,.08);
        border-color:rgba(239,68,68,.18);
    }

    .bod-badge-trend.flat{
        color:#d7dfeb;
        background:rgba(148,163,184,.08);
    }

    .bod-grid-2{
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:18px;
        margin-bottom:18px;
    }

    .bod-panel,
    .bod-section{
        border-radius:24px;
        overflow:hidden;
    }

    .bod-panel-head,
    .bod-section-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        flex-wrap:wrap;
        padding:16px 18px 12px;
    }

    .bod-panel-title,
    .bod-section-title{
        margin:0;
        font-size:18px;
        font-weight:800;
        color:#f2f7ff;
    }

    .bod-panel-sub,
    .bod-section-sub{
        margin-top:4px;
        font-size:12px;
        color:var(--bod-muted);
    }

    .bod-panel-tag,
    .bod-section-chip{
        padding:7px 11px;
        border-radius:999px;
        font-size:11px;
        font-weight:800;
        white-space:nowrap;
    }

    .bod-panel-tag{
        background:rgba(59,130,246,.10);
        color:#9bc1ff;
        border:1px solid rgba(59,130,246,.18);
    }

    .bod-section-chip{
        color:#92efab;
        border:1px solid rgba(34,197,94,.18);
        background:rgba(34,197,94,.08);
    }

    .bod-table-wrap{
        padding:0 14px 14px;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
    }

    .bod-table{
        width:100%;
        min-width:520px;
        border-collapse:separate;
        border-spacing:0 8px;
    }

    .bod-table.outlet{ min-width:860px; }

    .bod-table thead th{
        padding:0 12px 6px;
        text-align:left;
        font-size:12px;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.06em;
        color:#8ea1bd;
        white-space:nowrap;
    }

    .bod-table tbody td{
        padding:14px 12px;
        font-size:14px;
        color:#dbe8fb;
        background:rgba(255,255,255,.02);
        border-top:1px solid var(--bod-line-soft);
        border-bottom:1px solid var(--bod-line-soft);
        vertical-align:middle;
    }

    .bod-table tbody td:first-child{
        border-left:1px solid var(--bod-line-soft);
        border-top-left-radius:14px;
        border-bottom-left-radius:14px;
    }

    .bod-table tbody td:last-child{
        border-right:1px solid var(--bod-line-soft);
        border-top-right-radius:14px;
        border-bottom-right-radius:14px;
    }

    .bod-table tbody tr:hover td{ background:rgba(59,130,246,.05); }

    .bod-rank{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:30px;
        height:30px;
        padding:0 8px;
        border-radius:10px;
        background:linear-gradient(180deg,#1d4ed8,#1e40af);
        color:#fff;
        font-size:12px;
        font-weight:800;
        box-shadow:0 0 18px rgba(59,130,246,.24);
    }

    .bod-entity{
        font-weight:700;
        color:#f3f8ff;
    }

    .bod-money{
        text-align:right;
        font-variant-numeric:tabular-nums;
        font-weight:800;
        color:#f8fbff;
        white-space:nowrap;
    }

    .bod-section-body{ padding:0 16px 16px; }

    .bod-footer-note{
        padding-top:8px;
        font-size:11px;
        color:#7286a4;
        text-align:right;
    }

    .bod-empty{
        padding:28px 18px;
        text-align:center;
        color:#8ea1bd;
        font-size:13px;
    }

    .bod-zona-shell{ padding:0 16px 16px; }

    .bod-zona-layout{
        display:grid;
        grid-template-columns:minmax(0, 1.75fr) minmax(320px, .95fr);
        gap:18px;
        align-items:stretch;
    }

    .bod-zona-chart-card,
    .bod-zona-side,
    .bod-zona-table-card{
        background:linear-gradient(180deg, rgba(9,18,33,.96), rgba(6,12,23,.98));
        border:1px solid var(--bod-line);
        border-radius:20px;
        box-shadow:var(--bod-shadow);
    }

    .bod-zona-chart-card{
        padding:18px 18px 12px;
        min-height:460px;
    }

    .bod-zona-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:14px;
    }

    .bod-zona-title{
        margin:0;
        font-size:18px;
        font-weight:800;
        color:#f8fbff;
    }

    .bod-zona-subtitle{
        margin-top:4px;
        font-size:12px;
        color:#8ea1bd;
    }

    .bod-zona-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:34px;
        padding:8px 12px;
        border-radius:999px;
        background:rgba(59,130,246,.12);
        border:1px solid rgba(59,130,246,.22);
        color:#cfe0ff;
        font-size:12px;
        font-weight:800;
        white-space:nowrap;
    }

    #zonaChart{
        width:100%;
        min-height:360px;
    }

    .bod-zona-side{
        padding:16px;
        display:flex;
        flex-direction:column;
        gap:14px;
    }

    .bod-zona-top{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:14px;
        padding:16px;
        border-radius:18px;
        background:linear-gradient(90deg, rgba(29,78,216,.22), rgba(59,130,246,.08));
        border:1px solid rgba(59,130,246,.18);
    }

    .bod-zona-top-name{
        font-size:17px;
        font-weight:800;
        color:#fff;
        line-height:1.2;
    }

    .bod-zona-top-note{
        margin-top:4px;
        font-size:12px;
        color:#bdd0ef;
    }

    .bod-zona-top-sales{
        text-align:right;
        font-size:18px;
        font-weight:800;
        color:#fff;
        white-space:nowrap;
    }

    .bod-zona-stats{
        display:grid;
        grid-template-columns:repeat(2, minmax(0,1fr));
        gap:12px;
    }

    .bod-zona-stat{
        min-height:108px;
        padding:14px;
        border-radius:16px;
        background:rgba(255,255,255,.03);
        border:1px solid var(--bod-line-soft);
    }

    .bod-zona-stat.wide{ grid-column:1 / -1; }

    .bod-zona-stat-label{
        margin-bottom:8px;
        font-size:11px;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.08em;
        color:#8ea1bd;
    }

    .bod-zona-stat-value{
        font-size:28px;
        line-height:1.05;
        font-weight:800;
        color:#fff;
        letter-spacing:-.03em;
        word-break:break-word;
    }

    .bod-zona-stat-value.sm{ font-size:22px; }

    .bod-zona-stat-note{
        margin-top:6px;
        font-size:12px;
        color:#91a6c4;
    }

    .bod-zona-table-card{
        margin-top:18px;
        padding:16px;
    }

    .bod-zona-table-wrap{ overflow-x:auto; }

    .bod-zona-table{
        width:100%;
        min-width:860px;
        border-collapse:separate;
        border-spacing:0 10px;
    }

    .bod-zona-table th{
        padding:0 12px 8px;
        text-align:left;
        font-size:11px;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.08em;
        color:#8ea1bd;
        white-space:nowrap;
    }

    .bod-zona-table td{
        padding:14px 12px;
        background:rgba(255,255,255,.02);
        border-top:1px solid var(--bod-line-soft);
        border-bottom:1px solid var(--bod-line-soft);
        color:#e7f0ff;
        font-size:14px;
        vertical-align:middle;
    }

    .bod-zona-table td:first-child{
        border-left:1px solid var(--bod-line-soft);
        border-top-left-radius:14px;
        border-bottom-left-radius:14px;
    }

    .bod-zona-table td:last-child{
        border-right:1px solid var(--bod-line-soft);
        border-top-right-radius:14px;
        border-bottom-right-radius:14px;
    }

    .bod-zona-table tr:hover td{ background:rgba(59,130,246,.06); }

    .bod-zona-outlet{
        font-weight:800;
        color:#fff;
    }

    .bod-zona-meta{
        margin-top:4px;
        font-size:12px;
        color:#8ea1bd;
    }

    .bod-zona-money{
        text-align:right;
        font-weight:800;
        white-space:nowrap;
        color:#fff;
    }

    .bod-filter-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .select2-container{ width:100% !important; }

    .select2-container .select2-selection--single{
        height:46px !important;
        border-radius:14px !important;
        border:1px solid rgba(148,163,184,.18) !important;
        background:rgba(255,255,255,.04) !important;
        color:#eef4ff !important;
        display:flex !important;
        align-items:center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered{
        color:#eef4ff !important;
        line-height:46px !important;
        padding-left:14px !important;
        padding-right:36px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:46px !important;
        right:10px !important;
    }

    .select2-dropdown{
        background:#0f172a !important;
        border:1px solid rgba(148,163,184,.18) !important;
        color:#eef4ff !important;
    }

    .select2-search__field{
        background:#0b1220 !important;
        color:#eef4ff !important;
        border:1px solid rgba(148,163,184,.18) !important;
    }

    .select2-results__option{ color:#eaf2ff !important; }

    .select2-container--default .select2-results__option--highlighted[aria-selected]{
        background:#1d4ed8 !important;
    }

    .bod-pagination{
        display:flex;
        justify-content:flex-end;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
        margin-top:14px;
    }

    .bod-pagination-btn,
    .bod-pagination .page-link{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:38px;
        height:38px;
        padding:0 12px;
        border:none;
        border-radius:10px;
        background:rgba(255,255,255,.05);
        color:#dbe8fb;
        text-decoration:none;
        font-size:13px;
        font-weight:700;
        cursor:pointer;
        transition:.2s ease;
    }

    .bod-pagination-btn:hover,
    .bod-pagination .page-link:hover{
        background:rgba(59,130,246,.18);
        color:#fff;
    }

    .bod-pagination-btn.active,
    .bod-pagination .page-item.active .page-link{
        background:linear-gradient(180deg,#1d4ed8,#1e40af);
        color:#fff;
    }

    .bod-pagination-btn:disabled,
    .bod-pagination .page-item.disabled .page-link{
        background:rgba(255,255,255,.02);
        color:#6f829f;
        cursor:not-allowed;
        opacity:.7;
    }

    .bod-pagination .pagination{
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
        margin:0;
    }

    .bod-pagination .page-item{ list-style:none; }

    @media (max-width:1400px){
        .bod-kpi-grid{ grid-template-columns:repeat(3, minmax(0, 1fr)); }
        .bod-filter-grid{ grid-template-columns:repeat(3, minmax(0, 1fr)); }
        .bod-filter-col.span-2{ grid-column:span 1; }
    }

    @media (max-width:1200px){
        .bod-grid-2{ grid-template-columns:1fr; }
        .bod-zona-layout{ grid-template-columns:1fr; }
    }

    @media (max-width:992px){
        .bod-kpi-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
        .bod-filter-grid{ grid-template-columns:repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width:768px){
        .bod-dashboard{ padding:16px; }
        .bod-filter-body{ padding:14px; }
        .bod-panel-head, .bod-section-head{ padding:14px 14px 10px; }
        .bod-table-wrap{ padding:0 10px 12px; }
        .bod-section-body{ padding:0 10px 12px; }
        .bod-zona-shell{ padding:0 10px 12px; }
        .bod-zona-chart-card{ min-height:auto; padding:14px; }
        #zonaChart{ min-height:300px; }
        .bod-zona-stats{ grid-template-columns:1fr; }
        .bod-zona-stat.wide{ grid-column:auto; }
        .bod-zona-top{ flex-direction:column; align-items:flex-start; }
        .bod-zona-top-sales{ text-align:left; }
        .bod-filter-grid{ grid-template-columns:1fr; }
    }

    @media (max-width:576px){
        .bod-dashboard{ padding:12px; }
        .bod-title{ font-size:28px; }
        .bod-subtitle{ font-size:12px; }
        .bod-actions{ width:100%; }
        .bod-btn{ flex:1 1 auto; }
        .bod-kpi-grid{ grid-template-columns:1fr; }
        .bod-table{ min-width:500px; }
        .bod-table.outlet{ min-width:760px; }
        .bod-footer-note{ text-align:left; }
    }
</style>
<style>
        .bod-filter-head{
        cursor:pointer;
        user-select:none;
    }

    .bod-filter-toggle-icon{
        width:34px;
        height:34px;
        display:flex;
        align-items:center;
        justify-content:center;
        border-radius:999px;
        border:1px solid rgba(148,163,184,.18);
        background:rgba(255,255,255,.06);
        color:#fff;
        font-size:18px;
        font-weight:800;
        flex:0 0 34px;
        transition:.25s ease;
    }

    .bod-filter-body{
        padding:18px;
        max-height:1200px;
        overflow:hidden;
        opacity:1;
        transition:max-height .3s ease, opacity .25s ease, padding .25s ease;
    }

    .bod-filter-card.is-collapsed .bod-filter-body{
        max-height:0;
        opacity:0;
        padding-top:0;
        padding-bottom:0;
    }

    .bod-filter-card.is-collapsed .bod-filter-toggle-icon{
        transform:rotate(0deg);
    }

    .bod-filter-card:not(.is-collapsed) .bod-filter-toggle-icon{
        transform:rotate(45deg);
    }

    /* =========================
       PAGINATION FIX - COMPACT
       Support Laravel Tailwind & Bootstrap markup
    ========================= */
    .bod-pagination{
        display:flex !important;
        justify-content:flex-end !important;
        align-items:center !important;
        width:100% !important;
        margin-top:12px !important;
        padding:0 !important;
        gap:6px !important;
        font-size:12px !important;
        line-height:1 !important;
    }

    .bod-pagination nav{
        width:100% !important;
        display:flex !important;
        justify-content:flex-end !important;
        align-items:center !important;
    }

    .bod-pagination nav > div{
        display:flex !important;
        justify-content:flex-end !important;
        align-items:center !important;
        width:auto !important;
        gap:6px !important;
    }

    .bod-pagination nav p,
    .bod-pagination nav .text-sm,
    .bod-pagination nav .text-gray-700{
        margin:0 !important;
        color:#dbe8fb !important;
        font-size:12px !important;
        line-height:1.2 !important;
    }

    .bod-pagination .pagination{
        display:flex !important;
        align-items:center !important;
        justify-content:flex-end !important;
        gap:6px !important;
        margin:0 !important;
        padding:0 !important;
        flex-wrap:wrap !important;
    }

    .bod-pagination .page-item{
        list-style:none !important;
        margin:0 !important;
        padding:0 !important;
    }

    .bod-pagination a,
    .bod-pagination span,
    .bod-pagination .page-link,
    .bod-pagination .relative,
    .bod-pagination .inline-flex{
        display:inline-flex !important;
        align-items:center !important;
        justify-content:center !important;
        width:auto !important;
        min-width:28px !important;
        height:28px !important;
        min-height:28px !important;
        max-height:28px !important;
        padding:0 8px !important;
        margin:0 !important;
        border-radius:8px !important;
        border:1px solid rgba(148,163,184,.15) !important;
        background:rgba(255,255,255,.04) !important;
        color:#dbe8fb !important;
        font-size:12px !important;
        font-weight:600 !important;
        line-height:28px !important;
        text-decoration:none !important;
        box-shadow:none !important;
        position:static !important;
    }

    .bod-pagination a:hover,
    .bod-pagination .page-link:hover{
        background:rgba(59,130,246,.20) !important;
        color:#fff !important;
    }

    .bod-pagination .active .page-link,
    .bod-pagination .page-item.active .page-link,
    .bod-pagination span[aria-current="page"],
    .bod-pagination span[aria-current="page"] span{
        background:linear-gradient(180deg,#1d4ed8,#1e40af) !important;
        color:#fff !important;
        border-color:transparent !important;
    }

    .bod-pagination .disabled .page-link,
    .bod-pagination [aria-disabled="true"],
    .bod-pagination [aria-disabled="true"] span{
        opacity:.45 !important;
        cursor:not-allowed !important;
        pointer-events:none !important;
    }

    .bod-pagination svg,
    .bod-pagination .page-link svg,
    .bod-pagination span svg,
    .bod-pagination a svg{
        width:12px !important;
        height:12px !important;
        min-width:12px !important;
        min-height:12px !important;
        max-width:12px !important;
        max-height:12px !important;
        display:block !important;
        flex:0 0 12px !important;
    }

    .bod-pagination .hidden{
        display:none !important;
    }

    .bod-pagination .sm\\:flex-1,
    .bod-pagination .sm\\:items-center,
    .bod-pagination .sm\\:justify-between{
        flex:none !important;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">
            <div class="bod-dashboard">
                <div class="bod-shell">

                    <!--<div class="bod-topbar">-->
                    <!--    <div>-->
                    <!--        <h1 class="bod-title">Market Performance Dashboard</h1>-->
                    <!--        <div class="bod-subtitle">-->
                    <!--            Executive analytics view with stock-market style presentation, dibuat lebih terasa seperti analysis board market saham tanpa mengubah logic data.-->
                    <!--            @if(!empty($lastSyncAt))-->
                    <!--                • Last sync: {{ $lastSyncAt }}-->
                    <!--            @endif-->
                    <!--        </div>-->
                    <!--    </div>-->

                    <!--    <div class="bod-actions">-->
                    <!--        <button type="button" class="bod-btn bod-btn-outline">Board Analytics</button>-->
                    <!--        <button type="button" class="bod-btn bod-btn-outline">Bullish View</button>-->
                    <!--        <button type="button" class="bod-btn bod-btn-outline">Volatility Ready</button>-->
                    <!--    </div>-->
                    <!--</div>-->

                    <div class="bod-filter-card is-collapsed" id="bodFilterCard">
                        <div class="bod-filter-head" id="bodFilterToggle" role="button" tabindex="0" aria-expanded="false">
                            <div>
                                <div class="bod-filter-title">Filter Dashboard</div>
                                <div class="bod-filter-sub">Sesuaikan periode dan outlet untuk melihat performa detail</div>
                            </div>

                            <div class="bod-filter-toggle-icon" id="bodFilterIcon">+</div>
                        </div>

                        <div class="bod-filter-body" id="bodFilterBody">
                            <form method="GET" action="{{ route('investor.sales.dashboardBOD') }}">
                                <div class="bod-filter-grid">

                                    <div class="bod-filter-col">
                                        <label class="bod-form-label">Mode Periode</label>
                                        <select name="mode_periode" id="mode_periode" class="bod-select">
                                            <option value="range" {{ ($filters['mode_periode'] ?? 'range') === 'range' ? 'selected' : '' }}>
                                                Range Tanggal
                                            </option>
                                            <option value="bulanan" {{ ($filters['mode_periode'] ?? '') === 'bulanan' ? 'selected' : '' }}>
                                                Bulanan
                                            </option>
                                        </select>
                                    </div>

                                    <div class="bod-filter-col period-range">
                                        <label class="bod-form-label">Tanggal Awal</label>
                                        <input
                                            type="date"
                                            name="tanggal_awal"
                                            class="bod-input"
                                            value="{{ $filters['tanggal_awal'] ?? '' }}"
                                        >
                                    </div>

                                    <div class="bod-filter-col period-range">
                                        <label class="bod-form-label">Tanggal Akhir</label>
                                        <input
                                            type="date"
                                            name="tanggal_akhir"
                                            class="bod-input"
                                            value="{{ $filters['tanggal_akhir'] ?? '' }}"
                                        >
                                    </div>

                                    <div class="bod-filter-col period-bulanan" style="display:none;">
                                        <label class="bod-form-label">Bulan</label>
                                        <select name="bulan" class="bod-select">
                                            <option value="">-- Semua Bulan --</option>
                                            @foreach(($filterOptions['bulan'] ?? []) as $bulanValue => $bulanLabel)
                                                <option
                                                    value="{{ $bulanValue }}"
                                                    {{ ($filters['bulan'] ?? '') === $bulanValue ? 'selected' : '' }}
                                                >
                                                    {{ $bulanLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="bod-filter-col period-bulanan" style="display:none;">
                                        <label class="bod-form-label">Tahun</label>
                                        <select name="tahun" class="bod-select">
                                            <option value="">-- Semua Tahun --</option>
                                            @foreach(($filterOptions['tahun'] ?? []) as $tahunOption)
                                                <option
                                                    value="{{ $tahunOption }}"
                                                    {{ ($filters['tahun'] ?? '') == $tahunOption ? 'selected' : '' }}
                                                >
                                                    {{ $tahunOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="bod-filter-col span-2">
                                        <label class="bod-form-label">Outlet</label>
                                        <select name="outlet" id="filter-outlet" class="bod-select">
                                            <option value="">-- Semua Outlet --</option>
                                            @foreach(($filterOptions['outlets'] ?? []) as $outlet)
                                                <option
                                                    value="{{ $outlet['id'] }}"
                                                    {{ ($filters['outlet'] ?? '') === $outlet['id'] ? 'selected' : '' }}
                                                >
                                                    {{ $outlet['text'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="bod-filter-col">
                                        <label class="bod-form-label">Kota</label>
                                        <select name="kota" class="bod-select">
                                            <option value="">-- Semua Kota --</option>
                                            @foreach(($filterOptions['kota'] ?? []) as $kotaOption)
                                                <option
                                                    value="{{ $kotaOption }}"
                                                    {{ ($filters['kota'] ?? '') === $kotaOption ? 'selected' : '' }}
                                                >
                                                    {{ $kotaOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="bod-filter-col">
                                        <label class="bod-form-label">AM</label>
                                        <select name="am" class="bod-select">
                                            <option value="">-- Semua AM --</option>
                                            @foreach(($filterOptions['am'] ?? []) as $amOption)
                                                <option
                                                    value="{{ $amOption }}"
                                                    {{ ($filters['am'] ?? '') === $amOption ? 'selected' : '' }}
                                                >
                                                    {{ $amOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="bod-filter-col">
                                        <label class="bod-form-label">Zona</label>
                                        <select name="zona" class="bod-select">
                                            <option value="">-- Semua Zona --</option>
                                            @foreach(($filterOptions['zona'] ?? []) as $zonaOption)
                                                <option
                                                    value="{{ $zonaOption }}"
                                                    {{ ($filters['zona'] ?? '') === $zonaOption ? 'selected' : '' }}
                                                >
                                                    {{ $zonaOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="bod-filter-col full">
                                        <div class="bod-filter-actions">
                                            <button type="submit" class="bod-submit">Tampilkan</button>
                                            <a href="{{ route('investor.sales.dashboardBOD') }}" class="bod-btn bod-btn-outline">Reset</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="bod-kpi-grid">
                        <div class="bod-kpi green">
                            <div class="bod-kpi-head">
                                <div class="bod-kpi-label">Omset</div>
                                <span class="bod-kpi-dot"></span>
                            </div>
                            <div class="bod-kpi-value">
                                {{ formatRupiahShort($cards['omset'] ?? 0) }}
                            </div>
                            <div class="bod-kpi-foot">
                                <span>Total revenue</span>
                                <span class="bod-badge-trend up">▲ 8.4%</span>
                            </div>
                        </div>

                        <div class="bod-kpi green">
                            <div class="bod-kpi-head">
                                <div class="bod-kpi-label">Avg Sales / Day</div>
                                <span class="bod-kpi-dot"></span>
                            </div>
                            <div class="bod-kpi-value">
                                {{ formatRupiahShort($cards['avg_sales_day'] ?? 0) }}
                            </div>
                            <div class="bod-kpi-foot">
                                <span>Daily run rate</span>
                                <span class="bod-badge-trend up">▲ 3.1%</span>
                            </div>
                        </div>

                        <div class="bod-kpi blue">
                            <div class="bod-kpi-head">
                                <div class="bod-kpi-label">Target</div>
                                <span class="bod-kpi-dot"></span>
                            </div>
                            <div class="bod-kpi-value">
                                {{ formatRupiahShort($cards['target'] ?? 0) }}
                            </div>
                            <div class="bod-kpi-foot">
                                <span>Projected target</span>
                                <span class="bod-badge-trend up">▲ On Track</span>
                            </div>
                        </div>

                        <div class="bod-kpi gray">
                            <div class="bod-kpi-head">
                                <div class="bod-kpi-label">Total CU</div>
                                <span class="bod-kpi-dot"></span>
                            </div>
                            <div class="bod-kpi-value">
                                {{ number_format($cards['total_cu'] ?? 0, 0, ',', '.') }}
                            </div>
                            <div class="bod-kpi-foot">
                                <span>Customer unit</span>
                                <span class="bod-badge-trend flat">Stable</span>
                            </div>
                        </div>

                        <div class="bod-kpi red">
                            <div class="bod-kpi-head">
                                <div class="bod-kpi-label">Variance</div>
                                <span class="bod-kpi-dot"></span>
                            </div>
                            <div class="bod-kpi-value">
                                {{ formatRupiahShort($cards['variance'] ?? 0) }}
                            </div>
                            <div class="bod-kpi-foot">
                                <span>Gap to target</span>
                                <span class="bod-badge-trend down">
                                    {{ ($cards['variance'] ?? 0) > 0 ? '▼ Below Target' : '▲ On/Above Target' }}
                                </span>
                            </div>
                        </div>

                        <div class="bod-kpi gray">
                            <div class="bod-kpi-head">
                                <div class="bod-kpi-label">Avg Check</div>
                                <span class="bod-kpi-dot"></span>
                            </div>
                            <div class="bod-kpi-value small">
                                {{ number_format($cards['avg_check'] ?? 0, 2, ',', '.') }}
                            </div>
                            <div class="bod-kpi-foot">
                                <span>Basket size</span>
                                <span class="bod-badge-trend flat">Neutral</span>
                            </div>
                        </div>

                        <div class="bod-kpi black">
                            <div class="bod-kpi-head">
                                <div class="bod-kpi-label">Achievement</div>
                                <span class="bod-kpi-dot"></span>
                            </div>
                            <div class="bod-kpi-value">
                                {{ number_format($cards['achievement'] ?? 0, 2, ',', '.') }}%
                            </div>
                            <div class="bod-kpi-foot">
                                <span>Performance index</span>
                                <span class="bod-badge-trend up">▲ Strong</span>
                            </div>
                        </div>
                    </div>

                    <div class="bod-section" style="margin-bottom:18px;">
                        <div class="bod-section-head">
                            <div>
                                <div class="bod-section-title">Zona Performance Z1 - Z4</div>
                                <div class="bod-section-sub">Klik bar chart untuk lihat detail performa zona dan outlet.</div>
                            </div>
                            <div class="bod-section-chip">Landscape View</div>
                        </div>

                        <div class="bod-zona-shell">
                            <div class="bod-zona-layout">
                                <div class="bod-zona-chart-card">
                                    <div class="bod-zona-head">
                                        <div>
                                            <h3 class="bod-zona-title">Sales by Zone</h3>
                                            <div class="bod-zona-subtitle">Visual komparasi total sales antar zona</div>
                                        </div>
                                        <div class="bod-zona-badge" id="detailZonaBadge">
                                            {{ !empty($zonaChart[0]['zona']) ? 'Zona '.$zonaChart[0]['zona'] : 'Zona -' }}
                                        </div>
                                    </div>

                                    <div id="zonaChart"></div>
                                </div>

                                <div class="bod-zona-side">
                                    <div class="bod-zona-top">
                                        <div>
                                            <div class="bod-zona-top-name" id="topOutletName">-</div>
                                            <div class="bod-zona-top-note">Top outlet di zona terpilih</div>
                                        </div>
                                        <div class="bod-zona-top-sales" id="topOutletSales">Rp 0</div>
                                    </div>

                                    <div class="bod-zona-stats">
                                        <div class="bod-zona-stat">
                                            <div class="bod-zona-stat-label">Zona</div>
                                            <div class="bod-zona-stat-value" id="detailZona">-</div>
                                            <div class="bod-zona-stat-note">Zona aktif pada chart</div>
                                        </div>

                                        <div class="bod-zona-stat">
                                            <div class="bod-zona-stat-label">Outlet</div>
                                            <div class="bod-zona-stat-value" id="detailOutletCount">0</div>
                                            <div class="bod-zona-stat-note">Jumlah outlet</div>
                                        </div>

                                        <div class="bod-zona-stat wide">
                                            <div class="bod-zona-stat-label">Total Sales</div>
                                            <div class="bod-zona-stat-value sm" id="detailSales">Rp 0</div>
                                            <div class="bod-zona-stat-note">Akumulasi omset zona</div>
                                        </div>

                                        <div class="bod-zona-stat">
                                            <div class="bod-zona-stat-label">Total CU</div>
                                            <div class="bod-zona-stat-value sm" id="detailCU">0</div>
                                            <div class="bod-zona-stat-note">Customer unit</div>
                                        </div>

                                        <div class="bod-zona-stat">
                                            <div class="bod-zona-stat-label">Avg Check</div>
                                            <div class="bod-zona-stat-value sm" id="detailAvgCheck">0</div>
                                            <div class="bod-zona-stat-note">Rata-rata transaksi</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bod-zona-table-card">
                                <div class="bod-zona-head" style="margin-bottom:10px;">
                                    <div>
                                        <h3 class="bod-zona-title" style="font-size:16px;">Detail Outlet Zona</h3>
                                        <div class="bod-zona-subtitle">Daftar outlet dalam zona yang dipilih</div>
                                    </div>
                                </div>

                                <div class="bod-zona-table-wrap">
                                    <table class="bod-zona-table">
                                        <thead>
                                            <tr>
                                                <th>Outlet</th>
                                                <th style="text-align:right;">Sales</th>
                                                <th style="text-align:right;">Target</th>
                                                <th style="text-align:right;">Variance</th>
                                                <th style="text-align:right;">Achievement</th>
                                                <th style="text-align:right;">CU</th>
                                                <th style="text-align:right;">Avg Check</th>
                                                <th style="text-align:right;">Kontribusi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailOutletBody"></tbody>
                                    </table>
                                </div>
                                <div id="detailOutletPagination" class="bod-pagination"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bod-grid-2">
                        <div class="bod-panel">
                            <div class="bod-panel-head">
                                <div>
                                    <div class="bod-panel-title">Top Cities</div>
                                    <div class="bod-panel-sub">Regional leaders by revenue contribution</div>
                                </div>
                                <div class="bod-panel-tag">Geo Screen</div>
                            </div>

                            <div class="bod-table-wrap">
                                <table class="bod-table">
                                    <thead>
                                        <tr>
                                            <th style="width:70px;">Rank</th>
                                            <th>City</th>
                                            <th style="text-align:right;">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($kota ?? [] as $i => $row)
                                            <tr>
                                                <td>
                                                    <span class="bod-rank">
                                                        {{ isset($kota) && method_exists($kota, 'currentPage') ? (($kota->currentPage() - 1) * $kota->perPage()) + $i + 1 : $i + 1 }}
                                                    </span>
                                                </td>
                                                <td><span class="bod-entity">{{ $row['nama'] ?? $row->nama ?? '-' }}</span></td>
                                                <td class="bod-money">{{ number_format($row['omset'] ?? $row->omset ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="bod-empty">Data kota belum tersedia</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if(isset($kota) && method_exists($kota, 'total') && $kota->total() > $kota->perPage())
                                <div class="bod-pagination">
                                    {{ $kota->appends(request()->except('kota_page'))->onEachSide(1)->links('pagination::bootstrap-4') }}
                                </div>
                            @endif
                        </div>

                        <div class="bod-panel">
                            <div class="bod-panel-head">
                                <div>
                                    <div class="bod-panel-title">Top Area Managers</div>
                                    <div class="bod-panel-sub">People leaderboard by revenue output</div>
                                </div>
                                <div class="bod-panel-tag">AM Screen</div>
                            </div>

                            <div class="bod-table-wrap">
                                <table class="bod-table">
                                    <thead>
                                        <tr>
                                            <th style="width:70px;">Rank</th>
                                            <th>Name</th>
                                            <th style="text-align:right;">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($am ?? [] as $i => $row)
                                            <tr>
                                                <td>
                                                    <span class="bod-rank">
                                                        {{ isset($am) && method_exists($am, 'currentPage') ? (($am->currentPage() - 1) * $am->perPage()) + $i + 1 : $i + 1 }}
                                                    </span>
                                                </td>
                                                <td><span class="bod-entity">{{ $row['nama'] ?? $row->nama ?? '-' }}</span></td>
                                                <td class="bod-money">{{ number_format($row['omset'] ?? $row->omset ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="bod-empty">Data area manager belum tersedia</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if(isset($am) && method_exists($am, 'total') && $am->total() > $am->perPage())
                                <div class="bod-pagination">
                                    {{ $am->appends(request()->except('am_page'))->onEachSide(1)->links('pagination::bootstrap-4') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bod-section-body">
    <div class="bod-table-wrap">
        <table class="bod-table outlet">
            <thead>
                <tr>
                    <th style="width:70px;">Rank</th>
                    <th>Outlet</th>
                    <th style="text-align:right;">Revenue</th>
                    <th style="text-align:right;">Target</th>
                    <th style="text-align:right;">Variance</th>
                    <th style="text-align:right;">Achievement</th>
                    <th style="text-align:right;">Customer Unit</th>
                    <th style="text-align:right;">Avg Check</th>
                </tr>
            </thead>
            <tbody>
                @forelse($outletBoard as $i => $row)
                    <tr>
                        <td>
                            <span class="bod-rank">
                                {{ (($outletBoard->currentPage() - 1) * $outletBoard->perPage()) + $i + 1 }}
                            </span>
                        </td>
                        <td>
                            <span class="bod-entity">{{ $row['nama_outlet'] ?? '-' }}</span>
                        </td>
                        <td class="bod-money">{{ number_format($row['omset'] ?? 0, 0, ',', '.') }}</td>
                        <td class="bod-money">{{ number_format($row['target'] ?? 0, 0, ',', '.') }}</td>
                        <td class="bod-money">{{ number_format($row['variance'] ?? 0, 0, ',', '.') }}</td>
                        <td class="bod-money">{{ number_format($row['achievement'] ?? 0, 2, ',', '.') }}%</td>
                        <td class="bod-money">{{ number_format($row['cu'] ?? 0, 0, ',', '.') }}</td>
                        <td class="bod-money">{{ number_format($row['avg_check'] ?? 0, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="bod-empty">Data outlet belum tersedia</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(isset($outletBoard) && $outletBoard->total() > $outletBoard->perPage())
        <div class="bod-pagination">
            {{ $outletBoard->appends([
                'kota_page' => request('kota_page'),
                'am_page' => request('am_page'),
            ])->onEachSide(1)->links('pagination::bootstrap-4') }}
        </div>
    @endif

    <div class="bod-footer-note">
        Dark analytics theme • BOD executive board
    </div>
</div>

                </div>
            </div>
        </div>
    </div>
</main>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    const zonaData = @json($zonaChart ?? []);

    function formatRupiah(angka) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(angka || 0));
    }

    function formatNumber(angka) {
        return new Intl.NumberFormat('id-ID').format(Number(angka || 0));
    }

    function formatDecimal(angka) {
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(Number(angka || 0));
    }

    function safeOutlets(outlets) {
        if (!Array.isArray(outlets)) return [];
        return [...outlets].sort((a, b) => Number(b.total_sales || 0) - Number(a.total_sales || 0));
    }

    function renderZonaDetail(index, page = 1) {
        const item = zonaData[index];
        const tbody = document.getElementById('detailOutletBody');
        const paginationEl = document.getElementById('detailOutletPagination');
        const perPage = 10;

        if (!item) {
            document.getElementById('detailZona').textContent = '-';
            document.getElementById('detailZonaBadge').textContent = 'Zona -';
            document.getElementById('detailSales').textContent = 'Rp 0';
            document.getElementById('detailCU').textContent = '0';
            document.getElementById('detailAvgCheck').textContent = '0';
            document.getElementById('detailOutletCount').textContent = '0';
            document.getElementById('topOutletName').textContent = '-';
            document.getElementById('topOutletSales').textContent = 'Rp 0';
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center;color:#8ea1bd;">Belum ada data outlet</td>
                </tr>
            `;
            if (paginationEl) paginationEl.innerHTML = '';
            return;
        }

        const outlets = safeOutlets(item.outlets);
        const topOutlet = outlets.length ? outlets[0] : null;

        document.getElementById('detailZona').textContent = item.zona ?? '-';
        document.getElementById('detailZonaBadge').textContent = 'Zona ' + (item.zona ?? '-');
        document.getElementById('detailSales').textContent = formatRupiah(item.total_sales ?? 0);
        document.getElementById('detailCU').textContent = formatNumber(item.total_cu ?? 0);
        document.getElementById('detailAvgCheck').textContent = formatDecimal(item.avg_check ?? 0);
        document.getElementById('detailOutletCount').textContent = formatNumber(item.outlet_count ?? outlets.length ?? 0);

        document.getElementById('topOutletName').textContent = topOutlet ? (topOutlet.nama_outlet ?? '-') : '-';
        document.getElementById('topOutletSales').textContent = topOutlet ? formatRupiah(topOutlet.total_sales ?? 0) : 'Rp 0';

        if (!outlets.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center;color:#8ea1bd;">Belum ada data outlet</td>
                </tr>
            `;
            if (paginationEl) paginationEl.innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(outlets.length / perPage);
        const currentPage = Math.min(Math.max(page, 1), totalPages);
        const start = (currentPage - 1) * perPage;
        const currentItems = outlets.slice(start, start + perPage);

        tbody.innerHTML = currentItems.map(row => `
            <tr>
                <td>
                    <div class="bod-zona-outlet">${row.nama_outlet ?? '-'}</div>
                    <div class="bod-zona-meta">Outlet performance detail</div>
                </td>
                <td class="bod-zona-money">${formatRupiah(row.total_sales ?? 0)}</td>
                <td class="bod-zona-money">${formatRupiah(row.target ?? 0)}</td>
                <td class="bod-zona-money">${formatRupiah(row.variance ?? 0)}</td>
                <td class="bod-zona-money">${formatDecimal(row.achievement ?? 0)}%</td>
                <td class="bod-zona-money">${formatNumber(row.cu ?? 0)}</td>
                <td class="bod-zona-money">${formatDecimal(row.avg_check ?? 0)}</td>
                <td class="bod-zona-money">${formatDecimal(row.kontribusi ?? 0)}%</td>
            </tr>
        `).join('');

        if (!paginationEl) return;

        if (totalPages <= 1) {
            paginationEl.innerHTML = '';
            return;
        }

        let buttons = `<button type="button" class="bod-pagination-btn" ${currentPage === 1 ? 'disabled' : ''} data-zona-page="${currentPage - 1}" data-zona-index="${index}">Prev</button>`;

        for (let i = 1; i <= totalPages; i++) {
            if (
                i === 1 ||
                i === totalPages ||
                (i >= currentPage - 1 && i <= currentPage + 1)
            ) {
                buttons += `<button type="button" class="bod-pagination-btn ${i === currentPage ? 'active' : ''}" data-zona-page="${i}" data-zona-index="${index}">${i}</button>`;
            } else if (
                i === currentPage - 2 ||
                i === currentPage + 2
            ) {
                buttons += `<span class="bod-pagination-btn" style="pointer-events:none;opacity:.7;">...</span>`;
            }
        }

        buttons += `<button type="button" class="bod-pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} data-zona-page="${currentPage + 1}" data-zona-index="${index}">Next</button>`;

        paginationEl.innerHTML = buttons;
        paginationEl.setAttribute('data-current-index', index);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.select2) {
            $('#filter-outlet').select2({
                placeholder: '-- Semua Outlet --',
                allowClear: true,
                width: '100%'
            });
            
            const filterCard = document.getElementById('bodFilterCard');
            const filterToggle = document.getElementById('bodFilterToggle');
            const filterBody = document.getElementById('bodFilterBody');
            const filterIcon = document.getElementById('bodFilterIcon');
            
            function setFilterCollapsed(collapsed) {
                if (!filterCard || !filterToggle) return;
            
                filterCard.classList.toggle('is-collapsed', collapsed);
                filterToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                if (filterIcon) {
                    filterIcon.textContent = collapsed ? '+' : '−';
                }
            }
            
            if (filterCard && filterToggle && filterBody) {
                setFilterCollapsed(true); // default hidden
            
                filterToggle.addEventListener('click', function () {
                    const isCollapsed = filterCard.classList.contains('is-collapsed');
                    setFilterCollapsed(!isCollapsed);
                });
            
                filterToggle.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const isCollapsed = filterCard.classList.contains('is-collapsed');
                        setFilterCollapsed(!isCollapsed);
                    }
                });
            }
        }

        const chartCategories = zonaData.map(item => item.zona ?? '-');
        const chartSeries = zonaData.map(item => Number(item.total_sales ?? 0));

        const chartOptions = {
            chart: {
                type: 'bar',
                height: 360,
                toolbar: { show: false },
                background: 'transparent',
                fontFamily: 'inherit',
                events: {
                    dataPointSelection: function(event, chartContext, config) {
                        renderZonaDetail(config.dataPointIndex);
                    }
                }
            },
            series: [{
                name: 'Total Sales',
                data: chartSeries
            }],
            colors: ['#3b82f6', '#06b6d4', '#22c55e', '#f59e0b'],
            plotOptions: {
                bar: {
                    borderRadius: 12,
                    columnWidth: '48%',
                    distributed: true,
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return (val / 1000000000).toFixed(1) + 'B';
                },
                offsetY: -18,
                style: {
                    fontSize: '11px',
                    fontWeight: 800,
                    colors: ['#dbeafe']
                },
                background: {
                    enabled: true,
                    foreColor: '#fff',
                    borderRadius: 6,
                    padding: 6,
                    opacity: 0.12,
                    borderWidth: 1,
                    borderColor: 'rgba(148,163,184,.15)'
                }
            },
            xaxis: {
                categories: chartCategories,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: {
                        colors: '#9fb2cc',
                        fontSize: '13px',
                        fontWeight: 700
                    }
                }
            },
            yaxis: {
                tickAmount: 4,
                labels: {
                    style: {
                        colors: '#9fb2cc',
                        fontSize: '12px'
                    },
                    formatter: function(value) {
                        return (value / 1000000000).toFixed(0) + 'B';
                    }
                }
            },
            grid: {
                borderColor: 'rgba(148,163,184,.10)',
                strokeDashArray: 4,
                padding: {
                    top: 10,
                    right: 10,
                    bottom: 0,
                    left: 10
                }
            },
            legend: { show: false },
            tooltip: {
                theme: 'dark',
                custom: function({ dataPointIndex }) {
                    const item = zonaData[dataPointIndex] || {};
                    return `
                        <div style="padding:12px 14px; min-width:220px;">
                            <div style="font-weight:800; margin-bottom:8px; color:#fff;">${item.zona ?? '-'}</div>
                            <div style="font-size:12px; margin-bottom:4px;">Total Sales: ${formatRupiah(item.total_sales ?? 0)}</div>
                            <div style="font-size:12px; margin-bottom:4px;">Total CU: ${formatNumber(item.total_cu ?? 0)}</div>
                            <div style="font-size:12px; margin-bottom:4px;">Avg Check: ${formatDecimal(item.avg_check ?? 0)}</div>
                            <div style="font-size:12px;">Outlet: ${formatNumber(item.outlet_count ?? 0)}</div>
                        </div>
                    `;
                }
            },
            states: {
                hover: {
                    filter: {
                        type: 'lighten',
                        value: 0.08
                    }
                },
                active: {
                    filter: {
                        type: 'darken',
                        value: 0.12
                    }
                }
            }
        };

        if (document.querySelector('#zonaChart')) {
            const zonaChart = new ApexCharts(document.querySelector('#zonaChart'), chartOptions);
            zonaChart.render();
        }

        document.addEventListener('click', function (event) {
            const btn = event.target.closest('[data-zona-page]');
            if (!btn) return;

            const page = Number(btn.getAttribute('data-zona-page') || 1);
            const index = Number(btn.getAttribute('data-zona-index') || 0);
            renderZonaDetail(index, page);
        });

        renderZonaDetail(0, 1);
    });
</script>

<script>
    function syncPeriodeMode() {
        const modeEl = document.getElementById('mode_periode');
        if (!modeEl) return;

        const mode = modeEl.value;
        const rangeEls = document.querySelectorAll('.period-range');
        const bulananEls = document.querySelectorAll('.period-bulanan');

        rangeEls.forEach(el => {
            el.style.display = mode === 'range' ? '' : 'none';
        });

        bulananEls.forEach(el => {
            el.style.display = mode === 'bulanan' ? '' : 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncPeriodeMode();

        const modeEl = document.getElementById('mode_periode');
        if (modeEl) {
            modeEl.addEventListener('change', syncPeriodeMode);
        }
    });
</script>

@include('Temp.internal.footer_internal')
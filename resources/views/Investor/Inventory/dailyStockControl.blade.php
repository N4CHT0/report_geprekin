<!-- INI ADALAH BLADE DAILYSTOCKCONTROL -->
{{-- EXPORT STOCK OPNAME TEMPLATE --}}
@section('title', 'Daily Stock Control')
@section('breadcrumb', 'Inventory / DSC')

@include('Temp.Investor.header')

{{-- ADJUSTMENT disembunyikan sementara, Alpine tidak diload agar halaman lebih ringan. --}}
{{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

@php
    $startDate = $startDate ?? request('start_date', '');
    $endDate   = $endDate ?? request('end_date', '');
    $missingOutlets = $missingOutlets ?? [];
    $missingCheckStartDate = $missingCheckStartDate ?? \Carbon\Carbon::parse($today ?? date('Y-m-d'))->subDay()->format('Y-m-d');
    $missingCheckEndDate = $missingCheckEndDate ?? $missingCheckStartDate;
    $missingCount = $missingCount ?? (is_countable($missingOutlets) ? count($missingOutlets) : 0);
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
    $periodRows = $periodRows ?? [];
    $periodSummary = $periodSummary ?? [];
    $periodStartDate = $periodStartDate ?? ($startDate ?: $today);
    $periodEndDate = $periodEndDate ?? ($endDate ?: $periodStartDate);
    $periodMaxDays = $periodMaxDays ?? 31;
    $periodLabel = \Carbon\Carbon::parse($periodStartDate)->format('d-m-Y') . ' s/d ' . \Carbon\Carbon::parse($periodEndDate)->format('d-m-Y');
    $isPeriodLoaded = (bool) ($isPeriodLoaded ?? request()->boolean('load_period') || request('active_tab') === 'periode');
    $periodLoadUrl = request()->fullUrlWithQuery(['load_period' => 1, 'active_tab' => 'periode']);
    $rapelOutletId = (int) ($selectedOutletIds[0] ?? 0);

    // Data existing untuk modal Rapel/Revisi Uang Plus.
    // Diisi dari detail shift yang sudah dibangun controller: draft lebih dulu, fallback final.
    // Tambahan aman: __all__ hanya untuk tampilan total semua bahan di modal, tidak mengubah rumus.
    $rapelExistingMap = [];
    foreach (collect($shiftRows ?? []) as $row) {
        $shiftKey = (string) data_get($row, 'shift', '');
        $bahanKey = (string) data_get($row, 'bahan_id', '');
        if ($shiftKey !== '' && $bahanKey !== '') {
            $uangExisting = (float) (data_get($row, 'uang') ?? data_get($row, 'uang_plus') ?? 0);
            $ketExisting = (string) (data_get($row, 'ket') ?? data_get($row, 'keterangan') ?? '');
            $sourceExisting = (string) (data_get($row, 'source') ?? '');

            $rapelExistingMap[$shiftKey][$bahanKey] = [
                'uang_plus' => $uangExisting,
                'keterangan' => $ketExisting,
                'source' => $sourceExisting,
                'nama' => (string) (data_get($row, 'nama') ?? data_get($row, 'nama_bahan') ?? ''),
            ];

            if (!isset($rapelExistingMap[$shiftKey]['__all__'])) {
                $rapelExistingMap[$shiftKey]['__all__'] = [
                    'uang_plus' => 0,
                    'keterangan' => '',
                    'source' => '',
                    'nama' => 'Semua Bahan',
                ];
            }

            $rapelExistingMap[$shiftKey]['__all__']['uang_plus'] += $uangExisting;

            if ($ketExisting !== '') {
                // FIX tampilan existing: jangan ulang keterangan yang sama puluhan kali.
                $existingKetParts = collect(explode('|', $rapelExistingMap[$shiftKey]['__all__']['keterangan'] ?? ''))
                    ->merge(explode('|', $ketExisting))
                    ->map(fn ($v) => trim((string) $v))
                    ->filter()
                    ->unique()
                    ->values();

                $rapelExistingMap[$shiftKey]['__all__']['keterangan'] = $existingKetParts->implode(' | ');
            }

            if ($sourceExisting === 'draft') {
                $rapelExistingMap[$shiftKey]['__all__']['source'] = 'draft';
            } elseif ($rapelExistingMap[$shiftKey]['__all__']['source'] === '') {
                $rapelExistingMap[$shiftKey]['__all__']['source'] = $sourceExisting;
            }
        }
    }

    // MODE RINGAN: sebelum outlet dipilih, jangan render data besar ke tabel.
    // Ini mencegah halaman berat karena semua row DSC/shift/ADJUSTMENT tidak dibangun di Blade.
    if (!$hasRequiredFilter) {
        $rekapRows = [];
        $shiftRows = [];
        $periodRows = [];
        $periodSummary = [];
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
    // ADJUSTMENT DI-HIDE SEMENTARA.
    // Tab lain tetap tampil: REKAP, PERIODE DSC, DETAIL PER SHIFT, OMSET & SETORAN, SALES.
    $canADJUSTMENT = false;

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
        --dsc-bg:#f5f7fb;
        --dsc-card:#ffffff;
        --dsc-soft:#f8fafc;
        --dsc-text:#111827;
        --dsc-muted:#64748b;
        --dsc-line:#d9e0e8;
        --dsc-line-soft:#edf1f5;
        --dsc-primary:#0972d3;
        --dsc-primary-dark:#033160;
        --dsc-success:#038b18;
        --dsc-danger:#d13212;
        --dsc-warning:#b7791f;
        --dsc-radius:14px;
        --dsc-radius-sm:10px;
        --dsc-shadow:0 8px 24px rgba(15,23,42,.06);
        --dsc-focus:0 0 0 3px rgba(9,114,211,.20);
        --dsc-page-pad:clamp(12px,2.2vw,24px);
    }

    *, *::before, *::after{ box-sizing:border-box; }

    html, body{ max-width:100%; overflow-x:hidden; }

    .dsc-shell{
        width:100%;
        max-width:100%;
        min-width:0;
        display:flex;
        flex-direction:column;
        gap:clamp(12px,2vw,18px);
        padding:var(--dsc-page-pad);
        color:var(--dsc-text);
    }

    .dsc-page,
    .dsc-section,
    .dsc-filter,
    .dsc-body,
    .tab-content,
    .tab-pane{
        width:100%;
        min-width:0;
        background:transparent;
        border:0;
        overflow:visible;
    }

    .dsc-header{
        width:100%;
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:14px;
        flex-wrap:wrap;
        padding:0 0 14px;
        border-bottom:1px solid var(--dsc-line);
    }

    .dsc-header > .d-flex{ width:100%; min-width:0; }

    .dsc-title{
        min-width:0;
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .dsc-title h4{
        margin:0;
        max-width:100%;
        font-size:clamp(1.1rem,2vw,1.55rem);
        line-height:1.15;
        font-weight:800;
        letter-spacing:-.02em;
        color:var(--dsc-text);
        overflow-wrap:anywhere;
    }

    .dsc-title-mark{
        width:40px;
        height:40px;
        flex:0 0 40px;
        display:grid;
        place-items:center;
        border-radius:12px;
        background:#eef7ff;
        color:var(--dsc-primary);
        border:1px solid #b6d7f5;
    }

    .dsc-badge,
    .badge-wh{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 10px;
        border-radius:999px;
        background:#eef7ff;
        color:var(--dsc-primary);
        border:1px solid #b6d7f5;
        font-size:.72rem;
        font-weight:900;
        line-height:1;
        white-space:nowrap;
    }

    .dsc-toolbar{
        min-width:0;
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
        width:40px;
        height:40px;
        display:grid;
        place-items:center;
        border:1px solid var(--dsc-line)!important;
        background:var(--dsc-card)!important;
        color:var(--dsc-text)!important;
        border-radius:12px!important;
    }
    .dsc-bell-count{
        position:absolute;
        top:-7px;
        right:-7px;
        min-width:21px;
        height:21px;
        padding:0 5px;
        display:grid;
        place-items:center;
        border-radius:999px;
        background:var(--dsc-danger);
        color:#fff;
        font-size:.68rem;
        font-weight:900;
        border:2px solid var(--dsc-card);
    }
    .dsc-bell-panel{
        width:min(92vw,440px);
        min-width:min(92vw,360px);
        padding:0;
        border:1px solid var(--dsc-line);
        border-radius:14px;
        box-shadow:0 18px 48px rgba(15,23,42,.18);
        overflow:hidden;
    }
    .dsc-bell-head{
        padding:12px 14px;
        background:var(--dsc-soft);
        border-bottom:1px solid var(--dsc-line);
        font-weight:900;
        color:var(--dsc-text);
    }
    .dsc-bell-body{ max-height:360px; overflow:auto; -webkit-overflow-scrolling:touch; }
    .dsc-bell-item{
        padding:10px 14px;
        border-bottom:1px solid var(--dsc-line-soft);
        font-weight:700;
    }
    .dsc-bell-item small{ color:var(--dsc-muted); font-weight:700; }
    .dsc-bell-foot{
        padding:10px 14px;
        background:var(--dsc-card);
        color:var(--dsc-muted);
        font-size:.78rem;
        font-weight:700;
    }

    .btn{
        min-height:40px;
        border-radius:10px!important;
        font-weight:800!important;
        box-shadow:none!important;
        transform:none!important;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:4px;
    }
    .btn-sm{ padding:.45rem .75rem; font-size:.82rem; }
    .btn-primary{ background:var(--dsc-primary)!important; border-color:var(--dsc-primary)!important; }
    .btn-primary:hover,
    .btn-primary:focus{ background:var(--dsc-primary-dark)!important; border-color:var(--dsc-primary-dark)!important; }
    .btn-success{ background:var(--dsc-success)!important; border-color:var(--dsc-success)!important; }
    .btn-ghost{ background:var(--dsc-card)!important; border:1px solid var(--dsc-line)!important; color:#414d5c!important; }
    .btn-ghost:hover{ background:#f2f6fb!important; color:var(--dsc-text)!important; }

    .dsc-meta-grid{
        display:grid;
        grid-template-columns:minmax(0,1.35fr) minmax(260px,.65fr);
        gap:14px;
        margin:16px 0 0;
    }

    .dsc-card,
    .kpi,
    .soft-alert,
    .dsc-filter-bar{
        min-width:0;
        background:var(--dsc-card);
        border:1px solid var(--dsc-line);
        border-radius:var(--dsc-radius);
        box-shadow:var(--dsc-shadow);
    }

    .dsc-card .dsc-card-head{
        padding:13px 14px;
        border-bottom:1px solid var(--dsc-line);
        background:var(--dsc-soft);
        color:var(--dsc-text);
        font-weight:800;
    }
    .dsc-card .dsc-card-body{ padding:14px; min-width:0; }

    .dsc-kv{
        display:grid;
        grid-template-columns:minmax(120px,170px) minmax(0,1fr);
        gap:8px 14px;
        font-size:.9rem;
        align-items:start;
    }
    .dsc-kv .k{ color:var(--dsc-muted); font-weight:800; }
    .dsc-kv .v{ color:var(--dsc-text); font-weight:800; min-width:0; overflow-wrap:anywhere; }

    .dsc-note{
        padding:11px 12px;
        border:1px solid #b6d7f5;
        background:#eef7ff;
        border-radius:var(--dsc-radius-sm);
        color:var(--dsc-primary-dark);
        font-weight:700;
        font-size:.84rem;
        line-height:1.45;
    }
    .dsc-help{ color:var(--dsc-muted); font-size:.82rem; font-weight:700; line-height:1.4; }

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
    .kpi .label{ font-size:.78rem; color:var(--dsc-muted); font-weight:800; margin-bottom:5px; }
    .kpi .value{ font-size:clamp(1rem,2vw,1.16rem); font-weight:900; color:var(--dsc-text); line-height:1.2; overflow-wrap:anywhere; }
    .kpi .icon{
        width:44px;
        height:44px;
        flex:0 0 44px;
        display:grid;
        place-items:center;
        border-radius:12px;
        background:#f8fafc;
        border:1px solid var(--dsc-line);
        font-size:1.05rem;
    }
    .kpi.primary .icon{ background:#eef7ff;color:var(--dsc-primary);border-color:#b6d7f5; }
    .kpi.success .icon{ background:#ecfdf3;color:var(--dsc-success);border-color:#b7e4bf; }
    .kpi.warning .icon{ background:#fff7ed;color:var(--dsc-warning);border-color:#f8d7a0; }

    .dsc-filter-bar{ padding:14px; margin:16px 0; }
    .filter-grid{
        display:grid;
        grid-template-columns:minmax(260px,1.35fr) minmax(160px,.65fr) minmax(160px,.65fr) minmax(130px,.45fr) minmax(220px,1fr) auto;
        gap:12px;
        align-items:end;
    }
    .filter-item{ min-width:0; }
    .filter-item label,
    .form-label{ display:block; color:var(--dsc-text); font-size:.78rem; font-weight:900; margin-bottom:6px; }

    .form-control,
    .form-select{
        width:100%;
        min-height:42px;
        border-radius:12px!important;
        border:1px solid var(--dsc-line)!important;
        box-shadow:none!important;
        color:var(--dsc-text);
        background:var(--dsc-card);
        font-size:.9rem;
        font-weight:700;
    }
    .form-control:focus,
    .form-select:focus{ border-color:var(--dsc-primary)!important; box-shadow:var(--dsc-focus)!important; }

    .nav-pills{
        width:100%;
        min-width:0;
        gap:4px;
        margin-bottom:16px!important;
        border-bottom:1px solid var(--dsc-line);
        flex-wrap:wrap;
    }
    .nav-pills .nav-link{
        position:relative;
        border-radius:10px 10px 0 0!important;
        border:0;
        background:transparent;
        color:var(--dsc-muted);
        font-weight:900;
        padding:11px 13px;
    }
    .nav-pills .nav-link:hover{ background:#eef7ff; color:var(--dsc-primary); }
    .nav-pills .nav-link.active{ background:transparent!important; color:var(--dsc-primary)!important; }
    .nav-pills .nav-link.active::after{
        content:"";
        position:absolute;
        left:10px;
        right:10px;
        bottom:-1px;
        height:3px;
        background:var(--dsc-primary);
        border-radius:999px 999px 0 0;
    }

    .dsc-scroll,
    .dsc-scroll-y{
        width:100%;
        min-width:0;
        background:var(--dsc-card);
        border:1px solid var(--dsc-line);
        border-radius:var(--dsc-radius);
        box-shadow:var(--dsc-shadow);
        overflow:auto;
        -webkit-overflow-scrolling:touch;
        overscroll-behavior:contain;
    }
    .dsc-scroll{ max-height:680px; }
    .dsc-scroll-y{ max-height:680px; overflow-y:auto; overflow-x:hidden; }
    .dsc-scroll-x{ width:100%; max-width:100%; overflow:auto; -webkit-overflow-scrolling:touch; touch-action:pan-x pan-y; }
    .dsc-scroll-x > table{ width:max-content; min-width:100%; }

    .dsc-table{
        width:100%;
        margin:0!important;
        color:var(--dsc-text);
        vertical-align:middle;
        font-size:.86rem;
        border-collapse:separate;
        border-spacing:0;
    }
    .dsc-table th,
    .dsc-table td{
        border-bottom:1px solid var(--dsc-line-soft)!important;
        padding:10px 11px;
        font-size:.84rem;
        white-space:nowrap;
        vertical-align:middle;
        background:var(--dsc-card);
        color:var(--dsc-text);
    }
    .dsc-table th{
        position:sticky;
        top:0;
        z-index:5;
        background:var(--dsc-soft)!important;
        color:#414d5c;
        font-size:.72rem;
        font-weight:900;
        text-align:center;
        text-transform:uppercase;
        letter-spacing:.04em;
        border-bottom:1px solid var(--dsc-line)!important;
    }
    .dsc-table td.num{ text-align:right; font-variant-numeric:tabular-nums; font-weight:800; }
    .dsc-table td.center{ text-align:center; font-weight:800; }
    .dsc-table td.item{ font-weight:900; }
    .dsc-table tbody tr:hover td{ background:#f2f8fd; }

    .sticky-1{ position:sticky; left:0; z-index:4; background:var(--dsc-card)!important; }
    .sticky-2{ position:sticky; left:62px; z-index:4; background:var(--dsc-card)!important; box-shadow:10px 0 0 rgba(15,23,42,.03); }
    thead .sticky-1,
    thead .sticky-2{ background:var(--dsc-soft)!important; z-index:6; }

    .w-no{ width:62px; min-width:62px; }
    .w-name{ width:300px; min-width:300px; }
    .w-sat{ width:76px; min-width:76px; }
    .w-num{ width:124px; min-width:124px; }
    .w-wide{ width:300px; min-width:300px; }

    .cell-negative{ background:#fff1f0!important; color:var(--dsc-danger)!important; font-weight:900!important; }

    .soft-alert{ padding:13px 14px; margin-bottom:14px; }
    .soft-alert.primary{ border-color:#b6d7f5; background:#eef7ff; }
    .soft-alert.warning{ border-color:#f8d7a0; background:#fff7ed; }
    .soft-alert.danger{ border-color:#f3b8ad; background:#fff1f0; }
    .soft-alert .title{ color:var(--dsc-text); font-weight:900; margin-bottom:4px; }
    .soft-alert .desc{ color:var(--dsc-muted); font-weight:700; font-size:.84rem; margin:0; line-height:1.45; }

    .modal .modal-dialog{ max-width:min(96vw,1100px); margin:.75rem auto; }
    .modal .modal-content{ border-radius:14px; border:1px solid var(--dsc-line); box-shadow:0 24px 64px rgba(15,23,42,.18); overflow:hidden; }
    .modal .modal-header,
    .modal .modal-footer{ background:var(--dsc-soft); border-color:var(--dsc-line); }
    .omset-photo{ max-width:100%; max-height:240px; border-radius:10px; border:1px solid var(--dsc-line); box-shadow:var(--dsc-shadow); width:auto; background:var(--dsc-card); }

    .select2-container{ width:100%!important; min-width:0!important; }
    .select2-container--default .select2-selection--single{
        border:1px solid var(--dsc-line)!important;
        border-radius:12px!important;
        min-height:42px;
        display:flex!important;
        align-items:center;
        background:var(--dsc-card);
    }
    .select2-container .select2-selection--single .select2-selection__rendered{
        width:100%;
        min-width:0;
        line-height:40px!important;
        padding-left:.75rem!important;
        padding-right:2rem!important;
        font-weight:800;
        overflow:hidden;
        text-overflow:ellipsis;
    }
    .select2-container .select2-selection--single .select2-selection__arrow{ height:40px!important; }
    .select2-dropdown,
    .select2-results__option{ background:var(--dsc-card)!important; color:var(--dsc-text)!important; }

    #tab-missing-dsc .dsc-scroll{ overflow:visible; }
    #tab-missing-dsc table.dsc-table th{ position:static!important; }
    #tab-missing-dsc .sticky-1,
    #tab-missing-dsc .sticky-2{ position:static!important; left:auto!important; box-shadow:none!important; }
    #tab-missing-dsc table.dsc-table td,
    #tab-missing-dsc table.dsc-table th{ white-space:normal!important; }
    #dscTableADJUSTMENT tr.row-warning td{ background:#fff7ed!important; }

    @media (max-width:1400px){
        .filter-grid{ grid-template-columns:repeat(4,minmax(0,1fr)); }
        .filter-grid .span-2{ grid-column:span 2; }
    }

    @media (max-width:992px){
        .dsc-meta-grid,
        .kpi-grid{ grid-template-columns:1fr; }
        .filter-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        .filter-grid .span-2{ grid-column:span 2; }
        .dsc-toolbar{ justify-content:flex-start; }
    }

    @media (max-width:768px){
        :root{ --dsc-page-pad:10px; --dsc-radius:12px; }
        .dsc-shell{ gap:12px; padding:10px 8px 80px; }
        .dsc-header{ padding-bottom:12px; }
        .dsc-header > .d-flex{ gap:12px!important; }
        .dsc-title{ gap:8px; align-items:flex-start; }
        .dsc-title-mark{ width:36px; height:36px; flex-basis:36px; border-radius:10px; }
        .dsc-title h4{ font-size:1.06rem; max-width:calc(100vw - 78px); }
        .dsc-badge{ font-size:.66rem; padding:5px 8px; }
        .dsc-toolbar{ width:100%; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:8px; }
        .dsc-toolbar .dsc-bell-wrap{ grid-column:auto; }
        .dsc-toolbar .btn,
        .dsc-toolbar a.btn{ width:100%; min-width:0; padding:.48rem .5rem; font-size:.72rem; white-space:normal; line-height:1.2; }
        .dsc-bell-btn{ width:100%!important; height:42px!important; }
        .dsc-bell-panel{ width:calc(100vw - 20px); min-width:calc(100vw - 20px); }
        .dsc-meta-grid{ margin-top:12px; gap:12px; }
        .dsc-card .dsc-card-body,
        .dsc-filter-bar{ padding:12px; }
        .dsc-kv{ grid-template-columns:1fr; gap:3px 0; font-size:.84rem; }
        .dsc-kv .v{ margin-bottom:7px; }
        .dsc-kv .v::first-letter{ color:inherit; }
        .dsc-note{ font-size:.8rem; }
        .kpi-grid{ gap:10px; margin-top:12px; }
        .kpi{ padding:12px; border-radius:12px; }
        .kpi .label{ font-size:.75rem; }
        .kpi .value{ font-size:1.02rem; }
        .kpi .icon{ width:42px; height:42px; flex-basis:42px; }
        .filter-grid{ grid-template-columns:1fr; gap:11px; }
        .filter-grid .span-2{ grid-column:auto; }
        .filter-item label{ font-size:.78rem; }
        .form-control,
        .form-select,
        .select2-container--default .select2-selection--single{ min-height:44px; }
        .nav-pills{
            display:flex;
            flex-wrap:nowrap;
            overflow-x:auto;
            overflow-y:hidden;
            gap:6px;
            padding:0 0 8px;
            margin-bottom:12px!important;
            scrollbar-width:thin;
        }
        .nav-pills .nav-item{ flex:0 0 auto; }
        .nav-pills .nav-link{ white-space:nowrap; padding:10px 12px; font-size:.78rem; border-radius:999px!important; background:var(--dsc-card); border:1px solid var(--dsc-line); }
        .nav-pills .nav-link.active{ background:#eef7ff!important; border-color:#b6d7f5; }
        .nav-pills .nav-link.active::after{ display:none; }
        .dsc-scroll,
        .dsc-scroll-y{ max-height:65vh; border-radius:12px; }
        .dsc-scroll-x table,
        .dsc-scroll table{ min-width:1100px; }

        /* MOBILE: matikan freeze/sticky tabel supaya swipe lebih enak di HP. */
        .dsc-table th{ position:static!important; top:auto!important; z-index:auto!important; }
        .sticky-1,
        .sticky-2,
        thead .sticky-1,
        thead .sticky-2{
            position:static!important;
            left:auto!important;
            right:auto!important;
            z-index:auto!important;
            box-shadow:none!important;
        }
        .w-no{ width:48px; min-width:48px; }
        .w-name{ width:170px; min-width:170px; max-width:170px; }
        .w-sat{ width:66px; min-width:66px; }
        .w-num{ width:104px; min-width:104px; }
        .w-wide{ width:220px; min-width:220px; }
        .dsc-table th,
        .dsc-table td{ padding:8px 8px; font-size:.74rem; }
        .dsc-table th{ font-size:.64rem; }
        .dsc-table td.item{ max-width:170px; overflow:hidden; text-overflow:ellipsis; }
        .modal .modal-dialog{ width:calc(100vw - 16px); margin:.5rem auto; }
        .modal .modal-body{ padding:12px; }
        .row.g-3 > [class*="col-"]{ min-width:0; }
    }

    @media (max-width:420px){
        .dsc-shell{ padding-left:6px; padding-right:6px; }
        .dsc-toolbar{ grid-template-columns:1fr; }
        .dsc-title h4{ max-width:calc(100vw - 64px); }
        .dsc-card .dsc-card-body,
        .dsc-filter-bar,
        .soft-alert{ padding:10px; }
        .kpi{ padding:10px; }
        .kpi .icon{ width:38px; height:38px; flex-basis:38px; }
        .dsc-scroll,
        .dsc-scroll-y{ max-height:62vh; }
    }



    /* ==========================================================
       PATCH FREEZE TABLE TOP + LEFT
       Desktop/tablet:
       - Header tabel freeze saat scroll vertical.
       - Kolom NO dan NAMA BARANG freeze saat scroll horizontal.
       - Berlaku untuk REKAP, PERIODE DSC, DETAIL SHIFT, ADJUSTMENT, IMPORT.
       Mobile:
       - Freeze tetap aktif tapi ukuran kolom kiri diperkecil agar tidak menutup kolom lain.
       ========================================================== */

    .dsc-scroll,
    .dsc-scroll-y,
    .dsc-scroll-x{
        position: relative;
    }

    .dsc-scroll,
    .dsc-scroll-y{
        overflow: auto !important;
        max-height: 70vh;
    }

    .dsc-scroll-x{
        overflow: visible !important;
        min-width: max-content;
    }

    .dsc-scroll-x > table,
    .dsc-scroll > table{
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .dsc-table thead th{
        position: sticky !important;
        top: 0 !important;
        z-index: 30 !important;
        background: var(--dsc-soft) !important;
        box-shadow: 0 1px 0 var(--dsc-line);
    }

    .dsc-table .sticky-1,
    .dsc-table .sticky-2{
        position: sticky !important;
        background: var(--dsc-card) !important;
    }

    .dsc-table .sticky-1{
        left: 0 !important;
        z-index: 25 !important;
    }

    .dsc-table .sticky-2{
        left: 62px !important;
        z-index: 25 !important;
        box-shadow: 10px 0 12px rgba(15,23,42,.06) !important;
    }

    .dsc-table thead .sticky-1{
        z-index: 45 !important;
        background: var(--dsc-soft) !important;
    }

    .dsc-table thead .sticky-2{
        z-index: 45 !important;
        background: var(--dsc-soft) !important;
    }

    .dsc-table tbody tr:hover .sticky-1,
    .dsc-table tbody tr:hover .sticky-2{
        background: #f2f8fd !important;
    }

    /* Khusus tabel PERIODE: kolom TANGGAL ada di antara NO dan NAMA BARANG. */
    #dscTablePeriode th:nth-child(2),
    #dscTablePeriode td:nth-child(2){
        position: sticky !important;
        left: 62px !important;
        z-index: 24 !important;
        background: var(--dsc-card) !important;
        box-shadow: 10px 0 12px rgba(15,23,42,.04) !important;
    }

    #dscTablePeriode thead th:nth-child(2){
        z-index: 44 !important;
        background: var(--dsc-soft) !important;
    }

    #dscTablePeriode .sticky-2{
        left: 186px !important;
    }

    #dscTablePeriode thead .sticky-2{
        left: 186px !important;
    }

    #dscTablePeriodeSummary thead th,
    #dscTableOmset thead th{
        position: sticky !important;
        top: 0 !important;
        z-index: 30 !important;
    }

    @media (max-width: 768px){
        .dsc-scroll,
        .dsc-scroll-y{
            max-height: 65vh;
            overflow: auto !important;
        }

        .dsc-scroll-x{
            overflow: visible !important;
            min-width: max-content;
        }

        /* Override rule lama yang mematikan sticky di mobile. */
        .dsc-table th{
            position: sticky !important;
            top: 0 !important;
            z-index: 30 !important;
        }

        .sticky-1,
        .sticky-2,
        thead .sticky-1,
        thead .sticky-2{
            position: sticky !important;
            z-index: 25 !important;
        }

        .w-no{
            width: 46px !important;
            min-width: 46px !important;
            max-width: 46px !important;
        }

        .w-name{
            width: 155px !important;
            min-width: 155px !important;
            max-width: 155px !important;
        }

        .dsc-table .sticky-1{
            left: 0 !important;
        }

        .dsc-table .sticky-2{
            left: 46px !important;
            max-width: 155px !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
        }

        .dsc-table thead .sticky-1,
        .dsc-table thead .sticky-2{
            z-index: 45 !important;
            background: var(--dsc-soft) !important;
        }

        #dscTablePeriode th:nth-child(2),
        #dscTablePeriode td:nth-child(2){
            left: 46px !important;
            width: 104px !important;
            min-width: 104px !important;
            max-width: 104px !important;
            position: sticky !important;
        }

        #dscTablePeriode .sticky-2{
            left: 150px !important;
        }
    }


    /* ==========================================================
       FINAL PERFORMANCE PATCH - STICKY OPTIMIZED DSC
       Tujuan:
       - Sticky tetap dipakai.
       - Desktop/tablet: header + NO + NAMA BARANG tetap freeze.
       - Mobile: freeze hanya NAMA BARANG agar swipe lebih ringan.
       - Box-shadow besar diganti garis tipis.
       - Tidak mengubah rumus, data, route, form, modal, atau JavaScript.
       ========================================================== */

    .dsc-scroll,
    .dsc-scroll-y,
    .dsc-scroll-x{
        position:relative;
        -webkit-overflow-scrolling:touch!important;
        overscroll-behavior:contain!important;
    }

    .dsc-scroll,
    .dsc-scroll-y{
        overflow:auto!important;
        max-height:70vh;
        contain:layout paint;
    }

    .dsc-scroll-x{
        overflow:visible!important;
        min-width:max-content;
    }

    .dsc-card,
    .kpi,
    .soft-alert,
    .dsc-filter-bar,
    .dsc-scroll,
    .dsc-scroll-y,
    .modal .modal-content,
    .omset-photo{
        box-shadow:0 1px 2px rgba(15,23,42,.04)!important;
    }

    .dsc-table{
        border-collapse:separate!important;
        border-spacing:0!important;
    }

    .dsc-table thead th{
        position:sticky!important;
        top:0!important;
        z-index:30!important;
        background:var(--dsc-soft)!important;
        box-shadow:0 1px 0 var(--dsc-line-soft)!important;
    }

    .dsc-table .sticky-1,
    .dsc-table .sticky-2{
        position:sticky!important;
        background:var(--dsc-card)!important;
    }

    .dsc-table .sticky-1{
        left:0!important;
        z-index:25!important;
        box-shadow:none!important;
    }

    .dsc-table .sticky-2{
        left:62px!important;
        z-index:25!important;
        box-shadow:1px 0 0 var(--dsc-line-soft)!important;
    }

    .dsc-table thead .sticky-1,
    .dsc-table thead .sticky-2{
        z-index:45!important;
        background:var(--dsc-soft)!important;
    }

    .dsc-table tbody tr:hover td:not(.sticky-1):not(.sticky-2){
        background:#f2f8fd!important;
    }

    .dsc-table tbody tr:hover .sticky-1,
    .dsc-table tbody tr:hover .sticky-2{
        background:var(--dsc-card)!important;
    }

    /* PERIODE DSC: tanggal ikut freeze di desktop/tablet karena posisinya di antara NO dan NAMA */
    #dscTablePeriode th:nth-child(2),
    #dscTablePeriode td:nth-child(2){
        position:sticky!important;
        left:62px!important;
        z-index:24!important;
        background:var(--dsc-card)!important;
        box-shadow:1px 0 0 var(--dsc-line-soft)!important;
    }

    #dscTablePeriode thead th:nth-child(2){
        z-index:44!important;
        background:var(--dsc-soft)!important;
    }

    #dscTablePeriode .sticky-2{
        left:186px!important;
    }

    #dscTablePeriode thead .sticky-2{
        left:186px!important;
    }

    #dscTablePeriodeSummary thead th,
    #dscTableOmset thead th{
        position:sticky!important;
        top:0!important;
        z-index:30!important;
        background:var(--dsc-soft)!important;
        box-shadow:0 1px 0 var(--dsc-line-soft)!important;
    }

    @media (max-width:768px){
        .dsc-scroll,
        .dsc-scroll-y{
            max-height:65vh;
            overflow:auto!important;
        }

        .dsc-scroll-x{
            overflow:visible!important;
            min-width:max-content;
        }

        .dsc-table th{
            position:sticky!important;
            top:0!important;
            z-index:30!important;
        }

        .w-no{
            width:46px!important;
            min-width:46px!important;
            max-width:46px!important;
        }

        .w-name{
            width:160px!important;
            min-width:160px!important;
            max-width:160px!important;
        }

        /* Mobile: NO dibuat biasa, NAMA BARANG saja yang freeze */
        .dsc-table .sticky-1,
        .dsc-table thead .sticky-1{
            position:static!important;
            left:auto!important;
            z-index:auto!important;
            box-shadow:none!important;
            background:inherit!important;
        }

        .dsc-table .sticky-2{
            position:sticky!important;
            left:0!important;
            z-index:28!important;
            width:160px!important;
            min-width:160px!important;
            max-width:160px!important;
            overflow:hidden!important;
            text-overflow:ellipsis!important;
            white-space:nowrap!important;
            background:var(--dsc-card)!important;
            box-shadow:1px 0 0 var(--dsc-line-soft)!important;
        }

        .dsc-table thead .sticky-2{
            z-index:45!important;
            background:var(--dsc-soft)!important;
        }

        /* Mobile periode: tanggal tidak freeze, NAMA BARANG saja freeze */
        #dscTablePeriode th:nth-child(2),
        #dscTablePeriode td:nth-child(2){
            position:static!important;
            left:auto!important;
            z-index:auto!important;
            width:104px!important;
            min-width:104px!important;
            max-width:104px!important;
            background:inherit!important;
            box-shadow:none!important;
        }

        #dscTablePeriode .sticky-2{
            left:0!important;
        }
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
                                            @forelse(collect($missingOutlets)->take(50) as $o)
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
                                            <span>DATA RINGKAS H-1. @if(($missingCount ?? 0) > 50) Ditampilkan 50 dari {{ $missingCount }}. @endif</span>
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

                            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalRapelUangPlus"
                                @if (!$hasRequiredFilter) disabled @endif>
                                <i class="bi bi-cash-stack me-1"></i> RAPEL UANG PLUS
                            </button>

                            <a class="btn btn-sm btn-success"
                                href="{{ route('master.dsc.export', [
                                    'outlet_id' => $outletId,
                                    'tanggal' => $today,
                                    'start_date' => $exportStartDate,
                                    'end_date' => $exportEndDate,
                                    'shift_filter' => $shiftFilter,
                                ]) }}"
                                @if (!$hasRequiredFilter) style="pointer-events:none;opacity:.55;" aria-disabled="true" @endif>
                                <i class="bi bi-file-earmark-excel me-1"></i> EXPORT PERIODE AKHIR
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
                                    <div class="k"><i class="bi bi-calendar2-week me-1"></i>Periode</div>
                                    <div class="v">: {{ \Carbon\Carbon::parse($periodStartDate)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($periodEndDate)->format('d-m-Y') }}</div>
                                    <div class="k"><i class="bi bi-calendar-range me-1"></i>Periode DSC</div>
                                    <div class="v">: {{ $periodLabel }}</div>
                                    <div class="k"><i class="bi bi-journal-text me-1"></i>Catatan</div>
                                    <div class="v">: Monitoring + Koreksi SPV/Territorial Manager</div>
                                </div>

                                <div class="mt-3 dsc-note">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Gunakan filter Outlet, Periode Awal, dan Periode Akhir. REKAP mengikuti akumulasi periode, dengan rumus stok lama tetap dipertahankan.
                                </div>
                            </div>
                        </div>

                        <div class="dsc-card">
                            <div class="dsc-card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="text-muted fw-bold">Periode Akhir</div>
                                    <div class="fw-bold" style="font-size:1.25rem;">
                                        {{ (int) \Carbon\Carbon::parse($today)->format('d') }}
                                    </div>
                                </div>
                                <div class="dsc-help mt-2">Data harian mengikuti periode akhir.</div>
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

                                <input type="hidden" name="tanggal" value="{{ $periodEndDate }}">
                                <input type="hidden" name="load_period" value="1">

                                <div class="filter-item">
                                    <label><i class="bi bi-calendar-range me-1"></i>PERIODE AWAL</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $periodStartDate }}" required>
                                </div>

                                <div class="filter-item">
                                    <label><i class="bi bi-calendar-range-fill me-1"></i>PERIODE AKHIR</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $periodEndDate }}" required>
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
                            <button class="nav-link {{ request('active_tab') === 'periode' ? '' : 'active' }}" data-bs-toggle="pill" data-bs-target="#tab-rekap" type="button">
                                <i class="bi bi-table me-1"></i > REKAP
                            </button>
                        </li>

                        <li class="nav-item">
                            <button class="nav-link {{ request('active_tab') === 'periode' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#tab-periode" type="button">
                                <i class="bi bi-calendar-range me-1"></i > PERIODE DSC
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
                        <div class="tab-pane fade {{ request('active_tab') === 'periode' ? '' : 'show active' }}" id="tab-rekap">
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
                                                <td class="num">{{ number_format($r['adj'] ?? $r['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td>
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
                                                    Data dikosongkan. Pilih outlet dan periode lalu klik <b>TERAPKAN</b>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                              </div>
                            </div>
                        </div>


                        {{-- PERIODE DSC --}}
                        <div class="tab-pane fade {{ request('active_tab') === 'periode' ? 'show active' : '' }}" id="tab-periode">
                            @if(!$isPeriodLoaded)
                                <div class="soft-alert warning">
                                    <div class="title"><i class="bi bi-lightning-charge me-1"></i> Mode ringan aktif</div>
                                    <p class="desc mb-2">Detail Periode DSC belum dimuat supaya halaman awal tetap ringan. Klik tombol di bawah hanya saat perlu melihat data lintas tanggal.</p>
                                    <a class="btn btn-primary" href="{{ $periodLoadUrl }}">
                                        <i class="bi bi-cloud-download me-1"></i> Muat Periode DSC
                                    </a>
                                </div>
                            @else

                            <div class="dsc-scroll mb-3">
                                <table id="dscTablePeriodeSummary" class="table dsc-table" style="min-width:1150px;">
                                    <thead>
                                        <tr>
                                            <th class="w-num">TANGGAL</th>
                                            <th class="w-num">STATUS DSC</th>
                                            <th class="w-num">ITEM TAMPIL</th>
                                            <th class="w-num">SALES S1</th>
                                            <th class="w-num">SALES S2</th>
                                            <th class="w-num">TOTAL SALES</th>
                                            <th class="w-num">UANG PLUS</th>
                                            <th class="w-num">USED MINUS</th>
                                            <th class="w-num">USED PLUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($periodSummary as $s)
                                            <tr>
                                                <td class="center">{{ \Carbon\Carbon::parse($s['tanggal'])->format('d-m-Y') }}</td>
                                                <td class="center">
                                                    @if (!empty($s['has_dsc']))
                                                        <span class="badge text-bg-success">ADA DATA</span>
                                                    @else
                                                        <span class="badge text-bg-warning">BELUM ADA</span>
                                                    @endif
                                                </td>
                                                <td class="num">{{ number_format($s['row_count'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">Rp {{ number_format($s['sales_s1'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">Rp {{ number_format($s['sales_s2'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num fw-bold">Rp {{ number_format($s['sales_total'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">Rp {{ number_format($s['uang_plus'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num {{ ($s['used_minus_count'] ?? 0) > 0 ? 'cell-negative' : '' }}">{{ number_format($s['used_minus_count'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($s['used_plus_count'] ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">
                                                    Data periode dikosongkan. Pilih outlet dan periode lalu klik <b>TERAPKAN</b>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="dsc-scroll-y">
                              <div class="dsc-scroll-x">
                                <table id="dscTablePeriode" class="table dsc-table">
                                    <thead>
                                        <tr>
                                            <th class="w-no sticky-1">NO</th>
                                            <th class="w-num">TANGGAL</th>
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
                                        @forelse($periodRows as $r)
                                            <tr>
                                                <td class="center sticky-1">{{ $r['no'] }}</td>
                                                <td class="center">{{ \Carbon\Carbon::parse($r['tanggal'])->format('d-m-Y') }}</td>
                                                <td class="sticky-2 item">{{ $r['nama'] }}</td>
                                                <td class="center">
                                                    @if ($shiftFilter === 'all')
                                                        <div class="d-flex flex-column gap-1 align-items-center">
                                                            <span class="badge {{ ($r['source_s1'] ?? null) === 'final' ? 'text-bg-success' : (($r['source_s1'] ?? null) === 'draft' ? 'text-bg-warning' : 'text-bg-secondary') }}">S1 {{ strtoupper($r['source_s1'] ?? '-') }}</span>
                                                            <span class="badge {{ ($r['source_s2'] ?? null) === 'final' ? 'text-bg-success' : (($r['source_s2'] ?? null) === 'draft' ? 'text-bg-warning' : 'text-bg-secondary') }}">S2 {{ strtoupper($r['source_s2'] ?? '-') }}</span>
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
                                                <td class="num">{{ number_format($r['adj'] ?? $r['adj'] ?? $r['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num fw-bold">{{ number_format($r['total'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num">{{ number_format($r['ending_stock'] ?? 0, 0, ',', '.') }}</td>
                                                <td class="num {{ ($r['used'] ?? 0) < 0 ? 'cell-negative' : '' }}">{{ number_format($r['used'] ?? 0, 0, ',', '.') }}</td>
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
                                                <td colspan="22" class="text-center text-muted py-4">
                                                    Data periode dikosongkan. Pilih outlet dan periode lalu klik <b>TERAPKAN</b>.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                              </div>
                            </div>
                        </div>
                            @endif

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
                                                <td class="num">{{ number_format($r['adj'] ?? $r['ADJUSTMENT'] ?? 0, 0, ',', '.') }}</td>
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
                                                <td colspan="17" class="text-center text-muted py-4">Data dikosongkan. Pilih outlet dan periode lalu klik <b>TERAPKAN</b>.</td>
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
                                          $ADJUSTMENT  = (float) ($r['adj'] ?? $r['ADJUSTMENT'] ?? 0);
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
                                            Data dikosongkan. Pilih outlet dan periode lalu klik <b>TERAPKAN</b>.
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





{{-- MODAL RAPEL / REVISI UANG PLUS --}}
<div class="modal fade" id="modalRapelUangPlus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-cash-stack me-1"></i> Rapel / Revisi Uang Plus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="soft-alert primary">
                    <div class="title"><i class="bi bi-info-circle me-1"></i> Catatan Aman</div>
                    <p class="desc mb-0">Fitur ini hanya mengubah <b>uang_plus</b> dan menambah <b>keterangan</b>. Purchase, mutasi, adjustment, ending, used, dan rumus stok tidak diubah.</p>
                </div>

                <div class="soft-alert warning" id="rapelExistingBox">
                    <div class="title"><i class="bi bi-database-check me-1"></i> Data Saat Ini</div>
                    <p class="desc mb-1">
                        Uang Plus: <b id="rapelCurrentUang">-</b>
                        <span class="ms-2">Status: <b id="rapelCurrentSource">-</b></span>
                    </p>
                    <p class="desc mb-0">Keterangan: <span id="rapelCurrentKet">-</span></p>
                    <button type="button" class="btn btn-sm btn-ghost mt-2" id="btnUseCurrentRapelValue">
                        <i class="bi bi-arrow-down-circle me-1"></i> Pakai Nominal Saat Ini
                    </button>
                </div>

                <form id="formRapelUangPlus">
                    <input type="hidden" name="outlet_id" value="{{ $rapelOutletId }}">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Input / Periode Akhir</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ $today }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Shift</label>
                            <select name="shift" id="rapel_shift" class="form-select" required>
                                <option value="1">Shift 1</option>
                                <option value="2">Shift 2</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mode</label>
                            <select name="mode" id="rapel_mode" class="form-select" required>
                                <option value="set" selected>Set / Revisi Total</option>
                                <option value="add">Tambah / Rapel</option>
                            </select>
                            <div class="dsc-help mt-1">Untuk koreksi dari 15.000 ke 20.000 gunakan Set/Revisi Total.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Target Uang Plus</label>
                            <select name="apply_all_bahan" id="rapel_apply_all_bahan" class="form-select select2-rapel" required>
                                <option value="1" selected>Semua Bahan</option>
                                <option value="0">Per Bahan</option>
                            </select>
                            <div class="dsc-help mt-1">Pilih Semua Bahan jika uang plus berlaku global untuk shift tersebut.</div>
                        </div>

                        <div class="col-md-4" id="rapelBahanWrap">
                            <label class="form-label">Bahan</label>
                            <select name="bahan_id" id="rapel_bahan_id" class="form-select select2-rapel">
                                <option value="">Pilih bahan</option>
                                @php
                                    $rapelOptions = collect($rapelBahanRows ?? [])->isNotEmpty()
                                        ? collect($rapelBahanRows ?? [])
                                        : collect($rekapRows ?? []);
                                @endphp
                                @foreach($rapelOptions as $r)
                                    @php
                                        $bid = data_get($r, 'bahan_id') ?: data_get($r, 'id');
                                        $nama = data_get($r, 'nama') ?: data_get($r, 'nama_bahan');
                                        $sat = data_get($r, 'sat') ?: data_get($r, 'satuan') ?: '-';
                                    @endphp
                                    @if(!empty($bid))
                                        <option value="{{ $bid }}">{{ $nama }} ({{ $sat }})</option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="dsc-help mt-1">Diaktifkan hanya jika target Per Bahan.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nominal Uang Plus</label>
                            <input type="number" name="uang_plus" id="rapel_uang_plus" class="form-control" min="0" step="1" placeholder="0" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" id="rapel_keterangan" class="form-control" maxlength="255" value="RAPEL UANG PLUS" placeholder="Contoh: RAPEL UANG PLUS AYAM 12-06-2026">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnSubmitRapelUangPlus" @if(!$hasRequiredFilter || !$rapelOutletId) disabled @endif>
                    <i class="bi bi-save me-1"></i> Simpan Rapel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if ($canADJUSTMENT)
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
@endif


<script>
    if (typeof Swal === 'undefined') {
        window.Swal = {
            fire: (o) => alert(o.title || o.text || 'Info'),
            showLoading: () => {},
            close: () => {}
        };
    }

    const URL_DSC_UANG_PLUS_RAPEL = `{{ url('/inventory/dsc/uang-plus/rapel') }}`;
    const RAPEL_EXISTING_MAP = @json($rapelExistingMap ?? []);

    function rapelFormatNumber(n) {
        n = Number(n || 0);
        return n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function isRapelAllBahan() {
        return ($('#rapel_apply_all_bahan').val() || '1').toString() === '1';
    }

    function getRapelCurrentData() {
        const shift = ($('#rapel_shift').val() || '').toString();
        const bahanId = isRapelAllBahan() ? '__all__' : ($('#rapel_bahan_id').val() || '').toString();
        return RAPEL_EXISTING_MAP?.[shift]?.[bahanId] || null;
    }

    function refreshRapelTargetMode() {
        const allBahan = isRapelAllBahan();
        $('#rapel_bahan_id')
            .prop('disabled', allBahan)
            .prop('required', !allBahan);

        $('#rapelBahanWrap').toggleClass('opacity-50', allBahan);

        if (allBahan) {
            $('#rapel_bahan_id').val('').trigger('change.select2');
        }
    }

    function refreshRapelExistingBox({ forceFill = false } = {}) {
        refreshRapelTargetMode();
        const data = getRapelCurrentData();
        const $uang = $('#rapel_uang_plus');
        const mode = ($('#rapel_mode').val() || 'add').toString();

        if (!data) {
            $('#rapelCurrentUang').text('-');
            $('#rapelCurrentSource').text(isRapelAllBahan() ? 'Belum ada data DSC pada shift ini' : 'Belum ada data DSC');
            $('#rapelCurrentKet').text('-');
            $('#btnUseCurrentRapelValue').prop('disabled', true);
            if (mode === 'set' && forceFill) $uang.val('');
            return;
        }

        const currentUang = Number(data.uang_plus || 0);
        $('#rapelCurrentUang').text('Rp ' + rapelFormatNumber(currentUang));
        $('#rapelCurrentSource').text((data.source || '-').toUpperCase());
        $('#rapelCurrentKet').text(data.keterangan || '-');
        $('#btnUseCurrentRapelValue').prop('disabled', false);

        if (mode === 'set' && (forceFill || !$uang.val())) {
            $uang.val(currentUang);
        }

        if (!$('#rapel_keterangan').val()) {
            $('#rapel_keterangan').val('REVISI UANG PLUS');
        }
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
                placeholder: 'Ketik minimal 2 huruf outlet...',
                allowClear: true,
                minimumInputLength: 2,
                templateResult: item => cleanOutletText(item.text),
                templateSelection: item => cleanOutletText(item.text),
                ajax: {
                    url: `{{ route('outlets') }}`,
                    dataType: 'json',
                    delay: 350,
                    data: params => ({ q: params.term || '', page: params.page || 1, limit: 25 }),
                    processResults: (data, params) => {
                        params.page = params.page || 1;
                        const rows = data.results || data.items || [];
                        return {
                            results: rows.map(item => ({
                                ...item,
                                text: cleanOutletText(item.text || item.nama_outlet || item.label || '')
                            })),
                            pagination: { more: !!(data.pagination && data.pagination.more) }
                        };
                    },
                    cache: true
                }
            });


            $('#modalRapelUangPlus .select2-rapel').select2({
                width: '100%',
                dropdownParent: $('#modalRapelUangPlus'),
                placeholder: 'Pilih data'
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


        $('#modalRapelUangPlus').on('shown.bs.modal', function() {
            refreshRapelTargetMode();
            refreshRapelExistingBox({ forceFill: true });
        });

        $('#rapel_shift, #rapel_bahan_id, #rapel_mode, #rapel_apply_all_bahan').on('change', function() {
            refreshRapelExistingBox({ forceFill: true });
        });

        $('#btnUseCurrentRapelValue').on('click', function() {
            const data = getRapelCurrentData();
            if (!data) return;
            $('#rapel_mode').val('set');
            $('#rapel_uang_plus').val(Number(data.uang_plus || 0));
            $('#rapel_keterangan').val('REVISI UANG PLUS');
            refreshRapelExistingBox();
        });



        $('#btnSubmitRapelUangPlus').off('click').on('click', async function() {
            const form = document.getElementById('formRapelUangPlus');
            if (!form) return;

            refreshRapelTargetMode();

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (!isRapelAllBahan() && !($('#rapel_bahan_id').val() || '').toString()) {
                Swal.fire({ icon: 'warning', title: 'Bahan wajib dipilih', text: 'Pilih bahan jika targetnya Per Bahan.' });
                return;
            }

            const fd = new FormData(form);
            const payload = Object.fromEntries(fd.entries());
            payload.apply_all_bahan = isRapelAllBahan() ? 1 : 0;
            if (isRapelAllBahan()) {
                delete payload.bahan_id;
            }

            try {
                Swal.fire({
                    title: 'Menyimpan rapel uang plus...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const meta = document.querySelector('meta[name="csrf-token"]');
                const res = await fetch(URL_DSC_UANG_PLUS_RAPEL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': meta ? meta.content : ''
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json().catch(() => ({}));
                if (!res.ok || !json.ok) {
                    throw new Error(json.message || 'Gagal menyimpan rapel uang plus.');
                }

                Swal.close();
                await Swal.fire({ icon: 'success', title: 'Berhasil', text: json.message || 'Rapel uang plus berhasil disimpan.', timer: 1300, showConfirmButton: false });
                location.reload();
            } catch (err) {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.message });
            }
        });

        let searchBarangTimer = null;
        $('#searchBarang').on('input', function() {
            clearTimeout(searchBarangTimer);
            const input = this;
            searchBarangTimer = setTimeout(function() {
            const q = (input.value || '').toLowerCase();

            $('#dscTableRekap tbody tr').each(function() {
                const name = ($(this).find('td').eq(1).text() || '').toLowerCase();
                $(this).toggle(name.includes(q));
            });

            $('#dscTableShift tbody tr').each(function() {
                const name = ($(this).find('td').eq(1).text() || '').toLowerCase();
                $(this).toggle(name.includes(q));
            });

            $('#dscTablePeriode tbody tr').each(function() {
                const name = ($(this).find('td').eq(2).text() || '').toLowerCase();
                $(this).toggle(name.includes(q));
            });


            if ($('#dscTableOmset').length) {
                $('#dscTableOmset tbody tr').each(function() {
                    const label = ($(this).find('td').eq(0).text() || '').toLowerCase();
                    $(this).toggle(label.includes(q));
                });
            }
            }, 300);
        });
    });
    // ADJUSTMENT autosave/import handlers are defined once in the script above.
    // This lower script only initializes Select2/search to avoid duplicate global const/event binding.
</script>

@endpush

@include('Temp.Investor.footer')
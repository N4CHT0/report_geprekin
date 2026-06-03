{{-- resources/views/Investor/dashboard.blade.php --}}
@section('title', 'Dashboard Penjualan')
@section('breadcrumb', 'Dashboard / Sales')

@include('Temp.Investor.header')

<style>
    .page-stack { display: flex; flex-direction: column; gap: 20px; }

    .page-head {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        padding: 22px;
        border: 1px solid rgba(37, 99, 235, .16);
        border-radius: 18px;
        background:
            radial-gradient(circle at top right, rgba(37, 99, 235, .16), transparent 34%),
            linear-gradient(135deg, #ffffff 0%, #f8fbff 45%, #eef6ff 100%);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
    }

    .page-head::before {
        content: "";
        position: absolute;
        inset: auto -60px -120px auto;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .10);
        filter: blur(8px);
    }

    .page-head > * { position: relative; z-index: 1; }

    .page-head h2 {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: -0.04em;
        color: #0f172a;
    }

    .page-head .meta {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        margin-bottom: 8px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .10);
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .filter-grid { display: grid; grid-template-columns: 1fr 1fr 1.65fr auto; gap: 14px; align-items: end; }

    #filterCard {
        border: 1px solid rgba(15, 23, 42, .10);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 16px 38px rgba(15, 23, 42, .06);
    }

    #filterCard .c-card-header {
        background: linear-gradient(90deg, #ffffff, #f8fbff);
        padding: 15px 18px;
    }

    #filterCard .c-card-body { padding: 18px; }

    .f-input, .f-select {
        height: 44px;
        border-radius: 12px;
        border: 1px solid rgba(100, 116, 139, .25);
        background: #fff;
        transition: .18s ease;
    }

    .f-input:focus, .f-select:focus {
        transform: translateY(-1px);
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
    }

    .btn {
        border-radius: 12px;
        transition: .18s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        box-shadow: 0 12px 24px rgba(37, 99, 235, .22);
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
    }

    .kpi-card {
        position: relative;
        overflow: hidden;
        padding: 18px;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 18px;
        box-shadow: 0 16px 35px rgba(15, 23, 42, .06);
        transition: .2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 22px 50px rgba(15, 23, 42, .10);
    }

    .kpi-card::after {
        content: "";
        position: absolute;
        inset: auto -45px -45px auto;
        width: 120px;
        height: 120px;
        border-radius: 999px;
        opacity: .75;
    }

    .kpi-card:nth-child(1)::after { background: #dbeafe; }
    .kpi-card:nth-child(2)::after { background: #d1fae5; }
    .kpi-card:nth-child(3)::after { background: #fef3c7; }
    .kpi-card:nth-child(4)::after { background: #fee2e2; }
    .kpi-card:nth-child(5)::after { background: #cffafe; }

    .kpi-row { position: relative; z-index: 1; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; }

    .kpi-icon {
        width: 48px;
        height: 48px;
        display:flex;
        align-items:center;
        justify-content:center;
        border-radius: 16px;
        font-size: 18px;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.5);
    }

    .stat-lbl {
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
    }

    .stat-val {
        margin-top: 7px;
        font-size: 26px;
        font-weight: 950;
        color: #0f172a;
        letter-spacing: -0.045em;
        line-height: 1.05;
    }

    .c-card {
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 18px;
        background: rgba(255,255,255,.96);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .055);
    }

    .c-card-header {
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15, 23, 42, .075);
        background: linear-gradient(90deg, #ffffff, #fafcff);
        border-radius: 18px 18px 0 0;
    }

    .c-card-body { padding: 18px; }

    .metric-grid {
        display:grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }

    .metric-grid.payment { grid-template-columns: repeat(4, minmax(0, 1fr)); }

    .metric-item {
        position: relative;
        overflow: hidden;
        min-width: 0;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        padding: 14px;
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 16px;
        background: linear-gradient(135deg, #fff, #f8fafc);
        transition: .18s ease;
    }

    .metric-item:hover {
        transform: translateY(-2px);
        border-color: rgba(37, 99, 235, .25);
        box-shadow: 0 16px 28px rgba(15, 23, 42, .07);
    }

    .metric-icon {
        width: 40px;
        height: 40px;
        border-radius: 14px;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
        font-size: 16px;
    }

    .metric-val {
        font-size: 15px;
        font-weight: 950;
        color:#0f172a;
        white-space:nowrap;
        letter-spacing: -.02em;
    }

    .metric-label {
        margin-top: 2px;
        font-size:12px;
        color:#64748b;
        white-space:nowrap;
        overflow:hidden;
        text-overflow:ellipsis;
        text-align:right;
        font-weight: 650;
    }

    .section-title {
        display:flex;
        align-items:center;
        gap:8px;
        margin: 0 0 12px;
        font-size:15px;
        font-weight:900;
        color:#0f172a;
    }

    .chart-grid {
        display:grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        align-items:start;
    }

    .chart-full { grid-column: 1 / -1; }

    .chart-wrap {
        position: relative;
        width: 100%;
        height: 320px;
        min-height: 260px;
    }

    .chart-wrap.sm { height: 300px; }
    .chart-wrap.md { height: 300px; }
    .chart-wrap.h260 { height: 340px; }
    .chart-wrap canvas { width: 100% !important; height: 100% !important; display: block; }

    .table-scroll {
        max-height: 430px;
        overflow: auto;
    }

    .c-table {
        width: 100%;
        border-collapse: collapse;
    }

    .c-table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc;
        padding: 12px 16px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
        border-bottom: 1px solid rgba(15,23,42,.10);
    }

    .c-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(15,23,42,.055);
        color: #0f172a;
    }

    .c-table tbody tr:hover td { background: #f8fbff; }

    .empty-chart {
        height:100%;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        color:#64748b;
        text-align:center;
        border-radius: 14px;
        background: linear-gradient(135deg, #f8fafc, #fff);
    }

    .badge.badge-neutral {
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding: 8px 11px;
        border: 1px solid rgba(15,23,42,.10);
        border-radius: 999px;
        background: #fff;
        color: #334155;
        font-weight: 850;
        box-shadow: 0 8px 20px rgba(15,23,42,.045);
    }

    .select2-container--default .select2-selection--single {
        height: 44px !important;
        border-radius: 12px !important;
        border-color: rgba(100, 116, 139, .25) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px !important;
        padding-left: 13px !important;
        font-weight: 600;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 8px !important;
    }

    @media (max-width: 1280px) {
        .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .metric-grid, .metric-grid.payment { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 768px) {
        .page-head { flex-direction: column; align-items: stretch; }
        .page-head h2 { font-size: 23px; }
        .kpi-grid, .chart-grid, .filter-grid, .metric-grid, .metric-grid.payment { grid-template-columns: 1fr !important; }
        .chart-full { grid-column: auto; }
        .chart-wrap, .chart-wrap.sm, .chart-wrap.md, .chart-wrap.h260 { height: 265px; }
        .stat-val { font-size: 22px; }
    }

    :root {
        --accent: #2563eb;
        --success: #059669;
        --text-muted: #64748b;
        --border-subtle: rgba(15, 23, 42, .10);
    }

    .f-label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 750;
        color: #0f172a;
    }

    .f-input,
    .f-select {
        width: 100%;
        min-width: 0;
        padding: 0 13px;
        outline: none;
        font-weight: 650;
        color: #0f172a;
    }

    .btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 42px;
        padding: 0 14px;
        border: 1px solid rgba(100, 116, 139, .25);
        background: #fff;
        color: #334155;
        font-weight: 750;
        white-space: nowrap;
    }

    .btn-ghost:hover {
        border-color: rgba(37, 99, 235, .30);
        color: #1d4ed8;
        background: #eff6ff;
    }

    #filterCard,
    .c-card,
    .kpi-card,
    .metric-item {
        min-width: 0;
    }

    .select2-container {
        max-width: 100% !important;
    }

    #filterCard .select2-container--default .select2-selection--single,
    .page-stack .select2-container--default .select2-selection--single {
        height: 44px !important;
        min-height: 44px !important;
        border-radius: 12px !important;
        border-color: rgba(100, 116, 139, .25) !important;
    }

    #filterCard .select2-container--default .select2-selection--single .select2-selection__rendered,
    .page-stack .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 44px !important;
        padding-left: 13px !important;
        padding-right: 32px !important;
        font-weight: 650;
        color: #0f172a !important;
    }

    #filterCard .select2-container--default .select2-selection--single .select2-selection__arrow,
    .page-stack .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 8px !important;
    }

    .chart-grid > .c-card,
    .chart-full,
    .table-scroll {
        min-width: 0;
    }

    @media (max-width: 991.98px) {
        .page-stack { gap: 14px; }

        .page-head {
            padding: 18px;
            border-radius: 16px;
        }

        .metric-grid[style] {
            grid-template-columns: 1fr !important;
        }

        .c-card-header {
            align-items: flex-start;
            flex-direction: column;
            gap: 10px;
        }

        .c-card-header > div:last-child,
        .c-card-header > select {
            width: 100% !important;
        }

        #filterKategori {
            width: 100% !important;
        }
    }

    @media (max-width: 575.98px) {
        .page-head {
            padding: 16px;
        }

        .page-head h2 {
            font-size: 21px;
        }

        .page-head > div:last-child {
            width: 100%;
            align-items: stretch !important;
        }

        .page-head .badge,
        .page-head .btn {
            width: 100%;
            justify-content: center;
        }

        #filterCard .c-card-header,
        #filterCard .c-card-body,
        .c-card-header,
        .c-card-body {
            padding: 14px;
        }

        .filter-grid {
            gap: 12px;
        }

        .metric-item {
            align-items: flex-start;
        }

        .metric-val {
            font-size: 14px;
            white-space: normal;
            word-break: break-word;
        }

        .metric-label {
            white-space: normal;
        }

        .chart-wrap,
        .chart-wrap.sm,
        .chart-wrap.md,
        .chart-wrap.h260 {
            height: 245px;
        }

        .table-scroll {
            max-height: 360px;
        }
    }

</style>

<div class="page-stack">
    <div class="page-head">
        <div>
            <div class="meta">Dashboard / Sales</div>
            <h2>Dashboard Penjualan</h2>
        </div>

        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <span class="badge badge-neutral">
                {{ request('tanggal_awal') ?: ($tanggalAwal ?? '-') }} / {{ request('tanggal_akhir') ?: ($tanggalAkhir ?? '-') }}
            </span>

            <button id="exportPDF" type="button" class="btn btn-ghost">
                <i class="bi bi-file-earmark-pdf"></i>
                Export PDF
            </button>
        </div>
    </div>

    {{-- FILTER --}}
    <section class="c-card" id="filterCard">
        <div class="c-card-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <i class="bi bi-funnel" style="color:var(--accent)"></i>
                <strong>Filter Data</strong>
            </div>
            <span style="font-size:12px;color:var(--text-muted);font-weight:600;">Gunakan filter untuk menampilkan data</span>
        </div>

        <div class="c-card-body">
            <form id="filterForm" method="GET" action="{{ route('investor.sales.dashboard') }}">
                <div class="filter-grid">
                    <div>
                        <label class="f-label">Tanggal Awal</label>
                        <input type="text" id="tanggal_awal" name="tanggal_awal" class="f-input"
                               value="{{ request('tanggal_awal') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>

                    <div>
                        <label class="f-label">Tanggal Akhir</label>
                        <input type="text" id="tanggal_akhir" name="tanggal_akhir" class="f-input"
                               value="{{ request('tanggal_akhir') }}" placeholder="YYYY-MM-DD" autocomplete="off">
                    </div>

                    <div>
                        <label class="f-label">Outlet</label>
                        <select id="outlet" name="outlet" class="f-select form-select js-select2" data-placeholder="-- Semua Outlet --">
                            <option value="">-- Semua Outlet --</option>
                            @foreach ($outlets as $o)
                                <option value="{{ $o->id }}" {{ (string) request('outlet') === (string) $o->id ? 'selected' : '' }}>
                                    {{ $o->nama_outlet_display }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <button id="btnTampilkan" type="submit" class="btn btn-primary" style="width:100%; min-width:150px;">
                            <i class="bi bi-search"></i>
                            Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section id="dashboardArea" style="position:relative;">
        <div id="dashboardContainer">
            <div id="dashboardWrapper" class="page-stack">
                {{-- KPI --}}
                <div class="kpi-grid">
                    <div class="c-card kpi-card">
                        <div class="kpi-row">
                            <div>
                                <div class="stat-lbl">Total Omset</div>
                                <div class="stat-val">Rp {{ number_format($totalOmset ?? 0) }}</div>
                            </div>
                            <div class="kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="bi bi-cash-coin"></i></div>
                        </div>
                    </div>

                    <div class="c-card kpi-card">
                        <div class="kpi-row">
                            <div>
                                <div class="stat-lbl">Total CU</div>
                                <div class="stat-val">{{ number_format($totalCU ?? 0) }}</div>
                            </div>
                            <div class="kpi-icon" style="background:#ecfdf5;color:#059669;"><i class="bi bi-people-fill"></i></div>
                        </div>
                    </div>

                    <div class="c-card kpi-card">
                        <div class="kpi-row">
                            <div>
                                <div class="stat-lbl">Outlet Aktif Saat Ini</div>
                                <div class="stat-val">{{ number_format($totalOutletAktif ?? 0) }}</div>
                            </div>
                            <div class="kpi-icon" style="background:#ecfeff;color:#0891b2;"><i class="bi bi-shop-window"></i></div>
                        </div>
                    </div>

                    <div class="c-card kpi-card">
                        <div class="kpi-row">
                            <div>
                                <div class="stat-lbl">AVG Sales / Hari</div>
                                <div class="stat-val">Rp {{ number_format($averageSales ?? 0) }}</div>
                            </div>
                            <div class="kpi-icon" style="background:#fffbeb;color:#d97706;"><i class="bi bi-graph-up-arrow"></i></div>
                        </div>
                    </div>

                    <div class="c-card kpi-card">
                        <div class="kpi-row">
                            <div>
                                <div class="stat-lbl">Non Sales Transaction</div>
                                <div class="stat-val">Rp {{ number_format($nonSalesTransaction ?? 0) }}</div>
                            </div>
                            <div class="kpi-icon" style="background:#fef2f2;color:#dc2626;"><i class="bi bi-receipt-cutoff"></i></div>
                        </div>
                    </div>
                </div>

                {{-- RINGKASAN PLATFORM --}}
                <div class="c-card">
                    <div class="c-card-header">
                        <div>
                            <strong>Ringkasan Channel & Pembayaran</strong>
                            <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-top:2px;">Platform online, jenis pemesanan, dan metode pembayaran.</div>
                        </div>

                        <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
                            <span class="badge badge-neutral">Total Omset: Rp {{ number_format($totalOmset ?? 0) }}</span>
                            <span class="badge badge-neutral">Avg / Hari: Rp {{ number_format($averageSales ?? 0) }}</span>
                        </div>
                    </div>

                    <div class="c-card-body" style="display:flex; flex-direction:column; gap:18px;">
                        @php
                            $platformOnline = [
                                'shopeefood'  => ['label' => 'ShopeeFood',  'icon' => 'bi-basket-fill',      'color' => '#059669', 'bg' => '#ecfdf5'],
                                'grabfood'    => ['label' => 'GrabFood',    'icon' => 'bi-bag-fill',         'color' => '#d97706', 'bg' => '#fffbeb'],
                                'gofood'      => ['label' => 'GoFood',      'icon' => 'bi-shop',             'color' => '#dc2626', 'bg' => '#fef2f2'],
                                'qpon'        => ['label' => 'Qpon',        'icon' => 'bi-gift-fill',        'color' => '#2563eb', 'bg' => '#eff6ff'],
                                'tiktok_shop' => ['label' => 'TikTok Shop', 'icon' => 'bi-play-circle-fill', 'color' => '#334155', 'bg' => '#f1f5f9'],
                            ];

                            $jenisPemesanan = [
                                'takeaway' => ['label' => 'Takeaway', 'icon' => 'bi-bag-check-fill', 'color' => '#334155', 'bg' => '#f1f5f9'],
                                'dinein'   => ['label' => 'Dine In',  'icon' => 'bi-cup-hot-fill',   'color' => '#0891b2', 'bg' => '#ecfeff'],
                            ];

                            $platformPembayaran = [
                                'cash'           => ['label' => 'Cash',           'icon' => 'bi-cash-stack',   'color' => '#059669', 'bg' => '#ecfdf5'],
                                'transfer'       => ['label' => 'Transfer',       'icon' => 'bi-bank',         'color' => '#334155', 'bg' => '#f1f5f9'],
                                'qris_bca'       => ['label' => 'QRIS BCA',       'icon' => 'bi-qr-code-scan', 'color' => '#2563eb', 'bg' => '#eff6ff'],
                                'qris_bukupay'   => ['label' => 'QRIS Bukupay',   'icon' => 'bi-upc-scan',     'color' => '#475569', 'bg' => '#f8fafc'],
                                'qris_esb'       => ['label' => 'QRIS ESB (POS)', 'icon' => 'bi-qr-code',      'color' => '#0891b2', 'bg' => '#ecfeff'],
                                'qris_gopay'     => ['label' => 'QRIS GoPay',     'icon' => 'bi-qr-code-scan', 'color' => '#0891b2', 'bg' => '#ecfeff'],
                                'qris_shopeepay' => ['label' => 'QRIS ShopeePay', 'icon' => 'bi-upc',          'color' => '#d97706', 'bg' => '#fffbeb'],
                            ];
                        @endphp

                        <div>
                            <h3 class="section-title"><i class="bi bi-globe2" style="color:var(--accent)"></i> Platform Online</h3>
                            <div class="metric-grid">
                                @foreach ($platformOnline as $key => $p)
                                    @php $val = $totalPerPlatform[$key] ?? 0; @endphp
                                    <div class="metric-item">
                                        <div class="metric-icon" style="background:{{ $p['bg'] }};color:{{ $p['color'] }}"><i class="bi {{ $p['icon'] }}"></i></div>
                                        <div style="min-width:0;">
                                            <div class="metric-val">Rp {{ number_format($val) }}</div>
                                            <div class="metric-label">{{ $p['label'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h3 class="section-title"><i class="bi bi-bag" style="color:var(--accent)"></i> Jenis Pemesanan</h3>
                            <div class="metric-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                                @foreach ($jenisPemesanan as $key => $p)
                                    @php
                                        $val = 0;
                                        if ($key === 'takeaway') $val = $takeawayTotal ?? 0;
                                        if ($key === 'dinein') $val = $dineinTotal ?? 0;
                                    @endphp
                                    <div class="metric-item">
                                        <div class="metric-icon" style="background:{{ $p['bg'] }};color:{{ $p['color'] }}"><i class="bi {{ $p['icon'] }}"></i></div>
                                        <div style="min-width:0;">
                                            <div class="metric-val">Rp {{ number_format($val) }}</div>
                                            <div class="metric-label">{{ $p['label'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h3 class="section-title"><i class="bi bi-wallet2" style="color:var(--accent)"></i> Jenis Pembayaran</h3>
                            <div class="metric-grid payment">
                                @foreach ($platformPembayaran as $key => $p)
                                    <div class="metric-item">
                                        <div class="metric-icon" style="background:{{ $p['bg'] }};color:{{ $p['color'] }}"><i class="bi {{ $p['icon'] }}"></i></div>
                                        <div style="min-width:0;">
                                            <div class="metric-val">Rp {{ number_format($totalPerPlatform[$key] ?? 0) }}</div>
                                            <div class="metric-label">{{ $p['label'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CHARTS --}}
                <div class="chart-grid">
                    <div class="c-card chart-full">
                        <div class="c-card-header">
                            <div>
                                <strong><i class="bi bi-bar-chart-line" style="color:var(--accent);margin-right:6px;"></i> Statistik Penjualan</strong>
                                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-top:2px;">Total omset berdasarkan tanggal.</div>
                            </div>
                            <span class="badge badge-neutral">Rp {{ number_format($totalOmset ?? 0) }}</span>
                        </div>
                        <div class="c-card-body">
                            <div class="chart-wrap">
                                @if ($filterApplied && count($chartOmset ?? []) > 0)
                                    <canvas id="sales-chart"></canvas>
                                @else
                                    <div class="empty-chart"><i class="bi bi-info-circle"></i><div>Harap terapkan filter untuk melihat grafik</div></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="c-card">
                        <div class="c-card-header">
                            <div>
                                <strong><i class="bi bi-people-fill" style="color:var(--success);margin-right:6px;"></i> Customer Unit</strong>
                                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-top:2px;">Trend jumlah CU.</div>
                            </div>
                            <span class="badge badge-neutral">{{ number_format($totalCU ?? 0) }}</span>
                        </div>
                        <div class="c-card-body">
                            <div class="chart-wrap sm">
                                @if ($filterApplied && count($chartCU ?? []) > 0)
                                    <canvas id="visitors-chart"></canvas>
                                @else
                                    <div class="empty-chart"><i class="bi bi-info-circle"></i><div>Harap filter terlebih dahulu.</div></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="c-card">
                        <div class="c-card-header">
                            <div>
                                <strong><i class="bi bi-receipt" style="color:#7c3aed;margin-right:6px;"></i> Jumlah Transaksi</strong>
                                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-top:2px;">Transaksi dalam range terpilih.</div>
                            </div>
                            <span class="badge badge-neutral">{{ request('tanggal_awal') ?: '-' }} / {{ request('tanggal_akhir') ?: '-' }}</span>
                        </div>
                        <div class="c-card-body">
                            <div class="chart-wrap md">
                                @if ($filterApplied && count($chartOrders ?? []) > 0)
                                    <canvas id="orders-chart"></canvas>
                                @else
                                    <div class="empty-chart"><i class="bi bi-info-circle"></i><div>Harap filter terlebih dahulu.</div></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="c-card chart-full">
                        <div class="c-card-header">
                            <div>
                                <strong><i class="bi bi-graph-up-arrow" style="color:#f97316;margin-right:6px;"></i> AOV (Omset / CU)</strong>
                                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-top:2px;">Rata-rata nilai transaksi per customer unit.</div>
                            </div>
                            <span class="badge badge-neutral">Average order value</span>
                        </div>
                        <div class="c-card-body">
                            <div class="chart-wrap md">
                                @if ($filterApplied && count($chartAOV ?? []) > 0)
                                    <canvas id="aov-chart"></canvas>
                                @else
                                    <div class="empty-chart"><i class="bi bi-info-circle"></i><div>Harap filter terlebih dahulu.</div></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- JAM + PRODUK --}}
                <div class="chart-grid" style="align-items:start;">
                    <div class="c-card">
                        <div class="c-card-header">
                            <div>
                                <strong><i class="bi bi-clock" style="color:var(--accent);margin-right:6px;"></i> Transaksi per Jam</strong>
                                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-top:2px;">Distribusi transaksi dan omset per jam operasional.</div>
                            </div>
                        </div>
                        <div class="c-card-body">
                            <div class="chart-wrap h260">
                                @if ($filterApplied)
                                    <canvas id="hourly-sales-chart"></canvas>
                                @else
                                    <div class="empty-chart"><i class="bi bi-info-circle"></i><div>Harap filter terlebih dahulu.</div></div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="c-card">
                        <div class="c-card-header">
                            <div>
                                <strong><i class="bi bi-box-seam" style="color:var(--accent);margin-right:6px;"></i> Penjualan Produk</strong>
                                <div style="font-size:12px;color:var(--text-muted);font-weight:600;margin-top:2px;">Produk dengan performa penjualan tertinggi.</div>
                            </div>

                            <select id="filterKategori" class="f-select form-select js-select2-no-search" data-no-select2="1" style="width:150px;">
                                <option value="">Semua</option>
                                <option value="PAKET">PAKET</option>
                                <option value="ALACARTE">ALACARTE</option>
                                <option value="DISKON">PROMO</option>
                            </select>
                        </div>

                        <div class="table-scroll">
                            <table class="c-table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th style="text-align:right;">Penjualan</th>
                                        <th style="text-align:right;">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="produkTableBody">
                                    @forelse ($topProduk ?? collect() as $p)
                                        <tr class="produk-row">
                                            <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $p->item_nama }}</td>
                                            <td style="text-align:right;font-weight:700;">{{ number_format($p->total_penjualan) }}</td>
                                            <td style="text-align:right;font-weight:700;">Rp {{ number_format($p->total_harga ?? 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" style="text-align:center;color:var(--text-muted);padding:28px;">Tidak ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <script type="application/json" id="dashData">
                    {!! json_encode([
                        'filterApplied' => (bool)($filterApplied ?? false),
                        'chartLabels' => $chartLabels ?? [],
                        'chartOmset' => $chartOmset ?? [],
                        'chartCU' => $chartCU ?? [],
                        'chartOrders' => $chartOrders ?? [],
                        'chartAOV' => $chartAOV ?? [],
                        'chartHourlyLabels' => array_map(fn($h) => sprintf('%02d:00', $h), array_keys($chartHourlyFull ?? [])),
                        'chartHourlyFull' => array_values($chartHourlyFull ?? []),
                        'chartHourlyOmsetFull' => array_values($chartHourlyOmsetFull ?? []),
                    ]) !!}
                </script>
            </div>
        </div>

        <div id="loadingOverlay" style="pointer-events:none;position:absolute;inset:0;display:none;align-items:center;justify-content:center;border-radius:12px;background:rgba(255,255,255,.7);backdrop-filter:blur(3px);">
            <div style="display:inline-flex;align-items:center;gap:10px;border:1px solid var(--border-subtle);background:#fff;border-radius:10px;padding:12px 16px;font-weight:750;color:var(--accent);box-shadow:0 14px 34px rgba(15,23,42,.12);">
                <i class="bi bi-arrow-repeat"></i>
                Memuat dashboard
            </div>
        </div>
    </section>
</div>

@push('scripts')
@if (session('maintenance'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.Swal) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sedang Maintenance',
                    text: "{{ session('maintenance') }}",
                    confirmButtonColor: '#2563eb'
                });
            } else {
                alert("{{ session('maintenance') }}");
            }
        });
    </script>
@endif
<script>
(function () {
    const hasFlatpickr = typeof window.flatpickr !== 'undefined';
    const hasChart = typeof window.Chart !== 'undefined';
    const isMobile = () => window.matchMedia('(max-width: 576px)').matches;

    function initFlatpickr() {
        if (!hasFlatpickr) return;
        flatpickr("#tanggal_awal", { dateFormat: "Y-m-d", allowInput: true });
        flatpickr("#tanggal_akhir", { dateFormat: "Y-m-d", allowInput: true });
    }

    function initSelect2() {
        if (!window.jQuery || !jQuery.fn.select2) return;

        jQuery('.js-select2').each(function () {
            const $el = jQuery(this);

            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }

            const $modal = $el.closest('.modal');

            $el.select2({
                width: '100%',
                placeholder: $el.data('placeholder') || '-- Semua Outlet --',
                allowClear: true,
                dropdownParent: $modal.length ? $modal : jQuery(document.body)
            });
        });

        jQuery('.js-select2-no-search').each(function () {
            const $el = jQuery(this);

            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }

            $el.select2({
                width: '100%',
                minimumResultsForSearch: Infinity,
                dropdownParent: jQuery(document.body)
            });
        });
    }

    async function exportDashboardToPDF() {
        const dashboard = document.getElementById('dashboardWrapper');
        if (!dashboard) return alert('Area dashboard tidak ditemukan.');

        const scale = 2;
        const canvas = await html2canvas(dashboard, { scale, useCORS: true, logging: false });
        const imgData = canvas.toDataURL('image/png');

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'pt', 'a4');

        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();

        const ratio = canvas.width / pdfWidth;
        const imgHeightPt = canvas.height / ratio;

        let pos = 0;
        let remain = imgHeightPt;

        while (remain > 0) {
            pdf.addImage(imgData, 'PNG', 0, -pos, pdfWidth, imgHeightPt);
            remain -= pdfHeight;
            pos += pdfHeight;
            if (remain > 0) pdf.addPage();
        }

        pdf.save('dashboard.pdf');
    }

    const chartInstances = [];

    function safeCreateChart(canvasEl, config) {
        if (!canvasEl) return null;

        try {
            const chart = new Chart(canvasEl.getContext('2d'), config);
            chartInstances.push(chart);
            return chart;
        } catch (e) {
            console.warn('chart create failed', e);
            return null;
        }
    }

    function destroyAllCharts() {
        while (chartInstances.length) {
            const c = chartInstances.pop();
            try { c.destroy(); } catch {}
        }
    }

    function resizeAllCharts() {
        chartInstances.forEach(c => {
            try { c.resize(); } catch {}
        });
    }

    function getDashData() {
        const el = document.getElementById('dashData');
        if (!el) return null;

        try {
            return JSON.parse(el.textContent || '{}');
        } catch (e) {
            console.warn('dashData JSON parse failed', e);
            return null;
        }
    }

    function xAxisCommon() {
        const mobile = isMobile();

        return {
            grid: { display: false },
            ticks: {
                autoSkip: true,
                maxTicksLimit: mobile ? 6 : 12,
                maxRotation: 45,
                minRotation: mobile ? 45 : 0,
                font: { size: mobile ? 10 : 12 }
            }
        };
    }

    function rupiahTick(v) {
        const n = Number(v || 0);

        if (isMobile() && n >= 1000000) {
            return 'Rp ' + (n / 1000000).toFixed(0) + 'jt';
        }

        return 'Rp ' + n.toLocaleString();
    }

    function initCharts() {
        if (!hasChart) return;

        const d = getDashData();
        if (!d || !d.filterApplied || !Array.isArray(d.chartLabels) || d.chartLabels.length === 0) return;

        destroyAllCharts();

        const mobile = isMobile();

        safeCreateChart(document.getElementById('sales-chart'), {
            type: 'bar',
            data: {
                labels: d.chartLabels,
                datasets: [{
                    label: 'Omset',
                    data: d.chartOmset || [],
                    backgroundColor: '#2563eb',
                    borderRadius: 4,
                    maxBarThickness: mobile ? 22 : 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: !mobile,
                        anchor: 'end',
                        align: 'start',
                        rotation: -90,
                        color: 'white',
                        font: { size: 10, weight: '600' },
                        formatter: val => 'Rp ' + Number(val).toLocaleString()
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 10,
                        callbacks: {
                            label: ctx => 'Rp ' + Number(ctx.raw || 0).toLocaleString()
                        }
                    }
                },
                scales: {
                    x: xAxisCommon(),
                    y: {
                        beginAtZero: true,
                        ticks: { callback: rupiahTick },
                        grid: { color: 'rgba(15,23,42,0.06)' }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        safeCreateChart(document.getElementById('visitors-chart'), {
            type: 'line',
            data: {
                labels: d.chartLabels,
                datasets: [{
                    label: 'Customer Unit',
                    data: d.chartCU || [],
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5,150,105,0.10)',
                    fill: true,
                    tension: 0.32,
                    pointRadius: mobile ? 2 : 3,
                    pointHoverRadius: mobile ? 4 : 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: !mobile,
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        color: '#059669',
                        font: { size: 10, weight: '700' },
                        formatter: v => Number(v || 0).toLocaleString()
                    },
                    tooltip: { backgroundColor: '#111827', padding: 10 }
                },
                scales: {
                    x: xAxisCommon(),
                    y: { beginAtZero: true, grid: { color: 'rgba(15,23,42,0.06)' } }
                }
            },
            plugins: [ChartDataLabels]
        });

        safeCreateChart(document.getElementById('orders-chart'), {
            type: 'bar',
            data: {
                labels: d.chartLabels,
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: d.chartOrders || [],
                    backgroundColor: '#7c3aed',
                    borderRadius: 4,
                    maxBarThickness: mobile ? 22 : 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: !mobile,
                        anchor: 'end',
                        align: 'top',
                        offset: 2,
                        color: '#7c3aed',
                        font: { size: 10, weight: '700' },
                        formatter: v => (Number(v || 0) > 0 ? Number(v).toLocaleString() : '')
                    },
                    tooltip: { backgroundColor: '#111827', padding: 10 }
                },
                scales: {
                    x: xAxisCommon(),
                    y: { beginAtZero: true, grid: { color: 'rgba(15,23,42,0.06)' } }
                }
            },
            plugins: [ChartDataLabels]
        });

        safeCreateChart(document.getElementById('aov-chart'), {
            type: 'line',
            data: {
                labels: d.chartLabels,
                datasets: [{
                    label: 'AOV',
                    data: d.chartAOV || [],
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249,115,22,0.10)',
                    fill: true,
                    tension: 0.32,
                    pointRadius: mobile ? 2 : 3,
                    pointHoverRadius: mobile ? 4 : 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: !mobile,
                        anchor: 'end',
                        align: 'top',
                        offset: 4,
                        color: '#f97316',
                        font: { size: 9, weight: '700' },
                        formatter: v => (Number(v || 0) > 0 ? ('Rp ' + Number(v).toLocaleString()) : '')
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 10,
                        callbacks: {
                            label: ctx => 'Rp ' + Number(ctx.raw || 0).toLocaleString()
                        }
                    }
                },
                scales: {
                    x: xAxisCommon(),
                    y: {
                        beginAtZero: true,
                        ticks: { callback: rupiahTick },
                        grid: { color: 'rgba(15,23,42,0.06)' }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        safeCreateChart(document.getElementById('hourly-sales-chart'), {
            type: 'bar',
            data: {
                labels: d.chartHourlyLabels || [],
                datasets: [
                    {
                        label: 'Transaksi',
                        data: d.chartHourlyFull || [],
                        borderRadius: 4,
                        backgroundColor: 'rgba(16,185,129,0.60)',
                        yAxisID: 'y'
                    },
                    {
                        label: 'Omset',
                        data: d.chartHourlyOmsetFull || [],
                        borderRadius: 4,
                        backgroundColor: 'rgba(37,99,235,0.50)',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: mobile ? 'bottom' : 'top',
                        labels: { usePointStyle: true, boxWidth: 8, font: { weight: '600' } }
                    },
                    datalabels: { display: false },
                    tooltip: { backgroundColor: '#111827', padding: 10 }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: mobile ? 10 : 12 },
                            autoSkip: true,
                            maxTicksLimit: mobile ? 8 : 18
                        }
                    },
                    y: { beginAtZero: true, position: 'left', grid: { color: 'rgba(15,23,42,0.06)' } },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { callback: rupiahTick }
                    }
                }
            }
        });
    }

    function initCategoryFilter() {
        const select = document.getElementById('filterKategori');
        if (!select) return;

        select.addEventListener('change', function () {
            const val = (this.value || '').toUpperCase();

            document.querySelectorAll('#produkTableBody .produk-row').forEach(row => {
                const text = (row.querySelector('td:first-child')?.textContent || '').toUpperCase();

                if (!val) row.style.display = '';
                else if (val === 'PAKET') row.style.display = text.includes('PAKET') ? '' : 'none';
                else if (val === 'ALACARTE') row.style.display = (!text.includes('PAKET') && !text.includes('DISKON')) ? '' : 'none';
                else if (val === 'DISKON') row.style.display = text.includes('DISKON') ? '' : 'none';
            });
        });
    }

    function initFilterValidation() {
        const form = document.getElementById('filterForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const start = document.getElementById('tanggal_awal')?.value;
            const end = document.getElementById('tanggal_akhir')?.value;

            if (!start || !end) {
                e.preventDefault();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Filter belum lengkap',
                        text: 'Harap isi Tanggal Awal dan Akhir.',
                        confirmButtonColor: '#2563eb'
                    });
                } else {
                    alert('Harap isi Tanggal Awal dan Akhir!');
                }
            }
        });
    }

    function initExportButton() {
        const btn = document.getElementById('exportPDF');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            try {
                btn.disabled = true;
                const oldHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Exporting...';
                await exportDashboardToPDF();
                btn.innerHTML = oldHtml;
                btn.disabled = false;
            } catch (err) {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-file-earmark-pdf"></i> Export PDF';
                alert('Gagal mengekspor PDF.');
            }
        });
    }

    function attachResizeHandler() {
        let timer = null;

        window.addEventListener('resize', () => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                initCharts();
                resizeAllCharts();
            }, 250);
        });
    }

    function initAll() {
        initFlatpickr();
        initSelect2();
        initCharts();
        initCategoryFilter();
        initFilterValidation();
        initExportButton();
        attachResizeHandler();
    }

    document.readyState === 'loading'
        ? document.addEventListener('DOMContentLoaded', initAll)
        : initAll();
})();
</script>
@endpush

@include('Temp.Investor.footer')

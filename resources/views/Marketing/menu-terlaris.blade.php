@section('title', 'Dashboard Menu Terlaris')
@section('breadcrumb', 'Marketing / Menu Terlaris')

@include('Temp.Investor.header')
<style>
    :root {
        --menu-primary: #f97316;
        --menu-primary-dark: #c2410c;
        --menu-dark: #111827;
        --menu-muted: #6b7280;
        --menu-border: #e5e7eb;
        --menu-soft: #fff7ed;
        --menu-green: #16a34a;
        --menu-blue: #2563eb;
        --menu-purple: #7c3aed;
    }
    .menu-page { min-height: calc(100vh - 70px); background: linear-gradient(180deg, #fff7ed 0%, #f8fafc 55%, #ffffff 100%); padding: 24px; }
    .menu-hero { position: relative; overflow: hidden; border-radius: 30px; padding: 28px; color: #fff; background: radial-gradient(circle at 15% 10%, rgba(255,255,255,.34), transparent 28%), linear-gradient(135deg, #7c2d12, #f97316 52%, #f59e0b); box-shadow: 0 26px 70px rgba(194, 65, 12, .22); }
    .menu-hero:after { content: ''; position: absolute; right: -80px; top: -90px; width: 260px; height: 260px; border-radius: 999px; background: rgba(255,255,255,.18); }
    .menu-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.16); font-weight: 800; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; }
    .menu-title { margin: 14px 0 6px; font-size: 32px; font-weight: 900; letter-spacing: -.03em; }
    .menu-subtitle { margin: 0; max-width: 720px; color: rgba(255,255,255,.82); }
    .menu-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 20px; }
    .menu-filter { min-width: 190px; border: 1px solid rgba(255,255,255,.24); background: rgba(255,255,255,.14); color: #fff; border-radius: 18px; padding: 11px 14px; }
    .menu-filter label { display: block; font-size: 11px; color: rgba(255,255,255,.72); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px; }
    .menu-filter select { width: 100%; border: 0; outline: 0; background: transparent; color: #fff; font-weight: 800; }
    .menu-filter option { color: #111827; }
    .menu-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 16px; margin-top: 18px; }
    .menu-card { background: rgba(255,255,255,.92); border: 1px solid rgba(229,231,235,.9); border-radius: 24px; box-shadow: 0 16px 45px rgba(15, 23, 42, .07); padding: 18px; }
    .span-3 { grid-column: span 3; } .span-4 { grid-column: span 4; } .span-5 { grid-column: span 5; } .span-7 { grid-column: span 7; } .span-12 { grid-column: span 12; }
    .kpi small { color: var(--menu-muted); font-weight: 800; }
    .kpi strong { display: block; margin-top: 10px; color: var(--menu-dark); font-size: 24px; font-weight: 900; }
    .kpi .badge-soft { display: inline-flex; margin-top: 12px; border-radius: 999px; padding: 7px 10px; background: var(--menu-soft); color: var(--menu-primary-dark); font-size: 12px; font-weight: 900; }
    .section-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 14px; }
    .section-head h3 { margin: 0; color: var(--menu-dark); font-weight: 900; font-size: 18px; }
    .pill { border-radius: 999px; background: #f3f4f6; color: #374151; padding: 7px 11px; font-weight: 800; font-size: 12px; white-space: nowrap; }
    .top-menu { display: grid; gap: 10px; }
    .top-menu-row { display: grid; grid-template-columns: 34px 1fr auto; gap: 12px; align-items: center; padding: 12px; border-radius: 17px; background: #f9fafb; border: 1px solid #eef2f7; }
    .rank { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 12px; color: #fff; font-weight: 900; background: linear-gradient(135deg, #f97316, #fb923c); }
    .top-menu-row b { color: var(--menu-dark); }
    .top-menu-row small { color: var(--menu-muted); }
    .metric { text-align: right; font-weight: 900; color: var(--menu-dark); }
    .channel-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .channel-card { padding: 14px; border-radius: 18px; background: #f9fafb; border: 1px solid #eef2f7; }
    .channel-card i { font-size: 22px; color: var(--menu-primary); }
    .channel-card strong { display: block; margin-top: 8px; font-size: 18px; color: var(--menu-dark); }
    .channel-card small { color: var(--menu-muted); font-weight: 700; }
    .bar-list { display: grid; gap: 13px; }
    .bar-item { display: grid; gap: 7px; }
    .bar-label { display: flex; justify-content: space-between; color: var(--menu-dark); font-weight: 800; font-size: 13px; }
    .bar-track { height: 12px; border-radius: 999px; background: #ffedd5; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #fb923c, #f97316); }
    .table-wrap { overflow: auto; border-radius: 18px; border: 1px solid var(--menu-border); }
    .menu-table { width: 100%; border-collapse: collapse; min-width: 920px; background: #fff; }
    .menu-table th { text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; background: #111827; color: #fff; padding: 13px; white-space: nowrap; }
    .menu-table td { border-top: 1px solid #edf2f7; padding: 13px; color: #374151; font-weight: 700; }
    .menu-table tbody tr:hover { background: #fff7ed; }
    .money { color: var(--menu-green); font-weight: 900; }
    .qty { color: var(--menu-blue); font-weight: 900; }
    @media(max-width: 1100px){ .span-3,.span-4,.span-5,.span-7{grid-column:span 6}.channel-grid{grid-template-columns:1fr 1fr} }
    @media(max-width: 700px){ .menu-page{padding:14px}.menu-title{font-size:26px}.span-3,.span-4,.span-5,.span-7,.span-12{grid-column:span 12}.channel-grid{grid-template-columns:1fr} }

    /* Fix Select2 text colors for dark header */
    .menu-filter .select2-container--default .select2-selection--multiple {
        background-color: transparent !important;
        border: none !important;
        min-height: auto;
        padding-bottom: 0;
    }
    .menu-filter .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: rgba(255,255,255,0.2) !important;
        border: 1px solid rgba(255,255,255,0.3) !important;
        color: #fff !important;
        border-radius: 8px !important;
        padding: 4px 8px !important;
        margin-top: 0;
    }
    .menu-filter .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff !important;
        margin-right: 6px !important;
        border-right: 1px solid rgba(255,255,255,0.3) !important;
    }
    .menu-filter .select2-search__field {
        color: #fff !important;
    }
    .menu-filter .select2-search__field::placeholder {
        color: rgba(255,255,255,0.6) !important;
    }
    .menu-filter .select2-container--default.select2-container--focus .select2-selection--multiple {
        border: none !important;
    }
</style>

@php
    // $menus is now passed from the controller via DB
@endphp

<div class="menu-page">
    <section class="menu-hero">
        <div class="menu-kicker"><i class="bi bi-fire"></i> Menu Performance</div>
        <h1 class="menu-title">Dashboard Menu Terlaris</h1>
        <p class="menu-subtitle">Monitoring menu paling laku, kontribusi channel, total qty, dan omset tanpa tampilan tabel spreadsheet yang kaku.</p>

        <form class="menu-actions" method="GET">
            <div class="menu-filter" style="min-width: 140px;">
                <label>Tahun</label>
                <select name="tahun[]" class="mk-select2" multiple="multiple">
                    <option value="All" @selected(in_array('All', (array)($filters['tahun'] ?? ['All'])))>Semua Tahun</option>
                    @foreach($options['tahun'] ?? [] as $tahun)
                        <option value="{{ $tahun }}" @selected(in_array($tahun, (array)($filters['tahun'] ?? [])))>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="menu-filter" style="min-width: 140px;">
                <label>Bulan</label>
                <select name="bulan[]" class="mk-select2" multiple="multiple">
                    <option value="All" @selected(in_array('All', (array)($filters['bulan'] ?? ['All'])))>Semua Bulan</option>
                    @foreach($options['bulan'] ?? [] as $bulan)
                        <option value="{{ $bulan }}" @selected(in_array($bulan, (array)($filters['bulan'] ?? [])))>{{ $bulan }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="menu-filter" style="min-width: 140px;">
                <label>Mulai (Opsional)</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" style="width: 100%; border: 0; outline: 0; background: transparent; color: #fff; font-weight: 800; font-family: inherit;">
            </div>
            
            <div class="menu-filter" style="min-width: 140px;">
                <label>Akhir (Opsional)</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" style="width: 100%; border: 0; outline: 0; background: transparent; color: #fff; font-weight: 800; font-family: inherit;">
            </div>

            <div class="menu-filter" style="min-width: 180px;">
                <label>Outlet</label>
                <select name="outlet[]" class="mk-select2" multiple="multiple">
                    <option value="All" @selected(in_array('All', (array)($filters['outlet'] ?? ['All'])))>Semua Outlet</option>
                    @foreach($options['outlet'] ?? [] as $outlet)
                        <option value="{{ $outlet }}" @selected(in_array($outlet, (array)($filters['outlet'] ?? [])))>{{ $outlet }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex; align-items: flex-end; padding-bottom: 2px;">
                <button type="submit" style="background:rgba(255,255,255,0.2); color:#fff; border:1px solid rgba(255,255,255,0.4); border-radius:18px; padding:11px 24px; font-weight:800; cursor:pointer; transition: 0.2s;"><i class="bi bi-funnel-fill"></i> Terapkan</button>
            </div>
        </form>
    </section>

    <section class="menu-grid">
        <div class="menu-card kpi span-3"><small>Total Qty</small><strong>{{ $kpi['total_qty'] }}</strong><span class="badge-soft">Total Filtered</span></div>
        <div class="menu-card kpi span-3"><small>Total Omset</small><strong>{{ $kpi['total_omset'] }}</strong><span class="badge-soft">Total Filtered</span></div>
        <div class="menu-card kpi span-3"><small>Menu Teratas</small><strong>{{ $kpi['top_menu_name'] }}</strong><span class="badge-soft">{{ $kpi['top_menu_qty'] }} qty</span></div>
        <div class="menu-card kpi span-3"><small>Channel Dominan</small><strong>{{ $kpi['channel'] }}</strong><span class="badge-soft">Mayoritas transaksi</span></div>

        <div class="menu-card span-7">
            <div class="section-head"><h3>Top 10 Menu berdasarkan Qty</h3><span class="pill">Pareto Data</span></div>
            <div class="top-menu">
                @foreach($menus->take(10) as $index => $menu)
                    <div class="top-menu-row">
                        <div class="rank">{{ $index + 1 }}</div>
                        <div><b>{{ $menu['name'] }}</b><br><small>{{ $menu['omset'] }} total omset</small></div>
                        <div class="metric">{{ number_format($menu['qty'], 0, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="menu-card span-5">
            <div class="section-head"><h3>Kontribusi Channel</h3><span class="pill">Qty Ecommerce</span></div>
            <div class="channel-grid">
                @foreach($channels->take(3) as $ch)
                <div class="channel-card"><i class="bi bi-shop"></i><strong>{{ number_format($ch['qty'], 0, ',', '.') }}</strong><small>{{ $ch['name'] }}</small></div>
                @endforeach
            </div>
            <div class="bar-list" style="margin-top:18px">
                @foreach($channels as $ch)
                <div class="bar-item">
                    <div class="bar-label"><span>{{ $ch['name'] }}</span><span>{{ $ch['percent'] }}%</span></div>
                    <div class="bar-track"><div class="bar-fill" style="width:{{ $ch['percent'] }}%"></div></div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="menu-card span-12">
            <div class="section-head"><h3>Detail Menu</h3><span class="pill">Rekap Pareto</span></div>
            <div class="table-wrap">
                <table class="menu-table">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th>Total Qty</th>
                            <th>Omset</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $menu)
                            <tr>
                                <td>{{ $menu['name'] }}</td>
                                <td class="qty">{{ number_format($menu['qty'], 0, ',', '.') }}</td>
                                <td class="money">{{ $menu['omset'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.mk-select2').select2({
            width: '100%',
            dropdownAutoWidth: true,
            theme: 'default',
            placeholder: 'All',
            closeOnSelect: false
        });
    });
</script>
@endpush

@include('Temp.Investor.footer')

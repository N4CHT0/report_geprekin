@section('title', 'Dashboard Sales per Kota')
@section('breadcrumb', 'Marketing / Sales per Kota')

@include('Temp.Investor.header')

<style>
    :root {
        --mk-primary: #2563eb;
        --mk-dark: #0f172a;
        --mk-soft: #f8fafc;
        --mk-border: #e2e8f0;
        --mk-muted: #64748b;
        --mk-green: #16a34a;
        --mk-red: #dc2626;
        --mk-orange: #f97316;
    }
    .mk-page { background: linear-gradient(180deg, #f8fafc 0%, #eef4ff 100%); min-height: calc(100vh - 70px); padding: 24px; }
    .mk-hero { border-radius: 28px; padding: 28px; color: #fff; background: radial-gradient(circle at top left, #60a5fa, transparent 34%), linear-gradient(135deg, #0f172a, #1d4ed8); box-shadow: 0 24px 70px rgba(15, 23, 42, .20); }
    .mk-hero h1 { font-size: 30px; font-weight: 800; margin: 0; }
    .mk-hero p { color: rgba(255,255,255,.78); margin: 8px 0 0; }
    .mk-status { display: inline-flex; margin-top: 12px; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.14); font-weight: 800; font-size: 12px; }
    .mk-warning { margin-top: 12px; padding: 12px 14px; border-radius: 16px; background: rgba(255,255,255,.16); color: #fff; font-weight: 700; }
    .mk-filter { margin-top: 18px; display: grid; grid-template-columns: repeat(7, minmax(120px, 1fr)); gap: 12px; }
    .mk-control { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18); border-radius: 16px; padding: 11px 13px; color: #fff; }
    .mk-control label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: rgba(255,255,255,.62); margin-bottom: 5px; }
    .mk-control select { width: 100%; background: transparent; color: #fff; border: 0; outline: 0; font-weight: 700; }
    .mk-control option { color: #0f172a; }
    .mk-submit, .mk-reset { border: 0; border-radius: 16px; background: #fff; color: #1d4ed8; font-weight: 900; cursor: pointer; text-align:center; text-decoration:none; display:flex; align-items:center; justify-content:center; }
    .mk-reset { background: rgba(255,255,255,.18); color: #fff; border: 1px solid rgba(255,255,255,.22); }
    .mk-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; margin-top: 18px; }
    .mk-card { background: #fff; border: 1px solid var(--mk-border); border-radius: 24px; padding: 18px; box-shadow: 0 16px 48px rgba(15,23,42,.07); }
    .mk-kpi { min-height: 142px; position: relative; overflow: hidden; }
    .mk-kpi:after { content: ''; position: absolute; right: -28px; top: -28px; width: 92px; height: 92px; background: #dbeafe; border-radius: 50%; }
    .mk-kpi small { color: var(--mk-muted); font-weight: 700; }
    .mk-kpi strong { display: block; margin-top: 12px; font-size: 22px; color: var(--mk-dark); }
    .mk-trend { margin-top: 12px; display: inline-flex; gap: 6px; align-items: center; font-weight: 800; font-size: 13px; }
    .up { color: var(--mk-green); } .flat { color: var(--mk-orange); }
    .span-2 { grid-column: span 2; } .span-4 { grid-column: span 4; }
    .mk-section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .mk-section-title h3 { margin: 0; color: var(--mk-dark); font-weight: 800; }
    .mk-pill { border-radius: 99px; padding: 7px 12px; background: #eff6ff; color: #1d4ed8; font-weight: 800; font-size: 12px; }
    .mk-chart { height: 280px; display: flex; align-items: end; gap: 10px; padding-top: 24px; padding-bottom: 34px; }
    .mk-bar { flex: 1; min-width: 26px; background: linear-gradient(180deg, #60a5fa, #2563eb); border-radius: 12px 12px 6px 6px; position: relative; }
    .mk-bar span { position: absolute; bottom: -24px; left: 50%; transform: translateX(-50%); font-size: 11px; color: var(--mk-muted); white-space: nowrap; }
    .mk-bar b { position: absolute; top: -22px; left: 50%; transform: translateX(-50%); font-size: 10px; color: var(--mk-muted); white-space: nowrap; }
    .mk-list { display: grid; gap: 10px; }
    .mk-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 12px; background: var(--mk-soft); border-radius: 16px; }
    .mk-row b { color: var(--mk-dark); }
    .mk-row small { color: var(--mk-muted); }
    .mk-empty { color: var(--mk-muted); text-align: center; padding: 28px 12px; font-weight: 700; }
    @media(max-width: 1300px){ .mk-filter{grid-template-columns:repeat(4,1fr)} }
    @media(max-width: 1000px){ .mk-grid{grid-template-columns:1fr 1fr}.span-2,.span-4{grid-column:span 2} }
    @media(max-width: 640px){ .mk-page{padding:14px}.mk-filter,.mk-grid{grid-template-columns:1fr}.span-2,.span-4{grid-column:span 1} }
</style>

<div class="mk-page">
    <section class="mk-hero">
        <h1>Sales Intelligence per Kota</h1>
        <p>Ringkasan performa omset, CU, outlet aktif, dan basket size dari Google Sheet.</p>

        <div class="mk-status">
            Status Sheet: {{ $kpi['status_sheet'] ?? 'Unknown' }} | Data: {{ $kpi['jumlah_data'] ?? 0 }}
        </div>

        @if(($kpi['status_sheet'] ?? '') !== 'Connected')
            <div class="mk-warning">
                {{ $kpi['message_sheet'] ?? 'Google Sheet belum bisa dibaca.' }}
                <br>
                Solusi: Google Sheet harus Share → Anyone with the link → Viewer, atau File → Share → Publish to web → CSV.
            </div>
        @endif

        <form class="mk-filter" method="GET" action="{{ url()->current() }}">
            <div class="mk-control">
                <label>Provinsi</label>
                <select name="provinsi">
                    <option value="All">All</option>
                    @foreach($options['provinsi'] ?? [] as $provinsi)
                        <option value="{{ $provinsi }}" @selected(($filters['provinsi'] ?? 'All') == $provinsi)>{{ $provinsi }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mk-control">
                <label>Kota/Kab</label>
                <select name="kota">
                    <option value="All">All</option>
                    @foreach($options['kota'] ?? [] as $kota)
                        <option value="{{ $kota }}" @selected(($filters['kota'] ?? 'All') == $kota)>{{ $kota }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mk-control">
                <label>Tahun</label>
                <select name="tahun">
                    <option value="All">All</option>
                    @foreach($options['tahun'] ?? [] as $tahun)
                        <option value="{{ $tahun }}" @selected(($filters['tahun'] ?? date('Y')) == $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mk-control">
                <label>Bulan</label>
                <select name="bulan">
                    <option value="All">All</option>
                    @foreach($options['bulan'] ?? [] as $bulan)
                        <option value="{{ $bulan }}" @selected(($filters['bulan'] ?? 'All') == $bulan)>{{ $bulan }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mk-control">
                <label>Mulai (Ops.)</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" style="width: 100%; background: transparent; color: #fff; border: 0; outline: 0; font-weight: 700;">
            </div>
            
            <div class="mk-control">
                <label>Akhir (Ops.)</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" style="width: 100%; background: transparent; color: #fff; border: 0; outline: 0; font-weight: 700;">
            </div>

            <div class="mk-control">
                <label>Quarter</label>
                <select name="quarter">
                    <option value="All">All</option>
                    @foreach($options['quarter'] ?? [] as $quarter)
                        <option value="{{ $quarter }}" @selected(($filters['quarter'] ?? 'All') == $quarter)>{{ $quarter }}</option>
                    @endforeach
                </select>
            </div>

            <button class="mk-submit" type="submit">Filter</button>
            <a class="mk-reset" href="{{ url()->current() }}">Reset</a>
        </form>
    </section>

    <section class="mk-grid">
        <div class="mk-card mk-kpi span-2"><small>Total Omzet</small><strong>{{ $kpi['total_omzet'] ?? 'Rp0' }}</strong><div class="mk-trend up">▲ Live Sheet</div></div>
        <div class="mk-card mk-kpi span-2"><small>Total CU</small><strong>{{ $kpi['total_cu'] ?? '0' }}</strong><div class="mk-trend up">▲ Live Sheet</div></div>
        <div class="mk-card mk-kpi span-2"><small>Avg Basket Size</small><strong>{{ $kpi['avg_basket'] ?? 'Rp0' }}</strong><div class="mk-trend flat">● Calculated</div></div>
        <div class="mk-card mk-kpi span-2"><small>Total Outlet</small><strong>{{ $kpi['total_outlet'] ?? '0' }}</strong><div class="mk-trend up">▲ Unique Outlet</div></div>
        <div class="mk-card mk-kpi span-2"><small>Avg Omzet</small><strong>{{ $kpi['avg_omzet'] ?? 'Rp0' }}</strong><div class="mk-trend up">▲ Per Outlet</div></div>
        <div class="mk-card mk-kpi span-2"><small>Avg CU</small><strong>{{ $kpi['avg_cu'] ?? '0' }}</strong><div class="mk-trend up">▲ Per Outlet</div></div>

        <div class="mk-card span-4">
            <div class="mk-section-title"><h3>Tren Bulanan</h3><span class="mk-pill">Omzet</span></div>
            @if(($monthlyTrend ?? collect())->count() > 0)
                <div class="mk-chart">
                    @foreach($monthlyTrend as $item)
                        <div class="mk-bar" style="height:{{ $item['height'] }}%">
                            <b>{{ $item['omzet_label'] }}</b>
                            <span>{{ $item['bulan'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mk-empty">Tidak ada data untuk filter ini.</div>
            @endif
        </div>

        <div class="mk-card span-2">
            <div class="mk-section-title"><h3>Kota Teratas</h3><span class="mk-pill">Omzet</span></div>
            <div class="mk-list">
                @forelse($topCities ?? [] as $city)
                    <div class="mk-row">
                        <div>
                            <b>{{ $city['kota'] }}</b><br>
                            <small>{{ $city['provinsi'] }}</small>
                        </div>
                        <b>{{ $city['omzet_label'] }}</b>
                    </div>
                @empty
                    <div class="mk-empty">Tidak ada data untuk filter ini.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>

@include('Temp.Investor.footer')

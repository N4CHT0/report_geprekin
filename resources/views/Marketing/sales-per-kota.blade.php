@section('title', 'Dashboard Sales per Kota')
@section('breadcrumb', 'Marketing / Sales per Kota')

@include('Temp.Investor.header')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --mk-primary: #0f172a;
        --mk-primary-hover: #334155;
        --mk-accent: #2563eb;
        --mk-accent-light: #eff6ff;
        --mk-green: #059669;
        --mk-green-bg: #ecfdf5;
        --mk-red: #dc2626;
        --mk-red-bg: #fef2f2;
        --mk-orange: #d97706;
        --mk-dark: #0f172a;
        --mk-muted: #64748b;
        --mk-bg: #f8fafc;
        --mk-surface: #ffffff;
        --mk-border: #e2e8f0;
        --mk-border-hover: #cbd5e1;
    }

    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mk-bg); color: var(--mk-dark); -webkit-font-smoothing: antialiased; letter-spacing: -0.01em; }
    
    .mk-page { max-width: 1600px; margin: 0 auto; padding: 24px 32px; min-height: calc(100vh - 70px); }
    
    /* Clean Hero Section */
    .mk-hero {
        background: var(--mk-surface); padding: 24px 32px; border-radius: 12px;
        border: 1px solid var(--mk-border); box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        margin-bottom: 24px;
    }
    .mk-hero-top { display: flex; justify-content: space-between; align-items: flex-start; }
    .mk-hero h1 { font-size: 24px; font-weight: 700; margin: 0 0 4px 0; color: var(--mk-dark); letter-spacing: -0.02em; }
    .mk-hero p { font-size: 14px; color: var(--mk-muted); margin: 0; }
    
    .mk-status {
        display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px;
        border-radius: 6px; background: var(--mk-green-bg); color: var(--mk-green);
        border: 1px solid #a7f3d0; font-weight: 600; font-size: 12px;
    }
    .mk-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--mk-green); }
    
    .mk-warning { margin-top: 16px; padding: 12px 16px; border-radius: 6px; background: var(--mk-red-bg); border: 1px solid #fecaca; color: var(--mk-red); font-weight: 500; font-size: 13px; }
    
    /* Modern Tabs */
    .mk-tabs { display: flex; gap: 8px; margin-top: 20px; flex-wrap: wrap; }
    .mk-tab {
        background: var(--mk-bg); border: 1px solid var(--mk-border); color: var(--mk-muted);
        padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.15s;
        font-family: inherit; text-decoration: none; display: inline-block;
    }
    .mk-tab:hover { background: var(--mk-border); color: var(--mk-dark); }
    .mk-tab.active { background: var(--mk-primary); border-color: var(--mk-primary); color: #fff; }
    
    /* Enterprise Filters */
    .mk-filter { margin-top: 24px; display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; padding-top: 20px; border-top: 1px solid var(--mk-border); }
    .mk-control { flex: 1; min-width: 140px; }
    .mk-control label { display: block; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mk-muted); margin-bottom: 6px; }
    
    .mk-control select, .mk-control input { 
        width: 100%; border: 1px solid var(--mk-border); border-radius: 6px; padding: 8px 12px; 
        background: var(--mk-surface); color: var(--mk-dark); font-size: 13px; font-weight: 500;
        transition: all 0.15s ease; box-sizing: border-box; font-family: inherit;
    }
    .mk-control select:hover, .mk-control input:hover { border-color: var(--mk-border-hover); }
    .mk-control select:focus, .mk-control input:focus { outline: none; border-color: var(--mk-accent); box-shadow: 0 0 0 3px var(--mk-accent-light); }
    
    .mk-actions { display: flex; gap: 8px; }
    .mk-submit, .mk-reset { 
        border: 1px solid transparent; border-radius: 6px; font-weight: 600; cursor: pointer; text-align:center; text-decoration:none; 
        display:flex; align-items:center; justify-content:center; height: 36px; padding: 0 16px; font-size: 13px; transition: all 0.15s ease;
    }
    .mk-submit { background: var(--mk-primary); color: #fff; }
    .mk-submit:hover { background: var(--mk-primary-hover); }
    .mk-reset { background: var(--mk-surface); color: var(--mk-dark); border-color: var(--mk-border); }
    .mk-reset:hover { background: var(--mk-bg); }

    /* Layout */
    .mk-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 16px; }
    .span-2 { grid-column: span 2; } .span-4 { grid-column: span 4; }
    
    /* Data Cards */
    .mk-card { 
        background: var(--mk-surface); border: 1px solid var(--mk-border); border-radius: 12px; 
        padding: 24px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.01); transition: border-color 0.15s ease;
    }
    .mk-card:hover { border-color: var(--mk-border-hover); }
    
    /* KPIs */
    .mk-kpi { display: flex; flex-direction: column; }
    .mk-kpi small { color: var(--mk-muted); font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;}
    .mk-kpi strong { display: block; font-size: 26px; font-weight: 700; color: var(--mk-dark); letter-spacing: -0.03em; line-height: 1.1;}
    
    .mk-trend { margin-top: 12px; display: inline-flex; align-items: center; gap: 4px; font-weight: 600; font-size: 12px; padding: 4px 8px; border-radius: 4px; background: var(--mk-bg); }
    .up { color: var(--mk-green); } .flat { color: var(--mk-muted); }
    
    /* Section Titles */
    .mk-section-title { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid var(--mk-border); }
    .mk-section-title h3 { margin: 0; color: var(--mk-dark); font-weight: 600; font-size: 16px; letter-spacing: -0.01em;}
    .mk-pill { border-radius: 4px; padding: 4px 10px; background: var(--mk-bg); color: var(--mk-muted); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; border: 1px solid var(--mk-border); }
    
    /* Bar Chart Minimal */
    .mk-chart { height: 220px; display: flex; align-items: flex-end; gap: 8px; padding-top: 16px; padding-bottom: 24px; }
    .mk-bar { flex: 1; min-width: 30px; background: var(--mk-border); border-radius: 4px 4px 0 0; position: relative; transition: all 0.2s ease; }
    .mk-bar:hover { background: var(--mk-accent); }
    .mk-bar span { position: absolute; bottom: -24px; left: 50%; transform: translateX(-50%); font-size: 11px; font-weight: 500; color: var(--mk-muted); white-space: nowrap; }
    .mk-bar b { position: absolute; top: -22px; left: 50%; transform: translateX(-50%); font-size: 11px; font-weight: 600; color: var(--mk-dark); white-space: nowrap; opacity: 0; transition: opacity 0.2s; }
    .mk-bar:hover b { opacity: 1; }
    
    /* List Rows */
    .mk-list { display: grid; gap: 8px; }
    .mk-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border-bottom: 1px solid var(--mk-border); transition: background 0.15s ease; }
    .mk-row:last-child { border-bottom: none; }
    .mk-row:hover { background: var(--mk-bg); }
    .mk-row b { color: var(--mk-dark); font-size: 13px; font-weight: 600; }
    .mk-row small { color: var(--mk-muted); font-size: 12px; font-weight: 500; }
    .mk-empty { color: var(--mk-muted); text-align: center; padding: 32px 12px; font-size: 13px; }
    
    /* Select2 Overrides */
    .select2-container--default .select2-selection--multiple { background: var(--mk-surface) !important; border: 1px solid var(--mk-border) !important; border-radius: 6px !important; min-height: 36px !important; }
    .select2-container--default.select2-container--focus .select2-selection--multiple { border-color: var(--mk-accent) !important; box-shadow: 0 0 0 3px var(--mk-accent-light) !important; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice { 
        background: var(--mk-bg) !important; border: 1px solid var(--mk-border) !important; 
        color: var(--mk-dark) !important; border-radius: 4px !important; font-weight: 500 !important; 
        font-size: 12px !important; padding: 2px 8px 2px 24px !important; margin-top: 4px !important; position: relative;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { 
        color: var(--mk-muted) !important; position: absolute !important; left: 6px !important; top: 50% !important; transform: translateY(-50%) !important; border-right: none !important; margin-right: 0 !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover { background: transparent !important; color: var(--mk-red) !important; }

    @media(max-width: 1200px){ .mk-grid{grid-template-columns:1fr 1fr}.span-2,.span-4{grid-column:span 2} .mk-hero-top { flex-direction: column; gap: 16px; } }
    @media(max-width: 768px){ .mk-page{padding:16px}.mk-grid{grid-template-columns:1fr}.span-2,.span-4{grid-column:span 1} .mk-control { min-width: 100%; } }
</style>

<div class="mk-page">
    <section class="mk-hero">
        <div class="mk-hero-top">
            <div>
                <h1>Sales Intelligence per Kota</h1>
                <p>Analitik performa omset, CU, outlet aktif, dan basket size (Live Database).</p>
            </div>
            
            <div style="text-align: right;">
                <div class="mk-status">
                    Connected to Database ({{ $kpi['jumlah_data'] ?? 0 }} records)
                </div>
            </div>
        </div>
        
        <div class="mk-tabs">
            <a href="{{ url('/marketing/sales-per-kota') }}" class="mk-tab active">Overview</a>
            <a href="{{ url('/marketing/data-sales-perkota') }}" class="mk-tab">Outlet Ranking</a>
            <a href="{{ url('/marketing/data-sales-provinsi') }}" class="mk-tab">Provinsi</a>
            <a href="{{ url('/marketing/anomali-kota') }}" class="mk-tab">Anomali</a>
        </div>

        <form class="mk-filter" method="GET" action="{{ url()->current() }}">
            <div class="mk-control">
                <label>Provinsi</label>
                <select name="provinsi[]" class="mk-select2" multiple="multiple">
                    <option value="All" @selected(in_array('All', (array)($filters['provinsi'] ?? ['All'])))>All</option>
                    @foreach($options['provinsi'] ?? [] as $provinsi)
                        <option value="{{ $provinsi }}" @selected(in_array($provinsi, (array)($filters['provinsi'] ?? [])))>{{ $provinsi }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mk-control">
                <label>Kota/Kab</label>
                <select name="kota[]" class="mk-select2" multiple="multiple">
                    <option value="All" @selected(in_array('All', (array)($filters['kota'] ?? ['All'])))>All</option>
                    @foreach($options['kota'] ?? [] as $kota)
                        <option value="{{ $kota }}" @selected(in_array($kota, (array)($filters['kota'] ?? [])))>{{ $kota }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mk-control" style="max-width: 120px;">
                <label>Tahun</label>
                <select name="tahun[]" class="mk-select2" multiple="multiple">
                    <option value="All" @selected(in_array('All', (array)($filters['tahun'] ?? ['All'])))>All</option>
                    @foreach($options['tahun'] ?? [] as $tahun)
                        <option value="{{ $tahun }}" @selected(in_array($tahun, (array)($filters['tahun'] ?? [])))>{{ $tahun }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mk-control" style="max-width: 140px;">
                <label>Bulan</label>
                <select name="bulan[]" class="mk-select2" multiple="multiple">
                    <option value="All" @selected(in_array('All', (array)($filters['bulan'] ?? ['All'])))>All</option>
                    @foreach($options['bulan'] ?? [] as $bulan)
                        <option value="{{ $bulan }}" @selected(in_array($bulan, (array)($filters['bulan'] ?? [])))>{{ $bulan }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="mk-control" style="max-width: 140px;">
                <label>Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
            </div>
            
            <div class="mk-control" style="max-width: 140px;">
                <label>Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
            </div>

            <div class="mk-control" style="max-width: 120px; display: none;">
                <label>Quarter</label>
                <select name="quarter[]" class="mk-select2" multiple="multiple">
                    <option value="All" @selected(in_array('All', (array)($filters['quarter'] ?? ['All'])))>All</option>
                    @foreach($options['quarter'] ?? [] as $quarter)
                        <option value="{{ $quarter }}" @selected(in_array($quarter, (array)($filters['quarter'] ?? [])))>{{ $quarter }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mk-actions">
                <a class="mk-reset" href="{{ url()->current() }}">Clear</a>
                <button class="mk-submit" type="submit">Apply Filter</button>
            </div>
        </form>
    </section>

    <section class="mk-grid">
        <div class="mk-card mk-kpi span-2"><small>Total Omzet</small><strong>{{ $kpi['total_omzet'] ?? 'Rp0' }}</strong><div class="mk-trend up">▲ Live Data</div></div>
        <div class="mk-card mk-kpi span-2"><small>Total CU</small><strong>{{ $kpi['total_cu'] ?? '0' }}</strong><div class="mk-trend up">▲ Live Data</div></div>
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

@push('scripts')
<script>
    $(document).ready(function() {
        const kotaByProvinsi = @json($kotaByProvinsi ?? []);
        const currentKota = @json((array)($filters['kota'] ?? ['All']));
        const provinsiSelect = $('select[name="provinsi[]"]');
        const kotaSelect = $('select[name="kota[]"]');

        function updateKotaOptions(provs, selectedKotas) {
            kotaSelect.empty();
            let allOpt = new Option('All', 'All');
            if (selectedKotas.includes('All')) allOpt.selected = true;
            kotaSelect.append(allOpt);
            
            let cities = [];
            provs = Array.isArray(provs) ? provs : (provs ? [provs] : ['All']);

            if (provs.includes('All') || provs.length === 0) {
                Object.values(kotaByProvinsi).forEach(c => {
                    cities = cities.concat(c);
                });
            } else {
                provs.forEach(p => {
                    if (kotaByProvinsi[p]) {
                        cities = cities.concat(kotaByProvinsi[p]);
                    }
                });
            }

            cities = [...new Set(cities)].sort();
            
            cities.forEach(city => {
                let opt = new Option(city, city);
                if (selectedKotas.includes(city)) {
                    opt.selected = true;
                }
                kotaSelect.append(opt);
            });
            kotaSelect.trigger('change.select2');
        }

        provinsiSelect.on('change', function() {
            updateKotaOptions($(this).val(), ['All']);
        });

        // initial load
        updateKotaOptions(provinsiSelect.val(), currentKota);

        $('.mk-select2').select2({
            width: '100%',
            dropdownAutoWidth: true,
            theme: 'default',
            placeholder: 'All',
            closeOnSelect: false
        });
    });
</script>
<style>
    .select2-container--default .select2-selection--single {
        background-color: transparent !important;
        border: none !important;
        height: auto !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff !important;
        font-weight: 700;
        padding-left: 0 !important;
        line-height: normal !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: rgba(255,255,255,.62) transparent transparent transparent !important;
    }
    .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent rgba(255,255,255,.62) transparent !important;
    }
    .select2-dropdown {
        background-color: #1e293b !important;
        border: 1px solid rgba(255,255,255,.18) !important;
        color: #fff !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        background-color: #0f172a !important;
        border: 1px solid rgba(255,255,255,.18) !important;
        color: #fff !important;
        border-radius: 8px;
    }
    .select2-results__option {
        color: #fff !important;
    }
    .select2-container--default .select2-results__option--selected {
        background-color: #334155 !important;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #2563eb !important;
        color: #fff !important;
    }
</style>
@endpush

@include('Temp.Investor.footer')

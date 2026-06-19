@section('title', 'Dashboard Outlet Z')
@section('breadcrumb', 'Marketing / Outlet Z')

@include('Temp.Investor.header')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --primary: #0f172a;
        --primary-hover: #334155;
        --accent: #2563eb;
        --accent-light: #eff6ff;
        --success: #059669;
        --success-bg: #ecfdf5;
        --warning: #d97706;
        --danger: #dc2626;
        --danger-bg: #fef2f2;
        --bg-page: #f8fafc;
        --bg-card: #ffffff;
        --border: #e2e8f0;
        --border-hover: #cbd5e1;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --radius-lg: 12px;
        --radius-xl: 12px;
    }
    
    * { box-sizing: border-box; }
    
    body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--bg-page); color: var(--text-main); -webkit-font-smoothing: antialiased; letter-spacing: -0.01em; }
    
    .z1-page {
        padding: 24px 32px;
        max-width: 1600px;
        margin: 0 auto;
        min-height: calc(100vh - 70px);
    }
    
    /* Header Section */
    .z1-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
    .z1-title h1 { margin: 0; font-size: 24px; font-weight: 700; color: var(--text-main); letter-spacing: -0.02em; }
    .z1-title p { margin: 4px 0 0; color: var(--text-muted); font-size: 14px; font-weight: 500;}
    
    .z1-btn { 
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        border: 1px solid transparent; border-radius: 6px; padding: 8px 16px; height: 36px;
        font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.15s ease;
        background: var(--primary); color: #fff; text-decoration: none;
    }
    .z1-btn:hover { background: var(--primary-hover); }
    .z1-btn.light { background: var(--bg-card); color: var(--text-main); border-color: var(--border); }
    .z1-btn.light:hover { background: var(--bg-page); }
    
    /* Layout */
    .z1-shell { display: flex; gap: 24px; align-items: flex-start; }
    .z1-filter-wrapper { flex: 0 0 280px; position: sticky; top: 24px; }
    .z1-main { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 24px; }
    
    .z1-card { 
        background: var(--bg-card); border: 1px solid var(--border); 
        border-radius: var(--radius-lg); box-shadow: 0 1px 3px rgba(0, 0, 0, 0.01);
        transition: border-color 0.15s ease;
    }
    .z1-card:hover { border-color: var(--border-hover); }
    
    /* Sidebar Filter */
    .z1-filter { padding: 20px; }
    .z1-filter label { font-size: 11px; color: var(--text-muted); font-weight: 600; margin-top: 16px; display: block; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;}
    .z1-filter label:first-child { margin-top: 0; }
    .z1-filter input, .z1-filter select { 
        width: 100%; border: 1px solid var(--border); border-radius: 6px; 
        padding: 8px 12px; font-size: 13px; color: var(--text-main); font-weight: 500;
        background: var(--bg-card); transition: all 0.15s; font-family: inherit; box-sizing: border-box;
    }
    .z1-filter input:hover, .z1-filter select:hover { border-color: var(--border-hover); }
    .z1-filter input:focus, .z1-filter select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-light); }
    
    /* KPIs Top Row */
    .z1-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .z1-kpis-3 { grid-template-columns: repeat(3, 1fr); margin-top: -8px; }
    .z1-kpi { padding: 20px; display: flex; flex-direction: column; justify-content: space-between; }
    
    .z1-kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
    .z1-kpi-title { font-size: 12px; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.05em;}
    .z1-kpi-icon { width: 24px; height: 24px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; }
    .icon-blue { background: var(--accent-light); color: var(--accent); border: 1px solid #bfdbfe; }
    .icon-green { background: var(--success-bg); color: var(--success); border: 1px solid #a7f3d0; }
    .icon-red { background: var(--danger-bg); color: var(--danger); border: 1px solid #fecaca; }
    
    .z1-kpi-value { font-size: 24px; font-weight: 700; color: var(--text-main); letter-spacing: -0.03em; margin-bottom: 8px; line-height: 1.1; word-break: break-word;}
    
    /* Badge Styles */
    .z1-badge { 
        display: inline-flex; align-items: center; border-radius: 4px; 
        padding: 4px 8px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
    }
    .badge-green { background: var(--bg-page); color: var(--success); border: 1px solid var(--border);}
    .badge-red { background: var(--bg-page); color: var(--text-muted); border: 1px solid var(--border);}
    .badge-blue { background: var(--bg-page); color: var(--text-muted); border: 1px solid var(--border);}
    .badge-gray { background: var(--bg-page); color: var(--text-muted); border: 1px solid var(--border);}
    
    /* Table Section */
    .z1-toolbar { padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); }
    .z1-toolbar h3 { margin: 0; font-size: 16px; font-weight: 600; color: var(--text-main); }
    .z1-search { 
        border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; 
        font-size: 13px; width: 260px; transition: all 0.15s; background: var(--bg-card);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: 10px center; padding-left: 32px;
    }
    .z1-search:hover { border-color: var(--border-hover); }
    .z1-search:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-light); }
    
    .z1-table-wrap { overflow-x: auto; max-height: 600px; }
    table.z1-table { width: 100%; border-collapse: collapse; min-width: 1000px; text-align: left; }
    table.z1-table th { 
        background: var(--bg-page); color: var(--text-muted); font-size: 11px; font-weight: 600; 
        padding: 12px 20px; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 10;
        white-space: nowrap; text-transform: uppercase; letter-spacing: 0.05em;
    }
    table.z1-table td { 
        padding: 12px 20px; border-bottom: 1px solid var(--border); 
        font-size: 13px; color: var(--text-main); font-weight: 500;
        white-space: nowrap; transition: background 0.15s;
    }
    table.z1-table tr:last-child td { border-bottom: none; }
    table.z1-table tr:hover td { background: var(--bg-page); }
    
    /* Select2 Overrides */
    .select2-container--default .select2-selection--multiple { 
        border: 1px solid var(--border) !important; border-radius: 6px !important; 
        min-height: 36px !important; background: var(--bg-card) !important; 
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple { 
        border-color: var(--accent) !important; box-shadow: 0 0 0 3px var(--accent-light) !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice { 
        background: var(--bg-page) !important; border: 1px solid var(--border) !important; 
        color: var(--text-main) !important; border-radius: 4px !important; font-weight: 500 !important; 
        font-size: 12px !important; padding: 2px 8px 2px 24px !important; margin-top: 4px !important; position: relative;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { 
        color: var(--text-muted) !important; position: absolute !important; left: 6px !important; top: 50% !important; transform: translateY(-50%) !important; border-right: none !important; margin-right: 0 !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover { background: transparent !important; color: var(--danger) !important; }

    /* Info Box */
    .z1-info-box {
        background: var(--bg-page); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 16px 20px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start;
    }
    .z1-info-icon { font-size: 16px; margin-top: 2px; }
    .z1-info-text strong { color: var(--text-main); font-size: 13px; font-weight: 600; display: block; margin-bottom: 4px; }
    .z1-info-text p { color: var(--text-muted); font-size: 12px; line-height: 1.5; margin: 0; }
    
    @media (max-width: 1300px) {
        .z1-shell { flex-direction: column; }
        .z1-filter-wrapper { flex: none; width: 100%; position: static; }
        .z1-kpis { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .z1-kpis, .z1-kpis-3 { grid-template-columns: 1fr; }
        .z1-page { padding: 16px; }
        .z1-toolbar { flex-direction: column; align-items: flex-start; gap: 16px; }
        .z1-search { width: 100%; }
        .z1-kpi-value { font-size: 24px; }
        .z1-head { flex-direction: column; align-items: flex-start; }
    }
</style>

<div class="z1-page">
    <div class="z1-head">
        <div class="z1-title"><h1>Outlet Z Performance</h1><p>Monitoring outlet existing, closed, dan new dengan fokus ke problem dan kebutuhan follow up.</p></div>
        <div class="z1-actions"><button class="z1-btn light" onclick="let f = document.getElementById('filterFormZ'); let i = document.createElement('input'); i.type='hidden'; i.name='export'; i.value='1'; f.appendChild(i); f.submit(); setTimeout(() => i.remove(), 100); return false;">Export</button></div>
    </div>

    <div class="z1-info-box">
        <div class="z1-info-icon">💡</div>
        <div class="z1-info-text">
            <strong>Standar Akurasi Data (100% Raw-Catch)</strong>
            <p>Dashboard ini didesain untuk menangkap <strong>100% data transaksi aktual</strong> di lapangan. Outlet yang tidak/belum terdaftar di Master Sheet (Yatim-Piatu) namun menghasilkan omset akan tetap dipaksa tampil agar tidak ada kebocoran angka. Sebaliknya, entitas kosong/mati (Rp0) akan otomatis dibuang dari tabel untuk menjaga kemurnian analisis.</p>
        </div>
    </div>


    <div class="z1-shell">
        <aside class="z1-filter-wrapper">
            <div class="z1-card z1-filter">
                <form id="filterFormZ" method="GET">
                    <label>Tahun</label>
                    <select name="tahun[]" class="mk-select2" multiple="multiple">
                        <option value="All" @selected(in_array('All', (array)($filters['tahun'] ?? ['All'])))>All</option>
                        @foreach($options['tahun'] ?? [] as $tahun)
                            <option value="{{ $tahun }}" @selected(in_array($tahun, (array)($filters['tahun'] ?? [])))>{{ $tahun }}</option>
                        @endforeach
                    </select>

                    <label>Bulan</label>
                    <select name="bulan[]" class="mk-select2" multiple="multiple">
                        <option value="All" @selected(in_array('All', (array)($filters['bulan'] ?? ['All'])))>All</option>
                        @foreach($options['bulan'] ?? [] as $bulan)
                            <option value="{{ $bulan }}" @selected(in_array($bulan, (array)($filters['bulan'] ?? [])))>{{ $bulan }}</option>
                        @endforeach
                    </select>
                    
                    <label>Tanggal Mulai (Opsional)</label>
                    <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}">

                    <label>Tanggal Akhir (Opsional)</label>
                    <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                    
                    <label>Zona</label>
                    <select name="zona[]" class="mk-select2" multiple="multiple">
                        <option value="All" @selected(in_array('All', (array)($filters['zona'] ?? ['All'])))>All</option>
                        @foreach($options['zona'] ?? [] as $zona)
                            <option value="{{ $zona }}" @selected(in_array($zona, (array)($filters['zona'] ?? [])))>{{ $zona }}</option>
                        @endforeach
                    </select>
                    
                    <label>Status</label>
                    <select name="status[]" class="mk-select2" multiple="multiple">
                        <option value="All" @selected(in_array('All', (array)($filters['status'] ?? ['All'])))>All</option>
                        @foreach($options['status'] ?? [] as $status)
                            <option value="{{ $status }}" @selected(in_array($status, (array)($filters['status'] ?? [])))>{{ $status }}</option>
                        @endforeach
                    </select>
                    
                    <label>Provinsi</label>
                    <select name="provinsi[]" class="mk-select2" multiple="multiple">
                        <option value="All" @selected(in_array('All', (array)($filters['provinsi'] ?? ['All'])))>All</option>
                        @foreach($options['provinsi'] ?? [] as $provinsi)
                            <option value="{{ $provinsi }}" @selected(in_array($provinsi, (array)($filters['provinsi'] ?? [])))>{{ $provinsi }}</option>
                        @endforeach
                    </select>

                    <label>Kab/Kota</label>
                    <select name="kota[]" class="mk-select2" multiple="multiple">
                        <option value="All" @selected(in_array('All', (array)($filters['kota'] ?? ['All'])))>All</option>
                        @foreach($options['kota'] ?? [] as $kota)
                            <option value="{{ $kota }}" @selected(in_array($kota, (array)($filters['kota'] ?? [])))>{{ $kota }}</option>
                        @endforeach
                    </select>
                    
                    <label>Cari Outlet</label>
                    <select name="outlet[]" class="mk-select2" multiple="multiple">
                        <option value="All" @selected(in_array('All', (array)($filters['outlet'] ?? ['All'])))>All</option>
                        @foreach($options['outlet'] ?? [] as $outlet)
                            <option value="{{ $outlet }}" @selected(in_array($outlet, (array)($filters['outlet'] ?? [])))>{{ $outlet }}</option>
                        @endforeach
                    </select>
                    
                    <button class="z1-btn" type="submit" style="width:100%; margin-top:24px;">Terapkan Filter</button>
                </form>
            </div>
        </aside>

        <main class="z1-main">
            <div class="z1-kpis">
                <div class="z1-card z1-kpi">
                    <div class="z1-kpi-header">
                        <div class="z1-kpi-title"><div class="z1-kpi-icon icon-blue">🏠</div> Total Outlet</div>
                    </div>
                    <div class="z1-kpi-value">{{ number_format($kpi['total'] ?? 0, 0, ',', '.') }}</div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span class="z1-badge badge-blue">Unit</span>
                    </div>
                </div>
                
                <div class="z1-card z1-kpi success-kpi">
                    <div class="z1-kpi-header"><div class="z1-kpi-title"><div class="z1-kpi-icon icon-green">💰</div> Total Omset</div></div>
                    <div class="z1-kpi-value">{{ $kpi['formatted_omset'] }}</div>
                    <div><span class="z1-badge badge-green">Revenue</span></div>
                </div>
                
                <div class="z1-card z1-kpi">
                    <div class="z1-kpi-header"><div class="z1-kpi-title"><div class="z1-kpi-icon icon-blue">👥</div> Total Transaksi</div></div>
                    <div class="z1-kpi-value">{{ $kpi['formatted_cu'] }}</div>
                    <div><span class="z1-badge badge-blue">Customer Unit</span></div>
                </div>
                
                <div class="z1-card z1-kpi success-kpi">
                    <div class="z1-kpi-header"><div class="z1-kpi-title"><div class="z1-kpi-icon icon-green">📈</div> Rata-rata / Outlet</div></div>
                    <div class="z1-kpi-value">{{ $kpi['formatted_avg'] }}</div>
                    <div><span class="z1-badge badge-green">Average Omset</span></div>
                </div>
            </div>
            
            <div class="z1-kpis z1-kpis-3">
                <div class="z1-card z1-kpi" style="padding:16px 24px;">
                    <div class="z1-kpi-title" style="margin-bottom:8px;">Existing</div>
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <div class="z1-kpi-value" style="font-size:24px; margin:0;">{{ number_format($kpi['existing'] ?? 0, 0, ',', '.') }}</div>
                        <span class="z1-badge badge-green" style="font-size:10px;">Aktif</span>
                    </div>
                </div>
                <div class="z1-card z1-kpi" style="padding:16px 24px;">
                    <div class="z1-kpi-title" style="margin-bottom:8px;">Closed</div>
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <div class="z1-kpi-value" style="font-size:24px; margin:0;">{{ number_format($kpi['closed'] ?? 0, 0, ',', '.') }}</div>
                        <span class="z1-badge badge-red" style="font-size:10px;">Tutup</span>
                    </div>
                </div>
                <div class="z1-card z1-kpi" style="padding:16px 24px;">
                    <div class="z1-kpi-title" style="margin-bottom:8px;">New / Go</div>
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px;">
                        <div class="z1-kpi-value" style="font-size:24px; margin:0;">{{ number_format($kpi['new'] ?? 0, 0, ',', '.') }}</div>
                        <span class="z1-badge badge-blue" style="font-size:10px;">Growth</span>
                    </div>
                </div>
            </div>

            <section class="z1-card" style="border-radius:var(--radius-xl); overflow:hidden;">
                <div class="z1-toolbar">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <h3>Daftar Outlet Prioritas</h3>
                        <span class="z1-badge badge-gray">Sort by Omset</span>
                    </div>
                    <div>
                        <input type="text" id="liveSearch" class="z1-search" placeholder="Cari di tabel ini...">
                    </div>
                </div>
                <div class="z1-table-wrap">
                    <table class="z1-table">
                        <thead><tr><th>Outlet</th><th>Kab/Kota</th><th>Provinsi</th><th>Zona</th><th>Status</th><th>Total Omset</th><th>Avg Omset</th><th>Total CU</th><th>Avg CU</th></tr></thead>
                        <tbody>
                            @forelse($outlets ?? [] as $outlet)
                            <tr>
                                <td>{{ $outlet['nama'] }}</td>
                                <td>{{ $outlet['kota'] }}</td>
                                <td>{{ $outlet['provinsi'] }}</td>
                                <td><span class="z1-badge {{ str_replace(['green','red','blue','yellow'], ['badge-green','badge-red','badge-blue','badge-gray'], $outlet['zona_class']) }}" style="margin:0">{{ $outlet['zona'] }}</span></td>
                                <td><span class="z1-badge {{ str_replace(['existing','closed','new'], ['badge-green','badge-red','badge-blue'], $outlet['status_class']) }}">{{ $outlet['status_label'] }}</span></td>
                                <td>{{ $outlet['omset'] }}</td>
                                <td>{{ $outlet['avg_omset'] }}</td>
                                <td>{{ $outlet['cu'] }}</td>
                                <td>{{ $outlet['avg_cu'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="9" style="text-align:center; padding:32px; color:var(--text-muted);">Tidak ada data outlet untuk filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
document.getElementById('liveSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll('.z1-table tbody tr');
    
    rows.forEach(row => {
        // Abaikan baris "Tidak ada data"
        if(row.cells.length === 1) return;
        
        // Ambil text dari kolom Outlet, Kota, Provinsi
        let text = row.innerText.toLowerCase();
        
        if(text.includes(filter)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
</script>
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
@endpush

@include('Temp.Investor.footer')

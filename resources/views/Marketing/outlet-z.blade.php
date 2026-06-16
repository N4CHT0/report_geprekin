@section('title', 'Dashboard Outlet Z')
@section('breadcrumb', 'Marketing / Outlet Z')

@include('Temp.Investor.header')
<style>
    .z1-page{padding:24px;background:#f6f8fc;min-height:calc(100vh - 70px)}
    .z1-head{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:18px}
    .z1-title h1{margin:0;font-size:30px;font-weight:900;color:#111827}.z1-title p{margin:6px 0 0;color:#6b7280}
    .z1-actions{display:flex;gap:10px;flex-wrap:wrap}.z1-btn{border:0;border-radius:14px;padding:11px 15px;font-weight:800;background:#111827;color:#fff}.z1-btn.light{background:#fff;color:#111827;border:1px solid #e5e7eb}
    .z1-shell{display:grid;grid-template-columns:300px 1fr;gap:18px}.z1-card{background:#fff;border:1px solid #e5e7eb;border-radius:24px;box-shadow:0 14px 38px rgba(15,23,42,.06)}
    .z1-filter{padding:18px;position:sticky;top:16px}.z1-filter label{font-size:12px;color:#6b7280;font-weight:800;text-transform:uppercase}.z1-filter select,.z1-filter input{width:100%;border:1px solid #e5e7eb;border-radius:14px;padding:11px;margin:7px 0 14px;background:#f9fafb}
    .z1-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}.z1-kpi{padding:18px}.z1-kpi small{color:#6b7280;font-weight:800}.z1-kpi strong{display:block;font-size:24px;margin-top:8px;color:#111827}.z1-badge{display:inline-flex;margin-top:10px;border-radius:99px;padding:6px 10px;font-size:12px;font-weight:900}.green{background:#dcfce7;color:#166534}.red{background:#fee2e2;color:#991b1b}.blue{background:#dbeafe;color:#1d4ed8}.yellow{background:#fef3c7;color:#b45309}
    .z1-main{min-width:0}.z1-table-card{overflow:hidden}.z1-toolbar{padding:16px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e5e7eb}.z1-toolbar h3{margin:0;font-weight:900;color:#111827}.z1-table-wrap{overflow:auto;max-height:560px}
    table.z1-table{width:100%;border-collapse:collapse;min-width:980px}table.z1-table th{background:#f9fafb;color:#6b7280;font-size:12px;text-transform:uppercase;letter-spacing:.04em;text-align:left;padding:13px;position:sticky;top:0}table.z1-table td{padding:13px;border-top:1px solid #f1f5f9;color:#111827;font-weight:600}table.z1-table tr:hover{background:#f8fafc}.status{border-radius:99px;padding:6px 10px;font-size:12px;font-weight:900}.existing{background:#dcfce7;color:#166534}.closed{background:#fee2e2;color:#991b1b}.new{background:#dbeafe;color:#1d4ed8}
    @media(max-width:1000px){.z1-shell{grid-template-columns:1fr}.z1-kpis{grid-template-columns:1fr 1fr}.z1-filter{position:static}}@media(max-width:640px){.z1-page{padding:14px}.z1-kpis{grid-template-columns:1fr}.z1-head{display:block}.z1-actions{margin-top:12px}}
</style>

<div class="z1-page">
    <div class="z1-head">
        <div class="z1-title"><h1>Outlet Z Performance</h1><p>Monitoring outlet existing, closed, dan new dengan fokus ke problem dan kebutuhan follow up.</p></div>
        <div class="z1-actions"><button class="z1-btn light">Export</button><button class="z1-btn">Tambah Insight</button></div>
    </div>

    <div class="z1-shell">
        <aside class="z1-card z1-filter">
            <form method="GET">
                <label>Tahun</label>
                <select name="tahun">
                    <option value="All">All</option>
                    @foreach($options['tahun'] ?? [] as $tahun)
                        <option value="{{ $tahun }}" @selected(($filters['tahun'] ?? date('Y')) == $tahun)>{{ $tahun }}</option>
                    @endforeach
                </select>

                <label>Bulan</label>
                <select name="bulan">
                    <option value="All">All</option>
                    @foreach($options['bulan'] ?? [] as $bulan)
                        <option value="{{ $bulan }}" @selected(($filters['bulan'] ?? 'All') == $bulan)>{{ $bulan }}</option>
                    @endforeach
                </select>
                
                <label>Tanggal Mulai (Opsional)</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}">

                <label>Tanggal Akhir (Opsional)</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                
                <label>Zona</label>
                <select name="zona">
                    <option value="All">All</option>
                    @foreach($options['zona'] ?? [] as $zona)
                        <option value="{{ $zona }}" @selected(($filters['zona'] ?? 'All') == $zona)>{{ $zona }}</option>
                    @endforeach
                </select>
                
                <label>Status</label>
                <select name="status">
                    <option value="All">All</option>
                    @foreach($options['status'] ?? [] as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? 'All') == $status)>{{ $status }}</option>
                    @endforeach
                </select>
                
                <label>Provinsi</label>
                <select name="provinsi">
                    <option value="All">All</option>
                    @foreach($options['provinsi'] ?? [] as $provinsi)
                        <option value="{{ $provinsi }}" @selected(($filters['provinsi'] ?? 'All') == $provinsi)>{{ $provinsi }}</option>
                    @endforeach
                </select>

                <label>Kab/Kota</label>
                <select name="kota">
                    <option value="All">All</option>
                    @foreach($options['kota'] ?? [] as $kota)
                        <option value="{{ $kota }}" @selected(($filters['kota'] ?? 'All') == $kota)>{{ $kota }}</option>
                    @endforeach
                </select>
                
                <label>Cari Outlet</label>
                <input type="search" name="search" placeholder="Nama outlet..." value="{{ $filters['search'] ?? '' }}">
                
                <button class="z1-btn" type="submit" style="width:100%">Terapkan Filter</button>
            </form>
        </aside>

        <main class="z1-main">
            <div class="z1-kpis">
                <div class="z1-card z1-kpi"><small>Total Outlet (Filtered)</small><strong>{{ number_format($kpi['total'] ?? 0, 0, ',', '.') }}</strong><span class="z1-badge blue">Unit</span></div>
                <div class="z1-card z1-kpi"><small>Total Omset</small><strong>{{ $kpi['formatted_omset'] }}</strong><span class="z1-badge green">Revenue</span></div>
                <div class="z1-card z1-kpi"><small>Total Transaksi</small><strong>{{ $kpi['formatted_cu'] }}</strong><span class="z1-badge blue">Customer Unit</span></div>
                <div class="z1-card z1-kpi"><small>Rata-rata Omset / Outlet</small><strong>{{ $kpi['formatted_avg'] }}</strong><span class="z1-badge green">Average</span></div>
            </div>
            
            <div class="z1-kpis" style="grid-template-columns:repeat(3,1fr); margin-top:-4px;">
                <div class="z1-card z1-kpi" style="padding:12px 18px"><small>Existing</small><strong style="font-size:18px;">{{ number_format($kpi['existing'] ?? 0, 0, ',', '.') }}</strong><span class="z1-badge green" style="margin-top:4px; padding:4px 8px; font-size:10px;">Aktif</span></div>
                <div class="z1-card z1-kpi" style="padding:12px 18px"><small>Closed</small><strong style="font-size:18px;">{{ number_format($kpi['closed'] ?? 0, 0, ',', '.') }}</strong><span class="z1-badge red" style="margin-top:4px; padding:4px 8px; font-size:10px;">Tutup</span></div>
                <div class="z1-card z1-kpi" style="padding:12px 18px"><small>New / Go</small><strong style="font-size:18px;">{{ number_format($kpi['new'] ?? 0, 0, ',', '.') }}</strong><span class="z1-badge blue" style="margin-top:4px; padding:4px 8px; font-size:10px;">Growth</span></div>
            </div>

            <section class="z1-card z1-table-card">
                <div class="z1-toolbar">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <h3>Daftar Outlet Prioritas</h3>
                        <span class="z1-badge blue">Sort by Omset</span>
                    </div>
                    <div>
                        <input type="text" id="liveSearch" placeholder="Cari di tabel ini..." style="border:1px solid #e5e7eb; border-radius:14px; padding:8px 14px; font-size:12px; width:250px; background:#f9fafb; outline:none;">
                    </div>
                </div>
                <div class="z1-table-wrap">
                    <table class="z1-table">
                        <thead><tr><th>Outlet</th><th>Kab/Kota</th><th>Provinsi</th><th>Zona</th><th>Status</th><th>Total Omset</th><th>Avg Omset</th><th>Total CU</th><th>Avg CU</th><th>Keperluan</th></tr></thead>
                        <tbody>
                            @forelse($outlets ?? [] as $outlet)
                            <tr>
                                <td>{{ $outlet['nama'] }}</td>
                                <td>{{ $outlet['kota'] }}</td>
                                <td>{{ $outlet['provinsi'] }}</td>
                                <td><span class="z1-badge {{ $outlet['zona_class'] }}" style="margin:0">{{ $outlet['zona'] }}</span></td>
                                <td><span class="status {{ $outlet['status_class'] }}">{{ $outlet['status_label'] }}</span></td>
                                <td>{{ $outlet['omset'] }}</td>
                                <td>{{ $outlet['avg_omset'] }}</td>
                                <td>{{ $outlet['cu'] }}</td>
                                <td>{{ $outlet['avg_cu'] }}</td>
                                <td>{{ $outlet['keperluan'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="9" style="text-align:center; padding:20px;">Tidak ada data outlet untuk filter ini.</td></tr>
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
});
</script>

@include('Temp.Investor.footer')

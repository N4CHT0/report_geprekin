@include('Temp.Investor.header')
@include('Temp.Investor.partials.sidebar')

<style>
    :root {
        --menu-primary: #10b981; /* Emerald */
        --menu-primary-light: #d1fae5;
        --menu-dark: #1e293b;
        --menu-gray: #64748b;
        --menu-light: #f8fafc;
        --menu-green: #059669; /* Darker green for money text */
    }
    .menu-page {
        padding: 30px;
        background: #f1f5f9;
        min-height: 100vh;
        font-family: 'Inter', system-ui, sans-serif;
    }
    .menu-hero {
        background: linear-gradient(135deg, var(--menu-primary), #34d399);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 25px;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);
        position: relative;
        overflow: hidden;
    }
    .menu-hero::after {
        content: '';
        position: absolute;
        top: -50%; right: -10%;
        width: 300px; height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .menu-kicker {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 800;
        background: rgba(255,255,255,0.2);
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        margin-bottom: 15px;
        backdrop-filter: blur(5px);
    }
    .menu-title {
        font-size: 32px;
        font-weight: 900;
        margin: 0 0 10px 0;
    }
    .menu-subtitle {
        font-size: 15px;
        opacity: 0.9;
        margin: 0;
        max-width: 600px;
        line-height: 1.6;
    }
    .menu-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02), 0 10px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .section-head h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 800;
        color: var(--menu-dark);
    }
    .pill {
        background: var(--menu-light);
        color: var(--menu-gray);
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 12px;
    }
    
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    .go-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 1800px; /* Lebar tabel untuk scroll horizontal */
    }
    .go-table th, .go-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .go-table th {
        background: #f8fafc;
        font-weight: 700;
        color: var(--menu-gray);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .go-table td {
        font-size: 14px;
        color: var(--menu-dark);
    }
    .go-table tbody tr:hover {
        background: #f1f5f9;
    }
    
    /* Kolom kiri terkunci (Frozen columns) */
    .freeze-col-1 { position: sticky; left: 0; background: white; z-index: 5; box-shadow: 2px 0 5px rgba(0,0,0,0.05); border-right: 1px solid #e2e8f0; font-weight: 700;}
    .freeze-col-2 { position: sticky; left: 220px; background: white; z-index: 5; border-right: 1px solid #e2e8f0; }
    
    .go-table th.freeze-col-1 { background: #f8fafc; z-index: 15; }
    .go-table th.freeze-col-2 { background: #f8fafc; z-index: 15; }

    .tag-tipe {
        background: var(--menu-primary-light);
        color: var(--menu-primary);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 800;
    }
    .text-money {
        color: var(--menu-green);
        font-weight: 700;
    }
    .text-dim {
        color: #94a3b8;
    }
    .day-col {
        text-align: center !important;
        font-family: monospace;
    }
</style>

<div class="menu-page">
    <section class="menu-hero">
        <div class="menu-kicker"><i class="bi bi-shop"></i> Analitik Existing</div>
        <h1 class="menu-title">Dashboard Outlet Existing</h1>
        <p class="menu-subtitle">Pemantauan data penjualan harian untuk Outlet berstatus Existing. Data ditarik secara optimal dari ringkasan laporan untuk bulan {{ $bulanInfo }}.</p>
    </section>

    <!-- Filter Bar -->
    <section class="filter-section" style="margin-bottom: 20px;">
        <form method="GET" style="display: flex; gap: 15px; background: rgba(255,255,255,0.8); backdrop-filter: blur(10px); padding: 15px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(16, 185, 129, 0.2);">
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary); font-weight: 800; display: block; margin-bottom: 5px;">Tahun</label>
                <select name="tahun" class="select2">
                    <option value="All">Semua Tahun</option>
                    @foreach($options['tahun'] ?? [] as $t)
                        <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary); font-weight: 800; display: block; margin-bottom: 5px;">Bulan</label>
                <select name="bulan" class="select2">
                    <option value="All">Semua Bulan</option>
                    @foreach($options['bulan'] ?? [] as $k => $v)
                        <option value="{{ $k }}" @selected($bulan == $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary); font-weight: 800; display: block; margin-bottom: 5px;">Mulai (Opsional)</label>
                <input type="date" name="start_date" value="{{ $startDate ?? '' }}" style="width: 100%; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif; color: var(--menu-dark); font-weight: 600;">
            </div>
            
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary); font-weight: 800; display: block; margin-bottom: 5px;">Akhir (Opsional)</label>
                <input type="date" name="end_date" value="{{ $endDate ?? '' }}" style="width: 100%; padding: 8px 10px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif; color: var(--menu-dark); font-weight: 600;">
            </div>
            <div style="flex: 1;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary); font-weight: 800; display: block; margin-bottom: 5px;">Cari Outlet / Kota</label>
                <div style="position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                    <input type="text" id="filterOutlet" placeholder="Ketik nama outlet..." style="width: 100%; padding: 8px 10px 8px 35px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; transition: 0.2s; font-family: 'Inter', sans-serif;">
                </div>
            </div>
            <div style="min-width:auto; display:flex; align-items: flex-end;">
                <button type="submit" style="background:var(--menu-primary); color:#fff; border:0; border-radius:8px; padding:10px 24px; font-weight:800; cursor:pointer; height:42px;">Filter</button>
            </div>
        </form>
    </section>

    <style>
        .trend-chart { height: 220px; display: flex; align-items: flex-end; gap: 8px; padding: 25px 0 30px; margin-bottom: 10px; overflow-x: auto; }
        .trend-bar { flex: 1; min-width: 35px; max-width: 60px; background: linear-gradient(180deg, #10b981, #059669); border-radius: 6px 6px 4px 4px; position: relative; transition: 0.3s; cursor: pointer; }
        .trend-bar:hover { background: linear-gradient(180deg, #34d399, #10b981); transform: translateY(-3px); box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); }
        .trend-bar span { position: absolute; bottom: -24px; left: 50%; transform: translateX(-50%); font-size: 10px; color: var(--menu-gray); white-space: nowrap; font-weight: 600; }
        .trend-bar b { position: absolute; top: -20px; left: 50%; transform: translateX(-50%); font-size: 10px; color: #fff; background: var(--menu-dark); white-space: nowrap; opacity: 0; transition: 0.2s; padding: 4px 8px; border-radius: 6px; pointer-events: none; z-index: 10; font-weight: 700; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .trend-bar:hover b { opacity: 1; top: -32px; }
        /* Scrollbar untuk chart */
        .trend-chart::-webkit-scrollbar { height: 6px; }
        .trend-chart::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        .trend-chart::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .trend-chart::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>

    <div class="menu-card" style="margin-bottom: 20px;">
        <div class="section-head">
            <h3>Tren Gabungan Omzet Harian - {{ $bulanInfo }}</h3>
            <span class="pill">Grafik Batang (All Outlet Existing)</span>
        </div>
        <div class="trend-chart">
            @foreach($trendData ?? [] as $trend)
                <div class="trend-bar" style="height: {{ $trend['height'] }}%;">
                    <b>{{ $trend['omzet_label'] }}</b>
                    <span>{{ $trend['tanggal'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="menu-card">
        <div class="section-head">
            <h3>Rekam Jejak Harian - {{ $bulanInfo }}</h3>
            <span class="pill">Realtime Database Data</span>
        </div>
        
        <div class="table-container">
            <table class="go-table">
                <thead>
                    <tr>
                        <th class="freeze-col-1" style="min-width: 220px; max-width: 220px;">Outlet</th>
                        <th class="freeze-col-2" style="min-width: 150px; max-width: 150px;">Kota</th>
                        <th>Status</th>
                        <th>Total Omset</th>
                        <th>Avg / Harian</th>
                        @foreach($dateColumns ?? [] as $dateStr)
                            <th class="day-col" style="white-space:nowrap;">{{ date('d/m', strtotime($dateStr)) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="outletGoTableBody">
                    @forelse($outlets as $outlet)
                        <tr class="outlet-row" 
                            data-outlet="{{ strtolower($outlet['outlet']) }}" 
                            data-kota="{{ strtolower($outlet['kota']) }}">
                            
                            <td class="freeze-col-1">
                                {{ $outlet['outlet'] }}
                            </td>
                            <td class="freeze-col-2 text-dim">
                                {{ $outlet['kota'] ?: '-' }}
                            </td>
                            <td>
                                <span class="tag-tipe">{{ $outlet['kategori'] }}</span>
                            </td>
                            <td class="text-money">{{ $outlet['total_omset'] ?: '-' }}</td>
                            <td style="font-weight: 600;">{{ $outlet['avg_harian'] ?: '-' }}</td>
                            
                            @php $prevRaw = null; @endphp
                            @foreach($dateColumns ?? [] as $dateStr)
                                @php
                                    $dataObj = $outlet['daily_sales'][$dateStr] ?? null;
                                    $val = $dataObj ? $dataObj['label'] : '-';
                                    $raw = $dataObj ? $dataObj['raw'] : 0;
                                    $isEmpty = ($val === '' || $val === '-');
                                    
                                    $trendHtml = '';
                                    if (!$isEmpty && $prevRaw !== null) {
                                        if ($raw > $prevRaw) {
                                            $trendHtml = '<i class="bi bi-arrow-up-short" style="color: #10b981; font-size: 16px; margin-left: 2px;"></i>';
                                        } elseif ($raw < $prevRaw) {
                                            $trendHtml = '<i class="bi bi-arrow-down-short" style="color: #ef4444; font-size: 16px; margin-left: 2px;"></i>';
                                        }
                                    }
                                    $prevRaw = $raw;
                                @endphp
                                <td class="day-col {{ $isEmpty ? 'text-dim' : 'text-money' }}">
                                    <div style="display: flex; align-items: center; justify-content: center;">
                                        <span>{{ $val }}</span>
                                        {!! $trendHtml !!}
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + count($dateColumns ?? []) }}" style="text-align: center; padding: 40px; color: #94a3b8;">
                                Belum ada data Outlet Existing untuk bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterOutlet = document.getElementById('filterOutlet');

    function applyFilters() {
        const queryOutlet = filterOutlet.value.toLowerCase().trim();

        document.querySelectorAll('#outletGoTableBody .outlet-row').forEach(row => {
            const outletData = row.getAttribute('data-outlet');
            const kotaData = row.getAttribute('data-kota');
            
            const matchOutlet = queryOutlet === '' || outletData.includes(queryOutlet) || kotaData.includes(queryOutlet);

            if (matchOutlet) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterOutlet.addEventListener('input', applyFilters);
});
</script>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });
    });
</script>
@endpush

@include('Temp.Investor.footer')

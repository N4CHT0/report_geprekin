@section('title', 'Dashboard Tracker Produk')
@section('breadcrumb', 'Marketing / Tracker Produk')

@include('Temp.Investor.header')
@include('Temp.Investor.partials.sidebar')

<style>
    :root {
        --menu-primary: #14b8a6;
        --menu-primary-dark: #0f766e;
        --menu-dark: #111827;
        --menu-muted: #6b7280;
        --menu-border: #e5e7eb;
        --menu-soft: #f0fdfa;
        --menu-green: #10b981;
        --menu-blue: #3b82f6;
    }
    .menu-page { min-height: calc(100vh - 70px); background: linear-gradient(180deg, #f0fdfa 0%, #f8fafc 55%, #ffffff 100%); padding: 24px; font-family: 'Inter', system-ui, sans-serif; }
    .menu-hero { position: relative; overflow: hidden; border-radius: 30px; padding: 28px; color: #fff; background: radial-gradient(circle at 15% 10%, rgba(255,255,255,.34), transparent 28%), linear-gradient(135deg, #0f766e, #14b8a6 52%, #2dd4bf); box-shadow: 0 26px 70px rgba(15, 118, 110, .22); margin-bottom: 25px; }
    .menu-hero:after { content: ''; position: absolute; right: -80px; top: -90px; width: 260px; height: 260px; border-radius: 999px; background: rgba(255,255,255,.18); }
    .menu-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.16); font-weight: 800; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; }
    .menu-title { margin: 14px 0 6px; font-size: 32px; font-weight: 900; letter-spacing: -.03em; }
    .menu-subtitle { margin: 0; max-width: 720px; color: rgba(255,255,255,.82); line-height: 1.6; }
    .menu-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 20px; margin-top: 18px; }
    .menu-card { background: rgba(255,255,255,.92); border: 1px solid rgba(229,231,235,.9); border-radius: 24px; box-shadow: 0 16px 45px rgba(15, 23, 42, .07); padding: 24px; }
    .span-3 { grid-column: span 3; } .span-4 { grid-column: span 4; } .span-6 { grid-column: span 6; } .span-8 { grid-column: span 8; } .span-12 { grid-column: span 12; }
    .kpi small { color: var(--menu-muted); font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi strong { display: block; margin-top: 10px; color: var(--menu-dark); font-size: 32px; font-weight: 900; }
    .kpi .badge-soft { display: inline-flex; margin-top: 12px; border-radius: 999px; padding: 7px 12px; background: var(--menu-soft); color: var(--menu-primary-dark); font-size: 12px; font-weight: 900; }
    .section-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 20px; }
    .section-head h3 { margin: 0; color: var(--menu-dark); font-weight: 900; font-size: 18px; }
    .pill { border-radius: 999px; background: #f3f4f6; color: #374151; padding: 7px 11px; font-weight: 800; font-size: 12px; white-space: nowrap; }
    .top-menu { display: grid; gap: 12px; }
    .top-menu-row { display: grid; grid-template-columns: 38px 1fr auto; gap: 12px; align-items: center; padding: 14px; border-radius: 17px; background: #f9fafb; border: 1px solid #eef2f7; transition: transform 0.2s; }
    .top-menu-row:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .rank { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 12px; color: #fff; font-weight: 900; font-size: 16px; background: linear-gradient(135deg, #14b8a6, #2dd4bf); }
    .top-menu-row b { color: var(--menu-dark); font-size: 15px; }
    .top-menu-row small { color: var(--menu-muted); font-size: 13px; }
    .metric { text-align: right; font-weight: 900; color: var(--menu-dark); font-size: 15px; }
    .table-wrap { overflow: auto; border-radius: 18px; border: 1px solid var(--menu-border); }
    .menu-table { width: 100%; border-collapse: collapse; min-width: 400px; background: #fff; }
    .menu-table th { text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; background: #f8fafc; color: var(--menu-gray); padding: 14px 16px; white-space: nowrap; border-bottom: 2px solid #e2e8f0; position: sticky; top: 0; }
    .menu-table td { border-bottom: 1px solid #edf2f7; padding: 14px 16px; color: #374151; font-weight: 700; font-size: 14px; }
    .menu-table tbody tr:last-child td { border-bottom: none; }
    .menu-table tbody tr:hover { background: #f0fdfa; }
    .money { color: var(--menu-green); font-weight: 900; }
    .qty { color: var(--menu-blue); font-weight: 900; }
    .chart-container { height: 400px; width: 100%; position: relative; }
    .product-separator { grid-column: span 12; margin-top: 30px; margin-bottom: 10px; display: flex; align-items: center; gap: 15px; }
    .product-separator h2 { font-weight: 900; color: var(--menu-dark); margin: 0; font-size: 24px; }
    .product-separator .line { flex: 1; height: 2px; background: linear-gradient(90deg, var(--menu-primary) 0%, transparent 100%); border-radius: 2px; opacity: 0.3; }
    
    @media(max-width: 1100px){ .span-3{grid-column:span 6} .span-4,.span-6,.span-8{grid-column:span 12} }
    @media(max-width: 700px){ .menu-page{padding:14px}.menu-title{font-size:26px} }
</style>

<div class="menu-page">
    <section class="menu-hero">
        <div class="menu-kicker"><i class="bi bi-star-fill"></i> New Release Tracker</div>
        <h1 class="menu-title">Dashboard Tracker Produk</h1>
        <p class="menu-subtitle">Pantau performa berbagai menu secara bersamaan dengan rentang waktu yang spesifik. Bandingkan penjualan harian dan temukan outlet mana yang menjadi pahlawan bagi setiap menu.</p>
    </section>

    <!-- Filter Bar -->
    <section class="filter-section" style="margin-bottom: 25px;">
        <form method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; background: rgba(255,255,255,0.8); backdrop-filter: blur(10px); padding: 20px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(20, 184, 166, 0.2);">
            
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary-dark); font-weight: 800; display: block; margin-bottom: 8px;">Tahun</label>
                <select name="tahun" class="select2">
                    <option value="All">Semua Tahun</option>
                    @foreach($options['tahun'] ?? [] as $t)
                        <option value="{{ $t }}" @selected(($filters['tahun'] ?? date('Y')) == $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary-dark); font-weight: 800; display: block; margin-bottom: 8px;">Bulan</label>
                <select name="bulan" class="select2">
                    <option value="All">Semua Bulan</option>
                    @foreach($options['bulan'] ?? [] as $b)
                        <option value="{{ $b }}" @selected(($filters['bulan'] ?? 'All') == $b)>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary-dark); font-weight: 800; display: block; margin-bottom: 8px;">Mulai (Opsional)</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif; color: var(--menu-dark); font-weight: 600;">
            </div>
            
            <div style="flex: 1; min-width: 140px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary-dark); font-weight: 800; display: block; margin-bottom: 8px;">Akhir (Opsional)</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif; color: var(--menu-dark); font-weight: 600;">
            </div>

            <div style="flex: 3; min-width: 300px;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--menu-primary-dark); font-weight: 800; display: block; margin-bottom: 8px;">Pilih Produk (Bisa lebih dari 1)</label>
                <select name="produk[]" class="select2" multiple="multiple">
                    @foreach($options['produk'] ?? [] as $produk)
                        <option value="{{ $produk }}" @selected(in_array($produk, $filters['produk']))>{{ $produk }}</option>
                    @endforeach
                </select>
            </div>

            <div style="min-width:auto; display:flex; align-items: flex-end;">
                <button type="submit" style="background:linear-gradient(135deg, #0f766e, #14b8a6); color:#fff; border:0; border-radius:8px; padding:10px 28px; font-weight:800; font-size: 14px; cursor:pointer; height:44px; box-shadow: 0 4px 10px rgba(20, 184, 166, 0.3); transition: transform 0.2s;"><i class="bi bi-funnel-fill" style="margin-right: 6px;"></i> Terapkan</button>
            </div>
        </form>
    </section>

    <section class="menu-grid">
        
        <!-- Grafik Komparasi -->
        @if(count($productStats) > 0)
        <div class="menu-card span-12">
            <div class="section-head">
                <div>
                    <h3>Grafik Perbandingan Tren Penjualan Harian (Qty)</h3>
                    <p style="margin: 5px 0 0; font-size: 13px; color: var(--menu-muted);">Melihat pergerakan volume penjualan dari produk yang Anda pilih di rentang waktu ini.</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="comparisonChart"></canvas>
            </div>
        </div>
        @else
        <div class="menu-card span-12">
            <p style="text-align: center; color: var(--menu-muted); padding: 40px;">Belum ada produk yang dipilih atau tidak ada data pada rentang waktu tersebut.</p>
        </div>
        @endif

        <!-- Looping per produk -->
        @foreach($productStats as $stat)
            <div class="product-separator">
                <h2>{{ strtoupper($stat['name']) }}</h2>
                @if($stat['is_winner'])
                    <span style="margin-left:12px; background:#f59e0b; color:#fff; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:800; display:inline-flex; align-items:center; vertical-align: middle;"><i class="bi bi-trophy-fill" style="margin-right:4px;"></i> BEST SELLER</span>
                @endif
                <div class="line"></div>
            </div>

            <div class="menu-card kpi span-3">
                <small>Total Volume Qty</small>
                <strong>{{ number_format($stat['totalQty'], 0, ',', '.') }}</strong>
                <span class="badge-soft"><i class="bi bi-box-seam" style="margin-right:4px;"></i> {{ $stat['name'] }}</span>
            </div>
            <div class="menu-card kpi span-3">
                <small>Rata-rata Qty / Hari</small>
                <strong>{{ number_format($stat['avgQty'], 0, ',', '.') }}</strong>
                <span class="badge-soft"><i class="bi bi-graph-up" style="margin-right:4px;"></i> Avg Harian</span>
            </div>
            <div class="menu-card kpi span-3">
                <small>Total Omzet Revenue</small>
                <strong>Rp{{ $stat['totalOmzet'] }}</strong>
                <span class="badge-soft"><i class="bi bi-cash-stack" style="margin-right:4px;"></i> {{ $stat['name'] }}</span>
            </div>
            <div class="menu-card kpi span-3">
                <small>Rata-rata Omzet / Hari</small>
                <strong>Rp{{ $stat['avgOmzet'] }}</strong>
                <span class="badge-soft"><i class="bi bi-wallet2" style="margin-right:4px;"></i> Avg Harian</span>
            </div>

            <div class="menu-card span-6">
                <div class="section-head">
                    <h3>Top 5 Outlet Pahlawan</h3>
                    <span class="pill">Berdasarkan Qty Tertinggi</span>
                </div>
                <div class="top-menu">
                    @forelse($stat['topOutlets'] as $index => $outlet)
                        <div class="top-menu-row">
                            <div class="rank">{{ $index + 1 }}</div>
                            <div><b>{{ $outlet['nama'] }}</b><br><small><i class="bi bi-geo-alt-fill"></i> {{ $outlet['area'] }}</small></div>
                            <div class="metric">{{ number_format((int)$outlet['qty'], 0, ',', '.') }} <small>qty</small><br><span style="color:#10b981">Rp{{ $outlet['omzet'] }}</span></div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--menu-muted); padding: 20px;">Belum ada data penjualan outlet.</div>
                    @endforelse
                </div>
            </div>

            <div class="menu-card span-6">
                <div class="section-head">
                    <h3>Tabel Tren Harian</h3>
                    <span class="pill">Rincian Angka</span>
                </div>
                <div class="table-wrap" style="max-height: 480px;">
                    <table class="menu-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Volume Qty</th>
                                <th>Omzet Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $prevQty = null; $prevOmzet = null; @endphp
                            @forelse($stat['dailyTrends'] as $trend)
                                @php
                                    $qtyTrend = '';
                                    if ($prevQty !== null) {
                                        if ($trend['qty'] > $prevQty) $qtyTrend = '<i class="bi bi-arrow-up-short" style="color: #10b981; font-size: 16px;"></i>';
                                        elseif ($trend['qty'] < $prevQty) $qtyTrend = '<i class="bi bi-arrow-down-short" style="color: #ef4444; font-size: 16px;"></i>';
                                        else $qtyTrend = '<i class="bi bi-dash" style="color: #94a3b8; font-size: 16px;"></i>';
                                    }
                                    $prevQty = $trend['qty'];

                                    $omzetTrend = '';
                                    if ($prevOmzet !== null) {
                                        if ($trend['omzet_raw'] > $prevOmzet) $omzetTrend = '<i class="bi bi-arrow-up-short" style="color: #10b981; font-size: 16px;"></i>';
                                        elseif ($trend['omzet_raw'] < $prevOmzet) $omzetTrend = '<i class="bi bi-arrow-down-short" style="color: #ef4444; font-size: 16px;"></i>';
                                        else $omzetTrend = '<i class="bi bi-dash" style="color: #94a3b8; font-size: 16px;"></i>';
                                    }
                                    $prevOmzet = $trend['omzet_raw'];
                                @endphp
                                <tr>
                                    <td>{{ $trend['tgl'] }} {{ $trend['bulan'] }}</td>
                                    <td class="qty" style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                                        <span>{{ number_format($trend['qty'], 0, ',', '.') }}</span>
                                        <span>{!! $qtyTrend !!}</span>
                                    </td>
                                    <td class="money">
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                                            <span>Rp{{ $trend['omzet'] }}</span>
                                            <span>{!! $omzetTrend !!}</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center; padding: 20px;">Belum ada data harian.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

    </section>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Init Select2 for multiple selection
        $('.select2').select2({ 
            width: '100%',
            placeholder: "Pilih produk...",
            allowClear: true
        });
    });
</script>

@if(count($productStats) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('comparisonChart').getContext('2d');
    
    // Parse chart data from backend
    const chartDataRaw = @json($chartData);
    
    new Chart(ctx, {
        type: 'line',
        data: chartDataRaw,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            family: "'Inter', sans-serif",
                            weight: '600'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    titleFont: { family: "'Inter', sans-serif", size: 14 },
                    bodyFont: { family: "'Inter', sans-serif", size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: "'Inter', sans-serif" } }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: { 
                        font: { family: "'Inter', sans-serif" },
                        stepSize: 1
                    },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endif
@endpush

@include('Temp.Investor.footer')

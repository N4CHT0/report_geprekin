@include('Temp.Investor.header')
@include('Temp.Investor.partials.sidebar')

<style>
    :root {
        --komp-primary: #f97316; /* Orange */
        --komp-primary-light: #ffedd5;
        --komp-dark: #1e293b;
        --komp-gray: #64748b;
        --komp-light: #f8fafc;
        --color-geprekin: #dc2626; /* Red for Geprekin */
        --color-berbrand: #fde047; /* Yellow for Berbrand */
        --color-lainnya: #f97316; /* Orange for Lainnya */
    }
    .menu-page {
        padding: 30px;
        background: #f1f5f9;
        min-height: 100vh;
        font-family: 'Inter', system-ui, sans-serif;
    }
    .menu-hero {
        background: linear-gradient(135deg, var(--komp-primary), #fb923c);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 25px;
        box-shadow: 0 10px 25px rgba(249, 115, 22, 0.2);
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
    
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }
    .kpi-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        border: 1px solid rgba(249, 115, 22, 0.15);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s;
    }
    .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(249, 115, 22, 0.1); }
    .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(90deg, var(--komp-primary), #fb923c); }
    .kpi-card small {
        color: var(--komp-gray);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
        font-size: 12px;
        display: flex; align-items: center; gap: 6px;
    }
    .kpi-card h2 {
        font-size: 42px;
        font-weight: 900;
        color: var(--komp-dark);
        margin: 0;
        background: linear-gradient(135deg, var(--komp-dark), var(--komp-primary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .provinsi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 25px;
    }
    .prov-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        border: 1px solid rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .prov-header {
        background: var(--komp-light);
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 800;
        font-size: 16px;
        color: var(--komp-dark);
    }
    .prov-body {
        display: flex;
        padding: 20px;
        gap: 20px;
        align-items: center;
    }
    .prov-table-container {
        flex: 1;
    }
    .prov-chart-container {
        flex: 1;
        position: relative;
        height: 200px;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .komp-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    .komp-table th {
        background: var(--komp-light);
        color: var(--komp-gray);
        font-weight: 800;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 12px 15px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
    }
    .komp-table th:last-child { text-align: right; }
    .komp-table td {
        padding: 14px 15px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        font-weight: 700;
        color: var(--komp-dark);
        vertical-align: middle;
    }
    .komp-table td:last-child {
        text-align: right;
        font-family: 'Inter', sans-serif;
        font-size: 15px;
    }
    .brand-dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 4px;
        margin-right: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .progress-bar-bg {
        width: 100%;
        height: 6px;
        background: #e2e8f0;
        border-radius: 999px;
        margin-top: 6px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
    }

</style>

<!-- Sertakan Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="menu-page">
    <section class="menu-hero">
        <div class="menu-kicker"><i class="bi bi-pie-chart-fill"></i> Analisis Pasar</div>
        <h1 class="menu-title">Dashboard Kompetitor</h1>
        <p class="menu-subtitle" style="font-size: 15px; opacity: 0.9; margin: 0; max-width: 600px; line-height: 1.6;">Pemantauan persebaran cabang dan *market share* antara GeprekinAja dengan kompetitor ber-brand maupun lainnya di 4 Provinsi Utama.</p>
    </section>

    <!-- KPI Section -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <small><i class="bi bi-shop" style="color: var(--komp-primary); font-size: 16px;"></i> Total Geprekin</small>
            <h2>{{ $kpi['total_geprekin'] }}</h2>
        </div>
        <div class="kpi-card">
            <small><i class="bi bi-building" style="color: var(--komp-gray); font-size: 16px;"></i> Total Kompetitor</small>
            <h2>{{ $kpi['total_kompetitor'] }}</h2>
        </div>
        <div class="kpi-card" style="background: linear-gradient(135deg, var(--komp-primary), #fb923c);">
            <small style="color: rgba(255,255,255,0.9);"><i class="bi bi-pie-chart" style="color: #fff; font-size: 16px;"></i> Market Share</small>
            <h2 style="color: #fff; background: none; -webkit-text-fill-color: #fff;">{{ $kpi['market_share'] }}</h2>
        </div>
    </div>

    <!-- Grid Data Provinsi -->
    <div class="provinsi-grid">
        @foreach($provinsiData as $index => $prov)
            @php
                $total = $prov['geprekin'] + $prov['berbrand'] + $prov['lainnya'];
                $pctGeprekin = $total > 0 ? ($prov['geprekin'] / $total) * 100 : 0;
                $pctBerbrand = $total > 0 ? ($prov['berbrand'] / $total) * 100 : 0;
                $pctLainnya = $total > 0 ? ($prov['lainnya'] / $total) * 100 : 0;
            @endphp
            <div class="prov-card">
                <div class="prov-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="bi bi-geo-alt-fill" style="color: var(--komp-primary); margin-right: 8px;"></i> {{ $prov['name'] }}</span>
                    <span style="font-size: 12px; font-weight: 700; color: var(--komp-gray); background: var(--komp-light); padding: 4px 10px; border-radius: 999px;">Total: {{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <div class="prov-body">
                    <div class="prov-table-container">
                        <table class="komp-table">
                            <thead>
                                <tr>
                                    <th>Brand</th>
                                    <th>Cabang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center;"><span class="brand-dot" style="background: var(--color-geprekin);"></span> Geprekin Aja</div>
                                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: {{ $pctGeprekin }}%; background: var(--color-geprekin);"></div></div>
                                    </td>
                                    <td>{{ number_format($prov['geprekin'], 0, ',', '.') }}<br><small style="color: var(--komp-gray); font-size: 11px;">{{ number_format($pctGeprekin, 1, ',', '.') }}%</small></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center;"><span class="brand-dot" style="background: var(--color-berbrand);"></span> Berbrand</div>
                                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: {{ $pctBerbrand }}%; background: var(--color-berbrand);"></div></div>
                                    </td>
                                    <td>{{ number_format($prov['berbrand'], 0, ',', '.') }}<br><small style="color: var(--komp-gray); font-size: 11px;">{{ number_format($pctBerbrand, 1, ',', '.') }}%</small></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center;"><span class="brand-dot" style="background: var(--color-lainnya);"></span> Lainnya</div>
                                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: {{ $pctLainnya }}%; background: var(--color-lainnya);"></div></div>
                                    </td>
                                    <td>{{ number_format($prov['lainnya'], 0, ',', '.') }}<br><small style="color: var(--komp-gray); font-size: 11px;">{{ number_format($pctLainnya, 1, ',', '.') }}%</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="prov-chart-container">
                        <canvas id="chart-{{ $index }}"></canvas>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinsiData = @json($provinsiData);
    const colors = ['#dc2626', '#fde047', '#f97316']; 

    // Custom plugin to draw text in the middle of doughnut chart
    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw: function(chart) {
            if (chart.config.type !== 'doughnut') return;
            var width = chart.width,
                height = chart.height,
                ctx = chart.ctx;

            ctx.restore();
            var fontSize = (height / 140).toFixed(2);
            ctx.font = "900 " + fontSize + "em Inter";
            ctx.textBaseline = "middle";
            ctx.fillStyle = "#1e293b";

            var total = chart.config.data.datasets[0].data.reduce((a, b) => a + b, 0);
            var text = total.toLocaleString('id-ID'),
                textX = Math.round((width - ctx.measureText(text).width) / 2),
                textY = height / 2 + 5;

            ctx.fillText(text, textX, textY);
            
            ctx.font = "800 " + (fontSize * 0.4) + "em Inter";
            ctx.fillStyle = "#64748b";
            var text2 = "TOTAL",
                text2X = Math.round((width - ctx.measureText(text2).width) / 2),
                text2Y = height / 2 - 15;
            ctx.fillText(text2, text2X, text2Y);
            
            ctx.save();
        }
    };

    Chart.register(centerTextPlugin);

    provinsiData.forEach((prov, index) => {
        const ctx = document.getElementById('chart-' + index).getContext('2d');
        const total = prov.geprekin + prov.berbrand + prov.lainnya;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Geprekin Aja', 'Berbrand', 'Lainnya'],
                datasets: [{
                    data: [prov.geprekin, prov.berbrand, prov.lainnya],
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(30, 41, 59, 0.95)',
                        titleFont: { family: "'Inter', sans-serif", size: 13 },
                        bodyFont: { family: "'Inter', sans-serif", size: 14, weight: 'bold' },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                const value = context.raw;
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                label += value.toLocaleString('id-ID') + ' cabang (' + percentage + ')';
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
});
</script>

@include('Temp.Investor.footer')

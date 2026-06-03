@include('Temp.internal.header_internal')

@php
    function formatLabMoney($angka) {
        $angka = (float) $angka;

        if ($angka >= 1000000000) {
            return number_format($angka / 1000000000, 3, '.', '') . 'M';
        } elseif ($angka >= 1000000) {
            return number_format($angka / 1000000, 2, '.', '') . 'JT';
        } elseif ($angka >= 1000) {
            return number_format($angka / 1000, 2, '.', '') . 'RB';
        }

        return number_format($angka, 0, '.', ',');
    }
@endphp

<style>
    :root{
        --lab-bg:#07101d;
        --lab-bg2:#0a1526;
        --lab-panel:#0b1830;
        --lab-panel2:#091221;
        --lab-line:rgba(148,163,184,.14);
        --lab-line-soft:rgba(148,163,184,.08);
        --lab-text:#e5eefc;
        --lab-muted:#93a4bd;
        --lab-green:#2f8d35;
        --lab-red:#c9221d;
        --lab-blue:#0d67b5;
        --lab-yellow:#bfae58;
        --lab-shadow:0 16px 40px rgba(0,0,0,.35);
    }

    .lab-page{
        width:100%;
        min-height:100vh;
        padding:20px;
        background:
            radial-gradient(circle at top left, rgba(59,130,246,.12), transparent 22%),
            radial-gradient(circle at top right, rgba(6,182,212,.10), transparent 20%),
            linear-gradient(180deg, #07101d 0%, #0a1526 100%);
        color:var(--lab-text);
    }

    .lab-board{
        border-radius:26px;
        padding:18px;
        background:linear-gradient(180deg, rgba(10,21,38,.98), rgba(6,12,23,.99));
        border:1px solid var(--lab-line);
        box-shadow:var(--lab-shadow);
    }

    .lab-topbar{
        display:grid;
        grid-template-columns:240px minmax(0, 1fr) 220px;
        gap:18px;
        align-items:center;
        margin-bottom:18px;
    }

    .lab-brand-box{ display:flex; align-items:center; justify-content:flex-start; min-height:86px; }
    .lab-brand-pill{
        display:inline-flex;
        align-items:center;
        padding:12px 16px;
        border-radius:18px;
        background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
        border:1px solid var(--lab-line);
        box-shadow:var(--lab-shadow);
    }

    .lab-brand-pill img{
        max-height:52px;
        width:auto;
        max-width:100%;
        display:block;
        object-fit:contain;
    }

    .lab-header-box{ text-align:center; }
    .lab-header-label{
        color:#b9c8dc;
        font-size:13px;
        font-weight:800;
        letter-spacing:.1em;
        text-transform:uppercase;
        margin-bottom:8px;
    }

    .lab-header-title{
        color:#f8fbff;
        font-size:clamp(22px, 2.4vw, 30px);
        font-weight:900;
        letter-spacing:-.02em;
        line-height:1.1;
        word-break:break-word;
    }

    .lab-header-sub{
        margin-top:6px;
        color:var(--lab-muted);
        font-size:12px;
    }

    .lab-filter-box form{
        display:flex;
        flex-direction:column;
        gap:10px;
    }

    .lab-filter-box select{
        width:100%;
        height:42px;
        border-radius:12px;
        border:1px solid var(--lab-line);
        background:rgba(255,255,255,.04);
        color:#e5eefc;
        padding:0 14px;
        font-size:13px;
        outline:none;
    }

    .lab-filter-box option{ color:#111; }

    .lab-section-title{
        display:flex;
        justify-content:center;
        align-items:center;
        margin-bottom:16px;
    }

    .lab-section-title span{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:100%;
        min-height:42px;
        padding:10px 14px;
        border-radius:14px;
        border:1px solid rgba(239,68,68,.30);
        color:#f8fbff;
        background:linear-gradient(180deg, rgba(239,68,68,.08), rgba(239,68,68,.03));
        font-size:15px;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        text-align:center;
    }

    .lab-main-grid{
        display:grid;
        grid-template-columns:280px minmax(0, 1fr);
        gap:16px;
        align-items:start;
        margin-bottom:18px;
    }

    .lab-kpi-stack{ display:grid; gap:10px; }

    .lab-kpi-box{
        overflow:hidden;
        border:1px solid rgba(255,255,255,.06);
        border-radius:14px;
        background:linear-gradient(180deg, rgba(17,24,39,.96), rgba(9,14,27,.98));
        box-shadow:var(--lab-shadow);
    }

    .lab-kpi-title{
        text-align:center;
        font-size:11px;
        font-weight:800;
        text-transform:uppercase;
        color:#fff;
        padding:6px 10px;
        background:#2f8d35;
    }

    .lab-kpi-box.blue .lab-kpi-title{ background:#0d67b5; }
    .lab-kpi-box.yellow .lab-kpi-title{ background:#bfae58; color:#111; }

    .lab-kpi-number{
        color:#f8fbff;
        font-size:18px;
        font-weight:800;
        text-align:center;
        padding:18px 10px;
        background:transparent;
    }

    .lab-ratio-card{
        min-height:226px;
        border-radius:18px;
        border:1px solid var(--lab-line);
        background:
            radial-gradient(circle at center, rgba(239,68,68,.08), transparent 55%),
            linear-gradient(180deg, rgba(17,24,39,.96), rgba(9,14,27,.98));
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow:var(--lab-shadow);
        position:relative;
        overflow:hidden;
    }

    .lab-ratio-card::before{
        content:"LABOUR COST";
        position:absolute;
        inset:auto auto 18px 18px;
        font-size:14px;
        font-weight:800;
        color:rgba(255,255,255,.07);
        letter-spacing:.08em;
        text-transform:uppercase;
    }

    .lab-ratio-value{
        position:relative;
        z-index:2;
        font-size:56px;
        line-height:1;
        font-weight:800;
        color:#f8fbff;
    }

    .lab-table-card,
    .lab-chart-card{
        background:linear-gradient(180deg, rgba(17,24,39,.92), rgba(8,13,24,.96));
        border:1px solid var(--lab-line);
        border-radius:20px;
        box-shadow:var(--lab-shadow);
    }

    .lab-table-card{
        overflow:hidden;
        margin-bottom:18px;
    }

    .lab-table-wrap{
        overflow:auto;
        max-height:420px;
    }

    .lab-table{
        width:100%;
        border-collapse:collapse;
        min-width:720px;
        background:transparent;
        color:var(--lab-text);
    }

    .lab-table thead th{
        position:sticky;
        top:0;
        z-index:2;
        background:#cf1f1f;
        color:#fff;
        font-size:12px;
        padding:8px 10px;
        text-align:left;
        border-right:1px solid rgba(255,255,255,.12);
    }

    .lab-table tbody td{
        font-size:12px;
        padding:8px 10px;
        border-bottom:1px solid rgba(148,163,184,.10);
        white-space:nowrap;
        background:rgba(255,255,255,.02);
        color:#e5eefc;
    }

    .lab-table tbody tr:nth-child(even) td{
        background:rgba(255,255,255,.04);
    }

    .lab-status-good,
    .lab-status-bad{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:72px;
        padding:6px 10px;
        border-radius:999px;
        font-size:11px;
        font-weight:800;
    }

    .lab-status-good{
        color:#dcfce7;
        background:rgba(34,197,94,.15);
        border:1px solid rgba(34,197,94,.25);
    }

    .lab-status-bad{
        color:#fee2e2;
        background:rgba(239,68,68,.15);
        border:1px solid rgba(239,68,68,.25);
    }

    .lab-chart-stack{ display:grid; gap:18px; }
    .lab-chart-card{ padding:16px 18px 10px; }

    .lab-chart-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        margin-bottom:12px;
        flex-wrap:wrap;
    }

    .lab-chart-title{
        font-size:15px;
        font-weight:800;
        color:#f3f8ff;
        margin:0;
        letter-spacing:.02em;
    }

    .lab-chart-sub{
        margin-top:4px;
        font-size:12px;
        color:var(--lab-muted);
    }

    .lab-chart-chip{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:7px 11px;
        border-radius:999px;
        font-size:11px;
        font-weight:800;
        color:#9fc2ff;
        background:rgba(59,130,246,.10);
        border:1px solid rgba(59,130,246,.18);
        white-space:nowrap;
    }

    .lab-canvas-wrap{
        position:relative;
        height:260px;
        width:100%;
    }

    .lab-canvas-wrap canvas{
        width:100% !important;
        height:100% !important;
        display:block;
    }

    .lab-footer{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        margin-top:16px;
        color:#7286a4;
        font-size:11px;
        flex-wrap:wrap;
    }

    @media (max-width:1100px){
        .lab-topbar{
            grid-template-columns:1fr;
            text-align:center;
        }

        .lab-brand-box{
            justify-content:center;
        }

        .lab-filter-box{
            max-width:260px;
            margin:0 auto;
        }

        .lab-main-grid{
            grid-template-columns:1fr;
        }
    }

    @media (max-width:700px){
        .lab-page{ padding:14px; }
        .lab-board{ padding:14px; }
        .lab-header-title{ font-size:20px; }
        .lab-canvas-wrap{ height:220px; }
        .lab-ratio-value{ font-size:40px; }
    }

    @media (max-width:576px){
        .lab-page{ padding:12px; }
        .lab-board{ border-radius:20px; }
        .lab-chart-card{ padding:14px 14px 10px; border-radius:16px; }
        .lab-chart-sub,.lab-header-sub{ font-size:11px; }
        .lab-footer{ align-items:flex-start; flex-direction:column; }
        .lab-table{ min-width:620px; }
    }
    .lab-status-good,
    .lab-status-bad{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:72px;
        padding:6px 10px;
        border-radius:999px;
        font-size:11px;
        font-weight:800;
    }

    .lab-status-good{
        color:#dcfce7;
        background:rgba(34,197,94,.15);
        border:1px solid rgba(34,197,94,.25);
    }

    .lab-status-bad{
        color:#fee2e2;
        background:rgba(239,68,68,.15);
        border:1px solid rgba(239,68,68,.25);
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">
            <div class="lab-page">
                <div class="lab-board">
                    <div class="lab-topbar">
                        <div class="lab-brand-box">
                            <div class="lab-brand-pill">
                                <img src="{{ asset('img/logo2.jpg') }}" alt="Logo Geprekin">
                            </div>
                        </div>

                        <div class="lab-header-box">
                            <div class="lab-header-label">Board Analytics</div>
                            <div class="lab-header-title">Labour Cost Dashboard</div>
                            <div class="lab-header-sub">Dark executive view • data source sheet Recap LC</div>
                        </div>

                        <div class="lab-filter-box">
                            <form method="GET" action="{{ route('investor.sales.dashboardBOD.labourCost') }}">
                                <select name="tahun" onchange="this.form.submit()">
                                    <option value="">Tahun</option>
                                    @foreach(($availableYears ?? []) as $year)
                                        <option value="{{ $year }}" {{ (string)($filters['tahun'] ?? '') === (string)$year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="bulan" onchange="this.form.submit()">
                                    <option value="">Bulan</option>
                                    @foreach(($monthLabels ?? []) as $index => $monthLabel)
                                        <option value="{{ $index + 1 }}" {{ (string)($filters['bulan'] ?? '') === (string)($index + 1) ? 'selected' : '' }}>
                                            {{ $monthLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="lab-section-title">
                        <span>Control Labour Cost</span>
                    </div>

                    <div class="lab-main-grid">
                        <div class="lab-kpi-stack">
                            <div class="lab-kpi-box">
                                <div class="lab-kpi-title">Omset</div>
                                <div class="lab-kpi-number">{{ formatLabMoney($kpi['omset'] ?? 0) }}</div>
                            </div>

                            <div class="lab-kpi-box blue">
                                <div class="lab-kpi-title">Manload</div>
                                <div class="lab-kpi-number">{{ number_format($kpi['manload'] ?? 0, 0, ',', '.') }}</div>
                            </div>

                            <div class="lab-kpi-box yellow">
                                <div class="lab-kpi-title">Est Salary</div>
                                <div class="lab-kpi-number">{{ formatLabMoney($kpi['est_salary'] ?? 0) }}</div>
                            </div>
                        </div>

                        <div class="lab-ratio-card">
                            <div class="lab-ratio-value">{{ number_format($kpi['labour_ratio'] ?? 0, 3, '.', '') }}%</div>
                        </div>
                    </div>

                    <div class="lab-table-card">
                        <div class="lab-table-wrap">
                            <table class="lab-table">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th>Omset</th>
                                        <th>Jumlah Crew</th>
                                        <th>Est Gaji</th>
                                        <th>% Ratio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($monthlyTable ?? [] as $row)
                                        <tr>
                                            <td>{{ $row['bulan'] }}</td>
                                            <td>Rp{{ formatLabMoney($row['omset'] ?? 0) }}</td>
                                            <td>{{ number_format($row['jumlah'] ?? 0, 0, ',', '.') }}</td>
                                            <td>Rp{{ formatLabMoney($row['est_gaji'] ?? 0) }}</td>
                                            <td style="font-weight:700; color:{{ ($row['persen'] ?? 0) < 8.5 ? '#22c55e' : '#ef4444' }}">
                                                {{ number_format($row['persen'] ?? 0, 3, '.', '') }}%
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="text-align:center;">Data labour cost belum tersedia</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="lab-table-card">
                        <div style="padding:14px 16px 0;">
                            <div class="lab-chart-head" style="margin-bottom:10px;">
                                <div>
                                    <p class="lab-chart-title">Labour Cost per TM</p>
                                    <div class="lab-chart-sub">Monitoring ratio labour cost tiap TM per bulan</div>
                                </div>
                                <div class="lab-chart-chip">TM CONTROL</div>
                            </div>
                        </div>

                        <div class="lab-table-wrap">
                            <table class="lab-table" style="min-width:1450px;">
                                <thead>
                                    <tr>
                                        <th>TM</th>
                                        <th>Jan</th>
                                        <th>Feb</th>
                                        <th>Mar</th>
                                        <th>Apr</th>
                                        <th>Mei</th>
                                        <th>Jun</th>
                                        <th>Jul</th>
                                        <th>Agu</th>
                                        <th>Sep</th>
                                        <th>Okt</th>
                                        <th>Nov</th>
                                        <th>Des</th>
                                        <th>Avg Ratio</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tmTable ?? [] as $row)
                                        <tr>
                                            <td style="font-weight:800;">{{ $row['tm'] ?? '-' }}</td>

                                            @foreach(($row['months'] ?? []) as $ratio)
                                                <td style="font-weight:700; color:{{ ($ratio ?? 0) < 8.5 ? '#22c55e' : '#ef4444' }};">
                                                    {{ number_format($ratio ?? 0, 3, '.', '') }}%
                                                </td>
                                            @endforeach

                                            <td style="font-weight:800; color:#f8fbff;">
                                                {{ number_format($row['avg_ratio'] ?? 0, 3, '.', '') }}%
                                            </td>

                                            <td>
                                                @if(($row['status'] ?? '') === 'Good')
                                                    <span class="lab-status-good">Good</span>
                                                @else
                                                    <span class="lab-status-bad">Bad</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" style="text-align:center;">Data labour cost per TM belum tersedia</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="lab-chart-stack">
                        <div class="lab-chart-card">
                            <div class="lab-chart-head">
                                <div>
                                    <p class="lab-chart-title">Est Gaji by Month</p>
                                    <div class="lab-chart-sub">Perbandingan est gaji antar tahun</div>
                                </div>
                                <div class="lab-chart-chip">EST GAJI</div>
                            </div>
                            <div class="lab-canvas-wrap">
                                <canvas id="labourCostChart"></canvas>
                            </div>
                        </div>

                        <div class="lab-chart-card">
                            <div class="lab-chart-head">
                                <div>
                                    <p class="lab-chart-title">% Ratio by Month</p>
                                    <div class="lab-chart-sub">Persentase est gaji terhadap omset</div>
                                </div>
                                <div class="lab-chart-chip">RATIO VIEW</div>
                            </div>
                            <div class="lab-canvas-wrap">
                                <canvas id="labourRatioChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="lab-footer">
                        <span>Data Last Updated: {{ $lastSyncAt ?? now()->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labourMonthLabels = @json($monthLabels ?? []);
    const labourCostData = @json($labourCostData ?? []);
    const labourRatioData = @json($labourRatioData ?? []);
    const labourPalette = ['#4b84ea', '#f2a24a', '#aa83e6', '#22c55e', '#ef4444', '#06b6d4'];

    Chart.defaults.font.family = 'Inter, Arial, sans-serif';
    Chart.defaults.color = '#94a3b8';

    const isLabMobile = () => window.matchMedia('(max-width: 576px)').matches;

    function formatCompactNumber(value) {
        const number = Number(value || 0);
        if (number >= 1000000000) return (number / 1000000000).toFixed(2).replace('.00', '') + 'M';
        if (number >= 1000000) return (number / 1000000).toFixed(2).replace('.00', '') + 'JT';
        if (number >= 1000) return (number / 1000).toFixed(2).replace('.00', '') + 'RB';
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function buildBarDatasets(source, prefix) {
        return Object.keys(source || {}).map((year, index) => ({
            label: `${prefix} ${year}`,
            data: source[year] || [],
            originalLabels: (source[year] || []).map(v => Number(v || 0) === 0 ? '' : formatCompactNumber(v)),
            backgroundColor: labourPalette[index % labourPalette.length],
            borderRadius: 4,
            categoryPercentage: 0.62,
            barPercentage: 0.84
        }));
    }

    function buildLineDatasets(source, prefix) {
        return Object.keys(source || {}).map((year, index) => ({
            label: `${prefix} ${year}`,
            data: source[year] || [],
            originalLabels: (source[year] || []).map(v => Number(v || 0) === 0 ? '' : `${Number(v).toFixed(3)}%`),
            borderColor: labourPalette[index % labourPalette.length],
            backgroundColor: labourPalette[index % labourPalette.length],
            tension: 0.35,
            fill: false,
            pointRadius: isLabMobile() ? 3 : 4,
            pointHoverRadius: isLabMobile() ? 4 : 5
        }));
    }

    const labourCommonOptions = () => ({
        responsive: true,
        maintainAspectRatio: false,
        layout: { padding: { top: 8, right: 12, left: 12, bottom: 0 } },
        plugins: {
            legend: {
                position: 'top',
                align: 'start',
                labels: {
                    color: '#cbd5e1',
                    boxWidth: 20,
                    boxHeight: 10,
                    useBorderRadius: true,
                    borderRadius: 3,
                    font: { size: isLabMobile() ? 10 : 12, weight: '600' }
                }
            },
            tooltip: {
                backgroundColor: '#0f172a',
                titleColor: '#f8fafc',
                bodyColor: '#e2e8f0',
                borderColor: 'rgba(148,163,184,.20)',
                borderWidth: 1,
                padding: 10,
                callbacks: {
                    label: function(context) {
                        const val = context.parsed.y;
                        if (context.dataset.label.toLowerCase().includes('ratio')) {
                            return `${context.dataset.label}: ${Number(val || 0).toFixed(3)}%`;
                        }
                        return `${context.dataset.label}: ${formatCompactNumber(val)}`;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                border: { display: false },
                ticks: {
                    color: '#93a4bd',
                    autoSkip: true,
                    maxTicksLimit: isLabMobile() ? 6 : 12,
                    maxRotation: isLabMobile() ? 45 : 0,
                    minRotation: isLabMobile() ? 45 : 0,
                    font: { size: isLabMobile() ? 10 : 11 }
                }
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(148,163,184,.08)' },
                border: { display: false },
                ticks: {
                    color: '#7286a4',
                    font: { size: isLabMobile() ? 9 : 10 }
                }
            }
        }
    });

    const labourValueLabelPlugin = {
        id: 'labourValueLabelPlugin',
        afterDatasetsDraw(chart) {
            if (isLabMobile()) return;
            const { ctx } = chart;
            ctx.save();
            ctx.font = '11px Inter, Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';

            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                if (meta.hidden) return;

                meta.data.forEach((point, index) => {
                    const raw = dataset.originalLabels?.[index];
                    if (!raw || Number(dataset.data[index]) === 0) return;
                    ctx.fillStyle = dataset.borderColor || dataset.backgroundColor;
                    ctx.fillText(raw, point.x, point.y - 6);
                });
            });

            ctx.restore();
        }
    };

    let labourCostChartInstance = null;
    let labourRatioChartInstance = null;

    function renderLabourCharts() {
        if (labourCostChartInstance) labourCostChartInstance.destroy();
        if (labourRatioChartInstance) labourRatioChartInstance.destroy();

        labourCostChartInstance = new Chart(document.getElementById('labourCostChart'), {
            type: 'bar',
            data: {
                labels: labourMonthLabels,
                datasets: buildBarDatasets(labourCostData, 'Est Gaji')
            },
            options: labourCommonOptions(),
            plugins: [labourValueLabelPlugin]
        });

        labourRatioChartInstance = new Chart(document.getElementById('labourRatioChart'), {
            type: 'line',
            data: {
                labels: labourMonthLabels,
                datasets: buildLineDatasets(labourRatioData, 'Ratio')
            },
            options: labourCommonOptions(),
            plugins: [labourValueLabelPlugin]
        });
    }

    renderLabourCharts();

    let labourResizeTimer = null;
    window.addEventListener('resize', function () {
        clearTimeout(labourResizeTimer);
        labourResizeTimer = setTimeout(() => {
            renderLabourCharts();
        }, 250);
    });
</script>

@include('Temp.internal.footer_internal')
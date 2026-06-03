{{-- resources/views/Investor/dashboardSalesComparison_redesign.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root{
        --cmp-bg:#0b1020;
        --cmp-bg2:#0f172a;
        --cmp-panel:#111827;
        --cmp-panel2:#0d1528;
        --cmp-line:rgba(148,163,184,.14);
        --cmp-line-soft:rgba(148,163,184,.08);
        --cmp-text:#e5eefc;
        --cmp-muted:#93a4bd;
        --cmp-green:#22c55e;
        --cmp-red:#ef4444;
        --cmp-blue:#3b82f6;
        --cmp-orange:#f59e0b;
        --cmp-purple:#a78bfa;
        --cmp-shadow:0 12px 32px rgba(0,0,0,.35);
        --cmp-glow-blue:0 0 0 1px rgba(59,130,246,.12), 0 0 24px rgba(59,130,246,.08);
        --cmp-glow-red:0 0 0 1px rgba(239,68,68,.12), 0 0 24px rgba(239,68,68,.08);
        --cmp-glow-green:0 0 0 1px rgba(34,197,94,.12), 0 0 24px rgba(34,197,94,.08);
    }

    .cmp-page{
        width:100%;
        min-height:100vh;
        padding:20px;
        background:
            radial-gradient(circle at top left, rgba(59,130,246,.12), transparent 22%),
            radial-gradient(circle at top right, rgba(6,182,212,.10), transparent 20%),
            linear-gradient(180deg, #0a0f1e 0%, #0b1223 100%);
        color:var(--cmp-text);
    }

    .cmp-page *{
        box-sizing:border-box;
    }

    .cmp-board{
        position:relative;
        overflow:hidden;
        border:1px solid var(--cmp-line);
        border-radius:26px;
        padding:18px;
        background:linear-gradient(180deg, rgba(17,24,39,.96), rgba(9,14,27,.98));
        box-shadow:var(--cmp-shadow);
    }

    .cmp-board::before{
        content:"";
        position:absolute;
        top:0;
        left:0;
        width:100%;
        height:1px;
        background:linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
        opacity:.4;
    }

    .cmp-topbar{
        display:grid;
        grid-template-columns:240px minmax(0, 1fr) 240px;
        gap:18px;
        align-items:center;
        margin-bottom:18px;
    }

    .cmp-brand-box{
        display:flex;
        align-items:center;
        justify-content:flex-start;
        min-height:86px;
    }

    .cmp-brand-pill{
        display:inline-flex;
        align-items:center;
        gap:12px;
        padding:12px 16px;
        border-radius:18px;
        background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
        border:1px solid var(--cmp-line);
        box-shadow:var(--cmp-shadow);
    }

    .cmp-brand-pill img{
        display:block;
        width:auto;
        max-height:52px;
        max-width:100%;
        object-fit:contain;
    }

    .cmp-owner-box{
        text-align:center;
    }

    .cmp-owner-label{
        margin-bottom:8px;
        color:#b9c8dc;
        font-size:13px;
        font-weight:800;
        letter-spacing:.12em;
        text-transform:uppercase;
    }

    .cmp-owner-name{
        color:#f8fbff;
        font-size:clamp(22px, 2.6vw, 30px);
        font-weight:900;
        letter-spacing:-.02em;
        text-transform:uppercase;
        line-height:1.1;
        word-break:break-word;
    }

    .cmp-owner-sub{
        margin-top:6px;
        color:var(--cmp-muted);
        font-size:12px;
    }

    .cmp-filter-box{
        display:flex;
        flex-direction:column;
        gap:10px;
    }

    .cmp-filter-box select{
        width:100%;
        height:42px;
        border-radius:12px;
        border:1px solid var(--cmp-line);
        background:rgba(255,255,255,.04);
        color:#e5eefc;
        padding:0 14px;
        font-size:13px;
        outline:none;
        box-shadow:inset 0 1px 2px rgba(0,0,0,.18);
    }

    .cmp-filter-box select:focus{
        border-color:rgba(59,130,246,.5);
        box-shadow:0 0 0 3px rgba(59,130,246,.12);
    }

    .cmp-filter-box option{
        color:#111827;
    }

    .cmp-section-title{
        display:flex;
        justify-content:center;
        align-items:center;
        margin-bottom:16px;
    }

    .cmp-section-title span{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:100%;
        min-height:44px;
        padding:10px 14px;
        border-radius:14px;
        border:1px solid rgba(34,197,94,.30);
        color:#f8fbff;
        background:linear-gradient(180deg, rgba(34,197,94,.08), rgba(34,197,94,.03));
        font-size:15px;
        font-weight:800;
        letter-spacing:.04em;
        text-transform:uppercase;
        text-align:center;
        box-shadow:var(--cmp-glow-green);
    }

    .cmp-chart-stack{
        display:grid;
        gap:18px;
    }

    .cmp-chart-card{
        background:linear-gradient(180deg, rgba(17,24,39,.92), rgba(8,13,24,.96));
        border:1px solid var(--cmp-line);
        border-radius:20px;
        padding:16px 18px 12px;
        box-shadow:var(--cmp-shadow);
    }

    .cmp-chart-card.neutral{
        box-shadow:var(--cmp-shadow);
    }

    .cmp-chart-card.red{
        box-shadow:var(--cmp-shadow), var(--cmp-glow-red);
    }

    .cmp-chart-card.blue{
        box-shadow:var(--cmp-shadow), var(--cmp-glow-blue);
    }

    .cmp-chart-head{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:12px;
        margin-bottom:12px;
        flex-wrap:wrap;
    }

    .cmp-chart-title{
        margin:0;
        color:#f3f8ff;
        font-size:15px;
        font-weight:800;
        letter-spacing:.02em;
    }

    .cmp-chart-sub{
        margin-top:4px;
        font-size:12px;
        color:var(--cmp-muted);
    }

    .cmp-chart-chip{
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

    .cmp-canvas-wrap{
        position:relative;
        width:100%;
        height:260px;
    }

    .cmp-canvas-wrap canvas{
        width:100% !important;
        height:100% !important;
        display:block;
    }

    .cmp-footer{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
        margin-top:16px;
        color:#7286a4;
        font-size:11px;
    }

    .cmp-footer a{
        color:#8fa6c7;
        text-decoration:none;
    }

    .cmp-footer a:hover{
        text-decoration:underline;
    }

    @media (max-width:1100px){
        .cmp-topbar{
            grid-template-columns:1fr;
            text-align:center;
        }

        .cmp-brand-box{
            justify-content:center;
        }

        .cmp-filter-box{
            max-width:280px;
            margin:0 auto;
        }
    }

    @media (max-width:768px){
        .cmp-page{
            padding:16px;
        }

        .cmp-board{
            padding:16px;
        }

        .cmp-canvas-wrap{
            height:220px;
        }
    }

    @media (max-width:576px){
        .cmp-page{
            padding:12px;
        }

        .cmp-board{
            padding:14px;
            border-radius:20px;
        }

        .cmp-owner-name{
            font-size:22px;
        }

        .cmp-owner-sub,
        .cmp-chart-sub{
            font-size:11px;
        }

        .cmp-section-title span{
            font-size:13px;
            line-height:1.3;
        }

        .cmp-chart-card{
            padding:14px 14px 10px;
            border-radius:16px;
        }

        .cmp-canvas-wrap{
            height:200px;
        }

        .cmp-footer{
            align-items:flex-start;
            flex-direction:column;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">
            <div class="cmp-page">
                <div class="cmp-board">
                    <div class="cmp-topbar">
                        <div class="cmp-brand-box">
                            <div class="cmp-brand-pill">
                                <img src="{{ asset('img/logo2.jpg') }}" alt="Logo Geprekin">
                            </div>
                        </div>

                        <div class="cmp-owner-box">
                            <div class="cmp-owner-label">GEPREKINAJA</div>
                            <div class="cmp-owner-name">{{ $filters['tahun_omset'] ?? '2026' }}</div>
                            <div class="cmp-owner-sub">
                                Sales comparison by year • market analytics style
                                @if(!empty($lastSyncAt))
                                    • Last sync: {{ $lastSyncAt }}
                                @endif
                            </div>
                        </div>

                        <div class="cmp-filter-box">
                            <form method="GET" action="{{ route('investor.sales.dashboardBOD.salesComparison') }}">
                                <select name="tahun_omset" onchange="this.form.submit()">
                                    <option value="">Tahun Omset</option>
                                    @foreach(($tahunOmsetOptions ?? []) as $year)
                                        <option value="{{ $year }}" {{ (string)($filters['tahun_omset'] ?? '') === (string)$year ? 'selected' : '' }}>
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

                    <div class="cmp-section-title">
                        <span>Sales Comparison by Year GO</span>
                    </div>

                    <div class="cmp-chart-stack">
                        <div class="cmp-chart-card neutral">
                            <div class="cmp-chart-head">
                                <div>
                                    <p class="cmp-chart-title">Average Sales</p>
                                    <div class="cmp-chart-sub">Perbandingan rata-rata penjualan per bulan</div>
                                </div>
                                <div class="cmp-chart-chip">AVG VIEW</div>
                            </div>
                            <div class="cmp-canvas-wrap">
                                <canvas id="avgChart"></canvas>
                            </div>
                        </div>

                        <div class="cmp-chart-card red">
                            <div class="cmp-chart-head">
                                <div>
                                    <p class="cmp-chart-title">Omset by Year</p>
                                    <div class="cmp-chart-sub">Perbandingan omset tahunan per bulan</div>
                                </div>
                                <div class="cmp-chart-chip">OMSET VIEW</div>
                            </div>
                            <div class="cmp-canvas-wrap">
                                <canvas id="omsetChart"></canvas>
                            </div>
                        </div>

                        <div class="cmp-chart-card blue">
                            <div class="cmp-chart-head">
                                <div>
                                    <p class="cmp-chart-title">Outlet Comparison</p>
                                    <div class="cmp-chart-sub">Jumlah outlet aktif per tahun</div>
                                </div>
                                <div class="cmp-chart-chip">OUTLET VIEW</div>
                            </div>
                            <div class="cmp-canvas-wrap">
                                <canvas id="outletChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="cmp-footer">
                        <span>Data Last Updated: {{ $lastSyncAt ?? now()->format('d/m/Y H:i:s') }}</span>
                        <a href="#">Privacy Policy</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const monthLabels = @json($monthLabels ?? []);
    const avgData = @json($avgData ?? []);
    const omsetData = @json($omsetData ?? []);
    const outletData = @json($outletData ?? []);

    Chart.defaults.font.family = 'Inter, Arial, sans-serif';
    Chart.defaults.color = '#94a3b8';

    const isMobileChart = () => window.matchMedia('(max-width: 576px)').matches;
    const palette = ['#4b84ea', '#f2a24a', '#aa83e6', '#22c55e', '#ef4444', '#06b6d4'];

    function formatCompactNumber(value) {
        const number = Number(value || 0);

        if (number >= 1000000000) {
            return (number / 1000000000).toFixed(1).replace('.0', '') + 'M';
        }

        if (number >= 1000000) {
            return (number / 1000000).toFixed(1).replace('.0', '') + 'JT';
        }

        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 1
        }).format(number);
    }

    function buildDatasets(source, prefix) {
        return Object.keys(source || {}).map((year, index) => ({
            label: `${prefix} ${year}`,
            data: source[year] || [],
            originalLabels: (source[year] || []).map(value => Number(value || 0) === 0 ? '' : formatCompactNumber(value)),
            backgroundColor: palette[index % palette.length],
            borderRadius: 4,
            categoryPercentage: 0.62,
            barPercentage: 0.84
        }));
    }

    const commonOptions = () => ({
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: { top: 8, right: 10, left: 10, bottom: 0 }
        },
        plugins: {
            legend: {
                position: 'top',
                align: 'start',
                labels: {
                    color: '#cbd5e1',
                    boxWidth: 18,
                    boxHeight: 10,
                    useBorderRadius: true,
                    borderRadius: 3,
                    font: {
                        size: isMobileChart() ? 10 : 12,
                        weight: '600'
                    }
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
                        return `${context.dataset.label}: ${formatCompactNumber(context.parsed.y)}`;
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
                    maxTicksLimit: isMobileChart() ? 6 : 12,
                    maxRotation: isMobileChart() ? 45 : 0,
                    minRotation: isMobileChart() ? 45 : 0,
                    font: {
                        size: isMobileChart() ? 10 : 11
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(148,163,184,.08)'
                },
                border: {
                    display: false
                },
                ticks: {
                    color: '#7286a4',
                    font: {
                        size: isMobileChart() ? 9 : 10
                    },
                    callback: function(value) {
                        return formatCompactNumber(value);
                    }
                }
            }
        }
    });

    const valueLabelPlugin = {
        id: 'valueLabelPlugin',
        afterDatasetsDraw(chart) {
            if (isMobileChart()) return;

            const { ctx } = chart;
            ctx.save();
            ctx.font = '11px Inter, Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';

            chart.data.datasets.forEach((dataset, datasetIndex) => {
                const meta = chart.getDatasetMeta(datasetIndex);
                if (meta.hidden) return;

                meta.data.forEach((bar, index) => {
                    const raw = dataset.originalLabels?.[index];
                    if (!raw || Number(dataset.data[index]) === 0) return;
                    ctx.fillStyle = dataset.backgroundColor;
                    ctx.fillText(raw, bar.x, bar.y - 4);
                });
            });

            ctx.restore();
        }
    };

    let avgChartInstance = null;
    let omsetChartInstance = null;
    let outletChartInstance = null;

    function renderCharts() {
        if (avgChartInstance) avgChartInstance.destroy();
        if (omsetChartInstance) omsetChartInstance.destroy();
        if (outletChartInstance) outletChartInstance.destroy();

        avgChartInstance = new Chart(document.getElementById('avgChart'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: buildDatasets(avgData, 'Avg')
            },
            options: commonOptions(),
            plugins: [valueLabelPlugin]
        });

        omsetChartInstance = new Chart(document.getElementById('omsetChart'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: buildDatasets(omsetData, 'Omset')
            },
            options: commonOptions(),
            plugins: [valueLabelPlugin]
        });

        outletChartInstance = new Chart(document.getElementById('outletChart'), {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: buildDatasets(outletData, 'Outlet')
            },
            options: commonOptions(),
            plugins: [valueLabelPlugin]
        });
    }

    renderCharts();

    let resizeTimer = null;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            renderCharts();
        }, 250);
    });
</script>

@include('Temp.internal.footer_internal')
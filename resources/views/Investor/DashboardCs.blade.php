{{-- resources/views/Investor/DashboardCs.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --cs-bg: #020617;
        --cs-bg-2: #030712;
        --cs-card: rgba(8, 15, 28, .96);
        --cs-line: rgba(148, 163, 184, .12);
        --cs-line-soft: rgba(148, 163, 184, .07);
        --cs-text: #eaf2ff;
        --cs-muted: #7f92ae;
        --cs-green: #22c55e;
        --cs-blue: #3b82f6;
        --cs-red: #ef4444;
        --cs-gold: #f59e0b;
        --cs-shadow: 0 18px 44px rgba(0, 0, 0, .32);
        --cs-radius: 18px;
    }

    .cs-page {
        width: 100%;
        min-height: 100vh;
        padding: 16px;
        color: var(--cs-text);
        background:
            radial-gradient(circle at 12% 0%, rgba(59, 130, 246, .10), transparent 28%),
            radial-gradient(circle at 88% 0%, rgba(34, 197, 94, .08), transparent 24%),
            linear-gradient(180deg, var(--cs-bg) 0%, var(--cs-bg-2) 100%);
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .cs-page * {
        box-sizing: border-box;
    }

    .cs-board {
        width: 100%;
        border: 1px solid var(--cs-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(8, 15, 28, .98), rgba(2, 6, 23, .99));
        box-shadow: var(--cs-shadow);
        overflow: hidden;
    }

    .cs-topbar {
        display: grid;
        grid-template-columns: 116px minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--cs-line);
        background: rgba(255, 255, 255, .018);
    }

    .cs-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 104px;
        height: 52px;
        border: 1px solid var(--cs-line);
        border-radius: 14px;
        background: rgba(255, 255, 255, .035);
    }

    .cs-logo img {
        max-width: 82px;
        max-height: 38px;
        object-fit: contain;
    }

    .cs-title-wrap {
        min-width: 0;
    }

    .cs-eyebrow {
        margin-bottom: 5px;
        color: var(--cs-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .cs-title {
        margin: 0;
        color: #fff;
        font-size: clamp(20px, 2vw, 30px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
        text-transform: uppercase;
    }

    .cs-subtitle {
        margin-top: 6px;
        color: var(--cs-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .cs-filter-form {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .multi-dropdown {
        position: relative;
        min-width: 130px;
    }

    .multi-drop-btn {
        width: 100%;
        height: 38px;
        border: 1px solid var(--cs-line);
        border-radius: 999px;
        background: rgba(255, 255, 255, .035);
        color: var(--cs-text);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 800;
        transition: .16s ease;
    }

    .multi-drop-btn:hover {
        border-color: rgba(59, 130, 246, .38);
        background: rgba(59, 130, 246, .08);
    }

    .badge-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: rgba(59, 130, 246, .18);
        color: #bfdbfe;
        font-size: 11px;
        font-weight: 900;
    }

    .multi-drop-content {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 50;
        min-width: 180px;
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid var(--cs-line);
        border-radius: 14px;
        background: #07101f;
        box-shadow: 0 18px 34px rgba(0, 0, 0, .38);
    }

    .multi-drop-content.show {
        display: block;
    }

    .multi-drop-content label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 10px 12px;
        border-bottom: 1px solid var(--cs-line-soft);
        color: #cbd8eb;
        cursor: pointer;
        font-size: 12px;
        font-weight: 650;
    }

    .multi-drop-content label:hover {
        background: rgba(59, 130, 246, .08);
        color: #fff;
    }

    .multi-drop-content input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: var(--cs-blue);
    }

    .cs-content {
        padding: 16px;
    }

    .cs-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .cs-kpi-card {
        min-width: 0;
        border: 1px solid var(--cs-line);
        border-radius: var(--cs-radius);
        background: var(--cs-card);
        overflow: hidden;
    }

    .cs-kpi-card.actual { border-color: rgba(34, 197, 94, .24); }
    .cs-kpi-card.target { border-color: rgba(59, 130, 246, .24); }
    .cs-kpi-card.variance { border-color: rgba(239, 68, 68, .24); }
    .cs-kpi-card.achieve { border-color: rgba(245, 158, 11, .24); }

    .cs-kpi-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 13px;
        border-bottom: 1px solid var(--cs-line);
        color: #fff;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .1em;
        text-transform: uppercase;
        background: rgba(255, 255, 255, .016);
    }

    .cs-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex: 0 0 7px;
    }

    .cs-dot.green { background: var(--cs-green); box-shadow: 0 0 10px rgba(34,197,94,.60); }
    .cs-dot.blue { background: var(--cs-blue); box-shadow: 0 0 10px rgba(59,130,246,.60); }
    .cs-dot.red { background: var(--cs-red); box-shadow: 0 0 10px rgba(239,68,68,.60); }
    .cs-dot.gold { background: var(--cs-gold); box-shadow: 0 0 10px rgba(245,158,11,.60); }

    .cs-kpi-val {
        padding: 18px 14px 20px;
        color: #fff;
        font-size: clamp(23px, 2.25vw, 34px);
        font-weight: 950;
        line-height: .98;
        letter-spacing: -.05em;
        text-align: center;
        word-break: break-word;
        min-height: 82px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cs-kpi-card.actual .cs-kpi-val {
        color: #86efac;
        background: linear-gradient(180deg, rgba(34,197,94,.10), rgba(255,255,255,.012));
    }

    .cs-kpi-card.target .cs-kpi-val {
        color: #bfdbfe;
        background: linear-gradient(180deg, rgba(59,130,246,.10), rgba(255,255,255,.012));
    }

    .cs-kpi-card.variance .cs-kpi-val {
        color: #fca5a5;
        background: linear-gradient(180deg, rgba(239,68,68,.10), rgba(255,255,255,.012));
    }

    .cs-kpi-card.achieve .cs-kpi-val {
        color: #fde68a;
        font-size: clamp(30px, 3vw, 42px);
        background: linear-gradient(180deg, rgba(245,158,11,.10), rgba(255,255,255,.012));
    }

    .cs-chart-card,
    .cs-table-card {
        border: 1px solid var(--cs-line);
        border-radius: var(--cs-radius);
        background: var(--cs-card);
        overflow: hidden;
        margin-bottom: 16px;
    }

    .cs-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--cs-line);
        background: rgba(255, 255, 255, .016);
    }

    .cs-card-title {
        color: #fff;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .cs-card-chip {
        height: 24px;
        display: inline-flex;
        align-items: center;
        padding: 0 9px;
        border-radius: 999px;
        color: #bfdbfe;
        background: rgba(59, 130, 246, .12);
        border: 1px solid rgba(59, 130, 246, .20);
        font-size: 10px;
        font-weight: 900;
    }

    .chart-container {
        height: 380px;
        padding: 16px;
        background:
            radial-gradient(circle at 50% 0%, rgba(59,130,246,.08), transparent 34%),
            rgba(2, 6, 23, .70);
    }

    .cs-summary-wrap {
        overflow: auto;
    }

    .cs-summary-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
        text-align: right;
    }

    .cs-summary-table th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 10px 12px;
        background: #071426;
        border-bottom: 1px solid var(--cs-line);
        border-right: 1px solid var(--cs-line-soft);
        color: #8fa3c0;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .cs-summary-table td {
        padding: 11px 12px;
        border-bottom: 1px solid var(--cs-line-soft);
        border-right: 1px solid var(--cs-line-soft);
        color: #dbe8fb;
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap;
    }

    .cs-summary-table th:first-child,
    .cs-summary-table td:first-child {
        text-align: left;
    }

    .cs-summary-table tr:nth-child(even) td {
        background: rgba(255,255,255,.022);
    }

    .cs-green-text { color: #86efac !important; }
    .cs-blue-text { color: #bfdbfe !important; }
    .cs-red-text { color: #fca5a5 !important; }
    .cs-gold-text { color: #fde68a !important; }

    .cs-footer {
        color: #6f86a8;
        font-size: 11px;
        text-align: right;
    }

    @media (max-width: 1200px) {
        .cs-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cs-topbar {
            grid-template-columns: 1fr;
        }

        .cs-logo {
            width: 100%;
        }

        .cs-title-wrap {
            text-align: center;
        }

        .cs-filter-form {
            justify-content: center;
        }
    }

    @media (max-width: 720px) {
        .cs-page {
            padding: 10px;
        }

        .cs-topbar,
        .cs-content {
            padding: 12px;
        }

        .cs-kpi-grid {
            grid-template-columns: 1fr;
        }

        .multi-dropdown {
            min-width: 100%;
        }

        .cs-filter-form {
            width: 100%;
        }

        .chart-container {
            height: 300px;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="cs-page">
                <div class="cs-board">

                    <div class="cs-topbar">
                        <div class="cs-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="cs-title-wrap">
                            <!-- <div class="cs-eyebrow">Owner - Noor Haqqi</div> -->
                            <h1 class="cs-title">Performa Sales Customer Service</h1>
                            <div class="cs-subtitle">Dark market-board style untuk actual, target, variance, achievement, dan trend omset CS.</div>
                        </div>

                        <form method="GET" action="{{ route('investor.sales.dashboardBOD.cs') }}" id="filterForm" class="cs-filter-form">
                            <div class="multi-dropdown">
                                <button type="button" class="multi-drop-btn" onclick="toggleDropdown('dropTahun')">
                                    <span>Tahun</span>
                                    <span class="badge-count">{{ count($filters['tahun'] ?? []) }}</span>
                                </button>
                                <div id="dropTahun" class="multi-drop-content">
                                    @foreach(($availableYears ?? []) as $year)
                                        <label>
                                            <input type="checkbox" name="tahun[]" value="{{ $year }}" onchange="document.getElementById('filterForm').submit()" {{ in_array((string)$year, $filters['tahun'] ?? []) ? 'checked' : '' }}>
                                            {{ $year }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="multi-dropdown">
                                <button type="button" class="multi-drop-btn" onclick="toggleDropdown('dropBulan')">
                                    <span>Bulan</span>
                                    <span class="badge-count">{{ count($filters['bulan'] ?? []) }}</span>
                                </button>
                                <div id="dropBulan" class="multi-drop-content">
                                    @foreach(($monthLabels ?? []) as $index => $monthLabel)
                                        <label>
                                            <input type="checkbox" name="bulan[]" value="{{ $index + 1 }}" onchange="document.getElementById('filterForm').submit()" {{ in_array((string)($index + 1), $filters['bulan'] ?? []) ? 'checked' : '' }}>
                                            {{ $monthLabel }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="cs-content">
                        <div class="cs-kpi-grid">
                            <section class="cs-kpi-card actual">
                                <div class="cs-kpi-head">
                                    <span>Actual</span>
                                    <span class="cs-dot green"></span>
                                </div>
                                <div class="cs-kpi-val">Rp{{ number_format($metrics['omset'], 0, ',', '.') }}</div>
                            </section>

                            <section class="cs-kpi-card target">
                                <div class="cs-kpi-head">
                                    <span>Target</span>
                                    <span class="cs-dot blue"></span>
                                </div>
                                <div class="cs-kpi-val">Rp{{ number_format($metrics['target'], 0, ',', '.') }}</div>
                            </section>

                            <section class="cs-kpi-card variance">
                                <div class="cs-kpi-head">
                                    <span>Variance</span>
                                    <span class="cs-dot red"></span>
                                </div>
                                <div class="cs-kpi-val">Rp{{ number_format($metrics['variance'], 0, ',', '.') }}</div>
                            </section>

                            <section class="cs-kpi-card achieve">
                                <div class="cs-kpi-head">
                                    <span>Achievement</span>
                                    <span class="cs-dot gold"></span>
                                </div>
                                <div class="cs-kpi-val">{{ number_format($metrics['pencapaian'], 2, '.', '') }}%</div>
                            </section>
                        </div>

                        <section class="cs-chart-card">
                            <div class="cs-card-head">
                                <div class="cs-card-title">Monthly Omset Customer Service</div>
                                <div class="cs-card-chip">Chart</div>
                            </div>
                            <div class="chart-container">
                                <canvas id="csChart"></canvas>
                            </div>
                        </section>

                        <section class="cs-table-card">
                            <div class="cs-card-head">
                                <div class="cs-card-title">Summary Sales CS</div>
                                <div class="cs-card-chip">Table</div>
                            </div>
                            <div class="cs-summary-wrap">
                                <table class="cs-summary-table">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Actual</td>
                                            <td class="cs-green-text">Rp{{ number_format($metrics['omset'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Target</td>
                                            <td class="cs-blue-text">Rp{{ number_format($metrics['target'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Variance</td>
                                            <td class="cs-red-text">Rp{{ number_format($metrics['variance'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Achievement</td>
                                            <td class="cs-gold-text">{{ number_format($metrics['pencapaian'], 2, '.', '') }}%</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <div class="cs-footer">
                            Data Last Updated: {{ $lastSyncAt }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    function toggleDropdown(id) {
        document.querySelectorAll('.multi-drop-content').forEach(content => {
            if (content.id !== id) {
                content.classList.remove('show');
            }
        });

        const dropdown = document.getElementById(id);
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }

    window.addEventListener('click', function(event) {
        if (!event.target.closest('.multi-dropdown')) {
            document.querySelectorAll('.multi-drop-content').forEach(content => {
                content.classList.remove('show');
            });
        }
    });

    function formatIndoCurrency(value) {
        const num = Number(value || 0);
        if (num >= 1000000000000) return (num / 1000000000000).toFixed(1) + 'T';
        if (num >= 1000000000) return (num / 1000000000).toFixed(1) + 'M';
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return new Intl.NumberFormat('id-ID').format(num);
    }

    const ctx = document.getElementById('csChart');
    const monthLabels = @json($monthLabels);
    const chartData = @json($chartData);

    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#8fa3c0';

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 380);
    gradient.addColorStop(0, 'rgba(59, 130, 246, .95)');
    gradient.addColorStop(.55, 'rgba(37, 99, 235, .62)');
    gradient.addColorStop(1, 'rgba(15, 23, 42, .35)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Omset',
                data: chartData,
                backgroundColor: gradient,
                borderColor: 'rgba(147, 197, 253, .75)',
                borderWidth: 1,
                borderRadius: 10,
                borderSkipped: false,
                barPercentage: 0.58
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#020617',
                    titleColor: '#f8fafc',
                    bodyColor: '#e2e8f0',
                    borderColor: 'rgba(148,163,184,.20)',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Omset: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,.07)' },
                    border: { display: false },
                    ticks: {
                        color: '#8fa3c0',
                        callback: function(value) {
                            return formatIndoCurrency(value);
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#8fa3c0',
                        font: { size: 11 }
                    }
                }
            }
        },
        plugins: [{
            id: 'topLabels',
            afterDatasetsDraw(chart) {
                const chartCtx = chart.ctx;
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((bar, index) => {
                        const data = dataset.data[index];
                        if (data > 0) {
                            chartCtx.fillStyle = '#dbeafe';
                            chartCtx.textAlign = 'center';
                            chartCtx.textBaseline = 'bottom';
                            chartCtx.font = 'bold 11px Inter';
                            chartCtx.fillText(formatIndoCurrency(data), bar.x, bar.y - 6);
                        }
                    });
                });
            }
        }]
    });
</script>

@include('Temp.internal.footer_internal')

{{-- resources/views/Investor/DashboardCro.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --cro-bg: #020617;
        --cro-bg-2: #030712;
        --cro-card: rgba(8, 15, 28, .96);
        --cro-card-soft: rgba(15, 23, 42, .72);
        --cro-line: rgba(148, 163, 184, .12);
        --cro-line-soft: rgba(148, 163, 184, .07);
        --cro-text: #eaf2ff;
        --cro-muted: #7f92ae;
        --cro-green: #22c55e;
        --cro-blue: #3b82f6;
        --cro-red: #ef4444;
        --cro-gold: #f59e0b;
        --cro-shadow: 0 18px 44px rgba(0, 0, 0, .32);
        --cro-radius: 18px;
    }

    .cro-page {
        width: 100%;
        min-height: 100vh;
        padding: 16px;
        color: var(--cro-text);
        background:
            radial-gradient(circle at 12% 0%, rgba(59, 130, 246, .10), transparent 28%),
            radial-gradient(circle at 88% 0%, rgba(34, 197, 94, .08), transparent 24%),
            linear-gradient(180deg, var(--cro-bg) 0%, var(--cro-bg-2) 100%);
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .cro-page * {
        box-sizing: border-box;
    }

    .cro-board {
        width: 100%;
        border: 1px solid var(--cro-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(8, 15, 28, .98), rgba(2, 6, 23, .99));
        box-shadow: var(--cro-shadow);
        overflow: hidden;
    }

    .cro-topbar {
        display: grid;
        grid-template-columns: 116px minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--cro-line);
        background: rgba(255, 255, 255, .018);
    }

    .cro-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 104px;
        height: 52px;
        border: 1px solid var(--cro-line);
        border-radius: 14px;
        background: rgba(255, 255, 255, .035);
    }

    .cro-logo img {
        max-width: 82px;
        max-height: 38px;
        object-fit: contain;
    }

    .cro-title-wrap {
        min-width: 0;
    }

    .cro-eyebrow {
        margin-bottom: 5px;
        color: var(--cro-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .cro-title {
        margin: 0;
        color: #fff;
        font-size: clamp(20px, 2vw, 30px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
        text-transform: uppercase;
    }

    .cro-subtitle {
        margin-top: 6px;
        color: var(--cro-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .cro-filter-form {
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
        border: 1px solid var(--cro-line);
        border-radius: 999px;
        background: rgba(255, 255, 255, .035);
        color: var(--cro-text);
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
        border: 1px solid var(--cro-line);
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
        border-bottom: 1px solid var(--cro-line-soft);
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
        accent-color: var(--cro-blue);
    }

    .cro-content {
        padding: 16px;
    }

    .cro-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .cro-kpi-card {
        min-width: 0;
        border: 1px solid var(--cro-line);
        border-radius: var(--cro-radius);
        background: var(--cro-card);
        overflow: hidden;
    }

    .cro-kpi-card.actual { border-color: rgba(34, 197, 94, .24); }
    .cro-kpi-card.target { border-color: rgba(59, 130, 246, .24); }
    .cro-kpi-card.variance { border-color: rgba(239, 68, 68, .24); }
    .cro-kpi-card.achieve { border-color: rgba(245, 158, 11, .24); }

    .cro-kpi-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 13px;
        border-bottom: 1px solid var(--cro-line);
        color: #fff;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .1em;
        text-transform: uppercase;
        background: rgba(255, 255, 255, .016);
    }

    .cro-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex: 0 0 7px;
    }

    .cro-dot.green { background: var(--cro-green); box-shadow: 0 0 10px rgba(34,197,94,.60); }
    .cro-dot.blue { background: var(--cro-blue); box-shadow: 0 0 10px rgba(59,130,246,.60); }
    .cro-dot.red { background: var(--cro-red); box-shadow: 0 0 10px rgba(239,68,68,.60); }
    .cro-dot.gold { background: var(--cro-gold); box-shadow: 0 0 10px rgba(245,158,11,.60); }

    .cro-kpi-val {
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

    .cro-kpi-card.actual .cro-kpi-val {
        color: #86efac;
        background: linear-gradient(180deg, rgba(34,197,94,.10), rgba(255,255,255,.012));
    }

    .cro-kpi-card.target .cro-kpi-val {
        color: #bfdbfe;
        background: linear-gradient(180deg, rgba(59,130,246,.10), rgba(255,255,255,.012));
    }

    .cro-kpi-card.variance .cro-kpi-val {
        color: #fca5a5;
        background: linear-gradient(180deg, rgba(239,68,68,.10), rgba(255,255,255,.012));
    }

    .cro-kpi-card.achieve .cro-kpi-val {
        color: #fde68a;
        font-size: clamp(30px, 3vw, 42px);
        background: linear-gradient(180deg, rgba(245,158,11,.10), rgba(255,255,255,.012));
    }

    .cro-chart-card,
    .cro-table-card {
        border: 1px solid var(--cro-line);
        border-radius: var(--cro-radius);
        background: var(--cro-card);
        overflow: hidden;
        margin-bottom: 16px;
    }

    .cro-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--cro-line);
        background: rgba(255, 255, 255, .016);
    }

    .cro-card-title {
        color: #fff;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .cro-card-chip {
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
        height: 360px;
        padding: 16px;
        background:
            radial-gradient(circle at 50% 0%, rgba(59,130,246,.08), transparent 34%),
            rgba(2, 6, 23, .70);
    }

    .lab-table-wrap {
        overflow: auto;
        max-height: 410px;
    }

    .lab-table {
        width: 100%;
        min-width: 700px;
        border-collapse: collapse;
        text-align: left;
    }

    .lab-table th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 10px 12px;
        background: #071426;
        border-bottom: 1px solid var(--cro-line);
        border-right: 1px solid var(--cro-line-soft);
        color: #8fa3c0;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .lab-table td {
        padding: 11px 12px;
        border-bottom: 1px solid var(--cro-line-soft);
        border-right: 1px solid var(--cro-line-soft);
        color: #dbe8fb;
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap;
    }

    .lab-table tr:nth-child(even) td {
        background: rgba(255,255,255,.022);
    }

    .lab-table tr:hover td {
        background: rgba(59, 130, 246, .055);
    }

    .rank-badge {
        color: #fff;
        border-radius: 999px;
        min-width: 26px;
        height: 26px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 950;
        background: rgba(59,130,246,.72);
        border: 1px solid rgba(255,255,255,.12);
    }

    .rank-1 { background: linear-gradient(180deg,#facc15,#a16207); }
    .rank-2 { background: linear-gradient(180deg,#cbd5e1,#64748b); }
    .rank-3 { background: linear-gradient(180deg,#f97316,#9a3412); }

    .cro-green-text {
        color: #86efac !important;
    }

    .cro-footer {
        color: #6f86a8;
        font-size: 11px;
        text-align: right;
    }

    @media (max-width: 1200px) {
        .cro-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cro-topbar {
            grid-template-columns: 1fr;
        }

        .cro-logo {
            width: 100%;
        }

        .cro-title-wrap {
            text-align: center;
        }

        .cro-filter-form {
            justify-content: center;
        }
    }

    @media (max-width: 720px) {
        .cro-page {
            padding: 10px;
        }

        .cro-topbar,
        .cro-content {
            padding: 12px;
        }

        .cro-kpi-grid {
            grid-template-columns: 1fr;
        }

        .multi-dropdown {
            min-width: 100%;
        }

        .cro-filter-form {
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
            <div class="cro-page">
                <div class="cro-board">

                    <div class="cro-topbar">
                        <div class="cro-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="cro-title-wrap">
                            <!-- <div class="cro-eyebrow">Owner - Soehartono</div> -->
                            <h1 class="cro-title">Performa Sales CRO</h1>
                            <div class="cro-subtitle">Dark market-board style untuk actual, target, variance, achievement, dan kontribusi CRO.</div>
                        </div>

                        <form method="GET" action="{{ route('investor.sales.dashboardBOD.cro') }}" id="filterForm" class="cro-filter-form">
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

                    <div class="cro-content">
                        <div class="cro-kpi-grid">
                            <section class="cro-kpi-card actual">
                                <div class="cro-kpi-head">
                                    <span>Actual</span>
                                    <span class="cro-dot green"></span>
                                </div>
                                <div class="cro-kpi-val">Rp {{ number_format($metrics['omset'], 0, ',', '.') }}</div>
                            </section>

                            <section class="cro-kpi-card target">
                                <div class="cro-kpi-head">
                                    <span>Target</span>
                                    <span class="cro-dot blue"></span>
                                </div>
                                <div class="cro-kpi-val">Rp {{ number_format($metrics['target'], 0, ',', '.') }}</div>
                            </section>

                            <section class="cro-kpi-card variance">
                                <div class="cro-kpi-head">
                                    <span>Variance</span>
                                    <span class="cro-dot red"></span>
                                </div>
                                <div class="cro-kpi-val">Rp {{ number_format($metrics['kekurangan'], 0, ',', '.') }}</div>
                            </section>

                            <section class="cro-kpi-card achieve">
                                <div class="cro-kpi-head">
                                    <span>Achievement</span>
                                    <span class="cro-dot gold"></span>
                                </div>
                                <div class="cro-kpi-val">{{ number_format($metrics['pencapaian'], 2, '.', '') }}%</div>
                            </section>
                        </div>

                        <section class="cro-chart-card">
                            <div class="cro-card-head">
                                <div class="cro-card-title">Monthly Omset</div>
                                <div class="cro-card-chip">Chart</div>
                            </div>
                            <div class="chart-container">
                                <canvas id="croChart"></canvas>
                            </div>
                        </section>

                        @if(!empty($metrics['cros']))
                            <section class="cro-table-card">
                                <div class="cro-card-head">
                                    <div class="cro-card-title">Leaderboard Kontribusi CRO</div>
                                    <div class="cro-card-chip">Ranking</div>
                                </div>
                                <div class="lab-table-wrap">
                                    <table class="lab-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 90px; text-align:center;">Peringkat</th>
                                                <th>Nama CRO</th>
                                                <th style="text-align:right;">Total Omset Dihasilkan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $rank = 1; @endphp
                                            @foreach($metrics['cros'] as $name => $omset)
                                                @if($omset > 0)
                                                    <tr>
                                                        <td style="text-align:center;">
                                                            <span class="rank-badge rank-{{ $rank }}">{{ $rank }}</span>
                                                        </td>
                                                        <td style="text-transform: capitalize;">{{ $name }}</td>
                                                        <td class="cro-green-text" style="text-align:right;">Rp {{ number_format($omset, 0, ',', '.') }}</td>
                                                    </tr>
                                                    @php $rank++; @endphp
                                                @endif
                                            @endforeach

                                            @if($rank === 1)
                                                <tr>
                                                    <td colspan="3" style="text-align:center; padding:20px;">Belum ada data omset individu pada periode ini.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif

                        <div class="cro-footer">
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
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'JT';
        return new Intl.NumberFormat('id-ID').format(num);
    }

    const ctx = document.getElementById('croChart');
    const monthLabels = @json($monthLabels);
    const chartData = @json($chartData);

    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#8fa3c0';

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 360);
    gradient.addColorStop(0, 'rgba(59, 130, 246, .95)');
    gradient.addColorStop(.55, 'rgba(37, 99, 235, .62)');
    gradient.addColorStop(1, 'rgba(15, 23, 42, .35)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Omset per Bulan',
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
                        color: '#8fa3c0'
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

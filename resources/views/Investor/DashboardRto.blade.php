@include('Temp.internal.header_internal')

<style>
    :root {
        --lab-bg: #07101d;
        --lab-panel: #0b1830;
        --lab-line: rgba(148, 163, 184, .14);
        --lab-text: #e5eefc;
        --lab-muted: #93a4bd;
        --lab-shadow: 0 16px 40px rgba(0, 0, 0, .35);
        --rto-spk: #1e3a8a;
        --rto-masuk: #f97316;
        --rto-validasi: #9ca3af;
        --rto-rto: #eab308;
        --rto-go: #22c55e;
    }

    .lab-page { width: 100%; min-height: 100vh; padding: 20px; background: radial-gradient(circle at top left, rgba(59, 130, 246, .12), transparent 22%), radial-gradient(circle at top right, rgba(6, 182, 212, .10), transparent 20%), linear-gradient(180deg, #07101d 0%, #0a1526 100%); color: var(--lab-text); font-family: 'Inter', sans-serif; }
    .lab-board { border-radius: 26px; padding: 18px; background: linear-gradient(180deg, rgba(10, 21, 38, .98), rgba(6, 12, 23, .99)); border: 1px solid var(--lab-line); box-shadow: var(--lab-shadow); }
    .lab-topbar { display: grid; grid-template-columns: 240px minmax(0, 1fr) 360px; gap: 18px; align-items: center; margin-bottom: 24px; }
    .lab-brand-pill { display: inline-flex; align-items: center; padding: 12px 16px; border-radius: 18px; background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .02)); border: 1px solid var(--lab-line); }
    .lab-brand-pill img { max-height: 52px; width: auto; }
    .lab-header-box { text-align: center; }
    .lab-header-label { color: #b9c8dc; font-size: 13px; font-weight: 800; letter-spacing: .1em; margin-bottom: 8px; }
    .lab-header-title { color: #f8fbff; font-size: clamp(20px, 2.2vw, 26px); font-weight: 900; letter-spacing: -.02em; text-transform: uppercase; }

    .lab-filter-box form { display: flex; justify-content: flex-end; gap: 12px; }
    .multi-dropdown { position: relative; display: inline-block; }
    .multi-drop-btn { background: rgba(255, 255, 255, .05); border: 1px solid var(--lab-line); padding: 10px 16px; border-radius: 12px; cursor: pointer; color: #e5eefc; font-weight: 600; min-width: 140px; text-align: left; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    .multi-drop-content { display: none; position: absolute; background-color: #0f172a; min-width: 100%; box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.5); z-index: 100; border-radius: 12px; max-height: 250px; overflow-y: auto; margin-top: 8px; border: 1px solid var(--lab-line); }
    .multi-drop-content.show { display: block; }
    .multi-drop-content label { color: #cbd5e1; padding: 10px 16px; display: flex; align-items: center; cursor: pointer; border-bottom: 1px solid rgba(255, 255, 255, .05); margin: 0; font-size: 13px; }
    .multi-drop-content label:hover { background-color: rgba(255, 255, 255, .05); color: #fff; }
    .multi-drop-content input[type="checkbox"] { margin-right: 12px; transform: scale(1.2); accent-color: var(--rto-spk); }
    .badge-count { background: var(--rto-spk); color: #fff; padding: 2px 6px; border-radius: 6px; font-size: 11px; margin-left: 8px; }

    .rto-kpi-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 32px; margin-top: 16px; }
    .rto-kpi-card { border-radius: 16px; overflow: hidden; text-align: center; border: 1px solid rgba(255,255,255,0.05); background: linear-gradient(180deg, rgba(15, 23, 42, 0.8), rgba(2, 6, 23, 0.9)); box-shadow: 0 8px 20px rgba(0,0,0,0.3); transition: transform 0.2s; }
    .rto-kpi-card:hover { transform: translateY(-3px); }
    .rto-kpi-head { font-size: 13px; font-weight: 800; color: #fff; padding: 12px; text-transform: uppercase; letter-spacing: 1px; }
    .rto-kpi-val { font-size: clamp(24px, 2.5vw, 36px); font-weight: 900; color: #fff; padding: 20px 10px; line-height: 1; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }

    .card-spk .rto-kpi-head { background: var(--rto-spk); }
    .card-masuk .rto-kpi-head { background: var(--rto-masuk); }
    .card-validasi .rto-kpi-head { background: var(--rto-validasi); color: #111827; }
    .card-rto .rto-kpi-head { background: var(--rto-rto); color: #111827; }
    .card-go .rto-kpi-head { background: var(--rto-go); }

    .card-spk .rto-kpi-val { color: #bfdbfe; }
    .card-masuk .rto-kpi-val { color: #fed7aa; }
    .card-validasi .rto-kpi-val { color: #e5e7eb; }
    .card-rto .rto-kpi-val { color: #fef08a; }
    .card-go .rto-kpi-val { color: #bbf7d0; }

    .chart-container { background: rgba(15, 23, 42, 0.6); border: 1px solid var(--lab-line); border-radius: 16px; padding: 20px; height: 450px; margin-bottom: 24px; box-shadow: inset 0 2px 10px rgba(0,0,0,0.2); }
    .chart-title { text-align: center; color: #fff; font-weight: 900; font-size: 18px; margin-bottom: 20px; letter-spacing: 1px; text-transform: uppercase; }

    .table-container { background: linear-gradient(180deg, rgba(17, 24, 39, .92), rgba(8, 13, 24, .96)); border: 1px solid var(--lab-line); border-radius: 20px; overflow: hidden; margin-bottom: 24px; }
    .lab-table-wrap { overflow-x: auto; max-height: 350px; }
    .lab-table { width: 100%; border-collapse: collapse; text-align: center; }
    .lab-table th { background: #1e293b; color: #f8fafc; font-size: 12px; padding: 12px; font-weight: 800; border-bottom: 1px solid rgba(255, 255, 255, .1); position: sticky; top: 0; z-index: 2; }
    .lab-table td { font-size: 13px; padding: 12px; border-bottom: 1px solid rgba(148, 163, 184, .10); color: var(--lab-text); font-weight: 600; }
    .lab-table tr:nth-child(even) td { background: rgba(255, 255, 255, .02); }

    @media (max-width: 1024px) {
        .rto-kpi-grid { grid-template-columns: repeat(3, 1fr); }
        .lab-topbar { grid-template-columns: 1fr; text-align: center; }
        .lab-filter-box form { justify-content: center; }
    }

    @media (max-width: 576px) {
        .rto-kpi-grid { grid-template-columns: 1fr 1fr; }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="lab-page">
                <div class="lab-board">

                    <div class="lab-topbar">
                        <div class="lab-brand-box">
                            <div class="lab-brand-pill">
                                <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                            </div>
                        </div>

                        <div class="lab-header-box">
                            <div class="lab-header-label">OWNER - M. FAIZ</div>
                            <div class="lab-header-title">BUSINESS & DEVELOPMENT (RTO)</div>
                        </div>

                        <div class="lab-filter-box">
                            <form method="GET" action="{{ route('investor.sales.dashboardBOD.rto') }}" id="filterForm">
                                <div class="multi-dropdown">
                                    <div class="multi-drop-btn" onclick="toggleDropdown('dropTahun')">
                                        <div>Tahun <span class="badge-count">{{ count($availableYears ?? []) }}</span></div>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
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
                                    <div class="multi-drop-btn" onclick="toggleDropdown('dropBulan')">
                                        <div>Bulan <span class="badge-count">{{ count($filters['bulan'] ?? []) }}</span></div>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
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
                    </div>

                    <div class="rto-kpi-grid">
                        <div class="rto-kpi-card card-spk">
                            <div class="rto-kpi-head">SPK BI</div>
                            <div class="rto-kpi-val">{{ number_format($metrics['spk_bi'] ?? 0) }}</div>
                        </div>
                        <div class="rto-kpi-card card-masuk">
                            <div class="rto-kpi-head">Titik Masuk</div>
                            <div class="rto-kpi-val">{{ number_format($metrics['titik_masuk'] ?? 0) }}</div>
                        </div>
                        <div class="rto-kpi-card card-validasi">
                            <div class="rto-kpi-head">Titik Validasi</div>
                            <div class="rto-kpi-val">{{ number_format($metrics['titik_validasi'] ?? 0) }}</div>
                        </div>
                        <div class="rto-kpi-card card-rto">
                            <div class="rto-kpi-head">Titik RTO</div>
                            <div class="rto-kpi-val">{{ number_format($metrics['titik_rto'] ?? 0) }}</div>
                        </div>
                        <div class="rto-kpi-card card-go">
                            <div class="rto-kpi-head">Titik GO</div>
                            <div class="rto-kpi-val">{{ number_format($metrics['titik_go'] ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="chart-container">
                        <div class="chart-title">PERFORMA BND (TAHUNAN)</div>
                        <canvas id="rtoChart"></canvas>
                    </div>

                    <div class="table-container">
                        <div class="lab-table-wrap">
                            <table class="lab-table">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">Bulan</th>
                                        <th>SPK BI</th>
                                        <th>Titik Masuk</th>
                                        <th>Titik Validasi</th>
                                        <th>Titik RTO</th>
                                        <th>Titik GO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tableData ?? [] as $row)
                                        @if(($row['spk_bi'] ?? 0) > 0 || ($row['titik_masuk'] ?? 0) > 0 || ($row['titik_validasi'] ?? 0) > 0 || ($row['titik_rto'] ?? 0) > 0 || ($row['titik_go'] ?? 0) > 0)
                                            <tr>
                                                <td style="text-align: left; font-weight:800;">{{ $row['bulan'] }}</td>
                                                <td style="color: #bfdbfe;">{{ number_format($row['spk_bi']) }}</td>
                                                <td style="color: #fed7aa;">{{ number_format($row['titik_masuk']) }}</td>
                                                <td style="color: #e5e7eb;">{{ number_format($row['titik_validasi']) }}</td>
                                                <td style="color: #fef08a;">{{ number_format($row['titik_rto']) }}</td>
                                                <td style="color: #bbf7d0;">{{ number_format($row['titik_go']) }}</td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="6" style="padding: 20px;">Tidak ada data ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="text-align: center; color: #7286a4; font-size: 11px; margin-top: 16px;">
                        Data Last Updated: {{ $lastSyncAt }}
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
            if (content.id !== id) content.classList.remove('show');
        });
        document.getElementById(id).classList.toggle('show');
    }

    window.onclick = function(event) {
        if (!event.target.closest('.multi-dropdown')) {
            document.querySelectorAll('.multi-drop-content').forEach(content => {
                content.classList.remove('show');
            });
        }
    }

    const ctx = document.getElementById('rtoChart');
    const monthLabels = @json($monthLabels);
    const cData = @json($chartData);

    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#94a3b8';

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [
                { label: 'SPK BI', data: cData.spk_bi, backgroundColor: '#1e3a8a', borderRadius: 4, barPercentage: 0.8 },
                { label: 'Titik Masuk', data: cData.titik_masuk, backgroundColor: '#f97316', borderRadius: 4, barPercentage: 0.8 },
                { label: 'Titik Validasi', data: cData.titik_validasi, backgroundColor: '#9ca3af', borderRadius: 4, barPercentage: 0.8 },
                { label: 'Titik RTO', data: cData.titik_rto, backgroundColor: '#eab308', borderRadius: 4, barPercentage: 0.8 },
                { label: 'Titik GO', data: cData.titik_go, backgroundColor: '#22c55e', borderRadius: 4, barPercentage: 0.8 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, boxWidth: 10, padding: 20 }
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#f8fafc',
                    bodyColor: '#e2e8f0',
                    borderColor: 'rgba(148,163,184,.20)',
                    borderWidth: 1,
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,.05)' },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    border: { display: false }
                }
            }
        },
        plugins: [{
            id: 'topLabels',
            afterDatasetsDraw(chart) {
                const chartCtx = chart.ctx;
                chart.data.datasets.forEach((dataset, i) => {
                    chart.getDatasetMeta(i).data.forEach((bar, index) => {
                        const data = dataset.data[index];
                        if (data > 0) {
                            chartCtx.fillStyle = dataset.backgroundColor;
                            chartCtx.textAlign = 'center';
                            chartCtx.textBaseline = 'bottom';
                            chartCtx.font = 'bold 10px Inter';
                            chartCtx.fillText(data, bar.x, bar.y - 4);
                        }
                    });
                });
            }
        }]
    });
</script>

@include('Temp.internal.footer_internal')
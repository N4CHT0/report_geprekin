{{-- resources/views/Investor/DashboardMappingMarket.blade.php --}}
@include('Temp.internal.header_internal')

@php
    $mappingRows = $mappingRows ?? [];
    $kecamatanList = $kecamatanList ?? [];
    $grandTotal = $grandTotal ?? 0;

    $metrics = $metrics ?? [
        'outlet_aktif' => 0,
        'optimum' => 0,
        'agresif' => 0,
        'market_share' => 0,
    ];

    $chartLabels = $chartLabels ?? [];
    $chartData = $chartData ?? [];

    $marketingBiRows = $marketingBiRows ?? [];
    $marketingBiSummary = $marketingBiSummary ?? [
        'total_spk' => 0,
        'total_st' => 0,
        'active_rows' => 0,
        'ratio_st' => 0,
    ];

    $lastSyncAt = $lastSyncAt ?? now()->format('d/m/Y H:i:s');
@endphp

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"/>

<style>
    :root {
        --mm-bg: #000000;
        --mm-bg-2: #020617;
        --mm-panel: #030712;
        --mm-panel-2: #050b17;
        --mm-border: rgba(255,255,255,.08);
        --mm-border-strong: rgba(255,255,255,.14);
        --mm-text: #f8fafc;
        --mm-muted: #64748b;
        --mm-green: #22c55e;
        --mm-blue: #3b82f6;
        --mm-red: #ef4444;
        --mm-gold: #f59e0b;
        --mm-radius: 18px;
        --mm-shadow: 0 22px 48px rgba(0,0,0,.72);
        --mm-glow-green: 0 0 22px rgba(34,197,94,.24);
        --mm-glow-blue: 0 0 22px rgba(59,130,246,.28);
        --mm-glow-red: 0 0 22px rgba(239,68,68,.22);
        --mm-glow-gold: 0 0 22px rgba(245,158,11,.20);
    }

    * { box-sizing: border-box; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    html, body { margin: 0; padding: 0; background: var(--mm-bg); }

    .mm-page {
        width: 100%;
        min-height: 100vh;
        padding: 14px;
        color: var(--mm-text);
        background:
            radial-gradient(circle at 8% 0%, rgba(34,197,94,.10), transparent 24%),
            radial-gradient(circle at 92% 0%, rgba(59,130,246,.12), transparent 24%),
            linear-gradient(180deg, #000 0%, #020617 100%);
    }

    .mm-board {
        min-height: calc(100vh - 28px);
        border: 1px solid var(--mm-border);
        border-radius: 22px;
        background: linear-gradient(180deg, #020617 0%, #000 100%);
        box-shadow: var(--mm-shadow);
        overflow: hidden;
    }

    .mm-topbar {
        display: grid;
        grid-template-columns: 110px minmax(0, 1fr);
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--mm-border);
        background: #020617;
    }

    .mm-logo {
        width: 96px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--mm-border);
        border-radius: 14px;
        background: #000;
        box-shadow: inset 0 0 24px rgba(255,255,255,.025);
    }

    .mm-logo img { max-width: 74px; max-height: 36px; object-fit: contain; }
    .mm-title-wrap { min-width: 0; text-align: center; }

    .mm-kicker {
        margin-bottom: 5px;
        color: var(--mm-green);
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .18em;
        text-transform: uppercase;
        text-shadow: 0 0 10px rgba(34,197,94,.38);
    }

    .mm-title {
        margin: 0;
        color: #fff;
        font-size: clamp(21px, 2.2vw, 32px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.045em;
        text-transform: uppercase;
    }

    .mm-subtitle { margin-top: 7px; color: #94a3b8; font-size: 12px; font-weight: 650; }
    .mm-content { padding: 16px; }

    .mm-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .mm-kpi-card {
        position: relative;
        min-width: 0;
        min-height: 132px;
        overflow: hidden;
        border-radius: var(--mm-radius);
        border: 1px solid var(--mm-border);
        background: #000;
    }

    .mm-kpi-card::after {
        content: "";
        position: absolute;
        inset: auto -20px -42px -20px;
        height: 86px;
        opacity: .35;
        filter: blur(18px);
        pointer-events: none;
    }

    .mm-kpi-card.green { box-shadow: var(--mm-glow-green); border-color: rgba(34,197,94,.24); }
    .mm-kpi-card.blue { box-shadow: var(--mm-glow-blue); border-color: rgba(59,130,246,.24); }
    .mm-kpi-card.red { box-shadow: var(--mm-glow-red); border-color: rgba(239,68,68,.24); }
    .mm-kpi-card.gold { box-shadow: var(--mm-glow-gold); border-color: rgba(245,158,11,.24); }
    .mm-kpi-card.green::after { background: var(--mm-green); }
    .mm-kpi-card.blue::after { background: var(--mm-blue); }
    .mm-kpi-card.red::after { background: var(--mm-red); }
    .mm-kpi-card.gold::after { background: var(--mm-gold); }

    .mm-kpi-inner { position: relative; z-index: 2; padding: 13px; }
    .mm-kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
    .mm-kpi-label { color: #94a3b8; font-size: 10px; font-weight: 950; letter-spacing: .13em; text-transform: uppercase; }
    .mm-dot { width: 8px; height: 8px; border-radius: 50%; flex: 0 0 8px; }
    .mm-dot.green { background: var(--mm-green); box-shadow: 0 0 12px var(--mm-green); }
    .mm-dot.blue { background: var(--mm-blue); box-shadow: 0 0 12px var(--mm-blue); }
    .mm-dot.red { background: var(--mm-red); box-shadow: 0 0 12px var(--mm-red); }
    .mm-dot.gold { background: var(--mm-gold); box-shadow: 0 0 12px var(--mm-gold); }

    .mm-kpi-val { color: #fff; font-size: clamp(30px, 3vw, 46px); font-weight: 950; line-height: .95; letter-spacing: -.06em; word-break: break-word; }
    .mm-kpi-card.green .mm-kpi-val { color: #86efac; text-shadow: 0 0 14px rgba(34,197,94,.35); }
    .mm-kpi-card.blue .mm-kpi-val { color: #93c5fd; text-shadow: 0 0 14px rgba(59,130,246,.35); }
    .mm-kpi-card.red .mm-kpi-val { color: #fca5a5; text-shadow: 0 0 14px rgba(239,68,68,.32); }
    .mm-kpi-card.gold .mm-kpi-val { color: #fde68a; text-shadow: 0 0 14px rgba(245,158,11,.32); }

    .mm-main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 14px; }

    .mm-card {
        min-width: 0;
        border-radius: var(--mm-radius);
        border: 1px solid var(--mm-border);
        background: #000;
        box-shadow: 0 16px 36px rgba(0,0,0,.52);
        overflow: hidden;
        margin-bottom: 14px;
    }

    .mm-card-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 14px; border-bottom: 1px solid var(--mm-border); background: #020617; }
    .mm-card-title { color: #fff; font-size: 13px; font-weight: 950; letter-spacing: .08em; text-transform: uppercase; }
    .mm-chip { height: 24px; display: inline-flex; align-items: center; padding: 0 9px; border-radius: 999px; border: 1px solid rgba(34,197,94,.20); background: rgba(34,197,94,.08); color: #bbf7d0; font-size: 10px; font-weight: 950; text-transform: uppercase; }
    .mm-chart-wrap { height: 500px; padding: 14px; background: radial-gradient(circle at 50% 0%, rgba(59,130,246,.10), transparent 38%), #000; }
    .mm-table-wrap { overflow: auto; height: 500px; }

    .mm-table { width: 100%; min-width: 920px; border-collapse: collapse; text-align: right; }
    .mm-table th { padding: 10px 11px; background: #020617; border-bottom: 1px solid var(--mm-border); border-right: 1px solid var(--mm-border); color: #64748b; font-size: 10px; font-weight: 950; letter-spacing: .10em; text-transform: uppercase; white-space: nowrap; }
    .mm-table td { padding: 10px 11px; border-bottom: 1px solid rgba(255,255,255,.055); border-right: 1px solid rgba(255,255,255,.04); color: #e2e8f0; font-size: 12px; font-weight: 800; white-space: nowrap; }
    .mm-table th:first-child, .mm-table td:first-child { text-align: left; }
    .mm-table tr:nth-child(even) td { background: rgba(255,255,255,.018); }
    .mm-table tbody tr:hover td { background: rgba(59,130,246,.07); }
    .mm-table tfoot td { background: #020617; color: #fde68a; font-size: 13px; font-weight: 950; text-shadow: 0 0 10px rgba(245,158,11,.25); }
    .mm-blue-text { color: #93c5fd !important; text-shadow: 0 0 10px rgba(59,130,246,.25); }

    .mm-footer { padding: 13px 16px; border-top: 1px solid var(--mm-border); display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; color: #64748b; font-size: 12px; font-weight: 700; }
    .mm-last-update { color: #86efac; text-shadow: 0 0 10px rgba(34,197,94,.25); }

    .dataTables_wrapper { color: #cbd5e1; padding: 12px; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { color: #94a3b8 !important; font-size: 12px; font-weight: 800; margin-bottom: 10px; }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select { background: #020617; border: 1px solid var(--mm-border-strong); border-radius: 10px; color: #f8fafc; padding: 7px 10px; outline: none; }
    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus { border-color: rgba(59,130,246,.6); box-shadow: 0 0 0 3px rgba(59,130,246,.16); }
    table.dataTable.no-footer { border-bottom: 1px solid var(--mm-border); }
    table.dataTable thead th, table.dataTable thead td { border-bottom: 1px solid var(--mm-border); }
    .dataTables_wrapper .dataTables_paginate .paginate_button { border: 1px solid var(--mm-border) !important; background: #020617 !important; color: #cbd5e1 !important; border-radius: 8px !important; margin: 0 2px; }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background: rgba(59,130,246,.18) !important; color: #93c5fd !important; border-color: rgba(59,130,246,.35) !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: rgba(34,197,94,.12) !important; color: #bbf7d0 !important; border-color: rgba(34,197,94,.25) !important; }

    @media (max-width: 1200px) {
        .mm-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .mm-main-grid { grid-template-columns: 1fr; }
        .mm-topbar { grid-template-columns: 1fr; }
        .mm-logo { width: 100%; }
    }

    @media (max-width: 640px) {
        .mm-page { padding: 8px; }
        .mm-content { padding: 10px; }
        .mm-kpi-grid { grid-template-columns: 1fr; }
        .mm-chart-wrap, .mm-table-wrap { height: 420px; }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="mm-page">
                <div class="mm-board">

                    <div class="mm-topbar">
                        <div class="mm-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="mm-title-wrap">
                            <div class="mm-kicker">Market Mapping Monitor</div>
                            <h1 class="mm-title">Market Share Geprekinaja</h1>
                            <div class="mm-subtitle">Dashboard Mapping Market dari Google Sheets: Existing, Sehat/Optimum, Agresif, Zona Prioritas, SPK, dan ST Marketing BI.</div>
                        </div>
                    </div>

                    <div class="mm-content">
                        <div class="mm-kpi-grid">
                            <section class="mm-kpi-card green"><div class="mm-kpi-inner"><div class="mm-kpi-top"><div class="mm-kpi-label">Existing / Outlet Aktif</div><span class="mm-dot green"></span></div><div class="mm-kpi-val">{{ number_format($metrics['outlet_aktif'] ?? 0, 0, ',', '.') }}</div></div></section>
                            <section class="mm-kpi-card blue"><div class="mm-kpi-inner"><div class="mm-kpi-top"><div class="mm-kpi-label">Sehat / Optimum</div><span class="mm-dot blue"></span></div><div class="mm-kpi-val">{{ number_format($metrics['optimum'] ?? 0, 0, ',', '.') }}</div></div></section>
                            <section class="mm-kpi-card red"><div class="mm-kpi-inner"><div class="mm-kpi-top"><div class="mm-kpi-label">Agresif</div><span class="mm-dot red"></span></div><div class="mm-kpi-val">{{ number_format($metrics['agresif'] ?? 0, 0, ',', '.') }}</div></div></section>
                            <section class="mm-kpi-card gold"><div class="mm-kpi-inner"><div class="mm-kpi-top"><div class="mm-kpi-label">% Market Share</div><span class="mm-dot gold"></span></div><div class="mm-kpi-val">{{ number_format($metrics['market_share'] ?? 0, 2, '.', '') }}%</div></div></section>
                        </div>

                        <div class="mm-main-grid">
                            <section class="mm-card">
                                <div class="mm-card-head"><div class="mm-card-title">Top 15 Kecamatan Agresif</div><div class="mm-chip">Horizontal Chart</div></div>
                                <div class="mm-chart-wrap"><canvas id="marketChart"></canvas></div>
                            </section>

                            <section class="mm-card">
                                <div class="mm-card-head"><div class="mm-card-title">Database Mapping Market dari Spreadsheet</div><div class="mm-chip">Search Table</div></div>
                                <div class="mm-table-wrap">
                                    <table id="mappingMarketTable" class="mm-table display nowrap">
                                        <thead>
                                            <tr>
                                                <th>Provinsi</th>
                                                <th>Kota/Kabupaten</th>
                                                <th>Kecamatan</th>
                                                <th>Existing</th>
                                                <th>Sehat / Optimum</th>
                                                <th>Agresif</th>
                                                <th>Traffic Generator</th>
                                                <th>Zona Prioritas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($mappingRows as $row)
                                                <tr>
                                                    <td>{{ $row['provinsi'] ?? '-' }}</td>
                                                    <td>{{ $row['kota_kabupaten'] ?? '-' }}</td>
                                                    <td style="text-transform: capitalize;">{{ $row['kecamatan'] ?? '-' }}</td>
                                                    <td class="mm-blue-text">{{ number_format($row['existing'] ?? 0, 0, ',', '.') }}</td>
                                                    <td class="mm-blue-text">{{ number_format($row['sehat'] ?? 0, 0, ',', '.') }}</td>
                                                    <td class="mm-blue-text">{{ number_format($row['agresif'] ?? 0, 0, ',', '.') }}</td>
                                                    <td style="white-space: normal; min-width: 220px;">{{ $row['traffic_generator'] ?? '-' }}</td>
                                                    <td style="white-space: normal; min-width: 220px;">{{ $row['zona_prioritas'] ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="8" style="text-align:center; padding: 20px;">Tidak ada data mapping market dari spreadsheet.</td></tr>
                                            @endforelse
                                        </tbody>
                                        @if($grandTotal > 0)
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3">GRAND TOTAL AGRESIF</td>
                                                    <td>{{ number_format($metrics['outlet_aktif'] ?? 0, 0, ',', '.') }}</td>
                                                    <td>{{ number_format($metrics['optimum'] ?? 0, 0, ',', '.') }}</td>
                                                    <td>{{ number_format($metrics['agresif'] ?? 0, 0, ',', '.') }}</td>
                                                    <td colspan="2">Source: Google Sheets Mapping Market</td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </section>
                        </div>

                        <div class="mm-kpi-grid" style="margin-top:14px;">
                            <section class="mm-kpi-card green"><div class="mm-kpi-inner"><div class="mm-kpi-top"><div class="mm-kpi-label">Total SPK</div><span class="mm-dot green"></span></div><div class="mm-kpi-val">{{ number_format($marketingBiSummary['total_spk'] ?? 0, 0, ',', '.') }}</div></div></section>
                            <section class="mm-kpi-card blue"><div class="mm-kpi-inner"><div class="mm-kpi-top"><div class="mm-kpi-label">Total ST</div><span class="mm-dot blue"></span></div><div class="mm-kpi-val">{{ number_format($marketingBiSummary['total_st'] ?? 0, 0, ',', '.') }}</div></div></section>
                            <section class="mm-kpi-card gold"><div class="mm-kpi-inner"><div class="mm-kpi-top"><div class="mm-kpi-label">Rasio ST / SPK</div><span class="mm-dot gold"></span></div><div class="mm-kpi-val">{{ number_format($marketingBiSummary['ratio_st'] ?? 0, 2, '.', '') }}%</div></div></section>
                            <section class="mm-kpi-card red"><div class="mm-kpi-inner"><div class="mm-kpi-top"><div class="mm-kpi-label">Baris Aktif</div><span class="mm-dot red"></span></div><div class="mm-kpi-val">{{ number_format($marketingBiSummary['active_rows'] ?? 0, 0, ',', '.') }}</div></div></section>
                        </div>

                        <section class="mm-card">
                            <div class="mm-card-head"><div class="mm-card-title">SPK & ST Marketing BI</div><div class="mm-chip">Search Table</div></div>
                            <div class="mm-table-wrap" style="height:420px;">
                                <table id="marketingBiTable" class="mm-table display nowrap" style="min-width:980px;">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Bulan</th>
                                            <th>Tahun</th>
                                            <th>Outlet / Nama</th>
                                            <th>SPK</th>
                                            <th>ST</th>
                                            <th>Rasio</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($marketingBiRows as $row)
                                            @php
                                                $spk = (float) ($row['spk'] ?? 0);
                                                $st = (float) ($row['st'] ?? 0);
                                                $ratio = $spk > 0 ? ($st / $spk) * 100 : 0;
                                            @endphp
                                            <tr>
                                                <td>{{ $row['tanggal'] ?? '-' }}</td>
                                                <td>{{ $row['bulan'] ?? '-' }}</td>
                                                <td>{{ $row['tahun'] ?? '-' }}</td>
                                                <td style="text-align:left;">{{ $row['label'] ?? ($row['outlet'] ?? '-') }}</td>
                                                <td class="mm-blue-text">{{ number_format($spk, 0, ',', '.') }}</td>
                                                <td class="mm-blue-text">{{ number_format($st, 0, ',', '.') }}</td>
                                                <td>{{ number_format($ratio, 2, '.', '') }}%</td>
                                                <td style="white-space: normal; min-width:220px; text-align:left;">{{ $row['keterangan'] ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="8" style="text-align:center; padding:20px;">Tidak ada data SPK & ST Marketing BI.</td></tr>
                                        @endforelse
                                    </tbody>
                                    @if(!empty($marketingBiRows))
                                        <tfoot>
                                            <tr>
                                                <td colspan="4">GRAND TOTAL SPK & ST</td>
                                                <td>{{ number_format($marketingBiSummary['total_spk'] ?? 0, 0, ',', '.') }}</td>
                                                <td>{{ number_format($marketingBiSummary['total_st'] ?? 0, 0, ',', '.') }}</td>
                                                <td>{{ number_format($marketingBiSummary['ratio_st'] ?? 0, 2, '.', '') }}%</td>
                                                <td>Source: SPK & ST MARKETING BI</td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </section>
                    </div>

                    <div class="mm-footer">
                        <div class="mm-last-update">Data Last Updated: {{ $lastSyncAt }}</div>
                        <div>Internal BOD Dashboard - Source: Google Sheets Mapping Market</div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
    const ctx = document.getElementById('marketChart');
    const cLabels = @json($chartLabels);
    const cData = @json($chartData);

    if (ctx) {
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#64748b';

        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 800, 0);
        gradient.addColorStop(0, 'rgba(59,130,246,.30)');
        gradient.addColorStop(.55, 'rgba(59,130,246,.78)');
        gradient.addColorStop(1, 'rgba(34,197,94,.98)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: cLabels,
                datasets: [{
                    label: 'Jumlah Titik Potensial',
                    data: cData,
                    backgroundColor: gradient,
                    borderColor: 'rgba(147,197,253,.82)',
                    borderWidth: 1,
                    borderRadius: 9,
                    borderSkipped: false,
                    maxBarThickness: 30
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                layout: { padding: { top: 8, right: 34, bottom: 0, left: 0 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#020617',
                        titleColor: '#f8fafc',
                        bodyColor: '#e2e8f0',
                        borderColor: 'rgba(255,255,255,.14)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(255,255,255,.07)' }, border: { display: false }, ticks: { color: '#64748b', precision: 0 } },
                    y: { grid: { display: false }, border: { display: false }, ticks: { color: '#cbd5e1', font: { size: 11, weight: '800' } } }
                }
            },
            plugins: [{
                id: 'rightLabels',
                afterDatasetsDraw(chart) {
                    const chartCtx = chart.ctx;
                    chartCtx.save();
                    chartCtx.font = '800 11px Inter';
                    chartCtx.textAlign = 'left';
                    chartCtx.textBaseline = 'middle';
                    chart.data.datasets.forEach((dataset, i) => {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach((bar, index) => {
                            const data = dataset.data[index];
                            if (data > 0) {
                                chartCtx.fillStyle = '#86efac';
                                chartCtx.shadowColor = 'rgba(34,197,94,.45)';
                                chartCtx.shadowBlur = 8;
                                chartCtx.fillText(data, bar.x + 8, bar.y);
                            }
                        });
                    });
                    chartCtx.restore();
                }
            }]
        });
    }

    $(document).ready(function () {
        if ($('#mappingMarketTable tbody tr').length > 0 && $('#mappingMarketTable tbody td[colspan]').length === 0) {
            $('#mappingMarketTable').DataTable({
                pageLength: 25,
                scrollX: true,
                ordering: true,
                searching: true,
                lengthChange: true,
                info: true,
                language: {
                    search: "Search Mapping:",
                    lengthMenu: "Show _MENU_ rows",
                    zeroRecords: "Data mapping tidak ditemukan",
                    info: "Showing _START_ to _END_ of _TOTAL_ rows",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(filtered from _MAX_ total rows)",
                    paginate: { previous: "Prev", next: "Next" }
                }
            });
        }

        if ($('#marketingBiTable tbody tr').length > 0 && $('#marketingBiTable tbody td[colspan]').length === 0) {
            $('#marketingBiTable').DataTable({
                pageLength: 25,
                scrollX: true,
                ordering: true,
                searching: true,
                lengthChange: true,
                info: true,
                language: {
                    search: "Search SPK/ST:",
                    lengthMenu: "Show _MENU_ rows",
                    zeroRecords: "Data SPK/ST tidak ditemukan",
                    info: "Showing _START_ to _END_ of _TOTAL_ rows",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(filtered from _MAX_ total rows)",
                    paginate: { previous: "Prev", next: "Next" }
                }
            });
        }
    });
</script>

@include('Temp.internal.footer_internal')

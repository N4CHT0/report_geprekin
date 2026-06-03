{{-- resources/views/Investor/DashboardEcommerce.blade.php --}}
@include('Temp.internal.header_internal')

@php
    function formatBillionMillion($angka) {
        if ($angka >= 1000000000) return 'Rp' . number_format($angka / 1000000000, 2, '.', '') . ' M';
        if ($angka >= 1000000) return 'Rp' . number_format($angka / 1000000, 2, '.', '') . ' JT';
        return 'Rp' . number_format($angka, 0, ',', '.');
    }

    $selectedYears = $filters['tahun'] ?? [];
    $selectedMonths = $filters['bulan'] ?? [];
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
    :root {
        --ihsg-black: #000000;
        --ihsg-black-2: #020617;
        --ihsg-panel: #030712;
        --ihsg-panel-2: #050b17;
        --ihsg-border: rgba(255,255,255,.08);
        --ihsg-border-strong: rgba(255,255,255,.14);
        --ihsg-text: #f8fafc;
        --ihsg-muted: #64748b;

        --ihsg-green: #22c55e;
        --ihsg-green-soft: rgba(34,197,94,.14);
        --ihsg-red: #ef4444;
        --ihsg-blue: #3b82f6;
        --ihsg-blue-soft: rgba(59,130,246,.15);
        --ihsg-orange: #f97316;
        --ihsg-purple: #a855f7;
        --ihsg-yellow: #eab308;

        --ihsg-radius: 18px;
        --ihsg-shadow: 0 22px 48px rgba(0,0,0,.72);
        --ihsg-glow-blue: 0 0 22px rgba(59,130,246,.28);
        --ihsg-glow-green: 0 0 22px rgba(34,197,94,.24);
        --ihsg-glow-red: 0 0 22px rgba(239,68,68,.22);
    }

    * {
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        background: var(--ihsg-black);
    }

    body {
        color: var(--ihsg-text);
    }

    .ihsg-shell {
        min-height: 100vh;
        padding: 14px;
        background:
            radial-gradient(circle at 8% 0%, rgba(34,197,94,.10), transparent 24%),
            radial-gradient(circle at 92% 0%, rgba(59,130,246,.12), transparent 24%),
            linear-gradient(180deg, #000 0%, #020617 100%);
    }

    .ihsg-board {
        min-height: calc(100vh - 28px);
        border: 1px solid var(--ihsg-border);
        border-radius: 22px;
        background: linear-gradient(180deg, #020617 0%, #000 100%);
        box-shadow: var(--ihsg-shadow);
        overflow: visible;
    }

    .ihsg-topbar {
        display: grid;
        grid-template-columns: 110px minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--ihsg-border);
        background: #020617;
    }

    .ihsg-logo {
        width: 96px;
        height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--ihsg-border);
        border-radius: 14px;
        background: #000;
        box-shadow: inset 0 0 24px rgba(255,255,255,.025);
    }

    .ihsg-logo img {
        max-width: 74px;
        max-height: 36px;
        object-fit: contain;
    }

    .ihsg-title-wrap {
        min-width: 0;
        text-align: center;
    }

    .ihsg-kicker {
        margin-bottom: 5px;
        color: var(--ihsg-green);
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .18em;
        text-transform: uppercase;
        text-shadow: 0 0 10px rgba(34,197,94,.38);
    }

    .ihsg-title {
        margin: 0;
        color: #fff;
        font-size: clamp(21px, 2.2vw, 32px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.045em;
        text-transform: uppercase;
    }

    .ihsg-subtitle {
        margin-top: 7px;
        color: #94a3b8;
        font-size: 12px;
        font-weight: 650;
    }

    .ihsg-status {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ihsg-pill {
        height: 36px;
        display: inline-flex;
        align-items: center;
        gap: 9px;
        padding: 0 12px;
        border-radius: 999px;
        border: 1px solid var(--ihsg-border);
        background: #000;
        color: #dbeafe;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--ihsg-green);
        box-shadow: 0 0 12px var(--ihsg-green);
    }

    .ihsg-content {
        padding: 16px;
    }

    .filter-row {
        position: relative;
        z-index: 50;
        display: flex;
        justify-content: flex-end;
        margin-bottom: 14px;
    }

    .filter-wrap form {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .multi-dropdown {
        position: relative;
        min-width: 132px;
    }

    .multi-drop-btn {
        width: 100%;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 12px;
        cursor: pointer;
        border-radius: 999px;
        border: 1px solid rgba(59,130,246,.26);
        background: #000;
        color: #eaf2ff;
        font-size: 12px;
        font-weight: 850;
        user-select: none;
    }

    .multi-drop-btn:hover,
    .multi-drop-btn.active {
        border-color: rgba(34,197,94,.50);
        box-shadow: 0 0 0 3px rgba(34,197,94,.08);
    }

    .drop-label {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .badge-count {
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--ihsg-blue-soft);
        color: #bfdbfe;
        border: 1px solid rgba(59,130,246,.25);
        font-size: 10px;
        font-weight: 950;
    }

    .multi-drop-content {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        min-width: 210px;
        max-height: 270px;
        overflow-y: auto;
        padding: 7px;
        border-radius: 14px;
        border: 1px solid var(--ihsg-border-strong);
        background: #020617;
        box-shadow: 0 18px 44px rgba(0,0,0,.76);
        z-index: 99999;
    }

    .multi-drop-content.show {
        display: block;
    }

    .multi-drop-content label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        padding: 10px;
        border-radius: 10px;
        color: #cbd5e1;
        cursor: pointer;
        font-size: 12px;
        font-weight: 650;
    }

    .multi-drop-content label:hover {
        background: rgba(34,197,94,.08);
        color: #fff;
    }

    .multi-drop-content input[type="checkbox"] {
        accent-color: var(--ihsg-green);
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .kpi-card {
        position: relative;
        min-width: 0;
        min-height: 132px;
        overflow: hidden;
        border-radius: var(--ihsg-radius);
        border: 1px solid var(--ihsg-border);
        background: #000;
    }

    .kpi-card::after {
        content: "";
        position: absolute;
        inset: auto -20px -42px -20px;
        height: 86px;
        opacity: .35;
        filter: blur(18px);
        pointer-events: none;
    }

    .kpi-omset { box-shadow: var(--ihsg-glow-green); border-color: rgba(34,197,94,.24); }
    .kpi-target { box-shadow: var(--ihsg-glow-blue); border-color: rgba(59,130,246,.24); }
    .kpi-variance { box-shadow: 0 0 22px rgba(249,115,22,.20); border-color: rgba(249,115,22,.24); }
    .kpi-achievement { box-shadow: var(--ihsg-glow-green); border-color: rgba(34,197,94,.24); }

    .kpi-omset::after { background: var(--ihsg-green); }
    .kpi-target::after { background: var(--ihsg-blue); }
    .kpi-variance::after { background: var(--ihsg-orange); }
    .kpi-achievement::after { background: var(--ihsg-green); }

    .kpi-inner {
        position: relative;
        z-index: 2;
        padding: 13px;
    }

    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 16px;
    }

    .kpi-label {
        color: #94a3b8;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .kpi-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.035);
        border: 1px solid var(--ihsg-border);
        color: #eaf2ff;
        font-size: 12px;
    }

    .kpi-value {
        color: #fff;
        font-size: clamp(24px, 2.35vw, 38px);
        font-weight: 950;
        line-height: .95;
        letter-spacing: -.06em;
        word-break: break-word;
    }

    .kpi-omset .kpi-value,
    .kpi-achievement .kpi-value {
        color: #86efac;
        text-shadow: 0 0 14px rgba(34,197,94,.35);
    }

    .kpi-target .kpi-value {
        color: #93c5fd;
        text-shadow: 0 0 14px rgba(59,130,246,.35);
    }

    .kpi-variance .kpi-value {
        color: #fdba74;
        text-shadow: 0 0 14px rgba(249,115,22,.32);
    }

    .kpi-foot {
        margin-top: 11px;
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .chart-card,
    .summary-card {
        border-radius: var(--ihsg-radius);
        border: 1px solid var(--ihsg-border);
        background: #000;
        box-shadow: 0 16px 36px rgba(0,0,0,.52);
        overflow: hidden;
    }

    .chart-card {
        min-width: 0;
    }

    .chart-head,
    .summary-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--ihsg-border);
        background: #020617;
    }

    .chart-head-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .chart-icon {
        width: 34px;
        height: 34px;
        border-radius: 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(59,130,246,.13);
        border: 1px solid rgba(59,130,246,.24);
        color: #bfdbfe;
        box-shadow: 0 0 16px rgba(59,130,246,.18);
    }

    .chart-icon.platform {
        background: rgba(168,85,247,.13);
        border-color: rgba(168,85,247,.24);
        color: #d8b4fe;
        box-shadow: 0 0 16px rgba(168,85,247,.18);
    }

    .chart-title,
    .summary-title {
        margin: 0;
        color: #fff;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .chart-subtitle {
        margin-top: 4px;
        color: #64748b;
        font-size: 11px;
        font-weight: 650;
    }

    .mini-pill,
    .summary-chip {
        height: 24px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0 9px;
        border-radius: 999px;
        border: 1px solid rgba(34,197,94,.20);
        background: rgba(34,197,94,.08);
        color: #bbf7d0;
        font-size: 10px;
        font-weight: 950;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .chart-canvas-wrap {
        position: relative;
        height: 370px;
        padding: 14px;
        background:
            radial-gradient(circle at 50% 0%, rgba(59,130,246,.10), transparent 38%),
            #000;
    }

    .chart-footer-note {
        margin: 0 14px 14px;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid var(--ihsg-border);
        background: #020617;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .summary-wrap {
        overflow: auto;
        max-height: 360px;
    }

    .summary-table {
        width: 100%;
        min-width: 520px;
        border-collapse: collapse;
        text-align: right;
    }

    .summary-table th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 10px 11px;
        background: #020617;
        border-bottom: 1px solid var(--ihsg-border);
        border-right: 1px solid var(--ihsg-border);
        color: #64748b;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .10em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .summary-table td {
        padding: 10px 11px;
        border-bottom: 1px solid rgba(255,255,255,.055);
        border-right: 1px solid rgba(255,255,255,.04);
        color: #e2e8f0;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .summary-table th:first-child,
    .summary-table td:first-child {
        text-align: left;
    }

    .summary-table tr:nth-child(even) td {
        background: rgba(255,255,255,.018);
    }

    .summary-blue { color: #93c5fd !important; text-shadow: 0 0 10px rgba(59,130,246,.25); }
    .summary-green { color: #86efac !important; text-shadow: 0 0 10px rgba(34,197,94,.25); }
    .summary-orange { color: #fdba74 !important; text-shadow: 0 0 10px rgba(249,115,22,.22); }
    .summary-purple { color: #d8b4fe !important; text-shadow: 0 0 10px rgba(168,85,247,.22); }
    .summary-yellow { color: #fde047 !important; text-shadow: 0 0 10px rgba(234,179,8,.20); }

    .footer-bar {
        padding-top: 13px;
        border-top: 1px solid var(--ihsg-border);
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        color: #64748b;
        font-size: 12px;
        font-weight: 700;
    }

    .last-update {
        color: #86efac;
        text-shadow: 0 0 10px rgba(34,197,94,.25);
    }

    @media (max-width: 1200px) {
        .kpi-grid,
        .chart-grid,
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .chart-grid,
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .ihsg-topbar {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .ihsg-logo,
        .ihsg-status,
        .filter-row {
            justify-content: center;
        }
    }

    @media (max-width: 640px) {
        .ihsg-shell {
            padding: 8px;
        }

        .ihsg-content {
            padding: 10px;
        }

        .kpi-grid {
            grid-template-columns: 1fr;
        }

        .filter-wrap form {
            width: 100%;
            flex-direction: column;
        }

        .multi-dropdown,
        .multi-drop-btn {
            width: 100%;
        }

        .chart-canvas-wrap {
            height: 310px;
        }

        .footer-bar {
            flex-direction: column;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="ihsg-shell">
                <div class="ihsg-board">

                    <div class="ihsg-topbar">
                        <div class="ihsg-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="ihsg-title-wrap">
                            <div class="ihsg-kicker">Executive Market Monitor</div>
                            <h1 class="ihsg-title">Sales E-Commerce Overview</h1>
                            <div class="ihsg-subtitle">Full black IHSG dashboard untuk omset, target, platform, dan monthly performance.</div>
                        </div>

                        <div class="ihsg-status">
                            <div class="ihsg-pill">
                                <span class="live-dot"></span>
                                Live Internal
                            </div>
                            <div class="ihsg-pill">
                                {{ $lastSyncAt }}
                            </div>
                        </div>
                    </div>

                    <div class="ihsg-content">
                        <div class="filter-row">
                            <div class="filter-wrap">
                                <form method="GET" action="{{ route('investor.sales.dashboardBOD.ecommerce') }}" id="filterForm">
                                    <div class="multi-dropdown">
                                        <div class="multi-drop-btn" onclick="toggleDropdown('dropTahun')">
                                            <div class="drop-label">
                                                <span>Tahun</span>
                                                <span class="badge-count">{{ count($selectedYears) }}</span>
                                            </div>
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                        <div id="dropTahun" class="multi-drop-content">
                                            @foreach(($availableYears ?? []) as $year)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="tahun[]"
                                                        value="{{ $year }}"
                                                        onchange="document.getElementById('filterForm').submit()"
                                                        {{ in_array((string)$year, $selectedYears) ? 'checked' : '' }}>
                                                    <span>{{ $year }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="multi-dropdown">
                                        <div class="multi-drop-btn" onclick="toggleDropdown('dropBulan')">
                                            <div class="drop-label">
                                                <span>Bulan</span>
                                                <span class="badge-count">{{ count($selectedMonths) }}</span>
                                            </div>
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                        <div id="dropBulan" class="multi-drop-content">
                                            @foreach(($monthLabels ?? []) as $index => $monthLabel)
                                                <label>
                                                    <input
                                                        type="checkbox"
                                                        name="bulan[]"
                                                        value="{{ $index + 1 }}"
                                                        onchange="document.getElementById('filterForm').submit()"
                                                        {{ in_array((string)($index + 1), $selectedMonths) ? 'checked' : '' }}>
                                                    <span>{{ $monthLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="kpi-grid">
                            <div class="kpi-card kpi-omset">
                                <div class="kpi-inner">
                                    <div class="kpi-top">
                                        <div class="kpi-label">Omset</div>
                                        <div class="kpi-icon"><i class="fas fa-wallet"></i></div>
                                    </div>
                                    <div class="kpi-value">{{ formatBillionMillion($metrics['omset'] ?? 0) }}</div>
                                    <div class="kpi-foot">Total revenue terakumulasi</div>
                                </div>
                            </div>

                            <div class="kpi-card kpi-target">
                                <div class="kpi-inner">
                                    <div class="kpi-top">
                                        <div class="kpi-label">Target</div>
                                        <div class="kpi-icon"><i class="fas fa-bullseye"></i></div>
                                    </div>
                                    <div class="kpi-value">{{ formatBillionMillion($metrics['target'] ?? 0) }}</div>
                                    <div class="kpi-foot">Target periode aktif</div>
                                </div>
                            </div>

                            <div class="kpi-card kpi-variance">
                                <div class="kpi-inner">
                                    <div class="kpi-top">
                                        <div class="kpi-label">Variance</div>
                                        <div class="kpi-icon"><i class="fas fa-chart-pie"></i></div>
                                    </div>
                                    <div class="kpi-value">{{ formatBillionMillion($metrics['variance'] ?? 0) }}</div>
                                    <div class="kpi-foot">Selisih terhadap target</div>
                                </div>
                            </div>

                            <div class="kpi-card kpi-achievement">
                                <div class="kpi-inner">
                                    <div class="kpi-top">
                                        <div class="kpi-label">Achievement</div>
                                        <div class="kpi-icon"><i class="fas fa-trophy"></i></div>
                                    </div>
                                    <div class="kpi-value">{{ number_format($metrics['pencapaian'] ?? 0, 2, '.', '') }}%</div>
                                    <div class="kpi-foot">Tingkat pencapaian target</div>
                                </div>
                            </div>
                        </div>

                        <div class="chart-grid">
                            <div class="chart-card">
                                <div class="chart-head">
                                    <div class="chart-head-left">
                                        <div class="chart-icon">
                                            <i class="fas fa-chart-column"></i>
                                        </div>
                                        <div>
                                            <h3 class="chart-title">Omset Bulanan</h3>
                                            <div class="chart-subtitle">Pergerakan omset per bulan pada periode terpilih</div>
                                        </div>
                                    </div>

                                    <div class="mini-pill">
                                        Monthly
                                    </div>
                                </div>

                                <div class="chart-canvas-wrap">
                                    <canvas id="monthlyChart"></canvas>
                                </div>

                                <div class="chart-footer-note">
                                    Insight: fokus pada bulan dengan lonjakan tertinggi untuk benchmark strategi penjualan berikutnya.
                                </div>
                            </div>

                            <div class="chart-card">
                                <div class="chart-head">
                                    <div class="chart-head-left">
                                        <div class="chart-icon platform">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div>
                                            <h3 class="chart-title">Performa per Platform</h3>
                                            <div class="chart-subtitle">Distribusi kontribusi omset per channel e-commerce</div>
                                        </div>
                                    </div>

                                    <div class="mini-pill">
                                        Channel
                                    </div>
                                </div>

                                <div class="chart-canvas-wrap">
                                    <canvas id="platformChart"></canvas>
                                </div>

                                <div class="chart-footer-note">
                                    Insight: platform dominan bisa dijadikan kanal utama untuk scaling budget dan promo.
                                </div>
                            </div>
                        </div>

                        <div class="summary-grid">
                            <div class="summary-card">
                                <div class="summary-head">
                                    <div class="summary-title">Monthly Summary</div>
                                    <div class="summary-chip">Table</div>
                                </div>
                                <div class="summary-wrap">
                                    <table class="summary-table">
                                        <thead>
                                            <tr>
                                                <th>Bulan</th>
                                                <th>Omset</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(($monthLabels ?? []) as $i => $monthLabel)
                                                <tr>
                                                    <td>{{ $monthLabel }}</td>
                                                    <td class="summary-blue">Rp {{ number_format(($chartDataMonthly[$i] ?? 0), 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="summary-card">
                                <div class="summary-head">
                                    <div class="summary-title">Platform Summary</div>
                                    <div class="summary-chip">Channel</div>
                                </div>
                                <div class="summary-wrap">
                                    <table class="summary-table">
                                        <thead>
                                            <tr>
                                                <th>Platform</th>
                                                <th>Omset</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>ShopeeFood</td>
                                                <td class="summary-orange">Rp {{ number_format(($platformTotals['shopeefood'] ?? 0), 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>GoFood</td>
                                                <td class="summary-blue">Rp {{ number_format(($platformTotals['gofood'] ?? 0), 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>GrabFood</td>
                                                <td class="summary-green">Rp {{ number_format(($platformTotals['grabfood'] ?? 0), 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>TikTok</td>
                                                <td class="summary-purple">Rp {{ number_format(($platformTotals['tiktok'] ?? 0), 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <td>QPon</td>
                                                <td class="summary-yellow">Rp {{ number_format(($platformTotals['qpon'] ?? 0), 0, ',', '.') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="footer-bar">
                            <div class="last-update">
                                Data Last Updated: {{ $lastSyncAt }}
                            </div>
                            <div>
                                Internal BOD Dashboard • Full IHSG Hardcore Theme
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function toggleDropdown(id) {
        document.querySelectorAll('.multi-drop-content').forEach((content) => {
            if (content.id !== id) content.classList.remove('show');
        });
        document.getElementById(id).classList.toggle('show');
    }

    window.addEventListener('click', function(event) {
        if (!event.target.closest('.multi-dropdown')) {
            document.querySelectorAll('.multi-drop-content').forEach((content) => {
                content.classList.remove('show');
            });
        }
    });

    function formatShortCurrency(value) {
        const num = Number(value || 0);
        if (num === 0) return '0';
        if (num >= 1000000000) return (num / 1000000000).toFixed(2) + 'B';
        if (num >= 1000000) return (num / 1000000).toFixed(2) + 'M';
        return new Intl.NumberFormat('id-ID').format(num);
    }

    function formatRupiah(value) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value || 0));
    }

    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#64748b';

    const commonGridColor = 'rgba(255,255,255,.07)';
    const commonTickColor = '#64748b';

    const monthlyCtx = document.getElementById('monthlyChart');
    const monthlyData = @json($chartDataMonthly ?? []);
    const monthLabels = @json($monthLabels ?? []);

    const monthlyGradient = monthlyCtx.getContext('2d').createLinearGradient(0, 0, 0, 380);
    monthlyGradient.addColorStop(0, 'rgba(34,197,94,.98)');
    monthlyGradient.addColorStop(.48, 'rgba(22,163,74,.70)');
    monthlyGradient.addColorStop(1, 'rgba(2,6,23,.24)');

    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Omset',
                data: monthlyData,
                backgroundColor: monthlyGradient,
                borderColor: 'rgba(134,239,172,.82)',
                borderWidth: 1,
                borderRadius: 9,
                borderSkipped: false,
                hoverBackgroundColor: '#22c55e',
                maxBarThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            layout: {
                padding: { top: 26, right: 10, bottom: 0, left: 6 }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#020617',
                    borderColor: 'rgba(255,255,255,.14)',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: '#dbeafe',
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return formatRupiah(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: commonGridColor },
                    border: { display: false },
                    ticks: {
                        color: commonTickColor,
                        callback: function(value) {
                            return formatShortCurrency(value);
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: '#94a3b8' }
                }
            }
        },
        plugins: [{
            id: 'monthlyTopLabels',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                ctx.save();
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((bar, index) => {
                        const value = Number(dataset.data[index] || 0);
                        if (value > 0) {
                            const label = formatShortCurrency(value);
                            ctx.fillStyle = '#86efac';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            ctx.font = '800 11px Inter';
                            ctx.shadowColor = 'rgba(34,197,94,.45)';
                            ctx.shadowBlur = 8;
                            ctx.fillText(label, bar.x, bar.y - 8);
                        }
                    });
                });
                ctx.restore();
            }
        }]
    });

    const platformCtx = document.getElementById('platformChart');
    const platData = @json($platformTotals ?? []);

    const platformLabels = ['ShopeeFood', 'GoFood', 'GrabFood', 'TikTok', 'QPon'];
    const platformValues = [
        Number(platData.shopeefood || 0),
        Number(platData.gofood || 0),
        Number(platData.grabfood || 0),
        Number(platData.tiktok || 0),
        Number(platData.qpon || 0)
    ];

    new Chart(platformCtx, {
        type: 'bar',
        data: {
            labels: platformLabels,
            datasets: [{
                data: platformValues,
                backgroundColor: [
                    'rgba(249,115,22,.92)',
                    'rgba(59,130,246,.92)',
                    'rgba(34,197,94,.92)',
                    'rgba(168,85,247,.92)',
                    'rgba(234,179,8,.92)'
                ],
                borderColor: [
                    '#fdba74',
                    '#93c5fd',
                    '#86efac',
                    '#d8b4fe',
                    '#fde047'
                ],
                borderWidth: 1,
                borderRadius: 9,
                borderSkipped: false,
                maxBarThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            layout: { padding: { top: 8, right: 36, bottom: 0, left: 0 } },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#020617',
                    borderColor: 'rgba(255,255,255,.14)',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: '#dbeafe',
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return formatRupiah(context.parsed.x);
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: commonGridColor },
                    border: { display: false },
                    ticks: {
                        color: commonTickColor,
                        callback: function(value) {
                            return formatShortCurrency(value);
                        }
                    }
                },
                y: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#cbd5e1',
                        font: { weight: '800' }
                    }
                }
            }
        },
        plugins: [{
            id: 'platformRightLabels',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                ctx.save();
                ctx.font = '800 12px Inter';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((bar, index) => {
                        const value = Number(dataset.data[index] || 0);
                        if (value > 0) {
                            ctx.fillStyle = '#f8fafc';
                            ctx.shadowColor = 'rgba(255,255,255,.28)';
                            ctx.shadowBlur = 5;
                            ctx.fillText(formatShortCurrency(value), bar.x + 10, bar.y);
                        }
                    });
                });
                ctx.restore();
            }
        }]
    });
</script>

@include('Temp.internal.footer_internal')

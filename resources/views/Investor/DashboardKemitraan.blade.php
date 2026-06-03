{{-- resources/views/Investor/DashboardKemitraan.blade.php --}}
@include('Temp.internal.header_internal')

@php
    function formatBillionMillion($angka) {
        if ($angka >= 1000000000) return 'Rp' . number_format($angka / 1000000000, 2, '.', '') . ' M';
        if ($angka >= 1000000) return 'Rp' . number_format($angka / 1000000, 2, '.', '') . ' JT';
        return 'Rp' . number_format($angka, 0, ',', '.');
    }
@endphp

<style>
    :root {
        --km-bg: #030712;
        --km-bg-2: #07111f;
        --km-card: rgba(8, 15, 28, .96);
        --km-line: rgba(148, 163, 184, .12);
        --km-line-soft: rgba(148, 163, 184, .07);
        --km-text: #eaf2ff;
        --km-muted: #7f92ae;
        --km-blue: #3b82f6;
        --km-green: #22c55e;
        --km-red: #ef4444;
        --km-gold: #f59e0b;
        --km-shadow: 0 14px 32px rgba(0, 0, 0, .26);
        --km-radius: 18px;
    }

    .km-page {
        width: 100%;
        min-height: 100vh;
        padding: 16px;
        color: var(--km-text);
        background: linear-gradient(180deg, var(--km-bg) 0%, var(--km-bg-2) 100%);
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .km-page * {
        box-sizing: border-box;
    }

    .km-board {
        width: 100%;
        border: 1px solid var(--km-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(8, 15, 28, .98), rgba(4, 9, 19, .98));
        box-shadow: var(--km-shadow);
        overflow: hidden;
    }

    .km-topbar {
        display: grid;
        grid-template-columns: 116px minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--km-line);
        background: rgba(255, 255, 255, .018);
    }

    .km-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 104px;
        height: 52px;
        border: 1px solid var(--km-line);
        border-radius: 14px;
        background: rgba(255, 255, 255, .035);
    }

    .km-logo img {
        max-width: 82px;
        max-height: 38px;
        object-fit: contain;
    }

    .km-title-wrap {
        min-width: 0;
    }

    .km-eyebrow {
        margin-bottom: 5px;
        color: var(--km-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .km-title {
        margin: 0;
        color: #fff;
        font-size: clamp(20px, 2vw, 30px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
        text-transform: uppercase;
    }

    .km-subtitle {
        margin-top: 6px;
        color: var(--km-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .km-filter-form {
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
        border: 1px solid var(--km-line);
        border-radius: 999px;
        background: rgba(255, 255, 255, .035);
        color: var(--km-text);
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
        border: 1px solid var(--km-line);
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
        border-bottom: 1px solid var(--km-line-soft);
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
        accent-color: var(--km-blue);
    }

    .km-content {
        padding: 16px;
    }

    .km-grid {
        display: grid;
        grid-template-columns: 310px 220px minmax(0, 1fr);
        gap: 16px;
        align-items: stretch;
    }

    .km-kpi-stack {
        display: grid;
        gap: 12px;
    }

    .km-kpi-card {
        border: 1px solid var(--km-line);
        border-radius: 16px;
        background: var(--km-card);
        overflow: hidden;
    }

    .km-kpi-card.actual { border-color: rgba(34, 197, 94, .22); }
    .km-kpi-card.target { border-color: rgba(59, 130, 246, .22); }
    .km-kpi-card.variance { border-color: rgba(239, 68, 68, .22); }

    .km-kpi-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 12px;
        border-bottom: 1px solid var(--km-line);
        color: #fff;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .km-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex: 0 0 7px;
    }

    .km-dot.green { background: var(--km-green); box-shadow: 0 0 10px rgba(34,197,94,.55); }
    .km-dot.blue { background: var(--km-blue); box-shadow: 0 0 10px rgba(59,130,246,.55); }
    .km-dot.red { background: var(--km-red); box-shadow: 0 0 10px rgba(239,68,68,.55); }

    .km-kpi-body {
        display: grid;
        grid-template-columns: .7fr 1.3fr;
        gap: 8px;
        padding: 12px;
    }

    .km-mini-box {
        border: 1px solid var(--km-line-soft);
        border-radius: 13px;
        background: rgba(255, 255, 255, .022);
        padding: 11px;
        min-width: 0;
    }

    .km-mini-label {
        color: var(--km-muted);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .km-mini-val {
        color: #fff;
        font-size: 20px;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
        white-space: nowrap;
    }

    .km-kpi-card.actual .km-mini-val { color: #86efac; }
    .km-kpi-card.target .km-mini-val { color: #bfdbfe; }
    .km-kpi-card.variance .km-mini-val { color: #fca5a5; }

    .km-achievement-card {
        border: 1px solid rgba(34, 197, 94, .22);
        border-radius: var(--km-radius);
        background: linear-gradient(180deg, rgba(34, 197, 94, .10), rgba(255, 255, 255, .018));
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100%;
        padding: 16px;
    }

    .km-achievement-label {
        color: var(--km-muted);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
        margin-bottom: 12px;
        text-align: center;
    }

    .km-achievement-value {
        color: #fff;
        font-size: clamp(36px, 4vw, 56px);
        font-weight: 950;
        line-height: .95;
        letter-spacing: -.06em;
        text-align: center;
    }

    .km-achievement-note {
        margin-top: 10px;
        color: #86efac;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
    }

    .km-table-card {
        min-width: 0;
        border: 1px solid var(--km-line);
        border-radius: var(--km-radius);
        background: var(--km-card);
        overflow: hidden;
    }

    .km-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--km-line);
        background: rgba(255, 255, 255, .016);
    }

    .km-table-title {
        color: #fff;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .km-table-chip {
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

    .km-table-wrap {
        overflow: auto;
        max-height: 380px;
    }

    .km-table {
        width: 100%;
        min-width: 560px;
        border-collapse: collapse;
        text-align: right;
    }

    .km-table th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 10px 11px;
        background: #071426;
        border-bottom: 1px solid var(--km-line);
        border-right: 1px solid var(--km-line-soft);
        color: #8fa3c0;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .km-table td {
        padding: 10px 11px;
        border-bottom: 1px solid var(--km-line-soft);
        border-right: 1px solid var(--km-line-soft);
        color: #dbe8fb;
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap;
    }

    .km-table th:first-child,
    .km-table td:first-child {
        text-align: left;
    }

    .km-table tr:nth-child(even) td {
        background: rgba(255,255,255,.022);
    }

    .km-table tr:hover td {
        background: rgba(59, 130, 246, .055);
    }

    .km-blue-text { color: #bfdbfe !important; }
    .km-green-text { color: #86efac !important; }

    .km-footer {
        padding-top: 16px;
        color: #6f86a8;
        font-size: 11px;
        text-align: right;
    }

    @media (max-width: 1200px) {
        .km-topbar {
            grid-template-columns: 1fr;
        }

        .km-logo {
            width: 100%;
        }

        .km-title-wrap {
            text-align: center;
        }

        .km-filter-form {
            justify-content: center;
        }

        .km-grid {
            grid-template-columns: 1fr;
        }

        .km-achievement-card {
            min-height: 160px;
        }
    }

    @media (max-width: 720px) {
        .km-page {
            padding: 10px;
        }

        .km-topbar,
        .km-content {
            padding: 12px;
        }

        .km-kpi-body {
            grid-template-columns: 1fr;
        }

        .multi-dropdown {
            min-width: 100%;
        }

        .km-filter-form {
            width: 100%;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="km-page">
                <div class="km-board">

                    <div class="km-topbar">
                        <div class="km-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="km-title-wrap">
                            <!-- <div class="km-eyebrow">Owner - M. Faiz</div> -->
                            <h1 class="km-title">Fundraising Control</h1>
                            <div class="km-subtitle">Market-board style untuk actual, target, variance, achievement, dan monthly fundraising.</div>
                        </div>

                        <form method="GET" action="{{ route('investor.sales.dashboardBOD.kemitraan') }}" id="filterForm" class="km-filter-form">
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

                    <div class="km-content">
                        <div class="km-grid">

                            <div class="km-kpi-stack">
                                <section class="km-kpi-card actual">
                                    <div class="km-kpi-head">
                                        <span>Actual</span>
                                        <span class="km-dot green"></span>
                                    </div>
                                    <div class="km-kpi-body">
                                        <div class="km-mini-box">
                                            <div class="km-mini-label">Qty</div>
                                            <div class="km-mini-val">{{ $metrics['qty_actual'] }}</div>
                                        </div>
                                        <div class="km-mini-box">
                                            <div class="km-mini-label">Dana</div>
                                            <div class="km-mini-val">{{ formatBillionMillion($metrics['dana_actual']) }}</div>
                                        </div>
                                    </div>
                                </section>

                                <section class="km-kpi-card target">
                                    <div class="km-kpi-head">
                                        <span>Target</span>
                                        <span class="km-dot blue"></span>
                                    </div>
                                    <div class="km-kpi-body">
                                        <div class="km-mini-box">
                                            <div class="km-mini-label">Qty</div>
                                            <div class="km-mini-val">{{ $metrics['qty_target'] }}</div>
                                        </div>
                                        <div class="km-mini-box">
                                            <div class="km-mini-label">Dana</div>
                                            <div class="km-mini-val">{{ formatBillionMillion($metrics['dana_target']) }}</div>
                                        </div>
                                    </div>
                                </section>

                                <section class="km-kpi-card variance">
                                    <div class="km-kpi-head">
                                        <span>Variance</span>
                                        <span class="km-dot red"></span>
                                    </div>
                                    <div class="km-kpi-body">
                                        <div class="km-mini-box">
                                            <div class="km-mini-label">Qty</div>
                                            <div class="km-mini-val">{{ $metrics['qty_variance'] }}</div>
                                        </div>
                                        <div class="km-mini-box">
                                            <div class="km-mini-label">Dana</div>
                                            <div class="km-mini-val">{{ formatBillionMillion($metrics['dana_variance']) }}</div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <section class="km-achievement-card">
                                <div class="km-achievement-label">Achievement</div>
                                <div class="km-achievement-value">{{ number_format($metrics['achievement'], 2, '.', '') }}%</div>
                                <div class="km-achievement-note">Fundraising Index</div>
                            </section>

                            <section class="km-table-card">
                                <div class="km-table-head">
                                    <div class="km-table-title">Monthly Detail</div>
                                    <div class="km-table-chip">Scrollable</div>
                                </div>

                                <div class="km-table-wrap">
                                    <table class="km-table">
                                        <thead>
                                            <tr>
                                                <th>Bulan</th>
                                                <th>Qty</th>
                                                <th>Dana</th>
                                                <th>%</th>
                                                <th>No</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($tableData as $row)
                                                <tr>
                                                    <td>{{ $row['bulan'] }}</td>
                                                    <td>{{ $row['qty'] }}</td>
                                                    <td class="km-green-text">Rp {{ number_format($row['dana'], 0, ',', '.') }}</td>
                                                    <td class="km-blue-text">{{ $row['pct'] ?: 'null' }}</td>
                                                    <td>{{ $row['no'] }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" style="padding: 20px; text-align:center;">Tidak ada data ditemukan.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                        </div>

                        <div class="km-footer">
                            Data Last Updated: {{ $lastSyncAt }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

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
</script>

@include('Temp.internal.footer_internal')

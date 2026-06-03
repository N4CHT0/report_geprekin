{{-- resources/views/Investor/DashboardControlBudget.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --cb-bg: #030712;
        --cb-bg-2: #07111f;
        --cb-card: rgba(8, 15, 28, .96);
        --cb-line: rgba(148, 163, 184, .12);
        --cb-line-soft: rgba(148, 163, 184, .07);
        --cb-text: #eaf2ff;
        --cb-muted: #7f92ae;
        --cb-blue: #3b82f6;
        --cb-green: #22c55e;
        --cb-red: #ef4444;
        --cb-black: #020617;
        --cb-shadow: 0 14px 32px rgba(0, 0, 0, .26);
        --cb-radius: 18px;
    }

    .cb-page {
        width: 100%;
        min-height: 100vh;
        padding: 16px;
        color: var(--cb-text);
        background: linear-gradient(180deg, var(--cb-bg) 0%, var(--cb-bg-2) 100%);
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .cb-page * {
        box-sizing: border-box;
    }

    .cb-board {
        width: 100%;
        border: 1px solid var(--cb-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(8, 15, 28, .98), rgba(4, 9, 19, .98));
        box-shadow: var(--cb-shadow);
        overflow: hidden;
    }

    .cb-topbar {
        display: grid;
        grid-template-columns: 116px minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--cb-line);
        background: rgba(255, 255, 255, .018);
    }

    .cb-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 104px;
        height: 52px;
        border: 1px solid var(--cb-line);
        border-radius: 14px;
        background: rgba(255, 255, 255, .035);
    }

    .cb-logo img {
        max-width: 82px;
        max-height: 38px;
        object-fit: contain;
    }

    .cb-title-wrap {
        min-width: 0;
    }

    .cb-eyebrow {
        margin-bottom: 5px;
        color: var(--cb-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .cb-title {
        margin: 0;
        color: #fff;
        font-size: clamp(20px, 2vw, 30px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
        text-transform: uppercase;
    }

    .cb-subtitle {
        margin-top: 6px;
        color: var(--cb-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .cb-filter-form {
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
        border: 1px solid var(--cb-line);
        border-radius: 999px;
        background: rgba(255, 255, 255, .035);
        color: var(--cb-text);
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
        min-width: 190px;
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid var(--cb-line);
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
        border-bottom: 1px solid var(--cb-line-soft);
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
        accent-color: var(--cb-blue);
    }

    .cb-content {
        padding: 16px;
    }

    .cb-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .cb-metric-card {
        min-width: 0;
        border: 1px solid var(--cb-line);
        border-radius: var(--cb-radius);
        background: var(--cb-card);
        overflow: hidden;
    }

    .cb-metric-card.actual { border-color: rgba(59, 130, 246, .22); }
    .cb-metric-card.plan { border-color: rgba(34, 197, 94, .22); }
    .cb-metric-card.variance { border-color: rgba(239, 68, 68, .22); }
    .cb-metric-card.percent { border-color: rgba(148, 163, 184, .20); }

    .cb-metric-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 12px 13px;
        border-bottom: 1px solid var(--cb-line);
        color: #fff;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .1em;
        text-transform: uppercase;
        background: rgba(255, 255, 255, .016);
    }

    .cb-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex: 0 0 7px;
    }

    .cb-dot.blue { background: var(--cb-blue); box-shadow: 0 0 10px rgba(59,130,246,.55); }
    .cb-dot.green { background: var(--cb-green); box-shadow: 0 0 10px rgba(34,197,94,.55); }
    .cb-dot.red { background: var(--cb-red); box-shadow: 0 0 10px rgba(239,68,68,.55); }
    .cb-dot.gray { background: #94a3b8; box-shadow: 0 0 10px rgba(148,163,184,.35); }

    .cb-metric-value {
        padding: 18px 14px 20px;
        color: #fff;
        font-size: clamp(28px, 3vw, 40px);
        font-weight: 950;
        line-height: .95;
        letter-spacing: -.06em;
        text-align: center;
        white-space: nowrap;
    }

    .cb-metric-card.actual .cb-metric-value { color: #bfdbfe; }
    .cb-metric-card.plan .cb-metric-value { color: #86efac; }
    .cb-metric-card.variance .cb-metric-value { color: #fca5a5; }

    .cb-table-card {
        border: 1px solid var(--cb-line);
        border-radius: var(--cb-radius);
        background: var(--cb-card);
        overflow: hidden;
    }

    .cb-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--cb-line);
        background: rgba(255, 255, 255, .016);
    }

    .cb-table-title {
        color: #fff;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .cb-table-chip {
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

    .cb-table-wrap {
        overflow: auto;
        max-height: 440px;
    }

    .cb-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
        text-align: right;
    }

    .cb-table th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 10px 11px;
        background: #071426;
        border-bottom: 1px solid var(--cb-line);
        border-right: 1px solid var(--cb-line-soft);
        color: #8fa3c0;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .cb-table td {
        padding: 10px 11px;
        border-bottom: 1px solid var(--cb-line-soft);
        border-right: 1px solid var(--cb-line-soft);
        color: #dbe8fb;
        font-size: 12px;
        font-weight: 750;
        white-space: nowrap;
    }

    .cb-table th:first-child,
    .cb-table td:first-child {
        text-align: left;
        position: sticky;
        left: 0;
        background: #071426;
        z-index: 2;
    }

    .cb-table thead th:first-child {
        z-index: 4;
    }

    .cb-table tr:nth-child(even) td {
        background: rgba(255,255,255,.022);
    }

    .cb-table tr:nth-child(even) td:first-child {
        background: #09182a;
    }

    .cb-table tr:hover td {
        background: rgba(59, 130, 246, .055);
    }

    .cb-table tr:hover td:first-child {
        background: #0b1b30;
    }

    .cb-dep {
        color: #fff !important;
        font-weight: 900 !important;
    }

    .cb-green-text { color: #86efac !important; }
    .cb-red-text { color: #fca5a5 !important; }

    .cb-footer {
        padding-top: 16px;
        color: #6f86a8;
        font-size: 11px;
        text-align: right;
    }

    @media (max-width: 1200px) {
        .cb-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .cb-topbar {
            grid-template-columns: 1fr;
        }

        .cb-logo {
            width: 100%;
        }

        .cb-title-wrap {
            text-align: center;
        }

        .cb-filter-form {
            justify-content: center;
        }
    }

    @media (max-width: 720px) {
        .cb-page {
            padding: 10px;
        }

        .cb-topbar,
        .cb-content {
            padding: 12px;
        }

        .cb-card-grid {
            grid-template-columns: 1fr;
        }

        .multi-dropdown {
            min-width: 100%;
        }

        .cb-filter-form {
            width: 100%;
        }
    }

    <?php
        function formatBillion($value) {
            return number_format($value / 1000000000, 3, '.', '') . ' M';
        }
    ?>
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="cb-page">
                <div class="cb-board">

                    <div class="cb-topbar">
                        <div class="cb-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="cb-title-wrap">
                            <!-- <div class="cb-eyebrow">Owner - Febriansyah</div> -->
                            <h1 class="cb-title">Control Budgeting All Departement</h1>
                            <div class="cb-subtitle">Market-board style untuk actual, plan, variance, dan pencapaian budget per departemen.</div>
                        </div>

                        <form method="GET" action="{{ route('investor.sales.dashboardBOD.controlBudget') }}" id="filterForm" class="cb-filter-form">
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

                            <div class="multi-dropdown">
                                <button type="button" class="multi-drop-btn" onclick="toggleDropdown('dropDep')">
                                    <span>Dep</span>
                                    <span class="badge-count">{{ count($filters['dep'] ?? []) }}</span>
                                </button>
                                <div id="dropDep" class="multi-drop-content">
                                    @foreach(($availableDeps ?? []) as $depLabel)
                                        <label>
                                            <input type="checkbox" name="dep[]" value="{{ $depLabel }}" onchange="document.getElementById('filterForm').submit()" {{ in_array($depLabel, $filters['dep'] ?? []) ? 'checked' : '' }}>
                                            {{ $depLabel }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="cb-content">
                        <div class="cb-card-grid">
                            <section class="cb-metric-card actual">
                                <div class="cb-metric-head">
                                    <span>Actual</span>
                                    <span class="cb-dot blue"></span>
                                </div>
                                <div class="cb-metric-value">{{ formatBillion($metrics['actual']) }}</div>
                            </section>

                            <section class="cb-metric-card plan">
                                <div class="cb-metric-head">
                                    <span>Plan</span>
                                    <span class="cb-dot green"></span>
                                </div>
                                <div class="cb-metric-value">{{ formatBillion($metrics['plan']) }}</div>
                            </section>

                            <section class="cb-metric-card variance">
                                <div class="cb-metric-head">
                                    <span>Variance</span>
                                    <span class="cb-dot red"></span>
                                </div>
                                <div class="cb-metric-value">{{ formatBillion($metrics['variance']) }}</div>
                            </section>

                            <section class="cb-metric-card percent">
                                <div class="cb-metric-head">
                                    <span>%</span>
                                    <span class="cb-dot gray"></span>
                                </div>
                                <div class="cb-metric-value">{{ number_format($metrics['pencapaian'], 2, '.', '') }}%</div>
                            </section>
                        </div>

                        <section class="cb-table-card">
                            <div class="cb-table-head">
                                <div class="cb-table-title">Rincian Departemen</div>
                                <div class="cb-table-chip">Scrollable</div>
                            </div>

                            <div class="cb-table-wrap">
                                <table class="cb-table">
                                    <thead>
                                        <tr>
                                            <th>Dep</th>
                                            <th>Plan</th>
                                            <th>Actual</th>
                                            <th>Variance</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tableData as $row)
                                            <tr>
                                                <td class="cb-dep">{{ $row['dep'] }}</td>
                                                <td>Rp {{ number_format($row['plan'], 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($row['actual'], 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($row['variance'], 0, ',', '.') }}</td>
                                                <td class="{{ $row['pct'] >= 100 ? 'cb-green-text' : 'cb-red-text' }}">
                                                    {{ number_format($row['pct'], 2, '.', '') }}%
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" style="padding: 30px; text-align:center; color: #94a3b8; font-style: italic;">Belum ada data Departemen pada periode terpilih.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <div class="cb-footer">
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

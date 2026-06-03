{{-- resources/views/Investor/DashboardFulfillmentTraining.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --ihsg-bg: #030712;
        --ihsg-bg-2: #07111f;
        --ihsg-card: rgba(8, 15, 28, .96);
        --ihsg-card-2: rgba(11, 20, 36, .92);
        --ihsg-line: rgba(148, 163, 184, .12);
        --ihsg-line-soft: rgba(148, 163, 184, .07);
        --ihsg-text: #eaf2ff;
        --ihsg-muted: #7f92ae;
        --ihsg-green: #22c55e;
        --ihsg-red: #ef4444;
        --ihsg-blue: #3b82f6;
        --ihsg-gold: #f59e0b;
        --ihsg-shadow: 0 14px 32px rgba(0, 0, 0, .26);
        --ihsg-radius: 18px;
    }

    .ihsg-page {
        width: 100%;
        min-height: 100vh;
        padding: 16px;
        color: var(--ihsg-text);
        background: linear-gradient(180deg, var(--ihsg-bg) 0%, var(--ihsg-bg-2) 100%);
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .ihsg-page * {
        box-sizing: border-box;
    }

    .ihsg-board {
        width: 100%;
        border: 1px solid var(--ihsg-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(8, 15, 28, .98), rgba(4, 9, 19, .98));
        box-shadow: var(--ihsg-shadow);
        overflow: hidden;
    }

    .ihsg-topbar {
        display: grid;
        grid-template-columns: 116px minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--ihsg-line);
        background: rgba(255, 255, 255, .018);
    }

    .ihsg-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 104px;
        height: 52px;
        border: 1px solid var(--ihsg-line);
        border-radius: 14px;
        background: rgba(255, 255, 255, .035);
    }

    .ihsg-logo img {
        max-width: 82px;
        max-height: 38px;
        object-fit: contain;
    }

    .ihsg-title-wrap {
        min-width: 0;
    }

    .ihsg-eyebrow {
        margin-bottom: 5px;
        color: var(--ihsg-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .ihsg-title {
        margin: 0;
        color: #fff;
        font-size: clamp(20px, 2vw, 30px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
        text-transform: uppercase;
    }

    .ihsg-subtitle {
        margin-top: 6px;
        color: var(--ihsg-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .ihsg-filter-form {
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
        border: 1px solid var(--ihsg-line);
        border-radius: 999px;
        background: rgba(255, 255, 255, .035);
        color: var(--ihsg-text);
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
        border: 1px solid var(--ihsg-line);
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
        border-bottom: 1px solid var(--ihsg-line-soft);
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
        accent-color: var(--ihsg-blue);
    }

    .ihsg-content {
        padding: 16px;
    }

    .ihsg-main-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .ihsg-card {
        min-width: 0;
        border: 1px solid var(--ihsg-line);
        border-radius: var(--ihsg-radius);
        background: var(--ihsg-card);
        overflow: hidden;
    }

    .ihsg-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--ihsg-line);
        background: rgba(255, 255, 255, .016);
    }

    .ihsg-card-title {
        color: #fff;
        font-size: 14px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ihsg-chip {
        display: inline-flex;
        align-items: center;
        height: 25px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        color: #bbf7d0;
        border: 1px solid rgba(34, 197, 94, .20);
        background: rgba(34, 197, 94, .08);
    }

    .ihsg-chip.gold {
        color: #fde68a;
        border-color: rgba(245, 158, 11, .22);
        background: rgba(245, 158, 11, .09);
    }

    .ihsg-inner-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 150px minmax(0, 1fr);
        gap: 12px;
        align-items: stretch;
        padding: 14px;
    }

    .ihsg-inner-grid.existing {
        grid-template-columns: minmax(0, 1fr) 170px minmax(0, 1fr);
    }

    .ihsg-stack {
        display: grid;
        gap: 8px;
    }

    .ihsg-mini-kpi {
        border: 1px solid var(--ihsg-line-soft);
        border-radius: 12px;
        background: rgba(255, 255, 255, .025);
        padding: 10px;
    }

    .ihsg-mini-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        color: var(--ihsg-muted);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .ihsg-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex: 0 0 7px;
    }

    .ihsg-dot.blue { background: var(--ihsg-blue); box-shadow: 0 0 10px rgba(59, 130, 246, .55); }
    .ihsg-dot.green { background: var(--ihsg-green); box-shadow: 0 0 10px rgba(34, 197, 94, .55); }
    .ihsg-dot.red { background: var(--ihsg-red); box-shadow: 0 0 10px rgba(239, 68, 68, .55); }

    .ihsg-mini-value {
        margin-top: 8px;
        color: #fff;
        font-size: 22px;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
    }

    .ihsg-mini-value.sm {
        font-size: 17px;
    }

    .ihsg-total-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 176px;
        border: 1px solid rgba(59, 130, 246, .16);
        border-radius: 15px;
        background: linear-gradient(180deg, rgba(59, 130, 246, .10), rgba(255, 255, 255, .02));
    }

    .ihsg-total-value {
        color: #fff;
        font-size: 38px;
        font-weight: 950;
        line-height: .95;
        letter-spacing: -.06em;
    }

    .ihsg-total-value span {
        font-size: 20px;
        letter-spacing: -.02em;
    }

    .ihsg-total-label {
        margin-top: 10px;
        color: var(--ihsg-muted);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
        text-align: center;
    }

    .ihsg-table-card {
        border: 1px solid var(--ihsg-line);
        border-radius: var(--ihsg-radius);
        background: var(--ihsg-card);
        overflow: hidden;
    }

    .ihsg-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--ihsg-line);
        background: rgba(255, 255, 255, .016);
    }

    .ihsg-table-title {
        color: #fff;
        font-size: 14px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ihsg-table-note {
        color: var(--ihsg-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .ihsg-table-wrap {
        overflow: auto;
        max-height: 420px;
    }

    .ihsg-table {
        width: 100%;
        min-width: 920px;
        border-collapse: collapse;
        text-align: right;
    }

    .ihsg-table th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 10px 11px;
        border-bottom: 1px solid var(--ihsg-line);
        border-right: 1px solid var(--ihsg-line-soft);
        background: #071426;
        color: #8fa3c0;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ihsg-table th:first-child,
    .ihsg-table td:first-child {
        text-align: left;
        position: sticky;
        left: 0;
        z-index: 2;
        background: #071426;
    }

    .ihsg-table thead tr:first-child th:first-child {
        z-index: 4;
    }

    .ihsg-table td {
        padding: 10px 11px;
        border-bottom: 1px solid var(--ihsg-line-soft);
        border-right: 1px solid var(--ihsg-line-soft);
        color: #dbe8fb;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        background: rgba(255, 255, 255, .008);
    }

    .ihsg-table tr:nth-child(even) td {
        background: rgba(255, 255, 255, .022);
    }

    .ihsg-table tr:nth-child(even) td:first-child {
        background: #09182a;
    }

    .ihsg-table tr:hover td {
        background: rgba(59, 130, 246, .055);
    }

    .ihsg-table tr:hover td:first-child {
        background: #0b1b30;
    }

    .ihsg-green-text { color: #86efac !important; }
    .ihsg-red-text { color: #fca5a5 !important; }

    .ihsg-footer {
        padding: 12px 16px 16px;
        color: #6f86a8;
        font-size: 11px;
        text-align: right;
    }

    @media (max-width: 1200px) {
        .ihsg-topbar {
            grid-template-columns: 1fr;
        }

        .ihsg-logo {
            width: 100%;
        }

        .ihsg-title-wrap {
            text-align: center;
        }

        .ihsg-filter-form {
            justify-content: center;
        }

        .ihsg-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .ihsg-page {
            padding: 10px;
        }

        .ihsg-topbar,
        .ihsg-content {
            padding: 12px;
        }

        .ihsg-inner-grid,
        .ihsg-inner-grid.existing {
            grid-template-columns: 1fr;
        }

        .ihsg-total-box {
            min-height: 120px;
        }

        .multi-dropdown {
            min-width: 100%;
        }

        .ihsg-filter-form {
            width: 100%;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="ihsg-page">
                <div class="ihsg-board">

                    <div class="ihsg-topbar">
                        <div class="ihsg-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="ihsg-title-wrap">
                            <!-- <div class="ihsg-eyebrow">Owner - Soehartono</div> -->
                            <h1 class="ihsg-title">Fulfillment Training</h1>
                            <div class="ihsg-subtitle">Market-board style untuk monitoring plan, actual, variance, dan fulfillment training.</div>
                        </div>

                        <form method="GET" action="{{ route('investor.sales.dashboardBOD.fulfillmentTraining') }}" id="filterForm" class="ihsg-filter-form">
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

                    <div class="ihsg-content">
                        <div class="ihsg-main-grid">

                            {{-- NEW OUTLET --}}
                            <section class="ihsg-card">
                                <div class="ihsg-card-head">
                                    <div class="ihsg-card-title">New Outlet</div>
                                    <div class="ihsg-chip">Training Index</div>
                                </div>

                                <div class="ihsg-inner-grid">
                                    <div class="ihsg-stack">
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Leader <span class="ihsg-dot blue"></span></div>
                                            <div class="ihsg-mini-value sm">{{ number_format($kpiNew['pct_leader'], 2) }}%</div>
                                        </div>
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Plan <span class="ihsg-dot blue"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiNew['plan_leader']) }}</div>
                                        </div>
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Actual <span class="ihsg-dot green"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiNew['act_leader']) }}</div>
                                        </div>
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Variance <span class="ihsg-dot red"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiNew['var_leader']) }}</div>
                                        </div>
                                    </div>

                                    <div class="ihsg-total-box">
                                        <div class="ihsg-total-value">{{ number_format($kpiNew['pct_total'], 2) }}<span>%</span></div>
                                        <div class="ihsg-total-label">Total Fulfillment</div>
                                    </div>

                                    <div class="ihsg-stack">
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Crew <span class="ihsg-dot blue"></span></div>
                                            <div class="ihsg-mini-value sm">{{ number_format($kpiNew['pct_crew'], 2) }}%</div>
                                        </div>
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Plan <span class="ihsg-dot blue"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiNew['plan_crew']) }}</div>
                                        </div>
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Actual <span class="ihsg-dot green"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiNew['act_crew']) }}</div>
                                        </div>
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Variance <span class="ihsg-dot red"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiNew['var_crew']) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            {{-- EXISTING OUTLET --}}
                            <section class="ihsg-card">
                                <div class="ihsg-card-head">
                                    <div class="ihsg-card-title">Existing Outlet</div>
                                    <div class="ihsg-chip gold">Existing Index</div>
                                </div>

                                <div class="ihsg-inner-grid existing">
                                    <div class="ihsg-stack">
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Permintaan <span class="ihsg-dot blue"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiExisting['kebutuhan']) }}</div>
                                        </div>

                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Kekurangan <span class="ihsg-dot red"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiExisting['kekurangan']) }}</div>
                                        </div>
                                    </div>

                                    <div class="ihsg-total-box">
                                        <div class="ihsg-total-value">{{ number_format($kpiExisting['pct_total'], 2) }}<span>%</span></div>
                                        <div class="ihsg-total-label">Fulfillment Existing</div>
                                    </div>

                                    <div class="ihsg-stack">
                                        <div class="ihsg-mini-kpi">
                                            <div class="ihsg-mini-label">Pemenuhan <span class="ihsg-dot green"></span></div>
                                            <div class="ihsg-mini-value">{{ number_format($kpiExisting['pemenuhan']) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                        </div>

                        <section class="ihsg-table-card">
                            <div class="ihsg-table-head">
                                <div class="ihsg-table-title">Summary Board</div>
                                <div class="ihsg-table-note">Scrollable table</div>
                            </div>

                            <div class="ihsg-table-wrap">
                                <table class="ihsg-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">Bulan</th>
                                            <th colspan="3">Leader (New)</th>
                                            <th colspan="3">Crew (New)</th>
                                            <th colspan="3">Existing Outlet</th>
                                        </tr>
                                        <tr>
                                            <th>Plan</th>
                                            <th>Actual</th>
                                            <th>Var</th>
                                            <th>Plan</th>
                                            <th>Actual</th>
                                            <th>Var</th>
                                            <th>Permintaan</th>
                                            <th>Pemenuhan</th>
                                            <th>Kekurangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tableData as $row)
                                            <tr>
                                                <td>{{ $row['bulan'] }}</td>
                                                <td>{{ number_format($row['plan_leader']) }}</td>
                                                <td class="ihsg-green-text">{{ number_format($row['act_leader']) }}</td>
                                                <td class="ihsg-red-text">{{ number_format($row['var_leader']) }}</td>

                                                <td>{{ number_format($row['plan_crew']) }}</td>
                                                <td class="ihsg-green-text">{{ number_format($row['act_crew']) }}</td>
                                                <td class="ihsg-red-text">{{ number_format($row['var_crew']) }}</td>

                                                <td>{{ number_format($row['kebutuhan']) }}</td>
                                                <td class="ihsg-green-text">{{ number_format($row['pemenuhan']) }}</td>
                                                <td class="ihsg-red-text">{{ number_format($row['kekurangan']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" style="padding: 20px; text-align:center;">Tidak ada data.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <div class="ihsg-footer">
                        Data Last Updated: {{ $lastSyncAt }}
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

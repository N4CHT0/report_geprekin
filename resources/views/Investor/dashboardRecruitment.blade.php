{{-- resources/views/Investor/DashboardRecruitment.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --rec-bg: #040814;
        --rec-bg-soft: #07101f;
        --rec-panel: rgba(10, 18, 34, .92);
        --rec-panel-2: rgba(7, 13, 26, .96);
        --rec-border: rgba(148, 163, 184, .12);
        --rec-border-soft: rgba(148, 163, 184, .07);
        --rec-text: #eaf2ff;
        --rec-muted: #8fa3c0;
        --rec-blue: #3b82f6;
        --rec-green: #10b981;
        --rec-red: #ef4444;
        --rec-gold: #f59e0b;
        --rec-radius: 18px;
        --rec-shadow: 0 14px 34px rgba(0, 0, 0, .28);
    }

    .rec-page {
        min-height: 100vh;
        padding: 18px;
        color: var(--rec-text);
        background: linear-gradient(180deg, var(--rec-bg) 0%, var(--rec-bg-soft) 100%);
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .rec-page * {
        box-sizing: border-box;
    }

    .rec-shell {
        width: 100%;
        border: 1px solid var(--rec-border);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(9, 17, 32, .94), rgba(5, 10, 20, .98));
        box-shadow: var(--rec-shadow);
        overflow: hidden;
    }

    .rec-topbar {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        padding: 16px 18px;
        border-bottom: 1px solid var(--rec-border);
        background: rgba(255, 255, 255, .015);
    }

    .rec-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 112px;
        height: 58px;
        border-radius: 16px;
        border: 1px solid var(--rec-border);
        background: rgba(255, 255, 255, .035);
    }

    .rec-logo img {
        max-width: 86px;
        max-height: 42px;
        object-fit: contain;
    }

    .rec-title-wrap {
        min-width: 0;
    }

    .rec-eyebrow {
        margin-bottom: 6px;
        color: var(--rec-muted);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .rec-title {
        margin: 0;
        color: #fff;
        font-size: clamp(20px, 2vw, 28px);
        font-weight: 900;
        line-height: 1.05;
        letter-spacing: -.03em;
        text-transform: uppercase;
    }

    .rec-subtitle {
        margin-top: 6px;
        color: var(--rec-muted);
        font-size: 12px;
        line-height: 1.4;
    }

    .rec-filter-form {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .multi-dropdown {
        position: relative;
        min-width: 132px;
    }

    .multi-drop-btn {
        width: 100%;
        height: 38px;
        border: 1px solid var(--rec-border);
        border-radius: 12px;
        background: rgba(255, 255, 255, .035);
        color: var(--rec-text);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 700;
        transition: .18s ease;
    }

    .multi-drop-btn:hover {
        border-color: rgba(59, 130, 246, .35);
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
        z-index: 20;
        min-width: 180px;
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid var(--rec-border);
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
        border-bottom: 1px solid var(--rec-border-soft);
        color: #cbd8eb;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
    }

    .multi-drop-content label:hover {
        background: rgba(59, 130, 246, .08);
        color: #fff;
    }

    .multi-drop-content input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: var(--rec-blue);
    }

    .rec-content {
        padding: 16px;
    }

    .rec-main-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .rec-card {
        min-width: 0;
        border: 1px solid var(--rec-border);
        border-radius: var(--rec-radius);
        background: var(--rec-panel);
        overflow: hidden;
    }

    .rec-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--rec-border);
        background: rgba(255, 255, 255, .018);
    }

    .rec-card-title {
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .rec-card-chip {
        display: inline-flex;
        align-items: center;
        height: 26px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        color: #bfdbfe;
        background: rgba(59, 130, 246, .12);
        border: 1px solid rgba(59, 130, 246, .20);
    }

    .rec-card-chip.exist {
        color: #fde68a;
        background: rgba(245, 158, 11, .10);
        border-color: rgba(245, 158, 11, .22);
    }

    .rec-summary {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 150px;
        gap: 12px;
        padding: 14px;
    }

    .rec-kpi-list {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .rec-kpi-box {
        min-height: 92px;
        border: 1px solid var(--rec-border-soft);
        border-radius: 14px;
        background: rgba(255, 255, 255, .026);
        padding: 12px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .rec-kpi-label {
        color: var(--rec-muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .rec-kpi-value {
        color: #fff;
        font-size: 24px;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -.04em;
    }

    .rec-kpi-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .rec-kpi-box.blue .rec-kpi-dot { background: var(--rec-blue); box-shadow: 0 0 12px rgba(59,130,246,.55); }
    .rec-kpi-box.green .rec-kpi-dot { background: var(--rec-green); box-shadow: 0 0 12px rgba(16,185,129,.55); }
    .rec-kpi-box.red .rec-kpi-dot { background: var(--rec-red); box-shadow: 0 0 12px rgba(239,68,68,.55); }

    .rec-ratio-box {
        border: 1px solid rgba(59, 130, 246, .16);
        border-radius: 14px;
        background: linear-gradient(180deg, rgba(59, 130, 246, .10), rgba(255, 255, 255, .025));
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 92px;
        padding: 12px;
    }

    .rec-ratio-val {
        color: #fff;
        font-size: 34px;
        font-weight: 900;
        line-height: .95;
        letter-spacing: -.05em;
    }

    .rec-ratio-label {
        margin-top: 8px;
        color: var(--rec-muted);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .rec-table-wrap {
        overflow: auto;
        max-height: 330px;
        border-top: 1px solid var(--rec-border);
    }

    .rec-table {
        width: 100%;
        min-width: 460px;
        border-collapse: collapse;
    }

    .rec-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 11px 12px;
        background: #071426;
        border-bottom: 1px solid var(--rec-border);
        color: #9fb3d2;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-align: right;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .rec-table thead th:first-child,
    .rec-table tbody td:first-child {
        text-align: left;
    }

    .rec-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--rec-border-soft);
        color: #dbe8fb;
        font-size: 12px;
        font-weight: 700;
        text-align: right;
        white-space: nowrap;
    }

    .rec-table tbody tr:hover td {
        background: rgba(59, 130, 246, .055);
    }

    .rec-table tbody tr:last-child td {
        border-bottom: none;
    }

    .rec-footer {
        padding: 12px 16px 16px;
        color: #6f86a8;
        font-size: 11px;
        text-align: right;
    }

    @media (max-width: 1200px) {
        .rec-topbar {
            grid-template-columns: 1fr;
        }

        .rec-logo {
            width: 100%;
        }

        .rec-title-wrap {
            text-align: center;
        }

        .rec-filter-form {
            justify-content: center;
        }

        .rec-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .rec-page {
            padding: 10px;
        }

        .rec-topbar,
        .rec-content {
            padding: 12px;
        }

        .rec-summary {
            grid-template-columns: 1fr;
        }

        .rec-kpi-list {
            grid-template-columns: 1fr;
        }

        .multi-dropdown {
            min-width: 100%;
        }

        .rec-filter-form {
            width: 100%;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="rec-page">
                <div class="rec-shell">

                    <div class="rec-topbar">
                        <div class="rec-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="rec-title-wrap">
                            <!-- <div class="rec-eyebrow">Owner - Soehartono</div> -->
                            <h1 class="rec-title">Human Capital Recruitment</h1>
                            <div class="rec-subtitle">Dashboard ringan untuk monitoring kebutuhan, pemenuhan, variance, dan pencapaian recruitment.</div>
                        </div>

                        <form method="GET" action="{{ route('investor.sales.dashboardBOD.recruitment') }}" id="filterForm" class="rec-filter-form">
                            <div class="multi-dropdown">
                                <button type="button" class="multi-drop-btn" onclick="toggleDropdown('dropTahun')">
                                    <span>Tahun</span>
                                    <span class="badge-count">{{ count($filters['tahun'] ?? []) }}</span>
                                </button>
                                <div id="dropTahun" class="multi-drop-content">
                                    @foreach(($availableYears ?? []) as $year)
                                        <label>
                                            <input type="checkbox" name="tahun[]" value="{{ $year }}"
                                                onchange="document.getElementById('filterForm').submit()"
                                                {{ in_array((string)$year, $filters['tahun'] ?? []) ? 'checked' : '' }}>
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
                                            <input type="checkbox" name="bulan[]" value="{{ $index + 1 }}"
                                                onchange="document.getElementById('filterForm').submit()"
                                                {{ in_array((string)($index + 1), $filters['bulan'] ?? []) ? 'checked' : '' }}>
                                            {{ $monthLabel }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="rec-content">
                        <div class="rec-main-grid">

                            {{-- NEW OUTLET --}}
                            <section class="rec-card">
                                <div class="rec-card-head">
                                    <div class="rec-card-title">New Outlet</div>
                                    <div class="rec-card-chip">New Store</div>
                                </div>

                                <div class="rec-summary">
                                    <div class="rec-kpi-list">
                                        <div class="rec-kpi-box blue">
                                            <div class="rec-kpi-dot"></div>
                                            <div>
                                                <div class="rec-kpi-label">Kebutuhan</div>
                                                <div class="rec-kpi-value">{{ number_format($kpiNew['kebutuhan'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>

                                        <div class="rec-kpi-box green">
                                            <div class="rec-kpi-dot"></div>
                                            <div>
                                                <div class="rec-kpi-label">Pemenuhan</div>
                                                <div class="rec-kpi-value">{{ number_format($kpiNew['pemenuhan'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>

                                        <div class="rec-kpi-box red">
                                            <div class="rec-kpi-dot"></div>
                                            <div>
                                                <div class="rec-kpi-label">Variance</div>
                                                <div class="rec-kpi-value">{{ number_format($kpiNew['variance'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rec-ratio-box">
                                        <div class="rec-ratio-val">{{ number_format($kpiNew['pencapaian'] ?? 0, 2, ',', '.') }}</div>
                                        <div class="rec-ratio-label">Pencapaian %</div>
                                    </div>
                                </div>

                                <div class="rec-table-wrap">
                                    <table class="rec-table">
                                        <thead>
                                            <tr>
                                                <th>Bulan</th>
                                                <th>Keb.</th>
                                                <th>Pem.</th>
                                                <th>Var.</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tableNew as $row)
                                                <tr>
                                                    <td>{{ $row['bulan'] }}</td>
                                                    <td>{{ number_format($row['kebutuhan'], 0, ',', '.') }}</td>
                                                    <td>{{ number_format($row['pemenuhan'], 0, ',', '.') }}</td>
                                                    <td>{{ number_format($row['variance'], 0, ',', '.') }}</td>
                                                    <td>{{ number_format($row['pencapaian'], 2, ',', '.') }}%</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            {{-- EXISTING OUTLET --}}
                            <section class="rec-card">
                                <div class="rec-card-head">
                                    <div class="rec-card-title">Existing Outlet</div>
                                    <div class="rec-card-chip exist">Existing Store</div>
                                </div>

                                <div class="rec-summary">
                                    <div class="rec-kpi-list">
                                        <div class="rec-kpi-box blue">
                                            <div class="rec-kpi-dot"></div>
                                            <div>
                                                <div class="rec-kpi-label">Kebutuhan</div>
                                                <div class="rec-kpi-value">{{ number_format($kpiExisting['kebutuhan'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>

                                        <div class="rec-kpi-box green">
                                            <div class="rec-kpi-dot"></div>
                                            <div>
                                                <div class="rec-kpi-label">Pemenuhan</div>
                                                <div class="rec-kpi-value">{{ number_format($kpiExisting['pemenuhan'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>

                                        <div class="rec-kpi-box red">
                                            <div class="rec-kpi-dot"></div>
                                            <div>
                                                <div class="rec-kpi-label">Variance</div>
                                                <div class="rec-kpi-value">{{ number_format($kpiExisting['variance'] ?? 0, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="rec-ratio-box">
                                        <div class="rec-ratio-val">{{ number_format($kpiExisting['pencapaian'] ?? 0, 2, ',', '.') }}</div>
                                        <div class="rec-ratio-label">Pencapaian %</div>
                                    </div>
                                </div>

                                <div class="rec-table-wrap">
                                    <table class="rec-table">
                                        <thead>
                                            <tr>
                                                <th>Bulan</th>
                                                <th>Keb.</th>
                                                <th>Pem.</th>
                                                <th>Var.</th>
                                                <th>%</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tableExisting as $row)
                                                <tr>
                                                    <td>{{ $row['bulan'] }}</td>
                                                    <td>{{ number_format($row['kebutuhan'], 0, ',', '.') }}</td>
                                                    <td>{{ number_format($row['pemenuhan'], 0, ',', '.') }}</td>
                                                    <td>{{ number_format($row['variance'], 0, ',', '.') }}</td>
                                                    <td>{{ number_format($row['pencapaian'], 2, ',', '.') }}%</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                        </div>
                    </div>

                    <div class="rec-footer">
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

{{-- resources/views/Investor/DashboardOtif.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --otif-bg: #030712;
        --otif-bg-2: #07111f;
        --otif-card: rgba(8, 15, 28, .96);
        --otif-line: rgba(148, 163, 184, .12);
        --otif-line-soft: rgba(148, 163, 184, .07);
        --otif-text: #eaf2ff;
        --otif-muted: #7f92ae;
        --otif-orange: #f97316;
        --otif-green: #22c55e;
        --otif-red: #ef4444;
        --otif-blue: #3b82f6;
        --otif-shadow: 0 14px 32px rgba(0, 0, 0, .26);
        --otif-radius: 18px;
    }

    .otif-page {
        width: 100%;
        min-height: 100vh;
        padding: 16px;
        color: var(--otif-text);
        background: linear-gradient(180deg, var(--otif-bg) 0%, var(--otif-bg-2) 100%);
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .otif-page * {
        box-sizing: border-box;
    }

    .otif-board {
        width: 100%;
        border: 1px solid var(--otif-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(8, 15, 28, .98), rgba(4, 9, 19, .98));
        box-shadow: var(--otif-shadow);
        overflow: hidden;
    }

    .otif-topbar {
        display: grid;
        grid-template-columns: 116px minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--otif-line);
        background: rgba(255, 255, 255, .018);
    }

    .otif-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 104px;
        height: 52px;
        border: 1px solid var(--otif-line);
        border-radius: 14px;
        background: rgba(255, 255, 255, .035);
    }

    .otif-logo img {
        max-width: 82px;
        max-height: 38px;
        object-fit: contain;
    }

    .otif-title-wrap {
        min-width: 0;
    }

    .otif-eyebrow {
        margin-bottom: 5px;
        color: var(--otif-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .otif-title {
        margin: 0;
        color: #fff;
        font-size: clamp(20px, 2vw, 30px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
        text-transform: uppercase;
    }

    .otif-subtitle {
        margin-top: 6px;
        color: var(--otif-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .otif-filter-form {
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
        border: 1px solid var(--otif-line);
        border-radius: 999px;
        background: rgba(255, 255, 255, .035);
        color: var(--otif-text);
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
        border-color: rgba(249, 115, 22, .38);
        background: rgba(249, 115, 22, .08);
    }

    .badge-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: rgba(249, 115, 22, .18);
        color: #fed7aa;
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
        border: 1px solid var(--otif-line);
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
        border-bottom: 1px solid var(--otif-line-soft);
        color: #cbd8eb;
        cursor: pointer;
        font-size: 12px;
        font-weight: 650;
    }

    .multi-drop-content label:hover {
        background: rgba(249, 115, 22, .08);
        color: #fff;
    }

    .multi-drop-content input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: var(--otif-orange);
    }

    .otif-content {
        padding: 18px 16px 16px;
    }

    .otif-flow {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 34px;
    }

    .otif-main-card {
        position: relative;
        width: 100%;
        max-width: 420px;
        border: 1px solid rgba(249, 115, 22, .26);
        border-radius: var(--otif-radius);
        background: linear-gradient(180deg, rgba(249, 115, 22, .18), rgba(124, 45, 18, .42));
        box-shadow: 0 16px 34px rgba(0,0,0,.28);
        overflow: hidden;
    }

    .otif-main-card::after {
        content: "";
        position: absolute;
        bottom: -34px;
        left: 50%;
        width: 1px;
        height: 34px;
        background: rgba(148, 163, 184, .28);
    }

    .otif-main-head {
        padding: 13px 14px;
        border-bottom: 1px solid rgba(249, 115, 22, .24);
        color: #fed7aa;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .1em;
        text-align: center;
        text-transform: uppercase;
        background: rgba(0,0,0,.14);
    }

    .otif-main-value {
        padding: 28px 14px 30px;
        color: #fff;
        font-size: clamp(46px, 5vw, 68px);
        font-weight: 950;
        line-height: .95;
        letter-spacing: -.06em;
        text-align: center;
    }

    .otif-branches {
        position: relative;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        width: 100%;
        max-width: 1120px;
    }

    .otif-branches::before {
        content: "";
        position: absolute;
        top: -34px;
        left: 25%;
        right: 25%;
        height: 1px;
        background: rgba(148, 163, 184, .28);
    }

    .otif-branch-card {
        position: relative;
        min-width: 0;
        border: 1px solid var(--otif-line);
        border-radius: var(--otif-radius);
        background: var(--otif-card);
        box-shadow: 0 14px 30px rgba(0,0,0,.22);
        overflow: hidden;
    }

    .otif-branch-card::before {
        content: "";
        position: absolute;
        top: -34px;
        left: 50%;
        width: 1px;
        height: 34px;
        background: rgba(148, 163, 184, .28);
    }

    .otif-branch-card.left {
        border-color: rgba(245, 158, 11, .22);
    }

    .otif-branch-card.right {
        border-color: rgba(239, 68, 68, .22);
    }

    .otif-branch-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--otif-line);
        background: rgba(255,255,255,.016);
    }

    .otif-branch-title {
        color: #fff;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .otif-chip {
        height: 24px;
        display: inline-flex;
        align-items: center;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 900;
    }

    .otif-chip.gold {
        color: #fde68a;
        background: rgba(245, 158, 11, .10);
        border: 1px solid rgba(245, 158, 11, .20);
    }

    .otif-chip.red {
        color: #fecaca;
        background: rgba(239, 68, 68, .10);
        border: 1px solid rgba(239, 68, 68, .20);
    }

    .otif-branch-body {
        padding: 14px;
    }

    .otif-percent-box {
        margin-bottom: 14px;
        border: 1px solid rgba(148, 163, 184, .16);
        border-radius: 16px;
        background: linear-gradient(180deg, rgba(255,255,255,.045), rgba(255,255,255,.016));
        padding: 24px 12px;
        text-align: center;
    }

    .otif-branch-card.left .otif-percent-box {
        border-color: rgba(245, 158, 11, .28);
        background: linear-gradient(180deg, rgba(245, 158, 11, .13), rgba(255,255,255,.016));
    }

    .otif-branch-card.right .otif-percent-box {
        border-color: rgba(239, 68, 68, .28);
        background: linear-gradient(180deg, rgba(239, 68, 68, .13), rgba(255,255,255,.016));
    }

    .otif-percent-label {
        margin-bottom: 8px;
        color: var(--otif-muted);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .otif-percent-value {
        color: #fff;
        font-size: clamp(36px, 4vw, 52px);
        font-weight: 950;
        line-height: .95;
        letter-spacing: -.06em;
    }

    .otif-pill-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .otif-mini-card {
        min-width: 0;
        border: 1px solid var(--otif-line-soft);
        border-radius: 14px;
        background: rgba(255, 255, 255, .022);
        padding: 11px;
    }

    .otif-mini-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        color: var(--otif-muted);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .otif-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex: 0 0 7px;
    }

    .otif-dot.green { background: var(--otif-green); box-shadow: 0 0 10px rgba(34,197,94,.55); }
    .otif-dot.red { background: var(--otif-red); box-shadow: 0 0 10px rgba(239,68,68,.55); }
    .otif-dot.blue { background: var(--otif-blue); box-shadow: 0 0 10px rgba(59,130,246,.55); }

    .otif-mini-value {
        margin-top: 10px;
        color: #fff;
        font-size: 26px;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
    }

    .otif-mini-card.green .otif-mini-value { color: #86efac; }
    .otif-mini-card.red .otif-mini-value { color: #fca5a5; }
    .otif-mini-card.blue .otif-mini-value { color: #bfdbfe; }

    .otif-footer {
        padding: 18px 0 0;
        color: #6f86a8;
        font-size: 11px;
        text-align: right;
    }

    @media (max-width: 1200px) {
        .otif-topbar {
            grid-template-columns: 1fr;
        }

        .otif-logo {
            width: 100%;
        }

        .otif-title-wrap {
            text-align: center;
        }

        .otif-filter-form {
            justify-content: center;
        }
    }

    @media (max-width: 900px) {
        .otif-main-card::after,
        .otif-branches::before,
        .otif-branch-card::before {
            display: none;
        }

        .otif-branches {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .otif-page {
            padding: 10px;
        }

        .otif-topbar,
        .otif-content {
            padding: 12px;
        }

        .otif-pill-grid {
            grid-template-columns: 1fr;
        }

        .multi-dropdown {
            min-width: 100%;
        }

        .otif-filter-form {
            width: 100%;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="otif-page">
                <div class="otif-board">

                    <div class="otif-topbar">
                        <div class="otif-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="otif-title-wrap">
                            <div class="otif-eyebrow">Owner - Febriansyah</div>
                            <h1 class="otif-title">On Time In Full (OTIF) Monitoring</h1>
                            <div class="otif-subtitle">Market-board style untuk monitoring on time delivery dan fulfillment delivery.</div>
                        </div>

                        <form method="GET" action="{{ route('investor.sales.dashboardBOD.otif') }}" id="filterForm" class="otif-filter-form">
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

                    <div class="otif-content">
                        <div class="otif-flow">

                            <section class="otif-main-card">
                                <div class="otif-main-head">On Time In Full</div>
                                <div class="otif-main-value">{{ number_format($metrics['pct_otif'], 2, '.', '') }}%</div>
                            </section>

                            <div class="otif-branches">
                                <section class="otif-branch-card left">
                                    <div class="otif-branch-head">
                                        <div class="otif-branch-title">On Time Delivery</div>
                                        <div class="otif-chip gold">Delivery</div>
                                    </div>

                                    <div class="otif-branch-body">
                                        <div class="otif-percent-box">
                                            <div class="otif-percent-label">On Time Percentage</div>
                                            <div class="otif-percent-value">{{ number_format($metrics['pct_ontime'], 2, '.', '') }}%</div>
                                        </div>

                                        <div class="otif-pill-grid">
                                            <div class="otif-mini-card green">
                                                <div class="otif-mini-label">On Time <span class="otif-dot green"></span></div>
                                                <div class="otif-mini-value">{{ number_format($metrics['on_time']) }}</div>
                                            </div>

                                            <div class="otif-mini-card red">
                                                <div class="otif-mini-label">Late <span class="otif-dot red"></span></div>
                                                <div class="otif-mini-value">{{ number_format($metrics['late']) }}</div>
                                            </div>

                                            <div class="otif-mini-card blue">
                                                <div class="otif-mini-label">Total <span class="otif-dot blue"></span></div>
                                                <div class="otif-mini-value">{{ number_format($metrics['total_ontime']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="otif-branch-card right">
                                    <div class="otif-branch-head">
                                        <div class="otif-branch-title">Fulfillment Delivery</div>
                                        <div class="otif-chip red">Fulfillment</div>
                                    </div>

                                    <div class="otif-branch-body">
                                        <div class="otif-percent-box">
                                            <div class="otif-percent-label">Fulfillment Percentage</div>
                                            <div class="otif-percent-value">{{ number_format($metrics['pct_fulfill'], 2, '.', '') }}%</div>
                                        </div>

                                        <div class="otif-pill-grid">
                                            <div class="otif-mini-card green">
                                                <div class="otif-mini-label">Hit <span class="otif-dot green"></span></div>
                                                <div class="otif-mini-value">{{ number_format($metrics['clear']) }}</div>
                                            </div>

                                            <div class="otif-mini-card red">
                                                <div class="otif-mini-label">Miss <span class="otif-dot red"></span></div>
                                                <div class="otif-mini-value">{{ number_format($metrics['miss']) }}</div>
                                            </div>

                                            <div class="otif-mini-card blue">
                                                <div class="otif-mini-label">Total <span class="otif-dot blue"></span></div>
                                                <div class="otif-mini-value">{{ number_format($metrics['total_clear']) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                        </div>

                        <div class="otif-footer">
                            Data Last Updated: {{ $lastSyncAt }}

        {{-- TABLE DATA --}}
        <div style="margin-top:30px;border:1px solid rgba(148,163,184,.12);border-radius:16px;overflow:hidden;background:rgba(8,15,28,.96);">
            <div style="padding:12px 14px;border-bottom:1px solid rgba(148,163,184,.12);font-weight:900;letter-spacing:.08em;">
                OTIF SUMMARY
            </div>
            <div style="overflow:auto;">
                <table style="width:100%;min-width:700px;border-collapse:collapse;text-align:right;">
                    <thead>
                        <tr style="background:#071426;color:#8fa3c0;font-size:11px;text-transform:uppercase;">
                            <th style="padding:10px;text-align:left;">Category</th>
                            <th>Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:10px;text-align:left;">OTIF %</td>
                            <td style="padding:10px;">{{ number_format($metrics['pct_otif'],2) }}%</td>
                        </tr>
                        <tr>
                            <td style="padding:10px;text-align:left;">On Time</td>
                            <td style="padding:10px;">{{ number_format($metrics['on_time']) }}</td>
                        </tr>
                        <tr>
                            <td style="padding:10px;text-align:left;">Late</td>
                            <td style="padding:10px;">{{ number_format($metrics['late']) }}</td>
                        </tr>
                        <tr>
                            <td style="padding:10px;text-align:left;">Fulfill Hit</td>
                            <td style="padding:10px;">{{ number_format($metrics['clear']) }}</td>
                        </tr>
                        <tr>
                            <td style="padding:10px;text-align:left;">Fulfill Miss</td>
                            <td style="padding:10px;">{{ number_format($metrics['miss']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
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

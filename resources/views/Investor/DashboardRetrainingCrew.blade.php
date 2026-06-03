{{-- resources/views/Investor/DashboardRetrainingCrew.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --rt-bg: #030712;
        --rt-bg-2: #07111f;
        --rt-card: rgba(8, 15, 28, .96);
        --rt-card-2: rgba(11, 20, 36, .92);
        --rt-line: rgba(148, 163, 184, .12);
        --rt-line-soft: rgba(148, 163, 184, .07);
        --rt-text: #eaf2ff;
        --rt-muted: #7f92ae;
        --rt-blue: #3b82f6;
        --rt-green: #22c55e;
        --rt-red: #ef4444;
        --rt-gold: #f59e0b;
        --rt-shadow: 0 14px 32px rgba(0, 0, 0, .26);
        --rt-radius: 18px;
    }

    .rt-page {
        width: 100%;
        min-height: 100vh;
        padding: 16px;
        color: var(--rt-text);
        background: linear-gradient(180deg, var(--rt-bg) 0%, var(--rt-bg-2) 100%);
        font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .rt-page * {
        box-sizing: border-box;
    }

    .rt-board {
        width: 100%;
        border: 1px solid var(--rt-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(8, 15, 28, .98), rgba(4, 9, 19, .98));
        box-shadow: var(--rt-shadow);
        overflow: hidden;
    }

    .rt-topbar {
        display: grid;
        grid-template-columns: 116px minmax(0, 1fr) auto;
        align-items: center;
        gap: 16px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--rt-line);
        background: rgba(255, 255, 255, .018);
    }

    .rt-logo {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 104px;
        height: 52px;
        border: 1px solid var(--rt-line);
        border-radius: 14px;
        background: rgba(255, 255, 255, .035);
    }

    .rt-logo img {
        max-width: 82px;
        max-height: 38px;
        object-fit: contain;
    }

    .rt-title-wrap {
        min-width: 0;
    }

    .rt-eyebrow {
        margin-bottom: 5px;
        color: var(--rt-muted);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .rt-title {
        margin: 0;
        color: #fff;
        font-size: clamp(20px, 2vw, 30px);
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
        text-transform: uppercase;
    }

    .rt-subtitle {
        margin-top: 6px;
        color: var(--rt-muted);
        font-size: 12px;
        line-height: 1.35;
    }

    .rt-filter-form {
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
        border: 1px solid var(--rt-line);
        border-radius: 999px;
        background: rgba(255, 255, 255, .035);
        color: var(--rt-text);
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
        border: 1px solid var(--rt-line);
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
        border-bottom: 1px solid var(--rt-line-soft);
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
        accent-color: var(--rt-blue);
    }

    .rt-content {
        padding: 16px;
    }

    .rt-metrics {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .rt-metric-card {
        border: 1px solid rgba(239, 68, 68, .18);
        border-radius: var(--rt-radius);
        background: linear-gradient(180deg, rgba(239, 68, 68, .08), rgba(255, 255, 255, .018));
        overflow: hidden;
    }

    .rt-metric-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--rt-line);
        color: #fecaca;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .rt-market-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--rt-red);
        box-shadow: 0 0 12px rgba(239, 68, 68, .58);
    }

    .rt-metric-value {
        padding: 18px 14px 20px;
        color: #fff;
        font-size: clamp(34px, 4vw, 54px);
        font-weight: 950;
        line-height: .95;
        letter-spacing: -.06em;
        text-align: center;
    }

    .rt-validation-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .rt-val-card {
        min-width: 0;
        border: 1px solid var(--rt-line);
        border-radius: var(--rt-radius);
        background: var(--rt-card);
        overflow: hidden;
    }

    .rt-val-card.v1 { border-color: rgba(148, 163, 184, .18); }
    .rt-val-card.v2 { border-color: rgba(59, 130, 246, .22); }
    .rt-val-card.v3 { border-color: rgba(34, 197, 94, .22); }

    .rt-val-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--rt-line);
        background: rgba(255, 255, 255, .016);
    }

    .rt-val-title {
        color: #fff;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .rt-val-chip {
        height: 24px;
        display: inline-flex;
        align-items: center;
        padding: 0 9px;
        border-radius: 999px;
        color: #cbd5e1;
        background: rgba(148, 163, 184, .10);
        border: 1px solid rgba(148, 163, 184, .14);
        font-size: 10px;
        font-weight: 900;
    }

    .rt-val-card.v2 .rt-val-chip {
        color: #bfdbfe;
        background: rgba(59, 130, 246, .12);
        border-color: rgba(59, 130, 246, .20);
    }

    .rt-val-card.v3 .rt-val-chip {
        color: #bbf7d0;
        background: rgba(34, 197, 94, .10);
        border-color: rgba(34, 197, 94, .20);
    }

    .rt-score-box {
        padding: 18px 14px;
        border-bottom: 1px solid var(--rt-line);
        text-align: center;
        background: rgba(255, 255, 255, .018);
    }

    .rt-score-label {
        margin-bottom: 8px;
        color: var(--rt-muted);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .rt-score-value {
        color: #fff;
        font-size: 42px;
        font-weight: 950;
        line-height: .95;
        letter-spacing: -.06em;
    }

    .rt-val-card.v2 .rt-score-value { color: #93c5fd; }
    .rt-val-card.v3 .rt-score-value { color: #86efac; }

    .rt-sub-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        padding: 12px;
    }

    .rt-sub-box {
        min-width: 0;
        border: 1px solid var(--rt-line-soft);
        border-radius: 12px;
        background: rgba(255, 255, 255, .022);
        padding: 10px;
    }

    .rt-sub-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        color: var(--rt-muted);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .rt-sub-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex: 0 0 6px;
        background: #94a3b8;
    }

    .rt-val-card.v2 .rt-sub-dot { background: var(--rt-blue); box-shadow: 0 0 9px rgba(59, 130, 246, .55); }
    .rt-val-card.v3 .rt-sub-dot { background: var(--rt-green); box-shadow: 0 0 9px rgba(34, 197, 94, .55); }

    .rt-sub-value {
        margin-top: 8px;
        color: #fff;
        font-size: 22px;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.04em;
    }

    .rt-val-card.v2 .rt-sub-value { color: #bfdbfe; }
    .rt-val-card.v3 .rt-sub-value { color: #bbf7d0; }

    .rt-footer {
        padding: 16px 0 0;
        color: #6f86a8;
        font-size: 11px;
        text-align: right;
    }

    @media (max-width: 1200px) {
        .rt-topbar {
            grid-template-columns: 1fr;
        }

        .rt-logo {
            width: 100%;
        }

        .rt-title-wrap {
            text-align: center;
        }

        .rt-filter-form {
            justify-content: center;
        }

        .rt-validation-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .rt-page {
            padding: 10px;
        }

        .rt-topbar,
        .rt-content {
            padding: 12px;
        }

        .rt-metrics {
            grid-template-columns: 1fr;
        }

        .multi-dropdown {
            min-width: 100%;
        }

        .rt-filter-form {
            width: 100%;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="rt-page">
                <div class="rt-board">

                    <div class="rt-topbar">
                        <div class="rt-logo">
                            <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                        </div>

                        <div class="rt-title-wrap">
                            <div class="rt-eyebrow">Owner - Soehartono</div>
                            <h1 class="rt-title">Re-Training Crew Existing</h1>
                            <div class="rt-subtitle">Market-board style untuk score competency dan validasi retraining crew.</div>
                        </div>

                        <form method="GET" action="{{ route('investor.sales.dashboardBOD.retrainingCrew') }}" id="filterForm" class="rt-filter-form">
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

                    <div class="rt-content">
                        <div class="rt-metrics">
                            <section class="rt-metric-card">
                                <div class="rt-metric-head">
                                    <span>Total Re-Training Crew</span>
                                    <span class="rt-market-dot"></span>
                                </div>
                                <div class="rt-metric-value">{{ number_format($metrics['total_crew'], 1, '.', '') }}</div>
                            </section>

                            <section class="rt-metric-card">
                                <div class="rt-metric-head">
                                    <span>Score Competency</span>
                                    <span class="rt-market-dot"></span>
                                </div>
                                <div class="rt-metric-value">{{ number_format($metrics['score_competency'], 1, '.', '') }}</div>
                            </section>
                        </div>

                        <div class="rt-validation-grid">

                            {{-- VALIDASI 1 --}}
                            <section class="rt-val-card v1">
                                <div class="rt-val-head">
                                    <div class="rt-val-title">Score Validasi 1</div>
                                    <div class="rt-val-chip">V1</div>
                                </div>

                                <div class="rt-score-box">
                                    <div class="rt-score-label">Main Score</div>
                                    <div class="rt-score-value">{{ number_format($metrics['val1']['score'], 1, '.', '') }}</div>
                                </div>

                                <div class="rt-sub-grid">
                                    <div class="rt-sub-box"><div class="rt-sub-label">Grooming <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val1']['grooming'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Nasi <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val1']['nasi'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Ayam <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val1']['ayam'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Kasir <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val1']['kasir'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Sambal <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val1']['sambal'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Admin <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val1']['admin'], 1, '.', '') }}</div></div>
                                </div>
                            </section>

                            {{-- VALIDASI 2 --}}
                            <section class="rt-val-card v2">
                                <div class="rt-val-head">
                                    <div class="rt-val-title">Score Validasi 2</div>
                                    <div class="rt-val-chip">V2</div>
                                </div>

                                <div class="rt-score-box">
                                    <div class="rt-score-label">Main Score</div>
                                    <div class="rt-score-value">{{ number_format($metrics['val2']['score'], 1, '.', '') }}</div>
                                </div>

                                <div class="rt-sub-grid">
                                    <div class="rt-sub-box"><div class="rt-sub-label">Grooming <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val2']['grooming'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Nasi <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val2']['nasi'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Ayam <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val2']['ayam'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Kasir <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val2']['kasir'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Sambal <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val2']['sambal'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Admin <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val2']['admin'], 1, '.', '') }}</div></div>
                                </div>
                            </section>

                            {{-- VALIDASI 3 --}}
                            <section class="rt-val-card v3">
                                <div class="rt-val-head">
                                    <div class="rt-val-title">Score Validasi 3</div>
                                    <div class="rt-val-chip">V3</div>
                                </div>

                                <div class="rt-score-box">
                                    <div class="rt-score-label">Main Score</div>
                                    <div class="rt-score-value">{{ number_format($metrics['val3']['score'], 1, '.', '') }}</div>
                                </div>

                                <div class="rt-sub-grid">
                                    <div class="rt-sub-box"><div class="rt-sub-label">Grooming <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val3']['grooming'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Nasi <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val3']['nasi'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Ayam <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val3']['ayam'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Kasir <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val3']['kasir'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Sambal <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val3']['sambal'], 1, '.', '') }}</div></div>
                                    <div class="rt-sub-box"><div class="rt-sub-label">Admin <span class="rt-sub-dot"></span></div><div class="rt-sub-value">{{ number_format($metrics['val3']['admin'], 1, '.', '') }}</div></div>
                                </div>
                            </section>

                        </div>

                        <div class="rt-footer">
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

{{-- resources/views/Investor/DashboardTrainingLeader.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --lab-bg: #07101d;
        --lab-panel: #0b1830;
        --lab-line: rgba(148, 163, 184, .14);
        --lab-text: #e5eefc;
        --lab-muted: #93a4bd;
        --lab-shadow: 0 16px 40px rgba(0, 0, 0, .35);

        --fun-blue: #1d4ed8;
        --fun-yellow: #b45309;
        --fun-green: #047857;
        --fun-red: #991b1b;
        
        --card-int-bg: rgba(15, 23, 42, 0.70);
        --card-int-border: rgba(59, 130, 246, 0.28);
        --card-ext-bg: rgba(15, 23, 42, 0.70);
        --card-ext-border: rgba(239, 68, 68, 0.28);
    }

    .lab-page { width: 100%; min-height: 100vh; padding: 20px; background: radial-gradient(circle at top left, rgba(59, 130, 246, .12), transparent 22%), radial-gradient(circle at top right, rgba(6, 182, 212, .10), transparent 20%), linear-gradient(180deg, #07101d 0%, #0a1526 100%); color: var(--lab-text); font-family: 'Inter', sans-serif; }
    .lab-board { border-radius: 26px; padding: 18px; background: linear-gradient(180deg, rgba(10, 21, 38, .98), rgba(6, 12, 23, .99)); border: 1px solid var(--lab-line); box-shadow: var(--lab-shadow); }
    .lab-topbar { display: grid; grid-template-columns: 240px minmax(0, 1fr) 360px; gap: 18px; align-items: center; margin-bottom: 24px; }
    .lab-brand-pill { display: inline-flex; align-items: center; padding: 12px 16px; border-radius: 18px; background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .02)); border: 1px solid var(--lab-line); }
    .lab-brand-pill img { max-height: 52px; width: auto; }
    .lab-header-box { text-align: center; }
    .lab-header-label { color: #b9c8dc; font-size: 13px; font-weight: 800; letter-spacing: .1em; margin-bottom: 8px; }
    .lab-header-title { color: #f8fbff; font-size: clamp(20px, 2.2vw, 26px); font-weight: 900; letter-spacing: -.02em; text-transform: uppercase; }

    /* CSS MULTI SELECT DROPDOWN */
    .lab-filter-box form { display: flex; justify-content: flex-end; gap: 12px; }
    .multi-dropdown { position: relative; display: inline-block; }
    .multi-drop-btn { background: rgba(255, 255, 255, .05); border: 1px solid var(--lab-line); padding: 10px 16px; border-radius: 12px; cursor: pointer; color: #e5eefc; font-weight: 600; min-width: 140px; text-align: left; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    .multi-drop-content { display: none; position: absolute; background-color: #0f172a; min-width: 100%; box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.5); z-index: 100; border-radius: 12px; max-height: 250px; overflow-y: auto; margin-top: 8px; border: 1px solid var(--lab-line); }
    .multi-drop-content.show { display: block; }
    .multi-drop-content label { color: #cbd5e1; padding: 10px 16px; display: flex; align-items: center; cursor: pointer; border-bottom: 1px solid rgba(255, 255, 255, .05); margin: 0; font-size: 13px; }
    .multi-drop-content label:hover { background-color: rgba(255, 255, 255, .05); color: #fff; }
    .multi-drop-content input[type="checkbox"] { margin-right: 12px; transform: scale(1.2); accent-color: var(--fun-blue); }
    .badge-count { background: var(--fun-blue); color: #fff; padding: 2px 6px; border-radius: 6px; font-size: 11px; margin-left: 8px; }

    /* HEADER METRICS (ARROWS & TOTAL LEADER) */
    .hero-container { display: flex; justify-content: center; align-items: center; gap: 16px; margin-bottom: 40px; margin-top: 20px; flex-wrap: wrap;}
    
    .hero-box { border: 2px solid; border-radius: 8px; text-align: center; overflow: hidden; background: #0f172a; min-width: 120px;}
    .hero-box-head { padding: 6px; font-size: 12px; font-weight: 800; color: #fff; text-transform: uppercase; }
    .hero-box-val { font-size: 36px; font-weight: 900; padding: 12px; line-height: 1; }

    .box-internal { border-color: var(--card-int-border); }
    .box-internal .hero-box-head { background: var(--card-int-border); }
    .box-internal .hero-box-val { color: #93c5fd; }

    .box-external { border-color: var(--card-ext-border); }
    .box-external .hero-box-head { background: var(--card-ext-border); }
    .box-external .hero-box-val { color: #fca5a5; }

    .box-center { border-color: var(--fun-green); min-width: 160px; box-shadow: 0 0 20px rgba(16, 185, 129, 0.2); }
    .box-center .hero-box-head { background: var(--fun-green); font-size: 14px; }
    .box-center .hero-box-val { color: #fff; background: rgba(16, 185, 129, 0.1); font-size: 48px; }

    /* CSS ARROW SHAPE */
    .arrow-right { width: 60px; height: 30px; background: var(--fun-green); clip-path: polygon(0 20%, 70% 20%, 70% 0, 100% 50%, 70% 100%, 70% 80%, 0 80%); }
    .arrow-left { width: 60px; height: 30px; background: var(--fun-green); clip-path: polygon(30% 20%, 100% 20%, 100% 80%, 30% 80%, 30% 100%, 0 50%, 30% 0); }

    /* FUNNEL GRIDS */
    .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; }

    .funnel-card { border-radius: 20px; padding: 24px; border: 2px solid; position: relative; }
    .funnel-card.internal { background: var(--card-int-bg); border-color: var(--card-int-border); }
    .funnel-card.external { background: var(--card-ext-bg); border-color: var(--card-ext-border); }

    .funnel-title { text-align: center; font-size: 20px; font-weight: 900; color: #fff; margin-bottom: 30px; font-style: italic; letter-spacing: 1px; }

    .funnel-wrapper { display: flex; flex-direction: column; align-items: center; gap: 8px; }

    /* CSS TRAPEZOIDS */
    .funnel-step { display: flex; flex-direction: column; justify-content: center; align-items: center; color: #fff; font-weight: 800; line-height: 1.2; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }
    .f-label { font-size: 14px; font-style: italic; margin-bottom: 4px; }
    .f-val { font-size: 32px; font-weight: 900; }

    .f-step-1 { width: 90%; background: var(--fun-blue); clip-path: polygon(0 0, 100% 0, 92% 100%, 8% 100%); padding: 24px 10px; }
    .f-step-2 { width: 75%; background: var(--fun-yellow); clip-path: polygon(0 0, 100% 0, 88% 100%, 12% 100%); padding: 20px 10px; }
    .f-step-3 { width: 60%; background: var(--fun-green); clip-path: polygon(0 0, 100% 0, 85% 100%, 15% 100%); padding: 18px 10px; }
    
    /* Shield/Pin shape for Average */
    .f-step-4 { width: 45%; background: var(--fun-red); clip-path: polygon(0 0, 100% 0, 100% 60%, 50% 100%, 0 60%); padding: 16px 10px 40px; margin-top: 10px; }
    .f-step-4 .f-label { font-size: 12px; }
    .f-step-4 .f-val { font-size: 36px; }


    /* SUMMARY TABLE */
    .summary-table-card {
        margin-top: 28px;
        border: 1px solid var(--lab-line);
        border-radius: 18px;
        overflow: hidden;
        background: rgba(15, 23, 42, .62);
    }

    .summary-table-title {
        padding: 14px 16px;
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        letter-spacing: 1px;
        text-transform: uppercase;
        border-bottom: 1px solid var(--lab-line);
        background: rgba(255,255,255,.025);
    }

    .summary-table-wrap {
        overflow-x: auto;
    }

    .summary-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
        text-align: right;
    }

    .summary-table th,
    .summary-table td {
        padding: 11px 12px;
        border-bottom: 1px solid rgba(148,163,184,.10);
        border-right: 1px solid rgba(148,163,184,.06);
        white-space: nowrap;
    }

    .summary-table th {
        background: #071426;
        color: #9fb3d2;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .summary-table td {
        color: var(--lab-text);
        font-size: 12px;
        font-weight: 700;
    }

    .summary-table tr:nth-child(even) td {
        background: rgba(255,255,255,.02);
    }

    .summary-table th:first-child,
    .summary-table td:first-child {
        text-align: left;
    }

    .summary-blue { color: #93c5fd !important; }
    .summary-red { color: #fca5a5 !important; }
    .summary-green { color: #86efac !important; }

    @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } .arrow-right, .arrow-left { display: none; } }
    @media (max-width: 576px) { .hero-box-val { font-size: 24px; } .box-center .hero-box-val { font-size: 32px; } .f-val { font-size: 24px; } .f-step-4 .f-val { font-size: 28px; } }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="lab-page">
                <div class="lab-board">

                    {{-- TOP BAR --}}
                    <div class="lab-topbar">
                        <div class="lab-brand-box">
                            <div class="lab-brand-pill">
                                <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
                            </div>
                        </div>

                        <div class="lab-header-box">
                            <!-- <div class="lab-header-label">OWNER - SOEHARTONO</div> -->
                            <div class="lab-header-title">TRAINING LEADER</div>
                        </div>

                        <div class="lab-filter-box">
                            <form method="GET" action="{{ route('investor.sales.dashboardBOD.trainingLeader') }}" id="filterForm">
                                {{-- Filter Tahun --}}
                                <div class="multi-dropdown">
                                    <div class="multi-drop-btn" onclick="toggleDropdown('dropTahun')">
                                        <div>Tahun <span class="badge-count">{{ count($filters['tahun'] ?? []) }}</span></div>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div id="dropTahun" class="multi-drop-content">
                                        @foreach(($availableYears ?? []) as $year)
                                            <label><input type="checkbox" name="tahun[]" value="{{ $year }}" onchange="document.getElementById('filterForm').submit()" {{ in_array((string)$year, $filters['tahun'] ?? []) ? 'checked' : '' }}> {{ $year }}</label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Filter Bulan --}}
                                <div class="multi-dropdown">
                                    <div class="multi-drop-btn" onclick="toggleDropdown('dropBulan')">
                                        <div>Bulan <span class="badge-count">{{ count($filters['bulan'] ?? []) }}</span></div>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                    <div id="dropBulan" class="multi-drop-content">
                                        @foreach(($monthLabels ?? []) as $index => $monthLabel)
                                            <label><input type="checkbox" name="bulan[]" value="{{ $index + 1 }}" onchange="document.getElementById('filterForm').submit()" {{ in_array((string)($index + 1), $filters['bulan'] ?? []) ? 'checked' : '' }}> {{ $monthLabel }}</label>
                                        @endforeach
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- HERO METRICS (ARROWS) --}}
                    <div class="hero-container">
                        {{-- Kiri: % Internal --}}
                        <div class="hero-box box-internal">
                            <div class="hero-box-head">% Internal</div>
                            <div class="hero-box-val">{{ number_format($metrics['pct_internal'], 2) }}%</div>
                        </div>
                        <div class="hero-box box-internal" style="background:transparent; border:none;">
                            <div class="hero-box-head" style="background:transparent; color:#93c5fd;">Kompeten Internal</div>
                            <div class="hero-box-val" style="padding:0;">{{ number_format($metrics['int_kompeten']) }}</div>
                        </div>

                        {{-- Panah Kanan --}}
                        <div class="arrow-right"></div>

                        {{-- TENGAH: Total Leader --}}
                        <div class="hero-box box-center">
                            <div class="hero-box-head">Total Leader</div>
                            <div class="hero-box-val">{{ number_format($metrics['total_leader']) }}</div>
                        </div>

                        {{-- Panah Kiri --}}
                        <div class="arrow-left"></div>

                        {{-- Kanan: % External --}}
                        <div class="hero-box box-external" style="background:transparent; border:none;">
                            <div class="hero-box-head" style="background:transparent; color:#fca5a5;">Kompeten External</div>
                            <div class="hero-box-val" style="padding:0;">{{ number_format($metrics['ext_kompeten']) }}</div>
                        </div>
                        <div class="hero-box box-external">
                            <div class="hero-box-head">% External</div>
                            <div class="hero-box-val">{{ number_format($metrics['pct_external'], 2) }}%</div>
                        </div>
                    </div>

                    {{-- DASHBOARD FUNNELS --}}
                    <div class="dashboard-grid">
                        
                        {{-- INTERNAL TRAINING CLASS --}}
                        <div class="funnel-card internal">
                            <div class="funnel-title">Internal Training Class</div>
                            <div class="funnel-wrapper">
                                <div class="funnel-step f-step-1">
                                    <div class="f-label">Total TC</div>
                                    <div class="f-val">{{ number_format($metrics['int_total_tc']) }}</div>
                                </div>
                                <div class="funnel-step f-step-2">
                                    <div class="f-label">Tuntas TC</div>
                                    <div class="f-val">{{ number_format($metrics['int_tuntas_tc']) }}</div>
                                </div>
                                <div class="funnel-step f-step-3">
                                    <div class="f-label">Kompeten</div>
                                    <div class="f-val">{{ number_format($metrics['int_kompeten']) }}</div>
                                </div>
                                <div class="funnel-step f-step-4">
                                    <div class="f-label">Average Score</div>
                                    <div class="f-val">{{ number_format($metrics['int_avg_score'], 1) }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- EXTERNAL TRAINING CLASS --}}
                        <div class="funnel-card external">
                            <div class="funnel-title">External Training Class</div>
                            <div class="funnel-wrapper">
                                <div class="funnel-step f-step-1">
                                    <div class="f-label">Total TC</div>
                                    <div class="f-val">{{ number_format($metrics['ext_total_tc']) }}</div>
                                </div>
                                <div class="funnel-step f-step-2">
                                    <div class="f-label">Tuntas TC</div>
                                    <div class="f-val">{{ number_format($metrics['ext_tuntas_tc']) }}</div>
                                </div>
                                <div class="funnel-step f-step-3">
                                    <div class="f-label">Kompeten</div>
                                    <div class="f-val">{{ number_format($metrics['ext_kompeten']) }}</div>
                                </div>
                                <div class="funnel-step f-step-4">
                                    <div class="f-label">Average Score</div>
                                    <div class="f-val">{{ number_format($metrics['ext_avg_score'], 1) }}</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- SUMMARY TABLE --}}
                    <div class="summary-table-card">
                        <div class="summary-table-title">Training Leader Summary</div>
                        <div class="summary-table-wrap">
                            <table class="summary-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Total TC</th>
                                        <th>Tuntas TC</th>
                                        <th>Kompeten</th>
                                        <th>Average Score</th>
                                        <th>% Kompeten</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Internal Training Class</td>
                                        <td class="summary-blue">{{ number_format($metrics['int_total_tc']) }}</td>
                                        <td>{{ number_format($metrics['int_tuntas_tc']) }}</td>
                                        <td class="summary-green">{{ number_format($metrics['int_kompeten']) }}</td>
                                        <td>{{ number_format($metrics['int_avg_score'], 1) }}</td>
                                        <td class="summary-blue">{{ number_format($metrics['pct_internal'], 2) }}%</td>
                                    </tr>
                                    <tr>
                                        <td>External Training Class</td>
                                        <td class="summary-red">{{ number_format($metrics['ext_total_tc']) }}</td>
                                        <td>{{ number_format($metrics['ext_tuntas_tc']) }}</td>
                                        <td class="summary-green">{{ number_format($metrics['ext_kompeten']) }}</td>
                                        <td>{{ number_format($metrics['ext_avg_score'], 1) }}</td>
                                        <td class="summary-red">{{ number_format($metrics['pct_external'], 2) }}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="text-align: center; color: #7286a4; font-size: 11px; margin-top: 32px;">
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
            if(content.id !== id) content.classList.remove('show');
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
</script>

@include('Temp.internal.footer_internal')
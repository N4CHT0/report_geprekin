{{-- resources/views/Investor/DashboardLeadsKemitraan.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --lab-bg: #07101d;
        --lab-panel: #0b1830;
        --lab-line: rgba(148, 163, 184, .14);
        --lab-text: #e5eefc;
        --lab-shadow: 0 16px 40px rgba(0, 0, 0, .35);

        /* Warna Kotak & Funnel berdasarkan Screenshot */
        --lk-pink: #be123c;    /* Follow Up & Leads */
        --lk-blue: #1d4ed8;    /* Input Data, Rejected & Hot Prospect */
        --lk-green: #047857;   /* DP, Lunas & Deal */
        --lk-text-dark: #f8fafc;  /* Teks terang di dalam kotak dark */
        --lk-text-red: #fecaca; /* Teks Merah untuk Rejected */
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
    .multi-drop-content input[type="checkbox"] { margin-right: 12px; transform: scale(1.2); accent-color: var(--lk-pink); }
    .badge-count { background: var(--lk-pink); color: #000; padding: 2px 6px; border-radius: 6px; font-size: 11px; margin-left: 8px; font-weight: bold;}

    /* DASHBOARD LAYOUT */
    .leads-dashboard { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start; margin-top: 32px; padding: 20px;}

    /* KIRI: KOTAK-KOTAK STAGE */
    .boxes-container { display: flex; flex-direction: column; gap: 24px; }
    
    .boxes-row { display: flex; gap: 16px; justify-content: center; }
    .boxes-row-3 { grid-template-columns: repeat(3, 1fr); display: grid; gap: 16px;}

    .stage-box { padding: 16px; border-radius: 8px; text-align: center; color: var(--lk-text-dark); display: flex; flex-direction: column; justify-content: center; min-width: 140px;}
    .stage-box-title { font-size: 14px; font-weight: 900; font-style: italic; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;}
    .stage-box-val { font-size: 46px; font-weight: 900; line-height: 1; }

    /* Varian Warna Kotak */
    .box-pink { background: linear-gradient(180deg, #fb7185 0%, #be123c 100%); }
    .box-blue { background: linear-gradient(180deg, #60a5fa 0%, #1d4ed8 100%); }
    .box-green { background: linear-gradient(180deg, #34d399 0%, #047857 100%); }
    
    /* Warna Teks Khusus */
    .text-red { color: var(--lk-text-red); }

    /* KANAN: CORONG (FUNNEL) */
    .funnel-container { display: flex; flex-direction: column; align-items: center; gap: 12px; justify-content: center; height: 100%; padding-top: 10px;}
    
    .funnel-step { display: flex; flex-direction: column; justify-content: center; align-items: center; color: var(--lk-text-dark); position: relative; }
    .funnel-title { font-size: 14px; font-weight: 900; font-style: italic; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 1px;}
    .funnel-val { font-size: 52px; font-weight: 900; line-height: 1.1; }

    /* Bentuk Trapesium Terbalik dengan CSS Clip Path */
    .f-leads { width: 100%; max-width: 450px; background: linear-gradient(180deg, #fb7185 0%, #be123c 100%); clip-path: polygon(0 0, 100% 0, 85% 100%, 15% 100%); padding: 24px 10px 30px; }
    .f-hp { width: 70%; max-width: 315px; background: linear-gradient(180deg, #60a5fa 0%, #1d4ed8 100%); clip-path: polygon(0 0, 100% 0, 80% 100%, 20% 100%); padding: 18px 10px 24px; }
    .f-deal { width: 44%; max-width: 198px; background: linear-gradient(180deg, #34d399 0%, #047857 100%); clip-path: polygon(0 0, 100% 0, 70% 100%, 30% 100%); padding: 12px 10px 20px; }


    .stage-box,
    .funnel-step {
        border: 1px solid rgba(255,255,255,.16);
        box-shadow: 0 14px 30px rgba(0,0,0,.28);
        color: #fff;
    }

    .funnel-step {
        text-shadow: 0 2px 8px rgba(0,0,0,.45);
    }

    .stage-box-title,
    .funnel-title {
        color: rgba(255,255,255,.88);
    }

    .stage-box-val,
    .funnel-val {
        color: #fff;
    }

    /* DATA TABLE */
    .leads-table-card {
        margin-top: 30px;
        border: 1px solid var(--lab-line);
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(180deg, rgba(15,23,42,.82), rgba(8,13,24,.96));
        box-shadow: 0 14px 30px rgba(0,0,0,.24);
    }

    .leads-table-head {
        padding: 14px 16px;
        border-bottom: 1px solid var(--lab-line);
        color: #fff;
        font-size: 14px;
        font-weight: 900;
        letter-spacing: 1px;
        text-transform: uppercase;
        background: rgba(255,255,255,.025);
    }

    .leads-table-wrap {
        overflow-x: auto;
    }

    .leads-table {
        width: 100%;
        min-width: 860px;
        border-collapse: collapse;
        text-align: right;
    }

    .leads-table th,
    .leads-table td {
        padding: 11px 12px;
        border-bottom: 1px solid rgba(148,163,184,.10);
        border-right: 1px solid rgba(148,163,184,.06);
        white-space: nowrap;
    }

    .leads-table th {
        background: #071426;
        color: #9fb3d2;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .leads-table td {
        color: var(--lab-text);
        font-size: 12px;
        font-weight: 750;
    }

    .leads-table tr:nth-child(even) td {
        background: rgba(255,255,255,.022);
    }

    .leads-table th:first-child,
    .leads-table td:first-child {
        text-align: left;
    }

    .leads-pink { color: #fda4af !important; }
    .leads-blue { color: #93c5fd !important; }
    .leads-green { color: #86efac !important; }
    .leads-red { color: #fecaca !important; }

    @media (max-width: 1024px) { .leads-dashboard { grid-template-columns: 1fr; gap: 60px;} .lab-topbar { grid-template-columns: 1fr; text-align: center; } .lab-filter-box form { justify-content: center; } }
    @media (max-width: 576px) { .boxes-row-3 { grid-template-columns: 1fr; } .boxes-row { flex-direction: column; } .stage-box { min-height: 120px; } }
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
                            <!-- <div class="lab-header-label">OWNER - M. FAIZ</div> -->
                            <div class="lab-header-title">MONITORING LEADS KEMITRAAN</div>
                        </div>

                        <div class="lab-filter-box">
                            <form method="GET" action="{{ route('investor.sales.dashboardBOD.leadsKemitraan') }}" id="filterForm">
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

                    {{-- MAIN DASHBOARD --}}
                    <div class="leads-dashboard">
                        
                        {{-- KIRI: Kotak-kotak Stage --}}
                        <div class="boxes-container">
                            {{-- Row 1: Follow Ups --}}
                            <div class="boxes-row-3">
                                <div class="stage-box box-pink">
                                    <div class="stage-box-title">Follow Up 1</div>
                                    <div class="stage-box-val">{{ number_format($metrics['fu1']) }}</div>
                                </div>
                                <div class="stage-box box-pink">
                                    <div class="stage-box-title">Follow Up 2</div>
                                    <div class="stage-box-val">{{ number_format($metrics['fu2']) }}</div>
                                </div>
                                <div class="stage-box box-pink">
                                    <div class="stage-box-title">Follow Up 3</div>
                                    <div class="stage-box-val">{{ number_format($metrics['fu3']) }}</div>
                                </div>
                            </div>
                            
                            {{-- Row 2: Data & Reject --}}
                            <div class="boxes-row">
                                <div class="stage-box box-blue" style="flex:1;">
                                    <div class="stage-box-title">Input Data</div>
                                    <div class="stage-box-val">{{ number_format($metrics['input_data']) }}</div>
                                </div>
                                <div class="stage-box box-blue" style="flex:1;">
                                    <div class="stage-box-title">Rejected</div>
                                    <div class="stage-box-val text-red">{{ number_format($metrics['rejected']) }}</div>
                                </div>
                            </div>

                            {{-- Row 3: DP & Lunas --}}
                            <div class="boxes-row">
                                <div class="stage-box box-green" style="flex:1;">
                                    <div class="stage-box-title">DP</div>
                                    <div class="stage-box-val">{{ number_format($metrics['dp']) }}</div>
                                </div>
                                <div class="stage-box box-green" style="flex:1;">
                                    <div class="stage-box-title">Lunas</div>
                                    <div class="stage-box-val">{{ number_format($metrics['lunas']) }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- KANAN: Funnel --}}
                        <div class="funnel-container">
                            <div class="funnel-step f-leads">
                                <div class="funnel-title">Leads</div>
                                <div class="funnel-val">{{ number_format($metrics['leads']) }}</div>
                            </div>
                            <div class="funnel-step f-hp">
                                <div class="funnel-title">Hot Prospect</div>
                                <div class="funnel-val">{{ number_format($metrics['hot_prospect']) }}</div>
                            </div>
                            <div class="funnel-step f-deal">
                                <div class="funnel-title">Deal</div>
                                <div class="funnel-val">{{ number_format($metrics['deal']) }}</div>
                            </div>
                        </div>

                    </div>

                    {{-- DATA TABLE --}}
                    <div class="leads-table-card">
                        <div class="leads-table-head">Leads Kemitraan Summary</div>
                        <div class="leads-table-wrap">
                            <table class="leads-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Leads</th>
                                        <th>FU 1</th>
                                        <th>FU 2</th>
                                        <th>FU 3</th>
                                        <th>Input Data</th>
                                        <th>Rejected</th>
                                        <th>Hot Prospect</th>
                                        <th>DP</th>
                                        <th>Lunas</th>
                                        <th>Deal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Monitoring Leads</td>
                                        <td class="leads-pink">{{ number_format($metrics['leads']) }}</td>
                                        <td class="leads-pink">{{ number_format($metrics['fu1']) }}</td>
                                        <td class="leads-pink">{{ number_format($metrics['fu2']) }}</td>
                                        <td class="leads-pink">{{ number_format($metrics['fu3']) }}</td>
                                        <td class="leads-blue">{{ number_format($metrics['input_data']) }}</td>
                                        <td class="leads-red">{{ number_format($metrics['rejected']) }}</td>
                                        <td class="leads-blue">{{ number_format($metrics['hot_prospect']) }}</td>
                                        <td class="leads-green">{{ number_format($metrics['dp']) }}</td>
                                        <td class="leads-green">{{ number_format($metrics['lunas']) }}</td>
                                        <td class="leads-green">{{ number_format($metrics['deal']) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="text-align: center; color: #7286a4; font-size: 11px; margin-top: 40px;">
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
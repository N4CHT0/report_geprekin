{{-- resources/views/Investor/DashboardTimelineRecruitment.blade.php --}}
@include('Temp.internal.header_internal')

<style>
    :root {
        --lab-bg: #07101d;
        --lab-bg2: #0a1526;
        --lab-panel: #0b1830;
        --lab-line: rgba(148, 163, 184, .14);
        --lab-text: #e5eefc;
        --lab-muted: #93a4bd;
        --lab-shadow: 0 16px 40px rgba(0, 0, 0, .35);
        --rec-blue: #3b82f6;
    }

    .lab-page {
        width: 100%;
        min-height: 100vh;
        padding: 20px;
        background:
            radial-gradient(circle at top left, rgba(59, 130, 246, .12), transparent 22%),
            radial-gradient(circle at top right, rgba(6, 182, 212, .10), transparent 20%),
            linear-gradient(180deg, #07101d 0%, #0a1526 100%);
        color: var(--lab-text);
        font-family: 'Inter', sans-serif;
    }

    .lab-board {
        border-radius: 26px;
        padding: 18px;
        background: linear-gradient(180deg, rgba(10, 21, 38, .98), rgba(6, 12, 23, .99));
        border: 1px solid var(--lab-line);
        box-shadow: var(--lab-shadow);
    }

    .lab-topbar {
        display: grid;
        grid-template-columns: 240px minmax(0, 1fr) 360px;
        gap: 18px;
        align-items: center;
        margin-bottom: 24px;
    }

    .lab-brand-pill {
        display: inline-flex;
        align-items: center;
        padding: 12px 16px;
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .04), rgba(255, 255, 255, .02));
        border: 1px solid var(--lab-line);
    }
    .lab-brand-pill img { max-height: 52px; width: auto; }

    .lab-header-box { text-align: center; }
    .lab-header-label { color: #b9c8dc; font-size: 13px; font-weight: 800; letter-spacing: .1em; margin-bottom: 8px; }
    .lab-header-title { color: #f8fbff; font-size: clamp(20px, 2.2vw, 26px); font-weight: 900; letter-spacing: -.02em; }

    /* CSS MULTI SELECT DROPDOWN */
    .lab-filter-box form { display: flex; justify-content: flex-end; gap: 12px; }
    .multi-dropdown { position: relative; display: inline-block; }
    .multi-drop-btn { background: rgba(255, 255, 255, .05); border: 1px solid var(--lab-line); padding: 10px 16px; border-radius: 12px; cursor: pointer; color: #e5eefc; font-weight: 600; min-width: 140px; text-align: left; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    .multi-drop-content { display: none; position: absolute; background-color: #0f172a; min-width: 100%; box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.5); z-index: 100; border-radius: 12px; max-height: 250px; overflow-y: auto; margin-top: 8px; border: 1px solid var(--lab-line); }
    .multi-drop-content.show { display: block; }
    .multi-drop-content label { color: #cbd5e1; padding: 10px 16px; display: flex; align-items: center; cursor: pointer; border-bottom: 1px solid rgba(255, 255, 255, .05); margin: 0; font-size: 13px; }
    .multi-drop-content label:hover { background-color: rgba(255, 255, 255, .05); color: #fff; }
    .multi-drop-content input[type="checkbox"] { margin-right: 12px; transform: scale(1.2); accent-color: var(--rec-blue); }
    .badge-count { background: var(--rec-blue); color: #fff; padding: 2px 6px; border-radius: 6px; font-size: 11px; margin-left: 8px; }

    /* TABEL STYLE */
    .table-container {
        background: linear-gradient(180deg, rgba(17, 24, 39, .92), rgba(8, 13, 24, .96));
        border: 1px solid var(--lab-line);
        border-radius: 20px;
        box-shadow: var(--lab-shadow);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .table-head-title {
        padding: 16px;
        font-size: 16px;
        font-weight: 900;
        color: #fff;
        letter-spacing: 2px;
        background: rgba(255, 255, 255, .02);
    }

    .lab-table-wrap {
        overflow-x: auto;
        overflow-y: auto;
        max-height: 420px;
    }

    .lab-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
        text-align: center;
    }

    .lab-table th {
        color: #f8fafc;
        font-size: 11px;
        padding: 12px 10px;
        font-weight: 800;
        text-transform: uppercase;
        border-right: 1px solid rgba(255, 255, 255, .1);
        border-bottom: 1px solid rgba(255, 255, 255, .1);
    }
    
    /* Header Recruitment: Hijau Gelap */
    .th-recruitment { background: #065f46; } 
    
    /* Header Training: Biru Gelap */
    .th-training { background: #1e3a8a; }

    .lab-table td {
        font-size: 12px;
        padding: 10px;
        border-bottom: 1px solid rgba(148, 163, 184, .10);
        border-right: 1px solid rgba(148, 163, 184, .05);
        color: var(--lab-text);
        font-weight: 600;
        white-space: nowrap;
    }

    .lab-table tr:nth-child(even) td { background: rgba(255, 255, 255, .02); }

    .td-date { color: #93c5fd !important; font-weight: 700 !important; }

    @media (max-width: 1100px) {
        .lab-topbar { grid-template-columns: 1fr; text-align: center; }
        .lab-filter-box form { justify-content: center; }
    }
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
                            <div class="lab-header-title">TIMELINE RECRUITMENT & TRAINING</div>
                        </div>

                        <div class="lab-filter-box">
                            <form method="GET" action="{{ route('investor.sales.dashboardBOD.timelineRecruitment') }}" id="filterForm">
                                
                                {{-- Filter Tahun --}}
                                <div class="multi-dropdown">
                                    <div class="multi-drop-btn" onclick="toggleDropdown('dropTahun')">
                                        <div>Tahun <span class="badge-count">{{ count($filters['tahun'] ?? []) }}</span></div>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
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

                                {{-- Filter Bulan --}}
                                <div class="multi-dropdown">
                                    <div class="multi-drop-btn" onclick="toggleDropdown('dropBulan')">
                                        <div>Bulan <span class="badge-count">{{ count($filters['bulan'] ?? []) }}</span></div>
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
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
                    </div>

                    {{-- TABEL 1: TIMELINE RECRUITMENT --}}
                    <div class="table-container">
                        <div class="table-head-title" style="border-bottom: 3px solid #059669;">TIMELINE RECRUITMENT</div>
                        <div class="lab-table-wrap">
                            <table class="lab-table">
                                <thead>
                                    <tr>
                                        <th class="th-recruitment" style="text-align: left; position: sticky; left:0; z-index:2;">Bulan</th>
                                        <th class="th-recruitment">Jumlah Outlet GO</th>
                                        <th class="th-recruitment">Keb_AM</th>
                                        <th class="th-recruitment">Recruitment AM</th>
                                        <th class="th-recruitment">Keb_SPV</th>
                                        <th class="th-recruitment">Recruitment SPV</th>
                                        <th class="th-recruitment">Keb_Leader</th>
                                        <th class="th-recruitment">Recruitment Leader</th>
                                        <th class="th-recruitment">Keb_Crew</th>
                                        <th class="th-recruitment">Recruitment Staff</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tableData as $row)
                                    <tr>
                                        <td style="text-align: left; position: sticky; left:0; background: #0f172a; z-index:1; font-weight:800;">
                                            {{ $row['bulan_nama'] }} {{ $row['tahun'] }}
                                        </td>
                                        <td>{{ $row['jumlah_outlet_go'] }}</td>
                                        <td>{{ $row['keb_am'] }}</td>
                                        <td class="td-date">{{ $row['rec_am'] }}</td>
                                        <td>{{ $row['keb_spv'] }}</td>
                                        <td class="td-date">{{ $row['rec_spv'] }}</td>
                                        <td>{{ $row['keb_leader'] }}</td>
                                        <td class="td-date">{{ $row['rec_leader'] }}</td>
                                        <td>{{ $row['keb_crew'] }}</td>
                                        <td class="td-date">{{ $row['rec_staff'] }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" style="padding: 20px;">Tidak ada data ditemukan untuk filter ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TABEL 2: TIMELINE TRAINING --}}
                    <div class="table-container">
                        <div class="table-head-title" style="border-bottom: 3px solid #2563eb;">TIMELINE TRAINING</div>
                        <div class="lab-table-wrap">
                            <table class="lab-table">
                                <thead>
                                    <tr>
                                        <th class="th-training" style="text-align: left; position: sticky; left:0; z-index:2;">Bulan</th>
                                        <th class="th-training">Jumlah Outlet GO</th>
                                        <th class="th-training">Keb_AM</th>
                                        <th class="th-training">Training AM</th>
                                        <th class="th-training">Keb_SPV</th>
                                        <th class="th-training">Training SPV</th>
                                        <th class="th-training">Keb_Leader</th>
                                        <th class="th-training">Training Leader</th>
                                        <th class="th-training">Keb_Crew</th>
                                        <th class="th-training">Training Staff</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tableData as $row)
                                    <tr>
                                        <td style="text-align: left; position: sticky; left:0; background: #0f172a; z-index:1; font-weight:800;">
                                            {{ $row['bulan_nama'] }} {{ $row['tahun'] }}
                                        </td>
                                        <td>{{ $row['jumlah_outlet_go'] }}</td>
                                        <td>{{ $row['keb_am'] }}</td>
                                        <td class="td-date" style="color:#a7f3d0 !important;">{{ $row['tr_am'] }}</td>
                                        <td>{{ $row['keb_spv'] }}</td>
                                        <td class="td-date" style="color:#a7f3d0 !important;">{{ $row['tr_spv'] }}</td>
                                        <td>{{ $row['keb_leader'] }}</td>
                                        <td class="td-date" style="color:#a7f3d0 !important;">{{ $row['tr_leader'] }}</td>
                                        <td>{{ $row['keb_crew'] }}</td>
                                        <td class="td-date" style="color:#a7f3d0 !important;">{{ $row['tr_staff'] }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" style="padding: 20px;">Tidak ada data ditemukan untuk filter ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div style="text-align: center; color: #7286a4; font-size: 11px;">
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
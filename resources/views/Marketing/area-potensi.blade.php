@include('Temp.Investor.header')
@include('Temp.Investor.partials.sidebar')

<style>
    :root {
        --mi-primary: #3b82f6; 
        --mi-primary-light: #eff6ff;
        --mi-dark: #0f172a;
        --mi-gray: #64748b;
        --mi-light: #f8fafc;
        --mi-accent: #8b5cf6;
        --mi-success: #10b981;
        --mi-warning: #f59e0b;
        --mi-danger: #ef4444;
    }
    .menu-page {
        padding: 0;
        background: #f1f5f9;
        height: 100vh;
        font-family: 'Inter', system-ui, sans-serif;
        display: flex;
        flex-direction: column;
    }
    .map-container {
        flex: 1;
        position: relative;
        display: flex;
    }
    #territoryMap {
        flex: 1;
        height: 100%;
        background: #e2e8f0;
    }
    .side-panel {
        width: 400px;
        background: white;
        height: 100%;
        box-shadow: -4px 0 15px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        z-index: 10;
    }
    .panel-header {
        background: linear-gradient(135deg, var(--mi-dark), #1e293b);
        color: white;
        padding: 20px;
    }
    .panel-header h2 {
        font-size: 18px;
        font-weight: 800;
        margin: 0 0 5px 0;
    }
    .panel-header p {
        font-size: 12px;
        opacity: 0.8;
        margin: 0;
    }
    .panel-content {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }
    .info-box {
        background: var(--mi-light);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .info-box h4 {
        font-size: 14px;
        font-weight: 800;
        color: var(--mi-dark);
        margin: 0 0 10px 0;
        text-transform: uppercase;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--mi-gray);
        margin-bottom: 5px;
    }
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 13px;
        font-family: inherit;
        outline: none;
    }
    .form-control:focus {
        border-color: var(--mi-primary);
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .btn-mi {
        display: block;
        width: 100%;
        padding: 10px;
        background: var(--mi-primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-mi:hover {
        background: #2563eb;
    }
    .btn-mi-success {
        background: var(--mi-success);
    }
    .btn-mi-success:hover {
        background: #059669;
    }
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 15px;
    }
    .stat-box {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
    }
    .stat-box small {
        display: block;
        font-size: 10px;
        font-weight: 800;
        color: var(--mi-gray);
        text-transform: uppercase;
    }
    .stat-box span {
        display: block;
        font-size: 18px;
        font-weight: 900;
        color: var(--mi-dark);
        margin-top: 5px;
    }
    .map-controls {
        position: absolute;
        top: 20px;
        left: 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        padding: 15px;
        z-index: 5;
        width: 300px;
    }
</style>

<div class="menu-page">
    <div class="map-container">
        <!-- Floating Map Controls -->
        <div class="map-controls">
            <h3 style="font-size: 14px; font-weight: 800; margin: 0 0 10px 0;"><i class="bi bi-sliders"></i> Master Controls</h3>
            
            <style>
                details summary::-webkit-details-marker { display:none; }
                details summary { list-style: none; }
            </style>

            <!-- ACCORDION 1: Filter & Radar -->
            <details open style="margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                <summary style="padding: 8px 10px; font-size: 12px; font-weight: 700; cursor: pointer; outline: none;"><i class="bi bi-funnel"></i> Radar & Filter Outlet</summary>
                <div style="padding: 10px; border-top: 1px solid #e2e8f0; background: white; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 600; cursor: pointer; color: #b91c1c;">
                        <input type="checkbox" id="toggleOutlets" onchange="filterOutletMarkers()">
                        Tampilkan Seluruh Outlet Nasional
                    </label>
                    <div style="margin-left: 20px; display: flex; flex-direction: column; gap: 5px;">
                        <input type="text" id="filterOutletName" placeholder="&#128269; Cari spesifik nama outlet..." oninput="filterOutletMarkers()" style="width: 100%; margin-bottom: 2px; padding: 4px 8px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 10px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="filterBuka" checked onchange="filterOutletMarkers()">
                            <span style="color: #1d4ed8;">Aktif (Buka/Existing)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="filterTutup" checked onchange="filterOutletMarkers()">
                            <span style="color: #b91c1c;">Tutup</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 500; cursor: pointer;">
                            <input type="checkbox" id="filterGo" checked onchange="filterOutletMarkers()">
                            <span style="color: #a21caf;">Grand Opening (GO)</span>
                        </label>
                    </div>
                    <label style="display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" id="toggleRadius" checked onchange="filterOutletMarkers()">
                        Tampilkan Radius Proteksi (1.5km)
                    </label>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
                        <div style="font-size: 11px; font-weight: 700; color: #475569; margin-bottom: 5px; text-transform: uppercase;">Intelijen Lingkungan Bisnis</div>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; cursor: pointer; color: #b91c1c;">
                            <input type="checkbox" class="radar-env" value="direct" onchange="toggleEnvRadar()">
                            Kompetitor Langsung (Fried Chicken, Fast Food)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; cursor: pointer; color: #b45309; margin-top: 4px;">
                            <input type="checkbox" class="radar-env" value="fnb" onchange="toggleEnvRadar()">
                            F&B Ekosistem (Restoran, Cafe, Warkop)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; cursor: pointer; color: #1d4ed8; margin-top: 4px;">
                            <input type="checkbox" class="radar-env" value="traffic" onchange="toggleEnvRadar()">
                            Magnet Keramaian (Minimarket, Kampus, Pasar)
                        </label>
                    </div>
                </div>
            </details>

            <!-- ACCORDION 2: AI Predictive Engine -->
            <details style="margin-bottom: 8px; border: 1px solid #fde68a; border-radius: 8px; background: #fffbeb;">
                <summary style="padding: 8px 10px; font-size: 12px; font-weight: 700; color: #d97706; cursor: pointer; outline: none;"><i class="bi bi-robot"></i> AI Predictive Engine</summary>
                <div style="padding: 10px; border-top: 1px solid #fde68a; background: white; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                    <label style="display: flex; align-items: center; gap: 10px; font-size: 12px; font-weight: 800; cursor: pointer; color: #ea580c; margin-bottom: 10px;">
                        <input type="checkbox" id="toggleHeatmap" onchange="toggleHeatmapMode()">
                        <i class="bi bi-fire"></i> Mode Heatmap Pendapatan
                    </label>
                    
                    <h4 style="font-size: 10px; font-weight: 800; color: #d97706; margin: 0 0 8px 0; display:flex; align-items:center; gap:5px;"><i class="bi bi-sliders2"></i> Parameter Prediksi Omset</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div>
                            <small style="font-size:9px; color:#92400e; font-weight:700;">Base Omset (Rp)</small>
                            <input type="number" id="aiParamBase" value="150000000" class="form-control" style="font-size:11px; padding:4px 8px; height:24px;">
                        </div>
                        <div>
                            <small style="font-size:9px; color:#92400e; font-weight:700;">Bobot Kampus (%)</small>
                            <input type="number" id="aiParamEdu" value="3" class="form-control" style="font-size:11px; padding:4px 8px; height:24px;">
                        </div>
                        <div>
                            <small style="font-size:9px; color:#92400e; font-weight:700;">Bobot Mall (%)</small>
                            <input type="number" id="aiParamPub" value="5" class="form-control" style="font-size:11px; padding:4px 8px; height:24px;">
                        </div>
                        <div>
                            <small style="font-size:9px; color:#92400e; font-weight:700;">Kanibal Penalty (%)</small>
                            <input type="number" id="aiParamCannibal" value="50" class="form-control" style="font-size:11px; padding:4px 8px; height:24px;">
                        </div>
                    </div>
                </div>
            </details>

            <!-- ACCORDION 3: Statistik Nasional -->
            <details style="margin-bottom: 8px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc;">
                <summary style="padding: 8px 10px; font-size: 12px; font-weight: 700; cursor: pointer; outline: none;"><i class="bi bi-bar-chart"></i> Statistik & Target Nasional</summary>
                <div style="padding: 10px; border-top: 1px solid #e2e8f0; background: white; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 10px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px; border-radius: 6px;">
                            <small style="display: block; font-size: 9px; color: var(--mi-gray); font-weight: 800;">TOTAL EXISTING</small>
                            <strong id="statTotal" style="font-size: 14px; color: var(--mi-dark);">0</strong>
                        </div>
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 6px; border-radius: 6px;">
                            <small style="display: block; font-size: 9px; color: #3b82f6; font-weight: 800;">OUTLET AKTIF</small>
                            <strong id="statBuka" style="font-size: 14px; color: #1d4ed8;">0</strong>
                        </div>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 6px; border-radius: 6px;">
                            <small style="display: block; font-size: 9px; color: #ef4444; font-weight: 800;">OUTLET TUTUP</small>
                            <strong id="statTutup" style="font-size: 14px; color: #b91c1c;">0</strong>
                        </div>
                        <div style="background: #fdf4ff; border: 1px solid #fbcfe8; padding: 6px; border-radius: 6px;">
                            <small style="display: block; font-size: 9px; color: #d946ef; font-weight: 800;">OUTLET GO</small>
                            <strong id="statGo" style="font-size: 14px; color: #a21caf;">0</strong>
                        </div>
                    </div>
                    
                    <hr style="border-color: #f1f5f9; margin: 8px 0;">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 6px; border-radius: 6px;">
                            <small style="display: block; font-size: 9px; color: #16a34a; font-weight: 800;">TARGET SEHAT</small>
                            <strong style="font-size: 14px; color: #15803d;">{{ number_format($tgtSehatNasional ?? 0, 0, ',', '.') }}</strong>
                        </div>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; padding: 6px; border-radius: 6px;">
                            <small style="display: block; font-size: 9px; color: #dc2626; font-weight: 800;">TARGET AGRESIF</small>
                            <strong style="font-size: 14px; color: #b91c1c;">{{ number_format($tgtAgresifNasional ?? 0, 0, ',', '.') }}</strong>
                        </div>
                        <div style="grid-column: span 2; background: #fffbeb; border: 1px solid #fde68a; padding: 6px; border-radius: 6px;">
                            <small style="display: block; font-size: 9px; color: #d97706; font-weight: 800;">TOTAL PIN POTENSI (LEAD)</small>
                            <strong style="font-size: 14px; color: #b45309;">{{ number_format($totalPins ?? 0, 0, ',', '.') }} Titik</strong>
                        </div>
                    </div>
                </div>
            </details>

            <hr style="border-color: #e2e8f0; margin: 15px 0;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <input type="file" id="inpExcel" accept=".xlsx, .xls, .csv" style="display: none;" onchange="handleExcelUpload(event)">
                <button class="btn-mi btn-mi-success" style="padding: 8px; font-size: 12px; flex: 1;" onclick="document.getElementById('inpExcel').click()">
                    <i class="bi bi-file-earmark-excel"></i> Import Excel Target
                </button>
                <a href="#" style="font-size: 11px; color: var(--mi-primary); text-decoration: underline;">Format?</a>
            </div>

            <hr style="border-color: #e2e8f0; margin: 15px 0;">
            <p style="font-size: 11px; color: var(--mi-gray); margin: 0;">
                <i class="bi bi-info-circle"></i> Klik sembarang tempat di peta untuk menaruh PIN Potensi dan menganalisa area tersebut.
            </p>
        </div>


        <!-- The Map -->
        <div id="territoryMap"></div>

        <!-- Side Panel -->
        <div class="side-panel">
            <div class="panel-header">
                <h2>Intelijen Teritori</h2>
                <p>Analisis Kecamatan & Penugasan Surveyor</p>
            </div>
            <div class="panel-content" style="position: relative; overflow-x: hidden;">
                
                <div id="welcomeMessage" style="text-align: center; padding: 20px; color: var(--mi-gray); display: none;">
                    <i class="bi bi-geo-alt" style="font-size: 40px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                    <p style="font-size: 14px; line-height: 1.5;">Pilih atau drop PIN di peta untuk melihat target dan potensi area kecamatan.</p>
                </div>

                <!-- DIPINDAHKAN KE SINI: Priority List Sidebar -->
                <div id="priorityPanelContainer" style="display: block;">
                    <div style="display: flex; gap: 5px; margin-bottom: 10px;">
                        <button id="tabPriority" onclick="switchTab('priority')" style="flex:1; padding: 6px; font-size: 11px; font-weight: 700; border: none; border-radius: 6px; background: var(--mi-primary); color: white; cursor: pointer;">Area Prioritas</button>
                        <button id="tabUnmapped" onclick="switchTab('unmapped')" style="flex:1; padding: 6px; font-size: 11px; font-weight: 700; border: 1px solid #e2e8f0; border-radius: 6px; background: white; color: var(--mi-dark); cursor: pointer;">Belum di-Map</button>
                        <button id="tabQueue" onclick="switchTab('queue')" style="flex:1; padding: 6px; font-size: 11px; font-weight: 700; border: 1px solid #e2e8f0; border-radius: 6px; background: white; color: var(--mi-dark); cursor: pointer;">Antrean Surveyor</button>
                    </div>
                    
                    <div id="filterBlock" style="margin-bottom: 10px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 10px; font-weight: 800; color: var(--mi-gray); margin-bottom: 5px;"><i class="bi bi-funnel"></i> FILTER PENCARIAN</div>
                        <input type="text" id="filterProvinsi" list="listProv" placeholder="Semua Provinsi..." oninput="applyFilters()" style="width: 100%; margin-bottom: 5px; padding: 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 11px;">
                        <datalist id="listProv"></datalist>
                        
                        <input type="text" id="filterKota" list="listKota" placeholder="Semua Kota/Kab..." oninput="applyFilters()" style="width: 100%; margin-bottom: 5px; padding: 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 11px;">
                        <datalist id="listKota"></datalist>
                        
                        <input type="text" id="filterKecamatan" list="listKec" placeholder="Semua Kecamatan / Nama Outlet..." oninput="applyFilters()" style="width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #cbd5e1; font-size: 11px;">
                        <datalist id="listKec"></datalist>
                    </div>
                    
                    <div id="contentPriority">
                        <p style="font-size: 11px; color: var(--mi-gray); margin-top: -5px; margin-bottom: 10px;">Kecamatan dengan target belum tercapai.</p>
                        <div id="priorityList" style="display: flex; flex-direction: column; gap: 8px;">
                            <div style="text-align:center; padding: 10px; font-size:12px; color:var(--mi-gray);"><i class="bi bi-hourglass-split"></i> Memuat data...</div>
                        </div>
                    </div>
                    
                    <div id="contentUnmapped" style="display: none;">
                        <p style="font-size: 11px; color: var(--mi-gray); margin-top: -5px; margin-bottom: 10px;">Klik untuk mereview dan menyimpan titik GPS-nya.</p>
                        <div id="unmappedList" style="display: flex; flex-direction: column; gap: 8px;">
                            <div style="text-align:center; padding: 10px; font-size:12px; color:var(--mi-gray);"><i class="bi bi-hourglass-split"></i> Memuat data...</div>
                        </div>
                    </div>

                    <div id="contentQueue" style="display: none;">
                        <p style="font-size: 11px; color: var(--mi-gray); margin-top: -5px; margin-bottom: 10px;">Daftar tugas prospek lokasi untuk surveyor.</p>
                        <div id="queueList" style="display: flex; flex-direction: column; gap: 8px;">
                            <div style="text-align:center; padding: 10px; font-size:12px; color:var(--mi-gray);"><i class="bi bi-hourglass-split"></i> Memuat antrean...</div>
                        </div>
                    </div>
                </div>

                <div id="areaDetails" style="display: none;">
                    <button class="btn-mi" onclick="closePinDetails()" style="margin-bottom: 15px; width: 100%; background: #f8fafc; color: var(--mi-dark); border: 1px solid #e2e8f0; padding: 8px; font-size: 12px; font-weight: bold; border-radius: 8px;">
                        <i class="bi bi-arrow-left"></i> Kembali ke Antrean & Area
                    </button>
                    <!-- Lokasi Pin -->
                    <div class="info-box">
                        <h4>Lokasi Terpilih</h4>
                        <div style="font-size: 13px; color: var(--mi-dark); margin-bottom: 10px;">
                            <strong id="lblAddress">Memuat alamat...</strong>
                        </div>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <input type="text" id="lblLat" class="form-control" readonly style="background: #f8fafc; font-family: monospace; font-size: 11px;">
                            <input type="text" id="lblLng" class="form-control" readonly style="background: #f8fafc; font-family: monospace; font-size: 11px;">
                        </div>
                        <button class="btn-mi btn-mi-success" onclick="assignSurveyor()" id="btnAssign">
                            <i class="bi bi-send-check"></i> Jadikan Target Survey (Pin)
                        </button>
                        <button class="btn-mi" style="background:#0f172a; color:white; margin-top:8px; border:none;" onclick="openSiteScoreForm()" id="btnSiteScore">
                            <i class="bi bi-ui-checks"></i> Form Site Score
                        </button>
                    </div>

                    <!-- Location Intelligence (SIG) -->
                    <div class="info-box" id="sigPanel" style="display:none;">
                        <h4 style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                            <span><i class="bi bi-radar"></i> Location Intelligence</span>
                            <span id="sigScoreBadge" style="background:#3b82f6; color:white; padding:2px 8px; border-radius:12px; font-size:12px; font-weight:bold;">0/100</span>
                        </h4>
                        
                        <div style="font-size:11px; margin-bottom:10px; color:#64748b;">
                            Radius Pindai: 
                            <select id="sigRadius" onchange="if(currentPin) analyzeLocation(currentPin.getPosition())" style="padding:2px; font-size:10px; border-radius:4px; border:1px solid #cbd5e1;">
                                <option value="1000">1 KM</option>
                                <option value="1500" selected>1.5 KM</option>
                                <option value="2000">2 KM</option>
                            </select>
                        </div>

                        <div id="sigLoading" style="text-align:center; padding:15px; font-size:12px; color:#64748b;">
                            <i class="bi bi-arrow-repeat" style="display:inline-block; animation: spin 1s linear infinite;"></i> Menganalisa area spasial...
                        </div>

                        <div id="sigResults" style="display:none;">
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f1f5f9; padding:6px 0; font-size:11px;">
                                <span><i class="bi bi-book" style="color:#3b82f6"></i> Kampus & Sekolah</span>
                                <strong id="sigEdu">0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f1f5f9; padding:6px 0; font-size:11px;">
                                <span><i class="bi bi-shop" style="color:#eab308"></i> Pusat Belanja & Publik</span>
                                <strong id="sigPub">0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f1f5f9; padding:6px 0; font-size:11px;">
                                <span><i class="bi bi-exclamation-triangle" style="color:#ef4444"></i> Kompetitor Sekitar</span>
                                <strong id="sigComp" style="color:#ef4444">0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #f1f5f9; padding:6px 0; font-size:11px;">
                                <span><i class="bi bi-shield-x" style="color:#ef4444"></i> Risiko Kanibalisasi</span>
                                <strong id="sigCannibal">Aman</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:6px 0; font-size:11px;">
                                <span><i class="bi bi-truck" style="color:#8b5cf6"></i> Radius Logistik (DC)</span>
                                <strong id="sigLogistics" style="color:#8b5cf6">-</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-top:1px solid #f1f5f9; padding:8px 0 2px 0; font-size:11px; margin-top:5px;">
                                <span><i class="bi bi-robot" style="color:#d97706"></i> Estimasi Omset (AI)</span>
                                <strong id="sigPredict" style="color:#d97706">-</strong>
                            </div>
                            
                            <div style="margin-top:10px; font-size:10px; color:#64748b; line-height:1.4; background:#f8fafc; padding:8px; border-radius:6px;" id="sigInsight">
                                Insight...
                            </div>
                        </div>
                    </div>

                    <!-- Internal City Analytics (Cross-Dashboard) -->
                    <div class="info-box" id="cityInsightPanel" style="display:none; background: #fffbeb; border: 1px solid #fde68a;">
                        <h4 style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px; color:#b45309;">
                            <span><i class="bi bi-briefcase"></i> Internal City Analytics</span>
                            <span id="cityInsightKota" style="font-size:11px; font-weight:800;">-</span>
                        </h4>
                        <div id="cityInsightLoading" style="text-align:center; padding:10px; font-size:11px; color:#d97706;">
                            <i class="bi bi-arrow-repeat" style="display:inline-block; animation: spin 1s linear infinite;"></i> Menarik data Sales & Z-Zone...
                        </div>
                        <div id="cityInsightResults" style="display:none;">
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #fde68a; padding:6px 0; font-size:11px; color:#92400e;">
                                <span><i class="bi bi-shop"></i> Total Outlet (Existing)</span>
                                <strong id="ciTotalOutlet">0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:6px 0 2px 0; font-size:11px; color:#92400e;">
                                <span><i class="bi bi-cash-coin"></i> Rata-rata Omset / Bulan</span>
                                <strong id="ciAvgOmsetMonth">Rp0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #fde68a; padding:2px 0 6px 0; font-size:10px; color:#b45309;">
                                <span><i class="bi bi-calendar-day" style="visibility:hidden;"></i> Estimasi Harian</span>
                                <strong id="ciAvgOmsetDay">Rp0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-bottom:1px solid #fde68a; padding:6px 0; font-size:11px; color:#92400e;">
                                <span><i class="bi bi-trophy"></i> Top Performer (Z1)</span>
                                <strong id="ciTopPerformer">-</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:6px 0; font-size:11px; color:#92400e;">
                                <span><i class="bi bi-star"></i> Menu Terlaris Nasional</span>
                                <strong id="ciTopMenu">-</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Target Kecamatan Form -->
                    <div class="info-box">
                        <h4>Kuota & Target Kecamatan</h4>
                        
                        <div style="display: flex; gap: 10px;">
                            <div class="form-group" style="flex: 1;">
                                <label>Provinsi</label>
                                <input type="text" id="inpProvinsi" class="form-control" readonly>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Kota/Kab</label>
                                <input type="text" id="inpKota" class="form-control" readonly>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Kecamatan</label>
                            <input type="text" id="inpKecamatan" class="form-control" readonly>
                        </div>

                        <div class="stat-grid">
                            <div class="stat-box">
                                <small>Existing</small>
                                <input type="number" id="inpExisting" class="form-control" style="text-align: center; font-weight: 800; margin-top: 5px;" value="0">
                            </div>
                            <div class="stat-box">
                                <small>Tgt Sehat</small>
                                <input type="number" id="inpSehat" class="form-control" style="text-align: center; font-weight: 800; color: var(--mi-success); margin-top: 5px;" value="0">
                            </div>
                            <div class="stat-box">
                                <small>Tgt Agresif</small>
                                <input type="number" id="inpAgresif" class="form-control" style="text-align: center; font-weight: 800; color: var(--mi-danger); margin-top: 5px;" value="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Traffic Generator</label>
                            <textarea id="inpTraffic" class="form-control" rows="2" placeholder="contoh: kampus, kos, pekerja"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Zona Prioritas</label>
                            <textarea id="inpZona" class="form-control" rows="2" placeholder="contoh: jalan utama, dekat stasiun"></textarea>
                        </div>

                        <button class="btn-mi" onclick="saveAreaTarget()" id="btnSaveTarget">
                            <i class="bi bi-save"></i> Simpan Target Kecamatan
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- MarkerClusterer to prevent map panning lag with thousands of points -->
<script src="https://cdn.jsdelivr.net/npm/@googlemaps/markerclusterer@2.5.3/dist/index.min.js"></script>

<script>
let map;
let geocoder;
let placesService;
let markerCluster = null; 
let currentPin = null;
let currentCircle = null;
let globalInfoWindow = null;
let activeRenderTimeout = null;

let existingOutlets = [];
let outletMarkers = [];
let outletCircles = [];
let envMarkers = { direct: [], fnb: [], traffic: [] };
let warehouses = [];
let warehouseMarkers = [];
let areaTargets = [];
let targetBubbles = [];
let geocodeQueue = [];
let isGeocoding = false;
let reviewMarker = null;
let reviewInfoWindow = null;
let heatCircles = [];

function switchTab(tab) {
    let tabs = ['priority', 'unmapped', 'queue'];
    
    tabs.forEach(t => {
        let btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
        let content = document.getElementById('content' + t.charAt(0).toUpperCase() + t.slice(1));
        
        if (t === tab) {
            btn.style.background = 'var(--mi-primary)';
            btn.style.color = 'white';
            btn.style.border = 'none';
            content.style.display = 'block';
        } else {
            btn.style.background = 'white';
            btn.style.color = 'var(--mi-dark)';
            btn.style.border = '1px solid #e2e8f0';
            content.style.display = 'none';
        }
    });

    if (tab === 'queue') {
        loadQueuePins();
    }
}

function initTerritoryMap() {
    geocoder = new google.maps.Geocoder();
    
    // Default to Jakarta
    map = new google.maps.Map(document.getElementById("territoryMap"), {
        center: { lat: -6.200000, lng: 106.816666 },
        zoom: 12,
        styles: [
            { "featureType": "poi", "elementType": "labels", "stylers": [{ "visibility": "off" }] }
        ]
    });

    // Add idle listener for lazy-loading circles when panning/zooming
    map.addListener("idle", () => {
        if (typeof updateVisibleCircles === 'function') {
            updateVisibleCircles();
        }
    });

    placesService = new google.maps.places.PlacesService(map);

    // Map click event
    map.addListener("click", (e) => {
        placePotentialPin(e.latLng);
    });

    loadExistingOutlets();
    loadAllAreaTargets();
    loadWarehouses();
}

function loadExistingOutlets() {
    fetch('/api/marketing/outlets')
        .then(r => r.json())
        .then(data => {
            // Handle if data is wrapped in res.data or not
            let outlets = data.data ? data.data : data;
            existingOutlets = outlets;
            
            let countBuka = 0;
            let countTutup = 0;
            let countGo = 0;

            outlets.forEach(o => {
                const s = (o.status || '').toUpperCase();
                if(s.includes('BUKA') || s === 'OPEN' || s.includes('EXISTING')) countBuka++;
                else if(s.includes('TUTUP') || s === 'CLOSED') countTutup++;
                else if(s.includes('GO')) countGo++;
                
                // Draw marker but map: null initially
                drawSingleOutlet(o);
            });

            document.getElementById('statTotal').innerText = outlets.length;
            document.getElementById('statBuka').innerText = countBuka;
            document.getElementById('statTutup').innerText = countTutup;
            document.getElementById('statGo').innerText = countGo;

            document.getElementById('statTutup').innerText = countTutup;
            document.getElementById('statGo').innerText = countGo;

            // Run filter to apply markers in chunks
            filterOutletMarkers();
            
            if(typeof applyFilters === 'function') applyFilters();
        });
}

function loadWarehouses() {
    fetch('/api/marketing/warehouses')
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                warehouses = res.data;
                // Cek warehouse yang punya alamat tapi latitude/longitude kosong
                let needsGeocode = warehouses.filter(w => !w.latitude && w.alamat);
                let promises = needsGeocode.map(w => {
                    return new Promise((resolve) => {
                        geocoder.geocode({ address: w.alamat }, (results, status) => {
                            if (status === 'OK' && results[0]) {
                                w.latitude = results[0].geometry.location.lat();
                                w.longitude = results[0].geometry.location.lng();
                            }
                            resolve();
                        });
                    });
                });

                Promise.all(promises).then(() => {
                    drawWarehouses();
                });
            }
        });
}

function drawWarehouses() {
    clearArray(warehouseMarkers);
    warehouses.forEach(w => {
        if (!w.latitude || !w.longitude) return;
        const pos = { lat: parseFloat(w.latitude), lng: parseFloat(w.longitude) };
        const marker = new google.maps.Marker({
            position: pos,
            map: map,
            icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
            title: 'GUDANG: ' + w.nama_warehouse
        });
        const infoWindow = new google.maps.InfoWindow({
            content: `<div style="font-family:sans-serif;"><b><i class="bi bi-truck"></i> ${w.nama_warehouse}</b><br><small>${w.alamat || 'Tidak ada alamat'}</small></div>`
        });
        marker.addListener('click', () => infoWindow.open(map, marker));
        warehouseMarkers.push(marker);
    });
}

function renderUnmappedList(items) {
    const listEl = document.getElementById('unmappedList');
    
    if(items.length === 0) {
        listEl.innerHTML = '<div style="padding:10px; font-size:12px; color:#64748b;">Semua outlet sudah dipetakan!</div>';
        return;
    }
    
    // Use innerHTML instead of appendChild loop to prevent browser freeze
    let html = '';
    
    // Only render top 100 to prevent overwhelming the DOM
    const displayItems = items.slice(0, 100);
    
    displayItems.forEach(o => {
        html += `
            <div style="padding:10px; border-bottom:1px solid #e2e8f0; cursor:pointer;"
                 onmouseover="this.style.backgroundColor='#f8fafc'"
                 onmouseout="this.style.backgroundColor='transparent'"
                 onclick="startPinReviewById(${o.id})">
                <div style="font-size: 13px; font-weight: 700;">${o.nama_outlet}</div>
                <div style="font-size: 11px; color: #64748b;">Status: ${o.status || 'N/A'}</div>
                <div style="font-size: 10px; font-weight: 800; color: #eab308; margin-top: 5px;"><i class="bi bi-geo-alt"></i> PETAKAN SEKARANG</div>
            </div>
        `;
    });
    
    if (items.length > 100) {
        html += `<div style="padding:10px; text-align:center; font-size:11px; color:#64748b;">Menampilkan 100 dari ${items.length} outlet yang belum dipetakan. Gunakan filter untuk mencari.</div>`;
    }
    
    listEl.innerHTML = html;
}

function startPinReviewById(id) {
    const o = existingOutlets.find(x => x.id === id);
    if (o) startPinReview(o);
}

function startPinReview(o) {
    if (reviewMarker) reviewMarker.setMap(null);
    if (reviewInfoWindow) reviewInfoWindow.close();

    let queryStr = o.nama_outlet + ' GeprekInAja';
    if (o.area_kota || o.kota) queryStr += ' ' + (o.area_kota || o.kota);
    if (o.area_provinsi || o.provinsi) queryStr += ' ' + (o.area_provinsi || o.provinsi);

    const request = {
        query: queryStr,
        fields: ['name', 'geometry'],
        locationBias: map.getBounds() // Bias to current view to prevent throwing to another city
    };
    
    placesService.findPlaceFromQuery(request, function(results, status) {
        let guessPos;
        let isGuessed = false;

        if (status === google.maps.places.PlacesServiceStatus.OK && results && results.length > 0) {
            guessPos = results[0].geometry.location;
            isGuessed = true;
        } else {
            guessPos = map.getCenter();
        }

        map.panTo(guessPos);
        map.setZoom(15);

        reviewMarker = new google.maps.Marker({
            position: guessPos,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP
        });

        reviewInfoWindow = new google.maps.InfoWindow({
            content: `
                <div style="font-family:sans-serif; text-align:center;">
                    <div style="font-size:10px; font-weight:800; color:#d97706; margin-bottom:5px;">
                        ${isGuessed ? '✨ REKOMENDASI GOOGLE' : '📍 LOKASI MANUAL'}
                    </div>
                    <b>${o.nama_outlet}</b><br>
                    <small>Geser pin kuning ini ke lokasi yang benar!</small><br>
                    <button onclick="saveReviewPin(${o.id})" style="margin-top:10px; width:100%; padding: 6px; font-size: 11px; background: #22c55e; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight:bold;">
                        <i class="bi bi-check-circle"></i> Simpan Lokasi Ini
                    </button>
                </div>
            `
        });

        reviewInfoWindow.open(map, reviewMarker);

        reviewMarker.addListener('dragend', function() {
            reviewInfoWindow.open(map, reviewMarker);
        });
    });
}

function saveReviewPin(id) {
    const pos = reviewMarker.getPosition();
    fetch('/api/marketing/outlets/update-gps', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id: id,
            latitude: pos.lat(),
            longitude: pos.lng()
        })
    }).then(r => r.json()).then(res => {
        if(res.success) {
            Swal.fire({icon:'success', title:'Berhasil Dipetakan!', timer:1500, showConfirmButton:false});
            if (reviewMarker) reviewMarker.setMap(null);
            if (reviewInfoWindow) reviewInfoWindow.close();
            loadExistingOutlets();
        }
    });
}

function autoFixEmptyCoordinates() {
    const emptyOutlets = existingOutlets.filter(o => !o.latitude || !o.longitude);
    emptyOutlets.forEach(o => {
        let queryStr = o.nama_outlet + ' GeprekInAja';
        if (o.area_kota || o.kota) queryStr += ' ' + (o.area_kota || o.kota);
        if (o.area_provinsi || o.provinsi) queryStr += ' ' + (o.area_provinsi || o.provinsi);

        const request = {
            query: queryStr,
            fields: ['name', 'geometry'],
        };
        placesService.findPlaceFromQuery(request, function(results, status) {
            if (status === google.maps.places.PlacesServiceStatus.OK && results && results.length > 0) {
                const loc = results[0].geometry.location;
                // Save to DB
                fetch('/api/marketing/outlets/update-gps', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        id: o.id,
                        latitude: loc.lat(),
                        longitude: loc.lng()
                    })
                }).then(r => r.json()).then(res => {
                    if(res.success) {
                        o.latitude = loc.lat();
                        o.longitude = loc.lng();
                        drawSingleOutlet(o);
                    }
                });
            }
        });
    });
}

function drawSingleOutlet(o) {
    if(!o.latitude || !o.longitude) return;

    const pos = { lat: parseFloat(o.latitude), lng: parseFloat(o.longitude) };
    
    const isTutup = o.status && (o.status.toUpperCase() === 'TUTUP' || o.status.toUpperCase() === 'CLOSED');
    // Marker (map: null to avoid initial freeze)
    const marker = new google.maps.Marker({
        position: pos,
        map: null,
        icon: isTutup ? 'https://maps.google.com/mapfiles/ms/icons/red-dot.png' : 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
        title: o.nama_outlet
    });
    
    if (!globalInfoWindow) {
        globalInfoWindow = new google.maps.InfoWindow();
    }

    marker.addListener('click', () => {
        globalInfoWindow.setContent(`
            <div style="font-family:sans-serif;">
                <b>${o.nama_outlet}</b><br>
                Status: ${o.status}<br>
                <div style="margin-top: 8px;">
                    <button onclick="resetOutletGps(${o.id})" style="padding: 4px 8px; font-size: 10px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="bi bi-trash"></i> Reset Pin Salah
                    </button>
                </div>
            </div>
        `);
        globalInfoWindow.open(map, marker);
    });

    marker.outletData = o;
    // Track visibility
    marker.isFilterVisible = true; 
    outletMarkers.push(marker);

    // Instead of creating 4100 Circle objects upfront (which causes freeze),
    // we push null and create them lazily when they enter the viewport.
    outletCircles.push(null);
}

function triggerMarkerClick(id) {
    const marker = outletMarkers.find(m => m.outletData && m.outletData.id === id);
    if (marker) {
        if (globalInfoWindow) globalInfoWindow.close();
        map.panTo(marker.getPosition());
        map.setZoom(18); // Zoom in tightly
        
        // Render InfoWindow directly at coordinates instead of anchoring to marker.
        // This fixes the race condition where MarkerClusterer hasn't re-rendered the marker yet.
        const o = marker.outletData;
        globalInfoWindow.setContent(`
            <div style="font-family:sans-serif;">
                <b>${o.nama_outlet}</b><br>
                Status: ${o.status}<br>
                <div style="margin-top: 8px;">
                    <button onclick="resetOutletGps(${o.id})" style="padding: 4px 8px; font-size: 10px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        <i class="bi bi-trash"></i> Reset Pin Salah
                    </button>
                </div>
            </div>
        `);
        globalInfoWindow.setPosition(marker.getPosition());
        globalInfoWindow.open(map);
    }
}

function resetOutletGps(id) {
    if(!confirm("Hapus pin ini dari peta? (Sistem akan mengosongkan GPS-nya)")) return;
    fetch('/api/marketing/outlets/update-gps', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            id: id,
            latitude: null,
            longitude: null
        })
    }).then(r => r.json()).then(res => {
        if(res.success) {
            Swal.fire({icon:'success', title:'Dihapus', timer:1500, showConfirmButton:false});
            loadExistingOutlets(); // Refresh map
        }
    });
}

function drawOutlets() {
    clearArray(outletMarkers);
    clearArray(outletCircles);
    existingOutlets.forEach(o => drawSingleOutlet(o));
}

function toggleHeatmapMode() {
    const isHeatmapOn = document.getElementById('toggleHeatmap').checked;
    
    if (isHeatmapOn) {
        // Matikan marker biasa agar bersih
        document.getElementById('toggleOutlets').checked = false;
        filterOutletMarkers();
        
        if (heatCircles.length === 0) {
            existingOutlets.forEach(o => {
                if (o.latitude && o.longitude && o.omset > 0) {
                    let omsetM = parseFloat(o.omset) / 1000000;
                    
                    let color = '#3b82f6'; // Blue (Cold)
                    let rad = 400; // Radius pancaran
                    let opacity = 0.2;
                    
                    if (omsetM >= 100) {
                        color = '#ef4444'; // Red (Hot)
                        rad = 1200;
                        opacity = 0.45;
                    } else if (omsetM >= 50) {
                        color = '#f97316'; // Orange (Warm)
                        rad = 800;
                        opacity = 0.35;
                    } else if (omsetM >= 25) {
                        color = '#eab308'; // Yellow
                        rad = 600;
                        opacity = 0.3;
                    } else if (omsetM >= 10) {
                        color = '#22c55e'; // Green
                        rad = 500;
                        opacity = 0.25;
                    }

                    const circle = new google.maps.Circle({
                        strokeWeight: 0,
                        fillColor: color,
                        fillOpacity: opacity,
                        map: map,
                        center: new google.maps.LatLng(o.latitude, o.longitude),
                        radius: rad,
                        clickable: false
                    });
                    heatCircles.push(circle);
                }
            });
        } else {
            heatCircles.forEach(c => c.setMap(map));
        }
    } else {
        heatCircles.forEach(c => c.setMap(null));
        // Nyalakan marker biasa kembali
        document.getElementById('toggleOutlets').checked = true;
        filterOutletMarkers();
    }
}

function filterOutletMarkers() {
    const showBuka = document.getElementById('filterBuka').checked;
    const showTutup = document.getElementById('filterTutup').checked;
    const showGo = document.getElementById('filterGo').checked;
    const masterOn = document.getElementById('toggleOutlets').checked;
    const radOn = document.getElementById('toggleRadius').checked;
    
    const searchOutlet = (document.getElementById('filterOutletName').value || '').toLowerCase();
    
    // Parameter pencarian area dari panel kanan
    const p = (document.getElementById('filterProvinsi').value || '').toLowerCase();
    const k = (document.getElementById('filterKota').value || '').toLowerCase();
    const c = (document.getElementById('filterKecamatan').value || '').toLowerCase();
    const searchActive = p !== '' || k !== '' || c !== '' || searchOutlet !== '';
    
    // Jika user tidak mencentang Nasional dan tidak sedang melakukan pencarian (kosong), bersihkan peta
    if (!masterOn && !searchActive) {
        if (typeof markerClusterer !== 'undefined' && markerCluster) {
            markerCluster.clearMarkers();
        }
        outletMarkers.forEach(m => {
            m.isFilterVisible = false;
            m.setMap(null);
        });
        outletCircles.forEach(circle => { if (circle) circle.setMap(null); });
        return;
    }

    let bounds = new google.maps.LatLngBounds();
    let visibleCount = 0;
    let lastMarker = null;

    let visibleMarkers = [];
    
    let statTotal = 0;
    let statBuka = 0;
    let statTutup = 0;
    let statGo = 0;

    outletMarkers.forEach((m, idx) => {
        const o = m.outletData;
        const s = (o.status || '').toUpperCase();
        const oName = (o.nama_outlet || '').toLowerCase();
        
        let isBuka = s.includes('BUKA') || s === 'OPEN' || s.includes('EXISTING');
        let isTutup = s.includes('TUTUP') || s === 'CLOSED';
        let isGo = s.includes('GO');
        if (!isTutup && !isGo) isBuka = true; // Default as active if unknown
        
        // Geographical Match Check
        let matchGeo = true;
        if (masterOn) {
             matchGeo = true; // Nasional overrides all geographic filters
        } else if (searchActive && searchOutlet === '') {
             let areaProv = (o.area_provinsi || o.provinsi || '').toLowerCase();
             let areaKota = (o.area_kota || o.kota || '').toLowerCase();
             let areaKec = (o.area_kecamatan || '').toLowerCase();
             
             let matchP = p === '' || oName.includes(p) || (areaProv !== '' && (areaProv.includes(p) || p.includes(areaProv)));
             let matchK = k === '' || oName.includes(k) || (areaKota !== '' && (areaKota.includes(k) || k.includes(areaKota)));
             let matchC = c === '' || oName.includes(c) || (areaKec !== '' && (areaKec.includes(c) || c.includes(areaKec)));
             
             if (!(matchP && matchK && matchC)) matchGeo = false;
        } else if (searchOutlet !== '') {
             if (!oName.includes(searchOutlet)) matchGeo = false;
        }

        // Update Stats based on Geography (ignores showBuka/Tutup checkboxes so stats represent the area)
        if (matchGeo && (masterOn || searchActive)) {
            statTotal++;
            if (isTutup) statTutup++;
            else if (isGo) statGo++;
            else statBuka++;
        }
        
        let visible = false;
        if (isBuka && showBuka) visible = true;
        if (isTutup && showTutup) visible = true;
        if (isGo && showGo) visible = true;
        
        if (!matchGeo) visible = false;
        
        m.isFilterVisible = visible;
        
        if (visible) {
            visibleMarkers.push(m);
            if (searchOutlet !== '' || searchActive) {
                bounds.extend(m.getPosition());
                visibleCount++;
                lastMarker = m;
            }
        }
    });

    // Update DOM Stats
    document.getElementById('statTotal').innerText = statTotal;
    document.getElementById('statBuka').innerText = statBuka;
    document.getElementById('statTutup').innerText = statTutup;
    document.getElementById('statGo').innerText = statGo;
    
    // Use MarkerClusterer if available (prevents heavy lag during panning)
    if (typeof markerClusterer !== 'undefined') {
        if (!markerCluster) {
            // Make clustering less aggressive so users can see individual outlets easier
            const algorithm = new markerClusterer.SuperClusterAlgorithm({ maxZoom: 12, radius: 40 });
            
            const onClusterClick = (event, cluster, map) => {
                let content = `<div style="max-height: 250px; overflow-y: auto; font-family: sans-serif; padding: 5px; min-width: 200px;">`;
                content += `<b style="display:block; margin-bottom: 8px; color: #0f172a;">${cluster.markers.length} Outlet di Area Ini:</b>`;
                
                cluster.markers.forEach(m => {
                    const o = m.outletData;
                    if (!o) return;
                    content += `
                        <div style="padding: 8px; border-bottom: 1px solid #e2e8f0; cursor: pointer; transition: background 0.2s;" 
                             onmouseover="this.style.backgroundColor='#f1f5f9'" 
                             onmouseout="this.style.backgroundColor='transparent'"
                             onclick="triggerMarkerClick(${o.id})">
                            <b style="color: #0369a1; font-size: 13px;">${o.nama_outlet}</b><br>
                            <span style="font-size: 11px; color: #64748b;"><i class="bi bi-tag-fill"></i> ${o.status || 'Aktif'}</span>
                        </div>
                    `;
                });
                content += `</div>`;
                
                if (!globalInfoWindow) {
                    globalInfoWindow = new google.maps.InfoWindow();
                }
                
                globalInfoWindow.setPosition(cluster.position);
                globalInfoWindow.setContent(content);
                globalInfoWindow.open(map);
            };

            const renderer = {
                render: function(cluster, stats, map) {
                    if (cluster.count === 1) {
                        const m = cluster.markers[0];
                        m.setMap(map);
                        return m;
                    }
                    
                    const count = cluster.count;
                    const position = cluster.position;
                    const svg = window.btoa(`
                        <svg fill="#0ea5e9" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 240">
                            <circle cx="120" cy="120" opacity="0.9" r="100" />
                            <circle cx="120" cy="120" opacity="0.4" r="120" />
                        </svg>`);
                        
                    const clusterMarker = new google.maps.Marker({
                        position: position,
                        icon: {
                            url: `data:image/svg+xml;base64,${svg}`,
                            scaledSize: new google.maps.Size(45, 45)
                        },
                        label: {
                            text: String(count),
                            color: "white",
                            fontSize: "14px",
                            fontWeight: "bold"
                        },
                        zIndex: 1000 + count,
                    });
                    
                    clusterMarker.setMap(map);
                    return clusterMarker;
                }
            };

            markerCluster = new markerClusterer.MarkerClusterer({ 
                map: map, 
                markers: [], 
                algorithm: algorithm,
                renderer: renderer,
                onClusterClick: onClusterClick
            });
        }
        
        // Hide invisible markers manually
        outletMarkers.forEach(m => {
            if (!m.isFilterVisible && m.getMap()) m.setMap(null);
        });
        
        markerCluster.clearMarkers();
        markerCluster.addMarkers(visibleMarkers);
        
        // Trigger circles
        if (typeof updateVisibleCircles === 'function') updateVisibleCircles();
        
        // Auto pan if needed
        if (searchOutlet !== '') {
            if (visibleCount === 1 && lastMarker) {
                map.panTo(lastMarker.getPosition());
                map.setZoom(16);
            } else if (visibleCount > 1) {
                map.fitBounds(bounds);
            }
        }
    } else {
        // Fallback to chunked rendering if CDN fails (might lag during panning)
        if (activeRenderTimeout) clearTimeout(activeRenderTimeout);
        let renderIndex = 0;
        
        function renderChunk() {
            const chunkSize = 150;
            for(let i=0; i < chunkSize && renderIndex < outletMarkers.length; i++, renderIndex++) {
                let m = outletMarkers[renderIndex];
                if (m.isFilterVisible) {
                    if (!m.getMap()) m.setMap(map);
                } else {
                    if (m.getMap()) m.setMap(null);
                }
            }
            
            if(renderIndex < outletMarkers.length) {
                activeRenderTimeout = setTimeout(renderChunk, 10);
            } else {
                if (typeof updateVisibleCircles === 'function') updateVisibleCircles();
                
                if (searchOutlet !== '') {
                    if (visibleCount === 1 && lastMarker) {
                        map.panTo(lastMarker.getPosition());
                        map.setZoom(16);
                    } else if (visibleCount > 1) {
                        map.fitBounds(bounds);
                    }
                }
            }
        }
        
        renderChunk();
    }
}

function updateVisibleCircles() {
    const radOn = document.getElementById('toggleRadius').checked;
    
    if (!radOn || !map) {
        outletCircles.forEach(circle => { if (circle) circle.setMap(null); });
        return;
    }
    
    const bounds = map.getBounds();
    const zoom = map.getZoom();
    
    if (!bounds) return;
    
    // If zoom is too far out, hide all circles to save memory
    if (zoom < 11) {
        outletCircles.forEach(circle => { if (circle) circle.setMap(null); });
        return;
    }
    
    outletMarkers.forEach((m, idx) => {
        if (!m.isFilterVisible) {
            if (outletCircles[idx] && outletCircles[idx].getMap()) {
                outletCircles[idx].setMap(null);
            }
            return;
        }
        
        if (bounds.contains(m.getPosition())) {
            // Lazy instantiate circle if it doesn't exist yet
            if (!outletCircles[idx]) {
                const isTutup = m.outletData && m.outletData.status && (m.outletData.status.toUpperCase() === 'TUTUP' || m.outletData.status.toUpperCase() === 'CLOSED');
                const color = isTutup ? "#ef4444" : "#22c55e";
                outletCircles[idx] = new google.maps.Circle({
                    strokeColor: color,
                    strokeOpacity: 0.5,
                    strokeWeight: 1.5,
                    fillColor: color,
                    fillOpacity: 0.0, // Set to 0 to prevent solid block when hundreds of outlets overlap
                    map: null,
                    center: m.getPosition(),
                    radius: 1500,
                    clickable: false
                });
            }
            if (!outletCircles[idx].getMap()) outletCircles[idx].setMap(map);
        } else {
            if (outletCircles[idx] && outletCircles[idx].getMap()) {
                outletCircles[idx].setMap(null);
            }
        }
    });
}

// toggleMarkers and toggleRadius have been replaced by filterOutletMarkers

function closePinDetails() {
    if (currentPin) currentPin.setMap(null);
    if (currentCircle) currentCircle.setMap(null);
    if (typeof reviewMarker !== 'undefined' && reviewMarker) reviewMarker.setMap(null);
    if (typeof reviewInfoWindow !== 'undefined' && reviewInfoWindow) reviewInfoWindow.close();
    
    document.getElementById('areaDetails').style.display = 'none';
    document.getElementById('priorityPanelContainer').style.display = 'block';
}

function toggleEnvRadar() {
    const centerPos = map.getCenter();
    
    document.querySelectorAll('.radar-env').forEach(cb => {
        const cat = cb.value;
        if (cb.checked) {
            if (envMarkers[cat].length === 0) {
                searchEnvRadar(cat, centerPos);
            } else {
                envMarkers[cat].forEach(m => m.setMap(map));
            }
        } else {
            envMarkers[cat].forEach(m => m.setMap(null));
        }
    });
}

function searchEnvRadar(cat, centerPos) {
    let requests = [];
    let color = '';
    let title = '';
    
    if (cat === 'direct') {
        requests = [
            {keyword: 'ayam geprek'}, {keyword: 'fried chicken'}, 
            {keyword: 'ayam goreng'}, {keyword: 'ayam bakar'}, 
            {keyword: 'fast food'}, {keyword: 'kfc'}, 
            {keyword: 'mcdonalds'}, {keyword: 'mie gacoan'}
        ];
        color = '#ef4444'; // Red
        title = 'Kompetitor Langsung';
    } else if (cat === 'fnb') {
        requests = [
            {type: 'restaurant'}, {type: 'cafe'}, 
            {keyword: 'warung makan'}, {keyword: 'warkop'}, 
            {keyword: 'kedai kopi'}, {keyword: 'bakso'}, 
            {keyword: 'mie ayam'}, {keyword: 'street food'}
        ];
        color = '#f59e0b'; // Amber
        title = 'F&B Ekosistem';
    } else if (cat === 'traffic') {
        requests = [
            {type: 'convenience_store'}, {keyword: 'indomaret'}, 
            {keyword: 'alfamart'}, {type: 'school'}, 
            {type: 'university'}, {type: 'shopping_mall'}, 
            {keyword: 'pasar'}, {keyword: 'pabrik'}, 
            {keyword: 'rumah sakit'}
        ];
        color = '#3b82f6'; // Blue
        title = 'Magnet Keramaian';
    }
    
    requests.forEach((req, index) => {
        const fullRequest = {
            location: centerPos,
            radius: '3000', // 3km radius to ensure adequate data points
            ...req
        };
        
        // Stagger requests to avoid Google Maps OVER_QUERY_LIMIT (250ms delay per request)
        setTimeout(() => {
            placesService.nearbySearch(fullRequest, (results, status) => {
                if (status === google.maps.places.PlacesServiceStatus.OK && results) {
                results.forEach(place => {
                    // Ignore our own brand
                    if(!place.name.toLowerCase().includes('geprekinaja')) {
                        const marker = new google.maps.Marker({
                            position: place.geometry.location,
                            map: map,
                            icon: {
                                path: google.maps.SymbolPath.CIRCLE,
                                fillColor: color,
                                fillOpacity: 0.9,
                                strokeColor: '#ffffff',
                                strokeWeight: 2,
                                scale: 6
                            },
                            title: `[${title}] ${place.name}`
                        });
                        envMarkers[cat].push(marker);
                    }
                });
            }
        });
        }, index * 300);
    });
}

function placePotentialPin(latLng) {
    if (currentPin) {
        currentPin.setMap(null);
    }
    if (currentCircle) {
        currentCircle.setMap(null);
    }

    currentPin = new google.maps.Marker({
        position: latLng,
        map: map,
        animation: google.maps.Animation.DROP
    });

    // 1.5km potential reach
    currentCircle = new google.maps.Circle({
        strokeColor: "#3b82f6",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#3b82f6",
        fillOpacity: 0.15,
        map: map,
        center: latLng,
        radius: 1500
    });

    document.getElementById('welcomeMessage').style.display = 'none';
    document.getElementById('priorityPanelContainer').style.display = 'none';
    document.getElementById('areaDetails').style.display = 'block';

    document.getElementById('lblLat').value = latLng.lat().toFixed(6);
    document.getElementById('lblLng').value = latLng.lng().toFixed(6);
    document.getElementById('lblAddress').innerText = 'Menganalisa koordinat...';

    // Cari apakah pin ini jatuh di dalam area target (radius 3km)
    let closestTarget = null;
    let minDistance = 3000; // max 3km

    if (typeof areaTargets !== 'undefined') {
        areaTargets.forEach(t => {
            if (t.latitude && t.longitude) {
                let dist = google.maps.geometry.spherical.computeDistanceBetween(latLng, {lat: parseFloat(t.latitude), lng: parseFloat(t.longitude)});
                if (dist < minDistance) {
                    minDistance = dist;
                    closestTarget = t;
                }
            }
        });
    }

    if (closestTarget) {
        // Pin jatuh di dalam gelembung! Gunakan data gelembung ini secara langsung
        document.getElementById('inpProvinsi').value = closestTarget.provinsi || '';
        document.getElementById('inpKota').value = closestTarget.kota || '';
        document.getElementById('inpKecamatan').value = closestTarget.kecamatan || '';
        
        currentAreaPotensiId = closestTarget.id;
        document.getElementById('inpExisting').value = closestTarget.existing_count || '0';
        document.getElementById('inpSehat').value = closestTarget.sehat_target || '0';
        document.getElementById('inpAgresif').value = closestTarget.agresif_target || '0';
        document.getElementById('inpTraffic').value = closestTarget.traffic_generator || '';
        document.getElementById('inpZona').value = closestTarget.zona_prioritas || '';
        
        // Fetch Internal City Insights
        if (closestTarget.kota) {
            fetchCityInsights(closestTarget.kota);
        }

        // Tetap jalankan Geocoder HANYA untuk mendapatkan alamat lengkap (nama jalan)
        geocoder.geocode({ location: latLng }, (results, status) => {
            if (status === "OK" && results[0]) {
                document.getElementById('lblAddress').innerText = results[0].formatted_address;
            } else {
                document.getElementById('lblAddress').innerText = 'Alamat tidak ditemukan';
            }
        });
    } else {
        // Pin jatuh di luar gelembung manapun, gunakan metode Reverse Geocode
        geocoder.geocode({ location: latLng }, (results, status) => {
            if (status === "OK" && results[0]) {
                document.getElementById('lblAddress').innerText = results[0].formatted_address;
                
                let kecamatan = '';
                let kota = '';
                let provinsi = '';

                results[0].address_components.forEach(comp => {
                    if (comp.types.includes("administrative_area_level_3") || comp.types.includes("locality")) {
                        kecamatan = comp.short_name.replace('Kecamatan ', '');
                    }
                    if (comp.types.includes("administrative_area_level_2")) {
                        kota = comp.short_name;
                    }
                    if (comp.types.includes("administrative_area_level_1")) {
                        provinsi = comp.short_name;
                    }
                });

                document.getElementById('inpKecamatan').value = kecamatan;
                document.getElementById('inpKota').value = kota;
                document.getElementById('inpProvinsi').value = provinsi;

                // Fetch Target from DB
                fetchAreaTarget(provinsi, kota, kecamatan);
                
                // Fetch Internal City Insights
                fetchCityInsights(kota);

            } else {
                document.getElementById('lblAddress').innerText = 'Alamat tidak ditemukan';
            }
        });
    }

    // Jalankan SIG Analysis
    analyzeLocation(latLng);
}

function analyzeLocation(latLng) {
    document.getElementById('sigPanel').style.display = 'block';
    document.getElementById('sigLoading').style.display = 'block';
    document.getElementById('sigResults').style.display = 'none';
    
    let radius = parseInt(document.getElementById('sigRadius').value);
    
    let score = 0;
    let counts = { edu: 0, pub: 0, comp: 0 };
    let trafficNames = [];
    
    // 1. Cek Kanibalisasi & Logistik Radius
    let cannibalRisk = false;
    let minOutletDist = 999999;
    existingOutlets.forEach(o => {
        if(o.latitude && o.longitude) {
            let dist = google.maps.geometry.spherical.computeDistanceBetween(latLng, {lat: parseFloat(o.latitude), lng: parseFloat(o.longitude)});
            if(dist < 1500) cannibalRisk = true;
            if(dist < minOutletDist) minOutletDist = dist;
        }
    });

    let nearestDcDist = 9999999;
    let nearestDcName = '';
    warehouses.forEach(w => {
        if(w.latitude && w.longitude) {
            let dist = google.maps.geometry.spherical.computeDistanceBetween(latLng, {lat: parseFloat(w.latitude), lng: parseFloat(w.longitude)});
            if(dist < nearestDcDist) {
                nearestDcDist = dist;
                nearestDcName = w.nama_warehouse;
            }
        }
    });

    // 2. Pemindaian POI dengan Google Places Nearby Search
    let pendingRequests = 3;
    
    function checkComplete() {
        pendingRequests--;
        if(pendingRequests <= 0) {
            renderSigResults(score, counts, cannibalRisk, minOutletDist, trafficNames, nearestDcDist, nearestDcName);
        }
    }

    // A. Cari Sekolah & Kampus
    placesService.nearbySearch({
        location: latLng,
        radius: radius,
        keyword: 'universitas OR kampus OR sekolah OR institusi pendidikan'
    }, (results, status) => {
        if (status === google.maps.places.PlacesServiceStatus.OK && results) {
            counts.edu = results.length;
            results.slice(0, 3).forEach(r => trafficNames.push(r.name));
        }
        checkComplete();
    });

    // B. Cari Pusat Belanja / Mall / RS
    placesService.nearbySearch({
        location: latLng,
        radius: radius,
        keyword: 'mall OR plaza OR pasar OR rumah sakit OR stasiun OR terminal'
    }, (results, status) => {
        if (status === google.maps.places.PlacesServiceStatus.OK && results) {
            counts.pub = results.length;
            results.slice(0, 2).forEach(r => trafficNames.push(r.name));
        }
        checkComplete();
    });

    // C. Cari Kompetitor (Ayam Geprek, KFC, McD)
    placesService.nearbySearch({
        location: latLng,
        radius: radius,
        keyword: 'ayam geprek OR fried chicken OR kfc OR mcdonalds OR ayam penyet'
    }, (results, status) => {
        if (status === google.maps.places.PlacesServiceStatus.OK && results) {
            counts.comp = results.length;
        }
        checkComplete();
    });
}

function renderSigResults(score, counts, cannibalRisk, minOutletDist, trafficNames, nearestDcDist, nearestDcName) {
    // --- ALGORITMA SITE SCORE TERBARU ---
    // Base Score: 40 (Asumsi setiap area pemukiman punya pasar dasar)
    score = 40;
    
    // 1. Edu Factor (Max 25 poin)
    score += Math.min(counts.edu * 4, 25);
    
    // 2. Public Space Factor (Max 25 poin)
    score += Math.min(counts.pub * 4, 25);
    
    // 3. Competitor Cluster Effect
    // Sedikit kompetitor (1-2) = +5 (Bukti ada market ayam/F&B)
    // Sedang (3-5) = 0 (Normal)
    // Terlalu padat (>5) = -10 (Red Ocean / Oversaturated)
    if (counts.comp >= 1 && counts.comp <= 2) score += 5;
    else if (counts.comp > 5) score -= 10;
    
    // 4. Logistics Factor
    let dcKm = nearestDcDist / 1000;
    if (dcKm < 50) score += 10; // Bonus efisiensi logistik
    else if (dcKm > 100) score -= 20; // Penalti ongkos kirim jarak jauh
    
    // 5. Cannibal Risk (Penalti Fatal)
    if (cannibalRisk) {
        score -= 50; 
    }
    
    // Normalize score 0-100
    if (score < 0) score = 0;
    if (score > 100) score = 100;
    score = Math.round(score);
    
    document.getElementById('sigLoading').style.display = 'none';
    document.getElementById('sigResults').style.display = 'block';
    
    document.getElementById('sigEdu').innerText = counts.edu + ' Titik';
    document.getElementById('sigPub').innerText = counts.pub + ' Titik';
    document.getElementById('sigComp').innerText = counts.comp + ' Gerai';
    
    let canEl = document.getElementById('sigCannibal');
    if (cannibalRisk) {
        canEl.innerText = 'BAHAYA (< 1.5km)';
        canEl.style.color = '#ef4444';
    } else {
        canEl.innerText = 'Aman (' + (minOutletDist/1000).toFixed(1) + 'km)';
        canEl.style.color = '#22c55e';
    }
    
    let logEl = document.getElementById('sigLogistics');
    if (nearestDcName) {
        if (dcKm < 50) {
            logEl.innerHTML = `Aman (<small>${nearestDcName}</small> ${dcKm.toFixed(1)}km)`;
            logEl.style.color = '#22c55e';
        } else if (dcKm <= 100) {
            logEl.innerHTML = `Menengah (<small>${nearestDcName}</small> ${dcKm.toFixed(1)}km)`;
            logEl.style.color = '#eab308';
        } else {
            logEl.innerHTML = `BAHAYA (<small>${nearestDcName}</small> ${dcKm.toFixed(1)}km)`;
            logEl.style.color = '#ef4444';
        }
    } else {
        logEl.innerHTML = '-';
        logEl.style.color = '#64748b';
    }

    
    let badge = document.getElementById('sigScoreBadge');
    badge.innerText = score.toFixed(0) + '/100';
    if (score >= 75) badge.style.background = '#22c55e';
    else if (score >= 40) badge.style.background = '#eab308';
    else badge.style.background = '#ef4444';
    
    // 3. Predictive Revenue AI
    let baseOmset = parseFloat(document.getElementById('aiParamBase').value) || 150000000;
    
    let wEdu = (parseFloat(document.getElementById('aiParamEdu').value) || 3) / 100;
    let wPub = (parseFloat(document.getElementById('aiParamPub').value) || 5) / 100;
    let pCan = (parseFloat(document.getElementById('aiParamCannibal').value) || 50) / 100;
    
    let multiplier = 1.0 + (counts.edu * wEdu) + (counts.pub * wPub);
    if (cannibalRisk) {
        multiplier = multiplier * (1 - pCan);
    }
    
    let estOmset = baseOmset * multiplier;
    let minEst = estOmset * 0.85;
    let maxEst = estOmset * 1.15;
    
    document.getElementById('sigPredict').innerHTML = `Rp ${(minEst/1000000).toFixed(1)}Jt - ${(maxEst/1000000).toFixed(1)}Jt`;
    
    let insight = document.getElementById('sigInsight');
    if (cannibalRisk) {
        insight.innerHTML = "<b>DITOLAK:</b> Titik ini berada di zona merah outlet existing. Sangat berisiko memakan omset sendiri (Kanibalisasi).";
        insight.style.color = '#991b1b';
        insight.style.background = '#fee2e2';
    } else if (score >= 70) {
        insight.innerHTML = "<b>SANGAT POTENSIAL:</b> Area ini ramai dan dikelilingi banyak generator traffic. Persaingan sepadan dengan market size.";
        insight.style.color = '#166534';
        insight.style.background = '#dcfce7';
    } else if (score >= 40) {
        insight.innerHTML = "<b>POTENSI SEDANG:</b> Evaluasi kepadatan penduduk secara manual. Cukup kompetitif.";
        insight.style.color = '#854d0e';
        insight.style.background = '#fef3c7';
    } else {
        insight.innerHTML = "<b>RISIKO TINGGI:</b> Terlalu banyak kompetitor atau area ini terlalu sepi dari pusat aktivitas publik.";
        insight.style.color = '#991b1b';
        insight.style.background = '#fee2e2';
    }
    
    // Auto-fill form Traffic Generator & Zona Prioritas
    if (trafficNames.length > 0) {
        document.getElementById('inpTraffic').value = "Dekat " + trafficNames.join(', ');
    } else {
        document.getElementById('inpTraffic').value = "";
    }

    // Jika belum ada target di DB, buat rekomendasi Zona Prioritas
    if (!currentAreaPotensiId) {
        let zonaReco = [];
        if (counts.edu > 0) zonaReco.push('Kawasan Edukasi/Kampus');
        if (counts.pub > 0) zonaReco.push('Pusat Publik/Belanja');
        if (counts.comp > 2) zonaReco.push('Sentra Kuliner Kompetitif');
        document.getElementById('inpZona').value = zonaReco.join(', ') || 'Kawasan Pemukiman Penduduk';
    }
}

let currentAreaPotensiId = null;

function fetchAreaTarget(provinsi, kota, kecamatan) {
    const url = `/api/marketing/area-target?provinsi=${encodeURIComponent(provinsi)}&kota=${encodeURIComponent(kota)}&kecamatan=${encodeURIComponent(kecamatan)}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if(data && data.id) {
                currentAreaPotensiId = data.id;
                document.getElementById('inpExisting').value = data.existing_count;
                document.getElementById('inpSehat').value = data.sehat_target;
                document.getElementById('inpAgresif').value = data.agresif_target;
                document.getElementById('inpTraffic').value = data.traffic_generator || '';
                document.getElementById('inpZona').value = data.zona_prioritas || '';
            } else {
                currentAreaPotensiId = null;
                // Kalkulasi manual berdasarkan data outlet yang ter-load di peta
                let extCount = existingOutlets.filter(o => {
                    let n = o.nama_outlet ? o.nama_outlet.toLowerCase() : '';
                    let k = kecamatan.toLowerCase();
                    return n.includes(k) || (o.kota && o.kota.toLowerCase().includes(k));
                }).length;
                
                document.getElementById('inpExisting').value = extCount;
                document.getElementById('inpSehat').value = '0';
                document.getElementById('inpAgresif').value = '0';
                // inpTraffic dan inpZona di-handle oleh renderSigResults agar tidak menimpa hasil analisa
            }
        });
}

function fetchCityInsights(kota) {
    if(!kota) return;
    document.getElementById('cityInsightPanel').style.display = 'block';
    document.getElementById('cityInsightLoading').style.display = 'block';
    document.getElementById('cityInsightResults').style.display = 'none';
    document.getElementById('cityInsightKota').innerText = kota.toUpperCase();

    fetch('/api/marketing/city-insights?kota=' + encodeURIComponent(kota))
        .then(r => r.json())
        .then(res => {
            document.getElementById('cityInsightLoading').style.display = 'none';
            document.getElementById('cityInsightResults').style.display = 'block';
            
            if(res.success && res.data) {
                document.getElementById('ciTotalOutlet').innerText = res.data.total_outlet + ' Outlet';
                document.getElementById('ciAvgOmsetMonth').innerText = res.data.avg_omset_month_rp;
                document.getElementById('ciAvgOmsetDay').innerText = res.data.avg_omset_day_rp;
                document.getElementById('ciTopPerformer').innerText = res.data.top_performer;
                document.getElementById('ciTopMenu').innerText = res.data.top_menu;
                
                // Update Base Omset AI param if available
                if (res.data.avg_omset_raw && res.data.avg_omset_raw > 0) {
                    window.currentCityAvgOmset = parseFloat(res.data.avg_omset_raw);
                    document.getElementById('aiParamBase').value = Math.round(window.currentCityAvgOmset);
                } else {
                    window.currentCityAvgOmset = 0;
                }
            }
        }).catch(e => {
            document.getElementById('cityInsightLoading').style.display = 'none';
        });
}

function saveAreaTarget() {
    const btn = document.getElementById('btnSaveTarget');
    btn.innerHTML = '<i class="bi bi-hourglass"></i> Menyimpan...';
    
    const payload = {
        provinsi: document.getElementById('inpProvinsi').value,
        kota: document.getElementById('inpKota').value,
        kecamatan: document.getElementById('inpKecamatan').value,
        existing_count: document.getElementById('inpExisting').value,
        sehat_target: document.getElementById('inpSehat').value,
        agresif_target: document.getElementById('inpAgresif').value,
        traffic_generator: document.getElementById('inpTraffic').value,
        zona_prioritas: document.getElementById('inpZona').value,
    };

    fetch('/api/marketing/area-target', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    }).then(r => r.json()).then(res => {
        if(res.success) {
            currentAreaPotensiId = res.data.id;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Tersimpan';
            setTimeout(() => { btn.innerHTML = '<i class="bi bi-save"></i> Simpan Target Kecamatan'; }, 2000);
            Swal.fire({icon: 'success', title: 'Berhasil', text: 'Target Kecamatan diperbarui!', timer: 1500, showConfirmButton: false});
            loadAllAreaTargets();
        }
    });
}

function loadAllAreaTargets() {
    fetch('/api/marketing/area-targets/all')
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                document.getElementById('priorityList').innerHTML = `<div style="padding:10px; font-size:12px; color:#dc2626;">Error: ${data.error}</div>`;
                return;
            }
            areaTargets = data;
            processAreaTargets();
        })
        .catch(err => {
            document.getElementById('priorityList').innerHTML = `<div style="padding:10px; font-size:12px; color:#dc2626;">Gagal memuat target area.</div>`;
        });
}

function processAreaTargets() {
    targetBubbles.forEach(b => b.setMap(null));
    targetBubbles = [];
    geocodeQueue = [];
    
    areaTargets.forEach(target => {
        if (target.latitude && target.longitude) {
            drawAreaBubble(target);
        } else {
            geocodeQueue.push(target);
        }
    });
    
    populateFilterData();
    applyFilters();
    
    if(geocodeQueue.length > 0 && !isGeocoding) {
        processGeocodeQueue();
    }
}

let uniqueProv = new Set();
let uniqueKota = new Set();
let uniqueKec = new Set();

function populateFilterData() {
    areaTargets.forEach(t => {
        if(t.provinsi) uniqueProv.add(t.provinsi);
        if(t.kota) uniqueKota.add(t.kota);
        if(t.kecamatan) uniqueKec.add(t.kecamatan);
    });
    
    document.getElementById('listProv').innerHTML = Array.from(uniqueProv).sort().map(p => `<option value="${p}">`).join('');
    document.getElementById('listKota').innerHTML = Array.from(uniqueKota).sort().map(k => `<option value="${k}">`).join('');
    document.getElementById('listKec').innerHTML = Array.from(uniqueKec).sort().map(c => `<option value="${c}">`).join('');
}

function applyFilters() {
    const p = (document.getElementById('filterProvinsi').value || '').toLowerCase();
    const k = (document.getElementById('filterKota').value || '').toLowerCase();
    const c = (document.getElementById('filterKecamatan').value || '').toLowerCase();
    
    let bounds = new google.maps.LatLngBounds();
    let hasVisibleItems = false;
    let searchActive = p !== '' || k !== '' || c !== '';
    
    // Filter Area Prioritas (dari areaTargets)
    let priorityItems = [];
    areaTargets.forEach(t => {
        let matchP = p === '' || (t.provinsi && t.provinsi.toLowerCase().includes(p));
        let matchK = k === '' || (t.kota && t.kota.toLowerCase().includes(k));
        let matchC = c === '' || (t.kecamatan && t.kecamatan.toLowerCase().includes(c));
        let sehat = parseInt(t.sehat_target) || 0;
        let existing = parseInt(t.existing_count) || 0;
        
        if (matchP && matchK && matchC && (sehat > existing)) {
            priorityItems.push(t);
        }
    });
    priorityItems.sort((a, b) => (parseInt(b.sehat_target) - parseInt(b.existing_count)) - (parseInt(a.sehat_target) - parseInt(a.existing_count)));
    renderPriorityList(priorityItems);

    // Filter Bubbles di Peta
    targetBubbles.forEach(bubble => {
        let t = bubble.targetData;
        if (!t) return;
        let matchP = p === '' || (t.provinsi && t.provinsi.toLowerCase().includes(p));
        let matchK = k === '' || (t.kota && t.kota.toLowerCase().includes(k));
        let matchC = c === '' || (t.kecamatan && t.kecamatan.toLowerCase().includes(c));
        
        if (matchP && matchK && matchC) {
            bubble.setMap(map);
            if (searchActive) {
                bounds.extend(bubble.getCenter());
                hasVisibleItems = true;
            }
        } else {
            bubble.setMap(null);
        }
    });

    // Filter Belum di-Map (dari existingOutlets)
    let searchStr = (p + " " + k + " " + c).trim();
    let unmappedItems = existingOutlets.filter(o => !o.latitude || !o.longitude);
    
    if(searchStr !== '') {
        unmappedItems = unmappedItems.filter(o => {
            const name = (o.nama_outlet || '').toLowerCase();
            let matchP = p === '' || name.includes(p);
            let matchK = k === '' || name.includes(k);
            let matchC = c === '' || name.includes(c);
            return matchP && matchK && matchC;
        });
    }
    renderUnmappedList(unmappedItems);
    
    // Panggil filterOutletMarkers agar sinkron
    filterOutletMarkers();
    
    if (searchActive && hasVisibleItems) {
        map.fitBounds(bounds);
    }
}

function renderPriorityList(items) {
    const listEl = document.getElementById('priorityList');
    
    if(items.length === 0) {
        listEl.innerHTML = '<div style="padding:10px; font-size:12px; color:#64748b;">Semua target terpenuhi!</div>';
        return;
    }
    
    let html = '';
    // Limit to 100 to prevent DOM lag
    const displayItems = items.slice(0, 100);
    
    displayItems.forEach(target => {
        let deficiency = (parseInt(target.sehat_target) || 0) - (parseInt(target.existing_count) || 0);
        
        html += `
            <div style="padding:10px; border-bottom:1px solid #e2e8f0; cursor:pointer;"
                 onmouseover="this.style.backgroundColor='#f8fafc'"
                 onmouseout="this.style.backgroundColor='transparent'"
                 onclick="panToTargetById(${target.id})">
                <div style="font-size: 13px; font-weight: 700;">${target.kecamatan}</div>
                <div style="font-size: 11px; color: #64748b;">${target.kota}, ${target.provinsi}</div>
                <div style="font-size: 10px; font-weight: 800; color: #dc2626; margin-top: 5px;">KURANG: ${deficiency} OUTLET</div>
            </div>
        `;
    });
    
    if (items.length > 100) {
        html += `<div style="padding:10px; text-align:center; font-size:11px; color:#64748b;">Menampilkan 100 dari ${items.length} target area.</div>`;
    }
    
    listEl.innerHTML = html;
}

function panToTargetById(id) {
    const target = areaTargets.find(x => x.id === id);
    if(target && target.latitude && target.longitude) {
        map.panTo({ lat: parseFloat(target.latitude), lng: parseFloat(target.longitude) });
        map.setZoom(13);
    }
}

function drawAreaBubble(target) {
    let existing = parseInt(target.existing_count) || 0;
    let sehat = parseInt(target.sehat_target) || 0;
    let agresif = parseInt(target.agresif_target) || 0;
    
    let color = '#22c55e';
    if (existing >= agresif && agresif > 0) {
        color = '#ef4444';
    } else if (existing >= sehat && sehat > 0) {
        color = '#eab308';
    }

    const pos = { lat: parseFloat(target.latitude), lng: parseFloat(target.longitude) };
    
    const circle = new google.maps.Circle({
        strokeColor: color,
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: color,
        fillOpacity: 0.25,
        map: map,
        center: pos,
        radius: 800
    });
    
    const infoWindow = new google.maps.InfoWindow({
        content: `<b>Kec. ${target.kecamatan}</b><br>Existing: ${existing}<br>Tgt Sehat: ${sehat}<br>Tgt Agresif: ${agresif}`
    });

    circle.addListener('click', (e) => {
        infoWindow.setPosition(pos);
        infoWindow.open(map);
        // Teruskan klik ke fungsi drop pin agar user tetap bisa menaruh pin di dalam bubble
        if(e && e.latLng) {
            placePotentialPin(e.latLng);
        }
    });

    circle.targetData = target;
    targetBubbles.push(circle);
}

function processGeocodeQueue() {
    if (geocodeQueue.length === 0) {
        isGeocoding = false;
        return;
    }
    
    isGeocoding = true;
    const target = geocodeQueue.shift();
    const address = `${target.kecamatan}, ${target.kota}, ${target.provinsi}, Indonesia`;
    
    geocoder.geocode({ address: address }, function(results, status) {
        if (status === 'OK' && results[0]) {
            const lat = results[0].geometry.location.lat();
            const lng = results[0].geometry.location.lng();
            
            target.latitude = lat;
            target.longitude = lng;
            drawAreaBubble(target);
            
            fetch('/api/marketing/area-targets/gps', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ id: target.id, latitude: lat, longitude: lng })
            });
        }
        setTimeout(processGeocodeQueue, 1000);
    });
}

function handleExcelUpload(event) {
    const file = event.target.files[0];
    if(!file) return;

    Swal.fire({
        title: 'Mengimpor Data...',
        text: 'Mohon tunggu, sedang memproses file Excel.',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    const formData = new FormData();
    formData.append('file', file);

    fetch('/api/marketing/area-target/import', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    }).then(r => r.json()).then(res => {
        if(res.success) {
            Swal.fire('Berhasil', res.message, 'success');
            document.getElementById('inpExcel').value = '';
            loadAllAreaTargets();
        } else {
            Swal.fire('Gagal', res.message || 'Terjadi kesalahan saat impor.', 'error');
            document.getElementById('inpExcel').value = '';
        }
    }).catch(err => {
        Swal.fire('Error', 'Kesalahan jaringan atau server error.', 'error');
        document.getElementById('inpExcel').value = '';
    });
}

function assignSurveyor() {
    if(!currentAreaPotensiId) {
        Swal.fire({icon: 'warning', title: 'Oops', text: 'Silakan Simpan Target Kecamatan terlebih dahulu sebelum menugaskan PIN!'});
        return;
    }

    const payload = {
        area_potensi_id: currentAreaPotensiId,
        latitude: document.getElementById('lblLat').value,
        longitude: document.getElementById('lblLng').value,
        address: document.getElementById('lblAddress').innerText
    };

    const btn = document.getElementById('btnAssign');
    btn.innerHTML = '<i class="bi bi-hourglass"></i> Mengirim...';

    fetch('/api/marketing/pins', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    }).then(r => r.json()).then(res => {
        if(res.success) {
            btn.innerHTML = '<i class="bi bi-send-check"></i> Jadikan Target Survey (Pin)';
            btn.disabled = false;
            Swal.fire({icon: 'success', title: 'Assigned!', text: 'Titik potensi berhasil dikirim ke antrian Surveyor.', timer: 2000, showConfirmButton: false});
            currentPin.setIcon('http://maps.google.com/mapfiles/ms/icons/green-dot.png');
            if (document.getElementById('tabQueue').style.background === 'var(--mi-primary)') {
                loadQueuePins();
            }
        }
    }).catch(err => {
        alert('Terjadi kesalahan: ' + err.message);
        btn.innerHTML = '<i class="bi bi-send-check"></i> Jadikan Target Survey (Pin)';
        btn.disabled = false;
    });
}

function openSiteScoreForm() {
    if(!currentPin) {
        alert("Silakan klik area di peta untuk menentukan lokasi terlebih dahulu!");
        return;
    }
    const pos = currentPin.getPosition();
    const lat = pos.lat();
    const lng = pos.lng();
    const addr = document.getElementById('lblAddress').innerText;
    const mapsUrl = `https://www.google.com/maps?q=${lat},${lng}`;
    
    const url = `{{ route('investor.surveyor.site-score.create') }}?lat=${lat}&lng=${lng}&lokasi=${encodeURIComponent(addr)}&url=${encodeURIComponent(mapsUrl)}`;
    window.open(url, '_blank');
}

function loadQueuePins() {
    let list = document.getElementById('queueList');
    list.innerHTML = '<div style="text-align:center; padding: 10px; font-size:12px; color:var(--mi-gray);"><i class="bi bi-hourglass-split"></i> Memuat antrean...</div>';
    
    fetch('/api/marketing/pins/queue')
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                list.innerHTML = '';
                if (res.data.length === 0) {
                    list.innerHTML = '<div style="text-align:center; padding: 10px; font-size:12px; color:var(--mi-gray);">Tidak ada antrean tugas saat ini.</div>';
                    return;
                }
                
                res.data.forEach(pin => {
                    let d = new Date(pin.created_at);
                    let dateStr = d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    let bg = pin.status === 'CANCELLED' ? '#fef2f2' : '#ffffff';
                    let border = pin.status === 'CANCELLED' ? '#fecaca' : '#e2e8f0';
                    let badge = pin.status === 'CANCELLED' ? '<span style="background:#ef4444; color:white; padding:2px 4px; border-radius:4px; font-size:9px;">DIBATALKAN</span>' : '<span style="background:#3b82f6; color:white; padding:2px 4px; border-radius:4px; font-size:9px;">MENUNGGU SURVEYOR</span>';
                    
                    let actionButtons = '';
                    if (pin.status === 'LEAD') {
                        actionButtons = `
                            <button onclick="cancelPin(${pin.id}, event)" style="padding:4px 8px; font-size:10px; background:#ef4444; color:white; border:none; border-radius:4px; cursor:pointer;"><i class="bi bi-x-circle"></i> Batal</button>
                            <button onclick="deletePin(${pin.id}, event)" style="padding:4px 8px; font-size:10px; background:transparent; color:#94a3b8; border:1px solid #e2e8f0; border-radius:4px; cursor:pointer;"><i class="bi bi-trash"></i> Hapus</button>
                        `;
                    } else {
                        actionButtons = `<button onclick="deletePin(${pin.id}, event)" style="padding:4px 8px; font-size:10px; background:transparent; color:#ef4444; border:1px solid #fecaca; border-radius:4px; cursor:pointer;"><i class="bi bi-trash"></i> Hapus Permanen</button>`;
                    }
                    
                    list.innerHTML += `
                        <div onclick="flyToPin(${pin.latitude}, ${pin.longitude})" style="background: ${bg}; padding: 12px; border-radius: 8px; border: 1px solid ${border}; cursor: pointer; transition: 0.2s;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:5px;">
                                <strong style="font-size: 11px; color: var(--mi-dark);">${pin.area ? pin.area.kecamatan : 'Target Pin'}</strong>
                                ${badge}
                            </div>
                            <div style="font-size: 10px; color: var(--mi-gray); margin-bottom: 8px; line-height:1.4;">
                                ${pin.address}<br>
                                <small style="color:#94a3b8;"><i class="bi bi-clock"></i> Ditugaskan: ${dateStr}</small>
                            </div>
                            <div style="display:flex; gap:5px;">
                                ${actionButtons}
                            </div>
                        </div>
                    `;
                });
            }
        });
}

function flyToPin(lat, lng) {
    if (map) {
        let pos = new google.maps.LatLng(lat, lng);
        map.panTo(pos);
        map.setZoom(16);
        placePotentialPin(pos);
    }
}

function cancelPin(id, event) {
    event.stopPropagation();
    Swal.fire({
        title: 'Batalkan Tugas?',
        text: "Tugas ini akan ditarik dari antrean surveyor.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Batalkan'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/api/marketing/pins/' + id + '/cancel', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(res => {
                if(res.success) loadQueuePins();
            });
        }
    });
}

function deletePin(id, event) {
    event.stopPropagation();
    Swal.fire({
        title: 'Hapus Permanen?',
        text: "Data antrean ini akan dihapus permanen dari sistem.",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Hapus'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/api/marketing/pins/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => r.json()).then(res => {
                if(res.success) loadQueuePins();
            });
        }
    });
}

function clearArray(arr) {
    arr.forEach(m => m.setMap(null));
    arr.length = 0;
}
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,geometry,visualization&callback=initTerritoryMap" async defer></script>

@include('Temp.Investor.footer')

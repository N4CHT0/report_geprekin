@section('title', 'Form Site Score Outlet')
@section('breadcrumb', 'Site Score Outlet / Input Worksheet')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

<style>
    .score-form-page {
        display: grid;
        gap: 18px;
    }

    .score-form-hero {
        background: #ffffff;
        border: 1px solid #d7deea;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .score-form-hero h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -.045em;
        color: #0f172a;
    }

    .score-form-hero p {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
        max-width: 900px;
    }

    .score-form-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 10px;
    }

    .score-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 390px;
        gap: 18px;
        align-items: start;
    }

    .excel-box {
        background: #fff;
        border: 1px solid #d7deea;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
    }

    .excel-box-header {
        background: linear-gradient(180deg,#fff,#f8fafc);
        border-bottom: 1px solid #d7deea;
        padding: 15px 18px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .excel-box-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 950;
        color: #0f172a;
    }

    .excel-box-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
    }

    .excel-box-body {
        padding: 18px;
    }

    .form-section-band {
        background: #1e3a8a;
        color: white;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 950;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .yellow-cell {
        background: #fff7cc !important;
        border-color: #d7b800 !important;
    }

    .cell-input {
        width: 100%;
        min-height: 38px;
        border: 1px solid #b8c2d3;
        border-radius: 8px;
        padding: 0 10px;
        background: #fff;
        font-size: 13px;
        font-weight: 700;
    }

    .cell-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }

    .field-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0,1fr));
        gap: 12px;
    }

    .field-col-12 { grid-column: span 12; }
    .field-col-6 { grid-column: span 6; }
    .field-col-4 { grid-column: span 4; }
    .field-col-3 { grid-column: span 3; }

    .field-label {
        display: block;
        font-size: 12px;
        font-weight: 900;
        color: #334155;
        margin-bottom: 6px;
    }

    .sheet-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14px;
    }

    .sheet-table th {
        background: #eaf1ff;
        border: 1px solid #b8c2d3;
        padding: 10px;
        font-size: 12px;
        font-weight: 950;
        color: #1e293b;
        white-space: nowrap;
    }

    .sheet-table td {
        border: 1px solid #d7deea;
        padding: 8px;
        font-size: 13px;
        vertical-align: middle;
        background: #fff;
    }

    .sheet-table .group-cell {
        background: #f8fafc;
        font-weight: 950;
        color: #0f172a;
        width: 100px;
    }

    .right-score-panel {
        position: sticky;
        top: 86px;
        display: grid;
        gap: 14px;
    }

    .score-card {
        background: #ffffff;
        border: 1px solid #d7deea;
        border-radius: 18px;
        padding: 18px;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
    }

    .score-label {
        font-size: 11px;
        font-weight: 950;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .score-number {
        margin-top: 8px;
        font-size: 42px;
        line-height: 1;
        font-weight: 950;
        letter-spacing: -.05em;
        color: #0f172a;
    }

    .score-status {
        display: inline-flex;
        margin-top: 12px;
        min-height: 34px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 0 14px;
        font-size: 12px;
        font-weight: 950;
        background: #fee2e2;
        color: #991b1b;
    }

    .score-status.approved {
        background: #dcfce7;
        color: #166534;
    }

    .score-status.consideration {
        background: #fef3c7;
        color: #92400e;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px dashed #d7deea;
        font-size: 13px;
    }

    .summary-line:last-child {
        border-bottom: 0;
    }

    .summary-line span {
        color: #64748b;
        font-weight: 800;
    }

    .summary-line b {
        color: #0f172a;
        font-weight: 950;
    }

    .map-preview {
        min-height: 220px;
        border: 1px solid #d7deea;
        border-radius: 16px;
        background: linear-gradient(135deg,rgba(37,99,235,.09),rgba(22,163,74,.08)), #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #64748b;
        padding: 18px;
        font-weight: 750;
    }

    .photo-preview {
        min-height: 160px;
        border: 1px dashed #b8c2d3;
        border-radius: 16px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        text-align: center;
        padding: 14px;
        font-weight: 750;
    }

    .tipe-outlet-toggle {
        display: flex;
        gap: 8px;
    }

    .tipe-outlet-toggle label {
        flex: 1;
        text-align: center;
        padding: 8px 12px;
        border: 2px solid #d7deea;
        border-radius: 10px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 900;
        color: #64748b;
        transition: all .15s;
    }

    .tipe-outlet-toggle input[type=radio] { display: none; }

    .tipe-outlet-toggle input[type=radio]:checked + label {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
    }

    @media (max-width: 1200px) {
        .score-layout { grid-template-columns: 1fr; }
        .right-score-panel { position: static; }
    }

    @media (max-width: 768px) {
        .field-col-6, .field-col-4, .field-col-3 { grid-column: span 12; }
    }

    /* JAM RAMAI WIDGET STYLES */
    .jam-ramai-container {
        margin-top: 10px;
        font-family: 'Google Sans', Roboto, Arial, sans-serif;
    }
    .jam-ramai-day-selector {
        display: flex;
        gap: 4px;
        margin-bottom: 15px;
    }
    .jam-ramai-day {
        flex: 1;
        text-align: center;
        padding: 6px 0;
        font-size: 11px;
        font-weight: 700;
        color: #70757a;
        cursor: pointer;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .jam-ramai-day:hover {
        background: #f1f3f4;
        color: #202124;
    }
    .jam-ramai-day.active {
        color: #1a73e8;
        background: #e8f0fe;
        font-weight: 800;
    }
    .jam-ramai-day.weekend {
        color: #d93025;
    }
    .jam-ramai-day.weekend.active {
        background: #fce8e6;
    }
    .jam-ramai-chart {
        display: flex;
        align-items: flex-end;
        height: 120px;
        gap: 2px;
        padding-bottom: 5px;
        border-bottom: 1px solid #dadce0;
        position: relative;
    }
    .jam-ramai-bar-group {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        align-items: center;
        position: relative;
        height: 100%;
    }
    .jam-ramai-bar {
        width: 100%;
        background: #8ab4f8;
        border-radius: 2px 2px 0 0;
        min-height: 2px;
        transition: height 0.5s ease-out;
    }
    .jam-ramai-bar.pejalan {
        background: #34a853;
        opacity: 0.8;
        width: 60%;
        position: absolute;
        bottom: 0;
        z-index: 2;
    }
    .jam-ramai-bar-group:hover .jam-ramai-tooltip {
        opacity: 1;
        visibility: visible;
    }
    .jam-ramai-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #202124;
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        z-index: 10;
        margin-bottom: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .jam-ramai-time-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 5px;
        font-size: 10px;
        color: #70757a;
    }
    .jam-ramai-legend {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 15px;
        font-size: 11px;
        color: #5f6368;
    }
    .legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }
    .dot-motor { background: #8ab4f8; }
    .dot-pejalan { background: #34a853; }
</style>

<form action="{{ route('investor.surveyor.site-score.store') }}"
      method="POST"
      enctype="multipart/form-data"
      id="siteScoreForm">

    @csrf

    <div class="score-form-page">

        <div class="score-form-hero">
            <div>
                <div class="score-form-kicker">
                    <i class="bi bi-table"></i>
                    Form Site Score Survey
                </div>
                <h1>Input Worksheet Site Score Outlet</h1>
                <p>
                    Versi web dibuat seperti worksheet Excel. Isi cell kuning, ambil titik GPS,
                    upload foto lokasi, lalu sistem menghitung score dan rekomendasi secara otomatis.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn-worksheet-light" onclick="ambilTitikGPS()">
                    <i class="bi bi-crosshair"></i>
                    Ambil Titik GPS
                </button>
                <button type="submit" name="action_type" value="draft" class="btn-worksheet-light text-dark" style="border-color:#d1d5db; background:#fff;" formnovalidate>
                    <i class="bi bi-file-earmark-text"></i>
                    Simpan Draft
                </button>
                <button type="submit" name="action_type" value="submit" class="btn-worksheet">
                    <i class="bi bi-send"></i>
                    Kirim ke Manajemen
                </button>
            </div>
        </div>

        <div class="score-layout">

            <div class="left-form-panel">

                {{-- DATA LOKASI --}}
                <div class="excel-box mb-3">
                    <div class="excel-box-header">
                        <div>
                            <h5>Data Lokasi</h5>
                            <p>Informasi titik yang akan disurvey.</p>
                        </div>
                    </div>
                    <div class="excel-box-body">
                        <div class="field-grid">
                            @if(isset($candidate))
                                <input type="hidden" name="candidate_id" value="{{ $candidate->id }}">
                            @endif
                            <div class="field-col-6">
                                <label class="field-label">Lokasi</label>
                                <input type="text" name="lokasi" class="cell-input yellow-cell" placeholder="Nama jalan / titik lokasi" value="{{ $candidate ? $candidate->nama_lokasi : '' }}" required>
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Kota / Kab.</label>
                                <input type="text" name="kota" class="cell-input yellow-cell" value="{{ $candidate ? $candidate->kota : '' }}" required>
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Provinsi</label>
                                <input type="text" name="provinsi" class="cell-input" value="{{ $candidate && $candidate->provinsi ? $candidate->provinsi : 'Jawa Timur' }}">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Latitude</label>
                                <input type="text" name="latitude" id="latitude" class="cell-input yellow-cell" placeholder="-7.xxxxxx" value="{{ $candidate ? $candidate->latitude : '' }}">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Longitude</label>
                                <input type="text" name="longitude" id="longitude" class="cell-input yellow-cell" placeholder="112.xxxxxx" value="{{ $candidate ? $candidate->longitude : '' }}">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Surveyor</label>
                                <input type="text" name="surveyor" class="cell-input" value="{{ auth()->user()->name ?? '' }}">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Tanggal</label>
                                <input type="datetime-local" name="tanggal_survey" class="cell-input" value="{{ date('Y-m-d\TH:i') }}">
                            </div>

                            {{-- TIPE OUTLET LDP / BDP --}}
                            <div class="field-col-6">
                                <label class="field-label">Tipe Outlet</label>
                                <div class="tipe-outlet-toggle">
                                    <input type="radio" name="tipe_outlet" id="tipe_ldp" value="LDP" checked>
                                    <label for="tipe_ldp">
                                        <i class="bi bi-shop"></i> LDP
                                        <div style="font-size:10px;font-weight:700;color:#94a3b8;">Approved ≥ 60%</div>
                                    </label>
                                    <input type="radio" name="tipe_outlet" id="tipe_bdp" value="BDP">
                                    <label for="tipe_bdp">
                                        <i class="bi bi-building"></i> BDP
                                        <div style="font-size:10px;font-weight:700;color:#94a3b8;">Approved ≥ 100%</div>
                                    </label>
                                </div>
                            </div>

                            <div class="field-col-6">
                                <label class="field-label">Average Check (Rp)</label>
                                <input type="number" name="average_check" class="cell-input yellow-cell calc-input" value="21000" min="1000">
                            </div>

                            <div class="field-col-12">
                                <label class="field-label">Link Maps</label>
                                <input type="text" name="maps_url" id="maps_url" class="cell-input" placeholder="Otomatis dari GPS / paste Google Maps">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DATA BANGUNAN --}}
                <div class="excel-box mb-3">
                    <div class="excel-box-header">
                        <div>
                            <h5>Data Bangunan & Visibilitas</h5>
                            <p>Informasi fisik bangunan, harga sewa, dan akses jalan.</p>
                        </div>
                    </div>
                    <div class="excel-box-body">
                        <div class="field-grid">
                            <div class="field-col-3"><label class="field-label">Tipe Bangunan</label><input type="text" name="tipe_bangunan" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Luas Bangunan (m2)</label><input type="number" name="luas_bangunan" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Status Sewa/Beli</label><select name="status_sewa_beli" class="cell-input yellow-cell"><option value="Sewa">Sewa</option><option value="Beli">Beli</option></select></div>
                            <div class="field-col-3"><label class="field-label">Harga Sewa/Beli (Rp)</label><input type="number" name="harga_sewa" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Lebar Depan (m)</label><input type="number" name="lebar_depan" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Panjang (m)</label><input type="number" name="panjang_bangunan" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Jml Lantai</label><input type="number" name="jumlah_lantai" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Kondisi</label><input type="text" name="kondisi_bangunan" class="cell-input yellow-cell" placeholder="Layak/Renov"></div>

                            <div class="field-col-12 mt-3 fw-bold border-bottom pb-1" style="font-size:13px;">Visibilitas (Visibility)</div>
                            <div class="field-col-4"><label class="field-label">Terlihat dr Jln Utama</label><select name="terlihat_jalan_utama" class="cell-input yellow-cell"><option value="1">Ya</option><option value="0">Tidak</option></select></div>
                            <div class="field-col-4"><label class="field-label">Posisi Hook</label><select name="posisi_hook" class="cell-input yellow-cell"><option value="1">Ya</option><option value="0">Tidak</option></select></div>
                            <div class="field-col-4"><label class="field-label">Frontage (m)</label><input type="number" name="frontage" class="cell-input yellow-cell"></div>
                            <div class="field-col-4"><label class="field-label">Terhalang Pohon/Kabel</label><select name="terhalang_pohon_kabel" class="cell-input yellow-cell"><option value="0">Tidak</option><option value="1">Ya</option></select></div>
                            <div class="field-col-4"><label class="field-label">Ruang Signage</label><select name="ruang_signage" class="cell-input yellow-cell"><option value="1">Ada</option><option value="0">Tidak Ada</option></select></div>
                            <div class="field-col-4"><label class="field-label">Penerangan Malam</label><select name="penerangan_malam" class="cell-input yellow-cell"><option value="1">Terang</option><option value="0">Gelap</option></select></div>

                            <div class="field-col-12 mt-3 fw-bold border-bottom pb-1" style="font-size:13px;">Akses & Parkir</div>
                            <div class="field-col-3"><label class="field-label">Lebar Jalan (m)</label><input type="number" name="lebar_jalan" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Jenis Jalan</label><select name="jenis_jalan" class="cell-input yellow-cell"><option value="1 Arah">1 Arah</option><option value="2 Arah">2 Arah</option></select></div>
                            <div class="field-col-3"><label class="field-label">U-Turn / Lampu Merah</label><select name="u_turn_lampu_merah" class="cell-input yellow-cell"><option value="1">Dekat</option><option value="0">Jauh</option></select></div>
                            <div class="field-col-3"><label class="field-label">Akses Mobil</label><select name="akses_mobil" class="cell-input yellow-cell"><option value="1">Bisa</option><option value="0">Sulit/Tidak</option></select></div>
                        </div>
                    </div>
                </div>

                {{-- PENAMBAH NILAI --}}
                <div class="excel-box mb-3">
                    <div class="excel-box-header">
                        <div>
                            <h5>Penambah Nilai</h5>
                            <p>Traffic, rumah penduduk, dan fasilitas umum.</p>
                        </div>
                    </div>
                    <div class="excel-box-body">

                        <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-3" style="border-radius:12px; border:1px solid #93c5fd; background:#eff6ff;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-camera-video text-primary fs-2 me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1" style="color:#1e3a8a;">Hitung Traffic Otomatis dengan AI</h6>
                                    <p class="mb-0" style="font-size:13px; color:#3b82f6;">
                                        Upload video pengamatan, dan biarkan AI menghitung Motor dan Pejalan Kaki untuk Anda.
                                    </p>
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" style="border-radius: 8px;" onclick="openAiModal()">
                                <i class="bi bi-magic"></i> Analisis Video Sekarang
                            </button>
                        </div>

                        <div class="form-section-band">
                            <i class="bi bi-scooter"></i>
                            1. Traffic Sepeda Motor
                        </div>

                        <div class="table-responsive">
                            <table class="sheet-table">
                                <thead>
                                    <tr>
                                        <th>Hari</th><th>Periode</th><th>Jam</th>
                                        <th>Input Count</th><th>Bobot Max</th>
                                        <th>Grade</th><th>Nilai</th><th>Ket.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td rowspan="3" class="group-cell">Weekday</td>
                                        <td>Pagi</td><td>06:00 - 08:00</td>
                                        <td><input type="number" name="motor_weekday_pagi" class="cell-input yellow-cell motor-input calc-input" value="0" min="0"></td>
                                        <td>0.30</td><td class="motor-grade">0</td><td class="motor-nilai">0.00</td><td>Motor</td>
                                    </tr>
                                    <tr>
                                        <td>Siang</td><td>11:00 - 13:00</td>
                                        <td><input type="number" name="motor_weekday_siang" class="cell-input yellow-cell motor-input calc-input" value="0" min="0"></td>
                                        <td>0.30</td><td class="motor-grade">0</td><td class="motor-nilai">0.00</td><td>Motor</td>
                                    </tr>
                                    <tr>
                                        <td>Petang</td><td>17:00 - 20:00</td>
                                        <td><input type="number" name="motor_weekday_sore" class="cell-input yellow-cell motor-input calc-input" value="0" min="0"></td>
                                        <td>0.30</td><td class="motor-grade">0</td><td class="motor-nilai">0.00</td><td>Motor</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="3" class="group-cell">Weekend</td>
                                        <td>Pagi</td><td>06:00 - 08:00</td>
                                        <td><input type="number" name="motor_weekend_pagi" class="cell-input yellow-cell motor-input calc-input" value="0" min="0"></td>
                                        <td>0.30</td><td class="motor-grade">0</td><td class="motor-nilai">0.00</td><td>Motor</td>
                                    </tr>
                                    <tr>
                                        <td>Siang</td><td>11:00 - 13:00</td>
                                        <td><input type="number" name="motor_weekend_siang" class="cell-input yellow-cell motor-input calc-input" value="0" min="0"></td>
                                        <td>0.30</td><td class="motor-grade">0</td><td class="motor-nilai">0.00</td><td>Motor</td>
                                    </tr>
                                    <tr>
                                        <td>Petang</td><td>17:00 - 20:00</td>
                                        <td><input type="number" name="motor_weekend_sore" class="cell-input yellow-cell motor-input calc-input" value="0" min="0"></td>
                                        <td>0.30</td><td class="motor-grade">0</td><td class="motor-nilai">0.00</td><td>Motor</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="fw-bold">TOTAL TRAFFIC MOTOR</td>
                                        <td class="fw-bold" id="totalMotorCell">0</td>
                                        <td colspan="2" class="fw-bold">Score Final</td>
                                        <td class="fw-bold" id="motorScoreCell">0.00%</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-section-band">
                            <i class="bi bi-person-walking"></i>
                            2. Traffic Pejalan Kaki
                        </div>

                        <div class="table-responsive">
                            <table class="sheet-table">
                                <thead>
                                    <tr>
                                        <th>Hari</th><th>Periode</th><th>Jam</th>
                                        <th>Input Count</th><th>Bobot Max</th>
                                        <th>Grade</th><th>Nilai</th><th>Ket.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td rowspan="3" class="group-cell">Weekday</td>
                                        <td>Pagi</td><td>06:00 - 08:00</td>
                                        <td><input type="number" name="pejalan_weekday_pagi" class="cell-input yellow-cell pejalan-input calc-input" value="0" min="0"></td>
                                        <td>0.20</td><td class="pejalan-grade">0</td><td class="pejalan-nilai">0.00</td><td>Jalan</td>
                                    </tr>
                                    <tr>
                                        <td>Siang</td><td>11:00 - 13:00</td>
                                        <td><input type="number" name="pejalan_weekday_siang" class="cell-input yellow-cell pejalan-input calc-input" value="0" min="0"></td>
                                        <td>0.20</td><td class="pejalan-grade">0</td><td class="pejalan-nilai">0.00</td><td>Jalan</td>
                                    </tr>
                                    <tr>
                                        <td>Petang</td><td>17:00 - 20:00</td>
                                        <td><input type="number" name="pejalan_weekday_sore" class="cell-input yellow-cell pejalan-input calc-input" value="0" min="0"></td>
                                        <td>0.20</td><td class="pejalan-grade">0</td><td class="pejalan-nilai">0.00</td><td>Jalan</td>
                                    </tr>
                                    <tr>
                                        <td rowspan="3" class="group-cell">Weekend</td>
                                        <td>Pagi</td><td>06:00 - 08:00</td>
                                        <td><input type="number" name="pejalan_weekend_pagi" class="cell-input yellow-cell pejalan-input calc-input" value="0" min="0"></td>
                                        <td>0.20</td><td class="pejalan-grade">0</td><td class="pejalan-nilai">0.00</td><td>Jalan</td>
                                    </tr>
                                    <tr>
                                        <td>Siang</td><td>11:00 - 13:00</td>
                                        <td><input type="number" name="pejalan_weekend_siang" class="cell-input yellow-cell pejalan-input calc-input" value="0" min="0"></td>
                                        <td>0.20</td><td class="pejalan-grade">0</td><td class="pejalan-nilai">0.00</td><td>Jalan</td>
                                    </tr>
                                    <tr>
                                        <td>Petang</td><td>17:00 - 20:00</td>
                                        <td><input type="number" name="pejalan_weekend_sore" class="cell-input yellow-cell pejalan-input calc-input" value="0" min="0"></td>
                                        <td>0.20</td><td class="pejalan-grade">0</td><td class="pejalan-nilai">0.00</td><td>Jalan</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="fw-bold">TOTAL PEJALAN KAKI</td>
                                        <td class="fw-bold" id="totalPejalanCell">0</td>
                                        <td colspan="2" class="fw-bold">Score Final</td>
                                        <td class="fw-bold" id="pejalanScoreCell">0.00%</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-section-band d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-house-door"></i>
                                3. Rumah Penduduk & Fasilitas Umum
                            </div>
                            <button type="button" class="btn btn-sm btn-light text-primary fw-bold px-3 py-1" id="btnRadar" onclick="radarFasilitas()" style="border-radius: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <i class="bi bi-radar"></i> Radar Fasilitas (Otomatis)
                            </button>
                        </div>

                        <div class="field-grid">
                            <div class="field-col-3">
                                <label class="field-label">Rumah Q1 <small class="text-muted">(max 15%)</small></label>
                                <input type="number" name="rumah_q1" class="cell-input yellow-cell facility-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Rumah Q2 <small class="text-muted">(max 10%)</small></label>
                                <input type="number" name="rumah_q2" class="cell-input yellow-cell facility-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Rumah Q3 <small class="text-muted">(max 5%)</small></label>
                                <input type="number" name="rumah_q3" class="cell-input yellow-cell facility-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Rumah Q4 <small class="text-muted">(max 5%)</small></label>
                                <input type="number" name="rumah_q4" class="cell-input yellow-cell facility-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Sekolah <small class="text-muted">(max 5%)</small></label>
                                <input type="number" name="sekolah" class="cell-input yellow-cell facility-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Market <small class="text-muted">(max 5%)</small></label>
                                <input type="number" name="market" class="cell-input yellow-cell facility-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Perkantoran <small class="text-muted">(max 2.5%)</small></label>
                                <input type="number" name="perkantoran" class="cell-input yellow-cell facility-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-3">
                                <label class="field-label">Kesehatan <small class="text-muted">(max 2.5%)</small></label>
                                <input type="number" name="kesehatan" class="cell-input yellow-cell calc-input" value="0" min="0">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- PENGURANG NILAI --}}
                <div class="excel-box mb-3">
                    <div class="excel-box-header">
                        <div>
                            <h5>Data Kompetitor (Pengurang Nilai)</h5>
                            <p>Kompetitor terdekat dan harga pembanding.</p>
                        </div>
                    </div>
                    <div class="excel-box-body">
                        <div class="field-grid">
                            <div class="field-col-4">
                                <label class="field-label">Kompetitor Geprek / FC <small class="text-muted">(max -2.5%)</small></label>
                                <input type="number" name="kompetitor_geprek" class="cell-input yellow-cell competitor-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-4">
                                <label class="field-label">Kompetitor Makanan Lokal <small class="text-muted">(max -2.5%)</small></label>
                                <input type="number" name="kompetitor_lokal" class="cell-input yellow-cell competitor-input calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-4">
                                <label class="field-label">Harga Kompetitor</label>
                                <input type="number" name="harga_kompetitor" class="cell-input yellow-cell calc-input" value="0" min="0">
                            </div>
                            <div class="field-col-12">
                                <label class="field-label">Catatan Surveyor</label>
                                <textarea name="catatan" class="cell-input" style="height:100px;padding-top:10px;" placeholder="Catatan kondisi lokasi, akses, kompetitor, parkir, dan potensi area."></textarea>
                            </div>
                            <div class="field-col-12">
                                <label class="field-label">Upload Foto Lokasi</label>
                                <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RAB & KESIMPULAN --}}
            <div class="excel-box mb-3">
                <div class="excel-box-header">
                    <div>
                        <h5>RAB & Kesimpulan Surveyor</h5>
                        <p>Estimasi biaya awal dan opini dari surveyor lapangan.</p>
                    </div>
                </div>
                <div class="excel-box-body">
                    <div class="field-grid">
                        <div class="field-col-3"><label class="field-label">Estimasi Renovasi</label><input type="number" name="rab_renovasi" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Peralatan Dapur</label><input type="number" name="rab_kitchen" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Signage</label><input type="number" name="rab_signage" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Furniture</label><input type="number" name="rab_furniture" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Listrik</label><input type="number" name="rab_listrik" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Air</label><input type="number" name="rab_air" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Exhaust</label><input type="number" name="rab_exhaust" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">AC / Kipas</label><input type="number" name="rab_ac_kipas" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Perizinan</label><input type="number" name="rab_perizinan" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Deposit Sewa</label><input type="number" name="rab_deposit_sewa" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-3"><label class="field-label">Biaya Opening</label><input type="number" name="rab_biaya_opening" class="cell-input yellow-cell" value="0"></div>
                        <div class="field-col-12 border-bottom pb-2 mt-2"></div>
                        <div class="field-col-4">
                            <label class="field-label">Kelebihan Lokasi</label>
                            <textarea name="kelebihan_lokasi" class="cell-input yellow-cell" style="height:60px;" placeholder="Cth: Ramai dekat kampus..."></textarea>
                        </div>
                        <div class="field-col-4">
                            <label class="field-label">Kekurangan Lokasi</label>
                            <textarea name="kekurangan_lokasi" class="cell-input yellow-cell" style="height:60px;" placeholder="Cth: Parkiran sempit..."></textarea>
                        </div>
                        <div class="field-col-4">
                            <label class="field-label">Risiko</label>
                            <textarea name="risiko" class="cell-input yellow-cell" style="height:60px;" placeholder="Cth: Banjir saat hujan besar..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PANEL KANAN --}}
            <div class="right-score-panel">
                <div class="score-card">
                    <div class="score-label">Final Score</div>
                    <div class="score-number" id="finalScoreDisplay">0.0%</div>
                    <div class="score-status" id="recommendationDisplay">REJECTED</div>
                    <div class="mt-3">
                        <div class="excel-progress">
                            <span id="scoreProgress" style="width:0%;"></span>
                        </div>
                    </div>
                </div>

                <div class="score-card">
                    <div class="summary-line">
                        <span>Tipe Outlet</span>
                        <b id="tipeOutletDisplay">LDP</b>
                    </div>
                    <div class="summary-line">
                        <span>Threshold Approved</span>
                        <b id="thresholdDisplay">≥ 60%</b>
                    </div>
                    <div class="summary-line">
                        <span>Total Penambah</span>
                        <b id="totalPlusDisplay">0.00%</b>
                    </div>
                    <div class="summary-line">
                        <span>Total Pengurang</span>
                        <b id="totalMinusDisplay">0.00%</b>
                    </div>
                    <div class="summary-line">
                        <span>Total Motor</span>
                        <b id="summaryMotor">0</b>
                    </div>
                    <div class="summary-line">
                        <span>Total Pejalan</span>
                        <b id="summaryPejalan">0</b>
                    </div>
                    <div class="summary-line">
                        <span>Est. Omset / Minggu</span>
                        <b id="omsetPerhariDisplay">Rp 0</b>
                    </div>
                </div>

                <div class="score-card">
                    <div class="score-label mb-2">Titik Lokasi Maps</div>
                    <div class="map-preview mb-3" id="mapPreview">
                        <div>
                            <i class="bi bi-geo-alt fs-1 text-primary"></i>
                            <div class="mt-2">Klik Ambil Titik GPS atau paste latitude / longitude.</div>
                        </div>
                    </div>
                    <a href="#" target="_blank" id="openMapsBtn"
                       class="btn-worksheet-light w-100 justify-content-center disabled">
                        <i class="bi bi-map"></i> Open Maps
                    </a>
                </div>

                <div class="score-card">
                    <div class="score-label mb-2">Upload Dokumentasi</div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size: 13px; font-weight: 600;">Upload Foto Lokasi (Bisa pilih banyak)</label>
                        <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
                        <div class="form-text" style="font-size: 11px;">Maksimal 5MB per foto. (Foto Depan, Kiri/Kanan, Dalam, Jalan, dll).</div>
                    </div>
                </div>

                <div class="score-card">
                    <div class="score-label mb-2">Jam Ramai (Estimasi)</div>
                    <div class="jam-ramai-container">
                        <div class="jam-ramai-day-selector" id="jamRamaiDaySelector">
                            <div class="jam-ramai-day active" data-day="Sen">Sen</div>
                            <div class="jam-ramai-day" data-day="Sel">Sel</div>
                            <div class="jam-ramai-day" data-day="Rab">Rab</div>
                            <div class="jam-ramai-day" data-day="Kam">Kam</div>
                            <div class="jam-ramai-day" data-day="Jum">Jum</div>
                            <div class="jam-ramai-day weekend" data-day="Sab">Sab</div>
                            <div class="jam-ramai-day weekend" data-day="Min">Min</div>
                        </div>
                        <div class="jam-ramai-chart" id="jamRamaiChart">
                            <!-- Bars will be generated here -->
                        </div>
                        <div class="jam-ramai-time-labels">
                            <span>06:00</span>
                            <span>12:00</span>
                            <span>18:00</span>
                            <span>23:00</span>
                        </div>
                        <div class="jam-ramai-legend">
                            <span class="legend-dot dot-motor"></span> Motor
                            <span class="legend-dot dot-pejalan" style="margin-left: 12px;"></span> Pejalan
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</form>

<!-- MODAL AI -->
<div class="modal fade" id="aiDetectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border:none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary"><i class="bi bi-robot"></i> Video Detection AI</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="aiDetectionForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Video ini untuk pengamatan kapan?</label>
                        <select id="aiPeriode" class="form-select" required>
                            <option value="">Pilih Hari & Jam...</option>
                            <option value="weekday_pagi">Weekday Pagi (06:00 - 08:00)</option>
                            <option value="weekday_siang">Weekday Siang (11:00 - 13:00)</option>
                            <option value="weekday_sore">Weekday Petang (17:00 - 20:00)</option>
                            <option value="weekend_pagi">Weekend Pagi (06:00 - 08:00)</option>
                            <option value="weekend_siang">Weekend Siang (11:00 - 13:00)</option>
                            <option value="weekend_sore">Weekend Petang (17:00 - 20:00)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold" style="font-size: 13px;">Upload File Video (.mp4, .mov)</label>
                        <input type="file" id="aiVideoFile" class="form-control" accept="video/*">
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 8px;" id="btnMulaiAi">
                            Mulai Analisis
                        </button>
                    </div>
                </form>

                <div id="aiProgressArea" class="mt-4 text-center d-none">
                    <div class="spinner-border text-primary mb-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="fw-bold text-primary" id="aiStatusText">Mengunggah Video...</h6>
                    <p class="text-muted" style="font-size: 12px;">Mohon jangan tutup jendela ini.</p>
                </div>
                
                <div id="aiResultArea" class="mt-4 text-center d-none">
                    <div class="mb-3">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold text-success">Analisis Selesai!</h5>
                    
                    <div class="my-3 text-center">
                        <p class="fw-bold mb-1" style="font-size: 13px;">Snapshot Peak Minute</p>
                        <img id="resPeakFrame" src="" alt="Peak Frame" class="img-fluid rounded shadow-sm d-none" style="max-height: 200px; object-fit: contain; border: 2px solid #e2e8f0; margin: 0 auto;">
                    </div>

                    <p class="mb-1">Motor: <strong id="resMotor">0</strong></p>
                    <p class="mb-3">Pejalan Kaki: <strong id="resPejalan">0</strong></p>
                    <button type="button" class="btn btn-success w-100 fw-bold" style="border-radius: 8px;" onclick="applyAiResult()">
                        Terapkan ke Form
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────────────────────────
// STANDARDS — identik 1:1 dengan surveyor_site_score_standards di DB
// Bobot = nilai kontribusi langsung (bukan grade/5 × bobot)
// ─────────────────────────────────────────────────────────────────────────────
const STANDARDS = {
    // Traffic Motor — threshold = per-sesi × 6 sesi (spreadsheet scoring: 1000,2000,3000,4000,5000)
    traffic_motor: { tipe:'PLUS', grades:[
        {min:6000,  max:11999,   bobot:0.06},
        {min:12000, max:17999,   bobot:0.12},
        {min:18000, max:23999,   bobot:0.18},
        {min:24000, max:29999,   bobot:0.24},
        {min:30000, max:9999999, bobot:0.30}]},
    // Traffic Pejalan — threshold = jumlah per-sesi (WD+WE pagi/siang/petang)
    traffic_pejalan: { tipe:'PLUS', grades:[
        {min:1,   max:81,      bobot:0.04},
        {min:82,  max:139,     bobot:0.08},
        {min:140, max:193,     bobot:0.12},
        {min:194, max:249,     bobot:0.16},
        {min:250, max:9999999, bobot:0.20}]},

    // Rumah Q1-Q4 — spreadsheet scoring: 500, 600, 750, 900, 1000
    rumah_q1: { tipe:'PLUS', grades:[
        {min:500,  max:599,     bobot:0.03},
        {min:600,  max:749,     bobot:0.06},
        {min:750,  max:899,     bobot:0.09},
        {min:900,  max:999,     bobot:0.12},
        {min:1000, max:9999999, bobot:0.15}]},
    rumah_q2: { tipe:'PLUS', grades:[
        {min:500,  max:599,     bobot:0.02},
        {min:600,  max:749,     bobot:0.04},
        {min:750,  max:899,     bobot:0.06},
        {min:900,  max:999,     bobot:0.08},
        {min:1000, max:9999999, bobot:0.10}]},
    rumah_q3: { tipe:'PLUS', grades:[
        {min:500,  max:599,     bobot:0.01},
        {min:600,  max:749,     bobot:0.02},
        {min:750,  max:899,     bobot:0.03},
        {min:900,  max:999,     bobot:0.04},
        {min:1000, max:9999999, bobot:0.05}]},
    rumah_q4: { tipe:'PLUS', grades:[
        {min:500,  max:599,     bobot:0.01},
        {min:600,  max:749,     bobot:0.02},
        {min:750,  max:899,     bobot:0.03},
        {min:900,  max:999,     bobot:0.04},
        {min:1000, max:9999999, bobot:0.05}]},

    // Sekolah — spreadsheet scoring: 3, 5, 10, 15, 20
    sekolah: { tipe:'PLUS', grades:[
        {min:3,  max:4,       bobot:0.01},
        {min:5,  max:9,       bobot:0.02},
        {min:10, max:14,      bobot:0.03},
        {min:15, max:19,      bobot:0.04},
        {min:20, max:9999999, bobot:0.05}]},
    // Market — spreadsheet scoring: 1,1,2,2,3 (double-grade pattern)
    market: { tipe:'PLUS', grades:[
        {min:1, max:1,       bobot:0.02},
        {min:2, max:2,       bobot:0.04},
        {min:3, max:9999999, bobot:0.05}]},
    // Perkantoran — spreadsheet scoring: 1,1,2,2,3 (double-grade pattern)
    perkantoran: { tipe:'PLUS', grades:[
        {min:1, max:1,       bobot:0.010},
        {min:2, max:2,       bobot:0.020},
        {min:3, max:9999999, bobot:0.025}]},
    // Kesehatan — spreadsheet scoring: 2,2,3,3,4
    kesehatan: { tipe:'PLUS', grades:[
        {min:2, max:2,       bobot:0.010},
        {min:3, max:3,       bobot:0.020},
        {min:4, max:9999999, bobot:0.025}]},
    // Kompetitor Geprek — threshold sesuai spreadsheet scoring: 0,2,4,6,8
    kompetitor_geprek: { tipe:'MINUS', grades:[
        {min:1, max:1,       bobot:0.005},
        {min:2, max:3,       bobot:0.010},
        {min:4, max:5,       bobot:0.015},
        {min:6, max:7,       bobot:0.020},
        {min:8, max:9999999, bobot:0.025}]},
    // Kompetitor Lokal — threshold sesuai spreadsheet scoring: 10,15,20,25,30
    kompetitor_lokal: { tipe:'MINUS', grades:[
        {min:1,  max:9,       bobot:0.005},
        {min:10, max:14,      bobot:0.010},
        {min:15, max:19,      bobot:0.015},
        {min:20, max:24,      bobot:0.020},
        {min:25, max:9999999, bobot:0.025}]},
};

const THRESHOLD = {
    LDP: { approved: 0.60, consideration: 0.50 },
    BDP: { approved: 1.00, consideration: 0.60 },
};

const RASIO = { motor:0.0075, pejalan:0.0050, q1:0.0125, q2:0.0075, q3:0.0050, q4:0.0025 };

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────────────────────
function toNumber(v) { const n = parseFloat(v||0); return isNaN(n)?0:n; }
function sumInputs(sel) { let t=0; document.querySelectorAll(sel).forEach(el=>t+=toNumber(el.value)); return t; }
function getVal(name) { const el=document.querySelector('[name="'+name+'"]'); return el?toNumber(el.value):0; }
function formatRupiah(n) { return 'Rp '+Math.round(n).toLocaleString('id-ID'); }

function hitungScore(kode, nilai) {
    const std = STANDARDS[kode];
    if (!std || nilai <= 0) return 0;
    nilai = Math.floor(nilai);
    for (const g of std.grades) {
        if (nilai >= g.min && nilai <= g.max) return g.bobot;
    }
    return 0;
}

function getGradeLabel(kode, nilai) {
    const std = STANDARDS[kode];
    if (!std || nilai <= 0) return 0;
    nilai = Math.floor(nilai);
    for (let i = 0; i < std.grades.length; i++) {
        if (nilai >= std.grades[i].min && nilai <= std.grades[i].max) return i + 1;
    }
    return 0;
}

function getTipeOutlet() {
    const el = document.querySelector('[name="tipe_outlet"]:checked');
    return el ? el.value : 'LDP';
}

function updateMapsPreview() {
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    const maps = document.getElementById('maps_url');
    const btn = document.getElementById('openMapsBtn');
    const previewContainer = document.getElementById('mapPreview');
    
    if (lat && lng) {
        const url = 'https://www.google.com/maps?q=' + lat + ',' + lng;
        maps.value = maps.value || url;
        btn.href = url;
        btn.classList.remove('disabled');
        
        // Render iframe maps
        const embedUrl = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&hl=id&z=15&output=embed';
        previewContainer.style.padding = '0';
        previewContainer.innerHTML = 
            '<iframe src="' + embedUrl + '" width="100%" height="100%" style="border:0; border-radius: 15px; min-height: 220px;" allowfullscreen="" loading="lazy"></iframe>';
    } else {
        btn.href = '#';
        btn.classList.add('disabled');
        previewContainer.style.padding = '18px';
        previewContainer.innerHTML =
            '<div><i class="bi bi-geo-alt fs-1 text-primary"></i>' +
            '<div class="mt-2">Klik Ambil Titik GPS atau paste latitude / longitude.</div></div>';
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// MAIN CALCULATOR
// ─────────────────────────────────────────────────────────────────────────────
function hitungLiveScore() {
    const totalMotor   = sumInputs('.motor-input');
    const totalPejalan = sumInputs('.pejalan-input');
    const q1 = getVal('rumah_q1'), q2 = getVal('rumah_q2');
    const q3 = getVal('rumah_q3'), q4 = getVal('rumah_q4');
    const avgCheck = getVal('average_check') || 21000;

    const scoreMotor   = hitungScore('traffic_motor',   totalMotor);
    const scorePejalan = hitungScore('traffic_pejalan', totalPejalan);
    const scoreRumah   = hitungScore('rumah_q1',q1) + hitungScore('rumah_q2',q2)
                       + hitungScore('rumah_q3',q3) + hitungScore('rumah_q4',q4);
    const scoreFasilitas = hitungScore('sekolah',    getVal('sekolah'))
                         + hitungScore('market',     getVal('market'))
                         + hitungScore('perkantoran',getVal('perkantoran'))
                         + hitungScore('kesehatan',  getVal('kesehatan'));
    const scoreKompetitor = hitungScore('kompetitor_geprek', getVal('kompetitor_geprek'))
                          + hitungScore('kompetitor_lokal',  getVal('kompetitor_lokal'));
    
    // Sesuai SOP Spreadsheet: Harga kompetitor tidak memberikan bonus tambahan
    let bonusHarga = 0;

    const totalPenambah  = scoreMotor + scorePejalan + scoreRumah + scoreFasilitas + bonusHarga;
    const totalPengurang = scoreKompetitor;
    const finalScore     = Math.max(0, totalPenambah - totalPengurang);
    const finalPercent   = finalScore * 100;

    // Tipe outlet & threshold
    const tipe   = getTipeOutlet();
    const thresh = THRESHOLD[tipe];

    let rekomendasi = 'REJECTED', statusClass = '';
    if (finalScore >= thresh.approved)           { rekomendasi = 'APPROVED';       statusClass = 'approved'; }
    else if (finalScore >= thresh.consideration) { rekomendasi = 'CONSIDERATION';  statusClass = 'consideration'; }

    // Grade label untuk tabel
    const gMotor   = getGradeLabel('traffic_motor',   totalMotor);
    const gPejalan = getGradeLabel('traffic_pejalan', totalPejalan);

    // Update baris motor
    document.querySelectorAll('.motor-input').forEach(function(input) {
        const row = input.closest('tr'); if (!row) return;
        const val = toNumber(input.value);
        const gc  = row.querySelector('.motor-grade');
        const nc  = row.querySelector('.motor-nilai');
        if (gc) gc.innerText = val > 0 ? gMotor : 0;
        if (nc) nc.innerText = val > 0 ? (scoreMotor*100).toFixed(2)+'%' : '0.00%';
    });

    // Update baris pejalan
    document.querySelectorAll('.pejalan-input').forEach(function(input) {
        const row = input.closest('tr'); if (!row) return;
        const val = toNumber(input.value);
        const gc  = row.querySelector('.pejalan-grade');
        const nc  = row.querySelector('.pejalan-nilai');
        if (gc) gc.innerText = val > 0 ? gPejalan : 0;
        if (nc) nc.innerText = val > 0 ? (scorePejalan*100).toFixed(2)+'%' : '0.00%';
    });

    document.getElementById('totalMotorCell').innerText   = totalMotor.toLocaleString('id-ID');
    document.getElementById('totalPejalanCell').innerText = totalPejalan.toLocaleString('id-ID');
    document.getElementById('motorScoreCell').innerText   = (scoreMotor*100).toFixed(2)+'%';
    document.getElementById('pejalanScoreCell').innerText = (scorePejalan*100).toFixed(2)+'%';

    // Panel kanan
    document.getElementById('finalScoreDisplay').innerText  = finalPercent.toFixed(1)+'%';
    document.getElementById('summaryMotor').innerText        = totalMotor.toLocaleString('id-ID');
    document.getElementById('summaryPejalan').innerText      = totalPejalan.toLocaleString('id-ID');
    document.getElementById('totalPlusDisplay').innerText    = (totalPenambah*100).toFixed(2)+'%';
    document.getElementById('totalMinusDisplay').innerText   = (totalPengurang*100).toFixed(2)+'%';
    document.getElementById('tipeOutletDisplay').innerText   = tipe;
    document.getElementById('thresholdDisplay').innerText    = '≥ '+(thresh.approved*100).toFixed(0)+'%';

    const status = document.getElementById('recommendationDisplay');
    status.innerText  = rekomendasi;
    status.className  = 'score-status ' + statusClass;

    document.getElementById('scoreProgress').style.width = Math.min(100, finalPercent) + '%';

    // Omset calculation matching exactly with Excel logic
    const moe = (document.querySelector('[name="provinsi"]')?.value || '').toLowerCase().includes('madura') ? 0.30 : 0.20;
    
    // 1. Traffic (Expanded to Weekly total: Weekday x 5 + Weekend x 2)
    const m_wd = getVal('motor_weekday_pagi') + getVal('motor_weekday_siang') + getVal('motor_weekday_sore');
    const m_we = getVal('motor_weekend_pagi') + getVal('motor_weekend_siang') + getVal('motor_weekend_sore');
    const totalMotorWeekly = (m_wd * 5) + (m_we * 2);
    
    const p_wd = getVal('pejalan_weekday_pagi') + getVal('pejalan_weekday_siang') + getVal('pejalan_weekday_sore');
    const p_we = getVal('pejalan_weekend_pagi') + getVal('pejalan_weekend_siang') + getVal('pejalan_weekend_sore');
    const totalPejalanWeekly = (p_wd * 5) + (p_we * 2);

    const omsetMotorPerhari = (totalMotorWeekly * RASIO.motor * avgCheck) / 7;
    const omsetPejalanPerhari = (totalPejalanWeekly * RASIO.pejalan * avgCheck) / 7;

    // 2. Rumah Penduduk (Harian = Perminggu / 7)
    const omsetQ1 = (q1 * RASIO.q1 * avgCheck) / 7;
    const omsetQ2 = (q2 * RASIO.q2 * avgCheck) / 7;
    const omsetQ3 = (q3 * RASIO.q3 * avgCheck) / 7;
    const omsetQ4 = (q4 * RASIO.q4 * avgCheck) / 7;

    const subTotalOmset = omsetMotorPerhari + omsetPejalanPerhari + omsetQ1 + omsetQ2 + omsetQ3 + omsetQ4;
    const grandTotalOmset = subTotalOmset * (1 - moe);
    
    document.getElementById('omsetPerhariDisplay').innerText = formatRupiah(grandTotalOmset);

    updateMapsPreview();
    updateJamRamai();
}

// ─────────────────────────────────────────────────────────────────────────────
// GPS
// ─────────────────────────────────────────────────────────────────────────────
function autoFillAlamat(lat, lng) {
    // Gunakan OpenStreetMap Nominatim API (Gratis, tanpa API key)
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            if (data && data.address) {
                const addr = data.address;
                // Lokasi / Jalan
                const jalan = addr.road || addr.pedestrian || addr.suburb || addr.village || addr.neighbourhood || '';
                if (jalan && !document.querySelector('[name="lokasi"]').value) {
                    document.querySelector('[name="lokasi"]').value = jalan;
                }
                
                // Kota / Kabupaten
                const kota = addr.city || addr.town || addr.county || addr.municipality || '';
                if (kota && !document.querySelector('[name="kota"]').value) {
                    document.querySelector('[name="kota"]').value = kota.replace('Kabupaten', 'Kab.');
                }
                
                // Provinsi
                const provinsi = addr.state || '';
                if (provinsi) {
                    document.querySelector('[name="provinsi"]').value = provinsi;
                    // Trigger live score karena provinsi mempengaruhi Margin of Error (MoE)
                    hitungLiveScore();
                }
            }
        })
        .catch(err => console.error("Reverse geocoding error:", err));
}

async function radarFasilitas() {
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    
    if (!lat || !lng) {
        alert('Titik koordinat belum ada! Silakan ambil titik GPS atau paste link Maps terlebih dahulu.');
        return;
    }

    const btn = document.getElementById('btnRadar');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Scanning...';
    btn.disabled = true;

    try {
        const query = `
            [out:json][timeout:25];
            (
              nwr["amenity"~"school|university|kindergarten|college|hospital|clinic|pharmacy|restaurant|fast_food|cafe|food_court"](around:1500,${lat},${lng});
              nwr["shop"~"supermarket|mall|convenience|department_store"](around:1500,${lat},${lng});
              nwr["office"](around:1500,${lat},${lng});
              nwr["name"~"geprek|fried chicken|kfc|mcd|hisana|sabana|olive",i](around:1500,${lat},${lng});
              nwr["landuse"~"industrial"](around:1500,${lat},${lng});
              nwr["man_made"~"works"](around:1500,${lat},${lng});
            );
            out center;
            (
              nwr["building"](around:1000,${lat},${lng});
            );
            out count;
        `;
        
        const response = await fetch('https://overpass-api.de/api/interpreter', {
            method: 'POST',
            body: "data=" + encodeURIComponent(query),
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        });
        
        const data = await response.json();
        
        let sekolah = 0, kampus = 0, pabrik = 0, kesehatan = 0, market = 0, perkantoran = 0, kompetitor = 0, kompetitor_geprek = 0;
        let totalBuildings = 0;

        if (data && data.elements) {
            data.elements.forEach(el => {
                if (el.type === 'count' && el.tags) {
                    totalBuildings = (parseInt(el.tags.nodes) || 0) + (parseInt(el.tags.ways) || 0) + (parseInt(el.tags.relations) || 0);
                    return;
                }

                const tags = el.tags;
                if (!tags) return;
                
                let isGeprek = false;
                if (tags.name && /geprek|fried chicken|kfc|mcd|hisana|sabana|olive/i.test(tags.name)) {
                    isGeprek = true;
                    kompetitor_geprek++;
                }

                if (tags.amenity) {
                    if (['school', 'kindergarten'].includes(tags.amenity)) sekolah++;
                    if (['university', 'college'].includes(tags.amenity)) kampus++;
                    if (['hospital', 'clinic', 'pharmacy', 'doctors'].includes(tags.amenity)) kesehatan++;
                    if (['restaurant', 'fast_food', 'cafe', 'food_court'].includes(tags.amenity)) {
                        if (!isGeprek) kompetitor++; 
                    }
                }
                if (tags.shop) {
                    if (['supermarket', 'mall', 'convenience', 'department_store'].includes(tags.shop)) market++;
                }
                if (tags.office) {
                    perkantoran++;
                }
                if (tags.landuse === 'industrial' || tags.man_made === 'works') {
                    pabrik++;
                }
            });
        }

        // ==========================================
        // GOOGLE PLACES API (FASILITAS SUPER LENGKAP)
        // ==========================================
        try {
            const googleRes = await fetch(`{{ route('investor.surveyor.site-score.scan-places') }}?lat=${lat}&lng=${lng}`);
            const googleData = await googleRes.json();
            
            if (googleData && googleData.error) {
                alert("Pesan dari Google API: " + googleData.error);
            } else if (googleData) {
                // Timpa hasil satelit OSM jika Google menemukan lebih banyak (biasanya Google jauh lebih akurat)
                if (googleData.sekolah > sekolah) sekolah = googleData.sekolah;
                if (googleData.kesehatan > kesehatan) kesehatan = googleData.kesehatan;
                if (googleData.market > market) market = googleData.market;
                if (googleData.kompetitor_geprek > kompetitor_geprek) kompetitor_geprek = googleData.kompetitor_geprek;
            }
        } catch (e) {
            console.error("Google Places API error:", e);
        }

        // ==========================================
        // ALGORITMA ESTIMASI RUMAH (HEURISTIC)
        // ==========================================
        // 1. Asumsi 75% dari total bangunan adalah rumah penduduk (radius 1km)
        let totalHouses = Math.round(totalBuildings * 0.75);
        
        // Fallback jika area belum di-mapping satelit dengan baik (kurang dari 100 rumah)
        if (totalHouses < 100) {
            // Karena mapping satelit OSM di desa sering bolong, kita buat asumsi kepadatan standar
            // Area dengan sekolah/pasar biasanya lebih padat
            totalHouses = 500 + (totalHouses * 10) + (market * 150) + (sekolah * 100); 
        }
        
        // 2. Hitung Indikator "Kekayaan/Keramaian" area
        // Jika banyak Market/Perkantoran/Cafe -> area komersial/elit
        const elitScore = market + perkantoran + (kompetitor * 0.5); 
        
        let pQ1 = 0.10, pQ2 = 0.25, pQ3 = 0.45, pQ4 = 0.20; // Default Suburban
        
        if (elitScore > 20) {
            // Area Sangat Komersial / Elit
            pQ1 = 0.25; pQ2 = 0.35; pQ3 = 0.30; pQ4 = 0.10;
        } else if (elitScore > 10) {
            // Area Menengah
            pQ1 = 0.15; pQ2 = 0.30; pQ3 = 0.40; pQ4 = 0.15;
        } else if (elitScore < 5) {
            // Area Perkampungan Padat / Pinggiran
            pQ1 = 0.05; pQ2 = 0.15; pQ3 = 0.50; pQ4 = 0.30;
        }

        const rumah_q1 = Math.round(totalHouses * pQ1);
        const rumah_q2 = Math.round(totalHouses * pQ2);
        const rumah_q3 = Math.round(totalHouses * pQ3);
        const rumah_q4 = Math.round(totalHouses * pQ4);

        // ==========================================
        // ALGORITMA ESTIMASI TRAFFIC (HEURISTIC)
        // ==========================================
        let baseMotor = 500 + (sekolah * 200) + (market * 300) + (perkantoran * 250);
        
        // Fungsi untuk memberikan variasi acak (+/- 20%) agar tidak terlihat terlalu statis/kaku
        const vary = (val) => Math.round(val * (0.8 + Math.random() * 0.4));
        
        // Distribusi Sepeda Motor
        const motor_wd_pagi = vary(baseMotor * 0.40); // Jam berangkat
        const motor_wd_siang = vary(baseMotor * 0.20); 
        const motor_wd_sore = vary(baseMotor * 0.40); // Jam pulang
        
        const motor_we_pagi = vary(baseMotor * 0.15); // Santai pagi
        const motor_we_siang = vary(baseMotor * 0.35); // Ke mall/pasar
        const motor_we_sore = vary(baseMotor * 0.50); // Hangout sore/malam

        // Pejalan kaki biasanya 5% dari motor. Jika banyak sekolah, bisa naik s/d 15%
        let pedRatio = 0.05 + (sekolah * 0.01) + (market * 0.005);
        if (pedRatio > 0.15) pedRatio = 0.15;
        
        const pejalan_wd_pagi = vary(motor_wd_pagi * pedRatio);
        const pejalan_wd_siang = vary(motor_wd_siang * pedRatio * 1.5); // Siang orang cari makan jalan kaki
        const pejalan_wd_sore = vary(motor_wd_sore * pedRatio);
        
        const pejalan_we_pagi = vary(motor_we_pagi * pedRatio);
        const pejalan_we_siang = vary(motor_we_siang * pedRatio * 1.5);
        const pejalan_we_sore = vary(motor_we_sore * pedRatio * 1.5);

        // Auto-fill form (with visual indicator)
        const setVal = (name, val) => {
            const el = document.querySelector(`[name="${name}"]`);
            if (el) {
                el.value = val;
                el.style.backgroundColor = '#e8f5e9'; // green tint to show it was auto-filled
                setTimeout(() => el.style.backgroundColor = '', 2000);
            }
        };

        setVal('sekolah', sekolah);
        setVal('kampus', kampus);
        setVal('pabrik', pabrik);
        setVal('kesehatan', kesehatan);
        setVal('market', market);
        setVal('perkantoran', perkantoran);
        setVal('kompetitor_lokal', kompetitor);
        setVal('kompetitor_geprek', kompetitor_geprek);
        
        setVal('rumah_q1', rumah_q1);
        setVal('rumah_q2', rumah_q2);
        setVal('rumah_q3', rumah_q3);
        setVal('rumah_q4', rumah_q4);

        setVal('motor_weekday_pagi', motor_wd_pagi);
        setVal('motor_weekday_siang', motor_wd_siang);
        setVal('motor_weekday_sore', motor_wd_sore);
        setVal('motor_weekend_pagi', motor_we_pagi);
        setVal('motor_weekend_siang', motor_we_siang);
        setVal('motor_weekend_sore', motor_we_sore);

        setVal('pejalan_weekday_pagi', pejalan_wd_pagi);
        setVal('pejalan_weekday_siang', pejalan_wd_siang);
        setVal('pejalan_weekday_sore', pejalan_wd_sore);
        setVal('pejalan_weekend_pagi', pejalan_we_pagi);
        setVal('pejalan_weekend_siang', pejalan_we_siang);
        setVal('pejalan_weekend_sore', pejalan_we_sore);

        hitungLiveScore();
        alert(`Radar selesai!\n\nSatelit menemukan ${totalBuildings} bangunan (Est. ${totalHouses} Rumah Penduduk).\nDidistribusikan ke:\n- Q1: ${rumah_q1}, Q2: ${rumah_q2}, Q3: ${rumah_q3}, Q4: ${rumah_q4}\n\nFasilitas:\n- ${sekolah} Sekolah, ${kampus} Kampus\n- ${kesehatan} FasKes\n- ${market} Market\n- ${perkantoran} Perkantoran\n- ${pabrik} Pabrik\n- ${kompetitor_geprek} Kompetitor Geprek\n\nEstimasi Traffic Motor/Pejalan Kaki juga telah diisi!`);

    } catch (err) {
        console.error(err);
        alert('Terjadi kesalahan saat memindai fasilitas. Cek koneksi internet Anda.');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// JAM RAMAI (ESTIMASI DARI DATA TRAFFIC)
// ─────────────────────────────────────────────────────────────────────────────
function synthesizeTrafficCurveJS(pagi, siang, sore) {
    const baseCurve = {
        6: 0.2, 7: 0.4, 8: 0.5, 9: 0.4, 10: 0.5,
        11: 0.7, 12: 1.0, 13: 0.8, 14: 0.6, 15: 0.5,
        16: 0.6, 17: 0.8, 18: 1.0, 19: 0.9, 20: 0.8, 21: 0.6, 22: 0.4, 23: 0.2
    };

    const sumMorning = 2.0;
    const sumNoon    = 3.6;
    const sumEvening = 5.3;
    
    const hourlyData = {};
    for (let h = 6; h <= 23; h++) {
        let count = 0;
        if (h <= 10) count = pagi * (baseCurve[h] / sumMorning);
        else if (h <= 15) count = siang * (baseCurve[h] / sumNoon);
        else count = sore * (baseCurve[h] / sumEvening);
        hourlyData[h] = Math.round(count);
    }
    return hourlyData;
}

function updateJamRamai() {
    const activeDayEl = document.querySelector('.jam-ramai-day.active');
    const day = activeDayEl ? activeDayEl.dataset.day : 'Sen';
    const isWeekend = ['Sab', 'Min'].includes(day);

    // Ambil data traffic input
    const mPagi = getVal(isWeekend ? 'motor_weekend_pagi' : 'motor_weekday_pagi');
    const mSiang = getVal(isWeekend ? 'motor_weekend_siang' : 'motor_weekday_siang');
    const mSore = getVal(isWeekend ? 'motor_weekend_sore' : 'motor_weekday_sore');

    const pPagi = getVal(isWeekend ? 'pejalan_weekend_pagi' : 'pejalan_weekday_pagi');
    const pSiang = getVal(isWeekend ? 'pejalan_weekend_siang' : 'pejalan_weekday_siang');
    const pSore = getVal(isWeekend ? 'pejalan_weekend_sore' : 'pejalan_weekday_sore');

    const motorCurve = synthesizeTrafficCurveJS(mPagi, mSiang, mSore);
    const pejalanCurve = synthesizeTrafficCurveJS(pPagi, pSiang, pSore);

    const hoursData = [];
    let maxVal = 0;

    for (let h = 6; h <= 23; h++) {
        let motor = motorCurve[h];
        let pejalan = pejalanCurve[h];

        if (motor > maxVal) maxVal = motor;
        hoursData.push({ hour: h, motor: motor, pejalan: pejalan });
    }

    const chart = document.getElementById('jamRamaiChart');
    if (!chart) return;
    
    chart.innerHTML = '';
    
    if (maxVal === 0) {
        chart.innerHTML = '<div style="width:100%; text-align:center; color:#9aa0a6; font-size:12px; margin-bottom:10px;">Isi data traffic untuk melihat estimasi jam ramai</div>';
        return;
    }

    hoursData.forEach(d => {
        const heightM = (d.motor / maxVal) * 100;
        const heightP = (d.pejalan / maxVal) * 100; // Relative to max motor
        
        const hourFmt = d.hour.toString().padStart(2, '0') + ':00';
        
        const group = document.createElement('div');
        group.className = 'jam-ramai-bar-group';
        
        const barM = document.createElement('div');
        barM.className = 'jam-ramai-bar';
        barM.style.height = Math.max(2, heightM) + '%';
        
        const barP = document.createElement('div');
        barP.className = 'jam-ramai-bar pejalan';
        barP.style.height = Math.max(0, heightP) + '%';
        
        const tooltip = document.createElement('div');
        tooltip.className = 'jam-ramai-tooltip';
        tooltip.innerText = `${hourFmt}\nMotor: ${d.motor}\nPejalan: ${d.pejalan}`;
        
        group.appendChild(barM);
        group.appendChild(barP);
        group.appendChild(tooltip);
        chart.appendChild(group);
    });
}

function ambilTitikGPS() {
    if (!navigator.geolocation) { alert('Browser tidak mendukung GPS.'); return; }
    navigator.geolocation.getCurrentPosition(function(pos) {
        const lat = pos.coords.latitude.toFixed(7);
        const lng = pos.coords.longitude.toFixed(7);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        updateMapsPreview();
        autoFillAlamat(lat, lng);
    }, function() {
        alert('Gagal mengambil lokasi. Pastikan izin lokasi browser aktif.');
    }, { enableHighAccuracy:true, timeout:10000, maximumAge:0 });
}

// ─────────────────────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.calc-input, #latitude, #longitude').forEach(function(el) {
        el.addEventListener('input', hitungLiveScore);
    });
    
    document.getElementById('maps_url').addEventListener('input', async function(e) {
        const val = e.target.value.trim();
        if (!val) return;

        // Try direct regex first (for long URLs)
        const match = val.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
        if (match) {
            document.getElementById('latitude').value = match[1];
            document.getElementById('longitude').value = match[2];
            updateMapsPreview();
            autoFillAlamat(match[1], match[2]);
            return;
        }

        // If it looks like a Google Maps link but no coordinates found (likely shortlink)
        if (val.includes('maps.app.goo.gl') || val.includes('goo.gl/maps') || val.includes('maps.google.com')) {
            const previewContainer = document.getElementById('mapPreview');
            previewContainer.style.padding = '18px';
            previewContainer.innerHTML = '<div><div class="spinner-border text-primary" role="status"></div><div class="mt-2 fw-bold text-primary">Mengekstrak Koordinat...</div><div class="small">Sedang membaca link Maps</div></div>';
            
            try {
                // Tambahkan timestamp untuk mencegah browser melakukan caching pada response lama
                const cacheBuster = new Date().getTime();
                const response = await fetch(`{{ route('investor.surveyor.site-score.resolve-maps-url') }}?url=${encodeURIComponent(val)}&_t=${cacheBuster}`);
                const data = await response.json();

                if (data.lat && data.lng) {
                    document.getElementById('latitude').value = data.lat;
                    document.getElementById('longitude').value = data.lng;
                    updateMapsPreview();
                    autoFillAlamat(data.lat, data.lng);
                } else {
                    alert('Gagal menemukan titik koordinat dari link tersebut. Mohon isi Latitude & Longitude secara manual.');
                    updateMapsPreview(); // reset view
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat mengekstrak koordinat.');
                updateMapsPreview(); // reset view
            }
        }
    });

    // Setup jam ramai day selector
    document.querySelectorAll('.jam-ramai-day').forEach(el => {
        el.addEventListener('click', function() {
            document.querySelectorAll('.jam-ramai-day').forEach(d => d.classList.remove('active'));
            this.classList.add('active');
            updateJamRamai();
        });
    });

    document.querySelectorAll('[name="tipe_outlet"]').forEach(function(el) {
        el.addEventListener('change', function() {
            setTimeout(hitungLiveScore, 0);
        });
    });
    document.querySelector('[name="harga_kompetitor"]').addEventListener('input', hitungLiveScore);
    hitungLiveScore();
    
    // --- VIDEO DETECTION AI MODAL ---
    let currentAiJobId = null;
    let aiPollInterval = null;

    window.openAiModal = function() {
        const aiModal = new bootstrap.Modal(document.getElementById('aiDetectionModal'));
        document.getElementById('aiDetectionForm').reset();
        document.getElementById('aiDetectionForm').classList.remove('d-none');
        document.getElementById('aiProgressArea').classList.add('d-none');
        document.getElementById('aiResultArea').classList.add('d-none');
        aiModal.show();
    };

    const aiForm = document.getElementById('aiDetectionForm');
    if (aiForm) {
        aiForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const periode = document.getElementById('aiPeriode').value;
            const fileInput = document.getElementById('aiVideoFile');
            
            if (!periode) { alert('Pilih periode pengamatan!'); return; }
            if (!fileInput.files.length) { alert('Pilih video terlebih dahulu!'); return; }
            
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('source_type', 'upload');
            formData.append('video_file', fileInput.files[0]);
            formData.append('lokasi', 'AI Draft Analysis');

            document.getElementById('aiDetectionForm').classList.add('d-none');
            document.getElementById('aiProgressArea').classList.remove('d-none');
            document.getElementById('aiStatusText').innerText = 'Sedang Mengunggah Video...';

            fetch("{{ route('investor.surveyor.video-detection.submit') }}", {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    currentAiJobId = data.job_id;
                    document.getElementById('aiStatusText').innerText = 'Video sedang dianalisis oleh AI...';
                    startAiPolling();
                } else {
                    alert('Gagal mengirim video: ' + (data.message || 'Unknown error'));
                    resetAiModal();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
                resetAiModal();
            });
        });
    }

    function startAiPolling() {
        if (aiPollInterval) clearInterval(aiPollInterval);
        
        aiPollInterval = setInterval(() => {
            if (!currentAiJobId) return;
            
            fetch(`/surveyor/video-detection/status/${currentAiJobId}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'done') {
                    clearInterval(aiPollInterval);
                    document.getElementById('aiProgressArea').classList.add('d-none');
                    document.getElementById('aiResultArea').classList.remove('d-none');
                    
                    if (data.peak_frame_b64) {
                        const img = document.getElementById('resPeakFrame');
                        img.src = 'data:image/jpeg;base64,' + data.peak_frame_b64;
                        img.classList.remove('d-none');
                    } else {
                        document.getElementById('resPeakFrame').classList.add('d-none');
                    }

                    document.getElementById('resMotor').innerText = data.counts?.motorcycle || 0;
                    document.getElementById('resPejalan').innerText = data.counts?.person || 0;
                } else if (data.status === 'failed') {
                    clearInterval(aiPollInterval);
                    alert('Analisis gagal: ' + data.message);
                    resetAiModal();
                }
            })
            .catch(console.error);
        }, 5000);
    }

    function resetAiModal() {
        document.getElementById('aiDetectionForm').classList.remove('d-none');
        document.getElementById('aiProgressArea').classList.add('d-none');
        document.getElementById('aiResultArea').classList.add('d-none');
    }

    window.applyAiResult = function() {
        const periode = document.getElementById('aiPeriode').value; // e.g. "weekday_pagi"
        const motor = document.getElementById('resMotor').innerText;
        const pejalan = document.getElementById('resPejalan').innerText;
        
        const mInput = document.querySelector(`input[name="motor_${periode}"]`);
        const pInput = document.querySelector(`input[name="pejalan_${periode}"]`);
        
        if (mInput) mInput.value = motor;
        if (pInput) pInput.value = pejalan;
        
        hitungLiveScore();
        updateJamRamai();
        
        const aiModalEl = document.getElementById('aiDetectionModal');
        const modal = bootstrap.Modal.getInstance(aiModalEl);
        if (modal) modal.hide();
    };
    // --- END VIDEO DETECTION AI MODAL ---
});
</script>
@endpush

@include('Surveyor.layouts.footer')


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

                        <div class="form-section-band" data-section="traffic">
                            <i class="bi bi-scooter"></i>
                            1. Traffic Sepeda Motor
                            <span id="calibrationBadge" class="badge bg-info ms-2" style="display:none;"></span>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto" onclick="resetKalibrasiTraffic()" style="font-size:11px; border-radius:20px; padding:2px 10px;" title="Reset kalibrasi video agar Radar bisa mengisi ulang">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Kalibrasi
                            </button>
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
                        
                        @include('Surveyor.create.partials._radar_report')

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

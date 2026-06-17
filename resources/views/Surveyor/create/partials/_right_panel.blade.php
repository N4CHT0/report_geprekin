
            {{-- PANEL KANAN --}}
            <div class="right-score-panel">
                
                <!-- SITE SCORE PANEL -->
                <div id="right-panel-sitescore">
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

                <div class="score-card" style="padding: 1.25rem;">
                    <!-- Info Kelayakan -->
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.70rem; letter-spacing: 1px;">Parameter Kelayakan</div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Tipe Outlet</span>
                            <span class="badge bg-primary" id="tipeOutletDisplay">LDP</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Rekomendasi Kelas</span>
                            <span class="badge bg-warning text-dark" id="labelOutletDisplay">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Threshold Approved</span>
                            <b class="text-dark small" id="thresholdDisplay">≥ 60%</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Margin of Error</span>
                            <b class="text-dark small" id="moeDisplay">20%</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1 cursor-pointer" onclick="document.getElementById('breakdownPenambah').style.display = document.getElementById('breakdownPenambah').style.display === 'none' ? 'block' : 'none'">
                            <span class="text-secondary small fw-bold">Total Penambah <i class="bi bi-chevron-down" style="font-size: 0.6rem;"></i></span>
                            <b class="text-success small" id="totalPlusDisplay">0.00%</b>
                        </div>
                        
                        <!-- Breakdown Penambah -->
                        <div id="breakdownPenambah" style="padding-left: 10px; margin-bottom: 8px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Traffic Motor</span>
                                <span class="text-success" style="font-size: 0.70rem;" id="bdMotor">+0.00%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Traffic Pejalan</span>
                                <span class="text-success" style="font-size: 0.70rem;" id="bdPejalan">+0.00%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Kepadatan Residensial</span>
                                <span class="text-success" style="font-size: 0.70rem;" id="bdRumah">+0.00%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Fasilitas Umum & Market</span>
                                <span class="text-success" style="font-size: 0.70rem;" id="bdFasilitas">+0.00%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1" id="bdBonusHargaContainer" style="display: none;">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Bonus Kompetitor Mahal</span>
                                <span class="text-success" style="font-size: 0.70rem;" id="bdBonusHarga">+0.00%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Bonus Fisik Bangunan</span>
                                <span class="text-success" style="font-size: 0.70rem;" id="bdBonusFisik">+0.00%</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-1 cursor-pointer" onclick="document.getElementById('breakdownPengurang').style.display = document.getElementById('breakdownPengurang').style.display === 'none' ? 'block' : 'none'">
                            <span class="text-secondary small fw-bold">Total Pengurang <i class="bi bi-chevron-down" style="font-size: 0.6rem;"></i></span>
                            <b class="text-danger small" id="totalMinusDisplay">0.00%</b>
                        </div>

                        <!-- Breakdown Pengurang -->
                        <div id="breakdownPengurang" style="padding-left: 10px; margin-bottom: 5px;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Kanibalisasi Internal</span>
                                <span class="text-danger" style="font-size: 0.70rem;" id="bdKanibal">-0.00%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Kepadatan Kompetitor</span>
                                <span class="text-danger" style="font-size: 0.70rem;" id="bdKompetitor">-0.00%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1" id="bdMinusHargaContainer" style="display: none;">
                                <span class="text-muted" style="font-size: 0.70rem;">↳ Penalti Harga Kompetitor</span>
                                <span class="text-danger" style="font-size: 0.70rem;" id="bdMinusHarga">-0.00%</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top" id="bdBonusOptimismeContainer" style="display: none;">
                            <span class="text-primary small fw-bold"><i class="bi bi-rocket-takeoff-fill me-1"></i> Faktor Optimisme (Ekstra)</span>
                            <b class="text-success small" id="bdBonusOptimisme">+0.00%</b>
                        </div>
                    </div>

                    <!-- Traffic Efektif -->
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.70rem; letter-spacing: 1px;">Traffic Efektif (Per Hari)</div>
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="text-secondary" style="font-size: 0.75rem;"><i class="bi bi-bicycle"></i> Sepeda Motor</div>
                                    <b class="text-primary" style="font-size: 1.1rem;" id="summaryMotor">0</b>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="text-secondary" style="font-size: 0.75rem;"><i class="bi bi-person-walking"></i> Pejalan Kaki</div>
                                    <b class="text-primary" style="font-size: 1.1rem;" id="summaryPejalan">0</b>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Proyeksi Chicken Unit -->
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.70rem; letter-spacing: 1px;">Proyeksi Chicken Unit (CU)</div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Harian</span>
                            <b class="text-dark small" id="cuHarianDisplay">0 CU</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Mingguan</span>
                            <b class="text-dark small" id="cuMingguanDisplay">0 CU</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-secondary small">Bulanan</span>
                            <b class="text-dark small" id="cuBulananDisplay">0 CU</b>
                        </div>
                    </div>

                    <!-- Proyeksi Omset -->
                    <div>
                        <div class="text-uppercase text-muted fw-bold mb-2" style="font-size: 0.70rem; letter-spacing: 1px;">Proyeksi Omset Total</div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                            <span class="text-secondary small fw-bold">Average Check</span>
                            <b class="text-dark small" id="averageCheckDisplay">Rp 0</b>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Harian</span>
                            <b class="text-dark small" id="omsetPerhariDisplay">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small">Mingguan</span>
                            <b class="text-dark small" id="omsetMingguanDisplay">Rp 0</b>
                        </div>
                        <div class="p-3 rounded text-center mt-3" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #a7f3d0;">
                            <div class="text-success fw-bold mb-1" style="font-size: 0.8rem;">ESTIMASI OMSET BULANAN</div>
                            <b class="text-success" style="font-size: 1.3rem;" id="omsetBulananDisplay">Rp 0</b>
                        </div>
                        
                        <!-- Target Kontribusi Omset -->
                        <div class="mt-3 pt-3 border-top" style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">TARGET KONTRIBUSI (HARIAN)</span>
                                <span class="badge bg-warning text-dark border shadow-sm px-2 py-1 pulse-button" style="font-size: 0.70rem; cursor: pointer;" onclick="document.getElementById('section-konfigurasi').scrollIntoView({behavior: 'smooth'}); if(document.getElementById('config-body').style.display === 'none') toggleConfig();" title="Koreksi Rasio Secara Manual"><i class="bi bi-gear-fill"></i> Sesuaikan Manual</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary small"><i class="bi bi-shop me-1 text-primary"></i> Organik (<span id="labelRasioOrganik">85%</span>)</span>
                                <b class="text-dark small" id="omsetOrganikDisplay">Rp 0</b>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary small"><i class="bi bi-motorcycle me-1 text-success"></i> Online / Ojol (<span id="labelRasioOnline">15%</span>)</span>
                                <b class="text-dark small" id="omsetOjolDisplay">Rp 0</b>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs to submit to database -->
                <input type="hidden" name="cu_per_hari" id="input_cu_perhari" value="0">
                <input type="hidden" name="cu_per_minggu" id="input_cu_perminggu" value="0">
                <input type="hidden" name="cu_per_bulan" id="input_cu_perbulan" value="0">
                <input type="hidden" name="omset_per_hari" id="input_omset_perhari" value="0">
                <input type="hidden" name="omset_per_minggu" id="input_omset_perminggu" value="0">
                <input type="hidden" name="omset_per_bulan" id="input_omset_perbulan" value="0">

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
                </div> <!-- END SITE SCORE PANEL -->

                <!-- RAB & FEASIBILITY PANEL -->
                <div id="right-panel-rab" style="display: none;">
                    <div class="score-card">
                        <div class="score-label mb-2 text-primary" style="font-size: 0.8rem;"><i class="bi bi-wallet2 me-1"></i> Rincian Investasi</div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Kategori Outlet</span>
                            <span class="badge bg-primary" id="panel_kategori_badge">Express</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Sewa Outlet</span>
                            <b class="text-dark small" id="panel_inv_sewa">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Renovasi (BOQ)</span>
                            <b class="text-dark small" id="panel_inv_renovasi">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Aset & Equip.</span>
                            <b class="text-dark small" id="panel_inv_asset">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Marketing</span>
                            <b class="text-dark small" id="panel_inv_marketing">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-secondary small">Lain-lain</span>
                            <b class="text-dark small" id="panel_inv_lain">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold small text-dark">Total Investasi</span>
                            <b class="text-primary fs-5" id="panel_total_investasi">Rp 0</b>
                        </div>
                    </div>

                    <div class="score-card mt-3">
                        <div class="score-label mb-2 text-success" style="font-size: 0.8rem;"><i class="bi bi-bar-chart-fill me-1"></i> Proyeksi Finansial</div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Total OPEX / Bln</span>
                            <b class="text-dark small" id="panel_total_opex">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-secondary small">Gross Margin</span>
                            <b class="text-dark small" id="panel_margin">37%</b>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-1 mt-2">
                            <span class="text-secondary small">Omset / Bulan</span>
                            <b class="text-success small" id="panel_res_omset">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-secondary small">Net Profit / Bln</span>
                            <b class="text-success small" id="panel_res_netprofit">Rp 0</b>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small">BEP Target / Bln</span>
                            <b class="text-dark small" id="panel_res_bep">Rp 0</b>
                        </div>
                        
                        <div class="p-3 rounded text-center mt-3" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); border: none;">
                            <div class="text-white-50 fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">PAYBACK PERIOD (ROI)</div>
                            <b class="text-warning fw-bold" style="font-size: 1.4rem;" id="panel_res_pp">0 Bulan</b>
                        </div>
                    </div>
                </div> <!-- END RAB & FEASIBILITY PANEL -->

            </div>

        </div>
    </div>



<!-- Kalkulator BOQ Modal -->
<div class="modal fade" id="modalBoq" tabindex="-1" aria-labelledby="modalBoqLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            
            <div class="modal-header align-items-center" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: white; padding: 20px 24px; border-bottom: none;">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="modalBoqLabel" style="font-size: 1.25rem;">Kalkulator BOQ Terpusat</h5>
                    <p class="mb-0 text-white-50 small">Sistem akan otomatis menghitung Harga x Kuantitas.</p>
                </div>
                <div class="ms-auto text-end me-4">
                    <span class="d-block small text-white-50">Grand Total RAB</span>
                    <h4 class="mb-0 fw-bold" style="color: #60a5fa;" id="rab_grand_total_display">Rp 0</h4>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0" style="background: #f8fafc;">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-justified px-3 pt-3 border-bottom-0" id="rabTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active fw-bold" data-bs-toggle="tab" data-bs-target="#pane-rombong" type="button">Promosi</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#pane-listrik" type="button">Listrik</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#pane-sanitasi" type="button">Air & Sanitasi</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#pane-partisi" type="button">Partisi & Rak</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#pane-sipil" type="button">Sipil & Cat</button></li>
                    <li class="nav-item"><button class="nav-link fw-bold" data-bs-toggle="tab" data-bs-target="#pane-transport" type="button">Transport</button></li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content p-4" id="rabTabsContent">

                    <div class="tab-pane fade show active" id="pane-rombong" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Rombong Set & Stiker</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="6000000" id="rab_rombong_set" value="0">
                                    <span class="input-group-text bg-light">Rp 6.000.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Neon Sign 80x100</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="2000000" id="rab_neon_sign" value="0">
                                    <span class="input-group-text bg-light">Rp 2.000.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Billboard Rangka Hollow</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="250000" id="rab_billboard" value="0">
                                    <span class="input-group-text bg-light">Rp 250.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Banner Baliho</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="150000" id="rab_banner_baliho" value="0">
                                    <span class="input-group-text bg-light">Rp 150.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Stand Banner 60x100</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="450000" id="rab_stand_banner" value="0">
                                    <span class="input-group-text bg-light">Rp 450.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Rak 1 unit & Meja 3 unit</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="2500000" id="rab_rak_meja" value="0">
                                    <span class="input-group-text bg-light">Rp 2.500.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Rombong Teh Kecil</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_rombong_teh_kecil" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_rombong_teh_kecil" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Rombong Teh Besar</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_rombong_teh_besar" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_rombong_teh_besar" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Banner Is coming</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="90000" id="rab_banner_is_coming" value="0">
                                    <span class="input-group-text bg-light">Rp 90.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Stiker Rombong</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="250000" id="rab_stiker_rombong" value="0">
                                    <span class="input-group-text bg-light">Rp 250.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Stiker Neon Box</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="650000" id="rab_stiker_neon_box" value="0">
                                    <span class="input-group-text bg-light">Rp 650.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Stiker Billboard</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="536500" id="rab_stiker_billboard" value="0">
                                    <span class="input-group-text bg-light">Rp 536.500</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade " id="pane-listrik" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Lampu Hannochs 30 w</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="75000" id="rab_lampu_hannochs" value="0">
                                    <span class="input-group-text bg-light">Rp 75.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Kabel Nyyhy Eterna 2x1.5mm</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_kabel" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_kabel" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pasang Exaust Fan 16"</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="870000" id="rab_exhaust" value="0">
                                    <span class="input-group-text bg-light">Rp 870.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Stop Kontak 1 lb</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="50000" id="rab_sk_1lb" value="0">
                                    <span class="input-group-text bg-light">Rp 50.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Stop Kontak 3 lb</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="35000" id="rab_sk_3lb" value="0">
                                    <span class="input-group-text bg-light">Rp 35.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Steker Arde</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="18000" id="rab_steker" value="0">
                                    <span class="input-group-text bg-light">Rp 18.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Saklar Outbow single</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="25000" id="rab_saklar_single" value="0">
                                    <span class="input-group-text bg-light">Rp 25.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Saklar Outbow double</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="30000" id="rab_saklar_double" value="0">
                                    <span class="input-group-text bg-light">Rp 30.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pasang T DUZ</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="15000" id="rab_tduz" value="0">
                                    <span class="input-group-text bg-light">Rp 15.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pasang MCB 6/10A</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="120000" id="rab_mcb" value="0">
                                    <span class="input-group-text bg-light">Rp 120.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Jasa Instalasi Listrik</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="30000" id="rab_jasa_listrik" value="0">
                                    <span class="input-group-text bg-light">Rp 30.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pek. Fitting</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_fitting" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_fitting" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Meteran Listrik Baru</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_meteran_listrik" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_meteran_listrik" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Tambah Daya 450w-1300w</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_tambah_daya_450" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_tambah_daya_450" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Tambah Daya 900w-1300w</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_tambah_daya_900" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_tambah_daya_900" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade " id="pane-sanitasi" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Zink Cuci Piring Stainless</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="1200000" id="rab_zink_stainless" value="0">
                                    <span class="input-group-text bg-light">Rp 1.200.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Kran Zink cucian 3/4</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="150000" id="rab_kran_zink" value="0">
                                    <span class="input-group-text bg-light">Rp 150.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Instalasi Air Kotor 3"</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="38500" id="rab_air_kotor" value="0">
                                    <span class="input-group-text bg-light">Rp 38.500</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Instalasi Air bersih 3/4"</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="25000" id="rab_air_bersih" value="0">
                                    <span class="input-group-text bg-light">Rp 25.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pemasangan Keni 3"</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="12500" id="rab_keni_3" value="0">
                                    <span class="input-group-text bg-light">Rp 12.500</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pemasangan Keni 3/4</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="7500" id="rab_keni_34" value="0">
                                    <span class="input-group-text bg-light">Rp 7.500</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Zink Cuci Piring Standart</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_zink_standart" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_zink_standart" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pasang Kran KM</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_kran_km" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_kran_km" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pasang Avour KM</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_avour_km" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_avour_km" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pintu KM (Rangka Hollow)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_pintu_km" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_pintu_km" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pembuatan Septic Tank</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_septic_tank" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_septic_tank" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Closed Jongkok</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_closed_jongkok" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_closed_jongkok" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Keramic lantai KM 25x25</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_keramik_km" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_keramik_km" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pemasangan Keni Drat</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="7500" id="rab_keni_drat" value="0">
                                    <span class="input-group-text bg-light">Rp 7.500</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Keni T drat 3/4</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_keni_t_drat" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_keni_t_drat" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Keni 1 1/2 "</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_keni_1_setengah" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_keni_1_setengah" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Instal PDAM</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_instal_pdam" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_instal_pdam" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pembuatan Kamar Mandi</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_pembuatan_km" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_pembuatan_km" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pompa Air Otomatis Shimizu</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_pompa_air" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_pompa_air" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Meteran pembanding</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_meteran_pembanding" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_meteran_pembanding" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Tambah meteran air</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_tambah_meteran_air" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_tambah_meteran_air" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade " id="pane-partisi" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Partisi Penggorengan</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="201784" id="rab_partisi_penggorengan" value="0">
                                    <span class="input-group-text bg-light">Rp 201.784</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Partisi Cucian 90cm</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="100000" id="rab_partisi_cucian" value="0">
                                    <span class="input-group-text bg-light">Rp 100.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Partisi Breading 90cm</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="100000" id="rab_partisi_breading" value="0">
                                    <span class="input-group-text bg-light">Rp 100.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Rak Lunch Box</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="250000" id="rab_rak_lunchbox" value="0">
                                    <span class="input-group-text bg-light">Rp 250.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Partisi Gudang</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="201784" id="rab_partisi_gudang" value="0">
                                    <span class="input-group-text bg-light">Rp 201.784</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Sekat Dinding (GCR 0.6)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_sekat_dinding_1" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_sekat_dinding_1" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Sekat Dinding (GCR Double)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_sekat_dinding_2" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_sekat_dinding_2" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Plafon gypsum</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_plafon_gypsum" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_plafon_gypsum" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade " id="pane-sipil" role="tabpanel">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pengecatan dinding</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="30000" id="rab_cat_dinding" value="0">
                                    <span class="input-group-text bg-light">Rp 30.000</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Dinding bata</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_dinding_bata" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_dinding_bata" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Plester & Acian</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_plester_acian" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_plester_acian" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Rabat Teras</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_rabat_teras" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_rabat_teras" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Pengecatan Folding/Pintu</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_cat_folding" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_cat_folding" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Plamir</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_plamir" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_plamir" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Canopi Kencana</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_canopi" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_canopi" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Roda Kecil Neon Box</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_roda_kecil" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_roda_kecil" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Roda Besar Neon Box</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_roda_besar" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_roda_besar" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Tiang Neon Box 3"</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_tiang_neon_box" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_tiang_neon_box" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Support Neon Box</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_support_neon_box" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_support_neon_box" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Perbaikan pintu</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_perbaikan_pintu" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_perbaikan_pintu" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Perbaikan rolling door</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_perbaikan_rolling_door" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_perbaikan_rolling_door" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Tambah pintu rolling</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" class="form-control rab-input" data-price="custom" id="rab_tambah_pintu_rolling_door" value="0" placeholder="Qty" title="Kuantitas">
                                    <span class="input-group-text px-1">×</span>
                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="rab_tambah_pintu_rolling_door" value="0" placeholder="Harga Satuan" title="Harga per satuan">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="pane-transport" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-muted">Akomodasi & Transportasi</label>
                                <select class="form-select form-select-sm rab-select" id="rab_transport">
                                    <option value="0">Pilih Wilayah...</option>
                                    <option value="300000">SIDOARJO / SURABAYA - Rp 300.000</option>
                                    <option value="750000">SURABAYA / MOJOKERTO / GRESIK / PASURUAN - Rp 750.000</option>
                                    <option value="1125000">BANGKALAN / LAMONGAN / JOMBANG - Rp 1.125.000</option>
                                    <option value="1425000">SAMPANG / KEDIRI / NGANJUK / BOJONEGORO / TUBAN / MALANG / PROBOLINGGO - Rp 1.425.000</option>
                                    <option value="1800000">BLORA / NGAWI / MAGETAN / TRENGGALEK / TULUNGAGUNG / LUMAJANG / PAMEKASAN - Rp 1.800.000</option>
                                    <option value="2250000">REMBANG / PACITAN / SITUBONDO / JEMBER / SUMENEP / BONDOWOSO - Rp 2.250.000</option>
                                    <option value="2700000">BANYUWANGI - Rp 2.700.000</option>
                                    <option value="3000000">PURWODADI / JEPARA / SURAKARTA - Rp 3.000.000</option>
                                    <option value="3450000">SEMARANG / MAGELANG / JOGJA - Rp 3.450.000</option>
                                    <option value="3900000">WONOSOBO / KEBUMEN / PEKALONGAN - Rp 3.900.000</option>
                                    <option value="4200000">PEMALANG / CILACAP / PURWOKERTO - Rp 4.200.000</option>
                                    <option value="4500000">CIREBON / KUNINGAN / TASIKMALAYA - Rp 4.500.000</option>
                                    <option value="5100000">GARUT / SUMEDANG / INDRAMAYU - Rp 5.100.000</option>
                                    <option value="5550000">BANDUNG / PURWAKARTA - Rp 5.550.000</option>
                                    <option value="5850000">BEKASI / SUKABUMI - Rp 5.850.000</option>
                                    <option value="6300000">JAKARTA / DEPOK / BOGOR - Rp 6.300.000</option>
                                    <option value="6600000">CILEGON / BANTEN - Rp 6.600.000</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light" style="padding: 16px 24px; border-top: 1px solid #e2e8f0;">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4 rounded-3 fw-bold shadow-sm" data-bs-dismiss="modal" onclick="applyRabResult()">Gunakan Angka Ini</button>
            </div>
        </div>
    </div>
</div>


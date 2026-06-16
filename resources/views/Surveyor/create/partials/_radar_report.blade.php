<div class="mt-4 d-none" id="radarReportContainer">
    <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden; background: linear-gradient(145deg, #ffffff, #f8fafc);">
        <div class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center py-3" style="background: linear-gradient(90deg, #1e3a8a, #3b82f6);">
            <h6 class="mb-0 fw-bold"><i class="bi bi-radar me-2"></i> Radar Intelligence Report</h6>
            <span class="badge bg-light text-primary" style="font-size: 11px;">Radius 1.5KM</span>
        </div>
        
        <!-- Cannibalization Alert -->
        <div id="cannibalizationAlert" class="alert alert-success m-3 d-none mb-0" style="border-radius: 8px;">
            <div class="d-flex align-items-start">
                <i class="bi bi-shield-check fs-3 me-3" id="canibIcon"></i>
                <div>
                    <h6 class="fw-bold mb-1" id="canibTitle">Status Jaringan Internal: Aman</h6>
                    <p class="mb-0" style="font-size: 12px;" id="canibMessage">Tidak ditemukan cabang Geprekin Aja di zona konflik.</p>
                </div>
            </div>
        </div>

        <!-- Quadrant Profile Alert -->
        <div id="quadrantProfileAlert" class="alert alert-info mx-3 mt-3 mb-0 d-none" style="border-radius: 8px;">
            <div class="d-flex align-items-start">
                <i class="bi bi-compass fs-3 me-3" id="qProfileIcon"></i>
                <div>
                    <h6 class="fw-bold mb-1">Profil Kuadran & Resiko Distribusi</h6>
                    <p class="mb-0" style="font-size: 12px;" id="quadrantProfileMessage">Menganalisis distribusi pasar...</p>
                </div>
            </div>
        </div>

        <div class="card-body p-0 mt-2">
            <div class="accordion accordion-flush" id="accordionRadarReport">
                
                <!-- Pendidikan -->
                <div class="accordion-item bg-transparent border-bottom">
                    <h2 class="accordion-header" id="flush-headingPendidikan">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapsePendidikan" aria-expanded="false" aria-controls="flush-collapsePendidikan">
                            <i class="bi bi-mortarboard text-primary me-2"></i> Fasilitas Pendidikan <span class="badge bg-primary ms-auto" id="count_pendidikan">0</span>
                        </button>
                    </h2>
                    <div id="flush-collapsePendidikan" class="accordion-collapse collapse" aria-labelledby="flush-headingPendidikan" data-bs-parent="#accordionRadarReport">
                        <div class="accordion-body pt-0 pb-3">
                            <ul class="list-group list-group-flush small" id="list_pendidikan">
                                <!-- Lists injected via JS -->
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Modern Market -->
                <div class="accordion-item bg-transparent border-bottom">
                    <h2 class="accordion-header" id="flush-headingMarket">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseMarket" aria-expanded="false" aria-controls="flush-collapseMarket">
                            <i class="bi bi-shop text-success me-2"></i> Modern Market (Minimarket) <span class="badge bg-success ms-auto" id="count_market">0</span>
                        </button>
                    </h2>
                    <div id="flush-collapseMarket" class="accordion-collapse collapse" aria-labelledby="flush-headingMarket" data-bs-parent="#accordionRadarReport">
                        <div class="accordion-body pt-0 pb-3">
                            <ul class="list-group list-group-flush small" id="list_market">
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Bank -->
                <div class="accordion-item bg-transparent border-bottom">
                    <h2 class="accordion-header" id="flush-headingBank">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseBank" aria-expanded="false" aria-controls="flush-collapseBank">
                            <i class="bi bi-bank text-warning me-2"></i> Perbankan <span class="badge bg-warning text-dark ms-auto" id="count_bank">0</span>
                        </button>
                    </h2>
                    <div id="flush-collapseBank" class="accordion-collapse collapse" aria-labelledby="flush-headingBank" data-bs-parent="#accordionRadarReport">
                        <div class="accordion-body pt-0 pb-3">
                            <ul class="list-group list-group-flush small" id="list_bank">
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Kesehatan -->
                <div class="accordion-item bg-transparent border-bottom">
                    <h2 class="accordion-header" id="flush-headingSehat">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseSehat" aria-expanded="false" aria-controls="flush-collapseSehat">
                            <i class="bi bi-hospital text-danger me-2"></i> Fasilitas Kesehatan <span class="badge bg-danger ms-auto" id="count_kesehatan">0</span>
                        </button>
                    </h2>
                    <div id="flush-collapseSehat" class="accordion-collapse collapse" aria-labelledby="flush-headingSehat" data-bs-parent="#accordionRadarReport">
                        <div class="accordion-body pt-0 pb-3">
                            <ul class="list-group list-group-flush small" id="list_kesehatan">
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Kompetitor -->
                <div class="accordion-item bg-transparent">
                    <h2 class="accordion-header" id="flush-headingKomp">
                        <button class="accordion-button collapsed bg-transparent fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseKomp" aria-expanded="false" aria-controls="flush-collapseKomp">
                            <i class="bi bi-crosshair text-secondary me-2"></i> Kompetitor (Ayam Geprek/FC) <span class="badge bg-secondary ms-auto" id="count_kompetitor">0</span>
                        </button>
                    </h2>
                    <div id="flush-collapseKomp" class="accordion-collapse collapse" aria-labelledby="flush-headingKomp" data-bs-parent="#accordionRadarReport">
                        <div class="accordion-body pt-0 pb-3">
                            <ul class="list-group list-group-flush small" id="list_kompetitor">
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

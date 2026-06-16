<!-- FEASIBILITY MODAL -->
<div class="modal fade" id="modalFeasibility" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-graph-up-arrow me-2"></i> Kalkulator Financial Feasibility (ROI & BEP)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light p-4">
                
                <div class="row g-4">
                    <!-- KOLOM KIRI: INVESTASI -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom fw-bold text-success">
                                <i class="bi bi-wallet2 me-1"></i> Rincian Investasi Awal
                            </div>
                            <div class="card-body p-3 fs-6">
                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Kategori Outlet:</span>
                                    <span id="fs_kategori_badge" class="badge bg-primary">Express</span>
                                </div>
                                <hr class="my-2">
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-muted mb-1">Biaya Sewa Outlet</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" id="fs_inv_sewa" class="form-control fs-input" value="0">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-muted mb-1">Renovasi (Dari Kalkulator BOQ)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" id="fs_inv_renovasi" class="form-control fs-input bg-light" readonly value="0">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-muted mb-1">Asset (Building, Equipment, Digital)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" id="fs_inv_asset" class="form-control fs-input" value="0">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-muted mb-1">Marketing Cost</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" id="fs_inv_marketing" class="form-control fs-input" value="0">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-muted mb-1">Lain-lain (Tax, Training, Mobilisasi)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" id="fs_inv_lain" class="form-control fs-input" value="0">
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between align-items-center fw-bold text-dark fs-5">
                                    <span>Total Investasi:</span>
                                    <span id="fs_total_investasi">Rp 0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN: OPEX & HASIL -->
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-header bg-white border-bottom fw-bold text-danger">
                                <i class="bi bi-cart-dash me-1"></i> Biaya Operasional (OPEX) / Bulan
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-muted mb-1">Total OPEX</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" id="fs_total_opex" class="form-control fs-input" value="0">
                                    </div>
                                    <div class="form-text">Gaji, Listrik, Air, Internet, dll.</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold text-muted mb-1">Contribution Margin (%)</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" id="fs_margin" class="form-control fs-input" value="37">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 border-success">
                            <div class="card-body p-4 text-center bg-success text-white rounded">
                                <h5 class="fw-bold mb-3"><i class="bi bi-bar-chart-fill me-1"></i> Proyeksi Finansial</h5>
                                
                                <div class="row text-start mb-3">
                                    <div class="col-6">
                                        <div class="small opacity-75">Estimasi Omset / Bulan:</div>
                                        <div class="fw-bold fs-5" id="fs_res_omset">Rp 0</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small opacity-75">Proyeksi Net Profit:</div>
                                        <div class="fw-bold fs-5" id="fs_res_netprofit">Rp 0</div>
                                    </div>
                                </div>
                                <hr class="opacity-50">
                                <div class="row text-start">
                                    <div class="col-6">
                                        <div class="small opacity-75">Break Even Point (BEP) / Bulan:</div>
                                        <div class="fw-bold fs-6" id="fs_res_bep">Rp 0</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small opacity-75">Payback Period (PP):</div>
                                        <div class="fw-bold fs-4 text-warning" id="fs_res_pp">0 Bulan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<div class="excel-box mb-4">
    <div class="excel-box-header d-flex justify-content-between align-items-center">
        <div>
            <h5>Visibilitas & Akses Fisik</h5>
            <p>Parameter fisik bangunan yang berkontribusi langsung pada Bonus Site Score.</p>
        </div>
        <div>
            <span class="badge bg-success"><i class="bi bi-star-fill"></i> Bonus Max: <span id="maxBonusBadge">15%</span></span>
        </div>
    </div>
    <div class="excel-box-body">
        
        <h6 class="text-primary mb-3"><i class="bi bi-eye"></i> Visibilitas</h6>
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <label class="field-label">Terlihat dr Jalan Utama</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check cell-input" name="terlihat_jalan_utama" id="vis_jl_1" value="1">
                    <label class="btn btn-outline-success" for="vis_jl_1">Ya (Max)</label>
                    <input type="radio" class="btn-check cell-input" name="terlihat_jalan_utama" id="vis_jl_0" value="0">
                    <label class="btn btn-outline-secondary" for="vis_jl_0">Tidak</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="field-label">Posisi Hook</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check cell-input" name="posisi_hook" id="hook_1" value="1">
                    <label class="btn btn-outline-success" for="hook_1">Ya (Max)</label>
                    <input type="radio" class="btn-check cell-input" name="posisi_hook" id="hook_0" value="0">
                    <label class="btn btn-outline-secondary" for="hook_0">Tidak</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="field-label">Terhalang Pohon/Kabel</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check cell-input" name="terhalang_pohon_kabel" id="halang_0" value="0">
                    <label class="btn btn-outline-success" for="halang_0">Tidak (Max)</label>
                    <input type="radio" class="btn-check cell-input" name="terhalang_pohon_kabel" id="halang_1" value="1">
                    <label class="btn btn-outline-danger" for="halang_1">Ya</label>
                </div>
            </div>
        </div>

        <h6 class="text-primary mb-3"><i class="bi bi-signpost-split"></i> Akses & Parkir</h6>
        <div class="row mb-3">
            <div class="col-md-4 mb-3">
                <label class="field-label">Akses Mobil</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check cell-input" name="akses_mobil" id="akses_m_1" value="1">
                    <label class="btn btn-outline-success" for="akses_m_1">🚗 Bisa (Max)</label>
                    <input type="radio" class="btn-check cell-input" name="akses_mobil" id="akses_m_0" value="0">
                    <label class="btn btn-outline-danger" for="akses_m_0">🚫 Sulit</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="field-label">Jenis Jalan</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check cell-input" name="jenis_jalan" id="jln_2" value="2 Arah">
                    <label class="btn btn-outline-success" for="jln_2">2 Arah (Max)</label>
                    <input type="radio" class="btn-check cell-input" name="jenis_jalan" id="jln_1" value="1 Arah">
                    <label class="btn btn-outline-secondary" for="jln_1">1 Arah</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label class="field-label">U-Turn / Lampu Merah</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check cell-input" name="u_turn_lampu_merah" id="uturn_1" value="1">
                    <label class="btn btn-outline-success" for="uturn_1">Dekat</label>
                    <input type="radio" class="btn-check cell-input" name="u_turn_lampu_merah" id="uturn_0" value="0">
                    <label class="btn btn-outline-secondary" for="uturn_0">Jauh</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="field-label">Lebar Jalan (m)</label>
                <input type="number" name="lebar_jalan" class="cell-input yellow-cell" placeholder="Contoh: 6">
                <small class="text-success" style="font-size:11px;">✨ Minimal 6 meter untuk bonus maksimal</small>
            </div>
            <div class="col-md-4 mb-3">
                <label class="field-label">Frontage Lebar Muka (m)</label>
                <input type="number" name="frontage" class="cell-input yellow-cell" placeholder="Contoh: 8">
                <small class="text-success" style="font-size:11px;">✨ Minimal 6 meter untuk bonus parkir</small>
            </div>
            <div class="col-md-4 mb-3">
                <label class="field-label mt-2">Fasilitas Ekstra</label>
                <div class="d-flex gap-3 mt-1">
                    <div class="form-check">
                        <input class="form-check-input cell-input" type="checkbox" name="ruang_signage" value="1" id="chk_signage">
                        <label class="form-check-label" style="font-size:13px;" for="chk_signage">Ada Ruang Signage</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input cell-input" type="checkbox" name="penerangan_malam" value="1" id="chk_penerangan">
                        <label class="form-check-label" style="font-size:13px;" for="chk_penerangan">Penerangan Terang</label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.btn-group .btn {
    font-size: 0.8rem;
    padding: 0.4rem 0.5rem;
}
</style>

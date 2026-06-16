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
                            <div class="field-col-3">
                                <label class="field-label">Kategori Outlet (FS)</label>
                                <select name="kategori_outlet" id="kategori_outlet" class="cell-input yellow-cell" onchange="if(typeof initFeasibility === 'function') initFeasibility()">
                                    <option value="Express" {{ (isset($survey) && $survey->kategori_outlet == 'Express') ? 'selected' : '' }}>Express</option>
                                    <option value="Flagship" {{ (isset($survey) && $survey->kategori_outlet == 'Flagship') ? 'selected' : '' }}>Flagship</option>
                                </select>
                            </div>


                            <div class="field-col-12 mt-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div>
                                        <h6 class="mb-1 fw-bold text-primary">BOQ & Feasibility Study</h6>
                                        <p class="mb-0 text-muted small">Otomatisasi Hitungan Estimasi RAB Renovasi & Analisis Kelayakan</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-primary fw-bold px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBoq">
                                            <i class="bi bi-calculator me-1"></i> Kalkulator RAB
                                        </button>
                                        <button type="button" class="btn btn-success fw-bold px-4 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFeasibility" onclick="if(typeof initFeasibility === 'function') initFeasibility()">
                                            <i class="bi bi-gear me-1"></i> Atur OPEX & Margin
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="field-col-3"><label class="field-label">Status Sewa/Beli</label><select name="status_sewa_beli" class="cell-input yellow-cell"><option value="Sewa">Sewa</option><option value="Beli">Beli</option></select></div>
                            <div class="field-col-3"><label class="field-label">Harga Sewa/Beli (Rp)</label><input type="number" name="harga_sewa" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Lebar Depan (m)</label><input type="number" name="lebar_depan" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Panjang (m)</label><input type="number" name="panjang_bangunan" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Jml Lantai</label><input type="number" name="jumlah_lantai" class="cell-input yellow-cell"></div>
                            <div class="field-col-3"><label class="field-label">Kondisi</label><input type="text" name="kondisi_bangunan" class="cell-input yellow-cell" placeholder="Layak/Renov"></div>
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

<!-- Kalkulator BOQ Modal (Dynamic from Master BOQ) -->
<div class="modal fade" id="modalBoq" tabindex="-1" aria-labelledby="modalBoqLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            
            <div class="modal-header align-items-center" style="background: linear-gradient(135deg, #1e3a8a, #2563eb); color: white; padding: 20px 24px; border-bottom: none;">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="modalBoqLabel" style="font-size: 1.25rem;">Kalkulator BOQ Terpusat (Dinamis)</h5>
                    <p class="mb-0 text-white-50 small">Sistem akan otomatis menghitung Harga x Kuantitas.</p>
                </div>
                <div class="ms-auto text-end me-4">
                    <span class="d-block small text-white-50">Grand Total RAB</span>
                    <h4 class="mb-0 fw-bold" style="color: #60a5fa;" id="rab_grand_total_display">Rp 0</h4>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0" style="background: #f8fafc;">
                @if(isset($masterBoqs) && $masterBoqs->count() > 0)
                    @php
                        $groupedBoqs = $masterBoqs->groupBy('kategori');
                        $firstCategory = $groupedBoqs->keys()->first();
                    @endphp

                    <!-- Nav Tabs -->
                    <ul class="nav nav-tabs nav-justified px-3 pt-3 border-bottom-0" id="rabTabs" role="tablist">
                        @foreach($groupedBoqs as $kategori => $items)
                            <li class="nav-item">
                                <button class="nav-link {{ $kategori == $firstCategory ? 'active' : '' }} fw-bold" data-bs-toggle="tab" data-bs-target="#pane-{{ Str::slug($kategori) }}" type="button">
                                    {{ $kategori }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-4" id="rabTabsContent">
                        @foreach($groupedBoqs as $kategori => $items)
                            <div class="tab-pane fade {{ $kategori == $firstCategory ? 'show active' : '' }}" id="pane-{{ Str::slug($kategori) }}" role="tabpanel">
                                <div class="row g-3 mb-4">
                                    @foreach($items as $item)
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold small text-muted">{{ $item->nama_item }}</label>
                                            <div class="input-group input-group-sm">
                                                @if($item->harga_satuan == 0)
                                                    <input type="number" step="0.01" class="form-control rab-input" data-kategori="{{ $item->kategori }}" data-price="custom" id="{{ $item->slug_id }}" value="0" placeholder="Qty">
                                                    <span class="input-group-text px-1">×</span>
                                                    <input type="number" step="1" class="form-control rab-custom-price" data-target="{{ $item->slug_id }}" value="0" placeholder="Harga Satuan">
                                                @else
                                                    <input type="number" step="0.01" class="form-control rab-input" data-kategori="{{ $item->kategori }}" data-price="{{ $item->harga_satuan }}" id="{{ $item->slug_id }}" value="0">
                                                    <span class="input-group-text bg-light">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-exclamation-triangle fs-1 text-warning mb-3"></i>
                        <h5>Data Master BOQ Kosong</h5>
                        <p>Silakan input data BOQ di menu Admin Master BOQ.</p>
                    </div>
                @endif
            </div>

            <div class="modal-footer" style="background: #e2e8f0; border-top: none;">
                <button type="button" class="btn btn-outline-secondary px-4 rounded-3" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary px-4 rounded-3 fw-bold shadow-sm" data-bs-dismiss="modal" onclick="applyRabResult()">
                    <i class="bi bi-check2-circle me-1"></i> Gunakan Angka Ini
                </button>
            </div>
        </div>
    </div>
</div>

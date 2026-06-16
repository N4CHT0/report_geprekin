{{-- resources/views/Purchasing/recapListPO.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body { font-family: Arial; }
    .card { border-radius: 15px; border: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); }
    .table thead { background-color: #f8f9fa; }
    .table thead th { padding: 15px !important; color: #495057; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
    .table tbody td { padding: 15px !important; color: #333; }
    .form-label { font-weight: 600; color: #555; font-size: 0.9rem; }
    .form-control, .form-select { border-radius: 10px; padding: 10px 14px; border: 1px solid #dee2e6; background: #fdfdfd; }
    .btn-success, .btn-primary { border-radius: 10px; padding: 12px 25px; font-weight: 600; }
    .btn-secondary { border-radius: 10px; padding: 12px 25px; }
    .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
    .section-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    
    /* Tambahan untuk Daftar PO */
    .po-badge { display: inline-flex; align-items: center; background: #eef2ff; color: #4f46e5; border: 1px solid #c7d2fe; padding: 6px 12px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; margin: 4px; }
    .btn-remove-po { background: none; border: none; color: #818cf8; margin-left: 8px; cursor: pointer; padding: 0; }
    .btn-remove-po:hover { color: #ef4444; }
</style>

<main class="app-main">
    <div class="app-content py-4">
        <div class="container-fluid">

            {{-- ── PAGE TITLE & DAFTAR PO ─────────────────────────────────────── --}}
            <div class="d-flex align-items-center gap-3 mb-3">
                <a href="{{ route('scm.pengiriman.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <div>
                    <h4 class="fw-bold mb-0">Rekap & Finalisasi Surat Jalan</h4>
                    <small class="text-muted">Target Rute: <strong class="text-primary">{{ request('route_id') ? 'SESUAI RUTE' : 'MIXED' }}</strong></small>
                </div>
            </div>

            {{-- DAFTAR PO YANG BISA DI-EJECT --}}
            <div class="card p-3 mb-4 shadow-sm border-0">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark small text-uppercase"><i class="fas fa-list-ul me-2"></i>Daftar PO Muatan ({{ count($poIds) }} PO)</span>
                    <span class="text-muted" style="font-size: 11px;">*Klik tanda (x) untuk mengeluarkan PO jika truk kepenuhan</span>
                </div>
                <div class="d-flex flex-wrap" id="poListContainer">
                    {{-- Form ini hanya untuk me-reload halaman rekap jika ada PO yang dihapus --}}
                    <form id="formReloadRekap" action="{{ url()->current() }}" method="POST" class="d-none">
                        @csrf
                        <input type="hidden" name="route_id" value="{{ request('route_id') }}">
                    </form>

                    @foreach($poIds as $id)
                        <div class="po-badge" id="badge-po-{{ $id }}">
                            PO-ID #{{ $id }} 
                            <button type="button" class="btn-remove-po" data-id="{{ $id }}" title="Keluarkan dari muatan">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('scm.finalisasi-sj') }}" method="POST" id="formFinalisasi">
                @csrf
                <div id="hiddenInputsContainer">
                    @foreach($poIds as $id)
                        <input type="hidden" name="po_ids[]" value="{{ $id }}" id="input-po-{{ $id }}">
                    @endforeach
                </div>

                {{-- ══════════════════════════════════════════════════
                SECTION GUDANG — hanya tampil jika ada item gudang
                ══════════════════════════════════════════════════ --}}
                @if($rekapGudang->count() > 0)
                    @php
                        $totalTonaseGudang = $rekapGudang->sum(fn($i) => ($i->total_qty * ($i->berat_per_unit / 1000)));
                    @endphp
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card p-4 h-100 shadow-sm border-start border-primary border-4">
                                <div class="section-header">
                                    <div class="section-icon bg-primary bg-opacity-10">
                                        <i class="fas fa-warehouse text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-primary">Dikirim dari DC / Gudang</h6>
                                        <small class="text-muted">{{ $rekapGudang->count() }} jenis bahan</small>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">Nama Produk</th>
                                                <th class="text-center">Total Qty</th>
                                                <th class="text-center">Satuan</th>
                                                <th class="text-end pe-3">Est. Berat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rekapGudang as $item)
                                                @php
                                                    $subBerat = $item->total_qty * ($item->berat_per_unit / 1000);
                                                @endphp
                                                <tr>
                                                    <td class="ps-3 fw-bold">{{ $item->nama_bahan }}</td>
                                                    <td class="text-center text-primary fw-bold">{{ number_format($item->total_qty, 2) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark border">{{ $item->satuan }}</span>
                                                    </td>
                                                    <td class="text-end pe-3 text-secondary">{{ number_format($subBerat, 2) }} Kg</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card p-4 shadow text-white border-0 mb-3" style="background: linear-gradient(45deg, #4e73df, #224abe);">
                                <h6 class="text-white-50 mb-1">Total Muatan Gudang</h6>
                                <h2 class="fw-bold mb-0">
                                    <span id="labelTotalBerat" data-berat="{{ $totalTonaseGudang }}">{{ number_format($totalTonaseGudang, 2) }}</span>
                                    <small style="font-size:0.45em;">Kg</small>
                                </h2>
                                <div class="mt-2 pt-2 border-top border-white-25 d-flex justify-content-between">
                                    <span class="small">Dalam Ton:</span>
                                    <strong>{{ number_format($totalTonaseGudang / 1000, 3) }} Ton</strong>
                                </div>
                            </div>

                            {{-- Form Driver & Armada dengan PROGRESS BAR --}}
                            <div class="card p-4 shadow-sm border-0">
                                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-truck me-2"></i>Armada DC</h6>
                                
                                <label class="form-label small mb-1">Pilih Driver</label>
                                <select name="driver_id" class="form-select mb-3" required>
                                    <option value="">-- Pilih Supir --</option>
                                    @foreach($driverDaftar as $dr)
                                        <option value="{{ $dr->id }}">{{ $dr->nama_supir }}</option>
                                    @endforeach
                                </select>

                                <label class="form-label small mb-1">Pilih Armada (Nopol) <span class="text-danger">*</span></label>
                                <select name="armada_id" id="selectArmada" class="form-select mb-3" required>
                                    <option value="" data-kapasitas="0">-- Pilih Kendaraan --</option>
                                    @foreach($armadaDaftar as $ar)
                                        {{-- PASTIKAN $ar punya kolom kapasitas_kg, jika belum ada, anggap 0 atau default --}}
                                        <option value="{{ $ar->id }}" data-kapasitas="{{ $ar->kapasitas_kg ?? 3000 }}">
                                            {{ $ar->no_pol }} (Max: {{ number_format($ar->kapasitas_kg ?? 3000) }} Kg)
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Peringatan Kapasitas (Sensor Overload) --}}
                                <div id="kapasitasContainer" style="display: none;">
                                    <div class="d-flex justify-content-between small fw-bold mb-1">
                                        <span class="text-muted">Status Muatan:</span>
                                        <span id="textKapasitasPersen" class="text-primary">0%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div id="barKapasitas" class="progress-bar bg-primary" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <small id="textKapasitasPeringatan" class="text-danger fw-bold d-none mt-2 d-block">
                                        <i class="fas fa-exclamation-triangle me-1"></i> OVERLOAD! Kurangi PO dari daftar di atas.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ══════════════════════════════════════════════════
                SECTION SUPPLIER — hanya tampil jika ada item supplier
                ══════════════════════════════════════════════════ --}}
                @if($rekapSupplier->count() > 0)
                    @php
                        $totalTonaseSupplier = $rekapSupplier->sum(fn($i) => ($i->total_qty * ($i->berat_per_unit / 1000)));
                    @endphp
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card p-4 h-100 shadow-sm border-start border-warning border-4">
                                <div class="section-header">
                                    <div class="section-icon bg-warning bg-opacity-10">
                                        <i class="fas fa-store text-warning"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0 text-warning">Dikirim dari Supplier Langsung</h6>
                                        <small class="text-muted">{{ $rekapSupplier->count() }} jenis bahan · Distribusi via Ekspedisi Vendor</small>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3">Nama Produk</th>
                                                <th class="text-center">Total Qty</th>
                                                <th class="text-center">Satuan</th>
                                                <th class="text-end pe-3">Est. Berat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($rekapSupplier as $item)
                                                @php
                                                    $subBerat = $item->total_qty * ($item->berat_per_unit / 1000);
                                                @endphp
                                                <tr>
                                                    <td class="ps-3 fw-bold">{{ $item->nama_bahan }}</td>
                                                    <td class="text-center text-warning fw-bold">{{ number_format($item->total_qty, 2) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark border">{{ $item->satuan }}</span>
                                                    </td>
                                                    <td class="text-end pe-3 text-secondary">{{ number_format($subBerat, 2) }} Kg</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card p-4 h-100 shadow text-white border-0" style="background: linear-gradient(45deg, #f6c23e, #dda20a);">
                                <h6 class="text-white-75 mb-1">Total Muatan Supplier</h6>
                                <h2 class="fw-bold mb-0 text-dark">
                                    {{ number_format($totalTonaseSupplier, 2) }}
                                    <small style="font-size:0.45em;">Kg</small>
                                </h2>
                                <div class="mt-2 pt-2 border-top border-dark border-opacity-25 d-flex justify-content-between text-dark">
                                    <span class="small">Dalam Ton:</span>
                                    <strong>{{ number_format($totalTonaseSupplier / 1000, 3) }} Ton</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── TOMBOL FINALISASI ──────────────────────────── --}}
                <div class="card p-4 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h6 class="fw-bold mb-1">Ringkasan Eksekusi</h6>
                            <div class="d-flex gap-3 flex-wrap">
                                @if($rekapGudang->count() > 0)
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2"><i class="fas fa-warehouse me-1"></i> SJ Gudang</span>
                                @endif
                                @if($rekapSupplier->count() > 0)
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2"><i class="fas fa-store me-1"></i> SJ Supplier</span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('scm.pengiriman.index') }}" class="btn btn-outline-secondary">Batal</a>
                            <button type="button" class="btn btn-primary px-5 shadow-sm" id="btnFinalisasi">
                                <i class="fas fa-paper-plane me-2"></i> Finalisasi Surat Jalan
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</main>

@push('scripts')
    <script>
        $(document).ready(function() {

            // 1. FUNGSI HAPUS PO & RELOAD REKAP (EJECT)
            $('.btn-remove-po').click(function() {
                let id = $(this).data('id');
                
                // Kalau PO sisa 1, jangan izinkan dihapus, suruh klik batal saja
                if($('.po-badge').length <= 1) {
                    Swal.fire('Tidak Bisa', 'Ini adalah PO terakhir. Jika ingin membatalkan, silakan klik tombol Batal di bawah.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Keluarkan PO ini?',
                    text: 'Sistem akan menghitung ulang total berat barang.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Keluarkan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({title: 'Menghitung Ulang...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});
                        
                        // Hapus input hidden dari formReload
                        $('#input-po-' + id).remove();
                        $('#badge-po-' + id).remove();

                        // Ambil semua ID PO yang tersisa dan masukkan ke formReload
                        let remainingIds = [];
                        $('#hiddenInputsContainer input').each(function() {
                            remainingIds.push($(this).val());
                        });

                        // Pindahkan ID ke form reload
                        $('#formReloadRekap input[name="po_ids[]"]').remove();
                        remainingIds.forEach(function(val) {
                            $('#formReloadRekap').append('<input type="hidden" name="po_ids[]" value="'+val+'">');
                        });

                        // Submit form reload untuk menghitung ulang di Backend
                        $('#formReloadRekap').submit();
                    }
                });
            });

            // 2. FUNGSI SENSOR OVERLOAD ARMADA
            $('#selectArmada').change(function() {
                let kapasitasTruk = parseFloat($(this).find(':selected').data('kapasitas')) || 0;
                let totalBeratBarang = parseFloat($('#labelTotalBerat').data('berat')) || 0;
                
                if(kapasitasTruk > 0) {
                    $('#kapasitasContainer').slideDown();
                    let persen = (totalBeratBarang / kapasitasTruk) * 100;
                    
                    $('#barKapasitas').css('width', Math.min(persen, 100) + '%');
                    $('#textKapasitasPersen').text(persen.toFixed(1) + '%');

                    // Jika OVERLOAD (di atas 100%)
                    if(persen > 100) {
                        $('#barKapasitas').removeClass('bg-primary bg-success').addClass('bg-danger');
                        $('#textKapasitasPersen').removeClass('text-primary').addClass('text-danger');
                        $('#textKapasitasPeringatan').removeClass('d-none');
                        // Opsional: Kunci tombol finalisasi
                        $('#btnFinalisasi').prop('disabled', true).html('<i class="fas fa-ban me-2"></i> Truk Overload');
                    } else {
                        $('#barKapasitas').removeClass('bg-primary bg-danger').addClass('bg-success');
                        $('#textKapasitasPersen').removeClass('text-danger').addClass('text-success');
                        $('#textKapasitasPeringatan').addClass('d-none');
                        // Buka kunci tombol finalisasi
                        $('#btnFinalisasi').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Finalisasi Surat Jalan');
                    }
                } else {
                    $('#kapasitasContainer').slideUp();
                    $('#btnFinalisasi').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Finalisasi Surat Jalan');
                }
            });

            // 3. FUNGSI KONFIRMASI FINALISASI
            $('#btnFinalisasi').on('click', function (e) {
                e.preventDefault();

                const hasGudang = {{ $rekapGudang->count() > 0 ? 'true' : 'false' }};
                const hasSupplier = {{ $rekapSupplier->count() > 0 ? 'true' : 'false' }};

                if (hasGudang) {
                    if (!$('select[name="driver_id"]').val()) {
                        Swal.fire('Perhatian', 'Pilih driver untuk pengiriman dari DC/Gudang.', 'warning');
                        return;
                    }
                    if (!$('select[name="armada_id"]').val()) {
                        Swal.fire('Perhatian', 'Pilih armada untuk pengiriman dari DC/Gudang.', 'warning');
                        return;
                    }
                }

                let pesan = 'Sistem akan membuat:<br><ul class="text-start mt-2">';
                if (hasGudang) pesan += '<li>✅ Surat Jalan Gudang + SO + GD otomatis</li>';
                if (hasSupplier) pesan += '<li>✅ Surat Jalan Supplier + PO SCM otomatis</li>';
                pesan += '</ul>Lanjutkan?';

                Swal.fire({
                    title: 'Konfirmasi Finalisasi',
                    html: pesan,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#4e73df',
                    confirmButtonText: 'Ya, Buat Sekarang!',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (result.isConfirmed) {
                        $('#btnFinalisasi').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');
                        $('#formFinalisasi').submit();
                    }
                });
            });
        });
    </script>
@endpush

@include('Temp.Investor.footer')
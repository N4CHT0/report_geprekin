{{-- resources/views/Purchasing/recapListPO.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --primary-color: #696cff;
        --primary-light: #e7e7ff;
        --border-color: #d9dee3;
        --text-main: #566a7f;
    }

    body { background-color: #f5f5f9; font-family: 'Segoe UI', Tahoma, sans-serif; }

    /* Card Styling */
    .card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.2);
        margin-bottom: 1.5rem;
    }

    /* Table Improvements */
    .table thead { background-color: #f8f9fa; }
    .table thead th {
        padding: 15px 20px !important;
        color: #a1acb8 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.8px;
        border-bottom: 2px solid #eceef1 !important;
    }
    .table tbody td { padding: 15px 20px !important; color: #435971; font-weight: 500; }

    /* Badges & Pills */
    .po-badge {
        display: inline-flex;
        align-items: center;
        background: #f0f0ff;
        color: var(--primary-color);
        border: 1px solid #e1e1ff;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        margin: 4px;
        transition: all 0.2s;
    }
    .btn-remove-po {
        background: none; border: none;
        color: var(--primary-color);
        margin-left: 10px;
        cursor: pointer;
        opacity: 0.7;
    }
    .btn-remove-po:hover { opacity: 1; color: #ff3e1d; }

    /* Buttons */
    .btn-primary {
        background: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
    }
    .btn-primary:hover { background: #5f61e6 !important; }
    
    /* Inputs */
    .form-select { border-radius: 8px; border-color: var(--border-color); padding: 10px; }
    
    /* Progress Bar */
    .progress { height: 10px !important; border-radius: 5px !important; }
    
    /* Section Headers */
    .section-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-right: 15px;
    }
</style>

<main class="app-main">
    <div class="app-content py-4">
        <div class="container-fluid">

            {{-- ── PAGE TITLE ─────────────────────────────────────── --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="{{ route('scm.pengiriman.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                <div>
                    <h4 class="fw-bold mb-0">Rekap & Finalisasi Surat Jalan</h4>
                    <small class="text-muted">{{ count($poIds) }} PO dipilih untuk dimuat</small>
                </div>
            </div>

            {{-- ── DAFTAR PO (FITUR EJECT) ────────────────────────── --}}
            <div class="card p-3 mb-4 shadow-sm border-0 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold text-dark small text-uppercase"><i class="fas fa-box-open me-2"></i>Daftar PO Muatan</span>
                    <span class="text-muted" style="font-size: 11px;">*Klik tanda (x) untuk mengeluarkan PO jika truk overkapasitas</span>
                </div>
                
                <div class="d-flex flex-wrap" id="poListContainer">
                    {{-- Form tersembunyi untuk proses Eject / Hitung Ulang --}}
                    <form id="formReloadRekap" action="{{ url()->current() }}" method="POST" class="d-none">
                        @csrf
                        {{-- Mengirimkan route_id kembali jika ada --}}
                        <input type="hidden" name="route_id" value="{{ request('route_id') }}">
                    </form>

                    {{-- Looping daftar PO dari Controller baru --}}
                    @foreach($selectedPOs as $po)
                        <div class="po-badge" id="badge-po-{{ $po->id }}" title="{{ $po->outlet_name }} - {{ number_format($po->total_berat, 2) }} kg">
                            <span class="me-2">{{ $po->no_po }}</span>
                            <button type="button" class="btn-remove-po d-flex align-items-center justify-content-center" data-id="{{ $po->id }}" title="Keluarkan PO" style="line-height: 1;">
                                <span style="font-size: 1.3rem; font-weight: bold;">&times;</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('scm.finalisasi-sj') }}" method="POST" id="formFinalisasi">
                @csrf
                
                {{-- Kumpulan ID PO yang akan dikirim ke Backend --}}
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
                                                    <td class="text-center text-primary fw-bold">
                                                        {{ number_format($item->total_qty, 2) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark border">{{ $item->satuan }}</span>
                                                    </td>
                                                    <td class="text-end pe-3 text-secondary">{{ number_format($subBerat, 2) }}
                                                        Kg</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card p-4 h-100 shadow text-white border-0"
                                style="background: linear-gradient(45deg, #4e73df, #224abe);">
                                <h6 class="text-white-50 mb-1">Total Muatan Gudang</h6>
                                <h2 class="fw-bold mb-0">
                                    <span id="labelTotalBerat" data-berat="{{ $totalTonaseGudang }}">{{ number_format($totalTonaseGudang, 2) }}</span>
                                    <small style="font-size:0.45em;">Kg</small>
                                </h2>
                                <div class="mt-2 pt-2 border-top border-white-25 d-flex justify-content-between">
                                    <span class="small">Dalam Ton:</span>
                                    <strong>{{ number_format($totalTonaseGudang / 1000, 1) }} Ton</strong>
                                </div>
                                <div class="mt-3 p-3 bg-warning text-dark rounded shadow-sm">
                                    <small class="fw-bold d-block mb-1">
                                        <i class="fas fa-exclamation-circle me-1"></i>PENTING:
                                    </small>
                                    <small style="line-height:1.4; display:block;">
                                        Berat dihitung dari kolom <strong>weight</strong> tiap produk.
                                        Lengkapi di menu Daftar Produk jika belum ada.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Driver & Armada (hanya untuk GUDANG) --}}
                    <div class="card p-4 mb-4 shadow-sm border-0">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-truck me-2"></i>Armada Pengiriman DC
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pilih Driver <span class="text-danger">*</span></label>
                                <select name="driver_id" class="form-select" required>
                                    <option value="">-- Pilih Supir --</option>
                                    @foreach($driverDaftar as $dr)
                                        <option value="{{ $dr->id }}">{{ $dr->nama_supir }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pilih Armada (Nopol) <span class="text-danger">*</span></label>
                                <select name="armada_id" id="selectArmada" class="form-select" required>
                                    <option value="" data-kapasitas="0">-- Pilih Kendaraan --</option>
                                    @foreach($armadaDaftar as $ar)
                                        {{-- Pastikan data armada punya field kapasitas_kg --}}
                                        <option value="{{ $ar->id }}" data-kapasitas="{{ $ar->kapasitas_kg ?? 3000 }}">
                                            {{ $ar->no_pol }} (Max: {{ number_format($ar->kapasitas_kg ?? 3000) }} Kg)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tampilan Sensor Overload --}}
                        <div id="kapasitasContainer" class="mt-4" style="display: none;">
                            <div class="d-flex justify-content-between small fw-bold mb-2">
                                <span class="text-muted">Kapasitas Muatan Terpakai:</span>
                                <span id="textKapasitasPersen" class="text-primary fs-6">0%</span>
                            </div>
                            <div class="progress" style="height: 12px; border-radius: 10px;">
                                <div id="barKapasitas" class="progress-bar bg-primary" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small id="textKapasitasPeringatan" class="text-danger fw-bold d-none mt-2 d-block">
                                <i class="fas fa-exclamation-triangle me-1"></i> OVERLOAD! Beban truk melebihi kapasitas maksimal. Silakan keluarkan beberapa PO dari daftar di atas.
                            </small>
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
                                        <small class="text-muted">{{ $rekapSupplier->count() }} jenis bahan · Distribusi via
                                            Ekspedisi Vendor</small>
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
                                                    <td class="text-center text-warning fw-bold">
                                                        {{ number_format($item->total_qty, 2) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-dark border">{{ $item->satuan }}</span>
                                                    </td>
                                                    <td class="text-end pe-3 text-secondary">{{ number_format($subBerat, 2) }}
                                                        Kg</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card p-4 h-100 shadow text-white border-0"
                                style="background: linear-gradient(45deg, #f6c23e, #dda20a);">
                                <h6 class="text-white-75 mb-1">Total Muatan Supplier</h6>
                                <h2 class="fw-bold mb-0 text-dark">
                                    {{ number_format($totalTonaseSupplier, 2) }}
                                    <small style="font-size:0.45em;">Kg</small>
                                </h2>
                                <div
                                    class="mt-2 pt-2 border-top border-dark border-opacity-25 d-flex justify-content-between text-dark">
                                    <span class="small">Dalam Ton:</span>
                                    <strong>{{ number_format($totalTonaseSupplier / 1000, 3) }} Ton</strong>
                                </div>
                                <div class="mt-3 p-3 bg-white text-dark rounded shadow-sm">
                                    <small class="fw-bold d-block mb-1">
                                        <i class="fas fa-info-circle me-1 text-warning"></i>INFO DOKUMEN:
                                    </small>
                                    <small style="line-height:1.4; display:block; font-size: 0.8rem;">
                                        Barang dikirim langsung oleh masing-masing rekanan vendor. Surat Jalan Ekspedisi &
                                        berkas nota PO SCM akan otomatis terbit terpisah per-Supplier.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- FIX INFO UTAMA: Menampilkan status akurat bahwa Supplier sudah berhasil di-mapping oleh SCM --}}
                    <div class="card p-4 mb-4 shadow-sm border-start border-success border-3 bg-success bg-opacity-10">
                        <div class="d-flex align-items-start gap-3">
                            <div class="mt-1 text-success">
                                <i class="fas fa-check-circle fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-success mb-1">Otoritas Alokasi Supplier Valid</h6>
                                <p class="text-dark small mb-0 opacity-75">
                                    Sistem mendeteksi bahwa komoditas muatan dari pihak ketiga telah dikelompokkan secara
                                    otomatis berdasarkan <strong>ID Supplier</strong> yang ditentukan pada saat persetujuan
                                    SCM. Berkas manifestasi PO logistik siap dipisahkan per vendor.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── TOMBOL FINALISASI ──────────────────────────── --}}
                <div class="card p-4 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h6 class="fw-bold mb-1">Ringkasan</h6>
                            <div class="d-flex gap-3 flex-wrap">
                                @if($rekapGudang->count() > 0)
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                                        <i class="fas fa-warehouse me-1"></i>
                                        SJ Gudang akan dibuat + SO + GD otomatis
                                    </span>
                                @endif
                                @if($rekapSupplier->count() > 0)
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2">
                                        <i class="fas fa-store me-1"></i>
                                        SJ Supplier akan dibuat + PO SCM otomatis
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('scm.pengiriman.index') }}" class="btn btn-outline-secondary">
                                Batal
                            </a>
                            <button type="button" class="btn btn-primary px-5 shadow-sm" id="btnFinalisasi">
                                <i class="fas fa-paper-plane me-2"></i>
                                Finalisasi & Buat Surat Jalan
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

            // ==========================================
            // 1. FUNGSI MENGHAPUS (EJECT) PO DARI MUATAN
            // ==========================================
            $('.btn-remove-po').click(function() {
                let id = $(this).data('id');
                
                // Cegah penghapusan jika hanya tersisa 1 PO
                if($('.po-badge').length <= 1) {
                    Swal.fire('Tidak Bisa', 'Ini adalah PO terakhir. Jika ingin membatalkan semua, silakan klik tombol Batal di bawah.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Keluarkan PO ini?',
                    text: 'Sistem akan memuat ulang halaman untuk menghitung tonase terbaru tanpa PO ini.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    confirmButtonText: 'Ya, Keluarkan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({title: 'Menghitung Ulang...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() }});
                        
                        // Hapus input hidden dari formReload
                        $('#input-po-' + id).remove();
                        $('#badge-po-' + id).remove();

                        // Ambil semua ID PO yang tersisa dari form utama
                        let remainingIds = [];
                        $('#hiddenInputsContainer input').each(function() {
                            remainingIds.push($(this).val());
                        });

                        // Masukkan kembali ke form Reload
                        $('#formReloadRekap input[name="po_ids[]"]').remove();
                        remainingIds.forEach(function(val) {
                            $('#formReloadRekap').append('<input type="hidden" name="po_ids[]" value="'+val+'">');
                        });

                        // Lakukan submit ke route yang sama
                        $('#formReloadRekap').submit();
                    }
                });
            });

            // ==========================================
            // 2. FUNGSI SENSOR OVERLOAD ARMADA
            // ==========================================
            $('#selectArmada').change(function() {
                let kapasitasTruk = parseFloat($(this).find(':selected').data('kapasitas')) || 0;
                let totalBeratBarang = parseFloat($('#labelTotalBerat').data('berat')) || 0;
                
                if(kapasitasTruk > 0) {
                    $('#kapasitasContainer').slideDown();
                    let persen = (totalBeratBarang / kapasitasTruk) * 100;
                    
                    $('#barKapasitas').css('width', Math.min(persen, 100) + '%');
                    $('#textKapasitasPersen').text(persen.toFixed(1) + '%');

                    // Jika OVERLOAD
                    if(persen > 100) {
                        $('#barKapasitas').removeClass('bg-primary bg-success').addClass('bg-danger');
                        $('#textKapasitasPersen').removeClass('text-primary').addClass('text-danger');
                        $('#textKapasitasPeringatan').removeClass('d-none');
                        // Kunci tombol finalisasi
                        $('#btnFinalisasi').prop('disabled', true).html('<i class="fas fa-ban me-2"></i> Truk Overload');
                    } else {
                        $('#barKapasitas').removeClass('bg-primary bg-danger').addClass('bg-success');
                        $('#textKapasitasPersen').removeClass('text-danger').addClass('text-success');
                        $('#textKapasitasPeringatan').addClass('d-none');
                        // Buka kunci
                        $('#btnFinalisasi').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Finalisasi Surat Jalan');
                    }
                } else {
                    $('#kapasitasContainer').slideUp();
                    $('#btnFinalisasi').prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i> Finalisasi Surat Jalan');
                }
            });


            // ==========================================
            // 3. KONFIRMASI SEBELUM SUBMIT FINALISASI
            // ==========================================
            $('#btnFinalisasi').on('click', function (e) {
                e.preventDefault();

                const hasGudang = {{ $rekapGudang->count() > 0 ? 'true' : 'false' }};
                const hasSupplier = {{ $rekapSupplier->count() > 0 ? 'true' : 'false' }};

                // Validasi driver & armada jika ada item gudang
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
                        $('#btnFinalisasi')
                            .prop('disabled', true)
                            .html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');
                        $('#formFinalisasi').submit();
                    }
                });
            });
        });
    </script>
@endpush

@include('Temp.Investor.footer')
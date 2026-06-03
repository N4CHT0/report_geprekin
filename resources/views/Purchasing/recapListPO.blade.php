{{-- resources/views/Purchasing/recapListPO.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    body {
        font-family: Arial;
    }

    .card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .table thead {
        background-color: #f8f9fa;
    }

    .table thead th {
        padding: 15px !important;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        padding: 15px !important;
        color: #333;
    }

    .form-label {
        font-weight: 600;
        color: #555;
        font-size: 0.9rem;
    }

    .form-control,
    .form-select {
        border-radius: 10px;
        padding: 10px 14px;
        border: 1px solid #dee2e6;
        background: #fdfdfd;
    }

    .btn-success,
    .btn-primary {
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
    }

    .btn-secondary {
        border-radius: 10px;
        padding: 12px 25px;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1rem;
    }

    .section-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
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
                    <small class="text-muted">{{ count($poIds) }} PO dipilih</small>
                </div>
            </div>

            <form action="{{ route('scm.finalisasi-sj') }}" method="POST" id="formFinalisasi">
                @csrf
                @foreach($poIds as $id)
                    <input type="hidden" name="po_ids[]" value="{{ $id }}">
                @endforeach

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
                                    {{ number_format($totalTonaseGudang, 2) }}
                                    <small style="font-size:0.45em;">Kg</small>
                                </h2>
                                <div class="mt-2 pt-2 border-top border-white-25 d-flex justify-content-between">
                                    <span class="small">Dalam Ton:</span>
                                    <strong>{{ number_format($totalTonaseGudang / 1000, 3) }} Ton</strong>
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
                    <div class="card p-4 mb-4 shadow-sm">
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
                                <select name="armada_id" class="form-select" required>
                                    <option value="">-- Pilih Kendaraan --</option>
                                    @foreach($armadaDaftar as $ar)
                                        <option value="{{ $ar->id }}">{{ $ar->no_pol }}</option>
                                    @endforeach
                                </select>
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
        // Konfirmasi sebelum submit
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
    </script>
@endpush

@include('Temp.Investor.footer')
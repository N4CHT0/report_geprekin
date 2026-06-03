{{-- resources/views/purchasing/dashboardOutlet.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* CSS FIX UNTUK MASALAH MEPET & TABEL LURUS */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 20px 25px !important;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 20px 25px !important;
    }

    table.dataTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    /* Styling Header Tabel ala Sneat SaaS */
    #supplierTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #2c3e50;
    }

    /* Padding isi row biar lega */
    #supplierTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
    }

    #supplierTable tbody tr {
        transition: all 0.2s;
    }

    /* Efek hover premium dengan indikator warna indigo di kiri */
    #supplierTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    /* Custom Badge Code Mitra */
    .bg-supplier-subtle {
        background-color: #f0f0ff !important;
        color: #696cff !important;
        border: 1px solid #e1e1ff !important;
    }

    .icon-shape {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    /* Select2 dropdown override biar matching */
    .select2-container .select2-dropdown {
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid #f1f4f8;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            
            <!-- ==== HEADER & BREADCRUMB SECTION ==== -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Master Supplier</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Purchasing</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Supplier List</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#syncSupplierModal">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sync Supplier
                                </button>
                                <button type="button" class="btn btn-primary px-3 shadow-sm d-flex align-items-center text-white" 
                                        style="background-color: #696cff; border-color: #696cff;" id="btnBukaTambah">
                                    <i class="bi bi-plus-circle me-1"></i> Add Supplier
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==== FILTER SECTION ==== -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <form action="{{ route('supplier.index') }}" method="GET" id="filterForm">
                        <div class="row align-items-end g-3">
                            <div class="col-md-5 col-lg-4">
                                <label class="small fw-bold mb-2 text-secondary" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <i class="bi bi-filter-left me-1"></i> Filter By Mitra
                                </label>
                                <select name="credential_id" class="form-select form-select-sm select2-mitra" onchange="this.form.submit()">
                                    <option value="">-- Semua Mitra --</option>
                                    @foreach($credentials as $c)
                                    <option value="{{ $c->id }}" {{ request('credential_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->credential_code }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            @if(request('credential_id'))
                            <div class="col-md-3">
                                <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-light border px-3 d-inline-flex align-items-center text-muted" style="height: 31px; font-size: 12px;">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                                </a>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- ==== DATA TABLE CARD ==== -->
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-building me-2" style="color: #696cff;"></i> Supplier Database
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="supplierTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 200px;">Kode Mitra</th>
                                    <th>Nama Supplier</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
    @forelse($suppliers as $s)
    <tr>
        <td>
    <span class="badge bg-pi-subtle border px-3 py-2 fw-bold text-uppercase" style="font-size: 0.75rem; border-radius: 8px; color: #696cff !important;">
        {{ $s->credential_code }}
    </span>
</td>
        <td>
            <div class="d-flex align-items-center">
                <div class="icon-shape bg-light me-3" style="color: #696cff; min-width: 35px;">
                    <i class="bi bi-patch-check"></i>
                </div>
                <div>
                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $s->supplier_name }}</div>
                </div>
            </div>
        </td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 10px;">
                    <li>
                        <a class="dropdown-item rounded-2 btn-edit" href="javascript:void(0)"
                           data-id="{{ $s->id }}" 
                           data-nama="{{ $s->supplier_name }}" 
                           data-alamat="{{ $s->supplier_address ?? '' }}">
                            <i class="bi bi-pencil text-warning me-2"></i> Edit Data
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form id="deleteForm{{ $s->id }}" action="{{ route('supplier.delete', $s->id) }}" method="POST" class="d-block">
                            @csrf
                            @method('DELETE')
                            <a class="dropdown-item rounded-2 text-danger" href="javascript:void(0)" onclick="confirmDelete('{{ $s->id }}')">
                                <i class="bi bi-trash me-2"></i> Delete Supplier
                            </a>
                        </form>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="3" class="text-center py-5 text-muted">
            <div class="py-3">
                <i class="bi bi-filter-circle d-block mb-2 text-muted" style="font-size: 2rem;"></i>
                Silakan pilih mitra di atas untuk menampilkan data supplier.
            </div>
        </td>
    </tr>
    @endforelse
</tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ==== MODAL ADD/EDIT SUPPLIER ==== -->
            <div class="modal fade" id="modalSupplier" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0" style="border-radius: 12px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold" id="titleModal">Tambah Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('supplier.store') }}" method="POST" id="formSupplier">
                            @csrf
                            <input type="hidden" name="id" id="supplier_id">
                            <div class="modal-body px-4 pb-4">
                                <div class="mb-3">
                                    <label class="small fw-bold mb-1">Nama Supplier *</label>
                                    <input type="text" name="nama_supplier" id="in_nama" class="form-control form-control-sm shadow-none" placeholder="Masukkan nama supplier" required>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold mb-1">Alamat</label>
                                    <textarea name="alamat" id="in_alamat" class="form-control shadow-none small" rows="4" placeholder="Tulis alamat lengkap..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                                <button type="submit" class="btn btn-sm btn-primary px-4" style="background-color: #696cff; border-color: #696cff;"><i class="bi bi-save me-1"></i> Simpan</button>
                                <button type="button" class="btn btn-sm btn-danger px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ==== MODAL SYNC CONFIRMATION ==== -->
            <div class="modal fade" id="syncSupplierModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0" style="border-radius: 12px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-down-fill text-primary me-1"></i> Sinkronisasi</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center p-4">
                            <div class="mb-3">
                                <div class="icon-shape bg-light text-primary" style="width: 55px; height: 55px; border-radius: 50%;">
                                    <i class="bi bi-info-circle fs-3"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold text-dark">Mulai tarik data supplier?</h5>
                            <p class="text-muted small">
                                Sistem akan menarik data secara bertahap dari ESB melalui sistem Queue di background latar belakang agar tidak membebani server utama.
                            </p>
                        </div>
                        <div class="modal-footer border-top-0 bg-light justify-content-center p-3">
                            <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                            <form action="{{ route('sync.suppliers') }}" method="POST" id="formActionSync" class="d-inline">
                                @csrf
                                <button type="submit" id="btnConfirmSync" class="btn btn-sm btn-primary px-4" style="background-color: #696cff; border-color: #696cff;">
                                    Ya, Sinkronkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@push('scripts')
<script>
    $(document).ready(function() {
        // Redestroy Datatable jika sudah terinisialisasi sebelumnya
        if ($.fn.DataTable.isDataTable('#supplierTable')) {
            $('#supplierTable').DataTable().destroy();
        }

        // Terapkan DOM layout persis seperti halaman PI yang kamu suka
        $('#supplierTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Supplier...",
                lengthMenu: "_MENU_",
                emptyTable: "Silakan pilih mitra di atas untuk menampilkan data"
            },
            columnDefs: [{
                targets: [2],
                orderable: false
            }]
        });

        // Initialize Select2 Mitra
        $('.select2-mitra').select2({
            placeholder: "Cari Kode Mitra...",
            allowClear: true,
            width: '100%'
        });

        // Handler Edit Mode
        $('.btn-edit').on('click', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const alamat = $(this).data('alamat');

            $('#titleModal').text('Edit Supplier');
            $('#formSupplier').attr('action', "{{ route('supplier.update') }}");
            $('#supplier_id').val(id);
            $('#in_nama').val(nama);
            $('#in_alamat').val(alamat);
            $('#modalSupplier').modal('show');
        });

        // Trigger Add Mode Reset
        $('#btnBukaTambah').on('click', function() {
            $('#titleModal').text('Tambah Supplier');
            $('#formSupplier').attr('action', "{{ route('supplier.store') }}");
            $('#formSupplier')[0].reset();
            $('#supplier_id').val('');
            $('#modalSupplier').modal('show');
        });

        // Loading state Sync Button
        document.getElementById('formActionSync').addEventListener('submit', function() {
            var btn = document.getElementById('btnConfirmSync');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';
        });
    });

    // Custom SweetAlert2 Sweet konfirmasi hapus matching dengan UI Indigo
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data supplier ini akan terhapus permanen dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#ff3e1d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm' + id).submit();
            }
        })
    }
</script>
@endpush

@include('Temp.Investor.footer')
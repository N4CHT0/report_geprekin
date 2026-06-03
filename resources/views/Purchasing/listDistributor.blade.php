{{-- resources/views/purchasing/masterDC.blade.php --}}
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
    #stokTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #2c3e50;
        text-align: left !important;
    }

    /* Padding isi row biar lega */
    #stokTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
        text-align: left !important;
    }

    #stokTable tbody tr {
        transition: all 0.2s;
    }

    /* Efek hover premium dengan indikator warna indigo di kiri */
    #stokTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    .icon-shape {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .td-wrap {
        white-space: normal !important;
        word-break: break-word;
        min-width: 220px;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Master Distributor Center (DC)</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/dashboard-scm" class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">DC List</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center text-white" 
                                    style="background-color: #696cff; border-color: #696cff; height: 32px;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="bi bi-plus-circle me-1"></i> Add DC
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-geo me-2" style="color: #696cff;"></i> Distributor Center Database
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="stokTable">
                            <thead>
                                <tr>
                                    <th style="width: 60px;" class="text-center">No</th>
                                    <th>Nama Warehouse / DC</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($distributors as $dist)
                                <tr>
                                    <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light me-3" style="color: #696cff; min-width: 35px;">
                                                <i class="bi bi-building-gear"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $dist->nama_warehouse }}</div>
                                                <small class="text-muted" style="font-size: 10px;">ID System: #{{ $dist->id }}</small>
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
                                                       data-id="{{ $dist->id }}"
                                                       data-nama="{{ $dist->nama_warehouse }}">
                                                        <i class="bi bi-pencil text-warning me-2"></i> Edit DC
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-danger" href="javascript:void(0)" onclick="confirmDelete('{{ $dist->id }}')">
                                                        <i class="bi bi-trash me-2"></i> Delete DC
                                                    </a>
                                                    <form action="{{ route('dc.delete', $dist->id) }}" method="POST" id="deleteForm{{ $dist->id }}" style="display:none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modalSupplier" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0" style="border-radius: 12px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark" id="titleModal">Tambah DC</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('dc.store') }}" method="POST" id="formSupplier">
                            @csrf
                            <input type="hidden" name="id" id="supplier_id">
                            <div class="modal-body px-4 pb-3">
                                <div class="mb-3">
                                    <label class="small fw-bold mb-1">Nama Warehouse / DC *</label>
                                    <input type="text" name="nama_warehouse" id="in_nama" class="form-control form-control-sm shadow-none" placeholder="Masukkan nama distributor center" required>
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

        </div>
    </div>
</main>

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#stokTable')) {
            $('#stokTable').DataTable().destroy();
        }

        $('#stokTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search DC...",
                lengthMenu: "_MENU_",
            },
            columnDefs: [{
                targets: [0, 2],
                orderable: false
            }]
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Handle mode Edit dengan aman via Event Delegation
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');

            $('#titleModal').text('Edit Informasi DC');
            $('#formSupplier').attr('action', "{{ route('dc.update') }}");
            $('#supplier_id').val(id);
            $('#in_nama').val(nama);
            
            var myModal = new bootstrap.Modal(document.getElementById('modalSupplier'));
            myModal.show();
        });

        // Reset modal saat mau tambah baru via trigger data-bs-target
        $(document).on('click', '[data-bs-target="#modalTambah"]', function() {
            $('#titleModal').text('Tambah DC Baru');
            $('#formSupplier').attr('action', "{{ route('dc.store') }}");
            $('#formSupplier')[0].reset();
            $('#supplier_id').val('');

            var myModal = new bootstrap.Modal(document.getElementById('modalSupplier'));
            myModal.show();
        });
    });
</script>

<script>
    // Clean-up backdrop bug jika modal tertutup kasar
    $(document).ready(function() {
        $('#modalSupplier').on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
        });
    });

    // SweetAlert2 Konfirmasi Hapus Gantikan Confirm Jadul Browser
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data distributor center ini akan terhapus permanen dari sistem!",
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
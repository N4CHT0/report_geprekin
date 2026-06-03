{{-- resources/views/purchasing/masterArmada.blade.php --}}
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

    /* Custom Badge Plat Nomor */
    .bg-plate-subtle {
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
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Master Data Armada</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/dashboard-scm" class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Armada List</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center text-white" 
                                    style="background-color: #696cff; border-color: #696cff; height: 32px;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="bi bi-plus-circle me-1"></i> Add Armada
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-truck me-2" style="color: #696cff;"></i> Fleet Management
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="stokTable">
                            <thead>
                                <tr>
                                    <th style="width: 60px;" class="text-center">No</th>
                                    <th>Nama Armada</th>
                                    <th style="width: 180px;">Nomor Polisi</th>
                                    <th style="width: 180px;">Kapasitas (Ton)</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($armada as $ar)
                                <tr>
                                    <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light me-3" style="color: #696cff; min-width: 35px;">
                                                <i class="bi bi-truck-flatbed"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $ar->nama_armada }}</div>
                                                <small class="text-muted" style="font-size: 10px;">ID Fleet: #{{ $ar->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-plate-subtle border px-2.5 py-1.5 fw-bold text-uppercase" style="font-size: 0.7rem; border-radius: 8px; color: #696cff !important;">
                                            {{ $ar->no_pol }}
                                        </span>
                                    </td>
                                    <td class="fw-medium text-secondary small">
                                        {{ $ar->kapasitas_kg }} Ton
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 10px;">
                                                <li>
                                                    <a class="dropdown-item rounded-2 btn-edit" href="javascript:void(0)"
                                                       data-id="{{ $ar->id }}"
                                                       data-nama_armada="{{ $ar->nama_armada }}"
                                                       data-no_pol="{{ $ar->no_pol }}"
                                                       data-kapasitas_kg="{{ $ar->kapasitas_kg }}">
                                                        <i class="bi bi-pencil text-warning me-2"></i> Edit Fleet
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-danger" href="javascript:void(0)" onclick="confirmDelete('{{ $ar->id }}')">
                                                        <i class="bi bi-trash me-2"></i> Delete Fleet
                                                    </a>
                                                    <form id="deleteForm{{ $ar->id }}" action="{{ route('armada.delete', $ar->id) }}" method="POST" style="display:none;">
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

            <div class="modal fade" id="modalArmada" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0" style="border-radius: 12px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark" id="titleModal">Tambah Armada</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('armada.store') }}" method="POST" id="formArmada">
                            @csrf
                            <input type="hidden" name="id" id="armada_id">
                            <div class="modal-body px-4 pb-3">
                                <div class="mb-3">
                                    <label class="small fw-bold mb-1">Nama Armada *</label>
                                    <input type="text" name="nama_armada" id="in_nama_armada" class="form-control form-control-sm shadow-none" placeholder="Contoh: Truck Hino Wingbox" required>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold mb-1">No. Polisi *</label>
                                    <input type="text" name="no_pol" id="in_no_pol" class="form-control form-control-sm shadow-none" placeholder="Contoh: L 1234 AB" required>
                                </div>
                                <div class="mb-3">
                                    <label class="small fw-bold mb-1">Kapasitas (Ton) *</label>
                                    <input type="text" name="kapasitas_kg" id="in_kapasitas_kg" class="form-control form-control-sm shadow-none" placeholder="Contoh: 5" required>
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
                searchPlaceholder: "Search Fleet...",
                lengthMenu: "_MENU_",
            },
            columnDefs: [{
                targets: [0, 4],
                orderable: false
            }]
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Handle mode Edit dengan Event Delegation yang aman
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            const nama_armada = $(this).data('nama_armada');
            const no_pol = $(this).data('no_pol');
            const kapasitas_kg = $(this).data('kapasitas_kg');

            $('#titleModal').text('Edit Informasi Armada');
            $('#formArmada').attr('action', "{{ route('armada.update') }}");

            // Masukkan data ke input
            $('#armada_id').val(id);
            $('#in_nama_armada').val(nama_armada);
            $('#in_no_pol').val(no_pol);
            $('#in_kapasitas_kg').val(kapasitas_kg);

            // Buka Modal
            var myModal = new bootstrap.Modal(document.getElementById('modalArmada'));
            myModal.show();
        });

        // Reset modal saat mau tambah baru via trigger tombol tambah header
        $(document).on('click', '[data-bs-target="#modalTambah"]', function() {
            $('#titleModal').text('Tambah Armada Baru');
            $('#formArmada').attr('action', "{{ route('armada.store') }}");
            $('#formArmada')[0].reset();
            $('#armada_id').val('');

            var myModal = new bootstrap.Modal(document.getElementById('modalArmada'));
            myModal.show();
        });
    });
</script>

<script>
    // SweetAlert2 Konfirmasi Hapus Gantikan Confirm Kaku Browser
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data armada ini akan dihapus secara permanen!",
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
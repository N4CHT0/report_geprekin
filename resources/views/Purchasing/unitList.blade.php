{{-- resources/views/purchasing/masterUnit.blade.php --}}
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
    #unitTable thead th {
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
    #unitTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
        text-align: left !important;
    }

    #unitTable tbody tr {
        transition: all 0.2s;
    }

    /* Efek hover premium dengan indikator warna indigo di kiri */
    #unitTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    /* Custom Soft Badge untuk Satuan Unit */
    .bg-unit-subtle {
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
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Master Unit</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/dashboard-scm" class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Unit List</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" id="btnSyncUnit" class="btn btn-outline-secondary d-flex align-items-center">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sync dari ESB
                                </button>
                                <button type="button" id="btnTambahUnit" class="btn btn-primary px-3 shadow-sm d-flex align-items-center text-white"
                                    style="background-color: #696cff; border-color: #696cff;">
                                    <i class="bi bi-plus-circle me-1"></i> Add Unit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-box-seam me-2" style="color: #696cff;"></i> Measurement Units
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="unitTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;" class="text-center">No</th>
                                    <th>Nama Unit / Satuan</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($units as $s)
                                <tr>
                                    <td class="text-center text-muted small">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light me-3" style="color: #696cff; min-width: 35px;">
                                                <i class="bi bi-tag"></i>
                                            </div>
                                            <div>
                                                <span class="badge bg-unit-subtle border px-3 py-1.5 fw-bold text-uppercase" style="font-size: 0.75rem; border-radius: 6px; color: #696cff !important;">
                                                    {{ $s->nama_unit }}
                                                </span>
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
                                                        data-nama="{{ $s->nama_unit }}">
                                                        <i class="bi bi-pencil text-warning me-2"></i> Edit Unit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-danger" href="javascript:void(0)" onclick="confirmDelete('{{ $s->id }}')">
                                                        <i class="bi bi-trash me-2"></i> Delete Unit
                                                    </a>
                                                    <form action="{{ route('unit.delete', $s->id) }}" method="POST" id="deleteForm{{ $s->id }}" style="display:none;">
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

            <div class="modal fade" id="modalUnit" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0" style="border-radius: 12px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark" id="titleModal">Tambah Unit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('unit.store') }}" method="POST" id="formUnit">
                            @csrf
                            <input type="hidden" name="id" id="unit_id">
                            <div class="modal-body px-4 pb-3">
                                <div class="mb-3">
                                    <label class="small fw-bold mb-1">Nama Unit / Satuan *</label>
                                    <input type="text" name="nama_unit" id="in_nama" class="form-control form-control-sm shadow-none" placeholder="Contoh: PCS, KG, DUS, PACK" required>
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
        if ($.fn.DataTable.isDataTable('#unitTable')) {
            $('#unitTable').DataTable().destroy();
        }

        // Inisialisasi DataTable ala Sneat DOM layout
        $('#unitTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Unit...",
                lengthMenu: "_MENU_",
            },
            columnDefs: [{
                targets: [0, 2],
                orderable: false
            }]
        });

        // Trigger Modal Tambah Baru
        $('#btnTambahUnit').on('click', function() {
            $('#titleModal').text('Tambah Unit Satuan');
            $('#formUnit').attr('action', "{{ route('unit.store') }}");
            $('#formUnit')[0].reset();
            $('#unit_id').val('');
            $('#modalUnit').modal('show');
        });

        // Trigger Modal Edit Mode
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');

            $('#titleModal').text('Edit Informasi Unit');
            $('#formUnit').attr('action', "{{ route('unit.update') }}");
            $('#unit_id').val(id);
            $('#in_nama').val(nama);
            $('#modalUnit').modal('show');
        });
    });

    // Custom SweetAlert2 Konfirmasi Hapus Senada Nuansa Indigo
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data unit satuan barang ini akan dihapus secara permanen!",
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

<script>
    // Skrip Sinkronisasi Massal via API ESB (Tampilan Progress Bar Diperhalus)
    // Skrip Sinkronisasi Massal via API ESB (Fix Error Null innerHTML)
    document.getElementById('btnSyncUnit').addEventListener('click', async function() {
        let currentPage = 1;
        let totalData = 0;
        let processedSoFar = 0;

        Swal.fire({
            title: 'Sinkronisasi Unit',
            html: `
            <div id="swal-text" class="mb-2">Menghubungkan ke API ESB...</div>
            <div class="progress" style="height: 22px; border-radius: 8px; overflow: hidden;">
                <div id="sync-progress" class="progress-bar progress-bar-striped progress-bar-animated text-white fw-bold" 
                     role="progressbar" style="width: 0%; background-color: #696cff;">0%</div>
            </div>
        `,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            while (true) {
                // Memanggil route sync data unit per halaman
                const response = await fetch("{{ route('units.sync') }}?page=" + currentPage, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json",
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                const result = await response.json();

                if (!response.ok) throw new Error(result.message || 'Gagal memproses halaman ' + currentPage);

                totalData = result.total || 0;
                processedSoFar += result.processed;

                if (totalData === 0) break;

                let progressPercent = Math.min(Math.round((processedSoFar / totalData) * 100), 100);

                // FIX: Cek dulu elemen swal-text ada atau tidak sebelum injeksi innerHTML
                const textContainer = document.getElementById('swal-text');
                if (textContainer) {
                    textContainer.innerHTML = `Memproses halaman <b>${currentPage}</b>...<br>` +
                        `Terambil: ${processedSoFar} dari ${totalData} Unit.`;
                }

                const progressBar = document.getElementById('sync-progress');
                if (progressBar) {
                    progressBar.style.width = progressPercent + '%';
                    progressBar.innerText = progressPercent + '%';
                }

                if (result.processed === 0 || processedSoFar >= totalData) {
                    break;
                }

                currentPage++;
                await new Promise(resolve => setTimeout(resolve, 300));
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: `Total ${processedSoFar} unit telah diperbarui secara instan.`,
                confirmButtonColor: '#696cff',
                confirmButtonText: 'Selesai'
            }).then(() => {
                location.reload();
            });

        } catch (error) {
            console.error(error);
            Swal.fire('Gagal!', error.message, 'error');
        }
    });
</script>
@endpush

@include('Temp.Investor.footer')
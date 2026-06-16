{{-- resources/views/purchasing/masterProductScm.blade.php --}}
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
    #orderTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #2c3e50;
        text-align: left !important; /* Lurus kiri biar rapi */
    }

    /* Padding isi row biar lega */
    #orderTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
        text-align: left !important;
    }

    #orderTable tbody tr {
        transition: all 0.2s;
    }

    /* Efek hover premium dengan indikator warna indigo di kiri */
    #orderTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    /* Custom Badge Default Unit */
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
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Master Data Produk SCM</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/dashboard-scm" class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Product List</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" id="btnSyncProduct" class="btn btn-outline-secondary d-flex align-items-center">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sync API
                                </button>
                                <button type="button" class="btn btn-outline-secondary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="bi bi-upload me-1"></i> Import Excel
                                </button>
                                <a href="{{ route('scm.create-bahan') }}" class="btn btn-primary px-3 shadow-sm d-flex align-items-center text-white" style="background-color: #696cff; border-color: #696cff;">
                                    <i class="bi bi-plus-circle me-1"></i> Add Product
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-box-seam me-2" style="color: #696cff;"></i> Product Catalog
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="orderTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th style="width: 150px;">Default Unit</th>
                                    <th style="width: 120px;">Berat</th>
                                    <th style="width: 180px;" class="text-end">Price (per Unit)</th>
                                    <th style="width: 120px;">Tonase</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($listBahan as $b)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light me-3" style="color: #696cff; min-width: 35px;">
                                                <i class="bi bi-box"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $b->nama_bahan }}</div>
                                                <small class="text-muted" style="font-size: 11px;">Code: {{ $b->product_code ?: '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-unit-subtle border px-2.5 py-1.5 fw-bold text-uppercase" style="font-size: 0.7rem; border-radius: 8px; color: #696cff !important;">
                                            {{ $b->satuan_tampil ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="fw-medium text-secondary small">
                                        {{ number_format($b->weight ?? 0) }} g
                                    </td>
                                    <td class="text-end fw-bold text-dark small">
                                        IDR {{ number_format($b->base_price ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-secondary small fw-medium">
                                        {{ ($b->weight ?? 0) / 1000 }} Kg
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 10px;">
                                                <li>
                                                    <a class="dropdown-item rounded-2" href="{{ route('scm.show-bahan', $b->id) }}">
                                                        <i class="bi bi-eye text-primary me-2"></i> View Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2" href="{{ route('scm.edit-bahan', $b->id) }}">
                                                        <i class="bi bi-pencil text-warning me-2"></i> Edit Product
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-danger" href="javascript:void(0)" onclick="confirmDelete('{{ $b->id }}')">
                                                        <i class="bi bi-trash me-2"></i> Delete Product
                                                    </a>
                                                    <form id="deleteForm{{ $b->id }}" action="{{ route('scm.delete-bahan', $b->id) }}" method="POST" style="display:none;">
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

        </div>
    </div>
</main>

<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-file-earmark-excel text-success me-1"></i> Import Data Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('scm.import-bahan') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 pb-3">
                    <div class="mb-3">
                        <label class="small fw-bold mb-2">Pilih File Excel *</label>
                        <input type="file" name="file" class="form-control shadow-none" required>
                    </div>
                    <div class="p-3 bg-light rounded-3">
                        <small class="text-secondary d-block fw-semibold mb-1" style="font-size: 11px; text-transform: uppercase;">Struktur Kolom Excel:</small>
                        <code class="small text-danger" style="font-size: 11px;">product_name, unit, berat_gram, price, expire_date</code>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                    <button type="submit" class="btn btn-sm btn-success px-4"><i class="bi bi-upload me-1"></i> Import</button>
                    <button type="button" class="btn btn-sm btn-danger px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#orderTable')) {
            $('#orderTable').DataTable().destroy();
        }

        $('#orderTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Product...",
                lengthMenu: "_MENU_",
            },
            columnDefs: [{
                targets: [5],
                orderable: false
            }]
        });
    });
</script>

<script>
    // 1. Fungsi Konfirmasi Hapus SweetAlert2 (Matching Tema Indigo)
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data produk yang dihapus tidak dapat dikembalikan!",
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

    // 2. Logic Sinkronisasi Produk (AJAX per Page - Tanpa Mengubah Fungsi Bawaan)
    document.getElementById('btnSyncProduct').addEventListener('click', async function() {
        Swal.fire({
            title: 'Sinkronisasi Produk',
            html: `
                <div id="swal-text" class="mb-2">Menyiapkan antrean server...</div>
                <div class="progress" style="height: 25px; border-radius: 8px; overflow: hidden;">
                    <div id="prod-progress" class="progress-bar progress-bar-striped progress-bar-animated text-white fw-bold" 
                         role="progressbar" style="width: 0%; background-color: #696cff;">0%</div>
                </div>
                <small class="text-muted mt-2 d-block">Proses berjalan di background server. Aman dari suspend.</small>
            `,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const startResponse = await fetch("{{ route('products.sync') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            const startResult = await startResponse.json();
            if (startResult.status !== 'started') {
                throw new Error(startResult.message || 'Gagal memulai sinkronisasi');
            }

            const pollInterval = setInterval(async () => {
                try {
                    const checkResponse = await fetch("{{ route('products.sync') }}", {
                        method: "GET",
                        headers: {
                            "Accept": "application/json",
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    });

                    const data = await checkResponse.json();

                    if (data.status === 'running' || data.status === 'completed') {
                        let percent = data.percentage || 0;
                        
                        const progressBar = document.getElementById('prod-progress');
                        if (progressBar) {
                            progressBar.style.width = percent + '%';
                            progressBar.innerText = percent + '%';
                        }

                        document.getElementById('swal-text').innerHTML = 
                            `Memproses <b>${data.processed}</b> dari ${data.total} produk...`;

                        if (percent >= 100 || data.status === 'completed') {
                            clearInterval(pollInterval);
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Semua data produk telah disinkronkan.',
                                confirmButtonColor: '#696cff',
                                confirmButtonText: 'Selesai'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    } else if (data.status === 'error') {
                        clearInterval(pollInterval);
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                } catch (pollError) {
                    console.error("Polling error:", pollError);
                }
            }, 2000);

        } catch (error) {
            Swal.fire('Waduh!', error.message, 'error');
        }
    });
</script>
@endpush
@include('Temp.Investor.footer')
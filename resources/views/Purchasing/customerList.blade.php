{{-- resources/views/purchasing/masterCustomer.blade.php --}}
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
    #customerTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #2c3e50;
    }

    /* Padding isi row biar lega */
    #customerTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
    }

    #customerTable tbody tr {
        transition: all 0.2s;
    }

    /* Efek hover premium dengan indikator warna indigo di kiri */
    #customerTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    /* Custom Badge Code Customer & Status Aktif */
    .bg-customer-subtle {
        background-color: #f0f0ff !important;
        color: #696cff !important;
        border: 1px solid #e1e1ff !important;
    }

    .bg-active-subtle {
        background-color: #e8fadf !important;
        color: #71dd37 !important;
        border: 1px solid #d4f5c3 !important;
    }

    .bg-inactive-subtle {
        background-color: #ffe5e5 !important;
        color: #ff3e1d !important;
        border: 1px solid #ffd1d1 !important;
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
        min-width: 200px;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Master Customer</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Purchasing</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Customer List</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <form id="syncForm" action="{{ route('customers.sync') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="button" onclick="confirmSync()" class="btn btn-outline-secondary d-flex align-items-center">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync Customer
                                    </button>
                                </form>
                                <button type="button" class="btn btn-primary px-3 shadow-sm d-flex align-items-center text-white" 
                                        style="background-color: #696cff; border-color: #696cff;" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                    <i class="bi bi-plus-circle me-1"></i> Add Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-people me-2" style="color: #696cff;"></i> Customer List
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="customerTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Code</th>
                                    <th>Nama Customer</th>
                                    <th>Kategori</th>
                                    <th>PIC & Kontak</th>
                                    <th class="td-wrap" style="width: 300px;">Alamat</th>
                                    <th class="text-center" style="width: 100px;">Status</th>
                                    <th class="text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($customers as $c)
                                <tr>
                                    <td>
                                        <span class="badge bg-customer-subtle border px-2.5 py-2 fw-bold text-uppercase" style="font-size: 0.7rem; border-radius: 8px; color: #696cff !important;">
                                            {{ $c->customerCode ?: 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light me-2" style="color: #696cff;">
                                                <i class="bi bi-person-badge"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium small text-dark">{{ $c->customerName }}</div>
                                                <small class="text-muted" style="font-size: 10px;">ID: #{{ $c->customerID }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-secondary fw-medium small">{{ $c->customerCategoryName }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-medium small text-dark">{{ $c->picName ?: '-' }}</div>
                                        <small class="text-muted" style="font-size: 11px;"><i class="bi bi-telephone me-1"></i>{{ $c->picPhone ?: '-' }}</small>
                                    </td>
                                    <td class="td-wrap small text-secondary">
                                        {{ $c->address ?: '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if($c->flagActive)
                                            <span class="badge bg-active-subtle border px-3 py-1.5 fw-bold" style="font-size: 0.7rem; border-radius: 8px; color: #71dd37 !important;">ACTIVE</span>
                                        @else
                                            <span class="badge bg-inactive-subtle border px-3 py-1.5 fw-bold" style="font-size: 0.7rem; border-radius: 8px; color: #ff3e1d !important;">INACTIVE</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 10px;">
                                                <li>
                                                    <a class="dropdown-item rounded-2" href="#">
                                                        <i class="bi bi-eye text-primary me-2"></i> View Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2" href="#">
                                                        <i class="bi bi-pencil text-warning me-2"></i> Edit Customer
                                                    </a>
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

@push('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#customerTable')) {
            $('#customerTable').DataTable().destroy();
        }

        $('#customerTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            order: [[0, 'desc']],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Customer...",
                lengthMenu: "_MENU_",
            },
            columnDefs: [{
                targets: [6],
                orderable: false
            }]
        });
    });

    // SweetAlert untuk Konfirmasi Sync yang Senada dengan UI Indigo
    function confirmSync() {
        Swal.fire({
            title: 'Sinkronisasi Data?',
            text: "Proses ini akan mengambil data terbaru dari API ESB di latar belakang.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Ya, Jalankan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('syncForm').submit();
                
                Swal.fire({
                    title: 'Memproses!',
                    text: 'Proses sinkronisasi telah dijadwalkan.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        })
    }
</script>
@endpush
@include('Temp.Investor.footer')
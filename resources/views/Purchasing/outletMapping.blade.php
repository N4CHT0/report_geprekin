{{-- resources/views/purchasing/outletMapping.blade.php --}}
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
    #outletMappingTable thead th {
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
    #outletMappingTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
        text-align: left !important;
    }

    #outletMappingTable tbody tr {
        transition: all 0.2s;
    }

    /* Efek hover premium dengan indikator warna indigo di kiri */
    #outletMappingTable tbody tr:hover {
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
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Outlet Mapping ke DC
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/dashboard-scm"
                                            class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">
                                        Outlet Mapping</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <form id="formSyncEsb" action="{{ route('location.sync') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                            <button type="button"
                                class="btn btn-outline-secondary btn-sm px-3 shadow-sm d-inline-flex align-items-center gap-1"
                                style="height: 32px;" onclick="confirmSync()">
                                <i class="bi bi-arrow-repeat"></i> Sync Loc
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div
                    class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-diagram-3 me-2" style="color: #696cff;"></i> Regional Mapping Outlet to DC
                    </h6>

                    <!-- FILTER STATUS SETTING OUTLET -->
                    <div class="d-flex align-items-center gap-2" style="min-width: 280px;">
                        <label for="filterStatus" class="fw-semibold text-muted mb-0 flex-shrink-0"
                            style="font-size: 13px;">Status:</label>
                        <select id="filterStatus" class="form-select form-select-sm shadow-none"
                            style="border-radius: 8px; height: 36px;">
                            <option value="all">Semua Outlet</option>
                            <option value="set">Sudah Disetting (Ada DC)</option>
                            <option value="unset">Belum Disetting</option>
                        </select>
                    </div>
                </div>

                <form action="{{ route('admin.mapping.simpan') }}" method="POST">
                    @csrf
                    <div class="card-body px-0 pb-0 pt-0">
                        <div class="table-responsive">
                            <table id="outletMappingTable" class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Nama Outlet</th>
                                        <th style="width: 400px;">Pilih DC (Warehouse)</th>
                                    </tr>
                                </thead>
                                <tbody class="text-dark">
                                    @foreach($outlets as $o)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="icon-shape bg-light me-3"
                                                        style="color: #696cff; min-width: 35px;">
                                                        <i class="bi bi-shop"></i>
                                                    </div>
                                                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;">
                                                        {{ $o->nama_outlet }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $is_selected = (isset($mappingData[$o->id]) && $mappingData[$o->id]->warehouse_id);
                                                @endphp

                                                <select name="mapping[{{ $o->id }}]"
                                                    class="form-select form-select-sm shadow-none select-dc"
                                                    data-status="{{ $is_selected ? 'set' : 'unset' }}"
                                                    style="border-radius: 8px; height: 36px;">
                                                    <option value="">-- Belum Diset --</option>
                                                    @foreach($warehouses as $w)
                                                        <option value="{{ $w->id }}" {{ (isset($mappingData[$o->id]) && $mappingData[$o->id]->warehouse_id == $w->id) ? 'selected' : '' }}>
                                                            {{ $w->nama_warehouse }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top-0 d-flex justify-content-end p-4">
                        <button type="submit" class="btn btn-primary px-5 shadow-sm fw-semibold"
                            style="background-color: #696cff; border-color: #696cff; border-radius: 8px; height: 40px;">
                            <i class="bi bi-save me-1"></i> Simpan Semua Mapping
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>

@push('scripts')
    <script>
        $(document).ready(function () {
            if ($.fn.DataTable.isDataTable('#outletMappingTable')) {
                $('#outletMappingTable').DataTable().destroy();
            }

            // Custom Filter DataTables untuk Status Mapping
            $.fn.dataTable.ext.search.push(
                function (settings, data, dataIndex) {
                    var filterVal = $('#filterStatus').val();
                    if (filterVal === 'all') {
                        return true;
                    }

                    // Mengambil elemen select di dalam baris yang sedang dicek
                    var row = settings.aoData[dataIndex].anCells[1];
                    var currentStatus = $(row).find('.select-dc').val();

                    if (filterVal === 'set') {
                        return currentStatus !== ""; // Kembali true jika sudah di-set (tidak kosong)
                    } else if (filterVal === 'unset') {
                        return currentStatus === ""; // Kembali true jika belum di-set (kosong)
                    }
                    return true;
                }
            );

            var table = $('#outletMappingTable').DataTable({
                responsive: true,
                autoWidth: false,
                dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search Outlet...",
                    lengthMenu: "_MENU_",
                },
                columnDefs: [{
                    targets: [1],
                    orderable: false
                }]
            });

            // Trigger redraw tabel saat filter status diubah
            $('#filterStatus').on('change', function () {
                table.draw();
            });

            // Opsional: Update status secara dinamis jika user mengubah select sebelum klik simpan
            $(document).on('change', '.select-dc', function () {
                table.draw();
            });
        });

        // SweetAlert2 Konfirmasi Sinkronisasi Massal Lokasi ESB
        function confirmSync() {
            Swal.fire({
                title: 'Tarik Data Master?',
                text: "Sistem akan menarik ribuan data dari ESB secara background queue. Lanjutkan?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Ya, Tarik Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formSyncEsb').submit();

                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Antrean sinkronisasi telah dibuat.',
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
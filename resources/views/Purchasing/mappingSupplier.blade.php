{{-- resources/views/purchasing/dashboardOutlet.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* =========================================
       SNEAT/ENTERPRISE THEME ADAPTATION
       ========================================= */
    body {
        background-color: #f5f5f9; /* Warna dasar abu-abu sangat muda */
    }

    /* Kustomisasi DataTables Spacing */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 20px 25px !important;
        font-size: 0.85rem;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 20px 25px !important;
        font-size: 0.85rem;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d9dee3;
        border-radius: 6px;
        padding: 0.3rem 0.75rem;
        margin-left: 0.5rem;
        outline: none;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #d9dee3;
        border-radius: 6px;
        padding: 0.2rem 1.5rem 0.2rem 0.5rem;
    }

    /* Kustomisasi Tabel */
    table.dataTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    #outletMappingTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #566a7f;
        letter-spacing: 1px;
    }

    #outletMappingTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
        color: #697a8d;
        font-size: 0.85rem;
    }

    #outletMappingTable tbody tr {
        transition: all 0.2s ease-in-out;
        background-color: #fff;
    }

    #outletMappingTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    /* Button Utama Indigo */
    .btn-custom-primary {
        background-color: #696cff;
        border-color: #696cff;
        color: #fff;
        box-shadow: 0 0.125rem 0.25rem 0 rgba(105, 108, 255, 0.4);
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-custom-primary:hover {
        background-color: #5f61e6;
        border-color: #5f61e6;
        color: #fff;
        transform: translateY(-1px);
    }

    /* Bagdes & Chips */
    .badge-supplier {
        background-color: #f0f0ff !important;
        color: #696cff !important;
        border: 1px solid #e1e1ff !important;
        font-weight: 600;
        padding: 0.5em 0.8em;
        border-radius: 6px;
        margin: 0.15rem;
        display: inline-block;
        font-size: 11px;
    }

    .badge-empty {
        background-color: #f8f9fa;
        color: #a1acb8;
        border: 1px dashed #d9dee3;
        font-weight: 500;
        padding: 0.5em 0.8em;
        border-radius: 6px;
        font-size: 11px;
    }

    .icon-shape {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* =========================================
       PERBAIKAN SELECT2 MULTIPLE (ANTI MEUMPET & GARIS)
       ========================================= */
    .select2-container .select2-dropdown {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
        border-color: #d9dee3;
        border-radius: 8px;
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
    }

    /* Kotak utamanya diperluas */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d9dee3;
        border-radius: 8px;
        min-height: 44px; /* Ditinggikan agar lega */
        padding: 4px 8px; /* Ruang dalam (padding) ditambah */
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #696cff;
        box-shadow: none;
    }

    /* Kotak tag/chip */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #f0f0ff;
        border: 1px solid #e1e1ff;
        color: #696cff;
        border-radius: 6px;
        padding: 4px 8px; /* Ruang teks tag diperbesar */
        font-size: 0.8rem;
        font-weight: 500;
        margin-top: 5px;
        margin-right: 6px;
    }

    /* HILANGKAN GARIS VERTIKAL JELEK DI X */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        border-right: none !important; /* Fix utamanya di sini */
        color: #696cff;
        margin-right: 6px;
        font-weight: bold;
        padding: 0;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background-color: transparent !important;
        color: #5f61e6;
    }

    /* Paskan posisi garis ketik (kursor) */
    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 7px;
        padding-left: 5px;
        color: #566a7f;
        font-family: inherit;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            {{-- ═══ HEADER ═══ --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.15rem;">Mapping Supplier</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Supply Chain</a></li>
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Outlet Settings</a></li>
                                    <li class="breadcrumb-item active fw-bold" style="color: #696cff;" aria-current="page">Mapping</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2 align-items-center mt-3 mt-md-0">
                            <button class="btn btn-custom-primary btn-sm px-3 py-2 d-flex align-items-center" id="btnTambahBulk">
                                <i class="bi bi-plus-circle me-1"></i> Add Mapping
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ TABLE CARD ═══ --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-link-45deg me-2" style="color: #696cff; font-size: 1.2rem; vertical-align: middle;"></i> 
                        Daftar Mapping Outlet & Supplier
                    </h6>
                </div>
                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="outletMappingTable" class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Nama Outlet</th>
                                    <th style="width: 55%;">Supplier Terdaftar</th>
                                    <th class="text-center" style="width: 15%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outlets as $item)
                                <tr>
                                    <!-- Kolom Outlet -->
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light me-3" style="color: #696cff;">
                                                <i class="bi bi-shop"></i>
                                            </div>
                                            <div class="fw-bold" style="color: #566a7f;">{{ $item->nama_outlet }}</div>
                                        </div>
                                    </td>

                                    <!-- Kolom Supplier -->
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @if(isset($item->daftar_supplier) && trim($item->daftar_supplier) !== '')
                                                @foreach(explode(', ', $item->daftar_supplier) as $sup)
                                                    <span class="badge-supplier">{{ $sup }}</span>
                                                @endforeach
                                            @else
                                                <span class="badge-empty"><i class="bi bi-dash me-1"></i>Belum ada supplier</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Kolom Aksi -->
                                    <td class="text-center pe-4">
                                        <button class="btn btn-sm btn-light text-primary border-0 btn-edit-mapping d-inline-flex align-items-center justify-content-center"
                                            style="background-color: #f0f0ff; color: #696cff !important; border-radius: 6px; padding: 0.4rem 0.75rem;"
                                            data-id="{{ $item->id }}"
                                            data-nama="{{ $item->nama_outlet }}">
                                            <i class="bi bi-pencil-square me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══ MODAL MAPPING ═══ --}}
            <div class="modal fade" id="modalMapping" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);">
                        <form id="formMappingSupplier">
                            @csrf
                            <input type="hidden" name="mode" id="formMode" value="bulk">

                            <div class="modal-header border-bottom-0 pt-4 px-4 pb-2">
                                <h5 class="modal-title fw-bold" id="modalTitle" style="color: #2c3e50;">Mapping Supplier</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            
                            <div class="modal-body px-4 pb-4">
                                <div class="card border shadow-none" style="background-color: #fcfdfd;">
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <label class="fw-bold mb-2" style="font-size: 11px; color: #566a7f; text-transform: uppercase;">
                                                    <i class="bi bi-1-circle me-1" style="color: #696cff;"></i> Pilih Outlet
                                                </label>
                                                <select name="outlet_ids[]" id="selectOutlet" class="form-control" multiple="multiple">
                                                    @foreach($outlets as $o)
                                                    <option value="{{ $o->id }}">{{ $o->nama_outlet }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted mt-2 d-block" id="infoOutlet" style="font-size: 11px;">
                                                    <i class="bi bi-info-circle me-1"></i>Bisa pilih lebih dari satu outlet.
                                                </small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="fw-bold mb-2" style="font-size: 11px; color: #566a7f; text-transform: uppercase;">
                                                    <i class="bi bi-2-circle me-1" style="color: #696cff;"></i> Pilih Supplier
                                                </label>
                                                <select name="supplier_ids[]" id="selectSupplier" class="form-control" multiple="multiple">
                                                    @foreach($allSuppliers as $s)
                                                    <option value="{{ $s->id }}">{{ $s->supplier_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer border-top-0 px-4 pb-4">
                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                                <button type="submit" class="btn btn-custom-primary px-4" id="btnSimpan">
                                    <i class="bi bi-save me-1"></i> Save Mapping
                                </button>
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
        // 1. Inisialisasi Select2 Sekali Saja
        $('#selectOutlet, #selectSupplier').select2({
            placeholder: "Pilih data...",
            allowClear: true,
            dropdownParent: $('#modalMapping'),
            width: '100%'
        });

        // 2. Inisialisasi DataTable dengan Layout (dom) khusus
        $('#outletMappingTable').DataTable({
            responsive: true,
            pageLength: 10,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center"lf>rt<"d-flex flex-wrap justify-content-between align-items-center"ip>',
            language: {
                search: "",
                searchPlaceholder: "Search outlet...",
                lengthMenu: "_MENU_",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No data available",
                paginate: {
                    first: "«",
                    last: "»",
                    next: "›",
                    previous: "‹"
                }
            },
            columnDefs: [{
                targets: [2], // Kolom action tidak perlu bisa disortir
                orderable: false
            }]
        });

        // 3. Tombol ADD MAPPING (BULK)
        $('#btnTambahBulk').on('click', function() {
            $('#formMappingSupplier')[0].reset();
            $('#formMode').val('bulk'); 
            
            $('#modalTitle').html('Massal Mapping Outlet & Supplier');
            $('#infoOutlet').show();

            // Kosongkan pilihan Select2
            $('#selectOutlet').val(null).trigger('change');
            $('#selectSupplier').val(null).trigger('change');

            $('#modalMapping').modal('show');
        });

        // 4. Tombol EDIT (Per Baris)
        $(document).on('click', '.btn-edit-mapping', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');

            $('#formMode').val('edit'); 
            $('#modalTitle').html('Edit Mapping: <span style="color: #696cff;">' + nama + '</span>');
            $('#infoOutlet').hide(); // Sembunyikan info bulk saat edit satuan

            // Ambil data via AJAX
            $.get(`/mapping-supplier/edit/${id}`, function(data) {
                // Set Outlet yang dipilih (hanya satu)
                $('#selectOutlet').val([id]).trigger('change');

                // Set Supplier yang sudah terdaftar sebelumnya
                $('#selectSupplier').val(data.selected_ids).trigger('change');

                $('#modalMapping').modal('show');
            });
        });

        // 5. Proses Simpan AJAX
        $('#formMappingSupplier').on('submit', function(e) {
            e.preventDefault();

            const btn = $('#btnSimpan');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

            $.ajax({
                url: "{{ route('simpan.mapping.supplier') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    Swal.fire({
                        title: 'Success!',
                        text: res.msg,
                        icon: 'success',
                        confirmButtonColor: '#696cff'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(err) {
                    Swal.fire({
                        title: 'Failed',
                        text: 'Terjadi kesalahan saat menyimpan data',
                        icon: 'error',
                        confirmButtonColor: '#ff3e1d'
                    });
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
</script>
@endpush

@include('Temp.Investor.footer')
{{-- resources/views/scm/pricelist.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* =========================================
       SNEAT/ENTERPRISE THEME ADAPTATION
       ========================================= */
    body {
        background-color: #f5f5f9;
    }

    /* DataTables Spacing & Styling */
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

    /* Tabel Kustom */
    table.dataTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    #pricelistTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #566a7f;
        letter-spacing: 1px;
        vertical-align: middle;
    }

    #pricelistTable tbody td {
        padding: 1rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
        color: #697a8d;
        font-size: 0.85rem;
    }

    #pricelistTable tbody tr {
        transition: all 0.2s ease-in-out;
        background-color: #fff;
    }

    #pricelistTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    /* Elemen Kustom */
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

    .icon-shape {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .price-ho {
        color: #696cff;
        font-weight: 700;
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: -0.5px;
    }

    .price-mitra {
        color: #16a34a;
        font-weight: 700;
        font-family: 'Courier New', Courier, monospace;
        letter-spacing: -0.5px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
    }

    /* Select2 Tweaks */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #d9dee3;
        border-radius: 6px;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        color: #566a7f;
        padding-left: 14px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
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
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.15rem;">Master Pricelist SCM
                            </h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#"
                                            class="text-decoration-none text-muted">Supply Chain</a></li>
                                    <li class="breadcrumb-item"><a href="#"
                                            class="text-decoration-none text-muted">Master Data</a></li>
                                    <li class="breadcrumb-item active fw-bold" style="color: #696cff;"
                                        aria-current="page">Pricelist</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2 align-items-center mt-3 mt-md-0">
                            <button class="btn btn-outline-secondary btn-sm px-3 py-2 d-flex align-items-center"
                                style="border-radius: 8px;">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export
                            </button>
                            <input type="file" id="fileExcel" class="d-none" accept=".xlsx, .xls">

                            <button type="button"
                                class="btn btn-outline-success btn-sm px-3 py-2 d-flex align-items-center"
                                style="border-radius: 8px;" onclick="$('#fileExcel').click()">
                                <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
                            </button>
                            <button class="btn btn-custom-primary btn-sm px-3 py-2 d-flex align-items-center"
                                id="btnBukaModalCreate">
                                <i class="bi bi-plus-circle me-1"></i> Add Pricelist
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ TABLE CARD ═══ --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-tags me-2"
                            style="color: #696cff; font-size: 1.2rem; vertical-align: middle;"></i>
                        Daftar Harga Jual SCM
                    </h6>
                </div>
                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="pricelistTable" class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 30%;">Nama Bahan / Produk</th>
                                    <th class="text-center" style="width: 15%;">Satuan</th>
                                    <th class="text-end" style="width: 20%;">
                                        Harga Supply (HO)
                                    </th>
                                    <th class="text-end" style="width: 20%;">
                                        Harga Jual (Mitra)
                                    </th>
                                    <th class="text-center" style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pricelists as $index => $item)
                                    <tr>
                                        <td class="text-center text-muted">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon-shape bg-light me-3" style="color: #696cff;">
                                                    <i class="bi bi-box-seam"></i>
                                                </div>
                                                <div class="fw-bold" style="color: #566a7f;">
                                                    {{ $item->nama_bahan }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                                {{ $item->nama_unit }}
                                            </span>
                                        </td>
                                        <td class="text-end price-ho fs-6">
                                            Rp {{ number_format($item->harga_ho, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end price-mitra fs-6">
                                            Rp {{ number_format($item->harga_mitra, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-light text-primary border-0 btn-edit-price"
                                                style="background-color: #f0f0ff; color: #696cff !important; border-radius: 6px; padding: 0.4rem 0.75rem;"
                                                data-id="{{ $item->id }}" data-nama="{{ $item->nama_bahan }}"
                                                data-satuan="{{ $item->nama_unit }}" data-ho="{{ $item->harga_ho }}"
                                                data-mitra="{{ $item->harga_mitra }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ═══ MODAL CREATE PRICELIST ═══ --}}
            <div class="modal fade" id="modalCreatePrice" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0"
                        style="border-radius: 12px; box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);">
                        <form id="formCreatePrice">
                            @csrf
                            <div class="modal-header border-bottom-0 pt-4 px-4 pb-2">
                                <h5 class="modal-title fw-bold" style="color: #2c3e50;">Tambah Pricelist Baru</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body px-4 pb-4">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="fw-bold mb-1"
                                            style="font-size: 11px; color: #566a7f; text-transform: uppercase;">
                                            Pilih Bahan (Purchase Unit)
                                        </label>
                                        <select name="bahan_unit_id" id="selectBahanUnit" class="form-control w-100"
                                            required>
                                            <option value="">-- Cari Bahan & Satuan --</option>
                                            @foreach($availableItems as $avl)
                                                <option value="{{ $avl->bahan_unit_id }}">
                                                    {{ $avl->nama_bahan }} ({{ $avl->nama_unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted" style="font-size: 10px;">Hanya menampilkan item yang
                                            `is_purchase_unit = 1` dan belum memiliki harga.</small>
                                    </div>

                                    <div class="col-12">
                                        <label class="fw-bold mb-1"
                                            style="font-size: 11px; color: #566a7f; text-transform: uppercase;">
                                            Harga Supply (Outlet HO)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">Rp</span>
                                            <input type="number" class="form-control border-start-0 ps-0"
                                                name="harga_ho" placeholder="0" required>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="fw-bold mb-1"
                                            style="font-size: 11px; color: #566a7f; text-transform: uppercase;">
                                            Harga Jual (Outlet Mitra)
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">Rp</span>
                                            <input type="number"
                                                class="form-control border-start-0 ps-0 text-success fw-bold"
                                                name="harga_mitra" placeholder="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-top-0 px-4 pb-4 bg-light"
                                style="border-radius: 0 0 12px 12px;">
                                <button type="button" class="btn btn-light px-4 border shadow-sm"
                                    data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                                <button type="submit" class="btn btn-custom-primary px-4" id="btnSubmitCreate">
                                    <i class="bi bi-save me-1"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ═══ MODAL EDIT HARGA ═══ --}}
            <div class="modal fade" id="modalEditPrice" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0"
                        style="border-radius: 12px; box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);">
                        <form id="formEditPrice">
                            @csrf
                            @method('PUT') <input type="hidden" id="editPricelistId">

                            <div class="modal-header border-bottom-0 pt-4 px-4 pb-2">
                                <h5 class="modal-title fw-bold" style="color: #2c3e50;">Update Harga Produk</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body px-4 pb-4">
                                <div class="mb-4 p-3 bg-light rounded">
                                    <h6 class="fw-bold text-primary mb-1" id="editProductName">Nama Produk</h6>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border"
                                        id="editProductUnit">Satuan</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="fw-bold mb-1"
                                            style="font-size: 11px; color: #566a7f; text-transform: uppercase;">Harga
                                            Supply (Outlet HO)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">Rp</span>
                                            <input type="number" class="form-control border-start-0 ps-0"
                                                id="editHargaHO" name="harga_ho" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="fw-bold mb-1"
                                            style="font-size: 11px; color: #566a7f; text-transform: uppercase;">Harga
                                            Jual (Outlet Mitra)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">Rp</span>
                                            <input type="number"
                                                class="form-control border-start-0 ps-0 text-success fw-bold"
                                                id="editHargaMitra" name="harga_mitra" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-top-0 px-4 pb-4 bg-light"
                                style="border-radius: 0 0 12px 12px;">
                                <button type="button" class="btn btn-light px-4 border shadow-sm"
                                    data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                                <button type="submit" class="btn btn-custom-primary px-4" id="btnSimpanEdit">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ═══ MODAL PREVIEW EXCEL ═══ --}}
            <div class="modal fade" id="modalPreviewExcel" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0"
                        style="border-radius: 12px; box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);">
                        <div class="modal-header border-bottom-0 pt-4 px-4 pb-2">
                            <h5 class="modal-title fw-bold" style="color: #2c3e50;">Preview Data Excel</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body px-4 pb-4">
                            <div class="alert alert-info py-2" style="font-size: 0.85rem;">
                                <i class="bi bi-info-circle-fill me-1"></i> Pastikan kolom Excel kamu memiliki *header*:
                                <strong>bahan_unit_id</strong>, <strong>harga_ho</strong>, dan
                                <strong>harga_mitra</strong>.
                            </div>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-hover align-middle mb-0" id="tablePreviewExcel">
                                    <thead class="bg-light" style="font-size: 11px; text-transform: uppercase;">
                                        <tr>
                                            <th>Product Name</th>
                                            <th class="text-end">Harga Supply (HO)</th>
                                            <th class="text-end">Harga Jual (Mitra)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="modal-footer border-top-0 px-4 pb-4 bg-light" style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-light px-4 border shadow-sm" data-bs-dismiss="modal"
                                style="border-radius: 8px;">Batal</button>
                            <button type="button" class="btn btn-success px-4 shadow-sm" id="btnConfirmImport"
                                style="border-radius: 8px;">
                                <i class="bi bi-cloud-arrow-up me-1"></i> Konfirmasi & Import
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        $(document).ready(function () {
            // Initialize DataTables
            $('#pricelistTable').DataTable({
                responsive: true,
                pageLength: 15,
                dom: '<"d-flex flex-wrap justify-content-between align-items-center"lf>rt<"d-flex flex-wrap justify-content-between align-items-center"ip>',
                language: {
                    search: "",
                    searchPlaceholder: "Cari produk...",
                    lengthMenu: "_MENU_",
                },
                columnDefs: [{ targets: [0, 5], orderable: false }]
            });

            // Initialize Select2 untuk Modal Create
            $('#selectBahanUnit').select2({
                dropdownParent: $('#modalCreatePrice'),
                placeholder: "-- Cari Bahan & Satuan --",
                allowClear: true
            });

            // Buka Modal Create
            $('#btnBukaModalCreate').click(function () {
                $('#formCreatePrice')[0].reset();
                $('#selectBahanUnit').val(null).trigger('change');
                $('#modalCreatePrice').modal('show');
            });

            // Buka Modal Edit
            $(document).on('click', '.btn-edit-price', function () {
                $('#editPricelistId').val($(this).data('id'));
                $('#editProductName').text($(this).data('nama'));
                $('#editProductUnit').text($(this).data('satuan'));
                $('#editHargaHO').val($(this).data('ho'));
                $('#editHargaMitra').val($(this).data('mitra'));

                $('#modalEditPrice').modal('show');
            });

            // AJAX Store (Create)
            $('#formCreatePrice').on('submit', function (e) {
                e.preventDefault();
                const btn = $('#btnSubmitCreate');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

                $.ajax({
                    url: "{{ url('/scm-pricelist/store') }}", // Sesuaikan dengan route name kamu
                    method: "POST",
                    data: $(this).serialize(),
                    success: function (res) {
                        Swal.fire('Berhasil!', res.msg, 'success').then(() => location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    }
                });
            });

            // AJAX Update (Edit)
            $('#formEditPrice').on('submit', function (e) {
                e.preventDefault();
                const btn = $('#btnSimpanEdit');
                const id = $('#editPricelistId').val();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

                $.ajax({
                    url: `/scm-pricelist/${id}`, // Sesuaikan dengan route name kamu
                    type: "PUT",
                    data: $(this).serialize(),
                    success: function (res) {
                        Swal.fire('Berhasil!', res.msg, 'success').then(() => location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON.message || 'Terjadi kesalahan.', 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Perubahan');
                    }
                });
            });
        });

        $(document).ready(function () {
            let parsedDataToSend = [];

            $('#fileExcel').on('change', function (e) {
                let file = e.target.files[0];
                if (!file) return;

                let reader = new FileReader();
                reader.onload = function (e) {
                    let data = new Uint8Array(e.target.result);
                    let workbook = XLSX.read(data, { type: 'array' });

                    let firstSheetName = workbook.SheetNames[0];
                    let worksheet = workbook.Sheets[firstSheetName];

                    let rawExcelData = XLSX.utils.sheet_to_json(worksheet, { defval: "" });

                    let html = '';
                    parsedDataToSend = [];

                    rawExcelData.forEach(function (row) {
                        let cleanRow = {};
                        for (let key in row) {
                            cleanRow[key.toString().toLowerCase().replace(/\s+/g, '_')] = row[key];
                        }

                        // Ambil murni berdasar NAMA PRODUK
                        let nama = cleanRow.product_name || cleanRow.nama_produk || cleanRow.nama_bahan || '';
                        let hargaHo = parseFloat(cleanRow.harga_ho || cleanRow.harga_supply_ho || cleanRow.price || 0);
                        let hargaMitra = parseFloat(cleanRow.harga_mitra || cleanRow.harga_jual_mitra || cleanRow.harga_jual || 0);

                        if (nama !== '') {
                            parsedDataToSend.push({
                                nama_produk: nama,
                                harga_ho: hargaHo,
                                harga_mitra: hargaMitra
                            });

                            html += `<tr>
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">${nama}</div>
                                </td>
                                <td class="text-end text-primary fw-bold" style="font-family: monospace;">
                                    ${hargaHo.toLocaleString('id-ID')}
                                </td>
                                <td class="text-end text-success fw-bold" style="font-family: monospace;">
                                    ${hargaMitra.toLocaleString('id-ID')}
                                </td>
                            </tr>`;
                        }
                    });

                    if (html === '') {
                        html = '<tr><td colspan="3" class="text-center text-danger py-3">Format salah atau data kosong. Pastikan ada kolom <b>Product Name</b>, <b>Harga HO</b>, dan <b>Harga Mitra</b>.</td></tr>';
                        $('#btnConfirmImport').prop('disabled', true);
                    } else {
                        $('#btnConfirmImport').prop('disabled', false);
                    }

                    $('#tablePreviewExcel tbody').html(html);
                    $('#modalPreviewExcel').modal('show');

                    $('#modalPreviewExcel .alert-info').html('<i class="bi bi-info-circle-fill me-1"></i> Kolom Excel minimal harus ada: <strong>Product Name</strong>, <strong>Harga HO</strong>, dan <strong>Harga Mitra</strong>. Sistem akan otomatis melacak barang berdasarkan namanya.');

                    $('#fileExcel').val('');
                };
                reader.readAsArrayBuffer(file);
            });

            $('#btnConfirmImport').on('click', function () {
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');

                $.ajax({
                    url: "{{ route('scm.pricelist.import') }}",
                    method: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        data: parsedDataToSend
                    },
                    success: function (res) {
                        $('#modalPreviewExcel').modal('hide');
                        Swal.fire({
                            title: 'Berhasil!',
                            text: res.msg,
                            icon: 'success',
                            confirmButtonColor: '#696cff'
                        }).then(() => location.reload());
                    },
                    error: function (xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal mengimpor data.', 'error');
                        btn.prop('disabled', false).html('<i class="bi bi-cloud-arrow-up me-1"></i> Konfirmasi & Import');
                    }
                });
            });
        });
    </script>
@endpush

@include('Temp.Investor.footer')
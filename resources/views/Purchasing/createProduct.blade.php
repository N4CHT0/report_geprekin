{{-- resources/views/purchasing/createProductScm.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Styling form input entri data */
    .form-label-custom {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #495057;
        letter-spacing: 0.5px;
    }

    /* Tabel konversi unit khusus form entri */
    #tableUnit {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #tableUnit thead th {
        background-color: #f8f9fa !important;
        border: 1px solid #eef1f6 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #2c3e50;
        padding: 12px 10px !important;
    }

    #tableUnit tbody td {
        padding: 10px 8px !important;
        vertical-align: middle !important;
        border: 1px solid #f4f6f9 !important;
    }

    /* Modifikasi Checkbox / Radio custom */
    .form-check-input:checked {
        background-color: #696cff !important;
        border-color: #696cff !important;
    }

    /* Select2 dropdown scroll override */
    .select2-container .select2-dropdown {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Input Master Data Produk SCM</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/dashboard-scm" class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('scm.index-bahan') }}" class="text-decoration-none text-muted">Product List</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Create Product</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="{{ route('scm.index-bahan') }}" class="btn btn-sm btn-light border px-3 d-inline-flex align-items-center gap-1 text-secondary" style="height: 32px;">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4" style="border-radius: 15px;">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('scm.store-bahan') }}" method="POST" id="formProduct">
                        @csrf
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label form-label-custom mb-2">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="nama_bahan" class="form-control form-control-sm px-3 shadow-none border-light-dark" style="height: 36px; border-radius: 8px;" placeholder="Contoh: GULA CAIR" required>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label form-label-custom mb-2">Sumber Barang <span class="text-danger">*</span></label>
                                <select name="sumber_barang" class="form-select form-select-sm px-3 shadow-none" style="height: 36px; border-radius: 8px;">
                                    <option value="GUDANG">GUDANG</option>
                                    <option value="SUPPLIER">SUPPLIER</option>
                                </select>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25 my-4">

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="p-2 bg-light rounded text-primary" style="color: #696cff !important;">
                                <i class="bi bi-calculator fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0">Product Detail (Unit Conversion)</h6>
                        </div>

                        <div class="table-responsive mb-3" style="border-radius: 8px; overflow: hidden;">
                            <table class="table align-middle mb-0" id="tableUnit">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="width: 180px;">Unit</th>
                                        <th rowspan="2" class="text-center" style="width: 140px;">Conv. Factor</th>
                                        <th rowspan="2" style="width: 150px;">Base Unit</th>
                                        <th rowspan="2" class="text-end" style="width: 160px;">Base Price (IDR)</th>
                                        <th rowspan="2" class="text-end" style="width: 140px;">Weight (Gram)</th>
                                        <th colspan="5" class="text-center" style="background-color: #f1f3f7 !important; border-bottom: 1px solid #dee2e6 !important;">Default Flags</th>
                                        <th rowspan="2" class="text-center" style="width: 60px;">Aksi</th>
                                    </tr>
                                    <tr class="text-center" style="font-size: 10px;">
                                        <th style="background-color: #f8f9fa !important; width: 65px;">Stock</th>
                                        <th style="background-color: #f8f9fa !important; width: 65px;">Purchase</th>
                                        <th style="background-color: #f8f9fa !important; width: 65px;">Base</th>
                                        <th style="background-color: #f8f9fa !important; width: 65px;">Transfer</th>
                                        <th style="background-color: #f8f9fa !important; width: 65px;">Sales</th>
                                    </tr>
                                </thead>
                                <tbody id="unitWrapper">
                                    <tr>
                                        <td>
                                            <select name="units[0][unit_id]" class="form-select form-select-sm select-unit shadow-none" required>
                                                <option value="">- Select -</option>
                                                @foreach($units as $u) 
                                                    <option value="{{ $u->id }}">{{ $u->nama_unit }}</option> 
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="units[0][factor]" class="form-control form-control-sm text-center bg-light shadow-none" value="1" readonly>
                                        </td>
                                        <td>
                                            <input type="text" name="units[0][base_name]" class="form-control form-control-sm bg-light shadow-none base-name-display" value="-" readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="units[0][price]" class="form-control form-control-sm text-end shadow-none" value="0">
                                        </td>
                                        <td>
                                            <input type="number" name="units[0][weight]" class="form-control form-control-sm text-end shadow-none" value="0">
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="units[0][is_stock]" value="1" class="form-check-input cb-stock" checked>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="units[0][is_purchase]" value="1" class="form-check-input cb-purchase" checked>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="units[0][is_base]" value="1" class="form-check-input cb-base" checked>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="units[0][is_transfer]" value="1" class="form-check-input cb-transfer" checked>
                                        </td>
                                        <td class="text-center">
                                            <input type="checkbox" name="units[0][is_sales]" value="1" class="form-check-input cb-sales" checked>
                                        </td>
                                        <td class="text-center"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-4">
                            <button type="button" class="btn btn-sm btn-light border text-primary d-inline-flex align-items-center gap-1" id="btnAddUnit" style="color: #696cff !important;">
                                <i class="bi bi-plus-lg"></i> Add Unit Conversion
                            </button>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-5 shadow-sm fw-semibold" style="background-color: #696cff; border-color: #696cff; border-radius: 8px; height: 40px;">
                                <i class="bi bi-save me-1"></i> Simpan Informasi Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

@push('scripts')
<script>
    let rowCount = 1; // Mulai dari 1 karena baris pertama (index 0) sudah dicetak statis

    $(document).ready(function() {
        // Fungsi Tambah Baris Form Dinamis
        $('#btnAddUnit').click(function() {
            let baseUnitName = $('#unitWrapper tr:first-child .select-unit option:selected').text();
            if(baseUnitName == "- Select -") baseUnitName = "-";

            let newRow = `
            <tr>
                <td>
                    <select name="units[${rowCount}][unit_id]" class="form-select form-select-sm select-unit shadow-none" required>
                        <option value="">- Select -</option>
                        @foreach($units as $u) 
                            <option value="{{ $u->id }}">{{ $u->nama_unit }}</option> 
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="units[${rowCount}][factor]" class="form-control form-control-sm text-center shadow-none" placeholder="Isi faktor"></td>
                <td><input type="text" class="form-control form-control-sm bg-light shadow-none base-display" value="${baseUnitName}" readonly></td>
                <td><input type="number" name="units[${rowCount}][price]" class="form-control form-control-sm text-end shadow-none" value="0"></td>
                <td><input type="number" name="units[${rowCount}][weight]" class="form-control form-control-sm text-end shadow-none" value="0"></td>
                
                <td class="text-center"><input type="checkbox" name="units[${rowCount}][is_stock]" value="1" class="form-check-input cb-stock"></td>
                <td class="text-center"><input type="checkbox" name="units[${rowCount}][is_purchase]" value="1" class="form-check-input cb-purchase"></td>
                <td class="text-center"><input type="checkbox" name="units[${rowCount}][is_base]" value="1" class="form-check-input cb-base" disabled></td>
                <td class="text-center"><input type="checkbox" name="units[${rowCount}][is_transfer]" value="1" class="form-check-input cb-transfer"></td>
                <td class="text-center"><input type="checkbox" name="units[${rowCount}][is_sales]" value="1" class="form-check-input cb-sales"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-light border text-danger btnRemove" style="padding: 2px 6px;">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;

            $('#unitWrapper').append(newRow);
            rowCount++;
        });

        // Fungsi Hapus Baris Dinamis
        $(document).on('click', '.btnRemove', function() {
            $(this).closest('tr').remove();
        });

        // Otomatis memperbarui semua tulisan kolol "Base Unit" di baris baru jika unit dasar diubah
        $(document).on('change', '#unitWrapper tr:first-child .select-unit', function() {
            let selectedText = $(this).find('option:selected').text();
            if(selectedText == "- Select -") selectedText = "-";
            $('.base-display').val(selectedText);
            $('.base-name-display').val(selectedText);
        });

        // Script Checklist Tunggal (Mencegah multi-select flag default)
        function handleSingleCheck(className) {
            $(document).on('change', '.' + className, function() {
                if ($(this).is(':checked')) {
                    $('.' + className).not(this).prop('checked', false);
                }
            });
        }
        handleSingleCheck('cb-stock');
        handleSingleCheck('cb-purchase');
        handleSingleCheck('cb-base');
        handleSingleCheck('cb-transfer');
        handleSingleCheck('cb-sales');
    });
</script>
@endpush
@include('Temp.Investor.footer')
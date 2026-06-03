{{-- resources/views/purchasing/editProductScm.blade.php --}}
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

    /* Modifikasi Checkbox agar seragam dengan tema indigo */
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
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Edit Master Data Produk
                                SCM</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/dashboard-scm"
                                            class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('scm.index-bahan') }}"
                                            class="text-decoration-none text-muted">Product List</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Edit
                                        Product</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="{{ route('scm.index-bahan') }}"
                                class="btn btn-sm btn-light border px-3 d-inline-flex align-items-center gap-1 text-secondary"
                                style="height: 32px;">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4" style="border-radius: 15px;">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('scm.update-bahan', $product->id) }}" method="POST" id="formProduct">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-4">
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label form-label-custom mb-2">Product Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="nama_bahan"
                                    class="form-control form-control-sm px-3 shadow-none border-light-dark"
                                    style="height: 36px; border-radius: 8px;" value="{{ $product->nama_bahan }}"
                                    required>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <label class="form-label form-label-custom mb-2">Sumber Barang <span
                                        class="text-danger">*</span></label>
                                <select name="sumber_barang" class="form-select form-select-sm px-3 shadow-none"
                                    style="height: 36px; border-radius: 8px;">
                                    <option value="GUDANG" {{ $product->sumber_barang == 'GUDANG' ? 'selected' : '' }}>
                                        GUDANG</option>
                                    <option value="SUPPLIER" {{ $product->sumber_barang == 'SUPPLIER' ? 'selected' : '' }}>SUPPLIER</option>
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
                                        <th colspan="5" class="text-center"
                                            style="background-color: #f1f3f7 !important; border-bottom: 1px solid #dee2e6 !important;">
                                            Default Flags</th>
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
                                    @foreach($product->details as $index => $detail)
                                        <tr>
                                            <td>
                                                <select name="units[{{ $index }}][unit_id]"
                                                    class="form-select form-select-sm select-unit shadow-none" required>
                                                    @foreach($units as $u)
                                                        <option value="{{ $u->id }}" {{ $detail->unit_id == $u->id ? 'selected' : '' }}>
                                                            {{ $u->nama_unit }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="units[{{ $index }}][factor]"
                                                    class="form-control form-control-sm text-center shadow-none"
                                                    value="{{ $detail->conversion_factor }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                    class="form-control form-control-sm bg-light shadow-none base-display"
                                                    value="{{ $product->base_unit_name }}" readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="units[{{ $index }}][price]"
                                                    class="form-control form-control-sm text-end shadow-none"
                                                    value="{{ $detail->base_price }}">
                                            </td>
                                            <td>
                                                <input type="number" name="units[{{ $index }}][weight]"
                                                    class="form-control form-control-sm text-end shadow-none"
                                                    value="{{ $detail->weight }}">
                                            </td>

                                            <td class="text-center">
                                                <input type="checkbox" name="units[{{ $index }}][is_stock]" value="1"
                                                    class="form-check-input cb-stock" {{ $detail->is_stock_unit ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="units[{{ $index }}][is_purchase]" value="1"
                                                    class="form-check-input cb-purchase" {{ $detail->is_purchase_unit ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                {{-- Khusus baris pertama (index 0) is_base biasanya aktif, baris lain
                                                dinonaktifkan mengikuti logikamu --}}
                                                <input type="checkbox" name="units[{{ $index }}][is_base]" value="1"
                                                    class="form-check-input cb-base" {{ $detail->is_base_unit ? 'checked' : '' }} {{ $index > 0 ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="units[{{ $index }}][is_transfer]" value="1"
                                                    class="form-check-input cb-transfer" {{ $detail->is_transfer_unit ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" name="units[{{ $index }}][is_sales]" value="1"
                                                    class="form-check-input cb-sales" {{ $detail->is_sales_unit ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                @if($index > 0)
                                                    <button type="button"
                                                        class="btn btn-sm btn-light border text-danger btnRemove"
                                                        style="padding: 2px 6px;">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-4">
                            <button type="button"
                                class="btn btn-sm btn-light border text-primary d-inline-flex align-items-center gap-1"
                                id="btnAddUnit" style="color: #696cff !important;">
                                <i class="bi bi-plus-lg"></i> Add Unit Conversion
                            </button>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('scm.index-bahan') }}"
                                class="btn btn-light border px-4 d-inline-flex align-items-center"
                                style="border-radius: 8px; height: 40px;">Batal</a>
                            <button type="submit"
                                class="btn btn-primary px-5 shadow-sm fw-semibold d-inline-flex align-items-center"
                                style="background-color: #696cff; border-color: #696cff; border-radius: 8px; height: 40px;">
                                <i class="bi bi-save me-1"></i> Update Informasi Produk
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
        // Menyinkronkan counter baris baru berdasarkan jumlah detail yang dimuat secara dinamis dari database
        let rowCount = {{ count($product->details) }};

        $(document).ready(function () {
            // Fungsi pemicu awal agar kolom Base Unit langsung terisi nama unit index ke-0 saat halaman selesai dimuat
            function initializeBaseUnitDisplay() {
                let baseUnitName = $('#unitWrapper tr:first-child .select-unit option:selected').text().trim();
                if (baseUnitName) {
                    $('.base-display').val(baseUnitName);
                }
            }
            initializeBaseUnitDisplay();

            // Fungsi Tambah Baris Form Dinamis
            $('#btnAddUnit').click(function () {
                let baseUnitName = $('#unitWrapper tr:first-child .select-unit option:selected').text().trim();
                if (!baseUnitName) baseUnitName = "-";

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

                    <td class="text-center">
                        <input type="hidden" name="units[${rowCount}][is_stock]" value="0">
                        <input type="checkbox" name="units[${rowCount}][is_stock]" value="1" class="form-check-input cb-stock">
                    </td>
                    <td class="text-center">
                        <input type="hidden" name="units[${rowCount}][is_purchase]" value="0">
                        <input type="checkbox" name="units[${rowCount}][is_purchase]" value="1" class="form-check-input cb-purchase">
                    </td>
                    <td class="text-center">
                        <input type="hidden" name="units[${rowCount}][is_base]" value="0">
                        <input type="checkbox" name="units[${rowCount}][is_base]" value="1" class="form-check-input cb-base" disabled>
                    </td>
                    <td class="text-center">
                        <input type="hidden" name="units[${rowCount}][is_transfer]" value="0">
                        <input type="checkbox" name="units[${rowCount}][is_transfer]" value="1" class="form-check-input cb-transfer">
                    </td>
                    <td class="text-center">
                        <input type="hidden" name="units[${rowCount}][is_sales]" value="0">
                        <input type="checkbox" name="units[${rowCount}][is_sales]" value="1" class="form-check-input cb-sales">
                    </td>
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
            $(document).on('click', '.btnRemove', function () {
                $(this).closest('tr').remove();
            });

            // Otomatis memperbarui semua tulisan kolom "Base Unit" jika unit dasar di baris pertama diganti
            $(document).on('change', '#unitWrapper tr:first-child .select-unit', function () {
                let selectedText = $(this).find('option:selected').text().trim();
                $('.base-display').val(selectedText);
            });

            // Script Checklist Tunggal (Mencegah multi-select flag default)
            function handleSingleCheck(className) {
                $(document).on('change', '.' + className, function () {
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
{{-- resources/views/purchasing/dashboardOutlet.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Custom style untuk form agar lebih clean */
    .form-group label {
        font-weight: 700;
        font-size: 11px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .form-control-sm,
    .form-select-sm {
        border-radius: 4px;
    }

    .bg-light-blue {
        background-color: #bbd3ff;
    }

    .badge-status {
        font-size: 10px;
        font-weight: 900;
        padding: 5px 10px;
        border-radius: 4px;
    }

    .table-detail thead th {
        background-color: #f8f9fa;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">
            <form action="#" method="POST" id="formSimpleSales">
                @csrf
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-light-blue text-dark font-weight-bold">
                        <i class="fas fa-file-invoice mr-2"></i> Transaction Information - Simple Sales
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <!-- <div class="form-group mb-3">
                                    <label>Sequence</label>
                                    <input type="text" name="sequence" value="SS-{{ date('Ymd') }}-001" class="form-control form-control-sm bg-light" readonly>
                                </div> -->
                                <div class="form-group mb-3">
                                    <label>Sales Type</label>
                                    <select name="sales_type" id="sales_type" class="form-control form-control-sm">
                                        <option value="">- Select Product Sales Type -</option>
                                        <option value="goods">Goods</option>
                                        <option value="services">Services</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Customer</label>
                                    <input type="text" name="customer" class="form-control form-control-sm" placeholder="Input Customer Name">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Simple Sales Date</label>
                                    <input type="date" name="date" id="salesDate" value="{{ date('Y-m-d') }}" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Branch</label>
                                    <select name="branch" class="form-control form-control-sm">
                                        <option value="">- Select Branch -</option>
                                        <option value="HO">Kantor Pusat (HO)</option>
                                        <option value="Outlet1">Outlet Sudirman</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Location (Warehouse)</label>
                                    <select name="location" class="form-control form-control-sm">
                                        <option value="">- Select Location -</option>
                                        <option value="Gudang1">Gudang Utama</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Payment Method</label>
                                    <select name="payment_method" class="form-control form-control-sm" id="payMethod">
                                        <option value="">- Select Payment Method -</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Credit">Credit</option>
                                    </select>
                                </div>
                                <!-- <div class="form-group mb-3">
                                    <label>COA No (Akun Biaya/Kas)</label>
                                    <select name="coa_no" class="form-control form-control-sm ">
                                        <option value="">- Select Location -</option>
                                        <option value="1101">1101 - Kas Besar</option>
                                        <option value="1102">1102 - Bank Mandiri</option>
                                    </select>
                                </div> -->
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Credit Terms (Days)</label>
                                    <input type="number" name="credit_terms" class="form-control form-control-sm" placeholder="0">
                                </div>
                                <div class="form-group mb-3">
                                    <label>Currency</label>
                                    <select name="currency" class="form-control form-control-sm">
                                        <option value="">- Select Currency -</option>
                                        <option value="IDR">Rupiah</option>
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Rate</label>
                                    <input type="number" name="rate" value="1" class="form-control form-control-sm bg-light" readonly>
                                </div>
                                <!-- <div class="form-group mb-3">
                                    <label>Type (Status Ownership)</label>
                                    <div class="d-block mt-1">
                                        <span class="badge-status bg-warning text-dark border border-warning">MILIK MITRA (FRANCHISEE)</span>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>

                <div id="productDetailsContainer" style="display: none;" class="mt-4 ">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-light-blue text-white d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Simple Sales Details</span>
                            <button type="button" class="btn btn-sm btn-info" id="addItem"><i class="fa fa-plus"></i> Add Item</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-detail mb-0">
                                    <thead>
                                        <tr>
                                            <th width="50" class="text-center">No</th>
                                            <th>Product Name</th>
                                            <th width="120">Unit</th>
                                            <th width="120" class="text-center">Qty</th>
                                            <th width="180">Price</th>
                                            <th width="200">Amount</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemWrapper">
                                        <tr>
                                            <td class="text-center align-middle">1</td>
                                            <td>
                                                <select name="items[0][product]" class="form-control form-control-sm">
                                                    <option value="">- Select Product -</option>
                                                    <option value="P001">Kopi Bubuk Robusta 1kg</option>
                                                </select>
                                            </td>
                                            <td><input type="text" name="items[0][unit]" class="form-control form-control-sm bg-light" value="Pack" readonly></td>
                                            <td><input type="number" name="items[0][qty]" class="form-control form-control-sm text-center" value="1"></td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" name="items[0][price]" class="form-control text-right" value="0">
                                                </div>
                                            </td>
                                            <td><input type="text" class="form-control form-control-sm bg-light text-right font-weight-bold" value="0" readonly></td>
                                            <td class="text-center">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light-blue">
                        <span class="font-weight-bold text-dark">Simple Sales Cost</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="30%">Account</th>
                                        <th width="25%">Amount</th>
                                        <th width="40%">Notes</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="costWrapper">
                                    <tr>
                                        <td>
                                            <select name="costs[0][account]" class="form-control form-control-sm">
                                                <option value="">- Select Account -</option>
                                                <option value="1">Biaya Kirim</option>
                                                <option value="2">Biaya Packing</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="costs[0][amount]" class="form-control form-control-sm text-right cost-input" value="0">
                                        </td>
                                        <td>
                                            <input type="text" name="costs[0][notes]" class="form-control form-control-sm">
                                        </td>
                                        <td class="text-center">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-info mt-2" id="addCost">
                            <i class="fa fa-plus"></i> Add Cost
                        </button>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light-blue">
                        <span class="font-weight-bold text-dark">Transaction Summary</span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="font-weight-bold">Additional Information</label>
                                    <textarea name="additional_info" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Foot Note</label>
                                    <textarea name="foot_note" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group row mb-2">
                                    <label class="col-sm-5 col-form-label font-weight-bold text-right">Cost Total</label>
                                    <div class="col-sm-7">
                                        <input type="text" id="cost_total_display" class="form-control bg-light text-right font-weight-bold" value="0" readonly>
                                        <input type="hidden" name="cost_total_val" id="cost_total_val" value="0">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-5 col-form-label font-weight-bold text-right">Simple Sales Total</label>
                                    <div class="col-sm-7">
                                        <input type="text" id="sales_total_display" class="form-control bg-light text-right font-weight-bold" value="0" readonly style="font-size: 1.1rem;">
                                        <input type="hidden" name="sales_total_val" id="sales_total_val" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        // Fungsi untuk cek tipe sales
        $('#sales_type').on('change', function() {
            var selectedType = $(this).val();

            if (selectedType === 'goods') {
                // Tampilkan dengan efek slide/fade agar lebih halus
                $('#productDetailsContainer').slideDown();
            } else {
                // Sembunyikan jika bukan goods
                $('#productDetailsContainer').slideUp();

                // Opsional: Kosongkan item jika tipe diganti (agar tidak terkirim data sampah)
                // resetItems(); 
            }
        });
    });


    $(document).ready(function() {
        // 1. FUNGSI HITUNG TOTAL KESELURUHAN
        function calculateAll() {
            let productTotal = 0;
            let costTotal = 0;

            // Hitung total dari Product Details
            $('#itemWrapper tr').each(function() {
                let qty = parseFloat($(this).find('input[name$="[qty]"]').val()) || 0;
                let price = parseFloat($(this).find('input[name$="[price]"]').val()) || 0;
                let subtotal = qty * price;

                $(this).find('.subtotal-display').val(subtotal.toLocaleString('id-ID'));
                productTotal += subtotal;
            });

            // Hitung total dari Simple Sales Cost
            $('#costWrapper tr').each(function() {
                let amount = parseFloat($(this).find('.cost-input').val()) || 0;
                costTotal += amount;
            });

            // Update Grand Total di Footer Tabel Produk
            $('#productDetailsContainer tfoot h5').text('Rp ' + productTotal.toLocaleString('id-ID'));

            // Update Summary di Card Bawah
            $('#cost_total_display').val(costTotal.toLocaleString('id-ID'));
            $('#cost_total_val').val(costTotal);

            let grandTotal = productTotal + costTotal;
            $('#sales_total_display').val(grandTotal.toLocaleString('id-ID'));
            $('#sales_total_val').val(grandTotal);
        }

        // 2. TAMBAH BARIS PRODUCT
        let itemIdx = 1;
        $('#addItem').click(function() {
            let newRow = `
        <tr>
            <td class="text-center align-middle">${itemIdx + 1}</td>
            <td>
                <select name="items[${itemIdx}][product]" class="form-control form-control-sm">
                    <option value="">- Select Product -</option>
                    <option value="P001">Kopi Bubuk Robusta 1kg</option>
                </select>
            </td>
            <td><input type="text" name="items[${itemIdx}][unit]" class="form-control form-control-sm bg-light" value="Pack" readonly></td>
            <td><input type="number" name="items[${itemIdx}][qty]" class="form-control form-control-sm text-center qty-input" value="1"></td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">Rp</span>
                    <input type="number" name="items[${itemIdx}][price]" class="form-control text-right price-input" value="0">
                </div>
            </td>
            <td><input type="text" class="form-control form-control-sm bg-light text-right font-weight-bold subtotal-display" value="0" readonly></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-trash"></i></button></td>
        </tr>`;
            $('#itemWrapper').append(newRow);
            itemIdx++;
        });

        // 3. TAMBAH BARIS COST
        let costIdx = 1;
        $('#addCost').click(function() {
            let newCostRow = `
        <tr>
            <td>
                <select name="costs[${costIdx}][account]" class="form-control form-control-sm">
                    <option value="">- Select Account -</option>
                    <option value="1">Biaya Kirim</option>
                    <option value="2">Biaya Packing</option>
                </select>
            </td>
            <td><input type="number" name="costs[${costIdx}][amount]" class="form-control form-control-sm text-right cost-input" value="0"></td>
            <td><input type="text" name="costs[${costIdx}][notes]" class="form-control form-control-sm"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-trash"></i></button></td>
        </tr>`;
            $('#costWrapper').append(newCostRow);
            costIdx++;
        });

        // 4. EVENT LISTENER UNTUK PERUBAHAN INPUT
        $(document).on('input', '.qty-input, .price-input, .cost-input', function() {
            calculateAll();
        });

        // 5. HAPUS BARIS
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calculateAll();
        });

        // Jalankan kalkulasi saat pertama kali load
        calculateAll();
    });
</script>

@include('Temp.Investor.footer')
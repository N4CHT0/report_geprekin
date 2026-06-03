@include('Temp.Investor.header')
<main class="app-main bg-light">
    <div class="container-fluid py-4">
        <h5 class="mb-3">Create Simple Purchase - New</h5>

        <form id="purchaseForm">
            @csrf
            {{-- SECTION 1: TRANSACTION INFORMATION --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 text-primary"><i class="fas fa-info-circle mr-2"></i> Transaction Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <label class="small font-weight-bold">Supplier</label>
                            <div class="input-group mb-3">
                                <input type="text" name="supplier" class="form-control form-control-sm bg-light" placeholder="Select Supplier">
                                <div class="input-group-append"><span class="input-group-text bg-info text-white"><i class="fa fa-ellipsis-h"></i></span></div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="small font-weight-bold">Branch</label>
                                    <select name="branch" class="form-control form-control-sm">
                                        <option>HEAD OFFICE</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold">Location</label>
                                    <select name="location" class="form-control form-control-sm">
                                        <option>Main Warehouse</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="small font-weight-bold">Simple Purchase Date</label>
                            <input type="date" name="date" id="purchaseDate" value="{{ date('Y-m-d') }}" class="form-control form-control-sm mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label class="small font-weight-bold">Payment Method</label>
                                    <select name="payment_method" class="form-control form-control-sm">
                                        <option>- Select Payment -</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="small font-weight-bold">Rate</label>
                                    <input type="number" name="rate" value="1" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: PURCHASE DETAIL (ITEMS) --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Purchase Detail</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 text-center" style="font-size: 12px;">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Name</th>
                                <th>Unit</th>
                                <th>PO Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="itemBody">
                            <tr>
                                <td><input type="text" name="items[0][name]" class="form-control form-control-sm border-0"></td>
                                <td><input type="text" name="items[0][unit]" class="form-control form-control-sm border-0"></td>
                                <td><input type="number" name="items[0][qty]" class="form-control form-control-sm border-0 i-qty"></td>
                                <td><input type="number" name="items[0][price]" class="form-control form-control-sm border-0 i-price"></td>
                                <td class="align-middle font-weight-bold i-total">0</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-2"><button type="button" class="btn btn-sm btn-info" id="addItem">+ Add Product</button></div>
            </div>

            {{-- SECTION 3: CASH PURCHASE COST --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0">Cash Purchase Cost</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0 text-center" style="font-size: 12px;">
                        <thead class="bg-light">
                            <tr>
                                <th>Account</th>
                                <th width="200">Amount</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="costBody">
                            <tr>
                                <td><select name="costs[0][account]" class="form-control form-control-sm border-0">
                                        <option>- Select Account -</option>
                                    </select></td>
                                <td><input type="number" name="costs[0][amount]" class="form-control form-control-sm border-0 c-amount"></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="p-2"><button type="button" class="btn btn-sm btn-info" id="addCost">+ Add Cost</button></div>
            </div>

            {{-- FOOTER SUMMARY --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8"><label class="small font-weight-bold">Additional Information</label><textarea class="form-control" rows="2"></textarea></div>
                        <div class="col-md-4 text-right">
                            <div class="mb-1">Cost Total: <strong id="dispCost">0</strong></div>
                            <div class="mb-3">Purchase Total: <strong id="dispPurc">0</strong></div>
                            <button type="submit" class="btn btn-primary px-4">Save & Print</button>
                            <button type="submit" class="btn btn-success px-4">Save</button>
                            <button type="submit" class="btn btn-danger px-4">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
    $(document).ready(function() {

        $(document).on('click', '#purchaseDate', function() {
            try {
                this.showPicker(); // Cara standar browser modern
            } catch (e) {
                $(this).focus(); // Fallback kalau showPicker gagal
            }
        });

        let itemIdx = 1;
        let costIdx = 1;

        // --- FUNGSI ADD PRODUCT ROW ---
        $('#addItem').click(function() {
            let row = `
                <tr>
                    <td><input type="text" name="items[${itemIdx}][name]" class="form-control form-control-sm border-0"></td>
                    <td><input type="text" name="items[${itemIdx}][unit]" class="form-control form-control-sm border-0"></td>
                    <td><input type="number" name="items[${itemIdx}][qty]" class="form-control form-control-sm border-0 i-qty" value="0"></td>
                    <td><input type="number" name="items[${itemIdx}][price]" class="form-control form-control-sm border-0 i-price" value="0"></td>
                    <td class="align-middle font-weight-bold i-total text-right">0</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-row">&times;</button></td>
                </tr>`;
            $('#itemBody').append(row);
            itemIdx++;
        });

        // --- FUNGSI ADD COST ROW ---
        $('#addCost').click(function() {
            let row = `
                <tr>
                    <td><select name="costs[${costIdx}][account]" class="form-control form-control-sm border-0">
                        <option>- Select Account -</option>
                        <option value="6000-01">Ongkos Angkut</option>
                        <option value="6000-02">Biaya Parkir/Tol</option>
                    </select></td>
                    <td><input type="number" name="costs[${costIdx}][amount]" class="form-control form-control-sm border-0 c-amount" value="0"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger remove-row">&times;</button></td>
                </tr>`;
            $('#costBody').append(row);
            costIdx++;
        });

        // --- FUNGSI HAPUS BARIS ---
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calculateAll();
        });

        // --- LOGIKA PERHITUNGAN OTOMATIS ---
        $(document).on('input', '.i-qty, .i-price, .c-amount', function() {
            calculateAll();
        });

        function calculateAll() {
            let purchaseTotal = 0;
            let costTotal = 0;

            // Hitung setiap baris Product
            $('#itemBody tr').each(function() {
                let qty = parseFloat($(this).find('.i-qty').val()) || 0;
                let price = parseFloat($(this).find('.i-price').val()) || 0;
                let totalRow = qty * price;
                $(this).find('.i-total').text(totalRow.toLocaleString('id-ID'));
                purchaseTotal += totalRow;
            });

            // Hitung setiap baris Cost
            $('.c-amount').each(function() {
                costTotal += parseFloat($(this).val()) || 0;
            });

            // Update Tampilan Summary (Bagian Bawah)
            $('#dispPurc').text(purchaseTotal.toLocaleString('id-ID'));
            $('#dispCost').text(costTotal.toLocaleString('id-ID'));

            // Masukkan ke Hidden Input untuk simpan ke Database
            if ($('#purchase_total_val').length === 0) {
                $('#purchaseForm').append(`<input type="hidden" name="purchase_total_val" id="purchase_total_val">`);
                $('#purchaseForm').append(`<input type="hidden" name="cost_total_val" id="cost_total_val">`);
            }
            $('#purchase_total_val').val(purchaseTotal);
            $('#cost_total_val').val(costTotal);
        }

        // --- SUBMIT FORM VIA AJAX ---
        $('#purchaseForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('simple-purchase.store') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    // Pakai response.message sesuai JSON dari controller
                    alert(response.message); 
                    location.reload();
                },
                error: function(xhr) {
                    // Ambil pesan error dari Laravel jika ada
                    let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : "Terjadi kesalahan server.";
                    alert("Gagal: " + errorMsg);
                    console.log(xhr.responseText); // Cek detail di F12 Console
                }
            });
        });
    });
</script>
@include('Temp.Investor.footer')
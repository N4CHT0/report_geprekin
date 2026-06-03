@include('Temp.Investor.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">

            <form action="#" method="POST" id="formTransfer">
                @csrf

                {{-- SECTION 1: HEADER --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0 py-1 font-weight-bold">Simple Transfer - Inventory Movement</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 border-right">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted uppercase">Origin Branch</label>
                                    <select name="origin" class="form-control form-control select2-origin">
                                        <option value="HO">-- SELECT BRANCH --</option>
                                        @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}">
                                            {{ strtoupper($outlet->nama_outlet) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted uppercase">Transfer Date</label>
                                    <div class="position-relative">
                                        <input type="date" name="date" id="transferDate"
                                            value="{{ date('Y-m-d') }}"
                                            class="form-control form-control-sm"
                                            style="cursor: pointer; position: relative; z-index: 10;">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 border-right">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted uppercase">Destination Branch</label>
                                    <select name="destination" class="form-control form-control select2-destination border-primary" id="destinationSelect">
                                        <option value="HO">-- SELECT BRANCH --</option>
                                        @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}">
                                            {{ strtoupper($outlet->nama_outlet) }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted uppercase">Reference No.</label>
                                    <input type="text" value="TRF-{{ date('Ymd') }}-001" class="form-control form-control-sm bg-light" readonly>
                                </div> -->
                            </div>

                            <!-- <div class="col-md-4 bg-light p-3 rounded">
                                <label class="small font-weight-bold text-muted uppercase d-block">Ownership & Pricing</label>
                                <div class="mt-2">
                                    <span class="badge badge-warning p-2 d-block mb-2 font-weight-bold" id="statusBadge">
                                        <i class="fas fa-handshake mr-1"></i> STATUS: MILIK MITRA
                                    </span>
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block">Skema Biaya:</small>
                                        <strong class="text-danger" id="priceText">Mitra Price List (v.2)</strong>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: TABLE --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" id="tableTransfer">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="py-2 text-center" width="50">No</th>
                                        <th class="py-2">Product Name</th>
                                        <th class="py-2 text-center" width="120">Unit</th>
                                        <th class="py-2 text-right" width="180">Stock QTY</th>
                                        <th class="py-2 text-right" width="180">Qty</th>
                                        <th class="py-2" width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemBody">
                                    {{-- Baris pertama (default) --}}
                                    <tr>
                                        <td class="text-center align-middle iteration">1</td>
                                        <td>
                                            <select name="items[0][product]" class="form-control form-control select2-product border-0 shadow-none font-weight-bold">
                                                <option value="">- Pilih Produk -</option>
                                                @foreach($products as $p)
                                                <option value="{{ $p->id }}">
                                                    {{ strtoupper($p->nama_bahan) }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-right align-middle text-muted">0</td>
                                        <td class="text-right align-middle font-weight-bold">0</td>
                                        <td>
                                            <input type="number" name="items[0][qty]" value="1" class="form-control form-control-sm text-center">
                                        </td>
                                        <td class="text-center align-middle">
                                            {{-- Baris pertama tidak bisa dihapus --}}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-4">
                        <div class="row items-center">
                            <div class="col-md-6">
                                {{-- TOMBOL ADD ROW DISINI --}}
                                <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" id="btnAddNewRow">
                                    <i class="fa fa-plus"></i> ADD PRODUCT
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8"><label class="small font-weight-bold">Additional Information</label><textarea class="form-control" rows="2"></textarea></div>
                                <div class="col-md-4 text-right">
                                    <button type="submit" class="btn btn-primary px-4">Save & Print</button>
                                    <button type="button" id="btn-save-transfer" class="btn btn-success px-4">Save</button>
                                    <a href="{{ route('simple-transfer.index') }}" class="btn btn-danger px-4">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</main>

{{-- SCRIPT UNTUK MENJALANKAN TOMBOL ADD ROW --}}
<script>
    $(document).ready(function() {
        // Inisialisasi Select2 di dalam Modal
        $('.select2-origin').select2({
            // dropdownParent: $('#modalRequestPO'), // PENTING: Agar dropdown muncul di atas modal
            placeholder: "Select Origin Branch",
            allowClear: true
        });
    });

    $(document).ready(function() {
        // Inisialisasi Select2 di dalam Modal
        $('.select2-destination').select2({
            placeholder: "Select Origin Branch",
            allowClear: true
        });
    });

    $(document).ready(function() {
        // Inisialisasi Select2 di dalam Modal
        $('.select2-product').select2({
            placeholder: "Select Product",
            allowClear: true
        });
    });

    $(document).ready(function() {
        // Klik di kotak mana saja, paksa kalender keluar
        $(document).on('click', '#transferDate', function() {
            try {
                this.showPicker(); // Cara standar browser modern
            } catch (e) {
                $(this).focus(); // Fallback kalau showPicker gagal
            }
        });

        // --- Logika Add Row (Supaya tidak hilang) ---
        let rowIdx = $('#tableTransfer tbody tr').length; // Biar indexnya nyambung

        $('#btnAddNewRow').off('click').on('click', function() {
            let newRow = `
                <tr>
                    <td class="text-center align-middle iteration">${rowIdx + 1}</td>
                    <td>
                        <select name="items[${rowIdx}][product]" class="form-control form-control select2-product border-0 font-weight-bold">
                            <option value="">- Pilih Produk -</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">
                                    {{ strtoupper($p->nama_bahan) }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-right align-middle text-muted">0</td>
                    <td class="text-right align-middle font-weight-bold">0</td>
                    <td><input type="number" name="items[${rowIdx}][qty]" value="1" class="form-control form-control-sm text-center"></td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm text-danger btnRemoveRow">&times;</button>
                    </td>
                </tr>`;
            $('#itemBody').append(newRow);
            rowIdx++;
        });

        $(document).on('click', '.btnRemoveRow', function() {
            $(this).closest('tr').remove();
            $('.iteration').each(function(i) {
                $(this).html(i + 1);
            });
        });
    });

    $(document).ready(function() {
        $('#btn-save-transfer').on('click', function(e) {
            e.preventDefault(); // Mencegah form submit tradisional

            // 1. Ambil data Header (Selector disesuaikan dengan HTML)
            let data = {
                _token: "{{ csrf_token() }}",
                date: $('#transferDate').val(),
                origin_location: $('select[name="origin"]').val(),
                destination_id: $('#destinationSelect').val(),
                notes: $('#inputNotes').val(),
                items: []
            };

            // 2. Ambil data Barang (Selector disesuaikan dengan ID tabel dan Class input)
            $('#tableTransfer tbody tr').each(function() {
                let productId = $(this).find('.select2-product').val();

                // Pastikan produk sudah dipilih (value tidak kosong)
                if (productId) {
                    data.items.push({
                        product_id: productId,
                        name: $(this).find('.select2-product option:selected').text().trim(),
                        uom: 'PCS', // Hardcode karena tidak ada input UOM di tabelmu
                        qty: $(this).find('input[type="number"]').val(),
                        is_asset: 0 // Hardcode karena tidak ada checkbox is_asset di tabelmu
                    });
                }
            });

            // 3. Validasi simpel sebelum kirim
            if (data.items.length === 0) {
                alert("Pilih minimal satu barang!");
                return;
            }

            // 4. Eksekusi AJAX
            $.ajax({
                url: "{{ route('simple-transfer.store') }}",
                type: "POST",
                data: data,
                dataType: "json",
                beforeSend: function() {
                    $('#btn-save-transfer').attr('disabled', true).text('Menyimpan...');
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        window.location.href = "{{ url('/simple-transfer') }}";
                    }
                },
                error: function(xhr) {
                    let errorMsg = xhr.responseJSON ? xhr.responseJSON.message : "Terjadi kesalahan sistem";
                    alert("Gagal: " + errorMsg);
                    $('#btn-save-transfer').attr('disabled', false).text('Save');
                }
            });
        });
    });
</script>

@include('Temp.Investor.footer')
{{-- resources/views/Purchasing/Simples/editSimPurchase.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --primary: #696cff; --warn: #ffab00; --accent: #71dd37;
        --danger: #ff3e1d; --info: #03c3ec;
        --bg: #f5f5f9; --card: #fff; --border: #d9dee3;
        --shadow: 0 2px 6px rgba(67,89,113,.12); --radius: 12px;
    }
    body { background: var(--bg); }

    .form-label-custom { font-size:11px; font-weight:700; text-transform:uppercase; color:#566a7f; letter-spacing:.5px; }
    .form-control, .form-select { border-radius:8px; border:1px solid var(--border); height:40px; font-size:.875rem; color:#495057; }
    .form-control:focus, .form-select:focus { border-color:var(--primary)!important; box-shadow:0 0 0 .2rem rgba(105,108,255,.15)!important; }
    textarea.form-control { height:auto; }

    .select2-container .select2-selection--single { height:40px; border-radius:8px; border:1px solid var(--border); display:flex; align-items:center; padding:0 10px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height:40px; padding-left:0; color:#495057; font-size:.875rem; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height:40px; }

    #tablePurchaseEdit thead th { background:#f5f5f9; font-size:11px; font-weight:700; text-transform:uppercase; color:#566a7f; padding:12px 14px; border-bottom:1px solid var(--border); letter-spacing:.5px; }
    #tablePurchaseEdit tbody td { padding:10px 14px; vertical-align:middle; border-bottom:1px solid #f0f2f4; }
    #tableCostEdit thead th { background:#fffbf0; font-size:11px; font-weight:700; text-transform:uppercase; color:#b07d00; padding:10px 14px; border-bottom:1px solid #ffe082; letter-spacing:.5px; }
    #tableCostEdit tbody td { padding:10px 14px; vertical-align:middle; border-bottom:1px solid #fff8e1; }

    .btn { border-radius:8px; font-weight:600; font-size:.875rem; }

    /* Summary */
    .summary-box { background:linear-gradient(135deg, #ff8800 0%, #ffab00 100%); border-radius:12px; padding:20px 24px; color:#fff; }
    .summary-box .s-label { font-size:10px; text-transform:uppercase; letter-spacing:.6px; opacity:.85; margin-bottom:4px; }
    .summary-box .s-value { font-size:1.4rem; font-weight:800; }

    .badge-pushed { background:#e8fadf; color:#387a1e; border:1px solid #c5edaa; font-size:10px; padding:4px 10px; border-radius:20px; font-weight:700; }
    .badge-draft  { background:#f0f0f8; color:#666; border:1px solid #ddd; font-size:10px; padding:4px 10px; border-radius:20px; font-weight:700; }

    /* Tab nav untuk Items / Costs */
    .tab-nav { display:flex; gap:4px; padding:0 0 0; margin-bottom:0; }
    .tab-nav .tab-btn {
        padding:8px 18px; border-radius:8px 8px 0 0; font-size:.82rem; font-weight:700;
        border:1px solid var(--border); border-bottom:none; background:#f5f5f9;
        color:var(--muted, #8a8d9f); cursor:pointer; transition:all .15s;
    }
    .tab-nav .tab-btn.active { background:#fff; color:var(--primary); border-color:var(--border); border-bottom:2px solid #fff; margin-bottom:-1px; }
    .tab-pane { display:none; } .tab-pane.active { display:block; }
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-4">

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('simple-purchase.update', $header->purchase_num) }}" method="POST" id="editPurchaseForm">
        @csrf
        @method('PUT')

        {{-- ── Topbar ── --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="me-auto">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size:1.1rem;">
                            <i class="bi bi-pencil-square me-2" style="color:var(--warn);"></i>Edit Simple Purchase
                        </h5>
                        <span class="text-muted small">
                            No. Transaksi: <strong style="color:var(--warn);">{{ $header->purchase_num }}</strong>
                            &nbsp;·&nbsp;
                            @if($header->is_pushed ?? false)
                                <span class="badge-pushed">✓ Sudah di-push ESB</span>
                            @else
                                <span class="badge-draft">Draft</span>
                            @endif
                            @if($header->credential_code ?? null)
                            &nbsp;·&nbsp; <span class="badge bg-light border text-secondary" style="font-size:10px;">{{ $header->credential_code }}</span>
                            @endif
                        </span>
                    </div>
                    <a href="{{ route('simple-purchase.index') }}" class="btn btn-sm btn-light border" style="height:36px;">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-sm px-4 shadow-sm" id="btnSavePurchase"
                            style="height:36px; background:var(--warn); color:#fff; border:none;">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

        <div class="row g-4">

            {{-- ── Transaction Info ── --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius:15px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="p-2 bg-light rounded" style="color:var(--warn);">
                                <i class="bi bi-info-circle fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0">Transaction Information</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Supplier <span class="text-danger">*</span></label>
                                <input type="text" name="supplier_name" class="form-control shadow-none"
                                       value="{{ $header->supplier_name }}" placeholder="Nama supplier..." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" name="purchase_date" id="purchaseDate"
                                       class="form-control shadow-none"
                                       value="{{ date('Y-m-d', strtotime($header->purchase_date)) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Branch</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ $header->branch_name }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Location</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ $header->location_name ?? '-' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Purchase Type</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ $header->purchase_type_name ?? '-' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Status</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ $header->status_name ?? '-' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Items + Costs (Tab) ── --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius:15px; overflow:hidden;">
                    <div class="card-header bg-white border-bottom py-0 px-4 d-flex justify-content-between align-items-center">
                        <div class="tab-nav pt-3">
                            <button type="button" class="tab-btn active" onclick="switchTab('items', this)">
                                <i class="bi bi-box-seam me-1"></i>Items
                                <span class="badge bg-warning bg-opacity-20 text-warning ms-1" id="badge-item-count" style="font-size:10px;">{{ count($details) }}</span>
                            </button>
                            <button type="button" class="tab-btn" onclick="switchTab('costs', this)">
                                <i class="bi bi-receipt me-1"></i>Biaya Tambahan
                                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" id="badge-cost-count" style="font-size:10px;">{{ count($costs) }}</span>
                            </button>
                        </div>
                        <div id="tabAddBtn">
                            <button type="button" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1"
                                    id="btnAddItemRow" style="color:var(--warn);">
                                <i class="bi bi-plus-lg"></i> Tambah Item
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">

                        {{-- TAB: Items --}}
                        <div id="tab-items" class="tab-pane active">
                            <div class="table-responsive">
                                <table class="table mb-0" id="tablePurchaseEdit">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="50">No</th>
                                            <th>Product / Bahan</th>
                                            <th width="80" class="text-center">Satuan</th>
                                            <th width="140" class="text-center">Qty</th>
                                            <th width="170" class="text-end">Price (IDR)</th>
                                            <th width="190" class="text-end">Amount</th>
                                            <th class="text-center" width="60">#</th>
                                        </tr>
                                    </thead>
                                    <tbody id="purchaseItemBody">
                                        @forelse($details as $idx => $item)
                                        <tr class="item-row">
                                            <td class="text-center text-muted small iter">{{ $idx + 1 }}</td>
                                            <td>
                                                <select name="items[{{ $idx }}][product_id]"
                                                        class="form-select select2-product shadow-none" required style="width:100%;">
                                                    <option value="">— Pilih Produk —</option>
                                                    @foreach($products as $p)
                                                    <option value="{{ $p->id }}" {{ $p->id == $item->product_id ? 'selected' : '' }}>
                                                        {{ $p->nama_bahan }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted small uom-label">{{ $item->uom_name ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $idx }}][qty]"
                                                       class="form-control form-control-sm text-center shadow-none qty-input"
                                                       value="{{ $item->qty }}" min="0.01" step="0.01" required>
                                            </td>
                                            <td>
                                                <input type="number" name="items[{{ $idx }}][price]"
                                                       class="form-control form-control-sm text-end shadow-none price-input"
                                                       value="{{ $item->price }}" min="0" step="0.01" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm text-end bg-light shadow-none row-amount"
                                                       value="Rp {{ number_format($item->total_line, 2, ',', '.') }}" readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-light border text-danger btnRemoveItem"
                                                        style="padding:3px 9px;">&times;</button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr id="emptyItemRow">
                                            <td colspan="7" class="text-center py-4 text-muted small">
                                                Belum ada item. Klik "+ Tambah Item".
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB: Costs --}}
                        <div id="tab-costs" class="tab-pane">
                            <div class="table-responsive">
                                <table class="table mb-0" id="tableCostEdit">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="50">No</th>
                                            <th>Nama Akun / Keterangan Biaya</th>
                                            <th width="220" class="text-end">Jumlah (IDR)</th>
                                            <th class="text-center" width="60">#</th>
                                        </tr>
                                    </thead>
                                    <tbody id="costBody">
                                        @forelse($costs as $cidx => $cost)
                                        <tr class="cost-row">
                                            <td class="text-center text-muted small cost-iter">{{ $cidx + 1 }}</td>
                                            <td>
                                                <input type="text" name="costs[{{ $cidx }}][account_name]"
                                                       class="form-control form-control-sm shadow-none"
                                                       value="{{ $cost->account_name }}" placeholder="Contoh: Ongkos Kirim" required>
                                            </td>
                                            <td>
                                                <input type="number" name="costs[{{ $cidx }}][amount]"
                                                       class="form-control form-control-sm text-end shadow-none cost-amount"
                                                       value="{{ $cost->amount }}" min="0" step="0.01" required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-light border text-danger btnRemoveCost"
                                                        style="padding:3px 9px;">&times;</button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr id="emptyCostRow">
                                            <td colspan="4" class="text-center py-4 text-muted small">
                                                Belum ada biaya tambahan.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Notes & Summary ── --}}
            <div class="col-12">
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100" style="border-radius:15px;">
                            <div class="card-body p-4">
                                <label class="form-label form-label-custom mb-2">
                                    <i class="bi bi-chat-left-text me-1"></i>Catatan (Notes)
                                </label>
                                <textarea name="notes" class="form-control shadow-none" rows="4"
                                          placeholder="Tambahkan keterangan...">{{ $header->additional_info ?? $header->notes ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-box h-100 d-flex flex-column justify-content-around">
                            <div>
                                <div class="s-label">Purchase Total</div>
                                <div class="s-value" id="purchaseTotalDisplay">
                                    Rp {{ number_format($details->sum('total_line'), 2, ',', '.') }}
                                </div>
                            </div>
                            <hr style="border-color:rgba(255,255,255,.3); margin:8px 0;">
                            <div>
                                <div class="s-label">Cost Total</div>
                                <div class="s-value" id="costTotalDisplay" style="font-size:1rem; opacity:.9;">
                                    Rp {{ number_format($costs->sum('amount'), 2, ',', '.') }}
                                </div>
                            </div>
                            <hr style="border-color:rgba(255,255,255,.3); margin:8px 0;">
                            <div>
                                <div class="s-label">Grand Total</div>
                                <div class="s-value" id="grandTotalDisplay">
                                    Rp {{ number_format($header->total_amount, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- row --}}
    </form>

    {{-- Template item row --}}
    <script id="item-row-template" type="text/html">
        <tr class="item-row">
            <td class="text-center text-muted small iter">__IDX__</td>
            <td>
                <select name="items[__IDX__][product_id]" class="form-select select2-product shadow-none" required style="width:100%;">
                    <option value="">— Pilih Produk —</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_bahan }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center"><span class="text-muted small uom-label">-</span></td>
            <td><input type="number" name="items[__IDX__][qty]" class="form-control form-control-sm text-center shadow-none qty-input" value="1" min="0.01" step="0.01" required></td>
            <td><input type="number" name="items[__IDX__][price]" class="form-control form-control-sm text-end shadow-none price-input" value="0" min="0" step="0.01" required></td>
            <td><input type="text" class="form-control form-control-sm text-end bg-light shadow-none row-amount" value="Rp 0,00" readonly></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-light border text-danger btnRemoveItem" style="padding:3px 9px;">&times;</button></td>
        </tr>
    </script>

    {{-- Template cost row --}}
    <script id="cost-row-template" type="text/html">
        <tr class="cost-row">
            <td class="text-center text-muted small cost-iter">__CIDX__</td>
            <td><input type="text" name="costs[__CIDX__][account_name]" class="form-control form-control-sm shadow-none" placeholder="Contoh: Ongkos Kirim" required></td>
            <td><input type="number" name="costs[__CIDX__][amount]" class="form-control form-control-sm text-end shadow-none cost-amount" value="0" min="0" step="0.01" required></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-light border text-danger btnRemoveCost" style="padding:3px 9px;">&times;</button></td>
        </tr>
    </script>

</div>
</div>
</main>

<script>
$(document).ready(function () {

    let itemIdx = $('#purchaseItemBody .item-row').length;
    let costIdx = $('#costBody .cost-row').length;

    // ── Select2 ───────────────────────────────────────────────
    function initSelect2($scope) {
        $scope.find('.select2-product').each(function () {
            if ($(this).data('select2')) $(this).select2('destroy');
            $(this).select2({ width: '100%', placeholder: '🔍 Cari produk...', allowClear: true });
        });
    }
    initSelect2($('#purchaseItemBody'));

    // ── Tab Switch ────────────────────────────────────────────
    window.switchTab = function (tab, btn) {
        $('.tab-pane').removeClass('active');
        $('.tab-btn').removeClass('active');
        $('#tab-' + tab).addClass('active');
        $(btn).addClass('active');

        // Ganti tombol tambah sesuai tab aktif
        if (tab === 'items') {
            $('#tabAddBtn').html(`
                <button type="button" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1"
                        id="btnAddItemRow" style="color:var(--warn);">
                    <i class="bi bi-plus-lg"></i> Tambah Item
                </button>`);
        } else {
            $('#tabAddBtn').html(`
                <button type="button" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1"
                        id="btnAddCostRow" style="color:var(--warn);">
                    <i class="bi bi-plus-lg"></i> Tambah Biaya
                </button>`);
        }
    };

    // ── Tambah Item ───────────────────────────────────────────
    $(document).on('click', '#btnAddItemRow', function () {
        $('#emptyItemRow').remove();
        const html = $('#item-row-template').html().replace(/__IDX__/g, itemIdx);
        const $row = $(html);
        $('#purchaseItemBody').append($row);
        initSelect2($row);
        itemIdx++;
        recalc();
    });

    // ── Tambah Cost ───────────────────────────────────────────
    $(document).on('click', '#btnAddCostRow', function () {
        $('#emptyCostRow').remove();
        const html = $('#cost-row-template').html().replace(/__CIDX__/g, costIdx);
        $('#costBody').append(html);
        costIdx++;
        recalc();
    });

    // ── Hapus Item ────────────────────────────────────────────
    $(document).on('click', '.btnRemoveItem', function () {
        if ($('#purchaseItemBody .item-row').length > 1) {
            const $sel = $(this).closest('tr').find('.select2-product');
            if ($sel.data('select2')) $sel.select2('destroy');
            $(this).closest('tr').remove();
            reindexItems();
            recalc();
        } else {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Minimal harus ada 1 item!', confirmButtonColor: '#696cff' });
        }
    });

    // ── Hapus Cost ────────────────────────────────────────────
    $(document).on('click', '.btnRemoveCost', function () {
        $(this).closest('tr').remove();
        reindexCosts();
        recalc();
    });

    // ── Live Kalkulasi ────────────────────────────────────────
    $(document).on('input', '.qty-input, .price-input', function () {
        const $row  = $(this).closest('tr');
        const qty   = parseFloat($row.find('.qty-input').val())   || 0;
        const price = parseFloat($row.find('.price-input').val()) || 0;
        $row.find('.row-amount').val('Rp ' + (qty * price).toLocaleString('id-ID', { minimumFractionDigits: 2 }));
        recalc();
    });
    $(document).on('input', '.cost-amount', function () { recalc(); });

    function recalc() {
        let purchaseTotal = 0, itemCount = 0;
        $('#purchaseItemBody .item-row').each(function () {
            const qty   = parseFloat($(this).find('.qty-input').val())   || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            if ($(this).find('select').val()) { purchaseTotal += qty * price; itemCount++; }
        });

        let costTotal = 0;
        $('#costBody .cost-row').each(function () {
            costTotal += parseFloat($(this).find('.cost-amount').val()) || 0;
        });

        const grand = purchaseTotal + costTotal;
        const fmt   = n => 'Rp ' + n.toLocaleString('id-ID', { minimumFractionDigits: 2 });

        $('#purchaseTotalDisplay').text(fmt(purchaseTotal));
        $('#costTotalDisplay').text(fmt(costTotal));
        $('#grandTotalDisplay').text(fmt(grand));
        $('#badge-item-count').text(itemCount);
        $('#badge-cost-count').text($('#costBody .cost-row').length);
    }

    function reindexItems() {
        $('#purchaseItemBody .item-row').each(function (i) { $(this).find('.iter').text(i + 1); });
    }
    function reindexCosts() {
        $('#costBody .cost-row').each(function (i) { $(this).find('.cost-iter').text(i + 1); });
    }

    // ── Tanggal picker ────────────────────────────────────────
    $(document).on('click', '#purchaseDate', function () {
        try { this.showPicker(); } catch (e) { $(this).focus(); }
    });

    // ── Submit guard ─────────────────────────────────────────
    $('#editPurchaseForm').on('submit', function (e) {
        if ($('#purchaseItemBody .item-row').length === 0) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Tambahkan minimal 1 item sebelum menyimpan!', confirmButtonColor: '#696cff' });
            return;
        }
        $('#btnSavePurchase').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
    });

});
</script>

@include('Temp.Investor.footer')
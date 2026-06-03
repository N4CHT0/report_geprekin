{{-- resources/views/Purchasing/Simples/editSimSales.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --primary: #696cff;
        --bg: #f5f5f9;
        --card: #ffffff;
        --border: #d9dee3;
        --shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        --radius: 12px;
        --muted: #a1acb8;
        --text: #233446;
    }

    body { background: var(--bg); }

    .form-label-custom {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #566a7f;
        letter-spacing: 0.5px;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid var(--border);
        height: 40px;
        font-size: 0.875rem;
        color: #495057;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 0.2rem rgba(105,108,255,.15) !important;
    }
    textarea.form-control { height: auto; }

    /* Select2 */
    .select2-container .select2-selection--single {
        height: 40px; border-radius: 8px;
        border: 1px solid var(--border);
        display: flex; align-items: center; padding: 0 10px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px; padding-left: 0; color: #495057; font-size: 0.875rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }

    /* Table */
    #tableSalesEdit thead th {
        background: #f5f5f9; font-size: 11px; font-weight: 700;
        text-transform: uppercase; color: #566a7f; padding: 12px 14px;
        border-bottom: 1px solid var(--border); letter-spacing: 0.5px;
    }
    #tableSalesEdit tbody td {
        padding: 10px 14px; vertical-align: middle;
        border-bottom: 1px solid #f0f2f4;
    }

    .btn { border-radius: 8px; font-weight: 600; font-size: 0.875rem; }

    /* Summary box */
    .summary-box {
        background: linear-gradient(135deg, #696cff 0%, #8b8eff 100%);
        border-radius: 12px; padding: 20px 24px; color: #fff;
    }
    .summary-box .label { font-size: 10px; text-transform: uppercase; letter-spacing: .6px; opacity: .8; margin-bottom: 4px; }
    .summary-box .value { font-size: 1.6rem; font-weight: 800; }

    /* Pushed badge */
    .badge-pushed { background: #e8fadf; color: #387a1e; border: 1px solid #c5edaa; font-size: 10px; padding: 4px 10px; border-radius: 20px; font-weight: 700; }
    .badge-draft  { background: #f0f0f8; color: #666; border: 1px solid #ddd; font-size: 10px; padding: 4px 10px; border-radius: 20px; font-weight: 700; }
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-4">

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('simple-sales.update', $header->sales_num) }}" method="POST" id="editSalesForm">
        @csrf
        @method('PUT')

        {{-- ── Topbar ── --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="me-auto">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size:1.1rem;">
                            <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Simple Sales
                        </h5>
                        <span class="text-muted small">
                            No. Transaksi: <strong style="color:var(--primary);">{{ $header->sales_num }}</strong>
                            &nbsp;·&nbsp;
                            @if($header->is_pushed ?? false)
                                <span class="badge-pushed">✓ Sudah di-push ESB</span>
                            @else
                                <span class="badge-draft">Draft</span>
                            @endif
                        </span>
                    </div>
                    <a href="{{ route('simple-sales.index') }}" class="btn btn-sm btn-light border" style="height:36px;">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary px-4 shadow-sm" id="btnSaveSales" style="height:36px;">
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
                            <div class="p-2 bg-light rounded" style="color:var(--primary);">
                                <i class="bi bi-info-circle fs-5"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-0">Transaction Information</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Customer</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ $header->customer_name }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Branch</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ $header->branch_name }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Sales Date <span class="text-danger">*</span></label>
                                <input type="date" name="sales_date" id="salesDate"
                                       class="form-control shadow-none"
                                       value="{{ date('Y-m-d', strtotime($header->sales_date)) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Status</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ $header->status_name ?? '-' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">No. Referensi / PO</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ $header->notes ?? '-' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label form-label-custom mb-1">Dibuat</label>
                                <input type="text" class="form-control bg-light shadow-none"
                                       value="{{ \Carbon\Carbon::parse($header->created_at)->format('d M Y H:i') }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Item Details ── --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="border-radius:15px; overflow:hidden;">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="p-2 bg-light rounded" style="color:var(--primary);">
                                <i class="bi bi-box-seam fs-5"></i>
                            </div>
                            <h6 class="mb-0 fw-bold text-dark">Item Details</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary" id="badge-item-count" style="font-size:11px;">
                                {{ count($details) }} item
                            </span>
                        </div>
                        <button type="button" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1"
                                id="btnAddRow" style="color:var(--primary);">
                            <i class="bi bi-plus-lg"></i> Tambah Baris
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" id="tableSalesEdit">
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
                                <tbody id="salesItemBody">
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
                                            <button type="button" class="btn btn-sm btn-light border text-danger btnRemove"
                                                    style="padding:3px 9px;">&times;</button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr id="emptyRow">
                                        <td colspan="7" class="text-center py-4 text-muted small">
                                            Belum ada item. Klik "+ Tambah Baris" untuk menambahkan.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
                                    <i class="bi bi-chat-left-text me-1"></i> Catatan (Notes)
                                </label>
                                <textarea name="notes" class="form-control shadow-none" rows="4"
                                          placeholder="Tambahkan keterangan...">{{ $header->notes }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-box h-100 d-flex flex-column justify-content-center">
                            <div class="mb-3">
                                <div class="label">Total Item (Jenis)</div>
                                <div class="value" id="totalItemCount" style="font-size:1.4rem;">{{ count($details) }}</div>
                            </div>
                            <hr style="border-color:rgba(255,255,255,.3);margin:8px 0;">
                            <div>
                                <div class="label">Grand Total</div>
                                <div class="value" id="grandTotalDisplay">
                                    Rp {{ number_format($header->total_amount, 2, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- row --}}
    </form>

    {{-- Template baris baru (tidak pernah di-render) --}}
    <script id="row-template-sales" type="text/html">
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
            <td>
                <input type="number" name="items[__IDX__][qty]"
                       class="form-control form-control-sm text-center shadow-none qty-input"
                       value="1" min="0.01" step="0.01" required>
            </td>
            <td>
                <input type="number" name="items[__IDX__][price]"
                       class="form-control form-control-sm text-end shadow-none price-input"
                       value="0" min="0" step="0.01" required>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm text-end bg-light shadow-none row-amount"
                       value="Rp 0,00" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-light border text-danger btnRemove"
                        style="padding:3px 9px;">&times;</button>
            </td>
        </tr>
    </script>

</div>
</div>
</main>

<script>
$(document).ready(function () {

    let rowIdx = $('#salesItemBody .item-row').length;

    // ── Init Select2 ──────────────────────────────────────────
    function initSelect2($scope) {
        $scope.find('.select2-product').each(function () {
            if ($(this).data('select2')) $(this).select2('destroy');
            $(this).select2({
                width       : '100%',
                placeholder : '🔍 Cari produk...',
                allowClear  : true,
                language    : { noResults: () => 'Produk tidak ditemukan' }
            });
        });
    }
    initSelect2($('#salesItemBody'));

    // ── Tambah Baris ──────────────────────────────────────────
    $('#btnAddRow').on('click', function () {
        $('#emptyRow').remove();
        const html = $('#row-template-sales').html().replace(/__IDX__/g, rowIdx);
        const $row = $(html);
        $('#salesItemBody').append($row);
        initSelect2($row);
        rowIdx++;
        recalc();
    });

    // ── Hapus Baris ───────────────────────────────────────────
    $(document).on('click', '.btnRemove', function () {
        if ($('#salesItemBody .item-row').length > 1) {
            const $sel = $(this).closest('tr').find('.select2-product');
            if ($sel.data('select2')) $sel.select2('destroy');
            $(this).closest('tr').remove();
            reindex();
            recalc();
        } else {
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Minimal harus ada 1 item!', confirmButtonColor: '#696cff' });
        }
    });

    // ── Live kalkulasi ────────────────────────────────────────
    $(document).on('input', '.qty-input, .price-input', function () {
        const $row  = $(this).closest('tr');
        const qty   = parseFloat($row.find('.qty-input').val())   || 0;
        const price = parseFloat($row.find('.price-input').val()) || 0;
        $row.find('.row-amount').val('Rp ' + (qty * price).toLocaleString('id-ID', { minimumFractionDigits: 2 }));
        recalc();
    });

    function recalc() {
        let grand = 0, count = 0;
        $('#salesItemBody .item-row').each(function () {
            const qty   = parseFloat($(this).find('.qty-input').val())   || 0;
            const price = parseFloat($(this).find('.price-input').val()) || 0;
            if ($(this).find('select').val()) { grand += qty * price; count++; }
        });
        $('#totalItemCount').text(count);
        $('#grandTotalDisplay').text('Rp ' + grand.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
        $('#badge-item-count').text(count + ' item');
    }

    function reindex() {
        $('#salesItemBody .item-row').each(function (i) { $(this).find('.iter').text(i + 1); });
    }

    // ── Tanggal picker ────────────────────────────────────────
    $(document).on('click', '#salesDate', function () {
        try { this.showPicker(); } catch (e) { $(this).focus(); }
    });

    // ── Submit guard ─────────────────────────────────────────
    $('#editSalesForm').on('submit', function (e) {
        if ($('#salesItemBody .item-row').length === 0) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Tambahkan minimal 1 item sebelum menyimpan!', confirmButtonColor: '#696cff' });
            return;
        }
        $('#btnSaveSales').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');
    });

});
</script>

@include('Temp.Investor.footer')
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
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

    #piTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #2c3e50;
    }

    #piTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
    }

    #piTable tbody tr {
        transition: all 0.2s;
    }

    #piTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    .bg-pi-subtle {
        background-color: #f0f0ff !important;
        color: #696cff !important;
        border: 1px solid #e1e1ff !important;
    }

    .bg-overdue-subtle {
        background-color: #ffe5e5 !important;
        color: #ff3e1d !important;
        border: 1px solid #ffd1d1 !important;
    }

    .icon-shape {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .price-diff-warning {
        background-color: #fff8e1;
        border-left: 3px solid #ffab00;
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
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Purchase Invoice List</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Account Payable</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Purchase Invoice</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-secondary d-flex align-items-center">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Export Report
                                </button>
                                <button type="button" class="btn btn-primary px-3 shadow-sm d-flex align-items-center text-white"
                                    style="background-color: #696cff; border-color: #696cff;"
                                    data-bs-toggle="modal" data-bs-target="#modalCreatePI">
                                    <i class="bi bi-plus-circle me-1"></i> Add Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ SUMMARY WIDGETS ═══ --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-dark">{{ $summary['total'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Total Invoice</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-warning">{{ $summary['pending'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Pending Approval</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-success">{{ $summary['approved'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Approved / Unpaid</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px; border-left: 3px solid #ff3e1d !important;">
                        <div class="fw-bold fs-5 text-danger">
                            Rp {{ number_format($summary['outstanding'], 0, ',', '.') }}
                        </div>
                        <div class="text-muted" style="font-size:11px;">Total Outstanding</div>
                    </div>
                </div>
            </div>

            {{-- ═══ TABLE ═══ --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-wallet2 me-2" style="color: #696cff;"></i> Supplier Billing
                    </h6>
                </div>
                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="piTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small fw-bolder text-secondary">
                                    <th class="text-center" style="width:50px;"><input type="checkbox" id="checkAllPI" class="form-check-input"></th>
                                    <th>PI Info</th>
                                    <th>Supplier</th>
                                    <th>Total Bill</th>
                                    <th>Due Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @forelse($invoices as $inv)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input">
                                    </td>

                                    {{-- PI Info --}}
                                    <td>
                                        <div class="fw-bold" style="color:#696cff; font-size:0.85rem;">
                                            {{ $inv->invoice_number }}
                                        </div>
                                        @if($inv->supplier_invoice_number)
                                        <small class="text-muted d-block" style="font-size:10px;">
                                            Supplier: {{ $inv->supplier_invoice_number }}
                                        </small>
                                        @endif
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}
                                        </small>
                                    </td>

                                    {{-- Supplier --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light me-2" style="color:#696cff;">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium small">{{ $inv->supplier_name }}</div>
                                                <small class="text-muted" style="font-size:10px;">
                                                    GR: {{ $inv->gr_number }} · PO: {{ $inv->po_number }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Total Bill --}}
                                    <td>
                                        <div class="fw-bold small">
                                            Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                                        </div>
                                        @if($inv->outstanding > 0)
                                        <small class="text-danger" style="font-size:10px;">
                                            Outstanding: Rp {{ number_format($inv->outstanding, 0, ',', '.') }}
                                        </small>
                                        @endif
                                    </td>

                                    {{-- Due Date --}}
                                    <td>
                                        <small class="fw-bold {{ $inv->is_overdue ? 'text-danger' : 'text-muted' }}">
                                            <i class="bi bi-clock-history me-1"></i>
                                            {{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}
                                            @if($inv->is_overdue)
                                            <span class="badge bg-danger ms-1" style="font-size:9px;">OVERDUE</span>
                                            @endif
                                        </small>
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @php
                                        $statusMap = [
                                        'DRAFT' => ['bg-secondary-subtle text-secondary border', 'DRAFT'],
                                        'PENDING' => ['bg-warning-subtle text-warning border', 'PENDING'],
                                        'APPROVED' => ['bg-pi-subtle border', 'APPROVED'],
                                        'PARTIAL_PAID' => ['bg-info-subtle text-info border', 'PARTIAL PAID'],
                                        'PAID' => ['bg-success-subtle text-success border', 'PAID'],
                                        'CANCELLED' => ['bg-danger-subtle text-danger border', 'CANCELLED'],
                                        ];
                                        [$cls, $lbl] = $statusMap[$inv->status] ?? ['bg-secondary-subtle border', $inv->status];
                                        @endphp
                                        <span class="badge {{ $cls }} px-3 py-2 fw-bold" style="font-size:0.7rem; border-radius:8px;">
                                            {{ $lbl }}
                                        </span>
                                    </td>

                                    {{-- Action --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnDetailPI" href="#" data-id="{{ $inv->id }}">
                                                        <i class="bi bi-eye text-primary me-2"></i> Detail Bill
                                                    </a>
                                                </li>

                                                @if($inv->status === 'PENDING')
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnApprovePI" href="#"
                                                        data-id="{{ $inv->id }}" data-pi="{{ $inv->invoice_number }}">
                                                        <i class="bi bi-check-circle text-success me-2"></i> Approve Invoice
                                                    </a>
                                                </li>
                                                @endif

                                                @if(in_array($inv->status, ['APPROVED', 'PARTIAL_PAID']))
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnPayPI" href="#"
                                                        data-id="{{ $inv->id }}"
                                                        data-pi="{{ $inv->invoice_number }}"
                                                        data-outstanding="{{ $inv->outstanding }}">
                                                        <i class="bi bi-credit-card text-success me-2"></i> Pay Supplier
                                                    </a>
                                                </li>
                                                @endif

                                                <li>
                                                    <a class="dropdown-item rounded-2" href="{{ route('purchase.invoice.print', $inv->id) }}" target="_blank">
                                                        <i class="bi bi-printer text-dark me-2"></i> Print Invoice
                                                    </a>
                                                </li>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-danger" href="#">
                                                        <i class="bi bi-exclamation-triangle me-2"></i> Dispute
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-wallet2 fs-4 d-block mb-2"></i>
                                        Belum ada Purchase Invoice.
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalCreatePI">Buat sekarang</a>
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
</main>


{{-- ═══════════════════════════════════════════════════════ --}}
{{-- MODAL: CREATE PURCHASE INVOICE --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCreatePI" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Create Purchase Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pb-4">
                <form id="formCreatePI">
                    @csrf

                    {{-- Transaction Information --}}
                    <div class="card border shadow-none mb-4" style="background-color: #f8f9fa;">
                        <div class="card-header bg-white py-2 fw-bold small border-bottom">Transaction Information</div>
                        <div class="card-body">
                            <div class="row g-3">

                                {{-- Pilih GR (sumber 3-way match) --}}
                                <div class="col-md-6">
                                    <label class="small fw-bold">
                                        Goods Receipt Reference <span class="text-danger">*</span>
                                        <span class="badge bg-info-subtle text-info ms-1" style="font-size:9px;">3-Way Match</span>
                                    </label>
                                    <select name="goods_receipt_id" id="selectGR" class="form-select form-select-sm shadow-none border-primary" required>
                                        <option value="">-- Pilih Goods Receipt --</option>
                                        @forelse($readyGRs as $gr)
                                        <option value="{{ $gr->id }}"
                                            data-supplier="{{ $gr->supplier_name }}"
                                            data-supplier-id="{{ $gr->supplier_id }}"
                                            data-po="{{ $gr->po_number }}"
                                            data-total="{{ $gr->total_amount }}">
                                            [{{ $gr->gr_status }}] {{ $gr->gr_number }} — {{ $gr->supplier_name }}
                                            (PO: {{ $gr->po_number }}, {{ \Carbon\Carbon::parse($gr->receipt_date)->format('d M Y') }})
                                        </option>
                                        @empty
                                        <option value="" disabled>
                                            — Tidak ada GR siap invoice. Pastikan GR sudah berstatus RECEIVED/PARTIAL —
                                        </option>
                                        @endforelse
                                    </select>
                                    <small class="text-muted">
                                        GR berstatus <strong>RECEIVED</strong> atau <strong>PARTIAL</strong> yang belum punya invoice aktif.
                                        @if($readyGRs->isEmpty())
                                            <span class="text-danger fw-bold">⚠ Tidak ada GR tersedia — cek apakah GR sudah dikonfirmasi.</span>
                                        @else
                                            <span class="text-success">✓ {{ $readyGRs->count() }} GR tersedia.</span>
                                        @endif
                                    </small>
                                </div>

                                {{-- Supplier (auto-fill dari GR) --}}
                                <div class="col-md-6">
                                    <label class="small fw-bold">Supplier</label>
                                    <input type="text" id="displaySupplier" class="form-control form-control-sm bg-light shadow-none" readonly placeholder="Otomatis dari GR">
                                    <input type="hidden" name="supplier_id" id="hiddenSupplierId">
                                </div>

                                {{-- Nomor Invoice Supplier --}}
                                <div class="col-md-4">
                                    <label class="small fw-bold">
                                        Supplier Invoice Number
                                        <i class="bi bi-info-circle-fill ms-1 text-muted" title="Nomor yang tertera di invoice fisik dari supplier"></i>
                                    </label>
                                    <input type="text" name="supplier_invoice_number" class="form-control form-control-sm shadow-none" placeholder="e.g. INV/SUP/2026/001">
                                </div>

                                {{-- Currency --}}
                                <div class="col-md-2">
                                    <label class="small fw-bold">Currency</label>
                                    <select name="currency" class="form-select form-select-sm shadow-none">
                                        <option value="IDR">Rupiah (IDR)</option>
                                    </select>
                                </div>

                                {{-- Invoice Date --}}
                                <div class="col-md-3">
                                    <label class="small fw-bold">Invoice Date <span class="text-danger">*</span></label>
                                    <input type="date" name="invoice_date" id="inputInvoiceDate"
                                        class="form-control form-control-sm shadow-none" value="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- Due Date --}}
                                <div class="col-md-3">
                                    <label class="small fw-bold">Due Date <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="dueDays" class="form-control shadow-none" placeholder="N hari" min="0" value="30">
                                        <input type="date" name="due_date" id="inputDueDate"
                                            class="form-control shadow-none" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                    </div>
                                    <small class="text-muted">Isi hari atau tanggal langsung</small>
                                </div>

                                {{-- VAT % --}}
                                <div class="col-md-2">
                                    <label class="small fw-bold">VAT %</label>
                                    <input type="number" name="vat_percent" id="inputVat"
                                        class="form-control form-control-sm shadow-none" value="11" min="0" max="100" step="0.01">
                                </div>

                                {{-- Discount header --}}
                                <div class="col-md-2">
                                    <label class="small fw-bold">Discount (Rp)</label>
                                    <input type="number" name="discount" id="inputDiscount"
                                        class="form-control form-control-sm shadow-none" value="0" min="0">
                                </div>

                                {{-- Notes --}}
                                <div class="col-md-8">
                                    <label class="small fw-bold">Notes</label>
                                    <input type="text" name="notes" class="form-control form-control-sm shadow-none" placeholder="Catatan tambahan (opsional)">
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- 3-Way Match Warning (hidden by default) --}}
                    <div id="threewayAlert" class="alert alert-warning d-none py-2 px-3 mb-3" style="font-size:12px;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Perhatian:</strong>
                        Beberapa item memiliki <strong>perbedaan harga</strong> antara PO dan GR.
                        Qty invoice tidak boleh melebihi qty yang sudah diterima di GR.
                    </div>

                    {{-- Loading --}}
                    <div id="loadingItems" class="text-center py-3 d-none">
                        <div class="spinner-border spinner-border-sm" style="color:#696cff;"></div>
                        <span class="ms-2 small">Memuat item GR dan validasi 3-way match...</span>
                    </div>

                    {{-- Purchase Invoice Detail Table --}}
                    <div class="card border shadow-none mb-4" id="itemsSection">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center border-bottom">
                            <span class="fw-bold small">Purchase Invoice Detail</span>
                            <span class="badge bg-warning text-dark small" style="font-size:10px;">
                                ⚠ Qty invoice tidak boleh melebihi qty yang diterima (3-way match)
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="min-width: 900px;">
                                <thead class="bg-light small">
                                    <tr>
                                        <th class="ps-3">Item / Bahan</th>
                                        <th class="text-center">Satuan</th>
                                        <th class="text-center">Qty PO</th>
                                        <th class="text-center">Qty Diterima (GR)</th>
                                        <th class="text-center">Qty Invoice <span class="text-danger">*</span></th>
                                        <th class="text-end">Harga PO</th>
                                        <th class="text-end">Harga Invoice</th>
                                        <th class="text-end pe-3">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="piItemContainer">
                                    <tr class="text-muted text-center">
                                        <td colspan="8" class="py-4">
                                            <i class="bi bi-arrow-up-circle me-1"></i>
                                            Pilih GR di atas untuk menampilkan item
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="row">
                        <div class="col-md-7">
                            <div class="card border shadow-none h-100">
                                <div class="card-header bg-white py-2 fw-bold small border-bottom">Additional Notes</div>
                                <div class="card-body">
                                    <textarea name="additional_info" class="form-control shadow-none" rows="5"
                                        placeholder="Catatan tambahan, informasi khusus dari supplier, dll..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card border shadow-none p-3" style="background-color: #fcfcfc;">
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-6 small text-muted">Subtotal</div>
                                    <div class="col-6">
                                        <input type="text" id="dispSubtotal" class="form-control form-control-sm text-end bg-light" readonly value="0">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-6 small text-muted">Discount</div>
                                    <div class="col-6">
                                        <input type="text" id="dispDiscount" class="form-control form-control-sm text-end bg-light" readonly value="0">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-6 small text-muted">DPP</div>
                                    <div class="col-6">
                                        <input type="text" id="dispDpp" class="form-control form-control-sm text-end bg-light" readonly value="0">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-6 small text-muted">PPN (<span id="dispVatPct">11</span>%)</div>
                                    <div class="col-6">
                                        <input type="text" id="dispVat" class="form-control form-control-sm text-end bg-light" readonly value="0">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center border-top pt-2 mt-1">
                                    <div class="col-6 small fw-bold">Total Invoice</div>
                                    <div class="col-6">
                                        <input type="text" id="dispTotal" class="form-control form-control-sm text-end fw-bold bg-light" readonly value="0" style="color:#696cff;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                <div class="me-auto"></div>
                <button type="button" class="btn btn-sm btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-sm btn-primary px-4" id="btnSavePI"
                    style="background-color:#696cff; border-color:#696cff;">
                    <i class="bi bi-save me-1"></i> Save Invoice
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══ MODAL: DETAIL PI ═══ --}}
<div class="modal fade" id="modalDetailPI" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailPITitle">Detail Purchase Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailPIBody">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm" style="color:#696cff;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ MODAL: RECORD PAYMENT ═══ --}}
<div class="modal fade" id="modalPayPI" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Catat Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPayPI">
                    <div class="mb-3">
                        <label class="small fw-bold">Invoice</label>
                        <input type="text" id="payPiNumber" class="form-control form-control-sm bg-light" readonly>
                        <input type="hidden" id="payPiId">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Outstanding</label>
                        <input type="text" id="payOutstanding" class="form-control form-control-sm bg-light fw-bold text-danger" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Jumlah Bayar <span class="text-danger">*</span></label>
                        <input type="number" id="payAmount" name="payment_amount" class="form-control form-control-sm" placeholder="0" min="1">
                    </div>
                    <div class="mb-3">
                        <label class="small fw-bold">Tanggal Bayar <span class="text-danger">*</span></label>
                        <input type="date" id="payDate" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-success px-4" id="btnConfirmPay">
                    <i class="bi bi-credit-card me-1"></i> Konfirmasi Bayar
                </button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    $(document).ready(function() {

        // ─── DataTable ─────────────────────────────────────────────
        if ($.fn.DataTable.isDataTable('#piTable')) $('#piTable').DataTable().destroy();
        $('#piTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Bills...",
                lengthMenu: "_MENU_"
            },
            columnDefs: [{
                targets: [0, 6],
                orderable: false
            }]
        });
        $('#checkAllPI').click(function() {
            $('.form-check-input').prop('checked', this.checked);
        });

        // ─── Due date auto-calc ────────────────────────────────────
        $('#dueDays').on('input', function() {
            const days = parseInt($(this).val()) || 0;
            const base = new Date($('#inputInvoiceDate').val() || new Date());
            base.setDate(base.getDate() + days);
            $('#inputDueDate').val(base.toISOString().split('T')[0]);
        });

        // ─── VAT % → recalculate ──────────────────────────────────
        $('#inputVat, #inputDiscount').on('input', recalcTotals);

        // ─── Select GR → load items via AJAX ─────────────────────
        $('#selectGR').on('change', function() {
            const grId = $(this).val();
            const opt = $(this).find('option:selected');

            $('#displaySupplier').val('');
            $('#hiddenSupplierId').val('');
            $('#piItemContainer').html('<tr class="text-muted text-center"><td colspan="8" class="py-4">Pilih GR di atas untuk menampilkan item</td></tr>');
            $('#threewayAlert').addClass('d-none');
            recalcTotals();

            if (!grId) return;

            $('#displaySupplier').val(opt.data('supplier'));
            $('#hiddenSupplierId').val(opt.data('supplier-id'));
            $('#loadingItems').removeClass('d-none');

            $.ajax({
                url: '/scm/purchase-invoice/gr-details/' + grId,
                method: 'GET',
                success: function(res) {
                    $('#loadingItems').addClass('d-none');

                    if (res.status !== 'success') {
                        Swal.fire('Error', res.message, 'error');
                        return;
                    }

                    const hasPriceDiff = res.items.some(i => i.has_price_diff);
                    if (hasPriceDiff) $('#threewayAlert').removeClass('d-none');

                    let html = '';
                    res.items.forEach(function(item, idx) {
                        const rowClass = item.has_price_diff ? 'price-diff-warning' : '';
                        const priceDiffBadge = item.has_price_diff ?
                            `<span class="badge bg-warning text-dark ms-1" style="font-size:9px;">
                             Δ ${item.price_diff > 0 ? '+' : ''}${item.price_diff.toFixed(2)}
                           </span>` : '';

                        html += `
                    <tr class="${rowClass}">
                        <input type="hidden" name="items[${idx}][grd_id]" value="${item.grd_id}">
                        <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${idx}][unit_id]" value="${item.unit_id}">
                        <input type="hidden" name="items[${idx}][qty_received]" value="${item.qty_received}">

                        <td class="ps-3">
                            <div class="fw-medium small">${item.nama_bahan}</div>
                            ${priceDiffBadge}
                        </td>
                        <td class="text-center small">${item.nama_unit || '-'}</td>
                        <td class="text-center small">${item.po_qty}</td>

                        {{-- Qty Diterima (GR) - ceiling for 3-way match --}}
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success fw-bold">${item.qty_received}</span>
                            ${item.qty_already_invoiced > 0
                                ? `<br><small class="text-danger" style="font-size:10px;">sudah invoice: ${item.qty_already_invoiced}</small>`
                                : ''}
                        </td>

                        {{-- Qty Invoice - user input, max = qty_max_invoice --}}
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center item-qty-invoice"
                                name="items[${idx}][qty_invoiced]"
                                data-max="${item.qty_max_invoice}"
                                value="${item.qty_max_invoice}"
                                min="0" max="${item.qty_max_invoice}" step="0.01"
                                style="width:80px; margin:auto;">
                            <small class="text-muted" style="font-size:9px;">maks: ${item.qty_max_invoice}</small>
                        </td>

                        <td class="text-end small text-muted">
                            Rp ${numberFmt(item.po_price)}
                        </td>

                        {{-- Harga Invoice (bisa diubah, tapi akan di-flag jika beda dari GR) --}}
                        <td class="text-end">
                            <input type="number" class="form-control form-control-sm text-end item-price"
                                name="items[${idx}][price]"
                                value="${item.gr_price}" min="0" step="0.01"
                                style="width:110px; margin-left:auto;">
                        </td>

                        <td class="text-end pe-3 small fw-bold item-subtotal">
                            Rp ${numberFmt(item.qty_max_invoice * item.gr_price)}
                        </td>
                    </tr>`;
                    });

                    $('#piItemContainer').html(html);
                    bindItemInputs();
                    recalcTotals();
                },
                error: function(xhr) {
                    $('#loadingItems').addClass('d-none');
                    Swal.fire('Error', xhr.responseJSON?.message ?? 'Gagal memuat item GR.', 'error');
                }
            });
        });

        // ─── Bind qty/price inputs → recalc subtotal + totals ────
        function bindItemInputs() {
            // 3-way match: warn if qty_invoiced > max
            $(document).on('input', '.item-qty-invoice', function() {
                const max = parseFloat($(this).data('max'));
                const val = parseFloat($(this).val()) || 0;
                if (val > max) {
                    $(this).addClass('border-danger');
                    $(this).next('small').text(`⚠ maks: ${max}`).addClass('text-danger');
                } else {
                    $(this).removeClass('border-danger');
                    $(this).next('small').text(`maks: ${max}`).removeClass('text-danger');
                }
                updateRowSubtotal($(this).closest('tr'));
                recalcTotals();
            });

            $(document).on('input', '.item-price', function() {
                updateRowSubtotal($(this).closest('tr'));
                recalcTotals();
            });
        }

        function updateRowSubtotal($row) {
            const qty = parseFloat($row.find('.item-qty-invoice').val()) || 0;
            const price = parseFloat($row.find('.item-price').val()) || 0;
            $row.find('.item-subtotal').text('Rp ' + numberFmt(qty * price));
        }

        function recalcTotals() {
            let subtotal = 0;
            $('#piItemContainer tr').each(function() {
                const qty = parseFloat($(this).find('.item-qty-invoice').val()) || 0;
                const price = parseFloat($(this).find('.item-price').val()) || 0;
                subtotal += qty * price;
            });

            const discount = parseFloat($('#inputDiscount').val()) || 0;
            const vatPct = parseFloat($('#inputVat').val()) || 0;
            const dpp = subtotal - discount;
            const vat = Math.round(dpp * vatPct / 100);
            const total = dpp + vat;

            $('#dispSubtotal').val('Rp ' + numberFmt(subtotal));
            $('#dispDiscount').val('Rp ' + numberFmt(discount));
            $('#dispDpp').val('Rp ' + numberFmt(dpp));
            $('#dispVatPct').text(vatPct);
            $('#dispVat').val('Rp ' + numberFmt(vat));
            $('#dispTotal').val('Rp ' + numberFmt(total));
        }

        function numberFmt(n) {
            return Math.round(n).toLocaleString('id-ID');
        }

        // ─── Save PI ───────────────────────────────────────────────
        $('#btnSavePI').on('click', function() {
            if (!$('#selectGR').val()) {
                Swal.fire('Perhatian', 'Pilih GR terlebih dahulu.', 'warning');
                return;
            }

            // Cek apakah ada qty_invoiced yang melebihi max (3-way match)
            let hasViolation = false;
            $('.item-qty-invoice').each(function() {
                const val = parseFloat($(this).val()) || 0;
                const max = parseFloat($(this).data('max')) || 0;
                if (val > max) {
                    hasViolation = true;
                    return false;
                }
            });

            if (hasViolation) {
                Swal.fire('3-Way Match Gagal',
                    'Satu atau lebih item memiliki qty invoice melebihi qty yang diterima. Koreksi terlebih dahulu.',
                    'error');
                return;
            }

            const formData = new FormData(document.getElementById('formCreatePI'));
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: '/scm/purchase-invoice',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('#btnSavePI').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Save Invoice');
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Invoice Dibuat!',
                            html: `<b>${res.pi_number}</b> berhasil dibuat.<br>Total: <b>Rp ${res.total.toLocaleString('id-ID')}</b>`,
                            confirmButtonColor: '#696cff',
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    $('#btnSavePI').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Save Invoice');
                    Swal.fire('Error', xhr.responseJSON?.message ?? 'Terjadi kesalahan.', 'error');
                }
            });
        });

        // ─── Approve PI ────────────────────────────────────────────
        $(document).on('click', '.btnApprovePI', function() {
            const piId = $(this).data('id');
            const piNum = $(this).data('pi');
            Swal.fire({
                title: `Approve ${piNum}?`,
                text: 'Invoice akan berstatus APPROVED dan siap untuk pembayaran.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Approve!',
                confirmButtonColor: '#28a745',
            }).then(r => {
                if (!r.isConfirmed) return;
                $.ajax({
                    url: `/scm/purchase-invoice/${piId}/approve`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: res => {
                        if (res.status === 'success') {
                            Swal.fire({
                                    icon: 'success',
                                    title: 'Approved!',
                                    text: res.message,
                                    confirmButtonColor: '#696cff'
                                })
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: xhr => Swal.fire('Error', xhr.responseJSON?.message ?? 'Server error.', 'error')
                });
            });
        });

        // ─── Record Payment ────────────────────────────────────────
        $(document).on('click', '.btnPayPI', function() {
            $('#payPiId').val($(this).data('id'));
            $('#payPiNumber').val($(this).data('pi'));
            const outstanding = parseFloat($(this).data('outstanding')) || 0;
            $('#payOutstanding').val('Rp ' + numberFmt(outstanding));
            $('#payAmount').val(outstanding).attr('max', outstanding);
            $('#modalPayPI').modal('show');
        });

        $('#btnConfirmPay').on('click', function() {
            const piId = $('#payPiId').val();
            $.ajax({
                url: `/scm/purchase-invoice/${piId}/pay`,
                method: 'POST',
                data: {
                    payment_amount: $('#payAmount').val(),
                    payment_date: $('#payDate').val(),
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: res => {
                    if (res.status === 'success') {
                        $('#modalPayPI').modal('hide');
                        Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran Dicatat!',
                                text: res.message,
                                confirmButtonColor: '#696cff'
                            })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: xhr => Swal.fire('Error', xhr.responseJSON?.message ?? 'Server error.', 'error')
            });
        });

        // ─── Detail PI ─────────────────────────────────────────────
        $(document).on('click', '.btnDetailPI', function(e) {
            e.preventDefault();
            const piId = $(this).data('id');
            $('#detailPIBody').html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:#696cff;"></div></div>');
            $('#modalDetailPI').modal('show');

            $.ajax({
                url: `/scm/purchase-invoice/${piId}`,
                method: 'GET',
                success: function(res) {
                    if (res.status !== 'success') {
                        $('#detailPIBody').html('<p class="text-danger">Gagal memuat detail.</p>');
                        return;
                    }
                    const pi = res.pi;
                    const details = res.details;
                    $('#detailPITitle').text(`Detail PI: ${pi.invoice_number}`);

                    let rows = '';
                    details.forEach(d => {
                        rows += `<tr>
                        <td>${d.nama_bahan}</td><td>${d.nama_unit || '-'}</td>
                        <td class="text-center">${d.qty_received}</td>
                        <td class="text-center fw-bold" style="color:#696cff;">${d.qty_invoiced}</td>
                        <td class="text-end">Rp ${Math.round(d.price).toLocaleString('id-ID')}</td>
                        <td class="text-end fw-bold">Rp ${Math.round(d.subtotal).toLocaleString('id-ID')}</td>
                    </tr>`;
                    });

                    $('#detailPIBody').html(`
                    <div class="row g-2 mb-3">
                        <div class="col-6"><small class="text-muted">Invoice Number</small><div class="fw-bold" style="color:#696cff">${pi.invoice_number}</div></div>
                        <div class="col-6"><small class="text-muted">Supplier Inv No.</small><div>${pi.supplier_invoice_number || '-'}</div></div>
                        <div class="col-6"><small class="text-muted">Supplier</small><div>${pi.supplier_name}</div></div>
                        <div class="col-6"><small class="text-muted">GR / PO</small><div>${pi.gr_number} / ${pi.po_number}</div></div>
                        <div class="col-3"><small class="text-muted">Invoice Date</small><div>${pi.invoice_date}</div></div>
                        <div class="col-3"><small class="text-muted">Due Date</small><div>${pi.due_date}</div></div>
                        <div class="col-3"><small class="text-muted">Status</small><div><span class="badge bg-primary">${pi.status}</span></div></div>
                        <div class="col-3"><small class="text-muted">Outstanding</small>
                            <div class="fw-bold text-danger">Rp ${Math.round(pi.total_amount - pi.paid_amount).toLocaleString('id-ID')}</div>
                        </div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr><th>Bahan</th><th>Satuan</th><th class="text-center">Qty GR</th>
                                    <th class="text-center">Qty Invoice</th><th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <small class="text-muted">DPP: Rp ${Math.round(pi.dpp).toLocaleString('id-ID')} &nbsp;|&nbsp;
                        PPN: Rp ${Math.round(pi.vat_amount).toLocaleString('id-ID')} &nbsp;|&nbsp;</small>
                        <strong>Total: Rp ${Math.round(pi.total_amount).toLocaleString('id-ID')}</strong>
                    </div>
                `);
                },
                error: () => $('#detailPIBody').html('<p class="text-danger">Gagal memuat detail PI.</p>')
            });
        });

    });
</script>
@endpush

@include('Temp.Investor.footer')
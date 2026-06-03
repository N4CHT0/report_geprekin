@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { padding: 20px 25px !important; }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { padding: 20px 25px !important; }
    table.dataTable { width: 100% !important; margin: 0 !important; border-collapse: collapse !important; }
    #siTable thead th {
        background-color: #f8f9fa !important; padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important; font-size: 11px;
        font-weight: 700; text-transform: uppercase; color: #2c3e50;
    }
    #siTable tbody td { padding: 1.2rem 25px !important; vertical-align: middle !important; border-bottom: 1px solid #f8f9fa !important; }
    #siTable tbody tr:hover { background-color: rgba(0,207,221,0.04) !important; box-shadow: inset 4px 0 0 #00cfdd; }
    .bg-invoice-subtle { background-color: #e0f9fa !important; color: #00cfdd !important; border: 1px solid #b2f2f5 !important; }
    .bg-unpaid-subtle { background-color: #ffe5e5 !important; color: #ff3e1d !important; border: 1px solid #ffd1d1 !important; }
    .icon-shape { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; }
    .price-diff-warning { background-color: #fff8e1; border-left: 3px solid #ffab00; }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            {{-- ═══ HEADER ═══ --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Sales Invoice List</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Finance</a></li>
                                    <li class="breadcrumb-item active" style="color: #00cfdd;" aria-current="page">Sales Invoice</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info d-flex align-items-center">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Bulk Print
                                </button>
                                <button type="button" class="btn btn-info px-3 shadow-sm d-flex align-items-center text-white"
                                    style="background-color: #00cfdd; border-color: #00cfdd;"
                                    data-bs-toggle="modal" data-bs-target="#modalCreateSI">
                                    <i class="bi bi-plus-circle me-1"></i> Generate Invoice
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
                        <div class="fw-bold fs-4" style="color:#00cfdd;">{{ $summary['draft'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Issued / Unpaid</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-success">{{ $summary['paid'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Paid</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px; border-left: 3px solid #ff3e1d !important;">
                        <div class="fw-bold fs-5 text-danger">
                            Rp {{ number_format($summary['outstanding'], 0, ',', '.') }}
                        </div>
                        <div class="text-muted" style="font-size:11px;">Total AR Outstanding</div>
                    </div>
                </div>
            </div>

            {{-- ═══ TABLE ═══ --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-receipt-cutoff me-2" style="color: #00cfdd;"></i> Billing Transactions
                    </h6>
                </div>
                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="siTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small fw-bolder text-secondary">
                                    <th class="text-center" style="width:50px;"><input type="checkbox" id="checkAllSI" class="form-check-input"></th>
                                    <th>Invoice Info</th>
                                    <th>Bill To</th>
                                    <th>Total Amount</th>
                                    <th>Due Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @forelse($invoices as $inv)
                                <tr>
                                    <td class="text-center"><input type="checkbox" class="form-check-input"></td>

                                    {{-- Invoice Info --}}
                                    <td>
                                        <div class="fw-bold" style="color:#00cfdd; font-size:0.85rem;">
                                            {{ $inv->invoice_number }}
                                        </div>
                                        <small class="text-muted d-block" style="font-size:10px;">
                                            GD: {{ $inv->gd_number }} · SO: {{ $inv->so_number }}
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}
                                        </small>
                                    </td>

                                    {{-- Bill To --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light me-2" style="color:#00cfdd;">
                                                <i class="bi bi-person-badge"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium small">{{ $inv->customer_name }}</div>
                                                @if($inv->billing_address)
                                                    <small class="text-muted" style="font-size:10px;">
                                                        {{ Str::limit($inv->billing_address, 35) }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Total Amount --}}
                                    <td>
                                        <div class="fw-bold small">
                                            Rp {{ number_format($inv->total_amount, 0, ',', '.') }}
                                        </div>
                                        @if($inv->outstanding > 0)
                                            <small class="text-danger" style="font-size:10px;">
                                                AR: Rp {{ number_format($inv->outstanding, 0, ',', '.') }}
                                            </small>
                                        @endif
                                        <small class="text-muted d-block" style="font-size:10px;">Incl. VAT 11%</small>
                                    </td>

                                    {{-- Due Date --}}
                                    <td>
                                        <small class="fw-bold {{ $inv->is_overdue ? 'text-danger' : 'text-muted' }}">
                                            <i class="bi bi-exclamation-circle me-1"></i>
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
                                                'DRAFT'        => ['bg-secondary-subtle text-secondary border', 'DRAFT'],
                                                'ISSUED'       => ['bg-invoice-subtle border', 'ISSUED'],
                                                'PARTIAL_PAID' => ['bg-info-subtle text-info border', 'PARTIAL PAID'],
                                                'PAID'         => ['bg-success-subtle text-success border', 'PAID'],
                                                'OVERDUE'      => ['bg-unpaid-subtle border', 'OVERDUE'],
                                                'CANCELLED'    => ['bg-danger-subtle text-danger border', 'CANCELLED'],
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
                                            <button class="btn btn-sm btn-light border-0" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnDetailSI" href="#" data-id="{{ $inv->id }}">
                                                        <i class="bi bi-eye text-info me-2"></i> View Invoice
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2" href="#">
                                                        <i class="bi bi-envelope text-secondary me-2"></i> Send via Email
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2" href="{{ route('sales.invoice.print', $inv->id) }}" target="_blank">
                                                        <i class="bi bi-printer text-dark me-2"></i> Print Invoice
                                                    </a>
                                                </li>
                                                @if(in_array($inv->status, ['ISSUED', 'PARTIAL_PAID', 'OVERDUE']))
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-success btnPaySI" href="#"
                                                        data-id="{{ $inv->id }}"
                                                        data-si="{{ $inv->invoice_number }}"
                                                        data-outstanding="{{ $inv->outstanding }}">
                                                        <i class="bi bi-cash-stack me-2"></i> Record Payment
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-receipt fs-4 d-block mb-2"></i>
                                        Belum ada Sales Invoice.
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalCreateSI">Generate sekarang</a>
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
{{-- MODAL: GENERATE SALES INVOICE --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCreateSI" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius: 12px;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Generate Sales Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <form id="formCreateSI">
                    @csrf

                    {{-- Transaction Information --}}
                    <div class="card border shadow-none mb-4" style="background-color: #f8f9fa;">
                        <div class="card-header bg-white py-2 fw-bold small border-bottom">Transaction Information</div>
                        <div class="card-body">
                            <div class="row g-3">

                                {{-- Pilih GD (sumber 3-way match) --}}
                                <div class="col-md-6">
                                    <label class="small fw-bold">
                                        Goods Delivery Reference <span class="text-danger">*</span>
                                        <span class="badge bg-info-subtle text-info ms-1" style="font-size:9px;">3-Way Match</span>
                                    </label>
                                    <select name="goods_delivery_id" id="selectGD" class="form-select form-select-sm shadow-none" style="border-color:#00cfdd;" required>
                                        <option value="">-- Pilih Goods Delivery --</option>
                                        @forelse($readyGDs as $gd)
                                            <option value="{{ $gd->id }}"
                                                data-customer="{{ $gd->customer_name }}"
                                                data-customer-id="{{ $gd->customer_id }}"
                                                data-address="{{ $gd->customer_address ?? $gd->delivery_address }}">
                                                [{{ $gd->gd_status }}] {{ $gd->gd_number }} — {{ $gd->customer_name }}
                                                (SO: {{ $gd->so_number }},
                                                Tiba: {{ $gd->actual_arrival ? \Carbon\Carbon::parse($gd->actual_arrival)->format('d M Y') : 'N/A' }})
                                            </option>
                                        @empty
                                            <option value="" disabled>
                                                — Tidak ada GD siap invoice. Pastikan GD sudah berstatus DELIVERED —
                                            </option>
                                        @endforelse
                                    </select>
                                    <small class="text-muted">
                                        GD berstatus <strong>DELIVERED</strong> atau <strong>PARTIAL_DELIVERED</strong> yang belum punya invoice aktif.
                                        @if($readyGDs->isEmpty())
                                            <span class="text-danger fw-bold">⚠ Tidak ada GD tersedia — cek apakah GD sudah dikonfirmasi DELIVERED.</span>
                                        @else
                                            <span class="text-success">✓ {{ $readyGDs->count() }} GD tersedia.</span>
                                        @endif
                                    </small>
                                </div>

                                {{-- Customer (auto-fill) --}}
                                <div class="col-md-6">
                                    <label class="small fw-bold">Customer / Outlet</label>
                                    <input type="text" id="displayCustomer" class="form-control form-control-sm bg-light shadow-none" readonly placeholder="Otomatis dari GD">
                                    <input type="hidden" name="customer_id" id="hiddenCustomerId">
                                </div>

                                {{-- Billing Address --}}
                                <div class="col-md-6">
                                    <label class="small fw-bold">Billing Address</label>
                                    <input type="text" name="billing_address" id="inputBillingAddress" class="form-control form-control-sm shadow-none" placeholder="Alamat penagihan">
                                </div>

                                {{-- Currency --}}
                                <div class="col-md-2">
                                    <label class="small fw-bold">Currency</label>
                                    <select name="currency" class="form-select form-select-sm shadow-none">
                                        <option value="IDR">Rupiah (IDR)</option>
                                    </select>
                                </div>

                                {{-- Invoice Date --}}
                                <div class="col-md-2">
                                    <label class="small fw-bold">Invoice Date <span class="text-danger">*</span></label>
                                    <input type="date" name="invoice_date" id="inputInvoiceDate"
                                        class="form-control form-control-sm shadow-none" value="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- Due Date --}}
                                <div class="col-md-2">
                                    <label class="small fw-bold">Due Date <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="dueDays" class="form-control shadow-none" placeholder="Hari" value="30" min="0">
                                        <input type="date" name="due_date" id="inputDueDate" class="form-control shadow-none"
                                            value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                    </div>
                                </div>

                                {{-- VAT % --}}
                                <div class="col-md-2">
                                    <label class="small fw-bold">VAT %</label>
                                    <input type="number" name="vat_percent" id="inputVat"
                                        class="form-control form-control-sm shadow-none" value="11" min="0" max="100">
                                </div>

                                {{-- Discount --}}
                                <div class="col-md-2">
                                    <label class="small fw-bold">Discount (Rp)</label>
                                    <input type="number" name="discount" id="inputDiscount"
                                        class="form-control form-control-sm shadow-none" value="0" min="0">
                                </div>

                                {{-- VAT Invoice Number --}}
                                <div class="col-md-4">
                                    <label class="small fw-bold">VAT Invoice Number</label>
                                    <input type="text" name="vat_invoice_num" class="form-control form-control-sm shadow-none" placeholder="e.g. 01.00.25...">
                                </div>

                                {{-- Notes --}}
                                <div class="col-md-8">
                                    <label class="small fw-bold">Notes</label>
                                    <input type="text" name="notes" class="form-control form-control-sm shadow-none" placeholder="Catatan tambahan">
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- 3-Way Match Info Banner --}}
                    <div id="threewayAlertSI" class="alert alert-warning d-none py-2 px-3 mb-3" style="font-size:12px;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Perhatian:</strong> Beberapa item memiliki <strong>perbedaan harga</strong> antara SO dan GD.
                        Qty invoice tidak boleh melebihi qty yang sudah dikirim (3-way match).
                    </div>

                    <div id="loadingItemsSI" class="text-center py-3 d-none">
                        <div class="spinner-border spinner-border-sm" style="color:#00cfdd;"></div>
                        <span class="ms-2 small">Memuat item GD dan validasi 3-way match...</span>
                    </div>

                    {{-- Product Sales Invoice Detail --}}
                    <div class="card border shadow-none mb-4">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center border-bottom">
                            <span class="fw-bold small">Product Sales Invoice Detail</span>
                            <span class="badge bg-warning text-dark small" style="font-size:10px;">
                                ⚠ Qty invoice tidak boleh melebihi qty yang dikirim (3-way match)
                            </span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="min-width: 900px;">
                                <thead class="bg-light small">
                                    <tr>
                                        <th class="ps-3">Product Name</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Qty SO</th>
                                        <th class="text-center">Qty Dikirim (GD)</th>
                                        <th class="text-center">Qty Invoice <span class="text-danger">*</span></th>
                                        <th class="text-end">Harga SO</th>
                                        <th class="text-end">Harga Invoice</th>
                                        <th class="text-end pe-3">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="siItemContainer">
                                    <tr class="text-muted text-center">
                                        <td colspan="8" class="py-4">
                                            <i class="bi bi-arrow-up-circle me-1"></i>
                                            Pilih GD di atas untuk menampilkan item
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
                                <div class="card-header bg-white py-2 fw-bold small border-bottom">Additional Information</div>
                                <div class="card-body">
                                    <textarea name="additional_info" class="form-control shadow-none mb-3" rows="4"
                                        placeholder="Syarat pembayaran, catatan khusus, dll..."></textarea>
                                    <label class="small fw-bold">Foot Note</label>
                                    <textarea name="footnote" class="form-control shadow-none" rows="2"
                                        placeholder="Catatan kaki invoice..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card border shadow-none p-3" style="background-color: #fcfcfc;">
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-5 small text-muted">Subtotal</div>
                                    <div class="col-7"><input type="text" id="dispSubtotal" class="form-control form-control-sm text-end bg-light" readonly value="0"></div>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-5 small text-muted">Discount</div>
                                    <div class="col-7"><input type="text" id="dispDiscount" class="form-control form-control-sm text-end bg-light" readonly value="0"></div>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-5 small text-muted">DPP</div>
                                    <div class="col-7"><input type="text" id="dispDpp" class="form-control form-control-sm text-end bg-light" readonly value="0"></div>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-5 small text-muted">PPN (<span id="dispVatPct">11</span>%)</div>
                                    <div class="col-7"><input type="text" id="dispVat" class="form-control form-control-sm text-end bg-light" readonly value="0"></div>
                                </div>
                                <div class="row g-2 align-items-center border-top pt-2 mt-1">
                                    <div class="col-5 small fw-bold">Invoice Total</div>
                                    <div class="col-7"><input type="text" id="dispTotal" class="form-control form-control-sm text-end fw-bold bg-light" readonly value="0" style="color:#00cfdd;"></div>
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
                <button type="button" class="btn btn-sm btn-info px-4 text-white" id="btnSaveSI"
                    style="background-color:#00cfdd; border-color:#00cfdd;">
                    <i class="bi bi-save me-1"></i> Generate Invoice
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══ MODAL: DETAIL SI ═══ --}}
<div class="modal fade" id="modalDetailSI" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailSITitle">Detail Sales Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailSIBody">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:#00cfdd;"></div></div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ MODAL: RECORD PAYMENT ═══ --}}
<div class="modal fade" id="modalPaySI" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formPaySI">
                    <div class="mb-3">
                        <label class="small fw-bold">Invoice</label>
                        <input type="text" id="paySiNumber" class="form-control form-control-sm bg-light" readonly>
                        <input type="hidden" id="paySiId">
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
                <button type="button" class="btn btn-sm btn-success px-4" id="btnConfirmPaySI">
                    <i class="bi bi-cash-stack me-1"></i> Konfirmasi
                </button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
$(document).ready(function () {

    // ─── DataTable ─────────────────────────────────────────────
    if ($.fn.DataTable.isDataTable('#siTable')) $('#siTable').DataTable().destroy();
    $('#siTable').DataTable({
        responsive: true, autoWidth: false,
        dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
        language: { search: "_INPUT_", searchPlaceholder: "Search Invoices...", lengthMenu: "_MENU_" },
        columnDefs: [{ targets: [0, 6], orderable: false }]
    });
    $('#checkAllSI').click(function () { $('.form-check-input').prop('checked', this.checked); });

    // ─── Due date auto-calc ────────────────────────────────────
    $('#dueDays').on('input', function () {
        const days = parseInt($(this).val()) || 0;
        const base = new Date($('#inputInvoiceDate').val() || new Date());
        base.setDate(base.getDate() + days);
        $('#inputDueDate').val(base.toISOString().split('T')[0]);
    });

    $('#inputVat, #inputDiscount').on('input', recalcTotals);

    // ─── Select GD → load items via AJAX ─────────────────────
    $('#selectGD').on('change', function () {
        const gdId = $(this).val();
        const opt  = $(this).find('option:selected');

        $('#displayCustomer').val('');
        $('#hiddenCustomerId').val('');
        $('#inputBillingAddress').val('');
        $('#siItemContainer').html('<tr class="text-muted text-center"><td colspan="8" class="py-4">Pilih GD di atas untuk menampilkan item</td></tr>');
        $('#threewayAlertSI').addClass('d-none');
        recalcTotals();

        if (!gdId) return;

        $('#displayCustomer').val(opt.data('customer'));
        $('#hiddenCustomerId').val(opt.data('customer-id'));
        $('#inputBillingAddress').val(opt.data('address') || '');
        $('#loadingItemsSI').removeClass('d-none');

        $.ajax({
            url: '/scm/sales-invoice/gd-details/' + gdId,
            method: 'GET',
            success: function (res) {
                $('#loadingItemsSI').addClass('d-none');

                if (res.status !== 'success') {
                    Swal.fire('Error', res.message, 'error');
                    return;
                }

                if (res.items.some(i => i.has_price_diff)) {
                    $('#threewayAlertSI').removeClass('d-none');
                }

                let html = '';
                res.items.forEach(function (item, idx) {
                    const rowClass     = item.has_price_diff ? 'price-diff-warning' : '';
                    const priceDiffBadge = item.has_price_diff
                        ? `<span class="badge bg-warning text-dark ms-1" style="font-size:9px;">
                             Δ ${item.price_diff > 0 ? '+' : ''}${item.price_diff.toFixed(2)}
                           </span>` : '';

                    html += `
                    <tr class="${rowClass}">
                        <input type="hidden" name="items[${idx}][gdd_id]" value="${item.gdd_id}">
                        <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${idx}][unit_id]" value="${item.unit_id}">
                        <input type="hidden" name="items[${idx}][qty_delivered]" value="${item.qty_delivered}">
                        <td class="ps-3">
                            <div class="fw-medium small">${item.nama_bahan}</div>
                            ${priceDiffBadge}
                        </td>
                        <td class="text-center small">${item.nama_unit || '-'}</td>
                        <td class="text-center small">${item.so_qty}</td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success fw-bold">${item.qty_delivered}</span>
                            ${item.qty_already_invoiced > 0
                                ? `<br><small class="text-danger" style="font-size:10px;">sudah invoice: ${item.qty_already_invoiced}</small>`
                                : ''}
                        </td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center item-qty-invoice"
                                name="items[${idx}][qty_invoiced]"
                                data-max="${item.qty_max_invoice}"
                                value="${item.qty_max_invoice}"
                                min="0" max="${item.qty_max_invoice}" step="0.01"
                                style="width:80px; margin:auto;">
                            <small class="text-muted" style="font-size:9px;">maks: ${item.qty_max_invoice}</small>
                        </td>
                        <td class="text-end small text-muted">Rp ${numberFmt(item.so_price)}</td>
                        <td class="text-end">
                            <input type="number" class="form-control form-control-sm text-end item-price"
                                name="items[${idx}][price]"
                                value="${item.gd_price}" min="0" step="0.01"
                                style="width:110px; margin-left:auto;">
                        </td>
                        <td class="text-end pe-3 small fw-bold item-subtotal">
                            Rp ${numberFmt(item.qty_max_invoice * item.gd_price)}
                        </td>
                    </tr>`;
                });

                $('#siItemContainer').html(html);
                bindItemInputs();
                recalcTotals();
            },
            error: function (xhr) {
                $('#loadingItemsSI').addClass('d-none');
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Gagal memuat item GD.', 'error');
            }
        });
    });

    // ─── Bind qty/price inputs ────────────────────────────────
    function bindItemInputs() {
        $(document).on('input', '.item-qty-invoice', function () {
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

        $(document).on('input', '.item-price', function () {
            updateRowSubtotal($(this).closest('tr'));
            recalcTotals();
        });
    }

    function updateRowSubtotal($row) {
        const qty   = parseFloat($row.find('.item-qty-invoice').val()) || 0;
        const price = parseFloat($row.find('.item-price').val()) || 0;
        $row.find('.item-subtotal').text('Rp ' + numberFmt(qty * price));
    }

    function recalcTotals() {
        let subtotal = 0;
        $('#siItemContainer tr').each(function () {
            subtotal += (parseFloat($(this).find('.item-qty-invoice').val()) || 0)
                      * (parseFloat($(this).find('.item-price').val()) || 0);
        });
        const discount = parseFloat($('#inputDiscount').val()) || 0;
        const vatPct   = parseFloat($('#inputVat').val()) || 0;
        const dpp      = subtotal - discount;
        const vat      = Math.round(dpp * vatPct / 100);
        const total    = dpp + vat;

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

    // ─── Save SI ───────────────────────────────────────────────
    $('#btnSaveSI').on('click', function () {
        if (!$('#selectGD').val()) {
            Swal.fire('Perhatian', 'Pilih Goods Delivery terlebih dahulu.', 'warning');
            return;
        }

        // Client-side 3-way match check
        let hasViolation = false;
        $('.item-qty-invoice').each(function () {
            if ((parseFloat($(this).val()) || 0) > (parseFloat($(this).data('max')) || 0)) {
                hasViolation = true;
                return false;
            }
        });
        if (hasViolation) {
            Swal.fire('3-Way Match Gagal',
                'Satu atau lebih item melebihi qty yang sudah dikirim. Koreksi terlebih dahulu.', 'error');
            return;
        }

        const formData = new FormData(document.getElementById('formCreateSI'));
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Generating...');

        $.ajax({
            url: '/scm/sales-invoice',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $('#btnSaveSI').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Generate Invoice');
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Invoice Diterbitkan!',
                        html: `<b>${res.si_number}</b> berhasil diterbitkan ke outlet.<br>
                               Total: <b>Rp ${Math.round(res.total).toLocaleString('id-ID')}</b>`,
                        confirmButtonColor: '#00cfdd',
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function (xhr) {
                $('#btnSaveSI').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Generate Invoice');
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Terjadi kesalahan.', 'error');
            }
        });
    });

    // ─── Record Payment ────────────────────────────────────────
    $(document).on('click', '.btnPaySI', function () {
        $('#paySiId').val($(this).data('id'));
        $('#paySiNumber').val($(this).data('si'));
        const outstanding = parseFloat($(this).data('outstanding')) || 0;
        $('#payOutstanding').val('Rp ' + numberFmt(outstanding));
        $('#payAmount').val(outstanding).attr('max', outstanding);
        $('#modalPaySI').modal('show');
    });

    $('#btnConfirmPaySI').on('click', function () {
        const siId = $('#paySiId').val();
        $.ajax({
            url: `/scm/sales-invoice/${siId}/pay`,
            method: 'POST',
            data: {
                payment_amount: $('#payAmount').val(),
                payment_date:   $('#payDate').val(),
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: res => {
                if (res.status === 'success') {
                    $('#modalPaySI').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Pembayaran Dicatat!', text: res.message, confirmButtonColor: '#00cfdd' })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: xhr => Swal.fire('Error', xhr.responseJSON?.message ?? 'Server error.', 'error')
        });
    });

    // ─── View Detail SI ────────────────────────────────────────
    $(document).on('click', '.btnDetailSI', function (e) {
        e.preventDefault();
        const siId = $(this).data('id');
        $('#detailSIBody').html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm" style="color:#00cfdd;"></div></div>');
        $('#modalDetailSI').modal('show');

        $.ajax({
            url: `/scm/sales-invoice/${siId}`,
            method: 'GET',
            success: function (res) {
                if (res.status !== 'success') {
                    $('#detailSIBody').html('<p class="text-danger">Gagal memuat detail.</p>');
                    return;
                }
                const si = res.si;
                const details = res.details;
                $('#detailSITitle').text(`Detail SI: ${si.invoice_number}`);

                let rows = '';
                details.forEach(d => {
                    rows += `<tr>
                        <td>${d.nama_bahan}</td><td>${d.nama_unit || '-'}</td>
                        <td class="text-center">${d.qty_delivered}</td>
                        <td class="text-center fw-bold" style="color:#00cfdd;">${d.qty_invoiced}</td>
                        <td class="text-end">Rp ${Math.round(d.price).toLocaleString('id-ID')}</td>
                        <td class="text-end fw-bold">Rp ${Math.round(d.subtotal).toLocaleString('id-ID')}</td>
                    </tr>`;
                });

                $('#detailSIBody').html(`
                    <div class="row g-2 mb-3">
                        <div class="col-6"><small class="text-muted">Invoice Number</small>
                            <div class="fw-bold" style="color:#00cfdd">${si.invoice_number}</div></div>
                        <div class="col-6"><small class="text-muted">Customer</small><div>${si.customer_name}</div></div>
                        <div class="col-6"><small class="text-muted">GD / SO</small><div>${si.gd_number} / ${si.so_number}</div></div>
                        <div class="col-3"><small class="text-muted">Invoice Date</small><div>${si.invoice_date}</div></div>
                        <div class="col-3"><small class="text-muted">Due Date</small><div>${si.due_date}</div></div>
                        <div class="col-3"><small class="text-muted">Status</small>
                            <div><span class="badge bg-info">${si.status}</span></div></div>
                        <div class="col-3"><small class="text-muted">AR Outstanding</small>
                            <div class="fw-bold text-danger">Rp ${Math.round(si.total_amount - si.paid_amount).toLocaleString('id-ID')}</div></div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr><th>Bahan</th><th>Satuan</th>
                                    <th class="text-center">Qty GD</th><th class="text-center">Qty Invoice</th>
                                    <th class="text-end">Harga</th><th class="text-end">Subtotal</th></tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <small class="text-muted">DPP: Rp ${Math.round(si.dpp).toLocaleString('id-ID')} &nbsp;|&nbsp;
                        PPN: Rp ${Math.round(si.vat_amount).toLocaleString('id-ID')} &nbsp;|&nbsp;</small>
                        <strong>Total: Rp ${Math.round(si.total_amount).toLocaleString('id-ID')}</strong>
                    </div>
                `);
            },
            error: () => $('#detailSIBody').html('<p class="text-danger">Gagal memuat detail SI.</p>')
        });
    });

});
</script>
@endpush

@include('Temp.Investor.footer')
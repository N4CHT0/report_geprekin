{{-- resources/views/purchasing/viewSimplePurchase.blade.php --}}
@include('Temp.Investor.header')

<style>
    /* STYLE UNTUK PALET WARNA KALEM & LEMBUT (SNEAT DASHBOARD) */
    :root {
        --bg: #f5f5f9;
        --card: #ffffff;
        --text: #233446;
        --muted: #a1acb8;
        --border: #d9dee3;
        --shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        --radius: 12px;
        --primary: #696cff;
        --soft: #fcfcfd;
        --success: #71dd37;
    }

    /* Styling label informasi */
    .form-label-custom {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.5px;
    }

    /* Tampilan value teks read-only agar clean */
    .view-value-text {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2c3e50;
        padding: 6px 0;
    }

    /* Tabel konversi unit khusus read-only */
    .table-premium {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    .table-premium thead th {
        background-color: #f5f5f9 !important;
        border-bottom: 1px solid var(--border) !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #566a7f;
        padding: 12px 14px !important;
        letter-spacing: 0.5px;
        text-align: center;
    }

    .table-premium tbody td {
        padding: 14px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f0f2f4 !important;
        color: #697a8d;
    }

    .table-premium tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.02) !important;
    }

    /* Custom Soft Badges */
    .bg-unit-subtle {
        background-color: #f5f5f9 !important;
        color: #697a8d !important;
        border: 1px solid var(--border) !important;
    }

    .bg-status-subtle {
        background-color: #e8fadf !important;
        color: #71dd37 !important;
        border: 1px solid #d4f5c3 !important;
    }

    /* TOMBOL STYLING */
    .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 16px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .btn-primary {
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        color: #fff !important;
    }
    .btn-primary:hover {
        background: #5f61e6 !important;
        border-color: #5f61e6 !important;
    }

    .icon-shape {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
</style>

<style media="print">
    /* CSS Khusus Cetak Printer Kertas */
    .appbar, .btn, .btn-group-gap, .card-footer-action { display: none !important; }
    .card { box-shadow: none !important; border: 0 !important; }
    body { background: #fff !important; color: #000 !important; }
</style>

<main class="app-main">
    <div class="container-fluid py-4">
        
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="me-auto">
                        <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Detail Simple Purchase</h5>
                        <span class="text-muted small">Nomor Dokumen SCM: <strong style="color: var(--primary);">{{ $purchase->purchase_num }}</strong></span>
                    </div>
                    <div class="d-flex gap-2 btn-group-gap">
                        <button type="button" onclick="window.print()" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1" style="height: 36px; background: #fff;">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                        <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary d-inline-flex align-items-center gap-1" style="height: 36px;">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <form id="purchaseForm">
            {{-- SECTION 1: TRANSACTION INFORMATION --}}
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="p-2 bg-light rounded text-primary" style="color: var(--primary) !important;">
                            <i class="bi bi-info-circle fs-5"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-0">Transaction Information</h6>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 border-end border-light">
                            <div class="mb-3">
                                <label class="form-label-custom mb-1">Supplier</label>
                                <div class="view-value-text d-flex align-items-center gap-2">
                                    <div class="icon-shape bg-light text-secondary" style="width: 28px; height: 28px;"><i class="bi bi-truck"></i></div>
                                    <span class="fw-semibold text-dark">{{ $purchase->supplier_name }}</span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label-custom mb-1">Branch</label>
                                    <div class="view-value-text d-flex align-items-center gap-2">
                                        <div class="icon-shape bg-light text-secondary" style="width: 28px; height: 28px;"><i class="bi bi-geo-alt"></i></div>
                                        <span>{{ $purchase->branch_name }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label-custom mb-1">Location</label>
                                    <div class="view-value-text d-flex align-items-center gap-2">
                                        <div class="icon-shape bg-light text-secondary" style="width: 28px; height: 28px;"><i class="bi bi-compass"></i></div>
                                        <span>{{ $purchase->location_name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 ps-md-4">
                            <div class="mb-3">
                                <label class="form-label-custom mb-1">Simple Purchase Date</label>
                                <div class="view-value-text d-flex align-items-center gap-2">
                                    <div class="icon-shape bg-light text-secondary" style="width: 28px; height: 28px;"><i class="bi bi-calendar-check"></i></div>
                                    <span>{{ date('d F Y', strtotime($purchase->purchase_date)) }}</span>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label-custom mb-1">Payment Method</label>
                                    <div class="view-value-text d-flex align-items-center gap-2">
                                        <div class="icon-shape bg-light text-secondary" style="width: 28px; height: 28px;"><i class="bi bi-credit-card"></i></div>
                                        <span>{{ $purchase->payment_name ?? 'CASH' }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label-custom mb-1">Status</label>
                                    <div class="pt-1">
                                        <span class="badge bg-status-subtle border px-3 py-1.5 fw-bold" style="font-size: 0.75rem; border-radius: 6px;">
                                            {{ $purchase->status_name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: PURCHASE DETAIL (ITEMS) --}}
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-light rounded text-primary" style="color: var(--primary) !important;">
                            <i class="bi bi-box-seam fs-5"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark">Purchase Detail</h6>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-premium mb-0">
                        <thead>
                            <tr>
                                <th class="text-start ps-4">Product Name</th>
                                <th width="140">Unit</th>
                                <th width="120">Qty</th>
                                <th width="180" class="text-end">Price</th>
                                <th width="200" class="text-end pe-4">Total</th>
                            </tr>
                        </thead>
                        <tbody id="itemBody" class="text-dark">
                            @foreach($items as $item)
                            <tr>
                                <td class="text-start ps-4 fw-semibold text-dark">{{ $item->product_name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-unit-subtle border px-2.5 py-1.5 fw-bold text-uppercase" style="font-size: 0.7rem; border-radius: 6px;">
                                        {{ $item->uom_name }}
                                    </span>
                                </td>
                                <td class="text-center fw-medium text-secondary small">{{ number_format($item->qty, 0, ',', '.') }}</td>
                                <td class="text-end text-secondary small fw-medium">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-end pe-4 fw-bold text-dark" style="font-size: 0.9rem;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SECTION 3: CASH PURCHASE COST --}}
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-light rounded text-primary" style="color: var(--primary) !important;">
                            <i class="bi bi-receipt-cutoff fs-5"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark">Cash Purchase Cost</h6>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-premium mb-0">
                        <thead>
                            <tr>
                                <th class="text-start ps-4">Account</th>
                                <th width="220" class="text-end">Amount</th>
                                <th width="400" class="text-start ps-4">Notes</th>
                            </tr>
                        </thead>
                        <tbody id="costBody" class="text-dark">
                            @forelse($costs as $cost)
                            <tr>
                                <td class="text-start ps-4 fw-medium"><i class="bi bi-hash small text-muted me-1"></i>{{ $cost->coa_no }} - {{ $cost->description }}</td>
                                <td class="text-end fw-semibold text-dark">Rp {{ number_format($cost->amount, 0, ',', '.') }}</td>
                                <td class="text-start ps-4 text-secondary small">{{ $cost->notes ?: '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted small style-none"><i class="bi bi-patch-check text-success me-1"></i> No additional costs applied to this purchase.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER SUMMARY --}}
            <div class="row g-4">
                <div class="col-md-7 col-lg-8">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <label class="form-label-custom mb-2"><i class="bi bi-chat-left-text me-1"></i> Additional Information</label>
                            <div class="p-3 bg-light rounded-3 text-secondary small style-none" style="min-height: 90px; background-color: #f8f9fa !important; line-height: 1.6;">
                                {{ $purchase->additional_info ?: 'Tidak ada keterangan tambahan.' }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-5 col-lg-4">
                    <div class="p-4 bg-white rounded-4 shadow-sm border border-light" style="border-radius: 15px;">
                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                            <span class="text-muted small fw-semibold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Cost Total</span>
                            <span class="fw-semibold text-danger small">Rp {{ number_format($purchase->cost_total, 0, ',', '.') }}</span>
                        </div>
                        <hr class="text-muted opacity-25 my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-dark fw-bold small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Purchase Total</span>
                            <h4 class="fw-bold mb-0" style="color: var(--primary); font-size: 1.2rem;">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

@include('Temp.Investor.footer')
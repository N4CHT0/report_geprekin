{{-- resources/views/purchasing/viewTransfer.blade.php --}}
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

    /* Tabel detail barang khusus read-only */
    #tableTransferDetail {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #tableTransferDetail thead th {
        background-color: #f5f5f9 !important;
        border-bottom: 1px solid var(--border) !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #566a7f;
        padding: 12px 14px !important;
        letter-spacing: 0.5px;
    }

    #tableTransferDetail tbody td {
        padding: 14px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f0f2f4 !important;
        color: #697a8d;
    }

    #tableTransferDetail tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.02) !important;
    }

    /* Custom Soft Badges */
    .bg-unit-subtle {
        background-color: #e7e7ff !important;
        color: #696cff !important;
        border: 1px solid #e1e1ff !important;
    }

    .bg-status-subtle {
        background-color: #e2f8f5 !important;
        color: #20c997 !important;
        border: 1px solid #bfeadd !important;
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
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Detail Mutasi Transfer Stok</h5>
                            <span class="text-muted small">Nomor Dokumen SCM: <strong style="color: var(--primary);">{{ $transfer->transfer_num }}</strong></span>
                        </div>
                        <div class="d-flex gap-2 btn-group-gap">
                            <a href="{{ route('simple-transfer.index') }}" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1" style="height: 36px; background: #fff; color: #8592a3;">
                                <i class="bi bi-arrow-left-short fs-5"></i> Kembali
                            </a>
                            <button onclick="window.print()" class="btn btn-sm btn-primary px-3 shadow-sm d-inline-flex align-items-center gap-1" style="height: 36px;">
                                <i class="bi bi-printer me-1"></i> Print Dokumen
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                {{-- SECTION 1: INFO UTAMA PROFILE --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-2 mb-4">
                                <div class="p-2 bg-light rounded text-primary" style="color: var(--primary) !important;">
                                    <i class="bi bi-info-circle fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-0">Transaction Information</h6>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase form-label-custom mb-1">Origin Branch</label>
                                    <div class="view-value-text d-flex align-items-center gap-2">
                                        <div class="icon-shape bg-light text-secondary" style="width: 28px; height: 28px;"><i class="bi bi-geo-alt"></i></div>
                                        <span>{{ $transfer->origin_location_name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase form-label-custom mb-1">Destination Branch</label>
                                    <div class="view-value-text d-flex align-items-center gap-2">
                                        <div class="icon-shape bg-light text-primary" style="color: var(--primary) !important; width: 28px; height: 28px;"><i class="bi bi-building-arrow-right"></i></div>
                                        <span>{{ $transfer->destination_location_name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase form-label-custom mb-1">Transfer Date</label>
                                    <div class="view-value-text d-flex align-items-center gap-2">
                                        <div class="icon-shape bg-light text-secondary" style="width: 28px; height: 28px;"><i class="bi bi-calendar-event"></i></div>
                                        <span>{{ date('d-m-Y', strtotime($transfer->transfer_date)) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-uppercase form-label-custom mb-1">Status Dokumen</label>
                                    <div class="view-value-text pt-1.5">
                                        <span class="badge bg-status-subtle border px-3 py-1.5 fw-bold" style="font-size: 0.75rem; border-radius: 6px;">
                                            {{ $transfer->status_name }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: TABEL KATALOG PRODUK MUTASI --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="p-2 bg-light rounded text-primary" style="color: var(--primary) !important;">
                                    <i class="bi bi-box-seam fs-5"></i>
                                </div>
                                <h6 class="mb-0 fw-bold text-dark">Simple Transfer Details</h6>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0" id="tableTransferDetail">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="80">No</th>
                                            <th>Product Name</th>
                                            <th class="text-center" width="140">Unit</th>
                                            <th class="text-end" width="180">Stock Qty</th>
                                            <th class="text-end pe-4" width="180">Qty Transfer</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-dark">
                                        @foreach($transfer->items as $index => $item)
                                        <tr>
                                            <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                            <td class="fw-semibold text-dark">{{ $item->product_name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-unit-subtle border px-2.5 py-1.5 fw-bold text-uppercase" style="font-size: 0.7rem; border-radius: 6px;">
                                                    {{ $item->uom_name }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-medium text-secondary small">
                                                {{ number_format($item->stock_qty, 2, ',', '.') }}
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-primary" style="font-size: 0.9rem;">
                                                {{ number_format($item->qty, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end align-items-center gap-2 card-footer-action">
                            <span class="text-muted small fw-medium">Total Jenis Barang (Items):</span>
                            <span class="badge bg-light text-dark border fw-bold fs-6 px-2.5 py-1" style="border-radius: 6px;">{{ count($transfer->items) }}</span>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: ADDITIONAL NOTES INFORMATION --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body p-4">
                            <label class="form-label form-label-custom mb-2"><i class="bi bi-chat-left-text me-1"></i> Catatan Tambahan (Additional Information)</label>
                            <div class="p-3 bg-light rounded-3 text-secondary small border-0" style="min-height: 80px; background-color: #f8f9fa !important; line-height: 1.6;">
                                {!! nl2br(e($transfer->additional_info)) ?: '<span class="text-muted italic small">Tidak ada informasi tambahan yang dicantumkan.</span>' !!}
                            </div>
                            <div class="mt-4 pt-2 border-top border-light d-flex flex-wrap gap-4 text-muted" style="font-size: 11px; font-weight: 500;">
                                <span><i class="bi bi-person me-1"></i> Created By: <strong>{{ $transfer->created_by ?? '-' }}</strong></span>
                                <span><i class="bi bi-calendar-check me-1"></i> Created At: <strong>{{ $transfer->created_date ?? '-' }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@include('Temp.Investor.footer')
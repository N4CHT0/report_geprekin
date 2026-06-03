@include('Temp.Investor.header')
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Styling tetap dipertahankan sesuai template asli agar konsisten */
    #poTable_wrapper .dataTables_scroll,
    #poTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    #poTable thead th {
        padding: 15px 20px !important;
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #f1f4f8 !important;
        vertical-align: middle;
    }

    #poTable tbody td {
        padding: 1.2rem 20px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 15px 20px !important;
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 15px 20px !important;
    }

    #poTable tbody tr {
        transition: all 0.2s ease-in-out;
    }

    #poTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    .icon-shape {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        flex-shrink: 0;
    }

    .bg-success-subtle {
        background-color: #e8fadf !important;
        color: #71dd37 !important;
        border: 1px solid #d4f5c3 !important;
    }

    .bg-warning-subtle {
        background-color: #fff2e2 !important;
        color: #ffab00 !important;
        border: 1px solid #ffe5c4 !important;
    }

    .bg-info-subtle {
        background-color: #e7e7ff !important;
        color: #696cff !important;
        border: 1px solid #d9d9ff !important;
    }

    .bg-danger-subtle {
        background-color: #ffe5e5 !important;
        color: #ff3e1d !important;
        border: 1px solid #ffd1d1 !important;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Purchase Order</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Purchasing</a></li>
                                    <li class="breadcrumb-item active text-primary" aria-current="page">PO Supplier</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="mx-3">
                            <form action="#" method="GET" class="d-flex align-items-center">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted small">Period</span>
                                    <input type="date" name="start_date" class="form-control border-start-0" value="{{ request('start_date') }}">
                                    <span class="input-group-text bg-white border-start-0 border-end-0">-</span>
                                    <input type="date" name="end_date" class="form-control border-start-0 border-end-0" value="{{ request('end_date') }}">
                                    <button type="submit" class="btn btn-primary px-3"><i class="bi bi-filter"></i></button>
                                </div>
                            </form>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <!-- <button onclick="showSyncModal()" class="btn btn-outline-success"><i class="bi bi-arrow-repeat me-1"></i> Sync ESB</button> -->
                                <!-- <a href="#" ><i class="bi bi-plus-lg me-1"></i> Create PO</a> -->
                                <button type="button" class="btn btn-primary px-3 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCreatePO">
                                    <i class="bi bi-plus-lg me-1"></i> Create PO
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-cart-check me-2 text-primary"></i>Purchase Transactions
                    </h6>
                </div>


                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="poTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small fw-bolder text-secondary" style="background-color: #f8f9fa; letter-spacing: 0.05em;">
                                    <th class="py-3 text-center" style="width: 50px;"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                    <th class="py-3 px-3">PO Info</th>
                                    <th class="py-3 px-3">Supplier</th>
                                    <th class="py-3 px-3">Total Amount</th>
                                    <th class="py-3 px-3">ESB Ref</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @forelse($purchase_orders as $po)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input sub_chk" data-id="{{ $po->id }}">
                                    </td>
                                    <td class="px-3">
                                        <div class="fw-bold text-primary" style="font-size: 0.85rem;">{{ $po->po_number }}</div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i> {{ date('d M Y', strtotime($po->request_date)) }}
                                        </small>
                                    </td>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light text-secondary me-2" style="width: 25px; height: 25px; font-size: 12px;">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <span class="fw-medium small">{{ $po->supplier_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <span class="fw-bold small">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-3">
                                        <code class="small text-muted">{{ $po->request_number ?? '-' }}</code>
                                    </td>
                                    <td class="text-center">
                                        @if($po->status == 'PENDING')
                                        <span class="badge bg-warning-subtle px-3 py-2 fw-bold" style="font-size: 0.7rem; border-radius: 8px;">PENDING</span>
                                        @elseif($po->status == 'APPROVED')
                                        <span class="badge bg-success-subtle px-3 py-2 fw-bold" style="font-size: 0.7rem; border-radius: 8px;">APPROVED</span>
                                        @else
                                        <span class="badge bg-danger-subtle px-3 py-2 fw-bold" style="font-size: 0.7rem; border-radius: 8px;">{{ $po->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 shadow-none" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 12px;">
                                                <li>
                                                    <button type="button"
                                                        class="dropdown-item rounded-2"
                                                        onclick="showDetailPO({{ $po->id }})">
                                                        <i class="bi bi-eye-fill me-2 text-primary"></i> Detail
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button"
                                                        class="dropdown-item rounded-2"
                                                        onclick="openEditPO({{ $po->id }})">
                                                        <i class="bi bi-pencil-square me-2 text-warning"></i> Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider opacity-50">
                                                </li>
                                                <li>
                                                    <button type="button"
                                                        class="dropdown-item rounded-2 text-danger"
                                                        onclick="confirmDeletePO({{ $po->id }}, '{{ $po->po_number }}')">
                                                        <i class="bi bi-trash3-fill me-2"></i> Hapus
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Belum ada data transaksi pembelian.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- MODAL CREATE PO -->
                <div class="modal fade" id="modalCreatePO" tabindex="-1" aria-labelledby="modalCreatePOLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0" style="border-radius: 15px;">
                            <div class="modal-header border-bottom-0 pt-4 px-4">
                                <h5 class="modal-title fw-bold" id="modalCreatePOLabel">Create Purchase Order</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-4 pb-4">
                                <form id="formCreatePO">
                                    @csrf

                                    <div class="card border shadow-none mb-3" style="background-color: #fcfcfc;">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3" style="font-size: 0.9rem;">Branch Purchase Info</h6>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="small fw-bold">Branch *</label>
                                                    <select name="branch_id" id="selectBranch" class="form-select form-select-sm shadow-none">
                                                        <option value="">-- Pilih Branch --</option>
                                                        @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->nama_outlet }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Request Date *</label>
                                                    <input type="date" name="request_date" class="form-control form-control-sm shadow-none" value="{{ date('Y-m-d') }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Required Date *</label>
                                                    <input type="date" name="required_date" class="form-control form-control-sm shadow-none" value="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border shadow-none mb-3" style="background-color: #fcfcfc;">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3" style="font-size: 0.9rem;">Supplier</h6>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="small fw-bold">Supplier *</label>
                                                    <select name="supplier_id" id="selectSupplier" class="form-select form-select-sm shadow-none">
                                                        <option value="">Search or select supplier</option>
                                                        @foreach($suppliers as $s)
                                                        <option value="{{ $s->id }}">{{ $s->supplier_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Currency</label>
                                                    <select name="currency" class="form-select form-select-sm shadow-none">
                                                        <option value="IDR">IDR - Rupiah</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Rate</label>
                                                    <input type="number" name="rate" class="form-control form-control-sm bg-light shadow-none" value="1" readonly>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="small fw-bold">Request Number</label>
                                                    <select name="request_number" class="form-select form-select-sm shadow-none">
                                                        <option value="">Select request number</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border shadow-none mb-3">
                                        <div class="card-body p-0">
                                            <div class="p-3 d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">Product (Total <span id="totalProductCount">0</span>)</h6>
                                                <div class="d-flex gap-2">
                                                    <div class="form-check pt-1">
                                                        <input class="form-check-input" type="checkbox" id="vatAll">
                                                        <label class="form-check-label small" for="vatAll">Apply VAT to All</label>
                                                    </div>
                                                    <button type="button" id="btnBrowseProduct" class="btn btn-sm btn-outline-secondary">Browse Product</button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary"><i class="bi bi-upload"></i> Upload</button>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0" id="tableProductPO">
                                                    <thead class="bg-light text-uppercase" style="font-size: 11px;">
                                                        <tr>
                                                            <th class="ps-3 py-2">Product</th>
                                                            <th class="py-2">Product Code</th>
                                                            <th class="py-2">Unit</th>
                                                            <th class="py-2">Request Qty</th>
                                                            <th class="py-2">Stock</th>
                                                            <th class="py-2">PO Qty</th>
                                                            <th class="py-2">Last Price</th>
                                                            <th class="pe-3"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="productContainer">
                                                        <tr>
                                                            <td colspan="8" class="text-center py-4 text-muted small">
                                                                Browse Product
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="card border shadow-none p-3" style="background-color: #fcfcfc;">
                                                <h6 class="fw-bold mb-3" style="font-size: 0.9rem;">Additional Info</h6>
                                                <label class="small fw-bold">Notes</label>
                                                <textarea name="notes" class="form-control form-control-sm mb-3 shadow-none" rows="3" placeholder="Add transaction notes"></textarea>
                                                <label class="small fw-bold">Foot Note</label>
                                                <textarea name="footnote" class="form-control form-control-sm shadow-none" rows="3" placeholder="Add foot note"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-5 cost-summary">
                                            <div class="card border shadow-none p-3 h-100">
                                                <h6 class="fw-bold mb-3" style="font-size: 0.9rem;">Cost Summary</h6>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Subtotal (<span id="summaryItemCount">0</span>)</span>
                                                    <span class="fw-bold small" id="summarySubtotal">0</span>
                                                </div>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">Discount</span>
                                                    <span class="fw-bold small" id="summaryDiscount">0</span>
                                                </div>

                                                <hr class="my-2 border-dashed">

                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">DPP</span>
                                                    <span class="fw-bold small" id="summaryDPP">0</span>
                                                </div>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted small">VAT (11%)</span>
                                                    <span class="fw-bold small" id="summaryVAT">0</span>
                                                </div>

                                                <div class="d-flex justify-content-between mt-3 pt-2 border-top">
                                                    <span class="fw-bold text-primary">Total Purchase</span>
                                                    <h5 class="fw-bold mb-0 text-primary" id="summaryTotal">0</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-outline-primary btn-sm px-3">Save As Draft</button>
                                <button type="button" class="btn btn-outline-info btn-sm px-3">Save & Print</button>
                                <button type="button" class="btn btn-primary btn-sm px-4" id="btnSavePO">Save</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- === MODAL PRODUCT PICKER === -->
                <div class="modal fade" id="modalProductPicker" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-white border-bottom-0 pt-4 px-4">
                                <h5 class="modal-title fw-bold">Select Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-4">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="tableProductPicker" style="width:100%">
                                        <thead class="bg-light small text-uppercase">
                                            <tr>
                                                <th width="5%"></th>
                                                <th>Product Name</th>
                                                <th>Code</th>
                                                <th>Unit</th>
                                                <th class="text-end">Pricelist Price</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small">
                                            @foreach($products as $p)
                                            <tr style="cursor: pointer" class="product-row-picker" data-id="{{ $p->id }}">
                                                <td class="text-center">
                                                    <input type="radio" name="product_radio" class="form-check-input select-radio" value="{{ $p->id }}">
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $p->nama_bahan }}</div>
                                                </td>
                                                <td class="text-muted">{{ $p->product_code }}</td>
                                                <td><span class="badge bg-light text-dark border fw-normal">{{ $p->satuan }}</span></td>
                                                <td class="text-end fw-bold">Rp {{ number_format($p->base_price, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 px-4 pb-4">
                                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary btn-sm px-4 shadow-sm" id="btnConfirmSelect">Select Product</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================================
                    2A. MODAL DETAIL PO  — tempel setelah penutup modal Create PO
                    ============================================================ --}}
                <div class="modal fade" id="modalDetailPO" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0" style="border-radius: 15px;">
                            <div class="modal-header border-bottom-0 pt-4 px-4">
                                <div>
                                    <h5 class="modal-title fw-bold mb-0" id="detailPoNumber">Detail Purchase Order</h5>
                                    <small class="text-muted" id="detailPoDate"></small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body px-4 pb-2" id="detailPoBody">
                                <div class="text-center py-5">
                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                    <p class="small text-muted mt-2">Memuat data...</p>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 px-4 pb-4">
                                <span id="detailPoStatusBadge"></span>
                                <div class="ms-auto">
                                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- ============================================================
                    2B. MODAL EDIT PO  — tempel setelah modal Detail
                    ============================================================ --}}
                <div class="modal fade" id="modalEditPO" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content border-0" style="border-radius: 15px;">
                            <div class="modal-header border-bottom-0 pt-4 px-4">
                                <h5 class="modal-title fw-bold" id="editPoTitle">Edit Purchase Order</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body px-4 pb-4">
                                <form id="formEditPO">
                                    @csrf
                                    <input type="hidden" id="editPoId" name="po_id">

                                    <div class="card border shadow-none mb-3" style="background-color: #fcfcfc;">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3" style="font-size: 0.9rem;">Branch Purchase Info</h6>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="small fw-bold">Branch *</label>
                                                    <select name="branch_id" id="editSelectBranch" class="form-select form-select-sm shadow-none">
                                                        <option value="">-- Pilih Branch --</option>
                                                        @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}">{{ $branch->nama_outlet }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Request Date *</label>
                                                    <input type="date" name="request_date" id="editRequestDate" class="form-control form-control-sm shadow-none">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Required Date *</label>
                                                    <input type="date" name="required_date" id="editRequiredDate" class="form-control form-control-sm shadow-none">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border shadow-none mb-3" style="background-color: #fcfcfc;">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3" style="font-size: 0.9rem;">Supplier</h6>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="small fw-bold">Supplier *</label>
                                                    <select name="supplier_id" id="editSelectSupplier" class="form-select form-select-sm shadow-none">
                                                        <option value="">-- Pilih Supplier --</option>
                                                        @foreach($suppliers as $s)
                                                        <option value="{{ $s->id }}">{{ $s->supplier_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Currency</label>
                                                    <select name="currency" id="editCurrency" class="form-select form-select-sm shadow-none">
                                                        <option value="IDR">IDR - Rupiah</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="small fw-bold">Rate</label>
                                                    <input type="number" name="rate" id="editRate" class="form-control form-control-sm bg-light shadow-none" value="1" readonly>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="small fw-bold">Notes</label>
                                                    <textarea name="notes" id="editNotes" class="form-control form-control-sm shadow-none" rows="2" placeholder="Catatan..."></textarea>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="small fw-bold">Foot Note</label>
                                                    <textarea name="footnote" id="editFootnote" class="form-control form-control-sm shadow-none" rows="2" placeholder="Foot note..."></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card border shadow-none mb-3">
                                        <div class="card-body p-0">
                                            <div class="p-3 d-flex justify-content-between align-items-center">
                                                <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">
                                                    Items (<span id="editProductCount">0</span>)
                                                </h6>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="form-check pt-1">
                                                        <input class="form-check-input" type="checkbox" id="editVatAll" name="apply_vat">
                                                        <label class="form-check-label small" for="editVatAll">Apply VAT (11%)</label>
                                                    </div>
                                                    <button type="button" id="editBtnBrowse" class="btn btn-sm btn-outline-secondary">
                                                        Browse Product
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead class="bg-light text-uppercase" style="font-size: 11px;">
                                                        <tr>
                                                            <th class="ps-3 py-2">Product</th>
                                                            <th class="py-2">Code</th>
                                                            <th class="py-2">Unit</th>
                                                            <th class="py-2">PO Qty</th>
                                                            <th class="py-2">Price (Rp)</th>
                                                            <th class="py-2 text-end">Subtotal</th>
                                                            <th class="pe-3"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="editProductContainer">
                                                        <tr id="editEmptyRow">
                                                            <td colspan="7" class="text-center py-4 text-muted small">
                                                                Belum ada produk
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Cost Summary --}}
                                    <div class="d-flex justify-content-end">
                                        <div class="card border shadow-none p-3" style="min-width: 280px; background-color: #fcfcfc;">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted small">Subtotal</span>
                                                <span class="small fw-bold" id="editSummarySubtotal">Rp 0</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted small">VAT (11%)</span>
                                                <span class="small fw-bold" id="editSummaryVAT">Rp 0</span>
                                            </div>
                                            <div class="d-flex justify-content-between pt-2 border-top mt-2">
                                                <span class="fw-bold text-primary">Total</span>
                                                <span class="fw-bold text-primary" id="editSummaryTotal">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary btn-sm px-4" id="btnUpdatePO">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
    // =========================================================
    // FUNGSI GLOBAL (di luar ready — bisa dipanggil dari HTML)
    // =========================================================
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus PO ini?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
        }).then((result) => {
            if (result.isConfirmed) {
                // Logic delete
            }
        });
    }

    function showSyncModal() {
        Swal.fire({
            title: 'Sync PO dari ESB',
            html: `
                <p class="small text-muted">Ambil data PO terbaru yang diproses oleh ESB</p>
                <input type="date" id="s_date" class="swal2-input" placeholder="Start Date">
                <input type="date" id="e_date" class="swal2-input" placeholder="End Date">
            `,
            showCancelButton: true,
            confirmButtonText: 'Mulai Sync',
            preConfirm: () => {
                const s = $('#s_date').val();
                const e = $('#e_date').val();
                if (!s || !e) {
                    Swal.showValidationMessage('Periode harus diisi');
                    return false;
                }
                return {
                    s,
                    e
                };
            }
        }).then((res) => {
            if (res.isConfirmed) {
                // Trigger logic sync di sini
            }
        });
    }

    // =========================================================
    // SATU $(document).ready() — semua event binding di sini
    // FIX: sebelumnya ada 2 blok ready() terpisah, menyebabkan
    // #btnSavePO terdaftar 2x → setiap klik kirim 2 AJAX → 2 baris tersimpan
    // =========================================================
    $(document).ready(function() {

        // --- DATATABLE ---
        $('#poTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });

        $('#checkAll').on('click', function() {
            $(".sub_chk").prop('checked', $(this).prop('checked'));
        });

        // --------------------------------------------------------
        // FORM CREATE PO
        // --------------------------------------------------------
        let productIndex = 0;

        // --- 1. FUNGSI TAMBAH BARIS PRODUK ---
        function addProductRow(data = {}) {
            productIndex++;

            // Hapus baris "Search to add product" jika masih ada
            if ($('#productContainer tr').length === 1 && $('#productContainer td').attr('colspan')) {
                $('#productContainer').empty();
            }

            const rowHtml = `
        <tr class="product-row" id="row_${productIndex}">
            <td class="ps-3">
                <!-- Hidden ID: Kunci utama untuk simpan ke database -->
                <input type="hidden" name="items[${productIndex}][product_id]" value="${data.id || ''}">
                <input type="hidden" name="items[${productIndex}][unit_id]" value="${data.unit_id || ''}">
                
                <div class="fw-bold small text-dark">${data.name || 'New Product'}</div>
            </td>
            <td><small class="text-muted">${data.code || '-'}</small></td>
            <td>
                <!-- Badge Satuan: Otomatis dari is_purchase_unit -->
                <span class="badge bg-light text-dark border small px-2">${data.unit || 'Unit'}</span>
            </td>
            <td>
                <input type="number" name="items[${productIndex}][request_qty]" class="form-control form-control-sm text-center shadow-none bg-light" value="${data.request_qty || 0}" readonly style="width: 70px;">
            </td>
            <td><small class="text-muted">${data.stock || 0}</small></td>
            <td>
                <input type="number" name="items[${productIndex}][po_qty]" class="form-control form-control-sm po-qty shadow-none border-primary fw-bold" value="1" min="1" style="width: 80px;">
            </td>
            <td>
                <div class="input-group input-group-sm" style="width: 130px;">
                    <span class="input-group-text bg-light border-end-0">Rp</span>
                    <input type="number" name="items[${productIndex}][price]" class="form-control price-input shadow-none border-start-0" value="${data.price || 0}">
                </div>
            </td>
            <td class="pe-3 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
        `;

            $('#productContainer').append(rowHtml);
            updateCounters();
            calculateSummary();
        }

        // --- 2. EVENT: KLIK BROWSE (Pilih Produk dari Database) ---
        $('#btnBrowseProduct').on('click', function() {
            // Ambil data produk yang sudah di-join is_purchase_unit dari controller
            const masterProducts = @json($products);

            let options = {};
            masterProducts.forEach(p => {
                // Tampilan: Kode - Nama (Stok)
                options[p.id] = `${p.kode_bahan} - ${p.nama_bahan} (Stok: ${p.stok})`;
            });

            Swal.fire({
                title: 'Pilih Produk / Bahan Baku',
                input: 'select',
                inputOptions: options,
                inputPlaceholder: 'Cari bahan baku...',
                showCancelButton: true,
                confirmButtonText: 'Tambah ke List',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary btn-sm px-4',
                    cancelButton: 'btn btn-light btn-sm px-4'
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const selected = masterProducts.find(p => p.id == result.value);

                    addProductRow({
                        id: selected.id,
                        unit_id: selected.unit_id, // Dari tbl_bahan_unit (is_purchase_unit)
                        name: selected.nama_bahan,
                        code: selected.kode_bahan || '',
                        unit: selected.satuan || 'Pcs', // Nama satuan dari tbl_units
                        stock: selected.stok || 0,
                        price: selected.harga_beli_terakhir || selected.base_price || 0,
                        request_qty: 0
                    });
                }
            });
        });

        // --- 3. EVENT: HAPUS BARIS ---
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();

            // if ($('.product-row').length === 0) {
            //     $('#productContainer').append(`
            //     <tr>
            //         <td colspan="8" class="text-center py-4 text-muted small">
            //             <input type="text" class="form-control form-control-sm border-dashed" placeholder="Search to add product...">
            //         </td>
            //     </tr>
            // `);
            // }
            updateCounters();
            calculateSummary();
        });

        // --- 4. EVENT: HITUNG OTOMATIS ---
        $(document).on('input', '.po-qty, .price-input', function() {
            calculateSummary();
        });

        $('#vatAll').on('change', function() {
            calculateSummary();
        });

        // --- 5. FUNGSI UPDATE COUNTER & KALKULASI ---
        function updateCounters() {
            const totalRows = $('.product-row').length;
            $('#totalProductCount').text(totalRows);
            $('#summaryItemCount').text(totalRows);
        }

        function calculateSummary() {
            let subtotal = 0;

            $('.product-row').each(function() {
                const qty = parseFloat($(this).find('.po-qty').val()) || 0;
                const price = parseFloat($(this).find('.price-input').val()) || 0;
                subtotal += (qty * price);
            });

            const discount = 0;
            const dpp = subtotal - discount;

            // VAT 11%
            const isVat = $('#vatAll').is(':checked');
            const vatAmount = isVat ? (dpp * 0.11) : 0;
            const total = dpp + vatAmount;

            // Update UI Summary
            $('#summarySubtotal').text(formatRupiah(subtotal));
            $('#summaryDiscount').text(formatRupiah(discount));
            $('#summaryDPP').text(formatRupiah(dpp));
            $('#summaryVAT').text(formatRupiah(vatAmount));
            $('#summaryTotal').text(formatRupiah(total));
        }

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        // --- 6. EVENT: SIMPAN DATA ---
        // FIX: Gunakan .one() bukan .on() tidak diperlukan karena sudah 1 ready block.
        // Guard tambahan: cek prop('disabled') agar double-click tidak kirim 2x request.
        $('#btnSavePO').on('click', function(e) {
            e.preventDefault();

            // Double-click guard
            if ($(this).prop('disabled')) return;

            if ($('.product-row').length === 0) {
                Swal.fire("Peringatan", "Tambahkan minimal 1 produk sebelum menyimpan!", "warning");
                return;
            }

            const $btn = $(this);
            const formData = $('#formCreatePO').serialize();

            $.ajax({
                url: "{{ route('purchase-order.store') }}",
                method: "POST",
                data: formData,
                beforeSend: function() {
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
                },
                success: function(response) {
                    Swal.fire("Berhasil!", response.message, "success").then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).text('Save');
                    const err = xhr.responseJSON;
                    Swal.fire("Error!", err?.message || "Gagal menyimpan data", "error");
                }
            });
        });

        // --- 7. EVENT: FILTER SUPPLIER BY BRANCH ---
        $('#selectBranch').on('change', function() {
            const outletId = $(this).val();
            const $supplierSelect = $('#selectSupplier');

            $supplierSelect.html('<option value="">Loading Suppliers...</option>');

            if (outletId) {
                $.ajax({
                    url: "{{ url('purchase-order/get-suppliers-by-branch') }}/" + outletId,
                    method: 'GET',
                    success: function(data) {
                        let options = '<option value="">Search or select supplier</option>';
                        if (data.length > 0) {
                            data.forEach(function(s) {
                                options += `<option value="${s.id}">${s.supplier_name}</option>`;
                            });
                        } else {
                            options = '<option value="">No suppliers found for this branch</option>';
                        }
                        $supplierSelect.html(options);
                    },
                    error: function() {
                        $supplierSelect.html('<option value="">Error fetching data</option>');
                    }
                });
            } else {
                $supplierSelect.html('<option value="">-- Select Branch First --</option>');
            }
        });
    });

    function showDetailPO(id) {
        // Reset body
        $('#detailPoBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border spinner-border-sm text-primary"></div>
            <p class="small text-muted mt-2">Memuat data...</p>
        </div>
    `);
        $('#detailPoNumber').text('Detail Purchase Order');
        $('#detailPoDate').text('');
        $('#detailPoStatusBadge').html('');
        $('#modalDetailPO').modal('show');

        $.ajax({
            url: `/purchase-order/${id}`,
            method: 'GET',
            success: function(res) {
                if (res.status !== 'success') {
                    $('#detailPoBody').html(`<div class="alert alert-danger">${res.message}</div>`);
                    return;
                }

                const po = res.po;
                const details = res.details;

                // Header info
                $('#detailPoNumber').text(po.po_number);
                $('#detailPoDate').text('Request: ' + formatDate(po.request_date) + ' · Required: ' + formatDate(po.required_date));

                // Status badge
                const statusMap = {
                    'PENDING': 'bg-warning text-dark',
                    'APPROVED': 'bg-success text-white',
                    'REJECTED': 'bg-danger text-white',
                };
                const badgeClass = statusMap[po.status] || 'bg-secondary text-white';
                $('#detailPoStatusBadge').html(`<span class="badge ${badgeClass} px-3 py-2">${po.status}</span>`);

                // Build items table
                let rows = '';
                let grandTotal = 0;
                details.forEach((d, i) => {
                    const subtotal = d.po_qty * d.price;
                    grandTotal += subtotal;
                    rows += `
                <tr>
                    <td class="px-3 small">${i + 1}</td>
                    <td class="px-3">
                        <div class="fw-bold small">${d.nama_bahan}</div>
                        <small class="text-muted">${d.product_code ?? '-'}</small>
                    </td>
                    <td class="px-3 small">${d.nama_unit ?? '-'}</td>
                    <td class="px-3 small text-center">${d.po_qty}</td>
                    <td class="px-3 small text-end">${formatRp(d.price)}</td>
                    <td class="px-3 small text-end fw-bold">${formatRp(subtotal)}</td>
                </tr>`;
                });

                const vatAmt = po.vat_amount ?? 0;
                const discount = po.discount ?? 0;

                $('#detailPoBody').html(`
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">Branch</p>
                        <p class="small fw-bold mb-0">${po.branch_name ?? '-'}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">Supplier</p>
                        <p class="small fw-bold mb-0">${po.supplier_name}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">Currency / Rate</p>
                        <p class="small fw-bold mb-0">${po.currency ?? 'IDR'} · ${po.rate ?? 1}</p>
                    </div>
                    ${po.notes ? `<div class="col-12"><p class="small text-muted mb-1">Notes</p><p class="small mb-0">${po.notes}</p></div>` : ''}
                    ${po.footnote ? `<div class="col-12"><p class="small text-muted mb-1">Foot Note</p><p class="small mb-0">${po.footnote}</p></div>` : ''}
                </div>
 
                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <thead class="bg-light text-uppercase" style="font-size:11px;">
                            <tr>
                                <th class="px-3 py-2">#</th>
                                <th class="px-3 py-2">Product</th>
                                <th class="px-3 py-2">Unit</th>
                                <th class="px-3 py-2 text-center">Qty</th>
                                <th class="px-3 py-2 text-end">Price</th>
                                <th class="px-3 py-2 text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="5" class="px-3 small text-end text-muted">Subtotal</td>
                                <td class="px-3 small text-end">${formatRp(po.subtotal)}</td>
                            </tr>
                            ${discount > 0 ? `<tr>
                                <td colspan="5" class="px-3 small text-end text-muted">Discount</td>
                                <td class="px-3 small text-end text-danger">(${formatRp(discount)})</td>
                            </tr>` : ''}
                            ${vatAmt > 0 ? `<tr>
                                <td colspan="5" class="px-3 small text-end text-muted">VAT (11%)</td>
                                <td class="px-3 small text-end">${formatRp(vatAmt)}</td>
                            </tr>` : ''}
                            <tr>
                                <td colspan="5" class="px-3 fw-bold text-end text-primary">Total</td>
                                <td class="px-3 fw-bold text-end text-primary">${formatRp(po.total_amount)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `);
            },
            error: function(xhr) {
                $('#detailPoBody').html(`<div class="alert alert-danger">Gagal memuat data: ${xhr.responseJSON?.message ?? 'Server error'}</div>`);
            }
        });
    }

    // ==============================================================
    // EDIT PO — buka modal edit dan isi datanya
    // ==============================================================
    function openEditPO(id) {
        // Reset form
        $('#formEditPO')[0].reset();
        $('#editProductContainer').html(`
        <tr id="editEmptyRow">
            <td colspan="7" class="text-center py-4 text-muted small">Memuat data...</td>
        </tr>
    `);
        $('#editProductCount').text(0);
        $('#editPoTitle').text('Edit Purchase Order');
        $('#modalEditPO').modal('show');

        $.ajax({
            url: `/purchase-order/${id}/edit`,
            method: 'GET',
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Error', res.message, 'error');
                    $('#modalEditPO').modal('hide');
                    return;
                }

                const po = res.po;

                $('#editPoId').val(po.id);
                $('#editPoTitle').text(`Edit PO: ${po.po_number}`);
                $('#editSelectBranch').val(po.branch_id);
                $('#editSelectSupplier').val(po.supplier_id);
                $('#editRequestDate').val(po.request_date ? po.request_date.substring(0, 10) : '');
                $('#editRequiredDate').val(po.required_date ? po.required_date.substring(0, 10) : '');
                $('#editCurrency').val(po.currency ?? 'IDR');
                $('#editRate').val(po.rate ?? 1);
                $('#editNotes').val(po.notes ?? '');
                $('#editFootnote').val(po.footnote ?? '');
                if (po.vat_amount > 0) $('#editVatAll').prop('checked', true);

                // Isi tabel produk
                $('#editProductContainer').empty();
                editProductIndex = 0;

                res.details.forEach(d => {
                    addEditProductRow({
                        id: d.product_id,
                        unit_id: d.unit_id,
                        name: d.nama_bahan,
                        code: d.product_code ?? '',
                        unit: d.nama_unit ?? '-',
                        po_qty: d.po_qty,
                        price: d.price,
                    });
                });

                recalcEditSummary();
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message ?? 'Gagal memuat data PO.';
                Swal.fire('Error', msg, 'error');
                $('#modalEditPO').modal('hide');
            }
        });
    }

    // ==============================================================
    // EDIT — Tambah baris produk
    // ==============================================================
    let editProductIndex = 0;

    function addEditProductRow(data = {}) {
        editProductIndex++;
        $('#editEmptyRow').remove();

        const subtotal = (data.po_qty || 0) * (data.price || 0);

        const row = `
    <tr class="edit-product-row" id="editRow_${editProductIndex}">
        <td class="ps-3">
            <input type="hidden" name="items[${editProductIndex}][product_id]" value="${data.id ?? ''}">
            <input type="hidden" name="items[${editProductIndex}][unit_id]"    value="${data.unit_id ?? ''}">
            <div class="fw-bold small">${data.name ?? '-'}</div>
        </td>
        <td><small class="text-muted">${data.code ?? '-'}</small></td>
        <td><span class="badge bg-light text-dark border small">${data.unit ?? '-'}</span></td>
        <td>
            <input type="number" name="items[${editProductIndex}][po_qty]"
                   class="form-control form-control-sm edit-qty shadow-none border-primary"
                   value="${data.po_qty ?? 1}" min="1" style="width:80px;">
        </td>
        <td>
            <div class="input-group input-group-sm" style="width:130px;">
                <span class="input-group-text bg-light border-end-0">Rp</span>
                <input type="number" name="items[${editProductIndex}][price]"
                       class="form-control edit-price shadow-none border-start-0"
                       value="${data.price ?? 0}">
            </div>
        </td>
        <td class="text-end small fw-bold edit-row-subtotal">${formatRp(subtotal)}</td>
        <td class="pe-3 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger border-0 edit-remove-row">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>`;

        $('#editProductContainer').append(row);
        $('#editProductCount').text($('.edit-product-row').length);
    }

    function recalcEditSummary() {
        let subtotal = 0;
        $('.edit-product-row').each(function() {
            const qty = parseFloat($(this).find('.edit-qty').val()) || 0;
            const price = parseFloat($(this).find('.edit-price').val()) || 0;
            const sub = qty * price;
            $(this).find('.edit-row-subtotal').text(formatRp(sub));
            subtotal += sub;
        });
        const isVat = $('#editVatAll').is(':checked');
        const vat = isVat ? subtotal * 0.11 : 0;
        const total = subtotal + vat;
        $('#editSummarySubtotal').text(formatRp(subtotal));
        $('#editSummaryVAT').text(formatRp(vat));
        $('#editSummaryTotal').text(formatRp(total));
    }

    $(document).on('input', '.edit-qty, .edit-price', recalcEditSummary);
    $('#editVatAll').on('change', recalcEditSummary);

    $(document).on('click', '.edit-remove-row', function() {
        $(this).closest('tr').remove();
        const count = $('.edit-product-row').length;
        $('#editProductCount').text(count);
        if (count === 0) {
            $('#editProductContainer').html(`
            <tr id="editEmptyRow">
                <td colspan="7" class="text-center py-4 text-muted small">Belum ada produk</td>
            </tr>`);
        }
        recalcEditSummary();
    });

    // Browse product untuk modal edit
    $('#editBtnBrowse').on('click', function() {
        const masterProducts = @json($products);
        let options = {};
        masterProducts.forEach(p => {
            options[p.id] = `${p.product_code} - ${p.nama_bahan}`;
        });
        Swal.fire({
            title: 'Pilih Produk',
            input: 'select',
            inputOptions: options,
            inputPlaceholder: 'Cari produk...',
            showCancelButton: true,
            confirmButtonText: 'Tambah',
        }).then(result => {
            if (result.isConfirmed && result.value) {
                const p = masterProducts.find(x => x.id == result.value);
                addEditProductRow({
                    id: p.id,
                    unit_id: p.unit_id,
                    name: p.nama_bahan,
                    code: p.product_code ?? '',
                    unit: p.satuan ?? 'Pcs',
                    po_qty: 1,
                    price: p.base_price ?? 0,
                });
                recalcEditSummary();
            }
        });
    });

    // Simpan edit PO
    $('#btnUpdatePO').on('click', function() {
        if ($('.edit-product-row').length === 0) {
            Swal.fire('Peringatan', 'Tambahkan minimal 1 produk!', 'warning');
            return;
        }

        const poId = $('#editPoId').val();
        const $btn = $(this);

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: `/purchase-order/${poId}/update`,
            method: 'POST',
            data: $('#formEditPO').serialize(),
            success: function(res) {
                Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
            },
            error: function(xhr) {
                $btn.prop('disabled', false).text('Simpan Perubahan');
                Swal.fire('Error!', xhr.responseJSON?.message ?? 'Gagal menyimpan.', 'error');
            }
        });
    });

    // ==============================================================
    // DELETE PO
    // ==============================================================
    function confirmDeletePO(id, poNumber) {
        Swal.fire({
            title: `Hapus PO ${poNumber}?`,
            text: 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/purchase-order/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    Swal.fire('Dihapus!', res.message, 'success').then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON?.message ?? 'Gagal menghapus PO.', 'error');
                }
            });
        });
    }

    // ==============================================================
    // HELPER
    // ==============================================================
    function formatRp(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number ?? 0);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }
</script>
@endpush
@include('Temp.Investor.footer')
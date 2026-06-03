@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Konsistensi Styling dengan PO & Transfer */
    .dataTables_wrapper {
        padding: 0 !important;
    }

    table.dataTable {
        border-collapse: collapse !important;
        margin: 0 !important;
        width: 100% !important;
    }

    /* Header Styling agar pas dengan gambar */
    #soTable thead th {
        background-color: #f8f9fa !important;
        color: #2c3e50 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 15px 20px !important;
        border: none !important;
        border-bottom: 1px solid #f1f4f8 !important;
    }

    /* Body Row Styling */
    #soTable tbody td {
        padding: 1.2rem 20px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
        background-color: transparent !important;
    }

    /* Hilangkan double header yang sering muncul di scroll dataTables */
    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table {
        width: 100% !important;
        padding: 0 !important;
    }

    /* Styling Search & Length agar tidak mepet ke pinggir card */
    .dataTables_length,
    .dataTables_filter {
        padding: 1rem 1.5rem !important;
    }

    .dataTables_info,
    .dataTables_paginate {
        padding: 1rem 1.5rem !important;
    }

    /* Warna Hover Khusus Sales */
    #soTable tbody tr:hover {
        background-color: rgba(40, 167, 69, 0.04) !important;
        box-shadow: inset 4px 0 0 #28a745;
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

    /* Warna Hijau/Success untuk Sales */
    .bg-sales-subtle {
        background-color: #e2f9ed !important;
        color: #1eb461 !important;
        border: 1px solid #c3f2d7 !important;
    }

    .bg-pending-subtle {
        background-color: #fff8e1 !important;
        color: #f59e0b !important;
        border: 1px solid #ffecb3 !important;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Sales Order List</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Sales</a></li>
                                    <li class="breadcrumb-item active text-success" aria-current="page">Sales Order</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="mx-3">
                            <form action="#" method="GET" class="d-flex align-items-center">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted small">Period</span>
                                    <input type="date" name="start_date" class="form-control border-start-0" value="{{ date('Y-m-01') }}">
                                    <span class="input-group-text bg-white border-start-0 border-end-0">-</span>
                                    <input type="date" name="end_date" class="form-control border-start-0 border-end-0" value="{{ date('Y-m-d') }}">
                                    <button type="submit" class="btn btn-success px-3">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-success d-flex align-items-center">
                                    <i class="bi bi-download me-1"></i> Export
                                </button>
                                <button type="button" class="btn btn-success px-3 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalCreateSales">
                                    <i class="bi bi-plus-lg me-1"></i> Create SO
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-bag-check me-2 text-success"></i> Sales Transactions
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="soTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small fw-bolder text-secondary" style="background-color: #f8f9fa; letter-spacing: 0.05em;">
                                    <th class="py-3 text-center" style="width: 50px;"><input type="checkbox" id="checkAllSO" class="form-check-input"></th>
                                    <th class="py-3 px-3">SO Info</th>
                                    <th class="py-3 px-3">Customer / Outlet</th>
                                    <th class="py-3 px-3">Total Sales</th>
                                    <th class="py-3 px-3">Reference PO</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($sales_orders as $so)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" value="{{ $so->id }}">
                                    </td>
                                    <td class="px-3">
                                        <div class="fw-bold text-success" style="font-size: 0.85rem;">{{ $so->so_number }}</div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            {{ \Carbon\Carbon::parse($so->sales_date)->format('d M Y') }}
                                        </small>
                                    </td>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light text-success me-2" style="width: 25px; height: 25px; font-size: 12px;">
                                                <i class="bi bi-shop"></i>
                                            </div>
                                            <span class="fw-medium small">{{ $so->customer_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <span class="fw-bold small">Rp {{ number_format($so->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-3">
                                        <span class="badge bg-light text-muted border fw-normal">
                                            {{ $so->reference_number ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                        $statusBadge = [
                                        'NEW' => 'bg-info-subtle text-info',
                                        'AUTHORIZED' => 'bg-warning-subtle text-warning',
                                        'DELIVERED' => 'bg-success-subtle text-success',
                                        'CLOSED' => 'bg-secondary-subtle text-secondary',
                                        ];
                                        $class = $statusBadge[$so->status] ?? 'bg-light text-dark';
                                        @endphp
                                        <span class="badge {{ $class }} border px-3 py-2 fw-bold" style="font-size: 0.7rem; border-radius: 8px;">
                                            {{ $so->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                                                <li>
                                                    <button type="button" class="dropdown-item rounded-2"
                                                        onclick="showDetailSO({{ $so->id }})">
                                                        <i class="bi bi-eye text-primary me-2"></i> Detail
                                                    </button>
                                                </li>
                                                {{-- Edit: hanya status NEW --}}
                                                @if($so->status == 'NEW')
                                                <li>
                                                    <button type="button" class="dropdown-item rounded-2"
                                                        onclick="openEditSO({{ $so->id }})">
                                                        <i class="bi bi-pencil-square text-warning me-2"></i> Edit
                                                    </button>
                                                </li>
                                                @endif

                                                {{-- Authorize: hanya status NEW → ubah ke AUTHORIZED --}}
                                                @if($so->status == 'NEW')
                                                <li>
                                                    <button type="button" class="dropdown-item rounded-2"
                                                        onclick="confirmAuthorizeSO({{ $so->id }}, '{{ $so->so_number }}')">
                                                        <i class="bi bi-check-circle text-success me-2"></i> Authorize
                                                    </button>
                                                </li>
                                                @endif

                                                <li>
                                                    <a class="dropdown-item rounded-2" href="#">
                                                        <i class="bi bi-printer text-secondary me-2"></i> Print SO
                                                    </a>
                                                </li>

                                                {{-- Hapus: hanya status NEW --}}
                                                @if($so->status == 'NEW')
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item rounded-2 text-danger"
                                                        onclick="confirmDeleteSO({{ $so->id }}, '{{ $so->so_number }}')">
                                                        <i class="bi bi-trash3-fill me-2"></i> Hapus
                                                    </button>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- === MODAL ADD SALES ORDER === -->
            <div class="modal fade" id="modalCreateSales" tabindex="-1" aria-labelledby="modalCreateSalesLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0" style="border-radius: 12px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold" id="modalCreateSalesLabel">Create Product Sales - New</h5>
                            <div class="ms-auto d-flex gap-2">
                                <button class="btn btn-sm btn-info text-white px-3 shadow-sm"><i class="bi bi-info-circle me-1"></i> Transaction Information</button>
                                <button class="btn btn-sm btn-outline-info px-3 shadow-sm"><i class="bi bi-lightbulb me-1"></i> Help</button>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                        </div>

                        <div class="modal-body px-4 pb-4">
                            <form id="formCreateSales">
                                @csrf
                                <div class="card border shadow-none mb-4" style="background-color: #f8f9fa;">
                                    <div class="card-header bg-white py-2 fw-bold small border-bottom">Transaction Information</div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="small fw-bold">Branch</label>
                                                <select name="branch_id" class="form-select form-select-sm shadow-none">
                                                    <option value="">Select Branch</option>
                                                    @foreach($branches as $b)
                                                    <option value="{{ $b->id }}">{{ $b->nama_outlet }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Product Sales Date</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="date" name="sales_date" class="form-control shadow-none" value="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Required Date</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="date" name="required_date" class="form-control shadow-none" value="{{ date('Y-m-d') }}">
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="small fw-bold">Currency</label>
                                                <select name="currency" class="form-select form-select-sm shadow-none">
                                                    <option value="IDR">Rupiah</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Rate</label>
                                                <input type="number" name="rate" class="form-control form-control-sm bg-light shadow-none" value="1" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Customer</label>
                                                <div class="input-group input-group-sm">
                                                    <select name="customer_id" class="form-select shadow-none">
                                                        <option value="">Select Customer</option>
                                                        @foreach($customers as $c)
                                                        <option value="{{ $c->customerID }}">{{ $c->customerName }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button class="btn btn-info text-white" type="button"><i class="bi bi-three-dots"></i></button>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Customer Pic Name</label>
                                                <input type="text" name="customer_pic_name" class="form-control form-control-sm bg-light shadow-none" readonly>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="small fw-bold">Customer Pic Phone</label>
                                                <input type="text" name="customer_pic_phone" class="form-control form-control-sm bg-light shadow-none" readonly>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Customer Due Day</label>
                                                <input type="text" name="customer_due_day" class="form-control form-control-sm bg-light shadow-none" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small fw-bold">Sales Rep Name</label>
                                                <div class="input-group input-group-sm">
                                                    <select name="sales_rep_id" class="form-select shadow-none">
                                                        <option value="">Select Sales Representative</option>
                                                    </select>
                                                    <button class="btn btn-info text-white" type="button"><i class="bi bi-three-dots"></i></button>
                                                    <button class="btn btn-danger" type="button"><i class="bi bi-x-lg"></i></button>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="small fw-bold">Reference Number</label>
                                                <input type="text" name="reference_number" class="form-control form-control-sm bg-light shadow-none" readonly>
                                            </div>
                                            <div class="col-md-9">
                                                <label class="small fw-bold">Address</label>
                                                <textarea name="address" class="form-control form-control-sm shadow-none" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border shadow-none mb-4">
                                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                        <span class="fw-bold small">Product Detail</span>
                                        <!-- Tombol Tambah Produk Manual -->
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm px-3" onclick="openProductPicker()">
                                            <i class="bi bi-plus-circle me-1"></i> Add Product
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="bg-light small text-uppercase">
                                                <tr>
                                                    <th class="ps-3" style="width: 35%;">Product Name</th>
                                                    <th style="width: 15%;">Unit</th>
                                                    <th class="text-center" style="width: 10%;">Qty</th>
                                                    <th style="width: 20%;">Price</th>
                                                    <th class="text-end" style="width: 15%;">Subtotal</th>
                                                    <th class="text-center" style="width: 5%;"><i class="bi bi-gear"></i></th>
                                                </tr>
                                            </thead>
                                            <!-- Tempat JavaScript merender baris produk -->
                                            <tbody id="productSalesContainer" class="small">
                                                <tr>
                                                    <td colspan="6" class="py-4 text-center text-muted">
                                                        <i class="bi bi-cart-x d-block mb-1" style="font-size: 1.5rem;"></i>
                                                        No products added yet.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card border shadow-none mb-4">
                                    <div class="card-header bg-white py-2 fw-bold small border-bottom">Transaction Summary</div>
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <label class="small fw-bold">Additional Information</label>
                                                <textarea name="additional_info" class="form-control shadow-none" rows="2"></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small fw-bold d-block text-end">Product Sales Total</label>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" id="productSalesTotal" class="form-control text-end bg-light fw-bold" value="0" readonly style="font-size: 1.1rem;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                            <div class="me-auto"></div>
                            <button type="button" class="btn btn-sm btn-info text-white px-4" id="btnSaveSales"><i class="bi bi-save me-1"></i> Save</button>
                            <button type="button" class="btn btn-sm btn-danger px-4" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cancel</button>
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
                    2. MODAL DETAIL SO
                        Tempel setelah penutup </div> dari modal Create SO
                    ============================================================ --}}
            <div class="modal fade" id="modalDetailSO" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0" style="border-radius: 12px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <div>
                                <h5 class="modal-title fw-bold mb-0" id="detailSoNumber">Detail Sales Order</h5>
                                <small class="text-muted" id="detailSoDate"></small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body px-4 pb-2" id="detailSoBody">
                            <div class="text-center py-5">
                                <div class="spinner-border spinner-border-sm text-success"></div>
                                <p class="small text-muted mt-2">Memuat data...</p>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 px-4 pb-4">
                            <span id="detailSoStatusBadge"></span>
                            <div class="ms-auto">
                                <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ============================================================
     3. MODAL EDIT SO
        Tempel setelah modal Detail SO
     ============================================================ --}}
            <div class="modal fade" id="modalEditSO" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0" style="border-radius: 12px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold" id="editSoTitle">Edit Sales Order</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body px-4 pb-4">
                            <form id="formEditSO">
                                @csrf
                                <input type="hidden" id="editSoId" name="so_id">

                                {{-- Transaction Info --}}
                                <div class="card border shadow-none mb-4" style="background-color: #f8f9fa;">
                                    <div class="card-header bg-white py-2 fw-bold small border-bottom">Transaction Information</div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="small fw-bold">Branch</label>
                                                <select name="branch_id" id="editSoBranch" class="form-select form-select-sm shadow-none">
                                                    <option value="">Select Branch</option>
                                                    @foreach($branches as $b)
                                                    <option value="{{ $b->id }}">{{ $b->nama_outlet }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Sales Date</label>
                                                <input type="date" name="sales_date" id="editSoSalesDate" class="form-control form-control-sm shadow-none">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Required Date</label>
                                                <input type="date" name="required_date" id="editSoRequiredDate" class="form-control form-control-sm shadow-none">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Currency</label>
                                                <select name="currency" id="editSoCurrency" class="form-select form-select-sm shadow-none">
                                                    <option value="IDR">Rupiah</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Rate</label>
                                                <input type="number" name="rate" id="editSoRate" class="form-control form-control-sm bg-light shadow-none" value="1" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="small fw-bold">Customer</label>
                                                <select name="customer_id" id="editSoCustomer" class="form-select form-select-sm shadow-none">
                                                    <option value="">Select Customer</option>
                                                    @foreach($customers as $c)
                                                    <option value="{{ $c->customerID }}">{{ $c->customerName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Reference Number</label>
                                                <input type="text" name="reference_number" id="editSoRefNumber" class="form-control form-control-sm shadow-none">
                                            </div>
                                            <div class="col-md-9">
                                                <label class="small fw-bold">Address</label>
                                                <textarea name="address" id="editSoAddress" class="form-control form-control-sm shadow-none" rows="2"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Product Detail --}}
                                <div class="card border shadow-none mb-4">
                                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                                        <span class="fw-bold small">Product Detail</span>
                                        <button type="button" class="btn btn-sm btn-primary shadow-sm px-3" id="editSoBtnAddProduct">
                                            <i class="bi bi-plus-circle me-1"></i> Add Product
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="bg-light small text-uppercase">
                                                <tr>
                                                    <th class="ps-3" style="width:35%;">Product Name</th>
                                                    <th style="width:15%;">Unit</th>
                                                    <th class="text-center" style="width:10%;">Qty</th>
                                                    <th style="width:20%;">Price</th>
                                                    <th class="text-end" style="width:15%;">Subtotal</th>
                                                    <th class="text-center" style="width:5%;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="editSoProductContainer" class="small">
                                                <tr id="editSoEmptyRow">
                                                    <td colspan="6" class="py-4 text-center text-muted">
                                                        <i class="bi bi-cart-x d-block mb-1" style="font-size:1.5rem;"></i>
                                                        Belum ada produk.
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Summary --}}
                                <div class="card border shadow-none mb-2">
                                    <div class="card-body py-2">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <label class="small fw-bold">Additional Information</label>
                                                <textarea name="additional_info" id="editSoAdditionalInfo" class="form-control form-control-sm shadow-none" rows="2"></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small fw-bold d-block text-end">Total</label>
                                                <input type="text" id="editSoTotal" class="form-control form-control-sm text-end bg-light fw-bold" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                            <button type="button" class="btn btn-sm btn-light px-3" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-sm btn-success px-4" id="btnUpdateSO">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan
                            </button>
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
    // SATU $(document).ready() — semua event binding di sini
    //
    // FIX: Sebelumnya ada 3 blok $(document).ready() terpisah.
    // Akibatnya:
    //   1. #btnSaveSales terdaftar 3x → tiap klik kirim 3 AJAX → 3 baris tersimpan
    //   2. addSalesRow() ada di blok 2, dipanggil di blok 3 (executeSelection)
    //      → ReferenceError: addSalesRow is not defined
    //   3. formatRupiah() duplikat di blok 2 dan 3
    // Solusi: gabungkan semua ke dalam satu blok ready().
    // =========================================================
    $(document).ready(function() {

        // --- DATATABLE ---
        if ($.fn.DataTable.isDataTable('#soTable')) {
            $('#soTable').DataTable().destroy();
        }
        $('#soTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_ entries",
            },
            columnDefs: [{
                    targets: [0, 6],
                    orderable: false
                },
                {
                    width: "5%",
                    targets: 0
                },
                {
                    width: "15%",
                    targets: 1
                },
                {
                    width: "25%",
                    targets: 2
                },
                {
                    width: "15%",
                    targets: 3
                },
                {
                    width: "15%",
                    targets: 4
                },
                {
                    width: "15%",
                    targets: 5
                },
                {
                    width: "10%",
                    targets: 6
                },
            ]
        });

        // --------------------------------------------------------
        // CREATE SO FORM
        // --------------------------------------------------------
        let salesIndex = 0;
        const masterProducts = @json($products); // Data dari Controller

        // --- 1. FUNGSI TAMBAH BARIS PRODUK ---
        function addSalesRow(data = {}) {
            salesIndex++;

            // Hapus baris "No products added" jika ada
            if ($('#productSalesContainer tr').length === 1 && $('#productSalesContainer td').attr('colspan')) {
                $('#productSalesContainer').empty();
            }

            const rowHtml = `
        <tr class="sales-row">
            <td class="ps-3 text-start">
                <input type="hidden" name="items[${salesIndex}][product_id]" value="${data.id}">
                <input type="hidden" name="items[${salesIndex}][unit_id]" value="${data.unit_id}">
                <div class="fw-bold small text-dark">${data.name}</div>
                <small class="text-muted">${data.code}</small>
            </td>
            <td><span class="badge bg-light text-dark border small">${data.unit}</span></td>
            <td>
                <input type="number" name="items[${salesIndex}][qty]" class="form-control form-control-sm text-center sales-qty mx-auto" value="1" min="1" style="width: 70px;">
            </td>
            <td>
                <input type="number" name="items[${salesIndex}][price]" class="form-control form-control-sm text-end sales-price" value="${data.price}" style="width: 120px;">
            </td>
            <td class="pe-3 text-end fw-bold">
                <span class="row-subtotal">${formatRupiah(data.price)}</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-link text-danger remove-sales-row"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;

            $('#productSalesContainer').append(rowHtml);
            calculateSalesTotal();
        }

        // --- 2. EVENT: KLIK TOMBOL BROWSE (Pilih Produk) ---
        // Kamu bisa panggil ini dari tombol di modal atau integrasikan dengan pencarian
        window.openProductPicker = function() {
            let options = {};
            masterProducts.forEach(p => {
                options[p.id] = `${p.product_code} - ${p.nama_bahan}`;
            });

            Swal.fire({
                title: 'Select Product',
                input: 'select',
                inputOptions: options,
                showCancelButton: true,
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const selected = masterProducts.find(p => p.id == result.value);
                    addSalesRow({
                        id: selected.id,
                        unit_id: selected.unit_id,
                        name: selected.nama_bahan,
                        code: selected.product_code,
                        unit: selected.satuan,
                        price: selected.base_price || 0
                    });
                }
            });
        };

        // --- 3. EVENT: HITUNG TOTAL OTOMATIS ---
        $(document).on('input', '.sales-qty, .sales-price', function() {
            const row = $(this).closest('tr');
            const qty = parseFloat(row.find('.sales-qty').val()) || 0;
            const price = parseFloat(row.find('.sales-price').val()) || 0;
            const subtotal = qty * price;

            row.find('.row-subtotal').text(formatRupiah(subtotal));
            calculateSalesTotal();
        });

        function calculateSalesTotal() {
            let total = 0;
            $('.sales-row').each(function() {
                const qty = parseFloat($(this).find('.sales-qty').val()) || 0;
                const price = parseFloat($(this).find('.sales-price').val()) || 0;
                total += (qty * price);
            });
            $('#productSalesTotal').val(formatRupiah(total));
        }

        // --- 4. EVENT: HAPUS BARIS ---
        $(document).on('click', '.remove-sales-row', function() {
            $(this).closest('tr').remove();
            if ($('.sales-row').length === 0) {
                $('#productSalesContainer').html('<tr><td colspan="6" class="py-4 text-center text-muted"><i class="bi bi-cart-x d-block mb-1" style="font-size: 1.5rem;"></i>No products added yet.</td></tr>');
            }
            calculateSalesTotal();
        });

        // --- 5. EVENT: SIMPAN KE DATABASE ---
        // FIX: tambahkan double-click guard + beforeSend untuk disable tombol
        // sehingga tidak bisa klik 2x walaupun masih ada sisa listener lama.
        $('#btnSaveSales').on('click', function() {
            if ($(this).prop('disabled')) return; // double-click guard

            if ($('.sales-row').length === 0) {
                Swal.fire('Error', 'Please add at least one product', 'error');
                return;
            }

            const $btn = $(this);
            const formData = $('#formCreateSales').serialize();

            $.ajax({
                url: "{{ route('sales-order.store') }}",
                method: "POST",
                data: formData,
                beforeSend: function() {
                    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');
                },
                success: function(res) {
                    Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Save');
                    Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error');
                }
            });
        });

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        // --------------------------------------------------------
        // PRODUCT PICKER MODAL (merged from block 3)
        // --------------------------------------------------------
        // 1. Inisialisasi DataTable pada Modal Picker
        const tablePicker = $('#tableProductPicker').DataTable({
            responsive: true,
            pageLength: 10,
            dom: '<"d-flex justify-content-between align-items-center mb-3"f>rtip', // Menampilkan search box (f) di kanan atas
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search product name or code...",
            }
        });

        // 2. Fungsi buka modal
        window.openProductPicker = function() {
            $('#modalProductPicker').modal('show');
            // Reset filter pencarian saat dibuka kembali (opsional)
            tablePicker.search('').draw();
        };

        // 3. Event: Klik pada baris tabel (Biar sat-set)
        $(document).on('click', '.product-row-picker', function(e) {
            // Jika yang diklik bukan radio button itu sendiri, trigger klik radionya
            if (!$(e.target).is('input[type="radio"]')) {
                $(this).find('.select-radio').prop('checked', true);
            }

            // Opsional: Langsung konfirmasi jika ingin mode "Sekali Klik"
            // let productId = $(this).data('id');
            // executeSelection(productId);
        });

        // 4. Event: Klik tombol konfirmasi "Select Product"
        // Ganti $('#btnConfirmSelect').on('click', ...) dengan ini:
        $(document).off('click', '#btnConfirmSelect').on('click', '#btnConfirmSelect', function(e) {
            e.preventDefault();

            // Ambil value dari radio button yang dicentang
            const selectedId = $('input[name="product_radio"]:checked').val();

            console.log("ID yang dipilih:", selectedId); // Untuk cek di inspect element (F12)

            if (selectedId) {
                executeSelection(selectedId);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Produk',
                    text: 'Silakan pilih salah satu produk dari tabel terlebih dahulu!',
                });
            }
        });

        // Fungsi inti untuk memindahkan data ke tabel modalCreateSales
        function executeSelection(productId) {
            // Ambil data products dari variabel yang di-pass controller
            const allProducts = @json($products);
            const selected = allProducts.find(p => p.id == productId);

            if (selected) {
                // Panggil fungsi untuk nambah baris di modal utama
                // Pastikan fungsi addSalesRow sudah kamu definisikan sebelumnya
                addSalesRow({
                    id: selected.id,
                    unit_id: selected.unit_id,
                    name: selected.nama_bahan,
                    code: selected.product_code,
                    unit: selected.satuan,
                    price: selected.base_price || 0
                });

                // Tutup modal picker
                $('#modalProductPicker').modal('hide');

                // Reset pilihan radio
                $('input[name="product_radio"]').prop('checked', false);
            } else {
                console.error("Data produk tidak ditemukan untuk ID:", productId);
            }
        }

        // ---- END of $(document).ready() ----
    }); // single ready block closes here

    function fmtRp(n) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(n ?? 0);
    }

    function fmtDate(s) {
        if (!s) return '-';
        return new Date(s).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    // ==============================================================
    // SHOW / DETAIL SO
    // ==============================================================
    function showDetailSO(id) {
        $('#detailSoBody').html(`
        <div class="text-center py-5">
            <div class="spinner-border spinner-border-sm text-success"></div>
            <p class="small text-muted mt-2">Memuat data...</p>
        </div>`);
        $('#detailSoNumber').text('Detail Sales Order');
        $('#detailSoDate').text('');
        $('#detailSoStatusBadge').html('');
        $('#modalDetailSO').modal('show');

        $.ajax({
            url: `/sales-order/${id}`,
            method: 'GET',
            success: function(res) {
                if (res.status !== 'success') {
                    $('#detailSoBody').html(`<div class="alert alert-danger">${res.message}</div>`);
                    return;
                }

                const so = res.so;
                const details = res.details;

                $('#detailSoNumber').text(so.so_number);
                $('#detailSoDate').text('Sales: ' + fmtDate(so.sales_date) + ' · Required: ' + fmtDate(so.required_date));

                const statusMap = {
                    'NEW': 'bg-info text-white',
                    'AUTHORIZED': 'bg-warning text-dark',
                    'DELIVERED': 'bg-success text-white',
                    'CLOSED': 'bg-secondary text-white',
                };
                const badgeClass = statusMap[so.status] ?? 'bg-secondary text-white';
                $('#detailSoStatusBadge').html(`<span class="badge ${badgeClass} px-3 py-2">${so.status}</span>`);

                let rows = '';
                details.forEach((d, i) => {
                    rows += `
                <tr>
                    <td class="px-3 small">${i + 1}</td>
                    <td class="px-3">
                        <div class="fw-bold small">${d.nama_bahan}</div>
                        <small class="text-muted">${d.product_code ?? '-'}</small>
                    </td>
                    <td class="px-3 small">${d.nama_unit ?? '-'}</td>
                    <td class="px-3 small text-center">${d.qty}</td>
                    <td class="px-3 small text-end">${fmtRp(d.price)}</td>
                    <td class="px-3 small text-end fw-bold">${fmtRp(d.subtotal)}</td>
                </tr>`;
                });

                $('#detailSoBody').html(`
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">Branch</p>
                        <p class="small fw-bold mb-0">${so.branch_name ?? '-'}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">Customer</p>
                        <p class="small fw-bold mb-0">${so.customer_name ?? '-'}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">Currency / Rate</p>
                        <p class="small fw-bold mb-0">${so.currency ?? 'IDR'} · ${so.rate ?? 1}</p>
                    </div>
                    ${so.reference_number ? `
                    <div class="col-md-4">
                        <p class="small text-muted mb-1">Reference Number</p>
                        <p class="small fw-bold mb-0">${so.reference_number}</p>
                    </div>` : ''}
                    ${so.address ? `
                    <div class="col-md-8">
                        <p class="small text-muted mb-1">Address</p>
                        <p class="small mb-0">${so.address}</p>
                    </div>` : ''}
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
                                <td colspan="5" class="px-3 fw-bold text-end text-success">Total</td>
                                <td class="px-3 fw-bold text-end text-success">${fmtRp(so.total_amount)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `);
            },
            error: function(xhr) {
                $('#detailSoBody').html(`<div class="alert alert-danger">Gagal memuat data: ${xhr.responseJSON?.message ?? 'Server error'}</div>`);
            }
        });
    }

    // ==============================================================
    // EDIT SO
    // ==============================================================
    let editSoIndex = 0;

    function openEditSO(id) {
        $('#formEditSO')[0].reset();
        $('#editSoProductContainer').html(`
        <tr id="editSoEmptyRow">
            <td colspan="6" class="py-4 text-center text-muted">Memuat data...</td>
        </tr>`);
        $('#editSoTitle').text('Edit Sales Order');
        $('#editSoTotal').val('');
        editSoIndex = 0;
        $('#modalEditSO').modal('show');

        $.ajax({
            url: `/sales-order/${id}/edit`,
            method: 'GET',
            success: function(res) {
                if (res.status !== 'success') {
                    Swal.fire('Error', res.message, 'error');
                    $('#modalEditSO').modal('hide');
                    return;
                }

                const so = res.so;
                $('#editSoId').val(so.id);
                $('#editSoTitle').text('Edit SO: ' + so.so_number);
                $('#editSoBranch').val(so.branch_id ?? '');
                $('#editSoSalesDate').val(so.sales_date ? so.sales_date.substring(0, 10) : '');
                $('#editSoRequiredDate').val(so.required_date ? so.required_date.substring(0, 10) : '');
                $('#editSoCurrency').val(so.currency ?? 'IDR');
                $('#editSoRate').val(so.rate ?? 1);
                $('#editSoCustomer').val(so.customer_id ?? '');
                $('#editSoRefNumber').val(so.reference_number ?? '');
                $('#editSoAddress').val(so.address ?? '');

                // Isi tabel produk
                $('#editSoProductContainer').empty();
                res.details.forEach(d => {
                    addEditSoRow({
                        id: d.product_id,
                        unit_id: d.unit_id,
                        detail_id: d.id, // FIX: kirim id detail agar bisa UPDATE bukan DELETE
                        name: d.nama_bahan,
                        code: d.product_code ?? '',
                        unit: d.nama_unit ?? '-',
                        qty: d.qty,
                        price: d.price,
                    });
                });
                recalcEditSoTotal();
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Gagal memuat data SO.', 'error');
                $('#modalEditSO').modal('hide');
            }
        });
    }

    function addEditSoRow(data = {}) {
        editSoIndex++;
        $('#editSoEmptyRow').remove();

        const subtotal = (data.qty || 0) * (data.price || 0);

        const row = `
    <tr class="edit-so-row" id="editSoRow_${editSoIndex}">
        <td class="ps-3">
            <input type="hidden" name="items[${editSoIndex}][product_id]" value="${data.id ?? ''}">
            <input type="hidden" name="items[${editSoIndex}][unit_id]"    value="${data.unit_id ?? ''}">
            <!-- FIX: kirim detail_id agar controller bisa UPDATE bukan DELETE+INSERT -->
            <input type="hidden" name="items[${editSoIndex}][detail_id]"  value="${data.detail_id ?? ''}">
            <div class="fw-bold small">${data.name ?? '-'}</div>
            <small class="text-muted">${data.code ?? ''}</small>
        </td>
        <td><span class="badge bg-light text-dark border small">${data.unit ?? '-'}</span></td>
        <td class="text-center">
            <input type="number" name="items[${editSoIndex}][qty]"
                   class="form-control form-control-sm text-center edit-so-qty shadow-none mx-auto"
                   value="${data.qty ?? 1}" min="1" style="width:70px;">
        </td>
        <td>
            <input type="number" name="items[${editSoIndex}][price]"
                   class="form-control form-control-sm text-end edit-so-price shadow-none"
                   value="${data.price ?? 0}" style="width:120px;">
        </td>
        <td class="text-end fw-bold small edit-so-row-subtotal">${fmtRp(subtotal)}</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger edit-so-remove-row">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>`;

        $('#editSoProductContainer').append(row);
        recalcEditSoTotal();
    }

    function recalcEditSoTotal() {
        let total = 0;
        $('.edit-so-row').each(function() {
            const qty = parseFloat($(this).find('.edit-so-qty').val()) || 0;
            const price = parseFloat($(this).find('.edit-so-price').val()) || 0;
            const sub = qty * price;
            $(this).find('.edit-so-row-subtotal').text(fmtRp(sub));
            total += sub;
        });
        $('#editSoTotal').val(fmtRp(total));
    }

    $(document).on('input', '.edit-so-qty, .edit-so-price', recalcEditSoTotal);

    $(document).on('click', '.edit-so-remove-row', function() {
        $(this).closest('tr').remove();
        if ($('.edit-so-row').length === 0) {
            $('#editSoProductContainer').html(`
            <tr id="editSoEmptyRow">
                <td colspan="6" class="py-4 text-center text-muted">
                    <i class="bi bi-cart-x d-block mb-1" style="font-size:1.5rem;"></i>
                    Belum ada produk.
                </td>
            </tr>`);
        }
        recalcEditSoTotal();
    });

    // Browse product untuk modal edit SO
    $('#editSoBtnAddProduct').on('click', function() {
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
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed && result.value) {
                const p = masterProducts.find(x => x.id == result.value);
                addEditSoRow({
                    id: p.id,
                    unit_id: p.unit_id,
                    name: p.nama_bahan,
                    code: p.product_code ?? '',
                    unit: p.satuan ?? '-',
                    qty: 1,
                    price: p.base_price ?? 0,
                });
            }
        });
    });

    // Simpan edit SO
    $('#btnUpdateSO').on('click', function() {
        if ($('.edit-so-row').length === 0) {
            Swal.fire('Peringatan', 'Tambahkan minimal 1 produk!', 'warning');
            return;
        }

        const soId = $('#editSoId').val();
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');

        $.ajax({
            url: `/sales-order/${soId}/update`,
            method: 'POST',
            data: $('#formEditSO').serialize(),
            success: function(res) {
                Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Perubahan');
                Swal.fire('Error!', xhr.responseJSON?.message ?? 'Gagal menyimpan.', 'error');
            }
        });
    });

    // ==============================================================
    // AUTHORIZE SO — NEW → AUTHORIZED
    // ==============================================================
    function confirmAuthorizeSO(id, soNumber) {
        Swal.fire({
            title: `Authorize SO ${soNumber}?`,
            html: `Status akan berubah dari <b>NEW</b> ke <b>AUTHORIZED</b>.<br>
                   Setelah diauthorize, SO siap dibuatkan Goods Delivery.<br>
                   <small class="text-muted">SO tidak bisa diedit atau dihapus setelah ini.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Authorize!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#f59e0b',
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/sales-order/${id}/approve`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    Swal.fire('Authorized!', res.message, 'success')
                        .then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON?.message ?? 'Gagal authorize SO.', 'error');
                }
            });
        });
    }

    // ==============================================================
    // DELETE SO
    // ==============================================================
    function confirmDeleteSO(id, soNumber) {
        Swal.fire({
            title: `Hapus SO ${soNumber}?`,
            text: 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/sales-order/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    Swal.fire('Dihapus!', res.message, 'success').then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON?.message ?? 'Gagal menghapus SO.', 'error');
                }
            });
        });
    }
</script>
@endpush
@include('Temp.Investor.footer')
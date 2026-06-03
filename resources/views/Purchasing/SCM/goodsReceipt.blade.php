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

    #grTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    #grTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
    }

    #grTable tbody tr {
        transition: all 0.2s;
    }

    #grTable tbody tr:hover {
        background-color: rgba(255, 171, 0, 0.04) !important;
        box-shadow: inset 4px 0 0 #ffab00;
    }

    .bg-receipt-subtle {
        background-color: #fff8e1 !important;
        color: #ffab00 !important;
        border: 1px solid #ffecb3 !important;
    }

    .icon-shape {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    #itemsContainer .item-row {
        border: 1px solid #f1f4f8;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
        background: #fafafa;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- HEADER + TOMBOL --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Goods Receipt List</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Logistics</a></li>
                                    <li class="breadcrumb-item active text-warning" aria-current="page">Goods Receipt</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-warning px-3 shadow-sm d-flex align-items-center text-white"
                                    data-bs-toggle="modal" data-bs-target="#modalCreateGR">
                                    <i class="bi bi-box-arrow-in-down me-1"></i> Receive Goods
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- WIDGET RINGKASAN --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-dark">{{ $summary['total'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Total GR</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-secondary">{{ $summary['draft'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Draft</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-warning">{{ $summary['partial'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Partial</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-success">{{ $summary['received'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Received</div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════ --}}
            {{-- TABEL DAFTAR GR --}}
            {{-- ═══════════════════════════════════════════════════════════ --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-journal-check me-2 text-warning"></i> Incoming Stock Transactions
                    </h6>
                </div>
                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="grTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small fw-bolder text-secondary">
                                    <th class="text-center" style="width:50px;">
                                        <input type="checkbox" id="checkAllGR" class="form-check-input">
                                    </th>
                                    <th>Receipt Info</th>
                                    <th>Received From</th>
                                    <th>QC Status</th>
                                    <th>Reference PO</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                {{-- ═══ REAL DATA (bukan dummy @for) ═══ --}}
                                @forelse($goodsReceipts as $gr)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input">
                                    </td>

                                    {{-- Receipt Info --}}
                                    <td>
                                        <div class="fw-bold text-warning" style="font-size:0.85rem;">
                                            {{ $gr->gr_number }}
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar-check me-1"></i>
                                            {{ \Carbon\Carbon::parse($gr->receipt_date)->format('d M Y') }}
                                        </small>
                                    </td>

                                    {{-- Received From --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light text-warning me-2">
                                                <i class="bi bi-truck-flatbed"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium small">{{ $gr->supplier_name }}</div>
                                                @if($gr->driver_name)
                                                <small class="text-muted" style="font-size:10px;">
                                                    Driver: {{ $gr->driver_name }}
                                                </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- QC Status --}}
                                    <td>
                                        @php
                                        $qcColors = [
                                        'PENDING' => 'secondary',
                                        'PASSED' => 'success',
                                        'PARTIAL_REJECTED' => 'warning',
                                        'REJECTED' => 'danger',
                                        ];
                                        $qcColor = $qcColors[$gr->qc_status] ?? 'secondary';
                                        @endphp
                                        <span class="badge rounded-pill bg-{{ $qcColor }}-subtle text-{{ $qcColor }} border fw-normal">
                                            {{ $gr->qc_status }}
                                        </span>
                                    </td>

                                    {{-- Reference PO --}}
                                    <td>
                                        <small class="fw-bold text-muted d-block">{{ $gr->po_number }}</small>
                                        @if($gr->supplier_do_number)
                                        <small class="text-primary" style="font-size:10px;">
                                            DO: {{ $gr->supplier_do_number }}
                                        </small>
                                        @endif
                                    </td>

                                    {{-- Status GR --}}
                                    <td class="text-center">
                                        @php
                                        $statusColors = [
                                        'DRAFT' => 'secondary',
                                        'PARTIAL' => 'warning',
                                        'RECEIVED' => 'success',
                                        'CANCELLED' => 'danger',
                                        ];
                                        $statusColor = $statusColors[$gr->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge px-3 py-2 fw-bold bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border"
                                            style="font-size:0.7rem; border-radius:8px;">
                                            {{ $gr->status }}
                                        </span>
                                    </td>

                                    {{-- Action --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">

                                                {{-- Detail --}}
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnDetailGR" href="#"
                                                        data-id="{{ $gr->id }}">
                                                        <i class="bi bi-eye text-info me-2"></i> Lihat Detail
                                                    </a>
                                                </li>

                                                {{-- Konfirmasi (hanya untuk DRAFT) --}}
                                                @if($gr->status === 'DRAFT')
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnConfirmGR" href="#"
                                                        data-id="{{ $gr->id }}"
                                                        data-gr="{{ $gr->gr_number }}">
                                                        <i class="bi bi-check-circle text-success me-2"></i> Konfirmasi & Update Stok
                                                    </a>
                                                </li>
                                                @endif

                                                {{-- QC --}}
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnQC" href="#"
                                                        data-id="{{ $gr->id }}"
                                                        data-qc="{{ $gr->qc_status }}">
                                                        <i class="bi bi-clipboard-check text-warning me-2"></i> Quality Control
                                                    </a>
                                                </li>

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>

                                                {{-- Lihat invoice (hanya jika sudah RECEIVED) --}}
                                                @if($gr->status === 'RECEIVED')
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-primary" href="#">
                                                        <i class="bi bi-file-earmark-text me-2"></i> Buat Purchase Invoice
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
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                        Belum ada Goods Receipt. Klik "Receive Goods" untuk mulai.
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


{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: CREATE GR --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCreateGR" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-box-arrow-in-down me-2"></i> Receive Goods — Buat GR Baru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCreateGR">
                    @csrf
                    {{-- Row 1: Pilih PO + Tanggal --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Purchase Order <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="selectPO" name="purchase_order_id" required>
                                <option value="">-- Pilih PO yang akan diterima --</option>
                                @foreach($approvedPOs as $po)
                                <option value="{{ $po->id }}"
                                    data-supplier-id="{{ $po->supplier_id }}"
                                    data-supplier="{{ $po->supplier_name }}">
                                    {{ $po->po_number }} — {{ $po->supplier_name }}
                                    (Due: {{ \Carbon\Carbon::parse($po->required_date)->format('d M Y') }})
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hanya PO berstatus APPROVED yang tampil</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Terima <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="receipt_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Warehouse Tujuan <span class="text-danger">*</span></label>
                            <input type="hidden" name="warehouse_id" id="hiddenWarehouseId" value="{{ auth()->user()->warehouse_id ?? 1 }}">
                            <input type="text" class="form-control form-control-sm bg-light"
                                value="{{ auth()->user()->warehouse_id ? 'Warehouse #'.auth()->user()->warehouse_id : 'Warehouse SCM' }}"
                                readonly>
                        </div>
                    </div>

                    {{-- Row 2: Info supplier & driver --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Supplier</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="displaySupplier" readonly placeholder="Otomatis dari PO">
                            <input type="hidden" name="supplier_id" id="hiddenSupplierId">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">No. Surat Jalan Supplier</label>
                            <input type="text" class="form-control form-control-sm" name="supplier_do_number" placeholder="Contoh: SJ/SUP/2026/001">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nama Driver</label>
                            <input type="text" class="form-control form-control-sm" name="driver_name" placeholder="Nama driver pengantar">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Plat Kendaraan</label>
                            <input type="text" class="form-control form-control-sm" name="vehicle_plate" placeholder="B 1234 ABC">
                        </div>
                    </div>

                    {{-- Row 3: Catatan --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea class="form-control form-control-sm" name="notes" rows="2"
                                placeholder="Catatan penerimaan (opsional)..."></textarea>
                        </div>
                    </div>

                    {{-- Tabel Item (diisi dari AJAX setelah pilih PO) --}}
                    <div id="itemsSection" class="d-none">
                        <hr>
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-list-check me-1 text-warning"></i>
                            Item yang Diterima
                            <small class="text-muted fw-normal ms-2" style="font-size:11px;">
                                Isi qty_received sesuai barang yang datang. Kosongkan jika item tidak datang.
                            </small>
                        </h6>
                        <div id="itemsContainer">
                            {{-- Diisi oleh JavaScript setelah PO dipilih --}}
                        </div>
                    </div>

                    {{-- Loading state --}}
                    <div id="loadingItems" class="text-center py-4 d-none">
                        <div class="spinner-border spinner-border-sm text-warning me-2"></div>
                        Memuat item PO...
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning text-white px-4" id="btnSimpanGR">
                    <i class="bi bi-save me-1"></i> Simpan GR (Draft)
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: DETAIL GR --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetailGR" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailGRTitle">Detail Goods Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailGRBody">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-warning"></div>
                </div>
            </div>
        </div>
    </div>
</div>



{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: QC (Quality Control) --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalQC" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-clipboard-check me-2"></i> Quality Control
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="qcGrId">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">QC Status <span class="text-danger">*</span></label>
                    <select id="qcStatus" class="form-select form-select-sm">
                        <option value="PENDING">PENDING</option>
                        <option value="PASSED">PASSED</option>
                        <option value="PARTIAL_REJECTED">PARTIAL REJECTED</option>
                        <option value="REJECTED">REJECTED</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Catatan QC</label>
                    <textarea id="qcNotes" class="form-control form-control-sm" rows="3"
                        placeholder="Catatan hasil QC (opsional)..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning text-white btn-sm px-4" id="btnSimpanQC">
                    <i class="bi bi-save me-1"></i> Simpan QC
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {

        // ───────────────────────────────────────────────
        // DataTable init
        // ───────────────────────────────────────────────
        if ($.fn.DataTable.isDataTable('#grTable')) $('#grTable').DataTable().destroy();
        $('#grTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Receipts...",
                lengthMenu: "_MENU_",
            },
            columnDefs: [{
                targets: [0, 6],
                orderable: false
            }]
        });

        $('#checkAllGR').click(function() {
            $('.form-check-input').prop('checked', this.checked);
        });

        // ───────────────────────────────────────────────
        // Saat PO dipilih → load item via AJAX
        // ───────────────────────────────────────────────
        $('#selectPO').on('change', function() {
            const poId = $(this).val();
            const selectedOption = $(this).find('option:selected');

            // Reset supplier display
            $('#displaySupplier').val('');
            $('#hiddenSupplierId').val('');
            $('#itemsSection').addClass('d-none');
            $('#itemsContainer').empty();

            if (!poId) return;

            // Isi supplier dari data-attribute option
            $('#displaySupplier').val(selectedOption.data('supplier'));
            $('#hiddenSupplierId').val(selectedOption.data('supplier-id'));

            // Load item via AJAX
            $('#loadingItems').removeClass('d-none');

            $.ajax({
                url: '/scm/goods-receipt/po-details/' + poId,
                method: 'GET',
                success: function(res) {
                    $('#loadingItems').addClass('d-none');

                    if (res.status !== 'success') {
                        Swal.fire('Error', res.message, 'error');
                        return;
                    }

                    let html = '';
                    res.items.forEach(function(item, index) {
                        const remaining = item.qty_remaining > 0 ? item.qty_remaining : item.po_qty;
                        html += `
                    <div class="item-row mb-3">
                        <input type="hidden" name="items[${index}][purchase_order_detail_id]" value="${item.pod_id}">
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${index}][unit_id]" value="${item.unit_id}">
                        <input type="hidden" name="items[${index}][qty_ordered]" value="${item.po_qty}">
                        <input type="hidden" name="items[${index}][price]" value="${item.price}">

                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">${item.nama_bahan}</label>
                                <div class="text-muted" style="font-size:11px;">
                                    Satuan: ${item.nama_unit || '-'} |
                                    PO: ${item.po_qty} |
                                    Sudah terima: ${item.qty_already_received}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Qty Diterima <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm"
                                    name="items[${index}][qty_received]"
                                    placeholder="0" min="0" max="${remaining}" step="0.01"
                                    value="${remaining}">
                                <small class="text-muted" style="font-size:10px;">Maks: ${remaining}</small>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Qty Ditolak</label>
                                <input type="number" class="form-control form-control-sm"
                                    name="items[${index}][qty_rejected]"
                                    placeholder="0" min="0" step="0.01" value="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">No. Batch</label>
                                <input type="text" class="form-control form-control-sm"
                                    name="items[${index}][batch_number]" placeholder="Opsional">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Tgl Expired</label>
                                <input type="date" class="form-control form-control-sm"
                                    name="items[${index}][expiry_date]">
                            </div>
                            <div class="col-md-1">
                                <label class="form-label small mb-1">Harga</label>
                                <input type="number" class="form-control form-control-sm bg-light"
                                    name="items[${index}][price_display]"
                                    value="${item.price}" readonly>
                            </div>
                        </div>
                    </div>`;
                    });

                    $('#itemsContainer').html(html);
                    $('#itemsSection').removeClass('d-none');
                },
                error: function() {
                    $('#loadingItems').addClass('d-none');
                    Swal.fire('Error', 'Gagal memuat item PO.', 'error');
                }
            });
        });

        // ───────────────────────────────────────────────
        // Simpan GR (AJAX POST)
        // ───────────────────────────────────────────────
        $('#btnSimpanGR').on('click', function() {
            const formData = new FormData(document.getElementById('formCreateGR'));

            // Tambahkan warehouse_id dari hidden field
            formData.set('warehouse_id', $('#hiddenWarehouseId').val());

            // Validasi dasar di sisi client
            if (!formData.get('purchase_order_id')) {
                Swal.fire('Perhatian', 'Pilih Purchase Order terlebih dahulu.', 'warning');
                return;
            }

            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: '/scm/goods-receipt',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('#btnSimpanGR').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan GR (Draft)');

                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'GR Berhasil Dibuat!',
                            html: `<b>${res.gr_number}</b> berhasil disimpan sebagai <b>DRAFT</b>.<br>
                               Klik <b>Konfirmasi & Update Stok</b> di tabel untuk memperbarui stok.`,
                            confirmButtonColor: '#ffab00',
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    $('#btnSimpanGR').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan GR (Draft)');
                    const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan server.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });

        // ───────────────────────────────────────────────
        // Konfirmasi GR → update stok
        // ───────────────────────────────────────────────
        $(document).on('click', '.btnConfirmGR', function() {
            const grId = $(this).data('id');
            const grNumber = $(this).data('gr');

            Swal.fire({
                title: `Konfirmasi ${grNumber}?`,
                html: `Stok akan <b>langsung bertambah</b> setelah dikonfirmasi.<br>
                   Pastikan semua qty sudah benar sebelum melanjutkan.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Konfirmasi & Update Stok',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: `/scm/goods-receipt/${grId}/confirm`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                    },
                    success: function(res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Stok Berhasil Diperbarui!',
                                text: res.message,
                                confirmButtonColor: '#ffab00',
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan server.';
                        Swal.fire('Error', msg, 'error');
                    }
                });
            });
        });

        // ───────────────────────────────────────────────
        // Lihat Detail GR
        // ───────────────────────────────────────────────
        $(document).on('click', '.btnDetailGR', function(e) {
            e.preventDefault();
            const grId = $(this).data('id');
            $('#detailGRBody').html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-warning"></div></div>');
            $('#modalDetailGR').modal('show');

            $.ajax({
                url: `/scm/goods-receipt/${grId}`,
                method: 'GET',
                success: function(res) {
                    if (res.status !== 'success') {
                        $('#detailGRBody').html('<p class="text-danger">Gagal memuat detail.</p>');
                        return;
                    }

                    const gr = res.gr;
                    const details = res.details;

                    $('#detailGRTitle').text(`Detail GR: ${gr.gr_number}`);

                    let itemRows = '';
                    details.forEach(d => {
                        itemRows += `
                    <tr>
                        <td>${d.nama_bahan}</td>
                        <td>${d.nama_unit || '-'}</td>
                        <td class="text-end">${d.qty_ordered}</td>
                        <td class="text-end text-success fw-bold">${d.qty_received}</td>
                        <td class="text-end text-danger">${d.qty_rejected}</td>
                        <td>${d.batch_number || '-'}</td>
                        <td>${d.expiry_date ? new Date(d.expiry_date).toLocaleDateString('id-ID') : '-'}</td>
                    </tr>`;
                    });

                    $('#detailGRBody').html(`
                    <div class="row g-2 mb-3">
                        <div class="col-6"><small class="text-muted">GR Number</small><div class="fw-bold text-warning">${gr.gr_number}</div></div>
                        <div class="col-6"><small class="text-muted">PO Number</small><div class="fw-bold">${gr.po_number}</div></div>
                        <div class="col-6"><small class="text-muted">Supplier</small><div>${gr.supplier_name}</div></div>
                        <div class="col-6"><small class="text-muted">Driver</small><div>${gr.driver_name || '-'}</div></div>
                        <div class="col-6"><small class="text-muted">Tanggal Terima</small><div>${gr.receipt_date}</div></div>
                        <div class="col-6"><small class="text-muted">Status</small><div><span class="badge bg-warning text-white">${gr.status}</span></div></div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Item Detail</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Bahan</th><th>Satuan</th>
                                    <th class="text-end">Qty PO</th>
                                    <th class="text-end">Qty Diterima</th>
                                    <th class="text-end">Qty Ditolak</th>
                                    <th>Batch</th><th>Expired</th>
                                </tr>
                            </thead>
                            <tbody>${itemRows}</tbody>
                        </table>
                    </div>
                `);
                },
                error: function() {
                    $('#detailGRBody').html('<p class="text-danger">Gagal memuat detail GR.</p>');
                }
            });
        });


        // ───────────────────────────────────────────────
        // QC Button → open modal
        // ───────────────────────────────────────────────
        $(document).on('click', '.btnQC', function(e) {
            e.preventDefault();
            const grId  = $(this).data('id');
            const grQc  = $(this).data('qc');

            $('#qcGrId').val(grId);
            $('#qcStatus').val(grQc || 'PENDING');
            $('#qcNotes').val('');
            $('#modalQC').modal('show');
        });

        // ───────────────────────────────────────────────
        // Simpan QC
        // ───────────────────────────────────────────────
        $('#btnSimpanQC').on('click', function() {
            if ($(this).prop('disabled')) return;

            const grId     = $('#qcGrId').val();
            const qcStatus = $('#qcStatus').val();
            const qcNotes  = $('#qcNotes').val();

            if (!grId) return;

            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: `/scm/goods-receipt/${grId}/qc`,
                method: 'POST',
                data: {
                    qc_status : qcStatus,
                    qc_notes  : qcNotes,
                    _token    : $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(res) {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan QC');
                    if (res.status === 'success') {
                        $('#modalQC').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'QC Diperbarui!',
                            text: res.message,
                            confirmButtonColor: '#ffab00',
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan QC');
                    Swal.fire('Error', xhr.responseJSON?.message ?? 'Gagal menyimpan QC.', 'error');
                }
            });
        });

    });
</script>
@endpush

@include('Temp.Investor.footer')
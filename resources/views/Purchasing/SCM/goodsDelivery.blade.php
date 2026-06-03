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

    #gdTable thead th {
        background-color: #f8f9fa !important;
        padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }

    #gdTable tbody td {
        padding: 1.2rem 25px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
    }

    #gdTable tbody tr {
        transition: all 0.2s;
    }

    #gdTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }

    .bg-transit-subtle {
        background-color: #fff4e5 !important;
        color: #ffab00 !important;
        border: 1px solid #ffe5c4 !important;
    }

    .bg-delivery-subtle {
        background-color: #f0f0ff !important;
        color: #696cff !important;
        border: 1px solid #e1e1ff !important;
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

            {{-- ═══════════════════ HEADER ═══════════════════ --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Goods Delivery List</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Logistics</a></li>
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Goods Delivery</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary d-flex align-items-center">
                                    <i class="bi bi-printer me-1"></i> Print Batch
                                </button>
                                <button type="button" class="btn btn-primary px-3 shadow-sm d-flex align-items-center"
                                    data-bs-toggle="modal" data-bs-target="#modalCreateGD">
                                    <i class="bi bi-truck me-1"></i> Create Delivery
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ WIDGET RINGKASAN ═══════════════════ --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-dark">{{ $summary['total'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Total GD</div>
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
                        <div class="fw-bold fs-4 text-warning">{{ $summary['in_transit'] }}</div>
                        <div class="text-muted" style="font-size:11px;">In Transit</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-success">{{ $summary['delivered'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Delivered</div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════ TABEL ═══════════════════ --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-box-seam me-2 text-primary"></i> Shipment Transactions
                    </h6>
                </div>
                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="gdTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small fw-bolder text-secondary">
                                    <th class="text-center" style="width:50px;">
                                        <input type="checkbox" id="checkAllGD" class="form-check-input">
                                    </th>
                                    <th>Delivery Info</th>
                                    <th>Recipient / Destination</th>
                                    <th>Items</th>
                                    <th>Reference SO</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width:80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @forelse($goodsDeliveries as $gd)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input">
                                    </td>

                                    {{-- Delivery Info --}}
                                    <td class="px-3">
                                        <div class="fw-bold text-primary" style="font-size:0.85rem;">
                                            {{ $gd->gd_number }}
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($gd->delivery_date)->format('d M Y') }}
                                        </small>
                                        @if($gd->driver_name)
                                        <br><small class="text-muted" style="font-size:10px;">
                                            Driver: {{ $gd->driver_name }}
                                            @if($gd->vehicle_plate) · {{ $gd->vehicle_plate }} @endif
                                        </small>
                                        @endif
                                    </td>

                                    {{-- Recipient --}}
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light text-primary me-2" style="width:25px;height:25px;font-size:12px;">
                                                <i class="bi bi-geo-alt"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium small">{{ $gd->customer_name }}</div>
                                                @if($gd->delivery_address)
                                                <small class="text-muted" style="font-size:10px;">
                                                    {{ Str::limit($gd->delivery_address, 40) }}
                                                </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Items count --}}
                                    <td class="px-3 text-center">
                                        <span class="badge rounded-pill bg-light text-dark border fw-normal">
                                            {{ $gd->item_count }} Item{{ $gd->item_count > 1 ? 's' : '' }}
                                        </span>
                                    </td>

                                    {{-- Reference SO --}}
                                    <td class="px-3">
                                        <small class="fw-bold text-muted">{{ $gd->so_number }}</small>
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @php
                                        $statusMap = [
                                        'DRAFT' => ['class' => 'secondary', 'label' => 'DRAFT'],
                                        'IN_TRANSIT' => ['class' => 'transit', 'label' => 'IN TRANSIT'],
                                        'DELIVERED' => ['class' => 'success', 'label' => 'DELIVERED'],
                                        'CANCELLED' => ['class' => 'danger', 'label' => 'CANCELLED'],
                                        ];
                                        $s = $statusMap[$gd->status] ?? ['class' => 'secondary', 'label' => $gd->status];
                                        @endphp
                                        @if($gd->status === 'IN_TRANSIT')
                                        <span class="badge bg-transit-subtle border px-3 py-2 fw-bold" style="font-size:0.7rem;border-radius:8px;">
                                            IN TRANSIT
                                        </span>
                                        @elseif($gd->status === 'DELIVERED')
                                        <span class="badge bg-success-subtle text-success border px-3 py-2 fw-bold" style="font-size:0.7rem;border-radius:8px;">
                                            DELIVERED
                                        </span>
                                        @else
                                        <span class="badge bg-{{ $s['class'] }}-subtle text-{{ $s['class'] }} border px-3 py-2 fw-bold" style="font-size:0.7rem;border-radius:8px;">
                                            {{ $s['label'] }}
                                        </span>
                                        @endif
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
                                                    <a class="dropdown-item rounded-2 btnDetailGD" href="#"
                                                        data-id="{{ $gd->id }}">
                                                        <i class="bi bi-eye text-info me-2"></i> Lihat Detail
                                                    </a>
                                                </li>

                                                {{-- Dispatch (DRAFT → IN_TRANSIT) --}}
                                                @if($gd->status === 'DRAFT')
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnDispatch" href="#"
                                                        data-id="{{ $gd->id }}"
                                                        data-gd="{{ $gd->gd_number }}">
                                                        <i class="bi bi-truck text-primary me-2"></i> Dispatch (Berangkat)
                                                    </a>
                                                </li>
                                                @endif

                                                {{-- Mark as Delivered (DRAFT atau IN_TRANSIT) --}}
                                                @if(in_array($gd->status, ['DRAFT', 'IN_TRANSIT']))
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnDeliver" href="#"
                                                        data-id="{{ $gd->id }}"
                                                        data-gd="{{ $gd->gd_number }}">
                                                        <i class="bi bi-check2-all text-success me-2"></i> Mark as Delivered
                                                    </a>
                                                </li>
                                                @endif

                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>

                                                {{-- Buat Sales Invoice (hanya jika sudah DELIVERED) --}}
                                                @if($gd->status === 'DELIVERED')
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-primary" href="#">
                                                        <i class="bi bi-file-earmark-text me-2"></i> Buat Sales Invoice
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
                                        <i class="bi bi-truck fs-4 d-block mb-2"></i>
                                        Belum ada Goods Delivery. Klik "Create Delivery" untuk mulai.
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
{{-- MODAL: CREATE GD --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCreateGD" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-truck me-2"></i> Create Goods Delivery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCreateGD">
                    @csrf

                    {{-- Row 1: Pilih SO + Tanggal --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Sales Order <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="selectSO" name="sales_order_id" required>
                                <option value="">-- Pilih SO yang akan dikirim --</option>
                                @foreach($readySOs as $so)
                                <option value="{{ $so->id }}"
                                    data-customer="{{ $so->customer_name }}"
                                    data-customer-id="{{ $so->customer_id }}"
                                    data-address="{{ $so->address }}">
                                    {{ $so->so_number }} — {{ $so->customer_name }}
                                    (Due: {{ \Carbon\Carbon::parse($so->required_date)->format('d M Y') }})
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hanya SO berstatus NEW/APPROVED yang tampil</small>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold">Link ke Outlet PO <span class="text-muted fw-normal">(opsional)</span></label>
                            <select name="outlet_po_id" id="selectOutletPO" class="form-select form-select-sm shadow-none">
                                <option value="">-- Tanpa link outlet PO --</option>
                                @foreach($outletPOs as $opo)
                                <option value="{{ $opo->id }}" data-nopo="{{ $opo->no_po }}">
                                    {{ $opo->no_po }} — {{ $opo->nama_outlet }} ({{ $opo->status }})
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih jika GD ini untuk menyelesaikan PO outlet tertentu</small>
                        </div>

                        <div class="col-md-6">
                            <label class="small fw-bold">Link ke Surat Jalan <span class="text-muted fw-normal">(opsional)</span></label>
                            <select name="sj_id" id="selectSJ" class="form-select form-select-sm shadow-none">
                                <option value="">-- Tanpa surat jalan --</option>
                                @foreach($suratJalans as $sj)
                                <option value="{{ $sj->id }}">
                                    {{ $sj->no_sj }} — {{ $sj->driver_name ?? '-' }} ({{ $sj->armada_nopol ?? '-' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Kirim <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="delivery_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Estimasi Tiba</label>
                            <input type="date" class="form-control form-control-sm" name="estimated_arrival">
                        </div>
                    </div>

                    {{-- Row 2: Driver, Plat, Customer --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Customer / Outlet</label>
                            <input type="text" class="form-control form-control-sm bg-light" id="displayCustomer" readonly placeholder="Otomatis dari SO">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Alamat Pengiriman</label>
                            <input type="text" class="form-control form-control-sm" name="delivery_address" id="inputDeliveryAddress" placeholder="Alamat tujuan">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nama Driver</label>
                            <input type="text" class="form-control form-control-sm" name="driver_name" placeholder="Nama driver">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Plat Kendaraan</label>
                            <input type="text" class="form-control form-control-sm" name="vehicle_plate" placeholder="B 1234 ABC">
                        </div>
                    </div>

                    {{-- Row 3: Catatan --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea class="form-control form-control-sm" name="notes" rows="2"
                            placeholder="Catatan pengiriman (opsional)..."></textarea>
                    </div>

                    {{-- Tabel Item --}}
                    <div id="itemsSection" class="d-none">
                        <hr>
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-list-check me-1 text-primary"></i>
                            Item yang Dikirim
                            <small class="text-muted fw-normal ms-2" style="font-size:11px;">
                                Isi qty sesuai barang yang akan dikirim. Kosongkan jika tidak ikut dikirim.
                            </small>
                        </h6>

                        {{-- Peringatan stok --}}
                        <div class="alert alert-info alert-sm py-2 px-3 mb-3" style="font-size:12px;">
                            <i class="bi bi-info-circle me-1"></i>
                            Stok akan <strong>dikurangi</strong> saat kamu klik <strong>"Mark as Delivered"</strong>, bukan saat menyimpan GD ini.
                        </div>

                        <div id="itemsContainer"></div>
                    </div>

                    <div id="loadingItems" class="text-center py-4 d-none">
                        <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                        Memuat item SO...
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary px-4" id="btnSimpanGD">
                    <i class="bi bi-save me-1"></i> Simpan GD (Draft)
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: DETAIL GD --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetailGD" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailGDTitle">Detail Goods Delivery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailGDBody">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
    $(document).ready(function() {

        // ─── DataTable ───────────────────────────────────────────────
        if ($.fn.DataTable.isDataTable('#gdTable')) $('#gdTable').DataTable().destroy();
        $('#gdTable').DataTable({
            responsive: true,
            autoWidth: false,
            dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search Shipment...",
                lengthMenu: "_MENU_",
            },
            columnDefs: [{
                targets: [0, 6],
                orderable: false
            }]
        });

        $('#checkAllGD').click(function() {
            $('.form-check-input').prop('checked', this.checked);
        });

        // ─── Pilih SO → Load Item via AJAX ─────────────────────────
        $('#selectSO').on('change', function() {
            const soId = $(this).val();
            const opt = $(this).find('option:selected');

            $('#displayCustomer').val('');
            $('#inputDeliveryAddress').val('');
            $('#itemsSection').addClass('d-none');
            $('#itemsContainer').empty();

            if (!soId) return;

            $('#displayCustomer').val(opt.data('customer'));
            $('#inputDeliveryAddress').val(opt.data('address') || '');
            $('#loadingItems').removeClass('d-none');

            $.ajax({
                url: '/scm/goods-delivery/so-details/' + soId,
                method: 'GET',
                success: function(res) {
                    $('#loadingItems').addClass('d-none');

                    if (res.status !== 'success') {
                        Swal.fire('Error', res.message, 'error');
                        return;
                    }

                    let html = '';
                    res.items.forEach(function(item, index) {
                        const remaining = item.qty_remaining > 0 ? item.qty_remaining : item.qty;
                        html += `
                    <div class="item-row mb-3">
                        <input type="hidden" name="items[${index}][sales_order_detail_id]" value="${item.sod_id}">
                        <input type="hidden" name="items[${index}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${index}][unit_id]" value="${item.unit_id}">
                        <input type="hidden" name="items[${index}][qty_ordered]" value="${item.qty}">
                        <input type="hidden" name="items[${index}][price]" value="${item.price}">

                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold mb-1">${item.nama_bahan}</label>
                                <div class="text-muted" style="font-size:11px;">
                                    Satuan: ${item.nama_unit || '-'} |
                                    SO: ${item.qty} |
                                    Sudah kirim: ${item.qty_already_delivered}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Qty Dikirim <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm"
                                    name="items[${index}][qty_delivered]"
                                    placeholder="0" min="0" max="${remaining}" step="0.01"
                                    value="${remaining}">
                                <small class="text-muted" style="font-size:10px;">Maks: ${remaining}</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Catatan Item</label>
                                <input type="text" class="form-control form-control-sm"
                                    name="items[${index}][notes]" placeholder="Opsional">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Harga</label>
                                <input type="number" class="form-control form-control-sm bg-light"
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
                    Swal.fire('Error', 'Gagal memuat item SO.', 'error');
                }
            });
        });

        // ─── Simpan GD ───────────────────────────────────────────────
        $('#btnSimpanGD').on('click', function() {
            if (!$('#selectSO').val()) {
                Swal.fire('Perhatian', 'Pilih Sales Order terlebih dahulu.', 'warning');
                return;
            }

            const formData = new FormData(document.getElementById('formCreateGD'));
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: '/scm/goods-delivery',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    $('#btnSimpanGD').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan GD (Draft)');
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'GD Berhasil Dibuat!',
                            html: `<b>${res.gd_number}</b> disimpan sebagai <b>DRAFT</b>.<br>
                               Klik <b>Dispatch</b> untuk berangkatkan, atau<br>
                               <b>Mark as Delivered</b> langsung jika sudah sampai.`,
                            confirmButtonColor: '#696cff',
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    $('#btnSimpanGD').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan GD (Draft)');
                    Swal.fire('Error', xhr.responseJSON?.message ?? 'Terjadi kesalahan.', 'error');
                }
            });
        });

        // ─── Dispatch (DRAFT → IN_TRANSIT) ───────────────────────────
        $(document).on('click', '.btnDispatch', function() {
            const gdId = $(this).data('id');
            const gdNumber = $(this).data('gd');

            Swal.fire({
                title: `Dispatch ${gdNumber}?`,
                text: 'Status akan berubah ke IN_TRANSIT. Stok belum berubah.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Berangkatkan!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#696cff',
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: `/scm/goods-delivery/${gdId}/dispatch`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                    },
                    success: res => {
                        if (res.status === 'success') {
                            Swal.fire({
                                    icon: 'success',
                                    title: 'Dispatched!',
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

        // ─── Mark as Delivered → Stock OUT ───────────────────────────
        $(document).on('click', '.btnDeliver', function() {
            const gdId = $(this).data('id');
            const gdNumber = $(this).data('gd');

            Swal.fire({
                title: `Konfirmasi Delivered: ${gdNumber}?`,
                html: `Stok akan <b>langsung berkurang</b> setelah dikonfirmasi.<br>
                   Pastikan barang sudah benar-benar diterima outlet.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Konfirmasi Delivered!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#28a745',
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: `/scm/goods-delivery/${gdId}/deliver`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                    },
                    success: res => {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Delivered!',
                                text: res.message,
                                confirmButtonColor: '#28a745',
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: xhr => Swal.fire('Error', xhr.responseJSON?.message ?? 'Server error.', 'error')
                });
            });
        });

        // ─── Lihat Detail GD ─────────────────────────────────────────
        $(document).on('click', '.btnDetailGD', function(e) {
            e.preventDefault();
            const gdId = $(this).data('id');
            $('#detailGDBody').html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>');
            $('#modalDetailGD').modal('show');

            $.ajax({
                url: `/scm/goods-delivery/${gdId}`,
                method: 'GET',
                success: function(res) {
                    if (res.status !== 'success') {
                        $('#detailGDBody').html('<p class="text-danger">Gagal memuat detail.</p>');
                        return;
                    }
                    const gd = res.gd;
                    const details = res.details;
                    $('#detailGDTitle').text(`Detail GD: ${gd.gd_number}`);

                    let rows = '';
                    details.forEach(d => {
                        rows += `
                    <tr>
                        <td>${d.nama_bahan}</td>
                        <td>${d.nama_unit || '-'}</td>
                        <td class="text-end">${d.qty_ordered}</td>
                        <td class="text-end text-primary fw-bold">${d.qty_delivered}</td>
                        <td class="text-end">${new Intl.NumberFormat('id-ID').format(d.subtotal)}</td>
                    </tr>`;
                    });

                    $('#detailGDBody').html(`
                    <div class="row g-2 mb-3">
                        <div class="col-6"><small class="text-muted">GD Number</small><div class="fw-bold text-primary">${gd.gd_number}</div></div>
                        <div class="col-6"><small class="text-muted">SO Number</small><div class="fw-bold">${gd.so_number}</div></div>
                        <div class="col-6"><small class="text-muted">Customer</small><div>${gd.customer_name}</div></div>
                        <div class="col-6"><small class="text-muted">Driver</small><div>${gd.driver_name || '-'} ${gd.vehicle_plate ? '· '+gd.vehicle_plate : ''}</div></div>
                        <div class="col-6"><small class="text-muted">Tgl Kirim</small><div>${gd.delivery_date}</div></div>
                        <div class="col-6"><small class="text-muted">Status</small>
                            <div><span class="badge bg-primary">${gd.status}</span></div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="fw-bold mb-2">Item Detail</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Bahan</th><th>Satuan</th>
                                    <th class="text-end">Qty SO</th>
                                    <th class="text-end">Qty Dikirim</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                `);
                },
                error: () => $('#detailGDBody').html('<p class="text-danger">Gagal memuat detail GD.</p>')
            });
        });

    });
</script>
@endpush

@include('Temp.Investor.footer')
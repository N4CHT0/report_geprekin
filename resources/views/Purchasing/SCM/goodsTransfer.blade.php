@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { padding: 20px 25px !important; }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { padding: 20px 25px !important; }
    table.dataTable { width: 100% !important; margin: 0 !important; border-collapse: collapse !important; }
    #gtTable thead th {
        background-color: #f8f9fa !important; padding: 15px 25px !important;
        border-bottom: 1px solid #f1f4f8 !important; font-size: 11px;
        font-weight: 700; text-transform: uppercase;
    }
    #gtTable tbody td { padding: 1.2rem 25px !important; vertical-align: middle !important; border-bottom: 1px solid #f8f9fa !important; }
    #gtTable tbody tr { transition: all 0.2s; }
    #gtTable tbody tr:hover { background-color: rgba(113,221,55,0.04) !important; box-shadow: inset 4px 0 0 #71dd37; }
    .bg-transit-subtle  { background-color: #fff4e5 !important; color: #ffab00 !important; border: 1px solid #ffe5c4 !important; }
    .bg-complete-subtle { background-color: #e8fadf !important; color: #71dd37 !important; border: 1px solid #d4f5c3 !important; }
    .icon-shape { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; }
    .item-row { border: 1px solid #f1f4f8; border-radius: 8px; padding: 12px; margin-bottom: 10px; background: #fafafa; }
    .stok-badge { font-size: 10px; padding: 2px 8px; border-radius: 4px; }
    .stok-ok      { background: #e8fadf; color: #3a9c1a; border: 1px solid #c3e9ad; }
    .stok-warning { background: #fff4e5; color: #b37a00; border: 1px solid #ffd98a; }
    .stok-empty   { background: #ffe5e5; color: #cc2900; border: 1px solid #ffb3b3; }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            {{-- ═══ HEADER ═══ --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Goods Transfer</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/stock-control" class="text-decoration-none text-muted">Stock Control</a></li>
                                    <li class="breadcrumb-item active text-success" aria-current="page">Goods Transfer</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <button type="button" class="btn btn-success px-3 shadow-sm d-flex align-items-center"
                                data-bs-toggle="modal" data-bs-target="#modalCreateGT">
                                <i class="bi bi-arrow-left-right me-1"></i> Transfer Barang
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ SUMMARY WIDGETS ═══ --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm text-center p-3" style="border-radius:12px;">
                        <div class="fw-bold fs-4 text-dark">{{ $summary['total'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Total Transfer</div>
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
                        <div class="fw-bold fs-4 text-success">{{ $summary['completed'] }}</div>
                        <div class="text-muted" style="font-size:11px;">Completed</div>
                    </div>
                </div>
            </div>

            {{-- ═══ TABLE ═══ --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-arrow-left-right me-2 text-success"></i> Riwayat Transfer Antar Gudang
                    </h6>
                </div>
                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="gtTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small fw-bolder text-secondary">
                                    <th class="text-center" style="width:50px;">
                                        <input type="checkbox" id="checkAll" class="form-check-input">
                                    </th>
                                    <th>Nomor Transfer</th>
                                    <th>Asal → Tujuan</th>
                                    <th class="text-center">Items</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @forelse($transfers as $gt)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input sub-chk">
                                    </td>

                                    {{-- Nomor Transfer --}}
                                    <td>
                                        <div class="fw-bold text-success" style="font-size:0.85rem;">
                                            {{ $gt->gt_number }}
                                        </div>
                                        @if($gt->driver_name)
                                            <small class="text-muted" style="font-size:10px;">
                                                <i class="bi bi-person me-1"></i>{{ $gt->driver_name }}
                                                @if($gt->vehicle_plate) · {{ $gt->vehicle_plate }} @endif
                                            </small>
                                        @endif
                                    </td>

                                    {{-- Rute --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="icon-shape bg-light text-success" style="width:28px;height:28px;font-size:11px;">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <div>
                                                <div class="small fw-medium">{{ $gt->send_from }}</div>
                                                <div style="font-size:10px;" class="text-muted">
                                                    <i class="bi bi-arrow-down text-success"></i>
                                                    {{ $gt->send_to }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Item count --}}
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-light text-dark border">
                                            {{ $gt->item_count }} item
                                        </span>
                                    </td>

                                    {{-- Tanggal --}}
                                    <td>
                                        <small class="fw-bold text-muted">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            {{ \Carbon\Carbon::parse($gt->transfer_date)->format('d M Y') }}
                                        </small>
                                        @if($gt->actual_arrival)
                                            <br><small class="text-success" style="font-size:10px;">
                                                Tiba: {{ \Carbon\Carbon::parse($gt->actual_arrival)->format('d M Y') }}
                                            </small>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @php
                                            $statusMap = [
                                                'DRAFT'      => 'bg-secondary-subtle text-secondary border',
                                                'IN_TRANSIT' => 'bg-transit-subtle border',
                                                'COMPLETED'  => 'bg-complete-subtle border',
                                                'CANCELLED'  => 'bg-danger-subtle text-danger border',
                                            ];
                                            $cls = $statusMap[$gt->status] ?? 'bg-secondary-subtle border';
                                        @endphp
                                        <span class="badge {{ $cls }} px-3 py-2 fw-bold" style="font-size:0.7rem; border-radius:8px;">
                                            {{ $gt->status }}
                                        </span>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">

                                                <li>
                                                    <a class="dropdown-item rounded-2 btnDetailGT" href="#"
                                                        data-id="{{ $gt->id }}">
                                                        <i class="bi bi-eye text-info me-2"></i> Lihat Detail
                                                    </a>
                                                </li>

                                                @if($gt->status === 'DRAFT')
                                                <li>
                                                    <a class="dropdown-item rounded-2 btnDispatchGT" href="#"
                                                        data-id="{{ $gt->id }}"
                                                        data-gt="{{ $gt->gt_number }}">
                                                        <i class="bi bi-truck text-warning me-2"></i> Dispatch (Kirim Barang)
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-danger btnCancelGT" href="#"
                                                        data-id="{{ $gt->id }}"
                                                        data-gt="{{ $gt->gt_number }}">
                                                        <i class="bi bi-x-circle me-2"></i> Batalkan
                                                    </a>
                                                </li>
                                                @endif

                                                @if($gt->status === 'IN_TRANSIT')
                                                <li>
                                                    <a class="dropdown-item rounded-2 text-success btnCompleteGT" href="#"
                                                        data-id="{{ $gt->id }}"
                                                        data-gt="{{ $gt->gt_number }}">
                                                        <i class="bi bi-check2-all me-2"></i> Konfirmasi Diterima
                                                    </a>
                                                </li>
                                                @endif

                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-arrow-left-right fs-4 d-block mb-2 text-light"></i>
                                        Belum ada transfer. Klik <b>Transfer Barang</b> untuk mulai.
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
{{-- MODAL: CREATE GOODS TRANSFER --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCreateGT" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-arrow-left-right me-2"></i> Transfer Barang Antar Gudang
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCreateGT">
                    @csrf

                    {{-- Row 1: Warehouse + Tanggal --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Dari Gudang (Asal) <span class="text-danger">*</span></label>
                            <select name="from_warehouse_id" id="selectFromWarehouse" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Gudang Asal --</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->nama_warehouse }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Ke Gudang (Tujuan) <span class="text-danger">*</span></label>
                            <select name="to_warehouse_id" id="selectToWarehouse" class="form-select form-select-sm" required>
                                <option value="">-- Pilih Gudang Tujuan --</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->nama_warehouse }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Tanggal Transfer <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-sm" name="transfer_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold small">Estimasi Tiba</label>
                            <input type="date" class="form-control form-control-sm" name="estimated_arrival">
                        </div>
                    </div>

                    {{-- Row 2: Logistik --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Nama Driver</label>
                            <input type="text" class="form-control form-control-sm" name="driver_name" placeholder="Nama driver pengantar">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Plat Kendaraan</label>
                            <input type="text" class="form-control form-control-sm" name="vehicle_plate" placeholder="B 1234 ABC">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Catatan</label>
                            <input type="text" class="form-control form-control-sm" name="notes" placeholder="Catatan transfer (opsional)">
                        </div>
                    </div>

                    <hr>

                    {{-- Items Section --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-list-check me-1 text-success"></i> Item yang Ditransfer
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-success" id="btnAddItem">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Item
                        </button>
                    </div>

                    {{-- Peringatan stok belum loaded --}}
                    <div id="stockWarning" class="alert alert-info py-2 px-3 mb-3" style="font-size:12px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Pilih <b>Gudang Asal</b> terlebih dahulu agar stok tersedia ditampilkan.
                    </div>

                    <div id="itemsContainer">
                        {{-- Diisi oleh JavaScript --}}
                    </div>

                    <div id="loadingStock" class="text-center py-3 d-none">
                        <div class="spinner-border spinner-border-sm text-success me-2"></div>
                        Memuat stok gudang asal...
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4" id="btnSimpanGT">
                    <i class="bi bi-save me-1"></i> Simpan Transfer (Draft)
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: KONFIRMASI DITERIMA (Complete) --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalCompleteGT" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="completeGTTitle">
                    <i class="bi bi-check2-all me-2"></i> Konfirmasi Penerimaan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 px-3 mb-3" style="font-size:12px;">
                    <i class="bi bi-info-circle me-1"></i>
                    Isi qty yang <b>benar-benar diterima</b>. Stok akan langsung bertambah di gudang tujuan.
                    Qty diterima boleh lebih kecil dari qty dikirim jika ada barang rusak/hilang di jalan.
                </div>
                <input type="hidden" id="completeGtId">
                <form id="formCompleteGT">
                    <div id="completeItemsContainer">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-success"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success px-4" id="btnConfirmComplete">
                    <i class="bi bi-check2-all me-1"></i> Konfirmasi Diterima & Update Stok
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: DETAIL GT --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalDetailGT" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="detailGTTitle">Detail Goods Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailGTBody">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-success"></div>
                </div>
            </div>
        </div>
    </div>
</div>


@push('scripts')
<script>
$(document).ready(function () {

    // ─── DataTable ────────────────────────────────────────────────────────────
    if ($.fn.DataTable.isDataTable('#gtTable')) $('#gtTable').DataTable().destroy();
    $('#gtTable').DataTable({
        responsive: true,
        autoWidth: false,
        dom: '<"d-flex justify-content-between align-items-center"lf>rt<"d-flex justify-content-between align-items-center"ip>',
        language: {
            search: "_INPUT_", searchPlaceholder: "Cari transfer...", lengthMenu: "_MENU_"
        },
        columnDefs: [{ targets: [0, 6], orderable: false }]
    });

    $('#checkAll').click(function () {
        $('.sub-chk').prop('checked', this.checked);
    });

    // ─── State: stok aktual per bahan di gudang asal ────────────────────────
    let warehouseStocks = {};  // { bahan_id: stok_aktual }
    let itemIndex = 0;

    // ─── Load stok saat gudang asal dipilih ────────────────────────────────
    $('#selectFromWarehouse').on('change', function () {
        const whId = $(this).val();
        warehouseStocks = {};

        if (!whId) {
            $('#stockWarning').removeClass('d-none');
            return;
        }

        $('#stockWarning').addClass('d-none');
        $('#loadingStock').removeClass('d-none');

        $.ajax({
            url: `/scm/goods-transfer/stock/${whId}`,
            method: 'GET',
            success: function (res) {
                $('#loadingStock').addClass('d-none');
                if (res.status === 'success') {
                    res.stocks.forEach(s => {
                        warehouseStocks[s.bahan_id] = {
                            stok: parseFloat(s.stok_aktual),
                            unit: s.nama_unit,
                            harga: parseFloat(s.harga_satuan || 0),
                            unit_id: s.unit_id,
                        };
                    });
                    // Refresh stok badge di item rows yang sudah ada
                    refreshStokBadges();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function (xhr) {
                $('#loadingStock').addClass('d-none');
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Gagal memuat stok.', 'error');
            }
        });
    });

    // ─── Tambah Item Row ────────────────────────────────────────────────────
    function addItemRow(data = null) {
        const idx = itemIndex++;
        const options = @json($products).map(p =>
            `<option value="${p.id}"
                data-unit="${p.nama_unit ?? '-'}"
                data-unit-id="${p.unit_id ?? ''}"
                data-harga="${p.harga_satuan ?? 0}"
                ${data && data.bahan_id == p.id ? 'selected' : ''}>
                ${p.nama_bahan}
            </option>`
        ).join('');

        const row = `
        <div class="item-row" id="itemRow_${idx}">
            <input type="hidden" name="items[${idx}][unit_id]" class="item-unit-id" value="${data?.unit_id ?? ''}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Bahan <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm item-bahan" name="items[${idx}][bahan_id]" required>
                        <option value="">-- Pilih Bahan --</option>
                        ${options}
                    </select>
                    <div class="mt-1">
                        <span class="stok-badge item-stok-badge stok-empty">Pilih gudang asal dulu</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Qty <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm item-qty" name="items[${idx}][qty_requested]"
                        placeholder="0" min="0.01" step="0.01" value="${data?.qty ?? ''}" required>
                    <small class="text-muted item-unit-label" style="font-size:10px;">${data?.unit ?? ''}</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold mb-1">Harga/unit</label>
                    <input type="number" class="form-control form-control-sm item-harga" name="items[${idx}][harga_satuan]"
                        placeholder="0" min="0" step="0.01" value="${data?.harga ?? 0}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold mb-1">Catatan</label>
                    <input type="text" class="form-control form-control-sm" name="items[${idx}][notes]" placeholder="Opsional">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Hapus">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
        </div>`;

        $('#itemsContainer').append(row);
        refreshStokBadges();
    }

    function refreshStokBadges() {
        $('#itemsContainer .item-bahan').each(function () {
            const bahanId = $(this).val();
            const $badge  = $(this).closest('.item-row').find('.item-stok-badge');
            if (!bahanId) { $badge.text('Pilih bahan').removeClass('stok-ok stok-warning stok-empty').addClass('stok-empty'); return; }
            const info = warehouseStocks[bahanId];
            if (!info) { $badge.text('Tidak ada stok').removeClass('stok-ok stok-warning').addClass('stok-empty'); return; }
            const stok = info.stok;
            $badge.text(`Stok: ${stok.toLocaleString('id-ID')} ${info.unit}`);
            $badge.removeClass('stok-ok stok-warning stok-empty')
                  .addClass(stok <= 0 ? 'stok-empty' : stok <= 10 ? 'stok-warning' : 'stok-ok');
        });
    }

    // ─── Event: tambah item ────────────────────────────────────────────────
    $('#btnAddItem').on('click', () => addItemRow());
    addItemRow(); // satu baris default

    // ─── Event: hapus item ─────────────────────────────────────────────────
    $(document).on('click', '.btn-remove-item', function () {
        $(this).closest('.item-row').remove();
    });

    // ─── Event: pilih bahan → isi unit, harga, stok badge ─────────────────
    $(document).on('change', '.item-bahan', function () {
        const $row    = $(this).closest('.item-row');
        const opt     = $(this).find('option:selected');
        const bahanId = $(this).val();
        const unit    = opt.data('unit') || '-';
        const unitId  = opt.data('unit-id') || '';
        const harga   = parseFloat(opt.data('harga') || 0);

        $row.find('.item-unit-label').text(unit);
        $row.find('.item-unit-id').val(unitId);
        $row.find('.item-harga').val(harga > 0 ? harga : '');
        refreshStokBadges();
    });

    // ─── Simpan GT ─────────────────────────────────────────────────────────
    $('#btnSimpanGT').on('click', function () {
        if ($(this).prop('disabled')) return;

        if (!$('#selectFromWarehouse').val()) {
            Swal.fire('Perhatian', 'Pilih gudang asal terlebih dahulu.', 'warning'); return;
        }
        if (!$('#selectToWarehouse').val()) {
            Swal.fire('Perhatian', 'Pilih gudang tujuan terlebih dahulu.', 'warning'); return;
        }
        if ($('#selectFromWarehouse').val() === $('#selectToWarehouse').val()) {
            Swal.fire('Perhatian', 'Gudang asal dan tujuan tidak boleh sama.', 'warning'); return;
        }
        if ($('#itemsContainer .item-row').length === 0) {
            Swal.fire('Perhatian', 'Tambahkan minimal satu item.', 'warning'); return;
        }

        const $btn = $(this);
        const formData = new FormData(document.getElementById('formCreateGT'));

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: '/scm/goods-transfer',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Transfer (Draft)');
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Transfer Dibuat!',
                        html: `<b>${res.gt_number}</b> disimpan sebagai DRAFT.<br>
                               Klik <b>Dispatch</b> untuk mulai pengiriman barang.`,
                        confirmButtonColor: '#71dd37',
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Transfer (Draft)');
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Terjadi kesalahan.', 'error');
            }
        });
    });

    // ─── Dispatch (DRAFT → IN_TRANSIT) ─────────────────────────────────────
    $(document).on('click', '.btnDispatchGT', function () {
        const gtId = $(this).data('id');
        const gtNum = $(this).data('gt');

        Swal.fire({
            title: `Dispatch ${gtNum}?`,
            html: `Stok akan <b>langsung berkurang</b> di gudang asal.<br>
                   Pastikan barang sudah benar-benar disiapkan untuk dikirim.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Dispatch!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#ffab00',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/scm/goods-transfer/${gtId}/dispatch`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
                success: res => {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Dispatched!', text: res.message, confirmButtonColor: '#71dd37' })
                            .then(() => location.reload());
                    } else { Swal.fire('Gagal', res.message, 'error'); }
                },
                error: xhr => Swal.fire('Error', xhr.responseJSON?.message ?? 'Server error.', 'error')
            });
        });
    });

    // ─── Complete (IN_TRANSIT → COMPLETED) ─────────────────────────────────
    $(document).on('click', '.btnCompleteGT', function () {
        const gtId  = $(this).data('id');
        const gtNum = $(this).data('gt');

        $('#completeGtId').val(gtId);
        $('#completeGTTitle').html(`<i class="bi bi-check2-all me-2"></i> Konfirmasi Penerimaan: ${gtNum}`);
        $('#completeItemsContainer').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-success"></div></div>');
        $('#modalCompleteGT').modal('show');

        // Load detail items for qty_received input
        $.ajax({
            url: `/scm/goods-transfer/${gtId}`,
            method: 'GET',
            success: function (res) {
                if (res.status !== 'success') {
                    $('#completeItemsContainer').html('<p class="text-danger">Gagal memuat detail.</p>');
                    return;
                }

                let html = '';
                res.details.forEach((d, idx) => {
                    html += `
                    <div class="item-row mb-2">
                        <input type="hidden" name="items[${idx}][detail_id]" value="${d.id}">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-5">
                                <div class="fw-medium small">${d.nama_bahan}</div>
                                <small class="text-muted">${d.nama_unit ?? '-'} · Dikirim: <b>${d.qty_transferred}</b></small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-1">Qty Diterima <span class="text-danger">*</span></label>
                                <input type="number" class="form-control form-control-sm"
                                    name="items[${idx}][qty_received]"
                                    value="${d.qty_transferred}"
                                    min="0" max="${d.qty_transferred}" step="0.01">
                                <small class="text-muted" style="font-size:10px;">Maks: ${d.qty_transferred}</small>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Catatan selisih</label>
                                <input type="text" class="form-control form-control-sm" placeholder="Opsional">
                            </div>
                        </div>
                    </div>`;
                });

                $('#completeItemsContainer').html(html);
            },
            error: () => $('#completeItemsContainer').html('<p class="text-danger">Gagal memuat detail.</p>')
        });
    });

    $('#btnConfirmComplete').on('click', function () {
        if ($(this).prop('disabled')) return;
        const gtId = $('#completeGtId').val();
        const formData = new FormData(document.getElementById('formCompleteGT'));
        const $btn = $(this);

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: `/scm/goods-transfer/${gtId}/complete`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                $btn.prop('disabled', false).html('<i class="bi bi-check2-all me-1"></i> Konfirmasi Diterima & Update Stok');
                if (res.status === 'success') {
                    $('#modalCompleteGT').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Transfer Selesai!', text: res.message, confirmButtonColor: '#71dd37' })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).html('<i class="bi bi-check2-all me-1"></i> Konfirmasi Diterima & Update Stok');
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Terjadi kesalahan.', 'error');
            }
        });
    });

    // ─── Cancel ────────────────────────────────────────────────────────────
    $(document).on('click', '.btnCancelGT', function () {
        const gtId  = $(this).data('id');
        const gtNum = $(this).data('gt');

        Swal.fire({
            title: `Batalkan ${gtNum}?`,
            text: 'Transfer yang dibatalkan tidak bisa diaktifkan kembali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#ff3e1d',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: `/scm/goods-transfer/${gtId}/cancel`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
                success: res => {
                    if (res.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Dibatalkan', text: res.message, confirmButtonColor: '#71dd37' })
                            .then(() => location.reload());
                    } else { Swal.fire('Gagal', res.message, 'error'); }
                },
                error: xhr => Swal.fire('Error', xhr.responseJSON?.message ?? 'Server error.', 'error')
            });
        });
    });

    // ─── Detail GT ─────────────────────────────────────────────────────────
    $(document).on('click', '.btnDetailGT', function (e) {
        e.preventDefault();
        const gtId = $(this).data('id');
        $('#detailGTBody').html('<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-success"></div></div>');
        $('#modalDetailGT').modal('show');

        $.ajax({
            url: `/scm/goods-transfer/${gtId}`,
            method: 'GET',
            success: function (res) {
                if (res.status !== 'success') {
                    $('#detailGTBody').html('<p class="text-danger">Gagal memuat detail.</p>'); return;
                }
                const gt = res.gt;
                const details = res.details;
                const statusMap = {
                    'DRAFT': 'bg-secondary', 'IN_TRANSIT': 'bg-warning text-dark',
                    'COMPLETED': 'bg-success', 'CANCELLED': 'bg-danger'
                };
                $('#detailGTTitle').text(`Detail GT: ${gt.gt_number}`);

                let rows = '';
                details.forEach(d => {
                    rows += `<tr>
                        <td>${d.nama_bahan}</td>
                        <td>${d.nama_unit ?? '-'}</td>
                        <td class="text-end">${parseFloat(d.qty_requested).toLocaleString('id-ID')}</td>
                        <td class="text-end fw-bold text-warning">${parseFloat(d.qty_transferred).toLocaleString('id-ID')}</td>
                        <td class="text-end fw-bold text-success">${parseFloat(d.qty_received).toLocaleString('id-ID')}</td>
                        <td class="text-end">Rp ${Math.round(d.subtotal).toLocaleString('id-ID')}</td>
                    </tr>`;
                });

                $('#detailGTBody').html(`
                    <div class="row g-2 mb-3">
                        <div class="col-6"><small class="text-muted">Nomor Transfer</small>
                            <div class="fw-bold text-success">${gt.gt_number}</div></div>
                        <div class="col-6"><small class="text-muted">Status</small>
                            <div><span class="badge ${statusMap[gt.status] ?? 'bg-secondary'}">${gt.status}</span></div></div>
                        <div class="col-6"><small class="text-muted">Dari Gudang</small>
                            <div>${gt.send_from}</div></div>
                        <div class="col-6"><small class="text-muted">Ke Gudang</small>
                            <div>${gt.send_to}</div></div>
                        <div class="col-4"><small class="text-muted">Tanggal Transfer</small>
                            <div>${gt.transfer_date}</div></div>
                        <div class="col-4"><small class="text-muted">Tiba</small>
                            <div>${gt.actual_arrival ?? '-'}</div></div>
                        <div class="col-4"><small class="text-muted">Driver</small>
                            <div>${gt.driver_name ?? '-'}</div></div>
                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Bahan</th><th>Satuan</th>
                                    <th class="text-end">Qty Diminta</th>
                                    <th class="text-end">Qty Dikirim</th>
                                    <th class="text-end">Qty Diterima</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                `);
            },
            error: () => $('#detailGTBody').html('<p class="text-danger">Gagal memuat detail GT.</p>')
        });
    });

});
</script>
@endpush

@include('Temp.Investor.footer')
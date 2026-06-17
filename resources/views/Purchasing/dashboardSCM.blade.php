{{-- resources/views/purchasing/dashboardOutlet.blade.php --}}
@include('Temp.Investor.header')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Select2 dropdown scroll */
    .select2-container .select2-dropdown {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Datatable Premium Styling */
    table.dataTable td,
    table.dataTable th {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(248, 249, 250, 0.85) !important;
        transition: background-color 0.2s ease;
    }

    /* Kolom text panjang boleh wrap */
    .td-wrap {
        white-space: normal !important;
        word-break: break-word;
        min-width: 220px;
    }

    /* Tombol micro-interaction */
    .btn-action-custom {
        transition: all 0.2s ease-in-out;
    }

    .btn-action-custom:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    /* Custom scrollbar halus untuk container tabel jika diperlukan */
    .table-responsive::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background-color: #e2e8f0;
        border-radius: 4px;
    }

    /* Menghilangkan panah bawaan bootstrap pada dropdown menu tertentu */
    .no-caret::after {
        display: none !important;
    }

    /* Efek hover lembut pada ikon titik tiga */
    .dropdown .btn-link:hover i {
        color: #4f46e5 !important;
        /* Berubah jadi warna tema saat didekati */
        transition: color 0.2s ease;
    }
</style>

<main class="app-main" style="background-color: #f8fafc;">
    <div class="app-content">
        <div class="container-fluid py-4">

            <!-- WELCOME HEADER DECORATION -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1" style="letter-spacing: -0.5px;">
                        Selamat Datang di Dashboard –
                        @if($namaDc)
                            <span style="color: #4f46e5;">{{ $namaDc }}</span>
                        @else
                            <span class="text-secondary">BACKOFFICE SCM</span>
                        @endif
                    </h4>
                    <p class="text-muted small mb-0">
                        <i class="bi bi-calendar3 me-1"></i> Pantau dan kelola aktivitas Purchase Order hari ini.
                    </p>
                </div>
            </div>

            <!-- PREMIUM GRADIENT METRICS CARDS -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card shadow-sm border-0 h-100"
                        style="background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%); border-radius: 16px;">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="rounded-3 p-3 me-3"
                                style="background-color: rgba(79, 70, 229, 0.08); color: #4f46e5;">
                                <i class="bi bi-collection-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1"
                                    style="font-size: 11px; letter-spacing: 0.5px;">Total PO</h6>
                                <h3 class="fw-bold text-dark mb-0" style="font-size: 1.6rem;">{{ $total_po }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card shadow-sm border-0 h-100"
                        style="background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%); border-radius: 16px; border-bottom: 3px solid #d97706 !important;">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="rounded-3 p-3 me-3"
                                style="background-color: rgba(217, 119, 6, 0.08); color: #d97706;">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1"
                                    style="font-size: 11px; letter-spacing: 0.5px;">Waiting</h6>
                                <h3 class="fw-bold text-warning-emphasis mb-0" style="font-size: 1.6rem;">
                                    {{ $menunggu }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card shadow-sm border-0 h-100"
                        style="background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border-radius: 16px; border-bottom: 3px solid #16a34a !important;">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="rounded-3 p-3 me-3"
                                style="background-color: rgba(22, 163, 74, 0.08); color: #16a34a;">
                                <i class="bi bi-check-circle-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1"
                                    style="font-size: 11px; letter-spacing: 0.5px;">Approved</h6>
                                <h3 class="fw-bold text-success-emphasis mb-0" style="font-size: 1.6rem;">
                                    {{ $disetujui }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <div class="card shadow-sm border-0 h-100"
                        style="background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-radius: 16px; border-bottom: 3px solid #dc2626 !important;">
                        <div class="card-body p-4 d-flex align-items-center">
                            <div class="rounded-3 p-3 me-3"
                                style="background-color: rgba(220, 38, 38, 0.08); color: #dc2626;">
                                <i class="bi bi-x-circle-fill fs-4"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small text-uppercase fw-bold mb-1"
                                    style="font-size: 11px; letter-spacing: 0.5px;">Rejected</h6>
                                <h3 class="fw-bold text-danger-emphasis mb-0" style="font-size: 1.6rem;">{{ $ditolak }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MAIN TABLE CARD WITH MODERN CLEANLOOK -->
            <div class="card border-0 shadow-sm rounded-4" style="background-color: #ffffff;">
                <div class="card-body p-4">

                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-light rounded-3 text-secondary me-3">
                                <i class="bi bi-list-task fs-5"></i>
                            </div>
                            <h5 class="fw-bold text-dark mb-0" style="letter-spacing: -0.3px;">Daftar Purchase Order
                            </h5>
                        </div>
                    </div>

                    <!-- TABLE -->
                    <div class="table-responsive" style="min-height:60vh;">
                        <table id="orderTable" class="table table-hover align-middle text-center mb-0"
                            style="border-color: #f1f5f9;">
                            <thead class="table-light sticky-top text-secondary small text-uppercase"
                                style="background-color: #f8fafc;">
                                <tr>
                                    <th class="text-center py-3" style="min-width:20px; font-weight: 600;">No</th>
                                    <th class="text-center py-3" style="min-width:100px; font-weight: 600;">PO Number
                                    </th>
                                    <th class="text-center py-3" style="width:200px; font-weight: 600;">Tanggal
                                        Permintaan</th>
                                    <th class="text-center py-3" style="min-width:100px; font-weight: 600;">Nama Outlet
                                    </th>
                                    <th class="text-center py-3" style="min-width:100px; font-weight: 600;">Status</th>
                                    <th class="text-center py-3" style="min-width:100px; font-weight: 600;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-secondary">
                                @foreach($dataPO as $index => $po)
                                    <tr style="height:56px; border-bottom: 1px solid #f1f5f9;">
                                        <td class="text-center small text-muted">{{ $index + 1 }}</td>
                                        <td class="text-center fw-bold text-dark" style="font-size: 13.5px;">
                                            {{ $po->no_po }}
                                        </td>
                                        <td class="text-center small">{{ $po->tgl_permintaan }}</td>
                                        <td class="text-center text-dark text-start px-3">
                                            SCM-{{ $po->nama_outlet }}-LINK(NEW)</td>
                                        <td class="text-center">
                                            @php
                                                $statusColors = [
                                                    'Waiting' => 'bg-warning-subtle text-warning-emphasis border border-warning',
                                                    'Approved' => 'bg-success-subtle text-success-emphasis border border-success',
                                                    'Rejected' => 'bg-danger-subtle text-danger-emphasis border border-danger',
                                                    'In Transit' => 'bg-primary-subtle text-primary-emphasis border border-primary',
                                                    'Recieved' => 'bg-info-subtle text-info-emphasis border border-info'
                                                ];
                                                $badgeClass = $statusColors[$po->status] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                            @endphp

                                            <span class="badge {{ $badgeClass }} px-3 py-1.5 rounded-pill fw-semibold"
                                                style="font-size: 11px; letter-spacing: 0.2px; --bs-border-opacity: .2;">
                                                {{ $po->status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-link text-secondary p-0 border-0 dropdown-toggle no-caret"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border border-light-subtle rounded-3"
                                                    style="font-size: 13px;">
                                                    <li>
                                                        <button type="button" class="dropdown-item py-2 text-dark"
                                                            data-id="{{ $po->id }}" data-no-po="{{ $po->no_po }}"
                                                            data-nama-outlet="{{ $po->nama_outlet }}"
                                                            data-status="{{ $po->status }}"
                                                            data-tgl-req="{{ $po->tgl_permintaan }}"
                                                            data-items="{{ json_encode($po->items) }}"
                                                            data-bs-toggle="modal" data-bs-target="#detailModal">
                                                            <i class="bi bi-eye text-muted me-2"></i> Lihat Detail
                                                        </button>
                                                    </li>

                                                    <li>
                                                        <hr class="dropdown-divider my-1" style="border-color: #f1f5f9;">
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item py-2 text-warning"
                                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                                            data-id="{{ $po->id }}" data-no-po="{{ $po->no_po }}"
                                                            data-items="{{ json_encode($po->items) }}">
                                                            <i class="bi bi-pencil me-2"></i> Edit PO
                                                        </button>
                                                    </li>

                                                    <li>
                                                        <button type="button" class="dropdown-item py-2 text-danger"
                                                            onclick="confirmDelete('{{ $po->id }}')">
                                                            <i class="bi bi-trash3 me-2"></i> Hapus PO
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>

                                            <form id="deleteForm{{ $po->id }}" action="{{ route('po.delete', $po->id) }}"
                                                method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- MODAL VIEW PREMIUM DESIGN -->
                        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg"
                                    style="border-radius: 20px; overflow: hidden;">
                                    <div class="modal-header border-bottom px-4" style="background-color: #ffffff;">
                                        <div class="d-flex align-items-center">
                                            <div class="p-2 bg-info-subtle text-info rounded-3 me-2.5 d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;">
                                                <i class="bi bi-file-earmark-text-fill small"></i>
                                            </div>
                                            <h6 class="modal-title fw-bold text-dark mb-0" id="detailLabel">Detail
                                                Purchase Order</h6>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4" style="background-color: #fafafa;">
                                        <div id="modalContent"></div>

                                        <!-- SUPPLIER INTERACTION CONTAINER -->
                                        <div id="supplier-section" class="mt-4" style="display: none;">
                                            <div class="d-flex align-items-center mb-2.5">
                                                <i class="bi bi-truck text-primary me-2 fs-5"></i>
                                                <h6 class="fw-bold text-dark mb-0 small text-uppercase"
                                                    style="letter-spacing: 0.5px;">Alokasi Kendali Supplier</h6>
                                            </div>
                                            <div id="supplier-inputs"
                                                class="card p-3 bg-white shadow-sm border-0 rounded-4">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top px-4" style="background-color: #ffffff;">
                                        <button type="button" id="btn-approve"
                                            class="btn btn-action-custom px-4 rounded-3 btn-success text-white border-0 fw-semibold"
                                            data-status="Approved"
                                            style="background-color: #16a34a; font-size: 13.5px; padding-top: 8px; padding-bottom: 8px;">Setujui
                                            PO</button>
                                        <button type="button" id="btn-reject"
                                            class="btn btn-action-custom px-4 rounded-3 btn-outline-danger fw-medium"
                                            data-status="Rejected"
                                            style="font-size: 13.5px; padding-top: 8px; padding-bottom: 8px;">Tolak
                                            Transaksi</button>
                                        <button type="button"
                                            class="btn btn-light border px-4 rounded-3 text-secondary small"
                                            data-bs-dismiss="modal"
                                            style="font-size: 13.5px; padding-top: 8px; padding-bottom: 8px;">Batal</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL EDIT -->
                        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <form id="formEditPO" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                                        <div class="modal-header border-bottom px-4">
                                            <h6 class="modal-title fw-bold text-dark" id="editLabel">Edit Purchase Order
                                            </h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4 bg-light">
                                            <div id="editModalContent"></div>
                                        </div>
                                        <div class="modal-footer px-4">
                                            <button type="button" class="btn btn-light border"
                                                data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-warning text-white fw-semibold">Simpan
                                                Perubahan</button>
                                        </div>
                                    </div>
                                </form>
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
        $(document).ready(function () {
            // Init Datatable
            $('#orderTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
            });

            // Handle Modal Backdrop bug saat ditutup
            $('#detailModal').on('hidden.bs.modal', function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
            });

            // LOGIK RENDERING DATA MODAL
            const detailModal = document.getElementById('detailModal');
            detailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                // Ambil data dasar
                const poId = button.getAttribute('data-id');
                const noPo = button.getAttribute('data-no-po');
                const namaOutlet = button.getAttribute('data-nama-outlet') || '-';
                const status = button.getAttribute('data-status');
                const tglReq = button.getAttribute('data-tgl-req');
                const items = JSON.parse(button.getAttribute('data-items') || '[]');

                $('#detailModal').data('current-id', poId);
                detailModal.querySelector('.modal-title').textContent = 'Detail PO: ' + noPo;

                // Reset state & text tombol
                if (status !== 'Waiting') {
                    $('#btn-approve').prop('disabled', true).text('Sudah diproses').attr('data-status', '');
                    $('#btn-reject').prop('disabled', true).text('Sudah diproses').attr('data-status', '');
                } else {
                    $('#btn-approve').prop('disabled', false).text('Setujui PO').attr('data-status', 'Approved');
                    $('#btn-reject').prop('disabled', false).text('Tolak Transaksi').attr('data-status', 'Rejected');
                }

                const isEditable = (status === 'Waiting') ? '' : 'disabled';

                let itemRows = '';
                if (items.length > 0) {
                    items.forEach((item, index) => {
                        const sumber = (item.sumber_barang || 'GUDANG').toUpperCase();
                        const badge = sumber === 'SUPPLIER'
                            ? '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning fw-semibold px-2 py-1 rounded-pill" style="font-size:10px; --bs-border-opacity: .2;">Supplier</span>'
                            : '<span class="badge bg-primary-subtle text-primary-emphasis border border-primary fw-semibold px-2 py-1 rounded-pill" style="font-size:10px; --bs-border-opacity: .2;">DC</span>';

                        const detailId = item.id || item.detail_id || '';

                        // Render baris tabel, beri class 'satuan-dropdown' untuk diload via AJAX
                        itemRows += `
                                            <tr class="small text-secondary item-row" data-detail-id="${detailId}" style="border-bottom: 1px solid #f1f5f9;">
                                                <td class="text-center text-muted align-middle">${index + 1}</td>
                                                <td class="text-dark fw-bold align-middle" style="font-size:13px;">${item.nama_bahan}</td>
                                                <td class="align-middle">
                                                    <input type="number" class="form-control form-control-sm text-center edit-qty mx-auto" 
                                                        value="${item.jumlah}" ${isEditable} min="0" step="0.01" style="max-width: 80px; border-radius: 6px;">
                                                </td>
                                                <td class="align-middle">
                                                    <select class="form-select form-select-sm edit-satuan satuan-dropdown" 
                                                        data-bahan-id="${item.bahan_id}" data-current-unit="${item.unit_id}" 
                                                        ${isEditable} style="border-radius: 6px; min-width: 120px;">
                                                        <option value="${item.unit_id}">${item.satuan || 'Loading...'}</option>
                                                    </select>
                                                </td>
                                                <td class="align-middle text-center">${badge}</td>
                                            </tr>`;
                    });
                } else {
                    itemRows = '<tr><td colspan="5" class="text-center text-muted small py-3">Tidak ada detail barang</td></tr>';
                }

                let infoBadgeClass = 'bg-warning-subtle text-warning-emphasis border border-warning';
                if (status === 'Approved') infoBadgeClass = 'bg-success-subtle text-success-emphasis border border-success';
                if (status === 'Rejected') infoBadgeClass = 'bg-danger-subtle text-danger-emphasis border border-danger';

                $('#modalContent').html(`
                                    <div class="card p-3 border-0 shadow-sm rounded-4 mb-4" style="background-color: #ffffff;">
                                        <h6 class="fw-bold text-dark small text-uppercase mb-2.5" style="letter-spacing: 0.3px;">Informasi Pesanan</h6>
                                        <table class="table table-sm table-borderless mb-0 small text-secondary">
                                            <tr>
                                                <th class="text-muted fw-normal pb-2" width="30%">No PO</th>
                                                <td class="text-dark fw-bold pb-2">${noPo}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-normal pb-2">Nama Outlet</th>
                                                <td class="text-dark fw-semibold pb-2" style="color: #4f46e5 !important;">${namaOutlet}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-normal pb-2">Tanggal Request</th>
                                                <td class="text-dark pb-2">${tglReq}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted fw-normal">Status Logistik</th>
                                                <td><span class="badge ${infoBadgeClass} fw-semibold px-2.5 py-1 rounded-pill" style="font-size:11px; --bs-border-opacity: .2;">${status}</span></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="card p-3 border-0 shadow-sm rounded-4" style="background-color: #ffffff;">
                                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                                            <h6 class="fw-bold text-dark small text-uppercase mb-0" style="letter-spacing: 0.3px;">Daftar Item Pesanan</h6>
                                            ${status === 'Waiting' ? `
                                                <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle shadow-sm" 
                                                      style="cursor: pointer; padding: 6px 12px; font-size: 11px; letter-spacing: 0.3px;"
                                                      onclick="Swal.fire({
                                                          icon: 'info',
                                                          title: 'Petunjuk Validasi PO',
                                                          text: 'Sebelum menyetujui PO, harap pastikan Qty dan Satuan yang diajukan sudah sesuai dengan ketersediaan stok riil di gudang. Anda dapat mengubahnya langsung pada tabel di bawah ini.',
                                                          confirmButtonColor: '#4f46e5',
                                                          confirmButtonText: 'Mengerti'
                                                      })">
                                                    <i class="bi bi-info-circle-fill me-1"></i> Info Penyesuaian
                                                </span>
                                            ` : ''}
                                        </div>
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="text-muted small text-uppercase" style="border-bottom: 2px solid #f1f5f9;">
                                                <tr>
                                                    <th class="text-center pb-2" width="5%" style="font-weight:600;">No.</th>
                                                    <th class="pb-2" style="font-weight:600;">Nama Bahan</th>
                                                    <th class="text-center pb-2" width="20%" style="font-weight:600;">Qty</th>
                                                    <th class="text-start pb-2" width="25%" style="font-weight:600;">Satuan (Bisa diubah)</th>
                                                    <th class="text-center pb-2" width="15%" style="font-weight:600;">Asal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${itemRows}
                                            </tbody>
                                        </table>
                                    </div>
                                `);

                // --- LOAD DROPDOWN SATUAN VIA AJAX ---
                $('.satuan-dropdown').each(function () {
                    let selectElement = $(this);
                    let bahanId = selectElement.data('bahan-id');
                    let currentUnitId = selectElement.data('current-unit');

                    $.get('/get-satuan-bahan/' + bahanId, function (res) {
                        let options = '';
                        res.forEach(function (satuan) {
                            let isSelected = (satuan.id == currentUnitId) ? 'selected' : '';
                            options += `<option value="${satuan.id}" ${isSelected}>${satuan.nama_unit}</option>`;
                        });
                        selectElement.html(options);
                    });
                });

                $('#supplier-section').hide();
                $('#supplier-inputs').html('');

                if (status === 'Waiting') {
                    $.get('/dashboard-scm/po-supplier-items/' + poId, function (res) {
                        if (res.items && res.items.length > 0) {
                            $('#supplier-section').show();
                            let html = '';
                            res.items.forEach(function (item) {
                                html += `
                                                <div class="d-flex align-items-center gap-2 mb-2.5">
                                                    <span class="badge bg-light text-dark border fw-medium" style="min-width:160px; font-size:12px; text-align:left; display:inline-block; padding: 8px 12px; border-radius: 8px;">
                                                        <i class="bi bi-box-seam me-1 text-muted"></i> ${item.nama_bahan}
                                                    </span>
                                                    <select class="form-select form-select-sm supplier-select" data-detail-id="${item.detail_id}" required style="border-radius: 8px; padding-top: 6px; padding-bottom: 6px;">
                                                        <option value="">-- Pilih Supplier Penyuplai --</option>`;
                                res.suppliers.forEach(function (sup) {
                                    html += `<option value="${sup.id}">${sup.supplier_name}</option>`;
                                });
                                html += `</select>
                                                </div>`;
                            });
                            $('#supplier-inputs').html(html);
                        }
                    });
                }
            });

            // SELEKTOR KLIK UNTUK APPROVE / REJECT
            $('#btn-approve, #btn-reject').click(function () {
                let status = $(this).attr('data-status');
                let id = $('#detailModal').data('current-id');

                if (!status) return;

                if (status === 'Approved') {
                    let valid = true;
                    $('#supplier-inputs select.supplier-select').each(function () {
                        if (!$(this).val()) {
                            valid = false;
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });
                    if (!valid) {
                        Swal.fire('Perhatian', 'Pilih supplier untuk semua bahan dari Supplier terlebih dahulu!', 'warning');
                        return;
                    }
                }

                Swal.fire({
                    title: 'Memproses Data',
                    text: 'Sedang memperbarui status PO...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                let supplierData = [];
                $('#supplier-inputs select.supplier-select').each(function () {
                    if ($(this).val()) {
                        supplierData.push({
                            detail_id: $(this).data('detail-id'),
                            supplier_id: $(this).val()
                        });
                    }
                });

                // PERUBAHAN: Tangkap data Qty dan Satuan yang sudah diedit
                let modifiedItems = [];
                $('#modalContent .item-row').each(function () {
                    modifiedItems.push({
                        detail_id: $(this).data('detail-id'),
                        qty: $(this).find('.edit-qty').val(),
                        satuan: $(this).find('.edit-satuan').val()
                    });
                });

                $.ajax({
                    url: "{{ route('update.status.po') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status,
                        supplier_data: supplierData,
                        modified_items: modifiedItems // Data ini akan dikirim ke Backend
                    },
                    success: function (response) {
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Status diubah ke ' + status
                        }).then(() => { location.reload(); });
                    },
                    error: function (xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan sistem'
                        });
                    }
                });
            });

            // --- FIX PERBAIKAN: SELEKTOR KLIK BERDASARKAN ID LANGSUNG ---
            $('#btn-approve, #btn-reject').click(function () {
                let status = $(this).attr('data-status'); // Gunakan .attr() murni, anti-cache!
                let id = $('#detailModal').data('current-id');

                if (!status) return;

                if (status === 'Approved') {
                    let valid = true;
                    $('#supplier-inputs select.supplier-select').each(function () {
                        if (!$(this).val()) {
                            valid = false;
                            $(this).addClass('is-invalid');
                        } else {
                            $(this).removeClass('is-invalid');
                        }
                    });
                    if (!valid) {
                        Swal.fire('Perhatian', 'Pilih supplier untuk semua bahan dari Supplier terlebih dahulu!', 'warning');
                        return;
                    }
                }

                // Tampilkan loading loader agar user tahu proses sedang berjalan
                Swal.fire({
                    title: 'Memproses Data',
                    text: 'Sedang memperbarui status PO...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                let supplierData = [];
                $('#supplier-inputs select.supplier-select').each(function () {
                    if ($(this).val()) {
                        supplierData.push({
                            detail_id: $(this).data('detail-id'),
                            supplier_id: $(this).val()
                        });
                    }
                });

                $.ajax({
                    url: "{{ route('update.status.po') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status,
                        supplier_data: supplierData
                    },
                    success: function (response) {
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Status diubah ke ' + status
                        }).then(() => { location.reload(); });
                    },
                    error: function (xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan sistem'
                        });
                    }
                });
            });
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm' + id).submit();
                }
            });
        }

        // Handle Edit Modal
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const poId = button.getAttribute('data-id');
            const noPo = button.getAttribute('data-no-po');
            const items = JSON.parse(button.getAttribute('data-items'));

            document.getElementById('formEditPO').action = `/purchasing/po-update/${poId}`;
            document.getElementById('editLabel').textContent = 'Edit PO: ' + noPo;

            let html = `
            <table class="table table-white bg-white shadow-sm rounded-3">
                <thead><tr><th>Bahan</th><th>Qty</th><th>Satuan</th></tr></thead>
                <tbody>`;

            items.forEach((item) => {
                html += `
                <tr>
                    <td>${item.nama_bahan}</td>
                    <td><input type="number" name="items[${item.detail_id}][qty]" class="form-control" value="${item.jumlah}" step="0.01"></td>
                    <td>
                        <select name="items[${item.detail_id}][unit_id]" class="form-select">
                            <option value="${item.unit_id}">${item.satuan}</option>
                        </select>
                    </td>
                </tr>`;
            });

            html += `</tbody></table>`;
            $('#editModalContent').html(html);
        });
    </script>
@endpush

@include('Temp.Investor.footer')
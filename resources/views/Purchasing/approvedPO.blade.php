{{-- resources/views/purchasing/dashboardOutlet.blade.php --}}
@include('Temp.Investor.header')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* 1. Warna Utama & Typography */
    :root {
        --primary-color: #696cff;
        --primary-hover: #5f61e6;
        --light-bg: #f5f5f9;
        --text-muted: #697a8d;
    }

    body { background-color: var(--light-bg); }

    /* 2. Kartu dengan Border Radius Lembut */
    .card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.08);
    }

    /* 3. Tabel yang lebih "Clean" */
    .table thead th {
        background-color: #fcfcfd !important;
        font-size: 0.7rem !important;
        color: #566a7f !important;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 18px 20px !important;
        border-bottom: 2px solid #f0f0f4 !important;
    }

    .table tbody td {
        padding: 16px 20px !important;
        color: #435971;
        font-weight: 500;
    }

    /* 4. Tombol Indigo */
    .btn-primary {
        background: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        padding: 0.5rem 1.2rem;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .btn-primary:hover { background: var(--primary-hover) !important; transform: translateY(-1px); }

    /* 5. Input Field */
    .form-select {
        border: 1px solid #d9dee3;
        border-radius: 8px;
        padding: 0.6rem 1rem;
        transition: border 0.3s;
    }

    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
    }

    /* 6. Badge Status yang Elegan */
    .badge.bg-primary {
        background-color: #e7e7ff !important;
        color: var(--primary-color) !important;
        padding: 0.4em 0.8em;
        border-radius: 6px;
        font-size: 0.8rem;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-1">Daftar PO Siap Kirim</h5>
                    <p class="text-muted small mb-4">Pilih rute area untuk mengelompokkan barang yang searah.</p>

                    {{-- FILTER --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <form action="" method="GET" id="filterForm">
                                <label class="form-label fw-bold small text-uppercase text-muted">Rute
                                    Pengiriman</label>
                                <select name="route_id" class="form-select shadow-none"
                                    onchange="document.getElementById('filterForm').submit()">
                                    <option value="">-- Pilih Rute --</option>
                                    @foreach($routes ?? [] as $route)
                                        <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                                            {{ mb_strtoupper($route->hari_kirim) }} - {{ mb_strtoupper($route->nama_area) }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    <form id="formBuatSJ" action="{{ route('scm.buat-sj') }}" method="POST">
                        @csrf
                        <input type="hidden" name="route_id" id="hidden_route_id" value="{{ request('route_id') }}">
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover" id="orderTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>No PO</th>
                                    <th>Outlet</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($listPO as $po)
                                    <tr>
                                        <td class="text-center"><input type="checkbox" class="form-check-input po-checkbox"
                                                value="{{ $po->id }}"></td>
                                        <td class="fw-bold text-dark">{{ $po->no_po }}</td>
                                        <td>{{ $po->outlet_name }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-secondary btn-view-po"
                                                data-id="{{ $po->id }}" data-no-po="{{ $po->no_po }}"
                                                data-status="{{ $po->status }}">
                                                <i class="bi bi-eye"></i> Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Data tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-3 d-flex justify-content-between align-items-center">
                        <span class="text-dark fw-semibold" id="selectedCount">0 PO dipilih</span>
                        <button type="button" id="btnBuatSJ" class="btn btn-primary px-4" disabled>
                            Buat Surat Jalan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal (Struktur tetap sama agar JS tidak error) --}}
    <div class="modal fade" id="detailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Detail PO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalContent">Loading...</div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary btn-update-status" id="btn-approve"
                        data-status="Approved">Approve</button>
                    <button type="button" class="btn btn-danger btn-update-status" id="btn-reject"
                        data-status="Rejected">Reject</button>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
    <script>
        $(document).ready(function () {
            // 1. Inisialisasi DataTable
            $('#orderTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                columnDefs: [
                    { targets: 0, orderable: false } // Checkbox tidak perlu di-sort
                ]
            });

            // 2. Logika Checkbox "Pilih Semua"
            $('#selectAll').on('change', function () {
                $('.po-checkbox').prop('checked', $(this).prop('checked'));
                updateSelectedCount();
            });

            $(document).on('change', '.po-checkbox', function () {
                const total = $('.po-checkbox').length;
                const checked = $('.po-checkbox:checked').length;
                $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
                $('#selectAll').prop('checked', checked === total);
                updateSelectedCount();
            });

            function updateSelectedCount() {
                const count = $('.po-checkbox:checked').length;
                $('#selectedCount').text(count + ' PO dipilih');
                $('#btnBuatSJ').prop('disabled', count === 0);
            }

            // 3. Submit form Buat Surat Jalan
            $('#btnBuatSJ').on('click', function () {
                // Validasi: Pastikan rute sudah dipilih
                const routeId = $('#hidden_route_id').val();
                if (!routeId) {
                    Swal.fire('Perhatian', 'Anda harus memilih Rute Pengiriman di atas terlebih dahulu!', 'warning');
                    return;
                }

                const checkedIds = $('.po-checkbox:checked').map(function () {
                    return $(this).val();
                }).get();

                if (checkedIds.length === 0) {
                    Swal.fire('Perhatian', 'Pilih minimal 1 PO terlebih dahulu!', 'warning');
                    return;
                }

                // Hapus input lama lalu tambah yang baru ke form hidden
                $('#formBuatSJ input[name="po_ids[]"]').remove();
                checkedIds.forEach(function (id) {
                    $('#formBuatSJ').append(
                        $('<input>').attr({ type: 'hidden', name: 'po_ids[]', value: id })
                    );
                });

                $('#formBuatSJ').submit();
            });

            // 4. Logika Tombol Detail & Modal
            $('.btn-view-po').click(function () {
                let id = $(this).data('id');
                let noPo = $(this).data('no-po');
                let status = $(this).data('status');

                $('#detailModal').data('current-id', id);

                if (status !== 'Waiting') {
                    $('#btn-approve, #btn-reject').prop('disabled', true).text('Sudah diproses');
                } else {
                    $('#btn-approve').prop('disabled', false).text('Approve');
                    $('#btn-reject').prop('disabled', false).text('Reject');
                }

                $('#modalContent').html(`
                        <table class="table table-bordered mb-0">
                            <tr><th style="width: 40%;">No. PO</th><td>${noPo}</td></tr>
                            <tr><th>Status Saat Ini</th><td><span class="badge bg-primary">${status}</span></td></tr>
                        </table>
                    `);

                var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
                myModal.show();
            });

            // 5. Submit Update Status PO via AJAX
            $('.btn-update-status').click(function () {
                let status = $(this).data('status');
                let id = $('#detailModal').data('current-id');

                $.ajax({
                    url: "{{ route('update.status.po') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Status diubah ke ' + status
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan sistem'
                        });
                    }
                });
            });

            // Bersihkan sisa backdrop modal jika nyangkut
            $('#detailModal').on('hidden.bs.modal', function () {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
            });
        });
    </script>
@endpush

@include('Temp.Investor.footer')
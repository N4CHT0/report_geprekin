{{-- resources/views/Investor/master.blade.php --}}
@section('title', 'Data Mitra Investor')
@section('breadcrumb', 'Investor / Mitra')

@include('Temp.Investor.header')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<style>
    .investor-mitra-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .mitra-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        padding: 22px;
        border: 1px solid rgba(37, 99, 235, .16);
        border-radius: 18px;
        background:
            radial-gradient(circle at top right, rgba(37, 99, 235, .16), transparent 34%),
            linear-gradient(135deg, #ffffff 0%, #f8fbff 45%, #eef6ff 100%);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
    }

    .mitra-hero::after {
        content: "";
        position: absolute;
        right: -80px;
        bottom: -130px;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .11);
        filter: blur(8px);
    }

    .mitra-hero > * {
        position: relative;
        z-index: 1;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 5px 10px;
        margin-bottom: 8px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .10);
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .hero-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 950;
        letter-spacing: -.04em;
        color: #0f172a;
    }

    .hero-subtitle {
        margin-top: 6px;
        color: #64748b;
        font-size: 13.5px;
        font-weight: 650;
    }

    .hero-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .aws-card {
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 18px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .055);
    }

    .aws-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15, 23, 42, .075);
        background: linear-gradient(90deg, #ffffff, #fafcff);
    }

    .aws-card-title {
        margin: 0;
        font-size: 15px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -.02em;
    }

    .aws-card-subtitle {
        margin-top: 3px;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 650;
    }

    .aws-card-body {
        padding: 18px;
    }

    .btn {
        border-radius: 12px;
        font-weight: 750;
        font-size: 13px;
        min-height: 36px;
        transition: .18s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border-color: #2563eb;
        box-shadow: 0 12px 24px rgba(37, 99, 235, .20);
    }

    .btn-success {
        background: linear-gradient(135deg, #059669, #047857);
        border-color: #059669;
        box-shadow: 0 12px 24px rgba(5, 150, 105, .18);
    }

    .btn-soft {
        border: 1px solid rgba(15, 23, 42, .12);
        background: #fff;
        color: #334155;
    }

    .btn-soft:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .btn-outline-primary,
    .btn-outline-danger {
        border-radius: 10px;
        font-weight: 750;
    }

    .summary-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .summary-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, .10);
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 850;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .045);
    }

    .summary-badge.primary {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .table-shell {
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 16px;
        background: #fff;
    }

    .table-responsive {
        overflow: auto;
    }

    .table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }

    table.dataTable {
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    .table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
        padding: 12px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, .10);
        vertical-align: middle;
    }

    .table tbody td {
        padding: 13px 14px;
        vertical-align: middle;
        border-color: rgba(15, 23, 42, .06);
        color: #0f172a;
    }

    .table tbody tr:hover td {
        background: #f8fbff;
    }

    .investor-name {
        font-weight: 850;
        color: #0f172a;
    }

    .kode-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 9px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .mitra-name {
        font-weight: 800;
        color: #334155;
    }

    .action-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .modal-content {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 80px rgba(15, 23, 42, .18);
    }

    .modal-header {
        border-bottom: 1px solid rgba(15, 23, 42, .09);
        background: linear-gradient(90deg, #fff, #fafcff);
        padding: 16px 18px;
    }

    .modal-title {
        font-weight: 900;
        letter-spacing: -.02em;
        color: #0f172a;
    }

    .modal-body {
        padding: 18px;
    }

    .modal-footer {
        border-top: 1px solid rgba(15, 23, 42, .09);
        padding: 14px 18px;
        background: #f8fafc;
    }

    .form-label {
        font-weight: 800;
        font-size: 12px;
        color: #334155;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .form-control,
    .form-select {
        min-height: 42px;
        border-radius: 12px;
        border-color: rgba(100, 116, 139, .30);
        font-size: 13.5px;
        font-weight: 600;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--open {
        z-index: 2000;
    }

    .select2-dropdown {
        z-index: 2001;
        border-color: rgba(100, 116, 139, .30) !important;
        border-radius: 12px !important;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .15);
    }

    .select2-container--default .select2-selection--single {
        height: 42px !important;
        border: 1px solid rgba(100, 116, 139, .30) !important;
        border-radius: 12px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px !important;
        padding-left: 12px !important;
        color: #0f172a !important;
        font-weight: 650;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 42px !important;
        right: 7px !important;
    }

    .dt-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        padding: 0 0 14px;
    }

    .dataTables_length,
    .dataTables_filter {
        margin: 0 !important;
    }

    .dataTables_length label,
    .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .dataTables_length select,
    .dataTables_filter input {
        border: 1px solid rgba(100, 116, 139, .30);
        border-radius: 10px;
        padding: 7px 9px;
        background: #fff;
        color: #0f172a;
        font-weight: 650;
    }

    .dataTables_filter input {
        width: 230px;
    }

    .dataTables_info {
        font-size: 13px;
        color: #64748b;
        padding-top: 14px !important;
        font-weight: 650;
    }

    .dataTables_paginate {
        padding-top: 10px !important;
    }

    .dataTables_wrapper .pagination {
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 4px;
        margin: 0;
    }

    .dataTables_wrapper .pagination .page-link {
        border: 1px solid rgba(15, 23, 42, .12) !important;
        background: #fff !important;
        color: #334155 !important;
        border-radius: 10px !important;
        min-width: 34px;
        height: 34px;
        padding: 6px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: none !important;
        font-size: 13px;
        font-weight: 800;
    }

    .dataTables_wrapper .pagination .page-item.active .page-link {
        background: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
    }

    .dataTables_wrapper .pagination .page-item.disabled .page-link {
        background: #f8fafc !important;
        color: #94a3b8 !important;
    }

    .small-note {
        margin-top: 12px;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 650;
    }

    @media (max-width: 991px) {
        .mitra-hero {
            flex-direction: column;
            align-items: stretch;
        }

        .hero-actions {
            justify-content: flex-start;
        }

        .dt-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .dataTables_filter input {
            width: 100%;
        }

        .dataTables_length label,
        .dataTables_filter label {
            width: 100%;
            align-items: flex-start;
            flex-direction: column;
        }

        .dataTables_wrapper .pagination {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575px) {
        .mitra-hero {
            padding: 18px;
        }

        .hero-title {
            font-size: 22px;
        }

        .hero-actions .btn {
            width: 100%;
        }

        .aws-card-header {
            flex-direction: column;
            align-items: stretch;
        }

        .action-group {
            justify-content: flex-start;
        }
    }
</style>

<div class="investor-mitra-page">
    <section class="mitra-hero">
        <div>
            <div class="hero-kicker">
                <i class="bi bi-building"></i>
                Mitra Management
            </div>
            <h1 class="hero-title">Data Mitra Investor</h1>
            <div class="hero-subtitle">Kelola data mitra dan relasi investor dalam satu console.</div>
        </div>

        <div class="hero-actions">
            <button class="btn btn-soft btn-sm" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>

            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Mitra
            </button>
        </div>
    </section>

    <section class="aws-card">
        <div class="aws-card-header">
            <div>
                <h2 class="aws-card-title">Daftar Mitra</h2>
                <div class="aws-card-subtitle">Gunakan search, show entries, sorting, dan pagination untuk navigasi data.</div>
            </div>

            <div class="summary-row">
                <span class="summary-badge primary">
                    <i class="bi bi-database"></i>
                    Total: {{ count($data) }}
                </span>
                <span class="summary-badge">
                    <i class="bi bi-key"></i>
                    Kode mitra unik
                </span>
            </div>
        </div>

        <div class="aws-card-body">
            <div class="table-shell">
                <div class="table-responsive">
                    <table id="laporanTable" class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th>Nama Investor</th>
                                <th>Kode Mitra</th>
                                <th>Nama Mitra</th>
                                <th style="width:180px;text-align:center;">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($data as $index => $mitra)
                                <tr>
                                    <td style="font-weight:800;color:#64748b;">{{ $index + 1 }}</td>
                                    <td class="investor-name">{{ $mitra->nama_investor ?? '-' }}</td>
                                    <td>
                                        <span class="kode-pill">{{ $mitra->kode_mitra }}</span>
                                    </td>
                                    <td class="mitra-name">{{ $mitra->nama_mitra }}</td>
                                    <td>
                                        <div class="action-group">
                                            <button
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal"
                                                data-id="{{ $mitra->id }}"
                                                data-kode="{{ $mitra->kode_mitra }}"
                                                data-nama="{{ $mitra->nama_mitra }}"
                                                data-investor="{{ $mitra->investor_id }}"
                                            >
                                                <i class="bi bi-pencil-square me-1"></i> Ubah
                                            </button>

                                            <form
                                                action="{{ route('investor.master.delete', $mitra->id) }}"
                                                method="POST"
                                                class="d-inline form-delete"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="small-note">
                <i class="bi bi-info-circle me-1"></i>
                Catatan: kode mitra harus unik.
            </div>
        </div>
    </section>

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('investor.master.storeMitra') }}" method="POST" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mitra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Investor</label>
                        <select class="form-select select2-add" name="investor_id" data-placeholder="Pilih Investor" required>
                            <option value="">-- Pilih Investor --</option>
                            @foreach ($investors as $inv)
                                <option value="{{ $inv->id }}">{{ $inv->nama_investor }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Mitra</label>
                        <input type="text" class="form-control" name="kode_mitra" placeholder="contoh: MTR-001" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Nama Mitra</label>
                        <input type="text" class="form-control" name="nama_mitra" placeholder="Nama mitra" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i> Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('investor.master.update') }}" method="POST" class="modal-content">
                @csrf
                <input type="hidden" name="id" id="edit-id">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Mitra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Investor</label>
                        <select class="form-select select2-edit" name="investor_id" id="edit-investor" data-placeholder="Pilih Investor" required>
                            <option value="">-- Pilih Investor --</option>
                            @foreach ($investors as $inv)
                                <option value="{{ $inv->id }}">{{ $inv->nama_investor }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Mitra</label>
                        <input type="text" class="form-control" name="kode_mitra" id="edit-kode" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label">Nama Mitra</label>
                        <input type="text" class="form-control" name="nama_mitra" id="edit-nama" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save2 me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function () {
    @if (session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: @json(session('success')),
            timer: 1600,
            showConfirmButton: false
        });
    @endif

    @if (session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: @json(session('error')),
            confirmButtonText: 'OK'
        });
    @endif

    @if ($errors->any())
        Swal.fire({
            icon: 'warning',
            title: 'Validasi gagal',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            confirmButtonText: 'OK'
        });
    @endif

if ($.fn.DataTable.isDataTable('#laporanTable')) {
    $('#laporanTable').DataTable().destroy();
}

$('#laporanTable').DataTable({
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
    ordering: true,
    autoWidth: false,
    paging: true,
    searching: true,
    info: true,
    dom:
        "<'dt-toolbar'<'dt-length'l><'dt-search'f>>" +
        "<'row'<'col-12'tr>>" +
        "<'row align-items-center mt-2'<'col-md-5'i><'col-md-7'p>>",
    language: {
        search: 'Search:',
        lengthMenu: 'Show _MENU_ entries',
        info: 'Showing _START_ to _END_ of _TOTAL_ entries',
        infoEmpty: 'Showing 0 to 0 of 0 entries',
        zeroRecords: 'Data tidak ditemukan',
        paginate: {
            first: 'First',
            last: 'Last',
            next: '›',
            previous: '‹'
        }
    },
    columnDefs: [
        { orderable: false, targets: [4] }
    ]
});

    function initSelect2() {
        if (!$.fn.select2) return;

        $('.select2-add').each(function () {
            const el = $(this);
            if (el.hasClass('select2-hidden-accessible')) {
                el.select2('destroy');
            }

            el.select2({
                dropdownParent: $('#addModal'),
                width: '100%',
                allowClear: true,
                placeholder: el.data('placeholder') || 'Pilih Investor'
            });
        });

        $('.select2-edit').each(function () {
            const el = $(this);
            if (el.hasClass('select2-hidden-accessible')) {
                el.select2('destroy');
            }

            el.select2({
                dropdownParent: $('#editModal'),
                width: '100%',
                allowClear: true,
                placeholder: el.data('placeholder') || 'Pilih Investor'
            });
        });
    }

    initSelect2();
    $('#addModal').on('shown.bs.modal', initSelect2);
    $('#editModal').on('shown.bs.modal', initSelect2);

    $('#editModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);

        $('#edit-id').val(button.data('id'));
        $('#edit-kode').val(button.data('kode'));
        $('#edit-nama').val(button.data('nama'));
        $('#edit-investor').val(button.data('investor')).trigger('change');
    });

    document.querySelectorAll('.form-delete').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Yakin hapus data ini?',
                text: 'Data yang dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    document.addEventListener('hidden.bs.modal', function () {
        setTimeout(function () {
            if (!document.querySelector('.modal.show') && !document.querySelector('.swal2-container')) {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }
        }, 120);
    });
});
</script>
@endpush

@include('Temp.Investor.footer')

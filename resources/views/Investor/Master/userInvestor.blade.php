{{-- resources/views/Investor/user_master.blade.php --}}
@section('title', 'Data Investor')
@section('breadcrumb', 'Investor Management / Data Investor')

@include('Temp.Investor.header')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

<style>
    .investor-user-page{display:flex;flex-direction:column;gap:18px}
    .investor-hero{position:relative;overflow:hidden;display:flex;align-items:flex-end;justify-content:space-between;gap:16px;padding:22px;border:1px solid rgba(37,99,235,.16);border-radius:18px;background:radial-gradient(circle at top right,rgba(37,99,235,.16),transparent 34%),linear-gradient(135deg,#fff 0%,#f8fbff 45%,#eef6ff 100%);box-shadow:0 18px 45px rgba(15,23,42,.07)}
    .investor-hero:after{content:"";position:absolute;right:-80px;bottom:-130px;width:260px;height:260px;border-radius:999px;background:rgba(37,99,235,.11);filter:blur(8px)}
    .investor-hero>*{position:relative;z-index:1}
    .hero-kicker{display:inline-flex;align-items:center;gap:7px;padding:5px 10px;margin-bottom:8px;border-radius:999px;background:rgba(37,99,235,.10);color:#1d4ed8;font-size:11px;font-weight:900;letter-spacing:.04em;text-transform:uppercase}
    .hero-title{margin:0;font-size:28px;line-height:1.15;font-weight:950;letter-spacing:-.04em;color:#0f172a}
    .hero-subtitle{margin-top:6px;color:#64748b;font-size:13.5px;font-weight:650}
    .hero-actions{display:flex;gap:8px;align-items:center;justify-content:flex-end;flex-wrap:wrap}
    .aws-card{overflow:hidden;border:1px solid rgba(15,23,42,.09);border-radius:18px;background:rgba(255,255,255,.96);box-shadow:0 14px 36px rgba(15,23,42,.055)}
    .aws-card-header{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 18px;border-bottom:1px solid rgba(15,23,42,.075);background:linear-gradient(90deg,#fff,#fafcff)}
    .aws-card-title{margin:0;font-size:15px;font-weight:900;color:#0f172a;letter-spacing:-.02em}
    .aws-card-subtitle{margin-top:3px;color:#64748b;font-size:12.5px;font-weight:650}
    .aws-card-body{padding:18px}
    .btn{border-radius:12px;font-weight:750;font-size:13px;min-height:36px;transition:.18s ease}
    .btn:hover{transform:translateY(-1px)}
    .btn-primary{background:linear-gradient(135deg,#2563eb,#1d4ed8);border-color:#2563eb;box-shadow:0 12px 24px rgba(37,99,235,.20)}
    .btn-success{background:linear-gradient(135deg,#059669,#047857);border-color:#059669;box-shadow:0 12px 24px rgba(5,150,105,.18)}
    .btn-soft{border:1px solid rgba(15,23,42,.12);background:#fff;color:#334155}
    .btn-soft:hover{background:#f8fafc;color:#0f172a}
    .btn-outline-primary,.btn-outline-danger,.btn-outline-secondary{border-radius:10px;font-weight:750}
    .summary-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .summary-badge{display:inline-flex;align-items:center;gap:6px;padding:7px 10px;border-radius:999px;border:1px solid rgba(15,23,42,.10);background:#fff;color:#334155;font-size:12px;font-weight:850;box-shadow:0 8px 20px rgba(15,23,42,.045)}
    .summary-badge.primary{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
    .table-shell{overflow:hidden;border:1px solid rgba(15,23,42,.09);border-radius:16px;background:#fff}
    .table-responsive{overflow:auto}
    .table{width:100%!important;margin-bottom:0!important}
    table.dataTable{margin-top:0!important;margin-bottom:0!important}
    .table thead th{background:#f8fafc;color:#475569;font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;white-space:nowrap;padding:12px 14px;border-bottom:1px solid rgba(15,23,42,.10);vertical-align:middle}
    .table tbody td{padding:13px 14px;vertical-align:middle;border-color:rgba(15,23,42,.06);color:#0f172a}
    .table tbody tr:hover td{background:#f8fbff}
    .investor-name{font-weight:850;color:#0f172a}
    .user-name{font-weight:750;color:#334155}
    .email-text{color:#475569;font-weight:650}
    .date-pill{display:inline-flex;align-items:center;padding:5px 8px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:12px;font-weight:750;white-space:nowrap}
    .action-group{display:flex;align-items:center;justify-content:center;gap:7px;flex-wrap:wrap}
    .modal-content{border:none;border-radius:18px;overflow:hidden;box-shadow:0 24px 80px rgba(15,23,42,.18)}
    .modal-header{border-bottom:1px solid rgba(15,23,42,.09);background:linear-gradient(90deg,#fff,#fafcff);padding:16px 18px}
    .modal-title{font-weight:900;letter-spacing:-.02em;color:#0f172a}
    .modal-body{padding:18px}
    .modal-footer{border-top:1px solid rgba(15,23,42,.09);padding:14px 18px;background:#f8fafc}
    .form-label{font-weight:800;font-size:12px;color:#334155;letter-spacing:.04em;text-transform:uppercase;margin-bottom:7px}
    .form-control,.form-select{min-height:42px;border-radius:12px;border-color:rgba(100,116,139,.30);font-size:13.5px;font-weight:600}
    .form-control:focus,.form-select:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.10)}
    .select2-container{width:100%!important}
    .select2-container--open{z-index:2000}
    .select2-dropdown{z-index:2001;border-color:rgba(100,116,139,.30)!important;border-radius:12px!important;overflow:hidden;box-shadow:0 18px 45px rgba(15,23,42,.15)}
    .select2-container--default .select2-selection--single{height:42px!important;border:1px solid rgba(100,116,139,.30)!important;border-radius:12px!important}
    .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:42px!important;padding-left:12px!important;color:#0f172a!important;font-weight:650}
    .select2-container--default .select2-selection--single .select2-selection__arrow{height:42px!important;right:7px!important}
    .dt-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:0 0 14px}
    .dataTables_length,.dataTables_filter{margin:0!important}
    .dataTables_length label,.dataTables_filter label{display:flex;align-items:center;gap:8px;margin:0;color:#64748b;font-size:13px;font-weight:700}
    .dataTables_length select,.dataTables_filter input{border:1px solid rgba(100,116,139,.30);border-radius:10px;padding:7px 9px;background:#fff;color:#0f172a;font-weight:650}
    .dataTables_filter input{width:230px}
    .dataTables_info{font-size:13px;color:#64748b;padding-top:14px!important;font-weight:650}
    .dataTables_paginate{padding-top:10px!important}
    .dataTables_wrapper .pagination{justify-content:flex-end;flex-wrap:wrap;gap:4px;margin:0}
    .dataTables_wrapper .pagination .page-link{border:1px solid rgba(15,23,42,.12)!important;background:#fff!important;color:#334155!important;border-radius:10px!important;min-width:34px;height:34px;padding:6px 10px;display:inline-flex;align-items:center;justify-content:center;box-shadow:none!important;font-size:13px;font-weight:800}
    .dataTables_wrapper .pagination .page-item.active .page-link{background:#2563eb!important;border-color:#2563eb!important;color:#fff!important}
    .dataTables_wrapper .pagination .page-item.disabled .page-link{background:#f8fafc!important;color:#94a3b8!important}
    .small-note{margin-top:12px;color:#64748b;font-size:12.5px;font-weight:650}
    @media(max-width:991px){.investor-hero{flex-direction:column;align-items:stretch}.hero-actions{justify-content:flex-start}.dt-toolbar{align-items:stretch;flex-direction:column}.dataTables_filter input{width:100%}.dataTables_length label,.dataTables_filter label{width:100%;align-items:flex-start;flex-direction:column}.dataTables_wrapper .pagination{justify-content:flex-start}}
    @media(max-width:575px){.investor-hero{padding:18px}.hero-title{font-size:22px}.hero-actions .btn{width:100%}.aws-card-header{flex-direction:column;align-items:stretch}.action-group{justify-content:flex-start}}
</style>

<div class="investor-user-page">
    <section class="investor-hero">
        <div>
            <div class="hero-kicker">
                <i class="bi bi-people-fill"></i>
                Investor Management
            </div>
            <h1 class="hero-title">Data Investor</h1>
            <div class="hero-subtitle">Kelola investor, relasi user, email, dan akses operasional dalam satu console.</div>
        </div>

        <div class="hero-actions">
            <button class="btn btn-soft btn-sm" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
            </button>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus me-1"></i> Tambah User
            </button>
            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addInvestorModal">
                <i class="bi bi-plus-circle me-1"></i> Tambah Investor
            </button>
            <a href="{{ route('investor.user.operasional') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-person-gear me-1"></i> Users Operasional
            </a>
        </div>
    </section>

    <section class="aws-card">
        <div class="aws-card-header">
            <div>
                <h2 class="aws-card-title">Daftar Investor</h2>
                <div class="aws-card-subtitle">Gunakan search, show entries, sorting, dan pagination untuk navigasi data.</div>
            </div>
            <div class="summary-row">
                <span class="summary-badge primary"><i class="bi bi-database"></i>Total: {{ count($data) }}</span>
                <span class="summary-badge"><i class="bi bi-lightning-charge"></i>Data aktif</span>
            </div>
        </div>

        <div class="aws-card-body">
            <div class="table-shell">
                <div class="table-responsive">
                    <table id="investorTable" class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th>Nama Investor</th>
                                <th>User</th>
                                <th>Email</th>
                                <th style="width:180px">Created At</th>
                                <th style="width:180px;text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $index => $inv)
                                <tr>
                                    <td style="font-weight:800;color:#64748b;">{{ $index + 1 }}</td>
                                    <td class="investor-name">{{ $inv->nama_investor }}</td>
                                    <td class="user-name">{{ $inv->nama_user }}</td>
                                    <td class="email-text">{{ $inv->email }}</td>
                                    <td><span class="date-pill"><i class="bi bi-calendar3 me-1"></i>{{ $inv->created_at }}</span></td>
                                    <td>
                                        <div class="action-group">
                                            <button
                                                class="btn btn-sm btn-outline-primary btn-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal"
                                                data-id="{{ $inv->id }}"
                                                data-nama="{{ $inv->nama_investor }}"
                                                data-userid="{{ $inv->user_id }}"
                                                data-name="{{ $inv->nama_user }}"
                                                data-email="{{ $inv->email }}"
                                            >
                                                <i class="bi bi-pencil-square me-1"></i> Ubah
                                            </button>

                                            <form action="{{ route('investor.user.delete', $inv->id) }}" method="POST" class="d-inline form-delete">
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
                Tips: gunakan search DataTables untuk menemukan investor lebih cepat.
            </div>
        </div>
    </section>

    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('investor.user.storeUser') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="name" class="form-control" placeholder="Nama user" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="email@domain.com" required></div>
                    <div class="mb-0"><label class="form-label">Password</label><input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="addInvestorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('investor.user.storeInvestor') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Investor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Nama Investor</label><input type="text" name="nama_investor" class="form-control" placeholder="Nama investor" required></div>
                    <div class="mb-0">
                        <label class="form-label">Pilih User</label>
                        <select name="user_id" class="form-select select2-investor-user" data-placeholder="Pilih User" required>
                            <option value="">-- Pilih User --</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        <div class="small text-muted mt-1">User harus sudah dibuat terlebih dahulu.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST" class="modal-content">
                @csrf
                @method('POST')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Investor & User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="edit-userid">
                    <div class="mb-3"><label class="form-label">Nama Investor</label><input type="text" name="nama_investor" id="edit-nama" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Nama User</label><input type="text" name="name" id="edit-name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="edit-email" class="form-control" required></div>
                    <div class="mb-0"><label class="form-label">Password Opsional</label><input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save2 me-1"></i> Simpan</button>
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
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 1600, showConfirmButton: false });
    @endif

    @if (session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), confirmButtonText: 'OK' });
    @endif

    @if ($errors->any())
        Swal.fire({ icon: 'warning', title: 'Validasi gagal', html: `{!! implode('<br>', $errors->all()) !!}`, confirmButtonText: 'OK' });
    @endif

    if ($.fn.DataTable.isDataTable('#investorTable')) {
        $('#investorTable').DataTable().destroy(false);
    }

    $('#investorTable').DataTable({
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
            paginate: { first: 'First', last: 'Last', next: '›', previous: '‹' }
        },
        columnDefs: [{ orderable: false, targets: [5] }]
    });

    function initInvestorSelect2() {
        if (!$.fn.select2) return;

        const select = $('.select2-investor-user');

        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }

        select.select2({
            dropdownParent: $('#addInvestorModal'),
            width: '100%',
            allowClear: true,
            placeholder: select.data('placeholder') || 'Pilih User'
        });
    }

    initInvestorSelect2();
    $('#addInvestorModal').on('shown.bs.modal', initInvestorSelect2);

    const editModal = document.getElementById('editModal');
    const editForm  = document.getElementById('editForm');

    if (editModal && editForm) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');

            document.getElementById('edit-nama').value   = button.getAttribute('data-nama') || '';
            document.getElementById('edit-userid').value = button.getAttribute('data-userid') || '';
            document.getElementById('edit-name').value   = button.getAttribute('data-name') || '';
            document.getElementById('edit-email').value  = button.getAttribute('data-email') || '';

            editForm.action = `/investor/user/update/${id}`;
        });
    }

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

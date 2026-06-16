@include('Temp.Investor.header')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .page-wrap { padding: 18px 0; }
    .card-clean { border: 1px solid #eef2f7; border-radius: 14px; overflow: hidden; }
    .card-clean .card-header { background: #fbfdff; border-bottom: 1px solid #eef2f7; }
    .title { font-weight: 700; letter-spacing: .2px; }
    .subtle { color: #6b7280; font-size: .9rem; }
    .btn-soft { border: 1px solid #e5e7eb; background: #fff; }
    .btn-soft:hover { background: #f9fafb; }
    .table thead th { white-space: nowrap; }
    .table td, .table th { vertical-align: middle; }
    .toolbar { display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between; }
    .modal .form-label { font-weight: 600; font-size: .9rem; }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid page-wrap">

            <div class="card card-clean shadow-sm">
                <div class="card-header p-3">
                    <div class="toolbar">
                        <div>
                            <div class="title">All Users</div>
                            <div class="subtle">Kelola semua user berdasarkan role</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-soft btn-sm" onclick="$('#allUsersTable').DataTable().ajax.reload(null, false)">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>

                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addAllUserModal">
                                <i class="bi bi-person-plus me-1"></i> Tambah User
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="allUsersTable" class="table table-striped table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">No</th>
                                    <th class="text-start">Nama</th>
                                    <th class="text-start">Email</th>
                                    <th style="width:150px">Role</th>
                                    <th class="text-start">Outlet</th>
                                    <th style="width:90px">Area ID</th>
                                    <th style="width:130px">Previous Role</th>
                                    <th style="width:170px">Created At</th>
                                    <th style="width:170px">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="small text-muted mt-2">
                        Data dibatasi dengan AJAX server-side agar tidak membebani memory server.
                    </div>
                </div>
            </div>

            <div class="modal fade" id="addAllUserModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('investor.user.all.store') }}" method="POST" class="modal-content">
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title">Tambah All User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Outlet</label>
                                <select name="outlet_id" class="form-select select2-outlet">
                                    <option value="">-- Tidak Ada --</option>
                                    @foreach($outlets as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_outlet }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Area ID</label>
                                <input type="number" name="area_id" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check2-circle me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="editAllUserModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="editAllUserForm" method="POST" class="modal-content">
                        @csrf
                        @method('POST')

                        <div class="modal-header">
                            <h5 class="modal-title">Edit All User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" name="name" id="edit-name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit-email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" id="edit-role" class="form-select" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Outlet</label>
                                <select name="outlet_id" id="edit-outlet" class="form-select select2-outlet-edit">
                                    <option value="">-- Tidak Ada --</option>
                                    @foreach($outlets as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_outlet }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Area ID</label>
                                <input type="number" name="area_id" id="edit-area-id" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password (opsional)</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
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
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", () => {
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

    $('#allUsersTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        ordering: true,
        autoWidth: false,
        ajax: "{{ route('investor.user.all.data') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'u.name', className: 'text-start' },
            { data: 'email', name: 'u.email', className: 'text-start' },
            { data: 'role', name: 'u.role' },
            { data: 'outlet', name: 'o.nama_outlet', className: 'text-start' },
            { data: 'area_id', name: 'u.area_id' },
            { data: 'previous_role', name: 'u.previous_role' },
            { data: 'created_at', name: 'u.created_at' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
        ]
    });

    $('.select2-outlet').select2({
        dropdownParent: $('#addAllUserModal'),
        width: '100%'
    });

    $('.select2-outlet-edit').select2({
        dropdownParent: $('#editAllUserModal'),
        width: '100%'
    });

    const editModal = document.getElementById('editAllUserModal');
    const editForm  = document.getElementById('editAllUserForm');

    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');

        document.getElementById('edit-name').value = button.getAttribute('data-name') || '';
        document.getElementById('edit-email').value = button.getAttribute('data-email') || '';
        document.getElementById('edit-role').value = button.getAttribute('data-role') || '';
        document.getElementById('edit-area-id').value = button.getAttribute('data-area_id') || '';

        $('#edit-outlet').val(button.getAttribute('data-outlet_id') || '').trigger('change');

        editForm.action = `/investor/user/all-users/update/${id}`;
    });

    document.addEventListener('submit', function(e) {
        if (!e.target.classList.contains('form-delete')) return;

        e.preventDefault();

        Swal.fire({
            title: 'Yakin hapus data ini?',
            text: 'Data yang dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) e.target.submit();
        });
    });
});
</script>

@include('Temp.Investor.footer')
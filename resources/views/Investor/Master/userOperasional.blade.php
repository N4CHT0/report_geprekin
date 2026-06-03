@include('Temp.Investor.header')

{{-- SweetAlert2 --}}
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
                            <div class="title">Data Operasional</div>
                            <div class="subtle">Kelola user role crew, spv, dan tm_manager + multi outlet</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-soft btn-sm" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>

                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addOpModal">
                                <i class="bi bi-person-plus me-1"></i> Tambah User Operasional
                            </button>

                            <a href="{{ route('investor.user.master') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-people me-1"></i> Data Investor
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="opTable" class="table table-striped table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">No</th>
                                    <th class="text-start">Nama</th>
                                    <th class="text-start">Email</th>
                                    <th style="width:130px">Role</th>
                                    <th class="text-start">Outlet</th>
                                    <th style="width:170px">Created At</th>
                                    <th style="width:160px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index => $u)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-start">{{ $u->name }}</td>
                                        <td class="text-start">{{ $u->email }}</td>
                                        <td>{{ strtoupper($u->role) }}</td>
                                        <td class="text-start">
                                            {{ $u->nama_outlets ?? '-' }}
                                        </td>
                                        <td>{{ $u->created_at }}</td>
                                        <td>
                                            <button
                                                class="btn btn-sm btn-outline-primary me-1 btn-edit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editOpModal"
                                                data-id="{{ $u->id }}"
                                                data-name="{{ $u->name }}"
                                                data-email="{{ $u->email }}"
                                                data-role="{{ $u->role }}"
                                                data-outlet_ids="{{ $u->outlet_ids }}"
                                            >
                                                <i class="bi bi-pencil-square me-1"></i> Ubah
                                            </button>

                                            <form action="{{ route('investor.user.operasional.delete', $u->id) }}"
                                                  method="POST"
                                                  class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="small text-muted mt-2">
                        Tips: gunakan pencarian DataTables (kanan atas) untuk cepat menemukan user operasional.
                    </div>
                </div>
            </div>

            {{-- ===================== MODAL: TAMBAH USER OPERASIONAL ===================== --}}
            <div class="modal fade" id="addOpModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('investor.user.operasional.store') }}" method="POST" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah User Operasional</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" name="name" class="form-control" placeholder="Nama user" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="crew">Crew</option>
                                    <option value="leader">Leader</option>
                                    <option value="spv">SPV</option>
                                    <option value="tm_manager">TM Manager</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Outlet (bisa pilih banyak)</label>
                                <select name="outlet_ids[]" class="form-select select2-outlet" multiple required>
                                    @foreach ($outlets as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_outlet }}</option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1">Minimal pilih 1 outlet.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
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

            {{-- ===================== MODAL: EDIT USER OPERASIONAL ===================== --}}
            <div class="modal fade" id="editOpModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form id="editOpForm" method="POST" class="modal-content">
                        @csrf
                        @method('POST')

                        <div class="modal-header">
                            <h5 class="modal-title">Edit User Operasional</h5>
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
                                    <option value="crew">Crew</option>
                                    <option value="leader">Leader</option>
                                    <option value="spv">SPV</option>
                                    <option value="tm_manager">TM Manager</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Outlet (bisa pilih banyak)</label>
                                <select name="outlet_ids[]" id="edit-outlet" class="form-select select2-outlet-edit" multiple required>
                                    @foreach ($outlets as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_outlet }}</option>
                                    @endforeach
                                </select>
                                <div class="small text-muted mt-1">Minimal pilih 1 outlet.</div>
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

    // SweetAlert: flash messages
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

    // DataTables
    $('#opTable').DataTable({
        pageLength: 10,
        ordering: true,
        autoWidth: false
    });

    // Select2 (modal parent penting)
    $('.select2-outlet').select2({
        dropdownParent: $('#addOpModal'),
        width: '100%'
    });

    $('.select2-outlet-edit').select2({
        dropdownParent: $('#editOpModal'),
        width: '100%'
    });

    // Fill edit modal + set action
    const editModal = document.getElementById('editOpModal');
    const editForm  = document.getElementById('editOpForm');

    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');

        document.getElementById('edit-name').value  = button.getAttribute('data-name') || '';
        document.getElementById('edit-email').value = button.getAttribute('data-email') || '';
        document.getElementById('edit-role').value  = button.getAttribute('data-role') || 'crew';

        // outlet_ids: "1,3,5"
        const outletIdsStr = button.getAttribute('data-outlet_ids') || '';
        const outletIdsArr = outletIdsStr
            .split(',')
            .map(s => s.trim())
            .filter(s => s.length > 0);

        $('#edit-outlet').val(outletIdsArr).trigger('change');

        editForm.action = `/investor/user/operasional/update/${id}`;
    });

    // SweetAlert confirm delete
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
});
</script>

@include('Temp.Investor.footer')
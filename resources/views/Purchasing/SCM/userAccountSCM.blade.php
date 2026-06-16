@include('Temp.Investor.header')

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --bg-main: #f8fafc;
        --card-bg: #ffffff;
        --primary-theme: #4f46e5;
        --border-color: #f1f5f9;
        --text-main: #0f172a;
        --text-muted: #64748b;
    }

    body {
        background-color: var(--bg-main);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .page-wrap { 
        padding: 24px 0; 
    }

    /* PREMIUM CARD CLEAN DESIGN */
    .card-clean { 
        border: 1px solid var(--border-color); 
        border-radius: 16px; 
        overflow: hidden; 
        background: var(--card-bg);
    }
    
    .card-clean .card-header { 
        background: #ffffff; 
        border-bottom: 1px solid var(--border-color); 
        padding: 20px 24px !important;
    }

    .title { 
        font-weight: 800; 
        font-size: 1.25rem;
        letter-spacing: -0.5px; 
        color: var(--text-main);
    }

    .subtle { 
        color: var(--text-muted); 
        font-size: .85rem; 
        margin-top: 2px;
    }

    /* MODERN HOVER INTERACTIONS ON BUTTONS */
    .btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.45rem 1rem;
        font-size: 0.85rem;
        transition: all 0.2s ease-in-out;
    }

    .btn-soft { 
        border: 1px solid #e2e8f0; 
        background: #ffffff; 
        color: #475569;
    }

    .btn-soft:hover { 
        background: #f8fafc; 
        color: var(--text-main);
        border-color: #cbd5e1;
    }

    .btn-success {
        background-color: #16a34a;
        border-color: #16a34a;
    }

    .btn-success:hover {
        background-color: #15803d;
        border-color: #15803d;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
    }

    /* TABLE LAYOUT INTEGRATION */
    .table-responsive {
        padding: 12px;
    }

    .table {
        width: 100% !important;
    }

    .table thead th { 
        white-space: nowrap; 
        background-color: #f8fafc !important;
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 16px !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    .table tbody td {
        padding: 14px 16px !important;
        color: #334155;
        font-size: 0.88rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color) !important;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(79, 70, 229, 0.02) !important;
        transition: background-color 0.15s ease;
    }

    .toolbar { 
        display: flex; 
        gap: 12px; 
        flex-wrap: wrap; 
        align-items: center; 
        justify-content: space-between; 
    }

    /* FORM & ENHANCED MODAL UX */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .modal-header {
        border-bottom: 1px solid var(--border-color);
        padding: 1.25rem 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 1rem 1.5rem;
    }

    .modal .form-label { 
        font-weight: 700; 
        font-size: .75rem; 
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.4rem;
    }

    .form-control, .form-select {
        border-radius: 10px;
        padding: 0.5rem 1rem;
        border: 1px solid #cbd5e1;
        font-size: 0.85rem;
        transition: all 0.15s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-theme);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }

    .badge-role {
        background-color: rgba(79, 70, 229, 0.08);
        color: var(--primary-theme);
        border: 1px solid rgba(79, 70, 229, 0.15);
        font-weight: 600;
        font-size: 11px;
    }

    .badge-dc {
        background-color: #f0fdf4;
        color: #16a34a;
        border: 1px solid rgba(22, 163, 74, 0.15);
        font-weight: 600;
        font-size: 11px;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid page-wrap">

            <div class="card card-clean shadow-sm">
                <div class="card-header">
                    <div class="toolbar">
                        <div>
                            <div class="title">Data User SCM</div>
                            <div class="subtle">Kelola hak akses dan alokasi wilayah operasional admin DC</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-soft d-flex align-items-center" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1.5"></i> Refresh
                            </button>

                            <button class="btn btn-success d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#addOpModal">
                                <i class="bi bi-person-plus me-1.5"></i> Tambah User SCM
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="opTable" class="table table-hover align-middle text-center mb-0">
                            <thead>
                                <tr>
                                    <th style="width:60px" class="text-center">No</th>
                                    <th class="text-start">Nama</th>
                                    <th class="text-start">Email Resmi</th>
                                    <th style="width:130px" class="text-center">Otoritas Role</th>
                                    <th class="text-start">Penempatan DC</th>
                                    <th style="width:170px" class="text-center">Waktu Pendaftaran</th>
                                    <th style="width:160px" class="text-center">Aksi Manajemen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $index => $u)
                                    <tr>
                                        <td class="text-center text-muted fw-bold">{{ $index + 1 }}</td>
                                        <td class="text-start fw-semibold text-dark">{{ $u->name }}</td>
                                        <td class="text-start text-secondary">{{ $u->email }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-role px-2.5 py-1.5 rounded-pill">
                                                {{ strtoupper($u->role) }}
                                            </span>
                                        </td>
                                        <td class="text-start">
                                            @if($u->nama_warehouse)
                                                <span class="badge badge-dc px-2.5 py-1.5 rounded-3 fw-medium">
                                                    <i class="bi bi-building me-1"></i> {{ $u->nama_warehouse }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-secondary small">{{ $u->created_at }}</td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary d-inline-flex align-items-center btn-edit"
                                                    data-id="{{ $u->id }}"
                                                    data-name="{{ $u->name }}"
                                                    data-email="{{ $u->email }}"
                                                    data-role="{{ $u->role }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editOpModal">
                                                    <i class="bi bi-pencil-square me-1"></i>
                                                </button>

                                                <form action="{{ route('user.account.scm.delete', $u->id) }}"
                                                      method="POST"
                                                      class="d-inline form-delete">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center">
                                                        <i class="bi bi-trash me-1"></i> 
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
            </div>

            {{-- ===================== MODAL: TAMBAH USER OPERASIONAL ===================== --}}
            <div class="modal fade" id="addOpModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('user.account.scm.store') }}" method="POST" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-dark" style="font-size: 1.05rem;"><i class="bi bi-person-plus me-2 text-success"></i>Tambah Berkas User SCM</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" class="form-control" placeholder="Masukkan nama user..." required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat Email</label>
                                <input type="email" name="email" class="form-control" placeholder="contoh@domain.com" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Otoritas Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="">-- Pilih Akses Role --</option>
                                    <option value="admindc">Admin DC</option>
                                    <option value="purchasing">Purchasing</option>
                                    <option value="finance_scm">Finance SCM</option>
                                    <option value="accounting_scm">Accounting SCM</option>
                                    <option value="distribusi">Distribusi</option>
                                    <option value="qaqc">QA/QC</option>
                                    <option value="superadmin_scm">Superadmin SCM</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alokasi Penempatan DC</label>
                                <select name="warehouse_id" class="form-select select2-outlet" multiple required>
                                    @foreach ($warehouse as $w)
                                        <option value="{{ $w->id }}">{{ $w->nama_warehouse }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kata Sandi (Password)</label>
                                <input type="password" name="password" class="form-control" placeholder="Minimal masukkan 6 karakter" required>
                            </div>
                        </div>

                        <div class="modal-footer bg-light p-2 px-3">
                            <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success px-4 shadow-sm">
                                <i class="bi bi-check2-circle me-1"></i> Daftarkan User
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ===================== MODAL: EDIT USER OPERASIONAL ===================== --}}
            <div class="modal fade" id="editOpModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form id="editOpForm" method="POST" class="modal-content">
                        @csrf
                        @method('POST')

                        <div class="modal-header">
                            <h5 class="modal-title fw-bold text-dark" style="font-size: 1.05rem;"><i class="bi bi-pencil-square me-2 text-primary"></i>Perbarui Akun SCM</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body px-4 py-3">
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="name" id="edit-name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat Email</label>
                                <input type="email" name="email" id="edit-email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Otoritas Role</label>
                                <select name="role" id="edit-role" class="form-select" required>
                                    <option value="">-- Pilih Akses Role --</option>
                                    <option value="admindc">Admin DC</option>
                                    <option value="purchasing">Purchasing</option>
                                    <option value="finance_scm">Finance SCM</option>
                                    <option value="accounting_scm">Accounting SCM</option>
                                    <option value="distribusi">Distribusi</option>
                                    <option value="qaqc">QA/QC</option>
                                    <option value="superadmin_scm">Superadmin SCM</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alokasi Wilayah DC</label>
                                <select name="warehouse_id" id="edit-outlet" class="form-select select2-outlet-edit" multiple required>
                                    @foreach ($warehouse as $w)
                                        <option value="{{ $w->id }}">{{ $w->nama_warehouse }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Perbarui Password (Opsional)</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan kolom jika sandi tidak diganti">
                            </div>
                        </div>

                        <div class="modal-footer bg-light p-2 px-3">
                            <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-save2 me-1"></i> Simpan Perubahan
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
        document.getElementById('edit-role').value  = button.getAttribute('data-role') || 'admindc';

        // outlet_ids: "1,3,5"
        const outletIdsStr = button.getAttribute('data-outlet_ids') || '';
        const outletIdsArr = outletIdsStr
            .split(',')
            .map(s => s.trim())
            .filter(s => s.length > 0);

        $('#edit-outlet').val(outletIdsArr).trigger('change');

        editForm.action = `/user/scm/update/${id}`;
    });

    // SweetAlert confirm delete
    document.querySelectorAll('.form-delete').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Apakah yakin akan menghapus data ini?',
                text: 'Data yang dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
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
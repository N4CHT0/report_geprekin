@include('Temp.Investor.header')

<style>
    :root {
        --primary: #1976d2;
        --border: #e5e7eb;
        --soft: #f8fafc;
    }

    /* ===================== HEADER GRID ===================== */
    .header-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        grid-template-rows: auto auto;
        gap: .5rem 1rem;
        align-items: center;
    }

    .header-title h5 {
        margin: 0;
        font-weight: 600;
    }

    .header-actions {
        display: grid;
        grid-auto-flow: column;
        gap: .5rem;
        justify-content: end;
    }

    .header-actions .btn {
        white-space: nowrap;
    }

    .header-subactions {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(3, minmax(160px, 1fr));
        gap: .5rem;
    }

    /* ===================== FILTER GRID ===================== */
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        grid-template-rows: auto auto;
        gap: .75rem 1rem;
        padding: 1rem;
        background: var(--soft);
        border-radius: 12px;
        border: 1px solid var(--border);
        margin-bottom: 1rem;
    }

    .filter-keyword {
        grid-column: 1 / 3;
    }

    .filter-submit {
        grid-column: 3 / 5;
    }

    /* ===================== TABLE ===================== */
    table th,
    table td {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    /* ===================== SELECT2 FIX ===================== */
    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: .375rem !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: .75rem !important;
    }

    .select2-container .select2-dropdown {
        max-height: 240px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* ===================== RESPONSIVE ===================== */
    @media (max-width: 992px) {
        .header-grid {
            grid-template-columns: 1fr;
        }

        .header-actions {
            grid-auto-flow: row;
        }

        .header-actions .btn,
        .header-subactions .btn {
            width: 100%;
        }

        .header-subactions {
            grid-template-columns: 1fr;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .filter-keyword,
        .filter-submit {
            grid-column: auto;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <div class="header-grid">

                        <div class="header-title">
                            <h5>Data Mitra Investor & Outlet</h5>
                            <small class="text-muted">Manajemen outlet berdasarkan area dan mitra</small>
                        </div>

                        <div class="header-actions">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Outlet
                            </button>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                                <i class="bi bi-upload me-1"></i> Import
                            </button>
                        </div>

                        <div class="header-subactions">
                            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalImportOutlet">
                                <i class="bi bi-upload me-1"></i> Import Outlet
                            </button>
                            <a href="{{ route('outlet.template.download') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-download me-1"></i> Download Template
                            </a>
                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset Filter
                            </a>
                        </div>

                    </div>
                </div>

                <div class="card-body">

                    {{-- ================= FILTER ================= --}}
                    <form method="GET" action="{{ url()->current() }}" class="filter-grid">

                        <div>
                            <label class="form-label fw-semibold">Area</label>
                            <select class="form-select select2-basic" name="area_id" id="filter_area">
                                <option value="all">All Area</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->id }}"
                                        {{ (string) ($areaId ?? '') === (string) $a->id ? 'selected' : '' }}>
                                        {{ $a->nama_area }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label fw-semibold">Mitra</label>
                            <select class="form-select select2-basic" name="mitra_id" id="filter_mitra">
                                <option value="all">All Mitra</option>
                                @foreach ($mitra as $m)
                                    <option value="{{ $m->id }}"
                                        {{ (string) ($mitraId ?? '') === (string) $m->id ? 'selected' : '' }}>
                                        {{ $m->nama_mitra }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select select2-basic" name="status" id="filter_status">
                                <option value="all" {{ ($status ?? 'all') == 'all' ? 'selected' : '' }}>All Status
                                </option>
                                <option value="existing" {{ ($status ?? '') == 'existing' ? 'selected' : '' }}>Existing
                                </option>
                                <option value="go" {{ ($status ?? '') == 'go' ? 'selected' : '' }}>Go</option>
                                <option value="tutup" {{ ($status ?? '') == 'tutup' ? 'selected' : '' }}>Tutup
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100">
                                Reset
                            </a>
                        </div>

                        <div class="filter-keyword">
                            <label class="form-label fw-semibold">Keyword</label>
                            <input type="text" name="q" class="form-control"
                                placeholder="Cari outlet / kode / mitra / area" value="{{ $keyword ?? '' }}">
                        </div>

                        <div class="filter-submit">
                            <label class="form-label fw-semibold">&nbsp;</label>
                            <button class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i> Terapkan Filter
                            </button>
                        </div>
                    </form>

                    {{-- ================= TABLE ================= --}}
                    <div class="table-responsive">
                        <table id="outletTable" class="table table-bordered table-striped align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Outlet</th>
                                    <th>Area</th>
                                    <th>Nama Mitra</th>
                                    <th class="text-start">Nama Outlet</th>
                                    <th>Status</th>
                                    <th style="min-width:160px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $i => $row)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $row->kode_outlet ?? '-' }}</td>
                                        <td>{{ $row->nama_area ?? '-' }}</td>
                                        <td>{{ $row->nama_mitra ?? '-' }}</td>
                                        <td class="text-start">{{ $row->nama_outlet ?? '-' }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $row->status === 'existing' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($row->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                data-bs-target="#editModal" data-id="{{ $row->id }}"
                                                data-kode="{{ $row->kode_outlet }}"
                                                data-nama="{{ $row->nama_outlet }}" data-mitra="{{ $row->mitra_id }}"
                                                data-area="{{ $row->outlet_area_id }}"
                                                data-status="{{ $row->status }}">
                                                Ubah
                                            </button>

                                            <form action="{{ route('outlet.master.delete', $row->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Yakin hapus data ini?')">
                                                    Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- ================= MODAL ADD ================= --}}
            <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('outlet.master.store') }}" method="POST" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Outlet</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Kode Outlet</label>
                                <input type="text" class="form-control" name="kode_outlet" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Area</label>
                                <select class="form-select select2-add" name="area_id" required>
                                    <option value="">-- Pilih Area --</option>
                                    @foreach ($areas as $a)
                                        <option value="{{ $a->id }}">{{ $a->nama_area }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mitra</label>
                                <select class="form-select select2-add" name="mitra_id" required>
                                    <option value="">-- Pilih Mitra --</option>
                                    @foreach ($mitra as $m)
                                        <option value="{{ $m->id }}">{{ $m->nama_mitra }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Outlet</label>
                                <input type="text" class="form-control" name="nama_outlet" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select select2-add" name="status" required>
                                    <option value="existing">Existing</option>
                                    <option value="go">Go</option>
                                    <option value="tutup">Tutup</option>
                                </select>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Outlet</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ================= MODAL EDIT ================= --}}
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('outlet.master.update') }}" method="POST" class="modal-content">
                        @csrf
                        <input type="hidden" name="id" id="edit-id">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Outlet</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Area</label>
                                <select class="form-select select2-edit" name="area_id" id="edit-area" required>
                                    <option value="">-- Pilih Area --</option>
                                    @foreach ($areas as $a)
                                        <option value="{{ $a->id }}">{{ $a->nama_area }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mitra</label>
                                <select class="form-select select2-edit" name="mitra_id" id="edit-mitra" required>
                                    <option value="">-- Pilih Mitra --</option>
                                    @foreach ($mitra as $m)
                                        <option value="{{ $m->id }}">{{ $m->nama_mitra }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kode Outlet</label>
                                <input type="text" class="form-control" name="kode_outlet" id="edit-kode"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Outlet</label>
                                <input type="text" class="form-control" name="nama_outlet" id="edit-nama"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select select2-edit" name="status" id="edit-status" required>
                                    <option value="existing">Existing</option>
                                    <option value="go">Go</option>
                                    <option value="tutup">Tutup</option>
                                </select>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ===== Modal Import (placeholder) ===== --}}
            <div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi bi-upload me-2"></i> Import Data Outlet</h5>
                            <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info mb-0">
                                Form import kamu bisa taruh di sini (tetap bisa dipakai seperti sebelumnya).
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Modal Import Outlet (placeholder) ===== --}}
            <div class="modal fade" id="modalImportOutlet" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="bi bi-upload me-2"></i> Import Outlet</h5>
                            <button type="button" class="btn-close btn-close-white"
                                data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info mb-0">
                                Form import outlet kamu bisa taruh di sini (tetap bisa dipakai seperti sebelumnya).
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        // ===== DataTables =====
        if (document.querySelector('#outletTable')) {
            $('#outletTable').DataTable({
                pageLength: 10,
                ordering: false,
                responsive: true
            });
        }

        // ===== Select2 for FILTER (not modal) =====
        $('.select2-basic').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih...',
            allowClear: false
        });

        // ===== Select2 for ADD modal =====
        $('#addModal').on('shown.bs.modal', function() {
            $('.select2-add').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#addModal'),
            });
        });

        // ===== Select2 for EDIT modal =====
        $('#editModal').on('shown.bs.modal', function() {
            $('.select2-edit').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#editModal'),
            });
        });

        // ===== Fill EDIT Modal =====
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;

            const id = btn.getAttribute('data-id') || '';
            const kode = btn.getAttribute('data-kode') || '';
            const nama = btn.getAttribute('data-nama') || '';
            const mitra = btn.getAttribute('data-mitra') || '';
            const area = btn.getAttribute('data-area') || '';
            const status = btn.getAttribute('data-status') || 'existing';

            document.getElementById('edit-id').value = id;
            document.getElementById('edit-kode').value = kode;
            document.getElementById('edit-nama').value = nama;

            // set status normal
            document.getElementById('edit-status').value = status;

            // select2 needs trigger after initialized. Use small delay to be safe.
            setTimeout(() => {
                $('#edit-mitra').val(mitra).trigger('change');
                $('#edit-area').val(area).trigger('change');
                $('#edit-status').val(status).trigger('change');
            }, 50);
        });

    });
</script>

@include('Temp.Investor.footer')

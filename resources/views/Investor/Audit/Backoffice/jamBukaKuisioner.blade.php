@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Jam Buka Kuisioner</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Jam Buka
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="jamBukaTable"
                            class="table table-striped table-bordered table-sm align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Jam Mulai</th>
                                    <th>Jam Selesai</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jamBuka as $index => $jam)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-truncate" style="max-width: 100px;">{{ $jam->jam_mulai }}</td>
                                        <td class="text-truncate" style="max-width: 100px;">{{ $jam->jam_selesai }}</td>
                                        <td class="text-truncate" style="max-width: 150px;">
                                            {{ \Carbon\Carbon::parse($jam->created_at)->format('d-m-Y H:i') }}</td>
                                        <td>
                                            {{-- Tombol Edit --}}
                                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                data-bs-target="#editModal" data-id="{{ $jam->id }}"
                                                data-jam-mulai="{{ $jam->jam_mulai }}"
                                                data-jam-selesai="{{ $jam->jam_selesai }}"
                                                data-keterangan="{{ $jam->keterangan }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('auditDashboard.jamBuka.destroy', $jam->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash"></i>
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

            {{-- Modal Tambah Jam Buka --}}
            <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <form action="{{ route('auditDashboard.jamBuka.store') }}" method="POST" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addModalLabel">Tambah Jam Buka</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" name="jam_selesai" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal Edit Jam Buka --}}
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <form action="" method="POST" class="modal-content" id="editForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit-id">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Jam Buka</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" name="jam_mulai" id="edit-jam-mulai" class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" name="jam_selesai" id="edit-jam-selesai" class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan" id="edit-keterangan" rows="2" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

@include('Temp.Audit.footer')

<script>
    $(document).ready(function() {
        $('#jamBukaTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });

        // Edit modal populate
        $('#editModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var id = button.data('id');
            var jamMulai = button.data('jam-mulai');
            var jamSelesai = button.data('jam-selesai');
            var keterangan = button.data('keterangan');

            var modal = $(this);
            modal.find('#edit-id').val(id);
            modal.find('#edit-jam-mulai').val(jamMulai);
            modal.find('#edit-jam-selesai').val(jamSelesai);
            modal.find('#edit-keterangan').val(keterangan);

            // Set form action dinamis
            modal.find('#editForm').attr('action', '/jamBukaKuisioner/' + id);
        });

    });
</script>

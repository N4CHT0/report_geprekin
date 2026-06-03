@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Pertanyaan</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Pertanyaan
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="kuisionerTable"
                            class="table table-striped table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Pertanyaan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pertanyaan as $index => $k)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-start">{{ $k->pertanyaan }}</td>
                                    <td>
                                        {{-- Tombol Edit --}}
                                        <button class="btn btn-sm btn-primary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $k->id }}"
                                            data-pertanyaan="{{ $k->pertanyaan }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('auditDashboard.kuisioner.delete', $k->id) }}"
                                            method="POST" id="deleteForm{{ $k->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $k->id }}')">
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

            {{-- Modal Tambah Pertanyaan --}}
            <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <form action="{{ route('auditDashboard.kuisioner.store') }}" method="POST" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addModalLabel">Tambah Pertanyaan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="pertanyaan" class="form-label">Pertanyaan</label>
                                <textarea name="pertanyaan" id="pertanyaan" rows="3" class="form-control" required></textarea>
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="pertanyaan" class="form-label">Jam</label>
                                <input name="jam" id="jam" rows="3" class="form-control" required></input>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal Edit Kuisioner --}}
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <form action="{{ route('auditDashboard.kuisioner.update') }}" method="POST" class="modal-content">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit-id">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Kuisioner</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit-pertanyaan" class="form-label">Pertanyaan</label>
                                <textarea name="pertanyaan" id="edit-pertanyaan" rows="3" class="form-control" required></textarea>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="pertanyaan" class="form-label">Jam</label>
                                    <input name="jam" id="jam" rows="3" class="form-control" required></input>
                                </div>
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
        $('#kuisionerTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });
</script>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika user klik "Ya", maka form di-submit
                document.getElementById('deleteForm' + id).submit();
            }
        })
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            let button = event.relatedTarget;

            document.getElementById('edit-id').value = button.getAttribute('data-id');
            document.getElementById('edit-pertanyaan').value = button.getAttribute('data-pertanyaan');
            document.getElementById('edit-jam-mulai').value = button.getAttribute('data-jam-mulai');
            document.getElementById('edit-jam-selesai').value = button.getAttribute('data-jam-selesai');
        });
    });

    $('#editModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        var idJam = button.data('id-jam'); // tambahkan data-id-jam di tombol edit

        var modal = $(this);
        modal.find('#edit-id').val(id);
        modal.find('#edit-id-jam-dibuka').val(idJam);

        // Set form action
        modal.find('#editForm').attr('action', '/backOffice/jamBuka/' + id);
    });
</script>
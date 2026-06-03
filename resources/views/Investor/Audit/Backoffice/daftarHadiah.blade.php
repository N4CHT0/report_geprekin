@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
 <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Hadiah</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
                        <i class="bi bi-plus-circle"></i> Tambah Hadiah
                    </button>
                </div>
</div>
                

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="hadiahTable" class="table table-striped table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Hadiah</th>
                                    <th>Tipe</th>
                                    <th>Poin Dibutuhkan</th>
                                    <th>Deskripsi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($hadiah as $index => $h)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $h->nama_hadiah }}</td>
                                        <td>{{ ucfirst($h->tipe) }}</td>
                                        <td>{{ $h->poin_dibutuhkan }}</td>
                                        <td>{{ $h->deskripsi }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success edit-hadiah" data-id="{{ $h->id }}"
                                                data-nama="{{ $h->nama_hadiah }}" data-tipe="{{ $h->tipe }}"
                                                data-poin="{{ $h->poin_dibutuhkan }}" data-deskripsi="{{ $h->deskripsi }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            <form action="{{ route('hadiah.destroy', $h->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus hadiah ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted">Belum ada daftar hadiah.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@include('Temp.Audit.footer')

{{-- ✅ Modal Tambah/Edit --}}
<div class="modal fade" id="tambahModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formHadiah" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Hadiah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="mb-3">
                        <label>Nama Hadiah</label>
                        <input type="text" class="form-control" name="nama_hadiah" id="nama_hadiah" required>
                    </div>
                    <div class="mb-3">
                        <label>Tipe</label>
                        <select class="form-select" name="tipe" id="tipe" required>
                            <option value="voucher">Voucher</option>
                            <option value="transfer">Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Poin Dibutuhkan</label>
                        <input type="number" class="form-control" name="poin_dibutuhkan" id="poin_dibutuhkan" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="deskripsi"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    $('#hadiahTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 10,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { previous: "Sebelumnya", next: "Berikutnya" },
            emptyTable: "Belum ada daftar hadiah"
        }
    });

    // Edit Hadiah
    document.querySelectorAll('.edit-hadiah').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const form = document.getElementById('formHadiah');
            form.action = `/hadiah/${id}`;
            form.querySelector('#formMethod').value = 'PUT';
            form.querySelector('#nama_hadiah').value = this.dataset.nama;
            form.querySelector('#tipe').value = this.dataset.tipe;
            form.querySelector('#poin_dibutuhkan').value = this.dataset.poin;
            form.querySelector('#deskripsi').value = this.dataset.deskripsi;
            new bootstrap.Modal(document.getElementById('tambahModal')).show();
        });
    });

    // Reset form modal saat ditutup
    const tambahModal = document.getElementById('tambahModal');
    tambahModal.addEventListener('hidden.bs.modal', () => {
        const form = document.getElementById('formHadiah');
        form.action = "/hadiah";
        form.querySelector('#formMethod').value = 'POST';
        form.reset();
    });
});
</script>

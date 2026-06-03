@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Responden</h5>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Kuisioner
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="respondenTable"
                            class="table table-striped table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Lengkap</th>
                                    <th>No. Telp</th>
                                    <th>Bank / Rekening</th>
                                    <th>Foto</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Tabel Responden --}}
                                @foreach ($respondens as $index => $r)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $r->nama_lengkap }}</td>
                                    <td>{{ $r->nomor_telp ?? '-' }}</td>
                                    <td>{{ $r->jenis_bank ?? '-' }} / {{ $r->nomor_rekening ?? '-' }}</td>
                                    <td>
                                        @if ($r->foto_user)
                                            <img src="{{ asset('/' . $r->foto_user) }}" alt="Foto" width="100">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d-m-Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                            data-bs-target="#editModal"
                                            data-id="{{ $r->id }}"
                                            data-nama="{{ $r->nama_lengkap }}"
                                            data-username="{{ $r->username }}"
                                            data-telp="{{ $r->nomor_telp }}"
                                            data-bank="{{ $r->jenis_bank }}"
                                            data-rek="{{ $r->nomor_rekening }}"
                                            data-foto="{{ $r->foto_user }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <form action="{{ route('responden.destroy', $r->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus?')">
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

            {{-- Modal Tambah --}}
            <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    {{-- Modal Tambah Responden --}}
                    <form action="{{ route('responden.store') }}" method="POST" enctype="multipart/form-data"
                        class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah Responden</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" name="nama_lengkap" placeholder="Nama Lengkap"
                                class="form-control mb-2" required>
                            <input type="text" name="nomor_telp" placeholder="Nomor Telp" class="form-control mb-2">

                            <div class="mb-3">
                                <label for="jenis_bank" class="form-label">Bank</label>
                                <select name="jenis_bank" id="jenis_bank" class="form-select" required>
                                    <option value="">-- Pilih Bank --</option>
                                    <option value="Mandiri">Bank Mandiri</option>
                                    <option value="BNI">BNI (Bank Negara Indonesia)</option>
                                    <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                                    <option value="BCA">BCA (Bank Central Asia)</option>
                                    <option value="BTN">BTN (Bank Tabungan Negara)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
                                <input type="text" name="nomor_rekening" id="nomor_rekening" class="form-control"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="foto_user" class="form-label">Foto</label>
                                <input type="file" name="foto_user" id="foto_user" class="form-control"
                                    accept="image/*">
                                <img id="preview_foto" src="#" alt="Preview Foto" width="200"
                                    style="display:none;">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>

                    <script>
                        const bankPrefixes = {
                            "Mandiri": "1",
                            "BNI": "2",
                            "BRI": "3",
                            "BCA": "4",
                            "BTN": "5"
                        };

                        document.getElementById('jenis_bank').addEventListener('change', function() {
                            const nomorInput = document.getElementById('nomor_rekening');
                            const bank = this.value;
                            const prefix = bankPrefixes[bank] || '';
                            nomorInput.value = prefix;
                            nomorInput.focus();
                        });

                        // Preview foto
                        document.getElementById('foto_user').addEventListener('change', function(e) {
                            const preview = document.getElementById('preview_foto');
                            const file = e.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    preview.src = e.target.result;
                                    preview.style.display = 'block';
                                }
                                reader.readAsDataURL(file);
                            } else {
                                preview.style.display = 'none';
                            }
                        });
                    </script>
                </div>
            </div>

            {{-- Modal Edit Responden --}}
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <form action="" method="POST" enctype="multipart/form-data" class="modal-content" id="editForm">
                        @csrf
                        @method('PUT')

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Responden</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit-id">

                            <div class="mb-2">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" id="edit-nama" class="form-control" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" id="edit-username" class="form-control" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Nomor Telp</label>
                                <input type="text" name="nomor_telp" id="edit-telp" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Bank</label>
                                <select name="jenis_bank" id="edit-bank" class="form-select" required>
                                    <option value="">-- Pilih Bank --</option>
                                    <option value="Mandiri">Bank Mandiri</option>
                                    <option value="BNI">BNI</option>
                                    <option value="BRI">BRI</option>
                                    <option value="BCA">BCA</option>
                                    <option value="BTN">BTN</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor Rekening</label>
                                <input type="text" name="nomor_rekening" id="edit-rek" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto_user" id="edit-foto" class="form-control mb-2" accept="image/*">
                                <img id="preview-foto-edit" src="" alt="Preview Foto" width="200" style="display:none;">
                            </div>

                            <div class="mb-2">
                                <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                <input type="password" name="password" id="edit-password" class="form-control">
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
                document.addEventListener("DOMContentLoaded", function() {
                    const editModal = document.getElementById('editModal');
                    const previewFoto = document.getElementById('preview-foto-edit');

                    editModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;

                        const id = button.getAttribute('data-id');
                        const nama = button.getAttribute('data-nama');
                        const telp = button.getAttribute('data-telp');
                        const bank = button.getAttribute('data-bank');
                        const rek = button.getAttribute('data-rek');
                        const foto = button.getAttribute('data-foto');

                        document.getElementById('edit-id').value = id;
                        document.getElementById('edit-nama').value = nama;
                        document.getElementById('edit-telp').value = telp;
                        document.getElementById('edit-bank').value = bank;
                        document.getElementById('edit-rek').value = rek;

                        if (foto) {
                            previewFoto.src = '{{ asset('storage') }}/' + foto;
                            previewFoto.style.display = 'block';
                        } else {
                            previewFoto.style.display = 'none';
                        }

                        // Set form action dinamis
                        document.getElementById('editForm').action = '/responden/' + id;
                    });

                    // Preview foto saat pilih file baru
                    document.getElementById('edit-foto').addEventListener('change', function(event) {
                        const file = event.target.files[0];
                        if (file) {
                            previewFoto.src = URL.createObjectURL(file);
                            previewFoto.style.display = 'block';
                        }
                    });
                });
            </script>

{{-- Script Modal Edit --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
    const editModal = document.getElementById('editModal');
    const previewFoto = document.getElementById('preview-foto-edit');

    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;

        const id = button.getAttribute('data-id');
        const nama = button.getAttribute('data-nama');
        const username = button.getAttribute('data-username');
        const telp = button.getAttribute('data-telp');
        const bank = button.getAttribute('data-bank');
        const rek = button.getAttribute('data-rek');
        const foto = button.getAttribute('data-foto');

        document.getElementById('edit-id').value = id;
        document.getElementById('edit-nama').value = nama;
        document.getElementById('edit-username').value = username;
        document.getElementById('edit-telp').value = telp;
        document.getElementById('edit-bank').value = bank;
        document.getElementById('edit-rek').value = rek;

       if (foto) {
    // ambil nama file saja
    const fileName = foto.split('/').pop();
    previewFoto.src = '/audit/foto_registrasi/' + fileName;
        previewFoto.style.display = 'block';
    } else {
        previewFoto.style.display = 'none';
    }
        document.getElementById('editForm').action = '{{ url("responden/update") }}/' + id;
    });

    // Preview foto saat pilih file baru
    document.getElementById('edit-foto').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            previewFoto.src = URL.createObjectURL(file);
            previewFoto.style.display = 'block';
        }
    });
    });
</script>
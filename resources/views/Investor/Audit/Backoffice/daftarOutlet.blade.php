@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Outlet</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Outlet
                            </button>
                            <form action="{{ route('outlet.importOutletKuisioner') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="input-group">
                                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv"
                                        required>
                                    <button class="btn btn-success btn-sm" type="submit">
                                        <i class="bi bi-upload me-1"></i> Import Outlet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="outletTable"
                            class="table table-striped table-bordered table-sm align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Outlet</th>
                                    <th>Alamat</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($outlets as $index => $o)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="text-truncate" style="max-width: 150px;">{{ $o->nama_outlet }}</td>
                                        <td class="text-truncate" style="max-width: 200px;">{{ $o->alamat }}</td>
                                        <td>{{ $o->latitude ?? '-' }}</td>
                                        <td>{{ $o->longitude ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($o->created_at)->format('d-m-Y H:i') }}</td>
                                        <td>
                                            {{-- Tombol Edit --}}
                                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                                data-bs-target="#editModal" data-id="{{ $o->id }}"
                                                data-nama="{{ $o->nama_outlet }}" data-alamat="{{ $o->alamat }}"
                                                data-latitude="{{ $o->latitude }}"
                                                data-longitude="{{ $o->longitude }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>

                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('auditDashboard.outlet.destroy', $o->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Yakin hapus?')">
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

            {{-- Modal Tambah Outlet --}}
            <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <form action="{{ route('auditDashboard.outlet.store') }}" method="POST" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addModalLabel">Tambah Outlet</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Outlet</label>
                                <input type="text" name="nama_outlet" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <div class="input-group">
                                    <textarea name="alamat" rows="2" class="form-control" id="alamat"></textarea>
                                    <button type="button" class="btn btn-outline-secondary" id="convertBtn">
                                        <i class="bi bi-geo-alt"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Latitude</label>
                                    <input type="text" name="latitude" id="latitude" class="form-control" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Longitude</label>
                                    <input type="text" name="longitude" id="longitude" class="form-control" readonly>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                document.getElementById('convertBtn').addEventListener('click', function() {
                    const alamat = document.getElementById('alamat').value;
                    if (!alamat) {
                        alert('Alamat belum diisi!');
                        return;
                    }

                    // Pakai Nominatim OpenStreetMap
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(alamat)}`, {
                            headers: {
                                "Accept-Language": "id",
                                "User-Agent": "MyApp/1.0" // penting agar tidak ditolak
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                document.getElementById('latitude').value = data[0].lat;
                                document.getElementById('longitude').value = data[0].lon;
                            } else {
                                alert('Alamat tidak ditemukan!');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Gagal mengambil koordinat.');
                        });
                });
            </script>
            {{-- Modal Edit Outlet --}}
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <form action="" method="POST" class="modal-content" id="editForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit-id">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Outlet</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Outlet</label>
                                <input type="text" name="nama_outlet" id="edit-nama" class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" id="edit-alamat" rows="2" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="latitude" id="edit-latitude" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="longitude" id="edit-longitude" class="form-control">
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
        $('#editModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Tombol yang memicu modal
            var id = button.data('id');
            var nama = button.data('nama');
            var alamat = button.data('alamat');
            var latitude = button.data('latitude');
            var longitude = button.data('longitude');

            // Isi value ke form
            var modal = $(this);
            modal.find('#edit-id').val(id);
            modal.find('#edit-nama').val(nama);
            modal.find('#edit-alamat').val(alamat);
            modal.find('#edit-latitude').val(latitude);
            modal.find('#edit-longitude').val(longitude);

            // Gunakan route helper (Laravel Blade) untuk update
            let actionUrl = "{{ route('auditDashboard.outlet.update', ':id') }}";
            actionUrl = actionUrl.replace(':id', id);

            modal.find('#editForm').attr('action', actionUrl);
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#outletTable').DataTable({
            responsive: true, // membuat table responsive
            autoWidth: false, // matikan autoWidth
            pageLength: 10, // default 10 data per halaman
            lengthMenu: [10, 25, 50, 100, 250, 500], // dropdown show X entries
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                zeroRecords: "Data tidak ditemukan"
            }
        });
    });
</script>

<script>
    $('#convertBtn').click(function() {
        var alamat = $('#alamat').val().trim();
        if (!alamat) {
            alert("Alamat masih kosong!");
            return;
        }

        // Encode alamat untuk URL
        var url = "https://nominatim.openstreetmap.org/search?format=json&q=" + encodeURIComponent(alamat);

        // AJAX request
        $.getJSON(url, function(data) {
            if (data && data.length > 0) {
                $('#latitude').val(data[0].lat);
                $('#longitude').val(data[0].lon);
            } else {
                alert("Alamat tidak ditemukan, coba lebih umum atau koreksi penulisan.");
                $('#latitude').val('');
                $('#longitude').val('');
            }
        }).fail(function() {
            alert("Terjadi kesalahan saat mencari koordinat.");
        });
    });
</script>

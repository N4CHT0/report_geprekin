@include('Temp.Audit.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0 pt-3">
                    <h5 class="card-title text-primary fw-bold">
                        <i class="bi bi-box-seam me-2"></i> Laporan Audit Harian
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('audit.laporan') }}" method="GET">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Rentang Tanggal</label>
                                <div class="input-group input-group-sm">
                                    <input type="date" name="tgl_awal" class="form-control" value="{{ request('tgl_awal') }}">
                                    <span class="input-group-text">s/d</span>
                                    <input type="date" name="tgl_akhir" class="form-control" value="{{ request('tgl_akhir') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold">Outlet</label>
                                <select name="outlet" id="outlet" class="form-control select2">
                                    <option value="">-- Semua Outlet --</option>
                                    @foreach($outlets as $o)
                                    <option value="{{ $o->nama_outlet }}" {{ request('outlet') == $o->nama_outlet ? 'selected' : '' }}>
                                        {{ $o->nama_outlet }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Responden</label>
                                <input type="text" name="responden" class="form-control form-control-sm" placeholder="Cari nama..." value="{{ request('responden') }}">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    <i class="bi bi-search"></i> Terapkan Filter
                                </button>
                                <a href="{{ route('audit.laporan') }}" class="btn btn-outline-secondary btn-sm px-4">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-5">
                    <!-- TABLE -->
                    <div class="table-responsive" style="min-height:65vh;">

                        <table id="reportTable"
                            class="table table-striped table-bordered align-middle text-center mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Responden</th>
                                    <th class="text-center">Outlet</th>
                                    <th class="text-center">Pertanyaan</th>
                                    <th class="text-center">Jawaban</th>
                                    <th class="text-center">Alasan</th>
                                    <!-- <th class="text-center">Foto</th> -->
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $d)
                                <tr>
                                    <td class="text-center">{{ $loop -> iteration }}</td>
                                    <td class="text-nowrap">{{ $d->tanggal }}</td>
                                    <td class="text-start">{{ DB::table('tbl_user_responden')->where('id', $d->id_responden)->value('nama_lengkap') }}</td>
                                    <td class="text-start">{{ $d->nama_outlet }}</td>
                                    <td class="text-start">{{ $d->pertanyaan }}</td>
                                    <td class="">{{ $d->jawaban }}</td>
                                    <td class="text-center">
                                        @if($d->alasan)
                                        {{ $d->alasan }}
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <!-- <td class="text-center">
                                        @if ($d->foto)
                                        <img src="{{ asset('/' . $d->foto) }}" alt="Foto" width="100">
                                        @else
                                        -
                                        @endif
                                    </td> -->
                                    <td class="text-center text-wrap">
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $d->id }}">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        <form action="{{ route('audit.delete', $d->id) }}" method="POST" id="deleteForm{{ $d->id }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('{{ $d->id }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @foreach($data as $d)
                        <!-- MODAL VIEW -->
                        <div class="modal fade" id="detailModal{{ $d->id }}" tabindex="-1" aria-labelledby="detailLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title" id="detailLabel">Detail Daily Check Report {{ $d->nama_outlet }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Tanggal</th>
                                                <td>{{ $d->tanggal }}</td>
                                            </tr>
                                            <tr>
                                                <th>Pertanyaan</th>
                                                <td>{{ $d->pertanyaan }}</td>
                                            </tr>
                                            <tr>
                                                <th>Jawaban</th>
                                                <td>{{ $d->jawaban }}</td>
                                            </tr>
                                            <tr>
                                                <th>Alasan</th>
                                                <td>{{ $d->alasan ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Foto Bukti</th>
                                                <td>
                                                    @if($d->foto)
                                                    <img src="{{ asset($d->foto) }}" class="img-fluid rounded" style="max-height: 300px;">
                                                    @else
                                                    Tidak ada foto
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
</main>

@include('Temp.Audit.footer')

<script>
    $(document).ready(function() {
        $('#outlet').select2({
            placeholder: "Cari Outlet...",
            allowClear: true,
            width: '100%',
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#reportTable').DataTable({
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
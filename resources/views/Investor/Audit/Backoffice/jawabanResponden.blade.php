@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            {{-- 📋 Tabel Jawaban Responden --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Jawaban Responden</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="jawabanRespondenTable"
                            class="table table-striped table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Responden</th>
                                    <th>Outlet</th>
                                    <th>Pertanyaan / Jawaban</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jawabanResponden as $index => $j)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $j->name }}</td>
                                        <td>{{ $j->nama_outlet }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info view-detail"
                                                data-id="{{ $j->id }}">
                                                <i class="bi bi-eye"></i> Lihat Jawaban
                                            </button>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($j->created_at)->format('d-m-Y H:i') }}</td>
                                        <td>
                                            <form action="{{ route('jawabanResponden.destroy', $j->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Yakin hapus data ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted">Belum ada data responden.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 🔍 Modal Detail Jawaban --}}
            <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Detail Jawaban Responden</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="detailContent" class="table-responsive text-start">
                                <p class="text-muted">Memuat data...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@include('Temp.Audit.footer')

{{-- ✅ SCRIPT --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // 🔹 Inisialisasi DataTables
        $('#jawabanRespondenTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                emptyTable: "Tidak ada data responden yang tersedia",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                }
            }
        });

        // 🔹 Klik tombol "Lihat Jawaban"
        document.querySelectorAll('.view-detail').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const modal = new bootstrap.Modal(document.getElementById('detailModal'));
                const content = document.getElementById('detailContent');

                content.innerHTML = '<p class="text-muted">Memuat data...</p>';
                modal.show();

                fetch(`/jawaban-responden/${id}/detail`)
                    .then(res => res.text())
                    .then(html => content.innerHTML = html)
                    .catch(() => content.innerHTML =
                        '<p class="text-danger">Gagal memuat data.</p>');
            });
        });
    });
</script>

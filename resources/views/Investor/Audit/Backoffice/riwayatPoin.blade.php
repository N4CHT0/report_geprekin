@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="card shadow-sm border-0">

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="riwayatPoinTable"
                            class="table table-striped table-bordered align-middle text-center table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Responden</th>
                                    <th>Email</th>
                                    <th>Jumlah Poin</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($riwayatPoin as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $p->nama_responden ?? 'Tidak Diketahui' }}</td>
                                        <td>{{ $p->email ?? '-' }}</td>
                                        <td>{{ $p->jumlah_poin }}</td>
                                        <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d-m-Y H:i') }}</td>
                                        <td>
                                            <form action="{{ route('jawabanResponden.destroy', $p->id) }}"
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
                                        <td colspan="6" class="text-muted">Belum ada data riwayat poin.</td>
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

<script>
    document.addEventListener("DOMContentLoaded", () => {
        $('#riwayatPoinTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                paginate: {
                    previous: "Sebelumnya",
                    next: "Berikutnya"
                },
                emptyTable: "Belum ada data riwayat poin"
            }
        });
    });
</script>

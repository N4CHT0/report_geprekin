@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0">Laporan Pencairan Poin</h5>
                    
                            <div class="text-end w-100 w-md-auto">
                                <a href="{{ route('laporan.pencairan.export') }}"
                                   class="btn btn-success btn-sm">
                                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="laporanPencairanTable"
                            class="table table-striped table-bordered align-middle text-center nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Responden</th>
                                    <th>Kode Redeem</th>
                                    <th>Email</th>
                                    <th>No. Telp</th>
                                    <th>Hadiah</th>
                                    <th>Tipe Hadiah</th>
                                    <th>Jumlah Poin</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Expired Date</th>
                                    <th>Tanggal Pencairan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pencairan as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $p->nama_responden }}</td>
                                        <td>{{ $p->kode_reedem ?? '-' }}</td>
                                        <td>{{ $p->email ?? '-' }}</td>
                                        <td>{{ $p->nomor_telp ?? '-' }}</td>
                                        <td>{{ $p->nama_hadiah ?? '-' }}</td>
                                        <td>{{ ucfirst($p->tipe) }}</td>
                                        <td>{{ number_format($p->jumlah_poin, 0, ',', '.') }}</td>
                                        <td>{{ $p->metode }}</td>
                                        <td>{{ ucfirst($p->status) }}</td>
                                        <td>
                                            @if ($p->expired_date)
                                                {{ \Carbon\Carbon::parse($p->expired_date)->format('d-m-Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d-m-Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-muted">Belum ada data pencairan poin.</td>
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
        $('#laporanPencairanTable').DataTable({
            responsive: true,
            scrollX: true,
            autoWidth: false,
            pageLength: 10,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                paginate: { previous: "Sebelumnya", next: "Berikutnya" },
                emptyTable: "Belum ada data pencairan poin"
            }
        });
    });
</script>
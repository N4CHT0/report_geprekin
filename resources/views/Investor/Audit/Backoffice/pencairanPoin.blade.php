@include('Temp.Audit.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">
            <div class="card shadow-sm border-0">

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
                        <table id="pencairanTable" class="table table-striped table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Redeem</th>
                                    <th>Responden</th>
                                    <th>Email</th>
                                    <th>No. Telp</th>
                                    <th>Hadiah</th>
                                    <th>Tipe Hadiah</th>
                                    <th>Poin Hadiah</th>
                                    <th>Jumlah Poin Ditukar</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th>Expired Date</th>
                                    <th>Tanggal Pencairan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pencairan as $index => $p)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $p->kode_reedem ?? '-' }}</td>
                                        <td>{{ $p->nama_responden }}</td>
                                        <td>{{ $p->email ?? '-' }}</td>
                                        <td>{{ $p->nomor_telp ?? '-' }}</td>
                                        <td>{{ $p->nama_hadiah ?? '-' }}</td>
                                        <td>{{ ucfirst($p->tipe_hadiah ?? '-') }}</td>
                                        <td>{{ $p->poin_dibutuhkan ?? '-' }}</td>
                                        <td>{{ $p->jumlah_poin }}</td>
                                        <td>{{ ucfirst($p->metode) }}</td>

                                        {{-- STATUS DROPDOWN --}}
                                        <td>
                                            <form action="{{ route('pencairanPoin.update', $p->id) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('PUT')

                                                @php
                                                    $statusColor = [
                                                        'pending'  => ['#b08900', '#fff3cd'],
                                                        'approved' => ['#0f5132', '#d1e7dd'],
                                                        'rejected' => ['#842029', '#f8d7da'],
                                                    ];
                                                    $color = $statusColor[$p->status][0] ?? '#000';
                                                    $bg    = $statusColor[$p->status][1] ?? '#fff';
                                                @endphp

                                                <select name="status"
                                                        class="form-select form-select-sm"
                                                        onchange="this.form.submit()"
                                                        style="width:130px; font-weight:600; color: {{ $color }}; background-color: {{ $bg }};">
                                                    <option value="pending"  {{ $p->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="approved" {{ $p->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="rejected" {{ $p->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </form>
                                        </td>

                                        {{-- EXPIRED DATE --}}
                                        <td>
                                            @if($p->expired_date)
                                                {{ \Carbon\Carbon::parse($p->expired_date)->format('d-m-Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        {{-- CREATED AT --}}
                                        <td>
                                            {{ \Carbon\Carbon::parse($p->created_at)->format('d-m-Y H:i') }}
                                        </td>

                                        {{-- AKSI --}}
                                        <td>
                                            <form action="{{ route('pencairanPoin.destroy', $p->id) }}"
                                                  method="POST"
                                                  class="d-inline">
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
                                        <td colspan="14" class="text-muted">Belum ada data pencairan poin.</td>
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
        $('#pencairanTable').DataTable({
            responsive: true,
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

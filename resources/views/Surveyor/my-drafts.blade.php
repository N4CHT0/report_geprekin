
@section('title', 'Draft & Revisi Saya')
@section('breadcrumb', 'Site Score Outlet / Draft & Revisi Saya')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

<div class="worksheet-page">

    <div class="worksheet-hero">
        <div>
            <div class="worksheet-kicker">
                <i class="bi bi-file-earmark-text"></i>
                Tugas Surveyor
            </div>
            <h1>Draft & Revisi Saya</h1>
            <p>
                Daftar Site Score yang Anda simpan sebagai Draft atau yang dikembalikan oleh Manajemen untuk direvisi.
            </p>
        </div>
        <div class="worksheet-actions">
            <a href="{{ route('investor.surveyor.candidate.assignment') }}" class="btn-worksheet-light">
                <i class="bi bi-list-task"></i>
                Lihat Penugasan Baru
            </a>
            <a href="{{ route('investor.surveyor.site-score.create') }}" class="btn-worksheet">
                <i class="bi bi-plus-circle"></i>
                Input Site Score Baru
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="worksheet-card h-100">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Data Draft & Revisi</h5>
                        <p>Silakan klik "Lanjutkan" untuk melengkapi data form.</p>
                    </div>
                </div>

                <div class="worksheet-card-body">
                    <div class="worksheet-table-wrap">
                        <div class="table-responsive">
                            <table class="table worksheet-table align-middle" id="draftTable" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Lokasi</th>
                                        <th>Kota</th>
                                        <th>Tanggal Survey</th>
                                        <th>Status</th>
                                        <th>Catatan Terakhir</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scores as $row)
                                        <tr>
                                            <td class="fw-bold text-nowrap">{{ $row->kode_score ?? '-' }}</td>
                                            <td class="fw-bold" style="min-width: 150px;">{{ $row->lokasi ?? '-' }}</td>
                                            <td style="min-width: 120px;">{{ $row->kota ?? '-' }}</td>
                                            <td class="text-nowrap">{{ $row->tanggal_survey ?? '-' }}</td>
                                            <td class="text-nowrap">
                                                @if($row->workflow_status === 'REVISION')
                                                    <span class="badge bg-danger">REVISI</span>
                                                @else
                                                    <span class="badge bg-secondary">DRAFT</span>
                                                @endif
                                            </td>
                                            <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                @if($row->workflow_status === 'REVISION')
                                                    <span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> {{ $row->approval_note ?? 'Perlu perbaikan.' }}</span>
                                                @else
                                                    <span class="text-muted">{{ $row->catatan ?? '-' }}</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap text-end">
                                                <a href="{{ route('investor.surveyor.site-score.edit', $row->id) }}" class="btn btn-sm btn-primary" style="font-weight: 600; border-radius: 6px;">
                                                    @if($row->workflow_status === 'REVISION')
                                                        <i class="bi bi-tools"></i> Perbaiki Revisi
                                                    @else
                                                        <i class="bi bi-pencil-square"></i> Lanjutkan Draft
                                                    @endif
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($scores->count() === 0)
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-emoji-smile fs-3 mb-2 d-block text-success"></i>
                            Tidak ada Draft atau Revisi yang menunggu.<br>
                            Kerja bagus!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
$(function () {
    if($('#draftTable tbody tr').length > 0 && $('#draftTable tbody tr td').length > 1) {
        $('#draftTable').DataTable({
            pageLength: 10,
            ordering: false,
            autoWidth: false,
            language: {
                emptyTable: 'Belum ada data draft.',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                paginate: {
                    previous: 'Sebelumnya',
                    next: 'Berikutnya'
                }
            }
        });
    }
});
</script>
@endpush

@include('Surveyor.layouts.footer')

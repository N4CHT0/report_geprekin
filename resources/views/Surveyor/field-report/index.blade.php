@section('title', 'Field Report Draft')
@section('breadcrumb', 'Surveyor / Field Report Draft')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

@php $reports = $reports ?? collect(); @endphp

<div class="worksheet-page">
    <div class="worksheet-hero">
        <div>
            <div class="worksheet-kicker"><i class="bi bi-clipboard-data"></i> Draft Review</div>
            <h1>Field Report Draft</h1>
            <p>Semua laporan dari Telegram dan Form Backup masuk ke sini dulu. Admin review lalu approve menjadi final Site Score.</p>
        </div>
        <div class="worksheet-actions">
            <a href="{{ route('investor.surveyor.telegram.form') }}" class="btn-worksheet"><i class="bi bi-chat-dots"></i> Form Backup</a>
        </div>
    </div>

    <div class="worksheet-card">
        <div class="worksheet-card-header"><div><h5>Draft Laporan Surveyor</h5><p>Data masih draft, belum final Site Score.</p></div></div>
        <div class="worksheet-card-body">
            <div class="worksheet-table-wrap"><div class="table-responsive">
                <table class="table worksheet-table align-middle" id="fieldReportTable" style="width:100%;">
                    <thead><tr><th>Tanggal</th><th>Source</th><th>Kode Lokasi</th><th>Nama Lokasi</th><th>Surveyor</th><th>Status</th><th>Detail</th></tr></thead>
                    <tbody>
                    @foreach($reports as $row)
                        <tr>
                            <td>{{ $row->created_at ?? '-' }}</td>
                            <td>{{ $row->source ?? '-' }}</td>
                            <td class="fw-bold">{{ $row->kode_lokasi ?? '-' }}</td>
                            <td>{{ $row->nama_lokasi ?? '-' }}</td>
                            <td>{{ $row->surveyor ?? '-' }}</td>
                            <td><span class="status-pill status-consideration">{{ $row->status ?? '-' }}</span></td>
                            <td><a href="{{ route('investor.surveyor.field-report.detail', $row->id) }}" class="btn btn-sm btn-light fw-bold">Review</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div></div>
            @if($reports->count() === 0)<div class="text-center text-muted py-4">Belum ada field report.</div>@endif
        </div>
    </div>
</div>

@push('scripts')
<script>$(function () { $('#fieldReportTable').DataTable({ ordering:false, pageLength:25, autoWidth:false, scrollX:true }); });</script>
@endpush

@include('Surveyor.layouts.footer')

@section('title', 'Review Field Report')
@section('breadcrumb', 'Surveyor / Field Report / Detail')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

@php $payload = $payload ?? []; @endphp

<div class="worksheet-page">
    <div class="worksheet-hero">
        <div>
            <div class="worksheet-kicker"><i class="bi bi-clipboard-check"></i> Review Draft</div>
            <h1>Review Field Report</h1>
            <p>Periksa payload laporan surveyor. Jika sudah benar, approve menjadi final Site Score.</p>
        </div>
        <div class="worksheet-actions">
            <a href="{{ route('investor.surveyor.field-report.index') }}" class="btn-worksheet-light"><i class="bi bi-arrow-left"></i> Kembali</a>
            @if(empty($report->created_site_score_id))
                <form method="POST" action="{{ route('investor.surveyor.field-report.approve', $report->id) }}">
                    @csrf
                    <button class="btn-worksheet" onclick="return confirm('Approve laporan ini menjadi Site Score final?')"><i class="bi bi-check-circle"></i> Approve to Site Score</button>
                </form>
            @else
                <a href="{{ route('investor.surveyor.site-score.detail', $report->created_site_score_id) }}" class="btn-worksheet"><i class="bi bi-file-earmark-check"></i> Lihat Site Score</a>
            @endif
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-5">
            <div class="worksheet-card h-100">
                <div class="worksheet-card-header"><div><h5>Informasi Report</h5><p>Metadata laporan masuk.</p></div></div>
                <div class="worksheet-card-body">
                    <table class="table excel-table"><tbody>
                        <tr><td class="fw-bold">Source</td><td>{{ $report->source ?? '-' }}</td></tr>
                        <tr><td class="fw-bold">Status</td><td>{{ $report->status ?? '-' }}</td></tr>
                        <tr><td class="fw-bold">Kode Lokasi</td><td>{{ $report->kode_lokasi ?? '-' }}</td></tr>
                        <tr><td class="fw-bold">Nama Lokasi</td><td>{{ $report->nama_lokasi ?? '-' }}</td></tr>
                        <tr><td class="fw-bold">Surveyor</td><td>{{ $report->surveyor ?? '-' }}</td></tr>
                        <tr><td class="fw-bold">Tanggal</td><td>{{ $report->created_at ?? '-' }}</td></tr>
                    </tbody></table>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="worksheet-card h-100">
                <div class="worksheet-card-header"><div><h5>Parsed Payload</h5><p>Hasil parsing dari Telegram/Form Backup.</p></div></div>
                <div class="worksheet-card-body">
                    <div class="worksheet-table-wrap"><table class="table worksheet-table"><thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>
                        @foreach($payload as $key => $value)
                            <tr><td class="fw-bold">{{ $key }}</td><td>{{ is_array($value) ? json_encode($value) : $value }}</td></tr>
                        @endforeach
                    </tbody></table></div>
                    @if(count($payload) === 0)<div class="text-muted py-3">Payload kosong.</div>@endif
                </div>
            </div>
        </div>
    </div>

    <div class="worksheet-card">
        <div class="worksheet-card-header"><div><h5>Raw Message</h5><p>Pesan asli yang dikirim surveyor.</p></div></div>
        <div class="worksheet-card-body"><pre style="white-space:pre-wrap;background:#f8fafc;border:1px solid #d7deea;border-radius:14px;padding:16px;">{{ $report->raw_message }}</pre></div>
    </div>
</div>

@include('Surveyor.layouts.footer')

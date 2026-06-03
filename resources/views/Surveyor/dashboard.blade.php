@section('title', 'Dashboard Excel Site Score')
@section('breadcrumb', 'Site Score Outlet / Dashboard Excel')
@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

@php
    $scores = $scores ?? collect();
    $summary = $summary ?? (object) ['total_survey'=>0,'approved'=>0,'consideration'=>0,'rejected'=>0,'avg_score'=>0,'max_score'=>0];
@endphp

<div class="excel-sheet mb-3">
    <div class="excel-titlebar">
        <div>
            <h1>GC - SITE SCORE OUTLET DASHBOARD</h1>
            <p>Web worksheet untuk monitoring kelayakan lokasi outlet, dibuat mirip alur Excel supaya mudah adaptasi.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('investor.surveyor.site-score.create') }}" class="btn btn-green btn-excel"><i class="bi bi-plus-circle me-1"></i> Input Survey</a>
            <a href="{{ route('investor.surveyor.site-score.ranking') }}" class="btn btn-blue btn-excel"><i class="bi bi-trophy me-1"></i> Ranking</a>
        </div>
    </div>
    <div class="excel-toolbar">
        <span class="excel-tab active"><i class="bi bi-grid-3x3-gap"></i> Dashboard</span>
        <a class="excel-tab" href="{{ route('investor.surveyor.site-score.rekap') }}"><i class="bi bi-file-earmark-bar-graph"></i> Rekap</a>
        <a class="excel-tab" href="{{ route('investor.surveyor.site-score.comparison') }}"><i class="bi bi-columns-gap"></i> Compare</a>
    </div>
    <div class="p-3">
        <div class="row g-3 mb-3">
            <div class="col-xl-2 col-md-4"><div class="excel-kpi"><div class="kpi-head">TOTAL SURVEY</div><div class="kpi-body"><div class="kpi-value">{{ number_format($summary->total_survey ?? 0) }}</div><div class="kpi-note">Semua data</div></div></div></div>
            <div class="col-xl-2 col-md-4"><div class="excel-kpi"><div class="kpi-head excel-success">APPROVED</div><div class="kpi-body"><div class="kpi-value text-success">{{ number_format($summary->approved ?? 0) }}</div><div class="kpi-note">GO lokasi</div></div></div></div>
            <div class="col-xl-2 col-md-4"><div class="excel-kpi"><div class="kpi-head excel-warning">CONSIDER</div><div class="kpi-body"><div class="kpi-value text-warning">{{ number_format($summary->consideration ?? 0) }}</div><div class="kpi-note">Review</div></div></div></div>
            <div class="col-xl-2 col-md-4"><div class="excel-kpi"><div class="kpi-head excel-danger">REJECTED</div><div class="kpi-body"><div class="kpi-value text-danger">{{ number_format($summary->rejected ?? 0) }}</div><div class="kpi-note">No go</div></div></div></div>
            <div class="col-xl-2 col-md-4"><div class="excel-kpi"><div class="kpi-head">AVG SCORE</div><div class="kpi-body"><div class="kpi-value">{{ number_format($summary->avg_score ?? 0, 2) }}</div><div class="kpi-note">Rata-rata</div></div></div></div>
            <div class="col-xl-2 col-md-4"><div class="excel-kpi"><div class="kpi-head">MAX SCORE</div><div class="kpi-body"><div class="kpi-value">{{ number_format($summary->max_score ?? 0, 2) }}</div><div class="kpi-note">Tertinggi</div></div></div></div>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="excel-table-wrap">
                    <table class="table tech-table align-middle" id="dashboardSiteScoreTable">
                        <thead>
                            <tr>
                                <th>Kode</th><th>Lokasi</th><th>Kota</th><th>Surveyor</th><th>Total Motor</th><th>Total Pejalan</th><th>Penambah</th><th>Pengurang</th><th>Score</th><th>Status</th><th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scores as $row)
                                @php
                                    $status = $row->rekomendasi ?? 'REJECTED';
                                    $scoreClass = $status === 'APPROVED' ? 'score-green' : ($status === 'CONSIDERATION' ? 'score-yellow' : 'score-red');
                                @endphp
                                <tr>
                                    <td class="fw-bold">{{ $row->kode_score ?? '-' }}</td>
                                    <td class="fw-bold">{{ $row->lokasi ?? '-' }}</td>
                                    <td>{{ $row->kota ?? '-' }}</td>
                                    <td>{{ $row->surveyor ?? '-' }}</td>
                                    <td class="excel-number">{{ number_format($row->total_motor ?? 0) }}</td>
                                    <td class="excel-number">{{ number_format($row->total_pejalan ?? 0) }}</td>
                                    <td class="excel-number">{{ number_format($row->total_penambah ?? 0, 4) }}</td>
                                    <td class="excel-number">{{ number_format($row->total_pengurang ?? 0, 4) }}</td>
                                    <td><span class="score-cell {{ $scoreClass }}">{{ number_format($row->final_percent ?? 0, 2) }}</span></td>
                                    <td><span class="score-cell {{ $scoreClass }}">{{ $status }}</span></td>
                                    <td class="text-end"><a href="{{ route('investor.surveyor.site-score.detail', $row->id ?? 0) }}" class="btn btn-sm btn-outline-secondary btn-excel">Detail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="decision-box mb-3">
                    <div class="decision-head">DECISION RULE</div>
                    <div class="decision-body">
                        <table class="excel-grid">
                            <tr><td class="excel-success fw-bold">APPROVED</td><td class="excel-number">Score ≥ 45</td></tr>
                            <tr><td class="excel-warning fw-bold">CONSIDERATION</td><td class="excel-number">30 ≤ Score &lt; 45</td></tr>
                            <tr><td class="excel-danger fw-bold">REJECTED</td><td class="excel-number">Score &lt; 30</td></tr>
                        </table>
                    </div>
                </div>
                <div class="decision-box">
                    <div class="decision-head">SCORE QUALITY</div>
                    <div class="decision-body">
                        <div class="d-flex justify-content-between fw-bold mb-1"><span>Average</span><span>{{ number_format($summary->avg_score ?? 0, 2) }}</span></div>
                        <div class="excel-progress mb-3"><span style="width: {{ min(100, $summary->avg_score ?? 0) }}%"></span></div>
                        <div class="d-flex justify-content-between fw-bold mb-1"><span>Highest</span><span>{{ number_format($summary->max_score ?? 0, 2) }}</span></div>
                        <div class="excel-progress"><span style="width: {{ min(100, $summary->max_score ?? 0) }}%"></span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    $('#dashboardSiteScoreTable').DataTable({
        pageLength: 10,
        ordering: false,
        language: { emptyTable: 'Belum ada data site score outlet', search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data' }
    });
});
</script>
@endpush

@include('Surveyor.layouts.footer')

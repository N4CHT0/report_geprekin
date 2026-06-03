@section('title', 'List Site Score')
@section('breadcrumb', 'Site Score Outlet / List Ranking')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

@php
    $scores = $scores ?? collect();

    $totalData = $scores->count();
    $approved = $scores->where('rekomendasi', 'APPROVED')->count();
    $consideration = $scores->where('rekomendasi', 'CONSIDERATION')->count();
    $rejected = $scores->where('rekomendasi', 'REJECTED')->count();
    $avgScore = $scores->avg('final_percent') ?? 0;
@endphp

<style>
    .ranking-page {
        display: grid;
        gap: 18px;
    }

    .ranking-hero {
        background: #fff;
        border: 1px solid #d7deea;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .ranking-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 10px;
    }

    .ranking-hero h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -.045em;
        color: #0f172a;
    }

    .ranking-hero p {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
        max-width: 820px;
    }

    .ranking-kpi-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
    }

    .ranking-kpi {
        background: #fff;
        border: 1px solid #d7deea;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 8px 20px rgba(15,23,42,.035);
    }

    .ranking-kpi span {
        display: block;
        color: #64748b;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .ranking-kpi strong {
        display: block;
        margin-top: 9px;
        color: #0f172a;
        font-size: 28px;
        line-height: 1;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .ranking-card {
        background: #fff;
        border: 1px solid #d7deea;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
        overflow: hidden;
    }

    .ranking-card-header {
        padding: 16px 18px;
        background: linear-gradient(180deg, #fff, #f8fafc);
        border-bottom: 1px solid #d7deea;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .ranking-card-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 950;
        color: #0f172a;
    }

    .ranking-card-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
    }

    .ranking-card-body {
        padding: 18px;
    }

    .rank-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 34px;
        border-radius: 12px;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 950;
    }

    .score-final {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 68px;
        height: 34px;
        border-radius: 12px;
        background: #eef2ff;
        color: #1d4ed8;
        font-weight: 950;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        border-radius: 999px;
        padding: 0 11px;
        font-size: 11px;
        font-weight: 950;
        white-space: nowrap;
    }

    .status-approved {
        background: #dcfce7;
        color: #166534;
    }

    .status-consideration {
        background: #fef3c7;
        color: #92400e;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .ranking-table-wrap {
        border: 1px solid #d7deea;
        border-radius: 16px;
        overflow: hidden;
    }

    .ranking-table {
        width: 100%;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    .ranking-table thead th {
        background: #eaf1ff !important;
        border: 1px solid #b8c2d3 !important;
        color: #1e293b;
        padding: 12px 10px !important;
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
        vertical-align: middle;
    }

    .ranking-table tbody td {
        border: 1px solid #d7deea !important;
        padding: 12px 10px !important;
        font-size: 13px;
        color: #0f172a;
        vertical-align: middle;
        background: #fff;
    }

    .ranking-table tbody tr:nth-child(even) td {
        background: #fbfdff;
    }

    .ranking-table tbody tr:hover td {
        background: #f8fbff;
    }

    .location-title {
        font-weight: 950;
        color: #0f172a;
        line-height: 1.3;
    }

    .location-sub {
        margin-top: 3px;
        font-size: 12px;
        color: #64748b;
        font-weight: 650;
    }

    @media(max-width:1200px) {
        .ranking-kpi-grid {
            grid-template-columns: repeat(2, minmax(0,1fr));
        }
    }

    @media(max-width:768px) {
        .ranking-kpi-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ranking-page">

    <div class="ranking-hero">
        <div>
            <div class="ranking-kicker">
                <i class="bi bi-trophy"></i>
                List Site Score
            </div>

            <h1>Ranking Lokasi Site Score</h1>

            <p>
                Urutan lokasi berdasarkan final score tertinggi. Halaman ini dipakai untuk membaca kandidat outlet
                yang paling layak dilanjutkan ke tahap validasi dan approval.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('investor.surveyor.site-score.create') }}" class="btn-worksheet">
                <i class="bi bi-plus-circle"></i>
                Input Baru
            </a>

            <a href="{{ route('investor.surveyor.site-score.index') }}" class="btn-worksheet-light">
                <i class="bi bi-grid-3x3-gap"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="ranking-kpi-grid">
        <div class="ranking-kpi">
            <span>Total Lokasi</span>
            <strong>{{ number_format($totalData) }}</strong>
        </div>

        <div class="ranking-kpi">
            <span>Approved</span>
            <strong class="text-success">{{ number_format($approved) }}</strong>
        </div>

        <div class="ranking-kpi">
            <span>Consideration</span>
            <strong class="text-warning">{{ number_format($consideration) }}</strong>
        </div>

        <div class="ranking-kpi">
            <span>Rejected</span>
            <strong class="text-danger">{{ number_format($rejected) }}</strong>
        </div>

        <div class="ranking-kpi">
            <span>Average Score</span>
            <strong>{{ number_format($avgScore, 2) }}</strong>
        </div>
    </div>

    <div class="ranking-card">
        <div class="ranking-card-header">
            <div>
                <h5>Worksheet Ranking</h5>
                <p>Ranking dihitung dari kolom final percent pada data site score.</p>
            </div>

            <div class="small text-muted fw-bold">
                Approved ≥ 45 · Consideration ≥ 30 · Rejected &lt; 30
            </div>
        </div>

        <div class="ranking-card-body">
            <div class="ranking-table-wrap">
                <div class="table-responsive">
                    <table class="table ranking-table align-middle" id="rankingTable" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Kode</th>
                                <th>Lokasi</th>
                                <th>Kota</th>
                                <th>Surveyor</th>
                                <th>Motor</th>
                                <th>Pejalan</th>
                                <th>Penambah</th>
                                <th>Pengurang</th>
                                <th>Final</th>
                                <th>Status</th>
                                <th>Detail</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($scores as $i => $row)
                                @php
                                    $status = strtolower($row->rekomendasi ?? 'rejected');
                                @endphp

                                <tr>
                                    <td class="text-nowrap">
                                        <span class="rank-badge">
                                            #{{ $i + 1 }}
                                        </span>
                                    </td>

                                    <td class="fw-bold text-nowrap">
                                        {{ $row->kode_score ?? '-' }}
                                    </td>

                                    <td style="min-width: 180px;">
                                        <div class="location-title">
                                            {{ $row->lokasi ?? '-' }}
                                        </div>

                                        <div class="location-sub text-nowrap">
                                            {{ $row->tanggal_survey ?? $row->created_at ?? '-' }}
                                        </div>
                                    </td>

                                    <td style="min-width: 120px;">{{ $row->kota ?? '-' }}</td>

                                    <td class="text-nowrap">{{ $row->surveyor ?? '-' }}</td>

                                    <td class="text-nowrap">{{ number_format($row->total_motor ?? 0) }}</td>

                                    <td class="text-nowrap">{{ number_format($row->total_pejalan ?? 0) }}</td>

                                    <td class="text-nowrap">{{ number_format($row->total_penambah ?? 0, 2) }}</td>

                                    <td class="text-nowrap">{{ number_format($row->total_pengurang ?? 0, 2) }}</td>

                                    <td class="text-nowrap">
                                        <span class="score-final">
                                            {{ number_format($row->final_percent ?? 0, 2) }}
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <span class="status-pill status-{{ $status }}">
                                            {{ $row->rekomendasi ?? 'REJECTED' }}
                                        </span>
                                    </td>

                                    <td class="text-nowrap">
                                        <a href="{{ route('investor.surveyor.site-score.detail', $row->id) }}"
                                           class="btn btn-sm btn-light fw-bold text-nowrap">
                                            Detail
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
                    Belum ada data site score.
                </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
$(function () {
    $('#rankingTable').DataTable({
        ordering: false,
        pageLength: 25,
        autoWidth: false,
        language: {
            emptyTable: 'Belum ada data site score',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            paginate: {
                previous: 'Sebelumnya',
                next: 'Berikutnya'
            }
        }
    });
});
</script>
@endpush

@include('Surveyor.layouts.footer')
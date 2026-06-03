@section('title', 'Dashboard Worksheet Site Score')
@section('breadcrumb', 'Site Score Outlet / Dashboard Excel-like')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

@php
    $scores = $scores ?? collect();
    $summary = $summary ?? (object) [
        'total_survey' => 0,
        'approved' => 0,
        'consideration' => 0,
        'rejected' => 0,
        'avg_score' => 0,
        'max_score' => 0,
    ];
    
    $validScores = $scores->filter(function ($row) {
        return !empty($row->latitude) && !empty($row->longitude);
    })->values();
    
    $latestScore = $validScores->first();
@endphp

<div class="worksheet-page">

    <div class="worksheet-hero">
        <div>
            <div class="worksheet-kicker">
                <i class="bi bi-file-earmark-spreadsheet"></i>
                Dashboard Site Score
            </div>

            <h1>Worksheet Analisis Site Score Outlet</h1>

            <p>
                Ringkasan seperti dashboard Excel: status, score, lokasi, titik peta, surveyor,
                dan hasil rekomendasi berdasarkan perhitungan site score.
            </p>
        </div>

        <div class="worksheet-actions">
            <a href="{{ route('investor.surveyor.site-score.create') }}" class="btn-worksheet">
                <i class="bi bi-plus-circle"></i>
                Input Site Score
            </a>

            <a href="{{ route('investor.surveyor.site-score.map') }}" class="btn-worksheet-light">
                <i class="bi bi-map"></i>
                Lihat Peta
            </a>
        </div>
    </div>

    <div class="worksheet-kpi-grid">
        <div class="worksheet-kpi">
            <span>Total Survey</span>
            <strong>{{ number_format($summary->total_survey ?? 0) }}</strong>
            <small>Semua data masuk</small>
        </div>

        <div class="worksheet-kpi">
            <span>Approved</span>
            <strong class="text-success">{{ number_format($summary->approved ?? 0) }}</strong>
            <small>Lokasi layak</small>
        </div>

        <div class="worksheet-kpi">
            <span>Consideration</span>
            <strong class="text-warning">{{ number_format($summary->consideration ?? 0) }}</strong>
            <small>Perlu review</small>
        </div>

        <div class="worksheet-kpi">
            <span>Rejected</span>
            <strong class="text-danger">{{ number_format($summary->rejected ?? 0) }}</strong>
            <small>Tidak direkomendasikan</small>
        </div>

        <div class="worksheet-kpi">
            <span>Average</span>
            <strong>{{ number_format($summary->avg_score ?? 0, 2) }}</strong>
            <small>Rata-rata score</small>
        </div>

        <div class="worksheet-kpi">
            <span>Highest</span>
            <strong>{{ number_format($summary->max_score ?? 0, 2) }}</strong>
            <small>Score tertinggi</small>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="worksheet-card h-100">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Data Site Score</h5>
                        <p>Data hasil survey lokasi calon outlet.</p>
                    </div>
                </div>

                <div class="worksheet-card-body">
                    <div class="worksheet-table-wrap">
                        <div class="table-responsive">
                            <table class="table worksheet-table align-middle" id="dashboardSiteScoreTable" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Tanggal</th>
                                        <th>Lokasi</th>
                                        <th>Kota</th>
                                        <th>Surveyor</th>
                                        <th>Motor</th>
                                        <th>Pejalan</th>
                                        <th>Score</th>
                                        <th>Status</th>
                                        <th>Maps</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($scores as $row)
                                        <tr>
                                            <td class="fw-bold text-nowrap">{{ $row->kode_score ?? '-' }}</td>
                                            <td class="text-nowrap">{{ $row->tanggal_survey ?? $row->created_at ?? '-' }}</td>
                                            <td class="fw-bold" style="min-width: 150px;">{{ $row->lokasi ?? '-' }}</td>
                                            <td style="min-width: 120px;">{{ $row->kota ?? '-' }}</td>
                                            <td class="text-nowrap">{{ $row->surveyor ?? '-' }}</td>
                                            <td class="text-nowrap">{{ number_format($row->total_motor ?? 0) }}</td>
                                            <td class="text-nowrap">{{ number_format($row->total_pejalan ?? 0) }}</td>
                                            <td class="text-nowrap"><span class="score-chip">{{ number_format($row->final_percent ?? 0, 2) }}</span></td>
                                            <td class="text-nowrap">
                                                @php $status = strtolower($row->rekomendasi ?? 'rejected'); @endphp
                                                <span class="status-pill status-{{ $status }}">
                                                    {{ $row->rekomendasi ?? 'REJECTED' }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                @if(!empty($row->latitude) && !empty($row->longitude))
                                                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold btn-preview-map text-nowrap"
                                                            data-lat="{{ $row->latitude }}"
                                                            data-lng="{{ $row->longitude }}"
                                                            data-lokasi="{{ addslashes($row->lokasi ?? '') }}"
                                                            data-score="{{ number_format($row->final_percent ?? 0, 1) }}"
                                                            data-status="{{ strtoupper($row->rekomendasi ?? 'REJECTED') }}"
                                                            data-id="{{ $row->id }}">
                                                        <i class="bi bi-geo-alt-fill me-1"></i>Map
                                                    </button>
                                                @elseif(!empty($row->maps_url))
                                                    <a href="{{ $row->maps_url }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold text-nowrap">
                                                        <i class="bi bi-link-45deg me-1"></i>Link
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('investor.surveyor.site-score.detail', $row->id ?? 0) }}"
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

        <div class="col-xl-4">
            <div class="worksheet-card h-100">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Peta Titik Terbaru</h5>
                        <p>Preview lokasi survey terbaru.</p>
                    </div>
                </div>

                <div class="worksheet-card-body" style="padding: 0;">
                    <div class="worksheet-map-box" id="dashboardMap" style="border: 0; width: 100%; height: 100%; min-height: 450px; background: #e9ebee;">
                        @if(!$latestScore)
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%;">
                            <i class="bi bi-geo-alt fs-1 text-primary"></i>
                            <div class="mt-2 text-center" style="color: #64748b;">
                                Belum ada koordinat survey.<br>
                                Buka menu <b>Peta Titik Survey</b> untuk melihat marker.
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
@if($latestScore)
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initDashboardMap" async defer></script>
<script>
let dashboardMap;
let dashboardMarker;
let dashboardInfoWindow;

function initDashboardMap() {
    const lat = {{ (float)$latestScore->latitude }};
    const lng = {{ (float)$latestScore->longitude }};
    const status = "{{ strtoupper($latestScore->rekomendasi ?? 'REJECTED') }}";
    const lokasi = "{{ addslashes($latestScore->lokasi ?? '') }}";
    const score = "{{ number_format($latestScore->final_percent ?? 0, 1) }}";
    const id = "{{ $latestScore->id }}";

    dashboardMap = new google.maps.Map(document.getElementById("dashboardMap"), {
        zoom: 16,
        center: { lat: lat, lng: lng },
        mapTypeId: "roadmap",
        gestureHandling: "cooperative"
    });

    dashboardMarker = new google.maps.Marker({
        position: { lat: lat, lng: lng },
        map: dashboardMap,
        title: lokasi
    });

    dashboardInfoWindow = new google.maps.InfoWindow();
    
    updateMarkerInfo(lat, lng, lokasi, score, status, id);

    dashboardMarker.addListener("click", () => {
        dashboardInfoWindow.open(dashboardMap, dashboardMarker);
    });
}

function updateMarkerInfo(lat, lng, lokasi, score, status, id) {
    let iconUrl = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
    if (status === 'APPROVED') {
        iconUrl = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
    } else if (status === 'CONSIDERATION') {
        iconUrl = "http://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
    }

    const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
    
    dashboardMarker.setPosition(pos);
    dashboardMarker.setIcon({
        url: iconUrl,
        scaledSize: new google.maps.Size(32, 32)
    });
    dashboardMarker.setTitle(lokasi);

    dashboardInfoWindow.setContent(`
        <div style="min-width:180px; padding: 2px; font-family: 'Inter', sans-serif;">
            <b style="font-size: 13px; color: #1e293b;">${lokasi}</b><br>
            <div style="margin-top: 5px; font-size: 11px; color: #64748b;">
                Score: <b>${score}</b><br>
                Status: <b>${status}</b>
            </div>
            <div style="margin-top: 8px;">
                <a href="/surveyor/site-score/detail/${id}" class="btn btn-sm btn-outline-primary" style="font-size: 11px; padding: 2px 8px; font-weight: 700; width: 100%;">Detail</a>
            </div>
        </div>
    `);

    dashboardMap.panTo(pos);
    dashboardInfoWindow.open(dashboardMap, dashboardMarker);
}

document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-preview-map');
        if (btn) {
            e.preventDefault();
            // Scroll sedikit ke atas agar map terlihat jika di mobile
            if (window.innerWidth < 1200) {
                document.getElementById('dashboardMap').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            updateMarkerInfo(
                btn.dataset.lat,
                btn.dataset.lng,
                btn.dataset.lokasi,
                btn.dataset.score,
                btn.dataset.status,
                btn.dataset.id
            );
        }
    });
});
</script>
@endif

<script>
$(function () {
    $('#dashboardSiteScoreTable').DataTable({
        pageLength: 10,
        ordering: false,
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

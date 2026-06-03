@section('title', 'Peta Titik Survey')
@section('breadcrumb', 'Site Score Outlet / Maps')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

@php
    $scores = $scores ?? collect();
    $validScores = $scores->filter(function ($row) {
        return !empty($row->latitude) && !empty($row->longitude);
    })->values();

    $firstLat = $validScores->first()->latitude ?? -7.2575;
    $firstLng = $validScores->first()->longitude ?? 112.7521;
@endphp

<style>
    .map-page {
        display: grid;
        gap: 18px;
    }

    .map-hero {
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

    .map-kicker {
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

    .map-hero h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -.045em;
        color: #0f172a;
    }

    .map-hero p {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 13px;
        font-weight: 650;
        max-width: 880px;
    }

    .map-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 18px;
        align-items: stretch;
    }

    .map-card {
        background: #fff;
        border: 1px solid #d7deea;
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
        overflow: hidden;
    }

    .map-card-header {
        padding: 16px 18px;
        background: linear-gradient(180deg, #fff, #f8fafc);
        border-bottom: 1px solid #d7deea;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .map-card-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 950;
        color: #0f172a;
    }

    .map-card-header p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
    }

    .map-card-body {
        padding: 18px;
    }

    #surveyMap {
        width: 100%;
        height: calc(100vh - 250px);
        min-height: 560px;
        border-radius: 0;
        background: #e5e7eb;
        z-index: 1;
    }

    .map-list {
        display: grid;
        gap: 10px;
        max-height: calc(100vh - 250px);
        overflow-y: auto;
        padding-right: 4px;
    }

    .map-item {
        border: 1px solid #d7deea;
        border-radius: 14px;
        padding: 12px;
        background: #fff;
        cursor: pointer;
        transition: .15s ease;
    }

    .map-item:hover {
        background: #f8fafc;
        border-color: #93c5fd;
    }

    .map-item-title {
        font-size: 13px;
        font-weight: 950;
        color: #0f172a;
        line-height: 1.25;
    }

    .map-item-sub {
        margin-top: 4px;
        font-size: 12px;
        color: #64748b;
        font-weight: 650;
    }

    .map-status {
        display: inline-flex;
        margin-top: 8px;
        min-height: 28px;
        align-items: center;
        border-radius: 999px;
        padding: 0 10px;
        font-size: 11px;
        font-weight: 950;
    }

    .map-status.approved {
        background: #dcfce7;
        color: #166534;
    }

    .map-status.consideration {
        background: #fef3c7;
        color: #92400e;
    }

    .map-status.rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    .map-empty {
        background: #fff;
        border: 1px dashed #b8c2d3;
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        color: #64748b;
        font-weight: 700;
    }

    @media (max-width: 1200px) {
        .map-layout {
            grid-template-columns: 1fr;
        }

        #surveyMap {
            height: 520px;
            min-height: 520px;
        }

        .map-list {
            max-height: none;
        }
    }
</style>

<div class="map-page">

    <div class="map-hero">
        <div>
            <div class="map-kicker">
                <i class="bi bi-geo-alt"></i>
                Peta Titik Survey Outlet
            </div>

            <h1>Peta Titik Survey</h1>

            <p>
                Titik otomatis dari latitude dan longitude hasil input form survey.
                Klik marker atau list lokasi untuk melihat detail.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('investor.surveyor.site-score.create') }}" class="btn-worksheet">
                <i class="bi bi-plus-circle"></i>
                Input Titik Baru
            </a>

            <a href="{{ route('investor.surveyor.site-score.index') }}" class="btn-worksheet-light">
                <i class="bi bi-table"></i>
                Dashboard
            </a>
        </div>
    </div>

    <div class="map-layout">
        <div class="map-card">
            <div class="map-card-header">
                <div>
                    <h5>Map Survey</h5>
                    <p>Total marker valid: {{ $validScores->count() }}</p>
                </div>
            </div>

            <div id="surveyMap"></div>
        </div>

        <div class="map-card">
            <div class="map-card-header">
                <div>
                    <h5>Daftar Titik</h5>
                    <p>Lokasi yang punya koordinat.</p>
                </div>
            </div>

            <div class="map-card-body">
                @if($validScores->count() > 0)
                    <div class="map-list">
                        @foreach($validScores as $idx => $row)
                            @php
                                $status = strtolower($row->rekomendasi ?? 'rejected');
                            @endphp

                            <div class="map-item"
                                 data-marker-index="{{ $idx }}">
                                <div class="map-item-title">
                                    {{ $row->lokasi ?? '-' }}
                                </div>

                                <div class="map-item-sub">
                                    {{ $row->kota ?? '-' }} · Score {{ number_format($row->final_percent ?? 0, 2) }}
                                </div>

                                <div class="map-item-sub">
                                    {{ $row->latitude }}, {{ $row->longitude }}
                                </div>

                                <span class="map-status {{ $status }}">
                                    {{ $row->rekomendasi ?? 'REJECTED' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="map-empty">
                        <i class="bi bi-map fs-1 text-primary"></i>
                        <div class="mt-2">
                            Belum ada data latitude / longitude.
                        </div>
                        <div class="small mt-1">
                            Isi form Site Score lalu klik Ambil Titik GPS.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap" async defer></script>

@push('scripts')
<script>
let map;
let markers = [];
let markerCluster;
let infoWindow;

function initMap() {
    const points = @json($validScores);
    const defaultLat = parseFloat('{{ $firstLat }}') || -7.2575;
    const defaultLng = parseFloat('{{ $firstLng }}') || 112.7521;

    map = new google.maps.Map(document.getElementById("surveyMap"), {
        zoom: points.length ? 13 : 8,
        center: { lat: defaultLat, lng: defaultLng },
        mapTypeId: "roadmap",
        gestureHandling: "cooperative"
    });

    infoWindow = new google.maps.InfoWindow();
    const bounds = new google.maps.LatLngBounds();

    points.forEach(function (point, index) {
        const lat = parseFloat(point.latitude);
        const lng = parseFloat(point.longitude);

        if (!lat || !lng) return;

        const status = (point.rekomendasi || 'REJECTED').toUpperCase();
        
        let iconUrl = "http://maps.google.com/mapfiles/ms/icons/red-dot.png";
        if (status === 'APPROVED') {
            iconUrl = "http://maps.google.com/mapfiles/ms/icons/green-dot.png";
        } else if (status === 'CONSIDERATION') {
            iconUrl = "http://maps.google.com/mapfiles/ms/icons/yellow-dot.png";
        }

        const position = { lat: lat, lng: lng };
        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: point.lokasi,
            icon: {
                url: iconUrl,
                scaledSize: new google.maps.Size(32, 32)
            }
        });

        const contentString = `
            <div style="min-width:220px; padding: 5px; font-family: 'Inter', sans-serif;">
                <b style="font-size: 14px; color: #1e293b;">${point.lokasi || '-'}</b><br>
                <span style="color: #64748b; font-size: 12px;">${point.kota || '-'}</span><br>
                <hr style="margin:8px 0; border-color: #e2e8f0;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; font-size: 12px;">
                    <div><span style="color:#64748b">Score:</span><br><b>${Number(point.final_percent || 0).toFixed(1)}</b></div>
                    <div><span style="color:#64748b">Status:</span><br><b>${status}</b></div>
                    <div style="grid-column: span 2"><span style="color:#64748b">Surveyor:</span><br><b>${point.surveyor || '-'}</b></div>
                </div>
                <a href="/surveyor/site-score/detail/${point.id}" class="btn btn-sm btn-primary w-100" style="font-weight:700; border-radius: 8px; text-decoration: none; display: block; text-align: center; color: white;">Lihat Detail</a>
            </div>
        `;

        marker.addListener("click", () => {
            infoWindow.setContent(contentString);
            infoWindow.open(map, marker);
        });

        markers.push(marker);
        bounds.extend(position);
    });

    if (markers.length > 0) {
        map.fitBounds(bounds);
        // Cegah zoom terlalu dekat saat titik hanya sedikit
        const listener = google.maps.event.addListener(map, "idle", function () {
            if (map.getZoom() > 16) map.setZoom(16);
            google.maps.event.removeListener(listener);
        });
    }

    // Inisialisasi MarkerClusterer untuk Google Maps
    markerCluster = new markerClusterer.MarkerClusterer({
        map: map,
        markers: markers
    });

    // Event listeners untuk list item di sebelah kanan
    document.querySelectorAll('.map-item').forEach(function (item) {
        item.addEventListener('click', function () {
            const idx = parseInt(this.dataset.markerIndex);
            const marker = markers[idx];

            if (!marker) return;

            map.setZoom(17);
            map.panTo(marker.getPosition());
            google.maps.event.trigger(marker, 'click');
        });
    });
}
</script>
@endpush

@include('Surveyor.layouts.footer')

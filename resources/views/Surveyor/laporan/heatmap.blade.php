@section('title', 'Heatmap Lokasi')
@section('breadcrumb', 'Laporan / Heatmap Lokasi')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

<div class="worksheet-page">
    <div class="worksheet-hero">
        <div>
            <div class="worksheet-kicker">
                <i class="bi bi-map"></i>
                Laporan & Pemetaan
            </div>
            <h1 class="worksheet-title">Heatmap Lokasi Survei</h1>
            <p class="worksheet-subtitle">Visualisasi sebaran kandidat lokasi berdasarkan konsentrasi titik kumpul di peta interaktif.</p>
        </div>
    </div>

    <div class="worksheet-container">
        <div class="excel-box">
            <div class="excel-box-header d-flex justify-content-between align-items-center">
                <div class="fw-bold"><i class="bi bi-globe-asia-australia text-primary me-2"></i>Peta Persebaran Lokasi</div>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="toggleHeatmap()">Toggle Heatmap</button>
                    <button class="btn btn-sm btn-outline-success" onclick="toggleMarkers()">Toggle Markers</button>
                </div>
            </div>
            <div class="excel-box-body p-0">
                <div id="map" style="width: 100%; height: 600px; border-radius: 0 0 12px 12px;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<style>
    .leaflet-popup-content-wrapper { border-radius: 12px; }
    .leaflet-popup-content { margin: 16px; }
</style>
<script>
    let map, heatLayer, markerLayer;

    document.addEventListener("DOMContentLoaded", function() {
        const centerPos = [-6.200000, 106.816666]; // Default Jakarta
        const scores = @json($scores);
        
        map = L.map('map').setView(centerPos, 11);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
        }).addTo(map);

        const heatData = [];
        markerLayer = L.layerGroup().addTo(map);
        const bounds = [];

        scores.forEach(score => {
            if(score.latitude && score.longitude) {
                const lat = parseFloat(score.latitude);
                const lng = parseFloat(score.longitude);
                
                heatData.push([lat, lng, 1]); 
                bounds.push([lat, lng]);

                const popupContent = `
                    <div style="font-family: Inter, sans-serif; min-width:200px;">
                        <h6 class="fw-bold mb-1">${score.lokasi}</h6>
                        <p class="mb-1 text-muted" style="font-size:12px;">${score.kota}</p>
                        <hr class="my-2">
                        <p class="mb-0"><strong>Status:</strong> <span class="badge bg-primary">${score.rekomendasi ?? '-'}</span></p>
                        <p class="mb-0"><strong>Score:</strong> ${score.final_percent ? score.final_percent + '%' : (score.final_score ? (score.final_score * 100) + '%' : '0%')}</p>
                    </div>
                `;

                L.marker([lat, lng]).bindPopup(popupContent).addTo(markerLayer);
            }
        });

        if (bounds.length > 0) {
            map.fitBounds(bounds);
        }

        heatLayer = L.heatLayer(heatData, {
            radius: 35,
            blur: 25,
            maxZoom: 17,
        }).addTo(map);
    });

    function toggleHeatmap() {
        if (map.hasLayer(heatLayer)) {
            map.removeLayer(heatLayer);
        } else {
            map.addLayer(heatLayer);
        }
    }

    function toggleMarkers() {
        if (map.hasLayer(markerLayer)) {
            map.removeLayer(markerLayer);
        } else {
            map.addLayer(markerLayer);
        }
    }
</script>
@endpush

@include('Surveyor.layouts.footer')

@extends('Surveyor.layout')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-robot"></i> AI Training Data Collector (Isochrone Headless)</h5>
                    <button id="btnStartScraping" class="btn btn-warning btn-sm fw-bold text-dark">
                        Mulai Auto-Scan Radar <i class="bi bi-play-fill"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Cara Kerja:</strong> Sistem akan mengurutkan Top 10 Outlet berdasarkan Rata-rata Omset. Untuk setiap outlet, peta akan melakukan pencarian Isochrone 5 menit (mencari kompetitor, sekolah, dll). Data ini kemudian digabungkan dengan target omset (Variabel Y) lalu disimpan ke file JSON yang siap digunakan untuk melatih Machine Learning Prediksi Omset.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tblAiData">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Outlet</th>
                                    <th>Target Y (Omset/Hari)</th>
                                    <th>Status Scan</th>
                                    <th>X1 (Sekolah)</th>
                                    <th>X2 (Kampus)</th>
                                    <th>X3 (Kompetitor)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($outlets as $o)
                                <tr id="row-{{ $o['id'] }}" data-id="{{ $o['id'] }}" data-lat="{{ $o['latitude'] }}" data-lng="{{ $o['longitude'] }}" data-omset="{{ $o['avg_omset'] }}">
                                    <td>{{ $o['id'] }}</td>
                                    <td>{{ $o['nama'] }}</td>
                                    <td class="text-success fw-bold">Rp {{ number_format($o['avg_omset'], 0, ',', '.') }}</td>
                                    <td class="scan-status"><span class="badge bg-secondary">Menunggu...</span></td>
                                    <td class="scan-x1">-</td>
                                    <td class="scan-x2">-</td>
                                    <td class="scan-x3">-</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden Map Div for Google Places API -->
                    <div id="hiddenMap" style="width: 100%; height: 300px; display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,geometry"></script>
<script>
    // Mock the required variables from site-score-core.js
    let previewMap = null;
    let previewRadiusCircle = null;
    let service = null;

    // Inisialisasi Peta Headless
    function initMap() {
        previewMap = new google.maps.Map(document.getElementById('hiddenMap'), {
            center: { lat: -7.250445, lng: 112.768845 },
            zoom: 13
        });
        service = new google.maps.places.PlacesService(previewMap);
    }

    $(document).ready(function() {
        initMap();

        $('#btnStartScraping').click(async function() {
            $(this).prop('disabled', true).html('Sedang Scanning... <i class="bi bi-arrow-repeat spin"></i>');
            
            const rows = $('#tblAiData tbody tr').toArray();
            
            for (let i = 0; i < rows.length; i++) {
                const $row = $(rows[i]);
                const id = $row.data('id');
                const lat = parseFloat($row.data('lat'));
                const lng = parseFloat($row.data('lng'));
                const omset = parseFloat($row.data('omset'));
                const nama = $row.find('td:eq(1)').text();

                $row.find('.scan-status').html('<span class="badge bg-warning text-dark">Scanning Radius 5 Menit...</span>');

                // Simulate Isochrone delay and Places Search (Mocked for safety if places API quota is an issue, 
                // but let's implement the actual search)
                
                let results = await doHeadlessScan(lat, lng);

                $row.find('.scan-x1').text(results.sekolah);
                $row.find('.scan-x2').text(results.kampus);
                $row.find('.scan-x3').text(results.kompetitor);

                $row.find('.scan-status').html('<span class="badge bg-success">Selesai & Tersimpan</span>');

                // Send to Server
                await $.ajax({
                    url: '/ai-collector/save',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        nama: nama,
                        avg_omset: omset,
                        latitude: lat,
                        longitude: lng,
                        sekolah: results.sekolah,
                        kampus: results.kampus,
                        kompetitor: results.kompetitor
                    }
                });
            }

            $(this).html('Scan Selesai <i class="bi bi-check-circle"></i>').removeClass('btn-warning').addClass('btn-success');
            alert('Sukses! Semua data siap digunakan untuk Training ML.');
        });
    });

    // Helper: Hitung Offset Kordinat
    function computeOffset(lat, lng, distance, heading) {
        return google.maps.geometry.spherical.computeOffset(new google.maps.LatLng(lat, lng), distance, heading);
    }

    async function doHeadlessScan(lat, lng) {
        // 1. Gambar Isochrone (5 menit)
        const maxSeconds = 5 * 60;
        const maxRadius = 1500;
        const headings = [0, 45, 90, 135, 180, 225, 270, 315];
        const origin = { lat, lng };
        
        let polygonPaths = [];
        const dmService = new google.maps.DistanceMatrixService();
        const destinations = headings.map(heading => computeOffset(lat, lng, maxRadius, heading));

        try {
            const response = await new Promise((resolve) => {
                dmService.getDistanceMatrix({
                    origins: [origin],
                    destinations: destinations,
                    travelMode: google.maps.TravelMode.DRIVING,
                }, (res, status) => {
                    if (status === 'OK') resolve(res);
                    else resolve(null);
                });
            });

            if (response) {
                const results = response.rows[0].elements;
                for (let i = 0; i < headings.length; i++) {
                    const element = results[i];
                    if (element.status === 'OK' && element.duration && element.duration.value > 0) {
                        let ratio = maxSeconds / element.duration.value;
                        if (ratio > 1.0) ratio = 1.0;
                        if (ratio < 0.2) ratio = 0.2;
                        polygonPaths.push(computeOffset(lat, lng, maxRadius * ratio, headings[i]));
                    } else {
                        polygonPaths.push(computeOffset(lat, lng, maxRadius * 0.3, headings[i]));
                    }
                }
            } else {
                polygonPaths = destinations; // fallback to circle
            }
        } catch(e) {
            polygonPaths = destinations;
        }

        previewRadiusCircle = new google.maps.Polygon({ paths: polygonPaths });

        // 2. Scan Tempat
        const searchPlaces = (req) => {
            return new Promise((resolve) => {
                service.nearbySearch(req, (results, status) => {
                    if (status === google.maps.places.PlacesServiceStatus.OK && results) {
                        resolve(results);
                    } else {
                        resolve([]);
                    }
                });
            });
        };

        const filterByIsochrone = (results) => {
            return results.filter(place => {
                if (!place.geometry || !place.geometry.location) return false;
                return google.maps.geometry.poly.containsLocation(place.geometry.location, previewRadiusCircle);
            });
        };

        const loc = new google.maps.LatLng(lat, lng);
        
        // Parallel fetch
        const [resSekolah, resKampus, resKomp1, resKomp2] = await Promise.all([
            searchPlaces({ location: loc, radius: 1500, type: 'school' }),
            searchPlaces({ location: loc, radius: 1500, type: 'university' }),
            searchPlaces({ location: loc, radius: 1500, keyword: 'fried chicken' }),
            searchPlaces({ location: loc, radius: 1500, keyword: 'ayam geprek' })
        ]);

        return {
            sekolah: filterByIsochrone(resSekolah).length,
            kampus: filterByIsochrone(resKampus).length,
            kompetitor: filterByIsochrone(resKomp1).length + filterByIsochrone(resKomp2).length
        };
    }
</script>
@endsection

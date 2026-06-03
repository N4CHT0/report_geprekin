@section('title', 'Tambah Titik Survey')
@section('breadcrumb', 'Surveyor / Tambah Titik Survey')

@include('Surveyor.layouts.header')

<style>
    :root {
        --form-bg: #f6f8fb;
        --form-card: #ffffff;
        --form-text: #172033;
        --form-muted: #6b7280;
        --form-border: #e5e7eb;
        --form-primary: #2563eb;
        --form-primary-soft: #eff6ff;
        --form-green: #16a34a;
        --form-shadow: 0 18px 45px rgba(15, 23, 42, .08);
    }

    .candidate-form-page {
        min-height: calc(100vh - 90px);
        padding: 24px;
        color: var(--form-text);
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, .10), transparent 34rem),
            linear-gradient(180deg, #fff 0%, var(--form-bg) 34%);
    }

    .candidate-form-shell {
        max-width: 1080px;
        margin: 0 auto;
    }

    .candidate-form-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 20px;
    }

    .candidate-form-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: var(--form-primary-soft);
        color: var(--form-primary);
        font-weight: 800;
        font-size: 12px;
        letter-spacing: .04em;
        margin-bottom: 10px;
    }

    .candidate-form-hero h1 {
        margin: 0 0 8px;
        font-size: clamp(26px, 3vw, 40px);
        font-weight: 900;
        letter-spacing: -.04em;
    }

    .candidate-form-hero p {
        margin: 0;
        color: var(--form-muted);
        max-width: 680px;
        line-height: 1.6;
    }

    .candidate-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 15px;
        padding: 11px 15px;
        border: 1px solid var(--form-border);
        font-weight: 800;
        text-decoration: none;
        transition: .18s ease;
        white-space: nowrap;
        cursor: pointer;
    }

    .candidate-btn-primary {
        background: var(--form-primary);
        border-color: var(--form-primary);
        color: #fff;
        box-shadow: 0 12px 28px rgba(37, 99, 235, .22);
    }

    .candidate-btn-light {
        background: #fff;
        color: var(--form-text);
    }

    .candidate-btn-success {
        background: #ecfdf5;
        border-color: #bbf7d0;
        color: var(--form-green);
    }

    .candidate-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .candidate-form-card {
        background: rgba(255, 255, 255, .92);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(229, 231, 235, .9);
        border-radius: 28px;
        box-shadow: var(--form-shadow);
        overflow: hidden;
    }

    .candidate-form-card-header {
        padding: 20px;
        border-bottom: 1px solid var(--form-border);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .candidate-form-card-header i {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 16px;
        background: var(--form-primary-soft);
        color: var(--form-primary);
        font-size: 18px;
    }

    .candidate-form-card-header h2 {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
    }

    .candidate-form-card-header p {
        margin: 3px 0 0;
        color: var(--form-muted);
        font-size: 13px;
    }

    .candidate-form-body {
        padding: 20px;
    }

    .form-block {
        border: 1px solid var(--form-border);
        border-radius: 22px;
        padding: 16px;
        background: #fff;
        height: 100%;
    }

    .candidate-label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 8px;
        color: var(--form-muted);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .07em;
    }

    .candidate-input {
        width: 100%;
        min-height: 48px;
        border: 1px solid var(--form-border);
        border-radius: 16px;
        padding: 12px 14px;
        background: #f9fafb;
        color: var(--form-text);
        font-weight: 700;
        outline: none;
        transition: .18s ease;
    }

    .candidate-input:focus {
        border-color: var(--form-primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        background: #fff;
    }

    textarea.candidate-input {
        min-height: 120px;
        resize: vertical;
    }

    .map-preview {
        border: 1px dashed #bfdbfe;
        border-radius: 22px;
        background: var(--form-primary-soft);
        padding: 18px;
        color: var(--form-primary);
        height: 100%;
    }

    .map-preview i {
        font-size: 26px;
        margin-bottom: 10px;
    }

    .map-preview strong {
        display: block;
        font-size: 17px;
        margin-bottom: 6px;
    }

    .map-preview p {
        margin: 0;
        color: #1d4ed8;
        line-height: 1.5;
        font-size: 13px;
    }

    .candidate-form-footer {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        padding: 18px 20px;
        border-top: 1px solid var(--form-border);
        background: #fbfdff;
    }

    @media (max-width: 900px) {
        .candidate-form-page { padding: 16px; }
        .candidate-form-hero {
            display: grid;
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="candidate-form-page">
    <div class="candidate-form-shell">
        <div class="candidate-form-hero">
            <div>
                <div class="candidate-form-eyebrow">
                    <i class="bi bi-geo-alt"></i>
                    Admin Input
                </div>
                <h1>Tambah Kandidat Titik Lokasi</h1>
                <p>Masukkan titik lokasi dari hasil pencarian admin. Surveyor akan mengirim laporan atau video berdasarkan kode lokasi ini.</p>
            </div>

            <a href="{{ route('investor.surveyor.candidate.index') }}" class="candidate-btn candidate-btn-light">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('investor.surveyor.candidate.store') }}">
            @csrf

            <div class="candidate-form-card">
                <div class="candidate-form-card-header">
                    <i class="bi bi-pin-map"></i>
                    <div>
                        <h2>Detail Lokasi</h2>
                        <p>Lengkapi identitas lokasi, penugasan surveyor, dan koordinat Maps.</p>
                    </div>
                </div>

                <div class="candidate-form-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-block">
                                <label class="candidate-label" for="nama_lokasi">
                                    <i class="bi bi-shop"></i>
                                    Nama Lokasi
                                </label>
                                <input type="text" name="nama_lokasi" id="nama_lokasi" class="candidate-input" placeholder="Contoh: Ruko Depan Kampus" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-block">
                                <label class="candidate-label" for="assigned_surveyor">
                                    <i class="bi bi-person-badge"></i>
                                    Assigned Surveyor
                                </label>
                                <input type="text" name="assigned_surveyor" id="assigned_surveyor" class="candidate-input" placeholder="Nama surveyor">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-block">
                                <label class="candidate-label" for="alamat">
                                    <i class="bi bi-signpost"></i>
                                    Alamat
                                </label>
                                <input type="text" name="alamat" id="alamat" class="candidate-input" placeholder="Alamat lengkap kandidat lokasi">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-block">
                                <label class="candidate-label" for="kota">
                                    <i class="bi bi-buildings"></i>
                                    Kota
                                </label>
                                <input type="text" name="kota" id="kota" class="candidate-input" placeholder="Kota">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-block">
                                <label class="candidate-label" for="provinsi">
                                    <i class="bi bi-map"></i>
                                    Provinsi
                                </label>
                                <input type="text" name="provinsi" id="provinsi" class="candidate-input" placeholder="Provinsi">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-block">
                                <label class="candidate-label" for="priority">
                                    <i class="bi bi-flag"></i>
                                    Priority
                                </label>
                                <select name="priority" id="priority" class="candidate-input">
                                    <option value="LOW">LOW</option>
                                    <option value="MEDIUM" selected>MEDIUM</option>
                                    <option value="HIGH">HIGH</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-block">
                                <label class="candidate-label" for="latitude">
                                    <i class="bi bi-crosshair"></i>
                                    Latitude
                                </label>
                                <input type="text" name="latitude" id="latitude" class="candidate-input" placeholder="-6.200000">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-block">
                                <label class="candidate-label" for="longitude">
                                    <i class="bi bi-crosshair2"></i>
                                    Longitude
                                </label>
                                <input type="text" name="longitude" id="longitude" class="candidate-input" placeholder="106.816666">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-block" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                                <div style="padding: 16px; background: #fff; border-bottom: 1px solid var(--form-border);">
                                    <label class="candidate-label" for="mapSearch">
                                        <i class="bi bi-search"></i>
                                        Cari Lokasi Peta
                                    </label>
                                    <input type="text" id="mapSearch" class="candidate-input" placeholder="Ketik nama tempat, ruko, atau jalan...">
                                </div>
                                <div id="mapPicker" style="width: 100%; height: 350px; background: #f3f4f6;"></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-block">
                                <label class="candidate-label" for="mapsUrl">
                                    <i class="bi bi-link-45deg"></i>
                                    Maps URL
                                </label>
                                <input type="text" name="maps_url" id="mapsUrl" class="candidate-input" placeholder="https://www.google.com/maps?q=...">
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-block">
                                <label class="candidate-label" for="catatan_admin">
                                    <i class="bi bi-journal-text"></i>
                                    Catatan Admin
                                </label>
                                <textarea name="catatan_admin" id="catatan_admin" rows="4" class="candidate-input" placeholder="Catatan tambahan untuk surveyor"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="candidate-form-footer">
                    <button type="button" class="candidate-btn candidate-btn-success" onclick="ambilLokasi()">
                        <i class="bi bi-crosshair"></i>
                        Ambil Lokasi Saya
                    </button>
                    <button type="submit" class="candidate-btn candidate-btn-primary">
                        <i class="bi bi-save"></i>
                        Simpan Titik Lokasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initMapPicker" async defer></script>
<script>
    let map;
    let marker;
    let geocoder;

    function initMapPicker() {
        const defaultPos = { lat: -6.200000, lng: 106.816666 };
        
        map = new google.maps.Map(document.getElementById('mapPicker'), {
            center: defaultPos,
            zoom: 13,
            mapTypeId: 'roadmap',
            gestureHandling: 'cooperative'
        });

        marker = new google.maps.Marker({
            position: defaultPos,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP,
        });

        geocoder = new google.maps.Geocoder();

        // 1. Search Box
        const input = document.getElementById('mapSearch');
        const searchBox = new google.maps.places.SearchBox(input);
        
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });

        searchBox.addListener('places_changed', function() {
            const places = searchBox.getPlaces();
            if (places.length == 0) return;

            const place = places[0];
            if (!place.geometry || !place.geometry.location) return;

            map.setCenter(place.geometry.location);
            map.setZoom(17);
            
            updateMarkerPosition(place.geometry.location);
            fillAddressComponents(place);
        });

        // 2. Click Map to drop pin
        map.addListener('click', function(e) {
            updateMarkerPosition(e.latLng);
            reverseGeocode(e.latLng);
        });

        // 3. Drag Pin
        marker.addListener('dragend', function(e) {
            updateMarkerPosition(e.latLng);
            reverseGeocode(e.latLng);
        });

        // 4. Paste Maps URL
        document.getElementById('mapsUrl').addEventListener('input', function(e) {
            const url = e.target.value.trim();
            if(!url.startsWith('http')) return;

            // Simple parsing for long URL like ?q=lat,lng or @lat,lng
            const longUrlRegex = /@(-?\d+\.\d+),(-?\d+\.\d+)/;
            const qRegex = /q=(-?\d+\.\d+),(-?\d+\.\d+)/;
            
            let match = url.match(longUrlRegex) || url.match(qRegex);
            
            if(match) {
                const latLng = new google.maps.LatLng(parseFloat(match[1]), parseFloat(match[2]));
                map.setCenter(latLng);
                map.setZoom(17);
                updateMarkerPosition(latLng);
                reverseGeocode(latLng);
                return;
            }

            // Resolve short URL like maps.app.goo.gl via our backend
            if(url.includes('goo.gl') || url.includes('maps.app')) {
                // Tampilkan loading (opsional)
                e.target.style.opacity = '0.5';
                
                fetch(`{{ route('investor.surveyor.site-score.resolve-maps-url') }}?url=${encodeURIComponent(url)}`)
                    .then(res => res.json())
                    .then(data => {
                        e.target.style.opacity = '1';
                        if(data.lat && data.lng) {
                            const latLng = new google.maps.LatLng(data.lat, data.lng);
                            map.setCenter(latLng);
                            map.setZoom(17);
                            updateMarkerPosition(latLng);
                            reverseGeocode(latLng);
                        } else {
                            console.warn("Gagal mengekstrak koordinat dari link Maps pendek.");
                            alert("Gagal mengekstrak koordinat. Coba gunakan link Google Maps versi panjang, atau pastikan link tersebut benar.");
                        }
                    })
                    .catch(err => {
                        e.target.style.opacity = '1';
                        console.error(err);
                    });
            }
        });
    }

    function updateMarkerPosition(latLng) {
        marker.setPosition(latLng);
        document.getElementById('latitude').value = latLng.lat().toFixed(6);
        document.getElementById('longitude').value = latLng.lng().toFixed(6);
        document.getElementById('mapsUrl').value = `https://www.google.com/maps?q=${latLng.lat()},${latLng.lng()}`;
    }

    function reverseGeocode(latLng) {
        geocoder.geocode({ location: latLng }, function(results, status) {
            if (status === 'OK' && results[0]) {
                fillAddressFromGeocode(results[0]);
            }
        });
    }

    function fillAddressComponents(place) {
        if (place.formatted_address) {
            document.getElementById('alamat').value = place.formatted_address;
        }

        let kota = '';
        let provinsi = '';
        if (place.address_components) {
            for (let i = 0; i < place.address_components.length; i++) {
                const types = place.address_components[i].types;
                if (types.includes('administrative_area_level_2') || types.includes('locality')) {
                    kota = place.address_components[i].long_name;
                }
                if (types.includes('administrative_area_level_1')) {
                    provinsi = place.address_components[i].long_name;
                }
            }
        }

        if (kota) document.getElementById('kota').value = kota.replace('Kota ', '').replace('Kabupaten ', '');
        if (provinsi) document.getElementById('provinsi').value = provinsi;
    }

    function fillAddressFromGeocode(result) {
        fillAddressComponents(result);
    }

    function ambilLokasi(){
        if(!map) {
            alert('Tunggu sebentar, Google Maps sedang dimuat...');
            return;
        }

        if(!navigator.geolocation){
            alert('Browser tidak support GPS.');
            return;
        }

        // Beri indikasi loading
        const btn = document.querySelector('button[onclick="ambilLokasi()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Mencari...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(function(pos){
            btn.innerHTML = originalText;
            btn.disabled = false;

            const latLng = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
            map.setCenter(latLng);
            map.setZoom(17);
            updateMarkerPosition(latLng);
            reverseGeocode(latLng);
        }, function(error){
            btn.innerHTML = originalText;
            btn.disabled = false;
            console.error(error);
            alert('Gagal mengambil lokasi. Pastikan permission GPS diizinkan di browser/Windows Anda.');
        }, { enableHighAccuracy: true, timeout: 15000 });
    }
</script>
@endpush

@include('Surveyor.layouts.footer')

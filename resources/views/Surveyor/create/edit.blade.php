@extends('Surveyor.layout')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/surveyor/site-score.css') }}">
@endpush

@section('content')

@include('Surveyor.layouts.site-score-excel-style')

<form action="{{ route('investor.surveyor.site-score.update', $score->id) }}"
      method="POST"
      enctype="multipart/form-data"
      id="siteScoreForm">

    @csrf
    <input type="hidden" name="traffic_calibration_json" id="trafficCalibrationJson" value="{{ $score->traffic_calibration_json ?? '' }}">

    <div class="score-form-page">
        @if($errors->any())
            <div class="alert alert-danger shadow-sm mx-3 mt-3" style="border-radius: 12px; background-color: #fef2f2; border: 1px solid #f87171; color: #991b1b;">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Gagal Menyimpan! Mohon lengkapi data berikut:</h6>
                <ul class="mb-0" style="font-size: 14px;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if($score->workflow_status === 'REVISION')
        <div class="alert alert-danger" style="border-radius:12px; margin-bottom: 20px;">
            <h5 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Catatan Revisi dari Manajemen</h5>
            <p class="mb-0" style="white-space: pre-line;">{{ $score->approval_note }}</p>
        </div>
        @endif

        <div class="score-form-hero">
            <div>
                <div class="score-form-kicker">
                    <i class="bi bi-pencil-square"></i>
                    Edit Site Score Survey
                </div>
                <h1>Revisi / Lanjutkan Draft</h1>
                <p>
                    Versi web dibuat seperti worksheet Excel. Isi cell kuning, ambil titik GPS,
                    upload foto lokasi, lalu sistem menghitung score dan rekomendasi secara otomatis.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn-worksheet-light" onclick="ambilTitikGPS()">
                    <i class="bi bi-crosshair"></i>
                    Ambil Titik GPS
                </button>
                <button type="submit" name="action_type" value="draft" class="btn-worksheet-light text-dark" style="border-color:#d1d5db; background:#fff;" formnovalidate>
                    <i class="bi bi-file-earmark-text"></i>
                    Simpan Draft
                </button>
                <button type="submit" name="action_type" value="submit" class="btn-worksheet">
                    <i class="bi bi-send"></i>
                    Kirim ke Manajemen
                </button>
            </div>
        </div>

        @include('Surveyor.create._form')

    </div>
</form>

@include('Surveyor.create.partials._ai_modal')
@include('Surveyor.create.partials._feasibility_modal')

@endsection

@push('scripts')
<script>
window.SiteScoreConfig = {
    routes: {
        scanPlaces: "{{ route('investor.surveyor.site-score.scan-places') }}",
        resolveMapsUrl: "{{ route('investor.surveyor.site-score.resolve-maps-url') }}",
        videoSubmit: "{{ route('investor.surveyor.video-detection.submit') }}",
        videoStatus: "{{ url('/surveyor/video-detection/status') }}",
        videoProgress: "{{ url('/surveyor/video-detection/progress') }}"
    }
};
</script>
<script>
window.initMapPlaceholder = function() {
    console.log('Google Maps API Loaded');
    if (typeof window.initMapsPreview === 'function') {
        window.initMapsPreview();
    }
};
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,geometry&callback=initMapPlaceholder" async defer></script>
<script src="{{ asset('js/surveyor/site-score-core.js') }}?v={{ time() }}"></script>
<script>
// AUTO-FILL DATA DARI DATABASE
document.addEventListener("DOMContentLoaded", function() {
    const data = @json($score);
    for (let key in data) {
        if (data[key] !== null) {
            let el = document.querySelector(`[name="${key}"]`);
            if (el) {
                if (el.type === 'checkbox' || el.type === 'radio') {
                    if (el.value === String(data[key])) {
                        el.checked = true;
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                } else {
                    el.value = data[key];
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }
    }
    
    // Khusus radio tipe outlet
    if(data.tipe_outlet) {
        let r = document.querySelector(`input[name="tipe_outlet"][value="${data.tipe_outlet}"]`);
        if(r) {
            r.checked = true;
            r.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
    
    // Pastikan semua UI state yang reaktif merespon data yang di-load
    setTimeout(() => {
        if(typeof loadConfigFromJson === 'function') loadConfigFromJson();
        if(typeof toggleConfigInputs === 'function') toggleConfigInputs();
        if(typeof updateJamRamai === 'function') updateJamRamai();
        if(typeof hitungLiveScore === 'function') hitungLiveScore();
        if(typeof renderMapMarkers === 'function') renderMapMarkers(); // update markers if possible
    }, 500);
});
</script>
@endpush

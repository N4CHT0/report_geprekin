@extends('Surveyor.layout')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/surveyor/site-score.css') }}">
@endpush

@section('content')

@include('Surveyor.layouts.site-score-excel-style')

<form action="{{ route('investor.surveyor.site-score.store') }}"
      method="POST"
      enctype="multipart/form-data"
      id="siteScoreForm">

    @csrf
    <input type="hidden" name="traffic_calibration_json" id="trafficCalibrationJson" value="">

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

        <div class="score-form-hero">
            <div>
                <div class="score-form-kicker">
                    <i class="bi bi-table"></i>
                    Form Site Score Survey
                </div>
                <h1>Input Worksheet Site Score Outlet</h1>
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
@endpush

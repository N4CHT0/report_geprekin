@extends('Investor.Surveyor._layout')

@section('title', isset($survey->id) ? 'Edit Survey' : 'Input Survey')
@section('page_title', isset($survey->id) ? 'Edit Survey Site Score' : 'Input Survey Site Score')

@section('content')

@php
    $isEdit = isset($survey->id);
    $aiStatus = $survey->traffic_detection_status ?? 'Manual';
    $aiProgress = (int) ($survey->traffic_detection_progress ?? 0);
@endphp

<form id="surveyForm"
      method="POST"
      action="{{ $isEdit ? route('master.surveyor.update', $survey->id) : route('master.surveyor.store') }}"
      enctype="multipart/form-data">

    @csrf

    @if($isEdit)
        @method('PUT')
    @endif

    <div id="uploadProgressWrapper"
         class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-5">
        <div class="bg-white rounded-3xl p-8 w-full max-w-lg shadow-2xl">
            <h3 class="text-2xl font-black">Menyimpan Survey</h3>
            <p id="uploadStatusText" class="text-sm text-slate-500 mt-1 mb-5">
                Mengupload data dan video...
            </p>

            <div class="w-full bg-slate-200 rounded-full h-5 overflow-hidden">
                <div id="uploadProgressBar"
                     class="bg-blue-600 h-5 rounded-full transition-all duration-300"
                     style="width:0%"></div>
            </div>

            <div id="uploadProgressPercent" class="text-center mt-3 text-2xl font-black text-blue-700">
                0%
            </div>

            <div id="queueInfoText"
                 class="hidden mt-5 p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-700 font-bold">
                Upload selesai. Video akan diproses ringan oleh Redis worker di background.
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-2xl font-black mb-6">Informasi Lokasi</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-sm font-bold">Nama Surveyor</label>
                        <input type="text" name="surveyor_name"
                               value="{{ old('surveyor_name', $survey->surveyor_name ?? '') }}"
                               class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                    </div>

                    <div>
                        <label class="text-sm font-bold">Metode Input</label>
                        <select name="input_method"
                                class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                            @foreach(['Google Form','Laravel Web','Telegram Bot'] as $method)
                                <option value="{{ $method }}"
                                    {{ old('input_method', $survey->input_method ?? 'Laravel Web') == $method ? 'selected' : '' }}>
                                    {{ $method }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-bold">Jenis Lokasi</label>
                        <input type="text" name="location_type"
                               value="{{ old('location_type', $survey->location_type ?? '') }}"
                               class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                    </div>

                    <div>
                        <label class="text-sm font-bold">Kota</label>
                        <input type="text" name="city"
                               value="{{ old('city', $survey->city ?? '') }}"
                               class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                    </div>

                    <div>
                        <label class="text-sm font-bold">Latitude</label>
                        <input type="number" step="any" name="latitude"
                               value="{{ old('latitude', $survey->latitude ?? '') }}"
                               class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                    </div>

                    <div>
                        <label class="text-sm font-bold">Longitude</label>
                        <input type="number" step="any" name="longitude"
                               value="{{ old('longitude', $survey->longitude ?? '') }}"
                               class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                    </div>
                </div>

                <div class="mt-5">
                    <label class="text-sm font-bold">Alamat Lengkap</label>
                    <textarea name="address" rows="4"
                              class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">{{ old('address', $survey->address ?? '') }}</textarea>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-2xl font-black mb-6">Traffic Manual</h3>

                @php
                    $trafficFields = [
                        'motor_weekday_morning' => 'Motor Weekdays Pagi',
                        'motor_weekday_noon' => 'Motor Weekdays Siang',
                        'motor_weekday_evening' => 'Motor Weekdays Petang',
                        'motor_weekend_morning' => 'Motor Weekend Pagi',
                        'motor_weekend_noon' => 'Motor Weekend Siang',
                        'motor_weekend_evening' => 'Motor Weekend Petang',
                        'pedestrian_weekday_morning' => 'Pejalan Kaki Weekdays Pagi',
                        'pedestrian_weekday_noon' => 'Pejalan Kaki Weekdays Siang',
                        'pedestrian_weekday_evening' => 'Pejalan Kaki Weekdays Petang',
                        'pedestrian_weekend_morning' => 'Pejalan Kaki Weekend Pagi',
                        'pedestrian_weekend_noon' => 'Pejalan Kaki Weekend Siang',
                        'pedestrian_weekend_evening' => 'Pejalan Kaki Weekend Petang',
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    @foreach($trafficFields as $field => $label)
                        <div>
                            <label class="text-sm font-bold">{{ $label }}</label>
                            <input type="number" min="0" name="{{ $field }}"
                                   value="{{ old($field, $survey->$field ?? 0) }}"
                                   class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-2xl font-black mb-6">Hasil Traffic Detection AI</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label class="text-sm font-bold">Motor Terdeteksi</label>
                        <input type="number" min="0" name="detected_motor_traffic"
                               value="{{ old('detected_motor_traffic', $survey->detected_motor_traffic ?? 0) }}"
                               class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                    </div>

                    <div>
                        <label class="text-sm font-bold">Mobil Terdeteksi</label>
                        <input type="number" min="0" name="detected_car_traffic"
                               value="{{ old('detected_car_traffic', $survey->detected_car_traffic ?? 0) }}"
                               class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                    </div>

                    <div>
                        <label class="text-sm font-bold">Pejalan Kaki Terdeteksi</label>
                        <input type="number" min="0" name="detected_pedestrian_traffic"
                               value="{{ old('detected_pedestrian_traffic', $survey->detected_pedestrian_traffic ?? 0) }}"
                               class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                    </div>
                </div>

                <p class="text-sm text-slate-500 mt-4">
                    Mode ringan: AI tidak membuat video hasil deteksi agar CPU VPS tetap aman.
                </p>
            </div>
        </div>

        <div class="space-y-6">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-2xl font-black mb-6">Parameter Site Score</h3>

                @php
                    $params = [
                        'houses_north' => 'Rumah Utara',
                        'houses_south' => 'Rumah Selatan',
                        'houses_east' => 'Rumah Timur',
                        'houses_west' => 'Rumah Barat',
                        'public_facilities_15km' => 'Fasilitas Umum',
                        'culinary_centers' => 'Sentra Kuliner',
                        'competitors' => 'Kompetitor',
                        'average_price' => 'Rata-rata Harga'
                    ];
                @endphp

                <div class="space-y-4">
                    @foreach($params as $field => $label)
                        <div>
                            <label class="text-sm font-bold">{{ $label }}</label>
                            <input type="number" min="0" name="{{ $field }}"
                                   value="{{ old($field, $survey->$field ?? 0) }}"
                                   class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-2xl font-black mb-5">Upload Video Traffic</h3>

                <input type="file" id="trafficVideoInput" name="traffic_video" accept="video/*"
                       class="w-full border border-slate-300 rounded-2xl px-4 py-3">

                <div id="videoPreviewWrapper" class="hidden mt-5">
                    <video id="videoPreview" controls class="w-full rounded-2xl border border-slate-200 shadow-sm"></video>
                </div>

                @if(!empty($survey->traffic_video_path))
                    <div class="mt-5">
                        <p class="font-bold text-sm mb-2">Video tersimpan</p>
                        <video controls class="w-full rounded-2xl border">
                            <source src="{{ asset('storage/'.$survey->traffic_video_path) }}">
                        </video>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-2xl font-black mb-5">AI Worker Status</h3>

                <div id="aiStatusBadge"
                     class="px-4 py-3 rounded-2xl font-black text-center
                     {{ in_array($aiStatus, ['Waiting']) ? 'bg-yellow-100 text-yellow-700' : '' }}
                     {{ in_array($aiStatus, ['Processing']) ? 'bg-blue-100 text-blue-700 animate-pulse' : '' }}
                     {{ in_array($aiStatus, ['Done']) ? 'bg-green-100 text-green-700' : '' }}
                     {{ in_array($aiStatus, ['Failed']) ? 'bg-red-100 text-red-700' : '' }}
                     {{ in_array($aiStatus, ['Cancelled','Manual']) ? 'bg-slate-100 text-slate-700' : '' }}">
                    {{ $aiStatus }}
                </div>

                <div class="mt-4">
                    <div class="w-full bg-slate-200 rounded-full h-4 overflow-hidden">
                        <div id="aiProgressBar"
                             class="bg-purple-600 h-4 rounded-full transition-all"
                             style="width: {{ $aiProgress }}%"></div>
                    </div>
                    <div id="aiProgressText" class="text-center text-sm font-bold mt-2">
                        {{ $aiProgress }}%
                    </div>
                </div>

                @if($isEdit && !empty($survey->traffic_video_path))
                    <div class="grid grid-cols-1 gap-3 mt-5">
                        <form method="POST" action="{{ route('master.surveyor.detect', $survey->id) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-2xl font-black">
                                Jalankan / Ulang AI
                            </button>
                        </form>

                        <form method="POST" action="{{ route('master.surveyor.detect.cancel', $survey->id) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-2xl font-black">
                                Batalkan Antrian
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-2xl font-black mb-5">Status Survey</h3>

                <select name="status" class="w-full border border-slate-300 rounded-2xl px-4 py-3">
                    @foreach(['Draft','Perlu Revisi','Valid','Diproses','Prioritas','Ditolak'] as $status)
                        <option value="{{ $status }}"
                            {{ old('status', $survey->status ?? 'Draft') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>

                <textarea name="notes" rows="4" placeholder="Catatan..."
                          class="w-full mt-4 border border-slate-300 rounded-2xl px-4 py-3">{{ old('notes', $survey->notes ?? '') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-4 rounded-2xl font-black text-lg shadow-lg">
                {{ $isEdit ? 'Update Survey' : 'Simpan Survey' }}
            </button>
        </div>
    </div>
</form>

<script>
    const surveyForm = document.getElementById('surveyForm');
    const progressWrapper = document.getElementById('uploadProgressWrapper');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressPercent = document.getElementById('uploadProgressPercent');
    const statusText = document.getElementById('uploadStatusText');
    const queueInfoText = document.getElementById('queueInfoText');

    const videoInput = document.getElementById('trafficVideoInput');
    const previewWrapper = document.getElementById('videoPreviewWrapper');
    const previewVideo = document.getElementById('videoPreview');

    if (videoInput) {
        videoInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            previewVideo.src = URL.createObjectURL(file);
            previewWrapper.classList.remove('hidden');
        });
    }

    surveyForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(surveyForm);
        const xhr = new XMLHttpRequest();

        progressWrapper.classList.remove('hidden');
        progressBar.style.width = '0%';
        progressPercent.innerText = '0%';
        statusText.innerText = 'Mengupload data dan video...';
        queueInfoText.classList.add('hidden');

        xhr.open(surveyForm.method, surveyForm.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
                const percent = Math.round((event.loaded / event.total) * 100);
                progressBar.style.width = percent + '%';
                progressPercent.innerText = percent + '%';

                if (percent >= 100) {
                    statusText.innerText = 'Upload selesai. Menyimpan ke database...';
                    queueInfoText.classList.remove('hidden');
                }
            }
        });

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 400) {
                let response = null;

                try {
                    response = JSON.parse(xhr.responseText);
                } catch (e) {}

                if (response && response.redirect) {
                    window.location.href = response.redirect;
                } else {
                    window.location.href = xhr.responseURL || "{{ route('master.surveyor.index') }}";
                }
            } else {
                progressWrapper.classList.add('hidden');
                alert('Gagal menyimpan survey. Cek validasi atau log Laravel.');
            }
        };

        xhr.onerror = function () {
            progressWrapper.classList.add('hidden');
            alert('Upload gagal.');
        };

        xhr.send(formData);
    });

    @if($isEdit)
    const statusUrl = "{{ route('master.surveyor.traffic.status', $survey->id) }}";
    const aiStatusBadge = document.getElementById('aiStatusBadge');
    const aiProgressBar = document.getElementById('aiProgressBar');
    const aiProgressText = document.getElementById('aiProgressText');

    function pollAiStatus() {
        fetch(statusUrl)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;

                const data = res.data;
                const status = data.traffic_detection_status || 'Manual';
                const progress = parseInt(data.traffic_detection_progress || 0);

                aiStatusBadge.className = 'px-4 py-3 rounded-2xl font-black text-center';

                if (status === 'Waiting') aiStatusBadge.className += ' bg-yellow-100 text-yellow-700';
                else if (status === 'Processing') aiStatusBadge.className += ' bg-blue-100 text-blue-700 animate-pulse';
                else if (status === 'Done') aiStatusBadge.className += ' bg-green-100 text-green-700';
                else if (status === 'Failed') aiStatusBadge.className += ' bg-red-100 text-red-700';
                else aiStatusBadge.className += ' bg-slate-100 text-slate-700';

                aiStatusBadge.innerText = status;
                aiProgressBar.style.width = progress + '%';
                aiProgressText.innerText = progress + '%';

                if (status === 'Done' || status === 'Failed') {
                    setTimeout(() => window.location.reload(), 1200);
                }
            })
            .catch(() => {});
    }

    setInterval(pollAiStatus, 3000);
    @endif
</script>

@endsection

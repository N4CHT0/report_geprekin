@extends('Investor.Surveyor._layout')

@section('title', 'Detail Survey')
@section('page_title', 'Detail Survey Site Score')

@section('content')

@php
    $aiStatus = $survey->traffic_detection_status ?? 'Manual';
    $aiProgress = (int) ($survey->traffic_detection_progress ?? 0);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200">
            <div class="flex justify-between items-start gap-5">
                <div>
                    <h2 class="text-2xl font-black">{{ $survey->location_type }}</h2>
                    <p class="text-slate-500 mt-1">{{ $survey->city }}</p>
                </div>

                <span class="px-4 py-2 rounded-xl bg-blue-100 text-blue-700 font-black">
                    Grade {{ $survey->grade }}
                </span>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <div class="text-sm text-slate-500">Nama Surveyor</div>
                    <div class="font-bold mt-1">{{ $survey->surveyor_name }}</div>
                </div>

                <div>
                    <div class="text-sm text-slate-500">Metode Input</div>
                    <div class="font-bold mt-1">{{ $survey->input_method }}</div>
                </div>

                <div>
                    <div class="text-sm text-slate-500">Koordinat</div>
                    <div class="font-bold mt-1">{{ $survey->latitude ?? '-' }}, {{ $survey->longitude ?? '-' }}</div>
                </div>

                <div>
                    <div class="text-sm text-slate-500">Status</div>
                    <div class="font-bold mt-1">{{ $survey->status }}</div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-sm text-slate-500">Alamat</div>
                    <div class="font-bold mt-1">{{ $survey->address }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-black">AI Traffic Detection Ringan</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Mode hemat CPU: tanpa tracking berat dan tanpa render video hasil.
                    </p>
                </div>

                <div id="aiStatusBadge"
                     class="px-4 py-2 rounded-xl font-black
                     {{ $aiStatus == 'Waiting' ? 'bg-yellow-100 text-yellow-700' : '' }}
                     {{ $aiStatus == 'Processing' ? 'bg-blue-100 text-blue-700 animate-pulse' : '' }}
                     {{ $aiStatus == 'Done' ? 'bg-green-100 text-green-700' : '' }}
                     {{ $aiStatus == 'Failed' ? 'bg-red-100 text-red-700' : '' }}
                     {{ in_array($aiStatus, ['Manual','Cancelled']) ? 'bg-slate-100 text-slate-700' : '' }}">
                    {{ $aiStatus }}
                </div>
            </div>

            <div class="w-full bg-slate-200 rounded-full h-4 overflow-hidden mb-2">
                <div id="aiProgressBar"
                     class="bg-purple-600 h-4 rounded-full transition-all"
                     style="width: {{ $aiProgress }}%"></div>
            </div>

            <div id="aiProgressText" class="text-center text-sm font-bold mb-6">{{ $aiProgress }}%</div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="rounded-2xl bg-yellow-50 border border-yellow-200 p-5">
                    <div class="text-sm text-yellow-700">Motor</div>
                    <div id="detectedMotor" class="text-3xl font-black mt-2">{{ number_format($survey->detected_motor_traffic ?? 0) }}</div>
                </div>

                <div class="rounded-2xl bg-blue-50 border border-blue-200 p-5">
                    <div class="text-sm text-blue-700">Mobil</div>
                    <div id="detectedCar" class="text-3xl font-black mt-2">{{ number_format($survey->detected_car_traffic ?? 0) }}</div>
                </div>

                <div class="rounded-2xl bg-green-50 border border-green-200 p-5">
                    <div class="text-sm text-green-700">Pejalan Kaki</div>
                    <div id="detectedPerson" class="text-3xl font-black mt-2">{{ number_format($survey->detected_pedestrian_traffic ?? 0) }}</div>
                </div>
            </div>

            @if(!empty($survey->traffic_detection_error))
                <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm">
                    {{ $survey->traffic_detection_error }}
                </div>
            @endif

            @if(!empty($survey->traffic_video_path))
                <div class="flex flex-col md:flex-row gap-3 mb-6">
                    <form method="POST" action="{{ route('master.surveyor.detect', $survey->id) }}">
                        @csrf
                        <button type="submit"
                                class="px-5 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-black">
                            Jalankan / Ulang AI
                        </button>
                    </form>

                    <form method="POST" action="{{ route('master.surveyor.detect.cancel', $survey->id) }}">
                        @csrf
                        <button type="submit"
                                class="px-5 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-black">
                            Batalkan Antrian
                        </button>
                    </form>
                </div>

                <div>
                    <p class="font-bold mb-3">Video Original</p>
                    <video controls class="w-full rounded-2xl border">
                        <source src="{{ asset('storage/'.$survey->traffic_video_path) }}">
                    </video>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200">
            <h3 class="text-xl font-black mb-5">Traffic Manual</h3>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @php
                    $traffic = [
                        'Motor Weekday Pagi' => $survey->motor_weekday_morning,
                        'Motor Weekday Siang' => $survey->motor_weekday_noon,
                        'Motor Weekday Petang' => $survey->motor_weekday_evening,
                        'Motor Weekend Pagi' => $survey->motor_weekend_morning,
                        'Motor Weekend Siang' => $survey->motor_weekend_noon,
                        'Motor Weekend Petang' => $survey->motor_weekend_evening,
                        'Pejalan Weekday Pagi' => $survey->pedestrian_weekday_morning,
                        'Pejalan Weekday Siang' => $survey->pedestrian_weekday_noon,
                        'Pejalan Weekday Petang' => $survey->pedestrian_weekday_evening,
                        'Pejalan Weekend Pagi' => $survey->pedestrian_weekend_morning,
                        'Pejalan Weekend Siang' => $survey->pedestrian_weekend_noon,
                        'Pejalan Weekend Petang' => $survey->pedestrian_weekend_evening,
                    ];
                @endphp

                @foreach($traffic as $label => $value)
                    <div class="border border-slate-200 rounded-2xl p-4">
                        <div class="text-sm text-slate-500">{{ $label }}</div>
                        <div class="text-2xl font-black mt-2">{{ number_format($value ?? 0) }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200">
            <h3 class="text-xl font-black mb-5">Site Score</h3>
            <div class="text-center">
                <div id="siteScore" class="text-6xl font-black text-blue-600">{{ $survey->site_score }}</div>
                <div id="gradeText" class="mt-2 text-slate-500">Grade {{ $survey->grade }}</div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm p-6 border border-slate-200">
            <h3 class="text-xl font-black mb-5">Estimasi Sales</h3>
            <div class="space-y-5">
                <div>
                    <div class="text-sm text-slate-500">Daily Sales</div>
                    <div id="dailySales" class="text-2xl font-black text-green-600 mt-1">
                        Rp {{ number_format($survey->estimated_daily_sales ?? 0) }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-slate-500">Monthly Sales</div>
                    <div id="monthlySales" class="text-2xl font-black text-blue-600 mt-1">
                        Rp {{ number_format($survey->estimated_monthly_sales ?? 0) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <a href="{{ route('master.surveyor.edit', $survey->id) }}"
               class="block w-full text-center bg-yellow-500 hover:bg-yellow-600 text-white py-4 rounded-2xl font-bold">
                Edit Survey
            </a>

            <form method="POST" action="{{ route('master.surveyor.destroy', $survey->id) }}">
                @csrf
                @method('DELETE')

                <button type="submit"
                        onclick="return confirm('Hapus data survey?')"
                        class="w-full bg-red-600 hover:bg-red-700 text-white py-4 rounded-2xl font-bold">
                    Hapus Survey
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const statusUrl = "{{ route('master.surveyor.traffic.status', $survey->id) }}";
    const aiStatusBadge = document.getElementById('aiStatusBadge');
    const aiProgressBar = document.getElementById('aiProgressBar');
    const aiProgressText = document.getElementById('aiProgressText');

    function numberFormat(value) {
        return new Intl.NumberFormat('id-ID').format(value || 0);
    }

    function pollAiStatus() {
        fetch(statusUrl)
            .then(res => res.json())
            .then(res => {
                if (!res.success) return;

                const data = res.data;
                const status = data.traffic_detection_status || 'Manual';
                const progress = parseInt(data.traffic_detection_progress || 0);

                aiStatusBadge.className = 'px-4 py-2 rounded-xl font-black';

                if (status === 'Waiting') aiStatusBadge.className += ' bg-yellow-100 text-yellow-700';
                else if (status === 'Processing') aiStatusBadge.className += ' bg-blue-100 text-blue-700 animate-pulse';
                else if (status === 'Done') aiStatusBadge.className += ' bg-green-100 text-green-700';
                else if (status === 'Failed') aiStatusBadge.className += ' bg-red-100 text-red-700';
                else aiStatusBadge.className += ' bg-slate-100 text-slate-700';

                aiStatusBadge.innerText = status;
                aiProgressBar.style.width = progress + '%';
                aiProgressText.innerText = progress + '%';

                document.getElementById('detectedMotor').innerText = numberFormat(data.detected_motor_traffic);
                document.getElementById('detectedCar').innerText = numberFormat(data.detected_car_traffic);
                document.getElementById('detectedPerson').innerText = numberFormat(data.detected_pedestrian_traffic);
                document.getElementById('siteScore').innerText = data.site_score ?? 0;
                document.getElementById('gradeText').innerText = 'Grade ' + (data.grade ?? '-');
                document.getElementById('dailySales').innerText = 'Rp ' + numberFormat(data.estimated_daily_sales);
                document.getElementById('monthlySales').innerText = 'Rp ' + numberFormat(data.estimated_monthly_sales);
            })
            .catch(() => {});
    }

    setInterval(pollAiStatus, 3000);
</script>

@endsection

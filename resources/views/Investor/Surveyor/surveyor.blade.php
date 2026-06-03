@extends('Investor.Surveyor._layout')

@section('title', 'Dashboard Surveyor Site Score')
@section('page_title', 'Dashboard Surveyor Site Score')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #surveyMap { height: 430px; border-radius: 1.5rem; overflow: hidden; }
</style>
@endpush

@section('content')

{{-- HERO --}}
<div class="bg-gradient-to-r from-slate-950 via-blue-950 to-slate-900 rounded-[2rem] p-7 text-white mb-7 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <p class="text-blue-300 text-sm font-bold uppercase tracking-wider">Expansion Intelligence</p>
            <h1 class="text-3xl lg:text-4xl font-black mt-2">Pantau Potensi Lokasi Outlet</h1>
            <p class="text-slate-300 mt-3 max-w-3xl">
                Dashboard ini menampilkan rekap survey, ranking kota, performa site score,
                estimasi sales, dan persebaran titik lokasi berdasarkan grade.
            </p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('master.surveyor.create') }}"
               class="px-5 py-3 rounded-2xl bg-blue-500 hover:bg-blue-600 font-bold">
                + Input Survey
            </a>
        </div>
    </div>
</div>

{{-- STATS --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-6 gap-4 mb-7">
    @php
        $cards = [
            ['label' => 'Total Survey', 'value' => $stats['total'] ?? 0, 'icon' => '📍', 'color' => 'blue'],
            ['label' => 'Prioritas A/B', 'value' => $stats['priority'] ?? 0, 'icon' => '⭐', 'color' => 'emerald'],
            ['label' => 'Avg Score', 'value' => $stats['avg_score'] ?? 0, 'icon' => '📊', 'color' => 'purple'],
            ['label' => 'Avg Sales', 'value' => 'Rp '.number_format($stats['avg_sales'] ?? 0), 'icon' => '💰', 'color' => 'amber'],
            ['label' => 'Valid', 'value' => $stats['valid'] ?? 0, 'icon' => '✅', 'color' => 'green'],
            ['label' => 'Revisi', 'value' => $stats['revision'] ?? 0, 'icon' => '⚠️', 'color' => 'red'],
        ];
    @endphp

    @foreach($cards as $card)
        <div class="bg-white rounded-3xl p-5 border border-slate-200 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm text-slate-500">{{ $card['label'] }}</p>
                    <div class="text-2xl font-black mt-2">{{ $card['value'] }}</div>
                </div>
                <div class="text-3xl">{{ $card['icon'] }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- ANALYTICS --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-7">
    <div class="xl:col-span-2 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-xl font-black">Peta Lokasi Survey</h3>
                <p class="text-sm text-slate-500">Marker warna mengikuti grade site score.</p>
            </div>
        </div>

        <div id="surveyMap"></div>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <h3 class="text-xl font-black mb-1">Distribusi Grade</h3>
        <p class="text-sm text-slate-500 mb-5">Jumlah lokasi per grade.</p>
        <canvas id="gradeChart" height="240"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-7">
    <div class="xl:col-span-2 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-xl font-black">Ranking Kota</h3>
                <p class="text-sm text-slate-500">Diurutkan berdasarkan rata-rata site score tertinggi.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100 text-slate-600">
                        <th class="text-left p-4 rounded-l-2xl">Kota</th>
                        <th class="text-left p-4">Total Survey</th>
                        <th class="text-left p-4">Avg Score</th>
                        <th class="text-left p-4 rounded-r-2xl">Avg Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cityRanking as $city)
                        <tr class="border-b border-slate-100">
                            <td class="p-4 font-bold">{{ $city->city }}</td>
                            <td class="p-4">{{ number_format($city->total_survey) }}</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold">
                                    {{ $city->avg_score }}
                                </span>
                            </td>
                            <td class="p-4 font-bold">Rp {{ number_format($city->avg_sales) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500">
                                Belum ada data ranking kota.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <h3 class="text-xl font-black mb-1">Top Lokasi</h3>
        <p class="text-sm text-slate-500 mb-5">Lokasi paling potensial.</p>

        <div class="space-y-3">
            @forelse($topLocations as $item)
                <a href="{{ route('master.surveyor.show', $item->id) }}"
                   class="block border border-slate-200 rounded-2xl p-4 hover:bg-slate-50 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-black">{{ $item->location_type }}</div>
                            <div class="text-sm text-slate-500 mt-1">{{ $item->city }}</div>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-black">
                            {{ $item->grade }}
                        </span>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div class="text-slate-500">Score</div>
                            <div class="font-black">{{ $item->site_score }}</div>
                        </div>
                        <div>
                            <div class="text-slate-500">Sales</div>
                            <div class="font-black">Rp {{ number_format($item->estimated_monthly_sales) }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center text-slate-500 py-8">Belum ada lokasi.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- FILTER --}}
<div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm mb-7">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
            <label class="text-sm font-bold text-slate-600">Cari</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Surveyor, kota, alamat, jenis lokasi..."
                   class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
        </div>

        <div>
            <label class="text-sm font-bold text-slate-600">Status</label>
            <select name="status" class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                <option value="">Semua</option>
                @foreach(['Draft','Perlu Revisi','Valid','Diproses','Prioritas','Ditolak'] as $status)
                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-bold text-slate-600">Grade</label>
            <select name="grade" class="w-full mt-2 border border-slate-300 rounded-2xl px-4 py-3">
                <option value="">Semua</option>
                @foreach(['A','B','C','D'] as $grade)
                    <option value="{{ $grade }}" {{ request('grade') == $grade ? 'selected' : '' }}>
                        Grade {{ $grade }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white rounded-2xl px-4 py-3 font-bold">
                Filter
            </button>
        </div>
    </form>
</div>

{{-- TABLE --}}
<div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-black">Data Survey Site Score</h3>
            <p class="text-sm text-slate-500">CRUD utama survey lokasi.</p>
        </div>

        <a href="{{ route('master.surveyor.create') }}"
           class="px-4 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700">
            + Tambah
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="text-left p-4">Lokasi</th>
                    <th class="text-left p-4">Surveyor</th>
                    <th class="text-left p-4">Score</th>
                    <th class="text-left p-4">Grade</th>
                    <th class="text-left p-4">Sales/Bulan</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-right p-4">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($surveys as $survey)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="p-4">
                            <div class="font-black">{{ $survey->location_type }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ $survey->city }} - {{ $survey->address }}</div>
                        </td>

                        <td class="p-4">{{ $survey->surveyor_name }}</td>

                        <td class="p-4">
                            <span class="font-black text-blue-700">{{ $survey->site_score }}</span>
                        </td>

                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-black
                                {{ $survey->grade == 'A' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $survey->grade == 'B' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $survey->grade == 'C' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $survey->grade == 'D' ? 'bg-red-100 text-red-700' : '' }}">
                                Grade {{ $survey->grade }}
                            </span>
                        </td>

                        <td class="p-4 font-bold">
                            Rp {{ number_format($survey->estimated_monthly_sales) }}
                        </td>

                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                {{ $survey->status }}
                            </span>
                        </td>

                        <td class="p-4">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('master.surveyor.show', $survey->id) }}"
                                   class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 font-bold text-xs">
                                    Detail
                                </a>

                                <a href="{{ route('master.surveyor.edit', $survey->id) }}"
                                   class="px-3 py-2 rounded-xl bg-yellow-100 hover:bg-yellow-200 text-yellow-800 font-bold text-xs">
                                    Edit
                                </a>

                                <form method="POST"
                                      action="{{ route('master.surveyor.destroy', $survey->id) }}"
                                      onsubmit="return confirm('Hapus data survey ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-2 rounded-xl bg-red-100 hover:bg-red-200 text-red-800 font-bold text-xs">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-slate-500">
                            Belum ada data survey.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-5">
        {{ $surveys->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const gradeLabels = @json($gradeLabels);
    const gradeData = @json($gradeData);

    new Chart(document.getElementById('gradeChart'), {
        type: 'doughnut',
        data: {
            labels: gradeLabels,
            datasets: [{
                data: gradeData,
                backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            cutout: '62%'
        }
    });

    const mapLocations = @json($mapLocations);

    const map = L.map('surveyMap').setView([-2.5, 118], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    const bounds = [];

    function gradeColor(grade) {
        if (grade === 'A') return '#16a34a';
        if (grade === 'B') return '#2563eb';
        if (grade === 'C') return '#f59e0b';
        return '#ef4444';
    }

    mapLocations.forEach(function(item) {
        if (!item.latitude || !item.longitude) return;

        const lat = parseFloat(item.latitude);
        const lng = parseFloat(item.longitude);
        bounds.push([lat, lng]);

        const marker = L.circleMarker([lat, lng], {
            radius: 9,
            fillColor: gradeColor(item.grade),
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);

        marker.bindPopup(`
            <div style="font-size:13px;min-width:220px">
                <b>${item.location_type ?? '-'}</b><br>
                <small>${item.city ?? '-'} - ${item.address ?? '-'}</small>
                <hr style="margin:8px 0">
                Score: <b>${item.site_score ?? 0}</b><br>
                Grade: <b>${item.grade ?? '-'}</b><br>
                Sales/Bulan: <b>Rp ${Number(item.estimated_monthly_sales ?? 0).toLocaleString('id-ID')}</b><br>
                Status: <b>${item.status ?? '-'}</b>
            </div>
        `);
    });

    if (bounds.length) {
        map.fitBounds(bounds, { padding: [30, 30] });
    }
</script>
@endpush

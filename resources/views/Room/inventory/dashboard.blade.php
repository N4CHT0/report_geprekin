@extends('Room.layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Analitik Inventaris IT</h1>
        <p class="text-sm text-slate-500">Ringkasan status seluruh aset IT perusahaan.</p>
    </div>

    <!-- Statistik Utama -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
            <div class="text-slate-500 text-xs font-bold uppercase mb-1">Total Aset</div>
            <div class="text-3xl font-black text-slate-800">{{ $totalLaptops }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-green-200">
            <div class="text-green-600 text-xs font-bold uppercase mb-1">Tersedia</div>
            <div class="text-3xl font-black text-green-700">{{ $available }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-blue-200">
            <div class="text-blue-600 text-xs font-bold uppercase mb-1">Digunakan</div>
            <div class="text-3xl font-black text-blue-700">{{ $inUse }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-amber-200">
            <div class="text-amber-600 text-xs font-bold uppercase mb-1">Maintenance</div>
            <div class="text-3xl font-black text-amber-700">{{ $maintenance }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-red-200">
            <div class="text-red-600 text-xs font-bold uppercase mb-1">Rusak</div>
            <div class="text-3xl font-black text-red-700">{{ $damaged }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-300 bg-slate-50">
            <div class="text-slate-600 text-xs font-bold uppercase mb-1">Hilang</div>
            <div class="text-3xl font-black text-slate-700">{{ $missing }}</div>
        </div>
    </div>

    <!-- Peringatan Garansi -->
    @if($warrantyEndingSoon->count() > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-8">
        <h3 class="font-bold text-amber-800 flex items-center gap-2 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Perhatian: Masa Garansi Segera Habis (Kurang dari 30 Hari)
        </h3>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($warrantyEndingSoon as $asset)
            <div class="bg-white p-4 rounded-lg shadow-sm border border-amber-100 flex justify-between items-center">
                <div>
                    <div class="font-bold text-slate-800">{{ $asset->asset_code }}</div>
                    <div class="text-xs text-slate-500">{{ $asset->brand_model }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-amber-600">{{ \Carbon\Carbon::parse($asset->warranty_expired_at)->format('d M Y') }}</div>
                    <div class="text-[10px] text-slate-400">Tgl Berakhir</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

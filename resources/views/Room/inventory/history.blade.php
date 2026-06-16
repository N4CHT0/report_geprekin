@extends('Room.layouts.app')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('inventory.master') }}" class="text-[#2A435D] hover:underline flex items-center gap-2 mb-4 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Master Aset
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Detail Aset IT</h1>
        <p class="text-sm text-slate-500">Histori mutasi, audit, dan servis untuk perangkat ini.</p>
    </div>

    <!-- Info Laptop -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8 flex flex-col sm:flex-row gap-6 items-center sm:items-start justify-between">
        <div class="flex gap-6 items-center sm:items-start">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 hidden sm:block">
                <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-800 mb-1 font-mono tracking-wider">{{ $laptop->asset_code }}</h2>
                <p class="font-semibold text-slate-600 mb-3">{{ $laptop->brand_model }}</p>
                <div class="flex gap-4 text-sm">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-1">Serial Number</span>
                        <span class="font-medium text-slate-700">{{ $laptop->serial_number }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider mb-1">Status Saat Ini</span>
                        @php
                            $statusColor = match($laptop->status) {
                                'Available' => 'bg-emerald-100 text-emerald-700',
                                'In Use' => 'bg-blue-100 text-blue-700',
                                'Maintenance' => 'bg-amber-100 text-amber-700',
                                'Disposed' => 'bg-slate-200 text-slate-600',
                                default => 'bg-slate-100 text-slate-700'
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $statusColor }}">{{ $laptop->status }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center sm:text-right">
            <svg class="barcode-svg h-16 w-auto" data-barcode="{{ $laptop->barcode }}"></svg>
        </div>
    </div>

    <!-- Timeline Riwayat -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
        <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4 mb-8">Log Histori Perjalanan Aset</h3>
        
        @if(count($logs) > 0)
        <div class="relative border-l-2 border-slate-200 ml-4 space-y-8 pb-4">
            @foreach($logs as $log)
                @php
                    $iconBg = 'bg-slate-100';
                    $iconColor = 'text-slate-500';
                    $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';

                    if($log->action_type === 'Add') {
                        $iconBg = 'bg-emerald-100'; $iconColor = 'text-emerald-600';
                        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>';
                    } elseif($log->action_type === 'Update') {
                        $iconBg = 'bg-blue-100'; $iconColor = 'text-blue-600';
                        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>';
                    } elseif($log->action_type === 'Assign') {
                        $iconBg = 'bg-indigo-100'; $iconColor = 'text-indigo-600';
                        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>';
                    } elseif($log->action_type === 'Return') {
                        $iconBg = 'bg-amber-100'; $iconColor = 'text-amber-600';
                        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>';
                    } elseif($log->action_type === 'Audit') {
                        $iconBg = 'bg-purple-100'; $iconColor = 'text-purple-600';
                        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                    } elseif($log->action_type === 'Maintenance') {
                        $iconBg = 'bg-red-100'; $iconColor = 'text-red-600';
                        $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
                    }
                @endphp
                <div class="relative pl-8">
                    <!-- Lingkaran Icon -->
                    <div class="absolute -left-5 top-0 w-10 h-10 rounded-full {{ $iconBg }} {{ $iconColor }} flex items-center justify-center border-4 border-white shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconSvg !!}</svg>
                    </div>
                    
                    <!-- Konten Log -->
                    <div class="bg-slate-50 border border-slate-100 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-2">
                            <h4 class="font-bold text-slate-800">{{ $log->action_type }}</h4>
                            <span class="text-xs font-semibold text-slate-400">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</span>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $log->notes }}</p>
                        <div class="mt-3 text-xs text-slate-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Oleh: <span class="font-medium">{{ $log->admin_name ?? 'Sistem' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-10 text-slate-400">
            <p>Belum ada riwayat tercatat untuk perangkat ini.</p>
        </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const barcodes = document.querySelectorAll('.barcode-svg');
        barcodes.forEach(function(svg) {
            const code = svg.getAttribute('data-barcode');
            JsBarcode(svg, code, {
                format: "CODE128",
                width: 2,
                height: 40,
                displayValue: false,
                background: "transparent",
                lineColor: "#2A435D"
            });
        });
    });
</script>
@endsection

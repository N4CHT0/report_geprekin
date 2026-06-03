@extends('Room.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Halo, {{ auth()->user()->name ?? 'Karyawan' }} 👋</h2>
        <p class="text-slate-500 text-sm mt-1">Selamat datang di Sistem Reservasi Ruangan Head Office.</p>
    </div>
    <div class="inline-flex items-center justify-center bg-white px-4 py-2.5 rounded-xl shadow-sm border border-slate-200 text-sm font-semibold text-[#2A435D]">
        <svg class="w-5 h-5 mr-2 text-[#F59E0B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
    </div>
</div>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-6">
    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow group">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Total Ruangan</div>
                <div class="text-3xl font-black text-[#2A435D]">{{ $totalRooms ?? 4 }}</div>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl text-blue-600 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow group">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Reservasi Hari Ini</div>
                <div class="text-3xl font-black text-emerald-600">{{ $todayReservations ?? 12 }}</div>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow group">
        <div class="relative z-10 flex justify-between items-start">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Pemesanan Saya</div>
                <div class="text-3xl font-black text-[#F59E0B]">{{ $myPending ?? 2 }}</div>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl text-amber-500 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 flex flex-col gap-6">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-base font-bold text-slate-900">Jadwal Minggu Ini</h3>
                <div class="flex gap-2">
                    <a href="{{ route('dashboard', ['date' => $prevWeek]) }}" class="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg></a>
                    <a href="{{ route('dashboard', ['date' => $nextWeek]) }}" class="p-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg></a>
                </div>
            </div>

            <div class="flex justify-between gap-2 overflow-x-auto pb-2 hide-scrollbar">
                @foreach($weekDates as $day)
                <a href="{{ route('dashboard', ['date' => $day['full_date']]) }}"
                    class="flex flex-col items-center justify-center p-3 w-16 rounded-xl border transition-all hover:-translate-y-1 {{ $day['is_selected'] ? 'border-2 border-[#2A435D] bg-[#2A435D] text-white shadow-md relative' : 'border-slate-100 bg-white hover:bg-slate-50' }}">
                    <span class="text-xs font-semibold {{ $day['is_selected'] ? 'text-slate-300' : 'text-slate-400' }}">{{ $day['day_name'] }}</span>
                    <span class="text-lg font-bold my-1 {{ $day['is_selected'] ? 'text-white' : 'text-slate-700' }}">{{ $day['date_num'] }}</span>
                    @if($day['has_schedule'])
                    <div class="w-1.5 h-1.5 rounded-full {{ $day['is_selected'] ? 'bg-[#F59E0B]' : 'bg-blue-500' }}"></div>
                    @endif
                </a>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm flex flex-col overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    Jadwal Pemakaian Ruang: <span class="text-blue-600 ml-1">{{ \Carbon\Carbon::parse($selectedDateStr)->translatedFormat('d M Y') }}</span>
                </h3>
            </div>
            <div class="p-0 overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-3 font-semibold text-slate-700">Waktu</th>
                            <th class="px-5 py-3 font-semibold text-slate-700">Ruangan</th>
                            <th class="px-5 py-3 font-semibold text-slate-700">Divisi / Peminjam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($groupedAgenda as $agenda)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap font-bold text-[#2A435D]">{{ $agenda['start_time'] }} - {{ $agenda['end_time'] }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $agenda['room_name'] }}</td>
                            <td class="px-5 py-3.5 flex items-center gap-2">
                                <div class="w-6 h-6 shrink-0 rounded-full bg-[#2A435D] text-white flex items-center justify-center text-[10px] font-bold">{{ strtoupper(substr($agenda['division'], 0, 2)) }}</div>
                                <span class="truncate">{{ $agenda['division'] }} ({{ explode(' ', trim($agenda['user_name']))[0] }})</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-400">
                                    <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Tidak ada reservasi di tanggal ini.
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex flex-col gap-6">

        <div class="rounded-2xl bg-[#2A435D] p-6 shadow-md text-white relative overflow-hidden">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
            <h3 class="text-lg font-bold mb-2 relative z-10">Butuh Ruangan?</h3>
            <p class="text-sm text-slate-300 mb-5 relative z-10">Pesan ruangan meeting Anda sekarang sebelum keduluan divisi lain.</p>
            <a href="{{ route('reservations.index') }}" class="relative z-10 flex w-full items-center justify-center rounded-xl bg-[#F59E0B] px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-[#D97706] transition-colors gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Reservasi Baru
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-900">Live Status Ruangan</h3>
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
            </div>

            <ul class="flex flex-col gap-3">
                @foreach($liveStatus as $status)
                <li class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full {{ $status['is_used'] ? 'bg-red-500' : 'bg-emerald-500' }}"></div>
                        <span class="text-sm font-medium text-slate-700">{{ $status['name'] }}</span>
                    </div>
                    @if($status['is_used'])
                    <span class="text-[10px] font-bold bg-red-100 text-red-700 px-2 py-1 rounded-md">DIPAKAI</span>
                    @else
                    <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-1 rounded-md">KOSONG</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>

    </div>
</div>

<style>
    /* CSS kecil untuk menyembunyikan scrollbar di kalender mingguan */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection
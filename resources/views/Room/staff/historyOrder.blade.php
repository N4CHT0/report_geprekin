@extends('Room.layouts.app')

@section('title', 'Riwayat Reservasi')

@section('content')
<div>
    <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Riwayat Reservasi</h2>
            <p class="text-sm text-slate-500">Pantau status pengajuan dan riwayat peminjaman ruangan Anda.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-3 rounded-lg text-emerald-700 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg text-red-700 text-sm font-medium">
            @foreach ($errors->all() as $error) {{ $error }} <br> @endforeach
        </div>
    @endif

    <div class="mb-4 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <form method="GET" action="{{ route('reservations.history') }}" class="w-full flex flex-col sm:flex-row justify-between items-center gap-4" id="filterForm">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <span class="text-sm text-slate-500">Tampilkan:</span>
                <select name="per_page" onchange="document.getElementById('filterForm').submit()" class="bg-slate-50 border border-slate-300 text-slate-700 py-2 px-3 rounded-xl text-sm focus:ring-[#2A435D] focus:border-[#2A435D]">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 baris</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 baris</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 baris</option>
                </select>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <select name="status" onchange="document.getElementById('filterForm').submit()" class="w-full sm:w-44 bg-slate-50 border border-slate-300 text-slate-700 py-2 pl-3 pr-8 rounded-xl text-sm focus:ring-[#2A435D] focus:border-[#2A435D]">
                    <option value="All" {{ request('status') == 'All' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Disetujui</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Menunggu</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ruangan / agenda..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-[#2A435D] focus:border-[#2A435D]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <button type="submit" class="bg-[#2A435D] text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-[#1F3246] transition-colors hidden sm:block">Cari</button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 relative">
                <thead class="bg-slate-50 text-slate-700 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Jadwal Reservasi</th>
                        <th class="px-6 py-4 font-semibold">Peminjam (Divisi)</th>
                        <th class="px-6 py-4 font-semibold">Ruangan</th>
                        <th class="px-6 py-4 font-semibold">Agenda Keperluan</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($histories as $history)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($history->reservation_date)->format('d M Y') }}</div>
                            <div class="text-xs font-semibold text-blue-600 mt-0.5 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $history->time_range ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900">{{ $history->user_name ?? 'Karyawan' }}</div>
                            <div class="text-[11px] text-slate-500 font-medium">Divisi: {{ $history->division }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium text-[#2A435D]">{{ $history->room_name ?? 'Ruang Meeting' }}</td>
                        <td class="px-6 py-4 text-slate-600">
                            <div class="truncate max-w-[200px]" title="{{ $history->agenda }}">
                                "{{ $history->agenda }}"
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($history->status === 'Approved')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500"></div> Approved
                                </span>
                            @elseif($history->status === 'Pending')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div> Pending
                                </span>
                            @elseif($history->status === 'Rejected')
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 border border-red-200">
                                        <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div> Rejected
                                    </span>
                                </div>
                            @elseif($history->status === 'Cancelled')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 border border-slate-300">
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div> Cancelled
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($history->reservation_date >= \Carbon\Carbon::now()->toDateString() && in_array($history->status, ['Pending', 'Approved']))
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('reservations.reschedule', $history->id) }}" class="inline-flex items-center justify-center rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100 transition-all">
                                        Reschedule
                                    </a>
                                    <form action="{{ route('reservations.cancel', $history->id) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan jadwal ini?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-all">
                                            Batalkan
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 flex flex-col items-center">
                            <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            Belum ada riwayat reservasi ruangan yang sesuai pencarian.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $histories->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
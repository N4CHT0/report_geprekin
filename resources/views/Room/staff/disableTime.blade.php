@extends('Room.layouts.app')

@section('title', 'Master Pemblokiran Ruangan')

@section('content')
<div x-data="{ isModalOpen: false }">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Pemblokiran Ruangan</h2>
            <p class="text-sm text-slate-500">Tutup jadwal ruangan untuk perbaikan (Maintenance) atau keperluan khusus.</p>
        </div>
        <button type="button" @click="isModalOpen = true" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
            + Blokir Jadwal Baru
        </button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                <tr>
                    <th class="px-6 py-4 font-semibold">Tanggal</th>
                    <th class="px-6 py-4 font-semibold">Ruangan</th>
                    <th class="px-6 py-4 font-semibold">Alasan Penutupan</th>
                    <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($maintenances ?? [] as $maint)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-medium text-slate-900">{{ \Carbon\Carbon::parse($maint->reservation_date)->format('d M Y') }}</td>
                    <td class="px-6 py-4">{{ $maint->room_name }}</td>
                    <td class="px-6 py-4 italic">"{{ $maint->agenda }}"</td>
                    <td class="px-6 py-4 text-right">
                        <form action="{{ route('admin.maintenance.destroy', $maint->id) }}" method="POST" onsubmit="return confirm('Buka blokir jadwal ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                                Buka Blokir
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada ruangan yang diblokir.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="isModalOpen" x-cloak class="relative z-50">
        <div x-show="isModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div @click.away="isModalOpen = false" class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-xl">
                    <form action="{{ route('admin.maintenance.store') }}" method="POST">
                        @csrf
                        <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                            <h3 class="text-xl font-bold text-red-700">Tutup Jadwal Ruangan</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Pilih Ruangan</label>
                                <select name="room_id" required class="mt-1 w-full rounded-xl border-slate-300 px-4 py-2.5">
                                    <option value="">-- Pilih --</option>
                                    @foreach($rooms as $room) <option value="{{ $room->id }}">{{ $room->name }}</option> @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tanggal Penutupan</label>
                                <input type="date" name="reservation_date" required class="mt-1 w-full rounded-xl border-slate-300 px-4 py-2.5">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Pilih Jam yang Ditutup</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach($timeSlots as $slot)
                                    <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-2 cursor-pointer hover:bg-slate-50">
                                        <input type="checkbox" name="time_slots[]" value="{{ $slot->id }}" class="rounded text-red-600 focus:ring-red-500">
                                        <span class="text-xs font-semibold">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Alasan (Cth: Service AC)</label>
                                <input type="text" name="agenda" required class="mt-1 w-full rounded-xl border-slate-300 px-4 py-2.5">
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" @click="isModalOpen = false" class="rounded-xl px-4 py-2.5 border border-slate-300 bg-white font-semibold">Batal</button>
                            <button type="submit" class="rounded-xl bg-red-600 px-6 py-2.5 text-white font-bold hover:bg-red-700">Blokir Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
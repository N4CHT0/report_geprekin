@extends('Room.layouts.app')

@section('title', 'Ubah Jadwal Reservasi')

@section('content')
<div x-data="{ selectedRoom: '{{ old('room_id', $reservation->room_id) }}' }" class="max-w-6xl mx-auto">
    
    <div class="mb-6 flex justify-between items-end border-b border-slate-200 pb-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Ubah Jadwal (Reschedule)</h2>
            <p class="text-sm text-amber-600 font-medium">Perubahan jadwal akan mereset status menjadi 'Menunggu Persetujuan'.</p>
        </div>
        <a href="{{ route('reservations.history') }}" class="text-sm font-bold text-slate-500 hover:text-slate-700">← Kembali</a>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
            <ul class="list-disc list-inside text-xs text-red-700 font-medium ml-1">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 flex flex-col sm:flex-row items-center gap-4">
        <div class="shrink-0"><label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Cek Jadwal Tanggal:</label></div>
        <div class="w-full sm:w-64">
            <input type="date" value="{{ $selectedDate }}" onchange="window.location.href='?date='+this.value" class="w-full bg-slate-50 border border-slate-300 text-slate-900 px-4 py-2.5 rounded-xl text-sm outline-none cursor-pointer">
        </div>
    </div>

    <form action="{{ route('reservations.process_reschedule', $reservation->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="reservation_date" value="{{ $selectedDate }}">
        
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Divisi <span class="text-red-500">*</span></label>
                <select name="division" required class="w-full bg-slate-50 border border-slate-300 px-4 py-3 rounded-xl text-sm">
                    @foreach($divisions as $div)
                        <option value="{{ $div->name }}" {{ (old('division') ?? $reservation->division) == $div->name ? 'selected' : '' }}>{{ $div->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Agenda <span class="text-red-500">*</span></label>
                <input type="text" name="agenda" required value="{{ old('agenda', $reservation->agenda) }}" class="w-full bg-slate-50 border border-slate-300 px-4 py-3 rounded-xl text-sm">
            </div>
        </div>

        <div class="space-y-4 mb-8">
            <h3 class="text-lg font-bold text-slate-800">Ubah Ruangan & Jam:</h3>
            @foreach($rooms as $room)
            <div class="bg-white rounded-2xl border-2 transition-all cursor-pointer overflow-hidden shadow-sm"
                 :class="selectedRoom == '{{ $room->id }}' ? 'border-[#2A435D] shadow-md ring-1 ring-[#2A435D]/50' : 'border-slate-200'"
                 @click="selectedRoom = '{{ $room->id }}'">
                 
                <div class="p-4 border-b border-slate-100 flex items-center gap-3">
                    <input type="radio" name="room_id" value="{{ $room->id }}" x-model="selectedRoom" class="hidden">
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors" :class="selectedRoom == '{{ $room->id }}' ? 'border-[#2A435D]' : 'border-slate-300'">
                        <div class="w-2.5 h-2.5 rounded-full bg-[#2A435D] transition-transform" :class="selectedRoom == '{{ $room->id }}' ? 'scale-100' : 'scale-0'"></div>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900">{{ $room->name }}</h4>
                        <p class="text-[11px] text-slate-500 font-medium">Kapasitas: {{ $room->capacity }} Orang</p>
                    </div>
                </div>

                <div class="p-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                    @foreach($timeSlots as $slot)
                        @php 
                            $statusBooked = $bookedMatrix[$room->id][$slot->id] ?? null; 
                            // Centang otomatis jika ini adalah jam asli milik reservasi ini di tanggal yang sama
                            $isChecked = ($selectedDate == $reservation->reservation_date && $room->id == $reservation->room_id && in_array($slot->id, $currentSlots));
                        @endphp

                        @if($statusBooked === 'Approved')
                            <div class="text-center px-2 py-2 rounded-xl border border-red-200 bg-red-50 text-red-400 opacity-60 flex flex-col items-center justify-center">
                                <span class="text-xs font-bold line-through">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</span>
                            </div>
                        @elseif($statusBooked === 'Pending')
                            <div class="text-center px-2 py-2 rounded-xl border border-amber-200 bg-amber-50 text-amber-500 opacity-80 flex flex-col items-center justify-center">
                                <span class="text-xs font-bold">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</span>
                            </div>
                        @else
                            <label class="relative cursor-pointer h-full">
                                <input type="checkbox" name="slots_{{ $room->id }}[]" value="{{ $slot->id }}" class="peer sr-only" x-bind:disabled="selectedRoom != '{{ $room->id }}'" {{ $isChecked ? 'checked' : '' }}>
                                <div class="h-full flex flex-col items-center justify-center px-2 py-2 rounded-xl border-2 border-slate-200 bg-white text-slate-600 transition-all peer-checked:border-[#2A435D] peer-checked:bg-[#2A435D] peer-checked:text-white"
                                     :class="selectedRoom != '{{ $room->id }}' ? 'opacity-50 grayscale' : ''">
                                    <span class="text-xs font-bold">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</span>
                                </div>
                            </label>
                        @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <button type="submit" class="w-full bg-[#2A435D] text-white font-bold text-sm px-4 py-3.5 rounded-xl shadow-lg hover:bg-[#1F3246]">
            Simpan Perubahan Jadwal
        </button>
    </form>
</div>
@endsection
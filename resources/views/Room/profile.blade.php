@extends('Room.layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="relative mb-6">
        <div class="h-32 bg-gradient-to-r from-[#2A435D] to-[#3D5B7A] rounded-3xl shadow-sm"></div>
        <div class="absolute -bottom-12 left-8 flex items-end gap-5">
            <div class="w-32 h-32 rounded-3xl bg-white border-4 border-white shadow-xl flex items-center justify-center text-4xl font-black text-[#2A435D] overflow-hidden">
                @if(auth()->user()->photo)
                    <img src="{{ asset('storage/'.auth()->user()->photo) }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="pb-2">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ auth()->user()->name }}</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 uppercase">
                    {{ auth()->user()->role }}
                </span>
            </div>
        </div>
    </div>

    <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Informasi Personal
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Email Address</label>
                        <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->email }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Employee ID</label>
                        <p class="text-sm font-semibold text-slate-700">#{{ str_pad(auth()->id(), 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Division</label>
                        <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->division ?? 'Belum Diatur' }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Joined Date</label>
                        <p class="text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::parse(auth()->user()->created_at)->translatedFormat('d M Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Keamanan Akun
                </h3>
                <form action="#" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <input type="password" placeholder="Password Baru" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 transition">
                        <input type="password" placeholder="Konfirmasi Password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-blue-500 transition">
                    </div>
                    <button type="submit" class="bg-[#2A435D] text-white px-6 py-2.5 rounded-xl text-sm font-bold hover:bg-[#1F3246] transition">Update Password</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-[#F59E0B] rounded-3xl p-6 text-white shadow-lg shadow-amber-200">
                <div class="text-xs font-bold uppercase opacity-80 mb-1">Total Reservasi</div>
                <div class="text-4xl font-black mb-3">12</div>
                <p class="text-[11px] leading-relaxed opacity-90">Terima kasih telah aktif menggunakan sistem HOSpace untuk mendukung produktivitas kerja.</p>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h4 class="text-sm font-bold text-slate-900 mb-3 tracking-tight">Riwayat Aktivitas</h4>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></div>
                        <p class="text-[11px] text-slate-600">Terakhir login pada <strong>22 Mei 2026, 10:30</strong></p>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></div>
                        <p class="text-[11px] text-slate-600">Berhasil melakukan reservasi Ruang Meeting Utama.</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
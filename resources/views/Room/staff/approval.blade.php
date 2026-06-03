@extends('Room.layouts.app')

@section('title', 'Persetujuan Reservasi')

@section('content')
<div x-data="{
        isRejectModalOpen: false,
        rejectActionUrl: '',
        roomName: '',
        userName: '',
        
        // Fungsi untuk membuka modal penolakan
        openRejectModal(id, room, user) {
            this.rejectActionUrl = `/hospace/admin/approvals/${id}/reject`; // Sesuaikan dengan format route-mu
            this.roomName = room;
            this.userName = user;
            this.isRejectModalOpen = true;
        }
    }">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Daftar Pengajuan Ruangan</h2>
        <p class="text-sm text-slate-500">Tinjau dan tentukan persetujuan peminjaman fasilitas kantor secara fleksibel.</p>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg max-w-4xl">
        <ul class="list-disc list-inside text-xs text-red-700 font-medium">
            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Detail Waktu</th>
                        <th class="px-6 py-4 font-semibold">Peminjam</th>
                        <th class="px-6 py-4 font-semibold">Ruangan</th>
                        <th class="px-6 py-4 font-semibold">Agenda Keperluan</th>
                        <th class="px-6 py-4 font-semibold text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($approvals as $approval)
                    <tr class="hover:bg-slate-50 transition-all">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900">
                                {{ \Carbon\Carbon::parse($approval->reservation_date)->format('d M Y') }}
                            </div>
                            
                            <div class="text-xs font-semibold text-blue-600 mt-0.5 mb-1 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $approval->time_range ?? '-' }}
                            </div>
                            
                            <div class="text-[10px] text-slate-400">
                                Diajukan: {{ \Carbon\Carbon::parse($approval->created_at)->diffForHumans() }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $approval->user_name }}</div>
                            <div class="text-xs text-blue-600 font-medium">Divisi: {{ $approval->division }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $approval->room_name }}</td>
                        <td class="px-6 py-4 text-slate-600 italic">"{{ $approval->agenda }}"</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <form action="{{ route('admin.approvals.approve', $approval->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="rounded-xl bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 border border-emerald-200/60 hover:bg-emerald-100 transition-all">
                                        ✓ Setujui
                                    </button>
                                </form>

                                <button type="button" @click="openRejectModal('{{ $approval->id }}', '{{ $approval->room_name }}', '{{ $approval->user_name }}')" class="rounded-xl bg-red-50 px-4 py-2 text-xs font-bold text-red-700 border border-red-200/60 hover:bg-red-100 transition-all">
                                    ✕ Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                            <div class="text-2xl mb-1">🎉</div>
                            <div class="font-medium text-sm text-slate-500">Semua pengajuan bersih! Tidak ada antrean persetujuan.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="isRejectModalOpen" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="isRejectModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="isRejectModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.away="isRejectModalOpen = false"
                    class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8">

                    <form x-bind:action="rejectActionUrl" method="POST">
                        @csrf

                        <div class="bg-white px-6 pb-4 pt-6 sm:p-8 sm:pb-6">
                            <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900" id="modal-title">Tolak Persetujuan</h3>
                                    <p class="text-[11px] text-slate-500">Tindakan ini tidak dapat dibatalkan.</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                    <div class="text-xs text-slate-500 mb-1">Menolak pengajuan dari:</div>
                                    <div class="font-bold text-slate-800 text-sm"><span x-text="userName"></span> • <span class="text-[#2A435D]" x-text="roomName"></span></div>
                                </div>

                                <div>
                                    <label for="rejection_reason" class="block text-sm font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                                    <textarea name="rejection_reason" id="rejection_reason" rows="3" required placeholder="Tuliskan alasan mengapa ruangan ini tidak bisa dipinjam (wajib)..." class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:border-red-500 focus:ring-red-500 sm:text-sm outline-none resize-none"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8 border-t border-slate-100 gap-2">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-red-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-red-700 sm:w-auto">
                                Kirim Penolakan
                            </button>
                            <button type="button" @click="isRejectModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
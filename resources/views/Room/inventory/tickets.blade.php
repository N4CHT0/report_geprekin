@extends('Room.layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tiket Maintenance Aset IT</h1>
            <p class="text-sm text-slate-500">Kelola pelaporan kerusakan, keluhan, dan progres servis laptop.</p>
        </div>
        <button onclick="document.getElementById('modalAddTicket').classList.remove('hidden')" class="bg-[#2A435D] text-white px-5 py-2.5 rounded-lg font-bold hover:bg-[#3D5B7A] transition-all flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Buat Tiket Baru
        </button>
    </div>

    <!-- Ticket List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex gap-4">
            <!-- Filter / Search (Optional future enhancement) -->
            <h2 class="font-bold text-slate-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Daftar Antrean Servis
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">ID Tiket</th>
                        <th class="px-6 py-4">Aset Terkait</th>
                        <th class="px-6 py-4 w-1/3">Kendala / Laporan</th>
                        <th class="px-6 py-4">Status Tiket</th>
                        <th class="px-6 py-4">Tgl Dibuat</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-mono font-bold text-slate-800">#TCK-{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-[#2A435D]">{{ $ticket->asset_code }}</div>
                            <div class="text-[11px] text-slate-500">{{ $ticket->brand_model }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-slate-700 line-clamp-2" title="{{ $ticket->issue_description }}">{{ $ticket->issue_description }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusBadge = match($ticket->status) {
                                    'Open' => 'bg-red-100 text-red-700 border-red-200',
                                    'In Progress' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Resolved' => 'bg-green-100 text-green-700 border-green-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="px-3 py-1 text-xs font-bold rounded-full border {{ $statusBadge }}">
                                {{ $ticket->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500 font-medium">
                            {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($ticket->status !== 'Resolved')
                                <!-- Tombol Update Status (Buka Modal) -->
                                <button onclick="openUpdateModal({{ $ticket->id }}, '{{ $ticket->status }}')" class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-2 rounded transition-colors" title="Update Status">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                @endif

                                <form action="{{ route('inventory.tickets.destroy', $ticket->id) }}" method="POST" onsubmit="return confirm('Hapus permanen tiket ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded transition-colors" title="Hapus Tiket">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <p class="font-medium">Hore! Tidak ada antrean tiket maintenance saat ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $tickets->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah Tiket -->
<div id="modalAddTicket" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('modalAddTicket').classList.add('hidden')"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-lg">Buat Tiket Laporan Keluhan</h3>
            <button onclick="document.getElementById('modalAddTicket').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('inventory.tickets.store') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Aset Laptop Bermasalah</label>
                    <select name="laptop_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none">
                        <option value="">-- Pilih Laptop --</option>
                        @foreach($laptops as $laptop)
                            <option value="{{ $laptop->id }}">{{ $laptop->asset_code }} - {{ $laptop->brand_model }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Kerusakan / Kendala</label>
                    <textarea name="issue_description" rows="4" required placeholder="Contoh: Baterai cepat drop, tidak bisa charging, tuts keyboard error..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none"></textarea>
                </div>
                <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <input type="checkbox" id="set_maintenance" name="set_maintenance" value="1" class="mt-1 w-4 h-4 text-amber-600 rounded border-amber-300 focus:ring-amber-500">
                    <label for="set_maintenance" class="text-xs text-amber-800 font-medium">
                        Otomatis ubah status aset ini menjadi <strong>"Maintenance"</strong> di database master inventaris. (Disarankan jika laptop akan masuk ke tempat servis).
                    </label>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAddTicket').classList.add('hidden')" class="px-5 py-2.5 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2.5 bg-[#2A435D] text-white font-medium rounded-lg shadow-md hover:bg-[#3D5B7A] transition-all">Submit Tiket</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Update Tiket -->
<div id="modalUpdateTicket" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('modalUpdateTicket').classList.add('hidden')"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-lg">Update Progres Tiket</h3>
            <button onclick="document.getElementById('modalUpdateTicket').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="formUpdateTicket" method="POST" class="p-6">
            @csrf
            <!-- Di Laravel 10+, ubah POST jadi POST (karena di route pake Route::post) atau inject custom method via js -->
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Status Saat Ini</label>
                    <select name="status" id="ticketStatusSelect" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none">
                        <option value="Open">Open (Baru)</option>
                        <option value="In Progress">In Progress (Sedang Diperbaiki)</option>
                        <option value="Resolved">Resolved (Selesai)</option>
                    </select>
                </div>
                
                <!-- Muncul jika status yang dipilih adalah Resolved -->
                <div id="resolveOptions" class="hidden flex items-start gap-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <input type="checkbox" id="return_available" name="return_available" value="1" checked class="mt-1 w-4 h-4 text-green-600 rounded border-green-300 focus:ring-green-500">
                    <label for="return_available" class="text-xs text-green-800 font-medium">
                        Otomatis ubah status aset kembali menjadi <strong>"Available"</strong> di gudang IT.
                    </label>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="submit" class="w-full py-2.5 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 transition-all">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openUpdateModal(ticketId, currentStatus) {
        const form = document.getElementById('formUpdateTicket');
        const modal = document.getElementById('modalUpdateTicket');
        const selectStatus = document.getElementById('ticketStatusSelect');
        const resolveOpts = document.getElementById('resolveOptions');
        
        // Set dynamic action URL
        form.action = `/hospace/inventory/tickets/${ticketId}/update`;
        selectStatus.value = currentStatus;
        
        // Handle select change event
        selectStatus.onchange = function() {
            if(this.value === 'Resolved') {
                resolveOpts.classList.remove('hidden');
            } else {
                resolveOpts.classList.add('hidden');
            }
        };
        
        // Trigger onchange manually once
        selectStatus.onchange();

        // Show modal
        modal.classList.remove('hidden');
    }
</script>
@endsection

@extends('Room.layouts.app')

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Approval Pemusnahan Aset (Disposal)</h1>
            <p class="text-sm text-slate-500">Persetujuan untuk memusnahkan perangkat IT yang sudah tidak layak pakai.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-200 flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 bg-red-50 text-red-700 p-4 rounded-xl border border-red-200 flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        <span class="font-medium">{{ collect($errors->all())->first() }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600">
                        <th class="p-4 font-semibold text-sm">Aset</th>
                        <th class="p-4 font-semibold text-sm">Diajukan Oleh</th>
                        <th class="p-4 font-semibold text-sm">Alasan Disposal</th>
                        <th class="p-4 font-semibold text-sm">Status</th>
                        <th class="p-4 font-semibold text-sm text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($disposals as $disp)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $disp->asset_code }}</div>
                            <div class="text-xs text-slate-500">{{ $disp->brand_model }}</div>
                        </td>
                        <td class="p-4">
                            <div class="text-sm font-medium text-slate-700">{{ $disp->requester_name }}</div>
                            <div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($disp->created_at)->format('d M Y') }}</div>
                        </td>
                        <td class="p-4">
                            <p class="text-sm text-slate-600 line-clamp-2 max-w-xs" title="{{ $disp->reason }}">
                                {{ $disp->reason }}
                            </p>
                        </td>
                        <td class="p-4">
                            @if($disp->status === 'Pending')
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-md">Menunggu</span>
                            @elseif($disp->status === 'Approved')
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-md">Disetujui</span>
                            @else
                                <span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-md">Ditolak</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            @if($disp->status === 'Pending')
                                <button onclick="openProcessModal({{ $disp->id }}, '{{ $disp->asset_code }}')" class="px-3 py-1.5 bg-[#2A435D] text-white text-xs font-bold rounded hover:bg-[#3D5B7A] transition-colors">
                                    Proses
                                </button>
                            @else
                                <span class="text-xs text-slate-400 italic">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">Belum ada pengajuan disposal aset.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Proses -->
<div id="modalProcess" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-lg">Proses Pengajuan Disposal</h3>
            <button onclick="document.getElementById('modalProcess').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="processForm" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <p class="text-sm text-slate-600 mb-2">Anda akan memproses aset: <strong id="processAssetCode" class="text-slate-800"></strong></p>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tindakan</label>
                <select name="action" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all mb-4">
                    <option value="Approve">Setujui (Disposed)</option>
                    <option value="Reject">Tolak</option>
                </select>

                <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                <textarea name="notes" rows="3" placeholder="Alasan persetujuan / penolakan..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all"></textarea>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalProcess').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2 bg-[#2A435D] text-white font-medium rounded-lg shadow-md hover:bg-[#3D5B7A] transition-all">Simpan Keputusan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openProcessModal(id, code) {
        document.getElementById('processForm').action = `/hospace/inventory/disposals/${id}/process`;
        document.getElementById('processAssetCode').innerText = code;
        document.getElementById('modalProcess').classList.remove('hidden');
    }
</script>
@endsection

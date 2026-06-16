@extends('Room.layouts.app')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Master Inventaris IT</h1>
            <p class="text-sm text-slate-500">Kelola data laptop dan aset IT lainnya.</p>
        </div>
        <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-[#2A435D] text-white px-4 py-2 rounded-lg font-medium hover:bg-[#3D5B7A] transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Laptop
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 text-green-700 p-4 rounded-lg border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <form action="{{ route('inventory.master') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aset/serial/merk..." class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-[#2A435D] focus:border-[#2A435D] outline-none text-sm w-64">
                <button type="submit" class="bg-slate-200 px-4 py-2 rounded-lg text-slate-700 hover:bg-slate-300 transition text-sm font-medium">Cari</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-4">Kode Aset</th>
                        <th class="px-6 py-4">Merk & Serial</th>
                        <th class="px-6 py-4">Spesifikasi</th>
                        <th class="px-6 py-4">Status & Lokasi</th>
                        <th class="px-6 py-4">User Aktif</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($laptops as $laptop)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-[#2A435D]">{{ $laptop->asset_code }}</div>
                            <div class="mt-1">
                                <!-- Barcode SVG rendered by JsBarcode -->
                                <svg class="barcode-svg h-10 w-auto" data-barcode="{{ $laptop->barcode }}"></svg>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $laptop->brand_model }}</div>
                            <div class="text-xs text-slate-500">SN: {{ $laptop->serial_number }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs space-y-1">
                                <div><span class="font-medium text-slate-700">CPU:</span> {{ $laptop->cpu ?? '-' }}</div>
                                <div><span class="font-medium text-slate-700">RAM:</span> {{ $laptop->ram ?? '-' }} | <span class="font-medium text-slate-700">SSD:</span> {{ $laptop->ssd ?? '-' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'Available' => 'bg-green-100 text-green-700',
                                    'In Use' => 'bg-blue-100 text-blue-700',
                                    'Maintenance' => 'bg-amber-100 text-amber-700',
                                    'Damaged' => 'bg-red-100 text-red-700',
                                    'Missing' => 'bg-red-100 text-red-700',
                                    'Disposed' => 'bg-slate-100 text-slate-700',
                                ];
                                $color = $statusColors[$laptop->status] ?? 'bg-slate-100 text-slate-700';
                            @endphp
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full {{ $color }}">
                                {{ $laptop->status }}
                            </span>
                            <div class="text-[11px] text-slate-500 mt-2 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ $laptop->location }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($laptop->status === 'In Use' && $laptop->assigned_user_id)
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-[#3D5B7A] text-white flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(substr($laptop->assigned_user_name, 0, 1)) }}
                                    </div>
                                    <span class="text-sm font-medium">{{ $laptop->assigned_user_name }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Tidak ada user</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($laptop->status === 'Available')
                                    <a href="{{ route('inventory.assign', $laptop->id) }}" class="text-blue-600 hover:text-blue-800 bg-blue-50 p-1.5 rounded" title="Serahkan ke User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    </a>
                                @elseif($laptop->status === 'In Use')
                                    <a href="{{ route('inventory.print', $laptop->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 bg-indigo-50 p-1.5 rounded" title="Cetak Berita Acara">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>
                                    <form action="{{ route('inventory.return', $laptop->id) }}" method="POST" onsubmit="return confirm('Kembalikan laptop ini ke Gudang IT?');">
                                        @csrf
                                        <button class="text-amber-600 hover:text-amber-800 bg-amber-50 p-1.5 rounded" title="Kembalikan ke Gudang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                        </button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('inventory.history', $laptop->id) }}" class="text-slate-500 hover:text-slate-800 bg-slate-100 p-1.5 rounded ml-1" title="Lihat Riwayat & Log">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </a>

                                @if($laptop->status !== 'In Use' && $laptop->status !== 'Disposed')
                                    <button onclick="openDisposalModal({{ $laptop->id }}, '{{ $laptop->asset_code }}')" class="text-red-500 hover:text-red-800 bg-red-50 p-1.5 rounded ml-1" title="Ajukan Pemusnahan (Disposal)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                @endif

                                <form action="{{ route('inventory.destroy', $laptop->id) }}" method="POST" onsubmit="return confirm('Hapus permanen data laptop ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                            Tidak ada data laptop ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $laptops->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div id="modalAdd" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('modalAdd').classList.add('hidden')"></div>
    <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-lg bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-lg">Tambah Laptop Baru</h3>
            <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="{{ route('inventory.store') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Merek & Model</label>
                    <input type="text" name="brand_model" required placeholder="Contoh: Lenovo Thinkpad T490" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Serial Number</label>
                    <input type="text" name="serial_number" required placeholder="SN Laptop" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">CPU / Prosesor</label>
                        <input type="text" name="cpu" placeholder="Intel Core i5 Gen 10" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">RAM</label>
                        <input type="text" name="ram" placeholder="16GB DDR4" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Penyimpanan (SSD/HDD)</label>
                        <input type="text" name="ssd" placeholder="512GB NVMe" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Batas Garansi</label>
                        <input type="date" name="warranty_expired_at" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all">
                    </div>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2 bg-[#2A435D] text-white font-medium rounded-lg shadow-md shadow-[#2A435D]/20 hover:bg-[#3D5B7A] transition-all">Simpan Aset</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ajukan Disposal -->
<div id="modalDisposal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-lg">Ajukan Disposal Aset</h3>
            <button type="button" onclick="document.getElementById('modalDisposal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="disposalForm" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <p class="text-sm text-slate-600 mb-4">Anda akan mengajukan pemusnahan untuk aset: <strong id="disposalAssetCode"></strong></p>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Alasan Pemusnahan</label>
                <textarea name="reason" required rows="3" placeholder="Contoh: Mati total, motherboard terbakar, biaya servis lebih mahal dari beli baru..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none transition-all"></textarea>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalDisposal').classList.add('hidden')" class="px-4 py-2 text-slate-600 font-medium hover:bg-slate-100 rounded-lg transition-colors">Batal</button>
                <button type="submit" class="px-6 py-2 bg-red-600 text-white font-medium rounded-lg shadow-md hover:bg-red-700 transition-all">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>

<!-- Tambahkan script JsBarcode -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const barcodes = document.querySelectorAll('.barcode-svg');
        barcodes.forEach(function(svg) {
            const code = svg.getAttribute('data-barcode');
            JsBarcode(svg, code, {
                format: "CODE128",
                width: 1.5,
                height: 30,
                displayValue: false,
                background: "transparent",
                lineColor: "#2A435D"
            });
        });
    });

    function openDisposalModal(id, code) {
        document.getElementById('disposalForm').action = `/hospace/inventory/disposals/${id}/request`;
        document.getElementById('disposalAssetCode').innerText = code;
        document.getElementById('modalDisposal').classList.remove('hidden');
    }
</script>
@endsection

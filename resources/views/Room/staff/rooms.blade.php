@extends('Room.layouts.app')

@section('title', 'Master Ruangan')

@section('content')
<div x-data="{
        isModalOpen: false,
        modalMode: 'add',
        modalTitle: 'Tambah Ruangan',
        formAction: '{{ route('rooms.store') ?? url('rooms') }}',
        
        formData: {
            id: '',
            name: '',
            capacity: '',
            is_active: 1
        },

        openAddModal() {
            this.modalMode = 'add';
            this.modalTitle = 'Tambah Ruangan';
            this.formAction = '{{ route('rooms.store') ?? url('rooms') }}'; 
            this.formData = { id: '', name: '', capacity: '', is_active: 1 };
            this.isModalOpen = true;
        },

        openEditModal(room) {
        
            this.modalMode = 'edit';
            this.modalTitle = 'Edit Ruangan';
            let baseUrl = '{{ route('rooms.update', ':id') }}';
            // Menggunakan fungsi url() Laravel agar path-nya dinamis dan tidak nyasar
            this.formAction = baseUrl.replace(':id', room.id);
            this.formData = { ...room };
            this.isModalOpen = true;
        }
    }">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Ruangan</h2>
            <p class="text-sm text-slate-500">Kelola data ruangan yang tersedia untuk dipinjam.</p>
        </div>
        
        <button type="button" @click="openAddModal()" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700">
            + Tambah Ruangan
        </button>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg">
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
                        <th class="px-6 py-4 font-semibold">Nama Ruangan</th>
                        <th class="px-6 py-4 font-semibold">Kapasitas</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rooms ?? [] as $room)
                    <tr class="transition-colors hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $room->name }}</td>
                        <td class="px-6 py-4">{{ $room->capacity }} Orang</td>
                        <td class="px-6 py-4">
                            @if($room->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Aktif</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="openEditModal({{ json_encode($room) }})" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                                    Edit
                                </button>
                                
                                <form action="{{ url('rooms', $room->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada data ruangan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="isModalOpen" x-cloak class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div x-show="isModalOpen" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     @click.away="isModalOpen = false"
                     class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8">
                    
                    <form x-bind:action="formAction" method="POST">
                        @csrf
                        
                        <input type="hidden" name="_method" value="PUT" x-bind:disabled="modalMode === 'add'">

                        <div class="bg-white px-6 pb-4 pt-6 sm:p-8 sm:pb-6">
                            <h3 class="mb-5 text-xl font-bold text-slate-900" id="modal-title" x-text="modalTitle"></h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Ruangan <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="name" required x-model="formData.name" placeholder="Contoh: Ruang Meeting Direksi" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="capacity" class="block text-sm font-medium text-slate-700">Kapasitas (Orang) <span class="text-red-500">*</span></label>
                                    <input type="number" name="capacity" id="capacity" required min="1" x-model="formData.capacity" placeholder="Contoh: 12" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                </div>

                                <div>
                                    <label for="is_active" class="mb-2 block text-sm font-medium text-slate-700">Status Ruangan</label>
                                    <select name="is_active" id="is_active" x-model="formData.is_active" class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                        <option value="1">Aktif (Bisa Dipinjam)</option>
                                        <option value="0">Non-Aktif (Maintenance)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:px-8">
                            <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto">
                                Simpan Data
                            </button>
                            <button type="button" @click="isModalOpen = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">
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
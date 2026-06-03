@extends('Room.layouts.app')

@section('title', 'Master Divisi')

@section('content')
<div x-data="{
        isModalOpen: false,
        modalMode: 'add', // 'add' atau 'edit'
        modalTitle: 'Tambah Divisi',
        formAction: '{{ route('admin.divisions.store') ?? '#' }}',
        
        // State untuk menampung isian form
        formData: {
            id: '',
            name: ''
        },

        // Fungsi saat tombol Tambah diklik
        openAddModal() {
            this.modalMode = 'add';
            this.modalTitle = 'Tambah Divisi';
            this.formAction = '{{ route('admin.divisions.store') }}'; 
            this.formData = { id: '', name: '' };
            this.isModalOpen = true;
        },

        // Fungsi saat tombol Edit diklik
        openEditModal(div) {
            this.modalMode = 'edit';
            this.modalTitle = 'Edit Divisi';
            this.formAction = `/hospace/admin/divisions/${div.id}`; 
            this.formData = { ...div };
            this.isModalOpen = true;
        }
    }">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Divisi</h2>
            <p class="text-sm text-slate-500">Kelola data divisi yang tersedia untuk pilihan dropdown reservasi.</p>
        </div>
        
        <button type="button" @click="openAddModal()" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700">
            + Tambah Divisi
        </button>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-lg max-w-3xl">
            <ul class="list-disc list-inside text-xs text-red-700 font-medium">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm max-w-3xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Nama Divisi</th>
                        <th class="px-6 py-4 text-right font-semibold w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($divisions ?? [] as $div)
                    <tr class="transition-colors hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $div->name }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" @click="openEditModal({{ json_encode($div) }})" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100 transition">
                                    Edit
                                </button>
                                
                                <form action="/hospace/admin/divisions/{{ $div->id }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus divisi ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-8 text-center text-slate-500">Belum ada data divisi. Silakan tambah baru.</td>
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
                     class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8">
                    
                    <form x-bind:action="formAction" method="POST">
                        @csrf
                        <template x-if="modalMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="bg-white px-6 pb-4 pt-6 sm:p-8 sm:pb-6">
                            <h3 class="mb-5 text-xl font-bold text-slate-900" id="modal-title" x-text="modalTitle"></h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Divisi <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" id="name" required x-model="formData.name" placeholder="Contoh: IT, HRD, Finance" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-slate-900 focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none">
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
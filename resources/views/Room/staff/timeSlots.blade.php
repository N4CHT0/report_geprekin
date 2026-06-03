@extends('Room.layouts.app')

@section('title', 'Master Sesi Waktu')

@section('content')
<div x-data="{
        isModalOpen: false,
        modalMode: 'add',
        modalTitle: 'Tambah Sesi Waktu',
        formAction: '/time-slots',
        formData: { id: '', label: '', start_time: '', end_time: '' },

        openAddModal() {
            this.modalMode = 'add';
            this.modalTitle = 'Tambah Sesi Waktu';
            this.formAction = '/time-slots';
            this.formData = { id: '', label: '', start_time: '', end_time: '' };
            this.isModalOpen = true;
        },
        openEditModal(slot) {
            this.modalMode = 'edit';
            this.modalTitle = 'Edit Sesi Waktu';
            this.formAction = `/time-slots/${slot.id}`;
            this.formData = { ...slot };
            this.isModalOpen = true;
        }
    }">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Sesi Waktu</h2>
            <p class="text-sm text-slate-500">Atur blok jam operasional untuk peminjaman ruangan.</p>
        </div>
        <button type="button" @click="openAddModal()" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            + Tambah Waktu
        </button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-700">
                <tr>
                    <th class="px-6 py-4 font-semibold">Label Sesi</th>
                    <th class="px-6 py-4 font-semibold">Jam Mulai</th>
                    <th class="px-6 py-4 font-semibold">Jam Selesai</th>
                    <th class="px-6 py-4 text-right font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($timeSlots ?? [] as $slot)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-medium text-slate-900">{{ $slot->label }}</td>
                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}</td>
                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <button type="button" @click="openEditModal({{ json_encode($slot) }})" class="rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">Edit</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada master waktu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div x-show="isModalOpen" x-cloak class="relative z-50">
        <div x-show="isModalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div @click.away="isModalOpen = false" class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
                    <form x-bind:action="formAction" method="POST">
                        @csrf
                        <template x-if="modalMode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                        <div class="p-6">
                            <h3 class="mb-5 text-xl font-bold" x-text="modalTitle"></h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Label (Opsional)</label>
                                    <input type="text" name="label" x-model="formData.label" placeholder="Contoh: Sesi Pagi 1" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2 text-slate-900">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Jam Mulai <span class="text-red-500">*</span></label>
                                        <input type="time" name="start_time" required x-model="formData.start_time" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Jam Selesai <span class="text-red-500">*</span></label>
                                        <input type="time" name="end_time" required x-model="formData.end_time" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-2">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3">
                            <button type="button" @click="isModalOpen = false" class="rounded-xl px-4 py-2 border border-slate-300 bg-white">Batal</button>
                            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-white font-bold">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
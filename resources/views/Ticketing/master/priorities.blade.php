@extends('Ticketing.layouts.app')

@section('title','Master Prioritas')

@section('content')
<div class="space-y-5">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-5 border-b border-slate-100 p-6 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="mb-3 flex items-center gap-2 text-sm">
                    <a href="{{ route('ticketing.dashboard') }}" class="font-medium text-slate-500 hover:text-blue-600">
                        Dashboard
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="font-semibold text-slate-900">Master Prioritas</span>
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Master Prioritas
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola prioritas ticket dan SLA jam.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET">
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari prioritas..."
                        class="w-full sm:w-80 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </form>

                <button
                    onclick="document.getElementById('modalCreate').classList.remove('hidden')"
                    class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700">
                    + Tambah
                </button>
            </div>
        </div>

        <div class="grid grid-cols-2 divide-x divide-slate-100 lg:grid-cols-4">
            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Prioritas</div>
                <div class="mt-2 text-3xl font-black text-slate-900">{{ $rows->count() }}</div>
            </div>

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Active</div>
                <div class="mt-2 text-3xl font-black text-emerald-600">
                    {{ collect($rows)->where('is_active', 1)->count() }}
                </div>
            </div>

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">System</div>
                <div class="mt-3 inline-flex rounded-2xl bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    PRIORITY MASTER
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-700">
                    Data Prioritas
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    List prioritas dan SLA ticketing.
                </p>
            </div>

            <div class="text-sm text-slate-500">
                {{ $rows->count() }} Data
            </div>
        </div>

        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-white">
                    <tr class="border-b border-slate-200">
                        <th class="px-6 py-4 text-left text-xs font-black uppercase text-slate-500">No</th>
                        <th class="px-6 py-4 text-left text-xs font-black uppercase text-slate-500">Prioritas</th>
                        <th class="px-6 py-4 text-left text-xs font-black uppercase text-slate-500">SLA Jam</th>
                        <th class="px-6 py-4 text-left text-xs font-black uppercase text-slate-500">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-5 text-slate-400">{{ $loop->iteration }}</td>

                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-900">{{ $row->priority }}</div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="text-slate-600">{{ $row->sla_hours }} jam</div>
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex gap-2">
                                    <button
                                        data-id="{{ $row->id }}"
                                        data-priority="{{ $row->priority }}"
                                        data-sla="{{ $row->sla_hours }}"
                                        onclick="openEditModal(this)"
                                        class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 hover:bg-amber-100">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('ticketing.master.priorities.delete', $row->id) }}"
                                          onsubmit="return confirm('Hapus prioritas ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center text-slate-500">
                                Belum ada data prioritas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modalCreate" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-20 max-w-lg rounded-3xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-900">Tambah Prioritas</h3>
        </div>

        <form method="POST" action="{{ route('ticketing.master.priorities.store') }}">
            @csrf

            <div class="space-y-5 p-6">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Prioritas</label>
                    <input type="text" name="priority" placeholder="Contoh: High"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                           required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">SLA Jam</label>
                    <input type="number" name="sla_hours" placeholder="Contoh: 24"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                           required>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-5">
                <button type="button"
                        onclick="document.getElementById('modalCreate').classList.add('hidden')"
                        class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold">
                    Batal
                </button>

                <button class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modalEdit" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-20 max-w-lg rounded-3xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-900">Edit Prioritas</h3>
        </div>

        <form id="editPriorityForm" method="POST" action="#">
            @csrf
            @method('PUT')

            <div class="space-y-5 p-6">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Prioritas</label>
                    <input id="editPriorityInput" type="text" name="priority"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                           required>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">SLA Jam</label>
                    <input id="editSlaInput" type="number" name="sla_hours"
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"
                           required>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-5">
                <button type="button"
                        onclick="document.getElementById('modalEdit').classList.add('hidden')"
                        class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold">
                    Batal
                </button>

                <button class="rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(el) {
    document.getElementById('modalEdit').classList.remove('hidden');

    document.getElementById('editPriorityForm').action =
        '{{ route('ticketing.master.priorities.update', ['id' => '__ID__']) }}'
            .replace('__ID__', el.dataset.id);

    document.getElementById('editPriorityInput').value = el.dataset.priority || '';
    document.getElementById('editSlaInput').value = el.dataset.sla || '';
}
</script>
@endsection
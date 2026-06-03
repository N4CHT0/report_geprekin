@extends('Ticketing.layouts.app')

@section('title','Master Divisi')

@section('content')

<div
    x-data="{ q:'{{ request('q') }}' }"
    class="space-y-5"
>

    {{-- TOPBAR --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-5 border-b border-slate-100 p-6 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="mb-3 flex items-center gap-2 text-sm">
                    <a href="{{ route('ticketing.dashboard') }}"
                       class="font-medium text-slate-500 transition hover:text-blue-600">
                        Dashboard
                    </a>

                    <span class="text-slate-300">/</span>

                    <span class="font-semibold text-slate-900">
                        Master Divisi
                    </span>
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Master Divisi
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola divisi operasional untuk proses ticketing.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            🔍
                        </div>

                        <input
                            x-model="q"
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari divisi..."
                            class="w-full sm:w-80 rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                        >
                    </div>
                </form>

                <button
                    onclick="document.getElementById('modalCreate').classList.remove('hidden')"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <span>+</span>
                    <span>Tambah</span>
                </button>
            </div>
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 divide-x divide-y divide-slate-100 lg:grid-cols-4 lg:divide-y-0">
            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Total Divisi
                </div>
                <div class="mt-2 text-3xl font-black text-slate-900">
                    {{ $rows->count() }}
                </div>
            </div>

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Active
                </div>
                <div class="mt-2 text-3xl font-black text-emerald-600">
                    {{ collect($rows)->where('is_active', 1)->count() }}
                </div>
            </div>

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Inactive
                </div>
                <div class="mt-2 text-3xl font-black text-amber-500">
                    {{ collect($rows)->where('is_active', 0)->count() }}
                </div>
            </div>

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    System
                </div>

                <div class="mt-3 inline-flex rounded-2xl bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    MASTER DATA
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-700">
                    Data Divisi
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    List divisi operasional ticketing
                </p>
            </div>

            <div class="text-sm text-slate-500">
                {{ $rows->count() }} Data
            </div>
        </div>

        <div class="max-h-[720px] overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white">
                <tr class="border-b border-slate-200">
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        No
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Divisi
                    </th>

                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Action
                    </th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($rows as $row)
                    <tr class="transition hover:bg-slate-50/70">
                        <td class="px-6 py-5 text-slate-400">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-6 py-5">
                            <div class="font-bold text-slate-900">
                                {{ $row->division }}
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                        <button
                                    data-id="{{ $row->id }}"
                                    data-division="{{ $row->division }}"
                                    onclick="openEditModal(this)"
                                    class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('ticketing.master.divisions.delete', $row->id) }}" onsubmit="return confirm('Hapus divisi ini?')">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-24 text-center">
                            <div class="mx-auto max-w-sm">
                                <div class="text-5xl">📭</div>

                                <div class="mt-5 text-xl font-black text-slate-900">
                                    Tidak ada data
                                </div>

                                <div class="mt-2 text-sm text-slate-500">
                                    Belum ada data divisi.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- MODAL CREATE --}}
<div id="modalCreate" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-20 max-w-lg rounded-3xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-900">
                Tambah Divisi
            </h3>
        </div>

        <form method="POST" action="{{ route('ticketing.master.divisions.store') }}">
            @csrf

            <div class="space-y-5 p-6">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Nama Divisi
                    </label>

                    <input
                        type="text"
                        name="division"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        placeholder="Contoh: IT"
                        required
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-5">
                <button
                    type="button"
                    onclick="document.getElementById('modalCreate').classList.add('hidden')"
                    class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>

                <button
                    class="rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
    <div class="mx-auto mt-20 max-w-lg rounded-3xl bg-white shadow-2xl">
        <div class="border-b border-slate-100 px-6 py-5">
            <h3 class="text-lg font-bold text-slate-900">
                Edit Divisi
            </h3>
        </div>

        <form id="editDivisionForm" method="POST" action="#">
            @csrf
            @method('PUT')

            <div class="space-y-5 p-6">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">
                        Nama Divisi
                    </label>

                    <input
                        id="editDivisionInput"
                        type="text"
                        name="division"
                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        required
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 px-6 py-5">
                <button
                    type="button"
                    onclick="document.getElementById('modalEdit').classList.add('hidden')"
                    class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>

                <button
                    class="rounded-2xl bg-amber-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-amber-600">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(el)
{
    document.getElementById('modalEdit').classList.remove('hidden');
    const form = document.getElementById('editDivisionForm');
    form.action = '{{ route('ticketing.master.divisions.update', ['id' => '__ID__']) }}'.replace('__ID__', el.dataset.id);
    document.getElementById('editDivisionInput').value = el.dataset.division || '';
}
</script>

@endsection
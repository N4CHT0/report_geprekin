@extends('Ticketing.layouts.app')

@section('title','Master Barang')

@section('content')

@php
    $ownerLabels = $ownerLabels ?? [
        'SCM' => 'SCM',
        'HC' => 'HC',
        'Marketing' => 'Marketing',
        'BND' => 'BND',
    ];

    $itemCategories = $itemCategories ?? [
        'SCM' => ['Equipment'],
        'HC' => ['Jenis Seragam', 'Jabatan Penerima', 'Ukuran Seragam'],
        'Marketing' => ['Jenis Pengajuan'],
        'BND' => ['Building', 'Equipment'],
    ];

    $activeOwner = $activeOwner ?? request('owner', 'SCM');
    $activeCategory = $activeCategory ?? request('category', $itemCategories[$activeOwner][0] ?? null);
    $hasCategoryColumn = $hasCategoryColumn ?? false;

    $currentOwnerCategories = $itemCategories[$activeOwner] ?? [];
@endphp

<div
    x-data="{
        q: @js(request('q')),
        createOwner: @js($activeOwner),
        createCategory: @js($activeCategory),
        editOwner: '',
        editCategory: '',
        categories: @js($itemCategories),
        openCreate() {
            this.createOwner = @js($activeOwner);
            this.createCategory = @js($activeCategory);
            document.getElementById('modalCreate').classList.remove('hidden');
        },
        setCreateDefaultCategory() {
            this.createCategory = (this.categories[this.createOwner] || [])[0] || '';
        },
        setEditDefaultCategory() {
            this.editCategory = (this.categories[this.editOwner] || [])[0] || '';
        }
    }"
    class="space-y-5"
>

    {{-- TOPBAR --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        {{-- HEADER --}}
        <div class="flex flex-col gap-5 border-b border-slate-100 p-6 xl:flex-row xl:items-center xl:justify-between">

            {{-- LEFT --}}
            <div>
                <div class="mb-3 flex items-center gap-2 text-sm">
                    <a href="{{ route('ticketing.dashboard') }}"
                       class="font-medium text-slate-500 transition hover:text-blue-600">
                        Dashboard
                    </a>

                    <span class="text-slate-300">/</span>

                    <span class="font-semibold text-slate-900">
                        Master Barang
                    </span>
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Master Data Barang
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Kelola item operasional berdasarkan submenu owner dan kategori mapping.
                </p>
            </div>

            {{-- RIGHT --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                {{-- SEARCH --}}
                <form method="GET">
                    <input type="hidden" name="owner" value="{{ $activeOwner }}">
                    @if($activeCategory)
                        <input type="hidden" name="category" value="{{ $activeCategory }}">
                    @endif

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            🔍
                        </div>

                        <input
                            x-model="q"
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari barang..."
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 sm:w-80"
                        >
                    </div>
                </form>

                {{-- CREATE --}}
                <button
                    type="button"
                    @click="openCreate()"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">
                    <span>+</span>
                    <span>Tambah</span>
                </button>
            </div>
        </div>

        {{-- OWNER SUBMENU --}}
        <div class="border-b border-slate-100 p-5">
            <div class="mb-3 text-xs font-black uppercase tracking-wider text-slate-400">
                Submenu Master Item
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($ownerLabels as $ownerKey => $ownerLabel)
                    @php
                        $firstCategory = $itemCategories[$ownerKey][0] ?? null;
                    @endphp

                    <a href="{{ route('ticketing.master.items', ['owner' => $ownerKey, 'category' => $firstCategory]) }}"
                       class="rounded-2xl border px-4 py-3 text-sm font-black transition
                              {{ $activeOwner === $ownerKey
                                    ? 'border-blue-200 bg-blue-50 text-blue-700'
                                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                        {{ $ownerLabel }}
                    </a>
                @endforeach
            </div>

            @if(!empty($currentOwnerCategories))
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($currentOwnerCategories as $category)
                        <a href="{{ route('ticketing.master.items', ['owner' => $activeOwner, 'category' => $category]) }}"
                           class="rounded-xl border px-3 py-2 text-xs font-bold transition
                                  {{ $activeCategory === $category
                                        ? 'border-slate-900 bg-slate-900 text-white'
                                        : 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-white' }}">
                            {{ $category }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-2 divide-x divide-y divide-slate-100 lg:grid-cols-4 lg:divide-y-0">

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Total Item
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
                    Owner
                </div>

                <div class="mt-3 inline-flex rounded-2xl bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    {{ $activeOwner }}
                </div>
            </div>

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Kategori
                </div>

                <div class="mt-3 inline-flex rounded-2xl bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                    {{ $activeCategory ?? 'Semua' }}
                </div>
            </div>
        </div>
    </div>

    @if(!$hasCategoryColumn)
        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-800">
            Kolom <b>category</b> belum ada di tabel <b>ticket_items</b>.
            Jalankan file SQL yang saya sertakan agar submenu kategori bisa menyimpan mapping dengan benar.
        </div>
    @endif

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-700">
                    Data Barang
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    List item untuk {{ $activeOwner }} @if($activeCategory) / {{ $activeCategory }} @endif
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
                            Item
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                            Owner
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                            Kategori
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
                                    {{ $row->item }}
                                </div>
                            </td>

                            <td class="px-6 py-5">
                                <span class="inline-flex rounded-xl bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                    {{ $row->owner ?? '-' }}
                                </span>
                            </td>

                            <td class="px-6 py-5">
                                <span class="inline-flex rounded-xl bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    {{ $row->category ?? '-' }}
                                </span>
                            </td>

                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        data-id="{{ $row->id }}"
                                        data-item="{{ $row->item }}"
                                        data-owner="{{ $row->owner ?? '' }}"
                                        data-category="{{ $row->category ?? '' }}"
                                        onclick="openEditModal(this)"
                                        class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('ticketing.master.items.delete', $row->id) }}"
                                          onsubmit="return confirm('Hapus barang ini?')">
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
                            <td colspan="5" class="px-6 py-24 text-center">
                                <div class="mx-auto max-w-sm">
                                    <div class="text-5xl">
                                        📭
                                    </div>

                                    <div class="mt-5 text-xl font-black text-slate-900">
                                        Tidak ada data
                                    </div>

                                    <div class="mt-2 text-sm text-slate-500">
                                        Belum ada item untuk submenu ini.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL CREATE --}}
    <div id="modalCreate" class="fixed inset-0 z-50 hidden bg-black/50 p-4">
        <div class="mx-auto mt-20 max-w-lg rounded-3xl bg-white shadow-2xl">
            <div class="border-b border-slate-100 px-6 py-5">
                <h3 class="text-lg font-bold text-slate-900">
                    Tambah Item
                </h3>
            </div>

            <form method="POST" action="{{ route('ticketing.master.items.store') }}">
                @csrf

                <div class="space-y-5 p-6">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Owner
                        </label>

                        <select
                            name="owner"
                            x-model="createOwner"
                            @change="setCreateDefaultCategory()"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required>
                            @foreach($ownerLabels as $ownerKey => $ownerLabel)
                                <option value="{{ $ownerKey }}">
                                    {{ $ownerLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Kategori / Submenu
                        </label>

                        <select
                            name="category"
                            x-model="createCategory"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required>
                            <template x-for="category in (categories[createOwner] || [])" :key="category">
                                <option :value="category" x-text="category"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Item
                        </label>

                        <input
                            type="text"
                            name="item"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            placeholder="Contoh: Kompor gas"
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
                    Edit Item
                </h3>
            </div>

            <form id="editItemForm" method="POST" action="#">
                @csrf
                @method('PUT')

                <div class="space-y-5 p-6">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Owner
                        </label>

                        <select
                            id="editItemOwner"
                            name="owner"
                            x-model="editOwner"
                            @change="setEditDefaultCategory()"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required>
                            @foreach($ownerLabels as $ownerKey => $ownerLabel)
                                <option value="{{ $ownerKey }}">
                                    {{ $ownerLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Kategori / Submenu
                        </label>

                        <select
                            id="editItemCategory"
                            name="category"
                            x-model="editCategory"
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required>
                            <template x-for="category in (categories[editOwner] || [])" :key="category">
                                <option :value="category" x-text="category"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Item
                        </label>

                        <input
                            id="editItemName"
                            type="text"
                            name="item"
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
</div>

<script>
function openEditModal(el) {
    const modal = document.getElementById('modalEdit');
    const form = document.getElementById('editItemForm');

    form.action = '{{ route('ticketing.master.items.update', ['id' => '__ID__']) }}'.replace('__ID__', el.dataset.id);

    document.getElementById('editItemName').value = el.dataset.item || '';

    const root = document.querySelector('[x-data]');
    if (root && root.__x) {
        root.__x.$data.editOwner = el.dataset.owner || 'SCM';

        setTimeout(function () {
            root.__x.$data.editCategory = el.dataset.category || ((root.__x.$data.categories[root.__x.$data.editOwner] || [])[0] || '');
        }, 50);
    } else {
        document.getElementById('editItemOwner').value = el.dataset.owner || 'SCM';
    }

    modal.classList.remove('hidden');
}
</script>

@endsection

@extends('Ticketing.layouts.app')

@section('title','Users Maintenance')

@section('content')

<div class="space-y-5">

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
                        Users Maintenance
                    </span>
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Users Maintenance
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Daftar pengguna dengan role maintenance.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" class="flex-1">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            🔍
                        </div>

                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari user..."
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
                    Total Users
                </div>
                <div class="mt-2 text-3xl font-black text-slate-900">
                    {{ $rows->count() }}
                </div>
            </div>

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Role
                </div>
                <div class="mt-3 inline-flex rounded-2xl bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                    MAINTENANCE
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

            <div class="p-5">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Last Updated
                </div>
                <div class="mt-2 text-sm font-bold text-slate-900">
                    {{ now()->translatedFormat('d M Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="text-2xl">✅</div>
                <div>
                    <h3 class="font-bold text-emerald-900">Sukses!</h3>
                    <p class="mt-1 text-sm text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if($errors->any())
        <div class="rounded-3xl border border-red-200 bg-red-50 p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="text-2xl">⚠️</div>
                <div>
                    <h3 class="font-bold text-red-900">Ada error</h3>
                    <ul class="mt-1 list-disc pl-5 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-700">
                    Data Users
                </h2>

                <p class="mt-1 text-xs text-slate-500">
                    Daftar lengkap pengguna dengan role maintenance
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
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">No</th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Nama</th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Area/TM</th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Dibuat</th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Updated</th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Action</th>
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
                                {{ $row->name }}
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <div class="text-slate-600">
                                {{ $row->email }}
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                                {{ ucfirst($row->role) }}
                            </span>
                        </td>

                        <td class="px-6 py-5">
                            <div class="text-sm font-semibold text-slate-900">
                                {{ $row->areas ?: '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <div class="text-xs text-slate-500">
                                {{ $row->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <div class="text-xs text-slate-500">
                                {{ $row->updated_at?->translatedFormat('d M Y H:i') ?? '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('ticketing.master.users.edit', $row->id) }}"
                                   class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 transition hover:bg-amber-100">
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('ticketing.master.users.delete', $row->id) }}" onsubmit="return confirm('Hapus user ini?')" style="display: inline;">
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
                        <td colspan="8" class="px-6 py-24 text-center">
                            <div class="mx-auto max-w-sm">
                                <div class="text-5xl">👥</div>

                                <div class="mt-5 text-xl font-black text-slate-900">
                                    Tidak ada data
                                </div>

                                <div class="mt-2 text-sm text-slate-500">
                                    Belum ada user dengan role maintenance.
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

{{-- MODAL CREATE USER --}}
<div id="modalCreate" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="w-full max-w-md rounded-3xl border border-slate-200 bg-white shadow-lg">

        {{-- HEADER --}}
        <div class="border-b border-slate-100 px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">
                        Tambah User Maintenance
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Buat user baru dengan role maintenance
                    </p>
                </div>

                <button
                    type="button"
                    onclick="document.getElementById('modalCreate').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600">
                    ✕
                </button>
            </div>
        </div>

        {{-- BODY --}}
        <form method="POST" action="{{ route('ticketing.master.users.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Nama
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="Masukkan nama user"
                >
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="Masukkan email"
                >
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Password
                </label>
                <input
                    type="password"
                    name="password"
                    required
                    class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="Minimal 8 karakter"
                >
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Area / TM
                </label>

                <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-3">

                    @foreach(
                        \DB::table('ticket_areas')
                            ->where('is_active', 1)
                            ->orderBy('area')
                            ->get() as $area
                    )

                        <label
                            class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 cursor-pointer transition hover:border-blue-300 hover:bg-blue-50"
                        >
                            <input
                                type="checkbox"
                                name="area_id[]"
                                value="{{ $area->id }}"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"

                                @checked(in_array($area->id, old('area_id', [])))
                            >

                            <span class="text-sm font-medium text-slate-700">
                                {{ $area->area }}
                            </span>
                        </label>

                    @endforeach

                </div>

                <p class="mt-2 text-xs text-slate-500">
                    Bisa pilih lebih dari satu Area / TM.
                </p>

                @error('area_id')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror

                @error('area_id.*')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button
                    type="button"
                    onclick="document.getElementById('modalCreate').classList.add('hidden')"
                    class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                    Batal
                </button>

                <button
                    type="submit"
                    class="flex-1 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
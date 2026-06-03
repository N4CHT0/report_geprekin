@extends('Ticketing.layouts.app')

@section('title','Edit User Maintenance')

@section('content')

<div class="space-y-5 max-w-3xl">

    {{-- TOPBAR --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 p-6">
            <div class="mb-3 flex items-center gap-2 text-sm">
                <a href="{{ route('ticketing.dashboard') }}"
                   class="font-medium text-slate-500 transition hover:text-blue-600">
                    Dashboard
                </a>

                <span class="text-slate-300">/</span>

                <a href="{{ route('ticketing.master.users') }}"
                   class="font-medium text-slate-500 transition hover:text-blue-600">
                    Users Maintenance
                </a>

                <span class="text-slate-300">/</span>

                <span class="font-semibold text-slate-900">
                    Edit User
                </span>
            </div>

            <h1 class="text-2xl font-black tracking-tight text-slate-900">
                Edit User Maintenance
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Edit data user dengan role maintenance
            </p>
        </div>
    </div>

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

    {{-- FORM --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm p-6">

        <form method="POST" action="{{ route('ticketing.master.users.update', $user->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- NAMA --}}
            <div>
                <label class="text-sm font-bold uppercase tracking-wider text-slate-700">
                    Nama
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="Masukkan nama user"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- EMAIL --}}
            <div>
                <label class="text-sm font-bold uppercase tracking-wider text-slate-700">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="Masukkan email"
                >
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- PASSWORD --}}
            <div>
                <label class="text-sm font-bold uppercase tracking-wider text-slate-700">
                    Password Baru
                </label>
                <input
                    type="password"
                    name="password"
                    class="mt-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                    placeholder="Kosongkan jika tidak ingin mengubah password"
                >
                <p class="mt-2 text-xs text-slate-500">
                    Minimal 8 karakter. Kosongkan jika tidak ingin mengubah.
                </p>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- AREA / TM --}}
            <div>
                <label class="text-sm font-bold uppercase tracking-wider text-slate-700">
                    Area / TM
                </label>

                <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-3">

                    @php
                        $selectedAreas = old('area_id', $user->selected_areas ?? []);
                    @endphp

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

                                @checked(in_array($area->id, $selectedAreas))
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
            
            {{-- CREATED AT --}}
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-600">
                    Dibuat
                </div>
                <div class="mt-2 text-sm text-slate-900">
                    {{ $user->created_at?->translatedFormat('d M Y H:i') ?? '-' }}
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('ticketing.master.users') }}"
                   class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-100">
                    Batal
                </a>

                <button
                    type="submit"
                    class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-center text-sm font-bold text-white transition hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>

@endsection
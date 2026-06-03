@extends('Ticketing.layouts.app')

@section('title','Daftar Ticket')

@section('content')

<div class="space-y-5">

    {{-- TOPBAR --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-5 border-b border-slate-100 p-6 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="mb-3 flex items-center gap-2 text-sm">
                    <a href="{{ route('ticketing.dashboard') }}"
                       class="font-medium text-slate-500 hover:text-blue-600">
                        Dashboard
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="font-semibold text-slate-900">Daftar Ticket</span>
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Daftar Ticket
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Filter, cari, monitor status ticket operasional, dan buka lokasi maps outlet.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('ticketing.export.csv') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700">
                    Export Excel
                </a>
            </div>
        </div>

        {{-- FILTER --}}
        <form method="GET" action="{{ route('ticketing.index') }}" class="grid gap-3 p-6 md:grid-cols-2 xl:grid-cols-8">

            {{-- SEARCH --}}
            <div class="relative xl:col-span-2">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    🔍
                </div>

                <input
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari no ticket / outlet / deskripsi / item..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                >
            </div>

            {{-- STATUS --}}
            <select
                name="status"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
            >
                <option value="">Semua Status</option>
                @foreach($lookups['statuses'] ?? [] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ $status }}
                    </option>
                @endforeach
            </select>

            {{-- AREA --}}
            <select
                name="area"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
            >
                <option value="">Semua Area</option>
                @foreach($lookups['areas'] ?? [] as $area)
                    <option value="{{ $area }}" @selected(request('area') === $area)>
                        {{ $area }}
                    </option>
                @endforeach
            </select>

            {{-- DIVISI --}}
            <select
                name="division"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
            >
                <option value="">Semua Divisi</option>
                @foreach($lookups['divisions'] ?? [] as $division)
                    <option value="{{ $division }}" @selected(request('division') === $division)>
                        {{ $division }}
                    </option>
                @endforeach
            </select>

            {{-- START DATE --}}
            <input
                type="date"
                name="start_date"
                value="{{ request('start_date') }}"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
            >

            {{-- END DATE --}}
            <input
                type="date"
                name="end_date"
                value="{{ request('end_date') }}"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
            >

            {{-- PER PAGE --}}
            <select
                name="per_page"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                onchange="this.form.submit()"
            >
                @foreach([10,25,50,100] as $size)
                    <option value="{{ $size }}" @selected((int)request('per_page', 15) === $size)>
                        Show {{ $size }}
                    </option>
                @endforeach
            </select>

            {{-- ACTION --}}
            <div class="flex gap-2 xl:col-span-8">
                <button
                    class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800">
                    Klik Mencari
                </button>

                <a href="{{ route('ticketing.index') }}"
                   class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">
                    Reset
                </a>
            </div>

        </form>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="text-sm font-black uppercase tracking-wider text-slate-700">
                    Data Ticket
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    List ticket berdasarkan filter yang aktif.
                </p>
            </div>

            <div class="text-sm text-slate-500">
                {{ $tickets->total() }} Data
            </div>
        </div>

        <div class="max-h-[720px] overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="sticky top-0 z-10 bg-white">
                <tr class="border-b border-slate-200">
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        No Ticket
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Outlet
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Link Maps
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Divisi / Item
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        PIC / Vendor
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Status
                    </th>
                    <th class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        Priority
                    </th>
                    <th class="px-6 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">
                        Action
                    </th>
                </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                @forelse($tickets as $t)
                    @php
                        $statusList = $lookups['statuses'] ?? [];
                        $canQuickUpdate = in_array($role ?? '', ['admin', 'pic', 'vendor', 'maintenance'], true);
                    @endphp

                    <tr class="transition hover:bg-slate-50/70">
                        <td class="px-6 py-5 align-top">
                            <div class="font-bold text-slate-900">
                                {{ $t->ticket_number }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $t->created_at ?? '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top">
                            <div class="font-semibold text-slate-900">
                                {{ $t->nama_outlet ?? '-' }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $t->kota ?? '-' }} - {{ $t->area ?? '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top">
                            @if(!empty($t->maps_url))
                                <a href="{{ $t->maps_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100">
                                    Buka Maps
                                </a>
                            @else
                                <span class="text-xs font-semibold text-slate-400">Belum ada link</span>
                            @endif
                        </td>

                        <td class="px-6 py-5 align-top">
                            <div class="font-semibold text-slate-800">
                                {{ $t->division ?? '-' }}
                            </div>
                            <div class="text-xs text-slate-500">
                                {{ $t->ticket_type ?? '-' }} / {{ $t->item ?? '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top">
                            <div class="font-semibold text-slate-800">
                                {{ $t->pic_name ?? '-' }}
                            </div>
                            <div class="text-xs text-slate-500">
                                Vendor: {{ $t->vendor_name ?? '-' }}
                            </div>
                        </td>

                        <td class="px-6 py-5 align-top">
                            @if($canQuickUpdate)
                                <form method="POST" action="{{ route('ticketing.status.quick', $t->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select
                                        name="status"
                                        onchange="if(confirm('Ubah status ticket {{ $t->ticket_number }} menjadi ' + this.value + '?')) { this.form.submit(); } else { this.value = '{{ $t->status }}'; }"
                                        class="min-w-[150px] rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                    >
                                        @foreach($statusList as $status)
                                            <option value="{{ $status }}" @selected(strtolower($t->status) === strtolower($status))>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="inline-flex rounded-2xl bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    {{ $t->status }}
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-5 align-top">
                            <span class="inline-flex rounded-2xl bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">
                                {{ $t->priority ?? '-' }}
                            </span>
                        </td>

                        <td class="px-6 py-5 text-right align-top">
                            <a href="{{ route('ticketing.show', $t->id) }}"
                               class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700 hover:bg-blue-100">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-24 text-center">
                            <div class="mx-auto max-w-sm">
                                <div class="text-5xl">📭</div>
                                <div class="mt-5 text-xl font-black text-slate-900">
                                    Belum ada ticket
                                </div>
                                <div class="mt-2 text-sm text-slate-500">
                                    Data ticket belum tersedia atau filter terlalu spesifik.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 md:flex-row md:items-center md:justify-between">
            <div class="text-sm text-slate-500">
                Menampilkan
                <span class="font-bold text-slate-900">{{ $tickets->firstItem() ?? 0 }}</span>
                -
                <span class="font-bold text-slate-900">{{ $tickets->lastItem() ?? 0 }}</span>
                dari
                <span class="font-bold text-slate-900">{{ $tickets->total() }}</span>
                data
            </div>

            <div>
                {{ $tickets->links() }}
            </div>
        </div>
    </div>

</div>

@endsection

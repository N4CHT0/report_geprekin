@extends('Ticketing.layouts.app')

@section('title', 'Detail Ticket')

@section('content')
@php
    $user = auth()->user();
    $role = strtolower($user->role ?? '');
    $status = strtolower($row->status ?? 'open');

    $statusBadge = [
        'open' => 'bg-blue-50 text-blue-700 ring-blue-100',
        'confirmed' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        'process' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'hold' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'closed' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'cancel' => 'bg-red-50 text-red-700 ring-red-100',
    ];

    $priorityBadge = [
        'low' => 'bg-slate-50 text-slate-700 ring-slate-100',
        'medium' => 'bg-blue-50 text-blue-700 ring-blue-100',
        'high' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'urgent' => 'bg-red-50 text-red-700 ring-red-100',
    ];

    $actionItems = [
        [
            'label' => 'Hold',
            'desc' => 'Ticket ditahan sementara karena menunggu informasi, approval, sparepart, atau kendala lain.',
            'route' => route('ticketing.hold', $row->id),
            'icon' => '⏸',
            'class' => 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100',
        ],
        [
            'label' => 'Cancel',
            'desc' => 'Ticket dibatalkan dan tidak akan dilanjutkan ke proses berikutnya.',
            'route' => route('ticketing.cancel', $row->id),
            'icon' => '✕',
            'class' => 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100',
        ],
        [
            'label' => 'Konfirmasi',
            'desc' => 'Ticket sedang dikerjakan oleh PIC atau tim terkait.',
            'route' => route('ticketing.process', $row->id),
            'icon' => '↗',
            'class' => 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100',
        ],
        [
            'label' => 'Done',
            'desc' => 'Ticket sudah selesai ditangani dan siap ditutup.',
            'route' => route('ticketing.close', $row->id),
            'icon' => '✓',
            'class' => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
        ],
    ];
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <div class="mb-3 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                    @if($role !== 'crew')
                        <a href="{{ route('ticketing.dashboard') }}" class="transition hover:text-blue-600">
                            Dashboard
                        </a>

                        <span>/</span>

                        <a href="{{ route('ticketing.index') }}" class="transition hover:text-blue-600">
                            Ticket
                        </a>

                        <span>/</span>
                    @endif

                    <span class="font-semibold text-slate-900">
                        {{ $row->ticket_number }}
                    </span>
                </div>

                <h1 class="break-words text-2xl font-black text-slate-900 md:text-3xl">
                    {{ $row->ticket_number }}
                </h1>

                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $statusBadge[$status] ?? 'bg-slate-50 text-slate-700 ring-slate-100' }}">
                        {{ strtoupper($row->status) }}
                    </span>

                    @if($row->priority)
                        <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $priorityBadge[strtolower($row->priority)] ?? 'bg-slate-50 text-slate-700 ring-slate-100' }}">
                            PRIORITY {{ strtoupper($row->priority) }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:min-w-[280px]">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                        Dibuat
                    </div>

                    <div class="mt-1 text-sm font-bold text-slate-900">
                        {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y H:i') }}
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                        SLA
                    </div>

                    <div class="mt-1 text-sm font-bold text-slate-900">
                        {{ $row->sla_hours ?? '-' }} Jam
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">

        {{-- LEFT --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- DETAIL --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h2 class="text-lg font-black text-slate-900">
                        Detail Ticket
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Informasi utama terkait outlet, pelapor, kategori, dan kronologi ticket.
                    </p>
                </div>

                <div class="max-h-[520px] overflow-y-auto p-6">
                    <div class="grid gap-5 md:grid-cols-2">

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Outlet
                            </div>

                            <div class="mt-1 break-words font-bold text-slate-900">
                                {{ $row->nama_outlet }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Kota
                            </div>

                            <div class="mt-1 break-words font-bold text-slate-900">
                                {{ $row->kota }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Area
                            </div>

                            <div class="mt-1 break-words font-bold text-slate-900">
                                {{ $row->area }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Divisi
                            </div>

                            @if(in_array($role, ['superadmin', 'admin', 'admin_ticketing', 'ticket_admin', 'superadmin_audit']))
                                <form method="POST"
                                      action="{{ route('ticketing.update-division', $row->id) }}"
                                      class="mt-2">
                                    @csrf
                                    @method('PUT')

                                    <select name="division"
                                            onchange="this.form.submit()"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                        <option value="">Pilih Divisi</option>

                                        @foreach($lookups['divisions'] ?? [] as $division)
                                            <option value="{{ $division }}"
                                                @selected(strtolower((string) $row->division) === strtolower($division))>
                                                {{ $division }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <div class="mt-1 break-words font-bold text-slate-900">
                                    {{ $row->division }}
                                </div>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Jenis Ticket
                            </div>

                            <div class="mt-1 break-words font-bold text-slate-900">
                                {{ $row->ticket_type }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Item
                            </div>

                            <div class="mt-1 break-words font-bold text-slate-900">
                                {{ $row->item }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Pelapor
                            </div>

                            <div class="mt-1 break-words font-bold text-slate-900">
                                {{ $row->reporter_name ?? '-' }}
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                No HP
                            </div>

                            <div class="mt-1 break-words font-bold text-slate-900">
                                {{ $row->reporter_phone ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <form method="POST"
                          action="{{ route('ticketing.update-content', $row->id) }}"
                          class="mt-6 space-y-4 rounded-2xl border border-slate-100 bg-white p-5">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 class="text-sm font-black uppercase tracking-wide text-slate-700">
                                    Update Detail Ticket
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Deskripsi, catatan tambahan, dan link Google Maps bisa diubah langsung dari halaman ini.
                                </p>
                            </div>

                            @if(!empty($row->maps_url))
                                <a href="{{ $row->maps_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex shrink-0 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-black text-emerald-700 transition hover:bg-emerald-100">
                                    Buka Maps
                                </a>
                            @endif
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Deskripsi
                            </label>

                            <textarea name="description"
                                      rows="5"
                                      required
                                      class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">{{ old('description', $row->description) }}</textarea>

                            @error('description')
                                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Catatan Tambahan
                            </label>

                            <textarea name="extra_note"
                                      rows="4"
                                      class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">{{ old('extra_note', $row->extra_note) }}</textarea>

                            @error('extra_note')
                                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Link Google Maps
                            </label>

                            <input type="url"
                                   name="maps_url"
                                   value="{{ old('maps_url', $row->maps_url ?? '') }}"
                                   placeholder="https://maps.app.goo.gl/... atau https://www.google.com/maps/..."
                                   class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100">

                            <p class="mt-2 text-xs leading-5 text-slate-400">
                                Kosongkan jika belum ada link maps.
                            </p>

                            @error('maps_url')
                                <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-800 md:w-auto">
                            Simpan Perubahan Detail
                        </button>
                    </form>
                </div>
            </div>

            {{-- ACTIVITY LOG --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-6 py-5">
                    <h2 class="text-lg font-black text-slate-900">
                        Activity Log
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Riwayat perubahan status dan catatan aktivitas ticket.
                    </p>
                </div>

                <div class="max-h-[520px] overflow-y-auto divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <div class="p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="break-words font-bold text-slate-900">
                                        {{ $log->action }}
                                    </div>

                                    <div class="mt-1 text-sm text-slate-500">
                                        {{ $log->user_name ?? 'System' }}
                                    </div>
                                </div>

                                <div class="shrink-0 whitespace-nowrap text-xs text-slate-400">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}
                                </div>
                            </div>

                            @if($log->note)
                                <div class="mt-3 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                                    {{ $log->note }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-slate-500">
                            Belum ada activity log.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="space-y-6">

            {{-- ACTION --}}
            @if($role !== 'crew')
                <div
                    x-data="{
                        confirmOpen: false,
                        actionUrl: '',
                        actionLabel: '',
                        actionText: '',
                        openConfirm(url, label, text) {
                            this.actionUrl = url;
                            this.actionLabel = label;
                            this.actionText = text;
                            this.confirmOpen = true;
                        }
                    }"
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div>
                        <h2 class="text-lg font-black text-slate-900">
                            Action Ticket
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Pilih aksi sesuai progres penanganan ticket. Aksi tertentu akan mengubah status ticket.
                        </p>
                    </div>

                    <div class="mt-5 max-h-[360px] space-y-3 overflow-y-auto pr-1">
                        @foreach($actionItems as $item)
                            <button
                                type="button"
                                @click="openConfirm('{{ $item['route'] }}', '{{ $item['label'] }}', '{{ $item['desc'] }}')"
                                class="group flex w-full items-start gap-3 rounded-2xl border px-4 py-3 text-left text-sm transition {{ $item['class'] }}"
                            >
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/70 text-base font-black shadow-sm">
                                    {{ $item['icon'] }}
                                </span>

                                <span class="min-w-0">
                                    <span class="block font-black">
                                        {{ $item['label'] }}
                                    </span>

                                    <span class="mt-0.5 block text-xs leading-5 opacity-80">
                                        {{ $item['desc'] }}
                                    </span>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <div
                        x-cloak
                        x-show="confirmOpen"
                        x-transition.opacity
                        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4"
                    >
                        <div
                            x-show="confirmOpen"
                            x-transition.scale.origin.center
                            @click.outside="confirmOpen = false"
                            class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl"
                        >
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                                    !
                                </div>

                                <div>
                                    <h3 class="text-lg font-black text-slate-900">
                                        Konfirmasi Aksi
                                    </h3>

                                    <p class="mt-1 text-sm leading-6 text-slate-500">
                                        Anda akan menjalankan aksi
                                        <span class="font-bold text-slate-900" x-text="actionLabel"></span>.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                                <span x-text="actionText"></span>
                            </div>

                            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                <button
                                    type="button"
                                    @click="confirmOpen = false"
                                    class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50"
                                >
                                    Batal
                                </button>

                                <form method="POST" :action="actionUrl" class="w-full space-y-4 sm:w-auto">
                                    @csrf

                                    <div
                                        x-show="actionLabel === 'Hold' || actionLabel === 'Cancel'"
                                        x-transition
                                        class="sm:min-w-[320px]"
                                    >
                                        <label class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                            Alasan
                                        </label>

                                        <textarea
                                            name="reason"
                                            rows="3"
                                            placeholder="Isi alasan hold / cancel..."
                                            :required="actionLabel === 'Hold' || actionLabel === 'Cancel'"
                                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                        >{{ old('reason') }}</textarea>

                                        @error('reason')
                                            <div class="mt-2 text-xs font-bold text-red-600">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button
                                        type="submit"
                                        class="w-full rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800 sm:w-auto"
                                    >
                                        Ya, Lanjutkan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- PRIORITY --}}
            @if($role === 'admin')
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-900">
                        Update Prioritas
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Atur tingkat prioritas agar ticket lebih mudah dipantau.
                    </p>

                    <form method="POST"
                          action="{{ route('ticketing.update-priority', $row->id) }}"
                          class="mt-5 space-y-4">
                        @csrf
                        @method('PUT')

                        <select name="priority"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                required>
                            <option value="">
                                Pilih Prioritas
                            </option>

                            @foreach($lookups['priorities'] as $priority)
                                <option value="{{ $priority }}"
                                    @selected(strtolower((string)$row->priority) === strtolower($priority))>
                                    {{ $priority }}
                                </option>
                            @endforeach
                        </select>

                        <button
                            class="w-full rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                            Simpan Prioritas
                        </button>
                    </form>
                </div>
            @endif

            {{-- PIC --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">
                    PIC & Vendor
                </h2>

                <p class="mt-1 text-sm leading-6 text-slate-500">
                    Informasi penanggung jawab internal dan vendor terkait.
                </p>

                <div class="mt-5 space-y-3">
                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            PIC
                        </div>

                        <div class="mt-1 break-words font-bold text-slate-900">
                            {{ $row->pic_user_id ?? '-' }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Vendor
                        </div>

                        <div class="mt-1 break-words font-bold text-slate-900">
                            {{ $row->vendor_user_id ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- ATTACHMENT --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">
                            Attachment
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            Lampiran foto pendukung dari pelapor.
                        </p>
                    </div>

                    @if(($attachments ?? collect())->count() > 0)
                        <span class="shrink-0 rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700 ring-1 ring-blue-100">
                            {{ $attachments->count() }} Foto
                        </span>
                    @endif
                </div>

                @if(($attachments ?? collect())->count() > 0)
                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        @foreach($attachments as $attachment)
                            @php
                                $filePath = $attachment->file_path ?? null;
                                $fileName = $attachment->file_name ?? basename($filePath ?? 'attachment');
                                $fileType = strtolower($attachment->file_type ?? '');
                                $fileUrl = $filePath ? asset('storage/'.$filePath) : null;
                                $isImage = $fileUrl && (
                                    str_contains($fileType, 'image')
                                    || preg_match('/\.(jpg|jpeg|png|gif|webp|bmp|svg)$/i', $filePath)
                                );
                            @endphp

                            @if($fileUrl)
                                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 shadow-sm">
                                    @if($isImage)
                                        <a href="{{ $fileUrl }}" target="_blank" class="block bg-white">
                                            <img src="{{ $fileUrl }}"
                                                 alt="{{ $fileName }}"
                                                 loading="lazy"
                                                 decoding="async"
                                                 class="h-52 w-full object-contain">
                                        </a>
                                    @else
                                        <div class="flex h-40 items-center justify-center bg-white text-4xl">
                                            📎
                                        </div>
                                    @endif

                                    <div class="border-t border-slate-200 bg-white p-3">
                                        <div class="truncate text-xs font-bold text-slate-700">
                                            {{ $fileName }}
                                        </div>

                                        <a href="{{ $fileUrl }}"
                                           target="_blank"
                                           class="mt-3 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50">
                                            Lihat Foto
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                        Tidak ada attachment.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
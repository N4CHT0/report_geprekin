@extends('Ticketing.layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $totalTickets = (int) ($summary['total'] ?? $total ?? 0);
    $openTickets = (int) ($summary['open'] ?? 0);
    $confirmedTickets = (int) ($summary['confirmed'] ?? 0);
    $processTickets = (int) ($summary['process'] ?? 0);
    $holdTickets = (int) ($summary['hold'] ?? 0);
    $closedTickets = (int) ($summary['closed'] ?? $closed ?? 0);
    $cancelTickets = (int) ($summary['cancel'] ?? 0);
    $urgentTickets = (int) ($summary['urgent'] ?? 0);

    $activeTickets = $openTickets + $confirmedTickets + $processTickets + $holdTickets;

    $openPercent = $totalTickets > 0 ? round(($openTickets / $totalTickets) * 100) : 0;
    $activePercent = $totalTickets > 0 ? round(($activeTickets / $totalTickets) * 100) : 0;
    $closedPercent = $totalTickets > 0 ? round(($closedTickets / $totalTickets) * 100) : 0;
    $urgentPercent = $totalTickets > 0 ? round(($urgentTickets / $totalTickets) * 100) : 0;

    $trendLabels = collect($trend ?? [])->pluck('label')->values();
    $trendTotals = collect($trend ?? [])->pluck('total')->values();

    $statusColors = [
        'open' => 'bg-blue-500',
        'confirmed' => 'bg-cyan-500',
        'process' => 'bg-amber-500',
        'hold' => 'bg-orange-500',
        'closed' => 'bg-emerald-500',
        'cancel' => 'bg-red-500',
        'unknown' => 'bg-slate-500',
    ];

    $statusBadge = [
        'open' => 'bg-blue-50 text-blue-700 ring-blue-100',
        'confirmed' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
        'process' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'hold' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'closed' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'cancel' => 'bg-red-50 text-red-700 ring-red-100',
    ];

    $statusLabel = [
        'open' => 'Open',
        'confirmed' => 'Confirmed',
        'process' => 'Process',
        'hold' => 'Hold',
        'closed' => 'Closed',
        'cancel' => 'Cancel',
        'unknown' => 'Unknown',
    ];
@endphp

<div
    x-data="{
        tickets: @js($trendTotals),
        labels: @js($trendLabels),
        maxTicket: {{ (int) ($maxTrend ?? 1) }}
    }"
    class="space-y-6"
>
    {{-- HEADER REPORT --}}
    <div class="overflow-hidden rounded-3xl bg-slate-950 text-white shadow-xl">
        <div class="relative p-6 sm:p-8">
            <div class="absolute right-0 top-0 h-40 w-40 rounded-full bg-blue-500/30 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/2 h-32 w-32 rounded-full bg-cyan-400/20 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="mb-2 inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-blue-100 ring-1 ring-white/10">
                        Operational Ticketing Report
                    </div>

                    <h1 class="text-2xl font-black tracking-tight sm:text-3xl">
                        Dashboard Laporan Ticketing
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Monitoring performa ticket, SLA, status pekerjaan, area bermasalah,
                        divisi pemohon, dan aktivitas terbaru berdasarkan data aktual sistem.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:flex">
                    <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                        <div class="text-xs text-slate-300">Active Ticket</div>
                        <div class="mt-1 text-xl font-black">{{ number_format($activeTickets) }}</div>
                    </div>

                    <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                        <div class="text-xs text-slate-300">Urgent Active</div>
                        <div class="mt-1 text-xl font-black">{{ number_format($urgentTickets) }}</div>
                    </div>

                    <a href="{{ route('ticketing.index') }}"
                       class="col-span-2 inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-center text-sm font-bold text-white shadow-lg shadow-blue-950/40 transition hover:bg-blue-700 sm:col-span-1">
                        Lihat Semua Ticket
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Total Ticket</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">
                        {{ number_format($totalTickets) }}
                    </h2>
                </div>
                <div class="rounded-2xl bg-blue-50 p-3 text-2xl">🎫</div>
            </div>
            <p class="mt-5 text-sm text-slate-500">
                Semua ticket yang tercatat di sistem.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Open Ticket</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">
                        {{ number_format($openTickets) }}
                    </h2>
                </div>
                <div class="rounded-2xl bg-amber-50 p-3 text-2xl">⏳</div>
            </div>

            <div class="mt-5 h-2 rounded-full bg-slate-100">
                <div class="h-2 rounded-full bg-amber-500" style="width: {{ $openPercent }}%"></div>
            </div>

            <p class="mt-2 text-sm text-slate-500">
                {{ $openPercent }}% dari total ticket.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Closed Ticket</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">
                        {{ number_format($closedTickets) }}
                    </h2>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-3 text-2xl">✅</div>
            </div>

            <div class="mt-5 flex items-center gap-2 text-sm">
                <span class="rounded-full bg-emerald-50 px-2 py-1 font-bold text-emerald-600">
                    {{ number_format($completionRate ?? $closedPercent, 1) }}%
                </span>
                <span class="text-slate-500">completion rate</span>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-500">Overdue SLA</p>
                    <h2 class="mt-3 text-3xl font-black text-slate-900">
                        {{ number_format($overdue ?? 0) }}
                    </h2>
                </div>
                <div class="rounded-2xl bg-red-50 p-3 text-2xl">🚨</div>
            </div>

            <p class="mt-5 text-sm text-slate-500">
                Ticket aktif yang melewati batas SLA.
            </p>
        </div>
    </div>

    {{-- SECONDARY METRICS --}}
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Confirmed</p>
            <div class="mt-3 flex items-end justify-between">
                <div class="text-2xl font-black text-slate-900">{{ number_format($confirmedTickets) }}</div>
                <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-bold text-cyan-700">Verified</span>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Process</p>
            <div class="mt-3 flex items-end justify-between">
                <div class="text-2xl font-black text-slate-900">{{ number_format($processTickets) }}</div>
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">On Progress</span>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Hold</p>
            <div class="mt-3 flex items-end justify-between">
                <div class="text-2xl font-black text-slate-900">{{ number_format($holdTickets) }}</div>
                <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-700">Pending</span>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">Cancel</p>
            <div class="mt-3 flex items-end justify-between">
                <div class="text-2xl font-black text-slate-900">{{ number_format($cancelTickets) }}</div>
                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">Cancelled</span>
            </div>
        </div>
    </div>

    {{-- MAIN DASHBOARD --}}
    <div class="grid gap-6 xl:grid-cols-3">
        {{-- CHART --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Trend Ticket Masuk</h3>
                    <p class="text-sm text-slate-500">Jumlah ticket berdasarkan 7 hari terakhir.</p>
                </div>
                <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-600">
                    Data Aktual
                </span>
            </div>

            @if(collect($trend ?? [])->count() > 0)
                <div class="flex h-72 items-end gap-3">
                    <template x-for="(item, index) in tickets" :key="index">
                        <div class="flex flex-1 flex-col items-center gap-3">
                            <div class="flex h-56 w-full items-end rounded-2xl bg-slate-100 p-1">
                                <div
                                    class="w-full rounded-xl bg-blue-600 transition-all"
                                    :style="`height: ${maxTicket > 0 ? Math.max((item / maxTicket) * 100, item > 0 ? 8 : 0) : 0}%`"
                                ></div>
                            </div>

                            <div class="text-xs font-bold text-slate-500" x-text="labels[index]"></div>
                            <div class="text-xs font-semibold text-slate-400" x-text="item"></div>
                        </div>
                    </template>
                </div>
            @else
                <div class="grid h-72 place-items-center rounded-2xl bg-slate-50 text-sm text-slate-500">
                    Belum ada data trend ticket.
                </div>
            @endif
        </div>

        {{-- SLA --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-slate-900">Kesehatan SLA</h3>
            <p class="mt-1 text-sm text-slate-500">Ringkasan performa penyelesaian ticket aktif.</p>

            <div class="mt-6 grid place-items-center">
                <div class="relative grid h-44 w-44 place-items-center rounded-full bg-slate-100">
                    <div class="absolute inset-4 rounded-full bg-white"></div>
                    <div class="relative text-center">
                        <div class="text-4xl font-black text-emerald-600">
                            {{ (int) ($onSlaPercent ?? 0) }}%
                        </div>
                        <div class="text-xs font-bold uppercase text-slate-400">On SLA</div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-semibold text-slate-600">On Time</span>
                        <span class="font-bold text-slate-900">{{ (int) ($onSlaPercent ?? 0) }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-emerald-500" style="width: {{ (int) ($onSlaPercent ?? 0) }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-semibold text-slate-600">Warning</span>
                        <span class="font-bold text-slate-900">{{ (int) ($warningPercent ?? 0) }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-amber-500" style="width: {{ (int) ($warningPercent ?? 0) }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="font-semibold text-slate-600">Overdue</span>
                        <span class="font-bold text-slate-900">{{ (int) ($overduePercent ?? 0) }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-red-500" style="width: {{ (int) ($overduePercent ?? 0) }}%"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                <div class="rounded-2xl bg-emerald-50 p-4">
                    <div class="text-xs font-bold uppercase text-emerald-700">On SLA</div>
                    <div class="mt-1 text-xl font-black text-emerald-800">{{ number_format($onSla ?? 0) }}</div>
                </div>

                <div class="rounded-2xl bg-red-50 p-4">
                    <div class="text-xs font-bold uppercase text-red-700">Overdue</div>
                    <div class="mt-1 text-xl font-black text-red-800">{{ number_format($overdue ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE + BREAKDOWN --}}
    <div class="grid gap-6 xl:grid-cols-3">
        {{-- TABLE --}}
        <div class="rounded-3xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-black text-slate-900">Ticket Terbaru</h3>
                    <p class="text-sm text-slate-500">Daftar aktivitas terakhir dari sistem ticketing.</p>
                </div>

                <a href="{{ route('ticketing.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700">
                    Lihat semua
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-6 py-4">Ticket</th>
                            <th class="px-6 py-4">Outlet / Area</th>
                            <th class="px-6 py-4">PIC</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">SLA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($latest ?? [] as $ticket)
                            @php
                                $ticketStatus = strtolower(trim($ticket->status ?? 'unknown'));
                                $ticketTitle = $ticket->title
                                    ?? $ticket->subject
                                    ?? $ticket->description
                                    ?? $ticket->problem
                                    ?? $ticket->request
                                    ?? '-';

                                $ticketNumber = $ticket->ticket_number
                                    ?? $ticket->code
                                    ?? $ticket->no_ticket
                                    ?? $ticket->id;

                                $outletArea = $ticket->nama_outlet
                                    ?? $ticket->outlet_name
                                    ?? $ticket->area
                                    ?? '-';

                                if (!empty($ticket->kota) && $outletArea !== '-') {
                                    $outletArea .= ' - ' . $ticket->kota;
                                }

                                $isTicketOverdue = false;

                                if (
                                    !in_array($ticketStatus, ['closed', 'cancel']) &&
                                    !empty($ticket->opened_at) &&
                                    !empty($ticket->sla_hours)
                                ) {
                                    $isTicketOverdue = now()->diffInHours(\Carbon\Carbon::parse($ticket->opened_at)) > (int) $ticket->sla_hours;
                                }

                                $badgeClass = $statusBadge[$ticketStatus] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                            @endphp

                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900">
                                        #{{ $ticketNumber }}
                                    </div>
                                    <div class="max-w-xs truncate text-xs text-slate-500">
                                        {{ $ticketTitle }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $outletArea }}
                                </td>

                                <td class="px-6 py-4 text-slate-600">
                                    {{ $ticket->pic_name ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $badgeClass }}">
                                        {{ $statusLabel[$ticketStatus] ?? ucfirst($ticketStatus) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @if(in_array($ticketStatus, ['closed', 'cancel']))
                                        <span class="font-bold text-slate-500">-</span>
                                    @elseif($isTicketOverdue)
                                        <span class="font-bold text-red-600">Overdue</span>
                                    @else
                                        <span class="font-bold text-emerald-600">On Time</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">
                                    Belum ada data ticket.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BREAKDOWN --}}
        <div class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-black text-slate-900">Status Ticket</h3>
                <p class="mt-1 text-sm text-slate-500">Komposisi ticket berdasarkan status.</p>

                <div class="mt-5 space-y-4">
                    @forelse($byStatus ?? [] as $status)
                        @php
                            $key = strtolower(trim($status->status ?? 'unknown'));
                            $percent = $totalTickets > 0 ? round(((int) $status->total / $totalTickets) * 100) : 0;
                            $barColor = $statusColors[$key] ?? 'bg-slate-500';
                        @endphp

                        <div>
                            <div class="mb-2 flex justify-between text-sm">
                                <span class="font-semibold text-slate-600">
                                    {{ $statusLabel[$key] ?? ucfirst($key) }}
                                </span>
                                <span class="font-bold">{{ number_format($status->total ?? 0) }}</span>
                            </div>

                            <div class="h-3 rounded-full bg-slate-100">
                                <div class="h-3 rounded-full {{ $barColor }}" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada data status.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-black text-slate-900">Area Paling Banyak Ticket</h3>
                <p class="mt-1 text-sm text-slate-500">Top area berdasarkan jumlah ticket.</p>

                <div class="mt-5 space-y-3">
                    @forelse($byArea ?? [] as $area)
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 p-4">
                            <div class="min-w-0">
                                <div class="truncate font-bold text-slate-900">
                                    {{ $area->area ?? 'Tidak ada area' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    Total ticket area
                                </div>
                            </div>

                            <div class="shrink-0 text-xl font-black text-slate-900">
                                {{ number_format($area->total ?? 0) }}
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada data area.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- DIVISION REPORT --}}
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900">Ticket Berdasarkan Divisi</h3>
                <p class="text-sm text-slate-500">Ringkasan jumlah ticket dari masing-masing divisi.</p>
            </div>

            <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                Top {{ collect($byDivision ?? [])->count() }} Divisi
            </span>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse($byDivision ?? [] as $division)
                @php
                    $divisionPercent = $totalTickets > 0 ? round(((int) $division->total / $totalTickets) * 100) : 0;
                @endphp

                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="truncate font-bold text-slate-900">
                                {{ $division->division ?? 'Tidak ada divisi' }}
                            </div>
                            <div class="mt-1 text-xs text-slate-500">
                                {{ $divisionPercent }}% dari total ticket
                            </div>
                        </div>

                        <div class="shrink-0 text-xl font-black text-slate-900">
                            {{ number_format($division->total ?? 0) }}
                        </div>
                    </div>

                    <div class="mt-4 h-2 rounded-full bg-white">
                        <div class="h-2 rounded-full bg-blue-600" style="width: {{ $divisionPercent }}%"></div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-slate-50 p-6 text-sm text-slate-500 md:col-span-2 xl:col-span-4">
                    Belum ada data divisi.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

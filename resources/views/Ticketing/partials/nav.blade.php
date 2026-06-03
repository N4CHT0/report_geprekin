@php
    $role = strtolower(auth()->user()->role ?? '');
    $isMaintenance = $role === 'maintenance';
    $isCrew = $role === 'crew';

    $isAdmin = in_array($role, [
        'superadmin',
        'admin',
        'admin_ticketing',
        'ticket_admin',
        'superadmin_audit'
    ]);

    $navItem = 'group flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition-all';
    $navActive = 'bg-blue-50 text-blue-700 ring-1 ring-blue-100 shadow-sm';
    $navInactive = 'text-slate-600 hover:bg-slate-100 hover:text-slate-900';

    $mobileClose = 'if (window.innerWidth < 1024) sidebarOpen = false';
@endphp

<aside
    x-cloak
    class="fixed inset-y-0 left-0 z-50 flex w-[86vw] max-w-80 flex-col border-r border-slate-200 bg-white shadow-2xl transition-transform duration-300 sm:w-72 lg:w-64 lg:max-w-none lg:translate-x-0 lg:shadow-sm"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-100 px-4 sm:px-5">
        <a href="{{ $isCrew ? route('ticketing.create') : route('ticketing.dashboard') }}"
           class="flex min-w-0 items-center gap-3"
           @click="{{ $mobileClose }}">

            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 font-bold text-white shadow">
                TK
            </div>

            <div class="min-w-0">
                <h2 class="truncate text-sm font-bold leading-none text-slate-900">
                    Ticketing System
                </h2>

                <p class="mt-1 truncate text-xs text-slate-500">
                    Backoffice Panel
                </p>
            </div>
        </a>

        <button
            type="button"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 lg:hidden"
            @click="sidebarOpen = false"
            aria-label="Tutup menu">
            ✕
        </button>
    </div>

    <div class="app-scrollbar min-h-0 flex-1 overflow-y-auto px-3 py-4 pb-36">

        {{-- CREW --}}
        @if($isCrew)

            <div class="mb-6">
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Menu Ticketing
                </p>

                <div class="space-y-1">

                    <a href="{{ route('ticketing.create') }}"
                       @click="{{ $mobileClose }}"
                       class="{{ $navItem }}
                       {{ request()->routeIs('ticketing.create') ? $navActive : $navInactive }}">

                        <span class="text-base">🎫</span>
                        <span class="truncate">Buat Ticket</span>
                    </a>

                </div>
            </div>

            <div class="mx-3 rounded-2xl border border-blue-100 bg-blue-50 p-3 text-xs leading-5 text-blue-700">
                Role crew hanya dapat membuat ticket baru.
            </div>

        {{-- MAINTENANCE --}}
        @elseif($isMaintenance)

            <div class="mb-6">
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Main Menu
                </p>

                <div class="space-y-1">

                    <a href="{{ route('ticketing.dashboard') }}"
                       @click="{{ $mobileClose }}"
                       class="{{ $navItem }}
                       {{ request()->routeIs('ticketing.dashboard') ? $navActive : $navInactive }}">

                        <span class="text-base">📊</span>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <a href="{{ route('ticketing.index') }}"
                       @click="{{ $mobileClose }}"
                       class="{{ $navItem }}
                       {{ request()->routeIs('ticketing.index') || request()->routeIs('ticketing.show') ? $navActive : $navInactive }}">

                        <span class="text-base">🎫</span>
                        <span class="truncate">Daftar Ticket</span>
                    </a>

                </div>
            </div>

            <div class="mx-3 rounded-2xl border border-amber-100 bg-amber-50 p-3 text-xs leading-5 text-amber-700">
                Role maintenance hanya dapat melihat dan mengelola ticket sesuai area.
            </div>

        {{-- ADMIN / SUPERADMIN --}}
        @else

            <div class="mb-6">
                <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Main Menu
                </p>

                <div class="space-y-1">

                    <a href="{{ route('ticketing.dashboard') }}"
                       @click="{{ $mobileClose }}"
                       class="{{ $navItem }}
                       {{ request()->routeIs('ticketing.dashboard') ? $navActive : $navInactive }}">

                        <span class="text-base">📊</span>
                        <span class="truncate">Dashboard</span>
                    </a>

                    <a href="{{ route('ticketing.index') }}"
                       @click="{{ $mobileClose }}"
                       class="{{ $navItem }}
                       {{ request()->routeIs('ticketing.index') || request()->routeIs('ticketing.show') ? $navActive : $navInactive }}">

                        <span class="text-base">🎫</span>
                        <span class="truncate">Daftar Ticket</span>
                    </a>

                </div>
            </div>

            @if($isAdmin)
                <div class="mb-6">

                    <p class="mb-2 px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        Master Data
                    </p>

                    <div class="space-y-1">

                        <a href="{{ route('ticketing.master.area') }}"
                           @click="{{ $mobileClose }}"
                           class="{{ $navItem }}
                           {{ request()->routeIs('ticketing.master.area*') ? $navActive : $navInactive }}">

                            <span class="text-base">🗺️</span>
                            <span class="truncate">Master Area</span>
                        </a>

                        <a href="{{ route('ticketing.master.items') }}"
                           @click="{{ $mobileClose }}"
                           class="{{ $navItem }}
                           {{ request()->routeIs('ticketing.master.items*') ? $navActive : $navInactive }}">

                            <span class="text-base">📦</span>
                            <span class="truncate">Master Item</span>
                        </a>

                        <a href="{{ route('ticketing.master.types') }}"
                           @click="{{ $mobileClose }}"
                           class="{{ $navItem }}
                           {{ request()->routeIs('ticketing.master.types*') ? $navActive : $navInactive }}">

                            <span class="text-base">🏷️</span>
                            <span class="truncate">Master Jenis Ticket</span>
                        </a>

                        <a href="{{ route('ticketing.master.divisions') }}"
                           @click="{{ $mobileClose }}"
                           class="{{ $navItem }}
                           {{ request()->routeIs('ticketing.master.divisions*') ? $navActive : $navInactive }}">

                            <span class="text-base">🏢</span>
                            <span class="truncate">Master Divisi</span>
                        </a>

                        <a href="{{ route('ticketing.master.priorities') }}"
                           @click="{{ $mobileClose }}"
                           class="{{ $navItem }}
                           {{ request()->routeIs('ticketing.master.priorities*') ? $navActive : $navInactive }}">

                            <span class="text-base">🚨</span>
                            <span class="truncate">Master Priority</span>
                        </a>

                        <a href="{{ route('ticketing.master.users') }}"
                           @click="{{ $mobileClose }}"
                           class="{{ $navItem }}
                           {{ request()->routeIs('ticketing.master.users*') ? $navActive : $navInactive }}">

                            <span class="text-base">👥</span>
                            <span class="truncate">Master User</span>
                        </a>

                    </div>

                </div>
            @endif

        @endif

    </div>

    <div class="absolute bottom-0 left-0 right-0 border-t border-slate-200 bg-white p-3 sm:p-4">

        <div class="flex min-w-0 items-center gap-3">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-900 font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>

            <div class="min-w-0 flex-1">
                <div class="truncate text-sm font-bold text-slate-900">
                    {{ auth()->user()->name ?? 'User' }}
                </div>

                <div class="truncate text-xs text-slate-500">
                    {{ auth()->user()->role ?? '-' }}
                </div>
            </div>

        </div>

        <div class="mt-4 grid grid-cols-2 gap-2">

            <a href="{{ route('crew.profile.form') }}"
               @click="{{ $mobileClose }}"
               class="rounded-xl border border-slate-200 bg-slate-50 py-2.5 text-center text-xs font-bold text-slate-700 transition hover:bg-slate-100">
                Profile
            </a>

            <form method="POST" action="{{ route('auth.investor.logout') }}">
                @csrf

                <button
                    type="submit"
                    class="w-full rounded-xl bg-red-500 py-2.5 text-xs font-bold text-white transition hover:bg-red-600">
                    Logout
                </button>
            </form>

        </div>

    </div>

</aside>

<div
    x-show="sidebarOpen"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
    @click="sidebarOpen = false">
</div>

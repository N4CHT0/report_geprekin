<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen:false, profileOpen:false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <title>@yield('title', 'Ticketing')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            color-scheme: light;
        }

        [x-cloak] {
            display: none !important;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-width: 100%;
            max-width: 100%;
            min-height: 100%;
            margin: 0;
            overflow-x: hidden;
            background: #f1f5f9;
            color: #1e293b;
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        input,
        select,
        textarea,
        button {
            font-size: 16px;
        }

        @media (min-width: 640px) {
            input,
            select,
            textarea,
            button {
                font-size: 14px;
            }
        }

        img,
        video,
        canvas,
        svg {
            max-width: 100%;
            height: auto;
        }

        table {
            max-width: 100%;
        }

        .safe-bottom {
            padding-bottom: calc(1rem + env(safe-area-inset-bottom));
        }

        .app-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .app-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .app-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .app-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen w-full bg-slate-100 text-slate-800 antialiased">

@php
    $role = strtolower(auth()->user()->role ?? '');
    $isCrew = $role === 'crew';
@endphp

<div class="min-h-screen w-full overflow-x-hidden bg-slate-100">

    @include('Ticketing.partials.nav')

    <div class="min-h-screen w-full min-w-0 overflow-x-hidden transition-all duration-300 lg:pl-64">

        <header class="sticky top-0 z-30 w-full border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
            <div class="flex min-h-16 w-full items-center justify-between gap-2 px-3 py-2 sm:px-4 lg:px-6">

                <div class="flex min-w-0 flex-1 items-center gap-2 sm:gap-3">

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm transition hover:bg-slate-50 lg:hidden"
                        @click="sidebarOpen = true"
                        aria-label="Buka menu">
                        ☰
                    </button>

                    <div class="min-w-0 flex-1">
                        <h1 class="truncate text-sm font-bold leading-5 text-slate-900 sm:text-base">
                            @yield('title', 'Dashboard')
                        </h1>

                        <p class="hidden truncate text-xs text-slate-500 sm:block">
                            Ticketing Management System
                        </p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center justify-end gap-2 sm:gap-3">

                    @if(!$isCrew)
                        <div class="hidden md:block">
                            <form action="{{ route('ticketing.index') }}" method="GET">
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ request('q') }}"
                                    placeholder="Cari ticket..."
                                    class="h-10 w-52 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100 lg:w-64 xl:w-80">
                            </form>
                        </div>
                    @endif

                    <div class="relative shrink-0">

                        <button
                            type="button"
                            @click="profileOpen = !profileOpen"
                            class="flex max-w-[54px] items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 py-2 shadow-sm transition hover:bg-slate-50 sm:max-w-[240px] sm:px-3">

                            <div class="hidden min-w-0 text-right sm:block">
                                <div class="truncate text-sm font-semibold text-slate-800">
                                    {{ auth()->user()->name ?? 'User' }}
                                </div>

                                <div class="truncate text-xs text-slate-500">
                                    {{ auth()->user()->role ?? '-' }}
                                </div>
                            </div>

                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white sm:h-10 sm:w-10">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                        </button>

                        <div
                            x-show="profileOpen"
                            x-cloak
                            x-transition
                            @click.away="profileOpen = false"
                            class="absolute right-0 mt-3 w-[calc(100vw-1.5rem)] max-w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl sm:w-72">

                            <div class="border-b border-slate-100 p-4">
                                <div class="truncate text-sm font-bold text-slate-900">
                                    {{ auth()->user()->name ?? 'User' }}
                                </div>

                                <div class="mt-1 truncate text-xs text-slate-500">
                                    {{ auth()->user()->email ?? '-' }}
                                </div>
                            </div>

                            <div class="p-2">

                                <a href="{{ route('crew.profile.form') }}"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                                    <span>👤</span>
                                    <span>Profile</span>
                                </a>

                                @if(!$isCrew)
                                    <a href="{{ route('ticketing.dashboard') }}"
                                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                                        <span>📊</span>
                                        <span>Dashboard</span>
                                    </a>
                                @endif

                                <form method="POST" action="{{ route('auth.investor.logout') }}">
                                    @csrf

                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50">
                                        <span>🚪</span>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$isCrew)
                <div class="w-full border-t border-slate-100 px-3 pb-3 md:hidden">
                    <form action="{{ route('ticketing.index') }}" method="GET">
                        <input
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Cari ticket..."
                            class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100">
                    </form>
                </div>
            @endif
        </header>

        <main class="safe-bottom w-full min-w-0 overflow-x-hidden px-3 py-4 sm:px-4 lg:px-6 lg:py-6">

            @if(session('success'))
                <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <div class="mb-2 font-bold">
                        Ada kesalahan:
                    </div>

                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="w-full min-w-0">
                @yield('content')
            </div>

        </main>
    </div>
</div>

@stack('scripts')

</body>
</html>

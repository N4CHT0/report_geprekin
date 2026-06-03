<!DOCTYPE html>
<html lang="id" x-data="{ sidebarOpen: false, desktopSidebarOpen: true, profileOpen: false, darkMode: localStorage.getItem('darkMode') === 'true'}" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Dashboard Geprekin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            // ... (bisa tambah konfigurasi warna lain di sini)
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Palet Warna Kustom dari Referensi */
        :root {
            --bg-main: #EAF1F6;
            /* Biru muda cerah untuk background kanan */
            --bg-sidebar: #2A435D;
            /* Biru dongker/Navy untuk sidebar kiri */
        }

        body {
            background-color: var(--bg-main);
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        /* Trik CSS untuk Menu Aktif Melengkung ala Dashboard Modern */
        .nav-item-active {
            background-color: var(--bg-main);
            color: var(--bg-sidebar) !important;
            border-radius: 1.5rem 0 0 1.5rem;
            /* Melengkung di kiri saja */
            position: relative;
            font-weight: 700;
        }

        /* Lengkungan atas menyatu */
        .nav-item-active::before {
            content: '';
            position: absolute;
            top: -20px;
            right: 0;
            width: 20px;
            height: 20px;
            background-color: transparent;
            border-bottom-right-radius: 20px;
            box-shadow: 0 10px 0 0 var(--bg-main);
            pointer-events: none;
        }

        /* Lengkungan bawah menyatu */
        .nav-item-active::after {
            content: '';
            position: absolute;
            bottom: -20px;
            right: 0;
            width: 20px;
            height: 20px;
            background-color: transparent;
            border-top-right-radius: 20px;
            box-shadow: 0 -10px 0 0 var(--bg-main);
            pointer-events: none;
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="text-slate-800 antialiased flex h-screen overflow-hidden selection:bg-blue-200">

    @include('Room.partials.sidebar')

    <div class="flex-1 flex flex-col h-screen overflow-hidden transition-all duration-300 relative">

        <header class="flex items-center justify-between px-6 py-4 lg:py-6">
            <div class="flex items-center gap-4">

                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl bg-white shadow-sm text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <button @click="desktopSidebarOpen = !desktopSidebarOpen" class="hidden lg:block p-2 rounded-xl bg-white shadow-sm text-slate-600 hover:text-[#2A435D] transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <h1 class="text-2xl font-extrabold text-[#2A435D] hidden sm:block">
                    Welcome {{ explode(' ', auth()->user()->name ?? 'Nirmal')[0] }} !
                </h1>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center bg-white rounded-full px-5 py-2.5 shadow-sm border border-slate-100">
                    <input type="text" placeholder="Search" class="bg-transparent border-none outline-none text-sm w-48 text-slate-600 placeholder-slate-400">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <!-- <button class="p-2.5 bg-white rounded-full shadow-sm text-slate-500 hover:text-[#2A435D] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                </button> -->
                <button @click="darkMode = !darkMode"
                    class="p-2.5 bg-white dark:bg-slate-800 rounded-full shadow-sm text-slate-500 hover:text-[#2A435D] dark:text-slate-400 dark:hover:text-amber-400 transition relative overflow-hidden">

                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>

                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                <!-- <button class="p-2.5 bg-white rounded-full shadow-sm text-slate-500 hover:text-[#2A435D] transition relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                </button> -->
                @php
                // Menarik 5 notifikasi terbaru untuk user yang sedang login
                $notifications = \Illuminate\Support\Facades\DB::table('hospace_notifications')
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

                // Menghitung jumlah yang belum dibaca
                $unreadCount = $notifications->where('is_read', 0)->count();
                @endphp

                <div x-data="{ openNotif: false }" class="relative inline-block text-left">
                    <button @click="openNotif = !openNotif" @click.away="openNotif = false" type="button" class="relative flex items-center justify-center w-10 h-10 bg-white rounded-full shadow-sm text-slate-500 hover:text-[#2A435D] hover:shadow-md transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>

                        @if($unreadCount > 0)
                        <span class="absolute top-2 right-2 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500 border-2 border-white"></span>
                        </span>
                        @endif
                    </button>

                    <div x-show="openNotif" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                        class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-100 z-50 overflow-hidden origin-top-right">

                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-slate-800">Notifikasi Anda</h3>
                            @if($unreadCount > 0)
                            <span class="text-[10px] font-bold bg-[#F59E0B] text-white px-2 py-0.5 rounded-full shadow-sm">{{ $unreadCount }} Baru</span>
                            @endif
                        </div>

                        <div class="max-h-[350px] overflow-y-auto divide-y divide-slate-50">
                            @forelse($notifications as $notif)
                            <div class="p-4 hover:bg-slate-50 transition-colors {{ $notif->is_read == 0 ? 'bg-blue-50/20' : '' }}">
                                <div class="flex gap-3">
                                    <div class="shrink-0 mt-0.5">
                                        @if(str_contains($notif->title, 'Disetujui'))
                                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        @else
                                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600 shadow-sm border border-red-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <h4 class="text-xs font-bold text-slate-800 mb-1">{{ $notif->title }}</h4>
                                        <p class="text-[11px] text-slate-600 leading-relaxed">{{ $notif->message }}</p>
                                        <div class="text-[9px] text-slate-400 mt-2 font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="p-8 text-center text-slate-400 flex flex-col items-center">
                                <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                                <p class="text-xs font-medium">Belum ada notifikasi baru.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto px-4 pb-6 sm:px-6">
            @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-white px-4 py-4 text-sm font-medium text-emerald-700 shadow-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-5 rounded-2xl border border-red-200 bg-white p-4 text-sm font-medium text-red-700 shadow-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
            @endif

            <div class="w-full min-w-0">
                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>
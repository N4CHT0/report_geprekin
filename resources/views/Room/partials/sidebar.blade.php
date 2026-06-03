<div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-40 lg:hidden">
    <div x-transition.opacity class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="sidebarOpen = false"></div>
</div>

<aside
    :class="{ 
        '-translate-x-full': !sidebarOpen, 
        'translate-x-0': sidebarOpen,
        'w-64': desktopSidebarOpen,  'w-20': !desktopSidebarOpen
    }"
    class="fixed lg:static inset-y-0 left-0 z-50 flex flex-col bg-[#2A435D] text-slate-300 transition-all duration-300 h-screen shrink-0 overflow-y-auto hide-scrollbar transform lg:transform-none shadow-2xl lg:shadow-none">

    <div class="lg:hidden absolute top-4 right-4 z-50">
        <button @click="sidebarOpen = false" class="text-slate-300 hover:text-white bg-[#3D5B7A] p-1.5 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="flex items-center justify-center pt-6 pb-3 mt-4 lg:mt-0">
        <div class="bg-white px-3 py-2 rounded-xl shadow-md mx-4 flex items-center justify-center min-h-[3rem]">
            <img x-show="desktopSidebarOpen" src="{{ asset('img/logo-hd.png') }}" alt="Geprekin Aja" class="h-8 w-auto object-contain">
            <span x-show="!desktopSidebarOpen" x-cloak class="text-lg font-black text-red-600 tracking-tighter whitespace-nowrap">GA</span>
        </div>
    </div>

    <a href="{{ route('hospace.profile') }}" title="Lihat Profil Saya"
        :class="desktopSidebarOpen ? 'flex-col mx-5 border-b border-slate-500/30 pb-5 mb-3' : 'flex-col mx-2 pb-4 mb-2 border-b border-slate-500/30'"
        class="flex items-center justify-center mt-4 transition-all hover:bg-slate-700/30 rounded-2xl cursor-pointer">

        <div :class="desktopSidebarOpen ? 'w-16 h-16 text-xl mt-3' : 'w-10 h-10 text-base mt-2'" class="rounded-full border-[3px] border-[#3D5B7A] shadow-md mb-2 flex items-center justify-center bg-white text-[#2A435D] font-extrabold transition-all group-hover:scale-105">
            {{ strtoupper(substr(auth()->user()->name ?? 'N', 0, 1)) }}
        </div>

        <div x-show="desktopSidebarOpen" class="text-center pb-2">
            <h2 class="text-white font-bold text-base tracking-wide whitespace-nowrap">{{ auth()->user()->name ?? 'Karyawan' }}</h2>
            <p class="text-[11px] text-slate-400 whitespace-nowrap">{{ auth()->user()->email ?? 'admin@geprekin.com' }}</p>
        </div>
    </a>

    <nav class="flex-1 flex flex-col gap-1" :class="desktopSidebarOpen ? 'pl-5' : 'px-2'">

        <div x-show="desktopSidebarOpen" class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 mt-1 whitespace-nowrap">Menu Utama</div>

        <a href="{{ route('dashboard') }}" title="Dashboard"
            class="group flex items-center gap-3 py-2.5 px-4 transition-all overflow-hidden {{ request()->routeIs('dashboard') ? 'nav-item-active' : 'hover:bg-[#3D5B7A] hover:text-white rounded-l-xl' }}"
            :class="!desktopSidebarOpen ? 'rounded-xl justify-center px-0' : ''">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
            </svg>
            <span x-show="desktopSidebarOpen" class="text-[13px] font-medium whitespace-nowrap">Dashboard</span>
        </a>

        <a href="{{ route('reservations.index') }}" title="Reservasi Ruang"
            class="group flex items-center gap-3 py-2.5 px-4 transition-all overflow-hidden {{ request()->routeIs('reservations.index') ? 'nav-item-active' : 'hover:bg-[#3D5B7A] hover:text-white rounded-l-xl' }}"
            :class="!desktopSidebarOpen ? 'rounded-xl justify-center px-0' : ''">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span x-show="desktopSidebarOpen" class="text-[13px] font-medium whitespace-nowrap">Reservasi Ruang</span>
        </a>

        <a href="{{ route('reservations.history') }}" title="Riwayat Peminjaman"
            class="group flex items-center gap-3 py-2.5 px-4 transition-all overflow-hidden {{ request()->routeIs('reservations.history') ? 'nav-item-active' : 'hover:bg-[#3D5B7A] hover:text-white rounded-l-xl' }}"
            :class="!desktopSidebarOpen ? 'rounded-xl justify-center px-0' : ''">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <span x-show="desktopSidebarOpen" class="text-[13px] font-medium whitespace-nowrap">Riwayat</span>
        </a>


        @if(auth()->user()->role !== 'userhospace')
        <div x-show="desktopSidebarOpen" class="my-2 border-t border-slate-500/30 mr-5"></div>
        <div x-show="desktopSidebarOpen" class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1 whitespace-nowrap">Sistem Admin</div>

        <a href="{{ route('rooms.index') }}" title="Master Ruangan"
            class="group flex items-center gap-3 py-2.5 px-4 transition-all overflow-hidden {{ request()->routeIs('rooms.*') ? 'nav-item-active' : 'hover:bg-[#3D5B7A] hover:text-white rounded-l-xl' }}"
            :class="!desktopSidebarOpen ? 'rounded-xl justify-center px-0' : ''">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span x-show="desktopSidebarOpen" class="text-[13px] font-medium whitespace-nowrap">Master Ruangan</span>
        </a>

        <a href="{{ route('time_slots.index') }}" title="Master Waktu"
            class="group flex items-center gap-3 py-2.5 px-4 transition-all overflow-hidden {{ request()->routeIs('time_slots.*') ? 'nav-item-active' : 'hover:bg-[#3D5B7A] hover:text-white rounded-l-xl' }}"
            :class="!desktopSidebarOpen ? 'rounded-xl justify-center px-0' : ''">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-show="desktopSidebarOpen" class="text-[13px] font-medium whitespace-nowrap">Master Waktu</span>
        </a>

        <a href="{{ route('admin.divisions.index') }}" title="Master Divisi"
            class="group flex items-center gap-3 py-2.5 px-4 transition-all overflow-hidden {{ request()->routeIs('admin.divisions.*') ? 'nav-item-active' : 'hover:bg-[#3D5B7A] hover:text-white rounded-l-xl' }}"
            :class="!desktopSidebarOpen ? 'rounded-xl justify-center px-0' : ''">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <span x-show="desktopSidebarOpen" class="text-[13px] font-medium whitespace-nowrap">Master Divisi</span>
        </a>

        <a href="{{ route('admin.approvals.index') }}" title="Persetujuan Ruangan"
            class="group flex items-center gap-3 py-2.5 px-4 transition-all overflow-hidden relative {{ request()->routeIs('admin.approvals.*') ? 'nav-item-active' : 'hover:bg-[#3D5B7A] hover:text-white rounded-l-xl' }}"
            :class="!desktopSidebarOpen ? 'rounded-xl justify-center px-0' : ''">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-show="desktopSidebarOpen" class="text-[13px] font-medium whitespace-nowrap">Persetujuan</span>

            @php
            $pendingCount = DB::table('reservations')->where('status', 'Pending')->count();
            @endphp
            @if($pendingCount > 0)
            <span x-show="desktopSidebarOpen" class="ml-auto inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[9px] font-bold text-white shadow-sm">
                {{ $pendingCount }}
            </span>
            <span x-show="!desktopSidebarOpen" x-cloak class="absolute top-2 right-2 w-2 h-2 bg-amber-500 rounded-full border border-[#2A435D]"></span>
            @endif
        </a>

        <a href="{{ route('admin.maintenance.index') }}" title="Blokir / Maintenance"
            class="group flex items-center gap-3 py-2.5 px-4 transition-all overflow-hidden {{ request()->routeIs('admin.maintenance.*') ? 'nav-item-active' : 'hover:bg-[#3D5B7A] hover:text-white rounded-l-xl' }}"
            :class="!desktopSidebarOpen ? 'rounded-xl justify-center px-0' : ''">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span x-show="desktopSidebarOpen" class="text-[13px] font-medium whitespace-nowrap">Pengaturan</span>
        </a>
        @endif
    </nav>

    <div class="p-5 mb-1 mt-auto border-t border-slate-500/20">
        <form action="/hospace/logout" method="POST">
            @csrf
            <button type="submit" :class="desktopSidebarOpen ? 'w-full px-4 justify-start' : 'justify-center'" class="flex items-center gap-3 text-slate-400 hover:text-red-400 transition-colors">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span x-show="desktopSidebarOpen" class="text-[13px] font-bold whitespace-nowrap">Logout</span>
            </button>
        </form>
    </div>
</aside>
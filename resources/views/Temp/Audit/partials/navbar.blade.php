<header id="topbar">
    <div style="display:flex; align-items:center; gap:12px; flex:1; min-width:0;">
        <button class="topbar-btn" @click="toggle()" type="button"><i class="bi bi-layout-sidebar"></i></button>
        @hasSection('breadcrumb')
            <div style="font-size:12.5px; color:var(--text-muted); font-weight:500;">@yield('breadcrumb')</div>
        @endif
    </div>

    <div style="display:flex; align-items:center; gap:8px;">
        <button class="topbar-btn" x-data="{ full: false }" @click="full=!full; full ? document.documentElement.requestFullscreen() : document.exitFullscreen()" type="button">
            <i class="bi" :class="full ? 'bi-fullscreen-exit' : 'bi-arrows-fullscreen'"></i>
        </button>

        <div style="position:relative;" x-data="{ open: false }" @click.outside="open = false">
            <button class="user-pill" @click="open = !open" type="button">
                <img src="{{ asset('../img/logo2.jpg') }}" alt="">
                <span class="user-pill-name">{{ Auth::user()->name }}</span>
                <i class="bi bi-chevron-down" style="font-size:10px; color:var(--text-muted);"></i>
            </button>

            <div class="dd-panel" x-show="open" x-transition.origin.top.right x-cloak>
                <div class="dd-header">
                    <img src="{{ asset('../img/logo2.jpg') }}" alt="" style="width:36px; height:36px; border-radius:8px;">
                    <div>
                        <div class="dd-name">{{ Auth::user()->name }}</div>
                        <div style="font-size:12px; color:var(--text-muted);">{{ Auth::user()->email }}</div>
                    </div>
                </div>
                <div class="dd-items">
                    <a href="{{ route('crew.profile.form') }}" class="dd-item"><i class="bi bi-person"></i> Profil Saya</a>
                    <div style="height:1px; background:var(--border-subtle); margin:4px 6px;"></div>
                    <form method="POST" action="{{ route('auth.investor.logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="dd-item" style="color:var(--danger)"><i class="bi bi-box-arrow-right"></i> Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
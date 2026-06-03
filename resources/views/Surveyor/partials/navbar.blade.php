{{-- resources/views/Surveyor/partials/navbar.blade.php --}}
<header id="topbar">
    <div class="topbar-left">
        <button type="button" class="topbar-btn" x-on:click="toggleSidebar()">
            <i class="bi bi-layout-sidebar-inset"></i>
        </button>

        <div class="topbar-title">
            <h1>@yield('title', 'Surveyor Site Score')</h1>
            <p>@yield('breadcrumb', 'Surveyor / Dashboard')</p>
        </div>
    </div>

    <div class="topbar-right">
        <div class="topbar-search">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search lokasi, surveyor, report...">
        </div>

        <a href="{{ route('investor.surveyor.video-detection.index') }}" class="topbar-btn" title="Video Detection">
            <i class="bi bi-camera-video"></i>
        </a>

        <a href="{{ route('investor.surveyor.site-score.map') }}" class="topbar-btn" title="Peta Titik">
            <i class="bi bi-geo-alt"></i>
        </a>

        <div class="user-pill">
            <img src="{{ asset('img/logo2.jpg') }}" alt="User">
            <span>
                <b class="user-pill-name">{{ auth()->user()->name ?? 'GPRSuperadmin' }}</b>
                <b class="user-pill-role">{{ auth()->user()->role ?? 'Superadmin' }}</b>
            </span>
        </div>
    </div>
</header>

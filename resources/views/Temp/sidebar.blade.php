<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="#" class="brand-link">
            <img src="{{ asset('../img/logo2.jpg') }}" class="brand-image opacity-75 shadow" alt="Logo">
            <span class="brand-text fw-light">Geprekin V.1.1</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" data-accordion="true">

                <li class="nav-header">Dashboard</li>

                <li class="nav-item">
                    <a href="{{ route('investor.sales.dashboard') }}"
                       class="nav-link {{ request()->routeIs('investor.sales.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard Utama</p>
                    </a>
                </li>

                @if(Route::has('investor.sales.dashboardGO'))
                    <li class="nav-item">
                        <a href="{{ route('investor.sales.dashboardGO') }}"
                           class="nav-link {{ request()->routeIs('investor.sales.dashboardGO') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard GO</p>
                        </a>
                    </li>
                @endif

                <li class="nav-item">
                    <a href="{{ route('monitoring.sales') }}"
                       class="nav-link {{ request()->routeIs('monitoring.sales') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-graph-up-arrow"></i>
                        <p>Monitoring Sales</p>
                    </a>
                </li>

                <li class="nav-header">Laporan</li>

                @if(Route::has('investor.laporan.perbulan'))
                    <li class="nav-item">
                        <a href="{{ route('investor.laporan.perbulan') }}" class="nav-link">
                            <i class="nav-icon bi bi-calendar-month"></i>
                            <p>Penjualan per Bulan</p>
                        </a>
                    </li>
                @endif

                @if(Route::has('investor.laporan.menu'))
                    <li class="nav-item">
                        <a href="{{ route('investor.laporan.menu') }}" class="nav-link">
                            <i class="nav-icon bi bi-grid-3x3-gap"></i>
                            <p>Penjualan Menu</p>
                        </a>
                    </li>
                @endif

                <li class="nav-header">Master Data</li>

                @if(Route::has('investor.outlet.master'))
                    <li class="nav-item">
                        <a href="{{ route('investor.outlet.master') }}" class="nav-link">
                            <i class="nav-icon bi bi-shop"></i>
                            <p>Outlet</p>
                        </a>
                    </li>
                @endif

            </ul>
        </nav>
    </div>
</aside>
<nav class="app-header navbar navbar-expand bg-white border-bottom">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list fs-4"></i>
                </a>
            </li>

            <li class="nav-item d-none d-md-block ms-2">
                <div class="fw-bold text-primary">Monitoring Sales</div>
                <div class="small text-muted">{{ $tanggalAwal ?? '-' }} s/d {{ $tanggalAkhir ?? '-' }}</div>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center gap-2">
            <li class="nav-item">
                <button type="button" @click="filterOpen=!filterOpen" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
            </li>

            <li class="nav-item">
                <button type="button" @click="notifOpen=true" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
                    <i class="bi bi-bell-fill me-1"></i> {{ count($notifikasiTurunSales ?? []) }}
                </button>
            </li>

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-black">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}
                    </div>
                    <span class="d-none d-md-inline fw-semibold">{{ auth()->user()->name ?? 'Admin' }}</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <li class="user-header text-bg-primary">
                        <p>
                            {{ auth()->user()->name ?? 'Admin' }} - {{ auth()->user()->role ?? '-' }}
                            <small>Geprekin Dashboard</small>
                        </p>
                    </li>
                    <li class="user-footer">
                        @if(Route::has('investor.profile.edit'))
                            <a href="{{ route('investor.profile.edit', auth()->user()->id) }}" class="btn btn-default btn-flat">Profile</a>
                        @endif
                        @if(Route::has('auth.investor.logout'))
                            <form action="{{ route('auth.investor.logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-default btn-flat float-end">Sign out</button>
                            </form>
                        @endif
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
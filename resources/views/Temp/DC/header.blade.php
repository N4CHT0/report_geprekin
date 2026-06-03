<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Geprekin - Dashboard DC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="{{ asset('temp/lte/dist/css/adminlte.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        /* Mengatur custom warna background utama aplikasi kanan (Sky Light Blue) */
        body {
            background-color: #f4f7fe !important;
        }
        
        .app-main {
            background-color: #f4f7fe !important;
        }

        /* CUSTOM SIDEBAR GAYA PREMIUM (Warna gelap pekat, bukan abu-abu lte standar) */
        .bg-custom-dark {
            background-color: #111827 !important; /* Slate / Navy pekat premium */
        }

        .sidebar-brand {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            background-color: #0b0f19 !important;
        }

        /* GAYA MENU AKTIF KAPSUL INDIGO/BLUE MELAYANG SESUAI GAMBAR REFERENSI UTAMA */
        .sidebar-menu .nav-link.active {
            background: linear-gradient(135deg, #4318ff 0%, #3b82f6 100%) !important;
            color: #ffffff !important;
            border-radius: 0.75rem !important; /* Kapsul rounded melayang */
            font-weight: 700 !important;
            box-shadow: 0 10px 20px rgba(67, 24, 255, 0.25) !important;
        }

        /* Merapikan jarak item menu agar seimbang */
        .sidebar-menu .nav-item {
            padding: 0 12px;
            margin-bottom: 4px;
        }

        .sidebar-menu .nav-link {
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }

        .sidebar-menu .nav-link:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #ffffff !important;
        }

        /* Memperbaiki posisi teks navbar agar tebal dan elegan */
        .navbar-brand-text {
            font-weight: 800;
            color: #1e293b;
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="auditSidebarToggle" role="button" aria-label="Toggle sidebar">
                            <i class="bi bi-list fs-4"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-sm-flex align-items-center ps-2">
                        <span class="navbar-brand-text text-dark">
                            Welcome {{ explode(' ', auth()->user()->name ?? 'User')[0] }} !
                        </span>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img src="{{ asset('../img/logo2.jpg') }}" class="user-image rounded-circle shadow" alt="User Image">
                            <span class="d-none d-md-inline text-secondary font-weight-bold">{{ Auth::user()->name }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-sm">
                            <li class="user-header text-bg-primary text-center py-3" style="background-color: #111827 !important;">
                                <img src="{{ asset('../img/logo2.jpg') }}" class="rounded-circle shadow mb-2" alt="User Image">
                                <p class="mb-0">
                                    {{ Auth::user()->name }} – {{ Auth::user()->role }}
                                    <br>
                                    <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                                </p>
                            </li>

                            <li class="user-footer p-3 bg-white">
                                <a href="{{ route('investor.profile.edit', auth()->user()->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    Profile
                                </a>

                                <form action="{{ route('auditDashboard.auditLogout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-flat btn-light text-danger float-end rounded-pill px-3">Sign out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <aside class="app-sidebar bg-custom-dark shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="#" class="brand-link">
                    <img src="{{ asset('../img/logo2.jpg') }}" alt="Audit Logo" class="brand-image opacity-75 shadow">
                    <span class="brand-text font-weight-bold text-white">Geprekin SCM</span>
                </a>
            </div>

            <div class="sidebar-wrapper">
                <nav class="mt-3">
                    <div class="sidebar-search px-3 mb-2">
                        <div class="input-group">
                            <input type="text" id="sidebarSearchInput" class="form-control form-control-sm bg-dark border-secondary text-white" placeholder="Cari menu...">
                        </div>
                    </div>

                    <ul class="nav sidebar-menu flex-column" id="navigation" role="navigation" aria-label="Main navigation">
                        <li class="nav-header text-muted small tracking-wider pl-3">DASHBOARD</li>

                        <li class="nav-item">
                            <a href="{{ route('dashboard.scm.dc') }}"
                               class="nav-link {{ request()->routeIs('dashboard.scm.dc') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-speedometer2"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('dashboard.recap') }}"
                               class="nav-link {{ request()->routeIs('dashboard.recap') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-box-seam"></i>
                                <p>Monitoring Stock</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="{{ route('purchasing.dashboardSCM') }}"
                               class="nav-link {{ request()->routeIs('purchasing.dashboardSCM') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-cart-check"></i>
                                <p>Purchase Order</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('scm.pengiriman.index') }}"
                               class="nav-link {{ request()->routeIs('scm.pengiriman.index') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-clipboard-check"></i>
                                <p>Approved List</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <main class="app-main py-4">
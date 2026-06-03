{{-- resources/views/Temp/Investor/header.blade.php --}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Investor Console') - Geprekin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        function investorShell() {
            return {
                init() {
                    const saved = localStorage.getItem('investorSidebarMini');

                    if (saved === '1') {
                        document.body.classList.add('sidebar-mini-investor');
                    }
                },

                toggleSidebar() {
                    if (window.innerWidth < 992) {
                        document.body.classList.toggle('sidebar-mobile-open');
                        return;
                    }

                    document.body.classList.toggle('sidebar-mini-investor');

                    localStorage.setItem(
                        'investorSidebarMini',
                        document.body.classList.contains('sidebar-mini-investor') ? '1' : '0'
                    );
                }
            }
        }
    </script>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles
    @stack('styles')

    <style>
        [x-cloak] { display: none !important; }

        :root {
            --shell-sidebar: 264px;
            --shell-sidebar-mini: 76px;
            --shell-topbar: 64px;
            --shell-bg: #f3f6fb;
            --shell-line: rgba(15, 23, 42, .10);
            --shell-text: #0f172a;
            --shell-muted: #64748b;
            --shell-blue: #2563eb;
        }

        * { box-sizing: border-box; }

        html,
        body {
            min-height: 100%;
            background: var(--shell-bg);
            color: var(--shell-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            margin: 0;
            overflow-x: hidden;
        }

        #app-shell {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, .08), transparent 32rem),
                var(--shell-bg);
        }

        #sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--shell-sidebar);
            z-index: 1050;
            background: #020617;
            color: #e5e7eb;
            border-right: 1px solid rgba(255, 255, 255, .08);
            display: flex;
            flex-direction: column;
            transition: width .22s ease, transform .22s ease;
        }

        .sidebar-brand {
            height: var(--shell-topbar);
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .sidebar-brand img {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            object-fit: cover;
            background: #fff;
        }

        .brand-title {
            font-size: 14px;
            font-weight: 900;
            line-height: 1.1;
        }

        .brand-subtitle {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 700;
            color: #94a3b8;
        }

        .sidebar-search {
            padding: 12px;
        }

        .sidebar-search-box {
            height: 42px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, .22);
            background: rgba(15, 23, 42, .9);
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 12px;
            color: #94a3b8;
        }

        .sidebar-search-box input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            color: #e5e7eb;
            font-size: 13px;
            font-weight: 650;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 4px 10px 14px;
        }

        .sidebar-nav::-webkit-scrollbar { width: 8px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(148, 163, 184, .35); border-radius: 999px; }

        .nav-section {
            margin: 18px 10px 8px;
            color: #64748b;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .side-link,
        .side-toggle {
            width: 100%;
            min-height: 42px;
            border: 0;
            border-radius: 12px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            gap: 11px;
            color: #cbd5e1;
            background: transparent;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            transition: .16s ease;
        }

        .side-link:hover,
        .side-toggle:hover {
            color: #fff;
            background: rgba(148, 163, 184, .12);
        }

        .side-link.active {
            background: var(--shell-blue);
            color: #fff;
            box-shadow: 0 12px 28px rgba(37, 99, 235, .28);
        }

        .side-link i,
        .side-toggle i:first-child {
            width: 18px;
            text-align: center;
            font-size: 15px;
        }

        .side-chevron {
            margin-left: auto;
            font-size: 12px;
            transition: transform .18s ease;
            color: #94a3b8;
        }

        .side-child {
            margin: 4px 0 6px 38px;
            padding-left: 10px;
            border-left: 1px solid rgba(148, 163, 184, .20);
            display: grid;
            gap: 2px;
        }

        .side-child a {
            padding: 8px 10px;
            border-radius: 9px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 750;
        }

        .side-child a:hover {
            color: #fff;
            background: rgba(148, 163, 184, .10);
        }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .system-pill {
            min-height: 42px;
            border-radius: 14px;
            background: rgba(16, 185, 129, .10);
            border: 1px solid rgba(16, 185, 129, .22);
            color: #a7f3d0;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 900;
        }

        #main-area {
            min-height: 100vh;
            margin-left: var(--shell-sidebar);
            transition: margin-left .22s ease;
        }

        #topbar {
            position: sticky;
            top: 0;
            z-index: 1040;
            height: var(--shell-topbar);
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid var(--shell-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 0 18px;
        }

        .topbar-left,
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .topbar-title h1 {
            margin: 0;
            font-size: 15px;
            font-weight: 950;
            color: #0f172a;
            line-height: 1.1;
        }

        .topbar-title p {
            margin: 3px 0 0;
            font-size: 11px;
            font-weight: 750;
            color: #64748b;
        }

        .topbar-btn {
            width: 38px;
            height: 38px;
            border: 1px solid var(--shell-line);
            border-radius: 12px;
            background: #fff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: .16s ease;
        }

        .topbar-btn:hover {
            background: #f8fafc;
            color: var(--shell-blue);
            border-color: rgba(37, 99, 235, .22);
        }

        .topbar-search {
            width: min(360px, 34vw);
            height: 40px;
            border-radius: 999px;
            border: 1px solid var(--shell-line);
            background: #fff;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 13px;
            color: #94a3b8;
        }

        .topbar-search input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            font-size: 13px;
            font-weight: 650;
            color: #0f172a;
        }

        .notif-badge {
            position: absolute;
            top: -7px;
            right: -6px;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            background: #dc2626;
            color: #fff;
            font-size: 10px;
            font-weight: 950;
            display: grid;
            place-items: center;
            padding: 0 5px;
            border: 2px solid #fff;
        }

        .notif-badge.success {
            background: #059669;
        }

        .user-wrap {
            position: relative;
        }

        .user-pill {
            height: 42px;
            border: 1px solid var(--shell-line);
            background: #fff;
            border-radius: 999px;
            padding: 4px 12px 4px 5px;
            display: flex;
            align-items: center;
            gap: 9px;
            color: #0f172a;
        }

        .user-pill img {
            width: 32px;
            height: 32px;
            border-radius: 999px;
            object-fit: cover;
        }

        .user-pill span {
            display: grid;
            text-align: left;
            line-height: 1.05;
        }

        .user-pill-name {
            font-size: 13px;
            font-weight: 950;
        }

        .user-pill-role {
            margin-top: 3px;
            font-size: 10px;
            font-weight: 850;
            color: #64748b;
            text-transform: uppercase;
        }

        .dd-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: 250px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid var(--shell-line);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .16);
            overflow: hidden;
            z-index: 2000;
        }

        .dd-header {
            padding: 14px;
            display: flex;
            gap: 10px;
            align-items: center;
            border-bottom: 1px solid var(--shell-line);
            background: #f8fafc;
        }

        .dd-header img {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            object-fit: cover;
        }

        .dd-name {
            font-size: 13px;
            font-weight: 950;
            color: #0f172a;
        }

        .dd-role {
            margin-top: 2px;
            font-size: 11px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
        }

        .dd-items {
            padding: 8px;
        }

        .dd-item {
            width: 100%;
            border: 0;
            background: transparent;
            border-radius: 11px;
            padding: 10px 11px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #334155;
            text-decoration: none;
            font-size: 13px;
            font-weight: 800;
            text-align: left;
        }

        .dd-item:hover {
            background: #f1f5f9;
            color: var(--shell-blue);
        }

        #page-content {
            padding: 18px;
        }

        body.sidebar-mini-investor #sidebar {
            width: var(--shell-sidebar-mini);
        }

        body.sidebar-mini-investor #main-area {
            margin-left: var(--shell-sidebar-mini);
        }

        body.sidebar-mini-investor .brand-copy,
        body.sidebar-mini-investor .sidebar-search,
        body.sidebar-mini-investor .nav-section,
        body.sidebar-mini-investor .side-link span,
        body.sidebar-mini-investor .side-toggle span,
        body.sidebar-mini-investor .side-chevron,
        body.sidebar-mini-investor .side-child,
        body.sidebar-mini-investor .system-pill span {
            display: none !important;
        }

        body.sidebar-mini-investor .sidebar-brand {
            justify-content: center;
        }

        body.sidebar-mini-investor .side-link,
        body.sidebar-mini-investor .side-toggle {
            justify-content: center;
            padding: 0;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
                width: var(--shell-sidebar);
            }

            body.sidebar-mobile-open #sidebar {
                transform: translateX(0);
            }

            #main-area,
            body.sidebar-mini-investor #main-area {
                margin-left: 0;
            }

            #page-content {
                padding: 12px;
            }

            .topbar-search {
                display: none;
            }

            .user-pill span,
            .topbar-title p {
                display: none;
            }

            .user-pill {
                padding-right: 5px;
            }
        }
    </style>
</head>

<body>
<div id="app-shell" x-data="investorShell()" x-init="init()">
    @include('Temp.Investor.partials.sidebar')

    <div id="main-area">
        @include('Temp.Investor.partials.navbar')

        <main id="page-content">
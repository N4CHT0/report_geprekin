{{-- resources/views/Surveyor/layouts/header.blade.php --}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Surveyor Site Score') - Geprekin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        function surveyorShell() {
            return {
                init() {
                    const saved = localStorage.getItem('surveyorSidebarMini');
                    if (saved === '1') document.body.classList.add('sidebar-mini-surveyor');
                },
                toggleSidebar() {
                    if (window.innerWidth < 992) {
                        document.body.classList.toggle('sidebar-mobile-open');
                        return;
                    }
                    document.body.classList.toggle('sidebar-mini-surveyor');
                    localStorage.setItem(
                        'surveyorSidebarMini',
                        document.body.classList.contains('sidebar-mini-surveyor') ? '1' : '0'
                    );
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')

    <style>
        [x-cloak] { display: none !important; }

        :root {
            --sv-sidebar: 274px;
            --sv-sidebar-mini: 78px;
            --sv-topbar: 66px;
            --sv-bg: #f3f6fb;
            --sv-panel: #ffffff;
            --sv-line: #dbe3ef;
            --sv-text: #0f172a;
            --sv-muted: #64748b;
            --sv-primary: #2563eb;
            --sv-primary-dark: #1d4ed8;
            --sv-green: #16a34a;
            --sv-warning: #d97706;
            --sv-danger: #dc2626;
        }

        * { box-sizing: border-box; }

        html, body {
            min-height: 100%;
            margin: 0;
            background: var(--sv-bg);
            color: var(--sv-text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            overflow-x: hidden;
        }

        #app-shell {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(37,99,235,.08), transparent 34rem),
                radial-gradient(circle at top left, rgba(22,163,74,.06), transparent 28rem),
                var(--sv-bg);
        }

        #sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 1050;
            width: var(--sv-sidebar);
            background: #07111f;
            color: #e5e7eb;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255,255,255,.08);
            transition: width .22s ease, transform .22s ease;
        }

        .sidebar-brand {
            min-height: var(--sv-topbar);
            padding: 13px 14px;
            display: flex;
            align-items: center;
            gap: 11px;
            color: #fff;
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .sidebar-brand img {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            object-fit: cover;
            background: #fff;
        }

        .brand-title {
            font-size: 14px;
            font-weight: 950;
            line-height: 1.1;
        }

        .brand-subtitle {
            margin-top: 3px;
            color: #94a3b8;
            font-size: 11px;
            font-weight: 750;
        }

        .sidebar-search {
            padding: 12px;
        }

        .sidebar-search-box {
            height: 42px;
            border-radius: 13px;
            border: 1px solid rgba(148,163,184,.22);
            background: rgba(15,23,42,.92);
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
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(148,163,184,.32); border-radius: 999px; }

        .nav-section {
            margin: 18px 9px 8px;
            color: #64748b;
            font-size: 10px;
            font-weight: 950;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .side-link {
            width: 100%;
            min-height: 42px;
            border-radius: 13px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            gap: 11px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 13px;
            font-weight: 830;
            transition: .15s ease;
        }

        .side-link:hover {
            background: rgba(148,163,184,.12);
            color: #fff;
        }

        .side-link.active {
            background: var(--sv-primary);
            color: #fff;
            box-shadow: 0 12px 28px rgba(37,99,235,.28);
        }

        .side-link i {
            width: 18px;
            text-align: center;
            font-size: 15px;
        }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .system-pill {
            min-height: 42px;
            border-radius: 14px;
            background: rgba(16,185,129,.10);
            border: 1px solid rgba(16,185,129,.22);
            color: #bbf7d0;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 900;
        }

        #main-area {
            min-height: 100vh;
            margin-left: var(--sv-sidebar);
            transition: margin-left .22s ease;
        }

        #topbar {
            position: sticky;
            top: 0;
            z-index: 1040;
            height: var(--sv-topbar);
            background: rgba(255,255,255,.93);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--sv-line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 0 18px;
        }

        .topbar-left, .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .topbar-title h1 {
            margin: 0;
            font-size: 15px;
            font-weight: 950;
            line-height: 1.1;
        }

        .topbar-title p {
            margin: 4px 0 0;
            font-size: 11px;
            color: var(--sv-muted);
            font-weight: 750;
        }

        .topbar-btn {
            width: 38px;
            height: 38px;
            border-radius: 13px;
            border: 1px solid var(--sv-line);
            background: #fff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .topbar-btn:hover {
            color: var(--sv-primary);
            background: #f8fafc;
        }

        .topbar-search {
            width: min(380px, 36vw);
            height: 41px;
            border-radius: 999px;
            border: 1px solid var(--sv-line);
            background: #fff;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 0 14px;
            color: #94a3b8;
        }

        .topbar-search input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            font-size: 13px;
            color: #0f172a;
            font-weight: 650;
        }

        .user-pill {
            height: 42px;
            border-radius: 999px;
            border: 1px solid var(--sv-line);
            background: #fff;
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

        #page-content {
            padding: 22px;
        }

        body.sidebar-mini-surveyor #sidebar { width: var(--sv-sidebar-mini); }
        body.sidebar-mini-surveyor #main-area { margin-left: var(--sv-sidebar-mini); }
        body.sidebar-mini-surveyor .brand-copy,
        body.sidebar-mini-surveyor .sidebar-search,
        body.sidebar-mini-surveyor .nav-section,
        body.sidebar-mini-surveyor .side-link span,
        body.sidebar-mini-surveyor .system-pill span {
            display: none !important;
        }
        body.sidebar-mini-surveyor .sidebar-brand,
        body.sidebar-mini-surveyor .side-link {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                transform: translateX(-100%);
                width: var(--sv-sidebar);
            }

            body.sidebar-mobile-open #sidebar {
                transform: translateX(0);
            }

            #main-area,
            body.sidebar-mini-surveyor #main-area {
                margin-left: 0;
            }

            #page-content {
                padding: 14px;
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
<div id="app-shell" x-data="surveyorShell()" x-init="init()">

    @include('Surveyor.partials.sidebar')

    <div id="main-area">

        @include('Surveyor.partials.navbar')

        <main id="page-content">

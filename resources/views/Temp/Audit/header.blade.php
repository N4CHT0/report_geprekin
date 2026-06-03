<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Audit Backoffice') — Geprekin</title>

    {{-- TALL Stack Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --font-sans: 'DM Sans', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --bg-base: #f4f5f7; --bg-surface: #ffffff; --bg-overlay: #f9fafb;
            --border-faint: rgba(0,0,0,0.03); --border-subtle: rgba(0,0,0,0.08); --border-muted: rgba(0,0,0,0.15);
            --text-primary: #111827; --text-secondary: #4b5563; --text-muted: #6b7280; --text-disabled: #9ca3af;
            --accent: #000000; --success-muted: #d1fae5; --success: #059669; --danger-muted: #fee2e2; --danger: #dc2626;
            --sidebar-w: 250px; --topbar-h: 56px;
        }

        html, body {
            margin: 0; padding: 0; background: var(--bg-base); color: var(--text-primary);
            font-family: var(--font-sans); font-size: 13.5px; line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-thumb { background: var(--border-muted); border-radius: 99px; }

        #app-shell { display: flex; height: 100vh; overflow: hidden; }

        /* ─ Sidebar Base ─ */
        #sidebar {
            width: var(--sidebar-w); flex-shrink: 0; background: var(--bg-surface);
            border-right: 1px solid var(--border-subtle); display: flex; flex-direction: column;
            height: 100vh; overflow: hidden; transition: width .2s, transform .2s; z-index: 40;
        }
        #sidebar-brand { display: flex; align-items: center; gap: 10px; height: var(--topbar-h); padding: 0 16px; border-bottom: 1px solid var(--border-subtle); text-decoration: none; flex-shrink: 0; overflow: hidden; white-space: nowrap; }
        #sidebar-brand img { width: 28px; height: 28px; border-radius: 6px; object-fit: cover; flex-shrink: 0; }
        #sidebar-brand .brand-text { font-size: 14px; font-weight: 700; color: var(--text-primary); transition: opacity .2s; }
        .sb-search-wrap { padding: 12px 12px 4px; flex-shrink: 0; }
        .sb-search-inner { position: relative; }
        .sb-search-inner i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--text-muted); }
        .sb-search-input { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 6px; padding: 7px 10px 7px 30px; outline: none; }
        .sb-search-input:focus { border-color: var(--accent); background: var(--bg-surface); }
        .sb-nav { flex: 1; overflow-y: auto; padding: 8px 10px 12px; overflow-x: hidden; }
        .sb-group-label { padding: 12px 8px 4px; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--text-muted); }
        .sb-link { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 6px; text-decoration: none; color: var(--text-secondary); font-weight: 500; margin-bottom: 2px; transition: background .15s; white-space: nowrap; }
        .sb-link:hover { background: var(--bg-overlay); color: var(--text-primary); }
        .sb-link.active { background: var(--bg-base); color: var(--text-primary); font-weight: 600; }
        .sb-link i { font-size: 15px; width: 18px; text-align: center; flex-shrink: 0; }
        .sb-footer { padding: 10px; border-top: 1px solid var(--border-subtle); flex-shrink: 0; overflow: hidden; }

        /* ══════════════════════════════════════════════════
           PERBAIKAN SIDEBAR COLLAPSED (!IMPORTANT FORCE)
        ══════════════════════════════════════════════════ */
        body.sidebar-collapsed #sidebar { width: 64px; }
        body.sidebar-collapsed #sidebar-brand { justify-content: center; padding: 0; }
        body.sidebar-collapsed #sidebar-brand .brand-text { display: none !important; }
        body.sidebar-collapsed .sb-search-wrap { display: none !important; }
        body.sidebar-collapsed .sb-group-label { display: none !important; }
        body.sidebar-collapsed .sb-link { justify-content: center; padding: 12px 0; }
        body.sidebar-collapsed .sb-link span { display: none !important; }
        body.sidebar-collapsed .sb-footer .sb-link { justify-content: center; }
        body.sidebar-collapsed .sb-footer span { display: none !important; }

        @media (max-width: 1023px) {
            #sidebar { position: fixed; inset: 0 auto 0 0; transform: translateX(-100%); z-index: 50; }
            body.mobile-sidebar-open #sidebar { transform: translateX(0); box-shadow: 0 0 20px rgba(0,0,0,.1); }
        }

        /* ─ Main Area & Topbar ─ */
        #main-area { flex: 1; display: flex; flex-direction: column; min-width: 0; overflow: hidden; }
        #topbar { height: var(--topbar-h); background: var(--bg-surface); border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: space-between; padding: 0 20px; flex-shrink: 0; }
        .topbar-btn { display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 6px; background: transparent; border: 1px solid transparent; color: var(--text-secondary); cursor: pointer; font-size: 16px; }
        .topbar-btn:hover { background: var(--bg-overlay); color: var(--text-primary); border-color: var(--border-subtle); }
        .user-pill { display: flex; align-items: center; gap: 8px; padding: 4px 10px 4px 4px; border-radius: 8px; border: 1px solid var(--border-subtle); background: var(--bg-surface); cursor: pointer; }
        .user-pill:hover { background: var(--bg-overlay); border-color: var(--border-muted); }
        .user-pill img { width: 26px; height: 26px; border-radius: 5px; object-fit: cover; }
        .user-pill-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
        .dd-panel { position: absolute; right: 0; top: calc(100% + 8px); width: 240px; background: var(--bg-surface); border: 1px solid var(--border-muted); border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,.08); z-index: 200; overflow: hidden; }
        .dd-header { padding: 15px; border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 12px; background: var(--bg-overlay); }
        .dd-name { font-size: 14px; font-weight: 700; color: var(--text-primary); }
        .dd-items { padding: 6px; }
        .dd-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; color: var(--text-secondary); text-decoration: none; cursor: pointer; border: none; background: none; width: 100%; transition: background .15s; }
        .dd-item:hover { background: var(--bg-overlay); color: var(--text-primary); }

        #page-content { flex: 1; overflow-y: auto; padding: 24px; }

        /* ─ Design Tokens Kunci ─ */
        .c-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 10px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .c-card-header { padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .stat-val { font-size: 28px; font-weight: 700; color: var(--text-primary); font-family: var(--font-mono); line-height: 1; }
        .stat-lbl { font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); margin-top: 6px; }
        .f-label { display: block; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 6px; }
        .f-input, .f-select { width: 100%; background: var(--bg-surface); border: 1px solid var(--border-muted); border-radius: 6px; padding: 8px 12px; font-size: 13.5px; color: var(--text-primary); outline: none; appearance: none; }
        .f-input:focus, .f-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(0,0,0,0.15); }
        .f-select-wrap { position: relative; }
        .f-select-wrap i { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 11px; color: var(--text-muted); pointer-events: none; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 8px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; border: 1px solid transparent; cursor: pointer; text-decoration: none; }
        .btn-primary { background: var(--text-primary); color: #fff; border-color: var(--text-primary); }
        .btn-ghost { background: transparent; color: var(--text-secondary); border-color: var(--border-muted); }
        .c-table { width: 100%; border-collapse: collapse; }
        .c-table thead th { padding: 10px 16px; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-overlay); text-align: left; }
        .c-table tbody td { padding: 12px 16px; border-bottom: 1px solid var(--border-faint); vertical-align: middle; }
        .c-table tr:hover td { background: var(--bg-overlay); }
        .badge { display: inline-flex; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; font-family: var(--font-mono); }
        .badge-neutral { background: var(--bg-surface); color: var(--text-secondary); border: 1px solid var(--border-muted); }

        .modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.4); backdrop-filter: blur(4px); z-index: 300; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .modal-panel { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 12px; width: 100%; display: flex; flex-direction: column; box-shadow: 0 20px 40px rgba(0,0,0,.15); }
        .modal-header { padding: 20px 24px; border-bottom: 1px solid var(--border-faint); display: flex; align-items: flex-start; justify-content: space-between; }
        .modal-body { padding: 24px; overflow-y: auto; }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border-faint); display: flex; justify-content: flex-end; gap: 10px; background: var(--bg-overlay); border-radius: 0 0 12px 12px; }

        .combo-trigger { display: flex; align-items: center; justify-content: space-between; width: 100%; background: var(--bg-surface); border: 1px solid var(--border-muted); border-radius: 6px; padding: 8px 12px; cursor: pointer; }
        .combo-panel { position: absolute; z-index: 200; width: 100%; top: calc(100% + 6px); background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,.1); overflow: hidden; }
        .combo-search { padding: 10px; border-bottom: 1px solid var(--border-faint); background: var(--bg-overlay); }
        .combo-search input { width: 100%; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 6px; padding: 8px 12px; outline: none; }
        .combo-list { max-height: 220px; overflow-y: auto; padding: 6px; }
        .combo-item { padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .combo-item:hover { background: var(--bg-overlay); }

        /* Pagination TALL Stack Override */
        nav[role="navigation"] { display: flex !important; justify-content: flex-end !important; width: 100%; }
        nav[role="navigation"] > div:first-child, nav[role="navigation"] p { display: none !important; }
        nav[role="navigation"] ul { display: flex !important; flex-wrap: wrap; gap: 4px; padding: 0; margin: 0; list-style: none !important; }
        nav[role="navigation"] li { list-style: none !important; margin: 0; padding: 0; display: inline-flex; }
        nav[role="navigation"] > div:last-child > div:last-child > span { display: flex !important; flex-wrap: wrap; gap: 4px; box-shadow: none !important; }
        nav[role="navigation"] a, nav[role="navigation"] span.relative, nav[role="navigation"] .page-link {
            background: var(--bg-surface) !important; border: 1px solid var(--border-subtle) !important;
            color: var(--text-secondary) !important; font-size: 13px !important; min-width: 32px; height: 32px;
            padding: 0 10px !important; border-radius: 6px !important; font-family: var(--font-mono) !important;
            text-decoration: none !important; display: inline-flex !important; align-items: center !important;
            justify-content: center !important; margin: 0 !important; box-shadow: none !important;
        }
        nav[role="navigation"] a:hover { background: var(--bg-overlay) !important; color: var(--text-primary) !important; border-color: var(--border-muted) !important; }
        nav[role="navigation"] span[aria-current="page"] > span, nav[role="navigation"] li.active span {
            background: var(--text-primary) !important; color: #fff !important; border-color: var(--text-primary) !important; font-weight: bold !important;
        }
        nav[role="navigation"] svg { width: 16px; height: 16px; }
    </style>
</head>
<body>

<div id="mobile-overlay" onclick="document.body.classList.remove('mobile-sidebar-open')"></div>

<div id="app-shell"
     x-data="{
         sidebarCollapsed: false,
         mobileSidebarOpen: false,
         isMobile() { return window.innerWidth < 1024 },
         toggle() {
             if (this.isMobile()) {
                 this.mobileSidebarOpen = !this.mobileSidebarOpen;
                 document.body.classList.toggle('mobile-sidebar-open', this.mobileSidebarOpen);
             } else {
                 this.sidebarCollapsed = !this.sidebarCollapsed;
                 document.body.classList.toggle('sidebar-collapsed', this.sidebarCollapsed);
             }
         }
     }"
     x-init="window.addEventListener('resize', () => {
         if (!isMobile()) { mobileSidebarOpen = false; document.body.classList.remove('mobile-sidebar-open'); }
         else { sidebarCollapsed = false; document.body.classList.remove('sidebar-collapsed'); }
     })">

    {{-- MEMANGGIL PARTIAL SIDEBAR --}}
    @include('Temp.Audit.partials.sidebar')

    <div id="main-area">
        
        {{-- MEMANGGIL PARTIAL NAVBAR --}}
        @include('Temp.Audit.partials.navbar')

        <main id="page-content">
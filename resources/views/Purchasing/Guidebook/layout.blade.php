<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Guidebook') — SCM System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #696cff;
            --primary-light: #eeedfe;
            --sidebar-w: 280px;
            --topbar-h: 60px;
        }

        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f9; margin: 0; }

        /* ── Topbar ── */
        .gb-topbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e8e8f0;
            display: flex; align-items: center; padding: 0 24px;
            gap: 16px;
        }
        .gb-logo { font-size: 15px; font-weight: 700; color: var(--primary); }
        .gb-role-badge {
            font-size: 11px; font-weight: 700; padding: 3px 10px;
            border-radius: 20px; text-transform: uppercase; letter-spacing: .5px;
        }
        .gb-topbar .spacer { flex: 1; }
        .gb-topbar .gb-actions { display: flex; gap: 8px; }
        .btn-print-guide {
            background: var(--primary); color: #fff; border: none;
            padding: 6px 16px; border-radius: 8px; font-size: 13px;
            font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;
        }
        .btn-print-guide:hover { background: #5153e0; }
        .btn-back {
            background: #f0f0f8; color: #566a7f; border: none;
            padding: 6px 14px; border-radius: 8px; font-size: 13px;
            cursor: pointer; display: flex; align-items: center; gap: 5px;
            text-decoration: none;
        }
        .btn-back:hover { background: #e8e8f5; color: #233446; }

        /* ── Sidebar ── */
        .gb-sidebar {
            position: fixed; top: var(--topbar-h); left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: #fff;
            border-right: 1px solid #e8e8f0;
            overflow-y: auto;
            padding: 20px 0;
        }
        .gb-sidebar::-webkit-scrollbar { width: 4px; }
        .gb-sidebar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

        .sidebar-section-label {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .8px; color: #a0acb8; padding: 12px 20px 6px;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 9px;
            padding: 8px 20px; font-size: 13px; color: #566a7f;
            text-decoration: none; cursor: pointer;
            border-left: 3px solid transparent;
            transition: all .15s;
        }
        .sidebar-link:hover { background: #f8f8ff; color: var(--primary); }
        .sidebar-link.active {
            background: var(--primary-light); color: var(--primary);
            border-left-color: var(--primary); font-weight: 600;
        }
        .sidebar-link .icon {
            width: 20px; text-align: center; font-size: 14px;
        }

        /* ── Main Content ── */
        .gb-main {
            margin-left: var(--sidebar-w);
            margin-top: var(--topbar-h);
            padding: 32px 40px 80px;
            max-width: 960px;
        }

        /* ── Section ── */
        .gb-section {
            background: #fff;
            border-radius: 12px;
            border: 0.5px solid #e8e8f0;
            padding: 28px 32px;
            margin-bottom: 24px;
            scroll-margin-top: calc(var(--topbar-h) + 20px);
        }

        .gb-section-header {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 6px;
        }
        .gb-section-icon {
            width: 36px; height: 36px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .gb-section-title { font-size: 17px; font-weight: 700; color: #233446; margin: 0; }
        .gb-section-desc  { font-size: 13px; color: #697a8d; margin-bottom: 20px; }
        .gb-divider { border: none; border-top: 1px solid #f0f0f8; margin: 20px 0; }

        /* ── Steps ── */
        .step-list { display: flex; flex-direction: column; gap: 14px; }
        .step-item {
            display: flex; gap: 14px; align-items: flex-start;
        }
        .step-num {
            width: 28px; height: 28px; border-radius: 8px;
            background: var(--primary); color: #fff;
            font-size: 12px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 1px;
        }
        .step-body { flex: 1; }
        .step-title { font-size: 14px; font-weight: 600; color: #233446; margin-bottom: 3px; }
        .step-desc  { font-size: 13px; color: #566a7f; line-height: 1.6; }

        /* ── Screenshot Placeholder ── */
        .screenshot-box {
            background: #f8f8ff;
            border: 1.5px dashed #c5c5ff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #a0a0cc;
            font-size: 12px;
            margin: 12px 0;
        }
        .screenshot-box i { font-size: 28px; display: block; margin-bottom: 6px; }

        /* ── Tips & Warning Boxes ── */
        .tip-box, .warn-box, .info-box, .danger-box {
            border-radius: 8px; padding: 14px 16px;
            margin: 14px 0; font-size: 13px; line-height: 1.6;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .tip-box    { background: #eaf3de; border-left: 4px solid #71dd37; color: #2d5a1c; }
        .warn-box   { background: #faeeda; border-left: 4px solid #ffab00; color: #633806; }
        .info-box   { background: #e6f1fb; border-left: 4px solid #03c3ec; color: #0c447c; }
        .danger-box { background: #fcebeb; border-left: 4px solid #ff3e1d; color: #7a1010; }
        .tip-box i, .warn-box i, .info-box i, .danger-box i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }

        /* ── Troubleshoot ── */
        .trouble-item {
            border: 0.5px solid #e8e8f0; border-radius: 8px;
            margin-bottom: 10px; overflow: hidden;
        }
        .trouble-q {
            padding: 12px 16px; font-size: 13px; font-weight: 600;
            color: #233446; cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            background: #fafafe;
        }
        .trouble-q:hover { background: #f0f0ff; }
        .trouble-q .trouble-icon { color: #a32d2d; font-size: 14px; flex-shrink: 0; }
        .trouble-q .chev { margin-left: auto; color: #a0acb8; transition: transform .2s; }
        .trouble-q.open .chev { transform: rotate(180deg); }
        .trouble-a {
            display: none; padding: 12px 16px;
            font-size: 13px; color: #566a7f; line-height: 1.6;
            border-top: 0.5px solid #e8e8f0;
            background: #fff;
        }

        /* ── Badge inline ── */
        .badge-inline {
            display: inline-block; font-size: 10px; font-weight: 700;
            padding: 2px 7px; border-radius: 4px; vertical-align: middle;
            margin: 0 2px;
        }
        .bi-green  { background: #eaf3de; color: #27500a; }
        .bi-amber  { background: #faeeda; color: #633806; }
        .bi-red    { background: #fcebeb; color: #a32d2d; }
        .bi-blue   { background: #e6f1fb; color: #0c447c; }
        .bi-purple { background: #eeedfe; color: #3c3489; }

        /* ── Flow diagram inline ── */
        .flow-inline {
            display: flex; flex-wrap: wrap; align-items: center;
            gap: 6px; margin: 14px 0;
        }
        .flow-node {
            padding: 5px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }
        .flow-arrow { color: #a0acb8; font-size: 12px; }

        /* ── Table ── */
        .gb-table { width: 100%; border-collapse: collapse; font-size: 13px; margin: 12px 0; }
        .gb-table th { background: #f5f5f9; padding: 9px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: #566a7f; border-bottom: 1px solid #e8e8f0; text-align: left; }
        .gb-table td { padding: 9px 12px; border-bottom: 0.5px solid #f0f0f8; color: #566a7f; vertical-align: top; }
        .gb-table tr:last-child td { border-bottom: none; }

        /* ── Print ── */
        @media print {
            .gb-topbar, .gb-sidebar { display: none !important; }
            .gb-main { margin: 0; padding: 20px; max-width: 100%; }
            .gb-section { break-inside: avoid; border: 1px solid #ddd; }
            .trouble-a { display: block !important; }
        }
        @page { size: A4; margin: 15mm; }

        /* ── Progress bar ── */
        .reading-progress {
            position: fixed; top: var(--topbar-h); left: 0; right: 0;
            height: 3px; background: #e8e8f0; z-index: 99;
        }
        .reading-progress-fill {
            height: 100%; background: var(--primary);
            width: 0%; transition: width .1s;
        }
    </style>
</head>
<body>

{{-- ── Topbar ── --}}
<div class="gb-topbar">
    <div class="gb-logo">
        <i class="bi bi-book-half me-1"></i> Guidebook SCM
    </div>
    <span class="gb-role-badge @yield('role-badge-class')">
        @yield('role-label')
    </span>
    <div class="spacer"></div>
    <div class="gb-actions">
        <a href="{{ url()->previous() }}" class="btn-back">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button class="btn-print-guide" onclick="window.print()">
            <i class="bi bi-printer"></i> Export PDF
        </button>
    </div>
</div>

{{-- ── Reading Progress ── --}}
<div class="reading-progress">
    <div class="reading-progress-fill" id="readingProgress"></div>
</div>

{{-- ── Sidebar ── --}}
<nav class="gb-sidebar" id="gbSidebar">
    @yield('sidebar')
</nav>

{{-- ── Main Content ── --}}
<main class="gb-main" id="gbMain">
    @yield('content')
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    // ── Smooth scroll ke section ──────────────────────────────
    function scrollTo(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    }

    // ── Active sidebar link on scroll ────────────────────────
    const sections = document.querySelectorAll('.gb-section[id]');
    const links    = document.querySelectorAll('.sidebar-link[data-section]');

    function onScroll() {
        let current = '';
        sections.forEach(s => {
            const top = s.getBoundingClientRect().top;
            if (top <= 100) current = s.id;
        });
        links.forEach(l => {
            l.classList.toggle('active', l.dataset.section === current);
        });

        // Reading progress
        const main  = document.getElementById('gbMain');
        const total = main.scrollHeight - window.innerHeight;
        const pct   = Math.min(100, Math.round((window.scrollY - main.offsetTop + 60) / total * 100));
        document.getElementById('readingProgress').style.width = pct + '%';
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // ── Troubleshoot accordion ────────────────────────────────
    document.querySelectorAll('.trouble-q').forEach(q => {
        q.addEventListener('click', function () {
            const ans  = this.nextElementSibling;
            const open = this.classList.toggle('open');
            ans.style.display = open ? 'block' : 'none';
        });
    });
</script>
</body>
</html>
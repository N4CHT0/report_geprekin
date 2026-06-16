{{-- resources/views/Surveyor/partials/sidebar.blade.php --}}
<aside id="sidebar">
    <a href="{{ route('investor.surveyor.site-score.index') }}" class="sidebar-brand">
        <img src="{{ asset('img/logo2.jpg') }}" alt="Geprekin">
        <div class="brand-copy">
            <div class="brand-title">Geprekin</div>
            <div class="brand-subtitle">Site Feasibility</div>
        </div>
    </a>

    <div class="sidebar-search">
        <div class="sidebar-search-box">
            <i class="bi bi-search"></i>
            <input id="sidebarSearchInput" type="text" placeholder="Cari menu...">
        </div>
    </div>

    <nav class="sidebar-nav" id="sidebarNavigation">

        <div class="nav-section">Site Score Outlet</div>

        <a href="{{ route('investor.surveyor.site-score.index') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.index') ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap"></i>
            <span>Dashboard Worksheet</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.create') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.create') ? 'active' : '' }}">
            <i class="bi bi-table"></i>
            <span>Form Site Score</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.my-drafts') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.my-drafts') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i>
            <span>Draft & Revisi Saya</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.map') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.map') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i>
            <span>Peta Titik Survey</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.ranking') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.ranking') ? 'active' : '' }}">
            <i class="bi bi-trophy"></i>
            <span>List Site Score</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.comparison') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.comparison') ? 'active' : '' }}">
            <i class="bi bi-columns-gap"></i>
            <span>Comparison Lokasi</span>
        </a>

        <div class="nav-section">Candidate & Assignment</div>

        <a href="{{ route('investor.surveyor.candidate.index') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.candidate.index') ? 'active' : '' }}">
            <i class="bi bi-person-bounding-box"></i>
            <span>Kandidat Titik Lokasi</span>
        </a>

        <a href="{{ route('investor.surveyor.candidate.create') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.candidate.create') ? 'active' : '' }}">
            <i class="bi bi-plus-square"></i>
            <span>Tambah Kandidat</span>
        </a>

        <a href="{{ route('investor.surveyor.candidate.assignment') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.candidate.assignment') ? 'active' : '' }}">
            <i class="bi bi-person-check"></i>
            <span>Surveyor Assignment</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.approval') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.approval') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            <span>Workflow Approval</span>
        </a>

        <div class="nav-section">Pengamatan</div>

        <a href="{{ route('investor.surveyor.site-score.trial-pengamatan') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.trial-pengamatan') ? 'active' : '' }}">
            <i class="bi bi-binoculars"></i>
            <span>Trial Pengamatan</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.by-outlet') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.by-outlet') ? 'active' : '' }}">
            <i class="bi bi-shop-window"></i>
            <span>Pengamatan By Outlet</span>
        </a>

        {{-- KARENA AI SUDAH TERINTEGRASI DI DALAM FORM SITE SCORE, MENU INI TIDAK PERLU DITAMPILKAN LAGI
        <div class="nav-section">AI Detection</div>

        <a href="{{ route('investor.surveyor.video-detection.index') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.video-detection.*') ? 'active' : '' }}">
            <i class="bi bi-camera-video"></i>
            <span>Video Detection AI</span>
        </a>

        <a href="#"
           class="side-link">
            <i class="bi bi-people"></i>
            <span>People Counter</span>
        </a>

        <a href="#"
           class="side-link">
            <i class="bi bi-car-front"></i>
            <span>Vehicle Counter</span>
        </a>
        --}}

        <div class="nav-section">Telegram & Backup</div>

        <a href="{{ route('investor.surveyor.telegram.form') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.telegram.*') ? 'active' : '' }}">
            <i class="bi bi-chat-dots"></i>
            <span>Form Backup Telegram</span>
        </a>

        {{-- FITUR BELUM ADA (HANYA DUMMY #)
        <a href="#"
           class="side-link">
            <i class="bi bi-robot"></i>
            <span>Telegram AI Bot</span>
        </a>
        --}}

        <div class="nav-section">Laporan</div>

        <a href="{{ route('investor.surveyor.site-score.rekap') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.rekap') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-spreadsheet"></i>
            <span>Recap Report</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.traffic') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.traffic') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i>
            <span>Traffic Analytics</span>
        </a>

        <a href="{{ route('investor.surveyor.site-score.heatmap') }}"
           class="side-link {{ request()->routeIs('investor.surveyor.site-score.heatmap') ? 'active' : '' }}">
            <i class="bi bi-map"></i>
            <span>Heatmap Lokasi</span>
        </a>

        <div class="nav-section">Data Master (Admin)</div>

        <a href="{{ route('master.boq.index') }}"
           class="side-link {{ request()->routeIs('master.boq.*') ? 'active' : '' }}">
            <i class="bi bi-wallet2"></i>
            <span>Master BOQ</span>
        </a>

        <a href="{{ route('ai.collector.index') }}"
           class="side-link {{ request()->routeIs('ai.collector.*') ? 'active' : '' }}">
            <i class="bi bi-database-check"></i>
            <span>AI Dataset Collector</span>
        </a>

    </nav>

    <div class="sidebar-footer">
        <div class="system-pill">
            <i class="bi bi-cloud-check"></i>
            <span>Worksheet Online</span>
        </div>
    </div>
</aside>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('sidebarSearchInput');
    const nav = document.getElementById('sidebarNavigation');

    if (!input || !nav) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();

        nav.querySelectorAll('.side-link').forEach(item => {
            item.style.display = (!q || item.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });
});
</script>
@endpush

<aside id="sidebar">
    <a href="{{ route('dashboard.harian') }}" id="sidebar-brand">
        <img src="{{ asset('../img/logo2.jpg') }}" alt="Geprekin">
        <span class="brand-text">Audit Backoffice</span>
    </a>

    <div x-data="{ q: '' }" style="display:flex; flex-direction:column; flex:1; overflow:hidden;">
        <div class="sb-search-wrap">
            <div class="sb-search-inner">
                <i class="bi bi-search"></i>
                <input type="text" x-model="q" class="sb-search-input" placeholder="Cari menu...">
            </div>
        </div>

        <nav class="sb-nav">
            @php
                $navGroups = [
                    // PILIHAN BARU: Kembali ke Dashboard Utama
                    'Utama' => [
                        ['url' => url('/'), 'icon' => 'bi-arrow-left-circle', 'label' => 'Kembali Dashboard Utama'],
                    ],
                    'Dashboard' => [
                        ['route' => 'dashboard.harian', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard Harian'],
                        ['route' => 'dashboard.recap', 'icon' => 'bi-bar-chart-line', 'label' => 'Dashboard Recap'],
                    ],
                    'Laporan' => [
                        ['route' => 'laporan.compliance_recap', 'icon' => 'bi-clipboard2-check','label' => 'Compliance Recap'],
                        ['route' => 'laporan.ranking_outlet', 'icon' => 'bi-trophy', 'label' => 'Ranking Outlet'],
                        ['route' => 'laporan.kumulatif_ranking_pic', 'icon' => 'bi-people', 'label' => 'Kumulatif Ranking PIC'],
                    ],
                    'Master' => [
                        ['route' => 'audit.laporan', 'icon' => 'bi-chat-square-dots','label' => 'Data Responses'],
                        ['route' => 'auditDashboard.daftarKuisioner', 'icon' => 'bi-question-circle', 'label' => 'Data Pertanyaan'],
                        ['route' => 'master.data_outlet', 'icon' => 'bi-shop', 'label' => 'Data Outlet'],
                        ['route' => 'master.data_pic', 'icon' => 'bi-person-vcard', 'label' => 'Data PIC'],
                        ['route' => 'master.setting', 'icon' => 'bi-sliders', 'label' => 'Setting'],
                    ],
                ];
            @endphp

            @foreach($navGroups as $groupLabel => $navItems)
                @php $labelsLower = collect($navItems)->pluck('label')->map(fn($l) => strtolower($l))->implode('|'); @endphp
                <div class="sb-group-label" x-show="q === '' || '{{ $labelsLower }}'.split('|').some(l => l.includes(q.toLowerCase()))">
                    {{ $groupLabel }}
                </div>
                @foreach($navItems as $nav)
                    @php
                        // Logika untuk menentukan URL dan status active
                        $targetUrl = isset($nav['url']) ? $nav['url'] : route($nav['route']);
                        $isActive = isset($nav['route']) ? request()->routeIs($nav['route']) : request()->is('/');
                    @endphp
                    <a href="{{ $targetUrl }}"
                       class="sb-link {{ $isActive ? 'active' : '' }}"
                       title="{{ $nav['label'] }}"
                       x-show="q === '' || '{{ strtolower($nav['label']) }}'.includes(q.toLowerCase())">
                        <i class="bi {{ $nav['icon'] }}"></i>
                        <span>{{ $nav['label'] }}</span>
                    </a>
                @endforeach
            @endforeach
        </nav>
    </div>

    <div class="sb-footer">
        <a href="{{ route('crew.profile.form') }}" class="sb-link" style="margin:0;" title="Profil Saya">
            <i class="bi bi-person-circle"></i>
            <span>Profil Saya</span>
        </a>
    </div>
</aside>
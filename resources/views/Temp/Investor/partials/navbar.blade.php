{{-- resources/views/Temp/Investor/partials/navbar.blade.php --}}
@php
    if (!function_exists('investor_num_money')) {
        function investor_num_money($v): int {
            if ($v === null) return 0;
            if (is_int($v) || is_float($v)) return (int) round($v);

            $s = trim((string) $v);
            $s = str_replace(['Rp', 'rp', 'RP', ' ', "\u{00A0}", '.', ','], '', $s);
            $s = preg_replace('/[^0-9\-]/', '', $s);

            return ($s === '' || $s === '-') ? 0 : (int) $s;
        }
    }

    if (!function_exists('investor_pct_change')) {
        function investor_pct_change($today, $compare): float {
            $today = (float) $today;
            $compare = (float) $compare;
            if ($compare == 0) return 0;
            return (($today - $compare) / $compare) * 100;
        }
    }

    $hasWarningDown = false;
    $warningDownCount = 0;

    if (isset($notifikasiTurunSales)) {
        foreach ($notifikasiTurunSales as $notif) {
            $hariIni = investor_num_money($notif['total_hari_ini'] ?? 0);
            $banding = investor_num_money($notif['total_kemarin'] ?? 0);
            $pct = investor_pct_change($hariIni, $banding);

            if ($pct < 0 && abs($pct) > 50) {
                $hasWarningDown = true;
                $warningDownCount++;
            }
        }
    }

    $hasWarningUp = false;
    $warningUpCount = 0;

    if (isset($notifikasiNaikSales)) {
        foreach ($notifikasiNaikSales as $notif) {
            $hariIni = investor_num_money($notif['total_hari_ini'] ?? 0);
            $banding = investor_num_money($notif['total_kemarin'] ?? 0);
            $pct = investor_pct_change($hariIni, $banding);

            if ($pct > 50) {
                $hasWarningUp = true;
                $warningUpCount++;
            }
        }
    }
@endphp

<nav id="topbar" x-data="{ userOpen: false, notifDownOpen: false, notifUpOpen: false }">
    <style>
        .notif-menu {
            position: absolute;
            right: 18px;
            top: calc(var(--shell-topbar) + 8px);
            width: min(450px, calc(100vw - 32px));
            max-height: min(620px, calc(100vh - 90px));
            overflow: auto;
            border-radius: 18px;
            border: 1px solid rgba(15,23,42,.12);
            background: rgba(255,255,255,.97);
            backdrop-filter: blur(18px);
            box-shadow: 0 24px 70px rgba(15,23,42,.18);
            z-index: 9999;
        }

        .notif-menu-header {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 15px 16px;
            border-bottom: 1px solid rgba(15,23,42,.08);
            background: rgba(255,255,255,.94);
            backdrop-filter: blur(18px);
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:12px;
        }

        .notif-menu-title {
            margin:0;
            font-size:15px;
            font-weight:950;
            color:#0f172a;
            letter-spacing:-.02em;
        }

        .notif-menu-sub {
            margin-top:3px;
            font-size:12px;
            font-weight:700;
            color:#64748b;
        }

        .notif-list {
            padding: 10px;
            display:flex;
            flex-direction:column;
            gap:9px;
        }

        .notif-card {
            border: 1px solid rgba(15,23,42,.09);
            border-radius: 15px;
            background: linear-gradient(135deg,#fff,#f8fafc);
            padding: 13px;
            transition:.16s ease;
        }

        .notif-card:hover {
            transform: translateY(-1px);
            box-shadow:0 14px 28px rgba(15,23,42,.08);
            border-color:rgba(37,99,235,.20);
        }

        .notif-row {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:12px;
        }

        .notif-outlet {
            font-size:13px;
            font-weight:950;
            color:#0f172a;
            line-height:1.35;
        }

        .notif-date {
            margin-top:5px;
            font-size:12px;
            color:#64748b;
            font-weight:700;
        }

        .notif-value {
            margin-top:9px;
            font-size:12px;
            color:#334155;
            font-weight:700;
        }

        .notif-money-up { color:#059669;font-weight:950;white-space:nowrap; }
        .notif-money-down { color:#dc2626;font-weight:950;white-space:nowrap; }

        .notif-pct {
            display:inline-flex;
            align-items:center;
            gap:4px;
            padding:5px 8px;
            border-radius:999px;
            font-size:12px;
            font-weight:950;
            white-space:nowrap;
        }

        .notif-pct.up { background:#ecfdf5;color:#059669; }
        .notif-pct.down { background:#fef2f2;color:#dc2626; }

        .notif-empty {
            padding:42px 20px;
            text-align:center;
            color:#64748b;
            font-weight:800;
        }

        @media (max-width: 767px) {
            .notif-menu { right: 10px; left: 10px; width:auto; }
        }
    </style>

    <div class="topbar-left">
        <button type="button" class="topbar-btn" @click="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-layout-sidebar-inset"></i>
        </button>

        <div class="topbar-title">
            <h1>@yield('title', 'Dashboard Penjualan')</h1>
            <p>@yield('breadcrumb', 'Dashboard')</p>
        </div>
    </div>

    <div class="topbar-right">
        <div class="topbar-search">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search menu, report, outlet...">
        </div>

        <button type="button" class="topbar-btn notif-btn" title="Sales turun"
                @click="notifDownOpen = !notifDownOpen; notifUpOpen = false">
            <i class="bi bi-bell"></i>
            @if($hasWarningDown)
                <span class="notif-badge">{{ $warningDownCount }}</span>
            @endif
        </button>

        <button type="button" class="topbar-btn notif-btn" title="Sales naik"
                @click="notifUpOpen = !notifUpOpen; notifDownOpen = false">
            <i class="bi bi-graph-up-arrow"></i>
            @if($hasWarningUp)
                <span class="notif-badge success">{{ $warningUpCount }}</span>
            @endif
        </button>

        <div class="user-wrap" @click.outside="userOpen = false">
            <button type="button" class="user-pill" @click="userOpen = !userOpen">
                <img src="{{ asset('../img/logo2.jpg') }}" alt="User">
                <span>
                    <span class="user-pill-name">{{ Auth::user()->name }}</span>
                    <span class="user-pill-role">{{ Auth::user()->role }}</span>
                </span>
                <i class="bi bi-chevron-down" style="font-size:11px;color:#64748b"></i>
            </button>

            <div class="dd-panel" x-show="userOpen" x-transition x-cloak>
                <div class="dd-header">
                    <img src="{{ asset('../img/logo2.jpg') }}" alt="User">
                    <div>
                        <div class="dd-name">{{ Auth::user()->name }}</div>
                        <div class="dd-role">{{ Auth::user()->role }}</div>
                    </div>
                </div>

                <div class="dd-items">
                    <a href="{{ route('investor.profile.edit', auth()->user()->id) }}" class="dd-item">
                        <i class="bi bi-person-circle"></i>
                        Profile
                    </a>

                    <form action="{{ route('auth.investor.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dd-item">
                            <i class="bi bi-box-arrow-right"></i>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Dropdown Sales Turun --}}
    <div x-show="notifDownOpen" x-transition x-cloak class="notif-menu" @click.outside="notifDownOpen = false">
        <div class="notif-menu-header">
            <div>
                <h3 class="notif-menu-title">Sales Turun &gt; 50%</h3>
                <div class="notif-menu-sub">{{ $warningDownCount }} outlet perlu dicek</div>
            </div>
            <button type="button" class="topbar-btn" @click="notifDownOpen = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="notif-list">
            @php $hasAnyDown = false; @endphp

            @if(isset($notifikasiTurunSales) && count($notifikasiTurunSales) > 0)
                @foreach($notifikasiTurunSales as $notif)
                    @php
                        $hariIni = investor_num_money($notif['total_hari_ini'] ?? 0);
                        $banding = investor_num_money($notif['total_kemarin'] ?? 0);
                        $pct = investor_pct_change($hariIni, $banding);
                        $diff = $hariIni - $banding;

                        if (!($pct < 0 && abs($pct) > 50)) continue;

                        $hasAnyDown = true;
                    @endphp

                    <div class="notif-card">
                        <div class="notif-row">
                            <div>
                                <div class="notif-outlet">{{ $notif['nama_outlet'] ?? '-' }}</div>
                                <div class="notif-date">{{ $notif['tanggal_terbaru'] ?? '-' }} vs {{ $notif['tanggal_pembanding'] ?? '-' }}</div>
                                <div class="notif-value">
                                    Hari ini: <b>Rp {{ number_format($hariIni, 0, ',', '.') }}</b>
                                    &nbsp; | &nbsp;
                                    Pembanding: <b>Rp {{ number_format($banding, 0, ',', '.') }}</b>
                                </div>
                            </div>

                            <div style="text-align:right;">
                                <span class="notif-pct down">
                                    <i class="bi bi-arrow-down-short"></i>
                                    -{{ rtrim(rtrim(number_format(abs($pct), 1, '.', ''), '0'), '.') }}%
                                </span>
                                <div class="notif-money-down" style="margin-top:8px;">
                                    -Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!$hasAnyDown)
                <div class="notif-empty">
                    <div style="font-size:32px;">✅</div>
                    <div style="margin-top:8px;">Tidak ada penurunan sales signifikan.</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Dropdown Sales Naik --}}
    <div x-show="notifUpOpen" x-transition x-cloak class="notif-menu" @click.outside="notifUpOpen = false">
        <div class="notif-menu-header">
            <div>
                <h3 class="notif-menu-title">Sales Naik &gt; 50%</h3>
                <div class="notif-menu-sub">{{ $warningUpCount }} outlet mengalami lonjakan</div>
            </div>
            <button type="button" class="topbar-btn" @click="notifUpOpen = false">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="notif-list">
            @php $hasAnyUp = false; @endphp

            @if(isset($notifikasiNaikSales) && count($notifikasiNaikSales) > 0)
                @foreach($notifikasiNaikSales as $notif)
                    @php
                        $hariIni = investor_num_money($notif['total_hari_ini'] ?? 0);
                        $banding = investor_num_money($notif['total_kemarin'] ?? 0);
                        $pct = investor_pct_change($hariIni, $banding);
                        $diff = $hariIni - $banding;

                        if (!($pct > 50)) continue;

                        $hasAnyUp = true;
                    @endphp

                    <div class="notif-card">
                        <div class="notif-row">
                            <div>
                                <div class="notif-outlet">{{ $notif['nama_outlet'] ?? '-' }}</div>
                                <div class="notif-date">{{ $notif['tanggal_terbaru'] ?? '-' }} vs {{ $notif['tanggal_pembanding'] ?? '-' }}</div>
                                <div class="notif-value">
                                    Hari ini: <b>Rp {{ number_format($hariIni, 0, ',', '.') }}</b>
                                    &nbsp; | &nbsp;
                                    Pembanding: <b>Rp {{ number_format($banding, 0, ',', '.') }}</b>
                                </div>
                            </div>

                            <div style="text-align:right;">
                                <span class="notif-pct up">
                                    <i class="bi bi-arrow-up-short"></i>
                                    +{{ rtrim(rtrim(number_format(abs($pct), 1, '.', ''), '0'), '.') }}%
                                </span>
                                <div class="notif-money-up" style="margin-top:8px;">
                                    +Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if(!$hasAnyUp)
                <div class="notif-empty">
                    <div style="font-size:32px;">✅</div>
                    <div style="margin-top:8px;">Tidak ada kenaikan sales signifikan.</div>
                </div>
            @endif
        </div>
    </div>
</nav>

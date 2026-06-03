<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Internal Dashboard BOD' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root{
            --internal-bg:#040b16;
            --internal-bg-2:#07111f;
            --internal-panel:#0a1528;
            --internal-panel-2:#0c1729;
            --internal-line:rgba(148,163,184,.14);
            --internal-line-soft:rgba(148,163,184,.08);
            --internal-text:#eaf2ff;
            --internal-muted:#8ea1bd;
            --internal-blue:#3b82f6;
            --internal-cyan:#06b6d4;
            --internal-green:#22c55e;
            --internal-red:#ef4444;
            --internal-amber:#f59e0b;
            --internal-shadow:0 18px 40px rgba(0,0,0,.35);
            --sidebar-w:280px;
            --topbar-h:76px;
        }

        *{ box-sizing:border-box; }

        html,body{
            margin:0;
            padding:0;
            min-height:100%;
            background:
                radial-gradient(circle at top left, rgba(59,130,246,.15), transparent 20%),
                radial-gradient(circle at top right, rgba(6,182,212,.08), transparent 18%),
                linear-gradient(180deg, #030814 0%, #07111f 100%);
            color:var(--internal-text);
            font-family:Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a{
            color:inherit;
            text-decoration:none;
        }

        .internal-layout{
            min-height:100vh;
            display:flex;
            background:transparent;
        }

        .internal-sidebar{
            position:fixed;
            inset:0 auto 0 0;
            width:var(--sidebar-w);
            background:linear-gradient(180deg, rgba(8,18,33,.98), rgba(5,11,21,.98));
            border-right:1px solid var(--internal-line);
            box-shadow:var(--internal-shadow);
            z-index:1040;
            display:flex;
            flex-direction:column;
            overflow:hidden;
        }

        .internal-sidebar::before{
            content:"";
            position:absolute;
            top:-100px;
            left:-70px;
            width:230px;
            height:230px;
            border-radius:50%;
            background:radial-gradient(circle, rgba(59,130,246,.18), transparent 70%);
            pointer-events:none;
        }

        .internal-brand{
            position:relative;
            padding:22px 22px 16px;
            border-bottom:1px solid var(--internal-line);
            background:linear-gradient(180deg, rgba(59,130,246,.10), rgba(59,130,246,0));
        }

        .internal-brand-badge{
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:7px 10px;
            border-radius:999px;
            font-size:11px;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
            color:#cfe0ff;
            background:rgba(59,130,246,.12);
            border:1px solid rgba(59,130,246,.22);
        }

        .internal-brand-title{
            margin:14px 0 4px;
            font-size:24px;
            line-height:1.05;
            font-weight:900;
            letter-spacing:-.04em;
            color:#fff;
        }

        .internal-brand-sub{
            font-size:12px;
            color:var(--internal-muted);
            line-height:1.5;
        }

        .internal-nav{
            padding:16px 14px 18px;
            overflow:auto;
            flex:1;
        }

        .internal-nav-group{
            margin-bottom:18px;
        }

        .internal-nav-label{
            padding:0 10px 8px;
            font-size:11px;
            font-weight:800;
            letter-spacing:.08em;
            text-transform:uppercase;
            color:#6f86a8;
        }

        .internal-nav-link{
            display:flex;
            align-items:center;
            gap:12px;
            padding:12px 12px;
            border-radius:14px;
            color:#dce9ff;
            border:1px solid transparent;
            transition:.2s ease;
            margin-bottom:6px;
            background:transparent;
        }

        .internal-nav-link:hover{
            background:rgba(59,130,246,.08);
            border-color:rgba(59,130,246,.14);
            transform:translateX(2px);
        }

        .internal-nav-link.active{
            background:linear-gradient(90deg, rgba(59,130,246,.16), rgba(6,182,212,.08));
            border-color:rgba(59,130,246,.20);
            box-shadow:0 10px 22px rgba(0,0,0,.18);
        }

        .internal-nav-icon{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:36px;
            height:36px;
            border-radius:12px;
            background:rgba(255,255,255,.04);
            border:1px solid var(--internal-line-soft);
            color:#cfe0ff;
            font-size:15px;
            flex:0 0 36px;
        }

        .internal-nav-text{
            min-width:0;
            flex:1;
        }

        .internal-nav-title{
            display:block;
            font-size:13px;
            font-weight:800;
            color:#f4f8ff;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .internal-nav-desc{
            display:block;
            margin-top:2px;
            font-size:11px;
            color:#88a0bf;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }

        .internal-main{
            width:100%;
            min-width:0;
            margin-left:var(--sidebar-w);
            display:flex;
            flex-direction:column;
        }

        .internal-topbar{
            position:sticky;
            top:0;
            z-index:1030;
            min-height:var(--topbar-h);
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            padding:14px 20px;
            background:rgba(4,11,22,.78);
            backdrop-filter:blur(14px);
            border-bottom:1px solid var(--internal-line);
            flex-wrap:wrap;
        }

        .internal-topbar-left{
            display:flex;
            align-items:center;
            gap:12px;
            min-width:0;
            flex:1 1 420px;
        }

        .internal-toggle{
            display:none;
            align-items:center;
            justify-content:center;
            width:44px;
            height:44px;
            border:1px solid rgba(148,163,184,.14);
            border-radius:12px;
            background:linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            color:#fff;
            cursor:pointer;
            box-shadow:0 10px 24px rgba(0,0,0,.22);
            font-size:18px;
            transition:.2s ease;
        }

        .internal-toggle:hover{
            transform:translateY(-1px);
            background:rgba(59,130,246,.16);
        }

        .internal-topbar-title{
            font-size:16px;
            font-weight:900;
            letter-spacing:.02em;
            color:#f7fbff;
        }

        .internal-topbar-sub{
            margin-top:2px;
            font-size:12px;
            color:var(--internal-muted);
        }

        .internal-topbar-right{
            display:flex;
            align-items:center;
            justify-content:flex-end;
            gap:10px;
            flex-wrap:wrap;
            flex:1 1 260px;
        }

        .internal-pill{
            display:inline-flex;
            align-items:center;
            gap:8px;
            min-height:40px;
            padding:8px 12px;
            border-radius:999px;
            background:rgba(255,255,255,.04);
            border:1px solid var(--internal-line);
            font-size:12px;
            font-weight:800;
            color:#dce8ff;
            white-space:nowrap;
        }

        .internal-dot{
            width:9px;
            height:9px;
            border-radius:50%;
            background:var(--internal-green);
            box-shadow:0 0 12px rgba(34,197,94,.8);
        }

        .internal-content{
            padding:0;
            width:100%;
            min-width:0;
        }

        .internal-footer{
            margin-top:auto;
            padding:20px 24px 28px;
            color:#6f86a8;
            font-size:12px;
        }

        .internal-sidebar-backdrop{
            display:none;
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.45);
            z-index:1025;
        }

        .app-main,
        .app-content,
        .container-fluid{
            width:100%;
            max-width:none;
        }

        @media (max-width:1200px){
            :root{ --sidebar-w:260px; }

            .internal-sidebar{
                transform:translateX(-100%);
                transition:transform .25s ease;
            }

            .internal-layout.sidebar-open .internal-sidebar{
                transform:translateX(0);
            }

            .internal-main{
                margin-left:0;
            }

            .internal-toggle{
                display:inline-flex;
            }

            .internal-layout.sidebar-open .internal-sidebar-backdrop{
                display:block;
            }

            .internal-topbar{
                padding:12px 14px;
            }
        }

        @media (max-width:576px){
            .internal-brand-title{ font-size:22px; }
            .internal-topbar-right{ display:none; }
        }
    </style>
</head>
<body>
<div class="internal-layout" id="internalLayout">
    <div class="internal-sidebar-backdrop" id="internalSidebarBackdrop"></div>

    <aside class="internal-sidebar">
        <div class="internal-brand">
            <div class="internal-brand-badge">Internal Geprekin</div>
            <div class="internal-brand-title">BOD Analytics</div>
            <!--<div class="internal-brand-sub">Dark executive dashboard dengan nuansa market terminal untuk monitoring performa lintas divisi.</div>-->
        </div>

        <nav class="internal-nav">
            <div class="internal-nav-group">
                <div class="internal-nav-label">Main Board</div>
                <a href="{{ route('investor.sales.dashboardBOD') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD') ? 'active' : '' }}">
                    <span class="internal-nav-icon">◎</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Dashboard</span>
                        <span class="internal-nav-desc">Ringkasan market performance</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardGO') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardGO') ? 'active' : '' }}">
                    <span class="internal-nav-icon">◎</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Overview GO</span>
                        <span class="internal-nav-desc">Ringkasan market performance</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.salesComparison') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.salesComparison') ? 'active' : '' }}">
                    <span class="internal-nav-icon">◫</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Sales Comparison</span>
                        <span class="internal-nav-desc">Bandingkan performa periode</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.labourCost') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.labourCost') ? 'active' : '' }}">
                    <span class="internal-nav-icon">¤</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Labour Cost</span>
                        <span class="internal-nav-desc">Efisiensi tenaga kerja</span>
                    </span>
                </a>
            </div>

            <div class="internal-nav-group">
                <div class="internal-nav-label">Quality & People</div>
                <a href="{{ route('master.qcr.index') }}" class="internal-nav-link {{ request()->routeIs('master.qcr.index') ? 'active' : '' }}">
                    <span class="internal-nav-icon">✓</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">QCR</span>
                        <span class="internal-nav-desc">Quality control review</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.recruitment') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.recruitment') ? 'active' : '' }}">
                    <span class="internal-nav-icon">✦</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Recruitment</span>
                        <span class="internal-nav-desc">Hiring pipeline overview</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.timelineRecruitment') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.timelineRecruitment') ? 'active' : '' }}">
                    <span class="internal-nav-icon">⧗</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Timeline Recruitment</span>
                        <span class="internal-nav-desc">Tahapan dan progress hiring</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.fulfillmentTraining') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.fulfillmentTraining') ? 'active' : '' }}">
                    <span class="internal-nav-icon">▣</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Fulfillment Training</span>
                        <span class="internal-nav-desc">Ketersediaan training</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.retrainingCrew') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.retrainingCrew') ? 'active' : '' }}">
                    <span class="internal-nav-icon">↺</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Retraining Crew</span>
                        <span class="internal-nav-desc">Evaluasi pembinaan crew</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.trainingLeader') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.trainingLeader') ? 'active' : '' }}">
                    <span class="internal-nav-icon">◆</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Training Leader</span>
                        <span class="internal-nav-desc">Monitoring leadership training</span>
                    </span>
                </a>
            </div>

            <div class="internal-nav-group">
                <div class="internal-nav-label">Business Units</div>
                <a href="{{ route('investor.sales.dashboardBOD.rto') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.rto') ? 'active' : '' }}">
                    <span class="internal-nav-icon">◉</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">RTO</span>
                        <span class="internal-nav-desc">Readiness to open outlet</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.kemitraan') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.kemitraan') ? 'active' : '' }}">
                    <span class="internal-nav-icon">⬡</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Kemitraan</span>
                        <span class="internal-nav-desc">Partnership performance</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.leadsKemitraan') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.leadsKemitraan') ? 'active' : '' }}">
                    <span class="internal-nav-icon">◌</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Leads Kemitraan</span>
                        <span class="internal-nav-desc">Lead funnel kemitraan</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.controlBudget') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.controlBudget') ? 'active' : '' }}">
                    <span class="internal-nav-icon">▤</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Control Budget</span>
                        <span class="internal-nav-desc">Budget & financial control</span>
                    </span>
                </a>
            </div>

            <div class="internal-nav-group">
                <div class="internal-nav-label">Operational</div>
                <a href="{{ route('investor.sales.dashboardBOD.otif') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.otif') ? 'active' : '' }}">
                    <span class="internal-nav-icon">▦</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">OTIF</span>
                        <span class="internal-nav-desc">On time in full</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.cro') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.cro') ? 'active' : '' }}">
                    <span class="internal-nav-icon">☰</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">CRO</span>
                        <span class="internal-nav-desc">Customer relation ops</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.cs') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.cs') ? 'active' : '' }}">
                    <span class="internal-nav-icon">✆</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">CS</span>
                        <span class="internal-nav-desc">Customer service board</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.ecommerce') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.ecommerce') ? 'active' : '' }}">
                    <span class="internal-nav-icon">▥</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">E-Commerce</span>
                        <span class="internal-nav-desc">Digital channel performance</span>
                    </span>
                </a>
                <a href="{{ route('investor.sales.dashboardBOD.mappingMarket') }}" class="internal-nav-link {{ request()->routeIs('investor.sales.dashboardBOD.mappingMarket') ? 'active' : '' }}">
                    <span class="internal-nav-icon">⌖</span>
                    <span class="internal-nav-text">
                        <span class="internal-nav-title">Mapping Market</span>
                        <span class="internal-nav-desc">Zona & market opportunity</span>
                    </span>
                </a>
            </div>
        </nav>
    </aside>

    <div class="internal-main">
        <header class="internal-topbar">
            <div class="internal-topbar-left">
                <button type="button" class="internal-toggle" id="internalSidebarToggle" aria-label="Toggle Sidebar">☰</button>
                <div>
                    <div class="internal-topbar-title">{{ $pageTitle ?? 'Board of Directors Dashboard' }}</div>
                    <div class="internal-topbar-sub">Market-style dark dashboard untuk internal monitoring</div>
                </div>
            </div>

            <div class="internal-topbar-right">
                <div class="internal-pill"><span class="internal-dot"></span> Live Internal</div>
                <div class="internal-pill">{{ now()->format('d M Y H:i') }}</div>
            </div>
        </header>

        <div class="internal-content">

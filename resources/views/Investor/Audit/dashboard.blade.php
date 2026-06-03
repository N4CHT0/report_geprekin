<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Audit Harian</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root{
            --tb-body-bg:#f6f8fb;
            --tb-card-bg:#ffffff;
            --tb-card-border:#e6ebf2;
            --tb-text:#182433;
            --tb-muted:#667085;
            --tb-primary:#206bc4;
            --tb-primary-dark:#174e91;
            --tb-primary-soft:#edf4ff;
            --tb-success:#2fb344;
            --tb-danger:#d63939;
            --tb-warning:#f59f00;
            --tb-info:#4299e1;
            --tb-radius:18px;
            --tb-radius-sm:12px;
            --tb-shadow:0 1px 2px rgba(16,24,40,.04), 0 8px 24px rgba(16,24,40,.06);
        }

        body{
            background:var(--tb-body-bg);
            color:var(--tb-text);
            font-family:'Inter','Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
            margin:0;
        }

        .navbar-audit{
            background:linear-gradient(90deg, #163a5a, #206bc4);
            box-shadow:0 4px 14px rgba(0,0,0,.08);
        }

        .navbar-brand{
            font-weight:700;
            letter-spacing:.2px;
        }

        .hero{
            background:linear-gradient(135deg, #206bc4 0%, #2f7fdb 100%);
            color:#fff;
            border-radius:0 0 28px 28px;
            padding:42px 20px 54px;
            margin-bottom:28px;
            box-shadow:0 10px 20px rgba(31,78,121,.14);
        }

        .hero-logo{
            width:78px;
            height:78px;
            border-radius:50%;
            background:#fff;
            object-fit:cover;
            padding:8px;
            border:3px solid rgba(255,255,255,.55);
        }

        .hero-title{
            font-weight:700;
            margin-top:12px;
            margin-bottom:8px;
        }

        .hero-subtitle{
            opacity:.92;
            margin-bottom:0;
        }

        .audit-shell{
            max-width:1240px;
        }

        .audit-card{
            background:var(--tb-card-bg);
            border:1px solid var(--tb-card-border);
            border-radius:var(--tb-radius);
            box-shadow:var(--tb-shadow);
        }

        .audit-card-header{
            padding:20px 22px 0;
        }

        .audit-card-body{
            padding:22px;
        }

        .audit-title{
            font-size:1.1rem;
            font-weight:700;
            margin-bottom:6px;
            color:#0f172a;
        }

        .audit-subtitle{
            margin:0;
            color:var(--tb-muted);
            font-size:.94rem;
        }

        .activity-list .item{
            display:flex;
            gap:14px;
            align-items:flex-start;
            padding:16px 0;
            border-bottom:1px solid #edf2f7;
            transition:.18s ease;
        }

        .activity-list .item:hover{
            transform:translateX(2px);
        }

        .activity-list .item:last-child{
            border-bottom:none;
        }

        .time-badge{
            min-width:82px;
            text-align:center;
            background:var(--tb-primary-soft);
            color:var(--tb-primary);
            padding:8px 12px;
            border-radius:999px;
            font-weight:700;
            font-size:.92rem;
        }

        .slot-status{
            font-size:.85rem;
            font-weight:700;
            margin-bottom:4px;
        }

        .slot-status.done{ color:var(--tb-success); }
        .slot-status.active{ color:var(--tb-primary); }
        .slot-status.late_tolerance{ color:var(--tb-warning); }
        .slot-status.locked{ color:var(--tb-danger); }
        .slot-status.waiting{ color:#6c757d; }

        .audit-toolbar{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            flex-wrap:wrap;
        }

        .audit-slot-title{
            font-size:1.35rem;
            font-weight:700;
            line-height:1.15;
            margin:0 0 4px;
            color:#0f172a;
        }

        .audit-slot-meta{
            color:var(--tb-muted);
            font-size:.95rem;
            margin:0;
        }

        .audit-btn{
            min-height:44px;
            border-radius:12px;
            font-weight:600;
            padding:.625rem 1rem;
        }

        .audit-btn-light{
            background:#fff;
            border:1px solid #d8e1ec;
            color:#243447;
        }

        .audit-btn-light:hover{
            background:#f8fafc;
            border-color:#cbd5e1;
            color:#0f172a;
        }

        .audit-banner{
            border:1px solid var(--tb-card-border);
            border-radius:14px;
            padding:14px 16px;
            background:#f8fafc;
            color:#334155;
            box-shadow:var(--tb-shadow);
        }

        .audit-banner.secondary{
            background:#f3f4f6;
        }

        .audit-banner.success{
            background:#ecfdf3;
            border-color:#d1fadf;
            color:#027a48;
        }

        .status-alert{
            border-radius:14px;
            border:none;
            box-shadow:var(--tb-shadow);
        }

        .audit-form-grid{
            display:grid;
            grid-template-columns:repeat(12, minmax(0, 1fr));
            gap:16px;
            margin-bottom:20px;
        }

        .audit-col-3{ grid-column:span 3; }
        .audit-col-4{ grid-column:span 4; }
        .audit-col-6{ grid-column:span 6; }
        .audit-col-12{ grid-column:span 12; }

        .audit-field{
            display:flex;
            flex-direction:column;
            gap:8px;
        }

        .audit-label{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:8px;
            font-size:.875rem;
            font-weight:700;
            color:#344054;
            margin:0;
        }

        .audit-label small{
            font-weight:500;
            color:var(--tb-muted);
        }

        .audit-help{
            font-size:.8rem;
            color:var(--tb-muted);
            margin-top:2px;
        }

        .audit-chip{
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:6px 10px;
            border-radius:999px;
            background:var(--tb-primary-soft);
            color:var(--tb-primary);
            font-size:.78rem;
            font-weight:700;
            border:1px solid #dbe8ff;
        }

        .form-control,
        .form-select{
            min-height:46px;
            border-radius:12px;
            border:1px solid #d5dde7;
            box-shadow:none;
        }

        .form-control:focus,
        .form-select:focus{
            border-color:#9ec5fe;
            box-shadow:0 0 0 .2rem rgba(32,107,196,.12);
        }

        .audit-question-card{
            border:1px solid var(--tb-card-border);
            border-radius:16px;
            background:#fff;
            padding:18px;
            margin-bottom:16px;
            transition:.18s ease;
        }

        .audit-question-card:hover{
            box-shadow:0 10px 24px rgba(15,23,42,.05);
            border-color:#d7dee8;
        }

        .audit-question-head{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:12px;
            margin-bottom:14px;
            flex-wrap:wrap;
        }

        .audit-question-title{
            font-size:1rem;
            line-height:1.55;
            font-weight:700;
            color:#0f172a;
            margin:0;
            flex:1 1 0;
            min-width:0;
        }

        .audit-answer-wrap{
            display:flex;
            align-items:center;
            gap:18px;
            flex-wrap:wrap;
        }

        .audit-answer-wrap .form-check{
            margin:0;
            padding:0;
            display:flex;
            align-items:center;
            gap:8px;
            min-height:20px;
        }

        .audit-answer-wrap .form-check-input{
            float:none;
            margin:0;
            width:18px;
            height:18px;
            cursor:pointer;
        }

        .audit-answer-wrap .form-check-label{
            margin:0;
            cursor:pointer;
            font-weight:500;
            color:#475467;
        }

        .form-check-input:checked{
            background-color:var(--tb-primary);
            border-color:var(--tb-primary);
        }

        .audit-extra-box{
            margin-top:16px;
            border:1px dashed #cfd8e3;
            border-radius:14px;
            background:#f8fbff;
            padding:14px;
        }

        .audit-camera-box{
            background:#fff;
            border:1px solid #dbe7f3;
            border-radius:14px;
            padding:14px;
        }

        .audit-camera-frame{
            width:100%;
            min-height:220px;
            max-height:360px;
            object-fit:cover;
            background:#0f172a;
            border-radius:12px;
            border:1px solid #d5deea;
        }

        .audit-camera-placeholder{
            border:1px dashed #c8d3df;
            border-radius:12px;
            min-height:220px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:var(--tb-muted);
            background:#f8fafc;
            text-align:center;
            padding:18px;
        }

        .btn-audit-primary{
            background:var(--tb-primary);
            border-color:var(--tb-primary);
            color:#fff;
            border-radius:12px;
            font-weight:600;
            min-height:42px;
        }

        .btn-audit-primary:hover{
            background:var(--tb-primary-dark);
            border-color:var(--tb-primary-dark);
            color:#fff;
        }

        .btn-audit-light{
            background:#fff;
            border:1px solid #cfd8e3;
            color:#344054;
            border-radius:12px;
            font-weight:600;
            min-height:42px;
        }

        .btn-audit-success{
            background:var(--tb-success);
            border-color:var(--tb-success);
            color:#fff;
            border-radius:12px;
            font-weight:600;
            min-height:44px;
        }

        .btn-audit-success:hover{
            background:#24963a;
            border-color:#24963a;
            color:#fff;
        }

        .info-note{
            font-size:.88rem;
            color:var(--tb-muted);
        }

        .audit-submit-wrap{
            position:sticky;
            bottom:12px;
            z-index:20;
            background:rgba(255,255,255,.92);
            backdrop-filter:blur(10px);
            border:1px solid var(--tb-card-border);
            border-radius:14px;
            padding:12px;
            display:flex;
            justify-content:flex-end;
            margin-top:20px;
            box-shadow:0 10px 30px rgba(15,23,42,.08);
        }

        .audit-submit-btn{
            min-height:46px;
            border-radius:12px;
            padding:.7rem 1.2rem;
            font-weight:700;
            background:var(--tb-success);
            border-color:var(--tb-success);
            color:#fff;
        }

        .audit-submit-btn:hover{
            background:#24963a;
            border-color:#24963a;
            color:#fff;
        }

        .footer-audit{
            margin-top:42px;
            background:#163a5a;
            color:#fff;
            text-align:center;
            padding:18px 10px;
            border-radius:24px 24px 0 0;
        }

        .select2-container{
            width:100% !important;
        }

        .select2-container--bootstrap-5 .select2-selection{
            min-height:46px !important;
            border-radius:12px !important;
            border:1px solid #d5dde7 !important;
            background:#fff !important;
            box-shadow:none !important;
            display:flex !important;
            align-items:center !important;
            padding:4px 12px !important;
        }

        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection{
            border-color:#9ec5fe !important;
            box-shadow:0 0 0 .2rem rgba(32,107,196,.12) !important;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered{
            color:#101828 !important;
            padding-left:0 !important;
            line-height:1.4 !important;
            font-weight:500;
        }

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow{
            height:44px !important;
            right:10px !important;
        }

        .select2-dropdown{
            border:1px solid #dbe3ec !important;
            border-radius:12px !important;
            overflow:hidden;
            box-shadow:0 20px 30px rgba(15,23,42,.08);
        }

        .select2-search--dropdown .select2-search__field{
            border:1px solid #d5dde7 !important;
            border-radius:10px !important;
            padding:8px 10px !important;
        }

        @media (max-width: 1199.98px){
            .audit-col-3{ grid-column:span 6; }
            .audit-col-4{ grid-column:span 6; }
        }

        @media (max-width: 767.98px){
            .hero{
                padding:32px 16px 42px;
            }

            .hero-title{
                font-size:1.35rem;
            }

            .audit-card-header{
                padding:18px 18px 0;
            }

            .audit-card-body{
                padding:18px;
            }

            .time-badge{
                min-width:70px;
                font-size:.84rem;
            }

            .audit-col-3,
            .audit-col-4,
            .audit-col-6,
            .audit-col-12{
                grid-column:span 12;
            }

            .audit-slot-title{
                font-size:1.1rem;
            }

            .audit-toolbar{
                align-items:stretch;
            }

            .audit-toolbar .d-flex{
                width:100%;
            }

            .audit-toolbar .d-flex .audit-btn{
                flex:1 1 0;
            }

            .audit-question-card{
                padding:14px;
            }

            .audit-camera-frame,
            .audit-camera-placeholder{
                min-height:190px;
            }

            .audit-submit-wrap{
                padding:10px;
            }

            .audit-submit-btn{
                width:100%;
            }
            
            .audit-camera-frame.mirror {
                transform: scaleX(-1);
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-audit">
    <div class="container audit-shell">
        <a class="navbar-brand" href="#">Dashboard Outlet</a>

        <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
            <span class="text-white small">
                👤 {{ auth()->user()->name ?? session('responden_nama') ?? 'User' }}
                ({{ auth()->user()->email ?? session('responden_username') ?? '-' }})
            </span>

            @if(auth()->check() || session()->has('responden_id'))
                <form action="{{ route('auditDashboard.auditLogout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Logout</button>
                </form>
            @else
                <a href="{{ route('auditDashboard.auditLogin') }}" class="btn btn-outline-light btn-sm">Login / Daftar</a>
            @endif
        </div>
    </div>
</nav>

<section class="hero">
    <div class="container audit-shell text-center">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="hero-logo">
        <h2 class="hero-title">Halo, {{ auth()->user()->name ?? session('responden_nama') ?? 'Responden' }}</h2>
        <p class="hero-subtitle">Silakan isi audit harian sesuai outlet, PIC, jam aktivitas, dan bukti lapangan.</p>
    </div>
</section>

<section class="container audit-shell mb-4">
    <div class="audit-card">
        <div class="audit-card-header">
            <h5 class="audit-title">Standar Aktivitas Harian</h5>
            <p class="audit-subtitle">Checklist aktivitas di bawah ini mengikuti seluruh data pertanyaan DCR.</p>
        </div>
        <div class="audit-card-body">
            <div class="activity-list">
                @forelse($slotTimeline ?? collect() as $slot)
                    <a href="{{ route('auditDashboard.index', ['view_jam' => $slot->jam]) }}"
                       class="text-decoration-none text-dark">
                        <div class="item">
                            <span class="time-badge">{{ $slot->label }}</span>

                            <div class="flex-grow-1">
                                <div class="slot-status {{ $slot->status }}">
                                    @if($slot->status === 'done')
                                        Sudah diisi
                                    @elseif($slot->status === 'active')
                                        Sedang aktif
                                    @elseif($slot->status === 'late_tolerance')
                                        Terlambat (masih bisa diisi)
                                    @elseif($slot->status === 'locked')
                                        Terkunci
                                        @elseif($slot->status === 'manual_open')
    Dibuka sementara
                                    @else
                                        Menunggu
                                    @endif

                                    <span class="text-muted fw-normal">
                                        • {{ $slot->start->format('H:i') }} - {{ $slot->end->format('H:i') }} WIB
                                    </span>
                                </div>

                                @if(($slot->items ?? collect())->count() > 0)
                                    @foreach($slot->items as $q)
                                        <div>{{ $q->pertanyaan }}</div>
                                    @endforeach
                                @else
                                    <div class="text-muted">Tidak ada pertanyaan pada slot ini.</div>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-muted">Belum ada data pertanyaan DCR.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="container audit-shell mb-5">
    @if(session('success'))
        <div class="alert alert-success status-alert">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger status-alert">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger status-alert">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(auth()->check() || session()->has('responden_id'))

        @if(!empty($slotAktif))
            <div class="alert alert-info status-alert">
                Slot audit aktif saat ini:
                <strong>{{ \Carbon\Carbon::parse($slotAktif['jam_aktivitas'])->format('H:i') }} WIB</strong>
                • Berlaku sampai
                <strong>{{ $slotAktif['end']->format('H:i') }} WIB</strong>

                @if(($slotAktif['status'] ?? null) === 'late_tolerance')
                    • <strong>Dalam toleransi keterlambatan {{ $lateToleranceMinutes }} menit</strong>
                @endif
            </div>
        @endif

        @if(!empty($selectedSlot))
            <div class="audit-card mb-4">
                <div class="audit-card-body">
                    <div class="audit-toolbar">
                        <div>
                            <h4 class="audit-slot-title">
                                Preview Slot {{ \Carbon\Carbon::parse($selectedSlot['jam_aktivitas'])->format('H:i') }} WIB
                            </h4>
                            <p class="audit-slot-meta">
                                Berlaku {{ $selectedSlot['start']->format('H:i') }} - {{ $selectedSlot['end']->format('H:i') }} WIB
                                <br>
                                Toleransi akses sampai {{ $selectedSlot['late_tolerance_end']->format('H:i') }} WIB
                            </p>
                        </div>

                        <div class="d-flex gap-2">
                            @if(!empty($prevJam))
                                <a href="{{ route('auditDashboard.index', ['view_jam' => $prevJam]) }}"
                                   class="btn audit-btn audit-btn-light">
                                    ← Prev
                                </a>
                            @endif

                            @if(!empty($nextJam))
                                <a href="{{ route('auditDashboard.index', ['view_jam' => $nextJam]) }}"
                                   class="btn audit-btn audit-btn-light">
                                    Next →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($selectedSlotWarning))
            <div class="alert {{ $isSelectedSlotLocked ? 'alert-danger' : 'alert-warning' }} status-alert">
                {{ $selectedSlotWarning }}
            </div>
        @endif

        @if(!empty($selectedSlot) && !$isSelectedSlotAccessible)
            <div class="audit-banner secondary mb-3">
                Slot ini belum aktif atau sudah melewati batas toleransi, sehingga tidak bisa disubmit.
            </div>
        @endif

        @if(!empty($alreadyFilledSelectedSlot))
            <div class="audit-banner success mb-3">
                Slot ini sudah pernah Anda isi.
            </div>
        @endif

        @if(!empty($selectedSlot) && $questions->isNotEmpty())
            <div class="audit-card">
                <div class="audit-card-header">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h5 class="audit-title">Form Audit Harian</h5>
                            <p class="audit-subtitle">
                                Pilih outlet, PIC, dan jam aktivitas. Setelah itu isi semua pertanyaan yang muncul.
                            </p>
                        </div>

                        <span class="audit-chip">
                            Slot {{ \Carbon\Carbon::parse($viewJam)->format('H:i') }}
                        </span>
                    </div>
                </div>

                <div class="audit-card-body">
                    <form action="{{ route('investor.internal.audit.store') }}" method="POST" id="auditForm">
                        @csrf

                        <div class="audit-form-grid">
                            <div class="audit-col-3">
                                <div class="audit-field">
                                    <label class="audit-label">Pilih Outlet</label>
                            
                                    <select
                                        class="form-select"
                                        name="outlet_id"
                                        id="outletSelect"
                                        {{ !empty($lockedOutletId) ? 'disabled' : '' }}
                                        required
                                    >
                                        <option value="">-- Pilih Outlet --</option>
                                        @foreach($outlets as $outlet)
                                            @php
                                                $namaOutletBersih = trim(preg_replace('/\s*\[[^\]]*\]\s*$/', '', (string) $outlet->nama_outlet));
                                            @endphp
                                            <option value="{{ $outlet->id }}"
                                                {{ (string)($selectedOutletId ?? '') === (string)$outlet->id ? 'selected' : '' }}>
                                                {{ $namaOutletBersih }}
                                            </option>
                                        @endforeach
                                    </select>
                            
                                    <div class="audit-help">
                                        @if(!empty($lockedOutletId))
                                            Outlet mengikuti akun yang login dan tidak bisa diubah.
                                        @else
                                            Pilih outlet tempat audit dilakukan.
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="audit-col-3">
                                <div class="audit-field">
                                    <label class="audit-label">Nama PIC</label>
                                    <input type="text"
                                           class="form-control"
                                           name="nama_pic"
                                           id="namaPic"
                                           placeholder="Masukkan nama PIC"
                                           value="{{ old('nama_pic') }}"
                                           {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}
                                           required>
                                    <div class="audit-help">Isi nama PIC yang sedang bertugas saat audit dilakukan.</div>
                                </div>
                            </div>

                            <div class="audit-col-3">
                                <div class="audit-field">
                                    <label class="audit-label">Leader</label>
                                    <select class="form-select" name="leader_pic_id" id="leaderSelect" required
                                        {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'true' : '' }}>
                                        <option value="">-- Pilih Leader --</option>
                                        @foreach($leaderPics as $pic)
                                            <option value="{{ $pic->pic_id }}">{{ $pic->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="audit-col-3">
                                <div class="audit-field">
                                    <label class="audit-label">SPV</label>
                                    <select class="form-select" name="spv_pic_id" id="spvSelect" required
                                        {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'true' : '' }}>
                                        <option value="">-- Pilih SPV --</option>
                                        @foreach($spvPics as $pic)
                                            <option value="{{ $pic->pic_id }}">{{ $pic->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="audit-col-3">
                                <div class="audit-field">
                                    <label class="audit-label">TM</label>
                                    <select class="form-select" name="tm_pic_id" id="tmSelect" required
                                        {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'true' : '' }}>
                                        <option value="">-- Pilih TM --</option>
                                        @foreach($tmPics as $pic)
                                            <option value="{{ $pic->pic_id }}">{{ $pic->nama_lengkap }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="audit-col-4">
                                <div class="audit-field">
                                    <label class="audit-label">
                                        Pilih Jam Aktivitas
                                        <small>slot pertanyaan aktif</small>
                                    </label>
                                    <select class="form-select" name="jam_aktivitas" id="pilihJam" required
                                        {{ $alreadyFilledSelectedSlot ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Jam Aktivitas --</option>
                                        @foreach($jamAuditList ?? [] as $jam)
                                            <option value="{{ $jam }}" {{ (($viewJam ?? '') == $jam) ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::parse($jam)->format('H:i') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="areaPertanyaan">
                            @foreach($allQuestions as $p)
                                @php
                                    $jamItem = \Carbon\Carbon::parse($p->jam)->format('H:i:s');
                                    $isVisible = (($viewJam ?? '') === $jamItem);
                                @endphp
                        
                                <div class="audit-question-card pertanyaan-item"
                                     data-jam="{{ $jamItem }}"
                                     style="{{ $isVisible ? 'display:block;' : 'display:none;' }}">
                        
                                    <div class="audit-question-head">
                                        <h6 class="audit-question-title">{{ $p->pertanyaan }}</h6>
                                        <span class="audit-chip">Checklist</span>
                                    </div>
                        
                                    <div class="audit-answer-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="radio"
                                                   name="pertanyaan_{{ $p->id }}"
                                                   value="Ya"
                                                   id="ya_{{ $p->id }}"
                                                   onclick="toggleInput('{{ $p->id }}','ya')"
                                                   {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="ya_{{ $p->id }}">Ya</label>
                                        </div>
                        
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="radio"
                                                   name="pertanyaan_{{ $p->id }}"
                                                   value="Tidak"
                                                   id="tidak_{{ $p->id }}"
                                                   onclick="toggleInput('{{ $p->id }}','tidak')"
                                                   {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="tidak_{{ $p->id }}">Tidak</label>
                                        </div>
                                    </div>
                        
                                    <div id="foto_{{ $p->id }}" class="audit-extra-box d-none">
                                        <label class="audit-label mb-2">Ambil Foto Bukti</label>
                        
                                        <div class="audit-camera-box">
                                            <div id="placeholder_{{ $p->id }}" class="audit-camera-placeholder">
                                                Kamera belum dibuka. Tekan tombol <strong class="ms-1">Buka Kamera</strong>.
                                            </div>
                        
                                            <video id="video_{{ $p->id }}" class="audit-camera-frame d-none" autoplay playsinline></video>
                                            <canvas id="canvas_{{ $p->id }}" class="d-none"></canvas>
                        
                                            <div id="preview_list_{{ $p->id }}" class="mt-3 d-flex flex-wrap gap-2"></div>
                                            <div id="foto_inputs_{{ $p->id }}"></div>
                        
                                            <div class="d-flex flex-wrap gap-2 mt-3">
                                                <button type="button"
                                                        class="btn btn-audit-light"
                                                        id="start_btn_{{ $p->id }}"
                                                        onclick="startCamera('{{ $p->id }}')"
                                                        {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                    Buka Kamera
                                                </button>
                                                
                                                <button type="button"
                                                        class="btn btn-audit-light d-none"
                                                        id="switch_camera_btn_{{ $p->id }}"
                                                        onclick="switchCamera('{{ $p->id }}')"
                                                        {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                    Selfie / Kamera Depan
                                                </button>
                        
                                                <button type="button"
                                                        class="btn btn-audit-primary d-none"
                                                        id="capture_btn_{{ $p->id }}"
                                                        onclick="capturePhoto('{{ $p->id }}')"
                                                        {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                    Ambil Foto
                                                </button>
                        
                                                <button type="button"
                                                        class="btn btn-audit-light d-none"
                                                        id="add_more_btn_{{ $p->id }}"
                                                        onclick="startCamera('{{ $p->id }}')"
                                                        {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                    Tambah Foto
                                                </button>
                        
                                                <button type="button"
                                                        class="btn btn-audit-light d-none"
                                                        id="cancel_camera_btn_{{ $p->id }}"
                                                        onclick="cancelCamera('{{ $p->id }}')"
                                                        {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                    Tutup Kamera
                                                </button>
                                            </div>
                        
                                            <div class="info-note mt-2">
                                                Foto wajib diambil langsung dari kamera. Bisa upload lebih dari 1 foto per pertanyaan.
                                            </div>
                                        </div>
                                    </div>
                        
                                    <div id="alasan_{{ $p->id }}" class="audit-extra-box d-none">
                                        <label class="audit-label mb-2">Alasan</label>
                                        <input type="text"
                                               name="pertanyaan_{{ $p->id }}_alasan"
                                               class="form-control"
                                               placeholder="Tuliskan alasan"
                                               {{ (!$isSelectedSlotAccessible || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($isSelectedSlotAccessible && !$alreadyFilledSelectedSlot)
                            <div class="audit-submit-wrap">
                                <button type="submit" class="btn audit-submit-btn">
                                    Submit Audit
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        @elseif(empty($selectedSlot))
            <div class="alert alert-warning status-alert">Saat ini tidak ada sesi audit yang bisa ditampilkan.</div>
        @endif

    @else
        <div class="alert alert-danger status-alert">
            <h5 class="mb-2">Anda belum login</h5>
            <p class="mb-3">Silakan login atau registrasi terlebih dahulu untuk mengisi audit harian.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('auditDashboard.auditLogin') }}" class="btn btn-audit-primary">Login</a>
                <a href="{{ route('auditDashboard.auditRegistrasi') }}" class="btn btn-audit-light">Registrasi</a>
            </div>
        </div>
    @endif
</section>

<footer class="footer-audit">
    <div class="container audit-shell">
        <p class="mb-0">© 2026 Audit Outlet. All Rights Reserved.</p>
    </div>
</footer>

<div class="modal fade" id="auditReminderModal" tabindex="-1" aria-labelledby="auditReminderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; overflow:hidden;">
            <div class="modal-header border-0" style="background:#edf4ff;">
                <div>
                    <h5 class="modal-title fw-bold" id="auditReminderModalLabel">
                        Peringatan Pengisian Audit
                    </h5>
                    <div class="text-muted small">
                        Mohon dibaca sebelum mengisi form.
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3" style="border-radius:14px;">
                    Isi data DCR sesuai dengan <strong>nama outlet yang benar</strong> dan kondisi lapangan yang sebenarnya.
                </div>
                <p class="mb-0">
                    Pastikan semua jawaban, alasan, dan foto bukti diisi dengan <strong>jujur</strong>, karena data ini menjadi acuan evaluasi outlet.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-audit-primary" data-bs-dismiss="modal">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let cameraStreams = {};
    let cameraFacing = {};
    
    function stopCamera(id) {
        if (cameraStreams[id]) {
            cameraStreams[id].getTracks().forEach(track => track.stop());
            cameraStreams[id] = null;
        }
    }

    function resetCameraOnly(id) {
        const video = document.getElementById('video_' + id);
        const placeholder = document.getElementById('placeholder_' + id);
        const captureBtn = document.getElementById('capture_btn_' + id);
        const startBtn = document.getElementById('start_btn_' + id);
        const cancelBtn = document.getElementById('cancel_camera_btn_' + id);
        const switchBtn = document.getElementById('switch_camera_btn_' + id);
    
        stopCamera(id);
    
        video.srcObject = null;
        video.classList.add('d-none');
        video.classList.remove('mirror');
    
        captureBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    
        if (switchBtn) {
            switchBtn.classList.add('d-none');
        }
    
        const previewList = document.getElementById('preview_list_' + id);
        if (previewList.children.length === 0) {
            placeholder.classList.remove('d-none');
            startBtn.classList.remove('d-none');
        }
    }

    function clearAllPhotos(id) {
        const previewList = document.getElementById('preview_list_' + id);
        const hiddenContainer = document.getElementById('foto_inputs_' + id);
        const placeholder = document.getElementById('placeholder_' + id);
        const startBtn = document.getElementById('start_btn_' + id);
        const addMoreBtn = document.getElementById('add_more_btn_' + id);
        const switchBtn = document.getElementById('switch_camera_btn_' + id);
        const video = document.getElementById('video_' + id);
        const captureBtn = document.getElementById('capture_btn_' + id);
        const cancelBtn = document.getElementById('cancel_camera_btn_' + id);
    
        stopCamera(id);
    
        previewList.innerHTML = '';
        hiddenContainer.innerHTML = '';
    
        placeholder.classList.remove('d-none');
        startBtn.classList.remove('d-none');
        addMoreBtn.classList.add('d-none');
    
        if (switchBtn) {
            switchBtn.classList.add('d-none');
        }
    
        video.classList.add('d-none');
        video.classList.remove('mirror');
        captureBtn.classList.add('d-none');
        cancelBtn.classList.add('d-none');
    
        cameraFacing[id] = 'environment';
    }

    function cancelCamera(id) {
        resetCameraOnly(id);
    }

    function toggleInput(id, tipe) {
        const fotoBox = document.getElementById('foto_' + id);
        const alasanBox = document.getElementById('alasan_' + id);
        const alasanInput = alasanBox.querySelector('input[type="text"]');

        if (tipe === 'ya') {
            fotoBox.classList.remove('d-none');
            alasanBox.classList.add('d-none');
            alasanInput.required = false;
            alasanInput.value = '';
        }

        if (tipe === 'tidak') {
            alasanBox.classList.remove('d-none');
            fotoBox.classList.add('d-none');
            alasanInput.required = true;
            clearAllPhotos(id);
        }
    }

    async function startCamera(id) {
        const startBtn = document.getElementById('start_btn_' + id);
        const addMoreBtn = document.getElementById('add_more_btn_' + id);
    
        if ((startBtn && startBtn.disabled) || (addMoreBtn && addMoreBtn.disabled)) return;
    
        try {
            stopCamera(id);
    
            const video = document.getElementById('video_' + id);
            const placeholder = document.getElementById('placeholder_' + id);
            const captureBtn = document.getElementById('capture_btn_' + id);
            const cancelBtn = document.getElementById('cancel_camera_btn_' + id);
            const switchBtn = document.getElementById('switch_camera_btn_' + id);
    
            if (!cameraFacing[id]) {
                cameraFacing[id] = 'environment';
            }
    
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: cameraFacing[id] }
                },
                audio: false
            });
    
            cameraStreams[id] = stream;
            video.srcObject = stream;
    
            if (cameraFacing[id] === 'user') {
                video.classList.add('mirror');
            } else {
                video.classList.remove('mirror');
            }
    
            placeholder.classList.add('d-none');
            video.classList.remove('d-none');
            captureBtn.classList.remove('d-none');
            cancelBtn.classList.remove('d-none');
            startBtn.classList.add('d-none');
    
            if (switchBtn) {
                switchBtn.classList.remove('d-none');
            }
    
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Kamera tidak tersedia',
                text: 'Pastikan izin kamera aktif di browser atau perangkat Anda.'
            });
        }
    }
    
    function switchCamera(id) {
        if (!cameraFacing[id]) {
            cameraFacing[id] = 'environment';
        }
    
        cameraFacing[id] = (cameraFacing[id] === 'environment') ? 'user' : 'environment';
        startCamera(id);
    }
    
    function capturePhoto(id) {
        const captureBtn = document.getElementById('capture_btn_' + id);
        if (captureBtn && captureBtn.disabled) return;
    
        const video = document.getElementById('video_' + id);
        const canvas = document.getElementById('canvas_' + id);
        const previewList = document.getElementById('preview_list_' + id);
        const hiddenContainer = document.getElementById('foto_inputs_' + id);
        const placeholder = document.getElementById('placeholder_' + id);
        const addMoreBtn = document.getElementById('add_more_btn_' + id);
        const startBtn = document.getElementById('start_btn_' + id);
    
        const context = canvas.getContext('2d');
    
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
    
        // kalau selfie, hasil foto jangan mirror
        if (cameraFacing[id] === 'user') {
            context.save();
            context.scale(-1, 1);
            context.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
            context.restore();
        } else {
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
        }
    
        const now = new Date();
    
        const tanggal = now.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    
        const jam = now.toLocaleTimeString('id-ID');
    
        const userName = (document.getElementById('namaPic')?.value || 'PIC').trim();
        const outletName = document.querySelector('#outletSelect option:checked')?.text || 'Outlet';
    
        const watermarkText = `${tanggal} ${jam}\n${userName}\n${outletName}`;
    
        const padding = 16;
        const lineHeight = 22;
    
        context.font = "bold 16px Arial";
        context.textBaseline = "bottom";
    
        const lines = watermarkText.split("\n");
    
        let boxWidth = 0;
    
        lines.forEach(line => {
            const metrics = context.measureText(line);
            if (metrics.width > boxWidth) boxWidth = metrics.width;
        });
    
        const boxHeight = (lines.length * lineHeight) + padding;
    
        const x = canvas.width - boxWidth - padding;
        const y = canvas.height - padding;
    
        context.fillStyle = "rgba(0, 0, 0, 0.55)";
        context.fillRect(
            x - 10,
            y - boxHeight,
            boxWidth + 20,
            boxHeight + 10
        );
    
        context.fillStyle = "#ffffff";
    
        lines.forEach((line, index) => {
            context.fillText(
                line,
                x,
                y - ((lines.length - index - 1) * lineHeight)
            );
        });
    
        const imageData = canvas.toDataURL('image/jpeg', 0.9);
    
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `pertanyaan_${id}_foto_base64[]`;
        input.value = imageData;
        hiddenContainer.appendChild(input);
    
        const wrapper = document.createElement('div');
        wrapper.className = 'position-relative';
    
        const img = document.createElement('img');
        img.src = imageData;
        img.className = 'audit-camera-frame';
        img.style.width = '140px';
        img.style.minHeight = '140px';
        img.style.maxHeight = '140px';
        img.style.objectFit = 'cover';
    
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1';
        removeBtn.innerText = '×';
        removeBtn.onclick = function () {
            wrapper.remove();
            input.remove();
    
            if (hiddenContainer.children.length === 0) {
                placeholder.classList.remove('d-none');
                startBtn.classList.remove('d-none');
                addMoreBtn.classList.add('d-none');
            }
        };
    
        wrapper.appendChild(img);
        wrapper.appendChild(removeBtn);
        previewList.appendChild(wrapper);
    
        placeholder.classList.add('d-none');
        addMoreBtn.classList.remove('d-none');
    
        resetCameraOnly(id);
    }

    $(document).ready(function () {
        const auditReminderModalEl = document.getElementById('auditReminderModal');
        if (auditReminderModalEl && window.bootstrap) {
            const auditReminderModal = new bootstrap.Modal(auditReminderModalEl, {
                backdrop: 'static',
                keyboard: true
            });
            auditReminderModal.show();
        }

        $('#outletSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih Outlet --',
            allowClear: true
        });

        $('#leaderSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih Leader --',
            allowClear: true
        });

        $('#spvSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih SPV --',
            allowClear: true
        });

        $('#tmSelect').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih TM --',
            allowClear: true
        });

        $('#pilihJam').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Pilih Jam Aktivitas --',
            allowClear: false,
            minimumResultsForSearch: Infinity
        });

        $('#pilihJam').on('change', function () {
            const jamDipilih = $(this).val();

            if (jamDipilih) {
                const url = new URL(window.location.href);
                url.searchParams.set('view_jam', jamDipilih);
                window.location.href = url.toString();
            }
        });

        let auditSubmitConfirmed = false;

        $('#auditForm').on('submit', function (e) {
            const form = this;
            const outlet = $('#outletSelect').val() || $('input[name="outlet_id"]').val();
            const outletName = ($('#outletSelect option:selected').text() || 'Outlet').trim();
            const namaPic = ($('#namaPic').val() || '').trim();
            const leader = $('#leaderSelect').val();
            const spv = $('#spvSelect').val();
            const tm = $('#tmSelect').val();
            const jam = $('#pilihJam').val();
            const visibleQuestions = $('.pertanyaan-item:visible');

            const slotLocked = @json($isSelectedSlotLocked ?? false);
            const slotAccessible = @json($isSelectedSlotAccessible ?? false);

            let valid = true;
            let message = '';

            if (slotLocked || !slotAccessible) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Slot tidak bisa diakses',
                    text: 'Slot ini sudah melewati batas toleransi 30 menit atau belum masuk waktu akses.'
                });
                return false;
            }

            if (!outlet || !namaPic || !leader || !spv || !tm || !jam) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Form belum lengkap',
                    text: 'Silakan pilih outlet, isi nama PIC, pilih leader, SPV, TM, dan jam aktivitas.'
                });
                return false;
            }

            if (visibleQuestions.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pertanyaan tidak tersedia',
                    text: 'Tidak ada pertanyaan untuk jam aktivitas yang dipilih.'
                });
                return false;
            }

            visibleQuestions.each(function () {
                const radioChecked = $(this).find('input[type=radio]:checked').length > 0;

                if (!radioChecked) {
                    valid = false;
                    message = 'Semua pertanyaan wajib dijawab.';
                    return false;
                }

                const yaChecked = $(this).find('input[type=radio][value="Ya"]:checked').length > 0;
                const tidakChecked = $(this).find('input[type=radio][value="Tidak"]:checked').length > 0;

                if (yaChecked) {
                    const hiddenPhotos = $(this).find('input[type=hidden][name$="_foto_base64[]"]');
                    if (hiddenPhotos.length === 0) {
                        valid = false;
                        message = 'Jawaban Ya wajib dilengkapi minimal 1 foto dari kamera.';
                        return false;
                    }
                }

                if (tidakChecked) {
                    const alasan = ($(this).find('input[type=text]').val() || '').trim();
                    if (!alasan) {
                        valid = false;
                        message = 'Jawaban Tidak wajib diisi alasan.';
                        return false;
                    }
                }
            });

            if (!valid) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Form belum valid',
                    text: message
                });
                return false;
            }

            if (!auditSubmitConfirmed) {
                e.preventDefault();

                Swal.fire({
                    icon: 'question',
                    title: 'Konfirmasi Outlet',
                    html: `Apakah nama outlet yang Anda pilih sudah sesuai?<br><br><strong>${outletName}</strong><br><br>Pastikan data diisi jujur sesuai kondisi lapangan.`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, sudah sesuai & submit',
                    cancelButtonText: 'Cek lagi',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        auditSubmitConfirmed = true;

                        Swal.fire({
                            icon: 'success',
                            title: 'Data dikirim',
                            text: 'Audit sedang dikirim. Terima kasih sudah mengisi sesuai outlet dan kondisi sebenarnya.',
                            timer: 1300,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        });

                        setTimeout(function () {
                            form.submit();
                        }, 700);
                    }
                });

                return false;
            }
        });

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Audit berhasil disimpan',
                text: @json(session('success')),
                confirmButtonText: 'Siap'
            });
        @endif

        window.addEventListener('beforeunload', function () {
            Object.keys(cameraStreams).forEach(id => stopCamera(id));
        });
    });
</script>

</body>
</html>
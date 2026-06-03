<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Crew</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root{
            --primary:#1f4e79;
            --primary-dark:#163a5a;
            --soft:#eef5fb;
            --border:#d9e2ec;
            --success:#20793D;
            --success-dark:#155A38;
        }

        body{
            background: linear-gradient(135deg, #f4f7fb 0%, #eaf1f8 100%);
            min-height:100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-wrap{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
        }

        .menu-card{
            width:100%;
            max-width:920px;
            background:#fff;
            border-radius:24px;
            box-shadow:0 20px 50px rgba(15, 23, 42, .10);
            overflow:hidden;
            border:1px solid #e5edf5;
        }

        .menu-header{
            background:linear-gradient(135deg, var(--primary-dark), var(--primary));
            color:#fff;
            padding:32px 28px;
        }

        .menu-title{
            font-size:2rem;
            font-weight:700;
            margin-bottom:8px;
        }

        .menu-subtitle{
            margin:0;
            opacity:.95;
        }

        .menu-body{
            padding:28px;
        }

        .info-box{
            background:var(--soft);
            border:1px solid var(--border);
            border-radius:18px;
            padding:18px 20px;
            margin-bottom:24px;
        }

        .choice-card{
            height:100%;
            border:1px solid #e6edf5;
            border-radius:20px;
            padding:24px;
            transition:.2s ease;
            background:#fff;
        }

        .choice-card:hover{
            transform:translateY(-4px);
            box-shadow:0 14px 30px rgba(15, 23, 42, .08);
            border-color:#c9d9ea;
        }

        .choice-icon{
            width:62px;
            height:62px;
            border-radius:16px;
            background:var(--soft);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
            margin-bottom:18px;
        }

        .choice-title{
            font-size:1.25rem;
            font-weight:700;
            margin-bottom:8px;
            color:#163a5a;
        }

        .choice-desc{
            color:#6b7280;
            min-height:48px;
        }

        .btn-menu{
            width:100%;
            border-radius:14px;
            padding:12px 18px;
            font-weight:600;
        }

        .btn-primary-custom{
            background:var(--primary);
            border-color:var(--primary);
            color:#fff;
        }

        .btn-primary-custom:hover{
            background:var(--primary-dark);
            border-color:var(--primary-dark);
            color:#fff;
        }

        .btn-outline-custom{
            border:1px solid var(--primary);
            color:var(--primary);
            background:#fff;
        }

        .btn-outline-custom:hover{
            background:var(--soft);
            color:var(--primary-dark);
            border-color:var(--primary-dark);
        }
        
        .btn-success-custom{
            background:var(--success);
            border-color:var(--success);
            color:#fff;
        }

        .btn-success-custom:hover{
            background:var(--success-dark);
            border-color:var(--success-dark);
            color:#fff;
        }

        @media (max-width: 576px){
            .menu-header{
                padding:24px 20px;
            }

            .menu-body{
                padding:20px;
            }

            .menu-title{
                font-size:1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="hero-wrap">
        <div class="menu-card">
            <div class="menu-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div class="menu-title">Halo, {{ $user->name }}</div>
                        <p class="menu-subtitle">
                            Silakan pilih menu kerja yang ingin dibuka.
                        </p>
                    </div>

                    <form action="{{ route('auth.investor.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm">Logout</button>
                    </form>
                </div>
            </div>

            <div class="menu-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="info-box">
                    <div class="fw-semibold mb-1">Informasi Akun</div>
                    <div>Nama: <strong>{{ $user->name }}</strong></div>
                    <div>Email: <strong>{{ $user->email }}</strong></div>
                    <div>Role: <strong>{{ $user->role }}</strong></div>
                    <div>Outlet: <strong>Hi! Silahkan Pilih Outlet Setelah Masuk Menu ya</strong></div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="choice-card">
                            <div class="choice-icon">📝</div>
                            <div class="choice-title">Formulir</div>
                            <div class="choice-desc">
                                Masuk ke halaman formulir stock dan omset yang sudah berjalan saat ini.
                            </div>

                            <form action="{{ route('crew.menus.formulir') }}" method="POST" class="mt-4">
                                @csrf
                                <button type="submit" class="btn btn-outline-custom btn-menu">
                                    Buka Formulir
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="choice-card">
                            <div class="choice-icon">✅</div>
                            <div class="choice-title">Daily Ceklis Report</div>
                            <div class="choice-desc">
                                Masuk ke dashboard audit harian sesuai outlet crew yang sedang login.
                            </div>

                            <form action="{{ route('crew.menus.daily') }}" method="POST" class="mt-4">
                                @csrf
                                <button type="submit" class="btn btn-primary-custom btn-menu">
                                    Buka Daily Ceklis Report
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="choice-card">
                            <div class="choice-icon">🎫</div>
                            <div class="choice-title">Ticketing</div>
                            <div class="choice-desc">
                                Masuk ke dashboard ticketing untuk membuat dan memonitor ticket operasional.
                            </div>

                            @if(strtolower($user->role ?? '') === 'crew')
                                <a href="{{ route('ticketing.create') }}" class="btn btn-primary-custom btn-menu mt-4">
                                    Buat Ticket
                                </a>
                            @else
                                <a href="{{ route('ticketing.dashboard') }}" class="btn btn-primary-custom btn-menu mt-4">
                                    Buka Ticketing
                                </a>
                            @endif
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="choice-card">
                            <div class="choice-icon">🛒</div>
                            <div class="choice-title">Purchase Order</div>
                            <div class="choice-desc">
                                Masuk ke dashboard dan buat permintaan PO.
                            </div>

                            <form action="{{ route('crew.menus.formPO') }}" method="POST" class="mt-4">
                               @csrf
                               <button type="submit" class="btn btn-outline-custom btn-menu">
                                   Buka Menu PO
                               </button>
                            </form>
                            <!-- <div class="mt-4">
                                <button type="button" onclick="showComingSoon()" class="btn btn-outline-custom btn-menu">
                                    Buka Menu PO
                                </button>
                            </div> -->
                        </div>
                    </div>

                <!--    <div class="col-md-6">-->
                <!--        <div class="choice-card">-->
                <!--            <div class="choice-icon">👤</div>-->
                <!--            <div class="choice-title">Ubah Data Akun</div>-->
                <!--            <div class="choice-desc">-->
                <!--                Perbarui nama dan email akun crew yang sedang login.-->
                <!--            </div>-->

                <!--            <a href="{{ route('crew.profile.form') }}" class="btn btn-outline-custom btn-menu mt-4">-->
                <!--                Ubah Data-->
                <!--            </a>-->
                <!--        </div>-->
                <!--    </div>-->

                <!--    <div class="col-md-6">-->
                <!--        <div class="choice-card">-->
                <!--            <div class="choice-icon">🔒</div>-->
                <!--            <div class="choice-title">Ubah Password</div>-->
                <!--            <div class="choice-desc">-->
                <!--                Ganti password akun untuk meningkatkan keamanan login.-->
                <!--            </div>-->

                <!--            <a href="{{ route('crew.password.form') }}" class="btn btn-outline-custom btn-menu mt-4">-->
                <!--                Ubah Password-->
                <!--            </a>-->
                <!--        </div>-->
                <!--    </div>-->
                <!--</div>-->

                <div class="mt-4 text-muted small">
                    Tidak perlu login ulang setelah memilih salah satu menu.
                </div>
            </div>
        </div>
    </div>
</body>
<script>
function showComingSoon() {
    Swal.fire({
        icon: 'info',
        title: 'Coming Soon!',
        text: 'Menu Purchase Order saat ini masih dalam tahap pengembangan. Silakan kembali lagi nanti.',
        confirmButtonColor: '#1f2d5a'
    });
}
</script>
</html>


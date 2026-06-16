<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f8fafc;">
    <div class="container py-5">
        <div class="card shadow-sm border-0 mx-auto" style="max-width:520px;border-radius:18px;">
            <div class="card-body p-4 text-center">
                <h1 class="fw-bold text-danger">403</h1>
                <h5 class="fw-bold">Anda belum memiliki akses ke halaman ini</h5>
                <p class="text-muted mt-2">
                    Akun Anda sudah login, tetapi role akun ini belum diberi permission untuk membuka halaman ini.
                    Silakan logout lalu login ulang. Jika masih sama, hubungi admin.
                </p>

                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        Login Ulang
                    </a>

                    <a href="{{ route('crew.menus') }}" class="btn btn-outline-secondary">
                        Kembali ke Menu
                    </a>

                    <form method="POST" action="{{ route('auth.investor.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            Logout
                        </button>
                    </form>
                </div>

                <div class="small text-muted mt-3">
                    Role: {{ auth()->user()->role ?? '-' }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
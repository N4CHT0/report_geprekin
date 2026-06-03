<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .forgot-card {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .08);
            padding: 28px;
        }

        .step-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #e0ecff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 14px;
        }

        .title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: #111827;
        }

        .subtitle {
            color: #6b7280;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    @php
        $step = session('reset_step', 1);
    @endphp

    <div class="forgot-card">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($step == 1)
            <div class="step-badge">Step 1 dari 3</div>
            <div class="title">Cek Email Akun</div>
            <div class="subtitle">
                Masukkan email akun. Jika email valid dan aktif, kamu bisa lanjut ubah password.
            </div>

            <form action="{{ route('password.sendOtp') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input
                        type="text"
                        name="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        placeholder="Masukkan email akun"
                        required
                    >
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('login') }}" class="btn btn-light">Kembali ke Login</a>
                    <button type="submit" class="btn btn-primary">Lanjut</button>
                </div>
            </form>
        @endif

        @if($step == 2)
            <div class="step-badge">Step 2 dari 3</div>
            <div class="title">Verifikasi OTP</div>
            <div class="subtitle">
                Masukkan OTP yang sudah dibuat untuk email:
                <strong>{{ session('reset_email') }}</strong>
            </div>

            <form action="{{ route('password.verifyOtp') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Kode OTP</label>
                    <input
                        type="text"
                        name="otp"
                        class="form-control"
                        placeholder="Masukkan 6 digit OTP"
                        required
                    >
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('password.request') }}" class="btn btn-light">Kembali</a>
                    <button type="submit" class="btn btn-primary">Verifikasi OTP</button>
                </div>
            </form>
        @endif

        @if($step == 3)
            <div class="step-badge">Step 3 dari 3</div>
            <div class="title">Buat Password Baru</div>
            <div class="subtitle">
                OTP valid. Sekarang masukkan password baru untuk akun:
                <strong>{{ session('reset_email') }}</strong>
            </div>

            <form action="{{ route('password.reset.custom') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        required
                    >
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('password.request') }}" class="btn btn-light">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
                </div>
            </form>
        @endif
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Profile</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --surface: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: rgba(15, 23, 42, .08);
            --soft: #f1f5f9;
            --accent: #0d6efd;
            --success: #16a34a;
        }

        body {
            background: radial-gradient(1200px 500px at 10% -10%, rgba(13, 110, 253, .10), transparent 60%),
                radial-gradient(900px 420px at 90% 0%, rgba(22, 163, 74, .08), transparent 55%),
                #f8fafc;
            color: var(--ink);
        }

        .page-wrap {
            min-height: 100vh;
            display: flex;
            align-items: start;
            justify-content: center;
            padding: 2rem 0;
        }

        .profile-card {
            max-width: 780px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--surface);
            box-shadow: 0 10px 30px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, rgba(13, 110, 253, .12), rgba(22, 163, 74, .10));
            border-bottom: 1px solid var(--line);
        }

        .badge-soft {
            background: rgba(13, 110, 253, .10);
            color: #0b5ed7;
            border: 1px solid rgba(13, 110, 253, .18);
            font-weight: 600;
        }

        .form-label {
            font-weight: 600;
            color: var(--ink);
            margin-bottom: .35rem;
        }

        .form-control {
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, .12);
            padding: .75rem .9rem;
        }

        .form-control:focus {
            box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .12);
            border-color: rgba(13, 110, 253, .35);
        }

        .input-group-text {
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, .12);
            background: var(--soft);
            color: var(--muted);
        }

        .hint {
            color: var(--muted);
            font-size: .9rem;
        }

        .btn {
            border-radius: 12px;
            padding: .7rem 1rem;
            font-weight: 600;
        }

        .btn-ghost {
            background: transparent;
            border: 1px solid rgba(15, 23, 42, .14);
            color: var(--ink);
        }

        .btn-ghost:hover {
            background: rgba(15, 23, 42, .04);
        }

        .btn-save {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            border: 0;
        }

        .btn-save:hover {
            filter: brightness(.98);
        }

        .divider {
            height: 1px;
            background: var(--line);
            margin: 1.25rem 0;
        }
    </style>
</head>

<body>
    <div class="container page-wrap">
        <div class="profile-card w-100">

            <!-- Header -->
            <div class="profile-header p-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge badge-soft rounded-pill px-3 py-2">
                                <i class="bi bi-person-gear me-1"></i> Pengaturan Akun
                            </span>
                        </div>
                        <h4 class="mb-1 fw-bold">Edit Profile</h4>
                        <div class="hint">Perbarui informasi akun Anda dengan aman dan rapi.</div>
                    </div>

                    <div class="d-none d-md-flex align-items-center gap-2">
                        <div class="text-end">
                            <div class="hint mb-0">Status</div>
                            <div class="fw-semibold"><i class="bi bi-shield-check me-1"></i> Akun Aktif</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4 p-md-5">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm"
                        role="alert">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-check-circle-fill mt-1"></i>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Berhasil</div>
                                <div class="small">{{ session('success') }}</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('investor.profile.update', $user->id) }}">
                    @csrf

                    <div class="row g-4">
                        <!-- Nama -->
                        <div class="col-12">
                            <label class="form-label" for="name">Nama</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input id="name" type="text" name="name" class="form-control"
                                    value="{{ old('name', $user->name) }}" placeholder="Masukkan nama lengkap"
                                    autocomplete="name" />
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-12">
                            <label class="form-label" for="email">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input id="email" type="email" name="email" class="form-control"
                                    value="{{ old('email', $user->email) }}" placeholder="nama@perusahaan.com"
                                    autocomplete="email" />
                            </div>
                            <div class="hint mt-2">
                                Pastikan email aktif untuk menerima notifikasi.
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-12">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input id="password" type="password" name="password" class="form-control"
                                    placeholder="Kosongi jika tidak ingin diubah" autocomplete="new-password" />
                            </div>
                            <div class="hint mt-2">
                                Gunakan kombinasi huruf besar, kecil, angka, dan simbol untuk keamanan.
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between">
                        <a href="{{ route('investor.sales.dashboard') }}" class="btn btn-ghost">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>

                        <button type="submit" class="btn btn-save text-white">
                            <i class="bi bi-save2 me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

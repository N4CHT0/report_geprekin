<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Audit System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        body {
            background-color: #ECEFF1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 600px;
        }

        .card-header {
            background-color: #1A237E;
            color: #fff;
            text-align: center;
            font-weight: 600;
            border-radius: 8px 8px 0 0;
            padding: 15px;
            letter-spacing: 0.5px;
        }

        .form-control:focus {
            border-color: #1A237E;
            box-shadow: none;
        }

        .btn-blue {
            background-color: #1A237E;
            color: #fff;
            width: 100%;
            font-weight: 600;
            border-radius: 5px;
            padding: 10px;
        }

        .btn-blue:hover {
            background-color: #000;
        }

        .text-small {
            font-size: 14px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        @media (max-width: 576px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">LOGIN SISTEM</div>

        <div class="card-body px-4 py-4">
            <form id="loginForm">
                @csrf
                <div class="form-group">
                    <label class="text-small">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>

                <div class="form-group">
                    <label class="text-small">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn btn-blue mt-2">Login</button>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        Belum punya akun?
                        <a href="{{ route('auditDashboard.auditRegistrasi') }}" class="text-danger font-weight-bold">Daftar</a>
                    </small>
                </div>
            </form>
        </div>

        <div class="text-center py-2 bg-light border-top">
            <small class="text-muted">© 2025 Audit System. All Rights Reserved.</small>
        </div>
    </div>

    <!-- Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('auditDashboard.auditLoginProses') }}", // ubah sesuai route BE kamu
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message || 'Login berhasil!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "{{ route('auditDashboard.index') }}";
                        });
                    },
                    error: function(err) {
                        let msg = 'Login gagal, periksa kembali data Anda!';

                        // Deteksi error spesifik dari backend
                        if (err.responseJSON?.message?.toLowerCase().includes('password')) {
                            msg = 'Password anda salah!';
                        } else if (err.responseJSON?.message?.toLowerCase().includes('username')) {
                            msg = 'Username tidak ditemukan!';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: msg
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
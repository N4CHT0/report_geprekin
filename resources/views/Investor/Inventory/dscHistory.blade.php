<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>History DSC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header fw-bold">
            History Perubahan DSC
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                Halaman ini untuk melihat riwayat perubahan data DSC.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Jam</th>
                            <th>User/Petugas</th>
                            <th>Outlet</th>
                            <th>Tanggal</th>
                            <th>Shift</th>
                            <th>Bahan</th>
                            <th>Kolom</th>
                            <th>Sebelum</th>
                            <th>Sesudah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                Belum ada data history.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">
                Kembali
            </a>
        </div>
    </div>
</div>
</body>
</html>
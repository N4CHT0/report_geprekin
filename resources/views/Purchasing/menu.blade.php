@include('Temp.Investor.header')

<style>
    /* Select2 dropdown scroll */
    .select2-container .select2-dropdown {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Biar table gak mepet */
    table.dataTable td,
    table.dataTable th {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    /* Kolom text panjang boleh wrap */
    .td-wrap {
        white-space: normal !important;
        word-break: break-word;
        min-width: 220px;
    }

    /* Biar tombol rapih */
    .btn-group-gap>* {
        margin-right: .35rem;
    }

    .btn-group-gap>*:last-child {
        margin-right: 0;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">
            <div class="mb-4">
                <h4 class="fw-bold text-primary mb-1">
                    Menu Outlet
                </h4>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Total PO</h6>
                            <h3 class="fw-bold">jhbj</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Menunggu Persetujuan</h6>
                            <h3 class="fw-bold text-warning">jbjhb</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Disetujui</h6>
                            <h3 class="fw-bold text-success">kjnjk</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Ditolak</h6>
                            <h3 class="fw-bold text-danger">mnbjk</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <h4 class="fw-bold text-primary mb-1">
                    Menu SCM
                </h4>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Total PO</h6>
                            <h3 class="fw-bold">jhbj</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Menunggu Persetujuan</h6>
                            <h3 class="fw-bold text-warning">jbjhb</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Disetujui</h6>
                            <h3 class="fw-bold text-success">kjnjk</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Ditolak</h6>
                            <h3 class="fw-bold text-danger">mnbjk</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <h4 class="fw-bold text-primary mb-1">
                    Report SCM
                </h4>
            </div>
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Total PO</h6>
                            <h3 class="fw-bold">jhbj</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Menunggu Persetujuan</h6>
                            <h3 class="fw-bold text-warning">jbjhb</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Disetujui</h6>
                            <h3 class="fw-bold text-success">kjnjk</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Ditolak</h6>
                            <h3 class="fw-bold text-danger">mnbjk</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        $('#orderTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });
</script>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika user klik "Ya", maka form di-submit
                document.getElementById('deleteForm' + id).submit();
            }
        })
    }
</script>

@include('Temp.Investor.footer')
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
                    Selamat Datang di Dashboard SCM
                </h4>
                <p class="text-muted mb-0">
                    Laporan Perjalanan Pengiriman
                </p>
            </div>
            <div class="card border-0 shadow rounded-4">
                <div class="card-body p-5">

                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h4 class="fw-bold text-primary mb-0">
                            <i class="bi bi-box-seam me-2"></i>History Pengiriman
                        </h4>
                    </div>

                    <!-- TABLE -->
                    <div class="table-responsive" style="min-height:65vh;">
                        <table id="orderTable" class="table table-striped table-bordered align-middle text-center mb-0">

                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="text-center" style="min-width:20px;">No</th>
                                    <th class="text-center" style="min-width:150px;">Nomor PO</th>
                                    <th class="text-center" style="width:200px;">No. Polisi</th>
                                    <th class="text-center" style="min-width:100px;">Tanggal Jalan</th>
                                    <th class="text-center" style="min-width:100px;">Nama Supir</th>
                                    <th class="text-center" style="min-width:100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="height:56px;">
                                    <td class="text-center">1</td>
                                    <td class="text-center">2</td>
                                    <td class="text-center">3</td>
                                    <td class="text-center">4</td>
                                    <td class="text-center">5</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-info btn-sm">
                                            <i class="bi bi-eye-fill"></i> View
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
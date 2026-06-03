@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    .card { border-radius: 10px; }
    .table thead th { 
        font-size: 0.8rem; 
        text-transform: uppercase; 
        color: #8898aa !important;
        border-bottom: 2px solid #f4f7f6;
    }
    .table tbody td { padding: 15px 10px; }
    .form-check-input { width: 1.2rem; height: 1.2rem; cursor: pointer; }
    .btn-primary { background: #3b82f6; border: none; }
    .btn-primary:hover { background: #2563eb; }
</style>

<main class="app-main">
    <div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-12">
            <h3>Laporan Purchase Order</h3>
            <p class="text-muted">Pantau aktivitas pengadaan barang terkini.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label>Pilih Tampilan Role:</label>
                    <select class="form-select w-50" onchange="updateRoleView(this.value)">
                        <option value="all">-- Semua Role --</option>
                        <option value="SCM">Role SCM (Audit & Approval)</option>
                        <option value="DC">Role DC (Packing & Shipping)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-3">
            <h5 class="card-title">Daftar Permintaan PO</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th>No PO</th>
                            <th>Outlet</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dataPO as $index => $po)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>{{ $po->no_po }}</td>
                            <td>{{ $po->nama_outlet }}</td>
                            <td>
                                <span class="badge {{ $po->status == 'Approved' ? 'bg-success' : ($po->status == 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ $po->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info text-white">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <button class="btn btn-sm btn-success role-scm d-none">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                                <button class="btn btn-sm btn-danger role-scm d-none">
                                    <i class="bi bi-x-lg"></i>
                                </button>

                                <button class="btn btn-sm btn-warning role-dc d-none">
                                    <i class="bi bi-box-seam"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function updateRoleView(role) {
        const scmElements = document.querySelectorAll('.role-scm');
        const dcElements = document.querySelectorAll('.role-dc');

        // Reset tampilan
        scmElements.forEach(el => el.classList.add('d-none'));
        dcElements.forEach(el => el.classList.add('d-none'));

        // Aktifkan sesuai role
        if (role === 'SCM') {
            scmElements.forEach(el => el.classList.remove('d-none'));
        } else if (role === 'DC') {
            dcElements.forEach(el => el.classList.remove('d-none'));
        }
    }
</script>
</main>

<script>
    $('#selectAll').click(function(e) {
        $('input[name="po_ids[]"]').prop('checked', $(this).prop('checked'));
    });
</script>

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
    $(document).ready(function() {
        // Saat modal benar-benar tertutup (hidden.bs.modal)
        $('#detailModal').on('hidden.bs.modal', function() {
            // 1. Hapus backdrop hitam yang bandel
            $('.modal-backdrop').remove();

            // 2. Hapus class 'modal-open' dari body agar bisa di-scroll
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        });
    });
</script>

<script>
    $('.btn-view-po').click(function() {
        let id = $(this).data('id');
        let status = $(this).data('status'); // Pastikan tombol View di tabel punya data-status

        // Simpan ID ke modal
        $('#detailModal').data('current-id', id);

        // LOGIKA TOMBOL:
        // Jika status BUKAN 'Waiting', disable tombol
        if (status !== 'Waiting') {
            $('#btn-approve, #btn-reject').prop('disabled', true);
            $('#btn-approve, #btn-reject').text('Sudah diproses'); // Opsional: ganti teks
        } else {
            // Jika status 'Waiting', aktifkan kembali
            $('#btn-approve').prop('disabled', false).text('Approved');
            $('#btn-reject').prop('disabled', false).text('Rejected');
        }

        // Masukkan data ke modal
        $('#modalContent').html(`
            <table class="table table-bordered">
                <tr><th>No. PO</th><td>${noPo}</td></tr>
                <tr><th>Tanggal Permintaan</th><td>${tglReq}</td></tr>
                <tr><th>Tanggal Kedatangan</th><td>${tglDatang}</td></tr>
            </table>
        `);

        // Buka modal
        var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
        myModal.show();
    });
</script>

<script>
    $(document).ready(function() {
        // 1. Saat tombol View diklik, simpan ID ke modal
        $('.btn-view-po').click(function() {
            let id = $(this).data('id');
            let noPo = $(this).data('no-po');

            // Simpan ID ke attribute modal agar bisa dipakai tombol di dalam modal
            $('#detailModal').data('current-id', id);

            // Isi konten modal
            $('#modalContent').html('Detail untuk PO: ' + noPo);
        });

        // 2. Saat tombol Approved/Rejected diklik
        $('.btn-update-status').click(function() {
            let status = $(this).data('status');
            let id = $('#detailModal').data('current-id'); // Ambil ID yang disimpan tadi

            $.ajax({
                url: "{{ route('update.status.po') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status
                },
                success: function(response) {
                    Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Status diubah ke ' + status
                        })
                        .then(() => {
                            location.reload();
                        });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan'
                    });
                }
            });
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
@include('Temp.Investor.header')
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* Jarak antara search/entries dengan tabel */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1.5rem !important;
    }

    /* Jarak antara tabel dengan info/pagination di bawah */
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1.5rem !important;
    }

    /* Memastikan input search dan select entries rapi */
    .dataTables_wrapper .form-control-sm,
    .dataTables_wrapper .form-select-sm {
        border-radius: 8px;
        border: 1px solid #dce1e7;
    }

    /* Fix table-responsive agar tidak memotong dropdown */
    .table-responsive {
        overflow: visible !important;
        /* Gunakan visible agar dropdown action tidak terpotong */
    }

    /* Jika layar kecil, baru gunakan overflow-x */
    @media (max-width: 992px) {
        .table-responsive {
            overflow-x: auto !important;
        }
    }

    /* Paksa kolom Amount tetap satu baris */
    .text-amount {
        white-space: nowrap !important;
        font-weight: 700;
        color: #2c3e50;
        font-variant-numeric: tabular-nums;
    }

    /* Merapikan Header DataTables */
    #simTable thead th {
        vertical-align: middle !important;
        padding: 12px 10px !important;
        border-bottom: 2px solid #f1f4f8 !important;
        white-space: nowrap;
    }

    /* Mengatur jarak sel agar tidak terlalu mepet atau terlalu jauh */
    #simTable tbody td {
        padding: 1rem 10px !important;
        vertical-align: middle !important;
    }

    /* Khusus untuk checkbox/No agar tidak terlalu lebar */
    .col-compact {
        width: 1% !important;
        white-space: nowrap;
    }

    /* Perbaikan badge agar lebih tegas */
    .status-badge {
        min-width: 100px;
        padding: 6px 10px !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        font-size: 10px !important;
    }

    #simTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.03) !important;
    }

    .icon-shape {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .badge-soft-warning {
        font-size: 11px;
        text-transform: uppercase;
        border-radius: 30px;
    }

    .btn-sync:hover {
        background-color: #2e59d9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">

                        <div class="me-3">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Simple Purchase List</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Purchasing</a></li>
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Simple Purchase</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="flex-grow-1 mx-xl-4 my-3 my-xl-0" style="max-width: 450px;">
                            <form action="{{ route('simple-purchase.index') }}" method="GET">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted small">Period</span>
                                    <input type="date" name="start_date" class="form-control border-start-0" value="{{ request('start_date') }}">
                                    <span class="input-group-text bg-white border-start-0 border-end-0">-</span>
                                    <input type="date" name="end_date" class="form-control border-start-0 border-end-0" value="{{ request('end_date') }}">
                                    <button type="submit" class="btn btn-primary px-3">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                    @if(request('start_date') || request('end_date'))
                                    <a href="{{ route('simple-purchase.index') }}" class="btn btn-light border-start">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                    @endif
                                </div>
                            </form>
                        </div>

                        <div class="d-flex gap-2">
                            <div class="btn-group btn-group-sm shadow-sm">
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalSyncPurchase">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sync
                                </button>
                                <button id="pushSelected" class="btn btn-outline-success d-flex align-items-center">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Push
                                </button>
                            </div>

                            <div class="btn-group btn-group-sm shadow-sm">
                                <a href="#" class="btn btn-success d-flex align-items-center px-3" title="Upload Data">
                                    <i class="bi bi-upload"></i>
                                </a>
                                <a href="{{ route('simple-purchase.create') }}" class="btn btn-primary d-flex align-items-center px-3" title="Create Data">
                                    <i class="bi bi-plus-lg me-1"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ====== MODAL SINKRONISASI ===== -->
            <div class="modal fade" id="modalSyncPurchase" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content" style="border-radius: 15px;">
                        <div class="modal-header border-0">
                            <h5 class="modal-title font-weight-bold">Sinkronisasi Simple Purchase</h5>
                            <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"> -->
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info small">
                                <i class="fa fa-info-circle mr-1"></i> Proses ini akan menarik data dari <strong>semua mitra</strong> yang aktif secara otomatis.
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted">DARI TANGGAL</label>
                                {{-- Perhatikan penulisan kutipan agar tidak error identifier --}}
                                <input type="date" id="date_from" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-muted">SAMPAI TANGGAL</label>
                                <input type="date" id="date_to" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="button" id="btnProsesSync" class="btn btn-primary px-4">Mulai Sinkronisasi</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-cart-check-fill me-2 text-primary"></i> Purchase Transactions
                    </h6>
                </div>

                <div class="card-body p-0">
                    <div class="p-4">
                        <div class="table-responsive border-0">
                            <table id="simTable" class="table table-hover align-middle mb-0 w-100">
                                <thead>
                                    <tr class="text-uppercase small fw-bolder text-secondary" style="background-color: #f8f9fa;">
                                        <th class="text-center col-compact">
                                            <input type="checkbox" id="checkAll" class="form-check-input shadow-none">No.
                                        </th>
                                        <th class="px-3">Mitra</th>
                                        <th class="px-3">SP Number</th>
                                        <th class="text-center">Branch</th>
                                        <th class="px-3">Supplier</th>
                                        <th class="text-end px-3">Amount</th>
                                        <th class="px-3">Add Info</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center col-compact">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchases as $p)
                                    <tr>
                                        <td class="text-center text-muted small">
                                            <input type="checkbox" class="form-check-input sub_chk shadow-none" data-id="{{ $p->id }}">
                                            <span class="text-muted small">{{ $loop->iteration }}</span>
                                        </td>
                                        <td class="px-3 fw-bold">{{ $p->credential_code }}</td>
                                        <td class="px-3 text-nowrap">
                                            <div class="fw-bold text-primary">{{ $p->purchase_num }}</div>
                                            <small class="text-muted"><i class="bi bi-calendar3"></i> {{ date('d M Y', strtotime($p->purchase_date)) }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-secondary border-0 px-2 py-1 small">{{ $p->branch_name }}</span>
                                        </td>
                                        <td class="px-3 small fw-medium">{{ $p->supplier_name }}</td>
                                        <td class="text-end px-3">
                                            <span class="text-amount fw-bold">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="px-3 text-muted small">{{ $p->additional_info ?: '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge status-badge bg-success-subtle text-success border px-3 py-2">
                                                {{ strtoupper($p->status_name) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border-0 shadow-none" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('simple-purchase.show', [$p->purchase_num, $p->credential_id]) }}">
                                                            <i class="bi bi-eye me-2 text-primary"></i> Detail
                                                        </a>
                                                    </li>
                                                    <li><button class="dropdown-item" onclick="edit('{{ $p->id }}')"><i class="bi bi-pencil me-2 text-warning"></i> Edit</button></li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li><button class="dropdown-item text-danger" onclick="confirmDelete('{{ $p->id }}')"><i class="bi bi-trash me-2"></i> Hapus</button></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    function confirmSync() {
        Swal.fire({
            title: 'Sinkronisasi ESB',
            html: `
            <div class="text-left">
                <div class="form-group mb-3">
                    <label class="small fw-bold">Dari Tanggal:</label>
                    <input type="date" id="swal-date-from" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group mb-3">
                    <label class="small fw-bold">Sampai Tanggal (Optional):</label>
                    <input type="date" id="swal-date-to" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="small fw-bold">Nomor Transaksi Spesifik (Optional):</label>
                    <input type="text" id="swal-cp-num" class="form-control" placeholder="Contoh: CP/2026/04/0001">
                    <small class="text-muted" style="font-size: 10px;">Kosongkan jika ingin tarik semua data di range tanggal.</small>
                </div>
            </div>
        `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: '<i class="fas fa-sync"></i> Jalankan Sync',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const dateFrom = document.getElementById('swal-date-from').value;
                const dateTo = document.getElementById('swal-date-to').value;
                const cpNum = document.getElementById('swal-cp-num').value;

                if (!dateFrom) {
                    Swal.showValidationMessage('Tanggal "Dari" harus diisi!');
                    return false;
                }
                return {
                    dateFrom,
                    dateTo,
                    cpNum
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Jalankan proses sinkronisasi dengan data dari Modal
                startSyncProcess(result.value.dateFrom, result.value.dateTo, result.value.cpNum);
            }
        });
    }

    function startSyncProcess(dateFrom, dateTo, cpNum) {
        Swal.fire({
            title: 'Memulai...',
            text: 'Menghubungkan ke API ESB Core...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: "/simple-purchase/sync",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            data: {
                date_from: dateFrom,
                date_to: dateTo,
                cash_purchase_num: cpNum
            },
            success: function(response) {
                if (response.status === 'success') {
                    checkProgress(response.sync_key);
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memicu sinkronisasi';
                Swal.fire('Error', msg, 'error');
            }
        });
    }

    function checkProgress(syncKey) {
        // Tampilkan modal dengan Progress Bar sejak awal
        Swal.fire({
            title: 'Sinkronisasi Sedang Berjalan',
            html: `
                <div class="progress mb-3" style="height: 25px;">
                    <div id="pb-sync" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                         role="progressbar" style="width: 0%">0%</div>
                </div>
                <div class="text-left mb-2">
                    <small>Memproses: <b id="current-branch">-</b></small><br>
                    <small>Progress: <b id="stat-count">0/0</b> outlet</small>
                </div>
                <hr>
                <div class="text-left">
                    <small class="text-danger font-weight-bold">Tanpa Data Simple Purchase:</small>
                    <div id="empty-list" style="max-height: 100px; overflow-y: auto; font-size: 11px; color: #888;">
                        <i>Belum ada...</i>
                    </div>
                </div>
            `,
            allowOutsideClick: false,
            showConfirmButton: false
        });

        let timer = setInterval(function() {
            $.ajax({
                url: "/simple-purchase/sync-status/" + syncKey,
                method: "GET",
                success: function(res) {
                    // 1. Update Progress Bar & Teks
                    let percent = res.percentage || 0;
                    $('#pb-sync').css('width', res.percentage + '%').text(res.percentage + '%');
                    $('#current-branch').text(res.last_branch || '-'); // Pakai 'last_branch' sesuai Job
                    $('#stat-count').text(`${res.processed_jobs}/${res.total_jobs}`);

                    // 2. Update Daftar Mitra/Branch Kosong
                    if (res.empty_branches && res.empty_branches.length > 0) {
                        let listHtml = '<ul class="pl-3 m-0">';
                        res.empty_branches.forEach(name => {
                            listHtml += `<li>${name}</li>`;
                        });
                        listHtml += '</ul>';
                        $('#empty-list').html(listHtml);
                    }

                    // 3. Cek Jika Selesai
                    if (res.status === 'done') {
                        clearInterval(timer);
                        Swal.fire({
                            icon: 'success',
                            title: 'Sinkronisasi Selesai!',
                            html: `Berhasil memproses <b>${res.total}</b> outlet.<br>Total data baru: <b>${res.inserted}</b>`,
                            confirmButtonText: 'Oke, Refresh Halaman'
                        }).then(() => {
                            location.reload();
                        });
                    } else if (res.status === 'failed') {
                        clearInterval(timer);
                        Swal.fire('Gagal!', 'Terjadi kesalahan: ' + res.message, 'error');
                    }
                },
                error: function() {
                    // Jangan langsung hentikan timer jika network cuma lag sebentar
                    console.log("Gagal mengambil status, mencoba lagi...");
                }
            });
        }, 2500); // Cek setiap 2.5 detik agar tidak membebani server
    }
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
@push('scripts')
<script>
    $(document).ready(function() {
        $('#simTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });

    $(document).ready(function() {
        $('#btnProsesSync').on('click', function() {
            let dateFrom = $('#date_from').val();
            let dateTo = $('#date_to').val();
            let btn = $(this);

            // Validasi Sederhana
            if (!dateFrom || !dateTo) {
                Swal.fire('Oops!', 'Silakan pilih range tanggal.', 'warning');
                return;
            }

            // Tampilan Loading
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Memproses...');

            $.ajax({
                url: "{{ route('simple-purchase.sync') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function(response) {
                    $('#modalSyncPurchase').modal('hide');
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'Oke'
                    }).then(() => {
                        // Refresh halaman agar data (minimal info jumlah) terupdate
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let err = xhr.responseJSON;
                    Swal.fire('Gagal!', err.message || 'Terjadi kesalahan sistem.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('Mulai Sinkronisasi');
                }
            });
        });
    });

    $(document).ready(function() {
        // 1. Fitur Check All
        $('#checkAll').on('click', function() {
            $(".sub_chk").prop('checked', $(this).prop('checked'));
        });

        // 2. Handle Tombol Push Selected
        $('#pushSelected').on('click', function() {
            let allVals = [];
            let table = $('#simTable').DataTable();

            // Mengambil checkbox dari seluruh halaman table, bukan hanya yang tampil
            table.$(".sub_chk:checked").each(function() {
                allVals.push($(this).attr('data-id'));
            });

            if (allVals.length <= 0) {
                Swal.fire("Peringatan", "Pilih minimal satu data!", "warning");
            } else {
                Swal.fire({
                    title: 'Push ke ESB?',
                    text: "Kirim " + allVals.length + " data terpilih ke ESB?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Push Sekarang!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let ids = allVals.join(",");

                        // Tampilkan loading
                        Swal.fire({
                            title: 'Sedang Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('simple-purchase.push') }}", // Buat route baru nanti
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(data) {
                                Swal.fire("Berhasil", data.message, "success");
                                location.reload(); // Refresh halaman setelah sukses
                            },
                            error: function(err) {
                                Swal.fire("Gagal", "Terjadi kesalahan saat push data", "error");
                            }
                        });
                    }
                });
            }
        });
    });
</script>
@endpush
@include('Temp.Investor.footer')
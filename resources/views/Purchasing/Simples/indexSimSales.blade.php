@include('Temp.Investor.header')
<style>
    /* Menghilangkan border default datatables yang kaku */
    table.dataTable.no-footer {
        border-bottom: 1px solid #f0f2f5 !important;
    }

    /* Membuat baris lebih lega */
    #simTable tbody td {
        padding: 1rem 0.75rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f8f9fa;
    }

    /* Styling Header agar tidak mepet */
    #simTable thead th {
        background-color: #f8f9fa !important;
        color: #6c757d !important;
        font-weight: 600 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 12px 15px !important;
        border-bottom: 1px solid #ebedef !important;
    }

    /* Efek hover yang lebih halus */
    #simTable tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.02) !important;
        transition: 0.2s ease-in-out;
    }

    /* Custom Badge agar lebih elegan */
    .status-badge {
        padding: 0.5em 1em;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.7rem;
    }
</style>
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="row align-items-center g-3">
                        <!-- Judul & Breadcrumb -->
                        <div class="col-xl-3 col-lg-12">
                            <h5 class="fw-bold mb-1" style="color: #2c3e50;">Simple Sales List</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Purchasing</a></li>
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Simple Sales</li>
                                </ol>
                            </nav>
                        </div>

                        <!-- Filter Tanggal (Central) -->
                        <div class="col-xl-5 col-lg-7">
                            <form action="{{ route('simple-sales.index') }}" method="GET" class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <label class="small text-muted fw-bold text-uppercase" style="font-size: 10px;">Filter Periode:</label>
                                </div>
                                <div class="col">
                                    <div class="input-group input-group-sm">
                                        <input type="date" name="start_date" class="form-control border-start-0 ps-0" value="{{ request('start_date') }}" title="Start Date">
                                        <span class="input-group-text bg-light border-start-0 border-end-0 text-muted">to</span>
                                        <input type="date" name="end_date" class="form-control border-start-0" value="{{ request('end_date') }}" title="End Date">
                                        <button type="submit" class="btn btn-primary px-3">
                                            <i class="bi bi-filter"></i> <span class="d-none d-md-inline"></span>
                                        </button>
                                        @if(request('start_date') || request('end_date'))
                                        <a href="{{ route('simple-sales.index') }}" class="btn btn-light border">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Action Buttons (Right) -->
                        <div class="col-xl-4 col-lg-5 text-xl-end text-lg-start">
                            <div class="d-flex gap-2 justify-content-xl-end">
                                <!-- Tombol Sync & Push (Outline agar tidak terlalu dominan) -->
                                <div class="btn-group">
                                    <button data-bs-toggle="modal" data-bs-target="#modalSyncSales" class="btn btn-sm btn-outline-success" title="Sync ESB">
                                        <i class="bi bi-arrow-repeat me-1"></i> Sync
                                    </button>
                                    <button id="pushSelected" class="btn btn-sm btn-outline-primary" title="Push to ESB">
                                        <i class="bi bi-cloud-arrow-up me-1"></i> Push
                                    </button>
                                </div>

                                <!-- Tombol Upload & Create (Solid) -->
                                <div class="d-flex gap-1">
                                    <a href="#" class="btn btn-sm btn-success px-3 shadow-sm">
                                        <i class="bi bi-upload"></i> <span class="d-none d-sm-inline ms-1">Upload</span>
                                    </a>
                                    <a href="{{ route('simple-sales.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                                        <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline ms-1">Create</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-0"> <!-- p-0 penting agar tabel mentok ke pinggir card tapi tetap rapi -->
                    <div class="table-responsive p-3"> <!-- Tambah padding di pembungkus agar search & length menu ga nempel tembok -->
                        <table id="simTable" class="table align-middle mb-0 w-100">
                            <thead>
                                <tr>
                                    <!-- <th class="py-3 text-center" style="width: 50px;">
                                        <input type="checkbox" id="checkAll" class="form-check-input shadow-none">
                                    </th> -->
                                    <th class="text-center">
                                        <input type="checkbox" id="checkAll" class="form-check-input shadow-none">
                                        No
                                    </th>
                                    <th>Transaction Info</th>
                                    <th>Branch</th>
                                    <th>Customer</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $row)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input sub_chk shadow-none" data-id="{{ $row->id }}">
                                        <span class="text-muted small">{{ $loop->iteration }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $row->sales_num }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <i class="bi bi-calendar-event me-1"></i> {{ date('d M Y', strtotime($row->sales_date)) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border fw-medium">
                                            {{ $row->branch_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $row->customer_name }}</div>
                                        <div class="text-muted small" style="font-size: 11px;">ID: {{ $row->customer_id }}</div>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        Rp {{ number_format($row->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                        $statusColor = match($row->status_id) {
                                        10 => 'text-success bg-success-subtle',
                                        3 => 'text-primary bg-primary-subtle',
                                        default => 'text-secondary bg-light'
                                        };
                                        @endphp
                                        <span class="badge {{ $statusColor }} px-3 py-2 rounded-2" style="font-size: 10px;">
                                            {{ strtoupper($row->status_name) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                <li><a class="dropdown-item" href="{{ route('simple-sales.show', $row->sales_num) }}"><i class="bi bi-eye me-2 text-primary"></i> Detail</a></li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-2" href="{{ route('simple-sales.edit', $row->sales_num) }}">
                                                        <i class="bi bi-pencil-square me-2 text-warning"></i> Edit
                                                    </a>
                                                </li>
                                                <li><button class="dropdown-item" onclick="window.print()"><i class="bi bi-print me-2 text-secondary"></i> Print</button></li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li><button class="dropdown-item text-danger" onclick="confirmDelete('{{ $row->id }}')"><i class="bi bi-trash me-2"></i> Hapus</button></li>
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


            <!-- ==== MODAL PILIH TANGGAL GET DATA ===== -->
            <div class="modal fade" id="modalSyncSales" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-sm" role="document">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" style="font-size: 14px;"><i class="fas fa-calendar-alt mr-2"></i> Pilih Periode Sales</h5>
                            <!-- <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button> -->
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold">DARI TANGGAL</label>
                                <input type="date" id="sync_date_from" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">SAMPAI TANGGAL</label>
                                <input type="date" id="sync_date_to" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="button" id="executeSync" class="btn btn-primary btn-sm px-4">Mulai Sync</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#simTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });

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

    $(document).ready(function() {
        $('#executeSync').click(function() {
            let dateFrom = $('#sync_date_from').val();
            let dateTo = $('#sync_date_to').val();
            let $btn = $(this);

            // Validasi simpel
            if (!dateFrom || !dateTo) {
                Swal.fire('Oops!', 'Pilih tanggal dulu ya.', 'warning');
                return;
            }

            // Ubah tampilan tombol saat proses
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');

            $.ajax({
                url: "{{ route('sales.sync') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function(response) {
                    // Tutup modal pilih tanggal
                    $('#modalSyncSales').modal('hide');

                    // Kembalikan tombol ke semula
                    $btn.prop('disabled', false).html('Mulai Sync');

                    Swal.fire({
                        icon: 'success',
                        title: 'Sync Dimulai!',
                        text: response.message, // Menampilkan "Sinkronisasi 173 data Sales..."
                        showConfirmButton: true,
                        showCancelButton: true,
                    }).then(() => {
                        // Opsional: Refresh atau arahkan ke halaman log/antrean
                        location.reload();
                    });
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('Mulai Sync');
                    Swal.fire('Error', 'Gagal memicu sinkronisasi: ' + xhr.responseJSON.message, 'error');
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
                            url: "{{ route('simple-sales.push') }}", // Buat route baru nanti
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
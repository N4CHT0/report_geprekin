@include('Temp.Investor.header')
<!--<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    /* 1. Paksa tabel untuk mengambil lebar penuh dan hilangkan border default DT */
    #simTable_wrapper .dataTables_scroll,
    #simTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    /* 2. Beri ruang pada Header dan Baris (Paling Krusial) */
    #simTable thead th {
        padding: 15px 20px !important;
        /* Memberi nafas pada header */
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #f1f4f8 !important;
        vertical-align: middle;
    }

    #simTable tbody td {
        padding: 1.2rem 20px !important;
        /* Memberi jarak antar baris agar tidak mepet */
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
    }

    /* 3. Rapikan Search Bar dan Show Entries yang mepet ke tembok */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 15px 20px !important;
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 15px 20px !important;
    }

    /* 4. Atur ulang lebar kolom spesifik agar tidak balapan */
    #simTable th:nth-child(2),
    #simTable td:nth-child(2) {
        min-width: 150px;
    }

    /* Info */
    #simTable th:nth-child(3),
    #simTable td:nth-child(3) {
        min-width: 120px;
    }

    /* Origin */
    #simTable th:nth-child(4),
    #simTable td:nth-child(4) {
        min-width: 120px;
    }

    /* Destination */

    /* 5. Hilangkan garis double yang biasanya muncul di DataTables */
    table.dataTable.no-footer {
        border-bottom: none !important;
    }

    /* 1. Perbaikan Hover: Hilangkan scale, ganti dengan shadow tipis */
    #simTable tbody tr {
        transition: all 0.2s ease-in-out;
    }

    #simTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        /* Daripada scale, gunakan box-shadow inner agar baris terlihat "on focus" */
        box-shadow: inset 4px 0 0 #696cff;
    }

    /* 2. Badge Styling: Buat lebih konsisten */
    .badge-soft {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-radius: 8px;
        /* Lebih modern pakai rounded kotak daripada lonjong */
        padding: 6px 12px;
        border: 1px solid transparent;
    }

    /* 3. Button Sync: Tambahkan transisi agar halus */
    .btn-sync {
        transition: all 0.3s;
    }

    .btn-sync:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.2);
    }

    /* 4. Struktur Tabel: Tambahkan vertical align */
    #simTable thead th {
        border: none;
        white-space: nowrap;
    }

    #simTable tbody td {
        border-bottom: 1px solid #f1f4f8;
        padding-top: 1.1rem;
        /* Sedikit lebih lega */
        padding-bottom: 1.1rem;
        vertical-align: middle;
    }

    #simTable tbody tr:last-child td {
        border-bottom: none;
    }

    /* 5. Icon Shape: Pastikan presisi di tengah */
    .icon-shape {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        flex-shrink: 0;
        /* Agar icon tidak gepeng kalau teks panjang */
    }

    /* 6. Warna Subtil (Subtle) dengan Border agar lebih tegas */
    .bg-success-subtle {
        background-color: #e8fadf !important;
        color: #71dd37 !important;
        border: 1px solid #d4f5c3 !important;
    }

    .bg-warning-subtle {
        background-color: #fff2e2 !important;
        color: #ffab00 !important;
        border: 1px solid #ffe5c4 !important;
    }

    .bg-info-subtle {
        background-color: #e7e7ff !important;
        color: #696cff !important;
        border: 1px solid #d9d9ff !important;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">

                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Simple Transfer List</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Purchasing</a></li>
                                    <li class="breadcrumb-item active text-primary" aria-current="page">Simple Transfer</li>
                                </ol>
                            </nav>
                        </div>

                        <div class="mx-3">
                            <form action="{{ route('simple-transfer.index') }}" method="GET" class="d-flex align-items-center">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted small" style="font-size: 12px;">Period</span>
                                    <input type="date" name="start_date" class="form-control border-start-0" value="{{ request('start_date') }}" style="width: 140px;">
                                    <span class="input-group-text bg-white border-start-0 border-end-0">-</span>
                                    <input type="date" name="end_date" class="form-control border-start-0 border-end-0" value="{{ request('end_date') }}" style="width: 140px;">
                                    <button type="submit" class="btn btn-primary px-3">
                                        <i class="bi bi-filter"></i>
                                    </button>
                                    @if(request('start_date') || request('end_date'))
                                    <a href="{{ route('simple-transfer.index') }}" class="btn btn-light border">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </a>
                                    @endif
                                </div>
                            </form>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <div class="btn-group btn-group-sm">
                                <button onclick="showSyncModal()" class="btn btn-outline-success d-flex align-items-center">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sync
                                </button>
                                <button id="pushSelected" class="btn btn-outline-primary d-flex align-items-center">
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Push
                                </button>
                                <a href="#" class="btn btn-sm btn-success px-2 shadow-sm d-flex align-items-center justify-content-center" title="Upload" style="width: 32px; height: 31px;">
                                    <i class="bi bi-upload"></i>
                                </a>

                                <a href="{{ route('simple-transfer.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm d-flex align-items-center justify-content-center" title="Create" style="width: 32px; height: 31px;">
                                    <i class="bi bi-plus-lg "></i>
                                </a>
                            </div>


                        </div>

                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-arrow-left-right me-2 text-primary"></i> Transfer Transactions
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="simTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-uppercase small fw-bolder text-secondary" style="background-color: #f8f9fa; letter-spacing: 0.05em; border-bottom: 1px solid #f1f4f8;">
                                    <th class="py-3 text-center" style="width: 50px;">
                                        <input type="checkbox" id="checkAll" class="form-check-input shadow-none">
                                    </th>
                                    <th class="py-3 px-3">Transfer Info</th>
                                    <th class="py-3 px-3">Origin</th>
                                    <th class="py-3 px-3">Destination</th>
                                    <th class="py-3 px-3">Additional Info</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="py-3 text-center" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($transfers as $item)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input sub_chk shadow-none" data-id="{{ $item->id }}">
                                    </td>
                                    <td class="px-3">
                                        <div class="fw-bold text-primary" style="font-size: 0.85rem;">{{ $item->transfer_num }}</div>
                                        <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($item->transfer_date)->format('d M Y') }}</small>
                                    </td>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-light text-secondary me-2" style="width: 25px; height: 25px; font-size: 12px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-geo-alt"></i>
                                            </div>
                                            <span class="fw-medium small">{{ $item->origin_location_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-shape bg-primary-subtle text-primary me-2" style="width: 25px; height: 25px; font-size: 12px; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-geo-fill"></i>
                                            </div>
                                            <span class="fw-medium small text-dark">{{ $item->destination_location_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3">
                                        <p class="mb-0 text-muted small text-wrap" style="max-width: 200px; line-height: 1.2;">
                                            {{ $item->additional_info ?: '-' }}
                                        </p>
                                    </td>
                                    <td class="text-center">
                                        @php
                                        $statusClass = match(strtolower($item->status_name)) {
                                        'completed', 'success' => 'bg-success-subtle text-success border-success-subtle',
                                        'pending', 'authorized' => 'bg-warning-subtle text-warning border-warning-subtle',
                                        default => 'bg-info-subtle text-info border-info-subtle'
                                        };
                                        @endphp
                                        <span class="badge {{ $statusClass }} border px-3 py-2 fw-bold" style="font-size: 0.7rem; border-radius: 8px;">
                                            {{ strtoupper($item->status_name) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0 shadow-none" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 12px; min-width: 160px;">
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-2" href="{{ route('simple-transfer.show', $item->transfer_num) }}">
                                                        <i class="bi bi-eye-fill me-2 text-primary"></i> Detail
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2 py-2" href="{{ route('simple-transfer.edit', $item->transfer_num) }}">
                                                        <i class="bi bi-pencil-square me-2 text-warning"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider opacity-50">
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item rounded-2 py-2 text-danger" onclick="confirmDelete('{{ $item->id }}')">
                                                        <i class="bi bi-trash3-fill me-2"></i> Hapus
                                                    </button>
                                                </li>
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
                            url: "{{ route('simple-transfer.push') }}", // Buat route baru nanti
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


<script>
    function showSyncModal() {
        Swal.fire({
            title: 'Pilih Tanggal Transfer',
            html: `
                <input type="date" id="s_date" class="swal2-input">
                <input type="date" id="e_date" class="swal2-input">
            `,
            showCancelButton: true,
            cancelButtonColor: '#d33',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                let s = $('#s_date').val();
                let e = $('#e_date').val();

                if (!s || !e) {
                    Swal.showValidationMessage('Tanggal harus diisi');
                    return false;
                }

                if (s > e) {
                    Swal.showValidationMessage('Tanggal tidak valid');
                    return false;
                }

                return {
                    s,
                    e
                };
            }
        }).then((res) => {
            if (res.isConfirmed) {
                startProcess(res.value.s, res.value.e);
            }
        });
    }

    function startProcess(s, e) {

        Swal.fire({
            title: 'Proses Sync...',
            html: `
                <div class="progress">
                    <div id="pb" class="progress-bar" style="width:0%">0%</div>
                </div>
            `,
            allowOutsideClick: false,
            showConfirmButton: false
        });

        $.post('/simple-transfer/start', {
                start_date: s,
                end_date: e,
                _token: '{{csrf_token()}}'
            })
            .done(function(res) {

                let timer = setInterval(() => {

                    $.get('/simple-transfer/progress')
                        .done(function(data) {

                            let total = data.total || 0;
                            let progress = data.progress || 0;

                            // ✅ aman dari division by zero
                            let perc = total > 0 ?
                                Math.round((progress / total) * 100) :
                                0;

                            $('#pb').css('width', perc + '%').text(perc + '%');

                            // selesai
                            if (perc >= 100 && total > 0) {
                                clearInterval(timer);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Selesai',
                                    text: 'Data berhasil disinkronisasi'
                                });
                            }

                        })
                        .fail(function() {
                            clearInterval(timer);

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal mengambil progress'
                            });
                        });

                }, 1500); // agak dilonggarkan biar ringan
            })
            .fail(function(err) {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Start Sync',
                    text: err.responseJSON?.message || 'Terjadi kesalahan'
                });

            });
    }
</script>
@endpush
@include('Temp.Investor.footer')
@section('title', 'Laporan Undian Berhadiah')
@section('breadcrumb', 'Laporan / Undian Berhadiah')

@include('Temp.Investor.header')

<style>
    :root{
        --aws-card:#ffffff;
        --aws-text:#16191f;
        --aws-muted:#5f6b7a;
        --aws-line:#d5dbdb;
        --aws-line-soft:#e9ebed;
        --aws-blue:#0972d3;
        --aws-blue-dark:#033160;
        --aws-green:#037f0c;
        --aws-red:#d13212;
        --aws-radius:8px;
        --aws-shadow:0 1px 2px rgba(15,23,42,.06);
        --aws-focus:0 0 0 3px rgba(9,114,211,.18);
    }

    .undian-page{
        display:flex;
        flex-direction:column;
        gap:16px;
        min-width:0;
        max-width:100%;
        overflow:hidden;
    }

    .undian-hero{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
        padding-bottom:14px;
        border-bottom:1px solid var(--aws-line);
    }

    .undian-kicker{
        color:var(--aws-muted);
        font-size:.82rem;
        font-weight:700;
        margin-bottom:4px;
    }

    .undian-title-main{
        margin:0;
        color:var(--aws-text);
        font-size:1.5rem;
        font-weight:800;
        letter-spacing:-.02em;
    }

    .undian-pills{
        display:flex;
        gap:8px;
        align-items:center;
        flex-wrap:wrap;
    }

    .undian-pill{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:6px 10px;
        border-radius:999px;
        border:1px solid var(--aws-line);
        background:#fff;
        color:#414d5c;
        font-size:.78rem;
        font-weight:800;
        white-space:nowrap;
    }

    .aws-card{
        background:#fff;
        border:1px solid var(--aws-line);
        border-radius:var(--aws-radius);
        box-shadow:var(--aws-shadow);
        overflow:hidden;
    }

    .aws-card-header{
        padding:13px 14px;
        border-bottom:1px solid var(--aws-line);
        background:#fbfbfb;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
    }

    .aws-card-title{
        margin:0;
        color:var(--aws-text);
        font-size:1rem;
        font-weight:800;
        letter-spacing:-.01em;
    }

    .aws-card-subtitle{
        margin-top:3px;
        color:var(--aws-muted);
        font-size:.82rem;
        font-weight:600;
    }

    .aws-card-body{
        padding:14px;
        min-width:0;
    }

    .filter-shell{
        background:#f8f9fa;
        border:1px solid var(--aws-line-soft);
        border-radius:var(--aws-radius);
        padding:13px;
    }

    .form-label{
        color:var(--aws-text);
        font-size:.82rem;
        font-weight:800;
        margin-bottom:6px;
    }

    .form-control,
    .form-select{
        min-height:38px;
        border-radius:8px!important;
        border:1px solid var(--aws-line)!important;
        color:var(--aws-text);
        font-size:.9rem;
        font-weight:600;
        box-shadow:none!important;
        max-width:100%;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:var(--aws-blue)!important;
        box-shadow:var(--aws-focus)!important;
    }

    .btn{
        border-radius:8px!important;
        font-weight:800!important;
        box-shadow:none!important;
        transform:none!important;
    }

    .btn-primary{
        background:var(--aws-blue)!important;
        border-color:var(--aws-blue)!important;
    }

    .btn-primary:hover{
        background:var(--aws-blue-dark)!important;
        border-color:var(--aws-blue-dark)!important;
    }

    .btn-success{
        background:var(--aws-green)!important;
        border-color:var(--aws-green)!important;
    }

    .btn-danger{
        background:var(--aws-red)!important;
        border-color:var(--aws-red)!important;
    }

    .btn-outline-secondary,
    .btn-outline-success,
    .btn-outline-danger,
    .btn-outline-dark{
        background:#fff!important;
        border-color:var(--aws-line)!important;
    }

    .btn-outline-secondary{ color:#414d5c!important; }
    .btn-outline-success{ color:var(--aws-green)!important; }
    .btn-outline-danger{ color:var(--aws-red)!important; }
    .btn-outline-dark{ color:var(--aws-text)!important; }

    .btn-outline-secondary:hover{ background:#f2f3f3!important; color:var(--aws-text)!important; }
    .btn-outline-success:hover{ background:#ecfdf3!important; border-color:#b7e4bf!important; }
    .btn-outline-danger:hover{ background:#fff1f0!important; border-color:#f3b8ad!important; }
    .btn-outline-dark:hover{ background:#f2f3f3!important; }

    .filter-actions-main{
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:8px;
    }

    .export-actions{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        justify-content:flex-end;
        margin-top:12px;
    }

    .table-shell{
        border:1px solid var(--aws-line);
        border-radius:var(--aws-radius);
        background:#fff;
        overflow:hidden;
    }

    .table-responsive{
        overflow:auto;
        -webkit-overflow-scrolling:touch;
    }

    table.dataTable{
        width:100%!important;
        min-width:1280px;
        margin:0!important;
        border-collapse:separate!important;
        border-spacing:0!important;
        color:var(--aws-text);
    }

    table.dataTable thead th{
        background:#f8f9fa!important;
        color:#414d5c;
        font-size:.72rem;
        font-weight:850;
        text-transform:uppercase;
        letter-spacing:.04em;
        white-space:nowrap;
        border-bottom:1px solid var(--aws-line)!important;
        padding:9px 10px!important;
        vertical-align:middle;
    }

    table.dataTable tbody td{
        white-space:nowrap;
        border-bottom:1px solid var(--aws-line-soft)!important;
        color:var(--aws-text);
        font-size:.82rem;
        padding:8px 10px!important;
        vertical-align:middle;
    }

    table.dataTable tbody tr:hover td{
        background:#f2f8fd!important;
    }

    .dataTables_wrapper{
        padding:12px;
    }

    .dataTables_wrapper .row{
        margin-left:0!important;
        margin-right:0!important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter{
        margin-bottom:12px!important;
    }

    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label{
        display:flex;
        align-items:center;
        gap:8px;
        margin:0;
        color:var(--aws-muted);
        font-size:.84rem;
        font-weight:700;
    }

    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select{
        border:1px solid var(--aws-line)!important;
        border-radius:8px!important;
        min-height:34px;
        padding:6px 10px!important;
        background:#fff;
        color:var(--aws-text);
        outline:none!important;
    }

    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus{
        border-color:var(--aws-blue)!important;
        box-shadow:var(--aws-focus)!important;
    }

    .dataTables_wrapper .dataTables_info{
        color:var(--aws-muted);
        font-size:.84rem;
        font-weight:600;
        padding-top:10px!important;
    }

    .dataTables_wrapper .dataTables_paginate{
        padding-top:8px!important;
    }

    .dataTables_wrapper .pagination{
        gap:4px;
        flex-wrap:wrap;
        margin:0;
    }

    .dataTables_wrapper .page-link{
        border-radius:8px!important;
        border-color:var(--aws-line)!important;
        color:#414d5c!important;
        font-weight:800;
        min-width:34px;
        min-height:34px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
    }

    .dataTables_wrapper .page-item.active .page-link{
        background:var(--aws-blue)!important;
        border-color:var(--aws-blue)!important;
        color:#fff!important;
    }

    .select2-container{
        width:100%!important;
        max-width:100%!important;
    }

    .select2-container .select2-selection--single{
        min-height:38px;
        border:1px solid var(--aws-line)!important;
        border-radius:8px!important;
        display:flex!important;
        align-items:center!important;
        background:#fff;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered{
        line-height:36px!important;
        padding-left:12px!important;
        padding-right:24px!important;
        color:var(--aws-text)!important;
        font-weight:600;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height:36px!important;
        right:7px!important;
    }

    .select2-dropdown{
        border:1px solid var(--aws-line)!important;
        border-radius:8px!important;
        overflow:hidden;
        box-shadow:0 12px 28px rgba(15,23,42,.12);
        z-index:3000!important;
    }

    .select2-container--open{
        z-index:3001!important;
    }

    .select2-search__field{
        border:1px solid var(--aws-line)!important;
        border-radius:8px!important;
        padding:7px 10px!important;
        outline:none!important;
    }

    .row-check,
    #checkAll{
        width:16px;
        height:16px;
        cursor:pointer;
    }

    @media (max-width:991.98px){
        .export-actions{
            justify-content:flex-start;
        }
    }

    @media (max-width:767.98px){
        .undian-page{
            gap:12px;
        }

        .undian-hero{
            align-items:flex-start;
            flex-direction:column;
            gap:10px;
        }

        .undian-title-main{
            font-size:1.16rem;
        }

        .undian-pill{
            border-radius:8px;
            white-space:normal;
            font-size:.72rem;
        }

        .aws-card-header,
        .aws-card-body{
            padding:12px;
        }

        .filter-actions-main{
            grid-template-columns:1fr;
        }

        .export-actions{
            display:grid;
            grid-template-columns:1fr;
        }

        .export-actions .btn{
            width:100%;
        }

        .dataTables_wrapper{
            padding:10px;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate{
            float:none!important;
            text-align:left!important;
            width:100%;
        }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label{
            width:100%;
            align-items:flex-start;
            flex-direction:column;
        }

        .dataTables_wrapper .dataTables_filter input{
            width:100%;
            margin-left:0!important;
        }

        table.dataTable{
            min-width:1100px;
        }
    }
</style>

<div class="undian-page">
    <div class="undian-hero">
        <div>
            <div class="undian-kicker">Laporan / Undian Berhadiah</div>
            <h1 class="undian-title-main">Laporan Undian Berhadiah</h1>
        </div>
        <div class="undian-pills">
            <span class="undian-pill"><i class="bi bi-calendar-range"></i> {{ $tanggalMulai ?? '-' }} / {{ $tanggalSelesai ?? '-' }}</span>
            <span class="undian-pill"><i class="bi bi-shop"></i> {{ $outletId ? ($outlets->firstWhere('id', (int) $outletId)->nama_outlet ?? '-') : 'Semua Outlet' }}</span>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="aws-card">
        <div class="aws-card-header">
            <div>
                <h2 class="aws-card-title">Filter Data</h2>
                <div class="aws-card-subtitle">Pilih periode dan outlet untuk memfilter data undian.</div>
            </div>
        </div>
        <div class="aws-card-body">
            <div class="filter-shell">
            <form method="GET" action="{{ route('undian.undianReport') }}" class="row g-2 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Outlet</label>
                    <select name="outlet_id" id="outlet_id" class="form-select">
                        <option value="">-- Semua Outlet --</option>
                        @foreach ($outlets as $o)
                            <option value="{{ $o->id }}"
                                {{ (string) ($outletId ?? '') === (string) $o->id ? 'selected' : '' }}>
                                {{ $o->nama_outlet }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2"><div class="filter-actions-main">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="bi bi-funnel"></i> Filter
                    </button>

                    <a class="btn btn-outline-secondary w-100" href="{{ route('undian.undianReport') }}">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div></div>

                <div class="col-12 export-actions">
                    {{-- Export Excel SERVER-SIDE --}}
                    <a class="btn btn-success btn-sm"
                        href="{{ route('laporan.undianExportExcel', request()->only(['tanggal_mulai', 'tanggal_selesai', 'outlet_id'])) }}">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </a>

                    {{-- Export dari DataTables --}}
                    <button class="btn btn-outline-success btn-sm" type="button" id="btnExcelTable">
                        <i class="bi bi-file-earmark-excel"></i> Excel (Table)
                    </button>

                    <button class="btn btn-outline-danger btn-sm" type="button" id="btnPdf">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>

                    <button class="btn btn-outline-dark btn-sm" type="button" id="btnPrint">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>

            </form>
            </div>
        </div>
    </div>

    {{-- TABLE + BULK DELETE --}}
    <form action="{{ route('laporan.undianDestroy') }}" method="POST" id="bulkDeleteForm">
        @csrf
        @method('DELETE')

        <div class="aws-card">
            <div class="aws-card-header">
                <div>
                    <h2 class="aws-card-title">Laporan Undian Berhadiah (Struk)</h2>
                    <div class="aws-card-subtitle">
                        Periode: {{ $tanggalMulai ?? '-' }} s/d {{ $tanggalSelesai ?? '-' }}
                        | Outlet:
                        {{ $outletId ? $outlets->firstWhere('id', (int) $outletId)->nama_outlet ?? '-' : 'Semua Outlet' }}
                    </div>
                </div>

                <div class="ms-auto d-flex gap-2 flex-wrap justify-content-end">
                    <button type="submit" class="btn btn-danger btn-sm" id="btnBulkDelete">
                        <i class="bi bi-trash"></i> Hapus Terpilih
                    </button>
                </div>
            </div>

            <div class="aws-card-body">
                <div class="table-shell"><div class="table-responsive">
                    <table id="undianTable" class="table table-bordered table-hover table-sm align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">
                                    <input type="checkbox" id="checkAll">
                                </th>
                                <th>id</th>
                                <th>outlet_id</th>
                                <th>outlet</th>
                                <th>nama_lengkap</th>
                                <th>no_telp</th>
                                <th>nomor_struk</th>
                                <th>total_belanja</th>
                                <th>nomor_undian</th>
                                <th>tanggal_struk</th>
                                <th>periode</th>
                                <th style="width:80px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($dataUndian as $r)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $r->id }}"
                                            class="row-check">
                                    </td>

                                    <td>{{ $r->id }}</td>
                                    <td>{{ $r->outlet_id ?? '-' }}</td>
                                    <td>{{ $r->outlet ?? '-' }}</td>
                                    <td>{{ $r->nama_lengkap ?? '-' }}</td>
                                    <td>{{ $r->no_telp ?? '-' }}</td>
                                    <td>{{ $r->nomor_struk ?? '-' }}</td>
                                    <td class="text-end">
                                        {{ number_format((float) ($r->total_belanja ?? 0), 0, ',', '.') }}
                                    </td>
                                    <td>{{ $r->nomor_undian ?? '-' }}</td>
                                    <td>{{ $r->tanggal_struk ?? '-' }}</td>
                                    <td>{{ $r->periode ?? '-' }}</td>

                                    <td>
                                        {{-- single delete (pakai route sama, kirim ids[]) --}}
                                        <button type="submit" class="btn btn-sm btn-danger btn-single-delete"
                                            data-id="{{ $r->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div></div>
            </div>
        </div>
    </form>

</div>
{{-- Select2 --}}
{{-- DataTables --}}
{{-- Buttons --}}


{{-- Export deps --}}




@push('scripts')
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
    $(function() {
        // Select2 Outlet
        $('#outlet_id').select2({
            width: '100%',
            placeholder: '-- Semua Outlet --',
            allowClear: true,
            dropdownParent: $('.filter-shell')
        });

        // INIT DATATABLES (HANYA SEKALI!)
        if ($.fn.DataTable.isDataTable('#undianTable')) {
            $('#undianTable').DataTable().destroy();
        }

        const table = $('#undianTable').DataTable({
            pageLength: 10,
            order: [
                [9, 'desc'] // tanggal_struk index 9 (karena kolom checkbox di index 0)
            ],
            dom: '<"row mb-2"<"col-md-6"l><"col-md-6 d-flex justify-content-end"f>>rt<"row mt-2"<"col-md-6"i><"col-md-6 d-flex justify-content-end"p>>',
            buttons: [{
                    extend: 'excelHtml5',
                    filename: function() {
                        const start = @json($tanggalMulai ?? '');
                        const end = @json($tanggalSelesai ?? '');
                        const outlet = @json($outletId ?? '');
                        return `laporan_undian_struk_${start || 'all'}_${end || 'all'}_${outlet || 'alloutlet'}`;
                    },
                    title: null,
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9,
                            10] // termasuk checkbox (kalau mau HILANGIN, hapus 0)
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Laporan Undian Berhadiah (Struk)',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] // checkbox tidak ikut
                    }
                },
                {
                    extend: 'print',
                    title: 'Laporan Undian Berhadiah (Struk)',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10] // checkbox tidak ikut
                    }
                }
            ],
            columnDefs: [{
                    targets: 0,
                    orderable: false
                }, // checkbox
                {
                    targets: 11,
                    orderable: false
                }, // action
                {
                    targets: 7,
                    className: 'text-end'
                } // total_belanja
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            }
        });

        // Trigger export dari tombol custom
        $('#btnExcelTable').on('click', function() {
            table.button(0).trigger();
        });
        $('#btnPdf').on('click', function() {
            table.button(1).trigger();
        });
        $('#btnPrint').on('click', function() {
            table.button(2).trigger();
        });

        // CHECK ALL hanya untuk row halaman yg tampil
        $('#checkAll').on('change', function() {
            const checked = this.checked;
            table.rows({
                page: 'current'
            }).nodes().to$().find('input.row-check').prop('checked', checked);
        });

        // Reset checkAll saat draw (paging/search)
        table.on('draw', function() {
            $('#checkAll').prop('checked', false);
        });

        // Single delete: ceklis dulu hanya id itu lalu submit form
        $('.btn-single-delete').on('click', async function(e) {
            e.preventDefault();

            const id = $(this).data('id');
            const singleConfirm = window.Swal
                ? await Swal.fire({
                    icon: 'warning',
                    title: 'Hapus data ini?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d13212'
                }).then(r => r.isConfirmed)
                : confirm('Yakin ingin menghapus data ini?');

            if (!singleConfirm) return;

            // uncheck semua, check hanya yg dipilih
            $('input.row-check').prop('checked', false);
            $('input.row-check[value="' + id + '"]').prop('checked', true);

            $('#bulkDeleteForm').trigger('submit');
        });

        // Proteksi bulk delete: harus pilih minimal 1
        $('#bulkDeleteForm').on('submit', function(e) {
            if ($('input.row-check:checked').length === 0) {
                e.preventDefault();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Belum ada data dipilih',
                        text: 'Pilih minimal 1 data untuk dihapus.',
                        confirmButtonColor: '#0972d3'
                    });
                } else {
                    alert('Pilih minimal 1 data untuk dihapus.');
                }
                return;
            }
            if (window.Swal) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus data terpilih?',
                    text: 'Data yang dihapus tidak bisa dikembalikan.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d13212'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            } else if (!confirm('Yakin ingin menghapus data yang dipilih?')) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush

@include('Temp.Investor.footer')
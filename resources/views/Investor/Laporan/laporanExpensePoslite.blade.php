@include('Temp.Investor.header')

<style>
    /* Select2 biar mirip Bootstrap */
    .select2-container .select2-selection--single {
        height: calc(2.375rem + 2px);
        padding: .375rem .75rem;
        border: 1px solid #ced4da;
        border-radius: .375rem;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
    }

    /* DataTables spacing biar clean */
    div.dataTables_wrapper div.dataTables_length label,
    div.dataTables_wrapper div.dataTables_filter label {
        margin-bottom: 0;
    }

    div.dataTables_wrapper div.dataTables_filter input {
        margin-left: .5rem;
    }
</style>

<div class="container-fluid">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- FILTER --}}
    <div class="card mb-3 mt-3">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.laporanExpense') }}" class="row g-2 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" value="{{ $start }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" value="{{ $end }}" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Outlet</label>
                    <select name="outlet_id" id="outlet_id" class="form-select">
                        <option value="">-- Semua Outlet --</option>
                        @foreach ($outlets as $o)
                            <option value="{{ $o->id }}"
                                {{ (string) $outletId === (string) $o->id ? 'selected' : '' }}>
                                {{ $o->nama_outlet }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary w-100" type="submit">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a class="btn btn-outline-secondary w-100" href="{{ route('laporan.laporanExpense') }}">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-0 fw-bold">Expense Poslite</h6>
                <small class="text-muted">Periode: {{ $start }} s/d {{ $end }}</small>
            </div>

            {{-- Button kanan sendiri --}}
            <div class="ms-auto d-flex gap-2 flex-wrap justify-content-end">
                <button class="btn btn-outline-primary btn-sm" type="button" id="btnImport">
                    <i class="bi bi-upload"></i> Import
                </button>

                <button class="btn btn-success btn-sm" type="button" id="btnExcel">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </button>

                <button class="btn btn-danger btn-sm" type="button" id="btnPdf">
                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                </button>

                <button class="btn btn-outline-dark btn-sm" type="button" id="btnPrint">
                    <i class="bi bi-printer"></i> Print
                </button>

                <a href="{{ route('laporan.laporanExpense') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="expenseTable" class="table table-bordered table-hover table-sm align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Branch</th>
                            <th style="width: 130px;">Date</th>
                            <th style="width: 180px;">Purpose</th>
                            <th>Note</th>
                            <th style="width: 150px;" class="text-end">Amount</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($rows as $i => $r)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $r->branch }}</td>
                                <td>{{ $r->date }}</td>
                                <td>{{ $r->purpose }}</td>
                                <td>{{ $r->note }}</td>
                                <td class="text-end">{{ number_format((float) $r->amount, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">GRAND TOTAL</th>
                            <th class="text-end">{{ number_format((float) $totalAmount, 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>

                </table>
            </div>
        </div>
    </div>

    {{-- MODAL IMPORT --}}
    <div class="modal fade" id="importExpenseModal" tabindex="-1" aria-labelledby="importExpenseLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="importExpenseLabel">Import Expense Poslite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('laporan.expensePoslite.import') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <div class="fw-bold mb-1">Format kolom Excel (header):</div>
                            <ul class="mb-0">
                                <li><code>branch</code> atau <code>nama_outlet</code></li>
                                <li><code>date</code> atau <code>expense_date</code></li>
                                <li><code>purpose</code></li>
                                <li><code>note</code> (optional)</li>
                                <li><code>amount</code></li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Excel</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv"
                                required>
                            <small class="text-muted">Support: .xlsx / .xls / .csv</small>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="ignore_fk"
                                name="ignore_fk">
                            <label class="form-check-label" for="ignore_fk">
                                Abaikan validasi FK sementara (tidak direkomendasikan)
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import Sekarang
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

{{-- NOTE:
    Kalau header kamu sudah include jQuery, hapus baris jQuery di bawah ini.
--}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- DataTables --}}
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

{{-- Buttons (tetap dipakai untuk trigger export via tombol custom) --}}
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

{{-- Export deps --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
    $(function() {
        // Select2 Outlet
        $('#outlet_id').select2({
            width: '100%',
            placeholder: '-- Semua Outlet --',
            allowClear: true
        });

        // DataTables init
        const table = $('#expenseTable').DataTable({
            pageLength: 10,
            lengthMenu: [
                [5, 10, 25, 50, 100, 200, 500],
                [5, 10, 25, 50, 100, 200, 500]
            ],
            order: [
                [2, 'desc']
            ], // sort by Date desc

            // IMPORTANT: ini bikin tombol bawaan DataTables hilang
            dom: '<"row mb-2"<"col-md-6"l><"col-md-6 d-flex justify-content-end"f>>rt<"row mt-2"<"col-md-6"i><"col-md-6 d-flex justify-content-end"p>>',

            // tombol export tetap ada (tapi tidak tampil), dipakai lewat trigger custom
            buttons: [{
                    extend: 'excelHtml5',
                    title: 'Expense Poslite ({{ $start }} - {{ $end }})',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    title: 'Expense Poslite ({{ $start }} - {{ $end }})',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                },
                {
                    extend: 'print',
                    title: 'Expense Poslite ({{ $start }} - {{ $end }})',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    }
                }
            ],

            columnDefs: [{
                    targets: 0,
                    orderable: false
                },
                {
                    targets: 5,
                    className: 'text-end'
                }
            ],

            language: {
                search: "Cari:",
                lengthMenu: "Show _MENU_",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            }
        });

        // Trigger export dari tombol custom header
        $('#btnExcel').on('click', function() {
            table.button(0).trigger();
        });

        $('#btnPdf').on('click', function() {
            table.button(1).trigger();
        });

        $('#btnPrint').on('click', function() {
            table.button(2).trigger();
        });

        // Import placeholder
        document.getElementById('btnImport').addEventListener('click', function() {
            const el = document.getElementById('importExpenseModal');
            const modal = new bootstrap.Modal(el);
            modal.show();
        });
    });
</script>

@include('Temp.Investor.footer')
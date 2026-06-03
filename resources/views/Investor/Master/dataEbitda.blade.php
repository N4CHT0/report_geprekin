{{-- resources/views/investor/ebitda/index.blade.php --}}
@include('Temp.Investor.header')

<style>
    /* Select2 dropdown scroll */
    .select2-container .select2-dropdown {
        max-height: 240px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .card {
        border-radius: 14px;
    }

    .table thead th {
        white-space: nowrap;
    }

    .badge {
        font-weight: 600;
    }

    /* FIX BORDER INPUT (Bootstrap + Select2) */
    .form-control,
    .form-select,
    .select2-container--bootstrap-5 .select2-selection--single {
        border: 1px solid #ced4da !important;
        border-radius: .5rem;
        background-color: #fff;
    }

    /* Tinggi biar sejajar */
    .form-control-sm,
    .form-select-sm,
    .select2-container--bootstrap-5 .select2-selection--single {
        min-height: 31px;
        padding-top: .25rem;
        padding-bottom: .25rem;
    }

    /* Biar teks select2 sejajar */
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 1.6;
        padding-left: .5rem;
        padding-right: 2rem;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 100%;
    }

    /* Filter box */
    .filter-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
    }
</style>

@php
    // baca querystring (pola audit)
    $reqPeriod = request('period', $period ?? now()->format('Y-m')); // YYYY-MM
    $reqOutlet = request('outlet_id', '');
    $hasFilter = request()->filled('period') || request()->filled('outlet_id');
@endphp

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0">EBITDA</h5>
                            <small class="text-muted">Rekap EBITDA per outlet</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('investor.ebitda.master') }}">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </a>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"
                                type="button">
                                <i class="bi bi-plus-circle me-1"></i> Tambah EBITDA
                            </button>
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#importModal" type="button">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import
                            </button>
                        </div>
                    </div>
                </div>

                {{-- MODAL IMPORT --}}
                <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <form class="modal-content" method="POST" action="{{ route('investor.ebitda.import') }}"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="modal-header bg-light">
                                <h5 class="modal-title">Import Finance Monthly (Excel)</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="alert alert-info mb-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <b>Gunakan template resmi</b> agar kolom sesuai.
                                            <div class="small text-muted">Sheet: Finance_Input (utama) & PnL_Detail
                                                (opsional)</div>
                                        </div>
                                        <a class="btn btn-sm btn-outline-primary"
                                            href="{{ route('investor.ebitda.template') }}">
                                            <i class="bi bi-download me-1"></i> Download Template
                                        </a>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label">Periode (opsional)</label>
                                        {{-- NOTE: id beda biar gak bentrok dengan filter utama --}}
                                        <input type="month" class="form-control form-control-sm" id="import_period"
                                            value="{{ $reqPeriod }}">
                                        <small class="text-muted">Jika period sudah ada per baris di Excel, ini boleh
                                            kosong.</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">File Excel</label>
                                        <input type="file" class="form-control form-control-sm" name="file"
                                            accept=".xlsx,.xls" required>
                                        <small class="text-muted">Format: .xlsx / .xls</small>
                                    </div>
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger mt-3 mb-0">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-1"></i> Upload & Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">

                    {{-- FILTER (POLA AUDIT: FORM GET, reload page) --}}
                    <form class="filter-box mb-3" method="GET" action="{{ route('investor.ebitda.master') }}">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <label class="form-label mb-1">Periode</label>
                                <input name="period" type="month" class="form-control form-control-sm" id="f_period"
                                    value="{{ $reqPeriod }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label mb-1">Outlet</label>
                                <select name="outlet_id" class="form-select-sm select2 w-100" id="f_outlet_id">
                                    <option value="">Semua Outlet</option>
                                    @foreach ($outlets as $o)
                                        <option value="{{ $o->id }}" @selected((string) $reqOutlet === (string) $o->id)>
                                            {{ $o->nama_outlet }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                                <a class="btn btn-outline-secondary btn-sm"
                                    href="{{ route('investor.ebitda.master') }}">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table id="ebitdaTable" class="table table-striped table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th style="width:60px">No</th>
                                    <th>Periode</th>
                                    <th>Area</th>
                                    <th>Mitra</th>
                                    <th class="text-start">Outlet</th>
                                    <th class="text-end">Omset</th>
                                    <th class="text-end">HPP</th>
                                    <th class="text-end">EBITDA</th>
                                    <th>Margin</th>
                                    <th>Status</th>
                                    <th style="width:120px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- MODAL TAMBAH (DESAIN SAJA) --}}
            <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Tambah EBITDA</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Periode</label>
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="Contoh: 2025-12">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Area</label>
                                    <select class="form-select-sm select2 w-100">
                                        <option value="">-- Pilih Area --</option>
                                        <option>JAWA TIMUR</option>
                                        <option>JAWA TENGAH</option>
                                        <option>JAWA BARAT</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Mitra</label>
                                    <select class="form-select-sm select2 w-100">
                                        <option value="">-- Pilih Mitra --</option>
                                        <option>Mitra A</option>
                                        <option>Mitra B</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status Outlet</label>
                                    <select class="form-select form-select-sm">
                                        <option>Existing</option>
                                        <option>Go</option>
                                        <option>Tutup</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Kode Outlet</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="OT-0001">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Outlet</label>
                                    <input type="text" class="form-control form-control-sm"
                                        placeholder="Nama outlet">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Omset</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">HPP</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">EBITDA</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="0">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Margin (%)</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm"
                                        placeholder="0.00">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MODAL EDIT (DESAIN SAJA) --}}
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Edit EBITDA</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Periode</label>
                                    <input type="text" class="form-control form-control-sm" value="2025-12">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal</label>
                                    <input type="date" class="form-control form-control-sm" value="2025-12-18">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Area</label>
                                    <select class="form-select-sm select2 w-100">
                                        <option>JAWA TIMUR</option>
                                        <option>JAWA TENGAH</option>
                                        <option>JAWA BARAT</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Mitra</label>
                                    <select class="form-select-sm select2 w-100">
                                        <option>Mitra A</option>
                                        <option>Mitra B</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status Outlet</label>
                                    <select class="form-select form-select-sm">
                                        <option selected>Existing</option>
                                        <option>Go</option>
                                        <option>Tutup</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Kode Outlet</label>
                                    <input type="text" class="form-control form-control-sm" value="OT-0001">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Outlet</label>
                                    <input type="text" class="form-control form-control-sm" value="Outlet Contoh">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Omset</label>
                                    <input type="number" class="form-control form-control-sm" value="150000000">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">HPP</label>
                                    <input type="number" class="form-control form-control-sm" value="90000000">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">EBITDA</label>
                                    <input type="number" class="form-control form-control-sm" value="18000000">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Margin (%)</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm"
                                        value="12.00">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Select2 global
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Ambil querystring (pola audit)
        const params = new URLSearchParams(window.location.search);
        const period = params.get('period') || '';
        const outletId = params.get('outlet_id') || '';
        const hasFilter = (period !== '' || outletId !== '');

        // DataTable
        $('#ebitdaTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            searching: false,
            ajax: {
                url: "{{ route('investor.ebitda.data') }}",
                data: function(d) {
                    d.hasFilter = hasFilter ? 1 : 0;
                    d.period = period;
                    d.outlet_id = outletId;
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'period_month'
                },
                {
                    data: 'nama_area'
                },
                {
                    data: 'nama_mitra'
                },
                {
                    data: 'nama_outlet'
                },
                {
                    data: 'sales',
                    className: 'text-end'
                },
                {
                    data: 'hpp',
                    className: 'text-end'
                },
                {
                    data: 'ebitda',
                    className: 'text-end'
                },
                {
                    data: 'margin',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'aksi',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                emptyTable: "Silakan pilih filter (periode/outlet) lalu klik Cari.",
                zeroRecords: "Data tidak ditemukan."
            }
        });
    });
</script>

@if ($errors->any())
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            new bootstrap.Modal(document.getElementById('importModal')).show();
        });
    </script>
@endif

@include('Temp.Investor.footer')
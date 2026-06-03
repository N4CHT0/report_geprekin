@include('Temp.Investor.header')

<style>
    .card { border-radius: 14px; }

    .filter-box {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
    }

    /* Responsive */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    table#rtoTable { width: 100% !important; table-layout: auto; }
    #rtoTable th, #rtoTable td { white-space: nowrap; vertical-align: middle; }
    #rtoTable thead th { font-weight: 600; }

    /* Hilangkan overlay */
    div.dataTables_processing { display: none !important; }

    @media (max-width: 576px) {
        .btn-mobile-full { width: 100%; }
        #rtoTable th, #rtoTable td { padding: .35rem .5rem; }
    }
</style>

@php
    $currentYear = (int) date('Y');
    $startYear   = $currentYear - 5;
    $endYear     = $currentYear + 1;
@endphp

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            {{-- FLASH MESSAGE (dari store/import/update) --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0">Opening Progress</h5>
                            <small class="text-muted">Rekap opening progress per tahun (Jan–Des)</small>
                        </div>

                        {{-- BUTTONS: Refresh + Tambah + Import --}}
                        <div class="d-flex gap-2 flex-wrap">
                            <a class="btn btn-outline-secondary btn-sm btn-mobile-full"
                               href="{{ route('investor.rto.master') }}">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </a>

                            <button class="btn btn-primary btn-sm btn-mobile-full"
                                    data-bs-toggle="modal" data-bs-target="#addModal" type="button">
                                <i class="bi bi-plus-circle me-1"></i> Tambah
                            </button>

                            <button class="btn btn-outline-primary btn-sm btn-mobile-full"
                                    data-bs-toggle="modal" data-bs-target="#importModal" type="button">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    {{-- ALERT RUNTIME --}}
                    <div id="alertBox" class="alert d-none" role="alert"></div>

                    {{-- FILTER: DROPDOWN TAHUN --}}
                    <form id="filterForm" class="filter-box mb-3">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-4">
                                <label class="form-label mb-1">Tahun</label>
                                <select id="yearSelect" class="form-select form-select-sm">
                                    <option value="">-- Pilih Tahun --</option>
                                    @for ($y = $endYear; $y >= $startYear; $y--)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                                <div class="small text-muted mt-1">Klik untuk memilih tahun (tanpa mengetik).</div>
                            </div>

                            <div class="col-12 col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                                <button type="button" id="btnReset" class="btn btn-outline-secondary btn-sm">
                                    Reset
                                </button>
                            </div>
                        </div>
                    </form>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table id="rtoTable" class="table table-striped table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th style="width:60px">No</th>
                                    <th style="min-width:140px">Bulan</th>
                                    <th style="min-width:90px">Target</th>
                                    <th style="min-width:120px">Plan</th>
                                    <th style="min-width:90px">Done</th>
                                    <th style="min-width:120px">On Schedule</th>
                                    <th style="min-width:90px">Hold</th>
                                    <th style="min-width:100px">Rejected</th>
                                    <th style="min-width:100px">Backlog</th>
                                    <th style="min-width:110px">Lead Time</th>
                                    <th style="min-width:80px">Percentage</th>
                                    <th style="width:120px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- ================= MODAL IMPORT ================= --}}
            <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('investor.rto.import') }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Import Opening Progress (Excel)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <b>Gunakan template resmi</b> agar kolom sesuai.
                                    <div class="small text-muted">
                                        Kolom: bulan, target, plan_opening, done, on_schedule, hold, rejected, backlog, lead_time, percentage
                                    </div>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('investor.rto.template') }}">
                                    <i class="bi bi-download me-1"></i> Download Template
                                </a>
                            </div>

                            <div class="mb-2">
                                <label class="form-label">File Excel</label>
                                <input type="file" class="form-control form-control-sm" name="file"
                                       accept=".xlsx,.xls" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload me-1"></i> Upload & Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ================= MODAL TAMBAH ================= --}}
            <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('investor.rto.store') }}">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Tambah Opening Progress</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Bulan</label>
                                    <input name="bulan" type="month" class="form-control form-control-sm" required>
                                </div>

                                @foreach ([
                                    'target' => 'Target',
                                    'plan_opening' => 'Plan Opening',
                                    'done' => 'Done',
                                    'on_schedule' => 'On Schedule',
                                    'hold' => 'Hold',
                                    'rejected' => 'Rejected',
                                    'backlog' => 'Backlog',
                                    'lead_time' => 'Lead Time',
                                ] as $name => $label)
                                    <div class="col-md-4">
                                        <label class="form-label">{{ $label }}</label>
                                        <input name="{{ $name }}" type="number" class="form-control form-control-sm" value="0">
                                    </div>
                                @endforeach

                                <div class="col-md-4">
                                    <label class="form-label">Percentage</label>
                                    <input name="percentage" type="number" step="0.01"
                                           class="form-control form-control-sm" placeholder="contoh 75.5">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ================= MODAL EDIT ================= --}}
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form id="editForm" class="modal-content" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Edit Opening Progress</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Bulan</label>
                                    <input id="edit_bulan" name="bulan" type="month" class="form-control form-control-sm" required>
                                </div>

                                @foreach ([
                                    'target' => 'Target',
                                    'plan_opening' => 'Plan Opening',
                                    'done' => 'Done',
                                    'on_schedule' => 'On Schedule',
                                    'hold' => 'Hold',
                                    'rejected' => 'Rejected',
                                    'backlog' => 'Backlog',
                                    'lead_time' => 'Lead Time',
                                ] as $name => $label)
                                    <div class="col-md-4">
                                        <label class="form-label">{{ $label }}</label>
                                        <input id="edit_{{ $name }}" name="{{ $name }}" type="number" class="form-control form-control-sm">
                                    </div>
                                @endforeach

                                <div class="col-md-4">
                                    <label class="form-label">Percentage</label>
                                    <input id="edit_percentage" name="percentage" type="number" step="0.01"
                                           class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
$(function () {
    const $alertBox = $('#alertBox');

    function showAlert(type, msg) {
        $alertBox
            .removeClass('d-none alert-success alert-info alert-warning alert-danger')
            .addClass('alert-' + type)
            .html(msg);
    }
    function hideAlert() {
        $alertBox.addClass('d-none')
            .removeClass('alert-success alert-info alert-warning alert-danger')
            .html('');
    }

    // ===== Bulan -> Nama bulan saja =====
    const monthNames = {
        '01':'Januari','02':'Februari','03':'Maret','04':'April','05':'Mei','06':'Juni',
        '07':'Juli','08':'Agustus','09':'September','10':'Oktober','11':'November','12':'Desember'
    };
    function formatBulan(ym) {
        if (!ym || ym.length < 7) return ym;
        const m = ym.substring(5,7);
        return monthNames[m] || m;
    }

    showAlert('info', 'Pilih <b>Tahun</b> dari dropdown, lalu klik <b>Cari</b>.');

    // ===== DataTable (rekap 12 bulan) =====
    const table = $('#rtoTable').DataTable({
        processing: false,
        serverSide: true,
        searching: false,
        paging: false,
        info: false,
        ordering: false,
        scrollX: false,
        autoWidth: false,
        deferLoading: 0,

        ajax: {
            url: "{{ route('investor.rto.data') }}",
            data: function (d) {
                d.year = $('#yearSelect').val() || '';
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan saat mengambil data.';
                try {
                    const json = JSON.parse(xhr.responseText);
                    if (json.message) msg = json.message;
                } catch(e) {}
                showAlert('danger', '<b>Error:</b> ' + msg);
            }
        },

        columns: [
            { data: 'DT_RowIndex', className:'text-center' },
            { data: 'bulan', className:'text-start', render: (d)=>formatBulan(d) },
            { data: 'target', className:'text-center' },
            { data: 'plan_opening', className:'text-center' },
            { data: 'done', className:'text-center' },
            { data: 'on_schedule', className:'text-center' },
            { data: 'hold', className:'text-center' },
            { data: 'rejected', className:'text-center' },
            { data: 'backlog', className:'text-center' },
            {
                data: 'lead_time',
                className:'text-center',
                render: (d)=> (d===null||d==='') ? '-' : (isNaN(parseFloat(d)) ? d : parseFloat(d).toFixed(2))
            },
            { data: 'percentage', className:'text-center' },
            { data: 'aksi', orderable:false, searchable:false, className:'text-center' },
        ],

        language: {
            emptyTable: "Pilih Tahun lalu klik Cari",
            zeroRecords: "Data tidak ditemukan"
        }
    });

    // Submit filter
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        hideAlert();

        const year = $('#yearSelect').val();
        if (!year) {
            showAlert('warning', 'Tahun wajib dipilih.');
            return;
        }

        table.ajax.reload(function () {
            const json = table.ajax.json();
            if (!json || !json.data || json.data.length === 0) {
                showAlert('info', 'Tidak ada data untuk tahun tersebut.');
            } else {
                hideAlert();
            }
        }, true);
    });

    // Reset
    $('#btnReset').on('click', function () {
        $('#yearSelect').val('');
        table.clear().draw();
        showAlert('info', 'Pilih <b>Tahun</b> dari dropdown, lalu klik <b>Cari</b>.');
    });

    // ===== Handler tombol Edit (butuh data-id dari BE) =====
    $(document).on('click', '.btn-edit', function () {
        const btn = $(this);

        const id = btn.data('id');
        if (!id) {
            alert('ID tidak tersedia. Pastikan BE mengirim id (mis: MAX(id) per bulan).');
            return;
        }

        $('#editForm').attr('action', `{{ url('/investor/user/investor/rto/update') }}/${id}`);

        $('#edit_bulan').val(btn.data('bulan') || '');
        $('#edit_target').val(btn.data('target') || 0);
        $('#edit_plan_opening').val(btn.data('plan_opening') || 0);
        $('#edit_done').val(btn.data('done') || 0);
        $('#edit_on_schedule').val(btn.data('on_schedule') || 0);
        $('#edit_hold').val(btn.data('hold') || 0);
        $('#edit_rejected').val(btn.data('rejected') || 0);
        $('#edit_backlog').val(btn.data('backlog') || 0);
        $('#edit_lead_time').val(btn.data('lead_time') || '');
        $('#edit_percentage').val(btn.data('percentage') || '');
    });

});
</script>

@include('Temp.Investor.footer')
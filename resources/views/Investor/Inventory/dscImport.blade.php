{{-- resources/views/Investor/Inventory/dscImport.blade.php --}}
@include('Temp.Investor.header')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    // data outlets diharapkan dikirim dari controller: $outlets
    $outlets = $outlets ?? [];
    $outletId = request('outlet_id', '');
    $start = request('start_date', date('Y-m-01'));
    $end = request('end_date', date('Y-m-t'));
    $shift = request('shift', 'all'); // all | 1 | 2
@endphp

<style>
    /* =========================
        DSC IMPORT - GREEN TEMPLATE
        Officeable, readable, sharp
    ========================== */

    :root {
        --bg: #f5f7fb;
        --card: #ffffff;
        --border: #e6e8ef;
        --border2: #d6dae6;
        --text: #101828;
        --muted: #667085;
        --shadow: 0 10px 22px rgba(16, 24, 40, .06);
        --radius: 16px;

        /* DSC green */
        --dsc-green: #4f7f2a;
        --dsc-green-dark: #3c651f;
        --dsc-green-soft: rgba(79, 127, 42, .12);

        --warn: #d97706;
        --warnSoft: rgba(217, 119, 6, .12);
        --danger: #dc2626;
        --dangerSoft: rgba(220, 38, 38, .10);
        --primary: #2563eb;
        --primarySoft: rgba(37, 99, 235, .10);
        --success: #16a34a;
        --successSoft: rgba(22, 163, 74, .10);
    }

    .page-shell {
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: var(--card);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .page-head {
        padding: 18px 18px 12px;
        border-bottom: 1px solid var(--border);
        background:
            radial-gradient(1200px 420px at 12% -10%, rgba(79, 127, 42, .18), transparent 60%),
            radial-gradient(900px 380px at 95% 0%, rgba(37, 99, 235, .12), transparent 55%),
            linear-gradient(180deg, #fff 0%, #fbfcff 100%);
    }

    .title-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .title {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .title h4 {
        margin: 0;
        font-weight: 1000;
        letter-spacing: .2px;
        color: var(--text);
    }

    .badge-dsc {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 950;
        color: var(--dsc-green-dark);
        background: var(--dsc-green-soft);
        border: 1px solid rgba(79, 127, 42, .24);
        white-space: nowrap;
    }

    .subtitle {
        margin-top: 6px;
        color: var(--muted);
        font-size: .9rem;
        font-weight: 700;
        line-height: 1.35;
        max-width: 980px;
    }

    .toolbar {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .btn {
        border-radius: 12px !important;
        font-weight: 950 !important;
    }

    .btn-ghost {
        background: #fff !important;
        border: 1px solid var(--border2) !important;
        color: var(--text) !important;
    }

    .content {
        padding: 14px 18px 18px;
        background: var(--bg);
    }

    .card-soft {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 6px 14px rgba(16, 24, 40, .05);
    }

    .card-soft .card-head {
        padding: 12px 14px;
        border-bottom: 1px solid var(--border);
        font-weight: 1000;
        color: var(--text);
        background: linear-gradient(180deg, #fff 0%, #fafbff 100%);
        border-top-left-radius: 14px;
        border-top-right-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .card-soft .card-body {
        padding: 14px;
    }

    .help {
        font-size: .84rem;
        color: var(--muted);
        font-weight: 800;
    }

    .form-label {
        font-weight: 950;
        color: var(--muted);
        font-size: .8rem;
        margin-bottom: 6px;
    }

    .form-control,
    .form-select {
        height: 40px;
        border-radius: 12px;
        font-weight: 900;
        border-color: var(--border2);
    }

    .grid {
        display: grid;
        grid-template-columns: 1.3fr .8fr .8fr .6fr .9fr;
        gap: 10px;
        align-items: end;
    }

    @media (max-width: 1200px) {
        .grid {
            grid-template-columns: 1fr 1fr;
        }

        .span-2 {
            grid-column: span 2;
        }
    }

    @media (max-width: 576px) {
        .grid {
            grid-template-columns: 1fr;
        }

        .span-2 {
            grid-column: auto;
        }
    }

    .kpi-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 10px;
    }

    @media (max-width: 992px) {
        .kpi-row {
            grid-template-columns: 1fr;
        }
    }

    .kpi {
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        padding: 12px 14px;
        box-shadow: 0 6px 14px rgba(16, 24, 40, .05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .kpi .label {
        font-size: .8rem;
        color: var(--muted);
        font-weight: 900;
    }

    .kpi .value {
        font-size: 1.05rem;
        color: var(--text);
        font-weight: 1000;
    }

    .kpi .icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        border: 1px solid var(--border);
        background: #f8fafc;
        color: var(--text);
        font-size: 1.15rem;
    }

    .kpi.success .icon {
        border-color: rgba(22, 163, 74, .22);
        background: var(--successSoft);
        color: var(--success);
    }

    .kpi.primary .icon {
        border-color: rgba(37, 99, 235, .22);
        background: var(--primarySoft);
        color: var(--primary);
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: #fff;
        font-weight: 950;
        font-size: .82rem;
        color: var(--text);
        box-shadow: 0 6px 14px rgba(16, 24, 40, .05);
    }

    .dsc-scroll {
        max-height: 68vh;
        overflow: auto;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 6px 14px rgba(16, 24, 40, .05);
    }

    /* ===== TABLE (green header like template) ===== */
    .dsc-table th,
    .dsc-table td {
        border: 1px solid #1f2937;
        padding: 7px 8px;
        font-size: .86rem;
        white-space: nowrap;
        vertical-align: middle;
        background: #fff;
        color: #111827;
    }

    .dsc-table thead th {
        position: sticky;
        top: 0;
        z-index: 5;
        background: var(--dsc-green);
        color: #0b1a09;
        font-weight: 1000;
        text-align: center;
    }

    .dsc-table thead th.sub {
        background: var(--dsc-green-dark);
        color: #071306;
        font-weight: 1000;
    }

    .dsc-table td.num {
        text-align: right;
        font-variant-numeric: tabular-nums;
        font-weight: 900;
    }

    .dsc-table td.center {
        text-align: center;
        font-weight: 900;
    }

    .dsc-table td.item {
        font-weight: 1000;
    }

    .sticky-1 {
        position: sticky;
        left: 0;
        z-index: 4;
        background: #fff;
    }

    .sticky-2 {
        position: sticky;
        left: 60px;
        z-index: 4;
        background: #fff;
        box-shadow: 10px 0 0 rgba(0, 0, 0, .05);
    }

    thead .sticky-1,
    thead .sticky-2 {
        background: var(--dsc-green);
        z-index: 6;
        box-shadow: none;
    }

    .w-no {
        width: 60px;
        min-width: 60px;
    }

    .w-name {
        width: 280px;
        min-width: 280px;
    }

    .w-sat {
        width: 80px;
        min-width: 80px;
    }

    .w-num {
        width: 120px;
        min-width: 120px;
    }

    .w-wide {
        width: 320px;
        min-width: 320px;
    }

    .cell-warn {
        background: var(--warnSoft) !important;
        color: #92400e !important;
        font-weight: 1000 !important;
    }

    .cell-bad {
        background: var(--dangerSoft) !important;
        color: var(--danger) !important;
        font-weight: 1000 !important;
    }

    .alert-soft {
        border-radius: 14px;
        border: 1px solid var(--border);
        padding: 12px 14px;
        background: #fff;
        box-shadow: 0 6px 14px rgba(16, 24, 40, .05);
    }

    .alert-soft.primary {
        border-color: rgba(37, 99, 235, .22);
        background: rgba(37, 99, 235, .06);
    }

    .alert-soft.success {
        border-color: rgba(22, 163, 74, .22);
        background: rgba(22, 163, 74, .06);
    }

    .alert-soft.warning {
        border-color: rgba(217, 119, 6, .28);
        background: rgba(217, 119, 6, .08);
    }

    .alert-soft.danger {
        border-color: rgba(220, 38, 38, .28);
        background: rgba(220, 38, 38, .07);
    }

    .alert-soft .t {
        font-weight: 1000;
        color: var(--text);
        margin-bottom: 4px;
    }

    .alert-soft .d {
        color: var(--muted);
        font-weight: 800;
        margin: 0;
        font-size: .86rem;
    }

    .mini {
        font-size: .78rem;
        color: var(--muted);
        font-weight: 800;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            <div class="page-shell">
                <div class="page-head">
                    <div class="title-row">
                        <div>
                            <div class="title">
                                <h4>DSC Import (Bulk Harian)</h4>
                                <span class="badge-dsc">
                                    <i class="bi bi-file-earmark-spreadsheet"></i>
                                    Template Hijau / DSC
                                </span>
                            </div>
                            <div class="subtitle">
                                Upload <b>1 file = 1 outlet</b>. Sheet mengikuti hari (1–30/31). Sheet kosong akan
                                <b>di-skip</b>.
                                Import melakukan <b>fuzzy match</b> nama bahan Excel → <code>tbl_bahan_dsc</code>, lalu
                                insert/upsert ke
                                <code>tbl_stock</code> dan <code>tbl_sales_shift</code>.
                            </div>
                        </div>

                        <div class="toolbar">
                            <button class="btn btn-ghost" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </button>
                            <button class="btn btn-outline-secondary btn-ghost" type="button" id="btnDownloadTemplate">
                                <i class="bi bi-download me-1"></i> Template
                            </button>
                        </div>
                    </div>
                </div>

                <div class="content">

                    {{-- FILTER + UPLOAD --}}
                    <div class="card-soft mb-3">
                        <div class="card-head">
                            <div><i class="bi bi-funnel me-1"></i> Parameter Import</div>
                            <span class="pill" id="pillStatus"><i class="bi bi-circle-fill"></i> Belum Preview</span>
                        </div>
                        <div class="card-body">
                            <div class="alert-soft primary mb-3">
                                <div class="t"><i class="bi bi-info-circle me-1"></i> Catatan</div>
                                <p class="d">
                                    - Start/End date dipakai untuk mapping sheet hari: mis. 2026-01-01 s/d 2026-01-30 →
                                    sheet “1..30”.<br>
                                    - Shift bisa <b>1</b>, <b>2</b>, atau <b>All</b> (apply ke dua shift sesuai
                                    kebijakan BE).<br>
                                    - Sales shift 1 & 2 disimpan ke <code>tbl_sales_shift</code>.
                                </p>
                            </div>

                            <div class="grid">
                                <div>
                                    <label class="form-label"><i class="bi bi-person-badge me-1"></i>Nama
                                        Petugas</label>
                                    <input type="text" id="nama_petugas" class="form-control"
                                        placeholder="Nama PIC / Petugas">
                                    <div class="mini mt-1">Wajib diisi. Akan tersimpan sebagai PIC import.</div>
                                </div>

                                <div class="span-2">
                                    <label class="form-label"><i class="bi bi-shop me-1"></i>Outlet</label>
                                    <select id="outlet_id" class="form-select select2" style="width:100%;">
                                        <option value="">Pilih Outlet</option>
                                        @foreach ($outlets as $o)
                                            <option value="{{ $o->id }}"
                                                {{ (string) $outletId === (string) $o->id ? 'selected' : '' }}>
                                                {{ $o->nama_outlet }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mini mt-1">Nama outlet diambil dari sistem (dropdown).</div>
                                </div>

                                <div>
                                    <label class="form-label"><i class="bi bi-calendar2-range me-1"></i>Start
                                        Date</label>
                                    <input type="date" id="start_date" class="form-control"
                                        value="{{ $start }}">
                                </div>

                                <div>
                                    <label class="form-label"><i class="bi bi-calendar2-range me-1"></i>End Date</label>
                                    <input type="date" id="end_date" class="form-control"
                                        value="{{ $end }}">
                                </div>

                                <div>
                                    <label class="form-label"><i class="bi bi-hourglass-split me-1"></i>Shift</label>
                                    <select id="shift" class="form-select">
                                        <option value="all" {{ $shift === 'all' ? 'selected' : '' }}>All Shift
                                        </option>
                                        <option value="1" {{ $shift === '1' ? 'selected' : '' }}>Shift 1</option>
                                        <option value="2" {{ $shift === '2' ? 'selected' : '' }}>Shift 2</option>
                                    </select>
                                    <div class="mini mt-1">“All” → apply sesuai aturan BE.</div>
                                </div>
                            </div>

                            <div class="kpi-row">
                                <div class="kpi primary">
                                    <div>
                                        <div class="label">Sales Shift 1 (Rp)</div>
                                        <div class="value">
                                            <input type="number" step="1" id="sales_s1" class="form-control"
                                                placeholder="0">
                                        </div>
                                    </div>
                                    <div class="icon"><i class="bi bi-1-circle"></i></div>
                                </div>

                                <div class="kpi success">
                                    <div>
                                        <div class="label">Sales Shift 2 (Rp)</div>
                                        <div class="value">
                                            <input type="number" step="1" id="sales_s2" class="form-control"
                                                placeholder="0">
                                        </div>
                                    </div>
                                    <div class="icon"><i class="bi bi-2-circle"></i></div>
                                </div>

                                <div class="kpi">
                                    <div style="width:100%;">
                                        <div class="label">Upload File (xlsx/xls/csv)</div>
                                        <input type="file" id="file" class="form-control"
                                            accept=".xlsx,.xls,.csv">
                                        <div class="mini mt-1">1 file = 1 outlet. Sheet: “1..30/31”.</div>
                                    </div>
                                    <div class="icon"><i class="bi bi-upload"></i></div>
                                </div>
                            </div>

                            <div class="mt-3 d-flex gap-2 flex-wrap align-items-center">
                                <button class="btn btn-warning" id="btnPreview" type="button">
                                    <i class="bi bi-search me-1"></i> Preview Import
                                </button>
                                <button class="btn btn-primary" id="btnApply" type="button" disabled>
                                    <i class="bi bi-cloud-arrow-up me-1"></i> Apply Import
                                </button>
                                <button class="btn btn-ghost" id="btnReset" type="button">
                                    <i class="bi bi-eraser me-1"></i> Reset
                                </button>
                                <div class="help" id="meta">Belum preview.</div>
                            </div>

                        </div>
                    </div>

                    {{-- ERROR / WARNING --}}
                    <div class="row g-3 mb-3">
                        <div class="col-lg-6">
                            <div class="card-soft">
                                <div class="card-head"><i class="bi bi-exclamation-triangle me-1"></i> Errors</div>
                                <div class="card-body">
                                    <div id="errorsWrap" class="alert-soft danger" style="display:none;">
                                        <div class="t">Import ditahan karena error</div>
                                        <ul class="mb-0" id="errors"></ul>
                                    </div>
                                    <div class="help" id="errorsEmpty">Belum ada error.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card-soft">
                                <div class="card-head"><i class="bi bi-info-circle me-1"></i> Warnings (Fuzzy Match)
                                </div>
                                <div class="card-body">
                                    <div id="warnWrap" class="alert-soft warning" style="display:none;">
                                        <div class="t">Perlu dicek</div>
                                        <ul class="mb-0" id="warn"></ul>
                                    </div>
                                    <div class="help" id="warnEmpty">Belum ada warning.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PREVIEW TABLE --}}
                    <div class="card-soft">
                        <div class="card-head">
                            <div><i class="bi bi-table me-1"></i> Preview Data (Template Hijau)</div>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <span class="pill" id="pillDays"><i class="bi bi-calendar3"></i> Days: 0</span>
                                <span class="pill" id="pillRows"><i class="bi bi-list-ol"></i> Rows: 0</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="help mb-2">
                                Preview menampilkan baris yang terbaca. Baris dengan match rendah akan diberi tanda
                                (warning).
                                Sheet kosong akan di-skip.
                            </div>

                            <div class="dsc-scroll">
                                <table class="table dsc-table mb-0" id="previewTable" style="min-width:2200px;">
                                    <thead>
                                        <tr>
                                            <th class="w-no sticky-1" rowspan="2">NO</th>
                                            <th class="w-name sticky-2" rowspan="2">NAMA BARANG</th>
                                            <th class="w-sat" rowspan="2">SAT</th>
                                            <th class="w-num" rowspan="2">OPEN<br>STOCK</th>
                                            <th class="w-num" rowspan="2">PURCHASE</th>
                                            <th class="w-num sub" colspan="2">MUTASI</th>
                                            <th class="w-num" rowspan="2">TOTAL<br>STOK</th>
                                            <th class="w-num" rowspan="2">ENDING<br>STOCK</th>
                                            <th class="w-num" rowspan="2">ACTUAL<br>USED</th>
                                            <th class="w-num sub" colspan="3">WASTE</th>
                                            <th class="w-num" rowspan="2">ACTUAL<br>TEPUNG</th>
                                            <th class="w-num" rowspan="2">UANG PLUS<br>(RP)</th>
                                            <th class="w-wide" rowspan="2">KETERANGAN</th>
                                            <th class="w-num" rowspan="2">HARI</th>
                                            <th class="w-num" rowspan="2">SHIFT</th>
                                            <th class="w-wide" rowspan="2">MATCH INFO</th>
                                        </tr>
                                        <tr>
                                            <th class="w-num sub">IN</th>
                                            <th class="w-num sub">OUT</th>
                                            <th class="w-num sub">PRODUCT</th>
                                            <th class="w-num sub">BAHAN</th>
                                            <th class="w-num sub">TEPUNG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="19" class="text-center text-muted py-4">
                                                Klik <b>Preview Import</b> untuk menampilkan data.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2 mini">
                                * Kolom HARI & SHIFT hanya untuk preview (mapping sheet + shift form). Match Info
                                menampilkan hasil fuzzy/mapping.
                            </div>
                        </div>
                    </div>

                </div> {{-- content --}}
            </div> {{-- page-shell --}}
        </div>
    </div>
</main>

{{-- =========================
    IMPORTANT:
    Load libraries BEFORE our script.
    (Kalau Temp.Investor.header sudah load, ini tetap aman.)
========================= --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ===============================
    // ENDPOINT PLACEHOLDER (sesuaikan)
    // ===============================
    const BASE_URL = `{{ url('') }}`;
    const URL_PREVIEW = BASE_URL + '/dsc/import-preview-bulk';
    const URL_APPLY = BASE_URL + '/dsc/import-apply-bulk';

    function apiHeaders() {
        const el = document.querySelector('meta[name="csrf-token"]');
        return {
            'X-CSRF-TOKEN': el ? el.content : '',
            'Accept': 'application/json'
        };
    }

    function fmt(n) {
        const x = Number(n || 0);
        return x.toLocaleString('id-ID', {
            maximumFractionDigits: 2
        });
    }

    function resetUI() {
        $('#btnApply').prop('disabled', true);
        $('#meta').text('Belum preview.');
        $('#pillStatus').html('<i class="bi bi-circle-fill"></i> Belum Preview');
        $('#pillDays').html('<i class="bi bi-calendar3"></i> Days: 0');
        $('#pillRows').html('<i class="bi bi-list-ol"></i> Rows: 0');

        $('#errorsWrap').hide();
        $('#errors').html('');
        $('#errorsEmpty').show().text('Belum ada error.');

        $('#warnWrap').hide();
        $('#warn').html('');
        $('#warnEmpty').show().text('Belum ada warning.');

        $('#previewTable tbody').html(`
            <tr>
                <td colspan="19" class="text-center text-muted py-4">
                    Klik <b>Preview Import</b> untuk menampilkan data.
                </td>
            </tr>
        `);
    }

    function getForm() {
        const outletId = $('#outlet_id').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const shift = $('#shift').val();
        const namaPetugas = ($('#nama_petugas').val() || '').trim();
        const salesS1 = $('#sales_s1').val() || 0;
        const salesS2 = $('#sales_s2').val() || 0;

        const fileEl = document.getElementById('file');
        const file = fileEl.files && fileEl.files[0];

        return {
            outletId,
            startDate,
            endDate,
            shift,
            namaPetugas,
            salesS1,
            salesS2,
            file
        };
    }

    function validateForm({
        outletId,
        startDate,
        endDate,
        namaPetugas,
        file
    }) {
        if (!namaPetugas) {
            Swal.fire({
                icon: 'warning',
                title: 'Nama petugas wajib diisi'
            });
            return false;
        }
        if (!outletId) {
            Swal.fire({
                icon: 'warning',
                title: 'Outlet wajib dipilih'
            });
            return false;
        }
        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Start-End date wajib diisi'
            });
            return false;
        }
        if (!file) {
            Swal.fire({
                icon: 'warning',
                title: 'File wajib dipilih'
            });
            return false;
        }
        return true;
    }

    function buildFD({
        outletId,
        startDate,
        endDate,
        shift,
        namaPetugas,
        salesS1,
        salesS2,
        file
    }) {
        const fd = new FormData();
        fd.append('outlet_id', outletId);
        fd.append('start_date', startDate);
        fd.append('end_date', endDate);
        fd.append('shift', shift);
        fd.append('sales_shift_1', salesS1);
        fd.append('sales_shift_2', salesS2);
        fd.append('nama_petugas', namaPetugas);
        fd.append('file', file);
        return fd;
    }

    $(document).ready(function() {
        if ($.fn && $.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }

        $('#btnDownloadTemplate').on('click', function() {
            Swal.fire({
                icon: 'info',
                title: 'Template',
                text: 'Tombol ini bisa diarahkan ke file template DSC hijau (xlsx) di storage.'
            });
        });

        $('#btnReset').on('click', function() {
            $('#file').val('');
            resetUI();
        });

        // ===============================
        // PREVIEW
        // ===============================
        $('#btnPreview').on('click', async function() {
            try {
                const form = getForm();
                if (!validateForm(form)) return;

                const fd = buildFD(form);

                Swal.fire({
                    title: 'Preview...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const res = await fetch(URL_PREVIEW, {
                    method: 'POST',
                    headers: {
                        ...apiHeaders()
                    },
                    body: fd
                });

                const json = await res.json().catch(() => ({}));
                Swal.close();

                if (!res.ok || !json.ok) throw new Error(json.message || 'Gagal preview');

                const meta = json.data?.meta || {};
                const items = json.data?.items || [];
                const errors = json.data?.errors || [];
                const warnings = json.data?.warnings || [];

                $('#pillStatus').html('<i class="bi bi-check-circle-fill"></i> Preview OK');
                $('#pillDays').html(`<i class="bi bi-calendar3"></i> Days: ${meta.days || 0}`);
                $('#pillRows').html(
                    `<i class="bi bi-list-ol"></i> Rows: ${items.length || meta.rows || 0}`);
                $('#meta').text(
                    `Preview OK • outlet=${form.outletId} • ${form.startDate} → ${form.endDate} • shift=${form.shift} • valid_rows=${items.length}`
                );

                // errors
                if (errors.length) {
                    $('#errorsEmpty').hide();
                    $('#errorsWrap').show();
                    $('#errors').html(errors.map(e => {
                        const day = e.day ?? '-';
                        const row = e.row ?? '-';
                        const nama = e.nama ?? e.name ?? '-';
                        const msg = e.error ?? e.message ?? 'Error';
                        return `<li>Hari <b>${day}</b> • Row <b>${row}</b> • <b>${nama}</b> — ${msg}</li>`;
                    }).join(''));
                } else {
                    $('#errorsWrap').hide();
                    $('#errors').html('');
                    $('#errorsEmpty').show().text('Tidak ada error.');
                }

                // warnings
                if (warnings.length) {
                    $('#warnEmpty').hide();
                    $('#warnWrap').show();
                    $('#warn').html(warnings.map(w => {
                        const day = w.day ?? '-';
                        const row = w.row ?? '-';
                        const nama = w.nama ?? w.name ?? '-';
                        const msg = w.warning ?? w.message ?? 'Warning';
                        const sug = w.suggest ? ` (saran: ${w.suggest})` : '';
                        return `<li>Hari <b>${day}</b> • Row <b>${row}</b> • <b>${nama}</b> — ${msg}${sug}</li>`;
                    }).join(''));
                } else {
                    $('#warnWrap').hide();
                    $('#warn').html('');
                    $('#warnEmpty').show().text('Tidak ada warning.');
                }

                // render items
                const tbody = items.length ? items.map((it, i) => {
                    const match = it.match || {};
                    const score = (match.score != null) ? Number(match.score).toFixed(2) :
                        '';
                    const bahanId = match.bahan_id != null ? match.bahan_id : '';
                    const method = match.method || '';

                    const matchClass = (score && Number(score) < 0.75) ? 'cell-warn' : '';
                    const nameClass = (score && Number(score) < 0.60) ? 'cell-bad' : '';

                    return `
                        <tr>
                            <td class="center sticky-1">${i+1}</td>
                            <td class="sticky-2 item ${nameClass}">${it.nama_bahan || it.nama || ''}</td>
                            <td class="center">${it.satuan || ''}</td>
                            <td class="num">${fmt(it.open)}</td>
                            <td class="num">${fmt(it.purchase_in)}</td>
                            <td class="num">${fmt(it.mutasi_in)}</td>
                            <td class="num">${fmt(it.mutasi_out)}</td>
                            <td class="num">${fmt(it.total_stock)}</td>
                            <td class="num">${fmt(it.ending_stock)}</td>
                            <td class="num">${fmt(it.actual_used)}</td>
                            <td class="num">${fmt(it.waste_product)}</td>
                            <td class="num">${fmt(it.waste_bahan)}</td>
                            <td class="num">${fmt(it.waste_tepung)}</td>
                            <td class="num">${fmt(it.actual_tepung)}</td>
                            <td class="num">${fmt(it.uang_plus)}</td>
                            <td>${it.keterangan || ''}</td>
                            <td class="center">${it.day ?? ''}</td>
                            <td class="center">${it.shift ?? ''}</td>
                            <td class="${matchClass}">
                                ${bahanId ? `bahan_id=${bahanId}` : 'unmatched'}
                                ${score ? ` • score=${score}` : ''}
                                ${method ? ` • ${method}` : ''}
                            </td>
                        </tr>
                    `;
                }).join('') : `
                    <tr><td colspan="19" class="text-center text-muted py-4">Tidak ada data valid dari file.</td></tr>
                `;

                $('#previewTable tbody').html(tbody);

                const canApply = items.length > 0 && errors.length === 0;
                $('#btnApply').prop('disabled', !canApply);
                if (!canApply) {
                    $('#pillStatus').html(
                        '<i class="bi bi-exclamation-circle-fill"></i> Perlu Perbaikan');
                }

            } catch (err) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.message
                });
                resetUI();
            }
        });

        // ===============================
        // APPLY
        // ===============================
        $('#btnApply').on('click', async function() {
            try {
                const form = getForm();
                if (!validateForm(form)) return;

                const confirm = await Swal.fire({
                    icon: 'question',
                    title: 'Apply Import?',
                    html: `Data akan disimpan ke <code>tbl_stock</code> & <code>tbl_sales_shift</code>.<br>Pastikan preview sudah OK.`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Apply',
                    cancelButtonText: 'Batal'
                });
                if (!confirm.isConfirmed) return;

                const fd = buildFD(form);

                Swal.fire({
                    title: 'Applying...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const res = await fetch(URL_APPLY, {
                    method: 'POST',
                    headers: {
                        ...apiHeaders()
                    },
                    body: fd
                });

                const json = await res.json().catch(() => ({}));
                Swal.close();

                if (!res.ok || !json.ok) throw new Error(json.message || 'Gagal apply');

                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: json.message || 'Import berhasil diterapkan',
                    timer: 1400,
                    showConfirmButton: false
                });

                location.reload();

            } catch (err) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.message
                });
            }
        });

        resetUI();
    });
</script>

@include('Temp.Investor.footer')
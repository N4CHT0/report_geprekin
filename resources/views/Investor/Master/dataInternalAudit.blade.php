@include('Temp.Investor.header')

<style>
    /* === Base === */
    :root {
        --ink: #111827;
        --muted: #6b7280;
        --line: #e5e7eb;
        --head: #f9fafb;
        --head2: #f3f4f6;
        --rowHover: #f9fafb;
        --good: #166534;
        --bad: #991b1b;
        --pill: #eef2ff;
        --pillText: #3730a3;
    }

    .select2-container .select2-dropdown {
        max-height: 200px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .card {
        border-radius: 14px;
    }

    .card-header {
        border-bottom: 1px solid var(--line);
    }

    /* === Table wrapper === */
    .audit-wrap {
        overflow: auto;
        border: 1px solid var(--line);
        border-radius: 12px;
    }

    /* === Table === */
    .audit-table {
        margin: 0;
        border-color: var(--line) !important;
        background: #fff;
    }

    .audit-table th,
    .audit-table td {
        border-color: var(--line) !important;
        font-size: 12px;
        padding: 8px 10px;
        white-space: nowrap;
        color: var(--ink);
        vertical-align: middle;
    }

    /* sticky header */
    .audit-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
    }

    /* Header style */
    .audit-table thead tr:first-child th {
        background: var(--head);
        font-weight: 700;
        letter-spacing: .2px;
        border-bottom: 1px solid var(--line) !important;
        text-transform: uppercase;
        font-size: 11px;
        color: #374151;
    }

    .audit-table thead tr:nth-child(2) th {
        background: var(--head2);
        font-weight: 600;
        font-size: 11px;
        color: #4b5563;
    }

    /* Group headers look like “label chips” */
    .grp {
        background: var(--head) !important;
    }

    .grp>.grp-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        background: var(--pill);
        color: var(--pillText);
        font-weight: 700;
        text-transform: none;
        letter-spacing: 0;
        font-size: 11px;
    }

    /* Zebra + hover */
    .audit-table tbody tr:nth-child(even) {
        background: #fcfcfd;
    }

    .audit-table tbody tr:hover {
        background: var(--rowHover);
    }

    /* Outlet cell */
    .outlet-cell .name {
        font-weight: 700;
        font-size: 13px;
        line-height: 1.1;
    }

    .outlet-cell .meta {
        color: var(--muted);
        font-size: 11px;
    }

    /* Numeric alignment */
    .num {
        text-align: center;
    }

    .avg {
        font-weight: 700;
    }

    .end-col {
        background: #fbfbfb;
    }

    /* GAP coloring */
    .gap-plus {
        color: var(--good);
        font-weight: 700;
    }

    .gap-minus {
        color: var(--bad);
        font-weight: 700;
    }

    /* Action column */
    .col-aksi {
        background: #fff;
        position: sticky;
        right: 0;
        z-index: 2;
        border-left: 1px solid var(--line) !important;
    }

    .audit-table thead .col-aksi {
        z-index: 4;
        background: var(--head);
    }

    /* Make header “Outlet” a bit emphasized */
    .col-outlet {
        min-width: 280px;
        background: var(--head) !important;
    }

    /* DataTables small tweak (optional) */
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 10px;
    }
</style>

@php
    $pct = fn($v) => $v === null || $v === '' ? '' : number_format((float) $v, 2, ',', '.') . '%';
    $avg = function (array $vals) {
        $nums = array_values(array_filter($vals, fn($v) => $v !== null && $v !== ''));
        if (count($nums) === 0) {
            return null;
        }
        return array_sum($nums) / count($nums);
    };
@endphp

{{-- @php
    $toFloat = function ($v) {
        if ($v === null || $v === '') {
            return null;
        }
        $v = trim((string) $v);
        $v = str_replace('%', '', $v);
        $v = str_replace('.', '', $v); // buang ribuan (kalau ada)
        $v = str_replace(',', '.', $v); // koma jadi titik
        return is_numeric($v) ? (float) $v : null;
    };

    $pct = function ($v) use ($toFloat) {
        $n = $toFloat($v);
        return $n === null ? '' : number_format($n, 2, ',', '.') . '%';
    };

    $avg = function (array $vals) use ($toFloat) {
        $nums = [];
        foreach ($vals as $v) {
            $n = $toFloat($v);
            if ($n !== null) {
                $nums[] = $n;
            }
        }
        if (!count($nums)) {
            return null;
        }
        return array_sum($nums) / count($nums);
    };
@endphp --}}

@php
    $reqPeriod = request('period_ym', ''); // HARUS YYYY-MM
    $reqOutlet = request('outlet_id', '');
    $hasFilter = request()->filled('period_ym') || request()->filled('outlet_id');
@endphp

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Validasi error:</div>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">Internal Audit</h5>
                            <small class="text-muted">Rekap hasil audit internal per outlet</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('investor.internal.audit.master') }}"
                                class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                            </a>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Audit
                            </button>
                            <button class="btn btn-primary-transparent btn-sm" data-bs-toggle="modal"
                                data-bs-target="#importModal">
                                <i class="bi bi-up-circle me-1"></i> Import
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">

                    {{-- FILTER --}}
                    <form method="GET" action="{{ route('investor.internal.audit.master') }}" class="filter-box mb-3">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label mb-1">Periode (Bulan)</label>
                                <input type="month" name="period_ym" class="form-control form-control-sm"
                                    value="{{ $reqPeriod }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label mb-1">Outlet</label>
                                <select name="outlet_id" class="form-select-sm select2 w-100">
                                    <option value="">Semua Outlet</option>
                                    @foreach ($outlets as $o)
                                        <option value="{{ $o->id }}" @selected((string) $reqOutlet === (string) $o->id)>
                                            {{ $o->nama_outlet }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex align-items-end gap-2">
                                <button class="btn btn-primary btn-sm w-100" type="submit">
                                    <i class="bi bi-search me-1"></i> Cari
                                </button>
                                <a class="btn btn-outline-secondary btn-sm"
                                    href="{{ route('investor.internal.audit.master') }}">Reset</a>
                            </div>
                        </div>
                    </form>

                    {{-- TABLE --}}
                    <div class="table-responsive audit-wrap">
                        <table id="auditTable" class="table table-sm audit-table align-middle">
                            <thead>
                                <tr class="text-center">
                                    <th class="col-outlet" rowspan="2">Outlet</th>

                                    <th class="grp" colspan="3"><span class="grp-pill">Compliance</span></th>
                                    <th class="grp" rowspan="2">Avg</th>

                                    <th class="grp" colspan="3"><span class="grp-pill">Audit 5R</span></th>
                                    <th class="grp" rowspan="2">Avg</th>

                                    <th class="grp" colspan="3"><span class="grp-pill">Stock Opname</span></th>
                                    <th class="grp" rowspan="2">Avg</th>

                                    <th class="grp" colspan="3"><span class="grp-pill">Cash Opname</span></th>
                                    <th class="grp" rowspan="2">Avg</th>

                                    <th class="grp" colspan="3"><span class="grp-pill">Audit QCP</span></th>
                                    <th class="grp" rowspan="2">Avg</th>

                                    <th class="grp end-col" rowspan="2">Pencapaian</th>
                                    <th class="grp end-col" rowspan="2">Target</th>
                                    <th class="grp end-col" rowspan="2">GAP</th>
                                    <th class="grp end-col" rowspan="2" style="min-width:160px;">Keterangan</th>

                                    <th class="col-aksi" rowspan="2" style="width:120px;">Aksi</th>
                                </tr>

                                <tr class="text-center">
                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>

                                    <th>Ringkas</th>
                                    <th>Rapi</th>
                                    <th>Resik</th>

                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>

                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>

                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($audits as $r)
                                    @php
                                        $avgCompliance = $avg([$r->compliance_1, $r->compliance_2, $r->compliance_3]);
                                        $avg5r = $avg([$r->r5_ringkas, $r->r5_rapi, $r->r5_resik]);
                                        $avgStock = $avg([$r->stock_1, $r->stock_2, $r->stock_3]);
                                        $avgCash = $avg([$r->cash_1, $r->cash_2, $r->cash_3]);
                                        $avgQcp = $avg([$r->qcp_1, $r->qcp_2, $r->qcp_3]);

                                        $groups = array_values(
                                            array_filter(
                                                [$avgCompliance, $avg5r, $avgStock, $avgCash, $avgQcp],
                                                fn($v) => $v !== null,
                                            ),
                                        );
                                        $pencapaian = count($groups)
                                            ? round(array_sum($groups) / count($groups), 2)
                                            : null;

                                        $target = $r->target ?? 80;
                                        $gap = $pencapaian === null ? null : round($pencapaian - $target, 2);
                                        $gapClass = $gap === null ? '' : ($gap >= 0 ? 'gap-plus' : 'gap-minus');
                                    @endphp

                                    <tr>
                                        <td class="text-start outlet-cell">
                                            <div class="name">{{ $r->nama_outlet }}</div>
                                        </td>

                                        <td class="num">{{ $pct($r->compliance_1) }}</td>
                                        <td class="num">{{ $pct($r->compliance_2) }}</td>
                                        <td class="num">{{ $pct($r->compliance_3) }}</td>
                                        <td class="num avg">{{ $pct($avgCompliance) }}</td>

                                        <td class="num">{{ $pct($r->r5_ringkas) }}</td>
                                        <td class="num">{{ $pct($r->r5_rapi) }}</td>
                                        <td class="num">{{ $pct($r->r5_resik) }}</td>
                                        <td class="num avg">{{ $pct($avg5r) }}</td>

                                        <td class="num">{{ $pct($r->stock_1) }}</td>
                                        <td class="num">{{ $pct($r->stock_2) }}</td>
                                        <td class="num">{{ $pct($r->stock_3) }}</td>
                                        <td class="num avg">{{ $pct($avgStock) }}</td>

                                        <td class="num">{{ $pct($r->cash_1) }}</td>
                                        <td class="num">{{ $pct($r->cash_2) }}</td>
                                        <td class="num">{{ $pct($r->cash_3) }}</td>
                                        <td class="num avg">{{ $pct($avgCash) }}</td>

                                        <td class="num">{{ $pct($r->qcp_1) }}</td>
                                        <td class="num">{{ $pct($r->qcp_2) }}</td>
                                        <td class="num">{{ $pct($r->qcp_3) }}</td>
                                        <td class="num avg">{{ $pct($avgQcp) }}</td>

                                        <td class="num avg end-col">{{ $pct($pencapaian) }}</td>
                                        <td class="num end-col">{{ $pct($target) }}</td>
                                        <td class="num end-col {{ $gapClass }}">
                                            {{ $gap === null ? '' : number_format($gap, 2, ',', '.') . '%' }}
                                        </td>
                                        <td class="text-start end-col">{{ $r->keterangan ?? '' }}</td>

                                        <td class="text-center col-aksi">
                                            <button class="btn btn-sm btn-outline-primary btn-edit"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="{{ $r->id }}" data-outlet_id="{{ $r->outlet_id }}"
                                                data-auditor="{{ $r->auditor }}"
                                                data-audit_date="{{ $r->audit_date }}"
                                                data-period_ym="{{ $r->period_ym }}"
                                                data-target="{{ $r->target }}"
                                                data-keterangan="{{ $r->keterangan }}"
                                                data-compliance_1="{{ $r->compliance_1 }}"
                                                data-compliance_2="{{ $r->compliance_2 }}"
                                                data-compliance_3="{{ $r->compliance_3 }}"
                                                data-r5_ringkas="{{ $r->r5_ringkas }}"
                                                data-r5_rapi="{{ $r->r5_rapi }}"
                                                data-r5_resik="{{ $r->r5_resik }}"
                                                data-stock_1="{{ $r->stock_1 }}"
                                                data-stock_2="{{ $r->stock_2 }}"
                                                data-stock_3="{{ $r->stock_3 }}" data-cash_1="{{ $r->cash_1 }}"
                                                data-cash_2="{{ $r->cash_2 }}" data-cash_3="{{ $r->cash_3 }}"
                                                data-qcp_1="{{ $r->qcp_1 }}" data-qcp_2="{{ $r->qcp_2 }}"
                                                data-qcp_3="{{ $r->qcp_3 }}">
                                                Ubah
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- <tr>
                                        <td colspan="26" class="text-center text-muted py-4">Belum ada data audit.
                                        </td>
                                    </tr> --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- MODAL IMPORT --}}
            <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST"
                        action="{{ route('investor.internal.audit.import') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Import Internal Audit (CSV / XLSX)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info mb-3">
                                <div class="fw-semibold mb-1">Format file:</div>
                                <ul class="mb-0">
                                    <li>Mendukung: <b>.csv</b>, <b>.txt</b>, <b>.xlsx</b></li>
                                    <li>Untuk CSV: pemisah <b>koma</b> (,)</li>
                                    <li>Angka persen: isi <b>0-100</b> (contoh: 74.07) / bisa <b>74,07</b> / boleh
                                        <b>74,07%</b>
                                    </li>
                                    <li>Outlet bisa pakai: <b>outlet_id</b> / <b>kode_outlet</b> / <b>nama_outlet</b>
                                    </li>
                                    <li>Kolom wajib: <b>audit_date</b> + salah satu (<b>outlet_id</b> /
                                        <b>kode_outlet</b> / <b>nama_outlet</b>)
                                    </li>
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload File</label>
                                <input type="file" name="file" class="form-control"
                                    accept=".csv,.txt,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                                    required>
                                <small class="text-muted">Contoh: internal_audit_des_2025.csv atau
                                    internal_audit_des_2025.xlsx</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contoh Header (CSV / Excel kolom baris 1)</label>
                                <textarea class="form-control" rows="4" readonly>kode_outlet,nama_outlet,audit_date,period_ym,auditor,target,compliance_1,compliance_2,compliance_3,r5_ringkas,r5_rapi,r5_resik,stock_1,stock_2,stock_3,cash_1,cash_2,cash_3,qcp_1,qcp_2,qcp_3,keterangan</textarea>
                                <small class="text-muted">Header harus sesuai persis. period_ym opsional (auto dari
                                    audit_date).</small>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="skip_if_exists" value="1"
                                    id="skipIfExists" checked>
                                <label class="form-check-label" for="skipIfExists">
                                    Lewati jika data sudah ada (berdasarkan outlet_id + audit_date)
                                </label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-upload me-1"></i> Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            @if (session('failed_export_url'))
                <div class="alert alert-warning d-flex justify-content-between align-items-center">
                    <div>
                        Ada data yang gagal import. Silakan download Excel data gagal untuk diperbaiki lalu import
                        ulang.
                    </div>
                    <a class="btn btn-sm btn-outline-dark" href="{{ session('failed_export_url') }}"
                        target="_blank">
                        <i class="bi bi-download me-1"></i> Download Data Gagal
                    </a>
                </div>
            @endif

            @if (session('import_errors'))
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Detail gagal (maks tampil):</div>
                    <ul class="mb-0">
                        @foreach (array_slice(session('import_errors'), 0, 15) as $er)
                            <li>{{ $er }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('import_warnings'))
                <div class="alert alert-warning">
                    <div class="fw-semibold mb-1">Detail warning (cek match outlet):</div>
                    <ul class="mb-0">
                        @foreach (array_slice(session('import_warnings'), 0, 15) as $er)
                            <li>{{ $er }}</li>
                        @endforeach
                    </ul>
                    @if (count(session('import_warnings')) > 15)
                        <small class="text-muted">...dan lainnya</small>
                    @endif
                </div>
            @endif


            {{-- MODAL TAMBAH & EDIT: biarkan seperti punyamu (nggak perlu diubah) --}}
            {{-- ... modal add ... --}}
            {{-- ===================== MODAL TAMBAH ===================== --}}
            <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST"
                        action="{{ route('investor.internal.audit.store') }}">
                        @csrf
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Tambah Internal Audit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Outlet</label>
                                    <select name="outlet_id" class="form-select select2" required>
                                        <option value="">-- Pilih Outlet --</option>
                                        @foreach ($outlets as $o)
                                            <option value="{{ $o->id }}">
                                                {{ $o->nama_outlet }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Auditor</label>
                                    <input name="auditor" type="text" class="form-control"
                                        placeholder="Nama auditor">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Audit</label>
                                    <input name="audit_date" type="date" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Periode (opsional)</label>
                                    <input name="period_ym" type="month" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Target</label>
                                    <input name="target" type="number" step="0.01" class="form-control"
                                        value="80.00" required>
                                </div>

                                <div class="col-12">
                                    <hr class="my-2">
                                </div>

                                @php
                                    $fieldsAdd = [
                                        'compliance_1',
                                        'compliance_2',
                                        'compliance_3',
                                        'r5_ringkas',
                                        'r5_rapi',
                                        'r5_resik',
                                        'stock_1',
                                        'stock_2',
                                        'stock_3',
                                        'cash_1',
                                        'cash_2',
                                        'cash_3',
                                        'qcp_1',
                                        'qcp_2',
                                        'qcp_3',
                                    ];
                                @endphp

                                @foreach ($fieldsAdd as $f)
                                    <div class="col-md-4">
                                        <label class="form-label">{{ strtoupper(str_replace('_', ' ', $f)) }}</label>
                                        <input name="{{ $f }}" type="number" step="0.01"
                                            class="form-control">
                                    </div>
                                @endforeach

                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <input name="keterangan" type="text" class="form-control"
                                        placeholder="Opsional">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            {{-- ... modal edit ... --}}
            {{-- ===================== MODAL EDIT ===================== --}}
            <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form id="editForm" class="modal-content" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Edit Internal Audit</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Outlet</label>
                                    <select id="edit_outlet_id" name="outlet_id" class="form-select select2"
                                        required>
                                        <option value="">-- Pilih Outlet --</option>
                                        @foreach ($outlets as $o)
                                            <option value="{{ $o->id }}">
                                                {{ $o->nama_outlet }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Auditor</label>
                                    <input id="edit_auditor" name="auditor" type="text" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Tanggal Audit</label>
                                    <input id="edit_audit_date" name="audit_date" type="date"
                                        class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Periode (opsional)</label>
                                    <input id="edit_period_ym" name="period_ym" type="month" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Target</label>
                                    <input id="edit_target" name="target" type="number" step="0.01"
                                        class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <hr class="my-2">
                                </div>

                                @php
                                    $fieldsEdit = [
                                        'compliance_1',
                                        'compliance_2',
                                        'compliance_3',
                                        'r5_ringkas',
                                        'r5_rapi',
                                        'r5_resik',
                                        'stock_1',
                                        'stock_2',
                                        'stock_3',
                                        'cash_1',
                                        'cash_2',
                                        'cash_3',
                                        'qcp_1',
                                        'qcp_2',
                                        'qcp_3',
                                    ];
                                @endphp

                                @foreach ($fieldsEdit as $f)
                                    <div class="col-md-4">
                                        <label class="form-label">{{ strtoupper(str_replace('_', ' ', $f)) }}</label>
                                        <input id="edit_{{ $f }}" name="{{ $f }}"
                                            type="number" step="0.01" class="form-control">
                                    </div>
                                @endforeach

                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <input id="edit_keterangan" name="keterangan" type="text"
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</main>


<script>
    const HAS_DATA = {{ $audits->count() > 0 ? 'true' : 'false' }};
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Init Select2 (filter + modal)
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        $('#addModal .select2').select2({
            dropdownParent: $('#addModal'),
            theme: 'bootstrap-5',
            width: '100%'
        });
        $('#editModal .select2').select2({
            dropdownParent: $('#editModal'),
            theme: 'bootstrap-5',
            width: '100%'
        });

        // DataTable (init sekali)
        $('#auditTable').DataTable({
            pageLength: 10,
            scrollX: true,
            autoWidth: false,
            language: {
                emptyTable: "Silakan pilih filter (periode/outlet) lalu klik Cari.",
                zeroRecords: "Data tidak ditemukan."
            }
        });

        if (!HAS_DATA) {
            // optional: tampilkan alert/popup
            const alertBox = document.createElement('div');
            alertBox.className = "alert alert-info";
            alertBox.innerHTML = "Silakan filter dulu untuk melihat data.";
            document.querySelector('.card-body').prepend(alertBox);
        }

        // fill edit modal (tetap)
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;

                const form = document.getElementById('editForm');
                form.action = `{{ url('/internal/audit/update') }}/${id}`;

                $('#edit_outlet_id').val(btn.dataset.outlet_id).trigger('change');
                document.getElementById('edit_auditor').value = btn.dataset.auditor ?? '';
                document.getElementById('edit_audit_date').value = btn.dataset.audit_date ?? '';
                document.getElementById('edit_period_ym').value = btn.dataset.period_ym ?? '';
                document.getElementById('edit_target').value = btn.dataset.target ?? '80.00';
                document.getElementById('edit_keterangan').value = btn.dataset.keterangan ?? '';

                const fields = [
                    'compliance_1', 'compliance_2', 'compliance_3',
                    'r5_ringkas', 'r5_rapi', 'r5_resik',
                    'stock_1', 'stock_2', 'stock_3',
                    'cash_1', 'cash_2', 'cash_3',
                    'qcp_1', 'qcp_2', 'qcp_3'
                ];

                fields.forEach(f => {
                    const el = document.getElementById(`edit_${f}`);
                    if (el) el.value = (btn.dataset[f] ?? '');
                });
            });
        });
    });
</script>

@include('Temp.Investor.footer')
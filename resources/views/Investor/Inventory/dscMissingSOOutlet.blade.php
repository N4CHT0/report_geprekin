@section('title', 'Outlet Belum Mengisi DSC')
@section('breadcrumb', 'Inventory / Outlet Belum Mengisi DSC')

@include('Temp.Investor.header')

@php
    $today = $today ?? date('Y-m-d');
    $startDate = $startDate ?? request('start_date', \Carbon\Carbon::parse($today)->subDay()->format('Y-m-d'));
    $endDate = $endDate ?? request('end_date', $startDate);
    $keyword = $keyword ?? request('q', '');
    $rows = $rows ?? [];
    $totalMissing = $totalMissing ?? (is_countable($rows) ? count($rows) : 0);

    $formatDate = function ($value) {
        if (empty($value) || $value === '-') {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    };

    $cleanOutletName = function ($value) {
        $value = trim((string) $value);
        $value = preg_replace('/\s*\[ID:\s*[^\]]*\]\s*/i', '', $value);
        $value = preg_replace('/\s*\(ID:\s*[^\)]*\)\s*/i', '', $value);
        $value = preg_replace('/^Outlet ID:\s*/i', '', $value);
        return trim(preg_replace('/\s+/', ' ', $value)) ?: '-';
    };

    $periodText = $formatDate($startDate);
    if ($startDate !== $endDate) {
        $periodText .= ' s/d ' . $formatDate($endDate);
    }
@endphp

<style>
    :root{
        --aws-bg:#f7f8fa;
        --aws-card:#ffffff;
        --aws-text:#16191f;
        --aws-muted:#5f6b7a;
        --aws-line:#d5dbdb;
        --aws-line-soft:#e9ebed;
        --aws-blue:#0972d3;
        --aws-blue-dark:#033160;
        --aws-green:#037f0c;
        --aws-red:#d13212;
        --aws-orange:#b7791f;
        --aws-radius:8px;
        --aws-shadow:0 1px 2px rgba(15,23,42,.06);
        --aws-focus:0 0 0 3px rgba(9,114,211,.18);
    }

    .missing-shell{
        display:flex;
        flex-direction:column;
        gap:16px;
    }

    .missing-header{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
        padding-bottom:14px;
        border-bottom:1px solid var(--aws-line);
    }

    .missing-title{
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }

    .missing-title-mark{
        width:36px;
        height:36px;
        display:grid;
        place-items:center;
        border-radius:8px;
        background:#f1f8ff;
        color:var(--aws-blue);
        border:1px solid #b6d7f5;
    }

    .missing-title h4{
        margin:0;
        font-size:1.5rem;
        font-weight:800;
        letter-spacing:-.02em;
        color:var(--aws-text);
    }

    .missing-badge{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:5px 9px;
        border-radius:999px;
        background:#fff1f0;
        color:var(--aws-red);
        border:1px solid #f3b8ad;
        font-size:.72rem;
        font-weight:900;
        white-space:nowrap;
    }

    .missing-toolbar{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        align-items:center;
        justify-content:flex-end;
    }

    .btn{
        border-radius:8px!important;
        font-weight:700!important;
        box-shadow:none!important;
        transform:none!important;
    }

    .btn-sm{
        padding:.42rem .72rem;
        font-size:.84rem;
    }

    .btn-primary{
        background:var(--aws-blue)!important;
        border-color:var(--aws-blue)!important;
    }

    .btn-primary:hover,
    .btn-primary:focus{
        background:var(--aws-blue-dark)!important;
        border-color:var(--aws-blue-dark)!important;
    }

    .btn-success{
        background:var(--aws-green)!important;
        border-color:var(--aws-green)!important;
    }

    .btn-ghost{
        background:#fff!important;
        border:1px solid var(--aws-line)!important;
        color:#414d5c!important;
    }

    .btn-ghost:hover{
        background:#f2f3f3!important;
        color:var(--aws-text)!important;
    }

    .missing-card,
    .missing-filter,
    .missing-copy-box,
    .missing-table-wrap,
    .missing-kpi{
        background:#fff;
        border:1px solid var(--aws-line);
        border-radius:var(--aws-radius);
        box-shadow:var(--aws-shadow);
    }

    .missing-filter{
        padding:14px;
    }

    .filter-grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,180px)) minmax(220px,1fr) auto;
        gap:12px;
        align-items:end;
    }

    .form-label{
        display:block;
        color:var(--aws-text);
        font-size:.78rem;
        font-weight:800;
        margin-bottom:5px;
        text-transform:uppercase;
        letter-spacing:.02em;
    }

    .form-control,
    .form-select{
        min-height:38px;
        border-radius:8px!important;
        border:1px solid var(--aws-line)!important;
        box-shadow:none!important;
        color:var(--aws-text);
        font-size:.9rem;
        font-weight:600;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:var(--aws-blue)!important;
        box-shadow:var(--aws-focus)!important;
    }

    .missing-kpi-grid{
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:12px;
    }

    .missing-kpi{
        padding:14px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:10px;
    }

    .missing-kpi .label{
        font-size:.78rem;
        color:var(--aws-muted);
        font-weight:800;
        text-transform:uppercase;
    }

    .missing-kpi .value{
        font-size:1.2rem;
        font-weight:900;
        color:var(--aws-text);
    }

    .missing-kpi .icon{
        width:42px;
        height:42px;
        display:grid;
        place-items:center;
        border-radius:8px;
        background:#f8f9fa;
        border:1px solid var(--aws-line);
    }

    .missing-kpi.danger .icon{
        background:#fff1f0;
        color:var(--aws-red);
        border-color:#f3b8ad;
    }

    .missing-kpi.primary .icon{
        background:#f1f8ff;
        color:var(--aws-blue);
        border-color:#b6d7f5;
    }

    .missing-copy-box{
        padding:14px;
    }

    .copy-actions{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        align-items:center;
        justify-content:space-between;
        margin-bottom:10px;
    }

    .copy-actions-left,
    .copy-actions-right{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        align-items:center;
    }

    .copy-preview{
        min-height:150px;
        resize:vertical;
        font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size:.86rem;
        line-height:1.55;
        background:#fbfbfb;
    }

    .copy-help{
        color:var(--aws-muted);
        font-size:.82rem;
        font-weight:700;
        margin-top:8px;
    }

    .missing-table-wrap{
        overflow:auto;
        -webkit-overflow-scrolling:touch;
        max-height:68vh;
    }

    .missing-table{
        width:100%;
        margin:0!important;
        color:var(--aws-text);
        vertical-align:middle;
        font-size:.86rem;
        border-collapse:separate;
        border-spacing:0;
    }

    .missing-table th,
    .missing-table td{
        border-bottom:1px solid var(--aws-line-soft)!important;
        padding:10px 11px;
        font-size:.84rem;
        white-space:nowrap;
        vertical-align:middle;
        background:#fff;
        color:var(--aws-text);
    }

    .missing-table th{
        position:sticky;
        top:0;
        z-index:5;
        background:#f8f9fa!important;
        color:#414d5c;
        font-size:.72rem;
        font-weight:900;
        text-align:center;
        text-transform:uppercase;
        letter-spacing:.04em;
        border-bottom:1px solid var(--aws-line)!important;
    }

    .missing-table td.center{
        text-align:center;
        font-weight:700;
    }

    .missing-table td.outlet{
        font-weight:900;
    }

    .missing-table tbody tr:hover td{
        background:#f2f8fd;
    }

    .missing-table tbody tr.row-selected td{
        background:#f1f8ff!important;
    }

    .missing-check{
        width:18px;
        height:18px;
        cursor:pointer;
    }

    .status-badge{
        display:inline-flex;
        align-items:center;
        gap:5px;
        padding:5px 9px;
        border-radius:999px;
        background:#fff1f0;
        color:var(--aws-red);
        border:1px solid #f3b8ad;
        font-size:.72rem;
        font-weight:900;
        text-transform:uppercase;
    }

    .empty-state{
        padding:32px 14px;
        text-align:center;
        color:var(--aws-muted);
        font-weight:700;
    }

    .toast-copy{
        position:fixed;
        right:18px;
        bottom:18px;
        z-index:9999;
        display:none;
        background:#16191f;
        color:#fff;
        padding:10px 14px;
        border-radius:8px;
        box-shadow:0 14px 40px rgba(15,23,42,.2);
        font-weight:800;
        font-size:.86rem;
    }

    @media (max-width:992px){
        .filter-grid,
        .missing-kpi-grid{
            grid-template-columns:1fr;
        }
        .missing-toolbar,
        .copy-actions{
            justify-content:flex-start;
        }
    }

    @media (max-width:576px){
        .missing-title h4{ font-size:1.15rem; }
        .missing-toolbar .btn,
        .copy-actions .btn{
            flex:1 1 100%;
        }
        .missing-table{
            min-width:900px;
        }
    }
</style>

<div class="missing-shell">
    <div class="missing-header">
        <div>
            <div class="missing-title">
                <span class="missing-title-mark"><i class="bi bi-bell"></i></span>
                <h4>OUTLET BELUM MENGISI DSC</h4>
                <span class="missing-badge">
                    <i class="bi bi-exclamation-triangle"></i>
                    {{ number_format($totalMissing, 0, ',', '.') }} OUTLET
                </span>
            </div>
            <div class="text-muted fw-semibold mt-1">
                Periode: {{ $periodText }}
            </div>
        </div>

        <div class="missing-toolbar">
            <a class="btn btn-sm btn-ghost" href="{{ route('master.dsc.index') }}">
                <i class="bi bi-arrow-left me-1"></i> KEMBALI
            </a>

            <a class="btn btn-sm btn-success"
               href="{{ route('master.dsc.missing.export', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'q' => $keyword,
               ]) }}">
                <i class="bi bi-file-earmark-excel me-1"></i> EXPORT
            </a>

            <button type="button" class="btn btn-sm btn-primary" id="btnCopyAllTop">
                <i class="bi bi-clipboard-check me-1"></i> COPY ALL
            </button>
        </div>
    </div>

    <div class="missing-filter">
        <form method="GET" action="{{ route('master.dsc.missing') }}" id="missingFilterForm">
            <div class="filter-grid">
                <div>
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                </div>

                <div>
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                </div>

                <div>
                    <label class="form-label">Cari Outlet</label>
                    <input type="text" name="q" class="form-control" value="{{ $keyword }}" placeholder="Nama outlet...">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> TERAPKAN
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="missing-kpi-grid">
        <div class="missing-kpi danger">
            <div>
                <div class="label">Total Belum Isi</div>
                <div class="value">{{ number_format($totalMissing, 0, ',', '.') }}</div>
            </div>
            <div class="icon"><i class="bi bi-exclamation-circle"></i></div>
        </div>

        <div class="missing-kpi primary">
            <div>
                <div class="label">Periode Cek</div>
                <div class="value" style="font-size:1rem;">{{ $periodText }}</div>
            </div>
            <div class="icon"><i class="bi bi-calendar-range"></i></div>
        </div>

        <div class="missing-kpi primary">
            <div>
                <div class="label">Dipilih untuk Copy</div>
                <div class="value" id="selectedCount">0</div>
            </div>
            <div class="icon"><i class="bi bi-check2-square"></i></div>
        </div>
    </div>

    <div class="missing-copy-box">
        <div class="copy-actions">
            <div class="copy-actions-left">
                <button type="button" class="btn btn-sm btn-primary" id="btnSelectAll">
                    <i class="bi bi-check2-square me-1"></i> SELECT ALL
                </button>
                <button type="button" class="btn btn-sm btn-ghost" id="btnClearSelected">
                    <i class="bi bi-square me-1"></i> CLEAR
                </button>
                <button type="button" class="btn btn-sm btn-ghost" id="btnRefreshText">
                    <i class="bi bi-arrow-repeat me-1"></i> REFRESH TEXT
                </button>
            </div>

            <div class="copy-actions-right">
                <button type="button" class="btn btn-sm btn-success" id="btnCopySelected">
                    <i class="bi bi-clipboard me-1"></i> COPY SELECTED
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="btnCopyAll">
                    <i class="bi bi-clipboard-check me-1"></i> COPY ALL
                </button>
            </div>
        </div>

        <textarea id="copyPreview" class="form-control copy-preview" readonly></textarea>
        <div class="copy-help">
            Pilih outlet memakai checkbox atau tombol <b>Select All</b>, lalu klik <b>Copy Selected</b>. Format sudah dibuat siap paste ke grup Ops.
        </div>
    </div>

    <div class="missing-table-wrap">
        <table class="missing-table" id="missingTable">
            <thead>
                <tr>
                    <th style="width:58px;">
                        <input type="checkbox" class="missing-check" id="checkAllRows" title="Select all">
                    </th>
                    <th style="width:70px;">NO</th>
                    <th style="width:130px;">TANGGAL</th>
                    <th>NAMA OUTLET</th>
                    <th style="width:170px;">LAST INPUT</th>
                    <th style="width:180px;">STATUS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    @php
                        $namaOutlet = $cleanOutletName($row['nama_outlet'] ?? '-');
                        $rowTanggal = $formatDate($row['tanggal'] ?? $startDate);
                        $lastInput = $formatDate($row['last_input_date'] ?? '-');
                    @endphp
                    <tr data-row="missing"
                        data-outlet="{{ e($namaOutlet) }}"
                        data-tanggal="{{ e($rowTanggal) }}"
                        data-last-input="{{ e($lastInput) }}">
                        <td class="center">
                            <input type="checkbox" class="missing-check missing-row-check">
                        </td>
                        <td class="center">{{ $row['no'] ?? $loop->iteration }}</td>
                        <td class="center">{{ $rowTanggal }}</td>
                        <td class="outlet">{{ $namaOutlet }}</td>
                        <td class="center">{{ $lastInput }}</td>
                        <td class="center">
                            <span class="status-badge">
                                <i class="bi bi-x-circle"></i>
                                {{ $row['status'] ?? 'BELUM MENGISI DSC' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                                Tidak ada outlet yang belum mengisi DSC pada periode ini.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="toast-copy" id="copyToast">Text berhasil dicopy.</div>

@push('scripts')
<script>
(function () {
    const periodText = @json($periodText);
    const totalMissing = Number(@json($totalMissing));
    const table = document.getElementById('missingTable');
    const checkAllRows = document.getElementById('checkAllRows');
    const copyPreview = document.getElementById('copyPreview');
    const selectedCount = document.getElementById('selectedCount');
    const copyToast = document.getElementById('copyToast');

    function getRows() {
        return Array.from(document.querySelectorAll('#missingTable tbody tr[data-row="missing"]'));
    }

    function getSelectedRows() {
        return getRows().filter(row => {
            const check = row.querySelector('.missing-row-check');
            return check && check.checked;
        });
    }

    function setRowsSelected(rows, checked) {
        rows.forEach(row => {
            const check = row.querySelector('.missing-row-check');
            if (check) {
                check.checked = checked;
            }
            row.classList.toggle('row-selected', checked);
        });
        syncHeaderCheck();
        renderCopyText();
    }

    function syncHeaderCheck() {
        const rows = getRows();
        const selectedRows = getSelectedRows();

        if (selectedCount) {
            selectedCount.textContent = selectedRows.length.toLocaleString('id-ID');
        }

        if (!checkAllRows) {
            return;
        }

        checkAllRows.checked = rows.length > 0 && selectedRows.length === rows.length;
        checkAllRows.indeterminate = selectedRows.length > 0 && selectedRows.length < rows.length;
    }

    function buildText(rows) {
        if (!rows.length) {
            return 'Belum ada outlet yang dipilih.';
        }

        const lines = [];
        lines.push('Halo tim, mohon dibantu follow up outlet berikut karena belum mengisi DSC periode ' + periodText + ':');
        lines.push('');

        rows.forEach((row, idx) => {
            const outlet = row.getAttribute('data-outlet') || '-';
            const lastInput = row.getAttribute('data-last-input') || '-';
            const lastText = lastInput && lastInput !== '-' ? ' | terakhir isi: ' + lastInput : '';
            lines.push((idx + 1) + '. ' + outlet + lastText);
        });

        lines.push('');
        lines.push('Mohon segera dilengkapi agar data report DSC bisa terbaca normal. Terima kasih.');

        return lines.join('\n');
    }

    function renderCopyText(useAll = false) {
        const rows = useAll ? getRows() : getSelectedRows();
        if (copyPreview) {
            copyPreview.value = buildText(rows);
        }
        syncHeaderCheck();
    }

    async function copyText(text) {
        if (!text || text === 'Belum ada outlet yang dipilih.') {
            alert('Pilih outlet dulu atau gunakan Copy All.');
            return;
        }

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                copyPreview.removeAttribute('readonly');
                copyPreview.select();
                document.execCommand('copy');
                copyPreview.setAttribute('readonly', 'readonly');
                window.getSelection().removeAllRanges();
            }

            showToast('Text berhasil dicopy.');
        } catch (e) {
            copyPreview.removeAttribute('readonly');
            copyPreview.select();
            document.execCommand('copy');
            copyPreview.setAttribute('readonly', 'readonly');
            window.getSelection().removeAllRanges();
            showToast('Text berhasil dicopy.');
        }
    }

    function showToast(message) {
        if (!copyToast) {
            alert(message);
            return;
        }

        copyToast.textContent = message;
        copyToast.style.display = 'block';

        clearTimeout(window.__missingCopyToastTimer);
        window.__missingCopyToastTimer = setTimeout(() => {
            copyToast.style.display = 'none';
        }, 1800);
    }

    document.getElementById('btnSelectAll')?.addEventListener('click', function () {
        setRowsSelected(getRows(), true);
    });

    document.getElementById('btnClearSelected')?.addEventListener('click', function () {
        setRowsSelected(getRows(), false);
    });

    document.getElementById('btnRefreshText')?.addEventListener('click', function () {
        renderCopyText(false);
    });

    document.getElementById('btnCopySelected')?.addEventListener('click', function () {
        renderCopyText(false);
        copyText(copyPreview.value);
    });

    document.getElementById('btnCopyAll')?.addEventListener('click', function () {
        setRowsSelected(getRows(), true);
        renderCopyText(true);
        copyText(copyPreview.value);
    });

    document.getElementById('btnCopyAllTop')?.addEventListener('click', function () {
        setRowsSelected(getRows(), true);
        renderCopyText(true);
        copyText(copyPreview.value);
    });

    checkAllRows?.addEventListener('change', function () {
        setRowsSelected(getRows(), this.checked);
    });

    table?.addEventListener('change', function (event) {
        if (event.target && event.target.classList.contains('missing-row-check')) {
            const row = event.target.closest('tr');
            if (row) {
                row.classList.toggle('row-selected', event.target.checked);
            }
            renderCopyText(false);
        }
    });

    table?.addEventListener('click', function (event) {
        const row = event.target.closest('tr[data-row="missing"]');
        if (!row || event.target.classList.contains('missing-row-check')) {
            return;
        }

        const check = row.querySelector('.missing-row-check');
        if (check) {
            check.checked = !check.checked;
            row.classList.toggle('row-selected', check.checked);
            renderCopyText(false);
        }
    });

    if (totalMissing > 0) {
        setRowsSelected(getRows(), true);
    } else {
        renderCopyText(false);
    }
})();
</script>
@endpush

@include('Temp.Investor.footer')

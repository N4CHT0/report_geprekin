@section('title', 'Profit & Loss Internal Live')
@section('breadcrumb', 'Laporan / Profit & Loss Internal')

@include('Temp.Investor.header')

@php
    try {
        $startDateInput = !empty($startDate) ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : now()->format('Y-m-d');
    } catch (\Throwable $e) {
        $startDateInput = now()->format('Y-m-d');
    }

    try {
        $endDateInput = !empty($endDate) ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : now()->format('Y-m-d');
    } catch (\Throwable $e) {
        $endDateInput = now()->format('Y-m-d');
    }

    $formatNominal = function ($value) {
        $abs = abs((float) $value);
        $formatted = number_format($abs, 0, ',', '.');
        return (float) $value < 0 ? '(' . $formatted . ')' : $formatted;
    };

    $grandTotal = function ($row) {
        return array_sum($row['values'] ?? []);
    };

    $liveMeta = $liveMeta ?? [
        'generated_at' => null,
        'served_from_cache' => false,
        'is_stale' => false,
        'cache_key' => null,
    ];

    $syncState = $syncStatus ?? null;
    $currentSyncKey = $syncKey ?? request('sync_key');
    $currentSyncStatus = is_array($syncState) ? ($syncState['status'] ?? null) : null;
    $isSyncRunning = in_array($currentSyncStatus, ['queued', 'processing', 'finalizing'], true);
@endphp

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
        --aws-orange:#b7791f;
        --aws-radius:8px;
        --aws-shadow:0 1px 2px rgba(15,23,42,.06);
        --aws-focus:0 0 0 3px rgba(9,114,211,.18);
    }

    .pnl-page{
        display:flex;
        flex-direction:column;
        gap:14px;
        padding:0;
        min-width:0;
        max-width:100%;
        overflow:hidden;
    }

    .pnl-hero{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:14px;
        flex-wrap:wrap;
        padding-bottom:12px;
        border-bottom:1px solid var(--aws-line);
    }

    .pnl-kicker{
        color:var(--aws-muted);
        font-size:.82rem;
        font-weight:700;
        margin-bottom:4px;
    }

    .pnl-main-title{
        margin:0;
        color:var(--aws-text);
        font-size:1.35rem;
        font-weight:800;
        letter-spacing:-.02em;
    }

    .pnl-hero-pills{
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
    }

    .pnl-pill{
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

    .pnl-card{
        background:#fff;
        border:1px solid var(--aws-line);
        border-radius:var(--aws-radius);
        overflow:hidden;
        box-shadow:var(--aws-shadow);
        max-width:100%;
    }

    .pnl-card-header{
        padding:12px 14px;
        border-bottom:1px solid var(--aws-line);
        background:#fbfbfb;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
    }

    .pnl-title{
        margin:0;
        font-size:.98rem;
        font-weight:800;
        color:var(--aws-text);
        letter-spacing:-.01em;
    }

    .pnl-filter-wrap{
        padding:14px;
        border-bottom:1px solid var(--aws-line);
        background:#f8f9fa;
    }

    .filter-card{
        background:#fff;
        border:1px solid var(--aws-line-soft);
        border-radius:var(--aws-radius);
        padding:13px;
    }

    .filter-card .row{
        align-items:end;
    }

    .filter-label{
        display:block;
        font-size:.8rem;
        font-weight:800;
        color:var(--aws-text);
        margin-bottom:6px;
    }

    .form-control{
        min-height:38px;
        height:38px;
        border-radius:8px!important;
        border:1px solid var(--aws-line)!important;
        color:var(--aws-text);
        font-size:.9rem;
        font-weight:650;
        box-shadow:none!important;
    }

    .form-control:focus{
        border-color:var(--aws-blue)!important;
        box-shadow:var(--aws-focus)!important;
    }

    .filter-actions{
        display:flex;
        align-items:center;
        gap:8px;
        min-height:38px;
        flex-wrap:wrap;
    }

    .btn-filter-custom,
    .btn-reset-custom{
        height:38px;
        padding:0 14px;
        border-radius:8px;
        font-weight:800;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        border:1px solid transparent;
        font-size:.86rem;
        white-space:nowrap;
        line-height:1;
    }

    .btn-filter-custom{
        background:var(--aws-blue);
        border-color:var(--aws-blue);
        color:#fff;
    }

    .btn-filter-custom:hover{
        background:var(--aws-blue-dark);
        border-color:var(--aws-blue-dark);
        color:#fff;
    }

    .btn-reset-custom{
        background:#fff;
        border-color:var(--aws-line);
        color:#414d5c;
    }

    .btn-reset-custom:hover{
        background:#f2f3f3;
        color:var(--aws-text);
    }

    .filter-help{
        color:var(--aws-muted);
        font-size:.76rem;
        font-weight:600;
        margin-top:7px;
    }

    .summary-section{
        padding:14px;
        border-bottom:1px solid var(--aws-line);
        background:#fff;
    }

    .summary-box{
        background:#fff;
        border:1px solid var(--aws-line);
        border-radius:var(--aws-radius);
        padding:13px 14px;
        height:100%;
        box-shadow:var(--aws-shadow);
    }

    .summary-title{
        font-size:.76rem;
        color:var(--aws-muted);
        margin-bottom:6px;
        font-weight:800;
    }

    .summary-value{
        font-size:1.08rem;
        font-weight:850;
        color:var(--aws-text);
        line-height:1.2;
        letter-spacing:-.02em;
    }

    .meta-box{
        background:#f1f8ff;
        border:1px solid #b6d7f5;
        border-radius:var(--aws-radius);
        padding:10px 12px;
        margin:14px;
        font-size:.82rem;
        color:#033160;
        font-weight:700;
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:8px 12px;
    }

    .table-section{
        padding:0 14px 14px;
        min-width:0;
    }

    .table-outer{
        width:100%;
        max-width:100%;
        overflow:auto;
        border:1px solid var(--aws-line);
        border-radius:var(--aws-radius);
        background:#fff;
        -webkit-overflow-scrolling:touch;
        overscroll-behavior:contain;
        max-height:70vh;
    }

    .table-outer::-webkit-scrollbar{
        height:10px;
        width:10px;
    }

    .table-outer::-webkit-scrollbar-thumb{
        background:#cbd5e1;
        border-radius:999px;
    }

    .table-outer::-webkit-scrollbar-track{
        background:#f1f5f9;
    }

    #table-profit-loss{
        width:100%!important;
        min-width:720px;
        border-collapse:separate;
        border-spacing:0;
        margin:0!important;
        color:var(--aws-text);
    }

    #table-profit-loss thead th{
        position:sticky;
        top:0;
        z-index:3;
        background:#f8f9fa!important;
        color:#414d5c;
        font-size:.72rem;
        font-weight:850;
        text-align:center;
        text-transform:uppercase;
        letter-spacing:.04em;
        border-bottom:1px solid var(--aws-line)!important;
    }

    #table-profit-loss th,
    #table-profit-loss td{
        white-space:nowrap;
        vertical-align:middle;
        padding:10px 12px!important;
        border-right:1px solid var(--aws-line-soft);
        border-bottom:1px solid var(--aws-line-soft);
        font-size:.84rem;
        background:#fff;
    }

    #table-profit-loss .sticky-col{
        position:sticky;
        left:0;
        z-index:4;
        width:240px;
        min-width:240px;
        max-width:240px;
        background:#fff;
        box-shadow:8px 0 0 rgba(15,23,42,.03);
        white-space:normal;
    }

    #table-profit-loss thead .sticky-col{
        background:#f8f9fa!important;
        z-index:5;
    }

    #table-profit-loss .text-end{
        text-align:right;
        font-variant-numeric:tabular-nums;
    }

    #table-profit-loss .row-label{
        font-weight:850;
        color:var(--aws-text);
    }

    #table-profit-loss .highlight-row td{
        background:#f1f8ff!important;
        font-weight:850;
    }

    #table-profit-loss .highlight-row .sticky-col{
        background:#f1f8ff!important;
    }

    #table-profit-loss .percent-row td{
        background:#fff7ed!important;
        font-weight:850;
    }

    #table-profit-loss .percent-row .sticky-col{
        background:#fff7ed!important;
    }

    #table-profit-loss .negative{
        color:var(--aws-red);
        font-weight:850;
    }

    #table-profit-loss .positive{
        color:var(--aws-green);
        font-weight:850;
    }

    #table-profit-loss tbody tr:hover td{
        background:#f2f8fd!important;
    }

    #table-profit-loss tbody tr:hover .sticky-col{
        background:#f2f8fd!important;
    }

    .alert{
        margin:14px 14px 0;
        border-radius:var(--aws-radius);
        font-size:.86rem;
        font-weight:650;
    }

    .alert-danger{
        background:#fff1f0;
        border-color:#f3b8ad;
        color:#7c2d12;
    }

    .alert-warning{
        background:#fff7ed;
        border-color:#f8d7a0;
        color:#7c2d12;
    }


    .sync-panel{
        margin:14px;
        border:1px solid #b6d7f5;
        background:#f1f8ff;
        border-radius:var(--aws-radius);
        padding:12px 14px;
        display:none;
    }

    .sync-panel.is-visible{
        display:block;
    }

    .sync-panel-title{
        font-size:.86rem;
        font-weight:850;
        color:#033160;
        margin-bottom:8px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
        flex-wrap:wrap;
    }

    .sync-progress-wrap{
        height:10px;
        border-radius:999px;
        background:#dbeafe;
        overflow:hidden;
        border:1px solid #bfdbfe;
    }

    .sync-progress-bar{
        height:100%;
        width:0%;
        background:var(--aws-blue);
        transition:width .35s ease;
    }

    .sync-message{
        margin-top:8px;
        color:#033160;
        font-size:.8rem;
        font-weight:700;
    }

    .sync-grid{
        margin-top:8px;
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:8px;
    }

    .sync-stat{
        background:#fff;
        border:1px solid #dbeafe;
        border-radius:8px;
        padding:8px 10px;
        font-size:.76rem;
        color:#334155;
        font-weight:800;
    }

    .sync-stat span{
        display:block;
        margin-top:3px;
        color:#0f172a;
        font-size:.9rem;
    }

    .btn-sync-custom{
        height:38px;
        padding:0 14px;
        border-radius:8px;
        font-weight:800;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        border:1px solid var(--aws-green);
        background:var(--aws-green);
        color:#fff;
        font-size:.86rem;
        white-space:nowrap;
        line-height:1;
    }

    .btn-sync-custom:hover{
        background:#025f0a;
        border-color:#025f0a;
        color:#fff;
    }

    .btn-sync-custom:disabled{
        opacity:.7;
        cursor:not-allowed;
    }

    @media (max-width:991.98px){
        .meta-box{
            grid-template-columns:1fr;
        }

        .filter-actions{
            align-items:stretch;
        }

        .sync-grid{
            grid-template-columns:1fr 1fr;
        }
    }

    @media (max-width:768px){
        .pnl-page{
            gap:12px;
        }

        .pnl-hero{
            align-items:flex-start;
            flex-direction:column;
            gap:10px;
        }

        .pnl-main-title{
            font-size:1.16rem;
        }

        .pnl-pill{
            border-radius:8px;
            white-space:normal;
            font-size:.72rem;
        }

        .pnl-card-header,
        .pnl-filter-wrap,
        .summary-section,
        .table-section{
            padding-left:12px;
            padding-right:12px;
        }

        .filter-actions{
            display:grid;
            grid-template-columns:1fr;
            min-height:auto;
        }

        .btn-filter-custom,
        .btn-reset-custom,
        .btn-sync-custom{
            width:100%;
        }

        .sync-grid{
            grid-template-columns:1fr;
        }

        .summary-value{
            font-size:1.02rem;
        }

        .meta-box{
            margin:12px;
            padding:11px 12px;
        }

        .table-outer{
            max-height:70vh;
        }

        #table-profit-loss{
            min-width:760px;
        }

        #table-profit-loss th,
        #table-profit-loss td{
            padding:8px 9px!important;
            font-size:.76rem;
        }

        #table-profit-loss .sticky-col{
            width:170px;
            min-width:170px;
            max-width:170px;
        }
    }
</style>

<div class="pnl-page">
    <div class="pnl-hero">
        <div>
            <div class="pnl-kicker">Laporan / Profit & Loss Internal</div>
            <h1 class="pnl-main-title">Profit & Loss Internal Live</h1>
        </div>
        <div class="pnl-hero-pills">
            <span class="pnl-pill"><i class="bi bi-calendar-range"></i> {{ $startDateInput }} / {{ $endDateInput }}</span>
            <span class="pnl-pill"><i class="bi bi-lightning-charge"></i> {{ $liveMeta['served_from_cache'] ? 'Redis Cache' : 'Live Refresh' }}</span>
        </div>
    </div>

    <div class="pnl-card">
        <div class="pnl-card-header">
            <h4 class="pnl-title">Laporan Profit &amp; Loss Internal LIVE</h4>
        </div>

        @if (session('error'))
            <div class="alert alert-danger mx-3 mt-3 mb-0">
                {{ session('error') }}
            </div>
        @endif

        @if (!empty($liveErrors))
            <div class="alert alert-warning mx-3 mt-3 mb-0">
                <div><strong>Beberapa branch gagal ditarik live:</strong></div>
                <ul class="mb-0 mt-2">
                    @foreach ($liveErrors as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="pnl-filter-wrap">
            <div class="filter-card">
                <form method="GET" action="{{ route('investor.laporan.profitnloss.oknho') }}">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="filter-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDateInput }}" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="filter-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDateInput }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="filter-label">Aksi</label>
                            <div class="filter-actions">
                                <button type="submit" class="btn-filter-custom"><i class="bi bi-funnel"></i> Filter Cache</button>
                                <button type="button" id="btnStartPnlSync" class="btn-sync-custom" {{ $isSyncRunning ? 'disabled' : '' }}>
                                    <i class="bi bi-cloud-arrow-down"></i> {{ $isSyncRunning ? 'Sync Berjalan' : 'Tarik Data ESB' }}
                                </button>
                                <a href="{{ route('investor.laporan.profitnloss.oknho') }}" class="btn-reset-custom"><i class="bi bi-arrow-clockwise"></i> Reset</a>
                            </div>
                        </div>
                        <div class="filter-help">Batas maksimum data live: 1 sampai 7 hari.</div>
                    </div>
                </form>
            </div>
        </div>

        <div id="pnlSyncPanel" class="sync-panel {{ $currentSyncKey ? 'is-visible' : '' }}" data-sync-key="{{ $currentSyncKey }}">
            <div class="sync-panel-title">
                <span><i class="bi bi-activity"></i> Status Sync PNL Live</span>
                <span id="pnlSyncPercent">{{ (int) data_get($syncState, 'progress', 0) }}%</span>
            </div>
            <div class="sync-progress-wrap">
                <div id="pnlSyncBar" class="sync-progress-bar" style="width: {{ (int) data_get($syncState, 'progress', 0) }}%;"></div>
            </div>
            <div id="pnlSyncMessage" class="sync-message">
                {{ data_get($syncState, 'message', $currentSyncKey ? 'Menunggu status sync...' : 'Belum ada sync berjalan.') }}
            </div>
            <div class="sync-grid">
                <div class="sync-stat">Status <span id="pnlSyncStatus">{{ data_get($syncState, 'status', '-') }}</span></div>
                <div class="sync-stat">Page <span id="pnlSyncPages">{{ (int) data_get($syncState, 'processed_pages', 0) }} / {{ (int) data_get($syncState, 'total_pages', 0) }}</span></div>
                <div class="sync-stat">Rows API <span id="pnlSyncRows">{{ (int) data_get($syncState, 'total_api_rows', 0) }}</span></div>
                <div class="sync-stat">Rows Saved <span id="pnlSyncSaved">{{ (int) data_get($syncState, 'total_saved_rows', 0) }}</span></div>
            </div>
        </div>

        <div class="summary-section">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="summary-title">Total Pendapatan</div>
                        <div class="summary-value">{{ $formatNominal($grandPendapatan) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="summary-title">Laba (Rugi) Bersih</div>
                        <div class="summary-value">{{ $formatNominal($grandLaba) }}</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-box">
                        <div class="summary-title">NPM</div>
                        <div class="summary-value">{{ number_format((float) $grandNpm, 2, ',', '.') }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="meta-box">
            <div><strong>Status data:</strong> {{ $liveMeta['served_from_cache'] ? 'Redis Cache' : 'Live Refresh' }}</div>
            <div><strong>Stale fallback:</strong> {{ !empty($liveMeta['is_stale']) ? 'Ya' : 'Tidak' }}</div>
            <div><strong>Generated at:</strong> {{ $liveMeta['generated_at'] ?? '-' }}</div>
        </div>

        <div class="table-section">
            <div class="table-outer">
                <table id="table-profit-loss" class="table mb-0">
                    <thead>
                        <tr>
                            <th class="sticky-col">Keterangan</th>
                            @foreach ($units as $unit)
                                <th>{{ $unit->nama_outlet }}</th>
                            @endforeach
                            <th>Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                $isPercent = $row['is_percent'] ?? false;
                                $rowClass = '';

                                if ($row['keterangan'] === 'Laba (Rugi) Bersih') {
                                    $rowClass = 'highlight-row';
                                }

                                if ($row['keterangan'] === 'NPM') {
                                    $rowClass = 'percent-row';
                                }

                                $rowGrandTotal = $row['keterangan'] === 'NPM'
                                    ? $grandNpm
                                    : $grandTotal($row);
                            @endphp

                            <tr class="{{ $rowClass }}">
                                <td class="sticky-col row-label">{{ $row['keterangan'] }}</td>

                                @foreach ($row['values'] as $value)
                                    @if ($isPercent)
                                        <td class="text-end {{ $value < 0 ? 'negative' : 'positive' }}">
                                            {{ number_format((float) $value, 2, ',', '.') }}%
                                        </td>
                                    @else
                                        <td class="text-end {{ $value < 0 ? 'negative' : '' }}">
                                            {{ $formatNominal($value) }}
                                        </td>
                                    @endif
                                @endforeach

                                @if ($isPercent)
                                    <td class="text-end {{ $rowGrandTotal < 0 ? 'negative' : 'positive' }}">
                                        {{ number_format((float) $rowGrandTotal, 2, ',', '.') }}%
                                    </td>
                                @else
                                    <td class="text-end {{ $rowGrandTotal < 0 ? 'negative' : '' }}">
                                        {{ $formatNominal($rowGrandTotal) }}
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td class="sticky-col">Tidak ada data</td>
                                <td colspan="{{ count($units) + 1 }}" class="text-center">Tidak ada data untuk periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script>
(function () {
    const startButton = document.getElementById('btnStartPnlSync');
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const panel = document.getElementById('pnlSyncPanel');
    const bar = document.getElementById('pnlSyncBar');
    const percentText = document.getElementById('pnlSyncPercent');
    const messageText = document.getElementById('pnlSyncMessage');
    const statusText = document.getElementById('pnlSyncStatus');
    const pagesText = document.getElementById('pnlSyncPages');
    const rowsText = document.getElementById('pnlSyncRows');
    const savedText = document.getElementById('pnlSyncSaved');

    let syncKey = panel ? panel.dataset.syncKey : null;
    let pollTimer = null;

    function setLoading(isLoading) {
        if (!startButton) return;
        startButton.disabled = isLoading;
        startButton.innerHTML = isLoading
            ? '<i class="bi bi-hourglass-split"></i> Memulai Sync...'
            : '<i class="bi bi-cloud-arrow-down"></i> Tarik Data ESB';
    }

    function renderStatus(data) {
        if (!panel || !data) return;
        panel.classList.add('is-visible');

        const progress = Math.max(0, Math.min(100, parseInt(data.progress || 0, 10)));
        bar.style.width = progress + '%';
        percentText.textContent = progress + '%';
        messageText.textContent = data.message || '-';
        statusText.textContent = data.status || '-';
        pagesText.textContent = (data.processed_pages || 0) + ' / ' + (data.total_pages || 0);
        rowsText.textContent = data.total_api_rows || 0;
        savedText.textContent = data.total_saved_rows || 0;

        const doneStatuses = ['done', 'done_with_errors', 'failed'];
        if (doneStatuses.includes(data.status)) {
            clearInterval(pollTimer);
            pollTimer = null;
            if (startButton) {
                startButton.disabled = false;
                startButton.innerHTML = '<i class="bi bi-cloud-arrow-down"></i> Tarik Data ESB';
            }

            if (data.status === 'done' || data.status === 'done_with_errors') {
                setTimeout(function () {
                    window.location.reload();
                }, 1200);
            }
        } else if (startButton) {
            startButton.disabled = true;
            startButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Sync Berjalan';
        }
    }

    function pollStatus(key) {
        if (!key) return;
        const statusUrl = "{{ route('investor.laporan.profitnloss.oknho.status', ['key' => '__KEY__']) }}".replace('__KEY__', encodeURIComponent(key));

        fetch(statusUrl, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(function (response) { return response.json(); })
            .then(renderStatus)
            .catch(function () {
                if (messageText) messageText.textContent = 'Gagal membaca status sync. Cek koneksi atau laravel.log.';
            });
    }

    if (startButton) {
        startButton.addEventListener('click', function () {
            if (!startInput.value || !endInput.value) {
                alert('Tanggal mulai dan tanggal akhir wajib diisi.');
                return;
            }

            setLoading(true);
            if (panel) panel.classList.add('is-visible');
            if (messageText) messageText.textContent = 'Mengirim job sync PNL live...';

            fetch("{{ route('investor.laporan.profitnloss.oknho.start-sync') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    start_date: startInput.value,
                    end_date: endInput.value
                })
            })
                .then(async function (response) {
                    const data = await response.json().catch(function () { return {}; });
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memulai sync PNL live.');
                    }
                    return data;
                })
                .then(function (data) {
                    syncKey = data.sync_key;
                    if (panel) panel.dataset.syncKey = syncKey;
                    renderStatus(data);

                    const url = new URL(window.location.href);
                    url.searchParams.set('start_date', startInput.value);
                    url.searchParams.set('end_date', endInput.value);
                    url.searchParams.set('sync_key', syncKey);
                    window.history.replaceState({}, '', url.toString());

                    pollStatus(syncKey);
                    pollTimer = setInterval(function () { pollStatus(syncKey); }, 2500);
                })
                .catch(function (error) {
                    setLoading(false);
                    if (messageText) messageText.textContent = error.message;
                    alert(error.message);
                });
        });
    }

    if (syncKey) {
        pollStatus(syncKey);
        pollTimer = setInterval(function () { pollStatus(syncKey); }, 2500);
    }
})();
</script>

@include('Temp.Investor.footer')

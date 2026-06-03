@include('Temp.Investor.header')

@php
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
@endphp

<style>
    .pnl-page { padding: 20px; }
    .pnl-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,.04);
    }
    .pnl-card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .pnl-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }
    .pnl-filter-wrap { padding: 20px 20px 10px 20px; }
    .filter-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 16px;
    }
    .filter-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .filter-actions {
        display: flex;
        align-items: end;
        gap: 10px;
        height: 100%;
        flex-wrap: wrap;
    }
    .btn-filter-custom,
    .btn-reset-custom {
        padding: 9px 18px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        border: 1px solid transparent;
    }
    .btn-filter-custom {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }
    .btn-filter-custom:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }
    .btn-reset-custom {
        background: #fff;
        border-color: #d1d5db;
        color: #374151;
    }
    .btn-reset-custom:hover {
        background: #f3f4f6;
        color: #111827;
    }
    .summary-section { padding: 0 20px 20px 20px; }
    .summary-box {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px 16px;
        height: 100%;
    }
    .summary-title {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 6px;
    }
    .summary-value {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }
    .meta-box {
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        padding: 12px 16px;
        margin: 0 20px 20px 20px;
        font-size: 13px;
        color: #475569;
    }
    .table-section { padding: 0 20px 20px 20px; }
    .table-outer {
        width: 100%;
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }
    #table-profit-loss {
        width: max-content !important;
        min-width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    #table-profit-loss thead th {
        background: #eff6ff;
        color: #1f2937;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
        border-bottom: 1px solid #dbeafe !important;
    }
    #table-profit-loss th,
    #table-profit-loss td {
        white-space: nowrap;
        vertical-align: middle;
        padding: 12px 14px !important;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        background: #fff;
    }
    #table-profit-loss .sticky-col {
        position: sticky;
        left: 0;
        z-index: 4;
        min-width: 240px;
        max-width: 240px;
        background: #fff;
        box-shadow: 2px 0 0 #e5e7eb;
    }
    #table-profit-loss thead .sticky-col {
        background: #eff6ff !important;
        z-index: 6;
    }
    #table-profit-loss .text-end { text-align: right; }
    #table-profit-loss .row-label { font-weight: 600; color: #111827; }
    #table-profit-loss .highlight-row td { background: #f8fbff; font-weight: 700; }
    #table-profit-loss .percent-row td { background: #fcfcfc; font-weight: 700; }
    #table-profit-loss .negative { color: #dc2626; font-weight: 600; }
    #table-profit-loss .positive { color: #059669; font-weight: 600; }
    @media (max-width: 768px) {
        .pnl-page { padding: 12px; }
        .pnl-card-header,
        .pnl-filter-wrap,
        .summary-section,
        .table-section { padding-left: 12px; padding-right: 12px; }
        .meta-box { margin-left: 12px; margin-right: 12px; }
        .filter-actions { align-items: stretch; }
        .btn-filter-custom,
        .btn-reset-custom { width: 100%; text-align: center; }
    }
</style>

<div class="container-fluid pnl-page">
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
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="filter-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="filter-label">Aksi</label>
                            <div class="filter-actions">
                                <button type="submit" class="btn-filter-custom">Filter Live</button>
                                <a href="{{ route('investor.laporan.profitnloss.oknho') }}" class="btn-reset-custom">Reset</a>
                            </div>
                            <small class="text-muted d-block mt-2">Batas maksimum data live: 1 sampai 7 hari.</small>
                        </div>
                    </div>
                </form>
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

@include('Temp.Investor.footer')

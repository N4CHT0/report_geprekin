@section('title', 'Recap Site Score')
@section('breadcrumb', 'Site Score Outlet / Recap')

@include('Surveyor.layouts.header')

@php
    $summary = $summary ?? (object)[
        'total_survey' => 0,
        'approved' => 0,
        'consideration' => 0,
        'rejected' => 0,
        'avg_score' => 0
    ];

    $bySurveyor = $bySurveyor ?? collect();
@endphp

<style>
    :root {
        --recap-bg: #f4f7fb;
        --recap-card: #ffffff;
        --recap-text: #111827;
        --recap-muted: #64748b;
        --recap-border: #e5e7eb;
        --recap-primary: #2563eb;
        --recap-primary-soft: #eff6ff;
        --recap-green: #16a34a;
        --recap-green-soft: #dcfce7;
        --recap-yellow: #ca8a04;
        --recap-yellow-soft: #fef9c3;
        --recap-red: #dc2626;
        --recap-red-soft: #fee2e2;
        --recap-shadow: 0 12px 32px rgba(15, 23, 42, .07);
    }

    .recap-page {
        min-height: calc(100vh - 70px);
        padding: 22px 26px 32px;
        background: var(--recap-bg);
        color: var(--recap-text);
    }

    .recap-shell {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }

    .recap-hero {
        margin-bottom: 16px;
        padding: 18px 20px;
        border: 1px solid var(--recap-border);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .recap-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        margin-bottom: 8px;
        border-radius: 999px;
        background: var(--recap-primary-soft);
        color: var(--recap-primary);
        font-size: 12px;
        font-weight: 900;
    }

    .recap-hero h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: -.03em;
    }

    .recap-hero p {
        margin: 7px 0 0;
        max-width: 820px;
        color: var(--recap-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .recap-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 14px;
    }

    .recap-summary-card {
        position: relative;
        overflow: hidden;
        min-height: 118px;
        padding: 16px;
        border: 1px solid var(--recap-border);
        border-radius: 22px;
        background: #fff;
        box-shadow: var(--recap-shadow);
    }

    .recap-summary-card::after {
        content: "";
        position: absolute;
        width: 110px;
        height: 110px;
        right: -38px;
        top: -38px;
        border-radius: 999px;
        background: var(--recap-primary-soft);
    }

    .recap-summary-card.green::after { background: var(--recap-green-soft); }
    .recap-summary-card.yellow::after { background: var(--recap-yellow-soft); }
    .recap-summary-card.red::after { background: var(--recap-red-soft); }

    .recap-summary-icon {
        position: relative;
        z-index: 1;
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        margin-bottom: 12px;
        border-radius: 14px;
        background: var(--recap-primary-soft);
        color: var(--recap-primary);
        font-size: 18px;
    }

    .recap-summary-card.green .recap-summary-icon {
        background: var(--recap-green-soft);
        color: var(--recap-green);
    }

    .recap-summary-card.yellow .recap-summary-icon {
        background: var(--recap-yellow-soft);
        color: var(--recap-yellow);
    }

    .recap-summary-card.red .recap-summary-icon {
        background: var(--recap-red-soft);
        color: var(--recap-red);
    }

    .recap-summary-label {
        position: relative;
        z-index: 1;
        margin-bottom: 4px;
        color: var(--recap-muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .recap-summary-value {
        position: relative;
        z-index: 1;
        font-size: 30px;
        line-height: 1;
        font-weight: 950;
        letter-spacing: -.04em;
        color: var(--recap-text);
    }

    .recap-card {
        border: 1px solid var(--recap-border);
        border-radius: 22px;
        background: var(--recap-card);
        box-shadow: var(--recap-shadow);
        overflow: hidden;
    }

    .recap-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 15px 18px;
        border-bottom: 1px solid var(--recap-border);
        background: #fff;
    }

    .recap-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 17px;
        font-weight: 900;
    }

    .recap-card-title i {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--recap-primary-soft);
        color: var(--recap-primary);
    }

    .recap-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    .recap-table thead th {
        padding: 13px 16px !important;
        border-bottom: 1px solid var(--recap-border) !important;
        background: #f8fafc;
        color: var(--recap-muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .recap-table tbody td {
        padding: 14px 16px !important;
        border-bottom: 1px solid var(--recap-border);
        vertical-align: middle;
        color: #1f2937;
        font-size: 14px;
    }

    .recap-table tbody tr:hover td {
        background: #f8fafc;
    }

    .recap-surveyor {
        font-weight: 900;
        color: #111827;
    }

    .recap-number {
        font-weight: 900;
        color: #111827;
    }

    .recap-score {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--recap-primary-soft);
        color: var(--recap-primary);
        font-size: 12px;
        font-weight: 950;
    }

    .recap-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 64px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
    }

    .recap-pill.green {
        background: var(--recap-green-soft);
        color: var(--recap-green);
    }

    .recap-pill.yellow {
        background: var(--recap-yellow-soft);
        color: var(--recap-yellow);
    }

    .recap-pill.red {
        background: var(--recap-red-soft);
        color: var(--recap-red);
    }

    #rekapTable_wrapper .row:first-child,
    #rekapTable_wrapper .row:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 !important;
        padding: 12px 16px;
        background: #fff;
    }

    #rekapTable_wrapper .row:first-child {
        border-bottom: 1px solid var(--recap-border);
    }

    #rekapTable_wrapper .row:last-child {
        border-top: 1px solid var(--recap-border);
    }

    #rekapTable_wrapper .row:first-child > div,
    #rekapTable_wrapper .row:last-child > div {
        width: auto;
        padding: 0 !important;
        flex: 0 0 auto;
    }

    #rekapTable_wrapper .row:nth-child(2) {
        margin: 0 !important;
    }

    #rekapTable_wrapper .row:nth-child(2) > div {
        padding: 0 !important;
    }

    #rekapTable_length label,
    #rekapTable_filter label,
    #rekapTable_info {
        margin: 0;
        color: var(--recap-muted);
        font-size: 13px;
        font-weight: 700;
    }

    #rekapTable_length select,
    #rekapTable_filter input {
        height: 36px;
        border: 1px solid var(--recap-border);
        border-radius: 10px;
        padding: 6px 10px;
        margin: 0 6px;
        outline: none;
        box-shadow: none;
    }

    #rekapTable_filter input {
        min-width: 260px;
        margin-right: 0;
    }

    #rekapTable_paginate .pagination {
        margin: 0;
        gap: 6px;
    }

    #rekapTable_paginate .page-link {
        border-radius: 10px;
        border: 1px solid var(--recap-border);
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        box-shadow: none;
    }

    #rekapTable_paginate .page-item.active .page-link {
        background: var(--recap-primary);
        border-color: var(--recap-primary);
        color: #fff;
    }

    .dataTables_empty {
        padding: 28px 16px !important;
        color: var(--recap-muted) !important;
        font-weight: 800;
    }

    @media (max-width: 1000px) {
        .recap-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px) {
        .recap-page {
            padding: 14px;
        }

        .recap-hero {
            padding: 16px;
        }

        .recap-hero h1 {
            font-size: 24px;
        }

        #rekapTable_wrapper .row:first-child,
        #rekapTable_wrapper .row:last-child {
            align-items: stretch;
            flex-direction: column;
        }

        #rekapTable_wrapper .row:first-child > div,
        #rekapTable_wrapper .row:last-child > div {
            width: 100%;
        }

        #rekapTable_filter input {
            width: 100%;
            min-width: 0;
            margin: 8px 0 0;
        }
    }

    @media (max-width: 560px) {
        .recap-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="recap-page">
    <div class="recap-shell">

        <div class="recap-hero">
            <div class="recap-eyebrow">
                <i class="bi bi-bar-chart-line"></i>
                Recap Site Score
            </div>

            <h1>Rekapitulasi Site Score</h1>

            <p>
                Ringkasan hasil survey outlet berdasarkan status rekomendasi dan performa setiap surveyor.
                Tampilan ini menggantikan format pivot Excel agar lebih mudah dibaca.
            </p>
        </div>

        <div class="recap-summary-grid">
            <div class="recap-summary-card">
                <div class="recap-summary-icon">
                    <i class="bi bi-clipboard-data"></i>
                </div>
                <div class="recap-summary-label">Total Survey</div>
                <div class="recap-summary-value">{{ number_format($summary->total_survey ?? 0) }}</div>
            </div>

            <div class="recap-summary-card green">
                <div class="recap-summary-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="recap-summary-label">Approved</div>
                <div class="recap-summary-value">{{ number_format($summary->approved ?? 0) }}</div>
            </div>

            <div class="recap-summary-card yellow">
                <div class="recap-summary-icon">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div class="recap-summary-label">Consideration</div>
                <div class="recap-summary-value">{{ number_format($summary->consideration ?? 0) }}</div>
            </div>

            <div class="recap-summary-card red">
                <div class="recap-summary-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div class="recap-summary-label">Rejected</div>
                <div class="recap-summary-value">{{ number_format($summary->rejected ?? 0) }}</div>
            </div>
        </div>

        <div class="recap-card">
            <div class="recap-card-header">
                <h2 class="recap-card-title">
                    <i class="bi bi-people"></i>
                    Rekap Per Surveyor
                </h2>
            </div>

            <div class="table-responsive">
                <table class="table recap-table align-middle" id="rekapTable">
                    <thead>
                        <tr>
                            <th>Surveyor</th>
                            <th>Total Survey</th>
                            <th>Average Score</th>
                            <th>Approved</th>
                            <th>Consideration</th>
                            <th>Rejected</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($bySurveyor as $row)
                            <tr>
                                <td>
                                    <div class="recap-surveyor">
                                        {{ $row->surveyor }}
                                    </div>
                                </td>

                                <td>
                                    <span class="recap-number">
                                        {{ number_format($row->total_survey ?? 0) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="recap-score">
                                        {{ number_format($row->avg_score ?? 0, 2) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="recap-pill green">
                                        {{ number_format($row->approved ?? 0) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="recap-pill yellow">
                                        {{ number_format($row->consideration ?? 0) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="recap-pill red">
                                        {{ number_format($row->rejected ?? 0) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    $(function () {
        $('#rekapTable').DataTable({
            ordering: false,
            autoWidth: false,
            responsive: true,
            language: {
                emptyTable: 'Belum ada data recap site score',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 data',
                zeroRecords: 'Data tidak ditemukan',
                paginate: {
                    previous: 'Sebelumnya',
                    next: 'Berikutnya'
                }
            }
        });
    });
</script>
@endpush

@include('Surveyor.layouts.footer')

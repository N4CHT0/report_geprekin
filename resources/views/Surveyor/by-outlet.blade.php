@section('title', 'Pengamatan By Outlet')
@section('breadcrumb', 'Pengamatan / By Outlet')

@include('Surveyor.layouts.header')

@php $outlets = $outlets ?? collect(); @endphp

<style>
    :root {
        --outlet-bg: #f4f7fb;
        --outlet-card: #ffffff;
        --outlet-text: #111827;
        --outlet-muted: #64748b;
        --outlet-border: #e5e7eb;
        --outlet-primary: #2563eb;
        --outlet-primary-soft: #eff6ff;
        --outlet-green: #16a34a;
        --outlet-green-soft: #dcfce7;
        --outlet-shadow: 0 12px 32px rgba(15, 23, 42, .07);
    }

    .outlet-page {
        min-height: calc(100vh - 70px);
        padding: 22px 26px 32px;
        background: var(--outlet-bg);
        color: var(--outlet-text);
    }

    .outlet-shell {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }

    .outlet-hero {
        margin-bottom: 16px;
        padding: 18px 20px;
        border: 1px solid var(--outlet-border);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .outlet-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        margin-bottom: 8px;
        border-radius: 999px;
        background: var(--outlet-primary-soft);
        color: var(--outlet-primary);
        font-size: 12px;
        font-weight: 900;
    }

    .outlet-hero h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: -.03em;
    }

    .outlet-hero p {
        margin: 7px 0 0;
        max-width: 820px;
        color: var(--outlet-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .outlet-card {
        border: 1px solid var(--outlet-border);
        border-radius: 22px;
        background: var(--outlet-card);
        box-shadow: var(--outlet-shadow);
        overflow: hidden;
    }

    .outlet-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 15px 18px;
        border-bottom: 1px solid var(--outlet-border);
        background: #fff;
    }

    .outlet-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 17px;
        font-weight: 900;
    }

    .outlet-card-title i {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--outlet-primary-soft);
        color: var(--outlet-primary);
    }

    .outlet-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    .outlet-table thead th {
        padding: 13px 16px !important;
        border-bottom: 1px solid var(--outlet-border) !important;
        background: #f8fafc;
        color: var(--outlet-muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .outlet-table tbody td {
        padding: 14px 16px !important;
        border-bottom: 1px solid var(--outlet-border);
        vertical-align: middle;
        color: #1f2937;
        font-size: 14px;
    }

    .outlet-table tbody tr:hover td {
        background: #f8fafc;
    }

    .outlet-name {
        font-weight: 900;
        color: #111827;
    }

    .outlet-muted-text {
        color: var(--outlet-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .outlet-score {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 70px;
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--outlet-primary-soft);
        color: var(--outlet-primary);
        font-size: 12px;
        font-weight: 950;
    }

    .outlet-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 74px;
        padding: 7px 11px;
        border-radius: 999px;
        background: var(--outlet-green-soft);
        color: var(--outlet-green);
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }

    .outlet-map-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 7px 12px;
        border: 1px solid rgba(37, 99, 235, .22);
        border-radius: 999px;
        background: var(--outlet-primary-soft);
        color: var(--outlet-primary);
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .outlet-note {
        max-width: 360px;
        color: var(--outlet-muted);
        font-size: 13px;
        line-height: 1.4;
    }

    #outletTable_wrapper .row:first-child,
    #outletTable_wrapper .row:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 !important;
        padding: 12px 16px;
        background: #fff;
    }

    #outletTable_wrapper .row:first-child {
        border-bottom: 1px solid var(--outlet-border);
    }

    #outletTable_wrapper .row:last-child {
        border-top: 1px solid var(--outlet-border);
    }

    #outletTable_wrapper .row:first-child > div,
    #outletTable_wrapper .row:last-child > div {
        width: auto;
        padding: 0 !important;
        flex: 0 0 auto;
    }

    #outletTable_wrapper .row:nth-child(2) {
        margin: 0 !important;
    }

    #outletTable_wrapper .row:nth-child(2) > div {
        padding: 0 !important;
    }

    #outletTable_length label,
    #outletTable_filter label,
    #outletTable_info {
        margin: 0;
        color: var(--outlet-muted);
        font-size: 13px;
        font-weight: 700;
    }

    #outletTable_length select,
    #outletTable_filter input {
        height: 36px;
        border: 1px solid var(--outlet-border);
        border-radius: 10px;
        padding: 6px 10px;
        margin: 0 6px;
        outline: none;
        box-shadow: none;
    }

    #outletTable_filter input {
        min-width: 260px;
        margin-right: 0;
    }

    #outletTable_paginate .pagination {
        margin: 0;
        gap: 6px;
    }

    #outletTable_paginate .page-link {
        border-radius: 10px;
        border: 1px solid var(--outlet-border);
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        box-shadow: none;
    }

    #outletTable_paginate .page-item.active .page-link {
        background: var(--outlet-primary);
        border-color: var(--outlet-primary);
        color: #fff;
    }

    .dataTables_empty {
        padding: 28px 16px !important;
        color: var(--outlet-muted) !important;
        font-weight: 800;
    }

    @media (max-width: 900px) {
        .outlet-page {
            padding: 14px;
        }

        .outlet-hero {
            padding: 16px;
        }

        .outlet-hero h1 {
            font-size: 24px;
        }

        #outletTable_wrapper .row:first-child,
        #outletTable_wrapper .row:last-child {
            align-items: stretch;
            flex-direction: column;
        }

        #outletTable_wrapper .row:first-child > div,
        #outletTable_wrapper .row:last-child > div {
            width: 100%;
        }

        #outletTable_filter input {
            width: 100%;
            min-width: 0;
            margin: 8px 0 0;
        }
    }
</style>

<div class="outlet-page">
    <div class="outlet-shell">

        <div class="outlet-hero">
            <div class="outlet-eyebrow">
                <i class="bi bi-shop"></i>
                Pengamatan By Outlet
            </div>

            <h1>Pengamatan Berdasarkan Outlet</h1>

            <p>
                Mapping data outlet dengan histori pengamatan, titik koordinat, score terakhir,
                dan catatan survey untuk memudahkan monitoring lokasi aktif.
            </p>
        </div>

        <div class="outlet-card">
            <div class="outlet-card-header">
                <h2 class="outlet-card-title">
                    <i class="bi bi-table"></i>
                    Daftar Outlet Pengamatan
                </h2>
            </div>

            <div class="table-responsive">
                <table class="table outlet-table align-middle" id="outletTable">
                    <thead>
                        <tr>
                            <th>Outlet</th>
                            <th>Area</th>
                            <th>Status</th>
                            <th>Last Score</th>
                            <th>Lat</th>
                            <th>Lng</th>
                            <th>Maps</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($outlets as $row)
                            <tr>
                                <td>
                                    <div class="outlet-name">
                                        {{ $row->nama_outlet ?? $row->name ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="outlet-muted-text">
                                        {{ $row->area ?? $row->kota ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="outlet-status">ACTIVE</span>
                                </td>

                                <td>
                                    <span class="outlet-score">
                                        {{ number_format($row->final_percent ?? 0, 2) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="outlet-muted-text">
                                        {{ $row->latitude ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="outlet-muted-text">
                                        {{ $row->longitude ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    @if(!empty($row->maps_url))
                                        <a href="{{ $row->maps_url }}" target="_blank" class="outlet-map-btn">
                                            <i class="bi bi-map"></i>
                                            Maps
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <div class="outlet-note">
                                        {{ $row->catatan ?? '-' }}
                                    </div>
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
        $('#outletTable').DataTable({
            ordering: false,
            autoWidth: false,
            responsive: true,
            language: {
                emptyTable: 'Belum ada data outlet pengamatan',
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

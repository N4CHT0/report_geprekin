@section('title', 'Master Titik Lokasi')
@section('breadcrumb', 'Surveyor / Master Titik Lokasi')

@include('Surveyor.layouts.header')

<style>
    :root {
        --page-bg: #f4f7fb;
        --card-bg: #ffffff;
        --text-main: #111827;
        --text-muted: #64748b;
        --border: #e5e7eb;
        --primary: #2563eb;
        --primary-soft: #eff6ff;
        --green: #16a34a;
        --yellow: #ca8a04;
        --red: #dc2626;
        --shadow: 0 12px 32px rgba(15, 23, 42, .07);
    }

    .master-location-page {
        min-height: calc(100vh - 70px);
        padding: 22px 26px 32px;
        background: var(--page-bg);
        color: var(--text-main);
    }

    .master-location-shell {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }

    .master-location-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 16px;
        padding: 18px 20px;
        border: 1px solid var(--border);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .master-location-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        margin-bottom: 8px;
        border-radius: 999px;
        background: var(--primary-soft);
        color: var(--primary);
        font-size: 12px;
        font-weight: 800;
    }

    .master-location-hero h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: -.03em;
    }

    .master-location-hero p {
        margin: 7px 0 0;
        max-width: 760px;
        color: var(--text-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .master-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 10px 16px;
        border: 0;
        border-radius: 14px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .master-btn-primary {
        background: var(--primary);
        color: #fff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, .24);
    }

    .master-btn-primary:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .master-alert {
        margin-bottom: 14px;
        border: 0;
        border-radius: 16px;
        font-weight: 800;
    }

    .master-card {
        border: 1px solid var(--border);
        border-radius: 22px;
        background: var(--card-bg);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .master-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 15px 18px;
        border-bottom: 1px solid var(--border);
        background: #fff;
    }

    .master-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 17px;
        font-weight: 900;
    }

    .master-card-title i {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--primary-soft);
        color: var(--primary);
    }

    .master-table-wrap {
        padding: 0;
    }

    .master-table {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    .master-table thead th {
        padding: 13px 16px !important;
        border-bottom: 1px solid var(--border) !important;
        background: #f8fafc;
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .master-table tbody td {
        padding: 14px 16px !important;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        color: #1f2937;
        font-size: 14px;
    }

    .master-table tbody tr:hover td {
        background: #f8fafc;
    }

    .master-code {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #0f172a;
        font-size: 12px;
        font-weight: 900;
    }

    .master-location-name {
        margin-bottom: 3px;
        font-weight: 900;
        color: #111827;
    }

    .master-location-address {
        max-width: 420px;
        color: var(--text-muted);
        font-size: 12px;
        line-height: 1.4;
    }

    .master-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 72px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
        white-space: nowrap;
    }

    .pill-high { background: #fee2e2; color: var(--red); }
    .pill-medium { background: #fef9c3; color: var(--yellow); }
    .pill-low { background: #dcfce7; color: var(--green); }
    .pill-new { background: var(--primary-soft); color: var(--primary); }
    .pill-assigned { background: #dcfce7; color: var(--green); }
    .pill-default { background: #f1f5f9; color: #475569; }

    .master-mini-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 7px 12px;
        border: 1px solid var(--border);
        border-radius: 999px;
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .master-mini-btn-primary {
        border-color: rgba(37, 99, 235, .22);
        background: var(--primary-soft);
        color: var(--primary);
    }

    .master-mini-btn:hover {
        text-decoration: none;
        filter: brightness(.98);
    }

    #candidateTable_wrapper {
        padding: 0;
    }

    #candidateTable_wrapper .row:first-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 !important;
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        background: #fff;
    }

    #candidateTable_wrapper .row:first-child > div {
        width: auto;
        padding: 0 !important;
        flex: 0 0 auto;
    }

    #candidateTable_wrapper .row:nth-child(2) {
        margin: 0 !important;
    }

    #candidateTable_wrapper .row:nth-child(2) > div {
        padding: 0 !important;
    }

    #candidateTable_wrapper .row:last-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 !important;
        padding: 12px 16px;
        border-top: 1px solid var(--border);
        background: #fff;
    }

    #candidateTable_wrapper .row:last-child > div {
        width: auto;
        padding: 0 !important;
        flex: 0 0 auto;
    }

    #candidateTable_length label,
    #candidateTable_filter label,
    #candidateTable_info {
        margin: 0;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 700;
    }

    #candidateTable_length select,
    #candidateTable_filter input {
        height: 36px;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 6px 10px;
        margin: 0 6px;
        outline: none;
        box-shadow: none;
    }

    #candidateTable_filter input {
        min-width: 260px;
        margin-right: 0;
    }

    #candidateTable_paginate .pagination {
        margin: 0;
        gap: 6px;
    }

    #candidateTable_paginate .page-link {
        border-radius: 10px;
        border: 1px solid var(--border);
        color: #475569;
        font-size: 13px;
        font-weight: 800;
        box-shadow: none;
    }

    #candidateTable_paginate .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
    }

    table.dataTable > tbody > tr.child ul.dtr-details {
        width: 100%;
    }

    .dataTables_empty {
        padding: 28px 16px !important;
        color: var(--text-muted) !important;
        font-weight: 800;
    }

    @media (max-width: 900px) {
        .master-location-page {
            padding: 14px;
        }

        .master-location-hero {
            align-items: stretch;
            flex-direction: column;
            padding: 16px;
        }

        .master-location-hero h1 {
            font-size: 24px;
        }

        .master-btn {
            width: 100%;
        }

        #candidateTable_wrapper .row:first-child,
        #candidateTable_wrapper .row:last-child {
            align-items: stretch;
            flex-direction: column;
        }

        #candidateTable_wrapper .row:first-child > div,
        #candidateTable_wrapper .row:last-child > div {
            width: 100%;
        }

        #candidateTable_filter input {
            width: 100%;
            min-width: 0;
            margin: 8px 0 0;
        }
    }
</style>

<div class="master-location-page">
    <div class="master-location-shell">

        <div class="master-location-hero">
            <div>
                <div class="master-location-eyebrow">
                    <i class="bi bi-pin-map"></i>
                    Admin Candidate Location
                </div>

                <h1>Master Titik Lokasi Survey</h1>

                <p>
                    Kelola kandidat titik lokasi sebelum ditugaskan ke surveyor.
                    Data ini menjadi acuan laporan Telegram, video survey, dan proses scoring lokasi.
                </p>
            </div>

            <a href="{{ route('investor.surveyor.candidate.create') }}" class="master-btn master-btn-primary">
                <i class="bi bi-plus-circle"></i>
                Tambah Titik
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success master-alert">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="master-card">
            <div class="master-card-header">
                <h2 class="master-card-title">
                    <i class="bi bi-list-check"></i>
                    Daftar Kandidat Lokasi
                </h2>
            </div>

            <div class="table-responsive master-table-wrap">
                <table class="table master-table align-middle" id="candidateTable">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Kode</th>
                            <th class="text-nowrap">Nama Lokasi</th>
                            <th class="text-nowrap">Kota</th>
                            <th class="text-nowrap">Priority</th>
                            <th class="text-nowrap">Status</th>
                            <th class="text-nowrap">Surveyor</th>
                            <th class="text-nowrap">Maps</th>
                            <th class="text-nowrap">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($locations ?? [] as $row)
                            @php
                                $priority = strtoupper($row->priority ?? 'MEDIUM');
                                $status = strtoupper($row->status ?? 'NEW');

                                $priorityClass = match($priority) {
                                    'HIGH' => 'pill-high',
                                    'LOW' => 'pill-low',
                                    default => 'pill-medium',
                                };

                                $statusClass = match($status) {
                                    'ASSIGNED' => 'pill-assigned',
                                    'NEW' => 'pill-new',
                                    default => 'pill-default',
                                };
                            @endphp

                            <tr>
                                <td class="text-nowrap">
                                    <span class="master-code">{{ $row->kode_lokasi }}</span>
                                </td>

                                <td style="min-width: 200px;">
                                    <div class="master-location-name">{{ $row->nama_lokasi }}</div>
                                    <div class="master-location-address">
                                        {{ $row->alamat ?: 'Alamat belum diisi' }}
                                    </div>
                                </td>

                                <td class="text-nowrap">{{ $row->kota ?? '-' }}</td>

                                <td class="text-nowrap">
                                    <span class="master-pill {{ $priorityClass }}">{{ $priority }}</span>
                                </td>

                                <td class="text-nowrap">
                                    <span class="master-pill {{ $statusClass }}">{{ $status }}</span>
                                </td>

                                <td class="text-nowrap">{{ $row->assigned_surveyor ?? '-' }}</td>

                                <td class="text-nowrap">
                                    @if($row->maps_url)
                                        <a href="{{ $row->maps_url }}" target="_blank" class="master-mini-btn">
                                            <i class="bi bi-map"></i>
                                            Open
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    <form method="POST" action="{{ route('investor.surveyor.candidate.assigned', $row->id) }}">
                                        @csrf

                                        <div class="d-flex gap-2">
                                            @if($row->status != 'SURVEYED')
                                                <a href="{{ route('investor.surveyor.site-score.create', ['candidate_id' => $row->id]) }}" class="master-mini-btn master-mini-btn-primary" style="background: #3b82f6; color: white;">
                                                    <i class="bi bi-play-fill"></i>
                                                    Mulai Survey
                                                </a>
                                            @endif
                                            <button type="submit" class="master-mini-btn master-mini-btn-primary">
                                                <i class="bi bi-person-check"></i>
                                                Mark Assigned
                                            </button>
                                        </div>
                                    </form>
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
        $('#candidateTable').DataTable({
            ordering: false,
            autoWidth: false,
            language: {
                emptyTable: 'Belum ada titik kandidat lokasi',
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

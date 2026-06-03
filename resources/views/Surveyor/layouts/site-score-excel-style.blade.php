{{-- resources/views/Surveyor/layouts/site-score-excel-style.blade.php --}}
<style>
    :root {
        --xl-border: #d7deea;
        --xl-border-dark: #b8c2d3;
        --xl-blue: #2563eb;
        --xl-blue-soft: #eff6ff;
        --xl-green: #16a34a;
        --xl-green-soft: #dcfce7;
        --xl-yellow: #f59e0b;
        --xl-yellow-soft: #fef3c7;
        --xl-red: #dc2626;
        --xl-red-soft: #fee2e2;
        --xl-head: #eaf1ff;
        --xl-subhead: #f8fafc;
        --xl-text: #0f172a;
        --xl-muted: #64748b;
    }

    .worksheet-page {
        display: grid;
        gap: 18px;
    }

    .worksheet-hero {
        background: #fff;
        border: 1px solid var(--xl-border);
        border-radius: 18px;
        padding: 22px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
    }

    .worksheet-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #bfdbfe;
        background: var(--xl-blue-soft);
        color: #1d4ed8;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 10px;
    }

    .worksheet-hero h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -.045em;
        color: var(--xl-text);
    }

    .worksheet-hero p {
        margin: 8px 0 0;
        color: var(--xl-muted);
        font-size: 13px;
        font-weight: 650;
        max-width: 860px;
    }

    .worksheet-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .worksheet-card {
        background: #fff;
        border: 1px solid var(--xl-border);
        border-radius: 18px;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
        overflow: hidden;
    }

    .worksheet-card-header {
        padding: 16px 18px;
        background: linear-gradient(180deg, #fff, #f8fafc);
        border-bottom: 1px solid var(--xl-border);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .worksheet-card-header h5 {
        margin: 0;
        font-size: 15px;
        font-weight: 950;
        color: var(--xl-text);
    }

    .worksheet-card-header p {
        margin: 4px 0 0;
        color: var(--xl-muted);
        font-size: 12px;
        font-weight: 650;
    }

    .worksheet-card-body {
        padding: 18px;
    }

    .worksheet-kpi-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 12px;
    }

    .worksheet-kpi {
        background: #fff;
        border: 1px solid var(--xl-border);
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 8px 20px rgba(15,23,42,.035);
    }

    .worksheet-kpi span {
        display: block;
        color: var(--xl-muted);
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .worksheet-kpi strong {
        display: block;
        margin-top: 9px;
        color: var(--xl-text);
        font-size: 27px;
        line-height: 1;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .worksheet-kpi small {
        display: block;
        margin-top: 9px;
        color: var(--xl-muted);
        font-size: 11px;
        font-weight: 650;
    }

    .worksheet-table-wrap {
        border: 1px solid var(--xl-border);
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    .worksheet-table {
        width: 100%;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    .worksheet-table thead th {
        background: var(--xl-head) !important;
        border: 1px solid var(--xl-border-dark) !important;
        color: #1e293b;
        padding: 12px 10px !important;
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
        vertical-align: middle;
    }

    .worksheet-table tbody td {
        border: 1px solid var(--xl-border) !important;
        padding: 12px 10px !important;
        font-size: 13px;
        color: #0f172a;
        vertical-align: middle;
        background: #fff;
    }

    .worksheet-table tbody tr:nth-child(even) td {
        background: #fbfdff;
    }

    .worksheet-table tbody tr:hover td {
        background: #f8fbff;
    }

    .excel-table {
        width: 100%;
        border-collapse: collapse !important;
    }

    .excel-table thead th {
        background: var(--xl-head) !important;
        border: 1px solid var(--xl-border-dark) !important;
        color: #1e293b;
        padding: 12px 10px !important;
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .035em;
        white-space: nowrap;
    }

    .excel-table tbody td {
        border: 1px solid var(--xl-border) !important;
        padding: 12px 10px !important;
        font-size: 13px;
        vertical-align: middle;
    }

    .status-pill,
    .excel-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 30px;
        border-radius: 999px;
        padding: 0 11px;
        font-size: 11px;
        font-weight: 950;
        white-space: nowrap;
    }

    .status-approved,
    .pill-approved {
        background: var(--xl-green-soft);
        color: #166534;
    }

    .status-consideration,
    .pill-consideration {
        background: var(--xl-yellow-soft);
        color: #92400e;
    }

    .status-rejected,
    .pill-rejected {
        background: var(--xl-red-soft);
        color: #991b1b;
    }

    .score-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        height: 34px;
        border-radius: 10px;
        background: #eef2ff;
        color: #1d4ed8;
        font-weight: 950;
    }

    .btn-worksheet,
    .btn-excel-primary {
        border: 1px solid var(--xl-blue);
        background: var(--xl-blue);
        color: #fff;
        min-height: 38px;
        border-radius: 11px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
    }

    .btn-worksheet:hover,
    .btn-excel-primary:hover {
        background: #1d4ed8;
        color: #fff;
    }

    .btn-worksheet-light,
    .btn-excel-light {
        border: 1px solid var(--xl-border-dark);
        background: #fff;
        color: var(--xl-text);
        min-height: 38px;
        border-radius: 11px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
    }

    .btn-worksheet-light:hover,
    .btn-excel-light:hover {
        background: #f8fafc;
        color: var(--xl-blue);
    }

    .worksheet-map-box {
        min-height: 370px;
        border: 1px solid var(--xl-border);
        border-radius: 18px;
        background:
            linear-gradient(135deg, rgba(37,99,235,.08), rgba(22,163,74,.06)),
            #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--xl-muted);
        font-weight: 800;
        text-align: center;
        padding: 20px;
    }

    .excel-map {
        width: 100%;
        min-height: 420px;
        border-radius: 16px;
        border: 1px solid var(--xl-border);
        overflow: hidden;
    }

    .form-control,
    .form-select {
        border-radius: 11px;
        border-color: #cbd5e1;
        min-height: 42px;
        font-weight: 650;
        font-size: 13px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--xl-blue);
        box-shadow: 0 0 0 4px rgba(37,99,235,.10);
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 10px;
        border: 1px solid var(--xl-border-dark);
        min-height: 36px;
        padding: 4px 10px;
    }

    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_info {
        color: var(--xl-muted);
        font-size: 13px;
        font-weight: 650;
        padding-top: 14px !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 14px !important;
    }

    .pagination .page-link {
        border-color: var(--xl-border);
        color: var(--xl-blue);
        font-weight: 800;
    }

    .pagination .active .page-link {
        background: var(--xl-blue);
        border-color: var(--xl-blue);
    }

    @media (max-width: 1200px) {
        .worksheet-kpi-grid {
            grid-template-columns: repeat(3, minmax(0,1fr));
        }
    }

    @media (max-width: 768px) {
        .worksheet-hero {
            flex-direction: column;
        }

        .worksheet-actions {
            justify-content: flex-start;
        }

        .worksheet-kpi-grid {
            grid-template-columns: repeat(1, minmax(0,1fr));
        }
    }
</style>

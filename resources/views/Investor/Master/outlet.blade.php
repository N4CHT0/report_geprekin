@section('title', 'Data Mitra Investor & Outlet')
@section('breadcrumb', 'Investor Management / Outlet')

@include('Temp.Investor.header')


<style>
  :root {
    --page-bg: #f4f6f9;
    --card-bg: #ffffff;
    --border: rgba(15, 23, 42, .12);
    --border-strong: rgba(15, 23, 42, .18);
    --text: #0f172a;
    --muted: #64748b;
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --success: #059669;
    --danger: #dc2626;
    --warning: #d97706;
    --soft-primary: #eff6ff;
    --soft-success: #ecfdf5;
    --soft-danger: #fef2f2;
    --soft-warning: #fffbeb;
    --radius: 12px;
  }

  .investor-outlet-page {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .page-topline {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--border);
    padding-bottom: 14px;
  }

  .page-pretitle {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 4px;
  }

  .page-title {
    margin: 0;
    font-size: 22px;
    font-weight: 850;
    line-height: 1.2;
    color: var(--text);
  }

  .page-subtitle,
  .form-text,
  .pagination-note {
    color: var(--muted);
    font-size: 13px;
  }

  .aws-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    overflow: hidden;
  }

  .tabler-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    overflow: hidden;
  }

  .tabler-card-header {
    padding: 16px;
    border-bottom: 1px solid var(--border);
    background: #fff;
  }

  .tabler-card-body {
    padding: 16px;
  }

  .toolbar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
  }

  .toolbar-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
  }

  .table-toolbar,
  .pagination-wrap {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .table-meta,
  .action-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
  }

  .btn {
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
    min-height: 36px;
  }

  .btn-sm {
    padding: 6px 10px;
  }

  .btn-primary {
    background: var(--primary);
    border-color: var(--primary);
  }

  .btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
  }

  .btn-soft-primary,
  .btn-soft-success,
  .btn-soft-secondary {
    border: 1px solid var(--border);
    background: #fff;
    color: #334155;
  }

  .btn-soft-primary:hover {
    background: var(--soft-primary);
    color: var(--primary);
    border-color: #bfdbfe;
  }

  .btn-soft-success:hover {
    background: var(--soft-success);
    color: var(--success);
    border-color: #bbf7d0;
  }

  .btn-soft-secondary:hover {
    background: #f8fafc;
    color: var(--text);
  }

  .stat-alert,
  .sync-status-card {
    border: 1px solid #bfdbfe;
    background: var(--soft-primary);
    color: #1e40af;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 14px;
  }

  .sync-status-card .title {
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 8px;
  }

  .sync-status-card .meta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px 12px;
  }

  .badge-soft {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 999px;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 800;
    border: 1px solid transparent;
  }

  .badge-soft-primary {
    background: var(--soft-primary);
    color: var(--primary);
    border-color: #bfdbfe;
  }

  .badge-soft-success {
    background: var(--soft-success);
    color: var(--success);
    border-color: #bbf7d0;
  }

  .badge-soft-secondary {
    background: #f8fafc;
    color: #475569;
    border-color: var(--border);
  }

  .badge-soft-danger {
    background: var(--soft-danger);
    color: var(--danger);
    border-color: #fecaca;
  }

  .table-responsive {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: auto;
    background: #fff;
  }

  table.dataTable {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }

  .table {
    margin-bottom: 0;
    width: 100% !important;
  }

  .table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
    padding: 11px 12px;
    border-bottom: 1px solid var(--border);
  }

  .table tbody td {
    padding: 11px 12px;
    vertical-align: middle;
    border-color: rgba(15, 23, 42, .06);
  }

  .table tbody tr:hover td {
    background: #f8fafc;
  }

  .text-wrap-cell {
    white-space: normal;
    min-width: 220px;
    max-width: 380px;
  }

  .status-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 5px 9px;
    font-size: 12px;
    font-weight: 800;
    text-transform: capitalize;
    white-space: nowrap;
  }

  .status-existing {
    background: var(--soft-success);
    color: var(--success);
  }

  .status-go {
    background: var(--soft-primary);
    color: var(--primary);
  }

  .status-tutup {
    background: var(--soft-danger);
    color: var(--danger);
  }

  .modal-dialog {
    margin: .75rem auto;
  }

  .modal-content {
    border: 0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 24px 80px rgba(15, 23, 42, .18);
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 14px 16px;
  }

  .modal-header,
  .modal-footer {
    border-color: var(--border);
  }

  .modal-dialog.modal-xs { max-width: 380px; }
  .modal-dialog.modal-sm { max-width: 520px; }
  .modal-dialog.modal-md { max-width: 720px; }
  .modal-dialog.modal-lg-custom { max-width: 920px; }
  .modal-dialog.modal-xl-custom { max-width: 1080px; }
  .modal-dialog.modal-xxl-custom { max-width: 1240px; }
  .modal-dialog.modal-fluid { width: min(96vw, 1320px); max-width: none; }
  .modal-content.modal-fit-screen { max-height: calc(100vh - 1.5rem); }
  .modal-content.modal-fit-screen .modal-body { overflow-y: auto; }

  .form-label {
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
    font-size: 13px;
  }

  .form-control,
  .form-select {
    min-height: 40px;
    border-radius: 8px;
    border-color: #cbd5e1;
    font-size: 13.5px;
  }

  textarea.form-control {
    min-height: auto;
  }

  .form-control:focus,
  .form-select:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 .15rem rgba(37, 99, 235, .12);
  }

  .select2-container {
    width: 100% !important;
  }

  .select2-container--open {
    z-index: 2000;
  }

  .select2-dropdown {
    z-index: 2001;
    border-color: #cbd5e1 !important;
    border-radius: 10px !important;
    overflow: hidden;
    box-shadow: 0 16px 42px rgba(15, 23, 42, .14);
  }

  .select2-container--default .select2-selection--single,
  .select2-container--default .select2-selection--multiple {
    min-height: 40px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    padding-left: 10px;
    color: var(--text);
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 38px;
  }

  .select2-container--default .select2-selection--multiple {
    padding: 3px 6px;
  }

  .result-box,
  .debug-box {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: #f8fafc;
  }

  .result-box {
    max-height: 320px;
    overflow-y: auto;
    padding: 10px;
  }

  .debug-box {
    margin-top: 8px;
    padding: 8px;
    font-size: 12px;
    color: #64748b;
    white-space: pre-wrap;
    word-break: break-word;
    overflow-wrap: anywhere;
  }

  .debug-box:empty {
    display: none;
  }

  .progress {
    height: 18px;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
  }

  .progress-bar {
    font-size: 11px;
    font-weight: 800;
  }

  .is-hidden-initial {
    display: none !important;
  }

  .dataTables_wrapper {
    width: 100%;
  }

  .dt-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
    margin: 0 0 12px;
  }

  .dataTables_length,
  .dataTables_filter {
    margin: 0 !important;
  }

  .dataTables_length label,
  .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    color: var(--muted);
    font-size: 13px;
    font-weight: 650;
  }

  .dataTables_length select,
  .dataTables_filter input {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 6px 8px;
    background: #fff;
    color: var(--text);
  }

  .dataTables_filter input {
    width: 220px;
  }

  .dataTables_info {
    font-size: 13px;
    color: var(--muted);
    padding-top: 14px !important;
  }

  .dataTables_paginate {
    padding-top: 10px !important;
  }

  .dataTables_wrapper .pagination {
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 4px;
    margin: 0;
  }

  .pagination .page-link,
  .dataTables_wrapper .pagination .page-link {
    border: 1px solid var(--border) !important;
    background: #fff !important;
    color: #334155 !important;
    border-radius: 8px !important;
    min-width: 34px;
    height: 34px;
    padding: 6px 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: none !important;
    font-size: 13px;
    font-weight: 700;
  }

  .pagination .page-item.active .page-link,
  .dataTables_wrapper .pagination .page-item.active .page-link {
    background: var(--primary) !important;
    border-color: var(--primary) !important;
    color: #fff !important;
  }

  .pagination .page-item.disabled .page-link,
  .dataTables_wrapper .pagination .page-item.disabled .page-link {
    background: #f8fafc !important;
    color: #94a3b8 !important;
  }

  .pagination .page-link:hover,
  .dataTables_wrapper .pagination .page-link:hover {
    background: #f8fafc !important;
    color: var(--text) !important;
  }

  @media (max-width: 991.98px) {
    .page-topline {
      align-items: flex-start;
      flex-direction: column;
    }

    .toolbar-actions {
      width: 100%;
      justify-content: flex-start;
    }

    .toolbar-actions .btn {
      flex: 1 1 calc(50% - 8px);
    }

    .modal-dialog.modal-lg-custom,
    .modal-dialog.modal-xl-custom,
    .modal-dialog.modal-xxl-custom,
    .modal-dialog.modal-fluid {
      width: calc(100vw - 1rem);
      max-width: none;
    }
  }

  @media (max-width: 767.98px) {
    .dataTables_length label,
    .dataTables_filter label {
      width: 100%;
      align-items: flex-start;
      flex-direction: column;
    }

    .dataTables_filter input {
      width: 100%;
      margin-left: 0 !important;
    }

    .dt-toolbar {
      align-items: stretch;
      flex-direction: column;
    }

    .dataTables_info,
    .dataTables_paginate {
      text-align: left !important;
    }

    .dataTables_wrapper .pagination {
      justify-content: flex-start;
    }
  }

  @media (max-width: 575.98px) {
    .page-title {
      font-size: 18px;
    }

    .tabler-card-header,
    .tabler-card-body,
    .modal-header,
    .modal-body,
    .modal-footer {
      padding: 12px;
    }

    .toolbar-actions .btn {
      flex: 1 1 100%;
    }

    .table thead th,
    .table tbody td {
      padding: 9px 10px;
    }

    .text-wrap-cell {
      min-width: 180px;
    }

    .sync-status-card .meta {
      grid-template-columns: 1fr;
    }

    .result-box {
      max-height: 240px;
    }
  }

  /* Sync Sales Per Credential History */
  .sync-history-toolbar {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 10px;
  }

  .sync-history-search {
    width: min(100%, 280px);
    min-height: 38px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 13px;
    font-weight: 650;
    outline: none;
  }

  .sync-history-search:focus {
    border-color: #93c5fd;
    box-shadow: 0 0 0 .15rem rgba(37, 99, 235, .12);
  }

  .sync-history-wrap {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: #fff;
    overflow: auto;
    max-height: 360px;
  }

  .sync-history-table {
    width: 100%;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
  }

  .sync-history-table th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: .04em;
    white-space: nowrap;
    padding: 10px 12px;
    border-bottom: 1px solid var(--border);
  }

  .sync-history-table td {
    padding: 10px 12px;
    border-bottom: 1px solid rgba(15, 23, 42, .06);
    color: var(--text);
    font-size: 13px;
    vertical-align: top;
  }

  .sync-history-table tr:hover td {
    background: #f8fafc;
  }

  .sync-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border-radius: 999px;
    padding: 5px 8px;
    font-size: 11px;
    font-weight: 850;
    text-transform: uppercase;
    white-space: nowrap;
  }

  .sync-status-pill.queued,
  .sync-status-pill.processing {
    background: var(--soft-primary);
    color: var(--primary);
  }

  .sync-status-pill.done,
  .sync-status-pill.success {
    background: var(--soft-success);
    color: var(--success);
  }

  .sync-status-pill.failed,
  .sync-status-pill.error {
    background: var(--soft-danger);
    color: var(--danger);
  }

  .sync-history-empty {
    padding: 24px;
    text-align: center;
    color: var(--muted);
    font-weight: 750;
  }

  .sync-history-mobile {
    display: none;
  }

  .sync-history-card {
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 12px;
    background: #fff;
  }

  .sync-history-card + .sync-history-card {
    margin-top: 10px;
  }

  .sync-history-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 8px;
  }

  .sync-history-card-title {
    font-size: 13px;
    font-weight: 850;
    color: var(--text);
  }

  .sync-history-card-sub {
    margin-top: 3px;
    color: var(--muted);
    font-size: 12px;
    font-weight: 650;
  }

  .sync-history-card-body {
    color: #334155;
    font-size: 12px;
    line-height: 1.45;
    font-weight: 650;
    word-break: break-word;
  }

  @media (max-width: 767.98px) {
    .sync-history-toolbar {
      align-items: stretch;
      flex-direction: column;
    }

    .sync-history-search {
      width: 100%;
    }

    .sync-history-table {
      display: none;
    }

    .sync-history-mobile {
      display: block;
      padding: 10px;
    }

    .sync-history-wrap {
      max-height: 420px;
    }
  }

</style>

<div class="investor-outlet-page">
  <div class="page-topline">
    <div>
      <div class="page-pretitle">Investor Management</div>
      <h1 class="page-title">Data Mitra Investor & Outlet</h1>
      <div class="page-subtitle">Kelola master outlet, import sales, sinkronisasi ESB, dan status mitra.</div>
    </div>
    <div class="table-meta">
      <span class="badge-soft badge-soft-primary">
        Total: {{ method_exists($data, 'total') ? $data->total() : count($data) }}
      </span>
      <span class="badge-soft badge-soft-secondary">
        Per halaman: {{ method_exists($data, 'perPage') ? $data->perPage() : count($data) }}
      </span>
    </div>
  </div>

      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if (session('duplicate'))
        <div class="alert alert-warning">{{ session('duplicate') }}</div>
      @endif

      @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <div class="fw-bold mb-2">Validasi gagal</div>
          <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="tabler-card">
        <div class="tabler-card-header">
          <div class="toolbar">
            <div>
              <div class="page-pretitle">Actions</div>
              <div class="page-subtitle">Tambah, import, dan sinkronisasi data outlet.</div>
            </div>

            <div class="toolbar-actions">
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle me-1"></i>Tambah Outlet
              </button>

              <button type="button" class="btn btn-soft-primary btn-sm is-hidden-initial" data-bs-toggle="modal" data-bs-target="#modalPreviewSales">
                <i class="bi bi-search me-1"></i>Preview Import Sales
              </button>

              <button type="button" class="btn btn-soft-success btn-sm is-show-initial" data-bs-toggle="modal" data-bs-target="#modalImportSales">
                <i class="bi bi-upload me-1"></i>Import Sales
              </button>

              <button type="button" class="btn btn-soft-success btn-sm is-hidden-initial" data-bs-toggle="modal" data-bs-target="#modalImportOutlet">
                <i class="bi bi-upload me-1"></i>Import Outlet
              </button>

              <button type="button" class="btn btn-soft-primary btn-sm" id="btnSyncEsbOutlets">
                <i class="bi bi-arrow-repeat me-1"></i>Sync Outlet dari ESB
              </button>

              <button type="button" class="btn btn-soft-primary btn-sm" id="btnSyncEsbAllOutlets">
                <i class="bi bi-arrow-repeat me-1"></i>Sync Outlet ESB All Credential
              </button>

              <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSyncSalesEsbAllBranch">
                <i class="bi bi-receipt me-1"></i>Sync Sales ESB Per Credential
              </button>

              <button type="button" class="btn btn-soft-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSyncSalesSelected">
                <i class="bi bi-calendar-range me-1"></i>Sync Pilihan
              </button>

              <!-- <button type="button" class="btn btn-soft-primary btn-sm">
                <i class="bi bi-arrow-repeat me-1"></i>COBA SYNC SALES
              </button> -->

              <a href="{{ route('outlet.template.download') }}" class="btn btn-soft-secondary btn-sm">
                <i class="bi bi-download me-1"></i>Template CSV
              </a>
            </div>
          </div>
        </div>

        <div class="tabler-card-body">
          <div class="stat-alert">
            File sales kecil diproses langsung. File besar masuk queue.
          </div>

          <div id="syncEsbOutletStatus"></div>
          <div id="syncEsbOutletDebug" class="debug-box"></div>

          <div id="syncEsbAllStatus"></div>
          <div id="syncEsbAllDebug" class="debug-box"></div>

          <div class="table-toolbar">
            <div class="table-meta">
              <span class="badge-soft badge-soft-primary">
                Total:
                {{ method_exists($data, 'total') ? $data->total() : count($data) }}
              </span>

              <span class="badge-soft badge-soft-secondary">
                Per halaman:
                {{ method_exists($data, 'perPage') ? $data->perPage() : count($data) }}
              </span>
            </div>
          </div>

          {{-- Status sync sales outlet pilihan --}}
          <div id="syncSalesSelectedStatus" class="mt-3"></div>
          <div id="syncSalesSelectedDebug" class="debug-box"></div>

          <div class="table-responsive">
            <table id="outletTable" class="table align-middle">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Kode Outlet</th>
                  <th>Area</th>
                  <th>Nama Mitra</th>
                  <th>Nama Outlet</th>
                  <th>Kota</th>
                  <th>Alamat</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($data as $index => $outlet)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $outlet->kode_outlet ?? '-' }}</td>
                    <td>{{ $outlet->area_id ?? '-' }}</td>
                    <td>{{ $outlet->nama_mitra ?? '-' }}</td>
                    <td class="fw-semibold">{{ $outlet->nama_outlet ?? '-' }}</td>
                    <td>{{ $outlet->kota ?? '-' }}</td>
                    <td class="text-wrap-cell">{{ $outlet->alamat ?? '-' }}</td>
                    <td>
                      <span class="status-pill status-{{ $outlet->status ?? 'existing' }}">
                        {{ ucfirst($outlet->status ?? '-') }}
                      </span>
                    </td>
                    <td>
                      <div class="action-group">
                        <button
                          type="button"
                          class="btn btn-outline-primary btn-sm btn-edit-outlet"
                          data-bs-toggle="modal"
                          data-bs-target="#editModal"
                          data-id="{{ $outlet->id }}"
                          data-kode="{{ $outlet->kode_outlet }}"
                          data-nama="{{ $outlet->nama_outlet }}"
                          data-kota="{{ $outlet->kota }}"
                          data-alamat="{{ $outlet->alamat }}"
                          data-mitra="{{ $outlet->mitra_id }}"
                          data-area="{{ $outlet->area_id }}"
                          data-status="{{ $outlet->status }}"
                        >
                          Ubah
                        </button>

                        <form action="{{ route('outlet.master.delete', $outlet->id) }}" method="POST" class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">
                            Hapus
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="9" class="text-center">Belum ada data outlet.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          @if (method_exists($data, 'links'))
            <div class="pagination-wrap">
              <div class="pagination-note">
                Menampilkan
                {{ $data->firstItem() ?? 0 }} - {{ $data->lastItem() ?? 0 }}
                dari {{ $data->total() }} data
              </div>
              <div>
                {{ $data->onEachSide(1)->links() }}
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- MODAL SYNC SALES OUTLET PILIHAN --}}
      <div class="modal fade" id="modalSyncSalesSelected" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-md modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Sync Sales Outlet Pilihan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Range Tanggal</label>
                <div class="row g-2">
                  <div class="col-6">
                    <input type="date" id="syncSalesStart" class="form-control" aria-label="Tanggal mulai">
                  </div>
                  <div class="col-6">
                    <input type="date" id="syncSalesEnd" class="form-control" aria-label="Tanggal akhir">
                  </div>
                </div>
                <div class="form-text">Isi tanggal mulai dan tanggal akhir.</div>
              </div>

              <div class="mb-0">
                <label class="form-label">Outlet</label>
                <select id="syncSalesOutletIds" class="form-select select2-sales-outlet" multiple data-placeholder="Pilih outlet">
                  @foreach ($outlets as $outlet)
                    <option value="{{ $outlet->id }}">
                      {{ $outlet->nama_outlet }}
                      — {{ $outlet->kota ?? '-' }}
                      — {{ $outlet->branch_code ?? '-' }}
                    </option>
                  @endforeach
                </select>
                <div class="form-text">Maksimal 5 outlet.</div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
              <button type="button" class="btn btn-primary" id="btnSyncSalesSelected">
                <i class="bi bi-receipt me-1"></i>Sync Pilihan
              </button>
            </div>
          </div>
        </div>
      </div>

      {{-- MODAL SYNC SALES ESB ALL BRANCH --}}
      <div class="modal fade" id="modalSyncSalesEsbAllBranch" tabindex="-1" aria-hidden="true" data-bs-focus="false">
        <div class="modal-dialog modal-xl-custom modal-dialog-centered">
          <div class="modal-content modal-fit-screen">
            <form id="formSyncSalesEsbAllBranch">
              <div class="modal-header">
                <div>
                  <h5 class="modal-title">Sync Sales ESB Per Credential</h5>
                  <div class="form-text">Trigger tarik data API dan pantau history sync terbaru.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-lg-5">
                    <div class="aws-card">
                      <div class="tabler-card-header">
                        <div class="page-pretitle">Trigger Sync</div>
                        <div class="page-subtitle">Pilih tanggal sales lalu kirim job ke worker.</div>
                      </div>

                      <div class="tabler-card-body">
                        <div class="mb-3">
                          <label class="form-label">Sales Date</label>
                          <input
                            type="date"
                            class="form-control"
                            name="sales_date"
                            id="sync_sales_esb_all_branch_date"
                            required
                          >
                        </div>

                        <div id="syncSalesEsbAllBranchStatus"></div>
                        <div id="syncSalesEsbAllBranchDebug" class="debug-box"></div>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-7">
                    <div class="aws-card">
                      <div class="tabler-card-header">
                        <div class="toolbar">
                          <div>
                            <div class="page-pretitle">History Tarik Data API</div>
                            <div class="page-subtitle">Riwayat sync sales per credential terbaru.</div>
                          </div>

                          <div class="toolbar-actions">
                            <button type="button" class="btn btn-soft-secondary btn-sm" id="btnRefreshSyncSalesAllBranchHistory">
                              <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                            </button>
                          </div>
                        </div>
                      </div>

                      <div class="tabler-card-body">
                        <div class="sync-history-toolbar">
                          <input
                            type="search"
                            class="sync-history-search"
                            id="syncSalesAllBranchHistorySearch"
                            placeholder="Cari tanggal, status, sync key..."
                          >
                          <span class="badge-soft badge-soft-secondary" id="syncSalesAllBranchHistoryCount">0 history</span>
                        </div>

                        <div class="sync-history-wrap">
                          <table class="sync-history-table">
                            <thead>
                              <tr>
                                <th>Sales Date</th>
                                <th>Status</th>
                                <th>Sync Key</th>
                                <th>Message</th>
                                <th>Created</th>
                                <th>Finished</th>
                              </tr>
                            </thead>
                            <tbody id="syncSalesAllBranchHistoryBody">
                              <tr>
                                <td colspan="6" class="sync-history-empty">Memuat history...</td>
                              </tr>
                            </tbody>
                          </table>

                          <div id="syncSalesAllBranchHistoryMobile" class="sync-history-mobile">
                            <div class="sync-history-empty">Memuat history...</div>
                          </div>
                        </div>

                        <div class="form-text mt-2">
                          History diambil dari endpoint status/history. Jika endpoint belum tersedia, bagian ini akan menampilkan history kosong.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnSyncSalesEsbAllBranchSubmit">
                  <i class="bi bi-receipt me-1"></i>Sync Sales Per Credential
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- MODAL TAMBAH --}}
      <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
          <form action="{{ route('outlet.master.store') }}" method="POST" class="modal-content" id="formAddOutlet">
            @csrf

            <div class="modal-header">
              <h5 class="modal-title">Tambah Outlet</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Area</label>
                <select class="form-select modal-select-add" name="area_id" data-placeholder="Pilih area">
                  <option value="">-- Kosongkan (NULL) --</option>
                  @foreach ($areas as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Kode Outlet</label>
                <input type="text" class="form-control" name="kode_outlet" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Mitra</label>
                <select class="form-select modal-select-add" name="mitra_id" data-placeholder="Pilih mitra">
                  <option value="">-- Kosongkan (NULL) --</option>
                  @foreach ($mitra as $m)
                    <option value="{{ $m->id }}">{{ $m->nama_mitra }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Nama Outlet</label>
                <input type="text" class="form-control" name="nama_outlet" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Kota</label>
                <input type="text" class="form-control" name="kota">
              </div>

              <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" name="alamat" rows="3"></textarea>
              </div>

              <div class="mb-0">
                <label class="form-label">Status</label>
                <select class="form-select modal-select-add" name="status" required>
                  <option value="existing">Existing</option>
                  <option value="go">Go</option>
                  <option value="tutup">Tutup</option>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary" id="btnAddSubmit">Simpan Outlet</button>
            </div>
          </form>
        </div>
      </div>

      {{-- MODAL EDIT --}}
      <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
          <form action="{{ route('outlet.master.update') }}" method="POST" class="modal-content" id="formEditOutlet">
            @csrf
            <input type="hidden" name="id" id="edit-id">

            <div class="modal-header">
              <h5 class="modal-title">Edit Outlet</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Area</label>
                <select class="form-select modal-select-edit" name="area_id" id="edit-area" data-placeholder="Pilih area">
                  <option value="">-- Kosongkan (NULL) --</option>
                  @foreach ($areas as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Mitra</label>
                <select class="form-select modal-select-edit" name="mitra_id" id="edit-mitra" data-placeholder="Pilih mitra">
                  <option value="">-- Kosongkan (NULL) --</option>
                  @foreach ($mitra as $m)
                    <option value="{{ $m->id }}">{{ $m->nama_mitra }}</option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Kode Outlet</label>
                <input type="text" class="form-control" name="kode_outlet" id="edit-kode" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Nama Outlet</label>
                <input type="text" class="form-control" name="nama_outlet" id="edit-nama" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Kota</label>
                <input type="text" class="form-control" name="kota" id="edit-kota">
              </div>

              <div class="mb-3">
                <label class="form-label">Alamat</label>
                <textarea class="form-control" name="alamat" id="edit-alamat" rows="3"></textarea>
              </div>

              <div class="mb-0">
                <label class="form-label">Status</label>
                <select class="form-select modal-select-edit" name="status" id="edit-status" required>
                  <option value="existing">Existing</option>
                  <option value="go">Go</option>
                  <option value="tutup">Tutup</option>
                </select>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary" id="btnEditSubmit">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      {{-- MODAL PREVIEW SALES --}}
      <div class="modal fade" id="modalPreviewSales" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <form id="previewSalesForm" method="POST" action="{{ route('dataSalesImport.preview') }}" enctype="multipart/form-data">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title">Preview Import Sales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label for="file_preview_sales" class="form-label">Pilih File Preview</label>
                  <input type="file" class="form-control" name="file" id="file_preview_sales" accept=".xlsx,.xls,.csv" required>
                  <div class="form-text">Maksimal 2 MB.</div>
                </div>

                <div id="previewStatusSales"></div>
                <div id="previewDebugSales" class="debug-box"></div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" id="btnPreviewSales">Preview</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- MODAL IMPORT SALES --}}
      <div class="modal fade" id="modalImportSales" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <form id="importSalesForm" method="POST" action="{{ route('dataSalesImport.import') }}" enctype="multipart/form-data">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title">Import Sales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label for="file_import_sales" class="form-label">Pilih File Import</label>
                  <input type="file" class="form-control" name="file" id="file_import_sales" accept=".xlsx,.xls,.csv" required>
                  <div class="form-text">Maksimal 100 MB.</div>
                </div>

                <div class="alert alert-info mb-3">File akan diproses via queue.</div>

                <div id="importStatusSales"></div>
                <div id="importDebugSales" class="debug-box"></div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success" id="btnImportSales">Import</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      {{-- MODAL IMPORT OUTLET --}}
      <div class="modal fade" id="modalImportOutlet" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <form id="importOutletForm" method="POST" action="#" enctype="multipart/form-data">
              @csrf
              <div class="modal-header">
                <h5 class="modal-title">Import Outlet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label for="file_import_outlet" class="form-label">Pilih File Import Outlet</label>
                  <input type="file" class="form-control" name="file" id="file_import_outlet" accept=".xlsx,.xls,.csv" required>
                </div>

                <div id="importStatusOutlet"></div>
                <div id="importDebugOutlet" class="debug-box"></div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success" id="btnImportOutlet">Import Outlet</button>
              </div>
            </form>
          </div>
        </div>
      </div>

</div>

@push('scripts')
<script>
$(document).ready(function () {
  function clearStuckOverlay() {
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
  }

  function safeHideModal(selector) {
    const modalEl = document.querySelector(selector);
    if (!modalEl || !window.bootstrap) {
      clearStuckOverlay();
      return;
    }

    const modalInstance = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl);
    modalEl.addEventListener('hidden.bs.modal', clearStuckOverlay, { once: true });
    modalInstance.hide();
    setTimeout(clearStuckOverlay, 350);
  }

  $('#modalSyncSalesSelected').on('hidden.bs.modal', clearStuckOverlay);
  $('#modalSyncSalesEsbAllBranch').on('hidden.bs.modal', clearStuckOverlay);

  function initSyncSalesOutletSelect() {
    const $select = $('#syncSalesOutletIds');
    if (!$select.length || !$.fn.select2) return;

    if ($select.hasClass('select2-hidden-accessible')) {
      $select.select2('destroy');
    }

    $select.select2({
      width: '100%',
      dropdownParent: $('#modalSyncSalesSelected'),
      placeholder: $select.data('placeholder') || 'Pilih outlet',
      allowClear: true,
      closeOnSelect: false
    });
  }

  initSyncSalesOutletSelect();
  $('#modalSyncSalesSelected').on('shown.bs.modal', initSyncSalesOutletSelect);

  let selectedSalesTimer = null;

  function escapeHtml(text) {
    return $('<div>').text(text ?? '').html();
  }

  function renderSelectedSalesStatus(data) {
    const progress = Number(data.progress ?? 0);
    const totalJobs = Number(data.total_jobs ?? 0);
    const dispatchedJobs = Number(data.dispatched_jobs ?? 0);

    let logsHtml = '';
    if (Array.isArray(data.logs) && data.logs.length) {
      logsHtml = `
        <div class="result-box mt-3">
          <div class="fw-bold mb-2">Log terbaru</div>
          <ul class="mb-0 ps-3">
            ${data.logs.map(log => `
              <li>
                <strong>${escapeHtml(log.outlet_name ?? '-')}</strong>
                | ${escapeHtml(log.branch_code ?? '-')}
                | ${escapeHtml(log.sales_date ?? '-')}
                | ${escapeHtml(log.status ?? '-')}
              </li>
            `).join('')}
          </ul>
        </div>
      `;
    }

    if (data.status === 'queued') {
      return `<div class="alert alert-info mb-0">${escapeHtml(data.message ?? 'Masuk antrian.')}</div>`;
    }

    if (data.status === 'processing') {
      return `
        <div class="sync-status-card">
          <div class="title">Sync sales outlet pilihan berjalan</div>
          <div class="meta">
            <div>Outlet: <strong>${escapeHtml(data.outlet_count ?? 0)}</strong></div>
            <div>Range: <strong>${escapeHtml(data.start_date ?? '-')} s/d ${escapeHtml(data.end_date ?? '-')}</strong></div>
            <div>Total job: <strong>${totalJobs}</strong></div>
            <div>Dikirim: <strong>${dispatchedJobs}</strong></div>
          </div>
        </div>
        <div class="progress mt-2">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:${progress}%">${progress}%</div>
        </div>
        ${logsHtml}
      `;
    }

    if (data.status === 'done') {
      return `
        <div class="alert alert-success">
          <div class="fw-bold">Dispatch selesai</div>
          <div>Total job: <strong>${totalJobs}</strong></div>
          <div>Dikirim: <strong>${dispatchedJobs}</strong></div>
        </div>
        ${logsHtml}
      `;
    }

    if (data.status === 'failed') {
      return `<div class="alert alert-danger">${escapeHtml(data.message ?? 'Sync gagal.')}</div>`;
    }

    return `<div class="alert alert-secondary">Status tidak dikenali.</div>`;
  }

  async function getJson(url) {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw data;
    return data;
  }

  function startSelectedSalesPolling(syncKey) {
    // Polling Sync Pilihan dimatikan agar Blade tidak bergantung ke route status.
    // Worker tetap berjalan setelah submit berhasil.
    $('#syncSalesSelectedDebug').html('<pre style="margin:0">' + escapeHtml(JSON.stringify({ sync_key: syncKey, polling: 'disabled' }, null, 2)) + '<\/pre>');
  }

  $('#btnSyncSalesSelected').on('click', async function () {
    const btn = $(this);
    const startDate = $('#syncSalesStart').val();
    const endDate = $('#syncSalesEnd').val();
    const outletIds = $('#syncSalesOutletIds').val() || [];

    if (!startDate || !endDate) {
      Swal.fire('Tanggal wajib diisi', 'Pilih tanggal mulai dan akhir.', 'warning');
      return;
    }

    if (outletIds.length < 1 || outletIds.length > 5) {
      Swal.fire('Outlet tidak valid', 'Pilih minimal 1 dan maksimal 5 outlet.', 'warning');
      return;
    }

    try {
      const response = await withPostPassword(async () => {
        btn.prop('disabled', true).html('Memproses...');

        return await fetch(`{{ route('outlet.sales.sync.selected') }}`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            start_date: startDate,
            end_date: endDate,
            outlet_ids: outletIds
          })
        });
      });

      if (!response) return;

      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw data;

      $('#syncSalesSelectedStatus').html(renderSelectedSalesStatus(data));
      safeHideModal('#modalSyncSalesSelected');

      if (data.sync_key) {
        startSelectedSalesPolling(data.sync_key);
      }
    } catch (err) {
      clearStuckOverlay();
      $('#syncSalesSelectedStatus').html(`
        <div class="alert alert-danger">
          ${escapeHtml(err.message ?? 'Gagal memulai sync sales outlet pilihan.')}
        </div>
      `);
    } finally {
      btn.prop('disabled', false).html('<i class="bi bi-receipt me-1"></i>Sync Pilihan');
    }
  });
});
</script>

<script>
  (function () {
    function escapeHtml(text) {
      return $('<div>').text(text ?? '').html();
    }

    function setHtml(id, html) {
      const el = document.getElementById(id);
      if (el) el.innerHTML = html;
    }

    function setText(id, text) {
      const el = document.getElementById(id);
      if (el) el.innerHTML = text;
    }

    function initSelect2() {
      $('.select2-basic').select2({
        width: '100%',
        allowClear: true
      });

      $('#addModal .select2-basic').each(function () {
        $(this).select2({
          width: '100%',
          dropdownParent: $('#addModal'),
          allowClear: true
        });
      });

      $('#editModal .modal-select-edit').each(function () {
        $(this).select2({
          width: '100%',
          dropdownParent: $('#editModal'),
          allowClear: true
        });
      });
    }

    function buildFailedRowsHtml(rows) {
      if (!rows || !rows.length) return '';

      const items = rows.map(row => `
        <li class="mb-1">
          <strong>Baris ${escapeHtml(row.row)}</strong> - ${escapeHtml(row.reason)}
          ${row.nama_outlet_excel ? `(<code>${escapeHtml(row.nama_outlet_excel)}</code>)` : ''}
        </li>
      `).join('');

      return `
        <div class="alert alert-warning mt-3">Ditemukan ${rows.length} baris bermasalah.</div>
        <div class="result-box">
          <ul class="mb-0 ps-3">${items}</ul>
        </div>
      `;
    }

    async function getJson(url) {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw { status: response.status, data };
      return data;
    }

    async function ajaxSubmitForm(formEl, debugId, statusId, submitBtnId, btnText, maxSizeMb, successRenderer) {
      const submitBtn = document.getElementById(submitBtnId);
      const statusBox = document.getElementById(statusId);
      const fileInput = formEl.querySelector('input[type="file"]');
      const file = fileInput?.files?.[0] ?? null;

      setText(debugId, '');
      statusBox.innerHTML = '';

      if (!file) {
        setHtml(statusId, '<div class="alert alert-danger mb-0">Pilih file dulu.</div>');
        return;
      }

      const ext = file.name.split('.').pop().toLowerCase();
      if (!['xlsx', 'xls', 'csv'].includes(ext)) {
        setHtml(statusId, '<div class="alert alert-danger mb-0">Format file harus xlsx, xls, atau csv.</div>');
        return;
      }

      if (maxSizeMb && file.size > (maxSizeMb * 1024 * 1024)) {
        setHtml(statusId, `<div class="alert alert-danger mb-0">Ukuran file maksimal ${maxSizeMb} MB.</div>`);
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerText = 'Memproses...';

      setHtml(statusId, `
        <div class="progress mt-2">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 45%">
            Sedang upload...
          </div>
        </div>
      `);

      const formData = new FormData(formEl);

      try {
          const response = await withPostPassword(async () => {
            return await fetch(formEl.action, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              },
              body: formData
            });
          });
          
          if (!response) {
            submitBtn.disabled = false;
            submitBtn.innerText = btnText;
            return;
          }

        const data = await response.json().catch(() => ({}));
        if (!response.ok) throw { status: response.status, data };

        setText(debugId, JSON.stringify(data, null, 2));
        setHtml(statusId, successRenderer(data));
        formEl.reset();
        return data;
      } catch (err) {
        let msg = 'Terjadi kesalahan saat memproses file.';
        if (err.data?.message) msg = err.data.message;
        else if (err.status === 419) msg = 'Session expired, refresh halaman.';
        else if (err.status === 422) msg = 'Validasi gagal.';
        else if (err.status === 500) msg = 'Terjadi error di server.';

        setText(debugId, msg);
        setHtml(statusId, `<div class="alert alert-danger mt-3 mb-0">${escapeHtml(msg)}</div>`);
        throw err;
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = btnText;
      }
    }

    function renderImportSalesStatus(data) {
      const failedRows = data.failedRows ?? [];
      const progress = Number(data.progress ?? 0);
      const totalRows = Number(data.totalRows ?? 0);
      const processedRows = Number(data.processedRows ?? 0);
      const insertedRows = Number(data.insertedRows ?? 0);
      const skippedRows = Number(data.skippedRows ?? 0);
      const failedCount = Number(data.failedCount ?? 0);

      if (data.status === 'queued') {
        return `
          <div class="alert alert-info mt-3 mb-0">
            <div class="fw-bold mb-1">Masuk antrian</div>
            <div>${escapeHtml(data.message ?? 'File sedang menunggu proses.')}</div>
          </div>
        `;
      }

      if (data.status === 'processing') {
        return `
          <div class="alert alert-info mt-3 mb-2">
            <div class="fw-bold mb-2">Sedang diproses</div>
            <div>Total: <strong>${totalRows}</strong></div>
            <div>Diproses: <strong>${processedRows}</strong></div>
            <div>Masuk DB: <strong>${insertedRows}</strong></div>
            <div>Skip: <strong>${skippedRows}</strong></div>
            <div>Gagal: <strong>${failedCount}</strong></div>
          </div>
          <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:${progress}%">
              ${progress}%
            </div>
          </div>
        `;
      }

      if (data.status === 'done') {
        return `
          <div class="alert alert-success mt-3">
            <div class="fw-bold mb-2">Import selesai</div>
            <div>Total baris: <strong>${totalRows}</strong></div>
            <div>Diproses: <strong>${processedRows}</strong></div>
            <div>Masuk DB: <strong>${insertedRows}</strong></div>
            <div>Skip: <strong>${skippedRows}</strong></div>
            <div>Gagal: <strong>${failedCount}</strong></div>
            <div>Total Item Sub Total: <strong>${escapeHtml(data.totalItemSubTotal ?? 0)}</strong></div>
          </div>
          ${failedRows.length ? buildFailedRowsHtml(failedRows) : ''}
        `;
      }

      if (data.status === 'failed') {
        return `
          <div class="alert alert-danger mt-3 mb-0">
            <div class="fw-bold mb-1">Import gagal</div>
            <div>${escapeHtml(data.message ?? 'Terjadi kesalahan saat import.')}</div>
          </div>
        `;
      }

      return '<div class="alert alert-secondary mt-3 mb-0">Status tidak dikenali.</div>';
    }

    function startImportSalesPolling(importKey) {
      const urlTemplate = `{{ route('dataSalesImport.status', ['key' => '__IMPORT_KEY__']) }}`;
      const url = urlTemplate.replace('__IMPORT_KEY__', importKey);

      const timer = setInterval(async function () {
        try {
          const data = await getJson(url);
          setText('importDebugSales', JSON.stringify(data, null, 2));
          setHtml('importStatusSales', renderImportSalesStatus(data));

          if (data.status === 'done' || data.status === 'failed') {
            clearInterval(timer);
          }
        } catch (err) {
          clearInterval(timer);
          setHtml('importStatusSales', '<div class="alert alert-danger mt-3 mb-0">Gagal cek status import.</div>');
        }
      }, 3000);

      return timer;
    }

    document.addEventListener('DOMContentLoaded', function () {
      initSelect2();

      let importSalesPollingTimer = null;

      document.querySelectorAll('.btn-edit-outlet').forEach(btn => {
        btn.addEventListener('click', function () {
          $('#edit-id').val(this.dataset.id || '');
          $('#edit-kode').val(this.dataset.kode || '');
          $('#edit-nama').val(this.dataset.nama || '');
          $('#edit-kota').val(this.dataset.kota || '');
          $('#edit-alamat').val(this.dataset.alamat || '');
          $('#edit-area').val(this.dataset.area || '').trigger('change');
          $('#edit-mitra').val(this.dataset.mitra || '').trigger('change');
          $('#edit-status').val(this.dataset.status || 'existing').trigger('change');
        });
      });

      const importSalesForm = document.getElementById('importSalesForm');
      if (importSalesForm) {
        importSalesForm.addEventListener('submit', async function (e) {
          e.preventDefault();

          try {
            const data = await ajaxSubmitForm(
              importSalesForm,
              'importDebugSales',
              'importStatusSales',
              'btnImportSales',
              'Import',
              100,
              (data) => `
                <div class="alert alert-info mt-3 mb-0">
                  <div class="fw-bold mb-1">Upload berhasil</div>
                  <div>${escapeHtml(data.message ?? 'File masuk antrian import.')}</div>
                </div>
              `
            );

            if (data?.import_key) {
              if (importSalesPollingTimer) clearInterval(importSalesPollingTimer);
              importSalesPollingTimer = startImportSalesPolling(data.import_key);
            }
          } catch (err) {
            console.error(err);
          }
        });
      }

      const importSalesModal = document.getElementById('modalImportSales');
      if (importSalesModal && importSalesForm) {
        importSalesModal.addEventListener('hidden.bs.modal', function () {
          importSalesForm.reset();
          setHtml('importStatusSales', '');
          setText('importDebugSales', '');

          if (importSalesPollingTimer) {
            clearInterval(importSalesPollingTimer);
            importSalesPollingTimer = null;
          }

          const btn = document.getElementById('btnImportSales');
          if (btn) {
            btn.disabled = false;
            btn.innerText = 'Import';
          }
        });
      }
    });
  })();
</script>

<script>
  $(function () {
    if (!$.fn.DataTable) return;

    if ($.fn.DataTable.isDataTable('#outletTable')) {
      $('#outletTable').DataTable().destroy(false);
    }
    
    $('#outletTable').DataTable({
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Semua']],
      ordering: true,
      responsive: false,
      autoWidth: false,
      paging: true,
      searching: true,
      info: true,
      dom:
        "<'dt-toolbar'<'dt-length'l><'dt-search'f>>" +
        "<'row'<'col-12'tr>>" +
        "<'row align-items-center mt-2'<'col-md-5'i><'col-md-7'p>>",
      language: {
        search: "Cari:",
        lengthMenu: "Tampilkan _MENU_ data",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
        zeroRecords: "Data tidak ditemukan",
        paginate: {
          first: "First",
          last: "Last",
          next: "›",
          previous: "‹"
        }
      },
      columnDefs: [
        { orderable: false, targets: [8] }
      ]
    });
  });
</script>

<script>
  (function () {
    let syncEsbPollingTimer = null;

    function renderSyncEsbStatus(data) {
      const progress = Number(data.progress ?? 0);
      const total = Number(data.total ?? 0);
      const processed = Number(data.processed ?? 0);
      const inserted = Number(data.inserted ?? 0);
      const matched = Number(data.matched ?? 0);
      const skipped = Number(data.skipped ?? 0);
      const failed = Number(data.failed ?? 0);

      if (data.status === 'queued') {
        return `
          <div class="alert alert-info mt-3 mb-0">
            <div class="fw-bold mb-1">Masuk antrian</div>
            <div>${data.message ?? 'Menunggu worker queue.'}</div>
          </div>
        `;
      }

      if (data.status === 'processing') {
        return `
          <div class="alert alert-info mt-3 mb-2">
            <div class="fw-bold mb-2">Sinkronisasi outlet sedang berjalan</div>
            <div>Total branch: <strong>${total}</strong></div>
            <div>Diproses: <strong>${processed}</strong></div>
            <div>Insert baru: <strong>${inserted}</strong></div>
            <div>Cocok existing: <strong>${matched}</strong></div>
            <div>Skip: <strong>${skipped}</strong></div>
            <div>Gagal: <strong>${failed}</strong></div>
          </div>
          <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:${progress}%">
              ${progress}%
            </div>
          </div>
        `;
      }

      if (data.status === 'done') {
        return `
          <div class="alert alert-success mt-3">
            <div class="fw-bold mb-2">Sinkronisasi selesai</div>
            <div>Total branch: <strong>${total}</strong></div>
            <div>Diproses: <strong>${processed}</strong></div>
            <div>Insert baru: <strong>${inserted}</strong></div>
            <div>Cocok existing: <strong>${matched}</strong></div>
            <div>Skip: <strong>${skipped}</strong></div>
            <div>Gagal: <strong>${failed}</strong></div>
          </div>
        `;
      }

      if (data.status === 'failed') {
        return `
          <div class="alert alert-danger mt-3 mb-0">
            <div class="fw-bold mb-1">Sinkronisasi gagal</div>
            <div>${data.message ?? 'Terjadi kesalahan.'}</div>
          </div>
        `;
      }

      return `<div class="alert alert-secondary mt-3 mb-0">Status tidak dikenali.</div>`;
    }

    async function getJson(url) {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw { status: response.status, data };
      return data;
    }

    function startSyncEsbPolling(syncKey) {
      const urlTemplate = `{{ route('outlet.master.sync.esb.status', ['key' => '__SYNC_KEY__']) }}`;
      const url = urlTemplate.replace('__SYNC_KEY__', syncKey);

      syncEsbPollingTimer = setInterval(async function () {
        try {
          const data = await getJson(url);

          document.getElementById('syncEsbOutletDebug').innerHTML =
            `<pre style="margin:0">${JSON.stringify(data, null, 2)}</pre>`;

          document.getElementById('syncEsbOutletStatus').innerHTML = renderSyncEsbStatus(data);

          if (data.status === 'done' || data.status === 'failed') {
            clearInterval(syncEsbPollingTimer);
            syncEsbPollingTimer = null;

            if (data.status === 'done') {
              setTimeout(() => {
                window.location.reload();
              }, 1500);
            }
          }
        } catch (err) {
          clearInterval(syncEsbPollingTimer);
          syncEsbPollingTimer = null;
          document.getElementById('syncEsbOutletStatus').innerHTML =
            '<div class="alert alert-danger mt-3 mb-0">Gagal mengambil status sinkronisasi.</div>';
        }
      }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function () {
      const btn = document.getElementById('btnSyncEsbOutlets');
      if (!btn) return;

      btn.addEventListener('click', async function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Memproses...';

        document.getElementById('syncEsbOutletStatus').innerHTML = `
          <div class="alert alert-info mt-3 mb-0">Mengirim job sinkronisasi ke server...</div>
        `;
        document.getElementById('syncEsbOutletDebug').innerHTML = '';

        try {
          const response = await withPostPassword(async () => {
            return await fetch(`{{ route('outlet.master.sync.esb') }}`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                credential_code: 'OKNHO'
              })
            });
          });
          
          if (!response) return;

          const data = await response.json().catch(() => ({}));

          if (!response.ok) {
            throw data;
          }

          document.getElementById('syncEsbOutletStatus').innerHTML = renderSyncEsbStatus(data);

          if (data.sync_key) {
            startSyncEsbPolling(data.sync_key);
          }
        } catch (err) {
          document.getElementById('syncEsbOutletStatus').innerHTML = `
            <div class="alert alert-danger mt-3 mb-0">
              ${err.message ?? 'Gagal memulai sinkronisasi outlet.'}
            </div>
          `;
        } finally {
          btn.disabled = false;
          btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i>Sync Outlet dari ESB';
        }
      });
    });
  })();
</script>

<script>
  (function () {
    let syncEsbAllTimer = null;

    function escapeHtml(text) {
      return $('<div>').text(text ?? '').html();
    }

    function renderSyncAllStatus(data) {
      const progress = Number(data.progress ?? 0);
      const totalCredentials = Number(data.total_credentials ?? 0);
      const processedCredentials = Number(data.processed_credentials ?? 0);
      const successCredentials = Number(data.success_credentials ?? 0);
      const failedCredentials = Number(data.failed_credentials ?? 0);
      const totalInserted = Number(data.total_inserted ?? 0);
      const totalUpdated = Number(data.total_updated ?? 0);
      const totalSkipped = Number(data.total_skipped ?? 0);
      const totalFailedRows = Number(data.total_failed_rows ?? 0);

      let logsHtml = '';
      if (Array.isArray(data.logs) && data.logs.length) {
        logsHtml = `
          <div class="result-box mt-3">
            <div class="fw-bold mb-2">Log credential terbaru</div>
            <ul class="mb-0 ps-3">
              ${data.logs.map(log => `
                <li class="mb-1">
                  <strong>${escapeHtml(log.credential_code)}</strong>
                  - ${escapeHtml(log.status)}
                  | insert: ${escapeHtml(log.inserted ?? 0)}
                  | update: ${escapeHtml(log.updated ?? 0)}
                  | skip: ${escapeHtml(log.skipped ?? 0)}
                  | fail: ${escapeHtml(log.failed ?? 0)}
                  ${log.message ? `<br><small>${escapeHtml(log.message)}</small>` : ''}
                </li>
              `).join('')}
            </ul>
          </div>
        `;
      }

      if (data.status === 'queued') {
        return `
          <div class="alert alert-info mt-3 mb-0">
            <div class="fw-bold mb-1">Masuk antrian</div>
            <div>${escapeHtml(data.message ?? 'Menunggu worker queue.')}</div>
          </div>
        `;
      }

      if (data.status === 'processing') {
        return `
          <div class="alert alert-info mt-3 mb-2">
            <div class="fw-bold mb-2">Sinkronisasi semua credential sedang berjalan</div>
            <div>Total credential: <strong>${totalCredentials}</strong></div>
            <div>Diproses: <strong>${processedCredentials}</strong></div>
            <div>Credential sukses: <strong>${successCredentials}</strong></div>
            <div>Credential gagal: <strong>${failedCredentials}</strong></div>
            <hr>
            <div>Insert outlet baru: <strong>${totalInserted}</strong></div>
            <div>Update outlet existing: <strong>${totalUpdated}</strong></div>
            <div>Skip: <strong>${totalSkipped}</strong></div>
            <div>Failed rows: <strong>${totalFailedRows}</strong></div>
          </div>
          <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:${progress}%">
              ${progress}%
            </div>
          </div>
          ${logsHtml}
        `;
      }

      if (data.status === 'done') {
        return `
          <div class="alert alert-success mt-3 mb-2">
            <div class="fw-bold mb-2">Sinkronisasi semua credential selesai</div>
            <div>Total credential: <strong>${totalCredentials}</strong></div>
            <div>Diproses: <strong>${processedCredentials}</strong></div>
            <div>Credential sukses: <strong>${successCredentials}</strong></div>
            <div>Credential gagal: <strong>${failedCredentials}</strong></div>
            <hr>
            <div>Insert outlet baru: <strong>${totalInserted}</strong></div>
            <div>Update outlet existing: <strong>${totalUpdated}</strong></div>
            <div>Skip: <strong>${totalSkipped}</strong></div>
            <div>Failed rows: <strong>${totalFailedRows}</strong></div>
          </div>
          ${logsHtml}
          ${outletWarningsHtml}
        `;
      }

      if (data.status === 'failed') {
        return `
          <div class="alert alert-danger mt-3 mb-0">
            <div class="fw-bold mb-1">Sinkronisasi gagal</div>
            <div>${escapeHtml(data.message ?? 'Terjadi kesalahan')}</div>
          </div>
        `;
      }

      return `<div class="alert alert-secondary mt-3 mb-0">Status tidak dikenali.</div>`;
    }

    async function getJson(url) {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) throw { status: response.status, data };
      return data;
    }

    function startSyncAllPolling(syncKey) {
      const urlTemplate = `{{ route('outlet.master.sync.esb.all.status', ['key' => '__SYNC_KEY__']) }}`;
      const url = urlTemplate.replace('__SYNC_KEY__', syncKey);

      syncEsbAllTimer = setInterval(async function () {
        try {
          const data = await getJson(url);

          $('#syncEsbAllDebug').html('<pre style="margin:0">' + escapeHtml(JSON.stringify(data, null, 2)) + '</pre>');
          $('#syncEsbAllStatus').html(renderSyncAllStatus(data));

          if (data.status === 'done' || data.status === 'failed') {
            clearInterval(syncEsbAllTimer);
            syncEsbAllTimer = null;

            if (data.status === 'done') {
              setTimeout(function () {
                window.location.reload();
              }, 2000);
            }
          }
        } catch (err) {
          clearInterval(syncEsbAllTimer);
          syncEsbAllTimer = null;
          $('#syncEsbAllStatus').html('<div class="alert alert-danger mt-3 mb-0">Gagal cek status sync ESB.</div>');
        }
      }, 3000);
    }

    $(document).ready(function () {
      $('#btnSyncEsbAllOutlets').on('click', async function () {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="bi bi-arrow-repeat me-1"></i>Memproses...');

        $('#syncEsbAllStatus').html('<div class="alert alert-info mt-3 mb-0">Mengirim job sync ke server...</div>');
        $('#syncEsbAllDebug').html('');

        try {
          const response = await withPostPassword(async () => {
            return await fetch(`{{ route('outlet.master.sync.esb.all') }}`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({})
            });
          });

          if (!response) {
            btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i>Sync Outlet ESB All Credential');
            return;
          }

          const data = await response.json().catch(() => ({}));

          if (!response.ok) {
            throw data;
          }

          $('#syncEsbAllStatus').html(renderSyncAllStatus(data));

          if (data.sync_key) {
            startSyncAllPolling(data.sync_key);
          }
        } catch (err) {
          $('#syncEsbAllStatus').html(`
            <div class="alert alert-danger mt-3 mb-0">
              ${escapeHtml(err.message ?? 'Gagal memulai sync outlet ESB')}
            </div>
          `);
        } finally {
          btn.prop('disabled', false).html('<i class="bi bi-arrow-repeat me-1"></i>Sync Outlet ESB All Credential');
        }
      });
    });
  })();
</script>

<script>
  (function () {
    let syncSalesAllBranchTimer = null;
    let syncSalesAllBranchHistory = [];

    function escapeHtml(text) {
      return $('<div>').text(text ?? '').html();
    }

    function setHtml(id, html) {
      const el = document.getElementById(id);
      if (el) el.innerHTML = html;
    }

    function normalizeOutletWarningRows(data) {
      const rows = [];

      if (Array.isArray(data?.missing_outlets)) {
        data.missing_outlets.forEach(row => rows.push({ ...row, type: 'mapping' }));
      }

      if (Array.isArray(data?.outlets_without_sales)) {
        data.outlets_without_sales.forEach(row => rows.push({ ...row, type: 'no_sales' }));
      }

      if (Array.isArray(data?.no_sales_outlets)) {
        data.no_sales_outlets.forEach(row => rows.push({ ...row, type: 'no_sales' }));
      }

      const map = new Map();
      rows.forEach(row => {
        const key = [
          row.type || '',
          row.outlet_id || '',
          row.branch_id || '',
          row.branch_code || row.esb_branch_code || '',
          row.branch_name || row.outlet_name || row.nama_outlet || ''
        ].join('|');
        if (!map.has(key)) map.set(key, row);
      });

      return Array.from(map.values());
    }

    function renderOutletWarnings(data) {
      const rows = normalizeOutletWarningRows(data);
      if (!rows.length) return '';

      const items = rows.slice(0, 100).map(row => {
        const name = row.outlet_name || row.nama_outlet || row.branch_name || '-';
        const code = row.branch_code || row.esb_branch_code || '-';
        const typeLabel = row.type === 'mapping' ? 'Mapping belum ketemu' : 'Tidak ada sales';
        const reason = row.reason || typeLabel;

        return `
          <tr>
            <td>${escapeHtml(typeLabel)}</td>
            <td><strong>${escapeHtml(name)}</strong></td>
            <td>${escapeHtml(code)}</td>
            <td>${escapeHtml(row.kota || '-')}</td>
            <td>${escapeHtml(reason)}</td>
          </tr>
        `;
      }).join('');

      const more = rows.length > 100
        ? `<div class="form-text mt-2">Ditampilkan 100 dari ${escapeHtml(rows.length)} outlet. Lihat debug JSON untuk sisanya.</div>`
        : '';

      return `
        <div class="alert alert-warning mt-3">
          <div class="fw-bold mb-2">Outlet yang tidak masuk sales / mapping belum ketemu: ${escapeHtml(rows.length)}</div>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Tipe</th>
                  <th>Nama Outlet / Branch</th>
                  <th>Branch Code</th>
                  <th>Kota</th>
                  <th>Keterangan</th>
                </tr>
              </thead>
              <tbody>${items}</tbody>
            </table>
          </div>
          ${more}
        </div>
      `;
    }

    function setDebug(id, data) {
      const el = document.getElementById(id);
      if (!el) return;

      if (typeof data === 'string') {
        el.innerHTML = `<pre style="margin:0">${escapeHtml(data)}</pre>`;
        return;
      }

      el.innerHTML = `<pre style="margin:0">${escapeHtml(JSON.stringify(data, null, 2))}</pre>`;
    }

    function clearPolling() {
      if (syncSalesAllBranchTimer) {
        clearInterval(syncSalesAllBranchTimer);
        syncSalesAllBranchTimer = null;
      }
    }

    function normalizeStatus(status) {
      return String(status || 'queued').toLowerCase();
    }

    function renderStatusPill(status) {
      const s = normalizeStatus(status);
      const icon = ['done', 'success'].includes(s)
        ? 'bi-check-circle'
        : ['failed', 'error'].includes(s)
          ? 'bi-x-circle'
          : 'bi-hourglass-split';

      return `<span class="sync-status-pill ${escapeHtml(s)}"><i class="bi ${icon}"></i>${escapeHtml(s)}</span>`;
    }

    function normalizeHistoryRows(payload) {
      if (Array.isArray(payload)) return payload;
      if (Array.isArray(payload?.data)) return payload.data;
      if (Array.isArray(payload?.history)) return payload.history;
      if (payload && typeof payload === 'object' && payload.sync_key) return [payload];
      return [];
    }

    function renderSyncHistory() {
      const keyword = ($('#syncSalesAllBranchHistorySearch').val() || '').toLowerCase().trim();

      const rows = syncSalesAllBranchHistory.filter(row => {
        if (!keyword) return true;

        return [
          row.sales_date,
          row.status,
          row.sync_key,
          row.message,
          row.created_at,
          row.finished_at
        ].join(' ').toLowerCase().includes(keyword);
      });

      $('#syncSalesAllBranchHistoryCount').text(`${rows.length} history`);

      if (!rows.length) {
        $('#syncSalesAllBranchHistoryBody').html(`
          <tr><td colspan="6" class="sync-history-empty">History belum ada.</td></tr>
        `);

        $('#syncSalesAllBranchHistoryMobile').html(`
          <div class="sync-history-empty">History belum ada.</div>
        `);
        return;
      }

      $('#syncSalesAllBranchHistoryBody').html(rows.map(row => `
        <tr>
          <td>${escapeHtml(row.sales_date || '-')}</td>
          <td>${renderStatusPill(row.status)}</td>
          <td><code>${escapeHtml(row.sync_key || '-')}</code></td>
          <td>${escapeHtml(row.message || '-')}</td>
          <td>${escapeHtml(row.created_at || '-')}</td>
          <td>${escapeHtml(row.finished_at || '-')}</td>
        </tr>
      `).join(''));

      $('#syncSalesAllBranchHistoryMobile').html(rows.map(row => `
        <div class="sync-history-card">
          <div class="sync-history-card-head">
            <div>
              <div class="sync-history-card-title">${escapeHtml(row.sales_date || '-')}</div>
              <div class="sync-history-card-sub">${escapeHtml(row.created_at || '-')}</div>
            </div>
            ${renderStatusPill(row.status)}
          </div>
          <div class="sync-history-card-body">
            <div><strong>Sync Key:</strong> <code>${escapeHtml(row.sync_key || '-')}</code></div>
            <div class="mt-1">${escapeHtml(row.message || '-')}</div>
            <div class="mt-1 text-muted"><strong>Finished:</strong> ${escapeHtml(row.finished_at || '-')}</div>
          </div>
        </div>
      `).join(''));
    }

    async function getJson(url) {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });

      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw {
          status: response.status,
          data
        };
      }

      return data;
    }

    async function loadSyncSalesAllBranchHistory() {
      const statusUrlTemplate = `{{ route('outlet.master.sync.sales.esb.all.status', ['key' => '__SYNC_KEY__']) }}`;

      /*
       * Mode history:
       * 1. Kalau backend sudah punya endpoint history, isi variable berikut.
       * 2. Kalau belum ada, history minimal tetap diisi dari hasil trigger sync yang sedang berjalan.
       */
      const historyUrl = `{{ Route::has('outlet.master.sync.sales.esb.all.history') ? route('outlet.master.sync.sales.esb.all.history') : '' }}`;

      $('#syncSalesAllBranchHistoryBody').html(`
        <tr><td colspan="6" class="sync-history-empty">Memuat history...</td></tr>
      `);
      $('#syncSalesAllBranchHistoryMobile').html(`
        <div class="sync-history-empty">Memuat history...</div>
      `);

      if (!historyUrl) {
        renderSyncHistory();
        return;
      }

      try {
        const data = await getJson(historyUrl);
        syncSalesAllBranchHistory = normalizeHistoryRows(data);
        renderSyncHistory();
      } catch (err) {
        $('#syncSalesAllBranchHistoryBody').html(`
          <tr><td colspan="6" class="sync-history-empty">Gagal memuat history.</td></tr>
        `);
        $('#syncSalesAllBranchHistoryMobile').html(`
          <div class="sync-history-empty">Gagal memuat history.</div>
        `);
      }
    }

    function upsertHistory(row) {
      if (!row || !row.sync_key) return;

      const idx = syncSalesAllBranchHistory.findIndex(item => item.sync_key === row.sync_key);
      if (idx >= 0) {
        syncSalesAllBranchHistory[idx] = { ...syncSalesAllBranchHistory[idx], ...row };
      } else {
        syncSalesAllBranchHistory.unshift(row);
      }

      renderSyncHistory();
    }

    function renderSyncSalesAllBranchStatus(data) {
      const totalCredentials = Number(data.total_credentials ?? data.total_branches ?? 0);
      const preparedCredentials = Number(data.prepared_credentials ?? 0);
      const processedCredentials = Number(data.processed_credentials ?? data.processed_branches ?? 0);
      const successCredentials = Number(data.success_credentials ?? data.success_branches ?? 0);
      const failedCredentials = Number(data.failed_credentials ?? data.failed_branches ?? 0);

      const totalPages = Number(data.total_pages ?? 0);
      const dispatchedPages = Number(data.dispatched_pages ?? 0);
      const processedPages = Number(data.processed_pages ?? 0);
      const successPages = Number(data.success_pages ?? 0);
      const failedPages = Number(data.failed_pages ?? 0);

      const totalApiRows = Number(data.total_api_rows ?? 0);
      const totalBuiltRows = Number(data.total_built_rows ?? 0);
      const totalInsertedRows = Number(data.total_inserted_rows ?? 0);
      const progress = Number(data.progress ?? 0);

      let logsHtml = '';
      if (Array.isArray(data.logs) && data.logs.length) {
        logsHtml = `
          <div class="result-box mt-3">
            <div class="fw-bold mb-2">Log page terbaru</div>
            <ul class="mb-0 ps-3">
              ${data.logs.map(log => `
                <li class="mb-1">
                  <strong>${escapeHtml(log.credential_code ?? log.branch_code ?? '-')}</strong>
                  - ${escapeHtml(log.status ?? '-')}
                  ${log.page !== undefined ? `| page: ${escapeHtml(log.page)} / ${escapeHtml(log.page_count ?? '-')}` : ''}
                  ${log.api_rows !== undefined ? `| api_rows: ${escapeHtml(log.api_rows)}` : ''}
                  ${log.built_rows !== undefined ? `| built_rows: ${escapeHtml(log.built_rows)}` : ''}
                  ${log.inserted_rows !== undefined ? `| inserted: ${escapeHtml(log.inserted_rows)}` : ''}
                  ${log.message ? `<br><small>${escapeHtml(log.message)}</small>` : ''}
                </li>
              `).join('')}
            </ul>
          </div>
        `;
      }

      const outletWarningsHtml = renderOutletWarnings(data);

      const progressHtml = `
        <div class="progress">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width:${progress}%">
            ${progress}%
          </div>
        </div>
      `;

      const statsHtml = `
        <div class="meta">
          <div>Tanggal: <strong>${escapeHtml(data.sales_date ?? '-')}</strong></div>
          <div>Total credential: <strong>${totalCredentials}</strong></div>
          <div>Credential disiapkan: <strong>${preparedCredentials}</strong></div>
          <div>Credential selesai: <strong>${processedCredentials}</strong></div>
          <div>Credential sukses: <strong>${successCredentials}</strong></div>
          <div>Credential gagal: <strong>${failedCredentials}</strong></div>
          <div>Total page: <strong>${totalPages}</strong></div>
          <div>Page dikirim: <strong>${dispatchedPages}</strong></div>
          <div>Page diproses: <strong>${processedPages}</strong></div>
          <div>Page sukses: <strong>${successPages}</strong></div>
          <div>Page gagal: <strong>${failedPages}</strong></div>
          <div>API rows: <strong>${totalApiRows}</strong></div>
          <div>Built rows: <strong>${totalBuiltRows}</strong></div>
          <div>Inserted rows: <strong>${totalInsertedRows}</strong></div>
        </div>
      `;

      if (data.status === 'queued') {
        return `
          <div class="alert alert-info mt-3 mb-0">
            <div class="fw-bold mb-1">Masuk antrian</div>
            <div>${escapeHtml(data.message ?? 'Job sync sales per credential berhasil dikirim.')}</div>
          </div>
        `;
      }

      if (data.status === 'processing') {
        return `
          <div class="sync-status-card mt-3 mb-2">
            <div class="title">Sinkronisasi sales per credential/page sedang berjalan</div>
            ${statsHtml}
          </div>
          ${progressHtml}
          ${logsHtml}
          ${outletWarningsHtml}
        `;
      }

      if (data.status === 'done') {
        const hasFailure = failedCredentials > 0 || failedPages > 0;
        return `
          <div class="alert ${hasFailure ? 'alert-warning' : 'alert-success'} mt-3 mb-2">
            <div class="fw-bold mb-2">Sinkronisasi sales per credential/page selesai</div>
            <div>Tanggal: <strong>${escapeHtml(data.sales_date ?? '-')}</strong></div>
            <div>Total credential: <strong>${totalCredentials}</strong></div>
            <div>Credential sukses: <strong>${successCredentials}</strong></div>
            <div>Credential gagal: <strong>${failedCredentials}</strong></div>
            <div>Total page: <strong>${totalPages}</strong></div>
            <div>Page sukses: <strong>${successPages}</strong></div>
            <div>Page gagal: <strong>${failedPages}</strong></div>
            <div>API rows: <strong>${totalApiRows}</strong></div>
            <div>Built rows: <strong>${totalBuiltRows}</strong></div>
            <div>Inserted rows: <strong>${totalInsertedRows}</strong></div>
          </div>
          ${logsHtml}
        `;
      }

      if (data.status === 'failed') {
        return `
          <div class="alert alert-danger mt-3 mb-0">
            <div class="fw-bold mb-1">Sinkronisasi sales per credential gagal</div>
            <div>${escapeHtml(data.message ?? 'Terjadi kesalahan saat sinkronisasi.')}</div>
          </div>
          ${outletWarningsHtml}
        `;
      }

      return `
        <div class="alert alert-secondary mt-3 mb-0">
          Status tidak dikenali.
        </div>
      `;
    }

    function startSyncSalesAllBranchPolling(syncKey) {
      const urlTemplate = `{{ route('outlet.master.sync.sales.esb.all.status', ['key' => '__SYNC_KEY__']) }}`;
      const url = urlTemplate.replace('__SYNC_KEY__', syncKey);

      clearPolling();

      syncSalesAllBranchTimer = setInterval(async function () {
        try {
          const data = await getJson(url);

          setDebug('syncSalesEsbAllBranchDebug', data);
          setHtml('syncSalesEsbAllBranchStatus', renderSyncSalesAllBranchStatus(data));

          upsertHistory({
            sales_date: data.sales_date,
            status: data.status,
            sync_key: syncKey,
            message: data.message,
            created_at: data.created_at,
            finished_at: data.finished_at
          });

          if (data.status === 'done' || data.status === 'failed') {
            clearPolling();
          }
        } catch (err) {
          clearPolling();

          setHtml(
            'syncSalesEsbAllBranchStatus',
            '<div class="alert alert-danger mt-3 mb-0">Gagal cek status sales per credential. Cek route/status endpoint.</div>'
          );

          setDebug('syncSalesEsbAllBranchDebug', err?.data ?? err ?? 'Unknown error');
        }
      }, 3000);
    }

    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('formSyncSalesEsbAllBranch');
      const modalEl = document.getElementById('modalSyncSalesEsbAllBranch');
      const btnSubmit = document.getElementById('btnSyncSalesEsbAllBranchSubmit');
      const salesDateInput = document.getElementById('sync_sales_esb_all_branch_date');

      if (!form || !btnSubmit || !salesDateInput) return;

      $('#btnRefreshSyncSalesAllBranchHistory').on('click', loadSyncSalesAllBranchHistory);
      $('#syncSalesAllBranchHistorySearch').on('input', renderSyncHistory);

      if (modalEl) {
        modalEl.addEventListener('shown.bs.modal', function () {
          if (!salesDateInput.value) {
            salesDateInput.value = new Date().toISOString().slice(0, 10);
          }

          loadSyncSalesAllBranchHistory();
        });
      }

      form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const salesDate = salesDateInput.value;

        if (!salesDate) {
          setHtml(
            'syncSalesEsbAllBranchStatus',
            '<div class="alert alert-danger mt-3 mb-0">Sales Date wajib diisi.</div>'
          );
          return;
        }

        try {
          const response = await window.withPostPassword(async () => {
            btnSubmit.disabled = true;
            btnSubmit.innerText = 'Memproses...';

            setHtml(
              'syncSalesEsbAllBranchStatus',
              '<div class="alert alert-info mt-3 mb-0">Mengirim job sync sales per credential...</div>'
            );
            setHtml('syncSalesEsbAllBranchDebug', '');
            return await fetch(`{{ route('outlet.master.sync.sales.esb.all') }}`, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                sales_date: salesDate
              })
            });
          });

          if (!response) {
            btnSubmit.disabled = false;
            btnSubmit.innerText = 'Sync Sales Per Credential';
            return;
          }

          const data = await response.json().catch(() => ({}));

          if (!response.ok) {
            throw data;
          }

          setHtml('syncSalesEsbAllBranchStatus', renderSyncSalesAllBranchStatus(data));
          setDebug('syncSalesEsbAllBranchDebug', data);

          upsertHistory({
            sales_date: data.sales_date || salesDate,
            status: data.status || 'queued',
            sync_key: data.sync_key || '-',
            message: data.message || 'Job sync sales per credential berhasil dikirim.',
            created_at: data.created_at || new Date().toLocaleString('id-ID'),
            finished_at: data.finished_at || null
          });

          if (data.sync_key) {
            startSyncSalesAllBranchPolling(data.sync_key);
          }
        } catch (err) {
          setHtml(
            'syncSalesEsbAllBranchStatus',
            `
              <div class="alert alert-danger mt-3 mb-0">
                ${escapeHtml(err.message ?? 'Gagal memulai sync sales per credential')}
              </div>
            `
          );

          setDebug('syncSalesEsbAllBranchDebug', err);
        } finally {
          btnSubmit.disabled = false;
          btnSubmit.innerText = 'Sync Sales Per Credential';
        }
      });

      if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
          clearPolling();
          form.reset();
          setHtml('syncSalesEsbAllBranchStatus', '');
          setHtml('syncSalesEsbAllBranchDebug', '');
          btnSubmit.disabled = false;
          btnSubmit.innerText = 'Sync Sales Per Credential';
        });
      }
    });
  })();
</script>





<script>
  (function () {
    const DEFAULT_POST_PASSWORD = 'zzz';

    async function askPostPassword() {
      const result = await Swal.fire({
        title: 'Password diperlukan',
        text: 'Masukkan password untuk melanjutkan',
        input: 'password',
        inputPlaceholder: 'Masukkan password',
        inputAttributes: {
          autocapitalize: 'off',
          autocorrect: 'off',
          autocomplete: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Lanjut',
        cancelButtonText: 'Batal',
        allowOutsideClick: false,
        allowEscapeKey: true,
        didOpen: () => {
          const input = Swal.getInput();
          if (input) {
            input.removeAttribute('readonly');
            input.removeAttribute('disabled');
            input.focus();
          }
        },
        preConfirm: (value) => {
          if (!value) {
            Swal.showValidationMessage('Password wajib diisi');
            return false;
          }

          if (value !== DEFAULT_POST_PASSWORD) {
            Swal.showValidationMessage('Password salah');
            return false;
          }

          return true;
        }
      });

      return result.isConfirmed;
    }

    function validateOutletForm(form) {
      const kode = (form.querySelector('[name="kode_outlet"]')?.value || '').trim();
      const nama = (form.querySelector('[name="nama_outlet"]')?.value || '').trim();
      const status = (form.querySelector('[name="status"]')?.value || '').trim();

      if (!kode) {
        Swal.fire({
          icon: 'warning',
          title: 'Form belum lengkap',
          text: 'Kode outlet wajib diisi.'
        });
        return false;
      }

      if (!nama) {
        Swal.fire({
          icon: 'warning',
          title: 'Form belum lengkap',
          text: 'Nama outlet wajib diisi.'
        });
        return false;
      }

      if (!status) {
        Swal.fire({
          icon: 'warning',
          title: 'Form belum lengkap',
          text: 'Status wajib dipilih.'
        });
        return false;
      }

      return true;
    }

    async function protectedSubmit(form, submitBtn, loadingText) {
      if (form.dataset.submitting === '1') return;

      if (!validateOutletForm(form)) return;

      const activeModalEl = form.closest('.modal');
      const activeModalInstance = activeModalEl ? bootstrap.Modal.getInstance(activeModalEl) : null;

      if (activeModalInstance) {
        activeModalInstance.hide();
      }

      const ok = await askPostPassword();

      if (!ok) {
        if (activeModalInstance) {
          activeModalInstance.show();
        }
        return;
      }

      form.dataset.submitting = '1';

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerText = loadingText;
      }

      form.submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
      const addForm = document.getElementById('formAddOutlet');
      const editForm = document.getElementById('formEditOutlet');
      const addBtn = document.getElementById('btnAddSubmit');
      const editBtn = document.getElementById('btnEditSubmit');

      if (addForm) {
        addForm.addEventListener('submit', async function (e) {
          if (this.dataset.submitting === '1') return;
          e.preventDefault();
          await protectedSubmit(this, addBtn, 'Menyimpan...');
        });
      }

      if (editForm) {
        editForm.addEventListener('submit', async function (e) {
          if (this.dataset.submitting === '1') return;
          e.preventDefault();
          await protectedSubmit(this, editBtn, 'Menyimpan...');
        });
      }

      window.withPostPassword = async function (callback) {
        const ok = await askPostPassword();
        if (!ok) return null;
        return await callback();
      };
    });
  })();
</script>


<script>
  (function () {
    function initRedesignSelect2() {
      if (!window.jQuery || !$.fn.select2) return;
      $('.modal-select-add').select2({
        dropdownParent: $('#addModal'),
        width: '100%',
        allowClear: true,
        placeholder: function () { return $(this).data('placeholder') || 'Pilih data'; }
      });
      $('.modal-select-edit').select2({
        dropdownParent: $('#editModal'),
        width: '100%',
        allowClear: true,
        placeholder: function () { return $(this).data('placeholder') || 'Pilih data'; }
      });
      $('.select2-sales-outlet').each(function () {
        const $select = $(this);
        if ($select.hasClass('select2-hidden-accessible')) return;

        $select.select2({
          dropdownParent: $('#modalSyncSalesSelected'),
          width: '100%',
          allowClear: true,
          closeOnSelect: false,
          placeholder: function () { return $(this).data('placeholder') || 'Pilih outlet'; }
        });
      });
    }

    function fillEditModalFromButton(btn) {
      $('#edit-id').val(btn.data('id') || '');
      $('#edit-kode').val(btn.data('kode') || '');
      $('#edit-nama').val(btn.data('nama') || '');
      $('#edit-kota').val(btn.data('kota') || '');
      $('#edit-alamat').val(btn.data('alamat') || '');
      $('#edit-area').val(btn.data('area') || '').trigger('change');
      $('#edit-mitra').val(btn.data('mitra') || '').trigger('change');
      $('#edit-status').val(btn.data('status') || 'existing').trigger('change');
    }

    $(document).ready(function () {
      initRedesignSelect2();
      $(document).off('click.redesignEdit').on('click.redesignEdit', '.btn-edit-outlet', function () {
        fillEditModalFromButton($(this));
      });
    });
  })();
</script>


<script>
  // Fallback ringan: kalau Bootstrap/SweetAlert meninggalkan backdrop, UI tidak akan freeze.
  document.addEventListener('hidden.bs.modal', function () {
    setTimeout(function () {
      if (!document.querySelector('.modal.show') && !document.querySelector('.swal2-container')) {
        document.querySelectorAll('.modal-backdrop').forEach(function (el) { el.remove(); });
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
      }
    }, 120);
  });
</script>


@endpush

@include('Temp.Investor.footer')

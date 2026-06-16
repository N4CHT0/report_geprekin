<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Purchase Request Order</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --border: #f1f5f9;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --radius: 16px;
            --primary: #4f46e5;
            --accent: #0d9488;
            --warn: #d97706;
            --danger: #e11d48;
            --soft: #f8fafc;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 0.9rem;
        }

        .wrap {
            max-width: 1600px;
        }

        /* TOP APP BAR PREMIUM */
        .appbar {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px 24px;
        }

        .appbar h4 {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: 1.35rem;
            color: var(--text);
        }

        /* MODERN ACCENTED SUMMARY CARDS */
        .summary-card {
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--card);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow);
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
        }

        .icon-box {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.4rem;
        }

        /* BUTTONS WITH SMOOTH INTERACTIONS */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            transition: all 0.2s ease-in-out;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: #4338ca;
            border-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .btn-light {
            background: #ffffff;
            border-color: #e2e8f0;
            color: var(--muted);
        }

        .btn-light:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        /* FORMS & ENHANCED MODALS */
        .form-label {
            font-weight: 700;
            font-size: 0.75rem;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 0.4rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 0.5rem 1rem;
            background-color: #ffffff;
            font-size: 0.85rem;
            color: var(--text);
            transition: all 0.15s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .modal-header {
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border);
            padding: 1rem 1.5rem;
        }

        /* MAIN CLEAN TABLE CARD */
        .maincard {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-top: 1.5rem;
        }

        .table> :not(caption)>*>* {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(248, 249, 250, 0.9) !important;
            transition: background-color 0.2s ease;
        }

        .table-light th {
            background-color: #f8fafc;
            color: var(--muted);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 2px solid #e2e8f0;
        }

        .badge {
            padding: 0.45em 0.75em;
            font-weight: 600;
            border-radius: 50px;
            letter-spacing: 0.2px;
        }

        /* Select2 override premium adjustment */
        .select2-container .select2-selection--single {
            height: 38px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--text);
            padding-left: 12px;
        }

        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        /* DataTables Spacing Optimization */
        .dataTables_wrapper {
            padding: 1.25rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 0.35rem 0.75rem;
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_length {
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: var(--muted);
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--muted);
        }

        table.dataTable {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 8px !important;
            margin-bottom: 8px !important;
        }

        /* Camera Frame Smooth Adjustment */
        .camera-area {
            border: 2px dashed #cbd5e1 !important;
            background-color: #f8fafc !important;
            transition: border-color 0.2s ease;
        }

        .camera-area:hover {
            border-color: var(--primary) !important;
        }
    </style>
</head>

<body>
    <main class="container py-4 wrap">

        <div class="appbar mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="text-dark mb-1">
                        Dashboard Outlet <span class="text-light-grid mx-2" style="color: #cbd5e1;"> - </span>
                        @if($myOutlet)
                            <span style="color: var(--primary);">{{ $myOutlet->nama_outlet }}</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold"
                                style="font-size: 0.65em; vertical-align: middle; padding: 4px 10px;">
                                <i class="bi bi-shield-lock me-1"></i> MODE BACKOFFICE
                            </span>
                        @endif
                    </h4>

                    {{-- TAMBAHAN ALAMAT DI SINI --}}
                    @if($myOutlet && $myOutlet->alamat)
                        <p class="text-muted mb-2" style="font-size: 0.85rem;">
                            <i class="bi bi-geo-alt me-1"></i> {{ $myOutlet->alamat }}
                        </p>
                    @endif

                    <!-- <p class="text-muted mb-0" style="font-size: 0.85rem;">
                        Ringkasan aktivitas Purchase Order dan operasional distribusi logistik utama.
                    </p> -->
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('crew.menus') }}"
                        class="btn btn-light border shadow-sm d-flex align-items-center">
                        <i class="bi bi-grid me-1.5"></i> Back to Menu
                    </a>
                    @if(!$myOutlet)
                        <a href="{{ route('investor.sales.dashboard') }}"
                            class="btn btn-light border shadow-sm d-flex align-items-center">
                            <i class="bi bi-speedometer2 me-1.5"></i> Dashboard
                        </a>
                    @endif
                    <form action="{{ route('auth.investor.logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-danger shadow-sm d-flex align-items-center" type="submit"
                            style="background-color: var(--danger); border-color: var(--danger);">
                            <i class="bi bi-box-arrow-right me-1.5"></i> Logout
                        </button>
                    </form>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-3">
                    <div class="summary-card p-3 h-100"
                        style="background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box text-primary" style="background-color: rgba(79, 70, 229, 0.08);">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-bold mb-0.5"
                                    style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Total
                                    PO</h6>
                                <h4 class="fw-bold text-dark mb-0" style="font-size: 1.4rem;">{{ $total_po }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-card p-3 h-100"
                        style="background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%); border-bottom: 3px solid var(--warn) !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box text-warning" style="background-color: rgba(217, 119, 6, 0.08);">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-bold mb-0.5"
                                    style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Menunggu</h6>
                                <h4 class="fw-bold text-warning-emphasis mb-0" style="font-size: 1.4rem;">
                                    {{ $menunggu }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-card p-3 h-100"
                        style="background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%); border-bottom: 3px solid var(--accent) !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box text-success" style="background-color: rgba(13, 148, 136, 0.08);">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-bold mb-0.5"
                                    style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Disetujui</h6>
                                <h4 class="fw-bold text-success-emphasis mb-0" style="font-size: 1.4rem;">
                                    {{ $disetujui }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-card p-3 h-100"
                        style="background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%); border-bottom: 3px solid var(--danger) !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box text-danger" style="background-color: rgba(225, 29, 72, 0.08);">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted fw-bold mb-0.5"
                                    style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Ditolak
                                </h6>
                                <h4 class="fw-bold text-danger-emphasis mb-0" style="font-size: 1.4rem;">{{ $ditolak }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('po.store') }}" method="POST" id="formPO">
            @csrf
            <div class="modal fade" id="modalRequestPO" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content shadow-lg">

                        <div class="modal-header bg-light border-bottom">
                            <h6 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center">
                                <i class="bi bi-cart-plus me-2 text-primary fs-5"></i>Form Request Purchase Order
                            </h6>
                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-4" style="background-color: #fafafa;">

                            <ul class="nav nav-tabs mb-4" id="poTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold px-4" id="dc-tab" data-bs-toggle="tab"
                                        data-bs-target="#dc-pane" type="button" role="tab" aria-controls="dc-pane"
                                        aria-selected="true">
                                        <i class="bi bi-box-seam me-2"></i>PO Bahan Baku DC
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold px-4" id="supplier-tab" data-bs-toggle="tab"
                                        data-bs-target="#supplier-pane" type="button" role="tab"
                                        aria-controls="supplier-pane" aria-selected="false">
                                        <i class="bi bi-truck me-2"></i>PO Bahan Baku Supplier
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="poTabsContent">

                                <div class="tab-pane fade show active" id="dc-pane" role="tabpanel"
                                    aria-labelledby="dc-tab" tabindex="0">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div
                                                class="card p-3 border-0 shadow-sm rounded-4 bg-white border-top border-primary border-3">
                                                <h6 class="fw-bold border-bottom pb-2 mb-3 text-dark fs-6">Bahan Baku
                                                    (PO ke DC)</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Nama Outlet</label>
                                                        @if($myOutlet)
                                                            <input type="hidden" name="outlet_id_dc"
                                                                value="{{ $myOutlet->id }}">
                                                            <input type="text"
                                                                class="form-control bg-light fw-semibold text-dark"
                                                                value="SCM-{{ $myOutlet->nama_outlet }}-LINK(NEW)" disabled
                                                                style="cursor: not-allowed;">
                                                        @else
                                                            <select name="outlet_id_dc" class="form-control select2-outlet"
                                                                style="width: 100%;">
                                                                <option value="">— Pilih Outlet —</option>
                                                                @foreach($outlets as $outlet)
                                                                    <option value="{{ $outlet->id }}">{{ $outlet->nama_outlet }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Nama Penanggungjawab</label>
                                                        <input type="text" name="nama_pemesan_dc"
                                                            class="form-control shadow-none"
                                                            placeholder="Ketik penanggungjawab...">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Tanggal Permintaan</label>
                                                        <input type="date" name="tgl_permintaan_dc"
                                                            value="{{ date('Y-m-d') }}"
                                                            class="form-control shadow-none">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                                                <div
                                                    class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                                    <h6 class="fw-bold text-dark mb-0 fs-6">Item Bahan Baku DC</h6>
                                                    <button type="button" id="addRowDC"
                                                        class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center">
                                                        <i class="bi bi-plus-lg me-1"></i> Tambah Item
                                                    </button>
                                                </div>
                                                <div class="table-responsive border rounded-3">
                                                    <table class="table table-hover align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 60%;"
                                                                    class="text-secondary small fw-semibold">Produk /
                                                                    Bahan Baku</th>
                                                                <th style="width: 25%;"
                                                                    class="text-secondary small fw-semibold">Qty Order
                                                                </th>
                                                                <th style="width: 15%;"
                                                                    class="text-center text-secondary small fw-semibold">
                                                                    Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="produkBodyDC">
                                                            <tr>
                                                                <td class="p-2">
                                                                    <select name="bahan_id_dc[]"
                                                                        class="form-control select-bahan-searchable"
                                                                        style="width:100%;">
                                                                        <option value="">🔍 Cari Bahan</option>
                                                                        @foreach($bahansDC as $bahan)
                                                                            <option value="{{ $bahan->id }}"
                                                                                data-unit-id="{{ $bahan->purchase_unit_id ?? '' }}"
                                                                                data-satuan="{{ $bahan->nama_purchase_unit ?? '' }}">
                                                                                {{ $bahan->nama_bahan }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td class="p-2">
                                                                    <div class="input-group">
                                                                        <input type="number" name="jumlah_dc[]"
                                                                            class="form-control shadow-none text-center fw-bold"
                                                                            min="1" placeholder="0" step="0.01">
                                                                        <span
                                                                            class="input-group-text bg-light text-muted satuan-display"
                                                                            style="font-size:0.75rem; min-width:50px; justify-content: center;">—</span>
                                                                    </div>
                                                                    <input type="hidden" name="unit_id_dc[]"
                                                                        class="unit-id-input" value="">
                                                                </td>
                                                                <td class="text-center p-2">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger removeRow rounded-pill p-1 d-inline-flex align-items-center justify-content-center"
                                                                        style="width:28px; height:28px;">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="supplier-pane" role="tabpanel"
                                    aria-labelledby="supplier-tab" tabindex="0">
                                    <div class="row g-4">
                                        <div class="col-12">
                                            <div
                                                class="card p-3 border-0 shadow-sm rounded-4 bg-white border-top border-success border-3">
                                                <h6 class="fw-bold border-bottom pb-2 mb-3 text-dark fs-6">Bahan Baku
                                                    (Direct Supplier)</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Nama Outlet</label>
                                                        @if($myOutlet)
                                                            <input type="hidden" name="outlet_id_supplier"
                                                                value="{{ $myOutlet->id }}">
                                                            <input type="text"
                                                                class="form-control bg-light fw-semibold text-dark"
                                                                value="SCM-{{ $myOutlet->nama_outlet }}-LINK(NEW)" disabled
                                                                style="cursor: not-allowed;">
                                                        @else
                                                            <select name="outlet_id_supplier"
                                                                class="form-control select2-outlet" style="width: 100%;">
                                                                <option value="">— Pilih Outlet —</option>
                                                                @foreach($outlets as $outlet)
                                                                    <option value="{{ $outlet->id }}">{{ $outlet->nama_outlet }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Nama Penanggungjawab</label>
                                                        <input type="text" name="nama_pemesan_supplier"
                                                            class="form-control shadow-none"
                                                            placeholder="Ketik penanggungjawab...">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Tanggal Permintaan</label>
                                                        <input type="date" name="tgl_permintaan_supplier"
                                                            value="{{ date('Y-m-d') }}"
                                                            class="form-control shadow-none">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                                                <div
                                                    class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                                    <h6 class="fw-bold text-dark mb-0 fs-6">Item Bahan Baku Supplier
                                                    </h6>
                                                    <button type="button" id="addRowSupplier"
                                                        class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center">
                                                        <i class="bi bi-plus-lg me-1"></i> Tambah Item
                                                    </button>
                                                </div>
                                                <div class="table-responsive border rounded-3">
                                                    <table class="table table-hover align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 60%;"
                                                                    class="text-secondary small fw-semibold">Produk /
                                                                    Bahan Baku</th>
                                                                <th style="width: 25%;"
                                                                    class="text-secondary small fw-semibold">Qty Order
                                                                </th>
                                                                <th style="width: 15%;"
                                                                    class="text-center text-secondary small fw-semibold">
                                                                    Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="produkBodySupplier">
                                                            <tr>
                                                                <td class="p-2">
                                                                    <select name="bahan_id_supplier[]"
                                                                        class="form-control select-bahan-searchable"
                                                                        style="width:100%;">
                                                                        <option value="">🔍 Cari Bahan</option>
                                                                        @foreach($bahansSupplier as $bahan)
                                                                            <option value="{{ $bahan->id }}"
                                                                                data-unit-id="{{ $bahan->purchase_unit_id ?? '' }}"
                                                                                data-satuan="{{ $bahan->nama_purchase_unit ?? '' }}">
                                                                                {{ $bahan->nama_bahan }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td class="p-2">
                                                                    <div class="input-group">
                                                                        <input type="number" name="jumlah_supplier[]"
                                                                            class="form-control shadow-none text-center fw-bold"
                                                                            min="1" placeholder="0" step="0.01">
                                                                        <span
                                                                            class="input-group-text bg-light text-muted satuan-display"
                                                                            style="font-size:0.75rem; min-width:50px; justify-content: center;">—</span>
                                                                    </div>
                                                                    <input type="hidden" name="unit_id_supplier[]"
                                                                        class="unit-id-input" value="">
                                                                </td>
                                                                <td class="text-center p-2">
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger removeRow rounded-pill p-1 d-inline-flex align-items-center justify-content-center"
                                                                        style="width:28px; height:28px;">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="row g-4 mt-1">
                                <div class="col-12">
                                    <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                                        <label class="form-label text-dark mb-2">Catatan Khusus Permintaan
                                            Logistik</label>
                                        <textarea name="catatan" class="form-control shadow-none" rows="2"
                                            placeholder="Tambahkan catatan instruksi khusus pengiriman (opsional)..."></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer bg-light p-3">
                            <button type="button" class="btn btn-light border px-4"
                                data-bs-dismiss="modal">Batalkan</button>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="bi bi-send-fill me-2"></i>Kirim Berkas PO
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>


        <section class="maincard">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-3 px-4 border-bottom bg-white">
                    <div>
                        <h6 class="fw-bold text-dark mb-1 fs-5">
                            <i class="bi bi-box-seam me-2" style="color: var(--primary);"></i> Daftar Riwayat Purchase
                            Order
                        </h6>
                        <small class="text-muted" style="font-size: 0.8rem;">Kelola dokumen pengajuan, monitoring
                            perjalanan, dan verifikasi muatan.</small>
                    </div>
                    <button type="button" class="btn btn-primary shadow-sm px-4 d-flex align-items-center"
                        data-bs-toggle="modal" data-bs-target="#modalRequestPO">
                        <i class="bi bi-plus-lg me-1.5"></i> Buat Pengajuan PO
                    </button>
                </div>

                <div class="table-responsive">
                    <table id="orderTable" class="table table-hover align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center py-3" style="width: 5%;">No</th>
                                <th class="text-center py-3">Nomor PO</th>
                                <th class="text-center py-3">Tanggal Permintaan</th>
                                <th class="text-center py-3">Status Verifikasi</th>
                                <th style="width: 20%;" class="text-center py-3">Opsi Tindakan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($dataPO as $index => $po)
                                <tr>
                                    <td class="text-muted small fw-bold">{{ $index + 1 }}</td>
                                    <td><span class="fw-bold"
                                            style="color: var(--primary); font-size:13.5px;">{{ $po->no_po }}</span></td>
                                    <td class="small text-secondary"><i
                                            class="bi bi-calendar3 text-muted me-1.5"></i>{{ $po->tgl_permintaan }}</td>
                                    <td>
                                        @php
                                            // Soft Subtle Badge System Colors
                                            $statusColors = [
                                                'Waiting' => 'bg-warning-subtle text-warning-emphasis border border-warning',
                                                'Approved' => 'bg-success-subtle text-success-emphasis border border-success',
                                                'Rejected' => 'bg-danger-subtle text-danger-emphasis border border-danger',
                                                'In Transit' => 'bg-primary-subtle text-primary-emphasis border border-primary',
                                                'Recieved' => 'bg-info-subtle text-info-emphasis border border-info',
                                                'All Checked' => 'bg-success-subtle text-success-emphasis border border-success'
                                            ];
                                            $badgeClass = $statusColors[$po->status] ?? 'bg-secondary-subtle text-secondary-emphasis';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} px-3 py-1.5 rounded-pill"
                                            style="font-size: 0.75rem; --bs-border-opacity: .25;">
                                            {{ $po->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            @if($po->status == 'In Transit' || $po->status == 'Retur Requested' || $po->status == 'Partial Received')
                                                <button type="button"
                                                    class="btn btn-warning btn-sm btn-checking text-dark shadow-sm d-inline-flex align-items-center justify-content-center"
                                                    data-id="{{ $po->id }}" data-nopo="{{ $po->no_po }}" title="Checking Barang"
                                                    style="width:32px; height:32px;">
                                                    <i class="bi bi-list-check"></i>
                                                </button>
                                            @endif
                                            <button type="button"
                                                class="btn btn-info btn-sm btn-view-po text-white shadow-sm d-inline-flex align-items-center justify-content-center"
                                                data-id="{{ $po->id }}" data-no-po="{{ $po->no_po }}"
                                                data-status="{{ $po->status }}" data-tgl-req="{{ $po->tgl_permintaan }}"
                                                data-items="{{ json_encode($po->items) }}" data-bs-toggle="modal"
                                                data-bs-target="#detailModal" title="Lihat Detail"
                                                style="background-color: #0284c7; border-color:#0284c7; width:32px; height:32px;">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-danger shadow-sm d-inline-flex align-items-center justify-content-center"
                                                onclick="confirmDelete('{{ $po->id }}')" title="Hapus Berkas"
                                                style="background-color: var(--danger); border-color:var(--danger); width:32px; height:32px;">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                            <form id="deleteForm{{ $po->id }}" action="{{ route('po.delete', $po->id) }}"
                                                method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content shadow-lg" style="border-radius: 20px; overflow:hidden;">
                            <div class="modal-header border-bottom px-4" style="background-color: #ffffff;">
                                <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center"
                                    id="detailLabel">
                                    <i class="bi bi-file-earmark-text-fill text-muted me-2.5 fs-5"></i>Detail Ringkasan
                                    Purchase Order
                                </h5>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4" id="modalContent" style="background-color: #fafafa;">
                            </div>
                            <div class="modal-footer border-top px-4" style="background-color: #ffffff;">
                                <button type="button" id="btn-recieved"
                                    class="btn btn-info text-white px-4 btn-update-status shadow-sm fw-semibold"
                                    data-status="Recieved" style="background-color: #0284c7; border-color:#0284c7;"><i
                                        class="bi bi-check2-all me-1.5"></i> Konfirmasi Penerimaan</button>
                                <button type="button" class="btn btn-light border px-4 text-secondary small"
                                    data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>

                <form id="formPenerimaanUtama" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="po_id" id="checking_po_id">
                    <input type="hidden" name="no_po" id="hidden_no_po">
                    <input type="hidden" name="gd_id" id="checking_gd_id">
                    <input type="hidden" name="outlet_id" id="checking_outlet_id" value="{{ $myOutlet->id ?? '' }}">

                    <div class="modal fade" id="modalChecking" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
                            <div class="modal-content shadow-lg" style="border-radius: 20px; overflow:hidden;">
                                <div class="modal-header bg-white border-bottom px-4">
                                    <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center">
                                        <i class="bi bi-clipboard-check me-2.5 fs-4"
                                            style="color: var(--warn);"></i>Pemeriksaan Fisik Kargo - <span
                                            id="txt_no_po" class="text-primary ms-1.5"></span>
                                    </h5>
                                    <button type="button" class="btn-close shadow-none"
                                        data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body p-4" style="background-color: #fafafa;">
                                    <div class="card p-0 border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                                        <div class="p-3 border-bottom fw-bold text-dark bg-white small text-uppercase"
                                            style="letter-spacing: 0.5px;">
                                            <i class="bi bi-box-seam me-2 text-muted"></i>Manifes Bahan Baku Umum
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0 align-middle">
                                                <thead class="table-light"
                                                    style="font-size: 0.8rem; text-transform:uppercase; letter-spacing:0.3px;">
                                                    <tr>
                                                        <th class="text-secondary py-2.5">Nama Barang</th>
                                                        <th class="text-secondary py-2.5">Supplier</th>
                                                        <th class="text-secondary py-2.5">Satuan</th>
                                                        <th class="text-center text-secondary py-2.5">Qty PO</th>
                                                        <th class="text-center text-secondary py-2.5">Qty Terima</th>
                                                        <th class="text-center text-danger py-2.5">Qty Kurang</th>
                                                        <th id="th-alasan" style="display:none;"
                                                            class="text-secondary py-2.5">Alasan Selisih</th>
                                                        <th class="text-end text-secondary py-2.5 px-3">Total Dasar</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="bodyCheckingUmum"></tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div id="sectionAyam" style="display:none;" class="mb-4">
                                        <div class="card p-0 border-0 shadow-sm rounded-4 bg-white overflow-hidden"
                                            style="border: 1px solid rgba(225, 29, 72, 0.15) !important;">
                                            <div class="text-danger p-3 border-bottom fw-bold small text-uppercase bg-white"
                                                style="letter-spacing: 0.5px;">
                                                <i class="bi bi-exclamation-triangle-fill me-2"
                                                    style="color: var(--danger);"></i>Log Muatan Penerimaan Ayam Khusus
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0 align-middle">
                                                    <thead
                                                        style="font-size: 0.8rem; text-transform:uppercase; background-color:#fff5f5; letter-spacing:0.3px;">
                                                        <tr>
                                                            <th class="text-danger py-2.5">Nama Bahan</th>
                                                            <th class="text-danger py-2.5">Supplier</th>
                                                            <th class="text-center text-danger py-2.5">Qty PO</th>
                                                            <th class="text-center text-danger py-2.5">Ayam Besar</th>
                                                            <th class="text-center text-danger py-2.5">Ayam Kecil</th>
                                                            <th class="text-center text-primary py-2.5">Total Pcs</th>
                                                            <th class="text-center text-danger py-2.5">Pack</th>
                                                            <th class="text-center text-danger py-2.5">Gramase</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bodyCheckingAyam"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4" style="border-color: var(--border);">

                                    <div class="row justify-content-center">
                                        <div class="col-md-6 text-center">
                                            <label class="fw-bold mb-2 text-dark small text-uppercase"
                                                style="letter-spacing:0.5px;"><i
                                                    class="bi bi-camera me-2 text-muted"></i>Dokumentasi Bukti Fisik
                                                Barang</label>
                                            <div class="camera-area border rounded-4 bg-light mb-3 d-flex align-items-center justify-content-center overflow-hidden shadow-sm"
                                                style="height: 220px; position: relative; background-color: #ffffff !important;">
                                                <div id="camera_placeholder" class="text-muted">
                                                    <i class="bi bi-image fs-1 d-block mb-2 text-light-grid"
                                                        style="color: #cbd5e1;"></i>
                                                    <small class="fw-medium text-secondary">Modul Kamera Belum
                                                        Diaktifkan</small>
                                                </div>

                                                <video id="video_barang" width="100%" height="100%" autoplay playsinline
                                                    style="display:none; object-fit: cover; position: absolute; top: 0; left: 0;"></video>
                                                <canvas id="canvas_barang" style="display:none;"></canvas>
                                                <img id="img_barang" src="" class="img-fluid w-100 h-100"
                                                    style="display:none; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 2;">
                                            </div>
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button"
                                                    class="btn btn-outline-primary shadow-sm px-3 border-0 bg-light border-1 text-secondary"
                                                    onclick="startCamera('barang'); document.getElementById('camera_placeholder').style.display='none';">
                                                    <i class="bi bi-webcam me-1.5 text-primary"></i> Hubungkan Kamera
                                                </button>
                                                <button type="button" id="btn_snap_barang"
                                                    class="btn btn-danger shadow-sm px-3"
                                                    onclick="takeSnapshot('barang')"
                                                    style="display:none; background-color: var(--danger); border-color:var(--danger);">
                                                    <i class="bi bi-camera-fill me-1.5"></i> Ambil Foto Fisik
                                                </button>
                                            </div>
                                            <input type="hidden" name="foto_barang_base64" id="input_foto_barang">
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer bg-light px-4">
                                    <button type="button" class="btn btn-light border px-4 text-secondary small"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="button" id="btnSimpanPenerimaan"
                                        class="btn btn-success px-5 shadow-sm fw-semibold"
                                        style="background-color: #16a34a; border-color: #16a34a;">
                                        <i class="bi bi-save me-2"></i>Simpan Hasil Pemeriksaan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            $('#orderTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
            });
        });

        $('#btnSimpanPenerimaan').on('click', function () {
            // Validasi minimal ada 1 item yang diisi
            let adaYangDiisi = false;

            $('.qty-umum').each(function () {
                if (parseFloat($(this).val()) > 0) {
                    adaYangDiisi = true;
                    return false;
                }
            });
            if (!adaYangDiisi) {
                $('.qty-ayam-total').each(function () {
                    if (parseFloat($(this).val()) > 0) {
                        adaYangDiisi = true;
                        return false;
                    }
                });
            }

            if (!adaYangDiisi) {
                Swal.fire('Peringatan', 'Isi minimal 1 qty barang yang diterima.', 'warning');
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...'
            );

            $.ajax({
                url: "{{ route('recieve.store') }}",
                method: 'POST',
                data: $('#formPenerimaanUtama').serialize(),
                success: function (res) {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-2"></i>Simpan Penerimaan');

                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            confirmButtonText: 'OK'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-2"></i>Simpan Penerimaan');
                    const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan server.';
                    Swal.fire('Error!', msg, 'error');
                }
            });
        });

        // 1. GLOBAL VARIABLE UNTUK KAMERA
        let currentStream = null;

        // Fungsi Buka Kamera
        function startCamera(type) {
            const video = document.getElementById('video_' + type);
            const btnSnap = document.getElementById('btn_snap_' + type);
            video.style.display = 'block';
            btnSnap.style.display = 'inline-block';

            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: "environment"
                }
            })
                .then(stream => {
                    currentStream = stream;
                    video.srcObject = stream;
                })
                .catch(err => alert("Kamera tidak bisa dibuka!"));
        }

        // Fungsi Jepret
        function takeSnapshot(type) {
            const video = document.getElementById('video_' + type);
            const canvas = document.getElementById('canvas_' + type);
            const img = document.getElementById('img_' + type);
            const input = document.getElementById('input_foto_' + type);

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            // Kualitas 0.4 supaya size kecil & gak gagal store (Base64)
            const dataURL = canvas.toDataURL('image/jpeg', 0.4);
            img.src = dataURL;
            img.style.display = 'block';
            input.value = dataURL;

            // Matikan kamera
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }
            video.style.display = 'none';
            document.getElementById('btn_snap_' + type).style.display = 'none';
        }

        $(document).on('click', '.btn-checking', function () {
            const poId = $(this).data('id');
            const noPo = $(this).data('nopo');

            // Reset
            $('#checking_po_id').val(poId);
            $('#hidden_no_po').val(noPo);
            $('#checking_gd_id').val('');
            $('#txt_no_po').text(noPo);
            $('#bodyCheckingUmum').html('<tr><td colspan="8" class="text-center"><div class="spinner-border spinner-border-sm text-warning"></div> Memuat...</td></tr>');
            $('#bodyCheckingAyam').html('');
            $('#sectionAyam').hide();
            $('#th-alasan').hide();
            $('#input_foto_barang').val('');
            $('#img_barang').hide();
            $('#info-gd-banner').remove();

            $('#modalChecking').modal('show');

            // 1. Ambil detail bahan dari PO
            $.get('/dashboard-outlet/po-detail/' + poId, function (response) {
                let htmlUmum = '';
                let htmlAyam = '';

                if (!response.details || response.details.length === 0) {
                    $('#bodyCheckingUmum').html(
                        '<tr><td colspan="8" class="text-center text-success fw-bold">Semua barang sudah diterima.</td></tr>'
                    );
                    return;
                }

                response.details.forEach((item, index) => {
                    let sisa = parseFloat(item.jumlah) - parseFloat(item.total_diterima_sebelumnya ?? 0);
                    if (sisa <= 0) return; // item sudah full diterima, skip

                    let konv = item.conversion_factor || 1;
                    let sumber = (item.sumber_barang || '').toUpperCase();

                    // Kolom supplier — sudah fix dari SCM saat approval, read-only
                    let supplierCol = '';
                    if (sumber === 'SUPPLIER') {
                        const supName = item.supplier_name || '-- Belum dipilih --';
                        const supId = item.supplier_id || 0;
                        supplierCol = `
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-warning text-dark" style="font-size:11px;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${supName}">
                                    ${supName}
                                </span>
                                <input type="hidden" name="items[${index}][supplier_id]" value="${supId}">
                            </div>`;
                    } else {
                        supplierCol = `
                            <span class="badge bg-primary" style="font-size:11px;">DC / Gudang</span>
                            <input type="hidden" name="items[${index}][supplier_id]" value="0">`;
                    }

                    let isAyam = item.is_split_ayam == true || item.is_split_ayam == 1;

                    if (isAyam) {
                        $('#sectionAyam').show();
                        htmlAyam += `
                <tr>
                    <td>
                        <strong>${item.nama_bahan}</strong>
                        <span class="badge bg-danger bg-opacity-10 text-danger ms-1" style="font-size:10px;">
                            ${sumber === 'SUPPLIER' ? 'Supplier' : 'DC'}
                        </span>
                        <input type="hidden" name="items[${index}][bahan_id]"  value="${item.bahan_id}">
                        <input type="hidden" name="items[${index}][unit_id]"   value="${item.unit_id}">
                        <input type="hidden" name="items[${index}][qty_po]"    value="${sisa}">
                        <input type="hidden" name="items[${index}][konversi]"  value="${konv}">
                    </td>
                    <td>${supplierCol}</td>
                    <td class="text-center fw-bold">${sisa} Ekor</td>
                    <td>
                        <input type="number" name="items[${index}][qty_besar]"
                               class="form-control qty-besar text-center" value="0" min="0">
                    </td>
                    <td>
                        <input type="number" name="items[${index}][qty_kecil]"
                               class="form-control qty-kecil text-center" value="0" min="0">
                    </td>
                    <td>
                        <input type="number" name="items[${index}][qty_terima]"
                               class="form-control qty-ayam-total text-center bg-light" readonly>
                    </td>
                    <td>
                        <input type="number" name="items[${index}][qty_pack]"
                               class="form-control text-center" value="0" min="0">
                    </td>
                    <td>
                        <input type="text"  class="form-control total-display text-end bg-light" readonly>
                        <input type="hidden" class="konv" value="${konv}">
                    </td>
                </tr>`;
                    } else {
                        htmlUmum += `
                <tr>
                    <td>
                        <strong>${item.nama_bahan}</strong>
                        <span class="badge bg-opacity-10 ms-1 ${sumber === 'SUPPLIER' ? 'bg-warning text-warning' : 'bg-primary text-primary'}" style="font-size:10px;">
                            ${sumber === 'SUPPLIER' ? 'Supplier' : 'DC'}
                        </span>
                        <input type="hidden" name="items[${index}][bahan_id]"  value="${item.bahan_id}">
                        <input type="hidden" name="items[${index}][unit_id]"   value="${item.unit_id}">
                        <input type="hidden" name="items[${index}][qty_po]"    value="${sisa}">
                        <input type="hidden" name="items[${index}][konversi]"  value="${konv}">
                    </td>
                    <td>${supplierCol}</td>
                    <td class="text-center">${item.satuan || 'Pcs'}</td>
                    <td class="text-center fw-bold">${sisa}</td>
                    <td>
                        <input type="number" name="items[${index}][qty_terima]"
                               class="form-control qty-umum text-center"
                               value="${sisa}" step="0.01" min="0">
                    </td>
                    <td>
                        <input type="number" name="items[${index}][qty_kurang]"
                               class="form-control qty-kurang text-center bg-light"
                               value="0" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control total-display text-end bg-light" readonly>
                        <input type="hidden" class="konv" value="${konv}">
                    </td>
                    <td class="alasan-wrapper" style="display:none;">
                        <select name="items[${index}][alasan_kurang]" class="form-control form-control-sm">
                            <option value="">-- Alasan --</option>
                            <option value="OUT_OF_STOCK">Stok Habis</option>
                            <option value="DAMAGED">Rusak</option>
                            <option value="WRONG_ITEM">Salah Kirim</option>
                            <option value="EXPIRED">Kadaluarsa</option>
                            <option value="OTHER">Lainnya</option>
                        </select>
                    </td>
                </tr>`;
                    }
                });

                $('#bodyCheckingUmum').html(
                    htmlUmum || '<tr><td colspan="8" class="text-center text-muted">Semua bahan umum sudah diterima.</td></tr>'
                );
                $('#bodyCheckingAyam').html(htmlAyam);
                calculateAll();
            });

            // 2. Cek GD aktif dari SCM → isi gd_id otomatis + tampilkan banner info
            $.get('/dashboard-outlet/active-gd/' + poId, function (res) {
                if (res.status === 'success') {
                    $('#checking_gd_id').val(res.gd_id);

                    const banner = `
            <div id="info-gd-banner" class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                <i class="bi bi-truck fs-5"></i>
                <div>
                    <strong>Pengiriman dari DC:</strong> ${res.gd_number}
                    &nbsp;·&nbsp; Driver: <strong>${res.driver_name ?? '-'}</strong>
                    &nbsp;·&nbsp; Nopol: <strong>${res.vehicle_plate ?? '-'}</strong>
                    &nbsp;·&nbsp; Tgl Kirim: <strong>${res.delivery_date ?? '-'}</strong>
                </div>
            </div>`;

                    // Sisipkan banner sebelum tabel bahan umum
                    $('.modal-body .table-responsive').first().before(banner);
                }
                // Jika tidak ada GD aktif → tidak apa-apa, form tetap jalan
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            @if(!$myOutlet)
                $('.select2-outlet').select2({
                    dropdownParent: $('#modalRequestPO'),
                    placeholder: '— Pilih Outlet —',
                    allowClear: true
                });
            @endif
        });

        $(document).on('click', '#permintaanDate', function () {
            try {
                this.showPicker();
            } catch (e) {
                $(this).focus();
            }
        });
    </script>

    <script>
        $(document).on('click', '.btn-checking', function () {
            let poId = $(this).data('id');
            let noPo = $(this).data('nopo');

            console.log("Klik PO ID: " + poId);

            $('#modalChecking').modal('show');
            $('#modalChecking #po_id').val(poId);
            $('#modalChecking #no_po_text').text(noPo);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const detailModal = document.getElementById('detailModal');

            detailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                // 1. Ambil data dasar
                const id = button.getAttribute('data-id');
                const noPo = button.getAttribute('data-no-po');
                const status = button.getAttribute('data-status');
                const tglReq = button.getAttribute('data-tgl-req');
                const namaOutlet = button.getAttribute('data-nama-outlet') || '-';
                const items = JSON.parse(button.getAttribute('data-items') || '[]');

                // Simpan ID ke jQuery data state
                $(detailModal).data('current-id', id);

                // Set Title Modal (Ramping & Simpel)
                const modalTitle = detailModal.querySelector('.modal-title');
                modalTitle.innerHTML = `<i class="bi bi-file-earmark-text text-muted me-2" style="font-size: 0.95rem;"></i>Detail PO: <span style="color: var(--primary); font-weight: 700;">${noPo}</span>`;

                // Generate baris tabel barang (Compact Padding py-1.5)
                let itemRows = '';
                if (items.length > 0) {
                    items.forEach((item, index) => {
                        itemRows += `
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td class="text-center text-muted py-1.5" style="font-size: 0.82rem;">${index + 1}</td>
                        <td class="text-dark fw-bold py-1.5 text-start" style="font-size: 0.85rem;">${item.nama_bahan}</td>
                        <td class="text-center text-dark fw-bold py-1.5" style="font-size: 0.85rem;">${item.jumlah}</td>
                        <td class="text-secondary py-1.5 text-start" style="font-size: 0.82rem;">${item.satuan || '-'}</td>
                    </tr>`;
                    });
                } else {
                    itemRows = '<tr><td colspan="4" class="text-center text-muted small py-3">Tidak ada detail barang baku</td></tr>';
                }

                // Atur warna badge status soft
                let badgeStyle = 'bg-primary-subtle text-primary border border-primary';
                if (status === 'Approved') badgeStyle = 'bg-success-subtle text-success border border-success';
                if (status === 'Rejected') badgeStyle = 'bg-danger-subtle text-danger border border-danger';
                if (status === 'Waiting') badgeStyle = 'bg-warning-subtle text-warning-emphasis border border-warning';

                // 2. SUNTIK LAYOUT FULL-WIDTH COMPACT (Kembali ke Alur Awal tapi Ringkas)
                const modalBody = detailModal.querySelector('#modalContent');
                modalBody.innerHTML = `
                <div class="card p-3 border-0 shadow-sm rounded-3 mb-3 bg-white" style="font-size: 0.85rem;">
                    <h6 class="fw-bold text-dark mb-3" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="bi bi-info-circle me-1.5 text-muted"></i> Order Information
                    </h6>
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small" style="font-size: 0.72rem;">Nomor Dokumen</span>
                            <span class="text-dark fw-bold">${noPo}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small" style="font-size: 0.72rem;">Target Outlet</span>
                            <span class="fw-semibold" style="color: var(--primary);">${namaOutlet}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small" style="font-size: 0.72rem;">Tanggal Pengajuan</span>
                            <span class="text-dark"><i class="bi bi-calendar-event me-1 text-muted"></i> ${tglReq}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <span class="text-muted d-block small mb-1" style="font-size: 0.72rem;">Status Logistik</span>
                            <span class="badge ${badgeStyle} rounded-pill" style="font-size: 10px; padding: 4px 10px; --bs-border-opacity: .25;">
                                ${status}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card p-3 border-0 shadow-sm rounded-3 bg-white overflow-hidden">
                    <h6 class="fw-bold text-dark mb-2.5" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="bi bi-box-seam me-1.5 text-muted"></i> Product Detail List
                    </h6>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-sm table-hover align-middle mb-0 text-center">
                            <thead class="table-light text-muted small text-uppercase" style="font-size: 0.7rem; border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                                <tr>
                                    <th class="py-2" width="8%">No</th>
                                    <th class="text-start py-2" width="52%">Nama Bahan Baku</th>
                                    <th class="py-2" width="20%">Qty</th>
                                    <th class="text-start py-2" width="20%">Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemRows}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            });
        });

        // 3. Handler AJAX Update Status PO (Tetap aman tanpa ubah fungsional)
        $(document).ready(function () {
            $('.btn-update-status').click(function () {
                let status = $(this).data('status');
                let id = $('#detailModal').data('current-id');

                Swal.fire({
                    title: 'Memproses Data',
                    text: 'Sedang memperbarui status PO...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: "{{ route('update.status.po') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        status: status
                    },
                    success: function (response) {
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Status diubah ke ' + status
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function (xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message ?? 'Terjadi kesalahan sistem'
                        });
                    }
                });
            });
        });

        function checkGlobalAlasanHeader() {
            let hasKurang = false;
            $('.qty-kurang').each(function () {
                if (parseFloat($(this).val()) > 0) {
                    hasKurang = true;
                }
            });

            if (hasKurang) {
                $('#th-alasan').show();
            } else {
                $('#th-alasan').hide();
            }
        }

        // 2. Kalkulasi Otomatis untuk Bahan Baku Umum (Qty Kurang & Total Dasar)
        $(document).on('input', '.qty-umum', function () {
            let $row = $(this).closest('tr');

            // Ambil nilai PO, Terima, dan Konversi
            let qtyPo = parseFloat($row.find('input[name*="[qty_po]"]').val()) || 0;
            let qtyTerima = parseFloat($(this).val()) || 0;
            let konv = parseFloat($row.find('.konv').val()) || 1;

            // Hitung Qty Kurang (Selisih)
            let qtyKurang = qtyPo - qtyTerima;
            if (qtyKurang < 0) qtyKurang = 0; // Jika dikirim lebih dari PO, selisih kurang dianggap 0

            $row.find('.qty-kurang').val(qtyKurang);

            // Munculkan dropdown Alasan jika barang kurang
            if (qtyKurang > 0) {
                $row.find('.alasan-wrapper').show();
                $row.find('.alasan-wrapper select').prop('required', true);
            } else {
                $row.find('.alasan-wrapper').hide();
                $row.find('.alasan-wrapper select').val('').prop('required', false);
            }

            // Hitung Total Dasar (Qty Terima * Konversi)
            let totalDasar = qtyTerima * konv;
            $row.find('.total-display').val(totalDasar.toLocaleString('id-ID'));

            // Cek apakah header tabel alasan perlu dimunculkan
            checkGlobalAlasanHeader();
        });

        // 3. Kalkulasi Otomatis Khusus Ayam (Besar + Kecil = Total Pcs & Gramase)
        $(document).on('input', '.qty-besar, .qty-kecil', function () {
            let $row = $(this).closest('tr');

            let besar = parseFloat($row.find('.qty-besar').val()) || 0;
            let kecil = parseFloat($row.find('.qty-kecil').val()) || 0;
            let konv = parseFloat($row.find('.konv').val()) || 1;

            // Hitung Total Pcs
            let totalAyam = besar + kecil;
            $row.find('.qty-ayam-total').val(totalAyam);

            // Hitung Gramase
            let gramase = totalAyam * konv;
            $row.find('.total-display').val(gramase.toLocaleString('id-ID'));
        });

        // 4. Fungsi yang dipanggil oleh AJAX setelah tabel berhasil dimuat
        // Menggunakan window. agar bisa dipanggil dari blok script manapun
        window.calculateAll = function () {
            // Pancing event 'input' agar semua baris otomatis menghitung nilai default-nya
            $('.qty-umum').trigger('input');
            $('.qty-besar').trigger('input');
        }
    </script>

    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik "Ya", maka form di-submit
                    document.getElementById('deleteForm' + id).submit();
                }
            })
        }
    </script>

    <script>
        $('.btn-view-po').click(function () {
            let id = $(this).data('id');
            let status = $(this).data('status'); // Pastikan tombol View di tabel punya data-status

            // Simpan ID ke modal
            $('#detailModal').data('current-id', id);

            // LOGIKA TOMBOL:
            if (status === 'Recieved') {
                // Jika sudah diterima, kunci tombol
                $('#btn-recieved').prop('disabled', true).text('Sudah Diterima');
            } else if (status === 'All Checked') {
                // Jika sedang dikirim, tombol aktif
                $('#btn-recieved').prop('disabled', false).text('Recieved');
            } else if (status === 'In Transit') {
                // Status lainnya (misal: Pending, Packing), kunci tombol
                $('#btn-recieved').prop('disabled', true).text('Dalam Pengiriman');
            } else {
                // Status lainnya (misal: Pending, Packing), kunci tombol
                $('#btn-recieved').prop('disabled', true).text('Belum Dikirim');
            }

            // Masukkan data ke modal
            $('#modalContent').html(`
            <table class="table table-bordered">
                <tr><th>No. PO</th><td>${noPo}</td></tr>
                <tr><th>Tanggal Permintaan</th><td>${tglReq}</td></tr>
                <tr><th>Tanggal Kedatangan</th><td>${tglDatang}</td></tr>
            </table>
        `);

            // Buka modal
            var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
            myModal.show();
        });
    </script>

    <script>
        $(document).ready(function () {

            function initBahanSelect2() {
                $('.select-bahan-searchable').each(function () {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                $('.select-bahan-searchable').select2({
                    width: '100%',
                    dropdownParent: $('#modalRequestPO'),
                    placeholder: '🔍 Cari / Pilih Bahan...',
                    allowClear: true,
                    language: {
                        noResults: function () { return 'Bahan tidak ditemukan'; },
                        searching: function () { return 'Mencari...'; },
                        inputTooShort: function () { return 'Ketik minimal 1 huruf'; }
                    }
                });
            }

            function addRow(tipe) {
                let tbodyId = tipe === 'dc' ? '#produkBodyDC' : '#produkBodySupplier';
                let $tbody = $(tbodyId);

                let $firstRow = $tbody.find('tr').first();

                if ($firstRow.find('.select-bahan-searchable').data('select2')) {
                    $firstRow.find('.select-bahan-searchable').select2('destroy');
                }

                let $newRow = $firstRow.clone();

                $newRow.find('select').val('');
                $newRow.find('input[type="number"]').val('');
                $newRow.find('.unit-id-input').val('');
                $newRow.find('.satuan-display').text('—');

                $tbody.append($newRow);
                initBahanSelect2();
            }

            $('#modalRequestPO').on('shown.bs.modal', function () {
                initBahanSelect2();
            });

            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                initBahanSelect2();
            });

            $('#addRowDC').on('click', function () {
                addRow('dc');
            });

            $('#addRowSupplier').on('click', function () {
                addRow('supplier');
            });

            $(document).on('select2:select', '.select-bahan-searchable', function (e) {
                let $option = $(e.params.data.element);
                let satuan = $option.data('satuan') || '—';
                let unitId = $option.data('unit-id') || '';
                let $row = $(this).closest('tr');

                $row.find('.satuan-display').text(satuan);
                $row.find('.unit-id-input').val(unitId);
            });

            $(document).on('select2:unselect select2:clear', '.select-bahan-searchable', function () {
                let $row = $(this).closest('tr');
                $row.find('.satuan-display').text('—');
                $row.find('.unit-id-input').val('');
            });

            $(document).on('click', '.removeRow', function () {
                let $row = $(this).closest('tr');
                let $tbody = $row.closest('tbody');

                if ($tbody.find('tr').length > 1) {
                    let $sel = $row.find('.select-bahan-searchable');
                    if ($sel.data('select2')) { $sel.select2('destroy'); }
                    $row.remove();
                } else {
                    $row.find('.select-bahan-searchable').val(null).trigger('change');
                    $row.find('input[type="number"]').val('');
                    $row.find('.unit-id-input').val('');
                    $row.find('.satuan-display').text('—');
                }
            });

        });
    </script>

    <script>
        $(document).on('click', '.btn-retur', function () {
            const poId = $(this).data('id');
            const noPo = $(this).data('nopo');

            $('#txt_no_po_retur').text(noPo);
            $('#retur_po_id').val(poId);

            // Pakai colspan="3" karena kita sepakat cuma ada 3 kolom
            $('#returTableBody').html('<tr><td colspan="3" class="text-center">Memuat...</td></tr>');
            $('#modalRetur').modal('show');

            $.ajax({
                url: '/dashboard-outlet/po-receive-detail/' + poId,
                type: 'GET',
                success: function (res) {
                    let html = '';

                    if (!res.details || res.details.length === 0) {
                        html = '<tr><td colspan="3" class="text-center">Tidak ada data bahan</td></tr>';
                    } else {
                        res.details.forEach((item, i) => {
                            html += `
                        <tr>
                            <td>
                                <span class="fw-bold text-uppercase">${item.nama_bahan}</span>
                                <input type="hidden" name="returns[${i}][bahan_id]" value="${item.bahan_id}">
                            </td>

                            <td>
                                <div class="input-group">
                                    <input type="number" 
                                           name="returns[${i}][qty_return]" 
                                           class="form-control" 
                                           step="0.01" 
                                           placeholder="0">
                                    <span class="input-group-text bg-light">${item.satuan || 'unit'}</span>
                                </div>
                            </td>

                            <td>
                                <input type="text" 
                                       name="returns[${i}][alasan]" 
                                       class="form-control" 
                                       placeholder="Contoh: Rusak/Busuk">
                            </td>
                        </tr>`;
                        });
                    }
                    $('#returTableBody').html(html);
                },
                error: function () {
                    $('#returTableBody').html('<tr><td colspan="3" class="text-center text-danger">Gagal memuat data.</td></tr>');
                }
            });
        });
    </script>
</body>

</html>
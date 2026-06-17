@section('title', 'Master Stock Operasional')
@section('breadcrumb', 'Master Data / Stock Operasional')

@include('Temp.Investor.header')

@php
    /*
    |--------------------------------------------------------------------------
    | Permission Driven View
    |--------------------------------------------------------------------------
    | Tidak ada lagi penguncian berdasarkan role seperti SPV/TM.
    | Semua tab, tombol, modal, dan aksi ditentukan dari permission role_permissions.
    |
    | master.qcr.index / master.qcr.dataqcr hanya untuk membuka halaman QCR/data.
    | CRUD master stock operasional tetap memakai permission masing-masing.
    */
    $hargaOutletOnly = false;

    $canMenuView = hasAnyPermission(['menu.store', 'menu.update', 'menu.destroy', 'menu.export']);
    $canMenuStore = hasPermission('menu.store');
    $canMenuUpdate = hasPermission('menu.update');
    $canMenuDestroy = hasPermission('menu.destroy');
    $canMenuExport = hasPermission('menu.export');

    $canBahanView = hasAnyPermission(['bahan.store', 'bahan.update', 'bahan.destroy', 'bahan.export']);
    $canBahanStore = hasPermission('bahan.store');
    $canBahanUpdate = hasPermission('bahan.update');
    $canBahanDestroy = hasPermission('bahan.destroy');
    $canBahanExport = hasPermission('bahan.export');

    $canBahanDscView = hasAnyPermission(['bahan-dsc.store', 'bahan-dsc.update', 'bahan-dsc.destroy']);
    $canBahanDscStore = hasPermission('bahan-dsc.store');
    $canBahanDscUpdate = hasPermission('bahan-dsc.update');
    $canBahanDscDestroy = hasPermission('bahan-dsc.destroy');

    $canHargaOutletView = hasAnyPermission(['master.qcr.dataqcr', 'master.qcr.index']);
    $canHargaOutletEdit = hasAnyPermission(['master.qcr.uangplus.save', 'master.qcr.hide.save']);

    // Submenu baru: Bahan HO & MITRA
    // Permission sementara mengikuti akses harga outlet agar tidak mengganggu permission lama.
    $canBahanHoMitraView = $canHargaOutletView;
    $canBahanHoMitraEdit = $canHargaOutletEdit;

    $canBomView = hasAnyPermission(['bum.store', 'bum.update', 'bum.destroy', 'bum.export', 'bum.detail']);
    $canBomStore = hasPermission('bum.store');
    $canBomUpdate = hasPermission('bum.update');
    $canBomDestroy = hasPermission('bum.destroy');
    $canBomExport = hasPermission('bum.export');
    $canBomDetail = hasPermission('bum.detail');

    $canStockView = hasAnyPermission(['stock.store', 'stock.update', 'stock.edit', 'stock.destroy', 'stock.export']);
    $canStockStore = hasPermission('stock.store');
    $canStockUpdate = hasPermission('stock.update');
    $canStockEdit = hasPermission('stock.edit');
    $canStockDestroy = hasPermission('stock.destroy');
    $canStockExport = hasPermission('stock.export');

    $defaultPane = null;
    if ($canMenuView) $defaultPane = 'pane-menu';
    elseif ($canBahanView) $defaultPane = 'pane-bahan';
    elseif ($canBahanHoMitraView) $defaultPane = 'pane-bahan-ho-mitra';
    elseif ($canBahanDscView) $defaultPane = 'pane-bahan-dsc';
    elseif ($canHargaOutletView) $defaultPane = 'pane-bahan-harga-outlet';
    elseif ($canBomView) $defaultPane = 'pane-bom';
    elseif ($canStockView) $defaultPane = 'pane-inv';
@endphp

<style>
    :root{
        --aws-bg: #f7f8fa;
        --aws-card: #ffffff;
        --aws-text: #16191f;
        --aws-muted: #5f6b7a;
        --aws-line: #d5dbdb;
        --aws-line-soft: #e9ebed;
        --aws-blue: #0972d3;
        --aws-blue-hover: #033160;
        --aws-green: #037f0c;
        --aws-red: #d13212;
        --aws-orange: #b7791f;
        --aws-radius: 8px;
        --aws-shadow: 0 1px 2px rgba(15, 23, 42, .06);
        --aws-focus: 0 0 0 3px rgba(9,114,211,.18);
    }

    .office-wrap{
        min-height: 100%;
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
        box-shadow: none;
    }

    .office-topbar{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
        margin-bottom: 16px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--aws-line);
    }

    .office-title{
        font-weight: 700;
        color: var(--aws-text);
        font-size: 1.5rem;
        letter-spacing: -.02em;
        margin: 0;
    }

    .office-subtitle{
        display: none;
    }

    .office-breadcrumb{
        color: var(--aws-muted);
        font-size: .82rem;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .office-actions,
    .office-card-tools{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        align-items:center;
    }

    .btn{
        border-radius: 8px;
        font-weight: 700;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        box-shadow: none !important;
    }

    .btn:hover,
    .btn:focus{
        transform: none !important;
    }

    .btn-sm{
        padding: .43rem .74rem;
        font-size: .84rem;
    }

    .btn-primary{
        background: var(--aws-blue);
        border-color: var(--aws-blue);
        color: #fff;
    }

    .btn-primary:hover,
    .btn-primary:focus{
        background: var(--aws-blue-hover);
        border-color: var(--aws-blue-hover);
        color: #fff;
    }

    .btn-success{
        background: var(--aws-green);
        border-color: var(--aws-green);
    }

    .btn-danger{
        background: var(--aws-red);
        border-color: var(--aws-red);
    }

    .btn-outline-secondary,
    .btn-outline-success,
    .btn-outline-primary,
    .btn-outline-danger{
        background: #fff;
        border-color: var(--aws-line);
    }

    .btn-outline-secondary{
        color: #414d5c;
    }

    .btn-outline-secondary:hover,
    .btn-outline-secondary:focus{
        background: #f2f3f3;
        border-color: #7d8998;
        color: var(--aws-text);
    }

    .btn-outline-primary{
        color: var(--aws-blue);
    }

    .btn-outline-primary:hover{
        background: #f1f8ff;
        border-color: var(--aws-blue);
        color: var(--aws-blue-hover);
    }

    .btn-outline-success{
        color: var(--aws-green);
    }

    .btn-outline-danger{
        color: var(--aws-red);
    }

    .office-tabs{
        display:flex;
        gap:4px;
        overflow-x:auto;
        -webkit-overflow-scrolling: touch;
        border-bottom: 1px solid var(--aws-line);
        margin-bottom: 16px;
        padding-bottom: 0;
    }

    .office-tab{
        position: relative;
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 10px 12px 11px;
        display:inline-flex;
        align-items:center;
        gap:8px;
        font-weight: 700;
        color: var(--aws-muted);
        white-space: nowrap;
        box-shadow: none;
        cursor:pointer;
        user-select:none;
        transition: color .15s ease, background-color .15s ease;
    }

    .office-tab:hover{
        transform: none;
        background: #f2f8fd;
        color: var(--aws-blue);
    }

    .office-tab.active{
        color: var(--aws-blue);
        background: transparent;
        border-color: transparent;
        box-shadow: none;
    }

    .office-tab.active::after{
        content: "";
        position: absolute;
        left: 10px;
        right: 10px;
        bottom: -1px;
        height: 3px;
        border-radius: 999px 999px 0 0;
        background: var(--aws-blue);
    }

    .office-tab i{
        color: inherit;
        opacity: .95;
    }

    .office-card{
        background: var(--aws-card);
        border: 1px solid var(--aws-line);
        border-radius: var(--aws-radius);
        box-shadow: var(--aws-shadow);
        overflow:hidden;
        margin-bottom: 16px;
    }

    .office-card-hd{
        padding: 13px 16px;
        background: #fbfbfb;
        border-bottom: 1px solid var(--aws-line);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:12px;
        flex-wrap:wrap;
    }

    .office-card-title{
        font-weight: 700;
        margin: 0;
        color: var(--aws-text);
        letter-spacing: -.01em;
        font-size: 1rem;
    }

    .office-card-desc{
        margin: 3px 0 0 0;
        color: var(--aws-muted);
        font-size: .86rem;
    }

    .office-card-bd{
        padding: 14px;
    }

    .office-filter{
        background: #f8f9fa;
        border: 1px solid var(--aws-line-soft);
        border-radius: var(--aws-radius);
        padding: 13px;
        margin-bottom: 14px;
        box-shadow: none;
    }

    .form-label{
        color: var(--aws-text);
        font-size: .84rem;
        margin-bottom: .42rem;
        letter-spacing: 0;
        font-weight: 700;
    }

    .form-control,
    .form-select{
        min-height: 38px;
        border-radius: 8px;
        border-color: var(--aws-line);
        background-color: #fff;
        color: var(--aws-text);
        transition: border-color .15s ease, box-shadow .15s ease;
        box-shadow: none;
        font-size: .9rem;
    }

    .form-control::placeholder{
        color: #8c96a3;
    }

    .form-control:hover,
    .form-select:hover{
        border-color: #7d8998;
    }

    .form-control:focus,
    .form-select:focus{
        border-color: var(--aws-blue);
        box-shadow: var(--aws-focus);
    }

    .form-control[readonly]{
        background: #f8f9fa;
        color: #414d5c;
    }

    .form-check-input{
        border-color: #7d8998;
        box-shadow: none !important;
    }

    .form-check-input:checked{
        background-color: var(--aws-blue);
        border-color: var(--aws-blue);
    }

    .table-office{
        border-color: var(--aws-line-soft) !important;
        margin: 0;
        vertical-align: middle;
        font-size: .9rem;
    }

    .table-office thead th{
        background: #f8f9fa !important;
        border-color: var(--aws-line) !important;
        color: #414d5c;
        font-size: .73rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
        padding-top: 11px;
        padding-bottom: 11px;
    }

    .table-office td{
        border-color: var(--aws-line-soft) !important;
        color: var(--aws-text);
        vertical-align: middle;
    }

    .table-office tbody tr{
        transition: background-color .12s ease;
    }

    .table-office tbody tr:nth-child(odd),
    .table-office tbody tr:nth-child(even){
        background: #fff;
    }

    .table-office tbody tr:hover{
        background: #f2f8fd;
    }

    .table-office .num{
        text-align:right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .table-office .center{
        text-align:center;
        white-space: nowrap;
    }

    .table-responsive{
        overflow-x:auto;
        -webkit-overflow-scrolling: touch;
        border-radius: var(--aws-radius);
    }

    .table-sticky thead th{
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .btn-ellipsis{
        width: 34px;
        height: 34px;
        border-radius: 8px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:0;
    }

    .dropdown-menu{
        border-radius: 8px;
        border: 1px solid var(--aws-line);
        box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
        padding: .35rem;
        min-width: 12rem;
    }

    .dropdown-item{
        font-weight: 600;
        padding: .56rem .72rem;
        border-radius: 6px;
        font-size: .88rem;
    }

    .dropdown-item:hover,
    .dropdown-item:focus{
        background: #f2f8fd;
        color: var(--aws-blue-hover);
    }

    .modal-content{
        border-radius: 10px;
        border: 1px solid var(--aws-line);
        box-shadow: 0 24px 64px rgba(15, 23, 42, .18);
    }

    .modal-header{
        background: #fbfbfb;
        border-bottom: 1px solid var(--aws-line);
        padding: .95rem 1.05rem;
    }

    .modal-footer{
        background: #fbfbfb;
        border-top: 1px solid var(--aws-line);
        padding: .85rem 1.05rem;
    }

    .modal-title{
        font-weight: 700;
        color: var(--aws-text);
        letter-spacing: -.01em;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input{
        border: 1px solid var(--aws-line);
        border-radius: 8px;
        min-height: 36px;
        padding: .36rem .65rem;
        background: #fff;
        color: var(--aws-text);
        outline: none;
    }

    .dataTables_wrapper .dataTables_filter input:focus,
    .dataTables_wrapper .dataTables_length select:focus{
        border-color: var(--aws-blue);
        box-shadow: var(--aws-focus);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button{
        border-radius: 8px !important;
        border: 1px solid transparent !important;
        margin-left: 4px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover{
        background: var(--aws-blue) !important;
        border-color: var(--aws-blue) !important;
        color: #fff !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover{
        background: #f2f8fd !important;
        border-color: #b6d7f5 !important;
        color: var(--aws-blue-hover) !important;
    }

    .input-harga-outlet{
        min-width: 140px;
    }

    .select2-container{
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single{
        height: 38px;
        border-radius: 8px;
        border: 1px solid var(--aws-line);
        background: #fff;
        display: flex;
        align-items: center;
        padding: 0 .75rem;
        transition: border-color .15s ease, box-shadow .15s ease;
        box-shadow: none;
    }

    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single{
        border-color: var(--aws-blue);
        box-shadow: var(--aws-focus);
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered{
        color: var(--aws-text);
        line-height: normal;
        padding-left: 0;
        padding-right: 24px;
        font-weight: 600;
        font-size: .9rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder{
        color: #8c96a3;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow{
        height: 36px;
        right: 7px;
    }

    .select2-dropdown{
        border: 1px solid var(--aws-line);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
    }

    .select2-search--dropdown{
        padding: .55rem;
        background: #fff;
        border-bottom: 1px solid var(--aws-line-soft);
    }

    .select2-search--dropdown .select2-search__field{
        border: 1px solid var(--aws-line) !important;
        border-radius: 8px !important;
        min-height: 36px;
        padding: .4rem .62rem !important;
    }

    .select2-results__option{
        padding: .62rem .75rem;
        font-weight: 600;
        color: var(--aws-text);
        font-size: .9rem;
    }

    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable{
        background: #f2f8fd;
        color: var(--aws-blue-hover);
    }

    .select2-container--default .select2-results__option--selected{
        background: #f8f9fa;
        color: var(--aws-text);
    }

    .ux-empty-state{
        text-align:center;
        color: var(--aws-muted);
        padding: 24px 12px;
        font-weight: 600;
    }

    .ux-loading-row{
        text-align:center;
        color: var(--aws-muted);
        font-weight: 700;
    }

    @media (max-width: 576px){
        .office-actions .btn,
        .office-card-tools .btn{
            width: 100%;
        }

        .office-card-bd{
            padding: 12px;
        }

        .office-filter{
            padding: 12px;
        }

        .office-title{
            font-size: 1.25rem;
        }
    }
</style>

<div class="office-wrap">

                {{-- Topbar --}}
                <div class="office-topbar">
                    <div>
                        <div class="office-breadcrumb">Master Data</div>
                        <h1 class="office-title">Stock Operasional</h1>
                    </div>

                    <div class="office-actions">
                        <a href="javascript:location.reload()" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </a>
                    </div>
                </div>

                {{-- Tabs --}}
                <div class="mb-2 text-muted small fw-semibold">
                    Pilih modul untuk mengelola menu, bahan, BOM, harga outlet, atau inventory.
                </div>
                <div class="office-tabs" id="officeTabs">
                    @if($canMenuView)
                        <button class="office-tab {{ $defaultPane === 'pane-menu' ? 'active' : '' }}" data-target="#pane-menu" type="button">
                            <i class="bi bi-menu-button-wide"></i> Menu
                        </button>
                    @endif

                    @if($canBahanView)
                        <button class="office-tab {{ $defaultPane === 'pane-bahan' ? 'active' : '' }}" data-target="#pane-bahan" type="button">
                            <i class="bi bi-box-seam"></i> Bahan
                        </button>
                    @endif

                    @if($canBahanHoMitraView)
                        <button class="office-tab {{ $defaultPane === 'pane-bahan-ho-mitra' ? 'active' : '' }}" data-target="#pane-bahan-ho-mitra" type="button">
                            <i class="bi bi-cash-coin"></i> Bahan HO & MITRA
                        </button>
                    @endif

                    @if($canBahanDscView)
                        <button class="office-tab {{ $defaultPane === 'pane-bahan-dsc' ? 'active' : '' }}" data-target="#pane-bahan-dsc" type="button">
                            <i class="bi bi-clipboard2-check"></i> Bahan DSC
                        </button>
                    @endif

                    @if($canHargaOutletView)
                        <button class="office-tab {{ $defaultPane === 'pane-bahan-harga-outlet' ? 'active' : '' }}" data-target="#pane-bahan-harga-outlet" type="button">
                            <i class="bi bi-geo-alt"></i> Harga Bahan Outlet
                        </button>
                    @endif

                    @if($canBomView)
                        <button class="office-tab {{ $defaultPane === 'pane-bom' ? 'active' : '' }}" data-target="#pane-bom" type="button">
                            <i class="bi bi-diagram-3"></i> Menu + BOM
                        </button>
                    @endif

                    @if($canStockView)
                        <button class="office-tab {{ $defaultPane === 'pane-inv' ? 'active' : '' }}" data-target="#pane-inv" type="button">
                            <i class="bi bi-archive"></i> Inventory
                        </button>
                    @endif
                </div>

                @if(!$defaultPane)
                    <div class="alert alert-warning">
                        Anda belum memiliki akses ke modul Master Stock Operasional.
                    </div>
                @endif

                {{-- ===================== MENU ===================== --}}
                @if($canMenuView)
                <div id="pane-menu" class="office-pane {{ $defaultPane === 'pane-menu' ? '' : 'd-none' }}">
                    <div class="office-card">
                        <div class="office-card-hd">
                            <div>
                                <div class="office-card-title">Daftar Menu</div>
                                <div class="office-card-desc">Tambah, ubah, hapus data menu</div>
                            </div>
                            <div class="office-card-tools">
                                @if($canMenuStore)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMenuAdd">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah
                                </button>
                                @endif
                                @if($canMenuExport)
                                <a href="{{ route('menu.export') }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-download me-1"></i> Export
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="office-card-bd">
                            <div class="table-responsive">
                                <table id="menuTable" class="table table-bordered table-office table-sticky align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:70px">#</th>
                                            <th>Nama Menu</th>
                                            <th style="width:160px">Harga</th>
                                            <th style="width:120px" class="center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no=1; @endphp
                                        @foreach ($menu as $m)
                                            <tr>
                                                <td class="center">{{ $no++ }}</td>
                                                <td class="fw-semibold">{{ $m->item_produk }}</td>
                                                <td class="num">{{ number_format($m->harga, 0, ',', '.') }}</td>
                                                <td class="center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary btn-ellipsis dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @if(!$canMenuUpdate && !$canMenuDestroy)
                                                            <li><span class="dropdown-item text-muted">Tidak ada aksi</span></li>
                                                            @endif
                                                            @if($canMenuUpdate)
                                                            <li>
                                                                <button class="dropdown-item btn-menu-edit"
                                                                    data-id="{{ $m->id }}"
                                                                    data-name="{{ $m->item_produk }}"
                                                                    data-harga="{{ $m->harga }}">
                                                                    <i class="bi bi-pencil-square me-2"></i>Edit
                                                                </button>
                                                            </li>
                                                            @endif
                                                            @if($canMenuDestroy)
                                                            <li>
                                                                <button class="dropdown-item text-danger btn-menu-delete"
                                                                    data-id="{{ $m->id }}"
                                                                    data-name="{{ $m->item_produk }}">
                                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                                </button>
                                                            </li>
                                                            @endif
                                                        </ul>
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
                @endif

                {{-- ===================== BAHAN ===================== --}}
                @if($canBahanView)
                <div id="pane-bahan" class="office-pane {{ $defaultPane === 'pane-bahan' ? '' : 'd-none' }}"><div class="office-card">
                        <div class="office-card-hd">
                            <div>
                                <div class="office-card-title">Daftar Bahan</div>
                                <div class="office-card-desc">Kelola master bahan dan harga</div>
                            </div>
                            <div class="office-card-tools">
                                @if($canBahanStore)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBahanAdd">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah
                                </button>
                                @endif
                                @if($canBahanExport)
                                <a href="{{ route('bahan.export') }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-download me-1"></i> Export
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="office-card-bd">
                            <div class="table-responsive">
                                <table id="bahanTable" class="table table-bordered table-office table-sticky align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:70px">#</th>
                                            <th>Nama Bahan</th>
                                            <th style="width:110px">Qty</th>
                                            <th style="width:120px">Satuan</th>
                                            <th style="width:120px">Konversi</th>
                                            <th style="width:160px">Harga</th>
                                            <th style="width:120px" class="center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no=1; @endphp
                                        @foreach ($bahan as $b)
                                            <tr>
                                                <td class="center">{{ $no++ }}</td>
                                                <td class="fw-semibold">{{ $b->nama_bahan }}</td>
                                                <td class="num">{{ $b->qty }}</td>
                                                <td>{{ $b->satuan }}</td>
                                                <td class="num">{{ $b->konversi }}</td>
                                                <td class="num">{{ number_format($b->harga_bahan, 0, ',', '.') }}</td>
                                                <td class="center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary btn-ellipsis dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @if(!$canBahanUpdate && !$canBahanDestroy)
                                                            <li><span class="dropdown-item text-muted">Tidak ada aksi</span></li>
                                                            @endif
                                                            @if($canBahanUpdate)
                                                            <li>
                                                                <button class="dropdown-item btn-bahan-edit"
                                                                    data-id="{{ $b->id }}"
                                                                    data-nama="{{ $b->nama_bahan }}"
                                                                    data-qty="{{ $b->qty }}"
                                                                    data-satuan="{{ $b->satuan }}"
                                                                    data-konversi="{{ $b->konversi }}"
                                                                    data-harga="{{ $b->harga_bahan }}">
                                                                    <i class="bi bi-pencil-square me-2"></i>Edit
                                                                </button>
                                                            </li>
                                                            @endif
                                                            @if($canBahanDestroy)
                                                            <li>
                                                                <button class="dropdown-item text-danger btn-bahan-delete"
                                                                    data-id="{{ $b->id }}"
                                                                    data-name="{{ $b->nama_bahan }}">
                                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                                </button>
                                                            </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div></div>
                @endif

                {{-- ===================== BAHAN HO & MITRA ===================== --}}
                @if($canBahanHoMitraView)
                <div id="pane-bahan-ho-mitra" class="office-pane {{ $defaultPane === 'pane-bahan-ho-mitra' ? '' : 'd-none' }}">
                    @php
                        /*
                        |--------------------------------------------------------------------------
                        | Outlet dropdown untuk Bahan HO & MITRA
                        |--------------------------------------------------------------------------
                        | Dibuat di blade agar tidak mengubah method controller pembuka halaman.
                        | Outlet duplikat nama digabung menjadi 1 option.
                        | value option berisi array id outlet gabungan, contoh: 901,1461
                        */
                        $bahanHoMitraOutlets = DB::table('tbl_outlets')
                            ->select(
                                DB::raw('MIN(id) as id'),
                                DB::raw('UPPER(TRIM(nama_outlet)) as nama_outlet'),
                                DB::raw('GROUP_CONCAT(id ORDER BY id SEPARATOR ",") as outlet_ids'),
                                DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(kategori_harga ORDER BY FIELD(kategori_harga, 'HO', 'MITRA') SEPARATOR ','), ',', 1) as kategori_harga")
                            )
                            ->whereNotNull('nama_outlet')
                            ->where('nama_outlet', '!=', '')
                            ->whereNotNull('kategori_harga')
                            ->groupBy(DB::raw('UPPER(TRIM(nama_outlet))'))
                            ->orderBy(DB::raw('UPPER(TRIM(nama_outlet))'))
                            ->get();
                    @endphp

                    <div class="office-card">
                        <div class="office-card-hd">
                            <div>
                                <div class="office-card-title">Bahan HO & MITRA</div>
                                <div class="office-card-desc">Kelola harga bahan berdasarkan kategori HO / MITRA. Data baru tampil setelah filter dijalankan.</div>
                            </div>
                            <div class="office-card-tools">
                                <button id="btnReloadBahanHoMitra" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                                </button>
                            </div>
                        </div>

                        <div class="office-card-bd">
                            <div class="office-filter">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Kategori Harga</label>
                                        <select id="filterKategoriBahanHoMitra" class="form-select">
                                            <option value="all">Semua</option>
                                            <option value="HO">HO</option>
                                            <option value="MITRA">MITRA</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Outlet</label>
                                        <select id="filterOutletBahanHoMitra" class="form-select">
                                            <option value="">Semua Outlet</option>
                                            @foreach($bahanHoMitraOutlets as $o)
                                                <option
                                                    value="{{ $o->outlet_ids }}"
                                                    data-kategori="{{ $o->kategori_harga }}"
                                                >
                                                    {{ $o->nama_outlet }} [{{ $o->kategori_harga }}]
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <button type="button" id="btnLoadBahanHoMitra" class="btn btn-primary btn-sm">
                                            <i class="bi bi-search me-1"></i> Tampilkan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-office table-sticky align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:70px">#</th>
                                            <th style="width:220px">Nama Sheet</th>
                                            <th>Nama Bahan</th>
                                            <th style="width:120px" class="center">Kategori</th>
                                            <th style="width:170px">Harga</th>
                                            <th style="width:190px" class="center">Update</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bahanHoMitraBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                Pilih filter lalu klik Tampilkan.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-end">
                                @if($canBahanHoMitraEdit)
                                <div class="d-flex gap-2 flex-wrap justify-content-end">
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-simpan-kategori-bahan-ho-mitra" data-kategori="HO">
                                        <i class="bi bi-save me-1"></i> Simpan All HO
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm btn-simpan-kategori-bahan-ho-mitra" data-kategori="MITRA">
                                        <i class="bi bi-save me-1"></i> Simpan All MITRA
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" id="btnSimpanBahanHoMitra">
                                        <i class="bi bi-save me-1"></i> Simpan Semua
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif


                {{-- ===================== BAHAN DSC ===================== --}}
                @if($canBahanDscView)
                <div id="pane-bahan-dsc" class="office-pane {{ $defaultPane === 'pane-bahan-dsc' ? '' : 'd-none' }}">
                    <div class="office-card">
                        <div class="office-card-hd">
                            <div>
                                <div class="office-card-title">Daftar Bahan DSC</div>
                                <div class="office-card-desc">Kelola master bahan yang tampil pada Daily Stock Control</div>
                            </div>
                            <div class="office-card-tools">
                                @if($canBahanDscStore)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBahanDscAdd">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah
                                </button>
                                @endif
                            </div>
                        </div>

                        <div class="office-card-bd">
                            <div class="table-responsive">
                                <table id="bahanDscTable" class="table table-bordered table-office table-sticky align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:70px">#</th>
                                            <th>Nama Bahan DSC</th>
                                            <th style="width:120px">Satuan</th>
                                            <th style="width:120px" class="center">Tepung</th>
                                            <th style="width:120px" class="center">Status</th>
                                            <th style="width:120px" class="center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $noDsc=1; @endphp
                                        @foreach (($bahanDsc ?? collect()) as $d)
                                            <tr>
                                                <td class="center">{{ $noDsc++ }}</td>
                                                <td class="fw-semibold">{{ $d->nama_bahan }}</td>
                                                <td>{{ $d->satuan }}</td>
                                                <td class="center">
                                                    @if ((int)($d->is_tepung ?? 0) === 1)
                                                        <span class="badge text-bg-primary">YA</span>
                                                    @else
                                                        <span class="badge text-bg-secondary">TIDAK</span>
                                                    @endif
                                                </td>
                                                <td class="center">
                                                    @if ((int)($d->is_active ?? 1) === 1)
                                                        <span class="badge text-bg-success">AKTIF</span>
                                                    @else
                                                        <span class="badge text-bg-secondary">NONAKTIF</span>
                                                    @endif
                                                </td>
                                                <td class="center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary btn-ellipsis dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @if(!$canBahanDscUpdate && !$canBahanDscDestroy)
                                                            <li><span class="dropdown-item text-muted">Tidak ada aksi</span></li>
                                                            @endif
                                                            @if($canBahanDscUpdate)
                                                            <li>
                                                                <button class="dropdown-item btn-bahan-dsc-edit"
                                                                    data-id="{{ $d->id }}"
                                                                    data-nama="{{ $d->nama_bahan }}"
                                                                    data-satuan="{{ $d->satuan }}"
                                                                    data-is-tepung="{{ (int)($d->is_tepung ?? 0) }}"
                                                                    data-is-active="{{ (int)($d->is_active ?? 1) }}">
                                                                    <i class="bi bi-pencil-square me-2"></i>Edit
                                                                </button>
                                                            </li>
                                                            @endif
                                                            @if($canBahanDscDestroy)
                                                            <li>
                                                                <button class="dropdown-item text-danger btn-bahan-dsc-delete"
                                                                    data-id="{{ $d->id }}"
                                                                    data-name="{{ $d->nama_bahan }}">
                                                                    <i class="bi bi-trash me-2"></i>Hapus / Nonaktifkan
                                                                </button>
                                                            </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 small text-muted fw-semibold">
                                Catatan: jika bahan DSC sudah pernah dipakai pada stock/draft, tombol hapus akan otomatis menonaktifkan bahan agar histori tetap aman.
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($canHargaOutletView)
                <div id="pane-bahan-harga-outlet" class="office-pane {{ $defaultPane === 'pane-bahan-harga-outlet' ? '' : 'd-none' }}">
                    <div class="office-card">
                        <div class="office-card-hd">
                            <div>
                                <div class="office-card-title">Harga Bahan per Outlet</div>
                                <div class="office-card-desc">Kelola harga bahan per outlet khusus Ayam Besar, Ayam Kecil, Ayam Utuh, dan Beras</div>
                            </div>
                            <div class="office-card-tools">
                                <button id="btnReloadHargaOutlet" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                                </button>
                            </div>
                        </div>

                        <div class="office-card-bd">
                            <div class="office-filter">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Outlet</label>
                                        <select id="filterOutletHarga" class="form-select">
                                            <option value="">-- Pilih Outlet --</option>
                                            @foreach($outlets as $o)
                                                <option value="{{ $o->id }}">{{ $o->nama_outlet }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <button type="button" id="btnLoadHargaOutlet" class="btn btn-primary btn-sm">
                                            <i class="bi bi-search me-1"></i> Tampilkan Harga
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-office table-sticky align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:70px">#</th>
                                            <th>Nama Bahan</th>
                                            <th style="width:160px">Harga Master</th>
                                            <th style="width:180px">Harga Outlet</th>
                                            <th style="width:140px" class="center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bahanHargaOutletBody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Pilih outlet untuk melihat harga bahan.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-end">
                                @if($canHargaOutletEdit)
                                <button type="button" class="btn btn-success btn-sm" id="btnSimpanSemuaHargaOutlet">
                                    <i class="bi bi-save me-1"></i> Simpan Semua
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- ===================== MENU + BOM ===================== --}}
                @if($canBomView)
                <div id="pane-bom" class="office-pane {{ $defaultPane === 'pane-bom' ? '' : 'd-none' }}">
                    <div class="office-card">
                        <div class="office-card-hd">
                            <div>
                                <div class="office-card-title">Menu + BOM</div>
                                <div class="office-card-desc">Atur bahan apa saja untuk setiap menu</div>
                            </div>
                            <div class="office-card-tools">
                                @if($canBomStore)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalBomAdd">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah BOM
                                </button>
                                @endif
                                @if($canBomExport)
                                <a href="{{ route('bum.export') }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-download me-1"></i> Export
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="office-card-bd">
                            <div class="table-responsive">
                                <table id="menuBumTable" class="table table-bordered table-office table-sticky align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th>Menu</th>
                                            <th style="width:160px">Harga</th>
                                            <th style="width:120px" class="center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($menu as $m)
                                            <tr>
                                                <td class="fw-semibold">{{ $m->item_produk }}</td>
                                                <td class="num">{{ number_format($m->harga, 0, ',', '.') }}</td>
                                                <td class="center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary btn-ellipsis dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @if(!$canBomDetail && !$canBomUpdate && !$canBomDestroy)
                                                            <li><span class="dropdown-item text-muted">Tidak ada aksi</span></li>
                                                            @endif
                                                            @if($canBomDetail)
                                                            <li>
                                                                <button class="dropdown-item btn-bom-view"
                                                                    data-id="{{ $m->id }}"
                                                                    data-name="{{ $m->item_produk }}">
                                                                    <i class="bi bi-eye me-2"></i>Lihat BOM
                                                                </button>
                                                            </li>
                                                            @endif
                                                            @if($canBomUpdate)
                                                            <li>
                                                                <button class="dropdown-item btn-bom-edit"
                                                                    data-id="{{ $m->id }}"
                                                                    data-name="{{ $m->item_produk }}">
                                                                    <i class="bi bi-pencil-square me-2"></i>Edit BOM
                                                                </button>
                                                            </li>
                                                            @endif
                                                            @if($canBomDestroy)
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('bum.destroy', $m->id) }}" method="POST"
                                                                      onsubmit="return confirm('Yakin hapus semua bahan untuk menu {{ $m->item_produk }}?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="bi bi-trash me-2"></i>Hapus BOM
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Modal: Tambah BOM --}}
                            <div class="modal fade" id="modalBomAdd" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <form action="{{ route('bum.store') }}" method="POST" class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah BOM</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Menu</label>
                                                <select name="menu_id" id="menuSelect" class="form-select" required>
                                                    <option value="">-- Pilih Menu --</option>
                                                    @foreach ($menu as $m)
                                                        <option value="{{ $m->id }}">{{ $m->item_produk }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="fw-semibold mb-2">Pilih Bahan</div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-office align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="center" style="width:48px">Pilih</th>
                                                            <th>Bahan</th>
                                                            <th style="width:120px">Satuan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($bahan as $b)
                                                            <tr>
                                                                <td class="center">
                                                                    <input type="checkbox" class="form-check-input" name="bahan_id[]" value="{{ $b->id }}">
                                                                </td>
                                                                <td>{{ $b->nama_bahan }}</td>
                                                                <td>{{ $b->satuan }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Modal: Lihat BOM --}}
                            <div class="modal fade" id="modalBomView" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detail BOM: <span id="bomViewTitle" class="fw-bold"></span></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-office align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>Bahan</th>
                                                            <th style="width:120px">Satuan</th>
                                                            <th style="width:120px">Qty</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bomViewBody">
                                                        <tr><td colspan="3" class="text-center text-muted">Memuat...</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal: Edit BOM --}}
                            <div class="modal fade" id="modalBomEdit" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <form id="bomEditForm" method="POST" class="modal-content">
                                        @csrf
                                        @method('POST')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit BOM: <span id="bomEditTitle" class="fw-bold"></span></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="table-responsive">
                                            <table class="table table-bordered table-office align-middle mb-0">
                                              <thead>
                                                <tr>
                                                  <th class="center" style="width:48px">Pilih</th>
                                                  <th>Bahan</th>
                                                  <th style="width:120px">Satuan</th>
                                                  <th style="width:140px">Qty BOM</th>
                                                </tr>
                                              </thead>

                                              <tbody id="bomEditBody">
                                                <tr><td colspan="4" class="text-center text-muted">Klik Edit BOM untuk memuat...</td></tr>
                                              </tbody>
                                            </table>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @endif

                {{-- ===================== INVENTORY ===================== --}}
                @if($canStockView)
                <div id="pane-inv" class="office-pane {{ $defaultPane === 'pane-inv' ? '' : 'd-none' }}">
                    <div class="office-card">
                        <div class="office-card-hd">
                            <div>
                                <div class="office-card-title">Inventory</div>
                                <div class="office-card-desc">Kelola stock bahan</div>
                            </div>
                            <div class="office-card-tools">
                                @if($canStockStore)
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahStockModal">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah
                                </button>
                                @endif
                                @if($canStockExport)
                                <a href="{{ route('stock.export') }}" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-download me-1"></i> Export
                                </a>
                                @endif
                            </div>
                        </div>

                        <div class="office-card-bd">
                            <div class="table-responsive">
                                <table id="stockTable" class="table table-bordered table-office table-sticky align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th style="width:70px">#</th>
                                            <th>Nama Bahan</th>
                                            <th>Satuan</th>
                                            <th>Shift</th>
                                            <th>Opening</th>
                                            <th>Used</th>
                                            <th>Waste Product</th>
                                            <th>Waste Bahan</th>
                                            <th>Waste Tepung</th>
                                            <th>Ending</th>
                                            <th>Tanggal</th>
                                            <th class="center" style="width:120px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $no=1; @endphp
                                        @foreach ($stock as $s)
                                            <tr>
                                                <td class="center">{{ $no++ }}</td>
                                                <td class="fw-semibold">{{ $s->nama_bahan }}</td>
                                                <td>{{ $s->satuan }}</td>
                                                <td class="center">{{ $s->shift }}</td>
                                                <td class="num">{{ number_format($s->opening_stock, 2) }}</td>
                                                <td class="num">{{ number_format($s->used_qty, 2) }}</td>
                                                <td class="num">{{ number_format($s->waste_product, 2) }}</td>
                                                <td class="num">{{ number_format($s->waste_bahan, 2) }}</td>
                                                <td class="num">{{ number_format($s->waste_tepung, 2) }}</td>
                                                <td class="num">{{ number_format($s->ending_stock, 2) }}</td>
                                                <td class="center">{{ \Carbon\Carbon::parse($s->tanggal)->format('d-m-Y') }}</td>
                                                <td class="center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-secondary btn-ellipsis dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @if(!$canStockEdit && !$canStockUpdate && !$canStockDestroy)
                                                            <li><span class="dropdown-item text-muted">Tidak ada aksi</span></li>
                                                            @endif
                                                            @if($canStockEdit || $canStockUpdate)
                                                            <li>
                                                                <button class="dropdown-item btn-edit-stock" data-id="{{ $s->id }}">
                                                                    <i class="bi bi-pencil-square me-2"></i>Edit
                                                                </button>
                                                            </li>
                                                            @endif
                                                            @if($canStockDestroy)
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('stock.destroy', $s->id) }}" method="POST"
                                                                      onsubmit="return confirm('Yakin hapus stock ini?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="bi bi-trash me-2"></i>Hapus
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Modal Tambah Stock --}}
                            <div class="modal fade" id="tambahStockModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <form action="{{ route('stock.store') }}" method="POST" class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Stock</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nama Bahan</label>
                                                <select name="bahan_id" class="form-select" required>
                                                    <option value="">-- Pilih Bahan --</option>
                                                    @foreach ($bahan as $b)
                                                        <option value="{{ $b->id }}">{{ $b->nama_bahan }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Shift</label>
                                                    <select name="shift" class="form-select" required>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="TOT">TOT</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Opening Stock</label>
                                                    <input type="number" name="opening_stock" class="form-control" step="0.01" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Used Qty</label>
                                                    <input type="number" name="used_qty" class="form-control" step="0.01" required>
                                                </div>
                                            </div>

                                            <div class="row g-2 mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Waste Product</label>
                                                    <input type="number" name="waste_product" class="form-control" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Waste Bahan</label>
                                                    <input type="number" name="waste_bahan" class="form-control" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-semibold">Waste Tepung</label>
                                                    <input type="number" name="waste_tepung" class="form-control" step="0.01" value="0">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Tanggal</label>
                                                <input type="date" name="tanggal" class="form-control" required value="{{ date('Y-m-d') }}">
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            {{-- Modal Edit Stock --}}
                            <div class="modal fade" id="editStockModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <form id="editStockForm" method="POST" class="modal-content">
                                        @csrf
                                        @method('POST')
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Stock</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body" id="editStockBody">
                                            <div class="text-center text-muted">Memuat...</div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @endif

</div>{{-- /office-wrap --}}

@if($canMenuStore || $canMenuUpdate || $canMenuDestroy)
{{-- ===================== MODAL MENU ===================== --}}
<div class="modal fade" id="modalMenuAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('menu.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Menu</label>
                    <input type="text" name="item_produk" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Harga</label>
                    <input type="number" name="harga" class="form-control" value="0" required>
                </div>
                <input type="hidden" name="kategori" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalMenuEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formMenuEdit" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Menu</label>
                    <input type="text" name="item_produk" id="menuEditName" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Harga</label>
                    <input type="number" name="harga" id="menuEditHarga" class="form-control" required>
                </div>
                <input type="hidden" name="kategori" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalMenuDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formMenuDelete" method="POST" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Hapus Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Yakin hapus menu <b id="menuDeleteName"></b>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus</button>
            </div>
        </form>
    </div>
</div>

@endif

@if($canBahanStore || $canBahanUpdate || $canBahanDestroy)
{{-- ===================== MODAL BAHAN ===================== --}}
<div class="modal fade" id="modalBahanAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('bahan.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Bahan</label>
                    <input type="text" name="nama_bahan" class="form-control" required>
                </div>

                <div class="row g-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Qty</label>
                        <input type="number" name="qty" step="0.01" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Isi per Unit</label>
                      <input type="number" name="isi_per_unit" step="0.01" value="1" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Satuan</label>
                        <input type="text" name="satuan" class="form-control" required>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Konversi</label>
                        <input type="number" name="konversi" step="0.01" value="1" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Harga</label>
                        <input type="number" name="harga_bahan" step="0.01" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalBahanEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formBahanEdit" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Bahan</label>
                    <input type="text" name="nama_bahan" id="bahanEditNama" class="form-control" required>
                </div>

                <div class="row g-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Qty</label>
                        <input type="number" name="qty" step="0.01" id="bahanEditQty" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Satuan</label>
                        <input type="text" name="satuan" id="bahanEditSatuan" class="form-control" required>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Konversi</label>
                        <input type="number" name="konversi" step="0.01" id="bahanEditKonversi" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Harga</label>
                        <input type="number" name="harga_bahan" step="0.01" id="bahanEditHarga" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalBahanDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formBahanDelete" method="POST" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-trash me-2"></i>Hapus Bahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Yakin hapus bahan <b id="bahanDeleteName"></b>?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus</button>
            </div>
        </form>
    </div>
</div>


@endif

@if($canBahanDscStore || $canBahanDscUpdate || $canBahanDscDestroy)
{{-- ===================== MODAL BAHAN DSC ===================== --}}
<div class="modal fade" id="modalBahanDscAdd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('bahan-dsc.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Tambah Bahan DSC</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label fw-semibold">Nama Bahan DSC</label><input type="text" name="nama_bahan" class="form-control" placeholder="Contoh: BAHAN NASI UDUK" required></div>
                <div class="mb-3"><label class="form-label fw-semibold">Satuan</label><input type="text" name="satuan" class="form-control" placeholder="PCS / GRAM / LTR" required></div>
                <div class="row g-2">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Bahan Tepung?</label><select name="is_tepung" class="form-select"><option value="0">Tidak</option><option value="1">Ya</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Status</label><select name="is_active" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalBahanDscEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formBahanDscEdit" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Bahan DSC</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label fw-semibold">Nama Bahan DSC</label><input type="text" name="nama_bahan" id="bahanDscEditNama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label fw-semibold">Satuan</label><input type="text" name="satuan" id="bahanDscEditSatuan" class="form-control" required></div>
                <div class="row g-2">
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Bahan Tepung?</label><select name="is_tepung" id="bahanDscEditIsTepung" class="form-select"><option value="0">Tidak</option><option value="1">Ya</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-semibold">Status</label><select name="is_active" id="bahanDscEditIsActive" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalBahanDscDelete" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formBahanDscDelete" method="POST" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-trash me-2"></i>Hapus / Nonaktifkan Bahan DSC</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">Yakin hapus/nonaktifkan bahan DSC <b id="bahanDscDeleteName"></b>?<div class="small text-muted mt-2">Kalau bahan sudah pernah dipakai di stock, sistem akan menonaktifkan data, bukan menghapus permanen.</div></div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Proses</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalHargaOutletEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formHargaOutletEdit" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Harga Outlet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="outlet_id" id="hargaOutletEditOutletId">
                <input type="hidden" name="bahan_id" id="hargaOutletEditBahanId">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Outlet</label>
                    <input type="text" id="hargaOutletEditNamaOutlet" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Bahan</label>
                    <input type="text" id="hargaOutletEditNamaBahan" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Harga</label>
                    <input type="number" name="harga_bahan" id="hargaOutletEditHarga" class="form-control" step="0.01" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>


@endif

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const canHargaOutletEdit = @json($canHargaOutletEdit);
    const canBahanHoMitraEdit = @json($canBahanHoMitraEdit ?? false);
    const canMenuUpdate = @json($canMenuUpdate);
    const canMenuDestroy = @json($canMenuDestroy);
    const canBahanUpdate = @json($canBahanUpdate);
    const canBahanDestroy = @json($canBahanDestroy);
    const canBahanDscUpdate = @json($canBahanDscUpdate);
    const canBahanDscDestroy = @json($canBahanDscDestroy);
    const canBomUpdate = @json($canBomUpdate);
    const canStockUpdate = @json($canStockUpdate || $canStockEdit);
    if (window.$) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
    }

    const uxToast = (icon, title) => {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon,
                title,
                timer: 1800,
                showConfirmButton: false
            });
        } else {
            alert(title);
        }
    };

    const uxConfirm = async (title, text = '') => {
        if (window.Swal) {
            const result = await Swal.fire({
                icon: 'warning',
                title,
                text,
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#0972d3'
            });
            return result.isConfirmed;
        }

        return confirm(title);
    };


    function formatRupiah(angka) {
        const num = Number(angka || 0);
        return num.toLocaleString('id-ID');
    }

    function getSelectedOutletId() {
        return $('#filterOutletHarga').val();
    }

    function getSelectedOutletName() {
        return $('#filterOutletHarga option:selected').text();
    }

    function initSelect2Element(selector, options = {}) {
        if (!(window.$ && $.fn.select2) || !$(selector).length) return;

        const el = $(selector);
        if (!el.closest('body').length) return;

        const defaultOptions = {
            width: '100%',
            placeholder: '-- Pilih --',
            allowClear: false
        };

        try {
            el.select2({ ...defaultOptions, ...options });
        } catch (e) {
            console.warn('Select2 init skipped:', selector, e);
        }
    }

    function refreshSelect2Outlet() {
        if (!(window.$ && $.fn.select2) || !$('#filterOutletHarga').length) return;

        if ($('#filterOutletHarga').hasClass('select2-hidden-accessible')) {
            $('#filterOutletHarga').select2('destroy');
        }

        initSelect2Element('#filterOutletHarga', {
            placeholder: '-- Pilih Outlet --'
        });
    }

    const bahanHoMitraOutletOptions = $('#filterOutletBahanHoMitra option').clone();

    function refreshSelect2BahanHoMitra() {
        if (!(window.$ && $.fn.select2)) return;

        ['#filterKategoriBahanHoMitra', '#filterOutletBahanHoMitra'].forEach(function(selector) {
            if (!$(selector).length) return;
            if ($(selector).hasClass('select2-hidden-accessible')) {
                try { $(selector).select2('destroy'); } catch (e) {}
            }
        });

        initSelect2Element('#filterKategoriBahanHoMitra', {
            placeholder: 'Pilih kategori'
        });

        initSelect2Element('#filterOutletBahanHoMitra', {
            placeholder: 'Semua Outlet'
        });
    }

    function rebuildOutletBahanHoMitraOptions() {
        if (!$('#filterOutletBahanHoMitra').length) return;

        const kategori = $('#filterKategoriBahanHoMitra').val() || 'all';
        const select = $('#filterOutletBahanHoMitra');

        if (select.hasClass('select2-hidden-accessible')) {
            try { select.select2('destroy'); } catch (e) {}
        }

        select.empty();

        bahanHoMitraOutletOptions.each(function() {
            const opt = $(this).clone();
            const optKategori = String(opt.data('kategori') || '');

            if (!opt.val() || kategori === 'all' || optKategori === kategori) {
                select.append(opt);
            }
        });

        select.val('');
        refreshSelect2BahanHoMitra();

        $('#bahanHoMitraBody').html(`
            <tr>
                <td colspan="6" class="text-center text-muted">
                    Pilih filter lalu klik Tampilkan.
                </td>
            </tr>
        `);
    }



    function getBahanHoMitraParams() {
        return {
            kategori_harga: $('#filterKategoriBahanHoMitra').val() || 'all',
            outlet_id: $('#filterOutletBahanHoMitra').val() || ''
        };
    }

    function loadBahanHoMitra() {
        if (!$('#bahanHoMitraBody').length) return;

        const params = getBahanHoMitraParams();

        $('#bahanHoMitraBody').html(`
            <tr>
                <td colspan="6" class="ux-loading-row">Memuat...</td>
            </tr>
        `);

        $.get('/inventory/qcr/bahan-ho-mitra/list', params, function(res) {
            const rows = (res && res.data) ? res.data : [];

            if (!rows.length) {
                $('#bahanHoMitraBody').html(`
                    <tr>
                        <td colspan="6" class="text-center text-muted">Data harga bahan tidak ditemukan.</td>
                    </tr>
                `);
                return;
            }

            let html = '';
            rows.forEach((r, idx) => {
                const harga = r.harga_bahan ?? 0;
                html += `
                    <tr>
                        <td class="center">${idx + 1}</td>
                        <td class="fw-semibold">${r.nama_sheet || '-'}</td>
                        <td>${r.nama_bahan || '-'}</td>
                        <td class="center">
                            <span class="badge ${r.kategori_harga === 'HO' ? 'text-bg-primary' : 'text-bg-success'}">${r.kategori_harga}</span>
                        </td>
                        <td>
                            <input
                                type="number"
                                class="form-control form-control-sm input-bahan-ho-mitra"
                                data-bahan-id="${r.id}"
                                data-nama-bahan="${r.nama_bahan || ''}"
                                data-kategori="${r.kategori_harga}"
                                value="${harga}"
                                step="0.01"
                                ${canBahanHoMitraEdit ? '' : 'readonly'}
                            >
                        </td>
                        <td class="center">
                            ${canBahanHoMitraEdit ? `
                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm btn-update-all-kategori-bahan"
                                data-bahan-id="${r.id}"
                                data-nama-bahan="${r.nama_bahan || ''}"
                                data-kategori="${r.kategori_harga}">
                                Update All ${r.kategori_harga}
                            </button>` : `<span class="text-muted small">Read only</span>`}
                        </td>
                    </tr>
                `;
            });

            $('#bahanHoMitraBody').html(html);
        }).fail(function(xhr) {
            const message = xhr?.responseJSON?.message || 'Gagal memuat data harga bahan HO & MITRA.';
            $('#bahanHoMitraBody').html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">${message}</td>
                </tr>
            `);
        });
    }

    $('#filterKategoriBahanHoMitra').on('change', function() {
        rebuildOutletBahanHoMitraOptions();
    });

    $('#btnLoadBahanHoMitra').on('click', function() {
        loadBahanHoMitra();
    });

    $('#btnReloadBahanHoMitra').on('click', function() {
        loadBahanHoMitra();
    });


    $(document).on('click', '.btn-update-all-kategori-bahan', function() {
        if (!canBahanHoMitraEdit) return;

        const bahanId = $(this).data('bahan-id');
        const kategori = $(this).data('kategori');
        const input = $(`.input-bahan-ho-mitra[data-bahan-id="${bahanId}"][data-kategori="${kategori}"]`);
        const harga = input.val();

        if (!bahanId || !kategori) {
            uxToast('error', 'Data bahan/kategori tidak valid.');
            return;
        }

        $.ajax({
            url: '/inventory/qcr/bahan-ho-mitra/update-all-kategori',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                bahan_id: bahanId,
                kategori_harga: kategori,
                harga: harga
            },
            success: function(res) {
                if (res && res.success) {
                    uxToast('success', res.message || 'Harga berhasil diupdate.');
                    loadBahanHoMitra();
                } else {
                    uxToast('error', res.message || 'Gagal update harga.');
                }
            },
            error: function(xhr) {
                uxToast('error', xhr?.responseJSON?.message || 'Gagal update harga.');
            }
        });
    });

    $('#btnSimpanBahanHoMitra').on('click', function() {
        if (!canBahanHoMitraEdit) return;

        const harga = {};

        $('.input-bahan-ho-mitra').each(function() {
            const bahanId = $(this).data('bahan-id');
            const kategori = $(this).data('kategori');

            if (!harga[bahanId]) {
                harga[bahanId] = {};
            }

            harga[bahanId][kategori] = $(this).val();
        });

        $.ajax({
            url: '/inventory/qcr/bahan-ho-mitra/bulk-update',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                harga: harga
            },
            success: function(res) {
                if (res && res.success) {
                    uxToast('success', res.message || 'Harga bahan HO & MITRA berhasil disimpan.');
                    loadBahanHoMitra();
                } else {
                    uxToast('error', res.message || 'Gagal menyimpan harga.');
                }
            },
            error: function(xhr) {
                uxToast('error', xhr?.responseJSON?.message || 'Gagal menyimpan harga.');
            }
        });
    });

    function loadHargaOutlet() {
        const outletId = getSelectedOutletId();

        if (!outletId) {
            $('#bahanHargaOutletBody').html(`
                <tr>
                    <td colspan="5" class="text-center text-muted">Pilih outlet untuk melihat harga bahan.</td>
                </tr>
            `);
            return;
        }

        $('#bahanHargaOutletBody').html(`
            <tr>
                <td colspan="6" class="ux-loading-row">Memuat...</td>
            </tr>
        `);

        $.get(`/inventory/qcr/bahan-harga-outlet/list?outlet_id=${outletId}`, function(res) {
            const rows = (res && res.data) ? res.data : [];

            if (!rows.length) {
                $('#bahanHargaOutletBody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted">Data tidak ditemukan.</td>
                    </tr>
                `);
                return;
            }

            const allowedBahanHargaOutlet = ['ayam besar', 'ayam kecil', 'ayam utuh', 'beras', 'ayam cut 14'];
            const filteredRows = rows.filter((r) => {
                const nama = String(r.nama_bahan || '').trim().toLowerCase();
                return allowedBahanHargaOutlet.includes(nama);
            });

            if (!filteredRows.length) {
                $('#bahanHargaOutletBody').html(`
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Data bahan Ayam Besar, Ayam Kecil, Ayam Utuh, dan Beras tidak ditemukan untuk outlet ini.
                        </td>
                    </tr>
                `);
                return;
            }

            let html = '';
            filteredRows.forEach((r, idx) => {
                html += `
                    <tr>
                        <td class="center">${idx + 1}</td>
                        <td class="fw-semibold">${r.nama_bahan}</td>
                        <td class="num">Rp ${formatRupiah(r.harga_master ?? r.harga_bahan)}</td>
                        <td>
                            <input
                                type="number"
                                class="form-control form-control-sm input-harga-outlet"
                                data-bahan-id="${r.id}"
                                value="${r.harga_bahan}"
                                step="0.01"
                                ${canHargaOutletEdit ? '' : 'readonly'}
                            >
                        </td>
                        <td class="center">
                            ${canHargaOutletEdit ? `
                            <div class="d-flex gap-1 justify-content-center">
                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm btn-harga-outlet-edit"
                                    data-bahan-id="${r.id}"
                                    data-nama-bahan="${r.nama_bahan}"
                                    data-harga="${r.harga_bahan}">
                                    Edit
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm btn-harga-outlet-reset"
                                    data-bahan-id="${r.id}"
                                    data-nama-bahan="${r.nama_bahan}">
                                    Reset
                                </button>
                            </div>` : `<span class="text-muted small">Read only</span>`}
                        </td>
                    </tr>
                `;
            });

            $('#bahanHargaOutletBody').html(html);
        }).fail(function() {
            $('#bahanHargaOutletBody').html(`
                <tr>
                    <td colspan="5" class="text-center text-danger">Gagal memuat data harga outlet.</td>
                </tr>
            `);
        });
    }

    $('#btnLoadHargaOutlet').on('click', function() {
        loadHargaOutlet();
    });

    $('#btnReloadHargaOutlet').on('click', function() {
        loadHargaOutlet();
    });

    $(document).on('click', '.btn-harga-outlet-edit', function() {
        if (!canHargaOutletEdit) return;
        const outletId = getSelectedOutletId();
        const outletName = getSelectedOutletName();

        if (!outletId) {
            uxToast('warning', 'Pilih outlet terlebih dahulu.');
            return;
        }

        $('#hargaOutletEditOutletId').val(outletId);
        $('#hargaOutletEditBahanId').val($(this).data('bahan-id'));
        $('#hargaOutletEditNamaOutlet').val(outletName);
        $('#hargaOutletEditNamaBahan').val($(this).data('nama-bahan'));
        $('#hargaOutletEditHarga').val($(this).data('harga'));

        $('#modalHargaOutletEdit').modal('show');
    });

    $('#formHargaOutletEdit').on('submit', function(e) {
        if (!canHargaOutletEdit) return;
        e.preventDefault();

        $.ajax({
            url: '/inventory/qcr/bahan-harga-outlet/store-update',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if (res && res.success) {
                    $('#modalHargaOutletEdit').modal('hide');
                    uxToast('success', res.message || 'Harga outlet berhasil disimpan.');
                    loadHargaOutlet();
                } else {
                    uxToast('error', res.message || 'Gagal menyimpan harga outlet.');
                }
            },
            error: function(xhr) {
                uxToast('error', 'Gagal menyimpan harga outlet.');
            }
        });
    });

    $('#btnSimpanSemuaHargaOutlet').on('click', function() {
        if (!canHargaOutletEdit) return;
        const outletId = getSelectedOutletId();

        if (!outletId) {
            uxToast('warning', 'Pilih outlet terlebih dahulu.');
            return;
        }

        const harga = {};
        $('.input-harga-outlet').each(function() {
            harga[$(this).data('bahan-id')] = $(this).val();
        });

        $.ajax({
            url: '/inventory/qcr/bahan-harga-outlet/bulk-update',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                outlet_id: outletId,
                harga: harga
            },
            success: function(res) {
                if (res && res.success) {
                    uxToast('success', res.message || 'Semua harga outlet berhasil disimpan.');
                    loadHargaOutlet();
                } else {
                    uxToast('error', res.message || 'Gagal menyimpan semua harga.');
                }
            },
            error: function() {
                uxToast('error', 'Gagal menyimpan semua harga.');
            }
        });
    });

    $(document).on('click', '.btn-harga-outlet-reset', async function() {
        if (!canHargaOutletEdit) return;
        const outletId = getSelectedOutletId();
        const bahanId = $(this).data('bahan-id');
        const namaBahan = $(this).data('nama-bahan');

        if (!outletId) {
            uxToast('warning', 'Pilih outlet terlebih dahulu.');
            return;
        }

        if (!(await uxConfirm(`Reset harga ${namaBahan}?`, 'Harga akan dikembalikan ke harga default master.'))) {
            return;
        }

        $.ajax({
            url: '/inventory/qcr/bahan-harga-outlet/delete',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                outlet_id: outletId,
                bahan_id: bahanId
            },
            success: function(res) {
                if (res && res.success) {
                    uxToast('success', res.message || 'Harga outlet berhasil direset.');
                    loadHargaOutlet();
                } else {
                    uxToast('error', res.message || 'Gagal reset harga outlet.');
                }
            },
            error: function() {
                uxToast('error', 'Gagal reset harga outlet.');
            }
        });
    });

    // ===== Tabs switching =====
    const tabs = document.querySelectorAll('.office-tab');
    const panes = document.querySelectorAll('.office-pane');

    tabs.forEach(t => {
        t.addEventListener('click', () => {
            tabs.forEach(x => x.classList.remove('active'));
            t.classList.add('active');

            panes.forEach(p => p.classList.add('d-none'));
            const target = document.querySelector(t.dataset.target);
            if (target) target.classList.remove('d-none');

            setTimeout(() => {
                try { $($.fn.dataTable.tables(true)).DataTable().columns.adjust(); } catch(e){}
                refreshSelect2Outlet();
    refreshSelect2BahanHoMitra();
                refreshSelect2BahanHoMitra();
            }, 150);
        });
    });

    // ===== DataTables options =====
    const dtOpts = {
        paging: true,
        searching: true,
        ordering: true,
        info: false,
        scrollX: true,
        autoWidth: false,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        dom:
            '<"row g-2 align-items-center mb-2"' +
                '<"col-12 col-md-6"l>' +
                '<"col-12 col-md-6"f>' +
            '>' +
            '<"row"<"col-12"tr>>' +
            '<"row g-2 align-items-center mt-2"' +
                '<"col-12 col-md-6 small text-muted"i>' +
                '<"col-12 col-md-6"p>' +
            '>',
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            emptyTable: "Belum ada data",
            zeroRecords: "Data tidak ditemukan",
            paginate: { previous: "‹", next: "›" }
        }
    };

    ['#menuTable', '#bahanTable', '#bahanDscTable', '#menuBumTable', '#stockTable'].forEach(function(selector) {
        if (!$(selector).length) return;

        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
        }
        $(selector).DataTable(dtOpts);
    });

    // ===== Select2 =====
    initSelect2Element('#menuSelect', {
        dropdownParent: $('#modalBomAdd'),
        placeholder: '-- Pilih Menu --'
    });

    refreshSelect2Outlet();

    // ===== Reset form when modal closed =====
    $('.modal').on('hidden.bs.modal', function () {
        const form = $(this).find('form');
        if (form.length) form.trigger('reset');
    });

    // ===== MENU: edit =====
    $(document).on('click', '.btn-menu-edit', function() {
        if (!canMenuUpdate) return;
        const id = $(this).data('id');
        $('#menuEditName').val($(this).data('name'));
        $('#menuEditHarga').val($(this).data('harga'));
        $('#formMenuEdit').attr('action', `/master/menu/update/${id}`);
        $('#modalMenuEdit').modal('show');
    });

    // ===== MENU: delete =====
    $(document).on('click', '.btn-menu-delete', function() {
        if (!canMenuDestroy) return;
        const id = $(this).data('id');
        $('#menuDeleteName').text($(this).data('name'));
        $('#formMenuDelete').attr('action', `/master/menu/delete/${id}`);
        $('#modalMenuDelete').modal('show');
    });

    // ===== BAHAN: edit =====
    $(document).on('click', '.btn-bahan-edit', function() {
        if (!canBahanUpdate) return;
        const id = $(this).data('id');
        $('#bahanEditNama').val($(this).data('nama'));
        $('#bahanEditQty').val($(this).data('qty'));
        $('#bahanEditSatuan').val($(this).data('satuan'));
        $('#bahanEditKonversi').val($(this).data('konversi'));
        $('#bahanEditHarga').val($(this).data('harga'));
        $('#formBahanEdit').attr('action', `/master/bahan/update/${id}`);
        $('#modalBahanEdit').modal('show');
    });

    // ===== BAHAN: delete =====
    $(document).on('click', '.btn-bahan-delete', function() {
        if (!canBahanDestroy) return;
        const id = $(this).data('id');
        $('#bahanDeleteName').text($(this).data('name'));
        $('#formBahanDelete').attr('action', `/master/bahan/delete/${id}`);
        $('#modalBahanDelete').modal('show');
    });

    // ===== BAHAN DSC: edit =====
    $(document).on('click', '.btn-bahan-dsc-edit', function() {
        if (!canBahanDscUpdate) return;
        const id = $(this).data('id');
        $('#bahanDscEditNama').val($(this).data('nama'));
        $('#bahanDscEditSatuan').val($(this).data('satuan'));
        $('#bahanDscEditIsTepung').val(String($(this).data('is-tepung') ?? 0));
        $('#bahanDscEditIsActive').val(String($(this).data('is-active') ?? 1));
        $('#formBahanDscEdit').attr('action', `/master/bahan-dsc/update/${id}`);
        $('#modalBahanDscEdit').modal('show');
    });

    // ===== BAHAN DSC: delete/nonaktif =====
    $(document).on('click', '.btn-bahan-dsc-delete', function() {
        if (!canBahanDscDestroy) return;
        const id = $(this).data('id');
        $('#bahanDscDeleteName').text($(this).data('name'));
        $('#formBahanDscDelete').attr('action', `/master/bahan-dsc/delete/${id}`);
        $('#modalBahanDscDelete').modal('show');
    });

    // ===== BOM: view =====
    $(document).on('click', '.btn-bom-view', function() {
        const menuId = $(this).data('id');
        const menuName = $(this).data('name');

        $('#bomViewTitle').text(menuName);
        $('#bomViewBody').html('<tr><td colspan="3" class="ux-loading-row">Memuat...</td></tr>');
        $('#modalBomView').modal('show');

        $.get(`/bum/${menuId}/detail`, function(data) {
            data = data || [];
            if (!data.length) {
                $('#bomViewBody').html('<tr><td colspan="3" class="text-center text-muted">Belum ada bahan</td></tr>');
                return;
            }
            const rows = data.map(b => `
                <tr>
                    <td>${b.nama_bahan}</td>
                    <td>${b.satuan}</td>
                    <td class="num fw-semibold">${b.qty ?? 0}</td>
                </tr>
            `).join('');
            $('#bomViewBody').html(rows);
        }).fail(function(){
            $('#bomViewBody').html('<tr><td colspan="3" class="text-center text-danger">Gagal memuat BOM</td></tr>');
        });
    });

    // ===== BOM: edit =====
    $(document).on('click', '.btn-bom-edit', function() {
        if (!canBomUpdate) return;
        const menuId = $(this).data('id');
        const menuName = $(this).data('name');

        $('#bomEditTitle').text(menuName);
        $('#bomEditForm').attr('action', `/inventory/bum/update/${menuId}`);

        $('#bomEditBody').html(`<tr><td colspan="4" class="ux-loading-row">Memuat...</td></tr>`);
        $('#modalBomEdit').modal('show');

        $.get(`/bum/${menuId}/detail`, function(selected) {
            selected = selected || [];

            // map bahan_id => qty
            const map = {};
            selected.forEach(x => { map[Number(x.bahan_id)] = x.qty; });

            let html = '';
            @foreach ($bahan as $b)
            {
                const idB = Number({{ $b->id }});
                const isChecked = (map[idB] !== undefined);
                const qtyVal = (map[idB] !== undefined && map[idB] !== null) ? map[idB] : 1;

                html += `
                  <tr>
                    <td class="center">
                      <input type="checkbox"
                             class="form-check-input bom-check"
                             name="bahan_id[]"
                             value="${idB}"
                             ${isChecked ? 'checked' : ''}>
                    </td>
                    <td>{{ $b->nama_bahan }}</td>
                    <td>{{ $b->satuan }}</td>
                    <td>
                      <input type="number"
                             class="form-control form-control-sm bom-qty"
                             name="qty[${idB}]"
                             step="0.01" min="0"
                             value="${qtyVal}"
                             ${isChecked ? '' : 'disabled'}>
                    </td>
                  </tr>
                `;
            }
            @endforeach

            $('#bomEditBody').html(html);
        }).fail(function(){
            $('#bomEditBody').html(`<tr><td colspan="4" class="text-center text-danger">Gagal memuat BOM</td></tr>`);
        });
    });

    $(document).on('change', '.bom-check', function(){
      const tr = $(this).closest('tr');
      const qtyInput = tr.find('.bom-qty');
      if($(this).is(':checked')){
        qtyInput.prop('disabled', false);
        if(!qtyInput.val()) qtyInput.val(1);
      }else{
        qtyInput.prop('disabled', true);
      }
    });

    // ===== STOCK: edit load =====
    $(document).on("click", ".btn-edit-stock", function() {
        if (!canStockUpdate) return;
        const stockId = $(this).data("id");
        $("#editStockForm").attr("action", `/master/stock/update/${stockId}`);
        $("#editStockBody").html('<div class="text-center text-muted">Memuat...</div>');
        $("#editStockModal").modal("show");

        $.get(`/master/stock/${stockId}/edit`, function(data) {
            const html = `
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Bahan</label>
                    <select name="bahan_id" class="form-select" required>
                        ${(data.bahanList || []).map(b => `<option value="${b.id}" ${b.id==data.bahan_id?'selected':''}>${b.nama_bahan}</option>`).join('')}
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Shift</label>
                        <select name="shift" class="form-select" required>
                            <option value="1" ${data.shift=='1'?'selected':''}>1</option>
                            <option value="2" ${data.shift=='2'?'selected':''}>2</option>
                            <option value="TOT" ${data.shift=='TOT'?'selected':''}>TOT</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Opening Stock</label>
                        <input type="number" name="opening_stock" class="form-control" step="0.01" value="${data.opening_stock}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Used Qty</label>
                        <input type="number" name="used_qty" class="form-control" step="0.01" value="${data.used_qty}" required>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Waste Product</label>
                        <input type="number" name="waste_product" class="form-control" step="0.01" value="${data.waste_product ?? 0}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Waste Bahan</label>
                        <input type="number" name="waste_bahan" class="form-control" step="0.01" value="${data.waste_bahan ?? 0}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Waste Tepung</label>
                        <input type="number" name="waste_tepung" class="form-control" step="0.01" value="${data.waste_tepung ?? 0}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="${data.tanggal}" required>
                </div>
            `;
            $("#editStockBody").html(html);
        });
    });

    // ===== STOCK: submit ajax =====
    $('#editStockForm').on('submit', function(e){
        e.preventDefault();

        const form = $(this);
        const action = form.attr('action');

        $.ajax({
            url: action,
            method: 'POST',
            data: form.serialize(),
            success: function(res){
                if(res && res.success){
                    $('#editStockModal').modal('hide');
                    uxToast('success', res.message || 'Stock berhasil diperbarui');
                    location.reload();
                }else{
                    uxToast('error', res.message || 'Gagal update stock');
                }
            },
            error: function(){
                uxToast('error', 'Gagal update stock');
            }
        })
    });
});
</script>
@endpush

@include('Temp.Investor.footer')

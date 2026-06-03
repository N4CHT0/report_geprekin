{{-- resources/views/purchasing/stockControl.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --bg: #f5f5f9;
        --card: #ffffff;
        --text: #233446;
        --muted: #a1acb8;
        --border: #d9dee3;
        --shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        --radius: 12px;
        --primary: #696cff;
        --accent: #71dd37;
        --warn: #ffab00;
        --danger: #ff3e1d;
        --info: #03c3ec;
        --soft: #fcfcfd;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        padding: 20px 24px !important;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        padding: 20px 24px !important;
    }

    table.dataTable {
        width: 100% !important;
        margin: 0 !important;
        border-collapse: collapse !important;
    }

    #stokTable thead th {
        background-color: #f5f5f9 !important;
        padding: 14px 20px !important;
        border-bottom: 1px solid var(--border) !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #566a7f;
        letter-spacing: 0.5px;
    }

    #stokTable tbody td {
        padding: 14px 20px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f0f2f4 !important;
        color: #697a8d;
    }

    #stokTable tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04) !important;
        box-shadow: inset 4px 0 0 var(--primary);
    }

    .warehouse-card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border: 0;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .warehouse-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px 0 rgba(67, 89, 113, 0.15);
    }

    .badge {
        padding: 6px 12px;
        font-weight: 600;
        font-size: 0.75rem;
        border-radius: 6px;
        text-transform: uppercase;
    }

    .badge.bg-danger-subtle {
        background-color: #ffe5e5 !important;
        color: #ff3e1d !important;
        border: 1px solid #ffd1d1 !important;
    }

    .badge.bg-warning-subtle {
        background-color: #fff2d6 !important;
        color: #ffab00 !important;
        border: 1px solid #ffe3ad !important;
    }

    .badge.bg-success {
        background-color: #e8fadf !important;
        color: #71dd37 !important;
        border: 1px solid #d4f5c3 !important;
    }

    .badge.bg-danger {
        background-color: #ffe5e5 !important;
        color: #ff3e1d !important;
        border: 1px solid #ffd1d1 !important;
    }

    .widget-kritis {
        background-color: #ffe5e5 !important;
        color: #ff3e1d !important;
        border: 1px solid #ffd1d1;
    }

    .widget-menipis {
        background-color: #fff2d6 !important;
        color: #ffab00 !important;
        border: 1px solid #ffe3ad;
    }

    .btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        color: #fff !important;
    }

    .btn-primary:hover {
        background: #5f61e6 !important;
        border-color: #5f61e6 !important;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border-color: var(--border);
        height: 40px;
    }

    .select2-container .select2-dropdown {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    .icon-shape {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* Adjustment modal table */
    #tableAdjProduct thead th {
        background-color: #f8f9fa;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #566a7f;
    }
</style>

<main class="app-main">
    <div class="app-content py-4">
        <div class="container-fluid">

            {{-- Page Header --}}
            <div class="row align-items-center mb-4 g-3">
                <div class="col-sm">
                    <h4 class="fw-bold text-dark mb-1">Dashboard Monitoring</h4>
                    <p class="text-muted mb-0 small">Ringkasan aktivitas dan kontrol Supply Chain Management real-time</p>
                </div>
            </div>

            {{-- Warehouse Cards --}}
            <div class="row g-3 mb-4">
                @foreach($warehouses as $dc)
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card warehouse-card h-100">
                        <div class="card-body p-4 text-center">
                            <div class="d-flex justify-content-center mb-3">
                                <div class="icon-shape bg-light" style="color: var(--primary); width: 48px; height: 48px;">
                                    <i class="bi bi-building fs-3"></i>
                                </div>
                            </div>
                            <h5 class="fw-bold text-dark mb-1" style="font-size: 1rem;">{{ $dc->nama_warehouse }}</h5>
                            <p class="text-muted small mb-3">Distributor Center</p>
                            <a href="/warehouse/{{ $dc->id }}" class="btn btn-sm btn-light border text-primary w-100" style="border-radius: 6px;">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Metric Widgets --}}
            <div class="row g-3 mb-4 align-items-stretch">
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 widget-kritis rounded-3 shadow-none h-100">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="p-2 bg-white rounded text-danger">
                                <i class="bi bi-exclamation-octagon fs-3"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">Stok Habis/Kosong</h6>
                                <h3 class="fw-bold mb-0 mt-1">{{ $kritisCount }} <span class="fs-6 fw-normal">Bahan</span></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 widget-menipis rounded-3 shadow-none h-100">
                        <div class="card-body p-3 d-flex align-items-center gap-3">
                            <div class="p-2 bg-white rounded text-warning">
                                <i class="bi bi-shield-alert fs-3"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-semibold small text-uppercase" style="letter-spacing: 0.5px;">Stok Menipis (Warning)</h6>
                                <h3 class="fw-bold mb-0 mt-1">{{ $menipisCount }} <span class="fs-6 fw-normal">Bahan</span></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <button class="btn btn-outline-primary w-100 h-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#modalTransfer">
                        <i class="bi bi-arrow-left-right fs-5"></i> Transfer Antar Gudang
                    </button>
                </div>

                <div class="col-md-6 col-lg-3">
                    <button class="btn btn-outline-primary w-100 h-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#modalInventoryAdjustment">
                        <i class="bi bi-pencil-square fs-5"></i> Inventory Adjustment
                    </button>
                </div>
            </div>

            {{-- Global Monitoring Table --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <h5 class="mb-1 fw-bold text-dark">Real-Time Global Monitoring</h5>
                            <small class="text-muted">Data pergerakan stok logistik multi-gudang secara menyeluruh</small>
                        </div>
                        <div class="col-md-auto">
                            {{-- Filter date UI kept for UX but no longer drives stock query --}}
                            <form action="" method="GET" class="d-flex align-items-center bg-light px-2 py-1 rounded border">
                                <label class="mx-2 fw-bold text-secondary text-nowrap" style="font-size: 10px; letter-spacing: 0.5px; text-transform: uppercase;">
                                    <i class="bi bi-calendar3 me-1"></i> Filter Date:
                                </label>
                                <input type="date" name="filter_date" value="{{ $selectedDate }}"
                                    class="form-control form-control-sm border-0 bg-transparent py-0 px-1 text-dark fw-semibold"
                                    style="height: auto;" onchange="this.form.submit()">
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="stokTable">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nama Bahan</th>
                                    @foreach($warehouses as $wh)
                                    <th class="text-center">{{ $wh->nama_warehouse }}</th>
                                    @endforeach
                                    <th class="text-center bg-light text-dark fw-bold" style="width: 160px;">Total Global</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @forelse($products as $p)
                                @php $totalGlobal = 0; @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $p->nama_bahan }}</div>
                                        <small class="text-muted">Base Price: Rp {{ number_format($p->harga_satuan ?? 0, 0, ',', '.') }}</small>
                                        @if(!$p->bahan_id_lama)
                                        <div class="mt-1">
                                            <span class="badge bg-light text-secondary border px-2" style="font-size: 0.6rem; font-weight: 700;">NEW ITEM</span>
                                        </div>
                                        @endif
                                    </td>

                                    @foreach($warehouses as $wh)
                                    @php
                                    $stok = $stokData[$p->id][$wh->id] ?? 0;
                                    $totalGlobal += $stok;
                                    @endphp
                                    <td class="text-center">
                                        @if($stok <= 0)
                                            <span class="badge bg-danger-subtle px-3 fw-bold text-danger">0</span>
                                            @elseif($stok < ($p->stok_minimal ?? 15))
                                                <span class="badge bg-warning-subtle text-warning-emphasis px-3 fw-bold">
                                                    {{ number_format($stok, 2, ',', '.') }}
                                                </span>
                                                @else
                                                <span class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                    {{ number_format($stok, 2, ',', '.') }}
                                                </span>
                                                @endif
                                                <div class="d-block text-muted" style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; margin-top: 3px;">
                                                    {{ $p->nama_satuan ?? '' }}
                                                </div>
                                    </td>
                                    @endforeach

                                    <td class="text-center bg-light-subtle fw-bold text-primary" style="font-size: 0.95rem;">
                                        {{ number_format($totalGlobal, 2, ',', '.') }}
                                        <div class="d-block text-muted" style="font-size: 0.65rem; font-weight: 600; text-transform: uppercase; margin-top: 3px;">
                                            {{ $p->nama_satuan ?? '' }}
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ count($warehouses) + 2 }}" class="py-5 text-center text-muted">
                                        <div class="py-3">
                                            <i class="bi bi-inbox text-light d-block mb-2" style="font-size: 3rem;"></i>
                                            Data monitoring stok global belum tersedia.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- History & Fast Moving --}}
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 px-4 border-bottom fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-clock-history text-primary"></i> 10 History Transaksi Terakhir
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px;">
                                            <th class="ps-4">Nama Bahan</th>
                                            <th width="120px" class="text-center">Tipe</th>
                                            <th width="140px" class="text-end pe-4">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($histories as $h)
                                        <tr>
                                            <td class="ps-4 fw-medium text-dark small">{{ $h->nama_bahan }}</td>
                                            <td class="text-center">
                                                {{--
                                                    FIX #3: tipe enum in DB is uppercase (MASUK/KELUAR/ADJUSTMENT/WASTE).
                                                    Use strtoupper() comparison so badge colour is always correct.
                                                --}}
                                                @php $tipeUpper = strtoupper($h->tipe); @endphp
                                                <span class="badge {{ $tipeUpper === 'MASUK' ? 'bg-success' : ($tipeUpper === 'ADJUSTMENT' ? 'bg-info' : 'bg-danger') }}"
                                                    style="font-size: 0.65rem; padding: 4px 8px;">
                                                    {{ $tipeUpper }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-dark small">{{ number_format(abs($h->jumlah)) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                        <div class="card-header bg-white py-3 px-4 border-bottom fw-bold text-danger d-flex align-items-center gap-2">
                            <i class="bi bi-fire"></i> Top 5 Fast Moving (30 Hari Terakhir)
                        </div>
                        <div class="card-body p-4">
                            @foreach($fastMoving as $fm)
                            @php
                            $maxKeluar = $fastMoving->first()->total_keluar ?: 1;
                            $persen = ($fm->total_keluar / $maxKeluar) * 100;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-semibold text-dark" style="font-size: 0.88rem;">{{ $fm->nama_bahan }}</span>
                                    <span class="text-muted fw-medium">{{ number_format($fm->total_keluar) }} Unit</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: #eeeeee;">
                                    <div class="progress-bar bg-danger rounded-3" style="width: {{ $persen }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== MODAL: TRANSFER STOK ===== --}}
            <div class="modal fade" id="modalTransfer" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="fw-bold text-dark mb-0">🔄 Transfer Stok Antar Gudang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('stok.transfer') }}" method="POST">
                            @csrf
                            <div class="modal-body px-4 pb-3">
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Pilih Bahan Baku</label>
                                    <select name="id_bahan" class="form-select select2-bahan2" required style="width: 100%;">
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_bahan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-secondary fw-semibold small mb-1">Dari Gudang (Asal)</label>
                                        <select name="dari_warehouse" class="form-select form-select-sm shadow-none">
                                            @foreach($warehouses as $w)
                                            <option value="{{ $w->id }}">{{ $w->nama_warehouse }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-secondary fw-semibold small mb-1">Ke Gudang (Tujuan)</label>
                                        <select name="tujuan_id" class="form-select form-select-sm shadow-none">
                                            @foreach($warehouses as $w)
                                            <option value="{{ $w->id }}">{{ $w->nama_warehouse }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="tipe_tujuan" value="warehouse">
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Jenis Armada <span class="text-danger">*</span></label>
                                    <select name="jenis_armada" class="form-select form-select-sm shadow-none" required>
                                        <option value="motor">Motor</option>
                                        <option value="pickup">Pickup</option>
                                        <option value="truk">Truk</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Jumlah Transfer</label>
                                    <input type="number" name="jumlah" class="form-control form-control-sm shadow-none" min="1"
                                        placeholder="Masukkan total qty transfer" required>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 px-4 pb-4">
                                <button type="submit" class="btn btn-primary w-100 py-2">Kirim Barang Sekarang</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ===== MODAL: INPUT TRANSAKSI STOK ===== --}}
            <div class="modal fade" id="modalStok" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark">Input Transaksi Stok</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('stok.store') }}" method="POST">
                            @csrf
                            <div class="modal-body px-4 pb-3">
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Bahan</label>
                                    <select name="bahan_id" class="form-select select2-bahan" required style="width: 100%;">
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama_bahan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Lokasi Gudang</label>
                                    <select name="warehouse_id" class="form-select form-select-sm shadow-none" required>
                                        @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->nama_warehouse }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-secondary fw-semibold small mb-1">Tipe</label>
                                        {{--
                                            FIX #3: values now match DB enum uppercase (MASUK / KELUAR)
                                        --}}
                                        <select name="tipe" class="form-select form-select-sm shadow-none" required>
                                            <option value="MASUK">Barang Masuk (+)</option>
                                            <option value="KELUAR">Barang Keluar (-)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-secondary fw-semibold small mb-1">Jumlah</label>
                                        <input type="number" name="jumlah" class="form-control form-control-sm shadow-none" placeholder="0" required>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label text-secondary fw-semibold small mb-1">Keterangan</label>
                                    <input type="text" name="keterangan" class="form-control form-control-sm shadow-none" placeholder="Contoh: Pengiriman rutin">
                                </div>
                            </div>
                            <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                                <button type="button" class="btn btn-sm btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-sm btn-primary px-4">Simpan Transaksi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ===== MODAL: INVENTORY ADJUSTMENT ===== --}}
            <div class="modal fade" id="modalInventoryAdjustment" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content border-0" style="border-radius: 15px;">
                        <div class="modal-header border-bottom-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold">Inventory Adjustment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body px-4 pb-4">
                            <form id="formInventoryAdjustment">
                                @csrf

                                {{-- === Transaction Information (matches screenshot) === --}}
                                <div class="card border shadow-none mb-3" style="background-color: #fcfcfc;">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3" style="font-size: 0.9rem;">Transaction Information</h6>
                                        <div class="row g-3">
                                            {{--
                                                FIX #8: Added Location (warehouse) field and Purpose field
                                                to match the screenshot UI.
                                            --}}
                                            <div class="col-md-6">
                                                <label class="small fw-bold">Location (Gudang) <span class="text-danger">*</span></label>
                                                <select name="warehouse_id" id="adjWarehouseId" class="form-select form-select-sm shadow-none" required>
                                                    <option value="">- Select Location -</option>
                                                    @foreach($warehouses as $w)
                                                    <option value="{{ $w->id }}">{{ $w->nama_warehouse }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Opname Date <span class="text-danger">*</span></label>
                                                <input type="date" name="adjustment_date" class="form-control form-control-sm shadow-none"
                                                    value="{{ date('Y-m-d') }}" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="small fw-bold">Jenis Adjustment <span class="text-danger">*</span></label>
                                                {{--
                                                    FIX #3: value matches DB enum (MASUK/KELUAR → mapped
                                                    to in/out in JS, but tipe sent to server = ADJUSTMENT)
                                                --}}
                                                <select name="adjustment_type" id="adjustmentType" class="form-select form-select-sm shadow-none" required>
                                                    <option value="in">Penambahan (Stock In)</option>
                                                    <option value="out">Pengurangan (Stock Out)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="small fw-bold">Keterangan / Purpose <span class="text-danger">*</span></label>
                                                <input type="text" name="notes" class="form-control form-control-sm shadow-none"
                                                    placeholder="Contoh: Stok opname bulanan / Barang rusak" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- === Product Detail === --}}
                                <div class="card border shadow-none mb-3">
                                    <div class="card-body p-0">
                                        <div class="p-3 d-flex justify-content-between align-items-center">
                                            <h6 class="fw-bold mb-0" style="font-size: 0.9rem;">
                                                Stock Opname Detail (Total <span id="adjTotalProduct">0</span>)
                                            </h6>
                                            <div class="d-flex gap-2 align-items-center">
                                                {{-- FIX #8: File upload for bulk import (matches screenshot) --}}
                                                <div class="input-group input-group-sm" style="width: 260px;">
                                                    <input type="file" class="form-control form-control-sm shadow-none" id="adjFileUpload" accept=".xlsx,.csv">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUploadFile">
                                                        <i class="bi bi-upload me-1"></i> Upload
                                                    </button>
                                                </div>
                                                <button type="button" id="btnBrowseProductAdj" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-search me-1"></i> Browse Product
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0" id="tableAdjProduct">
                                                <thead class="bg-light text-uppercase" style="font-size: 11px;">
                                                    <tr>
                                                        <th class="ps-3 py-2">Product Name</th>
                                                        <th class="py-2">Product Code</th>
                                                        <th class="py-2 text-center">Unit</th>
                                                        {{--
                                                            FIX #8: "Stock" = physical count field,
                                                            "Current Stock" = system stock (read-only)
                                                        --}}
                                                        <th class="py-2 text-center" style="width: 110px;">Stock (Fisik)</th>
                                                        <th class="py-2 text-center">Current Stock</th>
                                                        <th class="py-2 text-center" style="width: 120px;">Adj Qty</th>
                                                        <th class="py-2 text-center">Final Stock</th>
                                                        <th class="py-2 text-end">Est. Value/Unit (IDR)</th>
                                                        <th class="py-2 text-end">Est. Total (IDR)</th>
                                                        <th class="pe-3 text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="adjProductContainer">
                                                    <tr id="emptyRowAdj">
                                                        <td colspan="10" class="text-center py-4 text-muted small">
                                                            Belum ada produk. Klik "Browse Product" untuk menambahkan.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- === Transaction Summary (matches screenshot) === --}}
                                <div class="card border shadow-none mb-3" style="background-color: #fcfcfc;">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3" style="font-size: 0.9rem;">Transaction Summary</h6>
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <label class="small fw-bold">Additional Information</label>
                                                <textarea name="additional_info" class="form-control form-control-sm shadow-none" rows="3"
                                                    placeholder="Catatan tambahan (opsional)..."></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small fw-bold">Estimated Stock Opname Total</label>
                                                {{-- FIX #8: live-updated total value field --}}
                                                <input type="text" id="adjGrandTotal" class="form-control form-control-sm shadow-none bg-light fw-bold text-end"
                                                    readonly value="0,0000">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>

                        {{-- FIX #8: Added "Save as Draft" button to match screenshot --}}
                        <div class="modal-footer border-top-0 px-4 pb-4 gap-2">
                            <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-4" id="btnSaveAsDraft">
                                <i class="bi bi-bookmark me-1"></i> Save as Draft
                            </button>
                            <button type="button" class="btn btn-primary btn-sm px-4" id="btnSaveAdjustment">
                                <i class="bi bi-check2 me-1"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@push('scripts')
<script>
    // =========================================================================
    // FIX #7: All $(document).ready() blocks merged into ONE to prevent
    //         double-binding and race conditions.
    // =========================================================================
    $(document).ready(function() {

        // --- Select2 init ---
        $('.select2-bahan').select2({
            dropdownParent: $('#modalStok'),
            placeholder: "Pilih Bahan",
            allowClear: true
        });

        $('.select2-bahan2').select2({
            dropdownParent: $('#modalTransfer'),
            placeholder: "Pilih Bahan",
            allowClear: true
        });

        // --- DataTable ---
        $('#stokTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });

        // --- Modal cleanup ---
        $('#detailModal').on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        });

        // =====================================================================
        // INVENTORY ADJUSTMENT LOGIC
        // =====================================================================

        let adjProductIndex = 0;

        // FIX #2: Pass stok totals per bahan from controller so JS always has
        //         current stock per product.
        //         Controller must add: foreach($products as $p) { $p->stok_total = $totalPerBahan[$p->id] ?? 0; }
        const masterProducts = @json($products ?? []);

        // FIX #6: Use harga_satuan (the correct alias from the controller query)
        // FIX #5: Use nama_satuan (the correct alias from the controller query)

        function formatRupiahAdj(number) {
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 4,
                maximumFractionDigits: 4
            }).format(number || 0);
        }

        // FIX #1 & #4: adjQty changed to `let`; totalValue recalculated AFTER
        //              the negative-stock correction so the displayed value is correct.
        function calculateAdjRow(row) {
            const adjType = $('#adjustmentType').val();
            const currentStock = parseFloat(row.find('.current-stock').val()) || 0;
            let adjQty = parseFloat(row.find('.adj-qty').val()) || 0; // FIX #1: let, not const
            const unitCost = parseFloat(row.find('.unit-cost').val()) || 0;

            let finalStock = 0;
            if (adjType === 'in') {
                finalStock = currentStock + adjQty;
            } else {
                finalStock = currentStock - adjQty;
                if (finalStock < 0) {
                    Swal.fire('Peringatan', 'Final stok tidak boleh minus!', 'warning');
                    adjQty = currentStock; // FIX #1: now works because let
                    row.find('.adj-qty').val(adjQty);
                    finalStock = 0;
                }
            }

            // FIX #4: totalValue computed AFTER adjQty may have been corrected
            const totalValue = adjQty * unitCost;

            row.find('.final-stock-text').text(formatRupiahAdj(finalStock));
            row.find('.final-stock-input').val(finalStock);
            row.find('.total-value-text').text(formatRupiahAdj(totalValue));

            updateGrandTotal();
        }

        // FIX #8: Grand total updater for Transaction Summary field
        function updateGrandTotal() {
            let grand = 0;
            $('.adj-row').each(function() {
                const adjQty = parseFloat($(this).find('.adj-qty').val()) || 0;
                const unitCost = parseFloat($(this).find('.unit-cost').val()) || 0;
                grand += adjQty * unitCost;
            });
            $('#adjGrandTotal').val(formatRupiahAdj(grand));
        }

        // Recalculate all rows when adjustment type changes
        $('#adjustmentType').on('change', function() {
            $('.adj-row').each(function() {
                calculateAdjRow($(this));
            });
        });

        // Recalculate on qty input
        $(document).on('input', '.adj-qty', function() {
            calculateAdjRow($(this).closest('tr'));
        });

        // Remove row
        $(document).on('click', '.remove-adj-row', function() {
            $(this).closest('tr').remove();
            updateAdjCounter();
            updateGrandTotal();
        });

        function updateAdjCounter() {
            const count = $('.adj-row').length;
            $('#adjTotalProduct').text(count);
            if (count === 0) {
                $('#adjProductContainer').html(`
                    <tr id="emptyRowAdj">
                        <td colspan="10" class="text-center py-4 text-muted small">
                            Belum ada produk. Klik "Browse Product" untuk menambahkan.
                        </td>
                    </tr>
                `);
                updateGrandTotal();
            }
        }

        // Browse Product
        $('#btnBrowseProductAdj').on('click', function() {
            let options = {};
            masterProducts.forEach(p => {
                // FIX #2: use stok_total (enriched in controller) instead of p.stok
                const stokDisplay = (p.stok_total !== undefined) ? p.stok_total : 0;
                // FIX #5: use nama_satuan; FIX for product_code field name
                options[p.id] = `${p.product_code ?? '-'} - ${p.nama_bahan} (Stok: ${stokDisplay} ${p.nama_satuan ?? ''})`;
            });

            Swal.fire({
                title: 'Pilih Produk',
                input: 'select',
                inputOptions: options,
                inputPlaceholder: 'Cari produk untuk disesuaikan...',
                showCancelButton: true,
                confirmButtonText: 'Tambah',
                customClass: {
                    confirmButton: 'btn btn-primary btn-sm px-4',
                    cancelButton: 'btn btn-light btn-sm px-4'
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const selected = masterProducts.find(p => p.id == result.value);

                    // FIX #2: safe current stock from enriched stok_total
                    const currentStock = selected.stok_total ?? 0;

                    // FIX #5: correct satuan field: nama_satuan
                    const satuanLabel = selected.nama_satuan ?? '';

                    // FIX #6: correct price field: harga_satuan
                    const unitCost = selected.harga_satuan ?? 0;

                    // FIX for product_code field name
                    const productCode = selected.product_code ?? '-';

                    $('#emptyRowAdj').remove();
                    adjProductIndex++;

                    const rowHtml = `
                        <tr class="adj-row">
                            <td class="ps-3">
                                <input type="hidden" name="items[${adjProductIndex}][product_id]" value="${selected.id}">
                                <div class="fw-bold small text-dark">${selected.nama_bahan}</div>
                            </td>
                            <td><small class="text-muted">${productCode}</small></td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border small">${satuanLabel}</span>
                            </td>

                            {{-- FIX #8: Physical count (stock opname) input --}}
                            <td>
                                <input type="number" name="items[${adjProductIndex}][physical_stock]"
                                    class="form-control form-control-sm text-center shadow-none physical-stock"
                                    value="${currentStock}" min="0" step="0.0001">
                            </td>

                            <td class="text-center bg-light">
                                <span class="fw-bold">${formatRupiahAdj(currentStock)}</span>
                                <input type="hidden" name="items[${adjProductIndex}][current_stock]" class="current-stock" value="${currentStock}">
                            </td>

                            <td>
                                <input type="number" name="items[${adjProductIndex}][adj_qty]"
                                    class="form-control form-control-sm text-center shadow-none border-primary adj-qty fw-bold"
                                    value="0" min="0" step="0.0001">
                            </td>

                            <td class="text-center bg-light">
                                <span class="fw-bold text-primary final-stock-text">${formatRupiahAdj(currentStock)}</span>
                                <input type="hidden" name="items[${adjProductIndex}][final_stock]" class="final-stock-input" value="${currentStock}">
                            </td>

                            <td class="text-end">
                                <span class="small text-muted">${formatRupiahAdj(unitCost)}</span>
                                <input type="hidden" name="items[${adjProductIndex}][unit_cost]" class="unit-cost" value="${unitCost}">
                            </td>

                            <td class="text-end fw-bold total-value-text">0,0000</td>

                            <td class="pe-3 text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-adj-row">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    $('#adjProductContainer').append(rowHtml);
                    updateAdjCounter();
                }
            });
        });

        // Save as Draft
        function collectAdjFormData() {
            const formData = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                warehouse_id: $('#adjWarehouseId').val(),
                adjustment_date: $('input[name="adjustment_date"]').val(),
                adjustment_type: $('#adjustmentType').val(),
                notes: $('input[name="notes"]').val(),
                additional_info: $('textarea[name="additional_info"]').val(),
                items: []
            };

            $('.adj-row').each(function() {
                formData.items.push({
                    product_id: $(this).find('input[name*="[product_id]"]').val(),
                    current_stock: $(this).find('.current-stock').val(),
                    physical_stock: $(this).find('.physical-stock').val(),
                    adj_qty: $(this).find('.adj-qty').val(),
                    final_stock: $(this).find('.final-stock-input').val(),
                    unit_cost: $(this).find('.unit-cost').val(),
                });
            });

            return formData;
        }

        // ---------------------------------------------------------------
        // Validasi sisi client sebelum kirim ke server
        // ---------------------------------------------------------------
        function validateAdjForm() {
            if (!$('#adjWarehouseId').val()) {
                Swal.fire('Peringatan', 'Pilih lokasi gudang terlebih dahulu!', 'warning');
                return false;
            }
            if (!$('input[name="adjustment_date"]').val()) {
                Swal.fire('Peringatan', 'Tanggal opname wajib diisi!', 'warning');
                return false;
            }
            if (!$('input[name="notes"]').val().trim()) {
                Swal.fire('Peringatan', 'Keterangan wajib diisi!', 'warning');
                return false;
            }
            if ($('.adj-row').length === 0) {
                Swal.fire('Peringatan', 'Pilih minimal 1 produk!', 'warning');
                return false;
            }
            return true;
        }

        // ---------------------------------------------------------------
        // SAVE AS DRAFT
        // ---------------------------------------------------------------
        $('#btnSaveAsDraft').on('click', function() {
            if (!validateAdjForm()) return;

            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ route('stock.opname.draft') }}",
                method: 'POST',
                data: JSON.stringify(collectAdjFormData()),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Draft Tersimpan!',
                            html: `Nomor Opname: <strong>${res.nomor_opname}</strong><br>
                            Data berhasil disimpan sebagai draft.`,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            $('#modalInventoryAdjustment').modal('hide');
                            // Reload halaman agar widget & tabel terupdate
                            location.reload();
                        });
                    } else {
                        Swal.fire('Gagal', res.message, 'error');
                    }
                },
                error: function(xhr) {
                    const res = xhr.responseJSON;

                    // Tampilkan error validasi per field jika ada
                    if (xhr.status === 422 && res.errors) {
                        const errList = Object.values(res.errors).flat().join('<br>');
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Gagal',
                            html: errList
                        });
                    } else {
                        Swal.fire('Error', res?.message ?? 'Terjadi kesalahan server.', 'error');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="bi bi-bookmark me-1"></i> Save as Draft');
                }
            });
        });

        // ---------------------------------------------------------------
        // SAVE (confirmed — masuk ke ledger tbl_stock_transactions)
        // ---------------------------------------------------------------
        $('#btnSaveAdjustment').on('click', function() {
            if (!validateAdjForm()) return;

            const $btn = $(this);

            // Konfirmasi sebelum simpan permanen
            Swal.fire({
                icon: 'question',
                title: 'Simpan Adjustment?',
                html: 'Data akan disimpan permanen dan stok akan <strong>langsung diupdate</strong>.<br>Pastikan semua data sudah benar.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Cek Lagi',
                confirmButtonColor: '#696cff',
            }).then((result) => {
                if (!result.isConfirmed) return;

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('stock.opname.store') }}",
                    method: 'POST',
                    data: JSON.stringify(collectAdjFormData()),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Adjustment Berhasil!',
                                html: `Nomor Opname: <strong>${res.nomor_opname}</strong><br>
                                Stok telah diperbarui di database.`,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                $('#modalInventoryAdjustment').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        const res = xhr.responseJSON;
                        if (xhr.status === 422 && res.errors) {
                            const errList = Object.values(res.errors).flat().join('<br>');
                            Swal.fire({
                                icon: 'warning',
                                title: 'Validasi Gagal',
                                html: errList
                            });
                        } else {
                            Swal.fire('Error', res?.message ?? 'Terjadi kesalahan server.', 'error');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="bi bi-check2 me-1"></i> Save');
                    }
                });
            });
        });

    }); // end single $(document).ready()
</script>
@endpush

@include('Temp.Investor.footer')
@include('Temp.Investor.header')

<style>
    :root {
        --primary: #696cff;
        --accent: #71dd37;
        --warn: #ffab00;
        --danger: #ff3e1d;
        --info: #03c3ec;
        --bg: #f5f5f9;
        --card: #fff;
        --border: #e0e0f0;
        --text: #233446;
        --muted: #8a8d9f;
        --radius: 12px;
        --shadow: 0 2px 8px rgba(67, 89, 113, 0.10);
    }

    body {
        background: var(--bg);
    }

    /* ── Layout ── */
    .report-header {
        padding: 28px 0 16px;
    }

    .report-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text);
    }

    .report-sub {
        font-size: .85rem;
        color: var(--muted);
        margin-top: 2px;
    }

    /* ── Summary Cards ── */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    .s-card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 18px 20px;
        border-left: 4px solid transparent;
        transition: transform .2s;
    }

    .s-card:hover {
        transform: translateY(-2px);
    }

    .s-card.masuk {
        border-color: var(--accent);
    }

    .s-card.keluar {
        border-color: var(--danger);
    }

    .s-card.adj {
        border-color: var(--warn);
    }

    .s-card.nilai {
        border-color: var(--primary);
    }

    .s-card .sc-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .6px;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .s-card .sc-value {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text);
    }

    .s-card .sc-sub {
        font-size: .75rem;
        color: var(--muted);
        margin-top: 3px;
    }

    /* ── Filter Card ── */
    .filter-card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        padding: 20px 24px;
        margin-bottom: 20px;
    }

    .filter-card .filter-title {
        font-size: .8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--primary);
        margin-bottom: 14px;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 1px solid var(--border);
        font-size: .85rem;
        height: 38px;
        padding: 0 12px;
        color: var(--text);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(105, 108, 255, .12);
        outline: none;
    }

    .btn-filter {
        background: var(--primary);
        color: #fff;
        border: none;
        padding: 8px 20px;
        border-radius: 8px;
        font-weight: 700;
        font-size: .85rem;
        cursor: pointer;
        transition: background .2s;
    }

    .btn-filter:hover {
        background: #5153e0;
    }

    .btn-reset {
        background: transparent;
        color: var(--muted);
        border: 1px solid var(--border);
        padding: 8px 16px;
        border-radius: 8px;
        font-size: .85rem;
        cursor: pointer;
    }

    /* ── Table ── */
    .table-card {
        background: var(--card);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .table-card .tc-header {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-card .tc-title {
        font-weight: 700;
        color: var(--text);
        font-size: .95rem;
    }

    .table-card .tc-count {
        font-size: .8rem;
        color: var(--muted);
    }

    table.rtable {
        width: 100%;
        border-collapse: collapse;
    }

    table.rtable thead th {
        background: #f8f8ff;
        padding: 11px 16px;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: var(--muted);
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    table.rtable tbody td {
        padding: 11px 16px;
        font-size: .83rem;
        color: var(--text);
        border-bottom: 1px solid #f0f0f8;
        vertical-align: middle;
    }

    table.rtable tbody tr:hover {
        background: rgba(105, 108, 255, .03);
    }

    table.rtable .text-right {
        text-align: right;
    }

    table.rtable .text-center {
        text-align: center;
    }

    /* ── Badges ── */
    .badge-tipe {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .t-masuk {
        background: #e8fadf;
        color: #387a1e;
        border: 1px solid #c5edaa;
    }

    .t-keluar {
        background: #ffe5e5;
        color: #c0392b;
        border: 1px solid #ffc5c5;
    }

    .t-adjustment {
        background: #fff2d6;
        color: #b07d00;
        border: 1px solid #ffe0a0;
    }

    .t-waste {
        background: #f0f0f8;
        color: #666;
        border: 1px solid #ddd;
    }

    /* ── Qty indicator ── */
    .qty-masuk {
        color: #387a1e;
        font-weight: 700;
    }

    .qty-keluar {
        color: #c0392b;
        font-weight: 700;
    }

    .qty-adj {
        color: #b07d00;
        font-weight: 700;
    }

    /* ── Export Buttons ── */
    .btn-export {
        background: transparent;
        border: 1px solid var(--border);
        padding: 6px 14px;
        border-radius: 8px;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        color: var(--text);
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        transition: all .2s;
    }

    .btn-export:hover {
        background: #f0f0f8;
        color: var(--primary);
        border-color: var(--primary);
    }

    /* ── Pagination ── */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 4px;
        padding: 16px;
    }

    .page-link {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: .82rem;
        border: 1px solid var(--border);
        color: var(--text);
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
    }

    .page-link:hover,
    .page-link.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }

    /* ── No data ── */
    .no-data {
        text-align: center;
        padding: 48px 24px;
        color: var(--muted);
    }

    .no-data i {
        font-size: 2.5rem;
        margin-bottom: 10px;
        display: block;
        opacity: .3;
    }
</style>

<main class="app-main">
    <div class="app-content py-4">
        <div class="container-fluid">

            {{-- Header --}}
            <div class="report-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="report-title">📦 Stock Movement Report</div>
                        <div class="report-sub">Riwayat pergerakan stok masuk, keluar, adjustment, dan waste</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('reports.stock.movement', array_merge(request()->query(), ['export' => 'print'])) }}"
                            target="_blank" class="btn-export">
                            <i class="bi bi-printer"></i> Print
                        </a>
                        <a href="{{ route('exports.stock.movement', array_merge(request()->query(), ['export' => 'excel'])) }}"
                            class="btn-export">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </a>
                    </div>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="summary-grid">
                <div class="s-card masuk">
                    <div class="sc-label">Total Masuk</div>
                    <div class="sc-value">{{ number_format($summary['total_masuk'], 0, ',', '.') }}</div>
                    <div class="sc-sub">Unit diterima dalam periode</div>
                </div>
                <div class="s-card keluar">
                    <div class="sc-label">Total Keluar</div>
                    <div class="sc-value">{{ number_format($summary['total_keluar'], 0, ',', '.') }}</div>
                    <div class="sc-sub">Unit keluar dalam periode</div>
                </div>
                <div class="s-card adj">
                    <div class="sc-label">Transaksi Adjustment</div>
                    <div class="sc-value">{{ number_format($summary['total_adjustment']) }}</div>
                    <div class="sc-sub">Penyesuaian stok</div>
                </div>
                <div class="s-card nilai">
                    <div class="sc-label">Total Nilai Transaksi</div>
                    <div class="sc-value" style="font-size:1.15rem;">Rp {{ number_format($summary['total_nilai'], 0, ',', '.') }}</div>
                    <div class="sc-sub">Akumulasi nilai semua tipe</div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="filter-card">
                <div class="filter-title">🔍 Filter Data</div>
                <form method="GET" action="{{ route('reports.stock.movement') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label" style="font-size:.78rem;font-weight:600;">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size:.78rem;font-weight:600;">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size:.78rem;font-weight:600;">Produk / Bahan</label>
                            <select name="bahan_id" class="form-select">
                                <option value="">— Semua Produk —</option>
                                @foreach($allProducts as $p)
                                <option value="{{ $p->id }}" {{ request('bahan_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama_bahan }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size:.78rem;font-weight:600;">Gudang</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">— Semua Gudang —</option>
                                @foreach($allWarehouses as $w)
                                <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>
                                    {{ $w->nama_warehouse }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label" style="font-size:.78rem;font-weight:600;">Tipe Transaksi</label>
                            <select name="tipe" class="form-select">
                                <option value="">— Semua Tipe —</option>
                                <option value="MASUK" {{ request('tipe') == 'MASUK'      ? 'selected' : '' }}>Masuk</option>
                                <option value="KELUAR" {{ request('tipe') == 'KELUAR'     ? 'selected' : '' }}>Keluar</option>
                                <option value="ADJUSTMENT" {{ request('tipe') == 'ADJUSTMENT' ? 'selected' : '' }}>Adjustment</option>
                                <option value="WASTE" {{ request('tipe') == 'WASTE'      ? 'selected' : '' }}>Waste</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn-filter w-100">Tampilkan</button>
                            <a href="{{ route('reports.stock.movement') }}" class="btn-reset">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="table-card">
                <div class="tc-header">
                    <span class="tc-title">Detail Pergerakan Stok</span>
                    <span class="tc-count">{{ $movements->total() }} transaksi · {{ $start }} s/d {{ $end }}</span>
                </div>
                <div class="table-responsive">
                    <table class="rtable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Produk</th>
                                <th>Gudang</th>
                                <th class="text-center">Tipe</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Stok Sebelum</th>
                                <th class="text-center">Stok Sesudah</th>
                                <th class="text-right">Nilai</th>
                                <th>Keterangan</th>
                                <th>Referensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $i => $m)
                            <tr>
                                <td style="color:var(--muted)">{{ $movements->firstItem() + $i }}</td>
                                <td style="white-space:nowrap;">
                                    <strong>{{ \Carbon\Carbon::parse($m->created_at)->format('d M Y') }}</strong><br>
                                    <span style="font-size:.72rem;color:var(--muted)">{{ \Carbon\Carbon::parse($m->created_at)->format('H:i') }}</span>
                                </td>
                                <td>
                                    <strong>{{ $m->nama_bahan }}</strong><br>
                                    <span style="font-size:.72rem;color:var(--muted)">{{ $m->product_code }}</span>
                                </td>
                                <td>{{ $m->nama_warehouse }}</td>
                                <td class="text-center">
                                    @php $tc = strtolower($m->tipe); @endphp
                                    <span class="badge-tipe t-{{ $tc === 'masuk' ? 'masuk' : ($tc === 'keluar' ? 'keluar' : ($tc === 'adjustment' ? 'adjustment' : 'waste')) }}">
                                        {{ $m->tipe }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="qty-{{ $m->tipe === 'MASUK' ? 'masuk' : ($m->tipe === 'KELUAR' ? 'keluar' : 'adj') }}">
                                        {{ $m->tipe === 'KELUAR' ? '-' : '+' }}{{ number_format($m->jumlah, 2, ',', '.') }}
                                    </span>
                                    <span style="font-size:.72rem;color:var(--muted)"> {{ $m->nama_unit }}</span>
                                </td>
                                <td class="text-center">{{ number_format($m->stok_sebelum, 2, ',', '.') }}</td>
                                <td class="text-center"><strong>{{ number_format($m->stok_sesudah, 2, ',', '.') }}</strong></td>
                                <td class="text-right" style="white-space:nowrap;">Rp {{ number_format($m->total_nilai, 0, ',', '.') }}</td>
                                <td style="font-size:.8rem;max-width:160px;word-break:break-word;">{{ $m->keterangan ?? '-' }}</td>
                                <td style="font-size:.78rem;color:var(--muted);">
                                    {{ $m->reference_type }}<br>
                                    <strong>#{{ $m->reference_id }}</strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11">
                                    <div class="no-data">
                                        <i class="bi bi-inbox"></i>
                                        Tidak ada data pergerakan stok untuk filter yang dipilih.
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                @if($movements->hasPages())
                <div class="pagination">
                    {{ $movements->onEachSide(1)->links('pagination::simple-bootstrap-5') }}
                </div>
                @endif
            </div>

        </div>
    </div>
</main>

@include('Temp.Investor.footer')
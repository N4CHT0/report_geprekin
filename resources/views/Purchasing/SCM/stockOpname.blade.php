@include('Temp.Investor.header')

<style>
:root {
    --primary: #696cff; --accent: #71dd37; --warn: #ffab00;
    --danger: #ff3e1d; --info: #03c3ec;
    --bg: #f5f5f9; --card: #fff; --border: #e0e0f0;
    --text: #233446; --muted: #8a8d9f;
    --radius: 12px; --shadow: 0 2px 8px rgba(67,89,113,0.10);
}
body { background: var(--bg); }
.report-header { padding: 28px 0 16px; }
.report-title  { font-size: 1.5rem; font-weight: 800; color: var(--text); }
.report-sub    { font-size: .85rem; color: var(--muted); margin-top: 2px; }

.summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.s-card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); padding: 18px 20px; border-left: 4px solid transparent; transition: transform .2s; }
.s-card:hover { transform: translateY(-2px); }
.s-card.c1 { border-color: var(--primary); } .s-card.c2 { border-color: var(--accent); }
.s-card.c3 { border-color: var(--warn); }    .s-card.c4 { border-color: var(--info); }
.sc-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: var(--muted); margin-bottom: 6px; }
.sc-value { font-size: 1.6rem; font-weight: 800; color: var(--text); }
.sc-sub   { font-size: .75rem; color: var(--muted); margin-top: 3px; }

.filter-card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); padding: 20px 24px; margin-bottom: 20px; }
.filter-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--primary); margin-bottom: 14px; }
.form-control, .form-select { border-radius: 8px; border: 1px solid var(--border); font-size: .85rem; height: 38px; padding: 0 12px; color: var(--text); }
.form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(105,108,255,.12); outline: none; }
.btn-filter { background: var(--primary); color: #fff; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 700; font-size: .85rem; cursor: pointer; }
.btn-reset  { background: transparent; color: var(--muted); border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; font-size: .85rem; cursor: pointer; }

.table-card { background: var(--card); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
.tc-header { padding: 16px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.tc-title  { font-weight: 700; color: var(--text); font-size: .95rem; }
.tc-count  { font-size: .8rem; color: var(--muted); }

table.rtable { width: 100%; border-collapse: collapse; }
table.rtable thead th { background: #f8f8ff; padding: 11px 16px; font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--muted); border-bottom: 1px solid var(--border); white-space: nowrap; }
table.rtable tbody td { padding: 11px 16px; font-size: .83rem; color: var(--text); border-bottom: 1px solid #f0f0f8; vertical-align: middle; }
table.rtable tbody tr:hover { background: rgba(105,108,255,.03); }
table.rtable .text-right { text-align: right; } table.rtable .text-center { text-align: center; }

.badge-status { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .68rem; font-weight: 700; text-transform: uppercase; }
.s-confirmed { background: #e8fadf; color: #387a1e; border: 1px solid #c5edaa; }
.s-draft     { background: #f0f0f8; color: #666;    border: 1px solid #ddd; }

.badge-jenis { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .68rem; font-weight: 700; }
.j-in  { background: #e8fadf; color: #387a1e; border: 1px solid #c5edaa; }
.j-out { background: #ffe5e5; color: #c0392b; border: 1px solid #ffc5c5; }

/* Expandable row */
.expand-btn {
    background: none; border: none; cursor: pointer; color: var(--primary);
    font-size: .8rem; padding: 4px 8px; border-radius: 6px;
    display: inline-flex; align-items: center; gap: 4px;
}
.expand-btn:hover { background: #f0f0ff; }
.detail-row { display: none; }
.detail-row.open { display: table-row; }
.detail-inner {
    background: #f9f9ff; padding: 16px 20px;
    border-radius: 8px; margin: 8px 16px;
}
.detail-table { width: 100%; border-collapse: collapse; font-size: .8rem; }
.detail-table th { background: #ededff; padding: 7px 12px; font-size: .7rem; text-transform: uppercase; letter-spacing: .4px; color: var(--muted); }
.detail-table td { padding: 7px 12px; border-bottom: 1px solid #eee; color: var(--text); }
.detail-table .text-right { text-align: right; }
.detail-table .text-center { text-align: center; }

.adj-plus  { color: #387a1e; font-weight: 700; }
.adj-minus { color: #c0392b; font-weight: 700; }

.btn-export { background: transparent; border: 1px solid var(--border); padding: 6px 14px; border-radius: 8px; font-size: .8rem; font-weight: 600; cursor: pointer; color: var(--text); display: inline-flex; align-items: center; gap: 5px; text-decoration: none; transition: all .2s; }
.btn-export:hover { background: #f0f0f8; color: var(--primary); border-color: var(--primary); }
.no-data { text-align: center; padding: 48px 24px; color: var(--muted); }
.no-data i { font-size: 2.5rem; margin-bottom: 10px; display: block; opacity: .3; }
</style>

<main class="app-main">
<div class="app-content py-4">
<div class="container-fluid">

    <div class="report-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="report-title">📋 Stock Opname Report</div>
                <div class="report-sub">Rekapitulasi seluruh sesi inventory adjustment & stock opname</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.stock.opname', array_merge(request()->query(), ['export' => 'print'])) }}" target="_blank" class="btn-export">
                    <i class="bi bi-printer"></i> Print
                </a>
                <a href="{{ route('reports.stock.opname', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn-export">
                    <i class="bi bi-download"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-grid">
        <div class="s-card c1">
            <div class="sc-label">Total Sesi Opname</div>
            <div class="sc-value">{{ $summary['total'] }}</div>
            <div class="sc-sub">Dalam periode terpilih</div>
        </div>
        <div class="s-card c2">
            <div class="sc-label">Confirmed</div>
            <div class="sc-value">{{ $summary['confirmed'] }}</div>
            <div class="sc-sub">Sudah disimpan permanen</div>
        </div>
        <div class="s-card c3">
            <div class="sc-label">Draft</div>
            <div class="sc-value">{{ $summary['draft'] }}</div>
            <div class="sc-sub">Belum dikonfirmasi</div>
        </div>
        <div class="s-card c4">
            <div class="sc-label">Total Nilai Adjustment</div>
            <div class="sc-value" style="font-size:1.15rem;">Rp {{ number_format($summary['total_nilai'], 0, ',', '.') }}</div>
            <div class="sc-sub">Dari sesi confirmed saja</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <div class="filter-title">🔍 Filter Data</div>
        <form method="GET" action="{{ route('reports.stock.opname') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;">Gudang</label>
                    <select name="warehouse_id" class="form-select">
                        <option value="">— Semua Gudang —</option>
                        @foreach($allWarehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->nama_warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;">Status</label>
                    <select name="status" class="form-select">
                        <option value="">— Semua Status —</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-filter">Tampilkan</button>
                    <a href="{{ route('reports.stock.opname') }}" class="btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="table-card">
        <div class="tc-header">
            <span class="tc-title">Detail Sesi Opname</span>
            <span class="tc-count">{{ $opnames->total() }} sesi · {{ $start }} s/d {{ $end }}</span>
        </div>
        <div class="table-responsive">
            <table class="rtable">
                <thead>
                    <tr>
                        <th></th>
                        <th>No. Opname</th>
                        <th>Tanggal</th>
                        <th>Gudang</th>
                        <th class="text-center">Jenis</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Jml. Item</th>
                        <th class="text-right">Total Nilai</th>
                        <th>Keterangan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($opnames as $op)
                    @php $items = $allItems[$op->id] ?? collect(); @endphp
                    <tr>
                        <td style="width:40px;">
                            <button class="expand-btn" onclick="toggleDetail({{ $op->id }}, this)">
                                <i class="bi bi-chevron-right" id="icon-{{ $op->id }}"></i>
                            </button>
                        </td>
                        <td><strong style="color:var(--primary);">{{ $op->nomor_opname }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($op->tanggal_opname)->format('d M Y') }}</td>
                        <td>{{ $op->nama_warehouse }}</td>
                        <td class="text-center">
                            <span class="badge-jenis j-{{ $op->jenis_adjustment }}">
                                {{ $op->jenis_adjustment === 'in' ? '+ Penambahan' : '- Pengurangan' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge-status s-{{ $op->status }}">{{ $op->status }}</span>
                        </td>
                        <td class="text-center">{{ $items->count() }} produk</td>
                        <td class="text-right"><strong>Rp {{ number_format($op->total_nilai, 0, ',', '.') }}</strong></td>
                        <td style="font-size:.8rem;max-width:160px;">{{ $op->keterangan ?? '-' }}</td>
                        <td>
                            @if($op->status === 'confirmed')
                            <a href="{{ route('reports.stock.opname') }}?print_id={{ $op->id }}"
                               target="_blank" style="color:var(--muted);font-size:.8rem;">
                                <i class="bi bi-printer"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    {{-- Expandable detail row --}}
                    <tr class="detail-row" id="detail-{{ $op->id }}">
                        <td colspan="10" style="padding:0 16px 16px;">
                            <div class="detail-inner">
                                <div style="font-size:.75rem;font-weight:700;color:var(--primary);text-transform:uppercase;margin-bottom:10px;">
                                    Detail Item — {{ $op->nomor_opname }}
                                </div>
                                <table class="detail-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Produk</th>
                                            <th class="text-center">Satuan</th>
                                            <th class="text-center">Stok Sistem</th>
                                            <th class="text-center">Stok Fisik</th>
                                            <th class="text-center">Adj Qty</th>
                                            <th class="text-center">Final Stok</th>
                                            <th class="text-right">Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $j => $item)
                                        <tr>
                                            <td>{{ $j + 1 }}</td>
                                            <td><strong>{{ $item->nama_bahan }}</strong><br><span style="font-size:.7rem;color:var(--muted);">{{ $item->product_code }}</span></td>
                                            <td class="text-center">{{ $item->nama_unit ?? '-' }}</td>
                                            <td class="text-center">{{ number_format($item->current_stock, 2, ',', '.') }}</td>
                                            <td class="text-center">{{ number_format($item->physical_stock, 2, ',', '.') }}</td>
                                            <td class="text-center">
                                                <span class="{{ $op->jenis_adjustment === 'in' ? 'adj-plus' : 'adj-minus' }}">
                                                    {{ $op->jenis_adjustment === 'in' ? '+' : '-' }}{{ number_format($item->adj_qty, 2, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="text-center"><strong>{{ number_format($item->final_stock, 2, ',', '.') }}</strong></td>
                                            <td class="text-right">Rp {{ number_format($item->total_nilai, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="no-data">
                                <i class="bi bi-clipboard-x"></i>
                                Tidak ada data stock opname untuk filter yang dipilih.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($opnames->hasPages())
        <div style="padding:16px;">{{ $opnames->links() }}</div>
        @endif
    </div>

</div>
</div>
</main>

<script>
function toggleDetail(id, btn) {
    const row  = document.getElementById('detail-' + id);
    const icon = document.getElementById('icon-' + id);
    const open = row.classList.toggle('open');
    icon.className = open ? 'bi bi-chevron-down' : 'bi bi-chevron-right';
}
</script>

@include('Temp.Investor.footer')
@include('Temp.Investor.header')

<style>
:root {
    --primary:#696cff;--accent:#71dd37;--warn:#ffab00;--danger:#ff3e1d;--info:#03c3ec;
    --bg:#f5f5f9;--card:#fff;--border:#e0e0f0;--text:#233446;--muted:#8a8d9f;
    --radius:12px;--shadow:0 2px 8px rgba(67,89,113,.10);
}
body{background:var(--bg);}
.report-title{font-size:1.5rem;font-weight:800;color:var(--text);}
.report-sub  {font-size:.85rem;color:var(--muted);margin-top:2px;}
.report-header{padding:28px 0 16px;}

.summary-grid{display:grid;grid-template-columns:repeat(3,1fr) 1.4fr;gap:16px;margin-bottom:24px;}
.s-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px 20px;border-left:4px solid transparent;transition:transform .2s;}
.s-card:hover{transform:translateY(-2px);}
.s-card.c1{border-color:var(--primary);}.s-card.c2{border-color:var(--accent);}
.s-card.c3{border-color:var(--warn);} .s-card.c4{border-color:var(--info);}
.s-card.c5{border-color:var(--danger);} .s-card.c6{border-color:#696cff;}
.sc-label{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:6px;}
.sc-value{font-size:1.6rem;font-weight:800;color:var(--text);}
.sc-sub  {font-size:.75rem;color:var(--muted);margin-top:3px;}

.filter-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px 24px;margin-bottom:20px;}
.filter-title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--warn);margin-bottom:14px;}
.form-control,.form-select{border-radius:8px;border:1px solid var(--border);font-size:.85rem;height:38px;padding:0 12px;color:var(--text);}
.form-control:focus,.form-select:focus{border-color:var(--warn);box-shadow:0 0 0 3px rgba(255,171,0,.12);outline:none;}
.btn-filter{background:var(--warn);color:#fff;border:none;padding:8px 20px;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer;}
.btn-reset{background:transparent;color:var(--muted);border:1px solid var(--border);padding:8px 16px;border-radius:8px;font-size:.85rem;cursor:pointer;}

/* Two column layout: table + sidebar */
.main-layout{display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start;}

.table-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
.tc-header{padding:16px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;}
.tc-title{font-weight:700;color:var(--text);font-size:.95rem;}
.tc-count{font-size:.8rem;color:var(--muted);}

table.rtable{width:100%;border-collapse:collapse;}
table.rtable thead th{background:#f8f8ff;padding:11px 14px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);border-bottom:1px solid var(--border);white-space:nowrap;}
table.rtable tbody td{padding:10px 14px;font-size:.82rem;color:var(--text);border-bottom:1px solid #f0f0f8;vertical-align:middle;}
table.rtable tbody tr:hover{background:rgba(255,171,0,.03);}
table.rtable .text-right{text-align:right;} table.rtable .text-center{text-align:center;}

.badge-s{display:inline-block;padding:3px 9px;border-radius:20px;font-size:.67rem;font-weight:700;text-transform:uppercase;}
.s-RECEIVED{background:#e8fadf;color:#387a1e;border:1px solid #c5edaa;}
.s-PARTIAL {background:#fff8e1;color:#b07d00;border:1px solid #ffe082;}
.s-DRAFT   {background:#f0f0f8;color:#666;   border:1px solid #ddd;}

.qc-PASSED          {background:#e8fadf;color:#387a1e;border:1px solid #c5edaa;}
.qc-PARTIAL_REJECTED{background:#fff8e1;color:#b07d00;border:1px solid #ffe082;}
.qc-REJECTED        {background:#ffe5e5;color:#c0392b;border:1px solid #ffc5c5;}
.qc-PENDING         {background:#f0f0f8;color:#666;   border:1px solid #ddd;}

.expand-btn{background:none;border:none;cursor:pointer;color:var(--warn);font-size:.8rem;padding:4px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;}
.expand-btn:hover{background:#fff8e1;}
.detail-row{display:none;} .detail-row.open{display:table-row;}
.detail-inner{background:#fffbf0;padding:14px 18px;border-radius:8px;margin:6px 14px;}
.detail-table{width:100%;border-collapse:collapse;font-size:.79rem;}
.detail-table th{background:#ffefc0;padding:6px 10px;font-size:.69rem;text-transform:uppercase;letter-spacing:.4px;color:var(--muted);}
.detail-table td{padding:7px 10px;border-bottom:1px solid #eee;}
.detail-table .text-right{text-align:right;} .detail-table .text-center{text-align:center;}

/* Sidebar */
.sidebar-card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;margin-bottom:16px;}
.sidebar-title{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--warn);margin-bottom:14px;}
.top-item{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f0f0f8;font-size:.82rem;}
.top-item:last-child{border-bottom:none;}
.top-rank{font-size:.7rem;font-weight:700;color:var(--muted);width:20px;}
.top-name{flex:1;color:var(--text);font-weight:600;margin:0 8px;}
.top-val{font-size:.78rem;color:var(--warn);font-weight:700;text-align:right;}

.progress-mini{height:4px;background:#f0f0f8;border-radius:2px;margin-top:3px;}
.progress-mini-bar{height:4px;background:var(--warn);border-radius:2px;}

.btn-export{background:transparent;border:1px solid var(--border);padding:6px 14px;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;color:var(--text);display:inline-flex;align-items:center;gap:5px;text-decoration:none;transition:all .2s;}
.btn-export:hover{background:#fff8e1;color:var(--warn);border-color:var(--warn);}
.no-data{text-align:center;padding:40px;color:var(--muted);}
.no-data i{font-size:2rem;display:block;margin-bottom:8px;opacity:.3;}
</style>

<main class="app-main">
<div class="app-content py-4">
<div class="container-fluid">

    <div class="report-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="report-title">🚚 Goods Receipt Recapitulation</div>
                <div class="report-sub">Rekapitulasi penerimaan barang dari supplier beserta status QC</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('reports.gr.recap', array_merge(request()->query(), ['export'=>'print'])) }}" target="_blank" class="btn-export"><i class="bi bi-printer"></i> Print</a>
                <a href="{{ route('reports.gr.recap', array_merge(request()->query(), ['export'=>'csv'])) }}" class="btn-export"><i class="bi bi-download"></i> CSV</a>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-grid">
        <div class="s-card c1"><div class="sc-label">Total GR</div><div class="sc-value">{{ $summary['total'] }}</div><div class="sc-sub">Semua status</div></div>
        <div class="s-card c2"><div class="sc-label">Received</div><div class="sc-value">{{ $summary['received'] }}</div><div class="sc-sub">Diterima penuh</div></div>
        <div class="s-card c3"><div class="sc-label">Partial</div><div class="sc-value">{{ $summary['partial'] }}</div><div class="sc-sub">Diterima sebagian</div></div>
        <div class="s-card c4" style="grid-column:span 1;">
            <div class="sc-label">Total Nilai Penerimaan</div>
            <div class="sc-value" style="font-size:1.1rem;">Rp {{ number_format($summary['total_nilai'],0,',','.') }}</div>
            <div class="sc-sub">QC Lulus: {{ $summary['qc_passed'] }} · Tolak: {{ $summary['qc_rejected'] }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <div class="filter-title">🔍 Filter Data</div>
        <form method="GET" action="{{ route('reports.gr.recap') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label" style="font-size:.78rem;font-weight:600;">Dari</label><input type="date" name="start_date" class="form-control" value="{{ $start }}"></div>
                <div class="col-md-2"><label class="form-label" style="font-size:.78rem;font-weight:600;">Sampai</label><input type="date" name="end_date" class="form-control" value="{{ $end }}"></div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">— Semua Supplier —</option>
                        @foreach($allSuppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>{{ $s->supplier_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;">Gudang</label>
                    <select name="warehouse_id" class="form-select">
                        <option value="">— Semua Gudang —</option>
                        @foreach($allWarehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse_id')==$w->id?'selected':'' }}>{{ $w->nama_warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;">Status GR</label>
                    <select name="status" class="form-select">
                        <option value="">— Semua —</option>
                        <option value="RECEIVED" {{ request('status')=='RECEIVED'?'selected':'' }}>Received</option>
                        <option value="PARTIAL"  {{ request('status')=='PARTIAL' ?'selected':'' }}>Partial</option>
                        <option value="DRAFT"    {{ request('status')=='DRAFT'   ?'selected':'' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label" style="font-size:.78rem;font-weight:600;">Status QC</label>
                    <select name="qc_status" class="form-select">
                        <option value="">— Semua —</option>
                        <option value="PASSED"           {{ request('qc_status')=='PASSED'           ?'selected':'' }}>Passed</option>
                        <option value="PARTIAL_REJECTED" {{ request('qc_status')=='PARTIAL_REJECTED' ?'selected':'' }}>Partial Reject</option>
                        <option value="REJECTED"         {{ request('qc_status')=='REJECTED'         ?'selected':'' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn-filter">Tampilkan</button>
                    <a href="{{ route('reports.gr.recap') }}" class="btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Layout --}}
    <div class="main-layout">

        {{-- Table --}}
        <div class="table-card">
            <div class="tc-header">
                <span class="tc-title">Daftar Goods Receipt</span>
                <span class="tc-count">{{ $receipts->total() }} GR · {{ $start }} s/d {{ $end }}</span>
            </div>
            <div class="table-responsive">
                <table class="rtable">
                    <thead>
                        <tr>
                            <th></th>
                            <th>No. GR</th>
                            <th>Tgl Terima</th>
                            <th>No. PO</th>
                            <th>Supplier</th>
                            <th>Gudang</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">QC</th>
                            <th class="text-right">Nilai</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $gr)
                        @php $items = $grItems[$gr->id] ?? collect(); @endphp
                        <tr>
                            <td style="width:36px;">
                                <button class="expand-btn" onclick="toggleDetail({{ $gr->id }},this)">
                                    <i class="bi bi-chevron-right" id="icon-{{ $gr->id }}"></i>
                                </button>
                            </td>
                            <td><strong style="color:var(--warn);">{{ $gr->gr_number }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($gr->receipt_date)->format('d M Y') }}</td>
                            <td style="font-size:.78rem;">{{ $gr->po_number }}</td>
                            <td><strong>{{ $gr->supplier_name }}</strong>@if($gr->supplier_do_number)<br><span style="font-size:.72rem;color:var(--muted);">DO: {{ $gr->supplier_do_number }}</span>@endif</td>
                            <td style="font-size:.82rem;">{{ $gr->nama_warehouse ?? '-' }}</td>
                            <td class="text-center"><span class="badge-s s-{{ $gr->status }}">{{ $gr->status }}</span></td>
                            <td class="text-center"><span class="badge-s qc-{{ $gr->qc_status ?? 'PENDING' }}">{{ $gr->qc_status ?? 'PENDING' }}</span></td>
                            <td class="text-right"><strong>Rp {{ number_format($gr->total_amount,0,',','.') }}</strong></td>
                            <td>
                                <a href="{{ route('goods.receipt.print', $gr->id) }}" target="_blank" style="color:var(--muted);font-size:.8rem;"><i class="bi bi-printer"></i></a>
                            </td>
                        </tr>
                        <tr class="detail-row" id="detail-{{ $gr->id }}">
                            <td colspan="10" style="padding:0 14px 14px;">
                                <div class="detail-inner">
                                    <div style="font-size:.74rem;font-weight:700;color:var(--warn);text-transform:uppercase;margin-bottom:8px;">Detail Item — {{ $gr->gr_number }}</div>
                                    <table class="detail-table">
                                        <thead><tr><th>#</th><th>Produk</th><th class="text-center">Satuan</th><th class="text-center">Qty PO</th><th class="text-center">Qty Diterima</th><th class="text-center">Qty Ditolak</th><th class="text-right">Harga</th><th class="text-right">Subtotal</th></tr></thead>
                                        <tbody>
                                            @foreach($items as $j => $item)
                                            <tr>
                                                <td>{{ $j+1 }}</td>
                                                <td><strong>{{ $item->nama_bahan }}</strong><br><span style="font-size:.7rem;color:var(--muted);">{{ $item->product_code }}</span></td>
                                                <td class="text-center">{{ $item->nama_unit??'-' }}</td>
                                                <td class="text-center">{{ number_format($item->qty_ordered??0,2,',','.') }}</td>
                                                <td class="text-center"><strong>{{ number_format($item->qty_received??0,2,',','.') }}</strong></td>
                                                <td class="text-center" style="{{ ($item->qty_rejected??0)>0?'color:#c0392b;font-weight:700':'' }}">{{ number_format($item->qty_rejected??0,2,',','.') }}</td>
                                                <td class="text-right">Rp {{ number_format($item->price??0,0,',','.') }}</td>
                                                <td class="text-right">Rp {{ number_format($item->subtotal??0,0,',','.') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="10"><div class="no-data"><i class="bi bi-inbox"></i>Tidak ada data GR.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($receipts->hasPages())
            <div style="padding:14px;">{{ $receipts->links() }}</div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div>
            <div class="sidebar-card">
                <div class="sidebar-title">🏆 Top 5 Supplier</div>
                @php $maxVal = $topSuppliers->first()->total ?? 1; @endphp
                @foreach($topSuppliers as $k => $sup)
                <div class="top-item">
                    <span class="top-rank">{{ $k+1 }}</span>
                    <div style="flex:1;margin:0 8px;">
                        <div class="top-name" style="margin:0;">{{ $sup->supplier_name }}</div>
                        <div class="progress-mini"><div class="progress-mini-bar" style="width:{{ ($sup->total/$maxVal)*100 }}%"></div></div>
                    </div>
                    <div class="top-val">
                        Rp {{ number_format($sup->total/1000000,1) }}jt<br>
                        <span style="font-size:.68rem;color:var(--muted);">{{ $sup->jumlah }} GR</span>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="sidebar-card">
                <div class="sidebar-title">📊 Ringkasan QC</div>
                @php
                    $totalQc = $summary['qc_passed'] + $summary['qc_rejected'];
                    $pctPass = $totalQc > 0 ? round($summary['qc_passed']/$totalQc*100) : 0;
                @endphp
                <div style="text-align:center;padding:12px 0;">
                    <div style="font-size:2.5rem;font-weight:800;color:var(--accent);">{{ $pctPass }}%</div>
                    <div style="font-size:.78rem;color:var(--muted);">QC Pass Rate</div>
                </div>
                <div class="progress-mini" style="height:8px;margin-bottom:12px;">
                    <div class="progress-mini-bar" style="height:8px;background:var(--accent);width:{{ $pctPass }}%"></div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.8rem;">
                    <span style="color:var(--accent);font-weight:700;">✓ Lulus: {{ $summary['qc_passed'] }}</span>
                    <span style="color:var(--danger);font-weight:700;">✗ Tolak: {{ $summary['qc_rejected'] }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
</div>
</main>

<script>
function toggleDetail(id,btn){
    const row=document.getElementById('detail-'+id);
    const icon=document.getElementById('icon-'+id);
    const open=row.classList.toggle('open');
    icon.className=open?'bi bi-chevron-down':'bi bi-chevron-right';
}
</script>

@include('Temp.Investor.footer')
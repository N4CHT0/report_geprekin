{{-- resources/views/Purchasing/TransferRequest/index.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root { --primary:#696cff; --accent:#71dd37; --warn:#ffab00; --danger:#ff3e1d; --bg:#f5f5f9; --card:#fff; --border:#e0e0f0; --shadow:0 2px 8px rgba(67,89,113,.10); --radius:12px; }
body { background:var(--bg); }

.page-header { padding:24px 0 16px; display:flex; justify-content:space-between; align-items:center; }
.page-title  { font-size:1.4rem; font-weight:800; color:#233446; margin:0; }
.page-sub    { font-size:13px; color:#697a8d; margin-top:2px; }

.summary-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
.s-card { background:var(--card); border-radius:var(--radius); border:0.5px solid var(--border); padding:14px 18px; border-left:4px solid transparent; }
.s-card.pending  { border-color:var(--warn); }
.s-card.approved { border-color:var(--accent); }
.s-card.rejected { border-color:var(--danger); }
.s-card.transferred { border-color:var(--primary); }
.s-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#697a8d; margin-bottom:4px; }
.s-value { font-size:22px; font-weight:800; color:#233446; }

.filter-card { background:var(--card); border-radius:var(--radius); border:0.5px solid var(--border); padding:16px 20px; margin-bottom:16px; }
.form-control,.form-select { border-radius:8px; border:1px solid var(--border); height:38px; font-size:.85rem; }
.form-control:focus,.form-select:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(105,108,255,.12); outline:none; }

.table-card { background:var(--card); border-radius:var(--radius); border:0.5px solid var(--border); overflow:hidden; box-shadow:var(--shadow); }
.tc-header { padding:14px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
.tc-title  { font-size:14px; font-weight:700; color:#233446; margin:0; }

table.rtable { width:100%; border-collapse:collapse; font-size:13px; }
table.rtable thead th { background:#f5f5f9; padding:10px 16px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#566a7f; border-bottom:1px solid var(--border); white-space:nowrap; }
table.rtable tbody td { padding:11px 16px; border-bottom:0.5px solid #f0f0f8; color:#435971; vertical-align:middle; }
table.rtable tbody tr:hover { background:rgba(105,108,255,.025); }

.badge-status { display:inline-block; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; }
.s-PENDING     { background:#faeeda; color:#633806; }
.s-APPROVED    { background:#eaf3de; color:#27500a; }
.s-REJECTED    { background:#fcebeb; color:#a32d2d; }
.s-TRANSFERRED { background:#e6f1fb; color:#0c447c; }

.btn-primary { background:var(--primary); color:#fff; border:none; padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; transition:.15s; }
.btn-primary:hover { background:#5153e0; color:#fff; }
.btn-filter  { background:var(--primary); color:#fff; border:none; padding:7px 18px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; }
.btn-reset   { background:transparent; border:1px solid var(--border); padding:7px 14px; border-radius:8px; font-size:13px; color:#697a8d; text-decoration:none; }
.btn-detail  { background:#e6f1fb; color:#0c447c; border:none; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; }

.arrow-icon { color:var(--primary); font-size:14px; }
.no-data    { text-align:center; padding:48px; color:#a0acb8; }
.no-data i  { font-size:2.5rem; display:block; margin-bottom:8px; opacity:.3; }
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-4">

    {{-- Alert --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h4 class="page-title"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Transfer Request Antar DC</h4>
            <div class="page-sub">Permintaan pengiriman bahan antar Distribution Center</div>
        </div>
        <a href="{{ route('transfer-request.create') }}" class="btn-primary">
            <i class="bi bi-plus-lg"></i> Buat Request Baru
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="summary-grid">
        <div class="s-card pending">
            <div class="s-label">Pending</div>
            <div class="s-value" style="color:#633806;">{{ $summary['pending'] }}</div>
        </div>
        <div class="s-card approved">
            <div class="s-label">Approved</div>
            <div class="s-value" style="color:#27500a;">{{ $summary['approved'] }}</div>
        </div>
        <div class="s-card rejected">
            <div class="s-label">Rejected</div>
            <div class="s-value" style="color:#a32d2d;">{{ $summary['rejected'] }}</div>
        </div>
        <div class="s-card transferred">
            <div class="s-label">Transferred</div>
            <div class="s-value" style="color:#0c447c;">{{ $summary['transferred'] }}</div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('transfer-request.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label" style="font-size:11px;font-weight:700;">Status</label>
                    <select name="status" class="form-select">
                        <option value="">— Semua —</option>
                        <option value="PENDING"     {{ request('status')=='PENDING'     ?'selected':'' }}>Pending</option>
                        <option value="APPROVED"    {{ request('status')=='APPROVED'    ?'selected':'' }}>Approved</option>
                        <option value="REJECTED"    {{ request('status')=='REJECTED'    ?'selected':'' }}>Rejected</option>
                        <option value="TRANSFERRED" {{ request('status')=='TRANSFERRED' ?'selected':'' }}>Transferred</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:11px;font-weight:700;">Dari DC</label>
                    <select name="from_warehouse_id" class="form-select">
                        <option value="">— Semua —</option>
                        @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('from_warehouse_id')==$w->id?'selected':'' }}>{{ $w->nama_warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:11px;font-weight:700;">Ke DC</label>
                    <select name="to_warehouse_id" class="form-select">
                        <option value="">— Semua —</option>
                        @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('to_warehouse_id')==$w->id?'selected':'' }}>{{ $w->nama_warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:11px;font-weight:700;">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:11px;font-weight:700;">Sampai</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn-filter">Filter</button>
                    <a href="{{ route('transfer-request.index') }}" class="btn-reset">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="table-card">
        <div class="tc-header">
            <span class="tc-title">Daftar Transfer Request</span>
            <span style="font-size:12px;color:#697a8d;">{{ $requests->total() }} request ditemukan</span>
        </div>
        <div class="table-responsive">
            <table class="rtable">
                <thead>
                    <tr>
                        <th>No. Request</th>
                        <th>Tgl Request</th>
                        <th>Dari DC</th>
                        <th class="text-center">→</th>
                        <th>Ke DC</th>
                        <th class="text-center">Item</th>
                        <th>Dibutuhkan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                    <tr>
                        <td>
                            <strong style="color:var(--primary);">{{ $r->request_number }}</strong>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($r->request_date)->format('d M Y') }}</td>
                        <td><strong>{{ $r->from_warehouse }}</strong></td>
                        <td class="text-center"><i class="bi bi-arrow-right arrow-icon"></i></td>
                        <td><strong>{{ $r->to_warehouse }}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-light border text-secondary">{{ $r->item_count }} item</span>
                        </td>
                        <td>
                            @if($r->needed_date)
                                <span style="font-size:12px;">{{ \Carbon\Carbon::parse($r->needed_date)->format('d M Y') }}</span>
                                @if(\Carbon\Carbon::parse($r->needed_date)->isPast() && $r->status === 'PENDING')
                                <br><span style="font-size:10px;color:#a32d2d;font-weight:700;">⚠ Lewat tenggat</span>
                                @endif
                            @else
                                <span style="color:#a0acb8;">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge-status s-{{ $r->status }}">{{ $r->status }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('transfer-request.show', $r->id) }}" class="btn-detail">
                                <i class="bi bi-eye me-1"></i>
                                {{ $r->status === 'PENDING' ? 'Review' : 'Detail' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="no-data">
                                <i class="bi bi-inbox"></i>
                                Belum ada transfer request. Klik "Buat Request Baru" untuk memulai.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
        <div style="padding:14px 20px;">{{ $requests->links() }}</div>
        @endif
    </div>

</div>
</div>
</main>

@include('Temp.Investor.footer')
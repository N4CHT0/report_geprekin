{{-- resources/views/Purchasing/dashboardSCM.blade.php --}}
@include('Temp.Investor.header')

<style>
    :root {
        --primary: #696cff;
        --primary-hover: #5f61e6;
        --light-bg: #f5f5f9;
    }

    body { background-color: var(--light-bg); }

    .scm-card {
        background: #fff;
        border-radius: 12px;
        border: 0.5px solid #e0e0f0;
        padding: 16px;
    }

    .metric-card {
        background: #f8f8ff;
        border-radius: 10px;
        padding: 14px 16px;
        border: 0.5px solid #e8e8f0;
    }

    .metric-label {
        font-size: 12px;
        color: #697a8d;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .metric-value {
        font-size: 22px;
        font-weight: 600;
        color: #233446;
    }

    .metric-sub {
        font-size: 11px;
        margin-top: 3px;
    }

    .metric-sub.up   { color: #27500a; }
    .metric-sub.down { color: #a32d2d; }
    .metric-sub.warn { color: #633806; }

    .section-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: #697a8d;
        margin-bottom: 12px;
    }

    .card-title-sm { font-size: 13px; font-weight: 600; color: #233446; margin-bottom: 3px; }
    .card-sub-sm   { font-size: 11px; color: #697a8d; margin-bottom: 14px; }

    .badge-scm {
        display: inline-block;
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 600;
    }

    .badge-green  { background: #eaf3de; color: #27500a; }
    .badge-amber  { background: #faeeda; color: #633806; }
    .badge-red    { background: #fcebeb; color: #a32d2d; }
    .badge-blue   { background: #e6f1fb; color: #0c447c; }
    .badge-purple { background: #eeedfe; color: #3c3489; }

    .progress-bar-wrap { height: 6px; background: #eeeef5; border-radius: 3px; margin-top: 6px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 3px; }

    .item-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 0;
        border-bottom: 0.5px solid #f0f0f8;
        font-size: 13px;
    }

    .item-row:last-child { border-bottom: none; }
    .item-name { color: #233446; font-weight: 500; }
    .item-meta { color: #697a8d; font-size: 11px; margin-top: 1px; }

    .alert-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 0.5px solid #f0f0f8;
        font-size: 13px;
    }

    .alert-row:last-child { border-bottom: none; }

    .alert-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
    }

    .tab-btn {
        font-size: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        border: 0.5px solid #d9dee3;
        background: transparent;
        color: #697a8d;
        cursor: pointer;
        transition: all .15s;
    }

    .tab-btn.active {
        background: #eeedfe;
        color: #3c3489;
        border-color: #afa9ec;
    }

    .flow-box {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .chart-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 10px;
        font-size: 12px;
        color: #697a8d;
    }

    .chart-legend span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 2px;
        flex-shrink: 0;
    }
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-4">

    {{-- ── Topbar ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-0">Dashboard SCM</h5>
            <p class="text-muted small mb-0">
                Monitoring Supply Chain Management · {{ now()->format('d M Y') }}
            </p>
        </div>
        <a href="{{ route('scm.pengiriman.index') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-truck me-1"></i> Kelola Pengiriman
        </a>
    </div>

    {{-- ── SECTION 1: Metric Cards ── --}}
    <p class="section-label">Ringkasan hari ini</p>
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="bi bi-truck-front" style="color:#185fa5;font-size:16px;"></i>
                    PO siap kirim
                </div>
                <div class="metric-value">{{ $kritisCount ?? 24 }}</div>
                <div class="metric-sub up">
                    <i class="bi bi-arrow-up" style="font-size:10px;"></i>
                    6 baru hari ini
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="bi bi-box-seam" style="color:#0f6e56;font-size:16px;"></i>
                    GR masuk bulan ini
                </div>
                <div class="metric-value">{{ $totalGR ?? 138 }}</div>
                <div class="metric-sub up">
                    <i class="bi bi-arrow-up" style="font-size:10px;"></i>
                    +12% vs bulan lalu
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="bi bi-exclamation-triangle" style="color:#854f0b;font-size:16px;"></i>
                    Stok kritis
                </div>
                <div class="metric-value">{{ $stokKritis ?? 7 }}</div>
                <div class="metric-sub warn">Perlu reorder segera</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="bi bi-receipt" style="color:#a32d2d;font-size:16px;"></i>
                    Invoice belum lunas
                </div>
                <div class="metric-value">Rp {{ number_format($totalOutstanding ?? 84000000, 0, ',', '.') }}</div>
                <div class="metric-sub down">{{ $jumlahOutstanding ?? 18 }} invoice outstanding</div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 2: Charts Row ── --}}
    <div class="row g-3 mb-4">
        {{-- Chart Stok Movement --}}
        <div class="col-md-8">
            <div class="scm-card h-100">
                <div class="card-title-sm">Pergerakan stok — 30 hari terakhir</div>
                <div class="card-sub-sm">Unit masuk vs keluar per minggu</div>
                <div class="chart-legend">
                    <span><span class="legend-dot" style="background:#185fa5;"></span>Masuk</span>
                    <span><span class="legend-dot" style="background:#d85a30;"></span>Keluar</span>
                </div>
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="stockChart"
                        aria-label="Grafik batang pergerakan stok masuk dan keluar per minggu"
                        role="img">
                        Stok masuk vs keluar per minggu.
                    </canvas>
                </div>
            </div>
        </div>

        {{-- Chart GD Status --}}
        <div class="col-md-4">
            <div class="scm-card h-100">
                <div class="card-title-sm">Status GD aktif</div>
                <div class="card-sub-sm">Goods Delivery per status</div>
                <div style="position:relative;width:100%;height:160px;">
                    <canvas id="gdChart"
                        aria-label="Donut chart status goods delivery"
                        role="img">
                        GD status: 38 In Transit, 52 Delivered, 14 Draft, 6 Cancelled.
                    </canvas>
                </div>
                <div class="chart-legend mt-3">
                    <span><span class="legend-dot" style="background:#185fa5;"></span>In Transit {{ $gdInTransit ?? 38 }}</span>
                    <span><span class="legend-dot" style="background:#27500a;"></span>Delivered {{ $gdDelivered ?? 52 }}</span>
                    <span><span class="legend-dot" style="background:#888780;"></span>Draft {{ $gdDraft ?? 14 }}</span>
                    <span><span class="legend-dot" style="background:#a32d2d;"></span>Cancelled {{ $gdCancelled ?? 6 }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 3: Operasional Row ── --}}
    <div class="row g-3 mb-4">

        {{-- Fast Moving --}}
        <div class="col-md-4">
            <div class="scm-card h-100">
                <div class="card-title-sm">Top 5 bahan fast moving</div>
                <div class="card-sub-sm">Keluar terbanyak 30 hari</div>
                @php
                    $fastMoving = $fastMoving ?? collect([
                        (object)['nama_bahan'=>'Ayam besar',   'total_keluar'=>1240, 'satuan'=>'kg'],
                        (object)['nama_bahan'=>'Daging sapi',  'total_keluar'=>980,  'satuan'=>'kg'],
                        (object)['nama_bahan'=>'Tepung terigu','total_keluar'=>850,  'satuan'=>'kg'],
                        (object)['nama_bahan'=>'Minyak goreng','total_keluar'=>720,  'satuan'=>'liter'],
                        (object)['nama_bahan'=>'Bawang merah', 'total_keluar'=>610,  'satuan'=>'kg'],
                    ]);
                    $maxKeluar = $fastMoving->max('total_keluar') ?: 1;
                @endphp
                @foreach($fastMoving as $fm)
                @php $pct = round($fm->total_keluar / $maxKeluar * 100); @endphp
                <div class="item-row" style="flex-direction:column;align-items:flex-start;">
                    <div class="d-flex justify-content-between w-100">
                        <span class="item-name">{{ $fm->nama_bahan }}</span>
                        <span style="font-size:12px;color:#697a8d;">
                            {{ number_format($fm->total_keluar) }} {{ $fm->satuan }}
                        </span>
                    </div>
                    <div class="progress-bar-wrap w-100">
                        <div class="progress-bar-fill" style="width:{{ $pct }}%;background:#185fa5;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Stok Kritis --}}
        <div class="col-md-4">
            <div class="scm-card h-100">
                <div class="card-title-sm">Stok kritis & menipis</div>
                <div class="card-sub-sm">Perlu perhatian segera</div>
                @php
                    $criticals = $criticals ?? collect([
                        (object)['nama_bahan'=>'Keju slice',  'stok'=>2.5,  'unit'=>'kg',    'stok_minimal'=>10,  'type'=>'kritis'],
                        (object)['nama_bahan'=>'Susu UHT',   'stok'=>8,    'unit'=>'liter', 'stok_minimal'=>20,  'type'=>'kritis'],
                        (object)['nama_bahan'=>'Saus tiram', 'stok'=>3,    'unit'=>'botol', 'stok_minimal'=>12,  'type'=>'kritis'],
                        (object)['nama_bahan'=>'Telur ayam', 'stok'=>18,   'unit'=>'pcs',   'stok_minimal'=>24,  'type'=>'menipis'],
                        (object)['nama_bahan'=>'Gula pasir', 'stok'=>12,   'unit'=>'kg',    'stok_minimal'=>15,  'type'=>'menipis'],
                    ]);
                @endphp
                @foreach($criticals as $c)
                @php
                    $pct   = round($c->stok / $c->stok_minimal * 100);
                    $color = $c->type === 'kritis' ? '#a32d2d' : '#854f0b';
                    $bgBadge  = $c->type === 'kritis' ? '#fcebeb' : '#faeeda';
                    $txtBadge = $c->type === 'kritis' ? '#a32d2d' : '#633806';
                @endphp
                <div class="item-row" style="flex-direction:column;align-items:flex-start;">
                    <div class="d-flex justify-content-between w-100">
                        <span class="item-name">{{ $c->nama_bahan }}</span>
                        <span style="background:{{ $bgBadge }};color:{{ $txtBadge }};font-size:11px;padding:2px 8px;border-radius:4px;font-weight:600;">
                            {{ $c->stok }} / {{ $c->stok_minimal }} {{ $c->unit }}
                        </span>
                    </div>
                    <div class="progress-bar-wrap w-100">
                        <div class="progress-bar-fill" style="width:{{ $pct }}%;background:{{ $color }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Alerts --}}
        <div class="col-md-4">
            <div class="scm-card h-100">
                <div class="card-title-sm">Alert & notifikasi</div>
                <div class="card-sub-sm">Perlu tindak lanjut</div>
                @php
                    $alerts = $alerts ?? [
                        ['icon'=>'bi-exclamation-triangle','bg'=>'#faeeda','color'=>'#854f0b','title'=>'3 GR menunggu konfirmasi QC','meta'=>'Sudah lebih dari 24 jam'],
                        ['icon'=>'bi-receipt',             'bg'=>'#fcebeb','color'=>'#a32d2d','title'=>'Invoice PI/2025/0041 jatuh tempo besok','meta'=>'Rp 12.500.000'],
                        ['icon'=>'bi-truck',               'bg'=>'#e6f1fb','color'=>'#0c447c','title'=>'SJ-DC/202506/018 masih In Transit','meta'=>'Sudah 2 hari'],
                        ['icon'=>'bi-box',                 'bg'=>'#fcebeb','color'=>'#a32d2d','title'=>'Stok Susu UHT di bawah minimum','meta'=>'Gudang Sidoarjo'],
                        ['icon'=>'bi-arrow-repeat',        'bg'=>'#eaf3de','color'=>'#27500a','title'=>'Reorder point tercapai: Keju slice','meta'=>'Saran: buat PO ke supplier'],
                    ];
                @endphp
                @foreach($alerts as $a)
                <div class="alert-row">
                    <div class="alert-icon" style="background:{{ $a['bg'] }};">
                        <i class="{{ $a['icon'] }}" style="color:{{ $a['color'] }};font-size:14px;"></i>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:500;color:#233446;">{{ $a['title'] }}</div>
                        <div style="font-size:11px;color:#697a8d;margin-top:2px;">{{ $a['meta'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ── SECTION 4: Charts + Flow + Invoice ── --}}
    <div class="row g-3 mb-4">

        {{-- Purchase vs Sales Chart --}}
        <div class="col-md-6">
            <div class="scm-card h-100">
                <div class="card-title-sm">Nilai pembelian vs penjualan</div>
                <div class="card-sub-sm">Perbandingan 6 bulan terakhir (Rp juta)</div>
                <div class="chart-legend">
                    <span><span class="legend-dot" style="background:#534ab7;"></span>Purchase</span>
                    <span><span class="legend-dot" style="background:#1d9e75;"></span>Sales</span>
                </div>
                <div style="position:relative;width:100%;height:200px;">
                    <canvas id="pvChart"
                        aria-label="Grafik garis nilai pembelian vs penjualan 6 bulan"
                        role="img">
                        Pembelian vs penjualan per bulan.
                    </canvas>
                </div>
            </div>
        </div>

        {{-- Flow + Invoice Recap --}}
        <div class="col-md-6">
            <div class="scm-card h-100">
                <div class="card-title-sm mb-2">Alur dokumen SCM — aktif hari ini</div>
                <div class="d-flex flex-wrap align-items-center gap-1 mb-4">
                    @php
                        $flowStages = $flowStages ?? [
                            ['label'=>'PO Outlet','count'=>24,'bg'=>'#eeedfe','color'=>'#3c3489'],
                            ['label'=>'SJ / GD',  'count'=>18,'bg'=>'#e6f1fb','color'=>'#0c447c'],
                            ['label'=>'GR',       'count'=>12,'bg'=>'#e1f5ee','color'=>'#085041'],
                            ['label'=>'Invoice',  'count'=>8, 'bg'=>'#faeeda','color'=>'#633806'],
                            ['label'=>'Lunas',    'count'=>5, 'bg'=>'#eaf3de','color'=>'#27500a'],
                        ];
                    @endphp
                    @foreach($flowStages as $i => $stage)
                        <span class="flow-box" style="background:{{ $stage['bg'] }};color:{{ $stage['color'] }};">
                            {{ $stage['label'] }} <strong>{{ $stage['count'] }}</strong>
                        </span>
                        @if(!$loop->last)
                            <span style="color:#697a8d;font-size:12px;">›</span>
                        @endif
                    @endforeach
                </div>

                <div class="card-title-sm mb-2">Rekapitulasi invoice bulan ini</div>
                @php
                    $invoiceRecap = $invoiceRecap ?? [
                        ['label'=>'Purchase Invoice', 'meta'=>'Bulan ini', 'nilai'=>'Rp 312 jt', 'badge'=>'42 dokumen', 'bclass'=>'badge-green'],
                        ['label'=>'Sales Invoice',    'meta'=>'Bulan ini', 'nilai'=>'Rp 487 jt', 'badge'=>'61 dokumen', 'bclass'=>'badge-blue'],
                        ['label'=>'Outstanding PI',   'meta'=>'Belum lunas','nilai'=>'Rp 47 jt', 'badge'=>'9 dokumen',  'bclass'=>'badge-red'],
                        ['label'=>'Outstanding SI',   'meta'=>'Belum lunas','nilai'=>'Rp 37 jt', 'badge'=>'9 dokumen',  'bclass'=>'badge-amber'],
                    ];
                @endphp
                @foreach($invoiceRecap as $inv)
                <div class="item-row">
                    <div>
                        <div class="item-name">{{ $inv['label'] }}</div>
                        <div class="item-meta">{{ $inv['meta'] }}</div>
                    </div>
                    <div class="text-end">
                        <div class="item-name">{{ $inv['nilai'] }}</div>
                        <span class="badge-scm {{ $inv['bclass'] }} mt-1 d-inline-block">{{ $inv['badge'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ── SECTION 5: Aktivitas Terkini ── --}}
    <div class="scm-card mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="card-title-sm">Aktivitas transaksi terkini</div>
                <div class="card-sub-sm">10 transaksi terakhir lintas modul</div>
            </div>
            <div class="d-flex gap-1" id="activityTabs">
                <button class="tab-btn active" onclick="filterActivity('all',this)">Semua</button>
                <button class="tab-btn" onclick="filterActivity('GR',this)">GR</button>
                <button class="tab-btn" onclick="filterActivity('GD',this)">GD</button>
                <button class="tab-btn" onclick="filterActivity('PI',this)">PI</button>
                <button class="tab-btn" onclick="filterActivity('SI',this)">SI</button>
            </div>
        </div>
        <div id="activityList"></div>
    </div>

</div>
</div>
</main>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
// ── Data Dummy Aktivitas (ganti dengan data dari controller) ──────────────
const activities = [
    {type:'GR',doc:'GR-20250606-0042',desc:'Penerimaan dari PT Sumber Jaya',status:'RECEIVED',time:'08:42',sc:'badge-green'},
    {type:'GD',doc:'GD20250606004',  desc:'Pengiriman ke G. Agus Salim',     status:'IN_TRANSIT',time:'09:15',sc:'badge-blue'},
    {type:'PI',doc:'PI/2025/0041',   desc:'Invoice dari CV Maju Bersama',    status:'PENDING',  time:'09:30',sc:'badge-amber'},
    {type:'GD',doc:'GD20250606003',  desc:'Pengiriman ke G. Sudirman',       status:'DELIVERED',time:'10:05',sc:'badge-green'},
    {type:'GR',doc:'GR-20250606-0041',desc:'Partial dari supplier',          status:'PARTIAL',  time:'10:22',sc:'badge-amber'},
    {type:'SI',doc:'SI/2025/0089',   desc:'Invoice outlet Surabaya',         status:'ISSUED',   time:'10:45',sc:'badge-blue'},
    {type:'PI',doc:'PI/2025/0040',   desc:'Lunas — PT Agri Nusantara',       status:'PAID',     time:'11:00',sc:'badge-green'},
    {type:'GD',doc:'GD20250606002',  desc:'Pengiriman ke G. Diponegoro',     status:'IN_TRANSIT',time:'11:20',sc:'badge-blue'},
    {type:'GR',doc:'GR-20250606-0040',desc:'Barang masuk CV Maju Bersama',   status:'RECEIVED', time:'12:10',sc:'badge-green'},
    {type:'SI',doc:'SI/2025/0088',   desc:'Invoice overdue — Outlet Malang', status:'OVERDUE',  time:'13:00',sc:'badge-red'},
];

const typeBg    = {GR:'#e1f5ee',GD:'#e6f1fb',PI:'#faeeda',SI:'#eeedfe'};
const typeColor = {GR:'#085041',GD:'#0c447c',PI:'#633806',SI:'#3c3489'};

function filterActivity(type, btn){
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    const list = type==='all' ? activities : activities.filter(a=>a.type===type);
    renderActivity(list);
}

function renderActivity(list){
    document.getElementById('activityList').innerHTML = list.map(a=>`
        <div class="item-row">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <span style="background:${typeBg[a.type]};color:${typeColor[a.type]};font-size:10px;font-weight:600;padding:2px 7px;border-radius:4px;min-width:26px;text-align:center;">${a.type}</span>
                <div>
                    <div class="item-name">${a.doc}</div>
                    <div class="item-meta">${a.desc}</div>
                </div>
            </div>
            <div class="text-end">
                <span class="badge-scm ${a.sc}">${a.status}</span>
                <div class="item-meta mt-1">${a.time}</div>
            </div>
        </div>`).join('');
}

renderActivity(activities);

// ── Chart: Stock Movement ─────────────────────────────────────────────────
new Chart(document.getElementById('stockChart'), {
    type: 'bar',
    data: {
        labels: ['Minggu 1','Minggu 2','Minggu 3','Minggu 4'],
        datasets: [
            {label:'Masuk', data:[2840,3120,2760,3450], backgroundColor:'#6390bd', borderRadius:4},
            {label:'Keluar',data:[2200,2900,2500,3100], backgroundColor:'#d48d75', borderRadius:4},
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false} },
        scales:{
            x:{ grid:{display:false}, ticks:{font:{size:11}, color:'#697a8d'} },
            y:{ grid:{color:'rgba(0,0,0,0.04)'}, ticks:{font:{size:11}, color:'#697a8d', callback:v=>v.toLocaleString('id')} }
        }
    }
});

// ── Chart: GD Status Donut ────────────────────────────────────────────────
new Chart(document.getElementById('gdChart'), {
    type: 'doughnut',
    data: {
        labels:['In Transit','Delivered','Draft','Cancelled'],
        datasets:[{ data:[38,52,14,6], backgroundColor:['#709ecc','#85b364','#888780','#d88686'], borderWidth:0, hoverOffset:4 }]
    },
    options: {
        responsive:true, maintainAspectRatio:false, cutout:'68%',
        plugins:{ legend:{display:false} }
    }
});

// ── Chart: Purchase vs Sales Line ─────────────────────────────────────────
new Chart(document.getElementById('pvChart'), {
    type: 'line',
    data: {
        labels:['Jan','Feb','Mar','Apr','Mei','Jun'],
        datasets:[
            {
                label:'Purchase',
                data:[280,310,295,340,360,312],
                borderColor:'#534ab7', backgroundColor:'rgba(83,74,183,0.08)',
                tension:.35, pointRadius:4, pointBackgroundColor:'#534ab7', fill:true,
                borderDash:[]
            },
            {
                label:'Sales',
                data:[350,390,370,420,460,487],
                borderColor:'#1d9e75', backgroundColor:'rgba(29,158,117,0.07)',
                tension:.35, pointRadius:4, pointBackgroundColor:'#1d9e75', fill:true,
                borderDash:[5,3]
            },
        ]
    },
    options: {
        responsive:true, maintainAspectRatio:false,
        plugins:{ legend:{display:false} },
        scales:{
            x:{ grid:{display:false}, ticks:{font:{size:11}, color:'#697a8d'} },
            y:{ grid:{color:'rgba(0,0,0,0.04)'}, ticks:{font:{size:11}, color:'#697a8d', callback:v=>v+' jt'} }
        }
    }
});
</script>
@endpush

@include('Temp.Investor.footer')
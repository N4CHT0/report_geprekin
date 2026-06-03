@include('Temp.DC.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --bg: #f3f4f6;
            --card: #fff;
            --text: #111827;
            --muted: #6b7280;
            --border: #d1d5db;
            --shadow: 0 10px 24px rgba(0, 0, 0, .07);
            --radius: 14px;

            --primary: #111827;
            --accent: #0f766e;
            --warn: #b45309;
            --danger: #b91c1c;
            --soft: #f9fafb;
        }

        body {
            background: var(--bg);
            color: var(--text);
        }

        .wrap {
            max-width: 1600px;
        }

        /* TOP APP BAR */
        .appbar {
            background: linear-gradient(180deg, #ffffff 0%, #fbfbfb 100%);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 12px 14px;
        }

        .appbar h1 {
            margin: 0;
            font-weight: 900;
            letter-spacing: .2px;
            font-size: 1.1rem;
        }

        .appbar .sub {
            color: var(--muted);
            font-size: .88rem;
            margin-top: 4px;
        }

        /* HORIZONTAL CONTEXT HEADER */
        .ctx {
            margin-top: 12px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            position: static;
            top: 12px;
            z-index: 50;
        }

        .ctx-head {
            padding: 10px 12px;
            background: var(--soft);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .ctx-title {
            font-weight: 900;
        }

        .hint {
            color: var(--muted);
            font-size: .86rem;
        }

        .badge-wh {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: #fff;
            font-weight: 800;
            color: var(--muted);
            font-size: .85rem;
            white-space: nowrap;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 99px;
            background: #9ca3af;
            display: inline-block;
        }

        .dot.ok {
            background: #16a34a;
        }

        .dot.bad {
            background: #dc2626;
        }

        .dot.loading {
            background: #f59e0b;
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .btn {
            border-radius: 10px;
            font-weight: 900;
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            filter: brightness(.95);
        }

        .btn-accent {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .btn-accent:hover {
            filter: brightness(.95);
            color: #fff;
        }

        .form-label {
            font-weight: 900;
            font-size: .88rem;
            margin-bottom: .25rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border-color: var(--border);
            height: 42px;
            font-weight: 800;
        }

        /* Select2 height match */
        .select2-container .select2-selection--single {
            height: 42px;
            border-radius: 10px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 10px;
            background: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            padding-left: 0;
            color: var(--text);
            font-weight: 800;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
        }

        /* CTX v2 */
        .ctx-v2 {
            overflow: hidden;
        }

        .ctx-head-v2 {
            padding: 12px 14px;
        }

        .ctx-body-v2 {
            padding: 14px;
        }

        .ctx-grid-v2 {
            display: grid;
            grid-template-columns: 1.2fr .9fr;
            gap: 14px;
            align-items: start;
        }

        .right-stack {
            display: grid;
            gap: 14px;
        }

        .box {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 12px;
        }

        .box-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .box-head h6 {
            font-size: .82rem;
            letter-spacing: .35px;
            text-transform: uppercase;
            color: #374151;
            font-weight: 900;
            margin: 0;
        }

        .help-mini {
            color: var(--muted);
            font-size: .83rem;
            font-weight: 700;
        }

        .pill-req,
        .pill-tip,
        .pill-safe {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--border);
            background: #fff;
            padding: 4px 10px;
            border-radius: 999px;
            font-weight: 900;
            font-size: .78rem;
            color: #374151;
            white-space: nowrap;
        }

        .pill-req i {
            color: #dc2626;
        }

        .pill-tip i {
            color: #0f766e;
        }

        .pill-safe i {
            color: #16a34a;
        }

        .input-group-v2 .input-group-text {
            border-radius: 10px 0 0 10px;
            border-color: var(--border);
            background: #fff;
            color: var(--muted);
        }

        .input-group-v2 .form-control {
            border-radius: 0 10px 10px 0;
        }

        .btn-lg {
            height: 48px;
            font-size: 1rem;
        }

        .scanbar {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .scanbar .form-control {
            height: 46px;
            font-size: 1rem;
        }

        .scanicon {
            width: 46px;
            height: 46px;
            border: 1px solid var(--border);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            color: var(--muted);
        }

        /* MAIN TABLE CARD */
        .maincard {
            margin-top: 12px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .main-head {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .main-head .title {
            font-weight: 900;
        }

        .main-head .sub {
            color: var(--muted);
            font-size: .86rem;
        }

        .dt-wrap {
            width: 100%;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        #tblDSC {
            width: 100%;
            min-width: 1400px;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
        }

        #tblDSC thead th {
            position: sticky;
            top: 0;
            z-index: 20;
            background: var(--soft) !important;
            border-bottom: 1px solid var(--border) !important;
            font-weight: 900;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .35px;
            white-space: nowrap;
            padding: 10px;
        }

        #tblDSC tbody td {
            white-space: nowrap;
            vertical-align: middle !important;
            padding: 8px 10px;
            font-size: .92rem;
            border-color: var(--border);
            background: #fff;
        }

        #tblDSC tbody tr:nth-child(even) td {
            background: #fcfcfd;
        }

        .num-read {
            color: var(--muted);
            font-weight: 900;
        }

        .neg {
            color: var(--danger);
            font-weight: 900;
        }

        .input-mini {
            width: 100%;
            min-width: 0;
            height: 40px;
            border-radius: 10px;
        }

        .note-mini {
            width: 100%;
            min-width: 0;
            height: 40px;
            border-radius: 10px;
        }

        th.col-no,
        td.col-no {
            width: 70px;
        }

        th.col-nama,
        td.col-nama {
            width: 320px;
        }

        #tblDSC thead th.col-no,
        #tblDSC tbody td.col-no {
            position: sticky;
            left: 0;
            z-index: 30;
            background: #fff;
        }

        #tblDSC thead th.col-nama,
        #tblDSC tbody td.col-nama {
            position: sticky;
            left: 70px;
            z-index: 30;
            background: #fff;
            box-shadow: 8px 0 0 rgba(0, 0, 0, .04);
        }

        #tblDSC thead th.col-no,
        #tblDSC thead th.col-nama {
            z-index: 40;
            background: var(--soft) !important;
        }

        /* FOOTER ACTIONS */
        .wh-footer {
            position: sticky;
            bottom: 10px;
            z-index: 30;
            margin: 12px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, .12);
            padding: 10px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .wh-footer .left {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .wh-footer .right {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        #warnEmpty {
            display: none;
            color: var(--warn);
            font-weight: 900;
        }

        @media (max-width: 1200px) {
            .ctx-grid-v2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width:575.98px) {
            .wh-footer {
                position: fixed;
                left: 12px;
                right: 12px;
                bottom: 12px;
                margin: 0;
            }

            main {
                padding-bottom: 120px;
            }

            .wh-footer .right .btn {
                flex: 1 1 auto;
            }
        }

        /* MOBILE CARDS */
        .card-list {
            display: grid;
            gap: 10px;
        }

        .bcard {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            padding: 12px;
        }

        .bcard .name {
            font-weight: 900;
            margin: 0;
        }

        .bcard .meta {
            color: var(--muted);
            font-size: .88rem;
            font-weight: 800;
        }

        .summary {
            border: 1px dashed var(--border);
            border-radius: 14px;
            padding: 10px;
            background: #fafafa;
            margin-top: 10px;
        }

        .kv {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            font-size: .9rem;
            margin: 4px 0;
        }

        .kv b {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .grid2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
        }

        .grid3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
        }

        @media (max-width: 575.98px) {

            .grid2,
            .grid3 {
                grid-template-columns: 1fr;
            }
        }
    </style>


<body>
    <main class="app-main">
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/stock-control">Dashboard</a></li>
                <li class="breadcrumb-item active">Monitoring DC {{ $warehouse->nama_warehouse }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 p-4 h-100">
                    <h3 class="fw-bold">{{ $warehouse->nama_warehouse }}</h3>
                    <p class="text-muted"><i class="bi bi-geo-alt"></i>
                        {{ $warehouse->lokasi ?? 'Lokasi tidak tersedia' }}</p>
                    <hr>
                    <h5>Informasi Lainnya</h5>
                    <p>Status: <span class="badge bg-success">Aktif</span></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-3 bg-light h-100">
                    <h6 class="fw-bold">Aksi Cepat</h6>
                    <button class="btn btn-primary w-100 mb-2">Edit Data DC</button>
                    <!--<button class="btn btn-outline-danger w-100">Hapus DC</button>-->
                </div>
            </div>

            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-3">Stok Bahan</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="stokTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Nama Bahan</th>
                                        <th class="text-center">Stok Aktual</th>
                                        <th class="text-center">Rata-rata kebutuhan</th>
                                        <th class="text-center">Safety Stock</th>
                                        <th class="text-center">Lead Time</th>
                                        <th class="text-center">ROP</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $p)
                                        @php
                                            $aktual = $stokAktual[$p->id] ?? 0;
                                            $rataRata = $p->total_keluar_7_hari / 7;

                                            // Safety Stock = 20% dari Rata-rata
                                            $safetyStock = $rataRata * 0.2;

                                            // ROP = (Rata-rata * Lead Time) + Safety Stock
                                            $saranRop = ($rataRata * ($p->lead_time ?? 1)) + $safetyStock;
                                        @endphp
                                        <tr id="row-{{ $p->id }}">
                                            <form action="{{ route('update.rop.dc') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="bahan_id" value="{{ $p->id }}">

                                                <td>{{ $p->nama_bahan }}</td>
                                                <td class="text-center font-weight-bold">{{ number_format($aktual) }}</td>
                                                <td class="text-center">
                                                    {{ number_format($rataRata, 1) }}
                                                    <input type="hidden" class="avg-usage" value="{{ $rataRata }}">
                                                </td>

                                                <td class="text-center">
                                                    <span class="display-safety">{{ number_format($safetyStock, 1) }}</span>
                                                    <input type="hidden" name="safety_stock" class="input-safety-val"
                                                        value="{{ $safetyStock }}">
                                                </td>

                                                <td>
                                                    <input type="number" name="lead_time"
                                                        class="form-control form-control-sm input-lead" form="form-{{ $p->id }}"
                                                        value="{{ $p->lead_time ?? 1 }}">
                                                </td>

                                                <td class="text-center fw-bold text-primary">
                                                    <span class="display-rop">{{ ceil($saranRop) }}</span>
                                                    <input type="hidden" name="rop_level" class="input-rop-val"
                                                        value="{{ ceil($saranRop) }}">
                                                </td>

                                                <td class="text-center">
                                                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                                </td>
                                            </form>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-success mb-3">Outlet Termapping</h6>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="mapTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Outlet</th>
                                        <th class="text-start">Alamat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($outlets as $outlet)
                                        <tr>
                                            <td class="align-middle">{{ $outlet->nama_outlet }}</td>
                                            <td class="text-start align-middle">
                                                <span class="text-muted small">{{ $outlet->alamat }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted p-3">
                                                Belum ada outlet yang termapping ke DC ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.input-lead').forEach(input => {
        input.addEventListener('input', function () {
            let row = this.closest('tr');
            let avg = parseFloat(row.querySelector('.avg-usage').value) || 0;
            let safety = parseFloat(row.querySelector('.input-safety-val').value) || 0;
            let lead = parseFloat(this.value) || 0;

            // Hitung ROP
            let hasilROP = Math.ceil((avg * lead) + safety);

            // Update Tampilan
            row.querySelector('.display-rop').innerText = hasilROP;
            // Update Input Hidden di dalam form
            row.querySelector('.input-rop-val').value = hasilROP;
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#mapTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#stokTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });
</script>
@include('Temp.DC.footer')

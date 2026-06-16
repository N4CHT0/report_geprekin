{{--
    resources/views/Purchasing/cetakRekap.blade.php  (Packing List)
    Controller harus kirim:
    - $sj          → object tbl_surat_jalan
    - $poDetails   → collection rekap barang
                     kolom: nama_bahan, total_qty, satuan, berat_per_unit
    - $totalTonase → float (kg)
    - $pos         → collection tbl_po yang linked
    - $dc          → object warehouse/DC pengirim (TAMBAHAN BARU)
                     kolom: nama_warehouse, alamat, telepon (opsional)
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Packing List — {{ $sj->no_sj }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            font-size: 11.5px;
            color: #1a1a2e;
            background: #f4f4f8;
            padding-top: 64px;
        }

        /* ── Toolbar ── */
        .toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            background: #1e1e2e;
            padding: 0 20px;
            height: 54px;
            display: flex; align-items: center;
            box-shadow: 0 2px 16px rgba(0,0,0,0.35);
        }
        .toolbar-inner { display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 12px; }
        .toolbar-brand { display: flex; align-items: center; gap: 10px; }
        .toolbar-brand .dot { width: 8px; height: 8px; border-radius: 50%; background: #696cff; }
        .toolbar-title { color: #cdd6f4; font-size: 13px; font-weight: 700; letter-spacing: .3px; }
        .toolbar-sub   { color: #6c7086; font-size: 11px; margin-top: 1px; }
        .toolbar-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        .toggle-group { display: flex; background: #313244; border-radius: 6px; overflow: hidden; }
        .toggle-btn {
            border: none; background: transparent; color: #cdd6f4;
            padding: 6px 13px; cursor: pointer; font-size: 11.5px; font-weight: 600;
            transition: all 0.15s; font-family: inherit;
        }
        .toggle-btn.active { background: #696cff; color: #fff; }
        .toggle-btn:hover:not(.active) { background: #45475a; color: #fff; }

        .btn-print {
            background: #696cff; color: #fff; border: none;
            padding: 7px 18px; border-radius: 6px; cursor: pointer;
            font-size: 12px; font-weight: 700; font-family: inherit;
            transition: background 0.15s; display: flex; align-items: center; gap: 6px;
        }
        .btn-print:hover { background: #5153e0; }
        .btn-close-tb {
            background: #313244; color: #a6adc8; border: none;
            padding: 7px 13px; border-radius: 6px; cursor: pointer;
            font-size: 12px; font-family: inherit; transition: background 0.15s;
        }
        .btn-close-tb:hover { background: #45475a; color: #cdd6f4; }

        /* ── Paper ── */
        .paper {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* ── Top accent band ── */
        .accent-band {
            height: 5px;
            background: linear-gradient(90deg, #696cff 0%, #03c3ec 55%, #71dd37 100%);
        }

        /* ── Document body ── */
        .doc-body { padding: 22px 28px 20px; flex: 1; display: flex; flex-direction: column; }

        /* ── Header ── */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1.5px solid #eeeef5;
        }
        .company-info .co-name {
            font-size: 15px; font-weight: 800; color: #1a1a2e; letter-spacing: -.3px;
        }
        .company-info .co-sub {
            font-size: 9.5px; color: #888; margin-top: 2px; line-height: 1.5;
        }

        .doc-title-block { text-align: right; }
        .doc-title-block .doc-type {
            font-size: 18px; font-weight: 900; color: #1a1a2e;
            text-transform: uppercase; letter-spacing: .5px;
        }
        .doc-title-block .doc-no {
            font-size: 12px; font-weight: 700; color: #696cff; margin-top: 3px;
        }
        .type-badge {
            display: inline-block; margin-top: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        }
        .badge-gudang   { background: #e8edff; color: #3730a3; border: 1px solid #c7d2fe; }
        .badge-supplier { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* ── Route banner ── */
        .route-banner {
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f0f0ff 0%, #e8f8ff 100%);
            border: 1px solid #e0e0f8;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            gap: 0;
        }
        .route-node {
            flex: 1;
            text-align: center;
        }
        .route-node .rn-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: 3px; }
        .route-node .rn-name  { font-size: 12px; font-weight: 800; color: #1a1a2e; }
        .route-node .rn-sub   { font-size: 9px; color: #666; margin-top: 1px; }

        .route-arrow {
            flex: 0 0 60px;
            text-align: center;
            color: #696cff;
            font-size: 18px;
            font-weight: 300;
            position: relative;
        }
        .route-arrow::before {
            content: '';
            display: block;
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 40px; height: 1px;
            background: #696cff;
        }
        .route-arrow-icon {
            position: relative; z-index: 1;
            background: #696cff; color: #fff;
            width: 22px; height: 22px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 11px; margin: 0 auto;
        }

        /* ── Info strip ── */
        .info-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0;
            border: 1px solid #eeeef5;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 14px;
        }
        .info-cell {
            padding: 9px 13px;
            border-right: 1px solid #eeeef5;
        }
        .info-cell:last-child { border-right: none; }
        .info-cell .ic-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: .5px; color: #aaa; margin-bottom: 2px; }
        .info-cell .ic-value { font-size: 11.5px; font-weight: 700; color: #1a1a2e; }

        /* ── Outlet tags ── */
        .outlet-row {
            display: flex; align-items: flex-start; gap: 8px;
            margin-bottom: 14px; flex-wrap: wrap;
        }
        .outlet-row-label {
            font-size: 8.5px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; color: #888; padding-top: 4px; white-space: nowrap;
        }
        .outlet-tag {
            display: inline-flex; align-items: center; gap: 4px;
            background: #f0f0ff; color: #3730a3; border: 1px solid #c7d2fe;
            padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 600;
        }
        .outlet-tag::before { content: '●'; font-size: 6px; }

        /* ── Summary cards ── */
        .summary-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Diubah jadi 3 kolom karena estimasi harga dihapus */
            gap: 10px;
            margin-bottom: 16px;
        }
        .s-card {
            border-radius: 8px; padding: 10px 14px;
            border: 1px solid #eeeef5;
            background: #fafafe;
        }
        .s-card .sc-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: .5px; color: #aaa; margin-bottom: 4px; }
        .s-card .sc-value { font-size: 17px; font-weight: 800; color: #696cff; }
        .s-card .sc-unit  { font-size: 9px; color: #888; font-weight: 400; margin-left: 2px; }

        /* ── Table ── */
        table.doc-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 11px; }
        table.doc-table thead tr { background: #696cff; }
        table.doc-table thead th {
            padding: 8px 10px; color: #fff;
            font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px;
            text-align: left;
        }
        table.doc-table thead th.tc { text-align: center; }
        table.doc-table thead th.tr { text-align: right; }

        table.doc-table tbody td {
            padding: 8px 10px; border-bottom: 1px solid #f0f0f8;
            color: #1a1a2e; vertical-align: middle;
        }
        table.doc-table tbody tr:nth-child(even) { background: #fafafe; }
        table.doc-table tbody tr:last-child td   { border-bottom: none; }
        table.doc-table tbody .tc { text-align: center; }
        table.doc-table tbody .tr { text-align: right; }

        table.doc-table tfoot td {
            padding: 9px 10px; font-weight: 700;
            background: #f0f0ff; color: #1a1a2e;
            border-top: 2px solid #696cff;
        }
        table.doc-table tfoot .tr { text-align: right; }
        table.doc-table tfoot .tc { text-align: center; }

        /* ── No badge & Checklist ── */
        .row-no {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 6px;
            background: #eeeeff; color: #696cff;
            font-size: 10px; font-weight: 700;
        }
        .check-box {
            display: inline-block;
            width: 16px; height: 16px;
            border: 1.5px solid #a6adc8;
            border-radius: 3px;
            background: #fff;
            vertical-align: middle;
        }

        /* ── Signature ── */
        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: auto;
            padding-top: 20px;
            gap: 10px;
        }
        .sign-box { text-align: center; flex: 1; }
        .sign-box .sign-role { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
        .sign-box .sign-name { font-size: 11px; font-weight: 600; color: #1a1a2e; }
        .sign-space { height: 60px; border-bottom: 1px solid #1a1a2e; margin: 4px 20px 4px; }

        /* ── Footer ── */
        .doc-footer {
            padding: 8px 28px;
            border-top: 1px solid #eeeef5;
            display: flex; justify-content: space-between;
            font-size: 8.5px; color: #bbb;
        }

        /* ── Print ── */
        @media print {
            body { background: #fff; padding-top: 0; }
            .toolbar { display: none !important; }
            .paper { box-shadow: none; margin: 0; border-radius: 0; width: 100%; }
            /* Memastikan border checkbox tetap terlihat saat di print */
            .check-box { border-color: #555 !important; } 
        }
        @page { size: A4 portrait; margin: 0; }
    </style>
</head>
<body>

{{-- ── Toolbar ── --}}
<div class="toolbar no-print">
    <div class="toolbar-inner">
        <div class="toolbar-brand">
            <div class="dot"></div>
            <div>
                <div class="toolbar-title">Packing List — {{ $sj->no_sj }}</div>
                <div class="toolbar-sub">PT. Olah Kuliner Nusantara</div>
            </div>
        </div>
        <div class="toolbar-right">
            <div class="toggle-group">
                <button class="toggle-btn active" onclick="setSize('A4')" id="btnA4">A4</button>
                <button class="toggle-btn" onclick="setSize('A5')" id="btnA5">A5</button>
            </div>
            <div class="toggle-group">
                <button class="toggle-btn active" onclick="setOrient('portrait')" id="btnP">▯ Portrait</button>
                <button class="toggle-btn" onclick="setOrient('landscape')" id="btnL">▭ Landscape</button>
            </div>
            <button onclick="doPrint()" class="btn-print">🖨️ Cetak</button>
            <button onclick="window.close()" class="btn-close-tb">✕ Tutup</button>
        </div>
    </div>
</div>

<div class="paper">
    <div class="accent-band"></div>
    <div class="doc-body">

        {{-- ── Header ── --}}
        <div class="doc-header">
            <div class="company-info">
                <div class="co-name">PT. Olah Kuliner Nusantara</div>
                <div class="co-sub">
                    Perumahan Pondok Mutiara Blok V-16, Sidoarjo<br>
                    Telp: (031) xxx-xxxx
                </div>
            </div>
            <div class="doc-title-block">
                <div class="doc-type">Packing List</div>
                <div class="doc-no">{{ $sj->no_sj }}</div>
                <div>
                    @if($sj->tipe_sj === 'GUDANG')
                        <span class="type-badge badge-gudang">🏭 DC / Gudang</span>
                    @else
                        <span class="type-badge badge-supplier">🚛 Supplier Langsung</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Route Banner (DC → Outlet) ── --}}
        <div class="route-banner">
            {{-- Pengirim (DC) --}}
            <div class="route-node">
                <div class="rn-label">📦 Dikirim dari</div>
                <div class="rn-name">{{ $dc->nama_warehouse ?? ($sj->tipe_sj === 'GUDANG' ? 'Distribution Center' : 'Supplier') }}</div>
                <div class="rn-sub">{{ $dc->alamat ?? 'Sidoarjo' }}</div>
            </div>

            {{-- Arrow --}}
            <div class="route-arrow">
                <div class="route-arrow-icon">→</div>
            </div>

            {{-- Driver / Armada --}}
            <div class="route-node">
                <div class="rn-label">🚗 Driver</div>
                <div class="rn-name">{{ $sj->driver_name }}</div>
                <div class="rn-sub">{{ $sj->armada_nopol }}</div>
            </div>

            {{-- Arrow --}}
            <div class="route-arrow">
                <div class="route-arrow-icon">→</div>
            </div>

            {{-- Tujuan --}}
            <div class="route-node">
                <div class="rn-label">🏪 Tujuan</div>
                <div class="rn-name">{{ $pos->count() }} Outlet</div>
                <div class="rn-sub">{{ $pos->pluck('outlet_name')->filter()->implode(', ') ?: 'Lihat detail' }}</div>
            </div>
        </div>

        {{-- ── Info Strip ── --}}
        <div class="info-strip">
            <div class="info-cell">
                <div class="ic-label">No. Surat Jalan</div>
                <div class="ic-value">{{ $sj->no_sj }}</div>
            </div>
            <div class="info-cell">
                <div class="ic-label">Tanggal Cetak</div>
                <div class="ic-value">{{ date('d M Y') }}</div>
            </div>
            <div class="info-cell">
                <div class="ic-label">Jumlah PO</div>
                <div class="ic-value">{{ $pos->count() }} PO</div>
            </div>
            <div class="info-cell">
                <div class="ic-label">Status</div>
                <div class="ic-value">{{ $sj->status ?? 'Packing' }}</div>
            </div>
        </div>

        {{-- ── Outlet Tujuan ── --}}
        @if($pos->count() > 0)
        <div class="outlet-row">
            <span class="outlet-row-label">Outlet Tujuan :</span>
            @foreach($pos as $po)
                <span class="outlet-tag">{{ $po->outlet_name ?? $po->no_po }}</span>
            @endforeach
        </div>
        @endif

        {{-- ── Summary Cards ── --}}
        @php
            $totalQty   = $poDetails->sum('total_qty');
            $totalBerat = $poDetails->sum(fn($i) => $i->total_qty * (($i->berat_per_unit ?? 0) / 1000));
            $totalItem  = $poDetails->count();
        @endphp
        <div class="summary-row">
            <div class="s-card">
                <div class="sc-label">Jenis Barang</div>
                <div class="sc-value">{{ $totalItem }}<span class="sc-unit">item</span></div>
            </div>
            <div class="s-card">
                <div class="sc-label">Total Qty</div>
                <div class="sc-value">{{ number_format($totalQty, 0, ',', '.') }}<span class="sc-unit">unit</span></div>
            </div>
            <div class="s-card">
                <div class="sc-label">Total Berat</div>
                <div class="sc-value">{{ number_format($totalBerat, 1) }}<span class="sc-unit">Kg</span></div>
            </div>
        </div>

        {{-- ── Tabel Barang ── --}}
        <table class="doc-table">
            <thead>
                <tr>
                    <th style="width:32px;" class="tc">#</th>
                    <th>Nama Barang</th>
                    <th class="tc" style="width:90px;">Total Qty</th>
                    <th class="tc" style="width:80px;">Satuan</th>
                    <th class="tr" style="width:110px;">Est. Berat</th>
                    <th class="tc" style="width:70px;">Cek</th>
                </tr>
            </thead>
            <tbody>
                @foreach($poDetails as $item)
                @php
                    $berat = $item->total_qty * (($item->berat_per_unit ?? 0) / 1000);
                @endphp
                <tr>
                    <td class="tc"><span class="row-no">{{ $loop->iteration }}</span></td>
                    <td><strong>{{ $item->nama_bahan }}</strong></td>
                    <td class="tc">{{ number_format($item->total_qty, 2, ',', '.') }}</td>
                    <td class="tc">{{ $item->satuan }}</td>
                    <td class="tr">{{ number_format($berat, 2, ',', '.') }} kg</td>
                    <td class="tc"><div class="check-box"></div></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="font-weight:700;">TOTAL</td>
                    <td class="tc">{{ number_format($totalQty, 2, ',', '.') }}</td>
                    <td></td>
                    <td class="tr">{{ number_format($totalBerat, 2, ',', '.') }} kg</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- ── Tanda Tangan ── --}}
        <div class="signature-area">
            <div class="sign-box">
                <div class="sign-role">Disiapkan</div>
                <div class="sign-name">Gudang / DC</div>
                <div class="sign-space"></div>
                <div style="font-size:10px;color:#555;">( _________________ )</div>
            </div>
            <div class="sign-box">
                <div class="sign-role">Diperiksa</div>
                <div class="sign-name">Supervisor</div>
                <div class="sign-space"></div>
                <div style="font-size:10px;color:#555;">( _________________ )</div>
            </div>
            <div class="sign-box">
                <div class="sign-role">Diserahkan kepada</div>
                <div class="sign-name">{{ $sj->driver_name }}</div>
                <div class="sign-space"></div>
                <div style="font-size:10px;color:#555;">( {{ $sj->driver_name }} )</div>
            </div>
        </div>

    </div>{{-- doc-body --}}

    <div class="doc-footer">
        <span>Dicetak: {{ date('d M Y H:i') }}</span>
        <span>{{ $sj->no_sj }} — PT. Olah Kuliner Nusantara</span>
        <span>Dokumen ini berlaku tanpa cap basah</span>
    </div>
</div>

<script>
    let sz = 'A4', or = 'portrait';

    function setSize(s) {
        sz = s;
        ['A4','A5'].forEach(v => document.getElementById('btn'+v).classList.toggle('active', v===s));
        applyPage();
    }
    function setOrient(o) {
        or = o;
        document.getElementById('btnP').classList.toggle('active', o==='portrait');
        document.getElementById('btnL').classList.toggle('active', o==='landscape');
        applyPage();
    }
    function applyPage() {
        let el = document.getElementById('dps');
        if (!el) { el = document.createElement('style'); el.id = 'dps'; document.head.appendChild(el); }
        el.textContent = `@page { size: ${sz} ${or}; margin: 0; }`;
    }
    function doPrint() { applyPage(); window.print(); }
</script>
</body>
</html>
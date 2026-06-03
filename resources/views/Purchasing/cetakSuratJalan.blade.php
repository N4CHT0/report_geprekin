{{--
    resources/views/Purchasing/cetakSuratJalan.blade.php
    Controller harus kirim:
    - $sj           → object tbl_surat_jalan
    - $pos          → collection tbl_po yang linked ke SJ ini
    - $detailsPerPo → collection detail per po_id (keyed by po_id)
    - $dc           → object warehouse/DC pengirim (TAMBAHAN BARU)
                      kolom: nama_warehouse, alamat, telepon (opsional)
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan — {{ $sj->no_sj }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            font-size: 11.5px;
            color: #1a1a2e;
            background: #f0f4f8;
            padding-top: 64px;
        }

        /* ── Toolbar ── */
        .toolbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 999;
            background: #1e1e2e; padding: 0 20px; height: 54px;
            display: flex; align-items: center;
            box-shadow: 0 2px 16px rgba(0,0,0,0.35);
        }
        .toolbar-inner { display: flex; align-items: center; justify-content: space-between; width: 100%; gap: 12px; }
        .toolbar-brand { display: flex; align-items: center; gap: 10px; }
        .toolbar-brand .dot { width: 8px; height: 8px; border-radius: 50%; background: #71dd37; }
        .toolbar-title { color: #cdd6f4; font-size: 13px; font-weight: 700; }
        .toolbar-sub   { color: #6c7086; font-size: 11px; margin-top: 1px; }
        .toolbar-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        .toggle-group { display: flex; background: #313244; border-radius: 6px; overflow: hidden; }
        .toggle-btn {
            border: none; background: transparent; color: #cdd6f4;
            padding: 6px 13px; cursor: pointer; font-size: 11.5px; font-weight: 600;
            transition: all 0.15s; font-family: inherit;
        }
        .toggle-btn.active { background: #71dd37; color: #1a1a2e; }
        .toggle-btn:hover:not(.active) { background: #45475a; color: #fff; }

        .btn-print {
            background: #71dd37; color: #1a1a2e; border: none;
            padding: 7px 18px; border-radius: 6px; cursor: pointer;
            font-size: 12px; font-weight: 700; font-family: inherit;
            transition: background 0.15s; display: flex; align-items: center; gap: 6px;
        }
        .btn-print:hover { background: #5ecc27; }
        .btn-close-tb {
            background: #313244; color: #a6adc8; border: none;
            padding: 7px 13px; border-radius: 6px; cursor: pointer;
            font-size: 12px; font-family: inherit; transition: background 0.15s;
        }
        .btn-close-tb:hover { background: #45475a; color: #cdd6f4; }

        /* ── Paper wrapper ── */
        .paper-wrap { padding: 16px 0 32px; }

        /* ── Page (per PO) ── */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 20px;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .page-break { page-break-after: always; }

        /* ── Accent side bar ── */
        .accent-side {
            height: 5px;
            background: linear-gradient(90deg, #71dd37 0%, #03c3ec 55%, #696cff 100%);
        }

        /* ── Doc body ── */
        .doc-body { padding: 22px 28px 20px; flex: 1; display: flex; flex-direction: column; }

        /* ── Header ── */
        .doc-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 16px; padding-bottom: 14px;
            border-bottom: 1.5px solid #eeeef5;
        }
        .co-name { font-size: 15px; font-weight: 800; color: #1a1a2e; letter-spacing: -.3px; }
        .co-sub  { font-size: 9.5px; color: #888; margin-top: 2px; line-height: 1.5; }

        .doc-title-block { text-align: right; }
        .doc-type { font-size: 18px; font-weight: 900; text-transform: uppercase; letter-spacing: .5px; color: #1a1a2e; }
        .doc-no   { font-size: 12px; font-weight: 700; color: #71dd37; margin-top: 3px; }

        .type-badge {
            display: inline-block; margin-top: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        }
        .badge-gudang   { background: #edfbe9; color: #2d6a1f; border: 1px solid #b7edaa; }
        .badge-supplier { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        /* ── Route Banner ── */
        .route-banner {
            display: flex; align-items: center;
            background: linear-gradient(135deg, #f0fff4 0%, #f0f8ff 100%);
            border: 1px solid #d4f5d0;
            border-radius: 10px;
            padding: 12px 16px; margin-bottom: 14px;
        }
        .route-node { flex: 1; text-align: center; }
        .rn-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-bottom: 3px; }
        .rn-name  { font-size: 12px; font-weight: 800; color: #1a1a2e; }
        .rn-sub   { font-size: 9px; color: #666; margin-top: 1px; }

        .route-arrow { flex: 0 0 50px; text-align: center; position: relative; }
        .route-arrow::before {
            content: ''; display: block; position: absolute;
            top: 50%; left: 50%; transform: translate(-50%, -50%);
            width: 36px; height: 1px; background: #71dd37;
        }
        .route-arrow-icon {
            position: relative; z-index: 1;
            background: #71dd37; color: #1a1a2e;
            width: 22px; height: 22px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 11px; margin: 0 auto;
        }

        /* ── Info Grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0;
            border: 1px solid #eeeef5;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 14px;
        }
        .ig-cell {
            padding: 9px 13px;
            border-right: 1px solid #eeeef5;
            border-bottom: 1px solid #eeeef5;
        }
        .ig-cell:nth-child(3n) { border-right: none; }
        .ig-cell:nth-last-child(-n+3) { border-bottom: none; }
        .ig-cell.span2 { grid-column: span 2; border-right: none; }
        .ig-cell.span3 { grid-column: span 3; border-right: none; }

        .ig-label { font-size: 8.5px; text-transform: uppercase; letter-spacing: .5px; color: #aaa; margin-bottom: 2px; }
        .ig-value { font-size: 11.5px; font-weight: 700; color: #1a1a2e; }

        /* ── Table ── */
        table.doc-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11px; }
        table.doc-table thead tr { background: #71dd37; }
        table.doc-table thead th {
            padding: 8px 10px; color: #1a1a2e;
            font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .4px;
        }
        table.doc-table thead th.tc { text-align: center; }
        table.doc-table thead th.tr { text-align: right; }
        table.doc-table thead th.tl { text-align: left; }

        table.doc-table tbody td {
            padding: 8px 10px; border-bottom: 1px solid #f0f0f8; vertical-align: middle;
        }
        table.doc-table tbody tr:nth-child(even) { background: #f8fff5; }
        table.doc-table tbody tr:last-child td   { border-bottom: none; }
        table.doc-table .tc { text-align: center; }
        table.doc-table .tr { text-align: right; }

        table.doc-table tfoot td {
            padding: 9px 10px; font-weight: 700;
            background: #edfbe9;
            border-top: 2px solid #71dd37;
            color: #1a1a2e;
        }
        table.doc-table tfoot .tr { text-align: right; }

        .row-no {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 6px;
            background: #edfbe9; color: #2d6a1f;
            font-size: 10px; font-weight: 700;
        }

        /* ── Empty state ── */
        .empty-row { text-align: center; padding: 20px; color: #bbb; font-style: italic; }

        /* ── Signature ── */
        .signature-area {
            display: flex; justify-content: space-between;
            margin-top: auto; padding-top: 20px; gap: 10px;
        }
        .sign-box { text-align: center; flex: 1; }
        .sign-role { font-size: 8.5px; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
        .sign-name { font-size: 11px; font-weight: 600; color: #1a1a2e; }
        .sign-space { height: 56px; border-bottom: 1px solid #1a1a2e; margin: 4px 18px 4px; }

        /* QR placeholder */
        .qr-box {
            border: 1px dashed #ccc; width: 56px; height: 56px; margin: 4px auto;
            display: flex; align-items: center; justify-content: center;
            font-size: 8px; color: #ccc; border-radius: 4px;
        }

        /* ── Footer ── */
        .doc-footer {
            padding: 8px 28px; border-top: 1px solid #eeeef5;
            display: flex; justify-content: space-between;
            font-size: 8.5px; color: #bbb;
        }

        /* ── Page counter (untuk multi-PO) ── */
        .page-counter {
            position: absolute; bottom: 10px; right: 28px;
            font-size: 8.5px; color: #bbb;
        }

        /* ── Print ── */
        @media print {
            body { background: #fff; padding-top: 0; }
            .toolbar { display: none !important; }
            .paper-wrap { padding: 0; }
            .page { box-shadow: none; margin: 0; border-radius: 0; width: 100%; min-height: 100vh; }
            .page-break { page-break-after: always; }
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
                <div class="toolbar-title">Surat Jalan — {{ $sj->no_sj }}</div>
                <div class="toolbar-sub">{{ $pos->count() }} PO · PT. Olah Kuliner Nusantara</div>
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

<div class="paper-wrap">

@foreach($pos as $po)
@php
    $items    = $detailsPerPo[$po->id] ?? collect();
    $subtotal = $items->sum(fn($i) => $i->jumlah * ($i->harga_satuan ?? 0));
    $isLast   = $loop->last;
@endphp

<div class="page {{ !$isLast ? 'page-break' : '' }}">
    <div class="accent-side"></div>
    <div class="doc-body">

        {{-- ── Header ── --}}
        <div class="doc-header">
            <div>
                <div class="co-name">PT. Olah Kuliner Nusantara</div>
                <div class="co-sub">
                    Perumahan Pondok Mutiara Blok V-16, Sidoarjo<br>
                    Telp: (031) xxx-xxxx
                </div>
            </div>
            <div class="doc-title-block">
                <div class="doc-type">Surat Jalan</div>
                <div class="doc-no">{{ $sj->no_sj }}</div>
                @if($pos->count() > 1)
                <div style="font-size:9px;color:#888;margin-top:2px;">Halaman {{ $loop->iteration }} dari {{ $pos->count() }}</div>
                @endif
                <div>
                    @if($sj->tipe_sj === 'GUDANG')
                        <span class="type-badge badge-gudang">🏭 Internal DC</span>
                    @else
                        <span class="type-badge badge-supplier">🚛 Direct Supplier</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Route Banner ── --}}
        <div class="route-banner">
            {{-- Dari DC --}}
            <div class="route-node">
                <div class="rn-label">📦 Dikirim dari</div>
                <div class="rn-name">{{ $dc->nama_warehouse ?? ($sj->tipe_sj === 'GUDANG' ? 'Distribution Center' : 'Supplier') }}</div>
                <div class="rn-sub">{{ $dc->alamat ?? 'Sidoarjo' }}</div>
            </div>
            <div class="route-arrow">
                <div class="route-arrow-icon">→</div>
            </div>
            {{-- Driver --}}
            <div class="route-node">
                <div class="rn-label">🚗 Driver / Armada</div>
                <div class="rn-name">{{ $sj->driver_name }}</div>
                <div class="rn-sub">{{ $sj->armada_nopol }}</div>
            </div>
            <div class="route-arrow">
                <div class="route-arrow-icon">→</div>
            </div>
            {{-- Tujuan --}}
            <div class="route-node">
                <div class="rn-label">🏪 Tujuan</div>
                <div class="rn-name">{{ $po->outlet_name ?? '-' }}</div>
                <div class="rn-sub">{{ $po->alamat_outlet ?? '' }}</div>
            </div>
        </div>

        {{-- ── Info Grid ── --}}
        <div class="info-grid">
            <div class="ig-cell">
                <div class="ig-label">No. Surat Jalan</div>
                <div class="ig-value">{{ $sj->no_sj }}</div>
            </div>
            <div class="ig-cell">
                <div class="ig-label">No. PO Outlet</div>
                <div class="ig-value">{{ $po->no_po }}</div>
            </div>
            <div class="ig-cell">
                <div class="ig-label">Tanggal</div>
                <div class="ig-value">{{ date('d M Y') }}</div>
            </div>
            <div class="ig-cell">
                <div class="ig-label">Outlet Tujuan</div>
                <div class="ig-value">{{ $po->outlet_name ?? '-' }}</div>
            </div>
            <div class="ig-cell">
                <div class="ig-label">Driver</div>
                <div class="ig-value">{{ $sj->driver_name }}</div>
            </div>
            <div class="ig-cell">
                <div class="ig-label">Nopol Armada</div>
                <div class="ig-value">{{ $sj->armada_nopol }}</div>
            </div>
            @if($po->catatan)
            <div class="ig-cell span3">
                <div class="ig-label">Catatan PO</div>
                <div class="ig-value" style="font-weight:500;">{{ $po->catatan }}</div>
            </div>
            @endif
        </div>

        {{-- ── Tabel Barang ── --}}
        <table class="doc-table">
            <thead>
                <tr>
                    <th style="width:32px;" class="tc">#</th>
                    <th class="tl">Nama Barang</th>
                    <th class="tc" style="width:72px;">Qty</th>
                    <th class="tc" style="width:58px;">Satuan</th>
                    <th class="tr" style="width:110px;">Harga Satuan</th>
                    <th class="tr" style="width:120px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="tc"><span class="row-no">{{ $loop->iteration }}</span></td>
                    <td><strong>{{ $item->nama_bahan }}</strong></td>
                    <td class="tc">{{ number_format($item->jumlah, 2, ',', '.') }}</td>
                    <td class="tc">{{ $item->satuan }}</td>
                    <td class="tr">Rp {{ number_format($item->harga_satuan ?? 0, 0, ',', '.') }}</td>
                    <td class="tr"><strong>Rp {{ number_format($item->jumlah * ($item->harga_satuan ?? 0), 0, ',', '.') }}</strong></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="empty-row">
                        Tidak ada barang {{ $sj->tipe_sj }} di PO ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="font-weight:700;">TOTAL TAGIHAN ({{ $sj->tipe_sj }})</td>
                    <td class="tr">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- ── Tanda Tangan ── --}}
        <div class="signature-area">
            @if($sj->tipe_sj === 'GUDANG')
            <div class="sign-box">
                <div class="sign-role">Verifikasi DC</div>
                <div class="sign-name">{{ $dc->nama_warehouse ?? 'Gudang' }}</div>
                <div class="qr-box">QR</div>
                <div style="font-size:10px;color:#555;margin-top:4px;">( _________________ )</div>
            </div>
            @endif
            <div class="sign-box">
                <div class="sign-role">Pengirim</div>
                <div class="sign-name">{{ $sj->driver_name }}</div>
                <div class="sign-space"></div>
                <div style="font-size:10px;color:#555;">( {{ $sj->driver_name }} )</div>
            </div>
            <div class="sign-box">
                <div class="sign-role">Penerima</div>
                <div class="sign-name">{{ $po->nama_pemesan ?? $po->outlet_name ?? '-' }}</div>
                <div class="sign-space"></div>
                <div style="font-size:10px;color:#555;">( _________________ )</div>
                <div style="font-size:8.5px;color:#aaa;margin-top:3px;">Nama & Tanggal Terima</div>
            </div>
        </div>

    </div>{{-- doc-body --}}

    <div class="doc-footer">
        <span>Dicetak: {{ date('d M Y H:i') }}</span>
        <span>{{ $sj->no_sj }} / {{ $po->no_po }} — PT. Olah Kuliner Nusantara</span>
        <span>Dokumen berlaku tanpa cap basah</span>
    </div>
</div>{{-- .page --}}

@endforeach

</div>{{-- paper-wrap --}}

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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Receipt {{ $gr->gr_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: 'Arial', sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; }

        @page { size: A4; margin: 0; }
        @media print {
            html, body { width: 210mm; }
            .no-print { display: none !important; }
            .page { box-shadow: none !important; margin: 0 !important; }
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        /* Warna GR = oranye amber */
        .accent-bar { height: 6px; background: linear-gradient(90deg, #ffab00 0%, #ff3e1d 50%, #696cff 100%); }

        .body-wrap { padding: 28px 36px 24px; flex: 1; display: flex; flex-direction: column; }

        /* ── Header ── */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .company-name { font-size: 20px; font-weight: 800; color: #ffab00; letter-spacing: -0.5px; }
        .company-sub  { font-size: 10px; color: #888; margin-top: 2px; }
        .doc-title-block { text-align: right; }
        .doc-title-block h1 { font-size: 20px; font-weight: 900; color: #1a1a2e; text-transform: uppercase; letter-spacing: 1px; }
        .doc-title-block .doc-number { font-size: 13px; font-weight: 700; color: #ffab00; margin-top: 4px; }

        .status-badge {
            display: inline-block; padding: 3px 12px; border-radius: 20px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 6px;
        }
        .status-DRAFT    { background: #f5f5f5;  color: #757575; border: 1px solid #e0e0e0; }
        .status-PARTIAL  { background: #fff8e1;  color: #f57f17; border: 1px solid #ffe082; }
        .status-RECEIVED { background: #e8f5e9;  color: #2e7d32; border: 1px solid #a5d6a7; }

        /* QC Badges */
        .qc-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .qc-PASSED           { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .qc-PARTIAL_REJECTED { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
        .qc-REJECTED         { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }
        .qc-PENDING          { background: #f5f5f5; color: #757575; border: 1px solid #e0e0e0; }

        .divider { height: 1px; background: #e8e8f0; margin: 14px 0; }

        /* ── Info Grid ── */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box .box-label {
            font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
            color: #ffab00; margin-bottom: 6px; border-bottom: 2px solid #ffab00;
            padding-bottom: 3px; display: inline-block;
        }
        .info-box .box-value { font-size: 12px; font-weight: 700; color: #1a1a2e; margin-bottom: 2px; }
        .info-box .box-sub   { font-size: 10px; color: #666; line-height: 1.6; }

        /* ── Meta Row ── */
        .meta-row { display: flex; gap: 0; margin-bottom: 20px; border: 1px solid #e8e8f0; border-radius: 8px; overflow: hidden; }
        .meta-cell { flex: 1; padding: 10px 14px; border-right: 1px solid #e8e8f0; }
        .meta-cell:last-child { border-right: none; }
        .meta-cell .meta-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.6px; color: #888; margin-bottom: 3px; }
        .meta-cell .meta-value { font-size: 12px; font-weight: 700; color: #1a1a2e; }

        /* ── Items Table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .items-table thead tr { background: #ffab00; color: #fff; }
        .items-table thead th {
            padding: 9px 12px; text-align: left; font-size: 10px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .items-table thead th.text-right  { text-align: right; }
        .items-table thead th.text-center { text-align: center; }
        .items-table tbody tr { border-bottom: 1px solid #f0f0f0; }
        .items-table tbody tr:nth-child(even) { background: #fffbf0; }
        .items-table tbody td { padding: 9px 12px; font-size: 11px; color: #1a1a2e; vertical-align: middle; }
        .items-table tbody td.text-right  { text-align: right; }
        .items-table tbody td.text-center { text-align: center; }

        /* Qty comparison */
        .qty-ok      { color: #2e7d32; font-weight: 700; }
        .qty-partial { color: #f57f17; font-weight: 700; }
        .qty-reject  { color: #c62828; font-weight: 700; }
        .qty-sub     { font-size: 9px; color: #888; margin-top: 2px; }

        /* ── Total Box ── */
        .total-box { margin-left: auto; width: 240px; margin-bottom: 22px; }
        .total-row-inner {
            display: flex; justify-content: space-between; padding: 9px 14px;
            background: #ffab00; border-radius: 8px; color: #fff; font-size: 14px; font-weight: 800;
        }

        /* ── QC Box ── */
        .qc-summary-box {
            background: #fffbf0; border: 1px solid #ffe082; border-radius: 8px;
            padding: 12px 16px; margin-bottom: 18px;
        }
        .qc-summary-title { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #f57f17; margin-bottom: 8px; letter-spacing: 0.5px; }
        .qc-row { display: flex; align-items: center; gap: 10px; font-size: 11px; }
        .qc-row .qc-label { color: #666; min-width: 110px; }
        .qc-row .qc-val { font-weight: 700; color: #1a1a2e; }

        /* ── Notes ── */
        .notes-box {
            background: #fffbf0; border-left: 4px solid #ffab00; padding: 10px 14px;
            border-radius: 0 6px 6px 0; margin-bottom: 18px; font-size: 10px; color: #555;
        }
        .notes-box strong { color: #f57f17; display: block; margin-bottom: 3px; font-size: 9px; text-transform: uppercase; }

        /* ── Signature ── */
        .signature-area { display: flex; justify-content: space-between; margin-top: auto; padding-top: 16px; }
        .sig-block { text-align: center; width: 160px; }
        .sig-block .sig-title { font-size: 10px; color: #888; margin-bottom: 2px; }
        .sig-block .sig-line  { border-top: 1px solid #1a1a2e; margin-top: 48px; padding-top: 5px; font-size: 10px; color: #555; }

        /* ── Footer ── */
        .page-footer {
            border-top: 1px solid #e8e8f0; padding: 10px 36px;
            display: flex; justify-content: space-between; font-size: 9px; color: #aaa;
        }

        /* ── Buttons ── */
        .print-btn-wrap { text-align: center; padding: 20px; }
        .print-btn { background: #ffab00; color: #fff; border: none; padding: 12px 36px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; margin-right: 10px; }
        .back-btn  { background: #fffbf0; color: #f57f17; border: 1px solid #ffab00; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>

<div class="print-btn-wrap no-print">
    <button class="print-btn" onclick="window.print()">🖨️ Cetak Goods Receipt</button>
    <a href="{{ route('goods-receipt.index') }}" class="back-btn">← Kembali</a>
</div>

<div class="page">
    <div class="accent-bar"></div>

    <div class="body-wrap">

        {{-- Header --}}
        <div class="header">
            <div>
                <div class="company-name">SCM System</div>
                <div class="company-sub">Supply Chain Management</div>
            </div>
            <div class="doc-title-block">
                <h1>Goods Receipt / Penerimaan Barang</h1>
                <div class="doc-number">{{ $gr->gr_number }}</div>
                <div style="display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap; margin-top:4px;">
                    <span class="status-badge status-{{ $gr->status }}">{{ $gr->status }}</span>
                    <span class="qc-badge qc-{{ $gr->qc_status ?? 'PENDING' }}">
                        QC: {{ $gr->qc_status ?? 'PENDING' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Supplier & Referensi --}}
        <div class="info-grid">
            <div class="info-box">
                <div class="box-label">Dari Supplier</div>
                <div class="box-value">{{ $gr->supplier_name }}</div>
                @if($gr->supplier_do_number ?? null)
                <div class="box-sub">No. DO Supplier: <strong>{{ $gr->supplier_do_number }}</strong></div>
                @endif
            </div>
            <div class="info-box" style="text-align:right;">
                <div class="box-label">Referensi Dokumen</div>
                <div class="box-sub">
                    <strong>No. GR:</strong> {{ $gr->gr_number }}<br>
                    <strong>No. PO:</strong> {{ $gr->po_number }}<br>
                    @if($gr->warehouse_name ?? null)
                    <strong>Gudang Terima:</strong> {{ $gr->warehouse_name }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta Dates --}}
        <div class="meta-row">
            <div class="meta-cell">
                <div class="meta-label">Tanggal Terima</div>
                <div class="meta-value">{{ $gr->receipt_date ? \Carbon\Carbon::parse($gr->receipt_date)->format('d M Y') : '-' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Driver</div>
                <div class="meta-value">{{ $gr->driver_name ?? '-' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Status GR</div>
                <div class="meta-value">{{ $gr->status }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Total Nilai</div>
                <div class="meta-value" style="color:#ffab00;">Rp {{ number_format($gr->total_amount ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:28px;">#</th>
                    <th>Nama Barang</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-center">Qty PO</th>
                    <th class="text-center">Qty Diterima</th>
                    <th class="text-center">Qty Ditolak</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $i => $d)
                @php
                    $qtyOrdered  = $d->qty_ordered  ?? 0;
                    $qtyReceived = $d->qty_received  ?? 0;
                    $qtyRejected = $d->qty_rejected  ?? 0;
                    $selisih     = $qtyOrdered - $qtyReceived;
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $d->nama_bahan }}</strong>
                        @if($d->batch_number ?? null)
                        <div style="font-size:9px;color:#888;">Batch: {{ $d->batch_number }}</div>
                        @endif
                        @if($d->expiry_date ?? null)
                        <div style="font-size:9px;color:#888;">Exp: {{ \Carbon\Carbon::parse($d->expiry_date)->format('d M Y') }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $d->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ number_format($qtyOrdered, 2, ',', '.') }}</td>
                    <td class="text-center">
                        @if($qtyReceived >= $qtyOrdered)
                        <span class="qty-ok">{{ number_format($qtyReceived, 2, ',', '.') }}</span>
                        @else
                        <span class="qty-partial">{{ number_format($qtyReceived, 2, ',', '.') }}</span>
                        @endif
                        @if($selisih > 0)
                        <div class="qty-sub">Kurang: {{ number_format($selisih, 2, ',', '.') }}</div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($qtyRejected > 0)
                        <span class="qty-reject">{{ number_format($qtyRejected, 2, ',', '.') }}</span>
                        @else
                        <span style="color:#aaa;">-</span>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($d->price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($d->subtotal ?? ($qtyReceived * ($d->price ?? 0)), 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total --}}
        <div class="total-box">
            <div class="total-row-inner">
                <span>Total Nilai Penerimaan</span>
                <span>Rp {{ number_format($gr->total_amount ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- QC Summary --}}
        <div class="qc-summary-box">
            <div class="qc-summary-title">🔍 Hasil Quality Control</div>
            <div style="display:flex; gap:30px; flex-wrap:wrap;">
                <div class="qc-row">
                    <span class="qc-label">Status QC:</span>
                    <span class="qc-val">
                        <span class="qc-badge qc-{{ $gr->qc_status ?? 'PENDING' }}">
                            {{ $gr->qc_status ?? 'PENDING' }}
                        </span>
                    </span>
                </div>
                @if($gr->qc_notes ?? null)
                <div class="qc-row">
                    <span class="qc-label">Catatan QC:</span>
                    <span class="qc-val">{{ $gr->qc_notes }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Notes --}}
        @if($gr->notes ?? null)
        <div class="notes-box">
            <strong>Catatan Penerimaan</strong>
            {{ $gr->notes }}
        </div>
        @endif

        {{-- Signature --}}
        <div class="signature-area">
            <div class="sig-block">
                <div class="sig-title">Diserahkan oleh,<br><span style="font-size:9px;">(Pihak Supplier/Driver)</span></div>
                <div class="sig-line">( ________________________ )</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">Diperiksa QC,</div>
                <div class="sig-line">( ________________________ )</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">Diterima oleh,<br><span style="font-size:9px;">(Gudang / Warehouse)</span></div>
                <div class="sig-line">( ________________________ )</div>
            </div>
        </div>

    </div>{{-- body-wrap --}}

    <div class="page-footer">
        <span>Dicetak: {{ now()->format('d M Y H:i') }}</span>
        <span>{{ $gr->gr_number }} — SCM System</span>
        <span>Dokumen ini sah tanpa tanda tangan basah</span>
    </div>
</div>

</body>
</html>
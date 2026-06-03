<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Delivery {{ $gd->gd_number }}</title>
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

        /* Warna GD = hijau toska */
        .accent-bar { height: 6px; background: linear-gradient(90deg, #71dd37 0%, #03c3ec 60%, #696cff 100%); }

        .body-wrap { padding: 28px 36px 24px; flex: 1; display: flex; flex-direction: column; }

        /* ── Header ── */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .company-name { font-size: 20px; font-weight: 800; color: #71dd37; letter-spacing: -0.5px; }
        .company-sub  { font-size: 10px; color: #888; margin-top: 2px; }

        .doc-title-block { text-align: right; }
        .doc-title-block h1 { font-size: 20px; font-weight: 900; color: #1a1a2e; text-transform: uppercase; letter-spacing: 1px; }
        .doc-title-block .doc-number { font-size: 13px; font-weight: 700; color: #71dd37; margin-top: 4px; }

        .status-badge {
            display: inline-block; padding: 3px 12px; border-radius: 20px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 6px;
        }
        .status-DRAFT      { background: #f5f5f5; color: #757575; border: 1px solid #e0e0e0; }
        .status-IN_TRANSIT { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .status-DELIVERED  { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .status-CANCELLED  { background: #fce4ec; color: #c62828; border: 1px solid #ef9a9a; }

        .divider { height: 1px; background: #e8e8f0; margin: 14px 0; }

        /* ── Info Grid ── */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box .box-label {
            font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
            color: #71dd37; margin-bottom: 6px; border-bottom: 2px solid #71dd37;
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

        /* ── Driver Info Box ── */
        .driver-box {
            display: flex; gap: 12px; margin-bottom: 20px;
        }
        .driver-cell {
            flex: 1; background: #f0fff4; border: 1px solid #c8f5d0; border-radius: 8px;
            padding: 10px 14px;
        }
        .driver-cell .dc-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #388e3c; margin-bottom: 3px; }
        .driver-cell .dc-value { font-size: 12px; font-weight: 700; color: #1a1a2e; }

        /* ── Items Table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .items-table thead tr { background: #71dd37; color: #fff; }
        .items-table thead th {
            padding: 9px 12px; text-align: left; font-size: 10px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .items-table thead th.text-right  { text-align: right; }
        .items-table thead th.text-center { text-align: center; }
        .items-table tbody tr { border-bottom: 1px solid #f0f0f0; }
        .items-table tbody tr:nth-child(even) { background: #f5fff7; }
        .items-table tbody td { padding: 9px 12px; font-size: 11px; color: #1a1a2e; vertical-align: middle; }
        .items-table tbody td.text-right  { text-align: right; }
        .items-table tbody td.text-center { text-align: center; }

        /* Perbandingan qty ordered vs delivered */
        .qty-compare { font-size: 9px; color: #888; margin-top: 2px; }
        .qty-short   { color: #ff3e1d; font-weight: 700; }
        .qty-full    { color: #71dd37; font-weight: 700; }

        /* ── Total Row ── */
        .total-box {
            margin-left: auto; width: 240px; margin-bottom: 22px;
        }
        .total-row-inner {
            display: flex; justify-content: space-between; padding: 9px 14px;
            background: #71dd37; border-radius: 8px; color: #fff;
            font-size: 14px; font-weight: 800;
        }

        /* ── Notes ── */
        .notes-box {
            background: #f0fff4; border-left: 4px solid #71dd37;
            padding: 10px 14px; border-radius: 0 6px 6px 0; margin-bottom: 18px;
            font-size: 10px; color: #555;
        }
        .notes-box strong { color: #388e3c; display: block; margin-bottom: 3px; font-size: 9px; text-transform: uppercase; }

        /* ── Terima Barang Checklist ── */
        .checklist-box {
            border: 1px solid #c8f5d0; border-radius: 8px; padding: 12px 16px;
            margin-bottom: 20px; background: #f9fff9;
        }
        .checklist-title { font-size: 10px; font-weight: 700; text-transform: uppercase; color: #388e3c; margin-bottom: 8px; letter-spacing: 0.5px; }
        .checklist-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .checklist-item  { display: flex; align-items: center; gap: 6px; font-size: 10px; color: #555; }
        .checkbox        { width: 14px; height: 14px; border: 1px solid #aaa; border-radius: 3px; display: inline-block; flex-shrink: 0; }

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

        /* ── Print Buttons ── */
        .print-btn-wrap { text-align: center; padding: 20px; }
        .print-btn { background: #71dd37; color: #fff; border: none; padding: 12px 36px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; margin-right: 10px; }
        .back-btn  { background: #f0fff4; color: #388e3c; border: 1px solid #71dd37; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>

<div class="print-btn-wrap no-print">
    <button class="print-btn" onclick="window.print()">🖨️ Cetak Surat Jalan</button>
    <a href="{{ route('goods-delivery.index') }}" class="back-btn">← Kembali</a>
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
                <h1>Surat Jalan / Delivery Order</h1>
                <div class="doc-number">{{ $gd->gd_number }}</div>
                <div>
                    <span class="status-badge status-{{ $gd->status }}">{{ $gd->status }}</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Info Customer & Referensi --}}
        <div class="info-grid">
            <div class="info-box">
                <div class="box-label">Dikirim Kepada</div>
                <div class="box-value">{{ $gd->customer_name }}</div>
                @if($gd->delivery_address)
                <div class="box-sub">{{ $gd->delivery_address }}</div>
                @endif
            </div>
            <div class="info-box" style="text-align:right;">
                <div class="box-label">Referensi</div>
                <div class="box-sub">
                    <strong>No. GD:</strong> {{ $gd->gd_number }}<br>
                    <strong>No. SO:</strong> {{ $gd->so_number }}<br>
                    @if($gd->warehouse_name ?? null)
                    <strong>Dari Gudang:</strong> {{ $gd->warehouse_name }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta Dates --}}
        <div class="meta-row">
            <div class="meta-cell">
                <div class="meta-label">Tanggal Kirim</div>
                <div class="meta-value">{{ $gd->delivery_date ? \Carbon\Carbon::parse($gd->delivery_date)->format('d M Y') : '-' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Est. Tiba</div>
                <div class="meta-value">{{ $gd->estimated_arrival ? \Carbon\Carbon::parse($gd->estimated_arrival)->format('d M Y') : '-' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Tgl. Tiba Aktual</div>
                <div class="meta-value">{{ $gd->actual_arrival ? \Carbon\Carbon::parse($gd->actual_arrival)->format('d M Y') : '-' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Total Nilai</div>
                <div class="meta-value" style="color:#71dd37;">Rp {{ number_format($gd->total_amount ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Driver Info --}}
        @if($gd->driver_name || $gd->vehicle_plate)
        <div class="driver-box">
            <div class="driver-cell">
                <div class="dc-label">🚗 Nama Driver</div>
                <div class="dc-value">{{ $gd->driver_name ?? '-' }}</div>
            </div>
            <div class="driver-cell">
                <div class="dc-label">🚘 No. Kendaraan</div>
                <div class="dc-value">{{ $gd->vehicle_plate ?? '-' }}</div>
            </div>
            @if($gd->sj_id ?? null)
            <div class="driver-cell">
                <div class="dc-label">📄 No. Surat Jalan</div>
                <div class="dc-value">{{ $gd->sj_id }}</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Items Table --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:28px;">#</th>
                    <th>Nama Barang</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-center">Qty Dipesan</th>
                    <th class="text-center">Qty Dikirim</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $i => $d)
                @php
                    $selisih = ($d->qty_ordered ?? 0) - ($d->qty_delivered ?? 0);
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <strong>{{ $d->nama_bahan }}</strong>
                        @if($d->notes ?? null)
                        <div style="font-size:9px;color:#888;">{{ $d->notes }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $d->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ number_format($d->qty_ordered ?? 0, 2, ',', '.') }}</td>
                    <td class="text-center">
                        <strong>{{ number_format($d->qty_delivered ?? 0, 2, ',', '.') }}</strong>
                        @if($selisih > 0)
                        <div class="qty-compare qty-short">Kurang: {{ number_format($selisih, 2, ',', '.') }}</div>
                        @elseif($selisih == 0)
                        <div class="qty-compare qty-full">✓ Lengkap</div>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($d->price ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($d->subtotal ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total --}}
        <div class="total-box">
            <div class="total-row-inner">
                <span>Total Nilai Pengiriman</span>
                <span>Rp {{ number_format($gd->total_amount ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Notes --}}
        @if($gd->notes)
        <div class="notes-box">
            <strong>Catatan Pengiriman</strong>
            {{ $gd->notes }}
        </div>
        @endif

        {{-- Checklist Penerimaan --}}
        <div class="checklist-box">
            <div class="checklist-title">✅ Checklist Penerimaan Barang oleh Outlet</div>
            <div class="checklist-grid">
                <div class="checklist-item"><span class="checkbox"></span> Barang diterima dalam kondisi baik</div>
                <div class="checklist-item"><span class="checkbox"></span> Jumlah sesuai dengan surat jalan</div>
                <div class="checklist-item"><span class="checkbox"></span> Tidak ada kerusakan kemasan</div>
                <div class="checklist-item"><span class="checkbox"></span> Tanggal kadaluarsa sesuai</div>
            </div>
        </div>

        {{-- Signature --}}
        <div class="signature-area">
            <div class="sig-block">
                <div class="sig-title">Dibuat oleh,</div>
                <div class="sig-line">( ________________________ )</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">Driver / Pengirim,</div>
                <div class="sig-line">( ________________________ )</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">Diterima oleh,</div>
                <div class="sig-line">( ________________________ )<br>
                    <span style="font-size:9px;color:#aaa;">Nama & Tanggal Terima</span>
                </div>
            </div>
        </div>

    </div>{{-- body-wrap --}}

    <div class="page-footer">
        <span>Dicetak: {{ now()->format('d M Y H:i') }}</span>
        <span>{{ $gd->gd_number }} — SCM System</span>
        <span>Dokumen ini sah tanpa tanda tangan basah</span>
    </div>
</div>

</body>
</html>
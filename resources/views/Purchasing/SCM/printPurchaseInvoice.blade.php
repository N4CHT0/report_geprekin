<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Invoice {{ $pi->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #1a1a2e;
            background: #fff;
        }

        @page { size: A4; margin: 0; }
        @media print {
            html, body { width: 210mm; height: 297mm; }
            .no-print { display: none !important; }
            .page { page-break-after: always; box-shadow: none; margin: 0; }
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        /* top accent bar — warna berbeda dari SI agar mudah dibedakan */
        .accent-bar {
            height: 6px;
            background: linear-gradient(90deg, #03c3ec 0%, #696cff 60%, #ffab00 100%);
        }

        .body-wrap { padding: 28px 36px 24px; flex: 1; display: flex; flex-direction: column; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
        .company-block .company-name { font-size: 20px; font-weight: 800; color: #03c3ec; letter-spacing: -0.5px; }
        .company-block .company-sub  { font-size: 10px; color: #888; margin-top: 2px; }

        .invoice-title-block { text-align: right; }
        .invoice-title-block h1 { font-size: 22px; font-weight: 900; color: #1a1a2e; letter-spacing: 1px; text-transform: uppercase; }
        .invoice-title-block .inv-number { font-size: 13px; font-weight: 700; color: #03c3ec; margin-top: 4px; }

        .status-badge {
            display: inline-block; padding: 3px 12px; border-radius: 20px;
            font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 6px;
        }
        .status-PENDING      { background: #fff8e1; color: #f57f17; border: 1px solid #ffe082; }
        .status-APPROVED     { background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; }
        .status-PAID         { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .status-PARTIAL_PAID { background: #fff3e0; color: #e65100; border: 1px solid #ffcc80; }
        .status-CANCELLED    { background: #f5f5f5; color: #757575; border: 1px solid #e0e0e0; }

        .divider { height: 1px; background: #e8e8f0; margin: 14px 0; }

        /* Supplier + Ref grid */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 22px; }
        .info-box .box-label {
            font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px;
            color: #03c3ec; margin-bottom: 6px; border-bottom: 2px solid #03c3ec;
            padding-bottom: 3px; display: inline-block;
        }
        .info-box .box-value { font-size: 12px; font-weight: 700; color: #1a1a2e; margin-bottom: 2px; }
        .info-box .box-sub   { font-size: 10px; color: #666; line-height: 1.6; }

        /* Meta row */
        .meta-row { display: flex; gap: 0; margin-bottom: 22px; border: 1px solid #e8e8f0; border-radius: 8px; overflow: hidden; }
        .meta-cell { flex: 1; padding: 10px 14px; border-right: 1px solid #e8e8f0; }
        .meta-cell:last-child { border-right: none; }
        .meta-cell .meta-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.6px; color: #888; margin-bottom: 3px; }
        .meta-cell .meta-value { font-size: 12px; font-weight: 700; color: #1a1a2e; }

        /* Items table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .items-table thead tr { background: #03c3ec; color: #fff; }
        .items-table thead th {
            padding: 9px 12px; text-align: left; font-size: 10px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .items-table thead th.text-right  { text-align: right; }
        .items-table thead th.text-center { text-align: center; }
        .items-table tbody tr { border-bottom: 1px solid #f0f0f8; }
        .items-table tbody tr:nth-child(even) { background: #f5fdff; }
        .items-table tbody td { padding: 9px 12px; font-size: 11px; color: #1a1a2e; vertical-align: middle; }
        .items-table tbody td.text-right  { text-align: right; }
        .items-table tbody td.text-center { text-align: center; }
        .items-table tfoot td { padding: 7px 12px; font-size: 11px; border-top: 1px solid #e0e0f0; }
        .items-table tfoot .total-row { background: #03c3ec; color: #fff; font-size: 13px; font-weight: 800; }

        /* Financial Summary */
        .fin-summary { margin-left: auto; width: 280px; margin-bottom: 22px; }
        .fin-row { display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f0f0f8; font-size: 11px; }
        .fin-row .fin-label { color: #666; }
        .fin-row .fin-val   { font-weight: 600; color: #1a1a2e; }
        .fin-total {
            display: flex; justify-content: space-between; padding: 9px 14px;
            background: #03c3ec; border-radius: 8px; margin-top: 6px; color: #fff;
            font-size: 14px; font-weight: 800;
        }

        /* Payment track */
        .payment-track { display: flex; gap: 12px; margin-bottom: 22px; }
        .payment-box { flex: 1; border: 1px solid #e8e8f0; border-radius: 8px; padding: 10px 14px; text-align: center; }
        .payment-box .pb-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #888; margin-bottom: 4px; }
        .payment-box .pb-value { font-size: 14px; font-weight: 800; color: #1a1a2e; }
        .payment-box.outstanding .pb-value { color: #ff3e1d; }
        .payment-box.paid-box .pb-value    { color: #71dd37; }

        /* Supplier invoice ref box */
        .supplier-ref-box {
            background: #f5fdff; border: 1px dashed #03c3ec; border-radius: 8px;
            padding: 10px 14px; margin-bottom: 18px; font-size: 11px;
        }
        .supplier-ref-box strong { color: #03c3ec; }

        /* Notes */
        .notes-box {
            background: #f5fdff; border-left: 4px solid #03c3ec;
            padding: 10px 14px; border-radius: 0 6px 6px 0; margin-bottom: 20px;
            font-size: 10px; color: #555;
        }
        .notes-box strong { color: #03c3ec; display: block; margin-bottom: 3px; font-size: 9px; text-transform: uppercase; }

        /* Signature */
        .signature-area { display: flex; justify-content: space-between; margin-top: auto; padding-top: 18px; }
        .sig-block { text-align: center; width: 160px; }
        .sig-block .sig-line { border-top: 1px solid #1a1a2e; margin-top: 48px; padding-top: 5px; font-size: 10px; color: #555; }
        .sig-block .sig-title { font-size: 10px; color: #888; margin-bottom: 2px; }

        /* Footer */
        .page-footer {
            border-top: 1px solid #e8e8f0; padding: 10px 36px;
            display: flex; justify-content: space-between; font-size: 9px; color: #aaa;
        }

        /* Print button */
        .print-btn-wrap { text-align: center; padding: 20px; }
        .print-btn {
            background: #03c3ec; color: #fff; border: none;
            padding: 12px 36px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; margin-right: 10px;
        }
        .back-btn {
            background: #f0fdff; color: #03c3ec; border: 1px solid #03c3ec;
            padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
    </style>
</head>
<body>

<div class="print-btn-wrap no-print">
    <button class="print-btn" onclick="window.print()">🖨️ Cetak Invoice</button>
    <a href="{{ route('purchase-invoice.index') }}" class="back-btn">← Kembali</a>
</div>

<div class="page">
    <div class="accent-bar"></div>

    <div class="body-wrap">

        {{-- ── Header ── --}}
        <div class="header">
            <div class="company-block">
                <div class="company-name">SCM System</div>
                <div class="company-sub">Supply Chain Management</div>
            </div>
            <div class="invoice-title-block">
                <h1>Purchase Invoice</h1>
                <div class="inv-number">{{ $pi->invoice_number }}</div>
                <div>
                    <span class="status-badge status-{{ $pi->status }}">{{ $pi->status }}</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- ── Supplier + Ref ── --}}
        <div class="info-grid">
            <div class="info-box">
                <div class="box-label">Dari Supplier</div>
                <div class="box-value">{{ $pi->supplier_name }}</div>
                @if(isset($pi->supplier_invoice_number) && $pi->supplier_invoice_number)
                <div class="box-sub">No. Invoice Supplier: <strong>{{ $pi->supplier_invoice_number }}</strong></div>
                @endif
            </div>
            <div class="info-box" style="text-align:right;">
                <div class="box-label">Referensi Dokumen</div>
                <div class="box-sub">
                    <strong>No. GR:</strong> {{ $pi->gr_number }}<br>
                    <strong>No. PO:</strong> {{ $pi->po_number }}<br>
                    <strong>Mata Uang:</strong> {{ $pi->currency ?? 'IDR' }}
                    @if(($pi->rate ?? 1) != 1)
                        (Rate: {{ number_format($pi->rate, 2) }})
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Meta Dates ── --}}
        <div class="meta-row">
            <div class="meta-cell">
                <div class="meta-label">Tanggal Invoice</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($pi->invoice_date)->format('d M Y') }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Jatuh Tempo</div>
                <div class="meta-value" style="{{ \Carbon\Carbon::parse($pi->due_date)->isPast() && $pi->status !== 'PAID' ? 'color:#ff3e1d' : '' }}">
                    {{ \Carbon\Carbon::parse($pi->due_date)->format('d M Y') }}
                </div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Tanggal Approve</div>
                <div class="meta-value">{{ $pi->approved_at ? \Carbon\Carbon::parse($pi->approved_at)->format('d M Y') : '-' }}</div>
            </div>
            <div class="meta-cell">
                <div class="meta-label">Status Bayar</div>
                <div class="meta-value">
                    @php
                        $outstanding = $pi->total_amount - $pi->paid_amount;
                    @endphp
                    {{ $outstanding <= 0 ? 'LUNAS' : 'Sisa Rp ' . number_format($outstanding, 0, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- ── Items Table ── --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Nama Produk</th>
                    <th class="text-center">Satuan</th>
                    <th class="text-center">Qty GR</th>
                    <th class="text-center">Qty Invoice</th>
                    <th class="text-right">Harga Satuan</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $i => $d)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $d->nama_bahan }}</strong></td>
                    <td class="text-center">{{ $d->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ number_format($d->qty_received ?? $d->qty_invoiced, 2, ',', '.') }}</td>
                    <td class="text-center"><strong>{{ number_format($d->qty_invoiced, 2, ',', '.') }}</strong></td>
                    <td class="text-right">Rp {{ number_format($d->price, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- ── Financial Summary ── --}}
        <div class="fin-summary">
            <div class="fin-row">
                <span class="fin-label">Subtotal</span>
                <span class="fin-val">Rp {{ number_format($pi->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($pi->discount > 0)
            <div class="fin-row">
                <span class="fin-label">Diskon</span>
                <span class="fin-val" style="color:#ff3e1d;">- Rp {{ number_format($pi->discount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="fin-row">
                <span class="fin-label">DPP</span>
                <span class="fin-val">Rp {{ number_format($pi->dpp, 0, ',', '.') }}</span>
            </div>
            <div class="fin-row">
                <span class="fin-label">PPN (11%)</span>
                <span class="fin-val">Rp {{ number_format($pi->vat_amount, 0, ',', '.') }}</span>
            </div>
            <div class="fin-total">
                <span>Total</span>
                <span>Rp {{ number_format($pi->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- ── Payment Track ── --}}
        <div class="payment-track">
            <div class="payment-box">
                <div class="pb-label">Total Invoice</div>
                <div class="pb-value">Rp {{ number_format($pi->total_amount, 0, ',', '.') }}</div>
            </div>
            <div class="payment-box paid-box">
                <div class="pb-label">Sudah Dibayar</div>
                <div class="pb-value">Rp {{ number_format($pi->paid_amount, 0, ',', '.') }}</div>
            </div>
            <div class="payment-box outstanding">
                <div class="pb-label">Outstanding</div>
                <div class="pb-value">Rp {{ number_format($pi->total_amount - $pi->paid_amount, 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- ── Notes ── --}}
        @if(isset($pi->notes) && $pi->notes)
        <div class="notes-box">
            <strong>Catatan</strong>
            {{ $pi->notes }}
        </div>
        @endif

        {{-- ── Signature ── --}}
        <div class="signature-area">
            <div class="sig-block">
                <div class="sig-title">Diperiksa oleh,</div>
                <div class="sig-line">( ________________________ )</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">Disetujui oleh,</div>
                <div class="sig-line">( ________________________ )</div>
            </div>
            <div class="sig-block">
                <div class="sig-title">Finance,</div>
                <div class="sig-line">( ________________________ )</div>
            </div>
        </div>

    </div>{{-- body-wrap --}}

    <div class="page-footer">
        <span>Dicetak: {{ now()->format('d M Y H:i') }}</span>
        <span>{{ $pi->invoice_number }} — SCM System</span>
        <span>Dokumen ini sah tanpa tanda tangan basah</span>
    </div>
</div>

</body>
</html>
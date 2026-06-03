<style>
    table { border-collapse: collapse; width: auto; }
    td, th {
        border: 1px solid #000;
        font-size: 10px;
        padding: 4px 6px;
        vertical-align: middle;
    }
    .no-border { border: none !important; }
    .center { text-align: center; }
    .right { text-align: right; }
    .bold { font-weight: bold; }
    .yellow { background: #fff200; }
    .blue { background: #d9e2f3; }
    .soft-red { background: #f4cccc; }
    .section-gap { height: 12px; }
</style>

@php
    $visibleBahan = collect($bahanPrice)->filter(function ($b) use ($menuData) {
        foreach ($menuData as $menu) {
            if (($menu['bahan'][$b->nama_bahan] ?? 0) > 0) {
                return true;
            }
        }
        return false;
    })->values();

    $extractMenuName = function ($name) {
        $name = trim((string) $name);
        return preg_replace('/\s*\((.*?)\)\s*$/', '', $name);
    };

    $extractVisitPurpose = function ($menu) {
        $tipe = trim((string) ($menu['tipe'] ?? ''));

        if ($tipe !== '') {
            return strtoupper($tipe);
        }

        $nama = trim((string) ($menu['nama_menu'] ?? ''));
        if (preg_match('/\((.*?)\)\s*$/', $nama, $m)) {
            return strtoupper(trim($m[1]));
        }

        return 'REGULAR';
    };

    $estimasiSales = $estimasi ?? 0;
    $salesEsb = $sales_esb ?? 0;
    $uangPlus = $uang_plus ?? 0;
    $totalSalesEsb = $total_esb ?? 0;
    $totalSalesReading = $reading ?? 0;
    $selisihSales = $selisih ?? 0;
    $totalVarian = $varian ?? 0;
    $bebanVarian = $beban ?? 0;
    $rpVarian = $rp_varian ?? 0;
    $jumlahCrew = $crew ?? 0;
    $rpVarianPerCrew = $rp_per_crew ?? 0;
@endphp

{{-- ===================== HEADER / SUMMARY TOP ===================== --}}
<table>
    <tr>
        <td class="bold" colspan="3">
            PERIODE {{ $start_date }} s/d {{ $end_date }}
        </td>
        <td class="no-border" colspan="2"></td>
        <td class="bold yellow">SALES ESB</td>
        <td class="right yellow">{{ number_format($salesEsb, 0, ',', '.') }}</td>
    </tr>

    <tr>
        <td class="center">Description</td>
        <td class="center">Value</td>
        <td class="center">%</td>
        <td class="no-border" colspan="2"></td>
        <td class="bold yellow">UANG PLUS</td>
        <td class="right yellow">{{ number_format($uangPlus, 0, ',', '.') }}</td>
    </tr>

    <tr>
        <td>Estimasi Sales 1 bulan</td>
        <td class="right">{{ number_format($estimasiSales, 0, ',', '.') }}</td>
        <td class="center">0</td>
        <td class="no-border" colspan="2"></td>
        <td class="bold yellow">TOTAL SALES ESB</td>
        <td class="right yellow">{{ number_format($totalSalesEsb, 0, ',', '.') }}</td>
    </tr>

    <tr>
        <td>Total Sales</td>
        <td class="right">{{ (float) ($summary['sales'] ?? 0) }}</td>
        <td class="center">100%</td>
        <td class="no-border" colspan="2"></td>
        <td class="bold">TOTAL SALES READING</td>
        <td class="right">{{ number_format($totalSalesReading, 0, ',', '.') }}</td>
    </tr>

    <tr>
        <td>HPP</td>
        <td class="right">{{ (float) ($summary['hpp'] ?? 0) }}</td>
        <td class="center">{{ number_format($summary['hpp_percent'] ?? 0, 1) }}%</td>
        <td class="no-border" colspan="2"></td>
        <td class="bold">SELISIH</td>
        <td class="right">{{ number_format($selisihSales, 0, ',', '.') }}</td>
    </tr>

    <tr>
        <td>GP</td>
        <td class="right">{{ (float) ($summary['profit'] ?? 0) }}</td>
        <td class="center">{{ number_format($summary['profit_percent'] ?? 0, 1) }}%</td>
        <td class="no-border" colspan="2"></td>
        <td class="bold">TOTAL VARIAN (%)</td>
        <td class="center">{{ number_format($totalVarian, 1) }}%</td>
    </tr>

    <tr>
        <td>Waste</td>
        <td class="right" style="color:#c00000;">{{ number_format($summary['waste'] ?? 0, 0, ',', '.') }}</td>
        <td class="center">{{ number_format($summary['waste_percent'] ?? 0, 1) }}%</td>
        <td class="no-border" colspan="2"></td>
        <td class="bold">BEBAN VARIAN (%)</td>
        <td class="center">{{ number_format($bebanVarian, 1) }}%</td>
    </tr>

    <tr>
        <td>Selisih Persediaan</td>
        <td class="right">{{ number_format($summary['selisih_loss'] ?? 0, 0, ',', '.') }}</td>
        <td class="center">{{ number_format($summary['selisih_percent'] ?? 0, 1) }}%</td>
        <td class="no-border" colspan="2"></td>
        <td class="bold">Rp VARIAN</td>
        <td class="right" style="color:#c00000;">{{ number_format($rpVarian, 0, ',', '.') }}</td>
    </tr>

    <tr>
        <td class="soft-red center bold" colspan="3">Unit Sold</td>
        <td class="no-border" colspan="2"></td>
        <td class="bold">JUMLAH CREW</td>
        <td class="center">{{ number_format($jumlahCrew, 0, ',', '.') }}</td>
    </tr>

    <tr>
        <td class="no-border" colspan="5"></td>
        <td class="bold blue">Rp VARIAN PER CREW</td>
        <td class="right blue bold">{{ number_format($rpVarianPerCrew, 0, ',', '.') }}</td>
    </tr>
</table>

<div class="section-gap"></div>

{{-- ===================== TABLE MENU ===================== --}}
<table>
    <thead>
        <tr class="soft-red bold center">
            <th style="min-width:40px;">No</th>
            <th style="min-width:260px;">Menu</th>
            <th style="min-width:120px;">Visit Purpose</th>
            <th style="min-width:70px;">Qty</th>
            <th style="min-width:90px;">Harga</th>
            <th style="min-width:100px;">Total</th>
            @foreach ($visibleBahan as $b)
                <th style="min-width:85px;">{{ $b->nama_bahan }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @php $no = 1; @endphp
        @foreach ($menuData as $menu)
            @php
                $menuName = $extractMenuName($menu['nama_menu'] ?? '');
                $visitPurpose = $extractVisitPurpose($menu);
            @endphp
            <tr>
                <td class="center">{{ $no++ }}</td>
                <td>{{ $menuName }}</td>
                <td class="center">{{ $visitPurpose }}</td>
                <td class="center">{{ number_format($menu['unit_sold'] ?? 0, 0, ',', '.') }}</td>
<td class="right">{{ (float) ($menu['harga'] ?? 0) }}</td>
<td class="right">{{ (float) ($menu['total_sales'] ?? 0) }}</td>

                @foreach ($visibleBahan as $b)
                    <td class="center">
                        {{ $menu['bahan'][$b->nama_bahan] ?? 0 }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>

<div class="section-gap"></div>

{{-- ===================== SUMMARY BAHAN ===================== --}}
<table>
    <thead>
        <tr class="yellow bold center">
            <th style="min-width:180px;">Keterangan</th>
            @foreach ($visibleBahan as $b)
                <th style="min-width:85px;">{{ $b->nama_bahan }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        <tr>
            <td class="bold">Price / Unit</td>
            @foreach ($visibleBahan as $b)
                <td class="right">{{ number_format($b->harga_bahan ?? 0, 0, ',', '.') }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">HPP</td>
            @foreach ($visibleBahan as $b)
                <td class="right">{{ number_format($bahanSummary[$b->nama_bahan]['hpp'] ?? 0, 0, ',', '.') }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Stock (Available)</td>
            @foreach ($visibleBahan as $b)
                <td class="center">{{ $bahanSummary[$b->nama_bahan]['stock'] ?? 0 }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Usage POS</td>
            @foreach ($visibleBahan as $b)
                <td class="center">{{ $bahanSummary[$b->nama_bahan]['qty_resep'] ?? 0 }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Usage DSC</td>
            @foreach ($visibleBahan as $b)
                <td class="center">{{ $bahanSummary[$b->nama_bahan]['qty_stock'] ?? 0 }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Waste Qty</td>
            @foreach ($visibleBahan as $b)
                <td class="center">{{ $bahanSummary[$b->nama_bahan]['waste_qty'] ?? 0 }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Waste Rp</td>
            @foreach ($visibleBahan as $b)
                <td class="right">{{ number_format($bahanSummary[$b->nama_bahan]['waste_rp'] ?? 0, 0, ',', '.') }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Difference (DSC - Waste - POS)</td>
            @foreach ($visibleBahan as $b)
                @php
                    $row = $bahanSummary[$b->nama_bahan] ?? [];

                    $rawQty = (float) ($row['diff_raw_qty'] ?? 0);
                    $visibleQty = (float) ($row['diff_visible_qty'] ?? 0);

                    // Ikuti logic UI:
                    // rawQty > 0 = minus/loss tampil "-"
                    // rawQty < 0 = plus/gain tampil angka biasa
                    $exportQty = $rawQty > 0 ? -1 * $visibleQty : $visibleQty;

                    $satuan = strtolower($row['satuan'] ?? '');
                    $dec = in_array($satuan, ['pcs', 'pc', 'piece'], true) ? 0 : 2;
                @endphp

                <td class="center">{{ number_format($exportQty, $dec, ',', '.') }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Avg Usage / Hari</td>
            @foreach ($visibleBahan as $b)
                <td class="center">{{ $bahanSummary[$b->nama_bahan]['avg_per_day'] ?? 0 }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Prediksi Usage {{ $forecastDays }} Hari</td>
            @foreach ($visibleBahan as $b)
                <td class="center">{{ $bahanSummary[$b->nama_bahan]['forecast_qty'] ?? 0 }}</td>
            @endforeach
        </tr>

        <tr>
            <td class="bold">Prediksi Stock Setelah {{ $forecastDays }} Hari</td>
            @foreach ($visibleBahan as $b)
                <td class="center">{{ $bahanSummary[$b->nama_bahan]['forecast_stock'] ?? 0 }}</td>
            @endforeach
        </tr>
    </tbody>
</table>
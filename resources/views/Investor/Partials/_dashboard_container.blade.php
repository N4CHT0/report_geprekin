<div id="dashboardWrapper" class="d-flex flex-column gap-4">

    {{-- KPI --}}
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total Omset</div>
                            <div class="fw-bold fs-5">Rp {{ number_format($totalOmset ?? 0) }}</div>
                        </div>
                        <i class="bi bi-cash-coin fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Total CU</div>
                            <div class="fw-bold fs-5">{{ number_format($totalCU ?? 0) }}</div>
                        </div>
                        <i class="bi bi-people-fill fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">AVG Sales / Hari</div>
                            <div class="fw-bold fs-5">Rp {{ number_format($averageSales ?? 0) }}</div>
                        </div>
                        <i class="bi bi-graph-up-arrow fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PLATFORM + PEMESANAN + PEMBAYARAN --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <h6 class="fw-semibold text-secondary mb-2 mt-2"></h6>
                <small class="text-muted d-block text-end">
                    Total Omset: <b>Rp {{ number_format($totalOmset ?? 0) }}</b><br>
                    Avg / Hari: <b>Rp {{ number_format($averageSales ?? 0) }}</b>
                </small>
            </div>

            @php
                $platformOnline = [
                    'shopeefood'  => ['label' => 'ShopeeFood',  'icon' => 'bi-basket-fill',        'color' => 'success'],
                    'grabfood'    => ['label' => 'GrabFood',    'icon' => 'bi-bag-fill',           'color' => 'warning'],
                    'gofood'      => ['label' => 'GoFood',      'icon' => 'bi-shop',               'color' => 'danger'],
                    'qpon'        => ['label' => 'Qpon',        'icon' => 'bi-gift-fill',          'color' => 'primary'],
                    'tiktok_shop' => ['label' => 'TikTok Shop', 'icon' => 'bi-play-circle-fill',   'color' => 'dark'],
                ];

                $jenisPemesanan = [
                    'takeaway' => ['label' => 'Takeaway', 'icon' => 'bi-bag-check-fill', 'color' => 'secondary'],
                    'dinein'   => ['label' => 'Dine In',  'icon' => 'bi-cup-hot-fill',   'color' => 'info'],
                ];

                $platformPembayaran = [
                    'cash'           => ['label' => 'Cash',           'icon' => 'bi-cash-stack',   'color' => 'success'],
                    'transfer'       => ['label' => 'Transfer',       'icon' => 'bi-bank',         'color' => 'dark'],
                    'qris_bca'       => ['label' => 'QRIS BCA',       'icon' => 'bi-qr-code-scan', 'color' => 'primary'],
                    'qris_bukupay'   => ['label' => 'QRIS Bukupay',   'icon' => 'bi-upc-scan',     'color' => 'secondary'],
                    'qris_esb'       => ['label' => 'QRIS ESB (POS)', 'icon' => 'bi-qr-code',      'color' => 'info'],
                    'qris_gopay'     => ['label' => 'QRIS GoPay',     'icon' => 'bi-qr-code-scan', 'color' => 'info'],
                    'qris_shopeepay' => ['label' => 'QRIS ShopeePay', 'icon' => 'bi-upc',          'color' => 'warning'],
                ];
            @endphp

            <h6 class="fw-semibold text-secondary mb-2">
                <i class="bi bi-globe2 me-1"></i> Platform Online
            </h6>
            <div class="row g-3 mb-4">
                @foreach ($platformOnline as $key => $p)
                    @php $val = $totalPerPlatform[$key] ?? 0; @endphp
                    <div class="col-6 col-md-3">
                        <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                            <div class="text-{{ $p['color'] }}"><i class="bi {{ $p['icon'] }} fs-3"></i></div>
                            <div class="text-end">
                                <div class="fw-bold text-dark">Rp {{ number_format($val) }}</div>
                                <small class="text-muted">{{ $p['label'] }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <h6 class="fw-semibold text-secondary mb-2">
                <i class="bi bi-bag me-1"></i> Jenis Pemesanan
            </h6>
            <div class="row g-3 mb-4">
                @foreach ($jenisPemesanan as $key => $p)
                    @php
                        $val = 0;
                        if ($key === 'takeaway') $val = $takeawayTotal ?? 0;
                        if ($key === 'dinein')   $val = $dineinTotal ?? 0;
                    @endphp
                    <div class="col-6 col-md-3">
                        <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                            <div class="text-{{ $p['color'] }}"><i class="bi {{ $p['icon'] }} fs-3"></i></div>
                            <div class="text-end">
                                <div class="fw-bold text-dark">Rp {{ number_format($val) }}</div>
                                <small class="text-muted">{{ $p['label'] }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <h6 class="fw-semibold text-secondary mb-2">
                <i class="bi bi-wallet2 me-1"></i> Jenis Pembayaran
            </h6>
            <div class="row g-3">
                @foreach ($platformPembayaran as $key => $p)
                    <div class="col-6 col-md-3">
                        <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                            <div class="text-{{ $p['color'] }}"><i class="bi {{ $p['icon'] }} fs-3"></i></div>
                            <div class="text-end">
                                <div class="fw-bold text-dark">Rp {{ number_format($totalPerPlatform[$key] ?? 0) }}</div>
                                <small class="text-muted">{{ $p['label'] }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    {{-- OMSET --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-semibold text-primary mb-0">
                    <i class="bi bi-bar-chart-line me-2"></i> Statistik Penjualan
                </h6>
                <small class="text-muted">Total Omset: Rp {{ number_format($totalOmset ?? 0) }}</small>
            </div>

            <div class="chart-container" style="min-height:350px;">
                @if ($filterApplied && count($chartOmset ?? []) > 0)
                    <canvas id="sales-chart" style="width:100%; height:340px;"></canvas>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-info-circle fs-1"></i>
                        <div class="mt-2">Harap terapkan filter untuk melihat grafik</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- CU --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-semibold text-primary mb-0">
                    <i class="bi bi-people-fill me-2"></i> Customer Unit
                </h6>
                <small class="text-muted">Total CU: {{ number_format($totalCU ?? 0) }}</small>
            </div>

            <div class="chart-container" style="min-height:320px;">
                @if ($filterApplied && count($chartCU ?? []) > 0)
                    <canvas id="visitors-chart" style="width:100%; height:320px;"></canvas>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle"></i><br>Harap filter terlebih dahulu.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ORDERS --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-semibold text-primary mb-0">
                    <i class="bi bi-receipt me-2"></i> Jumlah Transaksi
                </h6>
                <small class="text-muted">Range: {{ request('tanggal_awal') }} s/d {{ request('tanggal_akhir') }}</small>
            </div>

            <div class="chart-container" style="min-height:300px;">
                @if ($filterApplied && count($chartOrders ?? []) > 0)
                    <canvas id="orders-chart" style="width:100%; height:300px;"></canvas>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle"></i><br>Harap filter terlebih dahulu.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- AOV --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-semibold text-primary mb-0">
                    <i class="bi bi-graph-up-arrow me-2"></i> AOV (Omset / CU)
                </h6>
                <small class="text-muted">Rata-rata nilai transaksi per CU</small>
            </div>

            <div class="chart-container" style="min-height:300px;">
                @if ($filterApplied && count($chartAOV ?? []) > 0)
                    <canvas id="aov-chart" style="width:100%; height:300px;"></canvas>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-info-circle"></i><br>Harap filter terlebih dahulu.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- HOURLY + PRODUK --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h6 class="fw-semibold text-primary mb-3">
                        <i class="bi bi-clock me-2"></i> Transaksi per Jam
                    </h6>

                    <div class="chart-container chart-fixed-260">
                        @if ($filterApplied)
                            <canvas id="hourly-sales-chart"></canvas>
                            <div class="small text-muted mt-2">
                                * Menampilkan <b>Transaksi</b> (kiri), <b>Omset</b> (kanan), dan <b>CU</b> (line).
                            </div>
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-info-circle"></i><br>Harap filter terlebih dahulu.
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold text-primary mb-0">
                            <i class="bi bi-box-seam me-2"></i> Penjualan Produk
                        </h6>
                        <select id="filterKategori" class="form-select form-select-sm" style="width:150px;">
                            <option value="">Semua</option>
                            <option value="PAKET">PAKET</option>
                            <option value="ALACARTE">ALACARTE</option>
                            <option value="DISKON">PROMO</option>
                        </select>
                    </div>

                    <div class="table-responsive" style="max-height:420px;">
                        <table class="table table-hover table-sm align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="min-width:200px;">Produk</th>
                                    <th class="text-end">Penjualan</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topProduk ?? collect() as $p)
                                    <tr>
                                        <td class="text-truncate" style="max-width:260px;">{{ $p->item_nama }}</td>
                                        <td class="text-end">{{ number_format($p->total_penjualan) }}</td>
                                        <td class="text-end">Rp {{ number_format($p->total_harga ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Tidak ada data</td>
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

{{-- JSON data untuk charts --}}
@php
    $hourKeys = array_keys($chartHourlyFull ?? []);
    $hourLabels = [];
    foreach ($hourKeys as $h) { $hourLabels[] = sprintf('%02d:00', $h); }
@endphp

<script type="application/json" id="dashData">
{!! json_encode([
    'filterApplied' => (bool)($filterApplied ?? false),
    'chartLabels' => $chartLabels ?? [],
    'chartOmset' => $chartOmset ?? [],
    'chartCU' => $chartCU ?? [],
    'chartOrders' => $chartOrders ?? [],
    'chartAOV' => $chartAOV ?? [],
    'chartHourlyLabels' => $hourLabels,
    'chartHourlyFull' => array_values($chartHourlyFull ?? []),
    'chartHourlyOmsetFull' => array_values($chartHourlyOmsetFull ?? []),
    'chartHourlyCUFull' => array_values($chartHourlyCUFull ?? []),
]) !!}
</script>

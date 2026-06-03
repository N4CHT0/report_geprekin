@include('temp.header')
@include('temp.navbar')
@include('temp.sidebar')

@php
    $laporan = $laporan instanceof \Illuminate\Support\Collection ? $laporan->values() : collect($laporan ?? [])->values();
    $topProduk = $topProduk instanceof \Illuminate\Support\Collection ? $topProduk->values() : collect($topProduk ?? [])->values();
    $notifikasiTurunSales = $notifikasiTurunSales instanceof \Illuminate\Support\Collection ? $notifikasiTurunSales->values() : collect($notifikasiTurunSales ?? [])->values();

    $rp = fn($v) => 'Rp ' . number_format((float)($v ?? 0), 0, ',', '.');
    $num = fn($v) => number_format((float)($v ?? 0), 0, ',', '.');

    $platformOnline = [
        'shopeefood'  => ['label' => 'ShopeeFood',  'icon' => 'bi-basket-fill',      'class' => 'text-success bg-success-subtle'],
        'grabfood'    => ['label' => 'GrabFood',    'icon' => 'bi-bag-fill',         'class' => 'text-warning bg-warning-subtle'],
        'gofood'      => ['label' => 'GoFood',      'icon' => 'bi-shop',             'class' => 'text-danger bg-danger-subtle'],
        'qpon'        => ['label' => 'Qpon',        'icon' => 'bi-gift-fill',        'class' => 'text-primary bg-primary-subtle'],
        'tiktok_shop' => ['label' => 'TikTok Shop', 'icon' => 'bi-play-circle-fill', 'class' => 'text-dark bg-secondary-subtle'],
    ];

    $platformPembayaran = [
        'cash'           => ['label' => 'Cash',           'icon' => 'bi-cash-stack',   'class' => 'text-success bg-success-subtle'],
        'transfer'       => ['label' => 'Transfer',       'icon' => 'bi-bank',         'class' => 'text-dark bg-secondary-subtle'],
        'qris_bca'       => ['label' => 'QRIS BCA',       'icon' => 'bi-qr-code-scan', 'class' => 'text-primary bg-primary-subtle'],
        'qris_bukupay'   => ['label' => 'QRIS Bukupay',   'icon' => 'bi-upc-scan',     'class' => 'text-secondary bg-secondary-subtle'],
        'qris_esb'       => ['label' => 'QRIS ESB',       'icon' => 'bi-qr-code',      'class' => 'text-info bg-info-subtle'],
        'qris_gopay'     => ['label' => 'QRIS GoPay',     'icon' => 'bi-qr-code-scan', 'class' => 'text-info bg-info-subtle'],
        'qris_shopeepay' => ['label' => 'QRIS ShopeePay', 'icon' => 'bi-upc',          'class' => 'text-warning bg-warning-subtle'],
    ];

    $chartLabels = $chartLabels ?? [];
    $chartOmset = $chartOmset ?? [];
    $chartCU = $chartCU ?? [];
    $chartOrders = $chartOrders ?? [];
    $chartAOV = $chartAOV ?? [];
    $chartHourlyFull = $chartHourlyFull ?? [];
    $chartHourlyOmsetFull = $chartHourlyOmsetFull ?? [];
@endphp

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">

            {{-- FILTER ALPINE --}}
            <div x-cloak x-show="filterOpen" x-transition class="gp-card mb-4">
                <div class="card-header bg-primary text-white rounded-top-4 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-funnel me-2"></i> Filter Data
                        </h6>
                        <small class="text-white-50">Gunakan filter untuk menampilkan data</small>
                    </div>
                </div>

                <div class="card-body bg-light rounded-bottom-4">
                    <form method="GET" action="{{ route('monitoring.sales') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-semibold text-secondary">Tanggal Awal</label>
                                <input type="text" id="tanggal_awal" name="tanggal_awal" class="form-control form-control-sm"
                                       value="{{ $tanggalAwal ?? request('tanggal_awal') }}" placeholder="YYYY-MM-DD">
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-semibold text-secondary">Tanggal Akhir</label>
                                <input type="text" id="tanggal_akhir" name="tanggal_akhir" class="form-control form-control-sm"
                                       value="{{ $tanggalAkhir ?? request('tanggal_akhir') }}" placeholder="YYYY-MM-DD">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Outlet</label>
                                <select id="outlet" name="outlet" class="form-select form-select-sm">
                                    <option value="">-- Semua Outlet --</option>
                                    @foreach(($outlets ?? []) as $o)
                                        <option value="{{ $o->id }}" {{ (string)($selectedOutletId ?? request('outlet')) === (string)$o->id ? 'selected' : '' }}>
                                            {{ $o->nama_outlet_display ?? $o->nama_outlet }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                    <i class="bi bi-search me-1"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TITLE --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold text-primary mb-1">
                        <i class="bi bi-bar-chart-fill me-2"></i> Monitoring Sales
                    </h4>
                    <div class="text-muted small">{{ $tanggalAwal ?? '-' }} s/d {{ $tanggalAkhir ?? '-' }}</div>
                </div>

                <div class="d-flex gap-2">
                    <span class="badge rounded-pill text-bg-primary px-3 py-2">
                        Outlet: {{ $totalOutletUnik ?? 0 }}
                    </span>
                    <span class="badge rounded-pill text-bg-danger px-3 py-2">
                        Turun: {{ $notifikasiTurunSales->count() }}
                    </span>
                </div>
            </div>

            {{-- KPI --}}
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-3">
                    <div class="gp-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Total Omset</div>
                                <div class="fw-bold fs-4">{{ $rp($totalOmset ?? 0) }}</div>
                            </div>
                            <i class="bi bi-cash-coin fs-1 text-primary"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="gp-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Total CU</div>
                                <div class="fw-bold fs-4">{{ $num($totalCU ?? 0) }}</div>
                            </div>
                            <i class="bi bi-people-fill fs-1 text-success"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="gp-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">AVG Sales / Hari</div>
                                <div class="fw-bold fs-4">{{ $rp($averageSales ?? 0) }}</div>
                            </div>
                            <i class="bi bi-graph-up-arrow fs-1 text-warning"></i>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <div class="gp-card p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Non Sales Transaction</div>
                                <div class="fw-bold fs-4">{{ $rp($nonSalesTransaction ?? 0) }}</div>
                            </div>
                            <i class="bi bi-receipt-cutoff fs-1 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PLATFORM --}}
            <div class="gp-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-grid-3x3-gap me-2"></i> Ringkasan Platform
                    </h6>
                    <small class="text-muted text-end">
                        Total Omset: <b>{{ $rp($totalOmset ?? 0) }}</b><br>
                        Avg / Hari: <b>{{ $rp($averageSales ?? 0) }}</b>
                    </small>
                </div>

                <h6 class="fw-semibold text-secondary mb-2">
                    <i class="bi bi-globe2 me-1"></i> Platform Online
                </h6>
                <div class="row g-3 mb-4">
                    @foreach($platformOnline as $key => $p)
                        <div class="col-6 col-md-3">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded-4 bg-white h-100">
                                <div class="rounded-circle p-3 {{ $p['class'] }}">
                                    <i class="bi {{ $p['icon'] }} fs-4"></i>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-dark">{{ $rp($totalPerPlatform[$key] ?? 0) }}</div>
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
                    <div class="col-6 col-md-3">
                        <div class="d-flex justify-content-between align-items-center p-3 border rounded-4 bg-white h-100">
                            <div class="rounded-circle p-3 text-secondary bg-secondary-subtle">
                                <i class="bi bi-bag-check-fill fs-4"></i>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-dark">{{ $rp($takeawayTotal ?? 0) }}</div>
                                <small class="text-muted">Takeaway</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <div class="d-flex justify-content-between align-items-center p-3 border rounded-4 bg-white h-100">
                            <div class="rounded-circle p-3 text-info bg-info-subtle">
                                <i class="bi bi-cup-hot-fill fs-4"></i>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-dark">{{ $rp($dineinTotal ?? 0) }}</div>
                                <small class="text-muted">Dine In</small>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="fw-semibold text-secondary mb-2">
                    <i class="bi bi-wallet2 me-1"></i> Jenis Pembayaran
                </h6>
                <div class="row g-3">
                    @foreach($platformPembayaran as $key => $p)
                        <div class="col-6 col-md-3">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded-4 bg-white h-100">
                                <div class="rounded-circle p-3 {{ $p['class'] }}">
                                    <i class="bi {{ $p['icon'] }} fs-4"></i>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-dark">{{ $rp($totalPerPlatform[$key] ?? 0) }}</div>
                                    <small class="text-muted">{{ $p['label'] }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- CHARTS PERSIS POLA LAMA --}}
            <div class="gp-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-bar-chart-line me-2"></i> Statistik Penjualan
                    </h6>
                    <small class="text-muted">Total Omset: {{ $rp($totalOmset ?? 0) }}</small>
                </div>
                <div class="chart-wrap">
                    @if($filterApplied && count($chartOmset) > 0)
                        <canvas id="sales-chart"></canvas>
                    @else
                        <div class="text-center text-muted py-5">Harap filter terlebih dahulu.</div>
                    @endif
                </div>
            </div>

            <div class="gp-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-people-fill me-2"></i> Customer Unit
                    </h6>
                    <small class="text-muted">Total CU: {{ $num($totalCU ?? 0) }}</small>
                </div>
                <div class="chart-wrap sm">
                    @if($filterApplied && count($chartCU) > 0)
                        <canvas id="visitors-chart"></canvas>
                    @else
                        <div class="text-center text-muted py-5">Harap filter terlebih dahulu.</div>
                    @endif
                </div>
            </div>

            <div class="gp-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-receipt me-2"></i> Jumlah Transaksi
                    </h6>
                    <small class="text-muted">{{ $tanggalAwal ?? '-' }} s/d {{ $tanggalAkhir ?? '-' }}</small>
                </div>
                <div class="chart-wrap md">
                    @if($filterApplied && count($chartOrders) > 0)
                        <canvas id="orders-chart"></canvas>
                    @else
                        <div class="text-center text-muted py-5">Harap filter terlebih dahulu.</div>
                    @endif
                </div>
            </div>

            <div class="gp-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-primary mb-0">
                        <i class="bi bi-graph-up-arrow me-2"></i> AOV (Omset / CU)
                    </h6>
                    <small class="text-muted">Rata-rata nilai transaksi per CU</small>
                </div>
                <div class="chart-wrap md">
                    @if($filterApplied && count($chartAOV) > 0)
                        <canvas id="aov-chart"></canvas>
                    @else
                        <div class="text-center text-muted py-5">Harap filter terlebih dahulu.</div>
                    @endif
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="gp-card p-4 h-100">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-clock me-2"></i> Transaksi per Jam
                        </h6>
                        <div class="chart-wrap h260">
                            @if($filterApplied)
                                <canvas id="hourly-sales-chart"></canvas>
                            @else
                                <div class="text-center text-muted py-5">Harap filter terlebih dahulu.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    <div class="gp-card p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-primary mb-0">
                                <i class="bi bi-box-seam me-2"></i> Penjualan Produk
                            </h6>
                            <select id="filterKategori" class="form-select form-select-sm" style="width:150px;">
                                <option value="">Semua</option>
                                <option value="PAKET">PAKET</option>
                                <option value="ALACARTE">ALACARTE</option>
                                <option value="DISKON">PROMO</option>
                            </select>
                        </div>

                        <div class="table-responsive soft-scroll" style="max-height:420px;">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-end">Penjualan</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProduk as $p)
                                        <tr>
                                            <td class="text-truncate" style="max-width:260px;">{{ $p->item_nama }}</td>
                                            <td class="text-end">{{ $num($p->total_penjualan ?? 0) }}</td>
                                            <td class="text-end">{{ $rp($p->total_harga ?? 0) }}</td>
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

            <script type="application/json" id="dashData">
                {!! json_encode([
                    'filterApplied' => (bool)($filterApplied ?? false),
                    'chartLabels' => $chartLabels,
                    'chartOmset' => $chartOmset,
                    'chartCU' => $chartCU,
                    'chartOrders' => $chartOrders,
                    'chartAOV' => $chartAOV,
                    'chartHourlyLabels' => array_map(fn($h) => sprintf('%02d:00', $h), array_keys($chartHourlyFull)),
                    'chartHourlyFull' => array_values($chartHourlyFull),
                    'chartHourlyOmsetFull' => array_values($chartHourlyOmsetFull),
                ]) !!}
            </script>

        </div>
    </div>
</main>

{{-- MODAL NOTIF --}}
<div x-cloak x-show="notifOpen" x-transition class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4">
    <div @click.outside="notifOpen=false" class="bg-white rounded-4 shadow-lg w-full max-w-2xl overflow-hidden">
        <div class="p-4 bg-danger text-white d-flex justify-content-between align-items-center">
            <div class="fw-bold">Notifikasi Sales Turun</div>
            <button @click="notifOpen=false" class="btn btn-sm btn-light">Tutup</button>
        </div>
        <div class="p-4 soft-scroll" style="max-height:65vh;overflow:auto;">
            @forelse($notifikasiTurunSales as $n)
                <div class="border rounded-4 p-3 mb-3">
                    <div class="fw-bold">{{ $n['nama_outlet'] ?? '-' }}</div>
                    <div class="small text-muted">{{ $n['tanggal_pembanding'] ?? '-' }} → {{ $n['tanggal_terbaru'] ?? '-' }}</div>
                    <div class="mt-2 text-danger fw-bold">
                        Turun {{ $n['persen_turun'] ?? 0 }}% / Rp {{ $n['selisih_rupiah'] ?? 0 }}
                    </div>
                </div>
            @empty
                <div class="text-muted text-center py-4">Tidak ada notifikasi.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
(function () {
    const dataEl = document.getElementById('dashData');
    if (!dataEl || typeof Chart === 'undefined') return;

    const data = JSON.parse(dataEl.textContent || '{}');
    const rupiah = value => 'Rp ' + Number(value || 0).toLocaleString('id-ID');

    if (typeof flatpickr !== 'undefined') {
        flatpickr("#tanggal_awal", { dateFormat: "Y-m-d", allowInput: true });
        flatpickr("#tanggal_akhir", { dateFormat: "Y-m-d", allowInput: true });
    }

    if (window.jQuery && $.fn.select2) {
        $('#outlet').select2({ placeholder: '-- Semua Outlet --', allowClear: true, width: '100%' });
    }

    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = '#64748b';
    Chart.defaults.borderColor = 'rgba(148,163,184,.25)';

    function lineChart(id, labels, datasetLabel, values, color, formatter) {
        const el = document.getElementById(id);
        if (!el) return;

        new Chart(el, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: datasetLabel,
                    data: values,
                    borderColor: color,
                    backgroundColor: color.replace('1)', '.12)'),
                    borderWidth: 3,
                    tension: .35,
                    fill: true,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: ctx => datasetLabel + ': ' + formatter(ctx.raw)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: formatter }
                    }
                }
            }
        });
    }

    lineChart('sales-chart', data.chartLabels, 'Omset', data.chartOmset, 'rgba(13,110,253,1)', rupiah);
    lineChart('visitors-chart', data.chartLabels, 'Customer Unit', data.chartCU, 'rgba(25,135,84,1)', v => Number(v || 0).toLocaleString('id-ID'));
    lineChart('orders-chart', data.chartLabels, 'Jumlah Transaksi', data.chartOrders, 'rgba(255,193,7,1)', v => Number(v || 0).toLocaleString('id-ID'));
    lineChart('aov-chart', data.chartLabels, 'AOV', data.chartAOV, 'rgba(220,53,69,1)', rupiah);

    const hourlyEl = document.getElementById('hourly-sales-chart');
    if (hourlyEl) {
        new Chart(hourlyEl, {
            type: 'bar',
            data: {
                labels: data.chartHourlyLabels,
                datasets: [
                    {
                        label: 'Transaksi',
                        data: data.chartHourlyFull,
                        backgroundColor: 'rgba(13,110,253,.75)',
                        borderRadius: 8,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Omset',
                        data: data.chartHourlyOmsetFull,
                        type: 'line',
                        borderColor: 'rgba(255,99,132,1)',
                        borderWidth: 2,
                        tension: .35,
                        pointRadius: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label === 'Omset'
                                ? 'Omset: ' + rupiah(ctx.raw)
                                : 'Transaksi: ' + Number(ctx.raw || 0).toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { callback: rupiah }
                    }
                }
            }
        });
    }

    const kategori = document.getElementById('filterKategori');
    if (kategori) {
        kategori.addEventListener('change', function () {
            const url = new URL(window.location.href);
            if (this.value) url.searchParams.set('kategori', this.value);
            else url.searchParams.delete('kategori');
            window.location.href = url.toString();
        });
    }
})();
</script>

@include('temp.footer')
{{-- resources/views/investor/dashboardGO.blade.php --}}
@include('Temp.Investor.header')

<!--<main class="app-main">-->
<!--    <div class="app-content">-->
<!--        <div class="container-fluid py-3 py-md-4">-->
<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">

            <!--{{-- ================= DEPENDENCIES ================= --}}-->
            <!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">-->
            <!--<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">-->
            <!--<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet">-->

            <!--<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>-->
            <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>-->
            <!--<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>-->
            <!--{{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.full.min.js"></script> --}}-->
            <!--<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>-->
            <!--<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js">-->
            <!--</script>-->
            <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>-->
            <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>-->
            
            <!--{{-- ================= DEPENDENCIES ================= --}}-->
            <!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">-->
            <!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">-->
            <!--<link rel="stylesheet" href="adminlte.css">-->
            <!--<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>-->

            <!--{{-- Bootstrap JS diperlukan untuk modal --}}-->
            <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>-->

            <!--<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>-->
            <!--<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>-->
            <!--<script src="adminlte.js"></script>-->
            
            {{-- DEPENDENCIES --}}
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
            <!--<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet">-->
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
            <!--<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">-->
            <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
            <link href="adminlte.css" rel="stylesheet">

            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>-->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
            <!--<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.full.min.js"></script>-->
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
            <script src="adminlte.js"></script>

            {{-- ================= HEADER ================= --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <h4 class="fw-semibold mb-2 text-primary d-flex align-items-center">
                    <i class="bi bi-bar-chart-fill me-2"></i> Dashboard Penjualan
                </h4>

                <div class="d-flex gap-2">
                    <button id="exportPDF" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </button>
                </div>
            </div>

            {{-- ================= FILTER CARD ================= --}}
            <div id="filterCard" class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-primary text-white py-2 rounded-top-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold"><i class="fas fa-filter me-2"></i> Filter Data</h6>
                        <small class="text-white-50">Gunakan filter untuk menampilkan data</small>
                    </div>
                </div>

                <div class="card-body bg-light rounded-bottom-3">
                    <form id="filterForm" method="GET" action="{{ route('investor.sales.dashboard') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-semibold text-secondary">
                                    <i class="bi bi-calendar me-1 text-primary"></i> Tanggal Awal
                                </label>
                                <input type="text" id="tanggal_awal" name="tanggal_awal"
                                    class="form-control form-control-sm" value="{{ request('tanggal_awal') }}"
                                    placeholder="YYYY-MM-DD" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-semibold text-secondary">
                                    <i class="bi bi-calendar-check me-1 text-primary"></i> Tanggal Akhir
                                </label>
                                <input type="text" id="tanggal_akhir" name="tanggal_akhir"
                                    class="form-control form-control-sm" value="{{ request('tanggal_akhir') }}"
                                    placeholder="YYYY-MM-DD" autocomplete="off">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-secondary">
                                    <i class="bi bi-shop me-1 text-primary"></i> Outlet
                                </label>
                                <select id="outlet" name="outlet" class="form-select form-select-sm">
                                    <option value="">-- Semua Outlet --</option>
                                    @foreach ($outlets as $o)
                                        <option value="{{ $o->id }}"
                                            {{ request('outlet') == $o->id ? 'selected' : '' }}>
                                            {{ ucwords($o->nama_outlet_clean) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-search me-1"></i> Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ================= DASHBOARD WRAPPER ================= --}}
            <div id="dashboardWrapper" class="d-flex flex-column gap-4">

                {{-- 1️⃣ OMSET PER PLATFORM --}}
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="fw-semibold text-secondary mb-2 mt-2">
                                <i class="bi bi-globe2 me-1"></i> Platform Online
                            </h6>
                            <div class="text-end"> <small class="text-muted"> Total Omset: Rp {{ number_format($totalOmset ?? 0) }} </small><br> <small class="text-primary fw-semibold"> Average: Rp {{ number_format($averageSales ?? 0) }} </small> </div>
                        </div>

                        @php
                            $platformOnline = [
                                'shopeefood' => [
                                    'label' => 'ShopeeFood',
                                    'icon' => 'bi-basket-fill',
                                    'color' => 'success',
                                ],
                                'grabfood' => ['label' => 'GrabFood', 'icon' => 'bi-bag-fill', 'color' => 'warning'],
                                'gofood' => ['label' => 'GoFood', 'icon' => 'bi-shop', 'color' => 'danger'],
                                'qpon' => ['label' => 'Qpon', 'icon' => 'bi-gift-fill', 'color' => 'primary'],
                                  'tiktok_shop'=> ['label' => 'TikTok Shop', 'icon' => 'bi-tiktok', 'color' => 'dark'],                      ];

                            $platformPembayaran = [
                                'cash' => ['label' => 'Cash', 'icon' => 'bi-cash-stack', 'color' => 'success'],
                                // 'dp' => ['label' => 'DP', 'icon' => 'bi-wallet2', 'color' => 'secondary'],
                                'transfer' => ['label' => 'Transfer', 'icon' => 'bi-bank', 'color' => 'dark'],
                                'qris_bca' => ['label' => 'QRIS BCA', 'icon' => 'bi-qr-code-scan', 'color' => 'indigo'],
                                'qris_bukupay' => [
                                    'label' => 'QRIS Bukupay',
                                    'icon' => 'bi-upc-scan',
                                    'color' => 'purple',
                                ],
                                'qris_esb' => ['label' => 'QRIS ESB (POS)', 'icon' => 'bi-qr-code', 'color' => 'teal'],
                                'qris_gopay' => [
                                    'label' => 'QRIS GoPay',
                                    'icon' => 'bi-qr-code-scan',
                                    'color' => 'info',
                                ],
                                'qris_shopeepay' => [
                                    'label' => 'QRIS ShopeePay',
                                    'icon' => 'bi-upc',
                                    'color' => 'orange',
                                ],
                            ];
                        @endphp

                        {{-- 🔹 Bagian 1: Platform Online --}}
                        <div class="row g-3 mb-4">
                            @foreach ($platformOnline as $key => $p)
                                <div class="col-6 col-md-3">
                                    <div
                                        class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                                        <div class="text-{{ $p['color'] }}">
                                            <i class="bi {{ $p['icon'] }} fs-3"></i>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark">
                                                Rp {{ number_format($totalPerPlatform[$key] ?? 0) }}
                                            </div>
                                            <small class="text-muted">{{ $p['label'] }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- 🔹 Bagian 2: Jenis Pembayaran --}}
                        <h6 class="fw-semibold text-secondary mb-2">
                            <i class="bi bi-wallet2 me-1"></i> Jenis Pembayaran
                        </h6>
                        <div class="row g-3">
                            @foreach ($platformPembayaran as $key => $p)
                                <div class="col-6 col-md-3">
                                    <div
                                        class="d-flex justify-content-between align-items-center p-3 border rounded-3 bg-white">
                                        <div class="text-{{ $p['color'] }}">
                                            <i class="bi {{ $p['icon'] }} fs-3"></i>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark">
                                                Rp {{ number_format($totalPerPlatform[$key] ?? 0) }}
                                            </div>
                                            <small class="text-muted">{{ $p['label'] }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

                {{-- 2️⃣ STATISTIK PENJUALAN (FULL WIDTH) --}}
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold text-primary mb-0">
                                <i class="bi bi-bar-chart-line me-2"></i> Statistik Penjualan
                            </h6>
                            <div class="text-end"> <small class="text-muted"> Total: Rp {{ number_format($totalOmset ?? 0) }} </small><br> <small class="text-primary fw-semibold"> Average: Rp {{ number_format($averageSales ?? 0) }} </small> </div>
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

                {{-- 3️⃣ CUSTOMER UNIT (FULL WIDTH) --}}
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

                {{-- 4️⃣ TRANSAKSI PER JAM + PRODUK --}}
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body">
                                <h6 class="fw-semibold text-primary mb-3">
                                    <i class="bi bi-clock me-2"></i> Transaksi per Jam
                                </h6>
                                <div class="chart-container" style="min-height:260px;">
                                    @if ($filterApplied)
                                        <canvas id="hourly-sales-chart" style="width:100%; height:260px;"></canvas>
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
                                    <select id="filterKategori" class="form-select form-select-sm"
                                        style="width:150px;">
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
                                            @forelse ($topProduk as $p)
                                                <tr>
                                                    <td class="text-truncate" style="max-width:260px;">
                                                        {{ $p->item_nama }}</td>
                                                    <td class="text-end">{{ number_format($p->total_penjualan) }}</td>
                                                    <td class="text-end">Rp {{ number_format($p->total_harga ?? 0) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Tidak ada data
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

            </div> {{-- end dashboardWrapper --}}
        </div>
    </div>
</main>

{{-- ================= STYLES ================= --}}
<style>
    body {
        background-color: #f5f6f8;
        font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        color: #2b2b2b;
    }

    .card {
        transition: transform .18s ease, box-shadow .18s ease;
        border-radius: .6rem;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(16, 24, 40, 0.06);
    }

    .form-label {
        margin-bottom: .25rem;
        font-size: .85rem;
    }

    .table thead th {
        font-size: .85rem;
    }

    .table td {
        font-size: .9rem;
        vertical-align: middle;
    }

    .chart-container {
        position: relative;
        width: 100%;
    }

    /* ==== SELECT2 RESPONSIVE FIX ==== */
    .select2-container {
        width: 100% !important;
    }

    /* Samakan tinggi dan gaya dengan form-select-sm Bootstrap 5 */
    .select2-container .select2-selection--single {
        height: calc(1.8125rem + 2px) !important;
        padding: 0.25rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        color: #212529;
        font-size: .875rem;
    }

    .select2-container--bootstrap-5 .select2-selection__arrow {
        top: 50%;
        transform: translateY(-50%);
        right: 0.75rem;
    }

    /* Responsif di HP */
    @media (max-width: 576px) {
        .select2-container {
            font-size: .85rem;
        }
    }

    @media (max-width: 991px) {
        .card-body {
            padding: .9rem;
        }

        h4 {
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .card {
            border-radius: .5rem;
        }
    }
</style>

{{-- ================= SCRIPTS ================= --}}
<script>
    (function() {
        // ================== LIBRARY CHECK ==================
        const hasJQuery = typeof window.jQuery !== 'undefined';
        const hasFlatpickr = typeof window.flatpickr !== 'undefined';
        const hasChart = typeof window.Chart !== 'undefined';

        let select2Ready = false;
        console.log('%c[Dashboard Init Check]', 'color:#0d6efd;font-weight:bold;');
        console.table({
            jQuery: hasJQuery,
            flatpickr: hasFlatpickr,
            ChartJS: hasChart
        });

        if (!hasJQuery || !hasFlatpickr || !hasChart) {
            console.warn('⚠️ Beberapa library belum termuat dengan benar. Periksa CDN!');
        }

        // ================== INIT FLATPICKR ==================
        function initFlatpickr() {
            if (!hasFlatpickr) return;
            try {
                flatpickr("#tanggal_awal", {
                    dateFormat: "Y-m-d",
                    allowInput: true
                });
                flatpickr("#tanggal_akhir", {
                    dateFormat: "Y-m-d",
                    allowInput: true
                });
                console.log('✅ Flatpickr aktif');
            } catch (err) {
                console.warn('❌ Flatpickr gagal diinisialisasi', err);
            }
        }

        // ================== INIT SELECT2 ==================
        function tryInitSelect2() {
            if (typeof $.fn.select2 === 'undefined') {
                console.warn('⏳ Menunggu Select2 termuat...');
                setTimeout(tryInitSelect2, 300);
                return;
            }
            select2Ready = true;
            try {
                $('#outlet').select2({
                    theme: 'bootstrap-5',
                    placeholder: '-- Pilih Outlet --',
                    allowClear: true,
                    width: '100%'
                });
                console.log('✅ Select2 outlet aktif');
            } catch (err) {
                console.warn('❌ Gagal init Select2', err);
            }
        }

        // ================== EXPORT PDF ==================
        async function exportDashboardToPDF() {
            const dashboard = document.getElementById('dashboardWrapper');
            if (!dashboard) return alert('Area dashboard tidak ditemukan.');

            const scale = 2;
            const canvas = await html2canvas(dashboard, {
                scale,
                useCORS: true,
                logging: false
            });
            const imgData = canvas.toDataURL('image/png');
            const {
                jsPDF
            } = window.jspdf;
            const pdf = new jsPDF('p', 'pt', 'a4');

            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const ratio = canvas.width / pdfWidth;
            const imgHeightPt = canvas.height / ratio;

            let pos = 0;
            let remain = imgHeightPt;
            while (remain > 0) {
                pdf.addImage(imgData, 'PNG', 0, -pos, pdfWidth, imgHeightPt);
                remain -= pdfHeight;
                pos += pdfHeight;
                if (remain > 0) pdf.addPage();
            }

            pdf.save('dashboard.pdf');
        }

        // ================== INIT CHARTS ==================
        const chartInstances = [];

        function safeCreateChart(ctxElem, config) {
            if (!ctxElem) return null;
            try {
                const ctx = ctxElem.getContext('2d');
                const chart = new Chart(ctx, config);
                chartInstances.push(chart);
                return chart;
            } catch (e) {
                console.warn('chart create failed', e);
                return null;
            }
        }

        function resizeAllCharts() {
            chartInstances.forEach(c => {
                try {
                    c.resize();
                } catch {}
            });
        }

        function initCharts() {
            if (!hasChart) return;

            @if ($filterApplied && count($chartOmset ?? []) > 0)
                // Omset Chart
                safeCreateChart(document.getElementById('sales-chart'), {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Omset',
                            data: @json($chartOmset),
                            backgroundColor: '#0d6efd',
                            borderRadius: 4,
                            maxBarThickness: 40
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'start',
                                rotation: -90,
                                color: 'white',
                                font: {
                                    size: 10,
                                    weight: '600'
                                },
                                formatter: val => 'Rp ' + Number(val).toLocaleString()
                            }
                        },
                        scales: {
                            x: {
                                ticks: {
                                    color: '#444',
                                    font: {
                                        size: 10,
                                        weight: '600'
                                    }
                                },
                                grid: {
                                    drawOnChartArea: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: val => 'Rp ' + Number(val).toLocaleString()
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });

                // Customer Unit (CU)
                safeCreateChart(document.getElementById('visitors-chart'), {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Customer Unit',
                            data: @json($chartCU),
                            borderColor: '#0d6efd',
                            backgroundColor: ctx => {
                                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 180);
                                g.addColorStop(0, 'rgba(13,110,253,0.35)');
                                g.addColorStop(1, 'rgba(13,110,253,0)');
                                return g;
                            },
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#0d6efd',
                            pointBorderWidth: 2,
                            pointBorderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#0d6efd',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: ctx => `CU: ${ctx.formattedValue}`
                                }
                            },
                            datalabels: {
                                color: '#0d6efd',
                                anchor: 'end',
                                align: 'top',
                                font: {
                                    size: 9,
                                    weight: '600'
                                },
                                formatter: val => val
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6c757d',
                                    font: {
                                        size: 10,
                                        weight: '600'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                },
                                ticks: {
                                    color: '#6c757d',
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });

                // Hourly Chart
                safeCreateChart(document.getElementById('hourly-sales-chart'), {
                    type: 'bar',
                    data: {
                        labels: @json(array_map(fn($h) => sprintf('%02d:00', $h), array_keys($chartHourlyFull))),
                        datasets: [{
                            label: 'Transaksi per Jam',
                            data: @json(array_values($chartHourlyFull)),
                            borderRadius: 8,
                            backgroundColor: ctx => {
                                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart
                                    .height);
                                g.addColorStop(0, 'rgba(25,135,84,0.9)');
                                g.addColorStop(1, 'rgba(25,135,84,0.3)');
                                return g;
                            },
                            barPercentage: 0.6,
                            categoryPercentage: 0.7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#198754',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: ctx => `Transaksi: ${ctx.formattedValue}`
                                }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#198754',
                                font: {
                                    weight: '700',
                                    size: 10
                                },
                                formatter: val => val > 0 ? val : ''
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#6c757d',
                                    font: {
                                        size: 10,
                                        weight: '600'
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                },
                                ticks: {
                                    color: '#6c757d',
                                    font: {
                                        size: 10
                                    },
                                    stepSize: 5
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            @endif
        }

        // ================== FILTERS & BUTTON ==================
        function initCategoryFilter() {
            if (!hasJQuery) return;
            $('#filterKategori').on('change', function() {
                const val = ($(this).val() || '').toUpperCase();
                $('table tbody tr').each(function() {
                    const text = $(this).find('td:first').text().toUpperCase();
                    if (!val) $(this).show();
                    else if (val === 'PAKET') $(this).toggle(text.includes('PAKET'));
                    else if (val === 'ALACARTE') $(this).toggle(!text.includes('PAKET') && !text
                        .includes('DISKON'));
                    else if (val === 'DISKON') $(this).toggle(text.includes('DISKON'));
                });
            });
        }

        function initFilterValidation() {
            if (!hasJQuery) return;
            $('#filterForm').on('submit', function(e) {
                const start = $('#tanggal_awal').val();
                const end = $('#tanggal_akhir').val();
                if (!start || !end) {
                    e.preventDefault();
                    alert('Harap isi Tanggal Awal dan Akhir!');
                    return false;
                }
            });
        }

        function initExportButton() {
            const btn = document.getElementById('exportPDF');
            if (!btn) return;
            btn.addEventListener('click', async () => {
                try {
                    await exportDashboardToPDF();
                } catch (err) {
                    console.error('Export PDF gagal', err);
                    alert('Gagal mengekspor PDF.');
                }
            });
        }

        function attachResizeHandler() {
            let timer = null;
            window.addEventListener('resize', () => {
                clearTimeout(timer);
                timer = setTimeout(resizeAllCharts, 200);
            });
        }

        // ================== INIT ALL ==================
        function initAll() {
            initFlatpickr();
            tryInitSelect2();
            initCharts();
            initCategoryFilter();
            initFilterValidation();
            initExportButton();
            attachResizeHandler();
        }

        document.readyState === 'loading' ?
            document.addEventListener('DOMContentLoaded', initAll) :
            initAll();

    })();
</script>
@include('Temp.Investor.footer')
@include('Temp.Investor.header')
@include('Temp.Investor.partials.sidebar')

<style>
    :root {
        --mi-primary: #3b82f6; /* Blue */
        --mi-primary-light: #eff6ff;
        --mi-dark: #0f172a;
        --mi-gray: #64748b;
        --mi-light: #f8fafc;
        --mi-accent: #8b5cf6; /* Purple */
    }
    .menu-page {
        padding: 30px;
        background: #f1f5f9;
        min-height: 100vh;
        font-family: 'Inter', system-ui, sans-serif;
    }
    .menu-hero {
        background: linear-gradient(135deg, var(--mi-dark), #1e293b);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 25px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.3);
        position: relative;
        overflow: hidden;
    }
    .menu-hero::after {
        content: '';
        position: absolute;
        top: -50%; right: -10%;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(59,130,246,0.3) 0%, transparent 70%);
        border-radius: 50%;
    }
    .menu-kicker {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: 800;
        background: rgba(255,255,255,0.1);
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        margin-bottom: 15px;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .menu-title {
        font-size: 32px;
        font-weight: 900;
        margin: 0 0 10px 0;
        background: linear-gradient(to right, #60a5fa, #c084fc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }
    .kpi-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        border: 1px solid rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .kpi-card small {
        color: var(--mi-gray);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }
    .kpi-card h2 {
        font-size: 28px;
        font-weight: 900;
        color: var(--mi-dark);
        margin: 0;
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        border: 1px solid rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .chart-card h3 {
        margin: 0 0 20px 0;
        color: var(--mi-dark);
        font-weight: 800;
        font-size: 18px;
    }

    .mi-table-container {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        border: 1px solid rgba(0,0,0,0.05);
        overflow-x: auto;
    }
    .mi-table {
        width: 100%;
        border-collapse: collapse;
    }
    .mi-table th {
        background: var(--mi-light);
        color: var(--mi-gray);
        font-weight: 700;
        font-size: 12px;
        padding: 12px 15px;
        text-align: left;
        text-transform: uppercase;
        border-bottom: 2px solid #e2e8f0;
    }
    .mi-table td {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: var(--mi-dark);
    }
    .mi-table tbody tr:hover {
        background: #f8fafc;
    }
    .text-money { color: var(--mi-primary); font-family: monospace; font-weight: 700; font-size: 15px;}
    .badge {
        background: var(--mi-primary-light);
        color: var(--mi-primary);
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }

</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="menu-page">
    <section class="menu-hero">
        <div class="menu-kicker"><i class="bi bi-cpu"></i> Advanced Analytics</div>
        <h1 class="menu-title">Market Intelligence</h1>
        <p class="menu-subtitle" style="font-size: 15px; opacity: 0.8; margin: 0; max-width: 600px; line-height: 1.6;">Pemantauan mendalam terhadap *Sales Est Value*, *Basket Size*, dan perbandingan omzet kompetitor di tingkat nasional maupun provinsi.</p>
    </section>

    <!-- KPI Section -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <small>Total National Sales Estimate</small>
            <h2>Rp {{ number_format($kpi['total_sales'], 0, ',', '.') }}</h2>
        </div>
        <div class="kpi-card">
            <small>National Avg Basket Size</small>
            <h2>Rp {{ number_format($kpi['avg_basket'], 0, ',', '.') }}</h2>
        </div>
        <div class="kpi-card">
            <small>Total Brands Tracked</small>
            <h2 style="color: var(--mi-primary);">{{ number_format($kpi['total_brands'], 0, ',', '.') }} Brands</h2>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="chart-card">
        <h3>Top 10 Merek Berdasarkan Total Omzet (Nasional)</h3>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="topSalesChart"></canvas>
        </div>
    </div>

    <!-- Filter Bar -->
    <section class="filter-section" style="margin-bottom: 25px;">
        <div style="display: flex; gap: 15px; background: white; padding: 15px 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; align-items: center;">
            <div style="flex: 1;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--mi-gray); font-weight: 800; display: block; margin-bottom: 5px;">Filter Provinsi</label>
                <select id="filterProvinsi" style="width: 100%; padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif;">
                    <option value="">Semua Provinsi</option>
                    @foreach(array_unique(array_column($marketData, 'provinsi')) as $prov)
                        @if($prov !== '')
                            <option value="{{ strtolower($prov) }}">{{ $prov }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div style="flex: 2;">
                <label style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: var(--mi-gray); font-weight: 800; display: block; margin-bottom: 5px;">Cari Merek</label>
                <div style="position: relative;">
                    <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa;"></i>
                    <input type="text" id="filterBrand" placeholder="Ketik nama merek..." style="width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #e2e8f0; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif;">
                </div>
            </div>
        </div>
    </section>

    <!-- Table Section -->
    <div class="mi-table-container">
        <table class="mi-table">
            <thead>
                <tr>
                    <th>Provinsi</th>
                    <th>Brand</th>
                    <th style="text-align: center;">Cabang</th>
                    <th style="text-align: right;">Basket Size</th>
                    <th style="text-align: right;">Avg Sales / Outlet</th>
                    <th style="text-align: right;">Total Sales Est</th>
                </tr>
            </thead>
            <tbody id="marketTableBody">
                @foreach($marketData as $data)
                    <tr class="data-row" data-prov="{{ strtolower($data['provinsi']) }}" data-brand="{{ strtolower($data['brand']) }}">
                        <td>
                            @if($data['provinsi'] !== '')
                                <span class="badge">{{ $data['provinsi'] }}</span>
                            @else
                                <span class="badge" style="background: #e2e8f0; color: #64748b;">-</span>
                            @endif
                        </td>
                        <td style="font-weight: 700;">{{ $data['brand'] }}</td>
                        <td style="text-align: center; font-weight: 800;">{{ number_format($data['cabang'], 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #64748b;">Rp {{ number_format($data['basket_size'], 0, ',', '.') }}</td>
                        <td style="text-align: right; color: #64748b;">Rp {{ number_format($data['avg_sales_outlet'], 0, ',', '.') }}</td>
                        <td class="text-money">Rp {{ number_format($data['sales_est'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Setup Chart.js
    const topSalesData = @json($topSales);
    const labels = topSalesData.map(item => item.brand);
    const dataValues = topSalesData.map(item => item.sales);
    
    // Warnai GeprekinAja dengan warna merah, sisanya biru
    const backgroundColors = labels.map(label => label.toLowerCase().includes('geprekin') ? '#dc2626' : '#3b82f6');

    const ctx = document.getElementById('topSalesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Estimasi Omzet Nasional (Rp)',
                data: dataValues,
                backgroundColor: backgroundColors,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.raw;
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            if (value >= 1e9) return 'Rp ' + (value / 1e9) + ' Miliar';
                            if (value >= 1e6) return 'Rp ' + (value / 1e6) + ' Juta';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // 2. Setup Table Filters
    const filterProvinsi = document.getElementById('filterProvinsi');
    const filterBrand = document.getElementById('filterBrand');

    function applyFilters() {
        const queryProv = filterProvinsi.value;
        const queryBrand = filterBrand.value.toLowerCase().trim();

        document.querySelectorAll('#marketTableBody .data-row').forEach(row => {
            const provData = row.getAttribute('data-prov');
            const brandData = row.getAttribute('data-brand');
            
            const matchProv = queryProv === '' || provData === queryProv;
            const matchBrand = queryBrand === '' || brandData.includes(queryBrand);

            if (matchProv && matchBrand) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }

    filterProvinsi.addEventListener('change', applyFilters);
    filterBrand.addEventListener('input', applyFilters);
});
</script>

@include('Temp.Investor.footer')

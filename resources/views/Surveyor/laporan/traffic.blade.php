@section('title', 'Traffic Analytics')
@section('breadcrumb', 'Laporan / Traffic Analytics')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

<div class="worksheet-page">
    <div class="worksheet-hero">
        <div>
            <div class="worksheet-kicker">
                <i class="bi bi-bar-chart-line"></i>
                Laporan & Analitik
            </div>
            <h1 class="worksheet-title">Traffic Analytics</h1>
            <p class="worksheet-subtitle">Perbandingan volume traffic antar kandidat lokasi dan analisis waktu ramai.</p>
        </div>
    </div>

    <div class="worksheet-container">
        <div class="row g-4">
            <div class="col-md-8">
                <div class="excel-box">
                    <div class="excel-box-header">
                        <div class="fw-bold"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Top 10 Lokasi dengan Traffic Tertinggi</div>
                    </div>
                    <div class="excel-box-body">
                        <canvas id="trafficBarChart" height="100"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="excel-box">
                    <div class="excel-box-header">
                        <div class="fw-bold"><i class="bi bi-pie-chart text-success me-2"></i>Distribusi Waktu Ramai</div>
                    </div>
                    <div class="excel-box-body">
                        <canvas id="timePieChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const top10Data = @json($top10);
        const timeData = @json($timeData);

        const labels = top10Data.map(item => item.lokasi);
        const motorData = top10Data.map(item => item.total_motor);
        const pejalanData = top10Data.map(item => item.total_pejalan);

        // Bar Chart
        const ctxBar = document.getElementById('trafficBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sepeda Motor',
                        data: motorData,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pejalan Kaki',
                        data: pejalanData,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Pie Chart
        const ctxPie = document.getElementById('timePieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Pagi', 'Siang', 'Sore'],
                datasets: [{
                    data: [timeData.pagi, timeData.siang, timeData.sore],
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });
    });
</script>
@endpush

@include('Surveyor.layouts.footer')

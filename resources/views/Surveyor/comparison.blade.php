@section('title', 'Comparison Worksheet Site Score')
@section('breadcrumb', 'Site Score Outlet / Comparison')

@include('Surveyor.layouts.header')

@php $scores = $scores ?? collect(); @endphp

<style>
    :root {
        --compare-bg: #f6f8fb;
        --compare-card: #ffffff;
        --compare-text: #172033;
        --compare-muted: #6b7280;
        --compare-border: #e5e7eb;
        --compare-primary: #2563eb;
        --compare-primary-soft: #eff6ff;
        --compare-green: #16a34a;
        --compare-yellow: #ca8a04;
        --compare-red: #dc2626;
        --compare-shadow: 0 18px 45px rgba(15, 23, 42, .08);
    }

    .comparison-page {
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, .10), transparent 34rem),
            linear-gradient(180deg, #fff 0%, var(--compare-bg) 34%);
        min-height: calc(100vh - 90px);
        padding: 24px;
        color: var(--compare-text);
    }

    .comparison-shell {
        max-width: 1180px;
        margin: 0 auto;
    }

    .comparison-hero {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .comparison-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        background: var(--compare-primary-soft);
        color: var(--compare-primary);
        font-weight: 700;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .comparison-hero h1 {
        font-size: clamp(26px, 3vw, 40px);
        font-weight: 800;
        letter-spacing: -.04em;
        margin: 0 0 8px;
    }

    .comparison-hero p {
        margin: 0;
        color: var(--compare-muted);
        max-width: 620px;
    }

    .comparison-nav {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .comparison-tab {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 14px;
        border: 1px solid var(--compare-border);
        color: var(--compare-text);
        background: #fff;
        text-decoration: none;
        font-weight: 700;
        box-shadow: 0 8px 22px rgba(15, 23, 42, .05);
    }

    .comparison-tab.active {
        background: var(--compare-primary);
        color: #fff;
        border-color: var(--compare-primary);
    }

    .comparison-card {
        background: rgba(255, 255, 255, .88);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(229, 231, 235, .9);
        border-radius: 28px;
        box-shadow: var(--compare-shadow);
    }

    .selector-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        padding: 18px;
        margin-bottom: 18px;
    }

    .selector-box {
        border: 1px solid var(--compare-border);
        border-radius: 22px;
        padding: 16px;
        background: #fff;
    }

    .selector-box label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        color: var(--compare-muted);
        font-weight: 800;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .location-select {
        width: 100%;
        border: 1px solid var(--compare-border);
        border-radius: 16px;
        padding: 13px 14px;
        font-weight: 700;
        color: var(--compare-text);
        background: #f9fafb;
        outline: none;
    }

    .location-select:focus {
        border-color: var(--compare-primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        background: #fff;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }

    .location-card {
        overflow: hidden;
    }

    .location-card-header {
        padding: 20px;
        border-bottom: 1px solid var(--compare-border);
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
    }

    .location-card-header h2 {
        margin: 0 0 6px;
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .location-card-header p {
        margin: 0;
        color: var(--compare-muted);
        font-size: 13px;
    }

    .score-ring {
        min-width: 86px;
        min-height: 86px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: conic-gradient(var(--compare-primary) 0deg, var(--compare-primary) var(--score-deg, 0deg), #e5e7eb var(--score-deg, 0deg));
        position: relative;
    }

    .score-ring::after {
        content: "";
        position: absolute;
        inset: 8px;
        border-radius: inherit;
        background: #fff;
    }

    .score-ring span {
        position: relative;
        z-index: 1;
        font-size: 19px;
        font-weight: 900;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        padding: 16px;
    }

    .metric {
        border: 1px solid var(--compare-border);
        border-radius: 18px;
        padding: 14px;
        background: #fbfdff;
    }

    .metric small {
        display: block;
        color: var(--compare-muted);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .metric strong {
        font-size: 22px;
        font-weight: 900;
        letter-spacing: -.02em;
    }

    .status-pill,
    .winner-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 999px;
        padding: 7px 11px;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
    }

    .status-approved { background: #dcfce7; color: var(--compare-green); }
    .status-consideration { background: #fef9c3; color: var(--compare-yellow); }
    .status-rejected { background: #fee2e2; color: var(--compare-red); }

    .winner-board {
        padding: 18px;
    }

    .winner-title {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 14px;
    }

    .winner-title h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 900;
    }

    .winner-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
    }

    .winner-table th {
        color: var(--compare-muted);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .08em;
        padding: 0 12px 4px;
    }

    .winner-table td {
        background: #f9fafb;
        padding: 14px 12px;
        border-top: 1px solid var(--compare-border);
        border-bottom: 1px solid var(--compare-border);
        font-weight: 700;
    }

    .winner-table td:first-child {
        border-left: 1px solid var(--compare-border);
        border-radius: 16px 0 0 16px;
        color: var(--compare-muted);
    }

    .winner-table td:last-child {
        border-right: 1px solid var(--compare-border);
        border-radius: 0 16px 16px 0;
    }

    .winner-pill {
        background: var(--compare-primary-soft);
        color: var(--compare-primary);
    }

    .empty-state {
        padding: 28px;
        text-align: center;
        color: var(--compare-muted);
        border: 1px dashed var(--compare-border);
        border-radius: 22px;
        background: #fff;
        margin: 18px;
    }

    @media (max-width: 900px) {
        .comparison-page { padding: 16px; }
        .comparison-hero,
        .selector-grid,
        .summary-grid {
            grid-template-columns: 1fr;
            display: grid;
        }

        .comparison-nav {
            justify-content: flex-start;
        }
    }
</style>

<div class="comparison-page">
    <div class="comparison-shell">
        <div class="comparison-hero">
            <div>
                <div class="comparison-eyebrow">
                    <i class="bi bi-columns-gap"></i>
                    Site Score Comparison
                </div>
                <h1>Bandingkan Performa Lokasi</h1>
                <p>Pilih dua lokasi untuk melihat skor akhir, trafik, faktor penambah, faktor pengurang, dan pemenang tiap parameter secara cepat.</p>
            </div>

            <div class="comparison-nav">
                <span class="comparison-tab active">
                    <i class="bi bi-columns-gap"></i>
                    Comparison
                </span>
                <a href="{{ route('investor.surveyor.site-score.ranking') }}" class="comparison-tab">
                    <i class="bi bi-trophy"></i>
                    Ranking
                </a>
            </div>
        </div>

        <div class="comparison-card selector-grid">
            <div class="selector-box">
                <label for="locationA">
                    <span>Lokasi A</span>
                    <i class="bi bi-geo-alt"></i>
                </label>
                <select id="locationA" class="location-select">
                    <option value="">Pilih lokasi A</option>
                    @foreach($scores as $row)
                        <option value="{{ $row->id }}"
                            data-lokasi="{{ $row->lokasi }}"
                            data-score="{{ $row->final_percent ?? 0 }}"
                            data-motor="{{ $row->total_motor ?? 0 }}"
                            data-pejalan="{{ $row->total_pejalan ?? 0 }}"
                            data-plus="{{ $row->total_penambah ?? 0 }}"
                            data-minus="{{ $row->total_pengurang ?? 0 }}"
                            data-status="{{ $row->rekomendasi ?? 'REJECTED' }}"
                            data-lat="{{ $row->latitude ?? '' }}"
                            data-lng="{{ $row->longitude ?? '' }}"
                            data-komp-geprek="{{ $row->kompetitor_geprek ?? 0 }}"
                            data-komp-lokal="{{ $row->kompetitor_lokal ?? 0 }}"
                            data-harga="{{ $row->harga_kompetitor ?? 0 }}"
                            data-sekolah="{{ $row->sekolah ?? 0 }}"
                            data-market="{{ $row->market ?? 0 }}"
                            data-kantor="{{ $row->perkantoran ?? 0 }}"
                            data-sehat="{{ $row->kesehatan ?? 0 }}">
                            {{ $row->lokasi }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="selector-box">
                <label for="locationB">
                    <span>Lokasi B</span>
                    <i class="bi bi-geo-alt-fill"></i>
                </label>
                <select id="locationB" class="location-select">
                    <option value="">Pilih lokasi B</option>
                    @foreach($scores as $row)
                        <option value="{{ $row->id }}"
                            data-lokasi="{{ $row->lokasi }}"
                            data-score="{{ $row->final_percent ?? 0 }}"
                            data-motor="{{ $row->total_motor ?? 0 }}"
                            data-pejalan="{{ $row->total_pejalan ?? 0 }}"
                            data-plus="{{ $row->total_penambah ?? 0 }}"
                            data-minus="{{ $row->total_pengurang ?? 0 }}"
                            data-status="{{ $row->rekomendasi ?? 'REJECTED' }}"
                            data-lat="{{ $row->latitude ?? '' }}"
                            data-lng="{{ $row->longitude ?? '' }}"
                            data-komp-geprek="{{ $row->kompetitor_geprek ?? 0 }}"
                            data-komp-lokal="{{ $row->kompetitor_lokal ?? 0 }}"
                            data-harga="{{ $row->harga_kompetitor ?? 0 }}"
                            data-sekolah="{{ $row->sekolah ?? 0 }}"
                            data-market="{{ $row->market ?? 0 }}"
                            data-kantor="{{ $row->perkantoran ?? 0 }}"
                            data-sehat="{{ $row->kesehatan ?? 0 }}">
                            {{ $row->lokasi }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="emptyState" class="empty-state">
            <i class="bi bi-arrow-left-right"></i>
            Pilih Lokasi A dan Lokasi B untuk mulai membandingkan.
        </div>

        <div id="comparisonResult" style="display:none;">
            <div class="summary-grid">
                <div class="comparison-card location-card">
                    <div class="location-card-header">
                        <div>
                            <h2 id="nameA">Lokasi A</h2>
                            <p id="statusA">-</p>
                        </div>
                        <div class="score-ring" id="ringA" style="--score-deg:0deg;">
                            <span id="scoreA">0.00</span>
                        </div>
                    </div>

                    <div class="metric-grid">
                        <div class="metric">
                            <small>Total Motor</small>
                            <strong id="motorA">0</strong>
                        </div>
                        <div class="metric">
                            <small>Total Pejalan</small>
                            <strong id="pejalanA">0</strong>
                        </div>
                        <div class="metric">
                            <small>Total Penambah</small>
                            <strong id="plusA">0.0000</strong>
                        </div>
                        <div class="metric">
                            <small>Total Pengurang</small>
                            <strong id="minusA">0.0000</strong>
                        </div>
                    </div>
                    <div style="padding: 0 16px 16px;">
                        <div id="mapA" style="height: 220px; width: 100%; border-radius: 18px; border: 1px solid var(--compare-border); background: #f3f4f6;"></div>
                    </div>
                </div>

                <div class="comparison-card location-card">
                    <div class="location-card-header">
                        <div>
                            <h2 id="nameB">Lokasi B</h2>
                            <p id="statusB">-</p>
                        </div>
                        <div class="score-ring" id="ringB" style="--score-deg:0deg;">
                            <span id="scoreB">0.00</span>
                        </div>
                    </div>

                    <div class="metric-grid">
                        <div class="metric">
                            <small>Total Motor</small>
                            <strong id="motorB">0</strong>
                        </div>
                        <div class="metric">
                            <small>Total Pejalan</small>
                            <strong id="pejalanB">0</strong>
                        </div>
                        <div class="metric">
                            <small>Total Penambah</small>
                            <strong id="plusB">0.0000</strong>
                        </div>
                        <div class="metric">
                            <small>Total Pengurang</small>
                            <strong id="minusB">0.0000</strong>
                        </div>
                    </div>
                    <div style="padding: 0 16px 16px;">
                        <div id="mapB" style="height: 220px; width: 100%; border-radius: 18px; border: 1px solid var(--compare-border); background: #f3f4f6;"></div>
                    </div>
                </div>
            </div>

            <div class="comparison-card" style="padding: 20px; margin-bottom: 18px; display: flex; justify-content: center; align-items: center;">
                <div style="width: 100%; max-width: 500px; max-height: 400px; position: relative;">
                    <canvas id="comparisonChart"></canvas>
                </div>
            </div>

            <div class="comparison-card winner-board">
                <div class="winner-title">
                    <h3>Hasil Perbandingan</h3>
                    <span class="winner-pill" id="overallWinner">-</span>
                </div>

                <div class="table-responsive">
                    <table class="winner-table">
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Lokasi A</th>
                                <th>Lokasi B</th>
                                <th>Winner</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Final Score</td>
                                <td id="scoreATable">0.00</td>
                                <td id="scoreBTable">0.00</td>
                                <td class="text-center"><span class="winner-pill" id="winnerScore">-</span></td>
                            </tr>
                            <tr>
                                <td>Total Motor</td>
                                <td id="motorATable">0</td>
                                <td id="motorBTable">0</td>
                                <td class="text-center"><span class="winner-pill" id="winnerMotor">-</span></td>
                            </tr>
                            <tr>
                                <td>Total Pejalan</td>
                                <td id="pejalanATable">0</td>
                                <td id="pejalanBTable">0</td>
                                <td class="text-center"><span class="winner-pill" id="winnerPejalan">-</span></td>
                            </tr>
                            <tr>
                                <td>Fasilitas Publik</td>
                                <td id="fasilitasATable">0</td>
                                <td id="fasilitasBTable">0</td>
                                <td class="text-center"><span class="winner-pill" id="winnerFasilitas">-</span></td>
                            </tr>
                            <tr>
                                <td>Total Kompetitor</td>
                                <td id="kompetitorATable">0</td>
                                <td id="kompetitorBTable">0</td>
                                <td class="text-center"><span class="winner-pill" id="winnerKompetitor">-</span></td>
                            </tr>
                            <tr>
                                <td>Harga Kompetitor</td>
                                <td id="hargaATable">0</td>
                                <td id="hargaBTable">0</td>
                                <td class="text-center"><span class="winner-pill" id="winnerHarga">-</span></td>
                            </tr>
                            <tr>
                                <td>Faktor Penambah</td>
                                <td id="plusATable">0.0000</td>
                                <td id="plusBTable">0.0000</td>
                                <td class="text-center"><span class="winner-pill" id="winnerPlus">-</span></td>
                            </tr>
                            <tr>
                                <td>Faktor Pengurang</td>
                                <td id="minusATable">0.0000</td>
                                <td id="minusBTable">0.0000</td>
                                <td class="text-center"><span class="winner-pill" id="winnerMinus">-</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initComparisonMaps" async defer></script>
<script>
    let mapA, mapB;
    let markerA, markerB;
    let radarChart;

    function initComparisonMaps() {
        const defaultCenter = { lat: -7.25, lng: 112.75 };
        mapA = new google.maps.Map(document.getElementById("mapA"), { zoom: 15, center: defaultCenter, mapTypeId: "roadmap", disableDefaultUI: true, gestureHandling: 'cooperative' });
        mapB = new google.maps.Map(document.getElementById("mapB"), { zoom: 15, center: defaultCenter, mapTypeId: "roadmap", disableDefaultUI: true, gestureHandling: 'cooperative' });
        
        markerA = new google.maps.Marker({ map: mapA, icon: { url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png", scaledSize: new google.maps.Size(32, 32) }});
        markerB = new google.maps.Marker({ map: mapB, icon: { url: "http://maps.google.com/mapfiles/ms/icons/purple-dot.png", scaledSize: new google.maps.Size(32, 32) }});
    }

    function statusCell(status) {
        const normalized = (status || 'REJECTED').toUpperCase();
        const className = normalized === 'APPROVED'
            ? 'status-approved'
            : normalized === 'CONSIDERATION'
                ? 'status-consideration'
                : 'status-rejected';

        return `<span class="status-pill ${className}">${normalized}</span>`;
    }

    function getSelectedData(selector) {
        const option = document.querySelector(selector).selectedOptions[0];

        if (!option || !option.value) {
            return null;
        }

        return {
            lokasi: option.dataset.lokasi || '-',
            score: parseFloat(option.dataset.score || 0),
            motor: parseInt(option.dataset.motor || 0),
            pejalan: parseInt(option.dataset.pejalan || 0),
            plus: parseFloat(option.dataset.plus || 0),
            minus: parseFloat(option.dataset.minus || 0),
            status: option.dataset.status || 'REJECTED',
            lat: parseFloat(option.dataset.lat || 0),
            lng: parseFloat(option.dataset.lng || 0),
            kompetitor: parseInt(option.dataset.kompGeprek || 0) + parseInt(option.dataset.kompLokal || 0),
            fasilitas: parseInt(option.dataset.sekolah || 0) + parseInt(option.dataset.market || 0) + parseInt(option.dataset.kantor || 0) + parseInt(option.dataset.sehat || 0),
            harga: parseFloat(option.dataset.harga || 0)
        };
    }

    function formatNumber(value) {
        return Number(value || 0).toLocaleString('id-ID');
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) element.innerText = value;
    }

    function setHtml(id, value) {
        const element = document.getElementById(id);
        if (element) element.innerHTML = value;
    }

    function winnerLabel(aValue, bValue, lowerIsBetter = false) {
        if (aValue === bValue) return 'Seri';
        if (lowerIsBetter) return aValue < bValue ? 'Lokasi A' : 'Lokasi B';
        return aValue > bValue ? 'Lokasi A' : 'Lokasi B';
    }

    function highlightWinnerColumn(aValue, bValue, lowerIsBetter = false) {
        if (aValue === bValue) return 0;
        if (lowerIsBetter) return aValue < bValue ? 1 : 2;
        return aValue > bValue ? 1 : 2;
    }

    function fillLocation(data, suffix) {
        setText(`name${suffix}`, data.lokasi);
        setHtml(`status${suffix}`, statusCell(data.status));

        setText(`score${suffix}`, data.score.toFixed(2));
        setText(`motor${suffix}`, formatNumber(data.motor));
        setText(`pejalan${suffix}`, formatNumber(data.pejalan));
        setText(`plus${suffix}`, data.plus.toFixed(4));
        setText(`minus${suffix}`, data.minus.toFixed(4));

        setText(`score${suffix}Table`, data.score.toFixed(2));
        setText(`motor${suffix}Table`, formatNumber(data.motor));
        setText(`pejalan${suffix}Table`, formatNumber(data.pejalan));
        setText(`fasilitas${suffix}Table`, formatNumber(data.fasilitas));
        setText(`kompetitor${suffix}Table`, formatNumber(data.kompetitor));
        setText(`harga${suffix}Table`, 'Rp ' + formatNumber(data.harga));
        setText(`plus${suffix}Table`, data.plus.toFixed(4));
        setText(`minus${suffix}Table`, data.minus.toFixed(4));

        const ring = document.getElementById(`ring${suffix}`);
        if (ring) {
            const degree = Math.max(0, Math.min(100, data.score)) * 3.6;
            ring.style.setProperty('--score-deg', `${degree}deg`);
        }

        if (suffix === 'A' && mapA) {
            const pos = { lat: data.lat, lng: data.lng };
            markerA.setPosition(pos);
            mapA.panTo(pos);
        } else if (suffix === 'B' && mapB) {
            const pos = { lat: data.lat, lng: data.lng };
            markerB.setPosition(pos);
            mapB.panTo(pos);
        }
    }

    function drawChart(a, b) {
        const ctx = document.getElementById('comparisonChart').getContext('2d');
        
        // Normalize values for radar chart (relative scale)
        const maxMotor = Math.max(a.motor, b.motor, 1);
        const maxPejalan = Math.max(a.pejalan, b.pejalan, 1);
        const maxFasilitas = Math.max(a.fasilitas, b.fasilitas, 1);
        // For inverse metrics (lower is better), we invert the normalization
        const maxKomp = Math.max(a.kompetitor, b.kompetitor, 1);
        const maxMinus = Math.max(a.minus, b.minus, 0.001);

        const aData = [
            (a.motor / maxMotor) * 100,
            (a.pejalan / maxPejalan) * 100,
            (a.fasilitas / maxFasilitas) * 100,
            100 - ((a.kompetitor / maxKomp) * 100),
            100 - ((a.minus / maxMinus) * 100)
        ];

        const bData = [
            (b.motor / maxMotor) * 100,
            (b.pejalan / maxPejalan) * 100,
            (b.fasilitas / maxFasilitas) * 100,
            100 - ((b.kompetitor / maxKomp) * 100),
            100 - ((b.minus / maxMinus) * 100)
        ];

        if (radarChart) {
            radarChart.destroy();
        }

        radarChart = new Chart(ctx, {
            type: 'radar',
            data: {
                labels: ['Motor Trafik', 'Pejalan Trafik', 'Fasilitas', 'Minim Kompetitor', 'Minim Pengurang'],
                datasets: [
                    {
                        label: 'Lokasi A',
                        data: aData,
                        backgroundColor: 'rgba(37, 99, 235, 0.2)',
                        borderColor: 'rgba(37, 99, 235, 1)',
                        pointBackgroundColor: 'rgba(37, 99, 235, 1)',
                    },
                    {
                        label: 'Lokasi B',
                        data: bData,
                        backgroundColor: 'rgba(147, 51, 234, 0.2)',
                        borderColor: 'rgba(147, 51, 234, 1)',
                        pointBackgroundColor: 'rgba(147, 51, 234, 1)',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { display: true },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: { display: false }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ' Score Relatif';
                            }
                        }
                    }
                }
            }
        });
    }

    function applyWinnerHighlight(rowId, winnerIndex) {
        // winnerIndex: 0 = tie, 1 = A, 2 = B
        const tr = document.getElementById(rowId).closest('tr');
        const tdA = tr.children[1];
        const tdB = tr.children[2];
        
        tdA.style.backgroundColor = '';
        tdB.style.backgroundColor = '';
        
        if (winnerIndex === 1) {
            tdA.style.backgroundColor = '#dcfce7'; // green-100
        } else if (winnerIndex === 2) {
            tdB.style.backgroundColor = '#dcfce7'; // green-100
        }
    }

    function refresh() {
        const a = getSelectedData('#locationA');
        const b = getSelectedData('#locationB');
        const emptyState = document.getElementById('emptyState');
        const result = document.getElementById('comparisonResult');

        if (!a || !b) {
            emptyState.style.display = 'block';
            result.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        result.style.display = 'block';

        fillLocation(a, 'A');
        fillLocation(b, 'B');

        const winnerScore = winnerLabel(a.score, b.score);
        const winnerMotor = winnerLabel(a.motor, b.motor);
        const winnerPejalan = winnerLabel(a.pejalan, b.pejalan);
        const winnerFasilitas = winnerLabel(a.fasilitas, b.fasilitas);
        const winnerKompetitor = winnerLabel(a.kompetitor, b.kompetitor, true);
        const winnerHarga = winnerLabel(a.harga, b.harga, true);
        const winnerPlus = winnerLabel(a.plus, b.plus);
        const winnerMinus = winnerLabel(a.minus, b.minus, true);

        setText('winnerScore', winnerScore);
        setText('winnerMotor', winnerMotor);
        setText('winnerPejalan', winnerPejalan);
        setText('winnerFasilitas', winnerFasilitas);
        setText('winnerKompetitor', winnerKompetitor);
        setText('winnerHarga', winnerHarga);
        setText('winnerPlus', winnerPlus);
        setText('winnerMinus', winnerMinus);
        setText('overallWinner', `Overall: ${winnerScore}`);

        applyWinnerHighlight('winnerScore', highlightWinnerColumn(a.score, b.score));
        applyWinnerHighlight('winnerMotor', highlightWinnerColumn(a.motor, b.motor));
        applyWinnerHighlight('winnerPejalan', highlightWinnerColumn(a.pejalan, b.pejalan));
        applyWinnerHighlight('winnerFasilitas', highlightWinnerColumn(a.fasilitas, b.fasilitas));
        applyWinnerHighlight('winnerKompetitor', highlightWinnerColumn(a.kompetitor, b.kompetitor, true));
        applyWinnerHighlight('winnerHarga', highlightWinnerColumn(a.harga, b.harga, true));
        applyWinnerHighlight('winnerPlus', highlightWinnerColumn(a.plus, b.plus));
        applyWinnerHighlight('winnerMinus', highlightWinnerColumn(a.minus, b.minus, true));

        drawChart(a, b);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('locationA').addEventListener('change', refresh);
        document.getElementById('locationB').addEventListener('change', refresh);
        refresh();
    });
</script>
@endpush

@include('Surveyor.layouts.footer')

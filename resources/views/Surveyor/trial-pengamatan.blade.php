@section('title', 'Trial Sitescore Pengamatan')
@section('breadcrumb', 'Pengamatan / Trial Worksheet')

@include('Surveyor.layouts.header')

<style>
    :root {
        --trial-bg: #f4f7fb;
        --trial-card: #ffffff;
        --trial-text: #111827;
        --trial-muted: #64748b;
        --trial-border: #e5e7eb;
        --trial-primary: #2563eb;
        --trial-primary-soft: #eff6ff;
        --trial-green: #16a34a;
        --trial-green-soft: #dcfce7;
        --trial-yellow: #ca8a04;
        --trial-yellow-soft: #fef9c3;
        --trial-shadow: 0 12px 32px rgba(15, 23, 42, .07);
    }

    .trial-page {
        min-height: calc(100vh - 70px);
        padding: 22px 26px 32px;
        background: var(--trial-bg);
        color: var(--trial-text);
    }

    .trial-shell {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }

    .trial-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 16px;
        padding: 18px 20px;
        border: 1px solid var(--trial-border);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .trial-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        margin-bottom: 8px;
        border-radius: 999px;
        background: var(--trial-primary-soft);
        color: var(--trial-primary);
        font-size: 12px;
        font-weight: 900;
    }

    .trial-hero h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: -.03em;
    }

    .trial-hero p {
        margin: 7px 0 0;
        max-width: 820px;
        color: var(--trial-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .trial-grid {
        display: grid;
        grid-template-columns: 1.5fr .9fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    .trial-card {
        border: 1px solid var(--trial-border);
        border-radius: 22px;
        background: var(--trial-card);
        box-shadow: var(--trial-shadow);
        overflow: hidden;
    }

    .trial-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--trial-border);
        background: #fff;
    }

    .trial-card-header i {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--trial-primary-soft);
        color: var(--trial-primary);
    }

    .trial-card-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 900;
    }

    .trial-card-header p {
        margin: 2px 0 0;
        color: var(--trial-muted);
        font-size: 12px;
    }

    .trial-card-body {
        padding: 16px;
    }

    .trial-label {
        display: block;
        margin-bottom: 7px;
        color: var(--trial-muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .trial-input {
        width: 100%;
        height: 42px;
        border: 1px solid var(--trial-border);
        border-radius: 13px;
        padding: 9px 11px;
        background: #f8fafc;
        color: var(--trial-text);
        font-weight: 800;
        outline: none;
        transition: .18s ease;
    }

    .trial-input:focus {
        border-color: var(--trial-primary);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .trial-summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .trial-summary {
        min-height: 92px;
        padding: 14px;
        border: 1px solid var(--trial-border);
        border-radius: 18px;
        background: #f8fafc;
    }

    .trial-summary.green {
        border-color: #bbf7d0;
        background: var(--trial-green-soft);
    }

    .trial-summary.yellow {
        border-color: #fde68a;
        background: var(--trial-yellow-soft);
    }

    .trial-summary small {
        display: block;
        margin-bottom: 8px;
        color: var(--trial-muted);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .trial-summary strong {
        display: block;
        color: var(--trial-text);
        font-size: 25px;
        line-height: 1.1;
        font-weight: 950;
        letter-spacing: -.03em;
    }

    .trial-summary.yellow strong {
        font-size: 22px;
        color: #854d0e;
    }

    .trial-table-card {
        border: 1px solid var(--trial-border);
        border-radius: 22px;
        background: #fff;
        box-shadow: var(--trial-shadow);
        overflow: hidden;
    }

    .trial-table-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--trial-border);
    }

    .trial-table-top h2 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
        font-size: 16px;
        font-weight: 900;
    }

    .trial-table-top h2 i {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--trial-primary-soft);
        color: var(--trial-primary);
    }

    .trial-table-note {
        color: var(--trial-muted);
        font-size: 12px;
        font-weight: 800;
    }

    .trial-table-wrap {
        overflow-x: auto;
    }

    .trial-table {
        width: 100%;
        min-width: 1180px;
        border-collapse: collapse;
        margin: 0;
    }

    .trial-table th {
        padding: 12px 10px;
        border-bottom: 1px solid var(--trial-border);
        background: #f8fafc;
        color: var(--trial-muted);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .04em;
        text-align: center;
        white-space: nowrap;
    }

    .trial-table td {
        padding: 9px 8px;
        border-bottom: 1px solid var(--trial-border);
        text-align: center;
        vertical-align: middle;
        font-size: 13px;
        font-weight: 800;
    }

    .trial-table tbody tr:hover td {
        background: #f8fafc;
    }

    .trial-row-label {
        width: 180px;
        text-align: left !important;
        color: var(--trial-text);
        background: #fbfdff;
        font-weight: 900 !important;
        position: sticky;
        left: 0;
        z-index: 2;
        border-right: 1px solid var(--trial-border);
    }

    .trial-cell-input {
        width: 68px;
        height: 34px;
        border: 1px solid var(--trial-border);
        border-radius: 10px;
        padding: 6px 8px;
        background: #fff;
        text-align: center;
        font-weight: 900;
        outline: none;
    }

    .trial-cell-input:focus {
        border-color: var(--trial-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
    }

    .trial-total {
        background: var(--trial-yellow-soft) !important;
        color: #854d0e;
        font-weight: 950 !important;
    }

    .trial-est {
        background: var(--trial-green-soft) !important;
        color: #166534;
        font-weight: 950 !important;
    }

    .trial-revenue-row td {
        padding: 14px 12px;
        background: var(--trial-yellow-soft);
        color: #854d0e;
        font-size: 16px;
        font-weight: 950;
    }

    @media (max-width: 1100px) {
        .trial-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 900px) {
        .trial-page {
            padding: 14px;
        }

        .trial-hero {
            align-items: stretch;
            flex-direction: column;
            padding: 16px;
        }

        .trial-hero h1 {
            font-size: 24px;
        }

        .trial-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="trial-page">
    <div class="trial-shell">

        <div class="trial-hero">
            <div>
                <div class="trial-eyebrow">
                    <i class="bi bi-clipboard-data"></i>
                    Trial Sitescore Pengamatan
                </div>

                <h1>Worksheet Pengamatan Outlet</h1>

                <p>
                    Input traffic per jam dari pukul 06.00 sampai 18.00.
                    Sistem akan menghitung total actual count, estimasi weekday, estimasi weekend, dan potensi omzet.
                </p>
            </div>
        </div>

        <div class="trial-grid">
            <div class="trial-card">
                <div class="trial-card-header">
                    <i class="bi bi-geo-alt"></i>
                    <div>
                        <h2>Parameter Pengamatan</h2>
                        <p>Informasi lokasi, potensi transaksi, dan AOV.</p>
                    </div>
                </div>

                <div class="trial-card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="trial-label">Tanggal</label>
                            <input type="date" class="trial-input">
                        </div>

                        <div class="col-md-5">
                            <label class="trial-label">Lokasi</label>
                            <input class="trial-input" placeholder="Nama outlet / calon outlet">
                        </div>

                        <div class="col-md-2">
                            <label class="trial-label">Kota</label>
                            <input class="trial-input" placeholder="Kota">
                        </div>

                        <div class="col-md-2">
                            <label class="trial-label">Provinsi</label>
                            <input class="trial-input" placeholder="Provinsi">
                        </div>

                        <div class="col-md-6">
                            <label class="trial-label">Potensi dari 100 transaksi</label>
                            <input id="potensi" class="trial-input calc-observe" type="number" value="1">
                        </div>

                        <div class="col-md-6">
                            <label class="trial-label">Average Order Value</label>
                            <input id="aov" class="trial-input calc-observe" type="number" value="15000">
                        </div>
                    </div>
                </div>
            </div>

            <div class="trial-card">
                <div class="trial-card-header">
                    <i class="bi bi-speedometer2"></i>
                    <div>
                        <h2>Ringkasan Otomatis</h2>
                        <p>Ter-update setiap input berubah.</p>
                    </div>
                </div>

                <div class="trial-card-body">
                    <div class="trial-summary-grid">
                        <div class="trial-summary">
                            <small>Total Motor</small>
                            <strong id="obsMotorBox">0</strong>
                        </div>

                        <div class="trial-summary">
                            <small>Total Jalan</small>
                            <strong id="obsJalanBox">0</strong>
                        </div>

                        <div class="trial-summary green">
                            <small>Weekday Est.</small>
                            <strong id="obsWeekdayBox">0</strong>
                        </div>

                        <div class="trial-summary yellow">
                            <small>Omzet Est.</small>
                            <strong id="obsRevenueBox">Rp 0</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="trial-table-card">
            <div class="trial-table-top">
                <h2>
                    <i class="bi bi-table"></i>
                    Actual Count Per Jam
                </h2>

                <div class="trial-table-note">
                    Jam 06.00 - 18.00
                </div>
            </div>

            <div class="trial-table-wrap">
                <table class="trial-table">
                    <thead>
                        <tr>
                            <th class="trial-row-label">Status Row</th>
                            @for($i = 6; $i <= 18; $i++)
                                <th>{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</th>
                            @endfor
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td class="trial-row-label">Actual Count Motor</td>
                            @for($i = 6; $i <= 18; $i++)
                                <td>
                                    <input type="number" class="trial-cell-input observe-motor calc-observe" value="0" min="0">
                                </td>
                            @endfor
                            <td id="observeMotorTotal" class="trial-total">0</td>
                        </tr>

                        <tr>
                            <td class="trial-row-label">Actual Count Jalan</td>
                            @for($i = 6; $i <= 18; $i++)
                                <td>
                                    <input type="number" class="trial-cell-input observe-jalan calc-observe" value="0" min="0">
                                </td>
                            @endfor
                            <td id="observeJalanTotal" class="trial-total">0</td>
                        </tr>

                        <tr>
                            <td class="trial-row-label">Bar Weekday</td>
                            @for($i = 6; $i <= 18; $i++)
                                <td>
                                    <input type="number" step="0.1" class="trial-cell-input observe-bar-weekday calc-observe" value="0.5">
                                </td>
                            @endfor
                            <td>-</td>
                        </tr>

                        <tr>
                            <td class="trial-row-label">Bar Weekend</td>
                            @for($i = 6; $i <= 18; $i++)
                                <td>
                                    <input type="number" step="0.1" class="trial-cell-input observe-bar-weekend calc-observe" value="0.5">
                                </td>
                            @endfor
                            <td>-</td>
                        </tr>

                        <tr>
                            <td class="trial-row-label trial-est">Estimasi Weekday</td>
                            @for($i = 6; $i <= 18; $i++)
                                <td class="weekday-est trial-est">0</td>
                            @endfor
                            <td id="weekdayTotal" class="trial-est">0</td>
                        </tr>

                        <tr>
                            <td class="trial-row-label trial-est">Estimasi Weekend</td>
                            @for($i = 6; $i <= 18; $i++)
                                <td class="weekend-est trial-est">0</td>
                            @endfor
                            <td id="weekendTotal" class="trial-est">0</td>
                        </tr>

                        <tr class="trial-revenue-row">
                            <td class="trial-row-label">Estimasi Omzet</td>
                            <td colspan="14" id="estimatedRevenue">Rp 0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    function rupiah(n) {
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    }

    function calcObserve() {
        const motorEls = [...document.querySelectorAll('.observe-motor')];
        const jalanEls = [...document.querySelectorAll('.observe-jalan')];
        const weekdayBars = [...document.querySelectorAll('.observe-bar-weekday')];
        const weekendBars = [...document.querySelectorAll('.observe-bar-weekend')];

        let motor = 0;
        let jalan = 0;
        let weekday = 0;
        let weekend = 0;

        motorEls.forEach((el, index) => {
            const motorValue = parseFloat(el.value || 0);
            const jalanValue = parseFloat(jalanEls[index].value || 0);
            const base = motorValue + jalanValue;
            const weekdayValue = base * parseFloat(weekdayBars[index].value || 0);
            const weekendValue = base * parseFloat(weekendBars[index].value || 0);

            motor += motorValue;
            jalan += jalanValue;
            weekday += weekdayValue;
            weekend += weekendValue;

            document.querySelectorAll('.weekday-est')[index].innerText = Math.round(weekdayValue).toLocaleString('id-ID');
            document.querySelectorAll('.weekend-est')[index].innerText = Math.round(weekendValue).toLocaleString('id-ID');
        });

        const potensi = parseFloat(document.getElementById('potensi').value || 0) / 100;
        const aov = parseFloat(document.getElementById('aov').value || 0);
        const omzet = (weekday + weekend) * potensi * aov;

        document.getElementById('observeMotorTotal').innerText = Math.round(motor).toLocaleString('id-ID');
        document.getElementById('observeJalanTotal').innerText = Math.round(jalan).toLocaleString('id-ID');
        document.getElementById('weekdayTotal').innerText = Math.round(weekday).toLocaleString('id-ID');
        document.getElementById('weekendTotal').innerText = Math.round(weekend).toLocaleString('id-ID');
        document.getElementById('estimatedRevenue').innerText = rupiah(omzet);

        document.getElementById('obsMotorBox').innerText = Math.round(motor).toLocaleString('id-ID');
        document.getElementById('obsJalanBox').innerText = Math.round(jalan).toLocaleString('id-ID');
        document.getElementById('obsWeekdayBox').innerText = Math.round(weekday).toLocaleString('id-ID');
        document.getElementById('obsRevenueBox').innerText = rupiah(omzet);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.calc-observe').forEach(el => {
            el.addEventListener('input', calcObserve);
        });

        calcObserve();
    });
</script>
@endpush

@include('Surveyor.layouts.footer')

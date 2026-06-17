{{-- resources/views/Purchasing/warehouseDetail.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>

<style>
    :root {
        --primary: #696cff;
        --accent: #71dd37;
        --warn: #ffab00;
        --danger: #ff3e1d;
        --info: #03c3ec;
        --bg: #f5f5f9;
        --card: #ffffff;
        --border: #e0e0f0;
        --shadow: 0 2px 8px rgba(67, 89, 113, .10);
        --radius: 12px;
    }

    body {
        background: var(--bg);
    }

    /* ── Metric Cards (Modernized) ── */
    .inventory-status-row {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
    }

    .inv-card {
        flex: 1;
        background-color: var(--card);
        border-radius: var(--radius);
        padding: 16px 18px;
        box-shadow: var(--shadow);
        transition: all 0.3s ease-in-out;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border: 1px solid transparent;
    }

    .inv-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(67, 89, 113, .15);
    }

    .inv-main-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .inv-left {
        flex: 1;
    }

    .inv-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #697a8d;
        margin-bottom: 5px;
    }

    .inv-value {
        font-size: 24px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .inv-sub {
        font-size: 11px;
        color: #94a3b8;
    }

    .inv-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .inv-icon svg {
        width: 100%;
        height: 100%;
        transition: transform 0.3s ease;
    }

    .inv-card:hover .inv-icon svg {
        transform: scale(1.1);
    }

    .inv-action {
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
        display: flex;
        justify-content: flex-start;
        margin-top: 8px;
    }

    .inv-card:hover .inv-action {
        opacity: 1;
        transform: translateY(0);
    }

    .inv-btn {
        display: inline-block;
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        border-radius: 6px;
        transition: background 0.3s ease;
    }

    /* ── Metric Card Themes ── */
    .inv-critical {
        background: linear-gradient(135deg, var(--card) 0%, #fff1f2 100%);
        border-left: 5px solid var(--danger);
    }

    .inv-critical .inv-value {
        color: var(--danger);
    }

    .inv-critical .inv-icon svg path {
        fill: var(--danger);
    }

    .inv-btn-critical {
        color: var(--danger);
        border: 1px solid var(--danger);
    }

    .inv-btn-critical:hover {
        background-color: var(--danger);
        color: #ffffff;
    }

    .inv-warning-high {
        background: linear-gradient(135deg, var(--card) 0%, #fff7ed 100%);
        border-left: 5px solid var(--warn);
    }

    .inv-warning-high .inv-value {
        color: #d99200;
    }

    .inv-warning-high .inv-icon svg path {
        fill: var(--warn);
    }

    .inv-btn-warning-high {
        color: #d99200;
        border: 1px solid #d99200;
    }

    .inv-btn-warning-high:hover {
        background-color: var(--warn);
        color: #ffffff;
    }

    .inv-warning-low {
        background: linear-gradient(135deg, var(--card) 0%, #fffdf2 100%);
        border-left: 5px solid #facc15;
    }

    .inv-warning-low .inv-value {
        color: #ca8a04;
    }

    .inv-warning-low .inv-icon svg path {
        fill: #facc15;
    }

    .inv-btn-warning-low {
        color: #ca8a04;
        border: 1px solid #ca8a04;
    }

    .inv-btn-warning-low:hover {
        background-color: #facc15;
        color: #ffffff;
    }

    .inv-good {
        background: linear-gradient(135deg, var(--card) 0%, #f0fdf4 100%);
        border-left: 5px solid var(--accent);
    }

    .inv-good .inv-value {
        color: var(--accent);
    }

    .inv-good .inv-icon svg path {
        fill: var(--accent);
    }

    .inv-btn-good {
        color: var(--accent);
        border: 1px solid var(--accent);
    }

    .inv-btn-good:hover {
        background-color: var(--accent);
        color: #ffffff;
    }

    /* Responsivitas Metric Cards */
    @media (max-width: 900px) {
        .inventory-status-row {
            flex-wrap: wrap;
        }

        .inv-card {
            flex: 1 1 calc(50% - 16px);
        }
    }

    @media (max-width: 600px) {
        .inv-card {
            flex: 1 1 100%;
        }
    }

    /* ── Table ── */
    .rop-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }

    .rop-table thead th {
        background: #f5f5f9;
        padding: 10px 12px;
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #566a7f;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
        text-align: center;
    }

    .rop-table thead th.th-name {
        text-align: left;
        min-width: 160px;
    }

    .rop-table tbody td {
        padding: 9px 12px;
        border-bottom: 0.5px solid #f0f0f8;
        vertical-align: middle;
        text-align: center;
        color: #435971;
    }

    .rop-table tbody td.td-name {
        text-align: left;
        font-weight: 600;
        color: #233446;
    }

    .rop-table tbody tr:hover {
        background: rgba(105, 108, 255, .025);
    }

    /* ── Status badges ── */
    .s-badge {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
    }

    .s-habis {
        background: #fcebeb;
        color: #a32d2d;
    }

    .s-dibawah_rop {
        background: #faeeda;
        color: #633806;
    }

    .s-menipis {
        background: #fff8d6;
        color: #8a6000;
    }

    .s-aman {
        background: #eaf3de;
        color: #27500a;
    }

    /* ── ROP highlight ── */
    .rop-val {
        font-size: 13px;
        font-weight: 800;
    }

    .rop-ok {
        color: #27500a;
    }

    .rop-warn {
        color: #633806;
    }

    .rop-crit {
        color: #a32d2d;
    }

    /* ── Stok vs ROP bar ── */
    .mini-bar-wrap {
        width: 70px;
        height: 6px;
        background: #eeeef5;
        border-radius: 3px;
        margin: 0 auto;
    }

    .mini-bar-fill {
        height: 100%;
        border-radius: 3px;
    }

    /* ── Weekly usage pills ── */
    .week-cell {
        font-weight: 600;
    }

    .week-0 {
        color: #697a8d;
    }

    /* 0 */
    .week-low {
        color: #633806;
    }

    /* rendah */
    .week-med {
        color: #185fa5;
    }

    /* sedang */
    .week-high {
        color: #27500a;
    }

    /* tinggi */

    /* ── Chart panel ── */
    .chart-panel {
        background: var(--card);
        border-radius: var(--radius);
        border: 0.5px solid var(--border);
        padding: 20px 24px;
        margin-bottom: 20px;
    }

    .chart-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 4px;
    }

    .chart-title {
        font-size: 14px;
        font-weight: 700;
        color: #233446;
    }

    .chart-sub {
        font-size: 11px;
        color: #697a8d;
        margin-bottom: 14px;
    }

    /* ── Bahan selector ── */
    .bahan-selector-wrap {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .bahan-pill {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid var(--border);
        background: var(--bg);
        color: #697a8d;
        cursor: pointer;
        transition: all .15s;
    }

    .bahan-pill.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }

    .bahan-pill:hover:not(.active) {
        background: #eeedfe;
        color: var(--primary);
        border-color: var(--primary);
    }

    /* ── Section card ── */
    .section-card {
        background: var(--card);
        border-radius: var(--radius);
        border: 0.5px solid var(--border);
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: var(--shadow);
    }

    .section-header {
        padding: 14px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .section-title {
        font-size: 14px;
        font-weight: 700;
        color: #233446;
        margin: 0;
    }

    .section-sub {
        font-size: 11px;
        color: #697a8d;
    }

    /* ── Outlet table ── */
    .outlet-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .outlet-table td {
        padding: 9px 16px;
        border-bottom: 0.5px solid #f0f0f8;
        color: #435971;
    }

    .outlet-table tr:last-child td {
        border-bottom: none;
    }

    /* ── Lead time input inline ── */
    .lt-input {
        width: 56px;
        text-align: center;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 3px 6px;
        font-size: 12px;
        color: #233446;
        background: #fafafe;
    }

    .lt-input:focus {
        border-color: var(--primary);
        outline: none;
    }

    /* ── Save btn inline ── */
    .btn-save-rop {
        padding: 3px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        border: none;
        background: var(--primary);
        color: #fff;
        cursor: pointer;
        transition: .15s;
        opacity: .7;
    }

    .btn-save-rop:hover {
        opacity: 1;
    }

    .btn-save-rop.saved {
        background: var(--accent);
        opacity: 1;
    }

    /* ── DC info card ── */
    .dc-info-card {
        background: linear-gradient(135deg, var(--primary) 0%, #8b8eff 100%);
        border-radius: var(--radius);
        padding: 22px 26px;
        color: #fff;
        margin-bottom: 20px;
    }

    .dc-info-card h4 {
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .dc-badge {
        display: inline-block;
        background: rgba(255, 255, 255, .2);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="/stock-control" class="text-primary">Stock Control</a></li>
                    <li class="breadcrumb-item active">Detail DC — {{ $warehouse->nama_warehouse }}</li>
                </ol>
            </nav>

            {{-- ── DC Info ── --}}
            <div class="dc-info-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4><i class="bi bi-building me-2"></i>{{ $warehouse->nama_warehouse }}</h4>
                        <div class="mb-2 opacity-75" style="font-size:13px;">
                            <i class="bi bi-geo-alt me-1"></i>{{ $warehouse->alamat ?? 'Lokasi belum diatur' }}
                        </div>
                        <span class="dc-badge">✓ Aktif</span>
                    </div>
                    <div class="text-end">
                        <div style="font-size:12px;opacity:.75;margin-bottom:4px;">
                            Periode analisis ROP
                        </div>
                        <div style="font-size:13px;font-weight:700;">
                            {{ \Carbon\Carbon::parse($weeks[0]['start'])->format('d M') }}
                            — {{ \Carbon\Carbon::parse($weeks[3]['end'])->format('d M Y') }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Metric Cards ── --}}
            <div class="inventory-status-row">
                <div class="inv-card inv-critical {{ $summary['habis'] > 0 ? 'inv-active' : '' }}">
                    <div class="inv-main-info">
                        <div class="inv-left">
                            <div class="inv-label">Stok Habis</div>
                            <div class="inv-value">{{ $summary['habis'] }}</div>
                            <div class="inv-sub">Bahan kosong — reorder segera</div>
                        </div>
                        <div class="inv-icon">
                            <svg viewBox="0 0 24 24">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path
                                    d="M12 2c5.52 0 10 4.48 10 10s-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2zm0 18c4.41 0 8-3.59 8-8s-3.59-8-8-8-8 3.59-8 8 3.59 8 8 8zm3.59-13L12 10.59 8.41 7 7 8.41 10.59 12 7 15.59 8.41 17 12 13.41 15.59 17 17 15.59 13.41 12 17 8.41 15.59 7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="inv-action">
                        <a href="#" class="inv-btn inv-btn-critical">Lihat Detail</a>
                    </div>
                </div>

                <div class="inv-card inv-warning-high {{ $summary['dibawah_rop'] > 0 ? 'inv-active' : '' }}">
                    <div class="inv-main-info">
                        <div class="inv-left">
                            <div class="inv-label">Di Bawah ROP</div>
                            <div class="inv-value">{{ $summary['dibawah_rop'] }}</div>
                            <div class="inv-sub">Sudah lewati titik reorder</div>
                        </div>
                        <div class="inv-icon">
                            <svg viewBox="0 0 24 24">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm1-13v6h2v-6h-2zm1.5 8h-3v2h3v-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="inv-action">
                        <a href="#" class="inv-btn inv-btn-warning-high">Lihat Detail</a>
                    </div>
                </div>

                <div class="inv-card inv-warning-low {{ $summary['menipis'] > 0 ? 'inv-active' : '' }}">
                    <div class="inv-main-info">
                        <div class="inv-left">
                            <div class="inv-label">Stok Menipis</div>
                            <div class="inv-value">{{ $summary['menipis'] }}</div>
                            <div class="inv-sub">Mendekati ROP (&lt;20%)</div>
                        </div>
                        <div class="inv-icon">
                            <svg viewBox="0 0 24 24">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path
                                    d="M12 4C6.48 4 2 8.48 2 14s4.48 10 10 10 10-4.48 10-10S17.52 4 12 4zm0 18C7.59 22 4 18.41 4 14s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-11v4l2.5 1.5 1-1.72-2-1.18V11h-1.5z" />
                            </svg>
                        </div>
                    </div>
                    <div class="inv-action">
                        <a href="#" class="inv-btn inv-btn-warning-low">Lihat Detail</a>
                    </div>
                </div>

                <div class="inv-card inv-good {{ $summary['aman'] > 0 ? 'inv-active' : '' }}">
                    <div class="inv-main-info">
                        <div class="inv-left">
                            <div class="inv-label">Stok Aman</div>
                            <div class="inv-value">{{ $summary['aman'] }}</div>
                            <div class="inv-sub">Di atas ROP</div>
                        </div>
                        <div class="inv-icon">
                            <svg viewBox="0 0 24 24">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.59-12.42L10 14.17l-2.59-2.58L6 13l4 4 6-6-1.41-1.42z" />
                            </svg>
                        </div>
                    </div>
                    <div class="inv-action">
                        <a href="#" class="inv-btn inv-btn-good">Lihat Detail</a>
                    </div>
                </div>
            </div>



            {{-- ── Chart Interaktif ── --}}
            <div class="chart-panel">
                <div class="chart-header">
                    <div>
                        <div class="chart-title">Tren Pemakaian per Minggu + ROP</div>
                        <div class="chart-sub">Pilih bahan untuk melihat grafik pemakaian vs titik ROP</div>
                    </div>
                    <div id="chartStatusBadge"></div>
                </div>

                {{-- Pill selector bahan -- tampilkan max 20, sisanya bisa cari --}}
                <!-- <div class="bahan-selector-wrap" id="bahanPills">
                    @foreach($tableData as $i => $row)
                        @if($i < 20)
                            <span class="bahan-pill {{ $i === 0 ? 'active' : '' }}" data-bahan="{{ $row['id'] }}"
                                onclick="selectBahan({{ $row['id'] }}, this)">
                                {{ $row['nama_bahan'] }}
                            </span>
                        @endif
                    @endforeach
                    @if(count($tableData) > 20)
                        <span class="text-muted" style="font-size:11px;">+{{ count($tableData) - 20 }} lainnya lihat di
                            tabel</span>
                    @endif
                </div> -->

                <div class="bahan-selector-wrap mb-4">
                    <select id="bahanDropdown" class="form-select shadow-none"
                        style="width: auto; min-width: 250px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #435971; border-color: var(--border);"
                        onchange="selectBahan(this.value)">
                        @foreach($tableData as $row)
                            <option value="{{ $row['id'] }}">
                                {{ $row['nama_bahan'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="position:relative;height:280px;">
                    <canvas id="ropChart" aria-label="Grafik tren pemakaian dan ROP per minggu" role="img">
                        Grafik pemakaian bahan per minggu dibandingkan titik ROP.
                    </canvas>
                </div>

                {{-- Legend info --}}
                <div class="d-flex gap-4 mt-3" style="font-size:12px;color:#697a8d;">
                    <span><span
                            style="display:inline-block;width:12px;height:12px;background:#185fa5;border-radius:3px;margin-right:4px;"></span>Pemakaian
                        Aktual</span>
                    <span><span
                            style="display:inline-block;width:12px;height:3px;background:#ff3e1d;margin-right:4px;vertical-align:middle;border-radius:2px;"></span>Titik
                        ROP</span>
                    <span><span
                            style="display:inline-block;width:12px;height:3px;background:#ffab00;margin-right:4px;vertical-align:middle;border-radius:2px;border-top:2px dashed #ffab00;"></span>Safety
                        Stock</span>
                </div>
            </div>

            {{-- ── Tabel ROP per Minggu ── --}}
            <div class="section-card">
                <div class="section-header">
                    <div>
                        <div class="section-title">Tabel ROP & Pemakaian Mingguan</div>
                        <div class="section-sub">
                            Pemakaian 4 minggu terakhir dari tbl_stock_transactions (tipe KELUAR) · Lead Time dalam hari
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="text" id="searchBahan" class="form-control form-control-sm shadow-none"
                            placeholder="🔍 Cari bahan..." style="width:180px;border-radius:8px;">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="rop-table" id="ropTable">
                        <thead>
                            <tr>
                                <th class="th-name ps-4">Bahan</th>
                                <th>Satuan</th>
                                <th>Stok Aktual</th>
                                @foreach($weeks as $w)
                                    <th>{{ $w['short'] }}<br><span
                                            style="font-weight:400;font-size:9px;text-transform:none;">{{ $w['label'] }}</span>
                                    </th>
                                @endforeach
                                <th>Avg/Minggu</th>
                                <th>Safety Stock</th>
                                <th>Lead Time<br><span style="font-weight:400;font-size:9px;">(hari)</span></th>
                                <th>ROP</th>
                                <th>Status</th>
                                <th>Chart</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tableData as $row)
                                                    @php
                                                        $maxUsage = max($row['weekly_usage']) ?: 1;
                                                        $stokPct = $row['rop'] > 0 ? min(100, round($row['stok_aktual'] / ($row['rop'] * 2) * 100)) : 100;
                                                        $barColor = $row['status'] === 'habis' ? '#a32d2d'
                                                            : ($row['status'] === 'dibawah_rop' ? '#ffab00'
                                                                : ($row['status'] === 'menipis' ? '#f0c000' : '#71dd37'));
                                                        $ropClass = $row['status'] === 'habis' ? 'rop-crit'
                                                            : ($row['status'] === 'dibawah_rop' ? 'rop-warn' : 'rop-ok');
                                                    @endphp
                                                    <tr class="bahan-row" data-name="{{ strtolower($row['nama_bahan']) }}">
                                                        <td class="td-name ps-4">
                                                            {{ $row['nama_bahan'] }}
                                                        </td>
                                                        <td style="color:#697a8d;">{{ $row['nama_satuan'] }}</td>

                                                        {{-- Stok Aktual --}}
                                                        <td>
                                                            <span style="font-weight:700;color:#233446;">
                                                                {{ number_format($row['stok_aktual'], 1) }}
                                                            </span>
                                                            <div class="mini-bar-wrap mt-1">
                                                                <div class="mini-bar-fill"
                                                                    style="width:{{ $stokPct }}%;background:{{ $barColor }};"></div>
                                                            </div>
                                                        </td>

                                                        {{-- Pemakaian per Minggu --}}
                                                        @foreach($row['weekly_usage'] as $usage)
                                                            @php
                                                                $pct2 = $maxUsage > 0 ? $usage / $maxUsage : 0;
                                                                $wCls = $usage == 0 ? 'week-0'
                                                                    : ($pct2 < 0.3 ? 'week-low'
                                                                        : ($pct2 < 0.7 ? 'week-med' : 'week-high'));
                                                            @endphp
                                                            <td class="week-cell {{ $wCls }}">
                                                                {{ $usage > 0 ? number_format($usage, 1) : '—' }}
                                                            </td>
                                                        @endforeach

                                                        {{-- Avg --}}
                                                        <td style="font-weight:600;">
                                                            {{ number_format($row['avg_weekly'], 1) }}
                                                        </td>

                                                        {{-- Safety Stock --}}
                                                        <td style="color:#697a8d;">
                                                            {{ number_format($row['safety_stock'], 1) }}
                                                        </td>

                                                        {{-- Lead Time (editable) --}}
                                                        <td>
                                                            <input type="number" class="lt-input lt-field" data-bahan="{{ $row['id'] }}"
                                                                data-avg="{{ $row['avg_weekly'] }}" data-safety="{{ $row['safety_stock'] }}"
                                                                value="{{ $row['lead_time'] }}" min="1" max="60">
                                                        </td>

                                                        {{-- ROP --}}
                                                        <td>
                                                            <span class="rop-val {{ $ropClass }} display-rop" id="rop-{{ $row['id'] }}">
                                                                {{ number_format($row['rop'], 1) }}
                                                            </span>
                                                            <input type="hidden" class="rop-hidden" id="rop-hidden-{{ $row['id'] }}"
                                                                value="{{ $row['rop'] }}">
                                                        </td>

                                                        {{-- Status --}}
                                                        <td>
                                                            <span class="s-badge s-{{ $row['status'] }}">
                                                                {{ $row['status'] === 'habis' ? '🔴 Habis'
                                : ($row['status'] === 'dibawah_rop' ? '🟠 < ROP'
                                    : ($row['status'] === 'menipis' ? '🟡 Menipis' : '🟢 Aman')) }}
                                                            </span>
                                                        </td>

                                                        {{-- Tombol chart --}}
                                                        <td>
                                                            <button class="btn-save-rop" style="background:#e6f1fb;color:#0c447c;"
                                                                onclick="selectBahanById({{ $row['id'] }})">
                                                                <i class="bi bi-bar-chart-line"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Outlet Termapping ── --}}
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title">Outlet Termapping ke DC Ini</div>
                    <span class="section-sub">{{ count($outlets) }} outlet</span>
                </div>
                <table class="outlet-table">
                    @forelse($outlets as $outlet)
                        <tr>
                            <td style="font-weight:600;color:#233446;width:40%;">
                                <i class="bi bi-shop me-2 text-primary"></i>{{ $outlet->nama_outlet }}
                            </td>
                            <td style="color:#697a8d;font-size:12px;">{{ $outlet->alamat ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-4 text-muted" style="font-size:13px;">
                                Belum ada outlet yang termapping ke DC ini.
                            </td>
                        </tr>
                    @endforelse
                </table>
            </div>

        </div>
    </div>
</main>

@push('scripts')
    <script>
        // =========================================================================
        // DATA dari controller (di-pass sebagai JSON)
        // =========================================================================
        const TABLE_DATA = @json($tableData);
        const WEEK_LABELS = @json(array_column($weeks, 'short'));
        const WEEK_FULL = @json(array_column($weeks, 'label'));

        // =========================================================================
        // CHART: init
        // =========================================================================
        let ropChart = null;
        const ctx = document.getElementById('ropChart').getContext('2d');

        function buildChartData(bahanId) {
            const row = TABLE_DATA.find(r => r.id == bahanId);
            if (!row) return null;

            const ropLine = WEEK_LABELS.map(() => row.rop);
            const safetyLine = WEEK_LABELS.map(() => row.safety_stock);

            return {
                row,
                labels: WEEK_FULL,
                datasets: [
                    {
                        label: 'Pemakaian Aktual',
                        data: row.weekly_usage,
                        backgroundColor: 'rgba(24, 95, 165, 0.15)',
                        borderColor: '#185fa5',
                        borderWidth: 2.5,
                        pointRadius: 5,
                        pointBackgroundColor: '#185fa5',
                        fill: true,
                        tension: 0.3,
                        type: 'line',
                    },
                    {
                        label: 'Titik ROP',
                        data: ropLine,
                        borderColor: '#ff3e1d',
                        borderWidth: 2,
                        borderDash: [],
                        pointRadius: 0,
                        fill: false,
                        tension: 0,
                        type: 'line',
                    },
                    {
                        label: 'Safety Stock',
                        data: safetyLine,
                        borderColor: '#ffab00',
                        borderWidth: 1.5,
                        borderDash: [5, 3],
                        pointRadius: 0,
                        fill: false,
                        tension: 0,
                        type: 'line',
                    },
                ]
            };
        }

        function renderChart(bahanId) {
            const d = buildChartData(bahanId);
            if (!d) return;

            // Update status badge
            const statusMap = {
                habis: ['🔴 Habis', '#fcebeb', '#a32d2d'],
                dibawah_rop: ['🟠 Di Bawah ROP', '#faeeda', '#633806'],
                menipis: ['🟡 Menipis', '#fff8d6', '#8a6000'],
                aman: ['🟢 Aman', '#eaf3de', '#27500a'],
            };
            const [txt, bg, col] = statusMap[d.row.status] || ['—', '#f0f0f0', '#555'];
            document.getElementById('chartStatusBadge').innerHTML =
                `<span style="background:${bg};color:${col};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;">${txt}</span>`;

            if (ropChart) {
                ropChart.data.labels = d.labels;
                ropChart.data.datasets = d.datasets;
                ropChart.options.plugins.title.text = `${d.row.nama_bahan} — Avg: ${d.row.avg_weekly} ${d.row.nama_satuan}/minggu · Stok: ${d.row.stok_aktual} · ROP: ${d.row.rop}`;
                ropChart.update();
            } else {
                ropChart = new Chart(ctx, {
                    type: 'line',
                    data: { labels: d.labels, datasets: d.datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { font: { size: 11 }, boxWidth: 14, padding: 14 }
                            },
                            title: {
                                display: true,
                                text: `${d.row.nama_bahan} — Avg: ${d.row.avg_weekly} ${d.row.nama_satuan}/minggu · Stok: ${d.row.stok_aktual} · ROP: ${d.row.rop}`,
                                font: { size: 12, weight: '600' },
                                color: '#233446',
                                padding: { bottom: 14 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y.toFixed(1)}`,
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#697a8d' } },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.04)' },
                                ticks: { font: { size: 11 }, color: '#697a8d' }
                            }
                        }
                    }
                });
            }
        }

        // ── Pill click ────────────────────────────────────────────────
        // ── Dropdown onchange ─────────────────────────────────────────
        function selectBahan(id) {
            // Langsung render chart berdasarkan id yang dipilih dari dropdown
            renderChart(id);
        }

        // ── Dari tombol di tabel ──────────────────────────────────────
        function selectBahanById(id) {
            // 1. Scroll ke atas menuju chart
            document.querySelector('.chart-panel').scrollIntoView({ behavior: 'smooth' });

            // 2. Ubah pilihan dropdown agar sinkron dengan bahan yang diklik
            const dropdown = document.getElementById('bahanDropdown');
            if (dropdown) {
                dropdown.value = id;
            }

            // 3. Render chart
            renderChart(id);
        }

        // ── Init dengan bahan pertama ─────────────────────────────────
        if (TABLE_DATA.length > 0) renderChart(TABLE_DATA[0].id);

        // =========================================================================
        // LEAD TIME — update ROP saat input berubah
        // =========================================================================
        document.querySelectorAll('.lt-field').forEach(input => {
            input.addEventListener('input', function () {
                const avg = parseFloat(this.dataset.avg) || 0;
                const safety = parseFloat(this.dataset.safety) || 0;
                const leadDays = parseFloat(this.value) || 1;
                const leadWeeks = leadDays / 7;
                const newRop = Math.ceil((avg * leadWeeks) + safety);

                const bahanId = this.dataset.bahan;
                document.getElementById('rop-' + bahanId).textContent = newRop.toFixed(1);
                document.getElementById('rop-hidden-' + bahanId).value = newRop;

                // Juga update TABLE_DATA di memori supaya chart ikut
                const row = TABLE_DATA.find(r => r.id == bahanId);
                if (row) {
                    row.rop = newRop;
                    row.lead_time = leadDays;
                    // Re-render chart kalau bahan ini sedang aktif
                    const activePill = document.querySelector('.bahan-pill.active');
                    if (activePill && parseInt(activePill.dataset.bahan) == bahanId) {
                        renderChart(bahanId);
                    }
                }

                // Tandai perlu di-save
                this.closest('tr').dataset.dirty = '1';
            });

            // Auto-save saat blur (pindah focus)
            input.addEventListener('blur', function () {
                const row = this.closest('tr');
                if (!row.dataset.dirty) return;
                const bahanId = this.dataset.bahan;
                const rop = document.getElementById('rop-hidden-' + bahanId).value;

                fetch("{{ route('update.rop.dc') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        bahan_id: bahanId,
                        lead_time: this.value,
                        rop_level: rop,
                        safety_stock: this.dataset.safety,
                    })
                })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            delete row.dataset.dirty;
                            // Flash hijau sebentar
                            this.style.borderColor = '#71dd37';
                            setTimeout(() => this.style.borderColor = '', 1500);
                        }
                    })
                    .catch(() => { }); // silent fail
            });
        });

        // =========================================================================
        // SEARCH filter bahan di tabel
        // =========================================================================
        document.getElementById('searchBahan').addEventListener('input', function () {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.bahan-row').forEach(tr => {
                tr.style.display = tr.dataset.name.includes(q) ? '' : 'none';
            });
        });
    </script>
@endpush

@include('Temp.Investor.footer')
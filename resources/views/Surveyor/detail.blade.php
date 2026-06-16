@section('title', 'Detail Worksheet Site Score')
@section('breadcrumb', 'Site Score Outlet / Detail')
@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

@php $score = $score ?? null; $photos = $photos ?? collect(); @endphp

<style>
    /* Styling khusus detail page */
    .detail-hero {
        background: #fff;
        border: 1px solid var(--xl-border);
        border-radius: 18px;
        padding: 22px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        box-shadow: 0 10px 28px rgba(15,23,42,.045);
        margin-bottom: 18px;
    }
    .detail-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #bfdbfe;
        background: var(--xl-blue-soft);
        color: #1d4ed8;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 11px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 10px;
    }
    .detail-hero h1 { margin: 0; font-size: 24px; font-weight: 950; letter-spacing: -.04em; color: var(--xl-text); }
    .detail-hero p { margin: 8px 0 0; color: var(--xl-muted); font-size: 13px; font-weight: 650; }
    
    .detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }
    
    .photo-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .photo-preview {
        aspect-ratio: 4/3;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--xl-border);
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .photo-preview.empty {
        color: var(--xl-muted);
        font-size: 12px;
        font-weight: 700;
        text-align: center;
        padding: 20px;
        border: 1px dashed var(--xl-border-dark);
    }
    
    .map-box {
        height: 250px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid var(--xl-border);
        overflow: hidden;
    }

    /* JAM RAMAI WIDGET STYLES */
    .jam-ramai-container { margin-top: 10px; font-family: 'Google Sans', Roboto, Arial, sans-serif; }
    .jam-ramai-day-selector { display: flex; gap: 4px; margin-bottom: 15px; }
    .jam-ramai-day { flex: 1; text-align: center; padding: 6px 0; font-size: 11px; font-weight: 700; color: #70757a; cursor: pointer; border-radius: 4px; transition: all 0.2s; }
    .jam-ramai-day:hover { background: #f1f3f4; color: #202124; }
    .jam-ramai-day.active { color: #1a73e8; background: #e8f0fe; font-weight: 800; }
    .jam-ramai-day.weekend { color: #d93025; }
    .jam-ramai-day.weekend.active { background: #fce8e6; }
    .jam-ramai-chart { display: flex; align-items: flex-end; height: 120px; gap: 2px; padding-bottom: 5px; border-bottom: 1px solid #dadce0; position: relative; }
    .jam-ramai-bar-group { flex: 1; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; position: relative; height: 100%; }
    .jam-ramai-bar { width: 100%; background: #8ab4f8; border-radius: 2px 2px 0 0; min-height: 2px; transition: height 0.5s ease-out; }
    .jam-ramai-bar.pejalan { background: #34a853; opacity: 0.8; width: 60%; position: absolute; bottom: 0; z-index: 2; }
    .jam-ramai-bar-group:hover .jam-ramai-tooltip { opacity: 1; visibility: visible; }
    .jam-ramai-tooltip { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #202124; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; white-space: nowrap; opacity: 0; visibility: hidden; pointer-events: none; z-index: 10; margin-bottom: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .jam-ramai-time-labels { display: flex; justify-content: space-between; margin-top: 5px; font-size: 10px; color: #70757a; }
    .jam-ramai-legend { display: flex; justify-content: center; align-items: center; margin-top: 15px; font-size: 11px; color: #5f6368; }
    .legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 4px; }
    .dot-motor { background: #8ab4f8; }
    .dot-pejalan { background: #34a853; }

    @media (max-width: 992px) {
        .detail-layout { grid-template-columns: 1fr; }
    }

    /* PRINT STYLES UNTUK EXPORT PDF */
    @media print {
        @page {
            size: A4 portrait;
            margin: 1cm;
        }

        /* Sembunyikan elemen UI yang tidak perlu dicetak */
        nav, header, footer, #sidebar, #topbar, .btn, .btn-worksheet-light, .detail-hero .d-flex.gap-2 {
            display: none !important;
        }
        
        /* Pastikan background warna tercetak (berlaku di browser Chromium/Safari) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        /* Hilangkan margin/sidebar dari halaman web */
        body, html, #app-shell, .worksheet-page {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        #main-area, body.sidebar-mini-surveyor #main-area {
            margin-left: 0 !important;
            width: 100% !important;
        }

        #page-content {
            padding: 0 !important;
        }
        
        /* Susun layout menjadi 1 kolom agar pas di kertas A4 portrait */
        .detail-layout {
            display: block !important;
        }
        
        /* Perapihan Header & Kartu */
        .detail-hero {
            padding: 15px !important;
            margin-bottom: 15px !important;
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            border-radius: 12px !important;
        }

        .worksheet-card {
            page-break-inside: avoid;
            margin-bottom: 15px !important;
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            border-radius: 12px !important;
        }
        
        /* Jadikan Grid KPI 2 baris (2x2) agar angka besar tidak terjepit */
        .worksheet-kpi-grid {
            page-break-inside: avoid;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 15px !important;
            margin-bottom: 15px !important;
        }
        
        .jam-ramai-container, .photo-grid, .worksheet-table-wrap {
            page-break-inside: avoid;
        }
        
        /* Perkecil padding tabel agar hemat ruang kertas */
        .worksheet-table td, .worksheet-table th {
            padding: 8px 12px !important;
            font-size: 13px !important;
        }

        /* Atur tinggi peta untuk cetak */
        .map-box {
            height: 250px !important;
        }

        /* Sembunyikan kontrol zoom Google Maps */
        .gmnoprint, .gm-fullscreen-control, .gm-style-cc {
            display: none !important;
        }
    }
</style>

@if(!$score)
<div class="worksheet-page">
    <div class="worksheet-hero justify-content-center">
        <div class="text-center text-muted fw-bold">Data tidak ditemukan.</div>
    </div>
</div>
@else
<div class="worksheet-page mb-4">
    
    <div class="detail-hero">
        <div>
            <div class="detail-kicker">
                <i class="bi bi-file-earmark-bar-graph"></i> Detail Site Score
            </div>
            <h1 class="d-flex align-items-center gap-2">
                {{ $score->lokasi ?? '-' }}
                <span class="badge bg-dark fs-6">{{ $score->tipe_outlet ?? 'LDP' }}</span>
            </h1>
            <p>
                <i class="bi bi-upc-scan"></i> {{ $score->kode_score ?? '-' }} &nbsp;&bull;&nbsp;
                <i class="bi bi-geo-alt"></i> {{ $score->kota ?? '-' }}, {{ $score->provinsi ?? '-' }} &nbsp;&bull;&nbsp;
                <i class="bi bi-calendar-event"></i> {{ $score->tanggal_survey ?? '-' }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('investor.surveyor.site-score.index') }}" class="btn-worksheet-light">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn-worksheet-light text-success" style="border-color: #22c55e;">
                <i class="bi bi-printer"></i> Export PDF
            </button>
            @if(in_array($score->workflow_status ?? 'PENDING', ['DRAFT', 'REVISION']))
            <a href="{{ route('investor.surveyor.site-score.edit', $score->id) }}" class="btn-worksheet-light text-primary" style="border-color: #3b82f6;">
                <i class="bi bi-pencil"></i> Edit / Revisi Data
            </a>
            @endif
        </div>
    </div>

    <div class="worksheet-kpi-grid mb-3" style="grid-template-columns: repeat(4, 1fr);">
        <div class="worksheet-kpi">
            <span>Final Score</span>
            <strong style="color: var(--xl-blue);">{{ number_format($score->final_percent ?? 0, 1) }}%</strong>
            <small>Score Kelayakan</small>
        </div>
        
        @php
            $rekomendasi = $score->rekomendasi ?? 'REJECTED';
            $statusClass = 'pill-rejected';
            $icon = 'bi-x-circle';
            if($rekomendasi === 'APPROVED') { $statusClass = 'pill-approved'; $icon = 'bi-check-circle'; }
            elseif($rekomendasi === 'CONSIDERATION') { $statusClass = 'pill-consideration'; $icon = 'bi-exclamation-circle'; }
            
            // Perhitungan RAB Total
            $totalRab = ($score->rab_renovasi ?? 0) + ($score->rab_kitchen ?? 0) + ($score->rab_signage ?? 0) + ($score->rab_furniture ?? 0) + ($score->rab_listrik ?? 0) + ($score->rab_air ?? 0) + ($score->rab_exhaust ?? 0) + ($score->rab_ac_kipas ?? 0) + ($score->rab_perizinan ?? 0) + ($score->rab_deposit_sewa ?? 0) + ($score->rab_biaya_opening ?? 0);
            
            // Perhitungan Proyeksi Balik Modal (BEP)
            $omsetPerHari = $calcData['grand_total_perhari'] ?? ($score->potensi_omset_perhari ?? 0);
            $labaPerBulan = $omsetPerHari * 30 * 0.20; // Asumsi 20% Net Margin
            $bepBulan = $labaPerBulan > 0 ? ($totalRab / $labaPerBulan) : 0;
        @endphp
        <div class="worksheet-kpi">
            <span>Rekomendasi</span>
            <div style="margin-top: 9px;">
                <span class="excel-status {{ $statusClass }} fs-6 px-3 py-2">
                    <i class="bi {{ $icon }} me-2"></i> {{ $rekomendasi }}
                </span>
            </div>
            <small class="mt-2">Status Penilaian</small>
        </div>
        
        <div class="worksheet-kpi" style="background-color: #f0fdf4; border-color: #bbf7d0;">
            <span style="color: #166534;">Est. Omset Harian</span>
            <strong style="color: #166534;">Rp {{ number_format($omsetPerHari, 0, ',', '.') }}</strong>
            <small style="color: #166534;">Setelah Margin Error (MoE)</small>
        </div>

        <div class="worksheet-kpi" style="background-color: #eff6ff; border-color: #bfdbfe;">
            <span style="color: #1e40af;">Grand Total Investasi</span>
            <strong style="color: #1e40af;">Rp {{ number_format($totalRab, 0, ',', '.') }}</strong>
            <small style="color: #1e40af;">Estimasi Biaya Buka Outlet</small>
        </div>

        <div class="worksheet-kpi" style="background-color: #fffbeb; border-color: #fde68a;">
            <span style="color: #92400e;">Est. BEP / Balik Modal</span>
            <strong style="color: #92400e;">{{ number_format($bepBulan, 1) }} Bulan</strong>
            <small style="color: #92400e;">Asumsi Laba Bersih 20%</small>
        </div>
        


        @php
            $tipe = $score->tipe_outlet ?? 'LDP';
            $omset = $score->potensi_omset_perhari ?? 0;
            
            $labelOmset = 'Di Bawah Standar';
            $labelClass = 'bg-secondary text-white';
            
            if ($tipe === 'LDP') {
                if ($omset >= 3500000) { $labelOmset = 'Plus'; $labelClass = 'bg-success text-white'; }
                elseif ($omset >= 2000000) { $labelOmset = 'Flagship'; $labelClass = 'bg-primary text-white'; }
                elseif ($omset >= 1400000) { $labelOmset = 'Express'; $labelClass = 'bg-warning text-dark'; }
                elseif ($omset >= 750000)  { $labelOmset = 'Mini'; $labelClass = 'bg-info text-dark'; }
            } else {
                // BDP
                if ($omset >= 3500000) { $labelOmset = 'Flagship'; $labelClass = 'bg-primary text-white'; }
                elseif ($omset >= 2000000) { $labelOmset = 'Express'; $labelClass = 'bg-warning text-dark'; }
                elseif ($omset >= 1400000) { $labelOmset = 'Mini'; $labelClass = 'bg-info text-dark'; }
            }
        @endphp
        <div class="worksheet-kpi">
            <span>Label Target</span>
            <div style="margin-top: 10px;">
                <span class="badge {{ $labelClass }} fs-6 px-3 py-2 w-100" style="letter-spacing: 1px;">
                    {{ strtoupper($labelOmset) }}
                </span>
            </div>
            <small class="mt-2">Kategori Outlet</small>
        </div>

        <div class="worksheet-kpi">
            <span>Traffic Lapangan</span>
            <div class="d-flex gap-3 mt-2" style="font-size: 15px; font-weight: 800;">
                <div title="Total Sepeda Motor"><i class="bi bi-scooter text-primary"></i> {{ number_format($score->total_motor ?? 0) }}</div>
                <div title="Total Pejalan Kaki"><i class="bi bi-person-walking text-success"></i> {{ number_format($score->total_pejalan ?? 0) }}</div>
            </div>
            <small class="mt-2">Total pengamatan mingguan</small>
        </div>
    </div>

    <div class="detail-layout">
        
        <!-- KOLOM KIRI (TABEL DATA) -->
        <div class="d-flex flex-column gap-3">
            <ul class="nav nav-tabs shadow-sm rounded-top" id="detailTabs" role="tablist" style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; padding-top: 8px; padding-left: 8px;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold px-4 py-2" id="feasibility-tab" data-bs-toggle="tab" data-bs-target="#feasibility-content" type="button" role="tab" style="color: #475569; border: none; border-bottom: 3px solid transparent;">
                        <i class="bi bi-graph-up-arrow me-2"></i>Feasibility Study
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-2" id="rab-tab" data-bs-toggle="tab" data-bs-target="#rab-content" type="button" role="tab" style="color: #475569; border: none; border-bottom: 3px solid transparent;">
                        <i class="bi bi-calculator me-2"></i>RAB & Kesimpulan
                    </button>
                </li>
            </ul>
            <style>
                .nav-tabs .nav-link.active { color: #2563eb !important; border-bottom: 3px solid #2563eb !important; background-color: transparent !important; }
                .nav-tabs .nav-link:hover:not(.active) { color: #3b82f6 !important; border-bottom: 3px solid #cbd5e1 !important; }
                @media print { .nav-tabs { display: none !important; } .tab-content > .tab-pane { display: flex !important; opacity: 1 !important; } }
            </style>
            <div class="tab-content" id="detailTabsContent" style="margin-top: 15px;">
                <!-- TAB 2: RAB & Kesimpulan -->
                <div class="tab-pane fade" id="rab-content" role="tabpanel" tabindex="0">
                    <div class="d-flex flex-column gap-3">
            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Data Fisik Bangunan & Akses</h5>
                        <p>Spesifikasi teknis, harga, dan visibilitas lokasi</p>
                    </div>
                </div>
                <div class="worksheet-table-wrap m-3">
                    <table class="worksheet-table">
                        <tbody>
                            <tr>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Tipe Bangunan</th>
                                <td style="width: 25%;">{{ $score->tipe_bangunan ?? '-' }}</td>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Luas Bangunan</th>
                                <td style="width: 25%;">{{ $score->luas_bangunan ?? 0 }} m2</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Status Sewa/Beli</th>
                                <td>{{ $score->status_sewa_beli ?? '-' }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Harga Sewa/Beli</th>
                                <td>Rp {{ number_format($score->harga_sewa ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Lebar Depan</th>
                                <td>{{ $score->lebar_depan ?? 0 }} m</td>
                                <th style="background:#f8fafc; font-weight:900;">Panjang</th>
                                <td>{{ $score->panjang_bangunan ?? 0 }} m</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Frontage</th>
                                <td>{{ $score->frontage ?? 0 }} m</td>
                                <th style="background:#f8fafc; font-weight:900;">Posisi Hook</th>
                                <td>{{ ($score->posisi_hook ?? 0) ? 'Ya' : 'Tidak' }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Terhalang Pohon/Kabel</th>
                                <td>{{ ($score->terhalang_pohon_kabel ?? 0) ? 'Ya' : 'Tidak' }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Ruang Signage</th>
                                <td>{{ ($score->ruang_signage ?? 0) ? 'Ada' : 'Tidak Ada' }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Penerangan Malam</th>
                                <td>{{ ($score->penerangan_malam ?? 0) ? 'Terang' : 'Gelap' }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Lebar Jalan</th>
                                <td>{{ $score->lebar_jalan ?? 0 }} m</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Jenis Jalan</th>
                                <td>{{ $score->jenis_jalan ?? '-' }}</td>
                                <th style="background:#f8fafc; font-weight:900;">U-Turn / Lampu Merah</th>
                                <td>{{ ($score->u_turn_lampu_merah ?? 0) ? 'Dekat' : 'Jauh' }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Jumlah Lantai</th>
                                <td>{{ $score->jumlah_lantai ?? 0 }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Kondisi Bangunan</th>
                                <td>{{ $score->kondisi_bangunan ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Terlihat dr Jalan Utama</th>
                                <td>{{ ($score->terlihat_jalan_utama ?? 0) ? 'Ya' : 'Tidak' }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Akses Mobil</th>
                                <td>{{ ($score->akses_mobil ?? 0) ? 'Bisa' : 'Sulit/Tidak' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php /* START RAB BLOCK COPIED FROM BELOW */ ?>
            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Rencana Anggaran Biaya (RAB)</h5>
                        <p>Estimasi biaya awal pembukaan outlet</p>
                    </div>
                </div>
                
                @php
                    $rabSipil = $score->rab_renovasi ?? 0;
                    $rabDapur = $score->rab_kitchen ?? 0;
                    $rabPromosi = ($score->rab_signage ?? 0) + ($score->rab_furniture ?? 0);
                    $rabME = ($score->rab_listrik ?? 0) + ($score->rab_air ?? 0) + ($score->rab_exhaust ?? 0) + ($score->rab_ac_kipas ?? 0);
                    $rabLainnya = ($score->rab_perizinan ?? 0) + ($score->rab_deposit_sewa ?? 0) + ($score->rab_biaya_opening ?? 0);
                    
                    $pctSipil = $totalRab > 0 ? ($rabSipil / $totalRab) * 100 : 0;
                    $pctDapur = $totalRab > 0 ? ($rabDapur / $totalRab) * 100 : 0;
                    $pctPromosi = $totalRab > 0 ? ($rabPromosi / $totalRab) * 100 : 0;
                    $pctME = $totalRab > 0 ? ($rabME / $totalRab) * 100 : 0;
                    $pctLainnya = $totalRab > 0 ? ($rabLainnya / $totalRab) * 100 : 0;
                @endphp
                
                <div class="m-3 mb-0">
                    <div class="progress" style="height: 12px; border-radius: 6px;">
                        @if($pctSipil > 0)<div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pctSipil }}%" title="Sipil/Partisi: {{ number_format($pctSipil, 1) }}%"></div>@endif
                        @if($pctDapur > 0)<div class="progress-bar bg-success" role="progressbar" style="width: {{ $pctDapur }}%" title="Dapur: {{ number_format($pctDapur, 1) }}%"></div>@endif
                        @if($pctPromosi > 0)<div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pctPromosi }}%" title="Promosi: {{ number_format($pctPromosi, 1) }}%"></div>@endif
                        @if($pctME > 0)<div class="progress-bar bg-info" role="progressbar" style="width: {{ $pctME }}%" title="Mekanikal/Elektrikal: {{ number_format($pctME, 1) }}%"></div>@endif
                        @if($pctLainnya > 0)<div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $pctLainnya }}%" title="Lainnya: {{ number_format($pctLainnya, 1) }}%"></div>@endif
                    </div>
                    <div class="d-flex justify-content-between mt-2" style="font-size: 11px; font-weight: 700; color: #64748b;">
                        <div><span style="display:inline-block;width:10px;height:10px;background:var(--bs-primary);border-radius:50%;margin-right:4px;"></span> Sipil/Transport</div>
                        <div><span style="display:inline-block;width:10px;height:10px;background:var(--bs-success);border-radius:50%;margin-right:4px;"></span> Dapur</div>
                        <div><span style="display:inline-block;width:10px;height:10px;background:var(--bs-warning);border-radius:50%;margin-right:4px;"></span> Promosi</div>
                        <div><span style="display:inline-block;width:10px;height:10px;background:var(--bs-info);border-radius:50%;margin-right:4px;"></span> ME (Listrik/Air)</div>
                        <div><span style="display:inline-block;width:10px;height:10px;background:var(--bs-secondary);border-radius:50%;margin-right:4px;"></span> Lainnya</div>
                    </div>
                </div>

                <div class="worksheet-table-wrap m-3">
                    <table class="worksheet-table">
                        <tbody>
                            <tr>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Sipil, Partisi & Transport</th>
                                <td style="width: 25%;">Rp {{ number_format($score->rab_renovasi ?? 0, 0, ',', '.') }}</td>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Peralatan Dapur / Zink</th>
                                <td style="width: 25%;">Rp {{ number_format($score->rab_kitchen ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Promosi (Signage)</th>
                                <td>Rp {{ number_format($score->rab_signage ?? 0, 0, ',', '.') }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Promosi (Furniture)</th>
                                <td>Rp {{ number_format($score->rab_furniture ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Instalasi Listrik</th>
                                <td>Rp {{ number_format($score->rab_listrik ?? 0, 0, ',', '.') }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Air & Sanitasi</th>
                                <td>Rp {{ number_format($score->rab_air ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Exhaust</th>
                                <td>Rp {{ number_format($score->rab_exhaust ?? 0, 0, ',', '.') }}</td>
                                <th style="background:#f8fafc; font-weight:900;">AC / Kipas</th>
                                <td>Rp {{ number_format($score->rab_ac_kipas ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Perizinan</th>
                                <td>Rp {{ number_format($score->rab_perizinan ?? 0, 0, ',', '.') }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Deposit Sewa</th>
                                <td>Rp {{ number_format($score->rab_deposit_sewa ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Biaya Opening</th>
                                <td>Rp {{ number_format($score->rab_biaya_opening ?? 0, 0, ',', '.') }}</td>
                                <th style="background:#f8fafc; font-weight:900;"></th>
                                <td></td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900; vertical-align: top;">Kelebihan Lokasi</th>
                                <td style="white-space: pre-line;">{{ $score->kelebihan_lokasi ?: '-' }}</td>
                                <th style="background:#f8fafc; font-weight:900; vertical-align: top;">Kekurangan Lokasi</th>
                                <td style="white-space: pre-line;">{{ $score->kekurangan_lokasi ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900; vertical-align: top;">Risiko</th>
                                <td colspan="3" style="white-space: pre-line;">{{ $score->risiko ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900; vertical-align: top;">Catatan Surveyor</th>
                                <td colspan="3" style="white-space: pre-line;">{{ $score->catatan ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" style="background:#1e3a8a; color:#fff; text-align:right; font-weight:900; font-size:16px;">GRAND TOTAL INVESTASI</th>
                                <td style="background:#1e3a8a; color:#fff; font-weight:900; font-size:16px;">Rp {{ number_format($totalRab, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            </div> <!-- End d-flex wrapper -->
            </div> <!-- End Tab 2 -->

            <!-- TAB 1: Feasibility Study -->
            <div class="tab-pane fade show active" id="feasibility-content" role="tabpanel" tabindex="0">
                <div class="d-flex flex-column gap-3">
            <?php /* END RAB BLOCK */ ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="worksheet-card h-100">
                        <div class="worksheet-card-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <div>
                                <h5 class="mb-1" style="font-weight: 800; color: #1e293b;">Parameter Kelayakan</h5>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Bobot analisis penambah & pengurang nilai</p>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-bold text-success mb-1" style="font-size: 15px;"><i class="bi bi-plus-circle-fill me-1"></i> Total Penambah</div>
                                    </div>
                                    <div class="fw-bold text-success" style="font-size: 22px;">+{{ number_format(($score->total_penambah ?? 0) * 100, 2) }}%</div>
                                </div>
                                @if(!empty($calcData['details_penambah']))
                                <div class="row g-2 mt-2 px-1">
                                    @foreach($calcData['details_penambah'] as $key => $val)
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-1" style="border-color: #e2e8f0; border-style: dashed !important;">
                                                <span class="text-muted" style="font-size: 11px;">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                <span class="text-success fw-bold" style="font-size: 11px;">+{{ number_format($val * 100, 1) }}%</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-muted mt-2" style="font-size: 12px;">Dari traffic, perumahan, fasilitas umum, dll.</div>
                                @endif
                            </div>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-bold text-danger mb-1" style="font-size: 15px;"><i class="bi bi-dash-circle-fill me-1"></i> Total Pengurang</div>
                                    </div>
                                    <div class="fw-bold text-danger" style="font-size: 22px;">-{{ number_format(($score->total_pengurang ?? 0) * 100, 2) }}%</div>
                                </div>
                                @if(!empty($calcData['details_pengurang']))
                                <div class="row g-2 mt-2 px-1">
                                    @foreach($calcData['details_pengurang'] as $key => $val)
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-1" style="border-color: #e2e8f0; border-style: dashed !important;">
                                                <span class="text-muted" style="font-size: 11px;">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                <span class="text-danger fw-bold" style="font-size: 11px;">-{{ number_format($val * 100, 1) }}%</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="text-muted mt-2" style="font-size: 12px;">Pinalti dari jumlah kompetitor di area.</div>
                                @endif
                            </div>

                            <div class="p-3 rounded" style="background-color: #f1f5f9; border: 1px solid #cbd5e1;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary fw-bold" style="font-size: 13px;">Threshold Approved</span>
                                    <b class="text-dark" style="font-size: 14px;">≥ {{ ($score->tipe_outlet ?? 'LDP') === 'LDP' ? '60' : '60' }}%</b>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-secondary fw-bold" style="font-size: 13px;">Margin of Error (MoE)</span>
                                    <span class="badge bg-danger">{{ $calcData['moe_percent'] ?? 20 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="worksheet-card h-100">
                        <div class="worksheet-card-header" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <div>
                                <h5 class="mb-1" style="font-weight: 800; color: #1e293b;">Proyeksi Chicken Unit & Omset</h5>
                                <p class="mb-0 text-muted" style="font-size: 13px;">Breakdown perhitungan potensi penjualan</p>
                            </div>
                        </div>
                        <div class="p-4">
                            @php
                                $totalCuHari = $calcData['total_potensi_cu'] ?? 0;
                                $omsetHari = $calcData['grand_total_perhari'] ?? 0;
                                $averageCheck = $calcData['average_check'] ?? ($score->average_check ?? 21000);
                            @endphp
                            
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom" style="border-style: dashed !important; border-color: #e2e8f0 !important;">
                                <span class="text-secondary fw-bold" style="font-size: 13px;"><i class="bi bi-cash-stack me-1"></i> Average Check</span>
                                <b class="text-dark" style="font-size: 14px;">Rp {{ number_format($averageCheck, 0, ',', '.') }} / CU</b>
                            </div>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <div class="p-3 rounded text-center h-100" style="background: #eff6ff; border: 1px solid #bfdbfe;">
                                        <div class="text-primary fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px;">ESTIMASI CU (HARIAN)</div>
                                        <div class="fw-bold text-dark" style="font-size: 18px;">{{ number_format($totalCuHari, 1, ',', '.') }} CU</div>
                                        <div class="text-muted mt-1" style="font-size: 11px;">{{ number_format($totalCuHari * 30, 0, ',', '.') }} / bln</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 rounded text-center h-100" style="background: #fdf4ff; border: 1px solid #f5d0fe;">
                                        <div class="text-purple fw-bold mb-1" style="font-size: 11px; letter-spacing: 0.5px; color: #a21caf;">OMSET MINGGUAN</div>
                                        <div class="fw-bold text-dark" style="font-size: 18px;">Rp {{ number_format($omsetHari * 7, 0, ',', '.') }}</div>
                                        <div class="text-muted mt-1" style="font-size: 11px;">(7 Hari Operasional)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-3 rounded text-center" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border: 1px solid #a7f3d0; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1);">
                                <div class="text-success fw-bold mb-1" style="font-size: 0.85rem; letter-spacing: 1px;">ESTIMASI OMSET BULANAN</div>
                                <div class="text-success fw-bold" style="font-size: 1.8rem;">Rp {{ number_format($omsetHari * 30, 0, ',', '.') }}</div>
                                <div class="text-muted mt-2" style="font-size: 11px;">Subtotal (Sblm MoE): Rp {{ number_format(($calcData['subtotal_perhari'] ?? 0) * 30, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Breakdown Data Lapangan</h5>
                        <p>Detail fasilitas, kompetitor, dan perumahan (radius 1-1.5km)</p>
                    </div>
                </div>
                <div class="worksheet-table-wrap m-3">
                    @php
                        $multiplierRumah = 1.0;
                        if (($score->formula_type ?? 'DEFAULT') === 'CUSTOM' && !empty($score->custom_weights_json)) {
                            $customConfig = json_decode($score->custom_weights_json, true) ?? [];
                            if (isset($customConfig['multipliers']['rumah']) && $customConfig['multipliers']['rumah'] > 0) {
                                $multiplierRumah = (float) $customConfig['multipliers']['rumah'];
                            }
                        }
                    @endphp
                    <table class="worksheet-table">
                        <tbody>
                            <tr>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Rumah Q1</th>
                                <td style="width: 25%;">{{ number_format(($score->rumah_q1 ?? 0) * $multiplierRumah) }}</td>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Rumah Q2</th>
                                <td style="width: 25%;">{{ number_format(($score->rumah_q2 ?? 0) * $multiplierRumah) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Rumah Q3</th>
                                <td>{{ number_format(($score->rumah_q3 ?? 0) * $multiplierRumah) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Rumah Q4</th>
                                <td>{{ number_format(($score->rumah_q4 ?? 0) * $multiplierRumah) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Sekolah</th>
                                <td>{{ number_format($score->sekolah ?? 0) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Market/Supermarket</th>
                                <td>{{ number_format($score->market ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Perkantoran</th>
                                <td>{{ number_format($score->perkantoran ?? 0) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Fasilitas Kesehatan</th>
                                <td>{{ number_format($score->kesehatan ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Kompetitor Geprek</th>
                                <td>{{ number_format($score->kompetitor_geprek ?? 0) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Kompetitor Lokal</th>
                                <td>{{ number_format($score->kompetitor_lokal ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Harga Kompetitor</th>
                                <td class="fw-bold text-danger">Rp {{ number_format($score->harga_kompetitor ?? 0, 0, ',', '.') }}</td>
                                <th style="background:#f8fafc; font-weight:900;"></th>
                                <td></td>
                            </tr>
                            <!-- Kelebihan & Risiko dipindah ke tab RAB -->
                        </tbody>
                    </table>
                </div>
            </div>
            </div> <!-- End d-flex wrapper -->
            </div> <!-- End Tab 1 -->
            </div> <!-- End detailTabsContent -->
        </div>
        
        <!-- KOLOM KANAN (MAP & FOTO) -->
        <div class="d-flex flex-column gap-3">
            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Peta Lokasi</h5>
                        <p>Titik koordinat hasil survey</p>
                    </div>
                    @if(!empty($score->maps_url))
                    <a href="{{ $score->maps_url }}" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius: 8px; font-weight: 800;">
                        <i class="bi bi-map"></i> Buka Maps
                    </a>
                    @endif
                </div>
                <div class="worksheet-card-body">
                    @if(!empty($score->latitude) && !empty($score->longitude))
                        <div id="detailMap" class="map-box"></div>
                    @else
                        <div class="map-box d-flex align-items-center justify-content-center bg-light text-muted fw-bold">
                            Tidak ada koordinat GPS
                        </div>
                    @endif
                </div>
            </div>

            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Jam Ramai (Estimasi)</h5>
                        <p>Berdasarkan rata-rata traffic harian</p>
                    </div>
                </div>
                <div class="worksheet-card-body">
                    <div class="jam-ramai-container">
                        <div class="jam-ramai-day-selector" id="jamRamaiDaySelector">
                            <div class="jam-ramai-day active" data-day="Sen">Sen</div>
                            <div class="jam-ramai-day" data-day="Sel">Sel</div>
                            <div class="jam-ramai-day" data-day="Rab">Rab</div>
                            <div class="jam-ramai-day" data-day="Kam">Kam</div>
                            <div class="jam-ramai-day" data-day="Jum">Jum</div>
                            <div class="jam-ramai-day weekend" data-day="Sab">Sab</div>
                            <div class="jam-ramai-day weekend" data-day="Min">Min</div>
                        </div>
                        <div class="jam-ramai-chart" id="jamRamaiChart"></div>
                        <div class="jam-ramai-time-labels">
                            <span>06:00</span>
                            <span>12:00</span>
                            <span>18:00</span>
                            <span>23:00</span>
                        </div>
                        <div class="jam-ramai-legend">
                            <span class="legend-dot dot-motor"></span> Motor
                            <span class="legend-dot dot-pejalan" style="margin-left: 12px;"></span> Pejalan
                        </div>
                    </div>
                </div>
            </div>

            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Galeri Foto</h5>
                        <p>{{ $photos->count() }} foto lapangan terlampir</p>
                    </div>
                </div>
                <div class="worksheet-card-body">
                    <div class="photo-grid">
                        @forelse($photos as $photo)
                            <div class="photo-preview">
                                <a href="{{ asset('storage/'.$photo->path) }}" target="_blank" title="Klik untuk perbesar">
                                    <img src="{{ asset('storage/'.$photo->path) }}" alt="Foto Survey">
                                </a>
                            </div>
                        @empty
                            <div class="photo-preview empty" style="grid-column: span 2;">
                                <i class="bi bi-camera mb-2" style="font-size: 24px; display:block;"></i>
                                Belum ada foto terlampir
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endif

@push('scripts')
@if($score && !empty($score->latitude) && !empty($score->longitude))
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places,geometry"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lat = {{ (float)$score->latitude }};
        const lng = {{ (float)$score->longitude }};
        const center = { lat: lat, lng: lng };
        
        const mapBox = document.getElementById('detailMap');
        
        const map = new google.maps.Map(mapBox, {
            center: center,
            zoom: 15,
            mapTypeControl: false,
            streetViewControl: false,
        });

        // Target Marker
        new google.maps.Marker({
            position: center,
            map: map,
            icon: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
            title: 'Lokasi Target'
        });

        // Radius Circles
        new google.maps.Circle({
            strokeColor: "#3b82f6", strokeOpacity: 0.8, strokeWeight: 2,
            fillColor: "#3b82f6", fillOpacity: 0.1,
            map: map, center: center, radius: {{ $score->scan_radius_fasum ?? 750 }}
        });
        new google.maps.Circle({
            strokeColor: "#ef4444", strokeOpacity: 0.8, strokeWeight: 2,
            fillColor: "#ef4444", fillOpacity: 0.05,
            map: map, center: center, radius: {{ $score->scan_radius_kompetitor ?? 500 }}
        });

        // Legend
        const legend = document.createElement('div');
        legend.style.background = 'white';
        legend.style.padding = '10px';
        legend.style.margin = '10px';
        legend.style.borderRadius = '8px';
        legend.style.boxShadow = '0 2px 6px rgba(0,0,0,0.3)';
        legend.style.fontSize = '12px';
        legend.style.lineHeight = '1.5';
        legend.innerHTML = `
            <div style="font-weight:bold;margin-bottom:8px;font-size:14px;">Legenda Maps</div>
            <div><img src="http://maps.google.com/mapfiles/ms/icons/red-dot.png" width="16" style="vertical-align:middle"> Lokasi Target</div>
            <div><img src="http://maps.google.com/mapfiles/ms/icons/blue-dot.png" width="16" style="vertical-align:middle"> Sekolah</div>
            <div><img src="http://maps.google.com/mapfiles/ms/icons/purple-dot.png" width="16" style="vertical-align:middle"> Kampus</div>
            <div><img src="http://maps.google.com/mapfiles/ms/icons/pink-dot.png" width="16" style="vertical-align:middle"> Kesehatan</div>
            <div><img src="http://maps.google.com/mapfiles/ms/icons/green-dot.png" width="16" style="vertical-align:middle"> Market / Mall</div>
            <div><img src="http://maps.google.com/mapfiles/ms/icons/ltblue-dot.png" width="16" style="vertical-align:middle"> Perkantoran</div>
            <div><img src="http://maps.google.com/mapfiles/ms/icons/orange-dot.png" width="16" style="vertical-align:middle"> Kompetitor (Geprek)</div>
            <div><img src="http://maps.google.com/mapfiles/ms/icons/yellow-dot.png" width="16" style="vertical-align:middle"> F&B Lokal</div>
        `;
        map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(legend);

        // Traffic Generator (Places API)
        const service = new google.maps.places.PlacesService(map);
        function searchFasum(radius, type, iconPath) {
            service.nearbySearch({ location: center, radius: radius, type: type }, function(results, status) {
                if (status === google.maps.places.PlacesServiceStatus.OK && results) {
                    results.forEach(place => {
                        new google.maps.Marker({
                            map: map, position: place.geometry.location, icon: iconPath, title: place.name
                        });
                    });
                }
            });
        }
        function searchKeyword(radius, keyword, iconPath) {
            service.nearbySearch({ location: center, radius: radius, keyword: keyword }, function(results, status) {
                if (status === google.maps.places.PlacesServiceStatus.OK && results) {
                    results.forEach(place => {
                        new google.maps.Marker({
                            map: map, position: place.geometry.location, icon: iconPath, title: place.name
                        });
                    });
                }
            });
        }

        const radFasum = {{ $score->scan_radius_fasum ?? 750 }};
        searchFasum(radFasum, 'school', 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png');
        searchFasum(radFasum, 'university', 'http://maps.google.com/mapfiles/ms/icons/purple-dot.png');
        searchFasum(radFasum, 'hospital', 'http://maps.google.com/mapfiles/ms/icons/pink-dot.png');
        searchFasum(radFasum, 'supermarket', 'http://maps.google.com/mapfiles/ms/icons/green-dot.png');
        searchKeyword(radFasum, 'perkantoran', 'http://maps.google.com/mapfiles/ms/icons/ltblue-dot.png');
        searchKeyword(radFasum, 'pabrik', 'http://maps.google.com/mapfiles/ms/icons/ltblue-dot.png');

        const radKomp = {{ $score->scan_radius_kompetitor ?? 500 }};
        searchKeyword(radKomp, 'ayam geprek', 'http://maps.google.com/mapfiles/ms/icons/orange-dot.png');
        searchFasum(radKomp, 'restaurant', 'http://maps.google.com/mapfiles/ms/icons/yellow-dot.png');
    });
</script>
@endif

@if($score)
<script>
    @php
        $multiplierTraffic = 1.0;
        if (($score->formula_type ?? 'DEFAULT') === 'CUSTOM' && !empty($score->custom_weights_json)) {
            $customConfig = json_decode($score->custom_weights_json, true) ?? [];
            if (isset($customConfig['multipliers']['traffic']) && $customConfig['multipliers']['traffic'] > 0) {
                $multiplierTraffic = (float) $customConfig['multipliers']['traffic'];
            }
        }
    @endphp
    const trafficData = {
        wd: {
            mPagi: {{ ($score->motor_weekday_pagi ?? 0) * $multiplierTraffic }},
            mSiang: {{ ($score->motor_weekday_siang ?? 0) * $multiplierTraffic }},
            mSore: {{ ($score->motor_weekday_sore ?? 0) * $multiplierTraffic }},
            pPagi: {{ ($score->pejalan_weekday_pagi ?? 0) * $multiplierTraffic }},
            pSiang: {{ ($score->pejalan_weekday_siang ?? 0) * $multiplierTraffic }},
            pSore: {{ ($score->pejalan_weekday_sore ?? 0) * $multiplierTraffic }},
        },
        we: {
            mPagi: {{ ($score->motor_weekend_pagi ?? 0) * $multiplierTraffic }},
            mSiang: {{ ($score->motor_weekend_siang ?? 0) * $multiplierTraffic }},
            mSore: {{ ($score->motor_weekend_sore ?? 0) * $multiplierTraffic }},
            pPagi: {{ ($score->pejalan_weekend_pagi ?? 0) * $multiplierTraffic }},
            pSiang: {{ ($score->pejalan_weekend_siang ?? 0) * $multiplierTraffic }},
            pSore: {{ ($score->pejalan_weekend_sore ?? 0) * $multiplierTraffic }},
        }
    };

    function synthesizeTrafficCurveJS(pagi, siang, sore, day) {
        let baseCurve = {};

        // Google Maps-like organic curves per day
        if (day === 'Jum') {
            // Friday - evening peak is higher and stretches later
            baseCurve = {
                6: 0.2, 7: 0.5, 8: 0.7, 9: 0.5, 10: 0.4,
                11: 0.6, 12: 1.0, 13: 1.0, 14: 0.7, 15: 0.6,
                16: 0.8, 17: 0.9, 18: 1.0, 19: 1.2, 20: 1.1, 21: 0.9, 22: 0.6, 23: 0.4
            };
        } else if (day === 'Sab') {
            // Saturday - slow morning, steady afternoon, very high night
            baseCurve = {
                6: 0.1, 7: 0.2, 8: 0.4, 9: 0.6, 10: 0.7,
                11: 0.8, 12: 1.0, 13: 1.0, 14: 0.9, 15: 0.9,
                16: 1.0, 17: 1.0, 18: 1.1, 19: 1.3, 20: 1.4, 21: 1.2, 22: 0.9, 23: 0.6
            };
        } else if (day === 'Min') {
            // Sunday - morning activity (sports/CFD), high lunch, tapers early night
            baseCurve = {
                6: 0.4, 7: 0.7, 8: 0.9, 9: 0.8, 10: 0.7,
                11: 0.8, 12: 1.0, 13: 0.9, 14: 0.8, 15: 0.7,
                16: 0.8, 17: 0.9, 18: 0.9, 19: 0.8, 20: 0.6, 21: 0.4, 22: 0.2, 23: 0.1
            };
        } else {
            // Standard Weekday (Sen, Sel, Rab, Kam) - typical commute patterns
            baseCurve = {
                6: 0.2, 7: 0.6, 8: 0.8, 9: 0.5, 10: 0.4,
                11: 0.6, 12: 1.0, 13: 0.8, 14: 0.6, 15: 0.5,
                16: 0.7, 17: 0.9, 18: 1.0, 19: 0.8, 20: 0.6, 21: 0.4, 22: 0.3, 23: 0.1
            };
        }

        // Dynamically calculate sums so the distribution is perfectly accurate to the inputs
        let sumMorning = 0, sumNoon = 0, sumEvening = 0;
        for (let h = 6; h <= 10; h++) sumMorning += baseCurve[h];
        for (let h = 11; h <= 15; h++) sumNoon += baseCurve[h];
        for (let h = 16; h <= 23; h++) sumEvening += baseCurve[h];

        sumMorning = sumMorning || 1;
        sumNoon = sumNoon || 1;
        sumEvening = sumEvening || 1;

        const hourlyData = {};
        for (let h = 6; h <= 23; h++) {
            let count = 0;
            if (h <= 10) count = pagi * (baseCurve[h] / sumMorning);
            else if (h <= 15) count = siang * (baseCurve[h] / sumNoon);
            else count = sore * (baseCurve[h] / sumEvening);

            // Add tiny 2-4% organic variance to make it look realistic
            let variance = 1.0 + ((Math.random() * 0.08) - 0.04);
            hourlyData[h] = Math.max(0, Math.round(count * variance));
        }
        return hourlyData;
    }

    function updateJamRamai() {
        const activeDayEl = document.querySelector('.jam-ramai-day.active');
        const day = activeDayEl ? activeDayEl.dataset.day : 'Sen';
        const isWeekend = ['Sab', 'Min'].includes(day);
        const data = isWeekend ? trafficData.we : trafficData.wd;

        const motorCurve = synthesizeTrafficCurveJS(data.mPagi, data.mSiang, data.mSore, day);
        const pejalanCurve = synthesizeTrafficCurveJS(data.pPagi, data.pSiang, data.pSore, day);

        const hoursData = [];
        let maxVal = 0;

        for (let h = 6; h <= 23; h++) {
            let motor = motorCurve[h];
            let pejalan = pejalanCurve[h];

            if (motor > maxVal) maxVal = motor;
            hoursData.push({ hour: h, motor: motor, pejalan: pejalan });
        }

        const chart = document.getElementById('jamRamaiChart');
        if (!chart) return;
        chart.innerHTML = '';
        
        if (maxVal === 0) {
            chart.innerHTML = '<div style="width:100%; text-align:center; color:#9aa0a6; font-size:12px; margin-bottom:10px;">Tidak ada data traffic.</div>';
            return;
        }

        hoursData.forEach(d => {
            const heightM = (d.motor / maxVal) * 100;
            const heightP = (d.pejalan / maxVal) * 100;
            const hourFmt = d.hour.toString().padStart(2, '0') + ':00';
            
            const group = document.createElement('div');
            group.className = 'jam-ramai-bar-group';
            
            const barM = document.createElement('div');
            barM.className = 'jam-ramai-bar';
            barM.style.height = Math.max(2, heightM) + '%';
            
            const barP = document.createElement('div');
            barP.className = 'jam-ramai-bar pejalan';
            barP.style.height = Math.max(0, heightP) + '%';
            
            const tooltip = document.createElement('div');
            tooltip.className = 'jam-ramai-tooltip';
            tooltip.innerText = `${hourFmt}\nMotor: ${d.motor}\nPejalan: ${d.pejalan}`;
            
            group.appendChild(barM);
            group.appendChild(barP);
            group.appendChild(tooltip);
            chart.appendChild(group);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.jam-ramai-day').forEach(el => {
            el.addEventListener('click', function() {
                document.querySelectorAll('.jam-ramai-day').forEach(d => d.classList.remove('active'));
                this.classList.add('active');
                updateJamRamai();
            });
        });
        updateJamRamai();
    });
</script>
@endif
@endpush

@include('Surveyor.layouts.footer')

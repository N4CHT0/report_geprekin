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
            <h1>{{ $score->lokasi ?? '-' }}</h1>
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
        
        <div class="worksheet-kpi">
            <span>Est. Omset Harian</span>
            <strong>Rp {{ number_format($score->potensi_omset_perhari ?? 0, 0, ',', '.') }}</strong>
            <small>Berdasarkan Score & Traffic</small>
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
                        </tbody>
                    </table>
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
                    <table class="worksheet-table">
                        <tbody>
                            <tr>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Rumah Q1</th>
                                <td style="width: 25%;">{{ number_format($score->rumah_q1 ?? 0) }}</td>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Rumah Q2</th>
                                <td style="width: 25%;">{{ number_format($score->rumah_q2 ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Rumah Q3</th>
                                <td>{{ number_format($score->rumah_q3 ?? 0) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Rumah Q4</th>
                                <td>{{ number_format($score->rumah_q4 ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Sekolah</th>
                                <td>{{ number_format($score->sekolah ?? 0) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Kampus</th>
                                <td>{{ number_format($score->kampus ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Market/Supermarket</th>
                                <td>{{ number_format($score->market ?? 0) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Perkantoran</th>
                                <td>{{ number_format($score->perkantoran ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Fasilitas Kesehatan</th>
                                <td>{{ number_format($score->kesehatan ?? 0) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Pabrik</th>
                                <td>{{ number_format($score->pabrik ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Kompetitor Geprek</th>
                                <td>{{ number_format($score->kompetitor_geprek ?? 0) }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Kompetitor Lokal</th>
                                <td>{{ number_format($score->kompetitor_lokal ?? 0) }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Jarak Kompetitor Terdekat</th>
                                <td>{{ number_format($score->jarak_kompetitor ?? 0) }} m</td>
                                <th style="background:#f8fafc; font-weight:900;">Harga Kompetitor</th>
                                <td class="fw-bold text-danger">Rp {{ number_format($score->harga_kompetitor ?? 0, 0, ',', '.') }}</td>
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
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Rencana Anggaran Biaya (RAB)</h5>
                        <p>Estimasi biaya awal pembukaan outlet</p>
                    </div>
                </div>
                <div class="worksheet-table-wrap m-3">
                    <table class="worksheet-table">
                        <tbody>
                            <tr>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Renovasi</th>
                                <td style="width: 25%;">Rp {{ number_format($score->rab_renovasi ?? 0, 0, ',', '.') }}</td>
                                <th style="width: 25%; background:#f8fafc; font-weight:900;">Peralatan Dapur</th>
                                <td style="width: 25%;">Rp {{ number_format($score->rab_kitchen ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Signage</th>
                                <td>Rp {{ number_format($score->rab_signage ?? 0, 0, ',', '.') }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Furniture</th>
                                <td>Rp {{ number_format($score->rab_furniture ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#f8fafc; font-weight:900;">Listrik</th>
                                <td>Rp {{ number_format($score->rab_listrik ?? 0, 0, ',', '.') }}</td>
                                <th style="background:#f8fafc; font-weight:900;">Air</th>
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
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Analisis Score</h5>
                        <p>Bobot perbandingan elemen penambah dan pengurang nilai.</p>
                    </div>
                </div>
                <div class="worksheet-table-wrap m-3">
                    <table class="worksheet-table">
                        <thead>
                            <tr>
                                <th>Elemen</th>
                                <th>Kontribusi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-success"><i class="bi bi-plus-circle me-1"></i> Total Penambah</td>
                                <td class="fw-bold text-success">+{{ number_format(($score->total_penambah ?? 0) * 100, 2) }}%</td>
                                <td>Dari traffic, perumahan, fasilitas umum, dll.</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-danger"><i class="bi bi-dash-circle me-1"></i> Total Pengurang</td>
                                <td class="fw-bold text-danger">-{{ number_format(($score->total_pengurang ?? 0) * 100, 2) }}%</td>
                                <td>Pinalti dari jumlah kompetitor di area tersebut.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="worksheet-card">
                <div class="worksheet-card-header">
                    <div>
                        <h5>Estimasi Potensi Omset (Harian)</h5>
                        <p>Breakdown perhitungan omset dan klasifikasi label outlet</p>
                    </div>
                </div>
                <div class="worksheet-table-wrap m-3">
                    <table class="worksheet-table text-end">
                        <tbody>
                            <tr>
                                <th style="background:#1e3a8a; color:#fff; text-align:left; font-weight:800; width:50%; font-size:15px;">Sub Total</th>
                                <td class="fw-bold" style="background:#1e3a8a; color:#fff; width:50%; font-size:15px;">Rp {{ number_format($calcData['subtotal_perhari'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#dc2626; color:#fff; text-align:left; font-weight:800; font-size:15px;">% MoE (Margin of Error)</th>
                                <td class="fw-bold" style="background:#dc2626; color:#fff; font-size:15px;">{{ $calcData['moe_percent'] ?? 20 }}%</td>
                            </tr>
                            <tr>
                                <th style="background:#166534; color:#fff; text-align:left; font-weight:800; font-size:15px;">Grand Total After MoE</th>
                                <td class="fw-bold" style="background:#166534; color:#fff; font-size:15px;">Rp {{ number_format($calcData['grand_total_perhari'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th style="background:#831843; color:#fff; text-align:left; font-weight:800; font-size:15px;">Label Outlet</th>
                                <td class="fw-bold" style="background:#831843; color:#fff; font-size:18px; text-transform:uppercase;">{{ $calcData['label_outlet'] ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lat = {{ (float)$score->latitude }};
        const lng = {{ (float)$score->longitude }};
        const embedUrl = 'https://maps.google.com/maps?q=' + lat + ',' + lng + '&hl=id&z=16&output=embed';
        
        const mapBox = document.getElementById('detailMap');
        mapBox.innerHTML = '<iframe src="' + embedUrl + '" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>';
    });
</script>
@endif

@if($score)
<script>
    const trafficData = {
        wd: {
            mPagi: {{ $score->motor_weekday_pagi ?? 0 }},
            mSiang: {{ $score->motor_weekday_siang ?? 0 }},
            mSore: {{ $score->motor_weekday_sore ?? 0 }},
            pPagi: {{ $score->pejalan_weekday_pagi ?? 0 }},
            pSiang: {{ $score->pejalan_weekday_siang ?? 0 }},
            pSore: {{ $score->pejalan_weekday_sore ?? 0 }},
        },
        we: {
            mPagi: {{ $score->motor_weekend_pagi ?? 0 }},
            mSiang: {{ $score->motor_weekend_siang ?? 0 }},
            mSore: {{ $score->motor_weekend_sore ?? 0 }},
            pPagi: {{ $score->pejalan_weekend_pagi ?? 0 }},
            pSiang: {{ $score->pejalan_weekend_siang ?? 0 }},
            pSore: {{ $score->pejalan_weekend_sore ?? 0 }},
        }
    };

    function synthesizeTrafficCurveJS(pagi, siang, sore) {
        const baseCurve = {
            6: 0.2, 7: 0.4, 8: 0.5, 9: 0.4, 10: 0.5,
            11: 0.7, 12: 1.0, 13: 0.8, 14: 0.6, 15: 0.5,
            16: 0.6, 17: 0.8, 18: 1.0, 19: 0.9, 20: 0.8, 21: 0.6, 22: 0.4, 23: 0.2
        };

        const sumMorning = 2.0;
        const sumNoon    = 3.6;
        const sumEvening = 5.3;
        
        const hourlyData = {};
        for (let h = 6; h <= 23; h++) {
            let count = 0;
            if (h <= 10) count = pagi * (baseCurve[h] / sumMorning);
            else if (h <= 15) count = siang * (baseCurve[h] / sumNoon);
            else count = sore * (baseCurve[h] / sumEvening);
            hourlyData[h] = Math.round(count);
        }
        return hourlyData;
    }

    function updateJamRamai() {
        const activeDayEl = document.querySelector('.jam-ramai-day.active');
        const day = activeDayEl ? activeDayEl.dataset.day : 'Sen';
        const isWeekend = ['Sab', 'Min'].includes(day);
        const data = isWeekend ? trafficData.we : trafficData.wd;

        const motorCurve = synthesizeTrafficCurveJS(data.mPagi, data.mSiang, data.mSore);
        const pejalanCurve = synthesizeTrafficCurveJS(data.pPagi, data.pSiang, data.pSore);

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

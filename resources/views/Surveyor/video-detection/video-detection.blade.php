@section('title', 'Video Detection Surveyor')
@section('breadcrumb', 'Surveyor / Video Detection')

@include('Surveyor.layouts.header')
@include('Surveyor.layouts.site-score-excel-style')

@php
    $activeJobId = session('job_id');
@endphp

<style>
.video-hero {
    border: 1px solid #d0d7e2;
    border-radius: 18px;
    background: linear-gradient(135deg, #ffffff, #eff6ff);
    padding: 24px;
    margin-bottom: 18px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 18px;
    flex-wrap: wrap;
}

.video-kicker {
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: #1d4ed8;
    margin-bottom: 8px;
}

.video-hero h1 {
    margin: 0;
    font-size: 30px;
    font-weight: 950;
    letter-spacing: -.04em;
    color: #0f172a;
}

.video-hero p {
    margin: 8px 0 0;
    color: #64748b;
    font-size: 13px;
    font-weight: 650;
    max-width: 820px;
}

.video-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    background: #dbeafe;
    color: #1d4ed8;
    padding: 8px 13px;
    font-size: 12px;
    font-weight: 900;
}

.video-badge-dot {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: #2563eb;
    animation: vdPulse 1.1s infinite;
}

@keyframes vdPulse {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.55); opacity: .35; }
    100% { transform: scale(1); opacity: 1; }
}

.video-card {
    background: #fff;
    border: 1px solid #d0d7e2;
    border-radius: 18px;
    box-shadow: 0 8px 24px rgba(15,23,42,.045);
    overflow: hidden;
}

.video-card-header {
    padding: 17px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 14px;
}

.video-card-header h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 950;
    color: #0f172a;
}

.video-card-header p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 12px;
    font-weight: 650;
}

.video-card-body {
    padding: 20px;
}

.video-processing-box {
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
    border: 1px solid #bfdbfe;
    border-radius: 18px;
    padding: 24px;
    min-height: 100%;
}

.video-processing-box::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);
    transform: translateX(-100%);
    animation: loadingShine 2.2s infinite;
    pointer-events: none;
}

@keyframes loadingShine {
    100% { transform: translateX(100%); }
}

.processing-hero {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
}

.processing-title {
    font-size: 24px;
    font-weight: 950;
    letter-spacing: -.04em;
    margin: 0;
    color: #0f172a;
}

.processing-subtitle {
    margin-top: 7px;
    color: #64748b;
    font-size: 13px;
    font-weight: 650;
}

.processing-icon {
    width: 72px;
    height: 72px;
    border-radius: 22px;
    background: #2563eb;
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 32px;
    box-shadow: 0 14px 30px rgba(37,99,235,.25);
}

.live-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 13px;
    border-radius: 999px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 900;
}

.live-status-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: #2563eb;
    animation: vdPulse 1.1s infinite;
}

.ai-progress {
    width: 100%;
    height: 18px;
    border-radius: 999px;
    overflow: hidden;
    background: #dbeafe;
    margin-top: 18px;
}

.ai-progress-bar {
    height: 100%;
    width: 18%;
    border-radius: 999px;
    background: linear-gradient(90deg, #2563eb, #60a5fa);
    transition: all .45s ease;
}

.ai-progress-label {
    margin-top: 10px;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    font-size: 12px;
    color: #475569;
    font-weight: 750;
}

.live-metric-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-top: 22px;
    position: relative;
    z-index: 1;
}

.live-metric {
    background: #fff;
    border: 1px solid #dbeafe;
    border-radius: 16px;
    padding: 16px;
}

.live-metric span {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .08em;
    font-weight: 950;
    color: #64748b;
}

.live-metric strong {
    display: block;
    margin-top: 9px;
    font-size: 30px;
    line-height: 1;
    font-weight: 950;
    color: #0f172a;
}

.live-stage-list {
    margin-top: 22px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    z-index: 1;
}

.live-stage {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px 14px;
}

.live-stage-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.live-stage-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #eff6ff;
    color: #2563eb;
    display: grid;
    place-items: center;
    font-size: 18px;
}

.live-stage-title {
    font-size: 13px;
    font-weight: 900;
    color: #0f172a;
}

.live-stage-subtitle {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
    font-weight: 650;
}

.live-stage-status {
    font-size: 11px;
    font-weight: 950;
    border-radius: 999px;
    padding: 6px 12px;
    background: #dbeafe;
    color: #1d4ed8;
    white-space: nowrap;
}

.live-stage-status.done {
    background: #dcfce7;
    color: #166534;
}

.live-stage-status.waiting {
    background: #f1f5f9;
    color: #475569;
}

.live-stage-status.failed {
    background: #fee2e2;
    color: #991b1b;
}

.result-table thead th {
    background: #eef4ff !important;
    border: 1px solid #d0d7e2;
    font-size: 12px;
    font-weight: 950;
    text-transform: uppercase;
    color: #334155;
    white-space: nowrap;
}

.result-table tbody td {
    border: 1px solid #d0d7e2;
    font-size: 13px;
    vertical-align: middle;
}

.object-pill {
    display: inline-flex;
    min-width: 44px;
    height: 32px;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #eff6ff;
    color: #1d4ed8;
    font-weight: 950;
}

@media(max-width:991px) {
    .live-metric-grid {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style>

<div class="video-hero">
    <div>
        <div class="video-kicker">Video Object Detection</div>
        <h1>Deteksi Orang, Motor, Mobil, Bus, Truck</h1>
        <p>
            Video diproses sementara melalui Redis Queue. Maksimal durasi 15 menit.
            Video temporary akan dihapus setelah analisis selesai, lalu kamu bisa pilih simpan hasil atau buang.
        </p>
    </div>

    <div class="video-badge">
        <span class="video-badge-dot"></span>
        Redis Queue Ready
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-4 fw-bold">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-4 fw-bold">
        {{ session('error') }}
    </div>
@endif

@php
    $activeJobId = session('job_id');
@endphp

<div class="row g-3 mb-4">

    <div class="col-xl-5">
        <div class="video-card h-100">
            <div class="video-card-header">
                <div>
                    <h5>Input Video</h5>
                    <p>Upload video atau gunakan Google Drive public link.</p>
                </div>
                <i class="bi bi-upload text-primary fs-4"></i>
            </div>

            <div class="video-card-body">
                <form method="POST"
                      action="{{ route('investor.surveyor.video-detection.submit') }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold">Titik Kandidat Admin</label>
                        <select name="candidate_location_id" class="form-select">
                            <option value="">Tidak pilih kandidat</option>
                            @foreach($candidates ?? [] as $candidate)
                                <option value="{{ $candidate->id }}">
                                    {{ $candidate->kode_lokasi ?? '-' }} - {{ $candidate->nama_lokasi ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Pilih titik yang sudah dibuat admin agar video terhubung ke lokasi.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lokasi / Catatan Lokasi</label>
                        <input type="text"
                               name="lokasi"
                               class="form-control"
                               placeholder="Contoh: Depan calon outlet Jl. Raya Magetan">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Sumber Video</label>
                        <select name="source_type" id="sourceType" class="form-select">
                            <option value="upload">Upload Video</option>
                            <option value="google_drive">Google Drive Link</option>
                        </select>
                    </div>

                    <div id="uploadBox" class="mb-3">
                        <label class="form-label fw-bold">Upload Video</label>
                        <input type="file"
                               name="video_file"
                               class="form-control"
                               accept="video/*">
                        <small class="text-muted">
                            Maksimal 15 menit. File tidak disimpan permanen.
                        </small>
                    </div>

                    <div id="driveBox" class="mb-3" style="display:none;">
                        <label class="form-label fw-bold">Google Drive Public Link</label>
                        <input type="url"
                               name="google_drive_url"
                               class="form-control"
                               placeholder="https://drive.google.com/...">
                        <small class="text-muted">
                            Pastikan link bisa diakses public.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold py-2">
                        <i class="bi bi-play-circle me-1"></i>
                        Mulai Analisis
                    </button>
                </form>

                <div class="mt-4 p-3 rounded-4" style="background:#f8fafc;border:1px dashed #cbd5e1;">
                    <div class="fw-bold mb-2">
                        Cara kerja singkat
                    </div>
                    <div class="small text-muted">
                        Upload → Redis Queue → Python Detector → Hasil Temporary → Simpan / Buang.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="video-processing-box h-100">

            <div class="processing-hero">
                <div>
                    <div class="live-status mb-3">
                        <div class="live-status-dot"></div>
                        Redis Worker Monitor
                    </div>

                    <h2 class="processing-title">
                        AI Video Traffic Detection
                    </h2>

                    <div class="processing-subtitle">
                        YOLO detector, frame sampling, object counting, dan peak minute summary.
                    </div>
                </div>

                <div class="processing-icon">
                    <i class="bi bi-camera-video"></i>
                </div>
            </div>

            @if($activeJobId)
                <div class="mt-4 position-relative" style="z-index:1;">
                    <div class="fw-bold mb-2">
                        Job ID:
                        <span id="jobId">{{ $activeJobId }}</span>
                    </div>

                    <div class="ai-progress">
                        <div id="jobProgress" class="ai-progress-bar" style="width:20%;"></div>
                    </div>

                    <div class="ai-progress-label">
                        <span id="jobStatus">queued - Job masuk antrean Redis.</span>
                        <span id="jobPercent">20%</span>
                    </div>
                </div>

                <div class="live-metric-grid">
                    <div class="live-metric">
                        <span>Orang</span>
                        <strong id="personCount">0</strong>
                    </div>

                    <div class="live-metric">
                        <span>Motor</span>
                        <strong id="motorcycleCount">0</strong>
                    </div>

                    <div class="live-metric">
                        <span>Mobil</span>
                        <strong id="carCount">0</strong>
                    </div>

                    <div class="live-metric">
                        <span>Bus</span>
                        <strong id="busCount">0</strong>
                    </div>

                    <div class="live-metric">
                        <span>Truck</span>
                        <strong id="truckCount">0</strong>
                    </div>

                    <div class="live-metric">
                        <span>Peak Minute</span>
                        <strong id="peakMinute">-</strong>
                    </div>
                </div>

                <div id="aiInsightBox" class="mt-4 p-3 rounded-4" style="display:none; background:#f0fdf4; border:1px solid #bbf7d0;">
                    <div class="fw-bold text-success mb-2"><i class="bi bi-robot"></i> AI Business Insight (Groq)</div>
                    <p id="aiInsightText" class="mb-0 text-dark" style="font-size:13px;"></p>
                </div>

                <div class="live-stage-list">
                    <div class="live-stage">
                        <div class="live-stage-left">
                            <div class="live-stage-icon">
                                <i class="bi bi-upload"></i>
                            </div>
                            <div>
                                <div class="live-stage-title">Upload Validation</div>
                                <div class="live-stage-subtitle">Cek format dan antrean job</div>
                            </div>
                        </div>
                        <div class="live-stage-status done" id="uploadStage">DONE</div>
                    </div>

                    <div class="live-stage">
                        <div class="live-stage-left">
                            <div class="live-stage-icon">
                                <i class="bi bi-cpu"></i>
                            </div>
                            <div>
                                <div class="live-stage-title">AI Detection Engine</div>
                                <div class="live-stage-subtitle">Frame sampling dan object detection</div>
                            </div>
                        </div>
                        <div class="live-stage-status" id="aiStageStatus">QUEUED</div>
                    </div>

                    <div class="live-stage">
                        <div class="live-stage-left">
                            <div class="live-stage-icon">
                                <i class="bi bi-bar-chart"></i>
                            </div>
                            <div>
                                <div class="live-stage-title">Aggregation Result</div>
                                <div class="live-stage-subtitle">Hitung traffic dan peak minute</div>
                            </div>
                        </div>
                        <div class="live-stage-status waiting" id="aggregationStage">WAITING</div>
                    </div>

                    <div class="live-stage">
                        <div class="live-stage-left">
                            <div class="live-stage-icon">
                                <i class="bi bi-trash3"></i>
                            </div>
                            <div>
                                <div class="live-stage-title">Temporary Video Cleanup</div>
                                <div class="live-stage-subtitle">Video dibuang setelah analisis</div>
                            </div>
                        </div>
                        <div class="live-stage-status waiting" id="cleanupStage">WAITING</div>
                    </div>
                </div>

                <div id="actionBox" class="mt-4 position-relative" style="display:none;z-index:1;">
                    <form method="POST"
                          action="{{ route('investor.surveyor.video-detection.save', $activeJobId) }}"
                          class="d-inline">
                        @csrf
                        <button class="btn btn-success rounded-pill fw-bold px-4">
                            <i class="bi bi-save me-1"></i>
                            Simpan Hasil
                        </button>
                    </form>

                    <form method="POST"
                          action="{{ route('investor.surveyor.video-detection.discard', $activeJobId) }}"
                          class="d-inline">
                        @csrf
                        <button class="btn btn-outline-danger rounded-pill fw-bold px-4">
                            <i class="bi bi-x-circle me-1"></i>
                            Buang Hasil
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-4 position-relative" style="z-index:1;">
                    <div class="p-4 rounded-4 bg-white border text-muted">
                        Belum ada proses analisis aktif. Upload video untuk mulai membaca traffic.
                    </div>
                </div>

                <div class="live-metric-grid">
                    <div class="live-metric"><span>Orang</span><strong>0</strong></div>
                    <div class="live-metric"><span>Motor</span><strong>0</strong></div>
                    <div class="live-metric"><span>Mobil</span><strong>0</strong></div>
                    <div class="live-metric"><span>Bus</span><strong>0</strong></div>
                    <div class="live-metric"><span>Truck</span><strong>0</strong></div>
                    <div class="live-metric"><span>Peak Minute</span><strong>-</strong></div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="video-card">
    <div class="video-card-header">
        <div>
            <h5>Hasil Tersimpan</h5>
            <p>Yang disimpan hanya data hasil analisis, bukan file video.</p>
        </div>
        <i class="bi bi-database-check text-primary fs-4"></i>
    </div>

    <div class="video-card-body">
        <div class="table-responsive">
            <table class="table result-table align-middle" id="videoDetectionTable" style="width:100%;">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Lokasi</th>
                        <th>Source</th>
                        <th>Durasi</th>
                        <th>Orang</th>
                        <th>Motor</th>
                        <th>Mobil</th>
                        <th>Bus</th>
                        <th>Truck</th>
                        <th>Peak</th>
                        <th>AI Insight</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($savedResults ?? [] as $row)
                        @php
                            $raw = json_decode($row->raw_result ?? '{}', true);
                            $insight = $raw['ai_insight'] ?? null;
                        @endphp
                        <tr>
                            <td>{{ $row->created_at ?? '-' }}</td>
                            <td class="fw-bold">{{ $row->lokasi ?? '-' }}</td>
                            <td>{{ $row->source_type ?? '-' }}</td>
                            <td>{{ $row->duration_seconds ?? 0 }}s</td>
                            <td><span class="object-pill">{{ $row->person_count ?? 0 }}</span></td>
                            <td><span class="object-pill">{{ $row->motorcycle_count ?? 0 }}</span></td>
                            <td><span class="object-pill">{{ $row->car_count ?? 0 }}</span></td>
                            <td><span class="object-pill">{{ $row->bus_count ?? 0 }}</span></td>
                            <td><span class="object-pill">{{ $row->truck_count ?? 0 }}</span></td>
                            <td>{{ $row->peak_minute ?? '-' }}</td>
                            <td>
                                @if($insight)
                                    <div style="font-size:11px; max-width:250px; white-space:normal;" class="text-muted">
                                        <i class="bi bi-robot text-success me-1"></i>
                                        {{ Str::limit($insight, 100) }}
                                        <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="alert(`{{ addslashes($insight) }}`)">Lihat</button>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    $('#videoDetectionTable').DataTable({
        ordering: false,
        pageLength: 10,
        language: {
            emptyTable: 'Belum ada hasil video detection',
            search: 'Cari:',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            paginate: {
                previous: 'Sebelumnya',
                next: 'Berikutnya'
            }
        }
    });

    $('#sourceType').on('change', function () {
        if ($(this).val() === 'google_drive') {
            $('#driveBox').show();
            $('#uploadBox').hide();
        } else {
            $('#driveBox').hide();
            $('#uploadBox').show();
        }
    });

    const jobId = $('#jobId').text();

    if (jobId) {
        const timer = setInterval(function () {
            $.get('{{ url('/surveyor/video-detection/status') }}/' + jobId, function (res) {
                const status = res.status || '-';
                const message = res.message || '';

                $('#jobStatus').text(status + ' - ' + message);

                if (status === 'queued') {
                    $('#jobProgress').css('width', '20%');
                    $('#jobPercent').text('20%');
                    $('#aiStageStatus').text('QUEUED').removeClass('done failed').addClass('waiting');
                    $('#aggregationStage').text('WAITING').removeClass('done failed').addClass('waiting');
                    $('#cleanupStage').text('WAITING').removeClass('done failed').addClass('waiting');
                }

                if (status === 'processing') {
                    $('#jobProgress').css('width', '72%');
                    $('#jobPercent').text('72%');
                    $('#aiStageStatus').text('PROCESSING').removeClass('waiting failed');
                }

                if (status === 'done') {
                    $('#jobProgress').css('width', '100%');
                    $('#jobPercent').text('100%');

                    $('#aiStageStatus').text('DONE').removeClass('waiting failed').addClass('done');
                    $('#aggregationStage').text('DONE').removeClass('waiting failed').addClass('done');
                    $('#cleanupStage').text('DONE').removeClass('waiting failed').addClass('done');

                    $('#actionBox').show();
                    clearInterval(timer);
                }

                if (status === 'failed') {
                    $('#jobProgress')
                        .css('width', '100%')
                        .addClass('bg-danger');

                    $('#jobPercent').text('FAILED');
                    $('#aiStageStatus').text('FAILED').removeClass('waiting done').addClass('failed');
                    $('#aggregationStage').text('STOPPED').removeClass('waiting done').addClass('failed');
                    $('#cleanupStage').text('DONE').removeClass('waiting failed').addClass('done');

                    clearInterval(timer);
                }

                if (res.counts) {
                    $('#personCount').text(res.counts.person || 0);
                    $('#motorcycleCount').text(res.counts.motorcycle || 0);
                    $('#carCount').text(res.counts.car || 0);
                    $('#busCount').text(res.counts.bus || 0);
                    $('#truckCount').text(res.counts.truck || 0);
                    $('#peakMinute').text(res.peak_minute ?? '-');
                }
                
                if (res.ai_insight) {
                    $('#aiInsightText').text(res.ai_insight);
                    $('#aiInsightBox').slideDown();
                }
            }).fail(function () {
                $('#jobStatus').text('Job tidak ditemukan / expired.');
                $('#jobProgress').css('width', '100%').addClass('bg-danger');
                $('#jobPercent').text('EXPIRED');
                clearInterval(timer);
            });
        }, 2500);
    }
});
</script>
@endpush

@include('Surveyor.layouts.footer')
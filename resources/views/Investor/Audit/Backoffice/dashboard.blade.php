@include('Temp.Audit.header')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid my-4">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4 p-4"
         style="background: linear-gradient(135deg, #007bff1a, #00b09b1a);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h3 class="fw-bold mb-1 text-primary">
                    <i class="bi bi-bar-chart-line-fill"></i> Dashboard Audit
                </h3>
                <p class="text-muted mb-0">
                    Analisis cepat & interaktif hasil audit outlet secara real-time 📊
                </p>
            </div>
            <div>
                <span class="badge bg-light text-dark shadow-sm py-2 px-3 rounded-pill">
                    <i class="bi bi-clock me-1 text-primary"></i>
                    Last update:
                    <strong>{{ now('Asia/Jakarta')->format('d M Y, H:i') }} WIB</strong>
                </span>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="fw-bold text-primary mb-1">
                    <i class="bi bi-list-check me-1"></i> Standar Aktivitas Harian
                </h5>
                <small class="text-muted">Klik slot untuk lihat preview / history / next.</small>
            </div>
        </div>

        <div class="vstack gap-3">
            @forelse(($slotTimeline ?? collect()) as $slot)
                @php
                    $badgeClass = 'bg-secondary-subtle text-secondary';
                    $statusText = 'Menunggu';
                    $icon = 'bi-hourglass-split';

                    if ($slot->status === 'done') {
                        $badgeClass = 'bg-success-subtle text-success';
                        $statusText = 'Sudah diisi';
                        $icon = 'bi-check-circle-fill';
                    } elseif ($slot->status === 'active') {
                        $badgeClass = 'bg-primary-subtle text-primary';
                        $statusText = 'Sedang aktif';
                        $icon = 'bi-play-circle-fill';
                    } elseif ($slot->status === 'missed') {
                        $badgeClass = 'bg-danger-subtle text-danger';
                        $statusText = 'Terlewat';
                        $icon = 'bi-x-circle-fill';
                    }
                @endphp

                <a href="{{ route('audit.dashboard', ['view_jam' => $slot->jam]) }}"
                   class="text-decoration-none text-dark">
                    <div class="border rounded-4 p-3 slot-list-item {{ $slot->status }}">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2">
                                    {{ $slot->label }} WIB
                                </span>
                                <span class="fw-semibold">{{ $statusText }}</span>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <i class="bi {{ $icon }} fs-5"></i>
                                <small class="text-muted">
                                    Berlaku {{ $slot->start->format('H:i') }} - {{ $slot->end->format('H:i') }} WIB
                                </small>
                            </div>
                        </div>

                        @if(($slot->items ?? collect())->count() > 0)
                            <div class="vstack gap-2">
                                @foreach($slot->items as $idx => $item)
                                    <div class="d-flex gap-2">
                                        <span class="text-muted">{{ $idx + 1 }}.</span>
                                        <div>{{ $item->pertanyaan }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">Tidak ada pertanyaan pada slot ini.</div>
                        @endif
                    </div>
                </a>
            @empty
                <div class="alert alert-light border rounded-4 mb-0">
                    Belum ada data aktivitas harian.
                </div>
            @endforelse
        </div>
    </div>

    @if(!empty($slotAktif))
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span><i class="bi bi-clock-history me-1"></i> Slot audit aktif saat ini:</span>
                <strong>{{ \Carbon\Carbon::parse($slotAktif['jam_aktivitas'])->format('H:i') }} WIB</strong>
                <span>•</span>
                <span>Berlaku sampai:</span>
                <strong>{{ $slotAktif['end']->format('H:i') }} WIB</strong>
            </div>
        </div>
    @endif

    @if(!empty($selectedSlot))
        <div class="card border-0 shadow-sm rounded-4 mb-3 p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="fw-bold mb-1 text-primary">
                        <i class="bi bi-clock-history me-1"></i>
                        Preview Slot {{ \Carbon\Carbon::parse($selectedSlot['jam_aktivitas'])->format('H:i') }} WIB
                    </h6>
                    <small class="text-muted">
                        Berlaku {{ $selectedSlot['start']->format('H:i') }} - {{ $selectedSlot['end']->format('H:i') }} WIB
                    </small>
                </div>

                <div class="d-flex gap-2">
                    @if($prevJam)
                        <a href="{{ route('audit.dashboard', ['view_jam' => $prevJam]) }}"
                           class="btn btn-outline-secondary btn-sm rounded-3">
                            <i class="bi bi-chevron-left"></i> Prev
                        </a>
                    @endif

                    @if($nextJam)
                        <a href="{{ route('audit.dashboard', ['view_jam' => $nextJam]) }}"
                           class="btn btn-outline-secondary btn-sm rounded-3">
                            Next <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(!empty($alreadyFilledToday) && !empty($slotAktif) && empty($alreadyFilledCurrentSlot))
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-info-circle me-1"></i>
            Anda sudah pernah mengisi audit di slot lain hari ini. Slot aktif saat ini masih bisa diisi jika belum pernah disubmit.
        </div>
    @endif

    @if(!empty($slotLewat))
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-exclamation-triangle me-1"></i>
            Saat ini belum ada slot audit aktif atau semua slot hari ini sudah lewat.
        </div>
    @endif

    @if(!empty($selectedSlot))
        @if(!$isSelectedSlotActive)
            <div class="alert alert-secondary border-0 shadow-sm rounded-4 mb-3">
                <i class="bi bi-eye me-1"></i>
                Ini mode preview / history. Slot ini belum aktif atau sudah lewat, jadi belum bisa disubmit.
            </div>
        @endif

        @if($alreadyFilledSelectedSlot)
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-3">
                <i class="bi bi-check-circle me-1"></i>
                Slot ini sudah pernah Anda isi.
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold text-primary mb-1">
                        <i class="bi bi-clipboard-check me-1"></i>
                        Form Audit Slot {{ \Carbon\Carbon::parse($selectedSlot['jam_aktivitas'])->format('H:i') }} WIB
                    </h5>
                    <small class="text-muted">
                        @if($isSelectedSlotActive)
                            Isi audit untuk slot aktif yang sedang berlaku.
                        @else
                            Preview pertanyaan untuk slot ini.
                        @endif
                    </small>
                </div>
                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                    Berlaku sampai {{ $selectedSlot['end']->format('H:i') }} WIB
                </span>
            </div>

            <form method="POST" action="{{ route('audit.store') }}" id="formAuditHarian">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Outlet</label>
                        <select name="outlet_id" class="form-select rounded-3" required {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                            <option value="">Pilih outlet</option>
                            @foreach($outlets ?? [] as $o)
                                <option value="{{ $o->id }}">{{ $o->nama_outlet }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">Leader PIC</label>
                        <select name="leader_pic_id" class="form-select rounded-3" required {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                            <option value="">Pilih Leader</option>
                            @foreach($leaderPics ?? [] as $pic)
                                <option value="{{ $pic->pic_id }}">{{ $pic->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">SPV PIC</label>
                        <select name="spv_pic_id" class="form-select rounded-3" required {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                            <option value="">Pilih SPV</option>
                            @foreach($spvPics ?? [] as $pic)
                                <option value="{{ $pic->pic_id }}">{{ $pic->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <label class="form-label fw-semibold">TM PIC</label>
                        <select name="tm_pic_id" class="form-select rounded-3" required {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                            <option value="">Pilih TM</option>
                            @foreach($tmPics ?? [] as $pic)
                                <option value="{{ $pic->pic_id }}">{{ $pic->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input type="hidden" name="jam_aktivitas" value="{{ $selectedSlot['jam_aktivitas'] }}">

                <div class="vstack gap-4">
                    @forelse($questions ?? [] as $q)
                        <div class="card border rounded-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                    <div>
                                        <h6 class="fw-bold mb-1">Pertanyaan {{ $loop->iteration }}</h6>
                                        <div class="text-muted">{!! nl2br(e($q->pertanyaan)) !!}</div>
                                    </div>
                                    <span class="badge bg-light text-dark">
                                        {{ \Carbon\Carbon::parse($q->jam)->format('H:i') }} WIB
                                    </span>
                                </div>

                                <div class="row g-3 align-items-start">
                                    <div class="col-lg-3">
                                        <label class="form-label fw-semibold">Jawaban</label>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input jawaban-radio"
                                                       type="radio"
                                                       name="pertanyaan_{{ $q->id }}"
                                                       id="pertanyaan_{{ $q->id }}_ya"
                                                       value="Ya"
                                                       data-id="{{ $q->id }}"
                                                       required
                                                       {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="pertanyaan_{{ $q->id }}_ya">Ya</label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input jawaban-radio"
                                                       type="radio"
                                                       name="pertanyaan_{{ $q->id }}"
                                                       id="pertanyaan_{{ $q->id }}_tidak"
                                                       value="Tidak"
                                                       data-id="{{ $q->id }}"
                                                       required
                                                       {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="pertanyaan_{{ $q->id }}_tidak">Tidak</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-4 alasan-wrapper d-none" id="alasan_wrapper_{{ $q->id }}">
                                        <label class="form-label fw-semibold">Alasan</label>
                                        <textarea class="form-control rounded-3"
                                                  name="pertanyaan_{{ $q->id }}_alasan"
                                                  id="pertanyaan_{{ $q->id }}_alasan"
                                                  rows="3"
                                                  placeholder="Tulis alasan jika jawaban Tidak"
                                                  {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}></textarea>
                                    </div>

                                    <div class="col-lg-5 foto-wrapper d-none" id="foto_wrapper_{{ $q->id }}">
                                        <label class="form-label fw-semibold">Foto Bukti</label>

                                        <input type="hidden" name="pertanyaan_{{ $q->id }}_foto_base64" id="pertanyaan_{{ $q->id }}_foto_base64">

                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm btn-open-camera"
                                                    data-id="{{ $q->id }}"
                                                    data-facing="environment"
                                                    {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                <i class="bi bi-camera"></i> Kamera Belakang
                                            </button>

                                            <button type="button"
                                                    class="btn btn-outline-secondary btn-sm btn-open-camera"
                                                    data-id="{{ $q->id }}"
                                                    data-facing="user"
                                                    {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                <i class="bi bi-camera-front"></i> Kamera Depan
                                            </button>

                                            <button type="button"
                                                    class="btn btn-primary btn-sm d-none btn-capture-camera"
                                                    id="btn_capture_{{ $q->id }}"
                                                    data-id="{{ $q->id }}"
                                                    {{ (!$isSelectedSlotActive || $alreadyFilledSelectedSlot) ? 'disabled' : '' }}>
                                                📸 Potret
                                            </button>
                                        </div>

                                        <video id="camera_preview_{{ $q->id }}"
                                               class="w-100 rounded border d-none"
                                               autoplay
                                               playsinline
                                               style="max-height: 260px; object-fit: contain;"></video>

                                        <canvas id="camera_canvas_{{ $q->id }}"
                                                class="w-100 rounded border d-none"
                                                style="max-height: 260px; object-fit: contain;"></canvas>

                                        <div class="small text-muted mt-2">Foto wajib jika jawaban Ya.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border rounded-4 mb-0">
                            Tidak ada pertanyaan pada slot ini.
                        </div>
                    @endforelse
                </div>

                @if(($questions ?? collect())->count() > 0 && $isSelectedSlotActive && !$alreadyFilledSelectedSlot)
                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3">
                            <i class="bi bi-save me-1"></i> Simpan Audit
                        </button>
                    </div>
                @endif
            </form>
        </div>
    @endif

    <div class="row g-4 mb-4">
        @php
            $cards = [
                [
                    'title' => 'Total Jawaban',
                    'value' => number_format($totalJawaban ?? 0),
                    'desc' => 'Total baris data jawaban',
                    'icon' => 'bi-chat-left-text',
                    'gradient' => 'linear-gradient(135deg,#007bff,#00c6ff)'
                ],
                [
                    'title' => 'Total Outlet',
                    'value' => number_format($totalOutlet ?? 0),
                    'desc' => 'Jumlah outlet aktif',
                    'icon' => 'bi-shop',
                    'gradient' => 'linear-gradient(135deg,#28a745,#a8e063)'
                ],
                [
                    'title' => 'Total Responden',
                    'value' => number_format($totalResponden ?? 0),
                    'desc' => 'Jumlah user terdaftar',
                    'icon' => 'bi-people',
                    'gradient' => 'linear-gradient(135deg,#17a2b8,#00e4ff)'
                ],
                [
                    'title' => 'Total Poin',
                    'value' => number_format($totalPoin ?? 0) . ' pts',
                    'desc' => '≈ Rp ' . number_format($totalUang ?? 0, 0, ',', '.'),
                    'icon' => 'bi-cash-stack',
                    'gradient' => 'linear-gradient(135deg,#ffc107,#ffde7d)'
                ]
            ];
        @endphp

        @foreach($cards as $c)
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 metric-card position-relative overflow-hidden h-100">
                    <div class="d-flex align-items-center justify-content-between position-relative z-1">
                        <div>
                            <h6 class="text-muted small mb-1">{{ $c['title'] }}</h6>
                            <h3 class="fw-bold mb-0 text-dark">{{ $c['value'] }}</h3>
                            <small class="text-muted">{{ $c['desc'] }}</small>
                        </div>
                        <div class="icon-wrapper rounded-3 shadow-sm"
                             style="background: {{ $c['gradient'] }}; width: 45px; height: 45px;">
                            <i class="bi {{ $c['icon'] }} fs-5 text-white"></i>
                        </div>
                    </div>
                    <div class="hover-overlay position-absolute top-0 start-0 w-100 h-100 rounded-4"
                         style="background: {{ $c['gradient'] }};"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('Temp.Audit.footer')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .metric-card {
        background: #fff;
        border-radius: 1rem;
        transition: all 0.3s ease;
    }
    .metric-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    }
    .metric-card .hover-overlay {
        opacity: 0;
        transition: opacity 0.4s;
    }
    .metric-card:hover .hover-overlay {
        opacity: 0.06;
    }
    .icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.3s ease;
    }
    .metric-card:hover .icon-wrapper {
        transform: scale(1.1);
    }
    .metric-card h3 {
        font-size: 1.4rem;
        margin-bottom: 0.25rem;
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .slot-list-item.done {
        background: #ecfdf3;
        border-color: #b7ebc6 !important;
    }
    .slot-list-item.active {
        background: #eff6ff;
        border-color: #bfdbfe !important;
    }
    .slot-list-item.missed {
        background: #fef2f2;
        border-color: #fecaca !important;
    }
    .slot-list-item.waiting {
        background: #f8fafc;
        border-color: #e5e7eb !important;
    }
</style>

<script>
    $(document).ready(function () {
        $('.jawaban-radio').on('change', function () {
            const id = $(this).data('id');
            const value = $(this).val();

            if (value === 'Ya') {
                $('#foto_wrapper_' + id).removeClass('d-none');
                $('#alasan_wrapper_' + id).addClass('d-none');
                $('#pertanyaan_' + id + '_alasan').val('');
            } else {
                $('#alasan_wrapper_' + id).removeClass('d-none');
                $('#foto_wrapper_' + id).addClass('d-none');
                $('#pertanyaan_' + id + '_foto_base64').val('');

                const video = $('#camera_preview_' + id).get(0);
                if (video && video.srcObject) {
                    video.srcObject.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                }

                $('#camera_preview_' + id).addClass('d-none');
                $('#camera_canvas_' + id).addClass('d-none');
                $('#btn_capture_' + id).addClass('d-none');
            }
        });

        const stopStream = (video) => {
            if (video && video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
        };

        $('.btn-open-camera').on('click', async function () {
            if ($(this).is(':disabled')) return;

            const id = $(this).data('id');
            const facing = $(this).data('facing');
            const video = document.getElementById('camera_preview_' + id);
            const canvas = document.getElementById('camera_canvas_' + id);
            const btnCapture = document.getElementById('btn_capture_' + id);

            stopStream(video);

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: facing }
                });

                video.srcObject = stream;
                video.classList.remove('d-none');
                canvas.classList.add('d-none');
                btnCapture.classList.remove('d-none');
            } catch (err) {
                alert('Tidak bisa membuka kamera: ' + err.message);
            }
        });

        $('.btn-capture-camera').on('click', function () {
            if ($(this).is(':disabled')) return;

            const id = $(this).data('id');
            const video = document.getElementById('camera_preview_' + id);
            const canvas = document.getElementById('camera_canvas_' + id);
            const inputBase64 = document.getElementById('pertanyaan_' + id + '_foto_base64');
            const ctx = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const nowText = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
            ctx.font = '20px Arial';
            ctx.fillStyle = 'rgba(255,255,255,0.75)';
            const textWidth = ctx.measureText(nowText).width + 20;
            ctx.fillRect(10, canvas.height - 40, textWidth, 30);
            ctx.fillStyle = '#000';
            ctx.fillText(nowText, 20, canvas.height - 18);

            inputBase64.value = canvas.toDataURL('image/jpeg', 0.9);
            canvas.classList.remove('d-none');
            video.classList.add('d-none');
            $(this).addClass('d-none');

            stopStream(video);
        });
    });
</script>
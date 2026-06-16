@include('Temp.Investor.header')
@include('Temp.Investor.partials.sidebar')

<style>
    .brand-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: transform 0.2s ease-in-out;
        background: #fff;
    }
    .brand-card:hover {
        transform: translateY(-3px);
    }
    .stat-title {
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
    }
    .badge-sentiment-positif { background: #dcfce7; color: #166534; }
    .badge-sentiment-negatif { background: #fee2e2; color: #991b1b; }
    .badge-sentiment-netral { background: #f1f5f9; color: #475569; }
    .ai-box {
        background: #f8fafc;
        border-left: 4px solid #3b82f6;
        padding: 15px;
        border-radius: 4px;
        margin-top: 10px;
    }
    .ai-box h6 {
        font-size: 12px;
        font-weight: 700;
        color: #3b82f6;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    .ai-box p {
        font-size: 13px;
        color: #334155;
        margin-bottom: 0;
    }
    .table-hover tbody tr:hover {
        background-color: #f1f5f9;
    }
</style>

<div class="main-content" id="mainContent">
    <div class="container-fluid p-4" style="background:#f1f5f9; min-height: 100vh;">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark"><i class="bi bi-radar" style="color:#3b82f6;"></i> Brand 24 Engine</h3>
                <p class="text-muted mb-0" style="font-size: 14px;">AI-Powered Public Sentiment & Brand Reputation Monitor</p>
            </div>
            <div>
                <button class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalManual">
                    <i class="bi bi-pencil-square"></i> Input Manual
                </button>
                <button class="btn btn-outline-primary shadow-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalImport">
                    <i class="bi bi-cloud-arrow-up"></i> Import Excel
                </button>
                <button class="btn btn-primary shadow-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalAutoScrape">
                    <i class="bi bi-robot"></i> Auto Scrape (Apify)
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        <!-- STATS ROW -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="brand-card p-4 text-center">
                    <div class="stat-title">Total Ulasan</div>
                    <div class="stat-value text-primary">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="brand-card p-4 text-center">
                    <div class="stat-title text-success">Sentimen Positif</div>
                    <div class="stat-value text-success">{{ $stats['positive'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="brand-card p-4 text-center">
                    <div class="stat-title text-danger">Sentimen Negatif</div>
                    <div class="stat-value text-danger">{{ $stats['negative'] }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="brand-card p-4 text-center">
                    <div class="stat-title text-warning">Total Isu Service</div>
                    <div class="stat-value text-warning">{{ $stats['service'] }}</div>
                </div>
            </div>
        </div>

        <!-- MAIN DATA TABLE -->
        <div class="brand-card p-4">
            <h5 class="fw-bold mb-4">Live Mentions & Feedbacks</h5>
            <div class="table-responsive">
                <table class="table table-borderless table-hover align-middle">
                    <thead style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                        <tr>
                            <th style="font-size:12px; color:#64748b; padding:12px;">SOURCE</th>
                            <th style="font-size:12px; color:#64748b; padding:12px;">REVIEWER</th>
                            <th style="font-size:12px; color:#64748b; padding:12px; width:40%;">REVIEW TEXT</th>
                            <th style="font-size:12px; color:#64748b; padding:12px;">AI SENTIMENT</th>
                            <th style="font-size:12px; color:#64748b; padding:12px;">CATEGORY</th>
                            <th style="font-size:12px; color:#64748b; padding:12px; text-align:right;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mentions as $m)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td class="fw-bold text-dark" style="font-size:13px;">
                                @if(strtolower($m->source) == 'gmaps' || strtolower($m->source) == 'google')
                                    <i class="bi bi-google text-danger"></i>
                                @elseif(strtolower($m->source) == 'gofood')
                                    <i class="bi bi-bag-check-fill text-danger"></i>
                                @elseif(strtolower($m->source) == 'instagram' || strtolower($m->source) == 'ig')
                                    <i class="bi bi-instagram text-danger"></i>
                                @else
                                    <i class="bi bi-globe"></i>
                                @endif
                                {{ $m->source }}
                            </td>
                            <td style="font-size:13px; font-weight:600;">{{ $m->username }}</td>
                            <td style="font-size:13px; color:#334155;">
                                "{{ $m->review_text }}"
                                
                                @if($m->ai_root_cause != '-')
                                <!-- HASIL ANALISA AI -->
                                <div class="ai-box mt-2">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <h6><i class="bi bi-search"></i> Akar Masalah (Root Cause)</h6>
                                            <p>{{ $m->ai_root_cause }}</p>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <h6><i class="bi bi-chat-left-text"></i> Draft Balasan Admin (Marketing Step)</h6>
                                            <p>{{ $m->ai_marketing_step }}</p>
                                        </div>
                                        <div class="col-md-12 mt-2" style="border-top:1px dashed #cbd5e1; padding-top:10px;">
                                            <h6><i class="bi bi-lightbulb"></i> Solusi Operasional (Business Perspective)</h6>
                                            <p class="fw-bold text-dark">{{ $m->ai_business_solution }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif

                            </td>
                            <td>
                                @if(!$m->sentiment)
                                    <span class="badge bg-secondary rounded-pill">Belum Dianalisis</span>
                                @else
                                    <span class="badge rounded-pill badge-sentiment-{{ strtolower($m->sentiment) }} px-3 py-2">
                                        {{ $m->sentiment }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($m->category)
                                    <span class="badge bg-light text-dark border px-2 py-1">{{ $m->category }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                @if($m->ai_root_cause == '-' || !$m->sentiment)
                                    <button class="btn btn-sm btn-outline-primary rounded-pill btn-analyze" data-id="{{ $m->id }}">
                                        <i class="bi bi-robot"></i> Analisis AI
                                    </button>
                                @endif

                                @if($m->status == 'Open')
                                    <form action="{{ route('marketing.brand24.resolve', $m->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success rounded-pill" onclick="return confirm('Tandai isu ini telah diselesaikan?')">
                                            <i class="bi bi-check2-all"></i> Selesai
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Resolved</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada data ulasan. Silakan import dari Excel.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('marketing.brand24.import') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg" style="border-radius:12px;">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Import Ulasan (Excel/CSV)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-muted small">Format kolom wajib: <b>source</b>, <b>username</b>, <b>review_text</b>.</p>
                <input type="file" name="file" class="form-control" accept=".csv, .xlsx, .xls" required>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="submit" class="btn btn-primary px-4 rounded-3 w-100">Upload & Import</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Manual Input -->
<div class="modal fade" id="modalManual" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('marketing.brand24.store') }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius:12px;">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Input Ulasan Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Platform/Sumber (cth: Gmaps, GoFood)</label>
                    <input type="text" name="source" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Username Pelanggan</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Isi Ulasan / Keluhan</label>
                    <textarea name="review_text" class="form-control" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="submit" class="btn btn-primary px-4 rounded-3 w-100">Simpan & Auto-Analisis AI</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Auto Scrape (Apify) -->
<div class="modal fade" id="modalAutoScrape" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('marketing.brand24.auto-scrape') }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius:12px;">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Auto Scrape dengan Apify</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-info py-2" style="font-size:13px;">
                    <i class="bi bi-info-circle"></i> Memanfaatkan konfigurasi Apify Token Anda di menu <b>Content Posting</b>.
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Platform</label>
                    <select name="platform" class="form-select" required>
                        <option value="gmaps">Google Maps Reviews</option>
                        <option value="instagram">Instagram Comments</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">URL Target (Gmaps / IG Post)</label>
                    <input type="url" name="url" class="form-control" placeholder="https://..." required>
                    <small class="text-muted" style="font-size:11px;">Maksimal 10 ulasan terbaru agar proses cepat (Sinkronus).</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="submit" class="btn btn-primary px-4 rounded-3 w-100" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Sedang Menyedot Data...';">Mulai Scrape & Analisa AI</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const analyzeBtns = document.querySelectorAll('.btn-analyze');
        analyzeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const originalHtml = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading AI...';
                this.disabled = true;

                fetch(`/marketing/brand-24/analyze/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(r => r.json()).then(res => {
                    if(res.success) {
                        Swal.fire({icon: 'success', title: 'Berhasil', text: 'Analisis AI selesai!'});
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        Swal.fire({icon: 'error', title: 'Gagal', text: res.message});
                        this.innerHTML = originalHtml;
                        this.disabled = false;
                    }
                }).catch(err => {
                    Swal.fire({icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.'});
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                });
            });
        });
    });
</script>

@include('Temp.Investor.footer')

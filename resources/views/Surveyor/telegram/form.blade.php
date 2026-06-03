@section('title', 'Form Backup Telegram')
@section('breadcrumb', 'Surveyor / Telegram Backup')

@include('Surveyor.layouts.header')

<style>
    :root {
        --telegram-bg: #f4f7fb;
        --telegram-card: #ffffff;
        --telegram-text: #111827;
        --telegram-muted: #64748b;
        --telegram-border: #e5e7eb;
        --telegram-primary: #2563eb;
        --telegram-primary-soft: #eff6ff;
        --telegram-success: #16a34a;
        --telegram-success-soft: #dcfce7;
        --telegram-shadow: 0 12px 32px rgba(15, 23, 42, .07);
    }

    .telegram-page {
        min-height: calc(100vh - 70px);
        padding: 22px 26px 32px;
        background: var(--telegram-bg);
        color: var(--telegram-text);
    }

    .telegram-shell {
        width: 100%;
        max-width: 100%;
        margin: 0;
    }

    .telegram-hero {
        margin-bottom: 16px;
        padding: 18px 20px;
        border: 1px solid var(--telegram-border);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
    }

    .telegram-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        margin-bottom: 8px;
        border-radius: 999px;
        background: var(--telegram-primary-soft);
        color: var(--telegram-primary);
        font-size: 12px;
        font-weight: 900;
    }

    .telegram-hero h1 {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 900;
        letter-spacing: -.03em;
    }

    .telegram-hero p {
        margin: 7px 0 0;
        max-width: 820px;
        color: var(--telegram-muted);
        font-size: 14px;
        line-height: 1.55;
    }

    .telegram-alert {
        margin-bottom: 14px;
        border: 0;
        border-radius: 16px;
        font-weight: 800;
    }

    .telegram-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(360px, .65fr);
        gap: 14px;
        align-items: start;
    }

    .telegram-card {
        border: 1px solid var(--telegram-border);
        border-radius: 22px;
        background: var(--telegram-card);
        box-shadow: var(--telegram-shadow);
        overflow: hidden;
    }

    .telegram-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 18px;
        border-bottom: 1px solid var(--telegram-border);
        background: #fff;
    }

    .telegram-card-header i {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--telegram-primary-soft);
        color: var(--telegram-primary);
    }

    .telegram-card-header h2 {
        margin: 0;
        font-size: 17px;
        font-weight: 900;
    }

    .telegram-card-header p {
        margin: 2px 0 0;
        color: var(--telegram-muted);
        font-size: 12px;
        line-height: 1.4;
    }

    .telegram-card-body {
        padding: 18px;
    }

    .telegram-label {
        display: block;
        margin-bottom: 8px;
        color: var(--telegram-muted);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .telegram-input {
        width: 100%;
        border: 1px solid var(--telegram-border);
        border-radius: 14px;
        padding: 11px 13px;
        background: #f8fafc;
        color: var(--telegram-text);
        font-size: 14px;
        font-weight: 700;
        outline: none;
        transition: .18s ease;
    }

    .telegram-input:focus {
        border-color: var(--telegram-primary);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    select.telegram-input {
        height: 46px;
    }

    textarea.telegram-input {
        min-height: 390px;
        resize: vertical;
        line-height: 1.55;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
        font-size: 13px;
        font-weight: 700;
    }

    .telegram-form-group {
        margin-bottom: 16px;
    }

    .telegram-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 2px;
    }

    .telegram-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 10px 16px;
        border: 0;
        border-radius: 14px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }

    .telegram-btn-primary {
        background: var(--telegram-primary);
        color: #fff;
        box-shadow: 0 12px 24px rgba(37, 99, 235, .24);
    }

    .telegram-btn-primary:hover {
        color: #fff;
        transform: translateY(-1px);
    }

    .telegram-template-box {
        margin: 0;
        padding: 16px;
        border: 1px solid var(--telegram-border);
        border-radius: 16px;
        background: #0f172a;
        color: #e5e7eb;
        white-space: pre-wrap;
        font-size: 12px;
        line-height: 1.65;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
    }

    .telegram-hint-list {
        display: grid;
        gap: 10px;
        margin-bottom: 14px;
    }

    .telegram-hint {
        display: flex;
        gap: 10px;
        padding: 12px;
        border: 1px solid var(--telegram-border);
        border-radius: 16px;
        background: #f8fafc;
    }

    .telegram-hint i {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        flex: 0 0 28px;
        border-radius: 10px;
        background: var(--telegram-primary-soft);
        color: var(--telegram-primary);
    }

    .telegram-hint strong {
        display: block;
        margin-bottom: 2px;
        font-size: 13px;
        font-weight: 900;
    }

    .telegram-hint span {
        display: block;
        color: var(--telegram-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    @media (max-width: 1100px) {
        .telegram-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 900px) {
        .telegram-page {
            padding: 14px;
        }

        .telegram-hero {
            padding: 16px;
        }

        .telegram-hero h1 {
            font-size: 24px;
        }

        .telegram-actions {
            justify-content: stretch;
        }

        .telegram-btn {
            width: 100%;
        }
    }
</style>

<div class="telegram-page">
    <div class="telegram-shell">

        <div class="telegram-hero">
            <div class="telegram-eyebrow">
                <i class="bi bi-telegram"></i>
                Bot Fallback Form
            </div>

            <h1>Form Backup Laporan Telegram</h1>

            <p>
                Dipakai saat bot Telegram bermasalah. Paste format laporan surveyor di sini
                supaya data tetap masuk dan bisa diproses sebagai laporan site score.
            </p>
        </div>

        @if(session('success'))
            <div class="alert alert-success telegram-alert">
                <i class="bi bi-check-circle me-1"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="telegram-grid">
            <div class="telegram-card">
                <div class="telegram-card-header">
                    <i class="bi bi-chat-square-text"></i>
                    <div>
                        <h2>Paste Laporan</h2>
                        <p>Pilih titik kandidat jika tersedia, lalu paste isi laporan dari surveyor.</p>
                    </div>
                </div>

                <div class="telegram-card-body">
                    <form method="POST" action="{{ route('investor.surveyor.telegram.form-submit') }}">
                        @csrf

                        <div class="telegram-form-group">
                            <label class="telegram-label" for="candidate_location_id">Titik Kandidat</label>
                            <select name="candidate_location_id" id="candidate_location_id" class="telegram-input">
                                <option value="">Tidak pilih / auto dari Kode</option>
                                @foreach($candidates ?? [] as $row)
                                    <option value="{{ $row->id }}">
                                        {{ $row->kode_lokasi }} - {{ $row->nama_lokasi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="telegram-form-group">
                            <label class="telegram-label" for="message_text">Message Text</label>
                            <textarea
                                name="message_text"
                                id="message_text"
                                rows="16"
                                class="telegram-input"
                                required
                                placeholder="/sitescore&#10;Kode: LOC-202605280001-ABCD&#10;Surveyor: Alwan&#10;Kompetitor Geprek: 2&#10;Kompetitor Lokal: 5&#10;Sekolah: 3&#10;Market: 2&#10;Catatan: Traffic ramai"></textarea>
                        </div>

                        <div class="telegram-actions">
                            <button type="submit" class="telegram-btn telegram-btn-primary">
                                <i class="bi bi-save"></i>
                                Simpan Laporan Backup
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="telegram-card">
                <div class="telegram-card-header">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <h2>Template Telegram</h2>
                        <p>Gunakan struktur ini agar parsing laporan lebih mudah.</p>
                    </div>
                </div>

                <div class="telegram-card-body">
                    <div class="telegram-hint-list">
                        <div class="telegram-hint">
                            <i class="bi bi-upc-scan"></i>
                            <div>
                                <strong>Kode lokasi penting</strong>
                                <span>Jika tidak memilih titik kandidat, sistem bisa membaca dari baris Kode.</span>
                            </div>
                        </div>

                        <div class="telegram-hint">
                            <i class="bi bi-camera-video"></i>
                            <div>
                                <strong>Link video opsional</strong>
                                <span>Tambahkan URL Drive atau link video survey jika tersedia.</span>
                            </div>
                        </div>
                    </div>

                    <pre class="telegram-template-box">/sitescore
Kode: LOC-202605280001-ABCD
Surveyor: Alwan
Catatan: Traffic ramai jam pulang kerja
Kompetitor Geprek: 2
Kompetitor Lokal: 5
Sekolah: 3
Market: 2
Perkantoran: 1
Kesehatan: 1
Rumah Q1: 900
Rumah Q2: 850
Rumah Q3: 600
Rumah Q4: 400
Video: https://drive.google.com/...</pre>
                </div>
            </div>
        </div>

    </div>
</div>

@include('Surveyor.layouts.footer')

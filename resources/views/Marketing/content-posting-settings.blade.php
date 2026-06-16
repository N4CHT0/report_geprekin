@section('title', 'Integrasi Sosial Media')
@section('breadcrumb', 'Marketing / Content Posting / Social Integrations')

@include('Temp.Investor.header')

<style>
    :root{--s-bg:#f6f8fb;--s-panel:#fff;--s-line:#dce3ec;--s-line-dark:#b7c2cf;--s-text:#111827;--s-muted:#667085;--s-accent:#00a889;--s-danger:#b42318;--s-success:#067647;--s-warning:#b54708;--s-soft:#f8fafc}
    .s-page{min-height:calc(100vh - 70px);background:var(--s-bg);padding:0 0 36px;color:var(--s-text)}.s-shell{width:100%;max-width:none;padding:0 28px}.s-top{background:#fff;border-bottom:1px solid var(--s-line)}.s-top-inner{display:flex;align-items:center;justify-content:space-between;gap:18px;height:78px}.s-brand{display:flex;align-items:center;gap:14px}.s-mark{width:34px;height:34px;background:linear-gradient(135deg,var(--s-accent),#111827);clip-path:polygon(0 0,45% 0,100% 100%,55% 100%)}.s-title{font-size:25px;font-weight:900;letter-spacing:-.04em}.s-sub{font-size:11px;text-transform:uppercase;letter-spacing:.15em;color:var(--s-muted);font-weight:900;margin-top:4px}.s-nav{display:flex;gap:8px;flex-wrap:wrap}.s-nav a{padding:11px 14px;border:1px solid transparent;border-radius:4px;color:var(--s-text);font-weight:900;text-decoration:none}.s-nav a:hover{background:#f3f4f6}.s-nav a.primary{background:var(--s-text);color:#fff;border-color:var(--s-text)}
    .s-hero{background:#fff;border-bottom:1px solid var(--s-line)}.s-hero-grid{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(380px,.9fr);border-left:1px solid var(--s-line);border-right:1px solid var(--s-line)}.s-copy{padding:34px 36px;border-right:1px solid var(--s-line)}.s-kicker{font-size:12px;text-transform:uppercase;letter-spacing:.16em;font-weight:900;color:#027a66;margin-bottom:16px}.s-h1{margin:0;font-size:42px;line-height:1.05;letter-spacing:-.055em;font-weight:900}.s-desc{margin:16px 0 0;line-height:1.6;color:#475467;max-width:820px}.s-status{display:grid;grid-template-columns:repeat(2,1fr)}.s-stat{padding:28px;border-right:1px solid var(--s-line);border-bottom:1px solid var(--s-line);min-height:130px}.s-stat:nth-child(2n){border-right:0}.s-stat small{display:block;color:var(--s-muted);font-size:12px;text-transform:uppercase;letter-spacing:.11em;font-weight:900}.s-stat strong{display:block;margin-top:10px;font-size:30px;font-weight:900;letter-spacing:-.04em}.s-stat span{display:block;margin-top:10px;color:var(--s-muted);font-weight:750;font-size:13px}
    .s-panel{margin-top:22px;background:#fff;border:1px solid var(--s-line)}.s-panel-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:18px 20px;border-bottom:1px solid var(--s-line)}.s-panel-head h3{margin:0;font-size:17px;font-weight:900}.s-panel-body{padding:22px}.s-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.s-grid.three{grid-template-columns:repeat(3,minmax(0,1fr))}.s-label{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.12em;font-weight:900;margin-bottom:8px}.s-field,.s-textarea{width:100%;border:1px solid var(--s-line-dark);border-radius:4px;background:#fff;color:#111827;font-weight:750;padding:12px 13px;min-height:44px}.s-textarea{min-height:112px;resize:vertical}.s-field:focus,.s-textarea:focus{outline:2px solid rgba(0,168,137,.25);border-color:var(--s-accent)}.s-help{font-size:12px;color:var(--s-muted);line-height:1.45;margin-top:7px}.s-check{display:inline-flex;align-items:center;gap:9px;border:1px solid var(--s-line-dark);padding:11px 13px;border-radius:4px;font-weight:900;background:#fff}.s-check input{width:16px;height:16px}.s-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}.s-btn{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--s-text);background:var(--s-text);color:#fff;text-decoration:none;font-weight:900;padding:12px 16px;border-radius:4px;min-height:44px;cursor:pointer}.s-btn:hover{color:#fff;text-decoration:none}.s-btn.outline{background:#fff;color:var(--s-text)}.s-alert{padding:13px 16px;border:1px solid var(--s-line-dark);background:#fff;margin:18px 0 0;font-weight:800}.s-alert.ok{border-left:5px solid var(--s-success)}.s-alert.err{border-left:5px solid var(--s-danger)}.s-code{background:#0f172a;color:#e5e7eb;padding:14px;border-radius:4px;font-family:ui-monospace,monospace;font-size:12px;overflow:auto;line-height:1.5}.s-badge{display:inline-flex;align-items:center;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:900;text-transform:uppercase}.s-badge.ok{background:#ecfdf3;color:#067647}.s-badge.warn{background:#fffaeb;color:#b54708}.s-note{background:var(--s-soft);border:1px dashed var(--s-line-dark);padding:14px;line-height:1.55;color:#475467;font-weight:750}
    @media(max-width:1000px){.s-hero-grid{grid-template-columns:1fr}.s-copy{border-right:0;border-bottom:1px solid var(--s-line)}.s-grid,.s-grid.three{grid-template-columns:1fr}}@media(max-width:700px){.s-shell{padding:0 14px}.s-top-inner{height:auto;padding:14px 0;display:block}.s-nav{margin-top:12px}.s-h1{font-size:31px}.s-copy{padding:24px 18px}.s-status{grid-template-columns:1fr}.s-stat{border-right:0}}
</style>

@php
    $apiSettings = $apiSettings ?? [];
    $allowedIpsText = implode("\n", $apiSettings['allowed_ips'] ?? []);
    $tiktokActive = !empty($apiSettings['api_utama_enabled']) && !empty($apiSettings['api_utama_token']);
    $apifyActive = !empty($apiSettings['apify_enabled']) && !empty($apiSettings['apify_token']);
@endphp

<div class="s-page">
    <header class="s-top"><div class="s-shell"><div class="s-top-inner"><div class="s-brand"><div class="s-mark"></div><div><div class="s-title">API Settings</div><div class="s-sub">Social Media Integrations</div></div></div><nav class="s-nav"><a href="{{ route('marketing.content-posting') }}">Dashboard</a><a class="primary" href="{{ route('marketing.content-posting.settings') }}">Settings</a></nav></div></div></header>

    <div class="s-shell">
        @if(session('success'))<div class="s-alert ok">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="s-alert err">{{ $errors->first() }}</div>@endif
    </div>

    <section class="s-hero"><div class="s-shell"><div class="s-hero-grid"><div class="s-copy"><div class="s-kicker">Configuration</div><h1 class="s-h1">Setting Integrasi Sosial Media</h1><p class="s-desc">TikTok tetap memakai Omkar. Instagram, Threads, dan X memakai Apify agar alurnya mirip Omkar: cukup simpan API token, lalu dashboard menjalankan actor dan membaca dataset otomatis.</p></div><div class="s-status"><div class="s-stat"><small>TikTok Omkar</small><strong>{{ $tiktokActive ? 'Aktif' : 'Belum' }}</strong><span>{{ $tiktokActive ? 'Siap membaca metrik TikTok' : 'Isi API key Omkar' }}</span></div><div class="s-stat"><small>Apify</small><strong>{{ $apifyActive ? 'Aktif' : 'Belum' }}</strong><span>{{ $apifyActive ? 'Instagram, Threads, X siap sync' : 'Isi token Apify' }}</span></div><div class="s-stat"><small>IP Creator</small><strong>{{ count($apiSettings['allowed_ips'] ?? []) }}</strong><span>IP yang tersimpan</span></div><div class="s-stat"><small>Update</small><strong>{{ $apiSettings['updated_at'] ? 'Tersimpan' : 'Belum' }}</strong><span>{{ $apiSettings['updated_at'] ?? 'Belum ada perubahan' }}</span></div></div></div></div></section>

    <main class="s-shell">
        <section class="s-panel">
            <div class="s-panel-head"><h3>Konfigurasi TikTok Omkar</h3><span class="s-badge {{ $tiktokActive ? 'ok' : 'warn' }}">{{ $tiktokActive ? 'Ready' : 'Action Required' }}</span></div>
            <div class="s-panel-body">
                <form method="POST" action="{{ route('marketing.content-posting.settings.save') }}">
                    @csrf
                    <input type="hidden" name="api_utama_base_url" value="https://tiktok-scraper.omkar.cloud/tiktok/videos/details">
                    <input type="hidden" name="tiktok_api_read_views" value="1">
                    <input type="hidden" name="apify_enabled" value="{{ !empty($apiSettings['apify_enabled']) ? 1 : 0 }}">
                    <input type="hidden" name="apify_token" value="{{ $apiSettings['apify_token'] ?? '' }}">
                    <input type="hidden" name="apify_instagram_actor" value="{{ $apiSettings['apify_instagram_actor'] ?? 'apify/instagram-post-scraper' }}">
                    <input type="hidden" name="apify_threads_actor" value="{{ $apiSettings['apify_threads_actor'] ?? 'apify/threads-scraper' }}">
                    <input type="hidden" name="apify_x_actor" value="{{ $apiSettings['apify_x_actor'] ?? 'apidojo/tweet-scraper' }}">
                    <div class="s-grid">
                        <div><label class="s-label">Status API</label><label class="s-check"><input type="checkbox" name="api_utama_enabled" value="1" @checked(!empty($apiSettings['api_utama_enabled']))> Aktifkan baca views otomatis</label><div class="s-help">Centang agar tombol sync dapat membaca TikTok.</div></div>
                        <div><label class="s-label">API Key Omkar</label><input class="s-field" type="password" name="api_utama_token" value="{{ old('api_utama_token', $apiSettings['api_utama_token'] ?? '') }}" placeholder="Paste API key Omkar"><div class="s-help">Token dikirim sebagai header <b>API-Key</b>.</div></div>
                        <div><label class="s-label">IP Marketing / Creator</label><textarea class="s-textarea" name="allowed_ips" placeholder="1 baris 1 IP">{{ old('allowed_ips', $allowedIpsText) }}</textarea></div>
                        <div><label class="s-label">Catatan Internal</label><textarea class="s-textarea" name="api_utama_notes" placeholder="Catatan operasional API">{{ old('api_utama_notes', $apiSettings['api_utama_notes'] ?? '') }}</textarea></div>
                    </div>
                    <div class="s-actions"><button class="s-btn" type="submit">Simpan TikTok</button><a class="s-btn outline" href="{{ route('marketing.content-posting') }}">Kembali ke Dashboard</a></div>
                </form>
            </div>
        </section>

        <section class="s-panel">
            <div class="s-panel-head"><h3>Konfigurasi Apify untuk Instagram, Threads, dan X</h3><span class="s-badge {{ $apifyActive ? 'ok' : 'warn' }}">{{ $apifyActive ? 'Ready' : 'Action Required' }}</span></div>
            <div class="s-panel-body">
                <form method="POST" action="{{ route('marketing.content-posting.settings.save') }}">
                    @csrf
                    <input type="hidden" name="api_utama_base_url" value="{{ $apiSettings['api_utama_base_url'] ?? 'https://tiktok-scraper.omkar.cloud/tiktok/videos/details' }}">
                    <input type="hidden" name="api_utama_token" value="{{ $apiSettings['api_utama_token'] ?? '' }}">
                    @if(!empty($apiSettings['api_utama_enabled']))<input type="hidden" name="api_utama_enabled" value="1">@endif
                    <input type="hidden" name="allowed_ips" value="{{ implode("\n", $apiSettings['allowed_ips'] ?? []) }}">
                    <input type="hidden" name="api_utama_notes" value="{{ $apiSettings['api_utama_notes'] ?? '' }}">
                    <div class="s-grid">
                        <div><label class="s-label">Status Apify</label><label class="s-check"><input type="checkbox" name="apify_enabled" value="1" @checked(!empty($apiSettings['apify_enabled']))> Aktifkan Apify Sync</label><div class="s-help">Dipakai untuk Instagram, Threads, dan X/Twitter.</div></div>
                        <div><label class="s-label">Apify API Token</label><input class="s-field" type="password" name="apify_token" value="{{ old('apify_token', $apiSettings['apify_token'] ?? '') }}" placeholder="apify_api_xxxxx"><div class="s-help">Ambil dari Apify &gt; API &gt; Manage tokens. Jangan tampilkan token di screenshot.</div></div>
                        <div><label class="s-label">Instagram Actor ID</label><input class="s-field" name="apify_instagram_actor" value="{{ old('apify_instagram_actor', $apiSettings['apify_instagram_actor'] ?? 'apify/instagram-post-scraper') }}"><div class="s-help">Default: apify/instagram-post-scraper.</div></div>
                        <div><label class="s-label">Threads Actor ID</label><input class="s-field" name="apify_threads_actor" value="{{ old('apify_threads_actor', $apiSettings['apify_threads_actor'] ?? 'apify/threads-scraper') }}"><div class="s-help">Isi sesuai actor Threads yang dipilih di Apify.</div></div>
                        <div><label class="s-label">X / Twitter Actor ID</label><input class="s-field" name="apify_x_actor" value="{{ old('apify_x_actor', $apiSettings['apify_x_actor'] ?? 'apidojo/tweet-scraper') }}"><div class="s-help">Sesuai screenshot Anda: apidojo/tweet-scraper.</div></div>
                        <div class="s-note"><b>Mapping data:</b><br>Instagram: caption, videoPlayCount/videoViewCount, likesCount, commentsCount.<br>X: text, likeCount, replyCount, retweetCount, bookmarkCount.<br>Threads: text/caption, views, likes, replies, reposts.</div>
                    </div>
                    <div class="s-actions"><button class="s-btn" type="submit">Simpan Apify</button></div>
                </form>
            </div>
        </section>

        <section class="s-panel">
            <div class="s-panel-head"><h3>Endpoint Backend yang Dipakai</h3><span>Reference</span></div>
            <div class="s-panel-body"><div class="s-code">TikTok: GET https://tiktok-scraper.omkar.cloud/tiktok/videos/details?video_url=LINK<br>Apify: POST https://api.apify.com/v2/acts/ACTOR_ID/run-sync-get-dataset-items?token=APIFY_TOKEN</div></div>
        </section>
    </main>
</div>

@include('Temp.Investor.footer')

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spin Undian — Geprekin Aja</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --gold: #F5A623;
            --gold-dark: #C8860A;
            --gold-dim: rgba(245,166,35,0.12);
            --ink: #0E0C08;
            --ink2: #1C1408;
            --surface: #161309;
            --surface2: #201C10;
            --surface3: #2A2414;
            --border: rgba(245,166,35,0.18);
            --border2: rgba(245,166,35,0.32);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background: var(--ink);
            color: #F5EDD8;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 700px; height: 500px;
            background: radial-gradient(ellipse at 50% 30%, rgba(245,166,35,0.07) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── NAVBAR ── */
        .navbar {
            position: sticky; top: 0; z-index: 50;
            background: rgba(14,12,8,0.92);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: 56px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .navbar-logo { display: flex; align-items: center; gap: 10px; }
        .navbar-logo img { width: 30px; height: 30px; border-radius: 8px; object-fit: contain; background: var(--surface2); }
        .navbar-logo span { font-size: 15px; font-weight: 700; color: var(--gold); letter-spacing: 0.03em; }
        .navbar-badge {
            font-size: 11px; font-weight: 600;
            background: var(--gold-dim); color: var(--gold);
            border: 1px solid var(--border2);
            padding: 3px 10px; border-radius: 20px; letter-spacing: 0.05em;
        }
        .nav-btn {
            background: var(--surface2); border: 1px solid var(--border);
            color: #F5EDD8; border-radius: 8px;
            padding: 6px 12px; font-size: 13px; font-weight: 500;
            cursor: pointer; transition: all .15s;
            display: flex; align-items: center; gap: 6px;
        }
        .nav-btn:hover { border-color: var(--border2); background: var(--surface3); }

        /* ── LAYOUT ── */
        .layout {
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            gap: 0;
            min-height: calc(100vh - 56px);
            position: relative; z-index: 1;
        }
        @media (max-width: 1100px) {
            .layout { grid-template-columns: 1fr; }
            .side-panel { display: none; }
        }

        /* ── SIDE PANELS ── */
        .side-panel {
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column; overflow: hidden;
        }
        .side-panel.right { border-right: none; border-left: 1px solid var(--border); }
        .panel-header {
            padding: 18px 20px 14px; border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
        }
        .panel-title {
            font-size: 11px; font-weight: 700;
            letter-spacing: 0.1em; text-transform: uppercase; color: rgba(245,166,35,0.7);
        }
        .panel-count {
            font-size: 11px; font-weight: 600;
            background: var(--gold-dim); color: var(--gold);
            padding: 2px 8px; border-radius: 10px; font-family: 'Space Mono', monospace;
        }
        .participant-scroll { flex: 1; overflow: hidden; position: relative; }
        .participant-scroll-inner { position: absolute; inset: 0; overflow: hidden; }
        .participant-row {
            padding: 10px 20px; border-bottom: 1px solid rgba(245,166,35,0.06);
            display: flex; align-items: center; gap: 10px; transition: background .2s;
        }
        .participant-row:hover { background: rgba(245,166,35,0.04); }
        .participant-row.winner-row { background: rgba(245,166,35,0.08); border-color: rgba(245,166,35,0.2); }
        .participant-avatar {
            width: 28px; height: 28px; border-radius: 50%;
            background: var(--surface2); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; color: var(--gold); flex-shrink: 0;
        }
        .participant-name { font-size: 12px; font-weight: 500; color: #F5EDD8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
        .participant-num { font-family: 'Space Mono', monospace; font-size: 11px; font-weight: 700; color: var(--gold); flex-shrink: 0; }

        .winner-item { padding: 12px 20px; border-bottom: 1px solid rgba(245,166,35,0.08); }
        .winner-rank { font-size: 10px; color: rgba(245,166,35,0.5); font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 4px; }
        .winner-name-text { font-size: 13px; font-weight: 600; color: #F5EDD8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .winner-number { font-family: 'Space Mono', monospace; font-size: 18px; font-weight: 700; color: var(--gold); letter-spacing: 0.05em; margin-top: 2px; }
        .winner-struk { font-size: 11px; color: rgba(245,237,216,0.4); margin-top: 2px; }

        /* ── CENTER ── */
        .center-panel {
            display: flex; flex-direction: column; align-items: center;
            padding: 32px 24px 40px; gap: 28px; position: relative;
        }
        .period-badge {
            font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
            color: rgba(245,166,35,0.6); border: 1px solid var(--border);
            padding: 5px 16px; border-radius: 20px; background: var(--gold-dim);
        }

        /* ── 6 REELS ── */
        .reels-wrapper { display: flex; gap: 8px; justify-content: center; align-items: stretch; }
        .reel-col { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .reel-label { font-size: 9px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(245,166,35,0.3); }
        .reel {
            width: 72px; height: 96px; border-radius: 12px;
            background: var(--surface2); border: 1px solid var(--border);
            overflow: hidden; position: relative;
        }
        .reel::before, .reel::after {
            content: ''; position: absolute; inset-x: 0; height: 28px; z-index: 2; pointer-events: none;
        }
        .reel::before { top: 0; background: linear-gradient(to bottom, var(--surface2), transparent); }
        .reel::after  { bottom: 0; background: linear-gradient(to top, var(--surface2), transparent); }
        .reel-highlight { position: absolute; top: 50%; left: 0; right: 0; height: 2px; transform: translateY(-50%); background: var(--gold); opacity: 0.5; z-index: 3; }
        .reel-strip { position: absolute; top: 0; left: 0; right: 0; display: flex; flex-direction: column; }
        .reel-digit {
            height: 96px; display: flex; align-items: center; justify-content: center;
            font-family: 'Space Mono', monospace; font-size: 48px; font-weight: 700;
            color: #F5EDD8; flex-shrink: 0; transition: color .15s;
        }
        .reel-digit.active { color: var(--gold); }

        /* Reel glow on spin */
        .reel.spinning { border-color: rgba(245,166,35,0.5); }
        .reel.landed { border-color: var(--gold); }
        @keyframes reel-land {
            0%   { transform: scaleY(1.06); }
            40%  { transform: scaleY(0.96); }
            70%  { transform: scaleY(1.02); }
            100% { transform: scaleY(1); }
        }
        .reel.landed { animation: reel-land 0.35s ease forwards; }

        /* ── SPIN BUTTON ── */
        .spin-btn {
            position: relative; width: 200px; height: 56px; border-radius: 14px;
            background: var(--gold); border: none; cursor: pointer;
            font-family: 'Space Grotesk', sans-serif; font-size: 16px; font-weight: 700;
            color: var(--ink2); letter-spacing: 0.03em; transition: all .2s; overflow: hidden;
        }
        .spin-btn::after { content: ''; position: absolute; inset: 0; background: rgba(255,255,255,0); transition: background .15s; }
        .spin-btn:hover:not(:disabled)::after { background: rgba(255,255,255,0.12); }
        .spin-btn:active:not(:disabled) { transform: scale(0.97); }
        .spin-btn:disabled { opacity: 0.4; cursor: not-allowed; }
        @keyframes ring-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(245,166,35,0.4); }
            70%  { box-shadow: 0 0 0 14px rgba(245,166,35,0); }
            100% { box-shadow: 0 0 0 0 rgba(245,166,35,0); }
        }
        .spin-btn.ready { animation: ring-pulse 2s ease-out infinite; }

        /* ── COUNTDOWN OVERLAY ── */
        /*
          FIX: Sebelumnya pakai x-text + satu elemen → class 'pop' tidak re-trigger
          karena Alpine tidak replace DOM node-nya.
          Solusi: render 3 elemen terpisah, tampilkan satu per satu via x-show.
          Setiap elemen punya animasi independen → benar-benar fresh tiap angka.
        */
        .countdown-overlay {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(10,8,4,0.88);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity .25s;
        }
        .countdown-overlay.show { opacity: 1; pointer-events: all; }

        .cd-num {
            font-family: 'Space Mono', monospace;
            font-size: 200px; font-weight: 700; color: var(--gold);
            line-height: 1; position: absolute;
            opacity: 0; transform: scale(1.6);
        }
        /* Each digit animates independently */
        @keyframes cd-pop {
            0%   { opacity: 0; transform: scale(1.6); }
            15%  { opacity: 1; transform: scale(0.92); }
            30%  { transform: scale(1); }
            75%  { opacity: 1; transform: scale(1); }
            100% { opacity: 0; transform: scale(0.5); }
        }
        .cd-num.active { animation: cd-pop 0.9s cubic-bezier(.22,.68,0,1.2) forwards; }

        /* GO text */
        .cd-go {
            font-family: 'Space Mono', monospace;
            font-size: 120px; font-weight: 700;
            color: #fff; line-height: 1; position: absolute;
            opacity: 0; transform: scale(0.5);
            letter-spacing: 0.05em;
        }
        @keyframes cd-go {
            0%   { opacity: 0; transform: scale(0.5); }
            20%  { opacity: 1; transform: scale(1.1); }
            40%  { transform: scale(1); }
            80%  { opacity: 1; }
            100% { opacity: 0; transform: scale(1.5); }
        }
        .cd-go.active { animation: cd-go 0.7s ease forwards; }

        /* ── WINNER MODAL ── */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 300;
            background: rgba(10,8,4,0.85);
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none; transition: opacity .35s;
        }
        .modal-overlay.show { opacity: 1; pointer-events: all; }
        .modal-box {
            background: var(--surface); border: 1px solid var(--border2);
            border-radius: 20px; padding: 40px 36px; text-align: center;
            width: 90%; max-width: 480px;
            transform: scale(.85); transition: transform .35s cubic-bezier(.34,1.56,.64,1);
        }
        .modal-overlay.show .modal-box { transform: scale(1); }
        .modal-trophy {
            width: 60px; height: 60px; border-radius: 50%;
            background: var(--gold-dim); border: 1px solid var(--border2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 28px;
        }
        .modal-title { font-size: 13px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(245,166,35,0.6); margin-bottom: 6px; }
        .modal-winner-name { font-size: 22px; font-weight: 700; color: #F5EDD8; margin-bottom: 20px; }
        .modal-number-box { background: var(--surface2); border: 1px solid var(--border2); border-radius: 12px; padding: 18px 24px; margin-bottom: 12px; }
        .modal-number-label { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(245,166,35,0.5); margin-bottom: 6px; }
        .modal-number-val { font-family: 'Space Mono', monospace; font-size: 52px; font-weight: 700; color: var(--gold); letter-spacing: 0.08em; line-height: 1; }
        .modal-struk { font-size: 13px; color: rgba(245,237,216,0.45); margin-bottom: 24px; }
        .modal-close-btn { background: var(--gold); border: none; border-radius: 10px; padding: 12px 32px; font-family: 'Space Grotesk', sans-serif; font-size: 14px; font-weight: 700; color: var(--ink2); cursor: pointer; transition: all .15s; }
        .modal-close-btn:hover { background: #e09510; }

        /* ── STATS ── */
        .stats-row { display: flex; gap: 12px; width: 100%; max-width: 480px; }
        .stat-card { flex: 1; background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; padding: 12px 16px; text-align: center; }
        .stat-val { font-family: 'Space Mono', monospace; font-size: 22px; font-weight: 700; color: var(--gold); }
        .stat-label { font-size: 10px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(245,166,35,0.45); margin-top: 3px; }

        .empty-state { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 32px 20px; text-align: center; }
        .empty-icon { width: 44px; height: 44px; border-radius: 50%; background: var(--surface2); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .empty-text { font-size: 13px; color: rgba(245,237,216,0.35); }
        .reset-btn { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; background: rgba(220,60,60,0.12); border: 1px solid rgba(220,60,60,0.25); color: rgba(220,100,100,0.9); cursor: pointer; transition: all .15s; }
        .reset-btn:hover { background: rgba(220,60,60,0.2); border-color: rgba(220,60,60,0.4); }

        ::-webkit-scrollbar { width: 3px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border2); border-radius: 4px; }

        footer { text-align: center; background: var(--surface); border-top: 1px solid var(--border); color: rgba(245,237,216,0.25); padding: 12px; font-size: 12px; position: relative; z-index: 1; }
    </style>
</head>
<body x-data="spinApp()" x-init="init()">

    <nav class="navbar">
        <div class="navbar-logo">
            <img src="{{ asset('/img/logo2.jpg') }}" alt="Logo" onerror="this.style.display='none'">
            <span>Geprekin Aja</span>
        </div>
        <div class="navbar-badge">Spin Undian</div>
        <div class="flex items-center gap-2">
            <button class="nav-btn" @click="toggleSound">
                <span x-text="soundEnabled ? '🔊' : '🔇'"></span>
                <span x-text="soundEnabled ? 'Suara ON' : 'Suara OFF'" style="font-size:11px"></span>
            </button>
            <button class="nav-btn" @click="toggleFullscreen">⛶</button>
        </div>
    </nav>

    <div class="layout">

        {{-- LEFT: PESERTA --}}
        <aside class="side-panel">
            <div class="panel-header">
                <span class="panel-title">Peserta Aktif</span>
                <span class="panel-count" x-text="remaining.length"></span>
            </div>
            <div class="participant-scroll">
                <div class="participant-scroll-inner" id="participantScroll">
                    <div id="participantTape">
                        <template x-for="(p, i) in allParticipants" :key="p.nomor">
                            <div class="participant-row" :class="{'winner-row': winners.some(w => w.nomor === p.nomor)}">
                                <div class="participant-avatar" x-text="p.username.charAt(0).toUpperCase()"></div>
                                <span class="participant-name" x-text="p.username"></span>
                                <span class="participant-num" x-text="p.nomor"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </aside>

        {{-- CENTER --}}
        <main class="center-panel">
            <div class="period-badge" x-text="'Periode ' + periode"></div>

            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-val" x-text="allParticipants.length"></div>
                    <div class="stat-label">Total Peserta</div>
                </div>
                <div class="stat-card">
                    <div class="stat-val" x-text="remaining.length"></div>
                    <div class="stat-label">Belum Diundi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-val" x-text="winners.length"></div>
                    <div class="stat-label">Pemenang</div>
                </div>
            </div>

            <div class="reels-wrapper">
                <template x-for="(reel, idx) in reels" :key="idx">
                    <div class="reel-col">
                        <div class="reel-label" x-text="'D' + (idx+1)"></div>
                        <div class="reel" :id="'reel-box-' + idx">
                            <div class="reel-highlight"></div>
                            <div class="reel-strip" :id="'reel-strip-' + idx">
                                <template x-for="n in 10" :key="n">
                                    <div class="reel-digit" x-text="(n-1)"></div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <button
                class="spin-btn"
                :class="{ 'ready': !spinning && remaining.length > 0 }"
                :disabled="spinning || remaining.length === 0"
                @click="startCountdown">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px">
                    <template x-if="!spinning">
                        <span x-text="remaining.length === 0 ? 'Peserta Habis' : 'PUTAR UNDIAN'"></span>
                    </template>
                    <template x-if="spinning">
                        <span>Mengundi...</span>
                    </template>
                </div>
            </button>

            <template x-if="remaining.length === 0 && allParticipants.length > 0">
                <p style="font-size:12px;color:rgba(245,166,35,0.45);letter-spacing:0.08em;text-transform:uppercase">
                    Semua peserta telah diundi
                </p>
            </template>
        </main>

        {{-- RIGHT: PEMENANG --}}
        <aside class="side-panel right">
            <div class="panel-header">
                <span class="panel-title">Pemenang</span>
                <div class="flex items-center gap-2">
                    <span class="panel-count" x-text="winners.length"></span>
                    <button class="reset-btn" @click="resetAll">Reset</button>
                </div>
            </div>
            <div style="flex:1;overflow-y:auto">
                <template x-if="winners.length === 0">
                    <div class="empty-state">
                        <div class="empty-icon">🏆</div>
                        <p class="empty-text">Belum ada pemenang</p>
                    </div>
                </template>
                <template x-for="(w, i) in [...winners].reverse()" :key="w.nomor">
                    <div class="winner-item">
                        <div class="winner-rank" x-text="'Pemenang ke-' + (winners.length - i)"></div>
                        <div class="winner-name-text" x-text="w.username"></div>
                        <div class="winner-number" x-text="w.nomor"></div>
                        <div class="winner-struk" x-text="w.nomor_struk ? ('Struk: ' + w.nomor_struk) : ''"></div>
                    </div>
                </template>
            </div>
        </aside>
    </div>

    <footer>Hak Cipta &copy; 2025 Geprekin Aja &mdash; Sistem Undian Berhadiah</footer>

    {{-- COUNTDOWN OVERLAY --}}
    {{--
        FIX: 3 elemen terpisah, masing-masing x-show + class active via JS.
        Tidak bergantung pada Alpine re-render text — setiap elemen fresh animasinya.
    --}}
    <div class="countdown-overlay" :class="{ show: showCountdown }" id="cdOverlay">
        <div class="cd-num" id="cd3">3</div>
        <div class="cd-num" id="cd2">2</div>
        <div class="cd-num" id="cd1">1</div>
        <div class="cd-go"  id="cdGo">GO!</div>
    </div>

    {{-- WINNER MODAL --}}
    <div class="modal-overlay" :class="{ show: showModal }">
        <div class="modal-box">
            <div class="modal-trophy">🏆</div>
            <div class="modal-title">Selamat, pemenang undian!</div>
            <div class="modal-winner-name" x-text="currentWinner?.username ?? '-'"></div>
            <div class="modal-number-box">
                <div class="modal-number-label">Nomor Undian</div>
                <div class="modal-number-val" x-text="currentWinner?.nomor ?? '------'"></div>
            </div>
            <div class="modal-struk" x-text="currentWinner?.nomor_struk ? 'Nomor Struk: ' + currentWinner.nomor_struk : ''"></div>
            <button class="modal-close-btn" @click="closeModal">Lanjutkan</button>
        </div>
    </div>

    <script>
    function spinApp() {
        return {
            allParticipants: @json($participants),
            remaining: [],
            winners: [],
            spinning: false,
            showCountdown: false,
            showModal: false,
            currentWinner: null,
            reels: [0,1,2,3,4,5],
            periode: '{{ now()->locale("id")->isoFormat("MMMM YYYY") }}',
            soundEnabled: true,
            audioCtx: null,
            scrollInterval: null,
            scrollPos: 0,

            init() {
                this.remaining = [...this.allParticipants];
                this.initAudio();
                this.startAutoScroll();
            },

            // ─────────────────────────────────────────────────
            // AUDIO ENGINE
            // Semua suara dibuat dari Web Audio API murni —
            // tidak ada library eksternal, tidak ada file MP3.
            // ─────────────────────────────────────────────────
            initAudio() {
                try {
                    this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                } catch(e) { this.soundEnabled = false; }
            },

            resumeCtx() {
                if (this.audioCtx?.state === 'suspended') this.audioCtx.resume();
            },

            // Utiliti dasar: buat osc + gain, jalankan, stop otomatis
            _osc(freq, type, startAt, duration, vol, pitchEnd) {
                if (!this.soundEnabled || !this.audioCtx) return;
                const ctx = this.audioCtx;
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.type = type;
                osc.frequency.setValueAtTime(freq, startAt);
                if (pitchEnd) osc.frequency.linearRampToValueAtTime(pitchEnd, startAt + duration);
                gain.gain.setValueAtTime(vol, startAt);
                gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);
                osc.start(startAt); osc.stop(startAt + duration);
            },

            // Noise burst (untuk snare/impact)
            _noise(startAt, duration, vol = 0.3) {
                if (!this.soundEnabled || !this.audioCtx) return;
                const ctx    = this.audioCtx;
                const bufLen = ctx.sampleRate * duration;
                const buf    = ctx.createBuffer(1, bufLen, ctx.sampleRate);
                const data   = buf.getChannelData(0);
                for (let i = 0; i < bufLen; i++) data[i] = (Math.random() * 2 - 1);
                const src  = ctx.createBufferSource();
                const gain = ctx.createGain();
                const filt = ctx.createBiquadFilter();
                filt.type = 'bandpass'; filt.frequency.value = 2000; filt.Q.value = 0.8;
                src.buffer = buf;
                src.connect(filt); filt.connect(gain); gain.connect(ctx.destination);
                gain.gain.setValueAtTime(vol, startAt);
                gain.gain.exponentialRampToValueAtTime(0.0001, startAt + duration);
                src.start(startAt); src.stop(startAt + duration);
            },

            // ── Suara countdown: drum roll elektronik ──
            // 3 = deep kick, 2 = mid snap, 1 = high ping
            playCountdownBeep(n) {
                if (!this.soundEnabled || !this.audioCtx) return;
                const ctx = this.audioCtx;
                const t   = ctx.currentTime;
                if (n === 3) {
                    // Deep electronic kick: pitch sweep down + sine body
                    this._osc(200, 'sine', t, 0.35, 0.7, 40);
                    this._osc(200, 'sine', t, 0.35, 0.3, 40);
                    this._noise(t, 0.04, 0.25);
                } else if (n === 2) {
                    // Mid snappy click: square burst + noise
                    this._osc(440, 'square', t, 0.12, 0.25, 220);
                    this._noise(t, 0.06, 0.3);
                    this._osc(880, 'sine', t, 0.08, 0.2);
                } else if (n === 1) {
                    // High metallic ping: two sines + shimmer
                    this._osc(1200, 'sine', t, 0.25, 0.4);
                    this._osc(1800, 'sine', t, 0.15, 0.2);
                    this._osc(600,  'sine', t, 0.1,  0.15);
                } else if (n === 0) {
                    // GO! — power chord burst
                    [261, 329, 392, 523].forEach((f, i) => {
                        this._osc(f, 'sawtooth', t + i*0.015, 0.3, 0.35);
                    });
                    this._noise(t, 0.05, 0.2);
                }
            },

            // ── Suara tick reel: klik mekanis ──
            // Frekuensi naik sedikit saat reel melambat (feel slot machine)
            playTickSound(speed = 1) {
                if (!this.soundEnabled || !this.audioCtx) return;
                const ctx = this.audioCtx;
                const t   = ctx.currentTime;
                const freq = 800 + (1 - Math.min(speed, 1)) * 400;
                this._osc(freq, 'square', t, 0.025, 0.12);
                this._noise(t, 0.015, 0.08);
            },

            // ── Suara reel berhenti (landing) ──
            // Setiap reel punya "thunk" berbeda berdasarkan index
            playLandSound(reelIdx) {
                if (!this.soundEnabled || !this.audioCtx) return;
                const ctx  = this.audioCtx;
                const t    = ctx.currentTime;
                const base = [120, 100, 90, 80, 70, 60][reelIdx] || 80;
                // Heavy thud: pitch sweep + noise burst
                this._osc(base * 2, 'sine', t, 0.2, 0.6, base);
                this._noise(t, 0.08, 0.5);
                // Metallic ring overtone
                this._osc(base * 8, 'sine', t, 0.15, 0.1);
            },

            // ── Fanfare kemenangan ──
            // Arpeggio ascending + chord finish + shimmer
            playWinSound() {
                if (!this.soundEnabled || !this.audioCtx) return;
                const ctx = this.audioCtx;
                const t   = ctx.currentTime;
                // Arpeggio: C E G C' E'
                const arp = [523, 659, 784, 1047, 1319];
                arp.forEach((f, i) => {
                    this._osc(f, 'sine', t + i * 0.10, 0.35, 0.5);
                });
                // Final chord at 0.55s
                [523, 659, 784].forEach(f => {
                    this._osc(f, 'sine', t + 0.55, 0.6, 0.5);
                });
                // Shimmer bell
                this._osc(2093, 'sine', t + 0.55, 0.4, 0.2);
                // Bass hit
                this._osc(130, 'sine', t + 0.55, 0.4, 0.6, 65);
                this._noise(t + 0.55, 0.06, 0.3);
            },

            toggleSound() {
                this.soundEnabled = !this.soundEnabled;
                this.resumeCtx();
            },

            // ── Auto-scroll daftar peserta ──
            startAutoScroll() {
                const container = document.getElementById('participantScroll');
                const tape      = document.getElementById('participantTape');
                if (!container || !tape) return;
                this.scrollInterval = setInterval(() => {
                    const maxScroll = tape.scrollHeight - container.clientHeight;
                    if (maxScroll <= 0) return;
                    this.scrollPos += 0.6;
                    if (this.scrollPos > maxScroll) this.scrollPos = 0;
                    container.scrollTop = this.scrollPos;
                }, 30);
            },

            // ─────────────────────────────────────────────────
            // COUNTDOWN — FIX UTAMA
            // Setiap angka = elemen DOM terpisah yang di-animate
            // langsung via classList. Tidak ada re-render Alpine.
            // ─────────────────────────────────────────────────
            startCountdown() {
                if (this.spinning || this.remaining.length === 0) return;
                this.resumeCtx();
                this.spinning = true;
                this.showCountdown = true;

                const ids  = ['cd3', 'cd2', 'cd1', 'cdGo'];
                const nums = [3, 2, 1, 0]; // 0 = "GO!"
                let step   = 0;

                const showStep = () => {
                    // Remove active dari semua
                    ids.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.classList.remove('active');
                    });

                    if (step >= ids.length) {
                        // Selesai countdown
                        this.showCountdown = false;
                        this.doSpin();
                        return;
                    }

                    // Trigger animasi elemen saat ini
                    const el = document.getElementById(ids[step]);
                    if (el) {
                        // Force reflow agar animasi fresh meski elemen sama
                        void el.offsetWidth;
                        el.classList.add('active');
                    }

                    this.playCountdownBeep(nums[step]);
                    step++;

                    // GO! tampil lebih singkat
                    const delay = step <= 3 ? 950 : 700;
                    setTimeout(showStep, delay);
                };

                showStep();
            },

            // ── Spin utama ──
            doSpin() {
                const idx    = Math.floor(Math.random() * this.remaining.length);
                const winner = this.remaining[idx];
                const digits = String(winner.nomor).padStart(6, '0').split('');
                const SPIN_MS = 5500;

                this.animateReels(digits, SPIN_MS, () => {
                    this.remaining.splice(idx, 1);
                    this.winners.push(winner);
                    this.currentWinner = winner;
                    this.spinning = false;
                    this.playWinSound();
                    this.launchConfetti();
                    setTimeout(() => { this.showModal = true; }, 350);
                });
            },

            // ── Animasi 6 reel dengan stagger landing ──
            animateReels(targetDigits, totalDuration, onDone) {
                const DIGIT_H    = 96;
                const STAGGER_MS = 320; // jarak antar reel berhenti
                // Reel 0 berhenti paling awal, reel 5 paling akhir
                const stopAt = [0,1,2,3,4,5].map(i => totalDuration - (5 - i) * STAGGER_MS - 800);
                let finishedCount = 0;

                [0,1,2,3,4,5].forEach(ri => {
                    const strip  = document.getElementById('reel-strip-' + ri);
                    const box    = document.getElementById('reel-box-' + ri);
                    if (!strip || !box) return;

                    const targetDigit = parseInt(targetDigits[ri]);
                    let startTime     = null;
                    let stopped       = false;
                    let currentOffset = 0;
                    let lastTickAt    = 0;

                    box.classList.add('spinning');

                    const animate = (ts) => {
                        if (!startTime) startTime = ts;
                        const elapsed = ts - startTime;

                        if (!stopped) {
                            const MAX_SPEED = 30;
                            let speed;
                            const rampIn  = 600;
                            const rampOut = 700;
                            const stopTime = stopAt[ri];

                            if (elapsed < rampIn) {
                                speed = MAX_SPEED * (elapsed / rampIn);
                            } else if (elapsed > stopTime - rampOut) {
                                const t = Math.max(0, (elapsed - (stopTime - rampOut)) / rampOut);
                                speed = MAX_SPEED * Math.max(0.02, 1 - t * t);
                            } else {
                                speed = MAX_SPEED;
                            }

                            currentOffset = (currentOffset + speed) % (10 * DIGIT_H);
                            strip.style.transform = `translateY(-${currentOffset}px)`;

                            // Tick sound pada pergantian digit
                            const curLine  = Math.floor(currentOffset / DIGIT_H);
                            const prevLine = Math.floor((currentOffset - speed) / DIGIT_H);
                            if (curLine !== prevLine && ts - lastTickAt > 30) {
                                this.playTickSound(speed / MAX_SPEED);
                                lastTickAt = ts;
                            }

                            // Warna digit aktif saat ini
                            const vis = Math.round(currentOffset / DIGIT_H) % 10;
                            strip.querySelectorAll('.reel-digit').forEach((el, di) => {
                                el.classList.toggle('active', di === vis);
                            });
                        }

                        if (!stopped && elapsed >= stopAt[ri]) {
                            stopped = true;
                            // Snap tepat ke angka target
                            const snapOffset = targetDigit * DIGIT_H;
                            strip.style.transform = `translateY(-${snapOffset}px)`;
                            strip.querySelectorAll('.reel-digit').forEach((el, di) => {
                                el.classList.toggle('active', di === targetDigit);
                            });

                            // Landing sound + animasi bounce reel
                            this.playLandSound(ri);
                            box.classList.remove('spinning');
                            box.classList.remove('landed');
                            void box.offsetWidth;
                            box.classList.add('landed');

                            finishedCount++;
                            if (finishedCount === 6) onDone();
                        }

                        if (!stopped) requestAnimationFrame(animate);
                    };

                    requestAnimationFrame(animate);
                });
            },

            // ── Confetti gold ──
            launchConfetti() {
                const gold = '#F5A623', cream = '#F5EDD8', dark = '#C8860A';
                confetti({ particleCount: 80, spread: 100, origin: { y: 0.5 }, colors: [gold, cream, dark] });
                setTimeout(() => confetti({ particleCount: 50, spread: 80, angle: 60,  origin: { x: 0.1, y: 0.6 }, colors: [gold, cream, '#fff'] }), 200);
                setTimeout(() => confetti({ particleCount: 50, spread: 80, angle: 120, origin: { x: 0.9, y: 0.6 }, colors: [gold, cream, '#fff'] }), 350);
                let elapsed = 0;
                const rain = setInterval(() => {
                    confetti({ particleCount: 12, spread: 60, origin: { x: Math.random(), y: -0.1 }, gravity: 0.6, colors: [gold, cream, dark] });
                    elapsed += 150;
                    if (elapsed > 1500) clearInterval(rain);
                }, 150);
            },

            closeModal() { this.showModal = false; this.currentWinner = null; },

            resetAll() {
                if (!confirm('Reset semua pemenang dan mulai ulang dari awal?')) return;
                this.remaining     = [...this.allParticipants];
                this.winners       = [];
                this.spinning      = false;
                this.showModal     = false;
                this.currentWinner = null;
                this.reels.forEach((_, ri) => {
                    const strip = document.getElementById('reel-strip-' + ri);
                    const box   = document.getElementById('reel-box-'   + ri);
                    if (strip) strip.style.transform = 'translateY(0)';
                    if (box)   { box.classList.remove('spinning', 'landed'); }
                });
            },

            toggleFullscreen() {
                if (!document.fullscreenElement) document.documentElement.requestFullscreen?.();
                else document.exitFullscreen?.();
            }
        }
    }
    </script>
</body>
</html>
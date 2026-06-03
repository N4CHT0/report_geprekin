<!doctype html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Login | Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
  <meta name="color-scheme" content="light dark" />
  <meta name="theme-color" content="#2563eb" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#0b1220" media="(prefers-color-scheme: dark)" />

  <link rel="preload" href="{{ asset('temp/lte/dist/css/adminlte.css') }}" as="style" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
    integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q=" crossorigin="anonymous" media="print"
    onload="this.media='all'" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
    crossorigin="anonymous" />
  <link rel="stylesheet" href="{{ asset('temp/lte/dist/css/adminlte.css') }}" />

  <style>
    :root {
      --primary: #2563eb;
      --bg1: #f4f7ff;
      --bg2: #eef2ff;
      --card: #ffffff;
      --border: rgba(15, 23, 42, .08);
      --text: #0f172a;
      --muted: #64748b;
      --shadow: 0 18px 45px rgba(15, 23, 42, .12);
      --radius: 16px;
    }

    body.lockscreen {
      font-family: "Source Sans 3", system-ui, -apple-system, Segoe UI, Roboto, Arial;
      background: radial-gradient(900px 500px at 20% 10%, var(--bg2), transparent 55%),
        radial-gradient(900px 500px at 80% 20%, #e0f2fe, transparent 55%),
        linear-gradient(180deg, #ffffff, var(--bg1));
      min-height: 100vh;
      margin: 0;
    }

    .page-wrap {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 18px;
    }

    .login-card {
      width: min(420px, 92vw);
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 22px;
      animation: fadeUp .35s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 14px;
    }

    .brand img {
      width: 52px;
      height: 52px;
      border-radius: 14px;
      object-fit: cover;
      border: 1px solid rgba(15, 23, 42, .08);
    }

    .brand h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 800;
      color: var(--text);
    }

    .brand p {
      margin: 0;
      font-size: 13px;
      color: var(--muted);
    }

    .step-line {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 8px 0 16px;
      color: var(--muted);
      font-size: 12px;
    }

    .bar {
      flex: 1;
      height: 6px;
      border-radius: 999px;
      background: rgba(37, 99, 235, .12);
      overflow: hidden;
    }

    .bar span {
      display: block;
      height: 100%;
      width: 50%;
      background: var(--primary);
      transition: width .25s ease;
    }

    .dot {
      width: 10px;
      height: 10px;
      border-radius: 999px;
      background: rgba(37, 99, 235, .18);
      border: 1px solid rgba(37, 99, 235, .25);
    }

    .dot.active {
      background: var(--primary);
      border-color: var(--primary);
    }

    .form-control.soft {
      border-radius: 12px !important;
      padding: 12px 12px;
      border: 1px solid rgba(15, 23, 42, .10) !important;
      box-shadow: none !important;
    }

    .form-control.soft:focus {
      border-color: rgba(37, 99, 235, .45) !important;
      box-shadow: 0 0 0 .25rem rgba(37, 99, 235, .12) !important;
    }

    .btn.soft {
      border-radius: 12px;
      padding: 11px 12px;
      font-weight: 800;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-primary.soft {
      background: var(--primary) !important;
      border: 0 !important;
    }

    .btn-light.soft {
      border: 1px solid rgba(15, 23, 42, .10) !important;
      background: #fff !important;
    }

    .step {
      display: none;
      animation: slide .22s ease both;
    }

    .step.active { display: block; }

    @keyframes slide {
      from { opacity: 0; transform: translateY(6px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .pw-wrap { position: relative; }

    .pw-toggle {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      border: 0;
      background: transparent;
      color: #64748b;
      padding: 6px 8px;
      border-radius: 10px;
    }

    .pw-toggle:hover { background: rgba(15, 23, 42, .05); }

    .hint {
      color: var(--muted);
      font-size: 12px;
      margin-top: 12px;
      text-align: center;
    }

    .footer {
      margin-top: 14px;
      color: #94a3b8;
      font-size: 12px;
      text-align: center;
    }

    .spinner {
      width: 16px; height: 16px;
      border-radius: 999px;
      border: 2px solid rgba(255,255,255,.5);
      border-top-color: #fff;
      animation: spin 1s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    @media (prefers-reduced-motion: reduce) {
      .login-card, .step, .spinner { animation: none !important; }
      .bar span { transition: none !important; }
    }
  </style>
</head>

<body class="lockscreen">
  <div class="page-wrap">
    <div class="login-card">
      <div class="brand">
        <img src="{{ asset('img/logo2.jpg') }}" alt="Logo">
        <div>
          <h3>Geprekin V.1.1</h3>
          <p>Home Page</p>
        </div>
      </div>

      <div class="step-line">
        <span class="dot active" id="dot1"></span>
        <div class="bar"><span id="barFill"></span></div>
        <span class="dot" id="dot2"></span>
        <span id="stepLabel">Step 1/2</span>
      </div>

      <form id="lockscreenForm" action="{{ route('auth.investor.login') }}" method="POST" novalidate>
        @csrf

         Step Email 
        <div id="stepEmail" class="step active">
          <div class="mb-3">
            <input type="email" id="emailInput" name="email"
              class="form-control form-control-lg soft"
              placeholder="Email" autocomplete="username" required>
            @error('email')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="d-grid">
            <button type="button" id="nextBtn" class="btn btn-primary soft btn-lg">
              Next <i class="bi bi-arrow-right"></i>
            </button>
          </div>
        </div>

        <div id="stepPassword" class="step">
          <div class="mb-3 pw-wrap">
            <input type="password" id="passwordInput" name="password"
              class="form-control form-control-lg soft"
              placeholder="Password" autocomplete="current-password" required>
            <button type="button" class="pw-toggle" id="togglePw" aria-label="Toggle password">
              <i class="bi bi-eye"></i>
            </button>
            @error('password')
              <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>

          <div class="d-grid gap-2">
            <button type="submit" id="loginBtn" class="btn btn-primary soft btn-lg">
              <span id="loginText">Login</span>
            </button>
            <button type="button" id="backBtn" class="btn btn-light soft btn-lg">
              <i class="bi bi-arrow-left"></i> Back
            </button>
          </div>
        </div>
      </form>

      <div class="hint" id="helperText">Masukkan email Anda untuk melanjutkan.</div>
      <div class="footer">Copyright © 2024-2025 by Geprekinaja</div>
      <div class="footer">
          <a href="{{ route('password.request') }}">Lupa Password</a>
          <!--<label>Selamat Datang</label>-->
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const form = document.getElementById("lockscreenForm");

      const stepEmail = document.getElementById("stepEmail");
      const stepPassword = document.getElementById("stepPassword");

      const emailInput = document.getElementById("emailInput");
      const passwordInput = document.getElementById("passwordInput");

      const nextBtn = document.getElementById("nextBtn");
      const backBtn = document.getElementById("backBtn");

      const dot2 = document.getElementById("dot2");
      const barFill = document.getElementById("barFill");
      const stepLabel = document.getElementById("stepLabel");
      const helperText = document.getElementById("helperText");

      const togglePw = document.getElementById("togglePw");
      const loginBtn = document.getElementById("loginBtn");

      function toPassword() {
        const email = emailInput.value.trim();
        if (!email) {
          emailInput.focus();
          alert("Masukkan email terlebih dahulu!");
          return;
        }
        stepEmail.classList.remove("active");
        stepPassword.classList.add("active");

        dot2.classList.add("active");
        barFill.style.width = "100%";
        stepLabel.textContent = "Step 2/2";
        helperText.textContent = "Masukkan password untuk masuk.";

        setTimeout(() => passwordInput.focus(), 60);
      }

      function toEmail() {
        stepPassword.classList.remove("active");
        stepEmail.classList.add("active");

        dot2.classList.remove("active");
        barFill.style.width = "50%";
        stepLabel.textContent = "Step 1/2";
        helperText.textContent = "Masukkan email Anda untuk melanjutkan.";

        setTimeout(() => emailInput.focus(), 60);
      }

      nextBtn.addEventListener("click", toPassword);
      backBtn.addEventListener("click", toEmail);

      emailInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter") {
          e.preventDefault();
          toPassword();
        }
      });

      togglePw.addEventListener("click", () => {
        const isPw = passwordInput.type === "password";
        passwordInput.type = isPw ? "text" : "password";
        togglePw.innerHTML = isPw ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
      });

      form.addEventListener("submit", () => {
        loginBtn.disabled = true;
        loginBtn.innerHTML = '<span class="spinner"></span> <span>Signing in...</span>';
      });

      barFill.style.width = "50%";
      emailInput.focus();
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
  <script src="{{ asset('temp/lte/dist/js/adminlte.js') }}"></script>
</body>

</html>


<!--<!DOCTYPE html>-->
<!--<html lang="id">-->
<!--<head>-->
<!--  <meta charset="UTF-8" />-->
<!--  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>-->
<!--  <title>Maintenance</title>-->
<!--  <style>-->
<!--    :root{-->
<!--      --bg1:#f4f7ff;-->
<!--      --bg2:#fff7f1;-->
<!--      --card:#ffffffcc;-->
<!--      --text:#1f2937;-->
<!--      --muted:#6b7280;-->
<!--      --primary:#4f46e5;-->
<!--      --primary2:#06b6d4;-->
<!--      --shadow: 0 20px 60px rgba(0,0,0,.12);-->
<!--      --radius: 18px;-->
<!--    }-->

<!--    *{box-sizing:border-box}-->
<!--    body{-->
<!--      margin:0;-->
<!--      font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial;-->
<!--      min-height:100vh;-->
<!--      display:flex;-->
<!--      align-items:center;-->
<!--      justify-content:center;-->
<!--      color:var(--text);-->
<!--      background:-->
<!--        radial-gradient(900px 600px at 20% 20%, rgba(79,70,229,.18), transparent 60%),-->
<!--        radial-gradient(800px 520px at 80% 30%, rgba(6,182,212,.18), transparent 55%),-->
<!--        radial-gradient(700px 520px at 40% 85%, rgba(245,158,11,.16), transparent 55%),-->
<!--        linear-gradient(135deg, var(--bg1), var(--bg2));-->
<!--      overflow:hidden;-->
<!--    }-->

    <!--/* floating blobs */-->
<!--    .blob{-->
<!--      position:absolute;-->
<!--      width:340px;height:340px;-->
<!--      filter: blur(30px);-->
<!--      opacity:.45;-->
<!--      border-radius: 50%;-->
<!--      animation: float 8s ease-in-out infinite;-->
<!--      pointer-events:none;-->
<!--    }-->
<!--    .blob.b1{background: #4f46e5; left:-120px; top:-120px;}-->
<!--    .blob.b2{background: #06b6d4; right:-140px; top:40px; animation-delay: -2s;}-->
<!--    .blob.b3{background: #f59e0b; left:25%; bottom:-180px; animation-delay: -4s;}-->

<!--    @keyframes float{-->
<!--      0%,100%{ transform: translate(0,0) scale(1); }-->
<!--      50%{ transform: translate(18px, -16px) scale(1.03); }-->
<!--    }-->

<!--    .card{-->
<!--      width:min(560px, 92vw);-->
<!--      background: var(--card);-->
<!--      backdrop-filter: blur(10px);-->
<!--      border: 1px solid rgba(255,255,255,.6);-->
<!--      border-radius: var(--radius);-->
<!--      box-shadow: var(--shadow);-->
<!--      padding: 28px 26px;-->
<!--      text-align:center;-->
<!--      position:relative;-->
<!--      animation: pop .6s ease both;-->
<!--    }-->

<!--    @keyframes pop{-->
<!--      from{ opacity:0; transform: translateY(14px) scale(.98); }-->
<!--      to{ opacity:1; transform: translateY(0) scale(1); }-->
<!--    }-->

<!--    .badge{-->
<!--      display:inline-flex;-->
<!--      align-items:center;-->
<!--      gap:10px;-->
<!--      padding: 8px 12px;-->
<!--      border-radius: 999px;-->
<!--      background: rgba(79,70,229,.10);-->
<!--      color: var(--primary);-->
<!--      font-weight: 600;-->
<!--      font-size: 13px;-->
<!--      margin-bottom: 14px;-->
<!--    }-->

<!--    /* spinning gear */-->
<!--    .gear{-->
<!--      width:16px;height:16px;-->
<!--      border-radius:50%;-->
<!--      border:2px solid rgba(79,70,229,.25);-->
<!--      border-top-color: var(--primary);-->
<!--      animation: spin 1s linear infinite;-->
<!--    }-->
<!--    @keyframes spin{ to{ transform: rotate(360deg);} }-->

<!--    h1{-->
<!--      margin: 6px 0 8px;-->
<!--      font-size: 26px;-->
<!--      letter-spacing:.2px;-->
<!--    }-->
<!--    p{-->
<!--      margin: 0 auto 18px;-->
<!--      max-width: 46ch;-->
<!--      color: var(--muted);-->
<!--      line-height:1.55;-->
<!--      font-size: 15px;-->
<!--    }-->

<!--    .illustration{-->
<!--      width: 92px;-->
<!--      height: 92px;-->
<!--      margin: 10px auto 8px;-->
<!--      display:grid;-->
<!--      place-items:center;-->
<!--      border-radius: 24px;-->
<!--      background: linear-gradient(135deg, rgba(79,70,229,.12), rgba(6,182,212,.12));-->
<!--      position: relative;-->
<!--      overflow:hidden;-->
<!--    }-->

<!--    /* subtle shimmer */-->
<!--    .illustration::after{-->
<!--      content:"";-->
<!--      position:absolute;-->
<!--      inset:-40%;-->
<!--      background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent);-->
<!--      transform: rotate(25deg);-->
<!--      animation: shimmer 2.8s ease-in-out infinite;-->
<!--    }-->
<!--    @keyframes shimmer{-->
<!--      0%{ transform: translateX(-40%) rotate(25deg); opacity:.0;}-->
<!--      30%{opacity:.9;}-->
<!--      60%{opacity:.5;}-->
<!--      100%{ transform: translateX(55%) rotate(25deg); opacity:0;}-->
<!--    }-->

<!--    .emoji{-->
<!--      font-size: 38px;-->
<!--      animation: bounce 1.5s ease-in-out infinite;-->
<!--      position:relative;-->
<!--      z-index:1;-->
<!--    }-->
<!--    @keyframes bounce{-->
<!--      0%,100%{ transform: translateY(0); }-->
<!--      50%{ transform: translateY(-6px); }-->
<!--    }-->

<!--    .progress{-->
<!--      height: 10px;-->
<!--      border-radius: 999px;-->
<!--      background: rgba(31,41,55,.08);-->
<!--      overflow:hidden;-->
<!--      position:relative;-->
<!--      margin: 14px 0 10px;-->
<!--    }-->
<!--    .progress .bar{-->
<!--      position:absolute;-->
<!--      inset:0;-->
<!--      width: 35%;-->
<!--      background: linear-gradient(90deg, var(--primary), var(--primary2));-->
<!--      border-radius:999px;-->
<!--      animation: load 1.6s ease-in-out infinite;-->
<!--    }-->
<!--    @keyframes load{-->
<!--      0%{ transform: translateX(-120%); }-->
<!--      100%{ transform: translateX(320%); }-->
<!--    }-->

<!--    .small{-->
<!--      font-size: 13px;-->
<!--      color: rgba(107,114,128,.95);-->
<!--      margin-bottom: 18px;-->
<!--    }-->

<!--    .actions{-->
<!--      display:flex;-->
<!--      gap:10px;-->
<!--      justify-content:center;-->
<!--      flex-wrap:wrap;-->
<!--      margin-top: 6px;-->
<!--    }-->
<!--    .btn{-->
<!--      border:0;-->
<!--      cursor:pointer;-->
<!--      padding: 11px 14px;-->
<!--      border-radius: 12px;-->
<!--      font-weight: 700;-->
<!--      font-size: 14px;-->
<!--      transition: transform .12s ease, box-shadow .12s ease, background .12s ease;-->
<!--    }-->
<!--    .btn:active{ transform: translateY(1px); }-->

<!--    .btn.primary{-->
<!--      color:white;-->
<!--      background: linear-gradient(90deg, var(--primary), var(--primary2));-->
<!--      box-shadow: 0 12px 28px rgba(79,70,229,.25);-->
<!--    }-->
<!--    .btn.primary:hover{ box-shadow: 0 16px 36px rgba(79,70,229,.30); }-->

<!--    .btn.ghost{-->
<!--      background: rgba(255,255,255,.7);-->
<!--      color: var(--text);-->
<!--      border: 1px solid rgba(31,41,55,.10);-->
<!--    }-->
<!--    .btn.ghost:hover{ background: rgba(255,255,255,.9); }-->

<!--    .footer{-->
<!--      margin-top: 18px;-->
<!--      font-size: 12px;-->
<!--      color: rgba(107,114,128,.9);-->
<!--    }-->

<!--    /* accessibility: reduce motion */-->
<!--    @media (prefers-reduced-motion: reduce){-->
<!--      .blob, .gear, .emoji, .progress .bar, .illustration::after, .card{ animation:none !important; }-->
<!--    }-->
<!--  </style>-->
<!--</head>-->
<!--<body>-->
<!--  <div class="blob b1"></div>-->
<!--  <div class="blob b2"></div>-->
<!--  <div class="blob b3"></div>-->

<!--  <main class="card" role="main" aria-live="polite">-->
<!--    <div class="badge"><span class="gear" aria-hidden="true"></span> Sedang maintenance</div>-->

<!--    <div class="illustration" aria-hidden="true">-->
<!--      <div class="emoji">🚧</div>-->
<!--    </div>-->

<!--    <h1>Website sedang kami rapikan</h1>-->
<!--    <p>-->
<!--      Lagi ada perbaikan sistem biar layanan makin stabil dan cepat.-->
<!--      Mohon tunggu sebentar ya—coba refresh beberapa sesi lagi.-->
<!--    </p>-->

<!--    <div class="progress" aria-hidden="true"><div class="bar"></div></div>-->
<!--    <div class="small">Estimasi: sebentar lagi… (kami update secepatnya)</div>-->

<!--    <div class="actions">-->
<!--      <button class="btn primary" onclick="location.reload()">🔄 Refresh</button>-->
<!--       Ganti link ini sesuai kebutuhan (WhatsApp/Telegram/email/support page) -->
<!--      <a class="btn ghost" href="https://wa.me/6282331016638" style="text-decoration:none; display:inline-block;">-->
<!--        💬 Hubungi CS-->
<!--      </a>-->
<!--    </div>-->

<!--    <div class="footer">-->
<!--      Terima kasih sudah menunggu 🙏 Kalau urgent, silakan hubungi CS.-->
<!--    </div>-->
<!--  </main>-->
<!--</body>-->
<!--</html>-->
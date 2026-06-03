{{-- resources/views/Investor/Inventory/guideOmsetSetoran.blade.php --}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guidebook DSC - Omset & Setoran</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{
            --bg:#f4f6fb;
            --card:#ffffff;
            --text:#0f172a;
            --muted:#64748b;
            --border:#e2e8f0;
            --soft:#f8fafc;
            --primary:#0f172a;
            --accent:#0f766e;
            --blue:#206bc4;
            --danger:#dc2626;
            --warn:#b45309;
            --shadow:0 14px 34px rgba(15,23,42,.07);
            --shadow-sm:0 8px 18px rgba(15,23,42,.05);
            --radius:20px;
        }

        *{ -webkit-tap-highlight-color:transparent; }
        html{ scroll-behavior:smooth; }
        body{ background:var(--bg); color:var(--text); }
        .wrap{ max-width:1320px; }

        .hero,
        .guide-card,
        .side-card{
            background:var(--card);
            border:1px solid var(--border);
            border-radius:var(--radius);
            box-shadow:var(--shadow);
        }

        .hero{
            padding:22px;
            background:
                radial-gradient(900px 280px at 0% 0%, rgba(32,107,196,.12), transparent 55%),
                radial-gradient(700px 240px at 100% 0%, rgba(15,118,110,.10), transparent 45%),
                linear-gradient(180deg,#fff 0%,#fbfcff 100%);
        }

        .hero-title{ display:flex; align-items:flex-start; gap:14px; }
        .hero-icon{
            width:48px; height:48px; border-radius:16px;
            display:grid; place-items:center;
            background:#e0f2fe; color:#0369a1;
            border:1px solid #bae6fd;
            font-size:1.25rem; flex:0 0 auto;
        }
        .hero h1{ margin:0; font-size:1.35rem; font-weight:950; letter-spacing:.1px; }
        .hero .sub{ margin-top:5px; color:var(--muted); font-weight:700; font-size:.92rem; }
        .hero-actions{ display:flex; gap:8px; flex-wrap:wrap; }

        .btn{ border-radius:13px; font-weight:850; }
        .btn-dark{ background:#0f172a; border-color:#0f172a; }
        .btn-accent{ background:var(--accent); border-color:var(--accent); color:#fff; }
        .btn-accent:hover{ background:#115e59; border-color:#115e59; color:#fff; }

        .layout{ display:grid; grid-template-columns:300px minmax(0,1fr); gap:16px; margin-top:16px; align-items:start; }
        .side-card{ position:sticky; top:14px; padding:14px; }
        .side-title{ font-weight:950; margin-bottom:10px; }
        .nav-guide{ display:grid; gap:7px; }
        .nav-guide a{
            color:#334155; text-decoration:none; border:1px solid var(--border);
            background:#fff; padding:10px 12px; border-radius:14px;
            font-weight:850; display:flex; align-items:center; gap:9px;
        }
        .nav-guide a:hover{ background:#eef2ff; color:#0f172a; }

        .quick-box{
            margin-top:12px; padding:12px; border-radius:16px;
            border:1px dashed #cbd5e1; background:#f8fafc;
            color:#475569; font-size:.86rem; font-weight:750; line-height:1.5;
        }

        .guide-card{ margin-bottom:16px; overflow:hidden; }
        .guide-head{
            padding:16px 18px; border-bottom:1px solid var(--border);
            background:linear-gradient(180deg,#fff 0%,#fbfcff 100%);
            display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap;
        }
        .guide-head h2{ margin:0; font-size:1.05rem; font-weight:950; display:flex; align-items:center; gap:10px; }
        .guide-body{ padding:18px; }
        .badge-soft{
            display:inline-flex; align-items:center; gap:7px; padding:6px 11px;
            border-radius:999px; border:1px solid var(--border); background:#fff;
            color:#64748b; font-size:.78rem; font-weight:900;
        }

        .step-grid{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .step{
            border:1px solid var(--border); background:#fff; border-radius:17px;
            padding:14px; box-shadow:var(--shadow-sm); min-height:145px;
        }
        .step-no{
            width:32px; height:32px; border-radius:12px; background:#0f172a; color:#fff;
            display:grid; place-items:center; font-weight:950; margin-bottom:10px;
        }
        .step h3{ margin:0 0 6px; font-size:.95rem; font-weight:950; }
        .step p{ margin:0; color:#475569; line-height:1.48; font-size:.88rem; font-weight:650; }

        .flow-line{ display:grid; gap:10px; }
        .flow-item{
            display:grid; grid-template-columns:42px 1fr; gap:12px; align-items:start;
            padding:12px; border:1px solid var(--border); border-radius:16px; background:#fff;
        }
        .flow-icon{
            width:42px; height:42px; border-radius:14px; display:grid; place-items:center;
            background:#f1f5f9; color:#0f172a; font-size:1.05rem;
        }
        .flow-item b{ display:block; margin-bottom:4px; font-weight:950; }
        .flow-item span{ color:#475569; font-weight:650; font-size:.9rem; line-height:1.5; }

        .formula{
            display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px;
        }
        .formula-box{
            border:1px solid var(--border); border-radius:17px; padding:14px; background:#fff;
        }
        .formula-box .label{ color:#64748b; font-weight:900; font-size:.78rem; margin-bottom:8px; }
        .formula-box code{
            display:block; background:#0f172a; color:#e2e8f0; border-radius:12px;
            padding:10px 12px; font-weight:850; white-space:normal;
        }
        .formula-box .desc{ margin-top:8px; color:#475569; font-weight:650; font-size:.86rem; line-height:1.5; }

        .role-table{ border:1px solid var(--border); border-radius:16px; overflow:hidden; }
        .role-row{ display:grid; grid-template-columns:190px 1fr; border-bottom:1px solid var(--border); }
        .role-row:last-child{ border-bottom:0; }
        .role-row > div{ padding:12px 14px; font-weight:750; }
        .role-row > div:first-child{ background:#f8fafc; color:#0f172a; font-weight:950; }
        .ok{ color:#047857; font-weight:950; }
        .bad{ color:#b91c1c; font-weight:950; }
        .warn{ color:#b45309; font-weight:950; }

        .do-dont{ display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .panel{
            border:1px solid var(--border); border-radius:17px; padding:14px; background:#fff;
        }
        .panel h3{ font-size:.98rem; font-weight:950; margin-bottom:10px; }
        .panel ul{ margin:0; padding-left:1.1rem; color:#475569; font-weight:650; line-height:1.55; }
        .panel li+li{ margin-top:6px; }

        .callout{
            border-radius:17px; padding:14px; border:1px solid #bfdbfe; background:#eff6ff;
            color:#1e3a8a; font-weight:750; line-height:1.5;
        }
        .callout.warn{ border-color:#fde68a; background:#fffbeb; color:#92400e; }
        .callout.danger{ border-color:#fecaca; background:#fef2f2; color:#991b1b; }

        .accordion-button{ font-weight:900; }
        .accordion-button:not(.collapsed){ color:#0f172a; background:#f8fafc; box-shadow:none; }
        .accordion-item{ border-color:var(--border); border-radius:14px !important; overflow:hidden; margin-bottom:10px; }

        .checklist label{
            display:flex; gap:10px; align-items:flex-start; padding:10px 12px;
            border:1px solid var(--border); border-radius:14px; margin-bottom:8px; background:#fff;
            font-weight:750; color:#334155;
        }
        .checklist input{ margin-top:3px; }

        .top-btn{
            position:fixed; right:16px; bottom:16px; z-index:50;
            width:46px; height:46px; border-radius:16px; display:grid; place-items:center;
            background:#0f172a; color:#fff; text-decoration:none; box-shadow:0 16px 32px rgba(15,23,42,.24);
        }

        @media(max-width:991.98px){
            .layout{ grid-template-columns:1fr; }
            .side-card{ position:static; }
            .step-grid{ grid-template-columns:1fr; }
            .formula{ grid-template-columns:1fr; }
            .do-dont{ grid-template-columns:1fr; }
        }
        @media(max-width:575.98px){
            .wrap{ padding-left:10px; padding-right:10px; }
            .hero{ padding:16px; border-radius:17px; }
            .hero-title{ gap:10px; }
            .hero-icon{ width:42px; height:42px; border-radius:14px; }
            .hero h1{ font-size:1.08rem; }
            .hero .sub{ font-size:.82rem; }
            .hero-actions .btn{ width:100%; }
            .guide-head,.guide-body{ padding:14px; }
            .role-row{ grid-template-columns:1fr; }
            .role-row > div:first-child{ border-bottom:1px solid var(--border); }
            .nav-guide{ grid-template-columns:1fr; }
        }
    </style>
</head>
<body>
    <main class="container wrap py-3" id="top">
        <section class="hero">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div class="hero-title">
                    <div class="hero-icon"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <h1>Guidebook DSC • Omset & Setoran</h1>
                        <div class="sub">Panduan input omset harian, setoran sales, foto bukti, autosave, dan interpretasi selisih.</div>
                    </div>
                </div>
                <div class="hero-actions">
                    <a href="{{ route('master.dscFormulirOmset.index') }}" class="btn btn-dark">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Form Omset
                    </a>
                    <a href="{{ route('master.dscFormulir.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-box-seam me-1"></i>Form SO
                    </a>
                    <a href="{{ route('investor.sales.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </div>
            </div>
        </section>

        <div class="layout">
            <aside class="side-card">
                <div class="side-title"><i class="bi bi-list-check me-1"></i>Menu Guide</div>
                <nav class="nav-guide">
                    <a href="#ringkas"><i class="bi bi-lightning-charge"></i>Ringkasan Flow</a>
                    <a href="#mulai"><i class="bi bi-play-circle"></i>Cara Mulai</a>
                    <a href="#omset"><i class="bi bi-receipt"></i>Input Omset</a>
                    <a href="#setoran"><i class="bi bi-cash-coin"></i>Setoran Sales</a>
                    <a href="#foto"><i class="bi bi-camera"></i>Bukti Foto</a>
                    <a href="#autosave"><i class="bi bi-cloud-check"></i>Autosave</a>
                    <a href="#aturan"><i class="bi bi-shield-check"></i>Aturan & Role</a>
                    <a href="#masalah"><i class="bi bi-tools"></i>Troubleshooting</a>
                </nav>
                <div class="quick-box">
                    <b>Catatan penting:</b><br>
                    Form omset punya tombol <b>Load</b>, <b>Simpan Shift 1</b>, <b>Simpan Shift 2</b>, upload/camera bukti foto, dan popup aturan saat halaman dibuka.
                </div>
            </aside>

            <section>
                <div class="guide-card" id="ringkas">
                    <div class="guide-head">
                        <h2><i class="bi bi-lightning-charge"></i>Ringkasan Flow Omset & Setoran</h2>
                        <span class="badge-soft"><i class="bi bi-info-circle"></i>Ikuti urutan ini</span>
                    </div>
                    <div class="guide-body">
                        <div class="step-grid">
                            <div class="step"><div class="step-no">1</div><h3>Pilih konteks</h3><p>Pilih outlet dan tanggal. Isi PIC agar tombol simpan aktif dan data tercatat atas nama petugas yang benar.</p></div>
                            <div class="step"><div class="step-no">2</div><h3>Klik Load</h3><p>Load mengambil data omset/setoran yang sudah pernah tersimpan. Kalau belum ada data, form siap diisi dari nol.</p></div>
                            <div class="step"><div class="step-no">3</div><h3>Isi Shift</h3><p>Isi Total Transaction, Diskon, Non Tunai, Expense, Uang Fisik, setoran, dan bukti foto untuk shift yang aktif.</p></div>
                            <div class="step"><div class="step-no">4</div><h3>Cek hasil hitung</h3><p>Total, Cash Difference, Yang Harus Disetor, Total Disetor, dan Selisih dihitung otomatis oleh sistem.</p></div>
                            <div class="step"><div class="step-no">5</div><h3>Simpan</h3><p>Autosave membantu menyimpan, tetapi setelah selesai tetap tekan Simpan Shift 1 atau Simpan Shift 2.</p></div>
                            <div class="step"><div class="step-no">6</div><h3>Pastikan bukti</h3><p>Foto bukti setoran harus jelas. Foto realtime lebih disarankan daripada upload dari galeri.</p></div>
                        </div>
                    </div>
                </div>

                <div class="guide-card" id="mulai">
                    <div class="guide-head"><h2><i class="bi bi-play-circle"></i>Cara Mulai Input</h2><span class="badge-soft">Menu Konteks</span></div>
                    <div class="guide-body">
                        <div class="flow-line">
                            <div class="flow-item"><div class="flow-icon"><i class="bi bi-shop"></i></div><div><b>Outlet</b><span>Pilih outlet yang sesuai. Jangan input outlet lain karena omset dan setoran akan masuk ke outlet yang dipilih.</span></div></div>
                            <div class="flow-item"><div class="flow-icon"><i class="bi bi-calendar-date"></i></div><div><b>Tanggal</b><span>Pastikan tanggal sesuai hari transaksi. Salah tanggal bisa membuat rekap harian dan setoran finance ikut salah.</span></div></div>
                            <div class="flow-item"><div class="flow-icon"><i class="bi bi-person-badge"></i></div><div><b>PIC</b><span>Isi nama petugas. PIC wajib agar tombol simpan aktif dan audit perubahan bisa dibaca jelas.</span></div></div>
                            <div class="flow-item"><div class="flow-icon"><i class="bi bi-cloud-download"></i></div><div><b>Load</b><span>Gunakan Load untuk mengambil data yang sudah tersimpan. Sistem juga bisa auto cek data lama saat outlet/tanggal berubah.</span></div></div>
                        </div>
                    </div>
                </div>

                <div class="guide-card" id="omset">
                    <div class="guide-head"><h2><i class="bi bi-receipt"></i>Interpretasi Form Omset</h2><span class="badge-soft">Shift 1 & Shift 2</span></div>
                    <div class="guide-body">
                        <div class="formula">
                            <div class="formula-box"><div class="label">Rumus Total Omset Tunai</div><code>TOTAL = Total Transaction - Diskon - Non Tunai - Expense</code><div class="desc">Total ini adalah dasar uang tunai yang seharusnya tersedia sebelum dibandingkan dengan uang fisik.</div></div>
                            <div class="formula-box"><div class="label">Rumus Cash Difference</div><code>Cash Difference = Uang Fisik - TOTAL</code><div class="desc">Jika minus, uang fisik lebih kecil dari total tunai. Jika plus, uang fisik lebih besar dari total tunai.</div></div>
                        </div>

                        <div class="callout mt-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Kolom <b>Diskon</b>, <b>Non Tunai</b>, dan <b>Expense</b> mengurangi Total Transaction. Pastikan nominal non tunai tidak ikut dihitung sebagai uang fisik tunai.
                        </div>
                    </div>
                </div>

                <div class="guide-card" id="setoran">
                    <div class="guide-head"><h2><i class="bi bi-cash-coin"></i>Interpretasi Form Setoran Sales</h2><span class="badge-soft">Auto hitung</span></div>
                    <div class="guide-body">
                        <div class="formula">
                            <div class="formula-box"><div class="label">Yang Harus Disetor</div><code>Yang Harus Disetor = Uang Fisik - Hanya Selisih Minus</code><div class="desc">Dipakai untuk menentukan nominal setoran yang seharusnya masuk.</div></div>
                            <div class="formula-box"><div class="label">Total Disetor</div><code>Total Disetor = Sudah Disetor + Admin + Adjustment</code><div class="desc">Menunjukkan total nominal yang dianggap sudah terselesaikan di sisi setoran.</div></div>
                            <div class="formula-box"><div class="label">Selisih Setoran</div><code>Selisih = Total Disetor - Yang Harus Disetor</code><div class="desc">Selisih minus perlu dicek ulang oleh outlet/SPV/finance.</div></div>
                            <div class="formula-box"><div class="label">Catatan Tambahan</div><code>Akumulasi Selisih & Kekurangan Bulan Lalu</code><div class="desc">Field opsional untuk catatan rekap per outlet/tanggal.</div></div>
                        </div>
                    </div>
                </div>

                <div class="guide-card" id="foto">
                    <div class="guide-head"><h2><i class="bi bi-camera"></i>Bukti Foto Setoran</h2><span class="badge-soft">Realtime / Upload</span></div>
                    <div class="guide-body">
                        <div class="do-dont">
                            <div class="panel">
                                <h3 class="ok"><i class="bi bi-check-circle me-1"></i>Yang disarankan</h3>
                                <ul>
                                    <li>Gunakan <b>Ambil Foto Realtime</b> jika kamera browser bisa dipakai.</li>
                                    <li>Pastikan nominal, tanggal, dan bukti transfer/setoran terlihat jelas.</li>
                                    <li>Tunggu sampai preview berubah menjadi <b>Bukti tersimpan (server)</b>.</li>
                                </ul>
                            </div>
                            <div class="panel">
                                <h3 class="bad"><i class="bi bi-x-circle me-1"></i>Yang harus dihindari</h3>
                                <ul>
                                    <li>Jangan tutup halaman saat foto masih proses upload.</li>
                                    <li>Jangan upload foto blur, terpotong, atau bukti yang tidak sesuai tanggal.</li>
                                    <li>Jangan hapus foto kalau setoran sudah selesai tanpa konfirmasi SPV/finance.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="callout warn mt-3"><i class="bi bi-exclamation-triangle me-1"></i>Upload file/galeri tetap boleh, tetapi foto realtime lebih kuat untuk audit karena diambil langsung saat input.</div>
                    </div>
                </div>

                <div class="guide-card" id="autosave">
                    <div class="guide-head"><h2><i class="bi bi-cloud-check"></i>Autosave & Tombol Simpan</h2><span class="badge-soft">Jaringan harus stabil</span></div>
                    <div class="guide-body">
                        <div class="flow-line">
                            <div class="flow-item"><div class="flow-icon"><i class="bi bi-keyboard"></i></div><div><b>Saat mengetik angka</b><span>Sistem menjadwalkan autosave beberapa saat setelah input berubah. Autosave tidak menggantikan pengecekan manual.</span></div></div>
                            <div class="flow-item"><div class="flow-icon"><i class="bi bi-save"></i></div><div><b>Setelah selesai</b><span>Tetap klik <b>Simpan Shift 1</b> atau <b>Simpan Shift 2</b> agar user yakin data sudah tersimpan.</span></div></div>
                            <div class="flow-item"><div class="flow-icon"><i class="bi bi-wifi-off"></i></div><div><b>Kalau koneksi jelek</b><span>Jangan lanjut input banyak data. Tunggu koneksi normal, lalu klik simpan manual untuk memastikan data aman.</span></div></div>
                        </div>
                    </div>
                </div>

                <div class="guide-card" id="aturan">
                    <div class="guide-head"><h2><i class="bi bi-shield-check"></i>Aturan Operasional & Role</h2><span class="badge-soft">Audit aman</span></div>
                    <div class="guide-body">
                        <div class="role-table">
                            <div class="role-row"><div>Crew</div><div>Boleh input data awal sesuai tugas. Kalau nominal sudah tersimpan/terkunci, koreksi sebaiknya melalui SPV/TM Manager.</div></div>
                            <div class="role-row"><div>SPV</div><div>Boleh melakukan pengecekan dan koreksi operasional outlet jika ada kesalahan input.</div></div>
                            <div class="role-row"><div>TM Manager</div><div>Boleh melakukan koreksi yang lebih besar dan bertanggung jawab atas validasi final area/outlet.</div></div>
                            <div class="role-row"><div>Finance/Admin</div><div>Memeriksa bukti setoran, nominal disetor, selisih, dan status review foto.</div></div>
                        </div>
                        <div class="callout danger mt-3"><i class="bi bi-shield-exclamation me-1"></i>Jangan membuka form outlet/tanggal yang sama di dua tab atau dua device sekaligus, karena data terakhir bisa saling menimpa.</div>
                    </div>
                </div>

                <div class="guide-card" id="masalah">
                    <div class="guide-head"><h2><i class="bi bi-tools"></i>Troubleshooting</h2><span class="badge-soft">Jika ada kendala</span></div>
                    <div class="guide-body">
                        <div class="accordion" id="faqGuide">
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">Popup aturan muncul terus, normal?</button></h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqGuide"><div class="accordion-body">Popup aturan ditampilkan saat halaman dibuka supaya user membaca update terbaru. Tombol <b>Aturan</b> di atas bisa dipakai untuk membukanya lagi.</div></div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Data tidak tersimpan?</button></h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqGuide"><div class="accordion-body">Cek outlet, tanggal, PIC, dan koneksi internet. Kalau gagal, screenshot pesan error lalu laporkan ke admin/SPV.</div></div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Foto kamera tidak bisa dibuka?</button></h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqGuide"><div class="accordion-body">Kamera live butuh HTTPS atau izin kamera browser. Jika ditolak, pakai tombol Upload File sebagai fallback.</div></div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Selisih minus harus bagaimana?</button></h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqGuide"><div class="accordion-body">Cek ulang Total Transaction, Diskon, Non Tunai, Expense, Uang Fisik, dan nominal Sudah Disetor. Jika masih minus, laporkan ke SPV/finance.</div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="guide-card">
                    <div class="guide-head"><h2><i class="bi bi-clipboard-check"></i>Checklist Sebelum Logout</h2><span class="badge-soft">Wajib dicek</span></div>
                    <div class="guide-body checklist">
                        <label><input type="checkbox"> Outlet, tanggal, dan PIC sudah benar.</label>
                        <label><input type="checkbox"> Shift 1 dan/atau Shift 2 sudah diisi sesuai kondisi outlet.</label>
                        <label><input type="checkbox"> Total, Cash Difference, Yang Harus Disetor, Total Disetor, dan Selisih sudah dicek.</label>
                        <label><input type="checkbox"> Bukti foto setoran sudah terlihat jelas dan tersimpan di server.</label>
                        <label><input type="checkbox"> Tombol Simpan Shift sudah ditekan setelah selesai input.</label>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <a href="#top" class="top-btn" title="Ke atas"><i class="bi bi-arrow-up"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

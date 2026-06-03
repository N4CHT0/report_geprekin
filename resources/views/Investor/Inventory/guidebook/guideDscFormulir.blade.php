{{-- resources/views/Investor/Inventory/guidebook/guideDscFormulir.blade.php --}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guidebook DSC Formulir</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root{
            --bg:#f4f6fb; --card:#fff; --text:#0f172a; --muted:#64748b; --line:#e2e8f0;
            --dark:#0f172a; --green:#0f766e; --blue:#2563eb; --red:#b91c1c; --soft:#f8fafc;
            --shadow:0 18px 45px rgba(15,23,42,.08); --radius:20px;
        }
        *{scroll-behavior:smooth}
        body{background:var(--bg); color:var(--text); font-family:system-ui,-apple-system,"Segoe UI",sans-serif;}
        .wrap{max-width:1380px}
        .hero{background:radial-gradient(900px 300px at 0% 0%,rgba(37,99,235,.12),transparent 55%),radial-gradient(700px 240px at 100% 0%,rgba(15,118,110,.10),transparent 50%),#fff;border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:22px}
        .hero h1{font-size:1.45rem;font-weight:950;margin:0;letter-spacing:-.02em}
        .sub{color:var(--muted);font-weight:750;font-size:.92rem}
        .btn{border-radius:13px;font-weight:850}.btn-dark{background:var(--dark);border-color:var(--dark)}
        .layout{display:grid;grid-template-columns:310px minmax(0,1fr);gap:16px;margin-top:16px}
        .side{position:sticky;top:14px;align-self:start;background:#fff;border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:14px;max-height:calc(100vh - 28px);overflow:auto}
        .searchbox{position:relative}.searchbox i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted)}.searchbox input{padding-left:38px;border-radius:14px;font-weight:800;height:44px}
        .navlink{display:flex;align-items:center;gap:10px;width:100%;text-decoration:none;color:#334155;padding:10px 11px;border-radius:14px;font-weight:850;margin-top:5px;border:1px solid transparent}.navlink:hover,.navlink.active{background:#eef2ff;color:#1e3a8a;border-color:#c7d2fe}.navlink i{width:20px;text-align:center}
        .cardx{background:#fff;border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px;margin-bottom:16px}.cardx h2{font-size:1.13rem;font-weight:950;margin:0 0 7px}.cardx h3{font-size:.95rem;font-weight:950;margin:0 0 8px}.smallmuted{color:var(--muted);font-weight:750;font-size:.86rem}
        .pill{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--line);border-radius:999px;background:#fff;padding:6px 11px;font-weight:900;font-size:.78rem;color:#475569}.pill.ok{background:#ecfdf5;border-color:#bbf7d0;color:#047857}.pill.warn{background:#fffbeb;border-color:#fde68a;color:#92400e}.pill.bad{background:#fef2f2;border-color:#fecaca;color:#991b1b}.pill.blue{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
        .stepgrid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.step{background:var(--soft);border:1px solid var(--line);border-radius:17px;padding:14px}.step .num{width:34px;height:34px;border-radius:12px;background:#0f172a;color:#fff;display:grid;place-items:center;font-weight:950;margin-bottom:10px}.step b{display:block;margin-bottom:5px}.step p{margin:0;color:var(--muted);font-weight:700;font-size:.85rem}
        .formula{background:#0f172a;color:#e5e7eb;border-radius:18px;padding:15px;border:1px solid #1e293b}.formula code{color:#a7f3d0;font-weight:900}.formula .line{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid rgba(255,255,255,.08);padding:9px 0}.formula .line:last-child{border-bottom:0}.mono{font-family:ui-monospace,SFMono-Regular,Menlo,monospace}
        .timeline{position:relative;padding-left:21px}.timeline:before{content:"";position:absolute;left:8px;top:2px;bottom:2px;width:2px;background:#cbd5e1}.tl{position:relative;margin-bottom:13px;background:#fff;border:1px solid var(--line);border-radius:15px;padding:11px 12px}.tl:before{content:"";position:absolute;left:-18px;top:14px;width:12px;height:12px;border-radius:99px;background:#2563eb;border:3px solid #dbeafe}.tl b{display:block}.tl span{color:var(--muted);font-weight:700;font-size:.85rem}
        .rule{display:grid;grid-template-columns:190px 1fr;gap:10px;padding:11px 0;border-bottom:1px solid #edf2f7}.rule:last-child{border-bottom:0}.rule b{font-weight:950}.rule span{color:#475569;font-weight:750}
        .accordion-button{font-weight:900}.accordion-item{border-color:var(--line);border-radius:14px!important;overflow:hidden;margin-bottom:10px}.accordion-button:not(.collapsed){background:#f8fafc;color:#0f172a}
        .checklist{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.check{border:1px solid var(--line);background:#fff;border-radius:15px;padding:11px;display:flex;gap:10px;align-items:flex-start}.check input{margin-top:5px}.check label{font-weight:800}.check div{color:var(--muted);font-size:.82rem;font-weight:700}
        .toastcopy{position:fixed;right:18px;bottom:18px;z-index:9999;display:none;background:#0f172a;color:#fff;border-radius:14px;padding:12px 14px;box-shadow:var(--shadow);font-weight:850}
        mark.search-hit{background:#fef08a;padding:0 2px;border-radius:4px}
        @media(max-width:1000px){.layout{grid-template-columns:1fr}.side{position:static;max-height:none}.stepgrid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:575px){.stepgrid,.checklist{grid-template-columns:1fr}.rule{grid-template-columns:1fr}.hero{padding:16px}.cardx{padding:15px}.hero h1{font-size:1.18rem}}
    </style>
</head>
<body>
<main class="container wrap py-3 py-md-4">
    <section class="hero">
        <div class="d-flex justify-content-between gap-3 flex-wrap align-items-start">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="pill blue"><i class="bi bi-journal-richtext"></i> Guidebook</span>
                    <span class="pill ok"><i class="bi bi-shield-check"></i> DSC Formulir</span>
                </div>
                <h1>Panduan Operasional DSC • Warehouse Input</h1>
                <div class="sub mt-2">Panduan menu untuk Load Data, input stok fisik, autosave, Draft, Final, role Crew/SPV/TM Manager, dan History Audit.</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('master.dscFormulir.index') }}" class="btn btn-dark"><i class="bi bi-box-seam me-1"></i>Buka DSC</a>
                <a href="{{ route('master.dscFormulirOmset.index') }}" class="btn btn-outline-secondary"><i class="bi bi-cash-stack me-1"></i>Form Omset</a>
                <button class="btn btn-outline-primary" id="btnPrint"><i class="bi bi-printer me-1"></i>Print</button>
            </div>
        </div>
    </section>

    <section class="layout">
        <aside class="side">
            <div class="searchbox mb-2">
                <i class="bi bi-search"></i>
                <input type="search" id="guideSearch" class="form-control" placeholder="Cari panduan...">
            </div>
            <div class="smallmuted mb-2">Menu panduan</div>
            <a class="navlink active" href="#ringkas"><i class="bi bi-lightning-charge"></i> Ringkasan Flow</a>
            <a class="navlink" href="#aturan"><i class="bi bi-megaphone"></i> Aturan Terbaru</a>
            <a class="navlink" href="#load"><i class="bi bi-cloud-download"></i> Load Data</a>
            <a class="navlink" href="#input"><i class="bi bi-pencil-square"></i> Cara Input</a>
            <a class="navlink" href="#rumus"><i class="bi bi-calculator"></i> Rumus</a>
            <a class="navlink" href="#draft-final"><i class="bi bi-save2"></i> Draft & Final</a>
            <a class="navlink" href="#role"><i class="bi bi-person-lock"></i> Role & Lock</a>
            <a class="navlink" href="#history"><i class="bi bi-clock-history"></i> History Audit</a>
            <a class="navlink" href="#trouble"><i class="bi bi-tools"></i> Troubleshooting</a>
            <a class="navlink" href="#checklist"><i class="bi bi-check2-square"></i> Checklist Shift</a>
        </aside>

        <div id="guideContent">
            <article class="cardx guide-section" id="ringkas">
                <h2><i class="bi bi-lightning-charge me-1"></i>Ringkasan Flow DSC</h2>
                <div class="smallmuted mb-3">Urutan aman yang harus dilakukan tim outlet.</div>
                <div class="stepgrid">
                    <div class="step"><div class="num">1</div><b>Pilih Konteks</b><p>Pilih Outlet, Tanggal, Shift, isi nama Petugas.</p></div>
                    <div class="step"><div class="num">2</div><b>Load Data</b><p>Ambil bahan, opening stock, draft/final sebelumnya.</p></div>
                    <div class="step"><div class="num">3</div><b>Input Stok Fisik</b><p>Isi Ending Stock sesuai hasil hitung barang fisik.</p></div>
                    <div class="step"><div class="num">4</div><b>Draft / Final</b><p>Shift 1 Draft. Shift 2 Draft atau Final setelah lengkap.</p></div>
                </div>
            </article>

            <article class="cardx guide-section" id="aturan">
                <h2><i class="bi bi-megaphone me-1"></i>Aturan Interpretasi Terbaru</h2>
                <div class="row g-3">
                    <div class="col-lg-7">
                        <div class="rule"><b>Jaringan</b><span>Pastikan internet stabil. Autosave membantu, tetapi data dianggap aman setelah status <b>Tersimpan</b> muncul.</span></div>
                        <div class="rule"><b>Jangan 2 tab</b><span>Jangan buka outlet/tanggal/shift yang sama di 2 tab atau 2 device, karena bisa menimpa data terakhir.</span></div>
                        <div class="rule"><b>Ending Manual</b><span>Ending Stock adalah hasil hitung fisik. Angka ini akan menjadi opening shift/tanggal berikutnya.</span></div>
                        <div class="rule"><b>Crew</b><span>Crew hanya boleh mengisi kolom yang masih kosong atau 0. Nominal lebih dari 0 dikunci.</span></div>
                        <div class="rule"><b>SPV/TM Manager</b><span>SPV dan TM Manager dapat revisi angka yang sudah terisi, dan perubahan tercatat di History.</span></div>
                    </div>
                    <div class="col-lg-5">
                        <div class="alert alert-light border rounded-4 h-100 mb-0">
                            <b><i class="bi bi-wifi me-1"></i>Status jaringan:</b>
                            <div id="netStatus" class="mt-2"></div>
                            <hr>
                            <button class="btn btn-sm btn-outline-dark" data-copy="#sopSingkat"><i class="bi bi-copy me-1"></i>Copy SOP Singkat</button>
                            <div id="sopSingkat" class="d-none">Pastikan outlet, tanggal, shift, dan petugas benar. Klik Load Data sebelum input. Pastikan jaringan stabil sampai status tersimpan. Jangan membuka DSC yang sama di dua tab/device. Ending stock wajib sesuai stok fisik. Shift 1 simpan Draft, Shift 2 boleh Draft/Final. Semua perubahan tercatat di History.</div>
                        </div>
                    </div>
                </div>
            </article>

            <article class="cardx guide-section" id="load">
                <h2><i class="bi bi-cloud-download me-1"></i>Menu Load Data</h2>
                <div class="timeline">
                    <div class="tl"><b>Isi Outlet</b><span>Outlet bisa berupa outlet gabungan karena beberapa ID outlet dari API memiliki nama sama.</span></div>
                    <div class="tl"><b>Isi Tanggal & Shift</b><span>Shift 1 untuk awal hari/shift pertama. Shift 2 akan memakai ending Shift 1 sebagai opening.</span></div>
                    <div class="tl"><b>Isi Petugas</b><span>Nama petugas wajib agar data history dan audit jelas.</span></div>
                    <div class="tl"><b>Klik Load Data</b><span>Sistem mengambil bahan aktif, opening stock, draft terbaru, final terbaru, dan status lock.</span></div>
                </div>
            </article>

            <article class="cardx guide-section" id="input">
                <h2><i class="bi bi-pencil-square me-1"></i>Cara Input Stok</h2>
                <div class="accordion" id="accInput">
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#i1">Kolom Purchase / Mutasi / Adjustment</button></h2><div id="i1" class="accordion-collapse collapse show" data-bs-parent="#accInput"><div class="accordion-body">Isi hanya jika ada pembelian atau perpindahan stok. Untuk Adjustment biasanya dari backoffice/import adjustment, bukan asal ubah manual.</div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#i2">Kolom Ending Stock</button></h2><div id="i2" class="accordion-collapse collapse" data-bs-parent="#accInput"><div class="accordion-body">Ending adalah hasil hitung fisik barang di outlet. Ini bukan hasil rumus sistem. Ending akan menjadi opening berikutnya.</div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#i3">Search / Scan</button></h2><div id="i3" class="accordion-collapse collapse" data-bs-parent="#accInput"><div class="accordion-body">Gunakan kolom Search untuk lompat ke bahan tertentu. Di HP, sistem menampilkan satu bahan per layar agar input lebih mudah.</div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#i4">Next isi 0</button></h2><div id="i4" class="accordion-collapse collapse" data-bs-parent="#accInput"><div class="accordion-body">Tombol Next isi 0 dipakai saat bahan memang habis/kosong fisik. Pastikan bukan lupa hitung.</div></div></div>
                </div>
            </article>

            <article class="cardx guide-section" id="rumus">
                <h2><i class="bi bi-calculator me-1"></i>Rumus yang Dipakai</h2>
                <div class="formula">
                    <div class="line"><span>Total Available</span><code>Total = Open + Purchase In + Mutasi In - Mutasi Out + Adjustment</code></div>
                    <div class="line"><span>Actual Used</span><code>Used = Total - Ending Stock</code></div>
                    <div class="line"><span>Waste Tepung</span><code>Waste T = Waste Product + Waste Bahan</code></div>
                    <div class="line"><span>Actual Tepung</span><code>Actual Tepung = Used - Waste T</code></div>
                    <div class="line"><span>Opening Berikutnya</span><code>Opening = Ending terakhir yang diketahui</code></div>
                </div>
                <div class="alert alert-info mt-3 mb-0"><b>Catatan:</b> Ending Stock tetap input manual dari stok fisik, bukan otomatis dari rumus.</div>
            </article>

            <article class="cardx guide-section" id="draft-final">
                <h2><i class="bi bi-save2 me-1"></i>Draft & Final</h2>
                <div class="row g-3">
                    <div class="col-md-6"><div class="p-3 border rounded-4 h-100"><span class="pill warn mb-2">Shift 1</span><h3>Save Draft</h3><p class="smallmuted mb-0">Shift 1 disimpan sebagai draft. Ending Shift 1 menjadi Opening Shift 2 meskipun belum final.</p></div></div>
                    <div class="col-md-6"><div class="p-3 border rounded-4 h-100"><span class="pill ok mb-2">Shift 2</span><h3>Draft / Final</h3><p class="smallmuted mb-0">Shift 2 boleh draft dulu. Jika sudah lengkap, klik Final agar data masuk tabel stock final dan terkunci.</p></div></div>
                </div>
                <div class="alert alert-warning mt-3 mb-0"><b>Penting:</b> Draft Shift 2 kemarin menjadi Opening Shift 1 besok agar stok fisik terakhir tetap dipakai walaupun belum final.</div>
            </article>

            <article class="cardx guide-section" id="role">
                <h2><i class="bi bi-person-lock me-1"></i>Role & Lock Input</h2>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light"><tr><th>Role</th><th>Boleh Isi Kosong/0</th><th>Boleh Ubah Nominal > 0</th><th>Catatan</th></tr></thead>
                        <tbody>
                            <tr><td><b>Crew</b></td><td><span class="pill ok">Ya</span></td><td><span class="pill bad">Tidak</span></td><td>Jika sudah ada nominal lebih dari 0, kolom dikunci.</td></tr>
                            <tr><td><b>SPV</b></td><td><span class="pill ok">Ya</span></td><td><span class="pill ok">Ya</span></td><td>Revisi harus bisa dipertanggungjawabkan di History.</td></tr>
                            <tr><td><b>TM Manager</b></td><td><span class="pill ok">Ya</span></td><td><span class="pill ok">Ya</span></td><td>Bisa koreksi data outlet jika ada komplain/validasi.</td></tr>
                            <tr><td><b>Superadmin</b></td><td><span class="pill ok">Ya</span></td><td><span class="pill ok">Ya</span></td><td>Untuk monitoring dan audit teknis.</td></tr>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="cardx guide-section" id="history">
                <h2><i class="bi bi-clock-history me-1"></i>History Audit</h2>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <h3>Yang Dicatat</h3>
                        <div class="rule"><b>Jam</b><span>Waktu perubahan terjadi.</span></div>
                        <div class="rule"><b>Petugas/User</b><span>Nama petugas dan user login yang melakukan perubahan.</span></div>
                        <div class="rule"><b>Nilai lama/baru</b><span>Contoh: Ending Stock 770 → 720.</span></div>
                        <div class="rule"><b>IP & Device</b><span>Untuk bantu audit jika ada komplain data berubah.</span></div>
                    </div>
                    <div class="col-lg-6">
                        <h3>Cara Pakai Saat Komplain</h3>
                        <ol class="smallmuted mb-0">
                            <li>Buka outlet dan tanggal yang dikomplain.</li>
                            <li>Klik menu History.</li>
                            <li>Filter bahan, shift, dan tanggal.</li>
                            <li>Lihat perubahan signifikan, user, IP, dan device.</li>
                            <li>Cocokkan dengan tim outlet/SPV yang bertugas.</li>
                        </ol>
                    </div>
                </div>
            </article>

            <article class="cardx guide-section" id="trouble">
                <h2><i class="bi bi-tools me-1"></i>Troubleshooting Cepat</h2>
                <div class="accordion" id="accTrouble">
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#t1">Data tidak tersimpan</button></h2><div id="t1" class="accordion-collapse collapse show" data-bs-parent="#accTrouble"><div class="accordion-body">Cek jaringan, tunggu status tersimpan, atau klik Save Draft manual. Jangan langsung pindah halaman saat status masih menyimpan.</div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#t2">Opening besok tidak sesuai</button></h2><div id="t2" class="accordion-collapse collapse" data-bs-parent="#accTrouble"><div class="accordion-body">Pastikan Shift 2 tanggal sebelumnya sudah memiliki Ending Stock, baik draft maupun final.</div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#t3">Crew tidak bisa edit</button></h2><div id="t3" class="accordion-collapse collapse" data-bs-parent="#accTrouble"><div class="accordion-body">Jika nominal sudah lebih dari 0, itu memang terkunci untuk Crew. Hubungi SPV atau TM Manager.</div></div></div>
                    <div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#t4">History kosong</button></h2><div id="t4" class="accordion-collapse collapse" data-bs-parent="#accTrouble"><div class="accordion-body">Cek filter tanggal, bahan, shift, dan klik Refresh. History hanya muncul jika ada perubahan nilai.</div></div></div>
                </div>
            </article>

            <article class="cardx guide-section" id="checklist">
                <h2><i class="bi bi-check2-square me-1"></i>Checklist Sebelum Selesai Shift</h2>
                <div class="checklist">
                    <div class="check"><input type="checkbox"><label>Outlet, tanggal, shift, dan petugas sudah benar<div>Jangan input di outlet/tanggal yang salah.</div></label></div>
                    <div class="check"><input type="checkbox"><label>Semua bahan sudah dihitung fisik<div>Ending diisi sesuai hasil hitung barang.</div></label></div>
                    <div class="check"><input type="checkbox"><label>Status autosave sudah Tersimpan<div>Tunggu indikator sebelum keluar halaman.</div></label></div>
                    <div class="check"><input type="checkbox"><label>Shift 1 sudah Draft<div>Agar Shift 2 memiliki opening yang benar.</div></label></div>
                    <div class="check"><input type="checkbox"><label>Shift 2 sudah Draft/Final<div>Ending Shift 2 menjadi opening besok.</div></label></div>
                    <div class="check"><input type="checkbox"><label>Jika ada revisi, cek History<div>Pastikan perubahan bisa dijelaskan.</div></label></div>
                </div>
            </article>
        </div>
    </section>
</main>
<div class="toastcopy" id="toastCopy"><i class="bi bi-check-circle me-1"></i> Teks berhasil dicopy</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
    const links=[...document.querySelectorAll('.navlink')];
    const sections=[...document.querySelectorAll('.guide-section')];
    function setActive(){let cur=sections[0]?.id; sections.forEach(s=>{if(window.scrollY>=s.offsetTop-120)cur=s.id}); links.forEach(a=>a.classList.toggle('active',a.getAttribute('href')==='#'+cur));}
    window.addEventListener('scroll',setActive); setActive();
    document.getElementById('btnPrint')?.addEventListener('click',()=>window.print());
    function updateNet(){const el=document.getElementById('netStatus'); if(!el)return; el.innerHTML=navigator.onLine?'<span class="pill ok"><i class="bi bi-wifi"></i>Online</span><div class="smallmuted mt-2">Jaringan terdeteksi aktif. Tetap tunggu status Tersimpan saat input.</div>':'<span class="pill bad"><i class="bi bi-wifi-off"></i>Offline</span><div class="smallmuted mt-2">Jangan input dulu sampai koneksi normal.</div>';}
    window.addEventListener('online',updateNet); window.addEventListener('offline',updateNet); updateNet();
    document.querySelectorAll('[data-copy]').forEach(btn=>btn.addEventListener('click',async()=>{const target=document.querySelector(btn.dataset.copy); if(!target)return; await navigator.clipboard.writeText(target.textContent.trim()); const t=document.getElementById('toastCopy'); t.style.display='block'; setTimeout(()=>t.style.display='none',1600);}));
    const search=document.getElementById('guideSearch');
    search?.addEventListener('input',function(){const q=this.value.trim().toLowerCase(); sections.forEach(sec=>{sec.style.display=!q||sec.textContent.toLowerCase().includes(q)?'block':'none'});});
})();
</script>
</body>
</html>

<!-- Panduan & Formula -->
<div class="excel-box mb-3" id="section-panduan">
    <div class="excel-box-header" style="background: linear-gradient(90deg, #0f172a, #1e293b); color: #fff; cursor: pointer;" onclick="togglePanduan()">
        <div>
            <h5 class="text-white mb-1"><i class="bi bi-journal-text me-2"></i> Buku Panduan & Penjelasan Formula AI Radar</h5>
            <p class="text-light mb-0" style="opacity: 0.8;">Pelajari cara sistem menghitung skor dan cara menyesuaikan konfigurasinya.</p>
        </div>
        <span id="panduan-icon" class="text-white"><i class="bi bi-chevron-down fs-4"></i></span>
    </div>
    <div class="excel-box-body" id="panduan-body" style="display: none; background: #f8fafc;">
        <div class="row">
            <div class="col-md-6">
                <div class="p-3 border rounded bg-white h-100 shadow-sm">
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="bi bi-calculator me-2"></i> Transisi SOP Spreadsheet ke AI Radar</h6>
                    <p class="text-muted" style="font-size:13px; text-align:justify;">
                        Jika Anda terbiasa dengan <strong>SOP Spreadsheet Lama</strong> (di mana Anda harus mencari data lapangan lalu mengisikan skor manual 1 sampai 5 untuk Traffic, Rumah, dan Fasilitas), sekarang proses tersebut telah <strong>100% diotomatisasi</strong> oleh AI Radar.
                    </p>
                    <ul class="text-muted" style="font-size:13px; padding-left:15px; text-align:justify;">
                        <li class="mb-2"><strong>Otomatisasi Scoring 1-5:</strong> Anda tidak perlu lagi menebak-nebak skor 1-5. Sistem menarik data keramaian langsung dari Google Maps API dalam radius yang ditentukan, lalu AI akan mengkalkulasi dan mengonversinya menjadi skor dan Grade secara otomatis.</li>
                        <li class="mb-2"><strong>Total Penambah & Pengurang Nilai:</strong> Sama seperti di Spreadsheet, hasil otomatisasi dari Fasilitas Umum, Rumah, dan Traffic akan dijumlahkan sebagai *Penambah Nilai*, dikurangi dengan keberadaan Kompetitor (*Pengurang Nilai*).</li>
                        <li class="mb-2"><strong>Nilai Ekstra (Harga Kompetitor):</strong> Fitur ini sama persis dengan SOP lama. Jika kompetitor mematok harga lebih mahal (misal >10.000), Anda mendapat bonus poin persentase (+), dan sebaliknya (-).</li>
                        <li class="mb-0"><strong>Potensi Omset & MoE:</strong> Setelah skor akhir didapat, sistem akan otomatis menghitung potensi *Customer Unit (CU)*, mengalikannya dengan *Average Check*, lalu memotongnya dengan *Margin of Error (MoE)* (30% untuk Madura, 20% untuk reguler) sama seperti Spreadsheet Anda!</li>
                    </ul>
                    
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4"><i class="bi bi-gear-wide-connected me-2"></i> Dari Mana AI Mendapat Angka-Angkanya? (Logika Heuristik)</h6>
                    <ul class="text-muted" style="font-size:13px; padding-left:15px; text-align:justify;">
                        <li class="mb-2">
                            <strong>1. Estimasi Jumlah Rumah (Bangunan):</strong> <br>
                            Karena Google Maps tidak memiliki data sensus rumah warga, AI memprediksinya secara pintar (Heuristik) berdasarkan fasilitas publik. 
                            <strong>Logika AI:</strong> <em>"Di mana ada banyak sekolah, pasar, dan perkantoran besar, di situ pasti padat pemukiman."</em><br>
                            <span class="text-secondary">Rumus: Base Rumah + (Jumlah Pasar × Pengali) + (Sekolah × Pengali) + (Kantor × Pengali).</span>
                        </li>
                        <li class="mb-2">
                            <strong>2. Distribusi Kuadran Ekonomi (Q1 - Q4):</strong> <br>
                            Sistem akan mengecek skor "Kekayaan/Elit" sebuah area. Jika area tersebut penuh dengan perkantoran dan supermarket, sistem akan memprediksi mayoritas penduduknya menengah ke atas (Q1 & Q2). Jika areanya standar, sistem akan mengasumsikannya sebagai area perkampungan biasa (Q3 & Q4).
                        </li>
                        <li class="mb-2">
                            <strong>3. Estimasi Lalu Lintas (Traffic Motor):</strong> <br>
                            Volume kendaraan bermotor harian diukur dari tarikan pergerakan orang menuju pusat aktivitas (Sekolah/Pasar/Kantor). <br>
                            Setelah total motor harian ditemukan (misal: 10.000 motor), AI akan memecah motor tersebut ke dalam sesi <strong>Pagi, Siang, dan Sore</strong> sesuai persentase yang Anda tentukan di panel konfigurasi. AI juga pintar membedakan pola jam sibuk antara Hari Kerja (Weekday) vs Akhir Pekan (Weekend).
                        </li>
                        <li class="mb-2">
                            <strong>4. Estimasi Pejalan Kaki:</strong> <br>
                            Pejalan kaki sangat bergantung pada fasilitas lokal. AI mengasumsikannya dari <em>Base Ratio</em> (persentase dari total motor), lalu akan memberikan <strong>Bonus Pejalan Kaki Ekstra</strong> untuk setiap sekolah atau pasar yang ditemukan di peta!
                        </li>
                        <li class="mb-0">
                            <strong>5. Konversi Menjadi Omset & CU (Customer Unit):</strong> <br>
                            Setelah angka audiens terkumpul, sistem tidak menelannya mentah-mentah. Angka tersebut dikalikan dengan <strong>Rasio Ekstrapolasi (Conversion Rate)</strong> yang sangat ketat (misal: hanya 0.75% dari total motor yang dianggap akan mampir beli).<br>
                            <span class="text-secondary">Rumus: (Total Motor × Rasio Motor × Avg Check) + (Total Rumah × Rasio Rumah × Avg Check) - Margin of Error.</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 border rounded bg-white h-100 shadow-sm">
                    <h6 class="fw-bold text-success border-bottom pb-2 mb-3"><i class="bi bi-sliders me-2"></i> Panduan Konfigurasi Parameter (Advanced)</h6>
                    <div style="font-size:13px;" class="text-muted">
                        <p>Jika formula bawaan tidak cocok dengan karakteristik kota Anda, gunakan <strong>Mode Kustomisasi</strong> pada panel di bawah. Berikut adalah panduan fungsinya:</p>
                        
                        <strong>1. Radius Deteksi & Keyword:</strong>
                        <p class="mb-2">Perbesar radius (misal 2000m) untuk daerah pinggiran yang sepi. Pisahkan keyword kompetitor utama (mendapat pin Oranye) dengan kompetitor F&B lokal (mendapat pin Merah) agar lebih rapi.</p>

                        <strong>2. Pembobotan Kategori & Rasio:</strong>
                        <p class="mb-2">Pastikan total Persentase Kategori Utama (Motor, Pejalan Kaki, Rumah, Fasilitas) berjumlah persis <strong>100%</strong>. Rasio ekstrapolasi menentukan porsi *Market Share* target dari total audiens.</p>

                        <strong>3. Algoritma Pembatas Rasionalitas (Realism Cap):</strong>
                        <p class="mb-2">Google Maps sangat agresif dalam radius besar. Batasi total angka motor/rumah maksimal (misal 3500) agar proyeksi perhitungan tidak meledak di luar logika jalanan sekitar.</p>

                        <strong>4. Pengali Heuristik (Multipliers):</strong>
                        <p class="mb-2">Anda memegang kontrol penuh atas rumus! Jika area Anda memiliki mall raksasa, Anda bisa memperbesar nilai pengali "Market" menjadi lebih tinggi dari nilai defaultnya.</p>
                        
                        <strong>5. Nilai Ekstra (Harga Kompetitor):</strong>
                        <p class="mb-2">Jika harga jual di sekitar lebih mahal dari Harga Acuan, Anda bisa memberikan <em>Bonus %</em> karena punya keunggulan harga, atau <em>Penalti %</em> jika sebaliknya.</p>

                        <strong>6. Kenapa Total Pengurang Berbeda dengan Bobot?</strong>
                        <p class="mb-2">Saat Anda mengatur bobot pengurang (misal 5%), itu adalah <strong>Bobot Maksimal (Multiplier)</strong>. Angka "Total Pengurang" (misal 3.5%) di hasil akhir adalah skor <strong>aktual</strong> berdasarkan jumlah kompetitor yang benar-benar ada di lapangan. Sistem tidak memotong 5% mentah-mentah, melainkan menyesuaikan proporsinya agar adil!</p>

                        <strong>7. Faktor Optimisme (Volume Booster):</strong>
                        <p class="mb-2">Fitur khusus untuk simulasi agresif. Pengali ini <strong>mengalikan angka mentah survei</strong> secara harfiah SEBELUM diolah algoritma. Misal Pengali Traffic = 3, maka input 10.000 motor akan diproses seolah ada 30.000 motor. <strong>Dampaknya:</strong> Skor kelayakan akan dengan mudah menyentuh angka maksimal (100%), dan Proyeksi Omset serta CU akan melonjak drastis berlipat ganda karena audiens targetnya dianggap jauh lebih banyak.</p>

                        <strong>8. Kenapa Skor Akhir Tidak Bisa Lebih Dari 100%?</strong>
                        <p class="mb-2">Sistem dilengkapi dengan pelindung <strong>Upper Bound Limit</strong>. Meskipun secara matematis (Total Penambah - Pengurang) lokasi Anda menembus angka 110% atau 150% (akibat multiplier yang agresif), sistem akan menguncinya (<em>capping</em>) secara absolut di angka <strong>100.0%</strong>. Hal ini untuk menjaga integritas standar penilaian kelayakan tertinggi kita.</p>

                        <strong>9. Penentuan Status Kelayakan (Threshold):</strong>
                        <p class="mb-0">Status <em>APPROVED</em>, <em>CONSIDERATION</em>, atau <em>REJECTED</em> ditentukan dari perbandingan Skor Akhir dengan Tipe Outlet. Jika Tipe Outlet adalah <strong>LDP</strong>, batas minimal Approved adalah <strong>60%</strong>. Jika Tipe Outlet adalah <strong>BDP</strong> (daerah baru/jauh), batas minimal Approved dinaikkan jauh lebih ketat yaitu <strong>100%</strong> untuk meminimalisir risiko kegagalan cabang baru.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePanduan() {
    const body = document.getElementById('panduan-body');
    const icon = document.querySelector('#panduan-icon i');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.className = 'bi bi-chevron-up fs-4';
    } else {
        body.style.display = 'none';
        icon.className = 'bi bi-chevron-down fs-4';
    }
}
</script>

<!-- Konfigurasi Bobot & Radius (Advanced) -->
<div class="excel-box mb-3" id="section-konfigurasi">
    <div class="excel-box-header" style="background: linear-gradient(90deg, #1e293b, #334155); color: #fff; cursor: pointer;" onclick="toggleConfig()">
        <div>
            <h5 class="text-white mb-1"><i class="bi bi-gear-fill me-2"></i> Konfigurasi Bobot & Radius (Advanced)</h5>
            <p class="text-light mb-0" style="opacity: 0.8;">Atur radius deteksi Maps API dan kustomisasi rasio perhitungan.</p>
        </div>
        <span id="config-icon" class="text-white"><i class="bi bi-chevron-down fs-4"></i></span>
    </div>
    <div class="excel-box-body" id="config-body" style="display: none;">
        
        <div class="alert alert-info mb-3" style="border-radius: 8px; font-size: 13px;">
            <i class="bi bi-info-circle-fill me-1"></i> Mode <strong>Kustom</strong> akan menimpa standar perhitungan bawaan (Template Default) dengan bobot proporsional sesuai inputan Anda di bawah ini.
        </div>

        <div class="field-grid">
            <div class="field-col-12">
                <label class="field-label">Pilih Mode Formula</label>
                <div class="d-flex gap-4 mt-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="formula_type" id="formulaDefault" value="DEFAULT" {{ (old('formula_type', $score->formula_type ?? 'DEFAULT') == 'DEFAULT') ? 'checked' : '' }} onchange="toggleConfigInputs()">
                        <label class="form-check-label fw-bold" for="formulaDefault">Gunakan Default Template</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="formula_type" id="formulaCustom" value="CUSTOM" {{ (old('formula_type', $score->formula_type ?? 'DEFAULT') == 'CUSTOM') ? 'checked' : '' }} onchange="toggleConfigInputs()">
                        <label class="form-check-label fw-bold text-primary" for="formulaCustom">Gunakan Kustomisasi</label>
                    </div>
                </div>
            </div>
            
            <div class="field-col-12" id="custom-config-wrapper">
                <div class="field-grid p-3 mt-2 border rounded bg-light">
                    <div class="field-col-12 border-bottom pb-1 mb-2">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-bullseye me-2"></i> Radius Deteksi Maps API</span>
                    </div>
                    
                    <div class="field-col-6">
                        <label class="field-label">Radius Fasilitas Umum (m)</label>
                        <input type="number" class="cell-input yellow-cell" name="scan_radius_fasum" id="scan_radius_fasum" value="{{ old('scan_radius_fasum', $score->scan_radius_fasum ?? 750) }}" placeholder="750">
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Radius Kompetitor (m)</label>
                        <input type="number" class="cell-input yellow-cell" name="scan_radius_kompetitor" id="scan_radius_kompetitor" value="{{ old('scan_radius_kompetitor', $score->scan_radius_kompetitor ?? 500) }}" placeholder="500">
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Keywords Kompetitor Utama (Geprek / FC)</label>
                        <input type="text" class="cell-input yellow-cell config-keyword" data-keyword="kompetitor_utama" value="" placeholder="Default: geprek, fried chicken, ayam, kfc, mcd">
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Keywords Kompetitor Lokal (F&B)</label>
                        <input type="text" class="cell-input yellow-cell config-keyword" data-keyword="kompetitor_lokal" value="" placeholder="Default: warteg, bakso, soto, mie ayam, warung">
                    </div>
                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-pie-chart-fill me-2"></i> Pembobotan Kategori Utama (Total Penambah Nilai harus 100)</span>
                    </div>

                    <div class="field-col-2">
                        <label class="field-label">Traffic Motor <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.01" class="cell-input yellow-cell config-weight" data-cat="traffic_motor" value="30" placeholder="30">
                    </div>
                    <div class="field-col-2">
                        <label class="field-label">Traffic Pejalan <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.01" class="cell-input yellow-cell config-weight" data-cat="traffic_pejalan" value="20" placeholder="20">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Jumlah Rumah Penduduk <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.01" class="cell-input yellow-cell config-weight" data-cat="rumah" value="35" placeholder="35">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Fasilitas Umum <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.01" class="cell-input yellow-cell config-weight" data-cat="fasum" value="15" placeholder="15">
                    </div>
                    <div class="field-col-2">
                        <label class="field-label">Pengurang <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.01" class="cell-input yellow-cell config-weight" data-cat="pengurang" value="5" placeholder="5">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-graph-up-arrow me-2"></i> Rasio Ekstrapolasi Omset (Video & Kalkulator)</span>
                    </div>

                    <div class="field-col-3">
                        <label class="field-label">Rasio Motor <small class="text-muted">(Desimal)</small></label>
                        <input type="number" step="0.0001" class="cell-input yellow-cell config-ratio" data-ratio="motor" value="0.0075">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Rasio Pejalan <small class="text-muted">(Desimal)</small></label>
                        <input type="number" step="0.0001" class="cell-input yellow-cell config-ratio" data-ratio="pejalan" value="0.0050">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Rasio Rumah Q1 <small class="text-muted">(Desimal)</small></label>
                        <input type="number" step="0.0001" class="cell-input yellow-cell config-ratio" data-ratio="q1" value="0.0125">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Rasio Rumah Q2 <small class="text-muted">(Desimal)</small></label>
                        <input type="number" step="0.0001" class="cell-input yellow-cell config-ratio" data-ratio="q2" value="0.0075">
                    </div>
                    
                    <div class="field-col-3"></div>
                    <div class="field-col-3"></div>
                    <div class="field-col-3">
                        <label class="field-label">Rasio Rumah Q3 <small class="text-muted">(Desimal)</small></label>
                        <input type="number" step="0.0001" class="cell-input yellow-cell config-ratio" data-ratio="q3" value="0.0050">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Rasio Rumah Q4 <small class="text-muted">(Desimal)</small></label>
                        <input type="number" step="0.0001" class="cell-input yellow-cell config-ratio" data-ratio="q4" value="0.0025">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-building me-2"></i> Bobot Bonus Fisik Bangunan (Max Penambah %)</span>
                    </div>

                    <div class="field-col-4">
                        <label class="field-label">Bonus Akses Jalan <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.5" class="cell-input yellow-cell config-weight" data-cat="bonus_akses" value="5" placeholder="5">
                    </div>
                    <div class="field-col-4">
                        <label class="field-label">Bonus Visibilitas <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.5" class="cell-input yellow-cell config-weight" data-cat="bonus_visibilitas" value="5" placeholder="5">
                    </div>
                    <div class="field-col-4">
                        <label class="field-label">Bonus Parkir/Fasade <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.5" class="cell-input yellow-cell config-weight" data-cat="bonus_parkir" value="5" placeholder="5">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-pie-chart me-2"></i> Rasio Target Kontribusi Omset</span>
                    </div>
                    
                    <div class="field-col-6">
                        <label class="field-label">Rasio Organik (Dine-in / Takeaway) <small class="text-muted">(%)</small></label>
                        <input type="number" step="1" class="cell-input yellow-cell config-target" data-target="rasio_organik" id="config_rasio_organik" value="85" placeholder="85">
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Rasio Online (Ojol) <small class="text-muted">(%)</small></label>
                        <input type="number" step="1" class="cell-input yellow-cell config-target" data-target="rasio_online" id="config_rasio_online" value="15" placeholder="15" readonly style="background-color: #f1f5f9;">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-shield-check me-2"></i> Algoritma Pembatas Rasionalitas (Realism Cap) AI Radar</span>
                    </div>
                    
                    <div class="field-col-6">
                        <label class="field-label">Cap Maksimal Total Motor (Base)</label>
                        <input type="number" class="cell-input yellow-cell config-cap" data-cap="max_motor" value="3500" placeholder="3500">
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Cap Maksimal Total Rumah</label>
                        <input type="number" class="cell-input yellow-cell config-cap" data-cap="max_rumah" value="4000" placeholder="4000">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-cpu-fill me-2"></i> Algoritma Heuristik AI Radar (Multipliers / Pengali)</span>
                    </div>
                    
                    <div class="field-col-12 mb-2">
                        <span class="fw-bold text-secondary" style="font-size:13px;">Pengali Estimasi Rumah</span>
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Base Rumah</label>
                        <input type="number" class="cell-input yellow-cell config-heuristic" data-heuristic="rumah_base" value="600" placeholder="600">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Pengali Market</label>
                        <input type="number" class="cell-input yellow-cell config-heuristic" data-heuristic="rumah_market" value="200" placeholder="200">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Pengali Sekolah</label>
                        <input type="number" class="cell-input yellow-cell config-heuristic" data-heuristic="rumah_sekolah" value="150" placeholder="150">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Pengali Perkantoran</label>
                        <input type="number" class="cell-input yellow-cell config-heuristic" data-heuristic="rumah_perkantoran" value="100" placeholder="100">
                    </div>

                    <div class="field-col-12 mb-2 mt-2">
                        <span class="fw-bold text-secondary" style="font-size:13px;">Pengali Estimasi Traffic Motor</span>
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Base Motor</label>
                        <input type="number" class="cell-input yellow-cell config-heuristic" data-heuristic="motor_base" value="500" placeholder="500">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Pengali Market</label>
                        <input type="number" class="cell-input yellow-cell config-heuristic" data-heuristic="motor_market" value="300" placeholder="300">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Pengali Sekolah</label>
                        <input type="number" class="cell-input yellow-cell config-heuristic" data-heuristic="motor_sekolah" value="200" placeholder="200">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Pengali Perkantoran</label>
                        <input type="number" class="cell-input yellow-cell config-heuristic" data-heuristic="motor_perkantoran" value="250" placeholder="250">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-clock-history me-2"></i> Distribusi Persentase Jam Ramai (Traffic Motor)</span>
                    </div>

                    <div class="field-col-12 mb-2">
                        <span class="fw-bold text-secondary" style="font-size:13px;">Persentase Weekday (Hari Kerja)</span>
                    </div>
                    <div class="field-col-4">
                        <label class="field-label">Pagi <small class="text-muted">(%)</small></label>
                        <input type="number" step="1" class="cell-input yellow-cell config-traffic" data-traffic="wd_pagi" value="40" placeholder="40">
                    </div>
                    <div class="field-col-4">
                        <label class="field-label">Siang <small class="text-muted">(%)</small></label>
                        <input type="number" step="1" class="cell-input yellow-cell config-traffic" data-traffic="wd_siang" value="20" placeholder="20">
                    </div>
                    <div class="field-col-4">
                        <label class="field-label">Sore <small class="text-muted">(%)</small></label>
                        <input type="number" step="1" class="cell-input yellow-cell config-traffic" data-traffic="wd_sore" value="40" placeholder="40">
                    </div>

                    <div class="field-col-12 mb-2 mt-2">
                        <span class="fw-bold text-secondary" style="font-size:13px;">Persentase Weekend (Akhir Pekan)</span>
                    </div>
                    <div class="field-col-4">
                        <label class="field-label">Pagi <small class="text-muted">(%)</small></label>
                        <input type="number" step="1" class="cell-input yellow-cell config-traffic" data-traffic="we_pagi" value="15" placeholder="15">
                    </div>
                    <div class="field-col-4">
                        <label class="field-label">Siang <small class="text-muted">(%)</small></label>
                        <input type="number" step="1" class="cell-input yellow-cell config-traffic" data-traffic="we_siang" value="35" placeholder="35">
                    </div>
                    <div class="field-col-4">
                        <label class="field-label">Sore <small class="text-muted">(%)</small></label>
                        <input type="number" step="1" class="cell-input yellow-cell config-traffic" data-traffic="we_sore" value="50" placeholder="50">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-person-walking me-2"></i> Algoritma Pejalan Kaki (Pedestrian Ratio)</span>
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Base Ratio <small class="text-muted">(Desimal)</small></label>
                        <input type="number" step="0.01" class="cell-input yellow-cell config-pedestrian" data-pedestrian="base" value="0.05" placeholder="0.05">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Pengali Sekolah</label>
                        <input type="number" step="0.001" class="cell-input yellow-cell config-pedestrian" data-pedestrian="sekolah" value="0.01" placeholder="0.01">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Pengali Market</label>
                        <input type="number" step="0.001" class="cell-input yellow-cell config-pedestrian" data-pedestrian="market" value="0.005" placeholder="0.005">
                    </div>
                    <div class="field-col-3">
                        <label class="field-label">Batas Maks (Cap)</label>
                        <input type="number" step="0.01" class="cell-input yellow-cell config-pedestrian" data-pedestrian="max_cap" value="0.15" placeholder="0.15">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-gem me-2"></i> Skala Deteksi Kelas Ekonomi Area (Elit/Menengah)</span>
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Skor Minimal Area Elit / Komersial</label>
                        <input type="number" step="0.1" class="cell-input yellow-cell config-elit" data-elit="threshold_elit" value="20" placeholder="20">
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Skor Minimal Area Menengah</label>
                        <input type="number" step="0.1" class="cell-input yellow-cell config-elit" data-elit="threshold_menengah" value="10" placeholder="10">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-tag-fill me-2"></i> Nilai Ekstra (Harga Kompetitor)</span>
                    </div>
                    
                    <div class="field-col-6">
                        <label class="field-label">Harga Acuan Dasar (Rp)</label>
                        <input type="number" class="cell-input yellow-cell config-extra" data-extra="harga_acuan" value="10000" placeholder="10000">
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Bonus / Penalti <small class="text-muted">(%)</small></label>
                        <input type="number" step="0.1" class="cell-input yellow-cell config-extra" data-extra="bonus_persen" value="10" placeholder="10">
                    </div>

                    <div class="field-col-12 border-bottom pb-1 mb-2 mt-3">
                        <span class="fw-bold text-primary" style="font-size:14px;"><i class="bi bi-rocket-takeoff-fill me-2"></i> Faktor Optimisme (Multiplier Omset)</span>
                    </div>
                    <div class="field-col-12 mb-1">
                        <span class="text-muted" style="font-size:12px;">Gunakan ini secara legal jika Anda ingin mendongkrak proyeksi pendapatan (Misal: 4.0 untuk menyamakan dengan perhitungan agresif di spreadsheet lama).</span>
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Pengali Omset Rumah (x)</label>
                        <input type="number" step="0.1" class="cell-input yellow-cell config-multiplier" data-multiplier="rumah" value="1.0" placeholder="1.0">
                    </div>
                    <div class="field-col-6">
                        <label class="field-label">Pengali Omset Traffic (x)</label>
                        <input type="number" step="0.1" class="cell-input yellow-cell config-multiplier" data-multiplier="traffic" value="1.0" placeholder="1.0">
                    </div>
                </div>
            </div>

            <!-- Hidden input to store JSON -->
            <input type="hidden" name="custom_weights_json" id="custom_weights_json" value="{{ old('custom_weights_json', $score->custom_weights_json ?? '{}') }}">
        </div>

    </div>
</div>

<script>
function toggleConfig() {
    const body = document.getElementById('config-body');
    const icon = document.querySelector('#config-icon i');
    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.className = 'bi bi-chevron-up fs-4';
    } else {
        body.style.display = 'none';
        icon.className = 'bi bi-chevron-down fs-4';
    }
}

function toggleConfigInputs() {
    const isCustom = document.getElementById('formulaCustom').checked;
    const wrapper = document.getElementById('custom-config-wrapper');
    const inputs = wrapper.querySelectorAll('input:not([type="hidden"]):not(#scan_radius_fasum):not(#scan_radius_kompetitor)');
    
    inputs.forEach(input => {
        input.disabled = !isCustom;
        if(!isCustom) input.style.opacity = '0.6';
        else input.style.opacity = '1';
    });
    
    updateCustomJson();
}

function updateCustomJson() {
    const json = {
        weights: {},
        ratios: {},
        keywords: {},
        caps: {},
        extras: {},
        heuristics: {},
        traffic: {},
        pedestrian: {},
        elit: {},
        multipliers: {}
    };
    
    document.querySelectorAll('.config-weight').forEach(el => {
        json.weights[el.dataset.cat] = parseFloat(el.value) || 0;
    });
    
    document.querySelectorAll('.config-ratio').forEach(el => {
        json.ratios[el.dataset.ratio] = parseFloat(el.value) || 0;
    });

    document.querySelectorAll('.config-keyword').forEach(el => {
        json.keywords[el.dataset.keyword] = el.value.trim();
    });

    document.querySelectorAll('.config-cap').forEach(el => {
        json.caps[el.dataset.cap] = parseFloat(el.value) || 0;
    });

    document.querySelectorAll('.config-extra').forEach(el => {
        json.extras[el.dataset.extra] = parseFloat(el.value) || 0;
    });

    document.querySelectorAll('.config-heuristic').forEach(el => {
        json.heuristics[el.dataset.heuristic] = parseFloat(el.value) || 0;
    });

    document.querySelectorAll('.config-traffic').forEach(el => {
        json.traffic[el.dataset.traffic] = parseFloat(el.value) || 0;
    });

    document.querySelectorAll('.config-pedestrian').forEach(el => {
        json.pedestrian[el.dataset.pedestrian] = parseFloat(el.value) || 0;
    });

    document.querySelectorAll('.config-elit').forEach(el => {
        json.elit[el.dataset.elit] = parseFloat(el.value) || 0;
    });

    document.querySelectorAll('.config-multiplier').forEach(el => {
        json.multipliers[el.dataset.multiplier] = parseFloat(el.value) || 1.0;
    });
    
    document.getElementById('custom_weights_json').value = JSON.stringify(json);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    toggleConfigInputs();
    
    // Bind change events
    document.querySelectorAll('.config-weight, .config-ratio, .config-keyword, .config-cap, .config-extra, .config-heuristic, .config-traffic, .config-pedestrian, .config-elit, .config-multiplier').forEach(el => {
        el.addEventListener('input', () => {
            updateCustomJson();
            if (typeof hitungLiveScore === 'function') hitungLiveScore();
        });
    });
    
    // Load existing JSON if available
    const existingJsonStr = document.getElementById('custom_weights_json').value;
    if (existingJsonStr && existingJsonStr !== '{}') {
        try {
            const parsed = JSON.parse(existingJsonStr);
            if (parsed.weights) {
                document.querySelectorAll('.config-weight').forEach(el => {
                    if (parsed.weights[el.dataset.cat] !== undefined) el.value = parsed.weights[el.dataset.cat];
                });
            }
            if (parsed.ratios) {
                document.querySelectorAll('.config-ratio').forEach(el => {
                    if (parsed.ratios[el.dataset.ratio] !== undefined) el.value = parsed.ratios[el.dataset.ratio];
                });
            }
            if (parsed.keywords) {
                document.querySelectorAll('.config-keyword').forEach(el => {
                    if (parsed.keywords[el.dataset.keyword] !== undefined) el.value = parsed.keywords[el.dataset.keyword];
                });
            }
            if (parsed.caps) {
                document.querySelectorAll('.config-cap').forEach(el => {
                    if (parsed.caps[el.dataset.cap] !== undefined) el.value = parsed.caps[el.dataset.cap];
                });
            }
            if (parsed.extras) {
                document.querySelectorAll('.config-extra').forEach(el => {
                    if (parsed.extras[el.dataset.extra] !== undefined) el.value = parsed.extras[el.dataset.extra];
                });
            }
            if (parsed.heuristics) {
                document.querySelectorAll('.config-heuristic').forEach(el => {
                    if (parsed.heuristics[el.dataset.heuristic] !== undefined) el.value = parsed.heuristics[el.dataset.heuristic];
                });
            }
            if (parsed.traffic) {
                document.querySelectorAll('.config-traffic').forEach(el => {
                    if (parsed.traffic[el.dataset.traffic] !== undefined) el.value = parsed.traffic[el.dataset.traffic];
                });
            }
            if (parsed.pedestrian) {
                document.querySelectorAll('.config-pedestrian').forEach(el => {
                    if (parsed.pedestrian[el.dataset.pedestrian] !== undefined) el.value = parsed.pedestrian[el.dataset.pedestrian];
                });
            }
            if (parsed.elit) {
                document.querySelectorAll('.config-elit').forEach(el => {
                    if (parsed.elit[el.dataset.elit] !== undefined) el.value = parsed.elit[el.dataset.elit];
                });
            }
            if (parsed.multipliers) {
                document.querySelectorAll('.config-multiplier').forEach(el => {
                    if (parsed.multipliers[el.dataset.multiplier] !== undefined) el.value = parsed.multipliers[el.dataset.multiplier];
                });
            }
        } catch(e) { console.error('Error parsing custom config', e); }
    }
});
</script>

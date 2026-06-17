{{-- resources/views/Guidebook/guidebookOutlet.blade.php --}}
@extends('Purchasing.Guidebook.layout')

@section('title', 'Panduan Pengguna — Outlet')
@section('role-label', 'Untuk Outlet')
@section('role-badge-class', 'bg-primary text-white')

{{-- ── SIDEBAR ── --}}
@section('sidebar')
    <div class="sidebar-section-label">Mulai dari sini</div>
    <a class="sidebar-link active" data-section="pengantar" onclick="scrollTo('pengantar')">
        <span class="icon"><i class="bi bi-house"></i></span> Pengantar
    </a>

    <a class="sidebar-link" data-section="alur-po" onclick="scrollTo('alur-po')">
        <span class="icon"><i class="bi bi-diagram-3"></i></span> Alur Pemesanan Barang
    </a>

    <div class="sidebar-section-label">Purchase Order</div>

    <a class="sidebar-link" data-section="buat-po" onclick="scrollTo('buat-po')">
        <span class="icon"><i class="bi bi-file-plus"></i></span> Membuat PO
    </a>

    <a class="sidebar-link" data-section="status-po" onclick="scrollTo('status-po')">
        <span class="icon"><i class="bi bi-list-check"></i></span> Status PO
    </a>

    <a class="sidebar-link" data-section="detail-po" onclick="scrollTo('detail-po')">
        <span class="icon"><i class="bi bi-eye"></i></span> Detail PO
    </a>

    <a class="sidebar-link" data-section="ubah-po" onclick="scrollTo('ubah-po')">
        <span class="icon"><i class="bi bi-pencil-square"></i></span> Ubah / Batalkan PO
    </a>

    <a class="sidebar-link" data-section="terima-barang" onclick="scrollTo('terima-barang')">
        <span class="icon"><i class="bi bi-box-seam"></i></span> Penerimaan Barang
    </a>

    <a class="sidebar-link" data-section="barang-bermasalah" onclick="scrollTo('barang-bermasalah')">
        <span class="icon"><i class="bi bi-exclamation-triangle"></i></span> Barang Kurang / Rusak
    </a>

    <div class="sidebar-section-label">Bantuan</div>

    <a class="sidebar-link" data-section="faq" onclick="scrollTo('faq')">
        <span class="icon"><i class="bi bi-question-circle"></i></span> FAQ
    </a>

    <a class="sidebar-link" data-section="kontak" onclick="scrollTo('kontak')">
        <span class="icon"><i class="bi bi-headset"></i></span> Kontak Support
    </a>
@endsection

{{-- ── CONTENT ── --}}
@section('content')

    {{-- PENGANTAR --}}
    <div class="gb-section" id="pengantar">
        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#eeedfe;color:#696cff;">
                <i class="bi bi-house-heart"></i>
            </div>
            <h2 class="gb-section-title">Selamat Datang di SCM System</h2>
        </div>
        <p class="gb-section-desc">
            Panduan ini ditujukan untuk pengguna <strong>Outlet / Operasional</strong>.
            Ikuti panduan step-by-step di setiap bagian untuk menggunakan fitur dengan benar.
        </p>
        <div class="info-box">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>Untuk siapa panduan ini?</strong><br>
                Kasir, kepala outlet, atau staf operasional yang bertugas membuat permintaan barang,
                menerima pengiriman, dan mencatat transaksi harian di outlet.
            </div>
        </div>
        <div class="warn-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Akun outlet sudah terkunci ke 1 outlet.</strong>
                Kamu tidak perlu memilih outlet saat membuat PO — sistem otomatis mengisi sesuai akunmu.
            </div>
        </div>
    </div>

    {{-- ALUR UMUM --}}
    <div class="gb-section" id="alur-po">
        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#e1f5ee;color:#0f6e56;">
                <i class="bi bi-diagram-3"></i>
            </div>
            <h2 class="gb-section-title">Alur Kerja Outlet</h2>
        </div>
        <p class="gb-section-desc">Ini adalah urutan proses dari permintaan barang sampai barang diterima.</p>

        <div class="flow-inline">
            <span class="flow-node" style="background:#eeedfe;color:#3c3489;">1. Buat PO</span>
            <span class="flow-arrow">›</span>
            <span class="flow-node" style="background:#e6f1fb;color:#0c447c;">2. Tunggu Approve</span>
            <span class="flow-arrow">›</span>
            <span class="flow-node" style="background:#faeeda;color:#633806;">3. Barang Dikirim</span>
            <span class="flow-arrow">›</span>
            <span class="flow-node" style="background:#e1f5ee;color:#085041;">4. Terima & Konfirmasi</span>
            <span class="flow-arrow">›</span>
            <span class="flow-node" style="background:#eaf3de;color:#27500a;">5. Selesai</span>
        </div>

        <table class="gb-table mt-3">
            <thead>
                <tr>
                    <th>Tahap</th>
                    <th>Yang Outlet Lakukan</th>
                    <th>Status PO</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>1. Buat PO</strong></td>
                    <td>Isi form permintaan barang, pilih bahan dan jumlah</td>
                    <td><span class="badge-inline bi-amber">Waiting</span></td>
                </tr>
                <tr>
                    <td><strong>2. Approve</strong></td>
                    <td>Tunggu SCM memverifikasi — kamu akan notif jika disetujui</td>
                    <td><span class="badge-inline bi-blue">Approved</span></td>
                </tr>
                <tr>
                    <td><strong>3. Pengiriman</strong></td>
                    <td>SCM memproses pengiriman, surat jalan diterbitkan</td>
                    <td><span class="badge-inline bi-purple">In Transit</span></td>
                </tr>
                <tr>
                    <td><strong>4. Penerimaan</strong></td>
                    <td>Cek barang, cocokkan dengan surat jalan, konfirmasi di sistem</td>
                    <td><span class="badge-inline bi-green">Delivered</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- BUAT PO --}}
    <div class="gb-section" id="buat-po">
        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#eeedfe;color:#696cff;">
                <i class="bi bi-file-plus"></i>
            </div>
            <h2 class="gb-section-title">Membuat Purchase Order (PO)</h2>
        </div>
        <p class="gb-section-desc">
            PO adalah permintaan barang dari outlet ke gudang SCM. Buat PO setiap kali stok bahan mendekati habis.
        </p>

        <div class="step-list">
            <div class="step-item">
                <div class="step-num">1</div>
                <div class="step-body">
                    <div class="step-title">Buka menu Purchase Request</div>
                    <div class="step-desc">Di sidebar kiri, klik <strong>Purchasing → Purchase Request</strong>. Halaman
                        daftar PO akan terbuka.</div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <div class="step-body">
                    <div class="step-title">Klik tombol "+ Buat Request PO"</div>
                    <div class="step-desc">Tombol ada di pojok kanan atas tabel. Modal form akan muncul.</div>
                    <div class="screenshot-box">
                        <i class="bi bi-image"></i>
                        [Screenshot: Tombol "+ Buat Request PO" di pojok kanan atas]
                    </div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <div class="step-body">
                    <div class="step-title">Isi informasi dasar</div>
                    <div class="step-desc">
                        <ul style="padding-left:16px;margin:6px 0;">
                            <li><strong>Outlet</strong> — otomatis terisi sesuai akunmu, tidak bisa diubah</li>
                            <li><strong>Tanggal Permintaan</strong> — isi tanggal hari ini atau tanggal kebutuhan</li>
                            <li><strong>Catatan</strong> — opsional, isi jika ada informasi tambahan untuk SCM</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">4</div>
                <div class="step-body">
                    <div class="step-title">Tambahkan bahan yang diminta</div>
                    <div class="step-desc">
                        Di bagian <strong>Daftar Barang</strong>, klik <strong>"+ Tambah Barang"</strong>.
                        Gunakan kolom pencarian untuk mencari nama bahan, lalu isi jumlah yang dibutuhkan.
                        Ulangi untuk setiap bahan.
                    </div>
                    <div class="screenshot-box">
                        <i class="bi bi-image"></i>
                        [Screenshot: Form daftar barang dengan dropdown search bahan]
                    </div>
                    <div class="tip-box">
                        <i class="bi bi-lightbulb-fill"></i>
                        <div>
                            <strong>Tips:</strong> Ketik minimal 2 huruf di kolom pencarian bahan untuk hasil yang lebih
                            akurat. Satuan akan otomatis terisi setelah bahan dipilih.
                        </div>
                    </div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">5</div>
                <div class="step-body">
                    <div class="step-title">Klik "Simpan Request"</div>
                    <div class="step-desc">
                        Setelah semua bahan ditambahkan, klik tombol <strong>Simpan Request</strong>.
                        PO akan tersimpan dengan status <span class="badge-inline bi-amber">Waiting</span> dan dikirim ke
                        SCM untuk di-review.
                    </div>
                </div>
            </div>
        </div>

        <hr class="gb-divider">

        <div class="warn-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Perhatian:</strong> PO yang sudah dikirim <strong>tidak bisa diedit</strong>.
                Jika ada kesalahan, hubungi SCM untuk pembatalan dan buat PO baru.
            </div>
        </div>
    </div>

    {{-- STATUS PO--}}
    <div class="gb-section" id="status-po">

        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#e6f1fb;color:#0c447c;">
                <i class="bi bi-list-check"></i>
            </div>
            <h2 class="gb-section-title">Memahami Status Purchase Order</h2>
        </div>

        <p class="gb-section-desc">
            Setiap Purchase Order memiliki status yang menunjukkan proses yang sedang berjalan.
        </p>

        <table class="gb-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Arti</th>
                    <th>Tindakan Outlet</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge-inline bi-amber">Waiting</span></td>
                    <td>PO berhasil dibuat dan sedang menunggu review SCM.</td>
                    <td>Tunggu proses review.</td>
                </tr>

                <tr>
                    <td><span class="badge-inline bi-blue">Approved</span></td>
                    <td>PO disetujui dan sedang dipersiapkan oleh SCM.</td>
                    <td>Tunggu proses pengiriman.</td>
                </tr>

                <tr>
                    <td><span class="badge-inline bi-purple">In Transit</span></td>
                    <td>Barang sedang dalam perjalanan menuju outlet.</td>
                    <td>Siapkan proses penerimaan barang.</td>
                </tr>

                <tr>
                    <td><span class="badge-inline bi-green">Delivered</span></td>
                    <td>Barang sudah diterima dan PO selesai.</td>
                    <td>Tidak ada tindakan.</td>
                </tr>

                <tr>
                    <td><span class="badge-inline bi-red">Rejected</span></td>
                    <td>PO ditolak oleh SCM.</td>
                    <td>Lihat alasan penolakan dan buat PO baru jika diperlukan.</td>
                </tr>
            </tbody>
        </table>

    </div>

    {{-- PEMBATALAN PO --}}
    <div class="gb-section" id="ubah-po">

        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#faeeda;color:#633806;">
                <i class="bi bi-pencil-square"></i>
            </div>
            <h2 class="gb-section-title">Mengubah atau Membatalkan PO</h2>
        </div>

        <div class="warn-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                Setelah PO dikirim ke SCM, data tidak dapat diubah secara langsung oleh outlet.
            </div>
        </div>

        <div class="step-list mt-3">

            <div class="step-item">
                <div class="step-num">1</div>
                <div class="step-body">
                    <div class="step-title">Jika salah jumlah atau salah barang</div>
                    <div class="step-desc">
                        Segera hubungi tim SCM dan informasikan nomor PO yang bermasalah.
                    </div>
                </div>
            </div>

            <div class="step-item">
                <div class="step-num">2</div>
                <div class="step-body">
                    <div class="step-title">Jika PO masih bisa dibatalkan</div>
                    <div class="step-desc">
                        SCM akan memberikan arahan apakah PO perlu dibatalkan atau dibuat ulang.
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- TERIMA BARANG --}}
    <div class="gb-section" id="terima-barang">
        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#e1f5ee;color:#0f6e56;">
                <i class="bi bi-box-seam"></i>
            </div>
            <h2 class="gb-section-title">Penerimaan Barang dari Pengiriman</h2>
        </div>
        <p class="gb-section-desc">
            Saat barang tiba dari SCM, lakukan pengecekan fisik dan konfirmasi penerimaan di sistem.
        </p>

        <div class="danger-box">
            <i class="bi bi-shield-exclamation"></i>
            <div>
                <strong>Penting!</strong> Jangan konfirmasi penerimaan sebelum barang benar-benar dicek secara fisik.
                Konfirmasi yang salah akan mempengaruhi data stok gudang.
            </div>
        </div>

        <div class="step-list mt-3">
            <div class="step-item">
                <div class="step-num">1</div>
                <div class="step-body">
                    <div class="step-title">Cek barang secara fisik</div>
                    <div class="step-desc">
                        Saat barang datang, periksa:
                        <ul style="padding-left:16px;margin:6px 0;">
                            <li>Jumlah sesuai dengan surat jalan yang dibawa driver</li>
                            <li>Kondisi barang tidak rusak atau bocor</li>
                            <li>Tanggal kadaluarsa masih jauh</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <div class="step-body">
                    <div class="step-title">Buka PO yang statusnya "In Transit"</div>
                    <div class="step-desc">Di menu Purchase Request, cari PO dengan status <span
                            class="badge-inline bi-purple">In Transit</span>, lalu klik Detail.</div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <div class="step-body">
                    <div class="step-title">Konfirmasi penerimaan</div>
                    <div class="step-desc">
                        Jika barang sudah sesuai, klik tombol <strong>"Konfirmasi Terima"</strong>.
                        Status PO akan berubah menjadi <span class="badge-inline bi-green">Delivered</span>.
                    </div>
                    <div class="screenshot-box">
                        <i class="bi bi-image"></i>
                        [Screenshot: Tombol "Konfirmasi Terima" di halaman detail PO]
                    </div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">4</div>
                <div class="step-body">
                    <div class="step-title">Jika ada kekurangan atau kerusakan</div>
                    <div class="step-desc">
                        Jangan langsung konfirmasi. Catat di kolom keterangan berapa yang diterima dan apa masalahnya, lalu
                        hubungi SCM melalui kontak support.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BARANG RUSAK --}}
    <div class="gb-section" id="barang-bermasalah">

    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#fcebeb;color:#a32d2d;">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h2 class="gb-section-title">Barang Kurang atau Rusak</h2>
    </div>

    <div class="danger-box">
        <i class="bi bi-shield-exclamation"></i>
        <div>
            Jangan melakukan konfirmasi penerimaan sebelum masalah dicatat dan dilaporkan.
        </div>
    </div>

    <div class="step-list mt-3">

        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Periksa seluruh barang</div>
                <div class="step-desc">
                    Cocokkan jumlah barang dengan surat jalan dan detail PO.
                </div>
            </div>
        </div>

        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Ambil foto bukti</div>
                <div class="step-desc">
                    Foto barang yang rusak, bocor, atau jumlah yang tidak sesuai.
                </div>
            </div>
        </div>

        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Hubungi SCM</div>
                <div class="step-desc">
                    Sertakan nomor PO dan bukti foto agar proses tindak lanjut lebih cepat.
                </div>
            </div>
        </div>

    </div>

</div>
    {{-- CEK STOK --}}
    <div class="gb-section" id="cek-stok">
        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#e6f1fb;color:#0c447c;">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <h2 class="gb-section-title">Cek Stok Bahan</h2>
        </div>
        <p class="gb-section-desc">Pantau ketersediaan stok bahan di outletmu.</p>

        <div class="step-list">
            <div class="step-item">
                <div class="step-num">1</div>
                <div class="step-body">
                    <div class="step-title">Buka menu Stock Control / Monitoring</div>
                    <div class="step-desc">Navigasi ke <strong>Purchasing → Stock Control</strong>.</div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">2</div>
                <div class="step-body">
                    <div class="step-title">Perhatikan indikator warna</div>
                    <div class="step-desc">
                        <ul style="padding-left:16px;margin:6px 0;line-height:2">
                            <li>Angka <span style="color:#a32d2d;font-weight:700;">merah / 0</span> — stok habis, segera
                                buat PO</li>
                            <li>Angka <span style="color:#854f0b;font-weight:700;">kuning</span> — stok menipis, rencanakan
                                PO segera</li>
                            <li>Angka hitam biasa — stok aman</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">3</div>
                <div class="step-body">
                    <div class="step-title">Gunakan filter pencarian</div>
                    <div class="step-desc">Gunakan kolom search di atas tabel untuk mencari bahan tertentu dengan cepat.
                    </div>
                </div>
            </div>
        </div>

        <div class="tip-box">
            <i class="bi bi-lightbulb-fill"></i>
            <div>
                <strong>Kebiasaan baik:</strong> Cek stok minimal 1x sehari di awal shift.
                Buat PO jika ada bahan yang mendekati stok minimal sebelum habis.
            </div>
        </div>
    </div>

    {{-- TROUBLESHOOTING OUTLET --}}
    <div class="gb-section" id="troubleshoot-outlet">
        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#fcebeb;color:#a32d2d;">
                <i class="bi bi-tools"></i>
            </div>
            <h2 class="gb-section-title">Troubleshooting</h2>
        </div>
        <p class="gb-section-desc">Masalah umum yang sering ditemui dan cara mengatasinya.</p>

        <div class="trouble-item">
            <div class="trouble-q">
                <i class="bi bi-x-circle-fill trouble-icon"></i>
                Saat buat PO, dropdown bahan tidak bisa dicari / kosong
                <i class="bi bi-chevron-down chev"></i>
            </div>
            <div class="trouble-a">
                Coba refresh halaman (Ctrl+R). Jika masih kosong, pastikan koneksi internet stabil dan coba gunakan browser
                Chrome atau Edge versi terbaru. Jika masih bermasalah, hubungi tim IT.
            </div>
        </div>

        <div class="trouble-item">
            <div class="trouble-q">
                <i class="bi bi-x-circle-fill trouble-icon"></i>
                PO sudah dibuat tapi tidak muncul di daftar
                <i class="bi bi-chevron-down chev"></i>
            </div>
            <div class="trouble-a">
                Klik tombol Refresh / reload halaman. Pastikan filter tanggal tidak membatasi tampilan. Jika PO tetap tidak
                muncul setelah 5 menit, kemungkinan gagal tersimpan — coba buat ulang dan hubungi SCM.
            </div>
        </div>

        <div class="trouble-item">
            <div class="trouble-q">
                <i class="bi bi-x-circle-fill trouble-icon"></i>
                Tidak bisa konfirmasi penerimaan barang
                <i class="bi bi-chevron-down chev"></i>
            </div>
            <div class="trouble-a">
                Pastikan status PO sudah <span class="badge-inline bi-purple">In Transit</span>. Tombol konfirmasi hanya
                muncul saat status tersebut. Jika statusnya masih Approved padahal barang sudah datang, hubungi SCM untuk
                update status.
            </div>
        </div>

        <div class="trouble-item">
            <div class="trouble-q">
                <i class="bi bi-x-circle-fill trouble-icon"></i>
                Tombol "Tambah Barang" di Simple Sales tidak merespons
                <i class="bi bi-chevron-down chev"></i>
            </div>
            <div class="trouble-a">
                Pastikan JavaScript aktif di browser. Coba hard refresh dengan Ctrl+Shift+R. Jika masih tidak bisa, coba
                buka di browser lain.
            </div>
        </div>

        <div class="trouble-item">
            <div class="trouble-q">
                <i class="bi bi-x-circle-fill trouble-icon"></i>
                PO ditolak (Rejected) tanpa tahu alasannya
                <i class="bi bi-chevron-down chev"></i>
            </div>
            <div class="trouble-a">
                Klik tombol Detail di PO tersebut. Biasanya alasan penolakan tercatat di kolom Catatan / Notes. Jika tidak
                ada keterangan, hubungi SCM langsung.
            </div>
        </div>
    </div>

    {{-- KONTAK --}}
    <div class="gb-section" id="kontak">
        <div class="gb-section-header">
            <div class="gb-section-icon" style="background:#e1f5ee;color:#0f6e56;">
                <i class="bi bi-headset"></i>
            </div>
            <h2 class="gb-section-title">Kontak Support</h2>
        </div>
        <p class="gb-section-desc">Butuh bantuan lebih lanjut? Hubungi tim SCM atau IT.</p>

        <table class="gb-table">
            <thead>
                <tr>
                    <th>Masalah</th>
                    <th>Hubungi</th>
                    <th>Cara</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PO tidak di-approve, status barang salah</td>
                    <td><strong>Tim SCM</strong></td>
                    <td>WhatsApp / telpon langsung ke DC</td>
                </tr>
                <tr>
                    <td>Barang kurang / rusak saat terima</td>
                    <td><strong>Tim SCM</strong></td>
                    <td>Foto bukti → kirim ke grup WA outlet</td>
                </tr>
                <tr>
                    <td>Masalah teknis sistem / login</td>
                    <td><strong>Tim IT</strong></td>
                    <td>Email ke it@company.com atau ext. 100</td>
                </tr>
                <tr>
                    <td>Pertanyaan tentang fitur</td>
                    <td><strong>Guidebook ini</strong></td>
                    <td>Gunakan sidebar untuk navigasi topik</td>
                </tr>
            </tbody>
        </table>

        <div class="tip-box mt-3">
            <i class="bi bi-lightbulb-fill"></i>
            <div>
                Saat melaporkan masalah, selalu sertakan: <strong>nama outlet, nomor PO/transaksi, dan screenshot
                    error</strong> agar tim dapat membantu lebih cepat.
            </div>
        </div>
    </div>

@endsection
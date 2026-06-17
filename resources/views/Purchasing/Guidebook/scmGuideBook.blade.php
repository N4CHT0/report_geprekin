{{-- resources/views/Guidebook/guidebookSCM.blade.php --}}
@extends('Purchasing.Guidebook.layout')

@section('title', 'Panduan Pengguna — SCM')
@section('role-label', 'Untuk Tim SCM')
@section('role-badge-class', 'bg-primary text-white')

{{-- ── SIDEBAR ── --}}
@section('sidebar')
<div class="sidebar-section-label">Mulai dari sini</div>
<a class="sidebar-link active" data-section="pengantar-scm" onclick="scrollTo('pengantar-scm')">
    <span class="icon"><i class="bi bi-house"></i></span> Pengantar
</a>
<a class="sidebar-link" data-section="alur-scm" onclick="scrollTo('alur-scm')">
    <span class="icon"><i class="bi bi-diagram-3"></i></span> Alur Kerja SCM
</a>

<div class="sidebar-section-label">Manajemen PO</div>
<a class="sidebar-link" data-section="review-po" onclick="scrollTo('review-po')">
    <span class="icon"><i class="bi bi-check2-square"></i></span> Review & Approve PO
</a>
<a class="sidebar-link" data-section="finalisasi-pengiriman" onclick="scrollTo('finalisasi-pengiriman')">
    <span class="icon"><i class="bi bi-truck"></i></span> Finalisasi Pengiriman
</a>

<div class="sidebar-section-label">Goods & Invoice</div>
<a class="sidebar-link" data-section="goods-receipt" onclick="scrollTo('goods-receipt')">
    <span class="icon"><i class="bi bi-box-arrow-in-down"></i></span> Goods Receipt (GR)
</a>
<a class="sidebar-link" data-section="goods-delivery" onclick="scrollTo('goods-delivery')">
    <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span> Goods Delivery (GD)
</a>
<a class="sidebar-link" data-section="purchase-invoice" onclick="scrollTo('purchase-invoice')">
    <span class="icon"><i class="bi bi-receipt"></i></span> Purchase Invoice
</a>
<a class="sidebar-link" data-section="sales-invoice" onclick="scrollTo('sales-invoice')">
    <span class="icon"><i class="bi bi-file-earmark-text"></i></span> Sales Invoice
</a>

<div class="sidebar-section-label">Stok & Opname</div>
<a class="sidebar-link" data-section="stock-control" onclick="scrollTo('stock-control')">
    <span class="icon"><i class="bi bi-clipboard-data"></i></span> Stock Control
</a>
<a class="sidebar-link" data-section="stock-adjustment" onclick="scrollTo('stock-adjustment')">
    <span class="icon"><i class="bi bi-pencil-square"></i></span> Inventory Adjustment
</a>
<a class="sidebar-link" data-section="stock-transfer" onclick="scrollTo('stock-transfer')">
    <span class="icon"><i class="bi bi-arrow-left-right"></i></span> Transfer Stok
</a>

<div class="sidebar-section-label">Laporan</div>
<a class="sidebar-link" data-section="report-movement" onclick="scrollTo('report-movement')">
    <span class="icon"><i class="bi bi-bar-chart"></i></span> Stock Movement Report
</a>
<a class="sidebar-link" data-section="report-gr-gd" onclick="scrollTo('report-gr-gd')">
    <span class="icon"><i class="bi bi-file-earmark-bar-graph"></i></span> Rekap GR & GD
</a>
<a class="sidebar-link" data-section="cetak-dokumen" onclick="scrollTo('cetak-dokumen')">
    <span class="icon"><i class="bi bi-printer"></i></span> Cetak Dokumen
</a>

<div class="sidebar-section-label">Bantuan</div>
<a class="sidebar-link" data-section="troubleshoot-scm" onclick="scrollTo('troubleshoot-scm')">
    <span class="icon"><i class="bi bi-tools"></i></span> Troubleshooting
</a>
@endsection

{{-- ── CONTENT ── --}}
@section('content')

{{-- PENGANTAR --}}
<div class="gb-section" id="pengantar-scm">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#faeeda;color:#ff8800;">
            <i class="bi bi-building-gear"></i>
        </div>
        <h2 class="gb-section-title">Panduan Tim SCM</h2>
    </div>
    <p class="gb-section-desc">
        Panduan ini ditujukan untuk <strong>Tim SCM / Back Office</strong> yang mengelola seluruh alur supply chain:
        dari review PO outlet, pengiriman, penerimaan barang dari supplier, hingga penerbitan invoice.
    </p>
    <div class="info-box">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>Akses SCM lebih luas dari outlet.</strong> Tim SCM dapat melihat semua outlet, semua gudang,
            dan memiliki hak untuk approve, reject, dan memproses dokumen di seluruh sistem.
        </div>
    </div>
    <div class="warn-box">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div>
            <strong>Tanggung jawab besar:</strong> Setiap aksi SCM (approve, konfirmasi GR, buat invoice) langsung
            mempengaruhi data stok dan keuangan. Pastikan semua data sudah diverifikasi sebelum disimpan.
        </div>
    </div>
</div>

{{-- ALUR SCM --}}
<div class="gb-section" id="alur-scm">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#e1f5ee;color:#0f6e56;">
            <i class="bi bi-diagram-3"></i>
        </div>
        <h2 class="gb-section-title">Alur Kerja Tim SCM</h2>
    </div>
    <p class="gb-section-desc">Gambaran besar alur dokumen yang dikelola SCM dari awal sampai akhir.</p>

    <div class="flow-inline">
        <span class="flow-node" style="background:#eeedfe;color:#3c3489;">PO Outlet Masuk</span>
        <span class="flow-arrow">›</span>
        <span class="flow-node" style="background:#e6f1fb;color:#0c447c;">Review & Approve</span>
        <span class="flow-arrow">›</span>
        <span class="flow-node" style="background:#faeeda;color:#633806;">Finalisasi Kirim</span>
        <span class="flow-arrow">›</span>
        <span class="flow-node" style="background:#e1f5ee;color:#085041;">GD In Transit</span>
        <span class="flow-arrow">›</span>
        <span class="flow-node" style="background:#eaf3de;color:#27500a;">Delivered</span>
    </div>

    <div class="flow-inline mt-2">
        <span class="flow-node" style="background:#eeedfe;color:#3c3489;">PO ke Supplier</span>
        <span class="flow-arrow">›</span>
        <span class="flow-node" style="background:#e6f1fb;color:#0c447c;">GR Penerimaan</span>
        <span class="flow-arrow">›</span>
        <span class="flow-node" style="background:#faeeda;color:#633806;">QC Check</span>
        <span class="flow-arrow">›</span>
        <span class="flow-node" style="background:#e1f5ee;color:#085041;">Purchase Invoice</span>
        <span class="flow-arrow">›</span>
        <span class="flow-node" style="background:#eaf3de;color:#27500a;">Paid</span>
    </div>

    <table class="gb-table mt-4">
        <thead>
            <tr><th>Dokumen</th><th>Tabel di DB</th><th>Status yang Mungkin</th></tr>
        </thead>
        <tbody>
            <tr><td><strong>Purchase Order Outlet</strong></td><td>tbl_po</td><td>Waiting › Approved › In Transit › Delivered › Rejected</td></tr>
            <tr><td><strong>Surat Jalan</strong></td><td>tbl_surat_jalan</td><td>In Transit › Delivered</td></tr>
            <tr><td><strong>Goods Delivery (GD)</strong></td><td>tbl_goods_deliveries</td><td>DRAFT › IN_TRANSIT › DELIVERED › CANCELLED</td></tr>
            <tr><td><strong>Goods Receipt (GR)</strong></td><td>tbl_goods_receipts</td><td>DRAFT › PARTIAL › RECEIVED</td></tr>
            <tr><td><strong>Purchase Invoice (PI)</strong></td><td>tbl_purchase_invoices</td><td>DRAFT › PENDING › APPROVED › PARTIAL_PAID › PAID › CANCELLED</td></tr>
            <tr><td><strong>Sales Invoice (SI)</strong></td><td>tbl_sales_invoices</td><td>DRAFT › ISSUED › PARTIAL_PAID › PAID › OVERDUE › CANCELLED</td></tr>
        </tbody>
    </table>
</div>

{{-- REVIEW PO --}}
<div class="gb-section" id="review-po">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#eeedfe;color:#696cff;">
            <i class="bi bi-check2-square"></i>
        </div>
        <h2 class="gb-section-title">Review & Approve PO Outlet</h2>
    </div>
    <p class="gb-section-desc">
        Setiap PO yang dibuat outlet harus diverifikasi SCM sebelum diproses pengirimannya.
    </p>

    <div class="step-list">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Buka Dashboard Pengiriman SCM</div>
                <div class="step-desc">
                    Navigasi ke <strong>SCM → Dashboard Pengiriman</strong>.
                    Semua PO dengan status <span class="badge-inline bi-amber">Waiting</span> dari semua outlet akan tampil.
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Pilih Rute Area terlebih dahulu</div>
                <div class="step-desc">
                    Gunakan filter <strong>Rute Pengiriman</strong> untuk mengelompokkan PO berdasarkan area.
                    Ini penting agar pengiriman bisa dikelompokkan per rute.
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Klik Detail untuk review isi PO</div>
                <div class="step-desc">
                    Klik ikon <i class="bi bi-eye"></i> untuk melihat list barang yang diminta outlet.
                    Verifikasi: apakah barang tersedia di stok? Apakah jumlahnya wajar?
                </div>
                <div class="screenshot-box">
                    <i class="bi bi-image"></i>
                    [Screenshot: Modal detail PO dengan daftar barang dan tombol Approve/Reject]
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-body">
                <div class="step-title">Approve atau Reject</div>
                <div class="step-desc">
                    <ul style="padding-left:16px;margin:6px 0;">
                        <li>Klik <strong style="color:#27500a;">Approve</strong> jika PO bisa diproses → status jadi <span class="badge-inline bi-blue">Approved</span></li>
                        <li>Klik <strong style="color:#a32d2d;">Reject</strong> jika ada masalah → isi alasan penolakan yang jelas</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="tip-box">
        <i class="bi bi-lightbulb-fill"></i>
        <div>
            <strong>Best practice:</strong> Review PO di pagi hari sebelum jam 10.00 agar pengiriman bisa dijadwalkan hari yang sama. PO yang di-approve setelah jam 12 biasanya baru dikirim keesokan harinya.
        </div>
    </div>
</div>

{{-- FINALISASI PENGIRIMAN --}}
<div class="gb-section" id="finalisasi-pengiriman">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#e6f1fb;color:#0c447c;">
            <i class="bi bi-truck"></i>
        </div>
        <h2 class="gb-section-title">Finalisasi Pengiriman</h2>
    </div>
    <p class="gb-section-desc">
        Setelah PO di-approve, proses ini akan otomatis membuat Surat Jalan, Sales Order, dan Goods Delivery.
    </p>

    <div class="danger-box">
        <i class="bi bi-shield-exclamation"></i>
        <div>
            <strong>Perhatian!</strong> Proses finalisasi pengiriman <strong>langsung memotong stok gudang</strong>
            dan membuat dokumen SO + GD secara otomatis. Pastikan PO yang dipilih sudah benar sebelum klik Buat Surat Jalan.
        </div>
    </div>

    <div class="step-list mt-3">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Pilih rute area</div>
                <div class="step-desc">Filter PO berdasarkan rute. Ini memastikan pengiriman searah bisa digabung dalam 1 surat jalan.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Centang PO yang akan dikirim</div>
                <div class="step-desc">Centang semua PO berstatus Approved yang akan masuk ke pengiriman ini. Gunakan "Pilih Semua" jika semua PO dalam rute akan dikirim bersamaan.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Isi Driver dan Armada</div>
                <div class="step-desc">
                    Pilih driver dan kendaraan dari dropdown. Wajib diisi untuk pengiriman dari gudang (tipe GUDANG).
                    Untuk barang langsung dari supplier, driver otomatis diisi "DRIVER SUPPLIER".
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-body">
                <div class="step-title">Klik "Buat Surat Jalan"</div>
                <div class="step-desc">
                    Sistem akan otomatis membuat:
                    <ul style="padding-left:16px;margin:6px 0;">
                        <li>Surat Jalan (SJ) untuk barang GUDANG</li>
                        <li>Sales Order (SO) per outlet</li>
                        <li>Goods Delivery (GD) per outlet</li>
                        <li>Transaksi stok KELUAR di tbl_stock_transactions</li>
                        <li>PO SCM ke supplier (untuk barang tipe SUPPLIER)</li>
                    </ul>
                </div>
                <div class="screenshot-box">
                    <i class="bi bi-image"></i>
                    [Screenshot: Halaman dashboard pengiriman dengan PO tercentang dan tombol Buat Surat Jalan]
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">5</div>
            <div class="step-body">
                <div class="step-title">Cetak Surat Jalan & Packing List</div>
                <div class="step-desc">
                    Setelah SJ dibuat, cetak <strong>Surat Jalan</strong> dan <strong>Packing List</strong>
                    untuk diberikan ke driver. Akses dari menu cetak di detail surat jalan.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- GOODS RECEIPT --}}
<div class="gb-section" id="goods-receipt">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#faeeda;color:#854f0b;">
            <i class="bi bi-box-arrow-in-down"></i>
        </div>
        <h2 class="gb-section-title">Goods Receipt (GR) — Penerimaan dari Supplier</h2>
    </div>
    <p class="gb-section-desc">
        GR dibuat saat barang dari supplier tiba di gudang SCM. GR adalah syarat untuk membuat Purchase Invoice.
    </p>

    <div class="step-list">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Buka menu Goods Receipt</div>
                <div class="step-desc">Navigasi ke <strong>SCM → Goods Receipt</strong>. Klik "+ Buat GR Baru".</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Pilih Purchase Order (PO) dari supplier</div>
                <div class="step-desc">
                    Di dropdown PO, pilih PO yang barangnya baru tiba. Sistem akan otomatis mengisi
                    detail item berdasarkan PO yang dipilih.
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Isi qty yang diterima aktual</div>
                <div class="step-desc">
                    Sesuaikan kolom <strong>Qty Diterima</strong> dengan jumlah fisik yang benar-benar masuk.
                    Jika ada barang kurang, isi sesuai yang datang — GR akan berstatus
                    <span class="badge-inline bi-amber">PARTIAL</span>.
                </div>
                <div class="tip-box">
                    <i class="bi bi-lightbulb-fill"></i>
                    <div>GR berstatus PARTIAL tetap bisa dibuatkan invoice. Kekurangan barang bisa didokumentasikan di kolom catatan.</div>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-body">
                <div class="step-title">Lakukan QC dan update status</div>
                <div class="step-desc">
                    Setelah cek kualitas, update status QC:
                    <ul style="padding-left:16px;margin:6px 0;line-height:2">
                        <li><span class="badge-inline bi-green">PASSED</span> — semua barang lulus QC</li>
                        <li><span class="badge-inline bi-amber">PARTIAL_REJECTED</span> — sebagian ditolak</li>
                        <li><span class="badge-inline bi-red">REJECTED</span> — semua ditolak, kembalikan ke supplier</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">5</div>
            <div class="step-body">
                <div class="step-title">Konfirmasi GR</div>
                <div class="step-desc">
                    Klik <strong>"Konfirmasi"</strong> untuk mengubah status GR menjadi
                    <span class="badge-inline bi-green">RECEIVED</span>.
                    Stok gudang otomatis bertambah (tipe MASUK di tbl_stock_transactions).
                </div>
            </div>
        </div>
    </div>
</div>

{{-- GOODS DELIVERY --}}
<div class="gb-section" id="goods-delivery">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#e1f5ee;color:#0f6e56;">
            <i class="bi bi-box-arrow-up-right"></i>
        </div>
        <h2 class="gb-section-title">Goods Delivery (GD) — Konfirmasi Pengiriman</h2>
    </div>
    <p class="gb-section-desc">
        GD dibuat otomatis saat finalisasi pengiriman. SCM perlu mengupdate status saat barang tiba di outlet.
    </p>

    <div class="step-list">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Buka menu Goods Delivery</div>
                <div class="step-desc">Navigasi ke <strong>SCM → Goods Delivery</strong>. Filter status <span class="badge-inline bi-purple">IN_TRANSIT</span> untuk GD yang sedang dalam perjalanan.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Konfirmasi saat barang tiba di outlet</div>
                <div class="step-desc">
                    Saat outlet mengkonfirmasi penerimaan atau driver melapor, buka GD tersebut dan klik
                    <strong>"Konfirmasi Delivered"</strong>. Status berubah ke <span class="badge-inline bi-green">DELIVERED</span>.
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">GD status DELIVERED = syarat buat Sales Invoice</div>
                <div class="step-desc">
                    Hanya GD berstatus <span class="badge-inline bi-green">DELIVERED</span> atau
                    <span class="badge-inline bi-amber">PARTIAL_DELIVERED</span> yang bisa dibuatkan Sales Invoice.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- PURCHASE INVOICE --}}
<div class="gb-section" id="purchase-invoice">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#faeeda;color:#633806;">
            <i class="bi bi-receipt"></i>
        </div>
        <h2 class="gb-section-title">Purchase Invoice (PI)</h2>
    </div>
    <p class="gb-section-desc">
        PI dibuat berdasarkan GR yang sudah dikonfirmasi. Ini adalah tagihan dari supplier ke SCM.
    </p>

    <div class="info-box">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>Syarat membuat PI:</strong> GR harus berstatus <span class="badge-inline bi-green">RECEIVED</span> atau <span class="badge-inline bi-amber">PARTIAL</span> dan belum punya PI aktif.
        </div>
    </div>

    <div class="step-list mt-3">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Buka menu Purchase Invoice → Buat PI</div>
                <div class="step-desc">Klik "+ Generate Invoice". Modal form akan muncul.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Pilih GR dari dropdown</div>
                <div class="step-desc">
                    Dropdown menampilkan GR yang sudah RECEIVED/PARTIAL dan belum punya PI aktif.
                    Format: <code>[STATUS] GR-XXXXXX — Nama Supplier (PO: XXXXX, Tgl)</code>
                </div>
                <div class="warn-box">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>Jika dropdown kosong, berarti belum ada GR yang memenuhi syarat. Konfirmasi GR terlebih dahulu.</div>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Isi data invoice</div>
                <div class="step-desc">
                    <ul style="padding-left:16px;margin:6px 0;">
                        <li><strong>Tanggal Invoice</strong> — sesuai invoice dari supplier</li>
                        <li><strong>Jatuh Tempo</strong> — tanggal pembayaran harus dilakukan</li>
                        <li><strong>Payment Terms</strong> — default 30 hari</li>
                        <li><strong>Diskon</strong> — isi jika ada</li>
                        <li><strong>PPN</strong> — 11%, otomatis dihitung</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-body">
                <div class="step-title">Klik "Generate Invoice"</div>
                <div class="step-desc">PI dibuat dengan status <span class="badge-inline bi-amber">PENDING</span>. Bisa di-print dan diteruskan ke finance untuk pembayaran.</div>
            </div>
        </div>
    </div>
</div>

{{-- SALES INVOICE --}}
<div class="gb-section" id="sales-invoice">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#eeedfe;color:#3c3489;">
            <i class="bi bi-file-earmark-text"></i>
        </div>
        <h2 class="gb-section-title">Sales Invoice (SI)</h2>
    </div>
    <p class="gb-section-desc">
        SI adalah tagihan dari SCM ke outlet atas barang yang sudah dikirim dan diterima.
    </p>

    <div class="info-box">
        <i class="bi bi-info-circle-fill"></i>
        <div>
            <strong>Syarat membuat SI:</strong> GD harus berstatus <span class="badge-inline bi-green">DELIVERED</span> atau <span class="badge-inline bi-amber">PARTIAL_DELIVERED</span> dan belum punya SI aktif.
        </div>
    </div>

    <div class="step-list mt-3">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Buka menu Sales Invoice → Buat SI</div>
                <div class="step-desc">Klik "+ Generate Invoice" di halaman Sales Invoice.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Pilih GD dari dropdown</div>
                <div class="step-desc">
                    Dropdown menampilkan GD yang memenuhi syarat.
                    Format: <code>[STATUS] GD-XXXXXX — Nama Outlet (SO: XXXXX, Tiba: Tgl)</code>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Verifikasi detail dan isi data invoice</div>
                <div class="step-desc">
                    Data customer dan item otomatis terisi dari GD. Cek dan sesuaikan:
                    tanggal invoice, jatuh tempo, diskon, dan PPN.
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-body">
                <div class="step-title">Generate → Print</div>
                <div class="step-desc">SI dibuat dengan status <span class="badge-inline bi-blue">ISSUED</span>. Cetak untuk dikirimkan ke outlet.</div>
            </div>
        </div>
    </div>
</div>

{{-- STOCK CONTROL --}}
<div class="gb-section" id="stock-control">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#e6f1fb;color:#0c447c;">
            <i class="bi bi-clipboard-data"></i>
        </div>
        <h2 class="gb-section-title">Stock Control — Monitoring Real-time</h2>
    </div>
    <p class="gb-section-desc">
        Pantau stok aktual di semua gudang sekaligus dalam satu tampilan.
    </p>

    <div class="step-list">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Buka menu Stock Control</div>
                <div class="step-desc">Navigasi ke <strong>Purchasing → Stock Control</strong>.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Baca tabel Global Monitoring</div>
                <div class="step-desc">
                    Tabel menampilkan stok setiap bahan per gudang secara real-time.
                    Stok diambil dari transaksi terakhir (stok_sesudah baris terbaru).
                    <ul style="padding-left:16px;margin:6px 0;line-height:2">
                        <li>Angka <span style="color:#a32d2d;font-weight:700;">merah/0</span> → stok habis</li>
                        <li>Badge kuning → stok di bawah stok minimal</li>
                        <li>Angka hitam → stok aman</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Perhatikan widget Kritis dan Menipis</div>
                <div class="step-desc">
                    Di bagian atas halaman ada widget ringkasan:
                    <strong>Stok Habis/Kosong</strong> dan <strong>Stok Menipis (Warning)</strong>.
                    Ini adalah prioritas yang harus ditangani hari ini.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- INVENTORY ADJUSTMENT --}}
<div class="gb-section" id="stock-adjustment">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#eeedfe;color:#696cff;">
            <i class="bi bi-pencil-square"></i>
        </div>
        <h2 class="gb-section-title">Inventory Adjustment (Stock Opname)</h2>
    </div>
    <p class="gb-section-desc">
        Gunakan fitur ini untuk menyesuaikan stok sistem dengan kondisi fisik gudang.
        Biasanya dilakukan saat stock opname bulanan atau saat ada selisih.
    </p>

    <div class="step-list">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Klik "Inventory Adjustment" di Stock Control</div>
                <div class="step-desc">Modal form adjustment akan terbuka.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Isi Transaction Information</div>
                <div class="step-desc">
                    <ul style="padding-left:16px;margin:6px 0;">
                        <li><strong>Location (Gudang)</strong> — pilih gudang yang diopname</li>
                        <li><strong>Opname Date</strong> — tanggal pelaksanaan opname</li>
                        <li><strong>Jenis Adjustment</strong> — Penambahan (in) atau Pengurangan (out)</li>
                        <li><strong>Keterangan</strong> — alasan adjustment, wajib diisi</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Tambahkan produk yang akan di-adjust</div>
                <div class="step-desc">
                    Klik <strong>"Browse Product"</strong>, cari bahan, lalu isi:
                    <ul style="padding-left:16px;margin:6px 0;">
                        <li><strong>Stock Fisik</strong> — hasil hitung fisik di gudang</li>
                        <li><strong>Adj Qty</strong> — jumlah yang akan ditambah/dikurangi</li>
                    </ul>
                    Kolom <strong>Current Stock</strong> dan <strong>Final Stock</strong> otomatis terhitung.
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-body">
                <div class="step-title">Save as Draft atau Save</div>
                <div class="step-desc">
                    <ul style="padding-left:16px;margin:6px 0;">
                        <li><strong>Save as Draft</strong> — simpan sementara, stok belum berubah</li>
                        <li><strong>Save</strong> — konfirmasi permanen, stok langsung diupdate di tbl_stock_transactions</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="danger-box">
        <i class="bi bi-shield-exclamation"></i>
        <div>
            <strong>Tidak bisa di-undo!</strong> Adjustment yang sudah di-Save (status Confirmed) akan langsung mengubah stok secara permanen. Pastikan data sudah benar sebelum Save.
        </div>
    </div>
</div>

{{-- TRANSFER STOK --}}
<div class="gb-section" id="stock-transfer">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#e1f5ee;color:#0f6e56;">
            <i class="bi bi-arrow-left-right"></i>
        </div>
        <h2 class="gb-section-title">Transfer Stok Antar Gudang</h2>
    </div>
    <p class="gb-section-desc">
        Pindahkan stok dari satu gudang ke gudang lain saat ada kebutuhan redistribusi.
    </p>

    <div class="step-list">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Klik "Transfer Antar Gudang" di Stock Control</div>
                <div class="step-desc">Modal form transfer akan muncul.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Isi form transfer</div>
                <div class="step-desc">
                    <ul style="padding-left:16px;margin:6px 0;">
                        <li><strong>Pilih Bahan</strong> — cari dengan search</li>
                        <li><strong>Dari Gudang (Asal)</strong> — gudang sumber</li>
                        <li><strong>Ke Gudang (Tujuan)</strong> — gudang tujuan</li>
                        <li><strong>Jenis Armada</strong> — motor/pickup/truk</li>
                        <li><strong>Jumlah Transfer</strong> — qty yang dipindah</li>
                    </ul>
                </div>
                <div class="warn-box">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>Pastikan gudang asal dan tujuan tidak sama, dan stok gudang asal mencukupi.</div>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Kirim</div>
                <div class="step-desc">Klik "Kirim Barang Sekarang". Sistem akan mencatat KELUAR di gudang asal dan MASUK di gudang tujuan secara bersamaan.</div>
            </div>
        </div>
    </div>
</div>

{{-- REPORT MOVEMENT --}}
<div class="gb-section" id="report-movement">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#e6f1fb;color:#0c447c;">
            <i class="bi bi-bar-chart"></i>
        </div>
        <h2 class="gb-section-title">Stock Movement Report</h2>
    </div>
    <p class="gb-section-desc">
        Lihat riwayat lengkap semua pergerakan stok dengan filter yang fleksibel.
    </p>

    <div class="step-list">
        <div class="step-item">
            <div class="step-num">1</div>
            <div class="step-body">
                <div class="step-title">Buka Purchasing → Reports → Stock Movement</div>
                <div class="step-desc">Halaman report dengan tabel dan summary cards akan terbuka.</div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">2</div>
            <div class="step-body">
                <div class="step-title">Atur filter</div>
                <div class="step-desc">
                    Filter yang tersedia:
                    <ul style="padding-left:16px;margin:6px 0;">
                        <li><strong>Rentang Tanggal</strong> — default 30 hari terakhir</li>
                        <li><strong>Produk</strong> — filter per bahan tertentu</li>
                        <li><strong>Gudang</strong> — filter per lokasi gudang</li>
                        <li><strong>Tipe</strong> — MASUK / KELUAR / ADJUSTMENT / WASTE</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-body">
                <div class="step-title">Export atau Print</div>
                <div class="step-desc">Gunakan tombol <strong>Print</strong> untuk cetak laporan atau <strong>Export CSV</strong> untuk analisis lebih lanjut di Excel.</div>
            </div>
        </div>
    </div>
</div>

{{-- REPORT GR GD --}}
<div class="gb-section" id="report-gr-gd">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#eaf3de;color:#27500a;">
            <i class="bi bi-file-earmark-bar-graph"></i>
        </div>
        <h2 class="gb-section-title">Rekapitulasi GR & GD</h2>
    </div>
    <p class="gb-section-desc">
        Laporan rekap penerimaan dan pengiriman barang per periode.
    </p>

    <table class="gb-table">
        <thead>
            <tr><th>Laporan</th><th>Isi</th><th>Filter Tersedia</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>GR Recap</strong></td>
                <td>Semua penerimaan dari supplier, status QC, nilai</td>
                <td>Tanggal, Supplier, Gudang, Status, Status QC</td>
            </tr>
            <tr>
                <td><strong>GD Recap</strong></td>
                <td>Semua pengiriman ke outlet, status, nilai</td>
                <td>Tanggal, Outlet, Gudang, Status, Driver</td>
            </tr>
            <tr>
                <td><strong>Stock Opname</strong></td>
                <td>Riwayat semua adjustment stok</td>
                <td>Tanggal, Gudang, Status (Draft/Confirmed)</td>
            </tr>
        </tbody>
    </table>

    <div class="tip-box mt-3">
        <i class="bi bi-lightbulb-fill"></i>
        <div>
            Klik tanda <strong>›</strong> di setiap baris untuk expand detail item per transaksi.
            Gunakan sidebar kanan untuk melihat top supplier dan QC pass rate bulan ini.
        </div>
    </div>
</div>

{{-- CETAK DOKUMEN --}}
<div class="gb-section" id="cetak-dokumen">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#faeeda;color:#854f0b;">
            <i class="bi bi-printer"></i>
        </div>
        <h2 class="gb-section-title">Cetak Dokumen</h2>
    </div>
    <p class="gb-section-desc">
        Semua dokumen bisa dicetak langsung dari sistem. Halaman cetak terbuka di tab baru.
    </p>

    <table class="gb-table">
        <thead>
            <tr><th>Dokumen</th><th>Cara Akses</th><th>Keterangan</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Surat Jalan</strong></td>
                <td>List SJ → klik ikon printer</td>
                <td>Per PO outlet, berisi tanda tangan 3 pihak</td>
            </tr>
            <tr>
                <td><strong>Packing List</strong></td>
                <td>List SJ → klik Packing List</td>
                <td>Rekap semua barang dalam 1 pengiriman</td>
            </tr>
            <tr>
                <td><strong>Goods Delivery</strong></td>
                <td>List GD → klik ikon printer</td>
                <td>Termasuk checklist penerimaan outlet</td>
            </tr>
            <tr>
                <td><strong>Goods Receipt</strong></td>
                <td>List GR → klik ikon printer</td>
                <td>Termasuk info QC dan batch number</td>
            </tr>
            <tr>
                <td><strong>Purchase Invoice</strong></td>
                <td>List PI → klik ikon printer</td>
                <td>Termasuk payment tracker outstanding</td>
            </tr>
            <tr>
                <td><strong>Sales Invoice</strong></td>
                <td>List SI → klik ikon printer</td>
                <td>Termasuk referensi GD dan SO</td>
            </tr>
        </tbody>
    </table>

    <div class="tip-box">
        <i class="bi bi-lightbulb-fill"></i>
        <div>
            Semua halaman cetak memiliki opsi <strong>A4/A5</strong> dan <strong>Portrait/Landscape</strong>
            di toolbar atas. Pilih sesuai kebutuhan sebelum klik Cetak.
        </div>
    </div>
</div>

{{-- TROUBLESHOOTING SCM --}}
<div class="gb-section" id="troubleshoot-scm">
    <div class="gb-section-header">
        <div class="gb-section-icon" style="background:#fcebeb;color:#a32d2d;">
            <i class="bi bi-tools"></i>
        </div>
        <h2 class="gb-section-title">Troubleshooting</h2>
    </div>
    <p class="gb-section-desc">Masalah teknis umum yang ditemui tim SCM dan cara mengatasinya.</p>

    <div class="trouble-item">
        <div class="trouble-q">
            <i class="bi bi-x-circle-fill trouble-icon"></i>
            Dropdown GR/GD kosong saat mau buat invoice
            <i class="bi bi-chevron-down chev"></i>
        </div>
        <div class="trouble-a">
            Pastikan: (1) GR sudah berstatus RECEIVED atau PARTIAL untuk PI, atau GD sudah DELIVERED/PARTIAL_DELIVERED untuk SI. (2) GR/GD tersebut belum punya invoice aktif (bukan DRAFT/CANCELLED). Cek dengan query SQL di bagian bawah panduan ini atau hubungi tim IT.
        </div>
    </div>

    <div class="trouble-item">
        <div class="trouble-q">
            <i class="bi bi-x-circle-fill trouble-icon"></i>
            Error "Unknown column" saat membuka halaman invoice
            <i class="bi bi-chevron-down chev"></i>
        </div>
        <div class="trouble-a">
            Ini adalah error database — kolom yang di-query tidak ada di tabel. Segera laporkan ke tim IT beserta screenshot error lengkap. Jangan coba refresh berulang karena tidak akan memperbaiki masalah ini.
        </div>
    </div>

    <div class="trouble-item">
        <div class="trouble-q">
            <i class="bi bi-x-circle-fill trouble-icon"></i>
            Finalisasi pengiriman error / rollback
            <i class="bi bi-chevron-down chev"></i>
        </div>
        <div class="trouble-a">
            Sistem menggunakan transaction DB — jika ada error di tengah proses, semua perubahan dibatalkan otomatis (rollback). Cek pesan error yang muncul: biasanya karena data warehouse_id tidak valid, customer tidak ditemukan, atau nomor dokumen duplikat. Hubungi IT dengan screenshot error lengkap.
        </div>
    </div>

    <div class="trouble-item">
        <div class="trouble-q">
            <i class="bi bi-x-circle-fill trouble-icon"></i>
            Stok tidak berkurang setelah finalisasi pengiriman
            <i class="bi bi-chevron-down chev"></i>
        </div>
        <div class="trouble-a">
            Cek di tbl_stock_transactions apakah ada baris baru dengan reference_type = 'GD' dan tipe = 'KELUAR'. Jika tidak ada, berarti finalisasi gagal sebagian. Hubungi IT untuk investigasi lebih lanjut. Jangan lakukan adjustment manual sebelum root cause ditemukan.
        </div>
    </div>

    <div class="trouble-item">
        <div class="trouble-q">
            <i class="bi bi-x-circle-fill trouble-icon"></i>
            branch_id Sales Order tidak sesuai dengan DC yang benar
            <i class="bi bi-chevron-down chev"></i>
        </div>
        <div class="trouble-a">
            Pastikan setiap outlet sudah di-mapping ke warehouse di tabel tbl_warehouse (kolom branch_id = outlet.id). Cek dengan query: SELECT o.id, o.nama_outlet FROM tbl_outlets o LEFT JOIN tbl_warehouse w ON w.branch_id = o.id WHERE w.id IS NULL. Outlet yang muncul perlu di-mapping ke DC yang tepat.
        </div>
    </div>
</div>

@endsection
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Serah Terima Aset IT - {{ $laptop->asset_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { background-color: white !important; }
            .no-print { display: none !important; }
            @page { margin: 2cm; }
        }
        body { font-family: 'Times New Roman', Times, serif; background-color: #f1f5f9; }
        .page { background: white; max-width: 21cm; min-height: 29.7cm; margin: 2rem auto; padding: 2cm; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
    </style>
</head>
<body class="text-slate-900">

    <div class="no-print text-center py-4 bg-slate-800 fixed top-0 w-full z-50 shadow-md">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold shadow hover:bg-blue-700 flex items-center gap-2 mx-auto">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Dokumen (Print / Simpan PDF)
        </button>
        <div class="text-slate-300 text-sm mt-2">Tekan Cetak, lalu pada menu tujuan ubah printer ke "Save as PDF" jika ingin menyimpan file.</div>
    </div>

    <!-- Spacer for fixed header -->
    <div class="h-24 no-print"></div>

    <div class="page">
        <!-- KOP SURAT -->
        <div class="border-b-4 border-slate-900 pb-6 mb-8 flex items-center justify-between">
            <div>
                <!-- Bisa diganti dengan logo perusahaan yang valid -->
                <h1 class="text-3xl font-extrabold tracking-widest uppercase">GEPREKINAJA</h1>
                <p class="text-sm mt-1">Divisi IT Support & Asset Management</p>
                <p class="text-sm">Gedung Pusat Operasional Geprekin</p>
            </div>
            <div class="text-right">
                <svg class="barcode-svg h-12" data-barcode="{{ $laptop->barcode }}"></svg>
            </div>
        </div>

        <!-- JUDUL -->
        <div class="text-center mb-10">
            <h2 class="text-xl font-bold uppercase underline underline-offset-4 mb-2">Berita Acara Serah Terima Aset IT</h2>
            <p class="text-sm">Nomor Dokumen: BAST-IT/{{ date('Y/m', strtotime($laptop->updated_at)) }}/{{ $laptop->id }}</p>
        </div>

        <!-- ISI -->
        <div class="space-y-6 text-justify leading-relaxed">
            <p>Pada hari ini, <strong class="uppercase">{{ \Carbon\Carbon::parse($laptop->updated_at)->isoFormat('dddd, D MMMM Y') }}</strong>, telah dilakukan serah terima Aset IT Perusahaan berupa perangkat Komputer/Laptop, dengan rincian pihak sebagai berikut:</p>

            <div class="pl-4 border-l-2 border-slate-300 space-y-2">
                <h3 class="font-bold">PIHAK PERTAMA (Penyerah / IT Support):</h3>
                <p>Mewakili Divisi IT Geprekin, menyerahkan aset di bawah ini dalam kondisi berfungsi normal dan layak pakai.</p>
            </div>

            <div class="pl-4 border-l-2 border-slate-300 space-y-2 mt-4">
                <h3 class="font-bold">PIHAK KEDUA (Penerima / User):</h3>
                <table class="w-full">
                    <tr><td class="w-1/3 py-1">Nama Lengkap</td><td class="w-4">:</td><td class="font-bold">{{ $laptop->assigned_user_name }}</td></tr>
                    <tr><td class="w-1/3 py-1">Email Pengguna</td><td class="w-4">:</td><td>{{ $laptop->assigned_user_email }}</td></tr>
                </table>
            </div>

            <p class="mt-6">Adapun spesifikasi spesifik dari perangkat aset yang diserahterimakan adalah:</p>

            <table class="w-full border-collapse border border-slate-800 mb-6">
                <tbody>
                    <tr><td class="border border-slate-800 p-2 font-bold bg-slate-100 w-1/3">Kode Aset (Asset ID)</td><td class="border border-slate-800 p-2">{{ $laptop->asset_code }}</td></tr>
                    <tr><td class="border border-slate-800 p-2 font-bold bg-slate-100">Merek & Model</td><td class="border border-slate-800 p-2">{{ $laptop->brand_model }}</td></tr>
                    <tr><td class="border border-slate-800 p-2 font-bold bg-slate-100">Serial Number (SN)</td><td class="border border-slate-800 p-2">{{ $laptop->serial_number }}</td></tr>
                    <tr><td class="border border-slate-800 p-2 font-bold bg-slate-100">Prosesor (CPU)</td><td class="border border-slate-800 p-2">{{ $laptop->cpu ?? '-' }}</td></tr>
                    <tr><td class="border border-slate-800 p-2 font-bold bg-slate-100">Memori (RAM)</td><td class="border border-slate-800 p-2">{{ $laptop->ram ?? '-' }}</td></tr>
                    <tr><td class="border border-slate-800 p-2 font-bold bg-slate-100">Penyimpanan (SSD/HDD)</td><td class="border border-slate-800 p-2">{{ $laptop->ssd ?? '-' }}</td></tr>
                </tbody>
            </table>

            <h3 class="font-bold mt-8 mb-2">Syarat & Ketentuan Penggunaan Aset:</h3>
            <ol class="list-decimal pl-5 space-y-1 text-sm">
                <li>PIHAK KEDUA bersedia menjaga dan merawat aset dengan sebaik-baiknya.</li>
                <li>Perangkat ini adalah milik perusahaan dan hanya digunakan untuk keperluan dan operasional kerja.</li>
                <li>Dilarang menginstal perangkat lunak ilegal (bajakan) yang melanggar hukum hak cipta.</li>
                <li>Segala bentuk kerusakan yang diakibatkan oleh kelalaian (jatuh, terkena air, dsb.) dapat menjadi tanggung jawab PIHAK KEDUA.</li>
                <li>Apabila masa kerja PIHAK KEDUA berakhir atau ada instruksi pergantian unit, perangkat ini wajib dikembalikan ke Divisi IT dalam keadaan utuh.</li>
            </ol>

            <p class="mt-8">Demikian Berita Acara Serah Terima ini dibuat dengan sebenar-benarnya untuk digunakan sebagaimana mestinya.</p>
        </div>

        <!-- TANDA TANGAN -->
        <div class="mt-16 flex justify-between px-10">
            <div class="text-center w-1/3">
                <p class="mb-4">PIHAK PERTAMA<br>IT Support</p>
                <div class="h-24 flex items-center justify-center">
                    @if($laptop->admin_signature)
                        <img src="{{ $laptop->admin_signature }}" class="max-h-full max-w-full mix-blend-multiply" alt="Tanda Tangan Admin">
                    @endif
                </div>
                <p class="font-bold underline uppercase">( {{ auth()->user()->name }} )</p>
                <p class="text-xs mt-1">Tanda Tangan Digital</p>
            </div>
            <div class="text-center w-1/3">
                <p class="mb-4">PIHAK KEDUA<br>Penerima Aset</p>
                <div class="h-24 flex items-center justify-center">
                    @if($laptop->current_signature)
                        <img src="{{ $laptop->current_signature }}" class="max-h-full max-w-full mix-blend-multiply" alt="Tanda Tangan Penerima">
                    @endif
                </div>
                <p class="font-bold underline uppercase">( {{ $laptop->assigned_user_name }} )</p>
                <p class="text-xs mt-1">Tanda Tangan Digital</p>
            </div>
        </div>
    </div>

    <!-- Tambahkan script JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const barcodes = document.querySelectorAll('.barcode-svg');
            barcodes.forEach(function(svg) {
                const code = svg.getAttribute('data-barcode');
                JsBarcode(svg, code, {
                    format: "CODE128",
                    width: 1.5,
                    height: 40,
                    displayValue: false,
                    background: "transparent",
                    lineColor: "#000000"
                });
            });
        });
    </script>
</body>
</html>

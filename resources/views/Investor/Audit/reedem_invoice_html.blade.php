<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Redeem - {{ $kodeRedeem }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
            padding: 20px;
        }

        #downloadPdfBtn {
            padding: 10px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 7px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .invoice-container {
            max-width: 750px;
            margin: 0 auto;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #e5e5e5;
            padding-bottom: 15px;
        }

        .invoice-header h2 {
            margin: 10px 0 5px;
            font-size: 26px;
            letter-spacing: 1px;
        }

        .invoice-header p {
            margin: 0;
            color: #666;
        }

        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        .invoice-details td {
            padding: 10px 0;
            font-size: 14px;
        }

        .invoice-details td:first-child {
            width: 35%;
            color: #444;
            font-weight: bold;
        }

        .qr-section {
            text-align: center;
            margin-top: 35px;
        }

        .qr-section img {
            margin-bottom: 10px;
        }

        .note {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px dashed #aaa;
            font-size: 13px;
            color: #555;
        }

        @media print {
            #downloadPdfBtn {
                display: none;
            }
            body {
                background: white;
                padding: 0;
            }
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

    <button id="downloadPdfBtn" onclick="window.print()">Download PDF</button>

    <div class="invoice-container">

        {{-- HEADER --}}
        <div class="invoice-header">
            <!--<img src="https://i.imgur.com/7yUvePI.png" width="70" alt="Logo"> {{-- Ganti logo --}}-->
            <h2>Bukti Redeem Hadiah</h2>
            <p>Program Geprekinaja</p>
        </div>

        {{-- INFO DETAIL --}}
        <div class="invoice-details">
            <table>
                <tr>
                    <td>Kode Redeem</td>
                    <td>: <strong>{{ $kodeRedeem }}</strong></td>
                </tr>
                <tr>
                    <td>Nama Pengguna</td>
                    <td>: {{ $user->nama_lengkap }}</td>
                </tr>
                <tr>
                    <td>Hadiah</td>
                    <td>: {{ $redeem->nama_hadiah }}</td>
                </tr>
                <tr>
                    <td>Poin Ditukar</td>
                    <td>: {{ number_format($redeem->jumlah_poin) }} poin</td>
                </tr>
                <tr>
                    <td>Tanggal Redeem</td>
                    <td>: {{ \Carbon\Carbon::parse($redeem->created_at)->format('d-m-Y H:i') }}</td>
                </tr>
            </table>
        </div>

        {{-- QR CODE --}}
        <div class="qr-section">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ $kodeRedeem }}"
                 alt="QR Code Redeem">
            <p><strong>Scan QR ini di Outlet untuk Verifikasi Redeem</strong></p>
        </div>

        {{-- CATATAN --}}
        <div class="note">
            <strong>Catatan:</strong>
            <ul>
                <li>Tunjukkan bukti ini (atau QR dari HP Anda) saat melakukan penukaran hadiah.</li>
                <li>Pastikan kode masih berlaku dan belum digunakan.</li>
                <li>Kode redeem hanya dapat digunakan satu kali.</li>
            </ul>
        </div>

    </div>

</body>
</html>

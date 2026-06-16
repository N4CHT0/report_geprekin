{{-- resources/views/Rollate/cetak.blade.php --}}
{{--
  DomPDF: position:absolute layout
  Canvas: 595pt × 230pt
  Left  : 190pt  (nomor undian lebih lebar, tidak terpotong)
  Right : 405pt
  
  Fixes:
  - Nomor 6 digit tidak terpotong: font-size dikecilkan + letter-spacing disesuaikan
  - Nama panjang: font-size adaptif + overflow hidden + satu baris
  - Tanggal: pakai locale 'id' agar Januari bukan January
  - Kota outlet: hide jika kosong/dash
  - Divider naik jika nama pendek, fixed top untuk grid info
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Undian {{ $data->nomor_undian ?? '-' }}</title>
    <style>
        @page {
            size: 595pt 230pt;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            width: 595pt;
            height: 230pt;
            overflow: hidden;
            font-family: DejaVu Sans, sans-serif;
            background: #fff;
        }

        .wrap {
            position: absolute;
            top: 0; left: 0;
            width: 595pt; height: 230pt;
            background: #fff;
        }

        /* ── LEFT GOLD ── */
        .left {
            position: absolute;
            top: 0; left: 0;
            width: 190pt; height: 230pt;
            background: #F5A623;
            overflow: hidden;
        }
        .left-topbar {
            position: absolute; top: 0; left: 0;
            width: 190pt; height: 4pt;
            background: #C8860A;
        }
        .left-botbar {
            position: absolute; bottom: 0; left: 0;
            width: 190pt; height: 4pt;
            background: #C8860A;
        }
        /* subtle right edge shadow */
        .left-edge {
            position: absolute; top: 0; right: 0;
            width: 8pt; height: 230pt;
            background: rgba(0,0,0,0.05);
        }
        /* decorative circle bg */
        .left-circle {
            position: absolute;
            width: 130pt; height: 130pt;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
            bottom: -40pt; right: -40pt;
        }

        /* logo */
        .logo-wrap {
            position: absolute;
            top: 20pt; left: 0; width: 190pt;
            text-align: center;
        }
        .logo-circle {
            display: inline-block;
            width: 54pt; height: 54pt;
            background: #fff;
            border-radius: 50%;
            overflow: hidden;
            padding: 4pt;
        }
        .logo-circle img {
            width: 46pt; height: 46pt;
            object-fit: contain;
        }

        .lbl-no {
            position: absolute;
            top: 86pt; left: 0; width: 190pt;
            text-align: center;
            font-size: 6pt;
            letter-spacing: 3pt;
            text-transform: uppercase;
            color: rgba(28,20,8,0.48);
            font-weight: bold;
        }

        /* 
           Nomor 6 digit: font-size 38pt, letter-spacing 2pt
           190pt kolom, padding ~10pt kiri-kanan → 170pt ruang
           6 karakter × ~26pt/char = ~156pt → pas
        */
        .big-num {
            position: absolute;
            top: 96pt; left: 0; width: 190pt;
            text-align: center;
            font-size: 38pt;
            font-weight: bold;
            color: #1C1408;
            line-height: 1;
            letter-spacing: 2pt;
        }

        .periode {
            position: absolute;
            top: 162pt; left: 0; width: 190pt;
            text-align: center;
            font-size: 6.5pt;
            color: rgba(28,20,8,0.42);
            font-weight: bold;
            letter-spacing: 0.5pt;
        }

        /* ── RIGHT PANEL ── */
        .right {
            position: absolute;
            top: 0; left: 190pt;
            width: 405pt; height: 230pt;
            background: #fff;
        }

        .r-topstrip {
            position: absolute; top: 0; left: 0;
            width: 405pt; height: 4pt;
            background: #F5A623;
        }

        /* vertical gold accent bar */
        .r-vline {
            position: absolute;
            top: 18pt; left: 22pt;
            width: 2.5pt; height: 46pt;
            background: #F5A623;
            border-radius: 2pt;
        }

        .r-brand {
            position: absolute;
            top: 17pt; left: 35pt;
            font-size: 6pt;
            letter-spacing: 2pt;
            text-transform: uppercase;
            color: #C8860A;
            font-weight: bold;
        }

        /* 
           Name: single line, overflow hidden via width constraint
           Font size 14pt — cukup untuk nama panjang ~35 karakter dalam 270pt
           overflow:hidden + white-space:nowrap mencegah wrap
        */
        .r-name {
            position: absolute;
            top: 28pt; left: 35pt;
            width: 270pt;
            font-size: 14pt;
            font-weight: bold;
            color: #1C1408;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
        }

        /* badge */
        .badge {
            position: absolute;
            top: 17pt; right: 20pt;
            background: #1C1408;
            color: #F5A623;
            font-size: 6pt;
            font-weight: bold;
            letter-spacing: 1.5pt;
            text-transform: uppercase;
            padding: 3pt 8pt;
            border-radius: 20pt;
        }

        /* divider fixed at 72pt — below name max height */
        .r-divider {
            position: absolute;
            top: 72pt; left: 22pt;
            width: 360pt; height: 0;
            border-top: 0.75pt solid #EEE8D8;
        }

        /* col separator */
        .col-sep {
            position: absolute;
            top: 78pt; left: 202pt;
            width: 0; height: 90pt;
            border-left: 0.75pt dashed #EEE8D8;
        }

        /* ── Info grid ── */
        .ik {
            position: absolute;
            font-size: 6pt;
            font-weight: bold;
            letter-spacing: 0.8pt;
            text-transform: uppercase;
            color: #B0996A;
        }
        .iv {
            position: absolute;
            font-size: 8pt;
            font-weight: bold;
            color: #1C1408;
            white-space: nowrap;
            overflow: hidden;
        }

        /* Col 1: left=22, width=172pt */
        .c1r1-k { top: 80pt;  left: 22pt; }
        .c1r1-v { top: 89pt;  left: 22pt; width: 172pt; }

        .c1r2-k { top: 108pt; left: 22pt; }
        .c1r2-v { top: 117pt; left: 22pt; width: 172pt; }

        .c1r3-k { top: 136pt; left: 22pt; }
        .c1r3-v { top: 145pt; left: 22pt; width: 172pt; }

        /* Col 2: left=210, width=172pt */
        .c2r1-k { top: 80pt;  left: 210pt; }
        .c2r1-v { top: 89pt;  left: 210pt; width: 172pt; }

        .c2r2-k { top: 108pt; left: 210pt; }
        .c2r2-v { top: 117pt; left: 210pt; width: 172pt; }

        /* Row 3 col2: only shown if kota exists */
        .c2r3-k { top: 136pt; left: 210pt; }
        .c2r3-v { top: 145pt; left: 210pt; width: 172pt; }

        /* ── Bottom strip ── */
        .r-botstrip {
            position: absolute;
            bottom: 0; left: 0;
            width: 405pt; height: 28pt;
            background: #FFFBF0;
            border-top: 0.75pt solid #EEE8D8;
        }
        .bot-txt {
            position: absolute;
            top: 9pt; left: 22pt;
            font-size: 6.5pt;
            color: #9A8560;
            font-weight: bold;
        }
        .bot-code {
            position: absolute;
            top: 8pt; right: 20pt;
            font-size: 8pt;
            font-weight: bold;
            color: #C8860A;
            letter-spacing: 2pt;
        }

        /* QR Code */
        .qr-code {
            position: absolute;
            bottom: 35pt;
            right: 20pt;
            width: 45pt;
            height: 45pt;
        }
    </style>
</head>
<body>
<div class="wrap">

    {{-- ── LEFT GOLD ── --}}
    <div class="left">
        <div class="left-topbar"></div>
        <div class="left-botbar"></div>
        <div class="left-edge"></div>
        <div class="left-circle"></div>
        <div class="logo-wrap">
            <div class="logo-circle">
                <img src="{{ public_path('/img/logo2.jpg') }}" alt="Logo">
            </div>
        </div>
        <div class="lbl-no">Nomor Undian</div>
        <div class="big-num">{{ $data->nomor_undian ?? '-' }}</div>
        <div class="periode">Periode {{ $data->periode ?? '-' }}</div>
    </div>

    {{-- ── RIGHT ── --}}
    <div class="right">
        <div class="r-topstrip"></div>
        <div class="badge">&#10003;&nbsp;VALID</div>
        <div class="r-vline"></div>
        <div class="r-brand">Kartu Undian Resmi &mdash; Geprekin Aja</div>
        <div class="r-name">{{ $data->nama_lengkap ?? '-' }}</div>

        <div class="r-divider"></div>
        <div class="col-sep"></div>

        {{-- Col 1 --}}
        <div class="ik c1r1-k">Outlet</div>
        <div class="iv c1r1-v">{{ $data->nama_outlet ?? '-' }}</div>

        <div class="ik c1r2-k">No. Telepon</div>
        <div class="iv c1r2-v">{{ $data->no_telp ?? '-' }}</div>

        <div class="ik c1r3-k">Tanggal Struk</div>
        <div class="iv c1r3-v">
            @if(!empty($data->tanggal_struk))
                @php
                    \Carbon\Carbon::setLocale('id');
                @endphp
                {{ \Carbon\Carbon::parse($data->tanggal_struk)->isoFormat('D MMMM YYYY') }}
            @else
                -
            @endif
        </div>

        {{-- Col 2 --}}
        <div class="ik c2r1-k">No. Struk</div>
        <div class="iv c2r1-v">{{ $data->nomor_struk ?? '-' }}</div>

        <div class="ik c2r2-k">Total Belanja</div>
        <div class="iv c2r2-v">
            @if(!empty($data->total_belanja))
                Rp {{ number_format((int)$data->total_belanja, 0, ',', '.') }}
            @else
                -
            @endif
        </div>

        {{-- Kota: hanya tampil jika ada isinya --}}
        @if(!empty($data->outlet_kota) && $data->outlet_kota !== '-')
            <div class="ik c2r3-k">Kota</div>
            <div class="iv c2r3-v">{{ $data->outlet_kota }}</div>
        @endif

        {{-- QR Code --}}
        @if(!empty($data->qr_code))
            <img class="qr-code" src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data={{ urlencode($data->qr_code) }}" alt="QR Code">
        @endif

        {{-- Bottom --}}
        <div class="r-botstrip">
            <div class="bot-txt">Simpan kartu ini untuk klaim hadiah &mdash; berlaku selama periode berjalan</div>
            <div class="bot-code">#{{ $data->nomor_undian ?? '-' }}</div>
        </div>
    </div>

</div>
</body>
</html>
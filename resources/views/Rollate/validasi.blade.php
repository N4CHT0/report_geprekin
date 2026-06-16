<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Undian — Geprekin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-[#FAFAF8] text-[#1C1408] antialiased min-h-screen flex items-center justify-center p-4">

    <div class="max-w-sm w-full bg-white border border-[#EEE8D8] rounded-3xl overflow-hidden shadow-xl">
        <div class="h-2 w-full bg-gradient-to-r from-[#F5A623] to-[#FFD166]"></div>
        
        <div class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-[#FFF8EC] border border-[#EEE8D8] rounded-full overflow-hidden mb-6 shadow-sm">
                <img src="{{ asset('/img/logo2.jpg') }}" alt="Logo" class="w-14 h-14 object-contain" onerror="this.style.display='none'">
            </div>

            @if($data)
                <div class="mb-4">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-green-100 text-green-600 rounded-full mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h1 class="text-2xl font-extrabold text-green-700">Kupon Valid!</h1>
                    <p class="text-sm text-[#9A8560] mt-2">Nomor undian ini resmi dan terdaftar di sistem Geprekin Aja.</p>
                </div>

                <div class="bg-[#FAFAF8] border border-[#EEE8D8] rounded-2xl p-5 text-left space-y-4 mt-6">
                    <div class="text-center">
                        <p class="text-[10px] font-bold text-[#9A8560] uppercase tracking-widest">Nomor Undian</p>
                        <p class="font-mono text-3xl font-bold text-[#1C1408]">#{{ $data->nomor_undian }}</p>
                    </div>
                    <hr class="border-[#EEE8D8]">
                    <div>
                        <p class="text-[10px] font-bold text-[#9A8560] uppercase tracking-widest">Nama Peserta</p>
                        <p class="font-bold text-sm text-[#1C1408] uppercase">{{ $data->nama_lengkap }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-[#9A8560] uppercase tracking-widest">Nomor Telepon</p>
                        <p class="font-bold text-sm text-[#1C1408]">{{ substr_replace($data->no_telp, '****', -4) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-[#9A8560] uppercase tracking-widest">Outlet / Asal</p>
                        <p class="font-bold text-sm text-[#1C1408] uppercase">{{ $data->nama_outlet }}</p>
                    </div>
                </div>
            @else
                <div class="mb-4">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 text-red-600 rounded-full mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h1 class="text-2xl font-extrabold text-red-700">Tidak Ditemukan</h1>
                    <p class="text-sm text-[#9A8560] mt-2">Maaf, nomor undian <span class="font-bold">#{{ $nomor }}</span> tidak ditemukan atau tidak valid di sistem kami.</p>
                </div>
            @endif

        </div>
        
        <div class="bg-[#F5A623] py-4 text-center">
            <p class="text-xs font-bold text-white uppercase tracking-wider">GEPREKIN AJA &copy; {{ date('Y') }}</p>
        </div>
    </div>

</body>
</html>

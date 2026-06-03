<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - HOSpace Geprekin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-[#EAF1F6] flex items-center justify-center min-h-screen p-4">

    <div class="max-w-sm w-full bg-white rounded-2xl shadow-xl overflow-hidden my-6 border border-slate-100">
        
        <div class="bg-[#2A435D] p-6 text-center flex flex-col items-center">
            <div class="bg-white px-3 py-1.5 rounded-lg shadow-sm inline-block mb-3">
                <img src="{{ asset('img/logo-hd.png') }}" alt="Geprekin Aja" class="h-8 object-contain">
            </div>
            <h2 class="text-xl font-bold text-white tracking-wide">Daftar Akun Karyawan</h2>
            <p class="text-slate-300 text-xs mt-1">Gunakan email resmi @geprekin.com</p>
        </div>

        <div class="p-6">
            @if($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg">
                    <ul class="list-disc list-inside text-xs text-red-700 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('hospace.register.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                           class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-[#2A435D] focus:ring-[#2A435D] outline-none transition text-slate-700 bg-slate-50 focus:bg-white" 
                           placeholder="Nama sesuai ID Card">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email Kantor (@geprekin.com)</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                           class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-[#2A435D] focus:ring-[#2A435D] outline-none transition text-slate-700 bg-slate-50 focus:bg-white" 
                           placeholder="username@geprekin.com">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password Baru</label>
                    <input type="password" name="password" id="password" required 
                           class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-[#2A435D] focus:ring-[#2A435D] outline-none transition text-slate-700 bg-slate-50 focus:bg-white" 
                           placeholder="Minimal 6 karakter">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required 
                           class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-[#2A435D] focus:ring-[#2A435D] outline-none transition text-slate-700 bg-slate-50 focus:bg-white" 
                           placeholder="Ulangi password">
                </div>

                <button type="submit" class="w-full bg-[#2A435D] hover:bg-[#1f3246] text-white text-sm font-bold py-2.5 rounded-xl transition shadow-lg shadow-[#2A435D]/30 mt-3">
                    Buat Akun Sekarang
                </button>
            </form>
            
            <div class="text-center mt-5">
                <p class="text-[11px] text-slate-400">
                    Sudah punya akun? <br>
                    <a href="{{ route('hospace.login') }}" class="text-[#2A435D] font-bold hover:underline inline-block mt-1">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Surveyor AI')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    @stack('styles')
</head>

<body class="bg-slate-100 text-slate-800">

<div class="min-h-screen flex">
    <aside class="w-72 bg-slate-950 text-white hidden lg:block">
        <div class="p-6 border-b border-slate-800">
            <h1 class="text-2xl font-black">Surveyor AI</h1>
            <p class="text-slate-400 text-sm mt-1">Site Score & Expansion</p>
        </div>

        <nav class="p-4 space-y-2">
            <a href="{{ route('master.surveyor.index') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 transition">
                <span>📊</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('master.surveyor.create') }}"
               class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 transition">
                <span>➕</span>
                <span>Input Survey</span>
            </a>

            @if(Route::has('analyze.location'))
                <a href="{{ route('analyze.location') }}"
                   class="flex items-center gap-3 px-4 py-3 rounded-2xl hover:bg-slate-800 transition">
                    <span>🧭</span>
                    <span>AI Location</span>
                </a>
            @endif
        </nav>
    </aside>

    <div class="flex-1">
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="px-5 lg:px-8 py-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-slate-900">@yield('page_title', 'Dashboard')</h2>
                    <p class="text-sm text-slate-500 mt-1">Sistem survey lokasi, site score, dan estimasi sales outlet.</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('master.surveyor.index') }}"
                       class="px-4 py-2 rounded-xl border border-slate-300 hover:bg-slate-100 text-sm font-semibold">
                        Dashboard
                    </a>

                    <a href="{{ route('master.surveyor.create') }}"
                       class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-sm font-semibold">
                        + Survey Baru
                    </a>
                </div>
            </div>
        </header>

        <div class="px-5 lg:px-8 pt-6">
            @if(session('success'))
                <div class="mb-5 bg-green-100 border border-green-300 text-green-800 px-5 py-4 rounded-2xl">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 bg-red-100 border border-red-300 text-red-800 px-5 py-4 rounded-2xl">
                    <div class="font-bold mb-2">Terjadi kesalahan:</div>
                    <ul class="list-disc ml-5 text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <main class="px-5 lg:px-8 py-6">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>

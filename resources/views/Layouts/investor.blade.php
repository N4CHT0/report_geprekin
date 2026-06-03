<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard')</title>

    {{-- CSS global --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/investor.css') }}" rel="stylesheet">
</head>

<body>
    {{-- HEADER --}}
    @include('Temp.Investor.header')

    {{-- CONTENT --}}
    <main class="app-main">
        <div class="app-content">
            @yield('content')
        </div>
    </main>

    {{-- JS global --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

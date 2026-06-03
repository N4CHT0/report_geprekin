<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Investor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    {{-- HEADER --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="{{ route('investor.sales.dashboard') }}">
                <i class="fas fa-chart-line me-2"></i> Investor Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
    {{-- ALERT SUCCESS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

        @if(auth()->user()->role === 'superadmin')
        {{-- DROPDOWN PILIH INVESTOR --}}
        <!--<div class="card border-0 shadow-sm rounded-4 mb-4">-->
        <!--    <div class="card-body">-->
        <!--        <form method="GET" action="{{ route('investor.sales.dashboard') }}">-->
        <!--            <div class="row align-items-center">-->
        <!--                <div class="col-md-6">-->
        <!--                    <label for="investor_id" class="form-label fw-bold">Pilih Investor:</label>-->
        <!--                    <select class="form-select" id="investor_id" name="investor_id" onchange="this.form.submit()">-->
        <!--                        <option value="">-- Pilih Investor --</option>-->
        <!--                        @foreach($investors as $inv)-->
        <!--                            <option value="{{ $inv->investor_id }}" -->
        <!--                                {{ request('investor_id') == $inv->investor_id ? 'selected' : '' }}>-->
        <!--                                {{ $inv->nama_investor }} ({{ $inv->nama_user }})-->
        <!--                            </option>-->
        <!--                        @endforeach-->
        <!--                    </select>-->
        <!--                </div>-->
        <!--            </div>-->
        <!--        </form>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-user-tie text-primary me-2"></i> {{ $investor->name ?? '-' }}
                    </h5>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-envelope me-2"></i> {{ $investor->email ?? '-' }}
                    </p>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-id-badge me-2"></i> User: {{ $investor->nama_user ?? '-' }}
                    </p>
                </div>
        
                <div class="mt-3 mt-md-0 text-end">
                    <span class="badge bg-success px-3 py-2 mb-2 d-inline-block">
                        Investor ID: {{ $investor->investor_id ?? '-' }}
                    </span>
                    <br>
                    <a href="{{ route('investor.profile.edit', $investor->investor_id) }}"
                       class="btn btn-warning btn-sm mt-2">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        @endif

        {{-- INFO INVESTOR --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-user-tie text-primary me-2"></i> {{ $investor->nama_investor ?? '-' }}
                    </h5>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-envelope me-2"></i> {{ $investor->email ?? '-' }}
                    </p>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-id-badge me-2"></i> User: {{ $investor->nama_user ?? '-' }}
                    </p>
                </div>
                <!--<div class="mt-3 mt-md-0">-->
                <!--    <span class="badge bg-success px-3 py-2">-->
                <!--        Investor ID: {{ $investor->investor_id ?? '-' }}-->
                <!--    </span>-->
                <!--</div>-->
                <div class="mt-3 mt-md-0 text-end">
                    <span class="badge bg-success px-3 py-2 mb-2 d-inline-block">
                        Investor ID: {{ $investor->investor_id ?? '-' }}
                    </span>
                    <br>
                    <a href="{{ route('investor.profile.edit', $investor->investor_id) }}"
                       class="btn btn-warning btn-sm mt-2">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        {{-- DATA OUTLET --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-store me-2"></i> Outlet Investor</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-success">
                        <tr>
                            <th>No</th>
                            <th>Nama Outlet</th>
                            <th>Alamat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outlets as $i => $outlet)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $outlet->nama_outlet ?? '-' }}</td>
                                <td>{{ $outlet->alamat ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Belum ada outlet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- FOOTER --}}
    <footer class="text-center py-3 mt-4 text-muted small">
        &copy; {{ date('Y') }} Geprekin System - All Rights Reserved
    </footer>
    
</body>
</html>

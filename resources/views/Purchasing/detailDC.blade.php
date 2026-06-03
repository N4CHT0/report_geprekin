<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Select2 dropdown scroll */
    .select2-container .select2-dropdown {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Biar table gak mepet */
    table.dataTable td,
    table.dataTable th {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    /* Kolom text panjang boleh wrap */
    .td-wrap {
        white-space: normal !important;
        word-break: break-word;
        min-width: 220px;
    }

    /* Biar tombol rapih */
    .btn-group-gap>* {
        margin-right: .35rem;
    }

    .btn-group-gap>*:last-child {
        margin-right: 0;
    }
</style>

<main class="app-main">
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/stock-control">Dashboard</a></li>
                <li class="breadcrumb-item active">Monitoring DC {{ $warehouse->nama_warehouse }}</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 p-4 h-100">
                    <h3 class="fw-bold">{{ $warehouse->nama_warehouse }}</h3>
                    <p class="text-muted"><i class="bi bi-geo-alt"></i>
                        {{ $warehouse->lokasi ?? 'Lokasi tidak tersedia' }}</p>
                    <hr>
                    <h5>Informasi Lainnya</h5>
                    <p>Status: <span class="badge bg-success">Aktif</span></p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0 p-3 bg-light h-100">
                    <h6 class="fw-bold">Aksi Cepat</h6>
                    <button class="btn btn-primary w-100 mb-2">Edit Data DC</button>
                    <button class="btn btn-outline-danger w-100">Hapus DC</button>
                </div>
            </div>

            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="fw-bold text-primary mb-3">Stok Bahan</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="stokTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Nama Bahan</th>
                                        <th class="text-center">Stok Aktual</th>
                                        <th class="text-center">Rata-rata kebutuhan</th>
                                        <th class="text-center">Safety Stock</th>
                                        <th class="text-center">Lead Time</th>
                                        <th class="text-center">ROP</th>
                                        <!-- <th class="text-center">Aksi</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $p)
                                        @php
                                            $aktual = $stokAktual[$p->id] ?? 0;
                                            $rataRata = $p->total_keluar_7_hari / 7;

                                            // Safety Stock = 20% dari Rata-rata
                                            $safetyStock = $rataRata * 0.2;

                                            // ROP = (Rata-rata * Lead Time) + Safety Stock
                                            $saranRop = ($rataRata * ($p->lead_time ?? 1)) + $safetyStock;
                                        @endphp
                                        <tr id="row-{{ $p->id }}">
                                            <form action="{{ route('update.rop.dc') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="bahan_id" value="{{ $p->id }}">

                                                <td>{{ $p->nama_bahan }}</td>
                                                <td class="text-center font-weight-bold">{{ number_format($aktual) }}</td>
                                                <td class="text-center">
                                                    {{ number_format($rataRata, 1) }}
                                                    <input type="hidden" class="avg-usage" value="{{ $rataRata }}">
                                                </td>

                                                <td class="text-center">
                                                    <span class="display-safety">{{ number_format($safetyStock, 1) }}</span>
                                                    <input type="hidden" name="safety_stock" class="input-safety-val"
                                                        value="{{ $safetyStock }}">
                                                </td>

                                                <td>
                                                    <input type="number" name="lead_time"
                                                        class="form-control form-control-sm input-lead" form="form-{{ $p->id }}"
                                                        value="{{ $p->lead_time ?? 1 }}">
                                                </td>

                                                <td class="text-center fw-bold text-primary">
                                                    <span class="display-rop">{{ ceil($saranRop) }}</span>
                                                    <input type="hidden" name="rop_level" class="input-rop-val"
                                                        value="{{ ceil($saranRop) }}">
                                                </td>

                                                <!-- <td class="text-center">
                                                    <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                                </td> -->
                                            </form>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-success mb-3">Outlet Termapping</h6>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="mapTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Outlet</th>
                                        <th class="text-start">Alamat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($outlets as $outlet)
                                        <tr>
                                            <td class="align-middle">{{ $outlet->nama_outlet }}</td>
                                            <td class="text-start align-middle">
                                                <span class="text-muted small">{{ $outlet->alamat }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted p-3">
                                                Belum ada outlet yang termapping ke DC ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
    document.querySelectorAll('.input-lead').forEach(input => {
        input.addEventListener('input', function () {
            let row = this.closest('tr');
            let avg = parseFloat(row.querySelector('.avg-usage').value) || 0;
            let safety = parseFloat(row.querySelector('.input-safety-val').value) || 0;
            let lead = parseFloat(this.value) || 0;

            // Hitung ROP
            let hasilROP = Math.ceil((avg * lead) + safety);

            // Update Tampilan
            row.querySelector('.display-rop').innerText = hasilROP;
            // Update Input Hidden di dalam form
            row.querySelector('.input-rop-val').value = hasilROP;
        });
    });
</script>

<script>
    // FIX: 2 blok $(document).ready() digabung jadi 1
    $(document).ready(function () {
        $('#stokTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });

        $('#mapTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });
</script>

@endpush
@include('Temp.Investor.footer')
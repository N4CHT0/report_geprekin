@include('Temp.Investor.header')

{{-- ✅ DataTables & SweetAlert --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* === Corporate Style === */
    main.app-main {
        background: #f4f6f9;
        min-height: 100vh;
    }

    .card {
        border-radius: 0.75rem;
    }

    #laporanDiskonTable {
        font-size: 0.9rem;
        width: 100%;
        border-collapse: collapse;
    }

    #laporanDiskonTable thead th {
        background: #eef1f4;
        border: 1px solid #dee2e6;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    #laporanDiskonTable td {
        border: 1px solid #dee2e6;
        padding: 0.5rem;
        text-align: center;
    }

    .table-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* === Responsiveness === */
    @media (max-width: 768px) {
        .btn-group {
            flex-wrap: wrap;
            gap: 6px;
        }

        .card-body form .row>div {
            flex: 0 0 100%;
            max-width: 100%;
        }
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3">

            {{-- 🔍 Filter Section --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body">
                    <form method="GET" action="{{ route('investor.laporan.diskon') }}" id="formFilter">
                        <div class="row g-3 align-items-end">
                            {{-- 🗓 Bulan & Tahun --}}
                            <div class="col-md-3">
                                <label for="bulan_tahun" class="form-label fw-semibold text-secondary">Bulan &
                                    Tahun</label>
                                <input type="month" id="bulan_tahun" name="bulan_tahun" class="form-control shadow-sm"
                                    value="{{ request('bulan_tahun') }}">
                            </div>

                            {{-- 📅 Tanggal --}}
                            <div class="col-md-3">
                                <label for="tanggal" class="form-label fw-semibold text-secondary">Tanggal</label>
                                <input type="date" id="tanggal" name="tanggal" class="form-control shadow-sm"
                                    value="{{ request('tanggal') }}">
                            </div>

                            {{-- 🏪 Outlet --}}
                            <div class="col-md-3">
                                <label for="outlet" class="form-label fw-semibold text-secondary">Outlet</label>
                                <select id="outlet" name="outlet" class="form-select shadow-sm">
                                    <option value="">-- Semua Outlet --</option>
                                    @foreach ($outlets as $outlet)
                                        <option value="{{ $outlet->nama_outlet }}"
                                            {{ request('outlet') == $outlet->nama_outlet ? 'selected' : '' }}>
                                            {{ $outlet->nama_outlet }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ⚙️ Tombol Aksi --}}
                            <div class="col-md-3 d-flex justify-content-end">
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('investor.laporan.diskon') }}" class="btn btn-secondary">
                                        <i class="fas fa-sync-alt me-1"></i> Reset
                                    </a>
                                    <button type="button" class="btn btn-success" id="btnExcel">
                                        <i class="fas fa-file-excel me-1"></i> Excel
                                    </button>
                                    <button type="button" class="btn btn-danger" id="btnPDF">
                                        <i class="fas fa-file-pdf me-1"></i> PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 📊 Data Table --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pb-0">
                    <h6 class="fw-semibold text-primary mb-0">
                        <i class="bi bi-percent me-2"></i> Laporan Diskon Penjualan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table id="laporanDiskonTable" class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Outlet</th>
                                    <th>Item</th>
                                    <th>Jumlah</th>
                                    <th>Sub Total</th>
                                    <th>Diskon</th>
                                    <th>Net Sales</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->nama_outlet ?? '-' }}</td>
                                        <td>PAKET GEPREK JUMBO</td> {{-- tetap tampilkan nama item tetap --}}
                                        <td>{{ number_format($row->total_jumlah ?? 0, 0, ',', '.') }}</td>
                                        <td>{{ number_format($row->total_sub_total ?? 0, 0, ',', '.') }}</td>
                                        <td>{{ number_format($row->total_diskon ?? 0, 0, ',', '.') }}</td>
                                        <td class="fw-bold text-success">
                                            {{ number_format($row->total_net_sales ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ⚙️ Scripts --}}
            <script>
                $(document).ready(function() {
                    // Select2 for Outlet
                    $('#outlet').select2({
                        placeholder: "-- Semua Outlet --",
                        allowClear: true,
                        width: '100%'
                    });

                    // Datatable Init
                    $('#laporanDiskonTable').DataTable({
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        lengthChange: true,
                        pageLength: 10,
                        autoWidth: false,
                        language: {
                            emptyTable: "Tidak ada data ditemukan",
                            zeroRecords: "Data tidak sesuai filter"
                        }
                    });

                    // SweetAlert for PDF & Excel
                    $('#btnPDF').on('click', function() {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sedang Maintenance',
                            text: 'Fitur cetak PDF sedang dalam perbaikan.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6'
                        });
                    });

                    $('#btnExcel').on('click', function() {
                        Swal.fire({
                            icon: 'info',
                            title: 'Sedang Maintenance',
                            text: 'Fitur export Excel sedang dalam perbaikan.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#28a745'
                        });
                    });
                });
            </script>

        </div>
    </div>
</main>

@include('Temp.Investor.footer')

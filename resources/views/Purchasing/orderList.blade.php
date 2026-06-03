@include('Temp.Investor.header')

<style>
    .chart-fixed-260 {
        height: 260px !important;
        max-height: 260px !important;
        overflow: hidden;
    }

    .chart-fixed-300 {
        height: 300px !important;
        max-height: 300px !important;
        overflow: hidden;
    }

    .chart-container canvas {
        width: 100% !important;
        height: 100% !important;
        display: block;
    }

    body {
        background-color: #f5f6f8;
        font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        color: #2b2b2b;
    }

    .card {
        transition: transform .18s ease, box-shadow .18s ease;
        border-radius: .6rem;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(16, 24, 40, 0.06);
    }

    .form-label {
        margin-bottom: .25rem;
        font-size: .85rem;
    }

    .table thead th {
        font-size: .85rem;
    }

    .table td {
        font-size: .9rem;
        vertical-align: middle;
    }

    .chart-container {
        position: relative;
        width: 100%;
    }

    .select2-container {
        width: 100% !important;
    }

    /* FIX: theme yang dipakai adalah bootstrap4 (karena kita load select2-bootstrap4-theme) */
    .select2-container .select2-selection--single {
        height: calc(1.8125rem + 2px) !important;
        padding: 0.25rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        display: flex;
        align-items: center;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        color: #212529;
        font-size: .875rem;
        line-height: 1.2;
    }

    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        top: 50%;
        transform: translateY(-50%);
        right: 0.75rem;
    }

    @media (max-width: 991px) {
        .card-body {
            padding: .9rem;
        }

        h4 {
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .card {
            border-radius: .5rem;
        }
    }

    /* ===================== LAZY LOAD UI (TAMBAHAN) ===================== */
    .loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(245, 246, 248, .65);
        backdrop-filter: blur(2px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 50;
        border-radius: .6rem;
    }

    .loading-overlay.show {
        display: flex;
    }

    .loading-pill {
        display: inline-flex;
        align-items: center;
        gap: .6rem;
        padding: .55rem .9rem;
        background: #fff;
        border: 1px solid rgba(13, 110, 253, .15);
        box-shadow: 0 12px 30px rgba(16, 24, 40, .08);
        border-radius: 999px;
        font-weight: 600;
        color: #0d6efd;
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        opacity: .25;
        animation: pulse 1.1s infinite ease-in-out;
    }

    .dot:nth-child(2) {
        animation-delay: .15s
    }

    .dot:nth-child(3) {
        animation-delay: .3s
    }

    @keyframes pulse {

        0%,
        100% {
            transform: translateY(0);
            opacity: .25
        }

        50% {
            transform: translateY(-3px);
            opacity: .9
        }
    }

    .skeleton {
        border-radius: .6rem;
        background: linear-gradient(90deg, rgba(0, 0, 0, .04), rgba(0, 0, 0, .08), rgba(0, 0, 0, .04));
        background-size: 200% 100%;
        animation: shimmer 1.05s infinite;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0
        }

        100% {
            background-position: -200% 0
        }
    }

    .skel-card {
        height: 110px;
    }

    .skel-chart {
        height: 320px;
    }

    .skel-row {
        height: 16px;
        border-radius: 10px;
    }

    .is-loading-filter {
        pointer-events: none;
        opacity: .85;
        filter: saturate(.9);
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">

            {{-- DEPENDENCIES --}}
            {{-- FIX UTAMA BUG:
                 - Samakan Bootstrap CSS & JS versi 4 (karena AdminLTE umumnya BS4)
                 - Select2 theme disamakan ke bootstrap4
            --}}
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
            <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet">
            <link href="adminlte.css" rel="stylesheet">

            <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
            <script src="adminlte.js"></script>

        </div>

        <div class="container">
            <h3>Penyesuaian Armada</h3>

            <div class="card mb-3">
                <div class="card-body">
                    <h5>Daftar PO Approved:</h5>
                    <ul>
                        @foreach($dataPO as $po)
                        <li>PO #{{ $po->no_po }} - {{ $po->nama_outlet }}</li>
                        @endforeach
                    </ul>
                    <p><strong>Total Muatan: {{ $totalTonaseKg }} Kg</strong></p>
                </div>
            </div>

            <table class="table mb-4">
                <thead>
                    <tr>
                        <th>No PO</th>
                        <th>Outlet</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataPO as $po)
                    <tr>
                        <td>{{ $po->no_po }}</td>
                        <td>{{ $po->nama_outlet }}</td>
                        <td>{{ $po->status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <form action="{{ route('admin.simpan-pengiriman') }}" method="POST">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $warehouseId }}">

                <div class="row">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th>No PO</th>
                                <th>Outlet</th>
                                <th>Tonase (Kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataPO as $po)
                            <tr>
                                <td>
                                    <input type="checkbox" class="po-checkbox" name="po_ids[]"
                                        value="{{ $po->id }}"
                                        data-tonase="{{ $po->total_kg }}">
                                </td>
                                <td>{{ $po->no_po }}</td>
                                <td>{{ $po->nama_outlet }}</td>
                                <td>{{ number_format($po->total_kg, 2, ',', '.') }} Kg</td>
                            </tr>
                            @endforeach

                            <div class="mt-3">
                                <h5>Total Tonase Terpilih: <span id="display-total">0,00</span> Kg</h5>
                            </div>
                        </tbody>
                    </table>
                </div>
                <div class="card p-3 mt-3">
                    <h5>Pilih Armada:</h5>
                    <select name="armada_id" class="form-control" required>
                        @foreach($listArmada as $armada)
                        <option value="{{ $armada->id }}">
                            {{ $armada->nama_armada }} (Kapasitas: {{ $armada->kapasitas_kg }} Kg)
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-success mt-3">Proses Pengiriman</button>
                </div>
            </form>
        </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.po-checkbox');
        const displayTotal = document.getElementById('display-total');

        function updateSubtotal() {
            let total = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    // Ambil nilai dari data-tonase, konversi ke float
                    total += parseFloat(cb.getAttribute('data-tonase')) || 0;
                }
            });

            // Update tampilan ke format Indonesia
            displayTotal.innerText = total.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Event Listener untuk setiap checkbox
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSubtotal);
        });

        // Opsional: Handle "Check All"
        document.getElementById('checkAll').addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateSubtotal();
        });
    });
</script>

@include('Temp.Investor.footer')
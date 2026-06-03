@include('Temp.Investor.header')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

<style>
    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    #table-profit-loss {
        width: 100% !important;
        border-collapse: collapse;
    }

    #table-profit-loss th,
    #table-profit-loss td {
        white-space: nowrap;
        vertical-align: middle;
    }

    #table-profit-loss th.sticky-col,
    #table-profit-loss td.sticky-col {
        position: sticky;
        left: 0;
        background: #fff;
        z-index: 2;
    }

    #table-profit-loss thead th.sticky-col {
        z-index: 3;
        background: #f8f9fa;
    }

    #table-profit-loss tbody tr.total-row td,
    #table-profit-loss tbody tr.npm-row td {
        font-weight: bold;
    }
</style>

<div class="container-fluid mt-3">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Laporan Profit & Loss OKNHO</h4>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('investor.laporan.profitnloss.oknho') }}" class="row mb-3">
                <div class="col-md-3">
                    <label for="start_date">Tanggal Mulai</label>
                    <input
                        type="date"
                        name="start_date"
                        id="start_date"
                        class="form-control"
                        value="{{ $startDate }}"
                        max="{{ now()->format('Y-m-d') }}"
                    >
                </div>

                <div class="col-md-3">
                    <label for="end_date">Tanggal Akhir</label>
                    <input
                        type="date"
                        name="end_date"
                        id="end_date"
                        class="form-control"
                        value="{{ $endDate }}"
                        max="{{ now()->format('Y-m-d') }}"
                    >
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('investor.laporan.profitnloss.oknho') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @if (!$filterApplied)
                <div class="alert alert-info">
                    Silakan pilih tanggal mulai dan tanggal akhir terlebih dahulu. Maksimal filter 7 hari.
                </div>
            @endif
            <div class="table-wrapper">
                <table id="table-profit-loss" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="sticky-col">Keterangan</th>
                            @foreach ($units as $unit)
                                <th>{{ $unit }}</th>
                            @endforeach
                            <th>Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php
                                $grandTotal = array_sum($row['values']);
                            @endphp
                    
                            <tr class="{{ $row['keterangan'] == 'Laba (rugi) Bersih' ? 'total-row' : '' }} {{ $row['keterangan'] == 'NPM' ? 'npm-row' : '' }}">
                                <td class="sticky-col">{{ $row['keterangan'] }}</td>
                    
                                @foreach ($row['values'] as $value)
                                    <td class="text-end">
                                        @if (!$filterApplied)
                                            -
                                        @else
                                            @if (!empty($row['is_percent']))
                                                {{ number_format($value, 2) }}%
                                            @else
                                                {{ number_format($value, 0, ',', '.') }}
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                    
                                <td class="text-end">
                                    @if (!$filterApplied)
                                        -
                                    @else
                                        @if (!empty($row['is_percent']))
                                            {{ number_format($grandTotal / max(count($row['values']), 1), 2) }}%
                                        @else
                                            {{ number_format($grandTotal, 0, ',', '.') }}
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#table-profit-loss').DataTable({
            scrollX: true,
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            autoWidth: false,
            language: {
                emptyTable: "Data tidak tersedia"
            }
        });
    });
</script>

@include('Temp.Investor.footer')
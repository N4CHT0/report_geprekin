@include('Temp.Investor.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Transfer #{{ $transfer->transfer_num }}</h5>
                        <span class="badge bg-white text-primary fw-bold">{{ strtoupper($transfer->status_api) }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center align-items-center">
                        <div class="col-5">
                            <p class="text-muted mb-1 small">Origin Location</p>
                            <h5 class="fw-bold"><i class="fas fa-warehouse me-2"></i>{{ $transfer->origin_location_id }}</h5>
                        </div>
                        <div class="col-2">
                            <i class="fas fa-long-arrow-alt-right fa-2x text-muted"></i>
                        </div>
                        <div class="col-5">
                            <p class="text-muted mb-1 small">Destination Location</p>
                            <h5 class="fw-bold"><i class="fas fa-store me-2"></i>{{ $transfer->destination_location_id }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-0 text-center">
                    <table class="table mb-0">
                        <thead>
                            <tr class="text-muted small text-uppercase">
                                <th>Item Code</th>
                                <th>Qty Transfer</th>
                                <th>Unit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $item)
                            <tr>
                                <td><strong>{{ $item->bahan_id }}</strong></td>
                                <td>{{ number_format($item->qty, 2) }}</td>
                                <td>{{ $item->unit_id }}</td>
                                <td><span class="text-success small"><i class="fas fa-check-circle"></i> Ready</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

@include('Temp.Investor.footer')
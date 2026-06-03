@include('Temp.Investor.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Pembelian <small class="text-muted ms-2">#{{ $purchase->purchase_num }}</small></h4>
                <a href="{{ route('staging.purchase') }}" class="btn btn-outline-secondary btn-sm">Tutup</a>
            </div>

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 border-end">
                            <label class="small text-muted d-block">Supplier (DC)</label>
                            {{-- Tampilkan Nama Supplier, kalau tidak ada tampilkan ID --}}
                            <h6 class="fw-bold text-primary">{{ $purchase->supplier_name ?? 'ID: ' . $purchase->supplier_id }}</h6>
                        </div>
                        <div class="col-md-3 border-end">
                            <label class="small text-muted d-block">Total Pembelian</label>
                            <h6 class="fw-bold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</h6>
                        </div>
                        <div class="col-md-3 border-end">
                            <label class="small text-muted d-block">Ref Receive ID</label>
                            <h6>{{ $purchase->receive_id }}</h6>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted d-block">Status Sinkronisasi</label>
                            <span class="badge {{ $purchase->status_api == 'success' ? 'bg-success' : 'bg-warning' }}">
                                {{ $purchase->status_api }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Item ID</th>
                                <th>Unit ID</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $item)
                            <tr>
                                <td class="ps-3">{{ $item->bahan_id }}</td>
                                <td>{{ $item->unit_id }}</td>
                                <td class="text-center">{{ number_format($item->qty, 0) }}</td>
                                <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="text-end pe-3 fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
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
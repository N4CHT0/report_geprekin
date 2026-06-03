@include('Temp.Investor.header')

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Detail Penjualan</h4>
                    <span class="text-muted">No. Sales: {{ $sales->sales_num }}</span>
                </div>
                <a href="{{ route('staging.sales') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="row">
                <!-- Info Header -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white fw-bold border-bottom-0 pt-3">Informasi Transaksi</div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Status</td>
                                    <td>:
                                        <span class="badge {{ $sales->status_api == 'success' ? 'bg-success' : 'bg-warning' }}">
                                            {{ strtoupper($sales->status_api) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tanggal</td>
                                    <td>: {{ date('d M Y H:i', strtotime($sales->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Cabang / Branch</td>
                                    <td>: {{ $sales->nama_outlet ?? $sales->branch_id }}</td>
                                    {{-- Tampilkan nama, jika kosong tampilkan ID-nya --}}
                                </tr>
                                <tr>
                                    <td class="text-muted">Pelanggan</td>
                                    <td>: {{ $sales->customerName ?? $sales->customer_id }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Ref ID</td>
                                    <td>: <code>{{ $sales->receive_id }}</code></td>
                                </tr>
                                <tr class="border-top">
                                    <td class="fw-bold pt-2">Total Amount</td>
                                    <td class="fw-bold pt-2 text-primary">: Rp {{ number_format($sales->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Info Items -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-white fw-bold border-bottom-0 pt-3">Daftar Item Barang</div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-3">Bahan ID</th>
                                            <th>ESB Prod ID</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Harga</th>
                                            <th class="text-end pe-3">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($details as $item)
                                        <tr>
                                            <td class="ps-3">
                                                <span class="fw-bold">{{ $item->nama_bahan ?? 'Unknown Item' }}</span><br>
                                                <small class="text-muted">ID: {{ $item->bahan_id }}</small>
                                            </td>
                                            <td><span class="badge bg-light text-dark border">{{ $item->esb_product_id }}</span></td>
                                            <td class="text-center">{{ number_format($item->qty, 2) }}</td>
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
            </div>
        </div>
    </div>
</main>

@include('Temp.Investor.footer')
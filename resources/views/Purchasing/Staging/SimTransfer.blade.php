@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-end mb-4 bg-white p-3 shadow-sm" style="border-radius: 12px;">
                <div>
                    <h4 class="fw-bold mb-1" style="color: #2c3e50;">Simple Transfer List</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 mb-0 small">
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Inventory</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Simple Transfer</li>
                        </ol>
                    </nav>
                </div>

                <div class="d-flex gap-2">
                    <button onclick="showSyncModal()" class="btn btn-outline-success">
                        <i class="fas fa-sync-alt me-1"></i> Sync Transfer
                    </button>
                    <a href="{{ route('simple-transfer.create') }}" class="btn btn-primary shadow-sm d-flex align-items-center px-4">
                        <i class="fas fa-plus me-1"></i> Create Transfer
                    </a>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white py-4 px-4 border-bottom-0">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exchange-alt me-2"></i> Staging Transfer Transactions
                    </h5>
                </div>

                <div class="card-body px-4 pb-4 pt-0">
                    <div class="table-responsive p-0">
                        <table class="table table-hover text-nowrap" id="transferTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>No. Transfer</th>
                                    <th>Ref ID (Receive)</th>
                                    <th>Origin ID</th>
                                    <th>Dest ID</th>
                                    <th>Status API</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $key => $item)
                                <tr>
                                    <td>{{ $transfers->firstItem() + $key }}</td>
                                    <td>{{ date('d/m/Y H:i', strtotime($item->created_at)) }}</td>
                                    <td><code>{{ $item->transfer_num }}</code></td>
                                    <td><span class="badge bg-secondary">{{ $item->receive_id }}</span></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-sign-out-alt text-danger me-1"></i> {{ $item->origin_location_id }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-sign-in-alt text-success me-1"></i> {{ $item->destination_location_id }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->status_api == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($item->status_api == 'success')
                                            <span class="badge bg-success">Success</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('staging.transfer.detail', $item->id) }}" class="btn btn-sm btn-info text-white">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($item->status_api != 'success')
                                            <button class="btn btn-sm btn-primary" onclick="pushTransfer('{{ $item->id }}')" title="Push to ESB">
                                                <i class="bi bi-send"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="fas fa-truck-loading fa-3x mb-3 d-block text-light"></i>
                                        Data transfer tidak ditemukan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $transfers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .badge {
        font-size: 11px;
        padding: 6px 12px;
        border-radius: 50px;
    }
    #transferTable th {
        background-color: #f8f9fc;
        color: #4e73df;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }
    #transferTable tbody tr {
        transition: all 0.2s;
    }
    #transferTable tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }
</style>

<script>
    function pushTransfer(id) {
        Swal.fire({
            title: 'Push Transfer?',
            text: "Data akan dikirim ke sistem ESB pusat.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            confirmButtonText: 'Ya, Push Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Integrasi push logic
                Swal.fire('Diproses!', 'Data transfer sedang dalam antrean push.', 'success');
            }
        })
    }
</script>

@include('Temp.Investor.footer')
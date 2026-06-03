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
                            <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Purchasing</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Simple Transfer</li>
                        </ol>
                    </nav>
                </div>

                <div class="d-flex gap-2">
                    <button onclick="showSyncModal()" class="btn btn-outline-success">
                        <i class="fas fa-sync-alt me-1"></i> Sync Transfer
                    </button>
                    <a href="#" class="btn btn-success shadow-sm d-flex align-items-center px-4">
                        <i class="fas fa-file-import me-1"></i> Upload
                    </a>
                    <a href="{{ route('simple-transfer.create') }}" class="btn btn-primary shadow-sm d-flex align-items-center px-4">
                        <i class="fas fa-plus me-1"></i> Create
                    </a>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white py-4 px-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-exchange-alt me-2"></i> Antrean Sinkronisasi Simple Transfer (ESB)
                    </h5>
                    <button class="btn btn-primary btn-sm shadow-sm" id="btnBulkPush">
                        <i class="fas fa-paper-plane me-1"></i> Push Terpilih ke ESB
                    </button>
                </div>

                <div class="card-body px-4 pb-4 pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap" id="transferTable">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">
                                        <input type="checkbox" id="checkAll" class="form-check-input">
                                    </th>
                                    <th>Tgl Terima</th>
                                    <th>No. PO / Ref</th>
                                    <th>Asal (Text)</th>
                                    <th>Tujuan (Text)</th>
                                    <th>Status Sync</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stagingData as $row)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $row->id }}" class="form-check-input row-checkbox">
                                    </td>
                                    <td>{{ date('d/m/Y', strtotime($row->transfer_date)) }}</td>
                                    <td><code>{{ $row->additional_info }}</code></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-sign-out-alt text-danger me-1"></i> {{ $row->origin_name }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-sign-in-alt text-success me-1"></i> {{ $row->destination_name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($row->status_sinkron == 'waiting')
                                            <span class="badge bg-warning text-dark">Waiting</span>
                                        @elseif($row->status_sinkron == 'success')
                                            <span class="badge bg-success">Synced</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="/scm/staging/detail/{{ $row->id }}" class="btn btn-sm btn-info text-white" title="Review">
                                                <i class="fas fa-eye"></i> Review
                                            </a>
                                            @if($row->status_sinkron != 'success')
                                            <button class="btn btn-sm btn-primary btn-push" data-id="{{ $row->id }}">
                                                <i class="fas fa-upload"></i> Push
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-truck-loading fa-3x mb-3 d-block text-light"></i>
                                        Data antrean transfer tidak ditemukan.
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
        transform: scale(1.001);
    }
    .form-check-input:checked {
        background-color: #4e73df;
        border-color: #4e73df;
    }
</style>

<script>
    // Script untuk Check All
    document.getElementById('checkAll').addEventListener('change', function() {
        let checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Script untuk SweetAlert Push
    $('.btn-push').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Push ke ESB?',
            text: "Data akan segera dikirim ke sistem pusat.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            confirmButtonText: 'Ya, Push!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Tambahkan logic AJAX push di sini
                Swal.fire('Berhasil!', 'Data sedang diproses oleh antrean.', 'success');
            }
        });
    });
</script>

@include('Temp.Investor.footer')
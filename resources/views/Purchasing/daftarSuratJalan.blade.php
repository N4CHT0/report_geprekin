{{-- resources/views/Purchasing/daftarSuratJalan.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { padding: 20px 25px !important; }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { padding: 20px 25px !important; }
    table.dataTable { width: 100% !important; margin: 0 !important; border-collapse: collapse !important; }

    #orderTable thead th {
        background-color: #f8f9fa !important;
        padding: 14px 20px !important;
        border-bottom: 1px solid #f1f4f8 !important;
        font-size: 11px; font-weight: 700;
        text-transform: uppercase; color: #2c3e50; text-align: left !important;
    }
    #orderTable tbody td {
        padding: 1rem 20px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f8f9fa !important;
    }
    #orderTable tbody tr:hover {
        background-color: rgba(105,108,255,0.04) !important;
        box-shadow: inset 4px 0 0 #696cff;
    }
    .icon-shape {
        width: 34px; height: 34px;
        display: inline-flex; align-items: center;
        justify-content: center; border-radius: 8px;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            {{-- Breadcrumb --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
                <div class="card-body p-3 d-flex flex-wrap align-items-center">
                    <div class="me-auto">
                        <h5 class="fw-bold mb-0" style="color:#2c3e50;font-size:1.1rem;">Dashboard SCM</h5>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size:11px;">
                                <li class="breadcrumb-item">
                                    <a href="/dashboard-scm" class="text-decoration-none text-muted">Purchasing</a>
                                </li>
                                <li class="breadcrumb-item active" style="color:#696cff;">Daftar Surat Jalan</li>
                            </ol>
                        </nav>
                    </div>
                    <span class="badge px-3 py-2 fw-semibold" style="background:#f0f0ff;color:#696cff;border-radius:8px;">
                        <i class="bi bi-calendar3 me-1"></i> {{ date('d M Y') }}
                    </span>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="card border-0 shadow-sm" style="border-radius:15px;overflow:hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-box-seam me-2" style="color:#696cff;"></i>
                        Surat Jalan & Packing List
                    </h6>
                </div>

                <div class="card-body px-0 pb-4 pt-0">
                    <div class="table-responsive">
                        <table id="orderTable" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width:50px;" class="text-center">No</th>
                                    <th>No. Surat Jalan</th>
                                    <th>Tipe</th>
                                    <th>Driver / Armada</th>
                                    <th>Outlet Tujuan</th>
                                    <th>Jml PO</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width:80px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($listSJ as $index => $sj)
                                <tr>
                                    <td class="text-center text-muted small">{{ $index + 1 }}</td>

                                    {{-- No SJ --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="icon-shape bg-light" style="color:#696cff;min-width:34px;">
                                                <i class="bi bi-file-earmark-text"></i>
                                            </div>
                                            <span class="badge fw-bold text-uppercase px-2 py-1"
                                                  style="background:#f0f0ff;color:#696cff;border:1px solid #e1e1ff;font-size:0.75rem;border-radius:6px;">
                                                {{ $sj->no_sj }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Tipe --}}
                                    <td>
                                        @if($sj->tipe_sj === 'GUDANG')
                                            <span class="badge px-2 py-1"
                                                  style="background:#e3f2fd;color:#1565c0;border:1px solid #bbdefb;font-size:11px;">
                                                <i class="bi bi-building me-1"></i>DC / Gudang
                                            </span>
                                        @else
                                            <span class="badge px-2 py-1"
                                                  style="background:#fff8e1;color:#e65100;border:1px solid #ffe082;font-size:11px;">
                                                <i class="bi bi-shop me-1"></i>Supplier
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Driver / Armada --}}
                                    <td>
                                        <div class="fw-semibold text-dark" style="font-size:13px;">
                                            {{ $sj->driver_name }}
                                        </div>
                                        <span class="badge bg-light text-secondary border px-2 py-1 mt-1"
                                              style="font-size:11px;border-radius:6px;">
                                            <i class="bi bi-truck me-1" style="color:#696cff;"></i>
                                            {{ $sj->armada_nopol }}
                                        </span>
                                    </td>

                                    {{-- Outlet Tujuan --}}
                                    <td>
                                        <div class="small text-muted">
                                            @if($sj->outlet_names)
                                                @foreach(explode(',', $sj->outlet_names) as $outlet)
                                                    <span class="badge bg-light text-dark border mb-1" style="font-size:11px;">
                                                        {{ trim($outlet) }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Jumlah PO --}}
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3"
                                              style="background:#f0f0ff;color:#696cff;font-size:12px;">
                                            {{ $sj->jumlah_po ?? 0 }} PO
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @php
                                            $statusMap = [
                                                'Packing'    => ['bg'=>'#fff8e1','color'=>'#e65100','icon'=>'bi-box'],
                                                'In Transit' => ['bg'=>'#e3f2fd','color'=>'#1565c0','icon'=>'bi-truck'],
                                                'Delivered'  => ['bg'=>'#e8f5e9','color'=>'#2e7d32','icon'=>'bi-check-circle'],
                                                'Cancelled'  => ['bg'=>'#ffebee','color'=>'#c62828','icon'=>'bi-x-circle'],
                                            ];
                                            $s = $statusMap[$sj->status] ?? ['bg'=>'#f5f5f5','color'=>'#666','icon'=>'bi-circle'];
                                        @endphp
                                        <span class="badge px-2 py-1"
                                              style="background:{{ $s['bg'] }};color:{{ $s['color'] }};border:1px solid {{ $s['color'] }}33;font-size:11px;">
                                            <i class="bi {{ $s['icon'] }} me-1"></i>
                                            {{ $sj->status ?? 'Packing' }}
                                        </span>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button"
                                                    data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2"
                                                style="border-radius:10px;">
                                                <li>
                                                    <a class="dropdown-item rounded-2"
                                                       href="{{ route('scm.print-sj', $sj->id) }}" target="_blank">
                                                        <i class="bi bi-printer text-info me-2"></i> Cetak Surat Jalan
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item rounded-2"
                                                       href="{{ route('scm.print-pack-list', $sj->id) }}" target="_blank">
                                                        <i class="bi bi-clipboard-check text-warning me-2"></i> Packing List
                                                    </a>
                                                </li>
                                                @if(($sj->status ?? 'Packing') === 'Packing')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item rounded-2 text-danger"
                                                            onclick="confirmCancelSJ({{ $sj->id }}, '{{ $sj->no_sj }}')">
                                                        <i class="bi bi-x-circle me-2"></i> Batalkan SJ
                                                    </button>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@push('scripts')
<script>
$(document).ready(function() {
    if ($.fn.DataTable.isDataTable('#orderTable')) {
        $('#orderTable').DataTable().destroy();
    }
    $('#orderTable').DataTable({
        responsive: true,
        autoWidth: false,
        dom: '<"d-flex justify-content-between align-items-center px-3 py-2"lf>rt<"d-flex justify-content-between align-items-center px-3 py-2"ip>',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari surat jalan...",
            lengthMenu: "_MENU_",
        },
        columnDefs: [{ targets: [0, 7], orderable: false }]
    });
});

function confirmCancelSJ(id, noSj) {
    Swal.fire({
        title: `Batalkan ${noSj}?`,
        text: 'SJ yang dibatalkan tidak bisa dikembalikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Ya, batalkan!',
        cancelButtonText: 'Tidak',
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: `/scm/surat-jalan/${id}/cancel`,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: res => Swal.fire('Dibatalkan', res.message, 'success').then(() => location.reload()),
            error: xhr => Swal.fire('Gagal', xhr.responseJSON?.message ?? 'Error', 'error'),
        });
    });
}
</script>
@endpush

@include('Temp.Investor.footer')
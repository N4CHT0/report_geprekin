{{-- resources/views/scm/outlet_receiving.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* =========================================
       SNEAT/ENTERPRISE THEME ADAPTATION
       ========================================= */
    body { background-color: #f5f5f9; }

    /* DataTables & Table Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { padding: 20px 25px !important; font-size: 0.85rem; }
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate { padding: 20px 25px !important; font-size: 0.85rem; }
    .dataTables_wrapper .dataTables_filter input { border: 1px solid #d9dee3; border-radius: 6px; padding: 0.3rem 0.75rem; margin-left: 0.5rem; outline: none; }
    .dataTables_wrapper .dataTables_filter input:focus { border-color: #696cff; box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1); }
    
    table.dataTable { border-collapse: collapse !important; width: 100% !important; margin: 0 !important; }
    .table-modern thead th { background-color: #f8f9fa !important; padding: 15px 25px !important; border-bottom: 1px solid #f1f4f8 !important; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #566a7f; letter-spacing: 1px; }
    .table-modern tbody td { padding: 1rem 25px !important; vertical-align: middle; border-bottom: 1px solid #f8f9fa; color: #697a8d; font-size: 0.85rem; }
    .table-modern tbody tr { transition: all 0.2s ease-in-out; background-color: #fff; }
    .table-modern tbody tr:hover { background-color: rgba(105, 108, 255, 0.04); box-shadow: inset 4px 0 0 #696cff; }

    /* Image Proof Styling */
    .proof-img-container {
        border: 1px dashed #d9dee3;
        border-radius: 8px;
        padding: 4px;
        background: #f8f9fa;
        height: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .proof-img-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 4px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .proof-img-container img:hover {
        transform: scale(1.02);
    }
    .proof-title {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        color: #566a7f;
        margin-bottom: 8px;
        text-align: center;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            {{-- ═══ HEADER ═══ --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.15rem;">Laporan Penerimaan Outlet (POD)</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Supply Chain</a></li>
                                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted">Tracking</a></li>
                                    <li class="breadcrumb-item active fw-bold" style="color: #696cff;" aria-current="page">Outlet Receiving</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="d-flex gap-2 align-items-center mt-3 mt-md-0">
                            <button class="btn btn-outline-secondary btn-sm px-3 py-2 d-flex align-items-center" style="border-radius: 8px;">
                                <i class="bi bi-download me-1"></i> Export Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ MAIN TABLE ═══ --}}
            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <h6 class="m-0 fw-bold text-dark">
                        <i class="bi bi-clipboard-check me-2" style="color: #696cff; font-size: 1.2rem; vertical-align: middle;"></i> 
                        Daftar Log Penerimaan Barang
                    </h6>
                </div>
                <div class="table-responsive">
                    <table id="grTable" class="table table-modern">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th>No. PO</th>
                                <th>Outlet Penerima</th>
                                <th>Waktu Terima</th>
                                <th class="text-center" style="width: 15%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receives as $index => $row)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold" style="color: #696cff;">{{ $row->no_po }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $row->nama_outlet }}</div>
                                </td>
                                <td>
                                    <i class="bi bi-calendar-event me-1 text-muted"></i> 
                                    {{ \Carbon\Carbon::parse($row->tgl_terima)->format('d M Y H:i') }}
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-light text-primary border-0 btn-view-detail"
                                        style="background-color: #f0f0ff; color: #696cff !important; border-radius: 6px;"
                                        data-id="{{ $row->id }}">
                                        <i class="bi bi-search me-1"></i> Lihat Detail
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ═══ MODAL DETAIL & BUKTI FOTO ═══ --}}
            <div class="modal fade" id="modalDetailGR" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0" style="border-radius: 12px; box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);">
                        <div class="modal-header border-bottom-0 pt-4 px-4 pb-2 bg-white">
                            <h5 class="modal-title fw-bold" style="color: #2c3e50;">
                                <i class="bi bi-card-checklist me-2 text-primary"></i> 
                                Rincian Penerimaan: <span id="detPoNumber" style="color: #696cff;">Memuat...</span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        
                        <div class="modal-body px-4 pb-4">
                            
                            {{-- Info Outlet --}}
                            <div class="mb-3">
                                <span class="badge bg-label-secondary text-dark border">
                                    <i class="bi bi-shop me-1 text-muted"></i> <span id="detOutlet">Memuat...</span>
                                </span>
                            </div>

                            {{-- Tab Navigation --}}
                            <ul class="nav nav-tabs nav-fill mb-3" id="grTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold" id="item-tab" data-bs-toggle="tab" data-bs-target="#tab-item" type="button" role="tab">
                                        <i class="bi bi-box-seam me-1"></i> Rincian Barang
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold" id="foto-tab" data-bs-toggle="tab" data-bs-target="#tab-foto" type="button" role="tab">
                                        <i class="bi bi-camera me-1"></i> Bukti Foto
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="grTabContent">
                                
                                {{-- TAB 1: Rincian Barang --}}
                                <div class="tab-pane fade show active" id="tab-item" role="tabpanel">
                                    <div class="alert alert-light border mb-3" style="font-size: 0.85rem;">
                                        <strong>Catatan Outlet:</strong> <span id="detCatatan" class="fst-italic">Memuat catatan...</span>
                                    </div>
                                    <div class="table-responsive border rounded-3">
                                        <table class="table table-sm table-hover align-middle mb-0" id="detailItemsTable">
                                            <thead class="bg-light" style="font-size: 11px; text-transform: uppercase; color: #566a7f;">
                                                <tr>
                                                    <th>Bahan/Barang</th>
                                                    <th class="text-center">Qty PO</th>
                                                    <th class="text-center">Diterima</th>
                                                    <th class="text-center">Selisih/Kurang</th>
                                                    <th>Alasan Kekurangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detailItemsBody">
                                                <tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat data...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- TAB 2: Bukti Foto --}}
                                <div class="tab-pane fade" id="tab-foto" role="tabpanel">
                                    <div class="row g-3" id="proofImagesContainer">
                                        {{-- Foto Barang --}}
                                        <div class="col-md-4">
                                            <div class="proof-title">Foto Barang Fisik</div>
                                            <div class="proof-img-container">
                                                <img id="imgBarang" src="" alt="Bukti Barang" style="display: none;">
                                                <div class="text-muted small text-center" id="textBarang"><i class="bi bi-image d-block fs-3 mb-1"></i>Tidak ada foto</div>
                                            </div>
                                        </div>
                                        {{-- Foto Surat Jalan --}}
                                        <div class="col-md-4">
                                            <div class="proof-title">Surat Jalan / Resi</div>
                                            <div class="proof-img-container">
                                                <img id="imgSuratJalan" src="" alt="Bukti Surat Jalan" style="display: none;">
                                                <div class="text-muted small text-center" id="textSuratJalan"><i class="bi bi-file-earmark-text d-block fs-3 mb-1"></i>Tidak ada foto</div>
                                            </div>
                                        </div>
                                        {{-- Foto Supir --}}
                                        <div class="col-md-4">
                                            <div class="proof-title">Bukti Supir/Kurir</div>
                                            <div class="proof-img-container">
                                                <img id="imgSupir" src="" alt="Bukti Supir" style="display: none;">
                                                <div class="text-muted small text-center" id="textSupir"><i class="bi bi-person-badge d-block fs-3 mb-1"></i>Tidak ada foto</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                        <div class="modal-footer border-top-0 px-4 pb-4 bg-light" style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal" style="border-radius: 8px;">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#grTable').DataTable({
            responsive: true,
            pageLength: 10,
            dom: '<"d-flex flex-wrap justify-content-between align-items-center"lf>rt<"d-flex flex-wrap justify-content-between align-items-center"ip>',
            language: { search: "", searchPlaceholder: "Cari No PO / Outlet..." },
            columnDefs: [{ targets: [4], orderable: false }]
        });

        // Event Klik Lihat Detail (AJAX)
        $(document).on('click', '.btn-view-detail', function() {
            const receiveId = $(this).data('id');
            $('#modalDetailGR').modal('show');

            // Reset UI Modal ke mode loading
            $('#detPoNumber').text('Loading...');
            $('#detOutlet').text('Loading...');
            $('#detCatatan').text('Loading...');
            $('#detailItemsBody').html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat detail...</td></tr>');
            
            // Sembunyikan gambar lama
            ['Barang', 'SuratJalan', 'Supir'].forEach(type => {
                $('#img' + type).hide().attr('src', '');
                $('#text' + type).show();
            });

            // Pastikan URL di bawah ini sama dengan rute yang didefinisikan di web.php
            $.ajax({
                url: `/scm/outlet-receiving/detail/${receiveId}`,
                method: 'GET',
                success: function(res) {
                    if(res.status === 'success') {
                        // 1. Set Header
                        $('#detPoNumber').text(res.header.no_po);
                        $('#detOutlet').text(res.header.outlet);
                        $('#detCatatan').text(res.header.catatan || '-');

                        // 2. Set Detail Barang
                        let htmlItems = '';
                        if(res.details.length > 0) {
                            res.details.forEach(item => {
                                // Logika styling angka selisih
                                let qtyKurang = parseFloat(item.qty_kurang) || 0;
                                let selisihClass = qtyKurang > 0 ? 'text-danger fw-bold' : '';
                                let qtyTerimaClass = qtyKurang > 0 ? 'text-danger' : 'text-success';
                                let alasanHtml = item.alasan_kurang 
                                    ? `<span class="badge bg-danger bg-opacity-10 text-danger border">${item.alasan_kurang}</span>` 
                                    : '<span class="text-muted">-</span>';

                                htmlItems += `
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">${item.nama_bahan || '-'}</div>
                                            <small class="text-muted">${item.nama_unit || '-'}</small>
                                        </td>
                                        <td class="text-center">${parseFloat(item.qty_po)}</td>
                                        <td class="text-center ${qtyTerimaClass} fw-bold">${parseFloat(item.qty_terima)}</td>
                                        <td class="text-center ${selisihClass}">${qtyKurang}</td>
                                        <td>${alasanHtml}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            htmlItems = '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada rincian barang.</td></tr>';
                        }
                        $('#detailItemsBody').html(htmlItems);

                        // 3. Set Gambar Bukti (Base64)
                        if(res.images.barang) {
                            $('#imgBarang').attr('src', res.images.barang).show();
                            $('#textBarang').hide();
                        }
                        if(res.images.surat_jalan) {
                            $('#imgSuratJalan').attr('src', res.images.surat_jalan).show();
                            $('#textSuratJalan').hide();
                        }
                        if(res.images.supir) {
                            $('#imgSupir').attr('src', res.images.supir).show();
                            $('#textSupir').hide();
                        }
                    }
                },
                error: function(xhr) {
                    let errMsg = xhr.responseJSON?.message || 'Gagal memuat detail data.';
                    $('#detailItemsBody').html(`<tr><td colspan="5" class="text-center text-danger py-4">${errMsg}</td></tr>`);
                    $('#detPoNumber').text('Error');
                }
            });
        });

        // Fitur Zoom sederhana saat gambar diklik (opsional)
        $('.proof-img-container img').on('click', function() {
            let src = $(this).attr('src');
            if(src) {
                Swal.fire({
                    imageUrl: src,
                    imageAlt: 'Bukti Foto',
                    showConfirmButton: false,
                    width: 'auto',
                    padding: '1em',
                    background: 'transparent',
                    backdrop: 'rgba(0,0,0,0.85)'
                });
            }
        });
    });
</script>
@endpush

@include('Temp.Investor.footer')
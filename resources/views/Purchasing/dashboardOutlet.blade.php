{{-- resources/views/purchasing/dashboardOutlet.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Select2 dropdown scroll */
    .select2-container .select2-dropdown {
        max-height: 260px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Biar table gak mepet */
    table.dataTable td,
    table.dataTable th {
        vertical-align: middle !important;
        white-space: nowrap;
    }

    /* Kolom text panjang boleh wrap */
    .td-wrap {
        white-space: normal !important;
        word-break: break-word;
        min-width: 220px;
    }

    /* Biar tombol rapih */
    .btn-group-gap>* {
        margin-right: .35rem;
    }

    .btn-group-gap>*:last-child {
        margin-right: 0;
    }
</style>


<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-3 py-md-4">

            <div class="mb-4">
                <h4 class="fw-bold text-primary mb-1">
                    Dashboard Outlet – Authentication
                </h4>
                <p class="text-muted mb-0">
                    Ringkasan aktivitas Purchase Order outlet
                </p>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Total PO</h6>
                            <h3 class="fw-bold">{{ $total_po }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Menunggu Persetujuan</h6>
                            <h3 class="fw-bold text-warning">{{ $menunggu }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Disetujui</h6>
                            <h3 class="fw-bold text-success">{{ $disetujui }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Ditolak</h6>
                            <h3 class="fw-bold text-danger">{{ $ditolak }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL PO -->
            <form action="{{ route('po.store') }}" method="POST">
                @csrf
                <div class="modal fade" id="modalRequestPO" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-semibold">Form Request Purchase Order</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Outlet</label>
                                        <select name="outlet_id" id="outlet_id" class="form-control select2-outlet" required style="width: 100%;">
                                            <option value="">Pilih Outlet</option>
                                            @foreach($outlets as $outlet)
                                            <option value="{{ $outlet->id }}">{{ $outlet->nama_outlet }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Penanggungjawab</label>
                                        <input type="text" name="nama_pemesan" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Permintaan</label>
                                        <input type="date" name="tgl_permintaan" id="permintaanDate" value="{{ date('Y-m-d') }}" class="form-control" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Catatan</label>
                                        <textarea name="catatan" class="form-control" rows="2"></textarea>
                                    </div>

                                    <div class="col-12">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Produk</th>
                                                    <th>Qty</th>
                                                    <!-- <th>Satuan</th> -->
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="produkBody">
                                                <tr>
                                                    <td>
                                                        <select name="bahan_id[]" class="form-control select2" required>
                                                            <option value="">Pilih Bahan</option>
                                                            @foreach($bahans as $bahan)
                                                            <option value="{{ $bahan->id }}">{{ $bahan->nama_bahan }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="jumlah[]" class="form-control" required>
                                                        @foreach($bahans as $bahan)
                                                        <input type="hidden" name="unit_id[]" value="{{ $bahan->base_unit_id }}">
                                                        @endforeach

                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger removeRow">&times;</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button type="button" id="addRow" class="btn btn-outline-primary btn-sm">Tambah Barang</button>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Request</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold text-primary mb-0">
                            <i class="bi bi-box-seam me-2"></i> Daftar Purchase Order
                        </h4>
                        <button
                            type="button"
                            class="btn btn-primary btn-lg"
                            data-bs-toggle="modal"
                            data-bs-target="#modalRequestPO">
                            <i class="bi bi-plus-circle me-2"></i>
                            Request PO
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="orderTable" class="table table-striped table-bordered align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nomor PO</th>
                                    <th>Tanggal Permintaan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataPO as $index => $po)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $po->no_po }}</td>
                                    <td>{{ $po->tgl_permintaan }}</td>
                                    <td>
                                        {{-- Warna badge otomatis berdasarkan status --}}
                                        @php
                                        $statusColors = [
                                        'Waiting' => 'bg-warning text-dark',
                                        'Approved' => 'bg-success',
                                        'Rejected' => 'bg-danger',
                                        'In Transit'=> 'bg-primary',
                                        'Recieved' => 'bg-info text-dark',
                                        'All Checked' => 'bg-success text-dark'
                                        ];
                                        // Gunakan 'bg-dark' sebagai default jika status tidak ditemukan
                                        $badgeClass = $statusColors[$po->status] ?? 'bg-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $po->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($po->status == 'In Transit' || $po->status == 'Retur Requested'|| $po->status == 'Partial Received' )
                                        <button type="button" class="btn btn-warning btn-sm btn-checking" data-id="{{ $po->id }}" data-nopo="{{ $po->no_po }}">
                                            <i class="bi bi-list-check"></i>
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-info btn-sm btn-view-po"
                                            data-id="{{ $po->id }}"
                                            data-no-po="{{ $po->no_po }}"
                                            data-status="{{ $po->status }}"
                                            data-tgl-req="{{ $po->tgl_permintaan }}"
                                            data-items="{{ json_encode($po->items) }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailModal">
                                            <i class="bi bi-eye-fill"></i>
                                        </button>
                                        @if($po->status == 'Partial Received')
                                        <button type="button" class="btn btn-warning btn-sm btn-retur"
                                            data-id="{{ $po->id }}"
                                            data-nopo="{{ $po->no_po }}">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $po->id }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="deleteForm{{ $po->id }}" action="{{ route('po.delete', $po->id) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!-- MODAL VIEW -->
                        <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title" id="detailLabel">Detail Purchase Order</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="modalContent">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" id="btn-recieved" class="btn btn-outline-info btn-update-status" data-status="Recieved">Recieved</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MODAL CHECKING -->
                        <form action="{{ route('recieve.store') }}" method="POST" enctype="multipart/form-data" id="formPenerimaanUtama">
                            @csrf
                            <input type="hidden" name="po_id" id="checking_po_id">
                            <input type="hidden" name="no_po" id="hidden_no_po">

                            <div class="modal fade" id="modalChecking" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Checking Barang - <span id="txt_no_po"></span></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <h6 class="fw-bold">Daftar Bahan Baku Umum</h6>
                                                <table class="table table-bordered">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>Nama Barang</th>
                                                            <th>Supplier</th>
                                                            <th>Satuan</th>
                                                            <th>Qty PO</th>
                                                            <th>Qty Terima</th>
                                                            <th>Qty Kurang</th>
                                                            <th id="th-alasan" style="display:none;">Alasan</th>
                                                            <th>Total Dasar</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bodyCheckingUmum"></tbody>
                                                </table>
                                            </div>

                                            <div id="sectionAyam" style="display:none;" class="mt-4">
                                                <h6 class="fw-bold text-danger">Penerimaan Ayam</h6>
                                                <table class="table table-bordered">
                                                    <thead class="bg-danger text-white">
                                                        <tr>
                                                            <th>Nama Bahan</th>
                                                            <th>Supplier</th>
                                                            <th>Qty PO</th>
                                                            <th>Ayam Besar</th>
                                                            <th>Ayam Kecil</th>
                                                            <th>Total Pcs</th>
                                                            <th>Pack</th>
                                                            <th>Gramase</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bodyCheckingAyam"></tbody>
                                                </table>
                                            </div>

                                            <hr>
                                            <div class="row">
                                                <div class="col-md-6 text-center">
                                                    <label class="fw-bold">Foto Barang</label>
                                                    <div class="camera-area border bg-light mb-2" style="min-height: 150px;">
                                                        <video id="video_barang" width="100%" height="150" autoplay playsinline style="display:none; object-fit: cover;"></video>
                                                        <canvas id="canvas_barang" style="display:none;"></canvas>
                                                        <img id="img_barang" src="" class="img-fluid" style="display:none; max-height: 150px;">
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="startCamera('barang')">Buka Kamera</button>
                                                    <button type="button" id="btn_snap_barang" class="btn btn-sm btn-danger" onclick="takeSnapshot('barang')" style="display:none;">Ambil Foto</button>
                                                    <input type="hidden" name="foto_barang_base64" id="input_foto_barang">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success px-5">Simpan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- MODAL RETURN -->
                        <div class="modal fade" id="modalRetur" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <form action="{{ route('return.store') }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <input type="hidden" name="po_id" id="retur_po_id">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Form Return Barang - <span id="txt_no_po_retur"></span></h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Bahan</th>
                                                            <th width="200">Jumlah Retur</th>
                                                            <th width="200">Alasan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="returTableBody">
                                                        <!-- Data diisi via JS -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-danger">Ajukan Return</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
</main>

@push('scripts')
<script>
    // 1. GLOBAL VARIABLE UNTUK KAMERA
    let currentStream = null;

    // Fungsi Buka Kamera
    function startCamera(type) {
        const video = document.getElementById('video_' + type);
        const btnSnap = document.getElementById('btn_snap_' + type);
        video.style.display = 'block';
        btnSnap.style.display = 'inline-block';

        navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: "environment"
                }
            })
            .then(stream => {
                currentStream = stream;
                video.srcObject = stream;
            })
            .catch(err => alert("Kamera tidak bisa dibuka!"));
    }

    // Fungsi Jepret
    function takeSnapshot(type) {
        const video = document.getElementById('video_' + type);
        const canvas = document.getElementById('canvas_' + type);
        const img = document.getElementById('img_' + type);
        const input = document.getElementById('input_foto_' + type);

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        // Kualitas 0.4 supaya size kecil & gak gagal store (Base64)
        const dataURL = canvas.toDataURL('image/jpeg', 0.4);
        img.src = dataURL;
        img.style.display = 'block';
        input.value = dataURL;

        // Matikan kamera
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
        video.style.display = 'none';
        document.getElementById('btn_snap_' + type).style.display = 'none';
    }

    $(document).ready(function() {
        // Saat Tombol Checking diklik
        $('.btn-checking').on('click', function() {
            let poId = $(this).data('id');
            let noPo = $(this).data('nopo');

            $('#modalChecking').modal('show');
            $('#txt_no_po').text(noPo);
            $('#hidden_no_po').val(noPo);
            $('#checking_po_id').val(poId);

            // Reset Form, Foto, dan Sembunyikan Section Ayam dulu
            $('#bodyCheckingUmum').html('<tr><td colspan="5" class="text-center">Memuat...</td></tr>');
            $('#bodyCheckingAyam').html('');
            $('#sectionAyam').hide();
            $('#input_foto_barang, #input_foto_supir').val('');
            $('#img_barang, #img_supir').hide();

            // AJAX Ambil Detail
            $.get("/dashboard-outlet/po-detail/" + poId, function(response) {
                let htmlUmum = '';
                let htmlAyam = '';
                let globalIndex = 0;

                // Jika tidak ada detail (mungkin sudah lunas semua)
                if (response.details.length === 0) {
                    $('#bodyCheckingUmum').html('<tr><td colspan="5" class="text-center text-success fw-bold">Semua barang sudah diterima sepenuhnya.</td></tr>');
                    return;
                }

                // Di dalam $.get("/dashboard-outlet/po-detail/" + poId, function(response) { ...

                response.details.forEach((item, index) => {
                    let globalIndex = index;
                    let sisa = parseFloat(item.jumlah) - parseFloat(item.total_diterima_sebelumnya);
                    if (sisa <= 0) return;

                    let isAyam = item.nama_bahan.toLowerCase().includes('ayam');
                    let konv = item.conversion_factor || 1;

                    // --- LOGIKA DROPDOWN SUPPLIER ---
                    let supplierColumn = '';
                    if (item.sumber_barang === 'supplier') {
                        supplierColumn = `<select name="items[${globalIndex}][supplier_id]" class="form-control form-control-sm border-warning" required>
                            <option value="">-- Pilih Supplier --</option>`;
                        response.available_suppliers.forEach(sup => {
                            supplierColumn += `<option value="${sup.id}">${sup.nama_supplier}</option>`;
                        });
                        supplierColumn += `</select>`;
                    } else {
                        supplierColumn = `<input type="text" class="form-control form-control-sm bg-light" value="GUDANG PUSAT" readonly>
                          <input type="hidden" name="items[${globalIndex}][supplier_id]" value="459">`;
                    }

                    if (isAyam) {
                        $('#sectionAyam').show();
                        htmlAyam += `<tr>
            <td>
                <strong>${item.nama_bahan}</strong>
                <input type="hidden" name="items[${globalIndex}][bahan_id]" value="${item.bahan_id}">
                <input type="hidden" name="items[${globalIndex}][unit_id]" value="${item.unit_id}">
            </td>
            <td>${supplierColumn}</td>
            <td class="text-center">${sisa} Ekor</td>
            <td><input type="number" name="items[${globalIndex}][qty_besar]" class="form-control qty-besar text-center" value="0"></td>
            <td><input type="number" name="items[${globalIndex}][qty_kecil]" class="form-control qty-kecil text-center" value="0"></td>
            <td><input type="number" name="items[${globalIndex}][qty_terima]" class="form-control qty-ayam-total text-center bg-light" readonly></td>
            <td><input type="number" name="items[${globalIndex}][qty_pack]" class="form-control text-center" value="0"></td> <td>
                <input type="text" class="form-control total-display text-end bg-light" readonly>
                <input type="hidden" class="konv" value="${konv}">
            </td>
        </tr>`;
                    } else {
                        htmlUmum += `<tr>
            <td>
                <strong>${item.nama_bahan}</strong>
                <input type="hidden" name="items[${globalIndex}][bahan_id]" value="${item.bahan_id}">
                <input type="hidden" name="items[${globalIndex}][unit_id]" value="${item.unit_id}">
            </td>
            <td>${supplierColumn}</td>
            <td class="text-center">${item.satuan || 'Pcs'}</td>
            <td class="text-center">${sisa} (Sisa)</td>
            <td>
                <input type="number" name="items[${globalIndex}][qty_terima]" class="form-control qty-umum text-center" value="${sisa}" step="0.01">
            </td>
            <td>
                <input type="number" name="items[${globalIndex}][qty_kurang]" class="form-control qty-kurang text-center bg-light" readonly>
            </td>
            <td>
                <input type="text" class="form-control total-display text-end bg-light" readonly>
                <input type="hidden" class="konv" value="${konv}">
            </td>
        </tr>`;
                    }
                });

                $('#bodyCheckingUmum').html(htmlUmum || '<tr><td colspan="5" class="text-center">Semua barang umum sudah diterima.</td></tr>');
                $('#bodyCheckingAyam').html(htmlAyam);
                calculateAll();
            });
        });

        // Hitung Otomatis (Tetap sama)
        $(document).on('input', '.qty-umum, .qty-besar, .qty-kecil', function() {
            let row = $(this).closest('tr');
            if (row.find('.qty-besar').length || row.find('.qty-kecil').length) {
                let b = parseFloat(row.find('.qty-besar').val()) || 0;
                let k = parseFloat(row.find('.qty-kecil').val()) || 0;
                row.find('.qty-ayam-total').val(b + k);
            }
            calculateAll();
        });

        function calculateAll() {
            $('.total-display').each(function() {
                let row = $(this).closest('tr');
                let q = parseFloat(row.find('.qty-umum').val() || row.find('.qty-ayam-total').val()) || 0;
                let k = parseFloat(row.find('.konv').val()) || 0;
                $(this).val((q * k).toLocaleString('id-ID'));
            });
        }
    });

    $(document).on('input', '.qty-umum, .qty-besar, .qty-kecil', function() {
        let row = $(this).closest('tr');

        let qty_po = parseFloat(row.find('.qty-po').val()) || 0;

        let qty_terima = 0;

        // Jika ayam
        if (row.find('.qty-besar').length || row.find('.qty-kecil').length) {
            let b = parseFloat(row.find('.qty-besar').val()) || 0;
            let k = parseFloat(row.find('.qty-kecil').val()) || 0;
            qty_terima = b + k;
            row.find('.qty-ayam-total').val(qty_terima);
        } else {
            qty_terima = parseFloat(row.find('.qty-umum').val()) || 0;
        }

        let qty_kurang = qty_po - qty_terima;

        // Set nilai
        row.find('.qty-kurang').val(qty_kurang > 0 ? qty_kurang : 0);

        // Show/hide alasan
        if (qty_kurang > 0) {
            row.find('.alasan-wrapper').show();
        } else {
            row.find('.alasan-wrapper').hide();
            row.find('select').val('');
        }

        calculateAll();
        toggleAlasanColumn();
    });

    function toggleAlasanColumn() {
        let show = false;

        $('.qty-kurang').each(function() {
            let val = parseFloat($(this).val()) || 0;
            if (val > 0) {
                show = true;
                return false; // break loop
            }
        });

        if (show) {
            $('#th-alasan').show();
            $('.alasan-wrapper').show();
        } else {
            $('#th-alasan').hide();
            $('.alasan-wrapper').hide();
            $('select[name*="alasan_kurang"]').val('');
        }
    }
</script>

<script>
    $(document).ready(function() {
        $('#orderTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 10,
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Inisialisasi Select2 di dalam Modal
        $('.select2-outlet').select2({
            dropdownParent: $('#modalRequestPO'), // PENTING: Agar dropdown muncul di atas modal
            placeholder: "Pilih Outlet",
            allowClear: true
        });
    });

    $(document).ready(function() {
        $('.select2-bahan').select2({
            placeholder: "Pilih Bahan",
            allowClear: true,
            width: '100%',
        });
    });

    $(document).on('click', '#permintaanDate', function() {
        try {
            this.showPicker(); // Cara standar browser modern
        } catch (e) {
            $(this).focus(); // Fallback kalau showPicker gagal
        }
    });
</script>

<script>
    $(document).on('click', '.btn-checking', function() {
        let poId = $(this).data('id');
        let noPo = $(this).data('nopo');

        console.log("Klik PO ID: " + poId); // Cek di console (F12) apakah muncul?

        // Panggil Modal kamu di sini
        $('#modalChecking').modal('show');

        // Contoh: Isi input di modal dengan data dari tombol
        $('#modalChecking #po_id').val(poId);
        $('#modalChecking #no_po_text').text(noPo);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const detailModal = document.getElementById('detailModal');

        detailModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            // Ambil data dasar
            const noPo = button.getAttribute('data-no-po');
            const status = button.getAttribute('data-status');
            const tglReq = button.getAttribute('data-tgl-req');

            // Ambil data barang (Parsing dari JSON string ke Object/Array)
            const items = JSON.parse(button.getAttribute('data-items') || '[]');

            // Isi modal title
            const modalTitle = detailModal.querySelector('.modal-title');
            modalTitle.textContent = 'Detail PO: ' + noPo;

            // Generate baris tabel untuk barang
            let itemRows = '';
            if (items.length > 0) {
                items.forEach((item, index) => {
                    itemRows += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${item.nama_bahan}</td>
                            <td class="text-center">${item.jumlah}</td>
                            <td>${item.satuan || '-'}</td>
                        </tr>`;
                });
            } else {
                itemRows = '<tr><td colspan="4" class="text-center">Tidak ada detail barang</td></tr>';
            }

            // Isi konten modal
            const modalBody = detailModal.querySelector('#modalContent');
            modalBody.innerHTML = `
                <h6 class="fw-bold">Informasi Pesanan</h6>
                <table class="table table-sm table-bordered mb-4">
                    <tr><th class="bg-light" width="30%">No PO</th><td>${noPo}</td></tr>
                    <tr><th class="bg-light">Tanggal Request</th><td>${tglReq}</td></tr>
                    <tr><th class="bg-light">Status</th><td><span class="badge ${status === 'Approved' ? 'bg-success' : (status === 'Rejected' ? 'bg-danger' : 'bg-primary')}">${status}</span></td></tr>
                </table>

                <h6 class="fw-bold">Daftar Pesanan</h6>
                <table class="table table-sm table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center">No.</th>
                            <th>Nama Bahan</th>
                            <th class="text-center">Qty</th>
                            <th>Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemRows}
                    </tbody>
                </table>
            `;
        });
    });
</script>

<script>
    $(document).ready(function() {
        // 1. Saat tombol View diklik, simpan ID ke modal
        $('.btn-view-po').click(function() {
            let id = $(this).data('id');
            let noPo = $(this).data('no-po');

            // Simpan ID ke attribute modal agar bisa dipakai tombol di dalam modal
            $('#detailModal').data('current-id', id);

            // Isi konten modal
            $('#modalContent').html('Detail untuk PO: ' + noPo);
        });

        // 2. Saat tombol Approved/Rejected diklik
        $('.btn-update-status').click(function() {
            let status = $(this).data('status');
            let id = $('#detailModal').data('current-id'); // Ambil ID yang disimpan tadi

            $.ajax({
                url: "{{ route('update.status.po') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    status: status
                },
                success: function(response) {
                    Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Status diubah ke ' + status
                        })
                        .then(() => {
                            location.reload();
                        });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan'
                    });
                }
            });
        });
    });
</script>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika user klik "Ya", maka form di-submit
                document.getElementById('deleteForm' + id).submit();
            }
        })
    }
</script>

<script>
    $('.btn-view-po').click(function() {
        let id = $(this).data('id');
        let status = $(this).data('status'); // Pastikan tombol View di tabel punya data-status

        // Simpan ID ke modal
        $('#detailModal').data('current-id', id);

        // LOGIKA TOMBOL:
        if (status === 'Recieved') {
            // Jika sudah diterima, kunci tombol
            $('#btn-recieved').prop('disabled', true).text('Sudah Diterima');
        } else if (status === 'All Checked') {
            // Jika sedang dikirim, tombol aktif
            $('#btn-recieved').prop('disabled', false).text('Recieved');
        } else if (status === 'In Transit') {
            // Status lainnya (misal: Pending, Packing), kunci tombol
            $('#btn-recieved').prop('disabled', true).text('Dalam Pengiriman');
        } else {
            // Status lainnya (misal: Pending, Packing), kunci tombol
            $('#btn-recieved').prop('disabled', true).text('Belum Dikirim');
        }

        // Masukkan data ke modal
        $('#modalContent').html(`
            <table class="table table-bordered">
                <tr><th>No. PO</th><td>${noPo}</td></tr>
                <tr><th>Tanggal Permintaan</th><td>${tglReq}</td></tr>
                <tr><th>Tanggal Kedatangan</th><td>${tglDatang}</td></tr>
            </table>
        `);

        // Buka modal
        var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
        myModal.show();
    });
</script>

<script>
    $(document).ready(function() {
        // Fungsi untuk inisialisasi Select2 (agar bisa dipanggil berulang)
        function initSelect2(element) {
            $(element).select2({
                width: '100%',
                dropdownParent: $('#modalTambah') // Sesuaikan dengan ID modal kamu jika di dalam modal
            });
        }

        // Inisialisasi awal
        initSelect2('.select-bahan');

        // 1. Fungsi Tambah Baris
        $('#addRow').click(function() {
            // Hancurkan dulu select2 di baris pertama sebelum di clone agar tidak error
            $('.select-bahan').select2('destroy');

            var newRow = $('#produkBody tr:first').clone();

            // Bersihkan nilai
            newRow.find('input').val('');
            newRow.find('select').val('');
            newRow.find('.satuan-span').text(''); // Kosongkan satuan di baris baru

            // Tambahkan ke tabel
            $('#produkBody').append(newRow);

            // Inisialisasi ulang semua select2 (yang lama dan yang baru)
            initSelect2('.select-bahan');
        });

        // 2. Fungsi Update Satuan (Khusus Select2)
        $(document).ready(function() {
            // Fungsi untuk update satuan saat produk dipilih
            $(document).on('select2:select', '.select-bahan', function(e) {
                // Ambil data-satuan dari elemen option yang dipilih oleh Select2
                var data = e.params.data;
                var satuan = $(data.element).data('satuan');

                // Cari kolom Satuan di baris yang sama
                var targetSpan = $(this).closest('tr').find('.satuan-display');

                if (satuan) {
                    targetSpan.text(satuan);
                } else {
                    targetSpan.text('-');
                }
            });

            // Fungsi reset satuan jika pilihan dibersihkan (Clear)
            $(document).on('select2:unselect select2:clear', '.select-bahan', function() {
                $(this).closest('tr').find('.satuan-display').text('-');
            });
        });

        // 3. Fungsi Hapus Baris
        $(document).on('click', '.removeRow', function() {
            if ($('#produkBody tr').length > 1) {
                $(this).closest('tr').remove();
            }
        });
    });
</script>

<script>
    $(document).on('click', '.btn-retur', function() {
        const poId = $(this).data('id');
        const noPo = $(this).data('nopo');

        $('#txt_no_po_retur').text(noPo);
        $('#retur_po_id').val(poId);

        // Pakai colspan="3" karena kita sepakat cuma ada 3 kolom
        $('#returTableBody').html('<tr><td colspan="3" class="text-center">Memuat...</td></tr>');
        $('#modalRetur').modal('show');

        $.ajax({
            url: '/dashboard-outlet/po-receive-detail/' + poId,
            type: 'GET',
            success: function(res) {
                let html = '';

                if (!res.details || res.details.length === 0) {
                    html = '<tr><td colspan="3" class="text-center">Tidak ada data bahan</td></tr>';
                } else {
                    res.details.forEach((item, i) => {
                        html += `
                        <tr>
                            <!-- KOLOM 1: BAHAN -->
                            <td>
                                <span class="fw-bold text-uppercase">${item.nama_bahan}</span>
                                <input type="hidden" name="returns[${i}][bahan_id]" value="${item.bahan_id}">
                            </td>

                            <!-- KOLOM 2: JUMLAH RETUR -->
                            <td>
                                <div class="input-group">
                                    <input type="number" 
                                           name="returns[${i}][qty_return]" 
                                           class="form-control" 
                                           step="0.01" 
                                           placeholder="0">
                                    <span class="input-group-text bg-light">${item.satuan || 'unit'}</span>
                                </div>
                            </td>

                            <!-- KOLOM 3: ALASAN -->
                            <td>
                                <input type="text" 
                                       name="returns[${i}][alasan]" 
                                       class="form-control" 
                                       placeholder="Contoh: Rusak/Busuk">
                            </td>
                        </tr>`;
                    });
                }
                $('#returTableBody').html(html);
            },
            error: function() {
                $('#returTableBody').html('<tr><td colspan="3" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    });
</script>
@endpush

@include('Temp.Investor.footer')
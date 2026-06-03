{{-- resources/views/purchasing/editTransfer.blade.php --}}
@include('Temp.Investor.header')

<style>
    /* PALET WARNA BARU: LEBIH KALEM & MODERN (SNEAT THEME) */
    :root {
        --bg: #f5f5f9;
        --card: #ffffff;
        --text: #233446;
        --muted: #a1acb8;
        --border: #d9dee3;
        --shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        --radius: 12px;
        --primary: #696cff;
        --soft: #fcfcfd;
    }

    /* Styling form input entri data */
    .form-label-custom {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #566a7f;
        letter-spacing: 0.5px;
    }

    /* Tabel detail barang khusus form entri */
    #tableTransfer {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #tableTransfer thead th {
        background-color: #f5f5f9 !important;
        border-bottom: 1px solid var(--border) !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #566a7f;
        padding: 12px 14px !important;
        letter-spacing: 0.5px;
    }

    #tableTransfer tbody td {
        padding: 12px 14px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f0f2f4 !important;
        color: #697a8d;
    }

    #tableTransfer tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.02) !important;
    }

    /* TOMBOL STYLING */
    .btn {
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 16px;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        color: #fff !important;
    }
    .btn-primary:hover {
        background: #5f61e6 !important;
        border-color: #5f61e6 !important;
        box-shadow: 0 2px 4px 0 rgba(105, 108, 255, 0.4);
    }

    .btn-secondary {
        background: #8592a3 !important;
        border-color: #8592a3 !important;
        color: #fff !important;
    }

    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid var(--border);
        height: 40px;
        color: #495057;
        font-size: 0.875rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15) !important;
    }

    /* Select2 custom matching override */
    .select2-container .select2-selection--single {
        height: 40px;
        border-radius: 8px;
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        padding: 0 10px;
        background: #fff;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
        padding-left: 0;
        color: #495057;
        font-size: 0.875rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <form action="{{ route('simple-transfer.update', $transfer->transfer_num) }}" method="POST" id="editTransferForm">
                @csrf
                @method('PUT')

                {{-- Hidden Fields --}}
                <input type="hidden" name="transfer_num" value="{{ $transfer->transfer_num }}">

                <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap align-items-center">
                            <div class="me-auto">
                                <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Edit Simple Transfer</h5>
                                <span class="text-muted small">Nomor Dokumen: <strong style="color: var(--primary);">{{ $transfer->transfer_num }}</strong></span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('simple-transfer.index') }}" class="btn btn-sm btn-light border d-inline-flex align-items-center" style="height: 36px; background: #fff; color: #8592a3;">Batal</a>
                                <button type="submit" class="btn btn-sm btn-primary px-4 shadow-sm d-inline-flex align-items-center gap-1" id="btn-save-edit" style="height: 36px;">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    {{-- SECTION 1: INFO UTAMA --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center gap-2 mb-4">
                                    <div class="p-2 bg-light rounded text-primary" style="color: var(--primary) !important;">
                                        <i class="bi bi-info-circle fs-5"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-0">Informasi Transaksi Mutasi</h6>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label form-label-custom mb-2">Gudang Asal (Origin)</label>
                                            <input type="text" class="form-control bg-light shadow-none border-light-dark" style="border-radius: 8px;" value="{{ $transfer->origin_location_name }}" readonly>
                                            <input type="hidden" name="origin_id" value="{{ $transfer->origin_location_id }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label form-label-custom mb-2">Gudang Tujuan (Destination)</label>
                                            <input type="text" class="form-control bg-light shadow-none border-light-dark" style="border-radius: 8px;" value="{{ $transfer->destination_location_name }}" readonly>
                                            <input type="hidden" name="destination_id" value="{{ $transfer->destination_location_id }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label form-label-custom mb-2">Tanggal Transfer *</label>
                                            <input type="date" name="transfer_date" id="transferDate" class="form-control shadow-none" style="border-radius: 8px;"
                                                value="{{ date('Y-m-d', strtotime($transfer->transfer_date)) }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: TABEL PRODUK --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
                            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-2 bg-light rounded text-primary" style="color: var(--primary) !important;">
                                        <i class="bi bi-box-seam fs-5"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold text-dark">Item Details</h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-light border text-primary d-inline-flex align-items-center gap-1" id="btnAddNewRow" style="color: var(--primary) !important; background: #fff;">
                                    <i class="bi bi-plus-lg"></i> Tambah Baris
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table mb-0" id="tableTransfer">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="80">No</th>
                                                <th>Product Name</th>
                                                <th class="text-center" width="140">Available Stock</th>
                                                <th class="text-center" width="180">Qty Transfer</th>
                                                <th class="text-center" width="80">#</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itemBody">
                                            @forelse($transfer->items as $index => $item)
                                            <tr>
                                                <td class="text-center align-middle iteration text-muted small">{{ $index + 1 }}</td>
                                                <td>
                                                    <select name="items[{{ $index }}][product_detail_id]" class="form-select select2-product shadow-none">
                                                        @foreach($products as $p)
                                                        <option value="{{ $p->product_detail_id }}"
                                                            {{ $p->product_detail_id == $item->product_detail_id ? 'selected' : '' }}>
                                                            {{ $p->nama_bahan }} - [{{ $p->nama_unit }}]
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center align-middle text-secondary small fw-medium">0</td>
                                                <td class="px-4">
                                                    <input type="number" name="items[{{ $index }}][qty]" class="form-control text-center shadow-none form-control-sm" value="{{ $item->qty }}" step="0.01">
                                                </td>
                                                <td class="text-center align-middle">
                                                    <button type="button" class="btn btn-sm btn-light border text-danger btnRemoveRow" style="padding: 2px 8px;">&times;</button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-danger fw-semibold small">Data detail tidak ditemukan untuk nomor ini!</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: ADDITIONAL INFO --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <label class="form-label form-label-custom mb-2"><i class="bi bi-chat-left-text me-1"></i> Catatan Transfer (Notes)</label>
                                <textarea name="notes" id="inputNotes" class="form-control shadow-none p-3 small" rows="3" placeholder="Tambahkan keterangan tambahan mengenai mutasi stok jika diperlukan...">{{ $transfer->additional_info }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        function initSelect2() {
            $('.select2-product').select2({
                placeholder: "Pilih Produk",
                allowClear: true,
                width: '100%'
            });
        }
        initSelect2();

        // Trigger Kalender Standar Modern
        $(document).on('click', '#transferDate', function() {
            try {
                this.showPicker();
            } catch (e) {
                $(this).focus();
            }
        });

        // Tambah Baris Baru Dinamis (Tanpa Mengubah Struktur Array Nama)
        let rowIdx = $('#tableTransfer tbody tr').length;
        $('#btnAddNewRow').on('click', function() {
            let newRow = `
            <tr>
                <td class="text-center align-middle iteration text-muted small">${rowIdx + 1}</td>
                <td>
                    <select name="items[${rowIdx}][product_detail_id]" class="form-select select2-product shadow-none">
                        <option value="">- Pilih Produk -</option>
                        @foreach($products as $p)
                            <option value="{{ $p->product_detail_id }}">
                                {{ $p->nama_bahan }} - [{{ $p->nama_unit }}]
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="text-center align-middle text-secondary small fw-medium">0</td>
                <td class="px-4">
                    <input type="number" name="items[${rowIdx}][qty]" class="form-control text-center shadow-none form-control-sm" value="1" min="0.01" step="0.01" required>
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-sm btn-light border text-danger btnRemoveRow" style="padding: 2px 8px;">&times;</button>
                </td>
            </tr>`;
            $('#itemBody').append(newRow);
            initSelect2();
            rowIdx++;
        });

        // Hapus Baris Dinamis
        $(document).on('click', '.btnRemoveRow', function() {
            if ($('#tableTransfer tbody tr').length > 1) {
                $(this).closest('tr').remove();
                reindexRows();
            } else {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Minimal harus ada satu item transfer!', confirmButtonColor: '#696cff' });
            }
        });

        function reindexRows() {
            $('.iteration').each(function(i) {
                $(this).html(i + 1);
            });
        }

        // Handle Submit via AJAX Spoofing Terintegrasi SweetAlert2 (Matching Aksi Create)
        $('#btn-save-edit').on('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data mutasi stok akan diperbarui di sistem central ESB dan internal database.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Ya, Perbarui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let formData = {
                        _token: "{{ csrf_token() }}",
                        _method: "PUT",
                        transfer_num: $("input[name='transfer_num']").val(),
                        transfer_date: $('#transferDate').val(),
                        origin_id: $("input[name='origin_id']").val(),
                        destination_id: $("input[name='destination_id']").val(),
                        notes: $('#inputNotes').val(),
                        items: []
                    };

                    $('#tableTransfer tbody tr').each(function() {
                        let pId = $(this).find('select').val();
                        let q = $(this).find('input[type="number"]').val();
                        if (pId) {
                            formData.items.push({
                                product_detail_id: pId,
                                qty: q
                            });
                        }
                    });

                    $.ajax({
                        url: "{{ route('simple-transfer.update', $transfer->transfer_num) }}",
                        type: "POST",
                        data: formData,
                        beforeSend: function() {
                            $('#btn-save-edit').attr('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');
                        },
                        success: function(res) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Data mutasi stok berhasil diperbarui di sistem central.',
                                confirmButtonColor: '#696cff'
                            }).then(() => {
                                window.location.href = "{{ route('simple-transfer.index') }}";
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', (xhr.responseJSON ? xhr.responseJSON.message : "Internal Server Error"), 'error');
                            $('#btn-save-edit').attr('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Perubahan');
                        }
                    });
                }
            });
        });
    });
</script>

@include('Temp.Investor.footer')
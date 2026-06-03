{{-- resources/views/purchasing/dashboardOutlet.blade.php --}}
@if(auth()->user()->role === 'superadmin')
    @include('Temp.Investor.header') {{-- Ganti dengan path header backoffice kamu --}}
@else
    @include('Temp.DC.header') {{-- Ganti dengan path header DC kamu --}}
@endif
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

    .card { border-radius: 10px; }
    .table thead th { 
        font-size: 0.8rem; 
        text-transform: uppercase; 
        color: #8898aa !important;
        border-bottom: 2px solid #f4f7f6;
    }
    .table tbody td { padding: 15px 10px; }
    .form-check-input { width: 1.2rem; height: 1.2rem; cursor: pointer; }
    .btn-primary { background: #3b82f6; border: none; }
    .btn-primary:hover { background: #2563eb; }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">
            
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold text-primary mb-0"><i class="fas fa-truck-loading me-2"></i>Daftar PO Siap Kirim</h5>
                </div>
                
                <div class="card-body">
                    {{-- Form hidden di luar tabel agar tidak dirusak DataTable --}}
                    <form id="formBuatSJ" action="{{ route('scm.buat-sj') }}" method="POST">
                        @csrf
                        {{-- po_ids akan diisi via JS saat submit --}}
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="orderTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 50px;">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th class="text-center">No PO</th>
                                    <th class="text-center">Outlet</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($listPO as $po)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox"
                                               class="form-check-input po-checkbox"
                                               value="{{ $po->id }}">
                                    </td>
                                    <td class="fw-bold text-dark">{{ $po->no_po }}</td>
                                    <td class="text-muted">{{ $po->outlet_name }}</td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary px-3 btn-view-po"
                                                data-id="{{ $po->id }}"
                                                data-no-po="{{ $po->no_po }}">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <span class="text-muted small" id="selectedCount">0 PO dipilih</span>
                        <button type="button" id="btnBuatSJ"
                                class="btn btn-primary px-4 py-2 shadow-sm fw-bold" disabled>
                            <i class="fas fa-file-invoice me-2"></i> Buat Surat Jalan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
// Select all checkboxes
$('#selectAll').on('change', function() {
    $('.po-checkbox').prop('checked', $(this).prop('checked'));
    updateSelectedCount();
});

// Update counter & tombol saat checkbox berubah
$(document).on('change', '.po-checkbox', function() {
    const total    = $('.po-checkbox').length;
    const checked  = $('.po-checkbox:checked').length;
    $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
    $('#selectAll').prop('checked', checked === total);
    updateSelectedCount();
});

function updateSelectedCount() {
    const count = $('.po-checkbox:checked').length;
    $('#selectedCount').text(count + ' PO dipilih');
    $('#btnBuatSJ').prop('disabled', count === 0);
}

// Submit form dengan po_ids yang dipilih
$('#btnBuatSJ').on('click', function() {
    const checkedIds = $('.po-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (checkedIds.length === 0) {
        Swal.fire('Perhatian', 'Pilih minimal 1 PO terlebih dahulu!', 'warning');
        return;
    }

    // Hapus input lama lalu tambah yang baru ke form hidden
    $('#formBuatSJ input[name="po_ids[]"]').remove();
    checkedIds.forEach(function(id) {
        $('#formBuatSJ').append(
            $('<input>').attr({ type: 'hidden', name: 'po_ids[]', value: id })
        );
    });

    $('#formBuatSJ').submit();
});
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
        // Saat modal benar-benar tertutup (hidden.bs.modal)
        $('#detailModal').on('hidden.bs.modal', function() {
            // 1. Hapus backdrop hitam yang bandel
            $('.modal-backdrop').remove();

            // 2. Hapus class 'modal-open' dari body agar bisa di-scroll
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        });
    });
</script>

<script>
    $('.btn-view-po').click(function() {
        let id = $(this).data('id');
        let status = $(this).data('status'); // Pastikan tombol View di tabel punya data-status

        // Simpan ID ke modal
        $('#detailModal').data('current-id', id);

        // LOGIKA TOMBOL:
        // Jika status BUKAN 'Waiting', disable tombol
        if (status !== 'Waiting') {
            $('#btn-approve, #btn-reject').prop('disabled', true);
            $('#btn-approve, #btn-reject').text('Sudah diproses'); // Opsional: ganti teks
        } else {
            // Jika status 'Waiting', aktifkan kembali
            $('#btn-approve').prop('disabled', false).text('Approved');
            $('#btn-reject').prop('disabled', false).text('Rejected');
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
@endpush

@if(auth()->user()->role === 'superadmin')
    @include('Temp.Investor.footer')
@else
    @include('Temp.DC.footer')
@endif
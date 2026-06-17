{{-- resources/views/Purchasing/TransferRequest/create.blade.php --}}
@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
:root { --primary:#696cff; --accent:#71dd37; --warn:#ffab00; --danger:#ff3e1d; --bg:#f5f5f9; --card:#fff; --border:#e0e0f0; --shadow:0 2px 8px rgba(67,89,113,.10); --radius:12px; }
body { background:var(--bg); }

.form-card { background:var(--card); border-radius:var(--radius); border:0.5px solid var(--border); padding:24px 28px; margin-bottom:20px; box-shadow:var(--shadow); }
.form-section-title { font-size:13px; font-weight:700; color:#233446; margin-bottom:16px; display:flex; align-items:center; gap:8px; }
.form-section-icon  { width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }

.form-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#566a7f; margin-bottom:4px; }
.form-control,.form-select { border-radius:8px; border:1px solid var(--border); height:40px; font-size:.875rem; color:#233446; }
.form-control:focus,.form-select:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(105,108,255,.12); outline:none; }
textarea.form-control { height:auto; }

/* Select2 */
.select2-container .select2-selection--single { height:40px; border-radius:8px; border:1px solid var(--border); display:flex; align-items:center; padding:0 10px; }
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height:40px; padding-left:0; color:#233446; font-size:.875rem; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height:40px; }

/* DC Route Visual */
.dc-route { display:flex; align-items:center; gap:12px; background:#f5f5f9; border-radius:10px; padding:14px 18px; margin-bottom:20px; }
.dc-node  { flex:1; background:var(--card); border-radius:8px; padding:10px 14px; border:0.5px solid var(--border); text-align:center; }
.dc-node .dc-label { font-size:10px; font-weight:700; text-transform:uppercase; color:#697a8d; margin-bottom:4px; letter-spacing:.5px; }
.dc-node .dc-name  { font-size:13px; font-weight:700; color:#233446; }
.dc-arrow { color:var(--primary); font-size:20px; flex-shrink:0; }

/* Table item */
.items-table { width:100%; border-collapse:collapse; font-size:13px; }
.items-table thead th { background:#f5f5f9; padding:10px 12px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#566a7f; border-bottom:1px solid var(--border); }
.items-table tbody td { padding:10px 12px; border-bottom:0.5px solid #f0f0f8; vertical-align:middle; }

.btn-add-row { background:#eeedfe; color:var(--primary); border:1px dashed var(--primary); padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:.15s; }
.btn-add-row:hover { background:var(--primary); color:#fff; }
.btn-remove { background:none; border:none; color:#a32d2d; cursor:pointer; font-size:16px; padding:4px 8px; border-radius:6px; }
.btn-remove:hover { background:#fcebeb; }

/* Summary box */
.summary-box { background:linear-gradient(135deg,var(--primary) 0%,#8b8eff 100%); border-radius:12px; padding:20px 24px; color:#fff; }
.sb-label { font-size:10px; text-transform:uppercase; letter-spacing:.6px; opacity:.8; margin-bottom:4px; }
.sb-value { font-size:1.6rem; font-weight:800; }

/* Topbar */
.topbar-card { background:var(--card); border-radius:var(--radius); border:0.5px solid var(--border); padding:14px 20px; margin-bottom:20px; display:flex; align-items:center; gap:12px; box-shadow:var(--shadow); }
.btn-save { background:var(--primary); color:#fff; border:none; padding:8px 24px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; }
.btn-save:hover { background:#5153e0; }
.btn-back { background:#f0f0f8; color:#566a7f; border:none; padding:8px 16px; border-radius:8px; font-size:13px; cursor:pointer; text-decoration:none; }
</style>

<main class="app-main">
<div class="app-content">
<div class="container-fluid py-4">

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form action="{{ route('transfer-request.store') }}" method="POST" id="formCreate">
    @csrf

        {{-- Topbar --}}
        <div class="topbar-card">
            <div class="me-auto">
                <h5 class="fw-bold mb-0 text-dark" style="font-size:1.05rem;">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Buat Transfer Request Antar DC
                </h5>
                <div class="text-muted" style="font-size:12px;">Nomor akan dibuat otomatis saat disimpan</div>
            </div>
            <a href="{{ route('transfer-request.index') }}" class="btn-back">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <button type="submit" class="btn-save" id="btnSave">
                <i class="bi bi-send me-1"></i>Kirim Request
            </button>
        </div>

        {{-- Transaction Info --}}
        <div class="form-card">
            <div class="form-section-title">
                <div class="form-section-icon" style="background:#eeedfe;color:var(--primary);">
                    <i class="bi bi-info-circle"></i>
                </div>
                Transaction Information
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Dari DC (Pemohon) <span class="text-danger">*</span></label>
                    @if($myWarehouseId)
                        {{-- Kalau user sudah terkunci ke DC tertentu --}}
                        <input type="hidden" name="from_warehouse_id" value="{{ $myWarehouseId }}">
                        <input type="text" class="form-control bg-light"
                               value="{{ $warehouses->firstWhere('id', $myWarehouseId)->nama_warehouse ?? '' }}"
                               readonly>
                        <div style="font-size:11px;color:#697a8d;margin-top:4px;">
                            <i class="bi bi-lock-fill me-1"></i>Dikunci sesuai akun Anda
                        </div>
                    @else
                        {{-- Backoffice bisa pilih --}}
                        <select name="from_warehouse_id" id="fromWarehouse" class="form-select select2-dc" required>
                            <option value="">— Pilih DC Pemohon —</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('from_warehouse_id')==$w->id?'selected':'' }}>{{ $w->nama_warehouse }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ke DC (Tujuan) <span class="text-danger">*</span></label>
                    <select name="to_warehouse_id" id="toWarehouse" class="form-select select2-dc" required>
                        <option value="">— Pilih DC Tujuan —</option>
                        @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ old('to_warehouse_id')==$w->id?'selected':'' }}>{{ $w->nama_warehouse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Request <span class="text-danger">*</span></label>
                    <input type="date" name="request_date" class="form-control"
                           value="{{ old('request_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dibutuhkan Sebelum</label>
                    <input type="date" name="needed_date" class="form-control"
                           value="{{ old('needed_date') }}">
                </div>
            </div>

            {{-- Visual route DC → DC --}}
            <div class="dc-route">
                <div class="dc-node">
                    <div class="dc-label">📦 Dari DC</div>
                    <div class="dc-name" id="labelFromDC">— Belum dipilih —</div>
                </div>
                <div class="dc-arrow"><i class="bi bi-arrow-right-circle-fill"></i></div>
                <div class="dc-node">
                    <div class="dc-label">🏭 Ke DC</div>
                    <div class="dc-name" id="labelToDC">— Belum dipilih —</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Catatan / Alasan Request</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Jelaskan alasan request ini...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Item Details --}}
        <div class="form-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-section-title mb-0">
                    <div class="form-section-icon" style="background:#e1f5ee;color:#0f6e56;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    Daftar Barang yang Diminta
                    <span class="badge bg-primary bg-opacity-10 text-primary ms-1" id="badgeItemCount" style="font-size:11px;">0 item</span>
                </div>
                <button type="button" class="btn-add-row" id="btnAddRow">
                    <i class="bi bi-plus-lg"></i> Tambah Barang
                </button>
            </div>

            <div class="table-responsive">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:32px;">#</th>
                            <th>Nama Barang / Bahan</th>
                            <th style="width:80px;" class="text-center">Satuan</th>
                            <th style="width:140px;" class="text-center">Qty Diminta</th>
                            <th style="width:200px;">Catatan Item</th>
                            <th style="width:50px;" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="itemBody">
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center py-4 text-muted" style="font-size:13px;">
                                Belum ada barang. Klik "+ Tambah Barang" untuk menambahkan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </form>

    {{-- Template row (tidak pernah dirender ke DOM) --}}
    <script id="row-template" type="text/html">
        <tr class="item-row">
            <td class="text-center text-muted iter" style="font-size:12px;">__IDX__</td>
            <td>
                <select name="items[__IDX__][bahan_id]" class="form-select select2-bahan shadow-none" required style="width:100%;">
                    <option value="">🔍 Cari bahan...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}"
                            data-unit-id="{{ $p->unit_id ?? '' }}"
                            data-satuan="{{ $p->nama_satuan }}">
                        {{ $p->nama_bahan }}@if($p->product_code) ({{ $p->product_code }})@endif
                    </option>
                    @endforeach
                </select>
                <input type="hidden" name="items[__IDX__][unit_id]" class="unit-id-input" value="">
            </td>
            <td class="text-center">
                <span class="text-muted satuan-label" style="font-size:12px;">—</span>
            </td>
            <td>
                <input type="number" name="items[__IDX__][qty]"
                       class="form-control form-control-sm text-center shadow-none"
                       value="1" min="0.01" step="0.01" required>
            </td>
            <td>
                <input type="text" name="items[__IDX__][notes]"
                       class="form-control form-control-sm shadow-none"
                       placeholder="Opsional...">
            </td>
            <td class="text-center">
                <button type="button" class="btn-remove btnRemove" title="Hapus baris">
                    <i class="bi bi-trash3"></i>
                </button>
            </td>
        </tr>
    </script>

</div>
</div>
</main>

@push('scripts')
<script>
$(document).ready(function () {
    let rowIdx = 0;

    // ── Select2 DC dropdowns ──────────────────────────────────
    $('.select2-dc').select2({ width: '100%', placeholder: '— Pilih DC —', allowClear: true });

    // Update visual route label
    $('#fromWarehouse, #toWarehouse').on('select2:select select2:unselect', function () {
        const fromTxt = $('#fromWarehouse option:selected').text() || '— Belum dipilih —';
        const toTxt   = $('#toWarehouse option:selected').text()   || '— Belum dipilih —';
        $('#labelFromDC').text(fromTxt);
        $('#labelToDC').text(toTxt);
    });

    @if($myWarehouseId)
    // Kalau from warehouse sudah dikunci, update label langsung
    $('#labelFromDC').text('{{ $warehouses->firstWhere("id", $myWarehouseId)->nama_warehouse ?? "" }}');
    @endif

    // ── Select2 bahan (per baris) ─────────────────────────────
    function initBahanSelect2($row) {
        $row.find('.select2-bahan').each(function () {
            if ($(this).data('select2')) $(this).select2('destroy');
            $(this).select2({
                width        : '100%',
                placeholder  : '🔍 Cari bahan...',
                allowClear   : true,
                language     : { noResults: () => 'Bahan tidak ditemukan' }
            });
        });
    }

    // ── Update satuan saat bahan dipilih ─────────────────────
    $(document).on('select2:select', '.select2-bahan', function (e) {
        const $opt    = $(e.params.data.element);
        const satuan  = $opt.data('satuan')  || '—';
        const unitId  = $opt.data('unit-id') || '';
        const $row    = $(this).closest('tr');
        $row.find('.satuan-label').text(satuan);
        $row.find('.unit-id-input').val(unitId);
    });
    $(document).on('select2:unselect select2:clear', '.select2-bahan', function () {
        const $row = $(this).closest('tr');
        $row.find('.satuan-label').text('—');
        $row.find('.unit-id-input').val('');
    });

    // ── Tambah baris ─────────────────────────────────────────
    $('#btnAddRow').on('click', function () {
        $('#emptyRow').remove();
        const html = $('#row-template').html().replace(/__IDX__/g, rowIdx);
        const $row = $(html);
        $('#itemBody').append($row);
        initBahanSelect2($row);
        rowIdx++;
        updateCounter();
    });

    // ── Hapus baris ──────────────────────────────────────────
    $(document).on('click', '.btnRemove', function () {
        if ($('#itemBody .item-row').length > 1) {
            const $sel = $(this).closest('tr').find('.select2-bahan');
            if ($sel.data('select2')) $sel.select2('destroy');
            $(this).closest('tr').remove();
            reindex();
            updateCounter();
        } else {
            Swal.fire({ icon:'warning', title:'Perhatian', text:'Minimal harus ada 1 item!', confirmButtonColor:'#696cff' });
        }
    });

    function reindex() {
        $('#itemBody .item-row').each(function (i) { $(this).find('.iter').text(i + 1); });
    }
    function updateCounter() {
        const count = $('#itemBody .item-row').length;
        $('#badgeItemCount').text(count + ' item');
    }

    // ── Submit guard ─────────────────────────────────────────
    $('#formCreate').on('submit', function (e) {
        if ($('#itemBody .item-row').length === 0) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'Perhatian', text:'Tambahkan minimal 1 barang!', confirmButtonColor:'#696cff' });
            return;
        }
        const from = $('[name="from_warehouse_id"]').val();
        const to   = $('[name="to_warehouse_id"]').val();
        if (!from || !to) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'Perhatian', text:'Pilih DC Pemohon dan DC Tujuan!', confirmButtonColor:'#696cff' });
            return;
        }
        if (from === to) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'Perhatian', text:'DC Pemohon dan DC Tujuan tidak boleh sama!', confirmButtonColor:'#696cff' });
            return;
        }
        $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...');
    });
});
</script>
@endpush

@include('Temp.Investor.footer')
{{-- ================== OMSET & BUKTI ================== --}}

<div class="soft-alert primary mb-2">
  <div class="title">
    <i class="bi bi-receipt me-1"></i> Omset / Setoran + Bukti Foto
  </div>
  <p class="desc mb-0">
    Data tersimpan di <code>tbl_dsc_omset_setoran</code>.
    Foto disimpan ke <code>storage/dsc_bukti_uang_omset</code>.
  </p>
</div>

<div class="d-flex gap-2 flex-wrap mb-2">
  <select class="form-select form-select-sm"
          id="omset_shift"
          style="max-width:140px;"
          @if (empty($outletId)) disabled @endif>
    <option value="1">Shift 1</option>
    <option value="2">Shift 2</option>
  </select>

  <input class="form-control form-control-sm"
         id="omset_pic"
         placeholder="PIC wajib"
         style="min-width:220px; max-width:320px;"
         @if (empty($outletId)) disabled @endif>

  <button class="btn btn-sm btn-ghost"
          id="btnLoadOmset"
          type="button"
          @if (empty($outletId)) disabled @endif>
    <i class="bi bi-download me-1"></i> Load
  </button>

  <button class="btn btn-sm btn-primary"
          id="btnSaveOmset"
          type="button"
          @if (empty($outletId)) disabled @endif>
    <i class="bi bi-save me-1"></i> Simpan
  </button>

  <div class="dsc-help align-self-center" id="omset_meta">
    Outlet: <b>{{ $outletId ?: '-' }}</b> • Tanggal: <b>{{ $today }}</b>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label fw-bold">Total Transaction</label>
    <input type="number" class="form-control" id="omset_total_transaction" value="0">
  </div>
  <div class="col-md-4">
    <label class="form-label fw-bold">Diskon</label>
    <input type="number" class="form-control" id="omset_diskon" value="0">
  </div>
  <div class="col-md-4">
    <label class="form-label fw-bold">Non Tunai</label>
    <input type="number" class="form-control" id="omset_non_tunai" value="0">
  </div>

  <div class="col-md-4">
    <label class="form-label fw-bold">Expense</label>
    <input type="number" class="form-control" id="omset_expense" value="0">
  </div>
  <div class="col-md-4">
    <label class="form-label fw-bold">Uang Fisik</label>
    <input type="number" class="form-control" id="omset_uang_fisik" value="0">
  </div>
  <div class="col-md-4">
    <label class="form-label fw-bold">Admin Pot Sales</label>
    <input type="number" class="form-control" id="omset_admin_pot_sales" value="0">
  </div>

  <div class="col-md-4">
    <label class="form-label fw-bold">Adjustment</label>
    <input type="number" class="form-control" id="omset_adjustment" value="0">
  </div>

  <div class="col-md-8">
    <label class="form-label fw-bold">Bukti Foto</label>
    <input type="file" class="form-control" id="omset_bukti" accept="image/*">
  </div>

  <div class="col-12">
    <div class="dsc-card">
      <div class="dsc-card-head d-flex justify-content-between">
        <div><i class="bi bi-image me-1"></i> Preview Bukti</div>
        <a id="omset_bukti_link" href="#" target="_blank" style="display:none;">Buka</a>
      </div>
      <div class="dsc-card-body">
        <img id="omset_bukti_img"
             src=""
             style="display:none;max-width:100%;border-radius:14px;">
        <div id="omset_bukti_empty" class="text-muted">
          Belum ada bukti.
        </div>
      </div>
    </div>
  </div>
</div>

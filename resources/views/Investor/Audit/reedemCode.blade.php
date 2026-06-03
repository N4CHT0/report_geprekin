<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Redeem Poin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  body {
    background-color: #f8f9fa;
    font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  .navbar {
    background-color: #212529 !important;
  }
  .navbar-brand {
    font-weight: 600;
  }
  .card {
    border: none;
    border-radius: 10px;
    background: #fff;
  }
  .section-title {
    font-weight: 600;
    margin-bottom: 15px;
    border-bottom: 2px solid #198754;
    padding-bottom: 5px;
  }
  .info-box {
    background: #f1f3f5;
    border-radius: 8px;
    padding: 12px;
    text-align: center;
  }
  .info-value {
    font-size: 1.4rem;
    font-weight: 600;
  }
  .info-label {
    font-size: 0.85rem;
    color: #6b7280;
  }
  .table th {
    background-color: #f3f4f6;
    font-weight: 600;
  }
  .table td {
    font-size: 0.9rem;
  }
  footer {
    color: #9ca3af;
    text-align: center;
    font-size: 0.85rem;
    padding: 20px 0;
  }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Redeem Poin</a>
    <div class="d-flex">
      <a href="{{ route('auditDashboard.index') }}" class="btn btn-outline-light btn-sm me-2">Halaman Utama</a>
      <form action="{{ route('auditDashboard.auditLogout') }}" method="POST" style="display:inline;">
          @csrf
          <button type="submit" class="btn btn-outline-danger btn-sm"
              onclick="return confirm('Yakin mau logout?')">
              Logout
          </button>
      </form>
    </div>
  </div>
</nav>

<div class="container my-5">

  @php $poinKonversi = 5000; @endphp

  <!-- Ringkasan User -->
  <div class="card shadow-sm p-4 mb-4">
    <h5 class="section-title">Ringkasan Akun</h5>
    <div class="row text-center g-3">
      <div class="col-md-3 col-6">
        <div class="info-box">
          <div class="info-value text-success">{{ number_format(($user->total_poin ?? 0) * $poinKonversi, 0, ',', '.') }}</div>
          <div class="info-label">Total Poin</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="info-box">
          <div class="info-value">Rp {{ number_format(($user->total_poin ?? 0) * $poinKonversi, 0, ',', '.') }}</div>
          <div class="info-label">Setara Saldo</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="info-box">
          <div class="info-value">{{ $userHistory->count() }}</div>
          <div class="info-label">Total Redeem</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="info-box">
            <div class="info-value text-primary">{{ $user->kode_reedem ?? '-' }}</div>
          <div class="info-label">Kode Redeem</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Detail Pengguna -->
  <div class="card shadow-sm p-4 mb-4">
    <h5 class="section-title">Informasi Pengguna</h5>
    <div class="row g-3">
      <div class="col-md-3">
        <small class="text-muted d-block">Nama</small>
        <strong>{{ $user->nama_lengkap }}</strong>
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Nomor Telepon</small>
        <strong>{{ $user->nomor_telp ?? '-' }}</strong>
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Bank</small>
        <strong>{{ $user->jenis_bank ?? '-' }}</strong>
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Nomor Rekening</small>
        <strong>{{ $user->nomor_rekening ?? '-' }}</strong>
      </div>
    </div>
  </div>

    <!-- Form Redeem -->
    <div class="card shadow-sm p-4 mb-4">
      <h5 class="section-title">Tukar Poin</h5>
      <form id="redeemForm">
        @csrf
        <div class="row g-3 align-items-end">
          <div class="col-md-5">
            <label class="form-label">Pilih Hadiah</label>
            <select class="form-select" id="pilihanHadiah" name="hadiah_id" required>
              <option value="">-- Pilih Hadiah --</option>
              @foreach($hadiah as $h)
                <option value="{{ $h->id }}" 
                        data-tipe="{{ $h->tipe }}" 
                        data-min-poin="{{ $h->poin_dibutuhkan }}">
                  {{ $h->nama_hadiah }} (Rp {{ number_format($h->poin_dibutuhkan * $poinKonversi, 0, ',', '.') }})
                </option>
              @endforeach
            </select>
          </div>
    
          <div class="col-md-3">
            <label class="form-label">Jumlah Poin</label>
            <input type="text" class="form-control" id="jumlahPoin" name="jumlah_poin_display" readonly required>
          </div>
    
          <div class="col-md-3">
            <label class="form-label">Setara Uang</label>
            <input type="text" class="form-control" id="jumlahRupiah" readonly>
          </div>
    
          <div class="col-md-1 text-end">
            <button type="submit" class="btn btn-success w-100">Kirim</button>
          </div>
        </div>
    
        <div class="mt-3" id="rekeningField" style="display:none;">
          <label class="form-label">Nomor Rekening Tujuan</label>
          <input type="text" class="form-control" id="noRek" readonly value="{{ $user->nomor_rekening }}">
          <small class="text-muted">Bank: {{ $user->jenis_bank }}</small>
        </div>
      </form>
</div>

<!-- Riwayat Redeem -->
<div class="card shadow-sm p-4 mb-4">
  <h5 class="section-title">Riwayat Penukaran</h5>

  <div class="table-responsive">
    <table class="table table-bordered table-hover text-center align-middle">
      <thead class="table-light">
        <tr>
          <th>Tanggal</th>
          <th>Hadiah</th>
          <th>Poin</th>
          <th>Nominal</th>
          <th>Status</th>
          <th>Bukti</th> {{-- kolom baru --}}
        </tr>
      </thead>

      <tbody>
        @forelse ($userHistory as $row)
          <tr>
            <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td>
            <td>{{ $row->nama_hadiah ?? '-' }}</td>
            <td>{{ number_format($row->jumlah_poin) }}</td>
            <td>{{ number_format($row->jumlah_poin * $poinKonversi, 0, ',', '.') }}</td>

            {{-- STATUS (DB: pending/approved/rejected → tampilan: Pending/Disetujui/Ditolak) --}}
            <td>
              @if($row->status === 'approved')
                <span class="badge bg-success">Disetujui</span>
              @elseif($row->status === 'rejected')
                <span class="badge bg-danger">Ditolak</span>
              @else
                <span class="badge bg-secondary">Pending</span>
              @endif
            </td>

            {{-- BUKTI / INVOICE / QR --}}
            <td>
              @php
                $isUsed    = isset($row->is_used) ? (bool) $row->is_used : false;
                $isExpired = $row->expired_date && $row->expired_date <= now();
              @endphp
            
              @if(!$isUsed && !$isExpired)
                {{-- SELAMA belum dipakai & belum kadaluarsa, selalu boleh cek bukti --}}
                <a href="{{ route('auditDashboard.reedemInvoiceView', $row->id) }}"
                   class="btn btn-sm btn-outline-primary"
                   target="_blank">
                  Cek Bukti
                </a>
              @elseif($isUsed)
                <span class="badge bg-secondary">Sudah digunakan</span>
              @elseif($isExpired)
                <span class="badge bg-warning text-dark">Kadaluarsa</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-muted">Belum ada riwayat redeem.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

</div>

<footer>© {{ date('Y') }} Geprekinaja | Sistem Redeem Poin</footer>

<script>
$(function(){
  const KONVERSI = {{ $poinKonversi }}; // misal 5000
  let totalPoin = parseInt("{{ $user->total_poin ?? 0 }}", 10);

  // Update input saat pilih hadiah
  $('#pilihanHadiah').on('change', function() {
    const tipe = $(this).find(':selected').data('tipe');
    let minPoin = parseInt($(this).find(':selected').data('min-poin') || 0);

    // Set minimal poin berdasarkan tipe hadiah
    if (tipe === 'uang' && minPoin < 20) minPoin = 20;       // minimal 20 poin
    if ((tipe === 'makan' || tipe === 'geprekin') && minPoin < 2) minPoin = 2; // minimal 2 poin

    // Tampilkan poin konversi di input
    $('#jumlahPoin').val((minPoin * KONVERSI).toLocaleString('id-ID'));
    $('#jumlahRupiah').val('Rp ' + (minPoin * KONVERSI).toLocaleString('id-ID'));

    if (tipe === 'uang') $('#rekeningField').slideDown(150);
    else $('#rekeningField').slideUp(150);
  });

  // Submit form redeem
  $('#redeemForm').on('submit', function(e) {
    e.preventDefault();
    const hadiah_id = $('#pilihanHadiah').val();
    let minPoin = parseInt($('#pilihanHadiah').find(':selected').data('min-poin') || 0);
    const tipe = $('#pilihanHadiah').find(':selected').data('tipe');

    // Validasi minimal poin
    if (tipe === 'uang' && minPoin < 20) minPoin = 20;
    if ((tipe === 'makan' || tipe === 'geprekin') && minPoin < 2) minPoin = 2;

    if (!hadiah_id || !minPoin) return;

    if (minPoin > totalPoin) {
      Swal.fire('Gagal', 'Poin Anda tidak cukup.', 'error');
      return;
    }

    $.post("{{ route('auditDashboard.submitRedeem') }}", {
      _token: "{{ csrf_token() }}",
      hadiah_id,
      jumlah_poin: minPoin // kirim poin asli ke server
    })
    .done(res => {
      if (res.success) Swal.fire('Berhasil', res.message, 'success').then(()=>location.reload());
      else Swal.fire('Gagal', res.message, 'error');
    })
    .fail(()=> Swal.fire('Error','Terjadi kesalahan server.','error'));
  });
});
</script>

</body>
</html>
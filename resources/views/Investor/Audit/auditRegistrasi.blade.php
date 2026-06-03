<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registrasi - Kuisioner</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
<style>
    body { background-color: #ECEFF1; font-family: 'Segoe UI', sans-serif; }
    .card { box-shadow: 0px 4px 8px rgba(0,0,0,0.1); border-radius: 8px; }
    .btn-blue { background-color: #1A237E; color: #fff; border-radius: 4px; }
    .btn-blue:hover { background-color: #000; }
    input, select, textarea { border-radius: 5px; border: 1px solid #ccc; padding: 10px; width: 100%; }
    .grid-2col { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    @media (max-width: 768px) { .grid-2col { grid-template-columns: 1fr; } }
    .preview-img { max-width: 200px; border-radius: 8px; display: none; margin-top: 10px; }
    .remove-img {
        position: absolute;
        top: -10px;
        right: -10px;
        background: red;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: none;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        font-size: 16px;
    }
    video { width: 100%; max-height: 250px; border-radius: 8px; display: none; }
</style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 p-4">
                <h3 class="text-center mb-4">Formulir Registrasi Akun</h3>

                {{-- Error Laravel --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('auditDashboard.storeAuditRegistrasi') }}"
                      enctype="multipart/form-data"
                      id="regForm"
                      novalidate>
                    @csrf

                    <div class="grid-2col">
                        {{-- Nama lengkap --}}
                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="nama_lengkap"
                                   value="{{ old('nama_lengkap') }}"
                                   required
                                   minlength="3"
                                   maxlength="150"
                                   autocomplete="name">
                        </div>

                        {{-- Username --}}
                        <div class="form-group">
                            <label>Username <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="username"
                                   value="{{ old('username') }}"
                                   required
                                   minlength="3"
                                   maxlength="50"
                                   pattern="^[A-Za-z0-9._-]+$"
                                   title="Hanya huruf, angka, titik, underscore, dan strip yang diperbolehkan.">
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   maxlength="255"
                                   autocomplete="email">
                        </div>

                        {{-- Password --}}
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   name="password"
                                   required
                                   minlength="6"
                                   autocomplete="new-password">
                        </div>

                        {{-- Nomor HP --}}
                        <div class="form-group">
                            <label>Nomor HP <span class="text-danger">*</span></label>
                            <input type="tel"
                                   name="nomor_telp"
                                   value="{{ old('nomor_telp') }}"
                                   required
                                   minlength="9"
                                   maxlength="20"
                                   pattern="^[0-9+]+$"
                                   title="Hanya boleh angka dan tanda +.">
                        </div>

                        {{-- Jenis Bank --}}
                        <!--<div class="form-group">-->
                        <!--    <label>Jenis Bank <span class="text-danger">*</span></label>-->
                        <!--    <select name="jenis_bank" required>-->
                        <!--        <option value="">-- Pilih Bank --</option>-->
                        <!--        <option value="BCA"     {{ old('jenis_bank') == 'BCA' ? 'selected' : '' }}>BCA</option>-->
                        <!--        <option value="Mandiri" {{ old('jenis_bank') == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>-->
                        <!--        <option value="BRI"     {{ old('jenis_bank') == 'BRI' ? 'selected' : '' }}>BRI</option>-->
                        <!--        <option value="BNI"     {{ old('jenis_bank') == 'BNI' ? 'selected' : '' }}>BNI</option>-->
                        <!--        <option value="BSI"     {{ old('jenis_bank') == 'BSI' ? 'selected' : '' }}>BSI</option>-->
                        <!--    </select>-->
                        <!--</div>-->

                        {{-- Nomor Rekening --}}
                        <!--<div class="form-group">-->
                        <!--    <label>Nomor Rekening <span class="text-danger">*</span></label>-->
                        <!--    <input type="text"-->
                        <!--           name="nomor_rekening"-->
                        <!--           value="{{ old('nomor_rekening') }}"-->
                        <!--           required-->
                        <!--           minlength="5"-->
                        <!--           maxlength="50"-->
                        <!--           pattern="^[0-9]+$"-->
                        <!--           title="Nomor rekening hanya boleh angka.">-->
                        <!--</div>-->
                    </div> {{-- end grid-2col --}}

                    {{-- FOTO (WAJIB) --}}
                    <div class="form-group mt-3">
                        <label>Upload / Ambil Foto Selfie <span class="text-danger">*</span></label>
                        <input type="file" id="fotoFile" name="foto" accept="image/*">
                        <div class="mt-2">
                            <button type="button" id="openCameraBtn" class="btn btn-sm btn-blue">
                                Buka Kamera
                            </button>
                            <button type="button" id="captureBtn"
                                    class="btn btn-sm btn-blue"
                                    style="display:none;">
                                Ambil Foto
                            </button>
                            <button type="button" id="closeCameraBtn"
                                    class="btn btn-sm btn-secondary"
                                    style="display:none;">
                                Tutup Kamera
                            </button>
                        </div>

                        <div class="mt-2 position-relative" style="display:inline-block;">
                            <video id="video" autoplay playsinline></video>
                            <img id="preview" class="preview-img" alt="Preview Foto">
                            <button type="button" id="removePreview" class="remove-img">&times;</button>
                        </div>

                        {{-- Foto base64 dari kamera --}}
                        <input type="hidden" id="fotoBase64" name="fotoBase64" value="">
                        <small class="text-muted d-block">
                            Foto wajib diisi (boleh upload file atau ambil dari kamera).
                        </small>
                    </div>

                    <p class="text-muted small mb-2">
                        <i>* Tidak boleh mendaftar lebih dari 1x (data email / nomor HP / rekening harus unik).</i>
                    </p>

                    <button type="submit" class="btn btn-primary w-100">
                        Daftar Sekarang
                    </button>

                    <div class="text-center mt-3">
                        <small>Sudah punya akun?
                            <a href="{{ route('auditDashboard.auditLogin') }}">Login di sini</a>
                        </small>
                    </div>
                </form>
            </div>

            <div class="text-center mt-3 text-muted small">
                &copy; 2025 Kuisioner | All Rights Reserved
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- SweetAlert dari session (optional) --}}
<script>
document.addEventListener("DOMContentLoaded", function() {
    @if (session('sweetalert'))
        Swal.fire({
            icon: '{{ session('sweetalert.icon') }}',
            title: '{{ session('sweetalert.title') }}',
            text: '{{ session('sweetalert.text') }}',
        });
    @endif
});
</script>

<script>
const regForm        = document.getElementById('regForm');
const openCameraBtn  = document.getElementById('openCameraBtn');
const captureBtn     = document.getElementById('captureBtn');
const closeCameraBtn = document.getElementById('closeCameraBtn');
const video          = document.getElementById('video');
const preview        = document.getElementById('preview');
const removePreview  = document.getElementById('removePreview');
const fotoBase64Input= document.getElementById('fotoBase64');
const fotoFile       = document.getElementById('fotoFile');
let stream = null;

// --- Kamera ---
openCameraBtn.addEventListener('click', async () => {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
        video.srcObject = stream;
        video.style.display = 'block';
        captureBtn.style.display = 'inline-block';
        closeCameraBtn.style.display = 'inline-block';
        openCameraBtn.style.display = 'none';
        fotoFile.value = '';
        preview.style.display = 'none';
        removePreview.style.display = 'none';
        fotoBase64Input.value = '';
    } catch (err) {
        alert('Tidak dapat membuka kamera: ' + err.message);
    }
});

captureBtn.addEventListener('click', () => {
    if (!video.videoWidth) return alert('Video belum siap.');
    const canvas = document.createElement('canvas');
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    const dataUrl = canvas.toDataURL('image/png');
    preview.src = dataUrl;
    preview.style.display = 'block';
    removePreview.style.display = 'flex';
    fotoBase64Input.value = dataUrl;

    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    video.style.display = 'none';
    captureBtn.style.display = 'none';
    closeCameraBtn.style.display = 'none';
    openCameraBtn.style.display = 'inline-block';
});

closeCameraBtn.addEventListener('click', () => {
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    video.style.display = 'none';
    captureBtn.style.display = 'none';
    closeCameraBtn.style.display = 'none';
    openCameraBtn.style.display = 'inline-block';
});

fotoFile.addEventListener('change', e => {
    const [file] = e.target.files;
    if (!file) return;
    const url = URL.createObjectURL(file);
    preview.src = url;
    preview.style.display = 'block';
    removePreview.style.display = 'flex';
    fotoBase64Input.value = '';

    // matikan kamera kalau masih nyala
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    video.style.display = 'none';
    captureBtn.style.display = 'none';
    closeCameraBtn.style.display = 'none';
    openCameraBtn.style.display = 'inline-block';
});

removePreview.addEventListener('click', () => {
    preview.src = '';
    preview.style.display = 'none';
    removePreview.style.display = 'none';
    fotoBase64Input.value = '';
    fotoFile.value = '';
});

// --- Validasi sebelum submit ---
regForm.addEventListener('submit', function(e) {
    // cek HTML5 required & pattern
    if (!regForm.checkValidity()) {
        e.preventDefault();
        regForm.reportValidity();
        return;
    }

    // wajib: salah satu dari fotoFile atau fotoBase64
    if (!fotoBase64Input.value && !fotoFile.value) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Foto wajib diisi!',
            text: 'Silakan ambil foto via kamera atau upload file sebelum submit.'
        });
    }
});
</script>
</body>
</html>
@extends('Room.layouts.app')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('inventory.master') }}" class="text-[#2A435D] hover:underline flex items-center gap-2 mb-4 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Master Aset
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Serah Terima Aset IT</h1>
        <p class="text-sm text-slate-500">Proses penyerahan laptop ke karyawan beserta tanda tangan persetujuan.</p>
    </div>

    @if($errors->any())
        <div class="mb-4 bg-red-50 text-red-700 p-4 rounded-lg border border-red-200">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Informasi Aset -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 h-fit">
            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Informasi Aset</h2>
            
            <div class="flex justify-center mb-6">
                <div class="text-center">
                    <svg id="barcode-svg" class="h-16 mx-auto mb-2"></svg>
                    <div class="font-mono font-bold text-lg tracking-widest text-slate-800">{{ $laptop->asset_code }}</div>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Merk & Model</span>
                    <span class="font-bold text-slate-800">{{ $laptop->brand_model }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Serial Number</span>
                    <span class="font-bold text-slate-800">{{ $laptop->serial_number }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Prosesor</span>
                    <span class="font-semibold text-slate-700">{{ $laptop->cpu ?? '-' }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-50 pb-2">
                    <span class="text-slate-500">Memori (RAM)</span>
                    <span class="font-semibold text-slate-700">{{ $laptop->ram ?? '-' }}</span>
                </div>
                <div class="flex justify-between pb-2">
                    <span class="text-slate-500">Penyimpanan</span>
                    <span class="font-semibold text-slate-700">{{ $laptop->ssd ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Form Serah Terima -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">Form Penerima</h2>
            
            <form action="{{ route('inventory.assign.process', $laptop->id) }}" method="POST" id="assignForm">
                @csrf
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Karyawan (Penerima)</label>
                    <select name="user_id" id="userSelect" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#2A435D]/20 focus:border-[#2A435D] outline-none">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2 flex justify-between">
                        Tanda Tangan Digital Penerima (Karyawan)
                        <button type="button" id="clearPadUser" class="text-xs text-red-500 hover:text-red-700">Hapus (Clear)</button>
                    </label>
                    <div class="border-2 border-dashed border-slate-300 rounded-xl overflow-hidden bg-slate-50 relative">
                        <canvas id="signaturePadUser" class="w-full h-48 cursor-crosshair touch-none"></canvas>
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center text-slate-300 text-2xl font-bold opacity-30 select-none">Tanda Tangan Karyawan</div>
                    </div>
                    <input type="hidden" name="digital_signature" id="digitalSignatureInput" required>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-2 flex justify-between">
                        Tanda Tangan Admin IT (Penyerah)
                        <button type="button" id="clearPadAdmin" class="text-xs text-red-500 hover:text-red-700">Hapus (Clear)</button>
                    </label>
                    <div class="border-2 border-dashed border-slate-300 rounded-xl overflow-hidden bg-slate-50 relative">
                        <canvas id="signaturePadAdmin" class="w-full h-48 cursor-crosshair touch-none"></canvas>
                        <div class="absolute inset-0 pointer-events-none flex items-center justify-center text-slate-300 text-2xl font-bold opacity-30 select-none">Tanda Tangan Admin IT</div>
                    </div>
                    <input type="hidden" name="admin_signature" id="adminSignatureInput" required>
                </div>

                <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg mb-6 text-xs text-blue-800 leading-relaxed">
                    Dengan menandatangani form ini, Karyawan menyatakan telah menerima barang tersebut di atas dalam kondisi baik dan bersedia mematuhi peraturan perusahaan terkait penggunaan dan penjagaan aset IT.
                </div>

                <button type="button" id="submitBtn" class="w-full py-3 bg-[#2A435D] hover:bg-[#3D5B7A] text-white font-bold rounded-lg shadow-lg shadow-[#2A435D]/30 transition-all flex justify-center items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Konfirmasi & Terima Barang
                </button>
            </form>
        </div>
    </div>
</div>

<!-- jQuery (Diperlukan untuk Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Styling Select2 agar mirip Tailwind CSS / Form Input Lainnya */
    .select2-container .select2-selection--single {
        height: 46px !important;
        border-color: #cbd5e1 !important;
        border-radius: 0.5rem !important;
        background-color: #f8fafc !important;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        line-height: normal !important;
        padding-left: 1rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 10px !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #2A435D !important;
        outline: none !important;
        box-shadow: 0 0 0 2px rgba(42, 67, 93, 0.2) !important;
    }
    .select2-dropdown {
        border-color: #cbd5e1 !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Signature Pad & JsBarcode Script -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.5/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    $(document).ready(function() {
        $('#userSelect').select2({
            placeholder: "-- Pilih Karyawan --",
            allowClear: true,
            width: '100%'
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        // Generate Barcode
        JsBarcode("#barcode-svg", "{{ $laptop->barcode }}", {
            format: "CODE128",
            width: 2,
            height: 40,
            displayValue: false,
            background: "transparent",
            lineColor: "#2A435D"
        });

        const canvasUser = document.getElementById('signaturePadUser');
        const canvasAdmin = document.getElementById('signaturePadAdmin');
        
        // Sesuaikan resolusi canvas
        function resizeCanvas() {
            const ratio =  Math.max(window.devicePixelRatio || 1, 1);
            
            canvasUser.width = canvasUser.offsetWidth * ratio;
            canvasUser.height = canvasUser.offsetHeight * ratio;
            canvasUser.getContext("2d").scale(ratio, ratio);

            canvasAdmin.width = canvasAdmin.offsetWidth * ratio;
            canvasAdmin.height = canvasAdmin.offsetHeight * ratio;
            canvasAdmin.getContext("2d").scale(ratio, ratio);
        }
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        const signaturePadUser = new SignaturePad(canvasUser, {
            penColor: "rgb(15, 23, 42)", 
            backgroundColor: "rgba(255, 255, 255, 0)" 
        });

        const signaturePadAdmin = new SignaturePad(canvasAdmin, {
            penColor: "rgb(15, 23, 42)", 
            backgroundColor: "rgba(255, 255, 255, 0)" 
        });

        document.getElementById('clearPadUser').addEventListener('click', function () {
            signaturePadUser.clear();
        });

        document.getElementById('clearPadAdmin').addEventListener('click', function () {
            signaturePadAdmin.clear();
        });

        document.getElementById('submitBtn').addEventListener('click', function () {
            if (signaturePadUser.isEmpty() || signaturePadAdmin.isEmpty()) {
                alert("Mohon isi KEDUA tanda tangan digital (Karyawan & Admin IT) terlebih dahulu sebelum melakukan konfirmasi.");
                return;
            }

            // Ambil data gambar base64
            document.getElementById('digitalSignatureInput').value = signaturePadUser.toDataURL("image/png");
            document.getElementById('adminSignatureInput').value = signaturePadAdmin.toDataURL("image/png");
            
            // Submit form
            document.getElementById('assignForm').submit();
        });
    });
</script>
@endsection

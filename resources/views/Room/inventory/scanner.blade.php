@extends('Room.layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-slate-800">Audit & Scanner Aset</h1>
        <p class="text-sm text-slate-500">Scan barcode fisik laptop untuk pengecekan audit berkala.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 bg-slate-50 border-b border-slate-100">
            <form id="scannerForm" class="relative max-w-xl mx-auto">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Input Barcode / Asset Code</label>
                <div class="flex gap-2">
                    <input type="text" id="barcodeInput" autofocus required autocomplete="off" placeholder="Scan atau ketik kode LT-XXXX-XXXX" class="w-full px-5 py-4 text-lg font-mono font-bold tracking-wider border-2 border-slate-300 rounded-xl focus:border-[#2A435D] focus:ring-4 focus:ring-[#2A435D]/20 outline-none transition-all">
                    <button type="submit" class="bg-[#2A435D] text-white px-6 py-4 rounded-xl font-bold hover:bg-[#3D5B7A] transition flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    </button>
                </div>
                <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-1 justify-center">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Arahkan cursor ke kolom input jika menggunakan alat scanner fisik
                </div>

                <div class="mt-6 border-t border-slate-200 pt-6 text-center">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Atau Gunakan Kamera / Gambar</p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mx-auto">
                        <button type="button" id="startCameraBtn" class="bg-emerald-500 text-white px-5 py-2.5 rounded-lg font-bold shadow hover:bg-emerald-600 transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Mulai Scan Kamera
                        </button>

                        <label class="bg-[#2A435D] text-white px-5 py-2.5 rounded-lg font-bold shadow hover:bg-[#3D5B7A] transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload Foto Barcode
                            <input type="file" id="fileInput" accept="image/*" class="hidden">
                        </label>
                    </div>

                    <button type="button" id="stopCameraBtn" class="hidden bg-red-500 text-white px-5 py-2.5 rounded-lg font-bold shadow hover:bg-red-600 transition flex items-center justify-center gap-2 mx-auto mt-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path></svg>
                        Hentikan Kamera
                    </button>
                    
                    <div id="readerContainer" class="hidden mt-4 mx-auto max-w-sm overflow-hidden rounded-xl border-2 border-slate-300">
                        <div id="reader" width="100%"></div>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-8">
            <div id="loadingIndicator" class="hidden text-center py-10">
                <svg class="animate-spin h-8 w-8 text-[#2A435D] mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="text-slate-500 font-medium">Memeriksa data aset...</div>
            </div>

            <div id="resultCard" class="hidden max-w-md mx-auto">
                <!-- Diisi via JavaScript -->
            </div>
            
            <div id="idleState" class="text-center py-16 text-slate-300">
                <svg class="w-24 h-24 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <div class="text-lg font-medium text-slate-400">Belum ada data di-scan</div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" type="text/javascript"></script>
<script>
    document.getElementById('scannerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const barcode = document.getElementById('barcodeInput').value;
        const resultCard = document.getElementById('resultCard');
        const loading = document.getElementById('loadingIndicator');
        const idle = document.getElementById('idleState');
        
        if(!barcode) return;

        // Reset UI
        idle.classList.add('hidden');
        resultCard.classList.add('hidden');
        loading.classList.remove('hidden');

        // Fetch API
        fetch("{{ route('inventory.audit.process') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ barcode: barcode })
        })
        .then(response => response.json())
        .then(data => {
            loading.classList.add('hidden');
            resultCard.classList.remove('hidden');

            if(data.success) {
                // Berhasil
                resultCard.innerHTML = `
                    <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-6 text-center shadow-lg shadow-green-100/50 transform transition-all animate-[bounce_0.5s_ease-out]">
                        <div class="w-16 h-16 bg-green-500 rounded-full text-white flex items-center justify-center mx-auto mb-4 shadow-md">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-xl font-black text-green-800 mb-1">Aset Ditemukan!</h3>
                        <p class="text-sm text-green-600 font-medium mb-6">Audit fisik berhasil dicatat ke sistem.</p>
                        
                        <div class="bg-white rounded-xl p-4 text-left border border-green-100">
                            <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Kode Aset</div>
                            <div class="font-mono text-lg font-black text-slate-800 mb-3">${data.laptop.asset_code}</div>
                            
                            <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Merek / Model</div>
                            <div class="font-bold text-slate-700 mb-3">${data.laptop.brand_model}</div>
                            
                            <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Status Saat Ini</div>
                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-bold">${data.laptop.status}</span>
                        </div>
                    </div>
                `;
                
                // Clear input untuk scan berikutnya
                document.getElementById('barcodeInput').value = '';
                document.getElementById('barcodeInput').focus();
            } else {
                // Gagal / Tidak ditemukan
                resultCard.innerHTML = `
                    <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-6 text-center shadow-lg shadow-red-100/50">
                        <div class="w-16 h-16 bg-red-500 rounded-full text-white flex items-center justify-center mx-auto mb-4 shadow-md">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                        <h3 class="text-xl font-black text-red-800 mb-2">Aset Tidak Ditemukan</h3>
                        <p class="text-sm text-red-600 font-medium">${data.message}</p>
                    </div>
                `;
                document.getElementById('barcodeInput').select();
            }
        })
        .catch(err => {
            loading.classList.add('hidden');
            alert("Terjadi kesalahan jaringan.");
        });
    });

    // ==========================================
    // 📸 HTML5 QR Code Scanner (Camera & File)
    // ==========================================
    let html5QrcodeScanner = null;
    let html5QrcodeFile = null; // Inisialisasi nanti
    
    const startBtn = document.getElementById('startCameraBtn');
    const stopBtn = document.getElementById('stopCameraBtn');
    const readerContainer = document.getElementById('readerContainer');
    const fileInput = document.getElementById('fileInput');
    
    startBtn.addEventListener('click', function() {
        readerContainer.classList.remove('hidden');
        startBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');
        
        // Initialize Scanner
        html5QrcodeScanner = new Html5Qrcode("reader");
        
        const config = { fps: 10, qrbox: { width: 250, height: 100 } };
        
        html5QrcodeScanner.start({ facingMode: "environment" }, config, 
            (decodedText, decodedResult) => {
                // Success Callback
                document.getElementById('barcodeInput').value = decodedText;
                
                // Stop the scanner immediately upon successful read
                html5QrcodeScanner.stop().then((ignore) => {
                    readerContainer.classList.add('hidden');
                    startBtn.classList.remove('hidden');
                    stopBtn.classList.add('hidden');
                    
                    // Trigger form submit
                    document.getElementById('scannerForm').dispatchEvent(new Event('submit'));
                }).catch((err) => {
                    console.log("Error stopping scanner: ", err);
                });
            },
            (errorMessage) => {
                // parse error, ignore it.
            })
        .catch((err) => {
            alert("Gagal mengakses kamera. Pastikan browser memiliki izin (permission) akses kamera.");
            startBtn.classList.remove('hidden');
            stopBtn.classList.add('hidden');
            readerContainer.classList.add('hidden');
        });
    });

    stopBtn.addEventListener('click', function() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().then((ignore) => {
                readerContainer.classList.add('hidden');
                startBtn.classList.remove('hidden');
                stopBtn.classList.add('hidden');
            }).catch((err) => {
                console.log(err);
            });
        }
    });

    // Handle Upload File Image
    fileInput.addEventListener('change', e => {
        if (e.target.files.length == 0) {
            return;
        }

        // Hentikan kamera jika sedang berjalan
        if (html5QrcodeScanner && html5QrcodeScanner.getState() === 2) {
            html5QrcodeScanner.stop().then(ignore => {
                startBtn.classList.remove('hidden');
                stopBtn.classList.add('hidden');
                readerContainer.classList.add('hidden');
                processFile(e.target.files[0]);
            });
        } else {
            processFile(e.target.files[0]);
        }
    });

    function processFile(imageFile) {
        document.getElementById('loadingIndicator').classList.remove('hidden');
        document.getElementById('idleState').classList.add('hidden');
        document.getElementById('resultCard').classList.add('hidden');

        if (!html5QrcodeFile) {
            html5QrcodeFile = new Html5Qrcode("reader");
        }

        html5QrcodeFile.scanFile(imageFile, true)
            .then(decodedText => {
                // success
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('barcodeInput').value = decodedText;
                
                // Trigger form submit
                document.getElementById('scannerForm').dispatchEvent(new Event('submit'));
                
                // Reset file input
                fileInput.value = "";
            })
            .catch(err => {
                // failure
                document.getElementById('loadingIndicator').classList.add('hidden');
                document.getElementById('idleState').classList.remove('hidden');
                alert("Barcode tidak ditemukan pada gambar yang diunggah. Coba foto yang lebih jelas.");
                // Reset file input
                fileInput.value = "";
            });
    }
</script>
@endsection

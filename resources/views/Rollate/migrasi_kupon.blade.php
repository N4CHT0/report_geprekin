@php
    $outletOptions = $outlets->map(fn($o) => [
        'value' => (string) $o->id,
        'name'  => $o->nama_outlet,
        'city'  => $o->kota ?? '',
        'label' => $o->nama_outlet . ($o->kota ? ' (' . $o->kota . ')' : ''),
    ])->values();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Migrasi Kupon — Geprekin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e4d8b4; border-radius: 4px; }

        .upload-zone {
            background: repeating-linear-gradient(
                -45deg,
                transparent, transparent 6px,
                rgba(245,166,35,0.04) 6px,
                rgba(245,166,35,0.04) 12px
            );
            transition: all 0.2s ease;
        }
        .upload-zone.drag-over {
            background: rgba(245,166,35,0.08);
            border-color: #F5A623 !important;
        }

        .thumb-remove { opacity: 0; transition: opacity .15s; }
        .thumb-item:hover .thumb-remove { opacity: 1; }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .5; transform: scale(.8); }
        }
        .pulse-dot { animation: pulse-dot 1.4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-[#FAFAF8] text-[#1C1408] antialiased min-h-screen" x-data="migrasiApp()">

    {{-- HEADER --}}
    <div class="w-full bg-white border-b border-[#EEE8D8] py-8 px-4 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-[#FFF8EC] border border-[#EEE8D8] rounded-2xl overflow-hidden mb-4">
            <img src="{{ asset('/img/logo2.jpg') }}" alt="Logo" class="w-10 h-10 object-contain" onerror="this.style.display='none'">
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1C1408]">Migrasi Kupon Undian</h1>
        <p class="mt-1 text-sm text-[#9A8560]">Unggah foto kupon fisik, AI akan mengekstrak data secara otomatis</p>
    </div>

    <div class="max-w-xl mx-auto px-4 py-8 space-y-5">

        {{-- FORM CARD --}}
        <div class="bg-white border border-[#EEE8D8] rounded-2xl shadow-sm">
            <div class="h-1 w-full bg-[#F5A623] rounded-t-2xl"></div>
            <div class="p-6 sm:p-8">
                <form @submit.prevent="submitForm" class="space-y-6">
                    
                    {{-- Opsi Verifikasi --}}
                    <div>
                        <label class="block text-[11px] font-bold text-[#9A8560] uppercase tracking-wider mb-2">Mode Verifikasi</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" x-model="verifyMode" value="auto" class="peer hidden">
                                <div class="p-3 border rounded-xl text-center peer-checked:border-[#F5A623] peer-checked:bg-[#FFF8EC] transition">
                                    <div class="font-bold text-sm">Otomatis</div>
                                    <div class="text-xs text-[#9A8560] mt-1">Langsung diverifikasi jika terbaca</div>
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" x-model="verifyMode" value="manual" class="peer hidden">
                                <div class="p-3 border rounded-xl text-center peer-checked:border-[#F5A623] peer-checked:bg-[#FFF8EC] transition">
                                    <div class="font-bold text-sm">Manual Review</div>
                                    <div class="text-xs text-[#9A8560] mt-1">Masuk antrean untuk ditinjau admin</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- MULTI-UPLOAD ZONE --}}
                    <div>
                        <div
                            class="upload-zone border-2 border-dashed border-[#E8DFC8] rounded-xl p-5 transition-colors cursor-pointer"
                            :class="{ 'drag-over': isDragging }"
                            @click="$refs.fileInput.click()"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleDrop($event)">
                            <div class="flex flex-col items-center gap-2 text-center pointer-events-none">
                                <div class="w-10 h-10 bg-[#FFF8EC] rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#F5A623]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[#1C1408]">Klik atau seret foto kupon ke sini</p>
                                    <p class="text-xs text-[#9A8560] mt-0.5">Bisa pilih banyak sekaligus</p>
                                </div>
                            </div>
                        </div>

                        <input type="file" x-ref="fileInput" class="hidden" accept="image/*" multiple @change="handleFileAdd($event)">

                        <template x-if="fotoFiles.length > 0">
                            <div class="mt-3">
                                <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                    <template x-for="(f, i) in fotoFiles" :key="i">
                                        <div class="thumb-item relative group aspect-square rounded-lg overflow-hidden border bg-black">
                                            <img :src="f.preview" class="w-full h-full object-cover opacity-80">
                                            <button type="button" @click="removeFile(i)" class="thumb-remove absolute inset-0 flex items-center justify-center bg-black/40 text-white transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- OUTLET SELECTOR (Optional) --}}
                    <div x-data="outletSelect()" class="relative z-10">
                        <label class="block text-[11px] font-bold text-[#9A8560] uppercase tracking-wider mb-2">Asal Outlet (Opsional)</label>
                        <div class="relative">
                            <div @click="open = !open" class="w-full text-sm border border-[#E8DFC8] bg-[#FAFAF8] rounded-xl p-3 cursor-pointer flex justify-between items-center transition" :class="{'ring-2 ring-[#F5A623]/30 border-[#F5A623]': open}">
                                <span x-text="selectedLabel || '— Pilih Outlet (Terapkan untuk semua kupon ini) —'" :class="{'text-[#9A8560]': !selectedOutlet}"></span>
                                <svg class="w-4 h-4 text-[#9A8560] transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            
                            <div x-show="open" @click.away="open = false" x-transition.opacity class="absolute top-full mt-2 w-full bg-white border border-[#EEE8D8] rounded-xl shadow-lg z-20 overflow-hidden" style="display: none;">
                                <div class="p-3 border-b border-[#EEE8D8] bg-[#FAFAF8]">
                                    <div class="relative">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <input type="text" x-model="search" x-ref="searchInput" class="w-full text-sm border border-[#E8DFC8] rounded-lg pl-9 p-2 focus:outline-none focus:ring-1 focus:ring-[#F5A623] focus:border-[#F5A623]" placeholder="Cari nama outlet atau kota...">
                                    </div>
                                </div>
                                <ul class="max-h-56 overflow-y-auto p-1">
                                    <li @click="selectOption('', '— Pilih Outlet (Terapkan untuk semua kupon ini) —')" class="px-3 py-2 text-sm text-gray-500 cursor-pointer hover:bg-[#FFF8EC] hover:text-[#F5A623] rounded transition">
                                        — Kosongkan Pilihan —
                                    </li>
                                    <template x-for="option in filteredOptions" :key="option.value">
                                        <li @click="selectOption(option.value, option.label)" class="px-3 py-2.5 text-sm cursor-pointer hover:bg-[#FFF8EC] hover:text-[#F5A623] rounded transition" :class="{'bg-[#FFF8EC] text-[#F5A623] font-bold': option.value === selectedOutlet}">
                                            <span x-text="option.label"></span>
                                        </li>
                                    </template>
                                    <li x-show="filteredOptions.length === 0" class="px-3 py-4 text-sm text-center text-gray-500">
                                        Outlet tidak ditemukan
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" :disabled="isSubmitting || fotoFiles.length === 0"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-[#1C1408] text-white text-sm font-bold rounded-xl transition-all disabled:opacity-50">
                        <template x-if="!isSubmitting">
                            <span x-text="'Mulai Proses ' + fotoFiles.length + ' Kupon'"></span>
                        </template>
                        <template x-if="isSubmitting">
                            <span class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                Mengunggah...
                            </span>
                        </template>
                    </button>
                </form>
            </div>
        </div>

        {{-- STATUS POLLING PANEL --}}
        <div x-cloak x-show="statusPolling.length > 0" class="bg-white border border-[#EEE8D8] rounded-2xl overflow-hidden shadow-sm">
            <div class="h-1 w-full bg-[#F5A623]"></div>
            <div class="p-6">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-1.5 h-5 bg-[#F5A623] rounded-full"></div>
                    <h2 class="text-sm font-bold text-[#1C1408] uppercase tracking-wider">Status Pemrosesan</h2>
                </div>
                <div class="space-y-3">
                    <template x-for="(item, idx) in statusPolling" :key="item.id">
                        <div class="rounded-xl border p-4 transition-colors"
                             :class="{
                                'bg-[#FFFBF0]': item.status === 'pending',
                                'bg-green-50': item.status === 'verified',
                                'bg-red-50': item.status === 'failed_ocr',
                                'bg-amber-50': item.status === 'need_review',
                             }">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex gap-2">
                                    <span class="text-xs font-bold px-2 py-1 bg-white border rounded shadow-sm">ID: <span x-text="item.id"></span></span>
                                    <span class="text-xs font-bold px-2 py-1 bg-white border rounded shadow-sm" x-text="item.status_label"></span>
                                </div>
                            </div>
                            <div class="text-sm space-y-1">
                                <p><span class="text-gray-500">Nama:</span> <span class="font-semibold" x-text="item.nama_lengkap || '-'"></span></p>
                                <p><span class="text-gray-500">Telp:</span> <span class="font-semibold" x-text="item.no_telp || '-'"></span></p>
                                <p><span class="text-gray-500">KTP:</span> <span class="font-semibold" x-text="item.no_ktp || '-'"></span></p>
                                <p><span class="text-gray-500">Alamat:</span> <span class="font-semibold" x-text="item.alamat || '-'"></span></p>
                            </div>
                            <template x-if="item.status === 'verified'">
                                <div class="mt-3 pt-3 border-t flex items-center justify-between">
                                    <div class="text-green-700 font-bold">Nomor Undian: <span class="font-mono text-lg" x-text="item.nomor_undian"></span></div>
                                </div>
                            </template>
                            
                            <template x-if="item.status === 'need_review'">
                                <div class="mt-3 pt-3 border-t border-amber-200">
                                    <div x-show="!item.isEditing">
                                        <button @click="mulaiEdit(item)" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-bold transition">Perbaiki & Verifikasi</button>
                                    </div>
                                    <div x-show="item.isEditing" class="space-y-3 mt-2 p-4 bg-white rounded-xl border border-amber-200 shadow-sm">
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 uppercase">Nama Lengkap</label>
                                            <input type="text" x-model="item.editForm.nama_lengkap" class="w-full text-sm border border-gray-300 rounded p-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" placeholder="Nama Lengkap">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 uppercase">No Telepon/HP</label>
                                            <input type="text" x-model="item.editForm.no_telp" class="w-full text-sm border border-gray-300 rounded p-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" placeholder="08xxxxxxxx">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 uppercase">No KTP (NIK)</label>
                                            <input type="text" x-model="item.editForm.no_ktp" class="w-full text-sm border border-gray-300 rounded p-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" placeholder="16 Digit NIK">
                                        </div>
                                        <div>
                                            <label class="text-[10px] font-bold text-gray-500 uppercase">Alamat</label>
                                            <input type="text" x-model="item.editForm.alamat" class="w-full text-sm border border-gray-300 rounded p-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition" placeholder="Alamat">
                                        </div>
                                        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
                                            <button @click="simpanEdit(item)" class="flex-1 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-bold transition flex justify-center items-center gap-2" :disabled="item.isSaving">
                                                <span x-show="item.isSaving">Menyimpan...</span>
                                                <span x-show="!item.isSaving">Simpan & Buat Undian</span>
                                            </button>
                                            <button @click="item.isEditing = false" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg text-xs font-bold text-gray-700 transition" :disabled="item.isSaving">Batal</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <script>
    function outletSelect() {
        return {
            open: false,
            search: '',
            options: @json($outletOptions),
            
            get filteredOptions() {
                if (this.search === '') return this.options;
                const s = this.search.toLowerCase();
                return this.options.filter(opt => opt.label.toLowerCase().includes(s));
            },
            
            get selectedLabel() {
                if (!this.selectedOutlet) return '';
                const found = this.options.find(opt => opt.value === String(this.selectedOutlet));
                return found ? found.label : '';
            },
            
            selectOption(value, label) {
                this.selectedOutlet = value;
                this.open = false;
                this.search = '';
            },

            init() {
                this.$watch('open', value => {
                    if (value) {
                        setTimeout(() => this.$refs.searchInput.focus(), 50);
                    }
                });
            }
        }
    }

    function migrasiApp() {
        return {
            verifyMode: 'auto',
            selectedOutlet: '',
            fotoFiles: [],
            isDragging: false,
            isSubmitting: false,
            submitIds: [],
            pollingTimer: null,
            statusPolling: [],

            handleFileAdd(event) {
                const files = Array.from(event.target.files || []);
                files.forEach(file => {
                    this.fotoFiles.push({ file, preview: URL.createObjectURL(file) });
                });
                event.target.value = '';
            },

            handleDrop(event) {
                this.isDragging = false;
                const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                files.forEach(file => {
                    this.fotoFiles.push({ file, preview: URL.createObjectURL(file) });
                });
            },

            removeFile(idx) {
                URL.revokeObjectURL(this.fotoFiles[idx].preview);
                this.fotoFiles.splice(idx, 1);
            },

            async submitForm() {
                if (this.fotoFiles.length === 0) return;
                this.isSubmitting = true;

                const formData = new FormData();
                formData.append('verify_mode', this.verifyMode);
                if (this.selectedOutlet) {
                    formData.append('outlet_id', this.selectedOutlet);
                }
                this.fotoFiles.forEach((f, idx) => formData.append(`foto_kupon[${idx}]`, f.file));

                try {
                    const res = await fetch("{{ route('rollate.migrasi.store') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: formData
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.fotoFiles.forEach(f => URL.revokeObjectURL(f.preview));
                        this.fotoFiles = [];
                        this.submitIds = data.ids;
                        this.startPolling();
                    } else {
                        alert(data.message || 'Gagal upload');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan jaringan.');
                }
                this.isSubmitting = false;
            },

            startPolling() {
                if (this.pollingTimer) clearInterval(this.pollingTimer);
                this.fetchStatus();
                this.pollingTimer = setInterval(() => this.fetchStatus(), 3000);
            },

            mulaiEdit(item) {
                item.editForm = {
                    nama_lengkap: item.nama_lengkap || '',
                    no_telp: item.no_telp || '',
                    no_ktp: item.no_ktp || '',
                    alamat: item.alamat || ''
                };
                item.isEditing = true;
                item.isSaving = false;
            },

            async simpanEdit(item) {
                item.isSaving = true;
                try {
                    const formData = new FormData();
                    formData.append('nama_lengkap', item.editForm.nama_lengkap);
                    formData.append('no_telp', item.editForm.no_telp);
                    formData.append('no_ktp', item.editForm.no_ktp);
                    formData.append('alamat', item.editForm.alamat);

                    const res = await fetch(`/undian/migrasi/update/${item.id}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: formData
                    });
                    const data = await res.json();
                    
                    if (data.success) {
                        item.status = 'verified';
                        item.status_label = 'Berhasil Diverifikasi (Manual)';
                        item.nomor_undian = data.nomor_undian;
                        item.nama_lengkap = item.editForm.nama_lengkap;
                        item.no_telp = item.editForm.no_telp;
                        item.no_ktp = item.editForm.no_ktp;
                        item.alamat = item.editForm.alamat;
                        item.isEditing = false;
                    } else {
                        alert(data.message || 'Gagal menyimpan data.');
                    }
                } catch (e) {
                    alert('Terjadi kesalahan jaringan saat menyimpan.');
                }
                item.isSaving = false;
            },

            async fetchStatus() {
                if (!this.submitIds.length) return;
                try {
                    const url = new URL("{{ route('rollate.migrasi.status') }}", window.location.origin);
                    this.submitIds.forEach(id => url.searchParams.append('ids[]', id));

                    const res = await fetch(url);
                    const data = await res.json();
                    
                    if (Array.isArray(data)) {
                        data.forEach(newItem => {
                            const existingItem = this.statusPolling.find(i => i.id === newItem.id);
                            if (existingItem) {
                                if (!existingItem.isEditing) {
                                    Object.assign(existingItem, newItem);
                                }
                            } else {
                                newItem.isEditing = false;
                                newItem.isSaving = false;
                                newItem.editForm = {};
                                this.statusPolling.push(newItem);
                            }
                        });
                        
                        const stillPending = this.statusPolling.some(item => item.status === 'pending');
                        if (!stillPending && this.pollingTimer) {
                            clearInterval(this.pollingTimer);
                        }
                    }
                } catch (e) {
                    console.error("Polling error", e);
                }
            }
        }
    }
    </script>
</body>
</html>
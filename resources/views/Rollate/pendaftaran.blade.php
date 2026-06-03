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
    <title>Daftar Undian — Geprekin</title>

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

        :root {
            --gold: #F5A623;
            --gold-dark: #C8860A;
            --gold-light: #FFF8EC;
            --ink: #1C1408;
        }

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
            border-color: var(--gold) !important;
        }

        .thumb-remove { opacity: 0; transition: opacity .15s; }
        .thumb-item:hover .thumb-remove { opacity: 1; }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .5; transform: scale(.8); }
        }
        .pulse-dot { animation: pulse-dot 1.4s ease-in-out infinite; }

        @keyframes slide-in { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slide-out { from { opacity: 1; } to { opacity: 0; } }
        .toast-enter { animation: slide-in .3s ease forwards; }
        .toast-leave  { animation: slide-out .2s ease forwards; }

        input[type="date"]::-webkit-calendar-picker-indicator { opacity: .4; cursor: pointer; }

        /* ── Searchable dropdown ── */
        .outlet-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0; right: 0;
            background: #fff;
            border: 1px solid #E8DFC8;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(28,20,8,0.10);
            z-index: 50;
            overflow: hidden;
            max-height: 220px;
            display: flex;
            flex-direction: column;
        }
        .outlet-search-wrap {
            padding: 8px 10px;
            border-bottom: 1px solid #EEE8D8;
            flex-shrink: 0;
        }
        .outlet-search-input {
            width: 100%;
            padding: 6px 10px 6px 30px;
            font-size: 13px;
            background: #FAFAF8;
            border: 1px solid #E8DFC8;
            border-radius: 7px;
            outline: none;
            color: #1C1408;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .outlet-search-input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(245,166,35,0.15);
        }
        .outlet-list {
            overflow-y: auto;
            flex: 1;
        }
        .outlet-option {
            padding: 9px 12px;
            font-size: 13px;
            cursor: pointer;
            color: #1C1408;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background .1s;
        }
        .outlet-option:hover,
        .outlet-option.active {
            background: #FFF8EC;
        }
        .outlet-option.selected {
            background: #FFF8EC;
            color: var(--gold-dark);
            font-weight: 600;
        }
        .outlet-option .outlet-city {
            font-size: 11px;
            color: #9A8560;
            margin-left: auto;
        }
        .outlet-option-empty {
            padding: 14px 12px;
            font-size: 13px;
            color: #C4B89A;
            text-align: center;
        }
        /* Trigger button */
        .outlet-trigger {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            background: #FAFAF8;
            border: 1px solid #E8DFC8;
            border-radius: 8px;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #1C1408;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all .15s;
            outline: none;
        }
        .outlet-trigger:focus,
        .outlet-trigger.open {
            background: #fff;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(245,166,35,0.15);
        }
        .outlet-trigger .placeholder { color: #C4B89A; }
        .outlet-trigger .chevron {
            width: 16px; height: 16px;
            flex-shrink: 0;
            color: #C4B89A;
            transition: transform .2s;
        }
        .outlet-trigger.open .chevron { transform: rotate(180deg); }
        .outlet-trigger .clear-btn {
            width: 16px; height: 16px; flex-shrink: 0;
            color: #C4B89A; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .outlet-trigger .clear-btn:hover { color: #9A8560; }

        /* Highlight match in search results */
        mark {
            background: rgba(245,166,35,0.25);
            color: inherit;
            border-radius: 2px;
            padding: 0 1px;
        }
    </style>
</head>
<body class="bg-[#FAFAF8] text-[#1C1408] antialiased min-h-screen" x-data="undianApp()">

    {{-- HEADER --}}
    <div class="w-full bg-white border-b border-[#EEE8D8] py-8 px-4 text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-[#FFF8EC] border border-[#EEE8D8] rounded-2xl overflow-hidden mb-4">
            <img src="{{ asset('/img/logo2.jpg') }}" alt="Logo" class="w-10 h-10 object-contain" onerror="this.style.display='none'">
        </div>
        <h1 class="text-2xl font-extrabold tracking-tight text-[#1C1408]">Registrasi Undian</h1>
        <p class="mt-1 text-sm text-[#9A8560]">Unggah struk belanja untuk mendapatkan nomor undian resmi</p>
        <div class="flex items-center justify-center gap-3 mt-5">
            <div class="px-4 py-2 bg-[#FFF8EC] border border-[#EEE8D8] rounded-full text-xs font-semibold text-[#C8860A]">Min. Rp 10.000</div>
            <div class="px-4 py-2 bg-[#FFF8EC] border border-[#EEE8D8] rounded-full text-xs font-semibold text-[#C8860A]">1 Struk = 1 Nomor</div>
            <div class="px-4 py-2 bg-[#FFF8EC] border border-[#EEE8D8] rounded-full text-xs font-semibold text-[#C8860A]">Verifikasi Otomatis</div>
        </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-8 space-y-5">

        {{-- FORM CARD --}}
        <div class="bg-white border border-[#EEE8D8] rounded-2xl overflow-hidden shadow-sm">
            <div class="h-1 w-full bg-[#F5A623]"></div>
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-2.5 mb-6">
                    <div class="w-1.5 h-5 bg-[#F5A623] rounded-full"></div>
                    <h2 class="text-sm font-bold text-[#1C1408] uppercase tracking-wider">Data Peserta</h2>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-[#9A8560] uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                            <input type="text" x-model="formData.nama_lengkap" required maxlength="120" placeholder="Contoh: Budi Santoso"
                                class="w-full px-3.5 py-2.5 text-sm bg-[#FAFAF8] border border-[#E8DFC8] rounded-lg focus:bg-white focus:ring-2 focus:ring-[#F5A623]/30 focus:border-[#F5A623] outline-none transition placeholder-[#C4B89A]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-[#9A8560] uppercase tracking-wider mb-1.5">No. WhatsApp / HP <span class="text-red-400">*</span></label>
                            <input type="tel" x-model="formData.no_telp" required inputmode="numeric" maxlength="15" placeholder="08xxxxxxxxxx"
                                class="w-full px-3.5 py-2.5 text-sm bg-[#FAFAF8] border border-[#E8DFC8] rounded-lg focus:bg-white focus:ring-2 focus:ring-[#F5A623]/30 focus:border-[#F5A623] outline-none transition placeholder-[#C4B89A]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- ── SEARCHABLE OUTLET SELECT ── --}}
                        <div>
                            <label class="block text-[11px] font-bold text-[#9A8560] uppercase tracking-wider mb-1.5">Outlet Geprekin</label>
                            <div class="relative" x-data="outletSelect()" @keydown.escape="close()" @click.outside="close()">

                                {{-- Trigger --}}
                                <button
                                    type="button"
                                    class="outlet-trigger"
                                    :class="{ open: isOpen }"
                                    @click="toggle()"
                                    @keydown.enter.prevent="toggle()"
                                    @keydown.arrow-down.prevent="moveDown()"
                                    @keydown.arrow-up.prevent="moveUp()"
                                    :aria-expanded="isOpen">

                                    <span :class="selected ? '' : 'placeholder'"
                                          x-text="selected ? selected.label : '— Pilih Outlet (opsional) —'">
                                    </span>

                                    <div class="flex items-center gap-1">
                                        {{-- Clear button --}}
                                        <span x-show="selected"
                                              class="clear-btn"
                                              @click.stop="clearSelected()"
                                              title="Hapus pilihan">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                        </span>
                                        <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                                    </div>
                                </button>

                                {{-- Dropdown --}}
                                <div x-show="isOpen"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 translate-y-1"
                                     class="outlet-dropdown"
                                     x-cloak>

                                    {{-- Search input inside dropdown --}}
                                    <div class="outlet-search-wrap relative">
                                        <svg class="absolute left-[18px] top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-[#C4B89A] pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <input
                                            type="text"
                                            class="outlet-search-input"
                                            placeholder="Cari nama outlet atau kota..."
                                            x-model="query"
                                            x-ref="searchInput"
                                            @keydown.arrow-down.prevent="moveDown()"
                                            @keydown.arrow-up.prevent="moveUp()"
                                            @keydown.enter.prevent="selectHighlighted()"
                                            autocomplete="off"
                                            spellcheck="false">
                                    </div>

                                    {{-- Options list --}}
                                    <div class="outlet-list" x-ref="optionList">

                                        {{-- Opsi kosong/opsional --}}
                                        <div
                                            class="outlet-option"
                                            :class="{
                                                'active': highlighted === -1,
                                                'selected': !selected
                                            }"
                                            @mouseenter="highlighted = -1"
                                            @click="pick(null)">
                                            <span class="text-[#C4B89A] italic">— Tidak dipilih —</span>
                                        </div>

                                        <template x-for="(opt, i) in filtered" :key="opt.value">
                                            <div
                                                class="outlet-option"
                                                :class="{
                                                    'active': highlighted === i,
                                                    'selected': selected && selected.value === opt.value
                                                }"
                                                @mouseenter="highlighted = i"
                                                @click="pick(opt)"
                                                x-ref="option">
                                                <span x-html="highlight(opt.name)"></span>
                                                <span class="outlet-city" x-text="opt.city" x-show="opt.city"></span>
                                            </div>
                                        </template>

                                        <div x-show="filtered.length === 0" class="outlet-option-empty">
                                            Tidak ada outlet ditemukan
                                        </div>
                                    </div>
                                </div>

                                {{-- Hidden input biar bisa dipakai di formData --}}
                                <input type="hidden" :value="selected ? selected.value : ''" x-on:change="$dispatch('outlet-changed', selected?.value)">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-[#9A8560] uppercase tracking-wider mb-1.5">Tanggal Struk</label>
                            <input type="date" x-model="formData.tanggal_struk" max="{{ date('Y-m-d') }}"
                                class="w-full px-3.5 py-2.5 text-sm bg-[#FAFAF8] border border-[#E8DFC8] rounded-lg focus:bg-white focus:ring-2 focus:ring-[#F5A623]/30 focus:border-[#F5A623] outline-none transition text-[#1C1408]">
                        </div>
                    </div>

                    {{-- Divider --}}
                    <div class="flex items-center gap-3 pt-1">
                        <div class="h-px bg-[#EEE8D8] flex-1"></div>
                        <span class="text-[10px] font-bold text-[#C4B89A] uppercase tracking-widest">Upload Struk</span>
                        <div class="h-px bg-[#EEE8D8] flex-1"></div>
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
                                    <svg class="w-5 h-5 text-[#F5A623]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-[#1C1408]">Klik atau seret foto struk ke sini</p>
                                    <p class="text-xs text-[#9A8560] mt-0.5">JPG, PNG, WEBP · Bisa pilih banyak sekaligus</p>
                                </div>
                                <div class="flex gap-2 mt-1">
                                    <button type="button" @click.stop="bukaKamera()" class="px-3 py-1.5 text-xs font-semibold bg-white border border-[#E8DFC8] text-[#1C1408] rounded-lg hover:border-[#F5A623] transition pointer-events-auto flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Kamera
                                    </button>
                                    <button type="button" @click.stop="$refs.fileInput.click()" class="px-3 py-1.5 text-xs font-semibold bg-white border border-[#E8DFC8] text-[#1C1408] rounded-lg hover:border-[#F5A623] transition pointer-events-auto flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Galeri
                                    </button>
                                </div>
                            </div>
                        </div>

                        <input type="file" x-ref="fileInput" class="hidden" accept="image/*" multiple @change="handleFileAdd($event)">
                        <input type="file" x-ref="fileCamera" class="hidden" accept="image/*" capture="environment" @change="handleFileAdd($event)">

                        <template x-if="fotoFiles.length > 0">
                            <div class="mt-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-[#9A8560]" x-text="fotoFiles.length + ' struk dipilih'"></span>
                                    <button type="button" @click="clearAllFiles" class="text-xs font-semibold text-red-400 hover:text-red-600 transition">Hapus Semua</button>
                                </div>
                                <div class="grid grid-cols-4 gap-2">
                                    <template x-for="(f, i) in fotoFiles" :key="i">
                                        <div class="thumb-item relative group aspect-square rounded-lg overflow-hidden border border-[#EEE8D8] bg-[#FAFAF8]">
                                            <img :src="f.preview" class="w-full h-full object-cover">
                                            <button type="button" @click="removeFile(i)" class="thumb-remove absolute inset-0 bg-black/50 flex items-center justify-center text-white transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/60 to-transparent p-1">
                                                <span class="text-[9px] text-white font-bold leading-none" x-text="'#' + (i+1)"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <div class="aspect-square rounded-lg border-2 border-dashed border-[#E8DFC8] flex items-center justify-center cursor-pointer hover:border-[#F5A623] transition bg-white" @click="$refs.fileInput.click()">
                                        <svg class="w-5 h-5 text-[#C4B89A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" :disabled="isSubmitting || fotoFiles.length === 0"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-[#F5A623] hover:bg-[#E09510] text-[#1C1408] text-sm font-bold rounded-xl transition-all disabled:opacity-40 disabled:cursor-not-allowed mt-1">
                        <template x-if="!isSubmitting">
                            <span x-text="fotoFiles.length > 1 ? 'Daftar ' + fotoFiles.length + ' Struk Sekaligus →' : 'Daftar Sekarang →'"></span>
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
                    <h2 class="text-sm font-bold text-[#1C1408] uppercase tracking-wider">Status Pendaftaran</h2>
                    <span class="ml-auto text-xs text-[#9A8560]" x-text="statusPolling.length + ' struk'"></span>
                </div>
                <div class="space-y-2.5">
                    <template x-for="(item, idx) in statusPolling" :key="item.id">
                        <div class="rounded-xl border p-4 transition-colors"
                             :class="{
                                'border-[#EEE8D8] bg-[#FFFBF0]': item.status === 'pending',
                                'border-green-100 bg-green-50': item.status === 'verified',
                                'border-red-100 bg-red-50': item.status === 'failed_ocr',
                                'border-amber-100 bg-amber-50': item.status === 'need_review',
                             }">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="mt-0.5">
                                        <template x-if="item.status === 'pending'"><div class="w-2 h-2 rounded-full bg-[#F5A623] pulse-dot"></div></template>
                                        <template x-if="item.status === 'verified'"><div class="w-2 h-2 rounded-full bg-green-500"></div></template>
                                        <template x-if="item.status === 'failed_ocr' || item.status === 'need_review'"><div class="w-2 h-2 rounded-full bg-red-400"></div></template>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-[#1C1408]" x-text="'Struk #' + (idx + 1)"></p>
                                        <p class="text-xs text-[#9A8560] mt-0.5" x-text="item.status_label"></p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xs font-bold text-[#C8860A]" x-text="item.total_formatted"></p>
                                </div>
                            </div>
                            <template x-if="item.status === 'verified'">
                                <div class="mt-3 pt-3 border-t border-green-100 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold text-[#9A8560] uppercase tracking-wider">Nomor Undian</p>
                                        <p class="text-2xl font-black text-[#1C1408] font-mono tracking-widest leading-tight" x-text="item.nomor_undian"></p>
                                    </div>
                                    <a :href="'/undian/cetak/' + item.id" target="_blank"
                                        class="flex items-center gap-1.5 px-3.5 py-2 bg-[#F5A623] text-[#1C1408] text-xs font-bold rounded-lg hover:bg-[#E09510] transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Unduh Kartu
                                    </a>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <template x-if="statusPolling.some(s => s.status === 'pending')">
                    <div class="mt-3 flex items-center gap-2 text-xs text-[#9A8560]">
                        <svg class="animate-spin w-3.5 h-3.5 text-[#F5A623]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        Memproses struk yang masih pending...
                    </div>
                </template>
            </div>
        </div>

        {{-- CEK STATUS MANUAL --}}
        <div class="bg-white border border-[#EEE8D8] rounded-2xl overflow-hidden shadow-sm">
            <div class="h-1 w-full bg-[#EEE8D8]"></div>
            <div class="p-6">
                <div class="flex items-center gap-2.5 mb-1">
                    <div class="w-1.5 h-5 bg-[#EEE8D8] rounded-full"></div>
                    <h2 class="text-sm font-bold text-[#1C1408] uppercase tracking-wider">Cek Status Undian</h2>
                </div>
                <p class="text-xs text-[#9A8560] mb-4 ml-4">Cari berdasarkan nama lengkap atau nomor struk</p>
                <form @submit.prevent="checkStatusManual" class="space-y-2.5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#C4B89A]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </span>
                            <input type="text" x-model="searchNama" placeholder="Nama lengkap..."
                                class="w-full pl-9 pr-3.5 py-2.5 text-sm bg-[#FAFAF8] border border-[#E8DFC8] rounded-lg focus:ring-2 focus:ring-[#F5A623]/30 focus:border-[#F5A623] outline-none transition placeholder-[#C4B89A]">
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#C4B89A]">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <input type="text" x-model="searchNomorStruk" placeholder="Nomor struk..."
                                class="w-full pl-9 pr-3.5 py-2.5 text-sm bg-[#FAFAF8] border border-[#E8DFC8] rounded-lg focus:ring-2 focus:ring-[#F5A623]/30 focus:border-[#F5A623] outline-none transition placeholder-[#C4B89A]">
                        </div>
                    </div>
                    <button type="submit" :disabled="isSearching || (!searchNama && !searchNomorStruk)"
                        class="w-full py-2.5 bg-[#1C1408] text-white text-sm font-semibold rounded-lg hover:bg-[#2E2010] transition-colors disabled:opacity-40">
                        <span x-show="!isSearching">Cari Status</span>
                        <span x-show="isSearching" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                            Mencari...
                        </span>
                    </button>
                </form>
                <div x-cloak x-show="searchResult.length > 0" class="mt-4 space-y-2.5">
                    <p class="text-xs font-semibold text-[#9A8560]" x-text="searchResult.length + ' hasil ditemukan'"></p>
                    <template x-for="item in searchResult" :key="item.id">
                        <div class="p-3.5 border border-[#EEE8D8] rounded-xl bg-[#FAFAF8]">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-[#1C1408]" x-text="item.nama_lengkap"></p>
                                    <p class="text-xs text-[#9A8560] mt-0.5" x-text="item.status_label"></p>
                                    <p class="text-xs text-[#C4B89A] mt-0.5" x-text="item.total_formatted"></p>
                                </div>
                                <span class="shrink-0 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full border"
                                      :class="{
                                          'bg-amber-50 text-amber-600 border-amber-200': item.status === 'pending' || item.status === 'need_review',
                                          'bg-green-50 text-green-600 border-green-200': item.status === 'verified',
                                          'bg-red-50 text-red-500 border-red-200': item.status === 'failed_ocr',
                                      }" x-text="item.status"></span>
                            </div>
                            <template x-if="item.status === 'verified'">
                                <div class="mt-2.5 pt-2.5 border-t border-[#EEE8D8] flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] font-bold text-[#9A8560] uppercase tracking-wider">Nomor Undian</p>
                                        <p class="text-xl font-black font-mono text-[#1C1408] tracking-widest" x-text="item.nomor_undian"></p>
                                    </div>
                                    <a :href="'/undian/cetak/' + item.id" target="_blank"
                                        class="text-xs font-bold text-[#C8860A] underline underline-offset-2 hover:text-[#F5A623] transition">
                                        Unduh Kartu →
                                    </a>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
                <div x-cloak x-show="searchAttempted && searchResult.length === 0 && !isSearching"
                     class="mt-4 text-center text-sm text-[#9A8560] bg-[#FAFAF8] border border-[#EEE8D8] p-4 rounded-xl">
                    Tidak ditemukan hasil untuk pencarian ini.
                </div>
            </div>
        </div>

        <p class="text-center text-[11px] text-[#C4B89A] leading-relaxed pb-4">
            Syarat & ketentuan berlaku &middot; 1 struk = 1 nomor undian<br>
            Data dilindungi dengan enkripsi standar industri
        </p>
    </div>

    {{-- CAMERA MODAL --}}
    <div x-cloak x-show="showCamera" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90" @keydown.escape.window="tutupKamera()">
        <div class="bg-black w-full max-w-sm rounded-2xl overflow-hidden shadow-2xl relative" @click.away="tutupKamera()">
            <video x-ref="kameraVideo" autoplay playsinline muted class="w-full aspect-[4/3] object-cover bg-zinc-900"></video>
            <div class="absolute inset-x-0 bottom-0 p-5 flex justify-between items-center bg-gradient-to-t from-black/70 to-transparent">
                <button type="button" @click="flipKamera" class="p-2.5 text-white bg-white/10 hover:bg-white/20 rounded-full transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </button>
                <button type="button" @click="ambilFoto" class="w-14 h-14 rounded-full border-4 border-white flex items-center justify-center hover:scale-95 transition-transform">
                    <div class="w-10 h-10 bg-white rounded-full"></div>
                </button>
                <button type="button" @click="tutupKamera" class="p-2.5 text-white bg-white/10 hover:bg-white/20 rounded-full transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="absolute top-3 inset-x-0 text-center pointer-events-none">
                <span class="bg-black/50 text-white text-xs px-3 py-1.5 rounded-full backdrop-blur">Posisikan struk agar terbaca jelas</span>
            </div>
        </div>
    </div>
    <canvas x-ref="canvasCapture" class="hidden"></canvas>

    {{-- TOAST --}}
    <div x-cloak x-show="toast.show"
         class="fixed top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl max-w-xs w-full"
         :class="toast.type === 'error' ? 'bg-[#1C1408] text-white' : 'bg-[#F5A623] text-[#1C1408]'">
        <template x-if="toast.type === 'error'">
            <svg class="w-4 h-4 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </template>
        <template x-if="toast.type === 'success'">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </template>
        <p class="text-sm font-semibold" x-text="toast.message"></p>
    </div>

    <script>
    // ─────────────────────────────────────────────────────────
    // outletSelect() — komponen Alpine terpisah, bisa di-reuse
    // Data outlet diambil dari Blade, bukan fetch AJAX.
    // ─────────────────────────────────────────────────────────
    function outletSelect() {
        // Build options dari data Blade
        const allOptions = @json($outletOptions);

        return {
            allOptions,
            query: '',
            isOpen: false,
            selected: null,   // { value, name, city, label }
            highlighted: -1,  // -1 = opsi kosong, 0+ = index di filtered

            get filtered() {
                if (!this.query.trim()) return this.allOptions;
                const q = this.query.toLowerCase();
                return this.allOptions.filter(o =>
                    o.name.toLowerCase().includes(q) ||
                    (o.city && o.city.toLowerCase().includes(q))
                );
            },

            toggle() {
                this.isOpen ? this.close() : this.open();
            },

            open() {
                this.isOpen    = true;
                this.query     = '';
                this.highlighted = this.selected
                    ? this.filtered.findIndex(o => o.value === this.selected.value)
                    : -1;
                this.$nextTick(() => this.$refs.searchInput?.focus());
            },

            close() {
                this.isOpen = false;
                this.query  = '';
            },

            pick(opt) {
                this.selected = opt;
                // Sync ke formData outlet_id di parent scope
                this.$dispatch('outlet-picked', opt ? opt.value : '');
                this.close();
            },

            clearSelected() {
                this.selected = null;
                this.$dispatch('outlet-picked', '');
            },

            selectHighlighted() {
                if (this.highlighted === -1) {
                    this.pick(null);
                } else if (this.filtered[this.highlighted]) {
                    this.pick(this.filtered[this.highlighted]);
                }
            },

            moveDown() {
                if (!this.isOpen) { this.open(); return; }
                this.highlighted = Math.min(this.highlighted + 1, this.filtered.length - 1);
                this.scrollToHighlighted();
            },

            moveUp() {
                this.highlighted = Math.max(this.highlighted - 1, -1);
                this.scrollToHighlighted();
            },

            scrollToHighlighted() {
                this.$nextTick(() => {
                    const list = this.$refs.optionList;
                    if (!list) return;
                    // +1 karena ada opsi kosong di index 0 secara visual
                    const items = list.querySelectorAll('.outlet-option');
                    const target = items[this.highlighted + 1];
                    target?.scrollIntoView({ block: 'nearest' });
                });
            },

            // Highlight teks yang cocok dengan query
            highlight(text) {
                if (!this.query.trim()) return this.escHtml(text);
                const q   = this.query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const re  = new RegExp(`(${q})`, 'gi');
                return this.escHtml(text).replace(re, '<mark>$1</mark>');
            },

            escHtml(str) {
                return str.replace(/[&<>"']/g, c => ({
                    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
                }[c]));
            },
        }
    }

    // ─────────────────────────────────────────────────────────
    // undianApp() — komponen utama, sama seperti sebelumnya
    // Ditambah: listen event 'outlet-picked' dari outletSelect
    // ─────────────────────────────────────────────────────────
    function undianApp() {
        return {
            formData: { nama_lengkap: '', no_telp: '', outlet_id: '', tanggal_struk: '' },
            fotoFiles: [],
            isDragging: false,
            isSubmitting: false,
            submitIds: [],
            pollingTimer: null,
            statusPolling: [],
            searchNama: '',
            searchNomorStruk: '',
            searchResult: [],
            isSearching: false,
            searchAttempted: false,
            showCamera: false,
            stream: null,
            facingMode: 'environment',
            toast: { show: false, message: '', type: 'success' },

            init() {
                // Terima nilai outlet dari child component outletSelect
                this.$el.addEventListener('outlet-picked', (e) => {
                    this.formData.outlet_id = e.detail || '';
                });
            },

            showToast(msg, type = 'success') {
                this.toast = { show: true, message: msg, type };
                setTimeout(() => this.toast.show = false, 3500);
            },

            handleFileAdd(event) {
                const files = Array.from(event.target.files || []);
                this.addFiles(files);
                event.target.value = '';
            },

            handleDrop(event) {
                this.isDragging = false;
                const files = Array.from(event.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                this.addFiles(files);
            },

            addFiles(files) {
                files.forEach(file => {
                    if (file.size > 5 * 1024 * 1024) {
                        this.showToast(`${file.name} terlalu besar (maks. 5MB).`, 'error');
                        return;
                    }
                    this.fotoFiles.push({ file, preview: URL.createObjectURL(file) });
                });
            },

            removeFile(idx) {
                URL.revokeObjectURL(this.fotoFiles[idx].preview);
                this.fotoFiles.splice(idx, 1);
            },

            clearAllFiles() {
                this.fotoFiles.forEach(f => URL.revokeObjectURL(f.preview));
                this.fotoFiles = [];
            },

            async bukaKamera() {
                if (!navigator.mediaDevices?.getUserMedia) { this.$refs.fileCamera.click(); return; }
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: this.facingMode, width: { ideal: 1280 }, height: { ideal: 720 } },
                        audio: false
                    });
                    this.showCamera = true;
                    this.$nextTick(() => { this.$refs.kameraVideo.srcObject = this.stream; });
                } catch {
                    this.$refs.fileCamera.click();
                    this.showToast('Gagal mengakses kamera.', 'error');
                }
            },

            tutupKamera() {
                this.stream?.getTracks().forEach(t => t.stop());
                this.stream = null;
                this.showCamera = false;
            },

            async flipKamera() {
                this.tutupKamera();
                this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
                await this.bukaKamera();
            },

            ambilFoto() {
                const video  = this.$refs.kameraVideo;
                const canvas = this.$refs.canvasCapture;
                canvas.width  = video.videoWidth  || 1280;
                canvas.height = video.videoHeight || 720;
                canvas.getContext('2d').drawImage(video, 0, 0);
                canvas.toBlob(blob => {
                    if (!blob) { this.showToast('Gagal memproses foto.', 'error'); return; }
                    this.addFiles([new File([blob], 'struk-' + Date.now() + '.jpg', { type: 'image/jpeg' })]);
                    this.tutupKamera();
                }, 'image/jpeg', 0.90);
            },

            async submitForm() {
                if (this.fotoFiles.length === 0) { this.showToast('Pilih minimal 1 foto struk.', 'error'); return; }
                this.isSubmitting = true;
                const fd = new FormData();
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                fd.append('nama_lengkap', this.formData.nama_lengkap);
                fd.append('no_telp', this.formData.no_telp);
                if (this.formData.outlet_id) fd.append('outlet_id', this.formData.outlet_id);
                if (this.formData.tanggal_struk) fd.append('tanggal_struk', this.formData.tanggal_struk);
                this.fotoFiles.forEach(f => fd.append('foto_struk[]', f.file));
                try {
                    const res  = await fetch('{{ route("rollate.spin.store") }}', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.submitIds = data.ids;
                        this.showToast(data.message, 'success');
                        this.formData = { nama_lengkap: '', no_telp: '', outlet_id: '', tanggal_struk: '' };
                        this.clearAllFiles();
                        this.mulaiPolling();
                    } else {
                        this.showToast(data.message || 'Gagal mengirim pendaftaran.', 'error');
                    }
                } catch {
                    this.showToast('Terjadi kesalahan jaringan.', 'error');
                } finally {
                    this.isSubmitting = false;
                }
            },

            mulaiPolling() {
                clearInterval(this.pollingTimer);
                this.statusPolling = [];
                let attempts = 0;
                this.fetchStatusPolling();
                this.pollingTimer = setInterval(() => {
                    if (++attempts > 60) { clearInterval(this.pollingTimer); return; }
                    this.fetchStatusPolling();
                    if (this.statusPolling.length > 0 && this.statusPolling.every(s => s.status !== 'pending')) {
                        clearInterval(this.pollingTimer);
                    }
                }, 3000);
            },

            async fetchStatusPolling() {
                if (!this.submitIds.length) return;
                try {
                    const params = this.submitIds.map(id => `ids[]=${id}`).join('&');
                    const res    = await fetch(`{{ route("rollate.check.status") }}?${params}`);
                    const rows   = await res.json();
                    if (rows?.length) this.statusPolling = rows;
                } catch {}
            },

            async checkStatusManual() {
                if (!this.searchNama && !this.searchNomorStruk) return;
                this.isSearching = true; this.searchAttempted = true; this.searchResult = [];
                try {
                    const params = new URLSearchParams();
                    if (this.searchNama)       params.set('nama_lengkap', this.searchNama.trim());
                    if (this.searchNomorStruk) params.set('nomor_struk', this.searchNomorStruk.trim());
                    const res = await fetch(`{{ route("rollate.check.status") }}?${params}`);
                    this.searchResult = await res.json() || [];
                } catch {
                    this.showToast('Gagal mengambil data.', 'error');
                } finally {
                    this.isSearching = false;
                }
            }
        }
    }
    </script>
</body>
</html>
<div x-data="photoGallery()" style="font-family: var(--font-sans); color: var(--text-primary);">

    {{-- ── TOAST NOTIFICATIONS ── --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="toast toast-success" x-cloak>
            <i class="bi bi-check-circle-fill" style="font-size:16px; flex-shrink:0;"></i>
            <span style="flex:1;">{{ session('success') }}</span>
            <button @click="show = false" class="btn-ghost btn-icon btn-xs" style="border:none;"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    {{-- ── STATS ROW ── --}}
    <div class="c-card" style="margin-bottom: 18px;">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
            <div style="padding:18px 24px; border-right: 1px solid var(--border-subtle);">
                <div class="stat-val">{{ $data->total() }}</div>
                <div class="stat-lbl">Total Responses</div>
            </div>
            <div style="padding:18px 24px; border-right: 1px solid var(--border-subtle);">
                <div class="stat-val">{{ $data->count() }}</div>
                <div class="stat-lbl">Data Halaman Ini</div>
            </div>
            <div style="padding:18px 24px; border-right: 1px solid var(--border-subtle);">
                <div class="stat-val">{{ $data->currentPage() }}</div>
                <div class="stat-lbl">Halaman Aktif</div>
            </div>
            <div style="padding:18px 24px;">
                <div class="stat-val">{{ $data->lastPage() }}</div>
                <div class="stat-lbl">Total Halaman</div>
            </div>
        </div>
    </div>

    {{-- ── FILTER CARD ── --}}
    <div class="c-card" style="margin-bottom: 18px;">
        <div class="c-card-header">
            <h3 style="font-size: 15px; font-weight: 700; margin: 0;">Filter Data</h3>
        </div>
        <div style="padding: 20px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                
                {{-- Tanggal Awal --}}
                <div>
                    <label class="f-label">Tanggal Awal</label>
                    <input type="date" wire:model.live="tanggal_awal" class="f-input">
                </div>

                {{-- Tanggal Akhir --}}
                <div>
                    <label class="f-label">Tanggal Akhir</label>
                    <input type="date" wire:model.live="tanggal_akhir" class="f-input">
                </div>

                {{-- Combobox PIC (Refactored to Native Alpine+Livewire) --}}
                <div x-data="{ 
                        open: false, 
                        search: '', 
                        selected: @entangle('responden').live,
                        pics: @js($this->respondenList) 
                     }" 
                     @click.outside="open = false" style="position:relative;">
                    <label class="f-label">Nama PIC</label>
                    <div class="combo-trigger" @click="open = !open" style="min-height: 38px;">
                        <span x-text="selected === '' ? 'Semua PIC' : (pics.find(p => p.id == selected)?.nama_lengkap || 'Semua PIC')" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="combo-panel" x-show="open" x-cloak>
                        <div class="combo-search">
                            <div style="position:relative;">
                                <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:12px; color:var(--text-muted);"></i>
                                <input type="text" x-model="search" placeholder="Cari PIC..." class="f-input" style="padding-left:30px; width:100%;">
                            </div>
                        </div>
                        <ul class="combo-list" style="margin:0;">
                            <li class="combo-item" @click="selected = ''; open = false; search = ''" style="color:var(--text-muted); font-style:italic;">Semua PIC</li>
                            <template x-for="pic in pics" :key="pic.id">
                                <li class="combo-item" 
                                    x-show="search === '' || pic.nama_lengkap.toLowerCase().includes(search.toLowerCase())"
                                    @click="selected = pic.id; open = false; search = ''"
                                    x-text="pic.nama_lengkap">
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                {{-- Combobox Outlet (Refactored to Native Alpine+Livewire) --}}
                <div x-data="{ 
                        open: false, 
                        search: '', 
                        selected: @entangle('outlet').live,
                        outlets: @js($this->outletsList) 
                     }" 
                     @click.outside="open = false" style="position:relative;">
                    <label class="f-label">Outlet</label>
                    <div class="combo-trigger" @click="open = !open" style="min-height: 38px;">
                        <span x-text="selected === '' ? 'Semua Outlet' : (outlets.find(o => o.id == selected)?.nama_outlet || 'Semua Outlet')" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="combo-panel" x-show="open" x-cloak>
                        <div class="combo-search">
                            <div style="position:relative;">
                                <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:12px; color:var(--text-muted);"></i>
                                <input type="text" x-model="search" placeholder="Cari Outlet..." class="f-input" style="padding-left:30px; width:100%;">
                            </div>
                        </div>
                        <ul class="combo-list" style="margin:0;">
                            <li class="combo-item" @click="selected = ''; open = false; search = ''" style="color:var(--text-muted); font-style:italic;">Semua Outlet</li>
                            <template x-for="ot in outlets" :key="ot.id">
                                <li class="combo-item" 
                                    x-show="search === '' || ot.nama_outlet.toLowerCase().includes(search.toLowerCase())"
                                    @click="selected = ot.id; open = false; search = ''"
                                    x-text="ot.nama_outlet">
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 8px;">
                <button wire:click="resetFilter" class="btn btn-ghost"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</button>
            </div>
        </div>
    </div>

    {{-- ── MAIN TABLE CARD ── --}}
    <div class="c-card">
        <div class="c-card-header">
            <h3 style="font-size: 15px; font-weight: 700; margin: 0;">Daftar Data Responses</h3>
        </div>

        <div style="overflow-x:auto; position:relative;">
            
            <div wire:loading.flex style="position:absolute; inset:0; background:rgba(255,255,255,0.75); backdrop-filter:blur(2px); z-index:10; align-items:center; justify-content:center;">
                <div class="badge badge-neutral" style="font-size:13px; padding:8px 16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); background:var(--bg-surface);">
                    <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; margin-right:8px;"></i> Memuat data...
                </div>
            </div>

            <table class="c-table" style="min-width: 1000px;">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 10%;">Tanggal</th>
                        <th style="width: 8%; text-align:center;">Jam</th>
                        <th style="width: 15%;">Outlet</th>
                        <th style="width: 15%;">PIC</th>
                        <th style="width: 20%;">Pertanyaan</th>
                        <th style="width: 8%; text-align:center;">Jawaban</th>
                        <th style="width: 15%;">Alasan</th>
                        <th style="width: 10%; text-align:center;">Foto</th>
                        <th style="width: 5%; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        <tr wire:key="res-{{ $item->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                            <td style="color:var(--text-muted);">{{ $data->firstItem() + $index }}</td>
                            <td style="font-weight: 500;">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                            <td style="text-align:center; font-family:var(--font-mono); font-size:12.5px;">
                                {{ $item->jam_aktivitas ? \Carbon\Carbon::parse($item->jam_aktivitas)->format('H:i') : '-' }}
                            </td>
                            <td>{{ $item->nama_outlet ?? '-' }}</td>
                            <td style="font-weight: 600;">{{ $item->pic_nama ?? '-' }}</td>
                            <td style="line-height: 1.4;">{{ $item->pertanyaan ?? '-' }}</td>
                            
                            <td style="text-align:center;">
                                @if($item->jawaban === 'Ya')
                                    <span class="badge" style="background:#ecfdf3; color:#027a48; border:1px solid #d1fadf;">Ya</span>
                                @elseif($item->jawaban === 'Tidak')
                                    <span class="badge" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca;">Tidak</span>
                                @else
                                    <span class="badge badge-neutral">{{ $item->jawaban ?? '-' }}</span>
                                @endif
                            </td>

                            <td style="line-height: 1.4; color:var(--text-secondary);">{{ $item->alasan ?? '-' }}</td>

                            <td style="text-align:center;">
                                @if(!empty($item->foto_urls) && count($item->foto_urls))
                                    <div style="display:flex; flex-wrap:wrap; gap:4px; justify-content:center;">
                                        @foreach($item->foto_urls as $photoIndex => $foto)
                                            <div @click="openGallery(@js($item->foto_urls), {{ $photoIndex }})"
                                                 style="width:40px; height:40px; border-radius:6px; overflow:hidden; border:1px solid var(--border-subtle); cursor:pointer; transition:transform .15s;"
                                                 onmouseenter="this.style.transform='scale(1.1)'" onmouseleave="this.style.transform='scale(1)'">
                                                <img src="{{ $foto }}" style="width:100%; height:100%; object-fit:cover;">
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span style="color:var(--text-disabled); font-style:italic;">-</span>
                                @endif
                            </td>

                            <td style="text-align:center;">
                                <button wire:click="deleteData({{ $item->id }})" wire:confirm="Yakin ingin menghapus data ini?" class="btn btn-ghost btn-icon btn-sm" style="color:var(--danger);" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center; padding: 40px 20px;">
                                <i class="bi bi-inbox" style="font-size:32px; color:var(--border-muted); display:block; margin-bottom:8px;"></i>
                                <div style="color:var(--text-secondary); font-weight:500;">Belum ada data responses.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($data->hasPages())
            <div style="padding:16px 20px; border-top:1px solid var(--border-subtle);">
                {{ $data->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL PREVIEW FOTO (ALPINE.JS MURNI) --}}
    <div x-show="isOpen" x-cloak class="modal-backdrop" style="z-index: 999;">
        <div class="modal-panel" style="max-width: 800px; background: transparent; border: none; box-shadow: none;" @click.outside="closeGallery()">
            
            <div style="position:relative; display:flex; align-items:center; justify-content:center;">
                <button @click="closeGallery()" style="position:absolute; top:-40px; right:0; background:rgba(0,0,0,0.5); color:#fff; border:none; width:36px; height:36px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px; transition:background .2s;">
                    <i class="bi bi-x"></i>
                </button>
                <button @click="prev()" x-show="photos.length > 1" :disabled="currentIndex === 0" style="position:absolute; left:-50px; background:rgba(255,255,255,0.1); color:#fff; border:none; width:44px; height:44px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:24px; transition:all .2s;" :style="currentIndex === 0 ? 'opacity:0.3; cursor:not-allowed;' : 'opacity:1;'">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <img :src="photos[currentIndex]" style="max-width: 100%; max-height: 80vh; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); object-fit:contain; background:#000;">
                <button @click="next()" x-show="photos.length > 1" :disabled="currentIndex === photos.length - 1" style="position:absolute; right:-50px; background:rgba(255,255,255,0.1); color:#fff; border:none; width:44px; height:44px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:24px; transition:all .2s;" :style="currentIndex === photos.length - 1 ? 'opacity:0.3; cursor:not-allowed;' : 'opacity:1;'">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
            <div x-show="photos.length > 1" style="text-align:center; color:#fff; font-size:14px; font-weight:600; font-family:var(--font-mono); margin-top:16px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                Foto <span x-text="currentIndex + 1"></span> dari <span x-text="photos.length"></span>
            </div>
        </div>
    </div>

</div>

<script>
    if (typeof photoGallery !== 'function') {
        function photoGallery() {
            return {
                isOpen: false,
                photos: [],
                currentIndex: 0,
                openGallery(photoArray, index) {
                    this.photos = photoArray;
                    this.currentIndex = index;
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                },
                closeGallery() {
                    this.isOpen = false;
                    this.photos = [];
                    document.body.style.overflow = 'auto';
                },
                next() {
                    if (this.currentIndex < this.photos.length - 1) this.currentIndex++;
                },
                prev() {
                    if (this.currentIndex > 0) this.currentIndex--;
                }
            }
        }
    }
</script>
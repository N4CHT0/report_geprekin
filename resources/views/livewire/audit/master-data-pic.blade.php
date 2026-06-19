{{--
    FIXED VERSION
    - Action buttons dibuat selalu visible dan clickable.
    - Loading overlay diberi wire:target dan pointer-events:none agar tidak menutup semua klik.
    - Semua button non-submit diberi type="button" supaya tidak trigger submit form secara tidak sengaja.
--}}
<div style="font-family: var(--font-sans); color: var(--text-primary);">

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
                <div class="stat-val">{{ $this->stats['totalData'] }}</div>
                <div class="stat-lbl">Total Mapping</div>
            </div>
            <div style="padding:18px 24px; border-right: 1px solid var(--border-subtle);">
                <div class="stat-val">{{ $this->stats['totalPic'] }}</div>
                <div class="stat-lbl">PIC Terdaftar</div>
            </div>
            <div style="padding:18px 24px;">
                <div class="stat-val">{{ $this->stats['totalOutlet'] }}</div>
                <div class="stat-lbl">Outlet Aktif</div>
            </div>
        </div>
    </div>

    {{-- ── MAIN CARD ── --}}
    <div class="c-card">

        {{-- Toolbar & Filter --}}
        <div class="c-card-header" style="flex-wrap: wrap;">
            <div style="display:flex; flex-wrap:wrap; gap:12px; flex:1;">

                {{-- FILTER PIC --}}
                <div x-data="{ open: false, search: '', selected: @entangle('filterPic').live }" 
                     @click.outside="open = false"
                     style="position:relative; flex:1; min-width:200px; max-width:260px;">
                    <div class="combo-trigger" @click="open = !open">
                        <span x-text="selected === '' ? 'Semua PIC' : selected" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></span>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="combo-panel" x-show="open" x-cloak>
                        <div class="combo-search">
                            <input type="text" x-model="search" placeholder="Cari nama PIC..." autofocus>
                        </div>
                        <ul class="combo-list" style="list-style:none; margin:0;">
                            <li class="combo-item" @click="selected = ''; open = false; search = ''" style="color:var(--text-muted); font-style:italic;">Semua PIC</li>
                            @foreach($this->dropdownPics as $namaPic)
                                <li class="combo-item" 
                                    x-show="search === '' || @js(strtolower($namaPic)).includes(search.toLowerCase())" 
                                    @click="selected = @js($namaPic); open = false; search = ''">
                                    {{ $namaPic }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Filter Level --}}
                <div class="f-select-wrap" style="flex:1; min-width:140px; max-width:180px;">
                    <select wire:model.live="filterLevel" class="f-select" style="cursor:pointer;">
                        <option value="">Semua Level</option>
                        <option value="LEADER">LEADER</option>
                        <option value="SPV">SPV</option>
                        <option value="TM">TM</option>
                    </select>
                    <i class="bi bi-chevron-down"></i>
                </div>

                {{-- FILTER OUTLET --}}
                <div x-data="{ open: false, search: '', selected: @entangle('filterOutlet').live }" 
                     @click.outside="open = false"
                     style="position:relative; flex:1; min-width:200px; max-width:260px;">
                    <div class="combo-trigger" @click="open = !open">
                        <span x-text="selected === '' ? 'Semua Outlet' : selected" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></span>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <div class="combo-panel" x-show="open" x-cloak>
                        <div class="combo-search">
                            <input type="text" x-model="search" placeholder="Cari nama Outlet...">
                        </div>
                        <ul class="combo-list" style="list-style:none; margin:0;">
                            <li class="combo-item" @click="selected = ''; open = false; search = ''" style="color:var(--text-muted); font-style:italic;">Semua Outlet</li>
                            @foreach($this->dropdownOutlets as $outletNama)
                                <li class="combo-item" 
                                    x-show="search === '' || @js(strtolower($outletNama)).includes(search.toLowerCase())" 
                                    @click="selected = @js($outletNama); open = false; search = ''">
                                    {{ $outletNama }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

            </div>

            {{-- Tombol Tambah --}}
            <button type="button" wire:click="openModal" wire:loading.attr="disabled" wire:target="openModal" class="btn btn-primary" style="white-space:nowrap;">
                <i class="bi bi-plus-lg"></i> Tambah PIC
            </button>
        </div>

        {{-- Main Table --}}
        <div style="overflow-x:auto; position:relative; min-height:300px;">
            
            <div wire:loading.flex wire:target="filterPic,filterLevel,filterOutlet,openModal,editMapping,deleteMapping" style="position:absolute; inset:0; background:rgba(255,255,255,0.75); backdrop-filter:blur(2px); z-index:10; align-items:center; justify-content:center; pointer-events:none;">
                <div class="badge badge-neutral" style="font-size:13px; padding:8px 16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); background:var(--bg-surface);">
                    <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; margin-right:8px;"></i> Memuat data...
                </div>
            </div>

            <table class="c-table">
                <thead>
                    <tr>
                        <th style="width: 32%;">PIC Details</th>
                        <th style="width: 12%;">Level</th>
                        <th style="width: 45%;">Tugas Outlet</th>
                        <th style="width: 11%; text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $item)
                        <tr wire:key="row-{{ $item->pic_id }}-{{ $item->level_pic }}" class="group hover:bg-gray-50 transition-colors duration-200">
                            
                            {{-- Avatar & Nama --}}
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div class="avatar" style="width:36px; height:36px; font-size:14px;">{{ strtoupper(substr($item->nama_lengkap ?? 'U', 0, 1)) }}</div>
                                    <span style="font-weight:600; color:var(--text-primary); font-size:14px;">{{ $item->nama_lengkap }}</span>
                                </div>
                            </td>

                            {{-- Level Badge --}}
                            <td>
                                @if($item->level_pic === 'LEADER')
                                    <span class="badge badge-leader" style="font-size:11.5px; padding:4px 10px;">LEADER</span>
                                @elseif($item->level_pic === 'SPV')
                                    <span class="badge badge-spv" style="font-size:11.5px; padding:4px 10px;">SPV</span>
                                @elseif($item->level_pic === 'TM')
                                    <span class="badge badge-tm" style="font-size:11.5px; padding:4px 10px;">TM</span>
                                @else
                                    <span class="badge badge-neutral">{{ $item->level_pic }}</span>
                                @endif
                            </td>

                            {{-- Outlet Badges --}}
                            <td>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    @php
                                        $outletIds = array_filter(explode(',', $item->outlet_ids));
                                        $shown = array_slice($outletIds, 0, 4);
                                        $remaining = count($outletIds) - count($shown);
                                    @endphp
                                    @foreach($shown as $oid)
                                        @if(isset($this->outletsMap[trim($oid)]))
                                            <span class="badge badge-neutral" style="font-size:11.5px; padding:3px 8px;">{{ $this->outletsMap[trim($oid)] }}</span>
                                        @endif
                                    @endforeach
                                    @if($remaining > 0)
                                        <span class="badge badge-neutral" style="cursor:help; border-style:dashed; font-size:11.5px; padding:3px 8px;" title="{{ implode(', ', array_map(fn($id) => $this->outletsMap[trim($id)] ?? '', array_slice($outletIds, 4))) }}">
                                            +{{ $remaining }} lainnya
                                        </span>
                                    @endif
                                    @if(empty($outletIds) || (count($outletIds) === 1 && empty($outletIds[0])))
                                        <span style="font-size:12px; color:var(--text-disabled); font-style:italic;">Belum dipetakan</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Actions Hover --}}
                            <td style="text-align:right;">
                                <div class="actions" style="display:flex; justify-content:flex-end; gap:6px; opacity:1; transition:opacity 0.2s;">
                                    <button type="button" wire:click="editMapping('{{ $item->pic_id }}', '{{ $item->level_pic }}')" wire:loading.attr="disabled" wire:target="editMapping" class="btn btn-ghost btn-icon btn-sm" title="Edit">
                                        <i class="bi bi-pencil-square" style="font-size:16px;"></i>
                                    </button>
                                    <button type="button" wire:click="deleteMapping('{{ $item->pic_id }}', '{{ $item->level_pic }}')" wire:confirm="Hapus SEMUA mapping outlet untuk PIC ini?" wire:loading.attr="disabled" wire:target="deleteMapping" class="btn btn-danger btn-icon btn-sm" title="Hapus">
                                        <i class="bi bi-trash" style="font-size:16px;"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:60px 20px;">
                                <i class="bi bi-inbox" style="font-size:38px; color:var(--border-muted); display:block; margin-bottom:12px;"></i>
                                <div style="font-weight:600; color:var(--text-secondary); font-size:15px;">Tidak ada data pemetaan PIC</div>
                                <div style="font-size:13px; color:var(--text-muted); margin-top:6px;">Coba ubah filter atau tambah data baru.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="padding:16px 20px; border-top:1px solid var(--border-subtle); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="font-size:12.5px; color:var(--text-muted); font-family:var(--font-mono);">
                @if($data->total() > 0)
                    Menampilkan <strong style="color:var(--text-primary)">{{ $data->firstItem() }}</strong> - <strong style="color:var(--text-primary)">{{ $data->lastItem() }}</strong> dari <strong style="color:var(--text-primary)">{{ $data->total() }}</strong> data
                @else
                    0 data ditemukan
                @endif
            </div>
            @if($data->hasPages())
                <div>{{ $data->links() }}</div>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         MODAL: TAMBAH / EDIT MAPPING (ALPINE.JS)
    ══════════════════════════════════════════ --}}
    <div x-data="{ open: @entangle('isModalOpen').live }" x-show="open" x-cloak>
        
        <div class="modal-backdrop" x-transition.opacity.duration.200ms>
            <div class="modal-panel" x-transition.scale.95.duration.200ms @click.stop style="max-width: 850px;">

                <form wire:submit.prevent="save" style="display:flex; flex-direction:column; max-height:inherit;">
                    
                    {{-- Header Modal --}}
                    <div class="modal-header" style="padding: 24px 30px;">
                        <div>
                            <h2 class="modal-title" style="font-size: 20px;">{{ $isEditMode ? 'Edit Mapping PIC' : 'Tambah PIC Baru' }}</h2>
                            <p class="modal-sub" style="font-size: 14px; margin-top:6px;">Konfigurasi PIC dan distribusikan area outlet yang menjadi tanggung jawabnya.</p>
                        </div>
                        <button type="button" @click="$wire.closeModal()" class="btn btn-ghost btn-icon"><i class="bi bi-x-lg" style="font-size:20px;"></i></button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="modal-body" style="padding: 30px;">
                        
                        @error('database')
                            <div style="padding:14px 18px; background:var(--danger-muted); color:var(--danger); border-radius:8px; margin-bottom:20px; font-size:13.5px; border:1px solid rgba(239,68,68,0.2); display:flex; align-items:flex-start; gap:12px;">
                                <i class="bi bi-exclamation-triangle-fill" style="margin-top:2px; font-size:15px;"></i>
                                <div>
                                    <strong>Gagal menyimpan data!</strong><br>
                                    {{ $message }}
                                </div>
                            </div>
                        @enderror

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
                            
                            {{-- Input Nama --}}
                            <div>
                                <label class="f-label" style="font-size:12px;">Nama Lengkap</label>
                                <input type="text" wire:model="nama_lengkap" class="f-input" style="padding:10px 14px; font-size:14.5px;" placeholder="Masukkan nama...">
                                @error('nama_lengkap') <span class="f-error">{{ $message }}</span> @enderror
                            </div>

                            {{-- Select Level --}}
                            <div>
                                <label class="f-label" style="font-size:12px;">Level Otoritas</label>
                                <div class="f-select-wrap">
                                    <select wire:model="level_pic" class="f-select" style="padding:10px 14px; font-size:14.5px;">
                                        <option value="">-- Pilih Level --</option>
                                        <option value="LEADER">LEADER</option>
                                        <option value="SPV">SPV</option>
                                        <option value="TM">TM</option>
                                    </select>
                                    <i class="bi bi-chevron-down" style="font-size:12px; right:14px;"></i>
                                </div>
                                @error('level_pic') <span class="f-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Multi-Select Outlet Grid Box --}}
                        <div x-data="{ dropdownOpen: false, searchOutlet: '', get count() { return @entangle('selected_outlets').live?.length || 0; } }"
                             @click.outside="dropdownOpen = false">
                            
                            <label class="f-label" style="font-size:12px; display:flex; justify-content:space-between; align-items:flex-end;">
                                <span>Tugaskan ke Outlet</span>
                                <span x-show="count > 0" style="color:var(--text-primary); font-family:var(--font-mono); font-size:12px; background:var(--bg-overlay); padding:2px 8px; border-radius:4px; border:1px solid var(--border-subtle); text-transform:none;" x-text="count + ' dipilih'"></span>
                            </label>

                            <div style="position:relative;">
                                {{-- Trigger Combobox --}}
                                <div class="combo-trigger" @click="dropdownOpen = !dropdownOpen" style="padding:10px 14px; min-height:46px;">
                                    <span x-show="count === 0" style="color:var(--text-disabled); font-size:14px;">Pilih satu atau beberapa outlet...</span>
                                    <span x-show="count > 0" style="color:var(--text-primary); font-weight:600; font-size:14.5px;" x-text="count + ' outlet telah ditandai'"></span>
                                    <i class="bi bi-chevron-down" style="font-size:12px;"></i>
                                </div>

                                {{-- Dropdown Panel --}}
                                <div class="combo-panel" x-show="dropdownOpen" x-cloak style="max-width:none; top:calc(100% + 8px); box-shadow:0 15px 40px rgba(0,0,0,0.15);">
                                    <div class="combo-search" style="padding:14px;">
                                        <div style="position:relative;">
                                            <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-muted);"></i>
                                            <input type="text" x-model="searchOutlet" placeholder="Cari nama outlet..." style="width:100%; padding:10px 12px 10px 34px; background:var(--bg-surface); border:1px solid var(--border-muted); border-radius:6px; font-size:13.5px; outline:none; color:var(--text-primary); box-shadow:inset 0 1px 2px rgba(0,0,0,0.02);">
                                        </div>
                                    </div>

                                    <div style="padding:10px 14px; border-bottom:1px solid var(--border-subtle); display:flex; gap:10px; background:var(--bg-overlay);">
                                        <button type="button" wire:click="selectAllOutlets" class="btn btn-ghost btn-sm" style="background:#fff; border-color:var(--border-subtle);">Pilih Semua</button>
                                        <button type="button" wire:click="removeAllOutlets" class="btn btn-ghost btn-sm" style="color:var(--danger); border-color:transparent;">Hapus Semua</button>
                                    </div>

                                    <div style="max-height:320px; overflow-y:auto; padding:16px; display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:12px; background:var(--bg-overlay);">
                                        @forelse($this->allOutlets as $outlet)
                                            <label class="combo-item" 
                                                   style="display:flex; align-items:flex-start; gap:12px; padding:12px 14px; background:var(--bg-surface); border:1px solid var(--border-subtle); border-radius:8px; cursor:pointer; transition:all .15s;" 
                                                   onmouseenter="this.style.borderColor='var(--accent)';" 
                                                   onmouseleave="this.style.borderColor='var(--border-subtle)';"
                                                   x-show="searchOutlet === '' || @js(strtolower($outlet->nama_outlet)).includes(searchOutlet.toLowerCase())">
                                                <input type="checkbox" wire:model="selected_outlets" value="{{ $outlet->id }}" style="width:16px; height:16px; margin-top:2px;">
                                                <span style="font-size:13px; color:var(--text-primary); font-weight:500; line-height:1.4;">{{ $outlet->nama_outlet }}</span>
                                            </label>
                                        @empty
                                            <div style="grid-column: 1 / -1; padding:24px; text-align:center; color:var(--text-muted); font-size:13.5px;">Tidak ada outlet tersedia</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @error('selected_outlets') <span class="f-error" style="font-size:13px; margin-top:8px;">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="modal-footer" style="padding: 20px 30px;">
                        <button type="button" @click="$wire.closeModal()" class="btn btn-ghost" style="padding:10px 20px; font-size:14px;">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" style="padding:10px 24px; font-size:14px;">
                            <span wire:loading.remove wire:target="save"><i class="bi bi-check2" style="font-size:16px;"></i> Simpan Data</span>
                            <span wire:loading.flex wire:target="save" style="align-items:center; gap:6px;">
                                <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; font-size:16px;"></i> Menyimpan...
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div>

<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .c-table tr:hover .actions { opacity: 1 !important; }
    button:not(:disabled), .combo-trigger, .combo-item { pointer-events:auto; }
</style>
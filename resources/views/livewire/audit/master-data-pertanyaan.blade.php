<div style="font-family: var(--font-sans); color: var(--text-primary);">

    {{-- ── TOAST NOTIFICATIONS ── --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="toast toast-success" style="position:fixed; top:20px; right:20px; background:var(--success-muted); color:var(--success); padding:12px 16px; border-radius:8px; display:flex; align-items:center; gap:12px; z-index:9999; border:1px solid rgba(5,150,105,0.2); box-shadow:0 4px 12px rgba(0,0,0,0.1);" x-cloak>
            <i class="bi bi-check-circle-fill" style="font-size:16px; flex-shrink:0;"></i>
            <span style="flex:1; font-size:13.5px; font-weight:500;">{{ session('success') }}</span>
            <button @click="show = false" class="btn-ghost btn-icon btn-xs" style="border:none; cursor:pointer; background:transparent;"><i class="bi bi-x-lg"></i></button>
        </div>
    @endif

    {{-- ── MAIN CARD ── --}}
    <div class="c-card">

        {{-- Toolbar & Filter --}}
        <div class="c-card-header" style="flex-wrap: wrap;">
            <div style="flex:1; min-width:200px;">
                <h2 style="font-size: 18px; font-weight: 700; margin:0; color: var(--text-primary);">Data Pertanyaan</h2>
                <p style="font-size: 13px; color: var(--text-muted); margin:4px 0 0 0;">Kelola daftar pertanyaan audit operasional.</p>
            </div>
            
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                
                {{-- Search Box --}}
                <div style="position:relative; min-width:240px;">
                    <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-muted);"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari pertanyaan..." class="f-input" style="padding-left:34px;">
                </div>

                {{-- Tombol Tambah --}}
                <button wire:click="openModal" class="btn btn-primary" style="white-space:nowrap;">
                    <i class="bi bi-plus-lg"></i> Tambah Pertanyaan
                </button>
            </div>
        </div>

        {{-- Main Table --}}
        <div style="overflow-x:auto; position:relative; min-height:300px;">
            
            <div wire:loading.flex style="position:absolute; inset:0; background:rgba(255,255,255,0.75); backdrop-filter:blur(2px); z-index:10; align-items:center; justify-content:center;">
                <div class="badge badge-neutral" style="font-size:13px; padding:8px 16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); background:var(--bg-surface);">
                    <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; margin-right:8px;"></i> Memuat data...
                </div>
            </div>

            <table class="c-table">
                <thead>
                    <tr>
                        <th style="width: 8%; text-align:center;">No</th>
                        <th style="width: 55%;">Pertanyaan</th>
                        <th style="width: 17%; text-align:center;">Jam</th>
                        <th style="width: 20%; text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->questions as $index => $item)
                        <tr wire:key="row-{{ $item->id }}" class="group hover:bg-gray-50 transition-colors duration-200">
                            
                            <td style="text-align:center; color:var(--text-muted); font-family:var(--font-mono); font-size:13px;">
                                {{ $this->questions->firstItem() + $index }}
                            </td>

                            <td>
                                <span style="font-weight:500; color:var(--text-primary); font-size:14px;">{{ $item->pertanyaan }}</span>
                            </td>

                            <td style="text-align:center;">
                                @if($item->jam)
                                    <span class="badge badge-neutral" style="font-size:12px; font-family:var(--font-mono);">
                                        <i class="bi bi-clock" style="margin-right:4px; font-size:10px;"></i>
                                        {{ \Carbon\Carbon::parse($item->jam)->format('H:i') }}
                                    </span>
                                @else
                                    <span style="color:var(--text-disabled);">-</span>
                                @endif
                            </td>

                            {{-- Actions Hover --}}
                            <td style="text-align:right;">
                                <div class="actions" style="display:flex; justify-content:flex-end; gap:6px; opacity:0; transition:opacity 0.2s;" onmouseenter="this.style.opacity='1'" onmouseleave="this.parentElement.parentElement.matches(':hover') ? this.style.opacity='1' : this.style.opacity='0'">
                                    <button wire:click="edit({{ $item->id }})" class="btn btn-ghost btn-icon btn-sm" style="color:#2563eb;" title="Edit">
                                        <i class="bi bi-pencil-square" style="font-size:16px;"></i>
                                    </button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus pertanyaan ini?" class="btn btn-danger btn-icon btn-sm" style="color:var(--danger);" title="Hapus">
                                        <i class="bi bi-trash" style="font-size:16px;"></i>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:60px 20px;">
                                <i class="bi bi-inbox" style="font-size:38px; color:var(--border-muted); display:block; margin-bottom:12px;"></i>
                                <div style="font-weight:600; color:var(--text-secondary); font-size:15px;">Tidak ada data pertanyaan</div>
                                <div style="font-size:13px; color:var(--text-muted); margin-top:6px;">Coba ubah kata kunci pencarian atau tambah data baru.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div style="padding:16px 20px; border-top:1px solid var(--border-subtle); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="font-size:12.5px; color:var(--text-muted); font-family:var(--font-mono);">
                @if($this->questions->total() > 0)
                    Menampilkan <strong style="color:var(--text-primary)">{{ $this->questions->firstItem() }}</strong> - <strong style="color:var(--text-primary)">{{ $this->questions->lastItem() }}</strong> dari <strong style="color:var(--text-primary)">{{ $this->questions->total() }}</strong> data
                @else
                    0 data ditemukan
                @endif
            </div>
            @if($this->questions->hasPages())
                <div>{{ $this->questions->links() }}</div>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         MODAL: TAMBAH / EDIT PERTANYAAN
    ══════════════════════════════════════════ --}}
    <div x-data="{ open: @entangle('isModalOpen').live }" x-show="open" x-cloak>
        
        <div class="modal-backdrop" x-transition.opacity.duration.200ms>
            <div class="modal-panel" x-transition.scale.95.duration.200ms @click.stop style="max-width: 600px;">

                <form wire:submit.prevent="save" style="display:flex; flex-direction:column; max-height:inherit;">
                    
                    {{-- Header Modal --}}
                    <div class="modal-header" style="padding: 20px 24px;">
                        <div>
                            <h2 class="modal-title" style="font-size: 18px; margin:0;">{{ $isEditMode ? 'Edit Pertanyaan' : 'Tambah Pertanyaan Baru' }}</h2>
                        </div>
                        <button type="button" @click="$wire.closeModal()" class="btn btn-ghost btn-icon" style="margin:-8px;"><i class="bi bi-x-lg" style="font-size:18px;"></i></button>
                    </div>

                    {{-- Body Modal --}}
                    <div class="modal-body" style="padding: 24px;">
                        
                        @error('database')
                            <div style="padding:14px 18px; background:var(--danger-muted); color:var(--danger); border-radius:8px; margin-bottom:20px; font-size:13.5px; border:1px solid rgba(239,68,68,0.2); display:flex; align-items:flex-start; gap:12px;">
                                <i class="bi bi-exclamation-triangle-fill" style="margin-top:2px; font-size:15px;"></i>
                                <div>
                                    <strong>Gagal menyimpan data!</strong><br>
                                    {{ $message }}
                                </div>
                            </div>
                        @enderror

                        <div style="display:flex; flex-direction:column; gap:20px;">
                            
                            {{-- Input Pertanyaan --}}
                            <div>
                                <label class="f-label" style="font-size:12px;">Teks Pertanyaan</label>
                                <textarea wire:model="pertanyaan" class="f-input" rows="4" style="padding:10px 14px; font-size:14.5px; resize:none;" placeholder="Masukkan pertanyaan audit..."></textarea>
                                @error('pertanyaan') <span style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span> @enderror
                            </div>

                            {{-- Input Jam --}}
                            <div>
                                <label class="f-label" style="font-size:12px;">Jam Target</label>
                                <input type="time" wire:model="jam" class="f-input" style="padding:10px 14px; font-size:14.5px; font-family:var(--font-mono); max-width:150px;">
                                @error('jam') <span style="color:var(--danger); font-size:12px; margin-top:4px; display:block;">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </div>

                    {{-- Footer Modal --}}
                    <div class="modal-footer" style="padding: 16px 24px;">
                        <button type="button" @click="$wire.closeModal()" class="btn btn-ghost" style="padding:8px 16px; font-size:13.5px;">Batal</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" style="padding:8px 20px; font-size:13.5px;">
                            <span wire:loading.remove wire:target="save"><i class="bi bi-check2" style="font-size:15px;"></i> Simpan</span>
                            <span wire:loading.flex wire:target="save" style="align-items:center; gap:6px;">
                                <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; font-size:15px;"></i> Menyimpan...
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
</style>
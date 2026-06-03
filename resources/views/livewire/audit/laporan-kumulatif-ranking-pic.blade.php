<div style="font-family: var(--font-sans); color: var(--text-primary); position: relative;">

    {{-- Overlay Loading Global saat Kalkulasi Berat --}}
    <div wire:loading.flex style="position:absolute; inset:0; background:rgba(255,255,255,0.6); backdrop-filter:blur(3px); z-index:50; align-items:center; justify-content:center;">
        <div class="badge badge-neutral" style="font-size:14px; padding:12px 24px; box-shadow:0 10px 30px rgba(0,0,0,0.1); background:var(--bg-surface); display:flex; align-items:center; gap:10px;">
            <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; font-size:18px;"></i> Mengkalkulasi Ranking...
        </div>
    </div>

    {{-- Header Top --}}
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 6px 0; color: var(--text-primary);">Kumulatif Ranking PIC</h1>
        <p style="margin: 0; font-size: 14px; color: var(--text-muted);">Ranking kumulatif AM, SPV, dan Leader berdasarkan average score performa.</p>
    </div>

    {{-- ── FILTER CARD ── --}}
    <div class="c-card" style="margin-bottom: 24px;">
        <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
            
            <div>
                <label class="f-label">Tanggal Awal</label>
                <input type="date" wire:model.live="tanggalAwal" class="f-input" style="font-family:var(--font-mono);">
            </div>

            <div>
                <label class="f-label">Tanggal Akhir</label>
                <input type="date" wire:model.live="tanggalAkhir" class="f-input" style="font-family:var(--font-mono);">
            </div>

            {{-- Outlet Dropdown (Alpine Search) --}}
            <div x-data="{ 
                    open: false, 
                    search: '', 
                    selected: @entangle('filterOutlet').live,
                    outlets: @js($this->dropdownOutlets)
                 }" 
                 @click.outside="open = false" style="position:relative; width: 100%;">
                
                <label class="f-label">Filter Outlet</label>
                <div class="combo-trigger" @click="open = !open">
                    <span x-text="selected === '' ? 'Semua Outlet' : (outlets.find(o => o.id == selected)?.nama_outlet || 'Semua Outlet')" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></span>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="combo-panel" x-show="open" x-cloak>
                    <div class="combo-search">
                        <div style="position:relative;">
                            <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:12px; color:var(--text-muted);"></i>
                            <input type="text" x-model="search" placeholder="Cari nama Outlet..." class="f-input" style="padding-left:30px; width:100%;">
                        </div>
                    </div>
                    <ul class="combo-list" style="list-style:none; margin:0;">
                        <li class="combo-item" @click="selected = ''; open = false; search = ''" style="color:var(--text-muted); font-style:italic;">Semua Outlet</li>
                        
                        <template x-for="outlet in outlets" :key="outlet.id">
                            <li class="combo-item" 
                                x-show="search === '' || outlet.nama_outlet.toLowerCase().includes(search.toLowerCase())" 
                                @click="selected = outlet.id; open = false; search = ''"
                                x-text="outlet.nama_outlet">
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- PIC Dropdown (Alpine Search) --}}
            <div x-data="{ 
                    open: false, 
                    search: '', 
                    selected: @entangle('filterPic').live,
                    pics: @js($this->dropdownPics)
                 }" 
                 @click.outside="open = false" style="position:relative; width: 100%;">
                
                <label class="f-label">Filter PIC</label>
                <div class="combo-trigger" @click="open = !open">
                    <span x-text="selected === '' ? 'Semua PIC' : (pics.find(p => p.id == selected)?.nama_lengkap || 'Semua PIC')" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></span>
                    <i class="bi bi-chevron-down"></i>
                </div>
                <div class="combo-panel" x-show="open" x-cloak>
                    <div class="combo-search">
                        <div style="position:relative;">
                            <i class="bi bi-search" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); font-size:12px; color:var(--text-muted);"></i>
                            <input type="text" x-model="search" placeholder="Cari nama PIC..." class="f-input" style="padding-left:30px; width:100%;">
                        </div>
                    </div>
                    <ul class="combo-list" style="list-style:none; margin:0;">
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

        </div>
    </div>

    {{-- ── SUMMARY COUNTER ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px;">
        @php
            $amCount = $this->rankings['am']->count();
            $spvCount = $this->rankings['spv']->count();
            $leaderCount = $this->rankings['leader']->count();
        @endphp

        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(22, 163, 74, 0.1); color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-person-vcard-fill"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total AM</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">{{ $amCount }}</div>
            </div>
        </div>

        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(202, 138, 4, 0.1); color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total SPV</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">{{ $spvCount }}</div>
            </div>
        </div>

        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(15, 23, 42, 0.1); color: #0f172a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Leader</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">{{ $leaderCount }}</div>
            </div>
        </div>
    </div>

    {{-- ── RANKING TABLES GRID ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; align-items: start;">

        {{-- RANKING AM --}}
        <div class="c-card" style="overflow: hidden;">
            <div style="background: linear-gradient(135deg, #166534 0%, #15803d 100%); padding: 16px 20px; color: #fff;">
                <span style="display:inline-block; padding: 2px 8px; border-radius: 99px; background: rgba(255,255,255,0.2); font-size: 10px; font-weight: 800; letter-spacing: 1px; margin-bottom: 6px;">ROLE</span>
                <h3 style="margin: 0; font-size: 16px; font-weight: 800;">Ranking AM</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">Rnk</th>
                            <th>Nama PIC</th>
                            <th style="text-align: right;">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->rankings['am'] as $item)
                        <tr>
                            <td style="text-align: center;"><span style="display:inline-block; min-width:24px; padding:4px; background:#eff6ff; color:#1d4ed8; font-weight:bold; border-radius:6px; font-size:12px;">{{ $item->ranking }}</span></td>
                            <td style="font-weight: 600; font-size:13px;">{{ $item->nama }}</td>
                            <td style="text-align: right;">
                                <span class="badge" style="{{ $item->status == 'excellent' ? 'background:#ecfdf3; color:#027a48; border:1px solid #d1fadf;' : ($item->status == 'good' ? 'background:#fffaeb; color:#b54708; border:1px solid #fedf89;' : 'background:#fef3f2; color:#b42318; border:1px solid #fecdca;') }}">
                                    {{ number_format($item->average_score, 2) }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 13px;">Belum ada data AM.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RANKING SPV --}}
        <div class="c-card" style="overflow: hidden;">
            <div style="background: linear-gradient(135deg, #ca8a04 0%, #eab308 100%); padding: 16px 20px; color: #111827;">
                <span style="display:inline-block; padding: 2px 8px; border-radius: 99px; background: rgba(255,255,255,0.4); font-size: 10px; font-weight: 800; letter-spacing: 1px; margin-bottom: 6px;">ROLE</span>
                <h3 style="margin: 0; font-size: 16px; font-weight: 800;">Ranking SPV</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">Rnk</th>
                            <th>Nama PIC</th>
                            <th style="text-align: right;">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->rankings['spv'] as $item)
                        <tr>
                            <td style="text-align: center;"><span style="display:inline-block; min-width:24px; padding:4px; background:#eff6ff; color:#1d4ed8; font-weight:bold; border-radius:6px; font-size:12px;">{{ $item->ranking }}</span></td>
                            <td style="font-weight: 600; font-size:13px;">{{ $item->nama }}</td>
                            <td style="text-align: right;">
                                <span class="badge" style="{{ $item->status == 'excellent' ? 'background:#ecfdf3; color:#027a48; border:1px solid #d1fadf;' : ($item->status == 'good' ? 'background:#fffaeb; color:#b54708; border:1px solid #fedf89;' : 'background:#fef3f2; color:#b42318; border:1px solid #fecdca;') }}">
                                    {{ number_format($item->average_score, 2) }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 13px;">Belum ada data SPV.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RANKING LEADER --}}
        <div class="c-card" style="overflow: hidden;">
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 16px 20px; color: #fff;">
                <span style="display:inline-block; padding: 2px 8px; border-radius: 99px; background: rgba(255,255,255,0.2); font-size: 10px; font-weight: 800; letter-spacing: 1px; margin-bottom: 6px;">ROLE</span>
                <h3 style="margin: 0; font-size: 16px; font-weight: 800;">Ranking Leader</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="c-table">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">Rnk</th>
                            <th>Nama PIC</th>
                            <th style="text-align: right;">Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->rankings['leader'] as $item)
                        <tr>
                            <td style="text-align: center;"><span style="display:inline-block; min-width:24px; padding:4px; background:#eff6ff; color:#1d4ed8; font-weight:bold; border-radius:6px; font-size:12px;">{{ $item->ranking }}</span></td>
                            <td style="font-weight: 600; font-size:13px;">{{ $item->nama }}</td>
                            <td style="text-align: right;">
                                <span class="badge" style="{{ $item->status == 'excellent' ? 'background:#ecfdf3; color:#027a48; border:1px solid #d1fadf;' : ($item->status == 'good' ? 'background:#fffaeb; color:#b54708; border:1px solid #fedf89;' : 'background:#fef3f2; color:#b42318; border:1px solid #fecdca;') }}">
                                    {{ number_format($item->average_score, 2) }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" style="text-align: center; padding: 30px; color: var(--text-muted); font-size: 13px;">Belum ada data Leader.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
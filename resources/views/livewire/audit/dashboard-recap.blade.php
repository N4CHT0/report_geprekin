<div style="font-family: var(--font-sans); color: var(--text-primary); position: relative;">

    {{-- Loading Overlay Global --}}
    <div wire:loading.flex style="position:absolute; inset:0; background:rgba(255,255,255,0.6); backdrop-filter:blur(3px); z-index:50; align-items:center; justify-content:center;">
        <div class="badge badge-neutral" style="font-size:14px; padding:12px 24px; box-shadow:0 10px 30px rgba(0,0,0,0.1); background:#fff; border-radius:12px; display:flex; align-items:center; gap:10px;">
            <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; font-size:18px; color:#206bc4;"></i> Mengkalkulasi 4 Ranking Sekaligus...
        </div>
    </div>

    <div class="recap-page-head" style="margin-bottom: 24px;">
        <div>
            <h1 class="recap-page-title">Dashboard Recap</h1>
            <p class="recap-page-subtitle">Compliance recap berdasarkan TM, SPV, AM, dan Leader.</p>
        </div>
    </div>

    {{-- ── FILTER CARD ── --}}
    <div class="recap-filter-card mb-4" style="padding: 20px; background: #fff; border: 1px solid #e6ebf2; border-radius: 18px; box-shadow: 0 1px 2px rgba(16,24,40,.04);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: end;">
            
            {{-- Tanggal Awal --}}
            <div style="width: 100%; display: flex; flex-direction: column;">
                <label style="font-size: 13px; font-weight: 700; color: #344054; margin-bottom: 8px;">Tanggal Awal</label>
                <input type="date" wire:model.live="tanggalAwal" style="width: 100%; min-height: 46px; padding: 0 14px; border-radius: 12px; border: 1px solid #d5dde7; font-family: var(--font-mono); color: #101828; outline: none; background: #fff;">
            </div>

            {{-- Tanggal Akhir --}}
            <div style="width: 100%; display: flex; flex-direction: column;">
                <label style="font-size: 13px; font-weight: 700; color: #344054; margin-bottom: 8px;">Tanggal Akhir</label>
                <input type="date" wire:model.live="tanggalAkhir" style="width: 100%; min-height: 46px; padding: 0 14px; border-radius: 12px; border: 1px solid #d5dde7; font-family: var(--font-mono); color: #101828; outline: none; background: #fff;">
            </div>

            {{-- Outlet Dropdown dengan Alpine Search --}}
            <div x-data="{ 
                    open: false, 
                    search: '', 
                    selected: @entangle('filterOutletId').live,
                    outlets: @js($this->dropdownOutlets)
                 }" 
                 @click.outside="open = false" style="position:relative; width: 100%;">
                
                <label class="recap-label">Outlet</label>
                
                <div class="form-control recap-input" @click="open = !open" style="display:flex; align-items:center; justify-content:space-between; cursor:pointer; background:#fff;">
                    {{-- Kita ganti $wire.dropdownOutlets dengan variabel lokal 'outlets' --}}
                    <span x-text="selected === '' ? 'Semua Outlet' : (outlets.find(o => o.id == selected)?.nama_outlet || 'Semua Outlet')" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></span>
                    <i class="bi bi-chevron-down text-muted"></i>
                </div>
                
                <div class="combo-panel" x-show="open" x-cloak style="position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:100; background:#fff; border:1px solid #d5dde7; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden;">
                    <div style="padding:10px; border-bottom:1px solid #eef2f6; background:#f8fafc;">
                        <div style="position:relative;">
                            <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:#667085;"></i>
                            <input type="text" x-model="search" placeholder="Cari nama Outlet..." style="width:100%; padding:8px 12px 8px 34px; border:1px solid #d5dde7; border-radius:8px; outline:none; font-size:13.5px;">
                        </div>
                    </div>
                    <ul style="max-height:220px; overflow-y:auto; padding:6px; margin:0; list-style:none;">
                        <li @click="selected = ''; open = false; search = ''" style="padding:8px 12px; border-radius:6px; cursor:pointer; color:#667085; font-style:italic;" onmouseover="this.style.background='#f3f7ff'" onmouseout="this.style.background='transparent'">Semua Outlet</li>
                        
                        {{-- Kita gunakan template x-for untuk menghindari Blade loop & memaksimalkan Alpine --}}
                        <template x-for="outlet in outlets" :key="outlet.id">
                            <li x-show="search === '' || outlet.nama_outlet.toLowerCase().includes(search.toLowerCase())" 
                                @click="selected = outlet.id; open = false; search = ''"
                                x-text="outlet.nama_outlet"
                                style="padding:8px 12px; border-radius:6px; cursor:pointer; color:#101828; font-weight:500;"
                                onmouseover="this.style.background='#f3f7ff'" onmouseout="this.style.background='transparent'">
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Tombol Reset --}}
            <div style="width: 100%;">
                <button wire:click="resetFilter" style="width: 100%; min-height: 46px; border-radius: 12px; background: #fff; border: 1px solid #d5dde7; color: #344054; font-weight: 600; display:flex; align-items:center; justify-content:center; gap:8px; cursor:pointer; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                </button>
            </div>
        </div>

        {{-- Chips Summary --}}
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top: 16px;">
            <span style="display:inline-flex; align-items:center; padding:6px 12px; border-radius:999px; background:#f3f7ff; color:#174e91; font-size:13px; font-weight:600;">
                <i class="bi bi-calendar3" style="margin-right:6px;"></i> {{ \Carbon\Carbon::parse($tanggalAwal)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($tanggalAkhir)->format('d/m/Y') }}
            </span>
            <span style="display:inline-flex; align-items:center; padding:6px 12px; border-radius:999px; background:#f3f7ff; color:#174e91; font-size:13px; font-weight:600;">
                <i class="bi bi-shop" style="margin-right:6px;"></i> {{ $selectedOutletName }}
            </span>
        </div>
    </div>

    {{-- ── 4 RANKING CARDS GRID ── --}}
    @php
        $cardsConfig = [
            ['title' => 'TM', 'data' => $paginatedTm],
            ['title' => 'SPV', 'data' => $paginatedSpv],
            ['title' => 'AM', 'data' => $paginatedAm],
            ['title' => 'Leader', 'data' => $paginatedLeader],
        ];
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
        @foreach($cardsConfig as $card)
            <div class="recap-card" style="display:flex; flex-direction:column; background:#fff; border-radius:18px; border:1px solid #e6ebf2; box-shadow:0 4px 12px rgba(0,0,0,0.02); height:100%; overflow:hidden;">
                
                <div class="recap-card-header" style="padding:16px 20px; border-bottom:1px solid #eef2f6; background:#fff;">
                    <h3 class="recap-card-title" style="margin:0; font-weight:800; font-size:16px;">Ranking {{ $card['title'] }}</h3>
                </div>

                <div class="recap-card-body" style="flex:1; display:flex; flex-direction:column; min-height:0;">
                    <div class="recap-table-wrap" style="flex:1; overflow-y:auto;">
                        <table class="table recap-table mb-0" style="width:100%; border-collapse:collapse;">
                            <thead style="position:sticky; top:0; z-index:10; background:#f8fafc;">
                                <tr>
                                    <th style="padding:12px 16px; font-size:12px; color:#667085; text-transform:uppercase; border-bottom:1px solid #e5e7eb;">{{ $card['title'] }}</th>
                                    <th style="padding:12px 16px; font-size:12px; color:#667085; text-transform:uppercase; border-bottom:1px solid #e5e7eb; text-align:center;">Score</th>
                                    <th style="padding:12px 16px; font-size:12px; color:#667085; text-transform:uppercase; border-bottom:1px solid #e5e7eb; text-align:center;">Rnk</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($card['data'] as $item)
                                    <tr style="border-bottom:1px solid #eef2f6;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                        <td style="padding:12px 16px; font-weight:600; font-size:13.5px; color:#101828;">{{ $item->nama ?? '-' }}</td>
                                        <td style="padding:12px 16px; text-align:center; font-family:var(--font-mono); font-weight:700; color:#206bc4; font-size:13px;">
                                            {{ number_format((float) ($item->average_score ?? 0), 2) }}%
                                        </td>
                                        <td style="padding:12px 16px; text-align:center;">
                                            <span style="display:inline-block; width:26px; height:26px; line-height:26px; text-align:center; border-radius:6px; background:#f3f7ff; color:#174e91; font-weight:800; font-size:12px;">
                                                {{ $item->ranking ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="text-align:center; padding:40px 20px;">
                                            <i class="bi bi-inbox text-muted" style="font-size:32px; display:block; margin-bottom:10px;"></i>
                                            <div style="font-weight:700; color:#344054;">Belum ada data {{ $card['title'] }}</div>
                                            <div style="font-size:12.5px; color:#667085; margin-top:4px;">Coba ubah filter rentang tanggal.</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($card['data']->hasPages())
                        <div class="recap-pagination" style="padding:12px 16px; border-top:1px solid #eef2f6; background:#fff;">
                            {{ $card['data']->links() }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

</div>

<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    
    /* Mengamankan styling bawaan Livewire Paginator agar tidak merusak layout Card */
    .recap-pagination nav { display:flex; justify-content:center; }
    .recap-pagination nav div.hidden.sm\:flex-1 { display: none !important; }
    .recap-pagination nav span.relative.z-0.inline-flex.rounded-md.shadow-sm { box-shadow: none !important; display: flex; gap: 4px; flex-wrap: wrap; justify-content: center; }
    .recap-pagination nav a, .recap-pagination nav span[aria-current="page"] > span, .recap-pagination nav span.relative.inline-flex { 
        border-radius: 8px !important; min-width: 32px; height: 32px; padding: 0 8px !important; display: inline-flex; align-items: center; justify-content: center; font-size: 13px !important; border: 1px solid #d5dde7 !important;
    }
    .recap-pagination nav span[aria-current="page"] > span { background: #206bc4 !important; color: #fff !important; border-color: #206bc4 !important; }
</style>
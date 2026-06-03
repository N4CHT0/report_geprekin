<div style="font-family: var(--font-sans); color: var(--text-primary); position: relative; background: #f6f8fb; min-height: 100vh; padding-bottom: 24px;">

    {{-- Loading Overlay --}}
    <div wire:loading.flex style="position:fixed; inset:0; background:rgba(255,255,255,0.7); backdrop-filter:blur(3px); z-index:999; align-items:center; justify-content:center;">
        <div style="padding:16px 24px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); display:flex; align-items:center; gap:12px; font-weight:600; color:#206bc4;">
            <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; font-size:20px;"></i> Mengambil Data Harian...
        </div>
    </div>

    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 6px 0; color: #182433;">Dashboard Harian</h1>
        <p style="margin: 0; font-size: 14px; color: #667085;">Monitoring checklist audit outlet per tanggal secara spesifik.</p>
    </div>

    {{-- ── FILTER CARD ── --}}
    <div style="padding: 20px; background: #fff; border: 1px solid #e6ebf2; border-radius: 18px; box-shadow: 0 1px 2px rgba(16,24,40,.04); margin-bottom: 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; align-items: end;">
            
            {{-- Filter Tanggal --}}
            <div style="width: 100%; display: flex; flex-direction: column;">
                <label style="font-size: 13px; font-weight: 700; color: #344054; margin-bottom: 8px;">Tanggal Audit</label>
                <input type="date" wire:model.live="tanggal" style="width: 100%; min-height: 46px; padding: 0 14px; border-radius: 12px; border: 1px solid #d5dde7; font-family: var(--font-mono); color: #101828; outline: none; background: #fff;">
            </div>

            {{-- Outlet Dropdown dengan Alpine Search --}}
            {{-- Outlet Dropdown dengan Alpine Search --}}
            <div x-data="{ 
                    open: false, 
                    search: '', 
                    selected: @entangle('filterOutletId').live,
                    outlets: @js($this->dropdownOutlets)
                }" 
                @click.outside="open = false" 
                style="position:relative; width: 100%; display: flex; flex-direction: column;">
                
                <label style="font-size: 13px; font-weight: 700; color: #344054; margin-bottom: 8px;">
                    Outlet
                </label>
                
                <div @click="open = !open" style="width: 100%; min-height: 46px; padding: 0 14px; border-radius: 12px; border: 1px solid #d5dde7; display:flex; align-items:center; justify-content:space-between; cursor:pointer; background:#fff;">
                    <span 
                        x-text="selected === '' || selected === null 
                            ? 'Semua Outlet' 
                            : (outlets.find(o => String(o.id) === String(selected))?.nama_outlet || 'Semua Outlet')" 
                        style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size: 14px; color: #101828;">
                    </span>
                    <i class="bi bi-chevron-down" style="color: #667085;"></i>
                </div>
                
                <div x-show="open" x-cloak style="position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:100; background:#fff; border:1px solid #d5dde7; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.1); overflow:hidden;">
                    <div style="padding:10px; border-bottom:1px solid #eef2f6; background:#f8fafc;">
                        <div style="position:relative;">
                            <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:#667085;"></i>
                            <input type="text" x-model="search" placeholder="Cari nama Outlet..." style="width:100%; padding:8px 12px 8px 34px; border:1px solid #d5dde7; border-radius:8px; outline:none; font-size:13.5px;">
                        </div>
                    </div>

                    <ul style="max-height:220px; overflow-y:auto; padding:6px; margin:0; list-style:none;">
                        <li @click="selected = ''; open = false; search = ''" style="padding:8px 12px; border-radius:6px; cursor:pointer; color:#667085; font-style:italic;">
                            Semua Outlet
                        </li>

                        <template x-for="outlet in outlets" :key="outlet.id">
                            <li 
                                x-show="search === '' || outlet.nama_outlet.toLowerCase().includes(search.toLowerCase())" 
                                @click="selected = outlet.id; open = false; search = ''"
                                x-text="outlet.nama_outlet"
                                style="padding:8px 12px; border-radius:6px; cursor:pointer; color:#101828; font-weight:500;">
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="width: 100%;">
                <button wire:click="resetFilter" style="width: 100%; min-height: 46px; border-radius: 12px; background: #fff; border: 1px solid #d5dde7; color: #344054; font-weight: 600; display:flex; align-items:center; justify-content:center; gap:8px; cursor:pointer; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                </button>
            </div>
        </div>
    </div>

    {{-- ── DATA CARDS GRID ── --}}
    @if($this->dashboardData->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(600px, 1fr)); gap: 24px;">
            @foreach($this->dashboardData as $card)
                <div style="background: #fff; border: 1.5px solid #111827; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05); display: flex; flex-direction: column;">
                    
                    {{-- Header Kartu --}}
                    <div style="padding: 16px 18px 12px; border-bottom: 1.5px solid #111827;">
                        <div style="text-align: center; font-size: 1.12rem; font-weight: 800; letter-spacing: .5px; color: #111827; margin-bottom: 12px; line-height: 1.3;">
                            CHECKLIST HARIAN OUTLET
                        </div>
                        
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                            {{-- Info Meta --}}
                            <div style="flex: 1 1 auto; min-width: 200px;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <tr><td style="width:70px; font-weight:600; font-size:13px; padding-bottom:4px;">Tanggal</td><td style="width:10px; font-weight:600;">:</td><td style="font-weight:500; font-size:13px;">{{ $card['tanggal'] }}</td></tr>
                                    <tr><td style="font-weight:600; font-size:13px; padding-bottom:4px;">Bulan</td><td style="font-weight:600;">:</td><td style="font-weight:500; font-size:13px;">{{ $card['bulan'] }}</td></tr>
                                    <tr><td style="font-weight:600; font-size:13px; padding-bottom:4px;">Tahun</td><td style="font-weight:600;">:</td><td style="font-weight:500; font-size:13px;">{{ $card['tahun'] }}</td></tr>
                                    <tr>
                                        <td style="font-weight:600; font-size:13px;">Outlet</td><td style="font-weight:600;">:</td>
                                        <td>
                                            <span style="display: inline-flex; padding: 4px 12px; border-radius: 999px; background: #e5e7eb; color: #111827; font-size: 13px; font-weight: 600;">
                                                {{ $card['outlet'] }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            {{-- Box Score --}}
                            <div style="min-width: 120px; border: 1.5px solid #111827; border-radius: 6px; overflow: hidden; flex-shrink: 0;">
                                <div style="background: #000; color: #fff; text-align: center; font-size: 12px; font-style: italic; font-weight: 700; padding: 6px; border-bottom: 1.5px solid #111827;">Score</div>
                                <div style="text-align: center; font-size: 24px; font-weight: 800; color: #111827; padding: 16px 10px;">{{ number_format($card['score'], 2) }}%</div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Checklist --}}
                    <div style="flex: 1; overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                            <thead>
                                <tr>
                                    <th style="background: #f8fafc; color: #111827; text-align: center; font-size: 12.5px; font-weight: 700; border-bottom: 1px solid #111827; border-right: 1px solid #111827; padding: 8px;">Jam</th>
                                    <th style="background: #f8fafc; color: #111827; text-align: center; font-size: 12.5px; font-weight: 700; border-bottom: 1px solid #111827; border-right: 1px solid #111827; padding: 8px;">Aktivitas</th>
                                    <th style="background: #f8fafc; color: #111827; text-align: center; font-size: 12.5px; font-weight: 700; border-bottom: 1px solid #111827; border-right: 1px solid #111827; padding: 8px;">Keterangan</th>
                                    <th style="background: #f8fafc; color: #111827; text-align: center; font-size: 12.5px; font-weight: 700; border-bottom: 1px solid #111827; padding: 8px;">Jam Input</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($card['rows'] as $row)
                                    <tr>
                                        <td style="border-bottom: 1px solid #111827; border-right: 1px solid #111827; padding: 8px; font-size: 12.5px; font-weight: 600; text-align: center;">{{ $row['jam'] }}</td>
                                        <td style="border-bottom: 1px solid #111827; border-right: 1px solid #111827; padding: 8px; font-size: 12.5px; white-space: pre-line; min-width: 220px; line-height: 1.4;">{!! nl2br(e($row['pertanyaan'])) !!}</td>
                                        <td style="border-bottom: 1px solid #111827; border-right: 1px solid #111827; padding: 0;">
                                            <div style="width: 100%; height: 100%; min-height: 48px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; text-transform: uppercase; 
                                                {{ $row['status'] == 'success' ? 'background: #2d5b15;' : ($row['status'] == 'warning' ? 'background: #f59e0b; color: #111827;' : 'background: #ff1717;') }}"
                                                title="{{ $row['status_label'] }}">
                                                {{ $row['status_label'] }}
                                            </div>
                                        </td>
                                        <td style="border-bottom: 1px solid #111827; padding: 8px; font-size: 12.5px; text-align: center; font-family: var(--font-mono); font-weight: 600;">{{ $row['jam_input'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div style="background: #fff; border: 1px solid #e6ebf2; border-radius: 18px; padding: 60px 20px; text-align: center; box-shadow: 0 1px 2px rgba(16,24,40,.04);">
            <div style="font-size: 48px; margin-bottom: 16px;">📋</div>
            <h5 style="font-size: 18px; font-weight: 700; color: #182433; margin: 0 0 8px 0;">Belum ada data dashboard harian</h5>
            <p style="margin: 0; color: #667085; font-size: 14px;">Coba pilih tanggal atau outlet yang lain pada filter di atas.</p>
        </div>
    @endif

</div>

<style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @media (max-width: 768px) {
        /* Memaksa grid menjadi 1 kolom pada layar HP */
        div[style*="grid-template-columns: repeat(auto-fit, minmax(600px, 1fr))"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
<div style="font-family: var(--font-sans); color: var(--text-primary); position: relative;">

    {{-- Loading Overlay Global --}}
    <div wire:loading.flex style="position:absolute; inset:0; background:rgba(255,255,255,0.6); backdrop-filter:blur(3px); z-index:50; align-items:center; justify-content:center;">
        <div class="badge badge-neutral" style="font-size:14px; padding:12px 24px; box-shadow:0 10px 30px rgba(0,0,0,0.1); background:var(--bg-surface); display:flex; align-items:center; gap:10px;">
            <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; font-size:18px;"></i> Mengkalkulasi Ranking...
        </div>
    </div>

    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 6px 0; color: var(--text-primary);">Ranking Outlet</h1>
        <p style="margin: 0; font-size: 14px; color: var(--text-muted);">Peringkat outlet berdasarkan nilai compliance audit.</p>
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
        </div>
    </div>

    {{-- ── SUMMARY METRICS ── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="bi bi-shop"></i></div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Outlet</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">{{ $summary['total'] }}</div>
            </div>
        </div>
        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(22, 163, 74, 0.1); color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="bi bi-trophy-fill"></i></div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Nilai Tertinggi</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">{{ number_format($summary['max'], 2) }}%</div>
            </div>
        </div>
        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(202, 138, 4, 0.1); color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 20px;"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Nilai Rata-rata</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">{{ number_format($summary['avg'], 2) }}%</div>
            </div>
        </div>
    </div>

    {{-- ── CHART.JS (DIKENDALIKAN ALPINE.JS) ── --}}
    <div class="c-card" style="margin-bottom: 24px; overflow: hidden;"
         x-data="{
            initChart() {
                const ctx = this.$refs.canvas.getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: { labels: [], datasets: [{ label: 'Score (%)', data: [], backgroundColor: '#2563eb', borderRadius: 6 }] },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
                });
            }
         }"
         x-init="initChart()"
         @update-ranking-chart.window="
            const myChart = Chart.getChart($refs.canvas);
            if (myChart) {
                const wrap = (str) => { 
                    const words = str.split(' '); 
                    return words.reduce((acc, w, i) => { 
                        if(i%2===0) acc.push(w); 
                        else acc[acc.length-1] += ' '+w; 
                        return acc; 
                    }, []); 
                };
                myChart.data.labels = $event.detail.labels.map(wrap);
                myChart.data.datasets[0].data = $event.detail.values;
                myChart.update();
            }
         ">
        <div class="c-card-header">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">Grafik Top 10 Outlet</h3>
        </div>
        <div style="padding: 20px; height: 350px;" wire:ignore>
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>

    {{-- ── TABEL RANKING ── --}}
    <div class="c-card">
        <div class="c-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <h3 style="margin: 0; font-size: 16px; font-weight: 800;">Tabel Ranking Outlet</h3>
            {{-- Pengganti Search Box DataTables --}}
            <div style="position:relative; min-width:240px;">
                <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-muted);"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari outlet / leader / am..." class="f-input" style="padding-left:34px;">
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table class="c-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width: 60px;">Rnk</th>
                        <th style="min-width: 180px;">Outlet</th>
                        <th>Leader</th>
                        <th>SPV</th>
                        <th>AM</th>
                        <th style="text-align:right;">Score</th>
                        <th style="text-align:center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedData as $item)
                    <tr>
                        <td style="text-align:center;">
                            <span style="display:inline-block; width:30px; height:30px; line-height:30px; text-align:center; border-radius:50%; font-weight:bold; font-size:12px; 
                                {{ $item->ranking == 1 ? 'background:linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color:#92400e;' : 
                                  ($item->ranking == 2 ? 'background:linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); color:#475569;' : 
                                  ($item->ranking == 3 ? 'background:linear-gradient(135deg, #ffedd5 0%, #fdba74 100%); color:#9a3412;' : 'background:#f1f5f9; color:#0f172a;')) }}">
                                {{ $item->ranking }}
                            </span>
                        </td>
                        <td style="font-weight: 600;">{{ $item->nama_outlet }}</td>
                        <td style="color:var(--text-secondary); font-size:13px;">{{ $item->leader }}</td>
                        <td style="color:var(--text-secondary); font-size:13px;">{{ $item->spv }}</td>
                        <td style="color:var(--text-secondary); font-size:13px;">{{ $item->am }}</td>
                        <td style="text-align:right; font-weight:bold; font-family:var(--font-mono);">{{ number_format($item->score, 2) }}%</td>
                        <td style="text-align:center;">
                            <span class="badge" style="{{ $item->kategori == 'Excellent' ? 'background:#ecfdf3; color:#027a48; border:1px solid #d1fadf;' : ($item->kategori == 'Good' ? 'background:#fffaeb; color:#b54708; border:1px solid #fedf89;' : 'background:#fef3f2; color:#b42318; border:1px solid #fecdca;') }}">
                                {{ $item->kategori }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada data outlet yang sesuai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="padding:16px 20px; border-top:1px solid var(--border-subtle);">
            {{ $paginatedData->links() }}
        </div>
    </div>
</div>

{{-- Pastikan Chart.js di-load di layout utama Anda (header/footer). Jika belum: --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
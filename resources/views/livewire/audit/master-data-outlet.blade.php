<div style="font-family: var(--font-sans); color: var(--text-primary);">

    {{-- ── HEADER / TITLE ── --}}
    <div style="margin-bottom: 24px; padding: 0 4px;">
        <h2 style="font-size: 24px; font-weight: 700; margin: 0; color: var(--text-primary);">Data Outlet</h2>
        <p style="font-size: 13.5px; color: var(--text-muted); margin-top: 4px;">Daftar seluruh outlet yang terdaftar dalam sistem operasional.</p>
    </div>

    {{-- ── FILTER CARD ── --}}
    <div class="c-card" style="margin-bottom: 18px;">
        <div class="c-card-header">
            <h3 style="font-size: 15px; font-weight: 700; margin: 0;">Filter Pencarian</h3>
        </div>
        <div style="padding: 20px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                
                {{-- Cari Keyword --}}
                <div>
                    <label class="f-label">Cari Outlet</label>
                    <input type="text" wire:model.live.debounce.300ms="keyword" class="f-input" placeholder="Nama / Kode / Alamat...">
                </div>

                {{-- Kota (Alpine Combobox) --}}
                <div x-data="comboSelect('{{ $kota }}', 'Semua Kota')" @click.outside="open = false">
                    <label class="f-label">Kota</label>
                    <div style="position:relative;">
                        <input type="hidden" wire:model="kota" x-model="value">
                        <div class="combo-trigger" @click="open = !open" style="min-height: 38px;">
                            <span x-text="label" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
                            <i class="bi bi-chevron-down"></i>
                        </div>
                        <div class="combo-panel" x-show="open" x-cloak>
                            <div class="combo-search">
                                <input type="text" x-model="search" placeholder="Cari nama kota...">
                            </div>
                            <ul x-ref="list" class="combo-list" style="margin:0;">
                                <li class="combo-item" @click="select('', 'Semua Kota')" data-id="" style="color:var(--text-muted); font-style:italic;">Semua Kota</li>
                                @foreach($this->kotaList as $k)
                                    <li class="combo-item" 
                                        x-show="search === '' || @js(strtolower($k->kota)).includes(search.toLowerCase())"
                                        @click="select('{{ $k->kota }}', @js($k->kota))"
                                        data-id="{{ $k->kota }}">
                                        {{ $k->kota }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="f-label">Status Operasional</label>
                    <div class="f-select-wrap">
                        <select wire:model.live="status" class="f-select" style="min-height: 38px; cursor:pointer;">
                            <option value="">Semua Status</option>
                            <option value="existing">Aktif (Existing)</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                        <i class="bi bi-chevron-down"></i>
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
            <h3 style="font-size: 15px; font-weight: 700; margin: 0;">Daftar Data Outlet</h3>
            <div style="font-size: 12.5px; color: var(--text-muted); font-family: var(--font-mono);">
                Total Data: <strong style="color:var(--text-primary)">{{ $data->total() }}</strong>
            </div>
        </div>

        <div style="overflow-x:auto; position:relative;">
            
            {{-- Loading State --}}
            <div wire:loading.flex style="position:absolute; inset:0; background:rgba(255,255,255,0.75); backdrop-filter:blur(2px); z-index:10; align-items:center; justify-content:center;">
                <div class="badge badge-neutral" style="font-size:13px; padding:8px 16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); background:var(--bg-surface);">
                    <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; margin-right:8px;"></i> Memuat data...
                </div>
            </div>

            <table class="c-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 5%; text-align:center;">No</th>
                        <th style="width: 25%;">Nama Outlet</th>
                        <th style="width: 15%;">Kode Outlet</th>
                        <th style="width: 15%;">Kota</th>
                        <th style="width: 10%; text-align:center;">Status</th>
                        <th style="width: 30%;">Alamat Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $index => $item)
                        <tr wire:key="outlet-{{ $item->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                            <td style="text-align:center; color:var(--text-muted);">{{ $data->firstItem() + $index }}</td>
                            <td style="font-weight: 600; color:var(--text-primary);">{{ $item->nama_outlet }}</td>
                            <td style="font-family: var(--font-mono); font-size:12.5px;">{{ $item->kode_outlet ?? '-' }}</td>
                            <td>{{ $item->kota ?? '-' }}</td>
                            <td style="text-align:center;">
                                @if($item->status == 'existing')
                                    <span class="badge" style="background:#ecfdf3; color:#027a48; border:1px solid #d1fadf;">Aktif</span>
                                @else
                                    <span class="badge" style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca;">Nonaktif</span>
                                @endif
                            </td>
                            <td style="color:var(--text-secondary); line-height:1.4;">{{ $item->alamat ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding: 40px 20px;">
                                <i class="bi bi-inbox" style="font-size:32px; color:var(--border-muted); display:block; margin-bottom:8px;"></i>
                                <div style="color:var(--text-secondary); font-weight:500;">Tidak ada data outlet yang ditemukan.</div>
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

    {{-- Alpine Logic untuk Combobox khusus Livewire --}}
    <script>
        if (typeof comboSelect !== 'function') {
            function comboSelect(initialValue, defaultLabel) {
                return {
                    open: false,
                    search: '',
                    value: initialValue,
                    label: defaultLabel,
                    init() {
                        this.$nextTick(() => {
                            let el = this.$refs.list.querySelector(`[data-id='${this.value}']`);
                            if (el && this.value !== '') this.label = el.innerText.trim();
                        });
                    },
                    select(id, name) {
                        this.value = id;
                        this.label = name;
                        this.open = false;
                        this.search = '';
                        this.$dispatch('input', id); // Memicu update ke Livewire wire:model
                    }
                }
            }
        }
    </script>
</div>
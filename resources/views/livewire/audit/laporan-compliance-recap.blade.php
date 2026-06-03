<div style="font-family: var(--font-sans); color: var(--text-primary); position: relative;">

    {{-- Loading Overlay --}}
    <div wire:loading.flex style="position:absolute; inset:0; background:rgba(255,255,255,0.6); backdrop-filter:blur(3px); z-index:50; align-items:center; justify-content:center;">
        <div class="badge badge-neutral" style="font-size:14px; padding:12px 24px; box-shadow:0 10px 30px rgba(0,0,0,0.1); background:var(--bg-surface); display:flex; align-items:center; gap:10px;">
            <i class="bi bi-arrow-repeat" style="animation: spin 1s linear infinite; font-size:18px;"></i>
            Mengkalkulasi Matriks...
        </div>
    </div>

    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 6px 0; color: var(--text-primary);">
            Compliance Recap
        </h1>
        <p style="margin: 0; font-size: 14px; color: var(--text-muted);">
            Ringkasan compliance outlet per hari dan rata-rata periode.
        </p>
    </div>

    {{-- Filter --}}
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

            <div x-data="{ open: false, search: '', selected: @entangle('filterOutlet').live }"
                 @click.outside="open = false"
                 style="position:relative; width: 100%;">
                <label class="f-label">Filter Outlet</label>

                <div class="combo-trigger" @click="open = !open">
                    <span x-text="selected === '' ? 'Semua Outlet' : ($wire.dropdownOutlets.find(o => o.id == selected)?.nama_outlet || 'Semua Outlet')" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></span>
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
                        <li class="combo-item" @click="selected = ''; open = false; search = ''" style="color:var(--text-muted); font-style:italic;">
                            Semua Outlet
                        </li>

                        @foreach($this->dropdownOutlets as $outlet)
                            <li class="combo-item"
                                x-show="search === '' || @js(strtolower($outlet->nama_outlet)).includes(search.toLowerCase())"
                                @click="selected = '{{ $outlet->id }}'; open = false; search = ''">
                                {{ $outlet->nama_outlet }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>

    @php
        $safeRate = min(100, max(0, (float) ($complianceRate ?? 0)));
        $recapRows = isset($paginatedRecap) ? $paginatedRecap : ($recap ?? collect());
        $tanggalRows = $periodeTanggal ?? [];
    @endphp

    {{-- Summary --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(37, 99, 235, 0.1); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-clipboard-check-fill"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Audit</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">
                    {{ $totalAudit ?? 0 }}
                </div>
            </div>
        </div>

        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(22, 163, 74, 0.1); color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Total Compliance (Ya)</div>
                <div style="font-size: 24px; font-weight: 800; color: var(--text-primary); font-family: var(--font-mono);">
                    {{ $totalYa ?? 0 }}
                </div>
            </div>
        </div>

        <div class="c-card" style="padding: 16px 20px; display: flex; align-items: center; gap: 16px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(202, 138, 4, 0.1); color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-graph-up"></i>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Compliance Rate</div>
                <div style="font-size: 24px; font-weight: 800; color: #2563eb; font-family: var(--font-mono);">
                    {{ number_format($safeRate, 2) }}%
                </div>
            </div>
        </div>
    </div>

    {{-- Matrix --}}
    <div class="c-card">
        <div class="c-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 800;">Matrix Compliance Recap</h3>
                <p style="margin: 0; font-size: 13px; color: var(--text-muted);">Pantau score outlet harian dan nilai akhir periode.</p>
            </div>

            <div style="position:relative; min-width:240px;">
                <i class="bi bi-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:13px; color:var(--text-muted);"></i>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari outlet / leader / am..." class="f-input" style="padding-left:34px;">
            </div>
        </div>

        <div style="overflow-x: auto; padding-bottom: 8px;">
            <table class="c-table custom-matrix">
                <thead>
                    <tr>
                        <th class="sticky-col" style="width: 60px; text-align:center;">No</th>
                        <th class="sticky-col-2" style="min-width: 200px;">Outlet</th>
                        <th style="min-width: 140px;">Leader</th>
                        <th style="min-width: 140px;">SPV</th>
                        <th style="min-width: 140px;">AM</th>
                        <th style="min-width: 100px; text-align:center;">Score Akhir</th>

                        @foreach($tanggalRows as $tanggal)
                            <th style="min-width: 90px; text-align:center;">
                                {{ \Carbon\Carbon::parse($tanggal)->format('d/m') }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($recapRows as $index => $row)
                        @php
                            $score = min(100, max(0, (float) ($row->score ?? 0)));

                            $scoreStyle = $score >= 90
                                ? 'background:#ecfdf3; color:#027a48;'
                                : ($score >= 60
                                    ? 'background:#fffaeb; color:#b54708;'
                                    : 'background:#fef3f2; color:#b42318;');

                            $scoreLabel = $score >= 90
                                ? 'Excellent'
                                : ($score >= 60 ? 'Good' : 'Poor');

                            $rowNumber = method_exists($recapRows, 'firstItem')
                                ? $recapRows->firstItem() + $index
                                : $index + 1;
                        @endphp

                        <tr>
                            <td class="sticky-col" style="text-align:center; font-family:var(--font-mono); color:var(--text-muted); background:var(--bg-surface);">
                                {{ $rowNumber }}
                            </td>

                            <td class="sticky-col-2" style="font-weight: 600; background:var(--bg-surface); box-shadow: 2px 0 5px -2px rgba(0,0,0,0.05);">
                                {{ $row->nama_outlet }}
                            </td>

                            <td style="color:var(--text-secondary); font-size:13px;">{{ $row->leader }}</td>
                            <td style="color:var(--text-secondary); font-size:13px;">{{ $row->spv }}</td>
                            <td style="color:var(--text-secondary); font-size:13px;">{{ $row->am }}</td>

                            <td style="text-align:center; {{ $scoreStyle }}">
                                <div style="font-weight:800; font-family:var(--font-mono); font-size:14px;">
                                    {{ number_format($score, 2) }}%
                                </div>
                                <div style="font-size:10px; font-weight:700; text-transform:uppercase;">
                                    {{ $scoreLabel }}
                                </div>
                            </td>

                            @foreach($tanggalRows as $tanggal)
                                @php
                                    $dScore = min(100, max(0, (float) ($row->daily_scores[$tanggal] ?? 0)));

                                    $dailyStyle = $dScore >= 90
                                        ? 'background:#ecfdf3; color:#027a48;'
                                        : ($dScore >= 60
                                            ? 'background:#fffaeb; color:#b54708;'
                                            : 'background:#fef3f2; color:#b42318;');
                                @endphp

                                <td style="text-align:center; font-family:var(--font-mono); font-size:13px; font-weight:600; {{ $dailyStyle }}">
                                    {{ number_format($dScore, 0) }}%
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 6 + count($tanggalRows) }}" style="text-align:center; padding:40px; color:var(--text-muted);">
                                Tidak ada data recap yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($recapRows, 'links'))
            <div style="padding:16px 20px; border-top:1px solid var(--border-subtle);">
                {{ $recapRows->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .custom-matrix th,
    .custom-matrix td {
        border-right: 1px solid var(--border-faint);
    }

    .custom-matrix .sticky-col,
    .custom-matrix .sticky-col-2 {
        position: sticky;
        z-index: 4;
        background: var(--bg-surface);
    }

    .custom-matrix thead .sticky-col,
    .custom-matrix thead .sticky-col-2 {
        z-index: 5;
        background: var(--bg-overlay);
    }

    .custom-matrix .sticky-col {
        left: 0;
        border-right: none;
    }

    .custom-matrix .sticky-col-2 {
        left: 60px;
    }
</style>

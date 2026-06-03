{{-- resources/views/investor/laporan_pertahun.blade.php --}}
@section('title', 'Laporan Penjualan Tahunan')
@section('breadcrumb', 'Laporan / Penjualan Tahunan')

@include('Temp.Investor.header')

@php
    $tahun = $tahun ?? date('Y');
    $selectedOutlet = $selectedOutlet ?? '';
    $selectedEcommerce = $selectedEcommerce ?? [];
    $bulanLabels = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
    $allData = $data ?? [];
    $grandOutlets = array_values(array_filter($allData, fn($o) => !empty($o['is_grand'])));
@endphp

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 44px !important;
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            outline: none !important;
        }

        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px #dbeafe !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            height: 44px !important;
            line-height: 44px !important;
            padding-left: 12px !important;
            padding-right: 34px !important;
            color: #0f172a !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #64748b !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
            right: 8px !important;
        }

        .select2-dropdown {
            border-color: #cbd5e1 !important;
            border-radius: 0.75rem !important;
            overflow: hidden !important;
        }

        .select2-search--dropdown .select2-search__field {
            border-radius: 0.5rem !important;
            border-color: #cbd5e1 !important;
            outline: none !important;
            padding: 8px 10px !important;
            font-size: 0.875rem !important;
        }

        .select2-results__option {
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            padding: 8px 12px !important;
        }
    </style>
@endpush

<div
    x-data="{
        activeTab: 'all',
        search: '',
        showFilters: true
    }"
    class="min-h-screen bg-slate-50 text-slate-900"
>
    <div class="mx-auto flex w-full max-w-[1920px] flex-col gap-4 px-3 py-4 sm:px-5 lg:px-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">
                        Laporan / Penjualan Tahunan
                    </div>
                    <h1 class="mt-1 text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">
                        Laporan Penjualan Per Tahun
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">
                        Outlet duplikat digabung berdasarkan nama outlet. Sales, CU, AVG, dan AVG Size dikalkulasi dari seluruh ID outlet duplikat.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-end">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Periode</div>
                        <div class="mt-0.5 text-sm font-extrabold text-slate-900">{{ $tahun ?: 'Belum dipilih' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Jumlah Bulan</div>
                        <div class="mt-0.5 text-sm font-extrabold text-slate-900">12 Bulan</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Outlet Tampil</div>
                        <div class="mt-0.5 text-sm font-extrabold text-slate-900">{{ count($allData) }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Grand Opening</div>
                        <div class="mt-0.5 text-sm font-extrabold text-slate-900">{{ count($grandOutlets) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 p-4">
                <div>
                    <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-800">Filter Laporan</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500">Atur tahun, outlet, dan ecommerce.</p>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 lg:hidden"
                    @click="showFilters = !showFilters"
                >
                    <span x-text="showFilters ? 'Tutup' : 'Buka'"></span>
                </button>
            </div>

            <form
                x-show="showFilters"
                x-transition
                method="GET"
                action="{{ route('investor.laporan.pertahun') }}"
                id="formLaporanTahun"
                class="p-4"
            >
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-3">
                        <label for="tahun" class="mb-2 block text-xs font-extrabold uppercase tracking-wide text-slate-600">
                            Tahun
                        </label>
                        <input
                            type="number"
                            id="tahun"
                            name="tahun"
                            min="2000"
                            max="{{ date('Y') + 1 }}"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            value="{{ $tahun ?? date('Y') }}"
                        >
                    </div>

                    <div class="lg:col-span-4">
                        <label for="outlet" class="mb-2 block text-xs font-extrabold uppercase tracking-wide text-slate-600">
                            Outlet
                        </label>
                        <select
                            id="outlet"
                            name="outlet"
                            class="js-select2-outlet h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            data-placeholder="-- Semua Outlet --"
                        >
                            <option value="">-- Semua Outlet --</option>
                            @foreach ($outletList ?? [] as $outlet)
                                <option value="{{ $outlet->id }}" {{ (string) $selectedOutlet === (string) $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->nama_outlet }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Jika outlet duplikat dipilih, seluruh data outlet dengan nama sama tetap digabung.
                        </p>
                    </div>

                    <div class="lg:col-span-5">
                        <label class="mb-2 block text-xs font-extrabold uppercase tracking-wide text-slate-600">
                            Aksi
                        </label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <button
                                type="submit"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                                Filter
                            </button>

                            <a
                                href="{{ route('investor.laporan.pertahun') }}"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Reset
                            </a>

                            <a
                                href="{{ route('investor.laporan.pertahun.export', [
                                    'tahun' => $tahun,
                                    'outlet' => $selectedOutlet,
                                    'ecommerce' => $selectedEcommerce
                                ]) }}"
                                id="btnExcel"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-extrabold text-emerald-700 shadow-sm transition hover:bg-emerald-100"
                            >
                                Excel
                            </a>

                            <button
                                type="button"
                                id="btnPDF"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 text-sm font-extrabold text-rose-700 shadow-sm transition hover:bg-rose-100"
                            >
                                PDF
                            </button>
                        </div>

                        <div id="exportProgress" class="mt-3 hidden h-1.5 overflow-hidden rounded-full bg-blue-100">
                            <div class="h-full w-1/2 animate-pulse rounded-full bg-blue-600"></div>
                        </div>
                    </div>

                    <div class="lg:col-span-12">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                            <div class="mb-3">
                                <div class="text-xs font-extrabold uppercase tracking-wide text-slate-600">
                                    Ecommerce
                                </div>
                                <div class="text-xs font-medium text-slate-500">
                                    Kosongkan checklist ecommerce jika ingin menampilkan sales normal dari laporan tahunan.
                                </div>
                            </div>

                            @if (!empty($ecommerceList) && count($ecommerceList) > 0)
                                <div class="grid max-h-52 grid-cols-1 gap-2 overflow-auto rounded-xl border border-slate-200 bg-white p-3 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                                    @foreach ($ecommerceList as $eco)
                                        @php
                                            $ecoId = 'eco_' . \Illuminate\Support\Str::slug($eco, '_');
                                        @endphp
                                        <label for="{{ $ecoId }}" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
                                            <input
                                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                                type="checkbox"
                                                name="ecommerce[]"
                                                value="{{ $eco }}"
                                                id="{{ $ecoId }}"
                                                {{ in_array($eco, $selectedEcommerce ?? []) ? 'checked' : '' }}
                                            >
                                            <span class="truncate">{{ $eco }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-sm font-semibold text-slate-500">
                                    Data ecommerce belum tersedia.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex gap-2 overflow-x-auto">
                    <button
                        type="button"
                        @click="activeTab = 'all'"
                        class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-extrabold transition"
                        :class="activeTab === 'all' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        All Data
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'grand'"
                        class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-extrabold transition"
                        :class="activeTab === 'grand' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    >
                        Grand Opening
                    </button>
                </div>

                <div class="relative w-full lg:w-80">
                    <input
                        type="text"
                        x-model.debounce.250ms="search"
                        placeholder="Cari outlet / area / kota / provinsi..."
                        class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </div>
            </div>

            <div class="p-4">
                {{-- ALL DATA --}}
                <div x-show="activeTab === 'all'" x-transition>
                    @php
                        $tableData = $allData;
                    @endphp

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="flex flex-col gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm font-extrabold text-slate-900">
                                    Total Baris: {{ count($tableData) }}
                                </div>
                                <div class="text-xs font-medium text-slate-500">
                                    Kolom "ID Digabung" menunjukkan jumlah outlet duplikat yang dikalkulasi dalam satu baris.
                                </div>
                            </div>
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Geser tabel ke kanan untuk melihat bulan
                            </div>
                        </div>

                        <div class="max-h-[72vh] overflow-auto">
                            <table id="laporanTable" class="min-w-[1800px] border-separate border-spacing-0 text-sm">
                                <thead class="sticky top-0 z-20">
                                    <tr class="bg-slate-100 text-center text-[11px] font-extrabold uppercase tracking-wide text-slate-600">
                                        <th rowspan="2" class="sticky left-0 z-30 border-b border-r border-slate-200 bg-slate-100 px-3 py-3">No</th>
                                        <th rowspan="2" class="sticky left-[54px] z-30 min-w-64 border-b border-r border-slate-200 bg-slate-100 px-3 py-3 text-left">Nama Outlet</th>
                                        <th rowspan="2" class="min-w-36 border-b border-r border-slate-200 px-3 py-3 text-left">Area</th>
                                        <th rowspan="2" class="min-w-32 border-b border-r border-slate-200 px-3 py-3 text-left">Kota</th>
                                        <th rowspan="2" class="min-w-40 border-b border-r border-slate-200 px-3 py-3 text-left">Provinsi</th>
                                        <th rowspan="2" class="min-w-24 border-b border-r border-slate-200 px-3 py-3">ID Digabung</th>

                                        @foreach ($bulanLabels as $bln)
                                            <th colspan="4" class="border-b border-r border-slate-200 bg-slate-200/70 px-3 py-3">{{ $bln }}</th>
                                        @endforeach

                                        <th colspan="4" class="border-b border-r border-amber-200 bg-amber-100 px-3 py-3 text-amber-900">Sub Total</th>
                                    </tr>

                                    <tr class="bg-slate-100 text-center text-[11px] font-extrabold uppercase tracking-wide text-slate-600">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <th class="border-b border-r border-slate-200 px-3 py-2">Sales</th>
                                            <th class="border-b border-r border-slate-200 px-3 py-2">CU</th>
                                            <th class="border-b border-r border-slate-200 px-3 py-2">AVG</th>
                                            <th class="border-b border-r border-slate-200 px-3 py-2">AVG Size</th>
                                        @endfor

                                        <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">Sales</th>
                                        <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">CU</th>
                                        <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">AVG</th>
                                        <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">AVG Size</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($tableData as $index => $outlet)
                                        @php
                                            $searchText = strtolower(
                                                ($outlet['nama_outlet'] ?? '') . ' ' .
                                                ($outlet['area'] ?? '') . ' ' .
                                                ($outlet['kota'] ?? '') . ' ' .
                                                ($outlet['provinsi'] ?? '')
                                            );
                                        @endphp

                                        <tr
                                            class="group hover:bg-blue-50/60"
                                            x-show="!search || '{{ addslashes($searchText) }}'.includes(search.toLowerCase())"
                                        >
                                            <td class="sticky left-0 z-10 border-r border-slate-200 bg-white px-3 py-2 text-center font-bold text-slate-700 group-hover:bg-blue-50">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="sticky left-[54px] z-10 border-r border-slate-200 bg-white px-3 py-2 text-left font-extrabold text-slate-900 group-hover:bg-blue-50">
                                                <div class="flex flex-col gap-1">
                                                    <span>{{ $outlet['nama_outlet'] ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td class="border-r border-slate-100 px-3 py-2 text-left font-semibold text-slate-700">{{ $outlet['area'] ?? '-' }}</td>
                                            <td class="border-r border-slate-100 px-3 py-2 text-left font-semibold text-slate-700">{{ $outlet['kota'] ?? '-' }}</td>
                                            <td class="border-r border-slate-100 px-3 py-2 text-left font-semibold text-slate-700">{{ $outlet['provinsi'] ?? '-' }}</td>
                                            <td class="border-r border-slate-100 px-3 py-2 text-center font-extrabold text-slate-700">{{ $outlet['duplicate_count'] ?? 1 }}</td>

                                            @for ($m = 1; $m <= 12; $m++)
                                                @php
                                                    $sales = $outlet['bulan'][$m]['sales'] ?? 0;
                                                    $cu = $outlet['bulan'][$m]['cu'] ?? 0;
                                                    $avg = $outlet['bulan'][$m]['ac'] ?? 0;
                                                    $avgSize = $outlet['bulan'][$m]['basket_size'] ?? 0;
                                                @endphp

                                                <td class="border-r border-slate-100 px-3 py-2 text-right font-bold tabular-nums">{{ number_format($sales) }}</td>
                                                <td class="border-r border-slate-100 px-3 py-2 text-center font-semibold tabular-nums">{{ $cu }}</td>
                                                <td class="border-r border-slate-100 px-3 py-2 text-right font-bold tabular-nums">{{ number_format($avg) }}</td>
                                                <td class="border-r border-slate-100 px-3 py-2 text-right font-bold tabular-nums">{{ number_format($avgSize, 2) }}</td>
                                            @endfor

                                            @php
                                                $subSales = $outlet['sub_total']['sales'] ?? 0;
                                                $subCu = $outlet['sub_total']['cu'] ?? 0;
                                                $subAvg = $outlet['sub_total']['ac'] ?? 0;
                                                $subAvgSize = $outlet['sub_total']['basket_size'] ?? 0;
                                            @endphp

                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-right font-extrabold tabular-nums">{{ number_format($subSales) }}</td>
                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-center font-extrabold tabular-nums">{{ $subCu }}</td>
                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-right font-extrabold tabular-nums">{{ number_format($subAvg) }}</td>
                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-right font-extrabold tabular-nums">{{ number_format($subAvgSize, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 6 + (12 * 4) + 4 }}" class="px-4 py-10 text-center">
                                                <div class="text-base font-extrabold text-slate-800">Data tidak tersedia</div>
                                                <div class="mt-1 text-sm font-medium text-slate-500">Silakan pilih tahun atau ubah filter.</div>
                                            </td>
                                        </tr>
                                    @endforelse

                                    <tr class="bg-blue-600 text-white">
                                        <td class="sticky left-0 z-10 border-r border-blue-500 bg-blue-600 px-3 py-3 text-center font-extrabold">#</td>
                                        <td class="sticky left-[54px] z-10 border-r border-blue-500 bg-blue-600 px-3 py-3 text-left font-extrabold">GRAND TOTAL</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-left font-bold">-</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-left font-bold">-</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-left font-bold">-</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-center font-extrabold">-</td>

                                        @for ($m = 1; $m <= 12; $m++)
                                            @php
                                                $gsales = $grandTotal['bulan'][$m]['sales'] ?? 0;
                                                $gcu = $grandTotal['bulan'][$m]['cu'] ?? 0;
                                                $gac = $grandTotal['bulan'][$m]['ac'] ?? 0;
                                                $gbasket = $grandTotal['bulan'][$m]['basket_size'] ?? 0;
                                            @endphp

                                            <td class="border-r border-blue-500 px-3 py-3 text-right font-extrabold tabular-nums">{{ number_format($gsales) }}</td>
                                            <td class="border-r border-blue-500 px-3 py-3 text-center font-extrabold tabular-nums">{{ $gcu }}</td>
                                            <td class="border-r border-blue-500 px-3 py-3 text-right font-extrabold tabular-nums">{{ number_format($gac) }}</td>
                                            <td class="border-r border-blue-500 px-3 py-3 text-right font-extrabold tabular-nums">{{ number_format($gbasket, 2) }}</td>
                                        @endfor

                                        <td class="border-r border-blue-500 px-3 py-3 text-right font-extrabold tabular-nums">{{ number_format($grandTotal['sales'] ?? 0) }}</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-center font-extrabold tabular-nums">{{ $grandTotal['cu'] ?? 0 }}</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-right font-extrabold tabular-nums">{{ number_format($grandTotal['ac'] ?? 0) }}</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-right font-extrabold tabular-nums">{{ number_format($grandTotal['basket_size'] ?? ($grandTotal['basket'] ?? 0), 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- GRAND OPENING --}}
                <div x-show="activeTab === 'grand'" x-transition>
                    @php
                        $tableData = $grandOutlets;
                    @endphp

                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <div class="flex flex-col gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm font-extrabold text-slate-900">
                                    Total Baris: {{ count($tableData) }}
                                </div>
                                <div class="text-xs font-medium text-slate-500">
                                    Kolom "ID Digabung" menunjukkan jumlah outlet duplikat yang dikalkulasi dalam satu baris.
                                </div>
                            </div>
                            <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Geser tabel ke kanan untuk melihat bulan
                            </div>
                        </div>

                        <div class="max-h-[72vh] overflow-auto">
                            <table id="grandTable" class="min-w-[1800px] border-separate border-spacing-0 text-sm">
                                <thead class="sticky top-0 z-20">
                                    <tr class="bg-slate-100 text-center text-[11px] font-extrabold uppercase tracking-wide text-slate-600">
                                        <th rowspan="2" class="sticky left-0 z-30 border-b border-r border-slate-200 bg-slate-100 px-3 py-3">No</th>
                                        <th rowspan="2" class="sticky left-[54px] z-30 min-w-64 border-b border-r border-slate-200 bg-slate-100 px-3 py-3 text-left">Nama Outlet</th>
                                        <th rowspan="2" class="min-w-36 border-b border-r border-slate-200 px-3 py-3 text-left">Area</th>
                                        <th rowspan="2" class="min-w-32 border-b border-r border-slate-200 px-3 py-3 text-left">Kota</th>
                                        <th rowspan="2" class="min-w-40 border-b border-r border-slate-200 px-3 py-3 text-left">Provinsi</th>
                                        <th rowspan="2" class="min-w-24 border-b border-r border-slate-200 px-3 py-3">ID Digabung</th>

                                        @foreach ($bulanLabels as $bln)
                                            <th colspan="4" class="border-b border-r border-slate-200 bg-slate-200/70 px-3 py-3">{{ $bln }}</th>
                                        @endforeach

                                        <th colspan="4" class="border-b border-r border-amber-200 bg-amber-100 px-3 py-3 text-amber-900">Sub Total</th>
                                    </tr>

                                    <tr class="bg-slate-100 text-center text-[11px] font-extrabold uppercase tracking-wide text-slate-600">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <th class="border-b border-r border-slate-200 px-3 py-2">Sales</th>
                                            <th class="border-b border-r border-slate-200 px-3 py-2">CU</th>
                                            <th class="border-b border-r border-slate-200 px-3 py-2">AVG</th>
                                            <th class="border-b border-r border-slate-200 px-3 py-2">AVG Size</th>
                                        @endfor

                                        <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">Sales</th>
                                        <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">CU</th>
                                        <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">AVG</th>
                                        <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">AVG Size</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($tableData as $index => $outlet)
                                        @php
                                            $searchText = strtolower(
                                                ($outlet['nama_outlet'] ?? '') . ' ' .
                                                ($outlet['area'] ?? '') . ' ' .
                                                ($outlet['kota'] ?? '') . ' ' .
                                                ($outlet['provinsi'] ?? '')
                                            );
                                        @endphp

                                        <tr
                                            class="group hover:bg-blue-50/60"
                                            x-show="!search || '{{ addslashes($searchText) }}'.includes(search.toLowerCase())"
                                        >
                                            <td class="sticky left-0 z-10 border-r border-slate-200 bg-white px-3 py-2 text-center font-bold text-slate-700 group-hover:bg-blue-50">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="sticky left-[54px] z-10 border-r border-slate-200 bg-white px-3 py-2 text-left font-extrabold text-slate-900 group-hover:bg-blue-50">
                                                <div class="flex flex-col gap-1">
                                                    <span>{{ $outlet['nama_outlet'] ?? '-' }}</span>
                                                    @if (($outlet['duplicate_count'] ?? 1) > 1)
                                                        <span class="w-fit rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-blue-700">
                                                            {{ $outlet['duplicate_count'] }} ID digabung
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="border-r border-slate-100 px-3 py-2 text-left font-semibold text-slate-700">{{ $outlet['area'] ?? '-' }}</td>
                                            <td class="border-r border-slate-100 px-3 py-2 text-left font-semibold text-slate-700">{{ $outlet['kota'] ?? '-' }}</td>
                                            <td class="border-r border-slate-100 px-3 py-2 text-left font-semibold text-slate-700">{{ $outlet['provinsi'] ?? '-' }}</td>
                                            <td class="border-r border-slate-100 px-3 py-2 text-center font-extrabold text-slate-700">{{ $outlet['duplicate_count'] ?? 1 }}</td>

                                            @for ($m = 1; $m <= 12; $m++)
                                                @php
                                                    $sales = $outlet['bulan'][$m]['sales'] ?? 0;
                                                    $cu = $outlet['bulan'][$m]['cu'] ?? 0;
                                                    $avg = $outlet['bulan'][$m]['ac'] ?? 0;
                                                    $avgSize = $outlet['bulan'][$m]['basket_size'] ?? 0;
                                                @endphp

                                                <td class="border-r border-slate-100 px-3 py-2 text-right font-bold tabular-nums">{{ number_format($sales) }}</td>
                                                <td class="border-r border-slate-100 px-3 py-2 text-center font-semibold tabular-nums">{{ $cu }}</td>
                                                <td class="border-r border-slate-100 px-3 py-2 text-right font-bold tabular-nums">{{ number_format($avg) }}</td>
                                                <td class="border-r border-slate-100 px-3 py-2 text-right font-bold tabular-nums">{{ number_format($avgSize, 2) }}</td>
                                            @endfor

                                            @php
                                                $subSales = $outlet['sub_total']['sales'] ?? 0;
                                                $subCu = $outlet['sub_total']['cu'] ?? 0;
                                                $subAvg = $outlet['sub_total']['ac'] ?? 0;
                                                $subAvgSize = $outlet['sub_total']['basket_size'] ?? 0;
                                            @endphp

                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-right font-extrabold tabular-nums">{{ number_format($subSales) }}</td>
                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-center font-extrabold tabular-nums">{{ $subCu }}</td>
                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-right font-extrabold tabular-nums">{{ number_format($subAvg) }}</td>
                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-right font-extrabold tabular-nums">{{ number_format($subAvgSize, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 6 + (12 * 4) + 4 }}" class="px-4 py-10 text-center">
                                                <div class="text-base font-extrabold text-slate-800">Data grand opening tidak tersedia</div>
                                                <div class="mt-1 text-sm font-medium text-slate-500">Tidak ada outlet grand opening pada filter ini.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#outlet').select2({
                width: '100%',
                placeholder: jQuery('#outlet').data('placeholder') || '-- Semua Outlet --',
                allowClear: true
            });
        }

        const btnExcel = document.getElementById('btnExcel');
        const progress = document.getElementById('exportProgress');

        if (btnExcel && progress) {
            btnExcel.addEventListener('click', (e) => {
                e.preventDefault();
                btnExcel.classList.add('pointer-events-none', 'opacity-60');
                progress.classList.remove('hidden');

                setTimeout(() => {
                    window.location.href = btnExcel.href;

                    setTimeout(() => {
                        btnExcel.classList.remove('pointer-events-none', 'opacity-60');
                        progress.classList.add('hidden');
                    }, 4000);
                }, 500);
            });
        }

        const btnPDF = document.getElementById('btnPDF');

        if (btnPDF) {
            btnPDF.addEventListener('click', () => {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sedang Maintenance',
                        text: 'Fitur export PDF untuk sementara sedang dalam perawatan. Silakan coba lagi nanti.',
                        confirmButtonColor: '#2563eb',
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('Fitur export PDF untuk sementara sedang dalam perawatan.');
                }
            });
        }
    });
</script>
@endpush

@include('Temp.Investor.footer')

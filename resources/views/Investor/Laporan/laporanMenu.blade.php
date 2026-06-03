{{-- resources/views/Investor/Laporan/laporanMenu.blade.php --}}
@section('title', 'Laporan Penjualan Menu')
@section('breadcrumb', 'Laporan / Penjualan Menu')

@include('Temp.Investor.header')

@php
    $tanggalMulai = $tanggalMulai ?? '';
    $tanggalAkhir = $tanggalAkhir ?? '';
    $selectedOutlet = $selectedOutlet ?? '';
    $selectedEcommerce = $selectedEcommerce ?? [];
    $ecommerceList = $ecommerceList ?? [];
    $outletList = $outletList ?? [];
    $menuColumns = $menuColumns ?? [];
    $allData = $data ?? [];
    $filterApplied = $filterApplied ?? (!empty($tanggalMulai) && !empty($tanggalAkhir));
    $grandTotal = $grandTotal ?? [
        'menu' => [],
        'qty' => 0,
        'total_harga' => 0,
        'ecommerce' => [],
    ];

    $dynamicColspan = 6 + (count($menuColumns) * 2) + 2;
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
                        Laporan / Penjualan Menu
                    </div>
                    <h1 class="mt-1 text-xl font-extrabold tracking-tight text-slate-950 sm:text-2xl">
                        Laporan Menu Per Outlet
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm font-medium text-slate-500">
                        Outlet duplikat digabung berdasarkan nama outlet. Quantity dan total harga dikalkulasi dari seluruh ID outlet duplikat.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:justify-end">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Tanggal Mulai</div>
                        <div class="mt-0.5 text-sm font-extrabold text-slate-900">{{ $tanggalMulai ?: 'Belum dipilih' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Tanggal Akhir</div>
                        <div class="mt-0.5 text-sm font-extrabold text-slate-900">{{ $tanggalAkhir ?: 'Belum dipilih' }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Menu Tampil</div>
                        <div class="mt-0.5 text-sm font-extrabold text-slate-900">{{ count($menuColumns) }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <div class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Outlet Tampil</div>
                        <div class="mt-0.5 text-sm font-extrabold text-slate-900">{{ count($allData) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 p-4">
                <div>
                    <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-800">Filter Laporan</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500">Atur periode dan outlet untuk menampilkan laporan menu.</p>
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
                action="{{ route('investor.laporan.menu') }}"
                id="formLaporanMenu"
                class="p-4"
            >
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-3">
                        <label for="tanggal_mulai" class="mb-2 block text-xs font-extrabold uppercase tracking-wide text-slate-600">
                            Tanggal Mulai
                        </label>
                        <input
                            type="date"
                            id="tanggal_mulai"
                            name="tanggal_mulai"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            value="{{ $tanggalMulai }}"
                        >
                    </div>

                    <div class="lg:col-span-3">
                        <label for="tanggal_akhir" class="mb-2 block text-xs font-extrabold uppercase tracking-wide text-slate-600">
                            Tanggal Akhir
                        </label>
                        <input
                            type="date"
                            id="tanggal_akhir"
                            name="tanggal_akhir"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            value="{{ $tanggalAkhir }}"
                        >
                    </div>

                    <div class="lg:col-span-3">
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

                    <div class="lg:col-span-3">
                        <label class="mb-2 block text-xs font-extrabold uppercase tracking-wide text-slate-600">
                            Aksi
                        </label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                            <button
                                type="submit"
                                class="inline-flex h-11 items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                                Filter
                            </button>

                            <a
                                href="{{ route('investor.laporan.menu') }}"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-extrabold text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                Reset
                            </a>

                            <a
                                href="{{ route('investor.laporan.menu.export', [
                                    'tanggal_mulai' => $tanggalMulai,
                                    'tanggal_akhir' => $tanggalAkhir,
                                    'outlet' => $selectedOutlet
                                ]) }}"
                                id="btnExcel"
                                class="inline-flex h-11 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-extrabold text-emerald-700 shadow-sm transition hover:bg-emerald-100"
                            >
                                Excel
                            </a>
                        </div>

                        <div id="exportProgress" class="mt-3 hidden h-1.5 overflow-hidden rounded-full bg-blue-100">
                            <div class="h-full w-1/2 animate-pulse rounded-full bg-blue-600"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-800">Tabel Laporan Menu</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500">
                        Geser tabel ke kanan untuk melihat semua menu.
                    </p>
                </div>

                <div class="relative w-full lg:w-80">
                    <input
                        type="text"
                        x-model.debounce.250ms="search"
                        placeholder="Cari outlet..."
                        class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm font-bold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                </div>
            </div>

            <div class="p-4">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <div class="flex flex-col gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-extrabold text-slate-900">
                                Total Baris: {{ count($allData) }}
                            </div>
                            <div class="text-xs font-medium text-slate-500">
                                Kolom "ID Digabung" menunjukkan jumlah outlet duplikat yang dikalkulasi dalam satu baris.
                            </div>
                        </div>
                        <div class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Menu: {{ count($menuColumns) }}
                        </div>
                    </div>

                    <div class="max-h-[72vh] overflow-auto">
                        <table id="laporanMenuTable" class="min-w-[1800px] border-separate border-spacing-0 text-sm">
                            <thead class="sticky top-0 z-20">
                                <tr class="bg-slate-100 text-center text-[11px] font-extrabold uppercase tracking-wide text-slate-600">
                                    <th rowspan="2" class="sticky left-0 z-30 border-b border-r border-slate-200 bg-slate-100 px-3 py-3">No</th>
                                    <th rowspan="2" class="sticky left-[54px] z-30 min-w-64 border-b border-r border-slate-200 bg-slate-100 px-3 py-3 text-left">Nama Outlet</th>
                                    <th rowspan="2" class="min-w-36 border-b border-r border-slate-200 px-3 py-3 text-left">Area</th>
                                    <th rowspan="2" class="min-w-32 border-b border-r border-slate-200 px-3 py-3 text-left">Kota</th>
                                    <th rowspan="2" class="min-w-40 border-b border-r border-slate-200 px-3 py-3 text-left">Provinsi</th>
                                    <th rowspan="2" class="min-w-24 border-b border-r border-slate-200 px-3 py-3">ID Digabung</th>

                                    @foreach ($menuColumns as $menu)
                                        <th colspan="2" class="min-w-48 border-b border-r border-slate-200 bg-slate-200/70 px-3 py-3">{{ $menu }}</th>
                                    @endforeach

                                    <th colspan="2" class="border-b border-r border-amber-200 bg-amber-100 px-3 py-3 text-amber-900">Sub Total</th>
                                </tr>

                                <tr class="bg-slate-100 text-center text-[11px] font-extrabold uppercase tracking-wide text-slate-600">
                                    @foreach ($menuColumns as $menu)
                                        <th class="border-b border-r border-slate-200 px-3 py-2">QTY</th>
                                        <th class="border-b border-r border-slate-200 px-3 py-2">Total Harga</th>
                                    @endforeach

                                    <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">QTY</th>
                                    <th class="border-b border-r border-amber-200 bg-amber-50 px-3 py-2">Total Harga</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                @if (!$filterApplied)
                                    <tr>
                                        <td colspan="{{ $dynamicColspan }}" class="px-4 py-10 text-center">
                                            <div class="text-base font-extrabold text-slate-800">Silakan filter data terlebih dahulu</div>
                                            <div class="mt-1 text-sm font-medium text-slate-500">Pilih tanggal mulai dan tanggal akhir, lalu klik Filter.</div>
                                        </td>
                                    </tr>
                                @elseif (count($allData) > 0)
                                    <tr class="bg-blue-600 text-white">
                                        <td class="sticky left-0 z-10 border-r border-blue-500 bg-blue-600 px-3 py-3 text-center font-extrabold">#</td>
                                        <td class="sticky left-[54px] z-10 border-r border-blue-500 bg-blue-600 px-3 py-3 text-left font-extrabold">GRAND TOTAL</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-left font-bold">-</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-left font-bold">-</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-left font-bold">-</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-center font-extrabold">-</td>

                                        @foreach ($menuColumns as $menu)
                                            <td class="border-r border-blue-500 px-3 py-3 text-center font-extrabold tabular-nums">{{ number_format($grandTotal['menu'][$menu]['qty'] ?? 0) }}</td>
                                            <td class="border-r border-blue-500 px-3 py-3 text-right font-extrabold tabular-nums">{{ number_format($grandTotal['menu'][$menu]['total_harga'] ?? 0) }}</td>
                                        @endforeach

                                        <td class="border-r border-blue-500 px-3 py-3 text-center font-extrabold tabular-nums">{{ number_format($grandTotal['qty'] ?? 0) }}</td>
                                        <td class="border-r border-blue-500 px-3 py-3 text-right font-extrabold tabular-nums">{{ number_format($grandTotal['total_harga'] ?? 0) }}</td>
                                    </tr>

                                    @foreach ($allData as $index => $outlet)
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

                                            @foreach ($menuColumns as $menu)
                                                @php
                                                    $qty = $outlet['menu'][$menu]['qty'] ?? 0;
                                                    $totalHarga = $outlet['menu'][$menu]['total_harga'] ?? 0;
                                                @endphp

                                                <td class="border-r border-slate-100 px-3 py-2 text-center font-semibold tabular-nums">{{ number_format($qty) }}</td>
                                                <td class="border-r border-slate-100 px-3 py-2 text-right font-bold tabular-nums">{{ number_format($totalHarga) }}</td>
                                            @endforeach

                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-center font-extrabold tabular-nums">{{ number_format($outlet['sub_total']['qty'] ?? 0) }}</td>
                                            <td class="border-r border-amber-100 bg-amber-50 px-3 py-2 text-right font-extrabold tabular-nums">{{ number_format($outlet['sub_total']['total_harga'] ?? 0) }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="{{ $dynamicColspan }}" class="px-4 py-10 text-center">
                                            <div class="text-base font-extrabold text-slate-800">Data tidak tersedia</div>
                                            <div class="mt-1 text-sm font-medium text-slate-500">Silakan ubah periode atau filter outlet.</div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
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

        const form = document.getElementById('formLaporanMenu');
        if (form) {
            form.addEventListener('submit', function (e) {
                const mulai = document.getElementById('tanggal_mulai')?.value || '';
                const akhir = document.getElementById('tanggal_akhir')?.value || '';

                if (!mulai || !akhir) {
                    e.preventDefault();

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Filter belum lengkap',
                            text: 'Tanggal mulai dan tanggal akhir wajib diisi.',
                            confirmButtonColor: '#2563eb'
                        });
                    } else {
                        alert('Tanggal mulai dan tanggal akhir wajib diisi.');
                    }

                    return false;
                }

                if (mulai > akhir) {
                    e.preventDefault();

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tanggal tidak valid',
                            text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
                            confirmButtonColor: '#2563eb'
                        });
                    } else {
                        alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
                    }

                    return false;
                }
            });
        }

        const btnExcel = document.getElementById('btnExcel');
        const progress = document.getElementById('exportProgress');

        if (btnExcel && progress) {
            btnExcel.addEventListener('click', (e) => {
                const mulai = document.getElementById('tanggal_mulai')?.value || '';
                const akhir = document.getElementById('tanggal_akhir')?.value || '';

                if (!mulai || !akhir) {
                    e.preventDefault();

                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Filter belum lengkap',
                            text: 'Tanggal mulai dan tanggal akhir wajib diisi sebelum export.',
                            confirmButtonColor: '#2563eb'
                        });
                    } else {
                        alert('Tanggal mulai dan tanggal akhir wajib diisi sebelum export.');
                    }

                    return false;
                }

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
    });
</script>
@endpush

@include('Temp.Investor.footer')

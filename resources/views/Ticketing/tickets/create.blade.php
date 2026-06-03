@extends('Ticketing.layouts.app')

@section('title', 'Buat Ticket')

@section('content')
@php
    $user = auth()->user();
    $role = strtolower($user->role ?? '');
    $isCrew = $role === 'crew';

    $uniqueOutlets = collect($outlets ?? [])
        ->filter(fn ($outlet) => !empty($outlet->nama_outlet))
        ->map(function ($outlet) {
            $outlet->unique_key = strtolower(trim($outlet->nama_outlet)) . '|' . strtolower(trim($outlet->kota ?? ''));
            return $outlet;
        })
        ->unique('unique_key')
        ->sortBy('nama_outlet')
        ->values();
@endphp

<div class="space-y-6">
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-blue-600">
                        Ticketing System
                    </p>

                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                        Buat Ticket Baru
                    </h1>

                    <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                        @if($isCrew)
                            Silakan isi laporan kendala outlet dengan lengkap agar tim terkait dapat menindaklanjuti lebih cepat.
                        @else
                            Buat ticket baru untuk kebutuhan operasional dan tindak lanjut divisi terkait.
                        @endif
                    </p>
                </div>

                @if($isCrew)
                    <span class="inline-flex w-fit items-center rounded-full border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700">
                        Akses Crew
                    </span>
                @endif
            </div>
        </div>
    </div>

    <form action="{{ route('ticketing.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        @csrf

        <div class="grid gap-0 lg:grid-cols-2">
            <section class="border-b border-slate-100 p-6 lg:border-b-0 lg:border-r">
                <div class="mb-5">
                    <h2 class="text-base font-bold text-slate-900">Informasi Outlet</h2>
                    <p class="mt-1 text-sm text-slate-500">Lengkapi lokasi dan kategori ticket.</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="outlet_id" class="ticket-label">
                            Nama Outlet <span class="text-red-500">*</span>
                        </label>

                        <select id="outlet_id"
                                name="outlet_id"
                                class="select2 ticket-input"
                                required>
                            <option value="">Pilih Outlet</option>

                            @foreach($uniqueOutlets as $outlet)
                                <option value="{{ $outlet->id }}"
                                        data-kota="{{ $outlet->kota ?? '' }}"
                                        @selected(old('outlet_id') == $outlet->id)>
                                    {{ $outlet->nama_outlet }}{{ !empty($outlet->kota) ? ' - '.$outlet->kota : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="province" class="ticket-label">
                            Provinsi <span class="text-red-500">*</span>
                        </label>

                        <select id="province" name="province" class="ticket-input" required>
                            <option value="">Pilih Provinsi</option>

                            @foreach($provinces as $province)
                                <option value="{{ $province }}" @selected(old('province') == $province)>
                                    {{ $province }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="city" class="ticket-label">
                            Kabupaten / Kota <span class="text-red-500">*</span>
                        </label>

                        <select id="city" name="city" class="ticket-input" required>
                            <option value="">Pilih Kabupaten / Kota</option>
                        </select>
                    </div>

                    <div>
                        <label for="area" class="ticket-label">
                            Area <span class="text-red-500">*</span>
                        </label>

                        <select id="area" name="area" class="ticket-input" required>
                            <option value="">Pilih Area</option>

                            @foreach($lookups['areas'] ?? [] as $v)
                                <option value="{{ $v }}" @selected(old('area') == $v)>
                                    {{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ticket_type" class="ticket-label">
                            Jenis Tiket <span class="text-red-500">*</span>
                        </label>

                        <select id="ticket_type" name="ticket_type" class="ticket-input" required>
                            <option value="">Pilih Jenis Tiket</option>

                            @foreach($lookups['types'] ?? [] as $v)
                                <option value="{{ $v }}" @selected(old('ticket_type') == $v)>
                                    {{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="division_select" class="ticket-label">
                            Divisi Tujuan
                        </label>

                        <select id="division_select"
                                class="ticket-input bg-slate-50 text-slate-600"
                                disabled>
                            <option value="">Otomatis sesuai jenis tiket</option>

                            @foreach($lookups['divisions'] ?? [] as $v)
                                <option value="{{ $v }}" @selected(old('division') == $v)>
                                    {{ $v }}
                                </option>
                            @endforeach
                        </select>

                        <input type="hidden" id="division_hidden" name="division" value="{{ old('division') }}">

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Pengadaan otomatis ke <strong>SCM</strong>, Perbaikan otomatis ke <strong>BND</strong>.
                        </p>
                    </div>
                </div>
            </section>

            <section class="p-6">
                <div class="mb-5">
                    <h2 class="text-base font-bold text-slate-900">Detail Laporan</h2>
                    <p class="mt-1 text-sm text-slate-500">Isi detail item, pelapor, dan lampiran foto.</p>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="item_select" class="ticket-label">
                            Item
                        </label>

                        <select id="item_select" name="item" class="ticket-input">
                            <option value="">Pilih Item</option>

                            @foreach($itemsWithOwner ?? [] as $it)
                                <option value="{{ $it->item }}"
                                        data-owner="{{ $it->owner ?? '' }}"
                                        @selected(old('item') == $it->item)>
                                    {{ $it->item }}
                                </option>
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Item akan otomatis difilter sesuai jenis tiket.
                        </p>
                    </div>

                    <div>
                        <label for="custom_item" class="ticket-label">
                            Permintaan Item Barang
                        </label>

                        <input id="custom_item"
                               type="text"
                               name="custom_item"
                               value="{{ old('custom_item') }}"
                               placeholder="Isi jika item tidak tersedia"
                               class="ticket-input">
                    </div>

                    <div>
                        <label for="leader_name" class="ticket-label">
                            Nama Leader / Pelapor
                        </label>

                        <input id="leader_name"
                               type="text"
                               name="leader_name"
                               value="{{ old('leader_name', $isCrew ? ($user->name ?? '') : '') }}"
                               class="ticket-input">
                    </div>

                    <div>
                        <label for="reporter_phone" class="ticket-label">
                            Nomor HP Pelapor / No HP Outlet
                        </label>

                        <input id="reporter_phone"
                               type="text"
                               name="reporter_phone"
                               value="{{ old('reporter_phone') }}"
                               placeholder="Contoh: 08123456789"
                               class="ticket-input">
                    </div>

                    <div>
                        <label class="ticket-label">
                            Upload Foto
                        </label>

                        <div class="mt-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">

                            <div class="grid gap-3 sm:grid-cols-3">

                                <button type="button"
                                        onclick="openGallery()"
                                        class="photo-action bg-blue-600 text-white hover:bg-blue-700">
                                    <span class="text-base">📁</span>
                                    Gallery
                                </button>

                                <button type="button"
                                        onclick="openCameraBack()"
                                        class="photo-action bg-emerald-600 text-white hover:bg-emerald-700">
                                    <span class="text-base">📷</span>
                                    Kamera Belakang
                                </button>

                                <button type="button"
                                        onclick="openCameraFront()"
                                        class="photo-action bg-slate-800 text-white hover:bg-slate-900">
                                    <span class="text-base">🤳</span>
                                    Kamera Depan
                                </button>

                            </div>

                            <p class="mt-3 text-xs leading-5 text-slate-500">
                                Bisa tambah foto berkali-kali dari gallery maupun kamera. Semua foto yang muncul di preview akan ikut tersimpan.
                            </p>

                            <input id="galleryInput"
                                   type="file"
                                   accept="image/*"
                                   multiple
                                   hidden>

                            <input id="cameraInput"
                                   type="file"
                                   accept="image/*"
                                   capture="environment"
                                   hidden>

                            <input id="realPhotosInput"
                                   type="file"
                                   name="photos[]"
                                   multiple
                                   hidden>

                            <div id="photoCounter"
                                 class="mt-4 hidden rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700">
                                0 foto dipilih
                            </div>

                            <div id="photoPreview"
                                 class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
                            </div>
                        </div>

                        @error('photos')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @error('photos.*')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>
        </div>

        <section class="border-t border-slate-100 bg-slate-50/60 p-6">
            <div class="grid gap-5 lg:grid-cols-2">
                <div>
                    <label for="description" class="ticket-label">
                        Deskripsi <span class="text-red-500">*</span>
                    </label>

                    <textarea id="description"
                              name="description"
                              rows="5"
                              class="ticket-input resize-none"
                              required>{{ old('description') }}</textarea>
                </div>

                <div>
                    <label for="additional_notes" class="ticket-label">
                        Catatan & Informasi Tambahan
                    </label>

                    <textarea id="additional_notes"
                              name="additional_notes"
                              rows="5"
                              class="ticket-input resize-none">{{ old('additional_notes') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                @if($isCrew)
                    <a href="{{ url('/crew-menus') }}" class="btn-secondary">
                        Kembali
                    </a>
                @else
                    <a href="{{ route('ticketing.index') }}" class="btn-secondary">
                        Batal
                    </a>
                @endif

                <button type="submit" class="btn-primary">
                    Simpan Ticket
                </button>
            </div>
        </section>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .ticket-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 0.375rem;
    }

    .ticket-input {
        width: 100%;
        border-radius: 0.875rem;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        color: #0f172a;
        outline: none;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }

    .ticket-input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .photo-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border-radius: 0.875rem;
        padding: 0.625rem 1rem;
        font-size: 0.875rem;
        font-weight: 700;
        transition: background-color 150ms ease, transform 150ms ease, box-shadow 150ms ease;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .photo-action:hover {
        transform: translateY(-1px);
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.875rem;
        background-color: #2563eb;
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 800;
        color: #ffffff;
        transition: background-color 150ms ease, transform 150ms ease;
    }

    .btn-primary:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
    }

    .btn-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.875rem;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 800;
        color: #475569;
        transition: background-color 150ms ease, transform 150ms ease;
    }

    .btn-secondary:hover {
        background-color: #f8fafc;
        transform: translateY(-1px);
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container .select2-selection--single {
        height: 44px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 0.875rem !important;
        display: flex !important;
        align-items: center !important;
        background-color: #fff !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a !important;
        line-height: 44px !important;
        padding-left: 0.875rem !important;
        font-size: 0.875rem !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px !important;
        right: 0.625rem !important;
    }

    .select2-dropdown {
        border-color: #cbd5e1 !important;
        border-radius: 0.875rem !important;
        overflow: hidden !important;
    }

    .select2-search__field {
        border-radius: 0.625rem !important;
        border-color: #cbd5e1 !important;
        outline: none !important;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(function () {
        const outletSelect = $('#outlet_id');

        outletSelect.select2({
            width: '100%',
            placeholder: 'Pilih Outlet',
            allowClear: true
        });

        const citiesByProvince = {
            'Jawa Timur': ['Surabaya', 'Sidoarjo', 'Malang', 'Kediri'],
            'Jawa Tengah': ['Semarang', 'Solo', 'Kudus', 'Pekalongan'],
            'Jawa Barat': ['Bandung', 'Bekasi', 'Depok', 'Bogor'],
            'DKI Jakarta': ['Jakarta Pusat', 'Jakarta Selatan', 'Jakarta Barat', 'Jakarta Timur'],
            'Daerah Istimewa Yogyakarta': ['Yogyakarta', 'Sleman', 'Bantul'],
            'Banten': ['Serang', 'Tangerang', 'Cilegon'],
            'Lampung': ['Bandar Lampung', 'Metro']
        };

        function populateCities(province, selected = '') {
            const citySelect = $('#city');

            citySelect.empty().append(
                $('<option>').attr('value', '').text('Pilih Kabupaten / Kota')
            );

            const list = citiesByProvince[province] || [];

            list.forEach(function (city) {
                citySelect.append(
                    $('<option>').attr('value', city).text(city)
                );
            });

            if (selected) {
                if (citySelect.find('option[value="' + selected + '"]').length === 0) {
                    citySelect.append(
                        $('<option>').attr('value', selected).text(selected)
                    );
                }

                citySelect.val(selected);
            }
        }

        $('#province').on('change', function () {
            populateCities($(this).val());
        });

        function setKotaFromSelectedOutlet() {
            const kota = outletSelect.find(':selected').data('kota') || '';

            if (!kota) {
                return;
            }

            if ($('#city option[value="' + kota + '"]').length === 0) {
                $('#city').append(
                    $('<option>').attr('value', kota).text(kota)
                );
            }

            $('#city').val(kota);
        }

        outletSelect.on('change', setKotaFromSelectedOutlet);

        function autoDivisionByType() {
            const typeVal = ($('#ticket_type').val() || '').toLowerCase();
            const divisionSelect = $('#division_select');
            const divisionHidden = $('#division_hidden');

            let division = '';

            if (typeVal.includes('pengadaan')) {
                division = 'SCM';
            } else if (typeVal.includes('perbaikan')) {
                division = 'BND';
            }

            divisionSelect.val(division);
            divisionHidden.val(division);
        }

        function filterItemsByType() {
            const typeVal = ($('#ticket_type').val() || '').toLowerCase();

            let ownerNeeded = null;

            if (typeVal.includes('pengadaan')) {
                ownerNeeded = 'SCM';
            } else if (typeVal.includes('perbaikan')) {
                ownerNeeded = 'BND';
            }

            $('#item_select option').each(function () {
                const value = $(this).val();
                const owner = ($(this).data('owner') || '').toString().toLowerCase();

                if (value === '') {
                    $(this).show();
                    return;
                }

                if (!ownerNeeded) {
                    $(this).show();
                    return;
                }

                $(this).toggle(owner === ownerNeeded.toLowerCase());
            });

            const selected = $('#item_select option:selected');

            if (selected.is(':hidden')) {
                $('#item_select').val('');
            }
        }

        $('#ticket_type').on('change', function () {
            autoDivisionByType();
            filterItemsByType();
        });

        const initialProvince = $('#province').val();
        const initialCity = @json(old('city'));

        if (initialProvince) {
            populateCities(initialProvince, initialCity);
        }

        if (outletSelect.val()) {
            setKotaFromSelectedOutlet();
        }

        autoDivisionByType();
        filterItemsByType();
    });

    let selectedPhotos = [];

    const galleryInput = document.getElementById('galleryInput');
    const cameraInput = document.getElementById('cameraInput');
    const realPhotosInput = document.getElementById('realPhotosInput');
    const previewBox = document.getElementById('photoPreview');
    const photoCounter = document.getElementById('photoCounter');

    function openGallery() {
        galleryInput.click();
    }

    function openCameraBack() {
        cameraInput.setAttribute('capture', 'environment');
        cameraInput.click();
    }

    function openCameraFront() {
        cameraInput.setAttribute('capture', 'user');
        cameraInput.click();
    }

    function addFiles(files) {
        Array.from(files).forEach(function (file) {
            if (!file.type || !file.type.startsWith('image/')) {
                return;
            }

            selectedPhotos.push(file);
        });

        syncFiles();
        renderPreview();
    }

    function syncFiles() {
        const dataTransfer = new DataTransfer();

        selectedPhotos.forEach(function (file) {
            dataTransfer.items.add(file);
        });

        realPhotosInput.files = dataTransfer.files;
    }

    function renderPreview() {
        previewBox.innerHTML = '';

        if (selectedPhotos.length > 0) {
            photoCounter.classList.remove('hidden');
            photoCounter.innerText = selectedPhotos.length + ' foto dipilih dan akan disimpan';
        } else {
            photoCounter.classList.add('hidden');
            photoCounter.innerText = '0 foto dipilih';
        }

        selectedPhotos.forEach(function (file, index) {
            const url = URL.createObjectURL(file);
            const item = document.createElement('div');

            item.className = 'group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm';

            item.innerHTML = `
                <img src="${url}" class="h-40 w-full object-cover" alt="Preview foto">

                <button type="button"
                        onclick="removePhoto(${index})"
                        class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-red-600 text-base font-bold text-white shadow-sm hover:bg-red-700">
                    ×
                </button>

                <div class="truncate border-t border-slate-100 px-3 py-2 text-xs font-medium text-slate-500">
                    ${escapeHtml(file.name)}
                </div>
            `;

            previewBox.appendChild(item);
        });
    }

    function removePhoto(index) {
        selectedPhotos.splice(index, 1);
        syncFiles();
        renderPreview();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }

    galleryInput.addEventListener('change', function () {
        addFiles(this.files);
        this.value = '';
    });

    cameraInput.addEventListener('change', function () {
        addFiles(this.files);
        this.value = '';
    });

    document.querySelector('form[action="{{ route('ticketing.store') }}"]').addEventListener('submit', function () {
        syncFiles();
    });
</script>
@endpush

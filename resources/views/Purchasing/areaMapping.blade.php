@include('Temp.Investor.header')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* =========================================
       ✨ ELEGANT & CALM KANBAN THEME ✨
       ========================================= */
    body { 
        background-color: #f8fafc; /* Soft Slate 50 Background */
        font-family: 'Inter', -apple-system, sans-serif;
    }

    /* Header & Filter Customization */
    .elegant-header-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 30px -10px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
    }
    
    .elegant-select {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #334155;
        border-radius: 12px;
        padding: 0.6rem 2rem 0.6rem 1.2rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .elegant-select:focus, .elegant-select:hover {
        background-color: #ffffff;
        border-color: #818cf8;
        box-shadow: 0 0 0 4px rgba(129, 140, 248, 0.1);
    }

    /* Day Column Header (Calm & Editorial look) */
    .day-header {
        text-align: center;
        padding: 0.5rem 0;
        margin-bottom: 1rem;
        color: #64748b;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2.5px;
    }

    /* Route Card (Smooth, Rounded, Soft Shadow) */
    .route-card {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.02), 0 8px 10px -6px rgba(0,0,0,0.01);
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    
    .route-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.04), 0 10px 10px -5px rgba(0,0,0,0.02);
    }

    /* Area Title Banner (Soft Pastel Background) */
    .area-banner {
        background: #f8fafc;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .area-title-text {
        color: #334155;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    /* Outlet List Items (Clean, Borderless look) */
    .outlet-list {
        padding: 0.5rem 0;
    }
    
    .outlet-list-item {
        font-size: 0.8rem;
        padding: 0.6rem 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #475569;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .outlet-list-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background-color: #818cf8;
        border-radius: 0 4px 4px 0;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .outlet-list-item:hover {
        background-color: #f8fafc;
        color: #1e293b;
    }
    
    .outlet-list-item:hover::before {
        opacity: 1;
    }

    /* Subtle Remove Button */
    .btn-remove-outlet {
        color: #cbd5e1;
        background: none;
        border: none;
        padding: 4px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .outlet-list-item:hover .btn-remove-outlet {
        color: #f87171;
        background: #fef2f2;
    }

    /* Minimalist Add Button */
    .btn-add-outlet {
        width: 100%;
        border: none;
        background: transparent;
        color: #6366f1;
        font-weight: 600;
        padding: 0.85rem;
        font-size: 0.8rem;
        border-top: 1px dashed #e2e8f0;
        transition: all 0.2s ease;
    }
    
    .btn-add-outlet:hover {
        background: #eef2ff;
        color: #4f46e5;
    }

    /* Action Button Customization */
    .btn-elegant-primary {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        color: white;
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-elegant-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        color: white;
    }

    /* Select2 UI fixes for Modal */
    .select2-container--default .select2-selection--multiple {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        min-height: 42px;
        padding: 4px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #eef2ff;
        border: 1px solid #c7d2fe;
        color: #4f46e5;
        border-radius: 6px;
        padding: 4px 8px;
        font-size: 0.8rem;
    }
    =========================================
       KANBAN LANE BORDERS (BARU)
       ========================================= */
    .kanban-lane {
        background-color: #f1f5f9; /* Warna abu-abu kebiruan sangat lembut */
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 1.25rem 1rem;
        height: 100%;
    }

    /* Day Column Header (Dipertegas) */
    .day-header {
        text-align: center;
        padding-bottom: 0.85rem;
        margin-bottom: 1.25rem;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        border-bottom: 2px dashed #cbd5e1; /* Garis putus-putus pembatas hari */
    }

    /* Minimalist Add Button (Perbaikan Ikon) */
    .btn-add-outlet {
        width: 100%;
        border: none;
        background: transparent;
        color: #6366f1;
        font-weight: 600;
        padding: 0.85rem;
        font-size: 0.8rem;
        border-top: 1px dashed #e2e8f0;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px; /* Memberi jarak aman antara ikon dan teks */
    }
    
    .btn-add-outlet:hover {
        background: #eef2ff;
        color: #4f46e5;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            {{-- HEADER FILTER DC --}}
            <div class="elegant-header-card mb-5">
                <div class="card-body p-4 p-md-5 text-center">
                    <h4 class="fw-bold mb-2" style="color: #1e293b; letter-spacing: -0.5px;">Mapping Rute Logistik</h4>
                    <p class="text-muted mb-4 small">Atur jadwal pengiriman dan area distribusi outlet dengan mudah.</p>

                    <form action="" method="GET" class="d-flex justify-content-center align-items-center gap-3">
                        <div class="d-inline-flex align-items-center bg-white p-2 rounded-4 shadow-sm" style="border: 1px solid #f1f5f9;">
                            <div class="icon-box bg-light rounded-circle p-2 mx-2 text-primary">
                                <i class="bi bi-buildings"></i>
                            </div>
                            <select name="dc_id" onchange="this.form.submit()" class="form-select elegant-select border-0 shadow-none me-1" style="min-width: 220px; cursor: pointer;">
                                @foreach($dcs as $dc)
                                    <option value="{{ $dc->id }}" {{ $dcId == $dc->id ? 'selected' : '' }}>
                                        {{ mb_strtoupper($dc->nama) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KANBAN BOARD JADWAL DINAMIS --}}
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 row-cols-xxl-4 g-4">
                @php
                    $urutanHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                @endphp

                @foreach($urutanHari as $hari)
                    @if(isset($kanbanData[$hari]))
                        <div class="col">
                            {{-- BUNGKUSAN KANBAN LANE BARU --}}
                            <div class="kanban-lane">
                                
                                {{-- Judul Hari yang lebih terstruktur --}}
                                <div class="day-header">
                                    {{ $hari }}
                                </div>

                                {{-- Looping Rute per Hari --}}
                                @foreach($kanbanData[$hari] as $routeId => $route)
                                    <div class="route-card">
                                        {{-- Banner Area --}}
                                        <div class="area-banner">
                                            <span class="area-title-text">{{ mb_strtoupper($route['nama_area']) }}</span>
                                            <div class="d-flex gap-2">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background: #eef2ff; color: #6366f1;" title="Area Pengiriman">
                                                    <i class="bi bi-truck small"></i>
                                                </div>
                                                {{-- Tombol Hapus Rute --}}
                                                <button type="button" class="btn btn-sm btn-delete-route rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                                        style="width: 28px; height: 28px; padding: 0; background: #fef2f2; border: 1px solid #fee2e2; color: #f87171;" 
                                                        data-route-id="{{ $routeId }}" title="Hapus Blok Rute Ini">
                                                    <i class="bi bi-trash3 small"></i>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- List Outlet --}}
                                        <div class="outlet-list">
                                            @forelse($route['outlets'] ?? [] as $index => $outlet)
                                                <div class="outlet-list-item">
                                                    <span class="text-truncate pe-2">
                                                        <strong style="color: #94a3b8; font-size: 0.75rem;" class="me-2">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}.</strong>
                                                        {{ $outlet['nama'] }}
                                                    </span>
                                                    <button type="button" class="btn-remove-outlet flex-shrink-0" data-map-id="{{ $outlet['map_id'] }}" title="Keluarkan dari rute">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                            @empty
                                                <div class="p-4 text-center">
                                                    <i class="bi bi-inbox text-muted fs-3 opacity-50 mb-2 d-block"></i>
                                                    <span class="text-muted small">Belum ada outlet</span>
                                                </div>
                                            @endforelse
                                        </div>

                                        {{-- Tombol Tambah Outlet (Ikon diganti ke bi-plus-circle) --}}
                                        <button class="btn-add-outlet" data-bs-toggle="modal" data-bs-target="#modalAddOutlet" 
                                                data-route-id="{{ $routeId }}" 
                                                data-route-nama="{{ $route['nama_area'] }}" 
                                                data-hari="{{ mb_strtoupper($hari) }}">
                                            <i class="bi bi-plus-circle-fill"></i> Tambah Outlet
                                        </button>
                                    </div>
                                @endforeach
                            </div> {{-- End of Kanban Lane --}}
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- TOMBOL BUAT AREA BARU --}}
            <div class="text-center mt-5 mb-5">
                <button class="btn btn-elegant-primary" data-bs-toggle="modal" data-bs-target="#modalAddRoute">
                    <i class="bi bi-signpost-split me-2"></i> Buat Blok Rute Baru
                </button>
            </div>

            {{-- ========================================== --}}
            {{-- MODAL 1: BUAT RUTE / AREA BARU             --}}
            {{-- ========================================== --}}
            <div class="modal fade" id="modalAddRoute" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 20px; border: none;">
                        <form action="{{ route('scm.area_mapping.store_route') }}" method="POST">
                            @csrf
                            <input type="hidden" name="dc_id" value="{{ $dcId }}">

                            <div class="modal-header border-bottom-0 p-4 pb-2">
                                <h5 class="modal-title fw-bold" style="color: #1e293b;">Rute Baru</h5>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-4 pt-2">
                                <div class="mb-4">
                                    <label class="fw-semibold mb-2" style="font-size: 0.85rem; color: #475569;">Jadwal Pengiriman</label>
                                    <select name="hari_kirim" class="form-select elegant-select w-100" required>
                                        <option value="">-- Tentukan Hari --</option>
                                        <option value="Senin">Senin</option>
                                        <option value="Selasa">Selasa</option>
                                        <option value="Rabu">Rabu</option>
                                        <option value="Kamis">Kamis</option>
                                        <option value="Jumat">Jumat</option>
                                        <option value="Sabtu">Sabtu</option>
                                        <option value="Minggu">Minggu</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="fw-semibold mb-2" style="font-size: 0.85rem; color: #475569;">Nama Wilayah / Rute</label>
                                    <input type="text" name="nama_area" class="form-control" style="border-radius: 12px; padding: 0.75rem 1rem; text-transform: uppercase;" placeholder="Cth: MATARAMAN" required>
                                </div>
                            </div>

                            <div class="modal-footer border-top-0 p-4 pt-0">
                                <button type="submit" class="btn btn-elegant-primary w-100 rounded-3">
                                    Simpan Rute
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- MODAL 2: TAMBAH OUTLET KE RUTE TERPILIH    --}}
            {{-- ========================================== --}}
            <div class="modal fade" id="modalAddOutlet" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 20px; border: none;">
                        <form id="formAddMapping">
                            @csrf
                            <input type="hidden" name="route_id" id="inputRouteId">
                            
                            <div class="modal-header border-bottom-0 p-4 pb-2">
                                <h5 class="modal-title fw-bold" style="color: #1e293b;">Pilih Outlet Target</h5>
                                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                            </div>
                            
                            <div class="modal-body p-4 pt-2">
                                <div class="p-3 mb-4 rounded-3" style="background: #eef2ff; border: 1px dashed #c7d2fe;">
                                    <div class="small text-muted mb-1" style="font-size: 0.7rem; text-transform: uppercase;">Ditambahkan ke rute:</div>
                                    <strong id="modalRouteInfo" style="color: #4f46e5; font-size: 0.9rem;">Memuat...</strong>
                                </div>
                                
                                <label class="fw-semibold mb-2" style="font-size: 0.85rem; color: #475569;">Daftar Outlet</label>
                                <select name="outlet_ids[]" class="form-control select2-multiple w-100" multiple="multiple" required>
                                    @foreach($allOutlets as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_outlet }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="modal-footer border-top-0 p-4 pt-0">
                                <button type="submit" class="btn btn-elegant-primary w-100 rounded-3">
                                    Simpan ke Rute Ini
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

@push('scripts')
<script>
    $(document).ready(function () {
        // Init Select2
        $('.select2-multiple').select2({
            dropdownParent: $('#modalAddOutlet'),
            placeholder: "Cari dan pilih outlet...",
            allowClear: true
        });

        // Lempar data Rute ke Modal
        $(document).on('click', '.btn-add-outlet', function () {
            let routeId = $(this).data('route-id');
            let routeNama = $(this).data('route-nama');
            let hari = $(this).data('hari');

            $('#inputRouteId').val(routeId);
            $('#modalRouteInfo').html(`${hari} &nbsp;&rarr;&nbsp; ${routeNama}`);
            $('.select2-multiple').val(null).trigger('change');
        });

        // Hapus Outlet
        $(document).on('click', '.btn-remove-outlet', function () {
            let mapId = $(this).data('map-id');
            let row = $(this).closest('.outlet-list-item');

            Swal.fire({
                title: 'Hapus dari Rute?',
                text: "Outlet akan dikeluarkan dari jadwal ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f87171',
                cancelButtonColor: '#e2e8f0',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: '<span class="text-dark">Batal</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('scm.area_mapping.remove') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            map_id: mapId
                        },
                        success: function (res) {
                            row.slideUp(300, function () { $(this).remove(); });
                        },
                        error: function (xhr) {
                            Swal.fire('Error!', 'Gagal menghapus data.', 'error');
                        }
                    });
                }
            });
        });

        // Submit Tambah Outlet
        $('#formAddMapping').on('submit', function (e) {
            e.preventDefault();
            let btn = $(this).find('button[type="submit"]');
            let originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

            $.ajax({
                url: "{{ route('scm.area_mapping.add') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function (res) {
                    $('#modalAddOutlet').modal('hide');
                    Swal.fire({
                        title: 'Tersimpan!',
                        text: res.message,
                        icon: 'success',
                        confirmButtonColor: '#4f46e5'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function (xhr) {
                    btn.prop('disabled', false).html(originalText);
                    Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                }
            });
        });
    });

    // AJAX Hapus Blok Rute Secara Keseluruhan
        $(document).on('click', '.btn-delete-route', function () {
            let routeId = $(this).data('route-id');
            
            Swal.fire({
                title: 'Hapus Blok Rute?',
                text: "Semua outlet yang ada di jadwal rute ini akan ikut terhapus dari daftar!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f87171',
                cancelButtonColor: '#e2e8f0',
                confirmButtonText: 'Ya, Hapus Rute',
                cancelButtonText: '<span class="text-dark">Batal</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('scm.area_mapping.delete_route') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            route_id: routeId
                        },
                        success: function (res) {
                            Swal.fire({
                                title: 'Terhapus!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonColor: '#4f46e5'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function (xhr) {
                            Swal.fire('Error!', 'Gagal menghapus rute sistem.', 'error');
                        }
                    });
                }
            });
        });
</script>
@endpush

@include('Temp.Investor.footer')
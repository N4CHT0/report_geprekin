{{-- resources/views/purchasing/viewProduct.blade.php --}}
@include('Temp.Investor.header')

<style>
    /* Styling label informasi */
    .form-label-custom {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        letter-spacing: 0.5px;
    }

    /* Tampilan value teks read-only agar clean */
    .view-value-text {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2c3e50;
        padding: 6px 0;
    }

    /* Tabel konversi unit khusus read-only */
    #tableUnit {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    #tableUnit thead th {
        background-color: #f8f9fa !important;
        border: 1px solid #eef1f6 !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #2c3e50;
        padding: 12px 10px !important;
    }

    #tableUnit tbody td {
        padding: 14px 10px !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f4f6f9 !important;
    }

    #tableUnit tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.02) !important;
    }
</style>

<main class="app-main">
    <div class="app-content">
        <div class="container-fluid py-4">

            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex flex-wrap align-items-center">
                        <div class="me-auto">
                            <h5 class="fw-bold mb-0" style="color: #2c3e50; font-size: 1.1rem;">Detail Master Data Produk SCM</h5>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb bg-transparent p-0 mb-0" style="font-size: 11px;">
                                    <li class="breadcrumb-item"><a href="/dashboard-scm" class="text-decoration-none text-muted">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('scm.index-bahan') }}" class="text-decoration-none text-muted">Product List</a></li>
                                    <li class="breadcrumb-item active" style="color: #696cff;" aria-current="page">Product Profile</li>
                                </ol>
                            </nav>
                        </div>
                        <div>
                            <a href="{{ route('scm.index-bahan') }}" class="btn btn-sm btn-light border px-3 d-inline-flex align-items-center gap-1 text-secondary" style="height: 32px; font-weight: 500;">
                                <i class="bi bi-arrow-left-short fs-5"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4" style="border-radius: 15px;">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label form-label-custom mb-1">Product Name</label>
                            <div class="view-value-text d-flex align-items-center gap-2">
                                <div class="icon-shape bg-light text-primary" style="width: 30px; height: 30px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; color: #696cff !important;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <span>{{ $product->nama_bahan }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label class="form-label form-label-custom mb-1">Sumber Barang</label>
                            <div class="view-value-text">
                                @if($product->sumber_barang == 'GUDANG')
                                    <span class="badge bg-label-primary px-3 py-1.5 fw-bold" style="background-color: #e7e7ff; color: #696cff; border-radius: 6px; font-size: 11px;">GUDANG</span>
                                @else
                                    <span class="badge bg-label-info px-3 py-1.5 fw-bold" style="background-color: #d7f5fc; color: #03c3ec; border-radius: 6px; font-size: 11px;">SUPPLIER</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 my-4">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="p-2 bg-light rounded text-primary" style="color: #696cff !important;">
                            <i class="bi bi-layers fs-5"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-0">Product Detail (Unit Conversion)</h6>
                    </div>

                    <div class="table-responsive" style="border-radius: 8px; overflow: hidden; border: 1px solid #eef1f6;">
                        <table class="table align-middle mb-0" id="tableUnit">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th class="text-center" style="width: 140px;">Conv. Factor</th>
                                    <th>Base Unit</th>
                                    <th class="text-end" style="width: 180px;">Base Price (IDR)</th>
                                    <th class="text-end" style="width: 150px;">Weight (Gram)</th>
                                    <th style="background-color: #f1f3f7 !important; width: 70px;" class="text-center">Stock</th>
                                    <th style="background-color: #f1f3f7 !important; width: 70px;" class="text-center">Purchase</th>
                                    <th style="background-color: #f1f3f7 !important; width: 70px;" class="text-center">Base</th>
                                    <th style="background-color: #f1f3f7 !important; width: 70px;" class="text-center">Transfer</th>
                                    <th style="background-color: #f1f3f7 !important; width: 70px;" class="text-center">Sales</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark">
                                @foreach($product->details as $detail)
                                <tr>
                                    <td class="fw-semibold text-dark small">
                                        {{ $detail->nama_unit }}
                                    </td>
                                    <td class="text-center fw-medium text-secondary small">
                                        {{ number_format($detail->conversion_factor) }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $product->nama_unit ?? '-' }}
                                    </td>
                                    <td class="text-end fw-bold text-dark small">
                                        IDR {{ number_format($detail->base_price, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end text-secondary small fw-medium">
                                        {{ number_format($detail->weight) }} g
                                    </td>

                                    <td class="text-center">
                                        @if($detail->is_stock_unit)
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        @else
                                            <i class="bi bi-x-circle text-light fs-5" style="color: #d9dee3 !important;"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($detail->is_purchase_unit)
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        @else
                                            <i class="bi bi-x-circle text-light fs-5" style="color: #d9dee3 !important;"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($detail->is_base_unit)
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        @else
                                            <i class="bi bi-x-circle text-light fs-5" style="color: #d9dee3 !important;"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($detail->is_transfer_unit)
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        @else
                                            <i class="bi bi-x-circle text-light fs-5" style="color: #d9dee3 !important;"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($detail->is_sales_unit)
                                            <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                        @else
                                            <i class="bi bi-x-circle text-light fs-5" style="color: #d9dee3 !important;"></i>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</main>

@include('Temp.Investor.footer')
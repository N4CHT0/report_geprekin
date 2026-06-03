{{-- resources/views/Investor/summary_detail_transaksi.blade.php --}}
@section('title', 'Generate Summary Detail Transaksi')
@section('breadcrumb', 'Master Data / Summary Transaksi')

@include('Temp.Investor.header')

<style>
    .summary-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .summary-hero {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        padding: 22px;
        border: 1px solid rgba(37, 99, 235, .16);
        border-radius: 18px;
        background:
            radial-gradient(circle at top right, rgba(37, 99, 235, .16), transparent 34%),
            linear-gradient(135deg, #ffffff 0%, #f8fbff 45%, #eef6ff 100%);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
    }

    .summary-hero::after {
        content: "";
        position: absolute;
        right: -80px;
        bottom: -130px;
        width: 260px;
        height: 260px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .11);
        filter: blur(8px);
    }

    .summary-hero > * {
        position: relative;
        z-index: 1;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 5px 10px;
        margin-bottom: 8px;
        border-radius: 999px;
        background: rgba(37, 99, 235, .10);
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .hero-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.15;
        font-weight: 950;
        letter-spacing: -.04em;
        color: #0f172a;
    }

    .hero-subtitle {
        margin-top: 6px;
        color: #64748b;
        font-size: 13.5px;
        font-weight: 650;
    }

    .summary-card {
        overflow: hidden;
        border: 1px solid rgba(15, 23, 42, .09);
        border-radius: 18px;
        background: rgba(255, 255, 255, .96);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .055);
    }

    .summary-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15, 23, 42, .075);
        background: linear-gradient(90deg, #ffffff, #fafcff);
    }

    .summary-card-title {
        margin: 0;
        font-size: 15px;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -.02em;
    }

    .summary-card-subtitle {
        margin-top: 3px;
        color: #64748b;
        font-size: 12.5px;
        font-weight: 650;
    }

    .summary-card-body {
        padding: 18px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 14px;
        align-items: end;
    }

    .field-label {
        display: block;
        margin-bottom: 7px;
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .field-control {
        width: 100%;
        height: 44px;
        border: 1px solid rgba(100, 116, 139, .30);
        border-radius: 12px;
        background: #fff;
        color: #0f172a;
        padding: 0 13px;
        font-size: 13.5px;
        font-weight: 650;
        outline: none;
        transition: .18s ease;
    }

    .field-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        transform: translateY(-1px);
    }

    .btn-process {
        height: 44px;
        min-width: 190px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        font-size: 13.5px;
        font-weight: 900;
        box-shadow: 0 12px 24px rgba(37, 99, 235, .22);
        transition: .18s ease;
    }

    .btn-process:hover {
        transform: translateY(-1px);
        box-shadow: 0 16px 32px rgba(37, 99, 235, .28);
    }

    .summary-alert {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        border-radius: 14px;
        padding: 13px 14px;
        margin-bottom: 14px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .summary-alert.success {
        background: #ecfdf5;
        border-color: #bbf7d0;
        color: #047857;
    }

    .summary-alert.danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #b91c1c;
    }

    .impact-box {
        border-top: 1px solid rgba(15, 23, 42, .075);
        background: linear-gradient(135deg, #f8fafc, #ffffff);
        padding: 18px;
    }

    .impact-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: #0f172a;
        font-size: 14px;
        font-weight: 900;
    }

    .table-list {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .table-list li {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 13px;
        background: #fff;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        box-shadow: 0 8px 20px rgba(15, 23, 42, .035);
    }

    .table-list li i {
        color: #2563eb;
    }

    .warning-note {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-top: 14px;
        padding: 13px 14px;
        border-radius: 14px;
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #92400e;
        font-size: 13px;
        font-weight: 700;
    }

    @media (max-width: 1024px) {
        .summary-grid {
            grid-template-columns: 1fr 1fr;
        }

        .summary-grid .action-col {
            grid-column: 1 / -1;
        }

        .btn-process {
            width: 100%;
        }

        .table-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .summary-hero {
            padding: 18px;
        }

        .hero-title {
            font-size: 22px;
        }

        .summary-grid,
        .table-list {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="summary-page">
    <section class="summary-hero">
        <div>
            <div class="hero-kicker">
                <i class="bi bi-database-gear"></i>
                Summary Engine
            </div>
            <h1 class="hero-title">Generate Summary Detail Transaksi</h1>
            <div class="hero-subtitle">
                Rebuild laporan transaksi berdasarkan rentang tanggal yang dipilih.
            </div>
        </div>
    </section>

    <section class="summary-card">
        <div class="summary-card-header">
            <div>
                <h2 class="summary-card-title">Parameter Generate</h2>
                <div class="summary-card-subtitle">Pilih tanggal awal dan akhir, lalu jalankan proses summary.</div>
            </div>
        </div>

        <div class="summary-card-body">
            @if(session('success'))
                <div class="summary-alert success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="summary-alert danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="summary-alert danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <strong>Validasi gagal:</strong>
                        <ul style="margin:6px 0 0;padding-left:18px;">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('investor.SummaryDetailTransaksi.master') }}">
                @csrf

                <div class="summary-grid">
                    <div>
                        <label class="field-label">Tanggal Awal</label>
                        <input
                            type="date"
                            name="tanggal_awal"
                            class="field-control"
                            value="{{ old('tanggal_awal', date('Y-m-d')) }}"
                            required
                        >
                    </div>

                    <div>
                        <label class="field-label">Tanggal Akhir</label>
                        <input
                            type="date"
                            name="tanggal_akhir"
                            class="field-control"
                            value="{{ old('tanggal_akhir', date('Y-m-d')) }}"
                            required
                        >
                    </div>

                    <div class="action-col">
                        <button type="submit" class="btn-process">
                            <i class="bi bi-lightning-charge-fill"></i>
                            Proses Summary
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="impact-box">
            <div class="impact-title">
                <i class="bi bi-layers"></i>
                Tabel yang akan di-rebuild
            </div>

            <ul class="table-list">
                <li><i class="bi bi-table"></i> tbl_laporan_bulanan</li>
                <li><i class="bi bi-table"></i> tbl_laporan_ecommerce</li>
                <li><i class="bi bi-table"></i> tbl_laporan_pareto</li>
                <li><i class="bi bi-table"></i> tbl_summary_jam_outlet</li>
                <li><i class="bi bi-table"></i> tbl_laporan_payment</li>
                <li><i class="bi bi-table"></i> tbl_laporan_service</li>
            </ul>

            <div class="warning-note">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                    Proses ini akan menghitung ulang data summary pada rentang tanggal yang dipilih.
                    Pastikan tanggal sudah benar sebelum menjalankan proses.
                </div>
            </div>
        </div>
    </section>
</div>

@include('Temp.Investor.footer')

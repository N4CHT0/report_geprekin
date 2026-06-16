@section('title', 'Data Sales Perkota')
@section('breadcrumb', 'Marketing / Data Sales Perkota')

@include('Temp.Investor.header')

<style>
    .ds-page{padding:24px;background:#f8fafc;min-height:calc(100vh - 70px)}
    .ds-panel{background:#fff;border:1px solid #e2e8f0;border-radius:26px;box-shadow:0 18px 50px rgba(15,23,42,.07)}
    .ds-hero{padding:24px;margin-bottom:18px;background:linear-gradient(135deg,#052e16,#16a34a);color:#fff}
    .ds-hero h1{margin:0;font-weight:900}
    .ds-hero p{opacity:.8;margin:7px 0 0}
    .ds-tabs{display:flex;gap:8px;margin-top:18px;flex-wrap:wrap}
    .ds-tab{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.16);color:#fff;padding:10px 14px;border-radius:999px;font-weight:800}
    .ds-tab.active{background:#fff;color:#166534}
    .ds-filter{display:grid;grid-template-columns:repeat(7,1fr);gap:10px;padding:16px;margin-bottom:18px}
    .ds-filter select,.ds-filter input{border:1px solid #e2e8f0;border-radius:14px;padding:11px;background:#f8fafc;width:100%;box-sizing:border-box}
    .ds-filter button,.ds-filter a{border:0;border-radius:14px;background:#16a34a;color:#fff;font-weight:900;text-align:center;text-decoration:none;padding:11px;display:flex;align-items:center;justify-content:center}
    .ds-filter a{background:#64748b}
    
    /* Select2 Tweaks */
    .select2-container--default .select2-selection--single {
        border: 1px solid #e2e8f0; border-radius: 14px; background: #f8fafc; height: 42px; display: flex; align-items: center; padding: 0 4px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    .ds-layout{display:grid;grid-template-columns:1fr 360px;gap:18px}
    .ds-table-wrap{overflow:auto}
    .ds-table{width:100%;border-collapse:separate;border-spacing:0;min-width:1000px}
    .ds-table th{background:#f1f5f9;color:#64748b;font-size:12px;text-transform:uppercase;text-align:left;padding:13px;position:sticky;top:0}
    .ds-table td{padding:13px;border-bottom:1px solid #f1f5f9;font-weight:650;color:#0f172a}
    .ds-table tr:hover td{background:#f8fafc}
    .ds-rank{width:34px;height:34px;border-radius:12px;background:#dcfce7;color:#166534;display:inline-flex;align-items:center;justify-content:center;font-weight:900}
    .ds-score{height:8px;background:#e2e8f0;border-radius:99px;overflow:hidden}
    .ds-score span{display:block;height:100%;background:#16a34a;border-radius:99px}
    .ds-side{display:grid;gap:18px}
    .ds-card{padding:18px}
    .ds-card h3{margin:0 0 14px;font-weight:900;color:#0f172a}
    .ds-metric{display:flex;justify-content:space-between;gap:12px;padding:12px;border-radius:16px;background:#f8fafc;margin-bottom:10px}
    .ds-metric small{color:#64748b}
    .ds-metric b{color:#0f172a;text-align:right}
    .ds-insight{border-left:4px solid #16a34a;background:#f0fdf4;border-radius:16px;padding:14px;color:#14532d;font-weight:700}
    .ds-empty{text-align:center;color:#64748b;padding:28px!important}
    .ds-status{display:inline-flex;align-items:center;gap:8px;margin-top:12px;padding:8px 12px;border-radius:999px;background:rgba(255,255,255,.16);font-weight:800}
    @media(max-width:1200px){.ds-filter{grid-template-columns:repeat(3,1fr)}}
    @media(max-width:1100px){.ds-layout{grid-template-columns:1fr}}
    @media(max-width:640px){.ds-page{padding:14px}.ds-filter{grid-template-columns:1fr}}
</style>

<div class="ds-page">
    <section class="ds-panel ds-hero">
        <h1>Data Sales Perkota</h1>
        <p>Data performa outlet dari Google Sheet CSV.</p>

        <div class="ds-status">
            Status Sheet: {{ $snapshot['status_sheet'] ?? 'Unknown' }}
        </div>

        <div class="ds-tabs">
            <button type="button" class="ds-tab active">Overview</button>
            <button type="button" class="ds-tab">Outlet Ranking</button>
            <button type="button" class="ds-tab">Provinsi</button>
            <button type="button" class="ds-tab">Anomali</button>
        </div>
    </section>

    <form class="ds-panel ds-filter" method="GET" action="{{ url()->current() }}">
        <select name="tahun">
            <option value="All">All Tahun</option>
            @foreach($options['tahun'] ?? [] as $tahun)
                <option value="{{ $tahun }}" @selected(($filters['tahun'] ?? date('Y')) == $tahun)>
                    {{ $tahun }}
                </option>
            @endforeach
        </select>

        <select name="outlet">
            <option value="All">All Outlet</option>
            @foreach($options['outlet'] ?? [] as $outlet)
                <option value="{{ $outlet }}" @selected(($filters['outlet'] ?? 'All') == $outlet)>
                    {{ $outlet }}
                </option>
            @endforeach
        </select>

        <select name="provinsi">
            <option value="All">All Provinsi</option>
            @foreach($options['provinsi'] ?? [] as $provinsi)
                <option value="{{ $provinsi }}" @selected(($filters['provinsi'] ?? 'All') == $provinsi)>
                    {{ $provinsi }}
                </option>
            @endforeach
        </select>

        <select name="kota">
            <option value="All">All Kota</option>
            @foreach($options['kota'] ?? [] as $kota)
                <option value="{{ $kota }}" @selected(($filters['kota'] ?? 'All') == $kota)>
                    {{ $kota }}
                </option>
            @endforeach
        </select>

        <select name="bulan">
            <option value="All">All Bulan</option>
            @foreach($options['bulan'] ?? [] as $bulan)
                <option value="{{ $bulan }}" @selected(($filters['bulan'] ?? 'All') == $bulan)>
                    {{ $bulan }}
                </option>
            @endforeach
        </select>

        <select name="quarter">
            <option value="All">All Quarter</option>
            @foreach($options['quarter'] ?? [] as $quarter)
                <option value="{{ $quarter }}" @selected(($filters['quarter'] ?? 'All') == $quarter)>
                    {{ $quarter }}
                </option>
            @endforeach
        </select>

        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" title="Tanggal Mulai">
        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" title="Tanggal Akhir">

        <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari outlet / kota">

        <button type="submit">Filter</button>
        <a href="{{ url()->current() }}">Reset</a>
    </form>

    <div class="ds-layout">
        <section class="ds-panel ds-table-wrap">
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Outlet</th>
                        <th>Kota</th>
                        <th>Provinsi</th>
                        <th>Bulan</th>
                        <th>Quarter</th>
                        <th>Omzet</th>
                        <th>CU</th>
                        <th>Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginator as $index => $row)
                        <tr>
                            <td><span class="ds-rank">{{ $paginator->firstItem() + $index }}</span></td>
                            <td>{{ $row->outlet ?? '-' }}</td>
                            <td>{{ $row->kota ?? '-' }}</td>
                            <td>{{ $row->provinsi ?? '-' }}</td>
                            <td>{{ $row->bulan_str ?? '-' }}</td>
                            <td>{{ $row->quarter_str ?? '-' }}</td>
                            <td>Rp{{ number_format((float) ($row->omset ?? 0), 0, ',', '.') }}</td>
                            <td>{{ number_format((float) ($row->cu ?? 0), 0, ',', '.') }}</td>
                            <td>
                                <div class="ds-score">
                                    <span style="width:{{ $row->skor }}%"></span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="ds-empty">
                                Tidak ada data yang sesuai dengan filter Anda.
                                <br>
                                Coba sesuaikan pilihan Tahun, Bulan, atau Kata Kunci pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="padding: 16px;">
                {{ $paginator->links() }}
            </div>
        </section>

        <aside class="ds-side">
            <div class="ds-panel ds-card">
                <h3>Snapshot</h3>

                <div class="ds-metric">
                    <small>Total Omzet</small>
                    <b>{{ $snapshot['total_omzet'] ?? 'Rp0' }}</b>
                </div>

                <div class="ds-metric">
                    <small>Total CU</small>
                    <b>{{ $snapshot['total_cu'] ?? '0' }}</b>
                </div>

                <div class="ds-metric">
                    <small>Avg Basket</small>
                    <b>{{ $snapshot['avg_basket'] ?? 'Rp0' }}</b>
                </div>

                <div class="ds-metric">
                    <small>Jumlah Data</small>
                    <b>{{ $snapshot['jumlah_data'] ?? 0 }}</b>
                </div>
            </div>

            <div class="ds-panel ds-card">
                <h3>Insight</h3>
                <div class="ds-insight">
                    Semua data telah otomatis terhubung ke sistem basis data (database). Gunakan filter di atas untuk menelusuri rangkuman performa setiap outlet secara riil.
                </div>
            </div>
        </aside>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        $('.ds-filter select').select2({
            width: '100%'
        });
    });
</script>
@endpush

@include('Temp.Investor.footer')

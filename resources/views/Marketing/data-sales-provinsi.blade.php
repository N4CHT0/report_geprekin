@section('title', 'Data Sales Provinsi')
@section('breadcrumb', 'Marketing / Data Sales Provinsi')

@include('Temp.Investor.header')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        --primary: #0f172a;
        --primary-hover: #334155;
        --accent: #2563eb;
        --accent-light: #eff6ff;
        --success: #059669;
        --success-bg: #ecfdf5;
        --warning: #d97706;
        --danger: #dc2626;
        --danger-bg: #fef2f2;
        --bg-color: #f8fafc;
        --surface: #ffffff;
        --border: #e2e8f0;
        --border-hover: #cbd5e1;
        --text-strong: #0f172a;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --radius-xl: 12px;
        --shadow-soft: 0 1px 3px rgba(0, 0, 0, 0.01);
    }

    * { box-sizing: border-box; }

    body {
        background: var(--bg-color);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: var(--text-main);
        -webkit-font-smoothing: antialiased;
        letter-spacing: -0.01em;
    }

    .ds-page { padding: 24px 32px; max-width: 1600px; margin: 0 auto; min-height: calc(100vh - 70px); }
    
    /* Premium Cards */
    .ds-panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-soft);
        transition: border-color 0.15s ease;
    }
    .ds-panel:hover { border-color: var(--border-hover); }
    
    /* Hero Section */
    .ds-hero {
        padding: 24px;
        margin-bottom: 24px;
        background: var(--surface);
        color: var(--text-main);
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border);
    }
    .ds-hero h1 { margin: 0 0 4px 0; font-weight: 700; font-size: 24px; letter-spacing: -0.02em; color: var(--text-strong); }
    .ds-hero p { margin: 0; color: var(--text-muted); font-size: 14px; max-width: 600px; font-weight: 500; }
    
    /* Status Badge */
    .ds-status {
        display: inline-flex; align-items: center; gap: 6px; margin-top: 16px; padding: 4px 10px;
        border-radius: 6px; background: var(--success-bg); border: 1px solid #a7f3d0;
        font-weight: 600; font-size: 12px; color: var(--success);
    }
    .ds-status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--success); }

    /* Modern Tabs */
    .ds-tabs { display: flex; gap: 8px; margin-top: 20px; flex-wrap: wrap; }
    .ds-tab {
        background: var(--bg-color); border: 1px solid var(--border); color: var(--text-muted);
        padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; transition: all 0.15s;
        font-family: inherit; text-decoration: none; display: inline-block;
    }
    .ds-tab:hover { background: var(--border); color: var(--text-main); }
    .ds-tab.active { background: var(--primary); border-color: var(--primary); color: #fff; }

    /* Filters */
    .ds-filter { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; padding: 20px; margin-bottom: 24px; align-items: end;}
    .ds-filter-group { display: flex; flex-direction: column; gap: 6px; }
    .ds-filter-group label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .ds-filter input, .ds-filter select {
        border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; background: var(--surface);
        width: 100%; box-sizing: border-box; font-family: inherit; font-size: 13px; font-weight: 500; transition: all 0.15s; height: 36px;
    }
    .ds-filter input:hover, .ds-filter select:hover { border-color: var(--border-hover); }
    .ds-filter input:focus, .ds-filter select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-light); }
    
    .ds-filter-actions { display: flex; gap: 12px; height: 36px;}
    .ds-filter button, .ds-filter a {
        border: 1px solid transparent; border-radius: 6px; background: var(--primary); color: #fff; font-weight: 600; font-family: inherit;
        text-align: center; text-decoration: none; padding: 0 16px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.15s; font-size: 13px; flex: 1; height: 36px;
    }
    .ds-filter button:hover { background: var(--primary-hover); }
    .ds-filter a { background: var(--surface); color: var(--text-main); border-color: var(--border); }
    .ds-filter a:hover { background: var(--bg-color); }

    /* Select2 Overrides */
    .select2-container--default .select2-selection--multiple {
        border: 1px solid var(--border) !important; border-radius: 6px !important; min-height: 36px !important; background: var(--surface) !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: var(--accent) !important; box-shadow: 0 0 0 3px var(--accent-light) !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background: var(--bg-color) !important; border: 1px solid var(--border) !important; color: var(--text-main) !important;
        border-radius: 4px !important; font-weight: 500 !important; font-size: 12px !important; padding: 2px 8px 2px 24px !important; margin-top: 4px !important; position: relative;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: var(--text-muted) !important; position: absolute !important; left: 6px !important; top: 50% !important; transform: translateY(-50%) !important; border-right: none !important; margin-right: 0 !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover { background: transparent !important; color: var(--danger) !important; }

    /* Layout & Table */
    .ds-layout { display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start; }
    .ds-table-wrap { overflow: auto; border-radius: var(--radius-lg); }
    .ds-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 900px; }
    .ds-table th {
        background: var(--bg-color); color: var(--text-muted); font-size: 11px; font-weight: 600; letter-spacing: 0.05em;
        text-transform: uppercase; text-align: left; padding: 12px 20px; position: sticky; top: 0; border-bottom: 1px solid var(--border); z-index: 10;
    }
    .ds-table td { padding: 12px 20px; border-bottom: 1px solid var(--border); font-weight: 500; color: var(--text-strong); font-size: 13px; vertical-align: middle; transition: background 0.15s;}
    .ds-table tr:last-child td { border-bottom: none; }
    .ds-table tr:hover td { background: var(--bg-color); }
    
    .ds-rank {
        width: 28px; height: 28px; border-radius: 6px; background: var(--bg-color); color: var(--text-muted); border: 1px solid var(--border);
        display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px;
    }
    .ds-table tr:nth-child(1) .ds-rank { background: #fef9c3; color: #854d0e; border-color: #fde047; }
    .ds-table tr:nth-child(2) .ds-rank { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
    .ds-table tr:nth-child(3) .ds-rank { background: #ffedd5; color: #9a3412; border-color: #fdba74; }

    /* Progress Score */
    .ds-score { height: 6px; background: var(--bg-color); border-radius: 99px; overflow: hidden; width: 100%; max-width: 200px; border: 1px solid var(--border); }
    .ds-score span { display: block; height: 100%; background: var(--accent); border-radius: 99px; transition: width 1s ease-out; }

    /* Sidebar Snapshot */
    .ds-side { display: grid; gap: 24px; position: sticky; top: 24px; }
    .ds-card { padding: 20px; position: relative; overflow: hidden; }
    
    .ds-card h3 { margin: 0 0 16px 0; font-weight: 700; color: var(--text-strong); font-size: 16px; letter-spacing: -0.01em; display: flex; align-items: center; gap: 8px; text-transform: uppercase;}
    
    .ds-metric { display: flex; flex-direction: column; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--border); }
    .ds-metric:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .ds-metric small { color: var(--text-muted); font-weight: 600; font-size: 11px; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.05em;}
    .ds-metric b { color: var(--text-strong); font-size: 24px; font-weight: 700; letter-spacing: -0.03em; line-height: 1.1; }
    .ds-metric.highlight b { color: var(--success); }

    .ds-insight { border: 1px solid #bfdbfe; background: var(--accent-light); border-radius: var(--radius-sm); padding: 12px 16px; color: var(--accent); font-weight: 600; font-size: 13px; line-height: 1.5; }
    
    /* Animations */
    @keyframes fadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
    .ds-panel { animation: fadeUp 0.3s ease-out forwards; opacity: 0; }
    .ds-hero { animation-delay: 0s; }
    .ds-filter { animation-delay: 0.05s; }
    .ds-table-wrap { animation-delay: 0.1s; }
    .ds-side { animation: fadeUp 0.3s ease-out forwards 0.15s; opacity: 0; }

    @media(max-width:1200px){ .ds-layout { grid-template-columns: 1fr; } .ds-side { position: static; grid-template-columns: 1fr 1fr; } }
    @media(max-width:768px){ .ds-side { grid-template-columns: 1fr; } }
</style>

<div class="ds-page">
    <section class="ds-panel ds-hero">
        <h1>Sales Intelligence per Provinsi</h1>
        <p>Ringkasan performa omset, CU, outlet aktif, dan basket size (Live Database).</p>

        <div class="ds-status">
            Connected to Database | Data: {{ $snapshot['jumlah_data'] ?? 0 }}
        </div>

        <div class="ds-tabs">
            <a href="{{ url('/marketing/sales-per-kota') }}" class="ds-tab">Overview</a>
            <a href="{{ url('/marketing/data-sales-perkota') }}" class="ds-tab">Outlet Ranking</a>
            <a href="{{ url('/marketing/data-sales-provinsi') }}" class="ds-tab active">Provinsi</a>
            <a href="{{ url('/marketing/anomali-kota') }}" class="ds-tab">Anomali</a>
        </div>
    </section>

    <form class="ds-panel ds-filter" method="GET" action="{{ url()->current() }}">
        
        <div class="ds-filter-group">
            <label>Provinsi</label>
            <select name="provinsi[]" multiple="multiple">
                <option value="All" @selected(in_array('All', (array)($filters['provinsi'] ?? ['All'])))>All Provinsi</option>
                @foreach($options['provinsi'] ?? [] as $provinsi)
                    <option value="{{ $provinsi }}" @selected(in_array($provinsi, (array)($filters['provinsi'] ?? [])))>
                        {{ $provinsi }}
                    </option>
                @endforeach
            </select>
        </div>



        <div class="ds-filter-group">
            <label>Tahun</label>
            <select name="tahun[]" multiple="multiple">
                <option value="All" @selected(in_array('All', (array)($filters['tahun'] ?? ['All'])))>All Tahun</option>
                @foreach($options['tahun'] ?? [] as $tahun)
                    <option value="{{ $tahun }}" @selected(in_array($tahun, (array)($filters['tahun'] ?? [])))>
                        {{ $tahun }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ds-filter-group">
            <label>Bulan</label>
            <select name="bulan[]" multiple="multiple">
                <option value="All" @selected(in_array('All', (array)($filters['bulan'] ?? ['All'])))>All Bulan</option>
                @foreach($options['bulan'] ?? [] as $bulan)
                    <option value="{{ $bulan }}" @selected(in_array($bulan, (array)($filters['bulan'] ?? [])))>
                        {{ $bulan }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ds-filter-group" style="display: none;">
            <label>Outlet</label>
            <select name="outlet[]" multiple="multiple">
                <option value="All" @selected(in_array('All', (array)($filters['outlet'] ?? ['All'])))>All Outlet</option>
                @foreach($options['outlet'] ?? [] as $outlet)
                    <option value="{{ $outlet }}" @selected(in_array($outlet, (array)($filters['outlet'] ?? [])))>
                        {{ $outlet }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ds-filter-group" style="display: none;">
            <label>Quarter</label>
            <select name="quarter[]" multiple="multiple">
                <option value="All" @selected(in_array('All', (array)($filters['quarter'] ?? ['All'])))>All Quarter</option>
                @foreach($options['quarter'] ?? [] as $quarter)
                    <option value="{{ $quarter }}" @selected(in_array($quarter, (array)($filters['quarter'] ?? [])))>
                        {{ $quarter }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="ds-filter-group">
            <label>Pencarian</label>
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Ketik kata kunci...">
        </div>

        <div class="ds-filter-actions">
            <button type="submit">Filter Data</button>
            <a href="{{ url()->current() }}">Reset</a>
        </div>
    </form>

    <div class="ds-layout">
        <section class="ds-panel ds-table-wrap">
            <table class="ds-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Provinsi</th>
                        <th>Outlet Aktif</th>
                        <th>Total Omzet</th>
                        <th>Total CU</th>
                        <th>Avg Basket Size</th>
                        <th>Performa Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginator as $index => $row)
                        <tr>
                            <td><span class="ds-rank">{{ $paginator->firstItem() + $index }}</span></td>
                            <td>{{ $row->provinsi ?? 'Unidentified Area' }}</td>
                            <td>{{ $row->jumlah_outlet_aktif ?? 0 }} Unit</td>
                            <td style="color:var(--success); font-weight:800;">Rp{{ number_format((float) ($row->omset ?? 0), 0, ',', '.') }}</td>
                            <td>{{ number_format((float) ($row->cu ?? 0), 0, ',', '.') }}</td>
                            <td>Rp{{ number_format((float) ($row->avg_basket ?? 0), 0, ',', '.') }}</td>
                            <td style="width: 250px;">
                                <div class="ds-score" title="{{ $row->skor }}%">
                                    <span style="width:{{ $row->skor }}%"></span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="ds-empty">
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
                <h3><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg> Snapshot Performa</h3>

                <div class="ds-metric highlight">
                    <small>Total Omzet</small>
                    <b>{{ $snapshot['total_omzet'] ?? 'Rp0' }}</b>
                </div>

                <div class="ds-metric">
                    <small>Total Transaksi (CU)</small>
                    <b>{{ $snapshot['total_cu'] ?? '0' }}</b>
                </div>

                <div class="ds-metric highlight">
                    <small>Average Basket Size</small>
                    <b>{{ $snapshot['avg_basket'] ?? 'Rp0' }}</b>
                </div>
            </div>

            <div class="ds-panel ds-card">
                <h3><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Insight Intelijen</h3>
                <div class="ds-insight">
                    Data agregat ini di-generate secara <strong>live</strong> dari tabel transaksi gabungan. Anda dapat mengevaluasi provinsi mana yang paling menguntungkan (Cash Cow) berdasarkan skor performa di samping kiri.
                </div>
            </div>
        </aside>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {
        $('.ds-filter select').select2({
            width: '100%',
            placeholder: 'Pilih...',
            closeOnSelect: false
        });
    });
</script>
@endpush

@include('Temp.Investor.footer')

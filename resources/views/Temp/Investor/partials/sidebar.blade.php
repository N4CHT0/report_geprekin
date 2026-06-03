{{-- resources/views/Temp/Investor/partials/sidebar.blade.php --}}
<aside id="sidebar">
    @php
        $role = auth()->check() ? auth()->user()->role : null;
        $isSuperadmin = $role === 'superadmin';

        $inventoryFullRoles = ['superadmin', 'superadmin_audit', 'tm_manager', 'spv', 'leader'];
        $inventoryFormOnlyRoles = ['crew'];

        $isInventoryFull = in_array($role, $inventoryFullRoles, true);
        $isCrewFormOnly = in_array($role, $inventoryFormOnlyRoles, true);

        $laporanOpen =
            request()->routeIs('investor.laporan.*') ||
            request()->routeIs('laporan.laporanDSC') ||
            request()->routeIs('laporan.laporanExpense') ||
            request()->routeIs('undian.undianReport');

        $inventoryOpen =
            request()->routeIs('master.qcr.*') ||
            request()->routeIs('master.dsc.*') ||
            request()->routeIs('master.dscFormulir.*');

        $auditorOpen =
            request()->routeIs('dashboard.harian') ||
            request()->routeIs('dashboard.harian.*');

        $purchasingOpen =
            request()->is('dashboard-outlet') ||
            request()->is('dashboard-scm') ||
            request()->is('stock-control') ||
            request()->is('scm/pengiriman') ||
            request()->is('scm/surat-jalan');

        $setupScmOpen =
            request()->is('outlet-mapping') ||
            request()->is('list-distributor');

        $masterDataOpen =
            request()->routeIs('investor.internal.audit.*') ||
            request()->routeIs('investor.rto.*') ||
            request()->routeIs('investor.ebitda.*') ||
            request()->routeIs('investor.area.*') ||
            request()->routeIs('master.qcr.dataqcr') ||
            request()->routeIs('investor.user.*') ||
            request()->routeIs('investor.master') ||
            request()->routeIs('investor.outlet.*') ||
            request()->routeIs('investor.*.master');
    @endphp

    <a href="{{ route('investor.sales.dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('../img/logo2.jpg') }}" alt="Geprekin">
        <div class="brand-copy">
            <div class="brand-title">Geprekin</div>
            <div class="brand-subtitle">Investor Console</div>
        </div>
    </a>

    <div class="sidebar-search">
        <div class="sidebar-search-box">
            <i class="bi bi-search"></i>
            <input id="sidebarSearchInput" type="text" placeholder="Cari menu...">
        </div>
    </div>

    <nav class="sidebar-nav" id="sidebarNavigation">
        <div class="nav-section">Dashboard</div>

        <a href="{{ route('investor.sales.dashboard') }}"
           class="side-link {{ request()->routeIs('investor.sales.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard Utama</span>
        </a>

        <a href="{{ route('investor.sales.dashboardGO') }}"
           class="side-link {{ request()->routeIs('investor.sales.dashboardGO') ? 'active' : '' }}">
            <i class="bi bi-rocket-takeoff"></i>
            <span>Dashboard GO</span>
        </a>

        <div class="nav-section">Laporan</div>

        <div x-data="{ open: {{ $laporanOpen ? 'true' : 'false' }} }">
            <button type="button" class="side-toggle" @click="open = !open">
                <i class="bi bi-bar-chart-line"></i>
                <span>Laporan</span>
                <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
            </button>

            <div class="side-child" x-show="open" x-collapse>
                <a href="{{ route('investor.laporan.perbulan') }}">Penjualan per Bulan</a>
                <a href="{{ route('investor.laporan.pertahun') }}">Penjualan per Tahun</a>
                <a href="{{ route('investor.laporan.menu') }}">Penjualan per Menu</a>

                @if($isSuperadmin)
                    <a href="{{ route('laporan.laporanDSC') }}">Daily Stock Control</a>
                    <a href="{{ route('laporan.laporanExpense') }}">Expense POSLite</a>
                    <a href="{{ route('investor.laporan.profitnloss') }}">Profit & Loss</a>
                    <a href="{{ route('investor.laporan.profitnloss.oknho') }}">Profit & Loss Internal</a>
                    <a href="{{ route('undian.undianReport') }}">Undian Berhadiah</a>
                @endif
            </div>
        </div>

        <div class="nav-section">Ticketing</div>

        <a href="{{ route('ticketing.dashboard') }}"
        class="side-link {{ request()->routeIs('ticketing.dashboard') ? 'active' : '' }}">
            <i class="bi bi-ticket-detailed"></i>
            <span>Dashboard Ticketing</span>
        </a>

        @auth
            @if($isCrewFormOnly)
                <div class="nav-section">Inventory</div>

                <a href="{{ route('master.dscFormulir.index') }}"
                   class="side-link {{ request()->routeIs('master.dscFormulir.*') ? 'active' : '' }}">
                    <i class="bi bi-clipboard-check"></i>
                    <span>DSC Formulir</span>
                </a>
            @endif

            @if($isInventoryFull)
                <div class="nav-section">Inventory</div>

                <div x-data="{ open: {{ $inventoryOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventory Ops</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        <a href="{{ route('master.qcr.index') }}">Quality Cost Report</a>
                        <a href="{{ route('master.dsc.index') }}">Daily Stock Control</a>
                        <a href="{{ route('master.dscFormulir.index') }}">DSC Formulir</a>
                    </div>
                </div>

                <div x-data="{ open: {{ $auditorOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-shield-check"></i>
                        <span>Auditor Backoffice</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        <a href="{{ route('dashboard.harian') }}">Backoffice Auditor</a>
                    </div>
                </div>
            @endif

            <div class="nav-section">Purchasing</div>

            <div x-data="{ open: {{ $purchasingOpen ? 'true' : 'false' }} }">
                <button type="button" class="side-toggle" @click="open = !open">
                    <i class="bi bi-cart-check"></i>
                    <span>Purchasing</span>
                    <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                </button>

                <div class="side-child" x-show="open" x-collapse>
                    <a href="/dashboard-outlet">Dashboard Outlet</a>
                    <a href="/dashboard-scm">Dashboard SCM</a>
                    <a href="/stock-control">Stock DC</a>
                    <a href="/scm/pengiriman">Order List</a>
                    <a href="/scm/surat-jalan">Daftar Surat Jalan</a>
                </div>
            </div>

            <div x-data="{ open: {{ $setupScmOpen ? 'true' : 'false' }} }">
                <button type="button" class="side-toggle" @click="open = !open">
                    <i class="bi bi-diagram-3"></i>
                    <span>Setup SCM</span>
                    <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                </button>

                <div class="side-child" x-show="open" x-collapse>
                    <a href="/supplier-list">Daftar Supplier</a>
                    <a href="/customers">Daftar Customer</a>
                    <a href="/scm/produk">Data Produk SCM</a>
                    <a href="/list-armada">Daftar Armada</a>
                    <a href="/list-distributor">Daftar DC</a>
                    <a href="/list-driver">Daftar Supir</a>
                    <a href="/unit-list">Daftar Unit</a>
                    <a href="/outlet-mapping">Outlet Mapping</a>
                    <a href="/mapping-supplier">Supplier Mapping</a>
                </div>
            </div>

            <div x-data="{ open: false }">
                <button type="button" class="side-toggle" @click="open = !open">
                    <i class="bi bi-lightning-charge"></i>
                    <span>Simples</span>
                    <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                </button>

                <div class="side-child" x-show="open" x-collapse>
                    <a href="/simple-sales">Simple Sales</a>
                    <a href="/simple-transfer">Simple Transfer</a>
                    <a href="/simple-purchase">Simple Purchase</a>
                </div>
            </div>
            <div x-data="{ open: false }">
                <button type="button" class="side-toggle" @click="open = !open">
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>PO-SO Integrated</span>
                    <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                </button>

                <div class="side-child" x-show="open" x-collapse>
                    <a href="/purchase-order">Purchase Order</a>
                    <a href="/sales-order">Sales Order</a>
                    <a href="/scm/goods-delivery">Goods Delivery</a>
                    <a href="/scm/goods-receipt">Goods Receipt</a>
                    <a href="/scm/sales-invoice">Sales Invoice</a>
                    <a href="/scm/purchase-invoice">Purchase Invoice</a>
                </div>
            </div>

            <div x-data="{ open: false }">
                <button type="button" class="side-toggle" @click="open = !open">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>Report</span>
                    <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                </button>

                <div class="side-child" x-show="open" x-collapse>
                    <a href="/history-purchase-order">Outlet PO History</a>
                    <a href="/purchasing/reports/stock-movement">Stock Movement Report</a>
                    <a href="/purchasing/reports/stock-opname">Stock Opname Report</a>
                    <a href="/purchasing/reports/goods-receipt-recap">Goods Receipt Recapitulation Report</a>
                    <a href="/purchasing/reports/goods-delivery-recap">Goods Delivery Recapitulation Report</a>
                </div>
            </div>

            @if($isSuperadmin)
                <div class="nav-section">AI & Master</div>

                <a href="{{ route('master.surveyor.index') }}"
                   class="side-link {{ request()->routeIs('master.surveyor.*') ? 'active' : '' }}">
                    <i class="bi bi-robot"></i>
                    <span>Surveyor</span>
                </a>

                <div x-data="{ open: {{ $masterDataOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-database-fill-gear"></i>
                        <span>Master Data</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        <a href="{{ route('investor.user.master') }}">Users Geprekinaja</a>
                        <a href="{{ route('investor.master') }}">Mitra Investor</a>
                        <a href="{{ route('investor.outlet.master') }}">Data Outlet</a>
                        <a href="{{ route('investor.outletMatchAPI.master') }}">Match Outlet API</a>
                        <a href="{{ route('investor.SummaryDetailTransaksi.form') }}">Summary Transaksi</a>
                        <a href="{{ route('investor.area.master') }}">Area Teritorial</a>
                        <a href="{{ route('master.qcr.dataqcr') }}">Data Stock & DSC OPS</a>
                    </div>
                </div>
            @endif
        @endauth
    </nav>

    <div class="sidebar-footer">
        <div class="system-pill">
            <i class="bi bi-cloud-check"></i>
            <span>System Online</span>
        </div>
    </div>
</aside>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('sidebarSearchInput');
    const nav = document.getElementById('sidebarNavigation');

    if (!input || !nav) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();

        nav.querySelectorAll('.side-link, .side-toggle').forEach(item => {
            const wrapper = item.closest('[x-data]') || item;
            const text = wrapper.textContent.toLowerCase();

            wrapper.style.display = (!q || text.includes(q)) ? '' : 'none';
        });
    });
});
</script>
@endpush

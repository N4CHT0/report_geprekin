{{-- resources/views/Temp/Investor/partials/sidebar.blade.php --}}
<aside id="sidebar">
    @php
        /*
        |--------------------------------------------------------------------------
        | SIDEBAR BERBASIS PERMISSION
        |--------------------------------------------------------------------------
        | Pastikan helper hasPermission() sudah aktif di app/helpers.php
        | dan composer.json sudah load:
        |
        | "autoload": {
        |     "files": ["app/helpers.php"],
        |     "psr-4": {
        |         "App\\": "app/"
        |     }
        | }
        |
        | Setelah edit composer.json jalankan:
        | composer dump-autoload
        */

        $role = auth()->check() ? auth()->user()->role : null;
        $isSuperadmin = $role === 'superadmin';

        $can = function ($permission) {
            return function_exists('hasPermission') && hasPermission($permission);
        };

        /*
        |--------------------------------------------------------------------------
        | OPEN STATE MENU
        |--------------------------------------------------------------------------
        */
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
            request()->is('supplier-list') ||
            request()->is('customers') ||
            request()->is('scm/produk') ||
            request()->is('list-armada') ||
            request()->is('list-distributor') ||
            request()->is('list-driver') ||
            request()->is('unit-list') ||
            request()->is('outlet-mapping') ||
            request()->is('mapping-supplier');

        $simpleOpen =
            request()->is('simple-sales') ||
            request()->is('simple-transfer') ||
            request()->is('simple-purchase');

        $poSoOpen =
            request()->is('purchase-order') ||
            request()->is('sales-order') ||
            request()->is('scm/goods-delivery') ||
            request()->is('scm/goods-receipt') ||
            request()->is('scm/sales-invoice') ||
            request()->is('scm/purchase-invoice');

        $reportOpen =
            request()->is('history-purchase-order') ||
            request()->is('purchasing/reports/*');

        $masterDataOpen =
            request()->routeIs('investor.internal.audit.*') ||
            request()->routeIs('investor.rto.*') ||
            request()->routeIs('investor.ebitda.*') ||
            request()->routeIs('investor.area.*') ||
            request()->routeIs('master.qcr.dataqcr') ||
            request()->routeIs('investor.user.*') ||
            request()->routeIs('investor.master') ||
            request()->routeIs('investor.outlet.*') ||
            request()->routeIs('investor.*.master') ||
            request()->routeIs('permissions.*');

        /*
        |--------------------------------------------------------------------------
        | GROUP VISIBILITY
        |--------------------------------------------------------------------------
        */
        $showDashboard =
            $can('investor.sales.dashboard') ||
            $can('investor.sales.dashboardGO') ||
            $can('monitoring.sales');

        $showLaporan =
            $can('investor.laporan.perbulan') ||
            $can('investor.laporan.pertahun') ||
            $can('investor.laporan.menu') ||
            $can('laporan.laporanDSC') ||
            $can('laporan.laporanExpense') ||
            $can('investor.laporan.profitnloss') ||
            $can('investor.laporan.profitnloss.oknho') ||
            $can('undian.undianReport');

        $showInventory =
            $can('master.qcr.index') ||
            $can('master.dsc.index') ||
            $can('master.dscFormulir.index');

        $showAuditor =
            $can('dashboard.harian');

        $showPurchasing =
            $can('purchasing.dashboardOutlet') ||
            $can('purchasing.dashboardSCM') ||
            $can('purchasing.stockControl') ||
            $can('scm.pengiriman.index') ||
            $can('scm.surat-jalan.index');

        $showSetupScm =
            $can('supplier.index') ||
            $can('customers.index') ||
            $can('scm.index-bahan') ||
            $can('purchasing.armadaList') ||
            $can('purchasing.listDistributor') ||
            $can('purchasing.driverList') ||
            $can('purchasing.unitList') ||
            $can('admin.mapping.index') ||
            $can('outlet.mapping.supplier');

        $showSimple =
            $can('simple-sales.index') ||
            $can('simple-transfer.index') ||
            $can('simple-purchase.index');

        $showPoSo =
            $can('purchase-order.index') ||
            $can('sales-order.index') ||
            $can('goods-delivery.index') ||
            $can('goods-receipt.index') ||
            $can('sales-invoice.index') ||
            $can('purchase-invoice.index');

        $showPurchasingReport =
            $can('scm.history-po') ||
            $can('reports.stock.movement') ||
            $can('reports.stock.opname') ||
            $can('reports.gr.recap') ||
            $can('reports.gd.recap');

        $showMasterData =
            $can('permissions.index') ||
            $can('master.surveyor.index') ||
            $can('investor.user.master') ||
            $can('investor.user.operasional') ||
            $can('investor.master') ||
            $can('investor.outlet.master') ||
            $can('investor.outletMatchAPI.master') ||
            $can('investor.SummaryDetailTransaksi.form') ||
            $can('investor.area.master') ||
            $can('master.qcr.dataqcr');
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
        @if($showDashboard)
            <div class="nav-section">Dashboard</div>

            @if($can('investor.sales.dashboard'))
                <a href="{{ route('investor.sales.dashboard') }}"
                   class="side-link {{ request()->routeIs('investor.sales.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard Utama</span>
                </a>
            @endif

            @if(Route::has('investor.sales.dashboardGO') && $can('investor.sales.dashboardGO'))
                <a href="{{ route('investor.sales.dashboardGO') }}"
                   class="side-link {{ request()->routeIs('investor.sales.dashboardGO') ? 'active' : '' }}">
                    <i class="bi bi-rocket-takeoff"></i>
                    <span>Dashboard GO</span>
                </a>
            @endif

            @if(Route::has('monitoring.sales') && $can('monitoring.sales'))
                <a href="{{ route('monitoring.sales') }}"
                   class="side-link {{ request()->routeIs('monitoring.sales') ? 'active' : '' }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Monitoring Sales</span>
                </a>
            @endif
        @endif

        @if(
            (function_exists('hasPermission') && hasPermission('marketing.sales-per-kota')) ||
            (function_exists('hasPermission') && hasPermission('marketing.outlet-z1')) ||
            (function_exists('hasPermission') && hasPermission('marketing.data-sales-perkota')) ||
            (function_exists('hasPermission') && hasPermission('marketing.menu-terlaris')) ||
            (function_exists('hasPermission') && hasPermission('marketing.content-posting')) ||
            (function_exists('hasPermission') && hasPermission('marketing.produk-baru')) ||
            (function_exists('hasPermission') && hasPermission('marketing.outlet-go')) ||
            (function_exists('hasPermission') && hasPermission('marketing.outlet-existing')) ||
            (function_exists('hasPermission') && hasPermission('marketing.kompetitor')) ||
            (function_exists('hasPermission') && hasPermission('marketing.market-intelligence')) ||
            request()->routeIs('marketing.area_potensi')
        )
            <div class="nav-section">Marketing</div>
            <div x-data="{ open: {{ request()->routeIs('marketing.*') ? 'true' : 'false' }} }">
                <button type="button" class="side-toggle" @click="open = !open">
                    <i class="bi bi-megaphone"></i>
                    <span>Dashboard Marketing</span>
                    <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                </button>
                <div class="side-child" x-show="open" x-collapse>
                    @if(Route::has('marketing.sales-per-kota') && hasPermission('marketing.sales-per-kota'))
                        <a href="{{ route('marketing.sales-per-kota') }}" class="{{ request()->routeIs('marketing.sales-per-kota') || request()->routeIs('marketing.data-sales-perkota') || request()->routeIs('marketing.data-sales-provinsi') || request()->routeIs('marketing.anomali-kota') ? 'active' : '' }}">Sales Intelligence</a>
                    @endif
                    @if(Route::has('marketing.market-intelligence') && hasPermission('marketing.market-intelligence'))
                        <a href="{{ route('marketing.market-intelligence') }}" class="{{ request()->routeIs('marketing.market-intelligence') ? 'active' : '' }}">Market Intelligence</a>
                    @endif
                    @if(Route::has('marketing.kompetitor') && hasPermission('marketing.kompetitor'))
                        <a href="{{ route('marketing.kompetitor') }}" class="{{ request()->routeIs('marketing.kompetitor') ? 'active' : '' }}">Analisis Kompetitor</a>
                    @endif

                    <hr>

                    @if(Route::has('marketing.outlet-z') && hasPermission('marketing.outlet-z1'))
                        <a href="{{ route('marketing.outlet-z') }}" class="{{ request()->routeIs('marketing.outlet-z') ? 'active' : '' }}">Outlet Z</a>
                    @endif
                    @if(Route::has('marketing.outlet-go') && hasPermission('marketing.outlet-go'))
                        <a href="{{ route('marketing.outlet-go') }}" class="{{ request()->routeIs('marketing.outlet-go') ? 'active' : '' }}">Outlet Go</a>
                    @endif
                    @if(Route::has('marketing.outlet-existing') && hasPermission('marketing.outlet-existing'))
                        <a href="{{ route('marketing.outlet-existing') }}" class="{{ request()->routeIs('marketing.outlet-existing') ? 'active' : '' }}">Outlet Existing</a>
                    @endif

                    <hr>

                    @if(Route::has('marketing.menu-terlaris') && hasPermission('marketing.menu-terlaris'))
                        <a href="{{ route('marketing.menu-terlaris') }}" class="{{ request()->routeIs('marketing.menu-terlaris') ? 'active' : '' }}">Menu Terlaris</a>
                    @endif
                    @if(Route::has('marketing.produk-baru') && hasPermission('marketing.produk-baru'))
                        <a href="{{ route('marketing.produk-baru') }}" class="{{ request()->routeIs('marketing.produk-baru') ? 'active' : '' }}">Produk Baru</a>
                    @endif
                    @if(Route::has('marketing.area_potensi'))
                        <a href="{{ route('marketing.area_potensi') }}" class="{{ request()->routeIs('marketing.area_potensi') ? 'active' : '' }}">Area Potensi</a>
                    @endif

                    <hr>

                    @if(Route::has('marketing.content-posting') && hasPermission('marketing.content-posting'))
                        <a href="{{ route('marketing.content-posting') }}" class="{{ request()->routeIs('marketing.content-posting') ? 'active' : '' }}">Content Posting</a>
                    @endif
                    <a href="{{ route('marketing.brand24.index') }}" class="{{ request()->routeIs('marketing.brand24.*') ? 'active' : '' }}">
                        <i class="bi bi-radar text-primary me-1"></i> Brand 24
                    </a>
                </div>
            </div>
        @endif


        @if($showLaporan)
            <div class="nav-section">Laporan</div>

            <div x-data="{ open: {{ $laporanOpen ? 'true' : 'false' }} }">
                <button type="button" class="side-toggle" @click="open = !open">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Laporan</span>
                    <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                </button>

                <div class="side-child" x-show="open" x-collapse>
                    @if($can('investor.laporan.perbulan'))
                        <a href="{{ route('investor.laporan.perbulan') }}">Penjualan per Bulan</a>
                    @endif

                    @if(Route::has('investor.laporan.pertahun') && $can('investor.laporan.pertahun'))
                        <a href="{{ route('investor.laporan.pertahun') }}">Penjualan per Tahun</a>
                    @endif

                    @if($can('investor.laporan.menu'))
                        <a href="{{ route('investor.laporan.menu') }}">Penjualan per Menu</a>
                    @endif

                    @if(Route::has('laporan.laporanDSC') && $can('laporan.laporanDSC'))
                        <a href="{{ route('laporan.laporanDSC') }}">Daily Stock Control</a>
                    @endif

                    @if(Route::has('laporan.laporanExpense') && $can('laporan.laporanExpense'))
                        <a href="{{ route('laporan.laporanExpense') }}">Expense POSLite</a>
                    @endif

                    @if(Route::has('investor.laporan.profitnloss') && $can('investor.laporan.profitnloss'))
                        <a href="{{ route('investor.laporan.profitnloss') }}">Profit & Loss</a>
                    @endif

                    @if(Route::has('investor.laporan.profitnloss.oknho') && $can('investor.laporan.profitnloss.oknho'))
                        <a href="{{ route('investor.laporan.profitnloss.oknho') }}">Profit & Loss Internal</a>
                    @endif

                    @if(Route::has('undian.undianReport') && $can('undian.undianReport'))
                        <a href="{{ route('undian.undianReport') }}">Undian Berhadiah</a>
                    @endif
                </div>
            </div>
        @endif

        @if($can('ticketing.dashboard'))
            <div class="nav-section">Ticketing</div>

            <a href="{{ route('ticketing.dashboard') }}"
               class="side-link {{ request()->routeIs('ticketing.dashboard') ? 'active' : '' }}">
                <i class="bi bi-ticket-detailed"></i>
                <span>Dashboard Ticketing</span>
            </a>
        @endif

        @auth
            @if($showInventory)
                <div class="nav-section">Inventory</div>

                <div x-data="{ open: {{ $inventoryOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-box-seam"></i>
                        <span>Inventory Operasional</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        @if($can('master.qcr.index'))
                            <a href="{{ route('master.qcr.index') }}">Quality Cost Report (QCR)</a>
                        @endif

                        @if($can('master.dsc.index'))
                            <a href="{{ route('master.dsc.index') }}">Daily Stock Control (Report)</a>
                        @endif

                        @if($can('master.dscFormulir.index'))
                            <a href="{{ route('master.dscFormulir.index') }}">Daily Stock Control (Formulir)</a>
                        @endif
                    </div>
                </div>
            @endif

            @if($showAuditor)
                <div x-data="{ open: {{ $auditorOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-shield-check"></i>
                        <span>Auditor Backoffice</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        @if($can('dashboard.harian'))
                            <a href="{{ route('dashboard.harian') }}">Backoffice Auditor</a>
                        @endif
                    </div>
                </div>
            @endif

            @if($showPurchasing || $showSetupScm || $showSimple || $showPoSo || $showPurchasingReport)
                <div class="nav-section">Purchasing</div>
            @endif

            @if($showPurchasing)
                <div x-data="{ open: {{ $purchasingOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-cart-check"></i>
                        <span>Purchasing</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        @if($can('purchasing.dashboardOutlet'))
                            <a href="{{ route('purchasing.dashboardOutlet') }}">Dashboard Outlet</a>
                        @endif

                        @if($can('purchasing.dashboardSCM'))
                            <a href="{{ route('purchasing.dashboardSCM') }}">Dashboard SCM</a>
                        @endif

                        @if($can('purchasing.stockControl'))
                            <a href="{{ route('purchasing.stockControl') }}">Stock DC</a>
                        @endif

                        @if($can('scm.pengiriman.index'))
                            <a href="{{ route('scm.pengiriman.index') }}">Order List</a>
                        @endif

                        @if($can('scm.surat-jalan.index'))
                            <a href="{{ route('scm.surat-jalan.index') }}">Daftar Surat Jalan</a>
                        @endif
                    </div>
                </div>
            @endif

            @if($showSetupScm)
                <div x-data="{ open: {{ $setupScmOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-diagram-3"></i>
                        <span>Setup SCM</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        @if($can('supplier.index'))
                            <a href="{{ route('supplier.index') }}">Daftar Supplier</a>
                        @endif

                        @if($can('customers.index'))
                            <a href="{{ route('customers.index') }}">Daftar Customer</a>
                        @endif

                        @if($can('scm.index-bahan'))
                            <a href="{{ route('scm.index-bahan') }}">Data Produk SCM</a>
                        @endif

                        @if($can('purchasing.armadaList'))
                            <a href="{{ route('purchasing.armadaList') }}">Daftar Armada</a>
                        @endif

                        @if($can('purchasing.listDistributor'))
                            <a href="{{ route('purchasing.listDistributor') }}">Daftar DC</a>
                        @endif

                        @if($can('purchasing.driverList'))
                            <a href="{{ route('purchasing.driverList') }}">Daftar Supir</a>
                        @endif

                        @if($can('purchasing.unitList'))
                            <a href="{{ route('purchasing.unitList') }}">Daftar Unit</a>
                        @endif

                        @if($can('admin.mapping.index'))
                            <a href="{{ route('admin.mapping.index') }}">Outlet Mapping</a>
                        @endif

                        @if($can('outlet.mapping.supplier'))
                            <a href="{{ route('outlet.mapping.supplier') }}">Supplier Mapping</a>
                        @endif

                        @if($can('scm.area_mapping.index'))
                            <a href="{{ route('scm.area_mapping.index') }}">Area Mapping</a>
                        @endif
                    </div>
                </div>
            @endif

            @if($showSimple)
                <div x-data="{ open: {{ $simpleOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-lightning-charge"></i>
                        <span>Simples</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        @if($can('simple-sales.index'))
                            <a href="{{ route('simple-sales.index') }}">Simple Sales</a>
                        @endif

                        @if($can('simple-transfer.index'))
                            <a href="{{ route('simple-transfer.index') }}">Simple Transfer</a>
                        @endif

                        @if($can('simple-purchase.index'))
                            <a href="{{ route('simple-purchase.index') }}">Simple Purchase</a>
                        @endif
                    </div>
                </div>
            @endif

            @if($showPoSo)
                <div x-data="{ open: {{ $poSoOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-receipt-cutoff"></i>
                        <span>PO-SO Integrated</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        @if($can('purchase-order.index'))
                            <a href="{{ route('purchase-order.index') }}">Purchase Order</a>
                        @endif

                        @if($can('sales-order.index'))
                            <a href="{{ route('sales-order.index') }}">Sales Order</a>
                        @endif

                        @if($can('goods-delivery.index'))
                            <a href="{{ route('goods-delivery.index') }}">Goods Delivery</a>
                        @endif

                        @if($can('goods-receipt.index'))
                            <a href="{{ route('goods-receipt.index') }}">Goods Receipt</a>
                        @endif

                        @if($can('sales-invoice.index'))
                            <a href="{{ route('sales-invoice.index') }}">Sales Invoice</a>
                        @endif

                        @if($can('purchase-invoice.index'))
                            <a href="{{ route('purchase-invoice.index') }}">Purchase Invoice</a>
                        @endif
                    </div>
                </div>
            @endif

            @if($showPurchasingReport)
                <div x-data="{ open: {{ $reportOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Report</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        @if($can('scm.history-po'))
                            <a href="{{ route('scm.history-po') }}">Outlet PO History</a>
                        @endif

                        @if($can('reports.stock.movement'))
                            <a href="{{ route('reports.stock.movement') }}">Stock Movement Report</a>
                        @endif

                        @if($can('reports.stock.opname'))
                            <a href="{{ route('reports.stock.opname') }}">Stock Opname Report</a>
                        @endif

                        @if($can('reports.gr.recap'))
                            <a href="{{ route('reports.gr.recap') }}">Goods Receipt Recapitulation Report</a>
                        @endif

                        @if($can('reports.gd.recap'))
                            <a href="{{ route('reports.gd.recap') }}">Goods Delivery Recapitulation Report</a>
                        @endif
                    </div>
                </div>
            @endif

            @if($showMasterData)
                <div class="nav-section">AI & Master</div>

                @if($can('master.surveyor.index'))
                    <a href="{{ route('investor.surveyor.site-score.index') }}"
                       class="side-link {{ request()->routeIs('investor.surveyor.site-score.*') ? 'active' : '' }}">
                        <i class="bi bi-robot"></i>
                        <span>Surveyor</span>
                    </a>
                @endif

                <div x-data="{ open: {{ $masterDataOpen ? 'true' : 'false' }} }">
                    <button type="button" class="side-toggle" @click="open = !open">
                        <i class="bi bi-database-fill-gear"></i>
                        <span>Master Data</span>
                        <i class="bi bi-chevron-right side-chevron" :style="open ? 'transform: rotate(90deg)' : ''"></i>
                    </button>

                    <div class="side-child" x-show="open" x-collapse>
                        @if(Route::has('permissions.index') && $can('permissions.index'))
                            <a href="{{ route('permissions.index') }}">Hak Akses Role</a>
                        @endif

                        @if($can('investor.user.master'))
                            <a href="{{ route('investor.user.master') }}">Users Geprekinaja</a>
                        @endif

                        @if($can('investor.user.operasional'))
                            <a href="{{ route('investor.user.operasional') }}">Users Operasional</a>
                        @endif

                        @if($can('investor.user.all'))
                            <a href="{{ route('investor.user.all') }}">Users All</a>
                        @endif

                        @if($can('investor.master'))
                            <a href="{{ route('investor.master') }}">Mitra Investor</a>
                        @endif

                        @if($can('investor.outlet.master'))
                            <a href="{{ route('investor.outlet.master') }}">Data Outlet</a>
                        @endif

                        @if($can('investor.outletMatchAPI.master'))
                            <a href="{{ route('investor.outletMatchAPI.master') }}">Match Outlet API</a>
                        @endif

                        @if($can('investor.SummaryDetailTransaksi.form'))
                            <a href="{{ route('investor.SummaryDetailTransaksi.form') }}">Summary Transaksi</a>
                        @endif

                        @if($can('investor.area.master'))
                            <a href="{{ route('investor.area.master') }}">Area Teritorial</a>
                        @endif

                        @if($can('master.qcr.dataqcr'))
                            <a href="{{ route('master.qcr.dataqcr') }}">Data Stock & DSC OPS</a>
                        @endif
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
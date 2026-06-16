<div class="score-layout">
    <div class="left-form-panel">
        
        <ul class="nav nav-tabs mb-3 shadow-sm rounded-top" id="siteScoreTabs" role="tablist" style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; padding-top: 8px; padding-left: 8px;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold px-4 py-2" id="feasibility-tab" data-bs-toggle="tab" data-bs-target="#feasibility-content" type="button" role="tab" style="color: #475569; border: none; border-bottom: 3px solid transparent;">
                    <i class="bi bi-graph-up-arrow me-2"></i>Feasibility Study
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold px-4 py-2" id="rab-tab" data-bs-toggle="tab" data-bs-target="#rab-content" type="button" role="tab" style="color: #475569; border: none; border-bottom: 3px solid transparent;">
                    <i class="bi bi-calculator me-2"></i>RAB & Kesimpulan
                </button>
            </li>
        </ul>

        <div class="tab-content" id="siteScoreTabsContent">
            <!-- TAB 1: Feasibility Study -->
            <div class="tab-pane fade show active" id="feasibility-content" role="tabpanel" tabindex="0">
                @include('Surveyor.create.partials._konfigurasi_bobot')
                @include('Surveyor.create.partials._informasi_dasar')
                @include('Surveyor.create.partials._traffic_motor')
                @include('Surveyor.create.partials._traffic_pejalan')
                @include('Surveyor.create.partials._fisik_bangunan')
            </div>

            <!-- TAB 2: RAB & Kesimpulan -->
            <div class="tab-pane fade" id="rab-content" role="tabpanel" tabindex="0">
                @include('Surveyor.create.partials._rab_and_kesimpulan')
            </div>
        </div>
    </div>

    @include('Surveyor.create.partials._right_panel')
</div>

<style>
    .nav-tabs .nav-link.active {
        color: #2563eb !important;
        border-bottom: 3px solid #2563eb !important;
        background-color: transparent !important;
    }
    .nav-tabs .nav-link:hover:not(.active) {
        color: #3b82f6 !important;
        border-bottom: 3px solid #cbd5e1 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const feasibilityTab = document.getElementById('feasibility-tab');
    const rabTab = document.getElementById('rab-tab');
    const panelSiteScore = document.getElementById('right-panel-sitescore');
    const panelRab = document.getElementById('right-panel-rab');

    if (feasibilityTab && rabTab) {
        feasibilityTab.addEventListener('shown.bs.tab', function (e) {
            panelSiteScore.style.display = 'block';
            panelRab.style.display = 'none';
        });

        rabTab.addEventListener('shown.bs.tab', function (e) {
            panelSiteScore.style.display = 'none';
            panelRab.style.display = 'block';
            if(typeof initFeasibility === 'function') initFeasibility();
        });
    }
});
</script>
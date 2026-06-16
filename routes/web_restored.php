<?php

/*
|--------------------------------------------------------------------------
| WEB ROUTES - FULL ROUTE + INVENTORY URL CLEANUP
|--------------------------------------------------------------------------
| Catatan penting:
| - Route name TIDAK diubah, jadi Blade yang memakai route('master.qcr.dataqcr')
|   dan route name lama tetap aman.
| - Yang dirapikan hanya URL/path agar modul Inventory tidak campur dengan /master.
| - URL lama tetap disediakan sebagai redirect agar link lama/bookmark tidak langsung rusak.
|
| Contoh:
| - Lama: /master/qcrdata
| - Baru: /inventory/qcr/data
| - Route name tetap: master.qcr.dataqcr
|
| Setelah replace file ini jalankan:
|   php artisan optimize:clear
|   php artisan route:clear
|   php artisan config:clear
|   php artisan cache:clear
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| WEB ROUTES - PERMISSION FRIENDLY VERSION
|--------------------------------------------------------------------------
| Perubahan utama:
| 1. Group yang sebelumnya auth + CheckSuperAdmin diganti menjadi auth saja
|    supaya role biasa bisa masuk jika permission-nya dicentang.
| 2. Akses dibatasi lewat middleware permission:<route_name>.
| 3. Superadmin tetap lolos otomatis lewat App\Http\Middleware\CheckPermission.
| 4. Setelah replace file ini, jalankan:
|       php artisan optimize:clear
|       php artisan route:clear
|       php artisan config:clear
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestorReportController;
// |--------------------------------------------------------------------------
// | Catatan penting:
// | - Route name TIDAK diubah, jadi Blade yang memakai route('master.qcr.dataqcr')
// |   dan route name lama tetap aman.
// | - Yang dirapikan hanya URL/path agar modul Inventory tidak campur dengan /master.
// | - URL lama tetap disediakan sebagai redirect agar link lama/bookmark tidak langsung rusak.
// |
// | Contoh:
// | - Lama: /master/qcrdata
// | - Baru: /inventory/qcr/data
// | - Route name tetap: master.qcr.dataqcr
// |
// | Setelah replace file ini jalankan:
// |   php artisan optimize:clear
// |   php artisan route:clear
// |   php artisan config:clear
// |   php artisan cache:clear
// |--------------------------------------------------------------------------
// */

/*
|--------------------------------------------------------------------------
| WEB ROUTES - PERMISSION FRIENDLY VERSION
use App\Http\Controllers\SurveyorSiteScoreController;
use App\Http\Controllers\SurveyorVideoDetectionController;
use App\Http\Controllers\TelegramSiteScoreController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MarketingController;
use App\Http\Middleware\IsHospaceAdmin;
// use App\Http\Middleware\IsSCM;

use App\Services\EsbPurchaseService;

/*
|--------------------------------------------------------------------------
| PATCH NOTE - DSC OMSET SAVE SHIFT 1 / SHIFT 2
|--------------------------------------------------------------------------
| Route tombol Simpan Shift 1 dan Simpan Shift 2 memakai:
| - master.dscOmset.save
|
| URL utama:
| - POST /inventory/dsc/omset/save
|
| Fallback URL lama:
| - POST /master/dsc/omset/save
|
| Fallback POST wajib ada karena Route::redirect tidak aman untuk request POST/AJAX.
|--------------------------------------------------------------------------
*/

// Route::get('/', function () {
//     return view('welcome');
// });

/*
|--------------------------------------------------------------------------
| CUSTOM ROLE PERMISSION
|--------------------------------------------------------------------------
| File ini sudah ditambahkan middleware permission:xxx untuk route menu utama.
| Pastikan alias middleware 'permission' sudah didaftarkan di bootstrap/app.php
| dan tabel role_permissions sudah berisi permission yang sesuai.
|
| Superadmin tetap lolos otomatis lewat CheckPermission.
|--------------------------------------------------------------------------
*/

// Dashboard Investor
Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/auth/login/investor', [AuthController::class, 'prosesLogin'])
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestorReportController;
use App\Http\Controllers\InvestorSalesController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\MasterInvestorController;
use App\Http\Controllers\QCRController;
use App\Http\Controllers\RollateController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SurveyorController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SCMController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomAuthController;
use App\Http\Middleware\AuditAuth;
use App\Http\Middleware\CheckInvestorOrSuperAdmin;
use App\Http\Middleware\CheckSuperAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckInventoryAccess;
use App\Http\Controllers\CrewMenuController;
use App\Http\Controllers\TicketController;
use App\Http\Middleware\CheckRolePurchase;
use App\Http\Controllers\SurveyorCandidateLocationController;
use App\Http\Controllers\SurveyorSiteScoreController;
use App\Http\Controllers\SurveyorVideoDetectionController;
use App\Http\Controllers\TelegramSiteScoreController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\MarketingController;
use App\Http\Middleware\IsHospaceAdmin;
Route::post('/user/update-outlet', [MasterInvestorController::class, 'updateOutletTesting'])->name('user.updateOutlet');

// Data Menu Laporan Investor
Route::middleware(['auth'])->group(function () {
    Route::get('/investor/auth/user/investor', [AuthController::class, 'userInvestor'])->name('investor.user.auth');
    Route::get('/', [InvestorSalesController::class, 'index'])
        ->middleware('permission:investor.sales.dashboard')
        ->name('investor.sales.dashboard');
    Route::get('/dashboard/grandopening', [InvestorSalesController::class, 'indexGO'])
        ->middleware('permission:investor.sales.dashboardGO')
        ->name('investor.sales.dashboardGO');

    Route::get('/laporan/investor/perbulan', [InvestorReportController::class, 'laporanPerbulan'])
        ->middleware('permission:investor.laporan.perbulan')
        ->name('investor.laporan.perbulan');
    Route::get('/laporan/investor/perbulan/export', [InvestorReportController::class, 'laporanPerbulanExport'])->name('investor.laporan.perbulan.export');
    Route::get('/laporan/investor/perbulan/pdf', [InvestorReportController::class, 'laporanPerbulanPDF'])->name('investor.laporan.perbulan.pdf');

    Route::get('/laporan/investor/pertahun', [InvestorReportController::class, 'laporanPertahun'])
        ->middleware('permission:investor.laporan.pertahun')
        ->name('investor.laporan.pertahun');
    Route::get('/laporan/investor/pertahun/export', [InvestorReportController::class, 'laporanPertahunExport'])->name('investor.laporan.pertahun.export');
    Route::get('/laporan/investor/pertahun/pdf', [InvestorReportController::class, 'laporanPertahunPDF'])->name('investor.laporan.pertahun.pdf');

    Route::get('/laporan/investor/perhari/menu', [InvestorReportController::class, 'laporanMenu'])
        ->middleware('permission:investor.laporan.menu')
        ->name('investor.laporan.menu');
    Route::get('/laporan/investor/perhari/menu/export', [InvestorReportController::class, 'laporanMenuExport'])->name('investor.laporan.menu.export');

    // Route::get('/laporan/investor/diskon', [InvestorReportController::class, 'laporanDiskonOutlet'])->name('investor.laporan.diskon');
    Route::get('/laporan/investor/profitnloss', [InvestorReportController::class, 'laporanPNLOutlet'])
        ->middleware('permission:investor.laporan.profitnloss')
        ->name('investor.laporan.profitnloss');
    Route::get('/laporan/daily-stock-control', [InvestorReportController::class, 'indexDSC'])
        ->middleware('permission:dsc.laporan.dailystockcontrol')
        ->name('dsc.laporan.dailystockcontrol');

    Route::get('/laporan/investor/profitloss/oknho', [InvestorReportController::class, 'laporanPNLHo'])
        ->name('investor.laporan.profitnloss.oknho');
/*
|--------------------------------------------------------------------------
| CUSTOM ROLE PERMISSION
|--------------------------------------------------------------------------
| File ini sudah ditambahkan middleware permission:xxx untuk route menu utama.
| Pastikan alias middleware 'permission' sudah didaftarkan di bootstrap/app.php
| dan tabel role_permissions sudah berisi permission yang sesuai.
|
| Superadmin tetap lolos otomatis lewat CheckPermission.
|--------------------------------------------------------------------------
*/
    Route::get('/sync-esb/{date}', [InvestorReportController::class, 'dispatchAll']);
    Route::get('/test-sync-esb', [InvestorReportController::class, 'testSyncOne']);

    Route::get('/laporan/expense-Poslite', [InvestorReportController::class, 'indexExpensePoslite'])
        ->middleware('permission:laporan.laporanExpense')
        ->name('laporan.laporanExpense');
    Route::post('/laporan/expense-Poslite/import', [InvestorReportController::class, 'importExpensePoslite'])->name('laporan.expensePoslite.import');
});

// Data Master Mitra Investor dan Outlet
Route::get('/inventory/qcr/export', [QCRController::class, 'exportQcr'])
    ->middleware(['auth', 'permission:master.qcr.export'])
    ->name('master.qcr.export');
Route::middleware(['auth'])->group(function () {
    // endpoint JSON untuk modal
    Route::get('/investor/mitra-json', [InvestorSalesController::class, 'mitraJson'])->name('investor.sales.mitraJson');
    Route::get('/investor/outlet-json', [InvestorSalesController::class, 'outletJson'])->name('investor.sales.outletJson');
    ->name('password.sendOtp');
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyResetOtp'])->name('password.verifyOtp');
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('password.reset.custom');

Route::middleware(['auth'])->group(function () {
    Route::get('/crew-menus', [CrewMenuController::class, 'index'])->name('crew.menus');
    Route::post('/crew-menus/formulir', [CrewMenuController::class, 'goFormulir'])->name('crew.menus.formulir');
    Route::post('/crew-menus/daily-checklist', [CrewMenuController::class, 'goDailyChecklist'])->name('crew.menus.daily');
    Route::post('/crew-menus/formPO', [CrewMenuController::class, 'goPurchaseOrder'])->name('crew.menus.formPO');
    Route::get('/crew/profile', [CrewMenuController::class, 'showProfileForm'])->name('crew.profile.form');
    Route::post('/crew/profile', [CrewMenuController::class, 'updateProfile'])->name('crew.profile.update');

    Route::get('/crew/password', [CrewMenuController::class, 'showPasswordForm'])->name('crew.password.form');
    Route::post('/crew/password', [CrewMenuController::class, 'updatePassword'])->name('crew.password.update');
    Route::get('/auditDashboard', [AuditController::class, 'auditDashboard'])->name('auditDashboard.index');
});

Route::get('/master/bulk-user-outlet-mapping', [MasterInvestorController::class, 'index'])
    ->name('master.bulk_user_outlet_mapping');

Route::post('/master/bulk-user-outlet-mapping/save', [MasterInvestorController::class, 'save'])
    ->name('master.bulk_user_outlet_mapping.save');
Route::post('/user/update-outlet', [MasterInvestorController::class, 'updateOutletTesting'])->name('user.updateOutlet');

// Data Menu Laporan Investor
Route::middleware(['auth'])->group(function () {
    Route::get('/investor/auth/user/investor', [AuthController::class, 'userInvestor'])->name('investor.user.auth');
    // Surveyor AI
    Route::get('/master/surveyor/', [SurveyorController::class, 'index'])->name('master.surveyor.index');
    Route::get('/analyze-location', [SurveyorController::class, 'analyzeLocation'])->name('analyze.location');
    // Route::get('/inventory/qcr/data/', [QCRController::class, 'dataqcr'])->name('master.qcr.dataqcr');
    // Route::post('/inventory/stock/import', [QCRController::class, 'import'])->name('InventoryStock.import');

    // Route::get('/undian/berhadiah', [RollateController::class, 'index'])->name('rollate.spin.berhadiah');
    // Route::get('/undian/pendaftaran', [RollateController::class, 'pendaftaran'])->name('rollate.spin.pendaftaran');
        ->middleware('permission:investor.laporan.perbulan')
        ->name('investor.laporan.perbulan');
    Route::get('/laporan/investor/perbulan/export', [InvestorReportController::class, 'laporanPerbulanExport'])->name('investor.laporan.perbulan.export');
    Route::get('/laporan/investor/perbulan/pdf', [InvestorReportController::class, 'laporanPerbulanPDF'])->name('investor.laporan.perbulan.pdf');
    Route::get('/laporan/undian/export-excel', [LaporanController::class, 'undianExportExcel'])->name('laporan.undianExportExcel');
    Route::delete('/laporan/undian/destroy', [LaporanController::class, 'undianDestroy'])->name('laporan.undianDestroy');



    // DATA MASTER OUTLET
    Route::get('/investor/master/outlet/', [MasterInvestorController::class, 'outlet'])->name('investor.outlet.master');
    Route::post('/master/outlet/update', [MasterInvestorController::class, 'updateOutlet'])->name('outlet.master.update');

    Route::get('/laporan/investor/perhari/menu', [InvestorReportController::class, 'laporanMenu'])
        ->middleware('permission:investor.laporan.menu')
        ->name('investor.laporan.menu');
    Route::get('/laporan/investor/perhari/menu/export', [InvestorReportController::class, 'laporanMenuExport'])->name('investor.laporan.menu.export');

    // Route::get('/laporan/investor/diskon', [InvestorReportController::class, 'laporanDiskonOutlet'])->name('investor.laporan.diskon');
    Route::get('/laporan/investor/profitnloss', [InvestorReportController::class, 'laporanPNLOutlet'])
        ->middleware('permission:investor.laporan.profitnloss')
        ->name('investor.laporan.profitnloss');
    Route::get('/laporan/daily-stock-control', [InvestorReportController::class, 'indexDSC'])
        ->middleware('permission:dsc.laporan.dailystockcontrol')
        ->name('dsc.laporan.dailystockcontrol');

    Route::get('/laporan/investor/profitloss/oknho', [InvestorReportController::class, 'laporanPNLHo'])
        ->name('investor.laporan.profitnloss.oknho');

    Route::post('/laporan/investor/profitloss/oknho/start-sync', [InvestorReportController::class, 'startSyncPnlHo'])
        ->name('investor.laporan.profitnloss.oknho.start-sync');

    Route::get('/laporan/investor/profitloss/oknho/status/{key}', [InvestorReportController::class, 'syncPnlHoStatus'])
        ->name('investor.laporan.profitnloss.oknho.status');
    Route::get('/investor/laporan/profitnloss/oknho/data', [InvestorReportController::class, 'laporanPNLHoData'])->name('investor.laporan.profitnloss.oknho.data');

    Route::get('/test-login-esb', [InvestorReportController::class, 'testLoginEsb']);
    Route::get('/login-all-esb', [InvestorReportController::class, 'loginAllEsb']);
    Route::get('/test-sync-ledger/all', [InvestorReportController::class, 'testSyncLedgerAllBranches']);
    Route::get('/sync-esb/{date}', [InvestorReportController::class, 'dispatchAll']);
    Route::get('/test-sync-esb', [InvestorReportController::class, 'testSyncOne']);

    Route::get('/laporan/expense-Poslite', [InvestorReportController::class, 'indexExpensePoslite'])
        ->middleware('permission:laporan.laporanExpense')
        ->name('laporan.laporanExpense');
    Route::post('/laporan/expense-Poslite/import', [InvestorReportController::class, 'importExpensePoslite'])->name('laporan.expensePoslite.import');
});

// Data Master Mitra Investor dan Outlet
Route::get('/inventory/qcr/export', [QCRController::class, 'exportQcr'])
    ->middleware(['auth', 'permission:master.qcr.export'])
    ->name('master.qcr.export');
Route::middleware(['auth'])->group(function () {
    // endpoint JSON untuk modal
    Route::get('/investor/mitra-json', [InvestorSalesController::class, 'mitraJson'])->name('investor.sales.mitraJson');
    Route::get('/investor/outlet-json', [InvestorSalesController::class, 'outletJson'])->name('investor.sales.outletJson');

    Route::get('/master/investor/', [MasterInvestorController::class, 'investor'])->name('investor.master');
    Route::post('/master/update', [MasterInvestorController::class, 'update'])->name('investor.master.update');
    Route::post('/master/store', [MasterInvestorController::class, 'storeMitra'])->name('investor.master.storeMitra');
    Route::delete('/master/delete/{id}', [MasterInvestorController::class, 'destroy'])->name('investor.master.delete');

    Route::post('/investor/user/operasional/update/{id}', [MasterInvestorController::class, 'updateUserOperasional'])->name('investor.user.operasional.update');
    Route::delete('/investor/user/operasional/delete/{id}', [MasterInvestorController::class, 'destroyUserOperasional'])->name('investor.user.operasional.delete');

        // ===================== DATA MASTER ALL USERS =====================
        Route::get('/investor/user/all-users', [MasterInvestorController::class, 'allUsers'])
            ->name('investor.user.all');

        Route::get('/investor/user/all-users/data', [MasterInvestorController::class, 'allUsersData'])
            ->name('investor.user.all.data');

        Route::post('/investor/user/all-users/store', [MasterInvestorController::class, 'storeAllUser'])
            ->name('investor.user.all.store');

        Route::post('/investor/user/all-users/update/{id}', [MasterInvestorController::class, 'updateAllUser'])
            ->name('investor.user.all.update');

        Route::delete('/investor/user/all-users/delete/{id}', [MasterInvestorController::class, 'destroyAllUser'])
            ->name('investor.user.all.delete');
    // DATA INTERNAL AUDIT
    // Route::get('/investor/internal/audit', [MasterInvestorController::class, 'dataInternalAudit'])->name('investor.internal.audit.master');
    // Route::post('/internal/audit/store', [MasterInvestorController::class, 'storeInternalAudit'])->name('investor.internal.audit.store');
    /*
     |--------------------------------------------------------------------------
     | FIX ROUTE LAPORAN DSC
     |--------------------------------------------------------------------------
     | View resources/views/Investor/Laporan/laporanDailyStockControl.blade.php
     | memanggil:
     | - route('laporan.laporanDSC.data')
     | - route('laporan.laporanDSC.export')
     |
     | Sebelumnya route name ini belum ada sehingga muncul:
     | Route [laporan.laporanDSC.data] not defined.
     */
    Route::get('/laporan/laporanDSC/data', [LaporanController::class, 'laporanDSCData'])->name('laporan.laporanDSC.data');
    Route::get('/laporan/laporanDSC/export', [LaporanController::class, 'laporanDSCExport'])->name('laporan.laporanDSC.export');

    // Surveyor AI
    Route::get('/master/surveyor/', [SurveyorController::class, 'index'])->name('master.surveyor.index');
    Route::get('/analyze-location', [SurveyorController::class, 'analyzeLocation'])->name('analyze.location');
    // Route::get('/inventory/qcr/data/', [QCRController::class, 'dataqcr'])->name('master.qcr.dataqcr');
    // Route::post('/inventory/stock/import', [QCRController::class, 'import'])->name('InventoryStock.import');

    // Route::get('/undian/berhadiah', [RollateController::class, 'index'])->name('rollate.spin.berhadiah');
    // Route::get('/undian/pendaftaran', [RollateController::class, 'pendaftaran'])->name('rollate.spin.pendaftaran');
    Route::post('/undian/store', [RollateController::class, 'store'])->name('rollate.spin.store');
    Route::get('/undian/cetak/{id}', [RollateController::class, 'cetakPDF'])->name('undian.cetak');
    Route::post('/dummy/insert', [RollateController::class, 'insert'])->name('dummy.insert');
    Route::get('/undian/report', [LaporanController::class, 'undianReport'])->name('undian.undianReport');
    Route::get('/laporan/undian/export-excel', [LaporanController::class, 'undianExportExcel'])->name('laporan.undianExportExcel');
    Route::delete('/laporan/undian/destroy', [LaporanController::class, 'undianDestroy'])->name('laporan.undianDestroy');



    // DATA MASTER OUTLET
    Route::get('/investor/master/outlet/', [MasterInvestorController::class, 'outlet'])->name('investor.outlet.master');
    Route::post('/master/outlet/update', [MasterInvestorController::class, 'updateOutlet'])->name('outlet.master.update');
    Route::post('/master/outlet/store', [MasterInvestorController::class, 'storeOutlet'])->name('outlet.master.store');
    Route::delete('/master/outlet/delete/{id}', [MasterInvestorController::class, 'destroyOutlet'])->name('outlet.master.delete');
    Route::get('/outlet/template-download', [MasterInvestorController::class, 'downloadTemplate'])->name('outlet.template.download');
    Route::post('/outlet/import', [MasterInvestorController::class, 'importOutlet'])->name('outlet.import');
    Route::post('/outlets/import', [MasterController::class, 'import'])->name('outlets.import');
    // Route::post('/import/preview', [MasterController::class, 'previewImport'])->name('import.preview');
    Route::post('/import/commit', [MasterController::class, 'commitImport'])->name('import.commit');
    Route::post('/sales-data/preview-import', [MasterController::class, 'previewImport'])->name('dataSalesImport.preview');
    Route::post('/sales-data/import', [MasterController::class, 'dataSalesImport'])->name('dataSalesImport.import');
    Route::get('/data-sales/import/status/{key}', [MasterController::class, 'dataSalesImportStatus'])->name('dataSalesImport.status');
    // Migration Branches / Outlets ESB to INTERNAL
    Route::post('/master-investor/outlet/sync-esb', [MasterInvestorController::class, 'startSyncEsbOutlets'])->name('outlet.master.sync.esb');
    Route::get('/master-investor/outlet/sync-esb-status/{key}', [MasterInvestorController::class, 'syncEsbOutletsStatus'])->name('outlet.master.sync.esb.status');
    Route::post('/master-investor/outlet/sync-esb-all', [MasterInvestorController::class, 'startSyncAllEsbOutlets'])->name('outlet.master.sync.esb.all');
    Route::get('/master-investor/outlet/sync-esb-all/status/{key}', [MasterInvestorController::class, 'syncAllEsbOutletsStatus'])->name('outlet.master.sync.esb.all.status');

    Route::get('/investor/master/outletMatchAPI/', [MasterInvestorController::class, 'outletMatchAPI'])->name('investor.outletMatchAPI.master');
    Route::post('/investor/master/outletMatchAPI/update', [MasterInvestorController::class, 'outletMatchAPIUpdate'])->name('investor.outletMatchAPIUpdate.master');

    Route::get('/investor/master/SummaryDetailTransaksi', [MasterInvestorController::class, 'showSummaryDetailTransaksi'])->name('investor.SummaryDetailTransaksi.form');
    Route::post('/investor/master/SummaryDetailTransaksi/update', [MasterInvestorController::class, 'SummaryDetailTransaksi'])->name('investor.SummaryDetailTransaksi.master');

    Route::get('/sales/import/status/{key}', [MasterController::class, 'importStatus'])->name('dataSalesImport.status');
    Route::get('/sales/preview-import/status/{key}', [MasterInvestorController::class, 'previewImportStatus'])->name('dataSalesImport.preview.status');

    // Sales
    // Route::post('/master-investor/outlet/sync-sales-esb', [MasterInvestorController::class, 'startSyncSalesEsb'])
    // ->name('outlet.master.sync.sales.esb');
    Route::get('/master-investor/outlet/sync-sales-esb-status/{key}', [MasterInvestorController::class, 'syncSalesEsbStatus'])->name('outlet.master.sync.sales.esb.status');
    Route::post('/master-investor/outlet/sync-sales-selected', [MasterInvestorController::class, 'startSyncSalesSelected'])->name('outlet.sales.sync.selected');

    Route::post('/master-investor/outlet/sync-sales-esb-all', [MasterInvestorController::class, 'startSyncSalesEsbAll'])->name('outlet.master.sync.sales.esb.all');
    Route::get('/master-investor/outlet/sync-sales-esb-all/status/{key}', [MasterInvestorController::class, 'syncSalesEsbAllStatus'])->name('outlet.master.sync.sales.esb.all.status');

    // DATA MASTER AREA
    Route::get('/investor/master/area/', [MasterInvestorController::class, 'dataArea'])->name('investor.area.master');
    Route::post('/investor/master/area/store', [MasterInvestorController::class, 'storeArea'])->name('outlet.master.store');
    Route::post('/investor/master/area/update', [MasterInvestorController::class, 'updateArea'])->name('outlet.master.update');
    Route::delete('/investor/master/area/{id}', [MasterInvestorController::class, 'deleteArea'])->name('outlet.master.delete');

});

// ===================== SUPERADMIN ONLY =====================
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/bod', [InvestorSalesController::class, 'indexBOD'])->name('investor.sales.dashboardBOD');
    Route::get('/investor/mitra-json', [InvestorSalesController::class, 'mitraJson'])->name('investor.sales.mitraJson');
    Route::get('/investor/outlet-json', [InvestorSalesController::class, 'outletJson'])->name('investor.sales.outletJson');
    // ===================== DATA MASTER OPERASIONAL (CREW/SPV/TM) =====================
    Route::get('/investor/user/operasional', [MasterInvestorController::class, 'userOperasional'])->name('investor.user.operasional');
    Route::post('/investor/user/operasional/store', [MasterInvestorController::class, 'storeUserOperasional'])->name('investor.user.operasional.store');
    Route::post('/investor/user/operasional/update/{id}', [MasterInvestorController::class, 'updateUserOperasional'])->name('investor.user.operasional.update');
    Route::delete('/investor/user/operasional/delete/{id}', [MasterInvestorController::class, 'destroyUserOperasional'])->name('investor.user.operasional.delete');

        // ===================== DATA MASTER ALL USERS =====================
        Route::get('/investor/user/all-users', [MasterInvestorController::class, 'allUsers'])
            ->name('investor.user.all');

        Route::get('/investor/user/all-users/data', [MasterInvestorController::class, 'allUsersData'])
            ->name('investor.user.all.data');

        Route::post('/investor/user/all-users/store', [MasterInvestorController::class, 'storeAllUser'])
            ->name('investor.user.all.store');

        Route::post('/investor/user/all-users/update/{id}', [MasterInvestorController::class, 'updateAllUser'])
            ->name('investor.user.all.update');

        Route::delete('/investor/user/all-users/delete/{id}', [MasterInvestorController::class, 'destroyAllUser'])
            ->name('investor.user.all.delete');
    // DATA INTERNAL AUDIT
    // Route::get('/investor/internal/audit', [MasterInvestorController::class, 'dataInternalAudit'])->name('investor.internal.audit.master');
    // Route::post('/internal/audit/store', [MasterInvestorController::class, 'storeInternalAudit'])->name('investor.internal.audit.store');
    // Route::put('/internal/audit/update/{id}', [MasterInvestorController::class, 'updateInternalAudit'])->name('investor.internal.audit.update');
    // Route::post('/investor/internal/audit/validate', [MasterInvestorController::class, 'validateInternalAudit'])->name('investor.internal.audit.validate');
    // Route::post('/investor/internal/audit/import', [MasterInvestorController::class, 'importInternalAudit'])->name('investor.internal.audit.import');

    // ==========================

    // ---------- EXPORT ----------
    Route::get('/inventory/menu/export',  [QCRController::class, 'exportMenu'])->name('menu.export');
    Route::get('/inventory/bahan/export', [QCRController::class, 'exportBahan'])->name('bahan.export');
    Route::get('/inventory/bum/export',   [QCRController::class, 'exportBum'])->name('bum.export');
    Route::get('/inventory/stock/export', [QCRController::class, 'exportStock'])->name('stock.export'); // ✅ ini yang kamu error

    // ---------- MENU ----------
    Route::post('/inventory/menu/store',         [QCRController::class, 'storeMenu'])->name('menu.store');
    Route::put('/master/menu/update/{id}',   [QCRController::class, 'updateMenu'])->name('menu.update');
    Route::delete('/master/menu/delete/{id}', [QCRController::class, 'destroyMenu'])->name('menu.destroy');

    // ---------- BAHAN ----------
    Route::post('/inventory/bahan/store',         [QCRController::class, 'storeBahan'])->name('bahan.store');
    Route::put('/master/bahan/update/{id}',   [QCRController::class, 'updateBahan'])->name('bahan.update');
    Route::delete('/master/bahan/delete/{id}', [QCRController::class, 'destroyBahan'])->name('bahan.destroy');

    // ---------- BAHAN DSC ----------
    Route::post('/inventory/bahan-dsc/store',          [QCRController::class, 'storeBahanDsc'])->name('bahan-dsc.store');
    Route::put('/master/bahan-dsc/update/{id}',    [QCRController::class, 'updateBahanDsc'])->name('bahan-dsc.update');
    Route::delete('/master/bahan-dsc/delete/{id}', [QCRController::class, 'destroyBahanDsc'])->name('bahan-dsc.destroy');

    // ---------- BOM (BUM) ----------
    Route::post('/inventory/bum/store',            [QCRController::class, 'storeBum'])->name('bum.store');
    Route::post('/inventory/bum/update/{menu_id}', [QCRController::class, 'updateBum'])
    ->name('inventory.bum.update');
    Route::delete('/master/bum/delete/{menu_id}', [QCRController::class, 'destroyBum'])->name('bum.destroy');

    // ✅ dipakai AJAX di modal view/edit kamu:
    // Route::get('/bum/{menu_id}/detail', [QCRController::class, 'getMenuBahan'])->name('bum.detail');
    Route::get('/bum/{menu_id}/detail', [QCRController::class, 'getMenuBahan'])
    ->name('bum.detail');

    Route::get('/inventory/bum/{menu_id}/detail', [QCRController::class, 'getMenuBahan'])
        ->name('inventory.bum.detail');

    // ---------- STOCK ----------
    Route::post('/inventory/stock/store',         [QCRController::class, 'storeStock'])->name('stock.store');
    Route::post('/master/stock/update/{id}',   [QCRController::class, 'updateStock'])->name('stock.update');
    Route::get('/master/stock/{id}/edit',      [QCRController::class, 'editStock'])->name('stock.edit'); // AJAX edit load
    Route::delete('/master/stock/delete/{id}', [QCRController::class, 'destroyStock'])->name('stock.destroy');
    // Daftar Responden
    Route::get('/responden', [AuditController::class, 'daftarResponden'])->name('responden.index');
    Route::post('/responden/store', [AuditController::class, 'respondenStore'])->name('responden.store');
    Route::put('/responden/update/{id}', [AuditController::class, 'respondenUpdate'])->name('responden.update');
    Route::delete('/responden/destroy/{id}', [AuditController::class, 'respondenDestroy'])->name('responden.destroy');

    // Jawaban Responden Kuisioner
// 2) kalau bener-bener mau simpel, cukup auth:
Route::middleware(['auth'])->group(function () {

    Route::post('/inventory/dsc/adjustment/import-preview', [QCRController::class, 'dscAdjustmentImportPreview'])->name('dsc.adjustment.import_preview');
    Route::post('/inventory/dsc/adjustment/apply', [QCRController::class, 'dscAdjustmentApply'])->name('dsc.adjustment.apply');
    // ---------- QCR ----------
    Route::get('/inventory/qcr/', [QCRController::class, 'index'])
        ->middleware('permission:master.qcr.index')
        ->name('master.qcr.index');
    Route::get('/inventory/qcr/data/', [QCRController::class, 'dataqcr'])
        ->middleware('permission:master.qcr.dataqcr')
        ->name('master.qcr.dataqcr');
    Route::post('/inventory/stock/import', [QCRController::class, 'import'])->name('InventoryStock.import');
    Route::get('/inventory/qcr', [QCRController::class, 'index'])
        ->middleware('permission:master.qcr.index')
        ->name('master.qcr.index');
    Route::get('/inventory/qcr/export', [QCRController::class, 'exportQcr'])
        ->middleware('permission:master.qcr.export')
        ->name('master.qcr.export');
    Route::post('/inventory/qcr/hide-items/save', [QCRController::class, 'saveHiddenItems'])
        ->middleware('permission:master.qcr.hide.save')
        ->name('master.qcr.hide.save');
    Route::post('/inventory/qcr/uang-plus/save', [QCRController::class, 'saveUangPlus'])
        ->middleware('permission:master.qcr.uangplus.save')
        ->name('master.qcr.uangplus.save');

    Route::get('/inventory/qcr/outlet-options', [QCRController::class, 'outletOptions']);

    Route::get('/inventory/qcr/bahan-harga-outlet/list', [QCRController::class, 'bahanHargaOutletList']);
    Route::post('/inventory/qcr/bahan-harga-outlet/store-update', [QCRController::class, 'storeOrUpdateBahanHargaOutlet']);
    Route::post('/inventory/qcr/bahan-harga-outlet/bulk-update', [QCRController::class, 'bulkUpdateBahanHargaOutlet']);
    Route::post('/inventory/qcr/bahan-harga-outlet/delete', [QCRController::class, 'deleteBahanHargaOutlet']);

    // ---------- DSC ----------
    Route::get('/inventory/dsc/', [QCRController::class, 'dailyStockControl'])
        ->middleware('permission:master.dsc.index')
        ->name('master.dsc.index');
    Route::get('/inventory/dsc/export', [QCRController::class, 'exportDsc'])
        ->middleware('permission:master.dsc.export')
        ->name('master.dsc.export');
    Route::get('/inventory/dsc/missing', [QCRController::class, 'dscMissingOutlet'])
        ->middleware('permission:master.dsc.missing')
        ->name('master.dsc.missing');
    Route::get('/inventory/dsc/missing/export', [QCRController::class, 'exportDscMissingOutlet'])
        ->middleware('permission:master.dsc.missing.export')
        ->name('master.dsc.missing.export');

    // kalau kamu masih pakai InventoryController endpoints ini
    // Route::post('/dsc/sales/upsert', [InventoryController::class, 'dscSalesUpsert']);

    // Route::post('/dsc/stock/delete-row', [InventoryController::class, 'dscStockDeleteRow']);

    // DSC - FORMULIR STOCK
    Route::get('/inventory/dsc/formulir', [QCRController::class, 'dscFormulir'])
        ->middleware('permission:master.dscFormulir.index')
        ->name('master.dscFormulir.index');
    Route::get('/inventory/dsc/load', [QCRController::class, 'dscLoad'])->name('load');
    Route::post('/inventory/dsc/save-so', [QCRController::class, 'dscSaveSO'])
        ->middleware('throttle:30,1')
        ->name('saveSo');
    Route::post('/inventory/dsc/save-draft', [QCRController::class, 'dscSaveDraft'])
        ->middleware('throttle:60,1')
        ->name('dsc.save-draft');

    Route::get('/inventory/dsc/history', [QCRController::class, 'dscHistory'])
        ->middleware('permission:dsc.history')
        ->name('dsc.history');
    Route::view('/inventory/dsc/guidebook/formulir','Investor.Inventory.guidebook.guideDscFormulir')->name('master.dscGuidebook.formulir');
    Route::view('/inventory/dsc/guidebook/omset-setoran','Investor.Inventory.guidebook.guideOmsetSetoran')->name('master.dscFormulirOmset.guidebook');

    Route::post('/inventory/dsc/close-kasir', [QCRController::class, 'closeKasir'])->name('closeKasir');
    Route::get('/inventory/dsc/close-status', [QCRController::class, 'closeStatus'])->name('dsc.closeStatus');

    Route::post('/inventory/dsc/save-movement', [QCRController::class, 'dscSaveMovement'])->name('saveMovement');
    Route::get('/inventory/dsc/outlets', [QCRController::class, 'dscOutlets'])->name('outlets');
    Route::get('/inventory/dsc/bahan', [QCRController::class, 'dscBahan'])->name('bahan');
    Route::post('/inventory/dsc/import-preview', [QCRController::class, 'dscImportPreview'])->name('importPreview');

    // DSC - FORMULIR OMSET
    Route::get('/inventory/dsc/formulir/omset', [QCRController::class, 'dscFormulirOmset'])
        ->middleware('permission:master.dscFormulirOmset.index')
        ->name('master.dscFormulirOmset.index');

    Route::get('/inventory/dsc/omset/load', [QCRController::class, 'dscOmsetLoad'])
        ->middleware('permission:master.dscFormulirOmset.index')
        ->name('master.dscOmset.load');

    Route::get('/inventory/dsc/omset/load-harian', [QCRController::class, 'dscOmsetLoadHarian'])
        ->middleware('permission:master.dscFormulirOmset.index')
        ->name('master.dscOmset.loadHarian');

    /*
     * Tombol Simpan Shift 1 dan Simpan Shift 2 di dscFormulirOmset.blade.php
     * harus POST ke route name master.dscOmset.save.
     *
     * Permission yang wajib dicentang untuk role yang boleh simpan:
     * - master.dscOmset.save
     */
    Route::post('/inventory/dsc/omset/save', [QCRController::class, 'dscOmsetSave'])
        ->middleware(['permission:master.dscOmset.save', 'throttle:30,1'])
        ->name('master.dscOmset.save');

    /*
     * Simpan final / close setoran omset.
     *
     * Permission yang wajib dicentang:
     * - master.dscOmset.saveFinal
     */
    Route::post('/inventory/dsc/omset/save-final', [QCRController::class, 'dscOmsetSaveFinal'])
        ->middleware(['permission:master.dscOmset.saveFinal', 'throttle:10,1'])
        ->name('master.dscOmset.saveFinal');

    /*
     * LEGACY POST FALLBACK
     * Jangan pakai Route::redirect untuk POST AJAX karena method bisa berubah
     * dan sering berakhir 404/405 ketika JS lama masih hit URL /master.
     *
     * Fallback ini tidak diberi name agar tidak bentrok dengan route utama.
     */
    Route::post('/master/dsc/omset/save', [QCRController::class, 'dscOmsetSave'])
        ->middleware(['permission:master.dscOmset.save', 'throttle:30,1']);

    Route::post('/master/dsc/omset/save-final', [QCRController::class, 'dscOmsetSaveFinal'])
        ->middleware(['permission:master.dscOmset.saveFinal', 'throttle:10,1']);

    Route::get('/master/dsc/omset/load', [QCRController::class, 'dscOmsetLoad'])
        ->middleware('permission:master.dscFormulirOmset.index');

    Route::get('/master/dsc/omset/load-harian', [QCRController::class, 'dscOmsetLoadHarian'])
        ->middleware('permission:master.dscFormulirOmset.index');

    // DSC - IMPORT
    Route::get('/inventory/dsc/import', [QCRController::class, 'dscImport'])
        ->middleware('permission:master.dscImport.index')
        ->name('master.dscImport.index');
    Route::post('/inventory/dsc/import-preview-bulk', [QCRController::class, 'dscImportPreviewBulk'])->name('dsc.import.previewBulk');
    Route::post('/inventory/dsc/import-apply-bulk', [QCRController::class, 'dscImportApplyBulk'])->name('dsc.import.applyBulk');
    Route::delete('/master/destroy/{id}', [QCRController::class, 'destroyStock'])->name('stock.destroy');
});

    Route::put('/master/menu/update/{id}',   [QCRController::class, 'updateMenu'])->name('menu.update');
    Route::delete('/master/menu/delete/{id}', [QCRController::class, 'destroyMenu'])->name('menu.destroy');

    // ---------- BAHAN ----------
    Route::post('/inventory/bahan/store',         [QCRController::class, 'storeBahan'])->name('bahan.store');
    Route::put('/master/bahan/update/{id}',   [QCRController::class, 'updateBahan'])->name('bahan.update');
    Route::delete('/master/bahan/delete/{id}', [QCRController::class, 'destroyBahan'])->name('bahan.destroy');

    // ---------- BAHAN DSC ----------
    Route::post('/inventory/bahan-dsc/store',          [QCRController::class, 'storeBahanDsc'])->name('bahan-dsc.store');
    Route::put('/master/bahan-dsc/update/{id}',    [QCRController::class, 'updateBahanDsc'])->name('bahan-dsc.update');
    Route::delete('/master/bahan-dsc/delete/{id}', [QCRController::class, 'destroyBahanDsc'])->name('bahan-dsc.destroy');

    // ---------- BOM (BUM) ----------

Route::get('/undian/berhadiah', [RollateController::class, 'index'])->name('rollate.spin.berhadiah');
Route::get('/undian/pendaftaran', [RollateController::class, 'pendaftaran'])->name('rollate.spin.pendaftaran');
Route::get('/undian/migrasi', [RollateController::class, 'migrasi'])->name('rollate.migrasi');
Route::post('/undian/migrasi/store', [RollateController::class, 'storeMigrasi'])->name('rollate.migrasi.store');
Route::get('/undian/migrasi/status', [RollateController::class, 'checkStatusMigrasi'])->name('rollate.migrasi.status');
Route::post('/undian/migrasi/update/{id}', [RollateController::class, 'updateMigrasi'])->name('rollate.migrasi.update');
Route::get('/undian/validasi/{nomor}', [RollateController::class, 'validasi'])->name('rollate.validasi');

// DAILY CHECK REPORT - ROUTE
Route::get('/dashboard/harian', [AuditController::class, 'dashboardHarian'])
    ->middleware(['auth', 'permission:dashboard.harian'])
    ->name('dashboard.harian');
Route::get('/dashboard/recap', [AuditController::class, 'dashboardRecap'])
    ->middleware(['auth', 'permission:dashboard.recap'])
    ->name('dashboard.recap');

Route::get('/investor/internal/audit', [AuditController::class, 'dataInternalAudit'])
    ->middleware(['auth', 'permission:investor.internal.audit.master'])
    ->name('investor.internal.audit.master');
Route::post('/internal/audit/store', [AuditController::class, 'storeInternalAudit'])
    ->middleware('throttle:10,1')
    ->name('investor.internal.audit.store');
    // ---------- STOCK ----------
    Route::post('/inventory/stock/store',         [QCRController::class, 'storeStock'])->name('stock.store');
Route::post('/investor/internal/audit/import', [AuditController::class, 'importInternalAudit'])->name('investor.internal.audit.import');

// Laporan
Route::get('/laporan/compliance-recap', [AuditController::class, 'complianceRecap'])
    ->middleware(['auth', 'permission:laporan.compliance_recap'])
    ->name('laporan.compliance_recap');
Route::get('/laporan/ranking-outlet', [AuditController::class, 'rankingOutlet'])
    ->middleware(['auth', 'permission:laporan.ranking_outlet'])
    ->name('laporan.ranking_outlet');
Route::get('/laporan/kumulatif-ranking-pic', [AuditController::class, 'kumulatifRankingPic'])
    ->middleware(['auth', 'permission:laporan.kumulatif_ranking_pic'])
    ->name('laporan.kumulatif_ranking_pic');

// Master - Data Responses
Route::get('/audit/laporan', [AuditController::class, 'laporan'])
    ->middleware(['auth', 'permission:audit.laporan'])
    ->name('audit.laporan');
Route::post('/audit/store', [AuditController::class, 'storeActivity'])->name('audit.store');
Route::put('/audit/update/{id}', [AuditController::class, 'updateActivity'])->name('audit.update');
Route::delete('/audit/delete/{id}', [AuditController::class, 'destroyActivity'])->name('audit.delete');

// Master - Data Pertanyaan
Route::get('/master/data-pertanyaan', [AuditController::class, 'daftarPertanyaan'])
    ->middleware(['auth', 'permission:auditDashboard.daftarKuisioner'])
    ->name('auditDashboard.daftarKuisioner');
Route::post('/daftarKuisioner/store', [AuditController::class, 'store'])->name('auditDashboard.kuisioner.store');
Route::put('/daftarKuisioner/update', [AuditController::class, 'updatePertanyaan'])->name('auditDashboard.kuisioner.update');
Route::delete('/daftarKuisioner/delete/{id}', [AuditController::class, 'destroyPertanyaan'])->name('auditDashboard.kuisioner.delete');

// Master - Data Outlet
Route::get('/master/data-outlet', [AuditController::class, 'dataOutlet'])
    ->middleware(['auth', 'permission:master.data_outlet'])
    ->name('master.data_outlet');

// Master - Data PIC
Route::get('/master/data-pic', [AuditController::class, 'dataPic'])
    ->middleware(['auth', 'permission:master.data_pic'])
    ->name('master.data_pic');
Route::post('/master/data-pic/store', [AuditController::class, 'storePic'])->name('master.data_pic.store');
Route::put('/master/data-pic/update/{id}', [AuditController::class, 'updatePic'])->name('master.data_pic.update');
Route::delete('/master/data-pic/delete/{id}', [AuditController::class, 'destroyPic'])->name('master.data_pic.delete');

// Master - Setting
Route::get('/master/setting', [AuditController::class, 'setting'])
    ->middleware(['auth', 'permission:master.setting'])
    ->name('master.setting');
Route::post('/master/setting/store', [AuditController::class, 'storeSetting'])->name('master.setting.store');
Route::put('/master/setting/update/{id}', [AuditController::class, 'updateSetting'])->name('master.setting.update');
Route::delete('/master/setting/delete/{id}', [AuditController::class, 'destroySetting'])->name('master.setting.delete');
        ->middleware('permission:master.qcr.uangplus.save')
        ->name('master.qcr.uangplus.save');

    Route::get('/inventory/qcr/outlet-options', [QCRController::class, 'outletOptions']);

    Route::get('/inventory/qcr/bahan-harga-outlet/list', [QCRController::class, 'bahanHargaOutletList']);
    Route::post('/inventory/qcr/bahan-harga-outlet/store-update', [QCRController::class, 'storeOrUpdateBahanHargaOutlet']);
    Route::post('/inventory/qcr/bahan-harga-outlet/bulk-update', [QCRController::class, 'bulkUpdateBahanHargaOutlet']);
    Route::post('/inventory/qcr/bahan-harga-outlet/delete', [QCRController::class, 'deleteBahanHargaOutlet']);

    // ---------- DSC ----------
    Route::get('/inventory/dsc/', [QCRController::class, 'dailyStockControl'])
        ->middleware('permission:master.dsc.index')
        ->name('master.dsc.index');
    Route::get('/inventory/dsc/export', [QCRController::class, 'exportDsc'])
        ->middleware('permission:master.dsc.export')
        ->name('master.dsc.export');
Route::get('/dashboard-outlet/po-receive-detail/{id}', [PurchaseController::class, 'getReceiveDetail'])->name('detreceive.store');
Route::post('/dashboard-outlet/return/store', [PurchaseController::class, 'storeReturn'])->name('return.store');

Route::get('/dashboard-outlet', [PurchaseController::class, 'outletFormPO'])
    ->middleware(['auth', 'permission:purchasing.dashboardOutlet'])
    ->name('purchasing.dashboardOutlet');

Route::get('/dashboard-scm', [PurchaseController::class, 'scmDashboard'])
    ->middleware(['auth', 'permission:purchasing.dashboardSCM'])
    ->name('purchasing.dashboardSCM');
Route::post('/dashboard-scm/update-status-po', [PurchaseController::class, 'updateStatusPO'])->name('update.status.po');
Route::get('/dashboard-scm/po-supplier-items/{id}', [PurchaseController::class, 'getPoSupplierItems'])->name('scm.po-supplier-items');
Route::get('/setup-distribution', [PurchaseController::class, 'setupIndex'])->name('purchasing.setupDistribution');
Route::post('/setup-distribution/save', [PurchaseController::class, 'save'])->name('setup.distribusi.save');

Route::get('/stock-control', [PurchaseController::class, 'controlStock'])
    ->middleware(['auth', 'permission:purchasing.stockControl'])
    ->name('purchasing.stockControl');

Route::post('/purchasing/stok/transfer', [PurchaseController::class, 'stockTransfer'])->name('stok.transfer');
Route::post('/purchasing/stok/store', [PurchaseController::class, 'storeStock'])->name('stok.store');

Route::get('/stock-control', [PurchaseController::class, 'controlStock'])
    ->middleware(['auth', 'permission:purchasing.stockControl'])
    ->name('purchasing.stockControl');
Route::get('/list-distributor', [PurchaseController::class, 'distributorList'])
    ->middleware(['auth', 'permission:purchasing.listDistributor'])
    ->name('purchasing.listDistributor');
Route::post('/purchasing/stok/transfer', [PurchaseController::class, 'stockTransfer'])->name('stok.transfer');
Route::post('/purchasing/stok/store', [PurchaseController::class, 'storeStock'])->name('stok.store');

Route::get('/history-purchase-order', [PurchaseController::class, 'historyPO'])
    ->middleware(['auth', 'permission:scm.history-po'])
    ->name('scm.history-po');

Route::middleware(['auth', 'permission:scm.pengiriman.index'])->prefix('scm/pengiriman')->group(function () {

    // 1. Halaman List PO yang Siap Kirim (Pending Delivery)
    Route::get('/', [PurchaseController::class, 'indexPendingDelivery'])->name('scm.pengiriman.index');
    Route::view('/inventory/dsc/guidebook/formulir','Investor.Inventory.guidebook.guideDscFormulir')->name('master.dscGuidebook.formulir');
    Route::view('/inventory/dsc/guidebook/omset-setoran','Investor.Inventory.guidebook.guideOmsetSetoran')->name('master.dscFormulirOmset.guidebook');

    Route::post('/inventory/dsc/close-kasir', [QCRController::class, 'closeKasir'])->name('closeKasir');
    Route::get('/inventory/dsc/close-status', [QCRController::class, 'closeStatus'])->name('dsc.closeStatus');

    Route::post('/inventory/dsc/save-movement', [QCRController::class, 'dscSaveMovement'])->name('saveMovement');
    Route::get('/inventory/dsc/outlets', [QCRController::class, 'dscOutlets'])->name('outlets');
    Route::get('/inventory/dsc/bahan', [QCRController::class, 'dscBahan'])->name('bahan');
    Route::post('/inventory/dsc/import-preview', [QCRController::class, 'dscImportPreview'])->name('importPreview');

    // DSC - FORMULIR OMSET
    Route::get('/inventory/dsc/formulir/omset', [QCRController::class, 'dscFormulirOmset'])
        ->middleware('permission:master.dscFormulirOmset.index')
        ->name('master.dscFormulirOmset.index');

// MAIN
Route::get('/dashboard/bod', [InvestorSalesController::class, 'indexBOD'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD'])
    ->name('investor.sales.dashboardBOD');

Route::get('/dashboard/GO', [InvestorSalesController::class, 'indexGO'])
    ->middleware(['auth', 'permission:investor.sales.dashboardGO'])
    ->name('investor.sales.dashboardGO');

// SALES
Route::get('/dashboard/bod/sales', [InvestorSalesController::class, 'bodSales'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.sales'])
    ->name('investor.sales.dashboardBOD.sales');

Route::get('/dashboard/bod/sales-comparison', [InvestorSalesController::class, 'bodSalesComparison'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.salesComparison'])
    ->name('investor.sales.dashboardBOD.salesComparison');

Route::get('/dashboard/bod/labour-cost', [InvestorSalesController::class, 'bodLabourCost'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.labourCost'])
    ->name('investor.sales.dashboardBOD.labourCost');

// QCR
Route::get('/dashboard/bod/qcr', [InvestorSalesController::class, 'bodQcr'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.qcr'])
    ->name('investor.sales.dashboardBOD.qcr');


// RECRUITMENT
Route::get('/dashboard/bod/recruitment', [InvestorSalesController::class, 'bodRecruitment'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.recruitment'])
    ->name('investor.sales.dashboardBOD.recruitment');

Route::get('/dashboard/bod/timeline-recruitment', [InvestorSalesController::class, 'bodTimelineRecruitment'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.timelineRecruitment'])
    ->name('investor.sales.dashboardBOD.timelineRecruitment');

Route::get('/dashboard/bod/fulfillment-training', [InvestorSalesController::class, 'bodFulfillmentTraining'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.fulfillmentTraining'])
    ->name('investor.sales.dashboardBOD.fulfillmentTraining');

Route::get('/dashboard/bod/retraining-crew', [InvestorSalesController::class, 'bodRetrainingCrew'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.retrainingCrew'])
    ->name('investor.sales.dashboardBOD.retrainingCrew');

Route::get('/dashboard/bod/training-leader', [InvestorSalesController::class, 'bodTrainingLeader'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.trainingLeader'])
    ->name('investor.sales.dashboardBOD.trainingLeader');


// RTO
Route::get('/dashboard/bod/rto', [InvestorSalesController::class, 'bodRto'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.rto'])
    ->name('investor.sales.dashboardBOD.rto');


// KEMITRAAN
Route::get('/dashboard/bod/kemitraan', [InvestorSalesController::class, 'bodKemitraan'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.kemitraan'])
    ->name('investor.sales.dashboardBOD.kemitraan');

Route::get('/dashboard/bod/leads-kemitraan', [InvestorSalesController::class, 'bodLeadsKemitraan'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.leadsKemitraan'])
    ->name('investor.sales.dashboardBOD.leadsKemitraan');


// FINANCE / CONTROL
Route::get('/dashboard/bod/control-budget', [InvestorSalesController::class, 'bodControlBudget'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.controlBudget'])
    ->name('investor.sales.dashboardBOD.controlBudget');


// OPERASIONAL
Route::get('/dashboard/bod/otif', [InvestorSalesController::class, 'bodOtif'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.otif'])
    ->name('investor.sales.dashboardBOD.otif');

Route::get('/dashboard/bod/cro', [InvestorSalesController::class, 'bodCro'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.cro'])
    ->name('investor.sales.dashboardBOD.cro');

Route::get('/dashboard/bod/cs', [InvestorSalesController::class, 'bodCs'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.cs'])
    ->name('investor.sales.dashboardBOD.cs');

Route::get('/dashboard/bod/ecommerce', [InvestorSalesController::class, 'bodEcommerce'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.ecommerce'])
    ->name('investor.sales.dashboardBOD.ecommerce');

Route::get('/dashboard/bod/mapping-market', [InvestorSalesController::class, 'bodMappingMarket'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.mappingMarket'])
    ->name('investor.sales.dashboardBOD.mappingMarket');

//====== SURAT JALAN =====
Route::get('/scm/surat-jalan', [PurchaseController::class, 'indexSuratJalan'])
    ->middleware(['auth', 'permission:scm.surat-jalan.index'])
    ->name('scm.surat-jalan.index'); // ganti route yang sudah ada
Route::get('/scm/surat-jalan/{id}/print', [PurchaseController::class, 'printSuratJalan'])->name('scm.print-sj');
Route::get('/scm/surat-jalan/{id}/packing-list', [PurchaseController::class, 'printPackingList'])->name('scm.print-pack-list');
Route::post('/scm/surat-jalan/{id}/cancel', [PurchaseController::class, 'cancelSuratJalan'])->name('scm.sj.cancel');

Route::middleware(['auth', 'permission:scm.pengiriman.index'])->prefix('scm/pengiriman')->group(function () {

    // 1. Halaman List PO yang Siap Kirim (Pending Delivery)
    Route::get('/', [PurchaseController::class, 'indexPendingDelivery'])->name('scm.pengiriman.index');
    ->name('dashboard.harian');
Route::get('/dashboard/recap', [AuditController::class, 'dashboardRecap'])
    ->middleware(['auth', 'permission:dashboard.recap'])
    ->name('dashboard.recap');

Route::get('/investor/internal/audit', [AuditController::class, 'dataInternalAudit'])
    Route::post('/finalisasi', [PurchaseController::class, 'finalisasiPengiriman'])->name('scm.finalisasi-sj');
});
//====== OUTLET MAPPING to DC and SUPPLIER ======
Route::get('/outlet-mapping', [PurchaseController::class, 'indexMapping'])
    ->middleware(['auth', 'permission:admin.mapping.index'])
    ->name('admin.mapping.index');
Route::post('/mapping-dc/simpan', [PurchaseController::class, 'simpanMapping'])->name('admin.mapping.simpan');
Route::get('/mapping-supplier', [PurchaseController::class, 'indexMappingSupplier'])
    ->middleware(['auth', 'permission:outlet.mapping.supplier'])
    ->name('outlet.mapping.supplier');
Route::get('/mapping-supplier/{outlet_id}', [PurchaseController::class, 'getMapping']);
Route::post('/mapping-supplier/simpan', [PurchaseController::class, 'simpanMappingSupplier'])->name('simpan.mapping.supplier');
Route::get('/mapping-supplier/edit/{outlet_id}', [PurchaseController::class, 'editMappingSupplier'])->name('mapping.supplier.edit');


// Route untuk Master Data Produk SCM
Route::get('/scm/produk', [PurchaseController::class, 'indexBahan'])
    ->middleware(['auth', 'permission:scm.index-bahan'])
    ->name('scm.index-bahan');
Route::get('/scm/produk/create', [PurchaseController::class, 'createBahan'])->name('scm.create-bahan');
Route::get('/scm/produk/view/{id}', [PurchaseController::class, 'showBahan'])->name('scm.show-bahan');
Route::post('/scm/produk/simpan', [PurchaseController::class, 'storeBahan'])->name('scm.store-bahan');
Route::post('/scm/produk/import', [PurchaseController::class, 'importBahan'])->name('scm.import-bahan');
Route::get('/scm/produk/edit/{id}', [PurchaseController::class, 'editBahan'])->name('scm.edit-bahan');
Route::put('/scm/produk/update/{id}', [PurchaseController::class, 'updateBahan'])->name('scm.update-bahan');
Route::delete('/scm/produk/delete/{id}', [PurchaseController::class, 'deleteBahan'])->name('scm.delete-bahan');
Route::get('/get-satuan-bahan/{bahan_id}', [PurchaseController::class, 'getSatuanBahan'])
     ->name('get.satuan.bahan');
Route::get('/list-distributor', [PurchaseController::class, 'distributorList'])
    ->middleware(['auth', 'permission:purchasing.listDistributor'])
    ->name('purchasing.listDistributor');
Route::delete('/list-distributor/delete/{id}', [PurchaseController::class, 'destroyDC'])->name('dc.delete');
Route::post('/list-distributor/store', [PurchaseController::class, 'storeDC'])->name('dc.store');
Route::post('/list-distributor/update', [PurchaseController::class, 'updateDC'])->name('dc.update');
Route::get('/supplier-list', [PurchaseController::class, 'indexSupplier'])
    ->middleware(['auth', 'permission:supplier.index'])
    ->name('supplier.index');
Route::post('/supplier-list/store', [PurchaseController::class, 'storeSupplier'])->name('supplier.store');
Route::post('/supplier-list/update', [PurchaseController::class, 'updateSupplier'])->name('supplier.update');
Route::get('/supplier-list/delete/{id}', [PurchaseController::class, 'destroySupplier'])->name('supplier.delete');
Route::post('/supplier-list/sync-suppliers', [SCMController::class, 'syncSupplier'])->name('sync.suppliers');
Route::get('/list-armada', [PurchaseController::class, 'armadaList'])
    ->middleware(['auth', 'permission:purchasing.armadaList'])
    ->name('purchasing.armadaList');
Route::post('/list-armada/store', [PurchaseController::class, 'storeArmada'])->name('armada.store');
Route::post('/list-armada/update', [PurchaseController::class, 'updateArmada'])->name('armada.update');
Route::delete('/list-armada/delete/{id}', [PurchaseController::class, 'destroyArmada'])->name('armada.delete');
Route::get('/list-driver', [PurchaseController::class, 'driverList'])
    ->middleware(['auth', 'permission:purchasing.driverList'])
    ->name('purchasing.driverList');
Route::post('/list-driver/store', [PurchaseController::class, 'storeDriver'])->name('driver.store');
Route::post('/list-driver/update', [PurchaseController::class, 'updateDriver'])->name('driver.update');
Route::delete('/list-driver/delete/{id}', [PurchaseController::class, 'destroyDriver'])->name('driver.delete');
Route::get('/unit-list', [PurchaseController::class, 'unitList'])
    ->middleware(['auth', 'permission:purchasing.unitList'])
    ->name('purchasing.unitList');
Route::post('/unit-list/store', [PurchaseController::class, 'storeUnit'])->name('unit.store');
Route::post('/unit-list/update', [PurchaseController::class, 'updateUnit'])->name('unit.update');
Route::delete('/unit-list/delete/{id}', [PurchaseController::class, 'destroyUnit'])->name('unit.delete');
Route::post('/unit-list/sync', [SCMController::class, 'syncUnits'])->name('units.sync');
Route::match(['get', 'post'], '/products/sync', [SCMController::class, 'syncBahan'])->name('products.sync');
Route::get('/customers', [SCMController::class, 'indexCustomer'])
    ->middleware(['auth', 'permission:customers.index'])
    ->name('customers.index');
Route::post('/customers/sync', [SCMController::class, 'syncCustomer'])->name('customers.sync');
Route::post('/sync-master-location', [SCMController::class, 'syncLoc'])->name('location.sync');
Route::get('/scm-pricelist', [PurchaseController::class, 'indexPricelist'])->name('scm.pricelist.index');
Route::post('/scm-pricelist/store', [PurchaseController::class, 'storePricelist'])->name('scm.pricelist.store');
Route::put('/scm-pricelist/{id}', [PurchaseController::class, 'updatePricelist'])->name('scm.pricelist.update');
Route::post('/scm-pricelist/import', [PurchaseController::class, 'importPricelist'])->name('scm.pricelist.import');
Route::get('/scm/area-mapping', [SCMController::class, 'indexAreaMapping'])->name('scm.area_mapping.index');
Route::post('/scm/area-mapping/store-route', [SCMController::class, 'storeRoute'])->name('scm.area_mapping.store_route');
Route::post('/scm/area-mapping/delete-route', [SCMController::class, 'deleteRoute'])->name('scm.area_mapping.delete_route');
Route::post('/scm/area-mapping/add-outlet', [SCMController::class, 'storeMapping'])->name('scm.area_mapping.add');
Route::post('/scm/area-mapping/remove-outlet', [SCMController::class, 'removeMapping'])->name('scm.area_mapping.remove');

Route::get('/order-list', [PurchaseController::class, 'rekapTonase'])->name('admin.rekap-armada');
Route::post('/order-list/simpan-pengiriman', [PurchaseController::class, 'simpanPengiriman'])->name('admin.simpan-pengiriman');
Route::post('/master/setting/store', [AuditController::class, 'storeSetting'])->name('master.setting.store');
Route::get('/warehouse/{id}', [PurchaseController::class, 'showDC'])->name('warehouse.detail');
Route::post('/purchasing/update-rop-dc', [PurchaseController::class, 'updateRopDC'])->name('update.rop.dc');

Route::get('/simple-purchase', [PurchaseController::class, 'indexSimPurchase'])
    ->middleware(['auth', 'permission:simple-purchase.index'])
    ->name('simple-purchase.index');
Route::get('/simple-purchase/create', [PurchaseController::class, 'createSimPurchase'])->name('simple-purchase.create');
Route::post('/simple-purchase/store', [PurchaseController::class, 'storeSimPurchase'])->name('simple-purchase.store');
Route::get('/simple-purchase/{credential_id}/{purchase_num}', [PurchaseController::class, 'showSimPurchase'])->name('simple-purchase.show');
// Route::post('/dashboard-outlet/store', [PurchaseController::class, 'storePO'])->name('po.store');
// Route::delete('/dashboard-outlet/delete/{id}', [PurchaseController::class, 'destroyPO'])->name('po.delete');
// Route::get('/dashboard-outlet/po-detail/{id}', [PurchaseController::class, 'getDetailPO']);
Route::post('/simple-purchase/push', [PurchaseController::class, 'pushPurchase'])->name('simple-purchase.push');
// Route::get('/simple-purchase/fetch', [PurchaseController::class, 'fetchFromEsb'])->name('simple-purchase.fetch');

Route::get('/simple-sales', [PurchaseController::class, 'indexSimSales'])
    ->middleware(['auth', 'permission:simple-sales.index'])
    ->name('simple-sales.index');
Route::get('/simple-sales/create', [PurchaseController::class, 'createSimSales'])->name('simple-sales.create');
Route::post('/simple-sales/store', [PurchaseController::class, 'storeSimSales'])->name('simple-sales.store');
Route::get('/simple-sales/{sales_num}', [PurchaseController::class, 'showSimSales'])->name('simple-sales.show');
Route::get('/dashboard-outlet/po-detail/{id}', [PurchaseController::class, 'getDetailPO']);
Route::post('/dashboard-outlet/recieve/store', [PurchaseController::class, 'storeReceive'])->name('recieve.store');
Route::get('/simple-sales/{sales_num}/edit', [PurchaseController::class, 'editSimSales'])->name('simple-sales.edit');
Route::put('/simple-sales/{sales_num}', [PurchaseController::class, 'updateSimSales'])->name('simple-sales.update');

Route::get('/simple-transfer', [PurchaseController::class, 'indexSimTransfer'])
    ->middleware(['auth', 'permission:simple-transfer.index'])
    ->name('simple-transfer.index');
Route::get('/simple-transfer/create', [PurchaseController::class, 'createSimTransfer'])->name('simple-transfer.create');
Route::get('/simple-transfer/edit/{transfer_num}', [PurchaseController::class, 'editSimTransfer'])->name('simple-transfer.edit');
Route::put('/simple-transfer/{transfer_num}', [PurchaseController::class, 'updateSimTransfer'])->name('simple-transfer.update');
Route::get('/dashboard-scm', [PurchaseController::class, 'scmDashboard'])
    ->middleware(['auth', 'permission:purchasing.dashboardSCM'])
    ->name('purchasing.dashboardSCM');
Route::post('/dashboard-scm/update-status-po', [PurchaseController::class, 'updateStatusPO'])->name('update.status.po');
Route::get('/dashboard-scm/po-supplier-items/{id}', [PurchaseController::class, 'getPoSupplierItems'])->name('scm.po-supplier-items');
Route::get('/setup-distribution', [PurchaseController::class, 'setupIndex'])->name('purchasing.setupDistribution');
Route::post('/setup-distribution/save', [PurchaseController::class, 'save'])->name('setup.distribusi.save');

Route::get('/stock-control', [PurchaseController::class, 'controlStock'])
    ->middleware(['auth', 'permission:purchasing.stockControl'])
    ->name('purchasing.stockControl');

});

// ====== PO-SO Integrated =======
Route::get('/purchase-order', [SCMController::class, 'indexPurchaseOrder'])
    ->middleware(['auth', 'permission:purchase-order.index'])
    ->name('purchase-order.index');
Route::post('/purchase-order/store', [SCMController::class, 'storePurchaseOrder'])->name('purchase-order.store');
// PENTING: route spesifik harus SEBELUM route wildcard {id}
// jika tidak, Laravel menangkap 'po-details' sebagai nilai {id}
    ->name('purchasing.listDistributor');
Route::post('/purchasing/stok/transfer', [PurchaseController::class, 'stockTransfer'])->name('stok.transfer');
Route::get('/purchase-order/{id}/edit', [SCMController::class, 'editPurchaseOrder'])->name('purchase-order.edit');
Route::post('/purchase-order/{id}/update', [SCMController::class, 'updatePurchaseOrder'])->name('purchase-order.update');
Route::delete('/purchase-order/{id}', [SCMController::class, 'destroyPurchaseOrder'])->name('purchase-order.destroy');
Route::get('/sales-order', [SCMController::class, 'indexSalesOrder'])
    ->middleware(['auth', 'permission:sales-order.index'])
    ->name('sales-order.index');
Route::post('/sales-order/store', [SCMController::class, 'storeSalesOrder'])->name('sales-order.store');
Route::get('/sales-order/{id}', [SCMController::class, 'showSalesOrder'])->name('sales-order.show');
Route::get('/sales-order/{id}/edit', [SCMController::class, 'editSalesOrder'])->name('sales-order.edit');

    // 1. Halaman List PO yang Siap Kirim (Pending Delivery)



Route::get('/scm/goods-receipt', [SCMController::class, 'indexGoodsReceipt'])
    ->middleware(['auth', 'permission:goods-receipt.index'])
    ->name('goods-receipt.index');
// GET  /scm/goods-receipt/{id}     → detail 1 GR (JSON, untuk AJAX modal)
Route::get('/scm/goods-receipt/{id}', [SCMController::class, 'showGoodsReceipt'])->name('goods-receipt.show');
// GET  /scm/goods-receipt/po-details/{poId} → ambil item PO (JSON, untuk form modal)
});

Route::get('/menu', function () {
    return view('Purchasing.menu');
})->name('menu');

Route::post('/scm/goods-receipt/{id}/qc', [SCMController::class, 'updateQc'])->name('goods-receipt.qc');

// GET  /scm/goods-delivery               → daftar semua GD
Route::get('/scm/goods-delivery', [SCMController::class, 'indexGoodsDelivery'])
    ->middleware(['auth', 'permission:goods-delivery.index'])
    ->name('goods-delivery.index');
// GET  /scm/goods-delivery/so-details/{soId} → AJAX: load item SO ke form
// PENTING: route ini harus DI ATAS /{id} agar tidak tertangkap sebagai {id}
Route::get('/scm/goods-delivery/so-details/{soId}', [SCMController::class, 'getSoDetails'])->name('goods-delivery.so-details');
    ->name('investor.sales.dashboardBOD');

Route::get('/dashboard/GO', [InvestorSalesController::class, 'indexGO'])
    ->middleware(['auth', 'permission:investor.sales.dashboardGO'])
    ->name('investor.sales.dashboardGO');

// SALES
Route::get('/dashboard/bod/sales', [InvestorSalesController::class, 'bodSales'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.sales'])
// Route::post('/dashboard-outlet/po-receive', [SCMController::class, 'storePoReceive'])->name('recieve.store');


Route::get('/scm/purchase-invoice', [SCMController::class, 'indexPurchaseInvoice'])
    ->middleware(['auth', 'permission:purchase-invoice.index'])
    ->name('purchase-invoice.index');
// GET  /scm/purchase-invoice/gr-details/{grId}  → AJAX: load GR items + 3-way match check
// PENTING: harus di atas /{id} agar tidak ditangkap sebagai show
Route::get('/scm/purchase-invoice/gr-details/{grId}', [SCMController::class, 'getGrDetails'])->name('purchase-invoice.gr-details');
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.labourCost'])
    ->name('investor.sales.dashboardBOD.labourCost');

// QCR
Route::get('/dashboard/bod/qcr', [InvestorSalesController::class, 'bodQcr'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.qcr'])
    ->name('investor.sales.dashboardBOD.qcr');


// RECRUITMENT
Route::get('/purchasing/goods-delivery/{id}/print', [SCMController::class, 'printGoodsDelivery'])->name('goods.delivery.print');
Route::get('/purchasing/goods-receipt/{id}/print',  [SCMController::class, 'printGoodsReceipt'])->name('goods.receipt.print');

Route::get('/scm/sales-invoice', [SCMController::class, 'indexSalesInvoice'])
    ->middleware(['auth', 'permission:sales-invoice.index'])
    ->name('sales-invoice.index');
// GET  /scm/sales-invoice/gd-details/{gdId}  → AJAX: load GD items + 3-way match
// PENTING: harus di atas /{id}
Route::get('/scm/sales-invoice/gd-details/{gdId}', [SCMController::class, 'getGdDetails'])->name('sales-invoice.gd-details');

Route::get('/dashboard/bod/fulfillment-training', [InvestorSalesController::class, 'bodFulfillmentTraining'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.fulfillmentTraining'])
    ->name('investor.sales.dashboardBOD.fulfillmentTraining');
// POST /scm/sales-invoice/{id}/pay           → catat pembayaran dari outlet
Route::post('/scm/sales-invoice/{id}/pay', [SCMController::class, 'recordPaymentSalesInvoice'])->name('sales-invoice.pay');

Route::middleware(['auth', 'permission:reports.stock.movement'])->prefix('purchasing/reports')->name('reports.')->group(function () {
    Route::get('/stock-movement',       [SCMController::class, 'stockMovement'])->name('stock.movement');
    Route::get('/stock-opname',         [SCMController::class, 'stockOpname'])->name('stock.opname');
    Route::get('/goods-receipt-recap',  [SCMController::class, 'goodsReceiptRecap'])->name('gr.recap');
    ->name('investor.sales.dashboardBOD.trainingLeader');


// RTO
Route::get('/dashboard/bod/rto', [InvestorSalesController::class, 'bodRto'])
Route::get('/scm/outlet-receiving/detail/{id}', [SCMController::class, 'showReceivingReport'])->name('scm.outlet_receiving.detail');

// ACCOUNT SCM
Route::get('/user/scm', [SCMController::class, 'userSCM'])
    ->middleware(['auth', 'permission:user.account.scm'])
    ->name('user.account.scm');
Route::post('/user/scm/store', [SCMController::class, 'storeUserSCM'])->name('user.account.scm.store');
Route::post('/user/scm/update/{id}', [SCMController::class, 'updateUserSCM'])->name('user.account.scm.update');
Route::delete('/user/scm/delete/{id}', [SCMController::class, 'destroyUserSCM'])->name('user.account.scm.delete');
    ->name('investor.sales.dashboardBOD.kemitraan');

    Route::get('/dashboard-scm-dc', [SCMController::class, 'indexSCM'])->name('dashboard.scm.dc');
});

Route::get('/dashboard-coba/scm', function () {
    return view('Purchasing.SCM.dashboard');
})->name('user.scm');

// COBA
Route::middleware(['auth', CheckSuperAdmin::class])->get('/test-sync', function () {


// OPERASIONAL
Route::get('/dashboard/bod/otif', [InvestorSalesController::class, 'bodOtif'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.otif'])
    ->name('investor.sales.dashboardBOD.otif');

Route::get('/dashboard/bod/cro', [InvestorSalesController::class, 'bodCro'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.cro'])
    ->name('investor.sales.dashboardBOD.cro');

Route::get('/dashboard/bod/cs', [InvestorSalesController::class, 'bodCs'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.cs'])
    ->name('investor.sales.dashboardBOD.cs');

Route::get('/dashboard/bod/ecommerce', [InvestorSalesController::class, 'bodEcommerce'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.ecommerce'])
    ->name('investor.sales.dashboardBOD.ecommerce');

Route::get('/dashboard/bod/mapping-market', [InvestorSalesController::class, 'bodMappingMarket'])
    ->middleware(['auth', 'permission:investor.sales.dashboardBOD.mappingMarket'])
    ->name('investor.sales.dashboardBOD.mappingMarket');

//====== SURAT JALAN =====
Route::get('/scm/surat-jalan', [PurchaseController::class, 'indexSuratJalan'])
    ->middleware(['auth', 'permission:scm.surat-jalan.index'])
    ->name('scm.surat-jalan.index'); // ganti route yang sudah ada
Route::get('/scm/surat-jalan/{id}/print', [PurchaseController::class, 'printSuratJalan'])->name('scm.print-sj');
Route::get('/scm/surat-jalan/{id}/packing-list', [PurchaseController::class, 'printPackingList'])->name('scm.print-pack-list');
Route::post('/scm/surat-jalan/{id}/cancel', [PurchaseController::class, 'cancelSuratJalan'])->name('scm.sj.cancel');

Route::middleware(['auth', 'permission:scm.pengiriman.index'])->prefix('scm/pengiriman')->group(function () {

    // 1. Halaman List PO yang Siap Kirim (Pending Delivery)
    Route::get('/', [PurchaseController::class, 'indexPendingDelivery'])->name('scm.pengiriman.index');

    // 2. Halaman Rekap (Setelah pilih PO & klik "Buat Surat Jalan")
    // Route::post('/buat-rekap', [PurchaseController::class, 'buatSJ'])->name('scm.buat-sj');
    Route::match(['get', 'post'], '/buat-rekap', [PurchaseController::class, 'buatSJ'])->name('scm.buat-sj');

    // 3. Finalisasi (Simpan ke tbl_surat_jalan & Update status PO)
    Route::post('/finalisasi', [PurchaseController::class, 'finalisasiPengiriman'])->name('scm.finalisasi-sj');
});
//====== OUTLET MAPPING to DC and SUPPLIER ======
Route::get('/outlet-mapping', [PurchaseController::class, 'indexMapping'])
    ->middleware(['auth', 'permission:admin.mapping.index'])
    ->name('admin.mapping.index');
Route::post('/mapping-dc/simpan', [PurchaseController::class, 'simpanMapping'])->name('admin.mapping.simpan');
Route::get('/mapping-supplier', [PurchaseController::class, 'indexMappingSupplier'])
    ->middleware(['auth', 'permission:outlet.mapping.supplier'])
    ->name('outlet.mapping.supplier');
Route::get('/mapping-supplier/{outlet_id}', [PurchaseController::class, 'getMapping']);
Route::post('/mapping-supplier/simpan', [PurchaseController::class, 'simpanMappingSupplier'])->name('simpan.mapping.supplier');
Route::get('/mapping-supplier/edit/{outlet_id}', [PurchaseController::class, 'editMappingSupplier'])->name('mapping.supplier.edit');


// Route untuk Master Data Produk SCM
Route::get('/scm/produk', [PurchaseController::class, 'indexBahan'])
    ->middleware(['auth', 'permission:scm.index-bahan'])
    ->name('scm.index-bahan');
Route::get('/scm/produk/create', [PurchaseController::class, 'createBahan'])->name('scm.create-bahan');
Route::get('/scm/produk/view/{id}', [PurchaseController::class, 'showBahan'])->name('scm.show-bahan');
Route::post('/scm/produk/simpan', [PurchaseController::class, 'storeBahan'])->name('scm.store-bahan');
Route::post('/scm/produk/import', [PurchaseController::class, 'importBahan'])->name('scm.import-bahan');
Route::get('/scm/produk/edit/{id}', [PurchaseController::class, 'editBahan'])->name('scm.edit-bahan');
Route::put('/scm/produk/update/{id}', [PurchaseController::class, 'updateBahan'])->name('scm.update-bahan');
Route::delete('/scm/produk/delete/{id}', [PurchaseController::class, 'deleteBahan'])->name('scm.delete-bahan');
Route::get('/get-satuan-bahan/{bahan_id}', [PurchaseController::class, 'getSatuanBahan'])
     ->name('get.satuan.bahan');
Route::get('/list-distributor', [PurchaseController::class, 'distributorList'])
    ->middleware(['auth', 'permission:purchasing.listDistributor'])
    ->name('purchasing.listDistributor');
Route::delete('/list-distributor/delete/{id}', [PurchaseController::class, 'destroyDC'])->name('dc.delete');
Route::post('/list-distributor/store', [PurchaseController::class, 'storeDC'])->name('dc.store');
Route::post('/list-distributor/update', [PurchaseController::class, 'updateDC'])->name('dc.update');
Route::get('/supplier-list', [PurchaseController::class, 'indexSupplier'])
    ->middleware(['auth', 'permission:supplier.index'])
    ->name('supplier.index');
Route::post('/supplier-list/store', [PurchaseController::class, 'storeSupplier'])->name('supplier.store');
            Route::put('/admin/divisions/{id}', [ReservationController::class, 'divisionUpdate'])->name('admin.divisions.update');
            Route::delete('/admin/divisions/{id}', [ReservationController::class, 'divisionDestroy'])->name('admin.divisions.destroy');
        });

        // ---------------------------------------------------
        // 📦 SISTEM INVENTARIS LAPTOP IT (Disinkronisasikan)
        // ---------------------------------------------------
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\InventoryController::class, 'dashboard'])->name('dashboard');
            Route::get('/master', [\App\Http\Controllers\InventoryController::class, 'master'])->name('master');
            Route::post('/store', [\App\Http\Controllers\InventoryController::class, 'store'])->name('store');
            Route::put('/update/{id}', [\App\Http\Controllers\InventoryController::class, 'update'])->name('update');
            Route::delete('/destroy/{id}', [\App\Http\Controllers\InventoryController::class, 'destroy'])->name('destroy');
            
            // Serah Terima
            Route::get('/assign/{id}', [\App\Http\Controllers\InventoryController::class, 'assignView'])->name('assign');
            Route::post('/assign/{id}', [\App\Http\Controllers\InventoryController::class, 'assignProcess'])->name('assign.process');
            Route::post('/return/{id}', [\App\Http\Controllers\InventoryController::class, 'returnLaptop'])->name('return');
            
            // Audit Scanner
            Route::get('/audit', [\App\Http\Controllers\InventoryController::class, 'auditScanner'])->name('audit');
            Route::post('/audit/process', [\App\Http\Controllers\InventoryController::class, 'auditProcess'])->name('audit.process');
            
            // Maintenance Tickets
            Route::get('/tickets', [\App\Http\Controllers\InventoryController::class, 'tickets'])->name('tickets');
            Route::post('/tickets', [\App\Http\Controllers\InventoryController::class, 'storeTicket'])->name('tickets.store');
            Route::post('/tickets/{id}/update', [\App\Http\Controllers\InventoryController::class, 'updateTicket'])->name('tickets.update');
            Route::delete('/tickets/{id}', [\App\Http\Controllers\InventoryController::class, 'destroyTicket'])->name('tickets.destroy');
            
            // Print Berita Acara
            Route::get('/print/{id}', [\App\Http\Controllers\InventoryController::class, 'printBeritaAcara'])->name('print');
            
            // Riwayat Aset (Log Mutasi)
            Route::get('/master/{id}/history', [\App\Http\Controllers\InventoryController::class, 'history'])->name('history');
            
            // Approval Disposal
            Route::get('/disposals', [\App\Http\Controllers\InventoryController::class, 'disposals'])->name('disposals');
            Route::post('/disposals/{id}/request', [\App\Http\Controllers\InventoryController::class, 'requestDisposal'])->name('disposals.request');
            Route::post('/disposals/{id}/process', [\App\Http\Controllers\InventoryController::class, 'processDisposal'])->name('disposals.process');
        });
    });
});

Route::post('/list-armada/store', [PurchaseController::class, 'storeArmada'])->name('armada.store');
Route::post('/list-armada/update', [PurchaseController::class, 'updateArmada'])->name('armada.update');
Route::delete('/list-armada/delete/{id}', [PurchaseController::class, 'destroyArmada'])->name('armada.delete');
Route::get('/list-driver', [PurchaseController::class, 'driverList'])
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:ticketing.dashboard'])
    ->prefix('ticketing')
    ->name('ticketing.')
    ->group(function () {
    ->name('purchasing.unitList');
Route::post('/unit-list/store', [PurchaseController::class, 'storeUnit'])->name('unit.store');
Route::post('/unit-list/update', [PurchaseController::class, 'updateUnit'])->name('unit.update');
Route::delete('/unit-list/delete/{id}', [PurchaseController::class, 'destroyUnit'])->name('unit.delete');
Route::post('/unit-list/sync', [SCMController::class, 'syncUnits'])->name('units.sync');
Route::match(['get', 'post'], '/products/sync', [SCMController::class, 'syncBahan'])->name('products.sync');
Route::get('/customers', [SCMController::class, 'indexCustomer'])
    ->middleware(['auth', 'permission:customers.index'])
    ->name('customers.index');
Route::post('/customers/sync', [SCMController::class, 'syncCustomer'])->name('customers.sync');
Route::post('/sync-master-location', [SCMController::class, 'syncLoc'])->name('location.sync');
Route::get('/scm-pricelist', [PurchaseController::class, 'indexPricelist'])->name('scm.pricelist.index');
Route::post('/scm-pricelist/store', [PurchaseController::class, 'storePricelist'])->name('scm.pricelist.store');
Route::put('/scm-pricelist/{id}', [PurchaseController::class, 'updatePricelist'])->name('scm.pricelist.update');
Route::post('/scm-pricelist/import', [PurchaseController::class, 'importPricelist'])->name('scm.pricelist.import');
Route::get('/scm/area-mapping', [SCMController::class, 'indexAreaMapping'])->name('scm.area_mapping.index');
Route::post('/scm/area-mapping/store-route', [SCMController::class, 'storeRoute'])->name('scm.area_mapping.store_route');
Route::post('/scm/area-mapping/delete-route', [SCMController::class, 'deleteRoute'])->name('scm.area_mapping.delete_route');
Route::post('/scm/area-mapping/add-outlet', [SCMController::class, 'storeMapping'])->name('scm.area_mapping.add');
Route::post('/scm/area-mapping/remove-outlet', [SCMController::class, 'removeMapping'])->name('scm.area_mapping.remove');

Route::get('/order-list', [PurchaseController::class, 'rekapTonase'])->name('admin.rekap-armada');
Route::post('/order-list/simpan-pengiriman', [PurchaseController::class, 'simpanPengiriman'])->name('admin.simpan-pengiriman');

Route::get('/warehouse/{id}', [PurchaseController::class, 'showDC'])->name('warehouse.detail');
Route::post('/purchasing/update-rop-dc', [PurchaseController::class, 'updateRopDC'])->name('update.rop.dc');

Route::get('/simple-purchase', [PurchaseController::class, 'indexSimPurchase'])
    ->middleware(['auth', 'permission:simple-purchase.index'])
    ->name('simple-purchase.index');
Route::get('/simple-purchase/create', [PurchaseController::class, 'createSimPurchase'])->name('simple-purchase.create');
Route::post('/simple-purchase/store', [PurchaseController::class, 'storeSimPurchase'])->name('simple-purchase.store');
Route::get('/simple-purchase/{credential_id}/{purchase_num}', [PurchaseController::class, 'showSimPurchase'])->name('simple-purchase.show');
Route::post('/simple-purchase/sync', [PurchaseController::class, 'syncNow'])->name('purchase.sync');
Route::get('/simple-purchase/sync-status/{syncKey}', [PurchaseController::class, 'checkStatus']);
Route::post('/sync', [PurchaseController::class, 'syncSP'])->name('simple-purchase.sync');
Route::post('/simple-purchase/push', [PurchaseController::class, 'pushPurchase'])->name('simple-purchase.push');
// Route::get('/simple-purchase/fetch', [PurchaseController::class, 'fetchFromEsb'])->name('simple-purchase.fetch');

Route::get('/simple-sales', [PurchaseController::class, 'indexSimSales'])
    ->middleware(['auth', 'permission:simple-sales.index'])
    ->name('simple-sales.index');
Route::get('/simple-sales/create', [PurchaseController::class, 'createSimSales'])->name('simple-sales.create');
Route::post('/simple-sales/store', [PurchaseController::class, 'storeSimSales'])->name('simple-sales.store');
Route::get('/simple-sales/{sales_num}', [PurchaseController::class, 'showSimSales'])->name('simple-sales.show');
Route::post('/sync-sales', [PurchaseController::class, 'syncSS'])->name('sales.sync');
Route::post('/simple-sales/push', [PurchaseController::class, 'pushSales'])->name('simple-sales.push');
Route::get('/simple-sales/{sales_num}/edit', [PurchaseController::class, 'editSimSales'])->name('simple-sales.edit');
Route::put('/simple-sales/{sales_num}', [PurchaseController::class, 'updateSimSales'])->name('simple-sales.update');

Route::get('/simple-transfer', [PurchaseController::class, 'indexSimTransfer'])
    ->middleware(['auth', 'permission:simple-transfer.index'])
    ->name('simple-transfer.index');
Route::get('/simple-transfer/create', [PurchaseController::class, 'createSimTransfer'])->name('simple-transfer.create');
Route::get('/simple-transfer/edit/{transfer_num}', [PurchaseController::class, 'editSimTransfer'])->name('simple-transfer.edit');
Route::put('/simple-transfer/{transfer_num}', [PurchaseController::class, 'updateSimTransfer'])->name('simple-transfer.update');
Route::delete('/simple-transfer/delete/{transfer_num}', [PurchaseController::class, 'destroy'])->name('simple-transfer.destroy');
Route::get('/simple-transfer/{transfer_num}', [PurchaseController::class, 'showTransfer'])->name('simple-transfer.show');
Route::post('/simple-transfer/store', [PurchaseController::class, 'storeSimTransfer'])->name('simple-transfer.store');
Route::post('/simple-transfer/push', [PurchaseController::class, 'pushTransfer'])
    ->name('simple-transfer.push');
Route::post('/simple-transfer/start', [PurchaseController::class, 'startSyncSTF']);
Route::get('/simple-transfer/progress', [PurchaseController::class, 'getProgress']);
Route::prefix('purchasing/stock-opname')->name('stock.opname.')->group(function () {
    //POST: Save (confirmed) — dipanggil dari tombol "Save" di modal
    Route::post('/store', [PurchaseController::class, 'storeStockOpname'])->name('store');
    //POST: Save as Draft — dipanggil dari tombol "Save as Draft" di modal
    Route::post('/draft', [PurchaseController::class, 'draftStockOpname'])->name('draft');
});

// ====== PO-SO Integrated =======
Route::get('/purchase-order', [SCMController::class, 'indexPurchaseOrder'])
    ->middleware(['auth', 'permission:purchase-order.index'])
    ->name('purchase-order.index');
Route::post('/purchase-order/store', [SCMController::class, 'storePurchaseOrder'])->name('purchase-order.store');
// PENTING: route spesifik harus SEBELUM route wildcard {id}
// jika tidak, Laravel menangkap 'po-details' sebagai nilai {id}
Route::get('/purchase-order/get-suppliers-by-branch/{outletId}', [SCMController::class, 'getSuppliersByBranch']);
Route::get('/purchase-order/{id}', [SCMController::class, 'showPurchaseOrder'])->name('purchase-order.show');
Route::get('/purchase-order/{id}/edit', [SCMController::class, 'editPurchaseOrder'])->name('purchase-order.edit');
Route::post('/purchase-order/{id}/update', [SCMController::class, 'updatePurchaseOrder'])->name('purchase-order.update');
Route::delete('/purchase-order/{id}', [SCMController::class, 'destroyPurchaseOrder'])->name('purchase-order.destroy');
Route::get('/sales-order', [SCMController::class, 'indexSalesOrder'])
    ->middleware(['auth', 'permission:sales-order.index'])
    ->name('sales-order.index');
Route::post('/sales-order/store', [SCMController::class, 'storeSalesOrder'])->name('sales-order.store');
Route::get('/sales-order/{id}', [SCMController::class, 'showSalesOrder'])->name('sales-order.show');
Route::get('/sales-order/{id}/edit', [SCMController::class, 'editSalesOrder'])->name('sales-order.edit');
Route::post('/sales-order/{id}/update', [SCMController::class, 'updateSalesOrder'])->name('sales-order.update');
Route::delete('/sales-order/{id}', [SCMController::class, 'destroySalesOrder'])->name('sales-order.destroy');



Route::get('/scm/goods-receipt', [SCMController::class, 'indexGoodsReceipt'])
    ->middleware(['auth', 'permission:goods-receipt.index'])
    ->name('goods-receipt.index');
// GET  /scm/goods-receipt/{id}     → detail 1 GR (JSON, untuk AJAX modal)
Route::get('/scm/goods-receipt/{id}', [SCMController::class, 'showGoodsReceipt'])->name('goods-receipt.show');
// GET  /scm/goods-receipt/po-details/{poId} → ambil item PO (JSON, untuk form modal)
Route::get('/scm/goods-receipt/po-details/{poId}', [SCMController::class, 'getPoDetails'])->name('goods-receipt.po-details');
// POST /scm/goods-receipt          → simpan GR baru (DRAFT)
Route::post('/scm/goods-receipt', [SCMController::class, 'storeGoodsReceipt'])->name('goods-receipt.store');
// POST /scm/goods-receipt/{id}/confirm → konfirmasi GR → update stok
Route::post('/scm/goods-receipt/{id}/confirm', [SCMController::class, 'confirm'])->name('goods-receipt.confirm');
// POST /scm/goods-receipt/{id}/qc → update QC status
Route::post('/scm/goods-receipt/{id}/qc', [SCMController::class, 'updateQc'])->name('goods-receipt.qc');

// GET  /scm/goods-delivery               → daftar semua GD
Route::get('/scm/goods-delivery', [SCMController::class, 'indexGoodsDelivery'])
    ->middleware(['auth', 'permission:goods-delivery.index'])
    ->name('goods-delivery.index');
// GET  /scm/goods-delivery/so-details/{soId} → AJAX: load item SO ke form
// PENTING: route ini harus DI ATAS /{id} agar tidak tertangkap sebagai {id}
Route::get('/scm/goods-delivery/so-details/{soId}', [SCMController::class, 'getSoDetails'])->name('goods-delivery.so-details');
// GET  /scm/goods-delivery/{id}          → detail 1 GD (AJAX)
Route::get('/scm/goods-delivery/{id}', [SCMController::class, 'showGoodsDelivery'])->name('goods-delivery.show');
// POST /scm/goods-delivery               → simpan GD baru (DRAFT)
Route::post('/scm/goods-delivery', [SCMController::class, 'storeGoodsDelivery'])->name('goods-delivery.store');
// POST /scm/goods-delivery/{id}/dispatch → DRAFT → IN_TRANSIT (barang berangkat)
Route::post('/scm/goods-delivery/{id}/dispatch', [SCMController::class, 'dispatchGoodsDelivery'])->name('goods-delivery.dispatch');
// POST /scm/goods-delivery/{id}/deliver  → confirm DELIVERED → stock OUT dicatat
Route::post('/scm/goods-delivery/{id}/deliver', [SCMController::class, 'confirmDelivered'])->name('goods-delivery.deliver');
Route::get('/dashboard-outlet/active-gd/{po_id}', [SCMController::class, 'getActiveGdForOutlet'])->name('outlet.active-gd');
// Route::post('/dashboard-outlet/po-receive', [SCMController::class, 'storePoReceive'])->name('recieve.store');


Route::get('/scm/purchase-invoice', [SCMController::class, 'indexPurchaseInvoice'])
    ->middleware(['auth', 'permission:purchase-invoice.index'])
    ->name('purchase-invoice.index');
// GET  /scm/purchase-invoice/gr-details/{grId}  → AJAX: load GR items + 3-way match check
// PENTING: harus di atas /{id} agar tidak ditangkap sebagai show
Route::get('/scm/purchase-invoice/gr-details/{grId}', [SCMController::class, 'getGrDetails'])->name('purchase-invoice.gr-details');
// GET  /scm/purchase-invoice/{id}               → AJAX: detail 1 PI
Route::get('/scm/purchase-invoice/{id}', [SCMController::class, 'showPurchaseInvoice'])->name('purchase-invoice.show');
// POST /scm/purchase-invoice                    → simpan PI baru
Route::post('/scm/purchase-invoice', [SCMController::class, 'storePurchaseInvoice'])->name('purchase-invoice.store');
// POST /scm/purchase-invoice/{id}/approve       → approve PI → siap bayar
Route::post('/scm/purchase-invoice/{id}/approve', [SCMController::class, 'approvePurchaseInvoice'])->name('purchase-invoice.approve');
// POST /scm/purchase-invoice/{id}/pay           → catat pembayaran ke supplier
Route::post('/scm/purchase-invoice/{id}/pay', [SCMController::class, 'recordPayment'])->name('purchase-invoice.pay');
Route::get('/scm/sales-invoice/{id}/print',    [SCMController::class, 'printSalesInvoice'])->name('sales.invoice.print');
Route::get('/scm/purchase-invoice/{id}/print', [SCMController::class, 'printPurchaseInvoice'])->name('purchase.invoice.print');
Route::get('/purchasing/goods-delivery/{id}/print', [SCMController::class, 'printGoodsDelivery'])->name('goods.delivery.print');
Route::get('/purchasing/goods-receipt/{id}/print',  [SCMController::class, 'printGoodsReceipt'])->name('goods.receipt.print');

Route::get('/scm/sales-invoice', [SCMController::class, 'indexSalesInvoice'])
    ->middleware(['auth', 'permission:sales-invoice.index'])
    ->name('sales-invoice.index');
// GET  /scm/sales-invoice/gd-details/{gdId}  → AJAX: load GD items + 3-way match
// PENTING: harus di atas /{id}
Route::get('/scm/sales-invoice/gd-details/{gdId}', [SCMController::class, 'getGdDetails'])->name('sales-invoice.gd-details');
// GET  /scm/sales-invoice/{id}               → AJAX: detail 1 SI
Route::get('/scm/sales-invoice/{id}', [SCMController::class, 'showSalesInvoice'])->name('sales-invoice.show');
// POST /scm/sales-invoice                    → simpan SI baru
Route::post('/scm/sales-invoice', [SCMController::class, 'storeSalesInvoice'])->name('sales-invoice.store');
// POST /scm/sales-invoice/{id}/pay           → catat pembayaran dari outlet
Route::post('/scm/sales-invoice/{id}/pay', [SCMController::class, 'recordPaymentSalesInvoice'])->name('sales-invoice.pay');

Route::middleware(['auth', 'permission:reports.stock.movement'])->prefix('purchasing/reports')->name('reports.')->group(function () {
    Route::get('/stock-movement',       [SCMController::class, 'stockMovement'])->name('stock.movement');
    Route::get('/stock-opname',         [SCMController::class, 'stockOpname'])->name('stock.opname');
    Route::get('/goods-receipt-recap',  [SCMController::class, 'goodsReceiptRecap'])->name('gr.recap');
    Route::get('/goods-delivery-recap', [SCMController::class, 'goodsDeliveryRecap'])->name('gd.recap');
});
Route::get('/export/stock-movement', [SCMController::class, 'stockMovement'])
    ->name('exports.stock.movement');
Route::get('/scm/outlet-receiving', [SCMController::class, 'receivingReport'])->name('scm.outlet_receiving.index');
Route::get('/scm/outlet-receiving/detail/{id}', [SCMController::class, 'showReceivingReport'])->name('scm.outlet_receiving.detail');

// ACCOUNT SCM
Route::get('/user/scm', [SCMController::class, 'userSCM'])
    ->middleware(['auth', 'permission:user.account.scm'])
    ->name('user.account.scm');
Route::post('/user/scm/store', [SCMController::class, 'storeUserSCM'])->name('user.account.scm.store');
Route::post('/user/scm/update/{id}', [SCMController::class, 'updateUserSCM'])->name('user.account.scm.update');
Route::delete('/user/scm/delete/{id}', [SCMController::class, 'destroyUserSCM'])->name('user.account.scm.delete');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard-scm-dc', [SCMController::class, 'indexSCM'])->name('dashboard.scm.dc');
});

Route::get('/dashboard-coba/scm', function () {
    return view('Purchasing.SCM.dashboard');
})->name('user.scm');

// COBA
Route::middleware(['auth', CheckSuperAdmin::class])->get('/test-sync', function () {
    $service = new \App\Services\EsbPurchaseService();

    $result = $service->syncS('OKNHO', '2026-04-20');

    return response()->json($result);
});

Route::prefix('hospace')->group(function () {

    // =======================================================
    // 👤 RUTE TAMU (Belum Login)
    // =======================================================
    Route::middleware(['guest'])->group(function () {
        Route::get('/login', [RoomAuthController::class, 'index'])->name('hospace.login');
        Route::post('/login', [RoomAuthController::class, 'authenticate'])->name('hospace.login.post');

        Route::get('/register', [RoomAuthController::class, 'showRegister'])->name('hospace.register');
        Route::post('/register', [RoomAuthController::class, 'processRegister'])->name('hospace.register.post');
    });

    // =======================================================
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:reservation.dashboard'])
    ->prefix('reservation')
    ->name('reservation.')
    ->group(function () {
        Route::post('/logout', [RoomAuthController::class, 'logout'])->name('hospace.logout');

        // 📊 Dashboard
        Route::get('/dashboard-reservation', [ReservationController::class, 'dashboard'])->name('dashboard');

        // 📅 Peminjaman Ruangan & Riwayat
        Route::get('/reservations', [ReservationController::class, 'bookingIndex'])->name('reservations.index');
        Route::post('/reservations', [ReservationController::class, 'bookingStore'])->name('reservations.store');
        Route::post('/reservations/{id}/cancel', [ReservationController::class, 'cancelReservation'])->name('reservations.cancel');
        Route::get('/reservations/{id}/reschedule', [ReservationController::class, 'reschedule'])->name('reservations.reschedule');
Route::put('/reservations/{id}/reschedule', [ReservationController::class, 'processReschedule'])->name('reservations.process_reschedule');
        Route::get('/history', [ReservationController::class, 'historyIndex'])->name('reservations.history');
        Route::get('/profile', [ReservationController::class, 'profile'])->name('hospace.profile');

        // ---------------------------------------------------
        // ⛔ BAGIAN 2: HANYA ADMIN (User Biasa Diblokir 100%)
        // ---------------------------------------------------
        // 👇 Ubah bagian ini menggunakan class Middleware yang baru kita buat
        Route::middleware([IsHospaceAdmin::class])->group(function () {

            // 🚪 Master Ruangan
            Route::get('/rooms', [ReservationController::class, 'roomIndex'])->name('rooms.index');
            Route::post('/rooms', [ReservationController::class, 'roomStore'])->name('rooms.store');
            Route::put('/rooms/{id}', [ReservationController::class, 'roomUpdate'])->name('rooms.update');
            Route::delete('/rooms/{id}', [ReservationController::class, 'roomDestroy'])->name('rooms.destroy');

            // ⏰ Master Sesi Waktu
            Route::get('/time-slots', [ReservationController::class, 'timeSlotIndex'])->name('time_slots.index');
            Route::post('/time-slots', [ReservationController::class, 'timeSlotStore'])->name('time_slots.store');
            Route::put('/time-slots/{id}', [ReservationController::class, 'timeSlotUpdate'])->name('time_slots.update');
        Route::delete('/divisions/delete/{id}', [ReservationController::class, 'divisionDestroy'])->name('divisions.destroy');
    });

Route::middleware(['auth', 'permission:investor.surveyor.site-score.index'])
    ->prefix('surveyor/site-score')
    ->name('investor.surveyor.site-score.')
    ->group(function () {

            // ⚖️ Admin Approval System
            Route::get('/admin/approvals', [ReservationController::class, 'approvalIndex'])->name('admin.approvals.index');
            Route::post('/admin/approvals/{id}/approve', [ReservationController::class, 'approvalApprove'])->name('admin.approvals.approve');
        Route::get('/ranking', [SurveyorSiteScoreController::class, 'ranking'])->name('ranking');
        Route::get('/comparison', [SurveyorSiteScoreController::class, 'comparison'])->name('comparison');
        Route::get('/rekap', [SurveyorSiteScoreController::class, 'rekap'])->name('rekap');
        Route::get('/traffic', [SurveyorSiteScoreController::class, 'trafficAnalytics'])->name('traffic');
        Route::get('/heatmap', [SurveyorSiteScoreController::class, 'heatmap'])->name('heatmap');
        Route::get('/detail/{id}', [SurveyorSiteScoreController::class, 'detail'])->name('detail');
        Route::get('/edit/{id}', [SurveyorSiteScoreController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [SurveyorSiteScoreController::class, 'update'])->name('update');
        Route::get('/map', [SurveyorSiteScoreController::class, 'map'])->name('map');
            Route::post('/admin/divisions', [ReservationController::class, 'divisionStore'])->name('admin.divisions.store');
            Route::put('/admin/divisions/{id}', [ReservationController::class, 'divisionUpdate'])->name('admin.divisions.update');
            Route::delete('/admin/divisions/{id}', [ReservationController::class, 'divisionDestroy'])->name('admin.divisions.destroy');
        });

        // ---------------------------------------------------
        // 📦 SISTEM INVENTARIS LAPTOP IT (Disinkronisasikan)
        // ---------------------------------------------------
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\InventoryController::class, 'dashboard'])->name('dashboard');
            Route::get('/master', [\App\Http\Controllers\InventoryController::class, 'master'])->name('master');
            Route::post('/store', [\App\Http\Controllers\InventoryController::class, 'store'])->name('store');
    return 'Semua antrean Laravel berhasil di-restart! Silakan kembali ke halaman Video Detection dan mulai analisis lagi.';
});

Route::middleware(['auth', 'permission:investor.surveyor.candidate.index'])
    ->prefix('surveyor/candidate-location')
    ->name('investor.surveyor.candidate.')
    ->group(function () {
            
            // Audit Scanner
            Route::get('/audit', [\App\Http\Controllers\InventoryController::class, 'auditScanner'])->name('audit');
            Route::post('/audit/process', [\App\Http\Controllers\InventoryController::class, 'auditProcess'])->name('audit.process');
            
            // Maintenance Tickets
        Route::post('/assigned/{id}', [SurveyorCandidateLocationController::class, 'markAssigned'])->name('assigned');
    });

Route::middleware(['auth', 'permission:investor.surveyor.video-detection.index'])
    ->prefix('surveyor/video-detection')
    ->name('investor.surveyor.video-detection.')
    ->group(function () {
            
            // Riwayat Aset (Log Mutasi)
            Route::get('/master/{id}/history', [\App\Http\Controllers\InventoryController::class, 'history'])->name('history');
            
        Route::post('/discard/{jobId}', [SurveyorVideoDetectionController::class, 'discardResult'])->name('discard');
    });

Route::middleware(['auth', 'permission:investor.surveyor.telegram.form'])
    ->prefix('surveyor/telegram-report')
    ->name('investor.surveyor.telegram.')
    ->group(function () {


/*
|--------------------------------------------------------------------------
// Pasang route webhook ini di web.php atau api.php sesuai kebutuhan.
// Jika pakai web.php dan ada CSRF, exclude URL ini dari VerifyCsrfToken.
Route::post('/telegram/site-score/webhook', [TelegramSiteScoreController::class, 'webhook'])
    ->name('telegram.site-score.webhook');

Route::middleware(['auth'])->group(function () {
    Route::get('/master/permissions', [PermissionController::class, 'index'])
        ->name('permissions.index');

    Route::post('/master/permissions/update', [PermissionController::class, 'update'])
        ->name('permissions.update');

    Route::post('/master/permissions/seed-current-role', [PermissionController::class, 'seedCurrentRole'])
        ->name('permissions.seed-current-role');

    Route::post('/master/permissions/sync-routes', [PermissionController::class, 'syncRoutesToSuperadmin'])
        ->name('permissions.sync-routes');
});


/*
|--------------------------------------------------------------------------
| LEGACY INVENTORY URL REDIRECTS
|--------------------------------------------------------------------------
| URL lama diarahkan ke URL inventory baru.
| Route name tetap memakai route utama di atas.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::redirect('/master/qcr', '/inventory/qcr', 301);
    Route::redirect('/master/qcr/', '/inventory/qcr', 301);
    Route::redirect('/master/qcrdata', '/inventory/qcr/data', 301);
    Route::redirect('/master/qcrdata/', '/inventory/qcr/data', 301);
    Route::redirect('/master/qcr/export', '/inventory/qcr/export', 301);
    Route::redirect('/master/qcr/hide-items/save', '/inventory/qcr/hide-items/save', 301);
    Route::redirect('/master/qcr/uang-plus/save', '/inventory/qcr/uang-plus/save', 301);

    Route::redirect('/master/dsc', '/inventory/dsc', 301);
    Route::redirect('/master/dsc/', '/inventory/dsc', 301);
    Route::redirect('/dsc/export', '/inventory/dsc/export', 301);
    Route::redirect('/master/dsc/missing', '/inventory/dsc/missing', 301);
    Route::redirect('/master/dsc/missing/export', '/inventory/dsc/missing/export', 301);
    Route::redirect('/master/dsc/formulir', '/inventory/dsc/formulir', 301);
    Route::redirect('/master/dsc/formulir/omset', '/inventory/dsc/formulir/omset', 301); // GET only, POST fallback ada di route DSC Omset
    Route::redirect('/master/dsc/import', '/inventory/dsc/import', 301);
    Route::redirect('/dsc/history', '/inventory/dsc/history', 301);

    Route::redirect('/master/menu/export', '/inventory/menu/export', 301);
    Route::redirect('/master/bahan/export', '/inventory/bahan/export', 301);
    Route::redirect('/master/bum/export', '/inventory/bum/export', 301);
    Route::redirect('/master/stock/export', '/inventory/stock/export', 301);

    // BUG FIX: List active video detection jobs for background task persistence
    Route::get('/surveyor/video-detection/list-active', [SurveyorVideoDetectionController::class, 'listActiveJobs'])
        ->name('surveyor.video-detection.list-active');

    // BUG FIX: Get progress of specific video detection job
    Route::get('/surveyor/video-detection/progress/{jobId}', [SurveyorVideoDetectionController::class, 'getProgress'])
        ->name('surveyor.video-detection.progress');

    // AI DATA COLLECTOR ROUTE (PHASE 3)
    Route::get('/ai-collector', [App\Http\Controllers\AiCollectorController::class, 'index'])->name('ai.collector.index');
    Route::post('/ai-collector/save', [App\Http\Controllers\AiCollectorController::class, 'saveData'])->name('ai.collector.save');

    // AI PREDICTION ROUTE (PHASE 3)
    Route::post('/surveyor/ai-predict', [App\Http\Controllers\AiPredictController::class, 'predict'])->name('surveyor.ai-predict');

    // MASTER BOQ ROUTES
    Route::get('/surveyor/master-boq', [App\Http\Controllers\MasterBoqController::class, 'index'])->name('master.boq.index');
    Route::post('/surveyor/master-boq/store', [App\Http\Controllers\MasterBoqController::class, 'store'])->name('master.boq.store');
    Route::post('/surveyor/master-boq/update/{id}', [App\Http\Controllers\MasterBoqController::class, 'update'])->name('master.boq.update');
    Route::delete('/surveyor/master-boq/delete/{id}', [App\Http\Controllers\MasterBoqController::class, 'destroy'])->name('master.boq.delete');
});

Route::middleware(['auth'])->prefix('marketing')->name('marketing.')->group(function () {
    Route::get('/sales-per-kota', [MarketingController::class, 'salesPerKota'])
        ->middleware('permission:marketing.sales-per-kota')
        ->name('sales-per-kota');

    Route::get('/outlet-z1', [MarketingController::class, 'outletZ1'])
        ->middleware('permission:marketing.outlet-z1')
        ->name('outlet-z1');

    Route::get('/menu-terlaris', [MarketingController::class, 'menuTerlaris'])
        ->middleware('permission:marketing.menu-terlaris')
        ->name('menu-terlaris');

    Route::get('/content-posting', [MarketingController::class, 'contentPosting'])
        ->middleware('permission:marketing.content-posting')
        ->name('content-posting');

    Route::post('/content-posting', [MarketingController::class, 'contentPostingStore'])
        ->middleware(['permission:marketing.content-posting', 'throttle:30,1'])
        ->name('content-posting.store');

    Route::delete('/content-posting/{id}', [MarketingController::class, 'contentPostingDestroy'])
        ->middleware('permission:marketing.content-posting')
        ->name('content-posting.destroy');

    Route::get('/content-posting/settings', [MarketingController::class, 'contentPostingApiSettings'])
        ->middleware(['permission:marketing.content-posting', 'throttle:60,1'])
        ->name('content-posting.settings');

    Route::post('/content-posting/settings', [MarketingController::class, 'contentPostingSaveApiSettings'])
        ->middleware(['permission:marketing.content-posting', 'throttle:20,1'])
        ->name('content-posting.settings.save');

    Route::get('/content-posting/metrics', [MarketingController::class, 'contentPostingReadMetrics'])
        ->middleware(['permission:marketing.content-posting', 'throttle:60,1'])
        ->name('content-posting.metrics');

    Route::get('/data-sales-perkota', [MarketingController::class, 'dataSalesPerkota'])
        ->middleware('permission:marketing.data-sales-perkota')
        ->name('data-sales-perkota');
});
Route::middleware(['auth', 'permission:ticketing.dashboard'])
    ->prefix('ticketing')
    ->name('ticketing.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [TicketController::class, 'dashboard'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Tickets
        |--------------------------------------------------------------------------
        */
        Route::get('/', [TicketController::class, 'index'])
            ->name('index');

        Route::get('/create', [TicketController::class, 'create'])
            ->name('create');

        Route::post('/store', [TicketController::class, 'store'])
            ->name('store');

        Route::get('/show/{ticket}', [TicketController::class, 'show'])
            ->name('show');

        Route::post('/comment/{ticket}', [TicketController::class, 'comment'])
            ->name('comment');

        /*
        |--------------------------------------------------------------------------
        | Quick Action
        |--------------------------------------------------------------------------
        */
        Route::post('/quick-status/{ticket}', [TicketController::class, 'quickUpdateStatus'])
            ->name('quick-status');

        Route::post('/update-priority/{ticket}', [TicketController::class, 'updateTicketPriority'])
            ->name('update-priority');

        Route::post('/update-content/{ticket}', [TicketController::class, 'updateTicketContent'])
            ->name('update-content');

        Route::post('/update-division/{ticket}', [TicketController::class, 'updateTicketDivision'])
            ->name('update-division');

        /*
        |--------------------------------------------------------------------------
        | Execution
        |--------------------------------------------------------------------------
        */
        Route::post('/confirm/{ticket}', [TicketController::class, 'confirm'])
            ->name('confirm');

        Route::post('/process/{ticket}', [TicketController::class, 'process'])
            ->name('process');

        Route::post('/hold/{ticket}', [TicketController::class, 'hold'])
            ->name('hold');

        Route::post('/close/{ticket}', [TicketController::class, 'close'])
            ->name('close');

        Route::post('/cancel/{ticket}', [TicketController::class, 'cancel'])
            ->name('cancel');

        /*
        |--------------------------------------------------------------------------
        | Admin Review
        |--------------------------------------------------------------------------
        */
        Route::post('/admin-review/{ticket}', [TicketController::class, 'adminReview'])
            ->name('admin-review');

        /*
        |--------------------------------------------------------------------------
        | Mappings
        |--------------------------------------------------------------------------
        */
        Route::get('/mappings', [TicketController::class, 'mappings'])
            ->name('mappings');

        Route::post('/mappings/store', [TicketController::class, 'storeMapping'])
            ->name('mappings.store');

        Route::delete('/mappings/delete/{mapping}', [TicketController::class, 'deleteMapping'])
            ->name('mappings.delete');

        /*
        |--------------------------------------------------------------------------
        | Export
        |--------------------------------------------------------------------------
        */
        Route::get('/export/csv', [TicketController::class, 'exportCsv'])
            ->name('export.csv');

        Route::get('/print', [TicketController::class, 'print'])
            ->name('print');

        /*
        |--------------------------------------------------------------------------
        | Master Area
        |--------------------------------------------------------------------------
        */
        Route::get('/master/area', [TicketController::class, 'masterArea'])
            ->name('master.area');

        Route::post('/master/area/store', [TicketController::class, 'storeArea'])
            ->name('master.area.store');

        Route::put('/master/area/update/{id}', [TicketController::class, 'updateArea'])
            ->name('master.area.update');

        Route::delete('/master/area/delete/{id}', [TicketController::class, 'deleteArea'])
            ->name('master.area.delete');

        /*
        |--------------------------------------------------------------------------
        | Master Items
        |--------------------------------------------------------------------------
        */
        Route::get('/master/items', [TicketController::class, 'masterItems'])
            ->name('master.items');

        Route::post('/master/items/store', [TicketController::class, 'storeItem'])
            ->name('master.items.store');

        Route::put('/master/items/update/{id}', [TicketController::class, 'updateItem'])
            ->name('master.items.update');

        Route::delete('/master/items/delete/{id}', [TicketController::class, 'deleteItem'])
            ->name('master.items.delete');

        /*
        |--------------------------------------------------------------------------
        | Master Types
        |--------------------------------------------------------------------------
        */
        Route::get('/master/types', [TicketController::class, 'masterTypes'])
            ->name('master.types');

        Route::post('/master/types/store', [TicketController::class, 'storeType'])
            ->name('master.types.store');

        Route::put('/master/types/update/{id}', [TicketController::class, 'updateType'])
            ->name('master.types.update');

        Route::delete('/master/types/delete/{id}', [TicketController::class, 'deleteType'])
            ->name('master.types.delete');

        /*
        |--------------------------------------------------------------------------
        | Master Divisions
        |--------------------------------------------------------------------------
        */
        Route::get('/master/divisions', [TicketController::class, 'masterDivisions'])
            ->name('master.divisions');

        Route::post('/master/divisions/store', [TicketController::class, 'storeDivision'])
            ->name('master.divisions.store');

        Route::put('/master/divisions/update/{id}', [TicketController::class, 'updateDivision'])
            ->name('master.divisions.update');

        Route::delete('/master/divisions/delete/{id}', [TicketController::class, 'deleteDivision'])
            ->name('master.divisions.delete');

        /*
        |--------------------------------------------------------------------------
        | Master Priorities
        |--------------------------------------------------------------------------
        */
        Route::get('/master/priorities', [TicketController::class, 'masterPriorities'])
            ->name('master.priorities');

        Route::post('/master/priorities/store', [TicketController::class, 'storePriority'])
            ->name('master.priorities.store');

        Route::put('/master/priorities/update/{id}', [TicketController::class, 'updatePriority'])
            ->name('master.priorities.update');

        Route::delete('/master/priorities/delete/{id}', [TicketController::class, 'deletePriority'])
            ->name('master.priorities.delete');

        /*
        |--------------------------------------------------------------------------
        | Master Users
        |--------------------------------------------------------------------------
        */
        Route::get('/master/users', [TicketController::class, 'users'])
            ->name('master.users');

        Route::post('/master/users/store', [TicketController::class, 'storeUser'])
            ->name('master.users.store');

        Route::get('/master/users/edit/{id}', [TicketController::class, 'showEditUser'])
            ->name('master.users.edit');

        Route::put('/master/users/update/{id}', [TicketController::class, 'updateUser'])
            ->name('master.users.update');

        Route::delete('/master/users/delete/{id}', [TicketController::class, 'deleteUser'])
            ->name('master.users.delete');
    });

    /*
|--------------------------------------------------------------------------
| ROOM RESERVATION / HOSPACE
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'permission:reservation.dashboard'])
    ->prefix('reservation')
    ->name('reservation.')
    ->group(function () {

        Route::get('/dashboard', [ReservationController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [ReservationController::class, 'profile'])->name('profile');

        Route::get('/booking', [ReservationController::class, 'bookingIndex'])->name('booking.index');
        Route::post('/booking/store', [ReservationController::class, 'bookingStore'])->name('booking.store');

        Route::get('/approval', [ReservationController::class, 'approvalIndex'])->name('approval.index');
        Route::post('/approval/{id}/approve', [ReservationController::class, 'approvalApprove'])->name('approval.approve');
        Route::post('/approval/{id}/reject', [ReservationController::class, 'approvalReject'])->name('approval.reject');

        Route::get('/history', [ReservationController::class, 'historyIndex'])->name('history.index');

        Route::get('/maintenance', [ReservationController::class, 'maintenanceIndex'])->name('maintenance.index');
        Route::post('/maintenance/store', [ReservationController::class, 'maintenanceStore'])->name('maintenance.store');
        Route::delete('/maintenance/{id}', [ReservationController::class, 'maintenance.destroy']);

        Route::get('/rooms', [ReservationController::class, 'roomIndex'])->name('rooms.index');
        Route::post('/rooms/store', [ReservationController::class, 'roomStore'])->name('rooms.store');
        Route::post('/rooms/update/{id}', [ReservationController::class, 'roomUpdate'])->name('rooms.update');
        Route::delete('/rooms/delete/{id}', [ReservationController::class, 'roomDestroy'])->name('rooms.destroy');

        Route::get('/time-slots', [ReservationController::class, 'timeSlotIndex'])->name('time-slots.index');
        Route::post('/time-slots/store', [ReservationController::class, 'timeSlotStore'])->name('time-slots.store');
        Route::post('/time-slots/update/{id}', [ReservationController::class, 'timeSlotUpdate'])->name('time-slots.update');
        Route::delete('/time-slots/delete/{id}', [ReservationController::class, 'timeSlotDestroy'])->name('time-slots.destroy');

        Route::get('/divisions', [ReservationController::class, 'divisionIndex'])->name('divisions.index');
        Route::post('/divisions/store', [ReservationController::class, 'divisionStore'])->name('divisions.store');
        Route::post('/divisions/update/{id}', [ReservationController::class, 'divisionUpdate'])->name('divisions.update');
        Route::delete('/divisions/delete/{id}', [ReservationController::class, 'divisionDestroy'])->name('divisions.destroy');
    });

Route::middleware(['auth', 'permission:investor.surveyor.site-score.index'])
    ->prefix('surveyor/site-score')
    ->name('investor.surveyor.site-score.')
    ->group(function () {
        Route::get('/', [SurveyorSiteScoreController::class, 'index'])->name('index');
        Route::get('/my-drafts', [SurveyorSiteScoreController::class, 'myDrafts'])->name('my-drafts');
        Route::get('/create', [SurveyorSiteScoreController::class, 'create'])->name('create');
        Route::post('/store', [SurveyorSiteScoreController::class, 'store'])->name('store');
        Route::get('/ranking', [SurveyorSiteScoreController::class, 'ranking'])->name('ranking');
        Route::get('/comparison', [SurveyorSiteScoreController::class, 'comparison'])->name('comparison');
        Route::get('/rekap', [SurveyorSiteScoreController::class, 'rekap'])->name('rekap');
        Route::get('/traffic', [SurveyorSiteScoreController::class, 'trafficAnalytics'])->name('traffic');
        Route::get('/heatmap', [SurveyorSiteScoreController::class, 'heatmap'])->name('heatmap');
        Route::get('/detail/{id}', [SurveyorSiteScoreController::class, 'detail'])->name('detail');
        Route::get('/edit/{id}', [SurveyorSiteScoreController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [SurveyorSiteScoreController::class, 'update'])->name('update');
        Route::get('/map', [SurveyorSiteScoreController::class, 'map'])->name('map');
        Route::get('/trial-pengamatan', [SurveyorSiteScoreController::class, 'trialPengamatan'])->name('trial-pengamatan');
        Route::get('/by-outlet', [SurveyorSiteScoreController::class, 'byOutlet'])->name('by-outlet');
        Route::get('/resolve-maps-url', [SurveyorSiteScoreController::class, 'resolveMapsUrl'])->name('resolve-maps-url');
        Route::get('/scan-places', [SurveyorSiteScoreController::class, 'scanNearbyPlaces'])->name('scan-places');
        
        // Workflow Approval Routes
        Route::get('/approval', [SurveyorSiteScoreController::class, 'approvalBoard'])->name('approval');
        Route::post('/approval/{id}', [SurveyorSiteScoreController::class, 'updateApproval'])->name('approval.update');
    });

Route::get('/force-restart-workers', function () {
    \Illuminate\Support\Facades\Artisan::call('queue:restart');
    return 'Semua antrean Laravel berhasil di-restart! Silakan kembali ke halaman Video Detection dan mulai analisis lagi.';
});

Route::middleware(['auth', 'permission:investor.surveyor.candidate.index'])
    ->prefix('surveyor/candidate-location')
    ->name('investor.surveyor.candidate.')
    ->group(function () {
        Route::get('/', [SurveyorCandidateLocationController::class, 'index'])->name('index');
        Route::get('/create', [SurveyorCandidateLocationController::class, 'create'])->name('create');
        Route::post('/store', [SurveyorCandidateLocationController::class, 'store'])->name('store');
        
        Route::get('/assignment', [SurveyorCandidateLocationController::class, 'assignment'])->name('assignment');
        Route::post('/assignment/store', [SurveyorCandidateLocationController::class, 'storeAssignment'])->name('assignment.store');
        Route::post('/assigned/{id}', [SurveyorCandidateLocationController::class, 'markAssigned'])->name('assigned');
    });

Route::middleware(['auth', 'permission:investor.surveyor.video-detection.index'])
    ->prefix('surveyor/video-detection')
    ->name('investor.surveyor.video-detection.')
    ->group(function () {
        Route::get('/', [SurveyorVideoDetectionController::class, 'index'])->name('index');
        Route::post('/submit', [SurveyorVideoDetectionController::class, 'submit'])->name('submit');
        Route::get('/status/{jobId}', [SurveyorVideoDetectionController::class, 'status'])->name('status');
        Route::post('/save/{jobId}', [SurveyorVideoDetectionController::class, 'saveResult'])->name('save');
        Route::post('/discard/{jobId}', [SurveyorVideoDetectionController::class, 'discardResult'])->name('discard');
    });

Route::middleware(['auth', 'permission:investor.surveyor.telegram.form'])
    ->prefix('surveyor/telegram-report')
    ->name('investor.surveyor.telegram.')
    ->group(function () {
        Route::get('/form', [TelegramSiteScoreController::class, 'form'])->name('form');
        Route::post('/form-submit', [TelegramSiteScoreController::class, 'formSubmit'])->name('form-submit');
    });

// Pasang route webhook ini di web.php atau api.php sesuai kebutuhan.
// Jika pakai web.php dan ada CSRF, exclude URL ini dari VerifyCsrfToken.
Route::post('/telegram/site-score/webhook', [TelegramSiteScoreController::class, 'webhook'])
    ->name('telegram.site-score.webhook');

Route::middleware(['auth'])->group(function () {
    Route::get('/master/permissions', [PermissionController::class, 'index'])
        ->name('permissions.index');

    Route::post('/master/permissions/update', [PermissionController::class, 'update'])
        ->name('permissions.update');

    Route::post('/master/permissions/seed-current-role', [PermissionController::class, 'seedCurrentRole'])
        ->name('permissions.seed-current-role');

    Route::post('/master/permissions/sync-routes', [PermissionController::class, 'syncRoutesToSuperadmin'])
        ->name('permissions.sync-routes');
});


/*
|--------------------------------------------------------------------------
| LEGACY INVENTORY URL REDIRECTS
|--------------------------------------------------------------------------
| URL lama diarahkan ke URL inventory baru.
| Route name tetap memakai route utama di atas.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    Route::redirect('/master/qcr', '/inventory/qcr', 301);
    Route::redirect('/master/qcr/', '/inventory/qcr', 301);
    Route::redirect('/master/qcrdata', '/inventory/qcr/data', 301);
    Route::redirect('/master/qcrdata/', '/inventory/qcr/data', 301);
    Route::redirect('/master/qcr/export', '/inventory/qcr/export', 301);
    Route::redirect('/master/qcr/hide-items/save', '/inventory/qcr/hide-items/save', 301);
    Route::redirect('/master/qcr/uang-plus/save', '/inventory/qcr/uang-plus/save', 301);

    Route::redirect('/master/dsc', '/inventory/dsc', 301);
    Route::redirect('/master/dsc/', '/inventory/dsc', 301);
    Route::redirect('/dsc/export', '/inventory/dsc/export', 301);
    Route::redirect('/master/dsc/missing', '/inventory/dsc/missing', 301);
    Route::redirect('/master/dsc/missing/export', '/inventory/dsc/missing/export', 301);
    Route::redirect('/master/dsc/formulir', '/inventory/dsc/formulir', 301);
    Route::redirect('/master/dsc/formulir/omset', '/inventory/dsc/formulir/omset', 301); // GET only, POST fallback ada di route DSC Omset
    Route::redirect('/master/dsc/import', '/inventory/dsc/import', 301);
    Route::redirect('/dsc/history', '/inventory/dsc/history', 301);

    Route::redirect('/master/menu/export', '/inventory/menu/export', 301);
    Route::redirect('/master/bahan/export', '/inventory/bahan/export', 301);
    Route::redirect('/master/bum/export', '/inventory/bum/export', 301);
    Route::redirect('/master/stock/export', '/inventory/stock/export', 301);

    // BUG FIX: List active video detection jobs for background task persistence
    Route::get('/surveyor/video-detection/list-active', [SurveyorVideoDetectionController::class, 'listActiveJobs'])
        ->name('surveyor.video-detection.list-active');

    // BUG FIX: Get progress of specific video detection job
    Route::get('/surveyor/video-detection/progress/{jobId}', [SurveyorVideoDetectionController::class, 'getProgress'])
        ->name('surveyor.video-detection.progress');

    // AI DATA COLLECTOR ROUTE (PHASE 3)
    Route::get('/ai-collector', [App\Http\Controllers\AiCollectorController::class, 'index'])->name('ai.collector.index');
    Route::post('/ai-collector/save', [App\Http\Controllers\AiCollectorController::class, 'saveData'])->name('ai.collector.save');

    // AI PREDICTION ROUTE (PHASE 3)
    Route::post('/surveyor/ai-predict', [App\Http\Controllers\AiPredictController::class, 'predict'])->name('surveyor.ai-predict');

    // MASTER BOQ ROUTES
    Route::get('/surveyor/master-boq', [App\Http\Controllers\MasterBoqController::class, 'index'])->name('master.boq.index');
    Route::post('/surveyor/master-boq/store', [App\Http\Controllers\MasterBoqController::class, 'store'])->name('master.boq.store');
    Route::post('/surveyor/master-boq/update/{id}', [App\Http\Controllers\MasterBoqController::class, 'update'])->name('master.boq.update');
    Route::delete('/surveyor/master-boq/delete/{id}', [App\Http\Controllers\MasterBoqController::class, 'destroy'])->name('master.boq.delete');
});

Route::middleware(['auth'])->prefix('marketing')->name('marketing.')->group(function () {
    Route::get('/sales-per-kota', [MarketingController::class, 'salesPerKota'])
        ->middleware('permission:marketing.sales-per-kota')
        ->name('sales-per-kota');

    Route::get('/outlet-z1', [MarketingController::class, 'outletZ1'])
        ->middleware('permission:marketing.outlet-z1')
        ->name('outlet-z1');

    Route::get('/menu-terlaris', [MarketingController::class, 'menuTerlaris'])
        ->middleware('permission:marketing.menu-terlaris')
        ->name('menu-terlaris');

    Route::get('/content-posting', [MarketingController::class, 'contentPosting'])
        ->middleware('permission:marketing.content-posting')
        ->name('content-posting');

    Route::post('/content-posting', [MarketingController::class, 'contentPostingStore'])
        ->middleware(['permission:marketing.content-posting', 'throttle:30,1'])
        ->name('content-posting.store');

    Route::delete('/content-posting/{id}', [MarketingController::class, 'contentPostingDestroy'])
        ->middleware('permission:marketing.content-posting')
        ->name('content-posting.destroy');

    Route::get('/content-posting/settings', [MarketingController::class, 'contentPostingApiSettings'])
        ->middleware(['permission:marketing.content-posting', 'throttle:60,1'])
        ->name('content-posting.settings');

    Route::post('/content-posting/settings', [MarketingController::class, 'contentPostingSaveApiSettings'])
        ->middleware(['permission:marketing.content-posting', 'throttle:20,1'])
        ->name('content-posting.settings.save');

    Route::get('/content-posting/metrics', [MarketingController::class, 'contentPostingReadMetrics'])
        ->middleware(['permission:marketing.content-posting', 'throttle:60,1'])
        ->name('content-posting.metrics');

    Route::get('/data-sales-perkota', [MarketingController::class, 'dataSalesPerkota'])
        ->middleware('permission:marketing.data-sales-perkota')
        ->name('data-sales-perkota');
});
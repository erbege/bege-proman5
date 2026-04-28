<?php

use App\Http\Controllers\AhspController;
use App\Http\Controllers\AhspPriceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialAnalysisController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RabController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class);

    // Master Data
    Route::post('materials/import', [MaterialController::class, 'import'])->name('materials.import');
    Route::resource('materials', MaterialController::class)->except(['show']);
    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('clients', App\Http\Controllers\ClientController::class)->except(['show', 'create', 'edit']);

    // AHSP (Analisa Harga Satuan Pekerjaan)
    Route::prefix('ahsp')->name('ahsp.')->middleware('can:financials.view')->group(function () {
        // Static routes FIRST (before parameter routes)
        Route::get('/', [AhspController::class, 'index'])->name('index');
        Route::get('/create', [AhspController::class, 'create'])->name('create');
        Route::post('/', [AhspController::class, 'store'])->name('store');
        Route::get('/search', [AhspController::class, 'search'])->name('search');

        // AHSP Import (static paths)
        Route::get('/import/work-types', fn() => view('ahsp.import'))->name('import');
        Route::post('/import/work-types', [AhspController::class, 'processImport'])->name('import.process');

        // AHSP Prices (static paths - must be before /{ahspWorkType})
        Route::prefix('prices')->name('prices.')->group(function () {
            Route::get('/search', [AhspPriceController::class, 'search'])->name('search');
            Route::get('/', [AhspPriceController::class, 'index'])->name('index');
            Route::get('/import', [AhspPriceController::class, 'import'])->name('import');
            Route::post('/import', [AhspPriceController::class, 'processImport'])->name('import.process');
            Route::get('/regions', [AhspPriceController::class, 'regions'])->name('regions');
            Route::post('/', [AhspPriceController::class, 'store'])->name('store');
            Route::post('/sync', [AhspPriceController::class, 'syncMaterials'])->name('sync'); // Sync route
            Route::post('/bulk-update', [AhspPriceController::class, 'bulkUpdate'])->name('bulk-update');
            Route::get('/{price}', [AhspPriceController::class, 'show'])->name('show');
            Route::put('/{price}', [AhspPriceController::class, 'update'])->name('update');
            Route::delete('/{price}', [AhspPriceController::class, 'destroy'])->name('destroy');
        });

        // AHSP Work Type parameter routes (AFTER static routes)
        Route::get('/{ahspWorkType}', [AhspController::class, 'show'])->name('show');
        Route::get('/{ahspWorkType}/edit', [AhspController::class, 'edit'])->name('edit');
        Route::put('/{ahspWorkType}', [AhspController::class, 'update'])->name('update');
        Route::delete('/{ahspWorkType}', [AhspController::class, 'destroy'])->name('destroy');
        Route::get('/{ahspWorkType}/calculate', [AhspController::class, 'calculate'])->name('calculate');

        // AHSP Components
        Route::post('/{ahspWorkType}/components', [AhspController::class, 'storeComponent'])->name('components.store');
        Route::put('/{ahspWorkType}/components/{component}', [AhspController::class, 'updateComponent'])->name('components.update');
        Route::delete('/{ahspWorkType}/components/{component}', [AhspController::class, 'destroyComponent'])->name('components.destroy');
    });

    // Inventory
    Route::get('/inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/history', [App\Http\Controllers\InventoryController::class, 'history'])->name('inventory.history');
    Route::post('/inventory/{inventory}/adjust', [App\Http\Controllers\InventoryController::class, 'adjust'])->name('inventory.adjust');

    // RAB (nested under projects)
    Route::prefix('projects/{project}')->name('projects.')->middleware('project_member')->group(function () {
        // Team Management
        Route::get('/team', [App\Http\Controllers\ProjectTeamController::class, 'index'])->name('team.index');

        // RAB Management
        Route::get('/rab', [RabController::class, 'index'])->name('rab.index');
        Route::get('/rab/export-excel', [RabController::class, 'exportExcel'])->name('rab.export-excel')->middleware('can:financials.view');
        Route::get('/rab/export-pdf', [RabController::class, 'exportPdf'])->name('rab.export-pdf')->middleware('can:financials.view');
        Route::get('/rab/import', [RabController::class, 'import'])->name('rab.import')->middleware('can:financials.manage');
        Route::post('/rab/import', [RabController::class, 'processImport'])->name('rab.process-import')->middleware('can:financials.manage');
        Route::get('/rab/template', [RabController::class, 'downloadTemplate'])->name('rab.template')->middleware('can:financials.manage');

        // RAB Sections & Items Management
        Route::middleware('can:financials.manage')->group(function () {
            // RAB Sections
            Route::get('/rab/sections/create', [RabController::class, 'createSection'])->name('rab.sections.create');
            Route::post('/rab/sections', [RabController::class, 'storeSection'])->name('rab.sections.store');

            // RAB Items
            Route::get('/rab/sections/{section}/items/create', [RabController::class, 'createItem'])->name('rab.items.create');
            Route::post('/rab/sections/{section}/items', [RabController::class, 'storeItem'])->name('rab.items.store');
            Route::get('/rab/items/{item}/edit', [RabController::class, 'editItem'])->name('rab.items.edit');
            Route::put('/rab/items/{item}', [RabController::class, 'updateItem'])->name('rab.items.update');
            Route::delete('/rab/items/{item}', [RabController::class, 'destroyItem'])->name('rab.items.destroy');

            // RAB from AHSP
            Route::get('/rab/sections/{section}/ahsp', [RabController::class, 'showAhspSelector'])->name('rab.ahsp.selector');
            Route::post('/rab/sections/{section}/ahsp', [RabController::class, 'generateFromAhsp'])->name('rab.ahsp.generate');
            Route::get('/rab/ahsp/search', [RabController::class, 'searchAhsp'])->name('rab.ahsp.search');
            Route::post('/rab/ahsp/preview', [RabController::class, 'previewAhspCalculation'])->name('rab.ahsp.preview');

            // RAB Template Generator from AHSP Categories
            Route::get('/rab/template-generator', [RabController::class, 'showTemplateGenerator'])->name('rab.template-generator');
            Route::post('/rab/template-generator/preview', [RabController::class, 'previewTemplate'])->name('rab.template-generator.preview');
            Route::post('/rab/template-generator', [RabController::class, 'generateFromTemplate'])->name('rab.template-generator.generate');
        });

        // Schedule & S-Curve (using RAB data instead of WBS)
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::post('/schedule/regenerate', [ScheduleController::class, 'regenerate'])->name('schedule.regenerate');
        Route::get('/schedule/gantt', [ScheduleController::class, 'gantt'])->name('schedule.gantt');
        Route::get('/schedule/scurve', [ScheduleController::class, 'scurve'])->name('schedule.scurve');
        Route::get('/schedule/export-excel', [ScheduleController::class, 'exportExcel'])->name('schedule.export-excel');
        Route::get('/schedule/export-pdf', [ScheduleController::class, 'exportPdf'])->name('schedule.export-pdf');
        Route::get('/schedule/auto', [ScheduleController::class, 'autoSchedule'])->name('schedule.auto');
        Route::post('/schedule/auto', [ScheduleController::class, 'applyAutoSchedule'])->name('schedule.auto.apply');
        Route::patch('/schedule/items/{item}', [ScheduleController::class, 'updateItemSchedule'])->name('schedule.update-item');
        Route::post('/schedule/items/{item}/parallel', [ScheduleController::class, 'updateItemParallel'])->name('schedule.update-item-parallel');

        // Material Analysis (AI & Local)
        Route::prefix('analysis')->name('analysis.')->middleware('can:financials.view')->group(function () {
            Route::get('/', [MaterialAnalysisController::class, 'index'])->name('index');
            Route::get('/{item}', [MaterialAnalysisController::class, 'showItem'])->name('analysis.show');

            Route::middleware('can:financials.manage')->group(function () {
                Route::post('/analyze-all', [MaterialAnalysisController::class, 'analyzeAll'])->name('analyze-all');
                Route::match(['get', 'post'], '/analyze-all-local', [MaterialAnalysisController::class, 'analyzeAllLocal'])->name('analyze-all-local');
                Route::post('/{item}/analyze', [MaterialAnalysisController::class, 'analyze'])->name('analyze');
                Route::post('/{item}/analyze-local', [MaterialAnalysisController::class, 'analyzeLocal'])->name('analyze-local');
                Route::post('/{item}/reanalyze', [MaterialAnalysisController::class, 'reanalyze'])->name('reanalyze');
                Route::post('/{item}/reanalyze-local', [MaterialAnalysisController::class, 'reanalyzeLocal'])->name('reanalyze-local');
                Route::post('/forecast/{forecast}/mapping', [MaterialAnalysisController::class, 'updateMapping'])->name('update-mapping');
                Route::post('/bulk-delete', [MaterialAnalysisController::class, 'bulkDeleteForecasts'])->name('bulk-delete');
                Route::post('/bulk-delete-materials', [MaterialAnalysisController::class, 'bulkDeleteMaterials'])->name('bulk-delete-materials');
                Route::delete('/forecast/{forecast}', [MaterialAnalysisController::class, 'deleteForecast'])->name('delete-forecast');
            });
        });

        // Material Requests (MR)
        Route::get('/mr', [App\Http\Controllers\MaterialRequestController::class, 'index'])->name('mr.index');
        Route::get('/mr/create', [App\Http\Controllers\MaterialRequestController::class, 'create'])->name('mr.create');
        Route::post('/mr', [App\Http\Controllers\MaterialRequestController::class, 'store'])->name('mr.store');
        Route::get('/mr/{mr}', [App\Http\Controllers\MaterialRequestController::class, 'show'])->name('mr.show');
        Route::get('/mr/{mr}/edit', [App\Http\Controllers\MaterialRequestController::class, 'edit'])->name('mr.edit');
        Route::put('/mr/{mr}', [App\Http\Controllers\MaterialRequestController::class, 'update'])->name('mr.update');
        Route::delete('/mr/{mr}', [App\Http\Controllers\MaterialRequestController::class, 'destroy'])->name('mr.destroy');
        Route::post('/mr/{mr}/status', [App\Http\Controllers\MaterialRequestController::class, 'updateStatus'])->name('mr.status');

        // Purchase Requests (PR)
        Route::get('/pr', [App\Http\Controllers\PurchaseRequestController::class, 'index'])->name('pr.index');
        Route::get('/pr/create', [App\Http\Controllers\PurchaseRequestController::class, 'create'])->name('pr.create');
        Route::post('/pr', [App\Http\Controllers\PurchaseRequestController::class, 'store'])->name('pr.store');
        Route::get('/pr/{pr}', [App\Http\Controllers\PurchaseRequestController::class, 'show'])->name('pr.show');
        Route::post('/pr/{pr}/status', [App\Http\Controllers\PurchaseRequestController::class, 'updateStatus'])->name('pr.status');
        Route::delete('/pr/{pr}', [App\Http\Controllers\PurchaseRequestController::class, 'destroy'])->name('pr.destroy');

        // Purchase Orders (PO)
        Route::get('/po', [App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('po.index');
        Route::get('/po/create', [App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('po.create');
        Route::post('/po', [App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('po.store');
        Route::get('/po/{po}', [App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('po.show');
        Route::post('/po/{po}/status', [App\Http\Controllers\PurchaseOrderController::class, 'updateStatus'])->name('po.status');
        Route::get('/po/{po}/print', [App\Http\Controllers\PurchaseOrderController::class, 'print'])->name('po.print');
        Route::delete('/po/{po}', [App\Http\Controllers\PurchaseOrderController::class, 'destroy'])->name('po.destroy');

        // Goods Receipts (GR)
        Route::get('/gr', [App\Http\Controllers\GoodsReceiptController::class, 'index'])->name('gr.index');
        Route::get('/gr/create', [App\Http\Controllers\GoodsReceiptController::class, 'create'])->name('gr.create');
        Route::post('/gr', [App\Http\Controllers\GoodsReceiptController::class, 'store'])->name('gr.store');
        Route::get('/gr/{gr}', [App\Http\Controllers\GoodsReceiptController::class, 'show'])->name('gr.show');
        Route::post('/gr/{gr}/status', [App\Http\Controllers\GoodsReceiptController::class, 'updateStatus'])->name('gr.status');

        // Progress Reports
        Route::get('/progress', [ProgressReportController::class, 'index'])->name('progress.index');
        Route::get('/progress/create', [ProgressReportController::class, 'create'])->name('progress.create');
        Route::post('/progress', [ProgressReportController::class, 'store'])->name('progress.store');
        Route::get('/progress/{report}', [ProgressReportController::class, 'show'])->name('progress.show');
        Route::delete('/progress/{report}', [ProgressReportController::class, 'destroy'])->name('progress.destroy');

        // Weekly Reports
        Route::get('/weekly-reports', [App\Http\Controllers\WeeklyReportController::class, 'index'])->name('weekly-reports.index');
        Route::get('/weekly-reports/create', [App\Http\Controllers\WeeklyReportController::class, 'create'])->name('weekly-reports.create');
        Route::post('/weekly-reports', [App\Http\Controllers\WeeklyReportController::class, 'store'])->name('weekly-reports.store');
        Route::post('/weekly-reports/auto-generate', [App\Http\Controllers\WeeklyReportController::class, 'autoGenerate'])->name('weekly-reports.auto-generate');
        Route::post('/weekly-reports/auto-generate-all', [App\Http\Controllers\WeeklyReportController::class, 'autoGenerateAll'])->name('weekly-reports.auto-generate-all');
        Route::get('/weekly-reports/{report}', [App\Http\Controllers\WeeklyReportController::class, 'show'])->name('weekly-reports.show');
        Route::get('/weekly-reports/{report}/edit', [App\Http\Controllers\WeeklyReportController::class, 'edit'])->name('weekly-reports.edit');
        Route::put('/weekly-reports/{report}', [App\Http\Controllers\WeeklyReportController::class, 'update'])->name('weekly-reports.update');
        Route::delete('/weekly-reports/{report}', [App\Http\Controllers\WeeklyReportController::class, 'destroy'])->name('weekly-reports.destroy');
        Route::get('/weekly-reports/{report}/pdf', [App\Http\Controllers\WeeklyReportController::class, 'exportPdf'])->name('weekly-reports.pdf');
        Route::post('/weekly-reports/{report}/regenerate', [App\Http\Controllers\WeeklyReportController::class, 'regenerate'])->name('weekly-reports.regenerate');
        Route::patch('/weekly-reports/{report}/cumulative', [App\Http\Controllers\WeeklyReportController::class, 'updateCumulative'])->name('weekly-reports.update-cumulative');
        Route::post('/weekly-reports/{report}/update-cover', [App\Http\Controllers\WeeklyReportController::class, 'updateCover'])->name('weekly-reports.update-cover');
        Route::patch('/weekly-reports/{report}/update-detail', [App\Http\Controllers\WeeklyReportController::class, 'updateDetail'])->name('weekly-reports.update-detail');
        Route::post('/weekly-reports/{report}/upload-documentation', [App\Http\Controllers\WeeklyReportController::class, 'uploadDocumentation'])->name('weekly-reports.upload-documentation');
        Route::post('/weekly-reports/{report}/add-progress-photos', [App\Http\Controllers\WeeklyReportController::class, 'addProgressPhotos'])->name('weekly-reports.add-progress-photos');
        Route::delete('/weekly-reports/{report}/remove-documentation', [App\Http\Controllers\WeeklyReportController::class, 'removeDocumentation'])->name('weekly-reports.remove-documentation');
        Route::patch('/weekly-reports/{report}/update-documentation-ids', [App\Http\Controllers\WeeklyReportController::class, 'updateDocumentationIds'])->name('weekly-reports.update-documentation-ids');
        Route::patch('/weekly-reports/{report}/update-activities', [App\Http\Controllers\WeeklyReportController::class, 'updateActivities'])->name('weekly-reports.update-activities');
        Route::post('/weekly-reports/bulk-delete', [App\Http\Controllers\WeeklyReportController::class, 'bulkDestroy'])->name('weekly-reports.bulk-destroy');

        // Material Usage
        Route::get('/usage', [App\Http\Controllers\MaterialUsageController::class, 'index'])->name('usage.index');
        Route::get('/usage/create', [App\Http\Controllers\MaterialUsageController::class, 'create'])->name('usage.create');
        Route::post('/usage', [App\Http\Controllers\MaterialUsageController::class, 'store'])->name('usage.store');
        Route::get('/usage/{usage}', [App\Http\Controllers\MaterialUsageController::class, 'show'])->name('usage.show');

        // Project Files
        Route::get('/files', [App\Http\Controllers\ProjectFileController::class, 'index'])->name('files.index');
        Route::post('/files', [App\Http\Controllers\ProjectFileController::class, 'store'])->name('files.store');
        Route::post('/files/folder', [App\Http\Controllers\ProjectFileController::class, 'createFolder'])->name('files.folder');
        Route::get('/files/{file}', [App\Http\Controllers\ProjectFileController::class, 'show'])->name('files.show');
        Route::post('/files/{file}/version', [App\Http\Controllers\ProjectFileController::class, 'uploadVersion'])->name('files.version');
        Route::post('/files/{file}/rollback/{version}', [App\Http\Controllers\ProjectFileController::class, 'rollback'])->name('files.rollback');
        Route::patch('/files/{file}/status', [App\Http\Controllers\ProjectFileController::class, 'updateStatus'])->name('files.status');
        Route::get('/files/{file}/download/{version?}', [App\Http\Controllers\ProjectFileController::class, 'download'])->name('files.download');
        Route::delete('/files/{file}', [App\Http\Controllers\ProjectFileController::class, 'destroy'])->name('files.destroy');
        Route::post('/files/{file}/comments', [App\Http\Controllers\ProjectFileController::class, 'storeComment'])->name('files.comments.store');
        Route::post('/files/{file}/comments/{comment}/toggle', [App\Http\Controllers\ProjectFileController::class, 'toggleResolveComment'])->name('files.comments.toggle');
        // Cost Control / Financial
        Route::get('/financial', [App\Http\Controllers\CostControlController::class, 'index'])->name('financial.index')->middleware('can:financials.view');
    });
});

// System Settings (Superadmin only)
Route::middleware(['auth', 'role:Superadmin'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/system', [App\Http\Controllers\SystemSettingController::class, 'index'])->name('system');
    Route::put('/system', [App\Http\Controllers\SystemSettingController::class, 'update'])->name('system.update');
    Route::post('/system/test-s3', [App\Http\Controllers\SystemSettingController::class, 'testS3Connection'])->name('system.test-s3');
});

// User & Role Management (super-admin, Superadmin, administrator)
Route::middleware(['auth', 'role:super-admin|Superadmin|administrator'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/users', fn() => view('settings.users'))->name('users');
    Route::get('/roles', fn() => view('settings.roles'))->name('roles');
    
    // Approval Matrix
    Route::get('/approval-matrix', [App\Http\Controllers\ApprovalMatrixController::class, 'index'])->name('approval-matrix.index');
    Route::post('/approval-matrix', [App\Http\Controllers\ApprovalMatrixController::class, 'store'])->name('approval-matrix.store');
    Route::put('/approval-matrix/{matrix}', [App\Http\Controllers\ApprovalMatrixController::class, 'update'])->name('approval-matrix.update');
    Route::delete('/approval-matrix/{matrix}', [App\Http\Controllers\ApprovalMatrixController::class, 'destroy'])->name('approval-matrix.destroy');
});

// Notifications (Authenticated Users)
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/notifications', fn() => view('notifications.index'))->name('notifications.index');
});

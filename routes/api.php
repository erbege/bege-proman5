<?php

use App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ============================================================================
// Authentication Routes (Public)
// ============================================================================
Route::post('/auth/login', [Api\AuthController::class, 'login']);
Route::post('/auth/forgot-password', [Api\AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [Api\AuthController::class, 'resetPassword']);

// ============================================================================
// Authenticated Routes
// ============================================================================
Route::middleware('auth:sanctum')->group(function () {

    // Authentication
    Route::post('/auth/logout', [Api\AuthController::class, 'logout']);
    Route::get('/auth/user', [Api\AuthController::class, 'user']);

    // ========================================================================
    // Dashboard
    // ========================================================================
    Route::get('/dashboard/stats', [Api\DashboardController::class, 'stats']);

    // ========================================================================
    // Master Data
    // ========================================================================
    Route::get('/materials', [Api\MaterialController::class, 'index']);
    Route::get('/materials/{material}', [Api\MaterialController::class, 'show']);

    Route::get('/suppliers', [Api\SupplierController::class, 'index']);
    Route::get('/suppliers/{supplier}', [Api\SupplierController::class, 'show']);

    Route::get('/clients', [Api\ClientController::class, 'index']);
    Route::get('/clients/{client}', [Api\ClientController::class, 'show']);

    // ========================================================================
    // Projects
    // ========================================================================
    Route::get('/projects', [Api\ProjectController::class, 'index']);
    Route::get('/projects/{project}', [Api\ProjectController::class, 'show']);
    Route::get('/projects/{project}/team', [Api\ProjectController::class, 'team']);
    Route::get('/projects/{project}/stats', [Api\ProjectController::class, 'stats']);

    // ========================================================================
    // Project Scoped Resources
    // ========================================================================
    Route::prefix('projects/{project}')->scopeBindings()->group(function () {
        // RAB (Budget)
        Route::get('/rab', [Api\RabController::class, 'index']);
        Route::get('/rab/items/{item}', [Api\RabController::class, 'show']);

        // Schedule
        Route::get('/schedule', [Api\ScheduleController::class, 'index']);
        Route::get('/schedule/scurve', [Api\ScheduleController::class, 'scurve']);

        // Progress Reports
        Route::get('/progress', [Api\ProgressReportController::class, 'index']);
        Route::get('/progress/{report}', [Api\ProgressReportController::class, 'show']);
        Route::post('/progress', [Api\ProgressReportController::class, 'store']);

        // Weekly Reports
        Route::get('/weekly-reports', [Api\WeeklyReportController::class, 'index']);
        Route::post('/weekly-reports', [Api\WeeklyReportController::class, 'store']);
        Route::post('/weekly-reports/auto-generate', [Api\WeeklyReportController::class, 'autoGenerate']);
        Route::get('/weekly-reports/{report}', [Api\WeeklyReportController::class, 'show']);
        Route::delete('/weekly-reports/{report}', [Api\WeeklyReportController::class, 'destroy']);
        Route::post('/weekly-reports/{report}/cover', [Api\WeeklyReportController::class, 'updateCover']);
        Route::patch('/weekly-reports/{report}/cumulative', [Api\WeeklyReportController::class, 'updateCumulative']);
        Route::patch('/weekly-reports/{report}/detail', [Api\WeeklyReportController::class, 'updateDetail']);
        Route::post('/weekly-reports/{report}/documentations/upload', [Api\WeeklyReportController::class, 'uploadDocumentation']);
        Route::post('/weekly-reports/{report}/documentations/progress-photos', [Api\WeeklyReportController::class, 'addProgressPhotos']);
        Route::delete('/weekly-reports/{report}/documentations', [Api\WeeklyReportController::class, 'removeDocumentation']);
        Route::patch('/weekly-reports/{report}/activities', [Api\WeeklyReportController::class, 'updateActivities']);

        // Material Usage
        Route::get('/material-usages', [Api\MaterialUsageController::class, 'index']);
        Route::post('/material-usages', [Api\MaterialUsageController::class, 'store']);
        Route::get('/material-usages/{materialUsage}', [Api\MaterialUsageController::class, 'show']);

        // Procurement Helpers
        Route::get('/available-mr-items', [Api\PurchaseRequestController::class, 'availableMrItems']);
        Route::get('/available-pr-items', [Api\PurchaseOrderController::class, 'availablePrItems']);
    });

    // ========================================================================
    // Material Requests
    // ========================================================================
    Route::get('/material-requests', [Api\MaterialRequestController::class, 'index']);
    Route::get('/material-requests/{materialRequest}', [Api\MaterialRequestController::class, 'show']);
    Route::post('/material-requests', [Api\MaterialRequestController::class, 'store']);
    Route::post('/material-requests/{materialRequest}/approve', [Api\MaterialRequestController::class, 'approve']);
    Route::post('/material-requests/{materialRequest}/reject', [Api\MaterialRequestController::class, 'reject']);

    // ========================================================================
    // Purchase Requests
    // ========================================================================
    Route::get('/purchase-requests', [Api\PurchaseRequestController::class, 'index']);
    Route::get('/purchase-requests/{purchaseRequest}', [Api\PurchaseRequestController::class, 'show']);
    Route::post('/purchase-requests', [Api\PurchaseRequestController::class, 'store']);
    Route::post('/purchase-requests/{purchaseRequest}/approve', [Api\PurchaseRequestController::class, 'approve']);
    Route::post('/purchase-requests/{purchaseRequest}/reject', [Api\PurchaseRequestController::class, 'reject']);

    // ========================================================================
    // Purchase Orders
    // ========================================================================
    Route::get('/purchase-orders', [Api\PurchaseOrderController::class, 'index']);
    Route::get('/purchase-orders/{purchaseOrder}', [Api\PurchaseOrderController::class, 'show']);
    Route::post('/purchase-orders', [Api\PurchaseOrderController::class, 'store']);
    Route::post('/purchase-orders/{purchaseOrder}/approve', [Api\PurchaseOrderController::class, 'approve']);
    Route::post('/purchase-orders/{purchaseOrder}/reject', [Api\PurchaseOrderController::class, 'reject']);

    // ========================================================================
    // Goods Receipts
    // ========================================================================
    Route::get('/goods-receipts', [Api\GoodsReceiptController::class, 'index']);
    Route::get('/goods-receipts/{goodsReceipt}', [Api\GoodsReceiptController::class, 'show']);
    Route::post('/goods-receipts', [Api\GoodsReceiptController::class, 'store']);
    Route::post('/goods-receipts/{goodsReceipt}/approve', [Api\GoodsReceiptController::class, 'approve']);
    Route::post('/goods-receipts/{goodsReceipt}/reject', [Api\GoodsReceiptController::class, 'reject']);

    // ========================================================================
    // Inventory
    // ========================================================================
    Route::get('/inventory', [Api\InventoryController::class, 'index']);
    Route::get('/inventory/history', [Api\InventoryController::class, 'history']);
    Route::get('/inventory/{inventory}', [Api\InventoryController::class, 'show']);

    // ========================================================================
    // FCM Token Management
    // ========================================================================
    Route::post('/fcm-token', [\App\Http\Controllers\FcmTokenController::class, 'store']);
    Route::delete('/fcm-token', [\App\Http\Controllers\FcmTokenController::class, 'destroy']);

    // ========================================================================
    // Notifications
    // ========================================================================
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'recent']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
    Route::delete('/notifications', [\App\Http\Controllers\NotificationController::class, 'destroyAll']);

    // ========================================================================
    // Contextual Comments (Real-time)
    // ========================================================================
    Route::get('/comments', [Api\CommentController::class, 'index']);
    Route::post('/comments', [Api\CommentController::class, 'store']);
    Route::delete('/comments/{id}', [Api\CommentController::class, 'destroy']);

    // ========================================================================
    // Owner Portal API
    // ========================================================================
    Route::middleware(['owner_portal'])->prefix('owner')->name('api.owner.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\Portal\Owner\DashboardController::class, 'index'])->name('dashboard');
        
        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Api\Portal\Owner\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/recent', [\App\Http\Controllers\Api\Portal\Owner\NotificationController::class, 'recent'])->name('notifications.recent');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\Portal\Owner\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\Portal\Owner\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Api\Portal\Owner\NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // Toggle Publish for Weekly Reports (Available for PM/Admin via API)
    Route::post('/projects/{project}/weekly-reports/{report}/toggle-publish', [Api\WeeklyReportController::class, 'togglePublish']);
});

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
    // RAB (Budget)
    // ========================================================================
    Route::get('/projects/{project}/rab', [Api\RabController::class, 'index']);
    Route::get('/projects/{project}/rab/items/{item}', [Api\RabController::class, 'show']);

    // ========================================================================
    // Schedule
    // ========================================================================
    Route::get('/projects/{project}/schedule', [Api\ScheduleController::class, 'index']);
    Route::get('/projects/{project}/schedule/scurve', [Api\ScheduleController::class, 'scurve']);

    // ========================================================================
    // Progress Reports
    // ========================================================================
    Route::get('/projects/{project}/progress', [Api\ProgressReportController::class, 'index']);
    Route::get('/projects/{project}/progress/{report}', [Api\ProgressReportController::class, 'show']);
    Route::post('/projects/{project}/progress', [Api\ProgressReportController::class, 'store']);

    // ========================================================================
    // Weekly Reports
    // ========================================================================
    Route::get('/projects/{project}/weekly-reports', [Api\WeeklyReportController::class, 'index']);
    Route::post('/projects/{project}/weekly-reports', [Api\WeeklyReportController::class, 'store']);
    Route::post('/projects/{project}/weekly-reports/auto-generate', [Api\WeeklyReportController::class, 'autoGenerate']);
    Route::get('/projects/{project}/weekly-reports/{report}', [Api\WeeklyReportController::class, 'show']);
    Route::delete('/projects/{project}/weekly-reports/{report}', [Api\WeeklyReportController::class, 'destroy']);
    Route::post('/projects/{project}/weekly-reports/{report}/cover', [Api\WeeklyReportController::class, 'updateCover']);
    Route::patch('/projects/{project}/weekly-reports/{report}/cumulative', [Api\WeeklyReportController::class, 'updateCumulative']);
    Route::patch('/projects/{project}/weekly-reports/{report}/detail', [Api\WeeklyReportController::class, 'updateDetail']);
    Route::post('/projects/{project}/weekly-reports/{report}/documentations/upload', [Api\WeeklyReportController::class, 'uploadDocumentation']);
    Route::post('/projects/{project}/weekly-reports/{report}/documentations/progress-photos', [Api\WeeklyReportController::class, 'addProgressPhotos']);
    Route::delete('/projects/{project}/weekly-reports/{report}/documentations', [Api\WeeklyReportController::class, 'removeDocumentation']);
    Route::patch('/projects/{project}/weekly-reports/{report}/activities', [Api\WeeklyReportController::class, 'updateActivities']);

    // ========================================================================
    // Material Requests
    // ========================================================================
    Route::get('/material-requests', [Api\MaterialRequestController::class, 'index']);
    Route::get('/material-requests/{materialRequest}', [Api\MaterialRequestController::class, 'show']);
    Route::post('/material-requests', [Api\MaterialRequestController::class, 'store']);
    Route::post('/material-requests/{materialRequest}/approve', [Api\MaterialRequestController::class, 'approve']);
    Route::post('/material-requests/{materialRequest}/reject', [Api\MaterialRequestController::class, 'reject']);

    // ========================================================================
    // Material Usage
    // ========================================================================
    Route::get('/projects/{project}/material-usages', [Api\MaterialUsageController::class, 'index']);
    Route::post('/projects/{project}/material-usages', [Api\MaterialUsageController::class, 'store']);
    Route::get('/projects/{project}/material-usages/{materialUsage}', [Api\MaterialUsageController::class, 'show']);

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

    // ========================================================================
    // Goods Receipts
    // ========================================================================
    Route::get('/goods-receipts', [Api\GoodsReceiptController::class, 'index']);
    Route::get('/goods-receipts/{goodsReceipt}', [Api\GoodsReceiptController::class, 'show']);
    Route::post('/goods-receipts', [Api\GoodsReceiptController::class, 'store']);

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
});

<?php

use App\Modules\Orders\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    
    // 1) مسارات الطلبات العادية للمستخدمين
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->middleware('etag:private');
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show'])->whereNumber('id')->middleware('etag:private');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->whereNumber('id');
    });

    // 2) مسارات الإدارة الخاصة بالـ Admin (الرابط الصحيح: /api/v1/admin/orders)
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/orders', [OrderController::class, 'adminIndex'])->middleware('etag:private');
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus'])->whereNumber('id');
    });

});

<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Orders\Controllers\OrderController;

Route::prefix('v1')->group(function () {

    // الطلبات المحمية
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/orders', [OrderController::class, 'index']);

        Route::post('/orders', [OrderController::class, 'store']);

        Route::get('/orders/{id}', [OrderController::class, 'show']);

        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

        // مسارات الإدارة
        Route::get('/admin/orders', [OrderController::class, 'adminIndex']);

        Route::patch(
            '/admin/orders/{id}/status',
            [OrderController::class, 'updateStatus']
        );

    });

});

<?php

use App\Modules\Products\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/products')->group(function () {
    // عامة
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show']);
    Route::get('{product}/image', [ProductController::class, 'image']);
});
    // محمية + Admin فقط
    Route::middleware(['auth:sanctum', 'admin'])->prefix('v1/admin/products')->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::post('/{product}', [ProductController::class, 'update']); // POST بسبب رفع صورة
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });

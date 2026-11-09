<?php

use App\Modules\Products\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    // عامة
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show']);

    // محمية + Admin فقط
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::post('/{product}', [ProductController::class, 'update']); // POST بسبب رفع صورة
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });
});
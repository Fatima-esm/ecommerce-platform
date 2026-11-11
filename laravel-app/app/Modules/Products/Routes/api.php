<?php

use App\Modules\Products\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// عامة (+ ETag: ردّ 304 إذا لم تتغير البيانات)
Route::prefix('v1/products')->middleware('etag:public')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show'])->whereNumber('product');
    Route::get('/{product}/image', [ProductController::class, 'image'])->whereNumber('product');
});

// محمية + Admin فقط
Route::middleware(['auth:sanctum', 'admin'])->prefix('v1/admin/products')->group(function () {
    Route::post('/', [ProductController::class, 'store']);
    Route::post('/{product}', [ProductController::class, 'update'])->whereNumber('product'); // POST بسبب رفع صورة
    Route::put('/{product}/stock', [ProductController::class, 'updateStock'])->whereNumber('product');
    Route::delete('/{product}', [ProductController::class, 'destroy'])->whereNumber('product');
});

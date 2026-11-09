<?php

use App\Modules\Notifications\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'throttle:api', 'auth:api'])->group(function () {
    Route::get('v1/notifications', [NotificationController::class, 'index']);
    Route::post('v1/notifications/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
});

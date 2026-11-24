<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;






require base_path('app/Modules/Users/Routes/api.php');
require base_path('app/Modules/Products/Routes/api.php');
require base_path('app/Modules/Orders/Routes/api.php');
require base_path('app/Modules/Notifications/routes.php');
Broadcast::routes(['middleware' => ['auth:sanctum']]);
Route::get('/debug/instance', function () {
    return response()->json([
        'instance' => gethostname(),
    ]);
});
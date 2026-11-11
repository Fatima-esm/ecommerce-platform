<?php

namespace App\Modules\Notifications;

use Illuminate\Support\ServiceProvider;

class NotificationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // جدول user_notifications تابع لوحدة الإشعارات: نحمّل هجرته من داخل الوحدة
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }
}

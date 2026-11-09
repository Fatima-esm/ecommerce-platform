<?php

namespace App\Modules\Notifications;

use App\Modules\Notifications\Listeners\OrderNotificationListener;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class NotificationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // نستمع لأحداث الطلبات العامة فقط (Events)، لا نقرأ جداول الطلبات
        Event::listen(OrderCreated::class, [OrderNotificationListener::class, 'onCreated']);
        Event::listen(OrderStatusChanged::class, [OrderNotificationListener::class, 'onStatusChanged']);
    }
}

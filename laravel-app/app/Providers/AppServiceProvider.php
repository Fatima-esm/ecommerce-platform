<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \App\Modules\Orders\Events\OrderCreated::class,
            [\App\Modules\Notifications\Listeners\OrderNotificationListener::class, 'onCreated']
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Modules\Orders\Events\OrderStatusChanged::class,
            [\App\Modules\Notifications\Listeners\OrderNotificationListener::class, 'onStatusChanged']
        );
    }
    
    }

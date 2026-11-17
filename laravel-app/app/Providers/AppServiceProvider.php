<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderStatusChanged;
use App\Modules\Notifications\Listeners\OrderNotificationListener;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(RabbitMq::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Event::listen(OrderCreated::class,[OrderNotificationListener::class, 'onCreated']);
        Event::listen(OrderStatusChanged::class,[OrderNotificationListener::class, 'onStatusChanged']);
    }
    
    }

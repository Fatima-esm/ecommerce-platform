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
        // كشف مشكلة N+1: أي تحميل كسول لعلاقة يُسجَّل في السجل (بدون كسر الطلب)
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());
        \Illuminate\Database\Eloquent\Model::handleLazyLoadingViolationUsing(
            function ($model, string $relation): void {
                \Illuminate\Support\Facades\Log::warning('N+1: lazy loading detected', [
                    'model' => get_class($model),
                    'relation' => $relation,
                ]);
            }
        );

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

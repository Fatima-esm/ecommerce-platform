<?php

use App\Providers\AppServiceProvider;
use App\Modules\Notifications\NotificationsServiceProvider;
use Illuminate\Foundation\Providers\ArtisanServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\EventServiceProvider;
use VladimirYuldashev\LaravelQueueRabbitMQ\LaravelQueueRabbitMQServiceProvider;

return [
    AppServiceProvider::class,
    NotificationsServiceProvider::class,
    VladimirYuldashev\LaravelQueueRabbitMQ\LaravelQueueRabbitMQServiceProvider::class,
];


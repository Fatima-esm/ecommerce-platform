<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->statefulApi();
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // المتطلب الثالث: رؤوس تشخيصية + ضمان "اقرأ ما كتبته" مع نسخة القراءة
        $middleware->append(\App\Http\Middleware\PerfHeaders::class);
        $middleware->append(\App\Http\Middleware\ReadYourWritesMiddleware::class);
        $middleware->redirectGuestsTo(fn () => null);
        $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        'etag' => \App\Http\Middleware\ETagMiddleware::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn ($request, $e) => true);
        $exceptions->render(fn (Throwable $e, $request) => \App\Support\ApiErrors::fromThrowable($e));
    })->create();

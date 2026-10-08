<?php

use App\Services\TelegramNotifier;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            if (app()->isLocal()) {
                return;
            }

            if ($e instanceof HttpExceptionInterface && in_array($e->getStatusCode(), [404, 429], true)) {
                return;
            }

            $where = app()->runningInConsole() ? 'console (artisan)' : request()->method().' '.request()->fullUrl();

            $text = "<b>\u{1F6A8} ERROR SCREENED</b>\n"
                . "<b>".e(class_basename($e)).":</b> ".e($e->getMessage())."\n"
                . "<b>Lokasi:</b> ".e($e->getFile().':'.$e->getLine())."\n"
                . "<b>Dari:</b> ".e($where)."\n"
                . "<b>Env:</b> ".e(app()->environment())." | <b>Waktu:</b> ".e(now()->toDateTimeString());

            TelegramNotifier::send($text, (int) config('services.telegram.thread_error'));
        });
    })->create();

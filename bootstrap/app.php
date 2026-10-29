<?php

use App\Services\TelegramNotifier;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        // notifikasi pembayaran Midtrans dikirim tanpa token CSRF
        $middleware->validateCsrfTokens(except: [
            'payment/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Token CSRF kedaluwarsa (419) -> jangan halaman kosong, kembalikan ke halaman
        // dengan pesan agar user tinggal mengulangi aksinya.
        // Catatan: TokenMismatchException sudah diubah framework menjadi HttpException(419)
        // sebelum renderable callback dipanggil, jadi yang dicocokkan HttpException status 419.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            $message = 'Sesi keamanan Anda sudah berakhir. Silakan coba sekali lagi; '
                . 'bila terus terjadi, muat ulang halaman lalu ulangi.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 419);
            }

            return redirect()->back()->with('error', $message);
        });

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

            TelegramNotifier::send($text, 3);
        });
    })->create();

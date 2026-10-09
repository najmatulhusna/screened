<?php

namespace App\Providers;

use App\Models\Review;
use App\Observers\ReviewObserver;
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
        Review::observe(ReviewObserver::class);

        // Paksa session berbasis cookie di luar local. Filesystem & database di
        // Wasmer Edge tidak persist antar request sehingga session berbasis
        // file/database gagal memvalidasi token CSRF -> 419 Page Expired.
        if (!app()->isLocal()) {
            config(['session.driver' => 'cookie']);
        }
    }
}

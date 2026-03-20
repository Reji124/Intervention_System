<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\User;

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
        \Illuminate\Auth\Events\Login::class,
        function ($event) {
            // handled by redirectTo below
        }
    );
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Support\HtmlSanitizer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Khởi tạo HTMLPurifier khá nặng => dùng chung một instance cho cả request.
        $this->app->singleton(HtmlSanitizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        // Pagination links use our own simple view (resources/views/vendor/pagination/ecowatch.blade.php).
        Paginator::defaultView('vendor.pagination.ecowatch');
        Schema::defaultStringLength(191);
    }
}

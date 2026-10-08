<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Register any application services. */
    public function register(): void
    {
        // Stateless MVP: no custom bindings required.
    }

    /** Bootstrap any application services. */
    public function boot(): void
    {
        // Stateless MVP: no bootstrapping required.
    }
}

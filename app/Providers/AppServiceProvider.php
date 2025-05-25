<?php

namespace App\Providers;

use App\Models\Lump;
use App\Observers\LumpObserver;
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
        Lump::observe(LumpObserver::class);
    }
}

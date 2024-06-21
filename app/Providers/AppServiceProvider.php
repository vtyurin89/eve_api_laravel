<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

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
        //
    }

    // public function map()
    // {
    //     $this->mapApiRoutes();

    //     $this->mapWebRoutes();

    //     // Other route mappings...
    // }

    // protected function mapApiRoutes()
    // {
    //     Route::prefix('api')
    //         ->middleware('api')
    //         ->group(base_path('routes/api.php'));
    // }

    // protected function mapWebRoutes()
    // {
    //     Route::middleware('web')
    //         ->group(base_path('routes/web.php'));
    // }
}

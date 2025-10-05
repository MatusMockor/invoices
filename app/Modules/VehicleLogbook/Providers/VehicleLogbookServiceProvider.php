<?php

declare(strict_types=1);

namespace App\Modules\VehicleLogbook\Providers;

use App\Modules\VehicleLogbook\Repositories\Interfaces\TripRepository as TripRepositoryContract;
use App\Modules\VehicleLogbook\Repositories\Interfaces\VehicleRepository as VehicleRepositoryContract;
use App\Modules\VehicleLogbook\Repositories\TripRepository;
use App\Modules\VehicleLogbook\Repositories\VehicleRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class VehicleLogbookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(TripRepositoryContract::class, TripRepository::class);
        $this->app->bind(VehicleRepositoryContract::class, VehicleRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register routes with a name prefix to match view expectations
        Route::middleware(['web'])
            ->name('vehiclelogbook.')
            ->group(__DIR__.'/../routes/web.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'vehiclelogbook');

        // Register seeders
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../Database/Seeders/' => database_path('seeders'),
            ], 'vehiclelogbook-seeders');
        }
    }
}

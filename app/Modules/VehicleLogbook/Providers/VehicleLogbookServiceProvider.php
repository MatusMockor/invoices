<?php

namespace App\Modules\VehicleLogbook\Providers;

use App\Modules\VehicleLogbook\Repositories\Interfaces\TripRepository as TripRepositoryContract;
use App\Modules\VehicleLogbook\Repositories\Interfaces\VehicleRepository as VehicleRepositoryContract;
use App\Modules\VehicleLogbook\Repositories\TripRepository;
use App\Modules\VehicleLogbook\Repositories\VehicleRepository;
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
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}

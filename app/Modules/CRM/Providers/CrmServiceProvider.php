<?php

declare(strict_types=1);

namespace App\Modules\CRM\Providers;

use App\Modules\CRM\Repositories\ContactTagRepository;
use App\Modules\CRM\Repositories\CrmContactRepository;
use App\Modules\CRM\Repositories\Interfaces\ContactTagRepository as ContactTagRepositoryContract;
use App\Modules\CRM\Repositories\Interfaces\CrmContactRepository as CrmContactRepositoryContract;
use App\Modules\CRM\Services\CrmContactService;
use App\Modules\CRM\Services\Interfaces\CrmContactService as CrmContactServiceContract;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CrmServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register repositories
        $this->app->bind(CrmContactRepositoryContract::class, CrmContactRepository::class);
        $this->app->bind(ContactTagRepositoryContract::class, ContactTagRepository::class);

        // Register services
        $this->app->bind(CrmContactServiceContract::class, CrmContactService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register routes with a name prefix to match view expectations
        Route::middleware(['web', 'auth'])
            ->name('crm.')
            ->group(__DIR__.'/../routes/web.php');

        // Register API routes
        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('api/crm')
            ->name('api.crm.')
            ->group(__DIR__.'/../routes/api.php');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'crm');

        // Register seeders
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/seeders/' => database_path('seeders'),
            ], 'crm-seeders');
        }
    }
}

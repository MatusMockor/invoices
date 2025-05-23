<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use App\Repositories\BusinessEntityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\Interfaces\BusinessEntityRepository as BusinessEntityRepositoryContract;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Interfaces\InvoiceItemRepository as InvoiceItemRepositoryContract;
use App\Repositories\Interfaces\InvoiceRepository as InvoiceRepositoryContract;
use App\Repositories\Interfaces\TripRepository as TripRepositoryContract;
use App\Repositories\Interfaces\VehicleRepository as VehicleRepositoryContract;
use App\Repositories\InvoiceItemRepository as InvoiceItemRepositoryImpl;
use App\Repositories\InvoiceRepository as InvoiceRepositoryImpl;
use App\Repositories\TripRepository;
use App\Repositories\VehicleRepository;
use App\Services\BusinessEntityDataService as BusinessEntityDataServiceImpl;
use App\Services\CompanyAnalyticsService;
use App\Services\Interfaces\BusinessEntityDataService as BusinessEntityDataServiceContract;
use App\Services\Interfaces\CompanyAnalyticsService as CompanyAnalyticsServiceContract;
use App\Services\Interfaces\InvoicePdfService as InvoicePdfServiceContract;
use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use App\Services\Interfaces\ScraperService as ScraperServiceContract;
use App\Services\InvoicePdfService as InvoicePdfServiceImpl;
use App\Services\PayBySquareService;
use App\Services\ScraperService as ScraperServiceImpl;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repository interfaces with Contract suffix for aliases
        $this->app->bind(BusinessEntityRepositoryContract::class, BusinessEntityRepository::class);
        $this->app->bind(InvoiceRepositoryContract::class, InvoiceRepositoryImpl::class);
        $this->app->bind(InvoiceItemRepositoryContract::class, InvoiceItemRepositoryImpl::class);
        $this->app->bind(CompanyRepositoryContract::class, CompanyRepository::class);
        $this->app->bind(VehicleRepositoryContract::class, VehicleRepository::class);
        $this->app->bind(TripRepositoryContract::class, TripRepository::class);

        // Register service interfaces with Contract suffix for aliases
        $this->app->bind(BusinessEntityDataServiceContract::class, BusinessEntityDataServiceImpl::class);
        $this->app->bind(InvoicePdfServiceContract::class, InvoicePdfServiceImpl::class);
        $this->app->bind(ScraperServiceContract::class, ScraperServiceImpl::class);
        $this->app->bind(PayBySquareContract::class, PayBySquareService::class);
        $this->app->bind(CompanyAnalyticsServiceContract::class, CompanyAnalyticsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the InvoicePolicy for the Invoice model
        Gate::policy(Invoice::class, InvoicePolicy::class);
    }
}

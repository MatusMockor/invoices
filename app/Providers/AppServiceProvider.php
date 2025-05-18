<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Policies\InvoicePolicy;
use App\Repositories\BusinessEntityRepository;
use App\Repositories\Interfaces\BusinessEntityRepository as BusinessEntityRepositoryContract;
use App\Repositories\Interfaces\InvoiceItemRepository as InvoiceItemRepositoryContract;
use App\Repositories\Interfaces\InvoiceRepository as InvoiceRepositoryContract;
use App\Repositories\InvoiceItemRepository as InvoiceItemRepositoryImpl;
use App\Repositories\InvoiceRepository as InvoiceRepositoryImpl;
use App\Services\Interfaces\InvoicePdfService as InvoicePdfServiceContract;
use App\Services\Interfaces\PartnerDataService as BusinessEntityDataServiceContract;
use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use App\Services\Interfaces\ScraperService as ScraperServiceContract;
use App\Services\InvoicePdfService as InvoicePdfServiceImpl;
use App\Services\PartnerDataService as BusinessEntityDataServiceImpl;
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

        // Register service interfaces with Contract suffix for aliases
        $this->app->bind(BusinessEntityDataServiceContract::class, BusinessEntityDataServiceImpl::class);
        $this->app->bind(InvoicePdfServiceContract::class, InvoicePdfServiceImpl::class);
        $this->app->bind(ScraperServiceContract::class, ScraperServiceImpl::class);
        $this->app->bind(PayBySquareContract::class, PayBySquareService::class);
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

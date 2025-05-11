<?php

namespace App\Providers;

use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Repositories\Interfaces\PartnerRepository;
use App\Repositories\InvoiceItemRepository as InvoiceItemRepositoryImpl;
use App\Repositories\InvoiceRepository as InvoiceRepositoryImpl;
use App\Repositories\PartnerRepository as PartnerRepositoryImpl;
use App\Services\Interfaces\InvoicePdfService;
use App\Services\Interfaces\PartnerDataService;
use App\Services\Interfaces\ScraperService;
use App\Services\InvoicePdfService as InvoicePdfServiceImpl;
use App\Services\PartnerDataService as PartnerDataServiceImpl;
use App\Services\ScraperService as ScraperServiceImpl;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repository interfaces with Contract suffix for aliases
        $this->app->bind('PartnerRepositoryContract', PartnerRepositoryImpl::class);
        $this->app->bind('InvoiceRepositoryContract', InvoiceRepositoryImpl::class);
        $this->app->bind('InvoiceItemRepositoryContract', InvoiceItemRepositoryImpl::class);
        $this->app->bind(PartnerRepository::class, PartnerRepositoryImpl::class);
        $this->app->bind(InvoiceRepository::class, InvoiceRepositoryImpl::class);
        $this->app->bind(InvoiceItemRepository::class, InvoiceItemRepositoryImpl::class);

        // Register service interfaces with Contract suffix for aliases
        $this->app->bind('PartnerDataServiceContract', PartnerDataServiceImpl::class);
        $this->app->bind('InvoicePdfServiceContract', InvoicePdfServiceImpl::class);
        $this->app->bind('ScraperServiceContract', ScraperServiceImpl::class);
        $this->app->bind(PartnerDataService::class, PartnerDataServiceImpl::class);
        $this->app->bind(InvoicePdfService::class, InvoicePdfServiceImpl::class);
        $this->app->bind(ScraperService::class, ScraperServiceImpl::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

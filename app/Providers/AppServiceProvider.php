<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Invoice;
use App\Modules\CRM\Models\CrmContact;
use App\Policies\InvoicePolicy;
use App\Repositories\BusinessEntityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\ContactRepository as AppContactRepository;
use App\Repositories\Interfaces\BusinessEntityRepository as BusinessEntityRepositoryContract;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Interfaces\ContactRepository as AppContactRepositoryContract;
use App\Repositories\Interfaces\InvoiceItemRepository as InvoiceItemRepositoryContract;
use App\Repositories\Interfaces\InvoiceRepository as InvoiceRepositoryContract;
use App\Repositories\Interfaces\NoteRepository as NoteRepositoryContract;
use App\Repositories\Interfaces\UserRepository as UserRepositoryContract;
use App\Repositories\InvoiceItemRepository as InvoiceItemRepositoryImpl;
use App\Repositories\InvoiceRepository as InvoiceRepositoryImpl;
use App\Repositories\NoteRepository;
use App\Repositories\UserRepository;
use App\Services\BusinessEntityDataService as BusinessEntityDataServiceImpl;
use App\Services\CompanyAnalyticsService;
use App\Services\Interfaces\BusinessEntityDataService as BusinessEntityDataServiceContract;
use App\Services\Interfaces\CompanyAnalyticsService as CompanyAnalyticsServiceContract;
use App\Services\Interfaces\InvoicePdfService as InvoicePdfServiceContract;
use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use App\Services\Interfaces\ReportService as ReportServiceContract;
use App\Services\Interfaces\ScraperService as ScraperServiceContract;
use App\Services\InvoicePdfService as InvoicePdfServiceImpl;
use App\Services\PayBySquareService;
use App\Services\ReportService;
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
        $this->app->bind(AppContactRepositoryContract::class, AppContactRepository::class);
        $this->app->bind(NoteRepositoryContract::class, NoteRepository::class);
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);

        // Register service interfaces with Contract suffix for aliases
        $this->app->bind(BusinessEntityDataServiceContract::class, BusinessEntityDataServiceImpl::class);
        $this->app->bind(InvoicePdfServiceContract::class, InvoicePdfServiceImpl::class);
        $this->app->bind(ScraperServiceContract::class, ScraperServiceImpl::class);
        $this->app->bind(PayBySquareContract::class, PayBySquareService::class);
        $this->app->bind(CompanyAnalyticsServiceContract::class, CompanyAnalyticsService::class);
        $this->app->bind(ReportServiceContract::class, ReportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the InvoicePolicy for the Invoice model
        Gate::policy(Invoice::class, InvoicePolicy::class);

        // Register the CrmContactPolicy for the CrmContact model
        Gate::policy(CrmContact::class, CrmContactPolicy::class);
    }
}

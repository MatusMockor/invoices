<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Invoice;
use App\Models\UserCompany;
use App\Policies\InvoicePolicy;
use App\Policies\UserCompanyPolicy;
use App\Repositories\BusinessEntityRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanySyncLogRepository;
use App\Repositories\ContactRepository as AppContactRepository;
use App\Repositories\Contracts\BusinessEntityRepository as BusinessEntityRepositoryContract;
use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Repositories\Contracts\CompanySyncLogRepository as CompanySyncLogRepositoryContract;
use App\Repositories\Contracts\ContactRepository as AppContactRepositoryContract;
use App\Repositories\Contracts\EmailWhitelistRepository as EmailWhitelistRepositoryContract;
use App\Repositories\Contracts\InvoiceItemRepository as InvoiceItemRepositoryContract;
use App\Repositories\Contracts\InvoiceRepository as InvoiceRepositoryContract;
use App\Repositories\Contracts\NoteRepository as NoteRepositoryContract;
use App\Repositories\Contracts\UserCompanyRepository as UserCompanyRepositoryContract;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use App\Repositories\EmailWhitelistRepository;
use App\Repositories\InvoiceItemRepository as InvoiceItemRepositoryImpl;
use App\Repositories\InvoiceRepository as InvoiceRepositoryImpl;
use App\Repositories\NoteRepository;
use App\Repositories\UserCompanyRepository;
use App\Repositories\UserRepository;
use App\Services\BusinessEntityDataService as BusinessEntityDataServiceImpl;
use App\Services\CompanyAnalyticsService;
use App\Services\FinancialDataService as FinancialDataServiceImpl;
use App\Services\Interfaces\BusinessEntityDataService as BusinessEntityDataServiceContract;
use App\Services\Interfaces\CompanyAnalyticsService as CompanyAnalyticsServiceContract;
use App\Services\Interfaces\FinancialDataService as FinancialDataServiceContract;
use App\Services\Interfaces\InvoicePdfService as InvoicePdfServiceContract;
use App\Services\Interfaces\OracleCloudStorageService as OracleCloudStorageServiceContract;
use App\Services\Interfaces\PayBySquare as PayBySquareContract;
use App\Services\Interfaces\ReportService as ReportServiceContract;
use App\Services\Interfaces\ScraperService as ScraperServiceContract;
use App\Services\Interfaces\VatService as VatServiceContract;
use App\Services\InvoicePdfService as InvoicePdfServiceImpl;
use App\Services\OracleCloudStorageService;
use App\Services\PayBySquareService;
use App\Services\ReportService;
use App\Services\ScraperService as ScraperServiceImpl;
use App\Services\VatService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Application service provider for binding interfaces to implementations.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
        $this->app->bind(CompanySyncLogRepositoryContract::class, CompanySyncLogRepository::class);
        $this->app->bind(AppContactRepositoryContract::class, AppContactRepository::class);
        $this->app->bind(NoteRepositoryContract::class, NoteRepository::class);
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(UserCompanyRepositoryContract::class, UserCompanyRepository::class);
        $this->app->bind(EmailWhitelistRepositoryContract::class, EmailWhitelistRepository::class);

        // Register service interfaces with Contract suffix for aliases
        $this->app->bind(BusinessEntityDataServiceContract::class, BusinessEntityDataServiceImpl::class);
        $this->app->bind(InvoicePdfServiceContract::class, InvoicePdfServiceImpl::class);
        $this->app->bind(ScraperServiceContract::class, ScraperServiceImpl::class);
        $this->app->bind(PayBySquareContract::class, PayBySquareService::class);
        $this->app->bind(CompanyAnalyticsServiceContract::class, CompanyAnalyticsService::class);
        $this->app->bind(ReportServiceContract::class, ReportService::class);
        $this->app->bind(FinancialDataServiceContract::class, FinancialDataServiceImpl::class);
        $this->app->bind(OracleCloudStorageServiceContract::class, OracleCloudStorageService::class);
        $this->app->bind(VatServiceContract::class, VatService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies for models
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(UserCompany::class, UserCompanyPolicy::class);
    }
}

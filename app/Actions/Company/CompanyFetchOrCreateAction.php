<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepository;
use App\Services\Interfaces\ScraperService;
use Illuminate\Support\Facades\Log;

final class CompanyFetchOrCreateAction
{
    public function __construct(
        private readonly CompanyRepository $companyRepository,
        private readonly ScraperService $scraperService
    ) {}

    /**
     * Find company by ICO or create it (with scraper fallback)
     *
     * @param  array<string, mixed>  $fallbackData  Data to use if scraper fails
     */
    public function handle(string $ico, array $fallbackData = []): Company
    {
        // Try to find existing company
        $company = $this->companyRepository->findByIco($ico);

        if ($company) {
            return $company;
        }

        // Try to fetch from scraper
        $scraperResult = $this->scraperService->fetchCompanyDataByIco($ico);

        if ($scraperResult['success'] ?? false) {
            Log::info('Company data fetched from scraper', ['ico' => $ico]);

            return $this->companyRepository->create($scraperResult['data']);
        }

        // Fallback to provided data
        Log::info('Creating company with fallback data', ['ico' => $ico]);

        return $this->companyRepository->create(array_merge([
            'ico' => $ico,
            'country' => config('invoices.default_country'),
        ], $fallbackData));
    }
}

<?php

namespace App\Services;

use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use App\Services\Interfaces\CompanyAnalyticsService as CompanyAnalyticsServiceContract;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CompanyAnalyticsService implements CompanyAnalyticsServiceContract
{
    public function __construct(
        protected CompanyRepositoryContract $companyRepository
    ) {}

    /**
     * Get total number of companies
     */
    public function getTotalCompanies(): int
    {
        return $this->companyRepository->count();
    }

    /**
     * Get companies grouped by country with count
     */
    public function getCompaniesByCountry(): array
    {
        $companies = $this->companyRepository->getAllOrderedByName();
        $result = [];

        foreach ($companies as $company) {
            $country = $company->country;
            if (! isset($result[$country])) {
                $result[$country] = 0;
            }
            $result[$country]++;
        }

        return $result;
    }

    /**
     * Get companies created per year with count
     */
    public function getCompaniesPerYear(): array
    {
        $companies = $this->companyRepository->getAllOrderedByName();
        $result = [];

        foreach ($companies as $company) {
            $year = Carbon::parse($company->created_at)->year;
            if (! isset($result[$year])) {
                $result[$year] = 0;
            }
            $result[$year]++;
        }

        ksort($result);

        return $result;
    }

    /**
     * Get companies created per month for a specific year with count
     */
    public function getCompaniesPerMonth(int $year): array
    {
        $result = array_fill(1, 12, 0);
        $companies = $this->companyRepository->getByYear($year);

        foreach ($companies as $company) {
            $month = Carbon::parse($company->created_at)->month;
            $result[$month]++;
        }

        return $result;
    }

    /**
     * Get percentage of companies with VAT number
     */
    public function getVatNumberPercentage(): float
    {
        $totalCompanies = $this->getTotalCompanies();

        if ($totalCompanies === 0) {
            return 0;
        }

        $companiesWithVat = $this->companyRepository->getWithVatNumber()->count();

        return round(($companiesWithVat / $totalCompanies) * 100, 2);
    }

    /**
     * Get companies statistics summary
     *
     * Returns an array with various statistics about companies
     *
     * @param  int|null  $currentCompanyId  The ID of the current company, if any
     */
    public function getStatisticsSummary(?int $currentCompanyId = null): array
    {
        $totalCompanies = $this->getTotalCompanies();
        $companiesWithVat = $this->companyRepository->getWithVatNumber()->count();
        $companiesWithoutVat = $this->companyRepository->getWithoutVatNumber()->count();

        $currentYear = Carbon::now()->year;
        $companiesThisYear = $this->companyRepository->getByYear($currentYear)->count();

        $lastYear = $currentYear - 1;
        $companiesLastYear = $this->companyRepository->getByYear($lastYear)->count();

        $yearGrowth = 0;
        if ($companiesLastYear > 0) {
            $yearGrowth = round((($companiesThisYear - $companiesLastYear) / $companiesLastYear) * 100, 2);
        }

        $countriesData = $this->getCompaniesByCountry();
        arsort($countriesData);
        $topCountries = array_slice($countriesData, 0, 5, true);

        $result = [
            'total_companies' => $totalCompanies,
            'companies_with_vat' => $companiesWithVat,
            'companies_without_vat' => $companiesWithoutVat,
            'vat_percentage' => $this->getVatNumberPercentage(),
            'companies_this_year' => $companiesThisYear,
            'companies_last_year' => $companiesLastYear,
            'year_growth_percentage' => $yearGrowth,
            'top_countries' => $topCountries,
            'companies_per_year' => $this->getCompaniesPerYear(),
            'companies_per_month_current_year' => $this->getCompaniesPerMonth($currentYear),
        ];

        // Add income and expense information for the current company if provided
        if ($currentCompanyId) {
            $result['current_company_income'] = $this->getTotalIncomeForCompany($currentCompanyId);
            $result['current_company_expenses'] = $this->getTotalExpensesForCompany($currentCompanyId);
            $result['current_company_balance'] = $result['current_company_income'] - $result['current_company_expenses'];
        }

        return $result;
    }

    /**
     * Get companies created in a date range
     */
    public function getCompaniesByDateRange(string $startDate, string $endDate): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return $this->companyRepository->getAllOrderedByName()->filter(function ($company) use ($start, $end) {
            $createdAt = Carbon::parse($company->created_at);

            return $createdAt->between($start, $end);
        });
    }

    /**
     * Get total income for a specific company
     */
    public function getTotalIncomeForCompany(int $companyId): float
    {
        return $this->companyRepository->getTotalIncome($companyId);
    }

    /**
     * Get total expenses for a specific company
     */
    public function getTotalExpensesForCompany(int $companyId): float
    {
        return $this->companyRepository->getTotalExpenses($companyId);
    }

    /**
     * Get monthly financial data for a specific company
     *
     * Returns an array with monthly income and expenses data
     */
    public function getMonthlyFinancialData(int $companyId, int $year): array
    {
        $monthlyIncome = $this->companyRepository->getMonthlyIncome($companyId, $year);
        $monthlyExpenses = $this->companyRepository->getMonthlyExpenses($companyId, $year);

        $monthNames = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        $result = [
            'labels' => array_values($monthNames),
            'income' => array_values($monthlyIncome),
            'expenses' => array_values($monthlyExpenses),
        ];

        return $result;
    }
}

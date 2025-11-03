<?php

declare(strict_types=1);

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
        return $this->companyRepository->getCountByCountry();
    }

    /**
     * Get companies created per year with count
     */
    public function getCompaniesPerYear(): array
    {
        return $this->companyRepository->getCountByYear();
    }

    /**
     * Get companies created per month for a specific year with count
     */
    public function getCompaniesPerMonth(int $year): array
    {
        return $this->companyRepository->getCountByMonth($year);
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

        $companiesWithVat = $this->companyRepository->countWithVatNumber();

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
        $companiesWithVat = $this->companyRepository->countWithVatNumber();
        $companiesWithoutVat = $this->companyRepository->countWithoutVatNumber();

        $currentYear = Carbon::now()->year;
        $companiesThisYear = $this->companyRepository->countByYear($currentYear);

        $lastYear = $currentYear - 1;
        $companiesLastYear = $this->companyRepository->countByYear($lastYear);

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

        return $this->companyRepository->getByDateRange($start->toDateTimeString(), $end->toDateTimeString());
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

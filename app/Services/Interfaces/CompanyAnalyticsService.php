<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface CompanyAnalyticsService
{
    /**
     * Get total number of companies
     */
    public function getTotalCompanies(): int;

    /**
     * Get companies grouped by country with count
     */
    public function getCompaniesByCountry(): array;

    /**
     * Get companies created per year with count
     */
    public function getCompaniesPerYear(): array;

    /**
     * Get companies created per month for a specific year with count
     */
    public function getCompaniesPerMonth(int $year): array;

    /**
     * Get percentage of companies with VAT number
     */
    public function getVatNumberPercentage(): float;

    /**
     * Get companies statistics summary
     *
     * Returns an array with various statistics about companies
     */
    public function getStatisticsSummary(): array;

    /**
     * Get companies created in a date range
     */
    public function getCompaniesByDateRange(string $startDate, string $endDate): Collection;

    /**
     * Get total income for a specific company
     */
    public function getTotalIncomeForCompany(int $companyId): float;

    /**
     * Get total expenses for a specific company
     */
    public function getTotalExpensesForCompany(int $companyId): float;
}

<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

interface CompanyAnalyticsService
{
    /**
     * Get companies statistics summary
     *
     * Returns an array with various statistics about companies
     *
     * @param  int|null  $currentCompanyId  The ID of the current company, if any
     */
    public function getStatisticsSummary(?int $currentCompanyId = null): array;

    /**
     * Get total income for a specific company
     */
    public function getTotalIncomeForCompany(int $companyId): float;

    /**
     * Get total expenses for a specific company
     */
    public function getTotalExpensesForCompany(int $companyId): float;

    /**
     * Get monthly financial data for a specific company
     *
     * Returns an array with monthly income and expenses data
     */
    public function getMonthlyFinancialData(int $companyId, int $year): array;
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\CompanyRepository as CompanyRepositoryContract;
use App\Services\Interfaces\CompanyAnalyticsService as CompanyAnalyticsServiceContract;

class CompanyAnalyticsService implements CompanyAnalyticsServiceContract
{
    public function __construct(
        protected CompanyRepositoryContract $companyRepository
    ) {}

    /**
     * Get companies statistics summary
     *
     * Returns an array with various statistics about companies
     *
     * @param  int|null  $currentCompanyId  The ID of the current company, if any
     */
    public function getStatisticsSummary(?int $currentCompanyId = null): array
    {
        $result = [];

        // Add income and expense information for the current company if provided
        if ($currentCompanyId) {
            $result['current_company_income'] = $this->getTotalIncomeForCompany($currentCompanyId);
            $result['current_company_expenses'] = $this->getTotalExpensesForCompany($currentCompanyId);
            $result['current_company_balance'] = $result['current_company_income'] - $result['current_company_expenses'];
        }

        return $result;
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

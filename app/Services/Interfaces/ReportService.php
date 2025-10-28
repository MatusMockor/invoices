<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

interface ReportService
{
    /**
     * Get financial report for a company within a date range
     */
    public function getFinancialReport(int $companyId, ?string $startDate = null, ?string $endDate = null): array;

    /**
     * Get invoice summary for a company within a date range
     */
    public function getInvoiceSummary(int $companyId, ?string $startDate = null, ?string $endDate = null): array;
}

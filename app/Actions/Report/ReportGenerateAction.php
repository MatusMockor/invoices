<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\DTOs\ReportDTO;
use App\Services\Interfaces\ReportService as ReportServiceContract;

final class ReportGenerateAction
{
    public function __construct(
        private readonly ReportServiceContract $reportService
    ) {}

    public function handle(int $companyId, ?string $startDate = null, ?string $endDate = null): ReportDTO
    {
        $financialReport = $this->reportService->getFinancialReport($companyId, $startDate, $endDate);
        $invoiceSummary = $this->reportService->getInvoiceSummary($companyId, $startDate, $endDate);

        return ReportDTO::fromService($financialReport, $invoiceSummary);
    }
}

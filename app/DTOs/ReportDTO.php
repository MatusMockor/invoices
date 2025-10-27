<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ReportDTO
{
    public function __construct(
        public array $financial_report,
        public array $invoice_summary,
    ) {}

    public static function fromService(array $financialReport, array $invoiceSummary): self
    {
        return new self(
            financial_report: $financialReport,
            invoice_summary: $invoiceSummary,
        );
    }
}

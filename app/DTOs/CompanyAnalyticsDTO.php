<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class CompanyAnalyticsDTO
{
    public function __construct(
        public array $statistics,
        public array $monthlyData,
        public int $currentYear,
    ) {}

    public static function fromService(array $statistics, array $monthlyData, int $currentYear): self
    {
        return new self(
            statistics: $statistics,
            monthlyData: $monthlyData,
            currentYear: $currentYear,
        );
    }
}

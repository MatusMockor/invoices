<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

final readonly class FinancialDataState
{
    public function __construct(
        public string $extractedFileName,
    ) {}
}

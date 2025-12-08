<?php

declare(strict_types=1);

namespace App\DTOs\Vat;

final readonly class VatSummaryItemDTO
{
    public function __construct(
        public float $rate,
        public float $base,
        public float $vatAmount,
    ) {}
}

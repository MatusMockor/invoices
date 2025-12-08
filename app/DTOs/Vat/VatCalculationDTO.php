<?php

declare(strict_types=1);

namespace App\DTOs\Vat;

final readonly class VatCalculationDTO
{
    public function __construct(
        public float $base,
        public float $vatAmount,
        public float $total,
        public float $vatRate,
    ) {}

    /**
     * Create a zero VAT calculation (for non-VAT payers or reverse charge)
     */
    public static function zero(float $base): self
    {
        return new self(
            base: round($base, 2),
            vatAmount: 0.0,
            total: round($base, 2),
            vatRate: 0.0,
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Vat\VatCalculationDTO;
use App\DTOs\Vat\VatSummaryDTO;
use App\Services\Interfaces\VatCalculator;

/**
 * Service for VAT calculations.
 */
final class VatCalculatorService implements VatCalculator
{
    public function calculateVat(float $amountWithoutVat, float $vatRate): VatCalculationDTO
    {
        $base = round($amountWithoutVat, 2);
        $vatAmount = round($base * ($vatRate / 100), 2);
        $total = round($base + $vatAmount, 2);

        return new VatCalculationDTO(
            base: $base,
            vatAmount: $vatAmount,
            total: $total,
            vatRate: $vatRate,
        );
    }

    public function calculateVatSummary(array $items): VatSummaryDTO
    {
        return VatSummaryDTO::fromItems($items);
    }

    public function calculateVatSummaryForReverseCharge(array $items): VatSummaryDTO
    {
        return VatSummaryDTO::fromItemsWithReverseCharge($items);
    }
}

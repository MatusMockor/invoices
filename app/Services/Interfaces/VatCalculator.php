<?php

declare(strict_types=1);

namespace App\Services\Interfaces;

use App\DTOs\Vat\VatCalculationDTO;
use App\DTOs\Vat\VatSummaryDTO;

interface VatCalculator
{
    /**
     * Calculate VAT from base amount.
     */
    public function calculateVat(float $amountWithoutVat, float $vatRate): VatCalculationDTO;

    /**
     * Calculate VAT summary from invoice items.
     *
     * @param array<int, array{subtotal: float, tax_rate: float, tax_amount: float}> $items
     */
    public function calculateVatSummary(array $items): VatSummaryDTO;

    /**
     * Calculate VAT summary for reverse charge (VAT = 0).
     *
     * @param array<int, array{subtotal: float, tax_rate: float, tax_amount: float}> $items
     */
    public function calculateVatSummaryForReverseCharge(array $items): VatSummaryDTO;
}

<?php

declare(strict_types=1);

namespace App\Services\Invoice;

final class InvoiceTotalCalculatorService
{
    public function __construct(
        private readonly VatCalculatorService $vatCalculator
    ) {}

    /**
     * Calculate the total amount for invoice items including VAT.
     *
     * @deprecated Use calculateTotals() instead for VAT-compliant calculations
     */
    public function calculate(array $items): float
    {
        $totals = $this->calculateTotals($items);

        return $totals['total_amount'];
    }

    /**
     * Calculate invoice totals with VAT breakdown.
     *
     * @param  array  $items  Array of items with quantity, price/unit_price_without_tax, tax_rate
     * @param  float|null  $discountAmount  Invoice-level discount
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function calculateTotals(array $items, ?float $discountAmount = null): array
    {
        return $this->vatCalculator->calculateInvoiceTotals($items, $discountAmount);
    }

    /**
     * Calculate invoice totals with reverse charge (no VAT).
     *
     * @param  array  $items  Array of items with quantity, price/unit_price_without_tax, tax_rate
     * @param  float|null  $discountAmount  Invoice-level discount
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function calculateTotalsWithReverseCharge(array $items, ?float $discountAmount = null): array
    {
        return $this->vatCalculator->calculateInvoiceTotalsWithReverseCharge($items, $discountAmount);
    }
}

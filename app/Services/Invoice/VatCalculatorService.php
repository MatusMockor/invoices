<?php

declare(strict_types=1);

namespace App\Services\Invoice;

final class VatCalculatorService
{
    /**
     * Calculate VAT amount from subtotal and tax rate.
     */
    public function calculateVatAmount(float $subtotal, float $taxRate): float
    {
        return round($subtotal * ($taxRate / 100), 2);
    }

    /**
     * Calculate VAT amount with reverse charge mechanism (always returns 0).
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculateVatAmountWithReverseCharge(float $subtotal, float $taxRate): float
    {
        return 0.0;
    }

    /**
     * Calculate total amount including VAT.
     */
    public function calculateTotalWithVat(float $subtotal, float $taxRate): float
    {
        $vatAmount = $this->calculateVatAmount($subtotal, $taxRate);

        return round($subtotal + $vatAmount, 2);
    }

    /**
     * Calculate total amount with reverse charge (no VAT added).
     */
    public function calculateTotalWithReverseCharge(float $subtotal): float
    {
        return round($subtotal, 2);
    }

    /**
     * Calculate item subtotal (quantity * unit price - discount).
     */
    public function calculateItemSubtotal(float $quantity, float $unitPrice, ?float $discountAmount = null): float
    {
        $subtotal = $quantity * $unitPrice;

        if ($discountAmount !== null) {
            $subtotal -= $discountAmount;
        }

        return round($subtotal, 2);
    }

    /**
     * Calculate item total price (subtotal + VAT).
     */
    public function calculateItemTotal(float $quantity, float $unitPrice, float $taxRate, ?float $discountAmount = null): float
    {
        $subtotal = $this->calculateItemSubtotal($quantity, $unitPrice, $discountAmount);

        return $this->calculateTotalWithVat($subtotal, $taxRate);
    }

    /**
     * Calculate invoice totals from items.
     *
     * @param  array  $items  Array of items with quantity, unit_price_without_tax, tax_rate, discount_amount
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function calculateInvoiceTotals(array $items, ?float $invoiceDiscountAmount = null): array
    {
        return $this->buildInvoiceTotals($items, $invoiceDiscountAmount, reverseCharge: false);
    }

    /**
     * Calculate invoice totals with reverse charge (no VAT).
     *
     * @param  array  $items  Array of items with quantity, unit_price_without_tax, tax_rate, discount_amount
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function calculateInvoiceTotalsWithReverseCharge(array $items, ?float $invoiceDiscountAmount = null): array
    {
        return $this->buildInvoiceTotals($items, $invoiceDiscountAmount, reverseCharge: true);
    }

    /**
     * Build invoice totals calculation.
     *
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    private function buildInvoiceTotals(array $items, ?float $invoiceDiscountAmount, bool $reverseCharge): array
    {
        $invoiceSubtotal = 0.0;
        $invoiceTaxAmount = 0.0;

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 0;
            $unitPrice = $item['unit_price_without_tax'] ?? $item['price'] ?? 0;
            // Use 20.0 as default if tax_rate is not set (Slovak standard VAT rate), allows explicit 0% when provided
            $taxRate = isset($item['tax_rate']) ? (float) $item['tax_rate'] : 20.0;
            $itemDiscount = $item['discount_amount'] ?? null;

            $itemSubtotal = $this->calculateItemSubtotal($quantity, $unitPrice, $itemDiscount);
            $itemTaxAmount = $reverseCharge
                ? $this->calculateVatAmountWithReverseCharge($itemSubtotal, $taxRate)
                : $this->calculateVatAmount($itemSubtotal, $taxRate);

            $invoiceSubtotal += $itemSubtotal;
            $invoiceTaxAmount += $itemTaxAmount;
        }

        // Apply invoice-level discount if present
        if ($invoiceDiscountAmount !== null) {
            $invoiceSubtotal -= $invoiceDiscountAmount;
            $invoiceSubtotal = max(0, $invoiceSubtotal); // Prevent negative subtotal
        }

        $invoiceSubtotal = round($invoiceSubtotal, 2);
        $invoiceTaxAmount = round($invoiceTaxAmount, 2);
        $totalAmount = round($invoiceSubtotal + $invoiceTaxAmount, 2);

        return [
            'subtotal' => $invoiceSubtotal,
            'tax_amount' => $invoiceTaxAmount,
            'total_amount' => $totalAmount,
        ];
    }
}

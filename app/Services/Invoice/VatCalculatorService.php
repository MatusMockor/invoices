<?php

declare(strict_types=1);

namespace App\Services\Invoice;

final class VatCalculatorService
{
    /**
     * Calculate VAT amount from subtotal and tax rate.
     *
     * @param  bool  $reverseCharge  If true, VAT is not calculated (reverse charge mechanism)
     */
    public function calculateVatAmount(float $subtotal, float $taxRate, bool $reverseCharge = false): float
    {
        if ($reverseCharge) {
            return 0.0;
        }

        return round($subtotal * ($taxRate / 100), 2);
    }

    /**
     * Calculate total amount including VAT.
     *
     * @param  bool  $reverseCharge  If true, total equals subtotal (no VAT)
     */
    public function calculateTotalWithVat(float $subtotal, float $taxRate, bool $reverseCharge = false): float
    {
        $vatAmount = $this->calculateVatAmount($subtotal, $taxRate, $reverseCharge);

        return round($subtotal + $vatAmount, 2);
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
     * @param  bool  $reverseCharge  If true, VAT is not calculated
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function calculateInvoiceTotals(array $items, ?float $invoiceDiscountAmount = null, bool $reverseCharge = false): array
    {
        $invoiceSubtotal = 0.0;
        $invoiceTaxAmount = 0.0;

        foreach ($items as $item) {
            $quantity = $item['quantity'] ?? 0;
            $unitPrice = $item['unit_price_without_tax'] ?? $item['price'] ?? 0;
            $taxRate = $item['tax_rate'] ?? 20.0;
            $itemDiscount = $item['discount_amount'] ?? null;

            $itemSubtotal = $this->calculateItemSubtotal($quantity, $unitPrice, $itemDiscount);
            $itemTaxAmount = $this->calculateVatAmount($itemSubtotal, $taxRate, $reverseCharge);

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

<?php

declare(strict_types=1);

namespace App\Services\Invoice;

final class InvoiceTotalCalculatorService
{
    /**
     * Calculate the total amount for invoice items.
     */
    public function calculate(array $items): float
    {
        $totalAmount = 0.0;

        foreach ($items as $item) {
            $unitPrice = $item['price'] ?? $item['unit_price'] ?? 0;
            $itemTotal = $item['quantity'] * $unitPrice;
            $totalAmount += $itemTotal;
        }

        return $totalAmount;
    }
}

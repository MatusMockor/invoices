<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Actions\Invoice\InvoiceItemsUpdateHandler;
use App\Models\Invoice;

/**
 * Coordinates invoice items processing: calculation and persistence.
 *
 * Groups related services to reduce constructor dependencies in Actions.
 */
final readonly class InvoiceItemsProcessorService
{
    public function __construct(
        private InvoiceTotalCalculatorService $totalCalculator,
        private InvoiceItemsUpdateHandler $itemsHandler
    ) {}

    /**
     * Calculate totals for invoice items.
     *
     * @param  array  $items  Items with tax rate
     * @param  float|null  $discountAmount  Optional discount
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function calculateTotals(array $items, ?float $discountAmount = null): array
    {
        return $this->totalCalculator->calculateTotals($items, $discountAmount);
    }

    /**
     * Calculate totals with reverse charge (no VAT).
     *
     * @param  array  $items  Items with tax rate
     * @param  float|null  $discountAmount  Optional discount
     * @return array{subtotal: float, tax_amount: float, total_amount: float}
     */
    public function calculateTotalsWithReverseCharge(array $items, ?float $discountAmount = null): array
    {
        return $this->totalCalculator->calculateTotalsWithReverseCharge($items, $discountAmount);
    }

    /**
     * Update invoice items - delete removed, update existing, create new.
     */
    public function updateItems(Invoice $invoice, array $items): void
    {
        $this->itemsHandler->updateItems($invoice, $items);
    }
}

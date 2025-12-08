<?php

declare(strict_types=1);

namespace App\DTOs\Vat;

use Illuminate\Support\Collection;

final readonly class VatSummaryDTO
{
    /**
     * @param  Collection<int, VatSummaryItemDTO>  $items
     */
    public function __construct(
        public Collection $items,
        public float $totalBase,
        public float $totalVat,
        public float $grandTotal,
    ) {}

    /**
     * Create from invoice items grouped by VAT rate
     */
    public static function fromItems(array $items, bool $reverseCharge = false): self
    {
        $grouped = collect($items)->groupBy('tax_rate');

        $summaryItems = $grouped->map(function ($groupedItems, $rate) use ($reverseCharge) {
            $base = $groupedItems->sum('subtotal');
            $vatAmount = $reverseCharge ? 0.0 : $groupedItems->sum('tax_amount');

            return new VatSummaryItemDTO(
                rate: (float) $rate,
                base: round($base, 2),
                vatAmount: round($vatAmount, 2),
            );
        })->values();

        return new self(
            items: $summaryItems,
            totalBase: round($summaryItems->sum('base'), 2),
            totalVat: round($summaryItems->sum('vatAmount'), 2),
            grandTotal: round($summaryItems->sum('base') + $summaryItems->sum('vatAmount'), 2),
        );
    }
}

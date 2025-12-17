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
    public static function fromItems(array $items): self
    {
        return self::createFromItems($items, includeVat: true);
    }

    /**
     * Create from invoice items with reverse charge (VAT = 0)
     */
    public static function fromItemsWithReverseCharge(array $items): self
    {
        return self::createFromItems($items, includeVat: false);
    }

    private static function createFromItems(array $items, bool $includeVat): self
    {
        $grouped = collect($items)->groupBy('tax_rate');

        $summaryItems = $grouped->map(function ($groupedItems, $rate) use ($includeVat) {
            $base = $groupedItems->sum('subtotal');
            $vatAmount = $includeVat ? $groupedItems->sum('tax_amount') : 0.0;

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

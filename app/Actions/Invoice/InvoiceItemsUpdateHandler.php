<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceItemRepository;
use App\Services\Invoice\VatCalculatorService;
use Illuminate\Support\Arr;

/**
 * Handles invoice items update operations.
 */
final class InvoiceItemsUpdateHandler
{
    public function __construct(
        private readonly InvoiceItemRepository $invoiceItemRepository,
        private readonly VatCalculatorService $vatCalculator
    ) {}

    /**
     * Update invoice items - delete removed, update existing, create new.
     */
    public function updateItems(Invoice $invoice, array $items): void
    {
        [$existingItems, $newItems] = $this->separateItems($invoice, $items);
        $this->deleteRemovedItems($invoice, $existingItems);
        $this->updateExistingItems($existingItems);
        $this->createNewItems($newItems);
    }

    /**
     * Separate items into existing and new items.
     *
     * @return array{0: array, 1: array}
     */
    private function separateItems(Invoice $invoice, array $items): array
    {
        $reverseCharge = $invoice->reverse_charge ?? false;

        return $reverseCharge
            ? $this->separateItemsWithReverseCharge($invoice, $items)
            : $this->separateItemsWithVat($invoice, $items);
    }

    /**
     * Separate items with standard VAT calculation.
     *
     * @return array{0: array, 1: array}
     */
    private function separateItemsWithVat(Invoice $invoice, array $items): array
    {
        $existingItems = [];
        $newItems = [];

        foreach ($items as $item) {
            $itemData = $this->buildItemDataWithVat($invoice, $item);

            if (! isset($item['id'])) {
                $newItems[] = $itemData;

                continue;
            }

            $itemData['id'] = $item['id'];
            $existingItems[] = $itemData;
        }

        return [$existingItems, $newItems];
    }

    /**
     * Separate items with reverse charge (no VAT).
     *
     * @return array{0: array, 1: array}
     */
    private function separateItemsWithReverseCharge(Invoice $invoice, array $items): array
    {
        $existingItems = [];
        $newItems = [];

        foreach ($items as $item) {
            $itemData = $this->buildItemDataWithReverseCharge($invoice, $item);

            if (! isset($item['id'])) {
                $newItems[] = $itemData;

                continue;
            }

            $itemData['id'] = $item['id'];
            $existingItems[] = $itemData;
        }

        return [$existingItems, $newItems];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildItemDataWithVat(Invoice $invoice, array $item): array
    {
        $quantity = $item['quantity'];
        $unitPriceWithoutTax = $item['price'] ?? $item['unit_price_without_tax'] ?? 0;
        $taxRate = $item['tax_rate'] ?? 20.0;
        $discountAmount = $item['discount_amount'] ?? null;

        $subtotal = $this->vatCalculator->calculateItemSubtotal($quantity, $unitPriceWithoutTax, $discountAmount);
        $taxAmount = $this->vatCalculator->calculateVatAmount($subtotal, $taxRate);
        $totalPrice = $subtotal + $taxAmount;

        return [
            'invoice_id' => $invoice->id,
            'description' => $item['description'],
            'quantity' => $quantity,
            'unit_price_without_tax' => $unitPriceWithoutTax,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildItemDataWithReverseCharge(Invoice $invoice, array $item): array
    {
        $quantity = $item['quantity'];
        $unitPriceWithoutTax = $item['price'] ?? $item['unit_price_without_tax'] ?? 0;
        $taxRate = $item['tax_rate'] ?? 20.0;
        $discountAmount = $item['discount_amount'] ?? null;

        $subtotal = $this->vatCalculator->calculateItemSubtotal($quantity, $unitPriceWithoutTax, $discountAmount);
        $taxAmount = $this->vatCalculator->calculateVatAmountWithReverseCharge($subtotal, $taxRate);
        $totalPrice = $subtotal + $taxAmount;

        return [
            'invoice_id' => $invoice->id,
            'description' => $item['description'],
            'quantity' => $quantity,
            'unit_price_without_tax' => $unitPriceWithoutTax,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_price' => $totalPrice,
        ];
    }

    private function deleteRemovedItems(Invoice $invoice, array $existingItems): void
    {
        $itemIds = Arr::pluck($existingItems, 'id');
        $this->invoiceItemRepository->deleteItemsNotInIds($invoice->id, $itemIds);
    }

    private function updateExistingItems(array $existingItems): void
    {
        if (empty($existingItems)) {
            return;
        }

        $this->invoiceItemRepository->upsert(
            $existingItems,
            ['id'],
            ['description', 'quantity', 'unit_price_without_tax', 'tax_rate', 'tax_amount', 'subtotal', 'discount_amount', 'total_price']
        );
    }

    private function createNewItems(array $newItems): void
    {
        if (empty($newItems)) {
            return;
        }

        foreach ($newItems as $newItem) {
            $this->invoiceItemRepository->create($newItem);
        }
    }
}

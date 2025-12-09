<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\UserCompany;
use App\Repositories\Contracts\InvoiceItemRepository;
use App\Repositories\Contracts\InvoiceRepository;
use App\Services\Interfaces\VatService;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use App\Services\Invoice\VatCalculatorService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class InvoiceUpdateAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceItemRepository $invoiceItemRepository,
        private readonly CompanyFetchOrCreateAction $companyFetchOrCreate,
        private readonly InvoiceTotalCalculatorService $totalCalculator,
        private readonly VatCalculatorService $vatCalculator,
        private readonly VatService $vatService
    ) {}

    public function handle(Invoice $invoice, InvoiceUpdateDTO $dto, int $supplierCompanyId): Invoice
    {
        return DB::transaction(function () use ($invoice, $dto, $supplierCompanyId) {
            // Fetch supplier company to check VAT payer status
            $supplierCompany = UserCompany::find($supplierCompanyId);

            // Get VAT status at invoice issue date for historical accuracy
            $issueDate = Carbon::parse($dto->issueDate ?? $invoice->issue_date);
            $vatStatus = $supplierCompany
                ? $this->vatService->getVatStatusAtDate($supplierCompany, $issueDate)
                : null;

            // Determine if supplier is a VAT payer - force tax_rate to 0 if not
            $isVatPayer = $vatStatus?->status->isVatPayer() ?? false;

            // Force tax_rate to 0 for non-VAT payers (always update if not VAT payer)
            // This handles the edge case when company changes from VAT payer to non-VAT payer
            $taxRate = $isVatPayer
                ? $dto->taxRate  // Keep as null if not provided (won't update existing value)
                : 0.0;          // Always force to 0 for non-VAT payers

            $updateData = array_filter([
                'supplier_company_id' => $supplierCompanyId,
                'invoice_number' => $dto->invoiceNumber,
                'issue_date' => $dto->issueDate,
                'due_date' => $dto->dueDate,
                'delivery_date' => $dto->deliveryDate,
                'tax_rate' => $taxRate,
                'discount_amount' => $dto->discountAmount,
                'discount_percentage' => $dto->discountPercentage,
                'reverse_charge' => $dto->reverseCharge,
                'tax_exemption_reason' => $dto->taxExemptionReason,
                'special_text' => $dto->specialText,
                'notes' => $dto->notes,
                'currency' => $dto->currency,
                'variable_symbol' => $dto->variableSymbol,
                'constant_symbol' => $dto->constantSymbol,
                'specific_symbol' => $dto->specificSymbol,
                'status' => $dto->status,
            ], static fn (mixed $value): bool => $value !== null);

            // Handle custom company case explicitly (useCustomCompany === true)
            if ($dto->useCustomCompany === true) {
                $updateData = array_merge($updateData, [
                    'company_id' => null,
                    'company_ico' => $dto->customCompanyIco,
                    'company_dic' => $dto->customCompanyDic,
                    'company_ic_dph' => $dto->customCompanyIcDph,
                    'company_name' => $dto->customCompanyName,
                    'company_address' => $dto->customCompanyAddress,
                    'company_city' => $dto->customCompanyCity,
                    'company_zip' => $dto->customCompanyZip,
                    'company_country' => $dto->customCompanyCountry,
                ]);

                if ($dto->items !== null) {
                    $reverseCharge = $dto->reverseCharge ?? $invoice->reverse_charge ?? false;
                    $itemsForCalculation = $this->prepareItemsWithTaxRate($dto->items, $isVatPayer, [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $dto->invoiceNumber ?? $invoice->invoice_number,
                        'supplier_id' => $supplierCompanyId,
                        'user_id' => $invoice->user_id,
                    ]);
                    $totals = $this->totalCalculator->calculateTotals($itemsForCalculation, $dto->discountAmount, $reverseCharge);
                    $updateData['subtotal'] = $totals['subtotal'];
                    $updateData['tax_amount'] = $totals['tax_amount'];
                    $updateData['total_amount'] = $totals['total_amount'];
                    $this->updateInvoiceItems($invoice, $itemsForCalculation);
                }

                $this->invoiceRepository->update($invoice, $updateData);

                return $invoice->fresh()->load(['company', 'items']);
            }

            // Handle standard company case explicitly (useCustomCompany === false)
            // Only update company fields if client data is provided
            if ($dto->useCustomCompany === false && $dto->clientIco !== null) {
                $customerCompany = $this->findOrCreateCompany($dto);

                $updateData = array_merge($updateData, [
                    'company_id' => $customerCompany->id,
                    'company_ico' => $customerCompany->ico,
                    'company_dic' => $customerCompany->dic,
                    'company_ic_dph' => $customerCompany->ic_dph,
                    'company_name' => $customerCompany->name,
                    'company_address' => $customerCompany->street,
                    'company_city' => $customerCompany->city,
                    'company_zip' => $customerCompany->postal_code,
                    'company_country' => $customerCompany->country,
                ]);
            }

            // If useCustomCompany is null, company fields remain unchanged
            // Handle items update regardless of company field changes
            if ($dto->items !== null) {
                $reverseCharge = $dto->reverseCharge ?? $invoice->reverse_charge ?? false;
                $itemsForCalculation = $this->prepareItemsWithTaxRate($dto->items, $isVatPayer, [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $dto->invoiceNumber ?? $invoice->invoice_number,
                    'supplier_id' => $supplierCompanyId,
                    'user_id' => $invoice->user_id,
                ]);
                $totals = $this->totalCalculator->calculateTotals($itemsForCalculation, $dto->discountAmount, $reverseCharge);
                $updateData['subtotal'] = $totals['subtotal'];
                $updateData['tax_amount'] = $totals['tax_amount'];
                $updateData['total_amount'] = $totals['total_amount'];
                $this->updateInvoiceItems($invoice, $itemsForCalculation);
            }

            $this->invoiceRepository->update($invoice, $updateData);

            return $invoice->fresh()->load(['company', 'items']);
        });
    }

    private function findOrCreateCompany(InvoiceUpdateDTO $dto): Company
    {
        return $this->companyFetchOrCreate->handle($dto->clientIco, [
            'name' => $dto->clientName,
            'dic' => $dto->clientDic,
            'ic_dph' => $dto->clientIcDph,
            'street' => $dto->clientStreet,
            'city' => $dto->clientCity,
            'postal_code' => $dto->clientPostalCode,
            'country' => $dto->clientCountry ?? config('invoices.default_country'),
        ]);
    }

    /**
     * Prepare items with corrected tax_rate for non-VAT payers.
     *
     * @param  array  $items  Original items from DTO
     * @param  bool  $isVatPayer  Whether the supplier is a VAT payer
     * @param  array  $context  Context for logging (invoice_id, invoice_number, supplier_id, user_id)
     * @return array Items with corrected tax_rate
     */
    private function prepareItemsWithTaxRate(array $items, bool $isVatPayer, array $context = []): array
    {
        return array_map(static function (array $item) use ($isVatPayer, $context): array {
            $originalTaxRate = $item['tax_rate'] ?? null;

            // Force tax_rate to 0 for non-VAT payers regardless of frontend value
            $item['tax_rate'] = $isVatPayer ? ($originalTaxRate ?? 20.0) : 0.0;

            // Log warning when overriding non-zero tax_rate for non-VAT payer
            if (! $isVatPayer && $originalTaxRate !== null && $originalTaxRate > 0) {
                Log::warning('VAT override: Forcing tax_rate to 0 for non-VAT payer', [
                    'invoice_id' => $context['invoice_id'] ?? null,
                    'invoice_number' => $context['invoice_number'] ?? null,
                    'supplier_id' => $context['supplier_id'] ?? null,
                    'user_id' => $context['user_id'] ?? null,
                    'original_tax_rate' => $originalTaxRate,
                    'item_description' => $item['description'] ?? 'N/A',
                ]);
            }

            return $item;
        }, $items);
    }

    private function updateInvoiceItems(Invoice $invoice, array $items): void
    {
        [$existingItems, $newItems] = $this->separateItems($invoice, $items);
        $this->deleteRemovedItems($invoice, $existingItems);
        $this->updateExistingItems($existingItems);
        $this->createNewItems($newItems);
    }

    /**
     * Separate items into existing and new items
     *
     * @return array{0: array, 1: array}
     */
    private function separateItems(Invoice $invoice, array $items): array
    {
        $existingItems = [];
        $newItems = [];

        // Use invoice's reverse_charge setting
        $reverseCharge = $invoice->reverse_charge ?? false;

        foreach ($items as $item) {
            $quantity = $item['quantity'];
            $unitPriceWithoutTax = $item['price'] ?? $item['unit_price_without_tax'] ?? 0;
            $taxRate = $item['tax_rate'] ?? 20.0;
            $discountAmount = $item['discount_amount'] ?? null;

            // Calculate item subtotal (without VAT)
            $subtotal = $this->vatCalculator->calculateItemSubtotal($quantity, $unitPriceWithoutTax, $discountAmount);

            // Calculate VAT amount (respect reverse charge)
            $taxAmount = $this->vatCalculator->calculateVatAmount($subtotal, $taxRate, $reverseCharge);

            // Calculate total price (with or without VAT based on reverse charge)
            $totalPrice = $subtotal + $taxAmount;

            $itemData = [
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

            if (isset($item['id'])) {
                $itemData['id'] = $item['id'];
                $existingItems[] = $itemData;
            } else {
                $newItems[] = $itemData;
            }
        }

        return [$existingItems, $newItems];
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

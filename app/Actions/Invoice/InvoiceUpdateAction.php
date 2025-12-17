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

/**
 * Action for updating existing invoices with all related data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
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
        return DB::transaction(function () use ($invoice, $dto, $supplierCompanyId): Invoice {
            $isVatPayer = $this->determineVatPayerStatus($supplierCompanyId, $dto->issueDate ?? $invoice->issue_date);
            $updateData = $this->buildBaseUpdateData($dto, $supplierCompanyId, $isVatPayer);

            $updateData = $this->applyCompanyData($updateData, $dto, $invoice, $isVatPayer, $supplierCompanyId);

            $this->invoiceRepository->update($invoice, $updateData);

            return $invoice->fresh()->load(['company', 'items']);
        });
    }

    private function determineVatPayerStatus(int $supplierCompanyId, string|Carbon $issueDate): bool
    {
        $supplierCompany = UserCompany::find($supplierCompanyId);
        $issueDateParsed = $issueDate instanceof Carbon ? $issueDate : Carbon::parse($issueDate);
        $vatStatus = $supplierCompany
            ? $this->vatService->getVatStatusAtDate($supplierCompany, $issueDateParsed)
            : null;

        return $vatStatus?->status->isVatPayer() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildBaseUpdateData(InvoiceUpdateDTO $dto, int $supplierCompanyId, bool $isVatPayer): array
    {
        // Force tax_rate to 0 for non-VAT payers (handles edge case when company changes VAT status)
        $taxRate = $isVatPayer ? $dto->taxRate : 0.0;

        return array_filter([
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
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    private function applyCompanyData(
        array $updateData,
        InvoiceUpdateDTO $dto,
        Invoice $invoice,
        bool $isVatPayer,
        int $supplierCompanyId
    ): array {
        if ($dto->useCustomCompany === true) {
            $updateData = $this->applyCustomCompanyData($updateData, $dto);

            return $this->applyItemsData($updateData, $dto, $invoice, $isVatPayer, $supplierCompanyId);
        }

        if ($dto->useCustomCompany === false && $dto->clientIco !== null) {
            $updateData = $this->applyStandardCompanyData($updateData, $dto);
        }

        return $this->applyItemsData($updateData, $dto, $invoice, $isVatPayer, $supplierCompanyId);
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    private function applyCustomCompanyData(array $updateData, InvoiceUpdateDTO $dto): array
    {
        return array_merge($updateData, [
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
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    private function applyStandardCompanyData(array $updateData, InvoiceUpdateDTO $dto): array
    {
        $customerCompany = $this->findOrCreateCompany($dto);

        return array_merge($updateData, [
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

    /**
     * @param  array<string, mixed>  $updateData
     * @return array<string, mixed>
     */
    private function applyItemsData(
        array $updateData,
        InvoiceUpdateDTO $dto,
        Invoice $invoice,
        bool $isVatPayer,
        int $supplierCompanyId
    ): array {
        if ($dto->items === null) {
            return $updateData;
        }

        $reverseCharge = $dto->reverseCharge ?? $invoice->reverse_charge ?? false;
        $itemsForCalculation = $this->prepareItemsWithTaxRate($dto->items, $isVatPayer, [
            'invoice_id' => $invoice->id,
            'invoice_number' => $dto->invoiceNumber ?? $invoice->invoice_number,
            'supplier_id' => $supplierCompanyId,
            'user_id' => $invoice->user_id,
        ]);

        $totals = $reverseCharge
            ? $this->totalCalculator->calculateTotalsWithReverseCharge($itemsForCalculation, $dto->discountAmount)
            : $this->totalCalculator->calculateTotals($itemsForCalculation, $dto->discountAmount);
        $updateData['subtotal'] = $totals['subtotal'];
        $updateData['tax_amount'] = $totals['tax_amount'];
        $updateData['total_amount'] = $totals['total_amount'];

        $this->updateInvoiceItems($invoice, $itemsForCalculation);

        return $updateData;
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

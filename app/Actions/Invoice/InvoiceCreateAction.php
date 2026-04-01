<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\DTOs\Invoice\InvoiceCreateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\UserCompany;
use App\Repositories\Contracts\InvoiceItemRepository;
use App\Repositories\Contracts\InvoiceRepository;
use App\Services\Interfaces\VatService;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use App\Services\Invoice\VatCalculatorService;
use App\Support\InvoicePartySnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Action for creating new invoices with all related data.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
final class InvoiceCreateAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceItemRepository $invoiceItemRepository,
        private readonly CompanyFetchOrCreateAction $companyFetchOrCreate,
        private readonly InvoiceTotalCalculatorService $totalCalculator,
        private readonly VatCalculatorService $vatCalculator,
        private readonly VatService $vatService
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(InvoiceCreateDTO $dto, int $userId, int $supplierCompanyId): Invoice
    {
        return DB::transaction(function () use ($dto, $userId, $supplierCompanyId): Invoice {
            $supplierCompany = UserCompany::find($supplierCompanyId);
            $vatStatus = $this->getVatStatusForSupplier($supplierCompany, $dto->issueDate);
            $isVatPayer = $vatStatus?->status->isVatPayer() ?? false;

            $itemsForCalculation = $this->prepareItemsWithTaxRate($dto->items, $isVatPayer, [
                'user_id' => $userId,
                'supplier_id' => $supplierCompanyId,
                'invoice_number' => $dto->invoiceNumber,
            ]);

            $totals = $dto->reverseCharge
                ? $this->totalCalculator->calculateTotalsWithReverseCharge($itemsForCalculation, $dto->discountAmount ?? null)
                : $this->totalCalculator->calculateTotals($itemsForCalculation, $dto->discountAmount ?? null);
            $supplierSnapshot = InvoicePartySnapshot::supplierFromUserCompany($supplierCompany, $vatStatus);
            $invoiceData = $this->buildInvoiceData($dto, $userId, $supplierCompanyId, $totals, $isVatPayer);
            $invoiceData = $this->applyCompanyDataToInvoice($invoiceData, $dto, $supplierSnapshot);

            $invoice = $this->invoiceRepository->create($invoiceData);
            $this->createInvoiceItems($invoice, $itemsForCalculation, $dto->reverseCharge);

            return $invoice->load(['items']);
        });
    }

    private function getVatStatusForSupplier(?UserCompany $supplierCompany, string $issueDate): ?object
    {
        if (! $supplierCompany) {
            return null;
        }

        return $this->vatService->getVatStatusAtDate($supplierCompany, Carbon::parse($issueDate));
    }

    /**
     * @param  array{subtotal: float, tax_amount: float, total_amount: float}  $totals
     * @return array<string, mixed>
     */
    private function buildInvoiceData(
        InvoiceCreateDTO $dto,
        int $userId,
        int $supplierCompanyId,
        array $totals,
        bool $isVatPayer
    ): array {
        $taxRate = $isVatPayer ? ($dto->taxRate ?? 20.0) : 0.0;

        return [
            'invoice_number' => $dto->invoiceNumber,
            'user_id' => $userId,
            'issue_date' => $dto->issueDate,
            'due_date' => $dto->dueDate,
            'delivery_date' => $dto->deliveryDate,
            'supplier_company_id' => $supplierCompanyId,
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'tax_rate' => $taxRate,
            'total_amount' => $totals['total_amount'],
            'discount_amount' => $dto->discountAmount,
            'discount_percentage' => $dto->discountPercentage,
            'reverse_charge' => $dto->reverseCharge,
            'tax_exemption_reason' => $dto->taxExemptionReason,
            'special_text' => $dto->specialText,
            'notes' => $dto->notes,
            'currency' => $dto->currency ?? config('invoices.default_currency'),
            'variable_symbol' => $dto->variableSymbol,
            'constant_symbol' => $dto->constantSymbol,
            'specific_symbol' => $dto->specificSymbol,
            'status' => $dto->status,
        ];
    }

    /**
     * @param  array<string, mixed>  $invoiceData
     * @param  array<string, mixed>  $supplierSnapshot
     * @return array<string, mixed>
     */
    private function applyCompanyDataToInvoice(array $invoiceData, InvoiceCreateDTO $dto, array $supplierSnapshot): array
    {
        if ($dto->useCustomCompany) {
            return $this->applyCustomCompanyData($invoiceData, $dto, $supplierSnapshot);
        }

        return $this->applyStandardCompanyData($invoiceData, $dto, $supplierSnapshot);
    }

    /**
     * @param  array<string, mixed>  $invoiceData
     * @param  array<string, mixed>  $supplierSnapshot
     * @return array<string, mixed>
     */
    private function applyCustomCompanyData(array $invoiceData, InvoiceCreateDTO $dto, array $supplierSnapshot): array
    {
        $invoiceData['company_id'] = null;
        $invoiceData['party_snapshot'] = InvoicePartySnapshot::make(
            $supplierSnapshot,
            InvoicePartySnapshot::customerFromArray([
                'ico' => $dto->customCompanyIco,
                'dic' => $dto->customCompanyDic,
                'ic_dph' => $dto->customCompanyIcDph,
                'name' => $dto->customCompanyName,
                'street' => $dto->customCompanyAddress,
                'city' => $dto->customCompanyCity,
                'postal_code' => $dto->customCompanyZip,
                'country' => $dto->customCompanyCountry,
            ])
        );

        return $invoiceData;
    }

    /**
     * @param  array<string, mixed>  $invoiceData
     * @param  array<string, mixed>  $supplierSnapshot
     * @return array<string, mixed>
     */
    private function applyStandardCompanyData(array $invoiceData, InvoiceCreateDTO $dto, array $supplierSnapshot): array
    {
        $customerCompany = $this->findOrCreateCompany($dto);

        $invoiceData['company_id'] = $customerCompany->id;
        $invoiceData['party_snapshot'] = InvoicePartySnapshot::make(
            $supplierSnapshot,
            $this->buildStandardCustomerSnapshot($dto)
        );

        return $invoiceData;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStandardCustomerSnapshot(InvoiceCreateDTO $dto): array
    {
        return InvoicePartySnapshot::customerFromArray([
            'ico' => $dto->clientIco,
            'dic' => $dto->clientDic,
            'ic_dph' => $dto->clientIcDph,
            'name' => $dto->clientName,
            'street' => $dto->clientStreet,
            'city' => $dto->clientCity,
            'postal_code' => $dto->clientPostalCode,
            'country' => $dto->clientCountry ?? config('invoices.default_country'),
        ]);
    }

    private function findOrCreateCompany(InvoiceCreateDTO $dto): Company
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
     * @param  array  $context  Context for logging (user_id, supplier_id, invoice_number)
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

    private function createInvoiceItems(Invoice $invoice, array $items, bool $reverseCharge): void
    {
        $vatCalculator = $this->vatCalculator;

        $preparedItems = $reverseCharge
            ? $this->prepareItemsWithReverseCharge($invoice, $items, $vatCalculator)
            : $this->prepareItemsWithVat($invoice, $items, $vatCalculator);

        foreach ($preparedItems as $itemData) {
            $this->invoiceItemRepository->create($itemData);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function prepareItemsWithVat(Invoice $invoice, array $items, VatCalculatorService $vatCalculator): array
    {
        return array_map(static function (array $item) use ($invoice, $vatCalculator): array {
            $quantity = $item['quantity'];
            $unitPriceWithoutTax = $item['price'] ?? $item['unit_price_without_tax'] ?? 0;
            $taxRate = $item['tax_rate'] ?? 20.0;
            $discountAmount = $item['discount_amount'] ?? null;

            $subtotal = $vatCalculator->calculateItemSubtotal($quantity, $unitPriceWithoutTax, $discountAmount);
            $taxAmount = $vatCalculator->calculateVatAmount($subtotal, $taxRate);
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
        }, $items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function prepareItemsWithReverseCharge(Invoice $invoice, array $items, VatCalculatorService $vatCalculator): array
    {
        return array_map(static function (array $item) use ($invoice, $vatCalculator): array {
            $quantity = $item['quantity'];
            $unitPriceWithoutTax = $item['price'] ?? $item['unit_price_without_tax'] ?? 0;
            $taxRate = $item['tax_rate'] ?? 20.0;
            $discountAmount = $item['discount_amount'] ?? null;

            $subtotal = $vatCalculator->calculateItemSubtotal($quantity, $unitPriceWithoutTax, $discountAmount);
            $taxAmount = $vatCalculator->calculateVatAmountWithReverseCharge($subtotal, $taxRate);
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
        }, $items);
    }
}

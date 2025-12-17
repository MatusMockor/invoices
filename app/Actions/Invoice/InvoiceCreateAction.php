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
            $invoiceData = $this->buildInvoiceData($dto, $userId, $supplierCompanyId, $supplierCompany, $vatStatus, $totals, $isVatPayer);
            $invoiceData = $this->applyCompanyDataToInvoice($invoiceData, $dto);

            $invoice = $this->invoiceRepository->create($invoiceData);
            $this->createInvoiceItems($invoice, $itemsForCalculation, $dto->reverseCharge);

            return $invoice->load(['company', 'items']);
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
        ?UserCompany $supplierCompany,
        ?object $vatStatus,
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
            'supplier_registry_office' => $supplierCompany?->registration_office,
            'supplier_registry_number' => $supplierCompany?->registration_number,
            'supplier_vat_payer_status' => $vatStatus?->status->value,
            'supplier_vat_period' => $vatStatus?->period?->value,
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
     * @return array<string, mixed>
     */
    private function applyCompanyDataToInvoice(array $invoiceData, InvoiceCreateDTO $dto): array
    {
        if ($dto->useCustomCompany) {
            return $this->applyCustomCompanyData($invoiceData, $dto);
        }

        return $this->applyStandardCompanyData($invoiceData, $dto);
    }

    /**
     * @param  array<string, mixed>  $invoiceData
     * @return array<string, mixed>
     */
    private function applyCustomCompanyData(array $invoiceData, InvoiceCreateDTO $dto): array
    {
        $invoiceData['company_id'] = null;
        $invoiceData['company_ico'] = $dto->customCompanyIco;
        $invoiceData['company_dic'] = $dto->customCompanyDic;
        $invoiceData['company_ic_dph'] = $dto->customCompanyIcDph;
        $invoiceData['company_name'] = $dto->customCompanyName;
        $invoiceData['company_address'] = $dto->customCompanyAddress;
        $invoiceData['company_city'] = $dto->customCompanyCity;
        $invoiceData['company_zip'] = $dto->customCompanyZip;
        $invoiceData['company_country'] = $dto->customCompanyCountry;

        return $invoiceData;
    }

    /**
     * @param  array<string, mixed>  $invoiceData
     * @return array<string, mixed>
     */
    private function applyStandardCompanyData(array $invoiceData, InvoiceCreateDTO $dto): array
    {
        $customerCompany = $this->findOrCreateCompany($dto);

        $invoiceData['company_id'] = $customerCompany->id;
        $invoiceData['company_ico'] = $customerCompany->ico;
        $invoiceData['company_dic'] = $customerCompany->dic;
        $invoiceData['company_ic_dph'] = $customerCompany->ic_dph;
        $invoiceData['company_name'] = $customerCompany->name;
        $invoiceData['company_address'] = $customerCompany->street;
        $invoiceData['company_city'] = $customerCompany->city;
        $invoiceData['company_zip'] = $customerCompany->postal_code;
        $invoiceData['company_country'] = $customerCompany->country;

        return $invoiceData;
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

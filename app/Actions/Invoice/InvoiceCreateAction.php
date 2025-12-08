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
use Throwable;

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
        return DB::transaction(function () use ($dto, $userId, $supplierCompanyId) {
            // Fetch supplier company for registry data snapshot
            $supplierCompany = UserCompany::find($supplierCompanyId);

            // Get VAT status at invoice issue date for historical accuracy (REQ-06)
            $issueDate = Carbon::parse($dto->issueDate);
            $vatStatus = $supplierCompany
                ? $this->vatService->getVatStatusAtDate($supplierCompany, $issueDate)
                : null;

            // Calculate invoice totals with VAT (respecting reverse charge)
            $totals = $this->totalCalculator->calculateTotals($dto->items, $dto->discountAmount ?? null, $dto->reverseCharge);

            $invoiceData = [
                'invoice_number' => $dto->invoiceNumber,
                'user_id' => $userId,
                'issue_date' => $dto->issueDate,
                'due_date' => $dto->dueDate,
                'delivery_date' => $dto->deliveryDate,
                'supplier_company_id' => $supplierCompanyId,
                // Supplier registry snapshot - immutable after creation
                'supplier_registry_office' => $supplierCompany?->registration_office,
                'supplier_registry_number' => $supplierCompany?->registration_number,
                // VAT status snapshot - immutable after creation (REQ-06)
                'supplier_vat_payer_status' => $vatStatus?->status->value,
                'supplier_vat_period' => $vatStatus?->period?->value,
                'subtotal' => $totals['subtotal'],
                'tax_amount' => $totals['tax_amount'],
                'tax_rate' => $dto->taxRate ?? 20.0,
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

            // Handle custom company case with early return
            if ($dto->useCustomCompany) {
                $invoiceData['company_id'] = null;
                $invoiceData['company_ico'] = $dto->customCompanyIco;
                $invoiceData['company_dic'] = $dto->customCompanyDic;
                $invoiceData['company_ic_dph'] = $dto->customCompanyIcDph;
                $invoiceData['company_name'] = $dto->customCompanyName;
                $invoiceData['company_address'] = $dto->customCompanyAddress;
                $invoiceData['company_city'] = $dto->customCompanyCity;
                $invoiceData['company_zip'] = $dto->customCompanyZip;
                $invoiceData['company_country'] = $dto->customCompanyCountry;

                $invoice = $this->invoiceRepository->create($invoiceData);
                $this->createInvoiceItems($invoice, $dto->items, $dto->reverseCharge);

                return $invoice->load(['company', 'items']);
            }

            // Standard company case - copy all company data to invoice
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

            $invoice = $this->invoiceRepository->create($invoiceData);
            $this->createInvoiceItems($invoice, $dto->items, $dto->reverseCharge);

            return $invoice->load(['company', 'items']);
        });
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

    private function createInvoiceItems(Invoice $invoice, array $items, bool $reverseCharge): void
    {
        $vatCalculator = $this->vatCalculator;

        $preparedItems = array_map(static function (array $item) use ($invoice, $reverseCharge, $vatCalculator): array {
            $quantity = $item['quantity'];
            $unitPriceWithoutTax = $item['price'] ?? $item['unit_price_without_tax'] ?? 0;
            $taxRate = $item['tax_rate'] ?? 20.0;
            $discountAmount = $item['discount_amount'] ?? null;

            // Calculate item subtotal (without VAT)
            $subtotal = $vatCalculator->calculateItemSubtotal($quantity, $unitPriceWithoutTax, $discountAmount);

            // Calculate VAT amount (respect reverse charge)
            $taxAmount = $vatCalculator->calculateVatAmount($subtotal, $taxRate, $reverseCharge);

            // Calculate total price (with or without VAT based on reverse charge)
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

        foreach ($preparedItems as $itemData) {
            $this->invoiceItemRepository->create($itemData);
        }
    }
}

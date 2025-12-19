<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\DTOs\Invoice\InvoiceUpdateContext;
use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\UserCompany;
use App\Repositories\Contracts\InvoiceRepository;
use App\Services\Interfaces\VatService;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Action for updating existing invoices with all related data.
 */
final readonly class InvoiceUpdateAction
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private CompanyFetchOrCreateAction $companyFetchOrCreate,
        private InvoiceTotalCalculatorService $totalCalculator,
        private InvoiceItemsUpdateHandler $itemsHandler,
        private VatService $vatService
    ) {}

    public function handle(Invoice $invoice, InvoiceUpdateDTO $dto, int $supplierCompanyId): Invoice
    {
        return DB::transaction(function () use ($invoice, $dto, $supplierCompanyId): Invoice {
            $isVatPayer = $this->determineVatPayerStatus($supplierCompanyId, $dto->issueDate ?? $invoice->issue_date);

            $context = new InvoiceUpdateContext(
                invoice: $invoice,
                dto: $dto,
                supplierCompanyId: $supplierCompanyId,
                isVatPayer: $isVatPayer,
            );

            $updateData = $this->buildBaseUpdateData($dto, $supplierCompanyId, $isVatPayer);
            $updateData = $this->applyCompanyData($updateData, $context);

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
    private function applyCompanyData(array $updateData, InvoiceUpdateContext $context): array
    {
        if ($context->dto->useCustomCompany === true) {
            $updateData = $this->applyCustomCompanyData($updateData, $context->dto);

            return $this->applyItemsData($updateData, $context);
        }

        if ($context->dto->useCustomCompany === false && $context->dto->clientIco !== null) {
            $updateData = $this->applyStandardCompanyData($updateData, $context->dto);
        }

        return $this->applyItemsData($updateData, $context);
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
    private function applyItemsData(array $updateData, InvoiceUpdateContext $context): array
    {
        if ($context->dto->items === null) {
            return $updateData;
        }

        $reverseCharge = $context->dto->reverseCharge ?? $context->invoice->reverse_charge ?? false;
        $itemsForCalculation = $this->prepareItemsWithTaxRate($context->dto->items, $context->isVatPayer, [
            'invoice_id' => $context->invoice->id,
            'invoice_number' => $context->dto->invoiceNumber ?? $context->invoice->invoice_number,
            'supplier_id' => $context->supplierCompanyId,
            'user_id' => $context->invoice->user_id,
        ]);

        $totals = $reverseCharge
            ? $this->totalCalculator->calculateTotalsWithReverseCharge($itemsForCalculation, $context->dto->discountAmount)
            : $this->totalCalculator->calculateTotals($itemsForCalculation, $context->dto->discountAmount);
        $updateData['subtotal'] = $totals['subtotal'];
        $updateData['tax_amount'] = $totals['tax_amount'];
        $updateData['total_amount'] = $totals['total_amount'];

        $this->itemsHandler->updateItems($context->invoice, $itemsForCalculation);

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
     * @param  array  $context  Context for logging
     * @return array Items with corrected tax_rate
     */
    private function prepareItemsWithTaxRate(array $items, bool $isVatPayer, array $context = []): array
    {
        return array_map(static function (array $item) use ($isVatPayer, $context): array {
            $originalTaxRate = $item['tax_rate'] ?? null;
            $item['tax_rate'] = $isVatPayer ? ($originalTaxRate ?? 20.0) : 0.0;

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
}

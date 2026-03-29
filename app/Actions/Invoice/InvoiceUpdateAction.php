<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\DTOs\Invoice\InvoiceUpdateContext;
use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Models\Invoice;
use App\Models\UserCompany;
use App\Repositories\Contracts\InvoiceRepository;
use App\Support\InvoicePartySnapshot;
use App\Services\Interfaces\VatService;
use App\Services\Invoice\InvoiceItemsProcessorService;
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
        private InvoiceItemsProcessorService $itemsProcessor,
        private VatService $vatService
    ) {}

    public function handle(Invoice $invoice, InvoiceUpdateDTO $dto, int $supplierCompanyId): Invoice
    {
        return DB::transaction(function () use ($invoice, $dto, $supplierCompanyId): Invoice {
            $supplierChanged = $invoice->supplier_company_id !== $supplierCompanyId;
            $supplierCompany = UserCompany::find($supplierCompanyId);
            $issueDate = $dto->issueDate ?? $invoice->issue_date->toDateString();
            $vatStatus = $supplierChanged
                ? $this->getVatStatusForSupplier($supplierCompany, $issueDate)
                : null;
            $isVatPayer = $supplierChanged
                ? ($vatStatus?->status->isVatPayer() ?? false)
                : $invoice->supplierIsVatPayer();
            $supplierSnapshot = $supplierChanged
                ? InvoicePartySnapshot::supplierFromUserCompany($supplierCompany, $vatStatus)
                : $invoice->getSupplierSnapshot();

            $context = new InvoiceUpdateContext(
                invoice: $invoice,
                dto: $dto,
                supplierCompanyId: $supplierCompanyId,
                isVatPayer: $isVatPayer,
            );

            $updateData = $this->buildBaseUpdateData($dto, $supplierCompanyId, $isVatPayer);
            $updateData = $this->applyCompanyData($updateData, $context, $supplierSnapshot);

            $this->invoiceRepository->update($invoice, $updateData);

            return $invoice->fresh()->load(['items']);
        });
    }

    private function getVatStatusForSupplier(?UserCompany $supplierCompany, string|Carbon $issueDate): ?object
    {
        $issueDateParsed = $issueDate instanceof Carbon ? $issueDate : Carbon::parse($issueDate);

        return $supplierCompany
            ? $this->vatService->getVatStatusAtDate($supplierCompany, $issueDateParsed)
            : null;
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
    private function applyCompanyData(array $updateData, InvoiceUpdateContext $context, array $supplierSnapshot): array
    {
        if ($context->dto->useCustomCompany === true) {
            $updateData = $this->applyCustomCompanyData($updateData, $context->dto, $supplierSnapshot);

            return $this->applyItemsData($updateData, $context);
        }

        if ($context->dto->useCustomCompany === false && $context->dto->clientIco !== null) {
            $updateData = $this->applyStandardCompanyData($updateData, $context->dto, $supplierSnapshot);

            return $this->applyItemsData($updateData, $context);
        }

        $updateData['party_snapshot'] = InvoicePartySnapshot::make(
            $supplierSnapshot,
            $context->invoice->getCustomerSnapshot()
        );

        return $this->applyItemsData($updateData, $context);
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @param  array<string, mixed>  $supplierSnapshot
     * @return array<string, mixed>
     */
    private function applyCustomCompanyData(array $updateData, InvoiceUpdateDTO $dto, array $supplierSnapshot): array
    {
        return array_merge($updateData, [
            'company_id' => null,
            'party_snapshot' => InvoicePartySnapshot::make(
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
            ),
        ]);
    }

    /**
     * @param  array<string, mixed>  $updateData
     * @param  array<string, mixed>  $supplierSnapshot
     * @return array<string, mixed>
     */
    private function applyStandardCompanyData(array $updateData, InvoiceUpdateDTO $dto, array $supplierSnapshot): array
    {
        $customerCompany = $this->findOrCreateCompany($dto);

        return array_merge($updateData, [
            'company_id' => $customerCompany->id,
            'party_snapshot' => InvoicePartySnapshot::make(
                $supplierSnapshot,
                InvoicePartySnapshot::customerFromCompany($customerCompany)
            ),
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
            ? $this->itemsProcessor->calculateTotalsWithReverseCharge($itemsForCalculation, $context->dto->discountAmount)
            : $this->itemsProcessor->calculateTotals($itemsForCalculation, $context->dto->discountAmount);
        $updateData['subtotal'] = $totals['subtotal'];
        $updateData['tax_amount'] = $totals['tax_amount'];
        $updateData['total_amount'] = $totals['total_amount'];

        $this->itemsProcessor->updateItems($context->invoice, $itemsForCalculation);

        return $updateData;
    }

    private function findOrCreateCompany(InvoiceUpdateDTO $dto)
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

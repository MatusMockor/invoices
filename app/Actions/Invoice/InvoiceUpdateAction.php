<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class InvoiceUpdateAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceItemRepository $invoiceItemRepository,
        private readonly CompanyFetchOrCreateAction $companyFetchOrCreate,
        private readonly InvoiceTotalCalculatorService $totalCalculator
    ) {}

    public function handle(Invoice $invoice, InvoiceUpdateDTO $dto, int $supplierCompanyId): Invoice
    {
        return DB::transaction(function () use ($invoice, $dto, $supplierCompanyId) {
            $updateData = array_filter([
                'supplier_company_id' => $supplierCompanyId,
                'invoice_number' => $dto->invoiceNumber,
                'issue_date' => $dto->issueDate,
                'due_date' => $dto->dueDate,
                'delivery_date' => $dto->deliveryDate,
                'currency' => $dto->currency,
                'constant_symbol' => $dto->constantSymbol,
                'note' => $dto->notes,
                'status' => $dto->status,
            ], static fn (mixed $value): bool => $value !== null);

            // Handle custom company case with early return
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
                    $updateData['total_amount'] = $this->totalCalculator->calculate($dto->items);
                    $this->updateInvoiceItems($invoice, $dto->items);
                }

                $this->invoiceRepository->update($invoice, $updateData);

                return $invoice->fresh()->load(['company', 'items']);
            }

            // Handle standard company case - copy all company data to invoice
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

            // Handle remaining updates (items only, company unchanged)
            if ($dto->items !== null) {
                $updateData['total_amount'] = $this->totalCalculator->calculate($dto->items);
                $this->updateInvoiceItems($invoice, $dto->items);
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

        foreach ($items as $item) {
            $unitPrice = $item['price'];

            $itemData = [
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $item['quantity'] * $unitPrice,
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
            ['description', 'quantity', 'unit_price', 'total_price']
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

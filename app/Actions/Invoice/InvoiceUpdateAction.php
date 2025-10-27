<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\DTOs\Invoice\InvoiceUpdateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Repositories\Interfaces\CompanyRepository;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class InvoiceUpdateAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceItemRepository $invoiceItemRepository,
        private readonly CompanyRepository $companyRepository,
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
            ], static fn ($value) => $value !== null);

            if ($dto->clientIco !== null) {
                $customerCompany = $this->findOrCreateCompany($dto);

                $updateData = array_merge($updateData, [
                    'company_id' => $customerCompany->id,
                ]);
            }

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
        if ($dto->clientIco === null) {
            throw new InvalidArgumentException('Client ICO is required');
        }

        $company = $this->companyRepository->findByIco($dto->clientIco);

        if ($company) {
            return $company;
        }

        // Create new company
        return $this->companyRepository->create([
            'name' => $dto->clientName,
            'ico' => $dto->clientIco,
            'dic' => $dto->clientDic,
            'ic_dph' => $dto->clientIcDph,
            'street' => $dto->clientStreet,
            'city' => $dto->clientCity,
            'postal_code' => $dto->clientPostalCode,
            'country' => $dto->clientCountry ?? 'SK',
        ]);
    }

    private function updateInvoiceItems(Invoice $invoice, array $items): void
    {
        // Separate existing items from new items
        $existingItems = [];
        $newItems = [];

        foreach ($items as $item) {
            $unitPrice = $item['price'] ?? $item['unit_price'] ?? 0;

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

        // Get IDs of items that should be kept
        $itemIds = Arr::pluck($existingItems, 'id');

        // Delete items that are not in the update request
        $this->invoiceItemRepository->deleteItemsNotInIds($invoice->id, $itemIds);

        // Update existing items
        if (! empty($existingItems)) {
            $this->invoiceItemRepository->upsert(
                $existingItems,
                ['id'],
                ['description', 'quantity', 'unit_price', 'total_price']
            );
        }

        // Create new items
        foreach ($newItems as $newItem) {
            $this->invoiceItemRepository->create($newItem);
        }
    }
}

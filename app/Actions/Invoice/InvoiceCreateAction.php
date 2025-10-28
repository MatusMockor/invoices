<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\Actions\Company\CompanyFetchOrCreateAction;
use App\DTOs\Invoice\InvoiceCreateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use Illuminate\Support\Facades\DB;

final class InvoiceCreateAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceItemRepository $invoiceItemRepository,
        private readonly CompanyFetchOrCreateAction $companyFetchOrCreate,
        private readonly InvoiceTotalCalculatorService $totalCalculator
    ) {}

    public function handle(InvoiceCreateDTO $dto, int $userId, int $supplierCompanyId): Invoice
    {
        return DB::transaction(function () use ($dto, $userId, $supplierCompanyId) {
            $customerCompany = $this->findOrCreateCompany($dto);

            $totalAmount = $this->totalCalculator->calculate($dto->items);

            $invoice = $this->invoiceRepository->create([
                'invoice_number' => $dto->invoiceNumber,
                'user_id' => $userId,
                'issue_date' => $dto->issueDate,
                'due_date' => $dto->dueDate,
                'delivery_date' => $dto->deliveryDate,
                'company_id' => $customerCompany->id,
                'supplier_company_id' => $supplierCompanyId,
                'total_amount' => $totalAmount,
                'currency' => $dto->currency ?? config('invoices.default_currency'),
                'constant_symbol' => $dto->constantSymbol,
                'note' => $dto->notes,
                'status' => $dto->status ?? config('invoices.default_status'),
            ]);

            $this->createInvoiceItems($invoice, $dto->items);

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

    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        $preparedItems = array_map(static function ($item) use ($invoice) {
            $unitPrice = $item['price'];

            return [
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $item['quantity'] * $unitPrice,
            ];
        }, $items);

        foreach ($preparedItems as $itemData) {
            $this->invoiceItemRepository->create($itemData);
        }
    }
}

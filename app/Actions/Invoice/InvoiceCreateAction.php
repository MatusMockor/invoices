<?php

declare(strict_types=1);

namespace App\Actions\Invoice;

use App\DTOs\Invoice\InvoiceCreateDTO;
use App\Models\Company;
use App\Models\Invoice;
use App\Repositories\Interfaces\CompanyRepository;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Invoice\InvoiceTotalCalculatorService;
use Illuminate\Support\Facades\DB;

final class InvoiceCreateAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceItemRepository $invoiceItemRepository,
        private readonly CompanyRepository $companyRepository,
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
                'currency' => $dto->currency ?? 'EUR',
                'constant_symbol' => $dto->constantSymbol,
                'note' => $dto->notes,
                'status' => $dto->status ?? 'draft',

                'customer_name' => $dto->clientName,
                'customer_ico' => $dto->clientIco,
                'customer_dic' => $dto->clientDic,
                'customer_ic_dph' => $dto->clientIcDph,
                'customer_street' => $dto->clientStreet,
                'customer_city' => $dto->clientCity,
                'customer_postal_code' => $dto->clientPostalCode,
                'customer_country' => $dto->clientCountry ?? 'SK',
            ]);

            $this->createInvoiceItems($invoice, $dto->items);

            return $invoice->load(['company', 'items']);
        });
    }

    private function findOrCreateCompany(InvoiceCreateDTO $dto): Company
    {
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
            'country' => $dto->clientCountry,
        ]);
    }

    private function createInvoiceItems(Invoice $invoice, array $items): void
    {
        $preparedItems = array_map(static function ($item) use ($invoice) {
            $unitPrice = $item['price'] ?? $item['unit_price'] ?? 0;

            return [
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $item['quantity'] * $unitPrice,
            ];
        }, $items);

        $this->invoiceItemRepository->upsert(
            $preparedItems,
            ['id'],
            ['description', 'quantity', 'unit_price', 'total_price']
        );
    }
}

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
use Throwable;

final class InvoiceCreateAction
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly InvoiceItemRepository $invoiceItemRepository,
        private readonly CompanyFetchOrCreateAction $companyFetchOrCreate,
        private readonly InvoiceTotalCalculatorService $totalCalculator
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(InvoiceCreateDTO $dto, int $userId, int $supplierCompanyId): Invoice
    {
        return DB::transaction(function () use ($dto, $userId, $supplierCompanyId) {
            $totalAmount = $this->totalCalculator->calculate($dto->items);

            $invoiceData = [
                'invoice_number' => $dto->invoiceNumber,
                'user_id' => $userId,
                'issue_date' => $dto->issueDate,
                'due_date' => $dto->dueDate,
                'delivery_date' => $dto->deliveryDate,
                'supplier_company_id' => $supplierCompanyId,
                'total_amount' => $totalAmount,
                'currency' => $dto->currency ?? config('invoices.default_currency'),
                'variable_symbol' => $dto->variableSymbol,
                'constant_symbol' => $dto->constantSymbol,
                'specific_symbol' => $dto->specificSymbol,
                'note' => $dto->notes,
                'status' => $dto->status ?? config('invoices.default_status'),
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
                $this->createInvoiceItems($invoice, $dto->items);

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
        $preparedItems = array_map(static function (array $item) use ($invoice): array {
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

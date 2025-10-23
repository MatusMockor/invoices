<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Repositories\Interfaces\CompanyRepository;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Interfaces\InvoiceService as InvoiceServiceContract;
use Illuminate\Support\Arr;

class InvoiceService implements InvoiceServiceContract
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository,
        protected InvoiceItemRepository $invoiceItemRepository,
        protected CompanyRepository $companyRepository
    ) {}

    /**
     * Create a new invoice with items.
     */
    public function createInvoice(array $data, int $userId, int $supplierCompanyId): Invoice
    {
        // Find or create customer company
        $customerCompany = $this->findOrCreateCompany($data);

        // Parse address
        $addressParts = $this->parseAddress($data['clientAddress']);

        // Calculate total
        $totalAmount = $this->calculateTotalAmount($data['items']);

        // Create invoice - Observer will automatically populate supplier snapshot
        $invoice = $this->invoiceRepository->create([
            'invoice_number' => $data['invoiceNumber'],
            'user_id' => $userId,
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'],
            'delivery_date' => $data['delivery_date'] ?? $data['issue_date'],
            'company_id' => $customerCompany->id,
            'supplier_company_id' => $supplierCompanyId,
            'total_amount' => $totalAmount,
            'currency' => $data['currency'] ?? 'EUR',
            'constant_symbol' => $data['constantSymbol'] ?? null,
            'note' => $data['notes'] ?? null,
            'status' => $data['status'] ?? 'draft',

            // Customer snapshot
            'customer_name' => $data['clientName'],
            'customer_ico' => $data['clientIco'],
            'customer_dic' => $data['clientDic'],
            'customer_ic_dph' => $data['clientIcDph'],
            'customer_street' => $addressParts['street'],
            'customer_city' => $addressParts['city'],
            'customer_postal_code' => $addressParts['postal_code'],
            'customer_country' => $addressParts['country'] ?? 'SK',
        ]);

        $this->createInvoiceItems($invoice, $data['items']);

        return $invoice->load(['company', 'items']);
    }

    /**
     * Update an existing invoice with items.
     */
    public function updateInvoice(Invoice $invoice, array $data, int $supplierCompanyId): Invoice
    {
        $updateData = [
            'supplier_company_id' => $supplierCompanyId,
        ];

        // Update invoice fields
        if (isset($data['invoiceNumber'])) {
            $updateData['invoice_number'] = $data['invoiceNumber'];
        }
        if (isset($data['issue_date'])) {
            $updateData['issue_date'] = $data['issue_date'];
        }
        if (isset($data['due_date'])) {
            $updateData['due_date'] = $data['due_date'];
        }
        if (isset($data['delivery_date'])) {
            $updateData['delivery_date'] = $data['delivery_date'];
        }
        if (isset($data['currency'])) {
            $updateData['currency'] = $data['currency'];
        }
        if (isset($data['constantSymbol'])) {
            $updateData['constant_symbol'] = $data['constantSymbol'];
        }
        if (isset($data['notes'])) {
            $updateData['note'] = $data['notes'];
        }
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        // Update customer company if client data provided
        if (isset($data['clientIco'])) {
            $customerCompany = $this->findOrCreateCompany($data);
            $updateData['company_id'] = $customerCompany->id;

            // Parse address
            $addressParts = $this->parseAddress($data['clientAddress']);

            // Update customer snapshot
            $updateData['customer_name'] = $data['clientName'];
            $updateData['customer_ico'] = $data['clientIco'];
            $updateData['customer_dic'] = $data['clientDic'];
            $updateData['customer_ic_dph'] = $data['clientIcDph'];
            $updateData['customer_street'] = $addressParts['street'];
            $updateData['customer_city'] = $addressParts['city'];
            $updateData['customer_postal_code'] = $addressParts['postal_code'];
            $updateData['customer_country'] = $addressParts['country'] ?? 'SK';
        }

        // Calculate totals if items provided
        if (isset($data['items'])) {
            $updateData['total_amount'] = $this->calculateTotalAmount($data['items']);
        }

        // Update invoice - Observer will automatically populate supplier snapshot if supplier_company_id changed
        $this->invoiceRepository->update($invoice, $updateData);

        // Update items if provided
        if (isset($data['items'])) {
            $this->updateInvoiceItems($invoice, $data['items']);
        }

        return $invoice->fresh()->load(['company', 'items']);
    }

    /**
     * Find or create a company by ICO.
     */
    protected function findOrCreateCompany(array $data): Company
    {
        $company = $this->companyRepository->findByIco($data['clientIco']);

        if ($company) {
            return $company;
        }

        // Parse address
        $addressParts = $this->parseAddress($data['clientAddress']);

        // Create new company
        return $this->companyRepository->create([
            'name' => $data['clientName'],
            'ico' => $data['clientIco'],
            'dic' => $data['clientDic'],
            'ic_dph' => $data['clientIcDph'],
            'street' => $addressParts['street'],
            'city' => $addressParts['city'],
            'postal_code' => $addressParts['postal_code'],
            'country' => $addressParts['country'] ?? 'SK',
        ]);
    }

    /**
     * Parse address string into components.
     * Format: "Ulica 123, 811 01 Bratislava" or "Ulica 123, Bratislava"
     */
    protected function parseAddress(string $address): array
    {
        $parts = explode(',', $address);
        $street = trim($parts[0] ?? '');
        $cityPart = trim($parts[1] ?? '');

        // Try to extract postal code and city
        if (preg_match('/^(\d{3}\s?\d{2})\s+(.+)$/', $cityPart, $matches)) {
            $postalCode = str_replace(' ', '', $matches[1]);
            $city = $matches[2];
        } else {
            $postalCode = '';
            $city = $cityPart;
        }

        return [
            'street' => $street,
            'postal_code' => $postalCode,
            'city' => $city,
            'country' => 'SK',
        ];
    }

    /**
     * Calculate the total amount for invoice items.
     */
    protected function calculateTotalAmount(array $items): float
    {
        $totalAmount = 0;

        foreach ($items as $item) {
            $unitPrice = $item['price'] ?? $item['unit_price'] ?? 0;
            $itemTotal = $item['quantity'] * $unitPrice;
            $totalAmount += $itemTotal;
        }

        return $totalAmount;
    }

    /**
     * Create invoice items.
     */
    protected function createInvoiceItems(Invoice $invoice, array $items): void
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

    /**
     * Update invoice items.
     */
    protected function updateInvoiceItems(Invoice $invoice, array $items): void
    {
        // Get IDs of items that should be kept
        $itemIds = Arr::pluck(
            Arr::where($items, static function (array $item) {
                return isset($item['id']);
            }),
            'id'
        );

        // Delete items that are not in the update request
        $this->invoiceItemRepository->deleteItemsNotInIds($invoice->id, $itemIds);

        // Prepare items for upsert
        $preparedItems = array_map(static function ($item) use ($invoice) {
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
            }

            return $itemData;
        }, $items);

        $this->invoiceItemRepository->upsert(
            $preparedItems,
            ['id'],
            ['description', 'quantity', 'unit_price', 'total_price']
        );
    }
}

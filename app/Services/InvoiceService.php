<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceItemRepository;
use App\Repositories\Interfaces\InvoiceRepository;
use App\Services\Interfaces\InvoiceService as InvoiceServiceContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class InvoiceService implements InvoiceServiceContract
{
    public function __construct(
        protected InvoiceRepository $invoiceRepository,
        protected InvoiceItemRepository $invoiceItemRepository
    ) {}

    /**
     * Create a new invoice with items.
     */
    public function createInvoice(array $data, int $userId, int $companyId): Invoice
    {
        return DB::transaction(function () use ($data, $userId, $companyId) {
            $totalAmount = $this->calculateTotalAmount($data['items']);

            $invoice = $this->invoiceRepository->create([
                'invoice_number' => $data['invoice_number'],
                'user_id' => $userId,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'delivery_date' => $data['issue_date'], // Default to issue date
                'business_entity_id' => $data['business_entity_id'],
                'supplier_company_id' => $companyId,
                'total_amount' => $totalAmount,
                'currency' => $data['currency'],
                'constant_symbol' => $data['constant_symbol'] ?? null,
                'note' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'draft',
            ]);

            $this->createInvoiceItems($invoice, $data['items']);

            return $invoice->load(['company', 'items']);
        });
    }

    /**
     * Update an existing invoice with items.
     */
    public function updateInvoice(Invoice $invoice, array $data, int $companyId): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $companyId) {
            $updateData = Arr::except($data, ['items']);
            $updateData['supplier_company_id'] = $companyId;

            if (isset($data['notes'])) {
                $updateData['note'] = $data['notes'];
                unset($updateData['notes']);
            }

            // Calculate totals if items provided
            if (isset($data['items'])) {
                $updateData['total_amount'] = $this->calculateTotalAmount($data['items']);
            }

            $this->invoiceRepository->update($invoice, $updateData);

            // Update items if provided
            if (isset($data['items'])) {
                $this->updateInvoiceItems($invoice, $data['items']);
            }

            return $invoice->fresh()->load(['company', 'items']);
        });
    }

    /**
     * Calculate the total amount for invoice items.
     */
    protected function calculateTotalAmount(array $items): float
    {
        $totalAmount = 0;

        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['unit_price'];
            $totalAmount += $itemTotal + ($itemTotal * $item['vat_rate'] / 100);
        }

        return $totalAmount;
    }

    /**
     * Create invoice items.
     */
    protected function createInvoiceItems(Invoice $invoice, array $items): void
    {
        $preparedItems = array_map(static function ($item) use ($invoice) {
            return [
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ];
        }, $items);

        $this->invoiceItemRepository->upsert(
            $preparedItems,
            ['id'],
            ['description', 'quantity', 'unit_price', 'vat_rate', 'total_price']
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
            $itemData = [
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'vat_rate' => $item['vat_rate'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ];

            if (isset($item['id'])) {
                $itemData['id'] = $item['id'];
            }

            return $itemData;
        }, $items);

        $this->invoiceItemRepository->upsert(
            $preparedItems,
            ['id'],
            ['description', 'quantity', 'unit_price', 'vat_rate', 'total_price']
        );
    }
}

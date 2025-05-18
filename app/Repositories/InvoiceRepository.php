<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceRepository as InvoiceRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InvoiceRepository implements InvoiceRepositoryContract
{
    /**
     * Get all invoices for the current company with pagination
     */
    public function getAllForCompanyPaginated(int $companyId, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::with('businessEntity')
            ->where('supplier_company_id', $companyId)
            ->paginate($perPage);
    }

    /**
     * Create a new invoice
     */
    public function create(array $data): Invoice
    {
        return Invoice::query()->create($data);
    }

    /**
     * Update an invoice
     */
    public function update(Invoice $invoice, array $data): bool
    {
        return $invoice->update($data);
    }

    /**
     * Delete an invoice
     */
    public function delete(Invoice $invoice): bool
    {
        return $invoice->delete();
    }

    /**
     * Load relations for an invoice
     */
    public function loadRelations(Invoice $invoice, array $relations): Invoice
    {
        return $invoice->load($relations);
    }
}

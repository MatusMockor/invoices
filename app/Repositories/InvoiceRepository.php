<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Repositories\Interfaces\InvoiceRepository as InvoiceRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InvoiceRepository implements InvoiceRepositoryContract
{
    /**
     * Get all invoices for the current company with pagination
     *
     * @param int $companyId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllForCompanyPaginated(int $companyId, int $perPage = 10): LengthAwarePaginator
    {
        return Invoice::query()
            ->with('partner')
            ->where('supplier_company_id', $companyId)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new invoice
     *
     * @param array $data
     * @return Invoice
     */
    public function create(array $data): Invoice
    {
        return Invoice::query()->create($data);
    }

    /**
     * Update an invoice
     *
     * @param Invoice $invoice
     * @param array $data
     * @return bool
     */
    public function update(Invoice $invoice, array $data): bool
    {
        return $invoice->update($data);
    }

    /**
     * Delete an invoice
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function delete(Invoice $invoice): bool
    {
        return $invoice->delete();
    }

    /**
     * Load relations for an invoice
     *
     * @param Invoice $invoice
     * @param array $relations
     * @return Invoice
     */
    public function loadRelations(Invoice $invoice, array $relations): Invoice
    {
        return $invoice->load($relations);
    }
}

<?php

namespace App\Repositories\Interfaces;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface InvoiceRepository
{
    /**
     * Get all invoices for the current company with pagination
     *
     * @param int $companyId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllForCompanyPaginated(int $companyId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new invoice
     *
     * @param array $data
     * @return Invoice
     */
    public function create(array $data): Invoice;

    /**
     * Update an invoice
     *
     * @param Invoice $invoice
     * @param array $data
     * @return bool
     */
    public function update(Invoice $invoice, array $data): bool;

    /**
     * Delete an invoice
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function delete(Invoice $invoice): bool;

    /**
     * Load relations for an invoice
     *
     * @param Invoice $invoice
     * @param array $relations
     * @return Invoice
     */
    public function loadRelations(Invoice $invoice, array $relations): Invoice;
}
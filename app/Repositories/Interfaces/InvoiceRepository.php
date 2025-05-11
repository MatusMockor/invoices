<?php

namespace App\Repositories\Interfaces;

use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceRepository
{
    /**
     * Get all invoices for the current company with pagination
     */
    public function getAllForCompanyPaginated(int $companyId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new invoice
     */
    public function create(array $data): Invoice;

    /**
     * Update an invoice
     */
    public function update(Invoice $invoice, array $data): bool;

    /**
     * Delete an invoice
     */
    public function delete(Invoice $invoice): bool;

    /**
     * Load relations for an invoice
     */
    public function loadRelations(Invoice $invoice, array $relations): Invoice;
}

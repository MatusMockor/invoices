<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * Get income invoices for a company within date range
     */
    public function getIncomeInvoices(int $companyId, Carbon $startDate, Carbon $endDate): Collection;

    /**
     * Get expense invoices for a company within date range
     */
    public function getExpenseInvoices(int $companyId, Carbon $startDate, Carbon $endDate): Collection;
}

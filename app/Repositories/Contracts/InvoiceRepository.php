<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

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

    /**
     * Get the latest invoice number for a company
     */
    public function getLatestInvoiceNumber(int $companyId): ?string;

    /**
     * Find invoice by invoice number for a specific user.
     * Returns null if not found or doesn't belong to user's companies.
     */
    public function findByNumberForUser(string $invoiceNumber, int $userId): ?Invoice;
}

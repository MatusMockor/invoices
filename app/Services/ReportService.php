<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Repositories\Contracts\InvoiceRepository as InvoiceRepositoryContract;
use App\Services\Interfaces\ReportService as ReportServiceContract;
use Carbon\Carbon;

final class ReportService implements ReportServiceContract
{
    public function __construct(
        private readonly InvoiceRepositoryContract $invoiceRepository
    ) {}

    /**
     * Get financial report for a company within a date range
     */
    public function getFinancialReport(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        [$start, $end] = $this->parseDateRange($startDate, $endDate);

        $incomeInvoices = $this->invoiceRepository->getIncomeInvoices($companyId, $start, $end);
        $expenseInvoices = $this->invoiceRepository->getExpenseInvoices($companyId, $start, $end);

        $totalIncome = $incomeInvoices->sum('total_amount');
        $totalExpenses = $expenseInvoices->sum('total_amount');

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'balance' => $totalIncome - $totalExpenses,
            'income_count' => $incomeInvoices->count(),
            'expense_count' => $expenseInvoices->count(),
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
        ];
    }

    /**
     * Get invoice summary for a company within a date range
     */
    public function getInvoiceSummary(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        [$start, $end] = $this->parseDateRange($startDate, $endDate);

        /** @var \Illuminate\Database\Eloquent\Collection<int, Invoice> $incomeInvoices */
        $incomeInvoices = $this->invoiceRepository->getIncomeInvoices($companyId, $start, $end);
        /** @var \Illuminate\Database\Eloquent\Collection<int, Invoice> $expenseInvoices */
        $expenseInvoices = $this->invoiceRepository->getExpenseInvoices($companyId, $start, $end);

        return [
            'income_invoices' => $incomeInvoices->map(static fn (Invoice $invoice): array => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->company->name ?? 'N/A',
                'issue_date' => $invoice->issue_date->toDateString(),
                'due_date' => $invoice->due_date->toDateString(),
                'total_amount' => $invoice->total_amount,
                'currency' => $invoice->currency,
                'status' => $invoice->status,
            ])->toArray(),
            'expense_invoices' => $expenseInvoices->map(static fn (Invoice $invoice): array => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'supplier_name' => $invoice->supplierCompany->name ?? 'N/A',
                'issue_date' => $invoice->issue_date->toDateString(),
                'due_date' => $invoice->due_date->toDateString(),
                'total_amount' => $invoice->total_amount,
                'currency' => $invoice->currency,
                'status' => $invoice->status,
            ])->toArray(),
        ];
    }

    /**
     * Parse date range from strings
     */
    private function parseDateRange(?string $startDate, ?string $endDate): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        return [$start, $end];
    }
}

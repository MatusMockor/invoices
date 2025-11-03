<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Company;
use App\Models\Invoice;
use App\Repositories\Interfaces\CompanyRepository as CompanyRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CompanyRepository implements CompanyRepositoryContract
{
    /**
     * Get all companies with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Company::latest()->paginate($perPage);
    }

    /**
     * Get all companies ordered by name
     */
    public function getAllOrderedByName(): Collection
    {
        return Company::orderBy('name')->get();
    }

    /**
     * Find a company by ID
     */
    public function findById(int $id): ?Company
    {
        return Company::find($id);
    }

    /**
     * Find a company by ICO
     */
    public function findByIco(string $ico): ?Company
    {
        return Company::firstWhere('ico', $ico);
    }

    /**
     * Search companies by ICO or name
     */
    public function searchByIcoOrName(string $query): Collection
    {
        return Company::where('ico', 'LIKE', "%{$query}%")
            ->orWhere('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    /**
     * Get companies by country
     */
    public function getByCountry(string $country): Collection
    {
        return Company::where('country', $country)->get();
    }

    /**
     * Get companies created in a specific year
     */
    public function getByYear(int $year): Collection
    {
        return Company::whereYear('created_at', $year)
            ->get();
    }

    /**
     * Get companies created in a specific month of a year
     */
    public function getByMonth(int $year, int $month): Collection
    {
        return Company::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get();
    }

    /**
     * Get companies with VAT number
     */
    public function getWithVatNumber(): Collection
    {
        return Company::whereNotNull('ic_dph')
            ->get();
    }

    /**
     * Get companies without VAT number
     */
    public function getWithoutVatNumber(): Collection
    {
        return Company::whereNull('ic_dph')
            ->get();
    }

    /**
     * Count all companies
     */
    public function count(): int
    {
        return Company::count();
    }

    /**
     * Create a new company
     */
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * Update a company
     */
    public function update(Company $company, array $data): bool
    {
        return $company->update($data);
    }

    /**
     * Delete a company
     */
    public function delete(Company $company): bool
    {
        return $company->delete();
    }

    /**
     * Get total income for a company
     */
    public function getTotalIncome(int $companyId): float
    {
        return (float) Invoice::where('supplier_company_id', $companyId)
            ->sum('total_amount');
    }

    /**
     * Get total expenses for a company
     */
    public function getTotalExpenses(int $companyId): float
    {
        // Calculate total amount of invoices where this company is the recipient
        // (company_id = the company receiving the invoice = expense)
        return (float) Invoice::where('company_id', $companyId)
            ->sum('total_amount');
    }

    /**
     * Get monthly income for a company for the specified year
     *
     * @return array<int, float> Array indexed by month number (1-12) with total amounts
     */
    public function getMonthlyIncome(int $companyId, int $year): array
    {
        return $this->getMonthlyAmounts($companyId, $year, 'supplier_company_id');
    }

    /**
     * Get monthly expenses for a company for the specified year
     *
     * @return array<int, float> Array indexed by month number (1-12) with total amounts
     */
    public function getMonthlyExpenses(int $companyId, int $year): array
    {
        return $this->getMonthlyAmounts($companyId, $year, 'company_id');
    }

    /**
     * Upsert multiple companies in batch.
     *
     * @param  array<int, array<string, mixed>>  $companies
     * @param  array<int, string>  $uniqueBy
     * @param  array<int, string>|null  $update
     * @return int Number of rows affected
     */
    public function upsertBatch(array $companies, array $uniqueBy = ['ico'], ?array $update = null): int
    {
        if (empty($companies)) {
            return 0;
        }

        if ($update === null) {
            $update = ['name', 'street', 'city', 'postal_code', 'country', 'dic', 'ic_dph'];
        }

        return Company::upsert($companies, $uniqueBy, $update);
    }

    /**
     * Update VAT data (ic_dph) for a company by ICO.
     *
     * @param  array<string, mixed>  $vatData  Should contain 'ic_dph' key
     */
    public function updateVatData(string $ico, array $vatData): bool
    {
        $company = $this->findByIco($ico);

        if (! $company) {
            return false;
        }

        // Only update ic_dph field
        return $company->update([
            'ic_dph' => $vatData['ic_dph'] ?? null,
        ]);
    }

    /**
     * Get companies count grouped by country
     *
     * @return array<string, int> Array with country as key and count as value
     */
    public function getCountByCountry(): array
    {
        return Company::groupBy('country')
            ->selectRaw('country, COUNT(*) as count')
            ->pluck('count', 'country')
            ->toArray();
    }

    /**
     * Get companies count grouped by year
     *
     * @return array<int, int> Array with year as key and count as value
     */
    public function getCountByYear(): array
    {
        return Company::selectRaw('EXTRACT(YEAR FROM created_at)::integer as year, COUNT(*) as count')
            ->groupBy('year')
            ->orderBy('year')
            ->pluck('count', 'year')
            ->toArray();
    }

    /**
     * Get companies count grouped by month for a specific year
     *
     * @return array<int, int> Array with month (1-12) as key and count as value
     */
    public function getCountByMonth(int $year): array
    {
        $monthlyData = Company::whereYear('created_at', $year)
            ->selectRaw('EXTRACT(MONTH FROM created_at)::integer as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $result = array_fill(1, 12, 0);

        foreach ($monthlyData as $month => $count) {
            $result[(int) $month] = (int) $count;
        }

        return $result;
    }

    /**
     * Count companies with VAT number
     */
    public function countWithVatNumber(): int
    {
        return Company::whereNotNull('ic_dph')->count();
    }

    /**
     * Count companies without VAT number
     */
    public function countWithoutVatNumber(): int
    {
        return Company::whereNull('ic_dph')->count();
    }

    /**
     * Count companies created in a specific year
     */
    public function countByYear(int $year): int
    {
        return Company::whereYear('created_at', $year)->count();
    }

    /**
     * Get companies created in a date range
     */
    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return Company::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get monthly invoice amounts grouped by month using database aggregation
     *
     * @param  string  $companyField  Either 'supplier_company_id' or 'company_id'
     * @return array<int, float> Array indexed by month number (1-12) with total amounts
     */
    private function getMonthlyAmounts(int $companyId, int $year, string $companyField): array
    {
        $result = array_fill(1, 12, 0.0);

        $monthlyTotals = Invoice::where($companyField, $companyId)
            ->whereNotNull('issue_date')
            ->whereYear('issue_date', $year)
            ->selectRaw('EXTRACT(MONTH FROM issue_date)::integer as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        foreach ($monthlyTotals as $month => $total) {
            $result[(int) $month] = (float) $total;
        }

        return $result;
    }
}

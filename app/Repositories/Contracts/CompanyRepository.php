<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CompanyRepository
{
    /**
     * Get all companies with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all companies ordered by name
     */
    public function getAllOrderedByName(): Collection;

    /**
     * Find a company by ID
     */
    public function findById(int $id): ?Company;

    /**
     * Find a company by ICO
     */
    public function findByIco(string $ico): ?Company;

    /**
     * Search companies by ICO or name
     */
    public function searchByIcoOrName(string $query): Collection;

    /**
     * Create a new company
     */
    public function create(array $data): Company;

    /**
     * Update a company
     */
    public function update(Company $company, array $data): bool;

    /**
     * Delete a company
     */
    public function delete(Company $company): bool;

    /**
     * Get total income for a company
     */
    public function getTotalIncome(int $companyId): float;

    /**
     * Get total expenses for a company
     */
    public function getTotalExpenses(int $companyId): float;

    /**
     * Get monthly income for a company for the current year
     */
    public function getMonthlyIncome(int $companyId, int $year): array;

    /**
     * Get monthly expenses for a company for the current year
     */
    public function getMonthlyExpenses(int $companyId, int $year): array;

    /**
     * Upsert multiple companies in batch.
     *
     * @param  array<int, array<string, mixed>>  $companies
     * @param  array<int, string>  $uniqueBy
     * @param  array<int, string>|null  $update
     * @return int Number of rows affected
     */
    public function upsertBatch(array $companies, array $uniqueBy = ['ico'], ?array $update = null): int;

    /**
     * Update VAT data for a company by ICO.
     *
     * @param  array<string, mixed>  $vatData
     */
    public function updateVatData(string $ico, array $vatData): bool;

    /**
     * Update DIC data for a company by ICO.
     *
     * @param  array<string, mixed>  $dicData
     */
    public function updateDicData(string $ico, array $dicData): bool;
}

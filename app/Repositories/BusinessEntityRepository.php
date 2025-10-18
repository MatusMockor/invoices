<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Company;
use App\Repositories\Interfaces\BusinessEntityRepository as BusinessEntityRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BusinessEntityRepository implements BusinessEntityRepositoryContract
{
    /**
     * Get all business entities with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Company::latest()->paginate($perPage);
    }

    /**
     * Get all business entities ordered by name
     */
    public function getAllOrderedByName(): Collection
    {
        return Company::orderBy('name')->get();
    }

    /**
     * Find a business entity by ID
     */
    public function findById(int $id): ?Company
    {
        return Company::find($id);
    }

    /**
     * Find a business entity by ICO
     */
    public function findByIco(string $ico): ?Company
    {
        return Company::where('ico', $ico)->first();
    }

    /**
     * Create a new business entity
     */
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    /**
     * Update a business entity
     */
    public function update(Company $businessEntity, array $data): bool
    {
        return $businessEntity->update($data);
    }

    /**
     * Delete a business entity
     */
    public function delete(Company $businessEntity): bool
    {
        return $businessEntity->delete();
    }
}

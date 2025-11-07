<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\UserCompany;
use App\Repositories\Contracts\BusinessEntityRepository as BusinessEntityRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BusinessEntityRepository implements BusinessEntityRepositoryContract
{
    /**
     * Get all business entities with pagination
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return UserCompany::latest()->paginate($perPage);
    }

    /**
     * Get all business entities ordered by name
     */
    public function getAllOrderedByName(): Collection
    {
        return UserCompany::orderBy('name')->get();
    }

    /**
     * Find a business entity by ID
     */
    public function findById(int $id): ?UserCompany
    {
        return UserCompany::find($id);
    }

    /**
     * Find a business entity by ICO
     */
    public function findByIco(string $ico): ?UserCompany
    {
        return UserCompany::where('ico', $ico)->first();
    }

    /**
     * Create a new business entity
     */
    public function create(array $data): UserCompany
    {
        return UserCompany::create($data);
    }

    /**
     * Update a business entity
     */
    public function update(UserCompany $businessEntity, array $data): bool
    {
        return $businessEntity->update($data);
    }

    /**
     * Delete a business entity
     */
    public function delete(UserCompany $businessEntity): bool
    {
        return $businessEntity->delete();
    }
}

<?php

namespace App\Repositories;

use App\Models\Partner;
use App\Repositories\Interfaces\PartnerRepository as PartnerRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PartnerRepository implements PartnerRepositoryContract
{
    /**
     * Get all partners with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Partner::query()->latest()->paginate($perPage);
    }

    /**
     * Get all partners ordered by name
     *
     * @return Collection
     */
    public function getAllOrderedByName(): Collection
    {
        return Partner::query()->orderBy('name')->get();
    }

    /**
     * Find a partner by ID
     *
     * @param int $id
     * @return Partner|null
     */
    public function findById(int $id): ?Partner
    {
        return Partner::query()->find($id);
    }

    /**
     * Find a partner by ICO
     *
     * @param string $ico
     * @return Partner|null
     */
    public function findByIco(string $ico): ?Partner
    {
        return Partner::query()->where('ico', $ico)->first();
    }

    /**
     * Create a new partner
     *
     * @param array $data
     * @return Partner
     */
    public function create(array $data): Partner
    {
        return Partner::query()->create($data);
    }

    /**
     * Update a partner
     *
     * @param Partner $partner
     * @param array $data
     * @return bool
     */
    public function update(Partner $partner, array $data): bool
    {
        return $partner->update($data);
    }

    /**
     * Delete a partner
     *
     * @param Partner $partner
     * @return bool
     */
    public function delete(Partner $partner): bool
    {
        return $partner->delete();
    }
}

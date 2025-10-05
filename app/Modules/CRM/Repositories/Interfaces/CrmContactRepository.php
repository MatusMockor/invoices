<?php

declare(strict_types=1);

namespace App\Modules\CRM\Repositories\Interfaces;

use App\Modules\CRM\Models\CrmContact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CrmContactRepository
{
    public function find(int $id): ?CrmContact;

    public function findOrFail(int $id): CrmContact;

    public function all(): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): CrmContact;

    public function update(CrmContact $contact, array $data): bool;

    public function delete(CrmContact $contact): bool;

    public function restore(CrmContact $contact): bool;

    public function forceDelete(CrmContact $contact): bool;

    public function search(string $query, int $perPage = 15): LengthAwarePaginator;

    public function findByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator;

    public function findByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function findByTag(string $tagName, int $perPage = 15): LengthAwarePaginator;

    public function getActive(int $perPage = 15): LengthAwarePaginator;

    public function getInactive(int $perPage = 15): LengthAwarePaginator;

    public function getRecentlyContacted(int $days = 30, int $perPage = 15): LengthAwarePaginator;

    public function withRelations(array $relations = []): Collection;

    public function findWithRelations(int $id, array $relations = []): ?CrmContact;

    public function getAllTags(): array;
}

<?php

declare(strict_types=1);

namespace App\Modules\CRM\Repositories;

use App\Modules\CRM\Models\ContactTag;
use App\Modules\CRM\Models\CrmContact;
use App\Modules\CRM\Repositories\Interfaces\CrmContactRepository as CrmContactRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CrmContactRepository implements CrmContactRepositoryContract
{
    public function find(int $id): ?CrmContact
    {
        return CrmContact::find($id);
    }

    public function findOrFail(int $id): CrmContact
    {
        return CrmContact::findOrFail($id);
    }

    public function all(): Collection
    {
        return CrmContact::all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return CrmContact::with([
            'company:id,name',
            'user:id,name,email',
            'tags:id,name',
            'emails:id,contact_id,email,type,is_primary',
            'phones:id,contact_id,phone,type,is_primary',
        ])->paginate($perPage);
    }

    public function create(array $data): CrmContact
    {
        return CrmContact::create($data);
    }

    public function update(CrmContact $contact, array $data): bool
    {
        return $contact->update($data);
    }

    public function delete(CrmContact $contact): bool
    {
        return $contact->delete();
    }

    public function restore(CrmContact $contact): bool
    {
        return $contact->restore();
    }

    public function forceDelete(CrmContact $contact): bool
    {
        return $contact->forceDelete();
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return CrmContact::with([
            'company:id,name',
            'user:id,name,email',
            'tags:id,name',
            'emails:id,contact_id,email,type,is_primary',
            'phones:id,contact_id,phone,type,is_primary',
        ])->search($query)
            ->paginate($perPage);
    }

    public function findByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator
    {
        return CrmContact::with([
            'company:id,name',
            'user:id,name,email',
            'tags:id,name',
            'emails:id,contact_id,email,type,is_primary',
            'phones:id,contact_id,phone,type,is_primary',
        ])->byCompany($companyId)
            ->paginate($perPage);
    }

    public function findByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return CrmContact::with([
            'company:id,name',
            'user:id,name,email',
            'tags:id,name',
            'emails:id,contact_id,email,type,is_primary',
            'phones:id,contact_id,phone,type,is_primary',
        ])->byUser($userId)
            ->paginate($perPage);
    }

    public function findByTag(string $tagName, int $perPage = 15): LengthAwarePaginator
    {
        return CrmContact::with([
            'company:id,name',
            'user:id,name,email',
            'tags:id,name',
            'emails:id,contact_id,email,type,is_primary',
            'phones:id,contact_id,phone,type,is_primary',
        ])->withTag($tagName)
            ->paginate($perPage);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return CrmContact::with([
            'company:id,name',
            'user:id,name,email',
            'tags:id,name',
            'emails:id,contact_id,email,type,is_primary',
            'phones:id,contact_id,phone,type,is_primary',
        ])->active()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getInactive(int $perPage = 15): LengthAwarePaginator
    {
        return CrmContact::with([
            'company:id,name',
            'user:id,name,email',
            'tags:id,name',
            'emails:id,contact_id,email,type,is_primary',
            'phones:id,contact_id,phone,type,is_primary',
        ])->inactive()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getRecentlyContacted(int $days = 30, int $perPage = 15): LengthAwarePaginator
    {
        return CrmContact::with([
            'company:id,name',
            'user:id,name,email',
            'tags:id,name',
            'emails:id,contact_id,email,type,is_primary',
            'phones:id,contact_id,phone,type,is_primary',
        ])->recentlyContacted($days)
            ->paginate($perPage);
    }

    public function withRelations(array $relations = []): Collection
    {
        return CrmContact::with($relations)->get();
    }

    public function findWithRelations(int $id, array $relations = []): ?CrmContact
    {
        return CrmContact::with($relations)->find($id);
    }

    public function getAllTags(): array
    {
        return ContactTag::all()->toArray();
    }
}

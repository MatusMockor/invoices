<?php

declare(strict_types=1);

namespace App\Modules\CRM\Repositories;

use App\Modules\CRM\Models\ContactTag;
use App\Modules\CRM\Repositories\Interfaces\ContactTagRepository as ContactTagRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

class ContactTagRepository implements ContactTagRepositoryContract
{
    public function all(): Collection
    {
        return ContactTag::withCount('contacts')->get();
    }

    public function find(int $id): ?ContactTag
    {
        return ContactTag::find($id);
    }

    public function findByName(string $name): ?ContactTag
    {
        return ContactTag::where('name', $name)->first();
    }

    public function create(array $data): ContactTag
    {
        return ContactTag::create($data);
    }

    public function update(ContactTag $tag, array $data): bool
    {
        return $tag->update($data);
    }

    public function delete(ContactTag $tag): bool
    {
        return $tag->delete();
    }

    public function getActive(): Collection
    {
        return ContactTag::active()->withCount('contacts')->get();
    }
}

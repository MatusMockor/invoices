<?php

declare(strict_types=1);

namespace App\Modules\CRM\Repositories\Interfaces;

use App\Modules\CRM\Models\ContactTag;
use Illuminate\Database\Eloquent\Collection;

interface ContactTagRepository
{
    public function all(): Collection;

    public function find(int $id): ?ContactTag;

    public function findByName(string $name): ?ContactTag;

    public function create(array $data): ContactTag;

    public function update(ContactTag $tag, array $data): bool;

    public function delete(ContactTag $tag): bool;

    public function getActive(): Collection;
}

<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\UserCompany;
use Illuminate\Database\Eloquent\Collection;

interface UserCompanyRepository
{
    public function create(array $data): UserCompany;

    public function update(UserCompany $company, array $data): bool;

    public function findByUserId(int $userId): ?UserCompany;

    public function findAllByUserId(int $userId): Collection;

    public function findMinimalByUserId(int $userId): Collection;
}

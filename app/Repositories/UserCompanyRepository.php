<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\UserCompany;
use App\Repositories\Contracts\UserCompanyRepository as UserCompanyRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

final class UserCompanyRepository implements UserCompanyRepositoryContract
{
    public function create(array $data): UserCompany
    {
        return UserCompany::create($data);
    }

    public function update(UserCompany $company, array $data): bool
    {
        return $company->update($data);
    }

    public function findByUserId(int $userId): ?UserCompany
    {
        return UserCompany::where('user_id', $userId)->first();
    }

    public function findAllByUserId(int $userId): Collection
    {
        return UserCompany::where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }

    public function findMinimalByUserId(int $userId): Collection
    {
        return UserCompany::where('user_id', $userId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}

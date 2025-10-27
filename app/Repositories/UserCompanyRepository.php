<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\UserCompany;
use App\Repositories\Interfaces\UserCompanyRepository as UserCompanyRepositoryContract;

final class UserCompanyRepository implements UserCompanyRepositoryContract
{
    public function create(array $data): UserCompany
    {
        return UserCompany::create($data);
    }

    public function findByUserId(int $userId): ?UserCompany
    {
        return UserCompany::where('user_id', $userId)->first();
    }
}

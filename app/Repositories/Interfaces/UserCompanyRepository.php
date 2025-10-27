<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\UserCompany;

interface UserCompanyRepository
{
    public function create(array $data): UserCompany;

    public function findByUserId(int $userId): ?UserCompany;
}

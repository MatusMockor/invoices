<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\Models\UserCompany;
use App\Repositories\Interfaces\UserCompanyRepository as UserCompanyRepositoryContract;
use Illuminate\Support\Facades\DB;

final class UpdateCompanyAction
{
    public function __construct(
        private readonly UserCompanyRepositoryContract $userCompanyRepository,
    ) {}

    /**
     * Update a company with the provided data.
     */
    public function handle(UserCompany $company, array $data): UserCompany
    {
        return DB::transaction(function () use ($company, $data) {
            $this->userCompanyRepository->update($company, $data);

            return $company->fresh();
        });
    }
}

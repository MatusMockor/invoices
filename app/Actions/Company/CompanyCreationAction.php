<?php

declare(strict_types=1);

namespace App\Actions\Company;

use App\DTOs\Company\CompanyCreationDTO;
use App\Models\User;
use App\Models\UserCompany;
use App\Repositories\Interfaces\UserCompanyRepository as UserCompanyRepositoryContract;
use Illuminate\Support\Facades\DB;

final class CompanyCreationAction
{
    public function __construct(
        private readonly UserCompanyRepositoryContract $userCompanyRepository,
    ) {}

    /**
     * Create a company for the user and set it as current.
     */
    public function handle(CompanyCreationDTO $dto, User $user): UserCompany
    {
        return DB::transaction(function () use ($dto, $user) {
            $userCompany = $this->userCompanyRepository->create([
                'user_id' => $user->id,
                'ico' => $dto->ico,
                'name' => $dto->name,
                'street' => $dto->street,
                'city' => $dto->city,
                'postal_code' => $dto->postalCode,
                'country' => config('invoices.default_country', 'SK'),
                'dic' => $dto->dic,
                'ic_dph' => $dto->icDph,
                'company_type' => config('invoices.default_company_type', 's.r.o.'),
                'registration_number' => '',
            ]);

            $user->current_company_id = $userCompany->id;
            $user->save();

            return $userCompany;
        });
    }
}

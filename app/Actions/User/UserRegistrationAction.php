<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\UserRegistrationDTO;
use App\Models\User;
use App\Repositories\Contracts\UserCompanyRepository as UserCompanyRepositoryContract;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class UserRegistrationAction
{
    public function __construct(
        private readonly UserRepositoryContract $userRepository,
        private readonly UserCompanyRepositoryContract $userCompanyRepository,
    ) {}

    /**
     * Register a new user with their company.
     */
    public function handle(UserRegistrationDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->userRepository->create([
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'password' => Hash::make($dto->password),
            ]);

            $userCompany = $this->userCompanyRepository->create([
                'user_id' => $user->id,
                'ico' => $dto->companyIco,
                'name' => $dto->companyName,
                'street' => $dto->companyStreet,
                'city' => $dto->companyCity,
                'postal_code' => $dto->companyPostalCode,
                'country' => $dto->companyCountry,
                'dic' => $dto->companyDic,
                'ic_dph' => $dto->companyIcDph,
                'phone' => $dto->companyPhone,
                'email' => $dto->companyEmail,
                'website' => $dto->companyWebsite,
                'company_type' => $dto->companyType,
                'registration_number' => '',
            ]);

            $user->current_company_id = $userCompany->id;
            $user->save();

            return $user->fresh();
        });
    }
}

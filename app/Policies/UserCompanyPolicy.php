<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\UserCompany;

class UserCompanyPolicy
{
    /**
     * Determine whether the user can view any companies.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the company.
     */
    public function view(User $user, UserCompany $company): bool
    {
        return $company->user_id === $user->id;
    }

    /**
     * Determine whether the user can create companies.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the company.
     */
    public function update(User $user, UserCompany $company): bool
    {
        return $company->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the company.
     */
    public function delete(User $user, UserCompany $company): bool
    {
        return $company->user_id === $user->id;
    }
}

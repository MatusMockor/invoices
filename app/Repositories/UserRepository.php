<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepository as UserRepositoryContract;

final class UserRepository implements UserRepositoryContract
{
    /**
     * Find a user by ID
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update a user
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Delete a user
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Soft delete a user
     */
    public function softDelete(User $user): bool
    {
        return $user->delete();
    }
}

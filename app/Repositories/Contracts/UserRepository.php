<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepository
{
    /**
     * Find a user by ID
     */
    public function findById(int $id): ?User;

    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user
     */
    public function create(array $data): User;

    /**
     * Update a user
     */
    public function update(User $user, array $data): bool;

    /**
     * Delete a user
     */
    public function delete(User $user): bool;

    /**
     * Soft delete a user
     */
    public function softDelete(User $user): bool;
}

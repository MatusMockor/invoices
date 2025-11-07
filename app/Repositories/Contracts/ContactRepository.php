<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContactRepository
{
    /**
     * Get paginated contacts for a company
     */
    public function getPaginatedByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a contact by ID
     */
    public function findById(int $id): ?Contact;

    /**
     * Create a new contact
     */
    public function create(array $data): Contact;

    /**
     * Update a contact
     */
    public function update(Contact $contact, array $data): bool;

    /**
     * Delete a contact
     */
    public function delete(Contact $contact): bool;
}

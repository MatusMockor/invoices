<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Contact;
use App\Repositories\Contracts\ContactRepository as ContactRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactRepository implements ContactRepositoryContract
{
    /**
     * Get paginated contacts for a company
     */
    public function getPaginatedByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator
    {
        return Contact::query()
            ->where('company_id', $companyId)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($perPage);
    }

    /**
     * Find a contact by ID
     */
    public function findById(int $id): ?Contact
    {
        return Contact::find($id);
    }

    /**
     * Create a new contact
     */
    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    /**
     * Update a contact
     */
    public function update(Contact $contact, array $data): bool
    {
        return $contact->update($data);
    }

    /**
     * Delete a contact
     */
    public function delete(Contact $contact): bool
    {
        return $contact->delete();
    }
}

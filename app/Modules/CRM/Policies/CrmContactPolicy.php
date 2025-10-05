<?php

declare(strict_types=1);

namespace App\Modules\CRM\Policies;

use App\Models\User;
use App\Modules\CRM\Models\CrmContact;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrmContactPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view contacts
    }

    public function view(User $user, CrmContact $contact): bool
    {
        // Users can view contacts from their company or contacts assigned to them
        return $user->current_company_id === $contact->company_id ||
               $user->id === $contact->user_id;
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can create contacts
    }

    public function update(User $user, CrmContact $contact): bool
    {
        // Users can update contacts from their company or contacts assigned to them
        return $user->current_company_id === $contact->company_id ||
               $user->id === $contact->user_id;
    }

    public function delete(User $user, CrmContact $contact): bool
    {
        // Users can delete contacts from their company or contacts assigned to them
        return $user->current_company_id === $contact->company_id ||
               $user->id === $contact->user_id;
    }

    public function restore(User $user, CrmContact $contact): bool
    {
        // Users can restore contacts from their company or contacts assigned to them
        return $user->current_company_id === $contact->company_id ||
               $user->id === $contact->user_id;
    }

    public function forceDelete(User $user, CrmContact $contact): bool
    {
        // Users can force delete contacts from their company or contacts assigned to them
        return $user->current_company_id === $contact->company_id ||
               $user->id === $contact->user_id;
    }
}

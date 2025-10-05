<?php

declare(strict_types=1);

namespace App\Modules\CRM\Models;

class CrmContactObserver
{
    public function created(CrmContact $contact): void
    {
        $this->logActivity($contact, 'created', 'create', 'Contact was created');
    }

    public function updated(CrmContact $contact): void
    {
        $this->logActivity($contact, 'updated', 'update', 'Contact was updated');
    }

    public function deleted(CrmContact $contact): void
    {
        $this->logActivity($contact, 'deleted', 'delete', 'Contact was deleted');
    }

    public function restored(CrmContact $contact): void
    {
        $this->logActivity($contact, 'restored', 'restore', 'Contact was restored');
    }

    public function forceDeleted(CrmContact $contact): void
    {
        $this->logActivity($contact, 'force_deleted', 'force_delete', 'Contact was permanently deleted');
    }

    private function logActivity(CrmContact $contact, string $type, string $action, string $description): void
    {
        ContactActivity::create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'new_values' => $contact->getDirty(),
            'occurred_at' => now(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\ContactActivity;
use App\Modules\CRM\Models\ContactAddress;
use App\Modules\CRM\Models\ContactCustomFieldDefinition;
use App\Modules\CRM\Models\ContactCustomFieldValue;
use App\Modules\CRM\Models\ContactEmail;
use App\Modules\CRM\Models\ContactPhone;
use App\Modules\CRM\Models\ContactTag;
use App\Modules\CRM\Models\CrmContact;
use App\Modules\CRM\Repositories\Interfaces\CrmContactRepository as CrmContactRepositoryContract;
use App\Modules\CRM\Services\Interfaces\CrmContactService as CrmContactServiceContract;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use League\Csv\Writer;

class CrmContactService implements CrmContactServiceContract
{
    public function __construct(
        private readonly CrmContactRepositoryContract $contactRepository
    ) {}

    public function getAllContacts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->paginate($perPage);
    }

    public function getContact(int $id): CrmContact
    {
        return $this->contactRepository->findWithRelations($id, [
            'company',
            'user',
            'emails',
            'phones',
            'addresses',
            'tags',
            'customFieldValues.fieldDefinition',
            'activities.user',
            'notes',
        ]) ?? $this->contactRepository->findOrFail($id);
    }

    public function createContact(array $data): CrmContact
    {
        return DB::transaction(function () use ($data) {
            // Handle notes separately
            $notesData = $data['notes'] ?? [];
            unset($data['notes']);

            $contact = $this->contactRepository->create($data);

            if (isset($data['emails'])) {
                foreach ($data['emails'] as $emailData) {
                    $this->addEmailToContact($contact, $emailData);
                }
            }

            if (isset($data['phones'])) {
                foreach ($data['phones'] as $phoneData) {
                    $this->addPhoneToContact($contact, $phoneData);
                }
            }

            if (isset($data['addresses'])) {
                foreach ($data['addresses'] as $addressData) {
                    $this->addAddressToContact($contact, $addressData);
                }
            }

            if (isset($data['tags'])) {
                foreach ($data['tags'] as $tagName) {
                    $this->addTagToContact($contact, $tagName);
                }
            }

            if (isset($data['custom_fields'])) {
                foreach ($data['custom_fields'] as $fieldSlug => $value) {
                    $this->setCustomFieldValue($contact, $fieldSlug, $value);
                }
            }

            // Add notes
            if (! empty($notesData)) {
                $this->updateContactNotes($contact, $notesData);
            }

            $this->recordContactActivity($contact, 'create', 'Contact was created');

            return $contact;
        });
    }

    public function updateContact(CrmContact $contact, array $data): CrmContact
    {
        return DB::transaction(function () use ($contact, $data) {
            $oldValues = $contact->toArray();

            // Handle notes separately
            $notesData = $data['notes'] ?? [];
            unset($data['notes']);

            $this->contactRepository->update($contact, $data);
            $contact->refresh();

            // Update notes
            if (! empty($notesData)) {
                $this->updateContactNotes($contact, $notesData);
            }

            $this->recordContactActivity(
                $contact,
                'update',
                'Contact was updated',
                ['old_values' => $oldValues, 'new_values' => $contact->toArray()]
            );

            return $contact;
        });
    }

    public function deleteContact(CrmContact $contact): bool
    {
        $this->recordContactActivity($contact, 'delete', 'Contact was deleted');

        return $this->contactRepository->delete($contact);
    }

    public function restoreContact(CrmContact $contact): bool
    {
        $result = $this->contactRepository->restore($contact);

        if ($result) {
            $this->recordContactActivity($contact, 'restore', 'Contact was restored');
        }

        return $result;
    }

    public function forceDeleteContact(CrmContact $contact): bool
    {
        $this->recordContactActivity($contact, 'force_delete', 'Contact was permanently deleted');

        return $this->contactRepository->forceDelete($contact);
    }

    public function searchContacts(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->search($query, $perPage);
    }

    public function getContactsByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->findByCompany($companyId, $perPage);
    }

    public function getContactsByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->findByUser($userId, $perPage);
    }

    public function getContactsByTag(string $tagName, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->findByTag($tagName, $perPage);
    }

    public function getActiveContacts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->getActive($perPage);
    }

    public function getInactiveContacts(int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->getInactive($perPage);
    }

    public function getRecentlyContactedContacts(int $days = 30, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->getRecentlyContacted($days, $perPage);
    }

    public function addEmailToContact(CrmContact $contact, array $emailData): void
    {
        ContactEmail::create([
            'contact_id' => $contact->id,
            'email' => $emailData['email'],
            'type' => $emailData['type'] ?? 'secondary',
            'is_verified' => $emailData['is_verified'] ?? false,
        ]);
    }

    public function addPhoneToContact(CrmContact $contact, array $phoneData): void
    {
        ContactPhone::create([
            'contact_id' => $contact->id,
            'phone' => $phoneData['phone'],
            'type' => $phoneData['type'] ?? 'secondary',
            'country_code' => $phoneData['country_code'] ?? '+421',
            'is_verified' => $phoneData['is_verified'] ?? false,
        ]);
    }

    public function addAddressToContact(CrmContact $contact, array $addressData): void
    {
        ContactAddress::create([
            'contact_id' => $contact->id,
            'type' => $addressData['type'] ?? 'primary',
            'street' => $addressData['street'] ?? null,
            'city' => $addressData['city'] ?? null,
            'postal_code' => $addressData['postal_code'] ?? null,
            'state' => $addressData['state'] ?? null,
            'country' => $addressData['country'] ?? 'Slovakia',
            'latitude' => $addressData['latitude'] ?? null,
            'longitude' => $addressData['longitude'] ?? null,
            'is_primary' => $addressData['is_primary'] ?? false,
        ]);
    }

    public function addTagToContact(CrmContact $contact, string $tagName): void
    {
        $tag = ContactTag::firstOrCreate(['name' => $tagName]);
        $contact->tags()->syncWithoutDetaching([$tag->id]);
    }

    public function removeTagFromContact(CrmContact $contact, string $tagName): void
    {
        $tag = ContactTag::where('name', $tagName)->first();
        if ($tag) {
            $contact->tags()->detach($tag->id);
        }
    }

    public function setCustomFieldValue(CrmContact $contact, string $fieldSlug, mixed $value): void
    {
        $fieldDefinition = ContactCustomFieldDefinition::where('slug', $fieldSlug)->first();

        if ($fieldDefinition) {
            ContactCustomFieldValue::updateOrCreate(
                [
                    'contact_id' => $contact->id,
                    'field_definition_id' => $fieldDefinition->id,
                ],
                ['value' => $value]
            );
        }
    }

    public function importContactsFromCsv(UploadedFile $file): array
    {
        $csv = Reader::createFromPath($file->getPathname());
        $csv->setHeaderOffset(0);

        $imported = 0;
        $errors = [];

        foreach ($csv as $record) {
            try {
                $this->createContact([
                    'first_name' => $record['first_name'] ?? '',
                    'last_name' => $record['last_name'] ?? '',
                    'primary_email' => $record['email'] ?? null,
                    'primary_phone' => $record['phone'] ?? null,
                    'job_title' => $record['job_title'] ?? null,
                    'notes' => $record['notes'] ?? null,
                ]);
                $imported++;
            } catch (Exception $e) {
                $errors[] = 'Row '.($imported + 1).': '.$e->getMessage();
            }
        }

        return ['imported' => $imported, 'errors' => $errors];
    }

    public function exportContactsToCsv(array $contactIds = []): string
    {
        $contacts = empty($contactIds)
            ? CrmContact::with(['company', 'tags'])->get()
            : CrmContact::with(['company', 'tags'])->whereIn('id', $contactIds)->get();

        $filename = 'contacts_export_'.now()->format('Y-m-d_H-i-s').'.csv';
        $filepath = storage_path('app/exports/'.$filename);

        if (! file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $csv = Writer::createFromPath($filepath, 'w+');
        $csv->insertOne([
            'ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Job Title',
            'Company', 'Tags', 'Created At', 'Updated At',
        ]);

        foreach ($contacts as $contact) {
            $csv->insertOne([
                $contact->id,
                $contact->first_name,
                $contact->last_name,
                $contact->primary_email,
                $contact->primary_phone,
                $contact->job_title,
                $contact->company?->name,
                $contact->tags->pluck('name')->join(', '),
                $contact->created_at,
                $contact->updated_at,
            ]);
        }

        return $filepath;
    }

    public function bulkUpdateContacts(array $contactIds, array $data): int
    {
        return CrmContact::whereIn('id', $contactIds)->update($data);
    }

    public function bulkDeleteContacts(array $contactIds): int
    {
        return CrmContact::whereIn('id', $contactIds)->delete();
    }

    public function recordContactActivity(CrmContact $contact, string $action, string $description, array $metadata = []): void
    {
        ContactActivity::create([
            'contact_id' => $contact->id,
            'user_id' => auth()->id(),
            'type' => $action,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    public function getContactActivities(CrmContact $contact, int $perPage = 15): LengthAwarePaginator
    {
        return $contact->activities()
            ->with('user')
            ->orderBy('occurred_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllTags(): array
    {
        return $this->contactRepository->getAllTags();
    }

    private function updateContactNotes(CrmContact $contact, array $notesData): void
    {
        // Get existing notes
        $existingNotes = $contact->notes()->get()->keyBy('id');

        // Process each note
        foreach ($notesData as $noteData) {
            if (isset($noteData['id']) && $noteData['id']) {
                // Update existing note
                if ($existingNotes->has($noteData['id'])) {
                    $existingNotes->get($noteData['id'])->update([
                        'body' => $noteData['content'],
                    ]);
                }
            } else {
                // Create new note
                $contact->notes()->create([
                    'body' => $noteData['content'],
                    'user_id' => auth()->id(),
                ]);
            }
        }

        // Remove notes that are no longer in the data
        $submittedNoteIds = collect($notesData)
            ->filter(fn ($note) => isset($note['id']) && $note['id'])
            ->pluck('id')
            ->toArray();

        $notesToDelete = $existingNotes->keys()->diff($submittedNoteIds);
        if ($notesToDelete->isNotEmpty()) {
            $contact->notes()->whereIn('id', $notesToDelete)->delete();
        }
    }
}

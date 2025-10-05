<?php

declare(strict_types=1);

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\ContactActivity;
use App\Modules\CRM\Models\CrmContact;
use App\Modules\CRM\Repositories\Interfaces\CrmContactRepository as CrmContactRepositoryContract;
use App\Modules\CRM\Services\Interfaces\CrmContactService as CrmContactServiceContract;
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

        // Use chunked processing to handle large datasets efficiently
        $contactChunks = $this->getContactsForExport($contactIds, 1000);

        foreach ($contactChunks as $contacts) {
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
        }

        return $filepath;
    }

    public function bulkUpdateContacts(array $contactIds, array $data): int
    {
        // Process in chunks to avoid memory issues with large datasets
        $chunkSize = 1000;
        $totalUpdated = 0;

        foreach (array_chunk($contactIds, $chunkSize) as $chunk) {
            $totalUpdated += CrmContact::whereIn('id', $chunk)->update($data);
        }

        return $totalUpdated;
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

    private function updateContactNotes(CrmContact $contact, array $notesData): void
    {
        // Get existing notes with proper eager loading
        $existingNotes = $contact->notes()->with('user:id,name')->get()->keyBy('id');

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

<?php

declare(strict_types=1);

namespace App\Modules\CRM\Services\Interfaces;

use App\Modules\CRM\Models\CrmContact;
use Generator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface CrmContactService
{
    public function getAllContacts(int $perPage = 15): LengthAwarePaginator;

    public function getContact(int $id): CrmContact;

    public function createContact(array $data): CrmContact;

    public function updateContact(CrmContact $contact, array $data): CrmContact;

    public function deleteContact(CrmContact $contact): bool;

    public function restoreContact(CrmContact $contact): bool;

    public function forceDeleteContact(CrmContact $contact): bool;

    public function searchContacts(string $query, int $perPage = 15): LengthAwarePaginator;

    public function getContactsByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator;

    public function getContactsByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getContactsByTag(string $tagName, int $perPage = 15): LengthAwarePaginator;

    public function getActiveContacts(int $perPage = 15): LengthAwarePaginator;

    public function getInactiveContacts(int $perPage = 15): LengthAwarePaginator;

    public function getRecentlyContactedContacts(int $days = 30, int $perPage = 15): LengthAwarePaginator;

    public function addEmailToContact(CrmContact $contact, array $emailData): void;

    public function addPhoneToContact(CrmContact $contact, array $phoneData): void;

    public function addAddressToContact(CrmContact $contact, array $addressData): void;

    public function addTagToContact(CrmContact $contact, string $tagName): void;

    public function removeTagFromContact(CrmContact $contact, string $tagName): void;

    public function setCustomFieldValue(CrmContact $contact, string $fieldSlug, mixed $value): void;

    public function importContactsFromCsv(UploadedFile $file): array;

    public function exportContactsToCsv(array $contactIds = []): string;

    public function bulkUpdateContacts(array $contactIds, array $data): int;

    public function bulkDeleteContacts(array $contactIds): int;

    public function recordContactActivity(CrmContact $contact, string $action, string $description, array $metadata = []): void;

    public function getContactActivities(CrmContact $contact, int $perPage = 15): LengthAwarePaginator;

    public function getAllTags(): array;

    public function getContactsWithFullRelations(int $perPage = 15): LengthAwarePaginator;

    public function getContactsForExport(array $contactIds = [], int $chunkSize = 1000): Generator;
}

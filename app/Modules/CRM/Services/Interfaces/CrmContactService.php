<?php

declare(strict_types=1);

namespace App\Modules\CRM\Services\Interfaces;

use App\Modules\CRM\Models\CrmContact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface CrmContactService
{
    public function createContact(array $data): CrmContact;

    public function updateContact(CrmContact $contact, array $data): CrmContact;

    public function searchContacts(string $query, int $perPage = 15): LengthAwarePaginator;

    public function getContactsByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator;

    public function getContactsByUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getContactsByTag(string $tagName, int $perPage = 15): LengthAwarePaginator;

    public function getActiveContacts(int $perPage = 15): LengthAwarePaginator;

    public function getInactiveContacts(int $perPage = 15): LengthAwarePaginator;

    public function getRecentlyContactedContacts(int $days = 30, int $perPage = 15): LengthAwarePaginator;

    public function importContactsFromCsv(UploadedFile $file): array;

    public function exportContactsToCsv(array $contactIds = []): string;

    public function bulkUpdateContacts(array $contactIds, array $data): int;

    public function getContactActivities(CrmContact $contact, int $perPage = 15): LengthAwarePaginator;

    public function getAllTags(): array;

    public function recordContactActivity(CrmContact $contact, string $action, string $description, array $metadata = []): void;
}

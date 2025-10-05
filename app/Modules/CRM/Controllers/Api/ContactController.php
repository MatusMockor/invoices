<?php

declare(strict_types=1);

namespace App\Modules\CRM\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactBulkUpdateRequest;
use App\Http\Requests\ContactImportRequest;
use App\Modules\CRM\Enums\ContactStatus;
use App\Modules\CRM\Http\Requests\CrmContactCreateRequest;
use App\Modules\CRM\Http\Requests\CrmContactDeleteRequest;
use App\Modules\CRM\Http\Requests\CrmContactUpdateRequest;
use App\Modules\CRM\Http\Resources\ContactImportResource;
use App\Modules\CRM\Http\Resources\CrmContactResource;
use App\Modules\CRM\Models\CrmContact;
use App\Modules\CRM\Services\Interfaces\CrmContactService as CrmContactServiceContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function __construct(
        private readonly CrmContactServiceContract $contactService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        // Default values for AJAX requests
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $companyId = $request->get('company_id', auth()->user()->current_company_id);
        $userId = $request->get('user_id');
        $tag = $request->get('tag');
        $status = $request->get('status', ContactStatus::ACTIVE->value);

        $contacts = match (true) {
            ! empty($search) => $this->contactService->searchContacts($search, $perPage),
            ! empty($tag) => $this->contactService->getContactsByTag($tag, $perPage),
            ! empty($userId) => $this->contactService->getContactsByUser((int) $userId, $perPage),
            ! empty($companyId) => $this->contactService->getContactsByCompany((int) $companyId, $perPage),
            $status === ContactStatus::INACTIVE->value => $this->contactService->getInactiveContacts($perPage),
            default => $this->contactService->getActiveContacts($perPage),
        };

        return CrmContactResource::collection($contacts);
    }

    public function store(CrmContactCreateRequest $request): JsonResource
    {
        $contact = $this->contactService->createContact($request->validated());

        return new CrmContactResource($contact);
    }

    public function show(CrmContact $contact): JsonResource
    {
        $contact = $this->contactService->getContact($contact->id);

        return new CrmContactResource($contact);
    }

    public function update(CrmContactUpdateRequest $request, CrmContact $contact): JsonResource
    {
        $contact = $this->contactService->updateContact($contact, $request->validated());

        return new CrmContactResource($contact);
    }

    public function destroy(CrmContactDeleteRequest $request, CrmContact $contact): Response
    {
        $forceDelete = $request->boolean('force_delete', false);

        if ($forceDelete) {
            $this->contactService->forceDeleteContact($contact);
        } else {
            $this->contactService->deleteContact($contact);
        }

        return response()->noContent();
    }

    public function bulkDelete(Request $request): Response
    {
        $request->validate([
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
        ]);

        $this->contactService->bulkDeleteContacts($request->get('contact_ids'));

        return response()->noContent();
    }

    public function bulkUpdate(ContactBulkUpdateRequest $request): Response
    {
        $this->contactService->bulkUpdateContacts(
            $request->get('contact_ids'),
            $request->get('data')
        );

        return response()->noContent();
    }

    public function export(Request $request): JsonResource
    {
        $contactIds = $request->get('contact_ids', []);
        $filepath = $this->contactService->exportContactsToCsv($contactIds);

        return new JsonResource([
            'download_url' => Storage::url('exports/'.basename($filepath)),
        ]);
    }

    public function import(ContactImportRequest $request): ContactImportResource
    {
        $file = $request->file('file');
        $result = $this->contactService->importContactsFromCsv($file);

        return new ContactImportResource($result);
    }

    public function restore(CrmContact $contact): CrmContactResource
    {
        $this->contactService->restoreContact($contact);

        return new CrmContactResource($contact);
    }

    public function activities(Request $request, CrmContact $contact): AnonymousResourceCollection
    {
        $perPage = (int) $request->get('per_page', 15);
        $activities = $this->contactService->getContactActivities($contact, $perPage);

        return JsonResource::collection($activities);
    }

    public function tags(): JsonResource
    {
        $tags = $this->contactService->getAllTags();

        return new JsonResource($tags);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\CRM\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\CRM\Enums\ContactStatus;
use App\Modules\CRM\Http\Requests\ContactBulkUpdateRequest;
use App\Modules\CRM\Http\Requests\ContactImportRequest;
use App\Modules\CRM\Http\Requests\CrmContactCreateRequest;
use App\Modules\CRM\Http\Requests\CrmContactDeleteRequest;
use App\Modules\CRM\Http\Requests\CrmContactUpdateRequest;
use App\Modules\CRM\Http\Resources\CrmContactResource;
use App\Modules\CRM\Models\CrmContact;
use App\Modules\CRM\Services\Interfaces\CrmContactService as CrmContactServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function __construct(
        private readonly CrmContactServiceContract $contactService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $search = $request->get('search');
        $companyId = $request->get('company_id');
        $userId = $request->get('user_id');
        $tag = $request->get('tag');
        $status = $request->get('status', ContactStatus::ACTIVE->value);
        $isActive = $status === ContactStatus::ACTIVE->value;

        // If no company_id is provided, use the current user's company
        if (empty($companyId) && auth()->user()->current_company_id) {
            $companyId = auth()->user()->current_company_id;
        }

        $contacts = match (true) {
            ! empty($search) => $this->contactService->searchContacts($search, $perPage),
            ! empty($tag) => $this->contactService->getContactsByTag($tag, $perPage),
            ! $isActive && ! empty($companyId) => $this->contactService->getContactsByCompany((int) $companyId, $perPage),
            ! $isActive => $this->contactService->getInactiveContacts($perPage),
            ! empty($companyId) => $this->contactService->getContactsByCompany((int) $companyId, $perPage),
            ! empty($userId) => $this->contactService->getContactsByUser((int) $userId, $perPage),
            default => $this->contactService->getActiveContacts($perPage),
        };

        return response()->json([
            'data' => CrmContactResource::collection($contacts->items()),
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'last_page' => $contacts->lastPage(),
                'per_page' => $contacts->perPage(),
                'total' => $contacts->total(),
            ],
        ]);
    }

    public function store(CrmContactCreateRequest $request): JsonResponse
    {
        $contact = $this->contactService->createContact($request->validated());

        return response()->json([
            'data' => new CrmContactResource($contact),
            'message' => 'Contact created successfully.',
        ], Response::HTTP_CREATED);
    }

    public function show(CrmContact $contact): JsonResponse
    {
        $contact = $this->contactService->getContact($contact->id);

        return response()->json([
            'data' => new CrmContactResource($contact),
        ]);
    }

    public function update(CrmContactUpdateRequest $request, CrmContact $contact): JsonResponse
    {
        $contact = $this->contactService->updateContact($contact, $request->validated());

        return response()->json([
            'data' => new CrmContactResource($contact),
            'message' => 'Contact updated successfully.',
        ]);
    }

    public function destroy(CrmContactDeleteRequest $request, CrmContact $contact): JsonResponse
    {
        $forceDelete = $request->boolean('force_delete', false);

        if ($forceDelete) {
            $this->contactService->forceDeleteContact($contact);
            $message = 'Contact permanently deleted.';
        } else {
            $this->contactService->deleteContact($contact);
            $message = 'Contact deleted.';
        }

        return response()->json([
            'message' => $message,
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
        ]);

        $deleted = $this->contactService->bulkDeleteContacts($request->get('contact_ids'));

        return response()->json([
            'message' => "Deleted {$deleted} contacts.",
            'deleted_count' => $deleted,
        ]);
    }

    public function bulkUpdate(ContactBulkUpdateRequest $request): JsonResponse
    {
        $updated = $this->contactService->bulkUpdateContacts(
            $request->get('contact_ids'),
            $request->get('data')
        );

        return response()->json([
            'message' => "Updated {$updated} contacts.",
            'updated_count' => $updated,
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $contactIds = $request->get('contact_ids', []);
        $filepath = $this->contactService->exportContactsToCsv($contactIds);

        return response()->json([
            'download_url' => Storage::url('exports/'.basename($filepath)),
            'message' => 'Export prepared for download.',
        ]);
    }

    public function import(ContactImportRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $result = $this->contactService->importContactsFromCsv($file);

        return response()->json([
            'message' => "Import completed. Imported: {$result['imported']} contacts.",
            'imported' => $result['imported'],
            'errors' => $result['errors'],
        ]);
    }

    public function restore(CrmContact $contact): JsonResponse
    {
        $this->contactService->restoreContact($contact);

        return response()->json([
            'data' => new CrmContactResource($contact),
            'message' => 'Contact restored successfully.',
        ]);
    }

    public function activities(Request $request, CrmContact $contact): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 15);
        $activities = $this->contactService->getContactActivities($contact, $perPage);

        return response()->json([
            'data' => $activities->items(),
            'meta' => [
                'current_page' => $activities->currentPage(),
                'last_page' => $activities->lastPage(),
                'per_page' => $activities->perPage(),
                'total' => $activities->total(),
            ],
        ]);
    }

    public function tags(): JsonResponse
    {
        $tags = $this->contactService->getAllTags();

        return response()->json([
            'data' => $tags,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use App\Repositories\Interfaces\ContactRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactRepository $contactRepository
    ) {}

    public function index(Request $request): ContactCollection
    {
        $contacts = $this->contactRepository->getPaginatedByCompany(
            auth()->user()->current_company_id,
            $request->input('per_page', 15)
        );

        return new ContactCollection($contacts);
    }

    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact);
    }

    public function store(ContactCreateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        $contact = $this->contactRepository->create(array_merge($validated, [
            'company_id' => $user->current_company_id,
            'user_id' => $user->id,
        ]));

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    public function update(ContactUpdateRequest $request, Contact $contact): ContactResource
    {
        $this->contactRepository->update($contact, $request->validated());

        return new ContactResource($contact->fresh());
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorizeForUser(auth()->user(), 'delete', $contact);
        $this->contactRepository->delete($contact);

        return response()->json(['message' => 'Contact deleted successfully']);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): ContactCollection
    {
        $contacts = Contact::query()
            ->where('company_id', auth()->user()->current_company_id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate($request->input('per_page', 15));

        return new ContactCollection($contacts);
    }

    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = auth()->user();

        $contact = Contact::create(array_merge($validated, [
            'company_id' => $user->current_company_id,
            'user_id' => $user->id,
        ]));

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, Contact $contact): ContactResource
    {
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $contact->update($validated);

        return new ContactResource($contact);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorizeForUser(auth()->user(), 'delete', $contact);
        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }
}

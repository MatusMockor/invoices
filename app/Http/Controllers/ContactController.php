<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $contacts = Contact::query()
            ->where('company_id', auth()->user()->current_company_id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15);

        return view('contacts.index', compact('contacts'));
    }

    public function create(): View
    {
        return view('contacts.create');
    }

    public function store(Request $request): RedirectResponse
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

        Contact::create(array_merge($validated, [
            'company_id' => $user->current_company_id,
            'user_id' => $user->id,
        ]));

        return redirect()->route('contacts.index')->with('success', 'Contact created');
    }

    public function edit(Contact $contact): View
    {
        // Optional: add policy later
        return view('contacts.edit', compact('contact'));
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        // Optional: add policy later
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
        $contact->update($validated);

        return redirect()->route('contacts.index')->with('success', 'Contact updated');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorizeForUser(auth()->user(), 'delete', $contact);
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', 'Contact deleted');
    }
}

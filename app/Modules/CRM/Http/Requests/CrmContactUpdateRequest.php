<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Requests;

use App\Modules\CRM\Enums\ContactStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmContactUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('update', $this->route('contact'));
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'primary_email' => ['nullable', 'email', 'max:255'],
            'primary_phone' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'company_id' => ['required', 'exists:companies,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['boolean'],
            'status' => ['nullable', 'string', Rule::in(ContactStatus::values())],
            'metadata' => ['nullable', 'array'],
            'last_contacted_at' => ['nullable', 'date'],

            'emails' => ['nullable', 'array'],
            'emails.*.id' => ['nullable', 'exists:contact_emails,id'],
            'emails.*.email' => ['required_with:emails', 'email', 'max:255'],
            'emails.*.type' => ['required_with:emails', 'string', Rule::in(['primary', 'secondary', 'work', 'personal'])],
            'emails.*.is_verified' => ['boolean'],

            'phones' => ['nullable', 'array'],
            'phones.*.id' => ['nullable', 'exists:contact_phones,id'],
            'phones.*.phone' => ['required_with:phones', 'string', 'max:20'],
            'phones.*.type' => ['required_with:phones', 'string', Rule::in(['primary', 'secondary', 'work', 'mobile', 'home'])],
            'phones.*.country_code' => ['required_with:phones', 'string', 'max:5'],
            'phones.*.is_verified' => ['boolean'],

            'addresses' => ['nullable', 'array'],
            'addresses.*.id' => ['nullable', 'exists:contact_addresses,id'],
            'addresses.*.type' => ['required_with:addresses', 'string', Rule::in(['primary', 'secondary', 'work', 'home', 'billing'])],
            'addresses.*.street' => ['nullable', 'string', 'max:255'],
            'addresses.*.city' => ['nullable', 'string', 'max:255'],
            'addresses.*.postal_code' => ['nullable', 'string', 'max:20'],
            'addresses.*.state' => ['nullable', 'string', 'max:255'],
            'addresses.*.country' => ['nullable', 'string', 'max:255'],
            'addresses.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'addresses.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'addresses.*.is_primary' => ['boolean'],

            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:255'],

            'notes' => ['nullable', 'array'],
            'notes.*.id' => ['nullable', 'exists:notes,id'],
            'notes.*.content' => ['required', 'string', 'max:1000'],

            'custom_fields' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Meno je povinné.',
            'last_name.required' => 'Priezvisko je povinné.',
            'company_id.required' => 'Spoločnosť je povinná.',
            'company_id.exists' => 'Vybraná spoločnosť neexistuje.',
            'primary_email.email' => 'Hlavný email musí byť platná emailová adresa.',
            'emails.*.email.required_with' => 'Email je povinný.',
            'emails.*.email.email' => 'Email musí byť platná emailová adresa.',
            'phones.*.phone.required_with' => 'Telefón je povinný.',
            'addresses.*.latitude.between' => 'Zemepisná šírka musí byť medzi -90 a 90.',
            'addresses.*.longitude.between' => 'Zemepisná dĺžka musí byť medzi -180 a 180.',
            'notes.*.content.required_with' => 'Obsah poznámky je povinný.',
            'notes.*.content.max' => 'Poznámka môže mať maximálne 1000 znakov.',
        ];
    }
}

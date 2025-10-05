<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer', 'exists:contacts,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_ids.required' => 'Contact IDs are required.',
            'contact_ids.array' => 'Contact IDs must be an array.',
            'contact_ids.min' => 'At least one contact ID is required.',
            'contact_ids.*.integer' => 'Each contact ID must be an integer.',
            'contact_ids.*.exists' => 'One or more contact IDs do not exist.',
        ];
    }
}

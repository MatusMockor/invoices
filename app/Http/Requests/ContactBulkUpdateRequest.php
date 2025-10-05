<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactBulkUpdateRequest extends FormRequest
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
            'data' => ['required', 'array'],
            'data.position' => ['nullable', 'string', 'max:255'],
            'data.notes' => ['nullable', 'string'],
            'data.is_active' => ['nullable', 'boolean'],
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
            'data.required' => 'Update data is required.',
            'data.array' => 'Update data must be an array.',
            'data.position.string' => 'Position must be a string.',
            'data.position.max' => 'Position cannot exceed 255 characters.',
            'data.notes.string' => 'Notes must be a string.',
            'data.is_active.boolean' => 'Active status must be a boolean.',
        ];
    }
}

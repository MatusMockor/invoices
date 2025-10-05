<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a string.',
            'first_name.max' => 'First name cannot exceed 255 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.string' => 'Last name must be a string.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',
            'email.email' => 'Email must be a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'phone.string' => 'Phone must be a string.',
            'phone.max' => 'Phone cannot exceed 20 characters.',
            'position.string' => 'Position must be a string.',
            'position.max' => 'Position cannot exceed 255 characters.',
            'notes.string' => 'Notes must be a string.',
            'company_id.exists' => 'Selected company does not exist.',
            'is_active.boolean' => 'Active status must be a boolean.',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ContactBulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'contact_ids' => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer', 'exists:crm_contacts,id'],
            'data' => ['required', 'array'],
            'data.job_title' => ['nullable', 'string', 'max:255'],
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
            'data.job_title.string' => 'Job title must be a string.',
            'data.job_title.max' => 'Job title cannot exceed 255 characters.',
            'data.is_active.boolean' => 'Active status must be a boolean.',
        ];
    }

    public function getContactIds(): array
    {
        return $this->validated('contact_ids');
    }

    public function getData(): array
    {
        return $this->validated('data');
    }

    public function getJobTitle(): ?string
    {
        return $this->validated('data.job_title');
    }

    public function isActive(): ?bool
    {
        return $this->validated('data.is_active');
    }
}

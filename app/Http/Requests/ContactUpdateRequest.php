<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ContactUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function getFirstName(): ?string
    {
        return $this->validated('first_name');
    }

    public function getLastName(): ?string
    {
        return $this->validated('last_name');
    }

    public function getEmail(): ?string
    {
        return $this->validated('email');
    }

    public function getPhone(): ?string
    {
        return $this->validated('phone');
    }

    public function getPosition(): ?string
    {
        return $this->validated('position');
    }

    public function getNotes(): ?string
    {
        return $this->validated('notes');
    }
}

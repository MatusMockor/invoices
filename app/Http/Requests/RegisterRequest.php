<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\EmailWhitelisted;
use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', app(EmailWhitelisted::class)],
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function getFirstName(): string
    {
        return $this->validated('first_name');
    }

    public function getLastName(): string
    {
        return $this->validated('last_name');
    }

    public function getEmail(): string
    {
        return $this->validated('email');
    }

    public function getPassword(): string
    {
        return $this->validated('password');
    }

    public function getData(): array
    {
        return [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
        ];
    }
}

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

    public function getData(): array
    {
        return [
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'email' => $this->input('email'),
            'password' => $this->input('password'),
        ];
    }
}

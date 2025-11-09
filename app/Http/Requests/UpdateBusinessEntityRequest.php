<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBusinessEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'ico' => 'sometimes|required|string|max:20',
            'dic' => 'nullable|string|max:20',
            'ic_dph' => 'nullable|string|max:20',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'company_type' => 'sometimes|required|string|max:50',
            'registration_number' => 'sometimes|required|string|max:50',
        ];
    }

    public function getName(): ?string
    {
        return $this->validated('name');
    }

    public function getIco(): ?string
    {
        return $this->validated('ico');
    }

    public function getDic(): ?string
    {
        return $this->validated('dic');
    }

    public function getIcDph(): ?string
    {
        return $this->validated('ic_dph');
    }

    public function getAddress(): ?string
    {
        return $this->validated('address');
    }

    public function getCity(): ?string
    {
        return $this->validated('city');
    }

    public function getPostalCode(): ?string
    {
        return $this->validated('postal_code');
    }

    public function getCountry(): ?string
    {
        return $this->validated('country');
    }

    public function getPhone(): ?string
    {
        return $this->validated('phone');
    }

    public function getEmail(): ?string
    {
        return $this->validated('email');
    }

    public function getCompanyType(): ?string
    {
        return $this->validated('company_type');
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->validated('registration_number');
    }
}

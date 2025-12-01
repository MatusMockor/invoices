<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ico' => 'required|string|max:20',
            'name' => 'required|string|max:200',
            'street' => 'required|string|max:200',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'dic' => 'nullable|string|max:20',
            'ic_dph' => 'nullable|string|max:20',
            'registry_office' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
        ];
    }

    public function getIco(): string
    {
        return $this->validated('ico');
    }

    public function getName(): string
    {
        return $this->validated('name');
    }

    public function getStreet(): string
    {
        return $this->validated('street');
    }

    public function getCity(): string
    {
        return $this->validated('city');
    }

    public function getPostalCode(): string
    {
        return $this->validated('postal_code');
    }

    public function getDic(): ?string
    {
        return $this->validated('dic');
    }

    public function getIcDph(): ?string
    {
        return $this->validated('ic_dph');
    }

    public function getRegistryOffice(): ?string
    {
        return $this->validated('registry_office');
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->validated('registration_number');
    }
}

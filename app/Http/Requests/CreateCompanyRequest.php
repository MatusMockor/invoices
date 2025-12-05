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
            'registration_office' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'swift' => ['nullable', 'string', 'max:11', 'regex:/^[A-Z]{4}[A-Z]{2}[A-Z0-9]{2}([A-Z0-9]{3})?$/i'],
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

    public function getRegistrationOffice(): ?string
    {
        return $this->validated('registration_office');
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->validated('registration_number');
    }

    public function getIban(): ?string
    {
        $iban = $this->validated('iban');

        if (! $iban) {
            return null;
        }

        return str_replace(' ', '', strtoupper($iban));
    }

    public function getSwift(): ?string
    {
        $swift = $this->validated('swift');

        if (! $swift) {
            return null;
        }

        return strtoupper($swift);
    }
}

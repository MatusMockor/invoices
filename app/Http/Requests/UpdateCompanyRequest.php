<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'ico' => 'required|string|max:20|regex:/^\d+$/',
            'dic' => 'nullable|string|max:20|regex:/^\d*$/',
            'ic_dph' => 'nullable|string|max:30',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'iban' => 'nullable|string|max:50',
            'swift' => 'nullable|string|max:20',
        ];
    }

    public function getName(): string
    {
        return $this->validated('name');
    }

    public function getIco(): string
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

    public function getCountry(): string
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

    public function getIban(): ?string
    {
        return $this->validated('iban');
    }

    public function getSwift(): ?string
    {
        return $this->validated('swift');
    }

    public function getData(): array
    {
        return [
            'name' => $this->getName(),
            'ico' => $this->getIco(),
            'dic' => $this->getDic(),
            'ic_dph' => $this->getIcDph(),
            'street' => $this->getStreet(),
            'city' => $this->getCity(),
            'postal_code' => $this->getPostalCode(),
            'country' => $this->getCountry(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
            'iban' => $this->getIban(),
            'swift' => $this->getSwift(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterWithCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:100|confirmed',
            'company_ico' => 'required|string|max:20',
            'company_name' => 'required|string|max:200',
            'company_street' => 'required|string|max:200',
            'company_city' => 'required|string|max:100',
            'company_postal_code' => 'required|string|max:10',
            'company_country' => 'required|string|max:100',
            'company_dic' => 'nullable|string|max:20',
            'company_ic_dph' => 'nullable|string|max:20',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|string|email|max:255',
            'company_website' => 'nullable|string|url|max:255',
            'company_type' => 'required|string|max:50',
            'company_registration_number' => 'nullable|string|max:255',
            'company_registry_office' => 'nullable|string|max:255',
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

    public function getCompanyIco(): string
    {
        return $this->validated('company_ico');
    }

    public function getCompanyName(): string
    {
        return $this->validated('company_name');
    }

    public function getCompanyStreet(): string
    {
        return $this->validated('company_street');
    }

    public function getCompanyCity(): string
    {
        return $this->validated('company_city');
    }

    public function getCompanyPostalCode(): string
    {
        return $this->validated('company_postal_code');
    }

    public function getCompanyDic(): ?string
    {
        return $this->validated('company_dic');
    }

    public function getCompanyIcDph(): ?string
    {
        return $this->validated('company_ic_dph');
    }

    public function getCompanyCountry(): string
    {
        return $this->validated('company_country');
    }

    public function getCompanyPhone(): ?string
    {
        return $this->validated('company_phone');
    }

    public function getCompanyEmail(): ?string
    {
        return $this->validated('company_email');
    }

    public function getCompanyWebsite(): ?string
    {
        return $this->validated('company_website');
    }

    public function getCompanyType(): string
    {
        return $this->validated('company_type');
    }

    public function getCompanyRegistrationNumber(): ?string
    {
        return $this->validated('company_registration_number');
    }

    public function getCompanyRegistryOffice(): ?string
    {
        return $this->validated('company_registry_office');
    }
}

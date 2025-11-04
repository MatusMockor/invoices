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
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|max:100|confirmed',
            'company_ico' => 'required|string|max:20',
            'company_name' => 'required|string|max:200',
            'company_street' => 'required|string|max:200',
            'company_city' => 'required|string|max:100',
            'company_postal_code' => 'required|string|max:10',
            'company_dic' => 'nullable|string|max:20',
            'company_ic_dph' => 'nullable|string|max:20',
        ];
    }

    public function getName(): string
    {
        return $this->input('name');
    }

    public function getEmail(): string
    {
        return $this->input('email');
    }

    public function getPassword(): string
    {
        return $this->input('password');
    }

    public function getCompanyIco(): string
    {
        return $this->input('company_ico');
    }

    public function getCompanyName(): string
    {
        return $this->input('company_name');
    }

    public function getCompanyStreet(): string
    {
        return $this->input('company_street');
    }

    public function getCompanyCity(): string
    {
        return $this->input('company_city');
    }

    public function getCompanyPostalCode(): string
    {
        return $this->input('company_postal_code');
    }

    public function getCompanyDic(): ?string
    {
        return $this->input('company_dic');
    }

    public function getCompanyIcDph(): ?string
    {
        return $this->input('company_ic_dph');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'ico' => 'required|string|max:20',
            'dic' => 'nullable|string|max:20',
            'ic_dph' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'bank_account' => 'nullable|string|max:100',
            'iban' => 'nullable|string|max:34',
            'swift' => 'nullable|string|max:11',
        ];
    }

    public function getData(): array
    {
        return [
            'user_id' => auth()->id(),
            'name' => $this->input('name'),
            'ico' => $this->input('ico'),
            'dic' => $this->input('dic'),
            'ic_dph' => $this->input('ic_dph'),
            'street' => $this->input('address'),
            'city' => $this->input('city'),
            'postal_code' => $this->input('postal_code'),
            'country' => $this->input('country'),
            'phone' => $this->input('phone'),
            'email' => $this->input('email'),
            'iban' => $this->input('iban'),
            'swift' => $this->input('swift'),
            'company_type' => 'SRO',
            'registration_number' => $this->input('ico'),
        ];
    }
}

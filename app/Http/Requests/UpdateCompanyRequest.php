<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
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
            'bank_account' => 'nullable|string|max:100',
            'iban' => 'nullable|string|max:34',
            'swift' => 'nullable|string|max:11',
        ];
    }

    public function getData(): array
    {
        $data = [];

        if ($this->has('name')) {
            $data['name'] = $this->input('name');
        }
        if ($this->has('ico')) {
            $data['ico'] = $this->input('ico');
        }
        if ($this->has('dic')) {
            $data['dic'] = $this->input('dic');
        }
        if ($this->has('ic_dph')) {
            $data['ic_dph'] = $this->input('ic_dph');
        }
        if ($this->has('address')) {
            $data['street'] = $this->input('address');
        }
        if ($this->has('city')) {
            $data['city'] = $this->input('city');
        }
        if ($this->has('postal_code')) {
            $data['postal_code'] = $this->input('postal_code');
        }
        if ($this->has('country')) {
            $data['country'] = $this->input('country');
        }
        if ($this->has('phone')) {
            $data['phone'] = $this->input('phone');
        }
        if ($this->has('email')) {
            $data['email'] = $this->input('email');
        }
        if ($this->has('iban')) {
            $data['iban'] = $this->input('iban');
        }
        if ($this->has('swift')) {
            $data['swift'] = $this->input('swift');
        }

        return $data;
    }
}

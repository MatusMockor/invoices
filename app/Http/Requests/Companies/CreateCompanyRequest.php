<?php

namespace App\Http\Requests\Companies;

use Illuminate\Foundation\Http\FormRequest;

class CreateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'street' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'ico' => 'nullable|string|max:255',
            'dic' => 'nullable|string|max:255',
            'ic_dph' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34|regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/',
            'swift' => 'nullable|string|min:8|max:11|regex:/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'company_type' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'iban.regex' => 'The IBAN must be in valid format (e.g., SK1234567890123456789012).',
            'swift.regex' => 'The SWIFT/BIC code must be in valid format (e.g., TATRSKBX).',
        ];
    }
}

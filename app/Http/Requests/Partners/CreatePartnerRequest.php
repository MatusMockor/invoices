<?php

namespace App\Http\Requests\Partners;

use Illuminate\Foundation\Http\FormRequest;

class CreatePartnerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'ico' => ['required', 'string', 'size:8', 'digits'],
            'dic' => ['nullable', 'string', 'max:10'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'max:255'],
            'ic_dph' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'string', 'max:255'],
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
            'name.required' => 'Názov spoločnosti je povinný',
            'ico.required' => 'IČO je povinné',
            'street.required' => 'Ulica je povinná',
            'city.required' => 'Mesto je povinné',
            'postal_code.required' => 'PSČ je povinné',
            'country.required' => 'Krajina je povinná',
            'registration_number.required' => 'Registračné číslo je povinné',
            'company_type.required' => 'Právna forma je povinná',
        ];
    }
}

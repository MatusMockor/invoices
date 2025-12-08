<?php

declare(strict_types=1);

namespace App\Http\Requests\BusinessEntities;

use App\Enums\CompanyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class CreateBusinessEntityRequest extends FormRequest
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
            'ico' => ['required', 'string', 'max:8'],
            'dic' => ['nullable', 'string', 'max:10'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'country' => ['required', 'string', 'max:255'],
            'ic_dph' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', new Enum(CompanyType::class)],
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
            'registration_number.required' => 'Registracne cislo je povinne',
            'type.required' => 'Pravna forma je povinna',
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

    public function getIcDph(): ?string
    {
        return $this->validated('ic_dph');
    }

    public function getRegistrationNumber(): string
    {
        return $this->validated('registration_number');
    }

    public function getType(): CompanyType
    {
        return CompanyType::from($this->validated('type'));
    }
}

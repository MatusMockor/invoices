<?php

declare(strict_types=1);

namespace App\Http\Requests\Companies;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCompanyRequest extends FormRequest
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
            'ico' => 'required|string|max:255',
            'dic' => 'nullable|string|max:255',
            'ic_dph' => 'nullable|string|max:255',
        ];
    }

    public function getName(): string
    {
        return $this->validated('name');
    }

    public function getStreet(): ?string
    {
        return $this->validated('street');
    }

    public function getCity(): ?string
    {
        return $this->validated('city');
    }

    public function getPostalCode(): ?string
    {
        return $this->validated('postal_code');
    }

    public function getCountry(): ?string
    {
        return $this->validated('country');
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
}

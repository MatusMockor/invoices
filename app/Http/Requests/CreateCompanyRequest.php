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
        ];
    }

    public function getIco(): string
    {
        return $this->input('ico');
    }

    public function getName(): string
    {
        return $this->input('name');
    }

    public function getStreet(): string
    {
        return $this->input('street');
    }

    public function getCity(): string
    {
        return $this->input('city');
    }

    public function getPostalCode(): string
    {
        return $this->input('postal_code');
    }

    public function getDic(): ?string
    {
        return $this->input('dic');
    }

    public function getIcDph(): ?string
    {
        return $this->input('ic_dph');
    }
}

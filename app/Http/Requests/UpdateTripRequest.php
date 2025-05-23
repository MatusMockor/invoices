<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripRequest extends FormRequest
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
            'date' => ['sometimes', 'date'],
            'start_location' => ['sometimes', 'string', 'max:255'],
            'end_location' => ['sometimes', 'string', 'max:255'],
            'purpose' => ['sometimes', 'string', 'max:255'],
            'start_odometer' => ['sometimes', 'integer', 'min:0'],
            'end_odometer' => ['sometimes', 'integer', 'min:0', 'gt:start_odometer'],
            'distance' => ['sometimes', 'integer', 'min:0'],
            'driver_name' => ['sometimes', 'string', 'max:255'],
            'fuel_amount' => ['nullable', 'numeric', 'min:0'],
            'fuel_cost' => ['nullable', 'numeric', 'min:0'],
            'fuel_receipt_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}

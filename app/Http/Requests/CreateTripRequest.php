<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTripRequest extends FormRequest
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
            'vehicle_id' => ['required', 'integer', 'exists:vehicles,id'],
            'date' => ['required', 'date'],
            'start_location' => ['required', 'string', 'max:255'],
            'end_location' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'start_odometer' => ['required', 'integer', 'min:0'],
            'end_odometer' => ['required', 'integer', 'min:0', 'gt:start_odometer'],
            'distance' => ['required', 'integer', 'min:0'],
            'driver_name' => ['required', 'string', 'max:255'],
            'fuel_amount' => ['nullable', 'numeric', 'min:0'],
            'fuel_cost' => ['nullable', 'numeric', 'min:0'],
            'fuel_receipt_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}

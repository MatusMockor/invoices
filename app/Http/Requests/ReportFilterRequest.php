<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.before_or_equal' => 'Dátum začiatku musí byť pred alebo rovný dátumu konca.',
            'end_date.after_or_equal' => 'Dátum konca musí byť po alebo rovný dátumu začiatku.',
        ];
    }

    public function getStartDate(): ?string
    {
        return $this->validated('start_date');
    }

    public function getEndDate(): ?string
    {
        return $this->validated('end_date');
    }
}

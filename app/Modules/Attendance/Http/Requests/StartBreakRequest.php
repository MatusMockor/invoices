<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Http\Requests;

use App\Modules\Attendance\Enums\BreakType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartBreakRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $attendance = $this->route('attendance');

        // User can only start break for their own attendance
        return auth()->check() && $attendance->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'break_type' => ['required', Rule::enum(BreakType::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'break_type' => 'typ prestávky',
            'note' => 'poznámka',
        ];
    }
}

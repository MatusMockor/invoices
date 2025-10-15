<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'note' => ['required', 'string'],
            'follow_up_date' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => auth()->id(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Http\Requests;

use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'taskable_type' => ['nullable', 'string'],
            'taskable_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::enum(TaskStatus::class)],
            'priority' => ['nullable', 'string', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => auth()->user()->current_company_id,
            'user_id' => auth()->id(),
        ]);
    }
}

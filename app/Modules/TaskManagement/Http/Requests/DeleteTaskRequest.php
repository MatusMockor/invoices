<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrmContactDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('delete', $this->route('contact'));
    }

    public function rules(): array
    {
        return [
            'force_delete' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'force_delete.boolean' => 'Force delete musí byť boolean hodnota.',
        ];
    }
}

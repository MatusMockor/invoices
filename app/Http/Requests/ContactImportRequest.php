<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ContactImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File is required.',
            'file.file' => 'Must be a valid file.',
            'file.mimes' => 'File must be a CSV or TXT file.',
            'file.max' => 'File size cannot exceed 10MB.',
        ];
    }

    public function getFile()
    {
        return $this->validated('file');
    }
}

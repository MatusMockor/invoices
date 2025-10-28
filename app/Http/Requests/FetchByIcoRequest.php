<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FetchByIcoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'ico' => 'required|string|max:20',
        ];
    }

    public function getIco(): string
    {
        return $this->validated('ico');
    }
}

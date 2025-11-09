<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Company;

use Illuminate\Foundation\Http\FormRequest;

final class SearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:2'],
        ];
    }

    public function getQuery(): string
    {
        return $this->validated('query');
    }
}

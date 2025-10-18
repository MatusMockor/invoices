<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'noteable_type' => ['required', 'string'],
            'noteable_id' => ['required', 'integer'],
            'body' => ['required', 'string'],
        ];
    }

    public function getNoteableType(): string
    {
        return $this->validated('noteable_type');
    }

    public function getNoteableId(): int
    {
        return (int) $this->validated('noteable_id');
    }

    public function getBody(): string
    {
        return $this->validated('body');
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\CRM\Http\Requests;

use App\Modules\CRM\Enums\ContactStatus;
use Illuminate\Foundation\Http\FormRequest;

class ContactIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'tag' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,inactive,all'],
            'sort_by' => ['nullable', 'string', 'in:first_name,last_name,created_at,updated_at,last_contacted_at'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],
        ];
    }

    public function getPerPage(): int
    {
        return (int) $this->get('per_page', 15);
    }

    public function getSearch(): ?string
    {
        return $this->get('search');
    }

    public function getCompanyId(): ?int
    {
        return $this->get('company_id') ? (int) $this->get('company_id') : null;
    }

    public function getUserId(): ?int
    {
        return $this->get('user_id') ? (int) $this->get('user_id') : null;
    }

    public function getTag(): ?string
    {
        return $this->get('tag');
    }

    public function getStatus(): ContactStatus
    {
        $status = $this->get('status');

        return $status ? ContactStatus::from($status) : ContactStatus::ACTIVE;
    }

    public function getSortBy(): string
    {
        return $this->get('sort_by', 'created_at');
    }

    public function getSortDirection(): string
    {
        return $this->get('sort_direction', 'desc');
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\CRM\Filters;

use App\Modules\CRM\Enums\ContactStatus;
use Illuminate\Database\Eloquent\Builder;

class ContactFilter
{
    public function __construct(
        private readonly ?string $search = null,
        private readonly ?int $companyId = null,
        private readonly ?int $userId = null,
        private readonly ?string $tag = null,
        private readonly ?ContactStatus $status = null,
        private readonly string $sortBy = 'created_at',
        private readonly string $sortDirection = 'desc'
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            companyId: $data['company_id'] ?? null,
            userId: $data['user_id'] ?? null,
            tag: $data['tag'] ?? null,
            status: $data['status'] ?? null,
            sortBy: $data['sort_by'] ?? 'created_at',
            sortDirection: $data['sort_direction'] ?? 'desc'
        );
    }

    public function apply(Builder $query): Builder
    {
        $query = $this->applySearch($query);
        $query = $this->applyCompanyFilter($query);
        $query = $this->applyUserFilter($query);
        $query = $this->applyTagFilter($query);
        $query = $this->applyStatusFilter($query);
        $query = $this->applySorting($query);

        return $query;
    }

    private function applySearch(Builder $query): Builder
    {
        if (empty($this->search)) {
            return $query;
        }

        return $query->where(function (Builder $q) {
            $searchTerm = "%{$this->search}%";

            $q->where('first_name', 'like', $searchTerm)
                ->orWhere('last_name', 'like', $searchTerm)
                ->orWhere('primary_email', 'like', $searchTerm)
                ->orWhere('primary_phone', 'like', $searchTerm)
                ->orWhere('job_title', 'like', $searchTerm)
                ->orWhereHas('emails', function (Builder $emailQuery) use ($searchTerm) {
                    $emailQuery->where('email', 'like', $searchTerm);
                })
                ->orWhereHas('phones', function (Builder $phoneQuery) use ($searchTerm) {
                    $phoneQuery->where('phone', 'like', $searchTerm);
                });
        });
    }

    private function applyCompanyFilter(Builder $query): Builder
    {
        if ($this->companyId === null) {
            return $query;
        }

        return $query->where('company_id', $this->companyId);
    }

    private function applyUserFilter(Builder $query): Builder
    {
        if ($this->userId === null) {
            return $query;
        }

        return $query->where('user_id', $this->userId);
    }

    private function applyTagFilter(Builder $query): Builder
    {
        if (empty($this->tag)) {
            return $query;
        }

        return $query->whereHas('tags', function (Builder $q) {
            $q->where('name', $this->tag);
        });
    }

    private function applyStatusFilter(Builder $query): Builder
    {
        if ($this->status === null) {
            return $query;
        }

        return $query->where('is_active', $this->status === ContactStatus::ACTIVE);
    }

    private function applySorting(Builder $query): Builder
    {
        return $query->orderBy($this->sortBy, $this->sortDirection);
    }
}

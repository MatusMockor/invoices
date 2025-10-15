<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Repositories\Interfaces;

use App\Modules\TaskManagement\Models\FollowUp;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface FollowUpRepository
{
    public function create(array $data): FollowUp;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function findById(int $id): ?FollowUp;

    public function getByTask(int $taskId): Collection;

    public function getCompleted(int $perPage = 15): LengthAwarePaginator;

    public function getPending(int $perPage = 15): LengthAwarePaginator;

    public function getOverdue(int $perPage = 15): LengthAwarePaginator;

    public function getDueToday(int $perPage = 15): LengthAwarePaginator;
}

<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Repositories\Interfaces;

use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepository
{
    public function create(array $data): Task;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function findById(int $id): ?Task;

    public function getByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator;

    public function getByStatus(TaskStatus $status, int $perPage = 15): LengthAwarePaginator;

    public function getByPriority(TaskPriority $priority, int $perPage = 15): LengthAwarePaginator;

    public function getAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getCreatedBy(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getOverdue(int $perPage = 15): LengthAwarePaginator;

    public function getDueToday(int $perPage = 15): LengthAwarePaginator;

    public function getDueThisWeek(int $perPage = 15): LengthAwarePaginator;

    public function search(string $search, int $perPage = 15): LengthAwarePaginator;

    public function getTaskableRelated(string $taskableType, int $taskableId): Collection;
}

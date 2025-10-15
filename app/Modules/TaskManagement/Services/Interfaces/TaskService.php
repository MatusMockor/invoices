<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Services\Interfaces;

use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TaskService
{
    public function createTask(array $data): Task;

    public function updateTask(int $id, array $data): bool;

    public function deleteTask(int $id): bool;

    public function getTaskById(int $id): ?Task;

    public function getTasksByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator;

    public function getTasksByStatus(TaskStatus $status, int $perPage = 15): LengthAwarePaginator;

    public function getTasksByPriority(TaskPriority $priority, int $perPage = 15): LengthAwarePaginator;

    public function getTasksAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getTasksCreatedBy(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function getOverdueTasks(int $perPage = 15): LengthAwarePaginator;

    public function getTasksDueToday(int $perPage = 15): LengthAwarePaginator;

    public function getTasksDueThisWeek(int $perPage = 15): LengthAwarePaginator;

    public function searchTasks(string $search, int $perPage = 15): LengthAwarePaginator;

    public function getRelatedTasks(string $taskableType, int $taskableId): Collection;

    public function markTaskAsCompleted(int $id): bool;

    public function markTaskAsInProgress(int $id): bool;

    public function markTaskAsCancelled(int $id): bool;

    public function assignTask(int $taskId, int $userId): bool;

    public function addFollowUp(int $taskId, array $data): bool;
}

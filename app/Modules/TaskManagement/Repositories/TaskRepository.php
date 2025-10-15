<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Repositories;

use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Models\Task;
use App\Modules\TaskManagement\Repositories\Interfaces\TaskRepository as TaskRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements TaskRepositoryContract
{
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $task = Task::findOrFail($id);

        return $task->update($data);
    }

    public function delete(int $id): bool
    {
        $task = Task::findOrFail($id);

        return (bool) $task->delete();
    }

    public function findById(int $id): ?Task
    {
        return Task::with(['company', 'user', 'assignedUser', 'followUps'])
            ->find($id);
    }

    public function getByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['user', 'assignedUser', 'followUps'])
            ->byCompany($companyId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getByStatus(TaskStatus $status, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['company', 'user', 'assignedUser', 'followUps'])
            ->byStatus($status)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getByPriority(TaskPriority $priority, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['company', 'user', 'assignedUser', 'followUps'])
            ->byPriority($priority)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['company', 'user', 'followUps'])
            ->assignedTo($userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getCreatedBy(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['company', 'assignedUser', 'followUps'])
            ->createdBy($userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getOverdue(int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['company', 'user', 'assignedUser', 'followUps'])
            ->overdue()
            ->orderBy('due_date')
            ->paginate($perPage);
    }

    public function getDueToday(int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['company', 'user', 'assignedUser', 'followUps'])
            ->dueToday()
            ->orderBy('due_date')
            ->paginate($perPage);
    }

    public function getDueThisWeek(int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['company', 'user', 'assignedUser', 'followUps'])
            ->dueThisWeek()
            ->orderBy('due_date')
            ->paginate($perPage);
    }

    public function search(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Task::with(['company', 'user', 'assignedUser', 'followUps'])
            ->search($search)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getTaskableRelated(string $taskableType, int $taskableId): Collection
    {
        return Task::with(['user', 'assignedUser', 'followUps'])
            ->where('taskable_type', $taskableType)
            ->where('taskable_id', $taskableId)
            ->orderByDesc('created_at')
            ->get();
    }
}

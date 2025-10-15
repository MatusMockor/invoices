<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Services;

use App\Modules\TaskManagement\Enums\TaskPriority;
use App\Modules\TaskManagement\Enums\TaskStatus;
use App\Modules\TaskManagement\Models\Task;
use App\Modules\TaskManagement\Repositories\Interfaces\FollowUpRepository as FollowUpRepositoryContract;
use App\Modules\TaskManagement\Repositories\Interfaces\TaskRepository as TaskRepositoryContract;
use App\Modules\TaskManagement\Services\Interfaces\TaskService as TaskServiceContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskService implements TaskServiceContract
{
    public function __construct(
        private readonly TaskRepositoryContract $taskRepository,
        private readonly FollowUpRepositoryContract $followUpRepository
    ) {}

    public function createTask(array $data): Task
    {
        return $this->taskRepository->create($data);
    }

    public function updateTask(int $id, array $data): bool
    {
        return $this->taskRepository->update($id, $data);
    }

    public function deleteTask(int $id): bool
    {
        return $this->taskRepository->delete($id);
    }

    public function getTaskById(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    public function getTasksByCompany(int $companyId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getByCompany($companyId, $perPage);
    }

    public function getTasksByStatus(TaskStatus $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getByStatus($status, $perPage);
    }

    public function getTasksByPriority(TaskPriority $priority, int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getByPriority($priority, $perPage);
    }

    public function getTasksAssignedTo(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getAssignedTo($userId, $perPage);
    }

    public function getTasksCreatedBy(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getCreatedBy($userId, $perPage);
    }

    public function getOverdueTasks(int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getOverdue($perPage);
    }

    public function getTasksDueToday(int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getDueToday($perPage);
    }

    public function getTasksDueThisWeek(int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->getDueThisWeek($perPage);
    }

    public function searchTasks(string $search, int $perPage = 15): LengthAwarePaginator
    {
        return $this->taskRepository->search($search, $perPage);
    }

    public function getRelatedTasks(string $taskableType, int $taskableId): Collection
    {
        return $this->taskRepository->getTaskableRelated($taskableType, $taskableId);
    }

    public function markTaskAsCompleted(int $id): bool
    {
        $task = $this->taskRepository->findById($id);

        if (! $task) {
            return false;
        }

        $task->markAsCompleted();

        return true;
    }

    public function markTaskAsInProgress(int $id): bool
    {
        $task = $this->taskRepository->findById($id);

        if (! $task) {
            return false;
        }

        $task->markAsInProgress();

        return true;
    }

    public function markTaskAsCancelled(int $id): bool
    {
        $task = $this->taskRepository->findById($id);

        if (! $task) {
            return false;
        }

        $task->markAsCancelled();

        return true;
    }

    public function assignTask(int $taskId, int $userId): bool
    {
        return $this->taskRepository->update($taskId, ['assigned_to' => $userId]);
    }

    public function addFollowUp(int $taskId, array $data): bool
    {
        $data['task_id'] = $taskId;
        $this->followUpRepository->create($data);

        return true;
    }
}

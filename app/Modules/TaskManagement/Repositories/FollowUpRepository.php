<?php

declare(strict_types=1);

namespace App\Modules\TaskManagement\Repositories;

use App\Modules\TaskManagement\Models\FollowUp;
use App\Modules\TaskManagement\Repositories\Interfaces\FollowUpRepository as FollowUpRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class FollowUpRepository implements FollowUpRepositoryContract
{
    public function create(array $data): FollowUp
    {
        return FollowUp::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $followUp = FollowUp::findOrFail($id);

        return $followUp->update($data);
    }

    public function delete(int $id): bool
    {
        $followUp = FollowUp::findOrFail($id);

        return (bool) $followUp->delete();
    }

    public function findById(int $id): ?FollowUp
    {
        return FollowUp::with(['task', 'user'])->find($id);
    }

    public function getByTask(int $taskId): Collection
    {
        return FollowUp::with(['user'])
            ->where('task_id', $taskId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getCompleted(int $perPage = 15): LengthAwarePaginator
    {
        return FollowUp::with(['task', 'user'])
            ->completed()
            ->orderByDesc('completed_at')
            ->paginate($perPage);
    }

    public function getPending(int $perPage = 15): LengthAwarePaginator
    {
        return FollowUp::with(['task', 'user'])
            ->pending()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getOverdue(int $perPage = 15): LengthAwarePaginator
    {
        return FollowUp::with(['task', 'user'])
            ->overdue()
            ->orderBy('follow_up_date')
            ->paginate($perPage);
    }

    public function getDueToday(int $perPage = 15): LengthAwarePaginator
    {
        return FollowUp::with(['task', 'user'])
            ->dueToday()
            ->orderBy('follow_up_date')
            ->paginate($perPage);
    }
}

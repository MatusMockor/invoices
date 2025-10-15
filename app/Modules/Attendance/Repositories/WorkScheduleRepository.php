<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Repositories;

use App\Modules\Attendance\Models\WorkSchedule;
use App\Modules\Attendance\Repositories\Interfaces\WorkScheduleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WorkScheduleRepository implements WorkScheduleRepositoryInterface
{
    public function create(array $data): WorkSchedule
    {
        return WorkSchedule::create($data);
    }

    public function update(WorkSchedule $schedule, array $data): WorkSchedule
    {
        $schedule->update($data);

        return $schedule->fresh();
    }

    public function delete(WorkSchedule $schedule): bool
    {
        return $schedule->delete();
    }

    public function findById(int $id): ?WorkSchedule
    {
        return WorkSchedule::with(['user', 'company'])->find($id);
    }

    public function getSchedulesForUser(int $userId, int $companyId): Collection
    {
        return WorkSchedule::byUser($userId)
            ->byCompany($companyId)
            ->active()
            ->orderBy('day_of_week')
            ->get();
    }

    public function getScheduleForUserAndDay(int $userId, int $companyId, int $dayOfWeek): ?WorkSchedule
    {
        return WorkSchedule::byUser($userId)
            ->byCompany($companyId)
            ->where('day_of_week', $dayOfWeek)
            ->active()
            ->first();
    }
}

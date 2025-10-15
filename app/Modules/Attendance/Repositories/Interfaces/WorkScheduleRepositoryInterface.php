<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Repositories\Interfaces;

use App\Modules\Attendance\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Collection;

interface WorkScheduleRepositoryInterface
{
    public function create(array $data): WorkSchedule;

    public function update(WorkSchedule $schedule, array $data): WorkSchedule;

    public function delete(WorkSchedule $schedule): bool;

    public function findById(int $id): ?WorkSchedule;

    public function getSchedulesForUser(int $userId, int $companyId): Collection;

    public function getScheduleForUserAndDay(int $userId, int $companyId, int $dayOfWeek): ?WorkSchedule;
}

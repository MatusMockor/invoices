<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Repositories\Interfaces;

use App\Modules\Attendance\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function create(array $data): Attendance;

    public function update(Attendance $attendance, array $data): Attendance;

    public function delete(Attendance $attendance): bool;

    public function findById(int $id): ?Attendance;

    public function getActiveAttendanceForUser(int $userId, int $companyId): ?Attendance;

    public function getAttendancesByDateRange(int $companyId, string $startDate, string $endDate, ?int $userId = null): Collection;

    public function getMonthlyAttendances(int $companyId, int $year, int $month, ?int $userId = null): Collection;

    public function getPendingApprovals(int $companyId): Collection;
}

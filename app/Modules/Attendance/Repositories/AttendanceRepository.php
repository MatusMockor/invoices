<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Repositories;

use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Repositories\Interfaces\AttendanceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function create(array $data): Attendance
    {
        return Attendance::create($data);
    }

    public function update(Attendance $attendance, array $data): Attendance
    {
        $attendance->update($data);

        return $attendance->fresh();
    }

    public function delete(Attendance $attendance): bool
    {
        return $attendance->delete();
    }

    public function findById(int $id): ?Attendance
    {
        return Attendance::with(['user', 'breaks', 'approvedBy'])
            ->find($id);
    }

    public function getActiveAttendanceForUser(int $userId, int $companyId): ?Attendance
    {
        return Attendance::where('user_id', $userId)
            ->where('company_id', $companyId)
            ->whereNull('check_out')
            ->with(['breaks'])
            ->latest('check_in')
            ->first();
    }

    public function getAttendancesByDateRange(int $companyId, string $startDate, string $endDate, ?int $userId = null): Collection
    {
        $query = Attendance::byCompany($companyId)
            ->with(['user', 'breaks', 'approvedBy'])
            ->dateRange($startDate, $endDate);

        if ($userId) {
            $query->byUser($userId);
        }

        return $query->orderBy('check_in', 'desc')->get();
    }

    public function getMonthlyAttendances(int $companyId, int $year, int $month, ?int $userId = null): Collection
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return $this->getAttendancesByDateRange(
            $companyId,
            $startDate->toDateTimeString(),
            $endDate->toDateTimeString(),
            $userId
        );
    }

    public function getPendingApprovals(int $companyId): Collection
    {
        return Attendance::byCompany($companyId)
            ->byStatus(AttendanceStatus::PENDING)
            ->whereNotNull('check_out')
            ->with(['user', 'breaks'])
            ->orderBy('check_out', 'desc')
            ->get();
    }
}

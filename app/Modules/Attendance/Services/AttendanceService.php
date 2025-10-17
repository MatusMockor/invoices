<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Enums\AttendanceStatus;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceBreak;
use App\Modules\Attendance\Repositories\Interfaces\AttendanceRepositoryInterface;
use App\Modules\Attendance\Services\Interfaces\AttendanceServiceInterface;
use Carbon\Carbon;
use Exception;

class AttendanceService implements AttendanceServiceInterface
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository
    ) {}

    public function checkIn(int $userId, int $companyId, array $data): Attendance
    {
        // Check if user already has an active attendance
        $activeAttendance = $this->attendanceRepository->getActiveAttendanceForUser($userId, $companyId);

        if ($activeAttendance) {
            throw new Exception('Používateľ už má aktívnu dochádzku. Najprv sa musí odhlásiť.');
        }

        $attendanceData = array_merge([
            'user_id' => $userId,
            'company_id' => $companyId,
            'check_in' => now(),
            'check_in_ip' => request()->ip(),
            'status' => AttendanceStatus::PENDING,
        ], $data);

        return $this->attendanceRepository->create($attendanceData);
    }

    public function checkOut(Attendance $attendance, array $data = []): Attendance
    {
        if ($attendance->check_out) {
            throw new Exception('Dochádzka už bola ukončená.');
        }

        // End any active breaks
        $activeBreak = $attendance->breaks()->whereNull('break_end')->first();
        if ($activeBreak) {
            $this->endBreak($activeBreak);
        }

        $checkOutData = array_merge([
            'check_out' => now(),
            'check_out_ip' => request()->ip(),
        ], $data);

        $attendance = $this->attendanceRepository->update($attendance, $checkOutData);

        // Calculate total minutes
        $totalMinutes = $attendance->calculateTotalMinutes();
        $this->attendanceRepository->update($attendance, ['total_minutes' => $totalMinutes]);

        return $attendance->fresh();
    }

    public function startBreak(Attendance $attendance, array $data): AttendanceBreak
    {
        if ($attendance->check_out) {
            throw new Exception('Nemožno začať prestávku, dochádzka už bola ukončená.');
        }

        // Check if there's already an active break
        $activeBreak = $attendance->breaks()->whereNull('break_end')->first();
        if ($activeBreak) {
            throw new Exception('Už prebieha aktívna prestávka.');
        }

        $breakData = array_merge([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ], $data);

        return AttendanceBreak::create($breakData);
    }

    public function endBreak(AttendanceBreak $break): AttendanceBreak
    {
        if ($break->break_end) {
            throw new Exception('Prestávka už bola ukončená.');
        }

        $break->update([
            'break_end' => now(),
        ]);

        // Calculate duration after break_end is set
        $duration = $break->calculateDuration();
        $break->update(['duration_minutes' => $duration]);

        // Recalculate total attendance minutes if attendance is checked out
        if ($break->attendance->check_out) {
            $totalMinutes = $break->attendance->calculateTotalMinutes();
            $this->attendanceRepository->update($break->attendance, ['total_minutes' => $totalMinutes]);
        }

        return $break->fresh();
    }

    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
        return $this->attendanceRepository->update($attendance, $data);
    }

    public function approveAttendance(Attendance $attendance, int $approvedBy, ?string $note = null): Attendance
    {
        if ($attendance->status === AttendanceStatus::APPROVED) {
            throw new Exception('Dochádzka už bola schválená.');
        }

        if (! $attendance->check_out) {
            throw new Exception('Nemožno schváliť dochádzku, ktorá ešte nebola ukončená.');
        }

        return $this->attendanceRepository->update($attendance, [
            'status' => AttendanceStatus::APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'approval_note' => $note,
        ]);
    }

    public function rejectAttendance(Attendance $attendance, int $rejectedBy, string $note): Attendance
    {
        if ($attendance->status === AttendanceStatus::APPROVED) {
            throw new Exception('Nemožno zamietnuť už schválenú dochádzku.');
        }

        return $this->attendanceRepository->update($attendance, [
            'status' => AttendanceStatus::REJECTED,
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'approval_note' => $note,
        ]);
    }

    public function getMonthlyReport(int $companyId, int $year, int $month, ?int $userId = null): array
    {
        $attendances = $this->attendanceRepository->getMonthlyAttendances($companyId, $year, $month, $userId);

        $totalMinutes = $attendances->sum('total_minutes');
        $totalDays = $attendances->count();
        $approvedDays = $attendances->where('status', AttendanceStatus::APPROVED)->count();
        $pendingDays = $attendances->where('status', AttendanceStatus::PENDING)->count();

        $totalBreakMinutes = $attendances->flatMap(fn ($a) => $a->breaks)->sum('duration_minutes');

        return [
            'attendances' => $attendances,
            'summary' => [
                'total_days' => $totalDays,
                'approved_days' => $approvedDays,
                'pending_days' => $pendingDays,
                'total_hours' => round($totalMinutes / 60, 2),
                'total_break_hours' => round($totalBreakMinutes / 60, 2),
                'average_hours_per_day' => $totalDays > 0 ? round($totalMinutes / 60 / $totalDays, 2) : 0,
            ],
        ];
    }

    public function getTodayAttendance(int $userId, int $companyId): ?Attendance
    {
        $today = Carbon::today();
        $attendances = $this->attendanceRepository->getAttendancesByDateRange(
            $companyId,
            $today->startOfDay()->toDateTimeString(),
            $today->endOfDay()->toDateTimeString(),
            $userId
        );

        return $attendances->first();
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Services\Interfaces;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceBreak;

interface AttendanceServiceInterface
{
    public function checkIn(int $userId, int $companyId, array $data): Attendance;

    public function checkOut(Attendance $attendance, array $data = []): Attendance;

    public function startBreak(Attendance $attendance, array $data): AttendanceBreak;

    public function endBreak(AttendanceBreak $break): AttendanceBreak;

    public function updateAttendance(Attendance $attendance, array $data): Attendance;

    public function approveAttendance(Attendance $attendance, int $approvedBy, ?string $note = null): Attendance;

    public function rejectAttendance(Attendance $attendance, int $rejectedBy, string $note): Attendance;

    public function getMonthlyReport(int $companyId, int $year, int $month, ?int $userId = null): array;

    public function getTodayAttendance(int $userId, int $companyId): ?Attendance;
}

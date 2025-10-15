<?php

declare(strict_types=1);

namespace App\Modules\Attendance\Policies;

use App\Models\User;
use App\Modules\Attendance\Models\Attendance;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendances.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view their own attendances
        return true;
    }

    /**
     * Determine whether the user can view the attendance.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        // User can view their own attendance in their company
        return $attendance->user_id === $user->id
            && $attendance->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can create attendances.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create their own attendance
        return true;
    }

    /**
     * Determine whether the user can update the attendance.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // User can update their own attendance if it's pending and in their company
        return $attendance->user_id === $user->id
            && $attendance->status->value === 'pending'
            && $attendance->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can delete the attendance.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // User can delete their own pending attendance
        return $attendance->user_id === $user->id
            && $attendance->status->value === 'pending'
            && $attendance->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can approve the attendance.
     */
    public function approve(User $user, Attendance $attendance): bool
    {
        // For now, users cannot approve (can be extended later with roles)
        return false;
    }

    /**
     * Determine whether the user can export attendance reports.
     */
    public function export(User $user): bool
    {
        // All users can export their own attendance
        return true;
    }
}

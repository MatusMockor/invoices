<?php

declare(strict_types=1);

use App\Modules\Attendance\Controllers\Api\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('attendance')->name('attendance.')->group(function () {
    // Options endpoint for dropdowns
    Route::get('options', [AttendanceController::class, 'options'])
        ->name('options');

    // Today's attendance
    Route::get('today', [AttendanceController::class, 'today'])
        ->name('today');

    // Check in/out
    Route::post('check-in', [AttendanceController::class, 'checkIn'])
        ->name('check-in');
    Route::post('{attendance}/check-out', [AttendanceController::class, 'checkOut'])
        ->name('check-out');

    // Breaks
    Route::post('{attendance}/breaks/start', [AttendanceController::class, 'startBreak'])
        ->name('breaks.start');
    Route::post('breaks/{break}/end', [AttendanceController::class, 'endBreak'])
        ->name('breaks.end');

    // Approval
    Route::post('{attendance}/approve', [AttendanceController::class, 'approve'])
        ->name('approve');
    Route::post('{attendance}/reject', [AttendanceController::class, 'reject'])
        ->name('reject');

    // Monthly report
    Route::get('monthly-report', [AttendanceController::class, 'monthlyReport'])
        ->name('monthly-report');

    // Resource routes
    Route::apiResource('attendances', AttendanceController::class)
        ->except(['store']);
});

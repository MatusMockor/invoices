<?php

declare(strict_types=1);

use App\Modules\Attendance\Controllers\Api\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('attendances')->group(function () {
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
    Route::post('{attendance}/start-break', [AttendanceController::class, 'startBreak'])
        ->name('start-break');
    Route::post('breaks/{break}/end', [AttendanceController::class, 'endBreak'])
        ->name('end-break');

    // Approval
    Route::post('{attendance}/approve', [AttendanceController::class, 'approve'])
        ->name('approve');
    Route::post('{attendance}/reject', [AttendanceController::class, 'reject'])
        ->name('reject');

    // Monthly report
    Route::get('monthly-report', [AttendanceController::class, 'monthlyReport'])
        ->name('monthly-report');

    // Get specific attendance
    Route::get('{attendance}', [AttendanceController::class, 'show'])
        ->name('show');

    // Update attendance
    Route::put('{attendance}', [AttendanceController::class, 'update'])
        ->name('update');

    // Delete attendance
    Route::delete('{attendance}', [AttendanceController::class, 'destroy'])
        ->name('destroy');

    // List attendances - must be last
    Route::get('/', [AttendanceController::class, 'index'])
        ->name('index');
});

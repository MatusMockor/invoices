<?php

declare(strict_types=1);

use App\Modules\Attendance\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/attendance', [AttendanceController::class, 'index'])
    ->name('attendance.index');

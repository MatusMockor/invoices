<?php

declare(strict_types=1);

use App\Modules\TaskManagement\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');

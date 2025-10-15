<?php

declare(strict_types=1);

use App\Modules\TaskManagement\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::get('/tasks/calendar', [TaskController::class, 'calendar'])->name('tasks.calendar');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');
Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update');
Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

Route::post('/tasks/{id}/complete', [TaskController::class, 'markAsCompleted'])->name('tasks.complete');
Route::post('/tasks/{id}/in-progress', [TaskController::class, 'markAsInProgress'])->name('tasks.in-progress');
Route::post('/tasks/{id}/cancel', [TaskController::class, 'markAsCancelled'])->name('tasks.cancel');
Route::post('/tasks/{id}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
Route::post('/tasks/{id}/follow-ups', [TaskController::class, 'addFollowUp'])->name('tasks.follow-ups.store');

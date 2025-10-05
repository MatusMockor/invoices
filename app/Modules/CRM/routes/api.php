<?php

declare(strict_types=1);

use App\Modules\CRM\Controllers\Api\ContactController;
use Illuminate\Support\Facades\Route;

// CRM API Routes
Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store');
Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
Route::post('/contacts/{contact}/restore', [ContactController::class, 'restore'])->name('contacts.restore');
Route::get('/contacts/{contact}/activities', [ContactController::class, 'activities'])->name('contacts.activities');

// Bulk operations
Route::post('/contacts/bulk-update', [ContactController::class, 'bulkUpdate'])->name('contacts.bulk-update');
Route::post('/contacts/bulk-delete', [ContactController::class, 'bulkDelete'])->name('contacts.bulk-delete');

// Import/Export
Route::post('/contacts/import', [ContactController::class, 'import'])->name('contacts.import');
Route::post('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');
Route::get('/tags', [ContactController::class, 'tags'])->name('tags.index');

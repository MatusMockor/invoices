<?php

declare(strict_types=1);

use App\Modules\CRM\Controllers\Api\ContactController;
use App\Modules\CRM\Controllers\CrmContactController;
use Illuminate\Support\Facades\Route;

Route::prefix('crm')->group(function () {
    // Contacts - only index for web view
    Route::get('contacts', [CrmContactController::class, 'index'])->name('contacts.index');

    // API endpoints for CRUD operations
    Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::put('contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');
});

<?php

declare(strict_types=1);

use App\Http\Controllers\InvoicePdfPreviewController;
use Illuminate\Support\Facades\Route;

// Invoice PDF preview for Browsershot (token auth, no session required)
Route::get('/invoices/{invoice}/pdf-preview', [InvoicePdfPreviewController::class, 'show'])
    ->name('invoices.pdf-preview')
    ->withoutMiddleware(['web'])
    ->middleware(\Illuminate\Routing\Middleware\SubstituteBindings::class);

// SPA catch-all route - must be at the end to catch all non-API routes
Route::get('/{any}', function () {
    return view('layouts.app');
})->where('any', '^(?!api).*$');

require __DIR__.'/auth.php';

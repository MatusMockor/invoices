<?php

declare(strict_types=1);

use App\Http\Controllers\OAuth\AuthorizationController;
use App\Http\Controllers\PublicInvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Invoice Preview Routes
|--------------------------------------------------------------------------
|
| Public route for viewing invoice HTML preview via signed URL.
| Used by ChatGPT MCP integration to display invoices.
|
*/
Route::get('/invoices/{invoice}/preview', [PublicInvoiceController::class, 'preview'])
    ->name('invoices.preview')
    ->middleware('signed');

/*
|--------------------------------------------------------------------------
| OAuth Authorization Routes
|--------------------------------------------------------------------------
|
| Custom OAuth authorization routes that override Passport's default
| authorization endpoints. These provide a custom consent screen with
| Slovak translations and auto-approve for pre-authorized clients.
|
*/
Route::middleware(['web'])->prefix('oauth')->group(static function (): void {
    // OAuth-specific login (Blade-based, not SPA)
    Route::get('/login', static fn () => view('auth.login'))
        ->name('oauth.login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])
        ->name('oauth.login.store');

    // Authorization form - handles auth check internally to preserve query params
    Route::get('/authorize', [AuthorizationController::class, 'showAuthorizationForm'])
        ->name('oauth.authorize');
    // Approve/Deny require auth
    Route::post('/authorize', [AuthorizationController::class, 'approve'])
        ->middleware('auth')
        ->name('oauth.authorize.approve');
    Route::delete('/authorize', [AuthorizationController::class, 'deny'])
        ->middleware('auth')
        ->name('oauth.authorize.deny');
});

// SPA catch-all route - must be at the end to catch all non-API routes
Route::get('/{any}', static function () {
    return view('layouts.app');
})->where('any', '^(?!api|oauth).*$');

require __DIR__.'/auth.php';

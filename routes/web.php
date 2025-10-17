<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// SPA catch-all route - must be at the end to catch all non-API routes
Route::get('/{any}', function () {
    return view('layouts.app');
})->where('any', '^(?!api).*$');

require __DIR__.'/auth.php';

<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureCompanySelectedMiddleware;
use App\Modules\VehicleLogbook\Controllers\TripController;
use App\Modules\VehicleLogbook\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureCompanySelectedMiddleware::class])->group(function () {
    Route::resource('vehicles', VehicleController::class);
    Route::resource('trips', TripController::class);
});

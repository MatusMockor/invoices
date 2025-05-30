<?php

use App\Http\Middleware\EnsureCompanySelected;
use App\Modules\VehicleLogbook\Controllers\TripController;
use App\Modules\VehicleLogbook\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Vehicle Logbook routes
    Route::resource('vehicles', VehicleController::class)->middleware(EnsureCompanySelected::class);
    Route::resource('trips', TripController::class)->middleware(EnsureCompanySelected::class);
});

<?php

use App\Http\Middleware\EnsureCompanySelected;
use App\Modules\VehicleLogbook\Controllers\TripController;
use App\Modules\VehicleLogbook\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureCompanySelected::class])->group(function () {
    Route::resource('vehicles', VehicleController::class);
    Route::resource('trips', TripController::class);
});

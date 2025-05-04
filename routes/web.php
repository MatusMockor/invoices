<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Invoice routes
    Route::resource('invoices', InvoiceController::class);

    // Company routes
    Route::resource('companies', CompanyController::class);
    Route::post('/companies/fetch-by-ico', [CompanyController::class, 'fetchByIco'])->name('companies.fetch-by-ico');
    Route::resource('partners', PartnerController::class);
    Route::post('/partners/fetch-by-ico', [PartnerController::class, 'fetchByIco'])->name('companies.fetch-by-ico');
});

require __DIR__.'/auth.php';

<?php

use App\Http\Controllers\BusinessEntityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureCompanySelected;
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
    Route::resource('invoices', InvoiceController::class)->middleware(EnsureCompanySelected::class);
    Route::get('/invoices/{invoice}/pdf/download', [InvoiceController::class, 'downloadPdf'])
        ->middleware(EnsureCompanySelected::class)
        ->name('invoices.pdf.download');
    Route::get('/invoices/{invoice}/pdf/view', [InvoiceController::class, 'viewPdf'])
        ->middleware(EnsureCompanySelected::class)
        ->name('invoices.pdf.view');

    // Company routes
    Route::resource('companies', CompanyController::class);
    Route::post('/companies/{company}/switch', [CompanyController::class, 'switchCompany'])->name('companies.switch');

    Route::resource('business-entities', BusinessEntityController::class);
    Route::get('/business-entities-fetch-by-ico', [BusinessEntityController::class, 'fetchByIco'])->name('business-entities.fetch-by-ico');
});

require __DIR__.'/auth.php';

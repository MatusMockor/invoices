<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessEntityController;
use App\Http\Controllers\Api\CompanyAnalyticsApiController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ReportsApiController;
use App\Http\Controllers\Api\UserCompanyController;
use App\Http\Controllers\Api\UserSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (public) - stateless, no session
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:6,1')
    ->name('api.register');
Route::post('/register-with-company', [AuthController::class, 'registerWithCompany'])
    ->middleware('throttle:6,1')
    ->name('api.register-with-company');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('api.login');
Route::post('/clear-cookies', [AuthController::class, 'clearCookies'])
    ->name('api.clear-cookies');

// Protected routes (require authentication via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', [AuthController::class, 'user'])->name('api.user');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Onboarding (doesn't require company)
    Route::get('/onboarding/check', [OnboardingController::class, 'check'])->name('api.onboarding.check');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('api.onboarding.store');

    // Companies
    Route::get('/user/companies/minimal', [UserCompanyController::class, 'minimal'])->name('api.companies.minimal');
    Route::get('/companies', [UserCompanyController::class, 'index'])->name('api.companies.index');
    Route::get('/companies/{userCompany}', [UserCompanyController::class, 'show'])->name('api.companies.show');
    Route::post('/companies', [UserCompanyController::class, 'store'])->name('api.companies.store');
    Route::put('/companies/{userCompany}', [UserCompanyController::class, 'update'])->name('api.companies.update');
    Route::delete('/companies/{userCompany}', [UserCompanyController::class, 'destroy'])->name('api.companies.destroy');
    Route::post('/companies/{userCompany}/switch', [UserCompanyController::class, 'switch'])->name('api.companies.switch');

    // Business Entities
    Route::get('/business-entities', [BusinessEntityController::class, 'index'])->name('api.business-entities.index');
    Route::get('/business-entities/{businessEntity}', [BusinessEntityController::class, 'show'])->name('api.business-entities.show');
    Route::post('/business-entities', [BusinessEntityController::class, 'store'])->name('api.business-entities.store');
    Route::put('/business-entities/{businessEntity}', [BusinessEntityController::class, 'update'])->name('api.business-entities.update');
    Route::delete('/business-entities/{businessEntity}', [BusinessEntityController::class, 'destroy'])->name('api.business-entities.destroy');
    Route::get('/business-entities-fetch-by-ico', [BusinessEntityController::class, 'fetchByIco'])->name('api.business-entities.fetch-by-ico');

    // Customer Companies (for invoice autocomplete)
    Route::get('/customer-companies/search', [CompanyController::class, 'search'])->name('api.customer-companies.search');
    Route::get('/customer-companies', [CompanyController::class, 'index'])->name('api.customer-companies.index');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('api.invoices.index');
    Route::get('/invoices/latest-number', [InvoiceController::class, 'latestNumber'])->name('api.invoices.latest-number');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('api.invoices.show');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('api.invoices.store');
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('api.invoices.update');
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('api.invoices.update-status');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('api.invoices.destroy');
    Route::get('/invoices/{invoice}/pdf/download', [InvoiceController::class, 'downloadPdf'])->name('api.invoices.pdf.download');
    Route::get('/invoices/{invoice}/pdf/view', [InvoiceController::class, 'viewPdf'])->name('api.invoices.pdf.view');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('api.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('api.profile.update');
    Route::patch('/user/password', [ProfileController::class, 'updatePassword'])->name('api.user.password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('api.profile.destroy');

    // Contacts
    Route::get('/contacts', [ContactController::class, 'index'])->name('api.contacts.index');
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('api.contacts.show');
    Route::post('/contacts', [ContactController::class, 'store'])->name('api.contacts.store');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('api.contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('api.contacts.destroy');

    // Notes
    Route::get('/notes', [NoteController::class, 'index'])->name('api.notes.index');
    Route::post('/notes', [NoteController::class, 'store'])->name('api.notes.store');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('api.notes.destroy');

    // User Settings
    Route::get('/settings', [UserSettingController::class, 'show'])->name('api.settings.show');
    Route::put('/settings', [UserSettingController::class, 'update'])->name('api.settings.update');

    // Analytics
    Route::get('/analytics', [CompanyAnalyticsApiController::class, 'index'])->name('api.analytics.index');

    // Reports
    Route::get('/reports', [ReportsApiController::class, 'index'])->name('api.reports.index');
});

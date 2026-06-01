<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES
|--------------------------------------------------------------------------
*/

// ======================================
// LANDING PAGE
// ======================================
Route::get('/', function () {
    return view('welcome');
});


// ======================================
// OVERVIEW DASHBOARD (GLOBAL)
// ======================================
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/overview', function () {
        return view('dashboard');
    })->name('overview');

});


// ======================================
// STAFF ROUTES
// ======================================
Route::middleware(['auth', 'role:staff'])->group(function () {

    // STAFF DASHBOARD
    Route::get('/staff',
    [DocumentController::class, 'staffDashboard']
    )->name('staff.dashboard');

    // DOCUMENTS
    Route::resource('documents', DocumentController::class)->only([
        'index',
        'create',
        'store'
    ]);

    // DOWNLOAD DOCUMENT
    Route::get('/documents/{id}/download',
        [DocumentController::class, 'download']
    )->name('documents.download');

});


// ======================================
// ADMIN ROUTES
// ======================================
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

    // DASHBOARD ADMIN
    Route::get('/',
        [DocumentController::class, 'adminDashboard']
    )->name('admin.dashboard');

    // VERIFIKASI DOKUMEN
    Route::get('/documents',
        [DocumentController::class, 'adminIndex']
    )->name('admin.documents');

    // APPROVE
    Route::get('/documents/{id}/approve',
        [DocumentController::class, 'approve']
    )->name('documents.approve');

    // REJECT
    Route::get('/documents/{id}/reject',
        [DocumentController::class, 'reject']
    )->name('documents.reject');

    // MONITORING
    Route::get('/monitoring',
        [DocumentController::class, 'monitoring']
    )->name('admin.monitoring');

});


// ======================================
// PROFILE ROUTES
// ======================================
Route::middleware('auth')->group(function () {

    Route::get('/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch('/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete('/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

});


// ======================================
// AUTH ROUTES
// ======================================
require __DIR__.'/auth.php';
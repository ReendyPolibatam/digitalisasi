<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| LANDING PAGE
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    if (auth()->check()) {

        if (auth()->user()->role == 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('staff.dashboard');
    }

    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| STAFF
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:staff'])->group(function () {

    // Dashboard
    Route::get(
        '/staff',
        [DocumentController::class, 'staffDashboard']
    )->name('staff.dashboard');

    // Upload & Dokumen Saya
    Route::resource(
        'documents',
        DocumentController::class
    )->only([
        'index',
        'create',
        'store'
    ]);

    // Download
    Route::get(
        '/documents/{id}/download',
        [DocumentController::class, 'download'
    ])->name('documents.download');

});


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        // Dashboard
        Route::get(
            '/',
            [DocumentController::class, 'adminDashboard']
        )->name('admin.dashboard');

        // Verifikasi Dokumen
        Route::get(
            '/documents',
            [DocumentController::class, 'adminIndex']
        )->name('admin.documents');

        // Library Dokumen
        Route::get(
            '/library',
            [DocumentController::class, 'library']
        )->name('admin.library');

        // Approve
        Route::get(
            '/documents/{id}/approve',
            [DocumentController::class, 'approve']
        )->name('documents.approve');

        // Reject
        Route::get(
            '/documents/{id}/reject',
            [DocumentController::class, 'reject']
        )->name('documents.reject');

        // Monitoring
        Route::get(
            '/monitoring',
            [DocumentController::class, 'monitoring']
        )->name('admin.monitoring');

});


/*
|--------------------------------------------------------------------------
| PROFILE
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
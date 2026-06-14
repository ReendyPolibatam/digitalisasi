<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;

// Landing page
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('staff.dashboard');
    }

    return view('welcome');
});

// Staff routes
Route::middleware(['auth', 'role:staff'])->group(function () {

    Route::get('/staff', [DocumentController::class, 'staffDashboard'])
        ->name('staff.dashboard');

    Route::resource('documents', DocumentController::class)->only([
        'index',
        'create',
        'store',
    ]);

    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])
        ->name('documents.download');

    Route::post('/documents/{id}/ocr', [DocumentController::class, 'processOCR'])
        ->name('documents.ocr');

    Route::post('/documents/{id}/submit', [DocumentController::class, 'submit'])
        ->name('documents.submit');

    Route::get('/documents/{id}', [DocumentController::class, 'show'])
        ->name('documents.show');

});

// Admin routes
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function () {

        Route::get('/', [DocumentController::class, 'adminDashboard'])
            ->name('admin.dashboard');

        Route::get('/documents', [DocumentController::class, 'adminIndex'])
            ->name('admin.documents');

        Route::get('/documents/{id}', [DocumentController::class, 'showDocument'])
            ->name('admin.documents.show');

        Route::get('/documents/{id}/approve', [DocumentController::class, 'approve'])
            ->name('documents.approve');

        Route::get('/documents/{id}/reject', [DocumentController::class, 'reject'])
            ->name('documents.reject');

        Route::get('/library', [DocumentController::class, 'library'])
            ->name('admin.library');

        Route::get('/monitoring', [DocumentController::class, 'monitoring'])
            ->name('admin.monitoring');
    });

// Profile
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;

// Landing page: belum login -> login, sudah login -> dashboard sesuai role
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(
            Auth::user()->role === 'admin' ? 'admin.dashboard' : 'staff.dashboard'
        );
    }

    return redirect()->route('login');
});

// Route 'dashboard' bawaan Breeze (dipakai setelah login/verifikasi email)
Route::get('/dashboard', function () {
    return redirect()->route(
        Auth::user()->role === 'admin' ? 'admin.dashboard' : 'staff.dashboard'
    );
})->middleware('auth')->name('dashboard');

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
        ->whereNumber('id')
        ->name('documents.download');

    Route::post('/documents/{id}/ocr', [DocumentController::class, 'processOCR'])
        ->whereNumber('id')
        ->name('documents.ocr');

    Route::post('/documents/{id}/submit', [DocumentController::class, 'submit'])
        ->whereNumber('id')
        ->name('documents.submit');

    Route::get('/documents/{id}', [DocumentController::class, 'show'])
        ->whereNumber('id')
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
            ->whereNumber('id')
            ->name('admin.documents.show');

        // Aksi yang mengubah data harus POST, bukan GET
        Route::post('/documents/{id}/approve', [DocumentController::class, 'approve'])
            ->whereNumber('id')
            ->name('documents.approve');

        Route::post('/documents/{id}/reject', [DocumentController::class, 'reject'])
            ->whereNumber('id')
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
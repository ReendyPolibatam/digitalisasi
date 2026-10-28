<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

    // Simpan hasil edit teks OCR (draft, belum disubmit)
    Route::post('/documents/{id}/ocr-text', [DocumentController::class, 'updateOcrText'])
        ->whereNumber('id')
        ->name('documents.ocr.update');

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

        // Library: kapal -> voyage -> klasifikasi
        Route::get('/library', [DocumentController::class, 'library'])
            ->name('admin.library');

        Route::get('/library/{vessel}', [DocumentController::class, 'libraryVessel'])
            ->where('vessel', '[A-Z0-9]+')
            ->name('admin.library.vessel');

        Route::get('/library/{vessel}/{voyage}', [DocumentController::class, 'libraryVoyage'])
            ->where(['vessel' => '[A-Z0-9]+', 'voyage' => '[A-Z0-9]+'])
            ->name('admin.library.voyage');

        Route::get('/monitoring', [DocumentController::class, 'monitoring'])
            ->name('admin.monitoring');

        // UC-03: Kelola pengguna
        Route::resource('users', UserController::class)
            ->except('show')
            ->names('admin.users');
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

require __DIR__.'/auth.php';

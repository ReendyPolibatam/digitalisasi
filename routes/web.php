<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

// Halaman awal
Route::get('/', function () {
    return view('welcome');
});

// Dashboard default
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// 🔥 STAFF (Upload Dokumen)
Route::middleware(['auth', 'role:staff'])->group(function () {

    Route::get('/staff', function () {
        return view('staff.dashboard');
    })->name('staff.dashboard');

    Route::resource('documents', DocumentController::class)->only([
        'index', 'create', 'store'
    ]);

    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])
        ->name('documents.download');
});


// 🔥 ADMIN (Approver)
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // 🔥 LIHAT SEMUA DOKUMEN
    Route::get('/admin/documents', [DocumentController::class, 'adminIndex'])
        ->name('admin.documents');

    // 🔥 APPROVE / REJECT
    Route::get('/documents/{id}/approve', [DocumentController::class, 'approve'])
        ->name('documents.approve');

    Route::get('/documents/{id}/reject', [DocumentController::class, 'reject'])
        ->name('documents.reject');
});


// 🔥 PROFILE
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

// Documen Controller
Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff', fn() => view('staff.dashboard'))->name('staff.dashboard');

    Route::resource('documents', DocumentController::class)->only([
        'index', 'create', 'store'
    ]);
});


// Halaman awal
Route::get('/', function () {
    return view('welcome');
});

// Dashboard default (sementara)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// 🔥 ROUTE BERDASARKAN ROLE

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin', function () {
        return "Halaman Admin (Approver)";
    })->name('admin.dashboard');
});

Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff', function () {
        return "Halaman Staff (Upload Dokumen)";
    })->name('staff.dashboard');
});

// Profile (bawaan Laravel)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

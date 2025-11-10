<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// 1. PUBLIC ROUTES (Akses Pengguna Umum)
// ========================================================================

// Homepage / Galeri Utama (READ Gambar)
Route::get('/', [ImageController::class, 'index'])->name('gallery.index');


// ========================================================================
// 2. AUTHENTICATION (Login & Register)
// ========================================================================

// Group khusus untuk pengguna yang belum login (guest)
Route::middleware('guest')->group(function () {
    // Register
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Login
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});


// ========================================================================
// 3. PROTECTED ROUTES (Hanya untuk User yang Sudah Login)
// ========================================================================

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // CRUD GAMBAR (CREATE, EDIT, UPDATE, DELETE)
    Route::get('images', [ImageController::class, 'index'])->name('gallery.index');
    // --------------------------------------------------------------------

    // CREATE GAMBAR
    Route::get('images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('images', [ImageController::class, 'store'])->name('images.store');

    // EDIT & UPDATE GAMBAR
    Route::get('images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit');
    Route::patch('images/{id}', [ImageController::class, 'update'])->name('images.update');

    // DELETE GAMBAR
    Route::delete('images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');
});

// ========================================================================
// 4. ROUTE PUBLIC TERAKHIR (SHOW GAMBAR DETAIL) – letakkan PALING BAWAH
// ========================================================================

Route::get('images/{id}', [ImageController::class, 'show'])->name('images.show');

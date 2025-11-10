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
// 1. HOMEPAGE (Landing Page Artrium)
// ========================================================================
Route::get('/', function () {
    return view('home'); // tampilkan resources/views/home.blade.php
})->name('home');

// ========================================================================
// 2. PUBLIC ROUTES (Akses Pengguna Umum)
// ========================================================================

// Galeri publik (data dari Supabase)
Route::get('/gallery', [ImageController::class, 'index'])->name('gallery.index');

// Detail gambar
Route::get('images/{id}', [ImageController::class, 'show'])->name('images.show');


// ========================================================================
// 2. AUTHENTICATION (Login & Register)
// ========================================================================

Route::middleware('guest')->group(function () {
    // REGISTER
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // LOGIN
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// LOGOUT (user sudah login)
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// ========================================================================
// 4. PROTECTED ROUTES (Hanya untuk User yang Sudah Login)
// ========================================================================

Route::middleware('auth')->group(function () {

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // CREATE GAMBAR
    Route::get('images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('images', [ImageController::class, 'store'])->name('images.store');

    // EDIT & UPDATE GAMBAR
    Route::get('images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit');
    Route::patch('images/{id}', [ImageController::class, 'update'])->name('images.update');

    // DELETE GAMBAR
    Route::delete('images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');
});

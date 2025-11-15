<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommentController; // Menggunakan CommentController

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// 1. HOMEPAGE (Landing Page Artrium)
// ========================================================================
Route::get('/', function () {
    return view('home');
})->name('home');

// ========================================================================
// 2. AUTHENTICATION (Login, Register & Logout)
// ========================================================================

Route::middleware('guest')->group(function () {
    // REGISTER
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    // LOGIN
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// LOGOUT
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');


// ========================================================================
// 3. PROTECTED ROUTES (Hanya untuk User yang Sudah Login - CRUD & Profile)
// ========================================================================

Route::middleware('auth')->group(function () {

    // Rute Profile Saya
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.show');

    // CRUD GAMBAR
    // CREATE GAMBAR
    Route::get('images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('images', [ImageController::class, 'store'])->name('images.store');

    // EDIT & UPDATE GAMBAR
    // Rute Edit, Update, dan Delete MENGGUNAKAN {id} agar konsisten dengan Controller
    Route::get('images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit');
    Route::patch('images/{id}', [ImageController::class, 'update'])->name('images.update');

    // DELETE GAMBAR
    Route::delete('images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');

    // RUTE KOMENTAR
    // [C] STORE Komentar (Menggunakan {image} untuk ID gambar)
    Route::post('images/{image}/comments', [CommentController::class, 'store'])
        ->name('comments.store');

    // [D] DELETE Komentar (Menggunakan {id} untuk ID komentar, jika itu yang digunakan di controller)
    Route::delete('comments/{id}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    // RUTE LIKE
    Route::post('images/{imageId}/like', [LikeController::class, 'toggle'])->name('likes.toggle');
    Route::get('images/{imageId}/like/check', [LikeController::class, 'check'])->name('likes.check');
});


// ========================================================================
// 4. PUBLIC ROUTES (Akses Pengguna Umum)
// ========================================================================

// Galeri publik (Explore)
Route::get('/explore', [ImageController::class, 'index'])->name('gallery.index');

// Detail gambar (Rute Dinamis)
Route::get('images/{id}', [ImageController::class, 'show'])->name('images.show');

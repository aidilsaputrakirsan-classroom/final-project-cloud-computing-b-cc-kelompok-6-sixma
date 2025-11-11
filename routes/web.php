<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
<<<<<<< HEAD
use App\Http\Controllers\ProfileController;
=======
use App\Http\Controllers\ProfileController; 
>>>>>>> feature/comment
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommentController; // 🎯 BARU

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
// 3. PROTECTED ROUTES (Hanya untuk User Login)
// ========================================================================
Route::middleware('auth')->group(function () {
<<<<<<< HEAD
    // PROFIL SAYA
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.show'); 

=======

    // Rute Profile Saya
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.show'); 
    
>>>>>>> feature/comment
    // CRUD GAMBAR
    Route::get('images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('images', [ImageController::class, 'store'])->name('images.store');
    Route::get('images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit');
    Route::patch('images/{id}', [ImageController::class, 'update'])->name('images.update');
    Route::delete('images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');
    
    // =======================================================
    // RUTE BARU: KOMENTAR
    // =======================================================
    Route::post('images/{image}/comments', [CommentController::class, 'store'])
        ->name('comments.store');
        
    Route::delete('comments/{id}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');
});

// ========================================================================
// 4. PUBLIC ROUTES (Akses Umum)
// ========================================================================
Route::get('/explore', [ImageController::class, 'index'])->name('gallery.index');
<<<<<<< HEAD
Route::get('images/{id}', [ImageController::class, 'show'])->name('images.show');
=======

// Detail gambar (Rute Dinamis)
Route::get('images/{id}', [ImageController::class, 'show'])->name('images.show');
>>>>>>> feature/comment

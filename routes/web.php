<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LikeController; // KRITIS: Pastikan LikeController terimport

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
// 3. PROTECTED ROUTES with Middleware (Hanya untuk User yang Sudah Login - CRUD & Profile)
// ========================================================================

Route::middleware(['auth', 'role:user'])->group(function () {

    // Rute Profile Saya
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.show');

    // CRUD GAMBAR
    Route::get('images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('images', [ImageController::class, 'store'])->name('images.store');

    Route::get('images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit');
    Route::patch('images/{id}', [ImageController::class, 'update'])->name('images.update');
    Route::delete('images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');

    // RUTE KOMENTAR
    Route::post('images/{image}/comments', [CommentController::class, 'store'])
        ->name('comments.store');
    Route::delete('comments/{id}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    // RUTE PELAPORAN (REPORT)
    Route::post('images/{image}/report', [ReportController::class, 'store'])
        ->name('reports.store');

    // RUTE LIKES 
    Route::post('images/{image}/like', [LikeController::class, 'toggle'])
        ->name('likes.toggle'); // <<< Rute target yang error 404
});

// ========================================================================
// 3. PROTECTED ROUTES (Hanya untuk User yang Sudah Login - CRUD & Profile)
// ========================================================================

// Route::middleware('auth')->group(function () {

//     // Rute Profile Saya
//     Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.show');

//     // CRUD GAMBAR
//     Route::get('images/create', [ImageController::class, 'create'])->name('images.create');
//     Route::post('images', [ImageController::class, 'store'])->name('images.store');

//     Route::get('images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit');
//     Route::patch('images/{id}', [ImageController::class, 'update'])->name('images.update');
//     Route::delete('images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');

//     // RUTE KOMENTAR
//     Route::post('images/{image}/comments', [CommentController::class, 'store'])
//         ->name('comments.store');
//     Route::delete('comments/{id}', [CommentController::class, 'destroy'])
//         ->name('comments.destroy');

//     // RUTE PELAPORAN (REPORT)
//     Route::post('images/{image}/report', [ReportController::class, 'store'])
//         ->name('reports.store');

//     // RUTE LIKES 
//     Route::post('images/{image}/like', [LikeController::class, 'toggle'])
//         ->name('likes.toggle'); // <<< Rute target yang error 404
// });


// ========================================================================
// 4. PUBLIC ROUTES (Akses Pengguna Umum)
// ========================================================================

// Galeri publik (Explore)
Route::get('/explore', [ImageController::class, 'index'])->name('gallery.index');

// Detail gambar (Rute Dinamis)
Route::get('images/{id}', [ImageController::class, 'show'])->name('images.show');

// Notif 
Route::middleware('auth')->get(
    '/notifications',
    [NotificationController::class, 'index']
)->name('notifications');
Route::middleware('auth')
    ->post('/notifications/read', [NotificationController::class, 'markAllRead'])
    ->name('notifications.read');


Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard'); // sesuaikan view nya
    })->name('admin.dashboard');
});
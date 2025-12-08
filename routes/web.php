<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LikeController; 

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

// LOGOUT (POST request)
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');


// ========================================================================
// 3. ADMIN ROUTES (Khusus Role Admin) 🔥 PRIORITAS TERTINGGI
// ========================================================================


Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // 🔄 Clear cache dashboard (untuk tombol Refresh Data)
        Route::post('/dashboard/clear-cache', [AdminDashboardController::class, 'clearCache'])
            ->name('dashboard.clear-cache');

        // Detail Post Admin
        Route::get('/post/{post}', [ImageController::class, 'adminShow'])
            ->name('post.show');
    });


// ========================================================================
// 4. USER ROUTES (Khusus Role User)
// ========================================================================

Route::middleware(['auth', 'role:user'])->group(function () {

    // Profile User
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.show');

    // CRUD GAMBAR (Hanya pemilik yang bisa)
    Route::get('images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('images', [ImageController::class, 'store'])->name('images.store');
    Route::get('images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit');
    Route::patch('images/{id}', [ImageController::class, 'update'])->name('images.update');
    Route::delete('images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');

    // KOMENTAR
    Route::post('images/{image}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // PELAPORAN
    Route::post('images/{image}/report', [ReportController::class, 'store'])->name('reports.store');

    // LIKES 
    Route::post('images/{image}/like', [LikeController::class, 'toggle'])->name('likes.toggle');

    // NOTIFIKASI
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
});


// ========================================================================
// 5. PUBLIC ROUTES (Akses Pengguna Umum)
// ========================================================================

// Galeri publik (Explore) - Bisa diakses semua orang (guest & auth)
Route::get('/explore', [ImageController::class, 'index'])->name('gallery.index');

// Detail gambar - Bisa diakses semua orang
Route::get('images/{id}', [ImageController::class, 'show'])->name('images.show');
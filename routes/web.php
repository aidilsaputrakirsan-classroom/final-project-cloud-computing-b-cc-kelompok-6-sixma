<?php

// Pastikan baris-baris ini ada di bagian atas file routes/web.php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route; // Pastikan ini juga ada

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Baris lain dari routes Anda ada di sini...
|
*/

// ===================================================
// INI ADALAH ROUTES UNTUK AUTENTIKASI (LOGIN & REGISTER)
// ===================================================

// Route untuk menampilkan Form Register
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
// Route untuk memproses data Register
Route::post('/register', [RegisteredUserController::class, 'store']);

// Route untuk menampilkan Form Login
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
// Route untuk memproses data Login
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Route untuk proses Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

// Route Home (Contoh)
Route::get('/', function () {
    return view('welcome'); // atau view home/galeri Anda
});
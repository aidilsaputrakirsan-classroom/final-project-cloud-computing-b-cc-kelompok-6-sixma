<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReadController; // ✅ Tambahkan ReadController

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ini adalah rute utama project Laravel kamu.
| Digabung dengan fitur Upload Gambar, Galeri, dan Read (CRUD Read).
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 🖼️ Fitur Upload Gambar
Route::get('/images/create', [ImageController::class, 'create'])->name('images.create');
Route::post('/images', [ImageController::class, 'store'])->name('images.store');

// 🖼️ Halaman Galeri
Route::get('/gallery', [ImageController::class, 'index'])->name('gallery.index');

// 📖 Fitur Read (Menampilkan data dari database)
Route::get('/read', [ReadController::class, 'index'])->name('read.index');       // Menampilkan semua data
Route::get('/read/{id}', [ReadController::class, 'show'])->name('read.show');    // Menampilkan data spesifik by ID

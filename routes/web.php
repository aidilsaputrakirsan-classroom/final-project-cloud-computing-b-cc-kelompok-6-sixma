<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CategoryController; 
// Autentikasi sudah di merge ke development, jadi routesnya ada di routes/web.php
// Jika belum merge, tambahkan routes Login/Register di sini

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman utama (Galeri Publik)
Route::get('/', [ImageController::class, 'index'])->name('gallery.index'); 

// 📖 READ DETAIL GAMBAR (Publik)
Route::get('/images/{id}', [ImageController::class, 'show'])->name('images.show'); 


// ========================================================
// 🖼️ ROUTES CRUD GAMBAR (Hanya Bisa Diakses Setelah Login)
// ========================================================
Route::middleware(['auth'])->group(function () {
    
    // CREATE GAMBAR
    Route::get('/images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('/images', [ImageController::class, 'store'])->name('images.store');
    
    // UPDATE GAMBAR
    Route::get('/images/{id}/edit', [ImageController::class, 'edit'])->name('images.edit'); 
    Route::patch('/images/{id}', [ImageController::class, 'update'])->name('images.update'); // PATCH untuk Update
    
    // DELETE GAMBAR
    Route::delete('/images/{id}', [ImageController::class, 'destroy'])->name('images.destroy');
});
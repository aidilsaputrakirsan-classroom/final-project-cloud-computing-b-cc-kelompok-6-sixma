<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CategoryController; 
// ❌ Hapus: use App\Http\Controllers\ReadController; 
// ✅ Tambahkan: use App\Http\Controllers\Auth\AuthenticatedSessionController; (jika belum merge)

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ImageController::class, 'index'])->name('gallery.index'); // ✅ Gunakan Galeri sebagai Homepage

// ========================================================
// 🖼️ ROUTES CRUD GAMBAR (Satu Entitas)
// Gunakan resource routing atau penamaan konsisten
// ========================================================

Route::middleware(['auth'])->group(function () {
    // CREATE GAMBAR
    Route::get('/images/create', [ImageController::class, 'create'])->name('images.create');
    Route::post('/images', [ImageController::class, 'store'])->name('images.store');
    
    // UPDATE GAMBAR (Anda) - Gunakan Route Model Binding
    // {image} adalah ID gambar yang dikirim ke controller
    Route::get('/images/{image}/edit', [ImageController::class, 'edit'])->name('images.edit'); 
    Route::patch('/images/{image}', [ImageController::class, 'update'])->name('images.update'); 
    
    // DELETE GAMBAR (Daffa)
    Route::delete('/images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
});


// 📖 READ GAMBAR (Publik)
// Karena Read hanya menampilkan, tidak perlu middleware 'auth'
Route::get('/images', [ImageController::class, 'index'])->name('images.index'); 
Route::get('/images/{id}', [ImageController::class, 'show'])->name('images.show'); 
// ❌ Hapus routes lama: /read dan /read/{id}

// --------------------------------------------------------
// Jika sudah ada Routes Login/Register, biarkan di sini
// --------------------------------------------------------
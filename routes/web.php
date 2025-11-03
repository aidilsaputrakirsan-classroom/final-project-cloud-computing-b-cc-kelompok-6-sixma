<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CategoryController;


Route::get('/', function () {
    return view('welcome');
});

// 🖼️ Fitur Upload Gambar
Route::get('/images/create', [ImageController::class, 'create'])->name('images.create');
Route::post('/images', [ImageController::class, 'store'])->name('images.store');

// 🖼️ Halaman Galeri
Route::get('/gallery', [ImageController::class, 'index'])->name('gallery.index');



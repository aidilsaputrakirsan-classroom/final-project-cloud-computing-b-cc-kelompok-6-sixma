<?php

namespace App\Http\Controllers;

use App\Models\Image; // Wajib: Untuk model database
use App\Http\Requests\UpdateImageRequest; // Wajib: Untuk validasi dan otorisasi
use Illuminate\Http\Request;

class ImageController extends Controller
{
    // ... method lain (index, create, store, edit, show, destroy)
    
    public function update(UpdateImageRequest $request, Image $image)
    {
        // Catatan: Jika ada logic file upload, ia akan ditambahkan di sini.
        
        $image->update($request->validated());

        return redirect()->back()->with('success', 'Karya berhasil diperbarui.');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use App\Http\Requests\UpdateImageRequest; // Wajib Anda buat

class ImageController extends Controller
{
    // ==========================================================
    // 🟢 READ GAMBAR & FILTER/SEARCH (DAFFA & KIRANA)
    // ==========================================================
    
    // 🟢 Menampilkan semua gambar (Galeri Utama)
    public function index(Request $request)
    {
        // 1. Setup Query Dasar Supabase (Initial Logic READ oleh Daffa)
        // Mengambil data gambar, sekaligus join dengan nama user (penulis)
        $queryUrl = env('SUPABASE_REST_URL') . '/images?select=*,user:user_id(name)&order=created_at.desc';
        $filters = [];
        
        // --- [Tempat Logic FILTER/SEARCH Kirana akan masuk di sini] ---
        // Contoh: if ($request->has('category') && $request->category != '') { $filters[] = 'category_id=eq.' . $request->category; }
        // --- ------------------------------------------------------------- ---
        
        if (!empty($filters)) {
            $queryUrl .= '&' . implode('&', $filters);
        }

        // 2. Ambil data Gambar dari Supabase
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get($queryUrl);

        $images = $response->json() ?? [];
        
        // Ambil kategori untuk filter (Tugas Kirana)
        $categoriesResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');
        
        $categories = $categoriesResponse->json() ?? [];

        // Output WAJIB return VIEW
        return view('images.index', compact('images', 'categories')); 
    }

    // 🟢 Menampilkan detail gambar tunggal (DAFFA)
    public function show($id)
    {
        // Mengambil data gambar tunggal berdasarkan ID, dengan join nama user
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=*,user:user_id(name)'); 
        
        $image = $response->json()[0] ?? null;

        if (!$image) {
            abort(404); // Not Found
        }
        
        return view('images.show', compact('image')); 
    }

    // ==========================================================
    // 🟢 CREATE GAMBAR (KIRANA)
    // ==========================================================

    // 🟢 Menampilkan form unggah gambar
    public function create()
    {
        // Mengambil kategori untuk dropdown dari Supabase
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name';
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get($url);

        $categories = $response->json() ?? [];

        return view('images.create-image', compact('categories'));
    }

    // 🟢 Menyimpan hasil unggahan ke Supabase
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|integer', 
            'location' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        // 2. Upload ke Supabase Storage
        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();

        $upload = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/octet-stream',
        ])->put(
            env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $filename,
            file_get_contents($file)
        );

        if (!$upload->successful()) {
            Log::error('Supabase Upload Error: ' . $upload->body());
            return back()->with('error', 'Gagal mengunggah gambar ke Supabase Storage.');
        }

        // 3. Simpan metadata gambar ke Supabase Database
        $insert = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post(env('SUPABASE_REST_URL') . '/images', [
            'user_id' => Auth::id(), // ID user yang sedang login
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'location' => $request->location,
            'image_path' => $filename,
        ]);

        if (!$insert->successful()) {
            Log::error('Supabase Insert Error: ' . $insert->body());
            return back()->with('error', 'Gagal menyimpan data ke Supabase.');
        }

        return redirect()->route('gallery.index')->with('success', '✨ Gambar berhasil diunggah!');
    }

    // ==========================================================
    // 🟢 UPDATE GAMBAR (RIA)
    // ==========================================================
    
    // 🟢 Menampilkan form edit dengan data lama
    public function edit($id)
    {
        // 1. Ambil data gambar yang akan diedit dari Supabase
        $imageResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=*,user_id');
        
        $image = $imageResponse->json()[0] ?? null;

        if (!$image) {
            abort(404);
        }
        
        // 2. Otorisasi (Wajib): Hanya pemilik karya yang bisa edit
        if (Auth::id() !== $image['user_id']) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengedit karya ini.');
        }

        // 3. Ambil data kategori untuk dropdown
        $categoriesResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');

        $categories = $categoriesResponse->json() ?? [];

        return view('images.edit-image', compact('image', 'categories')); 
    }

    // 🟢 Memproses update data dan file
    public function update(UpdateImageRequest $request, $id)
    {
        // 1. Ambil data gambar lama (untuk cek otorisasi dan path file lama)
        $oldImageResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=user_id,image_path');
        
        $oldImage = $oldImageResponse->json()[0] ?? null;

        // Otorisasi: Re-check (defense in depth)
        if (!$oldImage || Auth::id() !== $oldImage['user_id']) {
            return back()->with('error', 'Anda tidak memiliki izin untuk memperbarui karya ini.');
        }

        $imagePathToUpdate = $oldImage['image_path'];

        // 2. Handle File Replacement (Jika ada file baru diupload)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();

            // A. Upload file baru
            $upload = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/octet-stream',
            ])->put(
                env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $filename,
                file_get_contents($file)
            );

            if (!$upload->successful()) {
                Log::error('Supabase Upload Error: ' . $upload->body());
                return back()->with('error', 'Gagal mengunggah gambar baru ke Supabase Storage.');
            }

            // B. Hapus file lama dari Supabase Storage (Wajib)
            if ($oldImage['image_path']) {
                // Supabase tidak mengembalikan HTTP 204 pada delete, jadi kita abaikan statusnya
                Http::withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                ])->delete(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $oldImage['image_path']);
            }

            // Tetapkan path baru untuk update database
            $imagePathToUpdate = $filename;
        }

        // 3. Update Metadata di Supabase Database
        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'location' => $request->location,
            'image_path' => $imagePathToUpdate, // Bisa path lama atau path baru
        ];

        $updateDb = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
        ])->patch(
            env('SUPABASE_REST_URL') . '/images?id=eq.' . $id, // Query filter berdasarkan ID
            $updateData
        );

        if (!$updateDb->successful()) {
            Log::error('Supabase Update DB Error: ' . $updateDb->body());
            return back()->with('error', 'Gagal memperbarui data di Supabase.');
        }

        return redirect()->route('images.show', $id)->with('success', '✅ Karya berhasil diperbarui!');
    }

    // ==========================================================
    // 🟢 DELETE GAMBAR (DAFFA)
    // ==========================================================
    public function destroy($id)
    {
        // 1. Ambil data gambar lama (untuk cek otorisasi dan path file)
        $imageResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=user_id,image_path');
        
        $image = $imageResponse->json()[0] ?? null;

        // Otorisasi: Hanya pemilik karya yang bisa delete
        if (!$image || Auth::id() !== $image['user_id']) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus karya ini.');
        }

        // 2. Hapus File dari Supabase Storage (Wajib)
        $deleteStorage = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->delete(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $image['image_path']);
        
        // Supabase Storage sering mengembalikan status 204 (No Content)
        if (!$deleteStorage->successful() && $deleteStorage->status() !== 204) {
            Log::error('Supabase Delete Storage Error: ' . $deleteStorage->body());
            return back()->with('error', 'Gagal menghapus file dari Storage.');
        }

        // 3. Hapus Metadata dari Supabase Database (Wajib)
        $deleteDb = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->delete(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id);

        if (!$deleteDb->successful()) {
            Log::error('Supabase Delete DB Error: ' . $deleteDb->body());
             return back()->with('error', 'Gagal menghapus data di database.');
        }

        return redirect()->route('gallery.index')->with('success', '🗑️ Karya berhasil dihapus!');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use App\Http\Requests\UpdateImageRequest;
use App\Models\Image;
use App\Models\Category;


class ImageController extends Controller
{
    // ==========================================================
    // 🟢 READ GAMBAR & FILTER/SEARCH
    // ==========================================================
    
    public function index(Request $request)
    {
        try {
            $query = env('SUPABASE_REST_URL') . '/images?select=*';
            
            // Logika Search (Judul)
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query .= '&title=ilike.%25' . $search . '%25';
            }
            
            // Logika Filter (Kategori)
            if ($request->has('category') && !empty($request->category)) {
                $category = $request->category;
                $query .= '&category_id=eq.' . $category; // Menggunakan category_id
            }

            // Tambahkan order by (baru ditambahkan agar Explore menampilkan yang terbaru di atas)
            $query .= '&order=created_at.desc';

            Log::info('📡 Supabase Query:', ['query' => $query]);

            $imagesResponse = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
            ])->get($query);

            $images = [];
            
            if ($imagesResponse->successful()) {
                $imagesData = $imagesResponse->json();
                
                if (is_array($imagesData) && count($imagesData) > 0) {
                    $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';
                    
                    foreach ($imagesData as $image) {
                        if (is_array($image)) {
                            $imageUrl = $baseStorageUrl . ($image['image_path'] ?? '');
                            $image['image_url'] = $imageUrl;
                            $images[] = $image;
                        }
                    }
                }
            }

            // Ambil data Categories untuk filter dropdown
            $categoriesResponse = Http::withHeaders([
                 'apikey' => env('SUPABASE_ANON_KEY'),
                 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
            ])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');

            $categories = $categoriesResponse->json() ?? [];

            return view('images.index', compact('images', 'categories'));

        } catch (\Exception $e) {
            Log::error('💥 Error in index(): ' . $e->getMessage());
            $images = [];
            $categories = [];
            return view('images.index', compact('images', 'categories'))->with('error', 'Gagal memuat galeri.');
        }
    }
    
    // ... Fungsi show() Anda di sini (Tidak diubah, hanya memastikan ID Gambar tersedia) ...
    public function show($id)
    {
        try {
            $response = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id);

            $imageData = $response->json();

            if (empty($imageData)) {
                abort(404);
            }

            $image = $imageData[0];
            
            $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';
            $image['image_url'] = $baseStorageUrl . $image['image_path'];

            return view('images.show', compact('image')); 
        } catch (\Exception $e) {
            Log::error('Error in show(): ' . $e->getMessage());
            abort(404);
        }
    }


    // ==========================================================
    // 🟢 CREATE GAMBAR
    // ==========================================================
    public function create()
    {
        // Ambil data categories dari Supabase untuk dropdown form
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name';
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'), 
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get($url);
        $categories = $response->json() ?? [];
        
        return view('images.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // 1. Dapatkan User ID (Middleware Auth memastikan user sudah login)
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login untuk mengunggah karya.');
        }
        $userId = Auth::id(); // 🔑 Ambil ID user yang sedang login

        // 2. Validasi Request
        $request->validate([
            'image' => 'required|image|max:2048',
            'title' => 'required|string|max:255',
            'category_id' => 'required|numeric', // Validasi category_id
            'description' => 'nullable|string' // Deskripsi opsional
        ]);

        try {
            $file = $request->file('image');
            $mimeType = $file->getMimeType();
            // Gunakan User ID di filename untuk mempermudah tracking/debugging
            $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', time() . '_' . $userId . '_' . $file->getClientOriginalName());

            // 3. Upload ke Supabase Storage
            $upload = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => $mimeType,
            ])->withBody(file_get_contents($file), $mimeType)
            ->post(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename);

            if (!$upload->successful()) {
                Log::error('Storage Upload Failed:', ['status' => $upload->status(), 'body' => $upload->body()]);
                return back()->with('error', 'Gagal upload ke Supabase Storage. Status: ' . $upload->status());
            }

            // 4. Data untuk tabel images (Database)
            $imageData = [
                'title' => $request->title,
                'description' => $request->description, // Tambahkan deskripsi
                'image_path' => $filename,
                'category_id' => $request->category_id,
                'user_id' => $userId, // 🎯 WAJIB: Ikat gambar ke User ID
                'created_at' => now()->toIso8601String() // Opsional: Tambahkan timestamp
            ];

            // 5. Simpan metadata ke database Supabase
            $databaseUrl = env('SUPABASE_REST_URL') . '/images';
            $createImage = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->post($databaseUrl, $imageData);

            if (!$createImage->successful()) {
                // Jika database gagal, hapus file yang sudah terupload
                Http::withHeaders([ /* ...headers... */ ])->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename);
                
                $errorBody = $createImage->body();
                Log::error('Database Save Failed:', ['error' => $errorBody, 'data_sent' => $imageData]);
                return back()->with('error', 'Gagal menyimpan metadata. Error: ' . $errorBody);
            }

            return redirect()->route('gallery.index')->with('success', '✨ Karya berhasil diunggah!');
            
        } catch (\Exception $e) {
            Log::error('❌ Exception in store():', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat upload: ' . $e->getMessage());
        }
    }


    // ==========================================================
    // 🟢 UPDATE GAMBAR (DENGAN OTORISASI)
    // ==========================================================
    
    public function edit($id)
    {
        // 1. Cek Autentikasi
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }
        $userId = Auth::id();

        try {
            // 2. Panggil API dengan filter ID dan USER ID (Otorisasi)
            $response = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId);

            $imageData = $response->json();

            if (empty($imageData)) {
                // Gambar tidak ditemukan ATAU ditemukan tapi bukan milik user ini
                abort(403, 'Akses ditolak. Anda bukan pemilik karya ini.');
            }

            $image = $imageData[0];

            // 3. Ambil data kategori
            $categoriesResponse = Http::withHeaders([ /* ...headers... */ ])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');
            $categories = $categoriesResponse->json() ?? [];

            return view('images.edit', compact('image', 'categories')); 
        } catch (\Exception $e) {
            Log::error('Error in edit(): ' . $e->getMessage());
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        // 1. Cek Autentikasi dan Otorisasi (Filter User ID)
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }
        $userId = Auth::id();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|numeric',
            'description' => 'nullable|string'
        ]);

        try {
            $updateData = $request->only(['title', 'category_id', 'description']);
            $oldImagePath = null;

            if ($request->hasFile('image')) {
                $file = $request->file('image'); 
                $filename = time() . '_' . $userId . '_' . $file->getClientOriginalName();
                
                // 2. Hapus gambar lama sebelum upload baru (Fetch path lama)
                $oldImageResponse = Http::withHeaders([ /* ...headers... */ ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId . '&select=image_path');
                $oldImageData = $oldImageResponse->json();

                if (!empty($oldImageData) && isset($oldImageData[0]['image_path'])) {
                    $oldImagePath = $oldImageData[0]['image_path'];
                    // Hapus file lama dari storage (Opsional: buat fungsi delete terpisah)
                    Http::withHeaders([ /* ...headers... */ ])->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $oldImagePath);
                }

                // Upload file baru
                $upload = Http::withHeaders([ /* ...headers... */ ])
                    ->post(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename, file_get_contents($file));
                
                if (!$upload->successful()) { 
                    return back()->with('error', 'Gagal mengunggah gambar baru.');
                }
                
                $updateData['image_path'] = $filename;
            }

            // 3. Update DB (pastikan hanya mengupdate milik user ini)
            $updateDb = Http::withHeaders([ /* ...headers... */ ])->patch(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId, 
                $updateData
            );

            if (!$updateDb->successful()) {
                Log::error('Supabase Update Error: ' . $updateDb->body());
                return back()->with('error', 'Gagal memperbarui data.');
            }

            return redirect()->route('images.show', $id)->with('success', '✅ Karya berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error in update(): ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }
    
    // ==========================================================
    // 🟢 DELETE GAMBAR (DENGAN OTORISASI)
    // ==========================================================
    public function destroy($id)
    {
        // 1. Cek Autentikasi
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }
        $userId = Auth::id();

        try {
            // 2. Dapatkan path gambar dan cek otorisasi (hanya milik user ini)
            $imageResponse = Http::withHeaders([ /* ...headers... */ ])->get(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId . '&select=image_path'
            );

            $imageToDelete = $imageResponse->json();

            if (empty($imageToDelete)) {
                abort(403, 'Akses ditolak. Karya tidak ditemukan atau bukan milik Anda.');
            }

            $imagePath = $imageToDelete[0]['image_path'];
            
            // 3. Hapus dari database Supabase (dengan filter user_id)
            $deleteDb = Http::withHeaders([ /* ...headers... */ ])->delete(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId
            );

            if (!$deleteDb->successful()) {
                Log::error('Database Delete Error: ' . $deleteDb->body());
                return back()->with('error', 'Gagal menghapus data dari database.');
            }

            // 4. Hapus juga file dari Supabase Storage
            Http::withHeaders([ /* ...headers... */ ])->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $imagePath);


            return redirect()->route('gallery.index')->with('success', '🗑️ Karya berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error in destroy(): ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }
}
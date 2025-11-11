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
    // Fungsi bantuan untuk mendapatkan header Supabase
    private function getSupabaseHeaders() {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ];
    }

    // ==========================================================
    // 🟢 READ GAMBAR & FILTER/SEARCH (EXPLORE)
    // ==========================================================
    
    public function index(Request $request)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();

        try {
            $selectFields = 'id,title,image_path,category_id,user_id,created_at,description';
            $query = env('SUPABASE_REST_URL') . '/images?select=' . $selectFields;
            
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query .= '&or=(title.ilike.%25' . $search . '%25,description.ilike.%25' . $search . '%25)';
            }
            
            if ($request->has('category') && !empty($request->category)) {
                $category = $request->category;
                $query .= '&category_id=eq.' . $category;
            }

            $query .= '&order=created_at.desc';

            $imagesResponse = Http::withHeaders($supabaseHeaders)->get($query);
            
            $images = [];
            
            if (!$imagesResponse->successful()) {
                Log::error('💥 Explore Fetch Failed:', [
                    'status' => $imagesResponse->status(),
                    'body' => $imagesResponse->body()
                ]);
            } else {
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

            $categoriesResponse = Http::withHeaders($supabaseHeaders)
                ->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');

            $categories = $categoriesResponse->json() ?? [];

            return view('images.index', compact('images', 'categories'));

        } catch (\Exception $e) {
            Log::error('💥 Error in index(): ' . $e->getMessage());
            $images = [];
            $categories = [];
            return view('images.index', compact('images', 'categories'))->with('error', 'Gagal memuat galeri.');
        }
    }
    
    // ==========================================================
    // 🟢 SHOW GAMBAR (DETAIL) - KRITIS
    // ==========================================================
    public function show($id)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();
        try {
            $response = Http::withHeaders($supabaseHeaders)
                ->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=*'); 

            $imageData = $response->json();

            if (empty($imageData)) {
                abort(404);
            }

            $image = $imageData[0];
            
            $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';
            $image['image_url'] = $baseStorageUrl . $image['image_path'];
            
            $userResponse = Http::withHeaders($supabaseHeaders)
                ->get(env('SUPABASE_REST_URL') . '/users?id=eq.' . $image['user_id'] . '&select=name');

            $userData = $userResponse->json();
            $image['user_name'] = $userData[0]['name'] ?? 'Pengguna Artrium'; 

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
        $supabaseHeaders = $this->getSupabaseHeaders();
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name&order=name.asc';
        $response = Http::withHeaders($supabaseHeaders)->get($url);
        $categories = $response->json() ?? [];
        
        return view('images.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login untuk mengunggah karya.');
        }

        $userId = Auth::id(); 

        $request->validate([
            'image' => 'required|image|max:2048',
            'title' => 'required|string|max:255',
            'category_id' => 'required|numeric', 
            'description' => 'nullable|string' 
        ]);

        try {
            $file = $request->file('image');
            $mimeType = $file->getMimeType();
            $filename = preg_replace(
                '/[^A-Za-z0-9_\-\.]/',
                '_',
                time() . '_' . $userId . '_' . $file->getClientOriginalName()
            );

            // 1. Upload ke Supabase Storage
            $upload = Http::withHeaders($supabaseHeaders)
                ->withBody(file_get_contents($file), $mimeType)
                ->post(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename);

            if (!$upload->successful()) {
                Log::error('Storage Upload Failed:', [
                    'status' => $upload->status(),
                    'body' => $upload->body()
                ]);
                return back()->with('error', 'Gagal upload ke Supabase Storage.');
            }

            // 2. Data untuk tabel images (Database)
            $imageData = [
                'title' => $request->title,
                'description' => $request->description, 
                'image_path' => $filename,
                'category_id' => $request->category_id,
                'user_id' => $userId, 
                'created_at' => now()->toIso8601String() 
            ];

            // 3. Simpan metadata ke database Supabase
            $databaseUrl = env('SUPABASE_REST_URL') . '/images';
            $createImage = Http::withHeaders($supabaseHeaders)->post($databaseUrl, $imageData);

            if (!$createImage->successful()) {
                Http::withHeaders($supabaseHeaders)->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename);
                
                Log::error('Database Save Failed (Final):', [
                    'error' => $createImage->body(),
                    'data_sent' => $imageData
                ]);
                return back()->with('error', 'Gagal menyimpan metadata.');
            }

            return redirect()->route('gallery.index')
                ->with('success', '✨ Karya berhasil diunggah!');
            
        } catch (\Exception $e) {
            Log::error('❌ Exception in store():', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat upload: ' . $e->getMessage());
        }
    }


    // ==========================================================
    // 🟢 EDIT GAMBAR (Sudah Dilengkapi Header dan Logging)
    // ==========================================================
    public function edit($id)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();
        
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }
        $userId = Auth::id();

        try {
            $requestUrl = env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId;
            
            $response = Http::withHeaders($supabaseHeaders)
                ->get($requestUrl);
            
            Log::info('Edit-Supabase Request:', ['URL' => $requestUrl, 'UserID' => $userId]);
            Log::info('Edit-Supabase Response:', ['Status' => $response->status(), 'Body' => $response->body()]);

            $imageData = $response->json();

            if (empty($imageData)) {
                abort(403, 'Akses ditolak. Karya tidak ditemukan atau bukan milik Anda.');
            }

            $image = $imageData[0];
            
            $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';
            $image['image_url'] = $baseStorageUrl . $image['image_path'];
            
            $categoriesResponse = Http::withHeaders($supabaseHeaders)
                ->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');
            $categories = $categoriesResponse->json() ?? [];

            return view('images.edit', compact('image', 'categories')); 
        } catch (\Exception $e) {
            Log::error('Error in edit(): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            abort(404);
        }
    }

    // ==========================================================
    // 🟢 UPDATE GAMBAR (Perbaikan: JSON Encoding Error)
    // ==========================================================
    public function update(Request $request, $id)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();

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

            // 🛑 PERBAIKAN PENTING: Membersihkan karakter non-UTF8 sebelum dikirim ke Supabase
            // Menggunakan konversi dan pembersihan agresif untuk mengatasi error JSON
            foreach ($updateData as $key => $value) {
                if (is_string($value)) {
                    // Konversi ke UTF-8 dari 'windows-1252' sebagai fallback yang umum
                    $value = mb_convert_encoding($value, 'UTF-8', 'windows-1252'); 
                    
                    // Membersihkan karakter kontrol dan non-cetak yang tidak valid
                    $updateData[$key] = preg_replace('/[[:^print:]]/', '', $value);
                }
            }

            if ($request->hasFile('image')) {
                // Logika penghapusan file lama dan upload file baru
                $oldImageResponse = Http::withHeaders($supabaseHeaders)
                    ->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId . '&select=image_path');
                $oldImageData = $oldImageResponse->json();
                
                if (!empty($oldImageData) && isset($oldImageData[0]['image_path'])) {
                    $oldImagePath = $oldImageData[0]['image_path'];
                    Http::withHeaders($supabaseHeaders)
                        ->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $oldImagePath);
                }
                
                $file = $request->file('image'); 
                $filename = time() . '_' . $userId . '_' . $file->getClientOriginalName();

                $upload = Http::withHeaders($supabaseHeaders)
                    ->post(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename, file_get_contents($file));
                
                if (!$upload->successful()) { 
                    Log::error('Supabase Storage Upload Failed (Update):', ['status' => $upload->status(), 'body' => $upload->body()]);
                    return back()->with('error', 'Gagal mengunggah gambar baru.'); 
                }
                
                $updateData['image_path'] = $filename;
            }

            // Update Database
            $updateDb = Http::withHeaders($supabaseHeaders)->patch(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId, 
                $updateData // Menggunakan data yang sudah bersih
            );

            if (!$updateDb->successful()) {
                Log::error('Supabase Update Error:', ['url' => env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId, 'status' => $updateDb->status(), 'body' => $updateDb->body()]);
                return back()->with('error', 'Gagal memperbarui data.');
            }

            return redirect()->route('images.show', $id)->with('success', '✅ Karya berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error('Error in update(): ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }
    
    // ==========================================================
    // 🟢 DELETE GAMBAR (Sudah Dilengkapi Header)
    // ==========================================================
    public function destroy($id)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }
        $userId = Auth::id();

        try {
            // Dapatkan path gambar dan cek otorisasi
            $imageResponse = Http::withHeaders($supabaseHeaders)->get(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId . '&select=image_path'
            );

            $imageToDelete = $imageResponse->json();

            if (empty($imageToDelete)) {
                abort(403, 'Akses ditolak. Karya tidak ditemukan atau bukan milik Anda.');
            }
            Log::info('🟢 Delete Response Supabase body:', $imageToDelete);


            $imagePath = $imageToDelete[0]['image_path'];
            
            // Hapus dari database Supabase (dengan filter user_id)
            $deleteDb = Http::withHeaders($supabaseHeaders)->delete(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId
            );

            if (!$deleteDb->successful()) {
                Log::error('Database Delete Error: ' . $deleteDb->body());
                return back()->with('error', 'Gagal menghapus data dari database.');
            }

            // Hapus juga file dari Supabase Storage
            Http::withHeaders($supabaseHeaders)->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $imagePath);


            return redirect()->route('gallery.index')->with('success', '🗑️ Karya berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error in destroy(): ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }
}
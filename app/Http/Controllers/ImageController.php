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
    // 🟢 READ GAMBAR & FILTER/SEARCH (EXPLORE)
    // ==========================================================
    
    public function index(Request $request)
    {
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

            Log::info('📡 Supabase Query (Explore):', ['query' => $query]);

            $imagesResponse = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
            ])->get($query);

            // 🔍 tambahan kecil untuk cek isi response Supabase
            Log::info('🟢 Response Supabase body:', ['body' => $imagesResponse->body()]);

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

            $categoriesResponse = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
            ])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');

            $categories = $categoriesResponse->json() ?? [];

            return view('images.index', compact('images', 'categories'));

        } catch (\Exception $e) {
            Log::error('💥 Error in index(): ' . $e->getMessage());
            return view('images.index', compact('images', 'categories'))
                ->with('error', 'Gagal memuat galeri.');
        }
    }

    // ==========================================================
    // 🟢 SHOW DETAIL GAMBAR
    // ==========================================================
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
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name&order=name.asc';
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'), 
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get($url);
        $categories = $response->json() ?? [];
        
        return view('images.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Anda harus login untuk mengunggah karya.');
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

            $upload = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => $mimeType,
            ])->withBody(file_get_contents($file), $mimeType)
            ->post(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename);

            if (!$upload->successful()) {
                Log::error('Storage Upload Failed:', [
                    'status' => $upload->status(),
                    'body' => $upload->body()
                ]);
                return back()->with('error', 'Gagal upload ke Supabase Storage.');
            }

            $imageData = [
                'title' => $request->title,
                'description' => $request->description, 
                'image_path' => $filename,
                'category_id' => $request->category_id,
                'user_id' => $userId, 
                'created_at' => now()->toIso8601String() 
            ];

            $databaseUrl = env('SUPABASE_REST_URL') . '/images';
            $createImage = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->post($databaseUrl, $imageData);

            if (!$createImage->successful()) {
                Http::withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
                ])->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename);
                
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
    // 🟢 UPDATE GAMBAR (DENGAN OTORISASI)
    // ==========================================================
    public function edit($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }
        $userId = Auth::id();

        try {
            $response = Http::withHeaders([ /* ...headers... */ ])
                ->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId);

            $imageData = $response->json();

            if (empty($imageData)) {
                abort(403, 'Akses ditolak. Anda bukan pemilik karya ini.');
            }

            $image = $imageData[0];
            $categoriesResponse = Http::withHeaders([ /* ...headers... */ ])
                ->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');
            $categories = $categoriesResponse->json() ?? [];

            return view('images.edit', compact('image', 'categories')); 
        } catch (\Exception $e) {
            Log::error('Error in edit(): ' . $e->getMessage());
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
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
                $oldImageResponse = Http::withHeaders([ /* ...headers... */ ])
                    ->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId . '&select=image_path');
                $oldImageData = $oldImageResponse->json();

                if (!empty($oldImageData) && isset($oldImageData[0]['image_path'])) {
                    $oldImagePath = $oldImageData[0]['image_path'];
                    Http::withHeaders([ /* ...headers... */ ])
                        ->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $oldImagePath);
                }
                
                $file = $request->file('image'); 
                $filename = time() . '_' . $userId . '_' . $file->getClientOriginalName();

                $upload = Http::withHeaders([ /* ...headers... */ ])
                    ->post(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename, file_get_contents($file));
                
                if (!$upload->successful()) { 
                    return back()->with('error', 'Gagal mengunggah gambar baru.');
                }
                
                $updateData['image_path'] = $filename;
            }

            $updateDb = Http::withHeaders([ /* ...headers... */ ])->patch(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId, 
                $updateData
            );

            if (!$updateDb->successful()) {
                Log::error('Supabase Update Error: ' . $updateDb->body());
                return back()->with('error', 'Gagal memperbarui data.');
            }

            return redirect()->route('images.show', $id)
                ->with('success', '✅ Karya berhasil diperbarui!');
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
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }
        $userId = Auth::id();

        try {
            $imageResponse = Http::withHeaders([ /* ...headers... */ ])->get(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId . '&select=image_path'
            );

            $imageToDelete = $imageResponse->json();

            if (empty($imageToDelete)) {
                abort(403, 'Akses ditolak. Karya tidak ditemukan atau bukan milik Anda.');
            }
            Log::info('🟢 Response Supabase body:', $imageResponse->json());


            $imagePath = $imageToDelete[0]['image_path'];
            
            $deleteDb = Http::withHeaders([ /* ...headers... */ ])->delete(
                env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&user_id=eq.' . $userId
            );

            if (!$deleteDb->successful()) {
                Log::error('Database Delete Error: ' . $deleteDb->body());
                return back()->with('error', 'Gagal menghapus data dari database.');
            }

            Http::withHeaders([ /* ...headers... */ ])
                ->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $imagePath);

            return redirect()->route('gallery.index')
                ->with('success', '🗑️ Karya berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error in destroy(): ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }
}

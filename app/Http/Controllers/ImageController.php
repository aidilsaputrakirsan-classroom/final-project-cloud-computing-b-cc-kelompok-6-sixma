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
    // 🟢 READ GAMBAR & FILTER/SEARCH (DAFFA & KIRANA)
    // ==========================================================
    

public function index(Request $request)
{
    try {
        $query = env('SUPABASE_REST_URL') . '/images?select=*';
        
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query .= '&title=ilike.%25' . $search . '%25';
        }
        
        if ($request->has('category') && !empty($request->category)) {
            $category = $request->category;
            $query .= '&category=eq.' . $category;
        }

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

        $allImagesResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ])->get(env('SUPABASE_REST_URL') . '/images?select=category');

        $categories = [];
        if ($allImagesResponse->successful()) {
            $allImagesData = $allImagesResponse->json();
            if (is_array($allImagesData)) {
                foreach ($allImagesData as $img) {
                    if (!empty($img['category'])) {
                        $categories[] = $img['category'];
                    }
                }
                $categories = array_unique($categories);
                sort($categories);
            }
        }

        return view('images.index', compact('images', 'categories'));

    } catch (\Exception $e) {
        Log::error('💥 Error in index(): ' . $e->getMessage());
        $images = [];
        $categories = [];
        return view('images.index', compact('images', 'categories'));
    }
}

// private function getSampleImages()
// {
//     $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';
    
//     // Coba ambil file yang ada di bucket, atau fallback ke placeholder
//     return [
//         [
//             'id' => 1,
//             'title' => 'Sample Nature',
//             'image_path' => 'sample1.jpg',
//             'image_url' => $baseStorageUrl . 'sample1.jpg', // Coba akses file di bucket
//             'location' => 'Bali',
//             'user' => ['name' => 'Test User']
//         ],
//         [
//             'id' => 2,
//             'title' => 'Sample Urban', 
//             'image_path' => 'sample2.jpg',
//             'image_url' => $baseStorageUrl . 'sample2.jpg', // Coba akses file di bucket
//             'location' => 'Jakarta',
//             'user' => ['name' => 'Test User 2']
//         ]
//     ];
// }





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
    // 🟢 CREATE GAMBAR (KIRANA)
    // ==========================================================
    public function create()
    {
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name';
        $response = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get($url);
        $categories = $response->json() ?? [];
        return view('images.create', compact('categories'));
    }

 public function store(Request $request)
{
    $request->validate([
        'image' => 'required|image|max:2048',
        'title' => 'required|string|max:255',
    ]);

    try {
        $file = $request->file('image');
        $mimeType = $file->getMimeType();
        $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', time() . '_' . $file->getClientOriginalName());

        Log::info('📁 File Info:', [
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'mime_type' => $mimeType,
            'size' => $file->getSize()
        ]);

        // Upload ke Supabase Storage
        $upload = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => $mimeType,
        ])->withBody(file_get_contents($file), $mimeType)
        ->post(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename);

        Log::info('🔼 Storage Upload Response:', [
            'status' => $upload->status(),
            'body' => $upload->body(),
            'successful' => $upload->successful()
        ]);

        if (!$upload->successful()) {
            return back()->with('error', 'Gagal upload ke Supabase Storage. Status: ' . $upload->status());
        }

        // Data untuk tabel images
        $imageData = [
            'title' => $request->title,
            'image_path' => $filename,
            'category' => $request->category,
        ];

        if (!empty($request->category_id)) {
            $imageData['category_id'] = $request->category_id;
        }

        Log::info('💾 Data untuk database:', $imageData);

        // Simpan metadata ke database Supabase
        $databaseUrl = env('SUPABASE_REST_URL') . '/images';
        $createImage = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation'
        ])->post($databaseUrl, $imageData);

        Log::info('🗃️ Database Response:', [
            'status' => $createImage->status(),
            'successful' => $createImage->successful(),
            'body' => $createImage->body(),
        ]);

        if (!$createImage->successful()) {
            $errorBody = $createImage->body();
            Log::error('❌ Gagal menyimpan ke database:', [
                'status' => $createImage->status(),
                'error' => $errorBody,
                'data_sent' => $imageData,
                'url' => $databaseUrl
            ]);
            
            // Hapus file dari storage jika gagal simpan ke database
            Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            ])->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $filename);
            
            return back()->with('error', 'Gagal menyimpan metadata. Error: ' . $errorBody);
        }

        // ✅ FIX: arahkan ke galeri
        return redirect()->route('gallery.index')->with('success', '✨ Karya berhasil diunggah!');
        
    } catch (\Exception $e) {
        Log::error('❌ Exception:', ['message' => $e->getMessage()]);
        return back()->with('error', 'Terjadi kesalahan saat upload.');
    }
}


    // ==========================================================
    // 🟢 UPDATE GAMBAR (RIA - ANDA)
    // ==========================================================
    
  public function edit($id)
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

        $categoriesResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');
        
        $categories = $categoriesResponse->json() ?? [];

        return view('images.edit', compact('image', 'categories')); 
    } catch (\Exception $e) {
        Log::error('Error in edit(): ' . $e->getMessage());
        abort(404);
    }
}

    public function update(Request $request, $id)
{
    try {
        $updateData = $request->except(['_token', '_method', 'image']);

        if ($request->hasFile('image')) {
            $file = $request->file('image'); 
            $filename = time() . '_' . $file->getClientOriginalName();
            
            $upload = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/octet-stream',
            ])->post(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $filename, file_get_contents($file));
            
            if (!$upload->successful()) { 
                return back()->with('error', 'Gagal mengunggah gambar baru.');
            }
            
            $updateData['image_path'] = $filename;
        }

        $updateDb = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
        ])->patch(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id, $updateData);

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
    // 🟢 DELETE GAMBAR (DAFFA)
    // ==========================================================
   public function destroy($id)
{
    try {
        // Hapus dari database Supabase
        $deleteDb = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->delete(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id);

        if (!$deleteDb->successful()) {
            Log::error('Database Delete Error: ' . $deleteDb->body());
            return back()->with('error', 'Gagal menghapus data dari database.');
        }

        return redirect()->route('gallery.index')->with('success', '🗑️ Karya berhasil dihapus!');
    } catch (\Exception $e) {
        Log::error('Error in destroy(): ' . $e->getMessage());
        return back()->with('error', 'Terjadi kesalahan.');
    }
}
}
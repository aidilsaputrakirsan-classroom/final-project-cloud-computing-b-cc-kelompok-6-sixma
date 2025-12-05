<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon; 
use Illuminate\Support\Facades\Cookie; 

class ImageController extends Controller
{
    /**
     * Mengambil JWT yang tersimpan di Model pengguna yang sedang login.
     */
    private function getAuthJwt() {
        return Auth::user()->supabase_jwt ?? null;
    }


    /**
     * Mengembalikan header standar untuk request Supabase REST API (Anon Key)
     */
    private function getSupabaseHeaders() {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ];
    }
    
    /**
     * Mengembalikan header dengan JWT Pengguna untuk operasi otentikasi (CUD)
     */
    private function getAuthHeaders() {
        $userJWT = $this->getAuthJwt();

        if (empty($userJWT)) {
            // Jika JWT kosong, kembalikan headers anonim, namun operasi CUD akan gagal
            return $this->getSupabaseHeaders(); 
        }

        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . $userJWT, // KRITIS: Menggunakan JWT Pengguna
            'Content-Type' => 'application/json'
        ];
    }


    /**
     * Mengambil URL dasar Storage Supabase.
     */
    private function getStorageUrl() {
        return env('SUPABASE_URL') . '/storage/v1/object/public/images/';
    }
    
    // ----------------------------------------------------------
    // READ (Galeri/Explore - INDEX) - DENGAN LIKES
    // ----------------------------------------------------------
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $category = $request->category ?? null;
        
        $cacheKey = 'explore_images_list_' . md5(($search ?? '') . '_' . ($category ?? ''));
        $supabase_storage_url = $this->getStorageUrl();

        $userId = Auth::check() ? Auth::user()->supabase_uuid : null;

        // 1. Coba ambil data dari cache selama 60 detik
        $images = Cache::remember($cacheKey, 60, function () use ($supabase_storage_url, $search, $category, $userId) {
            
            $headers = $this->getSupabaseHeaders();

            // Query untuk Index: Mengambil hitungan likes
            $url = env('SUPABASE_REST_URL') . '/images?select=
                id,
                title,
                image_path,
                category_id,
                created_at,
                users:user_id(name),
                categories:category_id(name),
                likes_count:likes(count)
                &order=created_at.desc';

            // FILTERING
            if (!empty($search)) {
                $encoded = urlencode('%' . $search . '%');
                $url .= "&or=(title.ilike.$encoded,description.ilike.$encoded)";
            }
            if (!empty($category)) {
                $url .= "&category_id=eq.$category";
            }

            $response = Http::withHeaders($headers)->get($url);

            if (!$response->successful()) {
                Log::error('Gagal mengambil data galeri dari Supabase: ' . $response->body());
                return [];
            }

            $images = $response->json() ?? [];

            // Memperbaiki struktur data dan menambahkan image_url
            $images = array_map(function($image) use ($supabase_storage_url) {
                if (isset($image['image_path'])) {
                    $image['image_url'] = $supabase_storage_url . $image['image_path'];
                }
                // Mengambil count dari likes_count array dan menormalkannya
                $image['like_count'] = $image['likes_count'][0]['count'] ?? 0;
                unset($image['likes_count']);

                // Menyesuaikan struktur data kategori
                $image['category_name'] = $image['categories']['name'] ?? null;
                
                return $image;
            }, $images);

            return $images; // Simpan hasil ke cache
        });
        
        // Cek status like pengguna (HARUS DILAKUKAN DI LUAR CACHE)
        if ($userId && !empty($images)) {
            $image_ids = array_column($images, 'id');
            $ids_string = implode(',', $image_ids);
            
            // Mengambil semua ID gambar yang sudah di-like oleh user ini
            $likeCheckUrl = env('SUPABASE_REST_URL') . "/likes?select=image_id&image_id=in.({$ids_string})&user_id=eq.{$userId}";
            
            $likeCheckHeaders = $this->getAuthHeaders();
            $likeCheckResponse = Http::withHeaders($likeCheckHeaders)->get($likeCheckUrl);

            $userLikes = [];
            if ($likeCheckResponse->successful()) {
                $userLikes = array_column($likeCheckResponse->json(), 'image_id');
            } else {
                Log::warning('⚠️ Gagal memeriksa status like pengguna: ' . $likeCheckResponse->body());
            }

            // Gabungkan status 'is_liked' ke array $images
            $images = array_map(function($image) use ($userLikes) {
                $image['is_liked'] = in_array($image['id'], $userLikes);
                return $image;
            }, $images);
        }

        return view('images.index', compact('images'));
    }
    
    // ----------------------------------------------------------
    // READ (Detail - SHOW) - LIKES count + user like status
    // ----------------------------------------------------------
    public function show($id)
    {
        $cacheKey = 'images_detail_' . $id;
        $supabase_storage_url = $this->getStorageUrl();
        $userId = Auth::check() ? Auth::user()->supabase_uuid : null;
        
        // FIX KRITIS: Ganti single complex query menjadi multi-step query untuk stabilitas
        $image = Cache::remember($cacheKey, 30, function () use ($id, $supabase_storage_url) {
            $headers = $this->getSupabaseHeaders();
            
            // --- STEP 1: Ambil data Gambar + Kategori + Pemilik (Query Sederhana) ---
            $imageResponse = Http::withHeaders($headers)->get(
                env('SUPABASE_REST_URL') . '/images?select=*,users:user_id(name, email),categories:category_id(name)&id=eq.'.$id
            );

            if (!$imageResponse->successful() || empty($imageResponse->json())) {
                Log::error('❌ Gagal mengambil data dasar gambar: ' . $imageResponse->body());
                return null;
            }

            $image = $imageResponse->json()[0];
            
            if (isset($image['image_path'])) {
                $image['image_url'] = $supabase_storage_url . $image['image_path'];
            }
            
            // --- STEP 2: Ambil data Komentar + Pemilik Komentar (Query Terpisah) ---
            // Karena ini adalah array terpisah, kita perlu menggabungkannya ke $image
            $commentsResponse = Http::withHeaders($headers)->get(
                env('SUPABASE_REST_URL') . '/comments?select=id,content,created_at,user_id,users:user_id(name)&image_id=eq.'.$id.'&order=created_at.desc'
            );
            
            $image['comments'] = [];
            if ($commentsResponse->successful() && !empty($commentsResponse->json())) {
                $image['comments'] = $commentsResponse->json();
            } else {
                Log::warning('⚠️ Gagal mengambil komentar (OK jika 404/Empty): ' . $commentsResponse->body());
            }

            // --- STEP 3: Ambil Jumlah Like (Query Terpisah) ---
            $likesCountResponse = Http::withHeaders($headers)->get(
                env('SUPABASE_REST_URL') . '/likes?image_id=eq.'.$id.'&select=count'
            );
            
            $image['like_count'] = 0;
            if ($likesCountResponse->successful() && !empty($likesCountResponse->json())) {
                 $image['like_count'] = $likesCountResponse->json()[0]['count'] ?? 0;
            }

            return $image;
        });

        if (is_null($image)) {
            abort(404);
        }
        
        // --- STEP 4: Cek status like pengguna (Dilakukan di luar cache) ---
        $image['is_liked'] = false; // Default
        if ($userId) {
            $likeCheckUrl = env('SUPABASE_REST_URL') . "/likes?select=id&image_id=eq.{$id}&user_id=eq.{$userId}";
            $likeCheckHeaders = $this->getAuthHeaders(); 
            $likeCheckResponse = Http::withHeaders($likeCheckHeaders)->get($likeCheckUrl);

            if ($likeCheckResponse->successful() && count($likeCheckResponse->json()) > 0) {
                $image['is_liked'] = true;
            } else {
                // Warning jika ada error RLS/token, tapi tidak memblokir halaman
                Log::warning('⚠️ Gagal memeriksa status like detail pengguna: ' . $likeCheckResponse->body());
            }
        }
        
        // Karena komen sudah diurutkan di query, tidak perlu usort.
        // Cukup pastikan struktur kategori sudah benar untuk Blade
        if (isset($image['categories']) && is_array($image['categories'])) {
            $image['category_name'] = $image['categories']['name'] ?? 'N/A';
        }

        return view('images.show', compact('image'));
    }
    
    // ----------------------------------------------------------
    // CREATE (Form Upload)
    // ----------------------------------------------------------
    public function create()
    {
        $headers = $this->getSupabaseHeaders();
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name&order=name.asc';
        $categories = Http::withHeaders($headers)->get($url)->json() ?? [];

        return view('images.create', compact('categories'));
    }

    // ----------------------------------------------------------
    // STORE (UPLOAD GAMBAR)
    // ----------------------------------------------------------
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }

        $user = Auth::user();
        $userUUID = $user->id;
        $userJWT = $this->getAuthJwt();
        
        if (empty($userUUID) || empty($userJWT)) { 
            Log::error('Upload Gagal: UUID atau JWT pengguna kosong.');
            return back()->with('error', 'Sesi otentikasi tidak lengkap. Harap logout dan login kembali.');
        }
        
        $authHeaders = $this->getAuthHeaders();
        
        $request->validate([
            'image' => 'required|image|max:4096',
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer|min:1', 
            'description' => 'nullable|string'
        ]);

        try {
            $file = $request->file('image');
            $mime = $file->getMimeType();

            $filename = time() . '_' . $userUUID . '_' . preg_replace(
                '/[^A-Za-z0-9\.\-_]/', '_', $file->getClientOriginalName()
            );

            $uploadUrl = env('SUPABASE_URL') . '/storage/v1/object/images/' . $filename;

            $storageHeaders = [
                 'apikey' => env('SUPABASE_ANON_KEY'),
                 'Authorization' => 'Bearer ' . $userJWT, 
                 'Content-Type' => $mime
            ];

            $upload = Http::withHeaders($storageHeaders)
                          ->withBody(file_get_contents($file), $mime)
                          ->post($uploadUrl);

            if (!$upload->successful()) {
                Log::error('Supabase Storage Upload Gagal: ' . $upload->body());
                return back()->with('error', 'Upload file gagal: ' . $upload->body());
            }

            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'image_path' => $filename,
                'category_id' => (int) $request->category_id,
                'user_id' => $userUUID,
                'created_at' => now()->toIso8601String()
            ];

            $db = Http::withHeaders(array_merge($authHeaders, ['Prefer' => 'return=minimal']))
                     ->post(env('SUPABASE_REST_URL') . '/images', $data);

            if (!$db->successful()) {
                Log::error('Supabase DB Insert Gagal: ' . $db->body() . ' Data yang dikirim: ' . json_encode($data));
                return back()->with('error', 'DB gagal: ' . ($db->json()['message'] ?? 'Constraint Kategori tidak valid.'));
            }
            
            // Hapus cache explore setelah insert
            Cache::forget('explore_images_list');

            return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diupload!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
            
        } catch (\Exception $e) {
            Log::error('Error saat proses upload: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    // ----------------------------------------------------------
    // UPDATE (PATCH Gambar)
    // ------------------------------------------------------
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|integer',
                'image' => 'nullable|image|max:4096'
            ]);

            $headers = $this->getAuthHeaders();

            $old = Http::withHeaders($this->getSupabaseHeaders())
                     ->get(env('SUPABASE_REST_URL') . "/images?id=eq.$id&select=image_path")
                     ->json()[0] ?? null;

            $newImagePath = $old['image_path'] ?? null;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $mime = $file->getMimeType();
                $newName = time() . '_' . Auth::id() . '_' . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $file->getClientOriginalName());

                $upload = Http::withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Authorization' => 'Bearer ' . $this->getAuthJwt(),
                    'Content-Type' => $mime
                ])
                ->withBody(file_get_contents($file), $mime)
                ->post(env('SUPABASE_URL') . '/storage/v1/object/images/' . $newName);

                if ($upload->successful()) {
                    $newImagePath = $newName;
                }
            }

            $payload = [
                [
                    'title' => $request->title,
                    'description' => $request->description,
                    'category_id' => $request->category_id,
                    'image_path' => $newImagePath,
                    'updated_at' => now()->toIso8601String()
                ]
            ];

            $update = Http::withHeaders(array_merge($headers, [
                'Content-Type' => 'application/json'
            ]))
            ->patch(env('SUPABASE_REST_URL') . "/images?id=eq.$id&user_id=eq.".Auth::user()->supabase_uuid, $payload);

            if (!$update->successful()) {
                \Log::error('Update gagal: ' . $update->body());
                return back()->with('error', 'Update gagal: ' . ($update->json()['message'] ?? 'Unknown error'));
            }

            Cache::forget('explore_images_list');
            Cache::forget('images_detail_' . $id);

            return redirect()->route('profile.show')
                ->with('success', 'Berhasil diperbarui!');

        } catch (\Exception $e) {
            \Log::error('Update Error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat update.');
        }
    }


    public function edit($id)
    {
        return $this->showEditForm($id);
    }

    private function showEditForm($id)
    {
        $headers = $this->getSupabaseHeaders();

        $image = Http::withHeaders($headers)
            ->get(env('SUPABASE_REST_URL') . "/images?id=eq.$id&select=*")
            ->json()[0] ?? null;

        if (!$image) {
            return back()->with('error', 'Gambar tidak ditemukan.');
        }
        
        if (!Auth::check() || Auth::user()->supabase_uuid !== $image['user_id']) {
             return back()->with('error', 'Anda tidak memiliki izin untuk mengedit karya ini.');
        }

        $image['image_url'] = $this->getStorageUrl() . $image['image_path'];

        $categories = Http::withHeaders($headers)
            ->get(env('SUPABASE_REST_URL') . '/categories?select=id,name')
            ->json() ?? [];

        return view('images.edit', compact('image', 'categories'));
    }

    // ----------------------------------------------------------
    // DELETE (Hapus Gambar)
    // ----------------------------------------------------------
    public function destroy($id)
    {
        if (!Auth::check()) {
             return back()->with('error', 'Anda harus login untuk menghapus karya.');
        }

        try {
            $userUUID = Auth::user()->supabase_uuid;
            $headers = $this->getAuthHeaders();

            $deleteUrl = env('SUPABASE_REST_URL') . "/images?id=eq.$id&user_id=eq.$userUUID";
            
            $response = Http::withHeaders($headers)->delete($deleteUrl);
            
            if (!$response->successful()) {
                Log::error('❌ DELETE_IMAGE_FAILURE:', ['status' => $response->status(), 'body' => $response->body()]);
                return back()->with('error', 'Gagal menghapus karya. Karya mungkin tidak ditemukan atau bukan milik Anda.');
            }

            Cache::forget('explore_images_list');
            Cache::forget('images_detail_' . $id);

            return redirect()->route('gallery.index')->with('success', 'Gambar berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('Error saat proses delete: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus karya.');
        }
    }
    
}
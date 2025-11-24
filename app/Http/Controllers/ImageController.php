<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
<<<<<<< Updated upstream
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // <-- Import Cache
use Carbon\Carbon; 
use Illuminate\Support\Facades\Cookie; 
=======
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Cache; // <-- Diperlukan untuk Cache Kategori
use Carbon\Carbon;
>>>>>>> Stashed changes

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
    
<<<<<<< Updated upstream
    /**
     * Mengembalikan header dengan JWT Pengguna untuk operasi otentikasi (CUD)
     */
    private function getAuthHeaders() {
        $userJWT = $this->getAuthJwt();

        if (empty($userJWT)) {
            // Jika JWT kosong, kembalikan headers anonim, namun operasi CUD akan gagal
            return $this->getSupabaseHeaders(); 
=======
    // ----------------------------------------------------------
    // INDEX / EXPLORE (Optimized for Concurrency & Caching)
    // ----------------------------------------------------------
    public function index(Request $request)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();
        $baseApiUrl = env('SUPABASE_REST_URL');
        $images = [];
        $categories = [];

        try {
            // A. CACHING untuk Kategori (Mempercepat load kategori)
            $categories = Cache::remember('all_categories_supabase', 60 * 60, function () use ($supabaseHeaders, $baseApiUrl) {
                $categoriesResponse = Http::withHeaders($supabaseHeaders)
                    ->get($baseApiUrl . '/categories?select=id,name');
                return $categoriesResponse->json() ?? [];
            });
            

            // B. CONCURRENCY (Mengambil Gambar Secara Paralel)
            $selectFields = 'id,title,image_path,category_id,user_id,created_at,description';
            $queryUrl = $baseApiUrl . '/images?select=' . $selectFields;
            
            // Logika Pencarian dan Filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $queryUrl .= '&or=(title.ilike.%25' . $search . '%25,description.ilike.%25' . $search . '%25)';
            }
            if ($request->has('category') && !empty($request->category)) {
                $queryUrl .= '&category_id=eq.' . $request->category;
            }

            $queryUrl .= '&order=created_at.desc';

            // Menggunakan Concurrency Pool (jika ada request lain bisa dimasukkan di sini)
            [$imagesResponse] = Http::pool(fn (Illuminate\Http\Client\Pool $pool) => [
                $pool->withHeaders($supabaseHeaders)->get($queryUrl),
            ]);

            if ($imagesResponse->successful()) {
                $imagesData = $imagesResponse->json();
                $baseStorageUrl = env('SUPABASE_URL') . '/storage/v1/object/public/images/';

                foreach ($imagesData as $image) {
                    $image['image_url'] = $baseStorageUrl . $image['image_path'];
                    $images[] = $image;
                }
            }

            return view('images.index', compact('images', 'categories'));

        } catch (\Exception $e) {
            Log::error('Explore Performance Error: ' . $e->getMessage());
            return view('images.index', compact('images', 'categories'))->with('error', 'Gagal memuat galeri.');
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
    // READ (Galeri/Explore - INDEX)
    // ----------------------------------------------------------
   public function index(Request $request)
{
    $cacheKey = 'explore_images_list_' . md5(($request->search ?? '') . '_' . ($request->category ?? ''));
    $supabase_storage_url = $this->getStorageUrl();

    // 🔥 FIX FILTER DAN SEARCH HANYA MENAMBAH 2 KONDISI TANPA UBAH LOGIKA LAIN
    $search = $request->search ?? null;
    $category = $request->category ?? null;

    // 1. Coba ambil data dari cache selama 60 detik
    $images = Cache::remember($cacheKey, 60, function () use ($supabase_storage_url, $search, $category) {
        
        $headers = $this->getSupabaseHeaders();

        // QUERY DASAR (SAMAAAA persis seperti kodenya Kirana)
       $url = env('SUPABASE_REST_URL') . '/images?select=id,title,image_path,category_id,created_at,categories(name),users:user_id(name)&order=created_at.desc';


        // 🔥 FIX #1 — SEARCH
        if (!empty($search)) {
            $encoded = urlencode('%' . $search . '%');
            $url .= "&title=ilike.$encoded";
        }

        // 🔥 FIX #2 — FILTER CATEGORY
        if (!empty($category)) {
            $url .= "&category_id=eq.$category";
        }

        Log::info("QUERY FIXED:", [$url]);

        $response = Http::withHeaders($headers)->get($url);

        if (!$response->successful()) {
            Log::error('Gagal mengambil data galeri dari Supabase: ' . $response->body());
            return [];
        }

        $images = $response->json() ?? [];

        // Buat image_url dan Category Name
        $images = array_map(function($image) use ($supabase_storage_url) {
            if (isset($image['image_path'])) {
                $image['image_url'] = $supabase_storage_url . $image['image_path'];
            }
            if (isset($image['categories']) && is_array($image['categories'])) {
                $image['category_name'] = $image['categories'][0]['name'] ?? null;
            }
            return $image;
        }, $images);

        return $images; // Simpan hasil ke cache
    });

    return view('images.index', compact('images'));
}

    // ----------------------------------------------------------
    // READ (Detail Gambar - SHOW)
    // ----------------------------------------------------------
    public function show($id)
    {
        $cacheKey = 'images_detail_' . $id; // Kunci cache spesifik per gambar
        $supabase_storage_url = $this->getStorageUrl();

        // FIX KRITIS: Tambahkan Caching untuk halaman detail (30 detik)
        $image = Cache::remember($cacheKey, 30, function () use ($id, $supabase_storage_url) {

            $headers = $this->getSupabaseHeaders();
=======
    // SHOW / DETAIL GAMBAR (Optimized for Joining/Embedding)
    // ----------------------------------------------------------
    public function show($id)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();
        $baseApiUrl = env('SUPABASE_REST_URL');

        try {
            // 1. Ambil Data Gambar dan Embed Data User Uploader (Join User)
            // Mengambil semua (*) data gambar dan data user Uploader (nama user)
            $response = Http::withHeaders($supabaseHeaders)
                // FIX: Menggunakan JOIN untuk mengambil detail user (uploader:user_id(name))
                ->get($baseApiUrl . '/images?id=eq.' . $id . '&select=*,uploader:user_id(name)'); 

            $data = $response->json();

            if (empty($data)) abort(404);

            $image = $data[0];
            $image['image_url'] = env('SUPABASE_URL') . '/storage/v1/object/public/images/' . $image['image_path'];

            // 2. Ambil Data Komentar TERKAIT dan Embed Data User Komentator
            // Mengambil semua komentar untuk image_id ini, dan JOIN detail user yang berkomentar
            $commentsResponse = Http::withHeaders($supabaseHeaders)
                // FIX: Menggunakan JOIN untuk mengambil detail user komentator (user:user_id(name))
                ->get($baseApiUrl . '/comments?image_id=eq.' . $id . '&select=*,user:user_id(name)&order=created_at.asc'); 
>>>>>>> Stashed changes
            
            // Perbaikan Query: Mengambil semua join yang diperlukan
            $url = env('SUPABASE_REST_URL') . '/images?select=*,categories(name),users:user_id(name),comments(*,users:user_id(name)).order=created_at.desc&id=eq.'.$id;

            $response = Http::withHeaders($headers)->get($url);

            if (!$response->successful() || empty($response->json())) {
                // Jika gagal, jangan simpan di cache, lempar exception
                throw new \Exception('Failed to fetch image detail from Supabase.');
            }

            $image = $response->json()[0]; 
            
            // Perbaikan: Tambahkan image_url
            if (isset($image['image_path'])) {
                $image['image_url'] = $supabase_storage_url . $image['image_path'];
            }
            
            return $image; // Simpan hasil ke cache
        });
        
        // Periksa jika cache/query gagal
        if (!is_array($image) || empty($image)) {
             return redirect()->route('gallery.index')->with('error', 'Gambar tidak ditemukan atau gagal dimuat.');
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
<<<<<<< Updated upstream
        $categories = Http::withHeaders($headers)->get($url)->json() ?? [];
=======

        // Menggunakan caching untuk kategori di sini juga
        $categories = Cache::remember('all_categories_supabase', 60 * 60, function () use ($headers, $url) {
            return Http::withHeaders($headers)->get($url)->json() ?? [];
        });
>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
        $userUUID = $user->id;
        $userJWT = $this->getAuthJwt();
        
        if (empty($userUUID) || empty($userJWT)) { 
            Log::error('Upload Gagal: UUID atau JWT pengguna kosong.');
            return back()->with('error', 'Sesi otentikasi tidak lengkap. Harap logout dan login kembali.');
=======
        $userUUID = $user->supabase_uuid ?? null; 


        if (empty($userUUID)) {
            Log::error('Upload Gagal: UUID pengguna kosong.');
            return back()->with('error', 'UUID pengguna tidak ditemukan. Pastikan Anda login dengan benar dan kolom UUID ada.');
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
    // UPDATE (PATCH Gambar) - DENGAN DEBUGGING LOG
    // ------------------------------------------------------
public function update(Request $request, $id)
{
    try {
=======
    // EDIT
    // ----------------------------------------------------------
    public function edit($id)
    {
        $headers = $this->getSupabaseHeaders();

        $requestUrl = env('SUPABASE_REST_URL') . '/images?id=eq.' . $id;

        $data = Http::withHeaders($headers)->get($requestUrl)->json();

        if (empty($data)) abort(404);

        $image = $data[0];
        $image['image_url'] = env('SUPABASE_URL') . '/storage/v1/object/public/images/' . $image['image_path'];

        $cats = Cache::remember('all_categories_supabase', 60 * 60, function () use ($headers) {
            return Http::withHeaders($headers)
                ->get(env('SUPABASE_REST_URL') . '/categories?select=id,name')->json();
        });

        return view('images.edit', ['image' => $image, 'categories' => $cats]);
    }

    // ----------------------------------------------------------
    // UPDATE GAMBAR
    // ----------------------------------------------------------
    public function update(Request $request, $id)
    {
        $headers = [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json'
        ];

        // Ambil UUID untuk penamaan file baru (jika ada)
        $user = Auth::user();
        $userId = $user->supabase_uuid ?? null; 


>>>>>>> Stashed changes
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|integer',
            'image' => 'nullable|image|max:4096'
        ]);

        // Ambil header otentikasi Supabase
        $headers = $this->getAuthHeaders();

        // Ambil image_path lama
        $old = Http::withHeaders($this->getSupabaseHeaders())
                ->get(env('SUPABASE_REST_URL') . "/images?id=eq.$id&select=image_path")
                ->json()[0] ?? null;

        $newImagePath = $old['image_path'] ?? null;

        // Upload gambar baru jika ada
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

        // 🔥 WAJIB: PATCH Supabase harus array list, bukan object
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
        ->patch(env('SUPABASE_REST_URL') . "/images?id=eq.$id", $payload);

        if (!$update->successful()) {
            \Log::error('Update gagal: ' . $update->body());
            return back()->with('error', 'Update gagal: ' . ($update->json()['message'] ?? 'Unknown error'));
        }

        Cache::flush();

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

    // Ambil data gambar
    $image = Http::withHeaders($headers)
        ->get(env('SUPABASE_REST_URL') . "/images?id=eq.$id&select=*")
        ->json()[0] ?? null;

    if (!$image) {
        return back()->with('error', 'Gambar tidak ditemukan.');
    }

    $image['image_url'] = $this->getStorageUrl() . $image['image_path'];

    // Ambil kategori
    $categories = Http::withHeaders($headers)
        ->get(env('SUPABASE_REST_URL') . '/categories?select=id,name')
        ->json() ?? [];

    return view('images.edit', compact('image', 'categories'));
}

    // ----------------------------------------------------------
    // DELETE (Hapus Gambar) - Dengan Pengecekan Status HTTP Ketat
    // ----------------------------------------------------------
    public function destroy($id)
    {
        // ... (Logika delete) ...

        try {
            // ... (Logika delete) ...

            // Hapus cache setelah delete
            Cache::forget('explore_images_list');
            Cache::forget('images_detail_' . $id); // Hapus cache detail spesifik

            return redirect()->route('gallery.index')->with('success', 'Gambar berhasil dihapus!');

        } catch (\Exception $e) {
            // ... (Error handling) ...
        }
    }
    
}
<?php

namespace App\Http\Controllers;

use App\Facades\Supabase;
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
    private function getAuthJwt()
    {
        return Auth::user()->supabase_jwt ?? null;
    }


    /**
     * Mengembalikan header standar untuk request Supabase REST API (Anon Key)
     */
    private function getSupabaseHeaders()
    {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ];
    }

    /**
     * Mengembalikan header dengan JWT Pengguna untuk operasi otentikasi (CUD)
     */
    private function getAuthHeaders()
    {
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
    private function getStorageUrl()
    {
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

        $images = Cache::remember($cacheKey, 60, function () use ($supabase_storage_url, $search, $category) {

            $headers = $this->getSupabaseHeaders();

            // QUERY SELECT TANPA NEWLINE
            $url = env('SUPABASE_REST_URL') . '/images?select=' .
                'id,' .
                'title,' .
                'image_path,' .
                'category_id,' .
                'created_at,' .
                'users:user_id(name),' .
                'categories:category_id(name),' .
                'likes_count:likes(count)' .
                '&order=created_at.desc';

            // FILTER SEARCH
            if (!empty($search)) {
                $encoded = urlencode('%' . $search . '%');
                $url .= "&or=(title.ilike.$encoded,description.ilike.$encoded)";
            }

            // FILTER CATEGORY
            if (!empty($category)) {
                $url .= "&category_id=eq.$category";
            }

            // FIX SSL PROBLEM (::withoutVerifying())
            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($url);

            if (!$response->successful()) {
                Log::error('❌ Gagal ambil data Supabase: ' . $response->body());
                return [];
            }

            $images = $response->json() ?? [];

            // NORMALISASI DATA
            $images = array_map(function ($image) use ($supabase_storage_url) {

                // Tambahan URL
                if (isset($image['image_path'])) {
                    $image['image_url'] = $supabase_storage_url . $image['image_path'];
                }

                // likes_count:likes(count) → array index 0
                $image['like_count'] = $image['likes_count'][0]['count'] ?? 0;
                unset($image['likes_count']);

                // Ambil kategori name
                $image['category_name'] = $image['categories']['name'] ?? null;

                return $image;
            }, $images);

            return $images;
        });

        // === CEK LIKE USER (TIDAK DI CACHE) ===
        if ($userId && !empty($images)) {

            $image_ids = array_column($images, 'id');

            if (!empty($image_ids)) {
                $ids_string = implode(',', $image_ids);

                $likeCheckUrl = env('SUPABASE_REST_URL') .
                    "/likes?select=image_id&image_id=in.({$ids_string})&user_id=eq.{$userId}";

                $likeCheckHeaders = $this->getAuthHeaders();

                $likeCheckResponse = Http::withHeaders($likeCheckHeaders)
                    ->withoutVerifying()
                    ->get($likeCheckUrl);

                $userLikes = [];

                if ($likeCheckResponse->successful()) {
                    $userLikes = array_column($likeCheckResponse->json(), 'image_id');
                }

                // Tambahkan flag is_liked
                $images = array_map(function ($image) use ($userLikes) {
                    $image['is_liked'] = in_array($image['id'], $userLikes);
                    return $image;
                }, $images);
            }
        }

        return view('images.index', compact('images'));
    }


    // ----------------------------------------------------------
    // READ (Detail - SHOW) - LIKES count + user like status
    // ----------------------------------------------------------
    // public function show($id)
    // {
    //     $cacheKey = 'images_detail_' . $id;
    //     $supabase_storage_url = $this->getStorageUrl();
    //     $userId = Auth::check() ? Auth::user()->supabase_uuid : null;

    //     // FIX KRITIS: Ganti single complex query menjadi multi-step query untuk stabilitas
    //     $image = Cache::remember($cacheKey, 30, function () use ($id, $supabase_storage_url) {
    //         $headers = $this->getSupabaseHeaders();

    //         // --- STEP 1: Ambil data Gambar + Kategori + Pemilik (Query Sederhana) ---
    //         $imageResponse = Http::withHeaders($headers)->get(
    //             env('SUPABASE_REST_URL') . '/images?select=*,users:user_id(name, email),categories:category_id(name)&id=eq.' . $id
    //         );

    //         if (!$imageResponse->successful() || empty($imageResponse->json())) {
    //             Log::error('❌ Gagal mengambil data dasar gambar: ' . $imageResponse->body());
    //             return null;
    //         }

    //         $image = $imageResponse->json()[0];

    //         if (isset($image['image_path'])) {
    //             $image['image_url'] = $supabase_storage_url . $image['image_path'];
    //         }

    //         // --- STEP 2: Ambil data Komentar + Pemilik Komentar (Query Terpisah) ---
    //         // Karena ini adalah array terpisah, kita perlu menggabungkannya ke $image
    //         $commentsResponse = Http::withHeaders($headers)->get(
    //             env('SUPABASE_REST_URL') . '/comments?select=id,content,created_at,user_id,users:user_id(name)&image_id=eq.' . $id . '&order=created_at.desc'
    //         );

    //         $image['comments'] = [];
    //         if ($commentsResponse->successful() && !empty($commentsResponse->json())) {
    //             $image['comments'] = $commentsResponse->json();
    //         } else {
    //             Log::warning('⚠️ Gagal mengambil komentar (OK jika 404/Empty): ' . $commentsResponse->body());
    //         }

    //         // --- STEP 3: Ambil Jumlah Like (Query Terpisah) ---
    //         $likesCountResponse = Http::withHeaders($headers)->get(
    //             env('SUPABASE_REST_URL') . '/likes?image_id=eq.' . $id . '&select=count'
    //         );

    //         $image['like_count'] = 0;
    //         if ($likesCountResponse->successful() && !empty($likesCountResponse->json())) {
    //             $image['like_count'] = $likesCountResponse->json()[0]['count'] ?? 0;
    //         }

    //         return $image;
    //     });

    //     if (is_null($image)) {
    //         abort(404);
    //     }

    //     // --- STEP 4: Cek status like pengguna (Dilakukan di luar cache) ---
    //     $image['is_liked'] = false; // Default
    //     if ($userId) {
    //         $likeCheckUrl = env('SUPABASE_REST_URL') . "/likes?select=id&image_id=eq.{$id}&user_id=eq.{$userId}";
    //         $likeCheckHeaders = $this->getAuthHeaders();
    //         $likeCheckResponse = Http::withHeaders($likeCheckHeaders)->get($likeCheckUrl);

    //         if ($likeCheckResponse->successful() && count($likeCheckResponse->json()) > 0) {
    //             $image['is_liked'] = true;
    //         } else {
    //             // Warning jika ada error RLS/token, tapi tidak memblokir halaman
    //             Log::warning('⚠️ Gagal memeriksa status like detail pengguna: ' . $likeCheckResponse->body());
    //         }
    //     }

    //     // Karena komen sudah diurutkan di query, tidak perlu usort.
    //     // Cukup pastikan struktur kategori sudah benar untuk Blade
    //     if (isset($image['categories']) && is_array($image['categories'])) {
    //         $image['category_name'] = $image['categories']['name'] ?? 'N/A';
    //     }

    //     return view('images.show', compact('image'));
    // }

    public function show($id)
    {
        $cacheKey = 'images_detail_' . $id;
        $supabase_storage_url = $this->getStorageUrl();
        $userId = Auth::check() ? Auth::user()->supabase_uuid : null;

        // STEP 1 — Caching detail image
        $image = Cache::remember($cacheKey, 30, function () use ($id, $supabase_storage_url) {

            $headers = $this->getSupabaseHeaders();

            /*
        |--------------------------------------------------------------------------
        | STEP 1A — Ambil data image dasar (tanpa newline di query)
        |--------------------------------------------------------------------------
        */
            $imageUrl =
                env('SUPABASE_REST_URL') .
                "/images?select=id,title,description,image_path,created_at,category_id,user_id," .
                "users:user_id(name,email)," .
                "categories:category_id(name)" .
                "&id=eq.$id";

            $imageResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($imageUrl);

            if (!$imageResponse->successful() || empty($imageResponse->json())) {
                Log::error('❌ Gagal ambil data gambar: ' . $imageResponse->body());
                return null;
            }

            $image = $imageResponse->json()[0];

            // Tambahkan URL Storage
            if (isset($image['image_path'])) {
                $image['image_url'] = $supabase_storage_url . $image['image_path'];
            }

            /*
        |--------------------------------------------------------------------------
        | STEP 1B — Ambil komentar (dipisah supaya stabil)
        |--------------------------------------------------------------------------
        */
            $commentsUrl =
                env('SUPABASE_REST_URL') .
                "/comments?select=id,content,created_at,user_id,users:user_id(name)" .
                "&image_id=eq.$id" .
                "&order=created_at.desc";

            $commentsResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($commentsUrl);

            $image['comments'] =
                ($commentsResponse->successful() && !empty($commentsResponse->json()))
                ? $commentsResponse->json()
                : [];

            /*
        |--------------------------------------------------------------------------
        | STEP 1C — Hitung likes (dipisah)
        |--------------------------------------------------------------------------
        */
            $likesUrl =
                env('SUPABASE_REST_URL') .
                "/likes?select=count&image_id=eq.$id";

            $likesResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($likesUrl);

            $image['like_count'] =
                ($likesResponse->successful() && !empty($likesResponse->json()))
                ? ($likesResponse->json()[0]['count'] ?? 0)
                : 0;

            return $image;
        });

        /*
    |--------------------------------------------------------------------------
    | Jika null → abort
    |--------------------------------------------------------------------------
    */
        if (is_null($image)) {
            abort(404);
        }

        /*
    |--------------------------------------------------------------------------
    | STEP 2 — Cek apakah user sudah like
    |--------------------------------------------------------------------------
    */
        $image['is_liked'] = false;

        if ($userId) {
            $likeCheckUrl =
                env('SUPABASE_REST_URL') .
                "/likes?select=id&image_id=eq.$id&user_id=eq.$userId";

            $likeResponse = Http::withHeaders($this->getAuthHeaders())
                ->withoutVerifying()
                ->get($likeCheckUrl);

            if ($likeResponse->successful() && !empty($likeResponse->json())) {
                $image['is_liked'] = true;
            } else {
                Log::warning('⚠️ Gagal cek status like user: ' . $likeResponse->body());
            }
        }

        /*
    |--------------------------------------------------------------------------
    | STEP 3 — Normalisasi kategori (supaya Blade tidak error)
    |--------------------------------------------------------------------------
    */
        $image['category_name'] =
            $image['categories']['name']
            ?? 'Tidak ada kategori';

        return view('images.show', compact('image'));
    }


    // ----------------------------------------------------------
    // CREATE (Form Upload)
    // ----------------------------------------------------------
    public function create()
    {
        // $headers = $this->getSupabaseHeaders();
        // $url = env('SUPABASE_REST_URL') . '/categories?select=id,name&order=name.asc';
        // $categories = Http::withHeaders($headers)
        //     ->withoutVerifying()
        //     ->get($url)->json() ?? [];

        // return view('images.create', compact('categories'));
        $categories = Supabase::table('categories')
            ->select('id,name')
            ->order('name')
            ->get() ?? [];

        return view('images.create', compact('categories'));
    }

    // ----------------------------------------------------------
    // STORE (UPLOAD GAMBAR)
    // ----------------------------------------------------------
    // public function store(Request $request)
    // {
    //     if (!Auth::check()) {
    //         return redirect()->route('login')->with('error', 'Silakan login.');
    //     }

    //     $user = Auth::user();
    //     $userUUID = $user->id;
    //     $userJWT = $this->getAuthJwt();

    //     if (empty($userUUID) || empty($userJWT)) {
    //         Log::error('Upload Gagal: UUID atau JWT pengguna kosong.');
    //         return back()->with('error', 'Sesi otentikasi tidak lengkap. Harap logout dan login kembali.');
    //     }

    //     $authHeaders = $this->getAuthHeaders();

    //     $request->validate([
    //         'image' => 'required|image|max:4096',
    //         'title' => 'required|string|max:255',
    //         'category_id' => 'required|integer|min:1',
    //         'description' => 'nullable|string'
    //     ]);

    //     try {
    //         $file = $request->file('image');
    //         $mime = $file->getMimeType();

    //         $filename = time() . '_' . $userUUID . '_' . preg_replace(
    //             '/[^A-Za-z0-9\.\-_]/',
    //             '_',
    //             $file->getClientOriginalName()
    //         );

    //         $uploadUrl = env('SUPABASE_URL') . '/storage/v1/object/images/' . $filename;

    //         $storageHeaders = [
    //             'apikey' => env('SUPABASE_ANON_KEY'),
    //             'Authorization' => 'Bearer ' . $userJWT,
    //             'Content-Type' => $mime
    //         ];

    //         $upload = Http::withHeaders($storageHeaders)
    //             ->withBody(file_get_contents($file), $mime)
    //             ->post($uploadUrl);

    //         if (!$upload->successful()) {
    //             Log::error('Supabase Storage Upload Gagal: ' . $upload->body());
    //             return back()->with('error', 'Upload file gagal: ' . $upload->body());
    //         }

    //         $data = [
    //             'title' => $request->title,
    //             'description' => $request->description,
    //             'image_path' => $filename,
    //             'category_id' => (int) $request->category_id,
    //             'user_id' => $userUUID,
    //             'created_at' => now()->toIso8601String()
    //         ];

    //         $db = Http::withHeaders(array_merge($authHeaders, ['Prefer' => 'return=minimal']))
    //             ->post(env('SUPABASE_REST_URL') . '/images', $data);

    //         if (!$db->successful()) {
    //             Log::error('Supabase DB Insert Gagal: ' . $db->body() . ' Data yang dikirim: ' . json_encode($data));
    //             return back()->with('error', 'DB gagal: ' . ($db->json()['message'] ?? 'Constraint Kategori tidak valid.'));
    //         }

    //         // Hapus cache explore setelah insert
    //         Cache::forget('explore_images_list');

    //         return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diupload!');
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return back()->withErrors($e->errors())->withInput();
    //     } catch (\Exception $e) {
    //         Log::error('Error saat proses upload: ' . $e->getMessage());
    //         return back()->with('error', 'Error: ' . $e->getMessage());
    //     }
    // }
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }

        $user = Auth::user();
        $userUUID = $user->id;
        $userJWT = $this->getAuthJwt();

        if (empty($userUUID) || empty($userJWT)) {
            Log::error('Upload Gagal: UUID atau JWT kosong.');
            return back()->with('error', 'Sesi otentikasi tidak lengkap.');
        }

        $request->validate([
            'image' => 'required|image|max:4096',
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer|min:1',
            'description' => 'nullable|string'
        ]);

        try {
            $file = $request->file('image');

            // Nama file aman
            $filename = time() . '_' . $userUUID . '_' . preg_replace(
                '/[^A-Za-z0-9\.\-_]/',
                '_',
                $file->getClientOriginalName()
            );

            // URL Supabase Storage
            $uploadUrl = env('SUPABASE_URL') . "/storage/v1/object/images/$filename";

            // Ambil mime type asli (image/jpeg, image/png, dll)
            $mime = $file->getMimeType();
            $fileContent = file_get_contents($file);

            // Header upload yang BENAR
            $storageHeaders = [
                'apikey'        => env('SUPABASE_ANON_KEY'),
                'Authorization' => "Bearer $userJWT",
                'Content-Type'  => $mime,    // wajib mime asli
                'x-upsert'      => 'true',
            ];

            // Upload PUT ke Supabase Storage
            $upload = Http::withHeaders($storageHeaders)
                ->withoutVerifying()
                ->withBody($fileContent, $mime)   // <- perbaikan utama
                ->put($uploadUrl);

            if (!$upload->successful()) {
                Log::error('Supabase Storage Upload ERROR: ' . $upload->body());
                return back()->with('error', 'Upload Storage gagal: ' . $upload->body());
            }

            // Insert metadata ke Supabase Database
            $authHeaders = $this->getAuthHeaders();

            $data = [
                'title'        => $request->title,
                'description'  => $request->description,
                'image_path'   => $filename,
                'category_id'  => (int) $request->category_id,
                'user_id'      => $userUUID,
                'created_at'   => now()->toIso8601String(),
            ];

            $db = Http::withHeaders(array_merge($authHeaders, ['Prefer' => 'return=minimal']))
                ->withoutVerifying()
                ->post(env('SUPABASE_REST_URL') . '/images', $data);

            if (!$db->successful()) {
                Log::error("Supabase DB Insert ERROR: {$db->body()} | Data: " . json_encode($data));
                return back()->with('error', 'DB gagal insert data gambar.');
            }

            Cache::forget('explore_images_list');

            return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diupload!');
        } catch (\Exception $e) {
            Log::error('Upload Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


    // ----------------------------------------------------------
    // UPDATE (PATCH Gambar)
    // ------------------------------------------------------
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|integer',
            'image' => 'nullable|image|max:4096'
        ]);

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $userJWT  = $user->supabase_jwt;

        if (!$userUUID || !$userJWT) {
            Log::warning("⚠️ Update gagal: missing UUID/JWT");
            return back()->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        // Header Supabase (WAJIB pakai JWT user)
        $authHeaders = [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => "Bearer {$userJWT}",
            'Content-Type'  => 'application/json',
        ];

        try {
            /*
        |--------------------------------------------------------------------------
        | STEP 1: Ambil data gambar lama (image_path + owner_id)
        |--------------------------------------------------------------------------
        */
            $imgUrl = env('SUPABASE_REST_URL') .
                "/images?select=image_path,user_id&id=eq.$id";

            $oldResponse = Http::withHeaders($authHeaders)
                ->withoutVerifying()
                ->get($imgUrl);

            $json = $oldResponse->json();
            if (empty($json)) {
                return back()->with('error', 'Gambar tidak ditemukan.');
            }

            $old = $json[0];

            // Pastikan gambar milik user
            if ($old['user_id'] !== $userUUID) {
                return back()->with('error', 'Anda tidak memiliki izin untuk mengedit gambar ini.');
            }

            $newImagePath = $old['image_path'];

            /*
        |--------------------------------------------------------------------------
        | STEP 2: Upload gambar baru jika ada
        |--------------------------------------------------------------------------
        */
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $mime = $file->getMimeType();

                $newName = time() . '_' . $userUUID . '_' .
                    preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $file->getClientOriginalName());

                $storageUrl = env('SUPABASE_URL') . "/storage/v1/object/images/$newName";

                $upload = Http::withHeaders([
                    'apikey'        => env('SUPABASE_ANON_KEY'),
                    'Authorization' => "Bearer {$userJWT}",
                    'Content-Type'  => $mime,
                ])
                    ->withoutVerifying()
                    ->withBody(file_get_contents($file), $mime)
                    ->put($storageUrl); // WAJIB PUT!

                if (!$upload->successful()) {
                    Log::error("❌ Upload gagal: " . $upload->body());
                    return back()->with('error', 'Upload gambar gagal.');
                }

                $newImagePath = $newName;
            }

            /*
        |--------------------------------------------------------------------------
        | STEP 3: Update data di Supabase REST API
        |--------------------------------------------------------------------------
        */
            $payload = [
                'title'       => $request->title,
                'description' => $request->description,
                'category_id' => (int)$request->category_id,
                'image_path'  => $newImagePath,
                'updated_at'  => now()->toIso8601String(),
            ];

            $updateUrl = env('SUPABASE_REST_URL') .
                "/images?id=eq.$id&user_id=eq.$userUUID";

            $updateRes = Http::withHeaders($authHeaders)
                ->withoutVerifying()
                ->patch($updateUrl, $payload);

            if (!$updateRes->successful()) {
                Log::error("❌ Update gagal: " . $updateRes->body());
                return back()->with('error', 'Gagal mengupdate data.');
            }

            /*
        |--------------------------------------------------------------------------
        | STEP 4: Bersihkan cache
        |--------------------------------------------------------------------------
        */
            Cache::forget('explore_images_list');
            Cache::forget('images_detail_' . $id);

            return redirect()->route('profile.show')
                ->with('success', 'Gambar berhasil diperbarui!');
        } catch (\Exception $e) {
            Log::error("💥 Update Error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat update.');
        }
    }



    public function edit($id)
    {
        return $this->showEditForm($id);
    }

    private function showEditForm($id)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $userJWT  = $user->supabase_jwt;

        if (!$userUUID || !$userJWT) {
            Log::warning("⚠️ Missing UUID/JWT saat akses edit image ID $id");
            return back()->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        // Headers (Wajib pakai JWT user untuk query images+RLS)
        $headers = [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => "Bearer {$userJWT}",
            'Content-Type'  => 'application/json',
        ];

        /*
    |--------------------------------------------------------------------------
    | STEP 1 – Ambil data image berdasarkan ID
    |--------------------------------------------------------------------------
    */
        $url = env('SUPABASE_REST_URL')
            . "/images?select=id,title,description,image_path,category_id,user_id"
            . "&id=eq.$id";

        $response = Http::withHeaders($headers)
            ->withoutVerifying()
            ->get($url);

        if (!$response->successful()) {
            Log::error("❌ Gagal ambil data image untuk edit: " . $response->body());
            return back()->with('error', 'Gagal mengambil data gambar.');
        }

        $json = $response->json();

        if (empty($json)) {
            return back()->with('error', 'Gambar tidak ditemukan.');
        }

        $image = $json[0];

        /*
    |--------------------------------------------------------------------------
    | STEP 2 – Cek kepemilikan image
    |--------------------------------------------------------------------------
    */
        if ($image['user_id'] !== $userUUID) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengedit karya ini.');
        }

        /*
    |--------------------------------------------------------------------------
    | STEP 3 – Tambahkan URL storage untuk preview
    |--------------------------------------------------------------------------
    */
        $image['image_url'] = $this->getStorageUrl() . $image['image_path'];

        /*
    |--------------------------------------------------------------------------
    | STEP 4 – Ambil semua kategori
    |--------------------------------------------------------------------------
    */
        $categoriesUrl = env('SUPABASE_REST_URL') . "/categories?select=id,name&order=name.asc";

        $catResponse = Http::withHeaders($headers)
            ->withoutVerifying()
            ->get($categoriesUrl);

        if (!$catResponse->successful()) {
            Log::warning("⚠️ Gagal ambil kategori: " . $catResponse->body());
            $categories = [];
        } else {
            $categories = $catResponse->json() ?? [];
        }

        /*
    |--------------------------------------------------------------------------
    | STEP 5 – Render halaman edit
    |--------------------------------------------------------------------------
    */
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

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $userJWT  = $user->supabase_jwt;

        if (!$userUUID || !$userJWT) {
            return back()->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        // Header wajib JWT user untuk lolos RLS Supabase
        $authHeaders = [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => "Bearer {$userJWT}",
            'Content-Type'  => 'application/json',
        ];

        try {
            /*
        |--------------------------------------------------------------------------
        | STEP 1 — Ambil Data Lama (image_path, user_id)
        |--------------------------------------------------------------------------
        */
            $getUrl = env('SUPABASE_REST_URL') .
                "/images?select=id,image_path,user_id&id=eq.$id";

            $oldRes = Http::withHeaders($authHeaders)
                ->withoutVerifying()
                ->get($getUrl);

            $json = $oldRes->json();

            if (empty($json)) {
                return back()->with('error', 'Gambar tidak ditemukan.');
            }

            $image = $json[0];

            // Validasi kepemilikan
            if ($image['user_id'] !== $userUUID) {
                return back()->with('error', 'Anda tidak memiliki izin untuk menghapus karya ini.');
            }

            $imagePath = $image['image_path'] ?? null;

            /*
        |--------------------------------------------------------------------------
        | STEP 2 — Delete di Supabase Database (REST API)
        |--------------------------------------------------------------------------
        */
            $deleteUrl = env('SUPABASE_REST_URL') . "/images?id=eq.$id&user_id=eq.$userUUID";

            $deleteRes = Http::withHeaders($authHeaders)
                ->withoutVerifying()
                ->delete($deleteUrl);

            if (!$deleteRes->successful()) {
                Log::error("❌ Gagal delete DB: " . $deleteRes->body());
                return back()->with('error', 'Gagal menghapus data gambar.');
            }

            /*
        |--------------------------------------------------------------------------
        | STEP 3 — Delete File di Supabase Storage (Hanya kalau file ada)
        |--------------------------------------------------------------------------
        */
            if ($imagePath) {
                $storageDeleteUrl = env('SUPABASE_URL') .
                    "/storage/v1/object/images/$imagePath";

                $delStorage = Http::withHeaders([
                    'apikey'        => env('SUPABASE_ANON_KEY'),
                    'Authorization' => "Bearer {$userJWT}",
                ])
                    ->withoutVerifying()
                    ->delete($storageDeleteUrl);

                if (!$delStorage->successful()) {
                    Log::warning("⚠️ Storage delete gagal: " . $delStorage->body());
                    // File gagal dihapus → tidak fatal, tetap sukses untuk user
                }
            }

            /*
        |--------------------------------------------------------------------------
        | STEP 4 — Bersihkan Cache
        |--------------------------------------------------------------------------
        */
            Cache::forget('explore_images_list');
            Cache::forget("images_detail_$id");

            return redirect()->route('gallery.index')->with('success', 'Gambar berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error("💥 Error delete: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus karya.');
        }
    }
}

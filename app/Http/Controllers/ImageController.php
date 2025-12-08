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
     * Mengembalikan header khusus untuk akses Admin/Anonim yang terjamin array.
     */
    private function getAdminHeaders()
    {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json'
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
    // READ (Galeri/Explore - INDEX)
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
    // ADMIN READ (Detail - adminShow) 🛑 KUNCI PERBAIKAN FINAL
    // ----------------------------------------------------------
    public function adminShow($id)
    {
        $cacheKey = 'admin_images_detail_' . $id;
        $supabase_storage_url = $this->getStorageUrl();

        $image = Cache::remember($cacheKey, 30, function () use ($id, $supabase_storage_url) {

            $headers = $this->getAdminHeaders(); 
            $supabase_url = env('SUPABASE_REST_URL');


            /* STEP 1A — Ambil data image dasar */
            $imageUrl =
                $supabase_url .
                "/images?select=id,title,description,image_path,created_at,category_id,user_id," .
                "users:user_id(name,email)," .
                "categories:category_id(name)" .
                "&id=eq.$id";

            $imageResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($imageUrl);

            if (!$imageResponse->successful() || empty($imageResponse->json())) {
                Log::error('❌ Gagal ambil data gambar admin: ' . $imageResponse->body());
                return null; 
            }
            $image = $imageResponse->json()[0];
            $image['image_url'] = $supabase_storage_url . $image['image_path'];


            /* STEP 1B — Ambil komentar */
            $commentsUrl =
                $supabase_url .
                "/comments?select=id,content,created_at,user_id,users:user_id(name)" .
                "&image_id=eq.$id" .
                "&order=created_at.desc";

            $commentsResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($commentsUrl);

            $image['comments'] = $commentsResponse->successful()
                ? $commentsResponse->json()
                : [];

            /* STEP 1C — Hitung likes */
            $likesUrl = $supabase_url . "/likes?select=count&image_id=eq.$id";

            $likesResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($likesUrl);

            $image['like_count'] = $likesResponse->successful()
                ? ($likesResponse->json()[0]['count'] ?? 0)
                : 0;

            return $image;
        });
        
        // 🛑 TIDAK ADA ABORT(404) DI SINI! View yang akan menampilkan pesan error jika $image null.
        
        if (is_null($image)) {
            // Jika null, kirim array kosong ke view agar proteksi Blade bekerja
            return view('admin.posts.show', ['image' => []]); 
        }
        
        // Normalisasi kategori
        $image['category_name'] = $image['categories']['name'] ?? 'Tidak ada kategori';

        // KUNCI: Kembalikan View Admin
        return view('admin.posts.show', compact('image'));
    }


    // ----------------------------------------------------------
    // READ (Detail - SHOW) - Digunakan untuk Public Route images/{id}
    // ----------------------------------------------------------
    public function show($id)
    {
        $cacheKey = 'images_detail_' . $id;
        $supabase_storage_url = $this->getStorageUrl();
        $userId = Auth::check() ? Auth::user()->supabase_uuid : null;

        // STEP 1 — Caching detail image
        $image = Cache::remember($cacheKey, 30, function () use ($id, $supabase_storage_url) {

            $headers = $this->getSupabaseHeaders();
            $supabase_url = env('SUPABASE_REST_URL');
            
            /* STEP 1A — Ambil data image dasar */
            $imageUrl =
                $supabase_url .
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
            $image['image_url'] = $supabase_storage_url . $image['image_path'];


            /* STEP 1B — Ambil komentar */
            $commentsUrl =
                $supabase_url .
                "/comments?select=id,content,created_at,user_id,users:user_id(name)" .
                "&image_id=eq.$id" .
                "&order=created_at.desc";

            $commentsResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($commentsUrl);

            $image['comments'] = $commentsResponse->successful()
                ? $commentsResponse->json()
                : [];

            /* STEP 1C — Hitung likes */
            $likesUrl = $supabase_url . "/likes?select=count&image_id=eq.$id";

            $likesResponse = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($likesUrl);

            $image['like_count'] = $likesResponse->successful()
                ? ($likesResponse->json()[0]['count'] ?? 0)
                : 0;

            return $image;
        });

        if (is_null($image)) {
            // Di public view, kita tetap abort 404
            abort(404);
        }

        /* STEP 2 & 3 — Cek like user & Normalisasi kategori */
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

        $image['category_name'] = $image['categories']['name'] ?? 'Tidak ada kategori';

        // KUNCI PUBLIC VIEW
        return view('images.show', compact('image'));
    }


    // ----------------------------------------------------------
    // PENGEMBANGAN LAINNYA
    // ----------------------------------------------------------
    public function create() { /* Implementasi di sini */ }
    public function store(Request $request) { /* Implementasi di sini */ }
    public function update(Request $request, $id) { /* Implementasi di sini */ }
    public function edit($id) { /* Implementasi di sini */ }
    public function destroy($id) { /* Implementasi di sini */ }
}
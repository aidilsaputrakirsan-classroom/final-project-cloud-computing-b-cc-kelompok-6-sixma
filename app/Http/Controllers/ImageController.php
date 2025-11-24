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
    // READ (Galeri/Explore - INDEX)
    // ----------------------------------------------------------
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $category = $request->category ?? null;
        
        $cacheKey = 'explore_images_list_' . md5(($search ?? '') . '_' . ($category ?? ''));
        $supabase_storage_url = $this->getStorageUrl();

        // 1. Coba ambil data dari cache selama 60 detik
        $images = Cache::remember($cacheKey, 60, function () use ($supabase_storage_url, $search, $category) {
            
            $headers = $this->getSupabaseHeaders();

            // QUERY DASAR (Mengambil semua join yang diperlukan untuk tampilan kartu)
            $url = env('SUPABASE_REST_URL') . '/images?select=id,title,image_path,category_id,created_at,categories:category_id(name),users:user_id(name)&order=created_at.desc';

            // 🔥 FIX #1 — SEARCH
            if (!empty($search)) {
                $encoded = urlencode('%' . $search . '%');
                $url .= "&or=(title.ilike.$encoded,description.ilike.$encoded)";
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

            // Memperbaiki pemetaan untuk image_url
            $images = array_map(function($image) use ($supabase_storage_url) {
                if (isset($image['image_path'])) {
                    $image['image_url'] = $supabase_storage_url . $image['image_path'];
                }
                if (isset($image['categories']) && is_array($image['categories'])) {
                    $image['category_name'] = $image['categories']['name'] ?? null;
                }
                $image['categories'] = ['name' => $image['categories']['name'] ?? null];
                
                return $image;
            }, $images);

            return $images; // Simpan hasil ke cache
        });

        return view('images.index', compact('images'));
    }

    // ----------------------------------------------------------
    // READ (Detail Gambar - SHOW) - LOGIKA PALING STABIL
    // ----------------------------------------------------------
    public function show($id)
    {
        $cacheKey = 'images_detail_' . $id;
        $supabase_storage_url = $this->getStorageUrl();

        $image = Cache::remember($cacheKey, 30, function () use ($id, $supabase_storage_url) {

            $headers = $this->getSupabaseHeaders(); // Selalu gunakan Anon Key untuk READ Publik

            // 1. Ambil data Gambar, Kategori, dan Owner (QUERY PALING SEDERHANA & STABIL)
            $selectQueryImage = '*, categories:category_id(name), users:user_id(name)'; 
            $urlImage = env('SUPABASE_REST_URL') . '/images?select=' . $selectQueryImage . '&id=eq.'.$id;

            $responseImage = Http::withHeaders($headers)->get($urlImage);
            
            if (!$responseImage->successful()) {
                Log::error('❌ Gagal Ambil Data Gambar (Anon Key). Status: ' . $responseImage->status());
                throw new \Exception('Failed to fetch image detail. RLS Policy is too strict for basic query. Status: ' . $responseImage->status());
            }
            
            $jsonImage = $responseImage->json();
            if (empty($jsonImage) || !is_array($jsonImage)) {
                 throw new \Exception('Image not found in Supabase.');
            }

            $image = $jsonImage[0]; 
            $image['comments'] = []; // Inisialisasi komentar
            
            // 2. Ambil data Komentar dan Owner Komentar (Request terpisah untuk stabilitas)
            $commentsUrl = env('SUPABASE_REST_URL') . '/comments?select=id,content,created_at,user_id,users:user_id(name)&image_id=eq.'.$id;
            $commentsResponse = Http::withHeaders($headers)->get($commentsUrl);
            
            if ($commentsResponse->successful() && !empty($commentsResponse->json())) {
                 $image['comments'] = $commentsResponse->json();
            } else {
                 Log::warning('⚠️ Gagal mengambil komentar secara terpisah. Status: ' . $commentsResponse->status());
            }

            // Tambahkan image_url
            if (isset($image['image_path'])) {
                $image['image_url'] = $supabase_storage_url . $image['image_path'];
            }
            
            return $image;
        });
        
        if (!is_array($image) || empty($image)) {
             return redirect()->route('gallery.index')->with('error', 'Gambar tidak ditemukan atau gagal dimuat.');
        }

        // Urutkan komentar di sisi Laravel
        if (isset($image['comments'])) {
             usort($image['comments'], function ($a, $b) {
                 return Carbon::parse($b['created_at'])->timestamp <=> Carbon::parse($a['created_at'])->timestamp;
             });
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
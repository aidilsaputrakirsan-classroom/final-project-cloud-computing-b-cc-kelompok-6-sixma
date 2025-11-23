<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Cache; // <-- Import Cache
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
    public function index()
    {
        $cacheKey = 'explore_images_list';
        $supabase_storage_url = $this->getStorageUrl();

        // 1. Coba ambil data dari cache selama 60 detik
        $images = Cache::remember($cacheKey, 60, function () use ($supabase_storage_url) {
            
            $headers = $this->getSupabaseHeaders();
            
            // OPTIMASI QUERY: Hanya ambil kolom yang dibutuhkan
            $url = env('SUPABASE_REST_URL') . '/images?select=id,title,image_path,created_at,categories(name),users:user_id(name)&order=created_at.desc';

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
        $userUUID = $user->supabase_uuid ?? null;
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
    // UPDATE (PATCH Gambar) - DENGAN DEBUGGING LOG
    // ----------------------------------------------------------
    public function update(Request $request, $id)
    {
        // ... (Logika update) ...

        try {
            // ... (Logika update) ...
            
            // Hapus cache setelah update
            Cache::forget('explore_images_list');
            Cache::forget('images_detail_' . $id); // Hapus cache detail spesifik
            
            return redirect()->route('profile.show')->with('success', 'Karya berhasil diperbarui!');

        } catch (\Exception $e) {
            // ... (Error handling) ...
        }
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
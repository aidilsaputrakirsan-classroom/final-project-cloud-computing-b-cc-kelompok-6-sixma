<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use App\Models\Image; // Meskipun tidak digunakan secara langsung untuk Supabase, ini baik untuk dokumentasi
use App\Models\Category; // Sama seperti Image
use Carbon\Carbon; // Untuk menghandle created_at jika diperlukan (tapi kita biarkan di view)

class ImageController extends Controller
{
    private function getSupabaseHeaders() {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ];
    }

    // ==========================================================
    // INDEX / EXPLORE
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
                $query .= '&category_id=eq.' . $request->category;
            }

            $query .= '&order=created_at.desc';

            $imagesResponse = Http::withHeaders($supabaseHeaders)->get($query);

            $images = [];

            if ($imagesResponse->successful()) {
                $imagesData = $imagesResponse->json();
                $baseStorageUrl = env('SUPABASE_URL') . '/storage/v1/object/public/images/';

                foreach ($imagesData as $image) {
                    $image['image_url'] = $baseStorageUrl . $image['image_path'];
                    $images[] = $image;
                }
            }

            $categoriesResponse = Http::withHeaders($supabaseHeaders)
                ->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');

            $categories = $categoriesResponse->json() ?? [];

            return view('images.index', compact('images', 'categories'));

        } catch (\Exception $e) {
            Log::error('Explore Error: ' . $e->getMessage());
            return view('images.index')->with('error', 'Gagal memuat galeri.');
        }
    }

    // ==========================================================
    // SHOW / DETAIL GAMBAR (PERBAIKAN DITAMBAHKAN DI SINI)
    // ==========================================================
    public function show($id)
    {
        $supabaseHeaders = $this->getSupabaseHeaders();

        try {
            // 1. Ambil Data Gambar
            $response = Http::withHeaders($supabaseHeaders)
                ->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=*');

            $data = $response->json();

            if (empty($data)) abort(404);

            $image = $data[0];
            $image['image_url'] = env('SUPABASE_URL') . '/storage/v1/object/public/images/' . $image['image_path'];

            // 2. Ambil Data Komentar TERKAIT
            // Mengambil semua kolom (*) dari tabel comments, serta 'name' dari tabel users (dijoin via user_id)
            $commentsResponse = Http::withHeaders($supabaseHeaders)
                ->get(env('SUPABASE_REST_URL') . '/comments?image_id=eq.' . $id . '&select=*,user:user_id(name)&order=created_at.asc');
            
            $comments = [];
            if ($commentsResponse->successful()) {
                $comments = $commentsResponse->json();
            }

            // 3. Kirim Gambar DAN Komentar ke View
            return view('images.show', compact('image', 'comments'));

        } catch (\Exception $e) {
            Log::error('Error loading image detail: ' . $e->getMessage());
            abort(404);
        }
    }

    // ==========================================================
    // CREATE (Form Upload)
    // ==========================================================
    public function create()
    {
        $headers = $this->getSupabaseHeaders();
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name&order=name.asc';

        $categories = Http::withHeaders($headers)->get($url)->json() ?? [];

        return view('images.create', compact('categories'));
    }

    // ==========================================================
    // STORE (UPLOAD GAMBAR)
    // ==========================================================
    public function store(Request $request)
    {
        $supabaseHeaders = [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json'
        ];

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login.');
        }

        // Validasi
        $request->validate([
            'image' => 'required|image|max:4096',
            'title' => 'required|string|max:255',
            'category_id' => 'required|numeric',
            'description' => 'nullable|string'
        ]);

        $userId = Auth::id();

        try {
            // -------------------------------------
            // 1. Upload ke Storage
            // -------------------------------------
            $file = $request->file('image');
            $mime = $file->getMimeType();

            $filename = time() . '_' . $userId . '_' . preg_replace(
                '/[^A-Za-z0-9\.\-_]/', '_', $file->getClientOriginalName()
            );

            // INI YANG BENAR (TANPA /public/)
            $uploadUrl = env('SUPABASE_URL') . '/storage/v1/object/images/' . $filename;

            $upload = Http::withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                    'Content-Type' => $mime
                ])
                ->withBody(file_get_contents($file), $mime)
                ->post($uploadUrl);

            if (!$upload->successful()) {
                return back()->with('error', 'Upload gagal: ' . $upload->body());
            }

            // -------------------------------------
            // 2. Simpan METADATA ke tabel images
            // -------------------------------------
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'image_path' => $filename,
                'category_id' => $request->category_id,
                'user_id' => $userId,
                'created_at' => now()->toIso8601String()
            ];

            $db = Http::withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=minimal'
                ])
                ->post(env('SUPABASE_REST_URL') . '/images', $data);

            if (!$db->successful()) {
                return back()->with('error', 'DB gagal: ' . $db->body());
            }

            return redirect()->route('gallery.index')->with('success', 'Gambar berhasil diupload!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


    // ==========================================================
    // EDIT
    // ==========================================================
    public function edit($id)
    {
        $headers = $this->getSupabaseHeaders();

        $requestUrl = env('SUPABASE_REST_URL') . '/images?id=eq.' . $id;

        $data = Http::withHeaders($headers)->get($requestUrl)->json();

        if (empty($data)) abort(404);

        $image = $data[0];
        $image['image_url'] = env('SUPABASE_URL') . '/storage/v1/object/public/images/' . $image['image_path'];

        $cats = Http::withHeaders($headers)
            ->get(env('SUPABASE_REST_URL') . '/categories?select=id,name')->json();

        return view('images.edit', ['image' => $image, 'categories' => $cats]);
    }

    // ==========================================================
    // UPDATE GAMBAR
    // ==========================================================
    public function update(Request $request, $id)
    {
        $headers = [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json'
        ];

        $userId = Auth::id();

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
        ]);

        try {
            // ambil data lama
            $oldData = Http::withHeaders($headers)
                ->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=image_path')
                ->json();

            if (empty($oldData)) {
                return back()->with('error', 'Data lama tidak ditemukan.');
            }

            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
                'category_id' => $request->category_id,
            ];

            // ============================
            // JIKA ADA GAMBAR BARU
            // ============================
            if ($request->hasFile('image')) {

                // HAPUS GAMBAR LAMA (FIXED)
                $oldFile = $oldData[0]['image_path'];

                Http::withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                ])->delete(env('SUPABASE_URL') . '/storage/v1/object/images/' . $oldFile);

                // UPLOAD BARU
                $file = $request->file('image');
                $mime = $file->getMimeType();
                $filename = time() . '_' . $userId . '_' . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $file->getClientOriginalName());

                $uploadUrl = env('SUPABASE_URL') . '/storage/v1/object/images/' . $filename;

                $upload = Http::withHeaders([
                            'apikey' => env('SUPABASE_ANON_KEY'),
                            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                            'Content-Type' => $mime
                        ])
                        ->withBody(file_get_contents($file), $mime)
                        ->post($uploadUrl);

                if (!$upload->successful()) {
                    return back()->with('error', 'Upload gagal: ' . $upload->body());
                }

                $updateData['image_path'] = $filename;
            }

            // ============================
            // UPDATE DATABASE
            // ============================
            $update = Http::withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=minimal'
                ])
                ->patch(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id, $updateData);

            if (!$update->successful()) {
                return back()->with('error', 'DB Update gagal: ' . $update->body());
            }

            return redirect()->route('images.show', $id)->with('success', 'Berhasil diperbarui!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }


    // ==========================================================
    // DELETE
    // ==========================================================
    public function destroy($id)
    {
        $headers = $this->getSupabaseHeaders();

        try {
            $old = Http::withHeaders($headers)
                ->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=image_path')
                ->json();

            if (!empty($old)) {
                Http::withHeaders($headers)
                    ->delete(env('SUPABASE_URL') . '/storage/v1/object/public/images/' . $old[0]['image_path']);
            }

            Http::withHeaders($headers)
                ->delete(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id);

            return redirect()->route('gallery.index')->with('success', 'Berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error($e);
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }
}
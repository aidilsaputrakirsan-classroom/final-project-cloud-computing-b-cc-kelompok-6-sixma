<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{
    // ==========================================================
    // 🟢 FITUR READ GAMBAR (DAFFA) - GALERI PUBLIK
    // ==========================================================
    
    // 🟢 Menampilkan semua gambar (Galeri Utama)
    public function index()
    {
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?select=*');

        $images = $response->json() ?? [];
        
        // Output WAJIB return VIEW untuk Galeri Publik
        return view('images.index', compact('images')); 
    }

    // 🟢 Menampilkan detail gambar tunggal
    public function show($id)
    {
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=*');

        $image = $response->json()[0] ?? null;

        if (!$image) {
            abort(404); // Not Found
        }
        
        // Output WAJIB return VIEW untuk detail gambar
        return view('images.show', compact('image')); 
    }

    // ==========================================================
    // 🟢 FITUR CREATE GAMBAR (KIRANA)
    // ==========================================================

    // 🟢 Menampilkan form unggah gambar
    public function create()
    {
        // Mengambil kategori untuk dropdown
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name';
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get($url);
        
        $categories = $response->json();
        if (!is_array($categories)) {
            $categories = [];
        }

        return view('images.create-image', compact('categories'));
    }

    // 🟢 Menyimpan hasil unggahan ke Supabase
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable',
            'location' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload ke Supabase Storage
        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();

        $upload = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/octet-stream',
        ])->put(
            env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $filename,
            file_get_contents($file)
        );

        if (!$upload->successful()) {
            return back()->with('error', 'Gagal mengunggah gambar ke Supabase Storage.');
        }

        // Simpan metadata gambar ke Supabase Database
        $insert = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post(env('SUPABASE_REST_URL') . '/images', [
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'location' => $request->location,
            'image_path' => $filename,
        ]);

        if (!$insert->successful()) {
            return back()->with('error', 'Gagal menyimpan data ke Supabase.');
        }

        return redirect()->route('gallery.index')->with('success', '✨ Gambar berhasil diunggah!');
    }

    // ==========================================================
    // 🟢 FITUR UPDATE GAMBAR (RIA - ANDA)
    // ==========================================================

    // 🟢 Menampilkan form edit gambar
    public function edit($id)
    {
        // 1. Ambil data gambar yang akan diedit dari Supabase
        $imageResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=*');
        
        $image = $imageResponse->json();
        
        if (!$imageResponse->successful() || empty($image)) {
            return back()->with('error', 'Gambar tidak ditemukan atau tidak dapat dimuat.');
        }
        $image = $image[0];

        // 2. Otorisasi: Hanya pemilik (atau Admin) yang bisa edit
        // PENTING: Jika menggunakan kolom 'role' untuk admin, tambahkan logic di sini
        if (Auth::id() !== $image['user_id']) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengedit karya ini.');
        }

        // 3. Ambil daftar kategori
        $categoriesResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');

        $categories = $categoriesResponse->json() ?? [];
        
        // Kirim data gambar dan kategori ke view
        return view('images.edit-image', compact('image', 'categories'));
    }

    // 🟢 Memperbarui data dan file gambar di Supabase
    public function update(Request $request, $id)
    {
        // PENTING: Gunakan UpdateImageRequest Anda di sini untuk validasi, 
        // namun untuk saat ini, kita gunakan Request standar
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable',
            'location' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', 
        ]);

        // 1. Ambil data gambar lama (untuk cek kepemilikan dan path file lama)
        $oldImageResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=user_id,image_path');
        
        $oldImage = $oldImageResponse->json()[0] ?? null;

        // Otorisasi (cek kepemilikan)
        if (!$oldImage || Auth::id() !== $oldImage['user_id']) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah karya ini.');
        }

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'location' => $request->location,
        ];
        
        // 2. Handle Upload File Baru (Jika ada file baru)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Upload file baru ke Supabase Storage
            $upload = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/octet-stream',
            ])->put(
                env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $filename,
                file_get_contents($file)
            );

            if (!$upload->successful()) {
                 return back()->with('error', 'Gagal mengunggah gambar baru ke Supabase Storage.');
            }

            // Hapus File Lama dari Supabase Storage (Opsional, tapi disarankan)
            // Http::withHeaders([...])->delete(env('SUPABASE_STORAGE_URL') . '/object/images/' . $oldImage['image_path']);
            
            // Update path baru
            $updateData['image_path'] = $filename;
        }

        // 3. Perbarui metadata di Supabase Database
        $update = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation', 
        ])->patch(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id, $updateData);

        if (!$update->successful()) {
             return back()->with('error', 'Gagal memperbarui data di Supabase.');
        }

        return redirect()->route('gallery.index')->with('success', '✅ Karya berhasil diperbarui!');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{
    // 🟢 Menampilkan semua gambar
    public function index()
    {
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?select=*');

        $images = $response->json() ?? [];
        return view('images.index', compact('images'));
    }

    // 🟢 Menampilkan form unggah gambar
    public function create()
    {
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name';
        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get($url);

        // 🔍 Debug hasil respons Supabase
        if (!$response->successful()) {
            dd([
                'status' => $response->status(),
                'error' => $response->body(),
                'url' => $url,
            ]);
        }

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

        // Simpan metadata gambar
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
}

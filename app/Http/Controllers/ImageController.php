<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 
use App\Http\Requests\UpdateImageRequest; 

class ImageController extends Controller
{
    // ==========================================================
    // 🟢 READ GAMBAR & FILTER/SEARCH (DAFFA & KIRANA)
    // ==========================================================
    
    public function index(Request $request)
    {
        // ... (Logic READ yang sudah disepakati) ...
        $queryUrl = env('SUPABASE_REST_URL') . '/images?select=*,user:user_id(name)&order=created_at.desc';
        $filters = [];
        // [Logic FILTER/SEARCH Kirana akan masuk di sini]
        if (!empty($filters)) { $queryUrl .= '&' . implode('&', $filters); }

        $response = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get($queryUrl);
        $images = $response->json() ?? [];
        $categoriesResponse = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');
        $categories = $categoriesResponse->json() ?? [];

        return view('images.index', compact('images', 'categories')); 
    }

    public function show($id)
    {
        $response = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=*,user:user_id(name)'); 
        $image = $response->json()[0] ?? null;

        if (!$image) { abort(404); }
        return view('images.show', compact('image')); 
    }

    // ==========================================================
    // 🟢 CREATE GAMBAR (KIRANA)
    // ==========================================================
    public function create()
    {
        $url = env('SUPABASE_REST_URL') . '/categories?select=id,name';
        $response = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get($url);
        $categories = $response->json() ?? [];
        return view('images.create-image', compact('categories'));
    }

    public function store(Request $request)
    {
        // Logic CREATE dan Upload
        $request->validate(['title' => 'required|string|max:255', 'image' => 'required|image|mimes:jpg,jpeg,png|max:2048']);
        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $upload = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'), 'Content-Type' => 'application/octet-stream',])->put(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $filename, file_get_contents($file));
        
        if (!$upload->successful()) { Log::error('Supabase Upload Error: ' . $upload->body()); return back()->with('error', 'Gagal mengunggah gambar ke Supabase Storage.'); }

        $insert = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'), 'Content-Type' => 'application/json', 'Prefer' => 'return=representation',])->post(env('SUPABASE_REST_URL') . '/images', ['user_id' => Auth::id(), 'title' => $request->title, 'location' => $request->location, 'image_path' => $filename, 'category_id' => $request->category_id]);
        
        if (!$insert->successful()) { Log::error('Supabase Insert Error: ' . $insert->body()); return back()->with('error', 'Gagal menyimpan data ke Supabase.'); }

        return redirect()->route('gallery.index')->with('success', '✨ Gambar berhasil diunggah!');
    }

    // ==========================================================
    // 🟢 UPDATE GAMBAR (RIA - ANDA)
    // ==========================================================
    
    public function edit($id)
    {
        $imageResponse = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=*,user_id');
        $image = $imageResponse->json()[0] ?? null;

        if (!$image) { abort(404); }
        if (Auth::id() !== $image['user_id']) { return back()->with('error', 'Anda tidak memiliki izin untuk mengedit karya ini.'); } // Otorisasi
        
        $categoriesResponse = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get(env('SUPABASE_REST_URL') . '/categories?select=id,name');
        $categories = $categoriesResponse->json() ?? [];
        return view('images.edit-image', compact('image', 'categories')); 
    }

    public function update(UpdateImageRequest $request, $id)
    {
        $oldImageResponse = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=user_id,image_path');
        $oldImage = $oldImageResponse->json()[0] ?? null;
        if (!$oldImage || Auth::id() !== $oldImage['user_id']) { return back()->with('error', 'Anda tidak memiliki izin untuk memperbarui karya ini.'); } // Otorisasi

        $imagePathToUpdate = $oldImage['image_path'];
        $updateData = $request->except(['_token', '_method', 'image']); // Data metadata

        if ($request->hasFile('image')) {
            // Logic Upload, Delete Lama, dan Set Path Baru
            $file = $request->file('image'); $filename = time() . '_' . $file->getClientOriginalName();
            $upload = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'), 'Content-Type' => 'application/octet-stream',])->put(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $filename, file_get_contents($file));
            if (!$upload->successful()) { return back()->with('error', 'Gagal mengunggah gambar baru ke Supabase Storage.'); }
            
            if ($oldImage['image_path']) { Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->delete(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $oldImage['image_path']); }
            $updateData['image_path'] = $filename;
        } else {
            $updateData['image_path'] = $imagePathToUpdate; // Pertahankan path lama jika tidak ada file baru
        }

        $updateDb = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'), 'Content-Type' => 'application/json',])->patch(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id, $updateData);

        if (!$updateDb->successful()) { Log::error('Supabase Update DB Error: ' . $updateDb->body()); return back()->with('error', 'Gagal memperbarui data di Supabase.'); }

        return redirect()->route('images.show', $id)->with('success', '✅ Karya berhasil diperbarui!');
    }

    // ==========================================================
    // 🟢 DELETE GAMBAR (DAFFA)
    // ==========================================================
    public function destroy($id)
    {
        $imageResponse = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=user_id,image_path');
        $image = $imageResponse->json()[0] ?? null;

        if (!$image || Auth::id() !== $image['user_id']) { return back()->with('error', 'Anda tidak memiliki izin untuk menghapus karya ini.'); } // Otorisasi

        $deleteStorage = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->delete(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $image['image_path']);
        
        if (!$deleteStorage->successful() && $deleteStorage->status() !== 204) { Log::error('Supabase Delete Storage Error: ' . $deleteStorage->body()); return back()->with('error', 'Gagal menghapus file dari Storage.'); }

        $deleteDb = Http::withHeaders(['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),])->delete(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id);

        if (!$deleteDb->successful()) { Log::error('Supabase Delete DB Error: ' . $deleteDb->body()); return back()->with('error', 'Gagal menghapus data di database.'); }

        return redirect()->route('gallery.index')->with('success', '🗑️ Karya berhasil dihapus!');
    }
}
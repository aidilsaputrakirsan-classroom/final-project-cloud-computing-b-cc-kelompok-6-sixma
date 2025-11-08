// app/Http/Controllers/ImageController.php
// (Pastikan method index(), show(), edit(), update(), destroy() juga ada di file ini)

// 🟢 Menampilkan form unggah gambar
public function create()
{
    // Mengambil kategori untuk dropdown dari Supabase
    $url = env('SUPABASE_REST_URL') . '/categories?select=id,name';
    $response = Http::withHeaders([
        'apikey' => env('SUPABASE_ANON_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
    ])->get($url);

    $categories = $response->json() ?? [];

    return view('images.create-image', compact('categories'));
}

// 🟢 Menyimpan hasil unggahan ke Supabase
public function store(Request $request)
{
    // 1. Validasi Input
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'category_id' => 'required|integer', 
        'location' => 'required|string|max:255',
        'image' => 'required|image|mimes:jpg,jpeg,png|max:2048', // Max 2MB
    ]);

    // 2. Upload ke Supabase Storage
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
        Log::error('Supabase Upload Error: ' . $upload->body());
        return back()->with('error', 'Gagal mengunggah gambar ke Supabase Storage.');
    }

    // 3. Simpan metadata gambar ke Supabase Database
    $insert = Http::withHeaders([
        'apikey' => env('SUPABASE_ANON_KEY'),
        'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        'Content-Type' => 'application/json',
        'Prefer' => 'return=representation',
    ])->post(env('SUPABASE_REST_URL') . '/images', [
        'user_id' => Auth::id(), // ID user yang sedang login
        'title' => $request->title,
        'description' => $request->description,
        'category_id' => $request->category_id,
        'location' => $request->location,
        'image_path' => $filename,
    ]);

    if (!$insert->successful()) {
        Log::error('Supabase Insert Error: ' . $insert->body());
        return back()->with('error', 'Gagal menyimpan data ke Supabase.');
    }

    return redirect()->route('gallery.index')->with('success', '✨ Gambar berhasil diunggah!');
}
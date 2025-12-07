<?php

namespace App\Http\Controllers;

use App\Facades\Supabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ImageController extends Controller
{
    /* ============================================================
     | Helper Methods
     ============================================================ */
    private function userUUID()
    {
        return Auth::user()->supabase_uuid ?? null;
    }

    private function userJWT()
    {
        return Auth::user()->supabase_jwt ?? null;
    }

    private function storageUrl()
    {
        return env('SUPABASE_URL') . '/storage/v1/object/public/images/';
    }

    /* ============================================================
     | INDEX (Explore)
     ============================================================ */
    public function index(Request $request)
    {
        $search = trim($request->search ?? '');
        $category = $request->category ?? null;

        $cacheKey = "explore_images_" . md5($search . '_' . $category);

        $images = Cache::remember($cacheKey, 60, function () use ($search, $category) {

            $query = Supabase::table('images')
                ->select('id,title,image_path,category_id,created_at,user_id')
                ->order('created_at', 'desc');

            if ($search) {
                $query->filterRaw('or', "(title.ilike.%$search%,description.ilike.%$search%)");
            }

            if ($category) {
                $query->eq('category_id', $category);
            }

            $images = $query->get() ?? [];

            foreach ($images as &$img) {
                $img['image_url'] = $this->storageUrl() . $img['image_path'];
            }

            return $images;
        });

        /* USER LIKE CHECK */
        $userId = Auth::check() ? $this->userUUID() : null;

        if ($userId && $images) {
            $ids = implode(',', array_column($images, 'id'));

            $liked = Supabase::table('likes')
                ->select('image_id')
                ->filterRaw('image_id', "in.($ids)")
                ->eq('user_id', $userId)
                ->get() ?? [];

            $likedIds = array_column($liked, 'image_id');

            foreach ($images as &$img) {
                $img['is_liked'] = in_array($img['id'], $likedIds);
            }
        }

        return view('images.index', compact('images'));
    }

    /* ============================================================
     | SHOW (Detail View)
     ============================================================ */
    public function show($id)
    {
        $cacheKey = "image_detail_$id";

        $image = Cache::remember($cacheKey, 30, function () use ($id) {

            $img = Supabase::table('images')
                ->select('id,title,description,image_path,created_at,category_id,user_id')
                ->eq('id', $id)
                ->first();

            if (!$img) return null;

            $img['image_url'] = $this->storageUrl() . $img['image_path'];

            $img['comments'] = Supabase::table('comments')
                ->select('id,content,user_id,created_at')
                ->eq('image_id', $id)
                ->order('created_at', 'desc')
                ->get() ?? [];

            $likeCount = Supabase::table('likes')
                ->select('count')
                ->eq('image_id', $id)
                ->get();

            $img['like_count'] = $likeCount[0]['count'] ?? 0;

            return $img;
        });

        if (!$image) abort(404);

        /* USER LIKE CHECK */
        $userId = Auth::check() ? $this->userUUID() : null;
        $image['is_liked'] = false;

        if ($userId) {
            $liked = Supabase::table('likes')
                ->select('id')
                ->eq('image_id', $id)
                ->eq('user_id', $userId)
                ->first();

            $image['is_liked'] = $liked ? true : false;
        }

        return view('images.show', compact('image'));
    }

    /* ============================================================
     | CREATE FORM
     ============================================================ */
    public function create()
    {
        $categories = Cache::remember('categories_list', 300, function () {
            return Supabase::table('categories')
                ->select('id,name')
                ->order('name')
                ->get() ?? [];
        });

        return view('images.create', compact('categories'));
    }

    /* ============================================================
     | STORE (Upload)
     ============================================================ */
    public function store(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $request->validate([
            'title'       => 'required|max:255',
            'category_id' => 'required|integer|min:1',
            'image'       => 'required|image|max:4096',
            'description' => 'nullable|string',
        ]);

        $userUUID = $this->userUUID();
        $jwt      = $this->userJWT();

        $file = $request->image;
        $mime = $file->getMimeType();
        $filename = time() . "_{$userUUID}_" . preg_replace(
            '/[^A-Za-z0-9\.\-_]/',
            '_',
            $file->getClientOriginalName()
        );

        /* UPLOAD STORAGE */
        $upload = Supabase::uploadFile(
            'images',
            $filename,
            file_get_contents($file),
            $mime,
            $jwt
        );

        if (!$upload->successful()) {
            Log::error("Storage upload error: " . $upload->body());
            return back()->with('error', 'Upload gagal.');
        }

       /* INSERT DB (WAJIB pakai JWT agar RLS menerima) */
$insert = Supabase::auth($jwt)
    ->table('images')
    ->insert([
        'title'       => $request->title,
        'description' => $request->description,
        'location'    => $request->location ?? null, // karena tabel punya kolom ini
        'image_path'  => $filename,
        'category_id' => (int) $request->category_id,
        'user_id'     => $userUUID,
        'created_at'  => now()->toIso8601String(),
        'updated_at'  => now()->toIso8601String(),
    ]);

if (!$insert || isset($insert['error'])) {
    Log::error("Insert Error: " . json_encode($insert));
    return back()->with('error', 'Gagal menyimpan data ke database.');
}
    }
    /* ============================================================
     | EDIT FORM
     ============================================================ */
    public function edit($id)
    {
        $uuid = $this->userUUID();

        $image = Supabase::table('images')
            ->select('id,title,description,image_path,category_id,user_id')
            ->eq('id', $id)
            ->first();

        if (!$image || $image['user_id'] !== $uuid) {
            return back()->with('error', 'Tidak memiliki izin.');
        }

        $image['image_url'] = $this->storageUrl() . $image['image_path'];

        $categories = Cache::remember('categories_list', 300, function () {
            return Supabase::table('categories')
                ->select('id,name')
                ->order('name')
                ->get();
        });

        return view('images.edit', compact('image', 'categories'));
    }

    /* ============================================================
     | UPDATE
     ============================================================ */
    public function update(Request $request, $id)
    {
        $uuid = $this->userUUID();
        $jwt  = $this->userJWT();

        $request->validate([
            'title'       => 'required',
            'category_id' => 'required|integer',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:4096',
        ]);

        $old = Supabase::table('images')
            ->select('image_path,user_id')
            ->eq('id', $id)
            ->first();

        if (!$old || $old['user_id'] !== $uuid) {
            return back()->with('error', 'Tidak memiliki izin.');
        }

        $imagePath = $old['image_path'];

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $mime = $file->getMimeType();
            $filename = time() . "_{$uuid}_" . preg_replace(
                '/[^A-Za-z0-9\.\-_]/',
                '_',
                $file->getClientOriginalName()
            );

            $upload = Supabase::uploadFile('images', $filename, file_get_contents($file), $mime, $jwt);

            if ($upload->successful()) {
                $imagePath = $filename;
            }
        }

        Supabase::auth($jwt)
    ->table('images')
    ->eq('id', $id)
    ->update([
        'title' => $request->title,
        'description' => $request->description,
        'location' => $request->location ?? null,
        'category_id' => (int) $request->category_id,
        'image_path' => $imagePath,
        'updated_at' => now()->toIso8601String(),
    ]);


        Cache::forget("image_detail_$id");

        return redirect()->route('profile.show')->with('success', 'Berhasil diupdate!');
    }

    /* ============================================================
     | DELETE
     ============================================================ */
    public function destroy($id)
    {
        $uuid = $this->userUUID();
        $jwt  = $this->userJWT();

        $old = Supabase::table('images')
            ->select('image_path,user_id')
            ->eq('id', $id)
            ->first();

        if (!$old || $old['user_id'] !== $uuid) {
            return back()->with('error', 'Tidak memiliki izin.');
        }

        Supabase::table('images')->delete($id);
        Supabase::deleteFile('images', $old['image_path'], $jwt);

        Cache::forget("image_detail_$id");

        return redirect()->route('gallery.index')->with('success', 'Berhasil dihapus!');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * 🟢 Menampilkan halaman profil user dan karya miliknya.
     */
    public function showProfile()
    {
        // Pastikan user sudah login (middleware 'auth' menangani juga)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userId = $user->id;
        $images = [];

        try {
            // 🔹 Ambil semua karya milik user login
            $query = env('SUPABASE_REST_URL') . '/images?select=*'
                   . '&user_id=eq.' . $userId
                   . '&order=created_at.desc';

            $response = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            ])->get($query);

            if ($response->successful()) {
                $imagesData = $response->json();

                if (is_array($imagesData)) {
                    $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';

                    foreach ($imagesData as $image) {
                        $image['image_url'] = $baseStorageUrl . ($image['image_path'] ?? '');
                        $images[] = $image;
                    }
                }
            } else {
                Log::warning('⚠️ Supabase gagal ambil data gambar', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            // Kirim data ke view
            return view('profile.index', [
                'user' => $user,
                'images' => $images,
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Error di showProfile(): ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat profil.');
        }
    }
}

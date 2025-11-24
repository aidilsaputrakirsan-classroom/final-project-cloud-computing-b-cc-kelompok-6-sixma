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
        // Pastikan user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // ✅ FIX KRITIS: Ambil UUID Supabase yang tersimpan di database lokal
        $userUUID = $user->supabase_uuid ?? null;
        
        $images = [];

        if (empty($userUUID)) {
             Log::warning('UUID Supabase user tidak ditemukan untuk user ID lokal: ' . $user->id);
             return view('profile.index', [
                'user' => $user,
                'images' => $images, // Kirim array kosong
            ])->with('warning', 'UUID pengguna tidak ditemukan. Sesi mungkin perlu diperbarui.');
        }

        $supabaseHeaders = [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ];


        try {
            // 🔹 Ambil semua karya milik user login, filter menggunakan UUID
            $query = env('SUPABASE_REST_URL') . '/images?select=*'
                     // ✅ FIX: Filter berdasarkan UUID
                    . '&user_id=eq.' . $userUUID 
                    . '&order=created_at.desc';

            $response = Http::withHeaders($supabaseHeaders)->get($query);

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
                Log::warning('⚠️ Supabase gagal ambil data gambar Profile', [
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
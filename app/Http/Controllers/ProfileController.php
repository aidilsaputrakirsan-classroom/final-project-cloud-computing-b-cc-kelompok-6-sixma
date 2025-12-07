<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function showProfile()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $userJWT  = $user->supabase_jwt;

        if (!$userUUID || !$userJWT) {
            Log::warning("⚠️ Missing UUID/JWT untuk user {$user->id}");
            return view('profile.index', [
                'user' => $user,
                'images' => []
            ])->with('warning', 'Token sesi kadaluarsa. Silakan login ulang.');
        }

        $headers = [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => "Bearer {$userJWT}",  // <<< FIX PENTING
        ];

        try {
            $url = env('SUPABASE_REST_URL') .
                "/images?select=*&user_id=eq.$userUUID&order=created_at.desc";

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->get($url);

            if (!$response->successful()) {
                Log::warning("⚠️ Gagal load images profile: " . $response->body());
                return view('profile.index', [
                    'user' => $user,
                    'images' => []
                ])->with('error', 'Gagal memuat gambar profil.');
            }

            $images = $response->json() ?? [];

            foreach ($images as &$img) {
                $img['image_url'] =
                    env('SUPABASE_URL') . '/storage/v1/object/public/images/' . $img['image_path'];
            }

            return view('profile.index', compact('user', 'images'));
        } catch (\Exception $e) {
            Log::error("💥 showProfile Error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat profil.');
        }
    }
}

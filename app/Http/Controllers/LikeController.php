<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    private function getSupabaseHeaders() {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ];
    }

    /**
     * Toggle like/unlike untuk gambar
     */
    public function toggle(Request $request, $imageId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();

        try {
            // Cek apakah sudah like
            $existingLike = Http::withHeaders($this->getSupabaseHeaders())
                ->get(env('SUPABASE_REST_URL') . '/likes?user_id=eq.' . $userId . '&image_id=eq.' . $imageId)
                ->json();

            if (!empty($existingLike)) {
                // Unlike: hapus like
                Http::withHeaders($this->getSupabaseHeaders())
                    ->delete(env('SUPABASE_REST_URL') . '/likes?user_id=eq.' . $userId . '&image_id=eq.' . $imageId);

                return response()->json(['liked' => false, 'message' => 'Unlike berhasil']);
            } else {
                // Like: tambah like
                $data = [
                    'user_id' => $userId,
                    'image_id' => $imageId,
                    'created_at' => now()->toIso8601String()
                ];

                Http::withHeaders(array_merge($this->getSupabaseHeaders(), [
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=minimal'
                ]))->post(env('SUPABASE_REST_URL') . '/likes', $data);

                return response()->json(['liked' => true, 'message' => 'Like berhasil']);
            }

        } catch (\Exception $e) {
            Log::error('Like toggle error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    /**
     * Cek status like untuk gambar
     */
    public function check($imageId)
    {
        if (!Auth::check()) {
            return response()->json(['liked' => false]);
        }

        $userId = Auth::id();

        try {
            $existingLike = Http::withHeaders($this->getSupabaseHeaders())
                ->get(env('SUPABASE_REST_URL') . '/likes?user_id=eq.' . $userId . '&image_id=eq.' . $imageId)
                ->json();

            return response()->json(['liked' => !empty($existingLike)]);

        } catch (\Exception $e) {
            return response()->json(['liked' => false]);
        }
    }
}

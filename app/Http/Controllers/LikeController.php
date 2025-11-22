<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Like;

class LikeController extends Controller
{

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
            $existingLike = Like::where('user_id', $userId)->where('image_id', $imageId)->first();

            if ($existingLike) {
                // Unlike: hapus like
                $existingLike->delete();

                return response()->json(['liked' => false, 'message' => 'Unlike berhasil']);
            } else {
                // Like: tambah like
                Like::create([
                    'user_id' => $userId,
                    'image_id' => $imageId,
                ]);

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

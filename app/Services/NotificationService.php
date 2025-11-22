<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotificationService
{
    public static function unreadCount($userId)
    {
        // 1. Ambil semua gambar milik user
        $images = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer '.env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . "/images?user_id=eq.$userId&select=id")
          ->json();

        if (!is_array($images) || count($images) === 0) return 0;

        $imageIds = array_column($images, 'id');
        $imageFilter = implode(',', $imageIds);

        // 2. Hitung komentar baru
        $comments = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer '.env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . "/comments?image_id=in.($imageFilter)")
          ->json();

        if (!is_array($comments)) return 0;

        return count($comments);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $userId = Auth::id(); // ID internal Laravel

        // Ambil semua images milik user ini (tabel public.images)
        $images = Http::withHeaders([
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer '.env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . "/images?user_id=eq.$userId&select=id")
          ->json();

        if (!is_array($images) || count($images) === 0) {
            return view('profile.notifications', ['notifications' => []]);
        }

        // Ambil ID gambar
        $imageIds = array_column($images, 'id');
        $imageFilter = implode(',', $imageIds);

        // Ambil komentar (join user)
        $comments = Http::withHeaders([
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer '.env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . "/comments?image_id=in.($imageFilter)&select=id,content,image_id,user_id,created_at,users(name)&order=created_at.desc")
          ->json();

        return view('profile.notifications', [
            'notifications' => $comments ?? []
        ]);
    }
}

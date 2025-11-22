<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Buat notifikasi baru
     */
    public static function create($userId, $actorId, $imageId, $type)
    {
        // Pesan notifikasi berdasarkan jenis
        $message = match ($type) {
            'comment' => 'Seseorang mengomentari fotomu.',
            'save'    => 'Seseorang menyimpan fotomu.',
            default   => 'Aktivitas baru pada fotomu.'
        };

        // Kirim ke tabel notifications di Supabase
        return Http::withHeaders([
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ])->post(env('SUPABASE_REST_URL') . '/notifications', [
            'user_id'  => $userId,
            'actor_id' => $actorId,
            'image_id' => $imageId,
            'type'     => $type,
            'message'  => $message
        ]);
    }

    /**
     * Hitung jumlah notifikasi yang belum dibaca
     */
    public static function unreadCount($userId)
    {
        $response = Http::withHeaders([
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ])->get(env('SUPABASE_REST_URL') . "/notifications?user_id=eq.$userId&is_read=eq.false");

        return count($response->json() ?? []);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User; // Digunakan untuk mencari pemilik karya

/**
 * Service Class untuk menangani logika pembuatan notifikasi dan penghitungan.
 */
class NotificationService
{
    /**
     * Membuat notifikasi baru dan menyimpannya ke tabel 'notifications' Supabase.
     * * @param string $type Tipe notifikasi (comment, like, report).
     * @param string $performerId UUID pengguna yang melakukan aksi (misal: pengirim komentar).
     * @param string $image_id ID karya yang dikenai aksi.
     * @param string $recipientId UUID pengguna yang menerima notifikasi (pemilik karya).
     */
    public static function create($type, $performerId, $imageId, $recipientId)
    {
        // 1. Dapatkan informasi detail karya untuk pesan notifikasi
        $headers = [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ];
        $imageUrl = env('SUPABASE_REST_URL') . '/images?select=title&id=eq.'.$imageId;
        $imageResponse = Http::withHeaders($headers)->get($imageUrl);
        
        $imageTitle = $imageResponse->successful() ? ($imageResponse->json()[0]['title'] ?? 'Karya') : 'Karya';
        
        // 2. Tentukan pesan
        $message = match ($type) {
            'comment' => "Seseorang mengomentari karyamu: '{$imageTitle}'.",
            'like' => "Seseorang menyukai karyamu: '{$imageTitle}'.",
            'report' => "Karyamu: '{$imageTitle}' dilaporkan oleh pengguna.",
            default => "Aktivitas baru pada karyamu: '{$imageTitle}'.",
        };

        // 3. Data untuk disimpan
        $data = [
            'recipient_id' => $recipientId,
            'performer_id' => $performerId,
            'image_id' => $imageId,
            'type' => $type,
            'message' => $message,
            'is_read' => false,
            'created_at' => now()->toIso8601String()
        ];
        
        // 4. Kirim data ke Supabase (Menggunakan Anon Key, asalkan RLS INSERT diizinkan)
        $databaseUrl = env('SUPABASE_REST_URL') . '/notifications';
        $createNotif = Http::withHeaders(array_merge($headers, ['Prefer' => 'return=minimal']))
            ->post($databaseUrl, $data);

        if (!$createNotif->successful()) {
            Log::error('❌ Gagal menyimpan notifikasi ke Supabase:', ['status' => $createNotif->status(), 'error' => $createNotif->body()]);
        } else {
            Log::info("✅ Notifikasi {$type} berhasil dibuat untuk User: {$recipientId}");
        }

        return $message;
    }
    
    /**
     * Menghitung jumlah notifikasi yang belum dibaca (Unread Count)
     * ... (Metode ini tetap sama)
     */
    public static function unreadCount()
    {
        if (!Auth::check()) {
            return 0; // Jika belum login, hitungan 0
        }

        $userUUID = Auth::user()->supabase_uuid ?? null;

        if (!$userUUID) {
            return 0;
        }

        try {
            // Kita akan menggunakan header Anon Key untuk READ
            $headers = [
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
            ];

            // URL untuk menghitung notifikasi yang belum dibaca (is_read=eq.false)
            // Menggunakan 'head' request dengan header 'Prefer: count=exact'
            $url = env('SUPABASE_REST_URL') . '/notifications?recipient_id=eq.' . $userUUID . '&is_read=eq.false';

            $response = Http::withHeaders(array_merge($headers, ['Prefer' => 'count=exact']))->head($url);

            if ($response->successful() && $response->hasHeader('Content-Range')) {
                // Supabase mengembalikan jumlah total baris di header Content-Range: 0-9/TOTAL_COUNT
                $range = $response->header('Content-Range');
                if (preg_match('/\/(?P<count>\d+)/', $range, $matches)) {
                    $count = (int)$matches['count'];
                    Log::info("🔔 Notifikasi Unread Count berhasil: {$count}");
                    return $count;
                }
            }
            
            Log::warning("⚠️ Gagal mendapatkan notifikasi count. Status: " . $response->status());
            return 0; 
            
        } catch (\Exception $e) {
            Log::error('❌ Error saat menghitung notifikasi: ' . $e->getMessage());
            return 0;
        }
    }
}
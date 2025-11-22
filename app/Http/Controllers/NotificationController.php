<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Tambahkan Log untuk debugging

class NotificationController extends Controller
{
    public function index()
    {
        // 1. Ambil User yang sedang login
        $user = Auth::user();
        
        // ✅ FIX KRITIS: Ambil UUID Supabase (yang bertindak sebagai user_id di Supabase)
        $userUUID = $user->supabase_uuid ?? null;
        
        if (empty($userUUID)) {
            Log::warning('Notifikasi Gagal: UUID pengguna kosong saat mengambil notifikasi.');
            return view('profile.notifications', ['notifications' => []])
                   ->with('error', 'Gagal memuat notifikasi. UUID tidak ditemukan.');
        }


        try {
            // 2. Buat Query dengan filter UUID yang Benar
            $query = env('SUPABASE_REST_URL') . "/notifications?user_id=eq.$userUUID&order=created_at.desc";

            $notificationsResponse = Http::withHeaders([
                'apikey'        => env('SUPABASE_ANON_KEY'),
                // Gunakan token yang sesuai, di sini kita tetap pakai anon key karena ini request backend
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'), 
            ])->get($query);

            if (!$notificationsResponse->successful()) {
                Log::error('Gagal mengambil notifikasi dari Supabase:', ['body' => $notificationsResponse->body()]);
                return view('profile.notifications', ['notifications' => []])
                       ->with('error', 'Gagal memuat notifikasi dari Supabase.');
            }

            // 3. Kembalikan data
            return view('profile.notifications', [
                'notifications' => $notificationsResponse->json()
            ]);

        } catch (\Exception $e) {
            Log::error('Exception saat memuat notifikasi: ' . $e->getMessage());
            return view('profile.notifications', ['notifications' => []])
                   ->with('error', 'Kesalahan sistem saat memuat notifikasi.');
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\NotificationService; // Import Service Class yang benar
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi untuk pengguna yang sedang login.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userUUID = $user->supabase_uuid ?? null;
        
        // Asumsi: Kita akan mengambil data notifikasi dari Supabase
        // Anda harus memiliki tabel 'notifications' yang menyimpan notifikasi
        
        $headers = [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY') // Anon key cukup untuk READ jika RLS diizinkan
        ];
        
        // URL untuk mengambil notifikasi milik user ini
        // Kita asumsikan tabel notifications memiliki kolom 'recipient_id' atau 'user_id'
        $url = env('SUPABASE_REST_URL') . '/notifications?select=*&recipient_id=eq.'.$userUUID.'&order=created_at.desc';

        try {
            $response = Http::withHeaders($headers)->get($url);

            if (!$response->successful()) {
                Log::error('Gagal mengambil notifikasi dari Supabase: ' . $response->body());
                $notifications = [];
            } else {
                $notifications = $response->json() ?? [];
            }

            // Arahkan ke view notifikasi baru
            return view('notifications.index', compact('notifications')); 

        } catch (\Exception $e) {
            Log::error('Error saat mengambil notifikasi: ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat notifikasi.');
        }
    }
}
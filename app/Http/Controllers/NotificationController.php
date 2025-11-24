<?php

namespace App\Http\Controllers;

use App\Services\NotificationService; // Import Service Class yang benar
use Illuminate\Support\Facades\Auth;
<<<<<<< Updated upstream
use Illuminate\Support\Facades\Http;
=======
>>>>>>> Stashed changes
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi untuk pengguna yang sedang login.
     */
    public function index()
    {
<<<<<<< Updated upstream
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
=======
        // 1. Ambil User yang sedang login
        $user = Auth::user();
        
        // FIX KRITIS: Ambil UUID Supabase (yang bertindak sebagai user_id di Supabase)
        $userUUID = $user->supabase_uuid ?? null;
        
        if (empty($userUUID)) {
            Log::warning('Notifikasi Gagal: UUID pengguna kosong saat mengambil notifikasi.');
            return view('profile.notifications', ['notifications' => []])
                   ->with('error', 'Gagal memuat notifikasi. Silakan login ulang.');
        }


        try {
            // 2. Buat Query dengan filter UUID yang Benar
            // user_id di tabel notifications adalah PENERIMA notifikasi
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
>>>>>>> Stashed changes
        }
    }
}
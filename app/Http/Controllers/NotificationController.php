<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Menampilkan daftar notifikasi untuk pengguna login.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $userJWT  = $user->supabase_jwt;

        if (!$userUUID || !$userJWT) {
            Log::warning("⚠️ Supabase UUID/JWT kosong untuk user lokal ID {$user->id}");
            return back()->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        // Header autentikasi Supabase (WAJIB JWT user untuk RLS)
        $headers = [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => "Bearer {$userJWT}",
            'Content-Type'  => 'application/json',
        ];

        /*
        |--------------------------------------------------------------------------
        | Ambil Semua Notifikasi User
        | Termasuk relasi performer (yang memberi like/comment)
        | Termasuk relasi image (gambar terkait notifikasi)
        |--------------------------------------------------------------------------
        */
        $url = env('SUPABASE_REST_URL') .
            "/notifications" .
            "?select=id,recipient_id,performer_id,type,message,image_id,is_read,created_at," .
            "performer:performer_id(name)," .
            "image:image_id(title,image_path)" .
            "&recipient_id=eq.$userUUID" .
            "&order=created_at.desc";

        try {
            $response = Http::withHeaders($headers)
                ->withoutVerifying()          // penting untuk Windows SSL issue
                ->get($url);

            if (!$response->successful()) {
                Log::error("❌ Gagal mengambil notifikasi: " . $response->body());
                $notifications = [];
            } else {
                $notifications = $response->json() ?? [];
            }

            // FIX: Tambahkan URL Storage untuk image notifikasi
            $storageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';

            foreach ($notifications as &$notif) {
                if (isset($notif['image']['image_path'])) {
                    $notif['image']['image_url'] =
                        $storageUrl . $notif['image']['image_path'];
                }
            }

            return view('notifications.index', compact('notifications'));
        } catch (\Exception $e) {
            Log::error("💥 Error notifikasi: " . $e->getMessage());
            return back()->with('error', 'Gagal memuat notifikasi.');
        }
    }

    /**
     * Tandai semua notifikasi sebagai telah dibaca.
     */
    public function markAllRead()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $userJWT  = $user->supabase_jwt;

        $headers = [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => "Bearer {$userJWT}",
            'Content-Type'  => 'application/json',
        ];

        try {
            $url = env('SUPABASE_REST_URL') . "/notifications?recipient_id=eq.$userUUID";

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->patch($url, ['is_read' => true]);

            if (!$response->successful()) {
                Log::error("❌ Gagal update read notifikasi: " . $response->body());
            }

            return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
        } catch (\Exception $e) {
            Log::error("💥 Error read notif: " . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui status notifikasi.');
        }
    }
}

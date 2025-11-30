<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Menampilkan semua notifikasi milik user login.
     */
    public function index()
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $jwt = $user->supabase_jwt;

        if (!$jwt) {
            return back()->with('error', 'JWT expired. Silakan login ulang.');
        }

        $headers = [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => "Bearer {$jwt}",
        ];

        // ambil notifikasi TANPA JOIN performer
        $url = env('SUPABASE_REST_URL') .
            "/notifications?recipient_id=eq.$userUUID&order=created_at.desc";

        $res = Http::withHeaders($headers)->withoutVerifying()->get($url);

        if (!$res->successful()) {
            Log::error('Notif error: ' . $res->body());
            $notifications = [];
        } else {
            $notifications = $res->json();
        }

        // get image public URL
        $storageUrl = rtrim(env('SUPABASE_URL'), '/') . "/storage/v1/object/public/images/";

        foreach ($notifications as &$n) {
            if (isset($n['image_id'])) {
                // ambil info gambar
                $imgUrl = env('SUPABASE_REST_URL') . "/images?id=eq.{$n['image_id']}&select=title,image_path";
                $imgRes = Http::withHeaders($headers)->withoutVerifying()->get($imgUrl);

                $img = $imgRes->json()[0] ?? null;

                $n['image'] = $img ? [
                    'title'      => $img['title'],
                    'image_url'  => $storageUrl . $img['image_path'],
                ] : null;
            }

            // ambil nama performer dari auth.users
            if (isset($n['performer_id'])) {
                $usrUrl = env('SUPABASE_REST_URL') . "/users?id=eq.{$n['performer_id']}&select=email";
                $usrRes = Http::withHeaders($headers)->withoutVerifying()->get($usrUrl);

                $usr = $usrRes->json()[0] ?? null;

                $n['performer'] = [
                    'name' => $usr['email'] ?? 'Pengguna'
                ];
            }
        }

        return view('notifications.index', compact('notifications'));
    }




    /**
     * Tandai semua notifikasi user sebagai "read".
     */
    public function markAllRead()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $userJWT  = $user->supabase_jwt;

        if (!$userJWT) {
            return back()->with('error', 'Sesi login tidak valid. Silakan login ulang.');
        }

        $headers = [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => "Bearer {$userJWT}",
            'Content-Type'  => 'application/json'
        ];

        try {
            $url = env('SUPABASE_REST_URL') . "/notifications?recipient_id=eq.$userUUID";

            $response = Http::withHeaders($headers)
                ->withoutVerifying()
                ->patch($url, ['is_read' => true]);

            if (!$response->successful()) {
                Log::error("❌ Gagal update read notifikasi: " . $response->body());
                return back()->with('error', 'Gagal menandai sebagai dibaca.');
            }

            return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
        } catch (\Exception $e) {
            Log::error("💥 Error read notif: " . $e->getMessage());
            return back()->with('error', 'Kesalahan saat memproses.');
        }
    }
}

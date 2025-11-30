<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LikeController extends Controller
{
    /* ============================================================
     |  HEADER JWT (WAJIB untuk INSERT / DELETE)
     ============================================================ */
    private function getAuthHeaders()
    {
        $jwt = Auth::user()->supabase_jwt ?? null;

        return [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . $jwt,
            'Content-Type'  => 'application/json',
        ];
    }

    /* ============================================================
     | HEADER PUBLIC (READ ONLY)
     ============================================================ */
    private function getPublicHeaders()
    {
        return [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ];
    }

    /* ============================================================
     |  LIKE / UNLIKE
     |  POST /images/{image}/like
     ============================================================ */
    public function toggle($imageId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Anda harus login untuk menyukai karya.'], 401);
        }

        $user = Auth::user();
        $userUUID = $user->supabase_uuid;
        $jwt = $user->supabase_jwt;

        if (!$jwt || !$userUUID) {
            return response()->json(['error' => 'Sesi tidak valid. Silakan login ulang.'], 401);
        }

        $dbUrl = env('SUPABASE_REST_URL');

        try {

            /* ============================================================
             | STEP 1 — CEK LIKE EXISTING
             ============================================================ */
            $checkUrl = $dbUrl . "/likes?select=id&image_id=eq.$imageId&user_id=eq.$userUUID";

            $checkRes = Http::withHeaders($this->getPublicHeaders())
                ->withoutVerifying()
                ->get($checkUrl);

            $existingLike = $checkRes->json()[0] ?? null;

            /* ============================================================
             | STEP 2 — UNLIKE (DELETE)
             ============================================================ */
            if ($existingLike) {

                $deleteUrl = $dbUrl . "/likes?id=eq.{$existingLike['id']}&user_id=eq.$userUUID";

                $deleteRes = Http::withHeaders($this->getAuthHeaders())
                    ->withoutVerifying()
                    ->delete($deleteUrl);

                if (!$deleteRes->successful()) {
                    Log::error("❌ DELETE LIKE FAILED:", ['body' => $deleteRes->body()]);
                    return response()->json(['success' => false, 'error' => 'Gagal menghapus like.'], 403);
                }

                $action = 'unliked';
            }

            /* ============================================================
             | STEP 3 — LIKE (INSERT)
             ============================================================ */ else {

                // Ambil pemilik gambar untuk notifikasi
                $ownerQuery = $dbUrl . "/images?select=user_id&id=eq.$imageId";
                $ownerRes = Http::withHeaders($this->getPublicHeaders())
                    ->withoutVerifying()
                    ->get($ownerQuery);

                $recipientUUID = $ownerRes->json()[0]['user_id'] ?? null;

                // Jangan kirim notifikasi ke diri sendiri
                $shouldNotify = ($recipientUUID && $recipientUUID !== $userUUID);

                // Insert like
                $payload = [
                    'image_id'   => $imageId,
                    'user_id'    => $userUUID,
                    'created_at' => now()->toIso8601String(),
                ];

                $insertRes = Http::withHeaders($this->getAuthHeaders())
                    ->withoutVerifying()
                    ->post($dbUrl . "/likes", $payload);

                if (!$insertRes->successful()) {
                    Log::error("❌ INSERT LIKE FAILED:", ['body' => $insertRes->body()]);
                    return response()->json(['success' => false, 'error' => 'Gagal menambahkan like.'], 403);
                }

                $action = 'liked';

                /* ============================================================
                 | STEP 4 — INSERT NOTIFIKASI (JIKA BUKAN KARYA SENDIRI)
                 ============================================================ */
                if ($shouldNotify) {

                    $notifPayload = [
                        'recipient_id' => $recipientUUID,
                        'performer_id' => $userUUID,
                        'image_id'     => $imageId,
                        'type'         => 'like',
                        'message'      => $user->name . ' menyukai fotomu.',
                        'is_read'      => false,
                        'created_at'   => now()->toIso8601String(),
                    ];

                    $notifRes = Http::withHeaders($this->getAuthHeaders())
                        ->withoutVerifying()
                        ->post($dbUrl . "/notifications", $notifPayload);

                    if (!$notifRes->successful()) {
                        Log::warning("⚠️ NOTIF LIKE GAGAL:", ['body' => $notifRes->body()]);
                    }
                }
            }

            /* ============================================================
             | STEP 5 — HAPUS CACHE
             ============================================================ */
            Cache::forget("explore_images_list");
            Cache::forget("images_detail_$imageId");

            /* ============================================================
             | STEP 6 — AMBIL LIKE COUNT BARU
             ============================================================ */
            $countUrl = $dbUrl . "/likes?image_id=eq.$imageId&select=count";

            $countRes = Http::withHeaders($this->getPublicHeaders())
                ->withoutVerifying()
                ->get($countUrl);

            $likeCount = $countRes->json()[0]['count'] ?? 0;

            return response()->json([
                'success'    => true,
                'action'     => $action,
                'like_count' => $likeCount,
            ]);
        } catch (\Exception $e) {
            Log::error("💥 EXCEPTION LIKE:", ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Kesalahan internal server.'], 500);
        }
    }
}

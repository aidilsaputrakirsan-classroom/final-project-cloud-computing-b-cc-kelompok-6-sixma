<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    /* ============================================================
     |  HELPER: GET JWT USER
     ============================================================ */
    private function getAuthJwt()
    {
        return Auth::user()->supabase_jwt ?? null;
    }

    /* ============================================================
     |  HELPER: HEADER OTENTIKASI (CUD → WAJIB JWT)
     ============================================================ */
    private function getAuthHeaders()
    {
        $jwt = $this->getAuthJwt();

        return [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . $jwt,
            'Content-Type'  => 'application/json',
        ];
    }

    /* ============================================================
     |  HELPER: HEADER BACA (READ ONLY)
     ============================================================ */
    private function getPublicHeaders()
    {
        return [
            'apikey'        => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ];
    }

    /* ============================================================
     |  STORE COMMENT
     |  POST /images/{image}/comments
     ============================================================ */
    public function store(Request $request, $imageId)
    {
        if (!Auth::check()) {
            return back()->with('error', 'Anda harus login untuk berkomentar.');
        }

        $user = Auth::user();
        $performerUUID = $user->supabase_uuid;
        $jwt           = $user->supabase_jwt;

        if (!$jwt || !$performerUUID) {
            return back()->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        try {
            /* ============================================================
             | STEP 1 — Dapatkan ID pemilik gambar
             ============================================================ */
            $imageQuery = env('SUPABASE_REST_URL') .
                "/images?select=user_id&id=eq.$imageId";

            $imageRes = Http::withHeaders($this->getPublicHeaders())
                ->withoutVerifying()
                ->get($imageQuery);

            $imgJson = $imageRes->json();
            if (empty($imgJson)) {
                return back()->with('error', 'Gambar tidak ditemukan.');
            }

            $recipientUUID = $imgJson[0]['user_id'];
            $shouldNotify  = ($recipientUUID !== $performerUUID);

            /* ============================================================
             | STEP 2 — Insert komentar
             ============================================================ */
            $commentData = [
                'content'    => $request->content,
                'image_id'   => $imageId,
                'user_id'    => $performerUUID,
                'created_at' => now()->toIso8601String(),
            ];

            $commentRes = Http::withHeaders(array_merge(
                $this->getAuthHeaders(),
                ['Prefer' => 'return=representation']
            ))
                ->withoutVerifying()
                ->post(env('SUPABASE_REST_URL') . '/comments', $commentData);

            if (!$commentRes->successful()) {
                Log::error("❌ INSERT KOMENTAR GAGAL:", [
                    'status' => $commentRes->status(),
                    'body'   => $commentRes->body()
                ]);

                return back()->with('error', 'Gagal menyimpan komentar.');
            }

            /* ============================================================
             | STEP 3 — Insert Notifikasi (jika bukan komentar ke diri sendiri)
             ============================================================ */
            if ($shouldNotify) {
                $notifPayload = [
                    'recipient_id' => $recipientUUID,
                    'performer_id' => $performerUUID,
                    'image_id'     => $imageId,
                    'type'         => 'comment',
                    'message'      => $user->name . ' mengomentari foto Anda',
                    'is_read'      => false,
                    'created_at'   => now()->toIso8601String(),
                ];

                $notifRes = Http::withHeaders($this->getAuthHeaders())
                    ->withoutVerifying()
                    ->post(env('SUPABASE_REST_URL') . '/notifications', $notifPayload);

                if (!$notifRes->successful()) {
                    Log::warning("⚠️ Notifikasi gagal:", [
                        'status' => $notifRes->status(),
                        'body'   => $notifRes->body()
                    ]);
                }
            }

            /* ============================================================
             | STEP 4 — Clear Cache Halaman Detail
             ============================================================ */
            Cache::forget('images_detail_' . $imageId);

            return back()->with('success', 'Komentar berhasil dikirim!');
        } catch (\Exception $e) {
            Log::error("💥 EXCEPTION COMMENT STORE:", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan komentar.');
        }
    }

    /* ============================================================
     |  DELETE COMMENT
     |  DELETE /comments/{id}
     ============================================================ */
    public function destroy($id)
    {
        if (!Auth::check()) {
            return back()->with('error', 'Anda harus login untuk menghapus komentar.');
        }

        try {
            $userUUID = Auth::user()->supabase_uuid;

            /* --- Ambil image_id komentar dulu untuk update cache --- */
            $commentQuery = env('SUPABASE_REST_URL') .
                "/comments?select=image_id&id=eq.$id";

            $commentRes = Http::withHeaders($this->getPublicHeaders())
                ->withoutVerifying()
                ->get($commentQuery);

            $imageId = $commentRes->json()[0]['image_id'] ?? null;

            /* --- DELETE komentar --- */
            $deleteUrl = env('SUPABASE_REST_URL')
                . "/comments?id=eq.$id&user_id=eq.$userUUID";

            $response = Http::withHeaders($this->getAuthHeaders())
                ->withoutVerifying()
                ->delete($deleteUrl);

            if (!$response->successful()) {
                Log::error("❌ DELETE KOMENTAR GAGAL:", [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);

                return back()->with('error', 'Komentar gagal dihapus.');
            }

            if ($imageId) {
                Cache::forget("images_detail_$imageId");
            }

            return back()->with('success', 'Komentar berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error("💥 EXCEPTION DELETE COMMENT:", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan internal.');
        }
    }
}

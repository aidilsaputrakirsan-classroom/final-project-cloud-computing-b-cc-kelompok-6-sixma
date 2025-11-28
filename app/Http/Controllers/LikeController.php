<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; 

class LikeController extends Controller
{
    /**
     * Mengambil header otentikasi (JWT)
     */
    private function getAuthHeaders() {
        $userJWT = Auth::user()->supabase_jwt ?? null;

        if (empty($userJWT)) {
            Log::error('JWT Pengguna Kosong saat operasi like.');
            return ['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'), 'Content-Type' => 'application/json'];
        }

        return ['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . $userJWT, 'Content-Type' => 'application/json'];
    }

    /**
     * Mengembalikan header standar (Anon Key)
     */
    private function getSupabaseHeaders() {
        return ['apikey' => env('SUPABASE_ANON_KEY'), 'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')];
    }


    /**
     * Mengaktifkan/menonaktifkan like pada sebuah gambar.
     * Rute: POST /images/{image}/like
     */
    public function toggle($image)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Anda harus login untuk menyukai karya.'], 401);
        }
        
        $userId = Auth::user()->supabase_uuid;
        $databaseUrl = env('SUPABASE_REST_URL');
        $authHeaders = $this->getAuthHeaders();

        // 1. Cek apakah user sudah memberikan like sebelumnya (READ)
        $checkUrl = $databaseUrl . "/likes?select=id&image_id=eq.{$image}&user_id=eq.{$userId}";
        $checkResponse = Http::withHeaders($this->getSupabaseHeaders())->get($checkUrl);
        
        if (!$checkResponse->successful()) {
            Log::error('❌ Gagal memeriksa status like: ' . $checkResponse->body());
            return response()->json(['success' => false, 'error' => 'Gagal memeriksa status like.'], 500);
        }

        $existingLike = $checkResponse->json()[0] ?? null;

        if ($existingLike) {
            // LAKUKAN DELETE (Unlike)
            $likeId = $existingLike['id'];
            $deleteUrl = $databaseUrl . "/likes?id=eq.{$likeId}&user_id=eq.{$userId}"; 
            
            $deleteResponse = Http::withHeaders($authHeaders)->delete($deleteUrl);
            
            if (!$deleteResponse->successful()) {
                Log::error('❌ Gagal DELETE like: ' . $deleteResponse->body());
                return response()->json(['success' => false, 'error' => 'Gagal menghapus like. Policy RLS DELETE "likes" mungkin salah.'], 403);
            }
            $action = 'unliked';
        } else {
            // LAKUKAN INSERT (Like)
            $likeData = [
                'image_id' => $image,
                'user_id' => $userId,
                'created_at' => now()->toIso8601String(),
            ];
            
            $insertResponse = Http::withHeaders($authHeaders)->post($databaseUrl . '/likes', $likeData);
            
            if (!$insertResponse->successful()) {
                Log::error('❌ Gagal INSERT like: ' . $insertResponse->body());
                return response()->json(['success' => false, 'error' => 'Gagal menambahkan like. Policy RLS INSERT "likes" mungkin salah.'], 403);
            }
            $action = 'liked';
        }
        
        // KRITIS: Hapus cache di sini agar count likes ter-update di Index/Show
        Cache::forget('explore_images_list');
        Cache::forget('images_detail_' . $image); 

        // 2. Ambil hitungan like yang baru (Supabase Count Aggregate)
        $countUrl = $databaseUrl . "/likes?image_id=eq.{$image}&select=count";
        $countResponse = Http::withHeaders($this->getSupabaseHeaders())->get($countUrl);
        
        $newCount = 0;
        if ($countResponse->successful() && !empty($countResponse->json())) {
             $newCount = $countResponse->json()[0]['count'] ?? 0;
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'like_count' => $newCount
        ]);
    }
}

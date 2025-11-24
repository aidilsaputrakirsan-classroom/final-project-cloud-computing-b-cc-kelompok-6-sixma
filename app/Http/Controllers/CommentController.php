<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; 
use App\Services\NotificationService; 

class CommentController extends Controller
{
    /**
     * Mengambil JWT yang tersimpan di Model pengguna yang sedang login.
     */
    private function getAuthJwt() {
        return Auth::user()->supabase_jwt ?? null;
    }

    /**
     * Mengembalikan header dengan JWT Pengguna untuk operasi otentikasi (CUD).
     */
    private function getAuthHeaders() {
        $userJWT = $this->getAuthJwt();

        if (empty($userJWT)) {
            Log::error('JWT Pengguna Kosong saat operasi komentar.');
            return [
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json'
            ];
        }

        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . $userJWT, 
            'Content-Type' => 'application/json'
        ];
    }
    
    /**
     * Mengembalikan header standar untuk request Supabase REST API (Anon Key).
     */
    private function getSupabaseHeaders() {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
        ];
    }
    
    /**
     * Menyimpan komentar baru ke database.
     * Rute: POST /images/{image}/comments
     */
    public function store(Request $request, $image) // $image adalah ID gambar
    {
        // 1. Cek Autentikasi & Header
        if (!Auth::check()) {
            return back()->with('error', 'Anda harus login untuk berkomentar.');
        }
        $user = Auth::user();
        $performerId = $user->supabase_uuid; 
        
        $authHeaders = $this->getAuthHeaders();
        // Cek jika header masih menggunakan Anon Key (sesi login bermasalah)
        if (str_contains($authHeaders['Authorization'], env('SUPABASE_ANON_KEY'))) {
            return back()->with('error', 'Sesi login tidak lengkap. Harap logout dan login ulang.');
        }

        // 2. Validasi Input
        $request->validate([
            'content' => 'required|string|max:500', 
        ]);

        try {
            // 2.1. Temukan Pemilik Karya (Recipient)
            $headers = $this->getSupabaseHeaders();
            $imageUrl = env('SUPABASE_REST_URL') . '/images?select=user_id&id=eq.'.$image;
            $imageResponse = Http::withHeaders($headers)->get($imageUrl);
            
            if (!$imageResponse->successful() || empty($imageResponse->json())) {
                Log::error('❌ GAGAL MENDAPATKAN PEMILIK KARYA UNTUK NOTIFIKASI.');
                return back()->with('error', 'Gagal memproses komentar (Karya tidak ditemukan).');
            }
            $recipientId = $imageResponse->json()[0]['user_id'] ?? null;
            $shouldNotify = ($performerId !== $recipientId) && $recipientId;
            
            
            // 3. Persiapkan Data
            $commentData = [
                'content' => $request->content,
                'image_id' => $image,
                'user_id' => $performerId,
                'created_at' => now()->toIso8601String(),
            ];
            $databaseUrl = env('SUPABASE_REST_URL');
            
            // 4. Proses POST Komentar (Serial Request)
            
            // Komentar - Harus berhasil
            $commentResponse = Http::withHeaders(array_merge($authHeaders, ['Prefer' => 'return=representation']))
                                  ->post($databaseUrl . '/comments', $commentData);
            
            // 5. Cek Hasil Komentar (KRITIS)
            if (!$commentResponse->successful()) {
                $errorBody = $commentResponse->body();
                Log::error('❌ COMMENT_INSERT_FAILURE:', ['status' => $commentResponse->status(), 'error_body' => $errorBody]);
                
                // MENGEMBALIKAN ERROR RLS 401 DENGAN JELAS
                $message = $commentResponse->status() == 401 ? 'Akses ditolak (401). Policy RLS INSERT "comments" salah.' : 'Gagal mengirim komentar.';
                return back()->with('error', $message);
            }
            
            // 6. POST Notifikasi (Serial Request, Opsional)
            if ($shouldNotify) {
                 $notificationResponse = Http::withHeaders($this->getSupabaseHeaders()) 
                          ->post($databaseUrl . '/notifications', [
                              'recipient_id' => $recipientId,
                              'performer_id' => $performerId,
                              'image_id' => $image,
                              'type' => 'comment',
                              'message' => 'Notifikasi akan dibuat oleh Service Class',
                              'is_read' => false,
                              'created_at' => now()->toIso8601String()
                          ]);
                          
                 if (!$notificationResponse->successful()) {
                      Log::warning('❌ Notifikasi gagal disimpan di Supabase. Status: ' . $notificationResponse->status());
                 }
            }
            
            // 7. HAPUS CACHE DETAIL KARYA 
            Cache::forget('images_detail_' . $image);

            return back()->with('success', 'Komentar berhasil dikirim!');

        } catch (\Exception $e) {
            // FIX KRITIS: Log error dan kembalikan pesan statis untuk menghindari PHP fatal error di browser
            Log::error('❌ EXCEPTION FATAL SAAT MENGIRIM KOMENTAR:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan komentar.');
        }
    }
    
    /**
     * Menghapus komentar berdasarkan ID.
     * Rute: DELETE /comments/{id}
     */
    public function destroy($id)
    {
        // ... (Logika destroy tetap sama) ...
    }
}
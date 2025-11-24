<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
<<<<<<< Updated upstream
use Illuminate\Support\Facades\Cache; 
use App\Services\NotificationService; 
=======
use Carbon\Carbon;
>>>>>>> Stashed changes

class CommentController extends Controller
{
    /**
<<<<<<< Updated upstream
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
=======
     * Mengambil header standar untuk request Supabase (menggunakan ANON KEY)
>>>>>>> Stashed changes
     */
    private function getSupabaseHeaders() {
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
<<<<<<< Updated upstream
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
=======
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
>>>>>>> Stashed changes
        ];
    }
    
    /**
<<<<<<< Updated upstream
     * Menyimpan komentar baru ke database.
=======
     * Menyimpan komentar baru ke database dan membuat notifikasi.
>>>>>>> Stashed changes
     * Rute: POST /images/{image}/comments
     */
    public function store(Request $request, $image) // $image adalah ID gambar
    {
<<<<<<< Updated upstream
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
=======
        // 1. Cek Autentikasi dan Ambil UUID Pengguna
        if (!Auth::check()) {
            return back()->with('error', 'Anda harus login untuk berkomentar.');
        }
        
        $user = Auth::user();
        // Menggunakan UUID yang sudah tersimpan di Model User lokal
        $userUUID = $user->supabase_uuid ?? null; 

        if (empty($userUUID)) {
             return back()->with('error', 'UUID pengguna tidak ditemukan. Silakan login ulang.');
>>>>>>> Stashed changes
        }

        // 2. Validasi Input
        $request->validate([
            'content' => 'required|string|max:500', 
        ]);

        $headers = $this->getSupabaseHeaders();
        $baseApiUrl = env('SUPABASE_REST_URL');

        try {
<<<<<<< Updated upstream
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
=======
            // ========================================================
            // A. Ambil Data Karya untuk Mengetahui Pemilik (Recipient)
            // ========================================================
            $imageResponse = Http::withHeaders($headers)
                ->get($baseApiUrl . '/images?id=eq.' . $image . '&select=user_id,title');
            
            if (!$imageResponse->successful() || empty($imageResponse->json())) {
                 return back()->with('error', 'Karya target tidak ditemukan.');
            }

            $imageDetail = $imageResponse->json()[0];
            $pemilikUUID = $imageDetail['user_id']; // UUID Penerima Notifikasi (Kirana)
            $karyaTitle = $imageDetail['title']; // Judul karya

            // 3. Kirim data komentar ke Supabase
            $commentData = [
                'content' => $request->content,
                'image_id' => $image, 
                'user_id' => $userUUID, // Pengirim Komentar (Anda)
                'created_at' => now()->toIso8601String(),
            ];

            $createComment = Http::withHeaders($headers)->post($baseApiUrl . '/comments', $commentData);

            if (!$createComment->successful()) {
                $errorBody = $createComment->body();
                // Ini akan muncul jika RLS INSERT di tabel comments masih salah
                Log::error('❌ Gagal menyimpan komentar:', ['status' => $createComment->status(), 'error' => $errorBody]);
                return back()->with('error', 'Gagal mengirim komentar. (Cek Policy INSERT RLS tabel comments)');
            }
            
            // ========================================================
            // B. Buat Notifikasi (INSERT ke tabel notifications)
            // ========================================================
            
            // Hanya buat notifikasi jika pengirim komentar BUKAN pemilik karya
            if ($userUUID !== $pemilikUUID) {
                $notifData = [
                    'user_id' => $pemilikUUID, // Penerima (Pemilik Karya)
                    'actor_id' => $userUUID, // Pengirim (Yang berkomentar)
                    'image_id' => $image, 
                    'type' => 'comment',
                    'message' => 'mengomentari karya "' . $karyaTitle . '".', 
                    'created_at' => now()->toIso8601String(),
                ];

                $notifUrl = $baseApiUrl . '/notifications'; 
                $createNotif = Http::withHeaders($headers)->post($notifUrl, $notifData);

                if (!$createNotif->successful()) {
                    Log::error('❌ Gagal membuat notifikasi:', ['status' => $createNotif->status(), 'error' => $createNotif->body()]);
                    // Kita biarkan user tetap sukses karena komentarnya sudah masuk
                }
            }

>>>>>>> Stashed changes

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
<<<<<<< Updated upstream
        // ... (Logika destroy tetap sama) ...
=======
        // 1. Cek Autentikasi & Ambil UUID
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $user = Auth::user();
        $userUUID = $user->supabase_uuid ?? null;
        
        try {
            // 2. Kirim permintaan DELETE ke Supabase dengan filter ID dan USER ID (UUID)
            $deleteDb = Http::withHeaders($this->getSupabaseHeaders())
                ->delete(env('SUPABASE_REST_URL') . '/comments?id=eq.' . $id . '&user_id=eq.' . $userUUID);

            if (!$deleteDb->successful()) {
                Log::error('Database Delete Comment Error:', ['body' => $deleteDb->body()]);
                return back()->with('error', 'Gagal menghapus komentar. (Mungkin bukan milik Anda)');
            }

            return back()->with('success', '🗑️ Komentar berhasil dihapus!');

        } catch (\Exception $e) {
            Log::error('❌ Error in destroy (Comment): ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus komentar.');
        }
>>>>>>> Stashed changes
    }
}
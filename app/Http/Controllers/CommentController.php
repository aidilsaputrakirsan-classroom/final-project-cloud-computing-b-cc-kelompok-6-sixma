<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CommentController extends Controller
{
    // Mengambil Auth Token dari user yang sedang login
    private function getAuthHeaders() {
        // Menggunakan anon key karena kita melakukan POST dari backend (server-to-server)
        return [
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
        ];
    }
    
    /**
     * Menyimpan komentar baru ke database dan membuat notifikasi.
     * Rute: POST /images/{image}/comments
     */
    public function store(Request $request, $image) // $image adalah ID gambar
    {
        // 1. Cek Autentikasi dan Ambil UUID Pengguna
        if (!Auth::check()) {
            return back()->with('error', 'Anda harus login untuk berkomentar.');
        }
        
        $user = Auth::user();
        // Menggunakan UUID yang sudah tersimpan di Model User lokal
        $userUUID = $user->supabase_uuid ?? null; 

        if (empty($userUUID)) {
             return back()->with('error', 'UUID pengguna tidak ditemukan. Silakan login ulang.');
        }

        // 2. Validasi Input
        $request->validate([
            'content' => 'required|string|max:500', 
        ]);

        $headers = $this->getAuthHeaders();

        try {
            // ========================================================
            // A. Ambil Data Karya untuk Mengetahui Pemilik (Recipient)
            // ========================================================
            $imageResponse = Http::withHeaders($headers)
                ->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $image . '&select=user_id,title');
            
            if (!$imageResponse->successful() || empty($imageResponse->json())) {
                 Log::warning('Karya tidak ditemukan saat mengirim komentar: ' . $image);
                 return back()->with('error', 'Karya target tidak ditemukan.');
            }

            $imageDetail = $imageResponse->json()[0];
            $pemilikUUID = $imageDetail['user_id']; // UUID Penerima Notifikasi
            $karyaTitle = $imageDetail['title']; // Judul karya

            // 3. Kirim data komentar ke Supabase
            $commentData = [
                'content' => $request->content,
                'image_id' => $image, 
                'user_id' => $userUUID, // Pengirim Komentar (Anda)
                'created_at' => now()->toIso8601String(),
            ];

            $databaseUrl = env('SUPABASE_REST_URL') . '/comments'; 
            $createComment = Http::withHeaders($headers)->post($databaseUrl, $commentData);

            if (!$createComment->successful()) {
                $errorBody = $createComment->body();
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

                $notifUrl = env('SUPABASE_REST_URL') . '/notifications'; 
                $createNotif = Http::withHeaders($headers)->post($notifUrl, $notifData);

                if (!$createNotif->successful()) {
                    Log::error('❌ Gagal membuat notifikasi:', ['status' => $createNotif->status(), 'error' => $createNotif->body()]);
                    // Kita biarkan user tetap sukses karena komentarnya sudah masuk
                }
            }


            return back()->with('success', 'Komentar berhasil dikirim!');

        } catch (\Exception $e) {
            Log::error('❌ Exception in store (Comment):', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan komentar.');
        }
    }

    /**
     * Menghapus komentar berdasarkan ID. (Tidak diubah)
     */
    public function destroy($id)
    {
        // ... (Kode destroy tidak diubah)
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $userUUID = $user->supabase_uuid ?? null;
        
        try {
            // 2. Kirim permintaan DELETE ke Supabase dengan filter ID dan USER ID
            $deleteDb = Http::withHeaders($this->getAuthHeaders())
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
    }
}
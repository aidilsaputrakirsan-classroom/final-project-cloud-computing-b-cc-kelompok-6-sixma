<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    /**
     * Menyimpan komentar baru ke database.
     * Rute: POST /images/{image}/comments
     */
    public function store(Request $request, $image) // $image adalah ID gambar
    {
        // 1. Cek Autentikasi
        if (!Auth::check()) {
            return back()->with('error', 'Anda harus login untuk berkomentar.');
        }
        $userId = Auth::id();

        // 2. Validasi Input
        $request->validate([
            'content' => 'required|string|max:500', // Pastikan konten komentar ada
        ]);

        try {
            // Data yang akan dikirim ke Supabase
            $commentData = [
                'content' => $request->content,
                'image_id' => $image, // ID Gambar dari URL
                'user_id' => $userId, // ID Pengguna yang berkomentar
                'created_at' => now()->toIso8601String(),
            ];

            Log::info('💾 Data Komentar:', $commentData);

            // 3. Kirim data ke Supabase REST API
            $databaseUrl = env('SUPABASE_REST_URL') . '/comments'; // Target tabel: comments
            $createComment = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation'
            ])->post($databaseUrl, $commentData);

            if (!$createComment->successful()) {
                $errorBody = $createComment->body();
                Log::error('❌ Gagal menyimpan komentar:', ['status' => $createComment->status(), 'error' => $errorBody]);
                return back()->with('error', 'Gagal mengirim komentar. (Cek Policy INSERT RLS tabel comments)');
            }

            return back()->with('success', 'Komentar berhasil dikirim!');

        } catch (\Exception $e) {
            Log::error('❌ Exception in store (Comment):', ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan komentar.');
        }
    }

    /**
     * Menghapus komentar berdasarkan ID.
     * Rute: DELETE /comments/{id}
     */
    public function destroy($id)
    {
        // 1. Cek Autentikasi & Otorisasi
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $userId = Auth::id();
        
        try {
            // 2. Kirim permintaan DELETE ke Supabase dengan filter ID dan USER ID
            // Ini memastikan hanya pemilik komentar yang bisa menghapusnya (Otorisasi)
            $deleteDb = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
            ])->delete(env('SUPABASE_REST_URL') . '/comments?id=eq.' . $id . '&user_id=eq.' . $userId);

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
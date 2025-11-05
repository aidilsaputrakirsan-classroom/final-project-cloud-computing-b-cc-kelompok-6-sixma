<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class DeleteController extends Controller
{
    public function destroy($id)
    {
        // 1. Ambil data gambar dari Supabase untuk verifikasi kepemilikan
        $imageResponse = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->get(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id . '&select=id,user_id,image_path');

        $image = $imageResponse->json()[0] ?? null;

        if (!$image) {
            return back()->with('error', 'Gambar tidak ditemukan.');
        }

        // 2. Otorisasi: Hanya pemilik atau admin yang bisa hapus
        if (Auth::id() !== $image['user_id']) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus karya ini.');
        }

        // 3. Hapus file dari Supabase Storage
        $deleteFile = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->delete(env('SUPABASE_STORAGE_URL') . '/object/public/images/' . $image['image_path']);

        // 4. Hapus record dari Supabase Database
        $deleteRecord = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY'),
        ])->delete(env('SUPABASE_REST_URL') . '/images?id=eq.' . $id);

        if ($deleteRecord->successful()) {
            return redirect()->route('images.index')->with('success', '✅ Karya berhasil dihapus!');
        } else {
            return back()->with('error', 'Gagal menghapus karya dari database.');
        }
    }
}

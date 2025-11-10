<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Menampilkan halaman profile pengguna dan karya mereka.
     * Halaman ini hanya menampilkan gambar milik user yang sedang login.
     */
    public function showProfile()
    {
        // Pastikan user sudah login (middleware 'auth' seharusnya sudah menangani ini)
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user(); // Ambil data user dari Laravel Auth
        $userId = $user->id; // Ambil ID user untuk filter Supabase
        $myImages = [];
        
        try {
            // 1. Ambil SEMUA gambar yang user_id-nya sesuai dengan user yang login
            // Kami menyertakan 'user' untuk memastikan data user (misal nama) juga diambil (jika ada relasi di Supabase)
            $query = env('SUPABASE_REST_URL') . '/images?user_id=eq.' . $userId . '&select=*,category:category_id(*),user:user_id(*)&order=created_at.desc';

            $imagesResponse = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_ANON_KEY')
            ])->get($query);

            if ($imagesResponse->successful()) {
                $imagesData = $imagesResponse->json();
                
                if (is_array($imagesData)) {
                    $baseStorageUrl = rtrim(env('SUPABASE_URL'), '/') . '/storage/v1/object/public/images/';
                    
                    foreach ($imagesData as $image) {
                        // Tambahkan URL gambar lengkap
                        $image['image_url'] = $baseStorageUrl . ($image['image_path'] ?? '');
                        $myImages[] = $image;
                    }
                }
            } else {
                Log::warning('Supabase Profile Image Fetch Failed', ['status' => $imagesResponse->status(), 'body' => $imagesResponse->body()]);
            }
            
            // Siapkan data untuk view
            $data = [
                'user' => $user,
                'myImages' => $myImages,
            ];
            
            return view('profile.show', $data);

        } catch (\Exception $e) {
            Log::error('💥 Error in showProfile(): ' . $e->getMessage());
            return back()->with('error', 'Gagal memuat halaman profil.');
        }
    }
    
    // Anda bisa tambahkan fungsi lain di sini, seperti editProfile() untuk mengganti password/nama.
}
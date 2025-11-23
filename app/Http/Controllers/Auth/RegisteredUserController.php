<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;


class RegisteredUserController extends Controller
{
    /**
     * Menampilkan form registrasi.
     */
    public function create()
    {
        return view('auth.register'); 
    }

    /**
     * Menangani permintaan registrasi pengguna baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Kita hapus 'unique:users' sementara karena ini sering konflik dengan ID yang bermasalah.
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'confirmed'], 
        ]);

        $supabaseAuthUrl = env('SUPABASE_URL') . '/auth/v1/signup';

        try {
            // 2. Registrasi ke Supabase Auth
            $supabaseResponse = Http::withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
            ])->post($supabaseAuthUrl, [
                'email' => $request->email,
                'password' => $request->password,
                'data' => [
                    'name' => $request->name, 
                ]
            ]);

            // Cek apakah registrasi Supabase berhasil
            if (!$supabaseResponse->successful()) {
                $errorBody = $supabaseResponse->json();
                
                Log::error('Supabase Register Gagal: ' . json_encode($errorBody) . ' - Status: ' . $supabaseResponse->status());
                
                if ($supabaseResponse->status() === 400) {
                    return back()->withInput()->withErrors(['email' => 'Email ini sudah terdaftar di Supabase atau formatnya tidak valid.']);
                }

                return back()->with('error', 'Registrasi Supabase gagal. Status: ' . $supabaseResponse->status() . ' - Detail: ' . ($errorBody['msg'] ?? 'Tidak ada pesan error.'));
            }

            $supabaseData = $supabaseResponse->json();

            // Mendapatkan UUID pengguna dari respons Supabase
            $userUUID = $supabaseData['user']['id'] ?? $supabaseData['id'] ?? null;
            
            if (empty($userUUID)) {
                Log::error('Gagal mengambil UUID setelah register. Data respons: ' . json_encode($supabaseData));
                return back()->with('error', 'Registrasi berhasil di Supabase, tapi gagal mengambil UUID.');
            }

            // 3. Simpan User ke Database LOKAL Laravel
            // PERBAIKAN KRITIS: Memasukkan UUID ke kolom ID dan supabase_uuid
            $user = User::create([
                'id' => $userUUID, // FIX: Memaksa ID lokal diisi dengan UUID Supabase
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), 
                'supabase_uuid' => $userUUID, // Menyimpan UUID ke kolom UUID
            ]);

            // 4. Otentikasi
            event(new Registered($user));
            Auth::login($user);
            
            return redirect('/'); 

        } catch (\Exception $e) {
            Log::error('Error pada proses register Laravel: ' . $e->getMessage() . ' di line ' . $e->getLine());
            return back()->with('error', 'Terjadi kesalahan sistem saat registrasi: ' . $e->getMessage());
        }
    }
}
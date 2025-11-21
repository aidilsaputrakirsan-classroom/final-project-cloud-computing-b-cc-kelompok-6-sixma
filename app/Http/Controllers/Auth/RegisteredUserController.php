<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // Digunakan untuk komunikasi dengan Supabase
use Illuminate\Support\Facades\Log;  // Digunakan untuk mencatat error
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Method untuk menampilkan form register
     */
    public function create()
    {
        return view('auth.register'); 
    }

    /**
     * Method untuk memproses data register
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Menghapus 'unique:users' dari Laravel karena keunikan email akan di-handle oleh Supabase
            'email' => ['required', 'string', 'email', 'max:255'], 
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $supabaseAuthUrl = env('SUPABASE_URL') . '/auth/v1/signup';

        try {
            // =======================================================
            // 2. Register ke SUPABASE AUTH DULU
            // =======================================================
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

            if (!$supabaseResponse->successful()) {
                $errorBody = $supabaseResponse->json();
                Log::error('Supabase Register Gagal: ' . json_encode($errorBody));
                
                // Jika error 400 (Bad Request), periksa apakah email sudah terdaftar
                if ($supabaseResponse->status() === 400) {
                    return back()->withInput()->withErrors(['email' => 'Email ini sudah terdaftar di Supabase.']);
                }

                return back()->withInput()->withErrors(['supabase' => $errorBody['msg'] ?? 'Registrasi Supabase gagal.']);
            }

            $supabaseData = $supabaseResponse->json();

            // ⚠️ FIX KRITIS: Mengambil UUID dari respons Supabase
            // Mencoba mengambil UUID dari beberapa kemungkinan kunci respons Supabase
            $userUUID = $supabaseData['user']['id'] ?? $supabaseData['id'] ?? null;
            
            if (empty($userUUID)) {
                 Log::error('Gagal mengambil UUID setelah register. Data respons: ' . json_encode($supabaseData));
                 return back()->with('error', 'Registrasi berhasil di Supabase, tapi gagal mengambil UUID.');
            }

            // =======================================================
            // 3. Simpan User ke Database LOKAL Laravel (Termasuk UUID)
            // =======================================================
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                // Kita menyimpan password hash agar Laravel Auth tetap bisa bekerja dengan sesi
                'password' => Hash::make($request->password), 
                'supabase_uuid' => $userUUID, // <-- FIX KRITIS: Menyimpan UUID
            ]);

            // 4. Notifikasi dan Otentikasi
            event(new Registered($user));
            Auth::login($user);
            
            // Redirect ke halaman dashboard atau homepage setelah login
            return redirect('/'); 

        } catch (\Exception $e) {
            Log::error('Error pada proses register: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem saat registrasi.');
        }
    }
}
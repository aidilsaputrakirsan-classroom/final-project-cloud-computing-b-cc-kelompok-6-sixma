<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;  
use Illuminate\Http\RedirectResponse;
use GuzzleHttp\Exception\ConnectException; // Tambahkan import ini

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $signInUrl = env('SUPABASE_URL') . '/auth/v1/token?grant_type=password';
            
            // 1. Coba Login ke Supabase Auth
            // 🚨 PERBAIKAN: Tambahkan ->verify(false) untuk mengatasi SSL/Koneksi lokal
            $response = Http::timeout(20)->verify(false)->withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
            ])->post($signInUrl, [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if (!$response->successful()) {
                // Supabase menolak (Password Salah atau User Tidak Ada)
                return back()->withErrors([
                    'email' => 'Email atau password yang Anda masukkan salah (Supabase).',
                ])->onlyInput('email');
            }

            $supabaseData = $response->json();
            $supabaseJwt = $supabaseData['access_token']; 
            $supabaseUuid = $supabaseData['user']['id'];

            // 2. Temukan dan Perbarui User Lokal
            $user = User::where('id', $supabaseUuid)->first();
            
            if (!$user) {
                // Skenario: User ada di Supabase, tapi hilang di database lokal Laravel
                return back()->withErrors(['email' => 'Akun ditemukan di Supabase tetapi tidak ada di database lokal. Harap hubungi admin.'])->onlyInput('email');
            }

            // Perbarui JWT yang baru diterima (untuk menjaga sesi Supabase)
            $user->supabase_jwt = $supabaseJwt;
            $user->save(); 

            // 3. Autentikasi Laravel
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended('/');

        } catch (ConnectException $e) {
             Log::error('❌ Guzzle Connect Exception in Login:', ['message' => $e->getMessage()]);
             return back()->withErrors(['error' => 'Gagal koneksi ke server Supabase. Cek jaringan atau .env.']);

        } catch (\Exception $e) {
            Log::error('❌ General Exception in Login:', ['message' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Gagal login: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        // ... (kode logout standar)
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log; 
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str; 
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Cookie; 

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
            $response = Http::timeout(20)->withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
            ])
            // FIX KRITIS UNTUK CURL ERROR 60 (SSL CERTIFICATE PROBLEM)
            ->withoutVerifying()
            ->post($signInUrl, [
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                
                $errorMessage = $errorBody['msg'] ?? 'Login gagal. Email atau password salah, atau akun belum terverifikasi.';

                return back()->withErrors([
                    'email' => $errorMessage,
                ])->onlyInput('email');
            }
            
            // ... (sisa logic penyimpanan JWT dan UUID) ...

            $supabaseData = $response->json();
            $supabaseJwt = $supabaseData['access_token'] ?? null;
            $supabaseUuid = $supabaseData['user']['id'] ?? null; 
            $userName = $supabaseData['user']['user_metadata']['name'] ?? 'Pengguna';
            
            if (!$supabaseJwt || !$supabaseUuid) {
                return back()->withErrors(['error' => 'Data otentikasi dari Supabase tidak lengkap.']);
            }
            
            $user = User::where('id', $supabaseUuid)->first();
            
            if (!$user) {
                return back()->withErrors([
                    'email' => 'Akun tidak ditemukan di database lokal. Harap hubungi admin.',
                ])->onlyInput('email');
            }

            $user->update([
                'remember_token' => Str::random(60), 
                'supabase_uuid' => $supabaseUuid, 
                'supabase_jwt' => $supabaseJwt,   
                'name' => $userName,             
            ]);

            Cookie::queue('supabase_jwt', $supabaseJwt, 60); 
            
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
            return redirect('/explore')->with('success', 'Selamat datang kembali, ' . $user->name . '!'); 
            

        } catch (ConnectException $e) {
            // Memberikan pesan error yang lebih jelas tentang SSL
            if (str_contains($e->getMessage(), 'SSL certificate problem')) {
                return back()->withErrors(['error' => 'Gagal koneksi: Masalah Sertifikat SSL (cURL). Coba update CA bundle atau hubungi admin.']);
            }
            return back()->withErrors(['error' => 'Gagal koneksi ke server Supabase. Cek jaringan atau .env.']);

        } catch (\Exception $e) {
            Log::error('Gagal login: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal login: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Cookie::queue(Cookie::forget('supabase_jwt')); 
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
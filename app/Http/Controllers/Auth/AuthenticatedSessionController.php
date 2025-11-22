<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;  
use Illuminate\Http\RedirectResponse;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            $signInUrl = env('SUPABASE_URL') . '/auth/v1/token?grant_type=password';

            // LOGIN KE SUPABASE (tanpa verify(false))
            $response = Http::timeout(20)
                ->withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Content-Type' => 'application/json',
                ])
                ->post($signInUrl, [
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

            if (!$response->successful()) {
                return back()->withErrors([
                    'email' => 'Email atau password salah.',
                ])->onlyInput('email');
            }

            $supabase = $response->json();
            $uuid  = $supabase['user']['id'];
            $token = $supabase['access_token'] ?? null;

            // CARI BERDASARKAN ID = UUID
            $user = User::where('id', $uuid)->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'Akun Supabase tidak ditemukan di database Laravel.',
                ]);
            }

            // Update token
            $user->supabase_jwt = $token;
            $user->save();

            // LOGIN LARAVEL
            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->intended('/');
        } 
        catch (\Exception $e) {
            Log::error('Login Exception:', ['msg' => $e->getMessage()]);
            return back()->withErrors([
                'error' => 'Gagal login: ' . $e->getMessage(),
            ]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // 1. Register ke Supabase Auth
            $signUpUrl = env('SUPABASE_URL') . '/auth/v1/signup';
            
            $response = Http::timeout(20)->withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
            ])->post($signUpUrl, [
                'email' => $request->email,
                'password' => $request->password,
                'data' => [
                    'name' => $request->name,
                ],
            ]);

            if (!$response->successful()) {
                $error = $response->json();
                Log::error('❌ Supabase registration failed', ['error' => $error]);
                
                return back()->withErrors([
                    'email' => $error['msg'] ?? 'Gagal mendaftar ke Supabase. Email mungkin sudah terdaftar.',
                ])->withInput();
            }

            $supabaseData = $response->json();
            $supabaseUuid = $supabaseData['user']['id'];
            $supabaseJwt = $supabaseData['access_token'] ?? null;

            Log::info('✅ Supabase registration success', [
                'uuid' => $supabaseUuid,
                'email' => $request->email,
            ]);

            // 2. Simpan ke Database Laravel dengan UUID dari Supabase sebagai ID
            $user = User::create([
                'id' => $supabaseUuid,  // ⭐ UUID dari Supabase sebagai primary key
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'supabase_jwt' => $supabaseJwt,
                'email_verified_at' => now(), // Auto-verify untuk dev
            ]);

            Log::info('✅ User created in Laravel database', [
                'id' => $user->id,
                'email' => $user->email,
            ]);

            // 3. Auto Login
            Auth::login($user);

            return redirect('/')->with('success', 'Registrasi berhasil!');

        } catch (\Exception $e) {
            Log::error('❌ Error pada proses register: ' . $e->getMessage());
            
            return back()->withErrors([
                'email' => 'Terjadi kesalahan saat registrasi: ' . $e->getMessage(),
            ])->withInput();
        }
    }
}
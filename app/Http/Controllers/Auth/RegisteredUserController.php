<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    public function create(): \Illuminate\View\View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $signUpUrl = env('SUPABASE_URL') . '/auth/v1/signup';
        $signInUrl = env('SUPABASE_URL') . '/auth/v1/token?grant_type=password';

        // REGISTER ke Supabase Auth
        $responseSignUp = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->withoutVerifying()
        ->post($signUpUrl, [
            'email' => $request->email,
            'password' => $request->password,
            'data' => ['name' => $request->name],
        ]);

        $responseBody = $responseSignUp->json();

        if (!$responseSignUp->successful()) {
            if (isset($responseBody['error_code']) && $responseBody['error_code'] === 'user_already_exists') {
                return back()->withErrors(['email' => 'Email sudah terdaftar. Silakan login.']);
            }
            return back()->withErrors(['email' => $responseBody['msg'] ?? 'Registrasi gagal.']);
        }

        // LOGIN otomatis untuk ambil UUID dan JWT
        $responseSignIn = Http::withHeaders([
            'apikey' => env('SUPABASE_ANON_KEY'),
            'Content-Type' => 'application/json',
        ])
        ->withoutVerifying()
        ->post($signInUrl, [
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if (!$responseSignIn->successful()) {
            return redirect()->route('login')->with('error', 'Registrasi berhasil, tetapi gagal login otomatis.');
        }

        $data = $responseSignIn->json();
        $supabaseUuid = $data['user']['id'] ?? null;
        $supabaseJwt = $data['access_token'] ?? null;

        if (!$supabaseUuid || !$supabaseJwt) {
            return back()->withErrors(['error' => 'UUID atau JWT Supabase tidak ditemukan.']);
        }

        // SIMPAN ke tabel users lokal
        $user = User::updateOrCreate(
            ['email' => $request->email],
            [
                'id' => $supabaseUuid,
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'supabase_jwt' => $supabaseJwt,
                'remember_token' => Str::random(60),
                'role_id' => 2, // <---- DEFAULT ROLE USER
            ]
        );

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('gallery.index')->with('success', 'Selamat datang! Akun berhasil dibuat.');
    }
}

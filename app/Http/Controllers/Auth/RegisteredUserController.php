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
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): \Illuminate\View\View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        
        $signUpUrl = env('SUPABASE_URL') . '/auth/v1/signup';
        $signInUrl = env('SUPABASE_URL') . '/auth/v1/token?grant_type=password';

        try {
            // =================================================================
            // 2. REGISTRASI KE SUPABASE AUTH (Membuat User di Supabase)
            // =================================================================
            $responseSignUp = Http::timeout(20)->withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
            ])
            // FIX KRITIS UNTUK CURL ERROR 60 (SSL CERTIFICATE PROBLEM)
            ->withoutVerifying()
            ->post($signUpUrl, [
                'email' => $request->email,
                'password' => $request->password,
                'data' => [
                    'name' => $request->name,
                ],
            ]);

            if (!$responseSignUp->successful()) {
                 $errorMessage = $responseSignUp->json()['msg'] ?? 'Registrasi gagal di Supabase Auth.';
                 return back()->withErrors(['email' => $errorMessage])->onlyInput('email');
            }

            // =================================================================
            // 3. LOGIN INSTAN (Ambil JWT dan UUID dari Supabase)
            // =================================================================
            // Supabase tidak mengembalikan JWT pada /signup, jadi kita harus /token
            $responseSignIn = Http::timeout(20)->withHeaders([
                'apikey' => env('SUPABASE_ANON_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->withoutVerifying()
            ->post($signInUrl, [
                'email' => $request->email,
                'password' => $request->password,
            ]);
            
            if (!$responseSignIn->successful()) {
                Log::error('Gagal mengambil token setelah registrasi.');
                // Mengarahkan ke halaman login jika token gagal, memaksa user login manual
                return redirect()->route('login')->with('error', 'Registrasi berhasil, tetapi gagal login otomatis. Silakan masuk secara manual.');
            }

            $supabaseData = $responseSignIn->json();
            $supabaseUuid = $supabaseData['user']['id'] ?? null;
            $supabaseJwt = $supabaseData['access_token'] ?? null;

            if (!$supabaseUuid || !$supabaseJwt) {
                 return back()->withErrors(['error' => 'Data UUID atau JWT tidak lengkap setelah sign-in.']);
            }
            
            // =================================================================
            // 4. BUAT/UPDATE USER DI DATABASE LOKAL
            // =================================================================
            $user = User::create([
                'id' => $supabaseUuid, // Menggunakan UUID Supabase sebagai Primary Key
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), 
                'supabase_uuid' => $supabaseUuid,
                'supabase_jwt' => $supabaseJwt, // Simpan JWT awal
                'remember_token' => Str::random(60),
            ]);

            event(new Registered($user));

            // 5. LOGIN OTOMATIS
            Auth::login($user);

            return redirect()->route('gallery.index')->with('success', 'Selamat datang, akun Anda berhasil dibuat!');

        } catch (ConnectException $e) {
            if (str_contains($e->getMessage(), 'SSL certificate problem')) {
                return back()->withErrors(['error' => 'Gagal koneksi: Masalah Sertifikat SSL (cURL). Hubungi admin.']);
            }
            Log::error('Registrasi Gagal (Koneksi): ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal koneksi ke server Supabase. Cek jaringan.']);

        } catch (\Exception $e) {
            Log::error('Registrasi Gagal: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Registrasi gagal total: ' . $e->getMessage()]);
        }
    }
}
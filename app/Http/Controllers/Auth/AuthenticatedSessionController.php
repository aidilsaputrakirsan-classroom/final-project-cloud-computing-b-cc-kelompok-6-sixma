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
        // Validasi input login
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            // URL login Supabase
            $signInUrl = env('SUPABASE_URL') . '/auth/v1/token?grant_type=password';

            // 1. Login ke Supabase Auth
            $response = Http::timeout(20)
                ->withHeaders([
                    'apikey' => env('SUPABASE_ANON_KEY'),
                    'Content-Type' => 'application/json',
                ])
                ->withoutVerifying()
                ->post($signInUrl, [
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

            // Jika gagal login
            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['msg']
                    ?? 'Login gagal. Email atau password salah, atau akun belum terverifikasi.';

                return back()->withErrors([
                    'email' => $errorMessage,
                ])->onlyInput('email');
            }

            // Ambil data dari Supabase
            $supabaseData = $response->json();
            $supabaseJwt  = $supabaseData['access_token'] ?? null;
            $supabaseUuid = $supabaseData['user']['id'] ?? null;
            $userName     = $supabaseData['user']['user_metadata']['name'] ?? 'Pengguna';

            if (!$supabaseJwt || !$supabaseUuid) {
                return back()->withErrors(['error' => 'Data otentikasi dari Supabase tidak lengkap.']);
            }

            // 2. Ambil user lokal dari database
            $user = User::with('role')->where('id', $supabaseUuid)->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'Akun tidak ditemukan di database lokal. Harap hubungi admin.',
                ])->onlyInput('email');
            }

            // Update informasi
            $user->update([
                'remember_token' => Str::random(60),
                'supabase_uuid'  => $supabaseUuid,
                'supabase_jwt'   => $supabaseJwt,
                'name'           => $userName,
            ]);

            // Simpan JWT ke cookie
            Cookie::queue('supabase_jwt', $supabaseJwt, 60);

            // Login Laravel
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            // 3. CEK ROLE DARI RELASI
            $roleName = $user->role->name ?? 'user';

            if ($roleName === 'admin') {
                return redirect('/dashboard-admin')
                    ->with('success', 'Selamat datang, Admin!');
            }

            return redirect('/explore')
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        } catch (ConnectException $e) {

            if (str_contains($e->getMessage(), 'SSL certificate problem')) {
                return back()->withErrors([
                    'error' => 'Gagal koneksi: Masalah Sertifikat SSL (cURL).'
                ]);
            }

            return back()->withErrors([
                'error' => 'Gagal koneksi ke server Supabase. Periksa jaringan atau file .env.'
            ]);
        } catch (\Exception $e) {

            Log::error('Gagal login: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Gagal login: ' . $e->getMessage()
            ]);
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

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use GuzzleHttp\Exception\ConnectException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        // Validasi login
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        try {
            // Supabase Login URL
            $signInUrl = env('SUPABASE_URL') . '/auth/v1/token?grant_type=password';

            // 1) Login ke Supabase Auth
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

            // Jika login gagal
            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['msg'] ?? 'Login gagal. Email atau password salah.';

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
                return back()->withErrors(['error' => 'Data login dari Supabase tidak lengkap.']);
            }

            // 2) Ambil user lokal berdasarkan UUID Supabase
            $user = User::with('role')->where('id', $supabaseUuid)->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'Akun tidak ditemukan di database lokal. Hubungi admin.',
                ]);
            }

            // Update user info
            $user->update([
                'remember_token' => Str::random(60),
                'supabase_uuid'  => $supabaseUuid,
                'supabase_jwt'   => $supabaseJwt,
                'name'           => $userName,
            ]);

            // Simpan JWT ke cookie
            Cookie::queue('supabase_jwt', $supabaseJwt, 60);

            // Login Laravel session
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            /*
            |--------------------------------------------------------------------------
            | REDIRECT SESUAI ROLE
            |--------------------------------------------------------------------------
            */

           $roleName = $user->role->name ?? 'user';

// Jika ADMIN → arahkan ke dashboard admin
if ($roleName === 'admin') {
    return redirect()
        ->route('admin.dashboard')
        ->with('success', 'Selamat datang, Admin!');
}

// Jika USER → arahkan ke explore
return redirect('/explore')
    ->with('success', 'Selamat datang kembali!');

        } catch (ConnectException $e) {

            if (str_contains($e->getMessage(), 'SSL certificate problem')) {
                return back()->withErrors([
                    'error' => 'Masalah SSL saat menghubungkan ke Supabase.'
                ]);
            }

            return back()->withErrors([
                'error' => 'Gagal terhubung ke Supabase. Cek koneksi atau file .env.'
            ]);

        } catch (\Exception $e) {

            Log::error('Login error: ' . $e->getMessage());

            return back()->withErrors([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        Cookie::queue(Cookie::forget('supabase_jwt'));

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

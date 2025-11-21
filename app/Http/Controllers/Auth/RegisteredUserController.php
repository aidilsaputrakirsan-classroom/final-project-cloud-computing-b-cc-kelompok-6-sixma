<?php

// Artrium/app/Http/Controllers/Auth/RegisteredUserController.php

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
use GuzzleHttp\Exception\ConnectException; // Import Guzzle Connect Exception untuk penanganan error koneksi

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman registrasi.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Tangani permintaan registrasi yang masuk.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Laravel
        // Aturan 'unique:users' sudah dihapus karena Primary Key dihandle oleh Supabase (UUID)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'], 
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // 2. Registrasi ke Supabase Auth
            $signUpUrl = env('SUPABASE_URL') . '/auth/v1/signup';
            $anonKey = env('SUPABASE_ANON_KEY');
            
            // Menggunakan Guzzle Client mentah untuk koneksi yang lebih andal
            $client = new \GuzzleHttp\Client(['verify' => false]);
            
            $response = $client->post($signUpUrl, [
                'headers' => [
                    'apikey' => $anonKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'email' => $request->email,
                    'password' => $request->password,
                    'data' => [
                        'full_name' => $request->name, 
                    ]
                ],
                'timeout' => 20, // Timeout 20 detik
            ]);
            
            // Cek respons Guzzle
            $status = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            // ----------------------------------------------------
            // PENANGANAN ERROR SUPABASE (Status GAGAL: 4xx/5xx)
            // ----------------------------------------------------
            if ($status < 200 || $status >= 300) {
                $error = json_decode($responseBody, true);
                $errorMessage = $error['msg'] ?? $error['message'] ?? 'Pesan error Supabase tidak tersedia.';

                Log::error('❌ Gagal Registrasi Supabase:', ['status_code' => $status, 'error_message' => $errorMessage]);

                return back()->withInput()->withErrors(['error' => 'Pendaftaran gagal: ' . $status . ' - ' . $errorMessage]);
            }
            
            // ----------------------------------------------------
            // PENANGANAN SUKSES SUPABASE (Status SUKSES: 2xx)
            // ----------------------------------------------------

            $supabaseData = json_decode($responseBody, true);
            
            // Cari UUID pengguna di beberapa tempat yang mungkin dikembalikan
            $supabaseUser = $supabaseData['user'] ?? null;
            $supabaseUuid = $supabaseUser['id'] ?? $supabaseData['id'] ?? null;

            if (empty($supabaseUuid)) {
                 Log::error('❌ Supabase Sukses Tapi UUID Hilang:', ['response' => $supabaseData]);
                 
                 // Jika UUID hilang, asumsikan pendaftaran sukses dan alihkan ke Login
                 return redirect()->route('login')->with('status', 'Pendaftaran berhasil! Silakan masuk menggunakan akun Anda.');
            }
            
            // 4. Buat User Lokal (Hanya kolom yang ada di migration UUID)
            $user = User::create([
                'id' => $supabaseUuid, 
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(uniqid()), // Menyimpan password hash dummy untuk Laravel
                // Kolom 'supabase_jwt' TIDAK disertakan di sini karena sudah dihapus dari migration
            ]);

            // 5. Otentikasi dan Redirect
            Auth::login($user); 
            event(new Registered($user));

            return redirect('/'); 

        } catch (ConnectException $e) {
            // Penanganan Koneksi Gagal (Timeout, SSL)
            Log::error('❌ Guzzle Connect Exception:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'Koneksi Gagal Total! Pesan: ' . $e->getMessage()]);

        } catch (\Exception $e) {
            // Penanganan Exception Umum
            Log::error('❌ General Exception in Registration:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'Kesalahan sistem: ' . $e->getMessage()]);
        }
    }
}
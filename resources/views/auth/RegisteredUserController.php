// Artrium/app/Http/Controllers/Auth/RegisteredUserController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisteredUserController extends Controller
{
    // Method untuk menampilkan form register
    public function create()
    {
        return view('auth.register'); // Harus membuat file view ini
    }

    // Method untuk memproses data register
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2. Buat User Baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // Tambahkan kolom lain jika ada (misal: role='user')
        ]);

        // 3. Notifikasi (Opsional)
        event(new Registered($user));

        // 4. Otentikasi dan Redirect
        Auth::login($user);
        
        // Redirect ke halaman dashboard atau homepage setelah login
        return redirect('/'); 
    }
}
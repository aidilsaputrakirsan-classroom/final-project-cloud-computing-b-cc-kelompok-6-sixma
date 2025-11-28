<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Role; // penting!

class RoleMiddleware
{
    public function handle($request, Closure $next, $roleName)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Jika user tidak punya relasi role
        if (!$user->role) {
            return redirect('/')->withErrors(['error' => 'Role user tidak ditemukan.']);
        }

        // Apakah nama role cocok?
        if ($user->role->name !== $roleName) {
            return redirect('/')->withErrors(['error' => 'Akses ditolak.']);
        }

        return $next($request);
    }
}

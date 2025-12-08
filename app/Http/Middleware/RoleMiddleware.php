<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $roleName = null)
    {
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::user();

        // ✅ Karena role adalah STRING di kolom users.role
        $userRole = $user->role ?? null;

        if (!$userRole) {
            return redirect('/explore')->with('error', 'Role user tidak ditemukan.');
        }

        if ($roleName && $userRole !== $roleName) {
            // Redirect sesuai role user
            if ($userRole === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Anda sudah di area admin.');
            }
            
            return redirect('/explore')->with('error', 'Akses ditolak. Halaman ini hanya untuk role: ' . $roleName);
        }

        return $next($request);
    }
}
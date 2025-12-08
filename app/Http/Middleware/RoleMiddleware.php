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
            return redirect('/login');
        }

        $user = Auth::user();

        // ✅ Cek apakah role adalah object (relasi) atau string langsung
        if (is_object($user->role)) {
            $userRole = $user->role->name ?? null;
        } else {
            $userRole = $user->role ?? null;
        }

        if (!$userRole) {
            return abort(403, 'Role user tidak ditemukan.');
        }

        if ($roleName && $userRole !== $roleName) {
            return abort(403, 'Akses ditolak untuk role ini.');
        }

        return $next($request);
    }
}
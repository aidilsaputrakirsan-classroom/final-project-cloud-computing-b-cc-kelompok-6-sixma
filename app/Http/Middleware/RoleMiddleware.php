<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
   public function handle($request, Closure $next, $roleName = null)
{
    if (!Auth::check()) {
        return redirect('/login');
    }

    $user = Auth::user();

    if (!$user->role) {
        return abort(403, 'Role user tidak ditemukan.');
    }

    if ($roleName && $user->role->name !== $roleName) {
        return abort(403, 'Akses ditolak.');
    }

    return $next($request);
}
}
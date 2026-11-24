<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Cek apakah user sudah login?
        if (!Auth::check()) {
            return redirect('/login');
        }

        // 2. Cek apakah role user sesuai dengan yang diminta?
        if (Auth::user()->role != $role) {
            // Jika Admin coba masuk halaman Kru, atau sebaliknya -> Tolak
            abort(403, 'AKSES DITOLAK: Anda tidak memiliki izin masuk ke halaman ini.');
        }

        return $next($request);
    }
}